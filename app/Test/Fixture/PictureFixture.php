<?php
/**
 * Picture Fixture
 */
class PictureFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'picture_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 10, 'key' => 'unique', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'picture_type' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'file_path' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 800, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'flag' => array('type' => 'string', 'null' => false, 'default' => '1', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '1:Active;0:Inactive', 'charset' => 'utf8'),
		'created_by' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'updated_by' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'updated_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'picture_name_UNIQUE' => array('column' => 'picture_name', 'unique' => 1),
			'picture_name' => array('column' => 'picture_name', 'unique' => 0),
			'file_path' => array('column' => 'file_path', 'unique' => 0, 'length' => array('file_path' => '255'))
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
			'picture_name' => 'Lorem ip',
			'picture_type' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'file_path' => 'Lorem ipsum dolor sit amet',
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_by' => 'Lorem ip',
			'updated_by' => 'Lorem ip',
			'created_date' => '2022-09-07 11:30:10',
			'updated_date' => '2022-09-07 11:30:10'
		),
	);

}
