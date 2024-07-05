<?php
App::uses('Rexchange', 'Model');

/**
 * Rexchange Test Case
 */
class RexchangeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.rexchange'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Rexchange = ClassRegistry::init('Rexchange');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Rexchange);

		parent::tearDown();
	}

}
