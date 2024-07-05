<?php
App::uses('BrmCopyTermLog', 'Model');

/**
 * BrmCopyTermLog Test Case
 */
class BrmCopyTermLogTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_copy_term_log',
		'app.brm_term',
		'app.copy_brm_term'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmCopyTermLog = ClassRegistry::init('BrmCopyTermLog');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmCopyTermLog);

		parent::tearDown();
	}

}
