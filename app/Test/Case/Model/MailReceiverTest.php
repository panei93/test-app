<?php
App::uses('MailReceiver', 'Model');

/**
 * MailReceiver Test Case
 */
class MailReceiverTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.mail_receiver',
		'app.mail',
		'app.role',
		'app.menu',
		'app.user',
		'app.labor_cost_detail',
		'app.position',
		'app.labor_cost'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->MailReceiver = ClassRegistry::init('MailReceiver');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->MailReceiver);

		parent::tearDown();
	}

}
