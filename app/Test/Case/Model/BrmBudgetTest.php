<?php
App::uses('BrmBudget', 'Model');

/**
 * BrmBudget Test Case
 */
class BrmBudgetTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_budget',
		'app.brm_term',
		'app.brm_saccount'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmBudget = ClassRegistry::init('BrmBudget');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmBudget);

		parent::tearDown();
	}

}
