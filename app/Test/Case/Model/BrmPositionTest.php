<?php
App::uses('BrmPosition', 'Model');

/**
 * BrmPosition Test Case
 */
class BrmPositionTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_position',
		'app.brm_field',
		'app.brm_manpower_plan'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmPosition = ClassRegistry::init('BrmPosition');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmPosition);

		parent::tearDown();
	}

}
