<?php
	/**
	* @class gestion_acces_manager_SQL.class.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief class de gestion de l'enregistrement, modification et suppression de access en SQL
	* @category chrysa_axx 
	*/
	class gestion_acces_manager_SQL{
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
		 * @var string $table_acces nom de la table de gestion des accès
		 */
		protected $table_acces='acces';
		/**
		 * @acces protected
		 * @var string $table_pages nom de la table de gestion des pages
		 */
		protected $table_pages='pages';
		/**
		 * @acces protected
		 * @var string $structure_table structure de la table de stockage
		 */
		protected $structure_table_acces='(`id` VARCHAR(20) NOT NULL,`name` VARCHAR(128) NOT NULL,`niveau` INT(10) NOT NULL,`membres` TEXT NOT NULL) ENGINE = MYISAM ;';		
		/**
		 * @acces protected
		 * @var string $structure_table structure de la table de stockage
		 */
		protected $structure_table_pages='(`id` VARCHAR(20) NOT NULL,`name` VARCHAR(128) NOT NULL,`acces` INT(10) NOT NULL) ENGINE = MYISAM ;';		
		/**
		* initialisation de l'objet
		* @access public
		*/
		public function __construct(){
			global $bdd,$db_name;
			$this->bdd=$bdd;
			$this->db_name=$db_name;			
		}
		/**
		* ajout d'un acces
		* @access public
		* @param gestion_acces $name 
		*/
		public function add_acces($id='',$name,$niveau){		
			//récupération d'un identifiant
			if(empty($id)){
	  		$id=$this->bdd->get_primkey();
			}
			$requete=$this->bdd->prepare("INSERT INTO ".$this->table_acces." (id,name,niveau,membres) VALUES (:id,:name,:niveau,'')");
			$requete->bindValue(':id', $id, PDO::PARAM_STR);
			$requete->bindValue(':name', $name, PDO::PARAM_STR);
			$requete->bindValue(':niveau', $niveau, PDO::PARAM_STR);
			$requete->execute();
		}
		/**
		* ajout du niveau d'accès à une page
		* @access public
		* @param string $id identifiant de la page
		* @param string $acces niveau d'accès demandé pour la page  
		*/
		public function add_acces_page($id='',$name,$niveau){		
			//récupération d'un identifiant
			if(empty($id)){
    		$id=$this->bdd->get_primkey();
    	}
			$requete=$this->bdd->prepare("UPDATE ".$this->table_pages." SET acces=:acces WHERE id=:id");
			$requete->bindValue(':id', $id, PDO::PARAM_STR);
			$requete->bindValue(':acces', $acces, PDO::PARAM_INT);
			$requete->execute();
		}
		/**
		* ajout d'une page
		* @access public
		* @param string $page nom de la page a ajouter
		*/
		public function add_page($id='',$page,$acces=''){ 			
			//récupération d'un identifiant
			if(empty($id)){
    		$id=$this->bdd->get_primkey();
    	}
			//récupération du nom de la page
			if(strcasecmp(substr($page,-4),'.php')==0){
				$page=substr($page,0,-4);
			}elseif(strcasecmp(substr($page,-5),'.html')==0){
				$page=substr($page,0,-5);
			}			
			//exécution de la requète
			$requete=$this->bdd->query2("INSERT INTO ".$this->table_pages." (`id`,`name`,`acces`) VALUES ('".$id."','".$page."','".$acces."')");
		}		
    /**
		 * fonction d'assignation d'un membre à un groupe
		 * @param string $id identifiant de l'utilisateur
		 * @param string $group identifiant du groupe 
		 */
    public function add_to_group($id,$groupe){
			$req=$this->bdd->prepare("SELECT membres FROM ".$this->table_acces." WHERE id=:groupe");
			$req->bindValue(':groupe', $groupe, PDO::PARAM_STR);
			$req->execute();
			$requete=$req->fetchAll();	
			foreach($requete as $membre){
				if(!empty($m)){
					$new_membres=$membre.'|'.$id;
				}else{
					$new_membres=$id;
				}
			}
			$this->bdd->query2('UPDATE '.$this->table_acces.' SET membres='.$new_membres .' WHERE id='.$groupe)->fetchAll();	
    }
		/**
		* création de la table de stockage
		* @access public
		* @return boolean
		*/
		public function create_store($type){
			switch ($type){
				case 'acces':					
					$name_table=$this->table_acces;
					$structure=$this->structure_table_acces;
					break;
				case 'pages':
					$name_table=$this->table_pages;
					$structure=$this->structure_table_pages;
					break;
			}
			$this->bdd->query2('CREATE TABLE `'.$this->db_name.'`.`'.$name_table.'` '.$structure);
			switch ($type){
				case 'acces':					
					$this->init_acces();
					break;
				case 'pages':
					$this->init_pages();
					break;
			}
		}
		/**
		* suppression d'un acces
		* @access public
		* @param string $id identifiant de l'accès cible
		*/
		public function delete_acces($id){
			$requete=$this->bdd->prepare("DELETE FROM ".$this->table_acces." WHERE id=:id");
			$requete->bindValue(':id', $id, PDO::PARAM_STR);
			$requete->execute();
		}
		/**
		* suppression d'une page
		* @access public
		* @param string $id identifiant de la page 
		*/
		public function delete_page($id){
			$requete=$this->bdd->prepare("DELETE FROM ".$this->table_pages." WHERE id=:id");
			$requete->bindValue(':id', $id, PDO::PARAM_STR);
			$requete->execute();
		}
		/**
		 * suppression des modifications apportées par le plugin
		 */
		public function delete_stock(){
			$this->bdd->query2('DROP TABLE `'.$this->db_name.'`.`'.$this->table_acces.'`');
			$this->bdd->query2('DROP TABLE `'.$this->db_name.'`.`'.$this->table_pages.'`');
		}		
		/**
		 * récupération du niveau d'accès de la page
     * @access public
		 * @param string $page nom de la page cible
		 * @return mixed
		 */
		public function get_acces_page($page){
			$req=$this->bdd->prepare("SELECT acces FROM ".$this->table_pages." WHERE name=:page");
			$req->bindValue(':page', $page, PDO::PARAM_STR);
			$req->execute();
		  $requete=$req->fetchAll();
			return $requete;			
		}
		/**
		* récupérer la liste des access
		* @access public
		* @return array $array_acces array contenant toutes les caractéristiques des niveaux d'accès 
		*/
		public function get_list_acces(){
			$list_acces=$this->bdd->query2('SELECT * FROM '.$this->table_acces);
			$list=$list_acces->fetchAll();	
			return $list;
		}
		/**
		 * récupération de la liste des identifiants des membres d'un groupe
		 * @param string $id identifiant du groupe
		 * @return array $array_mbr
		 */
    public function get_list_membre($id){
			$req=$this->bdd->prepare("SELECT * FROM ".$this->table_acces." >HERE id=:id");
			$req->bindValue(':id', $id, PDO::PARAM_STR);
			$req->execute();
		  $membres=$req->fetchAll();
			return $membres;	
    } 
		/**
		 * récupération de tous les niveau d'accès
		 * @accces public
		 * @return array $array
		 */
		public function get_list_niveau_acces(){		
			$array=array();			
			$list_niveau_acces=$this->bdd->query2('SELECT niveau FROM '.$this->table_acces);
			$list_niveau=$list_niveau_acces->fetchAll(PDO::FETCH_ASSOC);	
			asort($list_niveau);
			return $list_niveau;		
		}		
		/**
		 * récupération de la liste de tous les niveaux d'accès
		 * @accces public
		 * @return array $array
		 */
		public function get_list_nom_niveau_acces(){
			$array=array();			
			$list_niveau_acces=$this->bdd->query2('SELECT name,niveau FROM '.$this->table_acces);			
			$list_niveau=$list_niveau_acces->fetchAll();	
			asort($list_niveau);	
			return $list_niveau;		
		}		
		/**
		* récupérer la liste des pages
		* @access public
    * @return array $array contenant la liste des pages et de leurs caractéristiques
		*/
		public function get_list_pages(){
			$requete=$this->bdd->query2('SELECT * FROM '.$this->table_pages);
			$req=$requete->fetchAll();		
			if(count($req)==0){
				$array=$this->init_pages();
			}else{
				$array=$req;
			}
			return $array;		
		}
		/**
		 * récupération des caractéristiques d'un niveau d'accès
     * @access public
		 * @param string $id identifiant du niveau d'accès
		 * @return array $array contenant les caractéristiques de l'accès cible
		 */
		public function get_unique_acces($id){
			$choix_acces=$this->bdd->prepare("SELECT * FROM ".$this->table_pages." WHERE id=:id");
			$choix_acces->bindValue(':id', $id, PDO::PARAM_STR);
			$choix_acces->execute();
			$c_a=$choix_acces->fetchAll();		
			return $c_a;
		}
		/**
		* initialisation de la liste des access
		* @access public
		*/
		public function init_acces(){	
  		$id=$this->bdd->get_primkey();		
			$requete=$this->bdd->query2('INSERT INTO '.$this->table_acces.' (id,name,niveau,membres) VALUES (\''.$id.'\',\'administrateur\',0,\'\')');
		}
		/**
		* initialisation de la liste des pages
		* @access protected
		* @return mixed $array_page
		*/
		public function init_pages(){
			global $path;  
			//scan du contenu du dossier page
			$array=scandir($path.'/page');
			$array_page=array();
			foreach($array as $a){	
				if($a!='.' AND $a!='..'){
				  //ajout de la page
					if(!in_array($a,$array_page)){						
						$this->add_page('',$a,'');
						//stockage de la page 
						$array_page[]=$a;
					}
				}
			}
			return $array_page;    
		}		
		/**
		* test d'existence de la table de gestion des accès
		* @access public
		* @param numeric $replace défini si le fichier doit être remplacer si il existe déjà  
		* @return boolean
		*/
		public function isset_store($replace='0'){
			//teste de la table de gestion des accès
			$requete_acces=$this->bdd->query2('SHOW TABLES FROM '.$this->db_name.' LIKE \''.$this->table_acces.'\'');
			$requete_sel_acces=$requete_acces->fetchAll();
			if(count($requete_sel_acces)>0){
				 //si la page doit être remplacée
				if($replace!='0'){
					$this->bdd->query2('DROP TABLE `'.$this->db_name.'`.`'.$this->table_acces.'`');
					$this->create_store('acces');
				}
			}else{		
				$this->create_store('acces');
			}					
			//test de la table de gestion des pages
			$requete_pages=$this->bdd->query2('SHOW TABLES FROM '.$this->db_name.' LIKE \''.$this->table_pages.'\'');
			$requete_sel_pages=$requete_pages->fetchAll();
			if(count($requete_sel_pages)>0){
				 //si la page doit être remplacée
				if($replace!='0'){
					$this->bdd->query2('DROP TABLE `'.$this->db_name.'`.`'.$this->table_pages.'`');
					$this->create_store('pages');
				}
			}else{
				$this->create_store('pages');
			}
		}
		/**
		 * fonction de vérification de la validité du niveau d'accès a enregistrer
		 * @param string $name nom du groupe
		 * @param string $niveau niveau niveau de priorité du groupe
		 * @return boolean 
		 */
		public function is_valid_acces($name,$niveau){			
			if(is_string($name) AND is_numeric($niveau)){
				return true;
			}else{
				return false;
			}
		}
		/**
		* modification d'un acces
		* @access public
		* @param string $id identifiant du niveau d'accès 
		* @param string $name nom du groupe
		* @param numeric $niveau niveau de priorité
		*/
		public function modify_acces($id,$name,$niveau){
			$requete=$this->bdd->prepare("UPDATE ".$this->table_acces." SET name=:name, niveau=:niveau WHERE id=:id");
			$requete->bindValue(':id', $id, PDO::PARAM_STR);
			$requete->bindValue(':name', $name, PDO::PARAM_STR);
			$requete->bindValue(':niveau', $niveau, PDO::PARAM_INT);
			$requete->execute();		
		}
    /**
		 * fonction de suppression d'un membre du groupe cible
		 * @param string $id identiant du membre
		 * @param string $group identifiant du groupe 
		 */
    public function remove_from_group($id,$groupe){
			$membres=$this->bdd->prepare("SELECT membres FROM ".$this->table_acces." WHERE id=:groupe");
			$membres->bindValue(':groupe', $groupe, PDO::PARAM_STR);
			$membres->execute();
			$m=$membres->fetchAll();	
			foreach($m as $m){
				$new_membres=str_replace($id.'|','',$m);
			}
			$this->bdd->query2('UPDATE '.$this->table_acces.' SET membres='.$new_membres .' WHERE id='.$groupe)->fetchAll();	
    }
		/**
		* @access public
		*/
		public function __destruct(){
			$this->bdd->closeCursor();
		}
	}										
?>		