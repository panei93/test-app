<?php
App::uses('BrmBudgetApprove', 'Model');

/**
 * BrmBudgetApprove Test Case
 */
class BrmBudgetApproveTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_budget_approve',
		'app.brm_term'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmBudgetApprove = ClassRegistry::init('BrmBudgetApprove');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmBudgetApprove);

		parent::tearDown();
	}

}
