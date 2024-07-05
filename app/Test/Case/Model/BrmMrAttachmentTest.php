<?php
App::uses('BrmMrAttachment', 'Model');

/**
 * BrmMrAttachment Test Case
 */
class BrmMrAttachmentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_mr_attachment'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmMrAttachment = ClassRegistry::init('BrmMrAttachment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmMrAttachment);

		parent::tearDown();
	}

}
