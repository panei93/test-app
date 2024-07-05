<?php
App::uses('Position', 'Model');

/**
 * Position Test Case
 */
class PositionTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.position',
		'app.labor_cost_detail',
		'app.user',
		'app.role',
		'app.menu',
		'app.mail_receiver',
		'app.mail',
		'app.labor_cost'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Position = ClassRegistry::init('Position');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Position);

		parent::tearDown();
	}

}
