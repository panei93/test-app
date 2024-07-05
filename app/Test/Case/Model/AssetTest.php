<?php
App::uses('Asset', 'Model');

/**
 * Asset Test Case
 */
class AssetTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.asset',
		'app.event',
		'app.reference_event',
		'app.asset_remove',
		'app.asset_sold',
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
		$this->Asset = ClassRegistry::init('Asset');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Asset);

		parent::tearDown();
	}

}
