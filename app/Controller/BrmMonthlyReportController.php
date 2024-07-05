<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'Calculation');
/**
 * BrmMonthlyReports Controller
 *
 * @property BrmMonthlyReport $BrmMonthlyReport
 * @property PaginatorComponent $Paginator
 */
class BrmMonthlyReportController extends AppController
{
    public $uses = array('Layer','BrmMrAttachment','BrmMrOverview','BrmForecast','BrmAccount','BrmSaccount','BrmBudget','BrmActualResultSummary','BrmMrApprove', 'LayerType');
    public $components = array('Session','Flash','PhpExcel.PhpExcel','Paginator');
    public $helpers = array('Html', 'Form');
    
    /**
     * beforeFilter
     * @author NuNuLwin (2020/03/20)
     * @return data
     */
    public function beforeFilter()
    {
        $Common = New CommonController();
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        $layers = array_keys($permissions['index']['layers']);
        
        if((!in_array($layer_code, $layers)) || ($layer_code=="" && $permissions['index']['limit']>0)) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        } 
        $target_month 	= $this->request->query('target_month');
        $term_id 		= $this->request->query('term_id');
        $term_name 		= $this->request->query('term_name');
        $hq_name		= $this->request->query('hq_name');
        $hq_id			= $this->request->query('hq_id');
        $read_limit   	= $permissions['index']['limit'];
        
