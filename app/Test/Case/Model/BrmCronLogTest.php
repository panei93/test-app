<?php
App::uses('BrmCronLog', 'Model');

/**
 * BrmCronLog Test Case
 */
class BrmCronLogTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_cron_log',
		'app.brm_term'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmCronLog = ClassRegistry::init('BrmCronLog');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmCronLog);

		parent::tearDown();
	}

}
