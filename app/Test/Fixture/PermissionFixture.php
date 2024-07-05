<?php
/**
 * Permission Fixture
 */
class PermissionFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'menu_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'role_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'mail_send' => array('type' => 'integer', 'null' => false, 'default' => '1', 'unsigned' => false),
		'mail_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'limit' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '1: All\\n2: Headquarter\\n3: Department\\n4: BA', 'charset' => 'utf8'),
		'created_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'updated_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
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
			'menu_id' => 1,
			'role_id' => 1,
			'mail_send' => 1,
			'mail_id' => 1,
			'limit' => 'Lorem ipsum dolor sit ame',
			'created_by' => 1,
			'updated_by' => 1,
			'created_date' => '2022-09-09 05:41:28',
			'updated_date' => '2022-09-09 05:41:28'
		),
	);

}
