<?php
/**
 * BrmAccountGroup Fixture
 */
class BrmAccountGroupFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'group_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 2, 'key' => 'unique', 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'group_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 200, 'key' => 'unique', 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'flag' => array('type' => 'string', 'null' => false, 'default' => '1', 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'group_code_UNIQUE' => array('column' => 'group_code', 'unique' => 1),
			'group_name_UNIQUE' => array('column' => 'group_name', 'unique' => 1)
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
			'group_code' => '',
			'group_name' => 'Lorem ipsum dolor sit amet',
			'flag' => 'Lorem ipsum dolor sit ame'
		),
	);

}
