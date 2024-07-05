<?php
App::uses('Event', 'Model');

/**
 * Event Test Case
 */
class EventTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.event',
		'app.reference_event',
		'app.asset_remove',
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
		$this->Event = ClassRegistry::init('Event');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Event);

		parent::tearDown();
	}

}
