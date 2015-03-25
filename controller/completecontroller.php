<?php
/**
 * ownCloud - files_mv
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author eotryx <mhfiedler@gmx.de>
 * @copyright eotryx 2015
 */

namespace OCA\Files_Mv\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\IServerContainer;
use OCP\IL10N;

class CompleteController extends Controller {
	/** @var \OC\IL10N */
	private $l;
	private $storage;
	private $showLayers = 2; // TODO: Move to settings, default value

	public function __construct($AppName,
								IRequest $request,
								IL10N $l,
								$UserFolder){
		parent::__construct($AppName, $request);
		$this->storage = $UserFolder;
		$this->l = $l;
	}
	/**
	 * provide a list of directories based on the $startDir excluding all directories listed in $file(;sv)
	 * @param string $file - semicolon separated filenames
	 * @param string $startDir - Dir where to start with the autocompletion
	 * @return JSON list with all directories matching
	 *
	 * @NoAdminRequired
	 */
	public function index($file, $StartDir){
		$curDir = $StartDir;
		$files = $this->fixInputFiles($file);
		$dirs = array();
		$filePrefix = "";
		
		// fix curDir, so it always start with leading / 
		if(empty($curDir)) $curDir = '/';
		else {
			if(strlen($curDir)>1 && substr($curDir,0,1)!=='/'){
				$curDir = '/'.$curDir;
			}
		}
		if(!$this->storage->nodeExists($curDir)){
			// user is writing a longer directory name, so assume the base directory instead and set directory starting letters
			$pathinfo = pathinfo($curDir);
			$curDir = $pathinfo['dirname'];
			if($curDir == ".") $curDir = "";
			$filePrefix = $pathinfo['basename'];
		}
		if(!($this->storage->nodeExists($curDir)
			&& $this->storage->get($curDir)->getType()===\OCP\Files\FileInfo::TYPE_FOLDER
			)
		){ // node should exist and be a directory, otherwise something terrible happened
			return array("status"=>"error","message"=>$this->l->t('No filesystem found'));
		}
		if(dirname($files[0])!=="/" && dirname($files[0])!==""){
			$dirs[] = '/';
		}
		$patternFile = '!('. implode(')|(',$files) .')!';
		if($curDir!="/" && !preg_match($patternFile,$curDir)) $dirs[] = $curDir;
		$tmp = $this->getDirList(
								$curDir,
								$files,
								$filePrefix,
								$this->showLayers);
		$dirs = array_merge($dirs,$tmp);
		
		return $dirs;
	}

	/**
	 * clean Input param $files so that it is returned as an array where each file has a full path
	 * @param String $files
	 * @return array
	 */
	private function fixInputFiles($files){
		$files = explode(';',$files);
		if(!is_array($files)) $files = array($files); // files can be one or many
		$rootDir = dirname($files[0]).'/';//first file has full path
		// expand each file in $files to full path to the user root directory
		for($i=0,$len=count($files); $i<$len; $i++){
			if($i>0) $files[$i] = $rootDir.$files[$i];
			if(strpos($files[$i],'//')!==false){
				$files[$i] = substr($files[$i],1); // drop leading slash, because there are two slashes
			}
		}
		return $files;
	}

	/**
	 * Recursively create a directory listing for the current directory $dir, ignoring $actFile with the depth $depth
	 *
	 * @param string $dir - current directory
	 * @param string $actFile - file to be ignored
	 * @param string filePrefix - prefix with which the folder name should start
	 * @param int $depth - which depth, -1=all (sub-)levels, 0=finish
	 */
	private function getDirList($dir, $actFile, $filePrefix, $depth=-1){
		if($depth == 0) return array(); // Abbruch wenn depth = 0
		$ret = array();
		$patternFile = '!(('.implode(')|(',$actFile).'))$!';
		$folder = $this->storage->get($dir)->getDirectoryListing();
		$actFileDir = dirname($actFile[0]); // ignore exactly this path
		if(substr($dir,-1)=='/') $dir = substr($dir,0,-1); //remove ending '/'
		foreach($folder as $i ){
			// ignore files other than directories
			if($i->getType()!==\OCP\Files\FileInfo::TYPE_FOLDER) continue;
			if(!empty($filePrefix) && !(stripos($i->getName(), $filePrefix)===0)) continue; // continue when file-prefix is given and the prefix doesn't match

			$path = $dir.'/'.$i->getName();
			
			// ignore directories that are within the files to be moved
			if(preg_match($patternFile,$path)) continue;

			// only list paths, that are writable and are not the files own directory
			if($i->isUpdateable() && $path != $actFileDir){
				$ret[] =  $path;
			}
			//recursion for all sub directories
			$ret = array_merge($ret,$this->getDirList($path,$actFile,$filePrefix, $depth-1));
		}
		return $ret;
	}
}
