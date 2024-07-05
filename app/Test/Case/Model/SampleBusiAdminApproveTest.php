<?php
App::uses('SampleBusiAdminApprove', 'Model');

/**
 * SampleBusiAdminApprove Test Case
 */
class SampleBusiAdminApproveTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.sample_busi_admin_approve',
		'app.sample',
		'app.sample_acc_attachment',
		'app.sample_acc_request',
		'app.sample_busi_manager_request',
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
		$this->SampleBusiAdminApprove = ClassRegistry::init('SampleBusiAdminApprove');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->SampleBusiAdminApprove);

		parent::tearDown();
	}

}
