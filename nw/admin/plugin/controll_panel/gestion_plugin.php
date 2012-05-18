<?php 
	global $bdd,$base_url,$path;
?>
<div>
	<a href="<?php echo $base_url.'admin.php?page_admin=a&module=controll_panel&action=plugin&gestion=gestion'; ?>">gestion d'activation des plugins</a>
	<a href="<?php echo $base_url.'admin.php?page_admin=a&module=controll_panel&action=plugin&gestion=upload'; ?>">uploader un plugin</a>
	<a href="<?php echo $base_url.'admin.php?page_admin=a&module=controll_panel&action=plugin&gestion=download'; ?>">télécharger un plugin</a>
	<a href="<?php echo $base_url.'admin.php?page_admin=a&module=controll_panel&action=plugin&gestion=delete'; ?>">supprimer un plugin</a>
</div>
<div id="content_plugin">
	<?php
	$action = (!isset($_GET['gestion']) OR $_GET['gestion']=='') ? $action='gestion' : $action=$_GET['gestion'];
	switch($action)
	{
		case'gestion':
			echo list_plugin_add();
			break;
		case'upload':
			?>
			<h1>Upload d'un Plugin</h1>
			<form method="post" action="<?php echo $base_url ?>admin.php?page_admin=a&module=controll_panel&action=plugin&gestion=upload" enctype="multipart/form-data">
				<input type="file" name="upl_plug"/>
				<input type="submit" name="valider" value="uploader" />
				(l'extensions valide est .zip)
			</form>
			si le plugin comtients des fichiers langues le plugin chrysa_lang serat automatiquement activé pour la prise en compte des fichiers
			<?php
				if(isset($_POST['valider']) AND isset($_FILES) AND !empty($_FILES)){				
					$fichier=$_FILES['upl_plug'];
					$nom=strtolower(substr($fichier['name'],0,-4));
					if(is_dir($path.'admin/plugin/'.$nom)){
						echo 'un plugin portant le même nom existe déjà';
					}else{
						$ext=strtolower(substr($fichier['name'],-3));
						if($ext=='zip'){
							if(is_file($path.'admin/plugin/'.$fichier['name'])){
								unlink($path.'admin/plugin/'.$fichier['name']);
							}
							move_uploaded_file($fichier['tmp_name'], $path.'admin/plugin/'.$fichier['name']);
							if($ext=='zip'){
								if(uncompress_dir($fichier['name'].$ext,$path.'admin/plugin/'.$fichier['name'],'zip')){
									$uncompress=true;
								}else{
									$uncompress=false;
								}
							}
							unlink($path.'admin/plugin/'.$fichier['name']);
							if(is_file($path.'admin/plugin/'.$nom.'/installed') && $uncompress==true){
								unlink($path.'admin/plugin/'.$nom.'/installed');
							}		
							inc($path.'admin/plugin/controll_panel/controll_panel.php');			
							gen_liste_plugin_xml();
							echo'votre plugins a bien été uploader vous pouvez maintenant <a href="'.$base_url.'admin.php?install=1&plugin='.$fichier['name'].'">l\'installer</a>';	
						}else{
								echo'votre fichier ne possèdes pas une extension valide';
						}
					}
				}
			break;
		case'download':
			echo'<h1>téléchargement d\'un Plugin</h1>pour télécharger un plugin il doit être désinstallé';
			echo list_plugin_dl();
			if(isset($_GET['name'])){	
				if(is_file($path.'admin/plugin/'.$_GET['name'].'.zip')){
					unlink($path.'admin/plugin/'.$_GET['name'].'.zip');
				}

				$install_xml=new DOMDocument();
				$install_xml->Load($path.'admin/plugin/'.$_GET['name'].'/install.xml');
				$install_sql=$install_xml->getElementsByTagName('sql');
				foreach ($install_sql as $install){  
					$files_sql=$install->getElementsByTagName('file');
					foreach ($files_sql as $f_s){
						$tables_sql=$f_s->getElementsByTagName('table');
						foreach ($tables_sql as $table_sql){
							$array_tables[]=$table_sql->getAttribute('name');
						}
					}
				}
				$install_xml->saveXML();			

				foreach($array_tables as $table){			
					$sql='SHOW CREATE TABLE '.$table;
					$res=$bdd->query2($sql)->fetch(PDO::FETCH_ASSOC);
					$create_table=$res['Create Table'].";\n";
					$insertions='INSERT INTO '.$table.' VALUES (';
					$req_table=$bdd->query2('SELECT * FROM '.$table)->fetch(PDO::FETCH_ASSOC);
					foreach($req_table as $r_t){
						$insertions .='\''.$r_t.'\',';
					}
					$insertions=substr($insertions, 0, -1);
					$insertions.=");\n";
					$create.=$create_table.$insertions;
				}

				$sql_file=fopen($path.'admin/plugin/'.$_GET['name'].'/sql.sql','a+');
				fputs($sql_file,$create);
				fclose($sql_file);

				compress_dir($_GET['name'],$path.'admin/plugin',$_GET['name'],$path.'admin/plugin','zip');			

				ob_start();
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.$_GET['name'].'.zip');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: '.filesize($path.'admin/plugin/'.$_GET['name'].'.zip'));
				ob_clean();
				flush();
				readfile($path.'admin/plugin/'.$_GET['name'].'.zip');
				header('location: '.$base_url.'admin.php?page_admin=1&module=controll_panel&action=plugin&gestion=download');		
				unlink($path.'admin/plugin/'.$_GET['name'].'/sql.sql');
			}	
			break;
		case'delete':
			echo'<h1>supprimer un Plugin</h1>pour être supprimé un plugin il doit être désinstallé, lors de la suppression du plugins toutes les données stockées seront conservées';
			if(isset($_GET['name'])){
				if(!isset($_GET['OK']) OR !empty($_GET['OK'])){
				?>		
					<form method="post" action="<?php echo $base_url ?>admin.php?page_admin=a&module=controll_panel&action=plugin&gestion=delete&name=<?php echo $_GET['name']; ?>&OK=">
						êtes-vous sûr de vouloir supprimer le plugin <?php echo $_GET['name']; ?> ?<br/> 
						<label for="supprimer">oui </label><input type="radio" id="supprimer" name="confirmation" value="1"/>
						<label for="conserver">non </label><input type="radio" id="conserver" name="confirmation" value="0" checked="1"/><br/>
						<input type="submit" name="valider" value="supprimer" />
					</form>
				<?php
				}else{
					if($_POST['confirmation']==1){
						if(rmdir_r($path.'admin/plugin/'.$_GET['name'])){
							echo '<br/>le plugin '.$_GET['name'].' a bien été supprimé';
						}else{
							echo '<br/>le plugin '.$_GET['name'].' n\'as pas pu été supprimé';
						}
					}else{
						header('location: '.$base_url.'admin.php?page_admin=1&module=controll_panel&action=plugin&gestion=delete');	
					}
				}
			}else{
				echo list_plugin_delete();			
			}
			break;
	}
	?>
</div>