<?php
App::uses('BrmForecastSummary', 'Model');

/**
 * BrmForecastSummary Test Case
 */
class BrmForecastSummaryTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_forecast_summary',
		'app.brm_term'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmForecastSummary = ClassRegistry::init('BrmForecastSummary');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmForecastSummary);

		parent::tearDown();
	}

}
