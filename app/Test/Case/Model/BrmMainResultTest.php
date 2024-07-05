<?php
App::uses('BrmMainResult', 'Model');

/**
 * BrmMainResult Test Case
 */
class BrmMainResultTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_main_result',
		'app.brm_account',
		'app.brm_account_pair',
		'app.brm_account_setup',
		'app.brm_saccount',
		'app.brm_budget_prime',
		'app.brm_term',
		'app.brm_main_budget'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmMainResult = ClassRegistry::init('BrmMainResult');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmMainResult);

		parent::tearDown();
	}

}
