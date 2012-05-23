<?php
/**
 * @file chrysa_cache.php
 * @auteur chrysa
 * @version 1
 * @date 21 mai 2012
 * @category chrysa_cache
 * @brief page de fonctions de gestion de cache
 */

/**
 * @fn verif_cache($file,$duree=10)
 * @global string $path chemin du dossier nw
 * @param string $file nom de la page à vérifier
 * @param numeric $duree durée de validité de la page de cache
 * @return boolean 
 * @brief	fonction de vérification de l'existence et de la validité d'une page de cache
 */
function verif_cache($file,$duree=10){
	global $path;
	//test de l'age de la page de cache
	if((time()-filemtime($path.'data/cache/'.$file.'.cache'))>$duree){
		unlink($path.'cache/'.$file.'.cache');
		return false;
	}else{
		return true;
	}
}
/**
 * @fn mise_en_cache($page,$content)
 * @global string $path chemin du dossier nw
 * @param string $page nom de la page a transférer en fichier cache
 * @param string $content contenu de la page a enregistre en cache
 * @brief fonction de mise en cache de la page en cours
 */
function mise_en_cache($page,$content){
	global $path;
	//création du fichier de cache
	$fichier = fopen($path.'data/cache/'.$page.'.cache', 'w+');
	//remplissage du fichier de cache
	fwrite($fichier, $content);
	//fermeture du fichier de cache
	fclose($fichier);
}
/**
 * @fn affiche_cache($page,$verif_cache='0',$file='',$duree='')
 * @global string $path chemin du dossier nw
 * @param string $page nom de la page à afficher
 * @param numeric $verif_cache variable indiquant si on doit vérifier l'existence de la page en cache
 * @param string $file nom de la page a vérifier
 * @param numeric $duree durée de vie du fichier cache
 * @return mixed $content
 * @brief fonction d'affiche d'un fichier de cache
 */
function affiche_cache($page,$verif_cache='0',$file='',$duree=''){
	global $path;    
	//test pour voir si on doit vérifier l'existence du fichier cache
	if($verif_cache==1){
		$cache=verif_cache($file,$duree);
	}else{
		$cache=true;
	}
	
	if($cache==true){
		//récupération du contenu du cache
		$content = file_get_contents($path.'data/cache/'.$page.'.cache');    
		return $content;
	}
}
?>
