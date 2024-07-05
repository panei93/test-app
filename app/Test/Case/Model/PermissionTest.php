<?php
App::uses('Permission', 'Model');

/**
 * Permission Test Case
 */
class PermissionTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.permission',
		'app.menu',
		'app.role',
		'app.mail_receiver',
		'app.mail',
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
		$this->Permission = ClassRegistry::init('Permission');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Permission);

		parent::tearDown();
	}

}
