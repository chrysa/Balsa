<?php
 /**
	* @class gestion_user_manager_SQL.class.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief class de gestion de l'enregistrement, modification et suppression d'utilisateurs en SQL
	* @category chrysa_axx
	*/
	class gestion_user_manager_SQL{
  	/**
	 	* @acces protected
		* @var objet $bdd contient l'instance de la base de données primaire
		*/
		protected $bdd;
	 /**
	 * @acces protected
	 * @var string $db_name nom de la base de donnée principale
	 */
		protected $db_name;
	 /**
	 * @acces protected
	 * @var string $table nom de la table en cours d'utilisations
	 */
		protected $table;
	  /**
	 * @var string $table_user nom de la table de stockage des utilisateurs
	 * @access protected
	 */
		protected $table_user='user';
	  /**
	 * @var string $table_admin nom de la table de stockage des administrateurs
	 * @access protected
	 */
		protected $table_admin='admin';
		/**
		 * @acces protected
		 * @var string $structure_table_admin structure de la table de stockage
		 */
		protected $structure_table_user='(`id` VARCHAR(20) NOT NULL,`pseudo` VARCHAR(128) NOT NULL,`pass` VARCHAR(128) NOT NULL,`mail` VARCHAR(128) NOT NULL,`date_inscription` int(11) NOT NULL,`activer` enum(\'0\',\'1\') DEFAULT \'1\',`ban` enum(\'0\',\'1\') DEFAULT \'0\',`fin_ban` int(11) NOT NULL) ENGINE = MYISAM ;';				
		/**
		 * @acces protected
		 * @var string $structure_table_admin champs a ajouter à la table admin
		 */
		protected $array_modif_table_admin=array('date_inscription'=>array('type'=>'int(11)','default'=>'NOT NULL'));
		//définition des constantes
		const COMPTE_BAN=0;
		const COMPTE_INACTIF=1;
		const COMPTE_INEXISTANT=2;
		const COMPTE_VALID=3;
		const MAIL_INCORRECT=4;
		const MAIL_USE=5;
		const PASS_INCORRECT=6;
		const PSEUDO_USE=7;
		/**
		* initialisation de l'objet
		* @access public
		*/
		public function __construct($type='user'){
			global $bdd,$db_name;
			$this->bdd=$bdd;
			$this->db_name=$db_name;	
			if($type=='user'){
				$this->table=$this->table_user;
			}elseif($type=='admin'){
				$this->table=$this->table_admin;
			}else{
				die('le paramètre passé a l\'initialisation de l\'objet de gestion des utilisateurs ne correspond à aucuns paramètres référencé');
			}
		}
		/**
		 * ajout d'un nouvelle administrateur
		 * @param array $array 
		 */
		public function add_admin(array $array,$rinit=''){
			if($rinit==1){
				$this->bdd->query2('INSERT INTO '.$this->table.'(`id`,`login`,`mail`,`pass`) VALUES (\''.$array['id'].'\',\''.$array['pseudo'].'\',\''.$array['mail'].'\',\''.$array['pass'].'\')');						
			}else{
				$this->bdd->query2('INSERT INTO '.$this->table.'(`id`,`login`,`mail`,`pass`,`date_inscription`) VALUES (\''.$array['id'].'\',\''.$array['pseudo'].'\',\''.$array['mail'].'\',\''.$array['pass'].'\',\''.$array['inscription'].'\')');		
			}
		}
		/**
		 * ajout d'un nouvelle utilisateur
		 * @global objet $bdd instance de bdd permettant de généraer l'id si elle n'est pas en paramètre
		 * @param string $id identifiant de l'utilisateur
		 * @param string $pseudo pseudonyme du compte
		 * @param string $mdp mot de passe du compte
		 * @param string $mail adresse mail liée au compte
		 * @return boolean
		 */
		public function add_user($id='',$pseudo,$pass='',$mail,$activ='',$inscription='',$ban='',$fin_ban=''){
			if(empty($id)){
				//génération d'un identifiant
				global $bdd;
				$id=$this->bdd->get_primkey();
			}
			if(empty($inscription)){
				//récupération du timestamp
				$inscription=time();
				//hashage du mot de passe
				$pass=$this->hash_mdp($id,$pass,$inscription);
			}
			//création du noeud
			if(empty($activ)){
				$activ='1';
			}
			if(empty($ban)){
				$ban='0';
			}
			$mail=$this->crypte_mail($mail);
			$user=$this->bdd->prepare("INSERT INTO '.$this->table.'(id,pseudo,pass,mail,date_inscription,activer,ban,fin_ban) VALUES (:id,:pseudo,:pass,:mail,:date_inscription,:activ,:ban,:fin_ban)");
			$user->bindValue(':id', $id, PDO::PARAM_STR);
			$user->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
			$user->bindValue(':pass', $pass, PDO::PARAM_STR);
			$user->bindValue(':mail', $mail, PDO::PARAM_STR);
			$user->bindValue(':date_inscription', $inscription, PDO::PARAM_STR);
			$user->bindValue(':activ', $activ, PDO::PARAM_STR);
			$user->bindValue(':ban', $ban, PDO::PARAM_STR);
			$user->bindValue(':fin_ban', $fin_ban, PDO::PARAM_STR);
			$user->execute();
		}
		/**
		* connection d'un compte administrateur
		* @access public
 	  * @param string $pseudo pseudo de l'administrateur à connecter
 	  * @param string $mdp mot de passe a tester
		* @return mixed
		*/
		public function connect_admin($pseudo,$mdp){
			global $_HOOK;		
			$admin=$this->bdd->prepare("SELECT id,login,pass FROM '.$this->table.' WHERE login=:pseudo");
			$admin->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
			$admin->execute();
			$a=$admin->fetchAll();
			if(is_array($a)){		
				//hashing du mot de passe
				$pass=$this->hash_mdp($admin['id'],$mdp,$a['date_inscription']);
				//vérification du mot de passe
				if($pass===$a['pass']){					
					$_HOOK['id']=$admin['id'];
					$return=self::COMPTE_OK;
					exit;
				}elseif(!isset($return) OR empty($return)){
					$return=self::PASS_INCORRECT;
				}else{		
					$return=self::COMPTE_INEXISTANT;
				}
			}
			return $return;
		}
		/**
		* connection d'un compte utilisateur
		* @access public
 	  * @param string $input contenant soit le pseudo soit l'adresse mail du compte a connecter
 	  * @param string $mdp mot de passe du compte a tester
		* @return mixed
		*/
		public function connect_user($input,$mdp){		
			global $_HOOK;
			//test si l'input est une adresse mail ou pas pour lancer la recherche du noeud correspondant
			if(valid_input($input,array('mail'))){
				$user=$this->bdd->prepare("SELECT * FROM '.$this->table.' WHERE mail=:input");
			}else{
				$user=$this->bdd->prepare("SELECT * FROM '.$this->table.' WHERE pseudo=:input");
			}
			$user->bindValue(':input', $input, PDO::PARAM_STR);
			$user->execute();
			$u=$user->fetchAll();
			if(is_array($u)){
				//hashage du mot de passe
				$pass=$this->hash_mdp($u['id'],$mdp,$u['date_inscription']);	//vérification du mot de passe
				if($pass==$u['pass'] AND $u['activ']==1 AND $u['ban']==0){
					$_HOOK['id']=$u['id'];
					$return=self::COMPTE_OK;
				}else{
					if($pass!=$u['pass']){
						$return=self::PASS_INCORRECT;					
					}elseif($u['activ']!=1){
						$return=self::COMPTE_INACTIF;
					}elseif($u['ban']!=0){
						$return=self::COMPTE_BAN;
					}
				}
				if(!isset($return) OR empty($return)){
					$return=self::PASS_INCORRECT;
				}
			}else{
				$return=self::COMPTE_INEXISTANT;
			}
			return $return;
		}
		/**
		* création du fichier de stockage
		* @access public
		* @return boolean
		*/
		public function create_store(){
			if($this->init_user() AND $this->modify_table_admin()){
				return true;
			}else{
				return false;
			}
		}
		/**
		* fonction d'e vérification de la validité d'un utilisateur avant de l'enregistrere création de la table utilisateurs
		* @param string $pseudo pseudo a tester
		* @param string $mail adresse mail a tester
		* @return mixed $return 
		*/		
		public function create_user_store(){	
			$this->bdd->query2('CREATE TABLE `'.$this->db_name.'`.`'.$this->table_user.'` '.$this->structure_table_user);
		}		
		/**
		* fonction de cryptage des adresses mails
		* @param string $mail adresse mail a crypter
		* @return string $crypt
		*/
		public function crypte_mail($mail){
			//génération de l'array d'indexage de l'alphabet
			$alphabet=range('a','z');
			//intervertion des clés et des valeurs
			$alphabet_reverse=array_flip($alphabet);
			//extration de l'extension
			$ext=explode('.',$mail);
			//extraction du nom d'utilisateur et du nom de domaine
			$user=str_replace('.'.$ext[1],'',$mail);
			//séparation du nom d'utilisateur et du nom de domaine
			$user_explode=explode('@',$user);
			//calcul des longueurs de chaines
			$length_user=strlen($user_explode[0]);
			$length_ndd=strlen($user_explode[1]);
			$length_ext=strlen($ext[1]);
			//initialisation du cryptage et stockage des logueure de chaines
			$crypt=str_pad($length_user,3,"0", STR_PAD_LEFT).str_pad($length_ndd,3,"0", STR_PAD_LEFT).str_pad( $length_ext,3,"0", STR_PAD_LEFT);
			//encodage de l'adresse mail
			$array=array('user'=>$user_explode[0],'ndd'=>$user_explode[1],'ext'=>$ext[1]);
			foreach($array as $a){
				for($i=0,$i_max=strlen($a);$i<$i_max;$i++){ 
					//récupértion de la place de la lettre
					//test de la casse		
					if(in_array($a[$i],$alphabet) AND $a[$i]==strtolower($a[$i])){			
						$place_lettre=$alphabet_reverse[$a[$i]];	
						$crypt.='0';			
						if($place_lettre<10){
							$crypt.='0';
						}
						$crypt.=$place_lettre;
					}elseif(in_array(strtolower($a[$i]),$alphabet) AND $a[$i]==strtoupper(strtolower($a[$i]))){
						$place_lettre=$alphabet_reverse[strtolower($a[$i])];	
						$crypt.='1';
						if($place_lettre<10){
							$crypt.='0';
						}
						$crypt.=$place_lettre;
					}else{
						$crypt.='20'.$a[$i];
					}
				} 
			}
			//encodage
			$crypt=convert_uuencode($crypt);
			//renverssement de la chaine
			$crypt=strrev($crypt);
			return $crypt;
		}
		/**
		* fonction de décryptage des adresses mails
		* @param string $crypt hash à décrypter
		* @return string $mail
		*/
		public function decrypte_mail($crypt){
			//renverssement de la chaine
			$crypt=strrev($crypt);
			//décodage de la chaine
			$crypt=convert_uudecode($crypt);
			//récupération des longueurs des composants d'origines
			$length_user=substr($crypt,0,3);
			$length_ndd=substr($crypt,3,3);
			//récupération du hash d'adresse mail
			$hash_adr=substr($crypt,-(strlen($crypt)-9));
			//récupération des longueures des composants cryptés
			$length_user_crypt=$length_user*3;
			$length_ndd_crypt=$length_ndd*3;
			//génération de l'array d'indexage de l'alphabet
			$alphabet=range('a','z');
			//décocage des composants
			$plop=str_split($hash_adr, 3);
			foreach($plop as $k=>$v){
				$casse=substr($v,0,1);	
				$index=(substr($v,1,1)==0)?substr($v,2,1):substr($v,1,2);
				switch($casse){
					case '0':
						$mail.=$alphabet[$index];
						break;
					case '1':
						$mail.=strtoupper($alphabet[$index]);
						break;
					case '2':
						$mail.=substr($v,2,1);
						break;
				}
				if((($k+1)*3)==$length_user_crypt){
					$mail.='@';
				}elseif((($k+1)*3)==$length_user_crypt+$length_ndd_crypt){		
					$mail.='.';
				}else{
					continue;
				}	
			}
			return $mail;
		}
		/**
		 *suppresion d'un administrateur
		 * @param string $pseudo administrateur cible
		 */
		public function delete_admin($pseudo){
			$requete=$this->bdd->prepare("DELETE FROM ".$this->table." WHERE login=:pseudo");
			$requete->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
			$requete->execute();
		}
		/**
		 * suppression des modifications apportées par le plugin
		 */
		public function delete_stock(){	
			$list_admins=$this->get_list_admins();
			foreach($list_admins as $l_a){
				$this->modify_admin($l_a['id'], $l_a['pseudo'], $l_a['pass'], $l_a['mail']);
			}
			foreach($this->array_modif_table_admin as $name=>$cara){
				$this->bdd->query2('ALTER TABLE '.$this->table_admin.' DROP COLUMN '.$name);
			}	
			$this->bdd->query2('DROP TABLE `'.$this->db_name.'`.`'.$this->table_user.'`');			
		}				
		/**
		 * suppression d'un utilisateur
		 * @param type $id 
		 */
		public function delete_user($id){
			$requete=$this->bdd->prepare("DELETE FROM ".$this->table." WHERE id=:id");
			$requete->bindValue(':id', $id, PDO::PARAM_STR);
			$requete->execute();
		}
		/**
		 * fonction de récupération du timestamp de fin de ban 
     * @access public
  	 * @param string $input contenant soit le pseudo soit l'adresse mail du compte a connecter
		 * @return string
		 */
		public function fin_ban($id){
			$choix_user=$this->bdd->query2("SELECT id,fin_ban FROM ".$this->table." WHERE id=".$id."");
			$c_u=$choix_user->fetchAll();
			foreach($c_u as $c_u){	
				return $c_u['fin_ban'];
			}
		}
		/**
		 * récupération des informations sur tous les administrateurs
		 * @return array $array arrray conrenant la liste des informations des administrateurs
		 */
		public function get_list_admins(){
			$req=$this->bdd->query2("SELECT * FROM ".$this->table);	
			$comptes=$req->fetchALL(PDO::FETCH_ASSOC);
			foreach($comptes as $compte){
				$array[$compte['id']]['id']=$compte['id'];
				$array[$compte['id']]['pseudo']=$compte['login'];
				$array[$compte['id']]['mail']=$this->decrypte_mail($compte['mail']);
				$array[$compte['id']]['pass']=$compte['pass'];
				$array[$compte['id']]['inscription']=$compte['date_inscription'];
			}
			return $array;
		}
		/**
		 * récupération du pseudo des tous les administrateurs
		 * @return array $array arrray conrenant la liste des pseudos administrateurs
		 */
		public function get_list_admins_name(){
			return $this->bdd->query2("SELECT login FROM ".$this->table);	
		}
		/**
		 * récupération de la liste des utilisateurs et de leurs caractéristiques
		 * @acces public
		 * @return array $array_users
		 */
		public function get_list_users(){
			$users=$this->bdd->query2("SELECT id,pseudo,mail,date_inscription,activer,ban,fin_ban FROM ".$this->table);	
			foreach($users as $user){			
				$array_users[$user['id']]['id']=$user['id'];
				$array_users[$user['id']]['pseudo']=$user['pseudo'];
				$array_users[$user['id']]['pass']=$user['pass'];
				$array_users[$user['id']]['mail']=$this->decrypte_mail($user['mail']);
				$array_users[$user['id']]['inscription']=$user['date_inscription'];
				$array_users[$user['id']]['activ']=$user['activer'];
				$array_users[$user['id']]['ban']=$user['ban'];
				$array_users[$user['id']]['fin_ban']=$user['fin_ban'];
			}
			return $array_users;
		}
		/**
		 * récupération des informations d'unadministrateur 
		 * @param string $pseudo pseudo de l'administrateur cible
		 * @return type 
		 */
		public function get_unique_admin($pseudo){
			$admin=$this->bdd->prepare("SELECT id,login,mail,date_creation FROM '.$this->table.' WHERE login=:pseudo");
			$admin->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
			$admin->execute();
			$a=$admin->fetchAll();
			foreach($a as $a){
				$array_admin['pseudo']=$pseudo;
				$array_admin['id']=$an['id'];
				$array_admin['mail']=$this->decrypte_mail($admin['mail']);
				$array_admin['inscription']=$a['date_creation'];
			}
			return $array_admin;
		}
		/**
		 * fonction de récupérationdes information d'un utilisateur donné
		 * @acces public
		 * @param string $id identifiant de l'utilisateur cible
		 * @return array $array_user
		 */
		public function get_unique_user($id){
			$user=$this->bdd->prepare("SELECT * FROM '.$this->table.' WHERE id=:id");
			$user->bindValue(':id', $id, PDO::PARAM_STR);
			$user->execute();
			$u=$user->fetchAll();
			foreach($u as $u){	
				$array_user['id']=$id;
				$array_user['pseudo']=$u['pseudo'];
				$array_user['pass']=$u['pass'];
				$array_user['mail']=$this->decrypte_mail($u['mail']);
				$array_user['inscription']=$u['date_inscription'];
				$array_user['activ']=$u['activer'];
				$array_user['ban']=$u['ban'];
				$array_user['fin_ban']=$u['fin_ban'];
			}
			return $array_user;
		}
		/**
		* hashage du mot de passe du compte
		* @access public 
 	  * @global array $hash array contenant la méthode de hash 
		* @praram string $id identifiant du compte cible
		* @praram string $mdp mot de passe a hasher
		* @praram numeric $date_inscription timestamp d'inscription
		* @return string 
		*/
		public function hash_mdp($id,$mdp,$date_inscription,$hash_sel=''){
			if(empty($hash_sel)){
				global $hash;		
			}else{
				$hash=$hash_sel;
			}
			return hash($hash['SQL'], $id.$mdp.$date_inscription);
  	}
		/**
		* test d'existence du fichier de stockage
		* @access public
		* @param numeric $replace définit si le fichier doit être remplacer
		*/
		public function isset_store($replace='0'){
			//teste de la table de gestion des accès
			$requete_user=$this->bdd->query2('SHOW TABLES FROM '.$this->db_name.' LIKE \''.$this->table_user.'\'');
			$requete=$requete_user->fetchAll();
			if(count($requete)>0){
				 //si la page doit être remplacée
				if($replace!='0'){
					$this->bdd->query2('DROP TABLE `'.$this->db_name.'`.`'.$this->table_user.'`');
					$this->create_user_store();
				}
			}else{
				$this->create_user_store();
			}
			//test de la table de gestion des pages
			$requete_admin=$this->bdd->query2('SHOW TABLES FROM '.$this->db_name.' LIKE \''.$this->table_admin.'\'');
			$requete=$requete_admin->fetchAll();	
			if(count($requete)>0){
				 //si la page doit être remplacée
				if($replace!='0'){
					$this->bdd->query2('DROP TABLE `'.$this->db_name.'`.`'.$this->table_admin.'`');
					$this->bdd->creat_db_Balsa($this->db_name);
					$this->modify_table_admin();
				}else{
					$requete=$this->bdd->query2('DESCRIBE `'.$this->db_name.'`.`'.$this->table_admin.'`');
					$req=$requete->fetchAll();
					foreach($req as $r){
						$array_champ[]=$r['Field'];
					}		
					foreach($this->array_modif_table_admin as $k=>$v){
						if(!in_array($k,$array_champ)){
									$modif.='`'.$k.'` '.$v['type'].' '.$v['default'];	
							}
						}
						$this->bdd->query2('ALTER TABLE '.$this->table_admin.' ADD '.$modif.' AFTER pass');				
				}
			}else{
				$this->bdd->creat_db_Balsa($this->db_name);
				$this->modify_table_admin();
			}
		}
		/**
		 * fonction de vérification de la validité d'un utilisateur avant de l'enregistrer
		 * @param string $pseudo pseudo a tester
		 * @param string $mail adresse mail a tester
		 * @return mixed $return 
		 */
		protected function is_valid_user($pseudo,$mail){
			//test de validité de l'adresse mail
			if(valid_input($mail,array('mail'))){
				$crypt_mail=$this->crypte_mail($mail);
				$user=$this->bdd->prepare("SELECT count(*) as nbr FROM '.$this->table.' WHERE pseudo=:pseudo AND mail=:mail");
				$user->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
				$user->bindValue(':mail', $crypt_mail, PDO::PARAM_STR);
				$user->execute();
				$u=$user->fetchAll();
				if($u['nbr']==0){
					$return=self::COMPTE_VALID;
				}elseif($u['pseudo']==$pseudo){
					$return=self::PSEUDO_USE;
				}elseif($u['mail']==$crypt_mail){
					$return=self::MAIL_USE;
				}
			}else{
				$return=self::MAIL_INCORRECT;
			}
			return $return;
		}
		/**
		 * modification de la table de stockage des administrateurs
		 * @acces public
		 */
		public function modify_table_admin(){
			foreach($this->array_modif_table_admin as $name=>$cara){
				$modif.=$name.' '.$cara['type'].' '.$cara['default'];	
			}
			$this->bdd->query2('ALTER TABLE '.$this->table_admin.' ADD '.$modif.' AFTER pass');				
		}
		/**
		 * modification d'un administrateur
		 * @param string $id identifiant de l'administrateur
		 * @param string $pseudo pseudo de l'administrateur
		 * @param string $pass mot de passe de l'administrateur
		 * @param string $mail adresse mail de l'administrateur
		 * @param numeric $del util que dans le cas du stockage XML mais présent pour le stockage mixte
		 */
		public function modify_admin($id,$pseudo,$pass,$mail,$del=''){
			$user=$this->bdd->prepare("SELECT id,date_inscription FROM '.$this->table.' WHERE id=:id");
			$user->bindValue(':id', $id, PDO::PARAM_STR);
			$user->execute();
			$u=$user->fetchAll(PDO::FETCH_ASSOC);
			foreach($u as $u){		
				$id=$u['id'];
				$pass=$this->hash_mdp($id,$pass,$u['date_inscription']);
			}
			$mail=$this->crypte_mail($mail);
			$requete=$this->bdd->prepare("UPDATE ".$this->table." SET login=:pseudo, pass=:pass, mail=:mail WHERE id=:id");
			$requete->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
			$requete->bindValue(':pass', $pass, PDO::PARAM_STR);
			$requete->bindValue(':mail', $mail, PDO::PARAM_STR);
			$requete->bindValue(':id', $id, PDO::PARAM_STR);
			$requete->execute();	
		}	
		/**
		 * modification d'un utilisateur
  	 * @access public 
		 * @param string $id identifiant de l'utilisateur		 
		 * @param string $pseudo pseudo de l'utilisateur
		 * @param string $pass mot de passe
		 * @param string $mail adresse amil
		 * @param string $activ état d'activation du compte
		 * @param string $ban
		 * @param string $fin_ban 
		 */
		public function modify_user($id,$pseudo,$pass,$mail,$activ,$ban,$fin_ban){
			$user=$this->bdd->prepare("SELECT * FROM '.$this->table.' WHERE id=:id");
			$user->bindValue(':id', $id, PDO::PARAM_STR);
			$user->execute();
			$u=$user->fetchAll();
			foreach($u as $u){	
				$old_pass=$u['pass'];
				$date_inscription=$u['date_inscription'];	
			}			
			if(!empty($pass)){
				$mdp=$this->hash_mdp($id,$pass,$date_inscription);       
			}else{
				$mdp=$old_pass;
			}
			$mail=$this->crypte_mail($mail);
			if($ban==0){
				$fin_ban='';
			}
			$requete=$this->bdd->prepare("UPDATE ".$this->table." SET pseudo=:pseudo,pass=:pass,mail=:mail,activer=:activ,ban=:ban,fin_ban=:fin_ban WHERE id=:id");
			$requete->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
			$requete->bindValue(':pass', $mdp, PDO::PARAM_STR);
			$requete->bindValue(':mail', $mail, PDO::PARAM_STR);
			$requete->bindValue(':activ', $activ, PDO::PARAM_STR);
			$requete->bindValue(':ban', $ban, PDO::PARAM_STR);
			$requete->bindValue(':fin_ban', $fin_ban, PDO::PARAM_STR);
			$requete->execute();	
		}	
		/**
		 * mise à jour du compte du premier administrateur pour le rendre compatible avec le plugin
		 * @param string $pseudo pseudo de l'administrateur principal
		 * @param string $mdp mot de passe a mettre a jour
		 * @return boolean 
		 */
		public function update_admin_principal($pseudo,$mdp,$hash){
			$admin=$this->bdd->prepare('SELECT id,login,mail FROM '.$this->table.' WHERE login=:pseudo');
			$admin->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
			$admin->execute();
			$a=$admin->fetchAll();
			foreach($a as $a){
				$id=$a['id'];
				$mail=$a['mail'];
			}			
			$this->delete_admin($pseudo);
			$date_inscription=time();
			$this->add_admin(array('id'=>$id,'pseudo'=>$pseudo,'mail'=>$this->crypte_mail($mail),'inscription'=>$date_inscription,'pass'=>$this->hash_mdp($id,$mdp,$date_inscription,$hash))); 
		}
		/**
		* @access public
		*/
		public function __destruct(){
			$this->bdd->closeCursor();
		}
	}										
?>