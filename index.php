<?php
if(!defined('ROOT')) exit('No direct script access allowed');

include_once __DIR__."/api.php";

$slug=_slug("?/src");

if(isset($slug['src']) && !isset($_REQUEST['src'])) {
	$_REQUEST['src']=$slug['src'];
}

if(isset($_REQUEST['src']) && strlen($_REQUEST['src'])>0) {
	$viewConfig=findView($_REQUEST['src']);
	
	if($viewConfig) {
    printView($viewConfig,[]);
	} else {
		echo "<h1 class='errormsg'>Sorry, View '{$_REQUEST['src']}' not found.</h1>";
	}
} else {
	echo "<h1 class='errormsg'>Sorry, View not defined.</h1>";
}
?>