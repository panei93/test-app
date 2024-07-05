<?php
App::uses('LaborCostAdjustment', 'Model');

/**
 * LaborCostAdjustment Test Case
 */
class LaborCostAdjustmentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.labor_cost_adjustment'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->LaborCostAdjustment = ClassRegistry::init('LaborCostAdjustment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->LaborCostAdjustment);

		parent::tearDown();
	}

}
