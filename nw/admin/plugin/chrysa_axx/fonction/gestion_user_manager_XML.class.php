<?php
 /**
	* @class gestion_user_manager_XML.class.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief class de gestion de l'enregistrement, modification et suppression d'utilisateurs en XML
	* @category chrysa_axx
	*/
	class gestion_user_manager_XML{
	 /**
	 * @var object $xml instance de DOMDocument()
	 * @access protected
	 */
		protected $xml;
	 /*
	 * @var string $file_path chemin d'accès absolu au fichier en cours de traitement
	 * @access protected
	 */
		protected $file_path;
	 /**
	 * @var string $file_user_path chemin d'accès relatif aux stockage des utilisateurs
	 * @access protected
	 */
		protected $file_user_path='data/xml_axx/users.xml';
	 /**
	 * @var string $file_admin_path chemin d'accès relatif aux stockage des administrateurs
	 * @access protected
	 */
		protected $file_admin_path='admin/admin.xml';
	 /**
	 * @var string $content_user contenu de base du fichier XML
	 * @access protected
	 */
		protected $content_user='<?xml version="1.0" encoding="utf-8"?><root></root>';
	 /**
	 * @var string $type type de classe permettant de sélectionner le fichier XML
	 * @access protected
	 */
		protected $type;
		//définition des constantes
		const COMPTE_BAN=0;
		const COMPTE_INACTIF=1;
		const COMPTE_INEXISTANT=2;
		const COMPTE_VALID=3;
		const MAIL_INCORRECT=4;
		const MAIL_USE=5;
		const PASS_INCORRECT=6;
		const PSEUDO_USE=7;
		const COMPTE_OK=8;
		/**
		* initialisation de l'objet
		* @access public
		* @param string $type définit le fichier a utiliser 
		*/
		public function __construct($type='user') {
			global $path,$bdd;
			$this->type=$type;
			if($this->type=='user'){
				$this->file_path=$path.$this->file_user_path;
			}elseif($this->type=='admin'){
				$this->file_path=$path.$this->file_admin_path;
			}else{
				die('le paramètre passé a l\'initialisation de l\'objet de gestion des utilisateurs ne correspond à aucuns paramètres référencé');
			}
			$this->bdd=$bdd;
			$this->isset_store();
			$this->xml=new DOMDocument();		
			$this->xml->load($this->file_path);
		}
		/**
		 * ajout d'un nouvelle administrateur
		 * @param array $array 
		 */
		public function add_admin(array $array){
			$new_admin=$this->xml->createElement($array['pseudo']);
			$new_admin->setAttribute('mail',$array['mail']);     
			$new_admin->setAttribute('id',$array['id']);
			$new_admin->setAttribute('date_creation',$array['inscription']);
			$new_admin->appendChild($this->xml->createTextNode($array['pass']));        
			$place_admin=$this->xml->getElementsByTagName('comptes')->item(0);
			$place_admin->appendChild($new_admin); 			
		}
		/**
		 * ajout d'un nouvelle utilisateur
		 * @global objet $this->bdd instance de bdd permettant de généraer l'id si elle n'est pas en paramètre
		 * @param string $id identifiant de l'utilisateur
		 * @param string $pseudo pseudonyme du compte
		 * @param string $mdp mot de passe du compte
		 * @param string $mail adresse mail liée au compte
		 * @param numeric $activ état d'activation du compte
		 * @return boolean
		 */
		public function add_user($id='',$pseudo,$pass='',$mail,$activ='',$inscription='',$ban='',$fin_ban=''){
			if(empty($id)){
				//génération d'un identifiant
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
			$new_user=$this->xml->createElement('user');
			$new_user->setAttribute('id',$id);
			$new_user->setAttribute('pseudo',$pseudo);
			$new_user->setAttribute('pass',$pass);
			$new_user->setAttribute('mail',$this->crypte_mail($mail));
			$new_user->setAttribute('date_inscription',$inscription);
			$new_user->setAttribute('activer',$activ);
			$new_user->setAttribute('ban',$ban);
			$new_user->setAttribute('fin_ban','');
			$place_user=$this->xml->getElementsByTagName('root')->item(0);
			$place_user->appendChild($new_user); 
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
			$choix_admin=$this->xml->getElementsByTagName($pseudo);
			//test de l'existence d'un neud au nom de l'administrateur
			if($choix_admin->length>0){
				foreach($choix_admin as $c_a){		
					//hashing du mot de passe
					$hash_pass=$this->hash_mdp($c_a->getAttribute('id'),$mdp,$c_a->getAttribute('date_creation'));
					//vérification du mot de passe					
					if($hash_pass==$c_a->nodeValue){						
						$_HOOK['id']=$c_a->getAttribute('id');
						$return=self::COMPTE_OK;
						exit;
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
				$choix_user=$init_dom->query("//root/user[@mail='".$this->crypt($input)."']");
			}else{
				$choix_user=$init_dom->query("//root/user[@pseudo='".$input."']");
			}
			if($choix_user->length>0){
				//hashing du mot de passe
				$hash_pass=$this->hash_mdp($c_u->getAttribute('id'),$mdp,$c_u->getAttribute('date_inscription'));
				foreach($choix_user as $c_u){	
					//vérification du mot de passe
					if($hash_pass==$c_u->getAttribute('pass') AND $c_u->getAttribute('activ')==1 AND $c_u->getAttribute('ban')==0){
						$_HOOK['id']=$c_u->getAttribute('id');
						$return=self::COMPTE_OK;
					}elseif($hash_pass!=$c_u->getAttribute('pass')){
						$return=self::PASS_INCORRECT;					
					}elseif($c_u->getAttribute('activ')!=1){
						$return=self::COMPTE_INACTIF;
					}elseif($c_u->getAttribute('ban')!=0){
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
		public function create_store_user(){
			global $path;
			if(file_put_contents($path.$this->file_user_path,$this->content_user)){
				return true;
			}else{
				return false;
			}
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
			$admin=$this->xml->getElementsByTagName($pseudo);
			foreach($admin as $a){
				$a->parentNode->removeChild($a);
			}
		}
		/**
		 * suppression d'un utilisateur
		 * @param type $id 
		 */
		public function delete_user($id){
			$init_dom=new DOMXpath($this->xml);
			$choix_user=$init_dom->query("//root/user[@id='".$id."']");
			foreach($choix_user as $c_u){	
				$c_u->parentNode->removeChild($c_u);
			}
		}
		/**
		 * suppression des modifications apportées par le plugin
		 */
		public function delete_stock(){
			if($this->type=='admin'){
				$list_admin=$this->get_list_admins();
				foreach($list_admin as $l_a){
					$this->modify_admin($l_a['id'],$l_a['pseudo'],$l_a['pass'],$l_a['mail'],'0');
					$admin=$this->xml->getElementsByTagName($l_a['pseudo']);
					foreach($admin as $admin){
						$admin->remove_attribute('date_creation');
					}
				}
			}
			if($this->type=='user'){
				unlink($this->file_path);
			}
		}
		/**
		 * fonction de récupération du timestamp de fin de ban 
     * @access public
  	 * @param string $input contenant soit le pseudo soit l'adresse mail du compte a connecter
		 * @return string
		 */
		public function fin_ban($id){
			$choix_user=$init_dom->query("//root/user[@id='".$id."']");
			foreach($choix_user as $c_u){	
				return $c_u->getAttribute('fin_ban');
			}
		}
		/**
		 * récupération des informations sur tous les administrateurs
		 * @return array $array arrray conrenant la liste des informations des administrateurs
		 */
		public function get_list_admins(){
			$noeud_comptes=$this->xml->getElementsByTagName('comptes');
			$liste_comptes=$noeud_comptes->item(0);
			$comptes=$liste_comptes->childNodes;  
			foreach($comptes as $compte){
				$array[$compte->getAttribute('id')]['id']=$compte->getAttribute('id');
				$array[$compte->getAttribute('id')]['pseudo']=$compte->nodeName;
				$array[$compte->getAttribute('id')]['mail']=$this->decrypte_mail($compte->getAttribute('mail'));
				$array[$compte->getAttribute('id')]['pass']=$compte->nodeValue;
				$array[$compte->getAttribute('id')]['inscription']=$compte->getAttribute('date_creation');
			}
			return $array;
		}
		/**
		 * récupération du pseudo des tous les administrateurs
		 * @return array $array arrray conrenant la liste des pseudos administrateurs
		 */
		public function get_list_admins_name(){
			$noeud_comptes=$this->xml->getElementsByTagName('comptes');
			$liste_comptes=$noeud_comptes->item(0);
			$comptes=$liste_comptes->childNodes;  
			foreach($comptes as $compte){
				$array[]=$compte->nodeName;
			}
			return $array;
		}
		/**
		 * récupération de la liste des utilisateurs et de leurs caractéristiques
		 * @acces public
		 * @return array $array_users
		 */
		public function get_list_users(){
			$liste_users=$this->xml->getElementsByTagName('liste_users');
			$array_users=array();
			//si le noeud contenant la liste des utilisateurs n'existe pas on le cré
			//sinon on parcours le document en sockant dans un array
			if($liste_users->length==0){
				$this->init_users();
			}else{
				$users=$this->xml->getElementsByTagName('user');	
				foreach($users as $user){			
					$array_users[$user->getAttribute('id')]['id']=$user->getAttribute('id');
					$array_users[$user->getAttribute('id')]['pseudo']=$user->getAttribute('pseudo');
					$array_users[$user->getAttribute('id')]['pass']=$user->getAttribute('pass');
					$array_users[$user->getAttribute('id')]['mail']=$this->decrypt($user->getAttribute('mail'));
					$array_users[$user->getAttribute('id')]['inscription']=$user->getAttribute('date_inscription');
					$array_users[$user->getAttribute('id')]['activ']=$user->getAttribute('activer');
					$array_users[$user->getAttribute('id')]['ban']=$user->getAttribute('ban');
					$array_users[$user->getAttribute('id')]['fin_ban']=$user->getAttribute('fin_ban');
				}
			}
			return $array_users;
		}
		/**
		 * récupération des informations d'unadministrateur 
		 * @param string $pseudo pseudo de l'administrateur cible
		 * @return type 
		 */
		public function get_unique_admin($pseudo){
			$liste_admin=$this->xml->getElementsByTagName($pseudo);
			foreach($liste_admin as $admin){
				$array_admin['pseudo']=$pseudo;
				$array_admin['id']=$admin->getAttribute('id');
				$array_admin['mail']=$this->decrypt($admin->getAttribute('mail'));
				$array_admin['inscription']=$admin->getAttribute('date_creation');
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
			$init_dom=new DOMXpath($this->xml);
			$choix_user=$init_dom->query("//root/liste_users/user[@id='".$id."']");
			foreach($choix_user as $c_u){	
				$array_user['id']=$id;
				$array_user['pseudo']=$c_u->getAttribute('pseudo');
				$array_user['pass']=$c_u->getAttribute('pass');
				$array_user['mail']=$this->decrypt($c_u->getAttribute('mail'));
				$array_user['inscription']=$c_u->getAttribute('date_inscription');
				$array_user['activ']=$c_u->getAttribute('activer');
				$array_user['ban']=$c_u->getAttribute('ban');
				$array_user['fin_ban']=$c_u->getAttribute('fin_ban');
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
			return hash($hash['XML'], $id.$mdp.$date_inscription);
  	}
		/**
		 * fonction d'initilisation du noeud utilisateur
		 * @acces public
		 */
		public function init_users(){
			$new_liste=$this->xml->createElement('liste_users');
			$place_liste=$this->xml->getElementsByTagName('root')->item(0);
			$place_liste->appendChild($new_liste);  
		}
		/**
		* test d'existence du fichier de stockage
		* @access public
		* @param numeric $replace définit si le fichier doit être remplacer
		*/
		public function isset_store($replace='0'){
			if(is_file($this->file_path)){
				if($replace!='0'){
					unlink($this->file_path);
					$this->create_store_user();
				}
			}else{
				$this->create_store_user();
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
				$init_dom=new DOMXpath($this->xml);
				//sélection du mail et du pseudo pour vérifier qu'ils sont disponibles
				$choix_pseudo=$init_dom->query("//root/user[@pseudo='".$pseudo."']");
				$choix_mail=$init_dom->query("//root/user[@mail='".$mail."']");       
				if($choix_pseudo->length>0){
					$return.=self::PSEUDO_USE;
				}elseif($choix_mail->length>0){
					$return.=self::MAIL_USE;
				}else{
					$return=self::COMPTE_VALID;
				}
			}else{
				$return=self::MAIL_INCORRECT;
			}
			return $return;
		}
		/**
		 * modification d'un administrateur
		 * @param string $id identifiant de l'administrateur
		 * @param string $pseudo pseudo de l'administrateur
		 * @param string $pass mot de passe de l'administrateur
		 * @param string $mail adresse mail de l'administrateur
		 * @param numeric $del définit si il existe un compte avec le même identifiant qui doit être supprimé suite a un changement de pseudo
		 */
		public function modify_admin($id,$pseudo,$pass,$mail,$del=''){
			if($del==1){
				//suppression de l'ancien compte
				$liste=$this->get_list_admin();
				foreach($liste as $l){
					$admin=$this->xml->getElementsByTagName($l);
					foreach($admin as $a){
						if($a->getAttribute('id')==$id){
							$date_inscription=$a->getAttribute('date_creation');
							$a->parentNode->removeChild($a);
							break;
						}
					}
				}
				//création du nouveau compte
				$new_admin=$this->xml->createElement($pseudo);
				$new_admin->setAttribute('mail',$this->crypte_mail($mail));    
				$new_admin->setAttribute('mail',$this->crypte_mail($mail));     
				$new_admin->setAttribute('id',$id);
				$new_admin->setAttribute('date_creation',time());
				$pass=$this->hash_mdp($id,$pass,$date_inscription);   
				$new_admin->createTextNode($pass); 
				$place_admin=$this->xml->getElementsByTagName('comptes')->item(0);
				$place_admin->appendChild($new_admin);
			}else{	
				//mise à jour du compte
				$admin=$this->xml->getElementsByTagName($pseudo);
				foreach($admin as $admin){
					$admin->setAttribute('mail',$this->crypte_mail($admin['mail']));    
					if(!empty($pass)){
						$sel_admin=get_unique_admin($pseudo);
						$pass=$this->hash_mdp($id,$pass,$sel_admin['inscription']);   
						$admin->createTextNode($pass); 
					}           
				}			
			}
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
			$init_dom=new DOMXpath($this->xml);
			$choix_user=$init_dom->query("//root/user[@id='".$id."']");
			foreach($choix_user as $c_u){	
				$c_u->setAttribute('pseudo',$pseudo);
				if(!empty($pass)){
					$pass=$this->hash_mdp($id,$mdp,$c_u->getAttribute('date_inscription'));                    
					$c_u->setAttribute('pass',$pass);
				}
				$c_u->setAttribute('mail',$this->crypte_mail($mail));
				$c_u->setAttribute('activer',$activ);
				$c_u->setAttribute('ban',$ban);
				if($ban==0){
					$fin_ban='';
				}
				$c_u->setAttribute('fin_ban',$fin_ban);
			}
		}	
		/**
		 * mise à jour du compte du premier administrateur pour le rendre compatible avec le plugin
		 * @param string $pseudo pseudo de l'administrateur principal
		 * @param string $mdp mot de passe a mettre a jour
		 * @return boolean 
		 */
		public function update_admin_principal($pseudo,$mdp,$hash){
			$choix_admin=$this->xml->getElementsByTagName($pseudo);
			if($choix_admin->length==0){
				return false;
			}else{	
				foreach($choix_admin as $c_a){
					$id=$c_a->getAttribute('id');
					$mail=$c_a->getAttribute('mail');
				}
				$this->delete_admin($pseudo);
				$date_inscription=time();
				$this->add_admin(array('id'=>$id,'pseudo'=>$pseudo,'mail'=>$this->crypte_mail($mail),'inscription'=>$date_inscription,'pass'=>$this->hash_mdp($id,$mdp,$date_inscription,$hash)));
			}
		}
		/**
		* @access public
		*/
		public function __destruct(){ 		
			$this->xml->save($this->file_path);
		}
	}										
?>