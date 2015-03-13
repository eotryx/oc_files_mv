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

namespace OCA\Files_mv\Controller;

use PHPUnit_Framework_TestCase;

use OCP\AppFramework\Http\TemplateResponse;


class CompleteControllerTest extends PHPUnit_Framework_TestCase {
	private $appName = 'files_mv';

	private $controller;

	private $storage;
	private $l;
	private $folder;
	private $request;
	private $map;

	/**
	 * generate a mock folder
	 * @param string $name
	 * @param bool $updateAble
	 * @param array $map array(array($name, $ret_obj), ...)
	 * @param array $dirList array($folder, $file, $folder, ...)
	 *
	 */
	private function getMockFolder($name, $updateAble, $map, $dirList){
		$obj = $this->getMock('\OCP\Files\Folder',array(),array(),'',false);

		$obj->expects($this->any())
			->method('getType')
			->will($this->returnValue(\OCP\Files\FileInfo::TYPE_FOLDER));
		$obj->expects($this->any())
			->method('get')
			->will($this->returnValueMap($map));
		$obj->expects($this->any())
			->method('getName')
			->will($this->returnValue($name));
		$obj->expects($this->any())
			->method('isUpdateable')
			->will($this->returnValue($updateAble));
		$obj->expects($this->any())
			->method('getDirectoryListing')
			->will($this->returnValue($dirList));
		//TODO: maybe modify this method to something more realistic?
		$obj->expects($this->any())
			->method('nodeExists')
			->will($this->returnValue(true));

		$this->map[] = array($name, $obj);
		return $obj;
	}
	private function getMockFolders($names, $updateAble, $mapIn, $dirList){
		$list = $map = array();
		foreach(explode(',',$names) as $k=>$name){
			$obj = $this->getMockFolder($name, $updateAble[$k], $mapIn[$k],$dirList[$k]);
			$list[] = $obj;
			$map[] = array($name, $obj);
		}
		return array($list,$map);
	}
	/**
	 * generate a mock file
	 * @param string $name
	 */
	private function getMockFiles($names){
		$list = $map = array();
		foreach(explode(',',$names) as $name){
			$obj = $this->getMock('\OCP\Files\File',array(),array(),'',false);

			$obj->expects($this->any())
				->method('getType')
				->will($this->returnValue(\OCP\Files\FileInfo::TYPE_FILE));
			$list[] = $obj;
			$map[] = array($name,$obj);
		}
		return array($list,$map);
	}

	public function setUp() {
		$this->request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$this->l = $this->getMock('\OC_L10N', array('t'),array(),'',false);
		$this->l->expects($this->any())
			->method('t')
			->will($this->returnArgument(0));
		$this->storage = null;
	}
	
	/**
	 * create the controller and storage
	 */
	private function setController($map, $list){
		$folder = $this->getMockFolder('/',true,$map,$list);
		$this->storage = $this->getMockFolder('/', true, $this->map, array($folder));

		$this->controller = new CompleteController(
			$this->appName,
		   	$this->request,
			$this->l,
			$this->storage
		);
	}

	
	public function testNoDir(){
		// test with no directories existing
		list($list,$map) = $this->getMockFiles('test.txt,keks.odt');

		$this->setController($map,$list);

		$result = $this->controller->index('/test.txt','');
		$this->assertEmpty($result);

		// should produce the same output, as the directory can start with or without a slash
		$result = $this->controller->index('/test.txt','/');
		$this->assertEmpty($result);
	}

	//TODO: figure out how to do this test for one layer listing and for multiple layers of listing
	public function testOneLayerDir(){
		list($list, $map) = $this->getMockFolders(
			'/testA,/testB,/testC',
			array(true,true,true),
			array(
				array(),
				array(),
				array()
			),
			array(
				null,
				null,
				null
			)
		);
		$this->setController($map,$list);
		//$result = $this->controller->index('/file.txt','');

	}
}
