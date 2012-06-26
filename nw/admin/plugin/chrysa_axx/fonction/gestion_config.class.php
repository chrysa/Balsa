<?php
	/**
	* @class gestion_config.class.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief class de gestion de la configuration
	* @category chrysa_axx 
	*/
	class gestion_config {
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
	 * @var string $content_init chemin d'accès relatif aux fichier de stockage des paramètres
	 * @access protected
	 */
		protected $content_init='<?xml version="1.0" encoding="utf-8"?><root></root>';	
		/**
		* initialisation de l'objet
		* @access public
		*/
		public function __construct() {
			global $path;
			$this->file_path=$path.'data/xml_axx/config.xml';
			if(is_file($this->file_path)){
				$this->load_file();
			}else{
				$this->init_file();
				$this->load_file();
			}
		}
		/**
		 * charment du fichier
		 * @protected 
		 */
		protected function load_file(){
			$this->xml=new DOMDocument();		
			$this->xml->load($this->file_path);	
		}
		/**
		 * initialisation du fichier
		 * @protected 
		 */
		protected function init_file(){
			file_put_contents($this->file_path, $this->content_init);
		}
		/**
		 * ajout d'une méthode de stockage
		 * @public
		 */
		public function add_stock($stock){
			$new_stock=$this->xml->createElement('stock');
			$new_stock->setAttribute('type',$stock);
			$place_stock=$this->xml->getElementsByTagName('root')->item(0);
			$place_stock->appendChild($new_stock); 
		}
		/**
		 * ajout d'une méthode de hash
		 * @public
		 */
		public function add_hash($type_hash,$val){
			$new_hash=$this->xml->createElement('hash_'.$type_hash);
			$new_hash->setAttribute('type',$val);
			$place_hash=$this->xml->getElementsByTagName('root')->item(0);
			$place_hash->appendChild($new_hash); 
		}
		/**
		 * suppression d'une méthode de hash
		 * @public
		 */
		public function delete_hash($type_hash){
			$hash_sel=$this->xml->getElementsByTagName('hash_'.$type_hash);
			if($hash_sel->length>0){					
				foreach($hash_sel as $h_s){
					$h_s->parentNode->removeChild($h_s);
				}
			}
		}
		/**
		 * récupération d'un array contenant les méthode de hash utilisées suivant le type de stockage
		 * @public
		 */
		public function get_hash(){
			global $array_stock_clone;
			$hash=array();
			foreach($array_stock_clone as $k => $v){
				$config=$this->xml->getElementsByTagName('hash_'.$k);
				if($config->length>0){
					foreach($config as $c){
						$hash[$k]=$c->getAttribute('type');
					}
				}
			}
			return $hash;
		}		
		/**
		 * récupération du type de stock utilisé
		 * @public
		 */
		public function get_stock(){
			$type_stock=$this->xml->getElementsByTagName('stock');
			foreach($type_stock as $c){
				return $c->getAttribute('type');	
			}	
		}	
		/**
		 * modification du type de stock
		 * @public
		 */
		public function modify_stock($stock){
			$type_stock=$this->xml->getElementsByTagName('stock');
			foreach($type_stock as $c){
				$c->setAttribute('type',$stock);	
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
