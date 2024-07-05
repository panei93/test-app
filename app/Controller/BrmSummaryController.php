<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'BrmForecastBudgetDifference');
// App::import('Vendor', 'php-excel-reader/PHPExcel');
/**
 * BrmSummaryController
 *
 * @property BrmSummary $Summary
 * @property PaginatorComponent $Paginator
 */

define('UPLOAD_FILEPATH', ROOT); //server
define('UPLOAD_PATH', 'app'.DS.'temp');

class BrmSummaryController extends AppController
{

    /**
     * Components
     *
     * @var array
     */
    public $uses = array('BrmSmExplain','BrmBudgetApprove', 'BrmAccount', 'BrmSaccount', 'Layer', 'BrmActualResultSummary', 'BrmBudget', 'BrmExpected', 'BrmBudgetPrime', 'BrmCeoComment', 'BrmInvestment', 'BrmCashFlow', 'BrmManpowerPlan', 'BrmExpectedBudgetDiffJob','User','BrmTerm','RtaxFee','BrmCronLog');
    public $components = array('Paginator','PhpExcel.PhpExcel', 'Session',);

    /**
     * Check Session before render page
     * @author Khin Hnin Myo (20200218)
     * @return void
     */
    public function beforeFilter()
    {
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('HEAD_DEPT_ID')) {
            $hq_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('HEAD_DEPT_CODE')) {
            $hq_code = $this->Session->read('HEAD_DEPT_CODE');
        }
        if ($this->Session->check('LOGIN_ID')) {
            $login_id = $this->Session->read('LOGIN_ID');
        }
        $Common      = New CommonController();
        $layer_code  = $this->Session->read('SESSION_LAYER_CODE');
        $role_id     = $this->Session->read('ADMIN_LEVEL_ID');
        $pagename    = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        $layers = array_keys($permissions['index']['layers']);
        $user_parent_data = array_column($permissions['index']['parent_data'],'L'.SETTING::LAYER_SETTING['topLayer']);
        
