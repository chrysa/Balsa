<?php
	/**
	* @file is_ban_axx.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief hook vérifiant si l'utilisateur est banni
	* @category chrysa_axx
	* @global array $_HOOK contient les paramètre nécessaire au fonctoionnement du hook
	* @global string $manager_user nom de la class de gestion des utilisateurs
	*/
	global $_HOOK,$manager_user,$config;
	if(!empty($manager_user) AND $config==1){
		$user= new $manager_user('user');
		$user_sel=$user->get_unique_user($_HOOK['user_id']);
		if($user_sel['ban']==1){
			$_HOOK['display']=$user_sel['fin_ban'];
		}else{
			$_HOOK['display']='';
		}
	}
?>