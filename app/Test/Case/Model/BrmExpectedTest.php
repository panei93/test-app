<?php
App::uses('BrmExpected', 'Model');

/**
 * BrmExpected Test Case
 */
class BrmExpectedTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_expected',
		'app.brm_term',
		'app.brm_budget_approve',
		'app.brm_budget_prime',
		'app.brm_account',
		'app.brm_account_pair',
		'app.brm_account_setup',
		'app.brm_saccount',
		'app.brm_budget',
		'app.brm_main_budget',
		'app.brm_main_result',
		'app.brm_budget_summary',
		'app.brm_cash_flow',
		'app.brm_ceo_comment',
		'app.brm_copy_term_log',
		'app.copy_brm_term',
		'app.brm_cron_log',
		'app.brm_forecast_summary',
		'app.brm_investment',
		'app.brm_manpower_plan',
		'app.brm_position',
		'app.brm_field',
		'app.brm_manpower_plan_ot',
		'app.brm_sm_explain',
		'app.brm_term_deadline'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmExpected = ClassRegistry::init('BrmExpected');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmExpected);

		parent::tearDown();
	}

}
