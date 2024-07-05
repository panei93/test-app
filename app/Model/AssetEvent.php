<?php
App::uses('AppModel', 'Model');
/**
 * AssetEvent Model
 *
 * @property ReferenceEvent $ReferenceEvent
 * @property AssetRemove $AssetRemove
 * @property AssetSold $AssetSold
 * @property Asset $Asset
 */
class AssetEvent extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'event_name' => array(
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
	);

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'AssetRemove' => array(
			'className' => 'AssetRemove',
			'foreignKey' => 'asset_event_id',
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
		'AssetSold' => array(
			'className' => 'AssetSold',
			'foreignKey' => 'asset_event_id',
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
		'Asset' => array(
			'className' => 'Asset',
			'foreignKey' => 'asset_event_id',
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

	public function saveUserinfo($data)
    {
        $param = array();
        $sql  = "";
        $sql .= "	INSERT INTO asset_events(  ";
        $sql .= " 	event_name,";
        $sql .= "	reference_event_id,";
        
        $sql .= " 	flag , ";
        $sql .= " 	created_by, ";
        $sql .= " 	updated_by, ";
        $sql .= " 	created_date, ";
        $sql .= " 	updated_date ) ";
        $sql .= "   VALUES (";
        $sql .= " 	:event_name,";
        $sql .= "	:reference_event_id,";
        
        $sql .= "	1, ";
        $sql .= "	:created_by, ";
        $sql .=	"	:created_by, ";
        $sql .= " 	NOW(), ";
        $sql .= " 	NOW()) ";

        $param["event_name"] = $data["event_name"];

        if ($data["reference_event_id"]) {
            $param["reference_event_id"] = $data["reference_event_id"];
        } else {
            $param["reference_event_id"] = 0;
        }
        
        $param["created_by"] = $data["created_by"];
        $param["updated_by"] = $data["updated_by"];
        
        $data = $this->query($sql, $param);
        return $data;
    }

    /* Reference data check is not inactive when save */
    public function ref_data_flag($event_reference)
    {
        $sql = "";
        $sql.= " SELECT ";
        $sql.= "  event_name,flag ";
        $sql.= "   FROM asset_events";
        $sql.= "  WHERE asset_events.event_id= :event_reference";
        $param['event_reference'] = $event_reference;
        $data = $this->query($sql, $param);
        return $data;
    }

    // public function paginate($conditions, $fields, $order, $limit, $page=1, $recursive=null, $extra=array())
    // {
    //     $row_start = ($page-1) * $limit;
    //     $param = array();
                                    
    //     $sql = '';
    //     $sql .=" SELECT a.flag ,b.flag ,a.event_id ,a.event_name,b.event_id,b.event_name as referencename
	// 			FROM asset_events a 
	// 			LEFT JOIN asset_events b ON a.reference_event_id = b.event_id WHERE 	
	// 			a.flag = 0 OR a.flag = 1 ORDER BY a.event_id DESC";
    //     $sql .= '     LIMIT ' . $row_start;
    //     $sql .= '	 , ' . $limit;
                             
    //     $results= $this->query($sql, $param);
    //     return $results;
    // }

    public function paginateCount($conditions = null, $recursive = 0, $extra = array())
    {
        $this->recursive = -1;
        $param = array();
                                            
        $sql = "";
        $sql .= "	SELECT COUNT(*) AS count ";
        $sql .= "   FROM ( ";
        $sql .= "	SELECT *  ";
        $sql .= "   FROM asset_events AS EventModel ";
        $sql .= "   WHERE";
        $sql .= "	EventModel.flag = 0 OR EventModel.flag = 1" ;
        $sql .= "	) AS tmp";
                                            
        $results = $this->query($sql, $param);
        return $results[0][0]['count'];
    }

    /* Active status change when flag = 1 to Flag = 0*/
    public function activeFlagChange($updated_by, $eventId)
    {
        $sql  = "";
        $sql .= "	UPDATE asset_events";
        $sql .= "	SET ";
        $sql .= "	flag = 2, ";
        $sql .= "	updated_by = :updated_by, ";
        $sql .= "	updated_date = NOW() ";
        $sql .= " 	WHERE id = :event_id ";
        
        $param=array();
        $param['updated_by'] = $updated_by;
        $param['event_id'] = $eventId;
        
        $data = $this->query($sql, $param);
        return $data;
    }

    /* InActive status change when flag = 0 to Flag = 1*/
    public function InactiveFlagChange($updated_by, $eventId)
    {
        $sql  = "";
        $sql .= "	UPDATE asset_events";
        $sql .= "	SET ";
        $sql .= "	flag = 1, ";
        $sql .= "	updated_by = :updated_by, ";
        $sql .= "	updated_date = NOW() ";
        $sql .= " 	WHERE id = :event_id ";
        
        $param=array();
        $param['updated_by'] = $updated_by;
        $param['event_id'] = $eventId;
        
        $data = $this->query($sql, $param);
        return $data;
    }

    public function Update_Concurrency_1($event_id)
    {
        $sql  = "";
        $sql .= "	UPDATE asset_events";
        $sql .= "	SET ";
        $sql .= "	concurrency_status = 1 ";
        $sql .= " 	WHERE id = :event_id ";
        
        $param=array();
        $param['event_id'] = $event_id;
        
        $data = $this->query($sql, $param);
        return $data;
    }

    public function Update_Concurrency_0($event_id)
    {
        $sql  = "";
        $sql .= "	UPDATE asset_events";
        $sql .= "	SET ";
        $sql .= "	concurrency_status = 0 ";
        $sql .= " 	WHERE id = :event_id ";
        
        $param=array();
        $param['event_id'] = $event_id;
        
        $data = $this->query($sql, $param);
        return $data;
    }

}
