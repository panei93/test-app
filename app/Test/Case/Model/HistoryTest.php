<?php
App::uses('History', 'Model');

/**
 * History Test Case
 */
class HistoryTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.history',
		'app.org'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->History = ClassRegistry::init('History');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->History);

		parent::tearDown();
	}

}
