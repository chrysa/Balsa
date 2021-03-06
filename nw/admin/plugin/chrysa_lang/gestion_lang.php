<?php
/**
 * @file gestion_lang.php
 * @auteur chrysa
 * @version 1
 * @date 21 mai 2012
 * @category chrysa_lang
 * @global string $path chemin du dossier nw/
 * @global string $base_url url du site
 * @global array $array_lang array contenant les différentes langues prises en charges
 * @var array $array_lang_exist array contenant toutes les langues dans lequel le site est traduit
 * @brief page d'ajout de nouveaux code de traductions
 */
global $path,$array_lang,$base_url;
$array_lang_exist=scandir($path.'data/locale/');
array_shift($array_lang_exist); 
array_shift($array_lang_exist); 
inc($path.'admin/plugin/'.$_GET['module'].'/array_lang.php');
if(!isset($_GET['lang'])){
?>
<table width="100%">
	<caption>langues installées</caption>
	<tbody>
	<?php
	$compt=1;
	foreach ($array_lang_exist as $al){
			if($compt==1){
			?>
				<tr>
			<?php
			}
			?>
			<td>
				<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=gestion_lang&lang=<?php echo $al; ?>"><?php echo $array_lang[$al]; ?></a>
			</td>
			<td>
				<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=suppr_lang&lang=<?php echo $al; ?>">supprimer</a>
			</td>
			<?php
			if($compt==4){
			?>
				</tr>
			<?php
				$compt=0;
			}
			$compt++;
	}
	//finir la ligne si il n'y avait pas assez de langue
	if($compt>1){
	?>
		</tr>
	<?php
	}
	?>
	</tbody>
</table>
<?php
}else{
?>	
<h2><?php echo $array_lang[$_GET['lang']]; ?></h2>
<span>
	<form method="post" action="<?php echo $base_url ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&lang=<?php echo $_GET['lang']; ?>&action=ajout_page">
		<u>ajouter une page </u> :<br/>
		<label for="nom_page">nom de la page : </label><input type="text" name="nom_page" id="nom_page"/>
		<label for="nombre">nombre de traduction a saisir :</label>
		<select name="nombre">
		<?php		
			for($i=1, $i_max=100; $i<=$i_max; $i++){
				if($i<10){
					$i_aff='0'.$i;
				}else{
					$i_aff=$i;
				}
				$liste.='<option value="'.$i.'">'.$i_aff.'</option>';
			}
			echo $liste;
		?>		
		</select>
		<input type="hidden" name="lang" value="<?php echo $_GET['lang']; ?>"/>
		<input type="submit" name="ajouter" value="ajouter"/>
		(le nom de la page doit être différent de "traduction" car ce dernier est utilisé pour le fichier global)
	</form>
</span>
<?php
	$page=scandir($path.'data/locale/'.$_GET['lang'].'/LC_MESSAGES/');
	array_shift($page); 
	array_shift($page); 
	if(count($page>0)){
		foreach ($page as $p){
			$ext=substr($p,-3);
			$nom=substr($p,0,-3);
			if($ext=='.po'){
				if($nom!='traduction'){
					$liste.=str_replace('_', ' ',$nom).'
						<a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&lang='.$_GET['lang'].'&action=modif_page&page='.$nom.'">modifier</a>
						<a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&lang='.$_GET['lang'].'&action=suppr_page&page='.$nom.'">supprimer</a><br/>';
				}
			}else{
				echo '<br/>le fichier '.$nom.'.mo a été créé le '.date('d/m/Y \à H:i:s',filemtime($path.'data/locale/'.$_GET['lang'].'/LC_MESSAGES/'.$nom.'.mo')).'<br/>';
			}
		}
		echo $liste;
	}
}
?>