<?php
App::uses('BrmCashFlow', 'Model');

/**
 * BrmCashFlow Test Case
 */
class BrmCashFlowTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_cash_flow',
		'app.brm_term'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmCashFlow = ClassRegistry::init('BrmCashFlow');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmCashFlow);

		parent::tearDown();
	}

}
