<?php

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'BrmPositions');
App::import('Controller', 'BrmBudgetSummary');


use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\Exception\GoogleException;

/**
 * ExpectedBudgetDifferenceController
 * @author Hein Htet Ko
 */

define('UPLOAD_FILEPATH', ROOT); //server
define('UPLOAD_PATH', 'app'.DS.'temp');

class BrmForecastBudgetDifferenceController extends AppController
{
    public $uses = array('BrmAccount', 'BrmSaccount', 'BrmBudget', 'BrmExpected', 'BrmBudgetPrime', 'BrmExpectedBudgetDiffAccount','User', 'BrmPosition', 'BrmBudgetApprove', 'BrmTerm', 'BrmManpowerPlan', 'BrmExpectedBudgetDiffJob', 'BrmTermDeadline','BrmActualResultSummary','RtaxFee', 'LayerType','Layer');
    public $components = array('Session', 'PhpExcel.PhpExcel');
    public function beforeFilter()
    {
        parent::checkUserStatus();
        parent::CheckSession();
        //parent::CheckAdminSession();

        $Common = new CommonController;

        $flag = true;

        $ba_code = '';
        $head_dept_id = '';
        $term_id = '';

        if ($this->Session->check('LOGIN_ID')) {
            $login_id = $this->Session->read('LOGIN_ID');
        }
        if (empty($this->request->query('code'))) {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        } else {
            $ba_code = $this->request->query('code');
        }
        if (empty($this->request->query('hq'))) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        } else {
            $head_dept_id = $this->request->query('hq');
        }
        if (empty($this->request->query('term'))) {
            $term_id = $this->Session->read('TERM_ID');
        } else {
            $term_id = $this->request->query('term');
        }

        if ($term_id == "") {
            $flag = false;
            $errorMsg = parent::getErrorMsg('SE072', __('期間選択'));
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
        }
        $topLayer = Setting::LAYER_SETTING['topLayer'];
        $bottomLayer = Setting::LAYER_SETTING['bottomLayer'];
        if ($head_dept_id == "") {
            $layer = $this->LayerType->find('first', array(
                'conditions' => array('LayerType.type_order'=>$topLayer),
                'fields' => array('LayerType.name_jp'),
            ));
            $flag = false;
            $errorMsg = parent::getErrorMsg('SE072', __($layer['LayerType']['name_jp']));
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
        }
        if ($ba_code == "" && $_GET['code']=="") {
            $layer = $this->LayerType->find('first', array(
                'conditions' => array('LayerType.type_order'=>$bottomLayer),
                'fields' => array('LayerType.name_jp'),
            ));
            $flag = false;
            $errorMsg = parent::getErrorMsg('SE072', __($layer['LayerType']['name_jp']));
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
        }
        if (!$flag) {
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        }
        //}

        if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $pagename = $this->request->params['controller'];
        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        
        if ($term_id != "" && $head_dept_id != "" && ($ba_code != "" || $_GET['code']!="")) {
            if ($permissions['index']['limit'] == 0 || !isset($permissions['index']['limit'])) {
                //$errorMsg = parent::getErrorMsg('SE065');
                //$this->Flash->set($errorMsg, array("key"=>"TermError"));
                //$this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
            }
        } 
        $layers = array_keys($permissions['index']['layers']);
        
