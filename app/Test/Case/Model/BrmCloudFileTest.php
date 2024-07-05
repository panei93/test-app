<?php
App::uses('BrmCloudFile', 'Model');

/**
 * BrmCloudFile Test Case
 */
class BrmCloudFileTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_cloud_file'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmCloudFile = ClassRegistry::init('BrmCloudFile');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmCloudFile);

		parent::tearDown();
	}

}
