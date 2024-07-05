<?php
/**
 * AssetRemove Fixture
 */
class AssetRemoveFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
		'asset_event_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'asset_no' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 12, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'layer_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'quantity' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'remove_date' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'asset_status' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 1, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'eventId' => array('column' => 'asset_event_id', 'unique' => 0),
			'baCode' => array('column' => 'layer_code', 'unique' => 0),
			'assetNo' => array('column' => 'asset_no', 'unique' => 0)
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
			'asset_event_id' => 1,
			'asset_no' => 'Lorem ipsu',
			'layer_code' => 'Lore',
			'quantity' => 1,
			'remove_date' => 'Lorem ip',
			'asset_status' => 1
		),
	);

}
