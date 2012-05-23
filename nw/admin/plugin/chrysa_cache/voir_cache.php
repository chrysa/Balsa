<?php
/**
 * @file voir_cache.php
 * @auteur chrysa
 * @version 1
 * @date 21 mai 2012
 * @category chrysa_cache
 * @see convertion_temps
 * @global string $path chemin du dossier nw/
 * @global string $base_url url du site
 * @global array $get réattribution de $_GET
 * @global array $post réatribution de $_POST
 * @var array $dir array contenant les caractéristiques du fichier
 * @var numeric $val valeur des listes déroulantes
 * @var mixed $liste_j miste déroulante des jours
 * @var mixed $liste_h liste déroulante des heureq
 * @var mixed $liste_m_s liste déroulantes de minutes et liste déroulantes des secondes
 * @var mixed $content contenu de la page a afficher
 * @var string $d alias de $dir représentant un fichier
 * @var mixed $d_aff nom du fichier à afficher
 * @var numeric $age age du fichier
 * @var mixed $taille poids du fichier
 * @brief page de gestion des fichiers de cache
 */
	global $post,$get,$path,$base_url;
	//listing des fichiers de cache
	$dir=scandir($path.'data/cache/');
  for($i=0, $i_max=count($dir); $i<$i_max; $i++){
    if($dir[$i]=='.' OR $dir[$i]=='..'){
      unset($dir[$i]); 
    }
  }
  if(!empty($dir)){
	  //génération des option de la liste de nombre de jours
	  for($i=0, $i_max=30; $i<=$i_max; $i++){
		  if($i<10){
			  $val='0'.$i;
		  }else{
			  $val=$i;
		  }
		  $liste_j.='<option value='.$val.'>'.$val.'</option>';
	  }
	  //génération des option de la liste de nombre d'heures
	  for($i=0, $i_max=24; $i<=$i_max; $i++){
		  if($i<10){
			  $val='0'.$i;
		  }else{
			  $val=$i;
		  }
		  $liste_h.='<option value='.$val.'>'.$val.'</option>';
	  }
	  //génération des option de la liste de minutes/secondes
	  for($i=0, $i_max=60; $i<=$i_max; $i++){
		  if($i<10){
			  $val='0'.$i;
		  }else{
			  $val=$i;
		  }
		  $liste_m_s.='<option value='.$val.'>'.$val.'</option>';
	  }
	  //formulaire de sélection des fichiers a supprimer	
    $content='
    <form method="POST" action="'.$base_url.'admin.php?ajax_admin=1&module=chrysa_cache&action=suppr_tranch_cache">
      supprimer les fichiers de cache qui ont plus de : 
      <select name="jour">'.$liste_j.'</select> jours 
      <select name="heure">'.$liste_h.'</select> heures 
      <select name="minute">'.$liste_m_s.'</select> minutes 
      <select name="seconde">'.$liste_m_s.'</select> secondes
      <input type="submit" name="supprimer" value="supprimer les fichiers de cache">
    </form>
	  <table width="100%"><tr><td>nom</td><td>age</td><td>poids</td><td>supprimer</td></tr>';
	  foreach($dir as $d){		
		  if($d!='.' AND $d!='..'){
		    $d_aff=str_replace('.cache',' ',$d);
		    $d_aff=str_replace('_',' ',$d_aff);
			  $age=convertion_temps(time()-filemtime($path.'cache/'.$d));
			  $taille=filesize($path.'data/cache/'.$d);
			  //convertion du poids du fichier pour l'affichage
			  if($taille>1048576){
				  $taille=($taille/1048576);
				  if(is_float($taille)){
					  $taille=round($taille, 2);		
				  }
				  $taille=$taille.' Mega octets';
			  }else{
				  if($taille>1024){
					  $taille=($taille/1024);
					  if(is_float($taille)){
						  $taille=round($taille, 2);		
					  }
					  $taille=$taille.' Kilo octets';
				  }else{
					  $taille=$taille.' octets';					
				  }	
			  }			
			  $content.='<tr><td>'.$d_aff.'</td><td>'.$age.'</td><td>'.$taille.'</td><td><a href="'.$base_url.'ajax_admin=1&module=chrysa_cache&action=suppr_select_cache&name='.$d_aff.'">supprimer</a></td></tr>';
		  }
	  }
	  $content.='</table>';
  }else{
    $content='<div>aucuns fichiers de cache générés</div>';
  }
	echo $content;
?>
