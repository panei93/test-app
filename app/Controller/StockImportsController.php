<?php

App::uses('Controller', 'Controller');
App::import('Vendor', 'php-excel-reader/PHPExcel');
App::import('Controller', 'Common');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */

define('UPLOAD_FILEPATH', ROOT); //server
define('UPLOAD_PATH', 'app'.DS.'temp');


class StockImportsController extends AppController
{
    public $components = array('PhpExcel.PhpExcel','Flash');
    public $uses = array('Stock','Layer');

    public function beforeFilter()
    {
        parent::checkUserStatus();
        parent::CheckSession();
        parent::checkAccessType();
        parent::checkStockUrlSession('import');

        $Common = New CommonController();

        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        // $layers = array_keys($permissions['index']['layers']);

        // if((!in_array($layer_code, $layers)) || ($layer_code=="" && $permissions['index']['limit']>0)) {
        //     $errorMsg = parent::getErrorMsg('SE065');
        //     $this->Flash->set($errorMsg, array("key"=>"Error"));
        //     $this->redirect(array('controller'=>'StockSelections', 'action'=>'index'));
        // }
        
        
    }
    
    public function index()
    {
        $Common = new CommonController();
        $this->layout = 'stocks';
        $errorMsg   = "";
        $successMsg = "";
        $showSaveBtn = false;

        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');

        $data['role_id'] = $role_id;
        $data['period']         = $period;
        $data['layer_code']     = $layer_code;
        $data['page']           = 'StockImports';
        $data['flag_list']      = '';
        $data['modelName']      = 'Stock';
        
        $permissions = $this->Session->read('PERMISSIONS');
        $save_permt = $permissions['save'];
        $save_layer = array_keys($save_permt['layers']);
        if (($layer_code == '' && $save_permt['limit']==0) || in_array($layer_code, $save_layer)) {
            $showSaveBtn = true;
        }

        if (!empty($period)) {
            $BAName = "";
            if ($layer_code != null || $layer_code != '') {
                
                $getBAName = $Common->getLayerThreeName($layer_code,'StockSelections',date("Y-m-d", strtotime($period)));
                $BAName = $getBAName['name_jp'];
            }

            if ($this->Session->check('SkipCheckBAcode')) {
                $this->Session->delete('SkipCheckBAcode');
                $this->set('SkipCheckBAcode', 'SkipCheckBAcode');
            }

            if ($this->Session->check('SkipAccSlicLine')) {
                $this->Session->delete('SkipAccSlicLine');
                $this->set('SkipAccSlicLine', 'SkipAccSlicLine');
            }
            $this->set('showSaveBtn', $showSaveBtn);
            $this->set('target_month', $period);
            $this->set('PERIOD', $period);
            $this->set('BA_Code', $layer_code);
            $this->set('BAName', $this->Session->read('StockSelections_BA_NAME'));
            $this->set('successMsg', $successMsg);
            $this->set('errorMsg', $errorMsg);
            $this->render('index');
        } else {
            $this->redirect(array('controller' => 'StockSelections', 'action' => 'index'));
        }
    }

