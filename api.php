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
				APPROOT.APPS_MISC_FOLDER."views/{$file}.json",
			];
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

		$viewConfig['sourcefile']=$file;
		if(isset($viewConfig['singleton']) && $viewConfig['singleton']) {
			$viewConfig['viewkey']=md5(session_id().$file);
		} else {
			$viewConfig['viewkey']=md5(session_id().time().$file);
		}
		$viewConfig['srckey']=$fileName;
		if(!isset($viewConfig['dbkey'])) $viewConfig['dbkey']="app";

		return $viewConfig;
  }
  
  function printView($viewConfig,$dbKey="app",$params=[]) {
    if(!is_array($viewConfig)) $viewConfig=findView($viewConfig);
    
    if(!is_array($viewConfig) || count($viewConfig)<=2) {
			trigger_logikserror("Corrupt view defination");
			return false;
		}
    
    if($params==null) $params=[];
		$viewConfig=array_merge($viewConfig,$params);
    
    if(!isset($viewConfig['viewkey'])) $viewConfig['viewkey']=md5(session_id().time());
    
    $viewConfig['dbkey']=$dbKey;
    
    if(!isset($viewConfig['template'])) {
			$viewConfig['template']="simple";
		}
    
    if(!isset($viewConfig['actions'])) $viewConfig['actions']=[];
    
    
  }
}
?>