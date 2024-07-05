<?php
App::uses('BudgetPoint', 'Model');

/**
 * BudgetPoint Test Case
 */
class BudgetPointTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.budget_point'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BudgetPoint = ClassRegistry::init('BudgetPoint');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BudgetPoint);

		parent::tearDown();
	}

}
