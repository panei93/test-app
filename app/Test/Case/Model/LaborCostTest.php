<?php
App::uses('LaborCost', 'Model');

/**
 * LaborCost Test Case
 */
class LaborCostTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.labor_cost',
		'app.position',
		'app.labor_cost_detail',
		'app.user',
		'app.role',
		'app.menu',
		'app.mail_receiver',
		'app.mail'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->LaborCost = ClassRegistry::init('LaborCost');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->LaborCost);

		parent::tearDown();
	}

}
