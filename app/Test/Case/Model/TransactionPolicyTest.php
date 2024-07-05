<?php
App::uses('TransactionPolicy', 'Model');

/**
 * TransactionPolicy Test Case
 */
class TransactionPolicyTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.transaction_policy'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->TransactionPolicy = ClassRegistry::init('TransactionPolicy');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->TransactionPolicy);

		parent::tearDown();
	}

}
