<?php
App::uses('AssetRemove', 'Model');

/**
 * AssetRemove Test Case
 */
class AssetRemoveTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.asset_remove',
		'app.event',
		'app.reference_event',
		'app.asset_sold',
		'app.asset',
		'app.asset_busi_inc_comment',
		'app.asset_busi_mgr_approve'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->AssetRemove = ClassRegistry::init('AssetRemove');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->AssetRemove);

		parent::tearDown();
	}

}
