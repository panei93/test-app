<?php
/**
 * BrmPosition Fixture
 */
class BrmPositionFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'target_year' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false),
		'brm_field_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'position_name_jp' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'position_name_en' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'unit_salary' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'percentage' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'display_no' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 1, 'unsigned' => false),
		'edit_flag' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'flag' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'created_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'updated_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
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
			'brm_field_id' => 1,
			'position_name_jp' => 'Lorem ipsum dolor sit amet',
			'position_name_en' => 'Lorem ipsum dolor sit amet',
			'unit_salary' => '',
			'percentage' => 'Lorem ipsum dolor sit amet',
			'display_no' => 1,
			'edit_flag' => 'Lorem ipsum dolor sit ame',
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2022-10-05 08:35:57',
			'updated_date' => '2022-10-05 08:35:57'
		),
	);

}
