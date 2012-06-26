<?php
	/**
	* @file ajout_page_gestion_axx.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief hook d'ajout de page
	* @category chrysa_axx
	* @global array $_HOOK contient les paramètre nécessaire au fonctoionnement du hook
	* @global string $path chemin du dossier nw
	* @global string $manager_axx nom de la class de gestion des acces
	*/
	global $_HOOK,$manager_axx,$config;
	if(!empty($manager_axx) AND $config==1){
		//test de la valeur de la clé 	
		if($_HOOK['quoi']=='page'){			
			//initialisation du manager d'accès
			$pages=new $manager_axx();
			//récupération de la liste des pages		
			if($_HOOK['node']->getAttribute('file')=="all"){
				$array_pages=scandir($_HOOK['path'].'page');
				foreach($array_pages as $p){
					if($p!='.' AND $p!='..'){		
						$pages->add_page('',$p,'');
					}
				}
			}else{
				//récupération de la liste des noeuds enfants 
				$f_list=$_HOOK['node']->childNodes;			
				foreach($f_list as $f){
					//suppression de la page
					$pages->add_page($f->getAttribute('name'));
				}
			}
		}
	}
?>
