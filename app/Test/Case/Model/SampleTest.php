<?php
App::uses('Sample', 'Model');

/**
 * Sample Test Case
 */
class SampleTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.sample',
		'app.sample_acc_attachment',
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
		$this->Sample = ClassRegistry::init('Sample');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Sample);

		parent::tearDown();
	}

}
