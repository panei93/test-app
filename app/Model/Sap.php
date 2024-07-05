<?php
App::uses('AppModel', 'Model');
/**
 * Sap Model
 *
 * @property SapAccInchargeComment $SapAccInchargeComment
 * @property SapAccManagerApprove $SapAccManagerApprove
 * @property SapAccSubmanagerComment $SapAccSubmanagerComment
 * @property SapBusiAdminComment $SapBusiAdminComment
 * @property SapBusiInchargeComment $SapBusiInchargeComment
 * @property SapBusiManagerApprove $SapBusiManagerApprove
 */
class Sap extends AppModel {

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
		'account_slip_no' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'account_statement_no' => array(
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

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'SapAccInchargeComment' => array(
			'className' => 'SapAccInchargeComment',
			'foreignKey' => 'sap_id',
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
		'SapAccManagerApprove' => array(
			'className' => 'SapAccManagerApprove',
			'foreignKey' => 'sap_id',
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
		'SapAccSubmanagerComment' => array(
			'className' => 'SapAccSubmanagerComment',
			'foreignKey' => 'sap_id',
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
		'SapBusiAdminComment' => array(
			'className' => 'SapBusiAdminComment',
			'foreignKey' => 'sap_id',
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
		'SapBusiInchargeComment' => array(
			'className' => 'SapBusiInchargeComment',
			'foreignKey' => 'sap_id',
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
		'SapBusiManagerApprove' => array(
			'className' => 'SapBusiManagerApprove',
			'foreignKey' => 'sap_id',
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
     * Update flag 7 =>Account SubManager Comment
     *
     * @author Aye Thandar Lwin
     *
     * @param sap_id 
     * @return data
     */
    public function Update_Sap_flag($sap_id) {
         
        $param = array();
         
        $sql  = "";
        $sql .= " UPDATE saps ";
        $sql .= "    SET flag = 7, ";
        $sql .= "        updated_date = :updated_date ";
        $sql .= "  WHERE id = :sap_id ";
        $sql .= "  AND flag = 6 ";
        
        $currentTimestamp = date('Y-m-d H:i:s');
        $param['sap_id'] = $sap_id;     
        $param['updated_date'] = $currentTimestamp;
         
        $this->query($sql,$param);
         
    }
    
    /**
     *
     *
     * @author Nu Nu Lwin
     * @Form ImportSAP
     * @Date 20-Feb-2019
     * @param $data 
     * 
     */

    public function ExcelImport($data){
    
        $param = array();
        $date = date('Y-m-d H:i:s');

        $param['legacy_clearing']  = $data['legacy_clearing'];
        $param['organization']     = $data['organization'];
        $param['ba_code']          = $data['ba_code'];
        $param['account_code']     = $data['account_code'];
        $param['account_name']     = $data['account_name'];
        $param['logistic_index_no'] = $data['logistic_index_no'];
        $param['currency']         = $data['currency']; 
        $param['jp_amount']        = $data['jp_amount'];
        $param['vi']               = $data['vi'];
        $param['foreign_amount']   = $data['foreign_amount'];
        $param['destination_code'] = $data['destination_code'];
        $param['destination_name'] = $data['destination_name'];
        $param['incurrent_class']  = $data['incurrent_class'];
        $param['schedule_date']    = $data['schedule_date'];
        $param['numbers_day']      = $data['numbers_day'];
        $param['reference_number'] = $data['reference_number'];
        $param['pm']               = $data['pm'];
        $param['commencement_date'] = $data['commencement_date'];
        $param['maturity_date']    = $data['maturity_date'];
        // $param['receipt_pay_date'] = $data['receipt_pay_date'];
        $param['cash_receipt_pay_desti_cd'] = $data['cash_receipt_pay_desti_cd'];
        $param['inspection_category'] = $data['inspection_category'];
        $param['parent_index_no']  = $data['parent_index_no'];
        $param['contract_no']      = $data['contract_no'];
        $param['transaction_search_key'] = $data['transaction_search_key'];
        $param['line_item_text']   = $data['line_item_text'];
        $param['invoice_management'] = $data['invoice_management'];
        $param['claim_receive_flg']  = $data['claim_receive_flg'];
        $param['transaction_type']   = $data['transaction_type'];
        $param['sale_representative'] = $data['sale_representative'];
        $param['docu_no_row']        = $data['docu_no_row'];
        $param['in_out_date']        = $data['in_out_date'];
        $param['counterparty_cd']    = $data['counterparty_cd'];
        $param['item_code']          = $data['item_code'];
        $param['item_name']          = $data['item_name'];
        $param['item_name_2']        = $data['item_name_2'];
        $param['standard_grade']     = $data['standard_grade'];
        $param['consignment_ship_cd'] = $data['consignment_ship_cd'];
        $param['goods_receipt_issue_no'] = $data['goods_receipt_issue_no'];
        $param['sale_date']          = $data['sale_date'];
        $param['sale_purchase_no']   = $data['sale_purchase_no'];
        $param['ref_item_no']        = $data['ref_item_no'];
        $param['unit']               = $data['unit'];
        $param['quantity']           = $data['quantity'];
        $param['unit_price']         = $data['unit_price'];
        $param['transaction_system'] = $data['transaction_system'];
        $param['slip_ymd_no']        = $data['slip_ymd_no'];
        $param['supplementary_qty']  = $data['supplementary_qty'];
        $param['oversea_store_r_no'] = $data['oversea_store_r_no'];
        $param['lot_no']             = $data['lot_no'];
        $param['payee']              = $data['payee'];
        $param['opponent_subject']   = $data['opponent_subject'];
        $param['division_ratio']     = $data['division_ratio'];
        $param['category_branch_no'] = $data['category_branch_no'];
        $param['bl_date']            = $data['bl_date'];
        $param['borrower_code']      = $data['borrower_code'];
        $param['shipper_code']       = $data['shipper_code'];
        $param['company_nominal_district']  = $data['company_nominal_district'];    
        $param['product_sales_destination'] = $data['product_sales_destination'];
        $param['product_supplier']   = $data['product_supplier'];
        $param['country_code']       = $data['country_code'];
        $param['country_origin_code']= $data['country_origin_code'];
        $param['ship_name']          = $data['ship_name'];
        $param['harbor_name']        = $data['harbor_name'];
        $param['port_name']          = $data['port_name'];
        $param['year']               = $data['year'];
        $param['account_slip_no']    = $data['account_slip_no'];
        $param['account_statement_no'] = $data['account_statement_no'];
        $param['type']               = $data['type'];
        $param['pk']                 = $data['pk'];
        $param['borrow_classification'] = $data['borrow_classification'];   
        $param['posting_date']       = $data['posting_date'];
        $param['recorded_date']      = $data['recorded_date'];
        $param['registration_date']  = $data['registration_date'];
        $param['consumption_tax']    = $data['consumption_tax'];
        $param['clearing_date']      = $data['clearing_date'];
        $param['clearing_slip']      = $data['clearing_slip'];
        $param['request_no']         = $data['request_no'];
      
        $param['preview_comment']    = "";
        $param['period']   = $data['period'];
        $param['flag']     = '1';
        $param['cdate']    = $date;
        $param['created_by'] = $data['created_by'];
        $param['updated_by'] = $data['updated_by'];

        
        $sql  = "";
        $sql .= "  INSERT INTO `saps`
                                (`period`,
                                 `legacy_clearing`,
                                 `organization`,
                                 `layer_code`,
                                 `account_code`,
                                 `account_name`,
                                 `logistic_index_no`,
                                 `currency`,
                                 `jp_amount`,
                                 `vi`,
                                 `foreign_amount`,
                                 `destination_code`,
                                 `destination_name`,
                                 `incurrent_class`,
                                 `schedule_date`,
                                 `numbers_day`,
                                 `reference_number`,
                                 `pm`,
                                 `commencement_date`,
                                 `maturity_date`,
                                 -- `receipt_pay_date`,
                                 `cash_receipt_pay_desti_cd`,
                                 `inspection_category`,
                                 `parent_index_no`,
                                 `contract_no`,
                                 `transaction_search_key`,
                                 `line_item_text`,
                                 `invoice_management`,
                                 `claim_receive_flg`,
                                 `transaction_type`,
                                 `sale_representative`,
                                 `docu_no_row`,
                                 `in_out_date`,
                                 `counterparty_cd`,
                                 `item_code`,
                                 `item_name`,
                                 `item_name_2`,
                                 `standard_grade`,
                                 `consignment_ship_cd`,
                                 `goods_receipt_issue_no`,
                                 `sale_date`,
                                 `sale_purchase_no`,
                                 `ref_item_no`,
                                 `unit`,
                                 `quantity`,
                                 `unit_price`,
                                 `transaction_system`,
                                 `slip_ymd_no`,
                                 `supplementary_qty`,
                                 `oversea_store_r_no`,
                                 `lot_no`,
                                 `payee`,
                                 `opponent_subject`,
                                 `division_ratio`,
                                 `category_branch_no`,
                                 `bl_date`,
                                 `borrower_code`,
                                 `shipper_code`,
                                 `company_nominal_district`,
                                 `product_sales_destination`,
                                 `product_supplier`,
                                 `country_code`,
                                 `country_origin_code`,
                                 `ship_name`,
                                 `harbor_name`,
                                 `port_name`,
                                 `year`,
                                 `account_slip_no`,
                                 `account_statement_no`,
                                 `type`,
                                 `pk`,
                                 `borrow_classification`,
                                 `posting_date`,
                                 `recorded_date`,
                                 `registration_date`,
                                 `consumption_tax`,
                                 `clearing_date`,
                                 `clearing_slip`,
                                 `request_no`,
                                 `preview_comment`,
                                 `flag`,
                                 `created_by`,
                                 `updated_by`,
                                 `created_date`,
                                 `updated_date`)
                    VALUES      ( :period,
                                  :legacy_clearing,
                                 :organization,
                                 :ba_code,
                                 :account_code,
                                 :account_name,
                                 :logistic_index_no,
                                 :currency,
                                 :jp_amount,
                                 :vi,
                                 :foreign_amount,
                                 :destination_code,
                                 :destination_name,
                                 :incurrent_class,
                                 :schedule_date,
                                 :numbers_day,
                                 :reference_number,
                                 :pm,
                                 :commencement_date,
                                 :maturity_date,
                                 -- :receipt_pay_date,
                                 :cash_receipt_pay_desti_cd,
                                 :inspection_category,
                                 :parent_index_no,
                                 :contract_no,
                                 :transaction_search_key,
                                 :line_item_text,
                                 :invoice_management,
                                 :claim_receive_flg,
                                 :transaction_type,
                                 :sale_representative,
                                 :docu_no_row,
                                 :in_out_date,
                                 :counterparty_cd,
                                 :item_code,
                                 :item_name,
                                 :item_name_2,
                                 :standard_grade,
                                 :consignment_ship_cd,
                                 :goods_receipt_issue_no,
                                 :sale_date,
                                 :sale_purchase_no,
                                 :ref_item_no,
                                 :unit,
                                 :quantity,
                                 :unit_price,
                                 :transaction_system,
                                 :slip_ymd_no,
                                 :supplementary_qty,
                                 :oversea_store_r_no,
                                 :lot_no,
                                 :payee,
                                 :opponent_subject,
                                 :division_ratio,
                                 :category_branch_no,
                                 :bl_date,
                                 :borrower_code,
                                 :shipper_code,
                                 :company_nominal_district,
                                 :product_sales_destination,
                                 :product_supplier,
                                 :country_code,
                                 :country_origin_code,
                                 :ship_name,
                                 :harbor_name,
                                 :port_name,
                                 :year,
                                 :account_slip_no,
                                 :account_statement_no,
                                 :type,
                                 :pk,
                                 :borrow_classification,
                                 :posting_date,
                                 :recorded_date,
                                 :registration_date,
                                 :consumption_tax,
                                 :clearing_date,
                                 :clearing_slip,
                                 :request_no,
                                 :preview_comment,
                                 :flag,
                                 :created_by,
                                 :updated_by,
                                 :cdate,
                                 :cdate )";

        $result =  $this->query($sql,$param);
    
        return $result;

    }
   
    /**
     * Update del flag 6 => 1st condition = user click delete link
     *                      2nd condition = user click unchecked
     *
     * @author Aye Thandar Lwin
     *
     * @param sap_id
     */
    public function Update_Del_flag($sap_id,$login_id) {
    
        $param = array();
   
        $sql  = "";
        $sql .= " UPDATE saps ";
        $sql .= "    SET flag = 0, ";
        $sql .= "        updated_by = :updated_by, ";
        $sql .= "        updated_date = :updated_date ";
        $sql .= "  WHERE id = :sap_id ";
        $sql .= "  AND flag != 8 ";
    
        $currentTimestamp = date('Y-m-d H:i:s');
        
        $param['sap_id'] = $sap_id;
        $param['updated_by'] = $login_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql,$param);
        
    
    }
    /**
     * search data for save Acc Submanager Approve
     *
     * @author Aye Thandar Lwin
     *
     * @param business area,period
     * @return data
     */
    public function Search_SAP_Data($period,$layer_code,$flag) {
         
        $param = array();
         
        $sql  = "";
        $sql .= " SELECT id,period,layer_code,flag ";
        $sql .= "   FROM saps  ";
        $sql .= "  WHERE DATE_FORMAT(period,'%Y-%m') = :period ";
        
        if($layer_code != ''){
            $sql .= "    AND layer_code = :layer_code ";
            $param['layer_code'] = $layer_code;
        }
        
        $sql .= "    AND  flag = :flag ";
    
        $param['period'] = $period;
        $param['flag'] = $flag;
         
        $data =$this->query($sql, $param);
         
        return $data;
    }
    /**
     * Update flag 8 => Account SubManager Approve
     *
     * @author Aye Thandar Lwin
     *
     * @param sap_id
     */
    public function Update_AccManager_Approve($sap_id,$login_id) {
    
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE saps ";
        $sql .= "    SET flag = 8, ";
        $sql .= "        updated_by = :updated_by, ";
        $sql .= "        updated_date = :updated_date ";
        $sql .= "  WHERE id = :sap_id ";
        $sql .= "  AND flag = 7 ";
    
        $currentTimestamp = date('Y-m-d H:i:s');
        $param['sap_id'] = $sap_id;
        $param['updated_by'] = $login_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql,$param);
        
    
    }
    /**
     * Update flag 7 => Account Manager Approve Cancel
     *
     * @author Aye Thandar Lwin
     *
     * @param sap_id
     */
    public function AccManager_Approve_Cancel($sap_id,$user_id) {
    
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE saps ";
        $sql .= "    SET flag = 6, ";
        $sql .= "        updated_by = :updated_by, ";
        $sql .= "        updated_date = :updated_date ";
        $sql .= "  WHERE id = :sap_id ";
    
        $currentTimestamp = date('Y-m-d H:i:s');
        
        $param['sap_id'] = $sap_id;
        $param['updated_by'] = $user_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql,$param);
    
    }
    /**
     * Update del flag 6 => 1st condition = user click unchecked
     *
     * @author Aye Thandar Lwin
     *
     * @param sap_id
     */
    public function Update_Uncheck_flag($sap_id,$user_id) {
    
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE saps ";
        $sql .= "    SET flag = 6, ";
        $sql .= "        updated_by = :updated_by, ";
        $sql .= "        updated_date = :updated_date ";
        $sql .= "  WHERE id = :sap_id ";
        $sql .= "  AND (flag = 6 ||flag = 7)";
        
        $currentTimestamp = date('Y-m-d H:i:s');
        $param['sap_id'] = $sap_id;
        $param['updated_by'] = $user_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql,$param);
    
    }
    /**
     * select flag for btn hide/show
     *
     * @author Aye Thandar Lwin
     *
     * @param ba_code,period
     */
    public function search_flag($layer_code,$period) {
    
        $param = array();
    
        $sql  = "";
        $sql .= "   SELECT saps.flag,acc_sub_cmt.comment AS acc_submgr_comment ";
        $sql .= "     FROM saps ";
        $sql .= "LEFT JOIN tbl_acc_submanager_comment as acc_sub_cmt ";
        $sql .= "       ON acc_sub_cmt.sap_id = saps.id ";
        $sql .= "  WHERE ";
        if($layer_code != NULL ||$layer_code = ''){
            $sql .= "layer_code = :layer_code AND ";
            $param['layer_code'] = $layer_code;
        }
                
        $sql .= "     date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND saps.flag >1";
    
        
        $param['period'] = $period;
    
        $data = $this->query($sql,$param);
        return $data;       
    
    }
    
    /**
     * select data for Summary Report
     *
     * @author Aye Thandar Lwin
     *
     * @param ba_code,period
     */
    public function Search_equalOver_1Million($ba_code,$period) {
    
        $param = array();
    
        $sql  = "";
        $sql .= " SELECT * FROM ( ";
        $sql .= "               SELECT saps.layer_code,name_jp,destination_code, ";
        $sql .= "                      destination_name,date_format(schedule_date,'%Y-%m-%d') AS schedule_date,numbers_day, ";
        $sql .= "                      sum(jp_amount) yen_amt,preview_comment  ";
        $sql .= "                 FROM saps ";
        $sql .= "                 JOIN layers AS LayerGroup ";
        $sql .= "                   ON LayerGroup.layer_code = saps.layer_code ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       saps.layer_code = :ba_code
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND ";

            
            $param['ba_code'] = $ba_code;
            $param['periodYMD'] = $period.'-01';
        }
        $sql .= "                  date_format(period,'%Y-%m') = :period ";
        $sql .= "                  AND saps.flag != 0 ";
        $sql .= "                  AND LayerGroup.flag = 1 ";
        $sql .= "                  AND saps.account_code like '1%' ";
        $sql .= "             GROUP BY saps.destination_code) AS tmp ";
        $sql .= "             WHERE tmp.yen_amt > 1000000 ";
        $sql .= "             ORDER BY tmp.destination_code ";
    
        $param['period'] = $period;
        
        $data = $this->query($sql,$param);
        
        return $data;
    
    }
    /**
     * select data for result one 
     *
     * @author Aye Thandar Lwin
     *
     * @param destination_code
     */
    public function result_one_data($dest_code,$ba_code,$period) {
    
        $param = array();
        
        $sql  = "";
        $sql .= "SELECT saps.id,saps.layer_code,saps.name_jp,saps.destination_code, ";
        $sql .= "           saps.destination_name,date_format(saps.schedule_date,'%Y-%m-%d') AS schedule_date,";
        $sql .= "           saps.numbers_day,jp_amount,saps.preview_comment,";
        $sql .= "           busi_admin_cmt.comment AS busi_admin_comment ";
        $sql .= "           ,busi_inc_cmt.reason, ";
        $sql .= "           busi_inc_cmt.settlement_date,busi_inc_cmt.remark,";
        $sql .= "           acc_inc_cmt.comment AS acc_inc_comment,  ";
        $sql .= "           acc_submgr_cmt.comment AS acc_submanager_comment ";
        $sql .= "       FROM (	";
        $sql .= "	SELECT MIN(saps.id) id,saps.layer_code,LayerGroup.name_jp,";
        $sql .= "destination_code,destination_name,schedule_date,";
        $sql .= "	sum(jp_amount)jp_amount,numbers_day,preview_comment,";
        $sql .= "	DATEDIFF(base_date,schedule_date) as NoOfDays";
        $sql .= "	FROM saps LEFT JOIN layers AS LayerGroup  ";
        $sql .= "				  ON (LayerGroup.layer_code = saps.layer_code)";
        $sql .= "	 WHERE  ";
        if($ba_code != ''){
        	$sql .= "       saps.layer_code = :ba_code 
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND ";

            
            $param['ba_code'] = $ba_code;
            $param['periodYMD'] = $period.'-01';
        }
        $sql .= "        date_format(period,'%Y-%m') = :period ";
        $sql .= "        AND saps.destination_code IN (".$dest_code.")  ";
        $sql .= "        AND saps.flag != 0  ";
        $sql .= "        AND LayerGroup.flag = 1  ";
        $sql .= "        AND saps.account_code like '1%'  ";
     
        $sql .= "    GROUP BY saps.schedule_date,saps.logistic_index_no,saps.destination_code,saps.layer_code ";
        $sql .= "    ORDER BY destination_code";
        $sql .= "   )saps  ";
        $sql .= "LEFT JOIN sap_busi_incharge_comments AS busi_inc_cmt ";
        $sql .= "  ON (busi_inc_cmt.sap_id = saps.id AND busi_inc_cmt.flag=1) ";
        $sql .= "LEFT JOIN sap_busi_admin_comments AS busi_admin_cmt ";
        $sql .= "  ON (busi_admin_cmt.sap_id = saps.id AND busi_admin_cmt.flag=1) ";
        $sql .= "LEFT JOIN sap_acc_incharge_comments AS acc_inc_cmt ";
        $sql .= "  ON (acc_inc_cmt.sap_id = saps.id and acc_inc_cmt.flag=1) ";
        $sql .= "LEFT JOIN sap_acc_submanager_comments AS acc_submgr_cmt ";
        $sql .= "  ON (acc_submgr_cmt.sap_id = saps.id AND acc_submgr_cmt.flag=1) ";
        $sql .= "    ORDER BY saps.destination_code,saps.layer_code,saps.id";
    
        $param['period'] = $period;
        
        $data = $this->query($sql,$param);
        
        return $data;
    
    }
    /**
     * select data for result one
     *
     * @author Aye Thandar Lwin
     *
     * @param destination_code
     */
    public function sum_result_one($dest_code,$ba_code,$period) {
    
        $param = array();
         
        $sql  = "";
        $sql .= " SELECT destination_code,layer_code, ";
        $sql .= "       destination_name,sum(jp_amount) as total_amt ";
        $sql .= "   FROM saps ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       saps.layer_code = :ba_code AND ";
            $param['ba_code'] = $ba_code;
        }
        $sql .= "     date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND saps.destination_code IN (".$dest_code.") ";
        $sql .= "    AND saps.flag != 0 ";
        $sql .= "    AND saps.account_code like '1%' ";
        $sql .= "GROUP BY saps.destination_code ";
        $sql .= "ORDER BY destination_code ";
    
        $param['period'] = $period;
    
        $data = $this->query($sql,$param);
         
        return $data;
    
    }
    /**
     * select data for Summary Report=>No-2
     *
     * @author Aye Thandar Lwin
     *
     * @param ba_code,period
     */
    public function Search_under_1Million_morethan30days($ba_code,$period) {
        
        $param = array();
   
        $sql  = "";
        $sql .= " SELECT * FROM ( ";
        $sql .= "               SELECT saps.layer_code,LayerGroup.name_jp,saps.destination_code, ";
        $sql .= "                         saps.destination_name,date_format(saps.schedule_date,'%Y-%m-%d') AS schedule_date,numbers_day, ";
        $sql .= "                         sum(jp_amount) yen_amt,preview_comment  ";
        $sql .= "                 FROM saps ";
        $sql .= "                 JOIN layers AS LayerGroup ";
        $sql .= "                   ON LayerGroup.layer_code = saps.layer_code ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       saps.layer_code = :ba_code 
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND ";

            $param['ba_code'] = $ba_code;
            $param['periodYMD'] = $period.'-01';
        
        }
        $sql .= "                  date_format(period,'%Y-%m') = :period ";
        $sql .= "                  AND saps.flag != 0 ";
        $sql .= "                  AND LayerGroup.flag = 1 ";
        $sql .= "                  AND saps.account_code like '1%' ";
        $sql .= "             AND DATEDIFF(base_date,schedule_date) >= 30 ";
        $sql .= "             GROUP BY saps.destination_code) AS tmp ";
        $sql .= "             WHERE tmp.yen_amt < 1000000 ";
        $sql .= "ORDER BY tmp.destination_code ";
        
        $param['period'] = $period;
        
        $data = $this->query($sql,$param);
        
        return $data;
    
    }
    /**
     * select data for result one
     *
     * @author Aye Thandar Lwin
     *
     * @param destination_code
     */
    public function result_two_data($dest_code,$ba_code,$period) {
    
        $param = array();
         
        $sql  = "";
        $sql .= "SELECT saps.id,saps.layer_code,saps.name_jp,saps.destination_code, ";
        $sql .= "           saps.destination_name,date_format(saps.schedule_date,'%Y-%m-%d') AS schedule_date,";
        $sql .= "           saps.numbers_day,jp_amount,saps.preview_comment,";
        $sql .= "           busi_admin_cmt.comment AS busi_admin_comment ";
        $sql .= "           ,busi_inc_cmt.reason, ";
        $sql .= "           busi_inc_cmt.settlement_date,busi_inc_cmt.remark,";
        $sql .= "           acc_inc_cmt.comment AS acc_inc_comment,  ";
        $sql .= "           acc_submgr_cmt.comment AS acc_submanager_comment ";
        $sql .= "       FROM (	";
        $sql .= "	SELECT MIN(saps.id) id,saps.layer_code,LayerGroup.name_jp,";
        $sql .= "destination_code,destination_name,schedule_date,";
        $sql .= "	sum(jp_amount)jp_amount,numbers_day,preview_comment,";
        $sql .= "	DATEDIFF(base_date,schedule_date) as NoOfDays";
        $sql .= "	FROM saps LEFT JOIN layers AS LayerGroup  ";
        $sql .= "				  ON (LayerGroup.layer_code = saps.layer_code)";
        $sql .= "	 WHERE  ";
        if($ba_code != ''){
        	$sql .= "       saps.layer_code = :ba_code
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND ";

            $param['ba_code'] = $ba_code;
            $param['periodYMD'] = $period.'-01';
        }
        $sql .= "       date_format(period,'%Y-%m') = :period ";
        $sql .= "       AND saps.destination_code IN (".$dest_code.")  ";
        $sql .= "       AND saps.flag != 0  ";
        $sql .= "       AND LayerGroup.flag = 1  
                        AND LayerGroup.from_date <= :periodYMD 
                        AND LayerGroup.to_date >= :periodYMD "; 
        $sql .= "       AND saps.account_code like '1%'  ";
        $sql .= "	    AND DATEDIFF(base_date,schedule_date) >= 30";
        $sql .= "    GROUP BY saps.schedule_date,saps.logistic_index_no,saps.destination_code,saps.layer_code ";
        $sql .= "    ORDER BY destination_code";
        $sql .= "   )saps  ";
        $sql .= "LEFT JOIN sap_busi_incharge_comments AS busi_inc_cmt ";
        $sql .= "  ON (busi_inc_cmt.sap_id = saps.id AND busi_inc_cmt.flag=1) ";
        $sql .= "LEFT JOIN sap_busi_admin_comments AS busi_admin_cmt ";
        $sql .= "  ON (busi_admin_cmt.sap_id = saps.id AND busi_admin_cmt.flag=1) ";
        $sql .= "LEFT JOIN sap_acc_incharge_comments AS acc_inc_cmt ";
        $sql .= "  ON (acc_inc_cmt.sap_id = saps.id and acc_inc_cmt.flag=1) ";
        $sql .= "LEFT JOIN sap_acc_submanager_comments AS acc_submgr_cmt ";
        $sql .= "  ON (acc_submgr_cmt.sap_id = saps.id AND acc_submgr_cmt.flag=1) ";
        $sql .= "    ORDER BY saps.destination_code,saps.layer_code,saps.id ";
        
        $param['period'] = $period;
        $param['periodYMD'] = $period.'-01';
    
        $data = $this->query($sql,$param);
         
        return $data;
    
    }
    
