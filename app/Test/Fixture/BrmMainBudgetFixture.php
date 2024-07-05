<?php
/**
 * BrmMainBudget Fixture
 */
class BrmMainBudgetFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'brm_term_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'brm_account_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'brm_account_name_jp' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'target_month' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 8, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'hlayer_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'hlayer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'dlayer_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'dlayer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'layer_name_jp' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'layer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'index_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'transaction_key' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'submission_deadline_date' => array('type' => 'date', 'null' => true, 'default' => null),
		'amount' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '12,2', 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'budget_index' => array('column' => array('brm_term_id', 'layer_code', 'target_month'), 'unique' => 0)
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
			'brm_account_id' => 1,
			'brm_account_name_jp' => 'Lorem ipsum dolor sit amet',
			'target_month' => 'Lorem ',
			'hlayer_name' => 'Lorem ipsum dolor sit amet',
			'hlayer_code' => 'Lore',
			'dlayer_name' => 'Lorem ipsum dolor sit amet',
			'dlayer_code' => 'Lore',
			'layer_name_jp' => 'Lorem ipsum dolor sit amet',
			'layer_code' => 'Lore',
			'index_name' => 'Lorem ipsum dolor sit amet',
			'transaction_key' => 'Lorem ipsum dolor sit amet',
			'submission_deadline_date' => '2022-10-05',
			'amount' => '',
			'created_date' => '2022-10-05 15:01:55'
		),
	);

}
