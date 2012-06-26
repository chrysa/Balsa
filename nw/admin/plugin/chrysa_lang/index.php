<?php
/**
 * @file index.php
 * @auteur chrysa
 * @version 1
 * @date 21 mai 2012
 * @category chrysa_lang
 * @global string $path chemin du dossier nw/
 * @global string $base_url url du site
 * @see inc()
 * @see gen_multilingue()
 * @brief page de navigation pour la gestion des langues
 */
global $path,$base_url;
if(isset($_GET['install'])){

}else{
	?>
	<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=ajout_lang">ajouter une langue</a>
	<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=gestion_lang">gérer les langues</a>
	<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=gen_all_lang">regénérer les fichiers langues</a>
	<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=suppr_all">supprimer toutes les langues</a>
	<?php	
	switch($_GET['action']){
		case 'ajout_lang':
			inc($path.'admin/plugin/'.$_GET['module'].'/ajout_lang.php');
			break;
		case 'ajout_page':
			inc($path.'admin/plugin/'.$_GET['module'].'/ajout_page.php');
			break;
		case 'gestion_lang':
			inc($path.'admin/plugin/'.$_GET['module'].'/gestion_lang.php');
			break;
		case 'gen_all_lang':
			inc($path.'admin/plugin/'.$_GET['module'].'/gen_all_lang.php');
			gen_multilingue();
			inc($path.'admin/plugin/'.$_GET['module'].'/gestion_lang.php');
			break;
		case 'modif_page':
			inc($path.'admin/plugin/'.$_GET['module'].'/modif_page.php');
			break;
		case 'suppr_lang':
			rmdir_r($path.'data/locale/'.$_GET['lang']); 	
			$lang=scandir($path.'data/locale');
			if(count($lang)>2){
				inc($path.'admin/plugin/'.$_GET['module'].'/gestion_lang.php');
			}else{
				inc($path.'admin/plugin/'.$_GET['module'].'/ajout_lang.php'); 
			}	
			break;
		case 'suppr_all':
			$lang=scandir($path.'data/locale/'.$_GET['lang']);
			array_shift($lang); 
			array_shift($lang); 
			foreach($lang as $l){
				rmdir_r($path.'data/locale/'.$l); 
			}		
			inc($path.'admin/plugin/'.$_GET['module'].'/ajout_lang.php'); 
			break;
		case 'suppr_page':
			unlink($path.'data/locale/'.$_GET['lang'].'/LC_MESSAGES/'.$_GET['page'].'.po');
			header('location: '.$path.'admin/plugin/'.$_GET['module'].'/gestion_lang.php');
			break;
			default:
				$array_lang_exist=scandir($path.'data/locale/');
				//test de l'existence d'un dossier lang>2 pour ne pas prendre en compte '.' et '..'
				if(count($array_lang_exist)>2){
					inc($path.'admin/plugin/'.$_GET['module'].'/gestion_lang.php');
				}else{
					inc($path.'admin/plugin/'.$_GET['module'].'/ajout_lang.php');
				}
	}
	
}
?>