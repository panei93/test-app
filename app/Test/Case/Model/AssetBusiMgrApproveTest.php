<?php
App::uses('AssetBusiMgrApprove', 'Model');

/**
 * AssetBusiMgrApprove Test Case
 */
class AssetBusiMgrApproveTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.asset_busi_mgr_approve',
		'app.asset',
		'app.event',
		'app.reference_event',
		'app.asset_remove',
		'app.asset_sold',
		'app.asset_busi_inc_comment'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->AssetBusiMgrApprove = ClassRegistry::init('AssetBusiMgrApprove');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->AssetBusiMgrApprove);

		parent::tearDown();
	}

}
