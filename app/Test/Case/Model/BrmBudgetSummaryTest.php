<?php
App::uses('BrmBudgetSummary', 'Model');

/**
 * BrmBudgetSummary Test Case
 */
class BrmBudgetSummaryTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_budget_summary',
		'app.brm_term'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmBudgetSummary = ClassRegistry::init('BrmBudgetSummary');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmBudgetSummary);

		parent::tearDown();
	}

}
