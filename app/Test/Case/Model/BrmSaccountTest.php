<?php
App::uses('BrmSaccount', 'Model');

/**
 * BrmSaccount Test Case
 */
class BrmSaccountTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_saccount',
		'app.brm_account',
		'app.brm_account_setup',
		'app.brm_budget'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmSaccount = ClassRegistry::init('BrmSaccount');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmSaccount);

		parent::tearDown();
	}

}
