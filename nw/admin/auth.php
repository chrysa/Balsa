<?php
global $path,$base_url;
function is_admin()
{	
	global $manager_user;
	$hook=hook('is_admin_axx', array('user_id'=>$_SESSION['user_id']));
	if(!empty($hook)){
		return $hook;
	}else{
		return true;
	}
}

function ban()
{
	
}

function is_ban()
{
	$hook=hook('is_ban_axx', array('user_id'=>$_SESSION['user_id']));
	if(!empty($hook)){
		return $hook;
	}else{
		return false;
	}
}

function admin_login_form()
{
	
}

function admin_login()
{
	
}

if(is_admin())
{
	include_once($path.'admin/fonction/main.php');
	
	if(!isset($_GET['module']))
	{
		inclure_admin_header();
		hook('after_admin_header',array());
		inclure_plugin('controll_panel');
		inclure_admin_footer();
	}
	elseif(isset($_GET['page_admin']))
	{
		inclure_admin_header();
		hook('after_admin_header',array());
		if(is_file($path.'admin/plugin/'.$_GET['module'].'/install.xml')){
			if(is_file($path.'admin/plugin/'.$_GET['module'].'/installed')){
				inclure_plugin($_GET['module']);
			}else{
				header('location: '.$base_url.'admin.php?page_admin=a&module=controll_panel');
			}
		}else{
			inclure_plugin($_GET['module']);
		}
		inclure_admin_footer();
	}
	elseif(isset($_GET['ajax_admin']))
	{
		if(is_file($path.'admin/plugin/'.$_GET['module'].'/index.php'))
		{
			inclure_plugin($_GET['module']);
		}
	}
	else
	{		
		inclure_admin_header();
		hook('after_admin_header',array());
		inclure_plugin('controll_panel');
		inclure_admin_footer();
	}	
}
else
{
	if(!is_ban)
	{
		echo admin_login_form();
	}	
}
traite_fin_de_page();
?>
