<?php
App::uses('Sap', 'Model');

/**
 * Sap Test Case
 */
class SapTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.sap',
		'app.sap_acc_incharge_comment',
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
		$this->Sap = ClassRegistry::init('Sap');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Sap);

		parent::tearDown();
	}

}
