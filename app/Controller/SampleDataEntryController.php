<?php
ob_get_contents();// to clear POST Content length error when file upload
ob_end_clean();// to clear POST Content length error when file upload
/**
 *	SampleDataEntryController
 *	@author Thura Moe
 *
 **/
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
# Imports the Google Cloud client library
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\Exception\GoogleException;

define('PAGE', 'SampleDataEntry');
define('MENUID', 2);

class SampleDataEntryController extends AppController
{
    var $layout = 'samplecheck';
    public $uses = array('User', 'Sample', 'SampleAccAttachment', 'SampleBusiAdminApprove', 'Layer','SampleAccRequest', 'SampleBusiManagerRequest','Layer');
    public $components = array('Session','Flash');

    public function beforeFilter()
    {
        parent::checkSampleUrlSession();#checkurlsession
    }
    
    /**
     *	Allow user_level_id
     *	admin = 1, sales => 7,6,5
     **/
    public function index()
    {
        $this->layout = 'samplecheck';
        $sample_data_flag = '';//for approve/cancel button of user_level 6
        $no_data = '';
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');

        #$user_level != 1 && $user_level != 7 && $user_level != 6 && $user_level != 5 && $user_level != 8
        $Common = new CommonController();
        # check user permission
        $permissions = $this->Session->read('PERMISSIONS');

        $status = $this->Sample->find('first',array(
            'conditions' => array(
                'Sample.flag <>' => 0,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category'    => $category
            ),
        ));
        $allstatus = $this->Sample->find('list',array(
            'fields' => array('Sample.id', 'Sample.flag'),
            'conditions' => array(
                'Sample.flag <>' => 0,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category'    => $category
            ),
            'group' => 'Sample.flag',
            'order' => 'Sample.flag',
        ));
        $flag2 = $this->Sample->find('list',array(
            'fields' => array('Sample.id', 'Sample.flag'),
            'conditions' => array(
                'Sample.flag <>' => 0,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category'    => $category,
                'Sample.flag'   => 2
            ),
        ));

        $status = (!empty($status['Sample']['flag'])) ? $status['Sample']['flag'] : 0;
        if(count($allstatus) > 1) $status = min($allstatus);
        $lastRequest = 'no';
        if(count($flag2) == 1) $lastRequest = 'yes';
        
        $buttons = $Common->getButtonLists($status,$layer_code,$permissions);
        $data = $this->Sample->find('all', array(
            'conditions' => array(
                'CAST(Sample.flag AS UNSIGNED) >=' => 2,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category'    => $category
            ),
            'order' => array('Sample.id')
        ));

        $data = array_column($data, 'Sample');
        $get_id = array_column($data, 'id');//get sample data id
        $size = count($data);
        if (!empty($data)) {
            /* get attachment file for account team (status 1) */
            $acc_file = $this->SampleAccAttachment->find('all', array(
                'conditions' => array(
                    'SampleAccAttachment.status' => 1,
                    'SampleAccAttachment.flag' => 1,
                    'SampleAccAttachment.sample_id IN' => $get_id
                )
            ));
            $acc_file = array_column($acc_file, 'SampleAccAttachment');
            $cnt_acc_file = count($acc_file);

            /* get attachment file for sale team (status 2) */
            $sale_file = $this->SampleAccAttachment->find('all', array(
                'conditions' => array(
                    'SampleAccAttachment.status' => 2,
                    'SampleAccAttachment.flag' => 1,
                    'SampleAccAttachment.sample_id IN' => $get_id
                )
            ));
            $sale_file = array_column($sale_file, 'SampleAccAttachment');
            $cnt_sale_file = count($sale_file);

            for ($i=0; $i<$size; $i++) {
                $sid = $i+1;
                $data[$i]['sid'] = $sid;
                $sample_id = $data[$i]['id'];
                
                if ($cnt_acc_file > 0) {
                    $data[$i]['acc_file'] = $this->__prepareFileAttachment($cnt_acc_file, $acc_file, $sample_id);

                    /* if some of the sample_data has no attachment then add file index to $data array */
                    if (!array_key_exists('acc_file', $data[$i])) {
                        $data[$i]['acc_file'] = [];
                    }
                } else {
                    $data[$i]['acc_file'] = [];
                }

                if ($cnt_sale_file > 0) {
                    $data[$i]['sale_file'] = $this->__prepareFileAttachment($cnt_sale_file, $sale_file, $sample_id);

                    /* if some of the sample_data has no attachment then add file index to $data array */
                    if (!array_key_exists('sale_file', $data[$i])) {
                        $data[$i]['sale_file'] = [];
                    }
                } else {
                    $data[$i]['sale_file'] = [];
                }

                /* get flag to show/hide approve button for user_level 6 */
                $sample_data_flag = $data[$i]['flag'];
            }
        } else {
            $no_data = parent::getErrorMsg("SE001");
        }

        # to show `Request` button

        // $show_Save = $this->toShowBtnReq($period, $layer_code);

        // $show_Request = $this->toShowBtnReq($period, $layer_code);
        // // string urlencode(string $showSave&SendEmail) =
        // // $.'showSave&SendEmail' = $this->toShowBtnReq($period, $layer_code);
        // // pr($.'showSave&SendEmail');die();

        
        // # to show `Approve` button, find flag 3
        // $show_Approve = $this->toShowHideButtonStatus($period, $layer_code, 3);

        // #to show reject
        // $show_Reject = $this->toShowHideButtonStatus($period, $layer_code, 3);
        // # to show `Approve Cancel` button, find flag 4
        // $show_ApproveCancel = $this->toShowHideButtonStatus($period, $layer_code, 4);

        $page = PAGE;
        $this->set(compact('layer_name', 'data', 'user_level', 'sample_data_flag', 'no_data', 'showBtnApproveCancel', 'buttons', 'page', 'lastRequest'));
        $this->render("index");
    }

    public function __prepareFileAttachment($file_count, $file_arr, $sample_id)
    {
        $tmp = [];
        for ($j=0; $j<$file_count; $j++) {
            $attachment_id = $file_arr[$j]['id'];
            $file_sample_id = $file_arr[$j]['sample_id'];
            $file_name = $file_arr[$j]['file_name'];
            $file_type = $file_arr[$j]['file_type'];
            $file_size = $file_arr[$j]['file_size'];
            $url = $file_arr[$j]['url'];
            if ($sample_id == $file_sample_id) {
                $tmp[] = array(
                    'attachment_id' => $attachment_id,
                    'file_name' => $file_name,
                    'file_type' => $file_type,
                    'file_size' => $file_size,
                    'url' => $url
                );
            }
        }
        return $tmp;
    }

