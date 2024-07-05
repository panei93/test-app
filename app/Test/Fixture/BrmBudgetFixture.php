<?php
/**
 * BrmBudget Fixture
 */
class BrmBudgetFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'target_month' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 8, 'key' => 'index', 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'brm_term_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'hlayer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'dlayer_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 6, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'layer_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 6, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'team' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'sub' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'brm_saccount_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'account_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'brm_account_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'amount' => array('type' => 'decimal', 'null' => false, 'default' => '0.00', 'length' => '12,2', 'unsigned' => false),
		'logistic_index_no' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'flag' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8mb4_general_ci', 'comment' => '0: record\\n1: total', 'charset' => 'utf8mb4'),
		'created_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'updated_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'index_1' => array('column' => array('target_month', 'brm_term_id', 'layer_code', 'account_code'), 'unique' => 0)
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
			'target_month' => 'Lorem ',
			'brm_term_id' => 1,
			'hlayer_code' => 'Lore',
			'dlayer_code' => 'Lore',
			'layer_code' => 'Lore',
			'team' => 'Lorem ipsum dolor sit amet',
			'sub' => 'Lorem ipsum dolor sit amet',
			'brm_saccount_id' => 1,
			'account_code' => 'Lorem ip',
			'brm_account_name' => 'Lorem ipsum dolor sit amet',
			'amount' => '',
			'logistic_index_no' => 'Lorem ipsum dolor sit amet',
			'flag' => 'Lorem ipsum dolor sit ame',
			'type' => 'Lorem ipsum dolor sit ame',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2022-10-05 15:04:02',
			'updated_date' => '2022-10-05 15:04:02'
		),
	);

}
