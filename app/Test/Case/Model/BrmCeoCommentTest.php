<?php
App::uses('BrmCeoComment', 'Model');

/**
 * BrmCeoComment Test Case
 */
class BrmCeoCommentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_ceo_comment',
		'app.brm_term'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmCeoComment = ClassRegistry::init('BrmCeoComment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmCeoComment);

		parent::tearDown();
	}

}
