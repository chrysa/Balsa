<?php
 /**
	* @class gestion_user_manager_mixed.class.php
	* @auteur chrysa
	* @version 1
	* @date 9 juin 2012
  * @brief class permettant de vérifier les entrées stockées dans tous les moyens de stockages lists concernant les utilisateurs
	* @category chrysa_axx
	*/
	class gestion_user_manager_mixed{
		/**
		 * @acces protected
		 * @var array $global_stock array contenant les valeurs uhnitaires de stockage
		 */
		protected  $global_stock;
		/**
		 * @fn méthode d'initialisation de l'objet en initialisant les classes
		 * @global type $array_stock_clone
		 * @param type $type type d'initialisation
		 */		
		public function __construct($type='user'){
			global $array_stock_clone;
			//initialisation des classes de gestion des accès propres a chaque type de stockage en excluant le stockage mix
			$this->global_stock=$array_stock_clone;
			foreach($array_stock_clone as $a_s_c){
				$class='gestion_user_manager_'.$a_s_c;
				if(class_exists($class)){
					$a_s_c=new $class($type);
				}
			}
		}
		/**
		 * @fn méthode magiques d'appel de fonctions non déclarées dans la classe
		 * @acces public
		 * @param string $name nom de la méthode
		 * @param string $arguments non de l'argument
		 * @return mixed $return retourne le résultat de la fonction de vérification
		 */
		public function __call($name, $arguments) {
		  //appel de la fonction pour chaque type de stockage et stockage dans un array du résultat
			foreach($this->global_stock as $a_s_c){
				if(method_exists($a_s_c, $name)){
					$array[$a_s_c]=$a_s_c->$name($arguments);
				}
			}			
			$return=$this->verify_return($array);
			return $return;
		}
		/**
		 * @fn fonction de vérification des résultat stocké dans l'array indexé suivant les différents moyens de stockage 
		 * @param array $array array contenant les résultats des différents moyens de stockage
		 * @return mixed renvoi false si les résultats renvoyés diffèrents sinon retourne le résultat du premier moyen de stockage stocké dans l'array
		 */
		protected function verify_return(array $array){
		  //récupération des différents moyens de stockage
			$array_keys=array_keys($this->global_stock);
			//calcul du nombre d'entrée de l'array
			$nbr=count($array);	
			if(($nbr==2)){
			  //récupération des 2 array array stockés dans l'array passé en paramètres
				$array1=array_slice($array, 0, 1);
				$array2=array_slice($array, 1, 1);
				//vérification de la cohérence des clés et des valeurs
				$diff=array_diff_assoc($array1[$array_keys[0]], $array2[$array_keys[1]]);
				//retour suivant le nombre de différences détectées
				if(count($diff)==0){
				  //retour de la valeur
					return $array1[$array_keys[0]];
				}else{
					return false;
				}
			}else{
			  //parcours de l'array permettant suivant l'index de généré les couples à comparer
				for($i=0; $i<$nbr;$i++){
				  //définition des valeurs d'index à tester 
					$index1=$i;
					if(($i+1)<$nbr){
						$index2=($i+1);
					}else{
						$index2=0;
					}
					//test pour éviter de comparer les valeurs stocké dans un moyen de stockage a elles même
					if($array_keys[$index1]!==$array_keys[$index2]){
					  //génération des arrays a comparer
						$array1=array_slice($array, $index1, 1);
						$array2=array_slice($array, $index2, 1);
						//récupération moyens de stockage
						$key1=$array_keys[$index1];
						$key2=$array_keys[$index2];
    				//vérification de la cohérence des clés et des valeurs
						$diff=array_diff_assoc($array1[$key1], $array2[$key2]);
    				//retour suivant le nombre de différences détectées
				    if(count($diff)==0){
					    //retour de la valeur
							return $array1[$key1];
						}else{
							return false;
						}		
					}
				}
			}
		}
	}										
?>		
