<?php
if (!defined('ROOT')) exit('No direct script access allowed');

function loadTemplateFromDB($id,$table) {
	$sql="SELECT * FROM $table WHERE id=$id AND blocked='false'";
	$result=_dbQuery($sql);
	if($result) {
		$data=_dbData($result);
		_dbFree($result);
		
		if(count($data)>=1) {
			$data=$data[0];
			
			$cache=CacheManager::singleton();
			$cacheID="views_$id";
			$fpath=$cache->cacheDataLink($data["template"],$cacheID);
			
			$body=TemplateEngine::printTemplateFile($fpath,$data["queries"]);
			printViewReport($body);
		} else {
			exit("<h2 class=error align=center>Report Template ID Not Found</h2>");
		}
	} else {
		exit("<h2 class=error align=center>Error Loading Report Template</h2>");
	}
}
function loadTemplateFromFile($file) {
	$sqlData="";
	$fpath=substr($file,0,strlen($file)-4);
	if(file_exists($fpath.".sql")) {
		$sqlData=file_get_contents($fpath.".sql");
	}
	
	$body=TemplateEngine::printTemplateFile($file,$sqlData);
	printViewReport($body);
}
//Print The View Report Data
function printViewReport($body,$style="",$script="") {
?>
<style media="screen">
#reportbody {
	padding:3px;
	margin:0px;
}
.editable {
	color:black;
	cursor:pointer;
	background:#E7EDDA;
}
.editable:hover {
	background-color: #ffffc0;
	background-image: -o-linear-gradient(top,  #ffffc0,  #f9ee9c);
	background-image: -ms-linear-gradient(top,  #ffffc0,  #f9ee9c);
	background-image: -moz-linear-gradient(top,  #ffffc0,  #f9ee9c);
	background-image: -webkit-gradient(linear, left top, left bottom, from(#ffffc0), to(#f9ee9c));
	background-image: -webkit-linear-gradient(top,  #ffffc0,  #f9ee9c);
	background-image: linear-gradient(top,  #ffffc0,  #f9ee9c);
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffc0', endColorstr='#f9ee9c');
	color: #6d7829;
}
</style>
<style media="print">
#reportbody {
	padding:3px;
	margin:0px;
}
.editable,.editable:hover {
	background:#fff !important;
}
</style>
<?=$style?>
<div id=reportbody>
	<?=$body?>
</div>
<script language=javascript>
<?=$script?>
</script>
<?php } ?>
