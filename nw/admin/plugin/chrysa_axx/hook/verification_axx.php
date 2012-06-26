<?php 
	/**
	* @file verification_axx.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief hook vérifiant que l'utilisateur à accès a la page
	* @category chrysa_axx
	* @global array $_HOOK contient les paramètre nécessaire au fonctoionnement du hook
	* @global string $manager_axx nom de la class de gestion d'acces
	*/
	global $_HOOK,$manager_axx,$config;
	if(!empty($manager_axx) AND $config==1){
		$acces= new $manager_axx();
		$lvl_axx=$acces->get_acces_page($_HOOK['page']);
		$list_membre=$acces->get_list_membre($lvl_axx);
		if($lvl_axx!='' AND in_array($_SESSION['user_id'],$list_membre)){
			return true;
		}else{
			return false;
		}
	}
?>
