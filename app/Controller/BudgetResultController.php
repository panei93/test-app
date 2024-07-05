<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
define('ADMINPERMIT', ['システム 管理者']);
// define('UPLOAD_FILEPATH', ROOT); //server
// define('UPLOAD_PATH', 'app'.DS.'temp');

class BudgetResultController extends AppController
{

    /**
     * Components
     *
     * @var array
     */
    public $uses = array('LayerType', 'Layer', 'Account', 'AccountType', 'Rexchange', 'RtaxFee', 'Budget', 'BudgetHyoka', 'BudgetComp', 'BudgetSng', 'BudgetPoint', 'SettlementTerm', 'EmpNum', 'Menu', 'AccountSetting', 'BudgetData', 'BuApprovedLog', 'InterestCost', 'LaborCostDetail', 'Role');
    public $components = array('Paginator','PhpExcel.PhpExcel', 'Session', 'Flash');

    /**
     * Check Session before render page
     * @author Khin Hnin Myo
     * @return void
     */
    public function beforeFilter() {
        parent::CheckSession();
        parent::checkBuUrlSession($this->name);#checkurlsession

        $permissions = $this->getPermissionLayerLists();
        $read_permit = $permissions['index']['layer'];
        $next_layers = $permissions['index']['next_layers'][Setting::BU_BUDGET_MAX_LAYER[0]+1];
        $_SESSION['BU_LAYER_LISTS'] = $read_permit;
        $_SESSION['GP_LAYER_LISTS'] = $next_layers;
    }
    
    /**
     * index method
     *
     * @author Khin Hnin Myo (20200902)
     * @return void
     */
    public function index() {
        $this->layout = 'buanalysis';
        $language = $_SESSION['Config']['language'];
        $name = ($language == 'eng')? 'name_en' : 'name_jp';
        $req_data = $this->request->data;

        $menu_id = Setting::MENU_ID_LIST['BudgetResult'];
        $_SESSION['BR_MENU_ID'] = $menu_id;

        #from session (br's save fun: and another form)
        $term_id = $_SESSION['BU_TERM_ID'];
        $current_year = $_SESSION['BudgetTargetYear'];#target year form selected term
        
        #from req data
        $layerlist = [];
        $from_save_session = $_SESSION['LAYERLIST'];     
        $_SESSION['LAYERLIST'] = '';
        
        if(empty($this->request->data)) {
            $bu_code = $_SESSION['SELECTED_BU'];
            $gp_code = $_SESSION['SELECTED_GROUP'];
            $layerlist = array($bu_code, $gp_code);
            if($from_save_session) {
                $layerlist = [];
                $layerlist = $from_save_session;
            }
        }else {
            $layerlist = (!empty($req_data['sel_name'])) ? $req_data['sel_name'] : $_SESSION['SELECTED_LAYRES'];
            $_SESSION['SELECTED_LAYRES'] = '';
        }
        
        $layer_code = end(array_filter($layerlist));
        $select_code = array_values($layerlist)[0];

        $layers_list = $this->RetriveLayers($layerlist);
        
        if(empty(array_filter(array_values($layers_list)))) {
            $returnMsg['error'] = true;
            $returnMsg['errorMsg'] = parent::getErrorMsg('SE153');
        }else {
            $returnMsg['error'] = false;
            $returnMsg['errorMsg'] = '';
            
            $form = (!empty($this->request->data('form'))) ? $this->request->data('form') : $this->Session->read('FORM');
            $item_1 = (!empty($this->request->data('item_1'))) ? $this->request->data('item_1') : $this->Session->read('ITEM1');
            $item_2 = (!empty($this->request->data('item_2'))) ? $this->request->data('item_2') : $this->Session->read('ITEM2');

            
            $this->Session->delete('FORM');
            $this->Session->delete('ITEM1');
            $this->Session->delete('ITEM2');
            $layer_types = array_keys($layers_list);
            $yr_list = $this->YearList($current_year);
            $next_years = ($yr_list['hidden_title'] == '') ? array_slice($yr_list, 2) : array_slice($yr_list, 3);
            #exchange and tax
            $exchanges = $this->getTaxExList('Rexchange', $yr_list);
            $tax_fees = $this->getTaxExList('RtaxFee', $yr_list);
            
            $account_list = $this->CalculateBudget($term_id, $layer_code, $yr_list, $current_year, $tax_fees);
            $calcu_arr = $account_list[0];
            $budget_accounts = $account_list[1];
            $excel_formula = $account_list[2];
            $factor_formula = $account_list[3];
            $factor_calulate = $account_list[4];# for jquery calculation
            $factor_excel = $account_list[5];# for excel calculation
            
            $cmt_title = array('本ビジネス概要・取引背景・経緯', 'BUにおける本ビジネスの位置づけ、目指す姿及び戦略', '当社の機能');
            $budget_hyokas = $this->BudgetHyoka($term_id, $layer_code, $current_year);
            
            $budget_comps = $this->CalculateBudgetComp($term_id, $layer_code, $current_year);
            
            $budget_sngs = $this->BudgetSng($term_id, $layer_code, $current_year);

            $budget_points = $this->BudgetPoint($term_id, $layer_code, $current_year);

            $settlements = $this->Settlement($term_id, $layer_code, $current_year);

            $employee = $this->EmployeeNum($term_id, $layer_code, $next_years);
            
            $tot_sales_formula = $this->TotalSale($layer_code, $next_years, $current_year);
            $tot_sales_per_person = $this->ReplaceFormula($tot_sales_formula);

            $cache_data = array(
                'term_id' => $term_id,
                'layer_code' => $layer_code,
                'current_year' => $current_year,
                'yr_list' => $yr_list,
                'exchanges' => $exchanges,
                'tax_fees' => $tax_fees,
                'calcu_arr' => $calcu_arr,
                'budget_accounts' => $budget_accounts,
                'cmt_title' => $cmt_title,
                'budget_hyokas' => $budget_hyokas,
                'budget_comps' => $budget_comps,
                'budget_sngs' => $budget_sngs,
                'budget_points' => $budget_points,
                'settlements' => $settlements,
                'employees' => $employee,
                'tot_sales_formula' => $tot_sales_formula,
                'excel_formula' => $excel_formula,
                'factor_excel' => $factor_excel
            );
            
            unset($yr_list[0]);
            
            $sess_selid = $_SESSION['SELECT_IDS'];
            $select_ids = [];
            foreach ($sess_selid as $key => $value) {
                $select_ids[] = '$("#'.explode('/', $value)[0].'").val()';
            }
            $selectedTxt = $this->request->data('selected_txt');
            
            $year_code['target_year'] = $current_year;
            $year_code['layer_code'] = $layerlist[0];

            $manual_budget_year = Setting::LIMIT_YEAR - 1;
            #editable or not
            if(count($sess_selid) == count(array_filter($layerlist))) {
                $input_locked = 'unlocked';
            }else {
                $input_locked = 'locked';
                $one = 0;
                foreach ($sess_selid as $key => $value) {
                    if($one == count(array_filter($layerlist))) {
                        if(empty($layers_list[$value])) {
                            $input_locked = 'unlocked';
                        }
                    }
                    $one ++;
                }
            }

            $_SESSION['LAYER_LISTS'] = $layers_list;
            
            $this->Session->write('INPUT_LOCKED', $input_locked);
            $cache_data['input_locked'] = $input_locked;
            #show/hide/disabled or not
            $btn_arr = $this->ShowBtnList($term_id, $layers_list, $layerlist, $current_year);
            $show_tmp_btn = $btn_arr['save'];
            $show_complete_btn = $btn_arr['approve'];
            $show_app_cancel_btn = $btn_arr['approve_cancel'];
            $cancel_disabled = $btn_arr['cancel_disabled'];
            $form_disabled = $btn_arr['form_disabled'];
            /*$form_disabled = 'form_enabled';
            if($show_app_cancel_btn) $form_disabled = 'form_disabled';*/

            $cache_data['form_disabled'] = $form_disabled;

            $cache_name = 'budget_'.$term_id.'_'.$current_year.'_'.$layer_code;
            Cache::write($cache_name, $cache_data);

            $btn_hide_arr = $btn_arr['btn_hide'];
            $save_hide = $btn_hide_arr['save'];
            $approve_hide = $btn_hide_arr['approve'];
            $cancel_hide = $btn_hide_arr['approve_cancel'];
            #for hyokas table
            $check_role = $this->Role->find('count', array(
                'conditions' => array(
                    'flag' => 1,
                    'id' => $_SESSION['ADMIN_LEVEL_ID'],
                    'role_name' => ADMINPERMIT
                )
            ));
            $show_hyoka_btn = false;
            if($check_role > 0) {
                $show_hyoka_btn = true;
            }
        }
        
        # if refresh the browser, selected layers are lost
        $this->Session->write('SELECTION', 'SET');
        $_SESSION['SELECTED_LAYRES'] = $layerlist;
        $jq_nxt_yrs = array_values($next_years);
        $this->set(compact('layers_list', 'yr_list', 'budget_accounts', 'cmt_title', 'exchanges', 'tax_fees', 'layer_types', 'budget_hyokas', 'budget_comps', 'budget_sngs', 'budget_points', 'calcu_arr', 'current_year', 'layerlist', 'form', 'item_1', 'item_2', 'next_years', 'settlements', 'select_ids', 'emplyee_lists', 'employee', 'tot_sales_formula', 'selectedTxt', 'manual_budget_year', 'tot_sales_per_person', 'input_locked', 'show_complete_btn', 'show_tmp_btn', 'show_app_cancel_btn', 'last_code', 'cancel_disabled', 'returnMsg', 'form_disabled', 'factor_calulate', 'jq_nxt_yrs', 'save_hide', 'approve_hide', 'cancel_hide', 'show_hyoka_btn'));
        $this->render('index');
    }

    /**
     * SaveBudget method
     *
     * @author Khin Hnin Myo
     * @return void
     */
    public function SaveApproveBudget() {
        $this->layout = 'buanalysis';
        if ($this->request->is('post')) {
            $login_id = $this->Session->read('LOGIN_ID');
            $reqData = h($this->request->data);
            $layerlist = $reqData['sel_name'];
            $current_year = $reqData['current_year'];
            $form = $reqData['form'];
            $item_1 = $reqData['item_1'];
            $item_2 = $reqData['item_2'];
            $layer_code = end(array_filter($layerlist));
            $year_code['target_year'] = $current_year;
            $year_code['layer_code'] = $layer_code;

            #session save
            $this->Session->write('LAYERLIST', $layerlist);
            $this->Session->write('FORM', $form);
            $this->Session->write('ITEM1', $item_1);
            $this->Session->write('ITEM2', $item_2);
            
            $unit = Setting::BU_UNIT;
            $layers_list = $_SESSION['LAYER_LISTS'];
            $term_id = $_SESSION['BU_TERM_ID'];
            $btn_arr = $this->ShowBtnList($term_id, $layers_list, $layerlist, $current_year);
            $show_tmp_btn = $btn_arr['save'];
            $show_complete_btn = $btn_arr['approve'];
            $show_app_cancel_btn = $btn_arr['approve_cancel'];
            $cancel_disabled = $btn_arr['cancel_disabled'];
            $form_disabled = $btn_arr['form_disabled'];
            
            // $common_save = $this->CommonSave($login_id, $current_year, $layer_code, $reqData, $unit);

            if(($reqData['btn_name'] == 'btn_save' && $show_tmp_btn) || ($reqData['btn_name'] == 'btn_approve' && $show_complete_btn)) {
                $common_save = $this->CommonSave($login_id, $current_year, $layer_code, $reqData, $unit);

                if (isset($common_save['success']) || $common_save['success']!= '') {
                    $this->Flash->set($common_save['success'], array("key"=>"BRSuccess"));
                } elseif (isset($common_save['error']) || $common_save['error']!= '') {
                    $this->Flash->set($common_save['error'], array("key"=>"BRError"));
                }
            }else if ($show_app_cancel_btn) {
                if($reqData['btn_name'] == "btn_save") {
                    $this->Flash->set(parent::getErrorMsg('SE003'), array("key"=>"BRError"));
                }elseif($reqData['btn_name'] == "btn_approve") {
                    $this->Flash->set(parent::getErrorMsg('SE165'), array("key"=>"BRError"));
                }
            }else {
                if($reqData['btn_name'] == "btn_save") {
                    $this->Flash->set(parent::getErrorMsg('SE003'), array("key"=>"BRError"));
                }elseif($reqData['btn_name'] == "btn_approve") {
                    $this->Flash->set(parent::getErrorMsg('SE169'), array("key"=>"BRError"));
                }
                
            }
            $this->redirect(array(
                'controller' => 'BudgetResult',
                'action' => 'index'
            ));
        } else {
            $this->redirect(array(
                'controller' => 'BudgetResult',
                'action' => 'index'
            ));
        }
    }
    /**
     * AppCancelBudget method
     *
     * @author Khin Hnin Myo
     * @return void
     */
    public function AppCancelBudget() {
        $term_id = $_SESSION['BU_TERM_ID'];
        $login_id = $_SESSION['LOGIN_ID'];
        $reqData = $this->request->data;
        $form = $reqData['form'];
        $item_1 = $reqData['item_1'];
        $item_2 = $reqData['item_2'];
        $layerlist = $reqData['sel_name'];
        $current_year = $reqData['current_year'];
        $layer_code = end(array_filter($layerlist));
        $year_code['target_year'] = $current_year;
        $year_code['layer_code'] = $layer_code;
        #session save
        $this->Session->write('LAYERLIST', $reqData['sel_name']);
        $this->Session->write('FORM', $form);
        $this->Session->write('ITEM1', $item_1);
        $this->Session->write('ITEM2', $item_2);
        $menu_id = $_SESSION['BR_MENU_ID'];
        $layers_list = $_SESSION['LAYER_LISTS'];
        $term_id = $_SESSION['BU_TERM_ID'];
        $btn_arr = $this->ShowBtnList($term_id, $layers_list, $layerlist, $current_year);
        $show_tmp_btn = $btn_arr['save'];
        $show_complete_btn = $btn_arr['approve'];
        $show_app_cancel_btn = $btn_arr['approve_cancel'];
        $cancel_disabled = $btn_arr['cancel_disabled'];
        $form_disabled = $btn_arr['form_disabled'];

        if($btn_arr['approve_cancel'] && !$btn_arr['cancel_disabled']) {
            $save_datas = $this->BuApprovedLog($term_id, $login_id, $reqData, $menu_id);
            try {
                $ApprovedDB = $this->BuApprovedLog->getDataSource();
                $ApprovedDB->begin();
                $this->BuApprovedLog->saveAll($save_datas);
                $ApprovedDB->commit();
                $msgcode = parent::getSuccessMsg('SS034');
                $this->Flash->set($msgcode, array("key"=>"BRSuccess"));
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage().' Data cannot be approved. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msgcode = parent::getErrorMsg('SE155');
                $this->Flash->set($msgcode, array("key"=>"BRError"));
            }
        }else if($btn_arr['approve']) {#already cancel complete
            $msgcode = parent::getErrorMsg('SE166');
            $this->Flash->set($msgcode, array("key"=>"BRError"));
        }else {#not approve cancel state #parent layer completed
                $msgcode = parent::getErrorMsg('SE168');
                $this->Flash->set($msgcode, array("key"=>"BRError"));
        }
        $this->redirect(array(
            'controller' => 'BudgetResult',
            'action' => 'index'
        ));
    }
    /**
     * DownloadBudget method
     *
     * @author Khin Hnin Myo
     * @return void
     */
    public function DownloadBudget() {

        $reqData = h($this->request->data);
        $file_name = '';
        $PHPExcel = $this->PhpExcel;
        $this->DownloadExcel($reqData, $file_name, $PHPExcel);
        $this->render('index');
    }
    /**
     * CommonSave method
     * 
     * @author Khin Hnin Myo
     * @return $returnMsg
     */
    public function CommonSave($login_id, $current_year, $layer_code, $reqData, $unit) {
        $term_id = $_SESSION['BU_TERM_ID'];
        /*foreach ($reqData['rexchanges'] as $year => $exchanges) {
            $each_id = $this->Rexchange->find('list', array(
                'conditions' => array(
                    'target_year' => $year,
                    'flag' => 1
                ),
                'fields' => array('target_year', 'id')
            ))[$year];
            $tmp = [];
            if(!empty($each_id)) {
                $tmp['id'] = $each_id;
            }
            $tmp['target_year'] = $year;
            $tmp['rate'] = str_replace(',', '', $exchanges);
            $tmp['main_currency_code'] = 'YEN';
            $tmp['ex_currency_code'] = 'YEN';
            $tmp['flag'] = 1;
            $budgetSave['Rexchange'][] = $tmp;
        }
        foreach ($reqData['rtaxFees'] as $year => $tax_fees) {
            $each_id = $this->RtaxFee->find('list', array(
                'conditions' => array(
                    'target_year' => $year,
                    'flag' => 1
                ),
                'fields' => array('target_year', 'id')
            ))[$year];
            $tmp = [];
            if(!empty($each_id)) {
                $tmp['id'] = $each_id;
            }
            $tmp['target_year'] = $year;
            $tmp['rate'] = str_replace('%', '', $tax_fees);
            $tmp['flag'] = 1;
            $budgetSave['RtaxFee'][] = $tmp;
        }*/
        if($reqData['input_locked'] == 'unlocked') {
            $data_array = $this->request->data('json_data');
            $save_cmt = json_decode($data_array, true);
            if(!empty($save_cmt)) {
                $reqData['budget'] = [];
                foreach ($save_cmt as $str_datas) {
                    $arr_datas = explode('_', $str_datas);
                    $byear = $arr_datas[0];
                    $bacc_id = $arr_datas[1];
                    $bamt = $arr_datas[2];
                    $reqData['budget'][$byear][$bacc_id] = $bamt;
                }
            }
            if($reqData['process'] == 'merge') {
                $reqData['budget'] = $this->relatedCalculation($reqData);
            }
                
            foreach ($reqData['budget'] as $year => $acc_data) {
                foreach ($acc_data as $acc_id => $amt) {
                    $acc_code = $reqData['budgetAccount'][$current_year][$acc_id];
                    $this->Budget->virtualFields['fields'] = 'CONCAT(Budget.id, "/",Budget.created_by, "/", Budget.created_date)';
                    $each_id = $this->Budget->find('first', array(
                        'conditions' => array(
                            'Budget.bu_term_id' => $term_id,
                            'Budget.target_year' => $year,
                            'Budget.layer_code' => $layer_code,
                            'Budget.account_id' => $acc_id,
                            'Budget.account_code' => $acc_code,
                        ),
                        'fields' => 'fields'
                    ));
                    $tmp = $this->DataUpdated($each_id, 'Budget', $login_id, $year, $layer_code);
                    $tmp['bu_term_id'] = $term_id;
                    $tmp['account_id'] = $acc_id;
                    $tmp['account_code'] = $acc_code;
                    $tmp['amount'] = str_replace(',', '', $amt)*$unit;
                    $tmp['unit'] = $unit;
                    $tmp['flag'] = 1;
                    $tmp['updated_by'] = $login_id;
                    $tmp['updated_date'] = date("Y-m-d H:i:s");
                    if(in_array('dbl_'.$year.'_'.$acc_id, $reqData['dbl_edit_flag'])) {
                        $tmp['dbl_edit_flag'] = true;
                    }
                    $budgetSave['Budget'][] = $tmp;
                }  
            }
        }
        foreach ($reqData['sng_cmt'] as $year => $sng_data) {
            foreach ($sng_data as $sng_no => $cmt_amt) {
                $this->BudgetSng->virtualFields['fields'] = 'CONCAT(id, "/",created_by, "/", created_date)';
                $each_id = $this->BudgetSng->find('first', array(
                    'conditions' => array(
                        'bu_term_id' => $term_id,
                        'target_year' => $year,
                        'layer_code' => $layer_code,
                        'sng_no' => $sng_no
                    ),
                    'fields' => 'fields'
                ));
                $tmp = $this->DataUpdated($each_id, 'BudgetSng', $login_id, $year, $layer_code);
                $tmp['bu_term_id'] = $term_id;
                $tmp['sng_no'] = $sng_no;
                $tmp['sng_cmt'] = $cmt_amt['comment'];
                $tmp['sng_amt'] = str_replace(',', '', $cmt_amt['amount'])*$unit;
                $tmp['unit'] = $unit;
                $tmp['updated_by'] = $login_id;
                $tmp['updated_date'] = date("Y-m-d H:i:s");
                $budgetSave['BudgetSng'][] = $tmp;
            }
        }
        foreach ($reqData['comps'] as $year => $comps_data) {
            foreach ($comps_data as $no => $comps) {
                $tmp = [];
                $tmp['bu_term_id'] = $term_id;
                $tmp['target_year'] = $year;
                $tmp['layer_code'] = $layer_code;
                $tmp['sales_ratio'] = str_replace('%', '', $comps['sales_ratio']);
                $tmp['customer'] = (!empty($comps['customer'])) ? $comps['customer'] : '';
                $tmp['deli_share'] = str_replace('%', '', $comps['deli_share']);
                $tmp['deli_share_change'] = (!empty($comps['deli_share_change'])) ? $comps['deli_share_change'] : 0;
                $tmp['product_name'] = (!empty($comps['product_name'])) ? $comps['product_name'] : '';
                $tmp['industry'] = (!empty($comps['industry'])) ? $comps['industry'] : '';
                $tmp['industry_share'] = str_replace('%', '', $comps['industry_share']);
                $tmp['industry_share_change'] = (!empty($comps['industry_share_change'])) ? $comps['industry_share_change'] : 0;
                $tmp['market_size_change'] = (!empty($comps['market_size_change'])) ? $comps['market_size_change'] : 0;
                $tmp['growth_pot'] = ($tmp['industry_share_change']+$tmp['market_size_change'])+($tmp['industry_share_change']*$tmp['market_size_change']);
                $tmp['created_by'] = $login_id;
                $tmp['updated_by'] = $login_id;
                $tmp['created_date'] = date("Y-m-d H:i:s");
                $tmp['updated_date'] = date("Y-m-d H:i:s");
                $budgetSave['BudgetComp'][] = $tmp;
            }
        }
        $deltmp = [];
        $deltmp['bu_term_id'] = $term_id;
        $deltmp['target_year'] = $current_year;
        $deltmp['layer_code'] = $layer_code;
        $budgetDelete['BudgetComp'][] = $deltmp;
        
        #$reqData['point']
        $tmp = [];
        $this->BudgetPoint->virtualFields['fields'] = 'CONCAT(id, "/",created_by, "/", created_date)';
        $each_id = $this->BudgetPoint->find('first', array(
            'conditions' => array(
                'bu_term_id' => $term_id,
                'target_year' => $current_year,
                'layer_code' => $layer_code
            ),
            'fields' => 'fields'
        ));
        $tmp = $this->DataUpdated($each_id, 'BudgetPoint', $login_id, $current_year, $layer_code);
        $tmp['bu_term_id'] = $term_id;
        $tmp['overview'] = $reqData['point'][$current_year]['overview'];
        $tmp['vision'] = $reqData['point'][$current_year]['vision'];
        $tmp['feature'] = $reqData['point'][$current_year]['feature'];
        $tmp['issue'] = $reqData['point'][$current_year]['issue'];
        $tmp['updated_by'] = $login_id;
        $tmp['updated_date'] = date("Y-m-d H:i:s");
        $budgetSave['BudgetPoint'][] = $tmp;

        foreach ($reqData['hyoka'] as $year => $hyokas_data) {
            foreach ($hyokas_data as $key => $hyokas) {
                $tmp = [];
                $tmp['bu_term_id'] = $term_id;
                $tmp['target_year'] = $year;
                $tmp['layer_code'] = $layer_code;
                $tmp['region'] = ($hyokas['region'] == '')? '' : $hyokas['region'];
                $tmp['major_note'] = ($hyokas['major_note'] == '')? '' : $hyokas['major_note'];
                $tmp['monitor'] = ($hyokas['monitor'] == '')? '' : $hyokas['monitor'];
                $tmp['evaluation'] = ($hyokas['evaluation'] == '')? '' : $hyokas['evaluation'];
                $tmp['csr_record'] = ($hyokas['csr_record'] == '')? '' : $hyokas['csr_record'];
                $tmp['created_by'] = $login_id;
                $tmp['updated_by'] = $login_id;
                $tmp['created_date'] = date("Y-m-d H:i:s");
                $tmp['updated_date'] = date("Y-m-d H:i:s");
                $budgetSave['BudgetHyoka'][] = $tmp;
            }
        }
        $deltmp = [];
        $deltmp['bu_term_id'] = $term_id;
        $deltmp['target_year'] = $year;
        $deltmp['layer_code'] = $layer_code;
        $budgetDelete['BudgetHyoka'][] = $deltmp;

        # prepare settlement(決済条件) table array
        $hid_settlement = json_decode($this->request->data('hid_settlement'), true);
        foreach ($hid_settlement as $set_val) {
            $set_datas = explode('/', $set_val);
            $setNo = $set_datas[0];
            $textarea = $set_datas[1];
            $input = $set_datas[2];
            $reqData['settlement'][$current_year][$setNo]['settlement'] = $textarea;
            $reqData['settlement'][$current_year][$setNo]['composition_ratio'] = $input;
        }
        # prepare employee(【生産性】人員数) table array
        $hid_employee = json_decode($this->request->data('hid_employee'), true);
        
        foreach ($hid_employee as $emp_val) {
            $emp_datas = explode('/', $emp_val);
            $emp_year = $emp_datas[0];
            $emp_position = $emp_datas[1];
            $emp_count = $emp_datas[2];
            $reqData['employee'][$emp_year][$emp_position] = $emp_count;
        }
        foreach ($reqData['settlement'][$current_year] as $key => $sett_data) {
            $acc_code = explode("_", $key)[0];
            $sett_no = explode("_", $key)[1];
            $this->SettlementTerm->virtualFields['fields'] = 'CONCAT(id, "/",created_by, "/", created_date)';
            $each_id = $this->SettlementTerm->find('first', array(
                'conditions' => array(
                    'bu_term_id' => $term_id,
                    'target_year' => $current_year,
                    'layer_code' => $layer_code,
                    'account_code' => $acc_code,
                    'sett_no' => $sett_no,
                ),
                'fields' => 'fields'
            ));
            $tmp = $this->DataUpdated($each_id, 'SettlementTerm', $login_id, $current_year, $layer_code);
            $tmp['bu_term_id'] = $term_id;
            $tmp['account_code'] = $acc_code;
            $tmp['sett_no'] = $sett_no;
            $tmp['sett_cmt'] = $sett_data['settlement'];
            $tmp['composition_ratio'] = ($sett_data['composition_ratio'] == '%')? '0' : str_replace('%', '', $sett_data['composition_ratio']);
            $tmp['updated_by'] = $login_id;
            $tmp['updated_date'] = date("Y-m-d H:i:s");
            $budgetSave['SettlementTerm'][] = $tmp;
        }
        foreach ($reqData['employee'] as $years => $emp_datas) {
            foreach ($emp_datas as $emp => $emp_amt) {
                $this->EmpNum->virtualFields['fields'] = 'CONCAT(id, "/", created_by, "/", created_date)';
                $each_id = $this->EmpNum->find('first', array(
                    'conditions' => array(
                        'bu_term_id' => $term_id,
                        'target_year' => $years,
                        'layer_code' => $layer_code,
                        'emp' => $emp
                    ),
                    'fields' => 'fields'
                ));
                $tmp = $this->DataUpdated($each_id, 'EmpNum', $login_id, $years, $layer_code);
                $tmp['bu_term_id'] = $term_id;
                $tmp['emp'] = $emp;
                $tmp['emp_amt'] = (!empty($emp_amt)) ? str_replace(',', '', $emp_amt) : 0;
                $tmp['updated_by'] = $login_id;
                $tmp['updated_date'] = date("Y-m-d H:i:s");
                $budgetSave['EmpNum'][] = $tmp;
            }
        }
        
        
        $budgetSave['BuApprovedLog'][] = $this->BuApprovedLog($term_id, $login_id, $reqData, $_SESSION['BR_MENU_ID']);
        if(!empty($budgetSave)) {
            $BudgetDB = $this->Budget->getDataSource();
            $BudgetSngDB = $this->BudgetSng->getDataSource();
            $BudgetCompDB = $this->BudgetComp->getDataSource();
            $BudgetPointDB = $this->BudgetPoint->getDataSource();
            $BudgetHyokaDB = $this->BudgetHyoka->getDataSource();
            $SettlementDB = $this->SettlementTerm->getDataSource();
            $EmpNumDB = $this->EmpNum->getDataSource();
            $RtaxFeeDB = $this->RtaxFee->getDataSource();
            $RexchangeDB = $this->Rexchange->getDataSource();
            $ApprovedDB = $this->BuApprovedLog->getDataSource();
            try {
                $BudgetDB->begin();
                $BudgetSngDB->begin();
                $BudgetCompDB->begin();
                $BudgetPointDB->begin();
                $BudgetHyokaDB->begin();
                $SettlementDB->begin();
                $EmpNumDB->begin();
                $RtaxFeeDB->begin();
                $RexchangeDB->begin();
                $ApprovedDB->begin();

                foreach ($budgetDelete as $model => $delete_datas) {
                    $this->$model->deleteAll($delete_datas, false);
                }

                foreach ($budgetSave as $model => $save_datas) {
                    $this->$model->saveAll($save_datas);
                }
                $BudgetDB->commit();
                $BudgetSngDB->commit();
                $BudgetCompDB->commit();
                $BudgetPointDB->commit();
                $BudgetHyokaDB->commit();
                $SettlementDB->commit();
                $EmpNumDB->commit();
                $RtaxFeeDB->commit();
                $RexchangeDB->commit();
                $ApprovedDB->commit();
                $returnMsg['success'] = ($reqData['btn_name'] == 'btn_approve') ? parent::getSuccessMsg('SS033') : parent::getSuccessMsg('SS001');
            }catch(Exception $e) {
                $BudgetDB->rollback();
                $BudgetSngDB->rollback();
                $BudgetCompDB->rollback();
                $BudgetPointDB->rollback();
                $BudgetHyokaDB->rollback();
                $SettlementDB->rollback();
                $EmpNumDB->rollback();
                $RtaxFeeDB->rollback();
                $RexchangeDB->rollback();
                $ApprovedDB->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $returnMsg['error'] = ($reqData['btn_name'] == 'btn_approve') ? parent::getErrorMsg('SE164') : parent::getErrorMsg('SE003');
            }
        }
        
        return $returnMsg;
    }

