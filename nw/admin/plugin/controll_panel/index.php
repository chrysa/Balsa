<?php
global $path,$path_w,$bdd,$base_url,$nom_projet;
include_once('controll_panel.php');
$action=$_GET['action'];
switch($action)
{
	case'':
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<link rel="stylesheet" href="<?php echo $base_url; ?>admin.php?ajax_admin=1&module=controll_panel&action=css" type="text/css" media="all" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo $nom_projet ?></title>
	</head>
	<body>
		<div class="site" id="site">
			<?php
			echo list_plugin();
			echo list_function();
			echo list_page_controller();
			echo list_ajax_controller();
			echo list_js();
			echo list_css();
			?>
		</div>
		<div style="clear:both"></div>
		<div class="lightbox_filtre" id="lightbox_filtre"></div>
		<div class="lightbox" id="lightbox">
			<div class="lightbox_titre" id="lightbox_titre"></div>
			<div class="lightbox_close" onclick="lightbox_close();"></div>
			<div class="lightbox_content" id="lightbox_content"></div>
		</div>
		<div id="scipt_js">
			<?php echo inclure_js() ?>
		</div>
	</body>
</html><?php
		break;
	case'js':
		header('Content-type: text/javascript');
		include_once($path.'admin/plugin/controll_panel/controll_panel.js.php');
		break;
	case'css':		
		header('Content-type: text/css');
		include_once($path.'admin/plugin/controll_panel/controll_panel.css.php');
		break;
	case'regen_css':
                unlink($path_w.'media/css/css.css');
                inclure_css();
                header('location: '.$base_url.'admin.php');
		break;
	case'regen_js':
                unlink($path_w.'media/js/js.php');
echo '1';
                inclure_js();
echo '2';
                header('location: '.$base_url.'admin.php');
		break;
}

?>
