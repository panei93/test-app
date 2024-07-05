<?php
App::uses('LaborCostDetail', 'Model');

/**
 * LaborCostDetail Test Case
 */
class LaborCostDetailTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.labor_cost_detail',
		'app.user',
		'app.role',
		'app.menu',
		'app.mail_receiver',
		'app.mail',
		'app.labor_cost',
		'app.position'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->LaborCostDetail = ClassRegistry::init('LaborCostDetail');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->LaborCostDetail);

		parent::tearDown();
	}

}
