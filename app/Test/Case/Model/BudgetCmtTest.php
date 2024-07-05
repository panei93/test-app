<?php
App::uses('BudgetCmt', 'Model');

/**
 * BudgetCmt Test Case
 */
class BudgetCmtTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.budget_cmt',
		'app.budget'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BudgetCmt = ClassRegistry::init('BudgetCmt');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BudgetCmt);

		parent::tearDown();
	}

}
