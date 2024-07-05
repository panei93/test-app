<?php
App::uses('LoginLog', 'Model');

/**
 * LoginLog Test Case
 */
class LoginLogTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.login_log'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->LoginLog = ClassRegistry::init('LoginLog');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->LoginLog);

		parent::tearDown();
	}

}
