<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * Account Review Controller
 * @author - Aye Thandar Lwin
 */


class SapAccountReviewsController extends AppController
{
    public $uses = array('Sap','SapAccManagerApprove','SapAccSubmanagerComment',
            'SapBusiAdminComment','Layer',
            'SapBusiInchargeComment','SapBusiManagerApprove','SapAccInchargeComment');
    
    public $components = array('Session','Paginator','PhpExcel.PhpExcel');

    /**
     * Check Session before render page
     *
     */
    public function beforeFilter()
    {
        parent::checkSapUrlSession();
    }
    
   
    /**
     * Page Load Function
     * @author - Aye Thandar Lwin
     */

    public function index()
    {
        $this->layout = 'retentionclaimdebt';
        $errorMsg = "";
        $successMsg = "";
        $row_no_Msg = '';
        $row_succ_Msg = '';
        
        if ($this->Session->check('successMsg')) {
            $successMsg = $this->Session->read('successMsg');
            $this->Session->delete('successMsg');
        }
        
        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');

        /* get Base Date and DeadLine date (By Thura Moe) */
        $reference_date = '';
        $submission_deadline = '';
        $getDate = $this->getBaseAndDeadlineDate($period, $layer_code);
        if (!empty($getDate)) {
            $reference_date = $getDate['base_date'];
            $submission_deadline = $getDate['deadline_date'];
        }
        /* end */
        
        $Common = New CommonController();
        
        $flag_list  = Setting::ACCREV_FLAG;
      
        $data['role_id'] = $role_id;
        $data['period']         = $period;
        $data['layer_code']        = $layer_code;
        $data['page']           = 'SapAccountReviews';
        $data['flag_list']      = $flag_list;
        $data['modelName']      = 'Sap';

        $permissions = $this->Session->read('PERMISSIONS');
        unset($permissions['index']);
        foreach ($permissions as $action => $permission) {
            if((($layer_code == '' && $permission['limit']==0) || in_array($layer_code, array_keys($permission['layers'])))) {
                $checkButtonType[$action] = true;
            }
        }
        $condition = array();
        
        $condition["Sap.flag >"] = 1 ;
        
        if ($period != null || $period != '') {
            $condition["date_format(Sap.period,'%Y-%m')"] = $period;
        }
        if ($layer_code != null || $layer_code != '') {
            $condition["Sap.layer_code"] = $layer_code;
        }
        $conditions['Layer.from_date <='] = $period.'-01';
        $conditions['Layer.to_date >='] = $period.'-01';
        try {
            $this->paginate=array(
                'limit'=> Paging::TABLE_PAGING,
                'order' => array(
                        'Sap.id' => 'ASC',
                        'Sap.layer_code'=>'ASC',
                        'Sap.account_code' => 'ASC',
                        'Sap.destination_code' => 'ASC',
                        'Sap.logistic_index_no' => 'ASC',
                        'Sap.posting_date' => 'ASC',
                        'Sap.recorded_date' => 'ASC',
                        'Sap.receipt_shipment_date' => 'ASC',
                        'Sap.schedule_date' => 'ASC',
                        'Sap.id' => 'ASC',
                ),
                'group' => array( 'Sap.account_code',
                        'Sap.destination_code',
                        'Sap.logistic_index_no',
                        'Sap.posting_date',
                        'Sap.recorded_date',
                        'Sap.receipt_shipment_date',
                        'Sap.schedule_date',
                        'Sap.layer_code',
                        'Sap.currency'
                ),
                'conditions' =>$condition,
                'joins' => array(
                        array(
                                'alias' 	 => 'SapBusinessInchargeComment',
                                'table' 	 => 'sap_busi_incharge_comments',
                                'type' 		 => 'LEFT',
                                'conditions' => 'Sap.id = SapBusinessInchargeComment.sap_id AND SapBusinessInchargeComment.flag=1'
                        ),
                        array(
                                'alias' 	 => 'SapBusinessAdminComment',
                                'table' 	 => 'sap_busi_admin_comments',
                                'type' 		 => 'LEFT',
                                'conditions' => 'Sap.id = SapBusinessAdminComment.sap_id AND SapBusinessAdminComment.flag=1'
                        ),
                        array(
                                'alias' 	 => 'SapAccountInchargeComment',
                                'table' 	 => 'sap_acc_incharge_comments',
                                'type' 		 => 'LEFT',
                                'conditions' => 'Sap.id = SapAccountInchargeComment.sap_id AND SapAccountInchargeComment.flag=1'
                        ),
                        array(
                                'alias' 	 => 'SapAccSubManagerComment',
                                'table' 	 => 'sap_acc_submanager_comments',
                                'type' 		 => 'LEFT',
                                'conditions' => 'Sap.id = SapAccSubManagerComment.sap_id AND SapAccSubManagerComment.flag=1'
                        ),
                        array(
                                'alias' 	 => 'Layer',
                                'table' 	 => 'layers',   
                                'type' 		 => 'LEFT',
                                'conditions' => 'Layer.layer_code = Sap.layer_code AND Layer.flag=1'
                        )
                ),
                'fields'=> array(
                        'Sap.id',
                        'Sap.account_code',
                        'Sap.account_name',
                        'Sap.destination_code',
                        'Sap.destination_name',
                        'Sap.logistic_index_no',
                        'Sap.posting_date',
                        'Sap.recorded_date',
                        'Sap.receipt_shipment_date',
                        "DATE_FORMAT(Sap.schedule_date,'%Y-%m-%d') AS sap_schedule_date",
                        'Sap.numbers_day',
                        'Sap.currency',
                        'SUM(Sap.jp_amount) AS jp_amount',
                        'Sap.preview_comment',
                        'Sap.maturity_date',
                        'Sap.line_item_text',
                        'Sap.sale_representative',
                        'Sap.flag',
                        'SapBusinessInchargeComment.reason',
                        'SapBusinessInchargeComment.settlement_date',
                        'SapBusinessInchargeComment.remark',
                        'SapBusinessAdminComment.comment AS business_admin_comment',
                        'SapAccountInchargeComment.comment AS acc_incharge_comment',
                        'SapAccSubManagerComment.comment AS acc_submgr_comment')
        
            );
        
            $page = $this->Paginator->paginate('Sap');
            
            $check_confirm_master = 'true';
            
            $pg_count= count($page);
            
            for ($i = 0; $i< $pg_count;$i++) {
                $flg = $page[$i]['Sap']['flag'];
                $page[$i]['SapBusinessInchargeComment']['settlement_date'] = date('Y-m-d', strtotime($page[$i]['SapBusinessInchargeComment']['settlement_date']));
                if ($flg != 8) {
                    $check_confirm_master = 'false';
                    break;
                }
            }
            
            $count = $this->params['paging']['Sap']['count'];
            $pageno = $this->params['paging']['Sap']['page'];
            $this->Session->write('Page.pageCount', $pageno);
            $this->set('page', $this->paginate());
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
            $this->redirect(array("controller" => "SapAccountReviews",
               "action" => "index"));
        }
        
        if ($count == 0) {
            $row_no_Msg = parent::getErrorMsg('SE001');
            $this->set('row_no_Msg', $row_no_Msg);
            $this->set('row_succ_Msg', '');
        } else {
            $row_succ_Msg = parent::getSuccessMsg('SS004', $count);
            $this->set('row_succ_Msg', $row_succ_Msg);
            $this->set('row_no_Msg', "");
        }
        
        $flagArr = [];
        foreach ($this->paginate() as $key => $sap_value) {
            array_push($flagArr, $sap_value['Sap']['flag']);
        }
        
        $minFlag = min($flagArr);
        $show_btn = array();
       
        foreach($flag_list as $button => $flag){           
            $show[$button] = (in_array($minFlag,$flag))? '1': ''; 
            if(!array_diff_assoc($show,$checkButtonType)){              
                $show_btn[$button] = (in_array($minFlag,$flag))? '1': '';               
            }else{              
                $show_btn[$button] = '';
            }
            $show = array();
        }   

        $condi = array();
        $condi['Sap.flag >='] = '7';
        $condi["date_format(Sap.period,'%Y-%m')"] = $period;
        $condi['Sap.layer_code'] = $layer_code;
        $chk_count = $this->Sap->find('count', array(
            'conditions' =>$condi,
            'group' => array(
                'Sap.layer_code',
                'Sap.account_code',
                'Sap.destination_code',
                'Sap.logistic_index_no',
                'Sap.posting_date',
                'Sap.recorded_date',
                'Sap.schedule_date',
                'Sap.period'
            )
        ));
        
        $this->set('show_btn', $show_btn);
        $this->set('checkButtonType', $checkButtonType);
        $this->set('page', $page);
        $this->set('role_id', $role_id);
        $this->set('PERIOD', $period);
        $this->set('BA_CODE', $layer_code);
        $this->set('check_confirm_master', $check_confirm_master);
        $this->set('successMsg', $successMsg);
        $this->set('errorMsg', $errorMsg);
        $this->set('layer_name', $layer_name);
        $this->set('page_count', $count);
        $this->set('reference_date', $reference_date);
        $this->set('submission_deadline', $submission_deadline);
        $this->set('chk_count', $chk_count);
        $this->render('index');
    }
     /**
     * Get base_date and deadline_date based on period and layer_code
     * @author - Thura Moe
     * @return - array
     */
    public function getBaseAndDeadlineDate($period, $layer_code)
    {
        //get base_date, deadLine_date accourding to layer_code, period and max id
        $reference_date = '';
        $submission_deadline = '';

        $condi["date_format(Sap.period,'%Y-%m')"] = $period;
        if (!empty($layer_code)) {
            $condi["Sap.layer_code"] = $layer_code;
        }
        $toShowDate = $this->Sap->find('all', array(
            'conditions' => $condi,
            'fields' => array(
                'date_format(Sap.base_date,"%Y-%m-%d") as base_date',
                'date_format(Sap.deadline_date,"%Y-%m-%d") as deadline_date',
                'Sap.id'
            ),
            'order' => array('Sap.id DESC'),
            'limit' => 1
        ));
        if (!empty($toShowDate)) {
            foreach ($toShowDate as $date) {
                if (isset($date[0]['base_date']) && $date[0]['base_date'] != 0) {
                    $reference_date = $date[0]['base_date'];
                }
                if (isset($date[0]['deadline_date']) && $date[0]['deadline_date'] != 0) {
                    $submission_deadline = $date[0]['deadline_date'];
                }
            }
        }
        $result = array(
            'base_date' => $reference_date,
            'deadline_date' => $submission_deadline
        );
        return $result;
    }
    
    
    /**
     * Save Account Sub Manager Comment
     * @author - Aye Thandar Lwin
     */
    
