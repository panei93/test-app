<?php
App::uses('BrmTermDeadline', 'Model');

/**
 * BrmTermDeadline Test Case
 */
class BrmTermDeadlineTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_term_deadline',
		'app.brm_term',
		'app.brm_budget_approve',
		'app.brm_budget_prime',
		'app.brm_budget_summary',
		'app.brm_budget',
		'app.brm_cash_flow',
		'app.brm_ceo_comment',
		'app.brm_copy_term_log',
		'app.brm_cron_log',
		'app.brm_forecast_summary',
		'app.brm_investment',
		'app.brm_main_budget',
		'app.brm_manpower_plan',
		'app.brm_manpower_plan_ot',
		'app.brm_sm_explain'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmTermDeadline = ClassRegistry::init('BrmTermDeadline');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmTermDeadline);

		parent::tearDown();
	}

}
