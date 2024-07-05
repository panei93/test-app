<?php
App::uses('SampleAccAttachment', 'Model');

/**
 * SampleAccAttachment Test Case
 */
class SampleAccAttachmentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.sample_acc_attachment',
		'app.sample',
		'app.sample_acc_request',
		'app.sample_busi_admin_approve',
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
		$this->SampleAccAttachment = ClassRegistry::init('SampleAccAttachment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->SampleAccAttachment);

		parent::tearDown();
	}

}
