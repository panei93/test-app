<?php

/**
 * ActualResults Controller
 *
 * @property ActualResult $ActualResult
 * @property PaginatorComponent $Paginator
 */
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Vendor', 'php-excel-reader/PHPExcel'); # excel
App::import('Controller', 'Common'); # mail
define('UPLOAD_FILEPATH', ROOT); # server
define('UPLOAD_PATH', 'app'.DS.'temp'); # path

class BrmActualResultController extends AppController
{

/**
 * Components
 *
 * @var array
 */

    public $components = array('Session','Paginator','PhpExcel.PhpExcel','Flash');
    public $uses = array('BrmActualResult','BrmTerm','Account','BrmAccountResult', 'BrmLogistic', 'LayerType', 'Layer', 'BrmSaccount', 'BrmActualResultSummary', 'BrmBackupFile', 'Menu', 'Permission');

    public function beforeFilter()
    {
        ini_set('memory_limit', '-1');

        parent::checkUserStatus();
        parent::CheckSession();
        parent::checkAccessType();


        $term_id = $this->Session->read('TERM_ID');
        $target_month = $this->Session->read('TARGETMONTH');

        if (!$term_id && !$target_month) {
            $errorMsg = parent::getErrorMsg('SE080');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        } elseif (!$target_month) {
            $errorMsg = parent::getErrorMsg('SE085');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }


        if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }
        $Common = new CommonController();

        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        // $layers = array_keys($permissions['index']['layers']);
        
        // if ((!in_array($layer_code, $layers)) || ($layer_code == "" && $permissions['index']['limit'] > 0)) {
            
