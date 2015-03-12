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

use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Http\DataResponse;
use \OCP\AppFramework\Controller;
use \OCP\IServerContainer;
use \OCP\IL10N;

class MoveController extends Controller {
	//private $userId;
	private $l;
	private $storage;

	public function __construct($AppName, IRequest $request, IL10N $l, $UserFolder){
		parent::__construct($AppName, $request);
		$this->storage = $UserFolder;
		$this->l = $l;
	}
	/**
	 * move/copy $file from $srcDir to $dest
	 * @param string $srcDir
	 * @param string $file - semicolon separated filenames
	 * @param string $dest - destination Directory
	 * @param bool $copy - true=copy, false=move
	 * @NoAdminRequired
	 */
	public function index($srcDir, $srcFile, $dest, $copy=false){
		if(empty($srcFile) || empty($dest)){
			return array("status"=>"error","message"=>$this->l->t('No data supplied.'));
		}

		// prepare file names
		$files = explode(';', $srcFile);
		if(!is_array($files)) $files = array($files);
		$files = array_filter($files); // remove empty elements

		//TODO: use OCP instead of OC
		$srcDir = \OC\Files\Filesystem::normalizePath($srcDir).'/';
		$dest   = \OC\Files\Filesystem::normalizePath($dest).'/';
		if($srcDir==$dest){
		   	return array("status"=>"error","message"=>$this->l->t('Src and Dest are not allowed to be the same location!'));
		}

		$error = 0;
		$err = array();
		$filesMoved = array();
		$msg =array();
		foreach($files as $file){
			$toPath = ($dest.$file);
			$fromPath = ($srcDir.$file);
			// API: folder-obj->move/copy($to) not working
			$from = $this->storage->get($fromPath);
			$to = $this->storage->getFullPath($toPath);
			if($this->storage->nodeExists($toPath)){
				$err['exists'][] = $file;
			}
			else{
				try{
					if($copy){
						// when copying files, DO NOT ADD to $filesMoved, as the gui removes them then from the view
						//$this->copyRec($fromPath, $toPath);
						$from->copy($to);
					}
					else{
						/*
						if(\OC\Files\Filesystem::rename($fromPath, $toPath)){
							$filesMoved[] = $file;
						}
						else{
							$err['failed'][] = $file;
						}
						 */
						$from->move($to);
						$filesMoved[] = $file;
						
					}
				}
				catch(\OCP\Files\NotPermittedException $e){
					$err['failed'] = $file;
					$msg[] = $e->getMessage();
				}
				catch(\Exception $e){
					$msg[] = $file.": ".$e->getMessage();
				}
			}
		}
		if(!empty($err['exists'])){
		   	$msg[] = $this->l->t("Could not move %s - File with this name already exists", array(implode(", ",$err['exists'])));
		}
		if(!empty($err['failed'])){
		   	$msg[] = $this->l->t("Could not move %s", array(implode(", ",$err['failed'])));
		}
		$msg = implode("<br>\n",$msg);
		$status = (empty($msg)?'success':'error');
		$result = array('status'=>$status,'action'=>'mv','name'=>$filesMoved,'message'=>$msg);
		return $result;

	}


	/**
	 * copy object recursively, $src can be either file or folder, it doesn't matter
	 * @param string $src - sourcefile
	 * @param string $dest - destination file
	 * @deprecated supported natively now by OC8
	 */
	/*
	private function copyRec($src,$dest){
		if(\OC\Files\Filesystem::is_dir($src)){ // copy dir
			if($dh = \OC\Files\Filesystem::opendir($src)){
				\OC\Files\Filesystem::mkdir($dest);
				while(($file = readdir($dh)) !== false){
					if(in_array($file,array('.','..'))) continue; // skip links to self or upper folder
					if(\OC\Files\Filesystem::is_dir($src.'/'.$file)) $this->copyRec($src.'/'.$file,$dest.'/'.$file);
					else \OC\Files\Filesystem::copy($src.'/'.$file, $dest.'/'.$file);
				}
			}
		}
		else{ // copy file
			\OC\Files\Filesystem::copy($src, $dest);
		}
		return true;
	}
*/
}

