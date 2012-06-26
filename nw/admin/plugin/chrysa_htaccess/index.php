<?php
/**
 * @file index.php
 * @auteur chrysa
 * @version 1
 * @date 21 mai 2012
 * @category chrysa_htaccess
 * @see inc()
 * @global string $path chemin du dossier nw/
 * @global string $base_url url du site
 * @global array $array_type_para_redir array conenant les types de paramètres utilisable en URL 
 * @global array $array_regex_para_redir array contenant les regex de vérification correspondantes à $array_type_para_redir
 * @var array $array_mod_apache array contenant tous les modules apaches activés
 * @brief page de navigation
 */
	global $path,$base_url,$array_type_para_redir,$array_regex_para_redir;
	if(isset($_GET['install'])){

	}else{
		if(!isset($_GET['action'])){
			$_GET['action']='config';
		}
		
		$array_mod_apache=apache_get_modules();
		if(!in_array('mod_rewrite',$array_mod_apache)){
			echo '<div>l\'url rewriting n\'est pas activé sur le serveur, le plugin nécessite son activation pour fonctionner</div>';
		}
		?>
			<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=config">configuration</a> 
			<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=access">gestion d'accès</a> 
			<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=redirection">gestion de redirections d'URL</a> 
			<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=error">gestion des pages d'erreur</a> 
			<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=gen">générer le htaccess</a>
			<div>
				<?php	
					switch($_GET['action']){
						case 'config':
							inc($path.'admin/plugin/'.$_GET['module'].'/conf_hta.php');
							break;
						case 'access':
							inc($path.'admin/plugin/'.$_GET['module'].'/gestion_access.php');
							break;
						case 'redirection':
  						$array_type_para_redir=array('type de valeurs attendue','numérique','texte','numérique et texte');
							inc($path.'admin/plugin/'.$_GET['module'].'/gestion_redirection.php');
							break;
						case 'error':
							inc($path.'admin/plugin/'.$_GET['module'].'/gestion_error.php');
							break;
						case 'gen':
							$array_regex_para_redir=array('','([0-9\-_]+)','([a-zA-Z\-_]+)','([a-zA-Z0-9\-_]+)');
							inc($path.'admin/plugin/'.$_GET['module'].'/gen_hta.php');
							break;
					}
			?>
		</div>	
	<?php	
	}
?>
