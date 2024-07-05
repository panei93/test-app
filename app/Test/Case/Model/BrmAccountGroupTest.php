<?php
App::uses('BrmAccountGroup', 'Model');

/**
 * BrmAccountGroup Test Case
 */
class BrmAccountGroupTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_account_group'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmAccountGroup = ClassRegistry::init('BrmAccountGroup');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmAccountGroup);

		parent::tearDown();
	}

}
