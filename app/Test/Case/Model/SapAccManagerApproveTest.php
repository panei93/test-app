<?php
App::uses('SapAccManagerApprove', 'Model');

/**
 * SapAccManagerApprove Test Case
 */
class SapAccManagerApproveTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.sap_acc_manager_approve',
		'app.sap',
		'app.sap_acc_incharge_comment',
		'app.sap_acc_submanager_comment',
		'app.sap_busi_admin_comment',
		'app.sap_busi_incharge_comment',
		'app.sap_busi_manager_approve'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->SapAccManagerApprove = ClassRegistry::init('SapAccManagerApprove');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->SapAccManagerApprove);

		parent::tearDown();
	}

}
