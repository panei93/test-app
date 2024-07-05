<?php
App::uses('AccountSetting', 'Model');

/**
 * AccountSetting Test Case
 */
class AccountSettingTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.account_setting',
		'app.layer',
		'app.layer_type',
		'app.account',
		'app.account_type',
		'app.menu'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->AccountSetting = ClassRegistry::init('AccountSetting');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->AccountSetting);

		parent::tearDown();
	}

}
