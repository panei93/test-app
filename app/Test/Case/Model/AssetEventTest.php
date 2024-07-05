<?php
App::uses('AssetEvent', 'Model');

/**
 * AssetEvent Test Case
 */
class AssetEventTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.asset_event',
		'app.reference_event',
		'app.asset_remove',
		'app.event',
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
		$this->AssetEvent = ClassRegistry::init('AssetEvent');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->AssetEvent);

		parent::tearDown();
	}

}
