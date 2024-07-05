<?php
/**
 * AssetEvent Fixture
 */
class AssetEventFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'event_name' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'reference_event_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false),
		'flag' => array('type' => 'string', 'null' => false, 'default' => '1', 'length' => 1, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created_by' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'updated_by' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'concurrency_status' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 1, 'unsigned' => false, 'comment' => '0 for not occupy and 1 for occupy'),
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
			'event_name' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'reference_event_id' => 1,
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_by' => 'Lorem ip',
			'updated_by' => 'Lorem ip',
			'created_date' => '2022-09-07 11:29:56',
			'updated_date' => '2022-09-07 11:29:56',
			'concurrency_status' => 1
		),
	);

}
