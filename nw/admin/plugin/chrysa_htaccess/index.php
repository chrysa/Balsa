<?php
	global $path;
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
			<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_htaccess&action=config">configuration</a> 
			<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_htaccess&action=access">gestion d'accès</a> 
			<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_htaccess&action=redirection">gestion de redirections d'URL</a> 
			<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_htaccess&action=error">gestion des pages d'erreur</a> 
			<a href="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_htaccess&action=gen">générer le htaccess</a>
			<div>
				<?php	
					switch($_GET['action']){
						case 'config':
							inc($path.'admin/plugin/chrysa_htaccess/conf_hta.php');
							break;
						case 'access':
							inc($path.'admin/plugin/chrysa_htaccess/gestion_access.php');
							break;
						case 'redirection':
							inc($path.'admin/plugin/chrysa_htaccess/gestion_redirection.php');
							break;
						case 'error':
							inc($path.'admin/plugin/chrysa_htaccess/gestion_error.php');
							break;
						case 'gen':
							inc($path.'admin/plugin/chrysa_htaccess/gen_hta.php');
							break;
					}
			?>
		</div>	
	<?php	
	}
?>