        if ($target_month != "" && $term_name != "" && $term_id != "") {
            $this->Session->write('TARGETMONTH', $target_month);
            $this->Session->write('TERM_ID', $term_id);
            $this->Session->write('TERM_NAME', $term_name);
            if ($read_limit == '1') {
                if ($this->Session->check('HEAD_DEPT_ID') && $this->Session->check('HEAD_DEPT_NAME')) {
                    $hq_id = $this->Session->read('HEAD_DEPT_ID');
                    $hq_name = $this->Session->read('HEAD_DEPT_NAME');
                } else {
                    $errorMsg = parent::getErrorMsg('SE073');
                    $this->Flash->set($errorMsg, array("key"=>"TermError"));
                    $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
                }
            } 
            $this->Session->write('HEAD_DEPT_NAME', $hq_name);
            $this->Session->write('HEAD_DEPT_ID', $hq_id);
        } elseif (($this->Session->read('TERM_NAME') == "") || ($this->Session->read('TARGETMONTH') == "") || ($this->Session->read('HEAD_DEPT_NAME') == "")) {
            $errorMsg = parent::getErrorMsg('SE081');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        }
        #when saved and window.reload, msg not show so reset the msg
        if(!empty($_SESSION['Message']['mty_rp_ok'])) {
            $_SESSION['tmpSuccess'] = $_SESSION['Message']['mty_rp_ok'];
        }elseif(!empty($_SESSION['tmpSuccess'])) {
            $_SESSION['Message']['mty_rp_ok'] = $_SESSION['tmpSuccess'];
            unset($_SESSION['tmpSuccess']);
        }
        if(!empty($_SESSION['Message']['mty_rp_fail'])) {
            $_SESSION['tmpError'] = $_SESSION['Message']['mty_rp_fail'];
        }elseif(!empty($_SESSION['tmpError'])) {
            $_SESSION['Message']['mty_rp_fail'] = $_SESSION['tmpError'];
            unset($_SESSION['tmpError']);
        }
    }
    
    /**
     * index method
     * @author NuNuLwin (20200221)
     * @return data
     */
    public function index()
    { 
        $Common = new CommonController;
        $result = array();
        $total  = array();
        $this->layout = 'phase_3_menu';
        
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        if ($this->Session->check('TERM_NAME')) {
            $budget_term = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('TARGETMONTH')) {
            $target_month = $this->Session->read('TARGETMONTH');
        }
        if ($this->Session->check('PERMISSIONS')) {
            $permission = $this->Session->read('PERMISSIONS');
        }
        if ($this->Session->check('HEAD_DEPT_NAME')) {
            $headquarter = $this->Session->read('HEAD_DEPT_NAME');
            $hq_name = $headquarter; # to set ctp
        }
        if ($this->Session->check('LayerTypeData')) {
            $layer_types = $this->Session->read('LayerTypeData');
        }
        $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        $head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
        
        if (empty($budget_term) || empty($term_id) || empty($target_month) || empty($head_dept_id) || empty($headquarter)) {
            /* if empty(term||target||hq), show err msg*/
            $err_msg = $this->showErrMsg($term_id, $target_month, $head_dept_id);
            $errorMsg = parent::getErrorMsg('SE072', $err_msg);
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        } else {
            if ($this->Session->read('COMPARE_TM') == 'error') {
                $errorMsg = parent::getErrorMsg('SE082');
                $this->Flash->set($errorMsg, array("key"=>"TermError"));
                $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
            }
        }
        
        #get read_limit 1 = all  or 2 = head
        $read_limit   = $permission['index']['limit'];
        #get create_limit for save btn show/hide
        $create_limit = $permission['save']['limit'];
        #get create_limit for approve btn show/hide
        $approve_limit = $permission['approve']['limit'];

        $get_permit_codes = $this->getPermissionDept($permission['index']['parent_data']);
       
        $master_array = array(); #to return ctp
        
        if ($read_limit == '0' || $read_limit == '1') {
            #Get new calculated data
            $common_res = $this->commonIndexCal($head_dept_code, $headquarter, $budget_term, $term_id, $target_month);      
            array_push($master_array, $common_res);
        } elseif (empty($read_limit) || $read_limit >= '2') {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }
        #To show approve btn at headquarter.
        $tax_refund_ba = Setting::TAX_REFUND_BA; #BA 9000
        $departments = $this->Layer->find('list', array(
            'fields' => array('Layer.layer_code'),
            'conditions' => array(
                'Layer.flag' => 1,
                'Layer.type_order' => Setting::LAYER_SETTING['middleLayer'], 
                "Layer.parent_id LIKE CONCAT('%\"L', ".Setting::LAYER_SETTING['topLayer'].", '\":\"',".$head_dept_code.",'\"%')",
                'NOT' => array(
                    'Layer.layer_code' => $tax_refund_ba
                )
            ),
            'joins' => array(
                array(
                    'table' => 'layers',
                    'alias' => 'Layer2',
                    'conditions' => array(
                        'Layer2.flag' => 1,
                        'Layer2.type_order' => Setting::LAYER_SETTING['bottomLayer'], 
                        "Layer2.parent_id LIKE CONCAT('%\"L', ".Setting::LAYER_SETTING['middleLayer'].", '\":\"', Layer.layer_code,'\"%')"
                    )
                )
            ),
            'group' => array(
                'layer_code'
            ),
            'order' => array('Layer.id'=> 'DESC')
        ));
        
        $monthly_rp_count = $this->BrmMrApprove->find('count', array(
                                'conditions'=>array(
                                    "hlayer_code"	=>$head_dept_code,
                                    "dlayer_code"	=>$departments,
                                    "target_month"	=>$target_month,
                                    "flag >=" 		=> '2'
                                ),
                                'group' => array(
                                    'dlayer_code'
                                ),
                            ));
                  
        #If all dept are approved, show head approve btn at create_limit = 1 or 2
        (count($departments) == $monthly_rp_count)? $this->set('approvePermit', "Yes"):$this->set('approvePermit', "No");

        $this->set("master_array", $master_array);
        $this->set("budget_term", $budget_term);
        $this->set("headquarter", $hq_name);
        $this->set("target_month", $target_month);
        $this->set("user_level", $user_level);
        $this->set("read_limit", $read_limit);
        $this->set("create_limit", $create_limit);
        $this->set("approve_limit", $approve_limit);
        $this->set("get_permit_codes",$get_permit_codes);
        $this->set("layer_types", $layer_types);
        $this->render('index');
    }
    /**
     * Calculation table data
     * @author NuNuLwin (20200221)
     * @return data
     */
    
    public function commonIndexCal($head_dept_code, $headquarter, $budget_term, $term_id, $target_month)
    {
        $Common = new CommonController;
        $Calculation = new CalculationController;
        $user_cmd = array(); #For headquarter overview comment to set ctp.
        $tmp_result = array();
        $total = array();
        $get_calculate = array();

        #Prepare cache data for excel download
        $cache_name = 'monthly_report_excel_'.$term_id.'_'.$head_dept_code.'_'.$target_month;

        $chk_res = '0'; #to check sending result data empty or not

        # Added by PanEiPhyo(20200303), For calculation

        # Get only year form target_month
        $target_year = date("Y", strtotime($target_month));
        $term = explode('~', $budget_term);
        
        $term_start_month = $Common->getMonth($target_year, $term_id, 'start');
        $term_end_month = $Common->getMonth($target_year, $term_id, 'end');

        if ($term_start_month<=$target_month && $term_end_month>=$target_month) {
            $target_year = $target_year;
        } else {
            $target_year = $target_year - 1;
        }

        $last_year_tm = date("Y-m", strtotime($target_month. "last day of - 1 year"));
        $layer_code_list = $this->Layer->find('list', array(
            'fields' => array('layer_code'),
            'conditions' => array(
                'Layer.flag' => 1,
                'Layer.type_order' => Setting::LAYER_SETTING['bottomLayer'], 
                "Layer.parent_id LIKE CONCAT('%\"L', ".Setting::LAYER_SETTING['topLayer'].", '\":\"',".$head_dept_code.",'\"%')",
            ),
            'group' => array(
                'layer_code'
            )
        ));
        # Change ba_code array to string
        //$layer_code = "'".join("','", $layer_code_list)."'";
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        
        # Check actual result data
        $have_actual = $this->BrmActualResultSummary->checkActualData($layer_code, $target_month, $last_year_tm);
        # Check actual result data
        $have_budget = $this->BrmBudget->checkBudgetData($layer_code, $term_id, $target_month, $last_year_tm);
        
        #if no data in tbl_actual_result or tbl_budget, no show data (due to remove amt - zero)
        if ($have_actual > 0 || $have_budget > 0) {
            $cache_data = Cache::read($cache_name);
            
            if (!empty($cache_data)) {
                $get_calculate = $cache_data;

            } else {
                $get_calculate = $Calculation->CalculateMRAmt($head_dept_code, $headquarter, $target_month, $term_id, $term_name);
                
            }
            
            $chk_res = $get_calculate['chk_res'];
            #for button remove and add
            $flag_arr = $this->BrmMrApprove->find('list', array(
                            'fields' => array('dlayer_code','flag'),
                            'conditions' => array(
                                'target_month' => $target_month,
                                'hlayer_code' => $head_dept_code,
                                'flag !='=> 0
                                )
                            ));
            
            if ($chk_res == '0') {
                $result = array();
            }
            #to return index
            $master_array = $get_calculate;
            $master_array['flag_arr'] = $flag_arr;

            Cache::write($cache_name, $master_array);
        } else {
            $master_array = array();
        }
        return $master_array;
    }
    
    /**
     * save user input data at tbl_forecast and tbl_total_result_overview
     * flag not change
     * @author NuNuLwin 02/06/2020 new added
     * @throws NotFoundException
     * @param  no param
     * @return void
    */
    public function saveHead()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = ''; #you need to have a no html page, only the data
        $this->tmpMsgDelete();
        $login_id = $this->Session->read('LOGIN_ID'); #get login id
        $date = date('Y-m-d H:i:s');
        
        if ($this->Session->check('HEAD_DEPT_ID')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('TARGETMONTH')) {
            $target_month = $this->Session->read('TARGETMONTH');
        }
        $login_user_name = $this->Session->read('LOGIN_USER');
        $hq_id		 	 = $this->Session->read('HEAD_DEPT_ID');
        $term_name		 = $this->Session->read('TERM_NAME');
        $headquarter 	 = $this->Session->read('HEAD_DEPT_NAME');
        
        $request_data = $this->request->data('myJSONString');
        $mail_info    = $this->request->data('mail_info');
        $mail_info    = json_decode($mail_info, true);
        $request_data = json_decode($request_data, true);
        $tol_res_ov_db 	= $this->BrmMrOverview->getDataSource();
        $forecast_db   	= $this->BrmForecast->getDataSource();
        try {
            $tol_res_ov_db->begin();
            $forecast_db->begin(); 
            $call_function = $this->common_save($request_data);
            if ($call_function) {
                if($mail_info['mailSend']) {
                    $url = '/BrmMonthlyReport?target_month='.$target_month.'&hq_id='.$hq_id.'&hq_name='.$headquarter.'&term_id='.$term_id.'&term_name='.$term_name;
                    $period = '';
                    $to_email = $mail_info['toEmail'];
                    $cc_email = $mail_info['ccEmail'];
                    $bcc_email = $mail_info['bccEmail'];
                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email); 
                    #Mail contents
                    $mail_template         = 'common';
                    $mail['subject']       = $mail_info['mailSubj'];
                    $mail['template_body'] = $mail_info['mailBody'];
                
                    if (!empty($toEmail) || !empty($ccEmail) || !empty($bccEmail)) {
                        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                        if ($sentMail["error"]) {
                            $msg = $sentMail["errormsg"];
                            $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                            $invalid_email = parent::getErrorMsg('SE042');
                            $this->Flash->set($invalid_email, array('key'=>'mty_rp_fail'));
                            $tol_res_ov_db->rollback();
                            $forecast_db->rollback();
                        } else {
                            $tol_res_ov_db->commit();
                            $forecast_db->commit();
                            $msg = parent::getSuccessMsg("SS019", [__("正常")]);
                            $this->Flash->set($msg, array('key'=>'mty_rp_ok'));
                            $msg = parent::getSuccessMsg("SS018");
                            $this->Flash->set($msg, array('key'=>'mty_rp_ok'));
                        }
                    }
                } else {
                    $tol_res_ov_db->commit();
                    $forecast_db->commit();
                    $name 	 = $request_data['0']['name'];
                    $msg = parent::getSuccessMsg("SS019", $name);
                    $this->Flash->set($msg, array('key'=>'mty_rp_ok'));
                }
            } else {
                $msg = parent::getErrorMsg("SE003");
                $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                $tol_res_ov_db->rollback();
                $forecast_db->rollback();
            }
        } catch (Exception $e) {
            $tol_res_ov_db->rollback();
            $forecast_db->rollback();
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
        }
        return true;
    }

    /**
     * Approve monthly report=> change flag "2" at tbl_monthly_report
     *
     * @author NuNuLwin (20200405) edited =>(20200512)
     * @throws NotFoundException
     * @param  no param
     * @return void
    */
    public function approveHead()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = ''; #you need to have a no html page, only the data
        $this->tmpMsgDelete();
        $login_id = $this->Session->read('LOGIN_ID'); #get login id
        $date = date('Y-m-d H:i:s');
        
        if ($this->Session->check('HEAD_DEPT_ID')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('TARGETMONTH')) {
            $target_month = $this->Session->read('TARGETMONTH');
        }
        if ($this->Session->check('HEAD_DEPT_CODE')) {
            $head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
        }
        $login_user_name = $this->Session->read('LOGIN_USER');
        $hq_id		 	 = $this->Session->read('HEAD_DEPT_ID');
        $term_name		 = $this->Session->read('TERM_NAME');
        $headquarter 	 = $this->Session->read('HEAD_DEPT_NAME');

        $request_data = $this->request->data('myJSONString');
        $mail_info    = $this->request->data('mail_info');
        $mail_info    = json_decode($mail_info, true);
        $request_data = json_decode($request_data, true);
        
        $monthly_db		= $this->BrmMrApprove->getDataSource();
        $tol_res_ov_db 	= $this->BrmMrOverview->getDataSource();
        $forecast_db   	= $this->BrmForecast->getDataSource();

        try {
            $monthly_db->begin();
            $tol_res_ov_db->begin();
            $forecast_db->begin();
            $call_function = $this->common_save($request_data);
            
            if ($call_function) {
                $save_succ_flag = "1";
                $dept_id = $request_data['0']['dept_id'];
                $name 	 = $request_data['0']['name'];

                $monthly_db = $this->BrmMrApprove->getDataSource();
                    
                $monthly = $this->BrmMrApprove->find('all', array(
                            'fields'=>array('flag','id'),
                            'conditions'=>array(
                                    "dlayer_code"	=> $dept_id,
                                    "hlayer_code"	=> $head_dept_code,
                                    "target_month"	=> $target_month
                                    
                            )
                        ));
            
                if (empty($monthly)) { #Insert Mode

                    $save_monthly_rp  = array(
                        'hlayer_code' 	=> $head_dept_code,
                        'dlayer_code'   => $dept_id,
                        'target_month' 	=> $target_month,
                        'flag' 			=> "2",
                        'created_by' 	=> $login_id,
                        'updated_by' 	=> $login_id
                    );
                    
                    if (!empty($head_dept_id)) {
                        $this->BrmMrApprove->create();

                        if ($this->BrmMrApprove->save($save_monthly_rp)) {
                            if($mail_info['mailSend']) {
                                $url = '/BrmMonthlyReport?target_month='.$target_month.'&hq_id='.$hq_id.'&hq_name='.$headquarter.'&term_id='.$term_id.'&term_name='.$term_name;
                                $period = '';
                                $to_email = $mail_info['toEmail'];
                                $cc_email = $mail_info['ccEmail'];
                                $bcc_email = $mail_info['bccEmail'];
                                $toEmail = parent::formatMailInput($to_email);
                                $ccEmail = parent::formatMailInput($cc_email);
                                $bccEmail = parent::formatMailInput($bcc_email); 
                                #Mail contents
                                $mail_template         = 'common';
                                $mail['subject']       = $mail_info['mailSubj'];
                                $mail['template_body'] = $mail_info['mailBody'];
                            
                                if (!empty($toEmail) || !empty($ccEmail) || !empty($bccEmail)) {
                                    $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                    if ($sentMail["error"]) {
                                        $msg = $sentMail["errormsg"];
                                        $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                                        $invalid_email = parent::getErrorMsg('SE042');
                                        $this->Flash->set($invalid_email, array('key'=>'mty_rp_fail'));
                                        $monthly_db->rollback();
                                        $tol_res_ov_db->rollback();
                                        $forecast_db->rollback();
                                    } else {
                                        $monthly_db->commit();
                                        $save_succ_flag = 1;
                                    }
                                }
                            } else {
                                #Only save data without mail send
                                $monthly_db->commit();
                                $save_succ_flag = 4;
                            }
                        } else {
                            $save_succ_flag = 2;
                        }
                    }
                } else { #Update Mode

                    $get_flag = $monthly['0']['BrmMrApprove']['flag'];

                    if ($get_flag < '2') {
                        $monthly_rp_id = $monthly['0']['BrmMrApprove']['id'];
                        $this->BrmMrApprove->id = $monthly_rp_id;
                        $this->BrmMrApprove->set(array('flag'=>'2','updated_by'=>$login_id,'updated_date'=>$date));
                        
                        if ($this->BrmMrApprove->save()) {
                            if($mail_info['mailSend']) {
                                $url = '/BrmMonthlyReport?target_month='.$target_month.'&hq_id='.$hq_id.'&hq_name='.$headquarter.'&term_id='.$term_id.'&term_name='.$term_name;
                                $period = '';
                                $to_email = $mail_info['toEmail'];
                                $cc_email = $mail_info['ccEmail'];
                                $bcc_email = $mail_info['bccEmail'];
                                $toEmail = parent::formatMailInput($to_email);
                                $ccEmail = parent::formatMailInput($cc_email);
                                $bccEmail = parent::formatMailInput($bcc_email); 
                                #Mail contents
                                $mail_template         = 'common';
                                $mail['subject']       = $mail_info['mailSubj'];
                                $mail['template_body'] = $mail_info['mailBody'];
                            
                                if (!empty($toEmail) || !empty($ccEmail) || !empty($bccEmail)) {
                                    $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                    if ($sentMail["error"]) {
                                        $msg = $sentMail["errormsg"];
                                        $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                                        $invalid_email = parent::getErrorMsg('SE042');
                                        $this->Flash->set($invalid_email, array('key'=>'mty_rp_fail'));
                                        $monthly_db->rollback();
                                        $tol_res_ov_db->rollback();
                                        $forecast_db->rollback();
                                    } else {
                                        $monthly_db->commit();
                                        $save_succ_flag = 1;
                                    }
                                }
                            } else {
                                #Only save data without mail send
                                $monthly_db->commit();
                                $save_succ_flag = 4;
                            }
                        } else {
                            $save_succ_flag = 2;
                        }
                    } else {
                        $save_succ_flag = 3;
                    }
                }
            } else {
                $msg = parent::getErrorMsg("SE003");
                $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                $monthly_db->rollback();
                $tol_res_ov_db->rollback();
                $forecast_db->rollback();
            }
            $monthly_db->commit();
            $tol_res_ov_db->commit();
            $forecast_db->commit();
        } catch (Exception $e) {
            $monthly_db->rollback();
            $tol_res_ov_db->rollback();
            $forecast_db->rollback();
            $throwMsg = $e->getMessage(); #get throw error message
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            if ($throwMsg != 'mail_empty' && $throwMsg != 'mail_invalid') {
                $msg = parent::getErrorMsg("SE003");
                $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                return false;
            } else {
                return true;
            }
        } 
        if ($save_succ_flag == '1') {
            $msg = parent::getSuccessMsg("SS022", $name);
            $this->Flash->set($msg, array('key'=>'mty_rp_ok'));
            $msg = parent::getSuccessMsg("SS018");
            $this->Flash->set($msg, array('key'=>'mty_rp_ok'));
        } elseif ($save_succ_flag == '2') {
            $msg = parent::getErrorMsg("SE003");
            $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
        } elseif ($save_succ_flag == '3') {
            #Can't request because of data is already approve!
            $msg = parent::getErrorMsg("SE010");
            $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
        } elseif ($save_succ_flag == '4') {
            #Data has been saved successfully!
            $msg = parent::getSuccessMsg("SS022", $name);
            $this->Flash->set($msg, array('key'=>'mty_rp_ok'));
        }    
        return true;
    }


    /**
     * Approve Cancel monthly report=> change flag "1" at tbl_monthly_report
     *
     * @author NuNuLwin (20200407)
     * @throws NotFoundException
     * @param  no param
     * @return void
    */
    public function approve_cancelHead()
    {
        $login_id  = $this->Session->read('LOGIN_ID'); #get login id
        $date      = date('Y-m-d H:i:s');
        $mail_send = '';
        $this->tmpMsgDelete();
        if ($this->Session->check('HEAD_DEPT_ID')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('TARGETMONTH')) {
            $target_month = $this->Session->read('TARGETMONTH');
        }
        if ($this->Session->check('HEAD_DEPT_CODE')) {
            $head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
        }
        $login_user_name = $this->Session->read('LOGIN_USER');
        $hq_id		 	 = $this->Session->read('HEAD_DEPT_ID');
        $term_name		 = $this->Session->read('TERM_NAME');
        $headquarter 	 = $this->Session->read('HEAD_DEPT_NAME');
        
        $monthly_db = $this->BrmMrApprove->getDataSource();
        try {
            $monthly_db->begin();
            $successFlag = true;
            $dept_id = $this->request->data['hidden_dept_id'];
            $name	 = $this->request->data['hidden_file_name'];
            
            $monthly_rp = $this->BrmMrApprove->find('all', array(
                    'fields' => array('dlayer_code','id'),
                    'conditions' => array(
                            "BrmMrApprove.dlayer_code"	=> $dept_id,
                            "BrmMrApprove.hlayer_code"	=> $head_dept_code,
                            "BrmMrApprove.target_month"	=> $target_month,
                            "BrmMrApprove.flag" 		=> '2'
                    )
                ));
                
            if (!empty($monthly_rp)) {
                foreach ($monthly_rp as $value) {
                    $monthly_rp_id = $value['BrmMrApprove']['id'];

                    $this->BrmMrApprove->id = $monthly_rp_id;
                    $this->BrmMrApprove->set(array('flag'=>'1','updated_by'=>$login_id,'updated_date'=>$date));

                    if ($this->BrmMrApprove->save()) {
                        if($_POST['mailSend']) {
                            $url = '/BrmMonthlyReport?target_month='.$target_month.'&hq_id='.$hq_id.'&hq_name='.$headquarter.'&term_id='.$term_id.'&term_name='.$term_name;
                            $period = '';
                            $to_email = $_POST['toEmail'];
                            $cc_email = $_POST['ccEmail'];
                            $bcc_email = $_POST['bccEmail'];
                            $toEmail   = parent::formatMailInput($to_email);
                            $ccEmail   = parent::formatMailInput($cc_email);
                            $bccEmail  = parent::formatMailInput($bcc_email); 
                            #Mail contents
                            $mail_template         = 'common';
                            $mail['subject']       = $_POST['mailSubj'];
                            $mail['template_body'] = $_POST['mailBody'];
                        
                            if (!empty($toEmail) || !empty($ccEmail) || !empty($bccEmail)) {
                                $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                if ($sentMail["error"]) {
                                    $msg = $sentMail["errormsg"];
                                    $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                                    $invalid_email = parent::getErrorMsg('SE042');
                                    $this->Flash->set($invalid_email, array('key'=>'mty_rp_fail'));
                                    $monthly_db->rollback();
                                } else {
                                    $monthly_db->commit();
                                    $successFlag = true;
                                }
                            } else {
                                $mail_send = 'mail_empty';
                            }
                        } else {
                            #Only save data without mail send
                            $monthly_db->commit();
                            $successFlag = true;
                        }
                    } else {
                        $successFlag = false;
                    }
                }

                if ($successFlag) {
                    if ($mail_send=='mail_empty' || $mail_send=='mail_invalid' || !$_POST['mailSend']) {
                        $msg = parent::getSuccessMsg("SS023", $name);
                        $this->Flash->set($msg, array('key'=>'mty_rp_ok'));
                    } else {
                        $msg = parent::getSuccessMsg("SS023", $name);
                        $this->Flash->set($msg, array('key'=>'mty_rp_ok'));
                        $msg = parent::getSuccessMsg("SS018");
                        $this->Flash->set($msg, array('key'=>'mty_rp_ok'));
                    }
                } else {
                    $msg = parent::getErrorMsg("SE036");
                    $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                }
            } else {
                $msg = parent::getErrorMsg("SE036");
                $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
            }
        } catch (Exception $e) {
            $monthly_db->rollback();
            $throwMsg = $e->getMessage(); #get throw error message

            if ($throwMsg != 'mail_empty' && $throwMsg != 'mail_invalid') {
                $msg = parent::getErrorMsg("SE036");
                $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
            }
            
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'BrmMonthlyReport', 'action' => 'index'));
        }
        $this->redirect(array('controller' => 'BrmMonthlyReport', 'action' => 'index'));
    }

    /**
     * save at tbl_total_result_overviw and tbl_forecast.
     * common function for save and approve function.
     * @author NuNuLwin (20200407)
     * @throws NotFoundException
     * @param  $request_data
     * @return true/false
     */

    public function common_save($request_data)
    { 
        $Common = new CommonController;
            
        $login_id = $this->Session->read('LOGIN_ID'); #get login id
        $date = date('Y-m-d H:i:s');
        if ($this->Session->check('HEAD_DEPT_ID')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('HEAD_DEPT_CODE')) {
            $head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('TARGETMONTH')) {
            $target_month = $this->Session->read('TARGETMONTH');
        }
        $login_user_name = $this->Session->read('LOGIN_USER');
        $hq_id		 	 = $this->Session->read('HEAD_DEPT_ID');
        $term_name		 = $this->Session->read('TERM_NAME');
        $headquarter 	 = $this->Session->read('HEAD_DEPT_NAME');
        $tg_month = date('n', strtotime($target_month));
        $col = $Common->getMonthColumn($tg_month, $term_id);
        
        $save_total_result_oview = array();
        $save_succ_flag = "1";
       
        foreach ($request_data as $each) {

            #Get department Id
            $dept_id = $each['dept_id'];

            #Get total result overview comment
            $overview = $each['ovrviewDept'];
            
            #Get MM+1
            $month1_forecast = $each['month1'];
            $count_1 = strlen($month1_forecast);
            
            #Get MM+2
            $month2_forecast = $each['month2'];
            $count_2 = strlen($month2_forecast);
            
            #Get MM+3
            $month3_forecast = $each['month3'];
            $count_3 = strlen($month3_forecast);

            #Get Year
            $year = $each['yearDept'];
            $count_year = strlen($year);  

            if ($count_1 >= '7' || $count_2 >= '7' || $count_3 >= '7' || $count_year >= '8') {
                $msg = parent::getErrorMsg("SE079");
                $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                return false;
            }
            
            #Get Next 3 month forecast and annual prospects comment
            $remark = $each['annualProspects'];
            
            #Get month1_budget to save at tbl_forecast
            $month1_budget = filter_var($each['budgetMonth1Dept'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            #The FILTER_SANITIZE_NUMBER_FLOAT filter removes all illegal characters from a float number.

            #Get month2_budget to save at tbl_forecast
            $month2_budget = filter_var($each['budgetMonth2Dept'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            
            #Get month3_budget to save at tbl_forecast
            $month3_budget = filter_var($each['budgetMonth3Dept'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            
            #Get yearly_budget to save at tbl_forecast
            $yearly_budget = filter_var($each['budgetYearDept'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            #Prepare save and update in tbl_total_result_overview.
            $rst_oview_dept = $this->BrmMrOverview->find('first', array(
                    'conditions'=>array(
                            "dlayer_code"	=>$dept_id,
                            "hlayer_code"	=>$head_dept_code,
                            "target_month"	=>$target_month
                    )
                ));
            
            if (!empty($rst_oview_dept)) { #update mode

                $tbl_rst_over_id = $rst_oview_dept['BrmMrOverview']['id'];
                    
                if (!empty($tbl_rst_over_id) && !empty($head_dept_code)) {
                    if ($overview == "") {
                        $overview = "NULL";
                    }
                    #Update
                    $this->BrmMrOverview->id = $tbl_rst_over_id;
                    $this->BrmMrOverview->set(array('overview'=>$overview,'updated_by'=>$login_id,'updated_date'=>$date));
                    $this->BrmMrOverview->save();
                    Cache::clear();
                }
            } else { #save mode
                $save_total_result_oview  = array(
                    'hlayer_code' 	=> $head_dept_code,
                    'dlayer_code'   => $dept_id,
                    'target_month' 	=> $target_month,
                    'overview' 		=> $overview,
                    'created_by' 	=> $login_id,
                    'updated_by' 	=> $login_id
                );
                
                if (!empty($head_dept_id) && $overview !== '') {
                    #Save
                    $this->BrmMrOverview->create();
                    $this->BrmMrOverview->save($save_total_result_oview);
                    Cache::clear();
                }
            }
            #End prepare save and update in tbl_total_result_overview.

            #Prepare save and update in forecast table
            $forecast_dept = $this->BrmForecast->find('first', array(
                'conditions'=>array(
                    "dlayer_code"	=> $dept_id,
                    "hlayer_code"	=> $head_dept_code,
                    "target_month"	=> $target_month
                )
            ));

            if ($remark == "") {
                $remark = 'NULL';
            }
            
            if (!empty($forecast_dept)) { #update mode

                $tbl_forecast_id = $forecast_dept['BrmForecast']['id'];
                    
                if (!empty($tbl_forecast_id)) {
                    if (!empty($head_dept_id)){
                        $this->BrmForecast->id = $tbl_forecast_id;
                        $this->BrmForecast->set(array('month1_forecast'=>$month1_forecast,'month2_forecast'=>$month2_forecast,'month3_forecast'=>$month3_forecast,'yearly_forecast'=>$year,'remark'=>$remark, 'updated_by'=>$login_id,'updated_date'=>$date));

                        $this->BrmForecast->save();
                        Cache::clear();
                    } else {
                        return false;
                    }
                } else {
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class in ' . get_class()." tbl_forecast of id is empty");
                    
                    return false;
                }
            } else { #save mode

                $save_forecast  = array(
                    "target_month" 		=> $target_month,
                    "hlayer_code" 		=> $head_dept_code,
                    "dlayer_code" 		=> $dept_id,
                    "month1_forecast" 	=> $month1_forecast,
                    "month2_forecast" 	=> $month2_forecast,
                    "month3_forecast" 	=> $month3_forecast,
                    "yearly_forecast" 	=> $year,
                    "remark" 			=> $remark,
                    "created_by" 		=> $login_id,
                    "updated_by" 		=> $login_id
                );
                
                if (!empty($head_dept_id)) {
                    
                    $this->BrmForecast->create();
                    $this->BrmForecast->save($save_forecast);
                    Cache::clear();
                   
                } else {
                    return false;
                }
            }
            #End Prepare save and update in forecast table
        }
        return true;
    }

    /**
    * MailSending method
    *
    * @author Nu Nu Lwin (20200421)
    * @return void
    * $action => "request ,reject , approve , approve_cancel"
    * $head_or_dept => head_id or dept_id
    */
    public function commonMailSend($action, $head_or_dept, $hdname)
    {
        $Common = new CommonController;

        #If $head_or_dept is 0 => headquarter, if not => department.
        
        $login_user 	= $this->Session->read('LOGIN_USER');
        $login_id 		= $this->Session->read('LOGINID');
        $login_lvl_id   = $this->Session->read('ADMIN_LEVEL_ID');
        $term_id 		= $this->Session->read('TERM_ID');
        $target_month 	= $this->Session->read('TARGETMONTH');
        $hq_id		 	= $this->Session->read('HEAD_DEPT_ID');
        $term_name		= $this->Session->read('TERM_NAME');
        $headquarter 	= $this->Session->read('HEAD_DEPT_NAME');

        #Get deadline date
        $submission_date = $this->BrmActualResultSummary->find('first', array(
                    'fields' => 'submission_deadline_date',
                    'conditions' => array(
                        'target_month' => $target_month,
                    )
                ));
        $sub_date = $submission_date['BrmActualResultSummary']['submission_deadline_date'];
        $day = date("w", strtotime($sub_date));
        $dys = array("日","月","火","水","木","金","土");
        $deadline_date = date("n\月d\日（".$dys[$day]."）", strtotime($sub_date));
        
        $level_1 	= AdminLevel::ADMIN;
        $level_2 	= AdminLevel::ACCOUNT_MANAGER;
        $level_3 	= AdminLevel::ACCOUNT_SECTION_MANAGER;
        $level_4 	= AdminLevel::ACCOUNT_INCHARGE;
        $level_5 	= AdminLevel::BUSINESS_MANAGER;
        $level_6 	= AdminLevel::BUSINESS_ADMINISTRATIOR;
        $level_7 	= AdminLevel::BUSINESS_INCHARGE;
        $level_8 	= AdminLevel::DEPUTY_GENERAL_MANAGER;
        $level_9 	= AdminLevel::GENERAL_MANAGER;
        $level_10 	= AdminLevel::BUDGET_INCHARGE;
        $level_11 	= AdminLevel::BUDGET_MANAGER;
        $level_12 	= AdminLevel::BUDGET_PRESIDENT;
        $level_13 	= AdminLevel::BUDGET_CHIEF_OFFICER;
        $level_14 	= AdminLevel::BUDGET_AUDIT;
        $level_15 	= AdminLevel::BUDGET_BOARD_MEMBER;
        $level_16 	= AdminLevel::BUDGET_MANAGING_DIRECTOR;

        $ccEmail  = array();
        $bccEmail = array();
        $toEmail = array();
        $mail_template 	= 'common';
        #$url = '/MonthlyReport?target_month='.$target_month.'&hq_id='.$hq_id.'&term_id='.$term_id.'&term_name='.$term_name.'&head_dept_name='.urlencode($headquarter);
        $url = '/BrmMonthlyReport?target_month='.$target_month.'&hq_id='.$hq_id.'&hq_name='.$headquarter.'&term_id='.$term_id.'&term_name='.$term_name;

        if ($head_or_dept == "0") { #Headquater

            $ba_by_hq = $this->Layer->find('list', array(
                'fields' => 'ba_code',
                'conditions' => array(
                    'head_department' => $hdname,
                    'dept_id !=' => '',
                    'head_dept_id !=' => '',
                    'flag !=' => 0
                )
            ));

            $toLevel = array($level_12,$level_13,$level_14,$level_15,$level_16);
            $toEmail = $Common->getEmail($toLevel);# get email address from user table

            $to_id_list = array('AD100','AD200','AD300','LC100','AD500');
            $emails = $Common->getEmailByLoginID($to_id_list);

            $toEmail = array_merge($toEmail, $emails);

            $md_email = $Common->getEmailExceptCurrent($level_9, $login_id);
            $toEmail = array_merge($toEmail, $md_email);

            $toEmail = array_unique($toEmail);

            $ccLevel = array($level_3,$level_4);
            $ccEmail = $Common->getEmail($ccLevel);

            $ccLevelByBA = array($level_5,$level_8,$level_6,$level_10);
            $ccEmailByBA = $Common->getEmailByBA($ba_by_hq, $ccLevelByBA);

            $ccEmail = array_merge($ccEmail, $ccEmailByBA);

            $cc_id = 'AC014';
            $emails = $Common->getEmailByLoginID($cc_id);
            $ccEmail = array_merge($ccEmail, $emails);
            $ccEmail = array_unique($ccEmail);

            if ($action == 'approve') { #Headquarter Approve
                $mail['subject']	 	= '【月次業績報告】'.$hdname.' 承認完了通知';
                $mail['template_title'] = '各位';
                $mail['template_body'] 	= $target_month.'月度の'.$hdname.'の月次業績報告は本部長承認されましたので、下記リンクより内容をご確認ください。<br/>';
            } elseif ($action == 'approve_cancel') { #Headquarter Approve Cancel
                $mail['subject']	 	= '【月次業績報告】'.$hdname.' 承認キャンセル通知';
                $mail['template_title'] = '各位';
                $mail['template_body'] 	= $target_month.'月度の'.$hdname.'の月次業績報告のデータを確認した結果、本部長承認がキャンセルされました。<br/>下記提出期日までに再度、承認予定です。<br/><br/>
					提出期日：'.$deadline_date.'営業時間内<br/>';
            }
        } else { #Department

            $ba_by_dept = $this->Layer->find('list', array(
                'fields' => 'ba_code',
                'conditions' => array(
                    'department' => $hdname,
                    'head_dept_id <>' => '',
                    'flag <>' => 0,
                )
            ));

            $toLevel = array($level_6,$level_10);
            # get email address from user table by ba_code
            $toEmail = $Common->getEmailByBA($ba_by_dept, $toLevel);
            $ccEmail = '';# get email address from user table

            if ($action == 'approve') { #Department Approve
                $mail['subject']	 	= '【月次業績報告】'.$hdname.' 部署によるデータ入力承認完了通知';
                $mail['template_title'] = '各位';
                $mail['template_body'] 	= $target_month.'月度の'.$hdname.'の月次業績報告が承認されました。<br/>';
            } elseif ($action == 'approve_cancel') { #Department Approve Cancel

                $mail['subject']	 	= '【月次業績報告】'.$hdname.' 部署によるデータ入力 承認キャンセル通知';
                $mail['template_title'] = '各位';
                $mail['template_body'] 	= $target_month.'月度の'.$hdname.'の月次業績報告のデータを確認した結果、承認がキャンセルされました。<br/>内容を再度確認し、改めて依頼してください。<br/><br/>
					提出期日：'.$deadline_date.'営業時間内<br/>';
            }
        }

        if (!empty($toEmail)) {
            $sentMail = parent::sendEmailP3($target_month, $login_user, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
            
            if ($sentMail['error'] == 1 && $sentMail['errormsg'] != "") { # invalid
                
                return "mail_invalid";
            } else { # valid

                return "success";
            }
        } else {
            return "mail_empty";
        }
    }
 

    /**
     * excel download export method
     *
     * @author NuNuLwin (20200221)
     * @throws NotFoundException
     * @param no param
     * @return void
     */

    public function excelDownloadMonthlyReport()
    {
        if ($this->Session->check('TERM_NAME')) {
            $budget_term = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('TARGETMONTH')) {
            $target_month = $this->Session->read('TARGETMONTH');
        }
        if ($this->Session->check('HEAD_DEPT_NAME')) {
            $headquarter = $this->Session->read('HEAD_DEPT_NAME');
        }

        if ($this->Session->check('HEAD_DEPT_ID')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('HEAD_DEPT_CODE')) {
            $hlayer_code = $this->Session->read('HEAD_DEPT_CODE');
        }
        if (empty($budget_term) || empty($term_id) || empty($target_month) || empty($head_dept_id) || empty($hlayer_code) ||empty($headquarter)) {
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }

        $PHPExcel = $this->PhpExcel;
        $file_name = 'BrmMonthlyReport';
        $this->DownloadExcel($term_id, $budget_term, $target_month, $hlayer_code, $headquarter, $file_name, $PHPExcel);
        $this->redirect(array('controller'=>'BrmMonthlyReport','action'=>'index'));
    }

    public function DownloadExcel($term_id, $budget_term, $target_month, $hlayer_code, $hlayer_name, $file_name, $PHPExcel, $save_to_tmp=false)
    {
        $head_forecast_month1 = '';
        $head_forecast_month2 = '';
        $head_forecast_month3 = '';
        $head_forecast_year   = '';
        $head_forecast_remark = '';
        $head_p3_file_name    = array();

        $dept_forecast_month1 = '';
        $dept_forecast_month2 = '';
        $dept_forecast_month3 = '';
        $dept_forecast_year   = '';
        $dept_forecast_remark = '';
        $dept_p3_file_name    = array();

        $MM1 = date("Y-m", strtotime($target_month. "last day of + 1 months"));
        $MM2 = date("Y-m", strtotime($target_month. "last day of + 2 months"));
        $MM3 = date("Y-m", strtotime($target_month. "last day of + 3 months"));

        $excel_row = 6;
        #Start Excel Preparation
        // $PHPExcel = $PHPExcel;
        $objPHPExcel = $PHPExcel->createWorksheet()->setDefaultFont('Cambria', 12);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3);
        $objPHPExcel ->getActiveSheet()->setShowGridlines(true);
        $objPHPExcel ->setActiveSheetIndex(0);
        $cell_name = "C1";
        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
        

        $objPHPExcel->getActiveSheet()->setTitle(__('月次業績報告'));
    
        $sheet = $PHPExcel->getActiveSheet();
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );
        $sheet->getStyle("C")->applyFromArray($style);
        $border_dash = array(
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                ));
        $aligncenter = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
        );
        $alignleft = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    
                ),
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                )
        );
        $alignright = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ),
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                )
        );
        $aligntop = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
                ),
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                )
        );
        $negative = array(
                'font'  => array(
                    'color' => array('rgb' => 'FF0000')
                ));

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(21);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension("D")->setWidth(12);
        $sheet->getColumnDimension("E")->setWidth(12);
        $sheet->getColumnDimension("F")->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(13);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(12);
        $sheet->getColumnDimension('L')->setWidth(12);
        $sheet->getColumnDimension('M')->setWidth(12);
        $sheet->getColumnDimension('N')->setWidth(12);
        $sheet->getColumnDimension('O')->setWidth(12);
        $sheet->getColumnDimension('P')->setWidth(12);
        #End Excel Preparation

        #Excel Title(Monthly Report)
        $sheet->setCellValue('C1', __("月次業績報告"));
        $sheet->mergeCells('C1:L1');
        $sheet->getStyle('C1:L1')->applyFromArray($aligncenter);

        #Read Data from Cache data
        $cache_data = Cache::read('monthly_report_excel_'.$term_id.'_'.$hlayer_code.'_'.$target_month);

        #Data prepare for excel download
        if (!empty($cache_data)) {#Get cache from function index()
            
            $layerTypeData = $this->Session->read('LayerTypeData');
            $layerType = $layerTypeData[SETTING::LAYER_SETTING['topLayer']];
            #Start "budget term, terget month, headquarter"
            $sheet->setCellValue('A1', __("期間"));
            $sheet->getStyle('A1')->applyFromArray($alignleft);
            $sheet->getStyle('A1')->applyFromArray($border_dash);
            $sheet->setCellValue('A2', __("対象月"));
            $sheet->getStyle('A2')->applyFromArray($alignleft);
            $sheet->getStyle('A2')->applyFromArray($border_dash);
            $sheet->setCellValue('A3', __($layerType));
            $sheet->getStyle('A3')->applyFromArray($alignleft);
            $sheet->getStyle('A3')->applyFromArray($border_dash);
                                
            $sheet->setCellValue('B1', $budget_term);
            $sheet->getStyle('B1')->applyFromArray($alignleft);
            $sheet->getStyle('B1')->applyFromArray($border_dash);
            $sheet->setCellValue('B2', $target_month);
            $sheet->getStyle('B2')->applyFromArray($alignleft);
            $sheet->getStyle('B2')->applyFromArray($border_dash);
            $sheet->setCellValue('B3', $hlayer_name);
            $sheet->getStyle('B3')->applyFromArray($alignleft);
            $sheet->getStyle('B3')->applyFromArray($border_dash);
            #End "budget term, terget month, headquarter"

            $head_data = $cache_data['head_data']['data'];
            $head_input = $cache_data['head_data'];
            if (!empty($head_data)) {
                
                #For Headquarter
                #Headquarter drop down and label
                $sheet->setCellValue('A'.$excel_row, $hlayer_name);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$excel_row)->getFont()->setBold(true);
                $excel_row += 1;
                $sheet->setCellValue('A'.$excel_row, __("1. 業績"));
                $objPHPExcel->getActiveSheet()->getStyle('A'.$excel_row)->getFont()->setBold(true);
                    
                #Start Headquarter achievements table header
                $excel_row += 2;
                $sheet->getStyle('A'.$excel_row.':N'.$excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
                $sheet->getStyle('A'.($excel_row+1).':N'.($excel_row+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
                $objPHPExcel->getActiveSheet()->getStyle('A:N')->getAlignment()->setWrapText(true);

                $sheet->setCellValue('A'.$excel_row, "");
                $sheet->mergeCells('A'.$excel_row.':A'.($excel_row+1));
                $sheet->getStyle('A'.$excel_row.':A'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('A'.$excel_row.':A'.($excel_row+1))->applyFromArray($border_dash);
                        
                $sheet->setCellValue('B'.$excel_row, "");
                $sheet->mergeCells('B'.$excel_row.':B'.($excel_row+1));
                $sheet->getStyle('B'.$excel_row.':B'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('B'.$excel_row.':B'.($excel_row+1))->applyFromArray($border_dash);
                        
                $sheet->setCellValue('C'.$excel_row, __("当月"));
                $sheet->mergeCells('C'.$excel_row.':G'.$excel_row);
                $sheet->getStyle('C'.$excel_row.':G'.$excel_row)->applyFromArray($aligncenter);
                $sheet->getStyle('C'.$excel_row.':G'.$excel_row)->applyFromArray($border_dash);

                $sheet->setCellValue('C'.($excel_row+1), __("予算"));
                $sheet->getStyle('C'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('C'.($excel_row+1))->applyFromArray($border_dash);
                $sheet->setCellValue('D'.($excel_row+1), __("実績"));
                $sheet->getStyle('D'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('D'.($excel_row+1))->applyFromArray($border_dash);
                $sheet->setCellValue('E'.($excel_row+1), __("予実比"));
                $sheet->getStyle('E'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('E'.($excel_row+1))->applyFromArray($border_dash);
                $sheet->setCellValue('F'.($excel_row+1), __("前年実績"));
                $sheet->getStyle('F'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('F'.($excel_row+1))->applyFromArray($border_dash);
                $sheet->setCellValue('G'.($excel_row+1), __("前年同月比"));
                $sheet->getStyle('G'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('G'.($excel_row+1))->applyFromArray($border_dash);
                        

                $sheet->setCellValue('H'.$excel_row, __("累計"));
                $sheet->mergeCells('H'.$excel_row.':L'.$excel_row);
                $sheet->getStyle('H'.$excel_row.':L'.$excel_row)->applyFromArray($aligncenter);
                $sheet->getStyle('H'.$excel_row.':L'.$excel_row)->applyFromArray($border_dash);
                        
                $sheet->setCellValue('H'.($excel_row+1), __("予算"));
                $sheet->getStyle('H'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('H'.($excel_row+1))->applyFromArray($border_dash);
                $sheet->setCellValue('I'.($excel_row+1), __("実績"));
                $sheet->getStyle('I'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('I'.($excel_row+1))->applyFromArray($border_dash);
                $sheet->setCellValue('J'.($excel_row+1), __("予実比"));
                $sheet->getStyle('J'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('J'.($excel_row+1))->applyFromArray($border_dash);
                $sheet->setCellValue('K'.($excel_row+1), __("前年実績"));
                $sheet->getStyle('K'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('K'.($excel_row+1))->applyFromArray($border_dash);
                $sheet->setCellValue('L'.($excel_row+1), __("前年同月比"));
                $sheet->getStyle('L'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('L'.($excel_row+1))->applyFromArray($border_dash);

                $sheet->setCellValue('M'.$excel_row, __("予算"));
                $sheet->mergeCells('M'.$excel_row.':N'.$excel_row);
                $sheet->getStyle('M'.$excel_row.':N'.$excel_row)->applyFromArray($aligncenter);
                $sheet->getStyle('M'.$excel_row.':N'.$excel_row)->applyFromArray($border_dash);
                        
                $sheet->setCellValue('M'.($excel_row+1), __("年間"));
                $sheet->getStyle('M'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('M'.($excel_row+1))->applyFromArray($border_dash);
                $sheet->setCellValue('N'.($excel_row+1), __("進捗率"));
                $sheet->getStyle('N'.($excel_row+1))->applyFromArray($aligncenter);
                $sheet->getStyle('N'.($excel_row+1))->applyFromArray($border_dash);

                $excel_row+=2;
                #End Headquarter achievements table header

                #Start table body with data
                   
                foreach ($head_data as $key => $each_dept) {
                    $head_total = $each_dept['本部合計'];
                        
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$excel_row, $key);

                    $sheet->mergeCells('A'.$excel_row.':A'.(count($each_dept)+($excel_row-1)));

                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$excel_row, __("本部合計"));
                    $sheet->getStyle('B'.$excel_row.':N'.$excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
                    $objPHPExcel->getActiveSheet()->getStyle('B:N')->getAlignment()->setWrapText(true);

                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$excel_row, (round($head_total['tm_budget']/1000000)));
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$excel_row, (round($head_total['tm_result']/1000000)));
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$excel_row, (round($head_total['tm_ratio']/1000000)));
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$excel_row, (round($head_total['tm_previous_y_r']/1000000)));
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$excel_row, (round($head_total['tm_yoy_change']/1000000)));
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$excel_row, (round($head_total['total_tm_budget']/1000000)));
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$excel_row, (round($head_total['total_tm_result']/1000000)));
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$excel_row, (round($head_total['total_ratio']/1000000)));
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$excel_row, (round($head_total['previous_y_r']/1000000)));
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$excel_row, (round($head_total['yoy_change']/1000000)));
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$excel_row, (round($head_total['yearly_budget']/1000000)));
                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$excel_row, $head_total['achieve_rate']);

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$excel_row)->applyFromArray($alignleft);

                    $sheet->getStyle('A'.$excel_row.':A'.(count($each_dept)+($excel_row-1)))->applyFromArray($border_dash);
                    $sheet->getStyle('B'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('C'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('D'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('E'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('F'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('G'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('H'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('I'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('J'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('K'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('L'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('M'.$excel_row)->applyFromArray($border_dash);
                    $sheet->getStyle('N'.$excel_row)->applyFromArray($border_dash);

                    $sheet->getStyle('B'.$excel_row)->applyFromArray($alignleft);
                    $sheet->getStyle('C'.$excel_row)->applyFromArray($alignright);
                    $sheet->getStyle('D'.$excel_row)->applyFromArray($alignright);
                    $sheet->getStyle('E'.$excel_row)->applyFromArray($alignright);
                    $sheet->getStyle('F'.$excel_row)->applyFromArray($alignright);
                    $sheet->getStyle('G'.$excel_row)->applyFromArray($alignright);
                    $sheet->getStyle('H'.$excel_row)->applyFromArray($alignright);
                    $sheet->getStyle('I'.$excel_row)->applyFromArray($alignright);
                    $sheet->getStyle('J'.$excel_row)->applyFromArray($alignright);
                    $sheet->getStyle('K'.$excel_row)->applyFromArray($alignright);
                    $sheet->getStyle('L'.$excel_row)->applyFromArray($alignright);
                    $sheet->getStyle('M'.$excel_row)->applyFromArray($alignright);
                    $sheet->getStyle('N'.$excel_row)->applyFromArray($alignright);

                    foreach ($each_dept as $sub_key => $sub_data) { 
                        if ($sub_key != "本部合計") {
                            $objPHPExcel->getActiveSheet()->setCellValue('B'.$excel_row, $sub_key);
                            $objPHPExcel->getActiveSheet()->setCellValue('C'.$excel_row, (round($sub_data['tm_budget']/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('D'.$excel_row, (round($sub_data['tm_result']/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('E'.$excel_row, (round($sub_data['tm_ratio']/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('F'.$excel_row, (round($sub_data['tm_previous_y_r']/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('G'.$excel_row, (round($sub_data['tm_yoy_change']/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('H'.$excel_row, (round($sub_data['total_tm_budget']/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('I'.$excel_row, (round($sub_data['total_tm_result']/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('J'.$excel_row, (round($sub_data['total_ratio']/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('K'.$excel_row, (round($sub_data['previous_y_r']/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('L'.$excel_row, (round($sub_data['yoy_change']/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('M'.$excel_row, (round($sub_data['yearly_budget']/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('N'.$excel_row, $sub_data['achieve_rate']);


                            $sheet->getStyle('B'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('C'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('D'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('E'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('F'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('G'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('H'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('I'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('J'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('K'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('L'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('M'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('N'.$excel_row)->applyFromArray($border_dash);

                                
                            $objPHPExcel->getActiveSheet()->getStyle('B'.$excel_row)->getAlignment()->setIndent(1);
                            $sheet->getStyle('C'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('D'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('E'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('F'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('G'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('H'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('I'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('J'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('K'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('L'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('M'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('N'.$excel_row)->applyFromArray($alignright);
                        }
                        $excel_row ++;
                    }
                }
                #End table body
                
                #Start "2.Total Result Overview" text area =>8 rows 6 cols
                #Total Result Overview headquater Overview comment validation when data Null
                if ($head_input['overview_cmt']=='NULL') {
                    $head_input['overview_cmt'] ="";
                }
                        
                $excel_row = $excel_row+2;
                $sheet->setCellValue('A'.$excel_row, __("2. 単月・累計実績　概況説明"));
                $objPHPExcel->getActiveSheet()->getStyle('A'.$excel_row)->getFont()->setBold(true);
                $sheet->mergeCells('A'.$excel_row.':B'.$excel_row);

                $excel_row++;
                        
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$excel_row, $head_input['overview_cmt']);
                $sheet->getStyle('A'.$excel_row)->applyFromArray($alignleft);
                $sheet->getStyle('A'.$excel_row)->applyFromArray($aligntop);
                              
                $sheet->mergeCells('A'.$excel_row.':E'.($excel_row+7));
                $sheet->getStyle('A'.$excel_row.':E'.($excel_row+7))->applyFromArray($border_dash);
                #End "2.Total Result Overview" text area =>8 rows 6 cols
                        
                #Start 3.今後3ヶ月の業績予想と年間見込 Table
                $excel_row--;
                $sheet->setCellValue('G'.$excel_row, __("3. 今後3ヶ月の業績予想と年間見込"));
                $objPHPExcel->getActiveSheet()->getStyle('G'.$excel_row)->getFont()->setBold(true);
                $sheet->mergeCells('G'.$excel_row.':K'.$excel_row);
                $excel_row++;

                #Start Table thead row
                $sheet->setCellValue('G'.$excel_row, "");
                $sheet->getStyle('G'.$excel_row)->applyFromArray($aligncenter);
                $sheet->getStyle('G'.$excel_row)->applyFromArray($border_dash);
                            
                $sheet->setCellValue('H'.$excel_row, $MM1);
                $sheet->getStyle('H'.$excel_row)->applyFromArray($aligncenter);
                $sheet->getStyle('H'.$excel_row)->applyFromArray($border_dash);

                $sheet->setCellValue('I'.$excel_row, $MM2);
                $sheet->getStyle('I'.$excel_row)->applyFromArray($aligncenter);
                $sheet->getStyle('I'.$excel_row)->applyFromArray($border_dash);

                $sheet->setCellValue('J'.$excel_row, $MM3);
                $sheet->getStyle('J'.$excel_row)->applyFromArray($aligncenter);
                $sheet->getStyle('J'.$excel_row)->applyFromArray($border_dash);

                $sheet->setCellValue('K'.$excel_row, __("年間"));
                $sheet->getStyle('K'.$excel_row)->applyFromArray($aligncenter);
                $sheet->getStyle('K'.$excel_row)->applyFromArray($border_dash);

                $sheet->getStyle('G'.$excel_row.':K'.$excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
                #End Table thead row
                (count($departments) == count($monthly_rp))? $this->set('approveHead', "Yes"):$this->set('approveHead', "No");
                $h_forecast = $head_input['forecast'];

                $head_forecast_month1 = (!empty($h_forecast['month1_forecast']) || $h_forecast['month1_forecast'] == '0')? h(($h_forecast['month1_forecast'])): "";
                $head_forecast_month2 = (!empty($h_forecast['month2_forecast'])|| $h_forecast['month2_forecast'] == '0')? h(($h_forecast['month2_forecast'])): "";
                $head_forecast_month3 = (!empty($h_forecast['month3_forecast'])|| $h_forecast['month3_forecast'] == '0')? h(($h_forecast['month3_forecast'])): "";
                $head_forecast_year   = (!empty($h_forecast['yearly_forecast'])|| $h_forecast['yearly_forecast'] == '0')? h(($h_forecast['yearly_forecast'])): "";
                $head_forecast_remark = $h_forecast['remark'];
                                   
                #Start tbody 1st Row
                $excel_row++;
                $sheet->setCellValue('G'.$excel_row, __("当期利益(予算)"));
                $sheet->getStyle('G'.$excel_row)->applyFromArray($alignleft);
                $sheet->getStyle('G'.$excel_row)->applyFromArray($border_dash);
                $h_annual_budget = $head_input['annual_budget'];
                            
                $sheet->setCellValue('H'.$excel_row, ($h_annual_budget['h_next_budget']));
                $sheet->getStyle('H'.$excel_row)->applyFromArray($alignright);
                $sheet->getStyle('H'.$excel_row)->applyFromArray($border_dash);

                            
                $sheet->setCellValue('I'.$excel_row, ($h_annual_budget['h_next2month_budget']));
                $sheet->getStyle('I'.$excel_row)->applyFromArray($alignright);
                $sheet->getStyle('I'.$excel_row)->applyFromArray($border_dash);

                            
                $sheet->setCellValue('J'.$excel_row, ($h_annual_budget['h_next3month_budget']));
                $sheet->getStyle('J'.$excel_row)->applyFromArray($alignright);
                $sheet->getStyle('J'.$excel_row)->applyFromArray($border_dash);

                $sheet->setCellValue('K'.$excel_row, ($h_annual_budget['h_yearly_budget']));
                $sheet->getStyle('K'.$excel_row)->applyFromArray($alignright);
                $sheet->getStyle('K'.$excel_row)->applyFromArray($border_dash);
                #End tbody 1st Row
                            
                #Start tbody 2th Row

                $excel_row++;
                $sheet->setCellValue('G'.$excel_row, __("当期利益(予測)\n※百万円単位"));
                $sheet->getRowDimension($excel_row)->setRowHeight(35);
                $sheet->getStyle('G'.$excel_row)->getFont()->setSize(10);
                $sheet->getStyle('G'.$excel_row)->applyFromArray($alignleft);
                $sheet->getStyle('G'.$excel_row)->applyFromArray($border_dash);
                            
                $sheet->setCellValue('H'.$excel_row, $head_forecast_month1);
                $sheet->getStyle('H'.$excel_row)->applyFromArray($alignright);
                $sheet->getStyle('H'.$excel_row)->applyFromArray($border_dash);

                $sheet->setCellValue('I'.$excel_row, $head_forecast_month2);
                $sheet->getStyle('I'.$excel_row)->applyFromArray($alignright);
                $sheet->getStyle('I'.$excel_row)->applyFromArray($border_dash);

                $sheet->setCellValue('J'.$excel_row, $head_forecast_month3);
                $sheet->getStyle('J'.$excel_row)->applyFromArray($alignright);
                $sheet->getStyle('J'.$excel_row)->applyFromArray($border_dash);

                $sheet->setCellValue('K'.$excel_row, $head_forecast_year);
                $sheet->getStyle('K'.$excel_row)->applyFromArray($alignright);
                $sheet->getStyle('K'.$excel_row)->applyFromArray($border_dash);
                #End tbody 2th Row

                #Start TextArea

                #Next 3 month forecast and annual prospects headdept validation when data Null
                if ($head_forecast_remark=='NULL') {
                    $head_forecast_remark ="";
                }
                $excel_row++;
                $sheet->mergeCells('G'.$excel_row.':L'.($excel_row+4));
                $sheet->setCellValue('G'.$excel_row, $head_forecast_remark);
                $sheet->getStyle('G'.$excel_row)->applyFromArray($alignleft);
                $sheet->getStyle('G'.$excel_row)->applyFromArray($aligntop);
                $sheet->getStyle('G'.$excel_row.':L'.($excel_row+4))->applyFromArray($border_dash);
                #End TextArea
                #End 3.今後3ヶ月の業績予想と年間見込 Table

                #Start Attachement Upload
                $excel_row = $excel_row+6;
                $sheet->setCellValue('A'.$excel_row, __("4. 添付資料"));
                $objPHPExcel->getActiveSheet()->getStyle('A'.$excel_row)->getFont()->setBold(true);
                $sheet->mergeCells('A'.$excel_row.':B'.$excel_row);
                $excel_row++;
                $head_attached = $head_input['attached'];
                if (!empty($head_attached)) {
                    $head_p3 = implode(',   ', $head_attached);
                    $sheet->setCellValue('A'.$excel_row, $head_p3);
                }

                $sheet->getStyle('A'.$excel_row)->applyFromArray($alignleft);
                $sheet->getStyle('A'.$excel_row)->applyFromArray($aligntop);
                        
                $sheet->mergeCells('A'.$excel_row.':H'.($excel_row+2));
                $sheet->getStyle('A'.$excel_row.':H'.($excel_row+2))->applyFromArray($border_dash);
                #End Attachement Upload

                #End Headquarter

                #For Department Loop
                $dept = $cache_data['dept_data'];
                   
                foreach ($dept as $each_dept => $d_value) {
                    $each_dept_id   = $d_value['id'];
                    $each_dept_data = $d_value['data'];
                    $each_budget    = $d_value['annual_budget'];
                    $overview_cmt	= $d_value['overview_cmt'];
                    $forecast		= $d_value['forecast'];
                    $attached		= $d_value['attached'];

                    #Total Result Overview dept Overview commnet validation
                    if ($overview_cmt == 'NULL') {
                        $overview_cmt = "";
                    }
                    #Department drop down and label
                    $excel_row = $excel_row+4;
                    if (!empty($each_dept_data)) {
                        $sheet->setCellValue('A'.$excel_row, $each_dept);
                        $sheet->setCellValue('A'.($excel_row+1), __("1. 業績"));
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$excel_row)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->getStyle('A'.($excel_row+1))->getFont()->setBold(true);
                        #Start Department achievements table header
                        $excel_row = $excel_row+2;

                        $sheet->getStyle('A'.$excel_row.':N'.$excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
                        $sheet->getStyle('A'.($excel_row+1).':N'.($excel_row+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
                        $objPHPExcel->getActiveSheet()->getStyle('A:N')->getAlignment()->setWrapText(true);

                                
                        $sheet->setCellValue('A'.$excel_row, "");
                        $sheet->mergeCells('A'.$excel_row.':A'.($excel_row+1));
                        $sheet->getStyle('A'.$excel_row.':A'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('A'.$excel_row.':A'.($excel_row+1))->applyFromArray($border_dash);
                                
                        $sheet->setCellValue('B'.$excel_row, "");
                        $sheet->mergeCells('B'.$excel_row.':B'.($excel_row+1));
                        $sheet->getStyle('B'.$excel_row.':B'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('B'.$excel_row.':B'.($excel_row+1))->applyFromArray($border_dash);
                                
                        $sheet->setCellValue('C'.$excel_row, __("当月"));
                        $sheet->mergeCells('C'.$excel_row.':G'.$excel_row);
                        $sheet->getStyle('C'.$excel_row.':G'.$excel_row)->applyFromArray($aligncenter);
                        $sheet->getStyle('C'.$excel_row.':G'.$excel_row)->applyFromArray($border_dash);

                        $sheet->setCellValue('C'.($excel_row+1), __("予算"));
                        $sheet->getStyle('C'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('C'.($excel_row+1))->applyFromArray($border_dash);
                        $sheet->setCellValue('D'.($excel_row+1), __("実績"));
                        $sheet->getStyle('D'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('D'.($excel_row+1))->applyFromArray($border_dash);
                        $sheet->setCellValue('E'.($excel_row+1), __("予実比"));
                        $sheet->getStyle('E'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('E'.($excel_row+1))->applyFromArray($border_dash);
                        $sheet->setCellValue('F'.($excel_row+1), __("前年実績"));
                        $sheet->getStyle('F'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('F'.($excel_row+1))->applyFromArray($border_dash);
                        $sheet->setCellValue('G'.($excel_row+1), __("前年同月比"));
                        $sheet->getStyle('G'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('G'.($excel_row+1))->applyFromArray($border_dash);
                                

                        $sheet->setCellValue('H'.$excel_row, __("累計"));
                        $sheet->mergeCells('H'.$excel_row.':L'.$excel_row);
                        $sheet->getStyle('H'.$excel_row.':L'.$excel_row)->applyFromArray($aligncenter);
                        $sheet->getStyle('H'.$excel_row.':L'.$excel_row)->applyFromArray($border_dash);
                                
                        $sheet->setCellValue('H'.($excel_row+1), __("予算"));
                        $sheet->getStyle('H'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('H'.($excel_row+1))->applyFromArray($border_dash);
                        $sheet->setCellValue('I'.($excel_row+1), __("実績"));
                        $sheet->getStyle('I'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('I'.($excel_row+1))->applyFromArray($border_dash);
                        $sheet->setCellValue('J'.($excel_row+1), __("予実比"));
                        $sheet->getStyle('J'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('J'.($excel_row+1))->applyFromArray($border_dash);
                        $sheet->setCellValue('K'.($excel_row+1), __("前年実績"));
                        $sheet->getStyle('K'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('K'.($excel_row+1))->applyFromArray($border_dash);
                        $sheet->setCellValue('L'.($excel_row+1), __("前年同月比"));
                        $sheet->getStyle('L'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('L'.($excel_row+1))->applyFromArray($border_dash);

                        $sheet->setCellValue('M'.$excel_row, __("予算"));
                        $sheet->mergeCells('M'.$excel_row.':N'.$excel_row);
                        $sheet->getStyle('M'.$excel_row.':N'.$excel_row)->applyFromArray($aligncenter);
                        $sheet->getStyle('M'.$excel_row.':N'.$excel_row)->applyFromArray($border_dash);
                                
                        $sheet->setCellValue('M'.($excel_row+1), __("年間"));
                        $sheet->getStyle('M'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('M'.($excel_row+1))->applyFromArray($border_dash);
                        $sheet->setCellValue('N'.($excel_row+1), __("進捗率"));
                        $sheet->getStyle('N'.($excel_row+1))->applyFromArray($aligncenter);
                        $sheet->getStyle('N'.($excel_row+1))->applyFromArray($border_dash);
                        #End Department achievements table header

                        #Start tbody with data
                        foreach ($each_dept_data as $sub_accname => $data_value) {
                            $dept_total = $data_value['部合計'];

                            $tot_tm_budget          = $dept_total['tm_budget'];
                            $tot_tm_result          = $dept_total['tm_result'];
                            $tot_tm_ratio           = $dept_total['tm_ratio'];
                            $tot_tm_previous_y_r    = $dept_total['tm_previous_y_r'];
                            $tot_tm_yoy_change      = $dept_total['tm_yoy_change'];
                            $tot_total_tm_budget    = $dept_total['total_tm_budget'];
                            $tot_total_tm_result    = $dept_total['total_tm_result'];
                            $tot_total_ratio        = $dept_total['total_ratio'];
                            $tot_previous_y_r       = $dept_total['previous_y_r'];
                            $tot_yoy_change         = $dept_total['yoy_change'];
                            $tot_yearly_budget      = $dept_total['yearly_budget'];
                            $tot_achieve_rate       = $dept_total['achieve_rate'];
                                    
                            if ($sub_accname == "d_next_budget" || $sub_accname == "d_next2month_budget" || $sub_accname == "d_next3month_budget") {
                                continue;
                            }
                            $excel_row = $excel_row+2;
                            $rowspan = count($data_value)+($excel_row-1);
                                    
                            $objPHPExcel->getActiveSheet()->setCellValue('A'.$excel_row, $sub_accname);
                            
                            $sheet->mergeCells('A'.$excel_row.':A'.$rowspan);
                                    
                            $objPHPExcel->getActiveSheet()->setCellValue('B'.$excel_row, __("部合計"));
                            $sheet->getStyle('B'.$excel_row.':N'.$excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
                            $objPHPExcel->getActiveSheet()->getStyle('B:N')->getAlignment()->setWrapText(true);

                            $objPHPExcel->getActiveSheet()->setCellValue('C'.$excel_row, (round($tot_tm_budget/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('D'.$excel_row, (round($tot_tm_result/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('E'.$excel_row, (round($tot_tm_ratio/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('F'.$excel_row, (round($tot_tm_previous_y_r/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('G'.$excel_row, (round($tot_tm_yoy_change/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('H'.$excel_row, (round($tot_total_tm_budget/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('I'.$excel_row, (round($tot_total_tm_result/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('J'.$excel_row, (round($tot_total_ratio/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('K'.$excel_row, (round($tot_previous_y_r/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('L'.$excel_row, (round($tot_yoy_change/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('M'.$excel_row, (round($tot_yearly_budget/1000000)));
                            $objPHPExcel->getActiveSheet()->setCellValue('N'.$excel_row, $tot_achieve_rate);
                                    
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$excel_row)->applyFromArray($alignleft);
                            $objPHPExcel->getActiveSheet()->getStyle('B'.$excel_row)->applyFromArray($alignleft);
                            $sheet->getStyle('A'.$excel_row.':A'.$rowspan)->applyFromArray($border_dash);
                            $sheet->getStyle('B'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('C'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('D'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('E'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('F'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('G'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('H'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('I'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('J'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('K'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('L'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('M'.$excel_row)->applyFromArray($border_dash);
                            $sheet->getStyle('N'.$excel_row)->applyFromArray($border_dash);
                                    
                            $sheet->getStyle('C'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('D'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('E'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('F'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('G'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('H'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('I'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('J'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('K'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('L'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('M'.$excel_row)->applyFromArray($alignright);
                            $sheet->getStyle('N'.$excel_row)->applyFromArray($alignright);
                            foreach ($data_value as $ba_key => $ba_data) {
                                if ($ba_key != "部合計") {
                                    $tm_budget          = (round($ba_data['tm_budget']/1000000));
                                    $tm_result          = (round($ba_data['tm_result']/1000000));
                                    $tm_ratio           = (round($ba_data['tm_ratio']/1000000));
                                    $tm_previous_y_r    = (round($ba_data['tm_previous_y_r']/1000000));
                                    $tm_yoy_change      = (round($ba_data['tm_yoy_change']/1000000));
                                    $total_tm_budget    = (round($ba_data['total_tm_budget']/1000000));
                                    $total_tm_result    = (round($ba_data['total_tm_result']/1000000));
                                    $total_ratio        = (round($ba_data['total_ratio']/1000000));
                                    $previous_y_r       = (round($ba_data['previous_y_r']/1000000));
                                    $yoy_change         = (round($ba_data['yoy_change']/1000000));
                                    $yearly_budget      = (round($ba_data['yearly_budget']/1000000));
                                    $achieve_rate       = $ba_data['achieve_rate'];

                                    $excel_row++;
                                                                        
                                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$excel_row, $ba_key);
                                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$excel_row, $tm_budget);
                                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$excel_row, $tm_result);
                                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$excel_row, $tm_ratio);
                                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$excel_row, $tm_previous_y_r);
                                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$excel_row, $tm_yoy_change);
                                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$excel_row, $total_tm_budget);
                                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$excel_row, $total_tm_result);
                                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$excel_row, $total_ratio);
                                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$excel_row, $previous_y_r);
                                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$excel_row, $yoy_change);
                                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$excel_row, $yearly_budget);
                                    $objPHPExcel->getActiveSheet()->setCellValue('N'.$excel_row, $achieve_rate);


                                    $sheet->getStyle('B'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('C'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('D'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('E'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('F'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('G'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('H'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('I'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('J'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('K'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('L'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('M'.$excel_row)->applyFromArray($border_dash);
                                    $sheet->getStyle('N'.$excel_row)->applyFromArray($border_dash);

                                    //$sheet->getStyle('B'.$excel_row)->applyFromArray($alignleft);
                                    $objPHPExcel->getActiveSheet()->getStyle('B'.$excel_row)->getAlignment()->setIndent(1);
                                    $sheet->getStyle('C'.$excel_row)->applyFromArray($alignright);
                                    $sheet->getStyle('D'.$excel_row)->applyFromArray($alignright);
                                    $sheet->getStyle('E'.$excel_row)->applyFromArray($alignright);
                                    $sheet->getStyle('F'.$excel_row)->applyFromArray($alignright);
                                    $sheet->getStyle('G'.$excel_row)->applyFromArray($alignright);
                                    $sheet->getStyle('H'.$excel_row)->applyFromArray($alignright);
                                    $sheet->getStyle('I'.$excel_row)->applyFromArray($alignright);
                                    $sheet->getStyle('J'.$excel_row)->applyFromArray($alignright);
                                    $sheet->getStyle('K'.$excel_row)->applyFromArray($alignright);
                                    $sheet->getStyle('L'.$excel_row)->applyFromArray($alignright);
                                    $sheet->getStyle('M'.$excel_row)->applyFromArray($alignright);
                                    $sheet->getStyle('N'.$excel_row)->applyFromArray($alignright);
                                }
                            }
                            $excel_row--;
                        }
                        #End tbody

                        #Start "2.Total Result Overview" text area =>8 rows 6 cols
                        $excel_row = $excel_row+3;
                        $sheet->setCellValue('A'.$excel_row, __("2. 単月・累計実績　概況説明"));
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$excel_row)->getFont()->setBold(true);
                        $sheet->mergeCells('A'.$excel_row.':B'.$excel_row);

                        $excel_row++;
                                
                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$excel_row, $overview_cmt);
                        $sheet->getStyle('A'.$excel_row)->applyFromArray($alignleft);
                        $sheet->getStyle('A'.$excel_row)->applyFromArray($aligntop);
                        $sheet->mergeCells('A'.$excel_row.':E'.($excel_row+7));
                        $sheet->getStyle('A'.$excel_row.':E'.($excel_row+7))->applyFromArray($border_dash);
                        #End "2.Total Result Overview" text area =>8 rows 6 cols
                                
                        #Start 3.今後3ヶ月の業績予想と年間見込 Table
                        $excel_row--;
                        $sheet->setCellValue('G'.$excel_row, __("3. 今後3ヶ月の業績予想と年間見込"));
                        $objPHPExcel->getActiveSheet()->getStyle('G'.$excel_row)->getFont()->setBold(true);
                        $sheet->mergeCells('G'.$excel_row.':L'.$excel_row);
                        $excel_row++;

                        #Start Table thead row
                        $sheet->setCellValue('G'.$excel_row, "");
                        $sheet->getStyle('G'.$excel_row)->applyFromArray($aligncenter);
                        $sheet->getStyle('G'.$excel_row)->applyFromArray($border_dash);
                                    
                        $sheet->setCellValue('H'.$excel_row, $MM1);
                        $sheet->getStyle('H'.$excel_row)->applyFromArray($aligncenter);
                        $sheet->getStyle('H'.$excel_row)->applyFromArray($border_dash);

                        $sheet->setCellValue('I'.$excel_row, $MM2);
                        $sheet->getStyle('I'.$excel_row)->applyFromArray($aligncenter);
                        $sheet->getStyle('I'.$excel_row)->applyFromArray($border_dash);

                        $sheet->setCellValue('J'.$excel_row, $MM3);
                        $sheet->getStyle('J'.$excel_row)->applyFromArray($aligncenter);
                        $sheet->getStyle('J'.$excel_row)->applyFromArray($border_dash);

                        $sheet->setCellValue('K'.$excel_row, __("年間"));
                        $sheet->getStyle('K'.$excel_row)->applyFromArray($aligncenter);
                        $sheet->getStyle('K'.$excel_row)->applyFromArray($border_dash);

                        $sheet->getStyle('G'.$excel_row.':K'.$excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
                        #End Table thead row

                        #Start tbody 1st Row
                        if (!empty($user_cmd)) {
                            foreach ($user_cmd as $key => $value) {
                                if ($value['forecast_dept_id'] == $each_dept_id) {
                                    $dept_forecast_month1 = ($value['forecast_month1']);
                                    $dept_forecast_month2 = ($value['forecast_month2']);
                                    $dept_forecast_month3 = ($value['forecast_month3']);
                                    $dept_forecast_year   = ($value['forecast_year']);
                                    $dept_forecast_remark = h($value['forecast_remark']);
                                }
                            }
                        }
                        $excel_row++;
                        $sheet->setCellValue('G'.$excel_row, __("当期利益(予算)"));
                        $sheet->getStyle('G'.$excel_row)->applyFromArray($alignleft);
                        $sheet->getStyle('G'.$excel_row)->applyFromArray($border_dash);
                                    
                        $sheet->setCellValue('H'.$excel_row, ($each_budget['d_next_budget']));
                        $sheet->getStyle('H'.$excel_row)->applyFromArray($alignright);
                        $sheet->getStyle('H'.$excel_row)->applyFromArray($border_dash);

                        $sheet->setCellValue('I'.$excel_row, ($each_budget['d_next2month_budget']));
                        $sheet->getStyle('I'.$excel_row)->applyFromArray($alignright);
                        $sheet->getStyle('I'.$excel_row)->applyFromArray($border_dash);

                        $sheet->setCellValue('J'.$excel_row, ($each_budget['d_next3month_budget']));
                        $sheet->getStyle('J'.$excel_row)->applyFromArray($alignright);
                        $sheet->getStyle('J'.$excel_row)->applyFromArray($border_dash);

                        $sheet->setCellValue('K'.$excel_row, ($each_budget['d_yearly_budget']));
                        $sheet->getStyle('K'.$excel_row)->applyFromArray($alignright);
                        $sheet->getStyle('K'.$excel_row)->applyFromArray($border_dash);
                        #End tbody 1st Row
                                    
                        #Start tbody 2th Row
                        $d_month1 = $forecast['month1_forecast'];
                        $d_month2 = $forecast['month2_forecast'];
                        $d_month3 = $forecast['month3_forecast'];
                        $d_yearly = $forecast['yearly_forecast'];

                        #forecast and annual prospects dept remark validation
                                    
                        if ($forecast['remark'] =='NULL') {
                            $dept_f_remark = "";
                        } else {
                            $dept_f_remark = $forecast['remark'];
                        }

                                    
                        $excel_row++;
                        $sheet->setCellValue('G'.$excel_row, __("当期利益(予測)\n※百万円単位"));
                        $sheet->getRowDimension($excel_row)->setRowHeight(35);
                        $sheet->getStyle('G'.$excel_row)->getFont()->setSize(10);
                        $sheet->getStyle('G'.$excel_row)->applyFromArray($alignleft);
                        $sheet->getStyle('G'.$excel_row)->applyFromArray($border_dash);
                                    
                        $sheet->setCellValue('H'.$excel_row, $d_month1);
                        $sheet->getStyle('H'.$excel_row)->applyFromArray($alignright);
                        $sheet->getStyle('H'.$excel_row)->applyFromArray($border_dash);

                        $sheet->setCellValue('I'.$excel_row, $d_month2);
                        $sheet->getStyle('I'.$excel_row)->applyFromArray($alignright);
                        $sheet->getStyle('I'.$excel_row)->applyFromArray($border_dash);

                        $sheet->setCellValue('J'.$excel_row, $d_month3);
                        $sheet->getStyle('J'.$excel_row)->applyFromArray($alignright);
                        $sheet->getStyle('J'.$excel_row)->applyFromArray($border_dash);

                        $sheet->setCellValue('K'.$excel_row, $d_yearly);
                        $sheet->getStyle('K'.$excel_row)->applyFromArray($alignright);
                        $sheet->getStyle('K'.$excel_row)->applyFromArray($border_dash);
                        #End tbody 2th Row

                        #Start TextArea

                        $excel_row++;
                        $sheet->mergeCells('G'.$excel_row.':L'.($excel_row+4));
                        $sheet->setCellValue('G'.$excel_row, $dept_f_remark);
                        $sheet->getStyle('G'.$excel_row)->applyFromArray($alignleft);
                        $sheet->getStyle('G'.$excel_row)->applyFromArray($aligntop);
                        $sheet->getStyle('G'.$excel_row.':L'.($excel_row+4))->applyFromArray($border_dash);
                        #End TextArea
                        #End 3.今後3ヶ月の業績予想と年間見込 Table

                        #Start Attachement Upload
                        $excel_row = $excel_row+6;
                        $sheet->setCellValue('A'.$excel_row, __("4. 添付資料"));
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$excel_row)->getFont()->setBold(true);
                        $sheet->mergeCells('A'.$excel_row.':B'.$excel_row);
                        $excel_row++;

                        if (!empty($attached)) {
                            $dept_p3 = implode(',   ', $attached);
                                   
                            $sheet->setCellValue('A'.$excel_row, $dept_p3);
                        }
                        
                        $sheet->getStyle('A'.$excel_row)->applyFromArray($alignleft);
                        $sheet->getStyle('A'.$excel_row)->applyFromArray($aligntop);
                        $sheet->mergeCells('A'.$excel_row.':H'.($excel_row+2));
                        $sheet->getStyle('A'.$excel_row.':H'.($excel_row+2))->applyFromArray($border_dash);
                        #End Attachement Upload
                    }
                }
                #End Department Loop
                $excel_row += 4;
            } else {
                # If no show data
                
                $sheet->setCellValue('C'.$excel_row, __("計算する為のデータがシステムにありません。"));
                $sheet->mergeCells('C'.$excel_row.':H'.$excel_row);
                $sheet->getStyle('C'.$excel_row.':H'.$excel_row)->applyFromArray($aligncenter);

                $sheet->getStyle('C'.$excel_row.':H'.$excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
                $sheet->getStyle('C'.$excel_row.':H'.$excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
                $objPHPExcel->getActiveSheet()->getStyle('C:H')->getAlignment()->setWrapText(true);
            }
        } else {
            #If cache is clear
            // $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }

        #For red color at negative
        $alpharbat = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
        
        for ($i=2; $i < count($alpharbat); $i++) {
            $column = $alpharbat[$i];
            $lastRow = $sheet->getHighestRow();
            for ($row = 1; $row <= $lastRow; $row++) {
                
                /*$cell = (string)$sheet->getCell($column.$row)->getValue();

                if($cell[0] == '-'){

                    $replace_cell = str_replace('-','▲',$cell);

                    $objPHPExcel->getActiveSheet()->getStyle($column.$row)->applyFromArray($negative);

                    $sheet->setCellValue($column.$row, $replace_cell);

                }*/

                $objPHPExcel->getActiveSheet()
                            ->getStyle($column.$row)
                            ->getNumberFormat()
                            ->setFormatCode(
                                '"" #,##0;[Red]"▲" #,##0'
                            );
            }
        }
        
        if ($save_to_tmp) {
            $PHPExcel->save($file_name);
        } else {
            $PHPExcel->output($file_name.'.xlsx');
        }

        $this->autoLayout = false;
    }

    /**
     * check duplicate upload file in tbl_p3_attachment
     *
     * @author NU NU LWIN (20200317)
     * @throws NotFoundException
     * @param  -
     * @return JSON
     */

    public function checkDuplicateUploadFile()
    {
        // $this->autoRender = false; #We don't render a view in this example
        // $this->request->onlyAllow('ajax'); #No direct access via browser URL
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = ''; #you need to have a no html page, only the data
        
        if ($this->Session->check('TARGETMONTH')) {
            $target_month = $this->Session->read('TARGETMONTH');
        }

        $head_dept_id   = $this->request->data['head_id'];
        $dept_id   = $this->request->data['dept_id'];
        $file_name = $this->request->data['file_name'];

        $search_file_name = $this->BrmMrAttachment->find('all', array(
                        'fields' => array("file_name"),
                        'conditions'=>array(
                                "file_name"		=> $file_name,
                                "dlayer_code"   => $dept_id,
                                "hlayer_code"	=> $head_dept_id,
                                "target_month"	=> $target_month
                        )
                    ));
        if (empty($search_file_name)) {
            $return_data = "no_duplicate";
        } else {
            $return_data = "duplicate";
        }

        return json_encode($return_data);
    }

    /**
     * save upload file to cloud and tbl_p3_attachment
     *
     * @author NU NU LWIN (20200317)
     * @throws NotFoundException
     * @param  -
     * @return void
     */

    public function saveUploadFile()
    {
        // $this->autoRender = false; #We don't render a view in this example
        // $this->request->onlyAllow('ajax'); #No direct access via browser URL
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = ''; #you need to have a no html page, only the data
        $this->tmpMsgDelete();
        $login_id = $this->Session->read('LOGIN_ID'); #get login id
        $date = date('Y-m-d H:i:s');
        
        if ($this->Session->check('HEAD_DEPT_ID')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('HEAD_DEPT_CODE')) {
            $head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('TARGETMONTH')) {
            $target_month = $this->Session->read('TARGETMONTH');
        }
        if ($this->Session->check('SESSION_LAYER_CODE')) {
            $dlayer_code = $this->Session->read('SESSION_LAYER_CODE');
        }
        # Edited by PanEiPhyo(20200331) due to page refresh after file upload
        $data 		= $_FILES;
        $dept_id 	= key($_FILES);

        end($_FILES); # move the internal pointer to the end of the array
        $head_dept_id = key($_FILES); #not choose head_dept_name in term selection
        $file 		= $_FILES[$dept_id];
        $file_name 	= $file['name'];
        $file_path 	= $file['tmp_name'];
        $upload_folder_path = CloudStorageInfo::FOLDER_NAME.'/'."BrmMonthlyReportUpload/".$dept_id.$file_name;
        
        $p3DB = $this->BrmMrAttachment->getDataSource(); #for try catch
        $save_file = array(
                        "hlayer_code" => $head_dept_code,
                        "dlayer_code" => $dept_id,
                        "target_month"=> $target_month,
                        "url"         => $upload_folder_path,
                        "file_name"   => $file_name,
                        "created_by"  => $login_id,
                        "updated_by"  => $login_id
                    );
        
        if (!empty($save_file)) {
            try {
                $p3DB->begin();
                $this->BrmMrAttachment->saveAll($save_file);
                $this->__upload_object_to_cloud($file_name, $file_path, $upload_folder_path);

                # removed message for UX PanEiPhyo(20200401)

                $p3DB->commit();
                Cache::clear();//if upload file, clear cache for re-cache new upload file(wla)
            } catch (Exception $e) {
                $p3DB->rollback();
                
                CakeLog::write('debug', 'Save error at P3Attachment' .$login_id. ' In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                return false;
            }
            return true;
        }
    }

    /**
     * update upload file to cloud and tbl_p3_attachment
     *
     * @author NU NU LWIN (20200317)
     * @throws NotFoundException
     * @param  -
     * @return void
     */

    public function overwirteUploadFile()
    {
        // $this->autoRender = false; #We don't render a view in this example
        // $this->request->onlyAllow('ajax'); #No direct access via browser URL
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = ''; #you need to have a no html page, only the data
        $this->tmpMsgDelete();
        $login_id = $this->Session->read('LOGIN_ID'); #get login id
        $date = date('Y-m-d H:i:s');
        
        if ($this->Session->check('HEAD_DEPT_ID')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('TARGETMONTH')) {
            $target_month = $this->Session->read('TARGETMONTH');
        }
        $date = date('Y-m-d H:i:s');

        $p3DB 	 = $this->BrmMrAttachment->getDataSource(); #for try catch
        #Edited by NuNuLwin(20200401) due to page refresh after file upload
        $data 		= $_FILES;
        $dept_id 	= key($_FILES);
        end($_FILES);	 # move the internal pointer to the end of the array
        $head_dept_id = key($_FILES); #not choose head_dept_name in term selection
        $file 		= $_FILES[$dept_id];
        $file_name 	= $file['name'];
        $file_path 	= $file['tmp_name'];
        $upload_folder_path = CloudStorageInfo::FOLDER_NAME.'/'."BrmMonthlyReportUpload/".$dept_id.$file_name;
        
        $p3_id = $this->BrmMrAttachment->find('first', array(
                    'fields' 	=> array("id"),
                    'conditions'=> array(
                        "file_name"		=> $file_name,
                        "dlayer_code"   => $dept_id,
                        "hlayer_code"	=> $head_dept_id,
                        "target_month"	=> $target_month
                    )
                ));
        
        $p3_id = $p3_id['BrmMrAttachment']['id'];
        
        if (!empty($file)) {
            try {
                $p3DB->begin();
                $updated_date 	  = $p3DB->value($date, 'string');
                $updated_id 	  = $p3DB->value($login_id, 'string');
                $update_file_name = $this->BrmMrAttachment->updateAll(
                    array(
                                    "BrmMrAttachment.updated_by"   => $updated_id,
                                    "BrmMrAttachment.updated_date" => $updated_date
                                    ),
                    array("BrmMrAttachment.id" => $p3_id)
                );
                
                if ($update_file_name) {
                    if ($this->__delete_object_to_cloud($upload_folder_path)) {
                        $this->__upload_object_to_cloud($file_name, $file_path, $upload_folder_path);
                    } else {
                        CakeLog::write('debug', 'file not exist on cloud or file delete error.' .$login_id. ' In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    }
                } else {
                }

                

                $p3DB->commit();
            } catch (Exception $e) {
                $p3DB->rollback();
                
                CakeLog::write('debug', 'file already upload by another user and login_id ' .$login_id. ' In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            }
        } else {
            CakeLog::write('debug', 'upload file not exist or upload file has error ' .$login_id. ' In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
        }
        return true;
    }

    /**
     * delete upload file method
     *
     * @author NU NU LWIN (20200319)
     * @throws NotFoundException
     * @param  -
     * @return void
     */
    public function delete_upload_file()
    {

        # Added by PanEiPhyo(20200331) for file deletion by ajax
        // $this->autoRender = false; #We don't render a view in this example
        // $this->request->onlyAllow('ajax'); #No direct access via browser URL
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = ''; #you need to have a no html page, only the data
        $this->tmpMsgDelete();
        $login_id = $this->Session->read('LOGIN_ID'); #get login id
        $date = date('Y-m-d H:i:s');
        
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }

        if ($this->Session->check('HEAD_DEPT_CODE')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_CODE');
        } else {
            $head_dept_id 	= $this->request->data['HEAD_DEPT_CODE'];
        }

        if ($this->Session->check('TARGETMONTH')) {
            $target_month = $this->Session->read('TARGETMONTH');
        }
        $date = date('Y-m-d H:i:s');

        $dept_id 	= $this->request->data['dept_id'];
        $file_name 	= $this->request->data['file_name'];

        if (!empty($file_name)) {
            $p3DB 	= $this->BrmMrAttachment->getDataSource(); #for try catch
        
            $p3_id  = $this->BrmMrAttachment->find('first', array(
                        'fields' => array("id","url"),
                        'conditions'=>array(
                            "file_name"		=> $file_name,
                            "dlayer_code"   => $dept_id,
                            "hlayer_code"	=> $head_dept_id,
                            "target_month"	=> $target_month
                        )
                    ));
            
            $todelete_id  = $p3_id['BrmMrAttachment']['id'];
            $todelete_url = $p3_id['BrmMrAttachment']['url'];

            if (!empty($todelete_id)) {
                try {
                    $p3DB->begin();

                    if ($this->BrmMrAttachment->exists($todelete_id)) {
                        if ($this->BrmMrAttachment->delete($todelete_id)) {
                            
                            if ($this->__delete_object_to_cloud($todelete_url)) {
                                $p3DB->commit();
                                Cache::clear();
                            }
                            # removed message for UX PanEiPhyo(20200401)
                            $file_data = $this->BrmMrAttachment->find('all', array(
                                'fields' => array("dlayer_code","file_name"),
                                'conditions'=>array(
                                    "dlayer_code"	=> $dept_id,
                                    "hlayer_code"	=> $head_dept_id,
                                    "target_month"	=> $target_month
                                )
                            ));
                            Cache::clear();//(wla)
                            return json_encode($file_data);
                        } else {
                            throw new Exception("Fail to delete ".$file_name.".", 1);
                            $msg = parent::getErrorMsg("SE007");
                            $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                            return json_encode('error');
                        }
                    } else {
                        throw new Exception("id not exist in this file at brm_mr_attachments", 1);
                        $msg = parent::getErrorMsg("SE007");
                        $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                        return json_encode('error');
                    }
                } catch (Exception $e) {
                    $p3DB->rollback();
                
                    CakeLog::write('debug', "Fail to delete ".$file_name."." .$login_id. ' In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE007");
                    $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                    return json_encode('error');
                }
            } else {

                #file is already deleted by another user.
                $msg = parent::getErrorMsg("SE071", $file_name);
                $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                return json_encode('error');
            }
        } else {
            throw new Exception("file name and dept id are empty", 1);
            $msg = parent::getErrorMsg("SE007");
            $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
            return json_encode('error');
        }
    }

    /**
     * Upload file to cloud
     *
     * @author NU NU LWIN (20200318)
     * @throws NotFoundException
     * @param  fileName, filePath, url
     * @return void
     */

    public function __upload_object_to_cloud($objectName, $source, $folderStructure)
    {
        $cloud = parent::connect_to_google_cloud_storage();
        
        $storage = $cloud[0];
        
        $bucketName = $cloud[1];
        
        $file = fopen($source, 'r');

        $bucket = $storage->bucket($bucketName);
        
        try {
            $object = $bucket->upload($file, [
                'name' => $folderStructure
            ]);
        } catch (GoogleException $e) {
            CakeLog::write('debug', 'picture upload error on cloud '.$e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * delete file from cloud
     *
     * @author NU NU LWIN (20200318)
     * @throws NotFoundException
     * @param  url
     * @return void
     */

    public function __delete_object_to_cloud($url)
    {
        $cloud 		= parent::connect_to_google_cloud_storage();
        $storage 	= $cloud[0];
        $bucketName = $cloud[1];
        $bucket 	= $storage->bucket($bucketName);

        try {
            $object = $bucket->object($url);
                
            if ($object->exists()) {
                $object->delete();
                return true;
            }
        } catch (GoogleException $e) {
            return false;
            CakeLog::write('debug', 'picture delete error on cloud =>'.$e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Download an object from Cloud Storage and save it as a local file.
     *
     * @param string $bucketName the name of your Google Cloud bucket.
     * @param string $objectName the name of your Google Cloud object.
     * @param string $destination the local destination to save the encrypted object.
     * @author NU NU LWIN (20200320)
     * @return void
     */
    public function download_file_from_cloud()
    {
        $this->autoRender = false;
    
        if ($this->request->is('post')) {
            $dept_id 	= $this->request->data['hidden_dept_id'];
            $file_name 	= $this->request->data['hidden_file_name'];

            $url = CloudStorageInfo::FOLDER_NAME.'/'."BrmMonthlyReportUpload/".$dept_id.$file_name;

            try {
                $cloud 		= parent::connect_to_google_cloud_storage();
                $storage 	= $cloud[0];
                $bucketName = $cloud[1];
                $bucket 	= $storage->bucket($bucketName);
                $object 	= $bucket->object($url);
                if ($object->exists()) {
                    $stream 	= $object->downloadAsStream();
                    header('Content-disposition: attachment; filename*=UTF-8\'\''.rawurlencode($file_name));
                    echo $stream->getContents();
                } else {
                    $msg = parent::getErrorMsg("SE012");
                    $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                    $this->redirect(array('controller' => 'BrmMonthlyReport', 'action' => 'index'));
                }
                
                exit();
            } catch (GoogleException $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE012");
                $this->Flash->set($msg, array('key'=>'mty_rp_fail'));
                $this->redirect(array('controller' => 'BrmMonthlyReport', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'BrmMonthlyReport', 'action' => 'index'));
        }
    }

    /**
     * Calculate A B C D A' B' C'
     *
     * @author Pan Ei Phyo
     * @throws NotFoundException
     * @param  url
     * @return void
     */
    public function CalculateResults($ba_code, $account_code, $term_id, $start_month, $end_month, $target_month, $last_year_tm)
    {
        #年間 (D)
        $yearly_budget = $this->BrmBudget->getYearlyBudget($ba_code, $term_id, $start_month, $end_month, $account_code);

        #当月予算 (A)
        $tm_budget = $this->BrmBudget->getMonthlyBudget($ba_code, $term_id, $target_month, $account_code);
        
        #当月実績 (B)
        $tm_result = $this->BrmActualResultSummary->getMonthlyResult($ba_code, $target_month, $account_code);

        #予実比 (B-A)
        $tm_ratio = $tm_result - $tm_budget;

        #前年実績 (C) new added(07/05/2020)
        $tm_previous_y_r = $this->BrmActualResultSummary->getMonthlyResult($ba_code, $last_year_tm, $account_code);
        
        #前年同月比	(B-C) new added(07/05/2020)
        $tm_yoy_change = $tm_result - $tm_previous_y_r;

        #累計=>当月予算 (A')
        $total_tm_budget = $this->BrmBudget->getYearlyBudget($ba_code, $term_id, $start_month, $target_month, $account_code);

        #累計=>当月実績 (B')
        $total_tm_result = $this->BrmActualResultSummary->getYearlyResult($ba_code, $start_month, $target_month, $account_code);

        #累計=>予実比 (B'-A')
        $total_ratio = $total_tm_result - $total_tm_budget;

        #進捗率（%）対年間 (B'/D)
        // $achieve_rate = ($total_tm_result == 0 || $yearly_budget == 0) ? 0 : round(($total_tm_result/$yearly_budget)*100);
        $achieve_rate = $this->getRatio($yearly_budget, $total_tm_result);

        #累計=> 前年実績 (C’)
        $last_year_start_month = date("Y-m", strtotime($start_month. "last day of - 1 year"));
        $previous_y_r = $this->BrmActualResultSummary->getYearlyResult($ba_code, $last_year_start_month, $last_year_tm, $account_code);
        
        #累計=> 前年同月比 (B'-C')
        $yoy_change = $total_tm_result - $previous_y_r;

        $data = array(
            'yearly_budget' => round($yearly_budget/1000000),
            'tm_budget' => round($tm_budget/1000000),
            'tm_result' => round($tm_result/1000000),
            'tm_ratio' => round($tm_ratio/1000000),
            'tm_previous_y_r' => round($tm_previous_y_r/1000000),
            'tm_yoy_change' => round($tm_yoy_change/1000000),
            'total_tm_budget' => round($total_tm_budget/1000000),
            'total_tm_result' => round($total_tm_result/1000000),
            'total_ratio' => round($total_ratio/1000000),
            'previous_y_r' => round($previous_y_r/1000000),
            'yoy_change' => round($yoy_change/1000000),
            'achieve_rate' => $achieve_rate
        );

        return $data;
    }
    /**
     * get tbl_total_result_overview comment
     *
     * @author Pan Ei Phyo
     *
     */
    public function getTotalOverviewCmt($target_month, $head_dept_code, $dept_id=0)
    {
        $data = $this->BrmMrOverview->find('list', array(
                'fields' => array('dlayer_code','overview'),
                'conditions' => array(
                    'hlayer_code'	=> $head_dept_code,
                    "target_month"	=> $target_month
                )
                
        ));

        return $data;
    }
    /**
     * get tbl_forecast comment
     *
     * @author Pan Ei Phyo
     *
     */
    public function getForecast($target_month, $head_dept_code, $dept_id=0)
    {
        $result = [];
        $data = $this->BrmForecast->find(
            'all',
            array(
                    'fields'=> array(
                        'dlayer_code',
                        'month1_forecast',
                        'month2_forecast',
                        'month3_forecast',
                        'yearly_forecast',
                        'remark'
                    ),
                    'conditions'=>array(
                        'hlayer_code'	=> $head_dept_code,
                        "target_month"	=> $target_month)
                )
        );

        foreach ($data as $each) {
            $result[$each['BrmForecast']['dlayer_code']] = array(
                'month1_forecast' => $each['BrmForecast']['month1_forecast'], 
                'month2_forecast' => $each['BrmForecast']['month2_forecast'], 
                'month3_forecast' => $each['BrmForecast']['month3_forecast'], 
                'yearly_forecast' => $each['BrmForecast']['yearly_forecast'], 
                'remark' => $each['BrmForecast']['remark']
            );
        }

        return $result;
    }

    /**
     * get tbl_p3_attachment upload file name
     *
     * @author Pan Ei Phyo
     *
     */
    public function getAttachment($target_month, $head_dept_code, $dept_id=0)
    {
        $data = $this->BrmMrAttachment->find(
            "list",
            array(
                    'fields'=> array(
                        'id','file_name','dlayer_code'
                    ),
                    'conditions'=>array(
                        'hlayer_code'	=> $head_dept_code,
                        "target_month"	=> $target_month
                    )
                )
        );

        return $data;
    }

    /**
     * check update data to using cache
     *
     * @author Pan Ei Phyo
     *
     */
    public function CheckUpdate($last_save_time)
    {
        $check_actual = $this->BrmActualResultSummary->find('count', array(
                    'conditions' => array(
                        'updated_date >' => $last_save_time
                    )
                ));
        
        $check_budget = $this->BrmBudget->find('count', array(
                    'conditions' => array(
                        'updated_date >' => $last_save_time
                    )
                ));
        $check_forecast = $this->BrmForecast->find('count', array(
                    'conditions' => array(
                        'updated_date >' => $last_save_time
                    )
                ));
        $check_attached = $this->BrmMrAttachment->find('count', array(
                    'conditions' => array(
                        'updated_date >' => $last_save_time
                    )
                ));
        $check_overview = $this->BrmMrOverview->find('count', array(
                    'conditions' => array(
                        'updated_date >' => $last_save_time
                    )
                ));
    
        if ($check_actual>0 || $check_budget>0 || $check_forecast>0 || $check_attached>0 || $check_overview>0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
    * MailSending method
    *
    * @author PanEiPhyo (20200526)
    * @return void
    */
    public function MailSending()
    {
        if ($this->request->is('post')) {
            $login_user 	= $this->Session->read('LOGIN_USER');
            $term_id 		= $this->Session->read('TERM_ID');
            $target_month 	= $this->Session->read('TARGETMONTH');
            $hq_id		 	= $this->Session->read('HEAD_DEPT_ID');
            $term_name		= $this->Session->read('TERM_NAME');
            $headquarter 	= $this->Session->read('HEAD_DEPT_NAME');

            #Get deadline date
            $submission_date = $this->BrmActualResultSummary->find('first', array(
                        'fields' => 'submission_deadline_date',
                        'conditions' => array(
                            'target_month' => $target_month,
                        )
                    ));
            $sub_date = $submission_date['BrmActualResultSummary']['submission_deadline_date'];
            $day = date("w", strtotime($sub_date));
            $dys = array("日","月","火","水","木","金","土");
            $deadline_date = date("n\月d\日（".$dys[$day]."）", strtotime($sub_date));
            
            $Common  = new CommonController;
            # toEmail
            $level_1 = AdminLevel::ADMIN;
            # bccEmail
            $level_12 = AdminLevel::BUDGET_PRESIDENT;
            $level_13 = AdminLevel::BUDGET_CHIEF_OFFICER;
            $level_9  = AdminLevel::GENERAL_MANAGER;
            $level_8  = AdminLevel::DEPUTY_GENERAL_MANAGER;
            $level_5  = AdminLevel::BUSINESS_MANAGER;
            $level_6  = AdminLevel::BUSINESS_ADMINISTRATIOR;
            $level_7  = AdminLevel::BUSINESS_INCHARGE;
            $level_10 = AdminLevel::BUDGET_INCHARGE;
            $level_2  = AdminLevel::ACCOUNT_MANAGER;
            $level_3  = AdminLevel::ACCOUNT_SECTION_MANAGER;
            $level_4  = AdminLevel::ACCOUNT_INCHARGE;
            $level_14 = AdminLevel::BUDGET_AUDIT;
            $level_15 = AdminLevel::BUDGET_BOARD_MEMBER;

            $level = array($level_9,$level_8,$level_2,$level_5,$level_3,$level_6,$level_10,$level_4);

            # get email address from user table
            $toEmail = $Common->getEmail($level_1);
            $ccEmail = "";
            $bccEmail = $Common->getEmail($level);

            # mail content
            $mail_template 			= 'common';
            $mail['subject']	 	= '【月次業績報告】業績作成完了通知';
            $mail['template_title'] = '各位';
            $mail['template_body'] 	= $target_month.'月度月次業績報告の業績の作成が完了致しました。<br/>下記リンクにアクセス頂き、月次業績報告を作成の上、下記提出期日までにご提出頂きます様、宜しくお願い致します。<br/><br/>提出期日：'.$deadline_date.'営業時間内<br/>';
            
            # url link to access without choosing session
            
            //$url = '/MonthlyReport?target_month='.$target_month.'&hq_id='.$hq_id.'&term_id='.$term_id.'&term_name='.$term_name.'&head_dept_name='.urlencode($headquarter);
            $url = '/BrmMonthlyReport?target_month='.$target_month.'&hq_id='.$hq_id.'&hq_name='.$headquarter.'&term_id='.$term_id.'&term_name='.$term_name;
            # no have email address, show error msg
            if (empty($toEmail)) {
                $errorMsg = parent::getErrorMsg('SE059');
                $this->Flash->set($errorMsg, array('key'=>'mty_rp_fail'));

                $this->redirect(array('action' => 'index'));
            } else { # have email address, send the mail and show success msg
                    
                $sentMail = parent::sendEmailP3($target_month, $login_user, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                
                if ($sentMail['error'] == 1 && $sentMail['errormsg'] != "") { # invalid

                    $errorMsg = parent::getErrorMsg('SE042');
                    $this->Flash->set($errorMsg, array("key"=>"mty_rp_fail"));
                    $this->redirect(array('action' => 'index'));
                } else { # valid

                    $successMsg = parent::getSuccessMsg("SS018");
                    $this->Flash->set($successMsg, array('key'=>'mty_rp_ok'));
                    $this->redirect(array('action' => 'index'));
                }
            }
        }
    }
    /**
     * check permission for beforefilter
     *
     * @author Nu Nu Lwin
     *
     */
    public function getPermissionDept($login_layer_code)
    {   
        $layer_list = [];
        $today_date = date("Y/m/d") ;
        foreach ($login_layer_code as $p_data) {
            $parent_list = implode(",",$p_data);
            $layer_data = $this->Layer->find('all', array(
                'fields' => array(
                    'Layer.name_jp as hlayer','second_layer.name_jp as dlayer','Layer.layer_code as hlayer_code','second_layer.layer_code as dlayer_code'
                ),
                'joins' => array(
                    array(
                        'table' => 'layers',
                        'alias' => 'second_layer',
                        'type'  => 'LEFT',
                        'conditions' => array(
                            'second_layer.flag = 1',
                            'second_layer.to_date >= '=> $today_date,
                            'second_layer.type_order' => Setting::LAYER_SETTING['middleLayer'],
                            'second_layer.layer_code IN ('.$parent_list.')'
                        )
                    ),
                ),
                'conditions' => array(
                    'Layer.flag' => 1,
                    'Layer.to_date >=' => $today_date,
                    'Layer.layer_code' => $p_data['L1']
                    ),
                'group' => array('Layer.layer_code,second_layer.layer_code'),
                'order' => array('Layer.id ASC')
            ));
            array_push($layer_list,$layer_data);
        }
        $layer_list = array_reduce($layer_list, 'array_merge', array());
        return $layer_list;
    }

    /**
     * get headquarter name form tbl_head_department
     *
     * @author Nu Nu Lwin
     * @param $hq_id
     * @return data
     */
    public function getHqName($id)
    {
        $getHqName = $this->HeadDepartmentModel->find('first', array(
            'fields' => array('head_dept_name'),
            'conditions' => array(
                'id' => $id,
                'flag' => 1
            )
        ));

        return $getHqName['HeadDepartmentModel']['head_dept_name'];
    }

    /**
     * getRatio method
     *
     * @author PanEiPhyo (20200709)
     * @param  $yearly_budget,$total_result
     * @return array
     *
     */
    public function getRatio($yearly_budget, $total_result)
    {
        $ratio = 0;

        // if ($yearly_budget == 0) {
        // 	$ratio = '_';
        // } else {
        // 	$ratio = ($yearly_budget >= 0) ? $total_result/$yearly_budget : 1-($yearly_budget-$total_result)/-($yearly_budget);
        // 	$ratio = round($ratio*100).'%';
        // }

        # Change calculation by PanEiPhyo (20200728)
        if ($yearly_budget == 0) {
            $ratio = '－';
        } else {
            $ratio = (($yearly_budget > 0 && $total_result < 0) || ($total_result > 0 && $yearly_budget < 0)) ? '－' : round(($total_result/$yearly_budget)*100).'%';
        }

        return $ratio;
    }
    /**
     * showErrMsg method
     *
     * @author KhinHninMyo (20200901)
     * @param  $term_id, $target_month,$head_dept_id
     * @return $err_msg(string)
     *
     */
    public function showErrMsg($term_id, $target_month, $head_dept_id)
    {
        $layer = $this->LayerType->find('first', array(
            'conditions' => array('LayerType.type_order'=>$topLayer),
            'fields' => array('LayerType.name_jp'),
        ));
        $param = array();
        $param[] = (!$term_id)? '予算期間' : '';
        $param[] = (!$target_month)? '対象月' : '';
        $param[] = (!$head_dept_id)? $layer['LayerType']['name_jp'] : '';
        $err_msg = implode(', ', array_filter($param));
    
        return $err_msg;
    }

    #data pass from view ajax to controller to get mail data
    public function getMailLists() {
        #only allow ajax request
        $language = $this->Session->read('Config.language');
        parent::checkAjaxRequest($this);
        $Common     = New CommonController();
        $period     = $this->Session->read('TARGETMONTH');
        $layer_name = $this->Session->read('BUDGET_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $page       = $_POST['page'];
        $function   = $_POST['function'];
        
        $mails = $Common->getMailList($layer_code, $page, $function, $language, $layer_name, $period, Setting::LAYER_SETTING['bottomLayer']);
        return json_encode($mails);
    }

    public function tmpMsgDelete() {
        unset($_SESSION['Message']['mty_rp_ok']);
        unset($_SESSION['tmpSuccess']);
        unset($_SESSION['Message']['mty_rp_fail']);
        unset($_SESSION['tmpError']);
    }
}
