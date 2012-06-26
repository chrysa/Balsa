<?php
	/**
	* @class gestion_acces_manager_XML.class.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief class de gestion de l'enregistrement, modification et suppression de access en XML
	* @category chrysa_axx 
	*/
	class gestion_acces_manager_XML{
	 /**
	 * @var object $xml instance de DOMDocument()
	 * @access protected
	 */
		protected $xml;
	 /**
	 * @var string $file_path chemin d'accès absolu au fichier en cours de traitement
	 * @access protected
	 */
		protected $file_path;
	 /**
	 * @var string $gestion_acces_path chemin d'accès relatif au fichier en cours de traitement
	 * @access protected
	 */
		protected $gestion_acces_path='data/xml_axx/gestion_acces.xml';
	 /**
	 * @var string $content contenu de base du fichier XML
	 * @access protected
	 */
		protected $content='<?xml version="1.0" encoding="utf-8"?><root></root>';
		/**
		* initialisation de l'objet
		* @access public
		*/
		public function __construct() {
			global $path,$bdd;
			$this->bdd=$bdd;
			$this->file_path=$path.$this->gestion_acces_path;
			if($this->isset_store()==true){
				$this->load_file();
			}
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
			//création du noeud
			$nouvel_acces=$this->xml->createElement('acces');
			$nouvel_acces->setAttribute('id',$id);
			$nouvel_acces->setAttribute('name', $name);
			$nouvel_acces->setAttribute('niveau', $niveau);
			$place_acces=$this->xml->getElementsByTagName('liste_acces')->item(0);
			$place_acces->appendChild($nouvel_acces); 
		}
		/**
		* ajout du niveau d'accès à une page
		* @access public
		* @param string $id identifiant de la page
		* @param string $acces niveau d'accès demandé pour la page  
		*/
		public function add_acces_page($id,$acces){
			//sélection de la page
			$init_dom=new DOMXpath($this->xml);
			$choix_page=$init_dom->query("//root/liste_pages/page[@id='".$id."']");
			foreach($choix_page as $c_p){	
				//ajout de l'attribut
				$c_p->setAttribute('acces',$acces);
			}
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
			//cration du noeud
			$nouvelle_page=$this->xml->createElement('page');
			$nouvelle_page->setAttribute('id',$id);
			$nouvelle_page->setAttribute('name', $page);
			$nouvelle_page->setAttribute('acces', $acces);
			$place_page=$this->xml->getElementsByTagName('liste_pages')->item(0);
			$place_page->appendChild($nouvelle_page); 
		}
    /**
		 * fonction d'assignation d'un membre à un groupe
		 * @param string $id identifiant de l'utilisateur
		 * @param string $group identifiant du groupe 
		 */
    public function add_to_group($id,$group){
			  $init_dom=new DOMXpath($this->xml);
			  $choix_acces=$init_dom->query("//root/liste_acces/acces[@id='".$group."']");
			  foreach($choix_acces as $c_a){	
				  $new_mbr=$this->xml->createElement('mbr');
				  $new_mbr->setAttribute('id',$id);
				  $place_mbr=$c_a->item(0);
				  $place_mbr->appendChild($new_mbr); 			
			  }		
    }
		/**
		* création du fichier de stockage
		* @access public
		* @return boolean
		*/
		public function create_store(){
			if(file_put_contents($this->file_path, $this->content)){
				$this->load_file();
				if(is_array($this->init_pages()) AND $this->init_acces()){
					return true;					
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		* suppression d'un acces
		* @access public
		* @param string $id identifiant de l'accès cible
		*/
		public function delete_acces($id){
			//suppression de l'accès cible
			$init_dom=new DOMXpath($this->xml);
			$choix_acces=$init_dom->query("//root/liste_acces/acces[@id='".$id."']");
			foreach($choix_acces as $c_a){	
				$niveau=$c_a->getAttribute('niveau');
				$c_a->parentNode->removeChild($c_a);
			}			
			//reset du niveau d'accès des pages liées
			$choix_niveau=$init_dom->query("//root/liste_pages/page[@acces='".$niveau."']");
			foreach($choix_niveau as $c_a){			
				$c_a->setAttribute('acces', '');
			}
		}
		/**
		* suppression d'une page
		* @access public
		* @param string $id identifiant de la page 
		*/
		public function delete_page($id){
			$init_dom=new DOMXpath($this->xml);
			$choix_page=$init_dom->query("//root/liste_pages/page[@id='".$id."']");
			foreach($choix_page as $c_p){	
				$c_p->parentNode->removeChild($c_p);
			}
		}
		/**
		 * suppression des modifications apportées par le plugin
		 */
		public function delete_stock(){
			unlink($this->file_path);
		}
		/**
		 * récupération du niveau d'accès de la page
     * @access public
		 * @param type $page nom de la page cible
		 * @return mixed
		 */
		public function get_acces_page($page){	
			//récupération du nom de la page
			$page=(substr($page,-4)=='.php')?substr($page,0,-4):$page;
			//sélection de la page
			$init_dom=new DOMXpath($this->xml);
			$choix_page=$init_dom->query("//root/liste_pages/page[@name='".$page."']");
			foreach($choix_page as $c_p){	
				if($c_p->hasAttribute('acces')){
					return $c_p->getAttribute('acces');
				}else{
					return false;
				}
			}
		}
		/**
		* récupérer la liste des access
		* @access public
		* @return array $array_acces array contenant toutes les caractéristiques des niveaux d'accès 
		*/
		public function get_list_acces(){
			$array_acces='';
			$liste_acces=$this->xml->getElementsByTagName('liste_acces');
			if($liste_acces->length==0){
				$this->init_acces();
			}else{
				$acces=$this->xml->getElementsByTagName('acces');				
				if($acces->length>0){
					$childnodes=$this->xml->getElementsByTagName('acces');	
					foreach($childnodes as $c){
						$array_acces[$c->getAttribute('id')]['id']=$c->getAttribute('id');
						$array_acces[$c->getAttribute('id')]['name']=$c->getAttribute('name');
						$array_acces[$c->getAttribute('id')]['niveau']=$c->getAttribute('niveau');
					}
				}
			}			
			return $array_acces;
		}
		/**
		* récupérer la liste des pages
		* @access public
    * @return array $array contenant la liste des pages et de leurs caractéristiques
		*/
		public function get_list_pages(){
			$liste_pages=$this->xml->getElementsByTagName('liste_pages');
			if($liste_pages->length==0){
				$array=$this->init_pages();
			}else{
				$pages=$this->xml->getElementsByTagName('page');	
				if($pages->length>0){
					$array=array();
					foreach($pages as $p){
						$array[$p->getAttribute('id')]['id']=$p->getAttribute('id');
						$array[$p->getAttribute('id')]['name']=$p->getAttribute('name');
						$array[$p->getAttribute('id')]['niveau']=$p->getAttribute('acces');
					}
				}
			}
			return $array;		
		}
		/**
		 * récupération de la liste des identifiants des membres d'un groupe
		 * @param string $id identifiant du groupe
		 * @return array $array_mbr
		 */
    public function get_list_membre($id){
			  $init_dom=new DOMXpath($this->xml);
			  $choix_acces=$init_dom->query("//root/liste_acces/acces[@id='".$id."']");
			  foreach($choix_acces as $c_a){	
    		  $liste_membres=$c_a->getElementsByTagName('mbr');
			    foreach($liste_membres as $l_m){
			      $array_mbr[]=$l_m->getAttribute('id');
			    }
			  }		
			  return $array_mbr;
    }	
		/**
		 * récupération de tous les niveau d'accès
		 * @accces public
		 * @return array $array
		 */
		public function get_list_niveau_acces(){			
			$array=array();
			$acces=$this->xml->getElementsByTagName('acces');
			foreach($acces as $a){
				if(!in_array($a->getAttribute('niveau'),$array)){
					$array[$a->getAttribute('acces')]['niveau']=$a->getAttribute('niveau');
				}
			}
			asort($array);	
			return $array;
		}	
		/**
		 * récupération de la liste de tous les niveaux d'accès
		 * @accces public
		 * @return array $array
		 */
		public function get_list_nom_niveau_acces(){
			$array=array();
			$acces=$this->xml->getElementsByTagName('acces');
			foreach($acces as $a){
				if(!in_array($a->getAttribute('niveau'),$array)){
					$array[$a->getAttribute('id')]['name']=$a->getAttribute('name');
					$array[$a->getAttribute('id')]['niveau']=$a->getAttribute('niveau');
				}
			}
			asort($array);	
			return $array;
		}	
		/**
		 * récupération des caractéristiques d'un niveau d'accès
     * @access public
		 * @param string $id identifiant du niveau d'accès
		 * @return array $array contenant les caractéristiques de l'accès cible
		 */
		public function get_unique_acces($id){
			$init_dom=new DOMXpath($this->xml);
			$choix_acces=$init_dom->query("//root/liste_acces/acces[@id='".$id."']");
			foreach($choix_acces as $c_a){	
				$array['name']=$c_a->getAttribute('name');
				$array['niveau']=$c_a->getAttribute('niveau');              
			}	
			return $array;
		}
		/**
		* initialisation de la liste des access
		* @access public
		*/
		public function init_acces(){	
			$nouvelle_liste=$this->xml->createElement('liste_acces');
			$place_liste=$this->xml->getElementsByTagName('root')->item(0);		
			$place_liste->appendChild($nouvelle_liste); 
			$this->add_acces('','administrateur','0');
		}
		/**
		* initialisation de la liste des pages
		* @access protected
		* @return mixed $array_page
		*/
		public function init_pages(){
			global $path;
			//création du noeud primaire		
			$nouvelle_liste=$this->xml->createElement('liste_pages');
			$place_liste=$this->xml->getElementsByTagName('root')->item(0);
			$place_liste->appendChild($nouvelle_liste);  
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
		* test d'existence du fichier de gestion des accès
		* @access public
		* @param numeric $replace défini si le fichier doit être remplacer si il existe déjà  
		* @return boolean
		*/
		public function isset_store($replace='0'){
  		//si la page existe
			if(is_file($this->file_path)){
			  //si la page doit être remplacée
				if($replace!='0'){
				  //suppression de l'ancienne page
					unlink($this->file_path);
	        //création du nouveau fichier
					return $this->create_store();
				}else{
				 return true;
				}
			}else{
			  //création du fichier
				return $this->create_store();
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
		
		public function load_file(){
			$this->xml=new DOMDocument();		
			$this->xml->load($this->file_path);
		}
		/**
		* modification d'un acces
		* @access public
		* @param string $id identifiant du niveau d'accès 
		* @param string $name nom du groupe
		* @param numeric $niveau niveau de priorité
		*/
		public function modify_acces($id,$name,$niveau){
			//sélection du noeud
			$init_dom=new DOMXpath($this->xml);
			$choix_acces=$init_dom->query("//root/liste_acces/acces[@id='".$id."']");
			foreach($choix_acces as $c_a){
				$c_a->setAttribute('id',$id);
				$c_a->setAttribute('name',$name);
				$c_a->setAttribute('niveau', $niveau);
			}
		}	
    /**
		 * fonction de suppression d'un membre du groupe cible
		 * @param string $id identiant du membre
		 * @param string $group identifiant du groupe 
		 */
    public function remove_from_group($id,$group){
			$init_dom=new DOMXpath($this->xml);
			$choix_membre=$init_dom->query("//root/liste_acces/acces[@id='".$group."']/mbr[@id='".$id."']");
			foreach($choix_membre as $c_m){	
				$c_m->parentNode->removeChild($c_m);
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
