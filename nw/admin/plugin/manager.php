<?php
	global $path,$path_w,$base_url;

	class plugin_manager
	{
		public $path;
		public $name;
		public $xml;
		public $to_install;

		function __construct($plugin)
		{
			global $path;
			$this->path=$path.'admin/plugin/'.$plugin.'/';
			if(is_file($this->path.'install.xml')){
				$this->name=$plugin;
				$this->xml=new DOMDocument();
				$this->xml->load($this->path.'install.xml');
				$this->to_install=array('fonction','page','ajax','hook','media/css','media/js');
			}else{
				echo'Il n\'y a pas de ficher d\'installation pour ce plugin ou il n\'existe pas...';
			}
		}

		function install($quoi,$node)
		{
			global $path;			
			if($node->hasAttribute('file')){
				if($node->getAttribute('file')!=''){	
					if($node->getAttribute('file')!='all'){
						$f_list=$node->childNodes;
						foreach($f_list as $f){
							copy($this->path.$quoi.'/'.$f->getAttribute('name'),$path.$quoi.'/'.$f->getAttribute('name'));
						}
					}else{
						copy_r($this->path.$quoi,$path.$quoi);
					}
				}			
				hook('ajout_page_gestion_axx', array('path'=>$this->path,'quoi'=>$quoi,'node'=>$node));	
			}
		}

		function uninstall($quoi,$node)
		{
			global $path;
			if($node->hasAttribute('file')){
				if($node->getAttribute('file')!=''){
					if($node->getAttribute('file')!='all'){
						$f_list=$node->childNodes;
						foreach($f_list as $f){
							unlink($path.$quoi.'/'.$f->getAttribute('name'));
						}
					}else{
						$all=scandir($this->path.$quoi);
						foreach($all as $a_fn){			  
							if($a_fn=='.' or $a_fn=='..'){
								continue;
							}else{
								if(is_file($path.$quoi.'/'.$a_fn)){
									unlink($path.$quoi.'/'.$a_fn);
								}
							}
						}
					}
				}
				hook('suppression_page_gestion_axx', array('path'=>$this->path,'quoi'=>$quoi,'node'=>$node));	
			}
		}

		function install_data()
		{
			global $path;			
			$data=$this->xml->getElementsByTagName('data');
			foreach($data as $d){
				$fold=$d->getElementsByTagName('folder');
				foreach($fold as $f){
					if($f->getAttribute('parent')==''){
						if(!is_dir($path.'data/'.$f->getAttribute('name'))){
							mkdir($path.'data/'.$f->getAttribute('name'));
						}		
						if(is_dir($path.'admin/plugin/'.$this->name.'/data/'.$f->getAttribute('name'))){	
							$data=scandir($path.'admin/plugin/'.$this->name.'/data/'.$f->getAttribute('name'));
							foreach($data as  $d){
								if($d!='.' AND $d!='..'){
									copy_r($this->path.'data/'.$f->getAttribute('name').'/'.$d,$path.'data/'.$f->getAttribute('name').'/'.$d);
								}else{
									continue;
								}
							}
						}
					}else{
						if(!is_dir($path.'data/'.$f->getAttribute('parent').'/'.$f->getAttribute('name'))){
							mkdir($path.'data/'.$f->getAttribute('parent').'/'.$f->getAttribute('name'));
						}		    
						if(is_dir($path.'data/'.$f->getAttribute('parent').'/'.$f->getAttribute('name'))){				
							$data=scandir($path.'data/'.$f->getAttribute('parent').'/'.$f->getAttribute('name'));
							foreach($data as  $d){
								copy_r($this->path.'data/'.$f->getAttribute('parent').'/'.$f->getAttribute('name').'/'.$d,$path.'data/'.$f->getAttribute('parent').'/'.$f->getAttribute('name').'/'.$d);
							}
						}
					}
				}
			}
		}

		function install_bdd()
		{
			global $bdd;
			$sql=$this->xml->getElementsByTagName('sql');
			foreach($sql as $sql){
				if($sql->hasAttribute('count') AND is_numeric($sql->getAttribute('count')) AND ($sql->getAttribute('count')>0 OR $sql->getAttribute('count')!='')){
					foreach ($sql->getElementsByTagName('file') as $file_sql){
						$bdd->query2(file_get_contents($file_sql));
					}
				}
			}
		}

		function install_lang()
		{
			global $path;		  
			$lang=$this->xml->getElementsByTagName('lang');
			if($lang->length>0){
				foreach ($lang as $lang){
					if($lang->hasAttribute('count') AND is_numeric($lang->getAttribute('count')) AND ($lang->getAttribute('count')>0 OR $lang->getAttribute('count')!='')){
						$files=$lang->getRElementsByTagName('file');
						foreach($files as $file){
							if($file->getRElementsByTagName('lang_code')!='' AND $file->getRElementsByTagName('name')!=''){
								if(!is_file($path.'admin/plugin/chrysa_lang/installed')){
									$chrysa_lang_install=new plugin_manager('chrysa_lang');
									$chrysa_lang_install->instal_all();
								}
								if(!is_file($path.'data/'.$file->getRElementsByTagName('lang_code'))){
									mkdir($path.'data/'.$file->getRElementsByTagName('lang_code'));
								}
								copy($this->path.'/'.$file->getRElementsByTagName('lang_code').'/'.$file->getRElementsByTagName('name'),$path.'data/'.$file->getRElementsByTagName('lang_code').'/'.$file->getRElementsByTagName('name'));
							}						
						}
						inc($path.'admin/plugin/chrysa_lang/gen_all_lang.php');
						gen_multilingue();
					}
				}
			}
		}

		function install_www()
		{
			global $path_w;
			$www=$this->xml->getElementsByTagName('www');
			if($www->length>0){      
				foreach($www as $www){
					$file_www=$www->getElementsByTagName('file');
					foreach ($file_www as $f_w){    
						if($f_w->getAttribute('name')!=''){
							copy($this->path.'www/'.$f_w->getAttribute('name'),$path_w.$f_w->getAttribute('name'));
						}
					}
				}
			}
		}

		function uninstall_www()
		{
			global $path_w;
			$www=$this->xml->getElementsByTagName('www');
			if($www->length>0){
				foreach($www as $www){
					$file_www=$www->getElementsByTagName('file');
					foreach ($file_www as $f_w){  
						if(is_file($path_w.$f_w->getAttribute('name'))){
							unlink($path_w.$f_w->getAttribute('name'));
						}
					}
				}
			}
		}

		function install_nw()
		{
			global $path;
			$nw=$this->xml->getElementsByTagName('nw');
			if($nw->length>0){      
				foreach($nw as $nw){
					$folder_nw=$nw->getElementsByTagName('folder');
					foreach ($folder_nw as $f_n){    
						if($f_n->hasAttribute('name') AND $f_n->getAttribute('name')!='' AND !is_dir($path.$f_n->getAttribute('name'))){
							mkdir($path.$f_n->getAttribute('name'));
						}
						$files=$f_n->getElementsByTagName('file');
						if($files->length>0){ 
							foreach ($files as $file){			
								if($file->getAttribute('name')!=''){
									if($file->hasAttribute('name')){								
										copy($this->path.'nw/'.$file->getAttribute('name'),$path.$file->getAttribute('name'));
									}else{							
										copy($this->path.'nw/'.$f_n->getAttribute('name').'/'.$file->getAttribute('name'),$path.$f_n->getAttribute('name').'/'.$file->getAttribute('name'));
									}
								}
							}
						}else{						
							copy_r($this->path.'nw/'.$f_n->getAttribute('name'),$path.$f_n->getAttribute('name'));
						}
					}
				}
			}
		}

		function uninstall_nw()
		{
			global $path;
			$nw=$this->xml->getElementsByTagName('nw');
			if($nw->length>0){      
				foreach($nw as $nw){
					$folder_nw=$nw->getElementsByTagName('folder');
					foreach ($folder_nw as $f_n){ 
						if($f_n->hasAttribute('name') AND $f_n->getAttribute('name')!='' AND is_dir($path.$f_n->getAttribute('name'))){
							rmdir_r($path.$f_n->getAttribute('name'));
						}else{
							$files=$f_n->getElementsByTagName('file');
							foreach ($files as $file){
								if(is_file($path.$file->getAttribute('name'))){
									unlink($this->path.'nw/'.$file->getAttribute('name'),$path.$file->getAttribute('name'));
								}
							}
						}
					}
				}
			}
		}	

		function install_flag()
		{
		global $base_url;
			$install_t=fopen($this->path.'installed','a');
			fclose($install_t);
			hook('after_plugin_install',array('plugin'=>$this->name));
      exec($base_url.'admin.php?page_admin=a&module=controll_panel&action=regen_js');
      exec($base_url.'admin.php?page_admin=a&module=controll_panel&action=regen_css');
			echo '<div>l\'installation de '.$this->name.' c\'est bien deroule<br/><a href="'.$base_url.'admin.php?page_admin=a&module=controll_panel&action=plugin">retour à la gestion des plugins</a></div>';
			echo '<div><a href="'.$base_url.'admin.php?page_admin=1&module='.$this->name.'">accéder au plugin</a></div>';
		}

		function uninstall_flag()
		{
			global $base_url;
			unlink($this->path.'installed');
			hook('after_plugin_uninstall',array('plugin'=>$this->name));
      exec($base_url.'admin.php?page_admin=a&module=controll_panel&action=regen_js');
      exec($base_url.'admin.php?page_admin=a&module=controll_panel&action=regen_css');
			echo 'la desinstallation de '.$this->name.' c\'est bien deroule<br/><a href="'.$base_url.'admin.php?page_admin=a&module=controll_panel&action=plugin">retour à la gestion des plugins</a>';
		}

		function install_all()
		{
			global $path_w,$path;
			foreach($this->to_install as $t){
				if($t=='media/js'){
					$t2='js';
				}elseif($t=='media/css'){
					$t2='css';
				}else{
					$t2=$t;
				}
				$this->install($t,$this->xml->getElementsByTagName($t2)->item(0));
			}
			$this->install_data();
			copy_r($this->path.'media/img',$path_w.'media/img');
			$this->install_nw();
			$this->install_www();
			$this->install_bdd();
			$this->install_lang();
			$this->install_flag();
		}

		function uninstall_all()
		{
			global $path;
			foreach($this->to_install as $t){
				if($t=='media/js'){
					$t2='js';
				}elseif($t=='media/css'){
					$t2='css';
				}else{
					$t2=$t;
				}
				$this->uninstall($t,$this->xml->getElementsByTagName($t2)->item(0));
				$this->uninstall_nw();				
				$this->uninstall_www();
			}		
			//reste les image a desintaller !!	
			$this->uninstall_flag();
		}
	}
?>