        //     $errorMsg = parent::getErrorMsg('SE065');
        //     $this->Flash->set($errorMsg, array("key"=>"TermError"));
        //     $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        // }
    }
    
    /**
     * Show data in view from tbl_actual_data
     *
     * @author Ei Thandar Kyaw (20221014)
     * @return data
     */
    public function index()
    {
        $this->layout = 'phase_3_menu';
        # msg with confirmation box by Khin Hnin Myo (Start)
        if ($this->Session->check('REGBA')) {
            $regba = $this->Session->read('REGBA');
            $this->Session->delete('REGBA');
            $this->set('regba', $regba);
        }

        if ($this->Session->check('REGACC')) {
            $regacc = $this->Session->read('REGACC');
            $this->Session->delete('REGACC');
            $this->set('regacc', $regacc);
        }

        if ($this->Session->check('DUPLICATEDATA')) {
            $dupdata = $this->Session->read('DUPLICATEDATA');
            $this->Session->delete('DUPLICATEDATA');
            $this->set('dupdata', $dupdata);
        }
        if ($this->Session->check('REGLOGISTICNO')) {
            $regLogisticNo = $this->Session->read('REGLOGISTICNO');
            $this->Session->delete('REGLOGISTICNO');
            $this->set('regLogisticNo', $regLogisticNo);
        }

        $showSaveBtn = false;
        $showSendMailBtn = false;
        $showSendMailMRBtn = false;
        $permissions = $this->Session->read('PERMISSIONS');
        
        $save_permt = $permissions['save'];
        $save_layer = array_keys($save_permt['layers']);
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        if (($layer_code == '' && $save_permt['limit']==0) || in_array($layer_code, $save_layer)) {
            $showSaveBtn = true;
        }
        $page = 'BrmActualResult';
        $function = array('SendMailBRD', 'SendMailMR');
        $mailMethod = $this->Menu->find('all', array(
            'fields' => array('permission.*', 'Menu.method'),
            'conditions' => array(
                'Menu.page_name'     => $page,
                'Menu.method IN'        => $function,
                'permission.role_id' => $_SESSION['ADMIN_LEVEL_ID'],
            ),
            'joins' => array(
                array(
                    'table' => 'permissions',
                    'alias' => 'permission',
                    'type' => 'left',
                    'conditions' => array(
                        'permission.menu_id = Menu.id AND Menu.flag = 1'
                    )
                ),
            ),
        ));
        foreach($mailMethod as $key=>$value){
            $mailSend[$value['Menu']['method']] = $value['permission']['mail_send'];
        }
        if($mailSend['SendMailBRD'] == 1) $showSendMailBtn = true;
        if($mailSend['SendMailMR'] == 1) $showSendMailMRBtn = true;
        # msg with confirmation box by Khin Hnin Myo (End)

        $term_id = $this->Session->read('TERM_ID');
        $term = $this->Session->read('TERM_NAME');
        $hq_id = $this->Session->read('HEAD_DEPT_CODE');
        $topLayer = $this->Session->read('TOP_LAYER');
        $topLayerCode = explode(',', $topLayer);
        
        $headquarter = $this->Session->read('HEAD_DEPT_NAME');
       
        # get data from session
        $admin_level_id = $this->Session->read('ADMIN_LEVEL_ID');

        $Common = new CommonController;
        $topLayer = Setting::LAYER_SETTING['topLayer'];
        $bottomLayer = Setting::LAYER_SETTING['bottomLayer'];
        #Get All Headquarter list
        $hq_list = $this->Layer->find('list', array(
            'fields' => array('layer_code', 'name_jp'),
            'conditions' => array(
                'Layer.flag' => 1,
                'Layer.type_order' => $topLayer
            )
        ));

        $conditions = array();

        if ($hq_id != null || $hq_id != '') {
            $conditions["BrmActualResult.hlayer_code"] = $hq_id;
        }
        
        $type_order = $this->LayerType->find('first', array(
            'conditions' => array('LayerType.flag' => '1', 'type_order' => $topLayer),
            'fields' => array('LayerType.id' ,'LayerType.name_jp' ,'LayerType.name_en', 'type_order'), 
        ));
        $bottomLayerName = $this->LayerType->find('first', array(
            'conditions' => array('LayerType.flag' => '1', 'type_order' => $bottomLayer),
            'fields' => array('LayerType.id' ,'LayerType.name_jp' ,'LayerType.name_en', 'type_order'), 
        ));
        
        if ($this->Session->read('Config.language') == 'eng') {
            $lang_name = 'en';
        } else {
            $lang_name = 'jp';
        }
        try {
            $this->paginate  = array(
                'limit' => Paging::TABLE_PAGING,
                'conditions' => $conditions,
                'order' => 'BrmActualResult.id DESC'

            );
            
            $actual_data = $this->Paginator->paginate('BrmActualResult');
            
            $query_count = $this->params['paging']['BrmActualResult']['count'];
            if ($query_count == 0) {
                $errmsg = parent::getErrorMsg('SE001', $query_count);
                $this->set('errmsg', $errmsg);
            } else {
                $count = parent::getSuccessMsg('SS004', $query_count);
                $this->set('count', $count);
            }
            $b_code = $this->BrmActualResult->find('list', array(
                'fields' => 'layer_code',
                'group' => 'layer_code'));
            $today = date('Y-m-d');
            $conditions = [];
            $conditions['Layer.type_order'] = $bottomLayer;
            $conditions['Layer.layer_code'] = $b_code;
            $conditions['Layer.flag'] = 1;
            $conditions['Layer.to_date >='] = $today; #filter exprie data
            $conditions[] = 'Layer.id IN (select max(layers.id) from layers group by layers.layer_code)';
            $b_name = $this->Layer->find('all', array(
                'fields' => array('Layer.id', 'Layer.layer_code', 'Layer.name_jp', 'Layer.name_en'),
                'conditions' => $conditions,
                'order' 	 => array('Layer.layer_code ASC')
                
            ));
            
            $acc_code = $this->BrmActualResult->find('list', array(
            'fields' => 'account_code',
            'group' => 'account_code'));
            $t_month = $this->BrmActualResult->find('list', array(
            'fields' => 'target_month',
            'group' => 'target_month'));
            
            
            $this->set('b_name', $b_name);
            $this->set('acc_code', $acc_code);
            $this->set('t_month', $t_month);
            $this->set('actual_data', $actual_data);
            $this->set('term', $term);
            $this->set('term_id', $term_id);
            $this->set('headquarter', $headquarter);
            $this->set('hq_list', $hq_list);
            $this->set('hq_id', $hq_id);
            $this->set('admin_level_id', $admin_level_id);
            $this->set('type_order', $type_order);
            $this->set('bottomLayer', $bottomLayerName);
            $this->set('lang_name', $lang_name);
            $this->set('showSaveBtn', $showSaveBtn);
            $this->set('showSendMailBtn', $showSendMailBtn);
            $this->set('showSendMailMRBtn', $showSendMailMRBtn);
            $this->set('query_count', $query_count);
            $this->render('index');

            // $this->BrmActualResult->recursive = 0;
            // $this->set('actualResults', $this->Paginator->paginate());
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'BrmActualResult', 'action' => 'index'));
        }
    }

    /**
    * SaveActualFile method
    *
    * @author Ei Thandar Kyaw (20221014)
    * @return void
    */
    public function SaveActualFile()
    {
        if ($this->request->is('post')) {
            $topLayer = Setting::LAYER_SETTING['topLayer'];
            $ERROR_VALUE = "";
            $term_id = $this->request->data['term_id'];
            
            $hq_id = $this->request->data['hq_id'];
            #submission deadline date
            $submission_date = $this->request->data['submission_date'];
            $submission_deadline_date =date("Y-m-d", strtotime($submission_date));
            
            $file = $this->request->params['form']['uploadfile']; # get name, type, tmp_name, error, size of file
            $uploadPath = APP . 'tmp'; # file path
            $login_id = $this->Session->read('LOGIN_ID'); # get login id
            $date = date('Y-m-d H:i:s'); # for updated id and created id
            $header_flag = false;
            $data = array();
            $checkba = array();
            $checkacc = array();
            $backupList = array();

            #get all ba_code to check exist or not
            $ba_datas = $this->Layer->find('list', array(
                'fields' => array('id', 'layer_code','parent_id'),
                'conditions' => array(
                    'flag' => '1'
                )
            ));
            // $topLayers = $this->Layer->find('list', array(
            //     'fields' => array('id', 'layer_code'),
            //     'conditions' => array(
            //         'flag' => '1',
            //         'type_order' => $topLayer
            //     )
            // ));
            foreach($ba_datas as $key=>$value){
                if($key != ''){
                    foreach($value as $vKey=>$vValue){
                        $parent_id = json_decode($key, true);
                        if(array_keys($parent_id)[$topLayer-1] == 'L'.$topLayer) $ba_data[$vValue] = $parent_id['L'.$topLayer];
                
                    }
                }

            }
            
            #get all account_code to check exist or not
            $account_codes = $this->BrmSaccount->find('list', array(
                'fields' => array('account_code','id'),
                'conditions' => array(
                    'BrmSaccount.flag' => '1'
                )
            ));
            
            #start Import excel Year is not  Greater or Less than compare with Budget year in validation
            $term_data = $this->BrmTerm->find('first', array(
                'conditions'=>array(
                    'id'=>$term_id
                )
            ));
            // get logistic no and name
            $logistic_no = array();
            $logistic_name = array();
            # get all logistic index by year and ba code
            $logistic_indexs = $this->BrmLogistic->find('all', array(
                'conditions' => array(
                    'BrmLogistic.flag' => '1'
                )
            ));
            foreach ($logistic_indexs as $key=>$value) {
                foreach ($value['BrmLogistic'] as $colKey=>$column) {
                    if ($colKey == 'target_year') {
                        $target_year = $column;
                    }
                    if ($colKey == 'layer_code') {
                        $ba_code = $column;
                    }
                    if ($colKey == 'index_no' && $column != '') {
                        $logistic_no[$target_year][$ba_code][] = $column;
                    }
                    if ($colKey == 'index_name' && $column != '') {
                        $logistic_name[$target_year][$ba_code][] = $column;
                    }
                }
            }
            // get account code for sale
            $sale_account_codes = $this->BrmSaccount->find('list', array(
                'fields' => array('account_code'),
                'conditions' => array(
                    'BrmSaccount.flag' => '1',
                    'OR'=>array(
                        array('BrmSaccount.brm_account_id' => 1),
                        array('BrmSaccount.brm_account_id' => 2)
                    )
                )
            ));
            
            $start_term = date("Y-m", strtotime($term_data['BrmTerm']['budget_year'].'-'.$term_data['BrmTerm']['start_month']));
            $end_term   =  date("Y-m", strtotime($term_data['BrmTerm']['budget_end_year'].'-'.$term_data['BrmTerm']['end_month']));
            #previously 1 year from the budget terms
            $start_term = date("Y-m", strtotime($start_term. "last day of - 1 year"));
            $end_term 	= date("Y-m", strtotime($end_term. "last day of + 1 year"));

            # Calculate to adjust month
            $start_month = $term_data['BrmTerm']['start_month'];
            if ($start_month == 1) {
                $adjust_month = 12;
            } else {
                $adjust_month = $term_data['BrmTerm']['start_month'] - 1;
            }
            /*end validate*/

            try {
                if (!empty($file)) {
                    $file_type 	= 	$file['type'];
                    $file_name	=	$file['name'];
                    $file_loc 	= 	$file['tmp_name'];
                    $file_error = 	$file['error'];
                    $file_size 	= 	$file['size'];

                    if ($file_error == 0) {
                        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                        if ($ext =="xlsx" || $ext == "xls" || $ext =="XLSX" || $ext == "XLS") {
                            # access file size is 1 Megabytes (MB)
                            if ($file['size'] <= 1048576) {
                                # for excel
                                $fileName = "temporary.".$ext;
                                $tempPath = $uploadPath .DS. $fileName;
                                if (move_uploaded_file($file_loc, $tempPath)) {
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
                                            $rowData = $objWorksheet->rangeToArray('A' . $row . ':' . 'G' . $row, null, true, false);
                                            $worksheets[] = $rowData;
                                        }
                                        
                                        //if ($highestColumn == 'E') {
                                            if (ltrim($worksheets[0][0][0]) == '会計年度/月'
                                                && ltrim($worksheets[0][0][1]) == '部署コード'
                                                && ltrim($worksheets[0][0][2]) == '勘定コード'
                                                && ltrim($worksheets[0][0][3]) == '国内通貨での金額'
                                                && ltrim($worksheets[0][0][4]) == '取引検索キー'
                                                && ltrim($worksheets[0][0][5]) == '物流Index No.'
                                                && ltrim($worksheets[0][0][6]) == 'パートナ'
                                                
                                            ) {
                                                $header_flag = true;
                                            } else {
                                                $header_flag = false;

                                                $errorMsg = parent::getErrorMsg('SE022');
                                                $this->Flash->set($errorMsg, array('key'=>'ImportError'));
                                                $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                                            }
                                        // } else {
                                        //     $errorMsg = parent::getErrorMsg('SE022');
                                        //     $this->Flash->set($errorMsg, array('key'=>'ImportError'));

                                        //     CakeLog::write('debug', ' Your Column is between A to E. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                        //     $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                                        // }

                                        $regLogisticNo = array();
                                        $a = 0;
                                        for ($row = 2; $row <= $highestRow; ++$row) {
                                            $fiscal_ym = $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();

                                            $account_code = $objWorksheet->getCellByColumnAndRow(2, $row)->getFormattedValue();
                                            $amount = $objWorksheet->getCellByColumnAndRow(3, $row)->getValue();
                                            $transaction_key = $objWorksheet->getCellByColumnAndRow(4, $row)->getValue();
                                            
                                            #add ayezarnikyaw(4_June_2020)
                                            if (trim($transaction_key, " ") == '税還付') {
                                                
                                                #Added by Pan Ei Phyo (20201009)
                                                $ba_code = Setting::TAX_REFUND_BA; //ba code for 全社勘定（税還付）
                                            } else {
                                                $ba_code = $objWorksheet->getCellByColumnAndRow(1, $row)->getValue();
                                            }
                                            #end add ayezarnikyaw(4_June_2020)
                                            $head_id = $ba_data[$ba_code];
                                            
                                            $removeComma = str_replace(',', '', $amount);
                                            $amount = $removeComma * (-1);
                                            // if account code is for sale, check logistic no and name
                                            // if not, show popup to register
                                            if (in_array($account_code, $sale_account_codes)) {
                                                $fiscal_year = explode('/', $fiscal_ym);
                                                $trans_key_err = ("<b>").$transaction_key.("</b>").__(" の ").__("年度")." ".$fiscal_year[0].__(" と 事業領域 ").$ba_code;
                                                if ((!in_array($trans_key_err, $regLogisticNo)) && trim($transaction_key, " ") != '税還付' && $transaction_key != '' && (!in_array($transaction_key, $logistic_no[$fiscal_year[0]][$ba_code]) && !in_array($transaction_key, $logistic_name[$fiscal_year[0]][$ba_code]))) {
                                                    // $regLogisticNo[] = $transaction_key;
                                                    $regLogisticNo[] = $trans_key_err;
                                                }
                                            }
                                            if (!empty($fiscal_ym) && !empty($ba_code) && !empty($account_code) && (!empty($amount) || $amount==0)) {
                                                $fiscal_ym = str_replace('/', '-', $fiscal_ym);
                                                $fiscal_ym = date("Y-m", strtotime($fiscal_ym. "last day of + ".$adjust_month." months"));
                                                
                                                if ($fiscal_ym>=$start_term && $fiscal_ym<=$end_term) {
                                                    # check duplicated data in tbl_actualresult
                                                    $acc_result_data = $this->BrmActualResult->find('all', array(
                                                        'conditions'=>array(
                                                            'hlayer_code'=>$head_id,
                                                            'account_code'=>$account_code,
                                                            'target_month'=>$fiscal_ym,
                                                            'layer_code'=>$ba_code,
                                                            'amount' => $amount,
                                                            'transaction_key' => $transaction_key,
                                                        )
                                                    ));

                                                    if (empty($acc_result_data)) {

                                                        # validation for excel field
                                                        $ba_code_true = false;
                                                        $account_code_true = false;
                                                        $amount_true = false;
                                                        
                                                        $ba_code_length = mb_strlen(trim($ba_code));
                                                        if ($ba_code_length <= '5') {
                                                            $ba_code_true = true;
                                                            $account_code_length = mb_strlen(trim($account_code));
                                                            if (($account_code_length == 10) && (preg_match('/^\d+$/', $account_code))) {
                                                                $account_code_true = true;
                                                                if (preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/', $removeComma)) {
                                                                    $amount_true = true;
                                                                } else {
                                                                    CakeLog::write('debug', 'Invalid amount format error occur '.$amount.' will be "float Format", at col D and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                                                    $account_code_true = false;
                                                                    global $ERROR_VALUE;
                                                                    $ERROR_VALUE = "D";
                                                                }
                                                            } else {
                                                                CakeLog::write('debug', ' Invalid account_code format error occur. '.$account_code.' will be string length ==10, at col C and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                                                $account_code_true = false;
                                                                global $ERROR_VALUE;
                                                                $ERROR_VALUE = "C";
                                                            }
                                                        } else {
                                                            CakeLog::write('debug', ' Invalid ba_code format error occur. '.$ba_code.' will be string length <=4, at col B and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                                            $ba_code_true = false;
                                                            global $ERROR_VALUE;
                                                            $ERROR_VALUE = "B";
                                                        }
                                                        
                                                        if ($ba_code_true && $account_code_true && $amount_true) {
                                                            if (isset($account_codes[$account_code]) && isset($ba_data[$ba_code])) {
                                                                $a++;
                                                                
                                                                $backupList[$a]['head_id'][] = $head_id;
                                                                $backupList[$a]['fiscal_ym'][] = $fiscal_ym;

                                                                $data[] = array(
                                                                    "hlayer_code" => $head_id,
                                                                    "target_month"=> $fiscal_ym,
                                                                    "layer_code"=> $ba_code,
                                                                    "account_code"=> $account_code,
                                                                    "transaction_key"=> $transaction_key,
                                                                    "submission_deadline_date"=> $submission_deadline_date,
                                                                    "amount"=> $amount,
                                                                    "currency" => "JPY",
                                                                    //"flag"=> "1",
                                                                    "created_by"=> $login_id,
                                                                    "updated_by"=> $login_id
                                                                    
                                                                );
                                                            } else {
                                                                if (!isset($account_codes[$account_code])) {
                                                                    if (!in_array($account_code, $checkacc, true)) {
                                                                        array_push($checkacc, $account_code);
                                                                    }
                                                                    $this->Session->write('REGACC', 'REGACC'); # to read index()
                                                                }
                                                                if (!isset($ba_data[$ba_code])) {
                                                                    if (!in_array($ba_code, $checkba, true)) {
                                                                        array_push($checkba, $ba_code);
                                                                    }
                                                                    $this->Session->write('REGBA', 'REGBA'); # to read index()
                                                                }
                                                            }
                                                        } else {
                                                            
                                                            global $ERROR_VALUE;
                                                            $param_result = array();
                                                            $param_result['row']  = '( '.$row.' )';
                                                            $param_result['col'] = '( '.$ERROR_VALUE.' )';

                                                            $errorMsg = parent::getErrorMsg('SE023', $param_result);
                                                            $this->Flash->set($errorMsg, array('key'=>'ImportError'));

                                                            $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                                                        }
                                                    } else {
                                                        $skipDupData[] = (" <b>").$row.("</b> ").__("行目のデータは重複しています。");
                                                                                                                        
                                                        $this->Session->write('DUPLICATEDATA', 'DUPLICATEDATA');
                                                    }
                                                } else {
                                                    $errorMsg = parent::getErrorMsg('SE074');
                                                    $this->Flash->set($errorMsg, array('key'=>'ImportError'));

                                                    $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                                                }
                                            } else {
                                                if (empty($fiscal_ym) && empty($ba_code) && empty($account_code) && (!empty($amount) || $amount==0)) {
                                                } else {
                                                    $errorMsg = parent::getErrorMsg('SE075', $row);
                                                    $this->Flash->set($errorMsg, array('key'=>'ImportError'));

                                                    $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                                                }
                                            }
                                        }
                                        
                                        if (count($regLogisticNo) > 0) {
                                            $this->Session->write('REGLOGISTICNO', $regLogisticNo); # to read index()
                                        }
                                        
                                        if (!empty($data)) {
                                            //echo '<pre>';print_r($data);echo '</pre>';exit;
                                            $attachDB = $this->BrmActualResult->getDataSource();
                                            
                                            try {
                                                $attachDB->begin();

                                                $save_all = $this->BrmActualResult->saveAll($data);
                                                #add ayezarnikyaw(2_June_2020)
                                                // $summary_data = $this->BrmActualResult->find('all',array(
                                                // 				'fields' => array('BrmActualResult.head_dept_id','BrmActualResult.target_month','BrmActualResult.ba_code','BrmActualResult.account_code','BrmActualResult.transaction_key','BrmActualResult.destination_code','BrmActualResult.submission_deadline_date','sum(amount) as amount','BrmActualResult.updated_date'),
                                                // 				'group' => array('ba_code','account_code','target_month','submission_deadline_date','transaction_key','destination_code')
                                                // 				)
                                                // 			);
                                                            
                                                // for($i=0;$i<count($summary_data);$i++){
                                                // 	$summary_save[] = array(
                                                // 		'id'		   => $i+1,
                                                // 		'head_dept_id' => $summary_data[$i]
                                                //         				  ['BrmActualResult']['head_dept_id'],
                                                //         'target_month' => $summary_data[$i]
                                                //         				  ['BrmActualResult']['target_month'],
                                                //         'ba_code' 	   => $summary_data[$i]
                                                //         				  ['BrmActualResult']['ba_code'],
                                                //         'account_code' => $summary_data[$i]
                                                //         				  ['BrmActualResult']['account_code'],
                                                //         'transaction_key' => $summary_data[$i]
                                                //         				  ['BrmActualResult']['transaction_key'],
                                                // 		'destination_code'=> $summary_data[$i]
                                                // 						  ['BrmActualResult']['transaction_key'],
                                                //         'submission_deadline_date' => $summary_data[$i]
                                                //         				  ['BrmActualResult']['submission_deadline_date'],
                                                //         'amount' 	   => $summary_data[$i]
                                                //         				  ['0']['amount'],
                                                //         'updated_date' => $summary_data[$i]
                                                //         				  ['BrmActualResult']['updated_date'],

                                                //     );
                                                     
                                                // }
                                                
                                                $this->loadModel('BrmActualResultSummary');
                                                $count_summary_data = $this->BrmActualResultSummary->find('count');

                                                if ($count_summary_data >=1) {
                                                    $this->BrmActualResultSummary->query('TRUNCATE TABLE brm_actual_result_summary');
                                                }
                                                $this->BrmActualResultSummary->saveActualResultSummary();
                                                ##end add ayezarnikyaw(2_June_2020)

                                                $attachDB->commit();
                                            } catch (Exception $e) {
                                                $attachDB->rollback();

                                                $errorMsg = parent::getErrorMsg('SE015');
                                                $this->Flash->set($errorMsg, array('key'=>'ImportError'));

                                                CakeLog::write('debug', ' cannot saving process into database. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                                            }
                                        }

                                        if (!empty($checkba)) {
                                            $this->Session->write('CHECKBA', $checkba);
                                        }
                                        if (!empty($checkacc)) {
                                            $this->Session->write('CHECKACC', $checkacc);
                                        }
                                        if (!empty($skipDupData)) {
                                            $this->Session->write('SKIPDUPDATA', $skipDupData);
                                        }
                                        
                                        if ($save_all == 1 && $a > 0) {
                                            if (!empty($backupList)) {
                                                $this->ToSaveBackupFile(array_unique($backupList, SORT_REGULAR));
                                            }
                                            
                                            $successMsg = parent::getSuccessMsg('SS009', $a);
                                            
                                            $this->Flash->set($successMsg, array("key"=>"ImportSuccess"));

                                            $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                                        } else {
                                            $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                                        }
                                    }
                                    #when excel file is protect password show error message
                                    CakeLog::write('debug', 'Excel is protect password In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                    $errorMsg = parent::getErrorMsg('SE076');
                                    $this->Flash->set($errorMsg, array('key'=>'ImportError'));

                                    $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                                } else {
                                    CakeLog::write('debug', 'cannot upload file under tmp. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                    $errorMsg = parent::getErrorMsg('SE015');
                                    $this->Flash->set($errorMsg, array('key'=>'ImportError'));
                                }
                            } else {
                                CakeLog::write('debug', 'file size over 1MB. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                $errorMsg = parent::getErrorMsg('SE020');
                                $this->Flash->set($errorMsg, array('key'=>'ImportError'));

                                $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                            }
                        } else {
                            $errorMsg = parent::getErrorMsg('SE013', $ext);
                            $this->Flash->set($errorMsg, array('key'=>'ImportError'));

                            CakeLog::write('debug', ' File Extension is Invalid. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                            $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                        }
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE015');
                    $this->Flash->set($errorMsg, array('key'=>'ImportError'));

                    CakeLog::write('debug', ' File Format is Invalid. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                    $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
                }
            } catch (Expression $e) {
                $errorMsg = parent::getErrorMsg('SE015');
                $this->Flash->set($errorMsg, array('key'=>'ImportError'));
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
            }
        } else {
            $this->redirect(array('controller' => 'BrmActualResult','action'=>'index'));
        }
    }

    /**
    * CheckBA method
    *
    * @author Khin Hnin Myo (20200218)
    * @return void
    */
    public function CheckBA()
    {
        // $this->request->allowMethod('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);

        $check_ba = $this->Session->read('CHECKBA');
        $this->Session->delete('CHECKBA');
        
        echo json_encode($check_ba);
    }

    /**
    * CheckAcc method
    *
    * @author Khin Hnin Myo (20200218)
    * @return void
    */
    public function CheckAcc()
    {
        // $this->request->allowMethod('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);

        $check_acc = $this->Session->read('CHECKACC');
        $this->Session->delete('CHECKACC');
        
        echo json_encode($check_acc);
    }

    /**
    * SkipSameData method
    *
    * @author Khin Hnin Myo (20200218)
    * @return void
    */
    public function SkipSameData()
    {
        // $this->request->allowMethod('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        
        $dup_data = $this->Session->read('SKIPDUPDATA');
        $this->Session->delete('SKIPDUPDATA');
        
        echo json_encode($dup_data);
    }


    /**
     * delete for tbl_actual_result data  method
     *
     * @author Ei Thandar Kyaw (20221014)
     * @throws NotFoundException
     * @param string $hid
     * @return void
     */

    public function DeleteResultData()
    {
        if ($this->request->is('post')) {
            $page_no =  $this->request->data('hid_page_no');

            #get result id
            $get_id  = $this->request->data('hid_id');

            $login_id= $this->Session->read('LOGIN_ID');

            $delete_data = $this->BrmActualResult->find(
                'first',
                array(
                    'conditions' => array('BrmActualResult.id' => $get_id),
                    )
            );


            if (!empty($delete_data)) {
                
                $target_month = $delete_data['BrmActualResult']['target_month'];
                $ba_code =  $delete_data['BrmActualResult']['layer_code'];
                $account_code = $delete_data['BrmActualResult']['account_code'];
                $s_deadline_date = $delete_data['BrmActualResult']['submission_deadline_date'];
                $delete_amount = $delete_data['BrmActualResult']['amount'];

                $this->loadModel('BrmActualResultSummary');
                $update_summary = $this->BrmActualResultSummary->find('first', array(
                        'conditions' => array('target_month' => $target_month ,
                                                'layer_code' => $ba_code ,'account_code' => $account_code ,
                                                'submission_deadline_date' => $s_deadline_date)
                    ));

                $update_id = $update_summary['BrmActualResultSummary']['id'];
                $update_amount = $update_summary['BrmActualResultSummary']['amount']-$delete_amount;

                $update_data = array(
                        'id' 			=> $update_id,
                        'amount' 		=> $update_amount,
                        'updated_date'  => date("Y-m-d H:i:s")
                    );

                if ($update_amount == 0) {
                    $this->BrmActualResultSummary->delete($update_id);
                } else {
                    $this->BrmActualResultSummary->save($update_data);
                }
                #delete data from tbl_actual_result
                $this->BrmActualResult->delete($get_id);
                $successMsg = parent::getSuccessMsg('SS003');
                $this->Flash->set($successMsg, array("key"=>"ImportSuccess"));
            } else {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array("key" =>"ImportError"));
            }
            $this->redirect(array('controller' => 'BrmActualResult','action' => 'index/'.$page_no));
        }
    }

    /**
    * BudgetResultDifferenceMailSending method
    *
    * @author Ei Thandar Kyaw (20221014)
    * @return void
    */
    public function BRDMailSending()
    {
        
        if ($this->request->is('post')) {
            $login_user 	= $this->Session->read('LOGIN_USER');
            $term_id 		= $this->Session->read('TERM_ID');
            $target_month 	= $this->Session->read('TARGETMONTH');
            $hq_id		 	= $this->Session->read('HEAD_DEPT_ID');
            $term_name		= $this->Session->read('TERM_NAME');
            $headquarter 	= $this->Session->read('HEAD_DEPT_NAME');

            #Get deadline date
            $submission_date = $this->BrmActualResult->find('first', array(
                        'fields' => 'submission_deadline_date',
                        'conditions' => array(
                            'target_month' => $target_month,
                        )
                    ));
            $sub_date = $submission_date['BrmActualResult']['submission_deadline_date'];
            $day = date("w", strtotime($sub_date));
            $dys = array("日","月","火","水","木","金","土");
            $deadline_date = date("n\月d\日（".$dys[$day]."）", strtotime($sub_date));
            if($_POST['mailSend']) {
                $mail_template = 'common';
                $mail['subject']        = $_POST['mailSubj'];
                $mail['template_title'] = $_POST['mailTitle'];
                $mail['template_body']  = $_POST['mailBody'];
                $to_email  = $_POST['toEmail'];
                $cc_email  = $_POST['ccEmail']; 
                $bcc_email = $_POST['bccEmail']; 
                
                $url = '/BrmBudgetResultDifference?target_month='.$target_month.'&term_id='.$term_id.'&term_name='.$term_name;
                $toEmail = parent::formatMailInput($to_email);
                $ccEmail = parent::formatMailInput($cc_email);
                $bccEmail = parent::formatMailInput($bcc_email);
                $sentMail = parent::sendEmailP3($target_month, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                if ($sendMail["error"]) {
                    $invalid_email = parent::getErrorMsg('SE042');
                    $this->Flash->set($invalid_email, array('key'=>'ImportError'));
                } else {

                    $successMsg = parent::getSuccessMsg("SS018");
                    $this->Flash->set($successMsg, array('key'=>'ImportSuccess'));
                } 
            }
            $this->redirect(array('controller'=>'BrmActualResult','action'=>'index'));      
        }
    }

    /**
    * MonthlyReportMailSending method
    *
    * @author Ei Thandar Kyaw (20221014)
    * @return void
    */
    public function MRMailSending()
    {
        if ($this->request->is('post')) {
            $login_user 	= $this->Session->read('LOGIN_USER');
            $term_id 		= $this->Session->read('TERM_ID');
            $target_month 	= $this->Session->read('TARGETMONTH');
            $hq_id		 	= $this->Session->read('HEAD_DEPT_ID');
            $term_name		= $this->Session->read('TERM_NAME');
            $headquarter 	= $this->Session->read('HEAD_DEPT_NAME');

            #Get deadline date
            $submission_date = $this->BrmActualResult->find('first', array(
                        'fields' => 'submission_deadline_date',
                        'conditions' => array(
                            'target_month' => $target_month,
                        )
                    ));
            $sub_date = $submission_date['BrmActualResult']['submission_deadline_date'];
            $day = date("w", strtotime($sub_date));
            $dys = array("日","月","火","水","木","金","土");
            $deadline_date = date("n\月d\日（".$dys[$day]."）", strtotime($sub_date));
            if($_POST['mailSend']) {
                $mail_template = 'common';
                $mail['subject']        = $_POST['mailSubj'];
                $mail['template_title'] = $_POST['mailTitle'];
                $mail['template_body']  = $_POST['mailBody'];
                $to_email  = $_POST['toEmail'];
                $cc_email  = $_POST['ccEmail']; 
                $bcc_email = $_POST['bccEmail']; 
                
                $url = '/BrmMonthlyReport?target_month='.$target_month.'&term_id='.$term_id.'&term_name='.$term_name;
                $toEmail = parent::formatMailInput($to_email);
                $ccEmail = parent::formatMailInput($cc_email);
                $bccEmail = parent::formatMailInput($bcc_email);
                $sentMail = parent::sendEmailP3($target_month, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                if ($sendMail["error"]) {
                    $invalid_email = parent::getErrorMsg('SE042');
                    $this->Flash->set($invalid_email, array('key'=>'ImportError'));
                } else {

                    $successMsg = parent::getSuccessMsg("SS018");
                    $this->Flash->set($successMsg, array('key'=>'ImportSuccess'));
                } 
            }
            $this->redirect(array('controller'=>'BrmActualResult','action'=>'index'));
        }
    }
    
     /**
    * get mail list 
    *
    * @author Ei Thandar Kyaw (20221014)
    * @return void
    */
    public function getMailLists() {
        #only allow ajax request
        $language = $this->Session->read('Config.language');
        parent::checkAjaxRequest($this);
        $Common     = New CommonController();
        $period     = $this->Session->read('BrmTermSelections_PERIOD_DATE');
        $layer_name    = $this->Session->read('BUDGET_BA_CODE');
        $layer_code    = $_POST['layer_code']; //($_POST['layer_code'])? $_POST['layer_code'] : '';
        $page       = $_POST['page'];
        $function   = $_POST['function'];
        
        $mails = $Common->getMailList($layer_code, $page, $function, $language, $layer_name, $period, Setting::LAYER_SETTING['SapSelections']);
       
        return json_encode($mails);
    }
    /**
    * Search method
    *
    * @author Ei Thandar Kyaw (20221014)
    * @return void
    */
    public function Search()
    {
        $this->layout = 'phase_3_menu';

        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);

        $term_id = $this->Session->read('TERM_ID');
        $hq_id = $this->Session->read('HEAD_DEPT_CODE');
        $code_ba = $this->request->data('b_code');
        $code_acc = $this->request->data('acc_code');
        $month_t = $this->request->data('t_month');
        $logistic_index = $this->request->data('logistic_index');


        $conditions = array();
        if ($code_ba != null || $code_ba != '') {
            $conditions["BrmActualResult.layer_code"] = $code_ba;
        }
        if ($code_acc != null || $code_acc != '') {
            $conditions["BrmActualResult.account_code"] = $code_acc;
        }
        if ($month_t != null || $month_t != '') {
            $conditions["BrmActualResult.target_month"] = $month_t;
        }
        if($hq_id != null || $hq_id != '') {
        	$conditions["BrmActualResult.hlayer_code"] = $hq_id;
        }
        if ($logistic_index != null || $logistic_index != '') {
            $conditions["BrmActualResult.transaction_key"] = $logistic_index;
        }
        if ($this->Session->read('Config.language') == 'eng') {
            $lang_name = 'en';
        } else {
            $lang_name = 'jp';
        }
        $today = date('Y-m-d');
        $topLayer = Setting::LAYER_SETTING['topLayer'];
        $bottomLayer = Setting::LAYER_SETTING['bottomLayer'];
        $type_order = $this->LayerType->find('first', array(
            'conditions' => array('LayerType.flag' => '1', 'type_order' => $topLayer),
            'fields' => array('LayerType.id' ,'LayerType.name_jp' ,'LayerType.name_en', 'type_order'), 
        ));
        $bottomLayerName = $this->LayerType->find('first', array(
            'conditions' => array('LayerType.flag' => '1', 'type_order' => $bottomLayer),
            'fields' => array('LayerType.id' ,'LayerType.name_jp' ,'LayerType.name_en', 'type_order'), 
        ));
        $conditions1['Layer.type_order'] = $topLayer;
        $conditions1['Layer.flag'] = 1;
        //$conditions1['Layer.to_date >='] = $today; #filter exprie data
        $conditions1[] = 'Layer.id IN (select max(layers.id) from layers group by layers.layer_code)';
        $l_name = $this->Layer->find('list', array(
            'fields' => array( 'Layer.layer_code', 'Layer.name_jp'),
            'conditions' => $conditions1,
            'order' 	 => array('layer_code ASC')
            
        ));
        
        $actual_data = $this->BrmActualResult->find('all', array(
                            'conditions' => $conditions,
                            'fields' => array(
                                          'BrmActualResult.*','FORMAT(sum(amount),0) as amount'),
                            'group' => array('account_code','target_month','transaction_key','destination_code'),
                            'order' => 'BrmActualResult.id DESC'));
        foreach($actual_data as $key=>$value){
            $actual_data[$key]['LayerName']['top'] = $type_order['LayerType']['name_'.$lang_name];
            $actual_data[$key]['LayerName']['bottom'] = $bottomLayerName['LayerType']['name_'.$lang_name];
            $actual_data[$key]['layer']['name_jp'] = $l_name[$value['BrmActualResult']['hlayer_code']] ? $l_name[$value['BrmActualResult']['hlayer_code']] : '';
        }
        
        $query_count = count($actual_data);

        $response = array(

                'actual_data' => $actual_data,
                'query_count' => $query_count
                                
            );
        echo json_encode($response);
    }
    /**
    * Delete All method
    *
    * @author Ei Thandar Kyaw (20221014)
    * @return void
    */
    public function DeleteAll()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = true;
        #only allow ajax request
        parent::checkAjaxRequest($this);

        $term_id = $this->Session->read('TERM_ID');
        $topLayer = explode(',', $this->Session->read('TOP_LAYER'));
        $hq_id = $topLayer[1];
        $code_ba =  $this->request->data['b_code'];
        $code_acc =  $this->request->data['acc_code'];
        $month_t =  $this->request->data['t_month'];

        $conditions = array();

        if ($hq_id != null || $hq_id != '') {
            $conditions["hlayer_code"] = $hq_id;
        }
        if ($code_ba != null || $code_ba != '') {
            $conditions["layer_code"] = $code_ba;
        }
        if ($code_acc != null || $code_acc != '') {
            $conditions["account_code"] = $code_acc;
        }
        if ($month_t != null || $month_t != '') {
            $conditions["target_month"] = $month_t;
        }
        //print_r($conditions);exit;
        // exclusion
        $datas = $this->BrmActualResult->find('all', array(
                    'conditions' => $conditions));
        
        try {
            if (!empty($datas)) {
                $this->BrmActualResult->deleteAll($conditions, false);
                $this->loadModel('BrmActualResultSummary');
                $this->BrmActualResultSummary->deleteAll($conditions, false);

                $successMsg = parent::getSuccessMsg('SS003');
                $this->Flash->set($successMsg, array("key"=>"ImportSuccess"));
                return $this->redirect(array('controller' => '', 'action' => 'index'));
            } else {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array("key"=>"ImportError"));
                return $this->redirect(array('controller' => '', 'action' => 'index'));
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'BrmActualResult', 'action' => 'index'));
        }
    }
    /**
     * search logistic index for autocomplete form,
     * @author Hein Htet Ko
     * @return data
     *
     */
    public function autoCompleteLogistic()
    {
        // $this->autoRender = false;
        // $this->request->allowMethod('ajax');
        #only allow ajax request
        parent::checkAjaxRequest($this);

        if ($this->request->is('post')) {
            $ba_code = $this->request->data['b_code'];
            $acc_code = $this->request->data['acc_code'];
            $target_month = $this->request->data['t_month'];
            $search_key	= $this->request->data['searchValue'];

            $condition_arr = array();
            if (!empty($ba_code)) {
                $condition_arr['ba_code'] = $ba_code;
            }
            if (!empty($acc_code)) {
                $condition_arr['account_code'] = $acc_code;
            }
            if (!empty($target_month)) {
                $condition_arr['target_month'] = $target_month;
            }
            if (!empty($search_key)) {
                $condition_arr['transaction_key LIKE'] = $search_key.'%';
            }
            
            $logistic_data = array_column(array_column($this->BrmActualResult->find('all', array(
                'fields' => 'DISTINCT (transaction_key)',
                'conditions' => $condition_arr
            )), 'BrmActualResult'), 'transaction_key');
            
            if (!empty($logistic_data)) {
                return json_encode($logistic_data);
            } else {
                return false;
            }
        }
    }

    /**
    * To save tbl_backup_file
    *
    * @author Ei Thandar Kyaw (20221014)
    * @param $hq_arr_list
    */

    #queue according to term, type, headquarters to backup
    public function ToSaveBackupFile($backupList)
    {
        $term_id = $this->Session->read('TERM_ID');
        // $budget_term = $this->Session->read('TERM_NAME');
        
        $type_arr = array('03','04');

        $term_data = $this->BrmTerm->find('first', array(
                'conditions'=>array(
                    'BrmTerm.id'=>$term_id
                )
            ));
        
        $budget_term = $term_data['BrmTerm']['term_name'];

        if (empty($budget_term) || $budget_term == '') {
            $budget_term = $term_data['BrmTerm']['budget_year'].'~'.$term_data['BrmTerm']['budget_end_year'];
        }

        $backup_files = [];
        $topLayer = Setting::LAYER_SETTING['topLayer'];
        foreach ($type_arr as $type) {
            foreach ($backupList as $backup_value) {
                $head_dept 	 = $backup_value['head_id'][0];

                #Get All Headquarter list
                $hq_name = $this->Layer->find('first', array(
                                'fields' => 'Layer.name_jp',
                                'conditions' => array(
                                    array('Layer.flag' => 1,'Layer.layer_code' => $head_dept)
                                    
                                )
                            ))['Layer']['name_jp'];
                
                $fiscal_ym   = (new DateTime($backup_value['fiscal_ym'][0]))->format('Y-m-d');
                #get preivous month
                $fiscal_ym   = date('Y-m-d', strtotime($fiscal_ym.'first day of -1 month'));
                
                $queued_row = $this->BrmBackupFile->find('first', array(
                                        'fields' => array('BrmBackupFile.id', 'BrmBackupFile.status'),
                                        'conditions' => array(
                                                'term_id' => $term_id,
                                                'file_type' => $type,
                                                'hlayer_code' => $head_dept,
                                                'start_month' => $fiscal_ym,
                                                'end_month' => $fiscal_ym,
                                                'status' => '0',
                                                'flag' => '1'),
                                        'order' => array('BrmBackupFile.id')
                                    ));
                #read user id
                $login_id = $this->Session->read('LOGIN_ID');

                if (empty($queued_row)) {
                    $backup_files[] = array(
                        'term_id' => $term_id,
                        'term_name' => $budget_term,
                        'file_type' => $type,
                        //'head_dept_id' => $head_dept,
                        //'head_dept_name' => $hq_name,
                        'hlayer_code' => $head_dept,
                        'hlayer_name' => $hq_name,
                        'start_month' => $fiscal_ym,
                        'end_month' => $fiscal_ym,
                        'status' => '0',
                        'flag' => 1,
                        'created_by' => $login_id,
                        'updated_by' => $login_id,
                    );
                }
            }
        }
        //echo '<pre>***';print_r($backup_files);echo '</pre>';exit;
        $this->BrmBackupFile->saveAll($backup_files);
        //$this->loadModel('Queue.QueuedTask');
        //$this->QueuedTask->createJob('BackupFile', 2 * MINUTE);

        return true;
    }
}
