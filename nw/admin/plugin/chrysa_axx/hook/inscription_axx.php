<?php
	/**
	* @file ajout_page_gestion_axx.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief hook d'inscription de nouveaux utilisateurs
	* @category chrysa_axx
	* @global array $_HOOK contient les paramètre nécessaire au fonctoionnement du hook
	* @global string $manager_user nom de la class de gestion des utilisateurs
  * @var object $user instance de $manager_user dédiée aux utilisateurs
	*/
	global $_HOOK,$manager_user,$config;
	if(!empty($manager_user) AND $config==1){
		$user= new $manager_user('user');
		$exist=$user->is_valid_user($_HOOK['pseudo'],$_HOOK['mail']);
		switch ($exist){
			case $user::COMPTE_VALID:
					$user->add_user('',$_HOOK['pseudo'],$_HOOK['mdp'],$_HOOK['mail']);
					break;
			case $user::MAIL_INCORRECT:
					$_HOOK['display']='<div>l\'adresse mail renseignée n\'as pas un format valide</div>';
					break;
			case $user::MAIL_USE:
					$_HOOK['display']='<div>l\'adresse mail renseignée est déjà utilisée par un autre compte</div>';
					break;
		}
	}
?>