<?php
App::uses('SapAccInchargeComment', 'Model');

/**
 * SapAccInchargeComment Test Case
 */
class SapAccInchargeCommentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.sap_acc_incharge_comment',
		'app.sap',
		'app.sap_acc_manager_approve',
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
		$this->SapAccInchargeComment = ClassRegistry::init('SapAccInchargeComment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->SapAccInchargeComment);

		parent::tearDown();
	}

}
