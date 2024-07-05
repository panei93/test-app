<?php
ob_get_contents();// to clear POST Content length error when file upload
ob_end_clean();// to clear POST Content length error when file upload
/**
 *	SampleRegistrationsController
 *	@author Thura Moe
 *
 **/

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');

# Imports the Google Cloud client library
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\Exception\GoogleException;

define('PAGE', 'SampleRegistrations');
define('MENUID', 2);
define('PHASE', 2);//phase is menu id

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 * @Design By Nu Nu Lwin
 * @Coding By Thura Moe
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class SampleRegistrationsController extends AppController
{
    var $layout = 'samplecheck'; 
    public $components = array('Session', 'Flash');
    public $uses = array('Sample', 'SampleAccAttachment', 'SampleAccRequest','User', 'Layer', 'BrmLogistic');
    public $helpers = array('Html', 'Form');
       
    public function beforeFilter()
    {   
        parent::checkSampleUrlSession();#checkurlsession
    }
    
    /*
        Allow user_level_id
        admin = 1, accounting => 4,3,2
    */
    public function index()
    {
        $this->layout = 'samplecheck';
        $Common = new CommonController();
        $page   = $this->request->params['controller'];
        $data = [];
        $buttons = [];
        $show_action_column = false;
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        
        # check user permission
        $permissions = $this->Session->read('PERMISSIONS');
        unset($permissions['index']);

        $data = array();
        $data['role_id'] = $user_level;
        $data['period'] = $period;
        $data['layer_code'] = $layer_code;
        $data['category'] = $category;
        #sample data registration flag
        $flag_list = array(
            'Save' => array('1','5'),
            'Request' => array('2')
        );
        $data['flag_list'] = $flag_list;
        $data['modelName'] = 'Sample';
        $data['page'] = 'SampleRegistrations';

        $find = $this->Sample->find('all', array(
            'conditions' => array(
                'Sample.flag <>' => 0,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category' => $category
            ),
            'order' => array('Sample.id')
        ));

        $status = $this->Sample->find('first',array(
            'conditions' => array(
                'Sample.flag <>' => 0,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category' => $category
            ),
        ));
        $status = (!empty($status['Sample']['flag'])) ? $status['Sample']['flag'] : 1;
        $buttons = $Common->getButtonLists($status,$layer_code,$permissions);
       
        $find = array_column($find, 'Sample');
        $get_id = array_column($find, 'id');//get sample data id
        $size = count($find);
        if (!empty($find)) {
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
            
            for ($i=0; $i<$size; $i++) {
                $sid = $i+1;
                $find[$i]['sid'] = $sid;
                $sample_id = $find[$i]['id'];
                
                $language = ($this->Session->read('Config.language') == 'eng') ? 'en' : 'jp';
                $name = $this->Layer->find('all', array(
                    'conditions' => array(
                        'Layer.layer_code' => $find[$i]['layer_code'],
                        'Layer.flag' => 1,
                    ),
                    'fields'=>array(
                        'Layer.name_'.$language.' AS layer_name'
                    )
                ))[0]['Layer']['layer_name'];
              
                # attach file
                if ($cnt_file > 0) {
                    for ($j=0; $j<$cnt_file; $j++) {
                        $attachment_id = $file[$j]['id'];
                        $file_sample_id = $file[$j]['sample_id'];
                        $file_name = $file[$j]['file_name'];
                        $file_type = $file[$j]['file_type'];
                        $file_size = $file[$j]['file_size'];
                        $url = $file[$j]['url'];
                        if ($sample_id == $file_sample_id) {
                            $find[$i]['file'][] = array(
                                'attachment_id' => $attachment_id,
                                'file_name' => $file_name,
                                'file_type' => $file_type,
                                'file_size' => $file_size,
                                'url' => $url
                            );
                        }
                    }

                    /* if some of the sample_data has no attachment then add file index to $find array */
                    if (!array_key_exists('file', $find[$i])) {
                        $find[$i]['file'] = [];
                    }
                } else {
                    $find[$i]['file'] = [];
                }
                $find[$i]['layer_name'] = !empty($name) ? $name : [];
               
                # if some of the data have flag 1, then show Edit/Delete column
                $flag = $find[$i]['flag'];
                if ($flag == 1) {
                    $show_action_column = true;
                }

            }
        }
        $data = $find;
        $no_data = '';
        if(empty($data)) {
            $no_data = parent::getErrorMsg("SE001");
            unset($buttons["request"]);
        }
        $this->set(compact('buttons', 'user_level', 'page', 'get_mails', 'show_action_column', 'layer_code', 'layer_name', 'period', 'data', 'no_data'));
        $this->render("index");

    }

    /**
     * get total row count for selected period and business area
    **/
    public function __rowCount($period, $layer_code, $category)
    {
        $getCount = $this->Sample->find('count', array(
            'conditions' => array(
                'Sample.flag <>' => 0,
                'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                'Sample.layer_code' => $layer_code,
                'Sample.category' => $category
            )
        ));
        return $getCount;
    }

    /**
     *	Allow user_level_id
     *	Admin = 1, sales => 4
     **/
    public function copyingData()
    {
        // $this->autoRender = false;
        // $this->request->allowMethod('ajax');
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $response = [];
        $canCopy = true;
        if ($this->request->is('post')) {
            $admin_id = $this->Session->read('LOGIN_ID');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $id = $this->request->data['row_id'];
            
            # check user permission to access this method
            // if ($user_level != 1 && $user_level != 4) {
            //     $msg = parent::getErrorMsg("SE016", [__("register")]);
            //     $this->Flash->set($msg, array('key'=>'sampleFail'));
            //     $response = array(
            //         'status' => 'error'
            //     );
            //     $canCopy = false;
            // }

            # check some of the data is already request or not
            # if requested, then not allow to save new data anymore
            $check_request = $this->Sample->find('all', array(
                'conditions' => array(
                    'CAST(flag AS UNSIGNED) >=' => '2',
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category'  => $category
                )
            ));
            if (!empty($check_request)) {
                $msg = parent::getErrorMsg('SE019');
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $response = array(
                    'status' => 'error'
                );
                $canCopy = false;
            }

            /* check selected period and ba is reach max 6 row or not */
            $rowCount = $this->__rowCount($period, $layer_code, $category);
            if ($rowCount >= 6) {
                $msg = parent::getErrorMsg('SE003');
                $msg = parent::getErrorMsg('SE006');
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $response = array(
                    'status' => 'error'
                );
                $canCopy = false;
            }

            if ($canCopy == true) {
                $db = $this->Sample->getDataSource();
                try {
                    $db->begin();
                    $get_data = $this->Sample->find('first', array(
                        'conditions' => array(
                            'id' => $id,
                            'flag' => 1
                        ),
                        'fields' => array(
                            'period', 'layer_code', 'category', 'incharge_name', 'project_title',
                            'posting_date', 'index_no', 'account_item',
                            'destination_code', 'destination_name', 'money_amt',
                            'request_docu','submission_deadline_date', 'remark', 'complete_date'
                        )
                    ));
                    if (!empty($get_data)) {
                        $copy = array(
                            'period' => $get_data['Sample']['period'],
                            'layer_code' => $get_data['Sample']['layer_code'],
                            'category' => $get_data['Sample']['category'],
                            'incharge_name' => $get_data['Sample']['incharge_name'],
                            'project_title' => $get_data['Sample']['project_title'],
                            'posting_date' => $get_data['Sample']['posting_date'],
                            'index_no' => $get_data['Sample']['index_no'],
                            'account_item' => $get_data['Sample']['account_item'],
                            'destination_code' => $get_data['Sample']['destination_code'],
                            'destination_name' => $get_data['Sample']['destination_name'],
                            'money_amt' => $get_data['Sample']['money_amt'],
                            'request_docu' => $get_data['Sample']['request_docu'],
                            'submission_deadline_date' => $get_data['Sample']['submission_deadline_date'],
                            'remark' => $get_data['Sample']['remark'],
                            'complete_date' => $get_data['Sample']['complete_date'],
                            'flag' => 1,
                            'created_by' => $admin_id,
                            'updated_by' => $admin_id,
                            'created_date' => date('Y-m-d h:i:s')
                        );
                        $this->Sample->create();
                        $status = $this->Sample->save($copy);
                        if ($status == true) {
                            $msg = parent::getSuccessMsg('SS025', __("データ"));
                            $this->Flash->set($msg, array('key'=>'sampleOK'));
                            $response = array(
                                'status' => 'success'
                            );
                        } else {
                            $msg = parent::getErrorMsg('SE003');
                            $this->Flash->set($msg, array('key'=>'sampleFail'));
                            $response = array(
                                'status' => 'error'
                            );
                        }
                    } else {
                        //data can't copy
                        $msg = parent::getErrorMsg('SE003');
                        $this->Flash->set($msg, array('key'=>'sampleFail'));
                        $response = array(
                            'status' => 'error'
                        );
                    }
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollback();
                    $response = array(
                        'status' => 'error'
                    );
                    $msg = parent::getErrorMsg('SE001');
                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                }
            }
        }
        echo json_encode($response);
    }

    /**
     *	Allow user_level_id
     *	Admin = 1, sales => 4
     **/
    public function fun_save()
    {
        $Common = new CommonController();  
        if ($this->request->is('post')) {
            $isValid = true;
            $login_id = $this->Session->read('LOGIN_ID');
            $login_user = $this->Session->read('LOGIN_USER');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $admin_id = $this->Session->read('LOGIN_ID');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $date = date('Y-m-d H:i:s');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $data = $this->request->data;
            // $to_email = $_POST['toEmail'];
            #mail request
            $to_email = $this->request->data('toEmail');
            $cc_email = $this->request->data('ccEmail');
            $bcc_email = $this->request->data('bccEmail');

            # check some of the data is already request or not
            # if approved, then not allow to save new data anymore
            $check_request = $this->Sample->find('all', array(
                'conditions' => array(
                    'CAST(flag AS UNSIGNED) >=' => '2',
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category'  => $category
                )
            ));
           
            if (!empty($check_request)) {
                $msg = parent::getErrorMsg('SE019');
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            } 
            /* check selected period and ba is reach max 6 row or not */
            $rowCount = $this->__rowCount($period, $layer_code, $category);
            if ($rowCount >= 6) {
                $msg = parent::getErrorMsg('SE003');
                $msg = parent::getErrorMsg('SE006');
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
           
            if (!empty($data)) {
                $incharge = trim($data['incharge_name']);
                $proj_title = trim($data['project_title']);
                $posting_date = trim($data['posting_date']);
                $index = trim($data['hid_index_no']);
                $acc_item = trim($data['account_item']);
                $dest_code = trim($data['destination_code']);
                $dest_name = trim($data['destination_name']);
                $amount = trim($data['money_amt']);
                $request_doc = trim($data['request_docu']);
                $submission_deadline_date = trim($data['submission_deadline_date']);
                $remark = trim($data['remark']);

                if ($incharge=='' || $proj_title=='' || $posting_date=='' || $index=='' || $acc_item=='' || $dest_code=='' || $dest_name=='' || $amount=='' || $request_doc=='' || $submission_deadline_date =='') {
                    $isValid = false;
                }

                /* append necessary data */
                $tmp_data['layer_code'] = trim($layer_code);
                $tmp_data['category'] = trim($category);
                $tmp_data['incharge_name'] = trim($data['incharge_name']);
                $tmp_data['project_title'] = trim($data['project_title']);
                $tmp_data['posting_date'] = trim($data['posting_date']);
                $tmp_data['index_no'] = trim($data['hid_index_no']);
                $tmp_data['account_item'] = trim($data['account_item']);
                $tmp_data['destination_code'] = trim($data['destination_code']);
                $tmp_data['destination_name'] = trim($data['destination_name']);
                $tmp_data['money_amt'] = trim($data['money_amt']);
                $tmp_data['request_docu'] = trim($data['request_docu']);
                $tmp_data['submission_deadline_date'] = trim($data['submission_deadline_date']);
                $tmp_data['remark'] = trim($data['remark']);
                $tmp_data['period'] = $period.'-01';
                $tmp_data['flag'] = 1;
                $tmp_data['created_by'] = $admin_id;
                $tmp_data['updated_by'] = $admin_id;
                $tmp_data['created_date'] = $date;
                if ($isValid == true) {
                    #save into samples
                    try {
                        $this->Sample->create();
                        
                        /* Save and email*/

                        // $mail_template 			= 'common';
                        // $mail['subject'] 		= $_POST['mailSubj'];
                        // $mail['template_title'] = $_POST['mailTitle'];
                        // $mail['template_body'] 	= $_POST['mailBody'];

                        $mail_template 			= 'common';
                        $mail['subject'] 		= $this->request->data('mailSubj');
                        $mail['template_title'] = '';
                        $mail['template_body'] 	= $this->request->data('mailBody');
                       
                        #send email to account tantoesha(level 3) and cc Kacho (level 3)
                        // $toEmail = parent::formatMailInput($to_email);
                        // $ccEmail="";
                        $toEmail = parent::formatMailInput($to_email);
                        $ccEmail = parent::formatMailInput($cc_email);
                        $bccEmail = parent::formatMailInput($bcc_email);
                        
                        $url = '/SampleRegistrations?'.'period='.$period.'&ba='.$layer_code.'&category='.$category;
                       #mail send or not
                       $mail_send = $this->request->data('mailSend');
                       
                        if($mail_send == 0){
                            $status = $this->Sample->save($tmp_data);
                            $msg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($msg, array('key'=>'sampleOK'));
                            $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                        }else{  
                            if (!empty($toEmail)) {
                                
                                $mail = parent::sendEmailP3($period, $login_user, $toEmail, $ccEmail,$bccEmail, $mail_template, $mail, $url);
                                if ($mail["error"]) {
                                    $error = 1;
                                    $msg = $mail["errormsg"];
                                                                        
                                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                                    $invalid_email = parent::getErrorMsg('SE042');
                                    $this->Flash->set($invalid_email, array('key'=>'sampleFail'));
                                } else {
                                    $status = $this->Sample->save($tmp_data);
                                    $msg = parent::getSuccessMsg('SS001');
                                    $this->Flash->set($msg, array('key'=>'sampleOK'));
                                    $msg = parent::getSuccessMsg("SS018");
                                    $this->Flash->set($msg, array('key'=>'sampleOK'));
                                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                                }
                            } else {
                                $msg = parent::getErrorMsg("SE059");
                                $error = 1;
                                CakeLog::write('debug', 'Incharge(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$login_user_name. '('.$login_user_name.') click `Approve Cancel` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            }
                        }
                    } catch (Exception $e) {
                        $msg = parent::getErrorMsg('SE001');
                        $this->Flash->set($msg, array('key'=>'sampleFail'));
                        CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    }
                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                } else {


                    /* validation fail */
                    $msg = parent::getErrorMsg('SE004');
                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                    CakeLog::write('debug', 'validation fail when saving data in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                }
            } else {
                $msg = parent::getErrorMsg('SE001');
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                CakeLog::write('debug', 'request data is empty in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
        }
    }

    /**
     *	Allow user_level_id
     *	Admin = 1, sales => 4
     *	For updated Id change in when data have
     **/
    public function saveAndEmailUpdateID()
    {
        $Common = new CommonController();
        
        if ($this->request->is('post')) {
            $isValid = true;
            $login_id = $this->Session->read('LOGIN_ID');
            $login_user = $this->Session->read('LOGIN_USER');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $admin_id = $this->Session->read('LOGIN_ID');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $date = date('Y-m-d H:i:s');
            $data = $this->request->data;

            $to_email = $_POST['toEmail'];
            
            # check user permission to access this method
            if ($user_level != 1 && $user_level != 4) {
                $msg = parent::getErrorMsg("SE016", [__("register")]);
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
            
            # check some of the data is already request or not
            # if approved, then not allow to save new data anymore
            $check_request = $this->Sample->find('all', array(
                'conditions' => array(
                    'CAST(flag AS UNSIGNED) >=' => '2',
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code
                    )
                ));
                
                if (!empty($check_request)) {
                    $msg = parent::getErrorMsg('SE019');
                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                }
                
                $rowCount = $this->__rowCount($period, $layer_code, $category);
                if ($rowCount >= 6) {
                    $msg = parent::getErrorMsg('SE003');
                    $msg = parent::getErrorMsg('SE006');
                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                }
                if (!empty($data)) {
                    /* append necessary data */
                    if ($isValid == true) {
                        #save into samples
                        try {
                            $db = $this->Sample->getDataSource();
                            $arr['created_by'] = $db->value($admin_id, 'string');
                            $arr['updated_by'] = $db->value($admin_id, 'string');
                            $arr['updated_date'] = $db->value(date("Y-m-d H:i:s"), 'string');
                            /* condition to update */
                            $condition['DATE_FORMAT(Sample.period, "%Y-%m")'] = $period;
                            $condition['layer_code'] = $layer_code;
                            $condition['flag'] = 1;
                            
                            $db->begin();
                            $this->Sample->updateAll(
                                $arr,
                                $condition
                            );
                            $status = $this->Sample->getAffectedRows();
                            /* Save and email*/
                            $mail_template 			= 'common';
                            $mail['subject'] 		= $_POST['mailSubj'];
                            $mail['template_title'] = $_POST['mailTitle'];
                            $mail['template_body'] 	= $_POST['mailBody'];
                            
                            #send email to account tantoesha(level 3) and cc Kacho (level 3)
                            $toEmail = parent::formatMailInput($to_email);
                            $ccEmail="";
                            $url = '/SampleRegistrations?'.'period='.$period.'&ba='.$layer_code.'&category='.$category;
                            
                            if (!empty($toEmail)) {
                                $mail = parent::sendEmail($layer_code, $layer_name, $period, $login_user_name, $toEmail, $ccEmail, $mail_template, $mail, $url);
                                
                                if ($mail["error"]) {
                                    $error = 1;
                                    $msg = $mail["errormsg"];
                                    
                                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                                    $invalid_email = parent::getErrorMsg('SE042');
                                    $this->Flash->set($invalid_email, array('key'=>'sampleFail'));
                                } else {
                                    $db->commit();
                                    
                                    $msg = parent::getSuccessMsg('SS001');
                                    $this->Flash->set($msg, array('key'=>'sampleOK'));
                                    $msg = parent::getSuccessMsg("SS018");
                                    $this->Flash->set($msg, array('key'=>'sampleOK'));
                                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                                }
                        } else {
                            $msg = parent::getErrorMsg("SE059");
                            $error = 1;
                            CakeLog::write('debug', 'Incharge(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$login_user_name. '('.$login_user_name.') click `Approve Cancel` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        }
                    } catch (Exception $e) {
                        $msg = parent::getErrorMsg('SE001');
                        $this->Flash->set($msg, array('key'=>'sampleFail'));
                        CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    }
                } else {


                    /* validation fail */
                    $msg = parent::getErrorMsg('SE004');
                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                    CakeLog::write('debug', 'validation fail when saving data in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                }
            } else {
                $msg = parent::getErrorMsg('SE001');
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                CakeLog::write('debug', 'request data is empty in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
        }
    }

    /**
     *	Allow user_level_id
     *	Admin = 1, sales => 4
     **/
    public function saveSampleData()
    {
        if ($this->request->is('post')) {
            $isValid = true;
            $login_id = $this->Session->read('LOGIN_ID');
            $login_user = $this->Session->read('LOGIN_USER');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $admin_id = $this->Session->read('LOGIN_ID');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $date = date('Y-m-d H:i:s');
            $data = $this->request->data;
            $data['category'] = $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            
            #get data action 
            $data_action = $data['data-action'];
            
            #get user id list that have access
            $Common = new CommonController();
            // $user_id_list = $Common->getAdminLevelID(PAGE, array($data_action), PHASE);#page, function array, phase
            
            // # check user permission to access this method
            // if (!in_array($user_level, $user_id_list)) {
            //     $msg = parent::getErrorMsg("SE016", [__("register")]);
            //     $this->Flash->set($msg, array('key'=>'sampleFail'));
            //     $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            // }

            # check some of the data is already request or not
            # if approved, then not allow to save new data anymore
            $check_request = $this->Sample->find('all', array(
                'conditions' => array(
                    'CAST(flag AS UNSIGNED) >=' => '2',
                    'DATE_FORMAT(Sample.period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category'  => $category
                )
            ));
            
            if (!empty($check_request)) {
                $msg = parent::getErrorMsg('SE019');
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }

            /* check selected period and ba is reach max 6 row or not */
            $rowCount = $this->__rowCount($period, $layer_code, $category);
            if ($rowCount >= 6) {
                $msg = parent::getErrorMsg('SE003');
                $msg = parent::getErrorMsg('SE006');
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
            
            if (!empty($data)) {
                $incharge = trim($data['incharge_name']);
                $proj_title = trim($data['project_title']);
                $posting_date = trim($data['posting_date']);
                $index = trim($data['hid_index_no']);
                $acc_item = trim($data['account_item']);
                $dest_code = trim($data['destination_code']);
                $dest_name = trim($data['destination_name']);
                $amount = trim($data['money_amt']);
                $request_doc = trim($data['request_docu']);
                $submission_deadline_date = trim($data['submission_deadline_date']);
                $remark = trim($data['remark']);
                $data['index_no'] = $index;

                if ($incharge=='' || $proj_title=='' || $posting_date=='' || $index=='' || $acc_item=='' || $dest_code=='' || $dest_name=='' || $amount=='' || $request_doc=='' || $submission_deadline_date =='') {
                    $isValid = false;
                }

                /* append necessary data */
                $data['period'] = $period.'-01';
                $data['flag'] = 1;
                $data['created_by'] = $admin_id;
                $data['updated_by'] = $admin_id;
                $data['created_date'] = $date;
                
                if ($isValid == true) {
                    #save into samples
                    try {
                        $this->Sample->create();
                        $status = $this->Sample->save($data);
                        if ($status == true) {
                            # write to access_log
                            $msg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($msg, array('key'=>'sampleOK'));
                            $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                        } else {
                            $msg = parent::getErrorMsg('SE003');
                            $this->Flash->set($msg, array('key'=>'sampleFail'));
                        }
                    } catch (Exception $e) {
                        $msg = parent::getErrorMsg('SE001');
                        $this->Flash->set($msg, array('key'=>'sampleFail'));
                        CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    }
                } else {
                    /* validation fail */
                    $msg = parent::getErrorMsg('SE004');
                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                    CakeLog::write('debug', 'validation fail when saving data in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                }
            } else {
                $msg = parent::getErrorMsg('SE001');
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                CakeLog::write('debug', 'request data is empty in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
        }
    }

    /**
     *	Allow user_level_id
     *	Admin = 1, sales => 4
     **/
    public function updateSampleData()
    {
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $admin_id = $this->Session->read('LOGIN_ID'); //get from login
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        if ($this->request->is('post')) {
            $incharge_name = trim($this->request->data['incharge_name']);
            $project_title = trim($this->request->data['project_title']);
            $posting_date = trim($this->request->data['posting_date']);
            $index_no = trim($this->request->data['hid_index_no']);
            $account_item = trim($this->request->data['account_item']);
            $destination_code = trim($this->request->data['destination_code']);
            $destination_name = trim($this->request->data['destination_name']);
            $money_amt = trim($this->request->data['money_amt']);
            $request_docu = trim($this->request->data['request_docu']);
            $submission_deadline_date = trim($this->request->data['submission_deadline_date']);
            $remark = trim($this->request->data['remark']);
            $edit_sample_id = trim($this->request->data['edit_sample_id']);
            
            /* field to update */
            $db = $this->Sample->getDataSource();
            $arr['incharge_name'] = $db->value($incharge_name, 'string');
            $arr['project_title'] = $db->value($project_title, 'string');
            $arr['posting_date'] = $db->value($posting_date, 'string');
            $arr['index_no'] = $db->value($index_no, 'string');
            $arr['account_item'] = $db->value($account_item, 'string');
            $arr['destination_code'] = $db->value($destination_code, 'string');
            $arr['destination_name'] = $db->value($destination_name, 'string');
            $arr['money_amt'] = $db->value($money_amt, 'string');
            $arr['request_docu'] = $db->value($request_docu, 'string');
            $arr['submission_deadline_date'] = $db->value($submission_deadline_date, 'string');
            $arr['remark'] = $db->value($remark, 'string');
            $arr['updated_by'] = $db->value($admin_id, 'string');
            $arr['updated_date'] = $db->value(date("Y-m-d H:i:s"), 'string');
            /* condition to update */
            $condition['id'] = $edit_sample_id;
            $condition['DATE_FORMAT(Sample.period, "%Y-%m")'] = $period;
            $condition['layer_code'] = $layer_code;
            $condition['category'] = $category;
            $condition['Sample.flag'] = 1;
            
            try {
                $db->begin();
                $this->Sample->updateAll(
                    $arr,
                    $condition
                );
                $isUpdate = $this->Sample->getAffectedRows();
                if ($isUpdate > 0) {
                    $db->commit();
                    $msg = parent::getSuccessMsg("SS002");
                    $this->Flash->set($msg, array('key'=>'sampleOK'));
                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                } else {
                    throw new Exception("update condition not match in database", 1);
                }
            } catch (Exception $e) {
                $db->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE003");
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
        }
    }

    /**
     *	Allow user_level_id
     *	Admin = 1, sales => 4
     **/
    public function deleteSampleData()
    {
        $this->layout = 'samplecheck';
        # tantoesha (role_id 4) can delete when sample data flag is 1
        # admin (role_id 1) can delete every flag except flag = 0
        $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
        $admin_id = $this->Session->read('LOGIN_ID'); //get form login id
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');

        if ($this->request->is('post')) {
            $del_sample_id = $this->request->data['del_sample_id'];

            // # check user permission to access this method
            // if ($user_level != 1 && $user_level != 4) {
            //     $msg = parent::getErrorMsg("SE016", [__("delete")]);
            //     $this->Flash->set($msg, array('key'=>'sampleFail'));
            //     $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            // }

            $attachment = $this->SampleAccAttachment->find('all', array(
                'conditions' => array(
                    'SampleAccAttachment.sample_id' => $del_sample_id,
                    'SampleAccAttachment.flag' => 1
                )
            ));
            $attachment = array_column($attachment, 'SampleAccAttachment');
            $attach_count = count($attachment);

            $sampleDB = $this->Sample->getDataSource();
            $attachDB = $this->SampleAccAttachment->getDataSource();
            try {
                $sampleDB->begin();
                $attachDB->begin();

                # delete samples
                # field and condition array to update
                $sample['Sample.flag'] = $sampleDB->value(0, 'string');
                $sample['Sample.updated_by'] = $sampleDB->value($admin_id, 'string');
                $sample['Sample.updated_date'] = $sampleDB->value(date('Y-m-d H:i:s'), 'string');
                $sample_cond['Sample.id'] = $del_sample_id;
                $sample_cond['DATE_FORMAT(Sample.period, "%Y-%m")'] = $period;
                $sample_cond['Sample.layer_code'] = $layer_code;
                $sample_cond['Sample.flag'] = 1;
                $sample_cond['Sample.category'] = $category;


                $this->Sample->updateAll(
                    $sample,
                    $sample_cond
                );
                $updateSample = $this->Sample->getAffectedRows();
                if ($updateSample < 1) {
                    throw new Exception("Fail to delete sample data in samples table", 1);
                }

                if ($attach_count > 0) {
                    # delete sample_acc_attachments
                    # field and condition array to update
                    $attach['SampleAccAttachment.flag'] = $attachDB->value(0, 'string');
                    $attach['SampleAccAttachment.updated_by'] = $attachDB->value($admin_id, 'string');
                    $attach['SampleAccAttachment.updated_date'] = $attachDB->value(date('Y-m-d H:i:s'), 'string');
                    $attach_cond['SampleAccAttachment.sample_id'] = $del_sample_id;
                    $attach_cond['SampleAccAttachment.flag'] = 1;
                    $this->SampleAccAttachment->updateAll(
                        $attach,
                        $attach_cond
                    );
                    $updateAttach = $this->SampleAccAttachment->getAffectedRows();
                    if ($updateAttach < 1) {
                        throw new Exception("Fail to delete account attachment files in sample_acc_attachments table", 1);
                    }
                }

                # if both tables update successfully,
                # then delete files in google cloud storage
                if (($updateSample > 0 && $attach_count < 1) || ($updateSample > 0 && $updateAttach > 0)) {
                    $cloud = parent::connect_to_google_cloud_storage();
                    $storage = $cloud[0];
                    $bucketName = $cloud[1];
                    $bucket = $storage->bucket($bucketName);
                    
                    # delete files in google cloud storage
                    for ($i=0; $i<$attach_count; $i++) {
                        $url = $attachment[$i]['url'];
                        $file_name = $attachment[$i]['file_name'];
                        $url .= $file_name;
                        $object = $bucket->object($url);
                        if ($object->exists()) {
                            $object->delete();
                        }
                    }
                }
                
                $sampleDB->commit();
                $attachDB->commit();
                $msg = parent::getSuccessMsg("SS003");
                $this->Flash->set($msg, array('key'=>'sampleOK'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            } catch (Exception $e) {
                $sampleDB->rollback();
                $attachDB->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE007");
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
        }
    }

    /**
     *	Allow user_level_id
     *	Admin = 1, sales => 3,2
     **/
    public function fun_request()
    {
        if ($this->request->is('post')) {
            $admin_id = $this->Session->read('LOGIN_ID');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $sub_deadline_date = "";
            # check user permission to access this method
            # $user_level != 1 && $user_level != 3 && $user_level != 2
            // $Common = new CommonController();
            // $allowed_list = array_unique(array_column(array_column($Common->getButtonList(PAGE, MENUID, 'Request'),'Permissions'), 'role_id'));
            // if (!in_array($user_level, array_values($allowed_list))) {
            //     $msg = parent::getErrorMsg("SE016", [__("依頼")]);
            //     $this->Flash->set($msg, array('key'=>'sampleFail'));
            //     $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            // }

            # if one data row of same period and layer_code has flag 3 or above
            # then, request cannot be anymore
            $is_approved = $this->Sample->find('all', array(
                'conditions' => array(
                    'DATE_FORMAT(period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category'  => $category,
                    'flag >' => 2
                )
            ));
            if (!empty($is_approved)) {
                $msg = parent::getErrorMsg("SE010");
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }

            # check registered data (flag 1) exists or not
            $is_registered = $this->Sample->find('all', array(
                'conditions' => array(
                    'DATE_FORMAT(period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category'  => $category,
                    'flag' => 1
                )
            ));

            # check registered data (flag 2) exists or not
            $is_registered_last = $this->Sample->find('all', array(
                'conditions' => array(
                    'DATE_FORMAT(period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category'  => $category,
                    'flag' => 2
                )
            ));
            if (!empty($is_registered)) {
                # prepare data to save into tbl_sample_acc_request
                $is_registered = array_column($is_registered, 'Sample');
                $cnt = count($is_registered);
                $today = date("Y-m-d");
                $acc_request = [];
                for ($i=0; $i<$cnt; $i++) {
                    $sample_id = $is_registered[$i]['id'];
                    $acc_request[] = array(
                        'sample_id' => $sample_id,
                        'request_date' => $today,
                        'flag' => 1,
                        'created_by' => $admin_id,
                        'updated_by' => $admin_id
                    );
                }


                # make request (change flag 1 to flag 2)
                $samDB = $this->Sample->getDataSource();
                $accReqDB = $this->SampleAccRequest->getDataSource();
                try {
                    /* field and condition to update in tbl_sample data */
                    $flag = $samDB->value(2, 'string');
                    $updated_by = $samDB->value($admin_id, 'string');
                    $updated_date = $samDB->value(date('Y-m-d H:i:s'), 'string');
                    $record['flag'] = $flag;
                    $record['updated_by'] = $updated_by;
                    $record['updated_date'] = $updated_date;
                    $condition['DATE_FORMAT(period, "%Y-%m")'] = $period;
                    $condition['layer_code'] = $layer_code;
                    $condition['category']  = $category;
                    $condition['flag'] = 1;

                    $samDB->begin();
                    $accReqDB->begin();

                    $this->Sample->updateAll(
                        $record,
                        $condition
                    );

                    /* save data into tbl_sample_acc_request table */
                    $this->SampleAccRequest->saveMany($acc_request);

                    # send email to Account Manger(level 4 or 1)
                    /* Find  user ID from tbl sample data */
                    $getCreatedID = $this->Sample->getlastEmailUser($layer_code, $period, $category);
                
                    /* Get last  account user  email ID*/
                    $GetLastSaveId =end($getCreatedID);

                    $get_last_id = $GetLastSaveId['samples']['created_by'];

                    #Added by pan, get all unique deadline date
                    $get_deadline_date = $this->Sample->find('all', array(
                        'fields'		=> 'DISTINCT(submission_deadline_date)',
                        'conditions' 	=> array(
                            'period' => $period."-01",
                            'layer_code' => $layer_code,
                            'category' => $category,
                            'NOT'=>array('flag '=>'0')
                        )
                    ));

                    #Added by pan, loop deadline date and prepare for deadline date show in mail content
                    if (count($get_deadline_date)>1) {
                        foreach ($get_deadline_date as $deadline_date) {
                            // $cnt++;
                            $sub_deadline_date .= '提出期限 :'.$deadline_date['Sample']['submission_deadline_date'].'<br>';
                        }
                    } else {
                        foreach ($get_deadline_date as $deadline_date) {
                            // $cnt++;
                            $sub_deadline_date .= '提出期限 :'.$deadline_date['Sample']['submission_deadline_date'];
                        }
                    }
                    #Remove comment parts in foreach if customer ask date display by comma separating.
                    // if(!empty($this->request->data('toEmail'))){
                    //     $toEmail = $this->request->data('toEmail');
                    // }

                     #mail request
                    $to_email = $this->request->data('toEmail');
                    $cc_email = $this->request->data('ccEmail');
                    $bcc_email = $this->request->data('bccEmail');

                    $mail_template 			= 'common';
                    $mail['subject'] 		= $this->request->data('mailSubj');
                    $mail['template_title'] = '';
                    $mail['template_body'] 	= $this->request->data('mailBody');
                   
                    #send email to account tantoesha(level 3) and cc Kacho (level 3)
                    // $toEmail = parent::formatMailInput($to_email);
                    // $ccEmail="";
                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);
                    
                    #mail send or not
                    $mail_send = $this->request->data('mailSend');
                    
                    if($mail_send == 0){
                        $samDB->commit();
                        $accReqDB->commit();
                        $msg = parent::getSuccessMsg("SS008", [__("依頼")]);
                        $this->Flash->set($msg, array('key'=>'sampleOK'));
                        $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                    }else{
                        if (!empty($toEmail)) {
                            // $toEmail 	= $toEmail.$searchToEmail[0]['tbl_user']['email'];
                            $ccEmail    ="";
                            // pr($toEmail);
                            // die();
                            
                            // $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                            $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code.'&category='.$category;
                            
                            // die('arrived');
                            if (!empty($toEmail)) {
                                $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                    
                                if ($sentMail["error"]) {
                                    $msg = $sentMail["errormsg"];
                                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                                    $invalid_email = parent::getErrorMsg('SE042');
                                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                                }else{
                                    $samDB->commit();
                                    $accReqDB->commit();
                                    $msg = parent::getSuccessMsg("SS008", [__("依頼")]);
                                    $this->Flash->set($msg, array('key'=>'sampleOK'));
                                    $msg = parent::getSuccessMsg("SS018");
                                    $this->Flash->set($msg, array('key'=>'sampleOK'));
                                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                                }
                            }else{
                                CakeLog::write('debug', ' Account incharge emails are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click `Request` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            }
                        } else {
                            CakeLog::write('debug', 'Account incharge informations are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click `Approve Cancel` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        }
                    }           
                    /* email Ending for request data*/
                    /* old commit code place when  email does not have but save data. */
                    
                    $msg = parent::getErrorMsg("SE059");
                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                } catch (Exception $e) {
                    $samDB->rollback();
                    $accReqDB->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE011", [__("依頼")]);
                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                }
            }

            # finished flag 2 and lastUpdate ID change in level 3
            /*elseif(!empty($is_registered_last)){

                $samDB = $this->Sample->getDataSource();


            } */
            else {
                $msg = parent::getErrorMsg("SE017", [__("依頼")]);
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
        }
    }

    public function fun_requestcancel()
    {
        if ($this->request->is('post')) {
            $admin_id = $this->Session->read('LOGIN_ID');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');

            $sub_deadline_date = "";

            # if one data row of same period and layer_code has flag 3 or above
            # then, request cannot be anymore
            $is_approved = $this->Sample->find('all', array(
                'conditions' => array(
                    'DATE_FORMAT(period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category'  => $category,
                    'Sample.flag >' => 2
                )
            ));
            if (!empty($is_approved)) {
                $msg = parent::getErrorMsg("SE010");
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }

            # check registered data (flag 1) exists or not
            $is_registered = $this->Sample->find('all', array(
                'conditions' => array(
                    'DATE_FORMAT(period, "%Y-%m")' => $period,
                    'layer_code' => $layer_code,
                    'category'  => $category,
                    'Sample.flag' => 2
                )
            ));
        
            if (!empty($is_registered)) {
                # prepare data to save into tbl_sample_acc_request
                $is_registered = array_column($is_registered, 'Sample');
                $cnt = count($is_registered);
                $today = date("Y-m-d");
                $acc_request = [];
                for ($i=0; $i<$cnt; $i++) {
                    $sample_id = $is_registered[$i]['id'];
                    $acc_request[] = array(
                        'sample_id' => $sample_id,
                        'request_date' => $today,
                        'flag' => 2,
                        'created_by' => $admin_id,
                        'updated_by' => $admin_id
                    );
                }

                #This is check for when level 7 upload file finished cannot be request cancle

                # check request data(from SampleRegistrations) is exists or not
                $approved_ch = $this->Sample->find('all', array(
                    'conditions' => array(
                        'Sample.flag' => 2,
                        'DATE_FORMAT(period, "%Y-%m")' => $period,
                        'layer_code' => $layer_code,
                        'category'  => $category,
                        'id' => $sample_id
                    )
                ));
                if (!empty($approved_ch)) {
                    # check file is upload for each record of business side(status = 2)
                    # if record is  upload file level 7, then can't request cancle
                    $tmp = array_column($approved_ch, 'Sample');
                    $get_id = array_column($tmp, 'id');

                    $isFileExists = $this->SampleAccAttachment->find('count', array(
                        'conditions' => array(
                            'SampleAccAttachment.flag' => 1,
                            'status' => 2,
                            'sample_id IN' => $get_id
                        ),
                        'group' => array('sample_id')
                    ));
                    if (!empty($isFileExists) || $isFileExists != 0) {
                        $msg = parent::getErrorMsg("SE083");
                        $this->Flash->set($msg, array('key'=>'sampleFail'));
                        $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                    }
                }

                # make request (change flag 1 to flag 2)
                $samDB = $this->Sample->getDataSource();
                $accReqDB = $this->SampleAccRequest->getDataSource();
                try {
                    /* field and condition to update in tbl_sample data */
                    $flag = $samDB->value(1, 'string');
                    $updated_by = $samDB->value($admin_id, 'string');
                    $updated_date = $samDB->value(date('Y-m-d H:i:s'), 'string');
                    $record['flag'] = $flag;
                    $record['updated_by'] = $updated_by;
                    $record['updated_date'] = $updated_date;
                    $condition['DATE_FORMAT(period, "%Y-%m")'] = $period;
                    $condition['layer_code'] = $layer_code;
                    $condition['category']  = $category;
                    $condition['flag'] = 2;

                    $samDB->begin();
                    $accReqDB->begin();

                    $this->Sample->updateAll(
                        $record,
                        $condition
                    );

                    /* save data into tbl_sample_acc_request table */
                    $this->SampleAccRequest->saveMany($acc_request);

                    # send email to Account Manger(level 4 or 1)
                    /* Find  user ID from tbl sample data */
                    $getCreatedID = $this->Sample->getlastEmailUser($layer_code, $period, $category);
                
                    /* Get last  account user  email ID*/
                    $GetLastSaveId =end($getCreatedID);

                    $get_last_id = $GetLastSaveId['samples']['created_by'];

                    #Added by pan, get all unique deadline date
                    $get_deadline_date = $this->Sample->find('all', array(
                        'fields'		=> 'DISTINCT(submission_deadline_date)',
                        'conditions' 	=> array(
                            'period' => $period."-01",
                            'layer_code' => $layer_code,
                            'category'  => $category,
                            'NOT'=>array('flag '=>'0')
                        )
                    ));

                    #Added by pan, loop deadline date and prepare for deadline date show in mail content
                    if (count($get_deadline_date)>1) {
                        foreach ($get_deadline_date as $deadline_date) {
                            // $cnt++;
                            $sub_deadline_date .= '提出期限 :'.$deadline_date['Sample']['submission_deadline_date'].'<br>';
                        }
                    } else {
                        foreach ($get_deadline_date as $deadline_date) {
                            // $cnt++;
                            $sub_deadline_date .= '提出期限 :'.$deadline_date['Sample']['submission_deadline_date'];
                        }
                    }
                    #Remove comment parts in foreach if customer ask date display by comma separating.

                    /*  prepare to type to interger*/
                    // $get_user_email =intval($get_last_id);
                    
                    #GET Last save accountant user email
                    // $searchToEmail = $this->User->search_acc_Request_email($get_user_email);
                    
                    // #mail common temple
                    // $mail_template 			= 'common';
                    // #Mail contents
                
                    // $mail['subject']	 	= '【サンプルチェック】'.$layer_name.'サンプルデータ依頼キャンセル通知';
                    // $mail['template_title'] = '';
                    // $mail['template_body'] 	= $layer_name.'のデータの依頼をキャンセルしました。<br/>再度担当者は確認の上、サンプル作成をお願い致します。<br/>';

                    $to_email = $this->request->data('toEmail');
                    $cc_email = $this->request->data('ccEmail');
                    $bcc_email = $this->request->data('bccEmail');

                    $mail_template 			= 'common';
                    $mail['subject'] 		= $this->request->data('mailSubj');
                    $mail['template_title'] = '';
                    $mail['template_body'] 	= $this->request->data('mailBody');
                   
                    #send email to account tantoesha(level 3) and cc Kacho (level 3)
                    // $toEmail = parent::formatMailInput($to_email);
                    // $ccEmail="";
                    $toEmail = parent::formatMailInput($to_email);
                    $ccEmail = parent::formatMailInput($cc_email);
                    $bccEmail = parent::formatMailInput($bcc_email);

                    #mail send or not
                    $mail_send = $this->request->data('mailSend');

                    if($mail_send == 0){
                        $samDB->commit();
                        $accReqDB->commit();
                        $msg = parent::getSuccessMsg("SS024", [__("依頼")]);
                        $this->Flash->set($msg, array('key'=>'sampleOK'));
                        $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                    }else{
                        if (!empty($toEmail)) {
                            // $toEmail 	= $searchToEmail[0]['tbl_user']['email'];
                            // $ccEmail    ="";
    
                            // $url = '/SampleDataEntry?period='.$period.'&ba='.$layer_code.'&layer_name='.urlencode($layer_name);
                            $url = '/SampleRegistrations?period='.$period.'&ba='.$layer_code.'&category='.$category;
                            
                            if (!empty($toEmail)) {
                                $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                    
                                if ($sentMail["error"]) {
                                    $msg = $sentMail["errormsg"];
                                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                                    $invalid_email = parent::getErrorMsg('SE042');
                                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                                } else {
                                    #when email have user save
                                    $samDB->commit();
                                    $accReqDB->commit();
                                    $msg = parent::getSuccessMsg("SS024", [__("依頼")]);
                                    $this->Flash->set($msg, array('key'=>'sampleOK'));
                                    $msg = parent::getSuccessMsg("SS018");
                                    $this->Flash->set($msg, array('key'=>'sampleOK'));
                                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                                }
                            } else {
                                CakeLog::write('debug', ' Account incharge emails are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click `Request` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            }
                        } else {
                            CakeLog::write('debug', 'Account incharge informations are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click `Approve Cancel` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        }
                    }
                    /* email Ending for request data*/
                    /* old commit code place when  email does not have but save data. */
                    
                    $msg = parent::getErrorMsg("SE059");
                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                } catch (Exception $e) {
                    $samDB->rollback();
                    $accReqDB->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE011", [__("依頼")]);
                    $this->Flash->set($msg, array('key'=>'sampleFail'));
                    $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
                }
            } else {
                $msg = parent::getErrorMsg("SE017", [__("依頼")]);
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
        }
    }

    /**
     * File upload
     */
    public function uploadAccountFile()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $error = false;
        $response = [];
        $save_file_info = [];
        if ($this->request->is('post')) {
            if (isset($this->request->data['File'])) {
                $action = $this->request->data['action'];//to decide save or update query
                $sample_id = $this->request->data['sample_data_id'];
                $sid = $this->request->data['sid'];
                $file = $this->request->data['File']['upload_file'];
                $count = count($file);
                $ext_arr = ['exe'];//not allow extension
                
                # check data is already approved(flag = 4) or not before upload
                # if its approved, then can't upload
                $isRequested = $this->Sample->find('all', array(
                    'conditions' => array(
                        'id' => $sample_id,
                        'CAST(flag AS UNSIGNED) >=' => 4
                        )
                    ));
                   
                if (!empty($isRequested)) {
                    //can't upload files
                    $response = array(
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
                            $admin_id = $this->Session->read('LOGIN_ID'); //get login id
                            $date = date('Y-m-d H:i:s');
                            $save_file_info['sample_id'] = $sample_id;
                            $save_file_info['status'] = 1;//for accounting
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
                                $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
                                $layer_code = $this->Session->read('SESSION_LAYER_CODE');
                                $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
                                $category = $this->Session->read('SAMPLECHECK_CATEGORY');
                                $year = date('Y', strtotime($period));
                                $month = date('m', strtotime($period));
                                $type = '経理';
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
                                        $cond['status'] = 1;//for accounting
                                        $cond['url'] = $uploadFolderPath;
                                        $cond['file_name'] = $fileName;
                                        $cond['SampleAccAttachment.flag'] = 1;
                                        $this->SampleAccAttachment->updateAll(
                                            $update_arr,
                                            $cond
                                        );
                                    }
                                    $isUpload = $this->__upload_object_to_cloud($fileName, $filePath, $uploadFolderPath);
                                    $attachDB->commit();
                                    # to show success message when form reload
                                    $msg = parent::getSuccessMsg("SS007");
                                    $response = array(
                                        'file_name' => array(
                                            'name' => $fileName,
                                            'url' => $uploadFolderPath,
                                            
                                        ),
                                        'msg' => $msg
                                    );
                                    $this->Flash->set($msg, array('key'=>'sampleOK'));
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
            echo json_encode($response);exit();
        }
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
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
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

            $table_id = $this->request->data['attachment_id'];
            $url = $this->request->data['download_url'];//file_path
            $file_name = $this->request->data['download_file'];//file_name
            $url .= $file_name;// BA/2019/02/5/経理/220px-Vue.js_Logo_2.svg.png

            # check data is already approved(flag=4) or not before delete
            # if its approved, then can't delete
            $isRequested = $this->SampleAccAttachment->find('all', array(
                'conditions' => array(
                    'SampleAccAttachment.id' => $table_id,
                    'SampleAccAttachment.flag' => 1,
                    'CAST(samples.flag AS UNSIGNED) >=' => 4
                ),
                'joins' => array(
                    array(
                        'table' => 'samples',
                        'alias' => 'samples',
                        'type' => 'left',
                        'conditions' => 'SampleAccAttachment.sample_id = samples.id'
                    )
                )
            ));
            if (!empty($isRequested)) {
                //can't delete files
                $msg = parent::getErrorMsg("SE028");
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
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
                $condition['SampleAccAttachment.status'] = 1;//for account files
                $this->SampleAccAttachment->updateAll(
                    $record,
                    $condition
                );
                $db->commit();
                $msg = parent::getSuccessMsg('SS003');
                $this->Flash->set($msg, array('key'=>'sampleOK'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            } catch (GoogleException $e) {
                $db->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE009");
                $this->Flash->set($msg, array('key'=>'sampleFail'));
                $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'SampleRegistrations', 'action'=>'index'));
        }
    }

    public function getBaName($layer_code)
    {
        $getBaName = $this->Layer->find('first', array(
            'fields' => array(
                'Layer.name_jp'
            ),
            'conditions' => array(
                'Layer.flag' => 1,
                'Layer.layer_code' => $layer_code,
                'Layer.to_date >= ' => date('Y-m-d'),
                'Layer.type_order' => 3
            )
        ));
        return $getBaName['Layer']['name_jp'];
    }

    public function getMailContent(){
        parent::checkAjaxRequest($this);
        if ($this->request->is('POST')) {
            if ($this->Session->read('Config.language') == 'eng') {
                $language = 'eng';
            } else {
                $language = 'jp';
            }
            $Common = new CommonController();
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            // $page = 'SampleRegistrations';
            $function = $this->request->data('data_action');

            $get_mails = $Common->getMailList($layer_code, PAGE, $function, $language, $layer_name);
            // $get_mails['role_id'] = array_keys($get_mails['to']);
            // $get_mails['mails'] = array_values($get_mails['to']);
            // pr($get_mails);
            // die();
            echo json_encode($get_mails);
            
        }
    }

    public function getFlag($layer_code, $period){
        $find = $this->Sample->find('first', array(
            'fields' => array('flag'),
            'conditions' => array(
                'layer_code' => $layer_code,
                'period' => $period.'-01',
                'flag >' => 0
            )
        ));

        return $find['Sample']['flag'];
    }
    public function getLogistics(){
        #only allow ajax request
        $this->checkAjaxRequest($this);
        if ($this->request->is('post')) {
            $searchValue     = $this->request->data['searchValue'];
            $layer_code     = $this->request->data['layer_code'];
            $pattern = '/^'.$searchValue.'/';
            $condition_arr = array(
                
                'OR' => array(
                    'BrmLogistic.index_no LIKE' => '%'.$searchValue.'%',
                    'BrmLogistic.index_name LIKE' => '%'.$searchValue.'%',
                ),
                'BrmLogistic.layer_code' => $layer_code,
                'BrmLogistic.flag' => 1
            );
        
            $logi_data = $this->BrmLogistic->find('all', array(
                'fields' => 'DISTINCT (BrmLogistic.index_no),  CONCAT(BrmLogistic.index_no, IF(BrmLogistic.index_no = "", "", "/"), BrmLogistic.index_name) as name',
                'conditions' => $condition_arr
            ));
           
            $result = array();
            foreach($logi_data as $key=>$value){
                $result[$key] = $value[0]['name'];
            }
            
            if (!empty($result)) {
                return json_encode($result);
            }else{
                return json_encode($result);
            }
        }
    }
}
