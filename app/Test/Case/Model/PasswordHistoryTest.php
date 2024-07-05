<?php
App::uses('PasswordHistory', 'Model');

/**
 * PasswordHistory Test Case
 */
class PasswordHistoryTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.password_history'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->PasswordHistory = ClassRegistry::init('PasswordHistory');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->PasswordHistory);

		parent::tearDown();
	}

}
