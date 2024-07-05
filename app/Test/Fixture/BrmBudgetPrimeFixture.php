<?php
/**
 * BrmBudgetPrime Fixture
 */
class BrmBudgetPrimeFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'brm_term_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'target_year' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false),
		'layer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'filling_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'trade_filling_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'brm_account_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'account_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'logistic_index_no' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'destination' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'kpi_unit' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 45, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
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
		'1st_half_total' => array('type' => 'decimal', 'null' => true, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'2nd_half_total' => array('type' => 'decimal', 'null' => true, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'sub_total' => array('type' => 'decimal', 'null' => true, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'flag' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'updated_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'id' => array('column' => 'id', 'unique' => 1),
			'index_1' => array('column' => array('brm_term_id', 'target_year', 'layer_code', 'account_code', 'logistic_index_no'), 'unique' => 0, 'length' => array('logistic_index_no' => '255'))
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
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
			'filling_date' => '2022-10-05 15:03:32',
			'trade_filling_date' => '2022-10-05 15:03:32',
			'brm_account_id' => 1,
			'account_code' => 'Lorem ip',
			'logistic_index_no' => 'Lorem ipsum dolor sit amet',
			'destination' => 'Lore',
			'kpi_unit' => 'Lorem ipsum dolor sit amet',
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
			'flag' => 'Lorem ipsum dolor sit ame',
			'type' => 'Lorem ipsum dolor sit ame',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2022-10-05 15:03:32',
			'updated_date' => '2022-10-05 15:03:32'
		),
	);

}
