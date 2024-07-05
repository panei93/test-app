<?php
App::uses('BrmActualResult', 'Model');

/**
 * BrmActualResult Test Case
 */
class BrmActualResultTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_actual_result'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmActualResult = ClassRegistry::init('BrmActualResult');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmActualResult);

		parent::tearDown();
	}

}
