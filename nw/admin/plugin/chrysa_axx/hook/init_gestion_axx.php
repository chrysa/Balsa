<?php
	/**
	* @file connexion_axx.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief hook d'initialisation des variables utilisables dans le plugin
	* @category chrysa_axx
	* @global string $path chemin du dossier nw
	* @global array $array_stock array contenant toutes les méthodes de stockage utilisables
	* @global array $hash array contenant les hash utilisés
	* @global string $manager_axx nom de la class de gestion des acces
	* @global string $manager_user nom de la class de gestion des utilisateurs
	* @global string $stock contient le type de stockage utilisé
	*/
	global $path,$array_stock,$array_stock_clone,$hash,$manager_axx,$manager_user,$stock,$array_hash,$config;
	//définition des méthodes de stockage
	$array_stock=array('XML'=>'XML','SQL'=>'SQL','mixed'=>'XML et SQL');
	//création d'un array ne contenant pas le stockage mixé
	$array_stock_clone=$array_stock;
	unset($array_stock_clone['mixed']);
	//définition des méthode de hash utilisables
	$array_hash=array('md5','sha1','sha256','sha384','sha512');			
	if(is_file($path.'data/xml_axx/config.xml')){
		$config='1';
		//récupération des information de configuration en cours d'utilisation
		$conf=new gestion_config();
		//récupération de la méthode de stockage
		$stock=$conf->get_stock();
		//récupération de la méthode de hashage suivant le type de stockage le tout stocké dans un array
		$hash=$conf->get_hash();
		//initialisation des noms de classes suivant le stockage utilisé
		$manager_axx='gestion_acces_manager_'.$stock;
		$manager_user='gestion_user_manager_'.$stock;
	}else{
		$config='0';
	}
?>