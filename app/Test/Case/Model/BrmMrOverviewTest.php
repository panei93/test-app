<?php
App::uses('BrmMrOverview', 'Model');

/**
 * BrmMrOverview Test Case
 */
class BrmMrOverviewTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_mr_overview'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmMrOverview = ClassRegistry::init('BrmMrOverview');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmMrOverview);

		parent::tearDown();
	}

}
