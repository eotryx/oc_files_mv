<?php
/**
 * ownCloud - filesmv
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author eotryx <mhfiedler@gmx.de>
 * @copyright eotryx 2015
 */

namespace OCA\Files_Mv\Controller;

use PHPUnit_Framework_TestCase;

use OCP\AppFramework\Http\TemplateResponse;


class CompleteControllerTest extends \Test\TestCase {
	private static $user;
	/**
	 * @var \OCA\FIles_Mv\App
	 */
	private $files_mv;

	private $originalStorage;


	public function setUp() {
		parent::setUp();
		/**
		 * don't ask me, ask the guy developing files/tests/ajax_rename.php
		 */
		$this->originalStorage = \OC\Files\Filesystem::getStorage('/');

		// mock OC_L10n
		if (!self::$user) {
			self::$user = uniqid();
		}
		\OC_User::createUser(self::$user, 'password');
		\OC_User::setUserId(self::$user);

		\OC\Files\Filesystem::init(self::$user, '/' . self::$user . '/files');

	
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();

		$this->controller = new PageController(
			'filesmv', $request, $this->userId
		);
	}


	public function testIndex() {
		$result = $this->controller->index();

		$this->assertEquals(['user' => 'john'], $result->getParams());
		$this->assertEquals('main', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}


	public function testEcho() {
		$result = $this->controller->doEcho('hi');
		$this->assertEquals(['echo' => 'hi'], $result->getData());
	}


}
