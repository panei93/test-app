<?php
App::uses('Controller', 'Controller');
App::import('Controller', 'Common');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 * @author Nu Nu Lwin
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class SapAddCommentsController extends AppController
{
    public $uses = array('Sap','SapBusiInchargeComment','SapBusiAdminComment','SapBusiManagerApprove','SapAccInchargeComment','Layer','User');
    public $components = array('Session','Paginator','PhpExcel.PhpExcel');
    
    public function beforeFilter()
    {
        parent::checkSapUrlSession();        
        
    }
    public function index($errmessage = null)
    {
        $this->layout = 'retentionclaimdebt';
        $errorMsg = "";
        $successMsg = "";
        $allChk = false;
        
        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');

        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');

        $permissions = $this->Session->read('PERMISSIONS');
        unset($permissions['index']);
        foreach ($permissions as $action => $permission) {
            if((($layer_code == '' && $permission['limit']==0) || in_array($layer_code, array_keys($permission['layers'])))) {
                $checkButtonType[$action] = true;
            }
        }
        $Common = new CommonController();
        $flag_list  = Setting::ADDCMT_FLAG;

        $data['role_id'] = $role_id;
        $data['period']         = $period;
        $data['layer_code']     = $layer_code;
        $data['page']           = $this->request->params['controller'];
        $data['permission']     = $permission;
        $data['flag_list']      = $flag_list;
        $data['modelName']      = 'Sap';
        
        if ($period != null || $period != '') {
            $condi = array(); //get base_date and deadLine_date with condition
            $condition = array();
            $condition['Sap.flag != '] = '0';
            $condition['Sap.flag > '] = '1';
            $condition["date_format(Sap.period,'%Y-%m')"] = $period;

            if ($layer_code != null || $layer_code != '') {
                $condition['Sap.layer_code'] = $layer_code;
                $condi["Sap.layer_code"] = $layer_code;
            }

            try {
                $this->paginate = array(
                'limit'=> Paging::TABLE_PAGING,
                'conditions' => $condition,
                'joins' => array(
                    array(
                        'alias' => 'SapBusiInchargeComment',
                        'table' => 'sap_busi_incharge_comments',
                        'type' => 'Left',
                        'conditions' => 'Sap.id = SapBusiInchargeComment.sap_id AND SapBusiInchargeComment.flag <> 0'
                    ),
                    array(
                        'alias' => 'SapBusiAdminComment',
                        'table' => 'sap_busi_admin_comments',
                        'type' => 'Left',
                        'conditions' => 'Sap.id = SapBusiAdminComment.sap_id AND SapBusiAdminComment.flag <> 0'
                    ),
                    array(
                        'alias' => 'SapAccInchargeComment',
                        'table' => 'sap_acc_incharge_comments',
                        'type' => 'Left',
                        'conditions' => 'Sap.id = SapAccInchargeComment.sap_id AND SapAccInchargeComment.flag <> 0'
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
                    'Sap.currency',
                    'Sap.layer_code',
                    'Sap.receipt_shipment_date',
                    "DATE_FORMAT(Sap.schedule_date,'%Y-%m-%d') AS sap_schedule_date",
                    'Sap.numbers_day',
                    'SUM(Sap.jp_amount) AS jp_amount',
                    'Sap.maturity_date',
                    'Sap.line_item_text',
                    'Sap.sale_representative',
                    'Sap.flag',
                    'Sap.preview_comment',
                    'SapBusiInchargeComment.reason',
                    'SapBusiInchargeComment.settlement_date',
                    'SapBusiInchargeComment.remark',
                    'SapBusiInchargeComment.sap_id',
                    'SapBusiAdminComment.comment',
                    'SapBusiAdminComment.sap_id',
                    'SapAccInchargeComment.comment',
                    'SapAccInchargeComment.sap_id'
                ),
                'group' => array(
                    'Sap.layer_code',
                    'Sap.account_code',
                    'Sap.destination_code',
                    'Sap.logistic_index_no',
                    'Sap.posting_date',
                    'Sap.recorded_date',
                    'Sap.receipt_shipment_date',
                    'Sap.schedule_date',
                    'Sap.period',
                    'Sap.currency'
                ),
                'order' => array(      
                    'Sap.id' => 'ASC',
                    'Sap.layer_code'=>'ASC',
                    'Sap.account_code' => 'ASC',
                    'Sap.destination_code' => 'ASC',
                    'Sap.logistic_index_no' => 'ASC',
                    'Sap.posting_date' => 'ASC',
                    'Sap.recorded_date' => 'ASC',
                    'Sap.receipt_shipment_date' => 'ASC',
                    'Sap.schedule_date' => 'ASC'
                ));

                $page = $this->Paginator->paginate('Sap');
                
                $count = $this->params['paging']['Sap']['count'];
                $pageno = $this->params['paging']['Sap']['page'];
                $this->Session->write('Page.pageCount', $pageno);
                if ($count != 0) {
                    $this->set('succCount', parent::getSuccessMsg('SS004', $count));
                } else {
                    $this->set('errCount', parent::getErrorMsg('SE001'));
                }
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
            }
           
            $fApprove = 0;
            $CancelApprove = 0;
            $RejectApprove = 0;
            $sid_array_approve = array();
            $sid_array_cancel = array();
            
            $sid_array_reject = array();
            #select all data but not flag != 0,1 and layer_code
            $ForApprove1 = $this->Sap->find('all', array(
                'conditions' => array(
                    "date_format(Sap.period,'%Y-%m')" => $period,
                    "Sap.layer_code" => $layer_code,
                    "flag NOT IN" => array('0', '1')
                )
            ));            
            #check selected all data, if(flag == 4)?
            if (!empty($ForApprove1)) {
                foreach ($ForApprove1 as $value) {
                    $toApproveFlag = $value['Sap']['flag'];
                    $sid = $value['Sap']['id'];
                    if ($toApproveFlag == '4') {
                        $sid_array_approve[] = $sid;
                        $fApprove++;
                        $sid_array_reject[] = $sid;
                        $RejectApprove++;
                    } elseif ($toApproveFlag == '5') {
                        $sid_array_cancel[] = $sid;
                        $CancelApprove++;
                    } else {
                        break;
                    }
                }
            }
            
            $countApprove1 = count($ForApprove1);
            $sidArrApproveCount = count($sid_array_approve);
            $sidArrCancelCount = count($sid_array_cancel);
            $sidArrRejectCount = count($sid_array_reject);

            if ($countApprove1 == $sidArrApproveCount || $countApprove1 == $sidArrRejectCount) {
                $this->Session->write('SID_ARRAY', $sid_array_approve);
                $this->Session->write('SID_ARRAY_REJECT', $sid_array_reject);
            } elseif ($countApprove1 == $sidArrCancelCount) {
                $this->Session->write('SID_ARRAY_CANCEL', $sid_array_cancel);
            }

            $BAName = $this->Session->read('SapSelections_BA_NAME');
            
            #get base_date, deadLine_date accourding to layer_code, period and max id
            $condi["date_format(Sap.period,'%Y-%m')"] = $period;
            $submission_deadline = "";
            $reference_date = "";
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

            $condition1 = "";
            $condition2 = "";
            if ($layer_code == "") {
                $condition1 = array(
                    'flag' => 6,
                    'period' => $period.'-01'
                );
                $condition2 = array(
                    'Sap.flag' => array(5,6),
                    'Sap.period' => $period.'-01'
                );
            } else {
                $condition1 = array(
                    'flag' => 6,
                    'period' => $period.'-01',
                    'layer_code' => $layer_code
                );
                $condition2 = array(
                    'Sap.flag' => array(5,6),
                    'Sap.period' => $period.'-01',
                    'Sap.layer_code' => $layer_code
                );
            }
            
            $flag6_count = $this->Sap->find('count', array(
                'conditions' => $condition1,
                'group' => array(
                    'layer_code',
                    'account_code',
                    'destination_code',
                    'logistic_index_no',
                    'posting_date',
                    'recorded_date',
                    'Sap.receipt_shipment_date',
                    'schedule_date',
                    'period'
                )
            ));

            $f5and6count = $this->Sap->find('count', array(
                'conditions' => $condition2,
                'group' => array(
                    'layer_code',
                    'account_code',
                    'destination_code',
                    'logistic_index_no',
                    'posting_date',
                    'recorded_date',
                    'Sap.receipt_shipment_date',
                    'schedule_date',
                    'period'
                )
            ));
            
            #All permission allow to Admin when BA Code = '8000'
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
            
            if(!array_filter($show_btn) && count(array_filter($checkButtonType)) == 1){
                $show_btn = $checkButtonType;
            }
            if($show_btn['save']) $flag = '3';
            elseif($show_btn['request']) $flag = '4';
            elseif($show_btn['review']) $flag = '6';
            
            if(!empty($flag)) $saved_count = $Common->checkSavedCount('Sap', $period, $layer_code, $flag);
            
            $this->Session->write('SHOW_BTN',$show_btn);
            $this->Session->delete('SEARCH_QUERY');
            $this->set(compact('checkButtonType','show_btn'));
            $this->set('searchSaleRepre', "");
            $this->set('searchlogistics', "");
            $this->set('BAName', $BAName);
            $this->set('fApprove', $fApprove);
            $this->set('CancelApprove', $CancelApprove);
            $this->set('RejectApprove', $RejectApprove);
            $this->set('countApprove', $countApprove1);
            $this->set('count', $count);
            $this->set('page', $this->paginate());
            $this->set('role_id', $role_id);
            $this->set('PERIOD', $period);
            $this->set('BA_CODE', $layer_code);
            $this->set('successMsg', $successMsg);
            $this->set('errorMsg', $errorMsg);
            $this->set('target_month', $period);
            $this->set('reference_date', $reference_date);
            $this->set('submission_deadline', $submission_deadline);
            $this->set('flag6_count', $flag6_count);
            $this->set('f5and6count', $f5and6count);
            $this->set('layer_name', $layer_name);
            $this->set('saved_count', $saved_count);
            $this->render('index');
        } else {
            $this->redirect(array('controller' => 'SapSelections', 'action' => 'index'));
        }
    }

    public function SaveSapAddComments()
    {
        $Common = new CommonController;
        $errorMsg = "";
        $successMsg = "";
        $allChk = false;

        $layout = '';
        $period  = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        $login_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');//get login id
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $pageno = $this->Session->read('Page.pageCount');
        $search_query = $this->Session->read('SEARCH_QUERY');
        $this->Session->delete('SEARCH_QUERY');
        $date = date('Y-m-d H:i:s');
        $datas = array();
        
        $condi_deadline = array();
        $condi_deadline['Sap.flag >='] = '2';
        $condi_deadline["date_format(Sap.period,'%Y-%m')"] = $period;
        $condi_deadline['Sap.layer_code'] = $layer_code;
        $deadline_date = "";

        $date_foremail = $this->Sap->find('all', array(
            'conditions' => $condi_deadline,
            'fields' => array('date_format(Sap.deadline_date,"%Y-%m-%d") as deadline_date')
        ));
        
        if (!empty($date_foremail)) {
            foreach ($date_foremail as $value) {
                $deadline_date = $value[0]['deadline_date'];
            }
        }

        $data_array = $this->request->data('json_data');

        $save_cmt = json_decode($data_array);
        
        $sap_id_arr = array_column($save_cmt, '0');
        
        $rsl = array_unique(array_diff_assoc($sap_id_arr, array_unique($sap_id_arr)));
        
        if (!empty($rsl)) {
            $rsl = array_values($rsl);
            $count = count($save_cmt);
            $cnt_rsl = count($rsl);
            for ($i=0; $i<$count; $i++) {
                $save_id = $save_cmt[$i][0];
                $save_com1 = $save_cmt[$i][1];
                $save_com2 = $save_cmt[$i][2];
                $save_com3 = $save_cmt[$i][3];
                $save_status = $save_cmt[$i][4];

                for ($j=0; $j<$cnt_rsl; $j++) {
                    $rsl_id = $rsl[$j];
                    if (($save_id == $rsl_id) && ($save_status == false)) {
                        unset($save_cmt[$i]);
                    }
                }
            }
        }

        $save_cmt = array_values($save_cmt);
        $countSave = count($save_cmt);


        try {
            $sapDB = $this->Sap->getDataSource();
            $BicmDB = $this->SapBusiInchargeComment->getDataSource();
            $sapDB->begin();
            $BicmDB->begin();

            for ($i =0; $i < $countSave; $i++) {
                $sap_id = $save_cmt[$i][0];
                $Reason = trim($save_cmt[$i][1]);
                $Settlement_date = trim($save_cmt[$i][2]);
                $Remarks = trim($save_cmt[$i][3]);
                $chk_status = $save_cmt[$i][4];
                
                if (!empty($sap_id)) {
                    $checkAlreadyExistBICM = $this->SapBusiInchargeComment->find('all', array(
                        'conditions' => array(
                            'sap_id' => $sap_id,
                            'SapBusiInchargeComment.flag' => '1'
                        )
                    ));
                    
                    $chk_M_Sap = $this->Sap->find('all', array(
                        'conditions' => array(
                            'id' => $sap_id
                        )
                    ));
                    $chkSapFlag = "";
                    if (!empty($chk_M_Sap)) {
                        $chkSapFlag = $chk_M_Sap[0]['Sap']['flag'];
                    }

                    if ($chk_status == 'check') {
                        $to_flag = 3;#change flag 3
                        $from_flag = 2;#filter flag 2
                    }else {
                        $to_flag = 2;#change flag 2
                        $from_flag = 3;#filter flag 3
                    }
                    
                    $getMatchFlag = $this->Sap->getMatchFlag($sap_id);
                    
                    if(empty($getMatchFlag)) $getMatchFlag[0]['Saps']['id'] = $sap_id;
                    
                    $Reason = (!empty($Reason)) ? $Reason : "";
                    $Settlement_date = (!empty($Settlement_date)) ? date('Y-m-d', strtotime($Settlement_date)) : "0000-00-00";
                    $Remarks = (!empty($Remarks)) ? $Remarks : "";

                    $inch_cmt_flag = false;
                    if ($chkSapFlag == '2' || $chkSapFlag == "3") {
                        $inch_cmt_flag = true;
                    }
                    
                    foreach ($getMatchFlag as $value) {
                        $matchSapId = $value['saps']['id'];
                        
                        $this->Sap->updateAll(
                            array(
                                "Sap.flag" => $to_flag,
                                "Sap.updated_date" => "'".$date."'",
                                "Sap.updated_by" => "'".$login_id."'"
                            ),
                            array(
                                "Sap.id" => $matchSapId,
                                "Sap.flag" => $from_flag,
                            )
                        );
                        
                        if($inch_cmt_flag) {
                            if(!empty($checkAlreadyExistBICM)) {
                                $save_inch_cmt = [];
                                $getAllId = $this->SapBusiInchargeComment->find('first', array(
                                    'conditions' => array(
                                        'sap_id' => $matchSapId,
                                        'SapBusiInchargeComment.flag' => '1'
                                    ),
                                    'fields' => array('id')
                                ));
                                $save_inch_cmt['id'] = $getAllId['SapBusiInchargeComment']['id'];
                            }
                            $save_inch_cmt['sap_id'] = $matchSapId;
                            $save_inch_cmt['reason'] = $Reason;
                            $save_inch_cmt['settlement_date'] = $Settlement_date;
                            $save_inch_cmt['remark'] = $Remarks;
                            $save_inch_cmt['flag'] = 1;
                            $save_inch_cmt['created_by'] = $login_id;
                            $save_inch_cmt['updated_by'] = $login_id;
                            $save_inch_cmt['created_date'] = $date;
                            $save_inch_cmt['updated_date'] = $date;
                            $this->SapBusiInchargeComment->create();
                            $this->SapBusiInchargeComment->save($save_inch_cmt);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $sapDB->rollback();
            $BicmDB->rollback();

            $errorMsg = parent::getErrorMsg('SE003');
            $this->Flash->set($errorMsg, array('key'=>'saveError'));
            $data = array(
                'content' => "",
                'invalid' => "",
                'error'   => $errorMsg,
            );
            $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index'));
        }
        
        if($_POST['mailSend']) {
            if($this->sendEmailP3SapAddComments()){

                $sapDB->commit();
                $BicmDB->commit();
                $successMsg = parent::getSuccessMsg("SS001", [__("正常")]);
                $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                $successMsg = parent::getSuccessMsg("SS018");
                $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
            }

        }else{
            $sapDB->commit();
            $BicmDB->commit();
            $successMsg = parent::getSuccessMsg("SS001", [__("正常")]);
            $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
        }
        
        
        if(!empty($search_query)) {
            if($pageno > 1) {
                $this->redirect(array('controller'=>'SapAddComments', 'action'=>'searchSaleRepresentative', 'page' => $pageno, '?' => $search_query));
            }else
            $this->redirect(array('controller'=>'SapAddComments', 'action'=>'searchSaleRepresentative', '?' => $search_query));
        }else {
            if($pageno > 1) {
                $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index', 'page' => $pageno));
            }else
            $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index'));
        }
    }

    public function RequestSapAddComments()
    {
        $Common = new CommonController();
        #only allow ajax request

        $layout = '';
        $invalid_email = "";

        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $login_id = $this->Session->read('LOGIN_ID');
        $pageno = $this->Session->read('Page.pageCount');
        $search_query = $this->Session->read('SEARCH_QUERY');
        $this->Session->delete('SEARCH_QUERY');
        # added for submission deadline in mail body (by khin hnin myo)
        $condi_deadline = array();
        $condi_deadline['Sap.flag >='] = '2';
        $condi_deadline["date_format(Sap.period,'%Y-%m')"] = $period;
        $condi_deadline['Sap.layer_code'] = $layer_code;
        $deadline_date = "";

        $date_foremail = $this->Sap->find('all', array(
            'conditions' => $condi_deadline,
            'fields' => array(
                'date_format(Sap.deadline_date,"%Y-%m-%d") as deadline_date')));
        
        if (!empty($date_foremail)) {
            foreach ($date_foremail as $value) {
                $deadline_date = $value[0]['deadline_date'];
            }
        }
        
        $date = date('Y-m-d H:i:s');
        
        $data_array = $this->request->data('json_data');

        $save_cmt = json_decode($data_array);
        $sap_id_arr = array_column($save_cmt, '0');
        
        $rsl = array_unique(array_diff_assoc($sap_id_arr, array_unique($sap_id_arr)));

        if (!empty($rsl)) {

            $rsl = array_values($rsl);
            $count = count($save_cmt);
            $cnt_rsl = count($rsl);

            for ($i=0; $i<$count; $i++) {
                $save_id = $save_cmt[$i][0];
                $save_com = $save_cmt[$i][1];
                $save_status = $save_cmt[$i][2];
                
                for ($j=0; $j<$cnt_rsl; $j++) {
                    $rsl_id = $rsl[$j];
                    if (($save_id == $rsl_id) && ($save_status == false)) {
                        unset($save_cmt[$i]);
                    }
                }
            }
        }
        
        $eerror = 0;
        $save_cmt = array_values($save_cmt);
        $countSave = count($save_cmt);

        try {
            $sapDB = $this->Sap->getDataSource();
            $BacDB = $this->SapBusiAdminComment->getDataSource();
            $sapDB->begin();
            $BacDB->begin();
            
            for ($i =0; $i < $countSave; $i++) {
                $sap_id = $save_cmt[$i][0];
                $Comment = trim($save_cmt[$i][1]);

                $chk_status = $save_cmt[$i][2];

                $checkSapImportsModel = $this->Sap->find('all', array(
                    'conditions' => array(
                        'id' => $sap_id,
                        'OR' => array(
                            array('Sap.flag >=' => '5'),
                            array('Sap.flag <='=>'2')
                        ),
                    )
                ));

                if (empty($checkSapImportsModel)) {
                    if (!empty($sap_id)) {
                        $checkAlreadyExistBACM = $this->SapBusiAdminComment->find('all', array(
                            'conditions' => array(
                                'sap_id' => $sap_id,
                                'SapBusiAdminComment.flag' => '1'
                            )
                        ));
                        if ($chk_status == 'check') {
                            $to_flag = 4;#change flag 3
                            $from_flag = 3;#filter flag 2
                        }else {
                            $to_flag = 3;#change flag 2
                            $from_flag = 4;#filter flag 3
                        }
                        $getMatchFlag = $this->Sap->getMatchFlag($sap_id);
                        if(empty($getMatchFlag)) $getMatchFlag[0]['Saps']['id'] = $sap_id;

                        foreach ($getMatchFlag as $value) {
                            $matchSapId = $value['saps']['id'];
                            $this->Sap->updateAll(
                                array(
                                    "Sap.flag" => $to_flag,
                                    "Sap.updated_date" => "'".$date."'",
                                    "Sap.updated_by" => "'".$login_id."'"
                                ),
                                array(
                                    "Sap.id" => $matchSapId,
                                    "Sap.flag" => $from_flag
                                )
                            );
                            if (!empty($checkAlreadyExistBACM)) {
                                $save_adm_cmt = [];
                                $getAllId = $this->SapBusiAdminComment->find('first', array(
                                    'conditions' => array(
                                        'sap_id' => $matchSapId,
                                        'SapBusiAdminComment.flag' => '1'
                                    ),
                                    'fields' => array('id')
                                ));
                                $save_adm_cmt['id'] = $getAllId['SapBusiAdminComment']['id'];
                            }
                            $save_adm_cmt['sap_id'] = $matchSapId;
                            $save_adm_cmt['comment'] = (!empty($Comment)) ? $Comment : "";
                            $save_adm_cmt['flag'] = '1';
                            $save_adm_cmt['created_by'] = $login_id;
                            $save_adm_cmt['updated_by'] = $login_id;
                            $save_adm_cmt['created_date'] = $date;
                            $save_adm_cmt['updated_date'] = $date;
                            $this->SapBusiAdminComment->create();
                            $this->SapBusiAdminComment->save($save_adm_cmt);
                        }
                    }
                } else {
                    $errorFlag = $checkSapImportsModel[0]['Sap']['flag'];
                    
                    if ($errorFlag >= '5') {
                        $eerror++;
                        break;
                    }
                    break;
                }
            }

            //mail send function to bucho
            if ($eerror == 0) {

                //check count of all is equal to the count of check column
                $condition = array();
                $condition['Sap.flag != '] = '0';
                $condition['Sap.flag > '] = '1';
                $condition["date_format(Sap.period,'%Y-%m')"] = $period;
                $condition['Sap.layer_code'] = $layer_code;

                $count = $this->Sap->find('all', array(
                    'conditions' =>$condition,
                    'group' => array(
                        'Sap.layer_code',
                        'Sap.account_code',
                        'Sap.destination_code',
                        'Sap.logistic_index_no',
                        'Sap.posting_date',
                        'Sap.recorded_date',
                        'Sap.receipt_shipment_date',
                        'Sap.schedule_date',
                        'Sap.period'
                    )
                ));

                //count for flag 4 column
                $countSapAddComments = count($count);

                $condi = array();
                $condi['Sap.flag >='] = '4';
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
                        'Sap.receipt_shipment_date',
                        'Sap.schedule_date',
                        'Sap.period'
                    )
                ));

                $countChkSapAddComments = count($chkSapAddComments);

                # check or uncheck all checkbox
                if ($countSapAddComments == $countChkSapAddComments) {

                    if($_POST['mailSend']) {

                        if($this->sendEmailP3SapAddComments()){

                            $sapDB->commit();
                            $BacDB->commit();
                            $successMsg = parent::getSuccessMsg("SS008");
                            $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                            $successMsg = parent::getSuccessMsg("SS018");
                            $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                        } 

                    }else{
                        $sapDB->commit();
                        $BacDB->commit();
                        $successMsg = parent::getSuccessMsg("SS008");
                        $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                    }
                    
                } else {
                    $sapDB->commit();
                    $BacDB->commit();
                    $successMsg = parent::getSuccessMsg("SS008");
                    $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                    $data = array(
                      'content' => $successMsg,
                      'invalid' => "",
                      'error'   => ""
                  );

                }
            } else {
                $errorMsg = parent::getErrorMsg('SE033');
                $this->Flash->set($errorMsg, array('key'=>'saveError'));
                $data = array(
                  'content' => "",
                  'invalid' => "",
                  'error'   => $errorMsg
              );
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $sapDB->rollback();
            $BacDB->rollback();
            $errorMsg = parent::getErrorMsg("SE011", [__("依頼")]);
            $errorMsg .= " ".$invalid_email;

            $this->Flash->set($errorMsg, array('key'=>'saveError'));
            $data = array(
                'content' => "",
                'invalid' => "",
                'error'   => $errorMsg
            );
            
        }
        if(!empty($search_query)) {
            if($pageno > 1) {
                $this->redirect(array('controller'=>'SapAddComments', 'action'=>'searchSaleRepresentative', 'page' => $pageno, '?' => $search_query));
            }else
            $this->redirect(array('controller'=>'SapAddComments', 'action'=>'searchSaleRepresentative', '?' => $search_query));
        }else {
            if($pageno > 1) {
                $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index', 'page' => $pageno));
            }else
            $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index'));
        }
    }

    public function ApproveSapAddComments()
    {
        $Common = new CommonController();
        $errorMsg = "";
        $successMsg = "";
        $login_id = $this->Session->read('LOGIN_ID');
        $period   = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code  = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $date = date('Y-m-d H:i:s');
        $invalid_email = '';
        
        # added for submission deadline in mail body (by khin hnin myo)
        $condi_deadline = array();
        $condi_deadline['Sap.flag >='] = '2';
        $condi_deadline["date_format(Sap.period,'%Y-%m')"] = $period;
        $condi_deadline['Sap.layer_code'] = $layer_code;
        $deadline_date = "";

        $date_foremail = $this->Sap->find('all', array(
            'conditions' => $condi_deadline,
            'fields' => array(
                'date_format(Sap.deadline_date,"%Y-%m-%d") as deadline_date')));
        
        if (!empty($date_foremail)) {
            foreach ($date_foremail as $value) {
                $deadline_date = $value[0]['deadline_date'];
            }
        }

        $SID_ARRAY = $this->Session->read('SID_ARRAY');
        $this->Session->delete('SID_ARRAY');

        $sapDB = $this->Sap->getDataSource();
        $BmaDB = $this->SapBusiManagerApprove->getDataSource();

        $sapDB->begin();
        $BmaDB->begin();

        $getBACode = $this->Sap->find('all',array(
            'conditions' => array(
                'Sap.layer_code' => $layer_code,
                'date_format(Sap.period,"%Y-%m")' => $period,
                'OR' => array(
                    array('Sap.flag'=>'2'),
                    array('Sap.flag'=>'3')
                )
            ),
            'fields' => array(
                'Sap.layer_code',
                'Sap.id',
                'Sap.flag'
            )
        ));

        try {
            if (!empty($getBACode)) {
                $errorMsg = parent::getErrorMsg('SE040');
                $this->Flash->set($errorMsg, array('key'=>'saveError'));
                $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
            } else {
                if (!empty($SID_ARRAY)) {
                    foreach ($SID_ARRAY as $value) {

                        // change flag 5 at tbl_m_sap.
                        $this->Sap->updateAll(
                            array(
                                "Sap.flag"=>'5',
                                "Sap.updated_date"=>"'".$date."'",
                                "Sap.updated_by"=>"'".$login_id."'"
                            ),
                            array("Sap.id"=>$value,
                              "Sap.flag"=>'4')
                        );

                        $this->SapBusiManagerApprove->create();
                        $this->SapBusiManagerApprove->saveAll(array(
                            'sap_id'=>$value,
                            'approve_date'=> date('Y-m-d'),
                            'flag' => '1',
                            'created_by' => $login_id,
                            'updated_by' => $login_id
                        ));
                        
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE018');
                    $this->Flash->set($errorMsg, array('key'=>'saveError'));
                    $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
                }
            }
            if($_POST['mailSend']) {

                if($this->sendEmailP3SapAddComments()){
                    $sapDB->commit();
                    $BmaDB->commit();
                    $successMsg = parent::getSuccessMsg("SS005");
                    $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                    $successMsg = parent::getSuccessMsg("SS018");
                    $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                }

            }else{
                $sapDB->commit();
                $BmaDB->commit();
                $successMsg = parent::getSuccessMsg("SS005");
                $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
            }
        } catch (Exception $e) {

            $sapDB->rollback();
            $BmaDB->rollback();
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $msg = parent::getErrorMsg("SE011", [__("依頼")]);
            $msg .= " ".$invalid_email;
            $this->Flash->set($msg, array('key'=>'saveError'));
            $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
        }

        $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
    }

    public function ApproveCancelSapAddComments()
    {

        $errorMsg = "";
        $successMsg = "";
        $login_id   = $this->Session->read('LOGIN_ID');
        $period     = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code    = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name    = $this->Session->read('SapSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $date = date('Y-m-d H:i:s');
        $invalid_email = '';
        
        # added for submission deadline in mail body (by khin hnin myo)
        $condi_deadline = array();
        $condi_deadline['Sap.flag >='] = '2';
        $condi_deadline["date_format(Sap.period,'%Y-%m')"] = $period;
        $condi_deadline['Sap.layer_code'] = $layer_code;
        $deadline_date = "";

        $date_foremail = $this->Sap->find('all', array(
            'conditions' => $condi_deadline,
            'fields' => array(
                'date_format(Sap.deadline_date,"%Y-%m-%d") as deadline_date'
            )
        ));
        
        if (!empty($date_foremail)) {
            foreach ($date_foremail as $value) {
                $deadline_date = $value[0]['deadline_date'];
            }
        }
        
        $SID_ARRAY_CANCEL = $this->Session->read('SID_ARRAY_CANCEL');
        $this->Session->delete('SID_ARRAY_CANCEL');

        $sapDB = $this->Sap->getDataSource();
        $BmaDB = $this->SapBusiManagerApprove->getDataSource();

        try {
            $sapDB->begin();
            $BmaDB->begin();
            //change flag 0 at SapBusiManagerApprove.
            
            if (!empty($SID_ARRAY_CANCEL)) {
                foreach ($SID_ARRAY_CANCEL as $value) {
                    // select flag is not 5, to show error msg.
                    $checkSapImportsModel = $this->Sap->find('all', array(
                        'conditions' => array(
                            'id' => $value,
                            'NOT' => array('flag' => '5')
                        )
                    ));

                    if (!empty($checkSapImportsModel)) {
                        $errorMsg = parent::getErrorMsg('SE036');
                        $this->Flash->set($errorMsg, array('key'=>'saveError'));
                        $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
                    }
                }

                foreach ($SID_ARRAY_CANCEL as $value) {
                    $this->SapBusiManagerApprove->updateAll(
                        array(
                            "SapBusiManagerApprove.flag" => '0',
                            "SapBusiManagerApprove.updated_by" => "'".$login_id."'"
                        ),
                        array("SapBusiManagerApprove.sap_id" => $value)
                    );

                    // change (flag 4 at tbl_m_sap not use). new flow change flag 3
                    $this->Sap->updateAll(
                        array(
                            "Sap.flag" => '3',
                            "Sap.updated_date" => "'".$date."'",
                            "Sap.updated_by" => "'".$login_id."'"
                        ),
                        array(
                            "Sap.id" => $value,
                            "Sap.flag" => '5'
                        )
                    );
                }
                if($_POST['mailSend']) {

                    if($this->sendEmailP3SapAddComments()){

                        $sapDB->commit();
                        $BmaDB->commit();
                        $successMsg = parent::getSuccessMsg("SS006", [__("正常")]);
                        $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                        $successMsg = parent::getSuccessMsg("SS018");
                        $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                    }

                }else{

                    $sapDB->commit();
                    $BmaDB->commit();
                    $successMsg = parent::getSuccessMsg("SS006", [__("正常")]);
                    $this->Flash->set($successMsg, array('key'=>'saveSuccess'));

                }
                


            } else {
                $errorMsg = parent::getErrorMsg('SE039');
                $this->Flash->set($errorMsg, array('key'=>'saveError'));
                $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
            }
        } catch (Exception $e) {
            $sapDB->rollback();
            $BmaDB->rollback();
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $msg = parent::getErrorMsg("SE011", [__("依頼")]);
            $msg .= " ".$invalid_email;
            $this->Flash->set($msg, array('key'=>'saveError'));
            $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
        }

        $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
    }
    //added by Hein Htet Ko
    public function RejectSapAddComments()
    {
        $errorMsg   = "";
        $successMsg = "";
        $date       = date('Y-m-d H:i:s');
        $login_id   = $this->Session->read('LOGIN_ID');
        $period     = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code    = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name    = $this->Session->read('SapSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $invalid_email = '';

        # added for submission deadline in mail body (by khin hnin myo)
        $condi_deadline = array();
        $condi_deadline['Sap.flag >='] = '2';
        $condi_deadline["date_format(Sap.period,'%Y-%m')"] = $period;
        $condi_deadline['Sap.layer_code'] = $layer_code;
        $deadline_date = "";

        $date_foremail = $this->Sap->find('all', array(
            'conditions' => $condi_deadline,
            'fields' => array(
                'date_format(Sap.deadline_date,"%Y-%m-%d") as deadline_date')));
        
        if (!empty($date_foremail)) {
            foreach ($date_foremail as $value) {
                $deadline_date = $value[0]['deadline_date'];
            }
        }

        $SID_ARRAY_REJECT = $this->Session->read('SID_ARRAY_REJECT');
        
        $this->Session->delete('SID_ARRAY_REJECT');


        $sapDB = $this->Sap->getDataSource();
        $BmaDB = $this->SapBusiManagerApprove->getDataSource();

        try {
            $sapDB->begin();
            $BmaDB->begin();
            //change flag 0 at SapBusiManagerApprove.
            
            if (!empty($SID_ARRAY_REJECT)) {
                foreach ($SID_ARRAY_REJECT as $value) {
                    // select flag is not 4, to show error msg.
                    $checkSapImportsModel = $this->Sap->find('all', array(
                        'conditions' => array(
                            'id' => $value,
                            'NOT' => array('flag' => '4')
                        )
                    ));

                    if (!empty($checkSapImportsModel)) {
                        $errorMsg = parent::getErrorMsg('SE036');
                        $this->Flash->set($errorMsg, array('key'=>'saveError'));
                        $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
                    }
                }

                foreach ($SID_ARRAY_REJECT as $value) {
                    $this->SapBusiManagerApprove->updateAll(
                        array(
                            "SapBusiManagerApprove.flag" => '0',
                            "SapBusiManagerApprove.updated_by" => "'".$login_id."'"
                        ),
                        array("SapBusiManagerApprove.sap_id" => $value)
                    );

                    // change (flag 4 at tbl_m_sap not use). new flow change flag 3
                    $this->Sap->updateAll(
                        array(
                            "Sap.flag" => '3',
                            "Sap.updated_date" => "'".$date."'",
                            "Sap.updated_by" => "'".$login_id."'"
                        ),
                        array(
                            "Sap.id" => $value,
                            "Sap.flag" => '4'
                        )
                    );
                }
                if($_POST['mailSend']) {

                    if($this->sendEmailP3SapAddComments()){

                        $sapDB->commit();
                        $BmaDB->commit();
                        $successMsg = parent::getSuccessMsg("SS014");
                        $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                        $successMsg = parent::getSuccessMsg("SS018");
                        $this->Flash->set($successMsg, array('key'=>'saveSuccess'));

                    }

                }else{
                    $sapDB->commit();
                    $BmaDB->commit();
                    $successMsg = parent::getSuccessMsg("SS014");
                    $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                }
                

            } else {
                $errorMsg = parent::getErrorMsg('SE039');
                $this->Flash->set($errorMsg, array('key'=>'saveError'));
                $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
            }
        } catch (Exception $e) {
            $sapDB->rollback();
            $BmaDB->rollback();
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $msg = parent::getErrorMsg("SE011", [__("依頼")]);
            $msg .= " ".$invalid_email;
            $this->Flash->set($msg, array('key'=>'saveError'));
            $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
        }

        $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
    }


    public function ReviewSapAddComments()
    {

        $login_id = $this->Session->read('LOGIN_ID'); //get login id
        $period   = $this->Session->read('SapSelections_PERIOD_DATE');
        
        $login_user_name = $this->Session->read('LOGIN_USER');
        $pageno = $this->Session->read('Page.pageCount');
        $search_query = $this->Session->read('SEARCH_QUERY');
        $this->Session->delete('SEARCH_QUERY');
        $date = date('Y-m-d H:i:s');
        
        $data_array = $this->request->data('json_data');
        $save_cmt = json_decode($data_array);

        try {
            $sapDB = $this->Sap->getDataSource();
            $AicDB = $this->SapAccInchargeComment->getDataSource();
            $sapDB->begin();
            $AicDB->begin();

            for ($i =0; $i < count($save_cmt);$i++) {
                $sap_id = $save_cmt[$i][0];
                $Comment = trim($save_cmt[$i][1]);
                
                $chk_status = $save_cmt[$i][2];
                
                if (!empty($sap_id)) {
                    $checkAlreadyHasAICM = $this->SapAccInchargeComment->find('all', array(
                        'conditions' => array(
                            'sap_id' => $sap_id,
                            'SapAccInchargeComment.flag' => '1'
                        )
                    ));

                    $chkSapFlag = $this->Sap->find('all', array('conditions' => array(
                            'Sap.id' => $sap_id
                        )
                    ))[0]['Sap']['flag'];

                    if ($chk_status == 'check') {
                        $to_flag = '6';#change flag 6
                        $from_flag = array('5', '6');#filter flag array(5, 6)
                    }else {
                        $to_flag = '5';#change flag 5
                        $from_flag = array('5', '6');#filter flag array(5, 6)
                    }
                    
                    $getMatchFlag = $this->Sap->getMatchFlag($sap_id);
                    
                    if(empty($getMatchFlag)) $getMatchFlag[0]['Saps']['id'] = $sap_id;

                    foreach ($getMatchFlag as $value) {
                        $matchSapId = $value['saps']['id'];

                        //change flag 6 at tbl_m_sap.
                        $this->Sap->updateAll(
                            array(
                                "Sap.flag" => $to_flag,
                                "Sap.updated_by" => "'".$login_id."'",
                                "Sap.updated_date" => "'".$date."'"),
                            array(
                                "Sap.id" => $matchSapId,
                                "Sap.flag" => $from_flag
                            )
                        );
                        
                        if (in_array($chkSapFlag, array('5', '6'))) {
                            $save_Ainch_cmt = [];
                            if (!empty($checkAlreadyHasAICM)) {
                                $getAllId = $this->SapAccInchargeComment->find('first', array(
                                    'conditions' => array(
                                        'sap_id' => $matchSapId,
                                        'SapAccInchargeComment.flag' => '1'
                                    ),
                                    'fields' => array('id')
                                ));
                                $save_Ainch_cmt['id'] = $getAllId['SapAccInchargeComment']['id'];
                            }
                            $save_Ainch_cmt['sap_id'] = $matchSapId;
                            $save_Ainch_cmt['comment'] = (!empty($Comment)) ? $Comment : "";
                            $save_Ainch_cmt['flag'] = '1';
                            $save_Ainch_cmt['created_by'] = $login_id;
                            $save_Ainch_cmt['updated_by'] = $login_id;
                            $save_Ainch_cmt['created_date'] = $date;
                            $save_Ainch_cmt['updated_date'] = $date;
                            $this->SapAccInchargeComment->create();
                            $this->SapAccInchargeComment->save($save_Ainch_cmt);
                        }
                    }   
                }
            }
            if($_POST['mailSend']) {

                if($this->sendEmailP3SapAddComments()){

                    $sapDB->commit();
                    $AicDB->commit();

                    $successMsg = parent::getSuccessMsg("SS008");
                    $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                    $successMsg = parent::getSuccessMsg("SS018");
                    $this->Flash->set($successMsg, array('key'=>'saveSuccess'));

                    // $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index'));
                }
            }else{
                $sapDB->commit();
                $AicDB->commit();

                $successMsg = parent::getSuccessMsg("SS008");
                $this->Flash->set($successMsg, array('key'=>'saveSuccess'));
                // $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index'));
            }
            

        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $sapDB->rollback();
            $AicDB->rollback();
            
            $errorMsg = parent::getErrorMsg('SE003');
            $this->Flash->set($errorMsg, array('key'=>'saveError'));
            $data = array(
                'content' => "",
                'invalid' => "",
                'error'   => ""
            );
            $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index'));

        }
        if(!empty($search_query)) {
            if($pageno > 1) {
                $this->redirect(array('controller'=>'SapAddComments', 'action'=>'searchSaleRepresentative', 'page' => $pageno, '?' => $search_query));
            }else
            $this->redirect(array('controller'=>'SapAddComments', 'action'=>'searchSaleRepresentative', '?' => $search_query));
        }else {
            if($pageno > 1) {
                $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index', 'page' => $pageno));
            }else
            $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index'));
        }
    }

    public function searchSaleRepresentative()
    {
        $errorMsg = "";
        $successMsg = "";
        $this->layout = 'retentionclaimdebt';
        $Common = new CommonController;

        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        
        $flag_list  = Setting::ADDCMT_FLAG;

        $data['role_id'] = $role_id;
        $data['period']         = $period;
        $data['layer_code']        = $layer_code;
        $data['page']           = $this->request->params['controller'];
        $data['permission']     = $permission;
        $data['flag_list']      = $flag_list;
        $data['modelName']      = 'Sap';
        $this->Session->write('SEARCH_QUERY', $this->request->query);
        // $checkButtonType = $Common->checkButtonType($data);
        // if(!array_filter($checkButtonType)){

        //     $errorMsg = parent::getErrorMsg('SE065');
        //     $this->Flash->set($errorMsg, array('key'=>'Error'));
        //     $this->redirect(array('controller' => 'SapSelections', 'action' => 'index'));
        // }
        $permissions = $this->Session->read('PERMISSIONS');
        unset($permissions['index']);
        foreach ($permissions as $action => $permission) {
            if((($layer_code == '' && $permission['limit']==0) || in_array($layer_code, array_keys($permission['layers'])))) {
                $checkButtonType[$action] = true;
            }
        }
        $condi = array();
        if (($period != null || $period != '')) {
            $role_id = $this->Session->read('ADMIN_LEVEL_ID');
            
            $conditions = array();
            $conditions['Sap.flag > '] = '1';
            $conditions["date_format(Sap.period,'%Y-%m')"] = $period;

            if ($layer_code != null || $layer_code != '') {
                $conditions['Sap.layer_code'] = $layer_code;
                $condi["Sap.layer_code"] = $layer_code;
            }

            $saleRepre = $this->request->query('saleRepre');
            $logistics = $this->request->query('logistics');//search logistics_no by Khin Hnin Myo
            
            $saleRepre = str_replace(array('\\', '_', '%'), array('\\\\', '\\_', '\\%'), $saleRepre);
            $logistics = str_replace(array('\\', '_', '%'), array('\\\\', '\\_', '\\%'), $logistics);
            
            $conditions['Sap.sale_representative LIKE'] = "%".$saleRepre."%";
            $conditions['Sap.logistic_index_no LIKE'] = "%".$logistics."%";

            try {
                $this->paginate=array(
                    'limit'=> Paging::TABLE_PAGING,
                    'conditions' =>$conditions,
                    'joins' => array(
                        array(
                            'alias' 	 => 'SapBusiInchargeComment',
                            'table' 	 => 'sap_busi_incharge_comments',
                            'type' 	 => 'Left',
                            'conditions' => 'Sap.id = SapBusiInchargeComment.sap_id AND SapBusiInchargeComment.flag <> 0'
                        ),
                        array(
                            'alias' 	 => 'SapBusiAdminComment',
                            'table' 	 => 'sap_busi_admin_comments',
                            'type' 	 => 'Left',
                            'conditions' => 'Sap.id = SapBusiAdminComment.sap_id AND SapBusiAdminComment.flag <> 0'
                        ),
                        array(
                            'alias' 	 => 'SapAccInchargeComment',
                            'table' 	 => 'sap_acc_incharge_comments',
                            'type' 	 => 'Left',
                            'conditions' => 'Sap.id = SapAccInchargeComment.sap_id AND SapAccInchargeComment.flag <> 0'
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
                        'Sap.currency',
                        'Sap.layer_code',
                        'Sap.receipt_shipment_date',
                        "DATE_FORMAT(Sap.schedule_date,'%Y-%m-%d') AS sap_schedule_date",
                        'Sap.numbers_day',
                        'SUM(Sap.jp_amount) AS jp_amount',
                        'Sap.maturity_date',
                        'Sap.line_item_text',
                        'Sap.sale_representative',
                        'Sap.flag',
                        'Sap.preview_comment',
                        'SapBusiInchargeComment.reason',
                        'SapBusiInchargeComment.settlement_date',
                        'SapBusiInchargeComment.remark',
                        'SapBusiInchargeComment.sap_id',
                        'SapBusiAdminComment.comment',
                        'SapBusiAdminComment.sap_id',
                        'SapAccInchargeComment.comment',
                        'SapAccInchargeComment.sap_id'
                    ),
                    'group' => array(
                        'Sap.layer_code',
                        'Sap.account_code',
                        'Sap.destination_code',
                        'Sap.logistic_index_no',
                        'Sap.posting_date',
                        'Sap.recorded_date',
                        'Sap.receipt_shipment_date',
                        'Sap.schedule_date',
                        'Sap.period'
                    ),
                    'order' => array(

                        'Sap.id' => 'ASC',
                        'Sap.layer_code'=>'ASC',
                        'Sap.account_code' => 'ASC',
                        'Sap.destination_code' => 'ASC',
                        'Sap.logistic_index_no' => 'ASC',
                        'Sap.posting_date' => 'ASC',
                        'Sap.recorded_date' => 'ASC',
                        'Sap.receipt_shipment_date',
                        'Sap.schedule_date' => 'ASC'

                    )

                );

                $page = $this->Paginator->paginate('Sap');
                
                $count = $this->params['paging']['Sap']['count'];
                $pageno = $this->params['paging']['Sap']['page'];
                $this->Session->write('Page.pageCount', $pageno);
                if ($count != 0) {
                    $this->set('succCount', parent::getSuccessMsg('SS004', $count));
                } else {
                    $this->set('errCount', parent::getErrorMsg('SE001'));
                }
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                $this->redirect(array('controller' => 'SapAddComments', 'action' => 'index'));
            }

            //for level5
            $fApprove = 0;
            $CancelApprove = 0;
            $sid_array_approve = array();
            $sid_array_cancel = array();
            // select all data but not flag != 0 and layer_code
            $ForApprove1 = $this->Sap->find(
                'all',
                array('conditions'=>array("date_format(Sap.period,'%Y-%m')"=>$period,
                   "Sap.layer_code"=>$layer_code,
                   'NOT' => array(
                    array('flag'=>'0'),
                    array('flag'=>'1'))))
            );

            //check selected all data, if(flag == 4)?
            if (!empty($ForApprove1)) {
                foreach ($ForApprove1 as $value) {
                    $toApproveFlag = $value['Sap']['flag'];
                    $sid = $value['Sap']['id'];
                    if ($toApproveFlag == '4') {
                        $sid_array_approve[] = $sid;
                        $fApprove++;
                    } elseif ($toApproveFlag == '5') {
                        $sid_array_cancel[] = $sid;
                        $CancelApprove++;
                    } else {
                        break;
                    }
                }
            }
            
            $countApprove1 = count($ForApprove1);
            $sidArrApproveCount = count($sid_array_approve);
            $sidArrCancelCount  = count($sid_array_cancel);

            if ($countApprove1 == $sidArrApproveCount) {
                $this->Session->write('SID_ARRAY', $sid_array_approve);
            } elseif ($countApprove1 == $sidArrCancelCount) {
                $this->Session->write('SID_ARRAY_CANCEL', $sid_array_cancel);
            }

            //get name_jp

            $getBAName = $Common->getLayerThreeName($layer_code,date('Y-m-d', strtotime($period)));
            $BAName = $getBAName['name_jp'];
            //get base_date, deadLine_date accourding to layer_code, period and max id
            $condi["date_format(Sap.period,'%Y-%m')"] = $period;
            $submission_deadline = "";
            $reference_date = "";
            $toShowDate = $this->Sap->find('all', array(
                'conditions' => $condi,
                'fields' => array('date_format(Sap.base_date,"%Y-%m-%d") as base_date',
                    'date_format(Sap.deadline_date,"%Y-%m-%d") as deadline_date',
                    'Sap.id'),
                'order' => array('Sap.id DESC'),
                'limit' => 1,
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
            $saleRepre = str_replace(array('\\\\', '\\_', '\\%'), array('\\', '_', '%'), $saleRepre);
            $logistics = str_replace(array('\\\\', '\\_', '\\%'), array('\\', '_', '%'), $logistics);


            $condition1 = "";
            $condition2 = "";
            if ($layer_code == "") {
                $condition1 = array(
                    'flag' => 6,
                    'period' => $period.'-01'
                );

                $condition2 = array(
                    'Sap.flag' => array(5,6),
                    'Sap.period' => $period.'-01'

                );
            } else {
                $condition1 = array(
                    'flag' => 6,
                    'period' => $period.'-01',
                    'layer_code' => $layer_code
                );

                $condition2 = array(
                    'Sap.flag' => array(5,6),
                    'Sap.period' => $period.'-01',
                    'Sap.layer_code' => $layer_code
                );
            }
            

            $flag6_count = $this->Sap->find('count', array(
                'conditions' => $condition1,
                'group' => array(
                    'layer_code',
                    'account_code',
                    'destination_code',
                    'logistic_index_no',
                    'posting_date',
                    'recorded_date',
                    'Sap.receipt_shipment_date',
                    'schedule_date',
                    'period'
                )
            ));
            $f5and6count = $this->Sap->find('count', array(
                'conditions' => $condition2,
                'group' => array(
                    'layer_code',
                    'account_code',
                    'destination_code',
                    'logistic_index_no',
                    'posting_date',
                    'recorded_date',
                    'Sap.receipt_shipment_date',
                    'schedule_date',
                    'period'
                )
            ));

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
            if(!array_filter($show_btn) && count(array_filter($checkButtonType)) == 1){
                $show_btn = $checkButtonType;
            }
            
            $this->set(compact('checkButtonType','show_btn'));       
            $this->set('searchSaleRepre', $saleRepre);
            $this->set('searchlogistics', $logistics);
            $this->set('BAName', $BAName);
            $this->set('fApprove', $fApprove);//show approve btn
            $this->set('CancelApprove', $CancelApprove);//show approve cancel btn
            $this->set('countApprove', $countApprove1);
            $this->set('count', $count);
            $this->set('page', $this->paginate());
            $this->set('role_id', $role_id);
            $this->set('PERIOD', $period);
            $this->set('BA_CODE', $layer_code);
            $this->set('BAName', $layer_name);
            $this->set('successMsg', $successMsg);
            $this->set('errorMsg', $errorMsg);
            $this->set('target_month', $period);
            $this->set('reference_date', $reference_date);
            $this->set('submission_deadline', $submission_deadline);
            $this->set('flag6_count', $flag6_count);
            $this->set('f5and6count', $f5and6count);

            $this->set('layer_name', $layer_name);
            $this->render('index');
        } else {
            $this->redirect(array('controller' => 'SapSelections', 'action' => 'index'));
        }
    }
    /***
     ** @author Nu Nu Lwin
     ** @Date 06.08.2019
     ** New added From customer side
    ***/
    public function Download_Add_Comment()
    {
        $period  = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        $show_btn = $this->Session->read('SHOW_BTN');

        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $searchRepre = $this->request->data('saleRepre');
        $logistics = $this->request->data('logistics');
        
        $searchRepre = str_replace(array('\\', '_', '%'), array('\\\\', '\\_', '\\%'), $searchRepre);
        $logistics = str_replace(array('\\', '_', '%'), array('\\\\', '\\_', '\\%'), $logistics);
        /* get Base Date and DeadLine date (By Thura Moe) */
        $reference_date = '';
        $submission_deadline = '';

        $getDate = $this->getBaseAndDeadlineDate($period, $layer_code);

        if (!empty($getDate)) {
            $reference_date = $getDate['base_date'];
            $submission_deadline = $getDate['deadline_date'];
        }
        

        $objPHPExcel = $this->PhpExcel->createWorksheet()->setDefaultFont('Cambria', 12);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3);
        $objPHPExcel ->getActiveSheet()->setShowGridlines(true);
        $objPHPExcel ->setActiveSheetIndex(0);
        
        $cell_name = "A1";
        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
        

        $objPHPExcel->getActiveSheet()->setTitle('Add Comment');

        $sheet = $this->PhpExcel->getActiveSheet();

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );
        $sheet->getStyle("C")->applyFromArray($style);
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
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN)
            )
        );
        $alignright = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN)
            )
        );


        $objPHPExcel->getActiveSheet()->getStyle('A:V')->getAlignment()->setWrapText(true);
        if($show_btn['save']) {
            $sheet->getStyle('A9:R9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle('A10:R10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            
            $objPHPExcel->getActiveSheet()->getStyle('A:R')->getAlignment()->setWrapText(true);
        }
        if($show_btn['request'] || $show_btn['reject'] || $show_btn['approve']) {
            $sheet->getStyle('A9:T9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle('A10:T10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            
            $objPHPExcel->getActiveSheet()->getStyle('A:T')->getAlignment()->setWrapText(true);
        }
        if($show_btn['approve_cancel'] || $show_btn['review'] || count(array_filter($show_btn)) < 1) {
            $sheet->getStyle('A9:V9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle('A10:V10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            
            $objPHPExcel->getActiveSheet()->getStyle('A:V')->getAlignment()->setWrapText(true);
        }

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

        $excel_result = $this->Sap->Add_Comment_Excel($layer_code, $period, $searchRepre, $logistics);
        
        if (!empty($excel_result)) {

            // Excel  Title(Account Review)
            $sheet->setCellValue('A1', __("コメント追加"));
            $sheet->mergeCells('A1:S1');
            $sheet->getStyle('A1:S1')->applyFromArray($aligncenter);
            
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
            $sheet->getStyle('B2')->applyFromArray($alignright);
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

            $objPHPExcel->getActiveSheet()->getStyle('J9:N10')->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('M9:M10')->getAlignment()->setWrapText(true);

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

            if (!$show_btn['save']) {

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
            }
            
            if ($show_btn['review'] || $show_btn['approve_cancel'] || count(array_filter($show_btn)) < 1) {

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
            }

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
                
                $receipt_shipment_date = $result['account_review_excel']['receipt_shipment_date'];
                $receipt_shipment_date = ($receipt_shipment_date == '' || $receipt_shipment_date == '0000-00-00' || $receipt_shipment_date == '0000-00-00 00:00:00' || $receipt_shipment_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($receipt_shipment_date));
                
                $sap_schedule_date = $result['account_review_excel']['schedule_date'];
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

                if ($show_btn['approve_cancel'] || $show_btn['review']) {
                    if ($flag == 5 || $flag == 6) {
                        $sheet->getStyle('A'.$a .':'.'V'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
                    } else {
                        $sheet->getStyle('A'.$a .':'.'V'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                    }
                } elseif ($show_btn['approve'] || $show_btn['reject']) {
                    if ($flag == 4 || $flag == 5) {
                        $sheet->getStyle('A'.$a .':'.'T'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                    } else {
                        $sheet->getStyle('A'.$a .':'.'T'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
                    }
                } elseif ($show_btn['request']) {
                    if ($flag == 3 || $flag == 4) {
                        $sheet->getStyle('A'.$a .':'.'T'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
                    } else {
                        $sheet->getStyle('A'.$a .':'.'T'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                    }
                } elseif ($show_btn['save']) {
                    if ($flag == 2 || $flag == 3) {
                        $sheet->getStyle('A'.$a .':'.'R'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
                    } else {
                        $sheet->getStyle('A'.$a .':'.'R'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                    }
                }elseif($flag == 6) {
                    $sheet->getStyle('A'.$a .':'.'V'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                }
                
                $sheet->getStyle('M'.$a)->getNumberFormat()->setFormatCode('#,##0');
                /* set value */
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$a, $account_name);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$a, $destination_code);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$a, $destination_name);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$a, $logistic_index_no);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$a, $posting_date);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$a, $recorded_date);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$a, $receipt_shipment_date);
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
                if (!$show_btn['save']) {
                    $objPHPExcel->getActiveSheet()->setCellValue('S'.$a, $chk_busi_admin_confirm);//1
                    $objPHPExcel->getActiveSheet()->getStyle('S'.$a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('S'.$a)->applyFromArray($alignright);
                    
                    $objPHPExcel->getActiveSheet()->setCellValue('T'.$a, $business_admin_comment);
                    $objPHPExcel->getActiveSheet()->getStyle('T'.$a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('T'.$a)->applyFromArray($alignleft);
                }

                if ($show_btn['review'] || $show_btn['approve_cancel'] || count(array_filter($show_btn)) < 1) {
                    $objPHPExcel->getActiveSheet()->setCellValue('U'.$a, $chk_acc_inc_confirm); //1
                    $objPHPExcel->getActiveSheet()->getStyle('U'.$a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('U'.$a)->applyFromArray($alignright);
                    
                    $objPHPExcel->getActiveSheet()->setCellValue('V'.$a, $acc_incharge_comment);
                    $objPHPExcel->getActiveSheet()->getStyle('V'.$a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('V'.$a)->applyFromArray($alignleft);
                }
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
                /* end */

                $a++;
            }
            
            $this->PhpExcel->output("SapAddComments".".xlsx");
            $this->autoLayout = false;
            $this->render('index');
        } else {
            $param = array( __("export"));
            $msg = parent::getErrorMsg("SE017", $param);
            $this->Session->write('EXCEL_ERR_MSG', $msg);
            $this->redirect(array('controller'=>'SapAddComments','action'=>'index'));
        }
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

    /***
     ** @author Nu Nu Lwin
     ** @Date 01.07.2022
     ** New added From chemical
     #data pass from view ajax to controller to get mail data
    ***/
    public function getMailLists() {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $language = $this->Session->read('Config.language');
        $Common     = New CommonController();
        $period     = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_name    = $this->Session->read('SapSelections_BA_NAME');
        $layer_code    = $_POST['layer_code'];
        $page       = $_POST['page'];
        $function   = $_POST['function'];
        $language   = $_POST['language'];

        $mails = $Common->getMailList($layer_code, $page, $function, $language, $layer_name, $period, Setting::LAYER_SETTING['SapSelections']);

        return json_encode($mails);
    }

    public function sendEmailP3SapAddComments(){

        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');

        $mail_template = 'common';
        $toEmail  = parent::formatMailInput($_POST['toEmail']);
        $ccEmail  = parent::formatMailInput($_POST['ccEmail']);
        $bccEmail = parent::formatMailInput($_POST['bccEmail']);
        
        $mail['subject']        = $_POST['mailSubj'];
        $mail['template_body']  = $_POST['mailBody'];
        
        if($this->request->params['action'] == 'ReviewSapAddComments') {
            $url = '/SapAccountReviews?'.'param='.$period.'&ba='.$layer_code;
        }else {
            $url = '/SapAddComments?'.'param='.$period.'&ba='.$layer_code;
        }
        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

        if ($sentMail["error"]) {
            $msg = $sentMail["errormsg"];
            $this->Flash->set($msg, array('key'=>'saveError'));
            $invalid_email = parent::getErrorMsg('SE042');
            $this->Flash->set($invalid_email, array('key'=>'saveError'));
            $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index'));

        }else{
            return true;
        }
    }

    /**
     * Get layer_name
     * @author - Nu Nu Lwin (2020/06/16)
     * @return - array
     */
    public function getBaName($layer_code)
    {
        $getBaName = $this->Layer->find('first', array(
            'fields' => array('name_jp'),
            'conditions' => array(
                'layer_code' => $layer_code,
                'Layer.flag' => 1
            )
        ));

        return $getBaName['Layer']['name_jp'];
    }
}
