<h1>gestion des pages d'erreurs</h1>
<?php
  global $path,$base_url;
  
 	$act=(isset($_GET['act']))?$_GET['act']:'';
 	switch($act){
 	  case '':
 	    ?>
      <form method="post" action="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_htaccess&action=error&act=add">
	      code de l'erreur : <input type="text" name="code"/><br/> 	    
	      adreese native de la page : <?php echo $base_url; ?><input type="text" name="adr_nat"/><br/>
	      adreese réécrite de la page : <?php echo $base_url; ?><input type="text" name="adr_re"/><br/>
	      <input type="submit" name="add" value="ajouter"/> 
      </form>
 	    <?php
 	    break;
 	  case 'add':
	    $id=0;
	    $error_node = new DOMDocument();   
	    $error_node->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
	    if($error_node->getElementsByTagName('error_node')->length==0){
		    $newerror_node=$error_node->createElement('error_node');
		    $place_error_node=$error_node->getElementsByTagName('root')->item(0);
		    $place_error_node->appendChild($newerror_node);  
	    }
	    $select_error_node = $error_node->getElementsByTagName('error_node');
	    foreach($select_error_node as $s_error){		
		    $select_errors = $s_error->getElementsByTagName('error');
		    foreach($select_errors as $s){		
			    if($s->getAttribute('id')>$id){
				    $id=$s->getAttribute('id');
			    }
		    }
	    }
	    $id++;			
	    $newerror=$error_node->createElement('error');
	    $newerror->setAttribute('id', $id);
	    $newerror->setAttribute('code', $_POST['code']);
	    $newerror->setAttribute('adr_nat', $_POST['adr_nat']);
	    $newerror->setAttribute('adr_re', $_POST['adr_re']);
	    $place_error=$error_node->getElementsByTagName('error_node')->item(0);
	    $place_error->appendChild($newerror);  
	    $error_node->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
	    header('location: '.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=error'); 	  
 	    break;
    case 'mod':
			$error = new DOMDocument();
			$error->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			$init_dom = new DOMXpath($error);
			$choix_error = $init_dom->query("//root/error_node/error[@id=".$_GET['id']."]");
			foreach($choix_error as $choix_e){	
        ?>
        <form method="post" action="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_htaccess&action=error&act=modifier&id=<?php echo $_GET['id']; ?>">
	        code de l'erreur : <input type="text" name="code" value="<?php echo $choix_e->getAttribute('code');?>"/><br/> 	    
	        adreese native de la page : <?php echo $base_url; ?><input type="text" name="adr_nat" value="<?php echo $choix_e->getAttribute('adr_nat');?>"/><br/>
	        adreese réécrite de la page : <?php echo $base_url; ?><input type="text" name="adr_re" value="<?php echo $choix_e->getAttribute('adr_re');?>"/><br/>
	        <input type="submit" name="modifier" value="modifier"/> 
        </form>
        <?php
			}
			$error->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
 	    break;
    case 'modifier':
			$error = new DOMDocument();
			$error->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			$init_dom = new DOMXpath($error);
			$choix_error = $init_dom->query("//root/error_node/error[@id=".$_GET['id']."]");
			foreach($choix_error as $choix_e){	
	      $choix_e->setAttribute('code', $_POST['code']);
	      $choix_e->setAttribute('adr_nat', $_POST['adr_nat']);
	      $choix_e->setAttribute('adr_re', $_POST['adr_re']);
			}
			$error->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			header('location: '.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=error');
 	    break;
    case 'sup':
			$error = new DOMDocument();
			$error->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			$init_dom = new DOMXpath($error);
			$choix_error = $init_dom->query("//root/error_node/error[@id=".$_GET['id']."]");
			foreach($choix_error as $choix_e){	
				$choix_e->parentNode->removeChild($choix_e);
			}
			$error->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			header('location: '.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=error');
      break;
 	}
?>
<h3>liste des redirections</h3>
<?php
	$aff='<table width="100%"><thead><tr><td>code erreur</td><td>url native</td><td>url réécrite</td><td>modifier</td><td>supprimer</td></tr></thead><tbody>';
	$redir = new DOMDocument();
	$redir->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
	$select_redir = $redir->getElementsByTagName('error');
	foreach($select_redir as $s){		
		$aff.='<tr>
						<td>'.$s->getAttribute('code').'</td>
	 	        <td>'.$s->getAttribute('adr_nat').'</td>
	 	        <td>'.$s->getAttribute('adr_re').'</td>
						<td><a href="'.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=error&act=mod&id='.$s->getAttribute('id').'"/>modifier</a></td>
						<td><a href="'.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=error&act=sup&id='.$s->getAttribute('id').'"/>supprimer</a></td>
				 </tr>';
	}
	$redir->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
	$aff.='</tbody><tfoot><tr><td>code erreur</td><td>url native</td><td>url réécrite</td><td>modifier</td><td>supprimer</td></tr></tfoot></table>';
	echo $aff;
?>


