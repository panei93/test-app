<?php
/**
 * Account Fixture
 */
class AccountFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'account_type_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'account_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'account_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'account_type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'display_number' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false),
		'postfix' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 5, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'memo' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'base_param' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'calculation_formula' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'flag' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'created_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'updated_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'id' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'account_type_id' => 1,
			'account_code' => 'Lorem ip',
			'account_name' => 'Lorem ipsum dolor sit amet',
			'account_type' => 'Lorem ipsum dolor sit ame',
			'display_number' => 1,
			'postfix' => 'Lor',
			'memo' => 'Lorem ipsum dolor sit amet',
			'base_param' => 'Lorem ipsum dolor sit amet',
			'calculation_formula' => 'Lorem ipsum dolor sit amet',
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2022-09-07 11:29:54',
			'updated_date' => '2022-09-07 11:29:54'
		),
	);

}
