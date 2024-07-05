<?php
/**
 * BrmCopyTermLog Fixture
 */
class BrmCopyTermLogFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'brm_term_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'copy_brm_term_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created_by' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
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
			'brm_term_id' => 1,
			'copy_brm_term_id' => 1,
			'created_by' => 1,
			'created_date' => '2022-10-05 14:59:49'
		),
	);

}
