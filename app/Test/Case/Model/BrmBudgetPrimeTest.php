<?php
App::uses('BrmBudgetPrime', 'Model');

/**
 * BrmBudgetPrime Test Case
 */
class BrmBudgetPrimeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_budget_prime',
		'app.brm_term',
		'app.brm_account',
		'app.brm_account_pair',
		'app.brm_account_setup',
		'app.brm_saccount',
		'app.brm_main_budget',
		'app.brm_main_result'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmBudgetPrime = ClassRegistry::init('BrmBudgetPrime');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmBudgetPrime);

		parent::tearDown();
	}

}
