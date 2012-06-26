<?php
 /**
	* @file user.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief page de gestion des utilisateurs
	* @category chrysa_axx
	* @see day_selector()
	* @see month_selector()
	* @see year_selector()
	* @global string $base_url url du site
	* @global string $manager_user nom de la classe de gesion des utilisateurs
	*/
	global $base_url,$manager_user;
	$user=new $manager_user('user');
	switch ($_GET['act']){
    case 'mod':
        //récupération des information de l'utilisateur sélectionné
				$user_sel=$user->get_unique_user($_GET['mod']);
				$aff.='<h2>modification du compte '.$user_sel['pseudo'].'</h2>
							<form method="post" action="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=user&type=user&act=modifier&modifier='.$_GET['mod'].'">
								<div>
									<label for="pseudo">pseudo : </label><input name="pseudo" id="pseudo" type="text" value="'.$user_sel['pseudo'].'"/>
								</div>
								<div>
									<label for="pass">mot de passe : </label><input name="pass" id="pass" type="text" value=""/>
								</div>
								<div>
									<label for="mail">adresse mail : </label><input name="mail" id="mail" type="text" value="'.$user_sel['mail'].'" />
								</div>
								<div>
								<div>inscrit le '.date("d/m/Y à H:i:s",$user_sel['inscription']).'</div>
								<div>
									<lable for="etat">état du compte :</label>
									<select name="activ" id="etat">
										<option value="0"';
										$aff.=($user_sel['activ']==0) ? ' selected="selected"' : '';
										$aff.='>désactivé</option>
										<option value="1"';
										$aff.=($user_sel['activ']==1) ? ' selected="selected"' : '';
										$aff.='>activé</option>
									</select>
								</div>
								<div>
									<label for="ban">compte banni : </label> 
									<select name="ban" id="ban">
										<option value="0"';
										$aff.=($user_sel['ban']==0)?' selected="selected"':'';
										$aff.='>non</option>
										<option value="1"';
										$aff.=($user_sel['ban']==1)?' selected="selected"':'';
										$aff.='>oui</option>
									</select>
								</div>
								<div>
									date de fin du ban le '.day_selector().month_selector().year_selector().' à '.hour_selector().minute_selector().second_selector().'
								</div>
								<input type="submit" value="modifier"/>
							</form>
							<div><a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=user&type=user&act=supprimer&supp='.$_GET['mod'].'">supprimer le compte utilisateur</a></div>';
        break;
    case 'modifier':
				if($_POST['ban']==1){
					$fin_ban=mktime($_POST['hour_'],$_POST['minute_'],$_POST['second_'],$_POST['month_'],$_POST['day_'],$_POST['year_']);
				}else{
					$fin_ban='';
				}
				$user->modify_user($_GET['modifier'],$_POST['pseudo'],$_POST['pass'],$_POST['mail'],$_POST['activ'],$_POST['ban'],$fin_ban);
        break;
    case 'supprimer':
				$user->delete_user($_GET['supp']);
        break;
	}	
	//génération de la liste des utilisateurs
	$array_users=$user->get_list_users();
	if(count($array_users)>0){
		$aff.='<h2>liste des comptes utilisateurs</h2>
					<table width="100%">
						<tr><td>pseudo</td><td>mail</td><td>date d\'inscription</td><td>activé</td><td>banni</td><td>date de fin de ban</td><td></td></tr>';
						foreach($array_users as $a_u){
							$activ=($a_u['activ']==0) ? 'non activé' : 'activé';
							$ban=($a_u['ban']==0) ? 'non banni' : 'banni';
							$aff.='<tr><td>'.$a_u['pseudo'].'</td><td>'.$a_u['mail'].'</td><td>'.date("d/m/Y à H:i:s",$a_u['inscription']).'</td><td>'.$activ.'</td><td>'.$ban.'</td><td>'.$a_u['fin_ban'].'</td>
											<td><a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=user&type=user&act=mod&mod='.$a_u['id'].'">modifier</a></td>		
										</tr>';
						}
		$aff.='</table>';
	}else{
		$aff.='<div>aucun utilisateur n\'est enregistré</div>';
	}
	echo $aff;
?>
