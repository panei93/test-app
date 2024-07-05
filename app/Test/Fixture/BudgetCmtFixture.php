<?php
/**
 * BudgetCmt Fixture
 */
class BudgetCmtFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'budget_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'comment' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
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
			'budget_id' => 1,
			'comment' => 'Lorem ipsum dolor sit amet',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2022-09-07 11:29:59',
			'updated_date' => '2022-09-07 11:29:59'
		),
	);

}
