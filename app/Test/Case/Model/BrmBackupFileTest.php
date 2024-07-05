<?php
App::uses('BrmBackupFile', 'Model');

/**
 * BrmBackupFile Test Case
 */
class BrmBackupFileTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.brm_backup_file',
		'app.term',
		'app.created',
		'app.updated'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->BrmBackupFile = ClassRegistry::init('BrmBackupFile');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->BrmBackupFile);

		parent::tearDown();
	}

}
