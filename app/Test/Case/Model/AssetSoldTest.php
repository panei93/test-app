<?php
App::uses('AssetSold', 'Model');

/**
 * AssetSold Test Case
 */
class AssetSoldTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.asset_sold',
		'app.event',
		'app.reference_event',
		'app.asset_remove',
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
		$this->AssetSold = ClassRegistry::init('AssetSold');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->AssetSold);

		parent::tearDown();
	}

}
