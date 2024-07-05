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

App::uses('Controller', 'Controller');
App::import('Controller', 'Common');

/**
 * SampleSelections Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 * @Design By Nu Nu Lwin
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class SampleSelectionsController extends AppController
{
    public $uses = array('Users','Message','Sample','SampleChecklist','SampleTestResult','SampleAccAttachment', 'Layer');
    public $components = array('Session','Flash');
    public $helpers = array('Html', 'Form','Csv');

    /**
     * Check Session before render page
     *
     */
    public function beforeFilter()
    {
        parent::checkUserStatus();
        // parent::CheckSession();

        $Common = New CommonController();

        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);

        $show_menu_lists = $Common->getMenuByRole($role_id, $pagename);
        $this->Session->write('MENULISTS', $show_menu_lists);
        
        // if((!in_array($layer_code, array_keys($permissions['index']['layers']))) || ($layer_code=="" && $permissions['index']['limit']>0)) {
        //     $errorMsg = parent::getErrorMsg('SE065');
        //     $this->Flash->set($errorMsg, array("key"=>"Error"));
        //     $this->render("index");
        // }
    }
    /**
     *
     * index method
   * Aye Zar Ni Kyaw
     *
     */
    public function index()
    {
        $this->layout = 'samplecheck';

        $noti_message = "";
        $show_msg = "";

        $permissions = $this->Session->read('PERMISSIONS');
        $index_permt = $permissions['index'];
        $layer_list = $index_permt['layers'];
        
        //added by Khin Hnin Myo (retrieve Message from Message table)
        $noti_message = $this->Message->find('all', array(
                                            'fields'=>array('message'),
                                            'order' => array('Message.id DESC'),
                                            'limit' =>1));
        if (!empty($noti_message)) {
            $show_msg = $noti_message[0]['Message']['message'];
        }
        $layer_list = [];
        $this->set('show_msg', $show_msg);
        $this->set('layer_name', $layer_list);
        $this->render('index');
    }

    public function getBa() {
        parent::checkAjaxRequest($this);
        $Common = New CommonController();

        $period = $this->request->data['period'];
        $layer_code = $this->request->data['layer_code'];

        $login_id = $this->Session->read('LOGIN_ID');#ato increment id
        $admin_lvl = $this->Session->read('role_id');
        $get_login_id = $this->Session->read('LOGINID');#login id
        $period = date("Y-m-d", strtotime($period));
        $lan['language'] = $this->Session->read('Config.language');
        //$filterBaList = $Common->filterBAList($login_id, $get_login_id, $period, $this);
        $filterBaList = $Common->filterBAList($login_id, $get_login_id, $period, $lan['language'],$this->name);
        
        $filterBaLists = (!empty($filterBaList)) ? $filterBaList + $lan : false;
        echo json_encode($filterBaLists);exit();
    }
    /**
     *
     * add method
   * Aye Zar Ni Kyaw
     *
     */
    public function add()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        
        $period = $this->request->data['period'];
        $layer_code = $this->request->data['layer_code'];
        $category = $this->request->data['category'];
        
        $login_id = $this->Session->read('LOGIN_ID');#auto increment id
        $get_login_id = $this->Session->read('LOGINID');#login id
        $language = $this->Session->read('Config.language');
        $Common = New CommonController();
        $filterBaList = $Common->filterBAList($login_id, $get_login_id, date("Y-m-d", strtotime($period)), $language,$this->request->params['controller']);
        $list = $filterBaList;
        $this->Session->write('BALIST', $list);
        $layer_name = $list[$layer_code];
        /*$layer_name = $this->Layer->find(
            'all',
            array('conditions' => array(
            'code' => $layer_code,
            'flag' => 1,
            'to_date >= ' => date('Y-m-d'),
            'type_order' => 3//ba layer
        ),
            'fields'  => 'name_jp')
        );
        $this->Session->write('BALIST', $list);
        if (!empty($layer_name[0]['Layer']['name_jp'])) {
            $layer_name = $layer_name[0]['Layer']['name_jp'];
        } else {
            $layer_name ="";
        }*/
    
        $this->Session->write('SAMPLECHECK_PERIOD_DATE', $period);
        $this->Session->write('SESSION_LAYER_CODE', $layer_code);
        $this->Session->write('SAMPLECHECK_BA_NAME', $layer_name);
        $this->Session->write('SAMPLECHECK_CATEGORY', $category);

        $response = array(

            'period' => $period,
            'layer_code' 	  => $layer_code,
            'layer_name' 	  => $layer_name,
            'category'        => $category,
            
        );
        echo json_encode($response);
    }
    /**
     * Export CSV file action for admin sale list
     * @author Khin Nyein Chan Thu
     *
     */
    public function download()
    {
        $this->layout = "";
        $errorMsg = "";
        $successMsg = "";

        $this->layout = 'samplecheck';
        $successMsg = '';
        $errorMsg = '';
        $not_exist_data = '';
        $app_cancel_btn = true;
        $approve_btn = false;
        $save_btn_show = true;
        $enable_flag = '';
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
        $category = $this->Session->read('SAMPLECHECK_CATEGORY');
    
        $role_id = $this->Session->read('role_id');
        $result = $this->Sample->search_testResult_forall_ba($period);

        $resultBA = array_column($result, 'tbl_A');
        $get_ba = array_column($resultBA, 'name_jp');

        //get sample_id to find file in acc_attach db
        $result = array_column($result, 'samples');
        $get_id = array_column($result, 'id');
        $size = count($result);
    
        if (!empty($result)) {
            $flag = $result[0]['flag'];//for define disable or enable all input type
            if ($flag < 4) {
                $enable_flag = 'ReadOnly';
            }
            
            /* find sample id in sample_checklists is exist or not */
            $get_check_sample_id = $this->SampleChecklist->find('all', array(
                    'conditions' => array(
                            'SampleChecklist.sample_id IN' => $get_id
                    )
            ));
            
            if (count($get_check_sample_id) >0) {
                $app_cancel_btn = false; // for hide approve cancel button when sample id is in sample_checklists
                $save_btn_show = false;
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
                $approve_btn = true;
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
                $result[$i]['name_jp']=$get_ba[$i];
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
        $fill_data = $this->Sample->search_FillResult_forall_ba($period);
        foreach($fill_data as $key=>$value){
            $fill_data[$key]['question'] = $this->Sample->getQuestions($value['samples']['category']);
        }
        //$questions = $this->Sample->getQuestions($category);
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
        
        $checkListshow =$this->SampleChecklist->GetChecklistDataForallBA($period);

        $get_id = [];
        $count = count($checkListshow);
        $check_list_tab1 = array();
        for ($i =0 ; $i < $count; $i++) {
            $get_id[] = $checkListshow[$i]['tr']['sample_id'];
            if ($checkListshow[$i]['ch']['flag'] != '') {
                array_push($check_list_tab1, $checkListshow[$i]['ch']);
            }
        }

        if (!empty($get_id)) {
            $checkListshow =  $this->__addFileListIntoArray($get_id, $checkListshow);
        }

        $this->set('app_cancel_btn', $app_cancel_btn);
        $this->set('approve_btn', $approve_btn);
        $this->set('save_btn_show', $save_btn_show);
        $this->set('data', $result);
        $this->set('not_exist_data', $not_exist_data);
        $this->set('fill_data', $fill_data);
        $this->set('questions', $questions);
        $this->set('not_exist_data', $not_exist_data);
        $this->set('layer_code', $layer_code);
        $this->set('layer_name', $layer_name);
        $this->set('period', $period);
        $this->set('successMsg', $successMsg);
        $this->set('errorMsg', $errorMsg);
        $this->set('role_id', $role_id);
        $this->set('enable_flag', $enable_flag);
        $this->set('checkListshow', $checkListshow);
        
        $this->layout = null;
        $this->autoLayout = false;
        $this->render('report_list_export');
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

    public function __addFileListIntoArray($get_id, $checkListshow)
    {
        /* get attachment */
        $file = $this->SampleAccAttachment->find('all', array(
            'conditions' => array(
                'status' => 1,
                'SampleAccAttachment.flag' => 1,
                'sample_id IN' => $get_id
            )
        ));

        $file = array_column($file, 'SampleAccAttachment');
        $cnt_file = count($file);

        /*  right file show down form result*/

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

        $count = count($checkListshow);
        for ($i=0; $i<$count; $i++) {
            $sample_id = $checkListshow[$i]['tr']['sample_id'];
            
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
}
