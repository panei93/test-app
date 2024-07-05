<?php
/**
 * AccountSetting Fixture
 */
class AccountSettingFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'target_year' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 4, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'layer_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'account_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'menu_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'label_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'display_order' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 3, 'unsigned' => false),
		'flag' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'created_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'updated_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'target_year' => 'Lo',
			'layer_id' => 1,
			'account_id' => 1,
			'menu_id' => 1,
			'label_name' => 'Lorem ipsum dolor sit amet',
			'display_order' => 1,
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2023-01-24 05:51:14',
			'updated_date' => '2023-01-24 05:51:14'
		),
	);

}
