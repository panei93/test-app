<?php
App::uses('AppModel', 'Model');
/**
 * SampleTestResults Model
 *
 * @property Sample $Sample
 */
class SampleTestResult extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'sample_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'question1' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'question2' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'question3' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'question4' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'question5' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'question6' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'question7' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'report_times' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'report_necessary1' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'testresult_finish' => array(
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
	);

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Sample' => array(
			'className' => 'Sample',
			'foreignKey' => 'sample_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	/**
     * Update flag 7 =>Account Manager Comment
     *
     * @author Aye Thandar Lwin
     *
     * @param sample_id
     * @return data
     */
    public function Update_Approve_flag($sample_id,$login_id) {
   
    	$param = array();
    
    	$sql  = "";
    	$sql .= " UPDATE sample_test_results ";
    	$sql .= " 	 SET flag = 3, ";
    	$sql .= "		 updated_by = :updated_id, ";
    	$sql .= "		 updated_date = :updated_date ";
    	$sql .= "  WHERE sample_id = :sample_id ";
    	$sql .= "  AND flag = 2 ";
    	
    	$currentTimestamp = date('Y-m-d H:i:s');
    	$param['sample_id'] = $sample_id;
    	$param['updated_id'] = $login_id;
    	$param['updated_date'] = $currentTimestamp;
    
    	$this->query($sql,$param);
    
    }
    /**
     * Update flag 0 =>Account Manager Comment from TEST RESULT FORM
     *
     * @author Aye Thandar Lwin
     *
     * @param sample_id
     * @return data
     */
    public function Cancel_Approve_flag($sample_id,$login_id) {
    
    	$param = array();
    
    	$sql  = "";
    	$sql .= " UPDATE sample_test_results ";
    	$sql .= " 	 SET flag = 1, ";                   #original code is flag = 2 
    	$sql .= "		 updated_by = :updated_id, ";   #change NNL according to Customer feedback (24.1.2020)
    	$sql .= "		 updated_date = :updated_date ";
    	$sql .= "  WHERE sample_id = :sample_id ";
    	$sql .= "  AND flag = 3 ";
    	
    	$currentTimestamp = date('Y-m-d H:i:s');
    	$param['sample_id'] = $sample_id;
    	$param['updated_id'] = $login_id;
    	$param['updated_date'] = $currentTimestamp;
    
    	$this->query($sql,$param);
    
    }


    /**
     * Update flag 1 =>Account Manager Comment from TEST RESULT FORM
     *
     * @author Sandi khaing
     *
     * @param sample_id
     * @return data
     */
    public function Cancel_Reject_flag($sample_id,$login_id) {
    
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE sample_test_results ";
        $sql .= "    SET flag = 1, ";                   
        $sql .= "        updated_by = :updated_id, ";  
        $sql .= "        updated_date = :updated_date ";
        $sql .= "  WHERE sample_id = :sample_id ";
        $sql .= "  AND flag = 2 ";
        
        $currentTimestamp = date('Y-m-d H:i:s');
        $param['sample_id'] = $sample_id;
        $param['updated_id'] = $login_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql,$param);
    
    }
	/**
     * Update testresultfinish flag 1 
     *
     * @author Aye Thandar Lwin
     *
     * @param sample_id
     */
    public function UpdateSampleTestResultsFinish($data) {    	
    	
    	$admin_level_id = CakeSession::read('ADMIN_LEVEL_ID');
    	
    	$param = array();
    
    	$sql  = "";
    	$sql .= " UPDATE sample_test_results ";	
    	$sql .= " SET remark = :remark, ";
    	$sql .= " 	 report_times = :report_times, ";
    	$sql .= " 	 point_out1 = :point_out1, ";
    	$sql .= " 	 report_necessary1 = :report_necessary1, ";
    	
    
    	$sql .= " 	 deadline_date1 = :deadline_date1, ";
    	$param['deadline_date1'] = $data['deadline_date1'];
    	$sql .= " 	 testresult_finish = :testresult_finish, ";
    	$sql .= "		 flag = :flag, ";
    	$sql .= "		 updated_by = :updated_id, ";
    	$sql .= "		 updated_date = :updated_date ";
    	$sql .= "  WHERE sample_id = :sample_id ";
    	
    	if($admin_level_id == 3){
    		$sql .= "    AND flag IN('1','2') ";
    	}else if($admin_level_id == 4){
    		$sql .= "    AND flag NOT IN('2','3') ";
    	}
    	
    
    	$currentTimestamp = date('Y-m-d H:i:s');
    	
    	$param['sample_id'] = $data['sample_id'];
    	$param['remark'] = $data['remark'];
    	$param['report_times'] = $data['report_times'];
    	$param['point_out1'] = $data['point_out1'];
    	$param['report_necessary1'] = $data['report_necessary1'];    	
    	$param['testresult_finish'] = $data['testresult_finish'];
    	$param['flag'] = $data['flag'];
    	$param['updated_id'] = $data['updated_by'];
    	$param['updated_date'] = $currentTimestamp;
   
    	$this->query($sql,$param);
    	
    }
    
    /**
     * select search sample data for TestResult
     *
     * @author Aye Zar Ni Kyaw
     *
     * @param period_date
     */    

    function SampleMonthlyResult($period_date){
        $param = array();
        $param['type_order'] = Setting::LAYER_SETTING['SampleSelections'];
		$language    = $_SESSION['Config']['language'];
        $name = ($language == 'eng'?'name_en':'name_jp');
        
        // $sql = "SELECT * from (SELECT sample_data.layer_code as layer_code,sample_data.".$name." as name_tmp,
        // sample_data.sample_number,sample_data.layers_group_name,result_data.result_number,completion_data.completions
        // from  (SELECT samples.layer_code,layers.".$name.",(select group_concat(gp1.".$name.") from layers as gp1
        // where gp1.id in 
        // (select max(gp2.id) as id from layers as gp2 where FIND_IN_SET(gp2.layer_code,
        // replace(replace(replace(replace(json_extract
        // (layers.parent_id,'$.*'),'[',''),']',''),'\"',''),'\ ',''))
        // group by gp2.layer_code))layers_group_name,count(samples.id) 
        // as sample_number 
        // FROM samples 
        // left join layers on samples.layer_code = layers.layer_code
        // WHERE date_format(period, '%Y-%m') = :period_date and layers.flag = 1 and layers.object=1 and samples.flag != 0 
        // GROUP BY samples.layer_code) 
        // sample_data left outer join  (SELECT layer_code,count(id) as result_number FROM 
        // samples WHERE date_format(period, '%Y-%m') = :period_date and flag >= 4 GROUP BY layer_code) result_data
        // ON sample_data.layer_code = result_data.layer_code
        // left outer join  (SELECT layer_code,count(id) as completions FROM samples WHERE 
        // date_format(period, '%Y-%m') = :period_date and flag = 10 GROUP BY layer_code
        // ) completion_data
        // ON result_data.layer_code = completion_data.layer_code  UNION  
        // (SELECT layers.layer_code,layers.".$name.",'',
        // (select group_concat(gp3.".$name.") from layers as gp3
        // where gp3.id in 
        // (select max(gp4.id) as id from layers as gp4 where FIND_IN_SET(gp4.layer_code,
        // replace(replace(replace(replace(json_extract
        // (layers.parent_id,'$.*'),'[',''),']',''),'\"',''),'\ ',''))
        // group by gp4.layer_code))layers_group_name,
        // '','' from layers WHERE   
        // layers.flag=1 and layers.object=1 and layers.type_order=:type_order))summary 
        // GROUP BY summary.layer_code";
		$sql = "SELECT * from (SELECT sample_data.layer_code as layer_code,sample_data.category,sample_data.".$name." as name_tmp,
        sample_data.sample_number,sample_data.layers_group_name,result_data.result_number,completion_data.completions
        from  (SELECT samples.layer_code, samples.category, layers.".$name.",(select group_concat(gp1.".$name.") from layers as gp1
        where gp1.id in 
        (select max(gp2.id) as id from layers as gp2 where FIND_IN_SET(gp2.layer_code,
        replace(replace(replace(replace(json_extract
        (layers.parent_id,'$.*'),'[',''),']',''),'\"',''),'\ ',''))
        group by gp2.layer_code))layers_group_name,count(samples.id) 
        as sample_number 
        FROM samples 
        left join layers on samples.layer_code = layers.layer_code 
        WHERE date_format(period, '%Y-%m') = :period_date and layers.flag = 1 and layers.object=1 and samples.flag != 0  and layers.type_order=:type_order   
        GROUP BY samples.layer_code, samples.category) 
        sample_data left outer join  (SELECT layer_code,category,count(id) as result_number FROM 
        samples WHERE date_format(period, '%Y-%m') = :period_date and flag >= 4 GROUP BY layer_code, category) result_data
        ON sample_data.layer_code = result_data.layer_code AND sample_data.category = result_data.category
        left outer join  (SELECT layer_code,category,count(id) as completions FROM samples WHERE 
        date_format(period, '%Y-%m') = :period_date and flag = 10 GROUP BY layer_code, category
        ) completion_data
        ON ( result_data.layer_code = completion_data.layer_code AND result_data.category = completion_data.category) ) summary 
         GROUP BY summary.layer_code, summary.category";
        
        $param['period_date'] = $period_date;
        $data = $this->query($sql,$param);
        return $data;
    }
    /**
     * select search sample data for TestResult
     *
     * @author Aye Zar Ni Kyaw
     *
     * @param period_date
     */     
    public function paginate($conditions, $fields, $order, $limit, $page=1, $recursive=null, $extra=array()) {
        $row_start = ($page-1) * $limit;
        $param = array();
        
        $param['period_date'] = $conditions['Sample.period'];
        $param['type_order'] = Setting::LAYER_SETTING['SampleSelections'];
        $language    = $_SESSION['Config']['language'];
        if($language == 'jpn'){
            $name = 'name_jp';
        }else{
            $name = 'name_en';
        }
       
        // $sql = "SELECT * from (SELECT sample_data.layer_code as layer_code,sample_data.".$name." as name_tmp,
        // sample_data.sample_number,sample_data.layers_group_name,result_data.result_number,completion_data.completions
        // from  (SELECT samples.layer_code,layers.".$name.",(select group_concat(gp1.".$name.") from layers as gp1
        // where gp1.id in 
        // (select max(gp2.id) as id from layers as gp2 where FIND_IN_SET(gp2.layer_code,
        // replace(replace(replace(replace(json_extract
        // (layers.parent_id,'$.*'),'[',''),']',''),'\"',''),'\ ',''))
        // group by gp2.layer_code))layers_group_name,count(samples.id) 
        // as sample_number 
        // FROM samples 
        // left join layers on samples.layer_code = layers.layer_code
        // WHERE date_format(period, '%Y-%m') = :period_date and layers.flag = 1 and layers.object=1 and samples.flag != 0 
        // GROUP BY samples.layer_code) 
        // sample_data left outer join  (SELECT layer_code,count(id) as result_number FROM 
        // samples WHERE date_format(period, '%Y-%m') = :period_date and flag >= 4 GROUP BY layer_code) result_data
        // ON sample_data.layer_code = result_data.layer_code
        // left outer join  (SELECT layer_code,count(id) as completions FROM samples WHERE 
        // date_format(period, '%Y-%m') = :period_date and flag = 10 GROUP BY layer_code
        // ) completion_data
        // ON result_data.layer_code = completion_data.layer_code  UNION  
        // (SELECT layers.layer_code,layers.".$name.",'',
        // (select group_concat(gp3.".$name.") from layers as gp3
        // where gp3.id in 
        // (select max(gp4.id) as id from layers as gp4 where FIND_IN_SET(gp4.layer_code,
        // replace(replace(replace(replace(json_extract
        // (layers.parent_id,'$.*'),'[',''),']',''),'\"',''),'\ ',''))
        // group by gp4.layer_code))layers_group_name,
        // '','' from layers WHERE   
        // layers.flag=1 and layers.object=1 and layers.type_order=:type_order))summary 
        // GROUP BY summary.layer_code";


		$sql = "SELECT * from (SELECT sample_data.layer_code as layer_code,sample_data.category,sample_data.".$name." as name_tmp,
        sample_data.sample_number,sample_data.layers_group_name,result_data.result_number,completion_data.completions
        from  (SELECT samples.layer_code, samples.category, layers.".$name.",(select group_concat(gp1.".$name.") from layers as gp1
        where gp1.id in 
        (select max(gp2.id) as id from layers as gp2 where FIND_IN_SET(gp2.layer_code,
        replace(replace(replace(replace(json_extract
        (layers.parent_id,'$.*'),'[',''),']',''),'\"',''),'\ ',''))
        group by gp2.layer_code))layers_group_name,count(samples.id) 
        as sample_number 
        FROM samples 
        left join layers on samples.layer_code = layers.layer_code 
        WHERE date_format(period, '%Y-%m') = :period_date and layers.flag = 1 and layers.object=1 and samples.flag != 0  and layers.type_order=:type_order   
        GROUP BY samples.layer_code, samples.category) 
        sample_data left outer join  (SELECT layer_code,category,count(id) as result_number FROM 
        samples WHERE date_format(period, '%Y-%m') = :period_date and flag >= 4 GROUP BY layer_code, category) result_data
        ON sample_data.layer_code = result_data.layer_code AND sample_data.category = result_data.category
        left outer join  (SELECT layer_code,category,count(id) as completions FROM samples WHERE 
        date_format(period, '%Y-%m') = :period_date and flag = 10 GROUP BY layer_code, category
        ) completion_data
        ON ( result_data.layer_code = completion_data.layer_code AND result_data.category = completion_data.category) ) summary 
         GROUP BY summary.layer_code, summary.category";
        $sql .= "     LIMIT " . $row_start;
        $sql .= "        , " . $limit;
      
        $results = $this->query($sql, $param);
		//pr($results);
        return $results;
    }  
    /**
     * select search sample data for TestResult
     *
     * @author Aye Zar Ni Kyaw
     *
     * @param period_date
     */ 
    public function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
        $this->recursive = -1;
        
        $param['period_date'] = $conditions['period_date'];
        $sql = "";
        $sql = "SELECT count(*) from (SELECT sample_data.layer_code as layer_code,sample_data.name_jp,
        sample_data.sample_number,sample_data.layers_group_name,result_data.result_number,completion_data.completions
        from  (SELECT samples.layer_code,layers.name_jp,(select group_concat(gp1.name_jp) from layers as gp1
        where gp1.id in 
        (select max(gp2.id) as id from layers as gp2 where FIND_IN_SET(gp2.layer_code,
        replace(replace(replace(replace(json_extract
        (layers.parent_id,'$.*'),'[',''),']',''),'\"',''),'\ ',''))
        group by gp2.layer_code))layers_group_name,count(samples.id) 
        as sample_number 
        FROM samples 
        left join layers on samples.layer_code = layers.layer_code
        WHERE date_format(period, '%Y-%m') = :period_date and layers.flag = 1 and layers.object=1 and samples.flag != 0 
        GROUP BY samples.layer_code) 
        sample_data left outer join  (SELECT layer_code,count(id) as result_number FROM 
        samples WHERE date_format(period, '%Y-%m') = :period_date and flag >= 4 GROUP BY layer_code) result_data
        ON sample_data.layer_code = result_data.layer_code
        left outer join  (SELECT layer_code,count(id) as completions FROM samples WHERE 
        date_format(period, '%Y-%m') = :period_date and flag = 10 GROUP BY layer_code
        ) completion_data
        ON result_data.layer_code = completion_data.layer_code  UNION  
        (SELECT layers.layer_code,layers.name_jp,'',
        (select group_concat(gp3.name_jp) from layers as gp3
        where gp3.id in 
        (select max(gp4.id) as id from layers as gp4 where FIND_IN_SET(gp4.layer_code,
        replace(replace(replace(replace(json_extract
        (layers.parent_id,'$.*'),'[',''),']',''),'\"',''),'\ ',''))
        group by gp4.layer_code))layers_group_name,
        '','' from layers WHERE   
        layers.flag=1 and layers.object=1))summary 
        GROUP BY summary.layer_code";
                
        $results = $this->query($sql,$param); 

        return $results[0][0]['count(*)'];
    }  
}
