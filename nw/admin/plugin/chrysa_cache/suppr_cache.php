<?php
/**
 * @file suppr_cache.php
 * @auteur chrysa
 * @version 1
 * @date 21 mai 2012
 * @category chrysa_cache
 * @global string $path chemin du dossier nw/
 * @global string $base_url url du site
 * @var string $repertoire_traite
 * @var $repertoire ouverture du répertoire contenant le fichier cache
 * @var $fichier fichier a traiter
 * @var string $chemin chemin du fichier en traitement
 * @brief page de suppression de tous les fichiers de cache
 */
	global $path,$base_url;
	$repertoire_traite=$path.'data/cache';
	$repertoire=opendir($repertoire_traite);
	while(false!==($fichier=readdir($repertoire))){
		//définition du fichier a effacer
		$chemin=$repertoire_traite.'/'.$fichier;
		//on test si c'est bien un fichier valide
		if($fichier!='.' AND $fichier!='..' AND !is_dir($fichier)){
			//suppression du fichier
			unlink($chemin);
		}
	}
	//fermeture du dossier
	closedir($repertoire);  	
	//redirection vers la page de vision du cache
	header('location : '.$base_url.'admin.php?ajax_admin=1&module=chrysa_cache');
?>
