<?php
App::uses('AppModel', 'Model');
/**
 * Asset Model
 *
 * @property AssetEvent $AssetEvent
 * @property AssetBusiIncComment $AssetBusiIncComment
 * @property AssetBusiMgrApprove $AssetBusiMgrApprove
 */
class Asset extends AppModel {

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
		'layer_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'2nd_key_code' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'2nd_key_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
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
		'asset_name' => array(
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
		'acq_date' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'place_code' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'place_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'physical_chk' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'label_chk' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'flag' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
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

	/**
    *
    *  [ All BA code show and  tbl_asset flag 4 , Approve date Get show  when tbl_asset flag 4 .other flag 	show pink color not flag !=0 progress chart report .]
    *
    * @author 
    *
    * @param period
    **/
    public function getProgressChart($event_id, $lan = '')
    {   

        $param = array();
        $sql = "";
		$sql .= "select assets.id, assets.layer_code, assets.flag, users.user_name,
				layers1.name_".$lan." as cur_name, layers1.layer_type_id
				,layers2.name_".$lan." as name, layers2.layer_type_id
				from assets
				inner join layers as layers1 
				on layers1.layer_code = assets.layer_code and assets.created_date between layers1.from_date 
				and layers1.to_date and layers1.flag = 1
				inner join layers as layers2 on 
				layers1.parent_id like concat('%\"L', layers2.layer_type_id, '\":\"',layers2.layer_code,'\"%') and
				layers2.id IN (select max(layers.id) from layers group by layers.id)
			
				inner join users on users.id = assets.updated_by and users.flag=1
				where assets.asset_event_id =:asset_event_id and assets.flag!=0
				group by layer_code,layers2.id;";
        
        
        $param["asset_event_id"] = $event_id;
        $data = $this->query($sql, $param); 
		
        $result = [];
        foreach ($data as $value) {
            $key = $value['layers2']['layer_type_id'];
            $cur_key = $value['layers1']['layer_type_id'];
            $approve_date   ='';
                
            $getapprove_date =$this->getProgressBACode($value['assets']['layer_code'], $event_id);
            //pr($getapprove_date);die;
            if (!empty($getapprove_date)) {
                $approve_date = $getapprove_date[0]['asset_busi_mgr_approves']['approve_date'];
            }
            $result[$value['assets']['layer_code']][$key] = $value['layers2']['name'];
            $result[$value['assets']['layer_code']][$value['assets']['layer_code']] = $value['layers1']['cur_name'];

            $result[$value['assets']['layer_code']]['user_name'] = $value['users']['user_name'];
            $result[$value['assets']['layer_code']]['appDate'] = $approve_date;
        }
       // pr($result);die;
        return $result;
    }

	public function getProgressBACode($asset_ba_code, $event_id)
    {
        $param = array();
        $sql = "";
        $sql .= "SELECT distinct(approve_date)
				FROM asset_busi_mgr_approves 
				WHERE asset_busi_mgr_approves.flag != 0 and
				 asset_busi_mgr_approves.asset_id IN( SELECT id
				FROM assets
				WHERE layer_code = :layer_code AND
				assets.flag != 0 AND
				
				assets.asset_event_id = :asset_event_id 
				)";
       
        $param['layer_code'] = $asset_ba_code;
        $param['asset_event_id'] = $event_id;
        $data=$this->query($sql, $param);
    
        return $data;
    }

	public function getLatestIDOfSameAssetNo($rsl, $select_event_id)
    {
        $condition = [];
        $bindParam = [];
        $count = count($rsl);
        $sql = "SELECT max(assets.id) as latest_asset_id, max(assets.created_date), assets.asset_no ";
        $sql .= "FROM assets WHERE ";
        for ($i=0; $i<$count; $i++) {
            $asset_no = $rsl[$i]['Asset']['asset_no'];
            $created_date = $rsl[$i]['Asset']['created_date'];
            $condition[] = "(asset_no=? AND created_date < ? AND flag!=0)";
            $bindParam[] = $asset_no;
            $bindParam[] = $created_date;
        }
	
        $sql .= implode(' OR ', $condition);
        $sql .= " AND asset_event_id!=?";
        $bindParam[] = $select_event_id;
        $sql .= " GROUP BY asset_no";
        $find = $this->query($sql, $bindParam);
        return $find;
    }

	public function getReferenceEventData($event_id, $asset_no_arr, $ba_code='')
    {
        $sql = "SELECT FixedAssetsModel.asset_no,FixedAssetsModel.asset_name,FixedAssetsModel.layer_code,FixedAssetsModel.layer_name,FixedAssetsModel.label_chk,bac.remark FROM asset_busi_inc_comments as bac ";
        $sql .= "LEFT JOIN assets as FixedAssetsModel ON (FixedAssetsModel.id = bac.asset_id) ";
        $sql .= "LEFT JOIN asset_events as e ON (e.id = ?) ";
        $sql .= " WHERE FixedAssetsModel.asset_event_id=e.reference_event_id AND FixedAssetsModel.layer_code LIKE ? AND FixedAssetsModel.flag!=0 AND FixedAssetsModel.asset_no in ('".implode("','", $asset_no_arr)."') group by FixedAssetsModel.asset_no,FixedAssetsModel.layer_code";
        $param[] = $event_id;
        $param[] = '%'.$ba_code.'%';
        $collected_data = $this->query($sql, $param);
        $data = [];
        foreach ($collected_data as $value) {
            $data[$value['FixedAssetsModel']['asset_no']] = $value;
        }
        return $data;
    }

	public function updateAsset($arr, $asset_id_arr, $admin_id)
    {
		
        $bind = [];
        $len = count($arr);
		
        $sql = 'UPDATE assets SET physical_chk = (CASE ';
        for ($i=0; $i<$len; $i++) {
            $asset_id = $arr[$i]['asset_id'];
            $physical = $arr[$i]['physical_check'];
            $sql .= 'WHEN id=? AND (flag=1 OR flag=2) THEN ? ';
            $bind[] = $asset_id;
            $bind[] = $physical;
        }
        $sql .= 'END), ';
        $sql .= 'label_chk = (CASE ';
        for ($i=0; $i<$len; $i++) {
            $asset_id = $arr[$i]['asset_id'];
            $label = $arr[$i]['label_check'];
            $sql .= 'WHEN id=? AND (flag=1 OR flag=2) THEN ? ';
            $bind[] = $asset_id;
            $bind[] = $label;
        }
        $sql .= 'END), ';
        $sql .= 'flag = (CASE ';
        for ($i=0; $i<$len; $i++) {
            $asset_id = $arr[$i]['asset_id'];
            $flag = 2;
            $sql .= 'WHEN id=? AND (flag=1 OR flag=2) THEN ? ';
            $bind[] = $asset_id;
            $bind[] = $flag;
        }
        $sql .= 'END), ';
        $sql .= 'updated_by = (CASE ';
        for ($i=0; $i<$len; $i++) {
            $asset_id = $arr[$i]['asset_id'];
            $updated_id = $admin_id;
            $sql .= 'WHEN id=? AND (flag=1 OR flag=2) THEN ? ';
            $bind[] = $asset_id;
            $bind[] = $updated_id;
        }
        $sql .= 'END), ';
        $sql .= 'updated_date = (CASE ';
        for ($i=0; $i<$len; $i++) {
            $asset_id = $arr[$i]['asset_id'];
            $updated_date = date('Y-m-d H:i:s');
            $sql .= 'WHEN id=? AND (flag=1 OR flag=2) THEN ? ';
            $bind[] = $asset_id;
            $bind[] = $updated_date;
        }
        $sql .= 'END) ';
        $sql .= 'WHERE id IN ('.implode(',', $asset_id_arr).') ';
        $sql .= 'AND (flag = 1 OR flag = 2)';
		
        try {
            $rsl = $this->query($sql, $bind);
		
            $row = $this->getAffectedRows();
            if ($row > 0) {
                return true;
            } else {
                return false;
            }
			
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            return false;
        }
    }

	public function updateFADataToRequest($layer_code, $event_id)
    {
        $sql = "update assets";
        $sql .= " set flag=3 WHERE asset_event_id=:event_id and layer_code=:ba_code and flag=2 ";
        $param['event_id'] = $event_id;
        $param['ba_code'] = $layer_code;
        $data = $this->query($sql, $param);
        $row = $this->getAffectedRows();

        if ($row > 0) {
            return true;
        } else {
            return false;
        }
    }
	
	public function popUpData($event_name, $asset_no)
    {
        $sql="SELECT assets.asset_no,assets.asset_name,assets.layer_code,assets.layer_name,
					assets.quantity,assets.diff_qty,assets.asset_status,e.event_name
				from assets ,(SELECT id ,event_name
				FROM asset_events 
				WHERE event_name=:EventName and flag<>0) e
				where  assets.asset_no=:AssetNo and 
				assets.flag <> 0 and
				assets.asset_event_id=e.id";
        $param['EventName'] = $event_name;
        $param['AssetNo'] = $asset_no;
        $rsl = $this->query($sql, $param);
        return $rsl;
    }

	public function popUpDataRef($event_name, $asset_no)
    {
        $sql="SELECT assets.asset_no,assets.asset_name,assets.layer_code,assets.layer_name,
					assets.diff_qty,assets.quantity,assets.asset_status,e.event_name
				from assets ,(
				SELECT id, event_name
				from asset_events
				where id =
				(SELECT reference_event_id
				FROM asset_events 
				WHERE event_name=:EventName and flag<>0)) e
				where  assets.asset_no=:AssetNo and 
				assets.flag <> 0 and 
				assets.asset_event_id=e.id";
        $param['EventName'] = $event_name;
        $param['AssetNo'] = $asset_no;
        $rsl = $this->query($sql, $param);
        return $rsl;
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

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'AssetBusiIncComment' => array(
			'className' => 'AssetBusiIncComment',
			'foreignKey' => 'asset_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'AssetBusiMgrApprove' => array(
			'className' => 'AssetBusiMgrApprove',
			'foreignKey' => 'asset_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

}
