<?php
	/**
	* @file is_admin_axx.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief hook vérifiant si l'utilisateur est un administrateur
	* @category chrysa_axx
	* @global array $_HOOK contient les paramètre nécessaire au fonctoionnement du hook
	* @global string $manager_user nom de la class de gestion des utilisateurs
  * @global boolean $config variable désignant l'existence du fichier de config
	*/
	global $_HOOK,$manager_user,$config;
	if(!empty($manager_user) AND $config==1){
		$admin= new $manager_user('admin');
		$array_admin=$admin->get_list_admins();
		foreach($array_admin as $a_a){
			if($_HOOK['user_id']==$a_a['id']){
				return true;
				break;
			}else{
				return false;
			}
		}
	}
?>