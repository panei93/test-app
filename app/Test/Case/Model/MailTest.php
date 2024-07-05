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
		'app.mail',
		'app.mail_receiver',
		'app.role',
		'app.menu',
		'app.user',
		'app.labor_cost_detail',
		'app.position',
		'app.labor_cost',
		'app.permission'
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
