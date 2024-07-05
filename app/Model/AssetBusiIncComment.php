<?php
App::uses('AppModel', 'Model');
/**
 * AssetBusiIncComment Model
 *
 * @property Asset $Asset
 */
class AssetBusiIncComment extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'asset_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'created_by' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'updated_by' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'created_date' => array(
			'datetime' => array(
				'rule' => array('datetime'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'updated_date' => array(
			'datetime' => array(
				'rule' => array('datetime'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public function checkPhyLblState($ba_code, $event_id) {   
        $sql = "SELECT asset_busi_inc_comments.asset_id,tblTemp.physical_chk,tblTemp.label_chk";
        $sql .=",asset_busi_inc_comments.comment,asset_busi_inc_comments.remark";
        $sql .=" FROM asset_busi_inc_comments,";
        $sql .=" (SELECT id,physical_chk,label_chk";
        $sql .=" FROM assets";
        $sql .=" WHERE asset_event_id=:asset_event_id";
        if(!empty($ba_code)) $sql .=" AND assets.layer_code=:layer_code";
        $sql .=" AND assets.flag!=0) tblTemp";
        $sql .=" WHERE tblTemp.id=asset_busi_inc_comments.asset_id";
        

        $param['asset_event_id'] = $event_id;
        if(!empty($ba_code)) $param['layer_code'] = $ba_code;
        $data = $this->query($sql, $param);
           
        return $data;
    }

	public function updateComment($updateCmt, $updCmtAssetId, $admin_id) {
	
        $bind = [];
        $len = count($updateCmt);
        $sql = 'UPDATE asset_busi_inc_comments SET comment = (CASE ';
        for($i=0; $i<$len; $i++) {
            $sql .= 'WHEN id=? AND flag=1 THEN ? ';
            $bind[] = $updateCmt[$i]['asset_id'];
            $bind[] = $updateCmt[$i]['comment'];
        }
        $sql .= 'END), ';
        $sql .= 'remark = (CASE ';
        for($i=0; $i<$len; $i++) {
            $sql .= 'WHEN id=? AND flag=1 THEN ? ';
            $bind[] = $updateCmt[$i]['asset_id'];
            $bind[] = $updateCmt[$i]['remark'];
        }
        $sql .= 'END), ';
        $sql .= 'updated_by = (CASE ';
        for($i=0; $i<$len; $i++) {
            $sql .= 'WHEN id=? AND flag=1 THEN ? ';
            $bind[] = $updateCmt[$i]['asset_id'];
            $bind[] = $admin_id;
        }
        $sql .= 'END), ';
        $sql .= 'updated_date = (CASE ';
        for($i=0; $i<$len; $i++) {
            $sql .= 'WHEN id=? AND flag=1 THEN ? ';
            $bind[] = $updateCmt[$i]['asset_id'];
            $bind[] = date('Y-m-d H:i:s');
        }
        $sql .= 'END) ';
        $sql .= 'WHERE id IN ('.implode(',',$updCmtAssetId).') ';
        $sql .= 'AND flag=1';
		
        $rsl = $this->query($sql, $bind);
	
        $row = $this->getAffectedRows();
        if($row > 0) {
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
		'Asset' => array(
			'className' => 'Asset',
			'foreignKey' => 'asset_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
