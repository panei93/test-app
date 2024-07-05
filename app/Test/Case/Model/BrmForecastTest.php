<?php
App::uses('BrmForecast', 'Model');

/**
 * BrmForecast Test Case
 */
class BrmForecastTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_forecast'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmForecast = ClassRegistry::init('BrmForecast');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmForecast);

		parent::tearDown();
	}

}
