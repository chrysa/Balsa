<?php
/**
 * @file index.php
 * @auteur chrysa
 * @version 1
 * @date 21 mai 2012
 * @category chrysa_cache
 * @see inc()
 * @global string $path chemin du dossier nw/
 * @global string $base_url url du site
 * @global array $get réattribution de $_GET
 * @global array $post réattribution de $_POST
 * @var numeric $temps age des fichiers a supprimer en secondes
 * @ver string $repertoire_traite adresse du dossier de cache
 * @var $repertoire variable a parcourir pour récupérer tous les fichiers .cache
 * @var $fichier fichier en cours de traitement 
 * @var string $chemin chemin du fichier à traiter
 * @brief page de navigation
 */
global $path,$base_url,$post,$get;
if(isset($_GET['install'])){
        
}else{
  ?>
  <a href="<?php echo $base_url ?>admin.php?ajax_admin=1&module=chrysa_cache&action=voir">gérer les fichiers de cache</a>
  <a href="<?php echo $base_url ?>admin.php?ajax_admin=1&module=chrysa_cache&action=suppr">supprimer tous les fichiers de cache</a>
  <?php
    if(!isset($_GET['action'])){
    $_GET['action']='voir';
    }
    switch($_GET['action']){
      case 'voir':
        inc($path.'admin/plugin/chrysa_cache/voir_cache.php');
        break;
      case 'suppr':
        inc($path.'admin/plugin/chrysa_cache/suppr_cache.php');
        break;
			case 'suppr_tranch_cache':
				//calcul du nombre de secondes postées
				$temps=$post['seconde']+($post['minute']*60)+($post['heure']*3600)+($post['jour']*86400);		
				//définition tu répertoire d'install a vider et supprimer
				$repertoire_traite=$path.'data/cache';
				//ouverture du dossier d'install
				$repertoire=opendir($repertoire_traite);
				//parcours du dossier avec lecture de chaques fichiers
				while(false!==($fichier=readdir($repertoire))){
					//définition du fichier a effacer
					$chemin=$repertoire_traite.'/'.$fichier;
					//on test si c'est bien un fichier valide
					if(!is_dir($fichier) AND substr($fichier, 0, -strlen('.cache')) AND (time()-filemtime($chemin))>$temps-1){
						//suppression du fichier
						unlink($chemin);
					}
				}
				//fermeture du dossier
				closedir($repertoire); 
        break;
			case 'suppr_select_cache':				
				//test de l'existence d'un nom de fichier en paramètre pour une suppression unique
				if(isset($get['name']) AND !empty($get['name'])){
					//suppression du fichier cache
					if(unlink($path.'data/cache/'.$get['name'].'.cache')){		
						//redirection vers la page de visualisation des fichiers cache pour supprimer de l'url du parametre nom
						header('location: '.$base_url.'admin.php?module=cache&action=voir_cache');
					}
				}				
				break;
    }        
}
?>