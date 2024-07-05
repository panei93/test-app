<?php
/**
 * BrmManpowerPlan Fixture
 */
class BrmManpowerPlanFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'brm_manpower_plan';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'brm_term_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'target_year' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false),
		'layer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'comment' => '1. Normal
2. Adjustment', 'charset' => 'utf8mb4'),
		'display_no' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'brm_position_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'unit_salary' => array('type' => 'decimal', 'null' => true, 'default' => null, 'length' => '10,0', 'unsigned' => false),
		'month_1_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_2_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_3_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_4_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_5_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_6_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_7_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_8_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_9_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_10_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_11_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_12_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'1st_half_total' => array('type' => 'decimal', 'null' => true, 'default' => null, 'length' => '14,2', 'unsigned' => false),
		'2nd_half_total' => array('type' => 'decimal', 'null' => true, 'default' => null, 'length' => '14,2', 'unsigned' => false),
		'sub_total' => array('type' => 'decimal', 'null' => true, 'default' => null, 'length' => '14,2', 'unsigned' => false),
		'filling_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'deadline_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'flag' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'created_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'updated_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
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
			'brm_term_id' => 1,
			'target_year' => 1,
			'layer_code' => 'Lore',
			'type' => 'Lorem ipsum dolor sit ame',
			'display_no' => 'Lorem ipsum dolor sit ame',
			'brm_position_id' => 1,
			'unit_salary' => '',
			'month_1_amt' => '',
			'month_2_amt' => '',
			'month_3_amt' => '',
			'month_4_amt' => '',
			'month_5_amt' => '',
			'month_6_amt' => '',
			'month_7_amt' => '',
			'month_8_amt' => '',
			'month_9_amt' => '',
			'month_10_amt' => '',
			'month_11_amt' => '',
			'month_12_amt' => '',
			'1st_half_total' => '',
			'2nd_half_total' => '',
			'sub_total' => '',
			'filling_date' => '2022-10-05 08:37:28',
			'deadline_date' => '2022-10-05 08:37:28',
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2022-10-05 08:37:28',
			'updated_date' => '2022-10-05 08:37:28'
		),
	);

}