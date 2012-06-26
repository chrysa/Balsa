<?php
	/**
	* @file pages.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief page de gestion des niveaux d'accès aux différentes pages
	* @category chrysa_axx 
	* @see gestion_acces_manager_mixed.class.php
	* @see gestion_acces_manager_SQL.class.php
	* @see gestion_acces_manager_XML.class.php
	* @global string $base_url url du site
	* @global string $manager_axx nom de la classe de gestion d'accès
	*/
	global $base_url,$manager_axx;	
	$acces=new $manager_axx();
	switch($_GET['act']){
		case 'mod':
		  //parcours des $_POSTS pour sélectionné les valeurs de mises a jours des niveau d'accès
			foreach($_POST as $id=>$val_acces){
				$id=str_replace('page_', '', $id);
				$acces->add_acces_page($id,$val_acces);
			}
			break;
		case 'supp':
			$acces->delete_page($_GET['supp']);
			break;
	}	
	$aff='<h2>gestion d\'accès aux pages</h2>';
	//récupération de la liste des pages
	$array_pages=$acces->get_list_pages();
	if(count($array_pages)>0){
	  //récupération des noms de tous les groupes
		$array_niveau=$acces->get_list_nom_niveau_acces();
		$aff.='<form method="post" action="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=page&act=mod">';
		$aff.='<table width="100%">';
		$aff.='<tr><td>nom</td><td>niveau minimum d\'accès</td><td></td></tr>';
		foreach($array_pages as $a){
			$aff.='<tr>';
			//suppression de l'extenssion
			$aff.='<td>'.$a['name'].'</td>';	
			$aff.='<td>';			
			if(is_array($array_niveau) AND count($array_niveau)>0){
				$aff.='<select name="page_'.$a['id'].'">';
				$aff.='<option value="">sélectionner</option>';
				foreach($array_niveau as $a_n){	
					if(!empty($a_n['name'])){						
						$aff.='<option value="'.$a_n.'"';
						if(!empty($a['niveau'])){
							$aff.=($a_n['val']==$a['niveau'])?	' selected="selected"' :'';
						}
						$aff.='>'.$a_n['name'].'</option>';			
					}
				}
				$aff.='</select>';
			}else{
				$aff.='aucuns niveau d\'accès n\'est définit';
			}	
			$aff.='</td>';
			$aff.='<td><a href="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=page&act=supp&supp='.$k.'">supprimer</a></td>';
			$aff.='</tr>';
		}
		$aff.='</table>';
		$aff.='<input type="submit" value="appliquer les modifications"/>';
		$aff.='</form>';
	}else{
		$aff='<div>aucunes pages n\'est renseignée</div>';
	}	
	echo $aff;
?>