    /** 
     * In saving process, retrieve related data before saving
     * relatedCalculation method
     * @param reqData
     * @author Khin Hnin Myo
     * @return $merge_save_account
     */
    public function relatedCalculation($reqData) {
        $term_id = $_SESSION['BU_TERM_ID'];
        $layer_code = end(array_filter($reqData['sel_name']));
        $current_year = $reqData['current_year'];
        $cache_name = 'budget_'.$term_id.'_'.$current_year.'_'.$layer_code;
        $cache_datas = Cache::read($cache_name);
        $tax_fees = $cache_datas['tax_fees'];
        $yr_list = $this->YearList($reqData['current_year']);
        $data_formulas = $this->CalculateBudget($term_id, $layer_code, $yr_list, $current_year, $tax_fees)['2'];
        $chg_yr_lists = array_keys($reqData['budget']);
        
        foreach ($reqData['budget'] as $year => $acc) {
            foreach ($acc as $acc_id => $formu_amt) {
                $data_formulas[$year][$acc_id] = $formu_amt;
            }
        }
        $merge_save_account = $this->CalculateToSave($data_formulas);
            
        return $merge_save_account;
    }
    /** 
     * In saving process, calculate with db data before saving
     * CalculateToSave method
     * @param replace_arr
     * @author Khin Hnin Myo
     * @return $replace_arr
     */
    public function CalculateToSave($replace_arr) {
        $current_year = $_SESSION['BudgetTargetYear'];
        $yr_list = $this->YearList($current_year);
        $tax_fees = $this->getTaxExList('RtaxFee', $yr_list);
        foreach ($yr_list as $year) {
            foreach ($replace_arr[$year] as $acc_id => $value) {
                
                $lastyear = $year - 1;

                $replace_arr[$year] = str_replace('"'.$acc_id.'"', '('.$replace_arr[$year][$acc_id].')', $replace_arr[$year]);

                if(!empty($replace_arr[$lastyear][$acc_id])) {
                    $replace_arr[$year] = str_replace('"'.$acc_id.'_lastyear"', '('.$replace_arr[$lastyear][$acc_id].')', $replace_arr[$year]);
                }else {
                    $replace_arr[$year] = str_replace('"'.$acc_id.'_lastyear"', '(0)', $replace_arr[$year]);
                }
                $replace_arr[$year] = str_replace("tax", '('.$tax_fees[$year].'/100)', $replace_arr[$year]);
            }
            foreach ($replace_arr[$year] as $accId => $formulae) {
                $replace_arr[$year][$accId] = eval('return ('.$formulae.');');
                if(is_nan($replace_arr[$year][$accId]) || is_infinite($replace_arr[$year][$accId])) {
                    $replace_arr[$year][$accId] = '0';
                }
            }
        }
        return $replace_arr;
    }
    /**
     * DataUpdated method
     * 
     * @author Khin Hnin Myo
     * @return $tmp
     */
    public function DataUpdated($each_id, $modelName, $login_id, $year, $layer_code) {
        $tmp = [];
        if(!empty($each_id)) {
            $field_lists = explode("/", $each_id[$modelName]['fields']);
            $tmp['id'] = $field_lists[0];
            $tmp['created_by'] = $field_lists[1];
            $tmp['created_date'] = $field_lists[2];
        }else {
            $tmp['created_by'] = $login_id;
            $tmp['created_date'] = date("Y-m-d H:i:s");
        }
        $tmp['target_year'] = $year;
        $tmp['layer_code'] = $layer_code;
        return $tmp;
    }

