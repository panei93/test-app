<?php
/**
 * BrmForecastSummary Fixture
 */
class BrmForecastSummaryFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'brm_forecast_summary';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'brm_term_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'target_year' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false),
		'hlayer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'hlayer_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'dlayer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'dlayer_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'layer_name_jp' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'layer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'logistic_index_no' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'brm_account_id_2' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'brm_account_name_jp_2' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'brm_account_id_5' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'brm_account_name_jp_5' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'brm_account_id_6' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'brm_account_name_jp_6' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'month_1_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_2_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_3_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_4_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_5_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_6_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'first_half' => array('type' => 'decimal', 'null' => true, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_7_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_8_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_9_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_10_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_11_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'month_12_amt' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'second_half' => array('type' => 'decimal', 'null' => true, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'whole_total' => array('type' => 'decimal', 'null' => true, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'id' => array('column' => 'id', 'unique' => 1),
			'index_1' => array('column' => array('brm_term_id', 'target_year', 'layer_code', 'logistic_index_no'), 'unique' => 0, 'length' => array('logistic_index_no' => '255'))
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
			'hlayer_code' => 'Lore',
			'hlayer_name' => 'Lorem ipsum dolor sit amet',
			'dlayer_code' => 'Lore',
			'dlayer_name' => 'Lorem ipsum dolor sit amet',
			'layer_name_jp' => 'Lorem ipsum dolor sit amet',
			'layer_code' => 'Lore',
			'logistic_index_no' => 'Lorem ipsum dolor sit amet',
			'brm_account_id_2' => 1,
			'brm_account_name_jp_2' => 'Lorem ipsum dolor sit amet',
			'brm_account_id_5' => 1,
			'brm_account_name_jp_5' => 'Lorem ipsum dolor sit amet',
			'brm_account_id_6' => 1,
			'brm_account_name_jp_6' => 'Lorem ipsum dolor sit amet',
			'month_1_amt' => '',
			'month_2_amt' => '',
			'month_3_amt' => '',
			'month_4_amt' => '',
			'month_5_amt' => '',
			'month_6_amt' => '',
			'first_half' => '',
			'month_7_amt' => '',
			'month_8_amt' => '',
			'month_9_amt' => '',
			'month_10_amt' => '',
			'month_11_amt' => '',
			'month_12_amt' => '',
			'second_half' => '',
			'whole_total' => '',
			'created_date' => '2022-10-05 15:01:21'
		),
	);

}
