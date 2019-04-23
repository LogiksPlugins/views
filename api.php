<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("findView")) {
  function findView($file) {
    $fileName=$file;
		if(!file_exists($file)) {
			$file=str_replace(".","/",$file);
		}

		$fsArr=[
        $file,
        APPROOT.APPS_MISC_FOLDER."views/{$file}/view.json",
      ];
    
    if(isset($_REQUEST['forSite']) && defined("CMS_SITENAME")) {
      $fsArr[]=ROOT."apps/".CMS_SITENAME."/".APPS_MISC_FOLDER."views/{$file}.json";
    }
    
    $fArr = explode("/",$file);
    if(count($fArr)>1) {
      $fPath = checkModule($fArr[0]);
      if($fPath) {
        unset($fArr[0]);
        $fsArr[] = dirname($fPath)."/views/".implode("/",$fArr).".json";
      }
    }

		$file=false;
		foreach ($fsArr as $fs) {
			if(file_exists($fs)) {
				$file=$fs;
				break;
			}
		}
		if(!file_exists($file)) {
			return false;
		}

		$viewConfig=json_decode(file_get_contents($file),true);

		$viewConfig['sourcefile']=["config"=>$file];
    $viewConfig['sourcefile']['template']=dirname($file)."/template.tpl";
    $viewConfig['sourcefile']['script']=dirname($file)."/script.js";
    $viewConfig['sourcefile']['style']=dirname($file)."/style.css";
    $viewConfig['sourcefile']['dataprocessor']=dirname($file)."/data.php";
    
    foreach($viewConfig['sourcefile'] as $a=>$b) {
      if(!file_exists($b)) {
        $viewConfig['sourcefile'][$a]=false;
      }
    }
    
    
		if(isset($viewConfig['singleton']) && $viewConfig['singleton']) {
			$viewConfig['viewkey']=md5(session_id().$file);
		} else {
			$viewConfig['viewkey']=md5(session_id().time().$file);
		}
		$viewConfig['srckey']=$fileName;
    
    if(isset($viewConfig['source']['type'])) {
      if(!isset($viewConfig['source']['dbkey'])) $viewConfig['source']['dbkey']="app";
      $viewConfig['source']=[$viewConfig['source']];
    } else {
      foreach($viewConfig['source'] as $a=>$b) {
        if(!isset($viewConfig['source'][$a]['dbkey'])) $viewConfig['source'][$a]['dbkey']="app";
      }
    }
    
    
		//if(!isset($viewConfig['dbkey'])) $viewConfig['dbkey']="app";

		return $viewConfig;
  }
  
  function printView($viewConfig,$params=[]) {
    if(!is_array($viewConfig)) $viewConfig=findView($viewConfig);
    
    if(!is_array($viewConfig) || count($viewConfig)<=2) {
			trigger_logikserror("Corrupt view defination");
			return false;
		}
    
    if($params==null) $params=[];
		$viewConfig=array_merge($viewConfig,$params);
    
    if(!isset($viewConfig['viewkey'])) $viewConfig['viewkey']=md5(session_id().time());
    
    //$viewConfig['dbkey']=$dbKey;
    
    if($viewConfig['sourcefile']['template']===false) {
			echo "<h1 class='errormsg'>Sorry, View template not defined.</h1>";
      return;
		}
    
    if(!isset($viewConfig['actions'])) $viewConfig['actions']=[];
    
    $sqlData=[];
    foreach($viewConfig['source'] as $src) {
      if(!isset($src['type'])) $src['type']="sql";
      if(!isset($src['dbkey'])) $src['dbkey']="app";
      
      $data=_db($src['dbkey'])->_selectQ($src['table'],$src['cols'])->_where($src['where'])->_GET();
      if(!$data) $data=[];
      $sqlData[]=$data;
    }
    
    //$_ENV['SQLDATA']=$sqlData;
    
    if($viewConfig['sourcefile']['dataprocessor'] && file_exists($viewConfig['sourcefile']['dataprocessor'])) {
      include_once $viewConfig['sourcefile']['dataprocessor'];
    }
    
    if($viewConfig['sourcefile']['style'] && file_exists($viewConfig['sourcefile']['style'])) {
      echo "<style>";
      readfile($viewConfig['sourcefile']['style']);
      echo "</style>";
    }
    
    _template($viewConfig['sourcefile']['template'],["SQL"=>$sqlData]);
    
    if($viewConfig['sourcefile']['script'] && file_exists($viewConfig['sourcefile']['script'])) {
      echo "<script>";
      readfile($viewConfig['sourcefile']['script']);
      echo "</script>";
    }
    
    //    printArray($viewConfig);
  }
}
?>