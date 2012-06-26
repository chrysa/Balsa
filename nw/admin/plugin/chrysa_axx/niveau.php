<?php
	/**
	* @file niveau.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief
	* @category chrysa_axx
	* @see gestion_acces_manager_mixed.class.php
	* @see gestion_acces_manager_SQL.class.php
	* @see gestion_acces_manager_XML.class.php
	* @see gestion_acces_user_mixed.class.php
	* @see gestion_acces_user_SQL.class.php
	* @see gestion_acces_user_XML.class.php
	* @global string $base_url url du site
	* @global string $manager_axx nom de la classe de gestion d'accès
	* @global string $manager_user nom de la classe de gestion des utilisateurs
	*/
	global $base_url,$manager_user,$manager_axx;
	$acces=new $manager_axx();	
	$aff='<h2>gestion des groupes d\'accès </h2>';
	switch ($_GET['act']){
		case 'add':
			//test de validité du membre a ajouter
			if($acces->is_valid_acces($_POST['name'],$_POST['lvl'])){
				$acces->add_acces('',$_POST['name'],$_POST['lvl']);			
			}else{
				$aff.='<div>le groupe d\'accès saisi n\'est pas valide</div>';
			}
			break;
		case 'mod':
			//sélection du groupe a modifier
			$array_sel=$acces->get_unique_acces($_GET['mod']);
			$aff.='<h3>modification d\'un accès</h3>
					 	<form method="post" action="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=groupe&act=modifier&mod='.$_GET['mod'].'">
							<label for="name">nom du groupe d\'accès : </label><input type="input" id="name" name="name" value="'.$array_sel['name'].'"/>
							<label for="lvl">niveau de priorité</label><input type="input" id="lvl" name="lvl" value="'.$array_sel['niveau'].'"/>
							<input type="submit" value="modifier"/>
					 	</form>';
					 	if($_GET['mod']!=0){
        			$aff.='<div><a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=groupe&act=supp&supp='.$a['id'].'">supprimer</a></div>';
        	  }
			break;
		case 'modifier':	
			//enregistrement des modifications
			if($acces->is_valid_acces($_POST['name'],$_POST['lvl'])){
				$acces->modify_acces($_GET['mod'],$_POST['name'],$_POST['lvl']);
			}
			break;
		case 'supp':
			//suppression d'un niveau d'accès
			$acces->delete_acces($_GET['supp']);
			break;
		case 'list_user':
			//récupération des information du groupe
		  $acces_sel=$acces->get_unique_acces($_GET['list']);
			//récupération de la liste des membres suivant le groupe sélectionné
      if($_GET['list']==0){
	      $users=new $manager_user('admin');
	      $list_membres=$users->get_list_admins();
      }else{
        $list_membres=$users->get_list_membre($_GET['list']);
      }
		  $aff.='<h3>gestion des utilisateurs du groupe '.$acces_sel['name'].'</h3>';
			$aff.='<h4>ajouter un membre</h4>'; 	
			//formulaire d'ajout de membres au groupe
			$aff.='<form method="post" action="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=groupe&act=attr_user&group='.$_GET['list'].'">'; 	
				$aff.='<select name="list_users">';
					//récupération de la liste des tpus les utiolisateurs
					$liste_users=$users->get_list_users();
					foreach($liste_users as $l_u){
						//test d'existence de l'utilisateur en cours de traitement parmis les administrateurs
						if($_GET['list']==0){
							//dans le cas du groupe administrateur
							if(in_array($l_u['id'],$list_membres)){
								$exist=1;
							}else{
								$exist=0;
							}
						}else{
							//dans le cas de tout autres groupes que celui d'administrateur
							foreach($list_membres as $l_m){
								if($l_m['id']==$l_u['id'] AND $l_m['pseudo']==$l_u['pseudo']){
									$exist=1;
									break;
								}else{
									$exist=0;
								}
							}
						}						
						//générationd es option de la liste déroulante de choix
						if($exist==0){
							$aff.='<option value="'.$l_u['id'].'">'.$l_u['pseudo'].'</option>';	
						}
					}
				$aff.='</select>'; 	
				$aff.='<input type="submit" value="ajouter"/>'; 	
			$aff.='</form>';			
			//liste des membres du groupe
		  $aff.='<h4>liste des membres</h4>';
    	$aff.='<table width="100%">';	  
  		$aff.='<tr><td>nom</td><td></td></tr>';
		  foreach($list_membres as $l){
				//génération du lien permettant de retirer l'utilisateur du groupe
		    if($_GET['group']==0){
		      $user_sel=$users->get_unique_admin($l);
          $aff.='<tr><td>'.$user_sel['pseudo'].'</td><td><a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=groupe&act=rm_user&group='.$_GET['list'].'&pseudo='.$l.'">enlever</a></td></tr>';				 
		    }else{
		      $user_sel=$users->get_unique_user($l);
          $aff.='<tr><td>'.$user_sel['pseudo'].'</td><td><a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=groupe&act=rm_user&group='.$_GET['list'].'&remove='.$user_sel['id'].'">enlever</a></td></tr>';				  
        }
		  }
  		$aff.='</table>';
			break;
		case 'attr_user':
			//attribution d'un utilisateur a un groupe
			$acces->add_to_group($_POST['list_users'],$_GET['group']);
		  if($_GET['group']==0){
		    $admins=new $manager_user('admin');
		    $admins->add_admin($_GET['pseudo']);
		  }
			break;
		case 'rm_user':
			//suppression d'un utilisateur d'un groupe
		  $acces->remove_from_group($_GET['remove'],$_GET['group']);
		  if($_GET['group']==0){
		    $admins=new $manager_user('admin');
		    $admins->delete_admin($_GET['pseudo']);
		  }
			break;
	}		
	//formulaire d'ajout d'un nouveau groupe
	$aff.='<h3>ajouter un groupe d\'accès </h3>
				 <form method="post" action="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=group&act=add">
				 	 <label for="name">nom du groupe d\'accès : </label><input type="input" id="name" name="name"/>
				 	 <label for="lvl">niveau de priorité</label><input type="input" id="lvl" name="lvl"/>
					 <input type="submit" value="ajouter"/>
				 </form>';
	//liste des groupes d'accès déjà saisis
	$aff.='<h3>liste des groupes d\'accès </h3>';
	$array_global=$acces->get_list_acces();
	if(!is_array($array_global) OR count($array_global)==0){
		$aff.='aucuns groupes d\'accès n\'est définit';
	}else{	
		$array_niveau=$acces->get_list_niveau_acces();
		$aff.='<table width="100%">';
		$aff.='<tr><td>nom</td><td>priorité d\'accès</td><td></td></tr>';
		foreach($array_global as $a){
			$aff.='<tr>';
			$aff.='<td>'.$a['name'].'</td>';
			$aff.='<td>';
			//génération de la liste déroulante des priorité d'accès
			$aff.='<select name="lvl_'.$a['id'].'">';
			foreach($array_niveau as $a_n){		
				$aff.='<option value="'.$a_n['niveau'].'"';
				$aff.=($a_n['niveau']==$a['niveau'])?	' selected="selected"' :'';
				$aff.='>'.$a_n['niveau'].'</option>';			
			}
			$aff.='</select>';
			$aff.='</td>';
			$aff.='<td><a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=groupe&act=mod&mod='.$a['id'].'">modifier</a></td>';	
			$aff.='<td><a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=groupe&act=list_user&list='.$a['id'].'">gérer les membres</a></td>';
			$aff.='</tr>';
		}
		$aff.='</table>';
	}
	echo $aff;
?>
