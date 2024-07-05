<?php
App::uses('BrmAccountSetup', 'Model');

/**
 * BrmAccountSetup Test Case
 */
class BrmAccountSetupTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_account_setup',
		'app.brm_account',
		'app.brm_account_pair',
		'app.brm_budget_prime',
		'app.brm_term',
		'app.brm_main_budget',
		'app.brm_main_result',
		'app.brm_saccount'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmAccountSetup = ClassRegistry::init('BrmAccountSetup');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmAccountSetup);

		parent::tearDown();
	}

}
