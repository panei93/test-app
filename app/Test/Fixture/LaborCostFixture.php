<?php
/**
 * LaborCost Fixture
 */
class LaborCostFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'target_year' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false),
		'position_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'layer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'adjust_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'person_count' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'b_person_count' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'common_expense' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'b_person_total' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'labor_unit' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'corp_unit' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'yearly_labor_cost' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'unit_labor_cost' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'adjust_labor_cost' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'yearly_corpo_cost' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'unit_corpo_cost' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'adjust_corpo_cost' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
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
			'target_year' => 1,
			'position_id' => 1,
			'user_id' => 1,
			'layer_code' => 'Lore',
			'adjust_name' => 'Lorem ipsum dolor sit amet',
			'person_count' => 1,
			'b_person_count' => 1,
			'common_expense' => 1,
			'b_person_total' => 1,
			'labor_unit' => '',
			'corp_unit' => '',
			'yearly_labor_cost' => '',
			'unit_labor_cost' => '',
			'adjust_labor_cost' => '',
			'yearly_corpo_cost' => '',
			'unit_corpo_cost' => '',
			'adjust_corpo_cost' => '',
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2022-09-07 11:30:04',
			'updated_date' => '2022-09-07 11:30:04'
		),
	);

}
