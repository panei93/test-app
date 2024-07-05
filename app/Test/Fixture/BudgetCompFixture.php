<?php
/**
 * BudgetComp Fixture
 */
class BudgetCompFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'layer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'target_year' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false),
		'sales_ratio' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 3, 'unsigned' => false),
		'customer' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 300, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'deli_share' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 3, 'unsigned' => false),
		'deli_share_change' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '4,2', 'unsigned' => false),
		'product_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 300, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'industry' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 300, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'industry_share' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 3, 'unsigned' => false),
		'industry_share_change' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '4,2', 'unsigned' => false),
		'market_size_change' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '4,2', 'unsigned' => false),
		'growth_pot' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '4,2', 'unsigned' => false),
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
			'layer_code' => 'Lore',
			'target_year' => 1,
			'sales_ratio' => 1,
			'customer' => 'Lorem ipsum dolor sit amet',
			'deli_share' => 1,
			'deli_share_change' => '',
			'product_name' => 'Lorem ipsum dolor sit amet',
			'industry' => 'Lorem ipsum dolor sit amet',
			'industry_share' => 1,
			'industry_share_change' => '',
			'market_size_change' => '',
			'growth_pot' => '',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2022-09-07 11:29:59',
			'updated_date' => '2022-09-07 11:29:59'
		),
	);

}
