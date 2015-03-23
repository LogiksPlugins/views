<?php
function getViewEngines() {return array("default"=>"Default");}
function getFieldProperties() {
	return array(
			"required"=>"required",
		);
}
function getToolButtons() {
	$arr=array(
			"printview"=>"Print View Page",
			"exportview"=>"Export View Page",
			"mailview"=>"EMail View Page",	
		);
	return $arr;
}
?>
