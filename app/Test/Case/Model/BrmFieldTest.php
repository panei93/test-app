<?php
App::uses('BrmField', 'Model');

/**
 * BrmField Test Case
 */
class BrmFieldTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_field',
		'app.brm_manpower_plan_ot',
		'app.brm_position'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmField = ClassRegistry::init('BrmField');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmField);

		parent::tearDown();
	}

}
