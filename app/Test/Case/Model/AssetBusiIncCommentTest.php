<?php
App::uses('AssetBusiIncComment', 'Model');

/**
 * AssetBusiIncComment Test Case
 */
class AssetBusiIncCommentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.asset_busi_inc_comment',
		'app.asset',
		'app.event',
		'app.reference_event',
		'app.asset_remove',
		'app.asset_sold',
		'app.asset_busi_mgr_approve'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->AssetBusiIncComment = ClassRegistry::init('AssetBusiIncComment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->AssetBusiIncComment);

		parent::tearDown();
	}

}
