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

class MoveController extends Controller {
	private $userId;
	private $l;
	private $lF; // $l of app files
	private $storage;

	public function __construct($AppName, IRequest $request, $ServerContainer, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->storage = $ServerContainer->getUserFolder($UserId);
		$this->l = \OC_L10N::get($AppName);
		$this->lF = \OC_L10N::get('files');
	}
	/**
	 * move/copy $file from $srcDir to $dest
	 * @param bool $copy - true=copy, false=move
	 * @param string $srcDir
	 * @param string $file - semicolon separated filenames
	 * @param string $dest - destination Directory
	 * @NoAdminRequired
	 */
	public function index($srcDir, $srcFile, $dest, $copy){
		if(empty($srcFile) || empty($dest))
			return array("status"=>"error","message"=>$this->l->t('No data supplied.'));

		// prepare file names
		$files = explode(';', $srcFile);
		if(!is_array($files)) $files = array($files);
		$files = array_filter($files); // remove empty elements

		$srcDir  .='/';
		$dest .='/';
		if($srcDir=='//') $srcDir = '/';
		if($dest=='//') $dest = '/';
		if($srcDir==$dest) return array("status"=>"error","message"=>$this->l->t('Src and Dest are not allowed to be the same location!'));

		$error = 0;
		$err = array();
		$ttt = array();
		foreach($files as $file){
			$to = $dest.$file;
			$fromPath = $srcDir.$file;
			$from = $this->storage->get($fromPath);
			var_dump($to, $fromPath);
			if($this->storage->nodeExists($to)){
				$err['exists'][] = $file;
			}
			else{
				$ttt[]=$from;
				if($copy){
					$ttt[] = $from->copy($to);
				}
				else{
					$ttt[] = $from->move($to);
				}
			}
				/*
			   
				if($copy && copyRec($source,$target)){
				//copied, do not add to $files
			}
			else if(!$copy && $this->storage($source,$target)){
				// here is the code when mv was successfull
				$files[] = $f;
			}
			else{
				$err['failed'][] = $f;
			}
				 */

		}
		var_dump($ttt);
		$msg =array();
		if(!empty($err['exists'])) $msg[] = $this->lF->t("Could not move %s - File with this name already exists", array(implode(", ",$err['exists'])));
		if(!empty($err['failed'])) $msg[] = $this->lF->t("Could not move %s", array(implode(", ",$err['failed'])));
		$msg = implode("<br>\n",$msg);
		$status = (empty($msg)?'success':'error');
		$result = array('status'=>$status,'action'=>'mv','name'=>$files,'message'=>$msg);
		return $result;

	}


	/*
	private function copyRec($src,$dest){
		if(\OC\Files\Filesystem::is_dir($src)){ // copy dir
			if($dh = \OC\Files\Filesystem::opendir($src)){
				\OC\Files\Filesystem::mkdir($dest);
				while(($file = readdir($dh)) !== false){
					if(in_array($file,array('.','..'))) continue;
					if(\OC\Files\Filesystem::is_dir($src.'/'.$file)) copyRec($src.'/'.$file,$dest.'/'.$file);
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

