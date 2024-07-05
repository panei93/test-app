<?php
App::uses('BudgetSng', 'Model');

/**
 * BudgetSng Test Case
 */
class BudgetSngTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.budget_sng'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BudgetSng = ClassRegistry::init('BudgetSng');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BudgetSng);

		parent::tearDown();
	}

}
