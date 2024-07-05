<?php
/**
 * BrmAccountSetup Fixture
 */
class BrmAccountSetupFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'target_year' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 4, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'hlayer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'brm_account_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'brm_saccount_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'order' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 2, 'unsigned' => false),
		'sub_order' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 2, 'unsigned' => false),
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
			'target_year' => 'Lo',
			'hlayer_code' => 'Lore',
			'brm_account_id' => 1,
			'brm_saccount_id' => 1,
			'order' => 1,
			'sub_order' => 1,
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2022-10-05 15:04:35',
			'updated_date' => '2022-10-05 15:04:35'
		),
	);

}