    public function SaveExcelFile()
    {
        $Common = new CommonController();

        if ($this->request->is('post')) {
            //Global Variable for error message
            $ERROR_VALUE = "";
            $errorMsg = "";
            $successMsg = "";
            $role_id = $this->Session->read('ADMIN_LEVEL_ID');
            $user_id = $this->Session->read('LOGIN_ID'); //get login id
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $login_id = $this->Session->read('LOGIN_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $layer_name = $this->Session->read('StockSelections_BA_NAME');
            $period  = $this->Session->read('StockSelections_PERIOD_DATE');
            $period  = date('Y-m-d', strtotime($period));

            $base_date = $this->request->data['refer_date'];
            $deadLine_date = $this->request->data['submission_date'];
            
            $BAName = "";

            $isBA_Code = true;
            /** can import data, if already has layer_code in business_area_tbl **/
            if ($layer_code != null || $layer_code != '') {
               
                $getBAName = $Common->getLayerThreeName($layer_code,$period);
                $BAName = $getBAName['name_jp'];
                $isBA_Code = true;
            } else {
                $isBA_Code = false;
            }
            
            $file = $this->request->params['form']['uploadfile'];
    
            $uploadPath = APP . 'tmp'; // file path
            
            $data = array();
            $dataError = array();
            $SkipCheckBAcode = array();
            $SkipAccSlicLine = array();
            $header = "FALSE";

            if (!empty($file)) {
                if ($file['error'] == 0) {
                    if ($file['size'] <= 10485760) { //10 Megabytes (MB)

                        $file_name	= $file['name'];
                        $file_type 	= $file['type'];
                        $file_loc 	= $file['tmp_name'];
                        $file_size 	= $file['size'];

                        $file_type =  mb_convert_encoding($file_type, "SJIS", "UTF-8");
                        $file_loc  =  mb_convert_encoding($file_loc, "SJIS", "UTF-8");
                        $file_size =  mb_convert_encoding($file_size, "SJIS", "UTF-8");

                        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                        $fileName = "temporary.".$ext;
                        $tempPath = $uploadPath .DS. $fileName;

                        /** save under tmp/ **/
                        if (move_uploaded_file($file_loc, $tempPath)) {
                            if ($ext =="xlsx" || $ext == "xls") {
                                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                                $objReader->setReadDataOnly(true);
                                
                                if ($objReader->canRead($tempPath)) {
                                    $objPHPExcel   = $objReader->load($tempPath);
                                    $objWorksheet  = $objPHPExcel->getActiveSheet();
                                    $highestRow    = $objWorksheet->getHighestRow();
                                    $highestColumn = $objWorksheet->getHighestColumn();

                                    if ($highestRow == 1) {
                                        $highestRow = $highestRow +1;
                                    }

                                    for ($row = 1; $row <= $highestRow; $row++) {
                                        $rowData = $objWorksheet->rangeToArray(
                                            'F' . $row . ':' . 'CG' . $row,
                                            null,
                                            true,
                                            false
                                        );
                                        $worksheets[] = $rowData;
                                    }
                                   
                                    for ($j=0; $j<14; $j++) {
                                        if (!empty($worksheets[3][0][$j])) {
                                            if (ltrim($worksheets[3][0][0]) == '部署'
                                                && ltrim($worksheets[3][0][1]) == '保管場所'
                                                && ltrim($worksheets[3][0][2]) == '品目コード'
                                                && ltrim($worksheets[3][0][3]) == '品目テキスト'
                                                && ltrim($worksheets[3][0][4]) == '品目名2'
                                                && ltrim($worksheets[3][0][5]) == '単位'
                                                && ltrim($worksheets[3][0][6]) == '入庫日'
                                                && ltrim($worksheets[3][0][7]) == '滞留日数'
                                                && ltrim($worksheets[3][0][8]) == '入庫Index№'
                                                && ltrim($worksheets[3][0][9]) == '数量'
                                                && ltrim($worksheets[3][0][10]) == '金額'
                                                && ltrim($worksheets[3][0][11]) == '不完全品 有・無'
                                                && ltrim($worksheets[3][0][12]) == '売り繋ぎ済・未済'
                                                && ltrim($worksheets[3][0][13]) == '契約 有・無') {
                                                $header = "true";
                                            } else {
                                                if (@ file_get_contents($tempPath)) {
                                                    unlink($tempPath);
                                                }
                                                $header = "FALSE";
                                                $errorMsg = parent::getErrorMsg('SE022');
                                                $this->Flash->set($errorMsg, array('key'=>'excelError'));
                                                $this->redirect(array('controller'=>'StockImports','action'=>'index'));
                                            }
                                        } else {
                                            if (@ file_get_contents($tempPath)) {
                                                unlink($tempPath);
                                            }
                                            $errorMsg = parent::getErrorMsg('SE021');
                                            $this->Flash->set($errorMsg, array('key'=>'excelError'));
                                            $this->redirect(array('controller'=>'StockImports','action'=>'index'));
                                        }
                                    }
                                    
                                    if ($header == "true") {
                                        $i = 0;
                                        $data = array();
                                        for ($row = 6; $row <= $highestRow; ++$row) {

                                            /** if account_slip_no, $account_statement_no and layer_code are empty show error msg **/
                                            // $account_slip_no = $objWorksheet->getCellByColumnAndRow(73, $row)->getValue();
                                            // $account_statement_no = $objWorksheet->getCellByColumnAndRow(74, $row)->getValue();
                                            $layer_code = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();

                                            // $acc_slip_no = $this->validateInterger($account_slip_no, 'BV', $row);
                                            // if ($acc_slip_no == "true") {
                                            //     $acc_slip_no_Length = mb_strlen(trim($account_slip_no));

                                            //     if ($acc_slip_no_Length <= '11') {
                                            //         $acc_slip_no = "true";
                                            //     } else {
                                            //         $acc_slip_no = "false";

                                            //         CakeLog::write('debug', ' Invalid acc_slip_no format error occur.  '.$account_slip_no.' will be interger length <= 11, at col BV and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //         global $ERROR_VALUE;
                                            //         $ERROR_VALUE = "BV";
                                            //     }
                                            // }

                                            // $acc_state_no = $this->validateInterger($account_statement_no, 'BW', $row);
                                            // if ($acc_state_no == "true") {
                                            //     $acc_state_no_Length = mb_strlen(trim($account_statement_no));

                                            //     if ($acc_state_no_Length <= '11') {
                                            //         $acc_state_no = "true";
                                            //     } else {
                                            //         $acc_state_no = "false";

                                            //         CakeLog::write('debug', ' Invalid account_statement_no format error occur.  '.$account_statement_no.' will be interger length <= 11, at col BW and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //         global $ERROR_VALUE;
                                            //         $ERROR_VALUE = "BW";
                                            //     }
                                            // }

                                            // if ($acc_slip_no == "false" || $acc_state_no == "false") {
                                            //     if (@ file_get_contents($tempPath)) {
                                            //         unlink($tempPath);
                                            //     }
                                            //     $errorMsg = parent::getErrorMsg('SE025', array($row,__("BVまたはBW")));
                                                
                                            //     $this->Flash->set($errorMsg, array('key'=>'noSlipState'));
                                            //     $this->redirect(array('controller'=>'StockImports','action'=>'index'));
                                            //     break;
                                            // }

                                            if (empty($layer_code)) {
                                                $errorMsg = parent::getErrorMsg('SE024');
                                                $this->Flash->set($errorMsg, array('key'=>'noSlipState'));
                                                $this->redirect(array('controller'=>'StockImports','action'=>'index'));
                                                break;
                                            }
                                           
                                            //end

                                            // $legacy_clearing = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();
                                            $destination_name  = $objWorksheet->getCellByColumnAndRow(6, $row)->getValue();

                                            if (empty($destination_name)) {
                                                $errorMsg = parent::getErrorMsg('SE024');
                                                $this->Flash->set($errorMsg, array('key'=>'noSlipState'));
                                                $this->redirect(array('controller'=>'StockImports','action'=>'index'));
                                                break;
                                            }

                                            $item_code = $objWorksheet->getCellByColumnAndRow(7, $row)->getValue();
                                            
                                            $item_name = $objWorksheet->getCellByColumnAndRow(8, $row)->getValue();

                                            $item_name_2 = $objWorksheet->getCellByColumnAndRow(9, $row)->getValue();

                                            $unit = $objWorksheet->getCellByColumnAndRow(10, $row)->getValue();

                                            $registration_date = $objWorksheet->getCellByColumnAndRow(11, $row)->getValue();

                                            $registration_date = \PHPExcel_Style_NumberFormat::toFormattedString($registration_date, 'DD-MM-YYYY');

                                            $numbers_day  = $objWorksheet->getCellByColumnAndRow(12, $row)->getValue();
                                            
                                            $receipt_index_no = $objWorksheet->getCellByColumnAndRow(13, $row)->getValue();

                                            $quantity = $objWorksheet->getCellByColumnAndRow(14, $row)->getValue();
                                                                                        
                                            $amount = $objWorksheet->getCellByColumnAndRow(15, $row)->getValue();

                                            $is_error = $objWorksheet->getCellByColumnAndRow(16, $row)->getValue();

                                            $is_sold = $objWorksheet->getCellByColumnAndRow(17, $row)->getValue();

                                            $is_contract = $objWorksheet->getCellByColumnAndRow(18, $row)->getValue();
                                            
                                            // $reason = $objWorksheet->getCellByColumnAndRow(19, $row)->getValue();

                                            // $solution = $objWorksheet->getCellByColumnAndRow(20, $row)->getValue();

                                            // $reference_number = $objWorksheet->getCellByColumnAndRow(22, $row)->getValue();
                                            // $pm = $objWorksheet->getCellByColumnAndRow(23, $row)->getValue();

                                            // $commencement_date = $objWorksheet->getCellByColumnAndRow(24, $row)->getValue();

                                            // $commencement_date = \PHPExcel_Style_NumberFormat::toFormattedString($commencement_date, 'DD-MM-YY');
                                            
                                            // $maturity_date = $objWorksheet->getCellByColumnAndRow(25, $row)->getValue();

                                            // $maturity_date = \PHPExcel_Style_NumberFormat::toFormattedString($maturity_date, 'DD-MM-YYYY');
                                        
                                            // $receipt_pay_date = $objWorksheet->getCellByColumnAndRow(26, $row)->getValue();

                                            // $receipt_pay_date = \PHPExcel_Style_NumberFormat::toFormattedString($receipt_pay_date, 'DD-MM-YYYY');

                                            // $cash_receipt_pay_desti_cd 	= $objWorksheet->getCellByColumnAndRow(27, $row)->getValue();
                                            // $inspection_category 	= $objWorksheet->getCellByColumnAndRow(28, $row)->getValue();
                                            // $parent_index_no 		= $objWorksheet->getCellByColumnAndRow(29, $row)->getValue();
                                            // $contract_no 			= $objWorksheet->getCellByColumnAndRow(30, $row)->getValue();
                                            // $transaction_search_key = $objWorksheet->getCellByColumnAndRow(31, $row)->getValue();
                                            // $line_item_text 			= $objWorksheet->getCellByColumnAndRow(32, $row)->getValue();
                                            // $invoice_management 	= $objWorksheet->getCellByColumnAndRow(33, $row)->getValue();
                                            // $claim_receive_flg 		= $objWorksheet->getCellByColumnAndRow(34, $row)->getValue();
                                            // $transaction_type 		= $objWorksheet->getCellByColumnAndRow(35, $row)->getValue();
                                            // $sale_representative 	= $objWorksheet->getCellByColumnAndRow(36, $row)->getValue();
                                            // $docu_no_row 			= $objWorksheet->getCellByColumnAndRow(37, $row)->getValue();
                                            // $in_out_date 			= $objWorksheet->getCellByColumnAndRow(38, $row)->getValue();

                                            // $in_out_date 			= \PHPExcel_Style_NumberFormat::toFormattedString($in_out_date, 'DD-MM-YYYY');
                                            
                                            // $counterparty_cd        = $objWorksheet->getCellByColumnAndRow(39, $row)->getValue();
                                            // $item_code 				= $objWorksheet->getCellByColumnAndRow(40, $row)->getValue();
                                            // $item_name 				= $objWorksheet->getCellByColumnAndRow(41, $row)->getValue();
                                            // $item_name_2 			= $objWorksheet->getCellByColumnAndRow(42, $row)->getValue();
                                            // $standard_grade         = $objWorksheet->getCellByColumnAndRow(43, $row)->getValue();
                                            // $consignment_ship_cd 	= $objWorksheet->getCellByColumnAndRow(44, $row)->getValue();
                                            // $goods_receipt_issue_no = $objWorksheet->getCellByColumnAndRow(45, $row)->getValue();
                                            // $sale_date              = $objWorksheet->getCellByColumnAndRow(46, $row)->getValue();
                                            
                                            // $sale_purchase_no       = $objWorksheet->getCellByColumnAndRow(47, $row)->getValue();
                                            // $ref_item_no 			= $objWorksheet->getCellByColumnAndRow(48, $row)->getValue();
                                            // $unit 					= $objWorksheet->getCellByColumnAndRow(49, $row)->getValue();
                                            // $quantity 				= $objWorksheet->getCellByColumnAndRow(50, $row)->getValue();
                                            // $unit_price 			= $objWorksheet->getCellByColumnAndRow(51, $row)->getValue();
                                            // $transaction_system     = $objWorksheet->getCellByColumnAndRow(52, $row)->getValue();
                                            // $slip_ymd_no 			= $objWorksheet->getCellByColumnAndRow(53, $row)->getValue();
                                            // $supplementary_qty 	    = $objWorksheet->getCellByColumnAndRow(54, $row)->getValue();
                                            // $oversea_store_r_no     = $objWorksheet->getCellByColumnAndRow(55, $row)->getValue();
                                            // $lot_no                 = $objWorksheet->getCellByColumnAndRow(56, $row)->getValue();
                                            // $payee                  = $objWorksheet->getCellByColumnAndRow(57, $row)->getValue();
                                            // $opponent_subject       = $objWorksheet->getCellByColumnAndRow(58, $row)->getValue();
                                            // $division_ratio         = $objWorksheet->getCellByColumnAndRow(59, $row)->getValue();
                                            // $category_branch_no     = $objWorksheet->getCellByColumnAndRow(60, $row)->getValue();
                                            // $bl_date  	            = $objWorksheet->getCellByColumnAndRow(61, $row)->getValue();

                                            // $bl_date = \PHPExcel_Style_NumberFormat::toFormattedString($bl_date, 'DD-MM-YYYY');
                                            
                                            // $borrower_code	= $objWorksheet->getCellByColumnAndRow(62, $row)->getValue();
                                            // $shipper_code 	= $objWorksheet->getCellByColumnAndRow(63, $row)->getValue();
                                            // $company_nominal_district  = $objWorksheet->getCellByColumnAndRow(64, $row)->getValue();
                                            // $product_sales_destination = $objWorksheet->getCellByColumnAndRow(65, $row)->getValue();
                                            // $product_supplier = $objWorksheet->getCellByColumnAndRow(66, $row)->getValue();
                                            // $country_code 		= $objWorksheet->getCellByColumnAndRow(67, $row)->getValue();
                                            // $country_origin_code = $objWorksheet->getCellByColumnAndRow(68, $row)->getValue();
                                            // $ship_name 	 = $objWorksheet->getCellByColumnAndRow(69, $row)->getValue();
                                            // $harbor_name = $objWorksheet->getCellByColumnAndRow(70, $row)->getValue();
                                            // $port_name 	 = $objWorksheet->getCellByColumnAndRow(71, $row)->getValue();
                                            // $year 			 = $objWorksheet->getCellByColumnAndRow(72, $row)->getValue();
                                            // $account_slip_no 	= $objWorksheet->getCellByColumnAndRow(73, $row)->getValue();
                                            // $account_statement_no = $objWorksheet->getCellByColumnAndRow(74, $row)->getValue();
                                            // $type = $objWorksheet->getCellByColumnAndRow(75, $row)->getValue();
                                            // $pk 	= $objWorksheet->getCellByColumnAndRow(76, $row)->getValue();
                                            // $borrow_classification = $objWorksheet->getCellByColumnAndRow(77, $row)->getValue();
                                            // $posting_date = $objWorksheet->getCellByColumnAndRow(78, $row)->getValue();

                                            // $posting_date  = \PHPExcel_Style_NumberFormat::toFormattedString($posting_date, 'DD-MM-YYYY');
                                            
                                            // $recorded_date = $objWorksheet->getCellByColumnAndRow(79, $row)->getValue();

                                            // $recorded_date = \PHPExcel_Style_NumberFormat::toFormattedString($recorded_date, 'DD-MM-YYYY');
                                            
                                            // $registration_date = $objWorksheet->getCellByColumnAndRow(80, $row)->getValue();

                                            // $registration_date = \PHPExcel_Style_NumberFormat::toFormattedString($registration_date, 'DD-MM-YYYY');
                                            
                                            // $consumption_tax   = $objWorksheet->getCellByColumnAndRow(81, $row)->getValue();
                                            // $clearing_date 		 = $objWorksheet->getCellByColumnAndRow(82, $row)->getValue();

                                            // $clearing_date = \PHPExcel_Style_NumberFormat::toFormattedString($clearing_date, 'DD-MM-YYYY');
                                            
                                            // $clearing_slip = $objWorksheet->getCellByColumnAndRow(83, $row)->getValue();
                                            // $request_no    = $objWorksheet->getCellByColumnAndRow(84, $row)->getValue();
                                            //check varchar length limit
                                            // $org_true 				= "true";

                                            $layer_code_true = "true";
                                            $destination_name_true = "true";
                                            $item_code_true = "true";
                                            $item_name_true = "true";
                                            $item_name_2_true = "true";
                                            $unit_true = "true";
                                            $numbers_day_true = "true";
                                            $receipt_index_no_true = "true";
                                            $quantity_true = "true";
                                            $amount_true = "true";
                                            $is_error_true = "true";
                                            $is_sold_true = "true";
                                            $is_contract_true = "true";
                                            // $reason_true = "true";
                                            // $solution_true = "true";

                                            // if (!empty($organization)) {
                                            //     $ognLength = mb_strlen(trim($organization));

                                            //     if ($ognLength <= '15') {
                                            //         $org_true = "true";
                                            //     } else {
                                            //         $org_true = "false";

                                            //         CakeLog::write('debug', ' Invalid organization format error occur.  '.$organization.' will be string length <=15, at col G and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //         global $ERROR_VALUE;
                                            //         $ERROR_VALUE = "G";
                                            //     }
                                            // }

                                            if (!empty($layer_code)) {
                                                $layer_code_length = mb_strlen(trim($layer_code));

                                                if ($layer_code_length <= '6') {
                                                    $layer_code_true = "true";
                                                } else {
                                                    $layer_code_true = "false";

                                                    CakeLog::write('debug', ' Invalid layer_code format error occur. '.$layer_code.' will be string length <=6, at col F and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = "F";
                                                }
                                            }
                                            if (!empty($destination_name)) {
                                                $destination_nameLength = mb_strlen(trim($destination_name));

                                                if ($destination_nameLength <= '50') {
                                                    $destination_name_true = "true";
                                                } else {
                                                    $destination_name_true = "false";

                                                    CakeLog::write('debug', ' Invalid destination_name format error occur '.$destination_name.' will be string length <=50 at col G and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = "G";
                                                }
                                            }
                                            if (!empty($item_code)) {
                                                $item_codeLength = mb_strlen(trim($item_code));

                                                if ($item_codeLength <= '20') {
                                                    $item_code_true = "true";
                                                } else {
                                                    $item_code_true = "false";

                                                    CakeLog::write('debug', ' Invalid accaunt_name format error occur.'.$item_code.' will be string length <= 50, at col H and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = "H";
                                                }
                                            }
                                            if (!empty($item_name)) {
                                                $item_nameLength = mb_strlen(trim($item_name));

                                                if ($item_nameLength <= '50') {
                                                    $item_name_true = "true";
                                                } else {
                                                    $item_name_true = "false";

                                                    CakeLog::write('debug', ' Invalid item_name format error occur '.$item_name.' will be string length <= 50, at col I and row '.$row.' length '.$item_nameLength.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = "I";
                                                }
                                            }
                                        
                                            if (!empty($item_name_2)) {
                                                $item_name_2Length = mb_strlen(trim($item_name_2));

                                                if ($item_name_2Length <= '50') {
                                                    $item_name_2_true = "true";
                                                } else {
                                                    $item_name_2_true = "false";

                                                    CakeLog::write('debug', ' Invalid item_name_2 format error occur '.$item_name_2.' will be string length <=50, at col J and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;

                                                    $ERROR_VALUE = "J";
                                                }
                                            }
                                            if (!empty($unit_true)) {
                                                $unit_trueLength = mb_strlen(trim($unit));

                                                if ($unit_trueLength <= '4') {
                                                    $unit_true = "true";
                                                } else {
                                                    $unit_true = "false";

                                                    CakeLog::write('debug', ' Invalid unit_true format error occur '.$unit_true.' will be string length <= 4, at col K and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = 'K';
                                                }
                                            }
                                            
                                            if (!empty($numbers_day)) {
                                                $numbers_dayLength = mb_strlen(abs(trim($numbers_day)));
                                                if ($numbers_dayLength <= '10' && ($numbers_day >= -2147483648 && $numbers_day <= 2147483647)) {
                                                    $numbers_day_true = "true";
                                                } else {
                                                    $numbers_day_true = "false";

                                                    CakeLog::write('debug', ' Invalid number of days format error occur '.$numbers_day.' will be string length <= 11, at col M and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = 'M';
                                                }
                                            }

                                            if (!empty($receipt_index_no)) {
                                                $receipt_index_noLength = mb_strlen(trim($receipt_index_no));

                                                if ($receipt_index_noLength <= '20') {
                                                    $receipt_index_no_true = "true";
                                                } else {
                                                    $receipt_index_no_true = "false";

                                                    CakeLog::write('debug', 'Invalid receipt_index_no format error occur '.$receipt_index_no.' will be string length <=20, at col N and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = 'N';
                                                }
                                            }
                                            if (!empty($quantity)) {
                                                $quantityLength = mb_strlen(abs(trim($quantity)));

                                                if ($quantityLength <= '10' && ($quantity >= -2147483648 && $quantity <= 2147483647)) {
                                                    $quantity_true = "true";
                                                } else {
                                                    $quantity_true = "false";

                                                    CakeLog::write('debug', 'Invalid quantity format error occur '.$quantity.' will be string length <=20, at col O and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = 'O';
                                                }
                                            }
                                            if (!empty($amount)) {
                                                $amountLength = mb_strlen(abs(trim($amount)));

                                                if ($amountLength <= '19' && ($amount >= -9223372036854775808 && $amount <= 9223372036854775807)) {
                                                    $amount_true = "true";
                                                } else {
                                                    $amount_true = "false";

                                                    CakeLog::write('debug', 'Invalid amount format error occur '.$amount.' will be string length <=50, at col P and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = 'P';
                                                }
                                            }
                                            if (!empty($is_error)) {
                                                $is_errorLength = mb_strlen(trim($is_error));

                                                if ($is_errorLength <= '15') {
                                                    $is_error_true = "true";
                                                } else {
                                                    $is_error_true = "false";

                                                    CakeLog::write('debug', 'Invalid is_error format error occur '.$is_error.' will be string length <=15, at col Q and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = 'Q';
                                                }
                                            }
                                            if (!empty($is_sold)) {
                                                $is_soldLength = mb_strlen(trim($is_sold));

                                                if ($is_soldLength <= '15') {
                                                    $is_sold_true = "true";
                                                } else {
                                                    $is_sold_true = "false";

                                                    CakeLog::write('debug', 'Invalid is_sold format error occur '.$is_sold.' will be string length <=15, at col BZ and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = 'R';
                                                }
                                            }
                                            if (!empty($is_contract)) {
                                                $is_contractLength = mb_strlen(trim($is_contract));

                                                if ($is_contractLength <= '15') {
                                                    $is_contract_true = "true";
                                                } else {
                                                    $is_contract_true = "false";

                                                    CakeLog::write('debug', 'Invalid is_contract format error occur '.$is_contract.' will be string length <=15, at col S and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = 'S';
                                                }
                                            }
                                            
                                            //check validate "date DD-MM-YYYY" format
                                            // $schedule 		= "true";
                                            // $receipt_pay 	= "true";
                                            // $maturity 		= "true";
                                            // $in_out 	    = "true";
                                            // $sale  			= "true";
                                            // $bl 			= "true";
                                            // $posting 		= "true";
                                            // $recorded 		= "true";
                                            // $registration 	= "true";
                                            // $clearing 		= "true";
                                            $registration_date_true = "true";

                                            if (!empty($registration_date)) {
                                                $registration_date_true = $this->validateDate($registration_date, 'L(registration_date)', $row);
                                                $registration_date = date('Y-m-d', strtotime($registration_date));
                                            }
                                            
                                            // if (!empty($receipt_pay_date)) {
                                            //     $receipt_pay = $this->validateDate($receipt_pay_date, 'AA(receipt_pay_date)', $row);
                                            //     $receipt_pay_date = date('Y-m-d', strtotime($receipt_pay_date));
                                            // }
                                            // if (!empty($maturity_date)) {
                                            //     $posting = $this->validateDate($maturity, 'Z(maturity_date)', $row);
                                            //     $maturity_date = date('Y-m-d', strtotime($maturity_date));
                                            // }
                                            // if (!empty($in_out_date)) {
                                            //     $in_out = $this->validateDate($in_out_date, 'AM(in_out_date)', $row);
                                            //     $in_out_date = date('Y-m-d', strtotime($in_out_date));
                                            // }
                                            
                                            // if (!empty($bl_date)) {
                                            //     $bl = $this->validateDate($bl_date, 'BJ(bl_date)', $row);
                                            //     $bl_date = date('Y-m-d', strtotime($bl_date));
                                            // }
                                            // if (!empty($posting_date)) {
                                            //     $posting = $this->validateDate($posting_date, 'CA(posting_date)', $row);
                                            //     $posting_date = date('Y-m-d', strtotime($posting_date));
                                            // }
                                    
                                            // if (!empty($recorded_date)) {
                                            //     $recorded = $this->validateDate($recorded_date, 'CB(recorded_date)', $row);
                                            //     $recorded_date = date('Y-m-d', strtotime($recorded_date));
                                            // }
                                            // if (!empty($registration_date)) {
                                            //     $registration = $this->validateDate($registration_date, 'CC(registration_date)', $row);
                                            //     $registration_date = date('Y-m-d', strtotime($registration_date));
                                            // }
                                            // if (!empty($clearing_date)) {
                                            //     $clearing = $this->validateDate($clearing_date, 'CE(clearing_date)', $row);
                                            //     $clearing_date = date('Y-m-d', strtotime($clearing_date));
                                            // }

                                            //check validate interger no
                                            // $numbers 	  = "true";
                                            // $parent_index = "true";
                                            // $contract 	  = "true";
                                            // $transaction_search = "true";
                                            // $counterparty = "true";
                                            // $item 		  = "true";
                                            // $ref_item 	  = "true";
                                            // $uni 		  = "true";
                                            // $qty 		  = "true";
                                            // $unit_pric    = "true";
                                            // $slip_ymd 	  = "true";
                                            // $supplementary   = "true";
                                            // $oversea_store_r = "true";
                                            // $yer 			 = "true";
                                            // $account_slip = "true";
                                            // $account_statement = "true";
                                            // $p_k 		  = "true";
                                            // $chkCurrency = "true";
                                            // $req_no = "true";
                                            // $foreign_amt = "true";
                                            // $jp_amt = "true";
                                            #need to check
                                            // $numbers_day_true
                                            // $quantity_true

                                            $saveCurrency = "";

                                            if (!empty($numbers_day)) {
                                                if (is_numeric($numbers_day)) {
                                                    $numbers_dayLen = mb_strlen(trim($numbers_day));
                                                    
                                                    if ($numbers_dayLen <= '11') {
                                                        $removeComma = str_replace(',', '', $numbers_day);

                                                        if (preg_match("/^-?[0-9]+$/", $removeComma)) {
                                                            $numbers_day_true = "true";
                                                        } else {
                                                            $numbers_day_true = "false";
                                                            CakeLog::write('debug', ' Invalid jp_amount format error occur. '.$numbers_day.' will be interger length <=11, at col M and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                            global $ERROR_VALUE;
                                                            $ERROR_VALUE = 'M';
                                                        }
                                                    } else {
                                                        $numbers_day_true = "false";

                                                        CakeLog::write('debug', ' Invalid numbers_day format error occur. '.$numbers_day.' will be interger length <=11, at col M and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                        global $ERROR_VALUE;
                                                        $ERROR_VALUE = 'M';
                                                    }
                                                } else {
                                                    $numbers_day_true = "false";

                                                    CakeLog::write('debug', ' Invalid jp_amount format error occur. '.$numbers_day.' will be interger length <=11, at col M and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = 'M';
                                                }
                                            }
                                            if (!empty($quantity)) {
                                                if (is_numeric($quantity)) {
                                                    $quantityLen = mb_strlen(trim($quantity));
                                                    
                                                    if ($quantityLen <= '16') {
                                                        $removeComma = str_replace(',', '', $quantity);

                                                        if (preg_match("/^-?[0-9.]+$/", $removeComma)) {
                                                            $quantity_true = "true";
                                                        } else {
                                                            $quantity_true = "false";
                                                            CakeLog::write('debug', ' Invalid jp_amount format error occur. '.$quantity.' will be interger length <=16, at col O and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                            global $ERROR_VALUE;
                                                            $ERROR_VALUE = 'O';
                                                        }
                                                    } else {
                                                        $quantity_true = "false";

                                                        CakeLog::write('debug', ' Invalid numbers_day format error occur. '.$currency.' will be interger length <=14, at col O and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                        global $ERROR_VALUE;
                                                        $ERROR_VALUE = 'O';
                                                    }
                                                } else {
                                                    $quantity_true = "false";

                                                    CakeLog::write('debug', ' Invalid jp_amount format error occur. '.$quantity.' will be interger length <=16, at col O and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = 'O';
                                                }
                                            }
                                            if (!empty($amount)) {
                                                if (is_numeric($amount)) {
                                                    $amountLen = mb_strlen(trim($amount));
                                                    
                                                    if ($amountLen <= '16') {
                                                        $removeComma = str_replace(',', '', $amount);

                                                        if (preg_match("/^-?[0-9.]+$/", $removeComma)) {
                                                            $amount_true = "true";
                                                        } else {
                                                            $amount_true = "false";
                                                            CakeLog::write('debug', ' Invalid jp_amount format error occur. '.$amount.' will be interger length <=16, at col O and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                            global $ERROR_VALUE;
                                                            $ERROR_VALUE = 'P';
                                                        }
                                                    } else {
                                                        $amount_true = "false";

                                                        CakeLog::write('debug', ' Invalid numbers_day format error occur. '.$amount.' will be interger length <=14, at col P and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                        global $ERROR_VALUE;
                                                        $ERROR_VALUE = 'P';
                                                    }
                                                } else {
                                                    $amount_true = "false";

                                                    CakeLog::write('debug', ' Invalid jp_amount format error occur. '.$amount.' will be interger length <=16, at col P and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = 'P';
                                                }
                                            }

                                            // if (!empty($request_no)) {
                                            //     $req_no = $this->validateInterger($request_no, 'CG', $row);

                                            //     if ($req_no == "true") {
                                            //         $req_no_Len = mb_strlen(trim($request_no));

                                            //         if ($req_no_Len <= '13') {
                                            //             $req_no = "true";
                                            //         } else {
                                            //             $req_no = "false";

                                            //             CakeLog::write('debug', ' Invalid request_no format error occur. '.$currency.' will be interger length <=13, at col CG and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //             global $ERROR_VALUE;
                                            //             $ERROR_VALUE = 'CG';
                                            //         }
                                            //     }
                                            // }

                                            // if (!empty($currency)) {
                                            //     $currencyLen = mb_strlen(trim($currency));

                                            //     if ($currencyLen <= '5') {
                                            //         $chkCurrency = "true";
                                            //         $saveCurrency = strtolower($currency);
                                            //     } else {
                                            //         $chkCurrency = "false";

                                            //         CakeLog::write('debug', ' Invalid currency format error occur. '.$currency.' will be string length <=5, at col N and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //         global $ERROR_VALUE;
                                            //         $ERROR_VALUE = 'N';
                                            //     }
                                            // }

                                            // if (!empty($numbers_day)) {
                                            //     $numbers = $this->validateInterger($numbers_day, 'V', $row);
                                            // }
                                            
                                            // if (!empty($contract_no)) {
                                            //     $contract = $this->validateInterger($contract_no, 'AE', $row);
                                            // }
                                            // if (!empty($transaction_search_key)) {
                                            //     $transaction_len = mb_strlen(trim($transaction_search_key));

                                            //     if ($transaction_len <= '12') {
                                            //         $transaction_search = "true";
                                            //     } else {
                                            //         $transaction_search = "false";

                                            //         CakeLog::write('debug', ' Invalid transaction_search_key error occur '.$transaction_search_key.' will be string length <=12, at col AF and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //         global $ERROR_VALUE;
                                            //         $ERROR_VALUE = 'AF';
                                            //     }
                                            // }
                                            // if (!empty($counterparty_cd)) {
                                            //     $counterLen = mb_strlen(trim($counterparty_cd));

                                            //     if ($counterLen <= '10') {
                                            //         $counterparty = "true";
                                            //     } else {
                                            //         $counterparty = "false";

                                            //         CakeLog::write('debug', ' Invalid counterparty_cd format error occur '.$counterparty_cd.' will be length <= 10, at col AN and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //         global $ERROR_VALUE;
                                            //         $ERROR_VALUE = 'AN';
                                            //     }
                                            // }
                                        
                                            // if (!empty($ref_item_no)) {
                                            //     $ref_item = $this->validateInterger($ref_item_no, 'AW', $row);

                                            //     if ($ref_item == true) {
                                            //         $ref_len = mb_strlen(trim($ref_item_no));

                                            //         if ($ref_len <= '11') {
                                            //             $ref_item = "true";
                                            //         } else {
                                            //             $ref_item = "false";

                                            //             CakeLog::write('debug', ' Invalid ref_item_no format error '.$ref_item_no.' will be integet length <= 11, at col AW and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //             global $ERROR_VALUE;
                                            //             $ERROR_VALUE = 'AW';
                                            //         }
                                            //     }
                                            // }
                                            // if (!empty($unit)) {
                                            //     $uni_len = mb_strlen(trim($unit));

                                            //     if ($uni_len <= '4') {
                                            //         $uni = "true";
                                            //     } else {
                                            //         $uni = "false";

                                            //         CakeLog::write('debug', ' Invalid unit format error '.$unit.' will be string length <= 4, at col AX and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //         global $ERROR_VALUE;
                                            //         $ERROR_VALUE = 'AX';
                                            //     }
                                            // }
                                            // if (!empty($quantity)) {
                                            //     $removeComma = str_replace(',', '', $quantity);
        
                                            //     if (preg_match('/^[0-9]+$/', $removeComma)) {
                                            //         $qty = "true";
                                            //     } else {
                                            //         $removePoint = (int)$removeComma;

                                            //         $removePoint = strlen((string)$removePoint);
                
                                            //         if ($removePoint <= '12') {
                                            //             if (preg_match('/^[0-9]+\.[0-9]{1,4}$/', $removeComma)) {
                                            //                 $qty = "true";
                                            //             } else {
                                            //                 $qty = "false";

                                            //                 if (is_numeric($quantity)) {
                                            //                     CakeLog::write('debug', ' Invalid quantity format error occur. '.number_format($quantity, 4, '.', '').' will be decimal(16,4), at col AY and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //                     global $ERROR_VALUE;
                                            //                     $ERROR_VALUE = 'AY';
                                            //                 } else {
                                            //                     CakeLog::write('debug', ' Invalid quantity format error occur. '.$quantity.' will be decimal(16,4), at col AY and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //                     global $ERROR_VALUE;
                                            //                     $ERROR_VALUE = 'AY';
                                            //                 }
                                            //             }
                                            //         } else {
                                            //             $unit_pric = "false";

                                            //             CakeLog::write('debug', ' Invalid unit price format error occur. '.$unit_price.' will be decimal(17,3), at col AZ and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //             global $ERROR_VALUE;
                                            //             $ERROR_VALUE = 'AZ';
                                            //         }
                                            //     }
                                            // }
                                            // if (!empty($unit_price)) {
                                            //     $removeComma = str_replace(',', '', $unit_price);
        
                                            //     if (preg_match('/^[0-9]+$/', $removeComma)) {
                                            //         $unit_pric = "true";
                                            //     } else {
                                            //         $removePoint = (int)$removeComma;

                                            //         $removePoint = strlen((string)$removePoint);
                
                                            //         if ($removePoint <= '14') {
                                            //             if (preg_match('/^[0-9]+\.[0-9]{1,3}$/', $removeComma)) {
                                            //                 $unit_pric = "true";
                                            //             } else {
                                            //                 $unit_pric = "false";

                                            //                 if (is_numeric($unit_price)) {
                                            //                     CakeLog::write('debug', ' Invalid unit price format error occur. '.number_format($unit_price, 3, '.', '').' will be decimal(17,3), at col AZ and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //                     global $ERROR_VALUE;
                                            //                     $ERROR_VALUE = 'AZ';
                                            //                 } else {
                                            //                     CakeLog::write('debug', ' Invalid unit price format error occur. '.$unit_price.' will be decimal(17,3), at col AZ and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //                     global $ERROR_VALUE;
                                            //                     $ERROR_VALUE = 'AZ';
                                            //                 }
                                            //             }
                                            //         } else {
                                            //             $unit_pric = "false";

                                            //             CakeLog::write('debug', ' Invalid unit price format error occur. '.$unit_price.' will be decimal(17,3), at col AZ and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //             global $ERROR_VALUE;
                                            //             $ERROR_VALUE = 'AZ';
                                            //         }
                                            //     }
                                            // }

                                            // if (!empty($foreign_amount)) {
                                            //     $removeComma = str_replace(',', '', $foreign_amount);
                                                
                                            //     if (preg_match("/^-?[0-9]+$/", $removeComma)) {
                                            //         $foreign_amt = "true";
                                            //     } else {
                                            //         $removePoint = (int)$removeComma;

                                            //         $removePoint = strlen((string)$removePoint);
                                                
                                            //         if ($removePoint <= '14') {
                                            //             if (preg_match('/^-?[0-9]+\.[0-9]{1,5}$/', $removeComma)) {
                                            //                 $foreign_amt = "true";
                                            //             } else {
                                            //                 $foreign_amt = "false";

                                            //                 if (is_numeric($foreign_amount)) {
                                            //                     CakeLog::write('debug', ' Invalid foreign_amount format error occur. '.number_format($foreign_amount, 5, '.', '').' will be decimal(19,5), at col Q and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //                     global $ERROR_VALUE;
                                            //                     $ERROR_VALUE = 'Q';
                                            //                 } else {
                                            //                     CakeLog::write('debug', ' Invalid foreign_amount format error occur. '.$foreign_amount.' will be decimal(19,5), at col Q and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //                     global $ERROR_VALUE;
                                            //                     $ERROR_VALUE = 'Q';
                                            //                 }
                                            //             }
                                            //         } else {
                                            //             $foreign_amt = "false";

                                            //             if (is_numeric($foreign_amount)) {
                                            //                 CakeLog::write('debug', ' Invalid foreign_amount format error occur. '.number_format($foreign_amount, 5, '.', '').' will be decimal(19,5), at col Q and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());


                                            //                 global $ERROR_VALUE;
                                            //                 $ERROR_VALUE = 'Q';
                                            //             } else {
                                            //                 CakeLog::write('debug', ' Invalid foreign_amount format error occur. '.$foreign_amount.' will be decimal(19,5), at col Q and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());


                                            //                 global $ERROR_VALUE;
                                            //                 $ERROR_VALUE = 'Q';
                                            //             }
                                            //         }
                                            //     }
                                            // }

                                            // if (!empty($slip_ymd_no)) {
                                            //     $slip_ymd = $this->validateInterger($slip_ymd_no, 'BB', $row);
                                            // }
                                            // if (!empty($supplementary_qty)) {
                                            //     $removeComma = str_replace(',', '', $supplementary_qty);
        
                                            //     if (preg_match('/^[0-9]+$/', $removeComma)) {
                                            //         $supplementary = "true";
                                            //     } else {
                                            //         $removePoint = (int)$removeComma;

                                            //         $removePoint = strlen((string)$removePoint);
                
                                            //         if ($removePoint <= '12') {
                                            //             if (preg_match('/^[0-9]+\.[0-9]{1,4}$/', $removeComma)) {
                                            //                 $supplementary = "true";
                                            //             } else {
                                            //                 $supplementary = "false";

                                            //                 if (is_numeric($supplementary_qty)) {
                                            //                     CakeLog::write('debug', ' Invalid supplementary_qty format error occur. '.number_format($supplementary_qty, 4, '.', '').' will be decimal(16,4), at col BC and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //                     global $ERROR_VALUE;
                                            //                     $ERROR_VALUE = 'BC';
                                            //                 } else {
                                            //                     CakeLog::write('debug', ' Invalid supplementary_qty format error occur. '.$supplementary_qty.' will be decimal(16,4), at col BC and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //                     global $ERROR_VALUE;
                                            //                     $ERROR_VALUE = 'BC';
                                            //                 }
                                            //             }
                                            //         } else {
                                            //             $supplementary = "false";

                                            //             if (is_numeric($supplementary_qty)) {
                                            //                 CakeLog::write('debug', ' Invalid supplementary_qty format error occur. '.number_format($supplementary_qty, 4, '.', '').' will be decimal(16,4), at col BC and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //                 global $ERROR_VALUE;
                                            //                 $ERROR_VALUE = 'BC';
                                            //             } else {
                                            //                 CakeLog::write('debug', ' Invalid supplementary_qty format error occur. '.$supplementary_qty.' will be decimal(16,4), at col BC and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            //                 global $ERROR_VALUE;
                                            //                 $ERROR_VALUE = 'BC';
                                            //             }
                                            //         }
                                            //     }
                                            // }
                                            
                                            // if (!empty($year)) {
                                            //     $yer = $this->validateInterger($year, 'BU', $row);
                                            // }
                                            
                                            // if (!empty($pk)) {
                                            //     $p_k = $this->validateInterger($pk, 'BY', $row);
                                            // }
                                            if($layer_code_true == "true" && $destination_name_true == "true" && $item_code_true == "true" && $item_name_true == "true" && $item_name_2_true == "true" && $unit_true == "true" && $numbers_day_true == "true" && $receipt_index_no_true == "true" && $quantity_true == "true" && $amount_true == "true" && $is_error_true == "true" && $is_sold_true == "true" && $is_contract_true == "true" && $registration_date_true == "true") {
                                                // $checkAccSlip_state_no = $this->Stock->find(
                                                //     'all',
                                                //     array('conditions'=>array('account_slip_no'=>$account_slip_no,
                                                //         'account_statement_no' => $account_statement_no,
                                                //         'period'=>$period,
                                                //         'NOT'=>array('flag' => '0')))
                                                // );

                                                
                                                $IsHasBAcode = $Common->getLayerThreeName($layer_code,'StockSelections',$period);
                                                
                                                // layer_code of flag >=5, can't import this layer_code again.
                                                $ba_flag = $this->Stock->find('all', array(
                                                                                    'conditions' => array('Stock.layer_code' => $layer_code,'Stock.flag >='=> '5',
                                                                                        "Stock.period"=>$period)));
                                                if (!empty($IsHasBAcode)) {
                                                    // if (empty($checkAccSlip_state_no)) {
                                                        if (empty($ba_flag)) {
                                                            $i++;
                                                            $date = date('Y-m-d H:i:s');
                                                            $data[] = array("period"=>"$period", "layer_code"=>"$layer_code","destination_name"=>"$destination_name","item_code"=>"$item_code","item_name"=>"$item_name","item_name_2"=>"$item_name_2","unit"=>"$unit","registration_date"=>"$registration_date","numbers_day"=>"$numbers_day","receipt_index_no"=>"$receipt_index_no","quantity"=>"$quantity","amount"=>"$amount","is_error"=>"$is_error","is_sold"=>"$is_sold", "is_contract"=>"$is_contract","flag"=>"1","base_date"=>"$base_date","deadline_date"=>"$deadLine_date","created_by"=>"$user_id","updated_by"=>"$user_id",
                                                            "created_date"=>"$date",
                                                            "updated_date"=>"$date");
                                                        } else {
                                                            $SkipAccSlicLine[] = __("行目で部長によってこのコードを承認済み ").$row;
                                                        
                                                            $this->Session->write('SkipAccSlicLine', 'SkipAccSlicLine');
                                                        }
                                                    // } else {
                                                    //     $SkipAccSlicLine[] = __("行 ").$row.__(" の 会計伝票№ 又は 会計明細№ 既に存在します。");

                                                    //     $this->Session->write('SkipAccSlicLine', 'SkipAccSlicLine');
                                                    // }
                                                } else {
                                                    // to insert in Layer
                                                    if (!in_array($layer_code, $SkipCheckBAcode, true)) {
                                                        array_push($SkipCheckBAcode, $layer_code);
                                                    }

                                                    $this->Session->write('SkipCheckBAcode', 'SkipCheckBAcode');
                                                }
                                            } else {
                                                if (@ file_get_contents($tempPath)) {
                                                    unlink($tempPath);
                                                }

                                                global $ERROR_VALUE;
                                                $param_1 = array();
                                                $param_1['row']  = $row;
                                                $param_1['col'] = $ERROR_VALUE;

                                                $errorMsg = parent::getErrorMsg('SE023', $param_1);
                                                $this->Flash->set($errorMsg, array('key'=>'noSlipState'));
                                                $this->redirect(array('controller'=>'StockImports','action'=>'index'));
                                            }
                                        }
                                        if (!empty($data)) {
                                            $attachDB = $this->Stock->getDataSource();
                                            try {
                                                
                                                if($_POST['mailSend']) {

                                                    $mail_template = 'common';
                                                    $mail['subject']        = $_POST['mailSubj'];
                                                    $mail['template_title'] = $_POST['mailTitle'];
                                                    $mail['template_body']  = $_POST['mailBody'];
                                                    $to_email  = $_POST['toEmail'];
                                                    $cc_email  = $_POST['ccEmail']; 
                                                    $bcc_email = $_POST['bccEmail']; 
                                                    $url = '';
                                                    // $url = '/StockAccountPreviews/index?'.'param='.$period;
                                                    $toEmail = parent::formatMailInput($to_email);
                                                    $ccEmail = parent::formatMailInput($cc_email);
                                                    $bccEmail = parent::formatMailInput($bcc_email);
                                             
                                                    $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

                                                    if ($sendMail["error"]) {
                                                        $msg = $sendMail["errormsg"];
                                                        $this->Flash->set($msg, array('key'=>'excelError'));
                                                        $invalid_email = parent::getErrorMsg('SE042');
                                                        $this->Flash->set($invalid_email, array('key'=>'excelError'));
                                                    } else {
                                                        $this->Stock->saveAll($data);
                                                        $attachDB->commit();
                                                        $attachDB->begin();
                                                        $successMsg = parent::getSuccessMsg('SS009', $i);
                                                        $this->Flash->set($successMsg, array('key'=>'success'));

                                                        $successMsg = parent::getSuccessMsg("SS018");
                                                        $this->Flash->set($successMsg, array('key'=>'success'));
                                                    } 
                                                }else{

                                                    $this->Stock->saveAll($data);
                                                    $attachDB->commit();
                                                    $attachDB->begin();
                                                    $successMsg = parent::getSuccessMsg('SS009', $i);
                                                    $this->Flash->set($successMsg, array('key'=>'success'));

                                                }
                                                
                                            } catch (Exception $e) {
                                                $attachDB->rollback();
                                                CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                                                if (@ file_get_contents($tempPath)) {
                                                    unlink($tempPath);
                                                }
                                                $errorMsg = parent::getErrorMsg('SE015');
                                                $this->Flash->set($errorMsg, array('key'=>'excelError'));
                                            }
                                        }elseif(empty($SkipCheckBAcode) && empty($SkipAccSlicLine)) {
                                            #for only header and no data contain
                                            CakeLog::write('debug', ' Data do not contained in file. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                            $errorMsg = parent::getErrorMsg('SE048');
                                            $this->Flash->set($errorMsg, array('key'=>'excelError'));
                                            $this->redirect(array('controller'=>'StockImports','action'=>'index'));
                                        }
                                        
                                    
                                        if (@ file_get_contents($tempPath)) {
                                            unlink($tempPath);
                                        }
                                        if (!empty($SkipCheckBAcode)) {
                                            $this->Session->write('SKIPCHECKBALINE', $SkipCheckBAcode);
                                        }
                                        if (!empty($SkipAccSlicLine)) {
                                            $this->Session->write('SKIPSLICLINE', $SkipAccSlicLine);
                                        }
                                    } else {
                                        if (@ file_get_contents($tempPath)) {
                                            unlink($tempPath);
                                        }
                                        $errorMsg = parent::getErrorMsg('SE022');
                                        $this->Flash->set($errorMsg, array('key'=>'excelError'));
                                    }
                                } else {
                                    if (@ file_get_contents($tempPath)) {
                                        unlink($tempPath);
                                    }
                                    $errorMsg = parent::getErrorMsg('SE015');
                                    $this->Flash->set($errorMsg, array('key'=>'excelError'));
                                }
                            } else {
                                if (@ file_get_contents($tempPath)) {
                                    unlink($tempPath);
                                }
                                
                                $errorMsg = parent::getErrorMsg("SE013", $ext);
                                $this->Flash->set($errorMsg, array('key'=>'excelError'));
                            }
                        } else {
                            CakeLog::write('debug', 'cannot upload file under tmp. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                            $errorMsg = parent::getErrorMsg('SE015');
                            $this->Flash->set($errorMsg, array('key'=>'excelError'));
                        }
                    } else {
                        CakeLog::write('debug', 'file size over 10MB. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                        $errorMsg = parent::getErrorMsg('SE020');
                        $this->Flash->set($errorMsg, array('key'=>'excelError'));
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE015');
                    $this->Flash->set($errorMsg, array('key'=>'excelError'));
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE015');
                $this->Flash->set($errorMsg, array('key'=>'excelError'));
            }
            
            
        }
        $this->redirect(array('controller'=>'StockImports','action'=>'index'));
    }

    public function validateDate($date, $col, $row, $format = 'd-m-Y')
    {
        global $ERROR_VALUE;
            
        $d = DateTime::createFromFormat($format, $date);
        if ($d) {
            return "true";
        } else {
            CakeLog::write('debug', ' Invalid Date format error occur '.$date.' will be "Date Format (DD-MM-YYYY)", at col '.$col.' and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $ERROR_VALUE = $col;
            
            return "false";
        }
    }

    public function validateInterger($integ, $col, $row)
    {
        global $ERROR_VALUE;
        $removeComma = str_replace(',', '', $integ);
    
        if (preg_match('/^[0-9]+$/', $removeComma)) {
            return "true";
        } else {
            CakeLog::write('debug', ' Invalid Integer format error occur '.$integ.' will be "Integer Format[0-9]", at col '.$col.' and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $ERROR_VALUE = $col;

            return "false";
        }
    }

    public function inform_SkipCheckBAcode()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = false;

        $inform_bacode = $this->Session->read('SKIPCHECKBALINE');
        $this->Session->delete('SKIPCHECKBALINE');
        
        echo json_encode($inform_bacode);
    }
    
    public function inform_SkipSlicLine()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = false;

        $inform_slicline = $this->Session->read('SKIPSLICLINE');
        $this->Session->delete('SKIPSLICLINE');
        
        echo json_encode($inform_slicline);
    }
    #data pass from view ajax to controller to get mail data
    public function getMailLists() {
        #only allow ajax request
        $language = $this->Session->read('Config.language');
        parent::checkAjaxRequest($this);
        $Common     = New CommonController();
        $period     = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_name    = $this->Session->read('StockSelections_BA_NAME');
        $layer_code    = $_POST['layer_code']; //($_POST['layer_code'])? $_POST['layer_code'] : '';
        $page       = $_POST['page'];
        $function   = $_POST['function'];
        
        $mails = $Common->getMailList($layer_code, $page, $function, $language, $layer_name, $period, Setting::LAYER_SETTING['StockSelections']);
        return json_encode($mails);
    }
}
