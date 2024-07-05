<?php
App::uses('AppModel', 'Model');
/**
 * Sample Model
 *
 * @property SampleAccAttachment $SampleAccAttachment
 * @property SampleAccRequest $SampleAccRequest
 * @property SampleBusiAdminApprove $SampleBusiAdminApprove
 * @property SampleBusiManagerRequest $SampleBusiManagerRequest
 * @property SampleChecklist $SampleChecklist
 * @property SampleTestResults $SampleTestResults
 */
class Sample extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'period' => array(
			'date' => array(
				'rule' => array('date'),
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
		'incharge_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'project_title' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'posting_date' => array(
			'date' => array(
				'rule' => array('date'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'index_no' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'account_item' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'destination_code' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'destination_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'request_docu' => array(
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
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'SampleAccAttachment' => array(
			'className' => 'SampleAccAttachment',
			'foreignKey' => 'sample_id',
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
		'SampleAccRequest' => array(
			'className' => 'SampleAccRequest',
			'foreignKey' => 'sample_id',
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
		'SampleBusiAdminApprove' => array(
			'className' => 'SampleBusiAdminApprove',
			'foreignKey' => 'sample_id',
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
		'SampleBusiManagerRequest' => array(
			'className' => 'SampleBusiManagerRequest',
			'foreignKey' => 'sample_id',
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
		'SampleChecklist' => array(
			'className' => 'SampleChecklist',
			'foreignKey' => 'sample_id',
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
		'SampleTestResults' => array(
			'className' => 'SampleTestResults',
			'foreignKey' => 'sample_id',
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

	/**
     * select search sample data for TestResult
     *
     * @author Aye Thandar Lwin
     *
     * @param layer_code,period
     */
    public function search_testResult($layer_code, $period, $category)
    {
        $param = array();
    
        $sql  = "";
        $sql .= " SELECT samples.id,samples.incharge_name,samples.project_title,";
        $sql .= " 	     samples.posting_date,samples.index_no, ";
        $sql .= " 	     samples.account_item,samples.destination_code, ";
        $sql .= " 	     samples.destination_name,samples.money_amt, ";
        $sql .= " 	     samples.remark,samples.flag ";
        $sql .= "  FROM samples";
        $sql .= "  WHERE samples.layer_code = :layer_code ";
        $sql .= "  AND samples.category = :category ";
        $sql .= "    AND date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND samples.flag >= 4 ";
    
        $sql .= "ORDER BY samples.id ASC";
    
        $param['layer_code'] = $layer_code;
        $param['category'] = $category;
        $param['period'] = $period;
        
    
        $data = $this->query($sql, $param);
        return $data;
    }
    /**
     * select search sample data for TestResult
     *
     * @author Aye Thandar Lwin
     *
     * @param layer_code,period
     */
    public function search_testResult_forall_ba($period)
    {
        $param = array();
    
        $sql  = "";
        $sql .= " SELECT samples.layer_code,samples.category,samples.id,samples.incharge_name,samples.project_title,tbl_A.name_jp,";
        $sql .= "        samples.posting_date,samples.index_no, ";
        $sql .= "        samples.account_item,samples.destination_code, ";
        $sql .= "        samples.destination_name,samples.money_amt, ";
        $sql .= "        samples.remark,samples.flag ";
        //$sql .= "  FROM samples, (select layer_code, name_jp from layers where flag=1 and type_order=3 and to_date>=:todate) as tbl_A";
        $sql .= "  FROM samples, (select layer_code, name_jp from layers where flag=1 and type_order=3 and DATE_FORMAT(from_date, '%Y-%m') <=:todate and DATE_FORMAT(to_date, '%Y-%m') >=:todate) as tbl_A";
        $sql .= "  WHERE date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND samples.flag != 0 ";
        $sql .= "    AND tbl_A.layer_code = samples.layer_code ";
       
        $sql .= "ORDER BY samples.id ASC";
    
      
        $param['todate'] = date('Y-m-d');
        $param['period'] = $period;
        

    
        $data = $this->query($sql, $param);


        
        return $data;
    }
    
    /**
     * select search sample data for TestResult
     *
     * @author Aye Thandar Lwin
     *
     * @param layer_code,period
     */
    public function search_FillResult($layer_code, $period, $category)
    {
        $param = array();
    
        $sql  = "";
        $sql .= " SELECT samples.id,";
        $sql .= " 		test.remark AS test_result,";
        $sql .= " 		 test.report_times,point_out1,report_necessary1,DATE_FORMAT(deadline_date1,'%Y-%m-%d') as deadline_date,test.testresult_finish";
        $sql .= "  FROM samples";
        $sql .= "  LEFT JOIN sample_test_results as test ";
        $sql .= " 		ON samples.id = test.sample_id";
        $sql .= "  WHERE samples.layer_code = :layer_code ";
        $sql .= "    AND samples.category = :category ";
        $sql .= "    AND date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND samples.flag != 0 ";
        $sql .= "    AND samples.flag IN(4,5,6,7,8,9,10) ";
        $sql .= "ORDER BY samples.id ASC";
    
        $param['layer_code'] = $layer_code;
        $param['period'] = $period;
        $param['category'] = $category;
        
        $data = $this->query($sql, $param);
        return $data;
    }
    public function getQuestions($category){
        $sql  = "";
        $sql .= " SELECT questions.id,";
        $sql .= " 		questions.question";
        $sql .= "  FROM questions";
        
        $sql .= "  WHERE questions.category_type = :category ";
        $sql .= "  AND questions.flag = 1 ";
        $param['category'] = $category;
        $data = $this->query($sql, $param);
        return $data;
    }
    public function questionIsChecked($sampleId){
        $sql  = "";
        $sql .= " SELECT test_ques.id,";
        $sql .= " test_ques.sample_id, test_ques.question_id, test_ques.is_checked";
        $sql .= "  FROM sample_test_results_question as test_ques";
        $sql .= "  WHERE test_ques.sample_id = :sampleId ";
        $param['sampleId'] = $sampleId;
        $data = $this->query($sql, $param);
        return $data;
    }
    
    /**
    * select search sample data for TestResult
    *
    * @author Aye Thandar Lwin
    *
    * @param layer_code,period
    */
    public function search_FillResult_forall_ba($period)
    {
        $param = array();
    
        $sql  = "";
        $sql .= " SELECT samples.id, samples.category,";
        $sql .= "        test.remark AS test_result,";
        $sql .= "        test.report_times,point_out1,point_out2,point_out3,report_necessary1,DATE_FORMAT(deadline_date1,'%Y-%m-%d') as deadline_date,DATE_FORMAT(deadline_date2,'%Y-%m-%d') as deadline_date2,DATE_FORMAT(deadline_date3,'%Y-%m-%d') as deadline_date3,test.testresult_finish";
        $sql .= "  FROM samples";
        $sql .= "  LEFT JOIN sample_test_results as test ";
        $sql .= "       ON samples.id = test.sample_id";
        $sql .= "  WHERE ";
        $sql .= "    date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND samples.flag != 0 ";
        $sql .= "    AND samples.flag IN(4,5,6,7,8,9,10) ";
        $sql .= "ORDER BY samples.id ASC";
    
       
        $param['period'] = $period;
    
        $data = $this->query($sql, $param);
        return $data;
    }
    
    /**
     * Update del flag 4 to 5 from 'Test Result Form'
     *
     * @author Aye Thandar Lwin
     *
     * @param sample_id,layer_code,period,user_id
     */
    public function Update_SampleTestResultsFlag($sample_id, $user_id, $layer_code, $period, $category)
    {
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE samples ";
        $sql .= " 	 SET flag = 5, ";
        $sql .= "		 created_by = :created_by, ";
        $sql .= "		 updated_date = :updated_date ";
        $sql .= "  WHERE id = :sample_id ";
        $sql .= "    AND layer_code = :layer_code ";
        $sql .= "    AND category = :category ";
        $sql .= "    AND DATE_FORMAT(period,'%Y-%m') = :period ";
        $sql .= "    AND flag = 4 ";
    
        $currentTimestamp = date('Y-m-d H:i:s');
        
        $param['sample_id'] = $sample_id;
        $param['layer_code'] = $layer_code;
        $param['category'] = $category;
        $param['period'] = $period;
        $param['created_by'] = $user_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql, $param);
    }
    /**
     * Update flag 5 =>Account Manager Approve Cancel FROM Test Result Form
     *
     * @author Aye Thandar Lwin
     *
     * @param sample_id
     * @return data
     */
    public function Cancel_Approve_sample_flag($sample_id, $login_id)
    {
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE samples ";
        $sql .= " 	 SET flag = 5, ";  #orginal code is => flag = flag-1,
        $sql .= "		 updated_by = :updated_by, ";   #Change by NUNULWIN (24.1.2020) accourding customer fb.
        $sql .= "		 updated_date = :updated_date ";
        $sql .= "  WHERE id = :sample_id ";
        
        $sql .= "  AND (flag = '7' OR flag = '10')";
    
        
        $currentTimestamp = date('Y-m-d H:i:s');
         
        $param['sample_id'] = $sample_id;
        $param['updated_by'] = $login_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql, $param);
    }

    /**
     * Update flag 5 =>Account Manager Approve Cancel FROM Test Result Form
     *
     * @author sandi khaing
     *
     * @param sample_id
     * @return data
     */
    public function Cancel_Reject_sample_flag($sample_id, $login_id)
    {
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE samples ";
        $sql .= "    SET flag = 5, ";              #orginal code is => flag = flag-1,
        $sql .= "        updated_by = :updated_by, ";   #Change by NUNULWIN (24.1.2020) accourding customer fb.
        $sql .= "        updated_date = :updated_date ";
        $sql .= "  WHERE id = :sample_id ";
        
        $sql .= "  AND (flag = '6' OR flag = '9')";
    
        
        $currentTimestamp = date('Y-m-d H:i:s');
         
        $param['sample_id'] = $sample_id;
        $param['updated_by'] = $login_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql, $param);
    }
    /**
     * Update flag 5 to 6 from 'Test Result Form' when manager approve
     *
     * @author Aye Thandar Lwin
     *
     * @param sample_id,layer_code,period,user_id
     */
    public function Update_Approve_sample_flag($sample_id, $layer_code, $period, $user_id, $category)
    {
        $param = array();
    
        $sql  = "";
        $sql  .= " UPDATE samples INNER JOIN sample_test_results";
        $sql  .= "     ON samples.id = sample_test_results.sample_id";
        $sql  .= "	 SET samples.flag = samples.flag+1,";
        $sql  .= "		 sample_test_results.flag  = 3,";
        $sql  .= "		 samples.updated_by=:updated_by,samples.updated_date=:updated_date,";
        $sql  .= "		 sample_test_results.updated_by=:updated_by,sample_test_results.updated_date=:updated_date";
        $sql  .= "  WHERE samples.id = :sample_id";
        $sql  .= "	  AND (samples.flag = 9 OR samples.flag = 6)";
        $sql  .= "	 AND sample_test_results.flag = 2";
        $sql  .= "	 AND DATE_FORMAT(samples.period,'%Y-%m')=:period";
        $sql  .= "	 AND samples.layer_code = :layer_code";
        $sql  .= "	 AND samples.category = :category";
        
    
        $currentTimestamp = date('Y-m-d H:i:s');
         
        $param['sample_id'] = $sample_id;
        $param['layer_code'] = $layer_code;
        $param['category'] = $category;
        $param['period'] = $period;
        $param['updated_by'] = $user_id;
        $param['updated_date'] = $currentTimestamp;
        
        $this->query($sql, $param);
    }
    
    /**
     * MonnthlyProgress
     *
     * @author Aye Zar Ni Kyaw
     *
     * @param period
     */

    public function MonnthlyProgress($period,$check)
    {
        $param = array();
        
        $sql = "";
        $sql .= " select * from  (select *,(select case when 1=".$check." THEN GROUP_CONCAT(name_en order by group1.layer_code) ELSE GROUP_CONCAT(name_jp order by group1.layer_code) END as name from layers as group1 JOIN (
            SELECT MAX(`id`) as id 
            FROM layers 
            GROUP BY layer_code
            ) group2 ON group1.id = group2.id where FIND_IN_SET(group1.layer_code,replace(replace(replace(replace(json_extract(res.parent_id,'$.*'), '[', ''), ']', ''),'\"',''),' ','')) group by flag order by group1.layer_code)as head_name from (SELECT samples.layer_code, samples.category, count(samples.id) as sample_acc_incharge_num,name_en,name_jp,parent_id FROM samples 
                    left join layers on samples.layer_code = layers.layer_code
                    WHERE date_format(period, '%Y-%m') = :period and layers.from_date <= :date and layers.to_date >= :date and samples.flag >= 1 AND layers.flag = 1 and layers.object = 1 GROUP BY layer_code , category) res) sample_acc_incharge_data  ";          
        $sql .= " left outer join            
             (SELECT layer_code,category,count(id) as sample_acc_sub_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 2 GROUP BY layer_code, category) sample_acc_sub_manager_data
             ON sample_acc_incharge_data.layer_code = sample_acc_sub_manager_data.layer_code AND sample_acc_incharge_data.category = sample_acc_sub_manager_data.category";
        $sql .= "  left outer join
            (SELECT layer_code,category,count(id) as sample_acc_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 2 GROUP BY layer_code, category) sample_acc_manager_data
            ON sample_acc_incharge_data.layer_code = sample_acc_manager_data.layer_code AND sample_acc_incharge_data.category = sample_acc_manager_data.category";
       
        $sql .= " left outer join  (SELECT sample.layer_code,sample.category,count(sample.id) as data_bus_incharge_num from (SELECT sample.layer_code,sample.category,sample.id  FROM samples as sample JOIN sample_acc_attachments as attach ON sample.id = attach.sample_id  WHERE date_format(period, '%Y-%m') = :period and sample.flag >= 2 and attach.status = 2 and attach.flag = 1 GROUP BY sample.id)sample GROUP BY sample.layer_code, sample.category) data_bus_incharge_data
            ON sample_acc_incharge_data.layer_code = data_bus_incharge_data.layer_code AND sample_acc_incharge_data.category = data_bus_incharge_data.category
            left outer join
            (SELECT layer_code,category,count(id) as data_bus_sub_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 3 GROUP BY layer_code, category) data_bus_sub_manager_data
            ON sample_acc_incharge_data.layer_code = data_bus_sub_manager_data.layer_code AND sample_acc_incharge_data.category = data_bus_sub_manager_data.category
            left outer join
            (SELECT layer_code,category,count(id) as data_bus_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 4 GROUP BY layer_code, category) data_bus_manager_data
            ON sample_acc_incharge_data.layer_code = data_bus_manager_data.layer_code AND sample_acc_incharge_data.category = data_bus_manager_data.category
            ";
        $sql .= " left outer join  (SELECT layer_code,category,count(sample.id) as result_acc_incharge_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id WHERE date_format(period, '%Y-%m') = :period and (result.flag >= 1 OR report_times >= 2) GROUP BY layer_code, category) result_acc_incharge_data
            ON sample_acc_incharge_data.layer_code = result_acc_incharge_data.layer_code AND sample_acc_incharge_data.category = result_acc_incharge_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as result_acc_sub_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id WHERE date_format(period, '%Y-%m') = :period and (result.flag >= 2 OR report_times >= 2) GROUP BY layer_code, category) result_acc_sub_manager_data
            ON sample_acc_incharge_data.layer_code = result_acc_sub_manager_data.layer_code AND sample_acc_incharge_data.category = result_acc_sub_manager_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as result_acc_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id WHERE date_format(period, '%Y-%m') = :period and (result.flag >= 3 OR report_times >= 2)  GROUP BY layer_code,category) result_acc_manager_data
            ON sample_acc_incharge_data.layer_code = result_acc_manager_data.layer_code AND sample_acc_incharge_data.category = result_acc_manager_data.category
            ";
        $sql .= " left outer join (SELECT layer_code,category,count(sample.id) as check_bus_incharge_num FROM samples as sample JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE date_format(period, '%Y-%m') = :period and 
            checklist.flag >= 1 GROUP BY layer_code, category) check_bus_incharge_data
            ON sample_acc_incharge_data.layer_code = check_bus_incharge_data.layer_code AND sample_acc_incharge_data.category = check_bus_incharge_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as check_bus_sub_manager_num FROM samples as sample JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE date_format(period, '%Y-%m') = :period and checklist.flag >= 2 GROUP BY layer_code, category) check_bus_sub_manager_data
            ON sample_acc_incharge_data.layer_code = check_bus_sub_manager_data.layer_code AND sample_acc_incharge_data.category = check_bus_sub_manager_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as check_bus_manager_num FROM samples as sample JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE date_format(period, '%Y-%m') = :period and checklist.flag >= 3 GROUP BY layer_code, category) check_bus_manager_data
            ON sample_acc_incharge_data.layer_code = check_bus_manager_data.layer_code AND sample_acc_incharge_data.category = check_bus_manager_data.category
            ";
        $sql .= " left outer join  (SELECT layer_code,category,count(sample.id) as wrap_acc_incharge_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE (date_format(period, '%Y-%m') = :period and  sample.flag >= 5 and report_times = 2 and checklist.flag = 4) OR (date_format(period, '%Y-%m') = :period and sample.flag >= 5 and report_times = 3) GROUP BY layer_code, category) wrap_acc_incharge_data
            ON sample_acc_incharge_data.layer_code = wrap_acc_incharge_data.layer_code AND sample_acc_incharge_data.category = wrap_acc_incharge_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as wrap_acc_sub_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE (date_format(period, '%Y-%m') = :period and sample.flag >= 6 and report_times = 2 and checklist.flag = 4) OR (date_format(period, '%Y-%m') = :period and sample.flag >= 6 and report_times = 3 and result.flag >= 2) GROUP BY layer_code, category) wrap_acc_sub_manager_data
            ON sample_acc_incharge_data.layer_code = wrap_acc_sub_manager_data.layer_code AND sample_acc_incharge_data.category = wrap_acc_sub_manager_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as wrap_acc_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE (date_format(period, '%Y-%m') = :period and sample.flag >= 7 and report_times = 2 and checklist.flag = 4 and result.flag =3) OR (date_format(period, '%Y-%m') = :period and sample.flag >= 7 and report_times = 3 and result.flag = 3) GROUP BY layer_code, category) wrap_acc_manager_data
            ON sample_acc_incharge_data.layer_code = wrap_acc_manager_data.layer_code AND sample_acc_incharge_data.category = wrap_acc_manager_data.category";

        $param['period'] = $period;
        $param['date']        = $period.'-01';
        $data = $this->query($sql, $param);
        return $data;
    }

    /**
     * MonnthlyProgress
     *
     * @author Aye Zar Ni Kyaw
     *
     * @param period
     */

     public function paginate($conditions, $fields, $order, $limit, $page=1, $recursive=null, $extra=array())
     {
         $row_start = ($page-1) * $limit;
         $param = array();
         
         $param['period'] = $conditions['period'];
         $param['date']        = $conditions['period'].'-01';
         $check                = $conditions['check'];
         $sql = "";
         $sql .= " select * from  (select *,(select case when 1=".$check." THEN GROUP_CONCAT(name_en order by group1.layer_code) ELSE GROUP_CONCAT(name_jp order by group1.layer_code) END as name from layers as group1 JOIN (
             SELECT MAX(`id`) as id 
             FROM layers 
             GROUP BY layer_code
             ) group2 ON group1.id = group2.id where FIND_IN_SET(group1.layer_code,replace(replace(replace(replace(json_extract(res.parent_id,'$.*'), '[', ''), ']', ''),'\"',''),' ','')) group by flag order by group1.layer_code)as head_name from (SELECT samples.layer_code, samples.category, count(samples.id) as sample_acc_incharge_num,name_en,name_jp,parent_id FROM samples 
                     left join layers on samples.layer_code = layers.layer_code
                     WHERE date_format(period, '%Y-%m') = :period and layers.from_date <= :date and layers.to_date >= :date and samples.flag >= 1 AND layers.flag = 1 and layers.object = 1 GROUP BY layer_code , category) res) sample_acc_incharge_data  ";          
        $sql .= " left outer join            
              (SELECT layer_code,category,count(id) as sample_acc_sub_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 2 GROUP BY layer_code, category) sample_acc_sub_manager_data
              ON sample_acc_incharge_data.layer_code = sample_acc_sub_manager_data.layer_code AND sample_acc_incharge_data.category = sample_acc_sub_manager_data.category";
        $sql .= "  left outer join
             (SELECT layer_code,category,count(id) as sample_acc_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 2 GROUP BY layer_code, category) sample_acc_manager_data
             ON sample_acc_incharge_data.layer_code = sample_acc_manager_data.layer_code AND sample_acc_incharge_data.category = sample_acc_manager_data.category";
        
        $sql .= " left outer join  (SELECT sample.layer_code,sample.category,count(sample.id) as data_bus_incharge_num from (SELECT sample.layer_code,sample.category,sample.id  FROM samples as sample JOIN sample_acc_attachments as attach ON sample.id = attach.sample_id  WHERE date_format(period, '%Y-%m') = :period and sample.flag >= 2 and attach.status = 2 and attach.flag = 1 GROUP BY sample.id)sample GROUP BY sample.layer_code, sample.category) data_bus_incharge_data
             ON sample_acc_incharge_data.layer_code = data_bus_incharge_data.layer_code AND sample_acc_incharge_data.category = data_bus_incharge_data.category
             left outer join
             (SELECT layer_code,category,count(id) as data_bus_sub_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 3 GROUP BY layer_code, category) data_bus_sub_manager_data
             ON sample_acc_incharge_data.layer_code = data_bus_sub_manager_data.layer_code AND sample_acc_incharge_data.category = data_bus_sub_manager_data.category
             left outer join
             (SELECT layer_code,category,count(id) as data_bus_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 4 GROUP BY layer_code, category) data_bus_manager_data
             ON sample_acc_incharge_data.layer_code = data_bus_manager_data.layer_code AND sample_acc_incharge_data.category = data_bus_manager_data.category
             ";
        $sql .= " left outer join  (SELECT layer_code,category,count(sample.id) as result_acc_incharge_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id WHERE date_format(period, '%Y-%m') = :period and (result.flag >= 1 OR report_times >= 2) GROUP BY layer_code, category) result_acc_incharge_data
             ON sample_acc_incharge_data.layer_code = result_acc_incharge_data.layer_code AND sample_acc_incharge_data.category = result_acc_incharge_data.category
             left outer join
             (SELECT layer_code,category,count(sample.id) as result_acc_sub_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id WHERE date_format(period, '%Y-%m') = :period and (result.flag >= 2 OR report_times >= 2) GROUP BY layer_code, category) result_acc_sub_manager_data
             ON sample_acc_incharge_data.layer_code = result_acc_sub_manager_data.layer_code AND sample_acc_incharge_data.category = result_acc_sub_manager_data.category
             left outer join
             (SELECT layer_code,category,count(sample.id) as result_acc_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id WHERE date_format(period, '%Y-%m') = :period and (result.flag >= 3 OR report_times >= 2)  GROUP BY layer_code,category) result_acc_manager_data
             ON sample_acc_incharge_data.layer_code = result_acc_manager_data.layer_code AND sample_acc_incharge_data.category = result_acc_manager_data.category
             ";
        $sql .= " left outer join (SELECT layer_code,category,count(sample.id) as check_bus_incharge_num FROM samples as sample JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE date_format(period, '%Y-%m') = :period and 
             checklist.flag >= 1 GROUP BY layer_code, category) check_bus_incharge_data
             ON sample_acc_incharge_data.layer_code = check_bus_incharge_data.layer_code AND sample_acc_incharge_data.category = check_bus_incharge_data.category
             left outer join
             (SELECT layer_code,category,count(sample.id) as check_bus_sub_manager_num FROM samples as sample JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE date_format(period, '%Y-%m') = :period and checklist.flag >= 2 GROUP BY layer_code, category) check_bus_sub_manager_data
             ON sample_acc_incharge_data.layer_code = check_bus_sub_manager_data.layer_code AND sample_acc_incharge_data.category = check_bus_sub_manager_data.category
             left outer join
             (SELECT layer_code,category,count(sample.id) as check_bus_manager_num FROM samples as sample JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE date_format(period, '%Y-%m') = :period and checklist.flag >= 3 GROUP BY layer_code, category) check_bus_manager_data
             ON sample_acc_incharge_data.layer_code = check_bus_manager_data.layer_code AND sample_acc_incharge_data.category = check_bus_manager_data.category
             ";
        $sql .= " left outer join  (SELECT layer_code,category,count(sample.id) as wrap_acc_incharge_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE (date_format(period, '%Y-%m') = :period and  sample.flag >= 5 and report_times = 2 and checklist.flag = 4) OR (date_format(period, '%Y-%m') = :period and sample.flag >= 5 and report_times = 3) GROUP BY layer_code, category) wrap_acc_incharge_data
             ON sample_acc_incharge_data.layer_code = wrap_acc_incharge_data.layer_code AND sample_acc_incharge_data.category = wrap_acc_incharge_data.category
             left outer join
             (SELECT layer_code,category,count(sample.id) as wrap_acc_sub_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE (date_format(period, '%Y-%m') = :period and sample.flag >= 6 and report_times = 2 and checklist.flag = 4) OR (date_format(period, '%Y-%m') = :period and sample.flag >= 6 and report_times = 3 and result.flag >= 2) GROUP BY layer_code, category) wrap_acc_sub_manager_data
             ON sample_acc_incharge_data.layer_code = wrap_acc_sub_manager_data.layer_code AND sample_acc_incharge_data.category = wrap_acc_sub_manager_data.category
             left outer join
             (SELECT layer_code,category,count(sample.id) as wrap_acc_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE (date_format(period, '%Y-%m') = :period and sample.flag >= 7 and report_times = 2 and checklist.flag = 4 and result.flag =3) OR (date_format(period, '%Y-%m') = :period and sample.flag >= 7 and report_times = 3 and result.flag = 3) GROUP BY layer_code, category) wrap_acc_manager_data
             ON sample_acc_incharge_data.layer_code = wrap_acc_manager_data.layer_code AND sample_acc_incharge_data.category = wrap_acc_manager_data.category";
 
        $sql .= "     LIMIT " . $row_start;
        $sql .= "        , " . $limit;
        
        $results = $this->query($sql, $param);
        return $results;
     }

    /**
     * MonnthlyProgress
     *
     * @author Aye Zar Ni Kyaw
     *
     * @param period
     */

    public function paginateCount($conditions = null, $recursive = 0, $extra = array())
    {
        $this->recursive = -1;
        
        $param['period'] = $conditions['period'];
        $param['date']        = $conditions['period'].'-01';
        $check                = $conditions['check'];
        $sql = "";
        $sql .= " select count(*) from  (select *,(select case when 1=".$check." THEN GROUP_CONCAT(name_en order by group1.layer_code) ELSE GROUP_CONCAT(name_jp order by group1.layer_code) END as name from layers as group1 JOIN (
            SELECT MAX(`id`) as id 
            FROM layers 
            GROUP BY layer_code
            ) group2 ON group1.id = group2.id where FIND_IN_SET(group1.layer_code,replace(replace(replace(replace(json_extract(res.parent_id,'$.*'), '[', ''), ']', ''),'\"',''),' ','')) group by flag order by group1.layer_code)as head_name from (SELECT samples.layer_code, samples.category, count(samples.id) as sample_acc_incharge_num,name_en,name_jp,parent_id FROM samples 
                    left join layers on samples.layer_code = layers.layer_code
                    WHERE date_format(period, '%Y-%m') = :period and layers.from_date <= :date and layers.to_date >= :date and samples.flag >= 1 AND layers.flag = 1 and layers.object = 1 GROUP BY layer_code , category) res) sample_acc_incharge_data  ";          
        $sql .= " left outer join            
             (SELECT layer_code,category,count(id) as sample_acc_sub_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 2 GROUP BY layer_code, category) sample_acc_sub_manager_data
             ON sample_acc_incharge_data.layer_code = sample_acc_sub_manager_data.layer_code AND sample_acc_incharge_data.category = sample_acc_sub_manager_data.category";
        $sql .= "  left outer join
            (SELECT layer_code,category,count(id) as sample_acc_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 2 GROUP BY layer_code, category) sample_acc_manager_data
            ON sample_acc_incharge_data.layer_code = sample_acc_manager_data.layer_code AND sample_acc_incharge_data.category = sample_acc_manager_data.category";
       
        $sql .= " left outer join  (SELECT sample.layer_code,sample.category,count(sample.id) as data_bus_incharge_num from (SELECT sample.layer_code,sample.category,sample.id  FROM samples as sample JOIN sample_acc_attachments as attach ON sample.id = attach.sample_id  WHERE date_format(period, '%Y-%m') = :period and sample.flag >= 2 and attach.status = 2 and attach.flag = 1 GROUP BY sample.id)sample GROUP BY sample.layer_code, sample.category) data_bus_incharge_data
            ON sample_acc_incharge_data.layer_code = data_bus_incharge_data.layer_code AND sample_acc_incharge_data.category = data_bus_incharge_data.category
            left outer join
            (SELECT layer_code,category,count(id) as data_bus_sub_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 3 GROUP BY layer_code, category) data_bus_sub_manager_data
            ON sample_acc_incharge_data.layer_code = data_bus_sub_manager_data.layer_code AND sample_acc_incharge_data.category = data_bus_sub_manager_data.category
            left outer join
            (SELECT layer_code,category,count(id) as data_bus_manager_num FROM samples WHERE date_format(period, '%Y-%m') = :period and flag >= 4 GROUP BY layer_code, category) data_bus_manager_data
            ON sample_acc_incharge_data.layer_code = data_bus_manager_data.layer_code AND sample_acc_incharge_data.category = data_bus_manager_data.category
            ";
        $sql .= " left outer join  (SELECT layer_code,category,count(sample.id) as result_acc_incharge_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id WHERE date_format(period, '%Y-%m') = :period and (result.flag >= 1 OR report_times >= 2) GROUP BY layer_code, category) result_acc_incharge_data
            ON sample_acc_incharge_data.layer_code = result_acc_incharge_data.layer_code AND sample_acc_incharge_data.category = result_acc_incharge_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as result_acc_sub_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id WHERE date_format(period, '%Y-%m') = :period and (result.flag >= 2 OR report_times >= 2) GROUP BY layer_code, category) result_acc_sub_manager_data
            ON sample_acc_incharge_data.layer_code = result_acc_sub_manager_data.layer_code AND sample_acc_incharge_data.category = result_acc_sub_manager_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as result_acc_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id WHERE date_format(period, '%Y-%m') = :period and (result.flag >= 3 OR report_times >= 2)  GROUP BY layer_code,category) result_acc_manager_data
            ON sample_acc_incharge_data.layer_code = result_acc_manager_data.layer_code AND sample_acc_incharge_data.category = result_acc_manager_data.category
            ";
        $sql .= " left outer join (SELECT layer_code,category,count(sample.id) as check_bus_incharge_num FROM samples as sample JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE date_format(period, '%Y-%m') = :period and 
            checklist.flag >= 1 GROUP BY layer_code, category) check_bus_incharge_data
            ON sample_acc_incharge_data.layer_code = check_bus_incharge_data.layer_code AND sample_acc_incharge_data.category = check_bus_incharge_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as check_bus_sub_manager_num FROM samples as sample JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE date_format(period, '%Y-%m') = :period and checklist.flag >= 2 GROUP BY layer_code, category) check_bus_sub_manager_data
            ON sample_acc_incharge_data.layer_code = check_bus_sub_manager_data.layer_code AND sample_acc_incharge_data.category = check_bus_sub_manager_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as check_bus_manager_num FROM samples as sample JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE date_format(period, '%Y-%m') = :period and checklist.flag >= 3 GROUP BY layer_code, category) check_bus_manager_data
            ON sample_acc_incharge_data.layer_code = check_bus_manager_data.layer_code AND sample_acc_incharge_data.category = check_bus_manager_data.category
            ";
        $sql .= " left outer join  (SELECT layer_code,category,count(sample.id) as wrap_acc_incharge_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE (date_format(period, '%Y-%m') = :period and  sample.flag >= 5 and report_times = 2 and checklist.flag = 4) OR (date_format(period, '%Y-%m') = :period and sample.flag >= 5 and report_times = 3) GROUP BY layer_code, category) wrap_acc_incharge_data
            ON sample_acc_incharge_data.layer_code = wrap_acc_incharge_data.layer_code AND sample_acc_incharge_data.category = wrap_acc_incharge_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as wrap_acc_sub_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE (date_format(period, '%Y-%m') = :period and sample.flag >= 6 and report_times = 2 and checklist.flag = 4) OR (date_format(period, '%Y-%m') = :period and sample.flag >= 6 and report_times = 3 and result.flag >= 2) GROUP BY layer_code, category) wrap_acc_sub_manager_data
            ON sample_acc_incharge_data.layer_code = wrap_acc_sub_manager_data.layer_code AND sample_acc_incharge_data.category = wrap_acc_sub_manager_data.category
            left outer join
            (SELECT layer_code,category,count(sample.id) as wrap_acc_manager_num FROM samples as sample JOIN sample_test_results as result ON sample.id = result.sample_id JOIN sample_checklists as checklist ON sample.id = checklist.sample_id WHERE (date_format(period, '%Y-%m') = :period and sample.flag >= 7 and report_times = 2 and checklist.flag = 4 and result.flag =3) OR (date_format(period, '%Y-%m') = :period and sample.flag >= 7 and report_times = 3 and result.flag = 3) GROUP BY layer_code, category) wrap_acc_manager_data
            ON sample_acc_incharge_data.layer_code = wrap_acc_manager_data.layer_code AND sample_acc_incharge_data.category = wrap_acc_manager_data.category";

        $results = $this->query($sql, $param);
        return $results[0][0]['count(*)'];
    }
    
    /**
     * Update flag 5 =>Account Manager Approve Cancel
     *
     * @author Aye Thandar Lwin
     *
     * @param sample_id
     * @return data
     */
    public function Cancel_Approve_Checklist($sample_id, $user_id)
    {
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE samples ";
        $sql .= " 	 SET flag = 7, ";
        $sql .= "		 updated_by = :updated_by, ";
        $sql .= "		 updated_date = :updated_date ";
        $sql .= "  WHERE id = :sample_id ";
    
        $currentTimestamp = date('Y-m-d H:i:s');
    
        $param['sample_id'] = $sample_id;
        $param['updated_by'] = $user_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql, $param);
    }
    /**
     * Update flag 6 OR 9 when click Account Sub Manager Save&Review Button
     *
     * @author Aye Thandar Lwin
     *
     * @param sample_id,layer_code,period,user_id
     */
    public function AccSubMgrUpdateFlag($sample_id, $user_id, $layer_code, $period, $flag, $category)
    {
        $role_id = CakeSession::read('ADMIN_LEVEL_ID');
        
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE samples ";
        $sql .= " 	 SET flag = :flag, ";
        $sql .= "		 updated_by = :updated_by, ";
        $sql .= "		 updated_date = :updated_date ";
        $sql .= "  WHERE id = :sample_id ";
        $sql .= "    AND layer_code = :layer_code ";
        $sql .= "    AND category = :category ";
        $sql .= "    AND DATE_FORMAT(period,'%Y-%m') = :period ";
        
        if ($role_id == 4) {
            $sql .= "    AND flag IN ('5') ";
        } elseif ($role_id == 3) {
            $sql .= "    AND flag IN ('5','6','9') ";
        }
        
        
        
        $currentTimestamp = date('Y-m-d H:i:s');
         
        $param['sample_id'] = $sample_id;
        $param['layer_code'] = $layer_code;
        $param['category'] = $category;
        $param['period'] = $period;
        $param['flag'] = $flag;
        $param['updated_by'] = $user_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql, $param);
    }

    /**
     * To show review, approve, approve cancel button on warp up tab 1
     *
     * @author Thura Moe
     *
     * @param sample_id,layer_code,period,user_id
     */
    public function showWarpUpTab1Button($period, $layer_code, $category)
    {
        $bind = [];
        $sql = "SELECT sd.id, sd.period, sd.layer_code, sd.flag, chk.improvement_situation1, chk.improvement_situation2, chk.flag ";
        $sql .= "FROM samples AS sd ";
        $sql .= "left JOIN sample_checklists AS chk ON (sd.id = chk.sample_id)  ";
        $sql .= "WHERE DATE_FORMAT(sd.period, '%Y-%m') = :period AND sd.layer_code = :layer_code AND sd.category = :category ";
        $sql .= "AND (sd.flag=5 OR sd.flag=6 OR sd.flag=7 OR sd.flag=8 OR sd.flag=9 OR sd.flag=10) ";
        $sql .= "AND ((chk.improvement_situation2 IS NULL) OR (chk.improvement_situation2 = '')) ";
        $sql .= "AND ((NOT (chk.improvement_situation1 IS NULL)) AND (NOT (chk.improvement_situation1 = ''))) ";
        $sql .= "LIMIT 1";
        $bind['period'] = $period;
        $bind['layer_code'] = $layer_code;
        $bind['category'] = $category;
        $data = $this->query($sql, $bind);
        return $data;
    }
    
    /**
     * check flag is 4 or 5 when file is deleted by Business Incharge
     *
     * @author Aye Thandar Lwin
     *
     * @param layer_code,period
     */
    public function search_flag_file_delete($period, $sample_id)
    {
        $param = array();
    
        $sql  = "";
        $sql .= "   SELECT sample.flag ";
        $sql .= "     FROM sample_acc_attachments AS sample_attach ";
        $sql .= "LEFT JOIN samples AS sample ";
        $sql .= " 		ON (sample.id = sample_attach.sample_id) ";
        $sql .= "  WHERE sample_attach.id = :sample_id ";
        $sql .= "	 AND sample_attach.status = 1 ";
        $sql .= "    AND date_format(period,'%Y-%m') = :period ";

        $param['period'] = $period;
        $param['sample_id'] = $sample_id;
        
        $data = $this->query($sql, $param);
        return $data;
    }

    /**
     * If total count of selected period, layer_code and all of these data is flag 3
     * then show approve button
     * @author Thura Moe
     * @param period, layer_code
     **/
    public function chkToShowHideApprove($period, $layer_code, $flag)
    {
        $sql = "SELECT m.*, IF(m.total_Count=m.already_request AND m.total_Count!=0 AND m.already_request!=0,true,false) as approveStatus From ( ";
        $sql .= "SELECT COUNT(IF(DATE_FORMAT(period, '%Y-%m')=:period AND layer_code=:ba AND flag!=0,1,NULL)) 'total_Count', ";
        $sql .= "COUNT(IF(DATE_FORMAT(period, '%Y-%m')=:period AND layer_code=:ba AND flag=:flag,1,NULL)) 'already_request' ";
        $sql .= "FROM samples) as m";
        $param['period'] = $period;
        $param['ba'] = $layer_code;
        $param['flag'] = $flag;
        $rsl = $this->query($sql, $param);
        return $rsl;
    }


    /**
     * getlastEmailUser  data register page save  function
     * @author Sandi Khaing
     *
     * @param layer_code
     * @return data
     *
     */
    public function getlastEmailUser($layer_code, $period, $category)
    {
        $param = array();
     
        $sql = "";
        $sql.= " SELECT ";
        $sql.= "  layer_code,period,updated_by,created_by,submission_deadline_date";
        $sql.= "   FROM samples ";
        $sql.= "  WHERE samples.layer_code=:layer_code";
        $sql.= "  AND samples.category=:category";
        $sql.= " AND date_format(period,'%Y-%m') = :period ";
        $sql.= "  AND samples.flag!= 0";
     
        $param['layer_code'] = $layer_code;
        $param['category'] = $category;
        $param['period'] = $period;      
        $data = $this->query($sql, $param);
        
        return $data;
    }

    /**
     *counting for sample data for save all   request check mail
     * @author Sandi Khaing
     *
     * @param layer_code,period
     * @return data
     *
     */
     
    public function FlagCheckRequestEmail($layer_code, $period)
    {
        $param = array();
     
        $sql = "";
        $sql.= " SELECT ";
        $sql.= "  layer_code,period,flag";
        $sql.= "   FROM samples ";
        
        $sql.= "  WHERE samples.layer_code = :layer_code";
        $sql.= " AND date_format(period,'%Y-%m') = :period ";
       
        $sql.= "  AND samples.flag = 2";
        
      
        $param['layer_code'] = $layer_code;
        $param['period'] = $period;
       
        $data = $this->query($sql, $param);

     
        return $data;
    }

    /**
     * For Warp Up form (1st tab & 2nd tab)
     * If reject state, change the flag 9 or 6 to 5 in samples and
        change flag 2 to 1 insample_test_results
     * then redo save&review state
     * @author Khin Hnin Myo
     * @param admin_id,period,layer_code,sample_id
     **/
    public function FlagReject($admin_id, $period, $layer_code, $sample_id, $category)
    {
        $param = array();
        $sql  = "";
        $sql  .= " UPDATE samples INNER JOIN sample_test_results";
        $sql  .= " ON samples.id = sample_test_results.sample_id";
        $sql  .= " SET samples.flag = 5,";
        $sql  .= "     sample_test_results.flag  = 1,";
        $sql  .= "     samples.updated_date=:updated_date,";
        $sql  .= "     sample_test_results.updated_date=:updated_date";
        $sql  .= " WHERE samples.id IN (".$sample_id.")";
        $sql  .= " AND sample_test_results.sample_id IN (".$sample_id.")";
        $sql  .= " AND (samples.flag = 9 OR samples.flag = 6)";
        $sql  .= " AND sample_test_results.flag = 2";
        $sql  .= " AND DATE_FORMAT(samples.period,'%Y-%m')=:period";
        $sql  .= " AND samples.layer_code = :layer_code";
        $sql  .= " AND samples.category = :category";
            
        $currentTimestamp = date('Y-m-d H:i:s');
        $param['layer_code'] = $layer_code;
        $param['period'] = $period;
        $param['category'] = $category;
        $param['updated_date'] = $currentTimestamp;
       
        $this->query($sql, $param);
        return $this->query($sql, $param);
    }

}
