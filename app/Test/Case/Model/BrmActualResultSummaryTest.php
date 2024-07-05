<?php
App::uses('BrmActualResultSummary', 'Model');

/**
 * BrmActualResultSummary Test Case
 */
class BrmActualResultSummaryTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_actual_result_summary'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmActualResultSummary = ClassRegistry::init('BrmActualResultSummary');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmActualResultSummary);

		parent::tearDown();
	}

}
