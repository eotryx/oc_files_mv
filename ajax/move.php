<?php 
// Init owncloud
OCP\JSON::checkLoggedIn();

if(empty($_POST['src']) || empty($_POST['dest'])){
	OCP\JSON::error(array('data'=> array('message'=>'No data supplied.')));
	exit();
}

if(empty($_POST['dir'])) $_POST['dir'] = '';
$dir   = $_POST['dir'];
if($dir=='/') $dir = '';
$file  = $_POST['src'];
if(strpos($file,';')!==false){
	$path1 = array();
	$file = explode(';',$file);
	array_pop($file); // empty element at the end
}
else{
	$file = array($file);
}

$dir.='/';
$path2 = $_POST['dest'].'/';
if($dir=="//") $dir = "/";
if($path2=="//") $path2="/";

$error = 0;
$files = array();
foreach($file as $f){
	if(OC_Filesystem::rename($dir.$f,$path2.$f)){
		// here is the code when mv was successfull
		$files[] = $f;
	}
}
$result = array('status'=>'success','action'=>'mv','name'=>$files);
OCP\JSON::encodedPrint($result);

