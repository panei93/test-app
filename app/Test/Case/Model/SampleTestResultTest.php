<?php
App::uses('SampleTestResults', 'Model');

/**
 * SampleTestResults Test Case
 */
class SampleTestResultsTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.sample_test_result',
		'app.sample',
		'app.sample_acc_attachment',
		'app.sample_acc_request',
		'app.sample_busi_admin_approve',
		'app.sample_busi_manager_request',
		'app.sample_checklist',
		'app.result'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->SampleTestResults = ClassRegistry::init('SampleTestResults');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->SampleTestResults);

		parent::tearDown();
	}

}
