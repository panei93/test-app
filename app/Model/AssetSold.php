<?php
App::uses('AppModel', 'Model');
/**
 * AssetSold Model
 *
 * @property AssetEvent $AssetEvent
 */
class AssetSold extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'asset_event_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'asset_no' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'layer_code' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'quantity' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'asset_status' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public function DeletedSoldData($del_asset_no,$del_ba_code,$event_id){

		$param = array();
	 $sql  = "delete from asset_solds WHERE asset_no= :asset_no and layer_code=:layer_code and asset_event_id=:asset_event_id";

	 $param["asset_no"] = $del_asset_no;
	 $param["layer_code"] = $del_ba_code;
	 $param["asset_event_id"] = $event_id;

	 $data = $this->query($sql,$param);

	 $effect_rows = $this->getAffectedRows();
	 
	 if($effect_rows > 0) {
		 return true;
	 } else {
		 return false;
	 }
 }

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'AssetEvent' => array(
			'className' => 'AssetEvent',
			'foreignKey' => 'asset_event_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
