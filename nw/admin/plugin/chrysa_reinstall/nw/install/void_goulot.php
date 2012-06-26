
foreach($_GET as $gettemp=>$key)
{
	if(!valid_input($gettemp))
	{
		report_erreur('systeme','la variable '.$key.' n\'est pas valide');
		die(0);
	}
}
hook('goulot_before_inclure_ajax',array('page'=>$_GET['page']));
$acces=hook('verification_axx',array('page'=>$_GET['page']));
if(empty($acces)){
  $acces=true;
}
if($acces==true)
{
  inclure_ajax($_GET['page']);
}
inclure_ajax($_GET['page']);
hook('goulot_after_inclure_ajax',array('page'=>$_GET['page']));


traite_fin_de_page();
?>
