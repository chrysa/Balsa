<?php
/**
 * @file gestion_redirection.php
 * @auteur chrysa
 * @version 1
 * @date 21 mai 2012
 * @category chrysa_htaccess
 * @global string $path chemin du dossier nw/
 * @global string $base_url url du site
 * @global array $array_type_para_redir array conenant les types de paramètres utilisable en URL 
 * @var string $act variable d'initialisation d'action
 * @brief page de gestion des redirections d'url amettre en place
 */
	global $path,$path_w,$base_url,$array_type_para_redir;
?>	
<h1>gestion des réécriture d'URL</h1>
<?php 	
  //récupération de toutes la pages contenues dans le dossier page
	$page=scandir($path_w);
	//array des items contenus dans $pages qui ne seront pas pris en compte
	$except=array('.','..','.htaccess');
	//initialisation de l'action
	$act=(isset($_GET['act']))?$_GET['act']:'';
	switch ($act){
		case '':
		  //formulaire de lancement d'une nouvelle règle avec la saisie du nombre de paramètres
			?>
        <h2>ajout d'une règle de réécriture d'URL étape 1/2</h2>
				<form method="post" action="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_htaccess&action=redirection&act=gen">
					nombre de paramètres en URL : <input type="text" name="nbr_para"/>
					<input type="submit" name="generer" value="générer la règle"/> 
				</form>
			<?php
			break;
		case 'gen':
		  //génération du gabarit de réécriture
			$form='<h2>ajout d\'une règle de réécriture d\'URL étape 2/2</h2>page de référence : <select name="page">';
			//selection de la page
			foreach($page as $p){
				if(!in_array($p,$except) AND !is_dir($p)){
					$form.='<option value="'.$p.'">'.$p.'</option>';
				}
			}
			$form.='</select>?';
      //séléction des paramètres et de leurs type
			for($i=1; $i<=$_POST['nbr_para']; $i++){
				$form.='<input type="text" name="para_'.$i.'" size="5"/>=';
				$form.='<select name="type_para_'.$i.'">';
				foreach($array_type_para_redir as $k => $v){
					$form.='<option value="'.$k.'">'.$v.'</option>';
				}
				$form.='</select>&';
			}	
			$form=substr($form,0,-1);
			//choix du séparateur de terme
			$form.='<br/>séparateur de paramètres : <input type="text" name="sep_para" size="4"/>';
			$form.='<br/>commentaire : <input type="text" name="comment" size="60"/><br/>';
			//affichage du formulaire
			?>
			<form method="post" action="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_htaccess&action=redirection&act=rec&nbr_para=<?php echo $_POST['nbr_para'];?>">
				<?php echo $form; ?>
				<input type="submit" name="enregistrer" value="enregistrer la règle"/> 
			</form>
			<?php
			break;
		case 'rec':
		  //enregistrement de la règle dans le XML
			$id=0;
			$redir=new DOMDocument();
			$redir->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			//vérification de l'existence d'un noeud contenant les réécritures si il existe pas on le créé
			if($redir->getElementsByTagName('redir')->length==0){
				$newredir=$redir->createElement('redir');
				$place_redir=$redir->getElementsByTagName('root')->item(0);
				$place_redir->appendChild($newredir);  
			}
			//selection du noeud contenant les réécritures
			$select_redir = $redir->getElementsByTagName('redir');
			foreach($select_redir as $s_rule){		
				$select_rules = $s_rule->getElementsByTagName('rule');
				//récupération du dernier identifiant de réécriture saisi
				foreach($select_rules as $s){		
					if($s->getAttribute('id')>$id){
						$id=$s->getAttribute('id');
					}
				}
			}
			$id++;		
			//génération du noeud contenant la réécriture	
			$newrule=$redir->createElement('rule');
			$newrule->setAttribute('id', $id);
			$newrule->setAttribute('nbr_para', $_GET['nbr_para']);
			$newrule->setAttribute('page', $_POST['page']);
			for($i=1; $i<=$_GET['nbr_para']; $i++){
				$newrule->setAttribute('para_'.$i, $_POST['para_'.$i]);
				$newrule->setAttribute('type_para_'.$i, $_POST['type_para_'.$i]);
			}
			$newrule->setAttribute('sep_para', $_POST['sep_para']);
			$newrule->setAttribute('comment', $_POST['comment']);
			$place_rule=$redir->getElementsByTagName('redir')->item(0);
			$place_rule->appendChild($newrule);  
			$redir->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			header('location: '.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=redirection');
			break;
		case 'mod':			
		  //formulaire de modification d'une règle existante
			$redir = new DOMDocument();
			$redir->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			$init_dom = new DOMXpath($redir);
			//sélection de la règle en question et préremplissage du formulaire
			$choix_redir = $init_dom->query("//root/redir/rule[@id=".$_GET['id']."]");
			foreach($choix_redir as $choix_r){	
			  $aff='<form method="post" action="'.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=redirection&act=modifier&id='.$_GET['id'].'">
			  page de référence : <select name="page">';
			  foreach($page as $p){
				  if(!in_array($p,$except)){
					  $aff.='<option value="'.$p.'"';
					  if($p==$choix_r->getAttribute('page')){
						  $aff.=' selected="selected"';
					  }
					  $aff.='>'.$p.'</option>';
				  }
			  }
			  $aff.='</select>?';
			  for($i=1; $i<=$choix_r->getAttribute('nbr_para'); $i++){
				  $aff.='<input type="text" name="para_'.$i.'" value="'.$choix_r->getAttribute('para_'.$i).'"/>=';
				  $aff.='<select name="type_para_'.$i.'">';
				  foreach($array_type_para_redir as $k => $v){
					  $aff.='<option value="'.$k.'"';
					  if($k==$choix_r->getAttribute('type_para_'.$i)){
						  $aff.=' selected="selected"';
					  }
					  $aff.='>'.$v.'</option>';
				  }
				  $aff.='</select>&';
			  }	
			  //suppression du dernier "&"
			  $aff=substr($aff,0,-1);
			  $aff.='<br/>séparateur de paramètres : <input type="text" name="sep_para" value="'.$choix_r->getAttribute('sep_para').'"/>';
			  $aff.='<br/>commentaire : <input type="text" name="comment" value="'.$choix_r->getAttribute('comment').'"/><br/>';				
			  $aff.='<input type="submit" name="modifier" value="modifier la règle"/> 
						  </form>';			
			}
			$redir->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			echo $aff;
			break;
		case 'modifier':
		  //mise à jour d'une règle'
			$redir = new DOMDocument();
			$redir->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			$init_dom = new DOMXpath($redir);
			$choix_redir = $init_dom->query("//root/redir/rule[@id=".$_GET['id']."]");
			foreach($choix_redir as $choix_r){	
				$choix_r->setAttribute('page', $_POST['page']);
				for($i=1; $i<=$choix_r->getAttribute('nbr_para'); $i++){
					$choix_r->setAttribute('para_'.$i, $_POST['para_'.$i]);
					$choix_r->setAttribute('type_para_'.$i, $_POST['type_para_'.$i]);
				}
				$choix_r->setAttribute('sep_para', $_POST['sep_para']);
				$choix_r->setAttribute('comment', $_POST['comment']);
			}
			$redir->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			header('location: '.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=redirection');
			break;
		case 'sup':
		  //suppresssion de la règle sélectionnée
			$redir = new DOMDocument();
			$redir->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			$init_dom = new DOMXpath($redir);
			$choix_redir = $init_dom->query("//root/redir/rule[@id=".$_GET['id']."]");
			foreach($choix_redir as $choix_r){	
				$choix_r->parentNode->removeChild($choix_r);
			}
			$redir->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			header('location: '.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=redirection');
			break;
	}
?>
<h2>liste des réécritures d'URL enregistrées</h2>
<?php
  //génération d'un tableau récapitulatif
	$aff='<table width="100%"><thead><tr><td>url native</td><td>url réécrite</td><td>modifier</td><td>supprimer</td><td>commentaire</td></tr></thead><tbody>';
	$redir = new DOMDocument();
	$redir->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
	$select_redir = $redir->getElementsByTagName('rule');
	foreach($select_redir as $s){		
		$sep=$s->getAttribute('sep_para');
		$aff.='<tr>
						<td>'.$s->getAttribute('page').'?';
						for($i=1; $i<=$s->getAttribute('nbr_para'); $i++){
							$aff.=$s->getAttribute('para_'.$i).'='.$array_type_para_redir[$s->getAttribute('type_para_'.$i)].'&';
						}
						$aff=substr($aff,0,-1).'</td><td>';
						$sep=$s->getAttribute('sep_para');
						for($i=1; $i<=$s->getAttribute('nbr_para'); $i++){
							$aff.=$array_type_para_redir[$s->getAttribute('type_para_'.$i)].$sep;
						}
						$aff=substr($aff,0,-strlen($sep));
			$aff.='</td>
						<td><a href="'.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=redirection&act=mod&id='.$s->getAttribute('id').'"/>modifier</a></td>
						<td><a href="'.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=redirection&act=sup&id='.$s->getAttribute('id').'"/>supprimer</a></td>
						<td>'.$s->getAttribute('comment').'</td>
				 </tr>';
	}
	$redir->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
	$aff.='</tbody><tfoot><tr><td>url native</td><td>url réécrite</td><td>modifier</td><td>supprimer</td><td>commentaire</td></tr></tfoot></table>';
	echo $aff;
?>
