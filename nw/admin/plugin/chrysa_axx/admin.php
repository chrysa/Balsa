<?php
 /**
	* @file admin.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief page de gestion des administrateurs
	* @category chrysa_axx
	* @global string $base_url url du site
	* @global string $manager_user nom de la classe de gesion des utilisateurs
	* @global string $stock type de stockage utilisé
	*/
	global $base_url,$manager_user,$stock;
	//initialisation des classes
	$user_manager=new $manager_user('user');
	$admin_manager=new $manager_user('admin');
	//choix d'action
	switch ($_GET['act']){
		case 'add':
			//sélection d'un utilisateur
			$user=$user_manager->get_unique_user($_POST['users']);
			//ajout du compte administrateur
			$admin_manager->add_admin($user);
			break;
		case 'mod':
			//sélection de l'utilisateur cible
			$admin=$admin_manager->get_unique_admin($_GET['mod']);
			//génération du formulaire de modification
			$aff.='<h2>modification du compte '.$admin['pseudo'].'</h2>
						 <form method="post" action="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=user&type=admin&act=modifier&modifier='.$_GET['mod'].'&id='.$admin['id'].'">
							<div>
								<label for="pseudo">pseudo : </label><input name="pseudo" id="pseudo" type="text" value="'.$admin['pseudo'].'"/>
							</div>
							<div>
								<label for="pass">mot de passe : </label><input name="pass" id="pass" type="text" value=""/>
							</div>
							<div>
								<label for="mail">adresse mail : </label><input name="mail" id="mail" type="text" value="'.$admin['mail'].'" />
							</div>
							<input type="submit" value="modifier"/>
						 </form>
						 <div><a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=user&type=user&act=supprimer&supp='.$_GET['mod'].'">supprimer le compte utilisateur</a></div>';			
			break;
		case 'modifier':
			$del='';
			if($stock=='XML'){			
				//définition du pseudo à enregistrer eet traitement a faire du noeud existant
				if($_GET['modifier']!=$_POST['pseudo']){
					$pseudo=$_POST['pseudo'];
					$del=1;
				}else{
					$pseudo=$_GET['modifier'];
					$del=0;
				}
			}
			//enregistrement des modifications
			$admin_manager->modify_admin($_GET['id'],$pseudo,$_POST['pass'],$_POST['mail'],$del);
			break;
		case 'supprimer':
			//suppression du compte administrateur cible
			$admin_manager->delete_admin($_GET['supp']);
			break;
	}	
	//récupération de tous les noms de compte administrateurs
	$array_nom_admin=$admin_manager->get_list_admins_name();
	//récupération des informations de tous les comptes utilisateurs
	$array_users=$user_manager->get_list_users();
	$aff.='<h2>ajouter un administrateur</h2>';
	$aff.='<div>';	
		$aff.='<form method="post" action="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=user&type=admin&act=add">';
			$aff.='liste des utilisateurs non administrateurs : ';
			//génération de la liste de sélection des comptes utlisateurs pouvant être ajouté comme compte administrateur
			$aff.='<select name=users">';
				$aff.='<option value="">liste des utilisateurs</option>';
				foreach($array_users as $a_u){
					//test d'existence du pseudo en cours de traitement dans la liste des comptes admins
					if(!in_array($a_u['pseudo'],$array_nom_admin)){
						$aff.='<option value="'.$a_u['id'].'">'.$a_u['pseudo'].'</option>';
					}
				}
			$aff.='</select>';
			$aff.='<input type="submit" value="ajouter"/>';
		$aff.='</form>';
	$aff.='</div>';
	//génération de la liste des comptes administrateurs
	$aff.='<h2>liste des comptes administrateurs</h2>';
	$aff.='<table width="100%">';
		$aff.='<tr><td>nom</td><td></td></tr>';
		foreach($array_nom_admin as $compte){
			$aff.='<tr>';
				$aff.='<td>'.$compte.'</td>';
				$aff.='<td><a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=user&type=admin&act=mod&mod='.$compte.'">modifier</a></td>';
			$aff.='</tr>';
		}
	$aff.='</table>';
	echo $aff;
?>
