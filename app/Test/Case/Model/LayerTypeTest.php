<?php
App::uses('LayerType', 'Model');

/**
 * LayerType Test Case
 */
class LayerTypeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.layer_type',
		'app.layer',
		'app.manager'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->LayerType = ClassRegistry::init('LayerType');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->LayerType);

		parent::tearDown();
	}

}
