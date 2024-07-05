<?php
App::uses('Menu', 'Model');

/**
 * Menu Test Case
 */
class MenuTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
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
		$this->Menu = ClassRegistry::init('Menu');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Menu);

		parent::tearDown();
	}

}
