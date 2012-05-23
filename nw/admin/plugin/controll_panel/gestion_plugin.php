<?php
/**
 * @file gestion_plugin.php
 * @auteur chrysa
 * @version 1
 * @date 21 mai 2012
 * @category controll_panel
 * @global object $bdd objet de connexion à la base de données
 * @global string $base_url url du site
 * @global string $path chemin du dossier nw/
 * @see list_plugin_add()
 * @see list_plugin_dl()
 * @see list_plugin_delete()
 * @see compress_dir()
 * @see uncompress_dir()
 * @brief page de gestion des plugins additionnels
 * page de gestion des plugins additionnels permettant l'instalation, la désinstalation, l'upload, le téléchargement et la suppression de plugins
 * 
 * @todo réparer le téléchargemnt de fichier dû a des archives correompues après le téléchargement
 */
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
	//initialisation de l'actiona a faire
	$action=(!isset($_GET['gestion']) OR $_GET['gestion']=='') ? $action='gestion' : $action=$_GET['gestion'];
	//choix de l'action a faire
	switch($action)
	{
		case 'gestion':
			//affichage des plugins additionnels présents sur le serveur
			echo list_plugin_add();
			break;
		case 'upload':
			//affichage du formulaire d'upload
			?>
			<h1>Upload d'un Plugin</h1>
			<form method="post" action="<?php echo $base_url ?>admin.php?page_admin=a&module=controll_panel&action=plugin&gestion=upload" enctype="multipart/form-data">
				<input type="file" name="upl_plug"/>
				<input type="submit" name="valider" value="uploader" />
				(l'extensions valide est .zip)
			</form>
			si le plugin comtients des fichiers langues le plugin chrysa_lang serat automatiquement activé pour la prise en compte des fichiers
			<?php
				//test de présence d'un upload
				if(isset($_POST['valider']) AND isset($_FILES) AND !empty($_FILES)){			
					$fichier=$_FILES['upl_plug'];
					//suppression de l'extension
					$nom=strtolower(substr($fichier['name'],0,-4));
					//test de l'existence d'un plugin similaire
					if(is_dir($path.'admin/plugin/'.$nom)){
						echo 'un plugin portant le même nom existe déjà';
					}else{
						//récupération et test de l'extension
						$ext=strtolower(substr($fichier['name'],-3));
						if($ext=='zip'){
							//suppression de l'ancienne archive si elle est présente
							if(is_file($path.'admin/plugin/'.$fichier['name'])){
								unlink($path.'admin/plugin/'.$fichier['name']);
							}
							//déplacement du fichier
							move_uploaded_file($fichier['tmp_name'], $path.'admin/plugin/'.$fichier['name']);
							//décompression du fichier si il a la bonne extension
							if($ext=='zip'){
								if(uncompress_dir($fichier['name'].$ext,$path.'admin/plugin/'.$fichier['name'],'zip')){
									$uncompress=true;
								}else{
									$uncompress=false;
								}
							}
							//suppression de l'archive uploadée
							unlink($path.'admin/plugin/'.$fichier['name']);
							//suppression du fichier d'activation du plugin si il existe
							if(is_file($path.'admin/plugin/'.$nom.'/installed') && $uncompress==true){
								unlink($path.'admin/plugin/'.$nom.'/installed');
							}				
							echo '<div>votre plugins a bien été uploader vous pouvez maintenant <a href="'.$base_url.'admin.php?install=1&plugin='.$fichier['name'].'">l\'installer</a></div>';	
						}else{
								echo '<div>votre fichier ne possèdes pas une extension valide</div>';
						}
					}
				}
			break;
		case 'download':			
			//cas de téléchargement de plugins
			if(!isset($_GET['name'])){	
				echo '<h1>téléchargement d\'un Plugin</h1>pour télécharger un plugin il doit être désinstallé';
				//affichage de la liste de plugins
				echo list_plugin_dl();
			}else{
				//test de l'existence de l'archive
				if(is_file($path.'admin/plugin/'.$_GET['name'].'.zip')){
					unlink($path.'admin/plugin/'.$_GET['name'].'.zip');
				}
				//récupération des tables SQL à sauvegarder
				$install_xml=new DOMDocument();
				$install_xml->Load($path.'admin/plugin/'.$_GET['name'].'/install.xml');
				$install_sql=$install_xml->getElementsByTagName('sql');
				foreach ($install_sql as $install){  
					//test d'utilisation de tables SQL
					if(is_numeric($install->getAttribute('count')) AND $install->getAttribute('count')>0){
						$files_sql=$install->getElementsByTagName('file');
						foreach ($files_sql as $f_s){			
							$tables_sql=$f_s->getElementsByTagName('table');
							foreach ($tables_sql as $table_sql){
								//stockage des tables à sauvegarder
								$array_tables[]=$table_sql->getAttribute('name');
							}
						}
						$use_sql=1;
					}else{
						$use_sql=0;
					}
				}
				$install_xml->saveXML();
				if($use_sql==1){				
					foreach($array_tables as $table){		
						if($table!=''){					
							//génération du fichier des tables
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
					}
					//suppression d'anciens fichiers
					if(is_file($path.'admin/plugin/'.$_GET['name'].'/sql.sql')){
						unlink($path.'admin/plugin/'.$_GET['name'].'/sql.sql');
					}
					//création du nouveau fichier
					$sql_file=fopen($path.'admin/plugin/'.$_GET['name'].'/sql.sql','a+');
					fputs($sql_file,$create);
					fclose($sql_file);
				}
				//compression de l'archive
				compress_dir($_GET['name'],$path.'admin/plugin',$_GET['name'],$path.'admin/plugin','zip');		
				//génération de la redirection de téléchargement
				ob_start();
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.$_GET['name'].'.zip');
				header('Content-Length: '.filesize($_GET['name'].'.zip'));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				ob_clean();
				flush();
				readfile($path.'admin/plugin/'.$_GET['name'].'.zip'); 			
			}	
			break;
		case 'delete':
			//cas de suppression de plugins
			echo'<h1>supprimer un Plugin</h1>pour être supprimé un plugin il doit être désinstallé, lors de la suppression du plugins toutes les données stockées seront conservées';
			if(isset($_GET['name'])){
				if(!isset($_GET['OK']) OR !empty($_GET['OK'])){
				//message de confirmation de suppression
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
						//suppression du dossier
						if(rmdir_r($path.'admin/plugin/'.$_GET['name'])){
							echo '<div>le plugin '.$_GET['name'].' a bien été supprimé</div>';
						}else{
							echo '<div>le plugin '.$_GET['name'].' n\'as pas pu été supprimé</div>';
						}
					}else{
						//redirection vers la page de listing
						header('location: '.$base_url.'admin.php?page_admin=1&module=controll_panel&action=plugin&gestion=delete');	
					}
				}
			}else{
				//affichage des plugins supprimables
				echo list_plugin_delete();			
			}
			break;
		case 'install':
			include_once$path.'admin/plugin/installer.php';
			break;
		case 'uninstall':
			include_once$path.'admin/plugin/uninstaller.php';
			break;
	}
	?>
</div>