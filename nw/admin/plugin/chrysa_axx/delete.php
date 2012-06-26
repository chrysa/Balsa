<?php
/**
* @file delete.php
* @auteur chrysa
* @version 1
* @date 12 juin 2012
* @brief page de gestion de configuration
* @category chrysa_axx
* @global string $path chemin d'accès au dossier nw
* @global string $base_url url du site
* @global array $array_stock_clone array contenant les dénomination et la valeurs des méthodes de stockage mois la valeur mixée
* @global boolean $config variable désignant l'existence du fichier de config
*/
global $path,$base_url,$array_stock_clone,$config;
if(isset($_GET['reset']) AND $_GET['reset']=='OK' AND $config==1){
	foreach($array_stock_clone as $a_s_c){
		$manager_axx='gestion_acces_manager_'.$a_s_c;
		$manager_user='gestion_user_manager_'.$a_s_c;
		if(class_exists($manager_axx) AND class_exists($manager_user)){
			$axx=new $manager_axx();
			$admin=new $manager_user('admin');
			$user=new $manager_user('user');
			if(method_exists($axx, 'delete_stock')){
				$axx->delete_stock();
			}
			if(method_exists($admin, 'delete_stock')){
				$admin->delete_stock();
			}
			if(method_exists($user, 'delete_stock')){
				$user->delete_stock();
			}
		}
	}
	rmdir_r($path.'data/xml_axx');
 	header('Location: '.$base_url.'admin.php?page_admin=1&module='.$_GET['module']);
}
$aff='<div>réinitialiser le plugin rendra les compte utilisateurs et administrateurs inutilisables tant que le mot de passe n\'aurat pas été changé</div>';
$aff.='<a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=del&reset=OK">réinitialiser le plugin</a>';
echo $aff;
?>