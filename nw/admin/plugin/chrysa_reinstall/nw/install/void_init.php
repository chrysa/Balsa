

//fake log
if(empty($_SESSION['user_id'])){
	$_SESSION['user_id']='123';
}
//initialisation des erreurs
if(!isset($_SESSION['erreurs']))
{
	$_SESSION['erreurs']=array();
	$_SESSION['count_erreurs']=0;
}

$_SESSION['in_time']=microtime();

//reassignation des variable GET
$get=$_GET;

//reassignation des variable GET
$post=$_POST;

//mode debug
if(is_file($path.'debug'))
{
	define('debug_mod',true);
}
else
{
	define('debug_mod',false);
}

//récupération des paramètres utilisateurs
$u_agent = $_SERVER['HTTP_USER_AGENT']; 
$nav_name = 'unknown';
$OS_name = 'unknown';
$version= 'unknown';

if (preg_match('/linux/i', $u_agent)) {
		$OS_name = 'linux';
}elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$OS_name = 'mac';
}elseif (preg_match('/windows|win32/i', $u_agent)) {
		$OS_name = 'windows';
}

if(preg_match('/MSIE/i',$u_agent)) { 
		$nav_name = 'Internet Explorer'; 
		$nav_utilisateur = "MSIE"; 
}elseif(preg_match('/Firefox/i',$u_agent)){ 
		$nav_name = 'Firefox'; 
		$nav_utilisateur = "Firefox"; 
}elseif(preg_match('/Chrome/i',$u_agent)){ 
		$nav_name = 'Google Chrome'; 
		$nav_utilisateur = "Chrome"; 
}elseif(preg_match('/Safari/i',$u_agent)){ 
		$nav_name = 'Apple Safari'; 
		$nav_utilisateur = "Safari"; 
}elseif(preg_match('/Opera/i',$u_agent)){ 
		$nav_name = 'Opera'; 
		$nav_utilisateur = "Opera"; 
}elseif(preg_match('/Netscape/i',$u_agent)){ 
		$nav_name = 'Netscape'; 
		$nav_utilisateur = "Netscape"; 
} 

$known = array('Version', $nav_utilisateur, 'other');
$pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
if (preg_match_all($pattern, $u_agent, $matches)) {
		$i = count($matches['browser']);
		if ($i != 1){
				if (strripos($u_agent,"Version") < strripos($u_agent,$nav_utilisateur)){
						$version= $matches['version'][0];
				}else{
						$version= $matches['version'][1];
				}
		}else{
				$version= $matches['version'][0];
		}        
}

$navigateur['nom']=$nav_name;
$navigateur['version']=$version;
$navigateur['type']='desktop';
$navigateur['os']= $OS_name;
$navigateur['complete_user_agent']=$u_agent;
$_SESSION['client']=array();
$_SESSION['client']['navigateur']=$navigateur;
$_SESSION['client']['from']=false;

//controle du login
if(!is_logged())
{
	//valeur de la page par default
	$page_de_base='accueil';
	$_GET['page']=$page_de_base;
}

hook('after_init',array('p_name'=>'chrysa_lang'));
$_HOOK['display'];
hook('init_gestion_axx',array());
?>
