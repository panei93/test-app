<?php
App::uses('BudgetHyoka', 'Model');

/**
 * BudgetHyoka Test Case
 */
class BudgetHyokaTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.budget_hyoka'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BudgetHyoka = ClassRegistry::init('BudgetHyoka');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BudgetHyoka);

		parent::tearDown();
	}

}
