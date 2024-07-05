<?php
App::uses('RtaxFee', 'Model');

/**
 * RtaxFee Test Case
 */
class RtaxFeeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.rtax_fee'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->RtaxFee = ClassRegistry::init('RtaxFee');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->RtaxFee);

		parent::tearDown();
	}

}
