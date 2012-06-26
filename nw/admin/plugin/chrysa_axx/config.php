<?php
/**
	* @file config.php
	* @auteur chrysa
	* @version 1
	* @date 26 mai 2012
	* @brief page de gestion de configuration
	* @category chrysa_axx
	* @see copy_r($path, $dest)
	* @see gestion_files_and_store($type,$old_type='')
	* @global array $array_stock array contenant les dénomination et la veleurs des méthodes de stockage
	* @global string $base_url url du site
	* @global array $fonctions_XML array contenant le nom des fichiers de gestion XML
	* @global array $fonctions_SQL array contenant le nom des fichiers de gestion SQL
	* @global string $path chemin d'accès au dossier nw
	* @global string $db_name nom de la base de données à utiliser
	* @global string $host_db nom de l'hote de la base de données
	* @global string $mdp_db mot de passe d'accès a la base de données
  * @global string $stock type de stockage utilisé 
	* @global string $user_db nom d'utilisateur de la base de données
	*/
	global $base_url,$path,$array_stock,$array_stock_clone,$user_db,$db_name,$host_db,$hash,$array_hash,$stock;
	//génération d'une variable contenant un array composé de la liste des fichiers liés au type de stockage en cours de traitement
	foreach($array_stock as $k => $v){
	  global ${'fonctions_'.$k};
		${'fonctions_'.$k}=array('acces'=>'gestion_acces_manager_'.$k.'.class.php','user'=>'gestion_user_manager_'.$k.'.class.php');						
	}		
	/**
	 * @fn gestion_files_and_store($type,$old_type='')
   * @brief fonction de gestion des fichiers fonctions liés au plugin suivant le type de stockage
	 * @global string $path chemin d'accès au dossier 
	 * @global string $array_stock_clone clone de $array_stock duquel on a enlevé le type mixed
	 * @param string $stock type de stockage en cours d'utilisation
	 */
	function gestion_files($stock){		
			global $path,$array_stock;
			//récupération des variables globales contenant les fichiers classes
			foreach($array_stock as $k => $v){
				global ${'fonctions_'.$k};
			}
			//gestion des fichiers de class
			foreach($array_stock as $k => $v){
				foreach(${'fonctions_'.$k} as $f){						
					if($stock=='mixed'){
						copy($path.'admin/plugin/'.$_GET['module'].'/fonction/'.$f, $path.'fonction/'.$f);
					}elseif($k==$stock AND !is_file($path.'fonction/'.$f)){				
						copy($path.'admin/plugin/'.$_GET['module'].'/fonction/'.$f, $path.'fonction/'.$f);
					}elseif($k!=$stock AND is_file($path.'fonction/'.$f)){
						unlink($path.'fonction/'.$f);
					}else{
						continue;
					}					
				}			
			}
			//test des moyens de stockage
			foreach(${'fonctions_'.$stock} as $key =>$val){				
				//récupération du nom de la class
				$class=substr($val, 0, -strlen('.class.php'));
				//initialisation de la class
				${$key.'_'.$stock}=new $class();
				//test d'existence et initialisation
				${$key.'_'.$stock}->isset_store();				
			}		
		}
	/**
	 * @fn gestion_store($stock,$old_stock='',$hash,$old_hash='')
   * @brief fonction de gestion des données stockées suivanpour faciliter les migrations d'un stockage a l'autre
   * @global array $array_stock_clone array contenant tous les moyens de stockage sauf mixed
	 * @param string $stock type de stockage en cours d'utilisation
	 * @param string $old_stock ancien type de stockage
	 * @param array $hash array contenant le type de hashage pour chaque stockage possibles actuels
	 * @param mixed $old_hash array contenant le type de hashage pour chaque stockage possibles précédents
	 */
	function gestion_store($stock,$old_stock='',$hash,$old_hash=''){
	  global $array_stock_clone;
		//test du type et de la présence du hash pour ce type de stockage		
		if ($stock!='mixed' AND !array_key_exists('hash_'.$stock, $old_hash)){
			//test l'ancien hash par rapport au nouveau
			if(isset($hash['hash_'.$stock]) AND isset($old_hash['hash_'.$stock]) AND $old_hash['hash_'.$stock]!=$hash['hash_'.$stock]){	
				echo '<div>les fonction de hashage passant de <strong>'.$old_hash['hash_'.$stock].'</strong>  à <strong>'.$hash.'</strong> pour le stockage en <strong>'.$stock .'</strong> le système est de par ce fait inutilisable</div>';
			}
			//initialisation des class avec l'ancien stock
			foreach(${'fonctions_'.$old_stock} as $key =>$val){				
				//migration du stockage des utilisateurs et des administrateurs
				if($key=='user'){			
					//récupération du nom de la class
					$class=substr($val, 0, -strlen('.class.php'));
					//initialisation de la classe avec l'ancien stockage
					$class_old=str_replace($stock, $old_stock, $class);						
					${$key.'_'.$old_stock.'_user'}=new $class_old('user');	
					//récupération de la liste des anciens utilisateurs
					$list_user=${$key.'_'.$old_stock.'_user'}->get_list_users();						
					//migration de la liste des utilisateurs
					${$key.'_'.$stock.'_user'}=new $class('user');	
					//réinitialisation en cas de stockage antérieur
					${$key.'_'.$old_stock.'_user'}->isset_store('1');
					foreach($list_user as $l_u){
						//ajout dans la nouveau moyen de stockage
						${$key.'_'.$stock}->add_user($l_u['id'],$l_u['pseudo'],$l_u['pass'],$l_u['mail'],$l_u['activ'],$l_u['inscription'],$l_u['ban'],$l_u['fin_ban']);
					}
					//initialisation de la classe avec l'ancien stockage
					${$key.'_'.$old_stock.'_user'}=new $class_old('admin');	
					//récupération de la liste des anciens utilisateurs
					$list_admin=${$key.'_'.$old_stock.'_admin'}->get_list_admins();
					//migration de la liste des administrateurs
					${$key.'_'.$old_stock.'_admin'}=new $class('admin');	
					//réinitialisation en cas de stockage antérieur
					${$key.'_'.$old_stock.'_admin'}->isset_store('1');
					foreach($list_admin as $l_a){
						//ajout dans la nouveau moyen de stockage
						${$key.'_'.$stock}->add_admin($l_a);
					}
				}elseif($key=='acces'){
					//récupération du nom de la class
					$class=substr($v, 0, -strlen('.class.php'));
					//initialisation de la classe avec l'ancien stockage
					$class_old=str_replace($stock, $old_stock, $class);		
					${$key.'_'.$old_stock}=new $class_old();	
					//récupération de la liste des pages
					$list_pages=${$key.'_'.$old_stock}->get_list_pages();			
					//récupération de la liste accès
					$list_acces=${$key.'_'.$old_stock}->get_list_acces();		
											//migration du stockage de la gestion des accès
					${$key.'_'.$stock}=new $class();	
					//réinitialisation en cas de stockage antérieur
					${$key.'_'.$stock}->isset_store('1');
					foreach($list_pages as $l_p){
						//ajout dans la nouveau moyen de stockage
						${$key.'_'.$stock}->add_page($l_p['id'],$l_p['nom'],$l_p['niveau']);
					}					
					foreach($list_acces as $l_a){
						//ajout dans la nouveau moyen de stockage
						${$key.'_'.$stock}->add_acces($l_a['id'],$l_a['nom'],$l_a['niveau']);
					}

				}
			}	
			${'fonctions_'.$old_stock}->delete_stock();
		}else{
      $array_light=$array_stock_clone;
      unset($array_light[$stock]);
      foreach($array_light as $k => $v){
				gestion_store($k,$old_stock,$hash,$old_hash);
      }
		}
	}
	/*
	 *enregistrement d'une nouvelle configuration
	 */
	if(isset($_POST['rec'])){
		//initialisation du dossier de stockage
		if(!is_dir($path.'data/xml_axx')){
			mkdir($path.'data/xml_axx');
		}
		//définition du type de stockage
		$stock=(isset($_POST['stock']))?$_POST['stock']:'XML';
		//génération du fichier XML de configuration
		$conf=new gestion_config();
		$conf->add_stock($_POST['stock']);
		if($_POST['stock']=='mixed'){
			foreach($array_stock_clone as $k => $v){
				if(isset($_POST['hash_'.$k])){
					$conf->add_hash($k,$_POST['hash_'.$k]);
					$array_hash_sel[$k]=$_POST['hash_'.$k];
				}else{
					$conf->add_hash($k,$_POST['hash_']);
					$array_hash_sel[$k]=$_POST['hash_'];
				}
			}
		}else{
			$conf->add_hash($_POST['stock'],$_POST['hash_']);
			$array_hash_sel[$_POST['stock']]=$_POST['hash_'];
		}				
		//récupération des hashs
		$hash=$conf->get_hash();
		//gestion des fichiers
		gestion_files($stock);
		//gestion du mot de pass d'administrateur principal
		if(!empty($_POST['mdp_admin_mod']) AND $_POST['reset_mdp_admin']){
			//initialisation de l'objet de gestion des utilisateurs
			$manager_user='gestion_user_manager_'.$_POST['stock'];		
			$admin=new $manager_user('admin');
			//mise a jour du compte administrateur
			$admin->update_admin_principal($_POST['pseudo_admin'],$_POST['mdp_admin_mod'],$array_hash_sel);
		}
	}	
	/*
	 *modification de configuration
	 */
	if(isset($_POST['mod'])){
		//définition du type de stockage
		$stock=(isset($_POST['stock']))?$_POST['stock']:'XML';
		$conf=new gestion_config();
		//récupération de l'ancien type de stockage
		$old_stock=$conf->get_stock();	
		//récupération des anciens hash
		$old_hash=$conf->get_hash();
		//mise à jour du type de stockage
		$conf->modify_stock($stock);
		//suppression des noeuds de hash existants				
		foreach($array_stock_clone as $a_s_c){							
			$conf->delete_hash($a_s_c);
		}
		//création des noeuds de hash
		if($stock=='mixed'){
			foreach($array_stock_clone as $a_s_c){
				if(isset($_POST['hash_'.$a_s_c])){
					$conf->add_hash($a_s_c,$_POST['hash_'.$a_s_c]);
				}else{
					$conf->add_hash($a_s_c,$_POST['hash_']);
				}
			}			
		}else{		
			if($old_type!='mixed'){
				$conf->add_hash($a_s_c,$_POST['hash_'.$old_type]);
			}else{
				foreach($array_stock_clone as $a_s_c){
					if($a_s_c==$stock){
						$conf->add_hash($a_s_c,$_POST['hash_'.$a_s_c]);
						exit;
					}
				}
			}
		}		
		$hash=$conf->get_hash();
		//gestion des fichiers
		gestion_store($stock,$old_stock,$hash,$old_hash);
		gestion_files($stock);
	}	
	/**
	 * 
	 * affichage
	 * 
	 */
	$aff.='
	<form method="post" action="'.$base_url.'admin.php?page_admin=1&module='.$_GET['module'].'&action=conf">
		<h2>configuration de stockage des accès</h2>
		<div>';
		//génération de la liste déroulante déroulante de choix de stockage si une base de données est déclarée
		if($user_db!='' AND $db_name!='' AND $host_db!=''){
		$aff.='<label for="stock">méthode de stockage : </label>
					<select name="stock" id="stock">';			
					foreach($array_stock as $k=>$v){
						$stock_sel=(!empty($stock) AND $stock==$k) ? 'selected="selected"' : '';
						$liste_stock.='<option value="'.$k.'" '.$stock_sel.'>'.$v.'</option>';
					}
					$aff.=$liste_stock;
					$aff.='
					</select>';
			}else{
				$aff.='aucunes base de données n\'as été définit, le stockage se ferat exclusivement en XML';  
			}
		$aff.='</div>
		<h2>configuration du hashage des mot de passe utilisateurs</h2>';
		//génération des options de liste déroulantes
		if($stock=='mixed'){
			foreach($array_stock_clone as $k => $v){
				foreach($array_hash as $a_h){					
					${'hash_sel_'.$k}=(!empty($hash[$k]) AND $hash[$k]==$a_h) ? 'selected="selected"' : '';						
					${'liste_hash_'.$k}.='<option value="'.$a_h.'" '.${'hash_sel_'.$k}.'>'.$a_h.'</option>';			
				}
			}
		}else{		
			foreach($array_hash as $a_h){			
				${'hash_sel_'.$stock}=(!empty($hash[$stock]) AND $hash[$stock]==$a_h) ? 'selected="selected"' : '';						
				${'liste_hash_'.$stock}.='<option value="'.$a_h.'" '.${'hash_sel_'.$stock}.'>'.$a_h.'</option>';			
			}
		}
		$aff.='<div>';	
		//génération des listes déroulantes
		if($stock=='mixed'){
			foreach($array_stock_clone as $k => $v){
				$aff.='<div><label for="hash_'.$k.'">hash pour stockage '.$k.' : </label><select name="hash_'.$k.'" id="hash_'.$k.'">'.${'liste_hash_'.$k}.'</select></div>';
			}
		}else{				
			$aff.='<div><label for="hash_'.$stock.'">hash pour stockage '.$stock.' : </label><select name="hash_'.$stock.'" id="hash_'.$stock.'">'.${'liste_hash_'.$stock}.'</select></div>';	
		}
		$aff.='<div>si vous saisissez le même hash pour le stockage XML et le stockage SQL vous pourrez passez d\'un stockage à l\'autre sans aucuns soucis</div>';
		$aff.=(!empty($stock)) ? '<div><strong>modifier la méthode de hash entrenerat un disfonctionnement avec les comptes déjà enregistrés</strong></div>' : ''; 
		$aff.='</div>';
		//affichage de la mise à jour du mot de passe admin que pendant la configuration 
		if(empty($stock)){
			$aff.='<h2>mettre à jour le hash du mot de passe administrateur principal</h2>';
			$aff.='<h3>la mise à jour de ce mot de passe permettrat de le rendre l\'accès a ce compte compatible avec ce plugin</h3>';
			$aff.='<div>
				       <h4>Faut-il mettre à jour le mot de passe ? </h4>
				       <label for="mdp_admin_1">Oui :</label>
				       <input type="radio" value="1" id="mdp_admin_1" name="reset_mdp_admin" /><br/>
				       <label for="mdp_admin_2">Non :</label>
				       <input type="radio" value="0" id="mdp_admin_2" name="reset_mdp_admin" checked="checked" />
			       </div>';		
			$aff.='<div>pseudonyme administrateur : <input type="text" name="pseudo_admin"/></div>';
			$aff.='<div>mot de passe administrateur : <input type="password" name="mdp_admin_mod"/></div>';
		}
		$aff.=(empty($stock))?'<input type="submit" name="rec" value="enregistrer"/>':'<input type="submit" name="mod" value="modifier"/>'; 
	$aff.='</form>';
	echo $aff;
?>