<?php
	global $path,$path_w,$base_url;
	
	if(isset($_POST['configurer'])){
		$conf = new DOMDocument();
		$conf->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
		if($conf->getElementsByTagName('config')->length==0){
			$newconf=$conf->createElement('config');
			$newconf->setAttribute('nom', $_POST['nom']);
			$newconf->setAttribute('path_hta', $_POST['hta']);
			$newconf->setAttribute('path_htp', $_POST['htp']);
			$newconf->setAttribute('etat_axx', $_POST['activ_axx']);	
			$newconf->setAttribute('etat_redir', $_POST['activ_redir']);	
			$newconf->setAttribute('etat_err', $_POST['activ_err']);	
			$place_new_conf=$conf->getElementsByTagName('root')->item(0);
			$place_new_conf->appendChild($newconf);  
		}else{
			echo 'Le .htaccess a déja été configuré';
		}
		$conf->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
	}	
		
	if(isset($_POST['modifier'])){
		$axx = new DOMDocument();
		$axx->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
		$sel_conf=$axx->getElementsByTagName('config');
		foreach($sel_conf as $s_c){
			$s_c->setAttribute('nom', $_POST['nom']);
			$s_c->setAttribute('path_hta', $_POST['hta']);
			$s_c->setAttribute('path_htp', $_POST['htp']);
			$s_c->setAttribute('etat_axx', $_POST['activ_axx']);	
			$s_c->setAttribute('etat_redir', $_POST['activ_redir']);	
			$s_c->setAttribute('etat_err', $_POST['activ_err']);	
		}
		$axx->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');			
	}
	
	$conf = new DOMDocument();
	$conf->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
	if($conf->getElementsByTagName('config')->length>0){
		$sel_conf=$conf->getElementsByTagName('config');
		foreach($sel_conf as $s_c){
			$nom=$s_c->getAttribute('nom');
			$path_hta=$s_c->getAttribute('path_hta');
			$path_htp=$s_c->getAttribute('path_htp');
			$etat_axx=$s_c->getAttribute('etat_axx');
			$etat_redir=$s_c->getAttribute('etat_redir');
			$etat_err=$s_c->getAttribute('etat_err');
		}
		$mod=1;
	}else{
		$mod=0;
	}
	$conf->saveXML();
?>
<h1>configuration</h1>
<form action="<?php echo $base_url; ?>admin.php?page_admin=1&module=chrysa_htaccess&action=config" method="post">
	<h2>gestion d'accès</h2>
	nom du .htaccess : <input type="text" name="nom" value="<?php if(isset($nom) && !empty($nom)){ echo $nom; } ?>"/><br/>
	chemin du .htaccess : <input type="text" name="hta" value="<?php if(isset($path_hta) && !empty($path_hta)){ echo $path_hta; }else{echo $path_w;}?>"/>.htaccess<br/>
	chemin du .htpasswd : <input type="text" name="htp" value="<?php if(isset($path_htp) && !empty($path_htp)){ echo $path_htp; } ?>"/>.htpasswd<br/>
	<h3>activer la gestion d'accès</h3>
	<label for="axx_1">Oui : </label><input type="radio" value="1" id="axx_1" name="activ_axx" <?php if(isset($etat_axx) && $etat_axx==1){ echo 'checked="checked"'; } ?>/>
	<label for="axx_0">Non : </label><input type="radio" value="0" id="axx_0" name="activ_axx" <?php if(!isset($etat_axx) || (($etat_axx==0) || $etat_axx=='')){ echo 'checked="checked"'; } ?>/><br/>
	<h2>gestion de redirection</h2>
	<h3>activer la redirection d'url</h3>
	<label for="redir_1">Oui : </label><input type="radio" value="1" id="redir_1" name="activ_redir" <?php if(isset($etat_redir) && $etat_redir==1){ echo 'checked="checked"'; } ?>/>
	<label for="redir_0">Non : </label><input type="radio" value="0" id="redir_0" name="activ_redir" <?php if(!isset($etat_redir) || ($etat_redir==0) || $etat_redir==''){ echo 'checked="checked"'; } ?>/>
	<h3>activation de la gestion d'erreur apache</h3>
	<label for="err_1">Oui : </label><input type="radio" value="1" id="err_1" name="activ_err" <?php if(isset($etat_errr) && $etat_err==1){ echo 'checked="checked"'; } ?>/>
	<label for="err_0">Non : </label><input type="radio" value="0" id="err_0" name="activ_err" <?php if(!isset($etat_err) || (($etat_err==0) || $etat_err=='')){ echo 'checked="checked"'; } ?>/>
	<br/>
	<?php
		if($mod==0){
		?>		
			<input type="submit" value="configurer" name="configurer"/> 
		<?php
		}else{
		?>
			<input type="submit" value="modifier" name="modifier"/> 
		<?php
		}
	?>
</form>
	
