<?php
/**
 * MailReceiver Fixture
 */
class MailReceiverFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'mail_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'role_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'mail_send_type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 25, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'mail_limit' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'flag' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created_by' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'updated_by' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
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
			'mail_id' => 1,
			'role_id' => 1,
			'mail_send_type' => 'Lorem ipsum dolor sit a',
			'mail_limit' => 1,
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_by' => 'Lorem ip',
			'updated_by' => 'Lorem ip',
			'created_date' => '2022-09-07 11:30:07',
			'updated_date' => '2022-09-07 11:30:07'
		),
	);

}
