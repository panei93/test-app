<?php
App::uses('BrmCronTask', 'Model');

/**
 * BrmCronTask Test Case
 */
class BrmCronTaskTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_cron_task'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmCronTask = ClassRegistry::init('BrmCronTask');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmCronTask);

		parent::tearDown();
	}

}
