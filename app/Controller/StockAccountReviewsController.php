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


class StockAccountReviewsController extends AppController
{
    public $uses = array('Stock','StockAccManagerApprove','StockAccSubmanagerComment',
            'StockBusiAdminComment','Layer',
            'StockBusiInchargeComment','StockBusiManagerApprove','StockAccInchargeComment');
    
    public $components = array('Session','Paginator','PhpExcel.PhpExcel');

    /**
     * Check Session before render page
     *
     */
    public function beforeFilter()
    {
        parent::checkStockUrlSession();
    }
    
   
    /**
     * Page Load Function
     * @author - Aye Thandar Lwin
     */

    public function index()
    {
        $this->layout = 'stocks';
        $errorMsg = '';
        $successMsg = '';
        $row_no_Msg = '';
        $row_succ_Msg = '';
        
        if ($this->Session->check('successMsg')) {
            $successMsg = $this->Session->read('successMsg');
            $this->Session->delete('successMsg');
        }
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        
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
        $data['page']           = 'StockAccountReviews';
        $data['flag_list']      = $flag_list;
        $data['modelName']      = 'Stock';
        
        $permissions = $this->Session->read('PERMISSIONS');
        unset($permissions['index']);
        foreach ($permissions as $action => $permission) {
            if((($layer_code == '' && $permission['limit']==0) || in_array($layer_code, array_keys($permission['layers'])))) {
                $checkButtonType[$action] = true;
            }
        }
        $conditions = array();
        
        $conditions['Stock.flag >'] = 1 ;
       
        
        if ($period == null || $period == '') {
            $conditions["date_format(Stock.period,'%Y-%m')"] = $period;
        }else{
            $conditions["Stock.period"] = $period.'-01';
        }

        if ($layer_code != null || $layer_code != '') {
            $conditions['Stock.layer_code'] = $layer_code;
        }
        #to hide approave and reject
        $save_mode_on = in_array(6, $this->Stock->find('list', array(
            'fields' => array('flag'),
            'conditions' =>$conditions,
            'group' => array( 
                'Stock.destination_name',
                'Stock.layer_code',
            ),
        )))?? 0;
        $conditions['Layer.from_date <='] = $period.'-01';
        $conditions['Layer.to_date >='] = $period.'-01';
        try {
            $this->paginate=array(
                'limit'=> Paging::TABLE_PAGING,
                'order' => array(
                        'Stock.id' => 'ASC',
                        'Stock.layer_code'=>'ASC',
                        'Stock.destination_name' => 'ASC'
                ),
                'group' => array( 
                        'Stock.destination_name',
                        'Stock.layer_code',
                ),
                'conditions' =>$conditions,
                'joins' => array(
                        array(
                                'alias' 	 => 'StockBusinessInchargeComment',
                                'table' 	 => 'stock_busi_incharge_comments',
                                'type' 		 => 'LEFT',
                                'conditions' => 'Stock.id = StockBusinessInchargeComment.stock_id AND StockBusinessInchargeComment.flag=1'
                        ),
                        array(
                                'alias' 	 => 'StockBusinessAdminComment',
                                'table' 	 => 'stock_busi_admin_comments',
                                'type' 		 => 'LEFT',
                                'conditions' => 'Stock.id = StockBusinessAdminComment.stock_id AND StockBusinessAdminComment.flag=1'
                        ),
                        array(
                                'alias' 	 => 'StockAccountInchargeComment',
                                'table' 	 => 'stock_acc_incharge_comments',
                                'type' 		 => 'LEFT',
                                'conditions' => 'Stock.id = StockAccountInchargeComment.stock_id AND StockAccountInchargeComment.flag=1'
                        ),
                        array(
                                'alias' 	 => 'StockAccSubManagerComment',
                                'table' 	 => 'stock_acc_submanager_comments',
                                'type' 		 => 'LEFT',
                                'conditions' => 'Stock.id = StockAccSubManagerComment.stock_id AND StockAccSubManagerComment.flag=1'
                        ),
                        array(
                                'alias' 	 => 'Layer',
                                'table' 	 => 'layers',   
                                'type' 		 => 'LEFT',
                                'conditions' => 'Layer.layer_code = Stock.layer_code AND Layer.flag=1'
                        )
                ),
                'fields'=> array(
                        'Stock.id',
                        'Stock.period',
                        'Stock.layer_code',
                        'Stock.destination_name',
                        'Stock.item_code',
                        'Stock.item_name',
                        'Stock.item_name_2',
                        'Stock.unit',
                        'Stock.registration_date',
                        'Stock.numbers_day',
                        'Stock.receipt_index_no',
                        'Stock.quantity',
                        'SUM(Stock.amount) AS amount',
                        'Stock.is_error',
                        'Stock.is_sold',
                        'Stock.is_contract',
                        // 'Stock.reason',
                        // 'Stock.solution',
                        'Stock.preview_comment',
                        'Stock.flag',
                        'StockBusinessInchargeComment.reason',
                        'StockBusinessInchargeComment.settlement_date',
                        'StockBusinessInchargeComment.remark',
                        'StockBusinessAdminComment.comment AS business_admin_comment',
                        'StockAccountInchargeComment.comment AS acc_incharge_comment',
                        'StockAccSubManagerComment.comment AS acc_submgr_comment'
                        )
        
            );
            
            $page = $this->Paginator->paginate('Stock');
            
            $check_confirm_master = 'true';
            
            $pg_count= count($page);
            
            
            for ($i = 0; $i< $pg_count;$i++) {
                $flg = $page[$i]['Stock']['flag'];
                $page[$i]['StockBusinessInchargeComment']['settlement_date'] = date('Y-m-d', strtotime($page[$i]['StockBusinessInchargeComment']['settlement_date']));
                if ($flg != 8) {
                    $check_confirm_master = 'false';
                    break;
                }
            }
            
            $count = $this->params['paging']['Stock']['count'];
            $pageno = $this->params['paging']['Stock']['page'];
            $this->Session->write('Page.pageCount', $pageno);
            $this->set('page', $this->paginate());
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'StockAccountReviews',
               'action' => 'index'));
        }
        
        if ($count == 0) {
            $row_no_Msg = parent::getErrorMsg('SE001');
            $this->set('row_no_Msg', $row_no_Msg);
            $this->set('row_succ_Msg', '');
        } else {
            $row_succ_Msg = parent::getSuccessMsg('SS004', $count);
            $this->set('row_succ_Msg', $row_succ_Msg);
            $this->set('row_no_Msg', '');
        }
        
        $flagArr = [];
        foreach ($this->paginate() as $key => $sap_value) {
            array_push($flagArr, $sap_value['Stock']['flag']);
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
        $condi['Stock.flag >='] = '7';
        $condi["date_format(Stock.period,'%Y-%m')"] = $period;
        $condi['Stock.layer_code'] = $layer_code;
        $chk_count = $this->Stock->find('count', array(
            'conditions' =>$condi,
            'group' => array(
                'Stock.layer_code',
                'Stock.destination_name',
                'Stock.period'
            )
        ));
        
        $this->set('show_btn', $show_btn);
        $this->set('save_mode_on', $save_mode_on);
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

        $condi["date_format(Stock.period,'%Y-%m')"] = $period;
        if (!empty($layer_code)) {
            $condi['Stock.layer_code'] = $layer_code;
        }
        $toShowDate = $this->Stock->find('all', array(
            'conditions' => $condi,
            'fields' => array(
                "date_format(Stock.base_date,'%Y-%m-%d') as base_date",
                "date_format(Stock.deadline_date,'%Y-%m-%d') as deadline_date",
                'Stock.id'
            ),
            'order' => array('Stock.id DESC'),
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
    
    public function saveStockAccountReviews()
    {
        $acc_mgr_comment = $this->request->data('json_data');
        
        $save_acc_mgr_cmt = json_decode($acc_mgr_comment, true);
        $user_id = $this->Session->read('LOGIN_ID');
        
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');//added by Hein Htet Ko
        $login_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $pageno = $this->Session->read('Page.pageCount');
        $success = 0;
        $success_comment = 0;
        $date = date('Y-m-d H:i:s');
        $datas = array();
        $MgrCommentArr = array();
        
        try {
            $stockDB = $this->Stock->getDataSource();
            $BicmDB = $this->StockBusiInchargeComment->getDataSource();
            $stockDB->begin();
            $BicmDB->begin();
            
            /* Modify By Thura Moe */
            $get_stock_id = array_column($save_acc_mgr_cmt, '0');
            $findByFlag8 = $this->Stock->find('all', array(
                'conditions' => array(
                    'flag' => 8,
                    'id IN' => $get_stock_id
                ),
                'fields' => array('id', 'layer_code', 'flag')
            ));
            
            if (!empty($findByFlag8)) {
                # if user submit array count is not same with $findByFlag8 array
                # it means some of the data is not yet approved and can save
                # remove data from $save_acc_mgr_cmt that is approved(flag 8)
                $tmp_find = array_column($findByFlag8, 'Stock');
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
                        'invalid' => '',
                        'error'   => $errorMsg
                );
                 
                $this->redirect(array('controller'=>'StockAccountReviews','action'=>'index'));
            } else {
                //data for saving Account Manager Comment and update flag 7
            
                for ($i =0; $i < $cnt_save_acc_mgr_cmt; $i++) {
                    $form_stock_id = $save_acc_mgr_cmt[$i][0];
                    $mgr_comment = trim($save_acc_mgr_cmt[$i][1]);
                    $chk_status = $save_acc_mgr_cmt[$i][2];
                    

                    $same_merge_stockId = $this->Stock->getMatchFlag($form_stock_id);
                    
                    $cnt_merge_id = count($same_merge_stockId);
                    for ($j =0; $j < $cnt_merge_id; $j++) {
                        $stock_id = $same_merge_stockId[$j]['stocks']['id'];
                        if ($chk_status == 'true') {
                            $this->Stock->Update_Stock_flag($stock_id);
                            $update_flag = $this->Stock->getAffectedRows();
                            if (count($update_flag) > 0) {
                                $success = 1;
                                //break;
                            }
                        } else {
                            $this->Stock->Update_Uncheck_flag($stock_id, $user_id);
                            $update_flag = $this->Stock->getAffectedRows();
                            if (count($update_flag) > 0) {
                                $success = 1;
                                //break;
                            }
                        }
                            
                        $chk_result = $this->StockAccSubmanagerComment->find(
                            'all',
                            array(
                                    'conditions' => array(
                                        'stock_id' => $stock_id
                                    )
                                )
                        );
                        //get flag of stock_id from tbl_m_sap
                        $get_flag = $this->Stock->find('first', array(
                            'conditions' => array(
                                'id' => $stock_id
                            ),
                            'fields' => array('flag')
                        ));
                        if (!empty($get_flag)) {
                            $stock_id_flag = $get_flag['Stock']['flag'];
                        } else {
                            $stock_id_flag = '';
                        }
                            
                        if (count($chk_result) <= 0) {
                            // modify by Thura Moe
                            // save comment if flag is 6 or 7 in stocks
                            if ($stock_id_flag == 6 || $stock_id_flag == 7) {
                                if ($mgr_comment != '') {
                                    $this->StockAccSubmanagerComment->Save_Acc_submanagerComment($stock_id, $mgr_comment, $user_id);
                                }
                            }
                        } else {
                            // modify by Thura Moe
                            // update comment if flag is 6 or 7 in stocks
                            if ($stock_id_flag == 6 || $stock_id_flag == 7) {
                                $this->StockAccSubmanagerComment->update_Acc_submanagerComment($stock_id, $mgr_comment);
                                $update_cmt_flag = $this->StockAccSubmanagerComment->getAffectedRows();
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

            $stockDB->rollback();
            $BicmDB->rollback();

            $errorMsg = parent::getErrorMsg('SE003');
            $this->Flash->set($errorMsg, array('key'=>'acc_review_del_fail'));
           
            $this->redirect(array('controller'=>'StockAccountReviews','action'=>'index'));
        }
        #Edited by Kaung Zaw Thant for sending mail
        #to check all check-box is check or uncheck
        $con = array();
        $con['Stock.flag >='] = '2';
        $con["date_format(Stock.period,'%Y-%m')"] = $period;
        $con['id'] = $get_stock_id;
        //fetch ba-code and account-name in no choice ba condition
        $findByAllBa = $this->Stock->find('all', array(
                'conditions' => $con,
                'fields' => array('id', 'layer_code', 'flag'),
                'group' => array('layer_code'),
                // 'fields' => array('layer_code', 'account_name')
                'fields' => array('layer_code')
            ));
        //each ba-code send mail in no choice ba condition
        if ($success == 1 || $success_comment == 1) {
            foreach ($findByAllBa as $value) {
                $layer_code = $value['Stock']['layer_code'];
                //$layer_name = $value['Stock']['account_name'];
                //made a comment by Hein Htet Ko

                //get stock data flag > 1 according period and ba-code
                $condition = array();
                $condition['Stock.flag != '] = '0';
                $condition['Stock.flag > '] = '1';
                $condition["date_format(Stock.period,'%Y-%m')"] = $period;
                $condition['Stock.layer_code'] = $layer_code;
                    
                $count = $this->Stock->find('all', array(
                            'conditions' =>$condition,
                            'group' => array(
                                    'Stock.layer_code',
                                    // 'Stock.account_code',
                                    'Stock.destination_name',
                                    // 'Stock.logistic_index_no',
                                    // 'Stock.posting_date',
                                    // 'Stock.recorded_date',
                                    // 'Stock.schedule_date',
                                    'Stock.period'
                                    )
                        
                                ));
                $countStockAddComments = count($count);
                //only get stock data flag = 7
                $condi = array();
                $condi['Stock.flag >='] = '7';
                $condi["date_format(Stock.period,'%Y-%m')"] = $period;
                $condi['Stock.layer_code'] = $layer_code;
                $chkStockAddComments = $this->Stock->find('all', array(
                                    'conditions' =>$condi,
                                    'group' => array(
                                            'Stock.layer_code',
                                            // 'Stock.account_code',
                                            'Stock.destination_name',
                                            // 'Stock.logistic_index_no',
                                            // 'Stock.posting_date',
                                            // 'Stock.recorded_date',
                                            // 'Stock.schedule_date',
                                            'Stock.period'
                                        )
                                    ));

                $countChkStockAddComments = count($chkStockAddComments);
                //check all check-box is checked
                if ($countStockAddComments == $countChkStockAddComments) {
                   
                    if($_POST['mailSend']) {

                        $mail_template = 'common';
                        $toEmail  = parent::formatMailInput($_POST['toEmail']);
                        $ccEmail  = parent::formatMailInput($_POST['ccEmail']);
                        $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                        
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_body']  = $_POST['mailBody'];
                        $url = '/StockAccountReviews?'.'param='.$period.'&ba='.$layer_code;                          
                        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                       
                        if ($sentMail['error']) {
                            $stockDB->rollback();
                            $BicmDB->rollback();
                            $msg = $sentMail['errormsg'];
                            $this->Flash->set($msg, array('key'=>'acc_review_del_fail'));
                            $invalid_email = parent::getErrorMsg('SE042');
                        } else {
                            $stockDB->commit();
                            $BicmDB->commit();

                            CakeLog::write('debug', ' Save is successfully with layer_name = '.$layer_name.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                            $successMsg = parent::getSuccessMsg('SS001');
                            $this->Session->write('successMsg', $successMsg);

                            $successMsg = parent::getSuccessMsg('SS018');
                            $this->Flash->set($successMsg, array('key'=>'save_success'));
                        }
                    }else{
                        $stockDB->commit();
                        $BicmDB->commit();
                        $successMsg = parent::getSuccessMsg('SS001');
                        $this->Session->write('successMsg', $successMsg);
                    }                         
                } else {
                    $stockDB->commit();
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
                'invalid' => '',
                'error'   => ''
        );
        
        if($pageno > 1) {
            $this->redirect(array('controller'=>'StockAccountReviews', 'action'=>'index', 'page' => $pageno));
        }else {
            $this->redirect(array('controller'=>'StockAccountReviews', 'action'=>'index'));
        }        
    }
    
    /**
     * Approve SAP Data
     * @author - Aye Thandar Lwin
     */
    
    public function ApproveStockAccountReviews()
    {
        $login_id = $this->Session->read('LOGIN_ID');
        
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $pageno = $this->Session->read('Page.pageCount');
        $flag = 7;
        $update_count = '';
        $search_result = $this->Stock->Search_Stock_Data($period, $layer_code, $flag);
        
        $date = date('Y-m-d');
        $datas = array();
        $MgrApprovetArr = array();
        $this->StockAccManagerApprove->create();

        //data for saving Account Manager Approve
        for ($i =0; $i < count($search_result);$i++) {
            $stock_id = $search_result[$i]['stocks']['id'];
            $MgrApprovetArr = array( 'stock_id' => $stock_id,
                    'approve_date'=>$date,
                    'flag' =>1,
                    'created_by' => $login_id,
                    'updated_by' => $login_id
            );
    
            array_push($datas, $MgrApprovetArr);
            $this->Stock->Update_AccManager_Approve($stock_id, $login_id);
            $update_count = $this->Stock->getAffectedRows();
        }
        $save_data = $this->StockAccManagerApprove->saveAll($datas);
       
        if ($update_count > 0) {

            if($_POST['mailSend']) {

                $mail_template = 'common';
                $toEmail  = parent::formatMailInput($_POST['toEmail']);
                $ccEmail  = parent::formatMailInput($_POST['ccEmail']);
                $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                
                $mail['subject']        = $_POST['mailSubj'];
                $mail['template_body']  = $_POST['mailBody'];
                $url = '/StockAccountReviews?'.'param='.$period.'&ba='.$layer_code;                          
                $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
               
                if ($sentMail['error']) {
                    $this->StockAccManagerApprove->rollback();
                    $msg = $sentMail['errormsg'];
                    $this->Flash->set($msg, array('key'=>'approve_update_fail'));
                    $invalid_email = parent::getErrorMsg('SE042');
                    CakeLog::write('debug', ' Approve is mail sending erro with layer_name = '.$layer_name.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                } else {
                    $this->StockAccManagerApprove->commit();
        
                    CakeLog::write('debug', ' Approve is successfully with layer_name = '.$layer_name.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());


                    $successMsg = parent::getSuccessMsg('SS018');
                    $this->Flash->set($successMsg, array('key'=>'save_success'));

                    $successMsg = parent::getSuccessMsg('SS005');
                    $this->Session->write('successMsg', $successMsg);
                }
            }else{
                $this->StockAccManagerApprove->commit();
                $successMsg = parent::getSuccessMsg('SS005');
                $this->Flash->set($successMsg, array('key'=>'save_success'));
            }  

        } else {
            $errorMsg = parent::getErrorMsg('SE033');
            $this->Flash->set($errorMsg, array('key'=>'approve_update_fail'));
        }
        
        if($pageno > 1) {
            $this->redirect(array('controller'=>'StockAccountReviews', 'action'=>'index', 'page' => $pageno));
        }else {
            $this->redirect(array('controller'=>'StockAccountReviews', 'action'=>'index'));
        }
        
    }
    /**
     * Approve Cancel SAP Data
     * @author - Aye Thandar Lwin
     */
    
    public function ApproveCancelStockAccountReviews()
    {
        $Common = new CommonController();
    
        $login_id = $this->Session->read('LOGIN_ID');
        
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $flag = 8;
        $pageno = $this->Session->read('Page.pageCount');
        $search_result = $this->Stock->Search_Stock_Data($period, $layer_code, $flag); 

        //data for saving Account Manager Approve
        for ($i =0; $i < count($search_result);$i++) {
            $stock_id = $search_result[$i]['stocks']['id'];
            
            $this->Stock->AccManager_Approve_Cancel($stock_id, $login_id);
            $this->StockAccManagerApprove->AccMgrArrpoveCancel($stock_id, $login_id);
        }

        if($_POST['mailSend']) {

            $mail_template = 'common';
            $toEmail  = parent::formatMailInput($_POST['toEmail']);
            $ccEmail  = parent::formatMailInput($_POST['ccEmail']);
            $bccEmail = parent::formatMailInput($_POST['bccEmail']);
            
            $mail['subject']        = $_POST['mailSubj'];
            $mail['template_body']  = $_POST['mailBody'];
            $url = '/StockAccountReviews?'.'param='.$period.'&ba='.$layer_code;                          
            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
           
            if ($sentMail['error']) {
                $this->Flash->set($msg, array('key'=>'approve_update_fail'));
                $invalid_email = parent::getErrorMsg('SE042');
                $this->Flash->set($invalid_email, array('key'=>'approve_update_fail'));

            } else {
                CakeLog::write('debug', ' Approve Cancel is successfully with layer_name = '.$layer_name.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                $successMsg = parent::getSuccessMsg('SS018');
                $this->Flash->set($successMsg, array('key'=>'save_success'));

                $successMsg = parent::getSuccessMsg('SS006');
                $this->Session->write('successMsg', $successMsg);
                // $this->redirect(array('controller'=>'StockAccountReviews','action'=>'index'));
            }
        }else{
            $successMsg = parent::getSuccessMsg('SS006');
            $this->Flash->set($successMsg, array('key'=>'save_success'));
        }
        if($pageno > 1) {
            $this->redirect(array('controller'=>'StockAccountReviews', 'action'=>'index', 'page' => $pageno));
        }else {
            $this->redirect(array('controller'=>'StockAccountReviews', 'action'=>'index'));
        }
    }
    //added by Hein Htet Ko
    public function RejectAccountReveiw()
    {
        $login_id = $this->Session->read('LOGIN_ID');
        
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $flag = 7;

        $search_result = $this->Stock->Search_Stock_Data($period, $layer_code, $flag);
        //data for saving Account Manager Approve
        for ($i =0; $i < count($search_result);$i++) {
            $stock_id = $search_result[$i]['stocks']['id'];
                
            $this->Stock->AccManager_Approve_Cancel($stock_id, $login_id);
            $this->StockAccManagerApprove->AccMgrArrpoveCancel($stock_id, $login_id);
        }
            
        if($_POST['mailSend']) {

            $mail_template = 'common';
            $toEmail  = parent::formatMailInput($_POST['toEmail']);
            $ccEmail  = parent::formatMailInput($_POST['ccEmail']);
            $bccEmail = parent::formatMailInput($_POST['bccEmail']);
            
            $mail['subject']        = $_POST['mailSubj'];
            $mail['template_body']  = $_POST['mailBody'];
            $url = '/StockAccountReviews?'.'param='.$period.'&ba='.$layer_code;                          
            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
           
            if ($sentMail['error']) {
                $this->Flash->set($msg, array('key'=>'approve_update_fail'));
                $invalid_email = parent::getErrorMsg('SE042');
                $this->Flash->set($invalid_email, array('key'=>'approve_update_fail'));

            } else {
                CakeLog::write('debug', ' Reject is successfully with layer_name = '.$layer_name.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $successMsg = parent::getSuccessMsg('SS018');
                $this->Flash->set($successMsg, array('key'=>'save_success'));

                $successMsg = parent::getSuccessMsg('SS014');
                $this->Session->write('successMsg', $successMsg);
                // $this->redirect(array('controller'=>'StockAccountReviews','action'=>'index'));
            }
        }else{
            $successMsg = parent::getSuccessMsg('SS014');
            $this->Flash->set($successMsg, array('key'=>'save_success'));
        }
        $pageno = $this->Session->read('Page.pageCount');
        if($pageno > 1) {
            $this->redirect(array('controller'=>'StockAccountReviews', 'action'=>'index', 'page' => $pageno));
        }else {
            $this->redirect(array('controller'=>'StockAccountReviews', 'action'=>'index'));
        }
        
       
    }
    
    
    /**
     * Account Review Excel Download
     * @author - Aye Thandar Lwin
     */
    public function Download_Account_Review()
    {
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        // $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        

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
    
        $objPHPExcel->getActiveSheet()->setTitle('StockAccountReviews');
    
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
        
        // $objPHPExcel->getActiveSheet()->getStyle('A:H')->getAlignment()->setWrapText(true);
        
        $sheet->getStyle('A9:Z10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        
        // $objPHPExcel->getActiveSheet()->getStyle('A:Z')->getAlignment()->setWrapText(true);
                
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(15);
        $sheet->getColumnDimension('M')->setWidth(15);
        $sheet->getColumnDimension('N')->setWidth(15);
        $sheet->getColumnDimension('O')->setWidth(15);
        $sheet->getColumnDimension('P')->setWidth(15);
        $sheet->getColumnDimension('Q')->setWidth(15);
        $sheet->getColumnDimension('R')->setWidth(15);
        $sheet->getColumnDimension('S')->setWidth(15);
        $sheet->getColumnDimension('T')->setWidth(15);
        $sheet->getColumnDimension('U')->setWidth(15);
        $sheet->getColumnDimension('V')->setWidth(15);
        $sheet->getColumnDimension('W')->setWidth(15);
        $sheet->getColumnDimension('X')->setWidth(15);
        $sheet->getColumnDimension('Y')->setWidth(15);
        $sheet->getColumnDimension('Z')->setWidth(15);
        
        $excel_result = $this->Stock->Account_Review_Excel($layer_code, $period);
        
        if (!empty($excel_result)) {
            
            // Excel  Title(Account Review)
            $sheet->setCellValue('A1', __('経理レビュー'));
            $sheet->mergeCells('A1:U1');
            $sheet->getStyle('A1:Z1')->applyFromArray($aligncenter);

            $sheet->getColumnDimension('A')->setVisible(false);
            $sheet->getColumnDimension('B')->setVisible(false);
            
            //write layer_code,layer_name,period,etc..
            $sheet->setCellValue('C2', __('部署'));
            $sheet->getStyle('C2')->applyFromArray($alignleft);
            $sheet->getStyle('C2')->applyFromArray($border_dash);
            $sheet->setCellValue('C3', __('部署名'));
            $sheet->getStyle('C3')->applyFromArray($alignleft);
            $sheet->getStyle('C3')->applyFromArray($border_dash);
            $sheet->setCellValue('C4', __('対象月'));
            $sheet->getStyle('C4')->applyFromArray($alignleft);
            $sheet->getStyle('C4')->applyFromArray($border_dash);
            $sheet->setCellValue('C5', __('基準年月日'));
            $sheet->getStyle('C5')->applyFromArray($alignleft);
            $sheet->getStyle('C5')->applyFromArray($border_dash);
            $sheet->setCellValue('C6', __('提出期日'));
            $sheet->getStyle('C6')->applyFromArray($alignleft);
            $sheet->getStyle('C6')->applyFromArray($border_dash);            
            
            $sheet->setCellValue('D2', $layer_code);
            $sheet->getStyle('D2')->applyFromArray($alignleft);
            $sheet->getStyle('D2')->applyFromArray($border_dash);
            $sheet->setCellValue('D3', $layer_name);
            $sheet->getStyle('D3')->applyFromArray($alignleft);
            $sheet->getStyle('D3')->applyFromArray($border_dash);
            $sheet->setCellValue('D4', $period);
            $sheet->getStyle('D4')->applyFromArray($alignleft);
            $sheet->getStyle('D4')->applyFromArray($border_dash);
            $sheet->setCellValue('D5', $reference_date);
            $sheet->getStyle('D5')->applyFromArray($alignleft);
            $sheet->getStyle('D5')->applyFromArray($border_dash);
            $sheet->setCellValue('D6', $submission_deadline);
            $sheet->getStyle('D6')->applyFromArray($alignleft);
            $sheet->getStyle('D6')->applyFromArray($border_dash);
            
            //write table header
            $sheet->setCellValue('A9', __('部署コード'));
            $sheet->mergeCells('A9:A10');
            $sheet->getStyle('A9:A10')->applyFromArray($aligncenter);
            $sheet->getStyle('A9:A10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('B9', __('Period'));
            $sheet->mergeCells('B9:B10');
            $sheet->getStyle('B9:B10')->applyFromArray($aligncenter);
            $sheet->getStyle('B9:B10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('C9', __('保管場所'));
            $sheet->mergeCells('C9:C10');
            $sheet->getStyle('C9:C10')->applyFromArray($aligncenter);
            $sheet->getStyle('C9:C10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('D9', __('品目コード'));
            $sheet->mergeCells('D9:D10');
            $sheet->getStyle('D9:D10')->applyFromArray($aligncenter);
            $sheet->getStyle('D9:D10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('E9', __('品目テキスト'));
            $sheet->mergeCells('E9:E10');
            $sheet->getStyle('E9:E10')->applyFromArray($aligncenter);
            $sheet->getStyle('E9:E10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('F9', __('品目名2'));
            $sheet->mergeCells('F9:F10');
            $sheet->getStyle('F9:F10')->applyFromArray($aligncenter);
            $sheet->getStyle('F9:F10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('G9', __('単位'));
            $sheet->mergeCells('G9:G10');
            $sheet->getStyle('G9:G10')->applyFromArray($aligncenter);
            $sheet->getStyle('G9:G10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('H9', __('入庫日'));
            $sheet->mergeCells('H9:H10');
            $sheet->getStyle('H9:H10')->applyFromArray($aligncenter);
            $sheet->getStyle('H9:H10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('I9', __('滞留日数'));
            $sheet->mergeCells('I9:I10');
            $sheet->getStyle('I9:I10')->applyFromArray($aligncenter);
            $sheet->getStyle('I9:I10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('J9', __('レシートインデックス番号'));
            $sheet->mergeCells('J9:J10');
            $sheet->getStyle('J9:J10')->applyFromArray($aligncenter);
            $sheet->getStyle('J9:J10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('K9', __('数量'));
            $sheet->mergeCells('K9:K10');
            $sheet->getStyle('K9:K10')->applyFromArray($aligncenter);
            $sheet->getStyle('K9:K10')->applyFromArray($border_dash);

            $sheet->setCellValue('L9', __('金額'));
            $sheet->mergeCells('L9:L10');
            $sheet->getStyle('L9:L10')->applyFromArray($aligncenter);
            $sheet->getStyle('L9:L10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('M9', __('不完全品 有・無'));
            $sheet->mergeCells('M9:M10');
            $sheet->getStyle('M9:M10')->applyFromArray($aligncenter);
            $sheet->getStyle('M9:M10')->applyFromArray($border_dash);

            $sheet->setCellValue('N9', __('売り繋ぎ 済・未済'));
            $sheet->mergeCells('N9:N10');
            $sheet->getStyle('N9:N10')->applyFromArray($aligncenter);
            $sheet->getStyle('N9:N10')->applyFromArray($border_dash);

            $sheet->setCellValue('O9', __('契約 有・無'));
            $sheet->mergeCells('O9:O10');
            $sheet->getStyle('O9:O10')->applyFromArray($aligncenter);
            $sheet->getStyle('O9:O10')->applyFromArray($border_dash);

            $sheet->setCellValue('P9', __('プレビューコメント'));
            $sheet->mergeCells('P9:P10');
            $sheet->getStyle('P9:P10')->applyFromArray($aligncenter);
            $sheet->getStyle('P9:P10')->applyFromArray($border_dash);

            // $sheet->setCellValue('Q9', __('解決'));
            // $sheet->mergeCells('Q9:Q10');
            // $sheet->getStyle('Q9:Q10')->applyFromArray($aligncenter);
            // $sheet->getStyle('Q9:Q10')->applyFromArray($border_dash);

            
            $sheet->setCellValue('Q9', __('担当者コメント入力欄'));
            $sheet->setCellValue('Q10', __('確認完了'));
            $sheet->setCellValue('R10', __('滞留理由'));
            $sheet->setCellValue('S10', __('決済日'));
            $sheet->setCellValue('T10', __('備考'));
            $sheet->mergeCells('Q9:T9');
            $sheet->getStyle('Q9:T10')->applyFromArray($aligncenter);
            $sheet->getStyle('Q9:T10')->applyFromArray($border_dash);

            $sheet->setCellValue('U9', __('管理職'));
            $sheet->setCellValue('U10', __('確認済'));
            $sheet->setCellValue('V10', __('コメント入力欄'));
            $sheet->mergeCells('U9:V9');
            $sheet->getStyle('U9:V10')->applyFromArray($aligncenter);
            $sheet->getStyle('U9:V10')->applyFromArray($border_dash);
            
            $sheet->setCellValue('W9', __('経理担当者'));
            $sheet->setCellValue('W10', __('確認欄'));
            $sheet->setCellValue('X10', __('コメント入力欄'));
            $sheet->mergeCells('W9:X9');
            $sheet->getStyle('W9:X10')->applyFromArray($aligncenter);
            $sheet->getStyle('W9:X10')->applyFromArray($border_dash);

            $sheet->setCellValue('Y9', __('経理管理者'));
            $sheet->setCellValue('Y10', __('確認欄'));
            $sheet->setCellValue('Z10', __('コメント入力欄'));
            $sheet->mergeCells('Y9:Z9');
            $sheet->getStyle('Y9:Z10')->applyFromArray($aligncenter);
            $sheet->getStyle('Y9:Z10')->applyFromArray($border_dash);
            
            #wrap text
            $sheet->getStyle('C9:Z10')->getAlignment()->setWrapText(true);
            
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
                // $account_code = $result['account_review_excel']['account_code'];
                // $account_name = $result['account_review_excel']['account_name'];
                // $destination_code = $result['account_review_excel']['destination_code'];
                $layer_code = $result['account_review_excel']['layer_code'];
                $period = $result['account_review_excel']['period'];
                $destination_name = $result['account_review_excel']['destination_name'];
                $item_code = $result['account_review_excel']['item_code'];
                $item_name = $result['account_review_excel']['item_name'];
                $item_name_2 = $result['account_review_excel']['item_name_2'];
                $unit = $result['account_review_excel']['unit'];
                $registration_date = $result['account_review_excel']['registration_date'];
                // $logistic_index_no = $result['account_review_excel']['logistic_index_no'];
                // $posting_date = $result['account_review_excel']['posting_date'];
                // $recorded_date = $result['account_review_excel']['recorded_date'];
                $numbers_day = $result['account_review_excel']['numbers_day'];
                $receipt_index_no = $result['account_review_excel']['receipt_index_no'];
                // $currency =$result['account_review_excel']['currency'] ;
                $quantity =$result['account_review_excel']['quantity'] ;
                $amount = $result['0']['amount'];
                $is_error =$result['account_review_excel']['is_error'] ;
                $is_sold =$result['account_review_excel']['is_sold'] ;
                $is_contract =$result['account_review_excel']['is_contract'] ;
                // $reason =$result['account_review_excel']['reason'] ;
                // $solution =$result['account_review_excel']['solution'] ;
                $preview_comment = $result['account_review_excel']['preview_comment'];
                // $sale_representative = $result['account_review_excel']['sale_representative'];
                $flag = $result['account_review_excel']['flag'];
                $stock_schedule_date = date('Y-m-d', strtotime($result['account_review_excel']['schedule_date']));
                $ba_inc_reason = $result['account_review_excel']['reason'];
                $settlement_date = $result['account_review_excel']['settlement_date'];
                if ($settlement_date == '0000-00-00' || $settlement_date == NULL || $settlement_date == '') {
                    $settlement_date = '';
                }else {
                    $settlement_date = date('Y-m-d', strtotime($settlement_date));
                }
                $remark = $result['account_review_excel']['remark'];
                $business_admin_comment = $result['account_review_excel']['business_admin_comment'];
                $acc_incharge_comment = $result['account_review_excel']['acc_incharge_comment'];
                $acc_submgr_comment = $result['account_review_excel']['acc_submgr_comment'];
                
                // Calulating the difference in timestamps
                // $diff = strtotime($reference_date) - strtotime($sap_schedule_date);
                $diff = strtotime($reference_date) - strtotime($stock_schedule_date);
                // 24 * 60 * 60 = 86400 seconds
                $NoOfDays = round($diff/86400);//abs(round($diff / 86400));
                
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
                    $sheet->getStyle('A'.$a .':'.'Z'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
                } else {
                    $sheet->getStyle('A'.$a .':'.'Z'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                }
                #amount column to set number format
                $sheet->getStyle('L'.$a)->getNumberFormat()->setFormatCode('#,##0');

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$a, $layer_code);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$a, $period);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$a, $destination_name);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$a, $item_code);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$a, $item_name);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$a, $item_name_2);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$a, $unit);
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$a, $registration_date);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$a, $numbers_day);
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$a, $receipt_index_no);
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$a, $quantity);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$a, $amount);
                $objPHPExcel->getActiveSheet()->setCellValue('M'.$a, $is_error);
                $objPHPExcel->getActiveSheet()->setCellValue('N'.$a, $is_sold);
                $objPHPExcel->getActiveSheet()->setCellValue('O'.$a, $is_contract);
                $objPHPExcel->getActiveSheet()->setCellValue('P'.$a, $preview_comment);
                // $objPHPExcel->getActiveSheet()->setCellValue('Q'.$a, $solution);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('D'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('H'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('I'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('J'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('K'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('L'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('M'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('N'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('O'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('P'.$a)->applyFromArray($alignleft);
                // $objPHPExcel->getActiveSheet()->getStyle('Q'.$a)->applyFromArray($alignleft);
                    
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
                // $objPHPExcel->getActiveSheet()->getStyle('Q'.$a)->applyFromArray($border_dash);
                
                $objPHPExcel->getActiveSheet()->setCellValue('Q'.$a, $chk_busi_inc_confirm);//1
                $objPHPExcel->getActiveSheet()->getStyle('Q'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('Q'.$a)->applyFromArray($alignright);
                
                $objPHPExcel->getActiveSheet()->setCellValue('R'.$a, $ba_inc_reason);
                $objPHPExcel->getActiveSheet()->getStyle('R'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('R'.$a)->applyFromArray($alignleft);
                
                $objPHPExcel->getActiveSheet()->setCellValue('S'.$a, $settlement_date);
                $objPHPExcel->getActiveSheet()->getStyle('S'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('S'.$a)->applyFromArray($alignleft);
                
                $objPHPExcel->getActiveSheet()->setCellValue('T'.$a, $remark);
                $objPHPExcel->getActiveSheet()->getStyle('T'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('T'.$a)->applyFromArray($alignleft);
                
                $objPHPExcel->getActiveSheet()->setCellValue('U'.$a, $chk_busi_admin_confirm);//1
                $objPHPExcel->getActiveSheet()->getStyle('U'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('U'.$a)->applyFromArray($alignright);
                
                $objPHPExcel->getActiveSheet()->setCellValue('V'.$a, $business_admin_comment);
                $objPHPExcel->getActiveSheet()->getStyle('V'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('V'.$a)->applyFromArray($alignleft);
                
                $objPHPExcel->getActiveSheet()->setCellValue('W'.$a, $chk_acc_inc_confirm); //1
                $objPHPExcel->getActiveSheet()->getStyle('W'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('W'.$a)->applyFromArray($alignright);
                
                $objPHPExcel->getActiveSheet()->setCellValue('X'.$a, $acc_incharge_comment);
                $objPHPExcel->getActiveSheet()->getStyle('X'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('X'.$a)->applyFromArray($alignleft);
                
                // if (($role_id == 3)||($role_id == 1 || $role_id == 2)) {
                if ($acc_submgr_comment != '' && $flag == 6) {
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$a, 0);
                } elseif ($flag > 6) {
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$a, 1);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('Y'.$a, 0);
                }
                // }
                // $objPHPExcel->getActiveSheet()->setCellValue('Y'.$a, 1);
                $objPHPExcel->getActiveSheet()->getStyle('Y'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('Y'.$a)->applyFromArray($alignright);
                
                $objPHPExcel->getActiveSheet()->setCellValue('Z'.$a, $acc_submgr_comment);
                $objPHPExcel->getActiveSheet()->getStyle('Z'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('Z'.$a)->applyFromArray($alignleft);
                
                $a++;
            }
            //END ROW MERGE CELL
            
            $this->PhpExcel->output('StockAccountReviews'.'.xlsx');
            $this->autoLayout = false;
            $this->render('index');
        } else {
            $param = array( __('export'));
            $msg = parent::getErrorMsg('SE017', $param);
            $this->Session->write('EXCEL_ERR_MSG', $msg);
            $this->redirect(array('controller'=>'StockAccountReviews','action'=>'index'));
        }
    }
    
}
