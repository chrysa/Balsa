<?php
global $get,$nom_projet;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php echo inclure_css() ?>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo $nom_projet ?></title>
	</head>
	<body>
		<div class="site" id="site">
			<h1><?php echo $nom_projet; ?></h1>
			<?php
			if(isset($get['page']))
			{
				inclure_page($get['page']);
			}
			?>
		</div>
		<div style="clear:both"></div>
		<div class="lightbox_filtre" id="lightbox_filtre"></div>
		<div class="lightbox" id="lightbox">
			<div class="lightbox_titre" id="lightbox_titre"></div>
			<div class="lightbox_close" onclick="lightbox_close();"></div>
			<div class="lightbox_content" id="lightbox_content"></div>
		</div>
		<?php echo inclure_js() ?>
	</body>
</html>
