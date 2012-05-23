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
	<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_lang&action=ajout_lang">ajouter une langue</a>
	<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_lang&action=gestion_lang">gérer les langues</a>
	<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_lang&action=gen_all_lang">regénérer les fichiers langues</a>
	<?php	
	switch($_GET['action']){
		case 'ajout_lang':
				inc($path.'admin/plugin/chrysa_lang/ajout_lang.php');
			break;
		case 'ajout_page':
				inc($path.'admin/plugin/chrysa_lang/ajout_page.php');
			break;
		case 'gestion_lang':
				inc($path.'admin/plugin/chrysa_lang/gestion_lang.php');
			break;
		case 'gen_all_lang':
				inc($path.'admin/plugin/chrysa_lang/gen_all_lang.php');
				gen_multilingue();
			  header('location: '.$base_url.'admin.php?page_admin=1&module=chrysa_lang&action=gestion_lang');
			break;
		case 'modif_page':
				inc($path.'admin/plugin/chrysa_lang/modif_page.php');
			break;
		case 'suppr_page':
				unlink($path.'data/locale/'.$_GET['lang'].'/LC_MESSAGES/'.$_GET['page'].'.po');
			  header('location: '.$path.'admin/plugin/chrysa_lang/gestion_lang.php');
			break;
			default:
				$array_lang_exist=scandir($path.'data/locale/');
				//test de l'existence d'un dossier lang>2 pour ne pas prendre en compte '.' et '..'
				if(count($array_lang_exist)>2){
					inc($path.'admin/plugin/chrysa_lang/gestion_lang.php');
				}else{
					inc($path.'admin/plugin/chrysa_lang/ajout_lang.php');
				}
	}
	
}
?>