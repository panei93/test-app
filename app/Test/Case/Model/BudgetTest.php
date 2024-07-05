<?php
App::uses('Budget', 'Model');

/**
 * Budget Test Case
 */
class BudgetTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.budget',
		'app.budget_cmt'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Budget = ClassRegistry::init('Budget');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Budget);

		parent::tearDown();
	}

}
