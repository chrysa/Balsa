<?php
	global $path,$path_w,$base_url;
	$array_type_regex=array('','([0-9\-_]+)','([a-zA-Z\-_]+)','([a-zA-Z0-9\-_]+)');
	$content_hta='';
	$content_htp='';
	$doc = new DOMDocument();
	$doc->Load($path.'admin/plugin/chrysa_htaccess/htaccess.xml');
	$select_conf=$doc->getElementsByTagName('config');
	foreach($select_conf as $s_c){	
		if($s_c->getAttribute('etat_redir')==1){		
			$content_hta.='RewriteEngine on';
			$content_hta.="\n";
			$content_hta.="\n";
		}
		if($s_c->getAttribute('etat_axx')==1){
			$content_hta.='AuthName "'.$s_c->getAttribute('nom').'"';	 
			$content_hta.="\n";
			$content_hta.='AuthType Basic';	 
			$content_hta.="\n";
			$content_hta.='AuthUserFile "'.$s_c->getAttribute('path_htp').'.htpasswd"';	 
			$content_hta.="\n";
			$content_hta.='Require valid-user';			
			$select_user=$doc->getElementsByTagName('user');
			foreach($select_user as $s_u){
				$content_htp.=$s_u->getAttribute('nom').':'.$s_u->getAttribute('mdp');
				$content_htp.="\n";		
			}			
			if(is_file($s_c->getAttribute('path_htp').'.htpasswd')){
				unlink($s_c->getAttribute('path_htp').'.htpasswd');
			}
			if(file_put_contents(($s_c->getAttribute('path_htp').'.htpasswd'), $content_htp)){
				$aff.='<div>le fichier .htapasswd a bien été créé a l\'adresse '.$s_c->getAttribute('path_htp').'.htpasswd</div>';
			}	
			$content_hta.="\n";	
			$content_hta.="\n";	
		}
		if($s_c->getAttribute('etat_err')==1){	
			$content_hta.='#redirection des erreurs';	 
			$content_hta.="\n";
			if($s_c->getAttribute('etat_redir')==1){
				$select_error=$doc->getElementsByTagName('error');
				foreach($select_error as $s_e){
					$content_hta.='ErrorDocument '.$s_e->getAttribute('code').' '.$base_url.$s_e->getAttribute('adr_nat');
					$content_hta.="\n";
				}
			}else{
				$select_error=$doc->getElementsByTagName('error');
				foreach($select_error as $s_e){
					$content_hta.='ErrorDocument '.$s_e->getAttribute('code').' '.$base_url.$s_e->getAttribute('adr_re');
					$content_hta.="\n";
				}
			}
			$content_hta.="\n";			
		}		
		if($s_c->getAttribute('etat_redir')==1){		
			$select_rule=$doc->getElementsByTagName('rule');
			foreach($select_rule as $s_r){
				$content_hta.='#'.$s_r->getAttribute('comment');
				$content_hta.="\n";
				$content_hta.='RewriteRule ^';
				for($i=1; $i<=$s_r->getAttribute('nbr_para'); $i++){
					$content_hta.=$array_type_regex[$s_r->getAttribute('type_para_'.$i)].$s_r->getAttribute('sep_para');					
				}				
				$content_hta=substr($content_hta,0,-strlen($s_r->getAttribute('sep_para'))).'$';
				$content_hta.=' '.$s_r->getAttribute('page').'?';
				for($i=1; $i<=$s_r->getAttribute('nbr_para'); $i++){
					$content_hta.=$s_r->getAttribute('para_'.$i).'=$'.$i.'&';
				}
				$content_hta=substr($content_hta,0,-1);
				$content_hta.="\n";		
			}			
			$content_hta.="\n";
		}
		if(is_file($s_c->getAttribute('path_hta').'.htaccess')){
			unlink($s_c->getAttribute('path_hta').'.htaccess');
		}
		if(file_put_contents(($s_c->getAttribute('path_hta').'.htaccess'), $content_hta)){
			$aff.='<div>le fichier .htaccess a bien été créé a l\'adresse '.$s_c->getAttribute('path_hta').'.htaccess</div>';		
		}
	}
	$doc->save($path.'admin/plugin/chrysa_htaccess/htaccess.xml');			
	echo $aff;
?>