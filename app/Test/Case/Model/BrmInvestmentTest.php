<?php
App::uses('BrmInvestment', 'Model');

/**
 * BrmInvestment Test Case
 */
class BrmInvestmentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_investment',
		'app.brm_term'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmInvestment = ClassRegistry::init('BrmInvestment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmInvestment);

		parent::tearDown();
	}

}
