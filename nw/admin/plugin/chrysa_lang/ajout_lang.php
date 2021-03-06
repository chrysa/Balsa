<?php
/**
 * @file ajout_lang.php
 * @auteur chrysa
 * @version 1
 * @date 21 mai 2012
 * @category chrysa_lang
 * @global string $path chemin du dossier nw/
 * @global string $base_url url du site
 * @global array $array_lang array contenant les différentes langues prises en charges
 * @var array $array_lang_exist array contenant toutes les langues dans lequel le site est traduit
 * @brief page d'ajout d'une nouvelle langue
 */
global $path,$base_url,$array_lang;
//récupération des noms des fichiers langue
$array_lang_exist=scandir($path.'data/locale/');
//test de l'action d'ajout de langue
if(isset($_POST['ajouter'])){
	//création des nouveau dossier pour la langue
	mkdir($path.'data/locale/'.$_POST['lang']);
	mkdir($path.'data/locale/'.$_POST['lang'].'/LC_MESSAGES');
	//si il existe déja une langue on copie son contenu dans le nouveau dossier pour n'avoir plus qu'a traduire
	if(count($array_lang_exist)>2){
		$page=scandir($path.'data/locale/'.$array_lang_exist[2].'/LC_MESSAGES');
		array_shift($page); 
		array_shift($page);
		foreach($page as $p){
			copy($path.'data/locale/'.$array_lang_exist[2].'/LC_MESSAGES/'.$p, $path.'data/locale/'.$_POST['lang'].'/LC_MESSAGES/'.$p );
		}
	}
	header('location: '.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=gestion_lang');
}
//liste des langues disponibles
?>
<form method="post" action="<?php echo $base_url ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=ajout_lang">
	<table width="100%">
		<caption>Sélectionner une langue a ajouter</caption>
		<tbody>
		<?php
		inc($path.'admin/plugin/'.$_GET['module'].'/array_lang.php');
		$compt=1;
		foreach ($array_lang as $k => $v){
			if(!in_array($k,$array_lang_exist)){
				if($compt==1){
				?>
					<tr>
				<?php
				}
				?>
				<td>
					<input type="radio" id="lang_<?php echo $k; ?>" name="lang" value="<?php echo $k; ?>"/><label for="lang_<?php echo $k; ?>"><?php echo $v; ?></label>
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
	<input type="submit" name="ajouter" value="ajouter"/>
</form>