<?php 
// Init owncloud
OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();

if(empty($_POST['src']) || empty($_POST['dest'])){
	OCP\JSON::error(array('data'=> array('message'=>'No data supplied.')));
	exit();
}

/**
 * create src and destination path
 */
	if(empty($_POST['dir'])) $_POST['dir'] = '';
	$dir   = $_POST['dir'];
	if($dir=='/') $dir = '';
	$file  = $_POST['src'];
	if(strpos($file,';')!==false){
		$path1 = array();
		$file  = explode(';',$file);
		array_pop($file); // empty element at the end
	}
	else{
		$file = array($file);
	}

	$dir.='/';
	$path2 = $_POST['dest'].'/';
	if($dir=="//") $dir = "/";
	if($path2=="//") $path2="/";

function copyRec($src,$dest){
	if(OC_Filesystem::is_dir($src)){ // copy dir
		if($dh = OC_Filesystem::opendir($src)){
			OC_Filesystem::mkdir($dest);
			while(($file = readdir($dh)) !== false){
				if(in_array($file,array('.','..'))) continue;
				if(OC_Filesystem::is_dir($src.'/'.$file)) copyRec($src.'/'.$file,$dest.'/'.$file);
				else OC_Filesystem::copy($src.'/'.$file, $dest.'/'.$file);
			}
		}
	}
	else{ // copy file
		OC_Filesystem::copy($src, $dest);
	}
	return true;
}

/**
 * move or copy the file to the destination
 * default: move
 */
$error = 0;
$copy = $_POST['copy']=='true';
$files = array();

$l = OC_L10N::get('files'); // error messages from the files-app

$err = array();
foreach($file as $f){
	$target = $path2.$f;
	$source = $dir.$f;
	if(\OC\Files\Filesystem::file_exists($target)){
		$err['exists'][] = $f;
	}
	else if($copy && copyRec($source,$target)){
		//copied, do not add to $files
	}
	else if(!$copy && OC_Filesystem::rename($source,$target)){
		// here is the code when mv was successfull
		$files[] = $f;
	}
	else{
		$err['failed'][] = $f;
	}
}
$msg =array();
if(!empty($err['exists'])) $msg[] = $l->t("Could not move %s - File with this name already exists", array(implode(", ",$err['exists'])));
if(!empty($err['failed'])) $msg[] = $l->t("Could not move %s", array(implode(", ",$err['failed'])));
$msg = implode("<br>\n",$msg);
$status = (empty($msg)?'success':'error');
$result = array('status'=>$status,'action'=>'mv','name'=>$files,'message'=>$msg);
OCP\JSON::encodedPrint($result);

