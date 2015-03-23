<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($_REQUEST["vtype"]) || !isset($_REQUEST["view"])) {
	echo "<style>body {overflow:hidden;}</style>";
	dispErrMessage("View Type Could Not Be Found, Please notify your server admnistrator.",
		"400:Bad Request Made",400,"media/images/notfound/connection.png");
	exit();
}

loadModule("page");

//_css(array("formfields"));

$btns=array();
$btns[sizeOf($btns)]=array("title"=>"Reload","icon"=>"reloadicon","tips"=>"Reload Page","onclick"=>"window.location.reload();");
$btns[sizeOf($btns)]=array("title"=>"Mail","icon"=>"mailicon","tips"=>"Mail Page","onclick"=>"mailView()");
$btns[sizeOf($btns)]=array("title"=>"Print","icon"=>"printicon","tips"=>"Print Page","onclick"=>"printView()");

$layout="apppage";
$params=array("toolbar"=>$btns,"contentarea"=>"printContent");

printPageContent($layout,$params);

function printContent() {
	include "viewPane.php";
	
	$tbl=_dbtable('views');
	$type="db";
	
	$view=$_REQUEST["view"];
	$type=$_REQUEST["vtype"];
	
	$form="";
	$header="View Page";
	
	if($type=="db") {
		$sql="SELECT * FROM $tbl WHERE ID=$view";
		$result=_dbQuery($sql);
		if($result) {
			$data=_dbData($result);
			_dbFree($result);
			if(isset($data[0])) {
				$form=$data[0]['frmdata'];
				$header=$data[0]['header'];
			}
		}
	} else {
		$tmpl=$view.".tpl";
		$tmpl=str_replace(".tpl.tpl",".tpl",$tmpl);
		
		$template="";
		
		if(file_exists(APPROOT.TEMPLATE_FOLDER.$tmpl)) {
			$template=APPROOT.TEMPLATE_FOLDER.$tmpl;
			$form=APPROOT.TEMPLATE_FOLDER.$view.".frm";
		} elseif(file_exists(ROOT.TEMPLATE_FOLDER.$tmpl)) {
			$template=ROOT.TEMPLATE_FOLDER.$tmpl;
			$form=ROOT.TEMPLATE_FOLDER.$view.".frm";
		} else {
			exit("<h2 class=error align=center>View Template Not Found</h2>");
		}
		if(file_exists($form)) {
			$form=file_get_contents($form);
		} else {
			$form="";
		}
	}
	$webPath=getWebPath(__FILE__);
?>
<style>
#pgworkspace {
	overflow:hidden;
}
<?php if(strlen($form)>0) { ?>
#sidepane {
	width:22%;height:100%;
	float:left;
	border:0px;
}
#rptbody {
	width:77%;height:100%;
	float:right;
}
<?php } ?>
</style>
<link href='<?=$webPath?>/style.css' rel='stylesheet' type='text/css' media='all' />
<?php if(strlen($form)>0) { ?>
<div id='sidepane' class='templateForm'>
	<ul class='formHolder'>
		<?=$form?>
	</ul>
	<hr/>
	<div align=center>
		<button style='width:100px;height:30px;' onclick="$('#sidepane input').val('');">Reset</button>
		<button style='width:100px;height:30px;' onclick='loadReport();'>View</button>
	</div>
</div>
<?php } ?>
<?php
	if($type=="db")
		printDBViewPage($view,$tbl,$header);
	else
		printViewPage($view,$header);
?>
<script language=javascript>
var dateFormat="yy/m/d";
var timeFormat="h:mTT";
var showAMPM=false;
var yearRange='1950:2100';
var datefieldReadonly=true;
$(function() {
	initFormUI();
	<?php
		if(isset($_REQUEST['load']) && count($_REQUEST['load'])>0) {
			$load=$_REQUEST['load'];
			if(is_array($load)) {
				foreach($load as $a=>$b) {
					$load[$a]=str_replace("@","=",$b);
				}
				$load=implode("&",$load);
				echo "loadView('$load','Loading Requested View ...');";
			} else {
				$load=str_replace("@","=",$load);
				echo "loadView('$load','Loading Requested View ...');";
			}
		}
	?>
});
function initFormUI() {
	if($("#sidepane").length>0) {
		frmid="#sidepane";
		$("#sidepane .datefield").each(function() {			
			$(this).datepicker({
					changeMonth: true,
					changeYear: true,
					showButtonPanel: false,
					yearRange: yearRange,
					dateFormat:dateFormat,
				});
		});
		
		if(datefieldReadonly) {
			$(frmid+" .datetimefield, "+frmid+" .datefield, "+frmid+" .timefield").attr("readonly","readonly");
			$(".datefield",frmid).each(function() {
					$(this).parent("li").append("<br/><h6>Format "+dateFormat+"</h6>");
				});
			$(".datetimefield",frmid).each(function() {
					$(this).parent("li").append("<br/><h6>Format "+dateFormat+" "+timeFormat+"</h6>");
				});
			$(".timefield",frmid).each(function() {
					$(this).parent("li").append("<br/><h6>Format "+timeFormat+"</h6>");
				});
		}
		
		$(frmid+" select:not(.nostyle)").addClass("ui-state-default ui-corner-all");
		
		$(frmid+" input.autocomplete").each(function() {
				var minL=1;
				
				if($(this).attr("src")!=null) {
					var href=$(this).attr("src")+"&format=json";
					$(this).autocomplete({
							minLength: minL,
							source:href,
							select: function( event, ui ) {
								loadDataIntoForm(ui.item);
								return true;
							}
						});
				} else {
					if($(this).attr("name").length>0) {
						var href="services/?scmd=autocomplete&id="+$(this).attr("name");
						$(this).autocomplete({
								minLength: minL,
								source:href,
								select: function( event, ui ) {
									loadDataIntoForm(ui.item);
									return true;
								}
							});
					}
				}
			});
	}
}
function loadReport() {
	q=[];
	found=false;
	error=false;
	$("#sidepane .required").each(function() {
			if(this.value.length<=0) {
				error=true;
			}
		});
	if(error) {
		lgksAlert("All Colored Fields Are Required. <br/>Please fill all the required fields.");
		return;
	}
	$("#sidepane").find("input[name],select[name],textarea[name]").each(function() {
			nm=$(this).attr("name");
			vl=$(this).val();
			if(nm!=null && nm.length>0) {
				q.push(nm+"="+vl);
				if(vl.length>0) found=true;
			}
		});
	if(found) {
		loadView(q.join("&"),"Loading Requested View ...");
	} else {
		lgksAlert("Atleast One Field Must Be Filled To Continue Loading The View.");
	}
}
</script>
<?php
}
?>
