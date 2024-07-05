<?php
App::uses('AppController', 'Controller');


App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\Exception\GoogleException;
define('PAGE', 'SampleImprovementResults');
define('MENUID', 2);
App::import('Controller', 'Common');


class SampleImprovementResultsController extends AppController
{
    public $uses = array('SampleChecklist','SampleTestResult','SampleAccAttachment','AdminLevelModel','Sample','Layer');
    public $components = array('PhpExcel.PhpExcel', 'Session' ,'Flash');
    public $helpers = array('Html', 'Form');

    public function beforeFilter()
    {
        parent::checkSampleUrlSession();#checkurlsession
    }
    
    public function index()
    {
        $this->layout = 'samplecheck';
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period     = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $role_id    = $this->Session->read('ADMIN_LEVEL_ID');
        $errorMsg   = "";
        $successMsg = "";
        $deadline_date3 = "";
        /*Warpup first time show data */
        $warpUpdataShow =$this->SampleChecklist->warpUpShow($period, $layer_code, $user_level, $category);
        # check user permission
        $permissions = $this->Session->read('PERMISSIONS');
        unset($permissions['index']);
        //second tab show/hide
        $tab_check = $this->Sample->find('first', array(
            'conditions' => array(
                'Sample.flag <>' => 0,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category' => $category,
                'AND' => array(
                    array('NOT' => array('chk.improvement_situation2' => null)),
                    array('NOT' => array('chk.improvement_situation2' => ''))
                )
            ),
            'joins' => array(
                array(
                    'table' => 'sample_checklists',
                    'alias' => 'chk',
                    'type' => 'left',
                    'conditions' => 'Sample.id = chk.sample_id'
                ),
                array(
                    'table' => 'sample_test_results',
                    'alias' => 'tr',
                    'type' => 'left',
                    'conditions' => 'Sample.id = tr.sample_id'
                )
            ),
            'fields' => array(
                'chk.improvement_situation1',
                'chk.improvement_situation2',
                'chk.flag',
                'tr.report_times'
            )
        ));
        
        if (!empty($tab_check)) {
            $tab_1_show = $tab_check['chk']['improvement_situation1'];
            $tab_2_show = $tab_check['chk']['improvement_situation2'];
            $tab_chk_flag = $tab_check['chk']['flag'];
            $tab_2_report_times = $tab_check['tr']['report_times'];
        } else {
            $tab_1_show = '';
            $tab_2_show = '';
            $tab_chk_flag = '';
            $tab_2_report_times = '';
        }

        //check save button for tab 1 and 2
        $wp_tab =$this->SampleChecklist->warp_tabhide($period, $layer_code, $user_level, $category);
        
        if (!empty($wp_tab)) {
            $report_nec_2 = $wp_tab[0]['tr']['report_necessary2'];
            $tab_1_report_times = $wp_tab[0]['tr']['report_times'];
            $sd_flag = $wp_tab[0]['sd']['flag'];
            $chk_flag = $wp_tab[0]['ch']['flag'];
            $chk_improve_1 = $wp_tab[0]['ch']['improvement_situation1'];
            $chk_improve_2 = $wp_tab[0]['ch']['improvement_situation2'];
        } else {
            $report_nec_2 = '';
            $tab_1_report_times = '';
            $sd_flag = '';
            $chk_flag = '';
            $chk_improve_1 = '';
            $chk_improve_2 = '';
        }
        
        //check approve, approve cancel, review button for tab 1
        $chk_btn = $this->Sample->showWarpUpTab1Button($period, $layer_code, $category);
        if (!empty($chk_btn)) {
            $chk_btn_improve_1 = $chk_btn[0]['chk']['improvement_situation1'];
            $chk_btn_improve_2 = $chk_btn[0]['chk']['improvement_situation2'];
        } else {
            $chk_btn_improve_1 = '';
            $chk_btn_improve_2 = '';
        }
        
        //code for botton validate
        $get_id = [];
        $count = count($warpUpdataShow);
        $warp_list_tab1 = array();
        for ($i =0 ; $i < $count; $i++) {
            $get_id[] = $warpUpdataShow[$i]['ch']['sample_id'];
            if ($warpUpdataShow[$i]['ch']['flag'] != '') {
                array_push($warp_list_tab1, $warpUpdataShow[$i]['ch']);
            }
        }
        $warp_list_count = count($warp_list_tab1);
                                   
        $show_save = false;
        $show_review = false;
        $show_reject = false;
        $show_approve = false;
        $show_approve_cancel = false;
        $show_save_tab2 = false;
        $show_review_tab2 = false;
        $show_reject_tab2 = false;
        $show_approve_tab2 = false;
        $show_approve_cancel_tab2 = false;
        $save_flg_sd = array(5, 8);
        $review_flg_sd = array(5, 6, 9);
        $app_flg_sd = array(6, 9);
        $cancel_flg_sd = array(7, 10);
        for($i=0; $i<$warp_list_count ;$i++) {
            $sd_flag = $warpUpdataShow[$i]['sd']['flag'];
            $ch_flag = $warpUpdataShow[$i]['ch']['flag'];
            $tr_flag = $warpUpdataShow[$i]['tr'];
            if (in_array($sd_flag, $save_flg_sd) && $ch_flag == 3){
                $show_save = true;
                $show_review = false;
                $show_save_tab2 = true;
                $show_review_tab2 = false;
            } 
            if (in_array($sd_flag, $review_flg_sd)) {
                if($ch_flag == 4) {
                    $show_review = true;
                    $show_save = false;
                }elseif($ch_flag == 3) {
                    $show_review_tab2 = true;
                    $show_save_tab2 = false;
                }
            } 
            if (in_array($sd_flag, $app_flg_sd)) {
                if($ch_flag == 4) {
                    $show_approve = true;
                    $show_reject = true;
                    $show_review = false;
                }elseif($ch_flag == 3) {                
                    $show_approve_tab2 = true;
                    $show_reject_tab2 = true;
                    $show_review_tab2 = false;
                }
            }
            if($tr_flag['flag'] == 3 && (!empty($tr_flag['report_necessary2']) || $tr_flag['report_necessary2'] != NULL) && in_array($sd_flag, $cancel_flg_sd)) {
                if($ch_flag == 4 &&  $tr_flag['testresult_finish'] == 0) $show_approve_cancel = true;
                elseif($ch_flag == 3 &&  $tr_flag['testresult_finish'] == 1) {
                    $testresult_finish = $i;
                    $show_approve_cancel_tab2 = true;
                }
            }
        }
        if($testresult_finish != ($warp_list_count-1)) $show_approve_cancel_tab2 = false;
        foreach ($permissions as $action => $permission) {
            if(($layer_code == '' && $permission['limit']==0) || in_array($layer_code, array_keys($permission['layers']))) {
                $button_list[$action]     = true;
                $button_list_sec[$action] = true;
            }
        }
        foreach($button_list as $key => $button){            
            $button_list[$key]     = ${'show_'.str_replace(' ', '', $key)};
            $button_list_sec[$key] = ${'show_'.str_replace(' ', '', $key).'_tab2'};
        }
        
        $data = [];
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $find = $this->Sample->find('all', array(
            'conditions' => array(
                'Sample.flag <>' => 0,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category' => $category
            ),
            'order' => array('Sample.id')
        ));
        
        $find = array_column($find, 'Sample');
        $get_id = array_column($find, 'id');//get sample data idd
        $size = count($find);
        if (!empty($find)) {
            $warpUpdataShow = $this->__addFileListIntoArray($get_id, $warpUpdataShow);
        }

        /*WarpTime second time show data	*/
        $second_timeDataShow = $this->SampleChecklist->secondtimeWarpUpShow($period, $layer_code, $user_level, $category);
        
        $cnt_sec_time = count($second_timeDataShow);
        $sec_id = [];
        for ($i=0; $i<$cnt_sec_time; $i++) {
            $sec_id[] = $second_timeDataShow[$i]['ch']['sample_id'];
        }
        if (!empty($sec_id)) {
            $second_timeDataShow = $this->__addFileListIntoArray($sec_id, $second_timeDataShow);
        }
        
        $this->set('layer_name', $layer_name);
        $this->set('role_id', $role_id);
        $this->set('second_timeDataShow', $second_timeDataShow);
        $this->set('user_level', $user_level);
        $this->set('warpUpdataShow', $warpUpdataShow);
        $this->set('data', $find);
        $this->set('tab_1_report_times', $tab_1_report_times);
        $this->set('tab_2_report_times', $tab_2_report_times);
        $this->set('tab_1_show', $tab_1_show);
        $this->set('tab_2_show', $tab_2_show);
        $this->set('tab_chk_flag', $tab_chk_flag);
        $this->set('sd_flag', $sd_flag);
        $this->set('chk_flag', $chk_flag);
        $this->set('report_nec_2', $report_nec_2);
        $this->set('chk_improve_1', $chk_improve_1);
        $this->set('chk_improve_2', $chk_improve_2);
        $this->set('chk_btn_improve_1', $chk_btn_improve_1);
        $this->set('chk_btn_improve_2', $chk_btn_improve_2);
        $this->set('warp_list_tab1', $warp_list_tab1);
        $this->set('button_list', $button_list);
        $this->set('button_list_sec', $button_list_sec);
        $this->set('page', PAGE);
        $this->render('index');
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
                'SampleAccAttachment.status IN' => array(2,3),
                'SampleAccAttachment.flag' => 1,
                'SampleAccAttachment.sample_id IN' => $get_id
            )
        ));
        $business_file = array_column($business_file, 'SampleAccAttachment');
        $cnt_business_file = count($business_file);

        $count = count($checkListshow);
        for ($i=0; $i<$count; $i++) {
            $sample_id = $checkListshow[$i]['ch']['sample_id'];
            
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
                        $checkListshow[$i]['busi_attach_file'][] = array(
                            'attachment_id' => $attachment_id,
                            'file_name' => $file_name,
                            'file_type' => $file_type,
                            'file_size' => $file_size,
                            'url' => $url
                        );
                    }
                }

                /* if some of the sample_data has no attachment then add file index to $data array */
                if (!array_key_exists('busi_attach_file', $checkListshow[$i])) {
                    $checkListshow[$i]['busi_attach_file'] = [];
                }
            } else {
                $checkListshow[$i]['busi_attach_file'] = [];
            }
        }
        return $checkListshow;
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

            $cloud = parent::connect_to_google_cloud_storage();
            $storage = $cloud[0];
            $bucketName = $cloud[1];
            $bucket = $storage->bucket($bucketName);
            $object = $bucket->object($url);
            try {
                $stream = $object->downloadAsStream();
                header('Content-disposition: attachment; filename*=UTF-8\'\''.rawurlencode($file_name));
                echo $stream->getContents();
                exit();
            } catch (GoogleException $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE012");
                $this->Flash->set($msg, array('key'=>'UserError'));
                $this->redirect(array('controller'=>'SampleImprovementResults', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleImprovementResults', 'action'=>'index'));
        }
    }
    /* first tab */
    public function SaveSampleImprovementResults()
    {
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $admin_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');
                
        if ($this->request->is('post')) {
            
            $warp_sample_id = $this->request->data['warp_sample_id'];
            $warp_result_id =   $this->request->data['warp_result_id'];
            $warp_Commentone = $this->request->data['warp_Commentone'];
            $warp_Commenttwo = $this->request->data['warp_Commenttwo'];
            $selected_sample_id = $this->request->data['tab1_select_sample_id'];
            if (isset($this->request->data['warp_rp2_check']) && $this->request->data['warp_rp2_check'] != '') {
                $warp_rp2_check1 = $this->request->data['warp_rp2_check'];
            } else {
                $warp_rp2_check1 = 0;
            }
            $param = array();
            $param["layer_code"]   = $layer_code;
            $param["category"]   = $category;
            $param["period"]    = $period;
            $param["user_level"] = $user_level;
            $param["admin_id"] = $admin_id;
            $param["warp_Commentone"] = $warp_Commentone;
            $param["warp_Commenttwo"] = $warp_Commenttwo;
            $param["warp_rp2_check1"] = $warp_rp2_check1;
            $param["warp_sample_id"] = $warp_sample_id;
            $param["warp_result_id"] = $warp_result_id;
            $param["selected_sample_id"] = $selected_sample_id;

            $check = $this->Sample->find('all', array(
                'conditions' => array(
                    'DATE_FORMAT(period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category' => $category,
                    'OR' => array(
                        array('flag' => 5),
                        array('flag' => 8)
                    )
                )
            ));

            if (empty($check)) {
                $errorMsg = parent::getErrorMsg('SE017', __('保存'));
                $this->Flash->set($errorMsg, array("key"=>"UserError"));

                return $this->redirect(array('action' => 'index'));
            } else {
                $dbChk = $this->Sample->getDataSource();

                try {
                    $warpCheckupdate = $this->SampleChecklist->UpdateResult($param);
                    
                    if ($warpCheckupdate) {
                        if ($_POST['mailSend']) {
                            $mail_template          = 'common';
                            $mail['subject']        = $_POST['mailSubj'];
                            $mail['template_title'] = $_POST['mailTitle'];
                            $mail['template_body']  = $_POST['mailBody'];
                            $to_email = $_POST['toEmail'];
                            $cc_email = $_POST['ccEmail'];
                            $bcc_email = $_POST['bccEmail'];
                            $toEmail = parent::formatMailInput($to_email);
                            $ccEmail = parent::formatMailInput($cc_email);
                            $bccEmail = parent::formatMailInput($bcc_email);

                            if (!empty($toEmail) && $toEmail != "") {
                                
                                $url = '/SampleImprovementResults?period='.$period.'&ba='.$layer_code.'&category='.$category;
                                
                                $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                
                                if ($sentMail["error"]) {
                                    $errorMsg = $sentMail["errormsg"];
                                    $this->Flash->set($errorMsg, array("key"=>"UserError"));

                                    CakeLog::write('debug', 'Account Incharge (level 4) informations are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Reject button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                    return $this->redirect(array('action' => 'index'));
                                } else {
                                    $dbChk->commit();
                                    $successMsg = parent::getSuccessMsg("SS001");
                                    $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                                    
                                    $successMsg = parent::getSuccessMsg('SS018');
                                    $this->Flash->set($successMsg, array("key"=>"UserSuccess"));

                                    return $this->redirect(array('action' => 'index'));
                                }
                            } else {
                                $errorMsg = parent::getErrorMsg('SE059');
                                $this->Flash->set($errorMsg, array("key"=>"UserError"));

                                CakeLog::write('debug', 'Account Sub Manager (level 4) informations are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Reject button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            }

                            $dbChk->rollback();
                            $errorMsg = parent::getErrorMsg('SE003');
                            $this->Flash->set($errorMsg, array("key"=>"UserError"));

                            return $this->redirect(array('action' => 'index'));
                        }else {
                            $dbChk->commit();
                            $successMsg = parent::getSuccessMsg("SS001");
                            $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                            return $this->redirect(array('action' => 'index'));
                        }
                    } else {
                        $dbChk->rollback();
                        $errorMsg = parent::getErrorMsg('SE003');
                        $this->Flash->set($errorMsg, array("key"=>"UserError"));

                        return $this->redirect(array('action' => 'index'));
                    }
                } catch (Exception $e) {
                    $dbChk->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                    $errorMsg = parent::getErrorMsg('SE003');
                    $this->Flash->set($errorMsg, array("key"=>"UserError"));

                    return $this->redirect(array('action' => 'index'));
                }
            }
        }
    }
    public function ReviewSampleImprovementResults()
    {   
        $Common = new CommonController;
        $errorMsg   = "";
        $successMsg = "";
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $admin_id_level = $this->Session->read('LOGIN_ID');

        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        
        $login_user_name = $this->Session->read('LOGIN_USER');
        $invalid_email = '';
        
        $warp_sample_id = $this->request->data['warp_sample_id'];
        $warp_result_id = $this->request->data['warp_result_id'];
        $warp_Commentone = $this->request->data['warp_Commentone'];
        $warp_Commenttwo = $this->request->data['warp_Commenttwo'];
        $tab1_select_sample_id = $this->request->data['tab1_select_sample_id'];
        $param['warp_sample_id'] = $warp_sample_id;
        $param['warp_Commentone'] = $warp_Commentone;
        $param['warp_Commenttwo'] = $warp_Commenttwo;
        $param['admin_id'] = $this->Session->read('LOGIN_ID');
        $param['warp_result_id'] = $warp_result_id;
        $param['tab1_select_sample_id'] = $tab1_select_sample_id;

        # check data is need to review or not
        # if not found, no need to review
        $check = $this->Sample->find('all', array(
            'conditions' => array(
                'DATE_FORMAT(period, "%Y-%m")' => $period,
                'layer_code' => $layer_code,
                'category' => $category,
                'OR' => array(
                    array('flag' => 5),
                    array('flag' => 6),
                    array('flag' => 9)
                )
            )
        ));
        
        if (empty($check)) {
            $errMsg = parent::getErrorMsg('SE017', [__('レビュー')]);
            $this->Flash->set($errMsg, array("key"=>"UserError"));
            return $this->redirect(array('action' => 'index'));
        }

        if (isset($this->request->data['warp_rp2_check']) && $this->request->data['warp_rp2_check'] != '') {
            $warp_rp2_check1 = $this->request->data['warp_rp2_check'];
        } else {
            $warp_rp2_check1 = '';
        }
        $param["warp_rp2_check1"] = $warp_rp2_check1;
        if (isset($this->request->data['wap_finshedtab']) && $this->request->data['wap_finshedtab'] != '') {
            $wap_finshedtab = $this->request->data['wap_finshedtab'];
        } else {
            $wap_finshedtab = '';
        }
        $param["wap_finshedtab"] = $wap_finshedtab;

        $tmp = [];
        if (!empty($warp_rp2_check1) && empty($wap_finshedtab)) {
            # only check report optional
            $cnt_option = count($warp_rp2_check1);
            for ($i=0; $i<$cnt_option; $i++) {
                $tmp[] = array(
                    'sample_id' => $warp_rp2_check1[$i],
                    'flag' => 6,
                    'complete_date' => null,
                    'testresult_finish' => 0
                );
            }
            $param['sample_updates'] = $tmp;
        } elseif (empty($warp_rp2_check1) && !empty($wap_finshedtab)) {
            # only check complete flag
            $cnt_option = count($wap_finshedtab);
            for ($i=0; $i<$cnt_option; $i++) {
                $tmp[] = array(
                    'sample_id' => $wap_finshedtab[$i],
                    'flag' => 9,
                    'complete_date' => date('Y-m-d'),
                    'testresult_finish' => 1
                );
            }
            $param['sample_updates'] = $tmp;
        } elseif (!empty($warp_rp2_check1) && !empty($wap_finshedtab)) {
            $cnt = count($warp_sample_id);
            for ($i=0; $i<$cnt; $i++) {
                $s_id = $warp_sample_id[$i];
                if (in_array($s_id, $warp_rp2_check1) && in_array($s_id, $wap_finshedtab)) {
                    $sample_flag = 9;
                    $complete_date = date('Y-m-d');
                    $testresult_finish = 1;
                } elseif (!in_array($s_id, $warp_rp2_check1) && in_array($s_id, $wap_finshedtab)) {
                    $sample_flag = 9;
                    $complete_date = date('Y-m-d');
                    $testresult_finish = 1;
                } elseif (in_array($s_id, $warp_rp2_check1) && !in_array($s_id, $wap_finshedtab)) {
                    $sample_flag = 6;
                    $complete_date = null;
                    $testresult_finish = 0;
                }
                $tmp[] = array(
                    'sample_id' => $s_id,
                    'flag' => $sample_flag,
                    'complete_date' => $complete_date,
                    'testresult_finish' => $testresult_finish
                );
            }
            $param['sample_updates'] = $tmp;
        }

        $dbChk = $this->SampleChecklist->getDataSource();
        try {
            $dbChk->begin();

            $rsl = $this->SampleChecklist->warp_save_review($param, $period, $layer_code, $category);
            if ($rsl == true) {
                if($_POST['mailSend']) {
                    $mail_template          = 'common';
                    $mail['subject']        = $_POST['mailSubj'];
                    $mail['template_title'] = $_POST['mailTitle'];
                    $mail['template_body']  = $_POST['mailBody'];
                    $to_email = $_POST['toEmail'];
                    $cc_email = $_POST['ccEmail'];
                    $bcc_email = $_POST['bccEmail'];
                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);
                    
                    if (!empty($toEmail)) {
                        $url = '/SampleImprovementResults?period='.$period.'&ba='.$layer_code.'&category='.$category;

                        if (!empty($toEmail)) {
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                

                            if ($sentMail["error"]) {
                                $err = $sentMail["errormsg"];
                                $invalid_email = parent::getErrorMsg('SE042');
                                $this->Flash->set($err, array("key"=>"UserError"));
                                return $this->redirect(array('action' => 'index'));
                            } else {
                                #if email have save data
                                $dbChk->commit();
                                    
                                $successMsg = parent::getSuccessMsg('SS001');
                                $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                                $successMsg = parent::getSuccessMsg("SS018");
                                $this->Flash->set($successMsg, array('key'=>'UserSuccess'));
                                return $this->redirect(array('action' => 'index'));
                            }
                        } else {
                            CakeLog::write('debug', 'Account Manager (level 2)  emails are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id_level. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $err = parent::getErrorMsg('SE058');
                            $this->Flash->set($err, array("key"=>"UserError"));
                            return $this->redirect(array('action' => 'index'));
                        }
                    } else {
                        $err = parent::getErrorMsg('SE059');
                        $this->Flash->set($err, array("key"=>"UserError"));
                        CakeLog::write('debug', 'Account Manager (level 2) informations are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id_level. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    }
                }else {
                    $dbChk->commit();            
                    $successMsg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                    return $this->redirect(array('action' => 'index'));
                }
                
                $dbChk->rollback();
                $err = parent::getErrorMsg('SE003');
                $this->Flash->set($err, array("key"=>"UserError"));
                return $this->redirect(array('action' => 'index'));
            } else {
                throw new Exception();
            }
        } /* end try*/
        catch (Exception $e) {
            $dbChk->rollback();
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $err = parent::getErrorMsg('SE003');
            $err .= " ".$invalid_email;
            $this->Flash->set($err, array("key"=>"UserError"));
            return $this->redirect(array('action' => 'index'));
        }
    }
    public function ApproveSampleImprovementResults()
    {   
        $Common = new CommonController;
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $admin_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');
        
        if (isset($this->request->data['wap_finshedtabtwo'])) {
            $wp_sampleid =$this->request->data['wap_finshedtabtwo'];
        } elseif (isset($this->request->data['warp_sample_id'])) {
            $wp_sampleid =$this->request->data['warp_sample_id'];
        }
        # find flag 9 or 6 to approve
        $find = $this->Sample->find('all', array(
            'conditions' => array(
                'DATE_FORMAT(period, "%Y-%m")' => $period,
                'layer_code' => $layer_code,
                'category' => $category,
                'OR' => array(
                    array('flag' => 9),
                    array('flag' => 6),
                )
            )
        ));

        if (!empty($find)) {
            $tmp = array_column($find, 'Sample');
            $approve_id = array_column($tmp, 'id');
            
            $dbChk = $this->SampleChecklist->getDataSource();

            try {
                $dbChk->begin();
                $finished_flag =$this->SampleChecklist->ApproveChecklast($admin_id, $period, $layer_code, $approve_id, $category);
                
                if ($finished_flag == true) {
                    if($_POST['mailSend']) {
                        $mail_template          = 'common';
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body']  = $_POST['mailBody'];
                        $to_email = $_POST['toEmail'];
                        $cc_email = $_POST['ccEmail'];
                        $bcc_email = $_POST['bccEmail'];
                        $toEmail = parent::formatMailInput($to_email);
                        $ccEmail = parent::formatMailInput($cc_email);
                        $bccEmail = parent::formatMailInput($bcc_email);
                        
                        if (!empty($toEmail)) {
                            $url = '/SampleChecklists?period='.$period.'&ba='.$layer_code.'&category='.$category;

                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

                            if ($sentMail["error"]) {
                                $errorMsg = $sentMail["errormsg"];
                                $this->Flash->set($errorMsg, array("key"=>"UserError"));
                                return $this->redirect(array('action' => 'index'));
                            } else {
                                $dbChk->commit();

                                $successMsg = parent::getSuccessMsg('SS005');
                                $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                                $successMsg = parent::getSuccessMsg("SS018");
                                $this->Flash->set($successMsg, array('key'=>'UserSuccess'));

                                return $this->redirect(array('action' => 'index'));
                            }
                        } else {
                            $errorMsg = parent::getErrorMsg('SE059');
                            $this->Flash->set($errorMsg, array("key"=>"UserError"));

                            CakeLog::write('debug', 'Account Manager (level 2) informations are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Reject button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                            return $this->redirect(array('action' => 'index'));
                        }
                    }else {
                        $dbChk->commit();

                        $successMsg = parent::getSuccessMsg('SS005');
                        $this->Flash->set($successMsg, array("key"=>"UserSuccess"));

                        return $this->redirect(array('action' => 'index'));
                    }
                }
                $dbChk->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                $errorMsg = parent::getErrorMsg('SE011', __("Approve"));
                $this->Flash->set($errorMsg, array("key"=>"UserError"));

                return $this->redirect(array('action' => 'index'));
            } catch (Exception $e) {
                $dbChk->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                $errorMsg = parent::getErrorMsg('SE011', __("Approve"));
                $this->Flash->set($errorMsg, array("key"=>"UserError"));

                return $this->redirect(array('action' => 'index'));
            }
        } else {
            $errorMsg = parent::getErrorMsg('SE017', __("Approve"));
            $this->Flash->set($errorMsg, array("key"=>"UserError"));

            return $this->redirect(array('action' => 'index'));
        }
    }
    public function approve_cancelSampleImprovementResults()
    {
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $admin_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');
        
        # find data to approve cancel
        # find data with (flag 10 and not empty complete_date)
        # or (flag 7 and empty complete_date) in samples
        # and sample_checklists flag must be 4
        $get_to_cancel = $this->Sample->find('all', array(
            'conditions' => array(
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category' => $category,
                'OR' => array(
                    array(
                        'Sample.flag' => 10,
                        'not' => array('Sample.complete_date' => null)
                    ),
                    array(
                        'Sample.flag' => 7,
                        'Sample.complete_date' => null
                    )
                ),
                'chk.flag' => 4
            ),
            'joins' => array(
                array(
                    'table' => 'sample_checklists',
                    'alias' => 'chk',
                    'type' => 'left',
                    'conditions' => 'Sample.id = chk.sample_id'
                )
            )
        ));
        
        if (!empty($get_to_cancel)) {
            # if found, then make approve cancel
            $tmp = array_column($get_to_cancel, 'Sample');
            $approved_id = array_column($tmp, 'id');
            
            $dbChk = $this->SampleChecklist->getDataSource();

            try {
                $dbChk->begin();

                $finished_flag =$this->SampleChecklist->ApproveChecklastcancle($admin_id, $period, $layer_code, $approved_id, $category);
                
                if ($finished_flag == true) {
                    if($_POST['mailSend']) {
                        $mail_template          = 'common';
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body']  = $_POST['mailBody'];
                        $to_email = $_POST['toEmail'];
                        $cc_email = $_POST['ccEmail'];
                        $bcc_email = $_POST['bccEmail'];
                        $toEmail = parent::formatMailInput($to_email);
                        $ccEmail = parent::formatMailInput($cc_email);
                        $bccEmail = parent::formatMailInput($bcc_email);
                        if (!empty($toEmail)) {
                            $url = '/SampleImprovementResults?period='.$period.'&ba='.$layer_code.'&category='.$category;
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                            
                            #if does not mail have user validation
                            if ($sentMail["error"]) {
                                $err = $sentMail["errormsg"];
                                $this->Flash->set($err, array("key"=>"UserError"));
                                return $this->redirect(array('action' => 'index'));
                            } else {
                                $dbChk->commit();
                                $successMsg = parent::getSuccessMsg("SS006");
                                $this->Flash->set($successMsg, array("key"=>"UserSuccess"));

                                $successMsg = parent::getSuccessMsg('SS018');
                                $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                                return $this->redirect(array('action' => 'index'));
                            }
                        } else {
                            CakeLog::write('debug', 'Account Manager (level 2)  emails are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $err = parent::getErrorMsg('SE059');
                            $this->Flash->set($err, array("key"=>"UserError"));
                            return $this->redirect(array('action' => 'index'));
                        }
                    }else {
                        $dbChk->commit();
                        $successMsg = parent::getSuccessMsg("SS006");
                        $this->Flash->set($successMsg, array("key"=>"UserSuccess"));

                        return $this->redirect(array('action' => 'index'));
                    }
                }
                $dbChk->rollback();
                $err = parent::getErrorMsg('SE003');
                $this->Flash->set($err, array("key"=>"UserError"));
                return $this->redirect(array('action' => 'index'));
            }catch (Exception $e) {
                $dbChk->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $err = parent::getErrorMsg('SE003');
                $err .= " ".$invalid_email;
                $this->Flash->set($err, array("key"=>"UserError"));
                return $this->redirect(array('action' => 'index'));
            }
        } else {
            # if not found, no need to approve cancel
            $err = parent::getErrorMsg('SE017', [__("Approve Cancel")]);
            $this->Flash->set($err, array("key"=>"UserError"));
            return $this->redirect(array('action' => 'index'));
        }
    }
    public function RejectSampleImprovementResults()
    {
        if ($this->request->is('post')) {
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $admin_id = $this->Session->read('LOGIN_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $get_level4Email = array();
            $get_level3Email = array();

            # get data with flag 9 and 6 (sample_checklists.flag=4) from samples
            $get_reject_flag = $this->Sample->find('all', array(
                'conditions' => array(
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'Sample.layer_code' => $layer_code,
                    'Sample.category' => $category,
                    'OR' => array(
                        array(
                            'Sample.flag' => 9,
                            'not' => array('Sample.complete_date' => null)
                        ),
                        array(
                            'Sample.flag' => 6,
                            'Sample.complete_date' => null
                        )
                    ),
                    'chk.flag' => 4
                ),
                'joins' => array(
                    array(
                        'table' => 'sample_checklists',
                        'alias' => 'chk',
                        'type' => 'left',
                        'conditions' => 'Sample.id = chk.sample_id'
                    )
                )
            ));
            
            if (!empty($get_reject_flag)) {
                $tmp = array_column($get_reject_flag, 'Sample');
                $reject_id = array_column($tmp, 'id');
                $sample_id = implode(',', $reject_id);

                $dbChk = $this->Sample->getDataSource();

                try {
                    $dbChk->begin();
                    # update flag 9 and 6 to 5 from samples and flag 2 to 1 from sample_test_results
                    $rejectflag = $this->Sample->FlagReject($admin_id, $period, $layer_code, $sample_id, $category);
                    
                    if ($rejectflag == true) {
                        if($_POST['mailSend']) {
                            $mail_template          = 'common';
                            $mail['subject']        = $_POST['mailSubj'];
                            $mail['template_title'] = $_POST['mailTitle'];
                            $mail['template_body']  = $_POST['mailBody'];
                            $to_email = $_POST['toEmail'];
                            $cc_email = $_POST['ccEmail'];
                            $bcc_email = $_POST['bccEmail'];
                            $toEmail = parent::formatMailInput($to_email);
                            $ccEmail = parent::formatMailInput($cc_email);
                            $bccEmail = parent::formatMailInput($bcc_email);
                            if (!empty($toEmail)) { # have mail
                                $url = '/SampleImprovementResults?period='.$period.'&ba='.$layer_code.'&category='.$category;

                                $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                            
                                if ($sentMail["error"]) { # return error from sendmail function

                                    $errorMsg = $sentMail["errormsg"];
                                    $this->Flash->set($errorMsg, array("key"=>"UserError"));

                                    return $this->redirect(array('action' => 'index'));
                                } else { # no have error

                                    $dbChk->commit();

                                    $successMsg = parent::getSuccessMsg("SS014");
                                    $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                                    
                                    $successMsg = parent::getSuccessMsg('SS018');
                                    $this->Flash->set($successMsg, array("key"=>"UserSuccess"));

                                    return $this->redirect(array('action' => 'index'));
                                }
                            } else { # no have email
                                $dbChk->rollback();

                                $errorMsg = parent::getErrorMsg('SE059');
                                $this->Flash->set($errorMsg, array("key"=>"UserError"));

                                CakeLog::write('debug', 'Account Manager (level 2) informations are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Reject button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                return $this->redirect(array('action' => 'index'));
                            }
                        }else {
                            $dbChk->commit();

                            $successMsg = parent::getSuccessMsg("SS014");
                            $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                            return $this->redirect(array('action' => 'index'));
                        }    
                    }
                    $dbChk->rollback();

                    $errorMsg = parent::getErrorMsg('SE059');
                    $this->Flash->set($errorMsg, array("key"=>"UserError"));

                    return $this->redirect(array('action' => 'index'));
                } catch (Exception $e) {
                    $dbChk->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                    $errorMsg = parent::getErrorMsg('SE011', __("Reject"));
                    $this->Flash->set($errorMsg, array("key"=>"UserError"));

                    return $this->redirect(array('action' => 'index'));
                }
            } else {
                CakeLog::write('debug', 'Account Manager (level 2) informations are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Reject button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                $errorMsg = parent::getErrorMsg('SE017', __("Reject"));
                $this->Flash->set($errorMsg, array("key"=>"UserError"));

                return $this->redirect(array('action' => 'index'));
            }
        }
    }
    /* second tab */
    public function SaveSampleImprovementResultsTab2()
    { 
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $admin_id = $this->Session->read('LOGIN_ID');
        $data = $this->request->data;
        $login_user_name = $this->Session->read('LOGIN_USER');
                
        if ($this->request->is('post')) {
            
            $sec_warp_sample_id = $this->request->data['sec_warp_sample_id'];
            $sec_warp_result_id =   $this->request->data['sec_warp_result_id'];
            $warp_Cmttone = $this->request->data['warp_Cmttone'];
            $warp_Cmttwo = $this->request->data['warp_Cmttwo'];
            $selected2_sample_id = $this->request->data['warp2sampleall'];
                        
            if (isset($this->request->data['warp_rp2_check2']) !='') {
                $warp_rp2_check2 = $this->request->data['warp_rp2_check2'];
            } else {
                $warp_rp2_check2 = 0 ;
            }
            $param = array();
            $param["layer_code"] = $layer_code;
            $param["category"] = $category;
            $param["period"] = $period;
            $param["admin_id"] = $admin_id;
            $param["warp_Cmttone"] = $warp_Cmttone;
            $param["warp_Cmttwo"] = $warp_Cmttwo;
            $param["sec_warp_sample_id"] = $sec_warp_sample_id;
            $param["sec_warp_result_id"] = $sec_warp_result_id;
            $param["warp2sampleall"] = $selected2_sample_id;

            $check = $this->Sample->find('all', array(
                'conditions' => array(
                    'DATE_FORMAT(period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category' => $category,
                    'OR' => array(
                        array('flag' => 5),
                        array('flag' => 8)
                    )
                )
            ));

            if (empty($check)) {
                $err = parent::getErrorMsg('SE017', __('保存'));
                $this->Flash->set($err, array("key"=>"UserError"));

                return $this->redirect(array('action' => 'index'));
            } else {
                $dbChk = $this->Sample->getDataSource();

                try {
                    $warpCheckupdate2=$this->SampleChecklist->lastUpdateResult($param);

                    if ($warpCheckupdate2 == true) {
                        if($_POST['mailSend']) {
                            $mail_template = 'common';

                            #Mail contents
                            $mail['subject']        = $_POST['mailSubj'];
                            $mail['template_title'] = $_POST['mailTitle'];
                            $mail['template_body']  = $_POST['mailBody'];
                            $toEmail = parent::formatMailInput($_POST['toEmail']);
                            $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                            $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                                                  
                            $url = '/SampleImprovementResults?period='.$period.'&ba='.$layer_code.'&category='.$category;

                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

                            if ($sentMail["error"]) {
                                $errorMsg = $sentMail["errormsg"];
                                $this->Flash->set($errorMsg, array("key"=>"UserError"));

                                return $this->redirect(array('action' => 'index'));
                            } else {
                                $dbChk->commit();
                                $successMsg = parent::getSuccessMsg("SS001");
                                $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                                
                                $successMsg = parent::getSuccessMsg('SS018');
                                $this->Flash->set($successMsg, array("key"=>"UserSuccess"));

                                return $this->redirect(array('action' => 'index'));
                            }
                           
                        }else {
                            $dbChk->commit();
                            $successMsg = parent::getSuccessMsg("SS001");
                            $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                            return $this->redirect(array('action' => 'index'));
                        }
                        return $this->redirect(array('action' => 'index'));
                    } else {
                        $dbChk->rollback();
                        $errorMsg = parent::getErrorMsg('SE003');
                        $this->Flash->set($errorMsg, array("key"=>"UserError"));

                        return $this->redirect(array('action' => 'index'));
                    }
                } catch (Exception $e) {
                    $dbChk->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                    $errorMsg = parent::getErrorMsg('SE003');
                    $this->Flash->set($errorMsg, array("key"=>"UserError"));

                    return $this->redirect(array('action' => 'index'));
                }
            }
        } else {
            return $this->redirect(array('action' => 'index'));
        }
    }
    public function ReviewSampleImprovementResultsTab2()
    {
        $Common = new CommonController;
        $errorMsg   = "";
        $successMsg = "";
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $warp_sample_id = $this->request->data['sec_warp_sample_id'];
        $warp_result_id = $this->request->data['sec_warp_result_id'];
        $complete_flag = $this->request->data['wap_finshedtabtwo'];
        $warp_Commentone = $this->request->data['warp_Cmttone'];
        $warp_Commenttwo = $this->request->data['warp_Cmttwo'];
        $param['warp_sample_id'] = $warp_sample_id;
        $param['warp_result_id'] = $warp_result_id;
        $param['point_out_3'] = $warp_Commentone;
        $param['deadline_date3'] = $warp_Commenttwo;
        $param['complete_flag'] = $complete_flag;
        $param['admin_id'] = $this->Session->read('LOGIN_ID');

        # check data is need to review (flag 5)
        $isNeedToReview = $this->Sample->find('all', array(
            'conditions' => array(
                'DATE_FORMAT(period, "%Y-%m")' => $period,
                'layer_code' => $layer_code,
                'category' => $category,
                'OR' => array(
                    array('flag' => 5),
                    array('flag' => 6),
                    array('flag' => 9),
                )
            )
        ));
        if (empty($isNeedToReview)) {
            # if not exits, no need to review
            $successMsg = parent::getErrorMsg('SE017', [__('レビュー')]);
            $this->Flash->set($successMsg, array("key"=>"UserError"));
            return $this->redirect(array('action' => 'index'));
        } else {

            # if some data is not yet approved, then approved
        
            $dbChk = $this->SampleChecklist->getDataSource();

            try {
                $dbChk->begin();

                $rsl = $this->SampleChecklist->sec_tab_review_save($param, $period, $layer_code, $category);
                if ($rsl == true) {
                    if($_POST['mailSend']) {
                        $mail_template = 'common';

                        #Mail contents
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body']  = $_POST['mailBody'];
                        $toEmail = parent::formatMailInput($_POST['toEmail']);
                        $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                        $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                        $url = '/SampleImprovementResults?period='.$period.'&ba='.$layer_code.'&category='.$category;

                        if (!empty($toEmail)) {
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

                            if ($sentMail["error"]) {
                                $err = $sentMail["errormsg"];
                                $this->Flash->set($err, array("key"=>"UserError"));
                                return $this->redirect(array('action' => 'index'));
                            } else {
                                $dbChk->commit();

                                $successMsg = parent::getSuccessMsg('SS001');
                                $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                                $successMsg = parent::getSuccessMsg("SS018");
                                $this->Flash->set($successMsg, array('key'=>'UserSuccess'));
                                return $this->redirect(array('action' => 'index'));
                            }
                        } else {
                            $successMsg = parent::getErrorMsg("SE059");
                            $this->Flash->set($successMsg, array('key'=>'UserError'));
                            CakeLog::write('debug', 'Account Manager (level 2)  emails are not found for BA Code:' .$layer_code. ', when login user id:' .$user_level. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $err = parent::getErrorMsg('SE058');
                            $this->Flash->set($err, array("key"=>"UserError"));
                            return $this->redirect(array('action' => 'index'));
                        }
                    }else {
                        $dbChk->commit();

                        $successMsg = parent::getSuccessMsg('SS001');
                        $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                        
                        return $this->redirect(array('action' => 'index'));
                    } 
                    return $this->redirect(array('action' => 'index'));
                } else {
                    throw new Exception();
                }
            } /* end try*/
            catch (Exception $e) {
                $dbChk->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $err = parent::getErrorMsg('SE003');
                $err .= " ".$invalid_email;
                $this->Flash->set($err, array("key"=>"UserError"));
                return $this->redirect(array('action' => 'index'));
            }
        }
    }
    public function ApproveSampleImprovementResultsTab2()
    {
        $Common = new CommonController;
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $admin_id = $this->Session->read('LOGIN_ID');

        $login_user_name = $this->Session->read('LOGIN_USER');
        # get sample_id with complete_date and flag 9 to update sample_test_results
        $get_id = $this->Sample->find('all', array(
            'conditions' => array(
                'DATE_FORMAT(period, "%Y-%m")' => $period,
                'layer_code' => $layer_code,
                'category' => $category,
                'flag' => 9,
                'not' => array('complete_date' => null)
            )
        ));
        if (!empty($get_id)) {
            $tmp = array_column($get_id, 'Sample');
            $sample_id_arr = array_column($tmp, 'id');
            
            $dbChk = $this->SampleChecklist->getDataSource();
    
            try {
                $dbChk->begin();

                $finished_flag =$this->SampleChecklist->sec_ApproveChecklast($admin_id, $period, $layer_code, $sample_id_arr, $category);
                if ($finished_flag == true) {
                    if($_POST['mailSend']) {
                        $mail_template = 'common';

                        #Mail contents
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body']  = $_POST['mailBody'];
                        $toEmail = parent::formatMailInput($_POST['toEmail']);
                        $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                        $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                        $url = '/SampleImprovementResults?period='.$period.'&ba='.$layer_code.'&category='.$category;

                        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

                        if ($sentMail["error"]) {
                            $errorMsg = $sentMail["errormsg"];
                            $this->Flash->set($errorMsg, array("key"=>"UserError"));

                            return $this->redirect(array('action' => 'index'));
                        } else {
                            $dbChk->commit();

                            $successMsg = parent::getSuccessMsg('SS005');
                            $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                            $successMsg = parent::getSuccessMsg("SS018");
                            $this->Flash->set($successMsg, array('key'=>'UserSuccess'));

                            return $this->redirect(array('action' => 'index'));
                        }
                    }else {
                        $dbChk->commit();

                        $successMsg = parent::getSuccessMsg('SS005');
                        $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                        
                        return $this->redirect(array('action' => 'index'));
                    }  
                }
            } catch (Exception $e) {
                $dbChk->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $err = parent::getErrorMsg('SE011', __("Approve"));
                $err .= " ".$invalid_email;
                $this->Flash->set($err, array("key"=>"UserError"));
            }
        } else {
            $err = parent::getErrorMsg('SE017', [__('承認')]);
            $this->Flash->set($err, array("key"=>"UserError"));
        }
        return $this->redirect(array('action' => 'index'));
    }
    public function RejectSampleImprovementResultsTab2()
    {
        if ($this->request->is('post')) {
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $admin_id = $this->Session->read('LOGIN_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');

            $get_level3Email = array();
            $get_level4Email = array();

            $get_reject_flag = $this->Sample->find('all', array(
                'conditions' => array(
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category' => $category,
                    'Sample.flag' => 9,
                    'not' => array('Sample.complete_date' => null),
                    'AND' => array(
                        array('not' => array('chk.improvement_situation2' => null)),
                        array('not' => array('chk.improvement_situation2' => ''))
                    )
                ),
                'joins' => array(
                    array(
                        'table' => 'sample_checklists',
                        'alias' => 'chk',
                        'type' => 'left',
                        'conditions' => 'Sample.id = chk.sample_id'
                    )
                )
            ));
            
            if (!empty($get_reject_flag)) {
                $tmp = array_column($get_reject_flag, 'Sample');
                $reject_id = array_column($tmp, 'id');
                $sample_id = implode(',', $reject_id);
                
                $dbChk = $this->Sample->getDataSource();

                try {
                    $dbChk->begin();
                    $rejectflag =$this->Sample->FlagReject($admin_id, $period, $layer_code, $sample_id, $category);
                    
                    if ($rejectflag == true) {
                        if($_POST['mailSend']) {
                            $mail_template = 'common';

                            #Mail contents
                            $mail['subject']        = $_POST['mailSubj'];
                            $mail['template_title'] = $_POST['mailTitle'];
                            $mail['template_body']  = $_POST['mailBody'];
                            $toEmail = parent::formatMailInput($_POST['toEmail']);
                            $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                            $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                            $url = '/SampleImprovementResults?period='.$period.'&ba='.$layer_code.'&category='.$category;

                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

                            if ($sentMail["error"]) {
                                $errorMsg = $sentMail["errormsg"];
                                $this->Flash->set($errorMsg, array("key"=>"UserError"));

                                return $this->redirect(array('action' => 'index'));
                            } else {
                                $dbChk->commit();
                                $successMsg = parent::getSuccessMsg("SS014");
                                $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                                
                                $successMsg = parent::getSuccessMsg('SS018');
                                $this->Flash->set($successMsg, array("key"=>"UserSuccess"));

                                return $this->redirect(array('action' => 'index'));
                            }
                        }else {
                            $dbChk->commit();
                            $successMsg = parent::getSuccessMsg("SS014");
                            $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                            
                            return $this->redirect(array('action' => 'index'));
                        }

                    }
                } catch (Exception $e) {
                    $dbChk->rollback();

                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                    $errorMsg = parent::getErrorMsg('SE011', __("Reject"));
                    $this->Flash->set($errorMsg, array("key"=>"UserError"));

                    return $this->redirect(array('action' => 'index'));
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE017', __("Reject"));
                $this->Flash->set($errorMsg, array("key"=>"UserError"));

                return $this->redirect(array('action' => 'index'));
            }
        }
    }
    public function approve_cancelSampleImprovementResultsTab2()
    {
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $admin_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');
        # get sample_id with complete_date and flag 10 to update sample_test_results
        $get_id = $this->Sample->find('all', array(
            'conditions' => array(
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'layer_code' => $layer_code,
                'category' => $category,
                'Sample.flag' => 10,
                'not' => array('Sample.complete_date' => null),
                'AND' => array(
                    array('not' => array('chk.improvement_situation2' => null)),
                    array('not' => array('chk.improvement_situation2' => ''))
                )
            ),
            'joins' => array(
                array(
                    'table' => 'sample_checklists',
                    'alias' => 'chk',
                    'type' => 'left',
                    'conditions' => 'Sample.id = chk.sample_id'
                )
            )
        ));
        
        if (!empty($get_id)) {
            $tmp = array_column($get_id, 'Sample');
            $sample_id_arr = array_column($tmp, 'id');
            
            $dbChk = $this->SampleChecklist->getDataSource();
            try {
                $dbChk->begin();

                $finished_flag =$this->SampleChecklist->sec_ApproveChecklastcancle($admin_id, $period, $layer_code, $sample_id_arr, $category);

                if ($finished_flag == true) {
                    if($_POST['mailSend']) {
                        $mail_template = 'common';

                        #Mail contents
                        $mail['subject']        = $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body']  = $_POST['mailBody'];
                        $toEmail = parent::formatMailInput($_POST['toEmail']);
                        $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                        $bccEmail = parent::formatMailInput($_POST['bccEmail']);
                        $url = '/SampleImprovementResults?period='.$period.'&ba='.$layer_code.'&category='.$category;
                        
                        if (!empty($toEmail)) {
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                            
                            #if does not mail have user validation
                            if ($sentMail["error"]) {
                                $msg = $sentMail["errormsg"];
                                $this->Flash->set($msg, array("key"=>"UserError"));
                                return $this->redirect(array('action' => 'index'));
                            } else {
                                $dbChk->commit();

                                $successMsg = parent::getSuccessMsg('SS006');
                                $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                                $successMsg = parent::getSuccessMsg('SS018');
                                $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                                return $this->redirect(array('action' => 'index'));
                            }
                        } else {
                            CakeLog::write('debug', 'Account Manager (level 2)  emails are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $err = parent::getErrorMsg('SE059');
                            $this->Flash->set($err, array("key"=>"UserError"));
                            return $this->redirect(array('action' => 'index'));
                        }
                    }else {
                        $dbChk->commit();

                        $successMsg = parent::getSuccessMsg('SS006');
                        $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                        
                        return $this->redirect(array('action' => 'index'));
                    }
                } else {
                    throw new Exception();
                }
            } /* end try*/
            catch (Exception $e) {
                $dbChk->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $err = parent::getErrorMsg('SE003');
                $err .= " ".$invalid_email;
                $this->Flash->set($err, array("key"=>"UserError"));
                return $this->redirect(array('action' => 'index'));
            }
        } else {
            $err = parent::getErrorMsg('SE017', [__('承認キャンセル')]);
            $this->Flash->set($err, array("key"=>"UserError"));
            return $this->redirect(array('action' => 'index'));
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