    public function saveSapAccountReviews()
    {      
        $acc_mgr_comment = $this->request->data('json_data');
        
        $save_acc_mgr_cmt = json_decode($acc_mgr_comment, true);
        $user_id = $this->Session->read('LOGIN_ID');
        
        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');//added by Hein Htet Ko
        $login_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $pageno = $this->Session->read('Page.pageCount');
        $success = 0;
        $success_comment = 0;
        $date = date('Y-m-d H:i:s');
        $datas = array();
        $MgrCommentArr = array();
      
        try {
            $sapDB = $this->Sap->getDataSource();
            $BicmDB = $this->SapBusiInchargeComment->getDataSource();
            $sapDB->begin();
            $BicmDB->begin();
        
            /* Modify By Thura Moe */
            $get_sap_id = array_column($save_acc_mgr_cmt, '0');
            $findByFlag8 = $this->Sap->find('all', array(
                'conditions' => array(
                    'flag' => 8,
                    'id IN' => $get_sap_id
                ),
                'fields' => array('id', 'layer_code', 'flag')
            ));
            
            if (!empty($findByFlag8)) {
                # if user submit array count is not same with $findByFlag8 array
                # it means some of the data is not yet approved and can save
                # remove data from $save_acc_mgr_cmt that is approved(flag 8)
                $tmp_find = array_column($findByFlag8, 'Sap');
                $flag8_id = array_column($tmp_find, 'id');
                $cnt_flag8_id = count($flag8_id);
                
                for ($i=0; $i<$cnt_flag8_id; $i++) {
                    $id_flag_8 = $flag8_id[$i];//get id
                    for ($j=0; $j<count($save_acc_mgr_cmt); $j++) {
                        $user_submit_id = $save_acc_mgr_cmt[$j]['0'];
                        if ($id_flag_8 == $user_submit_id) {
                            unset($save_acc_mgr_cmt[$j]);
                            $save_acc_mgr_cmt = array_values($save_acc_mgr_cmt);
                        }
                    }
                }
            }
            /* end */
            $cnt_save_acc_mgr_cmt = count($save_acc_mgr_cmt);
            if ($cnt_save_acc_mgr_cmt == 0) {
                $errorMsg = parent::getErrorMsg('SE033');
                $this->Flash->set($errorMsg, array('key'=>'save_fail'));
                $data = array(
                        'content' => '',
                        'invalid' => "",
                        'error'   => $errorMsg
                );
                 
                $this->redirect(array('controller'=>'SapAccountReviews','action'=>'index'));
            } else {
                //data for saving Account Manager Comment and update flag 7
            
                for ($i =0; $i < $cnt_save_acc_mgr_cmt; $i++) {
                    $form_sap_id = $save_acc_mgr_cmt[$i][0];
                    $mgr_comment = trim($save_acc_mgr_cmt[$i][1]);
                    $chk_status = $save_acc_mgr_cmt[$i][2];

                    $same_merge_sapId = $this->Sap->getMatchFlag($form_sap_id);

                    $cnt_merge_id = count($same_merge_sapId);
                    for ($j =0; $j < $cnt_merge_id; $j++) {
                        $sap_id = $same_merge_sapId[$j]['saps']['id'];
                        if ($chk_status == 'true') {
                            $this->Sap->Update_Sap_flag($sap_id);
                            $update_flag = $this->Sap->getAffectedRows();
                            if (count($update_flag) > 0) {
                                $success = 1;
                                //break;
                            }
                        } else {
                            $this->Sap->Update_Uncheck_flag($sap_id, $user_id);
                            $update_flag = $this->Sap->getAffectedRows();
                            if (count($update_flag) > 0) {
                                $success = 1;
                                //break;
                            }
                        }
                            
                        $chk_result = $this->SapAccSubmanagerComment->find(
                            'all',
                            array(
                                    'conditions' => array(
                                        'sap_id' => $sap_id
                                    )
                                )
                        );
                        //get flag of sap_id from tbl_m_sap
                        $get_flag = $this->Sap->find('first', array(
                            'conditions' => array(
                                'id' => $sap_id
                            ),
                            'fields' => array('flag')
                        ));
                        if (!empty($get_flag)) {
                            $sap_id_flag = $get_flag['Sap']['flag'];
                        } else {
                            $sap_id_flag = '';
                        }
                            
                        if (count($chk_result) <= 0) {
                            // modify by Thura Moe
                            // save comment if flag is 6 or 7 in tbl_m_sap
                            if ($sap_id_flag == 6 || $sap_id_flag == 7) {
                                if ($mgr_comment != '') {
                                    $this->SapAccSubmanagerComment->Save_Acc_submanagerComment($sap_id, $mgr_comment, $user_id);
                                }
                            }
                        } else {
                            // modify by Thura Moe
                            // update comment if flag is 6 or 7 in tbl_m_sap
                            if ($sap_id_flag == 6 || $sap_id_flag == 7) {
                                $this->SapAccSubmanagerComment->update_Acc_submanagerComment($sap_id, $mgr_comment);
                                $update_cmt_flag = $this->SapAccSubmanagerComment->getAffectedRows();
                                if (count($update_cmt_flag) > 0) {
                                    $success_comment = 1;
                                    //break;
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $sapDB->rollback();
            $BicmDB->rollback();

            $errorMsg = parent::getErrorMsg('SE003');
            $this->Flash->set($errorMsg, array('key'=>'acc_review_del_fail'));
           
            $this->redirect(array('controller'=>'SapAccountReviews','action'=>'index'));
        }
        #Edited by Kaung Zaw Thant for sending mail
        #to check all check-box is check or uncheck
        $con = array();
        $con['Sap.flag >='] = '2';
        $con["date_format(Sap.period,'%Y-%m')"] = $period;
        $con['id'] = $get_sap_id;
        //fetch ba-code and account-name in no choice ba condition
        $findByAllBa = $this->Sap->find('all', array(
                'conditions' => $con,
                'fields' => array('id', 'layer_code', 'flag'),
                'group' => array('layer_code'),
                'fields' => array('layer_code', 'account_name')
            ));
        //each ba-code send mail in no choice ba condition
        if ($success == 1 || $success_comment == 1) {
            foreach ($findByAllBa as $value) {
                $layer_code = $value['Sap']['layer_code'];
                //$layer_name = $value['Sap']['account_name'];
                //made a comment by Hein Htet Ko

                //get sap data flag > 1 according period and ba-code
                $condition = array();
                $condition['Sap.flag != '] = '0';
                $condition['Sap.flag > '] = '1';
                $condition["date_format(Sap.period,'%Y-%m')"] = $period;
                $condition["Sap.layer_code"] = $layer_code;
                    
                $count = $this->Sap->find('all', array(
                            'conditions' =>$condition,
                            'group' => array(
                                    'Sap.layer_code',
                                    'Sap.account_code',
                                    'Sap.destination_code',
                                    'Sap.logistic_index_no',
                                    'Sap.posting_date',
                                    'Sap.recorded_date',
                                    'Sap.schedule_date',
                                    'Sap.period'
                                    )
                        
                                ));
                $countSapAddComments = count($count);
                //only get sap data flag = 7
                $condi = array();
                $condi['Sap.flag >='] = '7';
                $condi["date_format(Sap.period,'%Y-%m')"] = $period;
                $condi['Sap.layer_code'] = $layer_code;
                $chkSapAddComments = $this->Sap->find('all', array(
                                    'conditions' =>$condi,
                                    'group' => array(
                                            'Sap.layer_code',
                                            'Sap.account_code',
                                            'Sap.destination_code',
                                            'Sap.logistic_index_no',
                                            'Sap.posting_date',
                                            'Sap.recorded_date',
                                            'Sap.schedule_date',
                                            'Sap.period'
                                        )
                                    ));

                $countChkSapAddComments = count($chkSapAddComments);
                //check all check-box is checked
                if ($countSapAddComments == $countChkSapAddComments) {
                   
                    if($_POST['mailSend']) {

                        $mail_template = 'common';
                        $toEmail  = parent::formatMailInput($_POST['toEmail']);
                        $ccEmail  = parent::formatMailInput($_POST['ccEmail']);
                        $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                        
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_body']  = $_POST['mailBody'];
                        $url = '/SapAccountReviews?'.'param='.$period.'&ba='.$layer_code;                          
                        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                       
                        if ($sentMail["error"]) {
                            $sapDB->rollback();
                            $BicmDB->rollback();
                            $msg = $sentMail["errormsg"];
                            $this->Flash->set($msg, array('key'=>'acc_review_del_fail'));
                            $invalid_email = parent::getErrorMsg('SE042');
                        } else {
                            $sapDB->commit();
                            $BicmDB->commit();

                            CakeLog::write('debug', ' Save is successfully with layer_name = '.$layer_name.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                            $successMsg = parent::getSuccessMsg('SS001');
                            $this->Session->write('successMsg', $successMsg);

                            $successMsg = parent::getSuccessMsg("SS018");
                            $this->Flash->set($successMsg, array('key'=>'save_success'));
                        }
                    }else{
                        $sapDB->commit();
                        $BicmDB->commit();
                        $successMsg = parent::getSuccessMsg('SS001');
                        $this->Session->write('successMsg', $successMsg);
                    }                         
                } else {
                    $sapDB->commit();
                    $BicmDB->commit();
                    $successMsg = parent::getSuccessMsg('SS001');
                    $this->Session->write('successMsg', $successMsg);
                }
            }
        } else {
            $successMsg = parent::getErrorMsg('SE034');
            $this->Flash->set($successMsg, array('key'=>'acc_review_del_fail'));
        }
        
        $data = array(
                'content' => $successMsg,
                'invalid' => "",
                'error'   => ""
        );
        
        if($pageno > 1) {
            $this->redirect(array('controller'=>'SapAccountReviews', 'action'=>'index', 'page' => $pageno));
        }else {
            $this->redirect(array('controller'=>'SapAccountReviews', 'action'=>'index'));
        }        
    }
    
    /**
     * Approve SAP Data
     * @author - Aye Thandar Lwin
     */
    
    public function ApproveSapAccountReviews()
    {
        $login_id = $this->Session->read('LOGIN_ID');
        
        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $pageno = $this->Session->read('Page.pageCount');
        $flag = 7;
        $update_count = '';
        $search_result = $this->Sap->Search_SAP_Data($period, $layer_code, $flag);
        
        $date = date('Y-m-d');
        $datas = array();
        $MgrApprovetArr = array();
        $this->SapAccManagerApprove->create();

        //data for saving Account Manager Approve
        for ($i =0; $i < count($search_result);$i++) {
            $sap_id = $search_result[$i]['saps']['id'];
            $MgrApprovetArr = array( 'sap_id' => $sap_id,
                    'approve_date'=>$date,
                    'flag' =>1,
                    'created_by' => $login_id,
                    'updated_by' => $login_id
            );
    
            array_push($datas, $MgrApprovetArr);
            $this->Sap->Update_AccManager_Approve($sap_id, $login_id);
            $update_count = $this->Sap->getAffectedRows();
        }
        $save_data = $this->SapAccManagerApprove->saveAll($datas);
       
        if ($update_count > 0) {

            if($_POST['mailSend']) {

                $mail_template = 'common';
                $toEmail  = parent::formatMailInput($_POST['toEmail']);
                $ccEmail  = parent::formatMailInput($_POST['ccEmail']);
                $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                
                $mail['subject']        = $_POST['mailSubj'];
                $mail['template_body']  = $_POST['mailBody'];
                $url = '/SapAccountReviews?'.'param='.$period.'&ba='.$layer_code;                          
                $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
               
                if ($sentMail["error"]) {
                    $this->SapAccManagerApprove->rollback();
                    $msg = $sentMail["errormsg"];
                    $this->Flash->set($msg, array('key'=>'approve_update_fail'));
                    $invalid_email = parent::getErrorMsg('SE042');
                    CakeLog::write('debug', ' Approve is mail sending erro with layer_name = '.$layer_name.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                } else {
                    $this->SapAccManagerApprove->commit();
        
                    CakeLog::write('debug', ' Approve is successfully with layer_name = '.$layer_name.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());


                    $successMsg = parent::getSuccessMsg("SS018");
                    $this->Flash->set($successMsg, array('key'=>'save_success'));

                    $successMsg = parent::getSuccessMsg('SS005');
                    $this->Session->write('successMsg', $successMsg);
                }
            }else{
                $this->SapAccManagerApprove->commit();
                $successMsg = parent::getSuccessMsg("SS005");
                $this->Flash->set($successMsg, array('key'=>'save_success'));
            }  

        } else {
            $errorMsg = parent::getErrorMsg('SE033');
            $this->Flash->set($errorMsg, array('key'=>'approve_update_fail'));
        }
        
        if($pageno > 1) {
            $this->redirect(array('controller'=>'SapAccountReviews', 'action'=>'index', 'page' => $pageno));
        }else {
            $this->redirect(array('controller'=>'SapAccountReviews', 'action'=>'index'));
        }
        
    }
    /**
     * Approve Cancel SAP Data
     * @author - Aye Thandar Lwin
     */
    
    public function ApproveCancelSapAccountReviews()
    {
        $Common = new CommonController();
    
        $login_id = $this->Session->read('LOGIN_ID');
        
        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $flag = 8;
        $pageno = $this->Session->read('Page.pageCount');
        $search_result = $this->Sap->Search_SAP_Data($period, $layer_code, $flag); 

        //data for saving Account Manager Approve
        for ($i =0; $i < count($search_result);$i++) {
            $sap_id = $search_result[$i]['saps']['id'];
            
            $this->Sap->AccManager_Approve_Cancel($sap_id, $login_id);
            $this->SapAccManagerApprove->AccMgrArrpoveCancel($sap_id, $login_id);
        }

        if($_POST['mailSend']) {

            $mail_template = 'common';
            $toEmail  = parent::formatMailInput($_POST['toEmail']);
            $ccEmail  = parent::formatMailInput($_POST['ccEmail']);
            $bccEmail = parent::formatMailInput($_POST['bccEmail']);
            
            $mail['subject']        = $_POST['mailSubj'];
            $mail['template_body']  = $_POST['mailBody'];
            $url = '/SapAccountReviews?'.'param='.$period.'&ba='.$layer_code;                          
            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
           
            if ($sentMail["error"]) {
                $this->Flash->set($msg, array('key'=>'approve_update_fail'));
                $invalid_email = parent::getErrorMsg('SE042');
                $this->Flash->set($invalid_email, array('key'=>'approve_update_fail'));

            } else {
                CakeLog::write('debug', ' Approve Cancel is successfully with layer_name = '.$layer_name.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                $successMsg = parent::getSuccessMsg("SS018");
                $this->Flash->set($successMsg, array('key'=>'save_success'));

                $successMsg = parent::getSuccessMsg('SS006');
                $this->Session->write('successMsg', $successMsg);
                // $this->redirect(array('controller'=>'SapAccountReviews','action'=>'index'));
            }
        }else{
            $successMsg = parent::getSuccessMsg("SS006");
            $this->Flash->set($successMsg, array('key'=>'save_success'));
        }
        if($pageno > 1) {
            $this->redirect(array('controller'=>'SapAccountReviews', 'action'=>'index', 'page' => $pageno));
        }else {
            $this->redirect(array('controller'=>'SapAccountReviews', 'action'=>'index'));
        }
    }
    //added by Hein Htet Ko
    public function RejectAccountReveiw()
    {
        $login_id = $this->Session->read('LOGIN_ID');
        
        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $flag = 7;

        $search_result = $this->Sap->Search_SAP_Data($period, $layer_code, $flag);
        //data for saving Account Manager Approve
        for ($i =0; $i < count($search_result);$i++) {
            $sap_id = $search_result[$i]['saps']['id'];
                
            $this->Sap->AccManager_Approve_Cancel($sap_id, $login_id);
            $this->SapAccManagerApprove->AccMgrArrpoveCancel($sap_id, $login_id);
        }
            
        if($_POST['mailSend']) {

            $mail_template = 'common';
            $toEmail  = parent::formatMailInput($_POST['toEmail']);
            $ccEmail  = parent::formatMailInput($_POST['ccEmail']);
            $bccEmail = parent::formatMailInput($_POST['bccEmail']);
            
            $mail['subject']        = $_POST['mailSubj'];
            $mail['template_body']  = $_POST['mailBody'];
            $url = '/SapAccountReviews?'.'param='.$period.'&ba='.$layer_code;                          
            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
           
            if ($sentMail["error"]) {
                $this->Flash->set($msg, array('key'=>'approve_update_fail'));
                $invalid_email = parent::getErrorMsg('SE042');
                $this->Flash->set($invalid_email, array('key'=>'approve_update_fail'));

            } else {
                CakeLog::write('debug', ' Reject is successfully with layer_name = '.$layer_name.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $successMsg = parent::getSuccessMsg("SS018");
                $this->Flash->set($successMsg, array('key'=>'save_success'));

                $successMsg = parent::getSuccessMsg('SS014');
                $this->Session->write('successMsg', $successMsg);
                // $this->redirect(array('controller'=>'SapAccountReviews','action'=>'index'));
            }
        }else{
            $successMsg = parent::getSuccessMsg("SS014");
            $this->Flash->set($successMsg, array('key'=>'save_success'));
        }
        $pageno = $this->Session->read('Page.pageCount');
        if($pageno > 1) {
            $this->redirect(array('controller'=>'SapAccountReviews', 'action'=>'index', 'page' => $pageno));
        }else {
            $this->redirect(array('controller'=>'SapAccountReviews', 'action'=>'index'));
        }
        
       
    }
    
    
    /**
     * Account Review Excel Download
     * @author - Aye Thandar Lwin
     */
    public function Download_Account_Review()
    {
        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        

        /* get Base Date and DeadLine date (By Thura Moe) */
        $reference_date = '';
        $submission_deadline = '';
        $getDate = $this->getBaseAndDeadlineDate($period, $layer_code);
        if (!empty($getDate)) {
            $reference_date = $getDate['base_date'];
            $submission_deadline = $getDate['deadline_date'];
        }
        /* end */
        
        $objPHPExcel = $this->PhpExcel->createWorksheet()->setDefaultFont('Cambria', 12);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3);
        // $objPHPExcel ->getActiveSheet()->getPageSetup()->setFitToWidth ( 1 );
        $objPHPExcel ->getActiveSheet()->setShowGridlines(true);
        $objPHPExcel ->setActiveSheetIndex(0);
    
        $objPHPExcel->getActiveSheet()->setTitle('SapAccountReviews');
    
        $sheet = $this->PhpExcel->getActiveSheet();
    
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
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
    
                ),
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                )
        );
        $alignright = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                ),
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                )
        );
        
        $objPHPExcel->getActiveSheet()->getStyle('A:X')->getAlignment()->setWrapText(true);
        
        $sheet->getStyle('A9:X9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->getStyle('L10:X10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        
        $objPHPExcel->getActiveSheet()->getStyle('A:X')->getAlignment()->setWrapText(true);
                
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension("D")->setWidth(15);
        $sheet->getColumnDimension("E")->setWidth(15);
        $sheet->getColumnDimension("F")->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(20);
        $sheet->getColumnDimension('N')->setWidth(20);
        $sheet->getColumnDimension('P')->setWidth(20);
        $sheet->getColumnDimension('Q')->setWidth(20);
        $sheet->getColumnDimension('R')->setWidth(20);
        $sheet->getColumnDimension('T')->setWidth(20);
        $sheet->getColumnDimension('V')->setWidth(20);
        $sheet->getColumnDimension('X')->setWidth(20);
        
        $excel_result = $this->Sap->Account_Review_Excel($layer_code, $period);
        
        if (!empty($excel_result)) {
            
            // Excel  Title(Account Review)
            $sheet->setCellValue('A1', __("経理レビュー"));
            $sheet->mergeCells('A1:V1');
            $sheet->getStyle('A1:V1')->applyFromArray($aligncenter);
            
            //write layer_code,layer_name,period,etc..
            $sheet->setCellValue('A2', __("部署"));
            $sheet->getStyle('A2')->applyFromArray($alignleft);
            $sheet->getStyle('A2')->applyFromArray($border_dash);
            $sheet->setCellValue('A3', __("部署名"));
            $sheet->getStyle('A3')->applyFromArray($alignleft);
            $sheet->getStyle('A3')->applyFromArray($border_dash);
            $sheet->setCellValue('A4', __("対象月"));
            $sheet->getStyle('A4')->applyFromArray($alignleft);
            $sheet->getStyle('A4')->applyFromArray($border_dash);
            $sheet->setCellValue('A5', __("基準年月日"));
            $sheet->getStyle('A5')->applyFromArray($alignleft);
            $sheet->getStyle('A5')->applyFromArray($border_dash);
            $sheet->setCellValue('A6', __("提出期日"));
            $sheet->getStyle('A6')->applyFromArray($alignleft);
            $sheet->getStyle('A6')->applyFromArray($border_dash);
            
            $sheet->setCellValue('B2', $layer_code);
            $sheet->getStyle('B2')->applyFromArray($alignleft);
            $sheet->getStyle('B2')->applyFromArray($border_dash);
            $sheet->setCellValue('B3', $layer_name);
            $sheet->getStyle('B3')->applyFromArray($alignleft);
            $sheet->getStyle('B3')->applyFromArray($border_dash);
            $sheet->setCellValue('B4', $period);
            $sheet->getStyle('B4')->applyFromArray($alignleft);
            $sheet->getStyle('B4')->applyFromArray($border_dash);
            $sheet->setCellValue('B5', $reference_date);
            $sheet->getStyle('B5')->applyFromArray($alignleft);
            $sheet->getStyle('B5')->applyFromArray($border_dash);
            $sheet->setCellValue('B6', $submission_deadline);
            $sheet->getStyle('B6')->applyFromArray($alignleft);
            $sheet->getStyle('B6')->applyFromArray($border_dash);
            
            //write table header
            $sheet->setCellValue('A9', __("勘定コード名"));
            $sheet->mergeCells('A9:A10');
            $sheet->getStyle('A9:A10')->applyFromArray($aligncenter);
            $sheet->getStyle('A9:A10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('B9', __("相手先コード"));
            $sheet->mergeCells('B9:B10');
            $sheet->getStyle('B9:B10')->applyFromArray($aligncenter);
            $sheet->getStyle('B9:B10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('C9', __("相手先名"));
            $sheet->mergeCells('C9:C10');
            $sheet->getStyle('C9:C10')->applyFromArray($aligncenter);
            $sheet->getStyle('C9:C10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('D9', __("物流Index No."));
            $sheet->mergeCells('D9:D10');
            $sheet->getStyle('D9:D10')->applyFromArray($aligncenter);
            $sheet->getStyle('D9:D10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('E9', __("転記日付"));
            $sheet->mergeCells('E9:E10');
            $sheet->getStyle('E9:E10')->applyFromArray($aligncenter);
            $sheet->getStyle('E9:E10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('F9', __("計上基準日"));
            $sheet->mergeCells('F9:F10');
            $sheet->getStyle('F9:F10')->applyFromArray($aligncenter);
            $sheet->getStyle('F9:F10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('G9', __("入出荷年月日"));
            $sheet->mergeCells('G9:G10');
            $sheet->getStyle('G9:G10')->applyFromArray($aligncenter);
            $sheet->getStyle('G9:G10')->applyFromArray($border_dash);

            $sheet->setCellValue('H9', __("決済予定日"));
            $sheet->mergeCells('H9:H10');
            $sheet->getStyle('H9:H10')->applyFromArray($aligncenter);
            $sheet->getStyle('H9:H10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('I9', __("滞留日数"));
            $sheet->mergeCells('I9:I10');
            $sheet->getStyle('I9:I10')->applyFromArray($aligncenter);
            $sheet->getStyle('I9:I10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('J9', __("満期年月日"));
            $sheet->mergeCells('J9:J10');
            $sheet->getStyle('J9:J10')->applyFromArray($aligncenter);
            $sheet->getStyle('J9:J10')->applyFromArray($border_dash);

            $sheet->setCellValue('K9', __("明細テキスト"));
            $sheet->mergeCells('K9:K10');
            $sheet->getStyle('K9:K10')->applyFromArray($aligncenter);
            $sheet->getStyle('K9:K10')->applyFromArray($border_dash);

            $sheet->setCellValue('L9', __("営業担当者"));
            $sheet->mergeCells('L9:L10');
            $sheet->getStyle('L9:L10')->applyFromArray($aligncenter);
            $sheet->getStyle('L9:L10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('M9', __("円貨金額"));
            $sheet->mergeCells('M9:M10');
            $sheet->getStyle('M9:M10')->applyFromArray($aligncenter);
            $sheet->getStyle('M9:M10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('N9', __("経理コメント"));
            $sheet->mergeCells('N9:N10');
            $sheet->getStyle('N9:N10')->applyFromArray($aligncenter);
            $sheet->getStyle('N9:N10')->applyFromArray($border_dash);
            
            // 担当者コメント入力欄
            $sheet->setCellValue('O9', __("担当者コメント入力欄"));
            $sheet->mergeCells('O9:R9');
            $sheet->getStyle('O9:R9')->applyFromArray($aligncenter);
            $sheet->getStyle('O9:R9')->applyFromArray($border_dash);
            
            $sheet->setCellValue('O10', __("確認完了"));
            $sheet->getStyle('O10')->applyFromArray($aligncenter);
            $sheet->getStyle('O10')->applyFromArray($border_dash);
            $sheet->setCellValue('P10', __("滞留理由"));
            $sheet->getStyle('P10')->applyFromArray($aligncenter);
            $sheet->getStyle('P10')->applyFromArray($border_dash);
            $sheet->setCellValue('Q10', __("決済日"));
            $sheet->getStyle('Q10')->applyFromArray($aligncenter);
            $sheet->getStyle('Q10')->applyFromArray($border_dash);
            $sheet->setCellValue('R10', __("備考"));
            $sheet->getStyle('R10')->applyFromArray($aligncenter);
            $sheet->getStyle('R10')->applyFromArray($border_dash);
            
            //管理職
            $sheet->setCellValue('S9', __("管理職"));
            $sheet->mergeCells('S9:T9');
            $sheet->getStyle('S9:T9')->applyFromArray($aligncenter);
            $sheet->getStyle('S9:T9')->applyFromArray($border_dash);
            $sheet->setCellValue('S10', __("確認済"));
            $sheet->getStyle('S10')->applyFromArray($aligncenter);
            $sheet->getStyle('S10')->applyFromArray($border_dash);
            $sheet->setCellValue('T10', __("コメント入力欄"));
            $sheet->getStyle('T10')->applyFromArray($aligncenter);
            $sheet->getStyle('T10')->applyFromArray($border_dash);
            
            //経理担当者
            $sheet->setCellValue('U9', __("経理担当者"));
            $sheet->mergeCells('U9:V9');
            $sheet->getStyle('U9:V9')->applyFromArray($aligncenter);
            $sheet->getStyle('U9:V9')->applyFromArray($border_dash);
            $sheet->setCellValue('U10', __("確認欄"));
            $sheet->getStyle('U10')->applyFromArray($aligncenter);
            $sheet->getStyle('U10')->applyFromArray($border_dash);
            $sheet->setCellValue('V10', __("コメント入力欄"));
            $sheet->getStyle('V10')->applyFromArray($aligncenter);
            $sheet->getStyle('V10')->applyFromArray($border_dash);
            
            $objPHPExcel->getActiveSheet()->getStyle('L9:L10')->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('M9:M10')->getAlignment()->setWrapText(true);
            //経理管理者
            $sheet->setCellValue('W9', __("経理管理者"));
            $sheet->mergeCells('W9:X9');
            $sheet->getStyle('W9:X9')->applyFromArray($aligncenter);
            $sheet->getStyle('W9:X9')->applyFromArray($border_dash);
            $sheet->setCellValue('W10', __("確認欄"));
            $sheet->getStyle('W10')->applyFromArray($aligncenter);
            $sheet->getStyle('W10')->applyFromArray($border_dash);
            $sheet->setCellValue('X10', __("コメント入力欄"));
            $sheet->getStyle('X10')->applyFromArray($aligncenter);
            $sheet->getStyle('X10')->applyFromArray($border_dash);
            
            
            //start write data //
            $a = 11;
            $count = 0;
            $prev_dest_code = '';
            $prev_index_no = '';
            $prev_account_code = '';
            $prev_flag = '';
            $flag_chk = '';
            $prev_posting_date = '';
            $prev_schedule_date = '';
            $prev_recorded_date = '';
            
            
            foreach ($excel_result as $result) {
                $account_code = $result['account_review_excel']['account_code'];
                $account_name = $result['account_review_excel']['account_name'];
                $destination_code = $result['account_review_excel']['destination_code'];
                $destination_name = $result['account_review_excel']['destination_name'];
                $logistic_index_no = $result['account_review_excel']['logistic_index_no'];

                $posting_date = $result['account_review_excel']['posting_date'];
                $posting_date = ($posting_date == '' || $posting_date == '0000-00-00' || $posting_date == '0000-00-00 00:00:00' || $posting_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($posting_date));

                $recorded_date = $result['account_review_excel']['recorded_date'];
                $recorded_date = ($recorded_date == '' || $recorded_date == '0000-00-00' || $recorded_date == '0000-00-00 00:00:00' || $recorded_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($recorded_date));

                $numbers_day = $result['account_review_excel']['numbers_day'];
                $currency =$result['account_review_excel']['currency'] ;
                $jp_amount = $result['0']['jp_amount'];
                $preview_comment = $result['account_review_excel']['preview_comment'];
                $sale_representative = $result['account_review_excel']['sale_representative'];
                $flag = $result['account_review_excel']['flag'];

                $receipt_date = date('Y-m-d', strtotime($result['account_review_excel']['receipt_shipment_date']));
                $receipt_date = ($receipt_date == '' || $receipt_date == '0000-00-00' || $receipt_date == '0000-00-00 00:00:00' || $receipt_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($receipt_date));

                $sap_schedule_date = date('Y-m-d', strtotime($result['account_review_excel']['schedule_date']));
                $sap_schedule_date = ($sap_schedule_date == '' || $sap_schedule_date == '0000-00-00' || $sap_schedule_date == '0000-00-00 00:00:00' || $sap_schedule_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($sap_schedule_date));

                $ba_inc_reason = $result['account_review_excel']['reason'];

                $settlement_date = $result['account_review_excel']['settlement_date'];
                $settlement_date = ($settlement_date == '' || $settlement_date == '0000-00-00' || $settlement_date == '0000-00-00 00:00:00' || $settlement_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($settlement_date));
                
                $remark = $result['account_review_excel']['remark'];
                $business_admin_comment = $result['account_review_excel']['business_admin_comment'];
                $acc_incharge_comment = $result['account_review_excel']['acc_incharge_comment'];
                $acc_submgr_comment = $result['account_review_excel']['acc_submgr_comment'];
                
                // Calulating the difference in timestamps
                $diff = strtotime($reference_date) - strtotime($sap_schedule_date);
                // 24 * 60 * 60 = 86400 seconds
                $NoOfDays = round($diff/86400);//abs(round($diff / 86400));
                $maturity_date = $result['account_review_excel']['maturity_date'];
                $maturity_date = ($maturity_date == '' || $maturity_date == '0000-00-00' || $maturity_date == '0000-00-00 00:00:00' || $maturity_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($maturity_date));
                $line_item_text = $result['account_review_excel']['line_item_text'];
                # checkbox conditions
                if ($flag >= 3) {
                    $chk_busi_inc_confirm = 1;
                } else {
                    $chk_busi_inc_confirm = 0;
                }
                if ($flag >= 4) {
                    $chk_busi_admin_confirm = 1;
                } else {
                    $chk_busi_admin_confirm = 0;
                }
                if ($flag >= 6) {
                    $chk_acc_inc_confirm = 1;
                } else {
                    $chk_acc_inc_confirm = 0;
                }

                if ($flag ==6 || $flag == 7 || $flag == 8) {
                    $flag_chk = 'white';
                } else {
                    $flag_chk = 'gray';
                }
                if ($flag == 6 || $flag == 7 || $flag == 8) {
                    $sheet->getStyle('A'.$a .':'.'X'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
                } else {
                    $sheet->getStyle('A'.$a .':'.'X'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                }
                $sheet->getStyle('M'.$a)->getNumberFormat()->setFormatCode('#,##0');
                
                /* set value */
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$a, $account_name);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$a, $destination_code);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$a, $destination_name);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$a, $logistic_index_no);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$a, $posting_date);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$a, $recorded_date);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$a, $receipt_date);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$a, $sap_schedule_date);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$a, $NoOfDays);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$a, $maturity_date);
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$a, $line_item_text);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$a, $sale_representative);
                $objPHPExcel->getActiveSheet()->setCellValue('M'.$a, $jp_amount);
                $objPHPExcel->getActiveSheet()->setCellValue('N'.$a, $preview_comment);
                $objPHPExcel->getActiveSheet()->setCellValue('O'.$a, $chk_busi_inc_confirm);
                $objPHPExcel->getActiveSheet()->setCellValue('P'.$a, $ba_inc_reason);
                $objPHPExcel->getActiveSheet()->setCellValue('Q'.$a, $settlement_date);
                $objPHPExcel->getActiveSheet()->setCellValue('R'.$a, $remark);
                $objPHPExcel->getActiveSheet()->setCellValue('S'.$a, $chk_busi_admin_confirm);
                $objPHPExcel->getActiveSheet()->setCellValue('T'.$a, $business_admin_comment);
                $objPHPExcel->getActiveSheet()->setCellValue('U'.$a, $chk_acc_inc_confirm);
                $objPHPExcel->getActiveSheet()->setCellValue('V'.$a, $acc_incharge_comment);
                if ($acc_submgr_comment != '' && $flag == 6) {
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$a, 0);
                } elseif ($flag > 6) {
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$a, 1);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('W'.$a, 0);
                }
                $objPHPExcel->getActiveSheet()->setCellValue('X'.$a, $acc_submgr_comment);
                /* end */
                /* border */
                $objPHPExcel->getActiveSheet()->getStyle('A'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('D'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('H'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('I'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('J'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('K'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('L'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('M'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('N'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('O'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('P'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('Q'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('R'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('S'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('T'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('U'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('V'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('W'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('X'.$a)->applyFromArray($border_dash);
                /* end */
                /* align */
                $objPHPExcel->getActiveSheet()->getStyle('A'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('D'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('H'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('I'.$a)->applyFromArray($alignright);
                $objPHPExcel->getActiveSheet()->getStyle('J'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('K'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('L'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('M'.$a)->applyFromArray($alignright);
                $objPHPExcel->getActiveSheet()->getStyle('N'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('O'.$a)->applyFromArray($alignright);
                $objPHPExcel->getActiveSheet()->getStyle('P'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('Q'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('R'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('S'.$a)->applyFromArray($alignright);
                $objPHPExcel->getActiveSheet()->getStyle('T'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('U'.$a)->applyFromArray($alignright);
                $objPHPExcel->getActiveSheet()->getStyle('V'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('W'.$a)->applyFromArray($alignright);
                $objPHPExcel->getActiveSheet()->getStyle('X'.$a)->applyFromArray($alignleft);
                /* end */
                $a++;
            }
            //END ROW MERGE CELL
            
            $this->PhpExcel->output("SapAccountReviews".".xlsx");
            $this->autoLayout = false;
            $this->render('index');
        } else {
            $param = array( __("export"));
            $msg = parent::getErrorMsg("SE017", $param);
            $this->Session->write('EXCEL_ERR_MSG', $msg);
            $this->redirect(array('controller'=>'SapAccountReviews','action'=>'index'));
        }
    }
    
}
