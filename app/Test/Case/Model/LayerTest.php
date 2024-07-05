<?php
App::uses('Layer', 'Model');

/**
 * Layer Test Case
 */
class LayerTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.layer',
		'app.layer_type'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Layer = ClassRegistry::init('Layer');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Layer);

		parent::tearDown();
	}

}
