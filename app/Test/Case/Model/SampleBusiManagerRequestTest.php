<?php
App::uses('SampleBusiManagerRequest', 'Model');

/**
 * SampleBusiManagerRequest Test Case
 */
class SampleBusiManagerRequestTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.sample_busi_manager_request',
		'app.sample',
		'app.sample_acc_attachment',
		'app.sample_acc_request',
		'app.sample_busi_admin_approve',
		'app.sample_checklist',
		'app.result',
		'app.sample_test_result'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->SampleBusiManagerRequest = ClassRegistry::init('SampleBusiManagerRequest');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->SampleBusiManagerRequest);

		parent::tearDown();
	}

}
