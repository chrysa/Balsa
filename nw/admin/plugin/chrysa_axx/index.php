<?php
	/**
	* @file index.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief page de gestion de navigation
	* @category chrysa_axx
	* @see inc()
	* @global array $array_stock array contenant les dénomination et la veleurs des méthodes de stockage
	* @global string $base_url url du site
	* @global string $manager_axx nom de la classe de gesion des accès
	* @global string $manager_user nom de la classe de gesion des utilisateurs
	* @global string $path chemin du dossier nw
  * @global boolean $config variable désignant l'existence du fichier de config
	*/
	global $base_url,$path,$config;
	if(isset($_GET['install'])){

	}else{			
		?>
		<div>
			<a href="<?php echo $base_url ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=page">gérer les accès aux pages</a> 
			<a href="<?php echo $base_url ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=group">gérer les groupes d'accès</a> 
			<a href="<?php echo $base_url ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=user">gestion des utilisateurs</a> 
			<a href="<?php echo $base_url ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=conf">configurer</a>
			<a href="<?php echo $base_url ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=del">réinitialiser le plugin</a>
		</div>
		<?php
		//redirection vers la configuration si cette dernière n'as pas eu lieux
		if(!isset($config) OR $config==0){
			inc($path.'admin/plugin/'.$_GET['module'].'/config.php');
		}else{
			//sélection de la page à inclure
			$action=(isset($_GET['action']))?$_GET['action']:'page';
			switch($action){
				case 'page':
					inc($path.'admin/plugin/'.$_GET['module'].'/pages.php');
					break;
				case 'group':
					inc($path.'admin/plugin/'.$_GET['module'].'/niveau.php');
					break;
				case 'user':
					?>
					<div>
						<a href="<?php echo $base_url ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=user&type=user">liste des utilisateurs</a>
						<a href="<?php echo $base_url ?>admin.php?page_admin=1&module=<?php echo $_GET['module'] ?>&action=user&type=admin">liste des administrateurs</a>
					</div>
					<?php
					$type=(isset($_GET['type']))?$_GET['type']:'user';
					switch($type){
						case 'user':
							inc($path.'admin/plugin/'.$_GET['module'].'/user.php');
							break;
						case 'admin':
							inc($path.'admin/plugin/'.$_GET['module'].'/admin.php');
							break;
					}
					break;
				case 'conf':
					inc($path.'admin/plugin/'.$_GET['module'].'/config.php');
					break;
				case 'del':
					inc($path.'admin/plugin/'.$_GET['module'].'/delete.php');
					break;
					default:
						inc($path.'admin/plugin/'.$_GET['module'].'/pages.php');
			}        
		}
	}
?>
