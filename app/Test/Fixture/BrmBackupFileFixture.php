<?php
/**
 * BrmBackupFile Fixture
 */
class BrmBackupFileFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'term_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'term_name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'file_type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 4, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'hlayer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'hlayer_name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'start_month' => array('type' => 'date', 'null' => true, 'default' => null),
		'end_month' => array('type' => 'date', 'null' => true, 'default' => null),
		'status' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'flag' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'updated_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'term_id' => 1,
			'term_name' => 'Lorem ipsum dolor sit amet',
			'file_type' => 'Lo',
			'hlayer_code' => 'Lore',
			'hlayer_name' => 'Lorem ipsum dolor sit amet',
			'start_month' => '2022-10-12',
			'end_month' => '2022-10-12',
			'status' => 'Lorem ipsum dolor sit ame',
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_id' => 1,
			'updated_id' => 1,
			'created_date' => '2022-10-12 06:28:34',
			'updated_date' => '2022-10-12 06:28:34'
		),
	);

}
