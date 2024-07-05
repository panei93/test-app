<?php
App::uses('AppModel', 'Model');
/**
 * Stock Model
 *
 * @property StockAccInchargeComment $StockAccInchargeComment
 * @property StockAccManagerApprove $StockAccManagerApprove
 * @property StockAccSubmanagerComment $StockAccSubmanagerComment
 * @property StockBusiAdminComment $StockBusiAdminComment
 * @property StockBusiInchargeComment $StockBusiInchargeComment
 * @property StockBusiManagerApprove $StockBusiManagerApprove
 */
class Stock extends AppModel {

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
		'StockAccInchargeComment' => array(
			'className' => 'StockAccInchargeComment',
			'foreignKey' => 'stock_id',
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
		'StockAccManagerApprove' => array(
			'className' => 'StockAccManagerApprove',
			'foreignKey' => 'stock_id',
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
		'StockAccSubmanagerComment' => array(
			'className' => 'StockAccSubmanagerComment',
			'foreignKey' => 'stock_id',
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
		'StockBusiAdminComment' => array(
			'className' => 'StockBusiAdminComment',
			'foreignKey' => 'stock_id',
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
		'StockBusiInchargeComment' => array(
			'className' => 'StockBusiInchargeComment',
			'foreignKey' => 'stock_id',
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
		'StockBusiManagerApprove' => array(
			'className' => 'StockBusiManagerApprove',
			'foreignKey' => 'stock_id',
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
     * @param Stock_id 
     * @return data
     */
    public function Update_Stock_flag($stock_id) {
         
        $param = array();
         
        $sql  = "";
        $sql .= "UPDATE stocks ";
        $sql .= "SET flag = 7, ";
        $sql .= "updated_date = :updated_date ";
        $sql .= "WHERE id = :stock_id ";
        $sql .= "AND flag = 6 ";
        
        $currentTimestamp = date('Y-m-d H:i:s');
        $param['stock_id'] = $stock_id;     
        $param['updated_date'] = $currentTimestamp;
       
        $this->query($sql, $param);
         
    }
    
    /**
     *
     *
     * @author Nu Nu Lwin
     * @Form Importstock
     * @Date 20-Feb-2019
     * @param $data 
     * 
     */

   //  public function ExcelImport($data){
    
   //      $param = array();
   //      $date = date('Y-m-d H:i:s');

   //      $param['legacy_clearing']  = $data['legacy_clearing'];
   //      $param['organization']     = $data['organization'];
   //      $param['ba_code']          = $data['ba_code'];
   //      $param['account_code']     = $data['account_code'];
   //      $param['account_name']     = $data['account_name'];
   //      $param['logistic_index_no'] = $data['logistic_index_no'];
   //      $param['currency']         = $data['currency']; 
   //      $param['jp_amount']        = $data['jp_amount'];
   //      $param['vi']               = $data['vi'];
   //      $param['foreign_amount']   = $data['foreign_amount'];
   //      $param['destination_code'] = $data['destination_code'];
   //      $param['destination_name'] = $data['destination_name'];
   //      $param['incurrent_class']  = $data['incurrent_class'];
   //      $param['schedule_date']    = $data['schedule_date'];
   //      $param['numbers_day']      = $data['numbers_day'];
   //      $param['reference_number'] = $data['reference_number'];
   //      $param['pm']               = $data['pm'];
   //      $param['commencement_date'] = $data['commencement_date'];
   //      $param['maturity_date']    = $data['maturity_date'];
   //      $param['receipt_pay_date'] = $data['receipt_pay_date'];
   //      $param['cash_receipt_pay_desti_cd'] = $data['cash_receipt_pay_desti_cd'];
   //      $param['inspection_category'] = $data['inspection_category'];
   //      $param['parent_index_no']  = $data['parent_index_no'];
   //      $param['contract_no']      = $data['contract_no'];
   //      $param['transaction_search_key'] = $data['transaction_search_key'];
   //      $param['line_item_text']   = $data['line_item_text'];
   //      $param['invoice_management'] = $data['invoice_management'];
   //      $param['claim_receive_flg']  = $data['claim_receive_flg'];
   //      $param['transaction_type']   = $data['transaction_type'];
   //      $param['sale_representative'] = $data['sale_representative'];
   //      $param['docu_no_row']        = $data['docu_no_row'];
   //      $param['in_out_date']        = $data['in_out_date'];
   //      $param['counterparty_cd']    = $data['counterparty_cd'];
   //      $param['item_code']          = $data['item_code'];
   //      $param['item_name']          = $data['item_name'];
   //      $param['item_name_2']        = $data['item_name_2'];
   //      $param['standard_grade']     = $data['standard_grade'];
   //      $param['consignment_ship_cd'] = $data['consignment_ship_cd'];
   //      $param['goods_receipt_issue_no'] = $data['goods_receipt_issue_no'];
   //      $param['sale_date']          = $data['sale_date'];
   //      $param['sale_purchase_no']   = $data['sale_purchase_no'];
   //      $param['ref_item_no']        = $data['ref_item_no'];
   //      $param['unit']               = $data['unit'];
   //      $param['quantity']           = $data['quantity'];
   //      $param['unit_price']         = $data['unit_price'];
   //      $param['transaction_system'] = $data['transaction_system'];
   //      $param['slip_ymd_no']        = $data['slip_ymd_no'];
   //      $param['supplementary_qty']  = $data['supplementary_qty'];
   //      $param['oversea_store_r_no'] = $data['oversea_store_r_no'];
   //      $param['lot_no']             = $data['lot_no'];
   //      $param['payee']              = $data['payee'];
   //      $param['opponent_subject']   = $data['opponent_subject'];
   //      $param['division_ratio']     = $data['division_ratio'];
   //      $param['category_branch_no'] = $data['category_branch_no'];
   //      $param['bl_date']            = $data['bl_date'];
   //      $param['borrower_code']      = $data['borrower_code'];
   //      $param['shipper_code']       = $data['shipper_code'];
   //      $param['company_nominal_district']  = $data['company_nominal_district'];    
   //      $param['product_sales_destination'] = $data['product_sales_destination'];
   //      $param['product_supplier']   = $data['product_supplier'];
   //      $param['country_code']       = $data['country_code'];
   //      $param['country_origin_code']= $data['country_origin_code'];
   //      $param['ship_name']          = $data['ship_name'];
   //      $param['harbor_name']        = $data['harbor_name'];
   //      $param['port_name']          = $data['port_name'];
   //      $param['year']               = $data['year'];
   //      $param['account_slip_no']    = $data['account_slip_no'];
   //      $param['account_statement_no'] = $data['account_statement_no'];
   //      $param['type']               = $data['type'];
   //      $param['pk']                 = $data['pk'];
   //      $param['borrow_classification'] = $data['borrow_classification'];   
   //      $param['posting_date']       = $data['posting_date'];
   //      $param['recorded_date']      = $data['recorded_date'];
   //      $param['registration_date']  = $data['registration_date'];
   //      $param['consumption_tax']    = $data['consumption_tax'];
   //      $param['clearing_date']      = $data['clearing_date'];
   //      $param['clearing_slip']      = $data['clearing_slip'];
   //      $param['request_no']         = $data['request_no'];
      
   //      $param['preview_comment']    = "";
   //      $param['period']   = $data['period'];
   //      $param['flag']     = '1';
   //      $param['cdate']    = $date;
   //      $param['created_by'] = $data['created_by'];
   //      $param['updated_by'] = $data['updated_by'];

        
   //      $sql  = "";
   //      $sql .= "  INSERT INTO `stocks`
   //                              (`period`,
   //                               `legacy_clearing`,
   //                               `organization`,
   //                               `layer_code`,
   //                               `account_code`,
   //                               `account_name`,
   //                               `logistic_index_no`,
   //                               `currency`,
   //                               `jp_amount`,
   //                               `vi`,
   //                               `foreign_amount`,
   //                               `destination_code`,
   //                               `destination_name`,
   //                               `incurrent_class`,
   //                               `schedule_date`,
   //                               `numbers_day`,
   //                               `reference_number`,
   //                               `pm`,
   //                               `commencement_date`,
   //                               `maturity_date`,
   //                               `receipt_pay_date`,
   //                               `cash_receipt_pay_desti_cd`,
   //                               `inspection_category`,
   //                               `parent_index_no`,
   //                               `contract_no`,
   //                               `transaction_search_key`,
   //                               `line_item_text`,
   //                               `invoice_management`,
   //                               `claim_receive_flg`,
   //                               `transaction_type`,
   //                               `sale_representative`,
   //                               `docu_no_row`,
   //                               `in_out_date`,
   //                               `counterparty_cd`,
   //                               `item_code`,
   //                               `item_name`,
   //                               `item_name_2`,
   //                               `standard_grade`,
   //                               `consignment_ship_cd`,
   //                               `goods_receipt_issue_no`,
   //                               `sale_date`,
   //                               `sale_purchase_no`,
   //                               `ref_item_no`,
   //                               `unit`,
   //                               `quantity`,
   //                               `unit_price`,
   //                               `transaction_system`,
   //                               `slip_ymd_no`,
   //                               `supplementary_qty`,
   //                               `oversea_store_r_no`,
   //                               `lot_no`,
   //                               `payee`,
   //                               `opponent_subject`,
   //                               `division_ratio`,
   //                               `category_branch_no`,
   //                               `bl_date`,
   //                               `borrower_code`,
   //                               `shipper_code`,
   //                               `company_nominal_district`,
   //                               `product_sales_destination`,
   //                               `product_supplier`,
   //                               `country_code`,
   //                               `country_origin_code`,
   //                               `ship_name`,
   //                               `harbor_name`,
   //                               `port_name`,
   //                               `year`,
   //                               `account_slip_no`,
   //                               `account_statement_no`,
   //                               `type`,
   //                               `pk`,
   //                               `borrow_classification`,
   //                               `posting_date`,
   //                               `recorded_date`,
   //                               `registration_date`,
   //                               `consumption_tax`,
   //                               `clearing_date`,
   //                               `clearing_slip`,
   //                               `request_no`,
   //                               `preview_comment`,
   //                               `flag`,
   //                               `created_by`,
   //                               `updated_by`,
   //                               `created_date`,
   //                               `updated_date`)
   //                  VALUES      ( :period,
   //                                :legacy_clearing,
   //                               :organization,
   //                               :ba_code,
   //                               :account_code,
   //                               :account_name,
   //                               :logistic_index_no,
   //                               :currency,
   //                               :jp_amount,
   //                               :vi,
   //                               :foreign_amount,
   //                               :destination_code,
   //                               :destination_name,
   //                               :incurrent_class,
   //                               :schedule_date,
   //                               :numbers_day,
   //                               :reference_number,
   //                               :pm,
   //                               :commencement_date,
   //                               :maturity_date,
   //                               :receipt_pay_date,
   //                               :cash_receipt_pay_desti_cd,
   //                               :inspection_category,
   //                               :parent_index_no,
   //                               :contract_no,
   //                               :transaction_search_key,
   //                               :line_item_text,
   //                               :invoice_management,
   //                               :claim_receive_flg,
   //                               :transaction_type,
   //                               :sale_representative,
   //                               :docu_no_row,
   //                               :in_out_date,
   //                               :counterparty_cd,
   //                               :item_code,
   //                               :item_name,
   //                               :item_name_2,
   //                               :standard_grade,
   //                               :consignment_ship_cd,
   //                               :goods_receipt_issue_no,
   //                               :sale_date,
   //                               :sale_purchase_no,
   //                               :ref_item_no,
   //                               :unit,
   //                               :quantity,
   //                               :unit_price,
   //                               :transaction_system,
   //                               :slip_ymd_no,
   //                               :supplementary_qty,
   //                               :oversea_store_r_no,
   //                               :lot_no,
   //                               :payee,
   //                               :opponent_subject,
   //                               :division_ratio,
   //                               :category_branch_no,
   //                               :bl_date,
   //                               :borrower_code,
   //                               :shipper_code,
   //                               :company_nominal_district,
   //                               :product_sales_destination,
   //                               :product_supplier,
   //                               :country_code,
   //                               :country_origin_code,
   //                               :ship_name,
   //                               :harbor_name,
   //                               :port_name,
   //                               :year,
   //                               :account_slip_no,
   //                               :account_statement_no,
   //                               :type,
   //                               :pk,
   //                               :borrow_classification,
   //                               :posting_date,
   //                               :recorded_date,
   //                               :registration_date,
   //                               :consumption_tax,
   //                               :clearing_date,
   //                               :clearing_slip,
   //                               :request_no,
   //                               :preview_comment,
   //                               :flag,
   //                               :created_by,
   //                               :updated_by,
   //                               :cdate,
   //                               :cdate )";

   //      $result =  $this->query($sql,$param);
    
   //      return $result;

   //  }
   
    /**
     * Update del flag 6 => 1st condition = user click delete link
     *                      2nd condition = user click unchecked
     *
     * @author Aye Thandar Lwin
     *
     * @param Stock_id
     */
    public function Update_Del_flag($stock_id,$login_id) {
    
        $param = array();
   
        $sql  = "";
        $sql .= " UPDATE stocks ";
        $sql .= "    SET flag = 0, ";
        $sql .= "        updated_by = :updated_by, ";
        $sql .= "        updated_date = :updated_date ";
        $sql .= "  WHERE id = :stock_id ";
        $sql .= "  AND flag != 8 ";
    
        $currentTimestamp = date('Y-m-d H:i:s');
        
        $param['stock_id'] = $stock_id;
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
    public function Search_Stock_Data($period,$layer_code,$flag) {
         
        $param = array();
         
        $sql  = "";
        $sql .= " SELECT id, period, layer_code, flag ";
        $sql .= "   FROM stocks  ";
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
     * @param Stock_id
     */
    public function Update_AccManager_Approve($stock_id,$login_id) {
    
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE stocks ";
        $sql .= "    SET flag = 8, ";
        $sql .= "        updated_by = :updated_by, ";
        $sql .= "        updated_date = :updated_date ";
        $sql .= "  WHERE id = :stock_id ";
        $sql .= "  AND flag = 7 ";
    
        $currentTimestamp = date('Y-m-d H:i:s');
        $param['stock_id'] = $stock_id;
        $param['updated_by'] = $login_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql,$param);
        
    
    }
    /**
     * Update flag 7 => Account Manager Approve Cancel
     *
     * @author Aye Thandar Lwin
     *
     * @param Stock_id
     */
    public function AccManager_Approve_Cancel($stock_id,$user_id) {
    
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE stocks ";
        $sql .= "    SET flag = 6, ";
        $sql .= "        updated_by = :updated_by, ";
        $sql .= "        updated_date = :updated_date ";
        $sql .= "  WHERE id = :stock_id ";
    
        $currentTimestamp = date('Y-m-d H:i:s');
        
        $param['stock_id'] = $stock_id;
        $param['updated_by'] = $user_id;
        $param['updated_date'] = $currentTimestamp;
    
        $this->query($sql,$param);
    
    }
    /**
     * Update del flag 6 => 1st condition = user click unchecked
     *
     * @author Aye Thandar Lwin
     *
     * @param Stock_id
     */
    public function Update_Uncheck_flag($stock_id,$user_id) {
    
        $param = array();
    
        $sql  = "";
        $sql .= " UPDATE stocks ";
        $sql .= "    SET flag = 6, ";
        $sql .= "        updated_by = :updated_by, ";
        $sql .= "        updated_date = :updated_date ";
        $sql .= "  WHERE id = :stock_id ";
        $sql .= "  AND (flag = 6 ||flag = 7)";
        
        $currentTimestamp = date('Y-m-d H:i:s');
        $param['stock_id'] = $stock_id;
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
    // public function search_flag($layer_code,$period) {
    
    //     $param = array();
    
    //     $sql  = "";
    //     $sql .= "   SELECT stocks.flag,acc_sub_cmt.comment AS acc_submgr_comment ";
    //     $sql .= "     FROM stocks ";
    //     $sql .= "LEFT JOIN tbl_acc_submanager_comment as acc_sub_cmt ";
    //     $sql .= "       ON acc_sub_cmt.stock_id = stocks.id ";
    //     $sql .= "  WHERE ";
    //     if($layer_code != NULL ||$layer_code = ''){
    //         $sql .= "layer_code = :layer_code AND ";
    //         $param['layer_code'] = $layer_code;
    //     }
                
    //     $sql .= "     date_format(period,'%Y-%m') = :period ";
    //     $sql .= "    AND stocks.flag >1";
    
        
    //     $param['period'] = $period;
    
    //     $data = $this->query($sql,$param);
    //     return $data;       
    
    // }
    
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
      $sql .= "               SELECT stocks.layer_code,name_jp,item_code, ";
      $sql .= "                      destination_name,numbers_day, ";
      $sql .= "                      sum(amount) yen_amt ";
      $sql .= "                 FROM stocks ";
      $sql .= "                 JOIN layers AS LayerGroup ";
      $sql .= "                   ON LayerGroup.layer_code = stocks.layer_code ";
      $sql .= " WHERE ";
      if($ba_code != ''){
          $sql .= "       stocks.layer_code = :ba_code
                          AND LayerGroup.from_date <= :periodYMD 
                          AND LayerGroup.to_date >= :periodYMD AND ";
          
          $param['ba_code'] = $ba_code;
          $param['periodYMD'] = $period.'-01';
      }
      $sql .= "                  date_format(period,'%Y-%m') = :period ";
      $sql .= "                  AND stocks.flag != 0 ";
      $sql .= "                  AND LayerGroup.flag = 1 ";
      // $sql .= "                  AND stocks.account_code like '1%' ";
      $sql .= "             GROUP BY stocks.destination_name) AS tmp ";
      $sql .= "             WHERE tmp.yen_amt > 1000000 ";
      $sql .= "             ORDER BY tmp.destination_name ";
  
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
      $sql .= "SELECT stocks.id,stocks.layer_code,stocks.name_jp,";
      $sql .= "           stocks.destination_name,";
      $sql .= "           stocks.numbers_day,stocks.amount,";
      $sql .= "           busi_admin_cmt.comment AS busi_admin_comment ";
      $sql .= "           ,busi_inc_cmt.reason, ";
      $sql .= "           busi_inc_cmt.settlement_date,busi_inc_cmt.remark,";
      $sql .= "           acc_inc_cmt.comment AS acc_inc_comment,  ";
      $sql .= "           acc_submgr_cmt.comment AS acc_submanager_comment ";
      $sql .= "       FROM (	";
      $sql .= "	SELECT MIN(stocks.id) id,stocks.layer_code,LayerGroup.name_jp,";
      $sql .= "destination_name,";
      $sql .= "	sum(amount) amount,numbers_day";
      // $sql .= "	DATEDIFF(base_date,schedule_date) as NoOfDays";
      $sql .= "	FROM stocks LEFT JOIN layers AS LayerGroup  ";
      $sql .= "				  ON (LayerGroup.layer_code = stocks.layer_code)";
      $sql .= "	 WHERE  ";
      if($ba_code != ''){
        $sql .= "       stocks.layer_code = :ba_code 
                          AND LayerGroup.from_date <= :periodYMD 
                          AND LayerGroup.to_date >= :periodYMD AND ";
          
          $param['ba_code'] = $ba_code;
          $param['periodYMD'] = $period.'-01';
      }
      $sql .= "        date_format(period,'%Y-%m') = :period ";
      $sql .= "        AND stocks.destination_name IN (".$dest_code.")  ";
      $sql .= "        AND stocks.flag != 0  ";
      $sql .= "        AND LayerGroup.flag = 1  ";
      // $sql .= "        AND stocks.account_code like '1%'  ";
   
      $sql .= "    GROUP BY stocks.destination_name,stocks.layer_code ";
      $sql .= "    ORDER BY destination_name";
      $sql .= "   )stocks  ";
      $sql .= "LEFT JOIN stock_busi_incharge_comments AS busi_inc_cmt ";
      $sql .= "  ON (busi_inc_cmt.stock_id = stocks.id AND busi_inc_cmt.flag=1) ";
      $sql .= "LEFT JOIN stock_busi_admin_comments AS busi_admin_cmt ";
      $sql .= "  ON (busi_admin_cmt.stock_id = stocks.id AND busi_admin_cmt.flag=1) ";
      $sql .= "LEFT JOIN stock_acc_incharge_comments AS acc_inc_cmt ";
      $sql .= "  ON (acc_inc_cmt.stock_id = stocks.id and acc_inc_cmt.flag=1) ";
      $sql .= "LEFT JOIN stock_acc_submanager_comments AS acc_submgr_cmt ";
      $sql .= "  ON (acc_submgr_cmt.stock_id = stocks.id AND acc_submgr_cmt.flag=1) ";
      $sql .= "    ORDER BY stocks.destination_name,stocks.layer_code,stocks.id";
  
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
      $sql .= " SELECT layer_code, ";
      $sql .= "       destination_name,sum(amount) as total_amt ";
      $sql .= "   FROM stocks ";
      $sql .= " WHERE ";
      if($ba_code != ''){
          $sql .= "       stocks.layer_code = :ba_code AND ";
          $param['ba_code'] = $ba_code;
      }
      $sql .= "     date_format(period,'%Y-%m') = :period ";
      $sql .= "    AND stocks.destination_name IN (".$dest_code.") ";
      $sql .= "    AND stocks.flag != 0 ";
      // $sql .= "    AND stocks.account_code like '1%' ";
      $sql .= "GROUP BY stocks.destination_name ";
      $sql .= "ORDER BY destination_name ";
  
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
        $sql .= "               SELECT stocks.layer_code,LayerGroup.name_jp, ";
        $sql .= "                         stocks.destination_name,numbers_day, ";
        $sql .= "                         sum(amount) yen_amt  ";
        $sql .= "                 FROM stocks ";
        $sql .= "                 JOIN layers AS LayerGroup ";
        $sql .= "                   ON LayerGroup.layer_code = stocks.layer_code ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       stocks.layer_code = :ba_code 
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND ";

            $param['ba_code'] = $ba_code;
            $param['periodYMD'] = $period.'-01';
        
        }
        $sql .= "                  date_format(period,'%Y-%m') = :period ";
        $sql .= "                  AND stocks.flag != 0 ";
        $sql .= "                  AND LayerGroup.flag = 1 ";
        // $sql .= "                  AND stocks.account_code like '1%' ";
        $sql .= "             AND stocks.numbers_day >= 30 ";
        $sql .= "             GROUP BY stocks.destination_name) AS tmp ";
        $sql .= "             WHERE tmp.yen_amt < 1000000 ";
        $sql .= "ORDER BY tmp.destination_name ";
        
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
        $sql .= "SELECT stocks.id,stocks.layer_code,stocks.name_jp, ";
        $sql .= "           stocks.destination_name,";
        $sql .= "           stocks.numbers_day,amount,";
        $sql .= "           busi_admin_cmt.comment AS busi_admin_comment ";
        $sql .= "           ,busi_inc_cmt.reason, ";
        $sql .= "           busi_inc_cmt.settlement_date,busi_inc_cmt.remark,";
        $sql .= "           acc_inc_cmt.comment AS acc_inc_comment,  ";
        $sql .= "           acc_submgr_cmt.comment AS acc_submanager_comment ";
        $sql .= "       FROM (	";
        $sql .= "	SELECT MIN(stocks.id) id,stocks.layer_code,LayerGroup.name_jp,";
        $sql .= "destination_name,";
        $sql .= "	sum(amount) amount,numbers_day";
        // $sql .= "	DATEDIFF(base_date,schedule_date) as NoOfDays";
        $sql .= "	FROM stocks LEFT JOIN layers AS LayerGroup  ";
        $sql .= "				  ON (LayerGroup.layer_code = stocks.layer_code)";
        $sql .= "	 WHERE  ";
        if($ba_code != ''){
        	$sql .= "       stocks.layer_code = :ba_code
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND ";

            $param['ba_code'] = $ba_code;
            $param['periodYMD'] = $period.'-01';
        }
        $sql .= "       date_format(period,'%Y-%m') = :period ";
        $sql .= "       AND stocks.destination_name IN (".$dest_code.")  ";
        $sql .= "       AND stocks.flag != 0  ";
        $sql .= "       AND LayerGroup.flag = 1  
                        AND LayerGroup.from_date <= :periodYMD 
                        AND LayerGroup.to_date >= :periodYMD "; 
        // $sql .= "       AND stocks.account_code like '1%'  ";
        $sql .= "	    AND numbers_day >= 30";
        $sql .= "    GROUP BY stocks.destination_name,stocks.layer_code ";
        $sql .= "    ORDER BY destination_name";
        $sql .= "   )stocks  ";
        $sql .= "LEFT JOIN stock_busi_incharge_comments AS busi_inc_cmt ";
        $sql .= "  ON (busi_inc_cmt.stock_id = stocks.id AND busi_inc_cmt.flag=1) ";
        $sql .= "LEFT JOIN stock_busi_admin_comments AS busi_admin_cmt ";
        $sql .= "  ON (busi_admin_cmt.stock_id = stocks.id AND busi_admin_cmt.flag=1) ";
        $sql .= "LEFT JOIN stock_acc_incharge_comments AS acc_inc_cmt ";
        $sql .= "  ON (acc_inc_cmt.stock_id = stocks.id and acc_inc_cmt.flag=1) ";
        $sql .= "LEFT JOIN stock_acc_submanager_comments AS acc_submgr_cmt ";
        $sql .= "  ON (acc_submgr_cmt.stock_id = stocks.id AND acc_submgr_cmt.flag=1) ";
        $sql .= "    ORDER BY stocks.destination_name,stocks.layer_code,stocks.id ";
        
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
        $sql .= " SELECT destination_name, ";
        $sql .= "       sum(amount) as total_amt ";
        $sql .= "   FROM stocks ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       stocks.layer_code = :ba_code AND ";
            $param['ba_code'] = $ba_code;
        }
        $sql .= "    date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND stocks.destination_name IN (".$dest_code.") ";
        $sql .= "    AND stocks.flag != 0 ";
        // $sql .= "    AND stocks.account_code like '1%' ";
        $sql .= "    AND numbers_day >= 30 ";
        $sql .= "GROUP BY destination_name ";
        $sql .= "ORDER BY destination_name ";
       
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
        $sql .= "               SELECT stocks.layer_code,name_jp, ";
        $sql .= "                         destination_name,numbers_day, ";
        $sql .= "                         sum(amount) yen_amt  ";
        $sql .= "                 FROM stocks ";
        $sql .= "                 JOIN layers AS LayerGroup ";
        $sql .= "                   ON LayerGroup.layer_code = stocks.layer_code ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       stocks.layer_code = :ba_code  
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND ";

            $param['ba_code'] = $ba_code;
            $param['periodYMD'] = $period.'-01';
        }
        $sql .= "                  date_format(period,'%Y-%m') = :period ";
        $sql .= "                  AND stocks.flag != 0 ";
        $sql .= "                  AND LayerGroup.flag = 1 ";
        // $sql .= "                  AND stocks.account_code like '1%' ";
        $sql .= "                  AND numbers_day < 30";
        $sql .= "             GROUP BY stocks.destination_name) AS tmp ";
        $sql .= "           WHERE tmp.yen_amt < 1000000 ";
        $sql .= "ORDER BY tmp.destination_name ";
        
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
        $sql .= " SELECT stocks.layer_code,name_jp, ";
        $sql .= "       destination_name,numbers_day, ";
        $sql .= "       sum(amount) AS jp_amount";
        $sql .= "   FROM stocks ";
        $sql .= "   JOIN layers AS LayerGroup ";
        $sql .= "     ON LayerGroup.layer_code = stocks.layer_code ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       stocks.layer_code = :ba_code  
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD AND ";
            $param['ba_code'] = $ba_code;
            $param['periodYMD'] = $period.'-01';
           
        }
        $sql .= "    date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND stocks.destination_name IN (".$dest_code.") ";
        $sql .= "    AND stocks.flag != 0 ";
        $sql .= "    AND LayerGroup.flag = 1 ";
        // $sql .= "    AND stocks.account_code like '1%' ";
        $sql .= "    AND numbers_day < 30 ";
        $sql .= "GROUP BY stocks.destination_name,stocks.layer_code ";
        $sql .= "ORDER BY destination_name ";
    
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
        $sql .= " SELECT destination_name, ";
        $sql .= "       sum(amount) as total_amt ";
        $sql .= "   FROM stocks ";
        $sql .= " WHERE ";
        if($ba_code != ''){
            $sql .= "       stocks.layer_code = :ba_code AND ";
            $param['ba_code'] = $ba_code;
        }
        $sql .= "    date_format(period,'%Y-%m') = :period ";
        $sql .= "    AND stocks.destination_name IN (".$dest_code.") ";
        $sql .= "    AND stocks.flag != 0 ";
        // $sql .= "    AND stocks.account_code like '1%' ";
        $sql .= "    AND numbers_day < 30 ";
        $sql .= "GROUP BY destination_name ";
        $sql .= "ORDER BY destination_name ";
    
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
        $sql .= "                      (SELECT receipt_index_no,sum(stock.amount) as amount ";
        $sql .= "                  FROM stocks AS stock  ";
        $sql .= "                  LEFT JOIN layers as LayerGroup   ";
        $sql .= "                    ON LayerGroup.layer_code = stock.layer_code ";
        $sql .= "                  WHERE";
        if($ba_code != ''){
        	$sql .= "         stock.layer_code = :ba_code 
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD";
        	$param['ba_code'] = $ba_code;
        }
      //   $sql .= "        stock.account_code LIKE '1%'  ";
        $sql .= "                     AND date_format(stock.registration_date,'%Y-%m-%d') <= :prev_month_2";
        $sql .= "                    AND LayerGroup.flag = 1  AND stock.flag != 0 AND stock.period = :periodYMD and stock.numbers_day  BETWEEN 30 AND 60  ";
        $sql .= "                 GROUP BY stock.receipt_index_no) tmp  ";
        $sql .= "                   JOIN  ";
        $sql .= "               ( SELECT stock.registration_date,stock.id,LayerGroup.name_jp,stock.destination_name,stock.receipt_index_no, ";
        $sql .= "                      stock.item_code,busi_inc_cmt.reason,busi_inc_cmt.settlement_date,   ";
        $sql .= "        IF(busi_inc_cmt.flag  IS NULL,1,busi_inc_cmt.flag) AS busi_inc_cmt_flag , ";
      //   $sql .= "                      stock.receipt_pay_date,DATE_FORMAT(stock.schedule_date,'%Y-%m-%d') AS schedule_date,stock.numbers_day  ";
        $sql .= "                         stock.numbers_day  ";
        $sql .= "        FROM stocks AS stock  ";
        $sql .= "                       LEFT JOIN stock_busi_incharge_comments AS busi_inc_cmt  ";
        $sql .= "                    ON (stock.id = busi_inc_cmt.stock_id    and busi_inc_cmt.flag =1)";
        $sql .= "                  LEFT JOIN layers as LayerGroup   ";
        $sql .= "                    ON LayerGroup.layer_code = stock.layer_code   ";
        $sql .= "                  WHERE";
        if($ba_code != ''){
        	$sql .= "       stock.layer_code = :ba_code  
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD";
        	$param['ba_code'] = $ba_code;
        }
      //   $sql .= "        stock.account_code LIKE '1%'  ";
        $sql .= "                     AND date_format(stock.registration_date,'%Y-%m-%d') <= :prev_month_2";
        $sql .= "                    and stock.flag != 0 and LayerGroup.flag = 1 AND period = :periodYMD AND stock.numbers_day BETWEEN 30 AND 60  ) tbl_2  ";
        $sql .= "                     ON tbl_2.receipt_index_no = tmp.receipt_index_no  ";
        $sql .= "                     ORDER BY tbl_2.registration_date desc,tbl_2.receipt_index_no,tbl_2.id ";
    
        
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
        $sql .= "                      (SELECT receipt_index_no,sum(stock.amount) as amount ";
        $sql .= "                  FROM stocks AS stock  ";
        $sql .= "                  LEFT JOIN layers as LayerGroup ";
        $sql .= "                    ON LayerGroup.layer_code = stock.layer_code ";
        $sql .= "                  WHERE";
    	if($ba_code != ''){
            $sql .= "       stock.layer_code = :ba_code 
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD";
            $param['ba_code'] = $ba_code;
        }
      //   $sql .= "        stock.account_code LIKE '1%'  ";
        $sql .= "                     AND date_format(stock.registration_date,'%Y-%m') <= :prev_month_2";
        $sql .= "                    AND LayerGroup.flag = 1  AND stock.flag != 0  AND stock.period = :periodYMD and stock.numbers_day  > 60  ";
        $sql .= "                 GROUP BY stock.receipt_index_no) tmp  ";
        $sql .= "                   JOIN  ";
        $sql .= "               ( SELECT stock.registration_date,stock.id,LayerGroup.name_jp,stock.destination_name,stock.receipt_index_no, ";
        $sql .= "                      stock.item_code,busi_inc_cmt.reason,busi_inc_cmt.settlement_date,   ";
        $sql .= "        IF(busi_inc_cmt.flag  IS NULL,1,busi_inc_cmt.flag) AS busi_inc_cmt_flag , ";
      //   $sql .= "                      stock.receipt_pay_date,DATE_FORMAT(stock.schedule_date,'%Y-%m-%d') AS schedule_date,stock.numbers_day  ";
        $sql .= "                         stock.numbers_day  ";
        $sql .= "        FROM stocks AS stock  ";
        $sql .= "                       LEFT JOIN stock_busi_incharge_comments AS busi_inc_cmt  ";
        $sql .= "                    ON (stock.id = busi_inc_cmt.stock_id    and busi_inc_cmt.flag =1)";
        $sql .= "                  LEFT JOIN layers as LayerGroup   ";
        $sql .= "                    ON LayerGroup.layer_code = stock.layer_code   ";
        $sql .= "                  WHERE";
    	if($ba_code != ''){
            $sql .= "       stock.layer_code = :ba_code 
                            AND LayerGroup.from_date <= :periodYMD 
                            AND LayerGroup.to_date >= :periodYMD";
            $param['ba_code'] = $ba_code;
        }
      //   $sql .= "        stock.account_code LIKE '1%'  ";
        $sql .= "                     AND date_format(stock.registration_date,'%Y-%m') <= :prev_month_2";
        $sql .= "                    and stock.flag != 0 and LayerGroup.flag = 1 AND period = :periodYMD AND stock.numbers_day > 60  ) tbl_2  ";
        $sql .= "                     ON tbl_2.receipt_index_no = tmp.receipt_index_no  ";
        $sql .= "                     ORDER BY tbl_2.registration_date desc,tbl_2.receipt_index_no,tbl_2.id ";
    
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
        $sql .= "   SELECT *,sum(amount) as amount from stocks as account_review_excel";

        $sql .= " LEFT JOIN stock_busi_incharge_comments on account_review_excel.id = stock_busi_incharge_comments.stock_id AND stock_busi_incharge_comments.flag=1";

        $sql .= " LEFT JOIN stock_busi_admin_comments on account_review_excel.id = stock_busi_admin_comments.stock_id AND stock_busi_admin_comments.flag=1";

        $sql .= " LEFT JOIN stock_acc_incharge_comments on account_review_excel.id = stock_acc_incharge_comments.stock_id AND stock_acc_incharge_comments.flag=1";

        $sql .= " LEFT JOIN stock_acc_submanager_comments on account_review_excel.id = stock_acc_submanager_comments.stock_id AND stock_acc_submanager_comments.flag=1";

        $sql .= " WHERE date_format(account_review_excel.period,'%Y-%m') = :period";
        $sql .= " AND account_review_excel.flag != 0";
        if($ba_code != ''){
            $sql .= "  AND account_review_excel.layer_code = :ba_code ";
            $param['ba_code'] = $ba_code;
        }
        // $sql .= " GROUP BY layer_code, account_code, destination_code, logistic_index_no, posting_date, recorded_date, schedule_date";
        $sql .= " GROUP BY layer_code, destination_name";
        // $sql .= " ORDER BY account_review_excel.id,layer_code, account_code, destination_code, logistic_index_no, posting_date, recorded_date, schedule_date ";
        $sql .= " ORDER BY account_review_excel.id,layer_code, destination_name";
        
        $param['period'] = $period;
        $data = $this->query($sql,$param);
        for ($i=0; $i < count($data); $i++) { 
            $data[$i]['account_review_excel']['reason'] = $data[$i]['stock_busi_incharge_comments']['reason'];
            $data[$i]['account_review_excel']['settlement_date'] = $data[$i]['stock_busi_incharge_comments']['settlement_date'];
            $data[$i]['account_review_excel']['remark'] = $data[$i]['stock_busi_incharge_comments']['remark'];
            $data[$i]['account_review_excel']['business_admin_comment'] = $data[$i]['stock_busi_admin_comments']['comment'];
            $data[$i]['account_review_excel']['acc_incharge_comment'] = $data[$i]['stock_acc_incharge_comments']['comment'];
            $data[$i]['account_review_excel']['acc_submgr_comment'] = $data[$i]['stock_acc_submanager_comments']['comment'];
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
    public function Add_Comment_Excel($ba_code,$period,$logistics) {
       
        $param = array();
        $sql  = "";
        $sql .= "   SELECT *,sum(amount) as amount from stocks as account_review_excel";

        $sql .= " LEFT JOIN stock_busi_incharge_comments on account_review_excel.id = stock_busi_incharge_comments.stock_id AND stock_busi_incharge_comments.flag=1";

        $sql .= " LEFT JOIN stock_busi_admin_comments on account_review_excel.id = stock_busi_admin_comments.stock_id AND stock_busi_admin_comments.flag=1";

        $sql .= " LEFT JOIN stock_acc_incharge_comments on account_review_excel.id = stock_acc_incharge_comments.stock_id AND stock_acc_incharge_comments.flag=1";

        // $sql .= " JOIN stock_acc_submanager_comments on account_review_excel.id = stock_acc_submanager_comments.stock_id AND stock_acc_submanager_comments.flag=1";
        $sql .= " WHERE date_format(account_review_excel.period,'%Y-%m') = :period";
        $sql .= " AND account_review_excel.flag != 0";
        if($ba_code != ''){
            $sql .= "  AND account_review_excel.layer_code = :ba_code ";
            $param['ba_code'] = $ba_code;
        }
        // if($searchRepre != ''){
        //     $sql .= " AND account_review_excel.sale_representative LIKE  :searchRepre ";
        //     $param['searchRepre'] = '%'.$searchRepre.'%';
        // }
        if($logistics != ''){
            $sql .= " AND account_review_excel.receipt_index_no LIKE  :logistics ";
            $param['logistics'] = '%'.$logistics.'%';
        }
        $sql .= " GROUP BY layer_code, destination_name,period";
        $sql .= " ORDER BY account_review_excel.id,layer_code, destination_name, period";
        
        $param['period'] = $period;
        
        $data = $this->query($sql,$param);
        for ($i=0; $i < count($data); $i++) { 
            $data[$i]['account_review_excel']['ba_inc_reason'] = $data[$i]['stock_busi_incharge_comments']['reason'];
            $data[$i]['account_review_excel']['settlement_date'] = $data[$i]['stock_busi_incharge_comments']['settlement_date'];
            $data[$i]['account_review_excel']['remark'] = $data[$i]['stock_busi_incharge_comments']['remark'];
            $data[$i]['account_review_excel']['business_admin_comment'] = $data[$i]['stock_busi_admin_comments']['comment'];
            $data[$i]['account_review_excel']['acc_incharge_comment'] = $data[$i]['stock_acc_incharge_comments']['comment'];
            // $data[$i]['account_review_excel']['acc_submgr_comment'] = $data[$i]['stock_acc_submanager_comments']['comment'];
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
                      stock_data.id, 
                      stock_data.layer_code, 
                      MAX(stock_data.flag) as max_flag, 
                      MIN(stock_data.flag) as min_flag, 
                      stock_data.name_jp, 
                      stock_data.parent_id 
                    FROM 
                      (
                        SELECT 
                          stock.id, 
                          stock.layer_code, 
                          stock.flag, 
                          layerGroup.name_jp, 
                          layerGroup.parent_id 
                        FROM 
						stocks as stock 
                          LEFT JOIN layers as layerGroup ON (stock.layer_code = layerGroup.layer_code) 
                        WHERE 
                          date_format(stock.period, "%Y-%m")= :period 
                          AND stock.flag > 1 
                          AND layerGroup.flag = 1 
                          AND layerGroup.from_date <= :period_date 
                          AND layerGroup.to_date >= :period_date
                      ) as stock_data 
                    GROUP BY 
                      stock_data.layer_code 
                    UNION 
                    SELECT 
                      stock_data.id, 
                      stock_data.layer_code, 
                      MAX(stock_data.flag) as max_flag, 
                      MIN(stock_data.flag) as min_flag, 
                      stock_data.name_jp, 
                      stock_data.parent_id 
                    FROM 
                      (
                        SELECT 
                          stock.id, 
                          stock.layer_code, 
                          stock.flag, 
                          layerGroup.name_jp, 
                          layerGroup.parent_id 
                        FROM 
						stocks as stock 
                          LEFT JOIN layers as layerGroup ON (stock.layer_code = layerGroup.layer_code) 
                        WHERE 
                          date_format(stock.period, "%Y-%m")= :period 
                          AND stock.flag > 0 
                          AND layerGroup.flag = 1 
                          AND layerGroup.from_date <= :period_date 
                          AND layerGroup.to_date >= :period_date
                      ) as stock_data 
                    GROUP BY 
                      stock_data.layer_code
                  ) as tmp 
                  LEFT JOIN stock_busi_manager_approves as busi_approve ON (
                    tmp.id = busi_approve.stock_id 
                    and busi_approve.flag = 1
                  ) 
                  LEFT JOIN stock_acc_manager_approves as acc_approve ON (
                    tmp.id = acc_approve.stock_id 
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
		// $sql .= "	SUM(IF(flag=1, 1, 0)) AS complete_count ";
		$sql .= "FROM stocks ";
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
    public function getMatchFlag($stockID){

        // die($stockID);
        $param = array();
        $sql = "";
        $sql.= " SELECT
                       id 
                    FROM
					stocks 
                    WHERE
                    period = 
                       (
                          SELECT period FROM stocks WHERE id = :stockID
                       )
                       AND layer_code = 
                       (
                          SELECT TRIM(layer_code) FROM stocks WHERE id = :stockID
                       )
                    --    AND account_code = 
                    --    (
                    --       SELECT TRIM(account_code) FROM stocks WHERE id = :stockID
                    --    )
                       AND destination_name = 
                       (
                          SELECT TRIM(destination_name) FROM stocks WHERE id = :stockID
                       )
                    --    AND logistic_index_no = 
                    --    (
                    --       SELECT TRIM(logistic_index_no) FROM stocks WHERE id = :stockID
                    --    )
                    --    AND posting_date = 
                    --    (
                    --       SELECT TRIM(posting_date) FROM stocks WHERE id = :stockID
                    --    )
                    --    AND recorded_date = 
                    --    (
                    --       SELECT TRIM(recorded_date) FROM stocks WHERE id = :stockID
                    --    )
                    --    AND schedule_date = 
                    --    (
                    --       SELECT TRIM(schedule_date) FROM stocks WHERE id = :stockID
                    --     )
                        AND flag = 
                        (
                            SELECT stocks.flag FROM stocks WHERE id = :stockID
                        ) ";


        $param['stockID'] = $stockID;
        $data = $this->query($sql,$param);
       
        return $data;
                       
    }

    /** 
     * @author Nu Nu Lwin
     * @date 15.07.2019
     * for Account Preview 
     * add currency
    **/
    public function getMatchFlagPreview($stockID){

        $param = array();
        $sql = "";
        $sql.= " SELECT
                       id 
                    FROM
					stocks 
                    WHERE
                       period = 
                       (
                          SELECT period FROM stocks WHERE id = :stockID
                       )
                       AND layer_code = 
                       (
                          SELECT TRIM(layer_code) FROM stocks WHERE id = :stockID
                       )
                       AND destination_name = 
                       (
                          SELECT TRIM(destination_name) FROM stocks WHERE id = :stockID
                       )
                      --  AND item_code = 
                      --  (
                      --     SELECT TRIM(item_code) FROM stocks WHERE id = :stockID
                      --  )
                      --  AND receipt_index_no = 
                      --  (
                      --     SELECT TRIM(receipt_index_no) FROM stocks WHERE id = :stockID
                      --  )
                      --  AND registration_date = 
                      --  (
                      --     SELECT TRIM(registration_date) FROM stocks WHERE id = :stockID
                      --  )
                      --  AND deadline_date = 
                      --  (
                      --     SELECT TRIM(deadline_date) FROM stocks WHERE id = :stockID
                      --   )
                       AND flag = 
                       (
                            SELECT flag FROM stocks WHERE id = :stockID
                       )";


        $param['stockID'] = $stockID;
        $data = $this->query($sql,$param);
        
        return $data;
    }

}