        if ((!in_array($layer_code, $layers)) || ($layer_code == "" && $permissions['index']['limit'] > 0)) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }
    }

    public function index()
    {
        $this->layout = "phase_3_menu";
        $Common = new CommonController;

        #get session values
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        if (!empty($this->request->query('term'))) {
            $term_id = $this->request->query('term');
        } else {
            $term_id = $this->Session->read('TERM_ID');
        }
        if (!empty($this->request->query('term'))) {
            $budget_term = array_shift($this->BrmTerm->find('first', array(
            'fields' => array('budget_year','budget_end_year'),
            'conditions' => array('id' => $this->request->query('term')))));
            $budget_term = $budget_term['budget_year'].'~'.$budget_term['budget_end_year'];
            $this->Session->write('TERM_NAME', $budget_term);
        } else {
            $budget_term = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('LOGIN_ID')) {
            $login_id = $this->Session->read('LOGIN_ID');
        }
        if ($this->Session->check('PERMISSIONS')) {
            $permission = $this->Session->read('PERMISSIONS');
        }
        //echo '<pre>';print_r($permission);echo '</pre>';
        
        if ($this->Session->check('HEAD_DEPT_NAME')) {
            $headquarter = $this->Session->read('HEAD_DEPT_NAME');
            $hq_name = $headquarter; # to set ctp
        }
        if (!empty($this->request->query('hq'))) {
            $head_dept_id = $this->request->query('hq');
        } else {
            $head_dept_id = $this->Session->read('HEAD_DEPT_CODE');
        }
        if (!empty($this->request->query('code'))) {
            $ba_code = $this->request->query('code');
        } else {
            $ba_code = $this->Session->read('SESSION_LAYER_CODE');
        }
        
        #get create_limit for save btn show/hide
        $createLimit = isset($permission['save']['limit']) ? true : false;
        #get create_limit for approve btn show/hide
        $approveLimit = isset($permission['approve']['limit']) ? true : false;
        

        

        // Start : Edited by Ei Thandar Kyaw on 11/08/2020
        $showApprove = false;
        $showCancelApprove = false;
        $showSave = false;

        $yearsArr = [];
        $years = explode('~', $budget_term);
        $start_year = $years[0];
        $start = $years[0];
        $end = $years[1];
        $fbd_data = [];
        
        try {
            $cache_name = 'fbd_'.$term_id.'_'.$ba_code.'_'.$login_id;
            // $fbd_data = Cache::read($cache_name);
            $fbd_data = $this->getFbdData($term_id, $head_dept_id, $ba_code, $start_year, $end, $login_id);
            #to get ba_code/ba_name
            $language = $this->Session->read('Config.language');
            
            // $ba_name = $this->BusinessAreaModel->find('first', array('fields' => array('ba_name_jp','ba_name_en'),
            // 'conditions' => array('ba_code' => $ba_code,'flag' => 1)));
            // $full_ba_name = '';
            // if ($language == 'eng') {
            //     if ($ba_name['BusinessAreaModel']['ba_name_en']!='') {
            //         $full_ba_name = $ba_code.'/'.$ba_name['BusinessAreaModel']['ba_name_en'];
            //     } else {
            //         $full_ba_name = $ba_code;
            //     }
            // } else {
            //     if ($ba_name['BusinessAreaModel']['ba_name_jp']!='') {
            //         $full_ba_name = $ba_code.'/'.$ba_name['BusinessAreaModel']['ba_name_jp'];
            //     } else {
            //         $full_ba_name = $ba_code;
            //     }
            // }
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
            $ba_name = $this->Session->read('BUDGET_BA_NAME');
            if ($this->Session->read('Config.language') == 'eng') {
                $lang_name = 'en';
            } else {
                $lang_name = 'jp';
            }
            $layer = $this->Layer->find('first', array(
                'conditions' => array('Layer.flag' => '1', 'Layer.layer_code' => $this->Session->read('SESSION_LAYER_CODE')),
                'fields' => array('Layer.layer_code', 'LayerType.name_en', 'LayerType.name_jp'),
            ));
            
            if ($ba_name != '')  $full_ba_name = $layer['Layer']['layer_code'].'/'.$ba_name;
            else $full_ba_name = $ba_code;
            
            
            $term = $this->BrmTerm->find('all', array('fields' => array('budget_year', 'budget_end_year'),'conditions' => array('id' => $term_id)));
            $termRange = range($term[0]['BrmTerm']['budget_year'], $term[0]['BrmTerm']['budget_end_year']);
           
            $budgetLog = $this->BrmBudgetApprove->find('all', array('fields' => array('flag'),'conditions' => array('brm_term_id' => $term_id,'hlayer_code' => $this->Session->read('HEAD_DEPT_CODE'), 'layer_code'=> $this->Session->read('SESSION_LAYER_CODE'))));
           
            $HQ_approve_Disable = 'false';

            // if not approved, will show Approve button and save,
            // already approved, will show Cancel Approve button
            if (($budgetLog && $budgetLog[0]['BrmBudgetApprove']['flag'] == 2)) {
                $showCancelApprove = true;
                $showSave = false;
            } else {
                $showApprove = true;
                $showSave = true;
            }
            
            // End : Edited by Ei Thandar Kyaw on 11/08/2020
            // $data = $this->calculateAmt($term_id,$ba_code);
            // $diff_acc = $data[0];
            // $sub_acc = $data[1];
            // $term = explode('~', $this->Session->read('TERM_NAME'));
            // $nopage = $term[1]-$term[0];
            // if(empty($diff_acc))
            // 	$amounts = $data[2];
            // $field_names = $data[3];
            // $manpower_data = $data[4];
            // $diff_job = $data[5];

            #send required data to view
            
            $this->set('createLimit', $createLimit);
            $this->set('approveLimit', $approveLimit);
            $this->set('user_level', $user_level);
            $this->set('showApprove', $showApprove);
            $this->set('showCancelApprove', $showCancelApprove);
            $this->set('showSave', $showSave);
            $this->set('full_ba_name', $full_ba_name);
            $this->set('fbdData', $fbd_data);
            $this->set('HQ_approve_Disable', $HQ_approve_Disable);
            $this->set('lang_name', $lang_name);
            $this->set('layer', $layer);
            // $this->set('sub_acc',$sub_acc);
            // $this->set('amounts',$amounts);
            // $this->set('field_names', $field_names);
            // $this->set('manpower_data', $manpower_data);
            // $this->set('diff_job',$diff_job);
            $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
            $this->redirect(array('controller'=>'BrmForecastBudgetDifference', 'action'=>'index'));
        }
    }

    public function getFbdData($term_id, $head_dept_id, $ba_code, $start_year, $end, $loginId)
    {
        $Common = new CommonController();
        $fbd_data = [];
        $start = $start_year;
        
        
        if (!empty($ba_code) && !empty($head_dept_id)) {
            $hqDeadline = array_shift($this->BrmTermDeadline->find('list', array(
            'fields' => array('deadline_date'),
            'conditions' => array('brm_term_id' => $term_id, 'hlayer_code' => $head_dept_id))));
        } else {
            $hqDeadline = $this->Session->read('HEADQUARTER_DEADLINE');
        }
        
        $current_dd = $this->BrmExpectedBudgetDiffAccount->find('first', array(
            'fields' => array('filling_date'),
            'conditions' => array(
                'brm_term_id' 	=> $term_id,
                'target_year' 	=> $start_year,
                'layer_code' 	=> $ba_code,
                'flag'		=> 1,
                'filling_date !=' => '',
            )
        ));
        $dept_id = $this->Layer->find('all',array(
            'fields' => 'parent_id',
            'conditions' => array('Layer.flag'=>1 , 'Layer.layer_code'=> $ba_code)
        ));
        $deptId = json_decode($dept_id[0]['Layer']['parent_id'], true);
        $fill_date = $current_dd['BrmExpectedBudgetDiffAccount']['filling_date'];
        $fbd_data['filling_date'] = (empty($fill_date) || $fill_date == null || $fill_date == '0000-00-00 00:00:00') ? '' : date("Y/m/d", strtotime($fill_date));
        $fbd_data['deadline_date'] = (empty($hqDeadline) || $hqDeadline == null || $hqDeadline == '0000-00-00 00:00:00') ? '' : date("Y/m/d", strtotime($hqDeadline));
        //Start of edited by Ei Thandar Kyaw
        $BudgetSummary = new BrmBudgetSummaryController;
        $years = range($start_year, $end);
        //$fbd_data['acc'] = $this->getDifferenceDataAccount($head_dept_id, $term_id, $start_year, $end, $ba_code);
        $fbd_data['budgetSummary'] = $BudgetSummary->getBudgetSummary($term_id, $head_dept_id, $deptId['L2'], $ba_code, 'FBD', $start_year, $end);
        
        #Get forecast period(eg: 2020-05) to show actual result data till to this period
        $forecastPeriod = $this->BrmTerm->getForecastPeriod($term_id);
        $i = 0;
        // change the array format according the view
        foreach($years as $key=>$year){

            $y1title = ($year == $start_year) ? $year.'年度 予算' : (($year-$start_year > 1) ? ($year-1).'年度 予算' : ($year-1).'年度 見込');
            $y2title = ($year == $start_year) ? $year.'年度 見込' : (($year-$start_year > 1) ? $year.'年度 予算' : $year.'年度 予算');
            foreach($fbd_data['budgetSummary'] as $account=>$value){
                $factor = $this->BrmExpectedBudgetDiffAccount->find('first', array(
                    'fields' => array('factor'),
                    'conditions' => array(
                        'brm_term_id' => $term_id,
                        'target_year' => $year,
                        'layer_code' => $ba_code,
                        'sub_acc_name' => $account
                    )
                ))['BrmExpectedBudgetDiffAccount']['factor'];
                $tmp[$key]['year1_title'] = $y1title;
                $tmp[$key]['year2_title'] = $y2title; 
                if($year == $start_year){
                    $tmp[$key]['data'][$account]['year1'] = number_format($value[$years[$i]]['budget']/1000, 1);
                    $tmp[$key]['data'][$account]['year2'] = number_format($value[$years[$i]]['forecast']/1000, 1);
                    $diff = $value[$years[$i]]['forecast'] - $value[$years[$i]]['budget'];
                }else{
                    if(isset($value[$years[$i-1]]['forecast'])) {
                        $tmp[$key]['data'][$account]['year1'] = number_format($value[$years[$i-1]]['forecast']/1000, 1);
                        $tmp[$key]['data'][$account]['year2'] = number_format($value[$years[$i]]/1000, 1);
                        $diff = $value[$years[$i]] - $value[$years[$i-1]]['forecast'];
                    }
                    else {
                        $tmp[$key]['data'][$account]['year1'] = number_format($value[$years[$i-1]]/1000, 1);
                        $tmp[$key]['data'][$account]['year2'] = number_format($value[$years[$i]]/1000, 1);
                        $diff = $value[$years[$i]] - $value[$years[$i-1]];
                    }
                    
                   
                }
                
                
                $tmp[$key]['data'][$account]['difference'] = number_format($diff/1000, 1);
                $tmp[$key]['data'][$account]['factor'] = ($factor != null && $factor != 'NULL') ? $factor : '';
                $tmp[$key]['data'][$account]['factor_year'] = $year;
                
            }$i++;
            
        }
        $fbd_data['acc'] = $tmp;
        // End of edited by Ei Thandar Kyaw
        //echo '<pre>';print_r($fbd_data['acc']);echo '</pre>';
        //echo '<pre>';print_r($fbd_data['acc']);echo '</pre>';exit;
        
        while ($start <= $end) {
            if ($start == $start_year) {
                $fbd_data['job'][] = $this->getDifferenceDataJob($term_id, $start_year, $start, $start, $ba_code);
            } else {
                $fbd_data['job'][] = $this->getDifferenceDataJob($term_id, $start_year, $start-1, $start, $ba_code);
            }
            
            $start++;
        }
        
        $cache_name = 'fbd_'.$term_id.'_'.$ba_code.'_'.$loginId;
        Cache::write($cache_name, $fbd_data);
        
        return $fbd_data;
    }

    public function saveData()
    {
        if ($this->Session->check('TERM_NAME')) {
            $budget_term = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }
        if (!empty($this->request->query('code'))) {
            $ba_code = $this->request->query('code');
        } else {
            $ba_code = $this->Session->read('SESSION_LAYER_CODE');
        }
        if (!empty($this->request->query('hq'))) {
            $hq_id = $this->request->query('hq');
        } else {
            $hq_id = $this->Session->read('HEAD_DEPT_CODE');
        }
        $this->layout = 'phase_3_menu';
        if ($this->request->is('post')) {
            $budget_years = explode('~', $budget_term);
            $start_year = $budget_years[0];

            $deadline_date = $this->request->data('deadline_date');
            $filling_date = $this->request->data('filling_date');
            $factors = $this->request->data('factors');
            
            
            try {
                if($_POST['mailSend']) {
                    $mail_template = 'common';
                    $mail['subject']        = $_POST['mailSubj'];
                    $mail['template_title'] = $_POST['mailTitle'];
                    $mail['template_body']  = $_POST['mailBody'];
                    $to_email  = $_POST['toEmail'];
                    $cc_email  = $_POST['ccEmail']; 
                    $bcc_email = $_POST['bccEmail']; 
                    //$url = '/BrmForecastBudgetDifference?hq='.$hq_id.'&term='.$term_id.'&code='.$ba_code;
                    $url = '/BrmForecastBudgetDifference/?hq='.$hq_id.'&term='.$term_id.'&code='.$this->Session->read('SESSION_LAYER_CODE');
                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);
                
                    $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

                    if ($sendMail["error"]) {
                        $msg = $sendMail["errormsg"];
                        $this->Flash->set($msg, array('key'=>'FbdfSave'));
                        $invalid_email = parent::getErrorMsg('SE042');
                        $this->Flash->set($invalid_email, array('key'=>'FbdfSave'));
                    } else {
                        #save data to FBD tables
                        $save_factor = $this->commonSave($term_id, $start_year, $ba_code, $factors, $filling_date, $deadline_date, $loginId);
                        $successMsg = parent::getSuccessMsg("SS018");
                        $this->Flash->set($successMsg, array('key'=>'FbdsSaveSuccess'));
                    } 
                }else{
                    $save_factor = $this->commonSave($term_id, $start_year, $ba_code, $factors, $filling_date, $deadline_date, $loginId);
                }
            } catch (Exception $e) {
                $attachDB->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                if (@ file_get_contents($tempPath)) {
                    unlink($tempPath);
                }
                $errorMsg = parent::getErrorMsg('SE015');
                $this->Flash->set($errorMsg, array('key'=>'FbdfSave'));
            }
            #return and show error message if occurs any error
            if (isset($save_factor['error']) || $save_factor['error']!= '') {
                $this->Flash->set($save_factor['error'], array("key"=>"FbdfSave"));
            } elseif (isset($save_factor['success']) || $save_factor['success']!= '') {
                $this->Flash->set($save_factor['success'], array("key"=>"FbdsSaveSuccess"));
            }

            $this->redirect(array('controller'=>'BrmForecastBudgetDifference','action'=>'index'));
        } else {
            $this->redirect('index');
        }
    }
    public function approveFBDData()
    {
        if ($this->Session->check('TERM_NAME')) {
            $budget_term = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }
        if (!empty($this->request->query('code'))) {
            $ba_code = $this->request->query('code');
        } else {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        }
        $this->layout = 'phase_3_menu';
        if ($this->request->is('post')) {
            $budget_years = explode('~', $budget_term);
            $start_year = $budget_years[0];

            $deadline_date = $this->request->data('deadline_date');
            $filling_date = $this->request->data('filling_date');
            $factors = $this->request->data('factors');
            try {
                if($_POST['mailSend']) {
                    $mail_template = 'common';
                    $mail['subject']        = $_POST['mailSubj'];
                    $mail['template_title'] = $_POST['mailTitle'];
                    $mail['template_body']  = $_POST['mailBody'];
                    $to_email  = $_POST['toEmail'];
                    $cc_email  = $_POST['ccEmail']; 
                    $bcc_email = $_POST['bccEmail']; 
                    
                    //$url = '/BrmForecastBudgetDifference/';
                    $url = '';
                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);
                
                    $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                    if ($sendMail["error"]) {
                        $msg = $sendMail["errormsg"];
                        $this->Flash->set($msg, array('key'=>'FbdfSave'));
                        $invalid_email = parent::getErrorMsg('SE042');
                        $this->Flash->set($invalid_email, array('key'=>'FbdfSave'));
                    }else{
                        $successMsg = parent::getSuccessMsg("SS018");
                        $this->Flash->set($successMsg, array('key'=>'FbdsSaveSuccess'));
                        #save data to FBD tables
                        $save_factor = $this->commonSave($term_id, $start_year, $ba_code, $factors, $filling_date, $deadline_date, $loginId);

                        #return and show error message if occurs any error
                    
                        if (isset($save_factor['error']) || $save_factor['error']!= '') {
                            $this->Flash->set($save_factor['error'], array("key"=>"FbdfSave"));
                            $this->redirect(array('controller'=>'BrmForecastBudgetDifference','action'=>'index'));
                        }
            
                        $save_summary = $this->saveBudgetSummaryData($term_id, $budget_term, $loginId, $ba_code);

                        #return and show error message if occurs any error
                        if (isset($save_summary['error']) || $save_summary['error']!= '') {
                            $this->Flash->set($save_summary['error'], array("key"=>"FbdfSave"));
                            $this->redirect(array('controller'=>'BrmForecastBudgetDifference','action'=>'index'));
                        }

                        foreach ($save_summary['success'] as $successMsg) {
                            $this->Flash->set($successMsg, array("key"=>"FbdsSaveSuccess"));
                        }
                        $this->redirect(array('controller'=>'BrmForecastBudgetDifference','action'=>'index'));
                    }
                }else{
                    #save data to FBD tables
                    $save_factor = $this->commonSave($term_id, $start_year, $ba_code, $factors, $filling_date, $deadline_date, $loginId);

                    #return and show error message if occurs any error
                
                    if (isset($save_factor['error']) || $save_factor['error']!= '') {
                        $this->Flash->set($save_factor['error'], array("key"=>"FbdfSave"));
                        $this->redirect(array('controller'=>'BrmForecastBudgetDifference','action'=>'index'));
                    }
            
                    $save_summary = $this->saveBudgetSummaryData($term_id, $budget_term, $loginId, $ba_code);

                    #return and show error message if occurs any error
                    if (isset($save_summary['error']) || $save_summary['error']!= '') {
                        $this->Flash->set($save_summary['error'], array("key"=>"FbdfSave"));
                        $this->redirect(array('controller'=>'BrmForecastBudgetDifference','action'=>'index'));
                    }

                    foreach ($save_summary['success'] as $successMsg) {
                        $this->Flash->set($successMsg, array("key"=>"FbdsSaveSuccess"));
                    }
                    $this->redirect(array('controller'=>'BrmForecastBudgetDifference','action'=>'index'));
                }
            }catch (Exception $e) {
                $attachDB->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                if (@ file_get_contents($tempPath)) {
                    unlink($tempPath);
                }
                $errorMsg = parent::getErrorMsg('SE015');
                $this->Flash->set($errorMsg, array('key'=>'FbdfSave'));
            }
            
        }
    }

    public function commonSave($term_id, $start_year, $ba_code, $factors, $filling_date, $deadline_date, $loginId)
    {
        $returnMsg = array();
        $save_acc = array();
        $update_acc = array();
        $save_job = array();
        $update_job = array();
        
        foreach ($factors['acc'] as $accname => $yearlyDta) {
            if (!empty($accname) || $accname != '') {
                foreach ($yearlyDta as $factor_year => $factor) {
                    $tmp = array();
                    $type = ($factor_year == $start_year)? 'forecast': 'budget';
                    
                    $fbdExist = $this->BrmExpectedBudgetDiffAccount->find('first', array(
                        'conditions' => array(
                            'BrmExpectedBudgetDiffAccount.brm_term_id' => $term_id,
                            'target_year' => $factor_year,
                            'layer_code' => $ba_code,
                            'sub_acc_name' => $accname,
                            'type' => $type
                        )
                    ));

                    $tmp['factor'] 	= $factor;
                    $tmp['deadline_date'] 	= $deadline_date;
                    $tmp['filling_date'] 	= $filling_date;
                    $tmp['updated_by'] 	= $loginId;
                    if (!empty($fbdExist)) {
                        $dta = $fbdExist['BrmExpectedBudgetDiffAccount'];
                        $tmp['id'] 	= $dta['id'];
                        $tmp['created_date'] 	= $dta['created_date'];
                        $update_acc[] = $tmp;
                    } else {
                        $tmp['brm_term_id'] 		= $term_id;
                        $tmp['target_year'] 	= $factor_year;
                        $tmp['layer_code'] 		= $ba_code;
                        $tmp['sub_acc_name'] 	= $accname;
                        $tmp['amount']			= 0;
                        $tmp['type'] 			= $type;
                        $tmp['created_by'] 	= $loginId;

                        $save_acc[] = $tmp;
                    }
                }
            }
        }

        foreach ($factors['job'] as $fieldName => $yearlyDta) {
            if (!empty($fieldName) || $fieldName != '') {
                foreach ($yearlyDta as $factor_year => $factor) {
                    $tmp = array();
                    $type = ($factor_year == $start_year)? 'forecast': 'budget';
                    
                    $fbdExist = $this->BrmExpectedBudgetDiffJob->find('first', array(
                        'conditions' => array(
                            'BrmExpectedBudgetDiffJob.brm_term_id' => $term_id,
                            'target_year' => $factor_year,
                            'layer_code' => $ba_code,
                            'name_jp' => $fieldName,
                            'type' => $type
                        )
                    ));

                    $tmp['factor'] 	= $factor;
                    $tmp['updated_by'] 	= $loginId;
                    if (!empty($fbdExist)) {
                        $dta = $fbdExist['BrmExpectedBudgetDiffJob'];
                        $tmp['id'] 	= $dta['id'];
                        $tmp['created_date'] 	= $dta['created_date'];
                        $update_job[] = $tmp;
                    } else {
                        $tmp['brm_term_id'] 		= $term_id;
                        $tmp['target_year'] 	= $factor_year;
                        $tmp['layer_code'] 		= $ba_code;
                        $tmp['name_jp'] 	= $fieldName;
                        $tmp['amount']			= 0;
                        $tmp['type'] 			= $type;
                        $tmp['created_by'] 	= $loginId;

                        $save_job[] = $tmp;
                    }
                }
            }
        }
        
        if ((!empty($save_acc) || !empty($update_acc)) || (!empty($save_job) || !empty($update_job))) {
            $jobDB = $this->BrmExpectedBudgetDiffJob->getDataSource();
            $accDB = $this->BrmExpectedBudgetDiffAccount->getDataSource();

            try {
                $jobDB->begin();
                $accDB->begin();

                #Save Data
                if (!empty($save_acc)) {
                    $this->BrmExpectedBudgetDiffAccount->saveAll($save_acc);
                }
                if (!empty($save_job)) {
                    $this->BrmExpectedBudgetDiffJob->saveAll($save_job);
                }

                #Update Data
                if (!empty($update_acc)) {
                    $this->BrmExpectedBudgetDiffAccount->saveMany($update_acc);
                }
                if (!empty($update_job)) {
                    $this->BrmExpectedBudgetDiffJob->saveMany($update_job);
                }

                $jobDB->commit();
                $accDB->commit();

                $returnMsg['success'] = parent::getSuccessMsg('SS001');
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                $jobDB->rollback();
                $accDB->rollback();
                $returnMsg['error'] = parent::getErrorMsg('SE003');
            }
        } else {
            $returnMsg['error'] = parent::getErrorMsg('SE017', 'Save');
        }

        return $returnMsg;
    }


    
    public function downloadForecastBudget()
    {
        $yearsArr = [];
        $business_code = explode('/', $this->request->data('ba_code'));
        $ba_code = $business_code[0];
        $ba_name = $business_code[1];
        $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        $term_id = $this->Session->read('TERM_ID');
        $loginId = $this->Session->read('LOGIN_ID');
        $budget_term = $this->Session->read('TERM_NAME');
        if ($this->Session->check('HEADQUARTER_DEADLINE')) {
            $hqDeadline = $this->Session->read('HEADQUARTER_DEADLINE');
        }

        $file_name = 'BrmForecastBudgetDifference';
        $PHPExcel = $this->PhpExcel;
        $language = $this->Session->read('Config.language');
        $this->DownloadExcel($term_id, $budget_term, $ba_code, $loginId, $file_name, $PHPExcel, $language);
        $this->render('index');
    }

    public function DownloadExcel($term_id, $budget_term, $ba_code, $loginId, $file_name, $PHPExcel, $language, $save_into_tmp=false)
    {
        $years = explode('~', $budget_term);
        $start_year = $years[0];
        $start = $years[0];
        $end = $years[1];
        $fbd_data = [];
        $cache_name = 'fbd_'.$term_id.'_'.$ba_code.'_'.$loginId;
        $fbd_data = Cache::read($cache_name);
        $getBAName = $this->Layer->find('first', array(
            'fields' => array('name_en', 'name_jp'),
            // 'conditions'=> array('Layer.layer_code'=>$ba_code,'Layer.flag'=>1)
        ));
        if (!empty($getBAName)) {
            $ba_name = ($language == 'eng')? $getBAName['Layer']['name_en'] : $getBAName['Layer']['name_jp'];
        }
        
        $bg_color = ($this->BrmBudgetApprove->find('first', array(
            'fields' => array('BrmBudgetApprove.flag'),
            'conditions' => array(
                'BrmBudgetApprove.brm_term_id' 	=> $term_id,
                'BrmBudgetApprove.layer_code' 	=> $ba_code,
            )
        ))['BrmBudgetApprove']['flag']==2)? 'f9f9f9' : 'd5f4ff';
                
        $objPHPExcel = $PHPExcel->createWorksheet()->setDefaultFont('Cambria', 12);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel ->getActiveSheet()->setShowGridlines(true);
        $objPHPExcel ->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->setTitle(__('予測と予算の違い'));
        $sheet = $PHPExcel->getActiveSheet();
        #lock for excel file - not allow to edit
        $sheet->getProtection()->setPassword('*****');
        $sheet->getProtection()->setSheet(true);
        #lock to insert new column and row
        $sheet->getProtection()->setInsertRows(true);
        $sheet->getProtection()->setInsertColumns(true);

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

        $font_size = array(
            'font'  => array(
                'size'  => 18
            )
        );

        $negative = array(
            'font'  => array(
                'color' => array(
                    'rgb' => 'FF0000'
            )

        ));
        
        if (!empty($fbd_data)) {
            $accloop = 0;
            $jobloop = 0;
            $start_row = 5;
            $start_col = 1;
            foreach ($fbd_data['acc'] as $accountData) {
                $accloop++;
                $header_acc = $accloop.'. '.str_replace(' ', '', $accountData['year1_title']).' '.__(' 対 ').' '.str_replace(' ', '', $accountData['year2_title']);
                
                #set column width
                $sheet->getColumnDimension($sheet->getCellByColumnAndRow($start_col, $start_row)->getColumn())->setWidth(17);
                $sheet->getColumnDimension($sheet->getCellByColumnAndRow($start_col+1, $start_row)->getColumn())->setWidth(12);
                $sheet->getColumnDimension($sheet->getCellByColumnAndRow($start_col+2, $start_row)->getColumn())->setWidth(12);
                $sheet->getColumnDimension($sheet->getCellByColumnAndRow($start_col+3, $start_row)->getColumn())->setWidth(12);
                $sheet->getColumnDimension($sheet->getCellByColumnAndRow($start_col+4, $start_row)->getColumn())->setWidth(23);
                $sheet->getColumnDimension($sheet->getCellByColumnAndRow($start_col+5, $start_row)->getColumn())->setWidth(2);
                                                
                $sheet->setCellValueByColumnAndRow($start_col, $start_row, $header_acc);
                $sheet->mergeCells($sheet->getCellByColumnAndRow($start_col, $start_row)->getColumn().$start_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $start_row)->getColumn().$start_row);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $start_row)->getColumn().$start_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $start_row)->getColumn().$start_row)->applyFromArray($alignleft);

                #set font bold
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $start_row)->getColumn().$start_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $start_row)->getColumn().$start_row)->applyFromArray($title);
                
                $tmp_row = $start_row+1;
                $sheet->setCellValueByColumnAndRow($start_col, $tmp_row, __('（単位：千円）'));
                $sheet->mergeCells($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignright);

                #set font bold
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($title);

                $tmp_row = $start_row+2;
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');

                #set font bold
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($title);

                $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row, __('科目'));
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row)->applyFromArray($aligncenter);

                $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row, $accountData['year1_title']);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row)->applyFromArray($aligncenter);

                $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row, $accountData['year2_title']);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row)->applyFromArray($aligncenter);

                $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row, __('差異'));
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->applyFromArray($aligncenter);

                $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row, __('増減要因'));
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($aligncenter);
                
                
                $tmp_row = $start_row+3;
                foreach ($accountData['data'] as $sub_acc_name => $value) {
                    #set row height
                    $sheet->getRowDimension($tmp_row)->setRowHeight(40);

                    $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row, $sub_acc_name);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignleft);

                    $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row, str_replace(',', '', $value['year1']));
                    //$sheet->getStyle('C'.$tmp_row)->getNumberFormat()->setFormatCode("#,##0.0_);[Red]\(#,##0.0\);\"-\"??_);_(@_)");
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row)->getNumberFormat()->setFormatCode('#,##0.0_);[Red]\-#,##0.0');
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignright);
                            
                    $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row, str_replace(',', '', $value['year2']));
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row)->getNumberFormat()->setFormatCode('#,##0.0_);[Red]\-#,##0.0');
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignright);

                    $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row, str_replace(',', '', $value['difference']));
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->getNumberFormat()->setFormatCode('#,##0.0_);[Red]\-#,##0.0');
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignright);

                    $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row, $value['factor']);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignleft);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);

                    // #set background color
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');

                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                    $tmp_row++;
                }
                $start_col = $start_col+6;
            }

            $start_row = $sheet->getHighestRow()+2;
            $start_col = 1;
            foreach ($fbd_data['job'] as $jobData) {
                $accloop ++;
                $header_job = $accloop.'. '.__('人員増減').' '.str_replace(' ', '', $jobData['year1_title']).' '.__(' 対 ').' '.str_replace(' ', '', $jobData['year2_title']);
            
                $sheet->setCellValueByColumnAndRow($start_col, $start_row, $header_job);
                $sheet->mergeCells($sheet->getCellByColumnAndRow($start_col, $start_row)->getColumn().$start_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $start_row)->getColumn().$start_row);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $start_row)->getColumn().$start_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $start_row)->getColumn().$start_row)->applyFromArray($alignleft);

                #set font bold
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $start_row)->getColumn().$start_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $start_row)->getColumn().$start_row)->applyFromArray($title);

                $tmp_row = $start_row+1;
                $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row, '('.__('単位：人').')');
                $sheet->mergeCells($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignright);

                // set font bold
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($title);

                $tmp_row = $start_row+2;
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');

                #set font bold
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($title);

                $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row, __('ポジション'));
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row)->applyFromArray($aligncenter);

                $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row, $jobData['year1_title']);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row)->applyFromArray($aligncenter);

                $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row, $jobData['year2_title']);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row)->applyFromArray($aligncenter);

                $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row, __('差異'));
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->applyFromArray($aligncenter);

                $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row, __('備考'));
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($aligncenter);
                
                $tmp_row = $start_row+3;
                foreach ($jobData['data'] as $field_name => $fvalue) {
                    // #set row height
                    $sheet->getRowDimension($tmp_row)->setRowHeight(40);

                    $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row, $field_name);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignleft);

                    $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row, str_replace(',', '', $fvalue['year1']));
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignright);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row)->getNumberFormat()->setFormatCode('#,##0.0_);[Red]\-#,##0.0');

                    $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row, str_replace(',', '', $fvalue['year2']));
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignright);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+2, $tmp_row)->getColumn().$tmp_row)->getNumberFormat()->setFormatCode('#,##0.0_);[Red]\-#,##0.0');

                    $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row, str_replace(',', '', $fvalue['difference']));
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignright);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->getNumberFormat()->setFormatCode('#,##0.0_);[Red]\-#,##0.0');

                    $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row, $fvalue['factor']);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($border_thin);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->applyFromArray($alignleft);
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);

                    #set background color
                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+1, $tmp_row)->getColumn().$tmp_row.':'.$sheet->getCellByColumnAndRow($start_col+3, $tmp_row)->getColumn().$tmp_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');

                    $sheet->getStyle($sheet->getCellByColumnAndRow($start_col+4, $tmp_row)->getColumn().$tmp_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);

                    $tmp_row++;
                }
                $start_col = $start_col+6;
            }
        }
       
        $sheet->getColumnDimension('A')->setWidth(2); 
        $objPHPExcel->getActiveSheet()->getStyle('B1:'.$sheet->getHighestColumn().$sheet->getHighestRow())->getAlignment()->setWrapText(true);
                        
        $sheet->setCellValue('B3', __('見込対予算増減一覧'));
        $sheet->mergeCells('B3:'.$sheet->getHighestColumn().'3');
        $sheet->getRowDimension(3)->setRowHeight(42);
        $sheet->getStyle('B3:'.$sheet->getHighestColumn().'3')->applyFromArray($aligncenter);
        $sheet->getStyle('B3:'.$sheet->getHighestColumn().'3')->applyFromArray($font_size);
        $sheet->getStyle('B3:'.$sheet->getHighestColumn().'3')->applyFromArray($title);
        
        $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col-3, $start_row)->getColumn().'1', __('提出日'));
        $sheet->getStyle($sheet->getCellByColumnAndRow($start_col-3, $start_row)->getColumn().'1')->applyFromArray($alignleft);
        $sheet->getStyle($sheet->getCellByColumnAndRow($start_col-3, $start_row)->getColumn().'1')->applyFromArray($title);
        
        $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col-2, $start_row)->getColumn().'1', $fbd_data['filling_date']);
        $sheet->getStyle($sheet->getCellByColumnAndRow($start_col-2, $start_row)->getColumn().'1')->applyFromArray($aligncenter);
        $sheet->getStyle($sheet->getCellByColumnAndRow($start_col-2, $start_row)->getColumn().'1')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
        $layer = $this->Layer->find('first', array(
            'conditions' => array('Layer.flag' => '1', 'Layer.layer_code' => $ba_code),
            'fields' => array('Layer.layer_code', 'LayerType.name_en', 'LayerType.name_jp'),
        ));
        if ($language == 'eng') {
            $lang_name = 'en';
        } else {
            $lang_name = 'jp';
        }
        $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col-3, $start_row)->getColumn().'2', $layer['LayerType']['name_'.$lang_name]);
        $sheet->getStyle($sheet->getCellByColumnAndRow($start_col-3, $start_row)->getColumn().'2')->applyFromArray($alignleft);
        $sheet->getStyle($sheet->getCellByColumnAndRow($start_col-3, $start_row)->getColumn().'2')->applyFromArray($title);

        $sheet->setCellValue($sheet->getCellByColumnAndRow($start_col-2, $start_row)->getColumn().'2', $ba_name.'('.$ba_code.')');
        $sheet->getStyle($sheet->getCellByColumnAndRow($start_col-2, $start_row)->getColumn().'2')->applyFromArray($aligncenter);
        
        $sheet->getStyle($sheet->getCellByColumnAndRow($start_col-2, $start_row)->getColumn().'1:'.$sheet->getCellByColumnAndRow($start_col-2, $start_row)->getColumn().'2')->applyFromArray($border_thin);
        $sheet->getStyle($sheet->getCellByColumnAndRow($start_col-3, $start_row)->getColumn().'1:'.$sheet->getCellByColumnAndRow($start_col-3, $start_row)->getColumn().'2')->applyFromArray($border_thin);
        
        if ($save_into_tmp) {
            $PHPExcel->save($file_name);
        } else {
            $PHPExcel->output($file_name.".xlsx");
        }
        $this->autoLayout = false;
    }


    /**
     *
     * @author Ei Thandar Kyaw on 11/08/2020
     * @return json array
     */
    public function saveBudgetSummaryData($term_id, $term_name, $loginId, $ba_code, $ba_name='', $headDepId='', $page='')
    {
        if ($ba_name=='' && $this->Session->check('BUDGET_BA_NAME')) {
            $ba_name = $this->Session->read('BUDGET_BA_NAME');
        }
        $Common = new CommonController;
        if ($headDepId=='') {
            $headDepId = $this->Session->read('HEAD_DEPT_CODE');
        }
        $returnMsg = array();
        $budgetSummary = [];

        $budget_term = explode("~", $term_name);
        $termRange = range($budget_term[0], $budget_term[1]);
        
        // remove first year
        array_shift($termRange);

        $Months = $Common->get12Month($term_id);
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        // create condition to delete for budget log
        $delete_log = array(
            "brm_term_id" => $term_id,
            "hlayer_code" => $headDepId,
            "layer_code" => $layer_code
        );

        $budget_start_month 	= $Common->getMonth($termRange[0], $term_id, 'start');
        // create condition to delete for budget
        $delete_budget = array(
            "brm_term_id" => $term_id,
            "hlayer_code" => $headDepId,
            "layer_code" => $layer_code,
            "target_month >=" => $budget_start_month,
        );

        // check is approved
        $isApproved = $this->BrmBudget->checkApprove($term_id, $headDepId, $ba_code);
        if ($isApproved == 2 && $page != 'BrmSummary') {
            $returnMsg['error'] = parent::getErrorMsg('SE018');
        }
        // else if ($isApproved == 3) $returnMsg['error'] = parent::getErrorMsg('SE091');
        else {

            // create budget data accoring years
            $budgetData = $this->BrmBudgetPrime->find('all', array(
                'fields' => array(
                    'target_year','BrmBudgetPrime.brm_account_id','account_code','logistic_index_no','sum(month_1_amt) As month_1_amt' ,'sum(month_2_amt) As month_2_amt' ,'sum(month_3_amt) As month_3_amt' ,'sum(month_4_amt) As month_4_amt' ,'sum(month_5_amt) As month_5_amt' ,'sum(month_6_amt) As month_6_amt' ,'sum(month_7_amt) As month_7_amt' ,'sum(month_8_amt) As month_8_amt' ,'sum(month_9_amt) As month_9_amt' ,'sum(month_10_amt) As month_10_amt' ,'sum(month_11_amt) As month_11_amt' ,'sum(month_12_amt) As month_12_amt'
                ),
                'conditions' => array(
                    'BrmBudgetPrime.brm_term_id' => $term_id,
                    'target_year' => $termRange,
                    'layer_code' => $ba_code,
                    'BrmBudgetPrime.flag' => 1
                ),
                'group' => array('target_year,layer_code,BrmBudgetPrime.brm_account_id,account_code,logistic_index_no')
            ));
            
            $subacc_names = $this->BrmAccount->find('list', array(
                'fields' => array('id','name_jp'),
                'conditions' => array(
                    'flag' => 1,
                    'group_code' => '01',
                )
            ));
            $today_date = date("Y/m/d") ;
            
            
            $middleLayer = Setting::LAYER_SETTING['middleLayer'];
            $ba_data = $this->Layer->find('list', array(
                'fields' => array('parent_id'),
                'conditions' => array(
                    'layer_code' => $layer_code,
                    'flag' => 1,
                    'to_date >=' => $today_date
                )
            ));
            //print_r(json_decode($ba_data[$ba_code], true));exit;
            $baArr = json_decode($ba_data[$ba_code], true);
            
            // if no data to approve , will show error message
            if (!empty($budgetData)) {
                foreach ($budgetData as $data) { // for one year
                    $year 			= $data['BrmBudgetPrime']['target_year'];
                    $sub_acc_id 	= $data['BrmBudgetPrime']['sub_acc_id'];
                    $sub_acc_name 	= $subacc_names[$sub_acc_id];
                    $account_code 	= $data['BrmBudgetPrime']['account_code'];
                    $log_index_no 	= $data['BrmBudgetPrime']['logistic_index_no'];

                    $start_month 	= $Common->getMonth($year, $term_id, 'start');

                    #Loop through 12 months
                    for ($i=0; $i <12 ; $i++) {
                        $tmp = array();
                        #Increase month by loop count
                        $month = date("Y-m", strtotime($start_month. "last day of + ".$i." Month"));
                        #get database field name
                        $field = 'month_'.($i+1).'_amt';

                        $amount = $data[0][$field];
                        // if ($amount != 0) {
                        $budget_id_amount = $this->BrmBudget->find(
                            'first',
                            array(
                                'fields' => array(
                                    'id','amount'),
                                'conditions' => array(
                                    'brm_term_id' => $term_id,
                                    'hlayer_code' => $headDepId,
                                    'dlayer_code' => $baArr['L'.$middleLayer],
                                    'account_code' => $account_code,
                                    'target_month' => $month,
                                    'layer_code' => $layer_code,
                                    'flag' => 1,
                                    'logistic_index_no' => $log_index_no
                                ))
                        );
                        if (!empty($budget_id_amount)) {
                            $tmp['id'] = $budget_id_amount['BrmBudget']['id'];
                        }
                        $tmp['target_month'] = $month;
                        $tmp['brm_term_id'] = $term_id;
                        $tmp['hlayer_code'] = $headDepId;
                        $tmp['dlayer_code'] = $baArr['L'.$middleLayer];
                        $tmp['layer_code'] = $layer_code;
                        $tmp['team'] = '';
                        $tmp['sub'] = '';
                        $tmp['brm_saccount_id'] = '';
                        $tmp['account_code'] = $account_code;
                        $tmp['amount'] = $amount;
                        $tmp['logistic_index_no'] = $log_index_no;
                        $tmp['flag'] = 1;
                        $tmp['type'] = 0;
                        $tmp['created_by'] = $loginId;
                        $tmp['updated_by'] = $loginId;

                        $budgetSummary[] = $tmp;
                        // }
                    }
                }
            }
            
            try {
                $this->BrmBudgetApprove->begin();
                $this->BrmBudget->begin();

                // create params for budget log
                $logParam = array(
                    'brm_term_id' => $term_id,
                    'hlayer_code' => $headDepId,
                    'dlayer_code' => $baArr['L'.$middleLayer],
                    'layer_code' => $layer_code,
                    'flag' => 2,
                    'created_by' => $loginId,
                    'updated_by' => $loginId,
                    'created_date' => date("Y-m-d H:i:s"),
                );
                
                /* added by HHK */
                $deadline = '';
                if ($page != 'BrmSummary') {
                    $this->BrmBudgetApprove->deleteAll($delete_log, false);
                
                    $logSuccess = $this->BrmBudgetApprove->saveAll($logParam);
                }

                if (!empty($budgetSummary)) {
                    //$this->BrmBudget->deleteAll($delete_budget, false);
                    $budgetSuccess = $this->BrmBudget->saveAll($budgetSummary);
                    $this->BrmBudget->commit();
                }
                $this->BrmBudgetApprove->commit();
                if ($page != 'BrmSummary') {
                    $returnMsg['success'][] = parent::getSuccessMsg("SS022", $ba_name);
                } else {
                    $returnMsg ="success";
                }
               
            } catch (Exception $e) {
                $this->BrmBudgetApprove->rollback();
                $this->BrmBudget->rollback();

                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                if ($page != 'BrmSummary') {
                    $returnMsg['error'] = parent::getErrorMsg('SE040');
                } else {
                    $returnMsg = "error";
                }
            }
        }
        
        return $returnMsg;
    }

    /* Approve Cancel function */
    /**
     *
     * @author Ei Thandar Kyaw on 11/08/2020
     * @return json array
     */
    public function approveCancel()
    {
        $Common = new CommonController;
        
        if ($this->Session->check('BUDGET_BA_NAME')) {
            $ba_name = $this->Session->read('BUDGET_BA_NAME');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if (!empty($this->request->query('code'))) {
            $ba_code = $this->request->query('code');
        } else {
            $ba_code = $this->Session->read('SESSION_LAYER_CODE');
        }
        if ($this->Session->check('TERM_NAME')) {
            $budget_term = $this->Session->read('TERM_NAME');
        }

        $this->layout = 'phase_3_menu';
        $terms = explode('~', $term_name);
        $start_year = $terms[0];
        $end_year = $terms[1];

        if ($this->request->is('post')) {
            $requestData = $this->request->data;

            $deadline_date = $requestData['deadline_date'];
            $headDepId = $this->Session->read('HEAD_DEPT_CODE');
            
            if ($this->Session->check('LOGIN_ID')) {
                $loginId = $this->Session->read('LOGIN_ID');
            }
            //echo $term_id.', '.$headDepId.', '.$ba_code;exit;
            $isApproved = $this->BrmBudget->checkApprove($term_id, $headDepId, $ba_code);
            if ($isApproved == 1) {
                $errMsg = parent::getErrorMsg('SE039');
                $this->Flash->set($errorMsg, array('key'=>'FbdfSave'));
            } else {
                try {
                    if($_POST['mailSend']) {
                        $mail_template = 'common';
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body']  = $_POST['mailBody'];
                        $to_email  = $_POST['toEmail'];
                        $cc_email  = $_POST['ccEmail']; 
                        $bcc_email = $_POST['bccEmail']; 
                        
                        $url = '/BrmForecastBudgetDifference/';
                        $toEmail = parent::formatMailInput($to_email);
                        $ccEmail = parent::formatMailInput($cc_email);
                        $bccEmail = parent::formatMailInput($bcc_email);
                    
                        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                        if ($sendMail["error"]) {
                            $msg = $sendMail["errormsg"];
                            $this->Flash->set($msg, array('key'=>'FbdfSave'));
                            $invalid_email = parent::getErrorMsg('SE042');
                            $this->Flash->set($invalid_email, array('key'=>'FbdfSave'));
                        }else{
                            $this->BrmBudgetApprove->begin();
                            $this->BrmBudgetApprove->updateAll(
                                array(
                                    "flag"=>'1',
                                    "updated_by"=> $loginId,
                                    "updated_date"=>'"'.date("Y-m-d H:i:s").'"'
                                ),
                                array(
                                    "BrmBudgetApprove.brm_term_id"=>$term_id,
                                    "BrmBudgetApprove.hlayer_code"=>$headDepId,
                                    "BrmBudgetApprove.layer_code"=>$ba_code
                                )
                            );
                            $this->BrmBudgetApprove->commit();
                            $successMsg = parent::getSuccessMsg('SS023', $ba_name);
                            $this->Flash->set($successMsg, array("key"=>"FbdsSaveSuccess"));
                        }
                    }else{
                        $this->BrmBudgetApprove->begin();
                        $this->BrmBudgetApprove->updateAll(
                            array(
                                "flag"=>'1',
                                "updated_by"=> $loginId,
                                "updated_date"=>'"'.date("Y-m-d H:i:s").'"'
                            ),
                            array(
                                "BrmBudgetApprove.brm_term_id"=>$term_id,
                                "BrmBudgetApprove.hlayer_code"=>$headDepId,
                                "BrmBudgetApprove.layer_code"=>$ba_code
                            )
                        );
                        $this->BrmBudgetApprove->commit();
                        $successMsg = parent::getSuccessMsg('SS023', $ba_name);
                        $this->Flash->set($successMsg, array("key"=>"FbdsSaveSuccess"));
                    }
                    
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                    $this->BrmBudgetApprove->rollback();
                    // $this->BrmExpectedBudgetDiffAccount->rollback();
                    // $this->BrmExpectedBudgetDiffJob->rollback();

                    $errorMsg = parent::getErrorMsg('SE089');
                    $this->Flash->set($errorMsg, array('key'=>'FbdfSave'));
                }
            }
            $this->redirect(array('controller'=>'BrmForecastBudgetDifference','action'=>'index'));
        } else {
            $this->redirect('index');
        }
    }


    /* added by Hein Htet Ko */
    public function mailSend($approve_str, $deadline, $ba_name, $ba_code)
    {
        $Common = new CommonController;

        $level_9 	= AdminLevel::GENERAL_MANAGER;

        $level_4 	= AdminLevel::ACCOUNT_INCHARGE;
        $level_6	= AdminLevel::BUSINESS_ADMINISTRATIOR;
        $level_11 	= AdminLevel::BUDGET_MANAGER;
        $level_10 	= AdminLevel::BUDGET_INCHARGE;
        $level_5 	= AdminLevel::BUSINESS_MANAGER;
        $level_8	= AdminLevel::DEPUTY_GENERAL_MANAGER;

        if ($this->Session->check('LOGIN_USER')) {
            $login_user_name = $this->Session->read('LOGIN_USER');
        }
        if ($this->Session->check('HEAD_DEPT_ID')) {
            $hq_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('TERM_NAME')) { //to get term name
            $term_name = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('ADMIN_LEVEL_ID')) {
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        }

        $terms = explode('~', $term_name);
        $start_year = $terms[0];
        $end_year = $terms[1];

        $ccEmail  = array();
        $toEmail = array();

        $mail_template 	= 'common';

        $relatedBAs = array_values($Common->getAllBAOfSameHQ($hq_id));
    
        if ($approve_str=='approve') { # Approve

            # url link to access without choosing session
            $url ='';
            $toLevel = array($level_9);
            // $toEmail = array_filter($Common->getEmail($toLevel));
            $toEmail = $this->getEmailByBA($relatedBAs, $toLevel);

            # get to email address from user table
            $ccLevel = array($level_6,$level_11,$level_10,$level_5,$level_8);
            $ccEmail = $this->getEmailByBA($ba_code, $ccLevel);
            $ccEmail = array_filter($ccEmail);

            # get to email address from user table by ba code
            $mail['subject']	 	= '【予算策定】 '.$ba_name.' 部署によるデータ入力承認完了通知';
            $mail['template_title'] = '各位';
            $mail['template_body'] 	= ' '.$ba_name.' の '.$start_year.' 年度見込～'.$end_year.' 年度予算が承認されました。';
        } else {
            # url link to access without choosing session
            $url = '/BrmForecastBudgetDifference?hq='.$hq_id.'&term='.$term_id.'&code='.$ba_code;
            $toLevel = array($level_6,$level_11,$level_10);
            $toEmail = array_filter($this->getEmailByBA($ba_code, $toLevel));

            # get to email address from user table by ba code
            $ccEmail1 = $this->getEmailByBA($relatedBAs, array($level_9));
            $ccEmail2 = $this->getEmailByBA($ba_code, array($level_5, $level_8));
            $ccEmail = array_filter(array_merge($ccEmail1, $ccEmail2));

            /* day of week */
            $dys = array("日","月","火","水","木","金","土");

            $day = date("w", strtotime($deadline));
            $deadline_date = date("n\月d\日（".$dys[$day]."）", strtotime($deadline));

            //$deadline_date = $this->request->data('deadline_date');

            //$ddate = explode('/',$deadline_date);
            // $date = explode('-',date('Y-m-d-N',strtotime($ddate[2].'-'.$ddate[1].'-'.$ddate[0])));
            //$date = explode('/',date('m/d/N',strtotime($deadline_date)));
            //$ccEmail = array_merge($ccEmail,$loginLevelEmail);
            # get to email address from user table
            $mail['subject']	 	= '【予算策定】 '.$ba_name.' 部署によるデータ入力 承認キャンセル通知';
            $mail['template_title'] = '各位';
            $mail['template_body'] 	= ' '.$ba_name.'  の '.$start_year.' 年度見込～ '.$end_year.' 年度予算のデータを確認した結果、承認がキャンセルされました。<br>
			内容を再度確認し、改めて依頼してください。<br><br>
			提出期日：'.$deadline_date.'営業時間内';
        }
        

        if (!empty($toEmail)) {
            $period = '';
            
            $sentMail = parent::sendEmail($ba_code, $ba_name, $period, $login_user_name, $toEmail, $ccEmail, $mail_template, $mail, $url);
            return $sentMail;
        } else {
            $sentMail = 'MailNotFound';
            return $sentMail;
        }
    }

    /* added by Hein Htet Ko */
    public function getEmailByBA($ba_code, $id)
    {
        $mails = $this->UserModel->find('list', array(
            'fields'=>'email',
            'conditions'=>array(
                'admin_level_id'=>$id,
                'layer_code'=>$ba_code,
                'flag'=>1
            )
        ));

        return array_unique($mails);
    }

    public function getDifferenceDataAccount($head_dept_id, $term_id, $start_year, $end_year, $ba_code)
    {
        $Common = new CommonController();
        $returnData = [];
        $resultData = [];
        $budgetData = [];
        $result_arr = [];
        $net_profit = [];

        $years = range($start_year, $end_year);

        #Get budget start and end month
        $start_month = $Common->getMonth($start_year, $term_id, 'start');
        $end_month = $Common->getMonth($start_year, $term_id, 'end');

        $total_months = array('first_half','second_half','whole_total');

        $codes_pair = $this->getCodesPair('01');

        $group2_accs = $this->BrmSaccount->find('list', array(
            'fields' => 'sub_acc_name_jp',
            'conditions' => array(
                'group_code' => '02',
                'flag' => 1
            )
        ));

        $total_fields = $this->BrmSaccount->find('list', array(
            'fields' => 'sub_acc_name_jp',
            'conditions' => array(
                'group_code' => '02',
                'type !=' => 0,
                'flag' => 1
            )
        ));


        #Get forecast period(eg: 2020-05) to show actual result data till to this period
        $forecastPeriod = $this->BrmTerm->getForecastPeriod($term_id);
        // $forecastPeriod = $termData[0]['tbl_term']['forecast_period'];

        $manual_tax_ba = Setting::BA_BUDGET_TAX;
        $yearly_tax = $this->RtaxFee->find('list', array(
            'fields' => array('target_year','tax_amount'),
            'conditions' => array('flag' => 1)
        ));

        foreach ($years as $each_year) {
            $modelName = ($each_year == $start_year) ? 'BrmExpected' : 'BrmBudgetPrime';
            # for sub account pari
            $account_pair = $Common->getPairedAccount($head_dept_id, $each_year);
            
            foreach ($account_pair as $sub_name => $main_name) {
                $codes_arr = $codes_pair[$sub_name];
                $acc_codes = "'".join("','", $codes_arr)."'"; #change from array to string
                
                #Get Actual result data
                if ((!empty($forecastPeriod) || $forecastPeriod!='') && $each_year==$start_year) {
                    #get budget start and end month
                    $start_month = $Common->getMonth($start_year, $term_id, 'start');
                    // $this->BrmActualResultSummaryModel->virtualFields['result_total'] = 'SUM(amount)';
                    $this->BrmActualResultSummaryModel->virtualFields['result_total'] = 'ROUND(SUM(amount)/1000,3)*1000';
                    $actualResultData = $this->BrmActualResultSummary->find('list', array(
                                'fields' => array('target_month','result_total'),
                                'conditions' => array(
                                    'hlayer_code' => $head_dept_id,
                                    'layer_code' => $ba_code,
                                    'account_code' => $codes_arr,
                                    'target_month >=' => $start_month,
                                    'target_month <=' => $forecastPeriod //$end_month edit by NNL
                                ),
                                'group' => 'target_month',
                                'order' => 'target_month ASC',
                            ));

                    $intervalEnd = date("Y-m", strtotime($forecastPeriod. "last day of + 1 Month"));
                    
                    $interval = DateInterval::createFromDateString('1 months') ;
                    
                    $lockPeriods = new DatePeriod(new DateTime($start_month), $interval, new DateTime($intervalEnd)) ;
                    
                    foreach ($lockPeriods as $target_month) {
                        $target_month = $target_month->format('Y-m');
                        $amount = $actualResultData[$target_month];
                        $tg_month = date('n', strtotime($target_month));
                        $col = $Common->getMonthColumn($tg_month, $term_id);
                        $resultData[$start_year][$sub_name]['month_'.$col.'_amt'] = (!empty($amount)) ? $amount : 0;
                    }
                }

                #Get budget data
                if ($each_year == $start_year) {
                    $ba_codes = "'".$ba_code."'";

                    $budget = $this->BrmBudget->getYearlyBudget($ba_codes, $term_id, $start_month, $end_month, $acc_codes);
                    $budgetData[$each_year][$main_name] += $budget;
                }
                
                
                $year1_data = $this->$modelName->getMonthlyResultSummary($term_id, $acc_codes, $each_year, '', $ba_code);

                $ba_value = $year1_data[0][0];

                if (!empty($ba_value)) {
                    $yearly_amt = 0;
                    foreach ($ba_value as $col => $value) {
                        if (!in_array($col, $total_months)) {
                            $monthly_amt = (isset($resultData[$each_year][$sub_name][$col])) ? $resultData[$each_year][$sub_name][$col] : $value;
                            $yearly_amt += $monthly_amt;
                        }
                    }
                    $result_arr[$each_year][$main_name] += $yearly_amt;
                }
            }
        }
        $g2_total_acc = $this->BrmSaccount->find('list', array(
            'fields' => 'sub_acc_name_jp',
            'conditions' => array(
                'flag' => 1,
                'group_code' => '02',
                'type' => 1,
            )
        ));

        foreach ($result_arr as $year => $account_amt) {
            $tmp = array();
            $y1_total = 0;
            $y2_total = 0;
            $y1_diff = 0;
            $y2_diff = 0;

            $y1title = ($year == $start_year) ? $year.'年度 予算' : (($year-$start_year > 1) ? ($year-1).'年度 予算' : ($year-1).'年度 見込');
            $y2title = ($year == $start_year) ? $year.'年度 見込' : (($year-$start_year > 1) ? $year.'年度 予算' : $year.'年度 予算');
            foreach ($account_amt as $subacc_name => $amount) {
                if (in_array($subacc_name, $group2_accs)) {
                    $y2amount = $amount;

                    $factor = $this->BrmExpectedBudgetDiffAccount->find('first', array(
                        'fields' => array('factor'),
                        'conditions' => array(
                            'brm_term_id' => $term_id,
                            'target_year' => $year,
                            'layer_code' => $ba_code,
                            'sub_acc_name' => $subacc_name
                        )
                    ))['BrmExpectedBudgetDiffAccount']['factor'];

                    $y1amount = 0;
                    if ($year == $start_year) {
                        $budget = $budgetData[$year][$subacc_name];
                        $y1amount = (!empty($budget) ? $budget : 0);
                    } else {
                        $y1amount = $result_arr[$year-1][$subacc_name];
                    }

                    #Update value of total accs
                    if (in_array($subacc_name, $g2_total_acc)) {

                        #if not 税引後利益
                        if ($subacc_name != end($g2_total_acc)) {
                            $y1_diff = ($y1amount - $y1_total);
                            $y2_diff = ($y2amount - $y2_total);
                        }

                        $y1amount = $y1amount - $y1_diff;
                        $y2amount = $y2amount - $y2_diff;
                    } else {
                        $y1_total+= $y1amount;
                        $y2_total+= $y2amount;
                    }

                    $difference = $y2amount - $y1amount;

                    $tmp['year1_title'] = $y1title;
                    $tmp['year2_title'] = $y2title;
                    $tmp['data'][$subacc_name]['year1'] = number_format($y1amount/1000, 1);
                    $tmp['data'][$subacc_name]['year2'] = number_format($y2amount/1000, 1);
                    $tmp['data'][$subacc_name]['difference'] = number_format($difference/1000, 1);
                    $tmp['data'][$subacc_name]['factor'] = ($factor != null && $factor != 'NULL') ? $factor : '';
                    $tmp['data'][$subacc_name]['factor_year'] = $year;
                }
            }

            $returnData[] = $tmp;
        }

        return $returnData;
    }

    public function getDifferenceDataJob($term_id, $start_year, $year1, $year2, $ba_code)
    {
        $Position = new BrmPositionsController;
        $returnData = [];

        $display_no = array(1,4);
        $field = array_column(
            $this->BrmPosition->find('all', array(
                'fields' => array(
                    'BrmField.id', 'BrmField.field_name_jp',  'BrmField.field_name_en'
                ),
                'conditions' => array(
                    'BrmPosition.display_no' => $display_no,
                    'BrmField.flag' => 1,
                    'BrmPosition.flag' => 1
                ),
                'group' => 'BrmField.field_name_jp',
                'order' => array(
                    'BrmPosition.display_no ASC',
                    'BrmPosition.brm_field_id ASC',
                    'BrmPosition.id ASC'
                )
            )),
            'BrmField'
        );

        $twoField = $Position->getFirstTwoFieldName($start_year, $ba_code);
        $field_name_first  = $twoField['0'];
        $field_name_second = $twoField['1'];

        $field_names = array();
        foreach ($field as $value) {
            if ($value['field_name_jp']==$field_name_first || $value['field_name_jp']==$field_name_second) {
                array_push($field_names, $field_name_second.'（含'.$field_name_first.'）');
            } else {
                array_push($field_names, $value['field_name_jp']);
            }
        }
        $field_names = array_unique($field_names);
        
        # End, PanEiPhyo(20200519)
        foreach ($field_names as $field_name) {
            $budget = 0;
            $forecast = 0;
            $difference = 0;
            $y1amount = 0;
            $y2amount = 0;
            $y1title = '';
            $y2title = '';
            $factor = '';
            $getY1Data = array();
            $getY2Data = array();
            
            if ($year1 == $year2) {
                $y1title = $year1.'年度 予算';
                $y2title = $year1.'年度 見込';
                $getY1Data = $this->getJobData($term_id, $year1, $ba_code, $field_name, 'budget');
                $getY2Data = $this->getJobData($term_id, $year2, $ba_code, $field_name, 'forecast');
            } else {
                $getY1Data = array();
                if (($year2-$start_year) > 1 && $year1!=$year2) {
                    $y1title = $year1.'年度 予算';
                    $y2title = $year2.'年度 予算';
                    $getY1Data = $this->getJobData($term_id, $year1, $ba_code, $field_name, 'budget');
                } else {
                    $y1title = $year1.'年度 見込';
                    $y2title = $year2.'年度 予算';
                    $getY1Data = $this->getJobData($term_id, $year1, $ba_code, $field_name, 'forecast');
                }
                $getY2Data = $this->getJobData($term_id, $year2, $ba_code, $field_name, 'budget');
            }
            $y1 = $getY1Data['BrmExpectedBudgetDiffJob']['amount'];
            $y2 = $getY2Data['BrmExpectedBudgetDiffJob']['amount'];
            $y1amount = (!empty($y1) ? $y1 : 0);
            $y2amount = (!empty($y2) ? $y2 : 0);
            $difference = $y2amount - $y1amount;
            $factor = $getY2Data['BrmExpectedBudgetDiffJob']['factor'];

            $returnData['year1_title'] = $y1title;
            $returnData['year2_title'] = $y2title;
            $returnData['data'][$field_name]['year1'] = number_format($y1amount, 1);
            $returnData['data'][$field_name]['year2'] = number_format($y2amount, 1);
            $returnData['data'][$field_name]['difference'] = number_format($difference, 1);
            $returnData['data'][$field_name]['factor'] = ($factor != null && $factor != 'NULL') ? $factor : '';
            $returnData['data'][$field_name]['factor_year'] = $year2;
        }
        
        return $returnData;
    }

    public function getJobData($term_id, $target_year, $ba_code, $field_name, $type)
    {
        $return_data = $this->BrmExpectedBudgetDiffJob->find('first', array(
            'fields' => array('amount','factor'),
            'conditions' => array(
                'BrmExpectedBudgetDiffJob.brm_term_id' => $term_id,
                'target_year' => $target_year,
                'BrmExpectedBudgetDiffJob.layer_code' => $ba_code,
                'name_jp' => $field_name,
                'type' => $type
            )
        ));
        
        return $return_data;
    }

    public function getCodesPair($group_code, $sub_group_code = 'null')
    {
        $code_array = array();
        $codes_pair = array();

        # for sub account loop
        $sub_accs = $this->BrmSaccount->find('list', array(
            'fields' => array('sub_acc_name_jp','auto_changed','id'),
            'conditions' => array(
                'group_code' => $group_code,
                'flag' => 1
            )
        ));

        foreach ($sub_accs as $id => $sub_acc_data) {
            $sub_acc_name = array_keys($sub_acc_data)[0];
            $auto_changes = explode(',', array_values($sub_acc_data)[0]);

            $conditions = array();

            if ($group_code != '01') {
                $conditions = array(
                    'BrmAccount.pair_ids LIKE' => '%:'. $id . ',%',
                    'BrmAccount.flag' => 1
                );
            } else {
                $conditions = array(
                    'BrmAccount.sub_acc_id' => $id,
                    'BrmAccount.flag' => 1
                );
            }
            
            $acc_codes = $this->BrmAccount->find('list', array(
                'fields' => array('BrmAccount.account_code'),
                'conditions' => $conditions
            ));

            if (empty($acc_codes)) {
                $codes_pair[$sub_acc_name] = $code_array;
            } else {
                $code_array = array_merge($code_array, array_values($acc_codes));
                $codes_pair[$sub_acc_name] = array_values($acc_codes);

                foreach ($auto_changes as $total_acc_name) {
                    $total_acc_name = trim($total_acc_name);

                    if (isset($codes_pair[$total_acc_name]) && !in_array($acc_codes[0], $codes_pair[$total_acc_name])) {
                        $codes_pair[$total_acc_name] = array_merge($codes_pair[$total_acc_name], $acc_codes);
                    }
                }
            }
        }

        return $codes_pair;
    }

    /**
    * SaveFBDFile method
    *
    * @author Hein Htet Ko (20210121)
    * @return void
    */
    public function saveFBDFile()
    {
        App::import('Vendor', 'php-excel-reader/PHPExcel');
        if ($this->request->is('post')) {
            $yearsArr = [];
            $business_code = explode('/', $this->request->data('ba_code'));
            $filling_date =$this->request->data('filling_date');
            $factors = $this->request->data('factors');
            $ba_code = $business_code[0];
            $ba_name = $business_code[1];
            $head_dept_id = $this->Session->read('HEAD_DEPT_CODE');
            $term_id = $this->Session->read('TERM_ID');
            $budget_term = $this->Session->read('TERM_NAME');
            $loginId = $this->Session->read('LOGIN_ID');
            if ($this->Session->check('HEADQUARTER_DEADLINE')) {
                $hqDeadline = $this->Session->read('HEADQUARTER_DEADLINE');
            }
            $years = explode('~', $budget_term);
            $start_year = $years[0];
            $start = $years[0];
            $end = $years[1];

            $cache_name = 'fbd_'.$term_id.'_'.$ba_code.'_'.$loginId;
            $fbd_data = Cache::read($cache_name);

            $year_header = array();
            foreach ($fbd_data['acc'] as $key => $value) {
                $year_header[$key]['year1_title'] = $value['year1_title'];
                $year_header[$key]['year2_title'] = $value['year2_title'];
            }
                        
            # get name, type, tmp_name, error, size of file
            $file = $this->request->params['form']['uploadfile'];

            $uploadPath = APP . 'tmp'; # file path

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

                                        #To get filling date
                                        $row = 1;
                                        for ($column = 'A';$column != $highestColumn;$column++) {
                                            $raw = (int)$objWorksheet->getCell($column.$row)->getValue();
                                        }

                                        #check excel file editing or not
                                        $fill_date = '';
                                        if (($timestamp = strtotime($raw)) !== false) {
                                            $fill_date = date('Y-m-d', strtotime($raw));
                                        } else {
                                            $fill_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($raw));
                                        }

                                        #set filling date from excel or request
                                        if ($fill_date != '') {
                                            $fbd_data['filling_date'] = $fill_date;
                                        } else {
                                            $fbd_data['filling_date'] = date('Y-m-d', strtotime($filling_date));
                                        }
                                        
                                        #To check ba code match
                                        $row = 2;
                                        for ($column = 'A';$column != $highestColumn;$column++) {
                                            $ba_check_value = $objWorksheet->getCell($column.$row)->getValue();
                                        }
                                        if (strpos($ba_check_value, $ba_code) !== false) {
                                            $row = 7;
                                            $header_flag = true;
                                            $header_arr = array();
                                            $header_acc = [__('科目'), __('差異'), __('増減要因')];
                                            $header_job = [__('ポジション'), __('差異'), __('備考')];
                                            for ($i = 0; $i <= ($end-$start_year); $i++) {
                                                foreach ($header_acc as $value) {
                                                    array_push($header_arr, $value);
                                                    if ($value == __('科目')) {
                                                        array_push($header_arr, $year_header[$i]['year1_title']);
                                                        array_push($header_arr, $year_header[$i]['year2_title']);
                                                    }
                                                }
                                            }
                                            for ($i = 0; $i <= ($end-$start_year); $i++) {
                                                foreach ($header_job as $value) {
                                                    array_push($header_arr, $value);
                                                    if ($value == __('ポジション')) {
                                                        array_push($header_arr, $year_header[$i]['year1_title']);
                                                        array_push($header_arr, $year_header[$i]['year2_title']);
                                                    }
                                                }
                                            }
                                            
                                            $row = 7;
                                            $row_data = array();
                                            for ($col = 'B'; $col != $highestColumn; $col++) {
                                                if (!empty($objWorksheet->getCell($col.$row)->getValue())) {
                                                    array_push($row_data, __($objWorksheet->getCell($col.$row)->getValue()));
                                                }
                                            }
                                            $row = count($fbd_data['acc'][0]['data'])+11;
                                            for ($col = 'B'; $col != $highestColumn; $col++) {
                                                if (!empty($objWorksheet->getCell($col.$row)->getValue())) {
                                                    array_push($row_data, __($objWorksheet->getCell($col.$row)->getValue()));
                                                }
                                            }

                                            if ($header_arr === $row_data) {
                                                $header_flag = true;
                                            } else {
                                                $header_flag = false;
                                            }
                                        
                                            if ($header_flag) {
                                                $acc_data = array();
                                                $start_col = 1;
                                                for ($i = 0; $i < count($fbd_data['acc']); $i++) {
                                                    $start_row = 8;
                                                    foreach ($fbd_data['acc'][$i]['data'] as $sub_acc_name => $value) {
                                                        $fbd_data['acc'][$i]['data'][$sub_acc_name]['factor'] = $objWorksheet->getCellByColumnAndRow($start_col+4, $start_row)->getValue();
                                                        $start_row++;
                                                    }
                                                    $start_col = $start_col + 6;
                                                }
                                                $start_col = 1;
                                                for ($j = 0; $j < count($fbd_data['job']); $j++) {
                                                    $start_row = count($fbd_data['acc'][$j]['data'])+12;
                                                    foreach ($fbd_data['job'][$j]['data'] as $position => $value) {
                                                        $fbd_data['job'][$j]['data'][$position]['factor'] = $objWorksheet->getCellByColumnAndRow($start_col+4, $start_row)->getValue();
                                                        $start_row++;
                                                    }
                                                    $start_col = $start_col + 6;
                                                }
                                                
                                                $message = $this->uploadFileData($term_id, $start_year, $ba_code, $fbd_data, $hqDeadline, $factors);
                                                #decide success or error message
                                                if (!empty($message['success'])) {
                                                    $successMsg = $message['success'];
                                                    $this->Flash->set($successMsg, array('key'=>'FbdsSaveSuccess'));
                                                } else {
                                                    $errorMsg = $message['error'];
                                                    $this->Flash->set($errorMsg, array('key'=>'FbdfSave'));
                                                }
                                                $this->redirect(array('controller'=>'BrmForecastBudgetDifference','action'=>'index'));
                                            } else {
                                                $this->errorCommonMsg('SE022');
                                            }
                                        } else {
                                            $this->errorCommonMsg('SE107');
                                        }
                                    } else {
                                        #when excel file is protect password show error message
                                        $this->errorCommonMsg('SE076');
                                    }
                                } else {
                                    $this->errorCommonMsg('SE015');
                                }
                            } else {
                                $this->errorCommonMsg('SE020');
                            }
                        } else {
                            $this->errorCommonMsg('SE013', $ext);
                        }
                    }
                } else {
                    $this->errorCommonMsg('SE015');
                }
            } catch (Expression $e) {
                $this->errorCommonMsg('SE015');
            }
        } else {
            $this->redirect(array('controller'=>'BrmForecastBudgetDifference','action'=>'index'));
        }
    }

    /**
    * errorCommonMsg method
    *
    * @author Hein Htet Ko (20210128)
    */
    public function errorCommonMsg($key, $value = '')
    {
        $errorMsg = parent::getErrorMsg($key, $value);
        if (!empty($errorMsg)) {
            $this->Flash->set($errorMsg, array('key'=>'FbdfSave'));
        } else {
            $this->Flash->set($key, array('key'=>'FbdfSave'));
        }
        CakeLog::write('debug', ' In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

        $this->redirect(array('controller'=>'BrmForecastBudgetDifference','action'=>'index'));
    }

    /**
    * uploadFileData method
    *
    * @author Hein Htet Ko (20210128)
    * @return message
    */
    public function uploadFileData($term_id, $start_year, $ba_code, $fbd_data, $deadline_date, $factors)
    {
        $filling_date = $fbd_data['filling_date'];
        $deadline_date = $deadline_date;
        if ($this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }
        $save_acc = array();
        $save_job = array();
        $flag = true;
        
        foreach ($fbd_data['acc'] as $acc) {
            $target_year = substr($acc['year2_title'], 0, 4);
            $type = ($target_year == $start_year)? 'forecast': 'budget';
            foreach ($acc['data'] as $sub_acc_name => $value) {
                $acc_find = $this->BrmExpectedBudgetDiffAccount->find('first', array(
                            'conditions' => array(
                            'brm_term_id' => $term_id,
                            'target_year' => $target_year,
                            'layer_code' => $ba_code,
                            'sub_acc_name' => $sub_acc_name,
                            'type' => $type
                        )
                    ));
                if (!empty($acc_find)) {
                    $tmp = array();
                    $tmp['id'] 	= $acc_find['BrmExpectedBudgetDiffAccount']['id'];
                    $tmp['factor'] 	= $value['factor'];
                    if (!empty($filling_date)) {
                        $tmp['filling_date'] 	= $filling_date;
                    }
                    $tmp['updated_by'] 	= $loginId;
                } else {
                    $flag = false;
                }
                $save_acc[] = $tmp;
            }
        }

        foreach ($fbd_data['job'] as $job) {
            $target_year = substr($job['year2_title'], 0, 4);
            $type = ($target_year == $start_year)? 'forecast': 'budget';
            foreach ($job['data'] as $field_name_jp => $value) {
                $job_find = $this->BrmExpectedBudgetDiffJob->find('first', array(
                            'conditions' => array(
                            'brm_term_id' => $term_id,
                            'target_year' => $target_year,
                            'layer_code' => $ba_code,
                            'name_jp' => $field_name_jp,
                            'type' => $type
                        )
                    ));
                if (!empty($job_find)) {
                    $tmp = array();
                    $tmp['id'] 	= $job_find['BrmExpectedBudgetDiffJob']['id'];
                    $tmp['factor'] 	= $value['factor'];
                    if (!empty($filling_date)) {
                        $tmp['filling_date'] 	= $filling_date;
                    }
                    $tmp['updated_by'] 	= $loginId;
                } else {
                    $flag = false;
                }
                $save_job[] = $tmp;
            }
        }
        if ($flag) {
            if (!empty($save_acc) && !empty($save_job)) {
                $accDB = $this->BrmExpectedBudgetDiffAccount->getDataSource();
                $jobDB = $this->BrmExpectedBudgetDiffJob->getDataSource();

                try {
                    $jobDB->begin();
                    $accDB->begin();

                    #Save Data
                    if (!empty($save_acc)) {
                        $this->BrmExpectedBudgetDiffAccount->saveAll($save_acc);
                    }
                    if (!empty($save_job)) {
                        $this->BrmExpectedBudgetDiffJob->saveAll($save_job);
                    }

                    $accDB->commit();
                    $jobDB->commit();

                    $returnMsg['success'] = parent::getSuccessMsg('SS001');
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                    $accDB->rollback();
                    $jobDB->rollback();
                    $returnMsg['error'] = parent::getErrorMsg('SE003');
                }
            } else {
                $returnMsg['error'] = parent::getErrorMsg('SE017', 'save');
            }
        } else {
            $save_factor = $this->commonSave($term_id, $start_year, $ba_code, $factors, $filling_date, $deadline_date, $loginId);
            if (!empty($save_factor['success'])) {
                $returnMsg = $this->uploadFileData($term_id, $start_year, $ba_code, $fbd_data, $deadline_date, $factors);
                if (!empty($returnMsg['success'])) {
                    return $returnMsg;
                }
            } else {
                $returnMsg['error'] = parent::getErrorMsg('SE003');
            }
        }
        return $returnMsg;
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
}
