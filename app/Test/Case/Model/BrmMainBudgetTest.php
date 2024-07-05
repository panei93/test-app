<?php
App::uses('BrmMainBudget', 'Model');

/**
 * BrmMainBudget Test Case
 */
class BrmMainBudgetTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_main_budget',
		'app.brm_term',
		'app.brm_account',
		'app.brm_account_pair',
		'app.brm_account_setup',
		'app.brm_saccount',
		'app.brm_budget_prime',
		'app.brm_main_result'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmMainBudget = ClassRegistry::init('BrmMainBudget');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmMainBudget);

		parent::tearDown();
	}

}
