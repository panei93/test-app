<?php
App::uses('SapBusiManagerApprove', 'Model');

/**
 * SapBusiManagerApprove Test Case
 */
class SapBusiManagerApproveTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.sap_busi_manager_approve',
		'app.sap',
		'app.sap_acc_incharge_comment',
		'app.sap_acc_manager_approve',
		'app.sap_acc_submanager_comment',
		'app.sap_busi_admin_comment',
		'app.sap_busi_incharge_comment'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->SapBusiManagerApprove = ClassRegistry::init('SapBusiManagerApprove');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->SapBusiManagerApprove);

		parent::tearDown();
	}

}
