<?php
/**
 * SampleTestResults Fixture
 */
class SampleTestResultsFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'sample_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'question1' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '0:uncheck;1:check', 'charset' => 'utf8'),
		'question2' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '0:uncheck;1:check', 'charset' => 'utf8'),
		'question3' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '0:uncheck;1:check', 'charset' => 'utf8'),
		'question4' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '0:uncheck;1:check', 'charset' => 'utf8'),
		'question5' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '0:uncheck;1:check', 'charset' => 'utf8'),
		'question6' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '0:uncheck;1:check', 'charset' => 'utf8'),
		'question7' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '0:uncheck;1:check', 'charset' => 'utf8'),
		'remark' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'report_times' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '0:0times;1:1time;2:2times;3:3times', 'charset' => 'utf8'),
		'point_out1' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'report_necessary1' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '0:uncheck;1:check', 'charset' => 'utf8'),
		'deadline_date1' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'point_out2' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'report_necessary2' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'deadline_date2' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'point_out3' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'deadline_date3' => array('type' => 'datetime', 'null' => true, 'default' => null, 'length' => 1),
		'testresult_finish' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '0:uncheck;1:check', 'charset' => 'utf8'),
		'flag' => array('type' => 'string', 'null' => false, 'default' => '1', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => '1:tst_res_save;2:acc_smn_rev;3:acc_mng_app', 'charset' => 'utf8'),
		'created_by' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'updated_by' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
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
			'sample_id' => 1,
			'question1' => 'Lorem ipsum dolor sit ame',
			'question2' => 'Lorem ipsum dolor sit ame',
			'question3' => 'Lorem ipsum dolor sit ame',
			'question4' => 'Lorem ipsum dolor sit ame',
			'question5' => 'Lorem ipsum dolor sit ame',
			'question6' => 'Lorem ipsum dolor sit ame',
			'question7' => 'Lorem ipsum dolor sit ame',
			'remark' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'report_times' => 'Lorem ipsum dolor sit ame',
			'point_out1' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'report_necessary1' => 'Lorem ipsum dolor sit ame',
			'deadline_date1' => '2022-09-07 11:30:17',
			'point_out2' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'report_necessary2' => 'Lorem ipsum dolor sit ame',
			'deadline_date2' => '2022-09-07 11:30:17',
			'point_out3' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'deadline_date3' => '2022-09-07 11:30:17',
			'testresult_finish' => 'Lorem ipsum dolor sit ame',
			'flag' => 'Lorem ipsum dolor sit ame',
			'created_by' => 'Lorem ip',
			'updated_by' => 'Lorem ip',
			'created_date' => '2022-09-07 11:30:17',
			'updated_date' => '2022-09-07 11:30:17'
		),
	);

}
