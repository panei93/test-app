<?php
/**
 * LaborCostDetail Fixture
 */
class LaborCostDetailFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'target_year' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'position_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'layer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'adjust_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'business_type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'person_count' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'comment' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'flag' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'created_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'updated_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
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
			'target_year' => 1,
			'user_id' => 1,
			'position_id' => 1,
			'layer_code' => 'Lore',
			'adjust_name' => 'Lorem ipsum dolor sit amet',
			'business_type' => 'Lorem ipsum dolor sit ame',
			'person_count' => 1,
			'comment' => 'Lorem ipsum dolor sit amet',
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2022-09-07 11:30:04',
			'updated_date' => '2022-09-07 11:30:04'
		),
	);

}
