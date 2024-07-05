
<?php
ob_get_contents();// to clear POST Content length error when file upload
ob_end_clean();// to clear POST Content length error when file upload

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\Exception\GoogleException;

define('PAGE', 'SampleTestResults');
define('MENUID', 2);
class SampleTestResultsController extends AppController
{
    public $uses = array('SampleTestResult','Sample','SampleAccAttachment','SampleChecklist','Layer', 'SampleTestResultsQuestion');
    public $components = array('Session','Flash');
    
    public function beforeFilter()
    {
        parent::checkSampleUrlSession();#checkurlsession
    }
    
    public function index()
    {
        $this->layout     = 'samplecheck';
        $successMsg       = '';
        $errorMsg         = '';
        $not_exist_data   = '';
        $show_approve_cancel = false;
        $show_approve     = false;
        $show_reject      = false;
        $show_save        = false;
        $show_review      = false;
        $enable_flag      = '';
        if ($this->Session->check('successMsg')) {
            $successMsg = $this->Session->read('successMsg');
            $this->Session->delete('successMsg');
        }
        if ($this->Session->check('errorMsg')) {
            $errorMsg = $this->Session->read('errorMsg');
            $this->Session->delete('errorMsg');
        }
        
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');      
        $role_id    = $this->Session->read('ADMIN_LEVEL_ID');
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $result = $this->Sample->search_testResult($layer_code, $period, $category);    
        # review
        # $user_level != 3
        $Common = new CommonController();
        # check user permission
        $permissions = $this->Session->read('PERMISSIONS');
        unset($permissions['index']);
        
        //get sample_id to find file in acc_attach db
        $result = array_column($result, 'samples');
        $get_id = array_column($result, 'id');
        $size = count($result);
        if (!empty($result)) {
            $flag = $result[0]['flag'];//for define disable or enable all input type
            if ($flag < 4) {
                $enable_flag = 'ReadOnly';
            }
             /* find report necessity2 is null or '' for approve button show	 */
            $chk_rpt2 = $this->SampleTestResult->find('all', array(
                'conditions' => array(
                        'OR' => array(
                            array('report_times' => 2),
                            array('report_times' => 3)
                        ),
                        'sample_id IN' => $get_id
                ),
                    'fields'=> array('report_times')
            ));
            
            $cnt_chk_rpt = count($chk_rpt2);
            if ($cnt_chk_rpt == 0) {
                // $approve_btn = true;
                $show_approve = true;
                $show_reject = true;
            }
            /* find sample id in sample_checklists is exist or not */
            $get_check_sample_id = $this->SampleTestResult->find('all', array(
                    'conditions' => array(
                            'sample_id IN' => $get_id
                    )
            ));
            
            if (empty($get_check_sample_id)) {       
                $show_approve_cancel = false; // for hide approve cancel button when sample 
                $show_save = true;
                $show_approve = false;
                $show_reject = false;
                $show_review = false;
            }

            if ($get_check_sample_id[0]['SampleTestResult']['flag'] == 3) {
                $show_approve_cancel = true; // for hide approve cancel button when sample id is in sample_checklists
                $show_save = false;
                $show_approve = false;
                $show_reject = false;
                $show_review = false;
            }

            if ($get_check_sample_id[0]['SampleTestResult']['flag'] == 1) {
                $show_approve_cancel = false; 
                $show_save = false;
                $show_approve = false;
                $show_reject = false;
                $show_review = true;
            }

            if ($get_check_sample_id[0]['SampleTestResult']['flag'] == 2) {
                $show_approve_cancel = false; // for hide approve cancel button when sample id is in sample_checklists
                $show_save = false;
                $show_approve = true;
                $show_reject = true;
                $show_review = false;
            }
            
            /* get account attachment file (status - 1)*/
            $acc_attach_file = $this->SampleAccAttachment->find('all', array(
                'conditions' => array(
                    'status' => 1,
                    'SampleAccAttachment.flag' => 1,
                    'sample_id IN' => $get_id
                )
            ));
        
            $acc_attach_file = array_column($acc_attach_file, 'SampleAccAttachment');
            $cnt_accAttach_file = count($acc_attach_file);
            
            /* get attachment file for business (status 2) */
            $business_file = $this->SampleAccAttachment->find('all', array(
                    'conditions' => array(
                            'status' => 2,
                            'SampleAccAttachment.flag' => 1,
                            'sample_id IN' => $get_id
                    )
            ));
            
            $business_file = array_column($business_file, 'SampleAccAttachment');
            $cnt_business_file = count($business_file);
            
            for ($i=0; $i<$size; $i++) {
                $sid = $i+1;
                $result[$i]['sid'] = $sid;
                $sample_id = $result[$i]['id'];
            
                if ($cnt_accAttach_file > 0) {
                    $result[$i]['acc_attach_file'] = $this->__prepareFileAttachment($cnt_accAttach_file, $acc_attach_file, $sample_id);
            
                    /* if some of the sample_data has no attachment then add file index to $data array */
                    if (!array_key_exists('acc_attach_file', $result[$i])) {
                        $result[$i]['acc_attach_file'] = [];
                    }
                } else {
                    $result[$i]['acc_attach_file'] = [];
                }
            
                if ($cnt_business_file > 0) {
                    $result[$i]['busi_attach_file'] = $this->__prepareFileAttachment($cnt_business_file, $business_file, $sample_id);
            
                    /* if some of the sample_data has no attachment then add file index to $data array */
                    if (!array_key_exists('busi_attach_file', $result[$i])) {
                        $result[$i]['busi_attach_file'] = [];
                    }
                } else {
                    $result[$i]['busi_attach_file'] = [];
                }
            }
        } else {
            $not_exist_data = parent::getErrorMsg("SE001");
        }
        /* to fill data from sample_test_results when approve*/
        $fill_data = $this->Sample->search_FillResult($layer_code, $period, $category);

        // get questions based on category
        $questions = $this->Sample->getQuestions($category);
        foreach($fill_data as $key=>$value){
            // get questions is checked or not
            $qIsChecked = $this->Sample->questionIsChecked($value['samples']['id']);
            if(count($qIsChecked) == 0){
                foreach($questions as $qKey=>$qValue){
                    $no = $qKey + 1;
                    $fill_data[$key]['test']['question'.$no] = 0;
                }
            }else{
                foreach($qIsChecked as $qKey=>$qValue){
                    $no = $qValue['test_ques']['question_id'];
                    
                    $fill_data[$key]['test']['question'.$no] = $qValue['test_ques']['is_checked'];
                }
            }
            
        }
        #create common controller object
        $Common = new CommonController();

        foreach ($permissions as $action => $permission) {
            if(($layer_code == '' && $permission['limit']==0) || in_array($layer_code, array_keys($permission['layers']))) {
                $checkButtonType[$action] = true;
            }
        }
        foreach($checkButtonType as $key => $button){  
            $checkButtonType[$key] = ${'show_'.str_replace(' ', '', $key)};
        }
        
        $this->set('admin_list', $admin_list);
        $this->set('checkButtonType', $checkButtonType);
        $this->set('data', $result);
        //$this->set('get_email_sample_id',$get_email_sample_id);
        $this->set('not_exist_data', $not_exist_data);
        $this->set('fill_data', $fill_data);
        $this->set('not_exist_data', $not_exist_data);
        $this->set('layer_code', $layer_code);
        $this->set('layer_name', $layer_name);
        $this->set('period', $period);
        $this->set('successMsg', $successMsg);
        $this->set('errorMsg', $errorMsg);
        $this->set('role_id', $role_id);
        $this->set('user_level', $user_level);
        $this->set('enable_flag', $enable_flag);
        $this->set('page', PAGE);
        $this->set('questions', $questions);
        $this->set('category', $category);
        $this->render('index');
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
     * File upload for Test Result
     */
    public function uploadSampleTestResultsFile()
    {
        // $this->autoRender = false;
        // $this->request->allowMethod('ajax');
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $error = false;
        $check_permission = true;
        $response = [];
        $save_file_info = [];
    
        if ($this->request->is('post')) {
            if (isset($this->request->data['File'])) {
                $admin_level = $this->Session->read('ADMIN_LEVEL_ID');
                // if ($admin_level != 1 && $admin_level != 2 && $admin_level != 3 && $admin_level != 4) {
                //     $check_permission = false;
                //     $msg = parent::getErrorMsg("SE016", [__("upload")]);
                //     $response = array(
                //             'error' => $msg
                //     );
                // }
    
                if ($check_permission) {
                    $action = $this->request->data['action'];//to decide save or update query
                    $sample_id = $this->request->data['sample_data_id'];
                    $sid = $this->request->data['sid'];
                    $file = $this->request->data['File']['upload_file'];
                    $count = count($file);
                    $ext_arr = ['exe'];//not allow extension
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
                            $save_file_info['status'] = 1;//for account department
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
                        if (!$error) {
                            for ($i=0; $i<$count; $i++) {
                                $layer_code = $this->Session->read('SESSION_LAYER_CODE');
                                $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
                                $year = date('Y', strtotime($period));
                                $month = date('m', strtotime($period));
                                $type = '経理';// for Account Department
                                $uploadFolderPath = CloudStorageInfo::FOLDER_NAME.'/Sample Check/SampleTestResults/'.$layer_code.'/'.$year.'/'.$month.'/'.$sid.'/'.$type.'/';
    
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
                                        $cond['status'] = 1;//for accounting
                                        $cond['url'] = $uploadFolderPath;
                                        $cond['file_name'] = $fileName;
                                        $cond['flag'] = 1;
                                        $this->SampleAccAttachment->updateAll(
                                            $update_arr,
                                            $cond
                                        );
                                    }

                                    $isUpload = $this->upload_file_to_cloud($fileName, $filePath, $uploadFolderPath);
                                    
                                    $attachDB->commit();
    
                                    $response = array(
                                            'file_name' => array(
                                                    'name' => $fileName,
                                                    'url' => $uploadFolderPath
                                            )
                                    );
                                    # to show success message when form reload
                                    $msg = parent::getSuccessMsg("SS007");
                                    $this->Flash->set($msg, array('key'=>'testresultOK'));
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
                }
            } else {
                $response = array(
                        'error'=> parent::getErrorMsg("SE014")
                );
            }
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
    public function upload_file_to_cloud($objectName, $source, $folderStructure)
    {
        $cloud = parent::connect_to_google_cloud_storage();
        $storage = $cloud[0];
        $bucketName = $cloud[1];
    
        $file = fopen($source, 'r');
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->object($folderStructure.$objectName);
        if ($object->exists()) {
            $object->delete();
        }
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
                if ($object->exists()) {
                    $stream = $object->downloadAsStream();
                    header('Content-disposition: attachment; filename*=UTF-8\'\''.rawurlencode($file_name));
                    echo $stream->getContents();
                    exit();
                } else {
                    $msg = parent::getErrorMsg("SE084");
                    $this->Flash->set($msg, array('key'=>'testResultFail'));
                    $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
                }
            } catch (GoogleException $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE012");
                $this->Flash->set($msg, array('key'=>'testResultFail'));
                $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
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
            $admin_id = $this->Session->read('LOGIN_ID');//get from session
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            
            $table_id = $this->request->data['attachment_id'];
            $url = $this->request->data['download_url'];//file_path
            $file_name = $this->request->data['download_file'];//file_name
            $url .= $file_name;// BA/2019/02/5/経理/220px-Vue.js_Logo_2.svg.png
            
            $chk_file_del_flag = $this->Sample->search_flag_file_delete($period, $table_id);
            if (!empty($chk_file_del_flag)) {
                $chk_file_del_flag = $chk_file_del_flag[0]['sample']['flag'];
            }
            if ($chk_file_del_flag == 4 || $chk_file_del_flag ==5) {
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
                    $condition['SampleAccAttachment.status'] = 1;//for account files
                    $this->SampleAccAttachment->updateAll(
                        $record,
                        $condition
                    );
                    $db->commit();
                    $msg = parent::getSuccessMsg('SS003');
                    $this->Flash->set($msg, array('key'=>'testresultOK'));
                    $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
                } catch (GoogleException $e) {
                    $db->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE009");
                    $this->Flash->set($msg, array('key'=>'testresultFail'));
                    $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
                }
            } else {
                $msg = parent::getErrorMsg("SE009");
                $this->Flash->set($msg, array('key'=>'testresultFail'));
                $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
        }
    }
    
    /**
     * Save SampleTestResults Data
     * @author - Aye Thandar Lwin
     */
    public function fun_save()
    {
        $Common = new CommonController();
        if ($this->request->is('post')) {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $login_user_name = $this->Session->read('LOGIN_USER');
            
            $test_result_data = $this->request->data('myJSONString');
            $save_test_resultdata = json_decode($test_result_data, true);
            $questions = $this->Sample->getQuestions($category);

           
            $success = 0;
            $success_test = 0;
            $cnt_sampleflag4 = '';

            //Prepare for return data
            $data = array();
            $msg = '';
            $error = 0;
            
            $user_id = $this->Session->read('LOGIN_ID');
            $role_id = $this->Session->read('ADMIN_LEVEL_ID');
            
            $date = date('Y-m-d H:i:s');
            $datas = array();
            $SampleTestResultsData = array();
            $param = array();
            $isNeedToSave = false;
            $this->Sample->begin();
            $this->SampleTestResult->begin();
            
            if (!empty($save_test_resultdata)) {
               $loop_data = count($save_test_resultdata);
                //prepare and save data for Test Result Data
                $ques_arr = array();
                $c = 0;
                for ($i =0; $i < $loop_data;$i++) {
                    
                    $sample_id = $save_test_resultdata[$i][0];
                    
                    $loop_ques = count($questions);
                    foreach($save_test_resultdata[$i][1] as $key=>$value){
                        if(is_null($value)) unset($save_test_resultdata[$i][1][$key]);
                    }
                   $start = $questions[0]['questions']['id'] - 1;
                   $loop_ques = $questions[count($questions) - 1]['questions']['id'];
                   $k = 0;
                    for ($j=$start;$j<$loop_ques;$j++) {
                        
                        $question_id = $save_test_resultdata[$i][1][$j];
                        $question = 'question'.$questions[$k]['questions']['id'];
                        if($question_id == $questions[$k]['questions']['id']) {
                            $$question = 1;
                        }else $$question = 0;
                        $ij = $i + $j;
                        $ques_arr[$c]['sample_id'] = $sample_id;
                        $ques_arr[$c]['question_id'] = $questions[$k]['questions']['id'];
                        $ques_arr[$c]['is_checked'] = $$question;
                        $c++;
                        $k++;
                    }
                    
                    $remark = trim($save_test_resultdata[$i][2]);
                    $point_out = trim($save_test_resultdata[$i][3]);
                    $report_times = 1;
                    
                    $report_necessity = $save_test_resultdata[$i][4];
                    if ($report_necessity != '') {
                        $report_necessity = 1;
                    } else {
                        $report_necessity = 0;
                    }
                    
                    $deadline_date = $save_test_resultdata[$i][5];
                    if ($deadline_date != '' || $deadline_date != null) {
                        $deadline_date = $deadline_date;
                    } else {
                        $deadline_date = '0000-00-00 00:00:00';
                    }
                    
                    $completion = $save_test_resultdata[$i][6];
                    if ($completion == 1) {
                        $test_result_finish = 1;
                    } else {
                        $test_result_finish = 0;
                    }
                    $SampleTestResultsData = array( 'sample_id' => $sample_id,
                    
                            'remark' => $remark,
                            'report_times'=>$report_times,
                            'point_out1'=>$point_out,
                            'report_necessary1'=>$report_necessity,
                            'deadline_date1'=>$deadline_date,
                            'testresult_finish'=>$test_result_finish,
                            'flag' =>1,
                            'created_by' =>$user_id,
                            'updated_by' => $user_id,
                            'created_date'=>$date
                            
                        );
                
                //
                $chk_sample_id = $this->SampleTestResult->find(
                        'all',
                        array(
                            'conditions' => array(
                                'sample_id' => $sample_id,
                                'SampleTestResult.flag <>' => 0
                                    )
                            )
                    );
                    //check sample flag is 4 when save btn click
                    $chk_sample_flag4 = $this->Sample->find(
                        'all',
                        array(
                                    'conditions' => array(
                                            'id' => $sample_id,
                                            'Sample.flag' => 4
                                    )
                            )
                    );
                
                    $cnt_sampleflag4 = count($chk_sample_flag4);
                    
                    if (count($chk_sample_id) <= 0 && $cnt_sampleflag4 >0) {
                        array_push($datas, $SampleTestResultsData);
                    } else {
                        $param["sample_id"] = $sample_id;
                        $param["remark"] = $remark;
                        $param["report_times"] = $report_times;
                        $param["point_out1"] = $point_out;
                        $param["report_necessary1"] = $report_necessity;
                        $param["deadline_date1"] = $deadline_date;//date('Y-m-d H:i:s', strtotime($deadline_date));
                        $param["testresult_finish"] = $test_result_finish;
                        // if ($role_id == 3) {
                        //     $param["flag"] = 2;
                        // } else {
                            $param["flag"] = 1;
                        // }
                        $param["created_by"]   = $user_id;
                        $param["updated_by"]   = $user_id;
                        $param["created_date"] = $date;
                        
                        /* find sample id in sample_checklists is exist or not */
                        $get_check_sample_id = $this->SampleChecklist->find('all', array(
                                'conditions' => array(
                                        'sample_id' => $sample_id
                                )
                        ));
                        $cnt_sample_id = count($get_check_sample_id);
                        if ($cnt_sample_id == 0) {
                            $this->SampleTestResult->UpdateSampleTestResultsFinish($param);
                            $chk_test_update = $this->SampleTestResult->getAffectedRows();
                            if ($chk_test_update > 0) {
                                $success_test = 1;
                            }
                        }
                    }
                    // if ($role_id == 4) {
                        $flag = 4;
                        
                        $this->Sample->Update_SampleTestResultsFlag($sample_id, $user_id, $layer_code, $period, $category);
                        $chk_update = $this->Sample->getAffectedRows();

                        if ($chk_update > 0) {
                            $success = 1;
                        }
                    // } elseif ($role_id == 3) {
                    //     if ($report_necessity == 1 && $test_result_finish == 1) {
                    //         $flag = 9;
                    //     } elseif ($report_necessity == 1 && $test_result_finish == 0) {
                    //         $flag = 6;
                    //     } elseif ($test_result_finish == 1 && $report_necessity == 0) {
                    //         $flag = 9;
                    //     } else {
                    //         $flag = 5;
                    //     }
                    //     /* find sample id in sample_checklists is exist or not */
                    //     $get_check_sample_id = $this->SampleChecklist->find('all', array(
                    //             'conditions' => array(
                    //                     'sample_id' => $sample_id
                    //             )
                    //     ));
                    //     $cnt_chk_sample_id = count($get_check_sample_id);
                    //     if ($cnt_chk_sample_id == 0) {
                    //         $this->Sample->AccSubMgrUpdateFlag($sample_id, $user_id, $layer_code, $period, $flag);
                    //         $chk_update_count = $this->Sample->getAffectedRows();
                    //         if ($chk_update_count > 0) {
                    //             $success_test = 1;
                    //         }
                    //     }
                    // }
                }
            }
           
            try {
                $this->SampleTestResult->saveAll($datas);
                $this->SampleTestResultsQuestion->saveAll($ques_arr);
                if ($success == 1 || $success_test == 1) {
                    # send email to manager (level 5)
                    $data_action = $this->request->data('data-action');
                    // $user_id_list = $Common->getAdminLevelID(PAGE, $data_action, 2);#page, function array, phase                        
                    $mail_template 			= 'common';
                    #Mail contents
                    // $mail['subject']	 	= '【サンプルチェック】 '.$layer_name.'テスト結果作成 経理課長レビュー完了通知';
                    $mail['subject'] 		= $this->request->data('mailSubj');
                    // $mail['template_title'] = '経理部長';
                    $mail['template_title'] = '';
                    // $mail['template_body'] 	= '当月の'.$layer_name.'のサンプルチェックデータの経理課長レビューが完了しました。<br>データをご確認の上、承認をお願い致します。<br/>';
                    $mail['template_body'] 	= $this->request->data('mailBody');
                    
                    #Search ManagerID in BA table (2)

                    // $toEmail = $Common->getUserEmail($layer_code, $manager_level_id);
                    $to_email = $this->request->data('toEmail');
                    $cc_email = $this->request->data('ccEmail');
                    $bcc_email = $this->request->data('bccEmail');

                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);

                        #mail send or not
                    $mail_send = $this->request->data('mailSend');

                    if($mail_send == 0){
                        $this->Sample->commit();
                        $this->SampleTestResult->commit();
                        $msg = parent::getSuccessMsg('SS001');
                        $this->Session->write('successMsg', $msg);
                        $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
                    }else{
                        if (!$toEmail) {
                            $msg = parent::getErrorMsg("SE059");
                            $error = 1;
                        }

                        #Replace following line if no need CC
                        if (!empty($toEmail)) {
                            // $url = '/SampleTestResults?period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                            $url = '/SampleTestResults?period='.$period.'&ba='.$layer_code.'&category='.$category;
                            $ccEmail ="";

                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                            
                            if ($sentMail["error"]) {
                                $msg = $sentMail["errormsg"];
                                $msg = $msg.parent::getErrorMsg('SE042');
                                $error = 1;
                            } else {
                                #if user email have save data
                                $this->Sample->commit();
                                $this->SampleTestResult->commit();
                                $msg = parent::getSuccessMsg('SS001');
                                $msg = $msg.parent::getSuccessMsg("SS018");
                            }
                        } else {
                            $msg = parent::getErrorMsg("SE059");
                            $error = 1;
                            CakeLog::write('debug', 'Manager email not found for BA Code:' .$layer_code. ', when login user id:' .$manager_level_id. '('.$manager_level_id.') click `Request` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        }
                    }
                    // } else {
                    //     $this->Sample->commit();
                    //     $this->SampleTestResult->commit();
                    //     $msg = parent::getSuccessMsg('SS001');
                    // }
                } else {
                    $msg = parent::getErrorMsg('SE017', [__('保存')]);
                    $error = 1;
                }
            } catch (Exception $e) {
                $this->SampleTestResult->rollback();
                $this->Sample->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg('SE017', [__('保存')]);
                $error = 1;
            }
             
            if ($error) {
                $this->Session->write('errorMsg', $msg);
            } else {
                $this->Session->write('successMsg', $msg);
            }
            $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
            
            // return json_encode($data);
        }
    }

    public function fun_review()
    {
        $Common = new CommonController();
        if ($this->request->is('post')) {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $login_user_name = $this->Session->read('LOGIN_USER');
            
            $test_result_data = $this->request->data('myJSONString');
            $save_test_resultdata = json_decode($test_result_data, true);
            $questions = $this->Sample->getQuestions($category);
            $success = 0;
            $success_test = 0;
            $cnt_sampleflag4 = '';

            //Prepare for return data
            $data = array();
            $msg = '';
            $error = 0;
            
            $user_id = $this->Session->read('LOGIN_ID');
            $role_id = $this->Session->read('ADMIN_LEVEL_ID');
            
            $date = date('Y-m-d H:i:s');
            $datas = array();
            $SampleTestResultsData = array();
            $param = array();
            $isNeedToSave = false;
            
            $this->Sample->begin();
            $this->SampleTestResult->begin();
            $sampleIdArr = array();
            if (!empty($save_test_resultdata)) {
                $loop_data = count($save_test_resultdata);
                $ques_arr = array();
                $c = 0;
                //prepare and save data for Test Result Data
                for ($i =0; $i < $loop_data;$i++) {
                    
                    $sample_id = $sampleIdArr[] = $save_test_resultdata[$i][0];
                    $loop_ques =  count($questions);
                    foreach($save_test_resultdata[$i][1] as $key=>$value){
                        if(is_null($value)) unset($save_test_resultdata[$i][1][$key]);
                    }
                    $start = $questions[0]['questions']['id'] - 1;
                    $loop_ques = $questions[count($questions) - 1]['questions']['id'];
                    $k = 0;
                    for ($j=$start;$j<$loop_ques;$j++) {
                        $question_id = $save_test_resultdata[$i][1][$j];
                        $question = 'question'.$questions[$k]['questions']['id'];
                        if($question_id == $questions[$k]['questions']['id']) {
                            $$question = 1;
                        }else $$question = 0;
                        $ij = $i + $j;
                        $ques_arr[$c]['sample_id'] = $sample_id;
                        $ques_arr[$c]['question_id'] = $questions[$k]['questions']['id'];
                        $ques_arr[$c]['is_checked'] = $$question;
                        $c++;
                        $k++;
                    }
                    
                    $remark = trim($save_test_resultdata[$i][2]);
                    $point_out = trim($save_test_resultdata[$i][3]);
                    $report_times = 1;
                    
                    $report_necessity = $save_test_resultdata[$i][4];
                    if ($report_necessity != '') {
                        $report_necessity = 1;
                    } else {
                        $report_necessity = 0;
                    }
                    
                    $deadline_date = $save_test_resultdata[$i][5];
                    if ($deadline_date != '' || $deadline_date != null) {
                        $deadline_date = $deadline_date;
                    } else {
                        $deadline_date = '0000-00-00 00:00:00';
                    }
                    
                    $completion = $save_test_resultdata[$i][6];
                    if ($completion == 1) {
                        $test_result_finish = 1;
                    } else {
                        $test_result_finish = 0;
                    }
                
                    $SampleTestResultsData = array( 'sample_id' => $sample_id,
                        'remark' => $remark,
                        'report_times'=>$report_times,
                        'point_out1'=>$point_out,
                        'report_necessary1'=>$report_necessity,
                        'deadline_date1'=>$deadline_date,
                        'testresult_finish'=>$test_result_finish,
                        'flag' =>1,
                        'created_by' =>$user_id,
                        'updated_by' => $user_id,
                        'created_date'=>$date
                
                    );
                    //
                    $chk_sample_id = $this->SampleTestResult->find(
                        'all',
                        array(
                                    'conditions' => array(
                                            'sample_id' => $sample_id,
                                            'SampleTestResult.flag <>' => 0
                                    )
                            )
                    );
                    //check sample flag is 4 when save btn click
                    $chk_sample_flag4 = $this->Sample->find(
                        'all',
                        array(
                                    'conditions' => array(
                                            'id' => $sample_id,
                                            'Sample.flag' => 4
                                    )
                            )
                    );
                
                    $cnt_sampleflag4 = count($chk_sample_flag4);
                    
                    if (count($chk_sample_id) <= 0 && $cnt_sampleflag4 >0) {
                        array_push($datas, $SampleTestResultsData);
                    } else {
                        $param["sample_id"] = $sample_id;
                        $param["remark"] = $remark;
                        $param["report_times"] = $report_times;
                        $param["point_out1"] = $point_out;
                        $param["report_necessary1"] = $report_necessity;
                        $param["deadline_date1"] = $deadline_date;//date('Y-m-d H:i:s', strtotime($deadline_date));
                        $param["testresult_finish"] = $test_result_finish;
                        //if ($role_id == 3) {
                            $param["flag"] = 2;
                        // } else {
                        //     $param["flag"] = 1;
                        // }
                        $param["created_by"]   = $user_id;
                        $param["updated_by"]   = $user_id;
                        $param["created_date"] = $date;
                        
                        /* find sample id in sample_checklists is exist or not */
                        $get_check_sample_id = $this->SampleChecklist->find('all', array(
                                'conditions' => array(
                                        'SampleChecklist.sample_id' => $sample_id
                                )
                        ));
                        $cnt_sample_id = count($get_check_sample_id);
                        if ($cnt_sample_id == 0) {
                            $this->SampleTestResult->UpdateSampleTestResultsFinish($param);
                            $chk_test_update = $this->SampleTestResult->getAffectedRows();
                            if ($chk_test_update > 0) {
                                $success_test = 1;
                            }
                        }
                    }
                    // if ($role_id == 4) {
                        // $flag = 4;
                        
                        // $this->Sample->Update_SampleTestResultsFlag($sample_id, $user_id, $layer_code, $period);
                        // $chk_update = $this->Sample->getAffectedRows();

                        // if ($chk_update > 0) {
                        //     $success = 1;
                        // }
                    // if ($role_id == 3) {
                        if ($report_necessity == 1 && $test_result_finish == 1) {
                            $flag = 9;
                        } elseif ($report_necessity == 1 && $test_result_finish == 0) {
                            $flag = 6;
                        } elseif ($test_result_finish == 1 && $report_necessity == 0) {
                            $flag = 9;
                        } else {
                            $flag = 5;
                        }
                        /* find sample id in sample_checklists is exist or not */
                        $get_check_sample_id = $this->SampleChecklist->find('all', array(
                                'conditions' => array(
                                        'SampleChecklist.sample_id' => $sample_id
                                )
                        ));
                        $cnt_chk_sample_id = count($get_check_sample_id);
                        if ($cnt_chk_sample_id == 0) {
                            $this->Sample->AccSubMgrUpdateFlag($sample_id, $user_id, $layer_code, $period, $flag, $category);
                            $chk_update_count = $this->Sample->getAffectedRows();
                            if ($chk_update_count > 0) {
                                $success_test = 1;
                            }
                        }
                   // }
                }
            }
            
            try {
                $this->SampleTestResult->saveAll($datas);
                $this->SampleTestResultsQuestion->deleteAll(array(
                    'SampleTestResultsQuestion.sample_id ' => $sampleIdArr,
                ));
                $this->SampleTestResultsQuestion->saveAll($ques_arr);
                if ($success == 1 || $success_test == 1) {
                    # send email to manager (level 5)
                    $data_action = $this->request->data('data-action');
                    // $user_id_list = $Common->getAdminLevelID(PAGE, $data_action, 2);#page, function array, phase

                    // pr($user_id_list);
                    // die();
                    // if ($role_id == 3) {
                        #Set account manager ID (2)
                        //$manager_level_id  = AdminLevel::ACCOUNT_MANAGER;
                        
                        $mail_template 			= 'common';
                        #Mail contents
                        // $mail['subject']	 	= '【サンプルチェック】 '.$layer_name.'テスト結果作成 経理課長レビュー完了通知';
                        $mail['subject'] 		= $this->request->data('mailSubj');
                        // $mail['template_title'] = '経理部長';
                        $mail['template_title'] = '';
                        // $mail['template_body'] 	= '当月の'.$layer_name.'のサンプルチェックデータの経理課長レビューが完了しました。<br>データをご確認の上、承認をお願い致します。<br/>';
                        $mail['template_body'] 	= $this->request->data('mailBody');
                        
                        #Search ManagerID in BA table (2)

                        // $toEmail = $Common->getUserEmail($layer_code, $manager_level_id);
                        $to_email = $this->request->data('toEmail');
                        $cc_email = $this->request->data('ccEmail');
                        $bcc_email = $this->request->data('bccEmail');

                        $toEmail = parent::formatMailInput($to_email);
                        $ccEmail = parent::formatMailInput($cc_email);
                        $bccEmail = parent::formatMailInput($bcc_email);

                        $mail_send = $this->request->data('mailSend');

                        if($mail_send == 0){
                            $this->Sample->commit();
                            $this->SampleTestResult->commit();
                            $msg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($msg, array('key'=>'testresultOK'));
                            $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));

                        }else{
                            if (!$toEmail) {
                                $msg = parent::getErrorMsg("SE059");
                                $error = 1;
                            }
    
                            #Replace following line if no need CC
                            if (!empty($toEmail)) {
                                // $url = '/SampleTestResults?period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                                $url = '/SampleTestResults?period='.$period.'&ba='.$layer_code.'&category='.$category;
                                $ccEmail ="";
    
                                $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                
                                if ($sentMail["error"]) {
                                    $msg = $sentMail["errormsg"];
                                    $msg = $msg.parent::getErrorMsg('SE042');
                                    $error = 1;
                                } else {
                                    #if user email have save data
                                    $this->Sample->commit();
                                    $this->SampleTestResult->commit();
                                    $msg = parent::getSuccessMsg('SS001');
                                    $msg = $msg.parent::getSuccessMsg("SS018");
                                }
                            } else {
                                $msg = parent::getErrorMsg("SE059");
                                $error = 1;
                                CakeLog::write('debug', 'Manager email not found for BA Code:' .$layer_code. ', when login user id:' .$manager_level_id. '('.$manager_level_id.') click `Request` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            }
                        }
                    // } else {
                    //     $this->Sample->commit();
                    //     $this->SampleTestResult->commit();
                    //     $msg = parent::getSuccessMsg('SS001');
                    // }
                } else {
                    $msg = parent::getErrorMsg('SE017', [__('保存')]);
                    $error = 1;
                }
            } catch (Exception $e) {
                $this->SampleTestResult->rollback();
                $this->Sample->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg('SE017', [__('保存')]);
                $error = 1;
            }
            if ($error) {
                $this->Session->write('errorMsg', $msg);
            } else {
                $this->Session->write('successMsg', $msg);
            }
            // $msg = parent::getErrorMsg("SE017", [__("依頼")]);//no data to request
            $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
            
            // return json_encode($data);
        }
    }

    /**
     * Save&Email Sent Mail For user level 3
     * @author - Sandi Khaing
     */
    public function Ajax_SaveDataSentMail()
    {
        $Common = new CommonController();
    
        $this->autoRender = false; // We don't render a view in this example
        //$this->request->allowMethod('ajax'); // No direct access via browser URL
        $layout = ''; // you need to have a no html page, only the data
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $login_user_name = $this->Session->read('LOGIN_USER');
        
        $test_result_data = $this->request->data('myJSONString');
        $save_test_resultdata = json_decode($test_result_data, true);

        $to_email = $_POST['toEmail'];
        
        $success = 0;
        $success_test = 0;
        $cnt_sampleflag4 = '';

        //Prepare for return data
        $data = array();
        $msg = '';
        $error = 0;
        
        $user_id = $this->Session->read('LOGIN_ID');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        
        $date = date('Y-m-d H:i:s');
        $datas = array();
        $SampleTestResultsData = array();
        $param = array();
        $isNeedToSave = false;
        
        $this->Sample->begin();
        $this->SampleTestResult->begin();
        
        if (!empty($save_test_resultdata)) {
            $loop_data = count($save_test_resultdata);
            
            //prepare and save data for Test Result Data
            for ($i =0; $i < $loop_data;$i++) {
                $question1 = 0;
                $question2 = 0;
                $question3 = 0;
                $question4 = 0;
                $question5 = 0;
                $question6 = 0;
                $question7 = 0;
                
                $sample_id = $save_test_resultdata[$i][0];
            
                $loop_ques = count($save_test_resultdata[$i][1]);
                
                for ($j=0;$j<$loop_ques;$j++) {
                    $question_id = $save_test_resultdata[$i][1][$j];
                    if ($question_id == 1) {
                        $question1 = 1;
                    }
                    if ($question_id == 2) {
                        $question2 = 1;
                    }
                    if ($question_id == 3) {
                        $question3 = 1;
                    }
                    if ($question_id == 4) {
                        $question4 = 1;
                    }
                    if ($question_id == 5) {
                        $question5 = 1;
                    }
                    if ($question_id == 6) {
                        $question6 = 1;
                    }
                    if ($question_id == 7) {
                        $question7 = 1;
                    }
                }
                
                $remark = trim($save_test_resultdata[$i][2]);
                $point_out = trim($save_test_resultdata[$i][3]);
                $report_times = 1;
                
                $report_necessity = $save_test_resultdata[$i][4];
                if ($report_necessity != '') {
                    $report_necessity = 1;
                } else {
                    $report_necessity = 0;
                }
                
                $deadline_date = $save_test_resultdata[$i][5];
                if ($deadline_date != '' || $deadline_date != null) {
                    $deadline_date = $deadline_date;
                } else {
                    $deadline_date = '0000-00-00 00:00:00';
                }
                
                $completion = $save_test_resultdata[$i][6];
                if ($completion == 1) {
                    $test_result_finish = 1;
                } else {
                    $test_result_finish = 0;
                }
            
                $SampleTestResultsData = array( 'sample_id' => $sample_id,
                    'question1' => $question1,
                    'question2' => $question2,
                    'question3' => $question3,
                    'question4' => $question4,
                    'question5' => $question5,
                    'question6' => $question6,
                    'question7' => $question7,
                    'remark' => $remark,
                    'report_times'=>$report_times,
                    'point_out1'=>$point_out,
                    'report_necessary1'=>$report_necessity,
                    'deadline_date1'=> $deadline_date,//date('Y-m-d H:i:s', strtotime($deadline_date)),
                    'testresult_finish'=>$test_result_finish,
                    'flag' =>1,
                    'created_by' =>$user_id,
                    'updated_by' => $user_id,
                    'created_date'=>$date
            
                );
                //
                $chk_sample_id = $this->SampleTestResult->find(
                    'all',
                    array(
                                'conditions' => array(
                                        'sample_id' => $sample_id,
                                        'SampleTestResult.flag <>' => 0
                                )
                        )
                );
                //check sample flag is 4 when save btn click
                $chk_sample_flag4 = $this->Sample->find(
                    'all',
                    array(
                                'conditions' => array(
                                        'id' => $sample_id,
                                        'Sample.flag' => 4
                                )
                        )
                );
            
                $cnt_sampleflag4 = count($chk_sample_flag4);
                
                if (count($chk_sample_id) <= 0 && $cnt_sampleflag4 >0) {
                    array_push($datas, $SampleTestResultsData);
                } else {
                    $param["sample_id"] = $sample_id;
                    $param["question1"] = $question1;
                    $param["question2"] = $question2;
                    $param["question3"] = $question3;
                    $param["question4"] = $question4;
                    $param["question5"] = $question5;
                    $param["question6"] = $question6;
                    $param["question7"] = $question7;
                    $param["remark"] = $remark;
                    $param["report_times"] = $report_times;
                    $param["point_out1"] = $point_out;
                    $param["report_necessary1"] = $report_necessity;
                    $param["deadline_date1"] = $deadline_date;//date('Y-m-d H:i:s', strtotime($deadline_date));
                    $param["testresult_finish"] = $test_result_finish;
                    if ($role_id == 3) {
                        $param["flag"] = 2;
                    } else {
                        $param["flag"] = 1;
                    }
                    $param["created_by"]   = $user_id;
                    $param["updated_by"]   = $user_id;
                    $param["created_date"] = $date;
                    
                    /* find sample id in sample_checklists is exist or not */
                    $get_check_sample_id = $this->SampleChecklist->find('all', array(
                            'conditions' => array(
                                    'sample_id' => $sample_id
                            )
                    ));
                    $cnt_sample_id = count($get_check_sample_id);
                    if ($cnt_sample_id == 0) {
                        $this->SampleTestResult->UpdateSampleTestResultsFinish($param);
                        $chk_test_update = $this->SampleTestResult->getAffectedRows();
                        if ($chk_test_update > 0) {
                            $success_test = 1;
                        }
                    }
                }
                if ($role_id == 4) {
                    $flag = 4;
                    
                    $this->Sample->Update_SampleTestResultsFlag($sample_id, $user_id, $layer_code, $period, $flag);
                    $chk_update = $this->Sample->getAffectedRows();
                    if ($chk_update > 0) {
                        $success = 1;
                    }
                } elseif ($role_id == 3) {
                    if ($report_necessity == 1 && $test_result_finish == 1) {
                        $flag = 9;
                    } elseif ($report_necessity == 1 && $test_result_finish == 0) {
                        $flag = 6;
                    } elseif ($test_result_finish == 1 && $report_necessity == 0) {
                        $flag = 9;
                    } else {
                        $flag = 5;
                    }
                    /* find sample id in sample_checklists is exist or not */
                    $get_check_sample_id = $this->SampleChecklist->find('all', array(
                            'conditions' => array(
                                    'sample_id' => $sample_id
                            )
                    ));
                    $cnt_chk_sample_id = count($get_check_sample_id);
                    if ($cnt_chk_sample_id == 0) {
                        $this->Sample->AccSubMgrUpdateFlag($sample_id, $user_id, $layer_code, $period, $flag, $category);
                        $chk_update_count = $this->Sample->getAffectedRows();
                        if ($chk_update_count > 0) {
                            $success_test = 1;
                        }
                    }
                }
            }
        }
        
        try {
            $this->SampleTestResult->saveAll($datas);
            
            if ($success == 1 || $success_test == 1) {
                if ($role_id = 4) {

                    # send email to Account In Charge (level 4) and Account tcl (level 3)
                    $manager_level_id  = AdminLevel::ACCOUNT_SECTION_MANAGER;

                    $mail_template 			= 'common';
                    $mail['subject'] 		= $_POST['mailSubj'];
                    $mail['template_title'] = $_POST['mailTitle'];
                    $mail['template_body'] 	= $_POST['mailBody'];

                    #send email to account tantoesha(level 3) and cc Kacho (level 3)
                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail="";
                    
                    // $url = '/SampleTestResults?'.'period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                    $url = '/SampleTestResults?'.'period='.$period.'&ba='.$layer_code;
                            
                    if (!empty($toEmail)) {
                        $mail = parent::sendEmail($layer_code, $layer_name, $period, $login_user_name, $toEmail, $ccEmail, $mail_template, $mail, $url);

                        if ($mail["error"]) {
                            $error = 1;
                            $msg = $mail["errormsg"];
                            $this->Flash->set($msg, array('key'=>'testResultFail'));
                            $invalid_email = parent::getErrorMsg('SE042');
                            $this->Flash->set($invalid_email, array('key'=>'testResultFail'));
                        } else {
                            $msg = parent::getSuccessMsg("SS001");
                            $this->Flash->set($msg, array('key'=>'testresultOK'));

                            $msg = parent::getSuccessMsg("SS018");
                            $this->Flash->set($msg, array('key'=>'testresultOK'));
                        }
                    } else {
                        $msg = parent::getErrorMsg("SE059");
                        $error = 1;
                        CakeLog::write('debug', 'Incharge(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$login_user_name. '('.$login_user_name.') click `Approve Cancel` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    }
                } else {
                    $msg = parent::getErrorMsg('SE036');
                    $this->Flash->set($msg, array('key'=>'testResultFail'));
                    $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
                }
            } else {	#when data is already save show message
                $error = 1;
                $msg = parent::getErrorMsg('SE017', [__('保存')]);
                $this->Flash->set($msg, array('key'=>'testResultFail'));
            }
        } catch (Exception $e) {
            $this->SampleTestResult->rollback();
            $this->Sample->rollback();
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $error = 1;
            $msg = parent::getErrorMsg("SE036");
            $this->Flash->set($msg, array('key'=>'testResultFail'));
        }

        if ($error) {
            $data = array(
                    'content' => "",
                    'error'   => $msg
            );
        } else {
            $data = array(
                        'content' => $msg,
                        'error'   => ""
                );
        }
        $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
    }
    /**
     * Approve Test Result Data
     * @author - Aye Thandar Lwin
     */

    public function fun_approve()
    {
        $Common = new CommonController;
        
        // $this->autoRender = false; // We don't render a view in this example
        // $this->request->allowMethod('ajax'); // No direct access via browser URL
        #only allow ajax request
        // parent::checkAjaxRequest($this);
        $layout = 'samplecheck'; // you need to have a no html page, only the data
        if ($this->request->is('post')) {
            $success_sample = '';
            $success_test = '';
            
            //prepare for return data
            $data = array();
            $msg = '';
            $error = 0;
            
            $mail = array();
            
            $login_id = $this->Session->read('LOGIN_ID');
            
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            
            $login_user_name = $this->Session->read('LOGIN_USER');
            
            $Apptest_result_data = $this->request->data('myJSONString');
            $approve_test_result = json_decode($Apptest_result_data, true);

            $to_email = $this->request->data('toEmail');
            $cc_email = $this->request->data('ccEmail');
            $bcc_email = $this->request->data('bccEmail');
            
            $toEmail = parent::formatMailInput($to_email);
            $ccEmail = parent::formatMailInput($cc_email);
            $bccEmail = parent::formatMailInput($bcc_email);

            $sample_update = array();
            $cnt = count($approve_test_result);
            $sample_id_arr = array();
            #mail send or not
            $mail_send = $this->request->data('mailSend');

            try {
                // if (!empty($toEmail)) {
                    for ($i =0; $i < $cnt;$i++) {
                        $sample_id = $approve_test_result[$i][0];
                        array_push($sample_id_arr, $sample_id);
                        
                        $this->Sample->begin();
                        
                        $this->Sample->Update_Approve_sample_flag($sample_id, $layer_code, $period, $login_id, $category);
                        
                        $sample_update = $this->Sample->getAffectedRows();
                        
                        $success_sample += 1;
                    }
            
                    if ($success_sample == $cnt) {

                        #Get check for mail template report nessary 1 or 0
                        $get_rp1_check = $this->SampleTestResult->find('all', array(
                                    'conditions' => array(
                                            'sample_id' => $sample_id_arr,
                                            'report_necessary1' => 1,
                                            'NOT'=>array('SampleTestResult.flag '=>'0')
                                            
                                    )
                        ));

                        $get_point_out = $this->SampleTestResult->find('all', array(
                                    'fields' => array('SampleTestResult.point_out1','SampleTestResult.deadline_date1','samples.index_no'),
                                    'conditions' => array(
                                            'sample_id' => $sample_id_arr,
                                            
                                    ),
                                    'joins' => array(
                                        array(
                                            'table' => 'samples',
                                            'alias' => 'samples',
                                            'type' => 'left',
                                            'conditions' => 'SampleTestResult.sample_id = samples.id'
                                        )
                                    )
                        ));

                        $deadlineDate = "";
                        $ridIndexPoint = "";
                        $rid = 0;
                        foreach ($get_point_out as $each) {
                            $rid++;
                            $ridIndexPoint .= "RID".$rid."&lt;".$each['samples']['index_no']."&gt; <br/>".$each['SampleTestResult']['point_out1']."<br/>";
                            #check deadline1 date
                            if ($each['SampleTestResult']['deadline_date1'] != '0000-00-00 00:00:00') {
                                $date = new DateTime($each['SampleTestResult']['deadline_date1']);
                                $deadlineDate .= '提出期日：'.date_format($date, "Y-m-d").'<br/>';
                            } else {
                                $date = '';
                                $deadlineDate .= $date;
                            }
                        }    
                        $mail_template 			= 'common';
                        $mail['subject'] 		= $this->request->data('mailSubj');
                        $mail['template_title'] = '';
                        $mail['template_body'] 	= $this->request->data('mailBody');
                        if($mail_send == 0){
                            $this->Sample->commit();
                            $msg = parent::getSuccessMsg('SS005');
                            $this->Session->write('successMsg',$msg);
                            $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
                        }else{
                             if (!empty($toEmail)) {
                                 // $url = '/SampleChecklists?period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                                 $url = '/SampleChecklists?'.'period='.$period.'&ba='.$layer_code.'&category='.$category;
         
                                 $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                     
                                 if ($sentMail["error"]) {
                                     $msg = $sentMail["errormsg"];
                                     $msg .= parent::getErrorMsg('SE042');
                                     $error = 1;
                                 // $this->Flash->set($msg, array('key'=>'testresultFail'));
                                 } else {
                                     $this->Sample->commit();
                                     $msg = parent::getSuccessMsg('SS005');
                                     $msg .= parent::getSuccessMsg("SS018");
                                 }
                             }else{
                                $msg = parent::getErrorMsg("SE059");
                                $error = 1;
                                // $this->Flash->set($msg, array('key'=>'testresultFail'));
                                CakeLog::write('debug', 'Incharge(level 4) and Sub-Manager(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$login_user_name. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                             }
                        }
                                                                    
                    } else {
                        $msg = parent::getErrorMsg('SE033');
                        $error = 1;
                    }
            } catch (Exception $e) {
                $this->Sample->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    
                $msg = parent::getErrorMsg("SE033");
                $error = 1;
            }
            if ($error) {
                $this->Session->write('errorMsg',$msg);
            } else {
                $this->Session->write('successMsg',$msg);
            }
            $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
        }
    }
    /**
     * Approve Cancel SAP Data
     * @author - Aye Thandar Lwin
     */
    public function fun_approve_cancel()
    {
        $Common = new CommonController();
        $layout = ''; // you need to have a no html page, only the data
        if ($this->request->is('post')) {

                $login_id = $this->Session->read('LOGIN_ID');
            
                $layer_code = $this->Session->read('SESSION_LAYER_CODE');
                $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
                $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
                $category = $this->Session->read('SAMPLECHECK_CATEGORY');
                $login_user_name = $this->Session->read('LOGIN_USER');
            
                $Apptest_result_data =  $this->request->data('myJSONString');
                $approve_test_result = json_decode($Apptest_result_data, true);
                
                $success_cancel = '';
                $cancel_test = '';
                $msg = '';
                $error = 0;
                
                $cnt = count($approve_test_result);
                
                try {
                    //Update data for Account Manager Approve Cancel
                    for ($i =0; $i < $cnt;$i++) {
                        $sample_id = $approve_test_result[$i][0];
                        $chk_checklist = $this->SampleChecklist->CheckSampleId($sample_id);
                        $cnt_checklist = count($chk_checklist);
                        
                        if ($cnt_checklist <= 0) {
                            $this->Sample->begin();
                            $this->SampleTestResult->begin();
                            $update_flag = $this->SampleTestResult->Cancel_Approve_flag($sample_id, $login_id);
                            $update_sample_flag = $this->Sample->Cancel_Approve_sample_flag($sample_id, $login_id);
                        
                            $sample_update = $this->Sample->getAffectedRows();
                            if ($sample_update > 0) {
                                $success_cancel = 1;
                            }
                            $testresult_update = $this->SampleTestResult->getAffectedRows();
                            if ($testresult_update > 0) {
                                $cancel_test = 1;
                            }
                        }
                    }
                    if ($success_cancel ==1 && $cancel_test ==1) {
        
                        # send email to Account In Charge (level 4) and Account tcl (level 3)
                        // $manager_level_id  = AdminLevel::ACCOUNT_SECTION_MANAGER;
                        // $incharge_level_id = AdminLevel::ACCOUNT_INCHARGE;

                        $to_email = $this->request->data('toEmail');
                        $cc_email = $this->request->data('ccEmail');
                        $bcc_email = $this->request->data('bccEmail');
            
                        $toEmail = parent::formatMailInput($to_email);
                        $ccEmail = parent::formatMailInput($cc_email);
                        $bccEmail = parent::formatMailInput($bcc_email);
                        
                        $mail_template 			= 'common';
                        $mail['subject'] 		= $this->request->data('mailSubj');
                        $mail['template_title'] = '';
                        $mail['template_body'] 	= $this->request->data('mailBody');
                        
                        #mail send or not
                        $mail_send = $this->request->data('mailSend');
                        
                        if($mail_send == 0){
                            $this->SampleTestResult->commit();
                            $this->Sample->commit();    
                            $msg = parent::getSuccessMsg("SS006");
                            $this->Session->write('successMsg',$msg);
                            $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
                        }else{
                            $url = '/SampleTestResults?'.'period='.$period.'&ba='.$layer_code.'&category='.$category;
                            if (!empty($toEmail)) {                               
                                $mail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
            
                                if ($mail["error"]) {
                                    $msg = $mail["errormsg"];
                                    $msg .= parent::getErrorMsg('SE042');
                                    $error = 1;
                                } else {
                                    $this->SampleTestResult->commit();
                                    $this->Sample->commit();
            
                                    $msg = parent::getSuccessMsg("SS006");
                                    $msg .= parent::getSuccessMsg("SS018");
                                }
                            } else {
                                CakeLog::write('debug', 'Incharge(level 7) and Sub-Manager(level 6) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$login_id. '('.$login_user_name.') click `Approve Cancel` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            }
                        }        
                                
                    } else {
                        $msg = parent::getErrorMsg('SE036');
                        $error = 1;
                    }
                } catch (Exception $e) {
                    $this->SampleTestResult->rollback();
                    $this->Sample->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE036");
                    $this->Flash->set($msg, array('key'=>'testResultFail'));
                }
                if ($error) {
                    $this->Session->write('errorMsg',$msg);
                } else {
                    $this->Session->write('successMsg',$msg);
                }
                $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
        }

    }

    /**
     *Reject SAP DATA (Update  sample flag 5 and Test result flag = 1 )
     * @author - Sandi khaing
     */
    public function fun_reject()
    {
        $Common = new CommonController();
        $layout = ''; // you need to have a no html page, only the data
        if ($this->request->is('post')) {
            $login_id = $this->Session->read('LOGIN_ID');
        
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $login_user_name = $this->Session->read('LOGIN_USER');
        
            $Apptest_result_data =  $this->request->data('myJSONString');
            $approve_test_result = json_decode($Apptest_result_data, true);
            
            $success_cancel = '';
            $cancel_test = '';
            $msg = '';
            $error = 0;
            
            $cnt = count($approve_test_result);
            
            try {
                //Update data for Account Manager Approve Cancel
                for ($i =0; $i < $cnt;$i++) {
                    $sample_id = $approve_test_result[$i][0];
                    $chk_checklist = $this->SampleChecklist->CheckSampleId($sample_id);
                    $cnt_checklist = count($chk_checklist);
                    
                    if ($cnt_checklist <= 0) {
                        $this->Sample->begin();
                        $this->SampleTestResult->begin();
                        
                        $update_flag = $this->SampleTestResult->Cancel_Reject_flag($sample_id, $login_id);
                        $update_sample_flag = $this->Sample->Cancel_Reject_sample_flag($sample_id, $login_id);
                    
                        $sample_update = $this->Sample->getAffectedRows();
                        if ($sample_update > 0) {
                            $success_cancel = 1;
                        }
                        $testresult_update = $this->SampleTestResult->getAffectedRows();
                        if ($testresult_update > 0) {
                            $cancel_test = 1;
                        }
                    }
                }

                if ($success_cancel ==1 && $cancel_test ==1) {                   
                    $to_email = $this->request->data('toEmail');
                    $cc_email = $this->request->data('ccEmail');
                    $bcc_email = $this->request->data('bccEmail');

                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);

                    $mail_template 			= 'common';
                    #Mail contents
                    $mail['subject'] 		= $this->request->data('mailSubj');
                    $mail['template_title'] = '';
                    $mail['template_body'] 	= $this->request->data('mailBody');

                    #mail send or not
                    $mail_send = $this->request->data('mailSend');

                    if($mail_send == 0){
                        $this->SampleTestResult->commit();
                        $this->Sample->commit();
                        $msg = parent::getSuccessMsg('SS014');
                        $this->Session->Write('successMsg',$msg);
                        $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
                    }else{
                        // $url = '/SampleTestResults?'.'period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                        $url = '/SampleTestResults?'.'period='.$period.'&ba='.$layer_code.'&category='.$category;
                        if (!empty($toEmail)) {    
                            $mail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
        
                            if ($mail["error"]) {
                                $msg = $mail["errormsg"];
                                $msg .= parent::getErrorMsg('SE042');
                                $error = 1;
                            } else {
                                $this->SampleTestResult->commit();
                                $this->Sample->commit();
        
                                $msg = parent::getSuccessMsg("SS014");
                                $msg .= parent::getSuccessMsg("SS018");
                            }
                        } else {
                            CakeLog::write('debug', 'Incharge(level 7) and Sub-Manager(level 6) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$login_id. '('.$login_user_name.') click `Approve Cancel` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        }
                    }
    
                } else {
                    $msg = parent::getErrorMsg('SE068');
                    $error = 1;
                }
            } catch (Exception $e) {
                $this->SampleTestResult->rollback();
                $this->Sample->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE068");
                $error = 1;
            }
            if ($error) {
               $this->Session->write('errorMsg',$msg);
            } else {
                $this->Session->write('successMsg',$msg);
            }
            $this->redirect(array('controller'=>'SampleTestResults', 'action'=>'index'));
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
