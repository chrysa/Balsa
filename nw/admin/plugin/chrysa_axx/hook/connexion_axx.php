<?php
	/**
	* @file connexion_axx.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief hook de connexion suivant les comptes
	* @category chrysa_axx
	* @global array $_HOOK contient les paramètre nécessaire au fonctoionnement du hook
	* @global string $path chemin du dossier nw
	* @global string $manager_user nom de la class de gestion des utilisateurs
	*/
	global $_HOOK,$manager_user,$config;
	if(!empty($manager_user) AND $config==1){
		$user=new $manager_user($_HOOK['type']);
		switch ($_HOOK['type']){
			case 'admin':
					$return_admin=$user->connect_admin($_HOOK['pseudo'],$_HOOK['mdp']);
					//test du banissement d'un compte et affichage de la date de fin si le compte est banni
					//test de l'activation du compte		
					switch ($return_admin){
						case $user::COMPTE_OK:				
							$_SESSION['user_id']=$_HOOK['id'];
							break;
						case $user::PASS_INCORRECT:
							$_HOOK['display']='<div>le mot de passse saisi est incorrect</div>';
							break;
						case $user::COMPTE_BAN:
							$_HOOK['display']='<div>le compte spécifié a até banni jusqu\'au '.date("d/m/Y à H:i:s",$user->fin_ban($_HOOK['id'])).'</div>';
							break;
						case $user::COMPTE_INACTIF:
							$_HOOK['display']='<div>ce compte est actuelement inactif</div>';
							break;
						case $user::COMPTE_INEXISTANT:
							$_HOOK['display']='<div>aucuns comptes correspondant à ces critères n\'as été trouvé</div>';
							break;
						default:
							$_HOOK['display']='<div> une erreur inatendue est survenue</div>';
					}
					break;
			case 'user':
					$return_user=$user->connect_user($_HOOK['input'],$_HOOK['$mdp']);
					//test du banissement d'un compte et affichage de la date de fin si le compte est banni
					//test de l'activation du compte
					switch ($return_user){
						case $user::COMPTE_OK:						
							$_SESSION['user_id']=$_HOOK['id'];
							break;
						case $user::PASS_INCORRECT:
							$_HOOK['display']='<div>le mot de passse saisi est incorrect</div>';
							break;
						case $user::COMPTE_BAN:
							$_HOOK['display']='<div>le compte spécifié a até banni jusqu\'au '.date("d/m/Y à H:i:s",$user->fin_ban($_HOOK['id'])).'</div>';
							break;
						case $user::COMPTE_INACTIF:
							$_HOOK['display']='<div>ce compte est actuelement inactif</div>';
							break;
						case $user::COMPTE_INEXISTANT:
							$_HOOK['display']='<div>aucuns comptes correspondant à ces critères n\'as été trouvé</div>';
							break;
						default:
							$_HOOK['display']='<div> une erreur inatendue est survenue</div>';
					}
					break;
		}	
	}
?>