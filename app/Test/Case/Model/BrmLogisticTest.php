<?php
App::uses('BrmLogistic', 'Model');

/**
 * BrmLogistic Test Case
 */
class BrmLogisticTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_logistic'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmLogistic = ClassRegistry::init('BrmLogistic');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmLogistic);

		parent::tearDown();
	}

}
