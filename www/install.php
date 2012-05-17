<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<link rel="stylesheet" href="media/css/css.css" type="text/css" media="all" />
		<link rel="stylesheet" href="media/css/style_install.css" type="text/css" media="all" />
		<script type="text/javascript">
			function displayDbInfos(display) {
				if(display) {
					document.getElementById("db_infos").className = "db_infos_ok";
				}
				else {
					document.getElementById("db_infos").className = "no_db_infos";
				}
			}
		</script>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<?php
		#http://php.net/manual/en/function.copy.php

		function copy_r2($path, $dest) {
			if (is_dir($path)) {
				@mkdir($dest);
				$objects = scandir($path);
				if (sizeof($objects) > 0) {
					foreach ($objects as $file) {
						if ($file == "." || $file == "..")
							continue;
						// go on
						if (is_dir($path . '/' . $file)) {
							copy_r2($path . '/' . $file, $dest . '/' . $file);
						} else {
							copy($path . '/' . $file, $dest . '/' . $file);
						}
					}
				}
				return true;
			} elseif (is_file($path)) {
				return copy($path, $dest);
			} else {
				return false;
			}
		}

		#http://nashruddin.com/Remove_Directories_Recursively_with_PHP

		function rmdir_r_2($dir) {
			$files = scandir($dir);
			array_shift($files);	// remove '.' from array
			array_shift($files);	// remove '..' from array

			foreach ($files as $file) {
				$file = $dir . '/' . $file;
				if (is_dir($file)) {
					rmdir_r_2($file);
					rmdir($file);
				} else {
					unlink($file);
				}
			}
			if (rmdir($dir)) {
				return true;
			} else {
				return false;
			}
		}

		function move_nw($path, $path_w, $admin_name) {
			if (is_dir($path)) {
				if (!is_dir($path . 'fonction/')) {
					if (copy_r2('../nw', $path)) {
						#			if(!rmdir_r_2('../nw'))
						#			{
						#				$erreur.='<div>Suppression du repertoire ../nw</div>';
						#			}
						#			if(!rename($path.'admin',$path.'admin_'.$admin_name))
						#			{
						#				$erreur.='<div>renommage du repertoire '.$path.'/nw/admin</div>';
						#			}
						#			else
						#			{
						#			}
					} else {
						$erreur.='<div>creation du repertoire ' . $path . '/nw</div>';
						echo $erreur;
						return false;
					}
				}

				if (is_file($path_w . 'admin.php')) {
					unlink($path_w . 'admin.php');
				}

				$admin_php =
						'
		<?php

		include_once(\'' . $path . 'init.php\');
		include_once(\'' . $path . 'admin/auth.php\');

		?>
		';
				if (!file_put_contents($path_w . 'admin.php', $admin_php)) {
					$erreur.='<div>Création du fichier admin.php</div>';
					echo $erreur;
					return false;
				} else {
					return true;
				}
			}
		}

		function bdd_create($bdd_user, $bdd_host, $bdd_pass, $bdd_name, $bdd_use, $bdd_cre) {
			if ($bdd_use == 'oui') {
				global $bdd, $path, $path_w;
				if (is_file($path . 'fonction/bdd.class.php')) {
					unlink($path . 'fonction/bdd.class.php');
				}
				$bdd_class_str =
						'
		<?php
	class Bdd
	{
		private $bdUser;
		private $bdPassWord;
		private $bdDataBase;
		private $bdServer;
		private $connexion;
		private $estConnecte;


		function Bdd()
		{
			$this->bdUser = "' . $bdd_user . '";
			$this->bdPassWord = "' . $bdd_pass . '";
			$this->bdDataBase = "' . $bdd_name . '";
			$this->bdServer = "' . $bdd_host . '";
			$this->estConnecte = false;
			$this->nbreq=0;
			$this->reqtime=0;
		}
		';
				$bdd_class_str.=file_get_contents($path . 'install/void_bdd.class.php');
				if (file_put_contents($path . 'fonction/bdd.class.php', $bdd_class_str)) {
					include_once $path . 'fonction/fonction.php';
					inclure_fonction('bdd.class');
					if ($bdd_cre == '1') {
						$bdd = new Bdd;
						if ($bdd->creat_db_Balsa($bdd_name)) {
							return true;
						} else {
							$erreur.='<div>creation de la base de donnée</div>';
							echo $erreur;
							return false;
						}
					} else {
						return true;
					}
				} else {
					$erreur.='<div>creation du fichier php de la base de donnée</div>';
					echo $erreur;
					return false;
				}
			} else {
				return true;
			}
		}

		function create_admin($login, $mail, $pass, $pass2) {
			global $bdd, $path, $path_w;

			if (is_file($path . 'admin/admin.xml')) {
				unlink($path . 'admin/admin.xml');
			}

			$id = 1;
			if ($pass == $pass2) {
				$pass = hash('sha512', $pass);
			}
			
			if($db_==='oui'){
				if($bdd->query2("INSERT INTO admin (`id`, `login`,`mail` ,`pass`) VALUES ('".$bdd->get_primkey()."', '".$login."', '".$mail."', '".$pass."')")){
					$admin_db=true;
				}else{
					$admin_db=false;
				}
			}else{
				$admin_db=true;
			}
			
			$xml = '<comptes><' . $login . ' mail="' . $mail . '" id="' . $id . '">' . $pass . '</' . $login . '></comptes>';
			if (file_put_contents($path . 'admin/admin.xml', $xml)) {
				return true;
			} else {
				$erreur.='<div>insertion de l\'administrateur dans la base de donnée</div>';
				echo $erreur;
				return false;
			}
		}

		function create_index() 
		{
			global $path, $path_w;
			if (is_file($path_w.'index.php'))
			{
				unlink($path_w.'/index.php');
			}
			$index_str =
			'<?php

include_once \'' . $path . 'init.php\';

inclure_page(\'index\');

traite_fin_de_page();
?>
	';

			if (file_put_contents($path_w . 'index.php', $index_str)) {
				return true;
			} else {
				$erreur.='<div>creation du fichier www/index.php</div>';
				echo $erreur;
				return false;
			}
		}

  	function create_goulot() {
			global $path, $path_w;

			if (is_file($path_w . 'goulot.php')) {
				unlink($path_w . 'goulot.php');
			}

			$index_str =
					'<?php

include_once  \'' . $path . 'init.php\';
	';

			$index_str.=file_get_contents($path . 'install/void_goulot.php');

			if (file_put_contents($path_w . 'goulot.php', $index_str)) {
				return true;
			} else {
				$erreur.='<div>creation du fichier www/goulot.php</div>';
				echo $erreur;
				return false;
			}
		}

		function creat_init_php() {
			global $path, $path_w, $base_url;

			if (is_file($path . 'init.php')) {
				unlink($path . 'init.php');
			}

			$init_str =
					'<?php
//demarrage de la session
session_start();

//base systeme de fichier
$path=\'' . $path . '\';
$path_w=\'' . $path_w . '\';
$base_url=\'' . $base_url . '\';

$nom_projet=\'' . $_POST['projet_nom'] . '\';

$langage=\'' . $_POST['lang'] . '.utf8\';

//définition de la timezone
date_default_timezone_set(\''.$_POST['timezone'].'\');
';
			if ($_POST['bdd_'] == "oui") {
				$int_str.=
						'//connexion Bdd
		inclure_fonction(\'bdd.class\');
		$bdd=new Bdd;
		if($bdd->connect()!==true)
		{
			$_SESSION[\'erreur\'][$_SESSION[\'count_erreurs\']]=\'sql_connexion bdd\';
			$_SESSION[\'count_erreurs\']++;
		}';
			}

			$init_str.=file_get_contents($path . 'install/void_init.php');
			if (file_put_contents($path . 'init.php', $init_str)) {
				return true;
			} else {
				$erreur.='<div>creation du fichier nw/init.php</div>';
				echo $erreur;
				return false;
			}
		}

		function create_js() {
			global $path, $base_url;
			if (is_file($path.'media/js/balsa_comp_js.php')) {
				unlink($path.'media/js/balsa_comp_js.php');
			}
			
			if (is_file($path.'media/js/main.js')) {
				unlink($path.'media/js/main.js');
			}
			
			$js_str = 'var base_url="' . $base_url . '"';
			$js_str.=file_get_contents($path . 'install/void_main.js');
			if (file_put_contents($path . 'media/js/main.js', $js_str)) {
				return true;
			} else {
				$erreur.='<div>creation du fichier nw/media/js/js.js</div>';
				echo $erreur;
				return false;
			}
		}

		function create_mail($adresse_serveur, $port_serveur, $utilisateur, $mdp_mail) {
			global $path;
			if (is_file($path . 'fonction/mail.php')) {
				unlink($path . 'fonction/mail.php');
			}

			$mail_str = file_get_contents($path . 'install/void_mail.php');
			$mail_str = str_replace('||adresse_serveur||', $adresse_serveur, $mail_str);
			$mail_str = str_replace('||port_serveur||', $port_serveur, $mail_str);
			$mail_str = str_replace('||utilisateur||', $utilisateur, $mail_str);
			$mail_str = str_replace('||mdp_mail||', $mdp_mail, $mail_str);

			if (file_put_contents($path . 'fonction/mail.php', $mail_str)) {
				return true;
			} else {
				$erreur.='<div>creation du fichier nw/fonction/maiL.php</div>';
				echo $erreur;
				return false;
			}
		}

		function suppr_install() {
			global $path;
			//return rmdir_r_2($path.'/install');
			return true;
			//je le laisee en cas de reinstalation, vu que install.php est plus la ca craint rien niveau secu :)
		}

		function install_composant() {
			global $path, $path_w;
			echo 'Deplacement du dossier nw';
			if (move_nw($path, $path_w, $_POST['admin_path'])) {
				if ($_POST['db_cre'] == '1') {
					echo ' ---> ok<br/>creation de la base de donnée';
				}
				if (bdd_create($_POST['db_user'], $_POST['db_host'], $_POST['db_pass'], $_POST['db_name'], $_POST['db_'], $_POST['db_cre'])) {
					echo ' ---> ok <br/>creation du fichier admin';
					if (create_admin($_POST['admin_login'], $_POST['admin_mail'], $_POST['admin_pass'], $_POST['admin_pass_c'])) {
						echo ' ---> ok <br/>creation du fichier mail';
						if (create_mail($_POST['adresse_serveur'], $_POST['port_serveur'], $_POST['utilisateur'], $_POST['mdp_mail'])) {
							echo ' ---> ok <br/>creation du fichier index';
							if (create_index()) {
								echo ' ---> ok <br/>creation du fichier goulot';
								if (create_goulot()) {
									echo ' ---> ok <br/>creation du fichier init';
									if (creat_init_php()) {
										echo' ---> ok <br/>creation du fichier js';
										if (create_js()) {
											echo' ---> ok <br/>suppression du dossier install';
											if (suppr_install()) {
												echo' ---> ok <br/>suppression du fichier install';
												return unlink($path_w . 'install.php');
											}
										}
									}
								}
							}
						}
					}
				}
			} else {
				return false;
			}
		}

		$getAction = (isset($_GET['action'])) ? $_GET['action'] : "";
		switch ($getAction) {
			case '':
				?>
				<title>Installation de Balsa : étape I</title>
			</head>
			<body>
				<div class="site" id="site">
					<img src="media/img/logo_balsa_install.png" alt="logo" id="logo_install" />
					<div class="configStep" style="position: relative;">
						<h1 style="margin-top: 0;">Bienvenue sur Balsa !</h1>
						<h2 style="margin-bottom: 0;">Configuration de la partie d'administration</h2>
						<img src="media/img/img_config.png" alt="image config" id="imgConfig" height="100" />
					</div>
					<form method="post" action="install.php?action=admin">
						<div class="configStep">
							<h3>Configuration du système de fichier</h3>
								<?php
									$chemin_fichier=substr(realpath('install.php'),0,-16);
									$url_actuelle='http://'.substr($_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI'],0,-12);
								?>
							<div class='info'>
								chemin actuel des dossiers : <?php	echo $chemin_fichier; ?>
							</div>
							<div>
								<label for="admin_path_nw">Chemin du repertoire /nw </label>
								<input type="text" id="admin_path_nw" name="admin_path_nw" value="<?php	echo $chemin_fichier ?>"/>/nw/
								<div class='info'>C'est le repertoire de base, la ou se trouveront les fichiers de fonctions et les controlleurs</div>
							</div>
							<div>
								<label for="admin_path_www">Chemin du repertoire /www </label>
								<input type="text" id="admin_path_www" name="admin_path_www" value="<?php	echo $chemin_fichier ?>" />/www/
								<div  class='info'>C'est le repertoire web qui contiendrat les controlleurs maitre (index et goulot) ainsi ue les medias</div>
							</div>
							<div>
								<label for="admin_path">Modification du chemin de l'admin </label>
								<input type="text" id="admin_path" name="admin_path" />
								<div  class='info'>Pour plus de securité, il faut modifie le chemin d'accés a l'interface d'administration</div>
							</div>
							<div class='info'>
								URL actuelle : <?php	echo $url_actuelle; ?>
							</div>
							<div>
								<label for="url">URL du projet </label><input type="text" id="url" name="url" value="<?php echo $url_actuelle;?>"/>/
								<div  class='info'>L'url qui permet l'accé au repertoire www depuis internet</div>
							</div>
						</div>
						<div class="configStep">
							<h3>Configuration de la base de donnée admin</h3>

							<h4>Utiliser une base de donnée</h4>
							<label for="db_1">Oui :</label>
							<input type="radio" id="db_1" name="db_" value="oui" onclick="displayDbInfos(true);" /><br />
							<label for="db_2">Non :</label>
							<input type="radio" id="db_2" name="db_" value="non" checked="checked" onclick="displayDbInfos(false);" /><br />

							<div class="no_db_infos" id="db_infos">
								<label for="db_host">Hote de la base de donnée </label>
								<input type="text" value="localhost" id="db_host" name="db_host" />

								<label for="db_user">Utilisateur de la base de donnée </label>
								<input type="text" value="root" id="db_user" name="db_user" />

								<label for="db_pass">Password de la base de donnée </label>
								<input type="password" id="db_pass" name="db_pass" />

								<label for="db_name">Nom de la base de donnée </label>
								<input type="text" value="Balsa" id="db_name" name="db_name" />
							</div>

							<div>
								<h4>Faut-il créer la base de donnée ? </h4>
								<label for="db_cre2">Oui :</label>
								<input type="radio" value="1" id="db_cre2" name="db_cre" /><br/>
								<label for="db_cre1">Non :</label>
								<input type="radio" value="0" id="db_cre1" name="db_cre" checked="checked" />
							</div>
						</div>
						<div class="configStep">
							<h3>Création du compte admin</h3>
							<div>
								<label for="admin_login">Login admin </label>
								<input type="text" value="Balsa_Admin" id="admin_login" name="admin_login" />
							</div>
							<div>
								<label for="admin_pass">Password admin </label>
								<input type="password" id="admin_pass" name="admin_pass" />
							</div>
							<div>
								<label for="admin_pass_c">Confirmation password admin </label>
								<input type="password" id="admin_pass_c" name="admin_pass_c" />
							</div>
							<div>
								<label for="admin_mail">Mail de l'admin </label>
								<input type="text" id="admin_mail" name="admin_mail" />
							</div>
						</div>
						<div class="configStep">
							<h3>Configuration du serveur mail</h3>
							<div>
								<label for="adresse_serveur">Adresse du serveur </label>
								<input type="text" id="adresse_serveur" name="adresse_serveur" />
							</div>
							<div>
								<label for="port_serveur">Port de connexion au serveur </label>
								<input type="text" id="port_serveur" name="port_serveur" />
							</div>
							<div>
								<label for="utilisateur">Utilisateur </label>
								<input type="text" id="utilisateur" name="utilisateur" />
							</div>
							<div>
								<label for="mdp_mail">Mot de passe </label>
								<input type="password" id="mdp_mail" name="mdp_mail" />
							</div>
						</div>
						<div class="configStep">
							<h3>Finalisation de l'instalation</h3>
							<div>
								<?php	
								  $option_lang='<select name="lang" id="lang">';
									$array_lang=array('af_ZA'=>'Afrikaans', 'ar_AR'=>'العربية', 'az_AZ'=>'Azərbaycan dili', 'be_BY'=>'Беларуская', 'bg_BG'=>'Български', 'bn_IN'=>'বাংলা', 'bs_BA'=>'Bosanski', 'ca_ES'=>'Català', 'cs_CZ'=>'Čeština', 'cy_GB'=>'Cymraeg', 'da_DK'=>'Dansk', 'de_DE'=>'Deutsch', 'el_GR'=>'Ελληνικά', 'en_GB'=>'English (UK)', 'en_US'=>'English (US)', 'eo_EO'=>'Esperanto', 'es_ES'=>'Español (España)', 'es_LA'=>'Español', 'et_EE'=>'Eesti', 'eu_ES'=>'Euskara', 'fa_IR'=>'فارسی', 'fb_LT'=>'Leet Speak', 'fi_FI'=>'Suomi', 'fo_FO'=>'Føroyskt', 'fr_CA'=>'Français (Canada)', 'fr_FR'=>'Français (France)', 'fy_NL'=>'Frisian', 'ga_IE'=>'Gaeilge', 'gl_ES'=>'Galego', 'he_IL'=>'עברית', 'hi_IN'=>'हिन्दी', 'hr_HR'=>'Hrvatski', 'hu_HU'=>'Magyar', 'hy_AM'=>'Հայերեն', 'id_ID'=>'Bahasa Indonesia', 'is_IS'=>'Íslenska', 'it_IT'=>'Italiano', 'ja_JP'=>'日本語', 'ka_GE'=>'ქართული', 'ko_KR'=>'한국어', 'ku_TR'=>'Kurdî', 'la_VA'=>'lingua latina', 'lt_LT'=>'Lietuvių', 'lv_LV'=>'Latviešu', 'mk_MK'=>'Македонски', 'ml_IN'=>'മലയാളം', 'ms_MY'=>'Bahasa Melayu', 'nb_NO'=>'Norsk (bokmål)', 'ne_NP'=>'नेपाली', 'nl_NL'=>'Nederlands', 'nn_NO'=>'Norsk (nynorsk)', 'pa_IN'=>'ਪੰਜਾਬੀ', 'pl_PL'=>'Polski', 'ps_AF'=>'پښتو', 'pt_BR'=>'Português (Brasil)', 'pt_PT'=>'Português (Portugal)', 'ro_RO'=>'Română', 'ru_RU'=>'Русский', 'sk_SK'=>'Slovenčina', 'sl_SI'=>'Slovenščina', 'sq_AL'=>'Shqip', 'sr_RS'=>'Српски', 'sv_SE'=>'Svenska', 'sw_KE'=>'Kiswahili', 'ta_IN'=>'தமிழ்', 'te_IN'=>'తెలుగు', 'th_TH'=>'ภาษาไทย', 'tl_PH'=>'Filipino', 'tr_TR'=>'Türkçe', 'uk_UA'=>'Українська', 'vi_VN'=>'Tiếng Việt', 'zh_CN'=>'中文(简体)', 'zh_HK'=>'中文(香港)', 'zh_TW'=>'中文(台灣)');									
									//génération de la liste déroulante									
									foreach($array_lang as $k => $v){
										$option_lang.='<option value="'.$k.'"';
										if($k=='fr_FR'){
											$option_lang.=' selected="selected"';		
										}
										$option_lang.='>'.$v.'</option>';
									}
								$option_lang.='</select>';
								?>						
								<label for="lang">Langue </label>
									<?php echo $option_lang; ?>							
							</div>							
							<div>
								<?php								
									//récupération des timezones
									$timezone_abbreviations = Datetimezone::listAbbreviations(); 	
									//sélection et stockage des codes timezones						
									foreach($timezone_abbreviations as $k => $v){
										foreach($v as $k2 => $v2){
											if(!empty($v2['timezone_id'])){
												$array_timezone[$v2['timezone_id']]=$v2['timezone_id'];
											}
										}
									}
									//tri du tableau de stockage
									asort($array_timezone);			
									//génération de la liste déroulante			
									$option_timezone='<select name="timezone" id="timezone">';
									foreach($array_timezone as $k =>$v){
										$option_timezone.='<option value="'.$v.'"';
										if($v=='Europe/Paris'){
											$option_timezone.=' selected="selected"';		
										}
										$option_timezone.='>'.$v.'</option>';	
									}										
								  $option_timezone.='</select>';				
								?>	
								<label for="timezone">fuseau horaire </label>
									<?php echo $option_timezone; ?>									
							</div>
							<div>
								<label for="projet_nom">Nom du projet </label>
								<input type="text" id="projet_nom" name="projet_nom" />
							</div>
							<div style="clear:both"></div>
							<input type="submit" value="suivant" />
						</div>
					</form>
		<?php
		break;
	case 'admin':
		?>
				<title>Installation de Balsa : étape II</title>
			</head>
			<body>
				<div class="site" id="site">
		<?php
					
		/*
		  modif du chemin de l'admin (rename('../nw/admin','../nw/admin_'.$str))
		  creation de admin_<str>.php dans /www (incluant le chemin de l'admin)
		  creation et remplissage de la bdd admin
		  login admin
		  configuration de init.php
		 */
		$bdd;
		$path = $_POST['admin_path_nw'] . '/nw/';
		$path_w = $_POST['admin_path_www'] . '/www/';
		$base_url = $_POST['url'] . '/';
		if (install_composant()) {
			echo' ---> ok<br/><a href="' . $base_url . 'admin.php">entrez dans la page d\'administration</a>';
		}

		break;
}
?>
				</div>
				<div style="clear:both"></div>
			</body>
		</html>
