<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($_SESSION["SESS_USER_ID"])) $_SESSION["SESS_USER_ID"]='guest';
if(!isset($_SESSION["SESS_USER_NAME"])) $_SESSION["SESS_USER_ID"]='Guest';
if(!isset($_SESSION["SESS_PRIVILEGE_ID"])) $_SESSION["SESS_PRIVILEGE_ID"]='-99';
if(!isset($_SESSION["SESS_PRIVILEGE_NAME"])) $_SESSION["SESS_PRIVILEGE_NAME"]='*';

_js(array("jquery.mailform","jquery.editinplace"));

function printDBViewPage($tmplID,$tmplTable,$defMsg=null) {
	printViewPage("$tmplID@$tmplTable",$defMsg,"db");
}
function printViewPage($tmpl,$defMsg=null,$vType="file") {
	if($defMsg==null) $defMsg="View Page";
	if($vType==null) $vType="file";
?>
<div id=rptbody class='ui-widget-content' style='height:100%;overflow:auto;'>
	<h1 align=center>Load <?=$defMsg?></h1>
</div>
<script language=javascript>
viewMsg="<?=$defMsg?>";
function loadView(param,msg,func) {
	$("#rptbody").html("<div class=ajaxloading>Loading "+viewMsg+"</div>");
	url="<?=SiteLocation?>services/?scmd=views&site=<?=SITENAME?>&src=<?=$vType?>&template=<?=$tmpl?>&"+param;
	processAJAXQuery(url,function(txt) {
		$("#rptbody").html(txt);
		initView();
		if(func!=null) {
			if(typeof func=="function") func("");
		}
	});
	/*
	$("#rptbody").load(url,function() {
			initView();
			if(func!=null) {
				if(typeof func=="function") func("");
			}
		});*/
}
function initView() {
	$(".editable:not([edata])").editInPlace({
			callback: function(original_element, html, original) { return html;},
			show_buttons:false,
		});
	$(".editable[edata]").each(function() {
			d=$(this).attr("edata");
			$(this).editInPlace({
				select_options:d,
				field_type:"select",
				callback: function(original_element, html, original) { return html;},
				show_buttons:false,
			});
		});
				
	$(".editable-area").editInPlace({
			callback: function(original_element, html, original) { return html;},
			field_type:"textarea",
			textarea_cols:"35",
			show_buttons:false,
			use_html:true,
		});
}
function printView() {
	win=window.open("","Print Preview");
	body=$("#rptbody").html();
	body=body.replace("(Click here to add text)","");
	win.document.write(body);
	win.print();
}
function mailView() {
	subject=$(viewMsg).text();
	body=$("#rptbody").html();
	body=body.replace("(Click here to add text)","");
	$.mailform("",subject,body);
}
</script>
<?php } ?>
