<?php
App::uses('SapBusiAdminComment', 'Model');

/**
 * SapBusiAdminComment Test Case
 */
class SapBusiAdminCommentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.sap_busi_admin_comment',
		'app.sap',
		'app.sap_acc_incharge_comment',
		'app.sap_acc_manager_approve',
		'app.sap_acc_submanager_comment',
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
		$this->SapBusiAdminComment = ClassRegistry::init('SapBusiAdminComment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->SapBusiAdminComment);

		parent::tearDown();
	}

}
