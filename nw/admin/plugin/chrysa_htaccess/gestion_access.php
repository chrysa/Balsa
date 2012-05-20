 <?php
	global $path,$base_url;
?>
<h1>gestion des accès</h1>
<h2>ajout d'un utilisateur</h2>
<form method="post" action="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_htaccess&action=access&act=add">
	<label for="user">utilisateur : </label><input type="type" name="user" id="user"/>
	<label for="mdp">mot de passe : </label><input type="password" name="mdp" id="mdp"/>
	<input type="submit" name="envoyer" value="ajouter"/> 
</form>
<?php
	switch($_GET['act']){
		case 'add':
			$id=0;
			$nbr_user=0;
			$access = new DOMDocument();
			$access->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			if($access->getElementsByTagName('hta')->length==0){
				$newhta=$access->createElement('hta');
				$newhta->setAttribute('nbr_user',$nbr_user);
				$place_hta=$access->getElementsByTagName('root')->item(0);
				$place_hta->appendChild($newhta);  
			}
			$select_axx=$access->getElementsByTagName('hta');
			foreach($select_axx as $s_a){		
				$select_user=$s_a->getElementsByTagName('user');
				foreach($select_user as $s){		
					if($s->getAttribute('id')>$id){
						$id=$s->getAttribute('id');
					}
					$nbr_user++;
				}
				$s_a->setAttribute('nbr_user', $nbr_user+1);
			}
			$id++;
			$newaxx=$access->createElement('user');
			$newaxx->setAttribute('id', $id);
			$newaxx->setAttribute('nom', $_POST['user']);
			$newaxx->setAttribute('mdp', crypt($_POST['mdp']));
			$place_axx=$access->getElementsByTagName('hta')->item(0);
			$place_axx->appendChild($newaxx);  
			$access->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			break;		
		case 'mod':	
			$axx = new DOMDocument();
			$axx->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			$init_dom = new DOMXpath($axx);
			$choix_dup = $init_dom->query("//root/hta/user[@id=".$_GET['id']."]");
			foreach ($choix_dup as $c_s){	
				echo '<h2>utilisateur a modifier</h2><form method="post" action="'.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=access&act=modifier&id='.$_GET['id'].'">
								<label for="user">utilisateur : </label><input type="type" name="user" id="user" value="'.$c_s->getAttribute('nom').'"/>
								<label for="mdp">mot de passe : </label><input type="password" name="mdp" id="mdp" value="'.$c_s->getAttribute('mdp').'"/>
								<input type="submit" name="envoyer" value="modifier"/> 
							</form>';

			}
			$axx->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			break;		
		case 'modifier':
			$axx = new DOMDocument();
			$axx->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			$init_dom = new DOMXpath($axx);
			$choix_dup = $init_dom->query("//root/hta/user[@id=".$_GET['id']."]");
			foreach ($choix_dup as $c_s){	
				$c_s->setAttribute('nom', $_POST['user']);
				$c_s->setAttribute('mdp', crypt($_POST['mdp']));
			}
			$axx->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');		
			break;
		case 'sup':			
			$axx = new DOMDocument();
			$axx->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			$init_dom = new DOMXpath($axx);
			$choix_axx = $init_dom->query("//root/hta/user[@id=".$_GET['id']."]");
			foreach($choix_axx as $choix_d){	
				$choix_d->parentNode->removeChild($choix_d);
			}
			$axx->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
			break;
	}
	echo '<h2>liste des utilisateurs</h2><table width="100%"><thead><tr><td>nom</td><td></td><td></td></tr></thead><tbody>';
	$axx = new DOMDocument();
	$axx->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
	$select_axx = $axx->getElementsByTagName('user');
	foreach($select_axx as $s){		
		echo '<tr>
						<td>'.$s->getAttribute('nom').'</td>
						<td><a href="'.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=access&act=mod&id='.$s->getAttribute('id').'"/>modifier</a></td>
						<td><a href="'.$base_url.'admin.php?page_admin=1&module=chrysa_htaccess&action=access&act=sup&id='.$s->getAttribute('id').'"/>supprimer</a></td>
				</tr>';
	}
	$axx->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
	echo '</tbody><tfoot><tr><td>nom</td><td></td><td></td></tr></tfoot></table>';
?>