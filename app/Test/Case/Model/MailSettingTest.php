<?php
App::uses('Mail', 'Model');

/**
 * Mail Test Case
 */
class MailTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.mail_setting'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Mail = ClassRegistry::init('Mail');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Mail);

		parent::tearDown();
	}

}
