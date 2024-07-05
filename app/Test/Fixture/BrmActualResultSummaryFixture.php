<?php
/**
 * BrmActualResultSummary Fixture
 */
class BrmActualResultSummaryFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'brm_actual_result_summary';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'hlayer_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 6, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'target_month' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 8, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'layer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'account_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'transaction_key' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'destination_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 6, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'submission_deadline_date' => array('type' => 'date', 'null' => true, 'default' => null),
		'amount' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'updated_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'id' => array('column' => 'id', 'unique' => 1),
			'head_dept' => array('column' => 'hlayer_code', 'unique' => 0),
			'index_1' => array('column' => array('target_month', 'layer_code', 'account_code'), 'unique' => 0)
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
			'hlayer_code' => 'Lore',
			'target_month' => 'Lorem ',
			'layer_code' => 'Lore',
			'account_code' => 'Lorem ip',
			'transaction_key' => 'Lorem ipsum dolor sit amet',
			'destination_code' => 'Lore',
			'submission_deadline_date' => '2022-10-05',
			'amount' => '',
			'updated_date' => '2022-10-05 15:04:14'
		),
	);

}
