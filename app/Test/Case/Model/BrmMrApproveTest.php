<?php
App::uses('BrmMrApprove', 'Model');

/**
 * BrmMrApprove Test Case
 */
class BrmMrApproveTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_mr_approve'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmMrApprove = ClassRegistry::init('BrmMrApprove');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmMrApprove);

		parent::tearDown();
	}

}
