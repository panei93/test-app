<?php
App::uses('SampleChecklist', 'Model');

/**
 * SampleChecklist Test Case
 */
class SampleChecklistTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.sample_checklist',
		'app.sample',
		'app.sample_acc_attachment',
		'app.sample_acc_request',
		'app.sample_busi_admin_approve',
		'app.sample_busi_manager_request',
		'app.sample_test_result',
		'app.result'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->SampleChecklist = ClassRegistry::init('SampleChecklist');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->SampleChecklist);

		parent::tearDown();
	}

}
