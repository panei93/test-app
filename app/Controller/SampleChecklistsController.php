<?php
ob_get_contents();// to clear POST Content length error when file upload
ob_end_clean();// to clear POST Content length error when file upload
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

App::uses('Controller', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

App::import('Controller', 'Common');
# Imports the Google Cloud client library
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\Exception\GoogleException;

define('PAGE', 'SampleChecklists');
define('MENUID', 2);
/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class SampleChecklistsController extends AppController
{
    public $uses = array('SampleChecklist','SampleTestResult','SampleAccAttachment','AdminLevelModel','Sample','Layer');

    public $components = array('PhpExcel.PhpExcel', 'Session' ,'Flash');
    public $helpers = array('Html', 'Form');

    public function beforeFilter()
    {
        parent::checkSampleUrlSession();#checkurlsession
    }
    public function index($errmessage = null)
    {
        $Common = NEW CommonController();
        $this->layout = 'samplecheck';
        $login_id = $this->Session->read('LOGIN_ID');
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $errorMsg   = "";
        $successMsg = "";
        $mail_deadline_date1 = "";
        $mail_deadline_date2 ="";

        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        # check user permission
        $permissions = $this->Session->read('PERMISSIONS');
        $status = $this->Sample->find('first',array(
            'conditions' => array(
                'Sample.flag NOT IN' => array('0', '10'),
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category' => $category
            ),
        ));
        $status = (!empty($status['Sample']['flag'])) ? $status['Sample']['flag'] : 0;
        $buttons = $Common->getButtonLists($status,$layer_code,$permissions);
        
        #get buttons
        $show_save = false;
        $show_review = false;
        $show_reject = false;
        $show_approve = false;
        $show_approve_cancel = false;
        // $custom_button_list = array();
        // $button_list = array_column($Common->getButtonList(PAGE, MENUID), 'Permissions');
        // $admin_level_list = array_column($button_list, 'role_id');
        
        $checkListuser =" ";

        //show first tab  table in checklist
        $checkListshow =$this->SampleChecklist->GetChecklistData($layer_code, $period, $category);
        $get_id = [];
        $count = count($checkListshow);
        $check_list_tab1 = array();
        for ($i =0 ; $i < $count; $i++) {
            $get_id[] = $checkListshow[$i]['tr']['sample_id'];
            if ($checkListshow[$i]['ch']['flag'] != '') {
                array_push($check_list_tab1, $checkListshow[$i]['ch']);
                if($checkListshow[$i]['ch']['flag'] == 1) {
                    $show_save = true;
                    $show_review = true;
                }else if($checkListshow[$i]['ch']['flag'] == 2) {
                    $show_reject = true;
                    $show_approve = true;
                }else if($checkListshow[$i]['ch']['flag'] == 3) {
                    $show_approve_cancel = true;
                }
            }else {
                $show_save = true;
            }
        }
        
        /* File upoading and donload show tab 1 */
        $data = [];
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        
        if (!empty($get_id)) {
            $checkListshow =  $this->__addFileListIntoArray($get_id, $checkListshow);
        }

        # check all data is finished(flag = 10) or not
        $isAllFinishFirstTab = $this->__checkDataFinishStatus($checkListshow);
        $this->set('isAllFinishFirstTab', $isAllFinishFirstTab);
        $this->set('checkListshowdata', $checkListshow);

        $tabChang_ch= $this->SampleChecklist->tabChangevalide_ch();
        $tabChang = $this->set('tabChang_ch', $tabChang_ch);

        /*secondtime report table texrarea (second tab )*/
        $SecondCheckListshow =$this->SampleChecklist->checkTabShow2($layer_code, $period, $category);
        $second_count = count($SecondCheckListshow);
        $sec_id = [];
        $show_save_tab2 = false;
        $show_review_tab2 = false;
        $show_reject_tab2 = false;
        $show_approve_tab2 = false;
        $show_approve_cancel_tab2 = false;
        for ($i=0; $i<$second_count; $i++) {
            $sec_id[] = $SecondCheckListshow[$i]['tr']['sample_id'];
            if(($checkListshow[$i]['ch']['flag'] == 1 || $checkListshow[$i]['ch']['flag'] == 4) && $checkListshow[$i]['sd']['flag'] == 7) {
                $show_save_tab2 = true;
            }else if($checkListshow[$i]['ch']['flag'] == 2) {
                $show_reject_tab2 = true;
                $show_approve_tab2 = true;
            }else if($checkListshow[$i]['ch']['flag'] == 3) {
                $show_approve_cancel_tab2 = true;
            }
            if($checkListshow[$i]['ch']['flag'] == 1) {
                $show_review_tab2 = true;
            }
        }

        // $button_list_sec = array_column($Common->getButtonList(PAGE, MENUID), 'Permissions');
        // $admin_level_list = array_column($button_list_sec, 'role_id');
        // foreach($button_list_sec as $key => $button){
        //     if($user_level == $admin_level_list[$key]){
        //         $show_button = ${'show_'.str_replace(' ', '', $button['function']).'_tab2'};
        //         $button_list_sec[$key]['show_flag'] = $show_button;
        //     }else{
        //         $button_list_sec[$key]['show_flag'] = '';
        //     }
        //     if($user_level == $button['role_id'] && $button['show_flag']){
        //         $action_function = str_replace(' ', '', $button['function']);
        //         $show_flag = $button['show_flag'];
        //     }
        // }
        if (!empty($sec_id)) {
            $SecondCheckListshow =  $this->__addFileListIntoArray($sec_id, $SecondCheckListshow);
        }
        # check all data is finished(flag = 10) or not
        $isAllFinishSecTab = $this->__checkDataFinishStatus($SecondCheckListshow);//$checkListshow
        $this->set('isAllFinishSecTab', $isAllFinishSecTab);
        $this->set('SecondCheckListshow', $SecondCheckListshow);

        /*texrarea comment check level*/
        $textaraCommentLevel =$this->SampleChecklist->textCommnetStageinfo();
        $textaraCommentdata = $this->set('textaraCommentLevel', $textaraCommentLevel);

        /*show comment texrarea*/
        $checkCommentfist =$this->SampleChecklist->getCheckComment($layer_code, $period, $category);
        $checkCommentfistshow= $this->set('checkCommentfist', $checkCommentfist);
        
        //tab hide checklist
        $tab_check_flag =$this->SampleChecklist->TabHideCh_flag($layer_code, $period, $category);
        
        $tab1ShowEnable = false;
        $tab1ShowDisable = false;
        $tab2ShowEnable = false;
        $cnt_tab_check_flag = count($tab_check_flag);

        for ($i=0; $i<$cnt_tab_check_flag; $i++) {
            $sam_flag = $tab_check_flag[$i]['sd']['flag'];
            $rp_1 = $tab_check_flag[$i]['tr']['report_necessary1'];
            $rp_2 = $tab_check_flag[$i]['tr']['report_necessary2'];
            if ($sam_flag >= 7 && $rp_1 != '' && $rp_2 == null) {
                $tab1ShowEnable = true;
            }
            if ($sam_flag >= 5 && $rp_1 != '' && $rp_2 != '') {
                $tab1ShowDisable = true; // after warp-up form tab 1 finish state
                $tab2ShowEnable = true;
            }
        }

        #Added by pan, to show deadline date for first tab in mail popup
        foreach ($checkListshow as $each) {
            $deadline = $each[0]['deadline_date1'];
            if (!empty($deadline) || $deadline!='') {
                #check when complete data have does not have sumission dead line
                if ($deadline != 0000-00-00) {
                    $date = '提出期日：'.$deadline.'<br/>';
                    $mail_deadline_date1 .= $date;
                } else {
                    $date = '';
                    $mail_deadline_date1 .= $date;
                }
            }
        }

        foreach ($SecondCheckListshow as $each) {
            $deadline = $each[0]['deadline_date2'];
            if (!empty($deadline) || $deadline!='') {
                #check when complete data have does not have sumission dead line
                if ($deadline != 0000-00-00) {
                    $date = '提出期日：'.$deadline.'<br/>';
                    $mail_deadline_date2 .= $date;
                } else {
                    $date = '';
                    $mail_deadline_date2 .= $date;
                }
            }
        }
        foreach($buttons as $key => $button){  
            $buttons[$key]     = ${'show_'.str_replace(' ', '', $key)};
            $sec_buttons[$key] = ${'show_'.str_replace(' ', '', $key).'_tab2'};
        }
        // pr($buttons);die();
        $this->set('page', PAGE);
        $this->set('buttons', $buttons);
        $this->set('sec_buttons', $sec_buttons);
        $this->set('tab_check_flag', $tab_check_flag);
        $this->set('tab1ShowEnable', $tab1ShowEnable);
        $this->set('tab1ShowDisable', $tab1ShowDisable);
        $this->set('tab2ShowEnable', $tab2ShowEnable);
        $this->set('sec_id', $sec_id);

        //sample id check but
        $sample_butcheck =$this->SampleChecklist->tab_butsampleID($data);
        
        $this->set('sample_butcheck', $sample_butcheck);
        //second tab hide
        $this->set('user_level', $user_level);
        $this->set('layer_name', $layer_name);
        $this->set('period', $period);
        $this->set('check_list_tab1', $check_list_tab1);
        $this->set('mail_deadline_date1', $mail_deadline_date1);
        $this->set('mail_deadline_date2', $mail_deadline_date2);
        
        $success=$this->set('successMsg');
        
        $this->render('index');
    }
    public function SaveCheckList()
    {
        $errorMsg   = "";
        $successMsg = "";
        $deadline_date = "";
        $login_user_name = $this->Session->read('LOGIN_USER');
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $user_id = $this->Session->read('LOGIN_ID');
        
        if ($this->request->is('post')) {
            $current_date = date('Y-m-d H:i:s');

            $data = $this->request->data;
            $to_email = $this->request->data['toEmail'];

            $count_sample = count($this->request->data['chk_sample_id']);

            # check some of the data is already request or not
            # if request, then not allow to save/update data anymore
            $check_request = $this->SampleChecklist->find('all', array(
                'conditions' => array(
                    'CAST(SampleChecklist.flag AS UNSIGNED) >=' => 2,
                    'SampleChecklist.sample_id IN' => $this->request->data['chk_sample_id']
                )
            ));
            if (!empty($check_request)) {
                $msg = parent::getErrorMsg('SE019');
                $this->Flash->set($msg, array('key'=>'cbFail'));
            }

            # check some of the data sample flag 7 only save
            # if request, then not allow to save/update data anymore
            $check_sap_flag = $this->Sample->find('all', array(
                'conditions' => array(
                    'CAST(flag AS UNSIGNED)' => 7,
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'Sample.layer_code' => $layer_code,
                    'Sample.category' => $category,
                    'id IN' => $this->request->data['chk_sample_id']
                )
            ));
            if (empty($check_sap_flag)) {
                $msg = parent::getErrorMsg('SE003');
                $this->Flash->set($msg, array('key'=>'cbFail'));
            }

            #check sample flag save buttom flag 7 when come from warpup page
            $isSaveTab1 = $this->Sample->find('all', array(
                'fields' => 'SampleTestResult.deadline_date1',
                'conditions' => array(
                    'Sample.flag ' => 7,
                    'SampleTestResult.flag ' => 3,
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'Sample.layer_code' => $layer_code,
                    'Sample.category' => $category,
                    'Sample.id IN' => $this->request->data['chk_sample_id']
                ),
                'joins' => array(
                    array(
                        'table' => 'sample_test_results',
                        'alias' => 'SampleTestResult',
                        'type' => 'left',
                        'conditions' =>array('Sample.id = SampleTestResult.sample_id')
                    )
                )
            ));

            if (empty($isSaveTab1)) {
                $msg = parent::getErrorMsg('SE017', [__('保存')]);
                $this->Flash->set($msg, array('key'=>'cbFail'));
            } else {
                //get submission deadline for mail content
                foreach ($isSaveTab1 as $each) {
                    $deadline = $each['SampleTestResult']['deadline_date1'];
                    if (!empty($deadline) || $deadline!='') {
                        $deadlineDate = new DateTime($deadline);
                        $date = '提出期日：'.date_format($deadlineDate, "Y-m-d").'<br/>';
                        $deadline_date .= $date;
                    }
                }
            }
 
            try {
                if($_POST['mailSend']) {
                    $toEmail = parent::formatMailInput($_POST['toEmail']);
                    $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                    $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                    
                    if (empty($toEmail)) {
                        $msg = parent::getErrorMsg("SE059");
                        $this->Flash->set($msg, array("key"=>"cbFail"));
                    } else {
                        #send email to sales incharge (level 7)
                        $mail_template = 'common';

                        #Mail contents
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body']  = $_POST['mailBody'];
                        
                        $url = '/SampleChecklists?period='.$period.'&ba='.$layer_code.'&category='.$category;

                        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                
                        if ($sentMail["error"]) {
                            $msg = $sentMail["errormsg"];
                            $this->Flash->set($msg, array('key'=>'cbFail'));
                            $invalid_email = parent::getErrorMsg('SE042');
                            $this->Flash->set($invalid_email, array('key'=>'cbFail'));
                        } else {
                            $arr = array();
                            for ($i=0 ; $i<$count_sample;$i++) {
                                $sample_id = $this->request->data["chk_sample_id"][$i];
                                $result_id = $this->request->data["check_result_id"][$i];

                                $sap_flag_validate = $this->Sample->find('all', array(
                                    'conditions' => array(
                                        'flag' => 7,
                                        'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                                        'Sample.layer_code' => $layer_code,
                                        'Sample.category' => $category,
                                        'id' => $sample_id
                                    )
                                ));
                                if (!empty($sap_flag_validate)) {
                                    $cmt = $this->request->data["checknew_comment"][$i];
                                } else {
                                    $cmt = '';
                                }
                                $chk_sample_id = $this->SampleChecklist->GetChkSampleId($sample_id);
                                if (count($chk_sample_id)> 0) {
                                    $this->SampleChecklist->UpdateCheckListData($sample_id, $cmt, $user_id);
                                } else {
                                    $arr[] = array(
                                        'sample_id' => $sample_id,
                                        'result_id' => $result_id,
                                        'improvement_situation1' => $cmt,
                                        'improvement_situation2' => '',
                                        'flag' => 1,
                                        'created_by' => $user_id,
                                        'updated_by' => $user_id,
                                        'created_date' =>$current_date,
                                        'updated_date' =>$current_date
                                    );
                                }
                            }

                            if (count($arr)> 0) {
                                $this->SampleChecklist->saveMany($arr);
                            }
                            $successMsg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                            $successMsg = parent::getSuccessMsg('SS018');
                            $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                        }
                    }
                }else {
                    $arr = array();
                            for ($i=0 ; $i<$count_sample;$i++) {
                                $sample_id = $this->request->data["chk_sample_id"][$i];
                                $result_id = $this->request->data["check_result_id"][$i];

                                $sap_flag_validate = $this->Sample->find('all', array(
                                    'conditions' => array(
                                        'flag' => 7,
                                        'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                                        'Sample.layer_code' => $layer_code,
                                        'Sample.category' => $category,
                                        'id' => $sample_id
                                    )
                                ));
                                if (!empty($sap_flag_validate)) {
                                    $cmt = $this->request->data["checknew_comment"][$i];
                                } else {
                                    $cmt = '';
                                }
                                $chk_sample_id = $this->SampleChecklist->GetChkSampleId($sample_id);
                                if (count($chk_sample_id)> 0) {
                                    $this->SampleChecklist->UpdateCheckListData($sample_id, $cmt, $user_id);
                                } else {
                                    $arr[] = array(
                                        'sample_id' => $sample_id,
                                        'result_id' => $result_id,
                                        'improvement_situation1' => $cmt,
                                        'improvement_situation2' => '',
                                        'flag' => 1,
                                        'created_by' => $user_id,
                                        'updated_by' => $user_id,
                                        'created_date' =>$current_date,
                                        'updated_date' =>$current_date
                                    );
                                }
                            }

                            if (count($arr)> 0) {
                                $this->SampleChecklist->saveMany($arr);
                            }
                            $successMsg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                }
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                $errorMsg = parent::getErrorMsg('SE003');
                $this->Flash->set($errorMsg, array('key'=>'cbFail'));
            }
        }

        $this->redirect(array('controller' =>'SampleChecklists','action'=>'index'));
    }
    public function ReviewCheckList()
    {
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $admin_id = $this->Session->read('LOGIN_ID');

        #get required field to update
        $improvement_situation1 = (!empty($this->request->data["checknew_comment"]))? $this->request->data["checknew_comment"] : [];
        if(count($improvement_situation1)>=1){
            $tmp_sample_id = $this->request->data["chk_sample_id"];
            $col = 0;
            foreach($improvement_situation1 as $imp_val){
                $this->SampleChecklist->UpdateCheckListData($tmp_sample_id[$col], $imp_val, $admin_id);
                $col++;
            }
        }

        $login_user_name = $this->Session->read('LOGIN_USER');
        $invalid_email = '';
        if (isset($this->request->data['chk_sample_id'])) {
            # first tab data
            $sample_id = $this->request->data['chk_sample_id'];
        }
        if (isset($this->request->data['ch_two_sampleid'])) {
            # second tab data
            $sample_id = $this->request->data['ch_two_sampleid'];
        }
        /* mail sent function*/

        $db = $this->SampleChecklist->getDataSource();

        try {
            $db->begin();
            $isReview =$this->SampleChecklist->review_secondApprove($sample_id, $admin_id);
            
            if ($isReview == true) {
                if($_POST['mailSend'] == 1) {
                    
                    $mail_template          = 'common';
                    #Mail contents
                    $mail['subject']        = $_POST['mailSubj'];
                    $mail['template_title'] = $_POST['mailTitle'];
                    $mail['template_body']  = $_POST['mailBody'];
                    $toEmail = parent::formatMailInput($_POST['toEmail']);
                    $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                    $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                    
                    if (!empty($toEmail)) {
                        $url = '/SampleChecklists?period='.$period.'&ba='.$layer_code.'&category='.$category;
                        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                    
                        if (($sentMail["error"])) {
                            $msg = $sentMail["errormsg"];
                                            
                            $invalid_email = parent::getErrorMsg('SE042');
                            $this->Flash->set($msg, array("key"=>"cbFail"));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        } else {
                            $db->commit();
                            $successMsg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($successMsg, array('key'=>'cbSuccess'));
                            $successMsg = parent::getSuccessMsg('SS018');
                            $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        }
                    } else {
                        $db->rollback();
                        $msg = parent::getErrorMsg("SE011", [__("レビュー。")]);
                        $msg .= " ".$invalid_email;
                        CakeLog::write('debug', 'Incharge(level 4) and Sub-Manager(level 3) informations are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        $this->Flash->set($msg, array("key"=>"cbFail"));
                        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                    }
                } else {
                    $db->commit();
                    $successMsg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                    $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                }
            }else {
                #review is false
                $msg = parent::getErrorMsg("SE011", [__("レビュー。")]);
                $this->Flash->set($msg, array("key"=>"cbFail"));
                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
            }
        }catch (Exception $e) {
            $db->rollback();
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                
            $msg = parent::getErrorMsg("SE011", [__("レビュー。")]);
            $msg .= " ".$invalid_email;
            $this->Flash->set($msg, array("key"=>"cbFail"));
            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
        }
        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
    }
    public function ApproveCheckList()
    {   
        $Common = new CommonController();
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $invalid_email = '';

        $chk_sample_id = $this->request->data('chk_sample_id');
        $cnt_sample_id = count($chk_sample_id);
        $user_id = $this->Session->read('LOGIN_ID');

        $dbChk = $this->SampleChecklist->getDataSource();

        try {
            $dbChk->begin();
            $isUpdated = $this->SampleChecklist->thirdApproveflag($chk_sample_id, $user_id);
            
            if ($isUpdated == true) {
                if($_POST['mailSend']) {
                    # send email to Busines In Charge (level 5)
                    $mail_template          = 'common';
                    #Mail contents
                    $mail['subject']        = $_POST['mailSubj'];
                    $mail['template_title'] = $_POST['mailTitle'];
                    $mail['template_body']  = $_POST['mailBody'];
                    $toEmail = parent::formatMailInput($_POST['toEmail']);
                    $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                    $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                    if (!empty($toEmail)) {
                        $url = '/SampleImprovementResults?period='.$period.'&ba='.$layer_code.'&category='.$category;
                        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

                        if ($sentMail["error"]) {
                            $msg = $sentMail["errormsg"];
                            $this->Flash->set($msg, array("key"=>"cbFail"));
                            $invalid_email = parent::getErrorMsg('SE058');
                            $this->Flash->set($invalid_email, array("key"=>"cbFail"));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        } else {
                            $dbChk->commit();
                            $successMsg = parent::getSuccessMsg('SS005');
                            $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                            $successMsg = parent::getSuccessMsg("SS018");
                            $this->Flash->set($successMsg, array('key'=>'cbSuccess'));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        }
                    } else {
                        $dbChk->commit();
                        $successMsg = parent::getSuccessMsg('SS005');
                        $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                    }
                }else {
                    $dbChk->commit();
                    $successMsg = parent::getSuccessMsg('SS005');
                    $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                    $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                }
            } else {
                throw new Exception();
            }
        }catch (Exception $e) {
            $dbChk->rollback();
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $msg = parent::getErrorMsg("SE011", [__("承認")]);
            $msg .= " ".$invalid_email;
            $this->Flash->set($msg, array("key"=>"cbFail"));
            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
        }
    }
    public function RejectCheckList()
    {
        $Common = new CommonController();
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $chk_sample_id = $this->request->data('chk_sample_id');
        $cnt_sample_id = count($chk_sample_id);
        $user_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');
        
        $chk_approve_flag = $this->Sample->find('all', array(
            'conditions' => array(
                'Sample.flag ' => 7,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category' => $category,
                'id IN' => $chk_sample_id
            )
        ));
        
        if (!empty($chk_approve_flag)) {
            $dbChk = $this->SampleChecklist->getDataSource();

            try {
                $dbChk->begin();
                $isCancel = $this->SampleChecklist->th_App_flagReject($chk_sample_id, $user_id, $layer_code, $period);
                
                if ($isCancel == true) {
                    if($_POST['mailSend']){
                        
                        $mail_template          = 'common';
                        #Mail contents
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body']  = $_POST['mailBody'];
                        
                        $toEmail = parent::formatMailInput($_POST['toEmail']);
                        $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                        $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                        $url = '/SampleChecklists?period='.$period.'&ba='.$layer_code.'&category='.$category;

                        if (!empty($toEmail)) {
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                            #mail validation
                            if ($sentMail["error"]) {
                                $msg = $sentMail["errormsg"];
                                $invalid_email = parent::getErrorMsg('SE058');
                                $this->Flash->set($msg, array("key"=>"cbFail"));
                                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                            } else {
                                $dbChk->commit();
                                $successMsg = parent::getSuccessMsg('SS014');
                                $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                                $successMsg = parent::getSuccessMsg("SS018");
                                $this->Flash->set($successMsg, array('key'=>'cbSuccess'));
                                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                            }
                        } else {
                            $dbChk->rollback();
                            CakeLog::write('debug', 'Incharge(level 4) and Sub-Manager(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$user_id. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $msg = parent::getErrorMsg("SE059");
                            $this->Flash->set($msg, array("key"=>"cbFail"));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        } 
                    }else {      
                        $dbChk->commit();
                        $successMsg = parent::getSuccessMsg('SS014');
                        $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                    }
                } else {
                    throw new Exception();
                }
            } catch (Exception $e) {
                $dbChk->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE017", [__("差し戻し")]);
                $msg .= " ".$invalid_email;
                $this->Flash->set($msg, array("key"=>"cbFail"));
                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
            }
        }
        /*old code not mail include end*/
        $msg = parent::getErrorMsg("SE017", [__("差し戻し")]);
        $this->Flash->set($msg, array("key"=>"cbFail"));
        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
    }
    public function Approve_CancelCheckList()
    {
        $Common = new CommonController();
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $chk_sample_id = $this->request->data('chk_sample_id');
        $cnt_sample_id = count($chk_sample_id);
        $user_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $invalid_email = '';

        /* email Get data */

        $to_email = $_POST['toEmail'];
        $cc_email = $_POST['ccEmail'];

        //flag 8 select data 4.11.2019 validate
        $chk_approve_flag = $this->Sample->find('all', array(
        'conditions' => array(
            'Sample.flag ' => 8,
            'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
            'Sample.layer_code' => $layer_code,
            'Sample.category' => $category,
            'Sample.id IN' => $chk_sample_id
        )
        ));
        
        //start mail
        if (!empty($chk_approve_flag)) {
            $dbChk = $this->SampleChecklist->getDataSource();

            try {
                $dbChk->begin();
                $isCancel = $this->SampleChecklist->th_App_flagCancle($chk_sample_id, $user_id, $layer_code, $period);
                
                if ($isCancel == true) {
                    if($_POST['mailSend']) {
                        $mail_template          = 'common';
                        #Mail contents
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body']  = $_POST['mailBody'];
                        $toEmail = parent::formatMailInput($_POST['toEmail']);
                        $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                        $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                        $url = '/SampleChecklists?period='.$period.'&ba='.$layer_code.'&category='.$category;

                        if (!empty($toEmail)) {
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                            #mail validation
                            if ($sentMail["error"]) {
                                $msg = $sentMail["errormsg"];
                                $invalid_email = parent::getErrorMsg('SE058');
                                $this->Flash->set($msg, array("key"=>"cbFail"));
                                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));;
                            } else {
                                $dbChk->commit();
                                $successMsg = parent::getSuccessMsg('SS006');
                                $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                                $successMsg = parent::getSuccessMsg("SS018");
                                $this->Flash->set($successMsg, array('key'=>'cbSuccess'));
                                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                            }
                        } else {
                            CakeLog::write('debug', 'Incharge(level 4) and Sub-Manager(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$user_id. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $msg = parent::getErrorMsg("SE059");
                            $this->Flash->set($msg, array("key"=>"cbFail"));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        }
                    } else {
                        $dbChk->commit();
                        $successMsg = parent::getSuccessMsg('SS006');
                        $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                    }               
                    $dbChk->rollback();
                    $msg = parent::getErrorMsg("SE017", [__("承認キャンセル")]);
                    $this->Flash->set($msg, array("key"=>"cbFail"));
                    $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                } else {
                    throw new Exception();
                }
            } /* end try*/
            catch (Exception $e) {
                $dbChk->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE017", [__("承認キャンセル")]);
                $msg .= " ".$invalid_email;
                $this->Flash->set($msg, array("key"=>"cbFail"));
                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
            }
            //mail end
        }
        /*old code not mail include end*/
        $msg = parent::getErrorMsg("SE017", [__("承認キャンセル")]);
        $this->Flash->set($msg, array("key"=>"cbFail"));
        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
    }
    // second tab
    public function SaveCheckListTab2()
    {   
        $errorMsg   = "";
        $successMsg = "";
        $deadline_date = "";
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $user_id = $this->Session->read('LOGIN_ID');

        if ($this->request->is('post')) {
            $date = date('Y-m-d H:i:s');

            $data = $this->request->data;
            $to_email = $this->request->data['toEmail'];

            # check some of the data is already request or not
            # if request, then not allow to save/update data anymore
            $check_request = $this->SampleChecklist->find('all', array(
                'conditions' => array(
                    'OR' => array(
                        array('SampleChecklist.flag' => 2),
                        array('SampleChecklist.flag' => 3)
                    ),
                    'SampleChecklist.sample_id IN' => $this->request->data['ch_two_sampleid']
                )
            ));

            if (!empty($check_request)) {
                $msg = parent::getErrorMsg('SE019');
                $this->Flash->set($msg, array('key'=>'cbFail'));
            }

            # check some of the data sample flag 7 only save
            # if request, then not allow to save/update data anymore
            $check_sap_flag = $this->Sample->find('all', array(
                'conditions' => array(
                    'Sample.flag >' => 7,
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'Sample.layer_code' => $layer_code,
                    'Sample.category' => $category,
                    'Sample.id IN' => $this->request->data['ch_two_sampleid']
                )
            ));
            if (!empty($check_sap_flag)) {
                $msg = parent::getErrorMsg('SE018');
                $this->Flash->set($msg, array('key'=>'cbFail'));
            }

            #check sample flag save buttom flag 7 when come from warpup page
            $isSaveTab2 = $this->Sample->find('all', array(
                'conditions' => array(
                    'Sample.flag ' => 7,
                    'SampleTestResult.flag ' => 3,
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'Sample.layer_code' => $layer_code,
                    'Sample.category' => $category,
                    'Sample.id IN' => $this->request->data['ch_two_sampleid']
                ),
                'joins' => array(
                    array(
                        'table' => 'sample_test_results',
                        'alias' => 'SampleTestResult',
                        'type' => 'left',
                        'conditions' =>array('Sample.id = SampleTestResult.sample_id')
                    )
                )
            ));

            if (empty($isSaveTab2)) {
                $msg = parent::getErrorMsg('SE017', [__('保存')]);
                $this->Flash->set($msg, array('key'=>'cbFail'));
                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
            } else {
                //get submission deadline for mail content
                foreach ($isSaveTab2 as $each) {
                    $deadline = $each['Sample']['submission_deadline_date'];
                    if (!empty($deadline) || $deadline!='') {
                        $deadlineDate = new DateTime($deadline);
                        $date = '提出期日：'.date_format($deadlineDate, "Y-m-d").'<br/>';
                        $deadline_date .= $date;
                    }
                }
            }

            $ch_two_sampleid = count($this->request->data["ch_two_sampleid"]);

            try {
                if($_POST['mailSend']) {
                    $mail_template          = 'common';
                    #Mail contents
                    $mail['subject']        = $_POST['mailSubj'];
                    $mail['template_title'] = $_POST['mailTitle'];
                    $mail['template_body']  = $_POST['mailBody'];
                    $toEmail = parent::formatMailInput($_POST['toEmail']);
                    $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                    $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                    if (empty($toEmail)) {
                        $msg = parent::getErrorMsg("SE059");
                        $this->Flash->set($msg, array("key"=>"cbFail"));
                        $this->redirect(array('action' => 'index'));
                    } else {
                        
                        $url = '/SampleChecklists?period='.$period.'&ba='.$layer_code.'&category='.$category;

                        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                
                        if ($sentMail["error"]) {
                            $msg = $sentMail["errormsg"];
                            $this->Flash->set($msg, array('key'=>'cbFail'));
                            $invalid_email = parent::getErrorMsg('SE042');
                            $this->Flash->set($invalid_email, array('key'=>'cbFail'));
                        } else {
                            $param = array();

                            for ($i=0 ; $i<$ch_two_sampleid; $i++) {
                                $sample_id = $this->request->data["ch_two_sampleid"][$i];
                                $result_id = $this->request->data["ch_two_resultid"][$i];
                                
                                if (isset($this->request->data["checknew_commenttwo"][$i])) {
                                    $cmt_2 = $this->request->data["checknew_commenttwo"][$i];
                                } else {
                                    $cmt_2 ='';
                                }

                                # when test result  data flag 10 or 7 disable box in save data validation (browser cache) 28.06.2019
                                $sap_flag_validate = $this->Sample->find('all', array(
                                    'conditions' => array(
                                        'Sample.flag' => 7,
                                        'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                                        'Sample.layer_code' => $layer_code,
                                        'Sample.category' => $category,
                                        'Sample.id' => $sample_id
                                    )
                                ));
                                #test sample data flag 10 and 7 condition state add save data

                                if (!empty($sap_flag_validate)) {
                                    $cmt_2 = $this->request->data["checknew_commenttwo"][$i];
                                } else {
                                    $cmt_2 = '';
                                }
                                
                                $chk_sample_id = $this->SampleChecklist->find('all', array(
                                    'conditions' => array(
                                        'OR' => array(
                                            array('SampleChecklist.flag' => 4),
                                            array('SampleChecklist.flag' => 1)
                                        ),
                                        'SampleChecklist.sample_id' => $sample_id
                                    )
                                ));
                                
                                if (count($chk_sample_id)> 0) {
                                    $this->SampleChecklist->UpdateCheckListData_II($sample_id, $cmt_2, $user_id);
                                } else {
                                    if ($cmt_2 != null && $cmt_2 != '') {
                                        $param[] = array(
                                            'sample_id' => $sample_id,
                                            'result_id' => $result_id,
                                            'improvement_situation1' => '',
                                            'improvement_situation2' => $cmt_2,
                                            'flag' => 1,
                                            'created_by' => $user_id,
                                            'updated_by' => $user_id,
                                            'created_date' =>$date,
                                            'updated_date' =>$date
                                        );
                                    }
                                }
                            }
                            
                            /*---------------------------------------*/
                            if (count($param)> 0) {
                                $this->SampleChecklist->saveMany($param);
                            }
                            $successMsg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                            $successMsg = parent::getSuccessMsg('SS018');
                            $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                        }
                    }
                }else {
                    $param = array();

                    for ($i=0 ; $i<$ch_two_sampleid; $i++) {
                        $sample_id = $this->request->data["ch_two_sampleid"][$i];
                        $result_id = $this->request->data["ch_two_resultid"][$i];
                        
                        if (isset($this->request->data["checknew_commenttwo"][$i])) {
                            $cmt_2 = $this->request->data["checknew_commenttwo"][$i];
                        } else {
                            $cmt_2 ='';
                        }

                        # when test result  data flag 10 or 7 disable box in save data validation (browser cache) 28.06.2019
                        $sap_flag_validate = $this->Sample->find('all', array(
                            'conditions' => array(
                                'Sample.flag' => 7,
                                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                                'Sample.layer_code' => $layer_code,
                                'Sample.category' => $category,
                                'Sample.id' => $sample_id
                            )
                        ));
                        #test sample data flag 10 and 7 condition state add save data

                        if (!empty($sap_flag_validate)) {
                            $cmt_2 = $this->request->data["checknew_commenttwo"][$i];
                        } else {
                            $cmt_2 = '';
                        }
                        
                        $chk_sample_id = $this->SampleChecklist->find('all', array(
                            'conditions' => array(
                                'OR' => array(
                                    array('SampleChecklist.flag' => 4),
                                    array('SampleChecklist.flag' => 1)
                                ),
                                'SampleChecklist.sample_id' => $sample_id
                            )
                        ));
                        
                        if (count($chk_sample_id)> 0) {
                            $this->SampleChecklist->UpdateCheckListData_II($sample_id, $cmt_2, $user_id);
                        } else {
                            if ($cmt_2 != null && $cmt_2 != '') {
                                $param[] = array(
                                    'sample_id' => $sample_id,
                                    'result_id' => $result_id,
                                    'improvement_situation1' => '',
                                    'improvement_situation2' => $cmt_2,
                                    'flag' => 1,
                                    'created_by' => $user_id,
                                    'updated_by' => $user_id,
                                    'created_date' =>$date,
                                    'updated_date' =>$date
                                );
                            }
                        }
                    }
                    
                    /*---------------------------------------*/
                    if (count($param)> 0) {
                        $this->SampleChecklist->saveMany($param);
                    }
                    $successMsg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                }
                
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                $errorMsg = parent::getErrorMsg('SE003');
                $this->Flash->set($errorMsg, array('key'=>'cbFail'));
            }
        }
        
        $this->redirect(array('controller' =>'SampleChecklists','action'=>'index'));
    }
    public function ReviewCheckListTab2()
    {
        $Common = new CommonController;

        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $admin_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');

        #get required field to update
        $improvement_situation2 = (!empty($this->request->data["checknew_commenttwo"]))? $this->request->data["checknew_commenttwo"] : [];
        if(count($improvement_situation2)>=1){
            $tmp_sample_id = $this->request->data["chk_sample_id"];
            $col = 0;
            foreach($improvement_situation2 as $imp_val){
                $this->SampleChecklist->UpdateCheckListData_II($tmp_sample_id[$col], $imp_val, $admin_id);
                $col++;
            }
        }

        if (isset($this->request->data['chk_sample_id'])) {
            # first tab data
            $sample_id = $this->request->data['chk_sample_id'];
        }
        if (isset($this->request->data['ch_two_sampleid'])) {
            # second tab data
            $sample_id = $this->request->data['ch_two_sampleid'];
        }
        /* mail sent function*/

        $db = $this->SampleChecklist->getDataSource();

        try {
            $db->begin();
            $isReview =$this->SampleChecklist->review_secondApprove($sample_id, $admin_id);

            if ($isReview == true) {
                if($_POST['mailSend']) {
                    $mail_template          = 'common';
                    #Mail contents
                    $mail['subject']        = $_POST['mailSubj'];
                    $mail['template_title'] = $_POST['mailTitle'];
                    $mail['template_body']  = $_POST['mailBody'];
                    $toEmail = parent::formatMailInput($_POST['toEmail']);
                    $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                    $bccEmail = parent::formatMailInput($_POST['bccEmail']);

                    $url = '/SampleChecklists?period='.$period.'&ba='.$layer_code.'&category='.$category;
                    if (!empty($toEmail)) {
                        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

                                    
                        if (($sentMail["error"])) {
                            $msg = $sentMail["errormsg"];
                                            
                            $invalid_email = parent::getErrorMsg('SE042');
                            $this->Flash->set($msg, array("key"=>"cbFail"));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        } else {
                            $db->commit();
                            $successMsg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                            $successMsg = parent::getSuccessMsg("SS018");
                            $this->Flash->set($successMsg, array('key'=>'cbSuccess'));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        }
                    } else {
                        CakeLog::write('debug', 'Incharge(level 4) and Sub-Manager(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        $msg = parent::getErrorMsg("SE059");
                        $this->Flash->set($msg, array("key"=>"cbFail"));
                        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                    }
                }else {
                    $db->commit();
                    $successMsg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                    $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                }
                
                $db->rollback();
                $msg = parent::getErrorMsg("SE011", [__("レビュー。")]);
                $msg .= " ".$invalid_email;
                $this->Flash->set($msg, array("key"=>"cbFail"));
                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
            } else {

                #review is false
                $msg = parent::getErrorMsg("SE011", [__("レビュー。")]);
                $this->Flash->set($msg, array("key"=>"cbFail"));
                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
            }
        }
        catch (Exception $e) {
            $db->rollback();
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                
            $msg = parent::getErrorMsg("SE011", [__("レビュー。")]);
            $msg .= " ".$invalid_email;
            $this->Flash->set($msg, array("key"=>"cbFail"));
            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
        }

        
        $msg = parent::getErrorMsg("SE011", [__("レビュー。")]);
        $this->Flash->set($msg, array("key"=>"cbFail"));
        CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
    }
    public function ApproveCheckListTab2()
    {
        $Common = new CommonController();
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $chk_sample_id = $this->request->data['ch_two_sampleid'];
        $cnt_sample_id = count($chk_sample_id);
        $user_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');

        $dbChk = $this->SampleChecklist->getDataSource();

        try {
            $dbChk->begin();
            $isUpdated = $this->SampleChecklist->thirdApproveflag($chk_sample_id, $user_id);

            if ($isUpdated == true) {
                $mail_template          = 'common';
                #Mail contents
                $mail['subject']        = $_POST['mailSubj'];
                $mail['template_title'] = $_POST['mailTitle'];
                $mail['template_body']  = $_POST['mailBody'];
                $toEmail = parent::formatMailInput($_POST['toEmail']);
                $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                $bccEmail = parent::formatMailInput($_POST['bccEmail']);            
                
                if($_POST['mailSend']) {
                    $url = '/SampleImprovementResults?period='.$period.'&ba='.$layer_code.'&category='.$category;
                    if (!empty($toEmail)) {
                        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                        

                        if ($sentMail["error"]) {
                            $msg = $sentMail["errormsg"];
                            $this->Flash->set($msg, array("key"=>"cbFail"));
                            $invalid_email = parent::getErrorMsg('SE058');
                            $this->Flash->set($invalid_email, array("key"=>"cbFail"));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        } else {
                            $dbChk->commit();
                            $successMsg = parent::getSuccessMsg('SS005');
                            $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                            $successMsg = parent::getSuccessMsg('SS018');
                            $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        }
                    }
                } else {
                    $dbChk->commit();          
                    $successMsg = parent::getSuccessMsg('SS005');
                    $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                    $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                }
            } else {
                throw new Exception();
            }
        }
        catch (Exception $e) {
            $dbChk->rollback();
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $msg = parent::getErrorMsg("SE011", [__("承認")]);
            $msg .= " ".$invalid_email;
            $this->Flash->set($msg, array("key"=>"cbFail"));
            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
        }
    }
     public function RejectCheckListTab2()
    {
        $Common = new CommonController;
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $chk_sample_id = $this->request->data['ch_two_sampleid'];
        $cnt_sample_id = count($chk_sample_id);
        $user_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');

        /* email Get data */
        $to_email = $_POST['toEmail'];
        $cc_email = $_POST['ccEmail'];
        $mail['subject']        = $_POST['mailSubj'];
        $mail['template_title'] = $_POST['mailTitle'];
        $mail['template_body']  = $_POST['mailBody'];

        //check flag 8 when approve cancle
        $chk_approve_flag = $this->Sample->find('all', array(
            'conditions' => array(
                'flag ' => 7,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category' => $category,
                'id IN' => $chk_sample_id
            )
        ));

        /* mail include function*/

        if (!empty($chk_approve_flag)) {
            $dbChk = $this->SampleChecklist->getDataSource();

            try {
                $dbChk->begin();
                $isCancel = $this->SampleChecklist->th_App_flagReject($chk_sample_id, $user_id);

                if ($isCancel == true) {
                                
                    if($_POST['mailSend']) {
                        $mail_template          = 'common';
                        #Mail contents
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body']  = $_POST['mailBody'];
                        
                        $toEmail = parent::formatMailInput($to_email);
                        $ccEmail = parent::formatMailInput($cc_email);
                        $bccEmail = parent::formatMailInput($cc_email);
                        $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code;
                        if (!empty($toEmail)) {
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
    
                            if ($sentMail["error"]) {
                                $successMsg = $sentMail["errormsg"];
                                $this->Flash->set($successMsg, array("key"=>"cbFail"));
                                $this->redirect(array('action' => 'index'));
                                $invalid_email = parent::getErrorMsg('SE058');
                            } else {
                                #if email have save data
                                $dbChk->commit();
                                    
                                $successMsg = parent::getSuccessMsg('SS014');
                                $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                                $successMsg = parent::getSuccessMsg('SS018');
                                $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                            }
                        } else {
                            CakeLog::write('debug', 'Incharge(level 4) and Sub-Manager(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$user_level. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $msg = parent::getErrorMsg("SE059", [__("差し戻し")]);
                            $this->Flash->set($msg, array("key"=>"cbFail"));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        }
                    }else {
                        $dbChk->commit();
                        $successMsg = parent::getSuccessMsg('SS014');
                        $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                    }              
                } else {
                    throw new Exception();
                }
            }
            catch (Exception $e) {
                $dbChk->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE011", [__("差し戻し")]);
                $msg .= " ".$invalid_email;
                $this->Flash->set($msg, array("key"=>"cbFail"));
                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
            }

            /* mail include function end*/
        }

        $msg = parent::getErrorMsg("SE017", [__("承認キャンセル")]);
        $this->Flash->set($msg, array("key"=>"cbFail"));
        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
    }
    public function Approve_CancelCheckListTab2()
    {
        $Common = new CommonController;
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $chk_sample_id = $this->request->data['ch_two_sampleid'];
        $cnt_sample_id = count($chk_sample_id);
        $user_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');

        //check flag 8 when approve cancle
        $chk_approve_flag = $this->Sample->find('all', array(
            'conditions' => array(
                'flag ' => 8,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category' => $category,
                'id IN' => $chk_sample_id
            )
        ));

        /* mail include function*/

        if (!empty($chk_approve_flag)) {
            $dbChk = $this->SampleChecklist->getDataSource();

            try {
                $dbChk->begin();
                $isCancel = $this->SampleChecklist->th_App_flagCancle($chk_sample_id, $user_id);

                if ($isCancel == true) {
                    if($_POST['mailSend']) {
                        $mail_template          = 'common';
                        #Mail contents
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body']  = $_POST['mailBody'];
                        $toEmail = parent::formatMailInput($_POST['toEmail']);
                        $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                        $bccEmail = parent::formatMailInput($_POST['bccEmail']);       
                        $url = '/SampleChecklists?period='.$period.'&ba='.$layer_code.'&category='.$category;
                        if (!empty($toEmail)) {
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

                            if ($sentMail["error"]) {
                                $successMsg = $sentMail["errormsg"];
                                $this->Flash->set($successMsg, array("key"=>"cbFail"));
                                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                                $invalid_email = parent::getErrorMsg('SE058');
                            } else {
                                #if email have save data
                                $dbChk->commit();
                                    
                                $successMsg = parent::getSuccessMsg('SS006');
                                $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                                $successMsg = parent::getSuccessMsg('SS018');
                                $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                            }
                        } else {
                            CakeLog::write('debug', 'Incharge(level 4) and Sub-Manager(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$user_level. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $msg = parent::getErrorMsg("SE058", [__("依頼")]);
                            $this->Flash->set($msg, array("key"=>"cbFail"));
                            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                        }   
                    }else {
                        $dbChk->commit();
                        $successMsg = parent::getSuccessMsg('SS006');
                        $this->Flash->set($successMsg, array("key"=>"cbSuccess"));
                        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                    }
                                     
                    $dbChk->rollback();
                    $msg = parent::getErrorMsg("SE011", [__("承認キャンセル")]);
                    $this->Flash->set($msg, array("key"=>"cbFail"));
                    $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                } else {
                    throw new Exception();
                }
            } /* end try*/
            catch (Exception $e) {
                $dbChk->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE011", [__("承認キャンセル")]);
                $msg .= " ".$invalid_email;
                $this->Flash->set($msg, array("key"=>"cbFail"));
                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
            }

            /* mail include function end*/
        }

        $msg = parent::getErrorMsg("SE017", [__("承認キャンセル")]);
        $this->Flash->set($msg, array("key"=>"cbFail"));
        $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
    }
    public function uploadAccountFile()
    {
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $error = false;
        $response = [];
        $save_file_info = [];
        if ($this->request->is('post') && !empty($this->request->data)) {
            $find_id = [];
            $id = $this->request->data['check_sample_id'];
            
            if (!is_array($id)) {
                $find_id[] = $id;
            } else {
                $find_id = $id;
            }

            $sap_find_flag = $this->Sample->find('all', array(
                'conditions' => array(
                    'Sample.flag ' => 7,
                    'SampleTestResult.flag ' => 3,
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'Sample.layer_code' => $layer_code,
                    'Sample.category' => $category,
                    'Sample.id IN' => $find_id
                ),
                'joins' => array(
                    array(
                        'table' => 'sample_test_results',
                        'alias' => 'SampleTestResult',
                        'type' => 'left',
                        'conditions' =>array('Sample.id = SampleTestResult.sample_id')
                    )
                ),
                
            ));

            $chk_find_flag = $this->SampleChecklist->find('all', array(
                'conditions' => array(
                    'OR' => array(
                        array('SampleChecklist.flag'=>2),//1
                        array('SampleChecklist.flag'=>3)//4
                    ),
                    'SampleChecklist.sample_id IN' => $find_id
                )
            ));

            if (!empty($sap_find_flag) && empty($chk_find_flag)) {
                //(empty($chk_find_flag) || !empty($chk_find_flag))
                if (isset($this->request->data['File'])) {
                    $action = $this->request->data['action'];//to decide save or update query
                    $sample_id = $this->request->data['check_sample_id'];
                    $sid = $this->request->data['sid'];
                    $file = $this->request->data['File']['upload_file'];
                    $count = count($file);

                    $ext_arr = ['exe'];//not allow extension
                    
                    if ($count > 0) {
                        for ($i=0; $i<$count; $i++) {
                            if (!empty($file[$i]['name'])) {
                                $filePath = $file[$i]['tmp_name'];
                                $fileName = $file[$i]['name'];
                                $fileSize = $file[$i]['size'];
                                $fileInfo = pathinfo($file[$i]['name']);
                                $extension = $fileInfo['extension'];

                                /* prepare to save file info */
                                $admin_id = $this->Session->read('LOGIN_ID'); //get login id
                                $date = date('Y-m-d H:i:s');
                                $save_file_info['sample_id'] = $sample_id;
                                $save_file_info['status'] = 3;//for business
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
                                    $response = array(
                                        'error' => $message
                                    );
                                    break;
                                }
                                #check file size (allow 10 MB)
                                if ($fileSize > 10485760) {
                                    $error = true;
                                    $message = parent::getErrorMsg("SE014");
                                    $response = array(
                                        'error' => $message
                                    );
                                }
                            }
                        }

                        if (!$error) {
                            for ($i=0; $i<$count; $i++) {
                                // $period = '2019-02';
                                //  $layer_code = '8003'; //from cookie
                                $year = date('Y', strtotime($period));
                                $month = date('m', strtotime($period));
                                $type = '営業';
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
                                        $attach_id = $this->SampleAccAttachment
                                        ->id;
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
                                        $cond['status'] = 3;//for business
                                        $cond['url'] = $uploadFolderPath;
                                        $cond['file_name'] = $fileName;
                                        $cond['SampleAccAttachment.flag'] = 1;
                                        $this->SampleAccAttachment->updateAll(
                                            $update_arr,
                                            $cond
                                        );
                                        //get updated id
                                        $attach_id = $this->SampleAccAttachment->find('first', array(
                                            'conditions' => array(
                                                'sample_id' => $sample_id,
                                                'status' => 3,
                                                'url' => $uploadFolderPath,
                                                'file_name' => $fileName,
                                                'SampleAccAttachment.flag' => 1
                                            ),
                                            'fields' => array('id')
                                        ));
                                        if (!empty($attach_id)) {
                                            $attach_id = $attach_id['SampleAccAttachment']['id'];
                                        }
                                    }
                                    $isUpload = $this->upload_object_to_cloud($fileName, $filePath, $uploadFolderPath);
                                    $attachDB->commit();

                                    # to show success message
                                    $msg = parent::getSuccessMsg("SS007");
                                    $response = array(
                                        'file_name' => array(
                                            'name' => $fileName,
                                            'url' => $uploadFolderPath,
                                            'attach_id' => $attach_id,
                                            'sample_id' => $sample_id,
                                            'success' => $msg
                                        )
                                    );
                                } catch (Exception $e) {
                                    $attachDB->rollback();
                                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                    $message = parent::getErrorMsg("SE015");
                                    $response = array(
                                        'error'=> $message
                                    );
                                }
                            }
                        }
                    } else {
                        $response = array(
                            'error'=> parent::getErrorMsg("SE008")
                        );
                    }
                } else {
                    $response = array(
                        'error'=> parent::getErrorMsg("SE008")
                    );
                }
            } else {
                CakeLog::write('debug', 'conditions is not match to upload file in '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $response = array(
                    'error'=> parent::getErrorMsg("SE015")
                );
            }
        } else {
            $response = array(
                'error'=> parent::getErrorMsg("SE014")
            );
        }
        echo json_encode($response);
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
    public function upload_object_to_cloud($objectName, $source, $folderStructure)
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
                // header("Content-disposition: attachment; filename=\"".$file_name."\"");
                echo $stream->getContents();
                exit();
            } catch (GoogleException $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE012");
                
                $this->Flash->set($msg, array('key'=>'cbFail'));
                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
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
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');

        $this->autoRender = false;

        if ($this->request->is('post')) {
            $admin_id = $this->Session->read('LOGIN_ID');//get from session

            $table_id = $this->request->data['attachment_id'];
            
            $url = $this->request->data['download_url'];//file_path
            $file_name = $this->request->data['download_file'];//file_name
            $url .= $file_name;// BA/2019/02/5/経理/220px-Vue.js_Logo_2.svg.png

            //flag validate when file  delete file .

            $find_id = [];
            $table_id = $this->request->data['attachment_id'];


            if (!is_array($table_id)) {
                $find_id[] = $table_id;
            } else {
                $find_id = $table_id;
            }


            $sap_find_flag = $this->Sample->find('all', array(
                'conditions' => array(
                    'Sample.flag ' => 7,
                    'SampleTestResult.flag ' => 3,
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'Sample.layer_code' => $layer_code,
                    'Sample.category' => $category,
                    'SampleAccAttachment.id IN' => $find_id
                ),
                'joins' => array(
                    array(
                        'table' => 'sample_test_results',
                        'alias' => 'SampleTestResult',
                        'type' => 'left',
                        'conditions' =>array('Sample.id = SampleTestResult.sample_id')
                    ),
                    array(
                        'table' => 'sample_acc_attachments',
                        'alias' => 'SampleAccAttachment',
                        'type' => 'left',
                        'conditions' =>array('Sample.id = SampleAccAttachment.sample_id')
                    )
                )
                
            ));
         
            if (!empty($sap_find_flag)) {
                $tmp = array_column($sap_find_flag, 'Sample');
                $sap_id_arr = array_column($tmp, 'id');
            } else {
                $sap_id_arr[] = '';
            }

            $chk_find_flag = $this->SampleChecklist->find('all', array(
                'conditions' => array(
                    'OR' => array(
                        array('SampleChecklist.flag'=>2),//1
                        array('SampleChecklist.flag'=>3)//4
                    ),
                    'SampleChecklist.sample_id IN' => $sap_id_arr
                )
            ));
           
            if (!empty($sap_find_flag) && empty($chk_find_flag)) {
                //(empty($chk_find_flag) || !empty($chk_find_flag))
                //try catch
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
                    $record['SampleAccAttachment.flag'] = $flag;
                    $record['updated_by'] = $updated_by;
                    $record['updated_date'] = $updated_date;
                    /* condition to update */
                    $condition['SampleAccAttachment.id'] = $table_id;
                    $condition['SampleAccAttachment.flag'] = 1;
                    $condition['status'] = array(2,3);//for business files
                    $this->SampleAccAttachment->updateAll(
                        $record,
                        $condition
                    );
                    $db->commit();
                    $msg = parent::getSuccessMsg('SS003');
                    $this->Flash->set($msg, array('key'=>'cbSuccess'));
                    $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                } catch (GoogleException $e) {
                    $db->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE009");

                    $this->Flash->set($msg, array('key'=>'cbFail'));

                    $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
                }//try catch end
            } else {
                $msg = parent::getErrorMsg("SE009");

                $this->Flash->set($msg, array('key'=>'cbFail'));

                $this->redirect(array('controller'=>'SampleChecklists', 'action'=>'index'));
            }
        }
    }
    public function __checkDataFinishStatus($arr)
    {
        $isFinished = true;
        $cnt_row = count($arr);
        for ($i=0; $i<$cnt_row; $i++) {
            $sample_flag = $arr[$i]['sd']['flag'];
            if ($sample_flag != 10) {
                $isFinished = false;
            }
        }
        return $isFinished;
    }
    public function __addFileListIntoArray($get_id, $checkListshow)
    {
        /* get attachment */
        $file = $this->SampleAccAttachment->find('all', array(
            'conditions' => array(
                'SampleAccAttachment.status' => 1,
                'SampleAccAttachment.flag' => 1,
                'SampleAccAttachment.sample_id IN' => $get_id
            )
        ));

        $file = array_column($file, 'SampleAccAttachment');
        $cnt_file = count($file);

        /*  right file show down form result*/

        $acc_attach_file = $this->SampleAccAttachment->find('all', array(
            'conditions' => array(
                'SampleAccAttachment.status' => 1,
                'SampleAccAttachment.flag' => 1,
                'SampleAccAttachment.sample_id IN' => $get_id
            )
        ));

        $acc_attach_file = array_column($acc_attach_file, 'SampleAccAttachment');
        $cnt_accAttach_file = count($acc_attach_file);
        /* get attachment file for business (status 2 and 3) */
        $business_file = $this->SampleAccAttachment->find('all', array(
            'conditions' => array(
                'SampleAccAttachment.status' => array(2,3),
                'SampleAccAttachment.flag' => 1,
                'SampleAccAttachment.sample_id IN' => $get_id
            )
        ));
        $business_file = array_column($business_file, 'SampleAccAttachment');
        $cnt_business_file = count($business_file);

        $count = count($checkListshow);
        for ($i=0; $i<$count; $i++) {
            // $sid = $i+1;
            // $find[$i]['sid'] = $sid;
            $sample_id = $checkListshow[$i]['tr']['sample_id'];

            $checkListshow[$i]['busi_attach_file_dataentry'] = [];
            $checkListshow[$i]['busi_attach_file_checklist'] = [];
            
            if ($cnt_file > 0) {
                for ($j=0; $j<$cnt_file; $j++) {
                    $attachment_id = $file[$j]['id'];
                    $file_sample_id = $file[$j]['sample_id'];
                    $file_name = $file[$j]['file_name'];
                    $file_type = $file[$j]['file_type'];
                    $file_size = $file[$j]['file_size'];
                    $url = $file[$j]['url'];
                    if ($sample_id == $file_sample_id) {
                        $checkListshow[$i]['acc_file'][] = array(
                            'attachment_id' => $attachment_id,
                            'file_name' => $file_name,
                            'file_type' => $file_type,
                            'file_size' => $file_size,
                            'url' => $url
                        );
                    }
                }

                /* if some of the sample_data has no attachment then add file index to $find array */
                if (!array_key_exists('acc_file', $checkListshow[$i])) {
                    $checkListshow[$i]['acc_file'] = [];
                }
            } else {
                $checkListshow[$i]['acc_file'] = [];
            }

            if ($cnt_business_file > 0) {
                for ($j=0; $j<$cnt_business_file; $j++) {
                    $attachment_id = $business_file[$j]['id'];
                    $file_sample_id = $business_file[$j]['sample_id'];
                    $file_name = $business_file[$j]['file_name'];
                    $file_type = $business_file[$j]['file_type'];
                    $file_size = $business_file[$j]['file_size'];
                    $url = $business_file[$j]['url'];
                    if ($sample_id == $file_sample_id) {
                        if ($business_file[$j]['status'] == 2) {
                            $checkListshow[$i]['busi_attach_file_dataentry'][] = array(
                                'attachment_id' => $attachment_id,
                                'file_name' => $file_name,
                                'file_type' => $file_type,
                                'file_size' => $file_size,
                                'url' => $url
                            );
                        } else {
                            $checkListshow[$i]['busi_attach_file_checklist'][] = array(
                                'attachment_id' => $attachment_id,
                                'file_name' => $file_name,
                                'file_type' => $file_type,
                                'file_size' => $file_size,
                                'url' => $url
                            );
                        }
                    }
                }

                /* if some of the sample_data has no attachment then add file index to $data array */
            }
        }
        return $checkListshow;
    }
    # check duplicate files
    public function checkDuplicateFile()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $file_name = $this->request->data['file_name'];
        $sample_id = $this->request->data['sample_id'];
        $s_id = $this->request->data['s_id'];
        $period = str_replace('-', '/', $period);
        $type = '営業';
        $url = 'Menus/Sample Check/SampleChecklists/'.$layer_code.'/'.$period.'/'.$s_id.'/'.$type.'/';

        $check = $this->SampleAccAttachment->find('all', array(
            'conditions' => array(
                'SampleAccAttachment.sample_id' => $sample_id,
                'SampleAccAttachment.url' => $url,
                'SampleAccAttachment.file_name' => $file_name,
                'SampleAccAttachment.status' => array(2,3),
                'SampleAccAttachment.flag' => 1
            )
        ));

        if (!empty($check)) {
            $response = array(
                'isDuplicate' => 'Yes',
            );
        } else {
            $response = array(
                'isDuplicate' => 'No',
            );
        }
        return json_encode($response);
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