    /**
     * DownloadExcel method
     * 
     * @author Khin Hnin Myo
     * @return void
     */
    public function DownloadExcel($reqData, $file_name, $PHPExcel) {
        $term_id = $_SESSION['BU_TERM_ID'];
        $language = $this->Session->read('Config.language');
        $name = ($language == 'eng')? 'name_en' : 'name_jp';
        $layerlist = $reqData['sel_name'];
        $current_year = $reqData['current_year'];
        $form = (!empty($reqData['form']))? $reqData['form'] : '形態';
        $item_1 = (!empty($reqData['item_1']))? $reqData['item_1'] : '内訳① (販売先 or 商品）';
        $item_2 = (!empty($reqData['item_2']))? $reqData['item_2'] : '内訳② (商品 or 販売先）';
        $layer_code = end(array_filter($layerlist));
        $layerlist = $this->TypeLayerOrder($layerlist);
        $layers_list = $this->RetriveLayers($layerlist);
        $cache_name = 'budget_'.$term_id.'_'.$current_year.'_'.$layer_code;
        $cache_data = Cache::read($cache_name);
        
        $input_color = 'ffffcc';
        $budget_input_color = $input_color;
        $input_locked = $cache_data['input_locked'];
        if($input_locked == 'locked') {
            $budget_input_color = 'FFFFFF';
        }
        $form_disabled = $cache_data['form_disabled'];
        if($form_disabled == 'form_disabled') {
            $budget_input_color = 'EEEEEE';
        }
        $years = $cache_data['yr_list'];
        $lstyr = $years['hidden_title'];
        $next_years = (!empty($lstyr)) ? array_slice($years,3) : array_slice($years,2);
        $exchanges = $cache_data['exchanges'];
        $tax_fees = $cache_data['tax_fees'];
        $budget_accounts = $cache_data['budget_accounts'];
        $budget_sngs = $cache_data['budget_sngs'];
        $budget_comps = $cache_data['budget_comps'];
        $budget_points = $cache_data['budget_points'];
        $budget_hyokas = $cache_data['budget_hyokas'];
        $settlements = $cache_data['settlements'];
        $employees = $cache_data['employees'];
        $excel_formula = $cache_data['excel_formula'];
        $factor_excel = $cache_data['factor_excel'];

        $header_color = 'E5FFFF';

        $objPHPExcel = $PHPExcel->createWorksheet()->setDefaultFont('Cambria', 12);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel ->getActiveSheet()->setShowGridlines(true);
        $objPHPExcel ->setActiveSheetIndex(0);
        $mainHeader = array();
        
        $objPHPExcel->getActiveSheet()->setTitle('ビジネス総合分析表');
        $sheet = $PHPExcel->getActiveSheet();
        $border_thin = array(
            'borders' => array(
                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
        ));
        $aligncenter = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );
        $alignleft = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );
        $alignlefttop = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP)
        );
        $alignright = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );
        $title = array(
            'font'  => array(
                'bold'  => true
            )
        );
        $negative = array(
            'font'  => array(
                'color' => array(
                    'rgb' => 'FF0000'
            )
        ));
        $sheet->getColumnDimension('A')->setWidth(2);
        $sheet->getStyle('B:R')->getAlignment()->setWrapText(true);
        $sheet->getStyle('B2:R2')->applyFromArray($title);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(5);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(10);
        $sheet->getColumnDimension('K')->setWidth(10);
        $sheet->getColumnDimension('L')->setWidth(10);
        $sheet->getColumnDimension('M')->setWidth(10);
        $sheet->getColumnDimension('N')->setWidth(10);
        $sheet->getColumnDimension('O')->setWidth(10);
        $sheet->getColumnDimension('P')->setWidth(10);
        $sheet->getColumnDimension('Q')->setWidth(10);
        $sheet->getColumnDimension('R')->setWidth(10);
        #header
        $sheet->setCellValue('B2', __("ビジネス総合分析表"));
        $sheet->mergeCells('B2:R2');
        $sheet->getStyle('B2:R2')->applyFromArray($aligncenter);
        $sheet->getStyle('B2:R2')->getFont()->setSize(13);
        #select box
        $list = array_values($layers_list);
        $sel_name_list = [];$file_name_list = [];
        $order = 0;
        foreach ($_SESSION['SELECT_IDS'] as $id => $name) {
            // $var_name = explode("/", $name)[0];
            $show_name = explode("/", $name)[1];
            $var_name = ($list[$order][array_values($layerlist)[$order]] == '') ? "----- ".$show_name." ".__("名")." -----" : $list[$order][array_values($layerlist)[$order]];

            $var_arr = array_slice(explode("_/_", $var_name), 0, 2);
            $layer_gp_name = isset($var_arr[1]) ? $var_arr[1] : $var_arr[0];
            $selected_list[] = $layer_gp_name;
            
            if(strpos($layer_gp_name, '-----') === false) {
                $file_name_list[] = $layer_gp_name;
            }
            $order++;
        }
        
        $selected_txt = ($reqData['selected_txt'] == '') ? array_values($selected_list)[0] : $reqData['selected_txt'];
        
        $file_name = $selected_txt;
        $file_name = end($file_name_list);
        $start_num = 66;
        $end_num = $start_num + 2;

        $schar = chr($start_num)."4";
        $char = chr($start_num)."4:".chr($end_num)."4";
        $sheet->setCellValue($schar, $current_year.__("年度"));
        $sheet->mergeCells($char);
        $sheet->getStyle($char)->applyFromArray($alignleft);
        $sheet->getStyle($char)->applyFromArray($border_thin);
        $sheet->getStyle($char)->getFont()->setSize(13);

        $start_num = $end_num + 3;
        $end_num = $start_num + 2;
        
        $first_two_layer = array($selected_list[0], $selected_list[1]);
        foreach ($first_two_layer as $first_two) {
            
            $schar = chr($start_num)."4";
            $char = chr($start_num)."4:".chr($end_num)."4";
            
            $sheet->setCellValue($schar, $first_two);
            $sheet->mergeCells($char);
            $sheet->getStyle($char)->applyFromArray($alignleft);
            $sheet->getStyle($char)->applyFromArray($border_thin);
            $sheet->getStyle($char)->getFont()->setSize(13);

            $start_num = $end_num + 2;
            $end_num = $start_num + 2;
        }

        $start_num = 66;
        $end_num = $start_num + 2;
        
        $last_many_layer = $selected_list;
        unset($last_many_layer[0]);
        unset($last_many_layer[1]);
        $list_cnt = 0;
        foreach ($last_many_layer as $last_many) {
            $list_cnt++;
            $schar = chr($start_num)."6";
            $char = chr($start_num)."6:".chr($end_num)."6";
            
            $sheet->setCellValue($schar, $last_many);
            $sheet->mergeCells($char);
            $sheet->getStyle($char)->applyFromArray($alignleft);
            $sheet->getStyle($char)->applyFromArray($border_thin);
            $sheet->getStyle($char)->getFont()->setSize(13);

            if($list_cnt > 1) {
                $start_num = $end_num + 2;
                $end_num = $start_num + 2;
            }else {
                $start_num = $end_num + 3;
                $end_num = $start_num + 2;
            }
        }
        
        $sheet->setCellValue('B8', $form);
        $sheet->mergeCells('B8:D8');
        $sheet->getStyle('B8:D8')->applyFromArray($alignleft);
        $sheet->getStyle('B8:D8')->applyFromArray($border_thin);
        $sheet->getStyle('B8:D8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('B8:D8')->getFont()->setSize(13);

        $sheet->setCellValue('G8', $item_1);
        $sheet->mergeCells('G8:I8');
        $sheet->getStyle('G8:I8')->applyFromArray($alignleft);
        $sheet->getStyle('G8:I8')->applyFromArray($border_thin);
        $sheet->getStyle('G8:I8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('G8:I8')->getFont()->setSize(13);

        $sheet->setCellValue('K8', $item_2);
        $sheet->mergeCells('K8:M8');
        $sheet->getStyle('K8:M8')->applyFromArray($alignleft);
        $sheet->getStyle('K8:M8')->applyFromArray($border_thin);
        $sheet->getStyle('K8:M8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('K8:M8')->getFont()->setSize(13);
        #hide the tax-exchange table
        /*for ($i=10; $i < 20; $i++) { 
            $sheet->getRowDimension($i)->setVisible(false);
        }   
        /*$sheet->getRowDimension(10)->setVisible(false);
        $sheet->getRowDimension(11)->setVisible(false);
        $sheet->getRowDimension(12)->setVisible(false);*/
        #tax-exchanges
        $sheet->setCellValue('B12', __("為替換算レート：○○円/US\$"));
        $sheet->getStyle('B12:D12')->applyFromArray($border_thin);
        $sheet->getStyle('B12:D12')->applyFromArray($alignleft);
        $sheet->mergeCells('B12:D12');
        $sheet->setCellValue('B13', __("税率"));
        $sheet->getStyle('B13:D13')->applyFromArray($border_thin);
        $sheet->getStyle('B13:D13')->applyFromArray($alignleft);
        $sheet->mergeCells('B13:D13');
        $start = 69;#E
        if(!empty($years['hidden_title'])) {
            $sheet->getColumnDimension('E')->setVisible(false);
        }
        foreach ($years as $header => $yrs) {
            $col = chr($start);$row = 10;
            $sheet->setCellValue($col.$row, __($header));
            $sheet->getStyle($col.$row)->applyFromArray($border_thin);
            $sheet->getStyle($col.$row)->applyFromArray($aligncenter);
            $sheet->getStyle($col.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
            $sheet->mergeCells($col.$row.':'.$col.$row);
            $row++;
            $sheet->setCellValue($col.$row, $yrs);
            $sheet->getStyle($col.$row)->applyFromArray($border_thin);
            $sheet->getStyle($col.$row)->applyFromArray($aligncenter);
            $sheet->getStyle($col.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
            $sheet->mergeCells($col.$row.':'.$col.$row);
            $row++;
            $sheet->setCellValue($col.$row, $exchanges[$yrs]);
            $sheet->getStyle($col.$row)->applyFromArray($border_thin);
            $sheet->mergeCells($col.$row.':'.$col.$row);
            $sheet->getStyle($col.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
            $sheet->getStyle($col.$row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
            $row++;
            $taxRow[$yrs] =  $col.$row;
            $sheet->setCellValue($col.$row, $tax_fees[$yrs]/100);
            $sheet->getStyle($col.$row)->applyFromArray($border_thin);
            $sheet->mergeCells($col.$row.':'.$col.$row);
            $sheet->getStyle($col.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
            $sheet->getStyle($col.$row)->getNumberFormat()->setFormatCode('0.0%');
            $start++;
        }
        $row+=2;
        $col = chr($start2);
        $sheet->setCellValue(chr(66).$row, __("【取引採算】"));
        $sheet->getStyle(chr(66).$row.':'.chr(67).$row)->applyFromArray($alignleft);
        $sheet->mergeCells(chr(66).$row.':'.chr(67).$row);
        $sheet->setCellValue('O'.$row, __("修正後（ファクタリング考慮後）"));
        $sheet->getStyle('O'.$row.':R'.$row)->applyFromArray($border_thin);
        $sheet->getStyle('O'.$row.':R'.$row)->applyFromArray($aligncenter);
        $sheet->getStyle('O'.$row.':R'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->mergeCells('O'.$row.':R'.$row);
        $row++;
        $rows = $row;
        $start = 66;$end = $start + 2;
        $col1 = chr($start);
        $sheet->setCellValue($col1.$row, __("No."));
        $sheet->getStyle($col1.$row.':'.$col1.($row+1))->applyFromArray($border_thin);
        $sheet->getStyle($col1.$row.':'.$col1.($row+1))->applyFromArray($aligncenter);
        $sheet->getStyle($col1.$row.':'.$col1.($row+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->mergeCells($col1.$row.':'.$col1.($row+1));
        $start += 1;$col1 = chr($start);$col2 = chr($end);
        $sheet->setCellValue($col1.$row, '');
        $sheet->getStyle($col1.$row.':'.$col2.($row+1))->applyFromArray($border_thin);
        $sheet->mergeCells($col1.$row.':'.$col2.($row+1));
        $sheet->getStyle($col1.$row.':'.$col1.($row+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $start = 69;$start2 = 79;
        foreach ($years as $header => $yrs) {
            $col = chr($start);$row = $rows;
            $sheet->setCellValue($col.$row, __($header));
            $sheet->getStyle($col.$row)->applyFromArray($border_thin);
            $sheet->getStyle($col.$row)->applyFromArray($aligncenter);
            $sheet->getStyle($col.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
            $sheet->mergeCells($col.$row.':'.$col.$row);
            $row++;
            $sheet->setCellValue($col.$row, $yrs);
            $sheet->getStyle($col.$row)->applyFromArray($border_thin);
            $sheet->getStyle($col.$row)->applyFromArray($aligncenter);
            $sheet->mergeCells($col.$row.':'.$col.$row);
            $sheet->getStyle($col.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
            $start++;
            if(in_array($yrs, $next_years)) {
                $col2 = chr($start2);$row2 = $rows;
                $sheet->setCellValue($col2.$row2, __($header));
                $sheet->getStyle($col2.$row2)->applyFromArray($border_thin);
                $sheet->getStyle($col2.$row2)->applyFromArray($aligncenter);
                $sheet->getStyle($col2.$row2)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
                $sheet->mergeCells($col2.$row2.':'.$col2.$row2);
                $row2++;
                $sheet->setCellValue($col2.$row2, __($yrs));
                $sheet->getStyle($col2.$row2)->applyFromArray($border_thin);
                $sheet->getStyle($col2.$row2)->applyFromArray($aligncenter);
                $sheet->mergeCells($col2.$row2.':'.$col2.$row2);
                $sheet->getStyle($col2.$row2)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
                $start2++;
            }
        }
        $row++;
        $no = 0;
        $start = 66;$none1_3_head = [];
        # for 【取引採算】 table
        foreach ($budget_accounts as $acc_type_name => $type_datas) {
            if($acc_type_name != '【取引採算】') {
                if($acc_type_name == 'No Name 1' || $acc_type_name == 'No Name 3') {
                    $row += 1;
                    $none1_3_head['O'.$row] = 'O'.$row.':'.'R'.$row;
                }
                $acctype_name = (strpos($acc_type_name, 'No Name') !== false) ? '' : $acc_type_name;
                
                $sheet->setCellValue('B'.$row, __($acctype_name));
                $sheet->getStyle('B'.$row.':'.'J'.$row)->applyFromArray($alignleft);
                $sheet->mergeCells('B'.$row.':J'.$row);
                $row+=1;

            }
            foreach ($type_datas as $acc_name => $acc_datas) {$no++;
                $sheet->setCellValue('B'.$row, $no);
                $sheet->getStyle('B'.$row)->applyFromArray($border_thin);
                $sheet->getStyle('B'.$row)->applyFromArray($alignright);
                $sheet->mergeCells('B'.$row.':B'.$row);
                
                $sheet->setCellValue('C'.$row, __($acc_name));
                $sheet->getStyle('C'.$row.':'.'D'.$row)->applyFromArray($border_thin);
                $sheet->getStyle('C'.$row.':'.'D'.$row)->applyFromArray($alignleft);
                $sheet->mergeCells('C'.$row.':D'.$row);
                $start = 69;$start2 = 79;
                # 修正後（ファクタリング考慮後）table at right side
                foreach ($acc_datas as $year => $datas) {
                    if(in_array($year, $years)) {
                        $amount = (is_nan($datas['calculated_amt']) || is_infinite($datas['calculated_amt'])) ? 0 : $datas['calculated_amt'];
                        $input = ($datas['account_type'] == 1 || $year < Setting::LIMIT_YEAR) ? $budget_input_color : 'd5f4ff';
                        if(in_array($acc_name, Setting::INTEREST_COST)) {
                            $input = 'FFFFFF';
                            if($form_disabled == 'form_disabled') $input = 'EEEEEE';
                        }
                        
                        if($form_disabled == 'form_disabled') $input_color = 'EEEEEE';
                        $col = chr($start);
                        if($datas['postfix'] != '') {
                            $amount = ($datas['account_type'] == 1 && $datas['formula'] == '') ? $amount : $amount/100;
                            $sheet->getStyle($col.$row)->getNumberFormat()->setFormatCode('""#,##0.0%;[Red]"-"#,##0.0%');
                        }else {
                            if(strpos($acc_name, 'ヶ月') !== false) {
                                $sheet->getStyle($col.$row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                            }else {
                                $sheet->getStyle($col.$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                            }
                        }
                        if($datas['target_year'] < Setting::LIMIT_YEAR && $datas['postfix'] != '') {
                            $amount = number_format($datas['amount'], 1)."%";
                        }elseif($datas['target_year'] < Setting::LIMIT_YEAR && strpos($acc_name, 'ヶ月') !== false) {
                            $amount = number_format($datas['amount'], 1);
                        }
                        $excel[$year][$datas['account_id']] = $col.$row;#prepare id and rowcol for excel formula
                        $sheet->setCellValue($col.$row, $amount); # insert actual value into Excel cell
                        $sheet->getStyle($col.$row)->applyFromArray($border_thin);
                        $sheet->mergeCells($col.$row.':'.$col.$row);
                        $sheet->getStyle($col.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input);
                        $sheet->getStyle($col.$row)->applyFromArray($alignright);
                        #prepare for total sale
                        if(in_array($year, $next_years)) {
                            if($acc_name == '売上総利益') {
                                // $total_sale[$year][$acc_name] = $col.$row;
                                $total_sale[$year][$acc_name] = $amount;
                                
                            }
                            if($acc_name == '予算人員合計') {
                                // $total_sale[$year][$acc_name] = $col.$row;
                                $total_sale[$year][$acc_name] = $amount;
                            }
                        }
                        if(in_array($year, $next_years)) {
                            $col2 = chr($start2);$row2 = $row;
                            if($acc_type_name == '売上総利益成長率／対前年比') {
                                $sheet->getStyle('N'.$row2.':R'.($row2+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
                                $yr = substr($current_year, strlen($current_year)-2).'年8月末';
                                // $sheet->setCellValue('N'.($row2), __($yr));
                                $sheet->setCellValue('N'.($row2), '');
                                $sheet->getStyle('N'.($row2).':N'.($row2+1))->applyFromArray($aligncenter);
                                $sheet->getStyle('N'.($row2).':N'.($row2+1))->applyFromArray($border_thin);
                                $sheet->mergeCells('N'.($row2).':N'.($row2+1));

                                $sheet->setCellValue('O'.($row2), __("決済条件"));
                                $sheet->getStyle('O'.($row2).':Q'.($row2+1))->applyFromArray($aligncenter);
                                $sheet->getStyle('O'.($row2).':Q'.($row2+1))->applyFromArray($border_thin);
                                $sheet->mergeCells('O'.($row2).':Q'.($row2+1));

                                $sheet->setCellValue('R'.($row2), __("構成比率"));
                                $sheet->getStyle('R'.($row2).':R'.($row2+1))->applyFromArray($aligncenter);
                                $sheet->getStyle('R'.($row2).':R'.($row2+1))->applyFromArray($border_thin);
                                $sheet->mergeCells('R'.($row2).':R'.($row2+1));

                                $row2+=2;
                                $sheet->setCellValue('N'.$row2, __("回収条件→No.26"));
                                $sheet->getStyle('N'.$row2.':'.'N'.($row2+2))->applyFromArray($aligncenter);
                                $sheet->getStyle('N'.$row2.':N'.($row2+2))->applyFromArray($border_thin);
                                $sheet->mergeCells('N'.$row2.':N'.($row2+2));

                                $sheet->setCellValue('O'.$row2, __($settlements['25_1']['sett_cmt']));
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->applyFromArray($alignleft);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->mergeCells('O'.$row2.':Q'.($row2));

                                $sheet->setCellValue('R'.$row2, $settlements['25_1']['composition_ratio']/100);
                                $sheet->getStyle('R'.$row2.':'.'R'.($row2))->applyFromArray($alignright);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getNumberFormat()->setFormatCode('0%');
                                $sheet->mergeCells('R'.$row2.':R'.($row2));

                                $row2++;
                                $sheet->setCellValue('O'.$row2, __($settlements['25_2']['sett_cmt']));
                                $sheet->getStyle('O'.$row2.':'.'Q'.($row2))->applyFromArray($alignleft);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->mergeCells('O'.$row2.':Q'.($row2));

                                $sheet->setCellValue('R'.$row2, $settlements['25_2']['composition_ratio']/100);
                                $sheet->getStyle('R'.$row2.':'.'R'.($row2))->applyFromArray($alignright);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getNumberFormat()->setFormatCode('0%');
                                $sheet->mergeCells('R'.$row2.':R'.($row2));
                                $row2++;
                                $sheet->setCellValue('O'.$row2, __($settlements['25_3']['sett_cmt']));
                                $sheet->getStyle('O'.$row2.':'.'Q'.($row2))->applyFromArray($alignleft);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->mergeCells('O'.$row2.':Q'.($row2));

                                $sheet->setCellValue('R'.$row2, $settlements['25_3']['composition_ratio']/100);
                                $sheet->getStyle('R'.$row2.':'.'R'.($row2))->applyFromArray($alignright);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getNumberFormat()->setFormatCode('0%');
                                $sheet->mergeCells('R'.$row2.':R'.($row2));

                                $row2++;
                                $sheet->setCellValue('N'.$row2, __("支払条件→No.28"));
                                $sheet->getStyle('N'.$row2.':'.'N'.($row2+2))->applyFromArray($aligncenter);
                                $sheet->getStyle('N'.$row2.':N'.($row2+2))->applyFromArray($border_thin);
                                $sheet->mergeCells('N'.$row2.':N'.($row2+2));

                                $sheet->setCellValue('O'.$row2, __($settlements['27_1']['sett_cmt']));
                                $sheet->getStyle('O'.$row2.':'.'Q'.($row2))->applyFromArray($alignleft);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->mergeCells('O'.$row2.':Q'.($row2));

                                $sheet->setCellValue('R'.$row2, $settlements['27_1']['composition_ratio']/100);
                                $sheet->getStyle('R'.$row2.':'.'R'.($row2))->applyFromArray($alignright);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getNumberFormat()->setFormatCode('0%');
                                $sheet->mergeCells('R'.$row2.':R'.($row2));
                                $row2++;
                                $sheet->setCellValue('O'.$row2, __($settlements['27_2']['sett_cmt']));
                                $sheet->getStyle('O'.$row2.':'.'Q'.($row2))->applyFromArray($alignleft);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->mergeCells('O'.$row2.':Q'.($row2));

                                $sheet->setCellValue('R'.$row2, $settlements['27_2']['composition_ratio']/100);
                                $sheet->getStyle('R'.$row2.':'.'R'.($row2))->applyFromArray($alignright);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getNumberFormat()->setFormatCode('0%');
                                $sheet->mergeCells('R'.$row2.':R'.($row2));
                                $row2++;
                                $sheet->setCellValue('O'.$row2, __($settlements['27_3']['sett_cmt']));
                                $sheet->getStyle('O'.$row2.':'.'Q'.($row2))->applyFromArray($alignleft);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('O'.$row2.':Q'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->mergeCells('O'.$row2.':Q'.($row2));

                                $sheet->setCellValue('R'.$row2, $settlements['27_3']['composition_ratio']/100);
                                $sheet->getStyle('R'.$row2.':'.'R'.($row2))->applyFromArray($alignright);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->applyFromArray($border_thin);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                                $sheet->getStyle('R'.$row2.':R'.($row2))->getNumberFormat()->setFormatCode('0%');
                                $sheet->mergeCells('R'.$row2.':R'.($row2));
                                $row2++;
                            }elseif($acc_type_name == 'No Name 2' && ($datas['label_name'] == 'ファクタリング未決済残（期末）' || $datas['label_name'] == 'ファクタリング未決済残（期中平均）' || $datas['label_name'] == 'ﾌｧｸﾀﾘﾝｸﾞによる期間短縮（ヶ月）')) {
                                $none2[] = $row2;
                                $row2+=3;
                            }elseif($acc_type_name == 'No Name 4'){
                                $none4[] = $row2;
                            }elseif($acc_type_name == '【取引採算】' || $acc_type_name == 'No Name 1' || $acc_type_name == 'No Name 3') {
                                
                                #prepare id and rowcol for excel formula
                                $f_excel[$year][$datas['account_id']] = $col2.$row2;

                                if($datas['postfix'] != '') {
                                    $factor_calculated_amt = (is_nan($datas['factor_calculated_amt']) || is_infinite($datas['factor_calculated_amt'])) ? 0 : $datas['factor_calculated_amt']/100;
                                }else{
                                    $factor_calculated_amt = (is_nan($datas['factor_calculated_amt']) || is_infinite($datas['factor_calculated_amt'])) ? 0 : $datas['factor_calculated_amt'];
                                }
                                
                                // $sheet->setCellValue($col2.$row2, $tot_tbl_formula);
                                $sheet->setCellValue($col2.$row2, ($factor_calculated_amt == '') ? 0 : $factor_calculated_amt);
                                $sheet->getStyle($col2.$row2)->applyFromArray($border_thin);
                                $sheet->getStyle($col2.$row2)->applyFromArray($alignright);
                                $sheet->mergeCells($col2.$row2.':'.$col2.$row2);
                                if($datas['postfix'] != '') {
                                    $sheet->getStyle($col2.$row2)->getNumberFormat()->setFormatCode('""#,##0.0%;[Red]"-"#,##0.0%');
                                }else{
                                    $sheet->getStyle($col2.$row2)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');   
                                }
                                #prepare for total sale
                                $row2++;
                            }
                            #prepare for employee table
                            if($acc_type_name == '【予算人員】　　(実人員数）') {
                                $emp_st_row[] = $row;
                            }
                            $start2+=1;
                        }
                        $start+=1;
                        $sheet->setCellValue('L'.$row, $datas['memo']);
                        $sheet->mergeCells('L'.$row.':M'.$row);
                    }
                }
                $row+=1;
            }
        }
        #none 1/ none 3
        foreach ($none1_3_head as $st_col => $st_end_col) {
            $sheet->setCellValue($st_col, __("修正後（ファクタリング考慮後）"));
            $sheet->getStyle($st_col)->applyFromArray($aligncenter);
            $sheet->mergeCells($st_end_col);
            $sheet->getStyle($st_end_col)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
            $sheet->getStyle($st_end_col)->applyFromArray($border_thin);
        }
        #none 2
        $non21 = array_values(array_unique($none2))[0];
        $non23 = array_values(array_unique($none2))[2];
        $sheet->setCellValue('O'.$non21, __("数式はファクタリングを毎月実施する前提。与信の関係上、毎月実施しない場合は平均を直接入力"));
        $sheet->getStyle('O'.$non21.':R'.$non23)->applyFromArray($alignleft);
        $sheet->mergeCells('O'.$non21.':R'.$non23);
        #none 4
        $non41 = array_values(array_unique($none4))[0];
        $non43 = array_values(array_unique($none4))[2];
        $sheet->setCellValue('P'.$non41, __("※合致するよう割り振る（ﾋﾞｼﾞﾈｽ別人員表と合致させること。）"));
        $sheet->getStyle('P'.$non41)->applyFromArray($alignleft);
        $sheet->mergeCells('P'.$non41.':R'.$non43);
        $sheet->getStyle('P'.$non41.':R'.$non43)->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FF0000');
        #added excel formula calculation
        $colYear = array_combine($years, range('E', 'K'));
        $lastArr = [];
        // foreach ($excel as $year => $forArr) {
        //     #replace to equal count of two array and combine as one array
        //     $excel_formula[$year] = array_replace($excel[$year], $excel_formula[$year]);
        //     #replace acc id to excel row no.
        //     foreach ($excel_formula[$year] as $acc_id => $formula) {
        //         $excel_formula[$year] = str_replace('"'.$acc_id.'"', $excel[$year][$acc_id], $excel_formula[$year]);
        //         $excel_formula[$year] = str_replace('"'.$acc_id.'_lastyear"', ($excel[$year-1][$acc_id] == '') ? 0 : $excel[$year-1][$acc_id], $excel_formula[$year]);
        //         $excel_formula[$year] = str_replace("tax", $taxRow[$year], $excel_formula[$year]);
        //     }
        //     $lastArr[$year] = array_combine($excel[$year], $excel_formula[$year]);
        //     #set the formula of cell
        //     foreach ($lastArr[$year] as $curRC => $formu) {
        //         if($curRC != $formu && strpos($formu, $colYear[$year]) !== false) {
        //             $excelFormula = (strpos($formu, '/') !== false) ? '=IFERROR('.$formu.', 0)': '='.$formu;
        //             // $sheet->setCellValue($curRC, $excelFormula);
        //             $sheet->getColumnDimension('E')->setWidth(0);
        //         }
        //     }
        // }
        #factor table formula
        // foreach ($f_excel as $year => $forFactorArr) {
        //     $lastyear = $year - 1;
        //     foreach ($factor_excel[$year] as $exc_id => $exc_formula) {
        //         if(empty($factor_excel[$year][$exc_id])) {
        //             $factor_excel[$year][$exc_id] = 0;

        //         }
        //         $factor_excel[$year] = str_replace('"'.$exc_id.'"', $excel[$year][$exc_id], $factor_excel[$year]);
        //         $factor_excel[$year] = str_replace('"F'.$exc_id.'"', $f_excel[$year][$exc_id], $factor_excel[$year]);
                    
        //         if(!empty($factor_excel[$lastyear][$exc_id])) {
        //             $factor_excel[$year] = str_replace('"'.$exc_id.'_lastyear"', $excel[$lastyear][$exc_id], $factor_excel[$year]);
        //             $factor_excel[$year] = str_replace('"F'.$exc_id.'_lastyear"', $f_excel[$lastyear][$exc_id], $factor_excel[$year]);

        //         }else {
        //             $factor_excel[$year] = str_replace('"F'.$exc_id.'_lastyear"', $excel[$lastyear][$exc_id], $factor_excel[$year]);
        //         }
        //         $factor_excel[$year] = str_replace("tax", $taxRow[$year], $factor_excel[$year]);
        //     }

        //     #set the formula of cell
        //     foreach (array_filter($factor_excel[$year]) as $f_id => $f_formula) {
        //         $currentRC = $f_excel[$year][$f_id];
        //         $excelFormula = (strpos($f_formula, '/') !== false) ? '=IFERROR('.$f_formula.', 0)': '='.$f_formula;
        //         if(!empty($currentRC)) {
        //             # set formula for 修正後（ファクタリング考慮後） table at right side
        //             $sheet->setCellValue($currentRC, $excelFormula);
        //         }
        //         // $sheet->setCellValue($currentRC, $excelFormula);
        //     }
        // }


        $rowNum = $emp_st_row[0]-1;
        $sheet->getStyle('P'.$rowNum)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->setCellValue('P'.$rowNum, __("【生産性】人員数"));
        $sheet->getStyle('P'.$rowNum.':R'.$rowNum)->applyFromArray($border_thin);
        $sheet->getStyle('P'.$rowNum.':R'.$rowNum)->applyFromArray($aligncenter);
        $sheet->mergeCells('P'.$rowNum.':R'.$rowNum);
        $rowNum++;
        $sheet->getStyle('P'.$rowNum)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->setCellValue('P'.$rowNum, __(""));
        $sheet->getStyle('P'.$rowNum.':P'.$rowNum)->applyFromArray($border_thin);
        $sheet->getStyle('P'.$rowNum.':P'.$rowNum)->applyFromArray($alignleft);
        $sheet->mergeCells('P'.$rowNum.':P'.$rowNum);
        $rowNum++;
        $emp_head = array('経営・管理', '営業', 'ｵﾍﾟﾚｰｼｮﾝ', '合計', '一人当り売総');
        foreach($emp_head as $lefthead) {
            if($lefthead == '一人当り売総') {
                $rowNum+=2;
                $s_col = 'N';
            }else {
                $s_col = 'P';
            }
            $sheet->setCellValue($s_col.$rowNum, __($lefthead));
            if($lefthead != '一人当り売総') $sheet->getStyle($s_col.$rowNum.':'.$s_col.$rowNum)->applyFromArray($border_thin);
            $sheet->getStyle($s_col.$rowNum.':'.$s_col.$rowNum)->applyFromArray($alignleft);
            $sheet->mergeCells($s_col.$rowNum.':'.$s_col.$rowNum);
            $rowNum++;
        }
        
        $col = 81;$one = [];
        foreach ($employees as $yr => $value) {
            $rowNum = $emp_st_row[0];
            $colNum = chr($col);
            
            $sheet->getStyle($colNum.$rowNum)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
            $sheet->setCellValue($colNum.$rowNum, $yr);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->applyFromArray($border_thin);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->applyFromArray($aligncenter);
            $sheet->mergeCells($colNum.$rowNum.':'.$colNum.$rowNum);

            $rowNum++;
            $sheet->setCellValue($colNum.$rowNum, $value['経営・管理']);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->applyFromArray($border_thin);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->applyFromArray($alignright);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');
            $sheet->mergeCells($colNum.$rowNum.':'.$colNum.$rowNum);

            $rowNum++;
            $sheet->setCellValue($colNum.$rowNum, $value['営業']);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->applyFromArray($border_thin);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->applyFromArray($alignright);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');
            $sheet->mergeCells($colNum.$rowNum.':'.$colNum.$rowNum);

            $rowNum++;
            $sheet->setCellValue($colNum.$rowNum, $value['ｵﾍﾟﾚｰｼｮﾝ']);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->applyFromArray($border_thin);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->applyFromArray($alignright);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');
            $sheet->mergeCells($colNum.$rowNum.':'.$colNum.$rowNum);
            #emp total
            // $total = '=SUM('.$colNum.($emp_st_row[0]+1).':'.$colNum.$rowNum.')';
            $total = $value['経営・管理'] + $value['営業'] + $value['ｵﾍﾟﾚｰｼｮﾝ'];
            $rowNum++;
            $sheet->setCellValue($colNum.$rowNum, $total);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->applyFromArray($border_thin);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->applyFromArray($alignright);
            $sheet->getStyle($colNum.$rowNum.':'.$colNum.$rowNum)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');
            $sheet->mergeCells($colNum.$rowNum.':'.$colNum.$rowNum);
            $col++;
        }
        $rowNum+=2;
        $s_col = 79;
        $sale_acc_name = Setting::TOTAL_SALES_PER_PERSON_ACC;
        # set value for 一人当り売総 table
        foreach ($total_sale as $sale_yr => $sale_rcol) {
            $s_row = $rowNum;
            $colNum = chr($s_col);
            $gross_profit = $total_sale[$sale_yr][$sale_acc_name[0]];
            $budget_total = $total_sale[$sale_yr][$sale_acc_name[1]];

            $sheet->setCellValue($colNum.$rowNum, $sale_yr);
            $sheet->getStyle($colNum.$rowNum)->applyFromArray($border_thin);
            $sheet->getStyle($colNum.$rowNum)->applyFromArray($aligncenter);
            $sheet->getStyle($colNum.$rowNum)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
            $sheet->mergeCells($colNum.$rowNum.':'.$colNum.$rowNum);

            $rowNum+=1;
            // $sale_total = '=IFERROR('.$gross_profit.'/'.$budget_total.', 0)';# previous code
            // Division by zero check
            $sale_total = $budget_total === 0 ? $sale_total = 0 : $gross_profit / $budget_total;
            $sheet->setCellValue($colNum.$rowNum, $sale_total);
            $sheet->getStyle($colNum.$rowNum)->applyFromArray($border_thin);
            $sheet->getStyle($colNum.$rowNum)->applyFromArray($alignright);
            $sheet->getStyle($colNum.$rowNum)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
            $sheet->mergeCells($colNum.$rowNum.':'.$colNum.$rowNum);
            $s_col++;
            $rowNum = $s_row;
        }
        
        $row+=2;
        #budget_issues
        $sheet->setCellValue('B'.$row, __("本ビジネスの論点整理"));
        $sheet->getStyle('B'.$row.':N'.($row))->applyFromArray($alignleft);
        $sheet->getStyle('B'.$row.':N'.($row))->applyFromArray($border_thin);
        $sheet->getStyle('B'.$row.':N'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->mergeCells('B'.$row.':N'.($row));
        $row++;
        $sheet->setCellValue('B'.$row, $budget_points['issue']);
        $sheet->getStyle('B'.$row.':N'.($row+10))->applyFromArray($alignleft);
        $sheet->getStyle('B'.$row.':N'.($row+10))->applyFromArray($border_thin);
        $sheet->getStyle('B'.$row.':N'.($row+10))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
        $sheet->mergeCells('B'.$row.':N'.($row+10));
        $row+=12;
        #budget_points
        $sheet->setCellValue('B'.$row, __("本ビジネス概要・取引背景・経緯"));
        $sheet->getStyle('B'.$row.':N'.($row))->applyFromArray($alignleft);
        $sheet->getStyle('B'.$row.':N'.($row))->applyFromArray($border_thin);
        $sheet->getStyle('B'.$row.':N'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->mergeCells('B'.$row.':N'.($row));
        $row++;
        $sheet->setCellValue('B'.$row, $budget_points['overview']);
        $sheet->getStyle('B'.$row.':N'.($row+10))->applyFromArray($alignleft);
        $sheet->getStyle('B'.$row.':N'.($row+10))->applyFromArray($border_thin);
        $sheet->getStyle('B'.$row.':N'.($row+10))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
        $sheet->mergeCells('B'.$row.':N'.($row+10));
        $row+=11;
        $sheet->setCellValue('B'.$row, __("BUにおける本ビジネスの位置づけ、目指す姿及び戦略"));
        $sheet->getStyle('B'.$row.':N'.($row))->applyFromArray($alignleft);
        $sheet->getStyle('B'.$row.':N'.($row))->applyFromArray($border_thin);
         $sheet->getStyle('B'.$row.':N'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->mergeCells('B'.$row.':N'.($row));
        $row++;
        $sheet->setCellValue('B'.$row, $budget_points['vision']);
        $sheet->getStyle('B'.$row.':N'.($row+10))->applyFromArray($alignleft);
        $sheet->getStyle('B'.$row.':N'.($row+10))->applyFromArray($border_thin);
        $sheet->getStyle('B'.$row.':N'.($row+10))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
        $sheet->mergeCells('B'.$row.':N'.($row+10));
        $row+=11;
        $sheet->setCellValue('B'.$row, __("当社の機能"));
        $sheet->getStyle('B'.$row.':N'.($row))->applyFromArray($alignleft);
        $sheet->getStyle('B'.$row.':N'.($row))->applyFromArray($border_thin);
        $sheet->getStyle('B'.$row.':N'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->mergeCells('B'.$row.':N'.($row));
        $row++;
        $sheet->setCellValue('B'.$row, $budget_points['feature']);
        $sheet->getStyle('B'.$row.':N'.($row+10))->applyFromArray($alignleft);
        $sheet->getStyle('B'.$row.':N'.($row+10))->applyFromArray($border_thin);
        $sheet->getStyle('B'.$row.':N'.($row+10))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
        $sheet->mergeCells('B'.$row.':N'.($row+10));
        
        $row+=12;
        $sheet->setCellValue('B'.$row, __("【本取引の中期経営計画達成を阻害しかねない主なリスク、課題・留意点と対応策】"));
        $sheet->getStyle('B'.$row.':N'.($row))->applyFromArray($alignleft);
        $sheet->mergeCells('B'.$row.':N'.($row));
        $row++;
        $sheet->setCellValue('B'.$row, __("分野・領域"));
        $sheet->getStyle('B'.$row.':D'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('B'.$row.':D'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('B'.$row.':D'.($row));

        $sheet->setCellValue('E'.$row, __("主なリスク、課題、留意点"));
        $sheet->getStyle('E'.$row.':I'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('E'.$row.':I'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('E'.$row.':I'.($row));

        $sheet->setCellValue('J'.$row, __("対応策、モニタリング方法等"));
        $sheet->getStyle('J'.$row.':M'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('J'.$row.':M'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('J'.$row.':M'.($row));

        $sheet->setCellValue('N'.$row, __("評価"));
        $sheet->getStyle('N'.$row.':N'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('N'.$row.':N'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('N'.$row.':N'.($row));
        $sheet->getStyle('B'.$row.':N'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        #hyokas
        $row++;
        foreach ($budget_hyokas as $value) {
            if($value['csr_record'] == ''){
                $sheet->setCellValue('B'.$row, $value['region']);
                $sheet->getStyle('B'.$row.':D'.($row+3))->applyFromArray($alignleft);
                $sheet->getStyle('B'.$row.':D'.($row+3))->applyFromArray($border_thin);
                // $sheet->getStyle('B'.$row.':D'.($row+3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                $sheet->mergeCells('B'.$row.':D'.($row+3));

                $sheet->setCellValue('E'.$row, $value['major_note']);
                $sheet->getStyle('E'.$row.':I'.($row+3))->applyFromArray($alignleft);
                $sheet->getStyle('E'.$row.':I'.($row+3))->applyFromArray($border_thin);
                $sheet->getStyle('E'.$row.':I'.($row+3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                $sheet->mergeCells('E'.$row.':I'.($row+3));

                $sheet->setCellValue('J'.$row, $value['monitor']);
                $sheet->getStyle('J'.$row.':M'.($row+3))->applyFromArray($alignleft);
                $sheet->getStyle('J'.$row.':M'.($row+3))->applyFromArray($border_thin);
                $sheet->getStyle('J'.$row.':M'.($row+3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                $sheet->mergeCells('J'.$row.':M'.($row+3));

                $sheet->setCellValue('N'.$row, $value['evaluation']);
                $sheet->getStyle('N'.$row.':N'.($row+3))->applyFromArray($alignleft);
                $sheet->getStyle('N'.$row.':N'.($row+3))->applyFromArray($border_thin);
                $sheet->getStyle('N'.$row.':N'.($row+3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
                $sheet->mergeCells('N'.$row.':N'.($row+3));
                $row+=4;
            }
        }
        if($value['csr_record'] != '') {
            $major_note = $value['major_note'];
            $monitor = $value['monitor'];
            $csr = ($value['csr_record'] == 1) ? '該' : '非';
        
            $sheet->setCellValue('B'.$row, __("CSR上のリスク懸念"));
            $sheet->getStyle('B'.$row.':D'.($row+3))->applyFromArray($alignleft);
            $sheet->getStyle('B'.$row.':D'.($row+3))->applyFromArray($border_thin);
            $sheet->mergeCells('B'.$row.':D'.($row+3));

            $sheet->setCellValue('E'.$row, $major_note);
            $sheet->getStyle('E'.$row.':I'.($row+3))->applyFromArray($alignleft);
            $sheet->getStyle('E'.$row.':I'.($row+3))->applyFromArray($border_thin);
            $sheet->getStyle('E'.$row.':I'.($row+3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('E'.$row.':I'.($row+3));

            $sheet->setCellValue('J'.$row, $monitor);
            $sheet->getStyle('J'.$row.':M'.($row+3))->applyFromArray($alignleft);
            $sheet->getStyle('J'.$row.':M'.($row+3))->applyFromArray($border_thin);
            $sheet->getStyle('J'.$row.':M'.($row+3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('J'.$row.':M'.($row+3));
            
            $sheet->setCellValue('N'.$row, $csr);
            $sheet->getStyle('N'.$row.':N'.($row+3))->applyFromArray($alignleft);
            $sheet->getStyle('N'.$row.':N'.($row+3))->applyFromArray($border_thin);
            $sheet->getStyle('N'.$row.':N'.($row+3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('N'.$row.':N'.($row+3));
        }else {
            $major_note = '';
            $monitor = '';
            $csr = '該';
        
            $sheet->setCellValue('B'.$row, __("CSR上のリスク懸念"));
            $sheet->getStyle('B'.$row.':D'.($row+3))->applyFromArray($alignleft);
            $sheet->getStyle('B'.$row.':D'.($row+3))->applyFromArray($border_thin);
            $sheet->mergeCells('B'.$row.':D'.($row+3));

            $sheet->setCellValue('E'.$row, $major_note);
            $sheet->getStyle('E'.$row.':I'.($row+3))->applyFromArray($alignleft);
            $sheet->getStyle('E'.$row.':I'.($row+3))->applyFromArray($border_thin);
            $sheet->getStyle('E'.$row.':I'.($row+3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('E'.$row.':I'.($row+3));

            $sheet->setCellValue('J'.$row, $monitor);
            $sheet->getStyle('J'.$row.':M'.($row+3))->applyFromArray($alignleft);
            $sheet->getStyle('J'.$row.':M'.($row+3))->applyFromArray($border_thin);
            $sheet->getStyle('J'.$row.':M'.($row+3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('J'.$row.':M'.($row+3));
            
            $sheet->setCellValue('N'.$row, $csr);
            $sheet->getStyle('N'.$row.':N'.($row+3))->applyFromArray($alignleft);
            $sheet->getStyle('N'.$row.':N'.($row+3))->applyFromArray($border_thin);
            $sheet->getStyle('N'.$row.':N'.($row+3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('N'.$row.':N'.($row+3));
        }
        $row+=7;#91
        $col = chr($start);
        $sheet->setCellValue('B'.$row, __("１． シナジー"));
        $sheet->getStyle('B'.$row.':H'.$row)->applyFromArray($alignleft);
        $sheet->mergeCells('B'.$row.':H'.$row);
        $sheet->setCellValue('J'.$row, __("シナジー効果の評価"));
        $sheet->getStyle('J'.$row.':K'.$row)->applyFromArray($alignright);
        $sheet->mergeCells('J'.$row.':K'.$row);
        $row++;
        $sheet->setCellValue('B'.$row, __("本取引があることで明らかに認められる（期待される）下記各分野のｼﾅｼﾞｰ（ｲﾝﾊﾟｸﾄ）の合計額"));
        $sheet->getStyle('B'.$row.':J'.($row+1))->applyFromArray($aligncenter);
        $sheet->getStyle('B'.$row.':J'.($row+1))->applyFromArray($border_thin);
        $sheet->mergeCells('B'.$row.':J'.($row+1));

        $sheet->setCellValue('K'.$row, $budget_sngs['total_amount']);
        $sheet->getStyle('K'.$row.':K'.($row+1))->applyFromArray($border_thin);
        $sheet->getStyle('K'.$row.':K'.($row+1))->applyFromArray($alignright);
        $sheet->mergeCells('K'.$row.':K'.($row+1));
        $sngtot_row_col = 'K'.$row;

        $sheet->setCellValue('L'.$row, __("百万円"));
        $sheet->getStyle('L'.$row.':L'.($row))->applyFromArray($alignleft);
        $sheet->mergeCells('L'.$row.':L'.($row));

        $sheet->setCellValue('L'.($row+1), "(a+b+c)");
        $sheet->getStyle('L'.($row+1).':L'.($row+1))->applyFromArray($alignleft);
        $sheet->mergeCells('L'.($row+1).':L'.($row+1));
        $row+=2;
        $sheet->setCellValue('B'.$row, __("【具体的内容】"));
        $sheet->getStyle('B'.$row.':L'.($row))->applyFromArray($alignleft);
        $sheet->mergeCells('B'.$row.':L'.($row));
        $row++;
        $sheet->setCellValue('O'.$row, __("百万円"));
        $sheet->getStyle('O'.$row.':O'.($row))->applyFromArray($alignleft);
        $sheet->mergeCells('O'.$row.':O'.($row));

        $header = array("○収益増加・・・新規顧客基盤獲得、取引ｼｪｱ拡大、対取引先関係強化等に伴う価格交渉力向上、取引拡大など「正の期待値」（金額は税引前ﾍﾞｰｽ）", "○収益増加・・・新規顧客基盤獲得、取引ｼｪｱ拡大、対取引先関係強化等に伴う価格交渉力向上、取引拡大など「正の期待値」（金額は税引前ﾍﾞｰｽ）", "○コスト削減 ・・・ 共同配送/施設共用によるｺｽト引下げ、共通業務の集約化等によるｺｽﾄ削減（本取引を縮小・撤退した時に他取引に与える負のｲﾝﾊﾟｸﾄ/税引前ﾍﾞｰｽ）");
        $label = array("a)", "b)", "c)");
        unset($budget_sngs['total_amount']);
        $sng_row_col = [];
        foreach ($budget_sngs as $key => $value) {
            if($key == 0) {
                $sheet->setCellValue('B'.$row, __($header[$key]));
                $sheet->getStyle('B'.$row.':N'.($row))->applyFromArray($alignleft);
                $sheet->mergeCells('B'.$row.':N'.($row));$row++;
            }else {
                $sheet->setCellValue('B'.$row, __($header[$key]));
                $sheet->getStyle('B'.$row.':R'.($row))->applyFromArray($alignleft);
                $sheet->mergeCells('B'.$row.':R'.($row));$row++;
            }

            $sheet->setCellValue('B'.$row, __("if any ").__("具体的内容"));
            $sheet->getStyle('B'.$row.':E'.($row+1))->applyFromArray($aligncenter);
            $sheet->mergeCells('B'.$row.':E'.($row+1));

            $sheet->setCellValue('F'.$row, __($value['sng_cmt']));
            $sheet->getStyle('F'.$row.':N'.($row+1))->applyFromArray($alignleft);
            $sheet->getStyle('F'.$row.':N'.($row+1))->applyFromArray($border_thin);
            $sheet->getStyle('F'.$row.':N'.($row+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('F'.$row.':N'.($row+1));

            $sheet->setCellValue('O'.$row, ($value['amount'] == '') ? 0 : $value['amount']);
            $sheet->getStyle('O'.$row.':O'.($row+1))->applyFromArray($border_thin);
            $sheet->getStyle('O'.$row.':O'.($row+1))->applyFromArray($alignright);
            $sheet->getStyle('O'.$row.':O'.($row+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('O'.$row.':O'.($row+1));
            // array_push($sng_row_col, 'O'.$row);
            $total_sng += $value['amount'];

            $sheet->setCellValue('P'.$row, $label[$key]);
            $sheet->getStyle('P'.$row.':P'.($row+1))->applyFromArray($alignleft);
            $sheet->mergeCells('P'.$row.':P'.($row+1));
            $row+=2;
        }
        #budget_sngs total formula
        $sheet->setCellValue($sngtot_row_col,  $total_sng);
        // $sheet->setCellValue($sngtot_row_col,  '=SUM('.implode('+', $sng_row_col).')');

        $row++;#105
        $sheet->setCellValue('H'.$row, __("【「傾向」を表す数値の入力方法】　「納入ｼｪｱ」「業界内ｼｪｱ」「市場規模」の増減割合【19年度実績→23年度計画】を、「●割増/減」で入力（小数点第一位まで）して加重平均し、少数点以下を四捨五入して整数化"));
        $sheet->getStyle('H'.$row.':N'.($row+1))->applyFromArray($alignleft);
        $sheet->getStyle('H'.$row.':N'.($row+1))->getFont()->setBold(true)->setSize(6)->getColor()->setRGB('007EC4');
        $sheet->mergeCells('H'.$row.':N'.($row+1));
        $row+=2;
        $sheet->setCellValue('B'.$row, __("２．「商品の競争力」及び「最終製品の競争力と成長段階」"));
        $sheet->getStyle('B'.$row.':N'.($row))->applyFromArray($alignleft);
        $sheet->mergeCells('B'.$row.':N'.($row));
        $row++;
        $sheet->setCellValue('B'.$row, __(""));
        $sheet->getStyle('B'.$row.':B'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('B'.$row.':B'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('B'.$row.':B'.($row));
        $sheet->setCellValue('C'.$row, __("当社販売商品　（構成比、納入ｼｪｱは").($current_year-1).__("年度実績）"));
        $sheet->getStyle('C'.$row.':H'.($row))->applyFromArray($border_thin);
        $sheet->getStyle('C'.$row.':H'.($row))->applyFromArray($aligncenter);
        $sheet->mergeCells('C'.$row.':H'.($row));
        $sheet->setCellValue('I'.$row,  __("最終製品　（業界内ｼｪｱは").($current_year-1).__("年度実績）"));
        $sheet->getStyle('I'.$row.':N'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('I'.$row.':N'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('I'.$row.':N'.($row));
        $sheet->getStyle('B'.$row.':N'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $row++;
        $sheet->setCellValue('B'.$row, __(""));
        $sheet->getStyle('B'.$row.':B'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('B'.$row.':B'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('B'.$row.':B'.($row));
        $sheet->setCellValue('C'.$row, __("販売構成比"));
        $sheet->getStyle('C'.$row.':D'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('C'.$row.':D'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('C'.$row.':D'.($row));
        $sheet->setCellValue('E'.$row, __("取引先"));
        $sheet->getStyle('E'.$row.':F'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('E'.$row.':F'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('E'.$row.':F'.($row));
        $sheet->setCellValue('G'.$row, __("当社納入ｼｪｱ"));
        $sheet->getStyle('G'.$row.':G'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('G'.$row.':G'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('G'.$row.':G'.($row));
        $sheet->setCellValue('H'.$row, __("納入ｼｪｱ増減"));
        $sheet->getStyle('H'.$row.':H'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('H'.$row.':H'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('H'.$row.':H'.($row));
        $sheet->setCellValue('I'.$row, __("最終製品名"));
        $sheet->getStyle('I'.$row.':I'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('I'.$row.':I'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('I'.$row.':I'.($row));
        $sheet->setCellValue('J'.$row, __("最終需要家(業界) "));
        $sheet->getStyle('J'.$row.':J'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('J'.$row.':J'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('J'.$row.':J'.($row));
        $sheet->setCellValue('K'.$row, __("業界内ｼｪｱ"));
        $sheet->getStyle('K'.$row.':K'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('K'.$row.':K'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('K'.$row.':K'.($row));
        $sheet->setCellValue('L'.$row, __("業界内ｼｪｱ増減"));
        $sheet->getStyle('L'.$row.':L'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('L'.$row.':L'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('L'.$row.':L'.($row));
        $sheet->setCellValue('M'.$row, __("市場規模増減"));
        $sheet->getStyle('M'.$row.':M'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('M'.$row.':M'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('M'.$row.':M'.($row));
        $sheet->setCellValue('N'.$row, __("成長性"));
        $sheet->getStyle('N'.$row.':N'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('N'.$row.':N'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('N'.$row.':N'.($row));
        $sheet->getStyle('B'.$row.':N'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $row++;
        $num = 0;$ft_row = $row;
        foreach ($budget_comps['comp'] as $value) {$num++;
            $sheet->setCellValue('B'.$row, $num);
            $sheet->getStyle('B'.$row.':B'.($row))->applyFromArray($alignright);
            $sheet->getStyle('B'.$row.':B'.($row))->applyFromArray($border_thin);
            $sheet->mergeCells('B'.$row.':B'.($row));

            $sheet->setCellValue('C'.$row, ($value['sales_ratio']/100));
            $sheet->getStyle('C'.$row.':D'.($row))->applyFromArray($alignright);
            $sheet->getStyle('C'.$row.':D'.($row))->applyFromArray($border_thin);
            $sheet->getStyle('C'.$row.':D'.($row))->getNumberFormat()->setFormatCode('0%');
            $sheet->getStyle('C'.$row.':D'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('C'.$row.':D'.($row));
            $total_sales_ratio += $value['sales_ratio']/100;

            $sheet->setCellValue('E'.$row, $value['customer']);
            $sheet->getStyle('E'.$row.':F'.($row))->applyFromArray($alignleft);
            $sheet->getStyle('E'.$row.':F'.($row))->applyFromArray($border_thin);
            $sheet->getStyle('E'.$row.':F'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('E'.$row.':F'.($row));

            $sheet->setCellValue('G'.$row, $value['deli_share']/100);
            $sheet->getStyle('G'.$row.':G'.($row))->applyFromArray($alignright);
            $sheet->getStyle('G'.$row.':G'.($row))->applyFromArray($border_thin);
            $sheet->getStyle('G'.$row.':G'.($row))->getNumberFormat()->setFormatCode('0%');
            $sheet->getStyle('G'.$row.':G'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('G'.$row.':G'.($row));
            $sale_deli += ($value['sales_ratio']/100) * ($value['deli_share']/100);

            $sheet->setCellValue('H'.$row, $value['deli_share_change']);
            $sheet->getStyle('H'.$row.':H'.($row))->applyFromArray($alignright);
            $sheet->getStyle('H'.$row.':H'.($row))->applyFromArray($border_thin);
            $sheet->getStyle('H'.$row.':H'.($row))->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
            $sheet->getStyle('H'.$row.':H'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('H'.$row.':H'.($row));
            $sale_delishare += ($value['sales_ratio']/100) * $value['deli_share_change'];

            $sheet->setCellValue('I'.$row, $value['product_name']);
            $sheet->getStyle('I'.$row.':I'.($row))->applyFromArray($alignleft);
            $sheet->getStyle('I'.$row.':I'.($row))->applyFromArray($border_thin);
            $sheet->getStyle('I'.$row.':I'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('I'.$row.':I'.($row));

            $sheet->setCellValue('J'.$row, $value['industry']);
            $sheet->getStyle('J'.$row.':J'.($row))->applyFromArray($alignleft);
            $sheet->getStyle('J'.$row.':J'.($row))->applyFromArray($border_thin);
            $sheet->getStyle('J'.$row.':J'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('J'.$row.':J'.($row));

            $sheet->setCellValue('K'.$row, $value['industry_share']/100);
            $sheet->getStyle('K'.$row.':K'.($row))->applyFromArray($alignright);
            $sheet->getStyle('K'.$row.':K'.($row))->applyFromArray($border_thin);
            $sheet->getStyle('K'.$row.':K'.($row))->getNumberFormat()->setFormatCode('0%');
            $sheet->getStyle('K'.$row.':K'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('K'.$row.':K'.($row));
            $sale_industry += ($value['sales_ratio']/100) * ($value['industry_share']/100);

            $sheet->setCellValue('L'.$row, $value['industry_share_change']);
            $sheet->getStyle('L'.$row.':L'.($row))->applyFromArray($alignright);
            $sheet->getStyle('L'.$row.':L'.($row))->applyFromArray($border_thin);
            $sheet->getStyle('L'.$row.':L'.($row))->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
            $sheet->getStyle('L'.$row.':L'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('L'.$row.':L'.($row));
            $sale_industryshare += ($value['sales_ratio']/100) * ($value['industry_share_change']);

            $sheet->setCellValue('M'.$row, $value['market_size_change']);
            $sheet->getStyle('M'.$row.':M'.($row))->applyFromArray($alignright);
            $sheet->getStyle('M'.$row.':M'.($row))->applyFromArray($border_thin);
            $sheet->getStyle('M'.$row.':M'.($row))->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
            $sheet->getStyle('M'.$row.':M'.($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($input_color);
            $sheet->mergeCells('M'.$row.':M'.($row));
            $sale_market += ($value['sales_ratio']/100) * ($value['market_size_change']);

            // $growth_pot_each = '=L'.$row.'+M'.$row.'+L'.$row.'*M'.$row;
            $growth_pot_each = $value['industry_share_change'] + $value['market_size_change'] + $value['industry_share_change'] * $value['market_size_change'];
            $sheet->setCellValue('N'.$row, $growth_pot_each);
            $sheet->getStyle('N'.$row.':N'.($row))->applyFromArray($alignright);
            $sheet->getStyle('N'.$row.':N'.($row))->applyFromArray($border_thin);
            $sheet->getStyle('N'.$row.':N'.($row))->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
            $sheet->mergeCells('N'.$row.':N'.($row));
            $sale_growth += ($value['sales_ratio']/100) * $growth_pot_each;

            $salexdeli[] = 'G'.$row.'*$C'.$row;
            $salexdelishare[] = 'H'.$row.'*$C'.$row;
            $salexindus[] = 'K'.$row.'*$C'.$row;
            $salexindusshare[] = 'L'.$row.'*$C'.$row;
            $salexmarket[] = 'M'.$row.'*$C'.$row;
            $salexgrowth[] = 'N'.$row.'*$C'.$row;

            $row++;
        }
        
        $lt_row = $ft_row + $num - 1;#last row of comps table
        $common_cell = 'C'.($lt_row+1);#common cell for calculation
        $sheet->setCellValue('B'.$row, __("累計"));
        $sheet->getStyle('B'.$row.':B'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('B'.$row.':B'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('B'.$row.':B'.($row));

        // $colC_tot = '=SUM(C'.$ft_row.':C'.$lt_row.')';
        $sheet->setCellValue('C'.$row, $total_sales_ratio);
        $sheet->getStyle('C'.$row.':D'.($row))->applyFromArray($alignright);
        $sheet->getStyle('C'.$row.':D'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('C'.$row.':D'.($row));
        $sheet->getStyle('C'.$row.':D'.($row))->getNumberFormat()->setFormatCode('0%');

        $sheet->setCellValue('E'.$row, __("（加重平均）"));
        $sheet->getStyle('E'.$row.':F'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('E'.$row.':F'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('E'.$row.':F'.($row));

        // $colG_tot = '=IFERROR(('.implode('+', $salexdeli).')/'.$common_cell.', 0)';
        $colG_tot = $total_sales_ratio === 0 ? 0 : $sale_deli/$total_sales_ratio;
        $sheet->setCellValue('G'.$row, $colG_tot);
        $sheet->getStyle('G'.$row.':G'.($row))->applyFromArray($alignright);
        $sheet->getStyle('G'.$row.':G'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('G'.$row.':G'.($row));
        $sheet->getStyle('G'.$row.':G'.($row))->getNumberFormat()->setFormatCode('0.0%');
        
        // $colH_tot = '=IFERROR(('.implode('+', $salexdelishare).')/'.$common_cell.', 0)';
        $colH_tot = $total_sales_ratio === 0 ? 0 : $sale_delishare/$total_sales_ratio;
        $sheet->setCellValue('H'.$row, $colH_tot);
        $sheet->getStyle('H'.$row.':H'.($row))->applyFromArray($alignright);
        $sheet->getStyle('H'.$row.':H'.($row))->applyFromArray($border_thin);
        $sheet->getStyle('H'.$row.':H'.($row))->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
        $sheet->mergeCells('H'.$row.':H'.($row));

        $sheet->setCellValue('I'.$row, $budget_comps['grand_total']['product_name']);
        $sheet->getStyle('I'.$row.':I'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('I'.$row.':I'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('I'.$row.':I'.($row));

        $sheet->setCellValue('J'.$row, __("（加重平均）"));
        $sheet->getStyle('J'.$row.':J'.($row))->applyFromArray($aligncenter);
        $sheet->getStyle('J'.$row.':J'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('J'.$row.':J'.($row));

        // $colK_tot = '=IFERROR(('.implode('+', $salexindus).')/'.$common_cell.', 0)';
        $colK_tot = $total_sales_ratio === 0 ? 0 : $sale_industry/$total_sales_ratio;
        $sheet->setCellValue('K'.$row, $colK_tot);
        $sheet->getStyle('K'.$row.':K'.($row))->applyFromArray($alignright);
        $sheet->getStyle('K'.$row.':K'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('K'.$row.':K'.($row));
        $sheet->getStyle('K'.$row.':K'.($row))->getNumberFormat()->setFormatCode('0.0%');

        // $colL_tot = '=IFERROR(('.implode('+', $salexindusshare).')/'.$common_cell.', 0)';
        $colL_tot =  $total_sales_ratio === 0 ? 0 : $sale_industryshare/$total_sales_ratio;
        $sheet->setCellValue('L'.$row, $colL_tot);
        $sheet->getStyle('L'.$row.':L'.($row))->applyFromArray($alignright);
        $sheet->getStyle('L'.$row.':L'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('L'.$row.':L'.($row));
        $sheet->getStyle('L'.$row.':L'.($row))->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');

        // $colM_tot = '=IFERROR(('.implode('+', $salexmarket).')/'.$common_cell.', 0)';
        $colM_tot = $total_sales_ratio === 0 ? 0 : $sale_market/$total_sales_ratio;
        $sheet->setCellValue('M'.$row, $colM_tot);
        $sheet->getStyle('M'.$row.':M'.($row))->applyFromArray($alignright);
        $sheet->getStyle('M'.$row.':M'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('M'.$row.':M'.($row));
        $sheet->getStyle('M'.$row.':M'.($row))->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');

        // $colN_tot = '=IFERROR(('.implode('+', $salexgrowth).')/'.$common_cell.', 0)';
        $colN_tot = $total_sales_ratio === 0 ? 0 : $sale_growth/$total_sales_ratio;;
        $sheet->setCellValue('N'.$row, $colN_tot);
        $sheet->getStyle('N'.$row.':N'.($row))->applyFromArray($alignright);
        $sheet->getStyle('N'.$row.':N'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('N'.$row.':N'.($row));
        $sheet->getStyle('N'.$row.':N'.($row))->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');

        $row+=2;
        $sheet->setCellValue('D'.$row, __("当社納入「商品の競争力」"));
        $sheet->getStyle('D'.$row.':F'.($row))->applyFromArray($alignright);
        $sheet->mergeCells('D'.$row.':F'.($row));

        // $colG_final = '=G'.($lt_row+1).'*10';
        $colG_final = $colG_tot*10;
        $sheet->setCellValue('G'.$row, $colG_final);
        $sheet->getStyle('G'.$row.':G'.($row))->applyFromArray($alignright);
        $sheet->getStyle('G'.$row.':G'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('G'.$row.':G'.($row));
        $sheet->getStyle('G'.$row.':G'.($row))->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');

        $colH_final = $colH_tot;
        $sheet->setCellValue('H'.$row, $colH_final);
        $sheet->getStyle('H'.$row.':H'.($row))->applyFromArray($alignright);
        $sheet->getStyle('H'.$row.':H'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('H'.$row.':H'.($row));
        $sheet->getStyle('H'.$row.':H'.($row))->getNumberFormat()->setFormatCode('"△"#,##0;"▲"#,##0');

        $sheet->setCellValue('I'.$row, __("最終製品の　「競争力」"));
        $sheet->getStyle('I'.$row.':J'.($row))->applyFromArray($alignright);
        $sheet->mergeCells('I'.$row.':J'.($row));

        $colK_final = $colK_tot*10;
        $sheet->setCellValue('K'.$row, $colK_final);
        $sheet->getStyle('K'.$row.':K'.($row))->applyFromArray($alignright);
        $sheet->getStyle('K'.$row.':K'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('K'.$row.':K'.($row));
        $sheet->getStyle('K'.$row.':K'.($row))->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');

        $colL_final = $colL_tot;
        $sheet->setCellValue('L'.$row, $colL_final);
        $sheet->getStyle('L'.$row.':L'.($row))->applyFromArray($alignright);
        $sheet->getStyle('L'.$row.':L'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('L'.$row.':L'.($row));
        $sheet->getStyle('L'.$row.':L'.($row))->getNumberFormat()->setFormatCode('"△"#,##0;"▲"#,##0');

        $row++;
        $sheet->setCellValue('E'.$row, __("組合せで表記"));
        $sheet->getStyle('E'.$row.':F'.($row))->applyFromArray($alignright);
        $sheet->mergeCells('E'.$row.':F'.($row));

        // $lst_G = 'ROUND(G'.($lt_row+3).', 0)';
        // $lst_H = 'ROUND(H'.($lt_row+3).', 0)';
        // $lstH_final = '='.$lst_G.'&" "&IF('.$lst_H.'<0, REPLACE('.$lst_H.',1,1,"▲"), "△"&""&'.$lst_H.')';
        $lstH_final = round($colG_final,0).' ';
        if (round($colH_final,0) < 0) {
            $lstH_final .= str_replace('-', '▲', round($colH_final,0));
        } else {
            $lstH_final .= '△' . round($colH_final,0);
        }
        $sheet->setCellValue('H'.$row, $lstH_final);
        $sheet->getStyle('H'.$row.':H'.($row))->applyFromArray($alignright);
        $sheet->getStyle('H'.$row.':H'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('H'.$row.':H'.($row));

        $sheet->setCellValue('J'.$row, __("組合せで表記"));
        $sheet->getStyle('J'.$row.':K'.($row))->applyFromArray($alignright);
        $sheet->mergeCells('J'.$row.':K'.($row));

        // $lst_K = 'ROUND(K'.($lt_row+3).', 0)';
        // $lst_L = 'ROUND(L'.($lt_row+3).', 0)';
        // $lstL_final = '='.$lst_K.'&" "&IF('.$lst_L.'<0, REPLACE('.$lst_L.',1,1,"▲"), "△"&""&'.$lst_L.')';
        $lstL_final = round($colK_final,0).' ';
        if (round($colL_final,0) < 0) {
            $lstL_final .= str_replace('-', '▲', round($colL_final,0));
        } else {
            $lstL_final .= '△' . round($colL_final,0);
        }
        $sheet->setCellValue('L'.$row, $lstL_final);
        $sheet->getStyle('L'.$row.':L'.($row))->applyFromArray($alignright);
        $sheet->getStyle('L'.$row.':L'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('L'.$row.':L'.($row));

        $sheet->setCellValue('M'.($row-1), __("最終製品の「成長性」"));
        $sheet->getStyle('M'.($row-1).':M'.($row))->applyFromArray($alignright);
        $sheet->mergeCells('M'.($row-1).':M'.($row));

        // $colN_final = '=N'.($lt_row+1);
        $colN_final = $colN_tot;
        $sheet->setCellValue('N'.$row, $colN_final);
        $sheet->getStyle('N'.$row.':N'.($row))->applyFromArray($alignright);
        $sheet->getStyle('N'.$row.':N'.($row))->applyFromArray($border_thin);
        $sheet->mergeCells('N'.$row.':N'.($row));
        $sheet->getStyle('N'.$row.':N'.($row))->getNumberFormat()->setFormatCode('"△"#,##0;"▲"#,##0');
        $row++;
        $sheet->setCellValue('E'.$row, __("（　「＋」＝△、　「－」＝▲　）"));
        $sheet->getStyle('E'.$row.':G'.($row))->applyFromArray($alignright);
        $sheet->mergeCells('E'.$row.':G'.($row));

        $sheet->setCellValue('I'.$row, __("（　「＋」＝△、　「－」＝▲　）"));
        $sheet->getStyle('I'.$row.':K'.($row))->applyFromArray($alignright);
        $sheet->mergeCells('I'.$row.':K'.($row));

        $sheet->setCellValue('L'.$row, __("（　「＋」＝△、　「－」＝▲　）"));
        $sheet->getStyle('L'.$row.':M'.($row))->applyFromArray($alignright);
        $sheet->mergeCells('L'.$row.':M'.($row));
        $sheet->getStyle('E'.$row.':M'.($row))->getFont()->setBold(true)->setSize(7);
        
        $this->unLockedCells($sheet); #input cells are editableheader('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');

        // Encode the filename using urlencode
        $encodedFileName = urlencode('ビジネス総合分析表_' . $file_name . '.xlsx');
        header('Content-Disposition: attachment; filename="' . $encodedFileName . '"; charset=utf-8');

        $PHPExcel->output($encodedFileName);
        $this->autoLayout = false;

    }

    /**
     * CalculateBudget method
     * 
     * @author Khin Hnin Myo
     * @return array($calcu_arr, $account_lists)
     */
    public function CalculateBudget($term_id, $layer_code, $yr_list, $current_year, $tax_fees) {
        $layerArr = $this->getLayerArr($current_year, $layer_code);
        $chk_parent_status = false;
        if(!in_array($layer_code, $layerArr)) {
            $chk_parent_status = true;
        }        
        if($this->params['controller'] == 'BudgetResult') $page_name = 'ForecastForms';
        /*$this->Menu->virtualFields['budget_id'] = 'Budget.id';
        $this->Menu->virtualFields['target_year'] = 'Budget.target_year';
        $this->Menu->virtualFields['layer_code'] = 'Budget.layer_code';
        $this->Menu->virtualFields['dbl_edit_flag'] = 'Budget.dbl_edit_flag';
        $this->Menu->virtualFields['label_name'] = 'IF(AccountSetting.label_name IS NULL OR AccountSetting.label_name = "", Account.account_name, AccountSetting.label_name)';
        $this->Menu->virtualFields['account_id'] = 'Account.id';
        $this->Menu->virtualFields['account_code'] = 'Account.account_code';
        $this->Menu->virtualFields['postfix'] = 'Account.postfix';
        $this->Menu->virtualFields['account_type'] = 'Account.account_type';
        $this->Menu->virtualFields['base_param'] = 'Account.base_param';
        $this->Menu->virtualFields['formula'] = 'Account.calculation_formula';
        $this->Menu->virtualFields['memo'] = 'Account.memo';
        $this->Menu->virtualFields['type_name'] = 'AccountSetting.account_type_id';
        $this->Menu->virtualFields['acc_type_name'] = 'AccountType.type_name';
        $this->Menu->virtualFields['amount'] = 'IF(SUM(Budget.amount) IS NULL OR SUM(Budget.amount) = "", "0", SUM(Budget.amount)/Budget.unit)';
        $this->Menu->virtualFields['factor_formula'] = 'Account.factoring_formula';*/
        $get_cal_formula = array_column($this->Menu->query("
            SELECT IF(budgets.id IS NOT NULL, budgets.id, '') AS budget_id, 
            IF(budgets.target_year IS NOT NULL, budgets.target_year, '') AS target_year, 
            IF(budgets.layer_code IS NOT NULL, budgets.layer_code, '') AS layer_code, 
            IF(budgets.dbl_edit_flag IS NOT NULL, budgets.dbl_edit_flag, '') AS dbl_edit_flag, 
            IF(account_settings.label_name IS NULL OR account_settings.label_name = '', accounts.account_name, account_settings.label_name) AS label_name, 
            IF(accounts.id IS NOT NULL, accounts.id, '') AS account_id, 
            IF(accounts.account_code IS NOT NULL, accounts.account_code, '') AS account_code, 
            IF(accounts.postfix IS NOT NULL, accounts.postfix, '') AS postfix, 
            IF(accounts.account_type IS NOT NULL, accounts.account_type, '') AS account_type, 
            IF(accounts.base_param IS NOT NULL, accounts.base_param, '') AS base_param, 
            IF(accounts.calculation_formula IS NOT NULL, accounts.calculation_formula, '') AS formula, 
            IF(accounts.memo IS NOT NULL, accounts.memo, '') AS memo, 
            IF(account_settings.account_type_id IS NOT NULL, account_settings.account_type_id, '') AS type_name, 
            IF(account_types.type_name IS NOT NULL, account_types.type_name, '') AS acc_type_name, 
            IF(SUM(budgets.amount) IS NULL OR SUM(budgets.amount) = '', '0', SUM(budgets.amount)/budgets.unit) AS amount, 
            IF(accounts.factoring_formula IS NOT NULL, accounts.factoring_formula, '') AS factor_formula 
            FROM menus 
            LEFT JOIN account_settings ON account_settings.menu_id = menus.id AND account_settings.flag = 1
            LEFT JOIN account_types ON account_types.id = account_settings.account_type_id and account_types.flag = 1
            LEFT JOIN accounts ON accounts.id = account_settings.account_id and accounts.flag = 1
            LEFT JOIN budgets ON budgets.account_id = accounts.id AND budgets.account_code = accounts.account_code AND (budgets.layer_code = '".$layer_code."' OR budgets.layer_code IN ('".join("','", $layerArr)."') ) AND budgets.bu_term_id = '".$term_id."' AND budgets.target_year IN ('".join("','", array_values($yr_list))."')
            WHERE menus.page_name = '".$page_name."' AND menus.account_flag = 1 AND accounts.flag = 1 AND account_settings.flag = 1
            GROUP BY budgets.target_year, accounts.id, accounts.account_code
            ORDER BY account_settings.display_order, account_settings.id, budgets.target_year;"), '0');
        
        /*$get_cal_formula = array_column($this->Menu->find('all',array(
            'conditions' => array(
                'Menu.page_name' => $page_name,
                'Menu.account_flag' => 1,
                'Account.flag' => 1,
                'AccountSetting.flag' => 1
            ),
            'joins' => array(
                array(
                    'table' => 'account_settings',
                    'alias' => 'AccountSetting',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'AccountSetting.menu_id = Menu.id and AccountSetting.flag = 1'
                    )
                ),
                array(
                    'table' => 'account_types',
                    'alias' => 'AccountType',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'AccountType.id = AccountSetting.account_type_id and AccountType.flag = 1 '
                    )
                ),
                array(
                    'table' => 'accounts',
                    'alias' => 'Account',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Account.id = AccountSetting.account_id and Account.flag = 1 '
                    )
                ),
                array(
                    'table' => 'budgets',
                    'alias' => 'Budget',
                    'type' => 'left',
                    'conditions' => array(
                        0 => ' Budget.account_id = Account.id ',
                        1 => array(
                            'OR' => array(
                                0 => array('Budget.layer_code' => $layerArr),
                                1 => array('Budget.layer_code' => $layer_code),
                            )
                        ),
                        2 => array('Budget.bu_term_id' => $term_id),
                        3 => array('Budget.target_year' => array_values($yr_list)),
                        4 => ' Budget.account_code = Account.account_code ',
                    )
                )
            ),
            'group' => array('Budget.target_year', 'Account.id', 'Account.account_code'),
            'order' => array('AccountSetting.display_order', 'AccountSetting.id', 'Budget.target_year')
        )), 'Menu');*/
        
        $get_cal_formula = $this->getPersonnelAccount($layer_code, $layerArr, $get_cal_formula);

        $fix_formula = array_combine(array_column($get_cal_formula, 'account_id'), array_column($get_cal_formula, 'formula'));
        $arr_fill = array_combine(array_column($get_cal_formula, 'account_id'), array_fill(0, count($get_cal_formula), '0'));
        $label_name = array_combine(array_column($get_cal_formula, 'account_id'), array_column($get_cal_formula, 'label_name'));
        $base_param = array_combine(array_column($get_cal_formula, 'account_id'), array_column($get_cal_formula, 'base_param'));

        $change_acc_type = [];
        foreach ($base_param as $acc_id => $params) {
            $params = str_replace("{", "", $params);
            $params = str_replace("}", "", $params);
            $param_array = explode(",", $params);
            $check_acctype = [];
            foreach($param_array as $id) {
                $rem_str_id = str_replace('"', "", $id);
                if(!empty($rem_str_id) && (!in_array($rem_str_id, array_column($get_cal_formula, 'account_id')))) {
                    $fix_formula[$acc_id] = str_replace($id, "0", $fix_formula[$acc_id]);
                    $check_acctype[] = $acc_id;
                }
            }
            if(count($param_array) == count($check_acctype)) {
                $change_acc_type[$acc_id] = $acc_id;
            }
        }
        foreach ($fix_formula as $id => $formula) {
            $fix_formula[$id] = ($formula == '') ? 0 : $formula;
        }
        
        # set (=No. number)'業務委託費'    
        $name_number = "（ = No.".((array_search("業務委託費合計", array_values($label_name))) + 1)."）";
        $for_factor = [];
        $ftbl_yrs = array_slice(array_keys($_SESSION['yearListOnTerm']), 2);

        foreach ($get_cal_formula as $key => $value) {
            $value['formula'] = $fix_formula[$value['account_id']];
            if(in_array($value['account_id'], $change_acc_type)) {
                $value['account_type'] = 1;
            }
            if($value['label_name'] == '業務委託費') {
                $value['label_name'] = $value['label_name'].$name_number;
            }
            $get_cal_formula[$key] = $value;
            if($value['target_year'] != '') {
                if($value['target_year'] == $yr_list['hidden_title'] || $value['dbl_edit_flag'] || $value['account_type'] != 2 || $value['target_year'] < Setting::LIMIT_YEAR || $value['account_code'] == Setting::ACCOUNT_CODE_LCD) {
                    $replace_arr[$value['target_year']][$value['account_id']] = $value['amount'];
                }elseif($value['formula'] != '') {
                   $replace_arr[$value['target_year']][$value['account_id']] = $value['formula'];
                }else {
                    $replace_arr[$value['target_year']][$value['account_id']] = 0;
                }
                
                if($value['target_year'] != $yr_list['hidden_title'] && $value['postfix'] != '' && $chk_parent_status) {
                    $replace_arr[$value['target_year']][$value['account_id']] = $value['formula'];
                }elseif($value['target_year'] != $yr_list['hidden_title'] && $chk_parent_status && $value['postfix'] == '') {
                    $replace_arr[$value['target_year']][$value['account_id']] = $value['amount'];
                }
                #for factor table
                if(in_array($value['target_year'], $ftbl_yrs)) {
                    if($value['factor_formula'] != '') {
                        $for_factor[$value['target_year']][$value['account_id']] = $value['factor_formula'];
                    }else {
                        $for_factor[$value['target_year']][$value['account_id']] = '"'.$value['account_id'].'"';
                    }
                }
            }else {
                foreach ($yr_list as $target_year) {
                    if($target_year == $yr_list['hidden_title'] || $value['dbl_edit_flag'] || $value['account_type'] != 2 || $target_year < Setting::LIMIT_YEAR) {
                        $replace_arr[$target_year][$value['account_id']] = 0;
                    }elseif($value['formula'] != '') {
                        $replace_arr[$target_year][$value['account_id']] = $value['formula'];
                    }else {
                        $replace_arr[$target_year][$value['account_id']] = 0;
                    }
                    #for factor table
                    if(in_array($target_year, $ftbl_yrs)) {
                        if($value['factor_formula'] != '') {
                            $for_factor[$target_year][$value['account_id']] = $value['factor_formula'];
                        }else {
                            $for_factor[$target_year][$value['account_id']] = '"'.$value['account_id'].'"';;
                        }
                    }
                }
            }
        }
        $calcu_arr = [];$factor_calcu_arr = [];
        $excel_formula = [];$factor_excel_formula = [];
        foreach ($yr_list as $year) {
            if($year < Setting::LIMIT_YEAR && empty($replace_arr[$year])) $replace_arr[$year] = $arr_fill;
            $replace_arr[$year] = (!empty($replace_arr[$year])) ? array_replace($fix_formula, $replace_arr[$year]) : $fix_formula;

            $calcu_arr[$year] = $replace_arr[$year];
            $excel_formula[$year] = $replace_arr[$year];

            if(!empty($for_factor[$year])) {
                $factor_calcu_arr[$year] = $for_factor[$year];
                $factor_excel_formula[$year] = $for_factor[$year];
            }
            foreach ($replace_arr[$year] as $acc_id => $value) {
                
                $lastyear = $year - 1;
                $replace_arr[$year] = str_replace('"'.$acc_id.'"', '('.$replace_arr[$year][$acc_id].')', $replace_arr[$year]);

                if(!empty($replace_arr[$lastyear][$acc_id])) {
                    $replace_arr[$year] = str_replace('"'.$acc_id.'_lastyear"', '('.$replace_arr[$lastyear][$acc_id].')', $replace_arr[$year]);
                }else {
                    $replace_arr[$year] = str_replace('"'.$acc_id.'_lastyear"', '(0)', $replace_arr[$year]);
                }
                $replace_arr[$year] = str_replace("tax", '('.$tax_fees[$year].'/100)', $replace_arr[$year]);
                
                $calcu_arr[$year] = str_replace('"'.$acc_id.'"', "parseFloat($('#hid_".$year."_".$acc_id."').val().replace(/,/g, ''))", $calcu_arr[$year]);
                $calcu_arr[$year] = str_replace('"'.$acc_id.'_lastyear"', "parseFloat($('#hid_".$lastyear."_".$acc_id."').val().replace(/,/g, ''))", $calcu_arr[$year]);
                $calcu_arr[$year] = str_replace("tax", "(parseFloat($('#".$year."_Taxfees').val().replace(/,/g, ''))/(100))", $calcu_arr[$year]);
            }
        }
        $interest_costs = $this->getInterestCost('InterestCost', $yr_list);
        $code_lists = array_combine(array_column($get_cal_formula, 'account_id'), array_column($get_cal_formula, 'account_code'));
        
        foreach ($interest_costs as $cost_year => $Datarate) {
            foreach ($Datarate as $account => $rate) {
                $accountid = array_search($account, $code_lists);
                $calcu_arr[$cost_year][$accountid] = $rate;
                $excel_formula[$cost_year][$accountid] = $rate;
                $replace_arr[$cost_year][$accountid] = $rate;
            }
        }
        $datalists = $this->savedBdtCalculation($get_cal_formula, $yr_list, $replace_arr, $for_factor, $tax_fees, $chk_parent_status);
        $account_lists = $datalists[0];
        $factor_calcu_arr = $datalists[1];

        return array($calcu_arr, $account_lists, $excel_formula, $get_factor, $factor_calcu_arr, $factor_excel_formula);
    }

    /**
     * getPersonnelAccount method
     * @param $layer_code, $layerArr, $get_cal_formula
     * @author Khin Hnin Myo
     * @return $get_cal_formula
     */
    public function getPersonnelAccount($layer_code, $layerArr, $get_cal_formula) {
        unset($this->Layer->virtualFields['type_name']);

        $curr_layer_order =array_keys($this->TypeLayerOrder($layer_code))[0];
        $not_need_child_layer = array('3', '5', '6');
        
        $child_layer = [];
        if(!in_array($curr_layer_order, $not_need_child_layer)) {
            $order = array_keys($this->TypeLayerOrder($layer_code))[0] + 1;
            $child_layer = $this->Layer->find('list', array(
                'fields' => array('layer_code'),
                'conditions' => array(
                    'flag' => 1,
                    'parent_id LIKE' => '%'.$layer_code.'%',
                    'type_order' => $order
                )
            ));
        }
        $labor_amount = 0;
        $this->Budget->virtualFields['amount'] = 'IF(Budget.amount IS NULL OR Budget.amount = "", "0", Budget.amount/Budget.unit)';
        foreach ($get_cal_formula as $gcf_key => $gcf_value) {
            if($gcf_value['account_code'] == Setting::ACCOUNT_CODE_LCD) {
                if(!empty($child_layer)) {
                    $labor_amount_child = $this->Budget->find('list', array(
                        'fields' => array('layer_code', 'amount'),
                        'conditions' => array(
                            'account_code' => Setting::ACCOUNT_CODE_LCD,
                            'layer_code' => $child_layer,
                            'target_year' => $gcf_value['target_year']
                        )
                    ));
                    
                    $labor_amount = (!empty($labor_amount_child)) ? array_sum($labor_amount_child) : 0;
                }else {
                    $labor_cur_amount = $this->Budget->find('list', array(
                        'fields' => array('layer_code', 'amount'),
                        'conditions' => array(
                            'account_code' => Setting::ACCOUNT_CODE_LCD,
                            'layer_code' => $layer_code,
                            'target_year' => $gcf_value['target_year'],
                        )
                    ))[$layer_code];
                    
                    $labor_amount = (!empty($labor_cur_amount)) ? $labor_cur_amount : 0;
                }
                $get_cal_formula[$gcf_key]['amount'] = $labor_amount;
            }
        }
        return $get_cal_formula;
    }

    /**
     * getInterestCost method
     * @param $modelName, $year
     * @author Khin Hnin Myo
     * @return $data
     */
    public function getInterestCost($modelName, $year) {
        $interest_cost_account = Setting::INTEREST_COST;
        $account_codes = array_keys($interest_cost_account);
        $account_names = array_values($interest_cost_account);
        $conditions = [];
        $conditions[$modelName.'.flag'] = 1;
        $conditions[$modelName.'.target_year'] = $year;
        $conditions[$modelName.'.account_code'] = $account_codes;
        
        $fields = [];
        $this->$modelName->virtualFields['fields'] = '('.$modelName.'.rate/100)';
        $fields = array('account_code', 'fields', $modelName.'.target_year');
        
        $data = $this->$modelName->find('list', array(
            'conditions' => $conditions,
            'fields' => $fields
        ));

        return $data;
    }
    /**
     * savedBdtCalculation method
     * 
     * @author Khin Hnin Myo
     * @return $budget_list
     */
    public function savedBdtCalculation($account_list, $yr_list, $replace_arr, $factor_arr, $tax_fees, $chk_parent_status) {
        $budget_list = [];$pre_nt_saved = [];
        $ftbl_yrs = array_slice(array_keys($_SESSION['yearListOnTerm']), 2);
        foreach ($account_list as $value) {
            $pre_nt_saved[$value['account_id']] = $value;
        }
        $already_calculate = [];
        foreach ($yr_list as $tyr) {
            foreach ($pre_nt_saved as $a_id => $a_val) {
                if($a_val['account_code'] == Setting::ACCOUNT_CODE_LCD) {
                    $a_val['calculated_amt'] = $a_val['amount'];
                    $a_val['calculated'] = $a_val['amount'];
                }
                $a_val['target_year'] = $tyr;
                $a_val['calculated'] = $replace_arr[$tyr][$a_val['account_id']];
                $a_val['calculated_amt'] = eval('return ('.$replace_arr[$tyr][$a_val['account_id']].');');
                if($a_val['account_type'] != 1 && $a_val['postfix'] != '' && $a_val['account_type'] != 1 && $a_val['dbl_edit_flag'] != 1) {
                    $a_val['calculated_amt'] = $a_val['calculated_amt']*100;
                }elseif($chk_parent_status && $a_val['postfix'] != '' && !in_array($a_val['label_name'], Setting::INTEREST_COST)) {
                    # for parent layer
                    $a_val['calculated_amt'] = $a_val['calculated_amt']*100;
                }
                $a_val['dbl_edit_flag'] = "0";
                $a_val['amount'] = "0";
                
                $budget_list[$a_val['acc_type_name']][$a_val['label_name']][$tyr] = $a_val;   
            }
        }
        foreach ($account_list as $value) {
            if($value['target_year'] != '') {
                $value['calculated'] = $replace_arr[$value['target_year']][$value['account_id']];
                $value['calculated_amt'] = eval('return ('.$replace_arr[$value['target_year']][$value['account_id']].');');
                if($value['postfix'] != '' && $value['account_type'] != 1 && $value['dbl_edit_flag'] != 1) {
                    $value['calculated_amt'] = $value['calculated_amt']*100;
                }elseif($chk_parent_status && $value['postfix'] != '' && !in_array($value['label_name'], Setting::INTEREST_COST)) {
                    # for parent layer
                    $value['calculated_amt'] = $value['calculated_amt']*100;
                }

                #for factor table
                $already_calculate[$value['target_year']][$value['account_id']] = is_nan($value['calculated_amt']) ? 0 : $value['calculated_amt'];

                $budget_list[$value['acc_type_name']][$value['label_name']][$value['target_year']] = $value;
            }
        }
        $factor_calcu_arr = $factor_arr;
        foreach ($factor_arr as $factor_yr => $factor_acc_datas) {
            $lastyear = $factor_yr - 1;
            foreach ($factor_acc_datas as $factor_acc_id => $factor_formulae) {
                $factor_arr[$factor_yr] = str_replace('"F'.$factor_acc_id.'"', '('.$factor_arr[$factor_yr][$factor_acc_id].')', $factor_arr[$factor_yr]);
                if(!empty($already_calculate[$factor_yr][$factor_acc_id])) {
                    $factor_arr[$factor_yr] = str_replace('"'.$factor_acc_id.'"', '('.$already_calculate[$factor_yr][$factor_acc_id].')', $factor_arr[$factor_yr]);
                }else {
                    $factor_arr[$factor_yr] = str_replace('"'.$factor_acc_id.'"', '(0)', $factor_arr[$factor_yr]);
                }
                if(!empty($factor_arr[$lastyear][$factor_acc_id])) {
                    $factor_arr[$factor_yr] = str_replace('"F'.$factor_acc_id.'_lastyear"', '('.$factor_arr[$lastyear][$factor_acc_id].')', $factor_arr[$factor_yr]);
                }else {
                    $factor_arr[$factor_yr] = str_replace('"F'.$factor_acc_id.'_lastyear"', '('.$already_calculate[$lastyear][$factor_acc_id].')', $factor_arr[$factor_yr]);
                }
                $factor_arr[$factor_yr] = str_replace("tax", '('.$tax_fees[$factor_yr].'/100)', $factor_arr[$factor_yr]);

                $factor_calcu_arr[$factor_yr] = str_replace('"'.$factor_acc_id.'"', "parseFloat($('#budgets #hid_".$factor_yr."_".$factor_acc_id."').val().replace(/,/g, ''))", $factor_calcu_arr[$factor_yr]);
                $factor_calcu_arr[$factor_yr] = str_replace('"'.$factor_acc_id.'_lastyear"', "parseFloat($('#budgets #hid_".$lastyear."_".$factor_acc_id."').val().replace(/,/g, ''))", $factor_calcu_arr[$factor_yr]);

                $factor_calcu_arr[$factor_yr] = str_replace('"F'.$factor_acc_id.'"', "parseFloat($('#budgets_total #hid_".$factor_yr."_".$factor_acc_id."').val().replace(/,/g, ''))", $factor_calcu_arr[$factor_yr]);
                
                if(!in_array($lastyear, $ftbl_year)) {
                    $factor_calcu_arr[$factor_yr] = str_replace('"F'.$factor_acc_id.'_lastyear"', "parseFloat($('#budgets #hid_".$lastyear."_".$factor_acc_id."').val().replace(/,/g, ''))", $factor_calcu_arr[$factor_yr]);
                }else {
                    $factor_calcu_arr[$factor_yr] = str_replace('"F'.$factor_acc_id.'_lastyear"', "parseFloat($('#budgets_total #hid_".$lastyear."_".$factor_acc_id."').val().replace(/,/g, ''))", $factor_calcu_arr[$factor_yr]);
                }
                $factor_calcu_arr[$factor_yr] = str_replace("tax", "(parseFloat($('#".$factor_yr."_Taxfees').val().replace(/,/g, ''))/(100))", $factor_calcu_arr[$factor_yr]);
            }
        }
        foreach ($account_list as $value) {
            if($value['target_year'] != '') {
                $value['calculated'] = $budget_list[$value['acc_type_name']][$value['label_name']][$value['target_year']]['calculated'];
                $value['calculated_amt'] = $budget_list[$value['acc_type_name']][$value['label_name']][$value['target_year']]['calculated_amt'];
                if(in_array($value['target_year'], $ftbl_yrs)) {
                    $value['factor_calculated'] = $factor_arr[$value['target_year']][$value['account_id']];
                    $value['factor_calculated_amt'] = eval('return ('.$factor_arr[$value['target_year']][$value['account_id']].');');

                    if($value['postfix'] != '') {
                        $value['factor_calculated_amt'] = $value['factor_calculated_amt']*100;
                        if($value['factor_formula'] == '"'.$value['account_id'].'"') {
                            $value['factor_calculated_amt'] = $value['factor_calculated_amt']/100;
                        }
                    }
                }else{
                    $value['factor_calculated'] = 0;
                    $value['factor_calculated_amt'] = 0;
                }
                #for factor table
                $budget_list[$value['acc_type_name']][$value['label_name']][$value['target_year']] = $value;
            }
        }
        return array($budget_list, $factor_calcu_arr);
    }
    /**
     * CalculateBudgetComp method
     * 
     * @author Khin Hnin Myo
     * @return $budget_comps
     */
    public function CalculateBudgetComp($term_id, $layer_code, $current_year) {
        $this->BudgetComp->virtualFields['sale_deli'] = 'deli_share*sales_ratio';
        $this->BudgetComp->virtualFields['sale_deli_chg'] = 'deli_share_change*sales_ratio';
        $this->BudgetComp->virtualFields['sale_indus'] = 'industry_share*sales_ratio';
        $this->BudgetComp->virtualFields['sale_indus_chg'] = 'industry_share_change*sales_ratio';

        $this->BudgetComp->virtualFields['market_size_chg'] = 'market_size_change*sales_ratio';
        $this->BudgetComp->virtualFields['growth_pot_each'] = 'ROUND((industry_share_change+market_size_change+industry_share_change*market_size_change), 1)';
        $this->BudgetComp->virtualFields['growth_pot'] = 'ROUND((industry_share_change+market_size_change+industry_share_change*market_size_change), 1)*sales_ratio';
        $budget_comps['comp'] = array_column($this->BudgetComp->find('all', array(
            'conditions' => array(
                'bu_term_id' => $term_id,
                'layer_code' => $layer_code,
                'target_year' => $current_year
            )
        )), 'BudgetComp');
        
        $sales_ratio = $sale_deli = $sale_deli_chg = $sale_indus = $sale_indus_chg = $market_size_chg = $growth_pot = 0;
        foreach ($budget_comps['comp'] as $value) {
            $sales_ratio += $value['sales_ratio'];
            $sale_deli += $value['sale_deli'];
            $sale_deli_chg += $value['sale_deli_chg'];
            $sale_indus += $value['sale_indus'];
            $sale_indus_chg += $value['sale_indus_chg'];
            $market_size_chg += $value['market_size_chg'];
            $growth_pot += $value['growth_pot'];
        }
        
        $budget_comps['grand_total']['sales_ratio'] = $this->checkisNaN($sales_ratio, 1).'%';
        $budget_comps['grand_total']['sale_deli'] = $this->checkisNaN($sale_deli/$sales_ratio, 1).'%';
        if($budget_comps['grand_total']['sale_deli'] == "0%") {
            $budget_comps['grand_total']['sale_deli'] = "0.0%";
        }elseif(strpos($budget_comps['grand_total']['sale_deli'], ".") === false) {
            $budget_comps['grand_total']['sale_deli'] = $this->checkisNaN($sale_deli/$sales_ratio, 1).".0%";
        }
        $budget_comps['grand_total']['sale_deli_chg'] = $this->checkisNaN($sale_deli_chg/$sales_ratio, 1);
        if($budget_comps['grand_total']['sale_deli_chg'] == "0") {
            $budget_comps['grand_total']['sale_deli_chg'] = "0.0";
        }elseif(strpos($budget_comps['grand_total']['sale_deli_chg'], ".") === false) {
            $budget_comps['grand_total']['sale_deli_chg'] = $this->checkisNaN($sale_deli_chg/$sales_ratio, 1).".0";
        }
        $budget_comps['grand_total']['sale_indus'] = $this->checkisNaN($sale_indus/$sales_ratio, 1).'%';
        if($budget_comps['grand_total']['sale_indus'] == "0%") {
            $budget_comps['grand_total']['sale_indus'] = "0.0%";
        }elseif(strpos($budget_comps['grand_total']['sale_indus'], ".") === false) {
            $budget_comps['grand_total']['sale_indus'] = $this->checkisNaN($sale_indus/$sales_ratio, 1).".0%";
        }
        $budget_comps['grand_total']['sale_indus_chg'] = $this->checkisNaN($sale_indus_chg/$sales_ratio);
        $budget_comps['grand_total']['market_size_chg'] = $this->checkisNaN($market_size_chg/$sales_ratio);
        $budget_comps['grand_total']['growth_pot'] = $this->checkisNaN($growth_pot/$sales_ratio);
        $budget_comps['final_total']['deli_product'] = $this->checkisNaN((($sale_deli/$sales_ratio)*10)/100);
        $budget_comps['final_total']['deli_chg_product'] = $this->makeTriangle($this->checkisNaN($sale_deli_chg/$sales_ratio, 1));
        
        $budget_comps['final_total']['indus_fproduct'] = $this->checkisNaN(round((($sale_indus/$sales_ratio)*10)/100));
        $budget_comps['final_total']['indus_chg_fproduct'] = $this->makeTriangle($this->checkisNaN($sale_indus_chg/$sales_ratio));

        $budget_comps['final_total']['final_potential'] = $this->makeTriangle($this->checkisNaN($growth_pot/$sales_ratio));

        $empty_arr = [];
        $empty_arr['sales_ratio'] = 0;
        $empty_arr['deli_share'] = 0;
        $empty_arr['deli_share_change'] = 0;
        $empty_arr['industry_share'] = 0;
        $empty_arr['industry_share_change'] = 0;
        $empty_arr['market_size_change'] = 0;
        $empty_arr['growth_pot_each'] = 0;
        while(count($budget_comps['comp']) < 1) {
            array_push($budget_comps['comp'], $empty_arr);
        }
        
        return $budget_comps;
    }
    /**
     * BudgetHyoka method
     * 
     * @author Khin Hnin Myo
     * @return $budget_hyokas
     */
    public function BudgetHyoka($term_id, $layer_code, $current_year) {
        $fields = array('信用リスク：販売先', '信用リスク：仕入先', '市場リスク', '損害賠償責任', '業務管理');
        $_SESSION['SPECIFIC_REGION'] = $fields;
        $budget_hyokas = [];
        foreach ($fields as $region) {
            $empty_arr = [];
            $condi = [];
            $condi['bu_term_id'] = $term_id;
            $condi['layer_code'] = $layer_code;
            $condi['target_year'] = $current_year;
            $condi['region'] = $region;
            /*if($region != '') {
                $condi['region'] = $region;
            }
            else {
                $condi['csr_record <>'] = '';
            }*/
            
            $datas = $this->BudgetHyoka->find('first', array(
                'conditions' => $condi   
            ))['BudgetHyoka'];
            $empty_arr['bu_term_id'] = $term_id;
            $empty_arr['layer_code'] = $layer_code;
            $empty_arr['target_year'] = $current_year;
            $empty_arr['region'] = $region;
            if(empty($datas)) {
                $empty_arr['major_note'] = '';
                $empty_arr['monitor'] = '';
                $empty_arr['evaluation'] = ($region == '') ? '' : '0';
                $empty_arr['csr_record'] = ($region == '') ? '1' : '';
            }else {
                $empty_arr['major_note'] = $datas['major_note'];
                $empty_arr['monitor'] = $datas['monitor'];
                $empty_arr['evaluation'] = $datas['evaluation'];
                $empty_arr['csr_record'] = $datas['csr_record'];
            }
            array_push($budget_hyokas, $empty_arr);
        }

        $budget_hyokas = $this->NewRowAndCsr($term_id, $layer_code, $current_year, $fields, $budget_hyokas);
        
        return $budget_hyokas;
    }
    /**
     * BudgetSng method
     * 
     * @author Khin Hnin Myo
     * @return $budget_sngs
     */
    public function BudgetSng($term_id, $layer_code, $current_year) {
        $this->BudgetSng->virtualFields['amount'] = 'sng_amt/unit';
        $budget_sngs = array_column($this->BudgetSng->find('all', array(
            'conditions' => array(
                'bu_term_id' => $term_id,
                'layer_code' => $layer_code,
                'target_year' => $current_year
            )
        )), 'BudgetSng');
        if(empty($budget_sngs)) {
            $budget_sngs = [];
            $sngs['sng_cmt'] = '';
            $sngs['sng_amt'] = 0;
            $sngs['unit'] = Setting::BU_UNIT;
            while(count($budget_sngs) < 3) {
                array_push($budget_sngs, $sngs);
            } 
        }
        $sngs_total = 0;
        foreach ($budget_sngs as $value) {
            $sngs_total += $value['amount'];
        }
        $budget_sngs['total_amount'] = $sngs_total;
        return $budget_sngs;
    }
    /**
     * BudgetPoint method
     * 
     * @author Khin Hnin Myo
     * @return $budget_points
     */
    public function BudgetPoint($term_id, $layer_code, $current_year) {
        $budget_points = array_column($this->BudgetPoint->find('all', array(
            'conditions' => array(
                'bu_term_id' => $term_id,
                'layer_code' => $layer_code,
                'target_year' => $current_year
            )
        )), 'BudgetPoint')[0];
        return $budget_points;
    }
    /**
     * Settlement method
     * 
     * @author Khin Hnin Myo
     * @return $set_terms
     */
    public function Settlement($term_id, $layer_code, $current_year) {
        $settlement_terms = array_column($this->SettlementTerm->find('all', array(
            'conditions' => array(
                'bu_term_id' => $term_id,
                'layer_code' => $layer_code,
                'target_year' => $current_year
            )
        )), 'SettlementTerm');
        $set_terms = [];
        foreach ($settlement_terms as $key => $value) {
            $set_terms[$value['account_code']."_".$value['sett_no']] = $value;
        }
        
        return $set_terms;
    }
    /**
     * EmployeeNum method
     * 
     * @author Khin Hnin Myo
     * @return $empNum
     */
    public function EmployeeNum($term_id, $layer_code, $next_years) {
        $next_years = array_slice($next_years, 0, 2);
        $empNum = $this->EmpNum->find('list', array(
            'conditions' => array(
                'bu_term_id' => $term_id,
                'layer_code' => $layer_code,
                'target_year' => $next_years
            ),
            'fields' => array('emp', 'emp_amt', 'target_year'),
            'group' => array('target_year', 'emp')
        ));
        
        /*foreach ($empNum as $year => $datas) {
            foreach ($datas as $emp => $emp_amt) {
                $empNum[$year]['合計'] += $emp_amt;
            }
        }

        if(empty($empNum)) {
            $emp = array_fill_keys(array('経営・管理', '営業', 'ｵﾍﾟﾚｰｼｮﾝ'), '0.00');
            $empNum = array_fill_keys(array_values($next_years), $emp);
            
        }*/
        foreach ($next_years as $year) {
            foreach ($empNum[$year] as $emp => $emp_amt) {
                $empNum[$year]['合計'] += $emp_amt;
            }
            if(empty($empNum[$year])) {
               $empNum[$year]['経営・管理'] = '0.00';
               $empNum[$year]['営業'] = '0.00';
               $empNum[$year]['ｵﾍﾟﾚｰｼｮﾝ'] = '0.00';
            }
        }
        return $empNum;
    }
    /**
     * YearList method
     * 
     * @author Khin Hnin Myo
     * @return $yr_list
     */
    public function YearList($current_year) {
        $hidden_yr = $current_year - 4;
        $yr_1 = $current_year - 3;
        $yr_2 = $current_year - 2;
        $yr_3 = $current_year - 1;
        $yr_4 = $current_year;
        $yr_5 =  $current_year + 1;
        $yr_6 =  $current_year + 2;

        $hide = [];$yr_list = [];
        if($hidden_yr >= Setting::LIMIT_YEAR - 1) $hide['hidden_title'] = $hidden_yr;
        $yr_list = $hide + array(
            '①実績' => $yr_1,
            '②実績' => $yr_2,
            '③見込' => $yr_3,
            '④予算' => $yr_4,
            '⑤計画' => $yr_5,
            '⑥計画' => $yr_6
        );
        return $yr_list;
    }
    /**
     * RetriveLayers method
     * 
     * @author Khin Hnin Myo
     * @param $layerlist
     * @return $layers_list
     */
    public function RetriveLayers($layerlist) {
        $datas = $this->getLayerList($layerlist);
        return $datas;
    }
    /**
     * TotalSale method
     * 
     * @author Khin Hnin Myo
     * @return $formula
     */
    public function TotalSale($layer_code, $next_years, $current_yr) {

        $total_sale_acc = Setting::TOTAL_SALES_PER_PERSON_ACC;
        
        $get_sale_accid = $this->Account->find('list', array(
            'conditions' => array(
                'Account.flag' => 1,
                'Account.account_name' => $total_sale_acc
            ),
            'fields' => array('Account.account_name', 'Account.id')
        ));
        $first_acc = $get_sale_accid[$total_sale_acc[0]];
        $sec_acc = $get_sale_accid[$total_sale_acc[1]];

        $formula = [];
        foreach ($next_years as $year) {
            $formula[$year."_totSales"] = "$('#hid_".$year."_".$first_acc."').val().replace(/,/g, '')/$('#hid_".$year."_".$sec_acc."').val().replace(/,/g, '')";
        }
        return $formula;
    }
    /**
     * getTaxExList method
     * 
     * @author Khin Hnin Myo
     * @return $data
     */
    public function getTaxExList($modelName, $year) {
        $conditions = [];
        $conditions[$modelName.'.flag'] = 1;
        $conditions[$modelName.'.target_year'] = $year;
        $fields = [];
        $this->$modelName->virtualFields['fields'] = 'round('.$modelName.'.rate, 1)';
        $fields = array($modelName.'.target_year', 'fields');
        $data = $this->$modelName->find('list', array(
            'conditions' => $conditions,
            'fields' => $fields
        ));
        $data = $data + array_fill_keys(array_values($year), '0.0');
        ksort($data);
        
        return $data;
    }
    /**
     * FilterLayerList method
     * 
     * @author Khin Hnin Myo
     * @return $datas
     */
    public function FilterLayerList() {
        parent::checkAjaxRequest($this);
        if ($this->request->is('post')) {
            $layerlist = $this->request->data['selectedList'];
            $datas = $this->getLayerList($layerlist);
            return json_encode($datas);
        }else {
            return false;
        }
    }
    /**
     * TypeLayerOrder method
     * 
     * @author Khin Hnin Myo
     * @return $layerlist
     */
    public function TypeLayerOrder ($layerlist) {
        $layer_cond = [];
        $layer_cond['Layer.flag'] = 1;
        $layer_cond['Layer.layer_code'] = $layerlist;

        $layerlist = $this->Layer->find('list', array(
            'conditions' => $layer_cond,
            'fields' => array('Layer.type_order', 'Layer.layer_code'),
            'group' => array('Layer.layer_code'),
            'order' => array('Layer.type_order', 'Layer.layer_order', 'Layer.layer_code')
        ));
        
        return $layerlist;
    }
    /**
     * makeTriangle method
     * 
     * @author Khin Hnin Myo
     * @return $number
     */
    public function makeTriangle($number) {
        if($number > -1) {
            $number = '△'.$number;
        }else {
            $number = str_replace('-', '▲', $number);
        }
        return $number;
    }
    /**
     * checkisNaN method
     * 
     * @author Khin Hnin Myo
     * @return $value
     */
    public function checkisNaN($value, $dec = '') {
        $value = is_nan($value) ? 0 : $value;
        $value = ($dec == '') ? round($value) : round($value, $dec);
        return $value;
    }
    /**
     * unLockedCells method
     * 
     * @author Khin Hnin Myo
     * @return void
     */
    public function unLockedCells($sheet) {
        $colRange = range('A', 'Q');
        $highestRow = $sheet->getHighestRow();
        $sheet->getProtection()->setPassword('*****');
        $sheet->getProtection()->setSheet(true);
        #lock to insert new column and row
        $sheet->getProtection()->setInsertRows(true);
        $sheet->getProtection()->setInsertColumns(true);
        for($r = 1; $r <= $highestRow; $r++) {
            for($c = 0; $c < count($colRange); $c++) {
                $colrow = $colRange[$c].$r;
                $filledColor = $sheet->getStyle($colrow)->getFill()->getStartColor()->getRGB();
                /*if($filledColor == 'ffffcc') {
                    $sheet->getStyle($colrow)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                }*/
            }
        }
    }
    /**
     * ReplaceFormula method
     * @param $tot_sales_formula
     * @author Khin Hnin Myo
     * @return void
     */
    public function ReplaceFormula($tot_sales_formula) {
        $formula = [];
        $acc_name = Setting::TOTAL_SALES_PER_PERSON_ACC;
        foreach ($tot_sales_formula as $key => $value) {
            $year = explode("_", $key)[0];
            $formula[$key] = $year."_".$acc_name[0]."/".$year."_".$acc_name[1];
        }
        return $formula;
    }
    /**
     * getLayerList method
     * @param $layerlist
     * @author Khin Hnin Myo
     * @return datas
     * 
     * for set selection and jquery on change
     */
    public function getLayerList($layerlist) {
        $layerlist = array_filter($layerlist);
        $chosen_yr = $_SESSION['BudgetTargetYear'];
        $selectedTermYear = $_SESSION['yearListOnTerm'][$chosen_yr];
        $start_month = $selectedTermYear[0];
        $end_month = $selectedTermYear[1];
        $language = $_SESSION['Config']['language'];
        $name = ($language == 'eng')? 'name_en' : 'name_jp';

        $type_order = Setting::BU_BUDGET_MAX_LAYER[0];
        if(empty($layerlist)) {
            $Common = New CommonController();
            $first_gpcode = $Common->firstGpCode($current_year, $start_month, $end_month, $type_order, $name);
            $layerlist = array_keys($first_gpcode)[0];
        }
        $layerlist = $this->TypeLayerOrder($layerlist);
        
        $this->LayerType->virtualFields['layer_type_name'] = 'CONCAT(LOWER(REPLACE(LayerType.name_en, " ","")), "/", LayerType.'.$name.')';
        $ids = $this->LayerType->find('list', array(
            'conditions' => array(
                'LayerType.flag' => 1,
                'LayerType.type_order <>' => Setting::REMOVE_LAYER,
                'LayerType.type_order >=' => $type_order
            ),
            'fields' => array('LayerType.type_order', 'LayerType.layer_type_name'),
            'order' => array('LayerType.type_order')
        ));
        $_SESSION['SELECT_IDS'] = $ids;
        $this->LayerType->virtualFields['layer_type_eng'] = 'CONCAT(LOWER(REPLACE(LayerType.name_en, " ","")), "/", LayerType.name_en)';
        $eng_ids = $this->LayerType->find('list', array(
            'conditions' => array(
                'flag' => 1,
                'type_order <>' => Setting::REMOVE_LAYER,
                'type_order >=' => $type_order
            ),
            'fields' => array('type_order', 'LayerType.layer_type_eng'),
            'order' => array('LayerType.type_order')
        ));
        $_SESSION['SELECT_IDS_ENG'] = $eng_ids;
        if(!empty($layerlist)) {
            $this->Layer->virtualFields['layer_type_name'] = 'CONCAT(LOWER(REPLACE(LayerType.name_en, " ","")), "/", LayerType.'.$name.')';
            $this->Layer->virtualFields['layer_name'] = 'CONCAT(Layer.layer_code, "_/_", Layer.'.$name.', IF(Layer.form = "" OR Layer.form IS NULL, "", CONCAT("_/_", Layer.form)), IF(Layer.item_1 = "" OR Layer.item_1 IS NULL, "", CONCAT("_/_", Layer.item_1)), IF(Layer.item_2 = "" OR Layer.item_2 IS NULL, "", CONCAT("_/_", Layer.item_2)))';
            $joins = array(
                array(
                    'table' => 'layer_types',
                    'alias' => 'LayerType',
                    'type' => 'left',
                    'conditions' => array(
                        "LayerType.flag = 1 AND LayerType.id = Layer.layer_type_id"
                    )
                )
            );
            $fields = array('Layer.layer_code', 'Layer.layer_name', 'Layer.layer_type_name');
            $order = array('Layer.type_order', 'Layer.layer_order', 'Layer.layer_code');
            
            $conditions = '';
            $conditions .= "(";
            
            $tmp_order = array_keys($ids);
            $cnt_ord = 0;
            foreach ($layerlist as $orders => $codes) {
                $parentid = '';
                $nxt_order = $tmp_order[$cnt_ord+1];
                $parentid .= '"L'.$orders.'":"'.$codes.'"';
                if($nxt_order != '') {
                    if($orders > $type_order) {
                        $conditions .= " OR ";
                    }
                    $conditions .= "(Layer.parent_id LIKE '%".$parentid."%' AND Layer.type_order = '".$nxt_order."') ";
                    $cnt_ord ++;
                }
            }
            
            if(!empty($layerlist)) {
                $conditions .= " OR Layer.type_order = '".$type_order."')";
            }else {
                $conditions .= "Layer.type_order = '".$type_order."')";
            }
            $conditions .= "AND Layer.bu_status = 1";
            
            $datas = $this->Layer->find('list', array(
                'conditions' => array(
                    'Layer.flag' => 1,
                    /*'DATE_FORMAT(Layer.from_date, "%Y-%m") <=' => $start_month,
                    'DATE_FORMAT(Layer.to_date, "%Y-%m") >=' => $end_month,*/
                    'DATE_FORMAT(Layer.from_date,"%Y-%m") <=' => $end_month,
                    'DATE_FORMAT(Layer.to_date,"%Y-%m") >=' => $start_month,
                    $conditions
                ),
                'joins' => $joins,
                'fields' => $fields,
                'order' => $order
            ));
        }
        foreach ($ids as $ty_order => $val) {
            if(!empty($datas[$val])) {
                $datas[$val] = $datas[$val];
            }else {
                $datas[$val] = array();
            }
            $data = [];
            if($ty_order == Setting::BU_BUDGET_MAX_LAYER[0]) {
                foreach ($_SESSION['BU_LAYER_LISTS'] as $permit_bu_layer) {
                    $data[$permit_bu_layer] = $datas[$val][$permit_bu_layer];
                }
                $datas[$val] = $data;
            }elseif($ty_order == (Setting::BU_BUDGET_MAX_LAYER[0]+1) && !empty($_SESSION['GP_LAYER_LISTS'])) {
                foreach ($_SESSION['GP_LAYER_LISTS'] as $permit_gp_layer) {
                    $data[$permit_gp_layer] = $datas[$val][$permit_gp_layer];
                }
                $datas[$val] = $data;
            }
        }
        return $datas;
    }
    /**
     * getLayerArr method
     * @param $current_year, $layer_code, $layerlist
     * @author Khin Hnin Myo
     * @return $layerArr
     * 
     * for set selection and jquery on change
     */
    public function getLayerArr($current_year, $layer_code) {
        $selectedTermYear = $_SESSION['yearListOnTerm'][$current_year];
        $start_month = $selectedTermYear[0];
        $end_month = $selectedTermYear[1];
        $layerArr = $this->Layer->find('list', array(
            'conditions' => array(
                'Layer.flag' => 1,
                /*'DATE_FORMAT(Layer.from_date, "%Y-%m") <=' => $start_month,
                'DATE_FORMAT(Layer.to_date, "%Y-%m") >=' => $end_month,*/
                'DATE_FORMAT(Layer.from_date,"%Y-%m") <=' => $end_month,
                'DATE_FORMAT(Layer.to_date,"%Y-%m") >=' => $start_month,
                'OR' => array(
                    'Layer.parent_id LIKE ' => '%'.$layer_code.'%',
                    'Layer.layer_code' => $layer_code
                ),
                'Layer.type_order <>' => Setting::REMOVE_LAYER
            ),
            'fields' => array('layer_code'),
            'order' => array('type_order', 'layer_code')
        ));
        $list = [];
        if(count($layerArr) > 1) {
            foreach ($layerArr as $layer_id => $layercode) {
                $layer = $this->Layer->find('list', array(
                    'conditions' => array(
                        'Layer.flag' => 1,
                        /*'DATE_FORMAT(Layer.from_date, "%Y") <=' => $start_month,
                        'DATE_FORMAT(Layer.to_date, "%Y") >=' => $end_month,*/
                        'DATE_FORMAT(Layer.from_date,"%Y-%m") <=' => $end_month,
                        'DATE_FORMAT(Layer.to_date,"%Y-%m") >=' => $start_month,
                        'Layer.parent_id LIKE ' => '%'.$layercode.'%'
                    ),
                    'fields' => array('layer_code'),
                    'order' => array('type_order', 'layer_code')
                ));
                if(!empty($layer)) {
                    array_push($list, $layercode);
                }
            }
            if(!empty($list)) {
                foreach ($list as $code) {
                    #if have child layer, remove parent layer from layer array
                    if (($key = array_search($code, $layerArr)) !== false) {
                        unset($layerArr[$key]);
                    }
                }
            }
        }
        
        return $layerArr;
    }
    /**
     * MergeOrOverwrite method
     * @param 
     * @author Khin Hnin Myo
     * @return $count_datas
     * 
     * check count of saving data on selected layer and target year
     */
    public function MergeOrOverwrite() {
        parent::checkAjaxRequest($this);
        if ($this->request->is('post')) {
            $term_id = $_SESSION['BU_TERM_ID'];
            $target_year = $this->request->data['target_year'];
            $layer_code = $this->request->data['lastSelected'];
            
            $count_datas = $this->Budget->find('count', array(
                'conditions' => array(
                    'bu_term_id' => $term_id,
                    'target_year' => $target_year,
                    'layer_code' => $layer_code,
                )
            ));
            return json_encode($count_datas);
        }
    }
    /**
     * BuApprovedLog method
     * @param $term_id, $login_id, $reqData
     * @author Khin Hnin Myo
     * @return $tmp
     * 
     * check count of saving data on selected layer and target year
     */
    public function BuApprovedLog($term_id, $login_id, $reqData, $menu_id) {
        $selected_lists = $this->TypeLayerOrder($reqData['sel_name']);
        $conditions = [];
        $conditions['bu_term_id'] = $term_id;
        $conditions['menu_id'] = $menu_id;
        $conditions['target_year'] = $reqData['current_year'];
        $cancel_flag = 0;
        if($reqData['btn_name'] == 'btn_save') {
            $flag = 1;
            $conditions['flag <>'] = 0;
        }elseif($reqData['btn_name'] == 'btn_approve') {
            $flag = 2;
            $conditions['flag'] = 1;
        }else {
            $flag = 1;
            $conditions['flag'] = 2;
            $cancel_flag = 1;
        }
        $this->BuApprovedLog->virtualFields['fields'] = 'CONCAT(id, "/",created_by, "/", created_date)';
        foreach ($_SESSION['SELECT_IDS_ENG'] as $orders => $val_key) {
            $fields = str_replace(' ', '_', strtolower(explode('/', $val_key)[1])).'_code';
            if(!empty($selected_lists[$orders])) $conditions[$fields] = $selected_lists[$orders];
            else $conditions[$fields] = '0';
        }
        $each_id = $this->BuApprovedLog->find('first', array(
            'conditions' => $conditions,
            'fields' => 'fields'
        ));
        $tmp = array_filter($this->DataUpdated($each_id, 'BuApprovedLog', $login_id, '', ''));
        unset($conditions['flag']);
        
        $conditions['flag'] = $flag;
        $tmp = $tmp + $conditions;
        $tmp['department_code'] = '0';
        $tmp['cancel_flag'] = $cancel_flag;
        $tmp['updated_by'] = $login_id;
        $tmp['updated_date'] = date("Y-m-d H:i:s");
        return $tmp;
    }
    /**
     * getTBLfields method
     * @param $layerlist
     * @author Khin Hnin Myo
     * @return $conditions
     * 
     * check count of saving data on selected layer and target year
     */
    public function getTBLfields($layerlist) {
        $conditions = [];
        $conditions['flag <>'] = 0;
        $conditions['bu_term_id'] = $_SESSION['BU_TERM_ID'];
        $selected_lists = $this->TypeLayerOrder($layerlist);
        foreach ($_SESSION['SELECT_IDS_ENG'] as $orders => $val_key) {
            $fields = str_replace(' ', '_', strtolower(explode('/', $val_key)[1])).'_code';
            if(!empty($selected_lists[$orders])) $conditions[$fields] = $selected_lists[$orders];
            else $conditions[$fields] = '0';
        }
        return $conditions;
    }

    /**
     * ShowBtnList method
     * @param $term_id, $layers_list, $layerlist, $current_year
     * @author Khin Hnin Myo
     * @return $btn_arr
     */
    public function ShowBtnList($term_id, $layers_list, $layerlist, $current_year) {
        $show_tmp_btn = false;
        $show_complete_btn = false;
        $show_app_cancel_btn = false;

        $tmp_for_sheet = array_filter($layerlist);

        $start_month = $_SESSION['yearListOnTerm'][$current_year][0];
        $end_month = $_SESSION['yearListOnTerm'][$current_year][1];
        $cnt_selected = count(array_filter($layerlist));
        $last_selected = end(array_filter($layerlist));
        $last_selected_order = key($this->TypeLayerOrder($last_selected));
        $last_order = $last_selected_order;
        $layerID = $_SESSION['SELECT_IDS'];
        $last_type_order = array_search(end($layerID), $layerID);

        $cnt_layers_lists = count(array_filter($layers_list));
        $same_two = false;
        if($cnt_selected == $cnt_layers_lists) {
            $same_two = true;
        }
        if($last_selected_order == $last_type_order || $same_two) {
            $last_selected_order = $last_selected_order;
        }else if($last_selected_order+1 == Setting::REMOVE_LAYER) {
            $last_selected_order = $last_selected_order+2;
        }else {
            $last_selected_order = $last_selected_order+1;
        }
        $this->Layer->virtualFields['type_name'] = 'REPLACE(CONCAT(LOWER(LayerType.name_en),"_code")," ", "_")';
        $lists = $this->Layer->find('list', array(
            'conditions' => array(
                'Layer.flag' => 1,
                // 'DATE_FORMAT(Layer.from_date, "%Y-%m") <=' => $start_month,
                // 'DATE_FORMAT(Layer.to_date, "%Y-%m") >=' => $end_month,
                'DATE_FORMAT(Layer.from_date,"%Y-%m") <=' => $end_month,
                'DATE_FORMAT(Layer.to_date,"%Y-%m") >=' => $start_month,
                'Layer.type_order <>' => Setting::REMOVE_LAYER,
                'Layer.type_order >=' => Setting::BU_BUDGET_MAX_LAYER[0],
                'Layer.type_order' => $last_selected_order,
                'OR' => array(
                    'Layer.parent_id LIKE' => '%'.$last_selected.'%',
                    'Layer.layer_code LIKE' => '%'.$last_selected.'%',
                )
            ),
            'joins' => array(
                array(
                    'table' => 'layer_types',
                    'alias' => 'LayerType',
                    'type' => 'left',
                    'conditions' => array(
                        "LayerType.flag = 1 AND LayerType.id = Layer.layer_type_id"
                    )
                )
            ),
            'fields' => array(
                'id', 'layer_code', 'type_name'
            ),
            'order' => array('layer_order', 'id')
        ));

        $conditions = $this->getTBLfields($layerlist);
        $conditions['menu_id'] = $_SESSION['BR_MENU_ID'];
        $conditions['target_year'] = $current_year;
        #for current
        $show_tmp_btn = true;
        $current_flag = $this->BuApprovedLog->find('first', array(
            'conditions' => $conditions,
            'fields' => array('flag')
        ))['BuApprovedLog']['flag'];
        if($current_flag == 2) {
            $show_tmp_btn = false;
            $show_app_cancel_btn = true;
        }else {
            $show_tmp_btn = true;
            $show_complete_btn = true;
        }
        
        #for child
        $initial = 0;$save = 0;$approve = 0;$approve_cancel = 0;$cnt_code = 0;$cancel_disabled  = false;
        foreach ($lists as $field_name => $code_lists) {
            $cnt_code = count($code_lists);
            foreach ($code_lists as $code) {
                $conditions[$field_name] = $code;
                $check_flag = $this->BuApprovedLog->find('first', array(
                    'conditions' => $conditions,
                    'fields' => array('flag')
                ))['BuApprovedLog']['flag'];
                
                if(!empty($check_flag)) {
                    $save ++;
                    if($check_flag == 1) {
                        $approve ++;$save --;
                    }else {
                        $approve_cancel ++;$save --;
                    }
                }else {
                    $initial ++;
                }
            }
        }
        $cnt_ids = count($_SESSION['SELECT_IDS']);
        if($cnt_code > 0) {
            if($show_tmp_btn) {
                $show_tmp_btn = false;
                $show_complete_btn = false;
                if($cnt_selected == $cnt_ids || $same_two) {
                    if($approve_cancel == $cnt_code) {
                        $show_tmp_btn = false;
                        $show_complete_btn = false;
                        $show_app_cancel_btn = true;
                    }else if($approve == $cnt_code) {
                        $show_tmp_btn = true;
                        $show_complete_btn = true;
                    }else if($save == $cnt_code) {
                        $show_tmp_btn = true;
                        $show_complete_btn = false;
                    }else if($initial == $cnt_code) {
                        $show_tmp_btn = true;
                        $show_complete_btn = true;
                    }
                }else {
                    $show_tmp_btn = true;
                    if($approve_cancel == $cnt_code) {
                        $show_tmp_btn = true;
                        $show_complete_btn = true;
                        $show_app_cancel_btn = false;
                    }
                }
            }elseif($show_app_cancel_btn) {
                #for parent
                $layerlist = array_filter($layerlist);
                array_pop($layerlist);
                $conditions = $this->getTBLfields($layerlist);
                $current_flag = $this->BuApprovedLog->find('first', array(
                    'conditions' => $conditions,
                    'fields' => array('flag')
                ))['BuApprovedLog']['flag'];
                if($current_flag == 2) {
                    $cancel_disabled = true;
                }
            }
        }
        $btn_arr['save'] = $show_tmp_btn;
        $btn_arr['approve'] = $show_complete_btn;
        $btn_arr['approve_cancel'] = $show_app_cancel_btn;
        $btn_arr['cancel_disabled'] = $cancel_disabled;
        $btn_arr['form_disabled'] = 'form_enabled';
        
        if($btn_arr['save'] && $btn_arr['approve']) {
            $check_labor = $this->checkLaborComplete($term_id, $current_year, $tmp_for_sheet);
            if(!$check_labor) {#not finished in LC and LCD, so cn't allow to action
                // $btn_arr['save'] = false;
                $btn_arr['approve'] = false;
            }
        }elseif($btn_arr['approve_cancel'] && !$btn_arr['cancel_disabled']) {
            $btn_arr['form_disabled'] = 'form_disabled';
            $check_sheet = $this->checkSheetComplete($term_id, $current_year, $tmp_for_sheet);
            if($check_sheet) {#already complete in SpreadSheet, so cn't allow to cancel
                $btn_arr['approve_cancel'] = false;
            }
        }
        
        if(!$btn_arr['save'] && !$btn_arr['approve'] && !$btn_arr['approve_cancel'] && !$btn_arr['cancel_disabled']) {
            $btn_arr['form_disabled'] = 'form_disabled';
            $check_labor = $this->checkSheetComplete($term_id, $current_year, $tmp_for_sheet);
            if($check_labor) {#already complete in SpreadSheet, so cn't allow to cancel
                $btn_arr['approve_cancel'] = true;
                $btn_arr['cancel_disabled'] = true;
            }
        }
        $bu_code = $tmp_for_sheet[0];
        $permissions = $this->getPermissionLayerLists();
        if($last_order == Setting::BU_BUDGET_MAX_LAYER[0]) {
            $read_permit = $permissions['index']['all_layer'][$last_order];
            $save_permit = $permissions['save']['all_layer'][$last_order];
            $complete_permit = $permissions['complete']['all_layer'][$last_order];
            $cancel_permit = $permissions['complete_cancel']['all_layer'][$last_order];
        }else {
            $read_permit = $permissions['index']['all_layer'][$bu_code][$last_order];
            $save_permit = $permissions['save']['all_layer'][$bu_code][$last_order];
            $complete_permit = $permissions['complete']['all_layer'][$bu_code][$last_order];
            $cancel_permit = $permissions['complete_cancel']['all_layer'][$bu_code][$last_order];
        }
        $btn_arr['btn_hide']['save'] = false;
        $btn_arr['btn_hide']['approve'] = false;
        $btn_arr['btn_hide']['approve_cancel'] = false;
        if(!in_array($last_selected, $save_permit)) {
            $btn_arr['save'] = false;
            $btn_arr['btn_hide']['save'] = true;
        }
        if(!in_array($last_selected, $complete_permit)) {
            $btn_arr['approve'] = false;
            $btn_arr['btn_hide']['approve'] = true;
        }
        if(!in_array($last_selected, $complete_permit)) {
            $btn_arr['approve_cancel'] = false;
            $btn_arr['cancel_disabled'] = false;
            $btn_arr['btn_hide']['approve_cancel'] = true;
        }

        if($btn_arr['approve_cancel']) {
            $btn_arr['form_disabled'] = 'form_disabled';
        }
        return $btn_arr;
    }

    /** 
     * year lists that labor form' s active year
     * prepareMenuYear method
     * @param $session_year_lists, $labor_complete_code, $labor_menu_arr
     * @author Khin Hnin Myo
     * @return $labor_year_lists
     */
    public function prepareMenuYear($session_year_lists, $labor_complete_code, $labor_menu_arr) {
        $labor_year_lists = [];
        foreach (array_keys($session_year_lists) as $yr_key => $year) {
            if($yr_key > 1) {
                $users = $this->LaborCostDetail->checkUser($session_year_lists[$year], $labor_complete_code, $_SESSION['BU_TERM_ID'], 'LaborCostDetail');
                if(!empty($users)) {
                    foreach ($labor_menu_arr as $menu_id) {
                        $labor_year_lists[$menu_id][] = $year;
                    }
                    $businesses = $this->LaborCostDetail->businessCodes($session_year_lists[$year], $labor_complete_code)[1];
                    if(empty($businesses)) {
                        unset($labor_year_lists[$menu_id]);
                    }
                }
            }
        }
        return $labor_year_lists;
    }

    /**
     * checkLaborComplete method
     * @param $term_id, $current_year, $layerlist
     * @author Khin Hnin Myo
     * @return $check_labor_complete
     */
    public function checkLaborComplete($term_id, $current_year, $layerlist) {
        $sort_order = $this->TypeLayerOrder(array_filter($layerlist));
        end($sort_order);
        $last_selected_order = key($sort_order);
        $labor_layer = Setting::BU_LAYER_SETTING['topLayer'];
        $check_labor_complete = true;

        $labor_complete_code = $sort_order[$labor_layer];
        $session_year_lists = $_SESSION['yearListOnTerm'];
        $labor_menu_id = Setting::MENU_ID_LIST['LaborCosts'];
        $ldetail_menu_id = Setting::MENU_ID_LIST['LaborCostDetails'];
        $labor_menu_arr = array($labor_menu_id, $ldetail_menu_id);
        $getLabor = [];
        if(!empty($labor_complete_code)) {
            $labor_year_lists = $this->prepareMenuYear($session_year_lists, $labor_complete_code, $labor_menu_arr);
            foreach ($labor_year_lists as $menu_id => $year_lists) {
                foreach ($year_lists as $year) {
                    $datas = $this->BuApprovedLog->find('first', array(
                        'conditions' => array(
                            'flag' => 2,
                            'bu_term_id' => $term_id,
                            'target_year' => $year,
                            'bu_code' => 0,
                            'group_code' => $labor_complete_code,
                            'line_code' => 0,
                            'business_code' => 0,
                            'sub_business_code' => 0,
                            'menu_id' => $menu_id
                        )
                    ))['BuApprovedLog'];
                    
                    if(!empty($datas)) {
                        $getLabor[$menu_id][$year] = $datas;
                    }
                }
            }
            
            if(count($getLabor[$labor_menu_id]) == count($labor_year_lists[$labor_menu_id]) && count($getLabor[$ldetail_menu_id]) == count($labor_year_lists[$ldetail_menu_id])) {
                $check_labor_complete = true;
            }else {
                $check_labor_complete = false;
            }
        }
        return $check_labor_complete;
    }
    /**
     * checkSheetComplete method
     * @param $term_id, $current_year, $layerlist
     * @author Khin Hnin Myo
     * @return $check_sheet_complete
     */
    public function checkSheetComplete($term_id, $current_year, $layerlist) {
        $menu_id = Setting::MENU_ID_LIST['BusinessAnalysis'];
        $sort_order = $this->TypeLayerOrder($layerlist);
        end($sort_order);
        $last_selected_order = key($sort_order);

        $check_sheet_complete = true;
        
        $getSheet = [];
        $tmp_keep_cond = [];$bu_code = '0';$gp_code = '0';
        foreach ($sort_order as $order => $code) {
            if($order == '2') {
                $bu_code = $code;
            }elseif($order == '3') {
                $gp_code = $code;
            }
        }
        $year_lists = array_keys($_SESSION['TERM_YEAR_LIST']);
        $first_year_arr[] = $year_lists[0] - 1;
        $year_lists = array_merge($first_year_arr, $year_lists);
        foreach ($year_lists as $year) {
            $datas = $this->BuApprovedLog->find('first', array(
                'conditions' => array(
                    'flag' => 2,
                    'bu_term_id' => $term_id,
                    'target_year' => $year,
                    'bu_code' => $bu_code,
                    'group_code' => $gp_code,
                    'line_code' => 0,
                    'business_code' => 0,
                    'sub_business_code' => 0,
                    'menu_id' => $menu_id
                )
            ))['BuApprovedLog'];
            if(!empty($datas)) {
                $getSheet[$menu_id][$year] = $datas;
            }
        }
        
        if(count($getSheet[$menu_id]) > 0) {
            $check_sheet_complete = true;
        }else {
            $check_sheet_complete = false;
        }
        return $check_sheet_complete;
    }

    public function getPermissionLayerLists() {
        $login_id   = $this->Session->read('LOGIN_ID');
        $role_id    = $this->Session->read('ADMIN_LEVEL_ID');
        $pagename   = 'ForecastForms';
        $maxLimit = Setting::BU_BUDGET_MAX_LAYER[0];

        $chosen_yr = $_SESSION['BudgetTargetYear'];
        $selectedTermYear = $_SESSION['yearListOnTerm'][$chosen_yr];
        $start_month = $selectedTermYear[0];
        $end_month = $selectedTermYear[1];

        $Common = New CommonController();
        
        $permission = $Common->getPermissionsByRoleForBU($login_id, $role_id, $start_month, $end_month, $pagename, $maxLimit);
        
        return $permission;
    }

    /**
     * NewRowAndCsr method
     * 
     * @author Khin Hnin Myo
     * @param $term_id, $layer_code, $current_year, $fields, $budget_hyokas
     * @return $budget_hyokas
     */
    public function NewRowAndCsr($term_id, $layer_code, $current_year, $fields, $budget_hyokas){
        $condi = [];
        $condi['bu_term_id'] = $term_id;
        $condi['layer_code'] = $layer_code;
        $condi['target_year'] = $current_year;
        $conditions = [];
        $conditions = $condi;
        $conditions['region NOT IN'] = $fields;
        $conditions['region <>'] = '';
        $conditions['csr_record'] = '';

        $new_rows = array_column($this->BudgetHyoka->find('all', array(
            'conditions' => $conditions   
        )), 'BudgetHyoka');
        $budget_hyokas = array_merge($budget_hyokas, $new_rows);
        
        $condi['csr_record <>'] = '';
        $csr = array_column($this->BudgetHyoka->find('all', array(
            'conditions' => $condi   
        )), 'BudgetHyoka');
        
        $budget_hyokas = array_merge($budget_hyokas, $csr);

        return $budget_hyokas;
    }
}
