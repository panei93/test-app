<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * ForecastPlan Controller
 *
 * @property Account $Account
 * @property PaginatorComponent $Paginator
 */
class BrmBudgetProgressReportController extends AppController
{
    public $uses = array('BrmBudgetApprove', 'Layer','LayerType','User','HeadDepartmentModel','BrmTerm');
    public $components = array('Session');
    public $helpers = array('Html');

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
        
        $term = $this->Session->read('TERM_NAME');
        $term_id = $this->Session->read('TERM_ID');
        if (!$term_id && !$term) {
            $errorMsg = parent::getErrorMsg('SE080');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }
    }
    /**
     * index method
     *
     * @author Ei Thandar Kyaw on 13/08/2020
     * @return void
     */

    public function index()
    {
        $Common = new CommonController();
        
        $this->layout = 'phase_3_menu';
        $language     = $this->Session->read('Config.language');
        if ($this->Session->check('TERM_NAME')) {
            $term = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('HEAD_DEPT_NAME')) {
            $headQuarterName = $this->Session->read('HEAD_DEPT_NAME');
        }

        #get permission when login
        if ($this->Session->check('PERMISSIONS')) {
            $permission = $this->Session->read('PERMISSIONS');
        }
        #get user id
        $user_id = $this->Session->read('LOGIN_ID');
        $today_date = date("Y/m/d") ;

        #get read_limit 1 or 2 or 3 or 4
        $readLimit   = $permission['index']['limit'];
        $conditions = array();

        $conditions = array(
            'Layer.flag' => 1,
            'Layer.to_date >=' => $today_date,
            'Layer.type_order' => Setting::LAYER_SETTING['topLayer']
        );
        if ($readLimit == 1) {
            $budgetLogData = [];
            foreach ($permission['index']['parent_data'] as $layer_code=>$p_data) {
                $parent_list = implode(",",$p_data);
                $temp = $this->Layer->find('all', array(
                    'fields' => array(
                        'Layer.name_jp as hlayer',
                        'second_layer.name_jp as dlayer',
                        'Layer.layer_code as hlayer_code',
                        'second_layer.layer_code as dlayer_code',
                        'bottom_layer.name_jp as blayer',
                        'bottom_layer.layer_code as blayer_code',
                        'bl.updated_date',
                        'User.user_name',
                    ),
                    'joins' => array(
                        array(
                            'table' => 'layers',
                            'alias' => 'second_layer',
                            // 'type'  => 'LEFT',
                            'conditions' => array(
                                'second_layer.flag = 1',
                                'second_layer.to_date >= '=> $today_date,
                                'second_layer.type_order' => Setting::LAYER_SETTING['middleLayer'],
                                'second_layer.layer_code IN ('.$parent_list.')'
                            )
                        ),
                        array(
                            'table' => 'layers',
                            'alias' => 'bottom_layer',
                            // 'type'  => 'LEFT',
                            'conditions' => array(
                                'bottom_layer.flag = 1',
                                'bottom_layer.to_date >= '=> $today_date,
                                'bottom_layer.type_order' => Setting::LAYER_SETTING['bottomLayer'],
                                "bottom_layer.parent_id LIKE CONCAT('%', Layer.layer_code, '%')",
                                "bottom_layer.parent_id LIKE CONCAT('%', second_layer.layer_code, '%')"
                            )
                        ),
                        array(
                            'table' => 'brm_budget_approves',
                            'alias' => 'bl',
                            'type'  =>  'left',
                            'conditions' => array(
                                'bl.layer_code = Layer.layer_code AND 
                                bl.flag = 2 AND bl.brm_term_id ='.$term_id
                            )
                        ),
                        array(
                            'table' => 'users',
                            'alias' => 'User',
                            'type'  =>  'left',
                            'conditions' => array(
                                'User.id = bl.updated_by AND 
                                User.flag = 1'
                            )
                        )
                    ),     
                    'conditions' => array(
                        'Layer.flag' => 1,
                        'Layer.to_date >=' => $today_date,
                        'Layer.layer_code' => $p_data['L1']
                     ),
                    'order' => array('Layer.id ASC')
                ));
                array_push($budgetLogData,$temp);
            }
            $budgetLogData = array_reduce($budgetLogData, 'array_merge', array());
        } else {
            $budgetLogData = $this->Layer->find('all', array(
                'fields' => array(
                    'Layer.name_jp as hlayer',
                    'second_layer.name_jp as dlayer',
                    'Layer.layer_code as hlayer_code',
                    'second_layer.layer_code as dlayer_code',
                    'bottom_layer.name_jp as blayer',
                    'bottom_layer.layer_code as blayer_code',
                    'bl.updated_date',
                    'User.user_name',
                ),
                'joins' => array(
                    array(
                        'table' => 'layers',
                        'alias' => 'second_layer',
                        // 'type'  => 'LEFT',
                        'conditions' => array(
                            'second_layer.flag = 1',
                            'second_layer.to_date >= '=> $today_date,
                            'second_layer.type_order' => Setting::LAYER_SETTING['middleLayer'],
                            "second_layer.parent_id LIKE CONCAT('%', Layer.layer_code, '%')"
                        )
                    ),
                    array(
                        'table' => 'layers',
                        'alias' => 'bottom_layer',
                        // 'type'  => 'LEFT',
                        'conditions' => array(
                            'bottom_layer.flag = 1',
                            'bottom_layer.to_date >= '=> $today_date,
                            'bottom_layer.type_order' => Setting::LAYER_SETTING['bottomLayer'],
                            "bottom_layer.parent_id LIKE CONCAT('%', Layer.layer_code, '%')",
                            "bottom_layer.parent_id LIKE CONCAT('%', second_layer.layer_code, '%')"
                        )
                    ),
                    array(
                        'table' => 'brm_budget_approves',
                        'alias' => 'bl',
                        'type'  =>  'left',
                        'conditions' => array(
                            'bl.layer_code = Layer.layer_code AND 
                            bl.flag = 2 AND bl.brm_term_id ='.$term_id
                        )
                    ),
                    array(
                        'table' => 'users',
                        'alias' => 'User',
                        'type'  =>  'left',
                        'conditions' => array(
                            'User.id = bl.updated_by AND 
                            User.flag = 1'
                        )
                    )
                ),     
                'conditions' => $conditions,
                'order' => array('Layer.id ASC')
            ));
        }
        
        $budget_years = explode('~', $term);
        foreach ($budgetLogData as $budget_log) {
            $hq_approve_data = $this->BrmBudgetApprove->find('list', array(
                'fields' => array('BrmBudgetApprove.updated_date','User.user_name','hlayer_code'),
                'joins' => array(
                    array(
                        'table' => 'users',
                        'alias' => 'User',
                        'type'  =>  'left',
                        'conditions' => array(
                            'User.id = BrmBudgetApprove.updated_by AND 
							User.flag = 1'
                        )
                    )
                ),
                'conditions' => array(
                    'BrmBudgetApprove.brm_term_id' => $term_id,
                    'BrmBudgetApprove.dlayer_code' => 0,
                    'BrmBudgetApprove.layer_code' => 0,
                    'BrmBudgetApprove.flag' => 2,
                )
            ));

            $head_id = $budget_log['Layer']['hlayer_code'];
            $hq_approve_date = array_keys($hq_approve_data[$head_id])[0];
            
            $hq_approve_date = ($hq_approve_date == '') ? '' : date("m/d/Y", strtotime($hq_approve_date));
            $ba_approve_date = $budget_log['bl']['updated_date'];
            $ba_approve_date = ($ba_approve_date == '') ? '' : date("m/d/Y", strtotime($ba_approve_date));

            if($hq_approve_date != '' && $ba_approve_date == '') $ba_approve_date = $hq_approve_date;

            $hq_app_user = array_values($hq_approve_data[$head_id])[0];
            $ba_app_user = $budget_log['User']['user_name'];
            if($hq_app_user != '' && $ba_app_user == '') $ba_app_user = $hq_app_user;
            
            $ba_list[] = array(
                'term_id' => $term_id,
                'hq_name' => $budget_log['Layer']['hlayer'],
                'hq_id' => $budget_log['Layer']['hlayer_code'],
                'dept_name' => $budget_log['second_layer']['dlayer'],
                'layer_code' => $budget_log['bottom_layer']['blayer_code'],
                'ba_name' => $budget_log['bottom_layer']['blayer_code'].'/'.$budget_log['bottom_layer']['blayer'],
                'hq_approve_date' => $hq_approve_date,
                'hq_approver' => array_values($hq_approve_data[$head_id])[0],
                'ba_approve_date' => $ba_approve_date,
                'ba_approver' => $ba_app_user,
                'budget_start_yr' => $budget_years[0],
                'budget_end_yr' => $budget_years[1],
            );
        }
        $rowCount = sizeof($ba_list);
        $count = parent::getSuccessMsg('SS004', $rowCount);

        #get layer type name for header list
        $this->LayerType->virtualFields['layer_type_name'] = $language == 'eng' ? 'name_en' : 'name_jp';
        $layer_type = $this->LayerType->find('list',array(
            'fields' => array('type_order','layer_type_name'),
            'conditions' => array(
                'type_order' => array(SETTING::LAYER_SETTING['topLayer'],SETTING::LAYER_SETTING['middleLayer'],SETTING::LAYER_SETTING['bottomLayer']),
                'flag' => 1,
            ),
            'order' => 'type_order ASC'
        ));
        $usedName = array(
                'formName'     => '予算進捗報告',
                'term'         => '予算期間',
                'totalLines'   => '総行 :',
                'lines'        => '行',
                'headQuarter'  =>$layer_type[SETTING::LAYER_SETTING['topLayer']],
                'department'   =>$layer_type[SETTING::LAYER_SETTING['middleLayer']],
                'baName'       =>$layer_type[SETTING::LAYER_SETTING['bottomLayer']]."/".$layer_type[SETTING::LAYER_SETTING['bottomLayer']]." ".__('名'),
                'approveDate'  =>$layer_type[SETTING::LAYER_SETTING['bottomLayer']]." ".__('承認日'),
                'HQapproveDate'=>$layer_type[SETTING::LAYER_SETTING['topLayer']]." ".__('承認日'),
                'authorizer'   =>$layer_type[SETTING::LAYER_SETTING['bottomLayer']]." ".__('承認者'),
                'HQauthorizer' =>$layer_type[SETTING::LAYER_SETTING['topLayer']]." ".__('承認者'),
                'tradingPlan'  =>'取引計画',
                'manpowerPlan' =>'人員計画',
                'forecast'     =>'見込',
                'budget'       =>'予算',
                'planForm'     =>'計画',
                'f&bDifference'=>'見込対予算増減一覧',
            );
        if ($rowCount == 0) {
            $no_data = "no_data";
        } else {
            $no_data = "";
        }
        $restrict_hqs = Setting::TRADING_DISABLE_HQS;
        $this->set(compact('ba_list', 'rowCount', 'term', 'successMsg', 'usedName', 'no_data', 'count', 'restrict_hqs','layer_type'));
        $this->render('index');
    }

    /**
     * index method
     *
     * @author Aye Zar Ni Kyaw on 19/10/2020
     * @return void
     */
    public function DataApprove()
    {
        $Common = new CommonController;
        $term_id = $this->Session->read('TERM_ID');
        $head_dept_id = $this->request->data['head_dept_id'];

        #head_dept_name
        $head_dept_name = $this->HeadDepartmentModel->find('first', array(
                            'fields'     => array('head_dept_name'),
                            'conditions' => array('id' => $head_dept_id)));
        $head_name = $head_dept_name['HeadDepartmentModel']['head_dept_name'];

        $target_month 	= $this->Session->read('TARGETMONTH');
        $login_user 	= $this->Session->read('LOGIN_USER');

        #budget start and end
        $term_name = $this->Session->read('TERM_NAME');
        $terms = explode('~', $term_name);
        $start_year = $terms[0];
        $end_year = $terms[1];

        #get all BAs of current headquarters
        $relatedBAs = array_values($Common->getAllBAOfSameHQ($head_dept_id));

        #get admin level
        $level_2 	= AdminLevel::ACCOUNT_MANAGER;
        $level_4 	= AdminLevel::ACCOUNT_INCHARGE;
        $level_5 	= AdminLevel::BUSINESS_MANAGER;
        $level_6 	= AdminLevel::BUSINESS_ADMINISTRATIOR;
        $level_8 	= AdminLevel::DEPUTY_GENERAL_MANAGER;
        $level_9 	= AdminLevel::GENERAL_MANAGER;
        $level_10 	= AdminLevel::BUDGET_INCHARGE;
        $level_11 	= AdminLevel::BUDGET_MANAGER;

        #to mail level
        $to_level = array($level_5,$level_6,$level_8,$level_10,$level_11);
        $to_email = $Common->getEmailByBA($relatedBAs, $to_level);
        $to_level_finance = array($level_5,$level_8,$level_10);
        $finance_ba = Setting::FINANCE_DEPT;
        $to_email_finance = $Common->getEmailByBA($finance_ba, $to_level_finance);

        #to mail
        $toEmail = array_filter(array_merge($to_email, $to_email_finance));


        #cc mail level
        $cc_level = array($level_9);
        #cc mail
        $ccEmail = $Common->getEmailByBA($relatedBAs, $ccLevel);

        #bcc mail
        $bccEmail = array();

        # mail content
        $mail_template 			= 'common';
        $mail['subject']	 	= '【予算策定】'.$head_name.' 見込・予算シート承認完了通知';
        $mail['template_title'] = '各位';
        $mail['template_body'] 	= $head_name.'の'.$start_year.'年度見込～'
                                    .$end_year.'年度予算シートは本部長承認されました。
									<br/>
									下記リンクより内容をご確認ください。';
        #url
        $url = '/BrmBudgetProgressReport?term_id='.$term_id;

        if (!empty($toEmail)) {
            #go email send function
            $sentMail = parent::sendEmailP3($target_month, $login_user, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
            
            if ($sentMail['error'] == 1 && $sentMail['errormsg'] != "") {
                # invalid
                $errorMsg = parent::getErrorMsg('SE042');
                $this->Flash->set($errorMsg, array("key"=>"BudgetProgressReportError"));
                $this->redirect(array('action' => 'index'));
            } else {
                # valid
                #write bugetlog in approve flag
                $budgetLog = $this->BrmBudgetApprove->find('all', array(
                        'fields'     => array('id', 'flag'),
                        'conditions' => array('hlayer_code' => $head_dept_id,
                                              'brm_term_id'		 => $term_id,
                                              'dlayer_code'		=> 0,
                                              'layer_code'		=> 0
                                            )));
                if (sizeof($budgetLog) == 0) {
                    $newdata = true;
                    $budgetLog = $this->BrmBudgetApprove->find('all', array(
                        'fields'     => array('id', 'flag'),
                        'conditions' => array('hlayer_code' => $head_dept_id,
                                              'brm_term_id'		 => $term_id
                                            )));
                } else {
                    $newdata = false;
                }
                $flag = $budgetLog[0]['BrmBudgetApprove']['flag'];
                $loginId = $this->Session->read('LOGIN_ID');
                if ($newdata) {
                    $saveData[] = array(
                        'brm_term_id'		=> $term_id,
                        'hlayer_code'	=> $head_dept_id,
                        'dlayer_code'   => 0,
                        'layer_code'		=> 0,
                        'flag'			=> 2,
                        'created_by'	=> $loginId,
                        'created_date'	=> date("Y-m-d H:i:s"),
                        'updated_by'    => $loginId,
                        'updated_date'  => date("Y-m-d H:i:s")
                    );
                } else {
                    $saveData[] = array(
                        'id' => $budgetLog[0]['BrmBudgetApprove']['id'],
                        'brm_term_id'   => $term_id,
                        'hlayer_code'	=> $head_dept_id,
                        'dlayer_code'   => 0,
                        'layer_code'		=> 0,
                        'flag'			=> 2,
                        'created_date'	=> date("Y-m-d H:i:s"),
                        'updated_by'    => $loginId,
                        'updated_date'  => date("Y-m-d H:i:s")
                    );
                }
                    
                
            
                try {
                    $this->BrmBudgetApprove->begin();
                    $this->BrmBudgetApprove->saveAll($saveData);
                    $this->BrmBudgetApprove->commit();
                    $sucMsg1 = parent::getSuccessMsg('SS022', $head_name); #approve msg
                    $this->Flash->set($sucMsg1, array("key"=>"BudgetProgressReportSuccess"));
                    $sucMsg2 = parent::getSuccessMsg("SS018"); #email msg
                    $this->Flash->set($sucMsg2, array("key"=>"BudgetProgressReportSuccess"));
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                    $errorMsg = parent::getErrorMsg('SE003');
                    $this->Flash->set($errorMsg, array("key"=>"BudgetProgressReportError"));
                }
                $this->redirect(array('controller' => 'BrmBudgetProgressReport', 'action' => 'index'));
            }
        } else {
            $errorMsg = parent::getErrorMsg('SE097');
            $this->Flash->set($errorMsg, array("key"=>"BudgetProgressReportError"));
        }
        $this->redirect(array('controller' => 'BrmBudgetProgressReport', 'action' => 'index'));
    }
    /**
     * index method
     *
     * @author Aye Zar Ni Kyaw on 19/10/2020
     * @return void
     */
    public function DataApproveCancel()
    {
        $Common = new CommonController;
        $term_id = $this->Session->read('TERM_ID');
        $head_dept_id = $this->request->data['head_dept_id'];
        #head_dept_name
        $head_dept_name = $this->HeadDepartmentModel->find('first', array(
                            'fields'     => array('head_dept_name'),
                            'conditions' => array('id' => $head_dept_id)));
        $head_name = $head_dept_name['HeadDepartmentModel']['head_dept_name'];

        $target_month 	= $this->Session->read('TARGETMONTH');
        $login_user 	= $this->Session->read('LOGIN_USER');

        #deadline_date
        $dead_date = $this->Session->read('HEADQUARTER_DEADLINE');
            
        $day = date("w", strtotime($dead_date));
        $dys = array("日","月","火","水","木","金","土");
        $deadline_date = date("n\月d\日（".$dys[$day]."曜日）", strtotime($dead_date));

        #budget start and end
        $term_name = $this->Session->read('TERM_NAME');
        $terms = explode('~', $term_name);
        $start_year = $terms[0];
        $end_year = $terms[1];

        #get all BAs of current headquarters
        $relatedBAs = array_values($Common->getAllBAOfSameHQ($head_dept_id));

        #get admin level
        $level_2 	= AdminLevel::ACCOUNT_MANAGER;
        $level_4 	= AdminLevel::ACCOUNT_INCHARGE;
        $level_5 	= AdminLevel::BUSINESS_MANAGER;
        $level_6 	= AdminLevel::BUSINESS_ADMINISTRATIOR;
        $level_8 	= AdminLevel::DEPUTY_GENERAL_MANAGER;
        $level_9 	= AdminLevel::GENERAL_MANAGER;
        $level_10 	= AdminLevel::BUDGET_INCHARGE;
        $level_11 	= AdminLevel::BUDGET_MANAGER;

        #to mail level
        $to_level = array($level_5,$level_6,$level_8,$level_10,$level_11);
        $to_email = $Common->getEmailByBA($relatedBAs, $to_level);
        $to_level_finance = array($level_5,$level_8,$level_10);
        $finance_ba = Setting::FINANCE_DEPT;
        $to_email_finance = $Common->getEmailByBA($finance_ba, $to_level_finance);

        #to mail
        $toEmail = array_filter(array_merge($to_email, $to_email_finance));


        #cc mail level
        $cc_level = array($level_9);
        #cc mail
        $ccEmail = $Common->getEmailByBA($relatedBAs, $ccLevel);

        #bcc mail
        $bccEmail = array();

        # mail content
        $mail_template 			= 'common';
        $mail['subject']	 	= '【予算策定】'.$head_name.' 見込・予算シート承認キャンセル通知';
        $mail['template_title'] = '各位';
        $mail['template_body'] 	= $head_name.'の'.$start_year.'年度見込～'
                                    .$end_year.'年度予算シートのデータを確認した結果、本部長承認がキャンセルされました。
									<br/>
									下記提出期日までに再度、承認予定です。
									<br/><br/>
									提出期日：'.$deadline_date.'営業時間内';
        #url
        $url = '/BrmBudgetProgressReport?term_id='.$term_id;

        if (!empty($toEmail)) {
            
            #go email send function
            $sentMail = parent::sendEmailP3($target_month, $login_user, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
            
            if ($sentMail['error'] == 1 && $sentMail['errormsg'] != "") {
                # invalid
                $errorMsg = parent::getErrorMsg('SE042');
                $this->Flash->set($errorMsg, array("key"=>"BudgetProgressReportError"));
                $this->redirect(array('action' => 'index'));
            } else {
                # valid
                #write bugetlog in approve cancel flag
                $budgetLog = $this->BrmBudgetApprove->find('all', array(
                    'fields'     => array('id', 'flag'),
                    'conditions' => array(
                                    'hlayer_code' 	=> $head_dept_id,
                                    'brm_term_id'   => $term_id,
                                    'dlayer_code'   => 0,
                                    'layer_code'    => 0
                                    )));
                $id = $budgetLog[0]['BrmBudgetApprove']['id'];
                $loginId = $this->Session->read('LOGIN_ID');
                $saveData = array(
                    'id' 			=> $id,
                    'flag' 			=> 1,
                    'updated_by'    => $loginId,
                    'updated_date'  => '0000-00-00 00:00:00'
                );
                $this->BrmBudgetApprove->save($saveData);
            
                #approve  cancel msg
                $sucMsg1 = parent::getSuccessMsg('SS023', $head_name);
                $this->Flash->set($sucMsg1, array("key"=>"BudgetProgressReportSuccess"));
                $sucMsg2 = parent::getSuccessMsg("SS018"); #email msg
                $this->Flash->set($sucMsg2, array("key"=>"BudgetProgressReportSuccess"));
            }
        } else {
            $errorMsg = parent::getErrorMsg('SE097');
            $this->Flash->set($errorMsg, array("key"=>"BudgetProgressReportError"));
        }

        $this->redirect(array('controller' => 'BrmBudgetProgressReport', 'action' => 'index'));
    }
}