    /**
     * To show Request button,
     * if flag 2 data is found for selected period and BA Code
     */
    protected function toShowBtnReq($period, $layer_code)
    {
        $find = $this->Sample->find('all', array(
            'conditions' => array(
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.flag' => 2
            )
        ));
        if (!empty($find)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * To show Approve button,
     * if all of selected period and BA Code is flag 3, then show `Approve` button
     * To show Approve Cancel button,
     * if all of selected period and BA Code is flag 4, then show `Approve Cancel` button
     */
    protected function toShowHideButtonStatus($period, $layer_code, $flag)
    {
        // $rsl = $this->Sample->chkToShowHideApprove($period, $layer_code, $flag);
        // if (!empty($rsl)) {
        //     $status = $rsl[0][0]['approveStatus'];
        //     if ($status == 1) {
        //         # show approve button
        //         return true;
        //     } else {
        //         # hide approve button
        //         return false;
        //     }
        // } else {
        //     # hide approve button
        //     return false;
        // }
        return true;
    }

    /**
     * Approve
     * change flag 3 to flag 4 in samples
     */
    public function fun_approve()
    {
        $Common = new CommonController();
        if ($this->request->is('post')) {
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $admin_id = $this->Session->read('LOGIN_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $invalid_email = '';
            $Show_SID ='';

            # check user permission to access this method
            # $user_level != 1 && $user_level != 5 && $user_level != 8
            $Common = new CommonController();
            // $allowed_list = array_unique(array_column(array_column($Common->getButtonList(PAGE, MENUID, 'Approve'),'Permissions'), 'role_id'));
            // if (!in_array($user_level, array_values($allowed_list))) {
            //     $msg = parent::getErrorMsg("SE016", [__("承認")]);
            //     $this->Flash->set($msg, array('key'=>'EntryFail'));
            //     $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            // }

            # check data need to approve or not
            $isNeedToApprove = $this->Sample->find('all', array(
                'conditions' => array(
                    'Sample.flag' => 3,
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'Sample.layer_code' => $layer_code,
                    'Sample.category' => $category
                )
            ));

            if (!empty($isNeedToApprove)) {
                # check file is upload for each record of business side(status = 2)
                # if some record is not upload file, then can't approve
                $tmp = array_column($isNeedToApprove, 'Sample');
                $get_id = array_column($tmp, 'id');
                $isFileExists = $this->SampleAccAttachment->find('count', array(
                    'conditions' => array(
                        'SampleAccAttachment.flag' => 1,
                        'SampleAccAttachment.status' => 2,
                        'SampleAccAttachment.sample_id IN' => $get_id
                    ),
                    'group' => array('sample_id')
                ));
                if (count($get_id) != $isFileExists) {
                    $msg = parent::getErrorMsg("SE043");
                    $this->Flash->set($msg, array('key'=>'EntryFail'));
                    $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                }
                
                # prepare data to save into tbl_sample_busi_admin_approve
                $isNeedToApprove = array_column($isNeedToApprove, 'Sample');
                $cnt = count($isNeedToApprove);
                $today = date("Y-m-d");
                $admin_approve = [];
                for ($i=0; $i<$cnt; $i++) {
                    $sample_id = $isNeedToApprove[$i]['id'];
                    $admin_approve[] = array(
                        'SampleBusiAdminApprove.sample_id' => $sample_id,
                        'SampleBusiAdminApprove.approve_date' => $today,
                        'SampleBusiAdminApprove.flag' => 1,
                        'SampleBusiAdminApprove.created_by' => $admin_id,
                        'SampleBusiAdminApprove.updated_by' => $admin_id
                    );
                }
                $approveDB = $this->SampleBusiAdminApprove->getDataSource();
                
                # change all flag 3 data to flag 4 inside samples
                /* field and condition to update */
                $sampleDB = $this->Sample->getDataSource();
                $flag = $sampleDB->value(4, 'string');//3
                $updated_by = $sampleDB->value($admin_id, 'string');
                $updated_date = $sampleDB->value(date('Y-m-d H:i:s'), 'string');
                $record['Sample.flag'] = $flag;
                $record['Sample.updated_by'] = $updated_by;
                $record['Sample.updated_date'] = $updated_date;
                $condition['DATE_FORMAT(Sample.period, "%Y-%m")'] = $period;
                $condition['Sample.layer_code'] = $layer_code;
                $condition['Sample.category'] = $category;
                $condition['Sample.flag'] = 3;
                try {
                    $approveDB->begin();
                    $sampleDB->begin();
                    $this->Sample->updateAll(
                        $record,
                        $condition
                    );

                    /* save data into tbl_sample_busi_admin_approve table */
                    $this->SampleBusiAdminApprove->saveMany($admin_approve);

                    #Get Mail Sent For SID  no and index name.
                    $countSID =count($isNeedToApprove);
                    $Show_SID = array();
                    for ($i=0; $i<$countSID; $i++) {
                        $sid = $i+1;
                        $get_index_no =$isNeedToApprove[$i]['index_no'];
                        $temp = "SID".$sid." &lt;".$get_index_no."&gt;";
                        array_push($Show_SID, $temp);
                    }
                    $Show_SID = implode(',', $Show_SID);
                    
                    # send email to Account In Charge (level 4) and Account tcl (level 3)
                    // $manager_level_id  = AdminLevel::ACCOUNT_SECTION_MANAGER;//3
                    // $incharge_level_id = AdminLevel::ACCOUNT_INCHARGE;//4
                    // $busines_incharge_id = AdminLevel::BUSINESS_ADMINISTRATIOR;//6
                    $to_email = $this->request->data('toEmail');
                    $cc_email = $this->request->data('ccEmail');
                    $bcc_email = $this->request->data('bccEmail');
                    #Mail
                    $mail_template 			= 'common';
                    #Mail contents
                    // $mail['subject']	 	= '【サンプルチェック】'.$layer_name.'部署によるデータ入力 営業部長承認通知';
                    // $mail['template_title'] = '財務経理部各位';
                    // $mail['template_body'] 	= '当月の'.$layer_name.'のサンプルチェックデータの承認が完了しました。<br>'.$Show_SID.'<br/>';
                    $mail['subject'] 		= $this->request->data('mailSubj');
                    $mail['template_title'] = '';
                    $mail['template_body'] 	= $this->request->data('mailBody').$Show_SID;
                    #Search account user level in BA table (3,4)
                    #Check BA code exist or not in user table
                    #Get account  to level  4 email from tbl_user
                    // $searchToEmail = $Common->getUserEmail($layer_code, $incharge_level_id);
                    $searchToEmail = parent::formatMailInput($to_email);
                   

                    //pr($searchToEmail);die();

                    /*if (!$searchToEmail) {

                        $msg = parent::getErrorMsg("SE059");
                        $this->Flash->set($msg, array('key'=>'EntryFail'));
                        $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                    }
                */
                    #Get account  CC level  3 email from tbl_user
                    // $searchCCEmail = $Common->getUserEmail($layer_code, array($manager_level_id,$busines_incharge_id));
                    $searchCCEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);

                    //pr($searchCCEmail);die();
                    
                    /*if (!$searchCCEmail) {

                        $msg = parent::getErrorMsg("SE059");
                        $this->Flash->set($msg, array('key'=>'EntryFail'));
                        $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                    }*/

                    #mail send or not
                    $mail_send = $this->request->data('mailSend');

                    if($mail_send == 0){
                        $sampleDB->commit();
                        $approveDB->commit();
                        $msg = parent::getSuccessMsg("SS005");
                        $this->Flash->set($msg, array('key'=>'EntryOK'));
                        $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                    }else{
                        if (!empty($searchToEmail)) {
                        
    
                            // $url = '/SampleTestResults?period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                            $url = '/SampleTestResults?period='.$period.'&ba='.$layer_code.'&category='.$category;
    
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $searchToEmail, $searchCCEmail, $bccEmail, $mail_template, $mail, $url);
                            if ($sentMail["error"]) {
                                $msg = $sentMail["errormsg"];
                                $this->Flash->set($msg, array('key'=>'EntryFail'));
                                $msg = parent::getErrorMsg("SE011", [__("承認")]);//already approve
                                $this->Flash->set($msg, array('key'=>'EntryFail'));
                            } else {
                                $sampleDB->commit();
                                $approveDB->commit();
                                $msg = parent::getSuccessMsg("SS005");
                                $this->Flash->set($msg, array('key'=>'EntryOK'));
                                $msg = parent::getSuccessMsg("SS018");
                                $this->Flash->set($msg, array('key'=>'EntryOK'));
                                $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                            }
                        } else {
                            #mail does dont sent onlye approve
                            CakeLog::write('debug', 'Incharge(level 4) and Sub-Manager(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $sampleDB->commit();
                            $approveDB->commit();
                            $msg = parent::getSuccessMsg("SS005");
                            $this->Flash->set($msg, array('key'=>'EntryOK'));
                            $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                        }
                    }

                                    
                    $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                } catch (Exception $e) {
                    $sampleDB->rollback();
                    $approveDB->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE011", [__("承認")]);
                    $msg .= " ".$invalid_email;
                    $this->Flash->set($msg, array('key'=>'EntryFail'));
                    $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                }
            } else {
                # if no need to approve, check its already approve or not
                $isAlreadyApprove = $this->Sample->find('count', array(
                    'conditions' => array(
                        'CAST(Sample.flag AS UNSIGNED) >' => 3,
                        'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                        'Sample.layer_code' => $layer_code,
                        'Sample.category' => $category
                    )
                ));

                if ($isAlreadyApprove > 0) {
                    $msg = parent::getErrorMsg("SE018");//already approve
                    $this->Flash->set($msg, array('key'=>'EntryFail'));
                    $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                }
                $msg = parent::getErrorMsg("SE017", [__("承認")]);//no need to approve
                $this->Flash->set($msg, array('key'=>'EntryFail'));
                $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
        }
    }

    /**
     * Approve Cancellation
     * change flag 4 to flag 2 in samples
     **/
    public function fun_approvecancel()
    {
        if ($this->request->is('post')) {
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $admin_id = $this->Session->read('LOGIN_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $invalid_email = '';
            // $to_email = $_POST['toEmail'];
            // $cc_email = $_POST['ccEmail'];
            // die('arrriver');

            # check user permission to access this method
            # $user_level != 1 && $user_level != 5 && $user_level != 8
            $Common = new CommonController();
            // $allowed_list = array_unique(array_column(array_column($Common->getButtonList(PAGE, MENUID, 'Reject'),'Permissions'), 'role_id'));
            // if (!in_array($user_level, array_values($allowed_list))) {
            //     $msg = parent::getErrorMsg("SE016", [__("承認キャンセル")]);
            //     $this->Flash->set($msg, array('key'=>'EntryFail'));
            //     $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            // }

            # check data need to approve cancel or not
            $isNeedToCancel = $this->Sample->find('all', array(
                'conditions' => array(
                    'Sample.flag' => 4,
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'Sample.layer_code' => $layer_code,
                    'Sample.category' => $category
                )
            ));

            if (!empty($isNeedToCancel)) {
                # when approved data is cancel,
                # * change flag 2 in samples
                # * change flag 0 in tbl_sample_busi_admin_approve
                # * change flag 0 in tbl_sample_busi_manager_request
                $isNeedToCancel = array_column($isNeedToCancel, 'Sample');
                $to_cancel = array_column($isNeedToCancel, 'id');

                # change flag 0 in tbl_sample_busi_manager_request
                $bmReqDB = $this->SampleBusiManagerRequest->getDataSource();
                $req_flag = $bmReqDB->value(0, 'string');
                $req_updated_by = $bmReqDB->value($admin_id, 'string');
                $bm_req['flag'] = $req_flag;
                $bm_req['updated_by'] = $req_updated_by;
                $bm_req_cond[] = array(
                    'sample_id IN ('.implode(',', $to_cancel).')'
                );

                # change flag 0 in tbl_sample_busi_admin_approve table
                $approveDB = $this->SampleBusiAdminApprove->getDataSource();
                $approve_flag = $approveDB->value(0, 'string');
                $approve_updated_by = $approveDB->value($admin_id, 'string');
                $approve['flag'] = $approve_flag;
                $approve['updated_by'] = $approve_updated_by;
                $approve_cond[] = array(
                    'sample_id IN ('.implode(',', $to_cancel).')'
                );

                # change all flag 4 data to flag 2 inside samples
                /* field and condition to update */
                $db = $this->Sample->getDataSource();
                $flag = $db->value(2, 'string');
                $updated_by = $db->value($admin_id, 'string');
                $updated_date = $db->value(date('Y-m-d H:i:s'), 'string');
                $record['flag'] = $flag;
                $record['updated_by'] = $updated_by;
                $record['updated_date'] = $updated_date;
                $condition['DATE_FORMAT(period, "%Y-%m")'] = $period;
                $condition['layer_code'] = $layer_code;
                $condition['category'] = $category;
                $condition['flag'] = 4;
                try {
                    $db->begin();
                    $approveDB->begin();
                    $bmReqDB->begin();

                    $this->Sample->updateAll(
                        $record,
                        $condition
                    );

                    $this->SampleBusiAdminApprove->updateAll(
                        $approve,
                        $approve_cond
                    );

                    $this->SampleBusiManagerRequest->updateAll(
                        $bm_req,
                        $bm_req_cond
                    );

                    $to_email = $this->request->data('toEmail');
                    $cc_email = $this->request->data('ccEmail');
                    $bcc_email = $this->request->data('bccEmail');
                   
                    $mail_template 			= 'common';
                    $mail['subject'] 		= $this->request->data('mailSubj');
                    $mail['template_title'] = '';
                    $mail['template_body'] 	= $this->request->data('mailBody');
                    
                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);

                    # edited by khin hnin myo
                    // $mail['subject'] 		= $_POST['mailSubj'];
                    // $mail['template_title'] = $_POST['mailTitle'];
                    // $mail['template_body'] 	= $_POST['mailBody'];
                    
                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);

                    $mail_send = $this->request->data('mailSend');

                    if($mail_send == 0){
                        $approveDB->commit();
                        $bmReqDB->commit();
                        $db->commit();
                        $msg = parent::getSuccessMsg("SS006");
                        $this->Flash->set($msg, array('key'=>'EntryOK'));
                        $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                    }else{
                        // $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                        $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code.'&category='.$category;
    
                        if (!empty($toEmail)) {
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                            if ($sentMail["error"]) {
                                $msg = $sentMail["errormsg"];
                                $this->Flash->set($msg, array('key'=>'EntryFail'));
                                $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                                $invalid_email = parent::getErrorMsg('SE042');
                            } else {
                                $approveDB->commit();
                                $bmReqDB->commit();
                                $db->commit();
                                $msg = parent::getSuccessMsg("SS006");
                                $this->Flash->set($msg, array('key'=>'EntryOK'));
                                $msg = parent::getSuccessMsg("SS018");
                                $this->Flash->set($msg, array('key'=>'EntryOK'));
                            }
                        } else {
                            CakeLog::write('debug', 'Incharge(level 4) and Sub-Manager(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $msg = parent::getErrorMsg("SE058");
                            $this->Flash->set($msg, array('key'=>'EntryFail'));
                            $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                        }
                    }
                    /* mail ending for approve cancle*/
                    
                    
                    $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                } catch (Exception $e) {
                    $approveDB->rollback();
                    $bmReqDB->rollback();
                    $db->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE011", [__("承認キャンセル")]);
                    $msg .= ' '.$invalid_email;
                    $this->Flash->set($msg, array('key'=>'EntryFail'));
                    $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                }
            } else {
                # no data to approve cancel
                $msg = parent::getErrorMsg("SE017", [__("承認キャンセル")]);
                $this->Flash->set($msg, array('key'=>'EntryFail'));
                $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
        }
    }

    /**
     * Reject state when request finished
     * change flag 3 to flag 2 in samples
     **/
    public function fun_reject()
    {
        if ($this->request->is('post')) {
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $admin_id = $this->Session->read('LOGIN_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $invalid_email = '';
            // $to_email = $_POST['toEmail'];
            // $cc_email = $_POST['ccEmail'];
           


            # check user permission to access this method
            # $user_level != 1 && $user_level != 5 && $user_level != 8
            $Common = new CommonController();
            // $allowed_list = array_unique(array_column(array_column($Common->getButtonList(PAGE, MENUID, 'Reject'),'Permissions'), 'role_id'));
            // if (!in_array($user_level, array_values($allowed_list))) {
            //     $msg = parent::getErrorMsg("SE016", [__("拒否を確認")]);
            //     $this->Flash->set($msg, array('key'=>'EntryFail'));
            //     $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            // }

            # check data need to approve cancel or not
            $isNeedToCancel = $this->Sample->find('all', array(
                'conditions' => array(
                    'flag' => 3,
                    'DATE_FORMAT(period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category' => $category
                )
            ));

            if (!empty($isNeedToCancel)) {
                # when approved data is cancel,
                # * change flag 2 in samples
                # * change flag 0 in tbl_sample_busi_admin_approve
                # * change flag 0 in tbl_sample_busi_manager_request
                $isNeedToCancel = array_column($isNeedToCancel, 'Sample');
                $to_cancel = array_column($isNeedToCancel, 'id');

                # change flag 0 in tbl_sample_busi_manager_request
                $bmReqDB = $this->SampleBusiManagerRequest->getDataSource();
                $req_flag = $bmReqDB->value(0, 'string');
                $req_updated_by = $bmReqDB->value($admin_id, 'string');
                $bm_req['flag'] = $req_flag;
                $bm_req['updated_by'] = $req_updated_by;
                $bm_req_cond[] = array(
                    'sample_id IN ('.implode(',', $to_cancel).')'
                );

                # change flag 0 in tbl_sample_busi_admin_approve table
                $approveDB = $this->SampleBusiAdminApprove->getDataSource();
                $approve_flag = $approveDB->value(0, 'string');
                $approve_updated_by = $approveDB->value($admin_id, 'string');
                $approve['flag'] = $approve_flag;
                $approve['updated_by'] = $approve_updated_by;
                $approve_cond[] = array(
                    'sample_id IN ('.implode(',', $to_cancel).')'
                );

                # change all flag 3 data to flag 2 inside samples
                /* field and condition to update */
                $db = $this->Sample->getDataSource();
                $flag = $db->value(2, 'string');
                $updated_by = $db->value($admin_id, 'string');
                $updated_date = $db->value(date('Y-m-d H:i:s'), 'string');
                $record['flag'] = $flag;
                $record['updated_by'] = $updated_by;
                $record['updated_date'] = $updated_date;
                $condition['DATE_FORMAT(period, "%Y-%m")'] = $period;
                $condition['layer_code'] = $layer_code;
                $condition['category'] = $category;
                $condition['flag'] = 3;
                try {
                    $db->begin();
                    $approveDB->begin();
                    $bmReqDB->begin();

                    $this->Sample->updateAll(
                        $record,
                        $condition
                    );

                    $this->SampleBusiAdminApprove->updateAll(
                        $approve,
                        $approve_cond
                    );

                    $this->SampleBusiManagerRequest->updateAll(
                        $bm_req,
                        $bm_req_cond
                    );
                    $to_email = $this->request->data('toEmail');
                    $cc_email = $this->request->data('ccEmail');
                    $bcc_email = $this->request->data('bccEmail');
                   
                    $mail_template 			= 'common';
                    $mail['subject'] 		= $this->request->data('mailSubj');
                    $mail['template_title'] = '';
                    $mail['template_body'] 	= $this->request->data('mailBody').$Show_SID;
                   
                    # edited by khin hnin myo
                    // $mail['subject'] 		= $_POST['mailSubj'];
                    // $mail['template_title'] = $_POST['mailTitle'];
                    // $mail['template_body'] 	= $_POST['mailBody'];
                    
                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);

                    #mail send or not
                    $mail_send = $this->request->data('mailSend');

                    if($mail_send == 0){
                        $approveDB->commit();
                        $bmReqDB->commit();
                        $db->commit();
                        $msg = parent::getSuccessMsg("SS014");
                        $this->Flash->set($msg, array('key'=>'EntryOK'));
                        $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));                        
                    }else{
                        // $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                        $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code.'&category='.$category;
                        if (!empty($toEmail)) {
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                            if ($sentMail["error"]) {
                                $msg = $sentMail["errormsg"];
                                $this->Flash->set($msg, array('key'=>'EntryFail'));
                                $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                                $invalid_email = parent::getErrorMsg('SE042');
                            } else {
                                $approveDB->commit();
                                $bmReqDB->commit();
                                $db->commit();
                                $msg = parent::getSuccessMsg("SS014");
                                $this->Flash->set($msg, array('key'=>'EntryOK'));
                                $msg = parent::getSuccessMsg("SS018");
                                $this->Flash->set($msg, array('key'=>'EntryOK'));
                            }
                        } else {
                            CakeLog::write('debug', 'Incharge(level 4) and Sub-Manager(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $msg = parent::getErrorMsg("SE058");
                            $this->Flash->set($msg, array('key'=>'EntryFail'));
                            $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                        }

                    }
                                            

                    /* mail ending for approve cancle*/
                    
                    
                    $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                } catch (Exception $e) {
                    $approveDB->rollback();
                    $bmReqDB->rollback();
                    $db->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE011", [__("拒否を確認")]);
                    $msg .= ' '.$invalid_email;
                    $this->Flash->set($msg, array('key'=>'EntryFail'));
                    $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                }
            } else {
                # no data to approve cancel
                $msg = parent::getErrorMsg("SE017", [__("拒否を確認")]);
                $this->Flash->set($msg, array('key'=>'EntryFail'));
                $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
        }
    }

    /**
     * File upload  Send mail Button
     * when File uploading Finished Send Mail to User Level 7
     **/
    // public function dataEntryFileUploadMail()
    public function fun_save()
    {
        $Common = new CommonController;
        if ($this->request->is('post')) {
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $admin_id = $this->Session->read('LOGIN_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');

            $invalid_email = '';
            $countSID = '';
            $Show_SID = '';
            $get_index_no ='';
            $sid_arr = array();
            $id_arr = array();
            $id_pair = array();

            #mail request
            $to_email = $this->request->data('toEmail');
            $cc_email = $this->request->data('ccEmail');
            $bcc_email = $this->request->data('bccEmail');
            // pr($this->request);die();
            #Added by PanEiPhyo(20200305)
            $data = json_decode($this->request->data('request_data'));
            foreach ($data as $sid => $id) {
                if ($id != "") {
                    $id_arr[] = $id;
                    $sid_arr[] = $sid;
                    $id_pair[$id] = $sid;
                }
            }
            # check user permission to access this method
            # $user_level != 7
            $Common = new CommonController();
            // $allowed_list = array_unique(array_column(array_column($Common->getButtonList(PAGE, MENUID, 'Save'),'Permissions'), 'role_id'));
            // if (!in_array($user_level, array_values($allowed_list))) {
            //     $msg = parent::getErrorMsg("SE066", [__("アップロード")]);
            //     $this->Flash->set($msg, array('key'=>'EntryFail'));
            //     $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            // }

            # check request data(from SampleRegistrations) is exists or not
            $approved = $this->Sample->find('all', array(
                'conditions' => array(
                    'flag' => 2,
                    'DATE_FORMAT(period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category' => $category
                    
                )
            ));

            if (!empty($approved)) {
                # check file is upload for each record of business side(status = 2)
                # if some record is not upload file, then can't approve

                $isFileExists = $this->SampleAccAttachment->find('count', array(
                    'conditions' => array(
                        'flag' => 1,
                        'status' => 2,
                        'sample_id' => $id_arr
                    ),
                    'group' => array('sample_id')
                ));

                $isFileAll = $this->SampleAccAttachment->find('all', array(
                    'conditions' => array(
                        'flag' => 1,
                        'status' => 2,
                        'sample_id' => $id_arr
                    ),
                    'group' => array('sample_id')
                ));
                
                #Check File upload finished cannot be sent mail
                if (empty($isFileAll) || (count($isFileAll) < count($id_arr))) {
                    $msg = parent::getErrorMsg("SE067");
                    $this->Flash->set($msg, array('key'=>'EntryFail'));
                    $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                }

                #Check upload file and mail sent file upload
                $upload_File_mail = $this->Sample->find('all', array(
                'conditions' => array(
                'DATE_FORMAT(period, "%Y-%m")' => $period,
                'layer_code' => $layer_code,
                'category' => $category,
                'id IN' => $id_arr,
                'OR' => array(
                    array('flag'=>2), //Get change only flag 2 select
                    array('flag'=>3)
                        )
                    )
                ));

                # Flag 2,3 validation and Get indexo_no
                try {
                    #Email Sent to admin level 7 when file upload finished
                    $Show_SID = array();
                    foreach ($upload_File_mail as $mail_data) {
                        $get_index_no 	= $mail_data['Sample']['index_no'];
                        $sid 			= $id_pair[$mail_data['Sample']['id']];
                        #change code for vertical line index_no
                        $temp = "SID".$sid." &lt;".$get_index_no."&gt;";
                        array_push($Show_SID, $temp);
                    }
                    $Show_SID = implode(',', $Show_SID);
                    # send email to Busines incharge Level (level 7)
                    // $incharge_level_id  = AdminLevel::BUSINESS_INCHARGE;

                    $mail_template 			= 'common';
                    
                    // $mail['subject'] 		= $_POST['mailSubj'];
                    // $mail['template_title'] = $_POST['mailTitle'];
                    // $mail['template_body'] 	= $_POST['mailBody'].$Show_SID;

                    $mail['subject'] 		= $this->request->data('mailSubj');
                    $mail['template_title'] = '';
                    $mail['template_body'] 	= $this->request->data('mailBody').$Show_SID;
                    // pr($this->request);
                    // die();

                    
                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);
                    
                    #mail send or not
                    $mail_send = $this->request->data('mailSend');

                    if($mail_send == 0){
                        // $msg = parent::getSuccessMsg("SS018");
                        $msg = "Please change mail flow setting to send email.";
                        $this->Flash->set($msg, array('key'=>'EntryOK'));
                        $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                    }else{
                        if (!empty($toEmail)) {
                            // $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                            $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code.'&category='.$category;
                            
                            if (!empty($toEmail)) {
                                $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                if ($sentMail["error"]) {
                                    $msg = $sentMail["errormsg"];
                                    $this->Flash->set($msg, array('key'=>'EntryFail'));
                                    $invalid_email = parent::getErrorMsg('SE042');
                                    $this->Flash->set($invalid_email, array('key'=>'EntryFail'));
                                } else {
                                    $msg = parent::getSuccessMsg("SS018");
                                    $this->Flash->set($msg, array('key'=>'EntryOK'));
                                }
                            } else {
                                $msg = parent::getErrorMsg("SE059");
                                $this->Flash->set($msg, array('key'=>'EntryFail'));
                                CakeLog::write('debug', 'Manager email not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click `Request` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            }
                        } else {
                            $msg = parent::getErrorMsg("SE059");
                            $this->Flash->set($msg, array('key'=>'EntryFail'));
                            CakeLog::write('debug', 'Manager email not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click `Request` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        }
                    }
                    #Replace following line if no need CC
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE011", [__("依頼")]);
                    $this->Flash->set($msg, array('key'=>'EntryFail'));
                }
            } else {
                # if no approve data, then no data to request
                $msg = parent::getErrorMsg("SE017", [__("依頼")]);//no data to request
                $this->Flash->set($msg, array('key'=>'EntryFail'));
            }
        }
        $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
    }


    /**
     * Request
     * change flag 2 to flag 3 in samples
     **/
    public function fun_request()
    {
        $Common = new CommonController;
        if ($this->request->is('post')) {
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $admin_id = $this->Session->read('LOGIN_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $invalid_email = '';
            $sample_id_arr = json_decode($this->request->data['request_data'], true);
            # check user permission to access this method
            # $user_level != 1 && $user_level != 6
            // $allowed_list = array_unique(array_column(array_column($Common->getButtonList(PAGE, MENUID, 'Request'),'Permissions'), 'role_id'));
            // if (!in_array($user_level, array_values($allowed_list))) {
            //     $msg = parent::getErrorMsg("SE016", [__("依頼")]);
            //     $this->Flash->set($msg, array('key'=>'EntryFail'));
            //     $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            // }

            # check request data(from SampleRegistrations) is exists or not
            $approved = $this->Sample->find('all', array(
                'conditions' => array(
                    'Sample.flag' => 2,
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'Sample.layer_code' => $layer_code,
                    'Sample.category' => $category,
                    'Sample.id' => $sample_id_arr
                )
            ));
            
            if (!empty($approved)) {
                # check file is upload for each record of business side(status = 2)
                # if some record is not upload file, then can't approve
                $tmp = array_column($approved, 'Sample');
                $get_id = array_column($tmp, 'id');
                $isFileExists = $this->SampleAccAttachment->find('count', array(
                    'conditions' => array(
                        'SampleAccAttachment.flag' => 1,
                        'SampleAccAttachment.status' => 2,
                        'SampleAccAttachment.sample_id IN' => $get_id
                    ),
                    'group' => array('SampleAccAttachment.sample_id')
                ));
                if (count($get_id) != $isFileExists) {
                    $msg = parent::getErrorMsg("SE026");
                    $this->Flash->set($msg, array('key'=>'EntryFail'));
                    $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                }
                
                # prepare data to save in tbl_sample_busi_manager_request
                $approved = array_column($approved, 'Sample');
                $cnt = count($approved);
                $today = date("Y-m-d");
                $manager_req = [];
                for ($i=0; $i<$cnt; $i++) {
                    $sample_id = $approved[$i]['id'];
                    $manager_req[] = array(
                        'sample_id' => $sample_id,
                        'request_date' => $today,
                        'flag' => 1,
                        'created_by' => $admin_id,
                        'updated_by' => $admin_id
                    );
                }
                $mgReqDB = $this->SampleBusiManagerRequest->getDataSource();


                # change all flag 2 data to flag 3 inside samples
                /* field and condition to update */
                $db = $this->Sample->getDataSource();
                $flag = $db->value(3, 'string');
                $updated_by = $db->value($admin_id, 'string');
                $updated_date = $db->value(date('Y-m-d H:i:s'), 'string');
                $record['Sample.flag'] = $flag;
                $record['Sample.updated_by'] = $updated_by;
                $record['Sample.updated_date'] = $updated_date;
                $condition['DATE_FORMAT(Sample.period, "%Y-%m")'] = $period;
                $condition['Sample.layer_code'] = $layer_code;
                $condition['Sample.category']  = $category;
                $condition['Sample.flag'] = 2;
                $condition['Sample.id'] = $sample_id_arr;
                try {
                    $db->begin();
                    $mgReqDB->begin();
                    $this->Sample->updateAll(
                        $record,
                        $condition
                    );

                    # save data in tbl_sample_busi_manager_request
                    $this->SampleBusiManagerRequest->saveMany($manager_req);

                    # get all data count
                    $all_record = $this->Sample->find('count', array(
                        'conditions' => array(
                            'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                            'Sample.layer_code' => $layer_code,
                            'Sample.category' => $category,
                            'NOT'=>array('Sample.flag '=>'0'),
                        )
                    ));
                    # get already requested data count
                    $request_finish = $this->Sample->find('count', array(
                        'conditions' => array(
                            'Sample.flag' => 3,
                            'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                            'Sample.layer_code' => $layer_code,
                            'Sample.category' => $category,
                        )
                    ));

                    if ($request_finish == $all_record) {
                        # send email to manager (level 5)

                        $to_email = $this->request->data('toEmail');
                        $cc_email = $this->request->data('ccEmail');
                        $bcc_email = $this->request->data('bccEmail');

                        $mail['subject'] 		= $this->request->data('mailSubj');
                        $mail['template_title'] = '';
                        $mail['template_body'] 	= $this->request->data('mailBody');
    
                        
                        $toEmail = parent::formatMailInput($to_email);
                        $ccEmail = parent::formatMailInput($cc_email);
                        $bccEmail = parent::formatMailInput($bcc_email);
                        #Set sale manager ID (5)
                        // $manager_level_id  = array(AdminLevel::BUSINESS_MANAGER,AdminLevel::DEPUTY_GENERAL_MANAGER);

                        $mail_template 			= 'common';

                        #mail send or not
                        $mail_send = $this->request->data('mailSend');

                        if($mail_send == 0){
                            $db->commit();
                            $mgReqDB->commit();
                            $msg = parent::getSuccessMsg("SS008");
                            $this->Flash->set($msg, array('key'=>'EntryOK'));
                            $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
                        }else{
                            #Replace following line if no need CC
                            if (!empty($toEmail)) {
                                #Assign ''(null) if no need CC
                                $ccEmail  	= "";

                                // $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                                $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code.'&category='.$category;
                                
                                if (!empty($toEmail)) {
                                    $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                    if ($sentMail["error"]) {
                                        $msg = $sentMail["errormsg"];
                                        $this->Flash->set($msg, array('key'=>'EntryFail'));
                                        $invalid_email = parent::getErrorMsg('SE042');
                                        $this->Flash->set($invalid_email, array('key'=>'EntryFail'));
                                    } else {
                                        $db->commit();
                                        $mgReqDB->commit();
                                        $msg = parent::getSuccessMsg("SS008");
                                        $this->Flash->set($msg, array('key'=>'EntryOK'));
                                        $msg = parent::getSuccessMsg("SS018");
                                        $this->Flash->set($msg, array('key'=>'EntryOK'));
                                    }
                                } else {
                                    $msg = parent::getErrorMsg("SE059");
                                    $this->Flash->set($msg, array('key'=>'EntryFail'));
                                    CakeLog::write('debug', 'Manager email not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click `Request` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                }
                            } else {
                                $msg = parent::getErrorMsg("SE059");
                                $this->Flash->set($msg, array('key'=>'EntryFail'));
                                CakeLog::write('debug', 'Manager email not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click `Request` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            }
                        }
                        //}
                    } else {
                        $db->commit();
                        $mgReqDB->commit();
                        $msg = parent::getSuccessMsg("SS008", [__("依頼")]);
                        $this->Flash->set($msg, array('key'=>'EntryOK'));
                    }
                } catch (Exception $e) {
                    $db->rollback();
                    $mgReqDB->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE011", [__("依頼")]);
                    $this->Flash->set($msg, array('key'=>'EntryFail'));
                }
            } else {
                # if no approve data, then no data to request
                $msg = parent::getErrorMsg("SE017", [__("依頼")]);//no data to request
                $this->Flash->set($msg, array('key'=>'EntryFail'));
            }
        }
        $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
    }


    /**
     * File upload for sales department
     */
    public function uploadSalesFile()
    {
        $this->autoRender = false;
        $this->request->allowMethod('ajax');
        #only allow ajax request
        // parent::checkAjaxRequest($this);
        $error = false;
        $res_data = [];
        $save_file_info = [];
        
        if ($this->request->is('post')) {
            if (isset($this->request->data['File'])) {
                /*
                    Allow user level
                    admin = 1, sales's incharge person = 7
                */
                // $user_level = $this->Session->read('ADMIN_LEVEL_ID');
                // if ($user_level != 1 && $user_level != 7) {
                //     $check_permission = false;
                //     $msg = parent::getErrorMsg("SE016", [__("upload")]);
                //     $res_data = array(
                //         'error' => $msg
                //     );
                // }

                $action = $this->request->data['action'];//to decide save or update query
                $sample_id = $this->request->data['sample_data_id'];
                $sid = $this->request->data['sid'];
                $file = $this->request->data['File']['upload_file'];
                
                $count = count($file);
                $ext_arr = ['exe'];//not allow extension
                
                # check data is already approve(flag=4) or not before upload
                # if its approved, then can't upload
                $isApproved = $this->Sample->find('all', array(
                    'conditions' => array(
                        'Sample.id' => $sample_id,
                        'CAST(Sample.flag AS UNSIGNED) >=' => 4
                        )
                    ));
                    if (!empty($isApproved)) {
                        //can't upload files
                        $res_data = array(
                            'error'=> parent::getErrorMsg("SE027")
                        );
                    } else {
                        if ($count > 0) {
                            for ($i=0; $i<$count; $i++) {
                                $filePath = $file[$i]['tmp_name'];
                                $fileName = $file[$i]['name'];
                                $fileSize = $file[$i]['size'];
                                $fileInfo = pathinfo($file[$i]['name']);
                                $extension = $fileInfo['extension'];
                                
                                /* prepare to save file info */
                                $admin_id = $this->Session->read('LOGIN_ID');
                                $date = date('Y-m-d H:i:s');
                                $save_file_info['sample_id'] = $sample_id;
                                $save_file_info['status'] = 2;//for sales department
                                $save_file_info['file_name'] = $fileName;
                                $save_file_info['file_type'] = $extension;
                                $save_file_info['file_size'] = $fileSize;
                                $save_file_info['flag'] = 1;//active
                                $save_file_info['created_by'] = $admin_id;//active
                                $save_file_info['updated_by'] = $admin_id;//active
                                $save_file_info['created_date'] = $date;//active
                                
                                
                                #check file type
                                
                                if (in_array($extension, $ext_arr)) {
                                    $error = true;
                                    $message = parent::getErrorMsg("SE013", [implode(', ', $ext_arr)]);
                                    $res_data = array(
                                        'error' => $message
                                    );
                                    break;
                                }
                                #check file size (allow 10 MB)
                                if ($fileSize > 10485760) {
                                    $error = true;
                                    $message = parent::getErrorMsg("SE014");
                                    $res_data = array(
                                        'error' => $message
                                    );
                                }
                            }
                            
                        if (!$error) {
                            for ($i=0; $i<$count; $i++) {
                                $layer_code = $this->Session->read('SESSION_LAYER_CODE');
                                $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
                                $category = $this->Session->read('SAMPLECHECK_CATEGORY');
                                $year = date('Y', strtotime($period));
                                $month = date('m', strtotime($period));
                                $type = '営業';// for sales department
                                $page_name = $this->request->params['controller'];
                                $uploadFolderPath = CloudStorageInfo::FOLDER_NAME.'/Sample Check/'.$page_name.'/'.$layer_code.'/'.$category.'/'.$year.'/'.$month.'/'.$sid.'/'.$type.'/';

                                /* prepare to save file info */
                                $save_file_info['url'] = $uploadFolderPath;//url
                                
                                /* upload file to google cloud storage and save file into to sample_acc_attachments */
                                
                                $attachDB = $this->SampleAccAttachment->getDataSource();
                                try {
                                    $attachDB->begin();
                                    if ($action == 'save') {
                                        $this->SampleAccAttachment->create();
                                        $this->SampleAccAttachment->save($save_file_info);
                                    }
                                    if ($action == 'update') {
                                        $f_name = $attachDB->value($fileName, 'string');
                                        $f_type = $attachDB->value($extension, 'string');
                                        $f_size = $attachDB->value($fileSize, 'string');
                                        $updated_by = $attachDB->value($admin_id, 'string');
                                        $updated_date = $attachDB->value(date('Y-m-d H:i:s'), 'string');
                                        /* field to update */
                                        $update_arr['file_name'] = $f_name;
                                        $update_arr['file_type'] = $f_type;
                                        $update_arr['file_size'] = $f_size;
                                        $update_arr['updated_by'] = $updated_by;
                                        $update_arr['updated_date'] = $updated_date;
                                        /* condition to update */
                                        $cond['sample_id'] = $sample_id;
                                        $cond['status'] = 2;//for sales
                                        $cond['url'] = $uploadFolderPath;
                                        $cond['file_name'] = $fileName;
                                        $cond['flag'] = 1;
                                        $this->SampleAccAttachment->updateAll(
                                            $update_arr,
                                            $cond
                                        );
                                    }
                                    $isUpload = $this->__upload_object_to_cloud($fileName, $filePath, $uploadFolderPath);
                                    $attachDB->commit();

                                    $res_data = array(
                                        'file_name' => array(
                                            'name' => $fileName,
                                            'url' => $uploadFolderPath
                                        )
                                    );

                                    # to show success message when form reload
                                    $msg = parent::getSuccessMsg("SS007");
                                    $this->Flash->set($msg, array('key'=>'EntryOK'));
                                } catch (Exception $e) {
                                    $attachDB->rollback();
                                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                    $message = parent::getErrorMsg("SE015");
                                    $res_data = array(
                                        'error'=> $message
                                    );
                                }
                            }
                        }
                    } else {
                        $res_data = array(
                            'error'=> parent::getErrorMsg("SE008")
                        );
                    }
                }
            } else {
                $res_data = array(
                    'error'=> parent::getErrorMsg("SE014")
                );
            }
        }
        echo json_encode($res_data);exit();
    }

    /**
     * Upload a file.
     *
     * @param string $objectName the name of the object.
     * @param string $source the path to the file to upload.
     * @param string $folderStructure the path to save the file in cloud.
     *
     * @return Psr\Http\Message\StreamInterface
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
                'name' => $folderStructure.$objectName
            ]);
        } catch (GoogleException $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Download an object from Cloud Storage and save it as a local file.
     *
     * @param string $bucketName the name of your Google Cloud bucket.
     * @param string $objectName the name of your Google Cloud object.
     * @param string $destination the local destination to save the encrypted object.
     *
     * @return void
     */
    public function download_object_from_cloud()
    {
        $this->autoRender = false;
    
        if ($this->request->is('post')) {
            $url = $this->request->data['download_url'];//file_path
            $file_name = $this->request->data['download_file'];//file_name

            $url .= $file_name;// BA/2019/02/5/経理/220px-Vue.js_Logo_2.svg.png

            try {
                $cloud = parent::connect_to_google_cloud_storage();
                $storage = $cloud[0];
                $bucketName = $cloud[1];
                $bucket = $storage->bucket($bucketName);
                $object = $bucket->object($url);
                $stream = $object->downloadAsStream();
                header('Content-disposition: attachment; filename*=UTF-8\'\''.rawurlencode($file_name));
                echo $stream->getContents();
                exit();
            } catch (GoogleException $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE012");
                $this->Flash->set($msg, array('key'=>'EntryFail'));
                $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
        }
    }

    /**
     * Delete an object.
     *
     * @param string $bucketName the name of your Cloud Storage bucket.
     * @param string $objectName the name of your Cloud Storage object.
     * @param array $options
     *
     * @return void
     */
    public function delete_object_from_cloud()
    {
        $this->autoRender = false;
    
        if ($this->request->is('post')) {
            /*
                Allow user level
                admin = 1, sales's incharge person = 7
            */
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            if ($user_level != 1 && $user_level != 7) {
                $msg = parent::getErrorMsg("SE016", [__("delete")]);
                $this->Flash->set($msg, array('key'=>'EntryFail'));
                $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            }
            
            $admin_id = $this->Session->read('LOGIN_ID');//get from session

            $table_id = $this->request->data['attachment_id'];
            $url = $this->request->data['download_url'];//file_path
            $file_name = $this->request->data['download_file'];//file_name
            $url .= $file_name;// BA/2019/02/5/経理/220px-Vue.js_Logo_2.svg.png

            # check data is already approve or not before delete
            # if its approved, then can't delete
            $isApproved = $this->SampleAccAttachment->find('all', array(
                'conditions' => array(
                    'SampleAccAttachment.id' => $table_id,
                    'SampleAccAttachment.flag' => 1,
                    'CAST(Samples.flag AS UNSIGNED) >=' => 4
                ),
                'joins' => array(
                    array(
                        'table' => 'samples',
                        'alias' => 'Samples',
                        'type' => 'left',
                        'conditions' => 'SampleAccAttachment.sample_id = Samples.id'
                    )
                )
            ));
            
            if (!empty($isApproved)) {
                //can't delete files
                $msg = parent::getErrorMsg("SE028");
                $this->Flash->set($msg, array('key'=>'EntryFail'));
                $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            }

            $db = $this->SampleAccAttachment->getDataSource();
            try {
                $db->begin();

                $cloud = parent::connect_to_google_cloud_storage();
                $storage = $cloud[0];
                $bucketName = $cloud[1];
                $bucket = $storage->bucket($bucketName);
                $object = $bucket->object($url);
                if ($object->exists()) {
                    $object->delete();
                }
                
                /* field to update */
                $flag = $db->value(0, 'string');
                $updated_by = $db->value($admin_id, 'string');
                $updated_date = $db->value(date('Y-m-d H:i:s'), 'string');
                $record['flag'] = $flag;
                $record['updated_by'] = $updated_by;
                $record['updated_date'] = $updated_date;
                /* condition to update */
                $condition['SampleAccAttachment.id'] = $table_id;
                $condition['SampleAccAttachment.flag'] = 1;
                $condition['SampleAccAttachment.status'] = 2;//for sale file
                $this->SampleAccAttachment->updateAll(
                    $record,
                    $condition
                );
                $db->commit();
                $msg = parent::getSuccessMsg('SS003');
                $this->Flash->set($msg, array('key'=>'EntryOK'));
                $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            } catch (GoogleException $e) {
                $db->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE009");
                $this->Flash->set($msg, array('key'=>'EntryFail'));
                $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleDataEntry', 'action'=>'index'));
        }
    }
    
    public function getBaName($layer_code, $period = '')
    {
        $getBaName = $this->Layer->find('first', array(
            'fields' => array(
                'Layer.name_jp'
            ),
            'conditions' => array(
                'Layer.flag' => 1,
                'Layer.layer_code' => $layer_code,
                'Layer.type_order' => 3,
                'Layer.from_date <=' => date("Y-m-d", strtotime($period)),
                'Layer.to_date >=' => date("Y-m-d", strtotime($period))
            )
        ));
        return $getBaName['Layer']['name_jp'];
    }
}
