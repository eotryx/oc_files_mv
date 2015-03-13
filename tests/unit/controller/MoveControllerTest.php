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


class MoveControllerTest extends PHPUnit_Framework_TestCase {
	private $appName = 'files_mv';

	private $controller;
	private $userId = 'john';

	private $storage;
	private $l;

	private function setL(){
		$this->l->expects($this->any())
			->method('t')
			->will($this->returnArgument(0));
	}

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$this->l = $this->getMock('\OC_L10N', array('t'),array(),'',false);
		$this->setL();
		$this->storage = $this->getMock('\OCP\Files\Folder',array(),array(),'',false);
		$this->storage->expects($this->any())
			->method('get')
			->will($this->returnValue($this->storage));
		$this->storage->expects($this->any())
			->method('getFullPath')
			->will($this->returnArgument(0));

		$this->controller = new MoveController(
			$this->appName,
		   	$request,
			$this->l,
			$this->storage
		);
	}


	public function testErrorParams(){
		//test trivial error with no content
		$result = $this->controller->index(null,null,null);
		$this->assertEquals($result,array('status'=>'error','message'=>'No data supplied.'));

		//test move to same location
		$path = '/test';
		$result = $this->controller->index($path, 'asdf', $path);
		$this->assertEquals($result, array('status'=>'error','message'=>'Src and Dest are not allowed to be the same location!'));
	}

	public function testFileExists(){
		//File already exists
		$file = 'Keks.txt';
		$errStr = 'Could not move %s - File with this name already exists';

		$this->storage->expects($this->any())
			->method('nodeExists')
			->will($this->returnValue(true));

		$this->l->expects($this->once())
			->method('t')
			->with(
				$this->equalTo($errStr),
				$this->equalTo(array($file))
			);

		$result = $this->controller->index('/', $file, '/test');
		$this->assertEquals($result, array(
			'action'=>'mv',
			'status'=>'error',
			'message'=>$errStr,
			'name'=>array()
			)
		);
		$this->setL();
	}

	public function testSingleFileMove() {
		//File can be moved
		$file = 'Keks.txt';
		$this->doMove(
			$file,
			1,
			0,
			array($file),
			false
		);
	}
	public function testMultiFileMove() {
		//File can be moved
		$file = 'Keks.txt;Keks2.txt';
		$this->doMove(
			$file,
			2,
			0,
			explode(';', $file),
			false
		);
	}

	public function testSingleFileCopy(){
		$file = 'Test.txt';
		$this->doMove(
			$file,
			0,
			1,
			array(),
			true);
	}

	public function testMultiFileCopy(){
		$file = 'Test.txt;Keks.docx';
		$this->doMove(
			$file,
			0,
			2,
			array(),
			true);
	}

	private function doMove($file,$countMove,$countCopy,$resFile,$copy){
		$from = '/';
		$to = '/test/';
		$this->storage->expects($this->any())
			->method('nodeExists')
			->will($this->returnValue(false));
		$this->storage->expects($this->exactly($countMove))
			->method('move');
		$this->storage->expects($this->exactly($countCopy))
			->method('copy');
		$result = $this->controller->index($from, $file, $to, $copy);
		$this->assertEquals($result, array(
			'action'=>'mv',
			'status'=>'success',
			'message'=>'',
			'name'=>$resFile
			)
		);
	}

}