    /**
     * select data for result one
     *
     * @author Aye Thandar Lwin
     *
     * @param destination_code
     */
    public function sum_result_two($dest_code,$ba_code,$period) {
    
        $param = array();
    
        $sql  = "";
        $sql .= " SELECT destination_code, ";
        $sql .= "       destination_name,sum(jp_amount) as total_amt ";
        $sql .= "   FROM saps ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       saps.layer_code = :ba_code AND ";
            $param['ba_code'] = $ba_code;
        }
        $sql .= "    date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND saps.destination_code IN (".$dest_code.") ";
        $sql .= "    AND saps.flag != 0 ";
        $sql .= "    AND saps.account_code like '1%' ";
        $sql .= "    AND DATEDIFF(base_date,schedule_date) >= 30 ";
        $sql .= "GROUP BY destination_code ";
        $sql .= "ORDER BY destination_code ";
       
        $param['period'] = $period;
        
        $data = $this->query($sql,$param);
    
        return $data;
    
    }
    
    /**
     * select data for Summary Report=>No-3= less than 1 million yen and less than 30 days
     *
     * @author Aye Thandar Lwin
     *
     * @param ba_code,period
     */
    public function Search_under_1Million_lessthan30days($ba_code,$period) {
    
        $param = array();
    
        $sql  = "";
        $sql .= " SELECT * FROM ( ";
        $sql .= "               SELECT saps.layer_code,name_jp,destination_code, ";
        $sql .= "                         destination_name,date_format(schedule_date,'%Y-%m-%d') AS schedule_date,numbers_day, ";
        $sql .= "                         sum(jp_amount) yen_amt,preview_comment  ";
        $sql .= "                 FROM saps ";
        $sql .= "                 JOIN layers AS LayerGroup ";
        $sql .= "                   ON LayerGroup.layer_code = saps.layer_code ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       saps.layer_code = :ba_code  
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND ";

            $param['ba_code'] = $ba_code;
            $param['periodYMD'] = $period.'-01';
        }
        $sql .= "                  date_format(period,'%Y-%m') = :period ";
        $sql .= "                  AND saps.flag != 0 ";
        $sql .= "                  AND LayerGroup.flag = 1 ";
        $sql .= "                  AND saps.account_code like '1%' ";
        $sql .= "                  AND DATEDIFF(base_date,schedule_date) < 30";
        $sql .= "             GROUP BY saps.destination_code) AS tmp ";
        $sql .= "           WHERE tmp.yen_amt < 1000000 ";
        $sql .= "ORDER BY tmp.destination_code ";
        
        $param['period'] = $period;
   
        $data = $this->query($sql,$param);
        
        return $data;
    
    }
    
    /**
     * select data for result one
     *
     * @author Aye Thandar Lwin
     *
     * @param destination_code
     */
    public function result_three_data($dest_code,$ba_code,$period) {
    
        $param = array();
        
        $sql  = "";
        $sql .= " SELECT saps.layer_code,name_jp,destination_code, ";
        $sql .= "       destination_name,date_format(schedule_date,'%Y-%m-%d') AS schedule_date,numbers_day, ";
        $sql .= "       sum(jp_amount) AS jp_amount,preview_comment ";
        $sql .= "   FROM saps ";
        $sql .= "   JOIN layers AS LayerGroup ";
        $sql .= "     ON LayerGroup.layer_code = saps.layer_code ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       saps.layer_code = :ba_code  
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND ";
            $param['ba_code'] = $ba_code;
            $param['periodYMD'] = $period.'-01';
           
        }
        $sql .= "    date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND saps.destination_code IN (".$dest_code.") ";
        $sql .= "    AND saps.flag != 0 ";
        $sql .= "    AND LayerGroup.flag = 1 ";
        $sql .= "    AND saps.account_code like '1%' ";
        $sql .= "    AND DATEDIFF(base_date,schedule_date) < 30 ";
        $sql .= "GROUP BY saps.schedule_date,saps.destination_code,saps.logistic_index_no,saps.layer_code ";
        $sql .= "ORDER BY destination_code ";
    
        $param['period'] = $period;
    
        $data = $this->query($sql,$param);
    
        return $data;
    
    }
    
    /**
     * select data for result one
     *
     * @author Aye Thandar Lwin
     *
     * @param destination_code
     */
    public function sum_result_three($dest_code,$ba_code,$period) {
    
        $param = array();
    
        $sql  = "";
        $sql .= " SELECT destination_code, ";
        $sql .= "       destination_name,sum(jp_amount) as total_amt ";
        $sql .= "   FROM saps ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       saps.layer_code = :ba_code AND ";
            $param['ba_code'] = $ba_code;
        }
        $sql .= "    date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND saps.destination_code IN (".$dest_code.") ";
        $sql .= "    AND saps.flag != 0 ";
        $sql .= "    AND saps.account_code like '1%' ";
        $sql .= "    AND DATEDIFF(base_date,schedule_date) < 30 ";
        $sql .= "GROUP BY destination_code ";
        $sql .= "ORDER BY destination_code ";
    
        $param['period'] = $period;
    
        $data = $this->query($sql,$param);
    
        return $data;
    
    }
    
    /**
     * select data for Summary Report(bottom table)->greater than 30 days and less than 60days
     *
     * @author Aye Thandar Lwin
     *
     * @param ba_code,period
     */
    public function Search_prev2Month_30_60days($ba_code,$prev_2_month,$period) {
         
        $param = array();
    
        $sql  = "";
        $sql .= "SELECT * FROM   ";
        $sql .= "                      (SELECT logistic_index_no,sum(sap.jp_amount) as jp_amount ";
        $sql .= "                  FROM saps AS sap  ";
        $sql .= "                  LEFT JOIN layers as LayerGroup   ";
        $sql .= "                    ON LayerGroup.layer_code = sap.layer_code ";
        $sql .= "                  WHERE";
        if($ba_code != ''){
        	$sql .= "       sap.layer_code = :ba_code 
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND";
        	$param['ba_code'] = $ba_code;
        }
        $sql .= "        sap.account_code LIKE '1%'  ";
        $sql .= "                     AND date_format(sap.posting_date,'%Y-%m-%d') <= :prev_month_2";
        $sql .= "                    AND LayerGroup.flag = 1  AND sap.flag != 0 AND sap.period = :periodYMD and DATEDIFF(sap.base_date,sap.schedule_date)  BETWEEN 30 AND 60  ";
        $sql .= "                 GROUP BY sap.logistic_index_no,sap.destination_code) tmp  ";
        $sql .= "                   JOIN  ";
        $sql .= "               ( SELECT sap.posting_date,sap.id,LayerGroup.name_jp,sap.account_name,sap.destination_code,sap.destination_name,sap.logistic_index_no, ";
        $sql .= "                      sap.maturity_date,busi_inc_cmt.reason,busi_inc_cmt.settlement_date,   ";
        $sql .= "        IF(busi_inc_cmt.flag  IS NULL,1,busi_inc_cmt.flag) AS busi_inc_cmt_flag , ";
        $sql .= "                      DATE_FORMAT(sap.schedule_date,'%Y-%m-%d') AS schedule_date,sap.numbers_day  ";
        $sql .= "        FROM saps AS sap  ";
        $sql .= "                       LEFT JOIN sap_busi_incharge_comments AS busi_inc_cmt  ";
        $sql .= "                    ON (sap.id = busi_inc_cmt.sap_id    and busi_inc_cmt.flag =1)";
        $sql .= "                  LEFT JOIN layers as LayerGroup   ";
        $sql .= "                    ON LayerGroup.layer_code = sap.layer_code   ";
        $sql .= "                  WHERE";
        if($ba_code != ''){
        	$sql .= "       sap.layer_code = :ba_code  
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND";
        	$param['ba_code'] = $ba_code;
        }
        $sql .= "        sap.account_code LIKE '1%'  ";
        $sql .= "                     AND date_format(sap.posting_date,'%Y-%m-%d') <= :prev_month_2";
        $sql .= "                    and sap.flag != 0 and LayerGroup.flag = 1 AND period = :periodYMD AND DATEDIFF(sap.base_date,sap.schedule_date) BETWEEN 30 AND 60  ) tbl_2  ";
        $sql .= "                     ON tbl_2.logistic_index_no = tmp.logistic_index_no  ";
        $sql .= "                     ORDER BY tbl_2.posting_date desc,tbl_2.logistic_index_no,tbl_2.id ";
    
        
        $param['prev_month_2'] = $prev_2_month;
        $param['period'] = $period;
        /*** Added new condition to select with period according 
             to customer feedback (16.12.2019) ***/
        $periodYMD  = date('Y-m-d', strtotime($period));
        $param['periodYMD'] = $periodYMD;
        /****/
        $data = $this->query($sql,$param);
      
        return $data;
    
    }
    
    /**
     * select data for Summary Report(bottom table)->greater than 30 days and less than 60days
     *
     * @author Aye Thandar Lwin
     *
     * @param ba_code,period
     */
    public function Search_prev2Month_60days($ba_code,$prev_2_month,$period) {
        
        $param = array();
        $sql  = "";
        $sql .= "SELECT * FROM   ";
        $sql .= "                      (SELECT logistic_index_no,sum(sap.jp_amount) as jp_amount ";
        $sql .= "                  FROM saps AS sap  ";
        $sql .= "                  LEFT JOIN layers as LayerGroup ";
        $sql .= "                    ON LayerGroup.layer_code = sap.layer_code ";
        $sql .= "                  WHERE";
    	if($ba_code != ''){
            $sql .= "       sap.layer_code = :ba_code 
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND";
            $param['ba_code'] = $ba_code;
        }
        $sql .= "        sap.account_code LIKE '1%'  ";
        $sql .= "                     AND date_format(sap.posting_date,'%Y-%m') <= :prev_month_2";
        $sql .= "                    AND LayerGroup.flag = 1  AND sap.flag != 0  AND sap.period = :periodYMD and DATEDIFF(sap.base_date,sap.schedule_date)  > 60  ";
        $sql .= "                 GROUP BY sap.logistic_index_no,sap.destination_code) tmp  ";
        $sql .= "                   JOIN  ";
        $sql .= "               ( SELECT sap.posting_date,sap.id,LayerGroup.name_jp,sap.account_name,sap.destination_code,sap.destination_name,sap.logistic_index_no, ";
        $sql .= "                      sap.maturity_date,busi_inc_cmt.reason,busi_inc_cmt.settlement_date,   ";
        $sql .= "        IF(busi_inc_cmt.flag  IS NULL,1,busi_inc_cmt.flag) AS busi_inc_cmt_flag , ";
        $sql .= "                      DATE_FORMAT(sap.schedule_date,'%Y-%m-%d') AS schedule_date,sap.numbers_day  ";
        $sql .= "        FROM saps AS sap  ";
        $sql .= "                       LEFT JOIN sap_busi_incharge_comments AS busi_inc_cmt  ";
        $sql .= "                    ON (sap.id = busi_inc_cmt.sap_id    and busi_inc_cmt.flag =1)";
        $sql .= "                  LEFT JOIN layers as LayerGroup   ";
        $sql .= "                    ON LayerGroup.layer_code = sap.layer_code   ";
        $sql .= "                  WHERE";
    	if($ba_code != ''){
            $sql .= "       sap.layer_code = :ba_code 
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND";
            $param['ba_code'] = $ba_code;
        }
        $sql .= "        sap.account_code LIKE '1%'  ";
        $sql .= "                     AND date_format(sap.posting_date,'%Y-%m') <= :prev_month_2";
        $sql .= "                    and sap.flag != 0 and LayerGroup.flag = 1 AND period = :periodYMD AND DATEDIFF(sap.base_date,sap.schedule_date) > 60  ) tbl_2  ";
        $sql .= "                     ON tbl_2.logistic_index_no = tmp.logistic_index_no  ";
        $sql .= "                     ORDER BY tbl_2.posting_date desc,tbl_2.logistic_index_no,tbl_2.id ";
    
        $param['prev_month_2'] = $prev_2_month;
        $param['period'] = $period;
        /*** Added new condition to select with period according 
             to customer feedback (16.12.2019) ***/
        $periodYMD  = date('Y-m-d', strtotime($period));
        $param['periodYMD'] = $periodYMD;
        /****/
        $data = $this->query($sql,$param);
         
        return $data;
    
    }
    
    /**
     * select data from view for Account Review Excel
     *
     * @author Aye Thandar Lwin
     *
     * @param ba_code,period from view
     */
    public function Account_Review_Excel($ba_code,$period) {
        
        $param = array();
        $sql  = "";
        $sql .= "   SELECT *,sum(jp_amount) as jp_amount from saps as account_review_excel";

        $sql .= " LEFT JOIN sap_busi_incharge_comments on account_review_excel.id = sap_busi_incharge_comments.sap_id AND sap_busi_incharge_comments.flag=1";

        $sql .= " LEFT JOIN sap_busi_admin_comments on account_review_excel.id = sap_busi_admin_comments.sap_id AND sap_busi_admin_comments.flag=1";

        $sql .= " LEFT JOIN sap_acc_incharge_comments on account_review_excel.id = sap_acc_incharge_comments.sap_id AND sap_acc_incharge_comments.flag=1";

        $sql .= " LEFT JOIN sap_acc_submanager_comments on account_review_excel.id = sap_acc_submanager_comments.sap_id AND sap_acc_submanager_comments.flag=1";

        $sql .= " WHERE date_format(account_review_excel.period,'%Y-%m') = :period AND account_review_excel.flag <> 0";
        if($ba_code != ''){
            $sql .= "  AND account_review_excel.layer_code = :ba_code ";
            $param['ba_code'] = $ba_code;
        }
        $sql .= " GROUP BY layer_code, account_code, destination_code, logistic_index_no, posting_date, recorded_date, schedule_date";
        $sql .= " ORDER BY account_review_excel.id,layer_code, account_code, destination_code, logistic_index_no, posting_date, recorded_date, schedule_date ";
        
        $param['period'] = $period;
        $data = $this->query($sql,$param);
        for ($i=0; $i < count($data); $i++) { 
            $data[$i]['account_review_excel']['reason'] = $data[$i]['sap_busi_incharge_comments']['reason'];
            $data[$i]['account_review_excel']['settlement_date'] = $data[$i]['sap_busi_incharge_comments']['settlement_date'];
            $data[$i]['account_review_excel']['remark'] = $data[$i]['sap_busi_incharge_comments']['remark'];
            $data[$i]['account_review_excel']['business_admin_comment'] = $data[$i]['sap_busi_admin_comments']['comment'];
            $data[$i]['account_review_excel']['acc_incharge_comment'] = $data[$i]['sap_acc_incharge_comments']['comment'];
            $data[$i]['account_review_excel']['acc_submgr_comment'] = $data[$i]['sap_acc_submanager_comments']['comment'];
        }
        return $data;
   
    }

    /**
     * select data from view for Add Comment Excel
     *
     * @author Nu Nu Lwin
     *
     * @param ba_code,period, search data (sale Representation)from view
     */
    public function Add_Comment_Excel($ba_code,$period,$searchRepre,$logistics) {
       
        $param = array();
        $sql  = "";
        $sql .= "   SELECT *,sum(jp_amount) as jp_amount from saps as account_review_excel";

        $sql .= " LEFT JOIN sap_busi_incharge_comments on account_review_excel.id = sap_busi_incharge_comments.sap_id AND sap_busi_incharge_comments.flag=1";

        $sql .= " LEFT JOIN sap_busi_admin_comments on account_review_excel.id = sap_busi_admin_comments.sap_id AND sap_busi_admin_comments.flag=1";

        $sql .= " LEFT JOIN sap_acc_incharge_comments on account_review_excel.id = sap_acc_incharge_comments.sap_id AND sap_acc_incharge_comments.flag=1";

        $sql .= " WHERE date_format(account_review_excel.period,'%Y-%m') = :period";
        $sql .= " AND account_review_excel.flag != 0";

        if($ba_code != ''){
            $sql .= "  AND account_review_excel.layer_code = :ba_code ";
            $param['ba_code'] = $ba_code;
        }
        if($searchRepre != ''){
            $sql .= " AND account_review_excel.sale_representative LIKE  :searchRepre ";
            $param['searchRepre'] = '%'.$searchRepre.'%';
        }
        if($logistics != ''){
            $sql .= " AND account_review_excel.logistic_index_no LIKE  :logistics ";
            $param['logistics'] = '%'.$logistics.'%';
        }
        $sql .= " GROUP BY layer_code, account_code, destination_code, logistic_index_no, posting_date, recorded_date, schedule_date";
        $sql .= " ORDER BY account_review_excel.id,layer_code, account_code, destination_code, logistic_index_no, posting_date, recorded_date, schedule_date ";
        
        $param['period'] = $period;
        
        $data = $this->query($sql,$param);
        
        for ($i=0; $i < count($data); $i++) { 
            $data[$i]['account_review_excel']['reason'] = $data[$i]['sap_busi_incharge_comments']['reason'];
            $data[$i]['account_review_excel']['settlement_date'] = $data[$i]['sap_busi_incharge_comments']['settlement_date'];
            $data[$i]['account_review_excel']['remark'] = $data[$i]['sap_busi_incharge_comments']['remark'];
            $data[$i]['account_review_excel']['business_admin_comment'] = $data[$i]['sap_busi_admin_comments']['comment'];
            $data[$i]['account_review_excel']['acc_incharge_comment'] = $data[$i]['sap_acc_incharge_comments']['comment'];
        }
        
        return $data;
   
    }

    /**
     * Select Max, Min flag of each ba_code
     *
     * @author Thura Moe
     *
     * @param period 
     */

    public function getBACodeFlag($period) {
        $bind = [];
        $sql  = 'SELECT 
                  tmp.id, 
                  tmp.layer_code, 
                  max_flag, 
                  min_flag, 
                  tmp.name_jp, 
                  tmp.parent_id, 
                  busi_approve.approve_date, 
                  acc_approve.approve_date 
                FROM 
                  (
                    SELECT 
                      sap_data.id, 
                      sap_data.layer_code, 
                      MAX(sap_data.flag) as max_flag, 
                      MIN(sap_data.flag) as min_flag, 
                      sap_data.name_jp, 
                      sap_data.parent_id 
                    FROM 
                      (
                        SELECT 
                          sap.id, 
                          sap.layer_code, 
                          sap.flag, 
                          layerGroup.name_jp, 
                          layerGroup.parent_id 
                        FROM 
						saps as sap 
                          LEFT JOIN layers as layerGroup ON (sap.layer_code = layerGroup.layer_code) 
                        WHERE 
                          date_format(sap.period, "%Y-%m")= :period 
                          AND sap.flag > 1 
                          AND layerGroup.flag = 1 
                          AND layerGroup.from_date <= :period_date 
                          AND layerGroup.to_date >= :period_date
                      ) as sap_data 
                    GROUP BY 
                      sap_data.layer_code 
                    UNION 
                    SELECT 
                      sap_data.id, 
                      sap_data.layer_code, 
                      MAX(sap_data.flag) as max_flag, 
                      MIN(sap_data.flag) as min_flag, 
                      sap_data.name_jp, 
                      sap_data.parent_id 
                    FROM 
                      (
                        SELECT 
                          sap.id, 
                          sap.layer_code, 
                          sap.flag, 
                          layerGroup.name_jp, 
                          layerGroup.parent_id 
                        FROM 
						saps as sap 
                          LEFT JOIN layers as layerGroup ON (sap.layer_code = layerGroup.layer_code) 
                        WHERE 
                          date_format(sap.period, "%Y-%m")= :period 
                          AND sap.flag > 0 
                          AND layerGroup.flag = 1 
                          AND layerGroup.from_date <= :period_date 
                          AND layerGroup.to_date >= :period_date
                      ) as sap_data 
                    GROUP BY 
                      sap_data.layer_code
                  ) as tmp 
                  LEFT JOIN sap_busi_manager_approves as busi_approve ON (
                    tmp.id = busi_approve.sap_id 
                    and busi_approve.flag = 1
                  ) 
                  LEFT JOIN sap_acc_manager_approves as acc_approve ON (
                    tmp.id = acc_approve.sap_id 
                    and acc_approve.flag = 1
                  ) 
                GROUP BY 
                  tmp.layer_code';

        $bind['period'] = $period;
        $bind['period_date'] = $period.'-01';
        
        $rsl = $this->query($sql, $bind);
        return $rsl;
    }

    /**
     * Select complete BA
     *	[ if total row count of ba_code with flag<>0 and 
     *	  row count of these ba_code with flag=1 and not empty preview_comment is 	   same, then it is called complete BA ]
     *
     * @author Thura Moe
     *
     * @param period
     **/
    public function getCompleteBACode($period) {
    	$bind = [];
    	$sql = "SELECT ";
		$sql .= "	period, layer_code,";
		$sql .= "   SUM(IF(flag>0, 1, 0)) AS total_count,";
		$sql .= "	SUM(IF(flag=1 AND preview_comment<>'' AND preview_comment IS NOT NULL, 1, 0)) AS complete_count ";
		$sql .= "FROM saps ";
		$sql .= "WHERE date_format(period,'%Y-%m')=:period AND flag <> 0 ";
		$sql .= "GROUP by layer_code ";
		$sql .= "HAVING total_count = complete_count";
		$bind['period'] = $period;
		$result = $this->query($sql, $bind);
		return $result;
    }

    /** 
     * @author Nu Nu Lwin
     * @date 25.07.2019
     * 
    **/
    public function getMatchFlag($sapID){

        $param = array();
        $sql = "";
        $sql.= " SELECT
                       id 
                    FROM
					saps 
                    WHERE
                       period = 
                       (
                          SELECT period FROM saps WHERE id = :sapID
                       )
                       AND layer_code = 
                       (
                          SELECT TRIM(layer_code) FROM saps WHERE id = :sapID
                       )
                       AND account_code = 
                       (
                          SELECT TRIM(account_code) FROM saps WHERE id = :sapID
                       )
                       AND destination_code = 
                       (
                          SELECT TRIM(destination_code) FROM saps WHERE id = :sapID
                       )
                       AND logistic_index_no = 
                       (
                          SELECT TRIM(logistic_index_no) FROM saps WHERE id = :sapID
                       )
                       AND posting_date = 
                       (
                          SELECT TRIM(posting_date) FROM saps WHERE id = :sapID
                       )
                       AND recorded_date = 
                       (
                          SELECT TRIM(recorded_date) FROM saps WHERE id = :sapID
                       )
                       AND schedule_date = 
                       (
                          SELECT TRIM(schedule_date) FROM saps WHERE id = :sapID
                        )
                        AND flag = 
                        (
                            SELECT saps.flag FROM saps WHERE id = :sapID
                        ) ";


        $param['sapID'] = $sapID;
        $data = $this->query($sql,$param);
        
        return $data;
    }

    /** 
     * @author Nu Nu Lwin
     * @date 15.07.2019
     * for Account Preview 
     * add currency
    **/
    public function getMatchFlagPreview($sapID){

        $param = array();
        $sql = "";
        $sql.= " SELECT
                       id 
                    FROM
					saps 
                    WHERE
                       period = 
                       (
                          SELECT period FROM saps WHERE id = :sapID
                       )
                       AND layer_code = 
                       (
                          SELECT TRIM(layer_code) FROM saps WHERE id = :sapID
                       )
                       AND account_code = 
                       (
                          SELECT TRIM(account_code) FROM saps WHERE id = :sapID
                       )
                       AND destination_code = 
                       (
                          SELECT TRIM(destination_code) FROM saps WHERE id = :sapID
                       )
                       AND logistic_index_no = 
                       (
                          SELECT TRIM(logistic_index_no) FROM saps WHERE id = :sapID
                       )
                       AND posting_date = 
                       (
                          SELECT TRIM(posting_date) FROM saps WHERE id = :sapID
                       )
                       AND recorded_date = 
                       (
                          SELECT TRIM(recorded_date) FROM saps WHERE id = :sapID
                       )
                       AND schedule_date = 
                       (
                          SELECT TRIM(schedule_date) FROM saps WHERE id = :sapID
                        )
                       AND flag = 
                       (
                            SELECT flag FROM saps WHERE id = :sapID
                       )
                       AND currency =
                       (
                           SELECT currency FROM saps WHERE id = :sapID
                       )";


        $param['sapID'] = $sapID;
        $data = $this->query($sql,$param);
        
        return $data;
    }

}
