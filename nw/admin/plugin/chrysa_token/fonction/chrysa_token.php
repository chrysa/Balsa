<?php
/**
 * @file chrysa_token.php
 * @auteur chrysa
 * @version 1
 * @date 21 mai 2012
 * @category chrysa_token
 * @see generer_token($nom='')
 * @see verifier_token($temps, $referer, $nom='')
 * @brief page de fonction gérants les tokens
 * page de fonctions permettant de créer et gérer les tokens
 */
/**
 * @fn generer_token($nom='')
 * @brief foncction de génération d'un token
 * @param string $nom
 * @return mixed
 */
function generer_token($nom=''){
	//génération d'un token unique
	$token=uniqid(rand(), true);
	//stockage du token dans une $_SESSION
	$_SESSION[$nom.'_token']=$token;
	//stockage du du timestamp de génération dans une $_SESSION
	$_SESSION[$nom.'_token_time']=time();
	return $token;
}
/**
 * @fn verifier_token($temps, $referer, $nom='')
 * @brief fonction de vérification de validation du token
 * @global array $post array contenant toutes les variables passée en post
 * @global array $get array contenant toutes les variables passée en get
 * @param num $temps durée de validité du token
 * @param string $referer adresse d'appel
 * @param string $nom nom du token
 * @param num $url état du passage de paramètre 0 en get et 1 en post
 * @var array $passage
 * @return boolean 
 */
function verifier_token($temps, $referer, $nom='',$url='0'){
 //test et assignation duivant le passafe de paramètre
 if($url=='0'){
  	global $post;
   $passage=$post;
 }else{
  	global $get;
   $passage=$get;
 }
	//test d'existence d'un token
	if(isset($_SESSION[$nom.'_token']) AND isset($_SESSION[$nom.'_token_time']) AND isset($passage['token'])){
		//vérification du token
		if($_SESSION[$nom.'_token']==$post['token']){
			//vérification de la non expiration du token
			if($_SESSION[$nom.'_token_time']>=(time() - $temps)){
				//vérification de la page d'appel
				if($_SERVER['HTTP_REFERER']==$referer){
					return true;
				}
			}
		}
	}else{
		return false;
	}
}
?>