        if ($permissions['index']['limit'] == SETTING::LAYER_SETTING['topLayer'] && !in_array($hq_code,$user_parent_data)) {
            $errorMsg = parent::getErrorMsg('SE016', '総括表');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        }
        # error msg for term selection
        if ($term_id == "") {
            $errorMsg = parent::getErrorMsg('SE093');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        }
        if ($hq_code == "") {
            $errorMsg = parent::getErrorMsg('SE073');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        }
    }
    
    /**
     * index method
     *
     * @author Khin Hnin Myo (20200902)
     * @return void
     */
    public function index()
    {
        $this->layout = 'phase_3_menu';

        $term_id        = $this->Session->read('TERM_ID');
        $term_name      = $this->Session->read('TERM_NAME');
        $head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
        $headquarter    = $this->Session->read('HEAD_DEPT_NAME');
        $layer_code     = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name     = $this->Session->read('BUDGET_BA_NAME');
        $login_id       = $this->Session->read('LOGIN_ID');
        $admin_level_id = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_type     = $this->Session->read('LayerTypeData');
        $save_into_tmp = false;
        
        $summary = $this->getSummaryData($term_id, $term_name, $head_dept_code, $headquarter, $login_id, $admin_level_id, $save_into_tmp, $layer_code);

        $table_one_acc = $summary['table_one_acc'];
        $table_one_psc = $summary['table_one_psc'];
        $table_two = $summary['table_two'];
        $table_three_seven = $summary['table_three_seven'];
        $table_eight = $summary['table_eight'];
        $table_nine = $summary['table_nine'];
        $last_year = $summary['last_year'];
        $fc_year = $summary['fc_year'];
        $bg_year_arr = $summary['bg_year_arr'];
        $all_years = $summary['all_years'];
        $term_id = $summary['term_id'];
        $term_name = $summary['term_name'];
        $headquarter = $summary['headquarter'];
        $t_name = $summary['t_name'];
        $term_year = $summary['term_year'];
        $end_subid = $summary['end_subid'];
        $section_data = $summary['section_data'];
        $yr_to_jq = $summary['yr_to_jq'];
        $created_date = $summary['created_date'];
        $create_permit = $summary['create_permit'];
        $approve_permit = $summary['approve_permit'];
        $approve_flag = $summary['approve_flag'];
        $admin_level_id = $summary['admin_level_id'];

        $this->set(compact('table_one_acc', 'table_one_psc', 'table_two', 'table_three_seven', 'table_eight', 'table_nine', 'last_year', 'fc_year', 'bg_year_arr', 'all_years', 'term_id', 'term_name', 'headquarter', 't_name', 'term_year', 'end_subid', 'section_data', 'yr_to_jq', 'created_date', 'create_permit', 'approve_permit', 'approve_flag', 'admin_level_id', 'layer_code', 'layer_name','layer_type'));
        $this->render('index');
    }

    public function getSummaryData($term_id, $term_name, $head_dept_code, $headquarter, $login_id, $admin_level_id, $save_into_tmp, $layer_code=null)
    {
        $Common = new CommonController();
        $active_bas = array_values($this->getActiveBA($head_dept_code));
        $permission = $_SESSION['PERMISSIONS'];
        $created_date = date("Y/m/d");
        $term = explode('(', $term_name);
        $t_name = explode('~', $term[0]);
        $term_year = range($t_name[0], $t_name[1]);

        $create_permit = 'on';
        $approve_permit = 'on';
        
        $fc_year = $t_name[0];
        $bg_year_arr = range($t_name[0], $t_name[1]);
        array_shift($bg_year_arr);
        
        $last_year = $fc_year-1;
        $all_years = range($last_year, $t_name[1]);
        $approved_bas = $this->BrmBudgetApprove->find('list', array(
            'fields' => 'layer_code',
            'conditions' => array(
                'brm_term_id' => $term_id,
                'hlayer_code' => $head_dept_code,
                'layer_code' => $active_bas,
                'flag' => 2
            )
        ));
        
        $approve_data = $this->BrmBudgetApprove->find('first', array(
            'fields' => 'flag',
            'conditions' => array(
                'brm_term_id' => $term_id,
                'hlayer_code' => $head_dept_code,
                'dlayer_code' => 0,
                'layer_code'  => 0,
            )
        ));
        $approve_flag = (!empty($approve_data)) ? $approve_data['BrmBudgetApprove']['flag'] : '1';
        
        # create and approve permission
        if (!$save_into_tmp) {
            $rlimit = $permission['index']['limit'];
            $readLimit = $Common->checkLimit($rlimit, $active_bas[0], $login_id,$permission);
            if ($readLimit == false) {
                $errorMsg = parent::getErrorMsg('SE016', '総括表');
                $this->Flash->set($errorMsg, array("key"=>"TermError"));
                $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
            }

            $slimit   = $permission['save']['limit'];
            $alimit   = $permission['approve']['limit'];

            $createLimit  = $Common->checkLimit($slimit, $active_bas[0], $login_id, $permission);
            $ApproveLimit = $Common->checkLimit($alimit, $active_bas[0], $login_id, $permission);
            
            $create_permit = ($createLimit == 'true') ? 'on' : 'off';
            $approve_permit = ($ApproveLimit == 'true') ? 'on' : 'off';
        }

        #for $result_data
        $last_year_start = $Common->getMonth($last_year, $term_id, 'start');
        $last_year_end   = $Common->getMonth($last_year, $term_id, 'end');
        #for $budget_data and $expected_data
        $target_year = date("Y", strtotime($t_name[0])); #term year start
        $start_month = $Common->getMonth($target_year, $term_id, 'start');
        $end_month   = $Common->getMonth($target_year, $term_id, 'end');
        
        #Table One
        $table_one_acc = $this->getTableOneAccount($term_id, $head_dept_code, $last_year, $fc_year, $bg_year_arr);
        $table_one_psc = $this->getTableOnePersonCount($term_id, $head_dept_code, $fc_year, $bg_year_arr);
        
        #Table Two
        $table_two = array();
        $pl_explain = $this->BrmSmExplain->find('list', array(
            'fields' => array('target_year','comment'),
            'conditions' => array(
                'BrmSmExplain.flag' => 1,
                'brm_term_id'       => $term_id,
                'hlayer_code'       => $head_dept_code,
            )
        ));
        
        foreach ($term_year as $year) {
            $table_two[$year] = (!empty($pl_explain[$year])) ? $pl_explain[$year] : '';
        }

        #last sub acc id in summary_data array
        $end_subid = (end($summary_data))['sub_acc_id'];

        # prepare to show 3rd, 4th, 5th, 6th, 7th table
        $comments = $this->BrmCeoComment->find('first', array(
            'fields' => array('goal','business_outlook','plan_for_ext_busi','plan_for_new_busi','priority_n_consider'),
            'conditions' => array(
                'brm_term_id' => $term_id,
                'hlayer_code' => $head_dept_code,
            )
        ));
        #Table Three to Seven
        $table_three_seven = array(
            '3. 本部目標・成長戦略（マテリアリティ視点含む）' => $comments['BrmCeoComment']['goal'],
            '4. 市場動向・当社への影響（事業展望）、競合他社状況' => $comments['BrmCeoComment']['business_outlook'],
            '5. 既存事業についてのアクションプラン' => $comments['BrmCeoComment']['plan_for_ext_busi'],
            '6. 新規事業についてのアクションプラン' => $comments['BrmCeoComment']['plan_for_new_busi'],
            '7. 重点施策・その他留意事項' => $comments['BrmCeoComment']['priority_n_consider'],
        );
        
        #Table Eight
        $table_eight = array();
        foreach ($term_year as $year) {
            $eight_data = $this->BrmInvestment->find('all', array(
                'fields' => array('section_name','detail','purchase_date','lease_period','amount'),
                'conditions' => array(
                    'brm_term_id' => $term_id,
                    'hlayer_code' => $head_dept_code,
                    'target_year' => $year
                )
            ));
            
            if (!empty($eight_data)) {
                foreach ($eight_data as $data) {
                    $section_name = $data['BrmInvestment']['section_name'];
                    $table_eight[$year]['data'][] = $data['BrmInvestment'];
                    if ($section_name == "有形固定資産" || $section_name == "無形固定資産") {
                        $table_eight[$year]['total'] += $data['BrmInvestment']['amount'];
                    }
                }
            } else {
                $tmp['section_name'] = '';
                $tmp['detail'] = '';
                $tmp['purchase_date'] = '';
                $tmp['lease_period'] = '';
                $tmp['amount'] = 0;

                $table_eight[$year]['data'][] = $tmp;
                $table_eight[$year]['total'] += 0;
            }
        }
        
        #table_eight' s section select box
        $section_data = array(
            '1' => "リース",
            '2' => "有形固定資産",
            '3' => "無形固定資産",
            '4' => "一括償却",
        );
        #Table Nine
        $table_nine = $this->getTableNine($term_id, $head_dept_code, $last_year, $fc_year, $all_years, $table_eight, $table_one_acc);
        $yr_to_jq   = implode(',', $all_years);
        
        #head_dept_id 2 of admin_level_id 16
        $user_id = $login_id;
        $today_date = date("Y/m/d") ;
        #head_dept_id 2 of admin_level_id 16
        if ($permission['approve']) {
            $approve_permit = 'on';
        }

        # prepare for excel
        $disabled = $create_permit.'_'.$approve_flag;
        
        $cache_data = array(
            'disabled' => $disabled,
            'table_one_acc' => $table_one_acc,
            'table_one_psc' => $table_one_psc,
            'table_two' => $table_two,
            'table_three_seven' => $table_three_seven,
            'table_eight' => $table_eight,
            'table_nine' => $table_nine,
            'last_year' => $last_year,
            'fc_year' => $fc_year,
            'bg_year_arr' => $bg_year_arr,
            'all_years' => $all_years,
            'term_id' => $term_id,
            'term_name' => $term_name,
            'headquarter' => $headquarter,
            't_name' => $t_name,
            'term_year' => $term_year,
            'end_subid' => $end_subid,
            'section_data' => $section_data,
            'yr_to_jq' => $yr_to_jq,
            'created_date' => $created_date,
            'create_permit' => $create_permit,
            'approve_permit' => $approve_permit,
            'approve_flag' => $approve_flag,
            'admin_level_id' => $admin_level_id
        );

        $cache_name = 'brm_summary_'.$term_id.'_'.$head_dept_code;
        Cache::write($cache_name, $cache_data);
        return $cache_data;
    }
    
    /**
     * SaveSummary method
     *
     * @author Khin Hnin Myo ()
     * @return void
     */
    public function SaveSummary()
    {
        $this->layout = 'phase_3_menu';
        $Common = new CommonController();
        if ($this->request->is('post')) {
            $loginId = $this->Session->read('LOGIN_ID');
            $term_id = $this->Session->read('TERM_ID');
            $term_name = $this->Session->read('TERM_NAME');
            $head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
            $headquarter = $this->Session->read('HEAD_DEPT_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $reqData = h($this->request->data);
            #import excel file (20210114) by KhinHninMyo
            #get name, type, tmp_name, error, size of file
            $file = $this->request->params['form']['summ_file_upload'];
            if ($file['name'] != '' && $file['type'] != '') {
                #file import
                $common_save = $this->ImportExcel($file, $loginId, $term_id, $head_dept_code, $layer_code, $reqData, $term_name, $headquarter);
            } else {
                #normal save
                $common_save = $this->CommonSave($loginId, $term_id, $head_dept_code, $layer_code, $reqData);
            }
        
            if (isset($common_save['success']) || $common_save['success']!= '') {
                $this->Flash->set($common_save['success'], array("key"=>"SummarySuccess"));
            } elseif (isset($common_save['error']) || $common_save['error']!= '') {
                $this->Flash->set($common_save['error'], array("key"=>"SummaryError"));
            }

            $this->redirect(array(
                'controller' => 'BrmSummary',
                'action' => 'index'
            ));
        } else {
            $this->redirect(array(
                'controller' => 'BrmSummary',
                'action' => 'index'
            ));
        }
    }

    /**
     * Approve method
     *
     * @author PanEiPhyo (20201120)
     * @return void
     */
    public function ApproveAndCancelSummary()
    {
        $Common = new CommonController;
        $BudgetSave = new BrmForecastBudgetDifferenceController();
        $term_id = $this->Session->read('TERM_ID');
        $head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
        $login_id = $this->Session->read('LOGIN_ID');
        $target_month 	= $this->Session->read('TARGETMONTH');
        $login_user 	= $this->Session->read('LOGIN_USER');
        $term_name = $this->Session->read('TERM_NAME');
        $head_name = $this->Session->read('HEAD_DEPT_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');

        $button_type = $this->request->data('button_type');
        $to_email    = $this->request->data('toEmail');
        $cc_email    = $this->request->data('ccEmail');
        $bcc_email   = $this->request->data('bccEmail');
        #budget start and end
        $terms = explode('~', $term_name);
        $start_year = $terms[0];
        $end_year = $terms[1];

        // if ($button_type == 'approve') {
        //     $mail['subject']	 	= '【予算策定】'.$head_name.' 総括表承認完了通知';
        //     $mail['template_title'] = '各位';
        //     $mail['template_body'] 	= $head_name.'の'.$start_year.'年度見込～'
        //                                 .$end_year.'年度予算の総括表が本部長承認されました。
		// 								<br/>
		// 								下記リンクより内容をご確認ください。';
        // } else {
        //     #deadline_date
        //     $dead_date = $this->Session->read('HEADQUARTER_DEADLINE');
                
        //     $day = date("w", strtotime($dead_date));
        //     $dys = array("日","月","火","水","木","金","土");
        //     $deadline_date = date("n\月d\日（".$dys[$day]."曜日）", strtotime($dead_date));

        //     $mail['subject']	 	= '【予算策定】'.$head_name.' 総括表承認キャンセル通知';
        //     $mail['template_title'] = '各位';
        //     $mail['template_body'] 	= $head_name.'の'.$start_year.'年度見込～'
        //                                 .$end_year.'年度予算の総括表は、データを確認した結果、本部長承認がキャンセルされました。
		// 								<br/>
		// 								下記提出期日までに再度、承認予定です。<br/><br/>
		// 								提出期日：'.$deadline_date.'営業時間内';
        // }
        $mail_template 			= 'common';
        $mail['subject'] 		= $this->request->data('mailSubj');
        $mail['template_title'] = '';
        $mail['template_body'] 	= $this->request->data('mailBody');
        $toEmail = parent::formatMailInput($to_email);
        $ccEmail = parent::formatMailInput($cc_email);
        $bccEmail = parent::formatMailInput($bcc_email);
        $flag    = false;
        #url
        $url = '/BrmSummary?term_id='.$term_id.'&hq_id='.$head_dept_code;           
        #mail send or not
        $mail_send = $this->request->data('mailSend');       
        
        if($mail_send != 0){
            if (!empty($toEmail) || !empty($ccEmail) || !empty($bccEmail)) {    
                $mail = parent::sendEmailP3($target_month, $login_user, $toEmail, $ccEmail,$bccEmail, $mail_template, $mail, $url);
                if ($mail["error"]) {
                    $errorMsg = parent::getErrorMsg('SE042');
                    $this->Flash->set($errorMsg, array("key"=>"SummaryError"));
                    $this->redirect(array('action' => 'index'));
                }
                $flag = true;
            } else {
                $errorMsg = parent::getErrorMsg('SE097');
                $this->Flash->set($errorMsg, array("key"=>"SummaryError"));
            }
        }
        # save process when approve
        $reqData = h($this->request->data);
        // $common_save = ($button_type == 'approve') ? $this->CommonSave($login_id,$term_id,$head_dept_code,$ba_code,$reqData) : 'success';
        if ($button_type == 'approve') {
            $common_save = $this->CommonSave($login_id, $term_id, $head_dept_code, $layer_code, $reqData);
            $page = 'BrmSummary';
            $budget_save = [];
            foreach ($relatedBAs as $key => $ba) {
                $baname = array_values($this->Layer->find('list', array(
                    'fields' => array('ba_name_jp'),
                    'conditions' => array(
                        'layer_code' => $ba,
                        'flag' => 1
                    )
                )))[0];
                $retMsg = $BudgetSave->saveBudgetSummaryData($term_id, $term_name, $login_id, $ba, $baname, $head_dept_code, $page);
                array_push($budget_save, $retMsg);
                if ($retMsg == "success") {
                    $flag = '2';
                    $this->saveBudgetLogData($term_id, $head_dept_code, $ba, $flag, $login_id);
                }
            }
        } else {
            $common_save = 'success';
        }
        
        $approve_data = $this->BrmBudgetApprove->find('first', array(
            'fields' => array('id', 'flag','created_by','created_date'),
            'conditions' => array('hlayer_code' => $head_dept_code,
                'brm_term_id'   => $term_id,
                'dlayer_code'	=> 0,
                'layer_code'    => 0
            )
        ));

        # prepare for save data
        $save_data['brm_term_id'] = $term_id;
        $save_data['hlayer_code'] = $head_dept_code;
        $save_data['dlayer_code'] = 0;
        $save_data['layer_code'] = 0;
        $save_data['flag'] = ($button_type == 'approve') ? '2' : '1';
        $save_data['created_by'] = $login_id;
        $save_data['updated_by'] = $login_id;
        $save_data['created_date'] = date("Y-m-d H:i:s");
        $save_data['updated_date'] = date("Y-m-d H:i:s");
        if (!empty($approve_data)) {
            $save_data['id'] = $approve_data['BrmBudgetApprove']['id'];
            $save_data['created_by'] = $approve_data['BrmBudgetApprove']['created_by'];

            $created_date = $approve_data['BrmBudgetApprove']['created_date'];
            
            if ($created_date != '0000-00-00 00:00:00' && !empty($created_date) && $created_date != null) {
                $save_data['created_date'] =$created_date;
            }
        }
        
        if ((!empty($common_save['success'] || $common_save == 'success')) && (!in_array('error', $budget_save))) {
            try {
                $this->BrmBudgetApprove->begin();
                $this->BrmBudgetApprove->saveAll($save_data);
                $this->BrmBudgetApprove->commit();

                #save the log when approved to check to run the cron
                $cronlog = [];
                $cronlog['brm_term_id'] = $term_id;
                $cronlog['hlayer_code'] = $head_dept_code;
                $cronlog['layer_code'] = 0;
                $cronlog['flag'] = 1;
                $cronlog['created_by'] = $login_id;
                $cronlog['updated_by'] = $login_id;
                $cronlog['created_date'] = date("Y-m-d H:i:s");
                $cronlog['updated_date'] = date("Y-m-d H:i:s");

                $this->BrmCronLog->begin();
                $this->BrmCronLog->saveAll($cronlog);
                $this->BrmCronLog->commit();

                $msgcode = ($button_type == 'approve') ? 'SS022' : 'SS023';
                $sucMsg1 = parent::getSuccessMsg($msgcode, $head_name); #approve msg
                $this->Flash->set($sucMsg1, array("key"=>"SummarySuccess"));
                if ($flag) {
                    $sucMsg2 = parent::getSuccessMsg("SS018"); #email msg
                    $this->Flash->set($sucMsg2, array("key"=>"SummarySuccess"));
                }
            } catch (Exception $e) {
                CakeLog::write('debug', ' Data cannot be saved. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $errorMsg = parent::getErrorMsg('SE003');
                $this->Flash->set($errorMsg, array("key"=>"SummaryError"));
            }
        } else {
            CakeLog::write('debug', ' Data cannot be saved. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $errorMsg = parent::getErrorMsg('SE003');
            $this->Flash->set($errorMsg, array("key"=>"SummaryError"));
        }
        $this->redirect(array('controller' => 'BrmSummary', 'action' => 'index'));    
        $this->redirect(array('controller' => 'BrmSummary', 'action' => 'index'));
    }

    public function BudgetLogCancel($term_id, $head_dept_code, $button_type, $login_id)
    {
        $save_datas = array();

        $approve_data = $this->BrmBudgetApprove->find('all', array(
            'fields' => array('id', 'dlayer_code', 'layer_code', 'flag','created_by','created_date'),
            'conditions' => array('hlayer_code' => $head_dept_code,
                'brm_term_id'		 => $term_id,
            )
        ));
        
        foreach ($approve_data as $keys => $values) {
            $save_data['id'] = $values['BrmBudgetApprove']['id'];
            $save_data['brm_term_id'] = $term_id;
            $save_data['dlayer_code'] = $values['BrmBudgetApprove']['dlayer_code'];
            $save_data['hlayer_code'] = $head_dept_code;
            $save_data['layer_code'] = $values['BrmBudgetApprove']['layer_code'];
            $save_data['flag'] = '1';
            $save_data['created_by'] = $values['BrmBudgetApprove']['created_by'];
            $created_date = $values['BrmBudgetApprove']['created_date'];
            
            if ($created_date != '0000-00-00 00:00:00' && !empty($created_date) && $created_date != null) {
                $save_data['created_date'] =$created_date;
            }
            $save_datas[] = $save_data;
        }
        
        if (!empty($save_datas)) {
            try {
                $this->BrmBudgetApprove->begin();

                $this->BrmBudgetApprove->deleteAll(array(
                    'brm_term_id' => $term_id,
                    'hlayer_code' => $head_dept_code,
                ), false);
                
                $this->BrmBudgetApprove->saveAll($save_datas);
                $this->BrmBudgetApprove->commit();

                $returnMsg['success'] = parent::getSuccessMsg('SS001');
            } catch (Exception $e) {
                $this->BrmBudgetApprove->rollback();
                
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $returnMsg['error'] = parent::getErrorMsg('SE003');
            }
        } else {
            $returnMsg['error'] = parent::getErrorMsg('SE017', 'Save');
        }
        
        return $returnMsg;
    }
    /**
     * CommonSave method
     *
     * @author Khin Hnin Myo (20201223)
     * @param  $loginId,$term_id,$head_dept_code,$ba_code,$reqData
     * @return $returnMsg
     */
    public function CommonSave($loginId, $term_id, $head_dept_code, $layer_code, $reqData)
    {
        $target_month 	= $this->Session->read('TARGETMONTH');
        $login_user 	= $this->Session->read('LOGIN_USER');

        $save_data = array();

        $pl_explain = $reqData['year_pl_cmt'];
        $investment = $reqData['investment'];
        $cashflow   = $reqData['cashflow'];
        $to_email   = $reqData['toEmail'];
        $cc_email   = $reqData['ccEmail'];
        $bcc_email  = $reqData['bccEmail'];
        $mail_template          = 'common';
        $mail['subject'] 		= $reqData['mailSubj'];
        $mail['template_title'] = '';
        $mail['template_body'] 	= $reqData['mailBody'];
        $date = date("Y-m-d H:i:s");
        
        foreach ($pl_explain as $year => $year_pl_cmt) {
            $tmp = array();
            $tmp['brm_term_id'] = $term_id;
            $tmp['hlayer_code'] = $head_dept_code;
            $tmp['target_year'] = $year;
            $tmp['comment'] = $year_pl_cmt;
            $tmp['flag'] = 1;
            $tmp['created_by'] = $loginId;
            $tmp['updated_by'] = $loginId;
            $tmp['created_date'] = $date;
            $tmp['updated_date'] = $date;
            $save_data['BrmSmExplain'][] = $tmp;
        }

        $save_data['BrmCeoComment'] = array(
            'brm_term_id' 	=> $term_id,
            'hlayer_code' 	=> $head_dept_code,
            'goal' 			=> $reqData['comment3'],
            'business_outlook' 	=> $reqData['comment4'],
            'plan_for_ext_busi' => $reqData['comment5'],
            'plan_for_new_busi' => $reqData['comment6'],
            'priority_n_consider' => $reqData['comment7'],
            'created_by' => $loginId,
            'updated_by' => $loginId,
            'created_date' => $date,
            'updated_date' => $date
        );
        
        foreach ($investment as $year => $investment_data) {
            foreach ($investment_data as $inv_value) {
                $tmp = array();
                $purchase_date = ($inv_value['purchase_date'] == "" || $inv_value['purchase_date'] == $year.'-00')? "0000-00" : (date("Y-m", strtotime($inv_value['purchase_date'])));
                $section_name = ($inv_value['section_name'] == "")? "" : $inv_value['section_name'];
                $detail = ($inv_value['detail'] == "")? "" : $inv_value['detail'];
                $lease_period = ($inv_value['lease_period'] == "")? "" : $inv_value['lease_period'];
                $tmp['brm_term_id'] = $term_id;
                $tmp['hlayer_code'] = $head_dept_code;
                $tmp['target_year'] = $year;
                $tmp['section_name'] = $section_name;
                $tmp['detail'] = $detail;
                $tmp['purchase_date'] = $purchase_date;
                $tmp['lease_period'] = $lease_period;
                $tmp['amount'] = (preg_replace("/[^-0-9\.]/", "", $inv_value['hid_amt'])*1000);
                $tmp['flag'] = 1;
                $tmp['created_by'] = $loginId;
                $tmp['updated_by'] = $loginId;
                $tmp['created_date'] = $date;
                $tmp['updated_date'] = $date;
                $save_data['BrmInvestment'][] = $tmp;
            }
        }
        
        foreach ($cashflow as $year => $cashvalue) {
            foreach ($cashvalue as $flow_name => $flowvalue) {
                foreach ($flowvalue as $sub_flow => $amount) {
                    $tmp = array();
                    $amount = ($amount == '')? '0' : $amount;
                    $tmp['brm_term_id'] = $term_id;
                    $tmp['hlayer_code'] = $head_dept_code;
                    $tmp['target_year'] = $year;
                    $tmp['flow_name'] = $flow_name;
                    $tmp['sub_flow'] = $sub_flow;
                    $tmp['amount'] = (preg_replace("/[^-0-9\.]/", "", $amount)*1000);
                    $tmp['flag'] = 1;
                    $tmp['created_by'] = $loginId;
                    $tmp['updated_by'] = $loginId;
                    $tmp['created_date'] = $date;
                    $tmp['updated_date'] = $date;
                    $save_data['BrmCashFlow'][] = $tmp;
                }
            }
        }
        
        if (!empty($save_data)) {
            $CommentDB 		= $this->BrmCeoComment->getDataSource();
            $InvestmentDB 	= $this->BrmInvestment->getDataSource();
            $CashDB 		= $this->BrmCashFlow->getDataSource();
            $PlExplainDB 	= $this->BrmSmExplain->getDataSource();

            try {
                $CommentDB->begin();
                $InvestmentDB->begin();
                $CashDB->begin();
                $PlExplainDB->begin();

                $toEmail = parent::formatMailInput($to_email);
                $ccEmail = parent::formatMailInput($cc_email);
                $bccEmail = parent::formatMailInput($bcc_email);
                #url
                $url = '/BrmSummary?term_id='.$term_id.'&hq_id='.$head_dept_code;           
                #mail send or not
                $mail_send = $this->request->data('mailSend');       
                $flag = false;
                if($mail_send != 0){
                    if (!empty($toEmail) || !empty($ccEmail) || !empty($bccEmail)) {    
                        $mail = parent::sendEmailP3($target_month, $login_user, $toEmail, $ccEmail,$bccEmail, $mail_template, $mail, $url);
                        if ($mail["error"]) {
                            $errorMsg = parent::getErrorMsg('SE042');
                            $this->Flash->set($errorMsg, array("key"=>"SummaryError"));
                            $this->redirect(array('action' => 'index'));
                        }
                        $flag = true;
                    } else {
                        $errorMsg = parent::getErrorMsg('SE097');
                        $this->Flash->set($errorMsg, array("key"=>"SummaryError"));
                    }
                }
                
                # Save all tables by looping
                foreach ($save_data as $modelName => $tableValue) {
                    $this->$modelName->deleteAll(array(
                        'brm_term_id' => $term_id,
                        'hlayer_code' => $head_dept_code,
                    ), false);
                    $result = $this->$modelName->saveAll($tableValue);
                }

                $CommentDB->commit();
                $InvestmentDB->commit();
                $CashDB->commit();
                $PlExplainDB->commit();
                
                $success_message             = parent::getSuccessMsg('SS001');
                if ($flag)  $success_message = $success_message.'<br>'.parent::getSuccessMsg("SS018"); #email msg
                $returnMsg['success']        = $success_message;
            } catch (Exception $e) {
                $CommentDB->rollback();
                $InvestmentDB->rollback();
                $CashDB->rollback();
                $PlExplainDB->rollback();

                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $returnMsg['error'] = parent::getErrorMsg('SE003');
            }
        } else {
            $returnMsg['error'] = parent::getErrorMsg('SE017', 'Save');
        }  
        return $returnMsg;
    }

    /**
     * ExcelSummary method
     *
     * @author Khin Hnin Myo ()
     * @return void
     */
    public function ExcelSummary()
    {
        $term_id = $this->Session->read('TERM_ID');
        $term_name = $this->Session->read('TERM_NAME');
        $headquarter = $this->Session->read('HEAD_DEPT_NAME');
        $admin_level_id = $this->Session->read('ADMIN_LEVEL_ID');
        $hq_id = $this->Session->read('HEAD_DEPT_CODE');
        $file_name = 'BrmSummaryTable';

        $PHPExcel = $this->PhpExcel;
        $this->DownloadExcel($term_id, $term_name, $headquarter, $admin_level_id, $hq_id, $file_name, $PHPExcel);
        $this->render('index');
    }

    public function DownloadExcel($term_id, $term_name, $headquarter, $admin_level_id, $hq_id, $file_name, $PHPExcel, $save_to_tmp=false)
    {
        // $layer_type = $this->Session->read('LayerTypeData');
        $layer_type = $_SESSION['LayerTypeData'];
        $term = explode('(', $term_name);
        $t_name = explode('~', $term[0]);
        $term_year = range($t_name[0], $t_name[1]);
        $layer_code = (!$save_to_tmp) ? $this->request->data('layer_code') : '';
        $cache_name = 'brm_summary_'.$term_id.'_'.$hq_id;
        $cache_data = Cache::read($cache_name);
        
        $summary_data = $cache_data['table_one_acc'];
        $summary_data_psc = $cache_data['table_one_psc'];
        $pl_comment = $cache_data['table_two'];
        $txtarea_cmt = $cache_data['table_three_seven'];
        $table_eight = $cache_data['table_eight'];
        $table_nine = $cache_data['table_nine'];

        $disabled = $cache_data['disabled'];
        $disable = explode('_', $disabled);

        $fc_year = $t_name[0];
        $last_year = $fc_year-1;
        $all_years = range($last_year, $t_name[1]);
        $disable_color = ($disable[0] == 'off' || $disable[1] == 2 || $layer_code != '') ? 'F9F9F9' : 'D5F4FF';
    
        $objPHPExcel = $PHPExcel->createWorksheet()->setDefaultFont('Cambria', 12);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel ->getActiveSheet()->setShowGridlines(true);
        $objPHPExcel ->setActiveSheetIndex(0);
        $mainHeader = array();
        foreach ($term_year as $tkey => $tyear) {
            if ($tkey == 0) {
                $head = $tyear." ".__("見込");
            } else {
                $head = $tyear." ".__("予算");
            }
            array_push($mainHeader, $head);
        }
        
        $objPHPExcel->getActiveSheet()->setTitle('Summary Table');
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
        $sheet->getStyle('B:J')->getAlignment()->setWrapText(true);
        $sheet->getStyle('B2:J2')->applyFromArray($title);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(10);
        
        $sheet->setCellValue('B4', __("期間"));
        $sheet->getStyle('B4')->applyFromArray($border_thin);
        $sheet->getStyle('B4')->applyFromArray($alignleft);
        $sheet->mergeCells('B4:B4');

        $sheet->setCellValue('B5', __($layer_type[SETTING::LAYER_SETTING['topLayer']]));
        $sheet->getStyle('B5')->applyFromArray($border_thin);
        $sheet->getStyle('B5')->applyFromArray($alignleft);
        $sheet->mergeCells('B5:B5');

        $sheet->setCellValue('B6', __($layer_type[SETTING::LAYER_SETTING['bottomLayer']]));
        $sheet->getStyle('B6')->applyFromArray($border_thin);
        $sheet->getStyle('B6')->applyFromArray($alignleft);
        $sheet->mergeCells('B6:B6');

        $sheet->setCellValue('B7', __("提出日"));
        $sheet->getStyle('B7')->applyFromArray($border_thin);
        $sheet->getStyle('B7')->applyFromArray($alignleft);
        $sheet->mergeCells('B7:B7');

        $sheet->setCellValue('C4', $term_name);
        $sheet->getStyle('C4:D4')->applyFromArray($border_thin);
        $sheet->getStyle('C4:D4')->applyFromArray($alignleft);
        $sheet->mergeCells('C4:D4');

        $sheet->setCellValue('C5', $headquarter);
        $sheet->getStyle('C5:D5')->applyFromArray($border_thin);
        $sheet->getStyle('C5:D5')->applyFromArray($alignleft);
        $sheet->mergeCells('C5:D5');

        $sheet->setCellValue('C6', $layer_code);
        $sheet->getStyle('C6:D6')->applyFromArray($border_thin);
        $sheet->getStyle('C6:D6')->applyFromArray($alignleft);
        $sheet->mergeCells('C6:D6');

        $sheet->setCellValue('C7', date("Y/m/d"));
        $sheet->getStyle('C7:D7')->applyFromArray($border_thin);
        $sheet->getStyle('C7:D7')->applyFromArray($alignleft);
        $sheet->mergeCells('C7:D7');

        $tbl2_header = "1．".__("単体Ｐ／Ｌ（収益はﾌﾟﾗｽ表示、費用はﾏｲﾅｽ表示にて記載。）");
        $sheet->setCellValue('B8', $tbl2_header);
        $sheet->mergeCells('B8:J8');
        $sheet->getStyle('B8:J8')->applyFromArray($alignleft);
        $sheet->getStyle('B8:J8')->applyFromArray($title);

        $sheet->getStyle('B9:F9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->getStyle('B10:F10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->getStyle('B11:F11')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');

        $sheet->setCellValue('B9', "");
        $sheet->getStyle('B9:B11')->applyFromArray($border_thin);
        $sheet->mergeCells('B9:B11');

        $sheet->setCellValue('C9', ($t_name[0]-1).__("年度"));
        $sheet->getStyle('C9')->applyFromArray($border_thin);
        $sheet->getStyle('C9')->applyFromArray($aligncenter);
        $sheet->mergeCells('C9:C9');
        $sheet->setCellValue('C10', __("実績"));
        $sheet->getStyle('C10:C11')->applyFromArray($border_thin);
        $sheet->getStyle('C10:C11')->applyFromArray($aligncenter);
        $sheet->mergeCells('C10:C11');

        $sheet->setCellValue('D9', $t_name[0].__("年度"));
        $sheet->getStyle('D9:E9')->applyFromArray($border_thin);
        $sheet->getStyle('D9:E9')->applyFromArray($aligncenter);
        $sheet->mergeCells('D9:E9');

        $sheet->setCellValue('D10', __("予算"));
        $sheet->getStyle('D10:D11')->applyFromArray($border_thin);
        $sheet->getStyle('D10:D11')->applyFromArray($aligncenter);
        $sheet->mergeCells('D10:D11');

        $sheet->setCellValue('E10', __("見込"));
        $sheet->getStyle('E10:E11')->applyFromArray($border_thin);
        $sheet->getStyle('E10:E11')->applyFromArray($aligncenter);
        $sheet->mergeCells('E10:E11');

        $sheet->setCellValue('F9', __("見込対予算比"));
        $sheet->getStyle('F9:F11')->applyFromArray($border_thin);
        $sheet->getStyle('F9:F11')->applyFromArray($aligncenter);
        $sheet->mergeCells('F9:F11');
        
        # forecast
        $a=11;
        foreach ($summary_data['forecast'] as $sub_acc_name=>$value) {
            $a++;
            if ($value['type'] == 1) {
                $sheet->getStyle('B'.$a.':F'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
            }
            $sheet->setCellValue('B'.$a, $sub_acc_name);
            $sheet->getStyle('B'.$a)->applyFromArray($border_thin);
            $sheet->getStyle('B'.$a)->applyFromArray($alignleft);
            
            $result_amts = ($value['result_amt'] == '-0') ? 0 : $value['result_amt'];
            $budget_amts = ($value['budget_amt'] == '-0') ? 0 : $value['budget_amt'];
            $expected_amts = ($value['expected_amt'] == '-0') ? 0 : $value['expected_amt'];
            $expected_budget_diffs = ($value['expected_budget_diff'] == '-0') ? 0 : $value['expected_budget_diff'];
            $sheet->setCellValue('C'.$a, $result_amts);
            $sheet->getStyle('C'.$a)->applyFromArray($border_thin);
            $sheet->getStyle('C'.$a)->applyFromArray($alignright);

            $sheet->setCellValue('D'.$a, $budget_amts);
            $sheet->getStyle('D'.$a)->applyFromArray($border_thin);
            $sheet->getStyle('D'.$a)->applyFromArray($alignright);

            $sheet->setCellValue('E'.$a, $expected_amts);
            $sheet->getStyle('E'.$a)->applyFromArray($border_thin);
            $sheet->getStyle('E'.$a)->applyFromArray($alignright);

            $sheet->setCellValue('F'.$a, $expected_budget_diffs);
            $sheet->getStyle('F'.$a)->applyFromArray($border_thin);
            $sheet->getStyle('F'.$a)->applyFromArray($alignright);
            $sheet->getStyle('F'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
        }
        # person
        $a += 2;
        $getLine = $a; # get row to set numberformat
        $sheet->setCellValue('B'.$a, __(' 在籍人数（月平均）'));
        $sheet->setCellValue('C'.$a, $summary_data_psc['forecast']['last_result']);
        $sheet->setCellValue('D'.$a, $summary_data_psc['forecast']['budget']);
        $sheet->setCellValue('E'.$a, $summary_data_psc['forecast']['forecast']);
        $sheet->setCellValue('F'.$a, $summary_data_psc['forecast']['diff']);
        $sheet->getStyle('B'.$a.':F'.$a)->applyFromArray($border_thin);
        $sheet->getStyle('B'.$a)->applyFromArray($alignleft);
        $sheet->getStyle('C'.$a.':F'.$a)->applyFromArray($alignright);
        $sheet->getStyle('F'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');

        # budget
        $sheet->getStyle('H9:J9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->getStyle('H10:J10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->getStyle('H11:J11')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        
        $first = 68;
        $sec = 69;
        $third = 70;
        foreach ($summary_data['budget'] as $seckey => $secvalue) {
            $first += 4;
            $sec += 4;
            $third += 4;
            $alpha1 = chr($first);
            $alpha2 = chr($sec);
            $alpha3 = chr($third);
            $sheet->getStyle($alpha1.':'.$alpha3)->getAlignment()->setWrapText(true);
            $sheet->getStyle($alpha1.'9:'.$alpha3.'9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle($alpha1.'10:'.$alpha3.'10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle($alpha1.'11:'.$alpha3.'11')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');

            $sheet->setCellValue($alpha1.'9', $seckey.__("年度"));
            $sheet->getStyle($alpha1.'9:'.$alpha2.'9')->applyFromArray($border_thin);
            $sheet->getStyle($alpha1.'9:'.$alpha2.'9')->applyFromArray($aligncenter);
            $sheet->mergeCells($alpha1.'9:'.$alpha2.'9');

            $sheet->setCellValue($alpha1.'10', __("予算案"));
            $sheet->getStyle($alpha1.'10:'.$alpha2.'10')->applyFromArray($border_thin);
            $sheet->getStyle($alpha1.'10:'.$alpha2.'10')->applyFromArray($aligncenter);
            $sheet->mergeCells($alpha1.'10:'.$alpha2.'10');

            $sheet->setCellValue($alpha1.'11', __("上半期"));
            $sheet->getStyle($alpha1.'11')->applyFromArray($border_thin);
            $sheet->getStyle($alpha1.'11')->applyFromArray($aligncenter);
            $sheet->mergeCells($alpha1.'11:'.$alpha1.'11');

            $sheet->setCellValue($alpha2.'11', __("年間"));
            $sheet->getStyle($alpha2.'11')->applyFromArray($border_thin);
            $sheet->getStyle($alpha2.'11')->applyFromArray($aligncenter);
            $sheet->mergeCells($alpha1.'11:'.$alpha1.'11');

            $sheet->setCellValue($alpha3.'9', __("前年見込比"));
            $sheet->getStyle($alpha3.'9:'.$alpha3.'11')->applyFromArray($border_thin);
            $sheet->getStyle($alpha3.'9:'.$alpha3.'11')->applyFromArray($aligncenter);
            $sheet->mergeCells($alpha3.'9:'.$alpha3.'11');
            $b = 11;
            foreach ($secvalue as $vkey => $vvalue) {
                $b++;
                if ($vvalue['type'] == 1) {
                    $sheet->getStyle($alpha1.$b)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
                    $sheet->getStyle($alpha2.$b)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
                    $sheet->getStyle($alpha3.$b)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
                }
                $half_budgets = ($vvalue['half_budget'] == '-0')? 0 : $vvalue['half_budget'];
                $yearly_budgets = ($vvalue['yearly_budget'] == '-0')? 0 : $vvalue['yearly_budget'];
                $differences = ($vvalue['difference'] == '-0')? 0 : $vvalue['difference'];
                $sheet->setCellValue($alpha1.$b, $half_budgets);
                $sheet->getStyle($alpha1.$b)->applyFromArray($border_thin);
                $sheet->getStyle($alpha1.$b)->applyFromArray($alignright);
                
                $sheet->setCellValue($alpha2.$b, $yearly_budgets);
                $sheet->getStyle($alpha2.$b)->applyFromArray($border_thin);
                $sheet->getStyle($alpha2.$b)->applyFromArray($alignright);
                
                $sheet->setCellValue($alpha3.$b, $differences);
                $sheet->getStyle($alpha3.$b)->applyFromArray($border_thin);
                $sheet->getStyle($alpha3.$b)->applyFromArray($alignright);
                $sheet->getStyle($alpha3.$b)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
            }
        }
        $b += 2;
        $firsts = 68;
        $secs = 69;
        $thirds = 70;
        foreach ($summary_data_psc['budget'] as $year => $pscvalue) {
            $firsts += 4;
            $secs += 4;
            $thirds += 4;
            $alpha1 = chr($firsts);
            $alpha2 = chr($secs);
            $alpha3 = chr($thirds);
            $sheet->setCellValue($alpha1.$b, $pscvalue['first_half']);
            $sheet->getStyle($alpha1.$b)->applyFromArray($border_thin);
            $sheet->getStyle($alpha1.$b)->applyFromArray($alignright);
            
            $sheet->setCellValue($alpha2.$b, $pscvalue['total']);
            $sheet->getStyle($alpha2.$b)->applyFromArray($border_thin);
            $sheet->getStyle($alpha2.$b)->applyFromArray($alignright);
            
            $sheet->setCellValue($alpha3.$b, $pscvalue['diff']);
            $sheet->getStyle($alpha3.$b)->applyFromArray($border_thin);
            $sheet->getStyle($alpha3.$b)->applyFromArray($alignright);
            $sheet->getStyle($alpha3.$b)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
        }

        $unlocked_arr = [];
        $locked_arr = [];
        # table two
        $tbl_2 = $a + 2;
        $tbl2_header = "2．".__("単体Ｐ／Ｌ増減説明");
        $sheet->setCellValue('B'.$tbl_2, $tbl2_header);
        $sheet->mergeCells('B'.$tbl_2.':J'.$tbl_2);
        $sheet->getStyle('B'.$tbl_2.':J'.$tbl_2)->applyFromArray($alignleft);
        $sheet->getStyle('B'.$tbl_2.':J'.$tbl_2)->applyFromArray($title);
        
        $thcnt = 0;
        $pl_cmt1 = 66;
        $pl_cmt2 = 69;
        $tbl_2++;
        foreach ($pl_comment as $year => $tvalue) {
            $thcnt++;
            if ($thcnt == 1) {
                $thead = $year.__("年度")." ".__("見込の主な内容及び、予算比増減説明");
            } else {
                if ($thcnt == 2) {
                    $pl_cmt1 = $pl_cmt2+1;
                    $pl_cmt2 = $pl_cmt1+4;
                } else {
                    $pl_cmt1 = $pl_cmt2+1;
                    $pl_cmt2 = $pl_cmt1+3;
                }
                $thead = $year.__("年度")." ".__("予算案の主な内容及び、前年見込比増減説明");
            }
            
            $cmt1 = chr($pl_cmt1);
            $cmt2 = chr($pl_cmt2);
            
            $sheet->setCellValue($cmt1.$tbl_2, $thead);
            $sheet->getStyle($cmt1.$tbl_2.':'.$cmt2.$tbl_2)->applyFromArray($border_thin);
            $sheet->mergeCells($cmt1.$tbl_2.':'.$cmt2.$tbl_2);
            $sheet->getStyle($cmt1.$tbl_2.':'.$cmt2.$tbl_2)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle($cmt1.$tbl_2.':'.$cmt2.$tbl_2)->applyFromArray($aligncenter);
            $sheet->getStyle($cmt1.$tbl_2.':'.$cmt2.$tbl_2)->getAlignment()->setWrapText(true);
            for ($b = $tbl_2+1; $b <= $tbl_2+10; $b++) {
                // array_push($unlocked_arr, $cmt1.$b);
                $sheet->setCellValue($cmt1.$b, $tvalue);
                $sheet->getStyle($cmt1.$b.':'.$cmt2.$b)->applyFromArray($border_thin);
                $sheet->getStyle($cmt1.$b.':'.$cmt2.$b)->getAlignment()->setWrapText(true);
            }
            $sheet->mergeCells($cmt1.($tbl_2+1).':'.$cmt2.($b-1));
            $sheet->getStyle($cmt1.($tbl_2+1).':'.$cmt2.($b-1))->applyFromArray($alignlefttop);
            $sheet->getStyle($cmt1.($tbl_2+1).':'.$cmt2.($b-1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
        }

        # table three, four, five, six, seven
        $tbl_3 = $b + 1;
        foreach ($txtarea_cmt as $txtkey => $txtvalue) {
            $sheet->setCellValue('B'.$tbl_3, __($txtkey));
            $sheet->mergeCells('B'.$tbl_3.':J'.$tbl_3);
            $sheet->getStyle('B'.$tbl_3.':J'.$tbl_3)->applyFromArray($alignleft);
            $sheet->getStyle('B'.$tbl_3.':J'.$tbl_3)->applyFromArray($title);

            $limit = $tbl_3+10;
            for ($c = $tbl_3+1; $c <= $limit; $c++) {
                // array_push($unlocked_arr, 'B'.$c);
                $sheet->setCellValue('B'.$c, $txtvalue);
                $sheet->getStyle('B'.$c.':J'.$c)->applyFromArray($border_thin);
                $sheet->getStyle('B'.$c.':J'.$c)->applyFromArray($alignlefttop);
            }
            
            $sheet->mergeCells('B'.($tbl_3+1).':J'.($c-1));
            $sheet->getStyle('B'.($tbl_3+1).':J'.($c-1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
            $tbl_3 = $limit+2;
        }

        # table eight
        $tbl_8 = $c + 1;
        $colNum = PHPExcel_Cell::columnIndexFromString($cmt2);
        $de = PHPExcel_Cell::stringFromColumnIndex($colNum-3);
        $pe = PHPExcel_Cell::stringFromColumnIndex($colNum-2);
        
        $tbl8_header = "8. ".__("設備投資");
        $sheet->setCellValue('B'.$tbl_8, $tbl8_header);
        $sheet->mergeCells('B'.$tbl_8.':J'.$tbl_8);
        $sheet->getStyle('B'.$tbl_8.':J'.$tbl_8)->applyFromArray($alignleft);
        $sheet->getStyle('B'.$tbl_8.':J'.$tbl_8)->applyFromArray($title);
        $tbl_8++;
        $sheet->setCellValue('B'.$tbl_8, __("年度"));
        $sheet->setCellValue('C'.$tbl_8, __("区分"));
        $sheet->setCellValue('D'.$tbl_8, __("内容"));
        $sheet->mergeCells('D'.($tbl_8).':G'.$tbl_8);
        $sheet->setCellValue('H'.$tbl_8, __("購入日"));
        $sheet->setCellValue('I'.$tbl_8, __("リース期間"));
        $sheet->setCellValue('J'.$tbl_8, __("金額"));

        $sheet->getStyle('B'.$tbl_8.':J'.$tbl_8)->applyFromArray($border_thin);
        $sheet->getStyle('B'.$tbl_8.':J'.$tbl_8)->applyFromArray($aligncenter)->getAlignment()->setWrapText(true);
        $sheet->getStyle('B'.($tbl_8).':J'.$tbl_8)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        
        $tbl_8++;
        $section_data = "'---Select---,リース,有形固定資産,無形固定資産,一括償却";
        foreach ($table_eight as $years => $invalue) {
            $copy_cnt = count($invalue['data']);
            $cur = $tbl_8;
            $sheet->mergeCells('B'.$cur.':B'.($copy_cnt+$cur-1));
            foreach ($invalue['data'] as $key => $value) {
                $purchase_date = $value['purchase_date'];
                $purchase_date = ($purchase_date[7] == '-') ? (substr($purchase_date, 0, -1)) : $purchase_date;
                $purchase_date =  ($purchase_date == '') || ($purchase_date == '0000-00') ? '' : date("Y-m", strtotime($purchase_date));
                $section_name = ($value['section_name'] == '')? "---Select---" : $value['section_name'];
                // array_push($unlocked_arr,'C'.$tbl_8);
                // array_push($unlocked_arr,'D'.$tbl_8);
                // array_push($unlocked_arr,'H'.$tbl_8);
                // array_push($unlocked_arr,'I'.$tbl_8);
                // array_push($unlocked_arr,'J'.$tbl_8);
                $sheet->setCellValue('B'.$tbl_8, $years);

                $sheet->setCellValue('C'.$tbl_8, $section_name);
                # add the dropdown list
                $objValidation = $sheet->getCell('C'.$tbl_8)->getDataValidation();
                $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
                $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                $objValidation->setAllowBlank(false);
                $objValidation->setShowInputMessage(true);
                $objValidation->setShowErrorMessage(true);
                $objValidation->setShowDropDown(true);
                $objValidation->setFormula1('"'.$section_data.'"');

                $sheet->setCellValue('D'.$tbl_8, trim($value['detail']));
                $sheet->mergeCells('D'.$tbl_8.':G'.$tbl_8);
                $sheet->setCellValue('H'.$tbl_8, $purchase_date);
                $sheet->setCellValue('I'.$tbl_8, $value['lease_period']);
                $sheet->setCellValue('J'.$tbl_8, $value['amount']/1000);
                $sheet->getStyle('J'.$tbl_8.':J'.$tbl_8)->applyFromArray($alignright);
                $sheet->getStyle('B'.$tbl_8.':J'.$tbl_8)->applyFromArray($border_thin);
                $sheet->getStyle('B'.$tbl_8.':I'.$tbl_8)->applyFromArray($alignleft);
                $sheet->getStyle('C'.($tbl_8).':J'.$tbl_8)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                $sheet->getStyle('J'.$tbl_8.':J'.$tbl_8)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                $tbl_8++;
            }
            
            $sheet->setCellValue('B'.$tbl_8, __("投資キャッシュフローに影響する有形・無形固定資産の計"));
            $sheet->mergeCells('B'.($tbl_8).':I'.$tbl_8);
            if (empty($invalue['total'])) {
                $invalue['total'] = 0;
            }
            $sheet->setCellValue('J'.$tbl_8, $invalue['total']/1000);
            
            $sheet->getStyle('B'.$tbl_8.':J'.$tbl_8)->applyFromArray($border_thin);
            $sheet->getStyle('B'.$tbl_8.':I'.$tbl_8)->applyFromArray($alignleft);
            $sheet->getStyle('J'.$tbl_8.':J'.$tbl_8)->applyFromArray($alignright);
            $sheet->getStyle('B'.($tbl_8).':J'.$tbl_8)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
            $sheet->getStyle('J'.$tbl_8.':J'.$tbl_8)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
            $tbl_8++;
        }
        
        # table nine
        $tbl_9 = $tbl_8 + 1;
        $tbl9_header = "9. ".__("キャッシュフロー");
        $sheet->setCellValue('B'.$tbl_9, $tbl9_header);
        $sheet->mergeCells('B'.$tbl_9.':J'.$tbl_9);
        $sheet->getStyle('B'.$tbl_9.':J'.$tbl_9)->applyFromArray($alignleft);
        $sheet->getStyle('B'.$tbl_9.':J'.$tbl_9)->applyFromArray($title);
        
        $tbl_9++;
        $sheet->setCellValue('B'.$tbl_9, "");
        $sheet->mergeCells('B'.$tbl_9.':C'.$tbl_9);
        $char = 68;
        foreach ($all_years as $year) {
            $title = __('予算');
            if ($year == $last_year) {
                $title = __('実績');
            } elseif ($year == $fc_year) {
                $title = __('見込');
            }
            // array_push($unlocked_arr,chr($char).$tbl_9);
            $sheet->setCellValue(chr($char).$tbl_9, ($year).__($title));
            $sheet->getStyle(chr($char).$tbl_9.':'.chr($char+1).$tbl_9)->applyFromArray($border_thin);
            $sheet->mergeCells(chr($char).$tbl_9.':'.chr($char+1).$tbl_9);
            $sheet->getStyle(chr($char).$tbl_9.':'.chr($char+1).$tbl_9)->applyFromArray($aligncenter);
            $sheet->getStyle(chr($char).$tbl_9.':'.chr($char+1).$tbl_9)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $char += 2;
        }
        $chars = 67 + ((count($all_years))*2);
        foreach ($table_nine as $flow_name => $flow_data) {
            $tbl_9++;
            // array_push($unlocked_arr,'B'.$tbl_9);
            $sheet->setCellValue('B'.$tbl_9, $flow_name);
            $sheet->mergeCells('B'.$tbl_9.':'.(chr($chars)).$tbl_9);
            $sheet->getStyle('B'.$tbl_9.':'.(chr($chars)).$tbl_9)->applyFromArray($border_thin);
            $sheet->getStyle('B'.$tbl_9.':'.(chr($chars)).$tbl_9)->applyFromArray($alignleft);
            $sheet->getStyle('B'.$tbl_9.':C'.$tbl_9)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $curr = $tbl_9+1;
            foreach ($flow_data as $sub_flow => $subflow_data) {
                $tbl_9++;
                // array_push($unlocked_arr,'B'.$tbl_9);
                $sheet->setCellValue('B'.$tbl_9, $sub_flow);
                $sheet->mergeCells('B'.$tbl_9.':C'.$tbl_9);
                $sheet->getStyle('B'.$tbl_9.':C'.$tbl_9)->applyFromArray($border_thin);
                $sheet->getStyle('B'.$tbl_9.':C'.$tbl_9)->applyFromArray($aligncenter);
                if ($sub_flow == "計" || $sub_flow == "") {
                    $sheet->getStyle('B'.$tbl_9.':C'.$tbl_9)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
                }
                $char_sec = 68;
                foreach ($all_years as $years) {
                    if ($subflow_data['amounts'][$years] == "") {
                        $amount = 0;
                    } else {
                        $amount = $subflow_data['amounts'][$years];
                    }
                    // array_push($unlocked_arr,chr($char_sec).$tbl_9);
                    $sheet->setCellValue(chr($char_sec).$tbl_9, $amount);
                    if ($admin_level_id != 1 && $years == $last_year && strpos($subflow_data['id'], 'total') === false) {
                        $sheet->getStyle(chr($char_sec).$tbl_9.':'.chr($char_sec+1).$tbl_9)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                    } elseif ($subflow_data['input'] == true || ($years == $last_year && $admin_level_id == 1 && ($sub_flow == "固定資産" || $sub_flow == "配当（前期損益）"))) {
                        // array_push($unlocked_arr, chr($char_sec).$tbl_9);
                        $sheet->getStyle(chr($char_sec).$tbl_9.':'.chr($char_sec+1).$tbl_9)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                    } else {
                        if ($sub_flow == "計") {
                            $sheet->setCellValue(chr($char_sec).$tbl_9, '=SUM('.chr($char_sec).$curr.':'.chr($char_sec).($tbl_9-1).')');
                            $sheet->getStyle(chr($char_sec).$tbl_9.':'.chr($char_sec+1).$tbl_9)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
                            
                        // if($sheet->getCell(chr($char_sec).$tbl_9)->getValue() < 0) {
                            // 	$sheet->getStyle(chr($char_sec).$tbl_9)->applyFromArray($negative);
                            // }
                        } elseif ($sub_flow == "") {
                            if ($flow_name == "フリーキャッシュフロー（1+2）") {
                                $sheet->setCellValue(chr($char_sec).$tbl_9, '=('.chr($char_sec).($tbl_9-6).'+'.chr($char_sec).($tbl_9-2).')');
                            } elseif ($flow_name == "3.キャッシュの増減（1+2+3）") {
                                $sheet->setCellValue(chr($char_sec).$tbl_9, '=('.chr($char_sec).($tbl_9-4).'+'.chr($char_sec).($tbl_9-2).')');
                            }
                            
                            $sheet->getStyle(chr($char_sec).$tbl_9.':'.chr($char_sec+1).$tbl_9)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('eeeeee');
                        }
                    }
                    $sheet->mergeCells(chr($char_sec).$tbl_9.':'.chr($char_sec+1).$tbl_9);
                    $sheet->getStyle(chr($char_sec).$tbl_9.':'.chr($char_sec+1).$tbl_9)->applyFromArray($border_thin);
                    $sheet->getStyle('B'.$tbl_8.':'.chr($char_sec).$tbl_9)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                    $char_sec += 2;
                }
            }
        }
        #unlock editable row and column
        if ($disable_color ==  'F9F9F9') {
            $sheet->getProtection()->setPassword('*****');
            $sheet->getProtection()->setSheet(true);
        } elseif ($disable_color == 'D5F4FF') {
            /*$sheet->getProtection()->setPassword('*****');
            $sheet->getProtection()->setSheet(true);
            foreach ($unlocked_arr as $unprotect) {
                $sheet->getStyle($unprotect)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
            }
            */
            #lock editable row and column
            /*foreach ($locked_arr as $protect) {
                $sheet->protectCells($protect, '');
            }
            */
        }
        # number format
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('C12:'.$highestColumn.($getLine-2))->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
        $sheet->getStyle('C'.$getLine.':'.$highestColumn.($getLine+1))->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
        # minus is red color
        /*$alphabet = range('B', $highestColumn);
        for ($i=0; $i < count($alphabet); $i++) {
            $column = $alphabet[$i];
            for ($row = 1; $row <= $highestRow; $row++) {
                $cell = (string)$sheet->getCell($column.$row)->getValue();

                $objPHPExcel->getActiveSheet()
                            ->getStyle($column.$row)
                            ->getNumberFormat()
                            ->setFormatCode(
                                '""#,##0;[Red]"-"#,##0'
                            );
                // if($cell[0] == '-' && $cell!='---Select---'){
                //     $objPHPExcel->getActiveSheet()->getStyle($column.$row)->applyFromArray($negative);

                // }

            }
        }*/
        # header
        $sheet->setCellValue('B2', implode(' / ', $mainHeader));
        $sheet->mergeCells('B2:'.$highestColumn.'2');
        $sheet->getStyle('B2:'.$highestColumn.'2')->applyFromArray($aligncenter);
        $sheet->getStyle('B2:'.$highestColumn.'2')->getFont()->setSize(13);

        if ($save_to_tmp) {
            $PHPExcel->save($file_name);
        } else {
            $PHPExcel->output($file_name.".xlsx");
        }
        $this->autoLayout = false;
    }

    /**
     * ImportExcel method
     *
     * @author Khin Hnin Myo (20210114)
     * @param  $file,$loginId,$term_id,$head_dept_code,$ba_code,$reqData
     * @return $common_save
     */
    public function ImportExcel($file, $loginId, $term_id, $head_dept_code, $layer_code, $reqData, $term_name, $headquarter)
    {
        App::import('Vendor', 'php-excel-reader/PHPExcel');
        $year = (range(explode('~', $term_name)[0], explode('~', $term_name)[1]));
        
        foreach ($year as $key => $value) {
            if ($key==0) {
                $hder[] = $value.' '.__('Forecast');
            } else {
                array_push($hder, $value.' '.__('Budget'));
            }
        }
        #header from excel
        $hd = array(
            '1' => "1. ".__("単体Ｐ／Ｌ（収益はﾌﾟﾗｽ表示、費用はﾏｲﾅｽ表示にて記載。）"),
            '2' => "2．".__("単体Ｐ／Ｌ増減説明"),
            '3' =>  __("3. 本部目標・成長戦略（マテリアリティ視点含む）"),
            '4' => __("4. 市場動向・当社への影響（事業展望）、競合他社状況"),
            '5' => __("5. 既存事業についてのアクションプラン"),
            '6' => __("6. 新規事業についてのアクションプラン"),
            '7' => __("7. 重点施策・その他留意事項"),
            '8' => "8. ".__("設備投資"),
            '9' => "9. ".__("キャッシュフロー"),
            '10' => __("投資キャッシュフローに影響する有形・無形固定資産の計"),
        );
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION); #file extension
        $uploadPath = APP . 'tmp'; #file path
        if ($file['error'] == 0) {
            if ($file['size'] <= 1048576) { #1 Megabytes (MB)
                $file_path = $file['tmp_name'];
                if ($ext == "xlsx" || $ext == "xls") {
                    $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                    $objReader->setReadDataOnly(true);
                    if ($objReader->canRead($file_path)) {
                        $objPHPExcel = $objReader->load($file_path);
                        $objWorksheet = $objPHPExcel->getActiveSheet();
                        $highestRow = $objWorksheet->getHighestRow();
                        $highestColumn = $objWorksheet->getHighestColumn();
                        $term = $objWorksheet->getCell('C4')->getValue();
                        $hq = $objWorksheet->getCell('C5')->getValue();
                        $arr = [];
                        $header = [];
                        
                        if ($objWorksheet->getCell('B2')->getValue() == implode(' / ', $hder)) {
                            $chk_term = ($term == $term_name)? true : false;
                            $chk_hq = ($hq == $headquarter)? true : false;
                            if ((!$chk_term) || (!$chk_hq)) {
                                CakeLog::write('debug', 'term_name='.$term.' and headquarter='.$hq.'of imported file are not match with term_name='.$term_name.' and headquarter='.$headquarter.' . In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                $error[0] = __('期間');
                                $error[1] = __('と');
                                $error[2] = __('本部');
                                if (!$chk_term && !$chk_hq) {
                                    $err_msg = implode(' ', $error);
                                } elseif (!$chk_term) {
                                    $err_msg = $error[0];
                                } elseif (!$chk_hq) {
                                    $err_msg = $error[2];
                                }
                                
                                $common_save['error'] = parent::getErrorMsg('SE098', $err_msg);
                            } else {
                                $tbl_2 = $tbl_3 = $tbl_4 = $tbl_5 = $tbl_6 = $tbl_7 = $tbl_8 = $tbl_9 = 0;
                                for ($row = 1; $row <= $highestRow; $row++) {
                                    $rowData = $objWorksheet->rangeToArray('B' . $row . ':' . $highestColumn . $row, null, true, false);
                                    $worksheets[] = $rowData;
                                    $header = $rowData[0][0];
                                
                                    if ($header == $hd[2]) {
                                        $tbl_2 = count($worksheets);
                                    }
                                    if ($header == $hd[3]) {
                                        $tbl_3 = count($worksheets);
                                    }
                                    if ($header == $hd[4]) {
                                        $tbl_4 = count($worksheets);
                                    }
                                    if ($header == $hd[5]) {
                                        $tbl_5 = count($worksheets);
                                    }
                                    if ($header == $hd[6]) {
                                        $tbl_6 = count($worksheets);
                                    }
                                    if ($header == $hd[7]) {
                                        $tbl_7 = count($worksheets);
                                    }
                                    if ($header == $hd[8]) {
                                        $tbl_8 = count($worksheets)+1;
                                    }
                                    if ($header == $hd[10]) {
                                        array_push($arr, count($worksheets));
                                    }
                                    if ($header == $hd[9]) {
                                        $tbl_9 = count($worksheets)+1;
                                    }
                                }
                                
                                if ($tbl_2 != 0 && $tbl_3 != 0 && $tbl_4 != 0 && $tbl_5 != 0 && $tbl_6 != 0 && $tbl_7 != 0 && $tbl_8 != 0 && $tbl_9 != 0) {
                                    #table_two (tbl_pl_explain)
                                    $alphabet = range('B', $highestColumn);
                                    $first_cnt = 0;

                                    foreach ($reqData['year_pl_cmt'] as $key => $value) {
                                        $first_cnt++;
                                        if ($first_cnt == 1) {
                                            $pl_header = (($key).__("年度").(" ").__("見込の主な内容及び、予算比増減説明"));
                                            if ($pl_header != $worksheets[$tbl_2][0][0]) {
                                                CakeLog::write('debug', ' Invalid amount format error occur '.$worksheets[$tbl_2][0][0].' will be "'.$pl_header.'", at col B and row '.($tbl_2+1).'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                                $error[0] = $key;
                                                $error[1] = $hd[2];
                                                $common_save['error'] = parent::getErrorMsg('SE101', $error);
                                                return $common_save;
                                            }
                                        } else {
                                            if ($first_cnt == 2) {
                                                $column = 4;
                                            } elseif ($first_cnt == 3) {
                                                $column+=5;
                                            } else {
                                                $column+=4;
                                            }
                                            
                                            $pl_header = (($key).__("年度").(" ").__("予算案の主な内容及び、前年見込比増減説明"));
                                            if ($pl_header != $worksheets[$tbl_2][0][$column]) {
                                                CakeLog::write('debug', ' Invalid header format occur '.$worksheets[$tbl_2][0][$column].' will be "'.$pl_header.'", at row '.($tbl_2+1).'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                                $error[0] = $key;
                                                $error[1] = $hd[2];
                                                $common_save['error'] = parent::getErrorMsg('SE101', $error);
                                                return $common_save;
                                            }
                                        }
                                        for ($col = 0; $col < count($alphabet); $col++) {
                                            for ($a = $tbl_2; $a < ($tbl_3-1); $a++) {
                                                if ($worksheets[$a][0][$col] == $pl_header) {
                                                    $reqData['year_pl_cmt'][$key] = $worksheets[$a+1][0][$col];
                                                }
                                            }
                                        }
                                    }
                                    
                                    #table_three to table_seven (tbl_ceo_comment)
                                    $reqData['comment3'] = $worksheets[$tbl_3][0][0];
                                    $reqData['comment4'] = $worksheets[$tbl_4][0][0];
                                    $reqData['comment5'] = $worksheets[$tbl_5][0][0];
                                    $reqData['comment6'] = $worksheets[$tbl_6][0][0];
                                    $reqData['comment7'] = $worksheets[$tbl_7][0][0];
                                    
                                    #table eight (tbl_investment)
                                    $reqData['investment'] = [];
                                    if (($worksheets[$tbl_8-1][0][0] == __("年度")) &&
                                    ($worksheets[$tbl_8-1][0][1] == __("区分")) &&
                                    ($worksheets[$tbl_8-1][0][2] == __("内容")) &&
                                    ($worksheets[$tbl_8-1][0][6] == __("購入日")) &&
                                    ($worksheets[$tbl_8-1][0][7] == __("リース期間")) &&
                                    ($worksheets[$tbl_8-1][0][8] == __("金額"))) {
                                        foreach ($arr as $key => $value) {
                                            $cnt = 0;
                                            for ($one = $tbl_8; $one < $value; $one++) {
                                                $cnt++;
                                                $tbl_8++;
                                                $formulas = $objWorksheet->getCell("J". $tbl_8)->getOldCalculatedValue();

                                                if (strpos($formulas, '#') !== false) {
                                                    $err = array(__('小数点以下2桁の9桁'),'J',$tbl_8);
                                                    $common_save['error'] = parent::getErrorMsg('SE099', $err);
                                                    return $common_save;
                                                }

                                                if ($worksheets[$one][0][0] != $hd[10]) {
                                                    #excel copy row(year)
                                                    if($worksheets[$one][0][0] == '') {
                                                        $worksheets[$one][0][0] = $yr;
                                                    }
                                                    $yr = $worksheets[$one][0][0];
                                                    $sec_name = ($worksheets[$one][0][1] == '') ? '' : $worksheets[$one][0][1];
                                                    $detail = $worksheets[$one][0][2];
                                                    $purchase_date = $worksheets[$one][0][6];
                                                    $lease_period = $worksheets[$one][0][7];
                                                    $amount = round($worksheets[$one][0][8], 3);
                                                    
                                                    if ($purchase_date != "" && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $purchase_date)) {
                                                        $error[0] = __('日付形式（YYYY-MM）');
                                                        $error[1] = ' H';
                                                        $error[2] = ($one+1);
                                                        CakeLog::write('debug', ' Invalid date format error occur '.$purchase_date.' will be "Date Format(YYYY-MM)", at col H and row '.($one+1).'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                                        $common_save['error'] = parent::getErrorMsg('SE099', $error);
                                                        return $common_save;
                                                    }
                                                    if (is_numeric($amount)) {
                                                        if (!preg_match('/^\s*-?(\d{0,7})(\.\d{0,3})?\s*$/', $amount)) {
                                                            $error[0] = __('小数点以下3桁の9桁');
                                                            $error[1] = ' J';
                                                            $error[2] = ($one+1);
                                                            CakeLog::write('debug', ' Invalid amount format error occur '.$amount.' will be 9 digits with 2 decimal place at col J and row '.($one+1).'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                                            $common_save['error'] = parent::getErrorMsg('SE099', $error);
                                                            return $common_save;
                                                        }
                                                    } elseif ($amount != "") {
                                                        $error[0] = __('数値形式');
                                                        $error[1] = ' J';
                                                        $error[2] = ($one+1);
                                                        CakeLog::write('debug', ' Invalid amount format error occur '.$amount.' will be "float Format", at col J and row '.($one+1).'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                                        $common_save['error'] = parent::getErrorMsg('SE099', $error);
                                                        return $common_save;
                                                    }
                                                    
                                                    $reqData['investment'][$yr]['tr_'.$cnt]['section_name'] = $sec_name;
                                                    $reqData['investment'][$yr]['tr_'.$cnt]['detail'] = $detail;
                                                    $reqData['investment'][$yr]['tr_'.$cnt]['purchase_date'] = $purchase_date;
                                                    $reqData['investment'][$yr]['tr_'.$cnt]['lease_period'] = $lease_period;
                                                    $reqData['investment'][$yr]['tr_'.$cnt]['hid_amt'] = $amount;
                                                }
                                            }
                                        }
                                    } else {
                                        CakeLog::write('debug', ' Invalid header format occur at row '.($tbl_8).'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                        $common_save['error'] = parent::getErrorMsg('SE102', $hd[8]);
                                        return $common_save;
                                    }
                                    #table nine (tbl_cash_flow)
                                    $col = range('D', $highestColumn);
                                    for ($p =($tbl_9+2); $p <= $highestRow; $p++) {
                                        for ($a=0;$a<count($col);$a++) {
                                            $formulas = $objWorksheet->getCell($col[$a].$p)->getOldCalculatedValue();
                                            if (strpos($formulas, '#') !== false) {
                                                $err = array(__('小数点以下2桁の9桁'),$col[$a],$p);
                                                $common_save['error'] = parent::getErrorMsg('SE099', $err);
                                                return $common_save;
                                            }
                                        }
                                    }
                                    $cash_col = 0;
                                    foreach ($reqData['cashflow'] as $key => $value) {
                                        $cash_col += 2;
                                        $b = $tbl_9;
                                        if ($cash_col == 2) {
                                            $nine_hder = $key.__("実績");
                                        } elseif ($cash_col == 4) {
                                            $nine_hder = $key.__("見込");
                                        } else {
                                            $nine_hder = $key.__("予算");
                                        }

                                        if ($worksheets[$tbl_9-1][0][$cash_col] == $nine_hder) {
                                            foreach ($value as $keys => $values) {
                                                $b += 2;
                                                foreach ($values as $keyss => $valuess) {
                                                    if ($keyss == '配当（前期損益）') {
                                                        $b += 2;
                                                    }
                                                    if ($keys == '2.投資キャッシュフロー' && count($values) == '1') {
                                                        $b += 1;
                                                    }

                                                    if ($worksheets[$b][0][0] == $keyss) {
                                                        if (is_numeric($worksheets[$b][0][$cash_col])) {
                                                            if (!preg_match('/^\s*-?(\d{0,7})(\.\d{0,3})?\s*$/', $worksheets[$b][0][$cash_col])) {
                                                                $error[0] = __('小数点以下3桁の9桁');
                                                                $error[1] = ' '.$alphabet[$cash_col];
                                                                $error[2] = ($b+1);
                                                                CakeLog::write('debug', ' Invalid amount format error occur '.$worksheets[$b][0][$cash_col].' will be 9 digits with 2 decimal place at col '.$alphabet[$cash_col].' and row '.($b+1).'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                                                $common_save['error'] = parent::getErrorMsg('SE099', $error);
                                                                return $common_save;
                                                            }
                                                        } elseif ($worksheets[$b][0][$cash_col] != "") {
                                                            $error[0] = __('数値形式');
                                                            $error[1] = ' '.$alphabet[$cash_col];
                                                            $error[2] = ($b+1);
                                                            CakeLog::write('debug', ' Invalid amount format error occur '.$worksheets[$b][0][$cash_col].' will be "float Format", at col '.$alphabet[$cash_col].' and row '.($b+1).'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                                            $common_save['error'] = parent::getErrorMsg('SE099', $error);
                                                            return $common_save;
                                                        }
                                                        $reqData['cashflow'][$key][$keys][$keyss] = round($worksheets[$b][0][$cash_col], 3);
                                                    }
                                                    $b += 1;
                                                }
                                            }
                                        } else {
                                            CakeLog::write('debug', ' Invalid header format occur at row '.($tbl_9-1).'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                            $common_save['error'] = parent::getErrorMsg('SE102', $hd[9]);
                                            return $common_save;
                                        }
                                    }
                                    
                                    $common_save = $this->CommonSave($loginId, $term_id, $head_dept_code, $layer_code, $reqData);
                                } else {
                                    CakeLog::write('debug', 'table header is not completed. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                    $common_save['error'] = parent::getErrorMsg('SE021');
                                }
                            }
                        } else {
                            CakeLog::write('debug', 'file format is invalid(not summarytable excel download format).'. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $common_save['error'] = parent::getErrorMsg('SE021');
                        }
                    } else {
                        CakeLog::write('debug', 'cannot read the file. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        $common_save['error'] = parent::getErrorMsg('SE015');
                    }
                } else {
                    CakeLog::write('debug', 'extension of file is not correct. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $common_save['error'] = parent::getErrorMsg("SE013", $ext);
                }
            } else {
                CakeLog::write('debug', 'file size over 1MB. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $common_save['error'] = parent::getErrorMsg('SE020');
            }
        } else {
            CakeLog::write('debug', '$file[error]!=0. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $common_save['error'] = parent::getErrorMsg('SE015');
        }
        return $common_save;
    }

    public function getTableOneAccount($term_id, $head_dept_code, $last_year, $fc_year, $bg_year_arr)
    {
        $Common = new CommonController;
        $forecast_data = array();
        $budget_data = array();
        $table_data = array();
        $group_code = '05';

        $codes_pair = $this->getCodePairs($group_code);

        $start_month = $Common->getMonth($fc_year, $term_id, 'start');
        $end_month = $Common->getMonth($fc_year, $term_id, 'end');

        $last_year_start = date("Y-m", strtotime($start_month. "last day of - 1 year"));
        $last_year_end = date("Y-m", strtotime($end_month. "last day of - 1 year"));

        #Get all ba codes with head_dept_id
        $active_bas = $this->getActiveBA($head_dept_code);
        $layer_codes = "'".join("','", $active_bas)."'";

        $manual_tax_ba = Setting::BA_BUDGET_TAX;
        $yearly_tax = $this->RtaxFee->find('list', array(
            'fields' => array('target_year','rate'),
            'conditions' => array('flag' => 1)
        ));

        foreach ($active_bas as $each_ba) {
            if (in_array($each_ba, $manual_tax_ba)) {
                $manual_ba_exist = true;
            }
        }

        $result_amount = array();


        foreach ($codes_pair as $sub_acc_name => $each_account) {
            $type = $each_account['type'];
            $acc_codes = "'".join("','", $each_account['codes'])."'";
            $result_amt = $this->BrmActualResultSummary->getYearlyResult($layer_codes, $last_year_start, $last_year_end, $acc_codes);

            #adjust result amount(Delete Later)
            $adjustment = Setting::RESULT_ADJUSTMENT;
            $adjust_amt = $adjustment[$fc_year-1][$head_dept_code][$sub_acc_name];
            if (!empty($adjust_amt)) {
                $result_amt = $result_amt+$adjust_amt;
            }

            $budget_amt = $this->BrmBudget->getYearlyBudget($layer_codes, $term_id, $start_month, $end_month, $acc_codes);

            #Get forecast period(eg: 2020-05) to show actual result data till to this period
            $forecastPeriod = $this->BrmTerm->getForecastPeriod($term_id);
            // $forecastPeriod = $termData[0]['tbl_term']['forecast_period'];

            $forecast_months = array();

            #Loop through 12 months
            for ($i=0; $i <12 ; $i++) {

                #Increase month by loop count
                $month = date("Y-m", strtotime($start_month. "last day of + ".$i." Month"));
                $field = 'month_'.($i+1).'_amt';
                if ((!empty($forecastPeriod) || $forecastPeriod!='') && (strtotime($month) <= strtotime($forecastPeriod))) {
                    foreach ($active_bas as $each_ba) {
                        $bacode = "'".$each_ba."'";
                        $result_amount[$sub_acc_name][$each_ba] += $this->BrmActualResultSummary->getMonthlyResult($bacode, $month, $acc_codes);
                    }
                } else {
                    $forecast_months[] = $field;
                }
            }

            $month_cols = implode('+', $forecast_months);
            
            $expected_amt = (!empty($month_cols)) ? $this->BrmExpected->getYearlyExpected($layer_codes, $term_id, $fc_year, $acc_codes, $month_cols) + array_sum($result_amount[$sub_acc_name]) :  array_sum($result_amount[$sub_acc_name]);
            
            #first table
            $forecast_data[$sub_acc_name]['type'] = $type;
            $forecast_data[$sub_acc_name]['result_amt'] = round($result_amt/1000000);
            $forecast_data[$sub_acc_name]['budget_amt'] = round($budget_amt/1000000);
            $forecast_data[$sub_acc_name]['expected_amt'] = round($expected_amt/1000000);
            $forecast_data[$sub_acc_name]['expected_budget_diff'] = round(($expected_amt-$budget_amt)/1000000);
            
            #second,third,... table
            $last_year_budget_amt = array();
            foreach ($bg_year_arr as $budget_year) {
                $half_budget_amt = $this->BrmBudgetPrime->getFirstHalfBudget($layer_codes, $term_id, $budget_year, $acc_codes);
                $yearly_budget_amt = $this->BrmBudgetPrime->getYearlyBudget($layer_codes, $term_id, $budget_year, $acc_codes);

                $last_year_budget_amt[$budget_year] = $yearly_budget_amt;

                $budget_data[$budget_year][$sub_acc_name]['type'] = $type;
                $budget_data[$budget_year][$sub_acc_name]['half_budget'] = round($half_budget_amt/1000000);
                $budget_data[$budget_year][$sub_acc_name]['yearly_budget'] = round($yearly_budget_amt/1000000);

                $difference = 0;
                if ($budget_year-1 == $fc_year) {
                    $difference = $yearly_budget_amt-$expected_amt;
                } else {
                    $difference = $yearly_budget_amt-$last_year_budget_amt[$budget_year-1];
                }

                $budget_data[$budget_year][$sub_acc_name]['difference'] = round($difference/1000000);
            }
        }

        $return_data = array(
            'forecast' => $forecast_data,
            'budget' => $budget_data
        );
        
        return $return_data;
    }

    public function getCodePairs($group_code)
    {
        $codes_pair = array();
        $calculation_pair = array();
        $acc_data = $this->BrmAccount->find('list', array(
            'fields' => array(
                'name_jp','calculation_method','id'
            ),
            'conditions' => array(
                'BrmAccount.flag' => 1,
                'BrmAccount.group_code' => $group_code
            )
        ));

        foreach ($acc_data as $acc_id => $acc_value) {
            $acc_name = array_keys($acc_value)[0];
            $calculation_method = array_values($acc_value)[0];

            #prepare [sub_id] => [sub_name] as list array
            $acc_data = $this->BrmSaccount->find('list', array(
                'fields' => array(
                    'id','account_code'
                ),
                'conditions' => array(
                    'BrmSaccount.flag' => 1,
                    'BrmSaccount.pair_ids LIKE' => '%:'. $acc_id . ',%',
                )
            ));
            
            $codes_pair[$acc_name]['type'] = 0;
            $codes_pair[$acc_name]['codes'] = $acc_data;
            if (empty($acc_data)) {
                $calculation_pair[$acc_name] = json_decode($calculation_method, 1)['field'];
            }
        }

        foreach ($calculation_pair as $acc_name => $pair_names) {
            foreach ($pair_names as $pair_acc_name) {
                $codes_pair[$acc_name]['type'] = 1;
                $codes_pair[$acc_name]['codes'] = array_merge($codes_pair[$acc_name]['codes'], $codes_pair[$pair_acc_name]['codes']);
            }
        }
        return $codes_pair;
    }

    public function getTableOnePersonCount($term_id, $head_dept_code, $fc_year, $bg_year_arr)
    {
        $person_data = array();

        $layer_codes = $this->getActiveBA($head_dept_code);

        $this->BrmExpectedBudgetDiffJob->virtualFields['total'] = 'SUM(amount)';
        $emp_count_budget = $this->BrmExpectedBudgetDiffJob->find('all', array(
            'fields' => 'total',
            'conditions' => array(
                'brm_term_id' => $term_id,
                'layer_code'  => $layer_codes,
                'target_year' => $fc_year,
                'type'        => 'budget',
                'NOT'         => array(
                    'name_jp' => '派遣社員'
                )
            ),
        ));

        $emp_count_forecast = $this->BrmManpowerPlan->find('first', array(
            'fields' => array('total','first_half_total'),
            'conditions' => array(
                'BrmManpowerPlan.brm_term_id' => $term_id,
                'BrmManpowerPlan.layer_code'  => $layer_codes,
                'BrmManpowerPlan.target_year' => $fc_year,
                'NOT' => array(
                    'BrmPosition.display_no'      => array(2,4),
                    'BrmManpowerPlan.brm_position_id' => 0,
                )
            ),
        ));

        #adjust result employee count(Delete Later)
        $setting_emp_cnt = Setting::RESULT_EMP_COUNT;
        $result_emp_cnt = $setting_emp_cnt[$fc_year-1][$head_dept_code];

        $amount1 = number_format($emp_count_budget[0]['BrmExpectedBudgetDiffJob']['total'], 1);
        $amount2 = number_format($emp_count_forecast['BrmManpowerPlan']['total']/12, 1);
        $diff = $amount2 - $amount1;
        $person_data['forecast'] = array(
            // 'last_result' => '',
            'last_result' => $result_emp_cnt,
            'budget'      => $amount1,
            'forecast'    => $amount2,
            'diff'        => number_format($amount2-$amount1, 1)
        );
        $budget = array();
        $last_year_amount[$fc_year] = $amount2;
        
        foreach ($bg_year_arr as $each_year) {
            $budget_emp_cnt = $this->BrmManpowerPlan->find('first', array(
                'fields' => array('total','first_half_total'),
                'conditions' => array(
                    'BrmManpowerPlan.brm_term_id' => $term_id,
                    'BrmManpowerPlan.layer_code' => $layer_codes,
                    'BrmManpowerPlan.target_year' => $each_year,
                    'NOT' => array(
                        'BrmPosition.display_no' => array(2,4),
                        'BrmManpowerPlan.brm_position_id' => 0,
                    )
                ),
            ));

            $amt1 = number_format($budget_emp_cnt['BrmManpowerPlan']['first_half_total']/6, 1);
            $amt2 = number_format($budget_emp_cnt['BrmManpowerPlan']['total']/12, 1);
            $last_year_amount[$each_year] = $amt2;

            $tmp = array(
                'first_half' => $amt1,
                'total' => $amt2,
                'diff' => number_format($amt2-$last_year_amount[$each_year-1], 1)
            );
            $person_data['budget'][$each_year] = $tmp;
        }
        return $person_data;
    }

    public function getCashFlow($term_id, $head_dept_code, $flow, $sub_flow)
    {   $this->BrmCashFlow->virtualFields['amount'] = 'amount/1000';
        $cashflow = $this->BrmCashFlow->find('list', array(
            'fields' => array('target_year','amount'),
            'conditions' => array(
                'brm_term_id' => $term_id,
                'hlayer_code' => $head_dept_code,
                'flow_name' => $flow,
                'sub_flow' => $sub_flow,
                'flag' => 1
            )
        ));

        return $cashflow;
    }

    public function getTableNine($term_id, $head_dept_code, $last_year, $fc_year, $all_years, $table_eight, $table_one_acc)
    {
        $table_nine = array(
            '1.営業キャッシュフロー' => array(
                '税後利益' => array(
                    'input' => false,
                    'amounts' => array(),
                    'id' => 'operation_1'
                ),
                '減価償却' => array(
                    'input' => true,
                    'amounts' => array(),
                    'id' => 'operation_2'
                ),
                'その他' =>  array(
                    'input' => true,
                    'amounts' => array(),
                    'id' => 'operation_3',
                    'border' => 'border-bottom: 2px solid #808080;'
                ),
                '計' => array(
                    'input' => false,
                    'amounts' => array(),
                    'id' => 'total_operation',
                ),
            ),
            '2.投資キャッシュフロー' =>  array(
                '固定資産' => array(
                    'input' => false,
                    'amounts' => array(),
                    'id' => 'investment_1'
                ),
                '投資等' => array(
                    'input' => true,
                    'amounts' => array(),
                    'id' => 'investment_2',
                    'border' => 'border-bottom: 2px solid #808080;'
                ),
                '計' => array(
                    'input' => false,
                    'amounts' => array(),
                    'id' => 'total_investment'
                ),
            ),
            'フリーキャッシュフロー（1+2）' =>  array(
                '' => array(
                    'input' => false,
                    'amounts' => array(),
                    'id' => 'total_1_2'
                ),
            ),
            '3.財務キャッシュフロー' =>  array(
                '配当（前期損益）' => array(
                    'input' => false,
                    'amounts' => array(),
                    'id' => 'financial_1'
                ),
            ),
            '3.キャッシュの増減（1+2+3）' =>  array(
                '' => array(
                    'input' => false,
                    'amounts' => array(),
                    'id' => 'total_1_2_3'
                ),
            ),
        );

        $loopcnt = 0;
        foreach ($table_nine as $flow => $flow_data) {
            $loopcnt ++;
            foreach ($flow_data as $sub_flow => $sub_flow_data) {
                if ($sub_flow_data['input'] || ($sub_flow == '固定資産' || $sub_flow == '配当（前期損益）')) {
                    $yearly_amount = $this->getCashFlow($term_id, $head_dept_code, $flow, $sub_flow);
                    foreach ($all_years as $year) {
                        $amount = (!empty($yearly_amount[$year])) ? ($yearly_amount[$year]) : 0;
                        $table_nine[$flow][$sub_flow]['amounts'][$year] = $amount;

                        if ($flow != "3.財務キャッシュフロー") {
                            $table_nine[$flow]['計']['amounts'][$year] += $amount;
                        }
                        if ($loopcnt < 3) {
                            $table_nine['フリーキャッシュフロー（1+2）']['']['amounts'][$year] += $amount;
                        }
                        $table_nine['3.キャッシュの増減（1+2+3）']['']['amounts'][$year] += $amount;
                    }
                }
            }
        }
    
        foreach ($all_years as $year) {
            $profit = 0;
            if ($year == $fc_year) {
                $profit_data = end($table_one_acc['forecast']);
                $profit = round($profit_data['expected_amt']);
                $profits = round($profit_data['expected_amt']);
            } elseif ($year == $last_year) {
                $profit_data = end($table_one_acc['forecast']);
                $profit = round($profit_data['result_amt']);
            } else {
                $profit_data = end($table_one_acc['budget'][$year]);
                $profit = round($profit_data['yearly_budget']);
            }

            $table_nine['1.営業キャッシュフロー']['税後利益']['amounts'][$year] = $profit;
            $table_nine['1.営業キャッシュフロー']['計']['amounts'][$year] += $profit;
            $table_nine['フリーキャッシュフロー（1+2）']['']['amounts'][$year] += $profit;
            $table_nine['3.キャッシュの増減（1+2+3）']['']['amounts'][$year] += $profit;
            #for 配当（前期損益）by khin hnin myo

            $table_nine['3.財務キャッシュフロー']['配当（前期損益）']['amounts'][$year+1] = -$profit;
            $table_nine['3.財務キャッシュフロー']['配当（前期損益）']['amounts'][$fc_year+1] = -$profits;
            $table_nine['3.キャッシュの増減（1+2+3）']['']['amounts'][$year+1] += -$profit;
        }

        #for 配当（前期損益）by khin hnin myo(total)
        $table_nine['3.キャッシュの増減（1+2+3）']['']['amounts'][$fc_year+1] = $table_nine['フリーキャッシュフロー（1+2）']['']['amounts'][$fc_year+1] + $table_nine['3.財務キャッシュフロー']['配当（前期損益）']['amounts'][$fc_year+1];
        
        foreach ($table_eight as $year => $eight_value) {
            $table_nine['2.投資キャッシュフロー']['固定資産']['amounts'][$year] = ($eight_value['total']/1000);
            $table_nine['2.投資キャッシュフロー']['計']['amounts'][$year] += ($eight_value['total']/1000);
            $table_nine['フリーキャッシュフロー（1+2）']['']['amounts'][$year] += ($eight_value['total']/1000);
            $table_nine['3.キャッシュの増減（1+2+3）']['']['amounts'][$year] += ($eight_value['total']/1000);
        }   
        return $table_nine;
    }

    public function getActiveBA($head_dept_code)
    {
        if (isset($_SESSION['SESSION_LAYER_CODE']) && !empty($_SESSION['SESSION_LAYER_CODE'])) {
            $layer_code = $_SESSION['SESSION_LAYER_CODE'];
        }
        if (!empty($layer_code)) {
            $data = array($layer_code);
        } else {
            $today_date = date("Y/m/d");
            $data = $this->Layer->find('list', array(
                'fields' => 'layer_code',
                'conditions' => array(
                    "Layer.parent_id LIKE CONCAT('%\"L', ".Setting::LAYER_SETTING['topLayer'].", '\":\"',".$head_dept_code.",'\"%')",
                    'Layer.flag' => 1,
                    'type_order' => Setting::LAYER_SETTING['bottomLayer'],
                    'to_date >=' => $today_date
                )
            ));
        }
        return $data;
    }

    public function saveBudgetLogData($term_id, $head_dept_code, $ba, $flag, $login_id)
    {
        $dept_id = empty($this->getDeptid($ba))? 0 : $this->getDeptid($ba);
        
        $record_log = array(
            'brm_term_id' => $term_id,
            'hlayer_code' => $head_dept_code,
            'dlayer_code' => $dept_id,
            'layer_code' => $ba,
            'flag' => $flag,
            'created_by' => $login_id,
            'updated_by' => $login_id
        );
        
        $budget_log = $this->BrmBudgetApprove->find('first', array(
            'fields' => array('id', 'created_by', 'created_date'),
            'conditions' => array(
                'brm_term_id' => $term_id,
                'hlayer_code' => $head_dept_code,
                'layer_code' => $ba
            )
        ));
        if (!empty($budget_log)) {
            $record_log['id'] = $budget_log['BrmBudgetApprove']['id'];
            $record_log['created_by'] = $budget_log['BrmBudgetApprove']['created_by'];
            $record_log['created_date'] = $budget_log['BrmBudgetApprove']['created_date'];
        }
        $this->BrmBudgetApprove->saveAll($record_log);
    }

    public function getDeptid($ba)
    {
        $today_date = date("Y/m/d") ;
        $dept_id = $this->Layer->find('first', array(
            'fields' => 'layer_code',
            'conditions' => array(
                "layer_code IN (select JSON_EXTRACT(parent_id, '$.L".SETTING::LAYER_SETTING['middleLayer']."') from layers where layer_code = ".$ba." AND flag = 1 AND to_date >= ".$today_date.")",
                'Layer.flag' => 1,
                'to_date >=' => $today_date
            )
        ))['Layer']['layer_code'];
        return $dept_id;
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
}
