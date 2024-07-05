<?php
App::uses('BudgetComp', 'Model');

/**
 * BudgetComp Test Case
 */
class BudgetCompTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.budget_comp'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BudgetComp = ClassRegistry::init('BudgetComp');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BudgetComp);

		parent::tearDown();
	}

}
