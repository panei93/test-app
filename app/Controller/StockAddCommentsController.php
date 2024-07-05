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
class StockAddCommentsController extends AppController
{
    public $uses = array('Stock', 'StockBusiInchargeComment', 'StockBusiAdminComment', 'StockBusiManagerApprove', 'StockAccInchargeComment', 'Layer', 'User');
    public $components = array('Session', 'Paginator', 'PhpExcel.PhpExcel');

    public function beforeFilter()
    {
        parent::checkUserStatus();
        parent::CheckSession();
        parent::checkStockUrlSession();     
    }
    public function index($errmessage = null)
    {
        $this->layout = 'stocks';
        
        $errorMsg = "";
        $successMsg = "";
        $allChk = false;

        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');

        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');

        $permissions = $this->Session->read('PERMISSIONS');
        unset($permissions['index']);
        foreach ($permissions as $action => $permission) {
            if ((($layer_code == '' && $permission['limit'] == 0) || in_array($layer_code, array_keys($permission['layers'])))) {
                $checkButtonType[$action] = true;
            }
        }
        $Common = new CommonController();
        $flag_list  = Setting::ADDCMT_FLAG;

        $data['role_id']        = $role_id;
        $data['period']         = $period;
        $data['layer_code']     = $layer_code;
        $data['page']           = $this->request->params['controller'];
        $data['permission']     = $permission;
        $data['flag_list']      = $flag_list;
        $data['modelName']      = 'Stock';

        // $checkButtonType = $Common->checkButtonType($data);
        // if(!array_filter($checkButtonType)){
        //     $errorMsg = parent::getErrorMsg('SE065');
        //     $this->Flash->set($errorMsg, array('key'=>'Error'));
        //     $this->redirect(array('controller' => 'SapSelections', 'action' => 'index'));
        // }       

        if ($period != null || $period != '') {


            $condi = array(); //get base_date and deadLine_date with condition
            $condition = array();
            $condition['Stock.flag != '] = '0';
            $condition['Stock.flag > '] = '1';
            $condition["date_format(Stock.period,'%Y-%m')"] = $period;

            if ($layer_code != null || $layer_code != '') {
                $condition['Stock.layer_code'] = $layer_code;
                $condi["Stock.layer_code"] = $layer_code;
            }

            try {
                $this->paginate = array(
                    'limit' => Paging::TABLE_PAGING,
                    'conditions' => $condition,
                    'joins' => array(
                        array(
                            'alias'      => 'StockBusiInchargeComment',
                            'table'      => 'stock_busi_incharge_comments',
                            'type'      => 'Left',
                            'conditions' => 'Stock.id = StockBusiInchargeComment.stock_id AND StockBusiInchargeComment.flag <> 0'
                        ),
                        array(
                            'alias'      => 'StockBusiAdminComment',
                            'table'      => 'stock_busi_admin_comments',
                            'type'      => 'Left',
                            'conditions' => 'Stock.id = StockBusiAdminComment.stock_id AND StockBusiAdminComment.flag <> 0'
                        ),
                        array(
                            'alias'      => 'StockAccInchargeComment',
                            'table'      => 'stock_acc_incharge_comments',
                            'type'      => 'Left',
                            'conditions' => 'Stock.id = StockAccInchargeComment.stock_id AND StockAccInchargeComment.flag <> 0'
                        )

                    ),
                    'fields' => array(
                        'Stock.id',
                        'Stock.layer_code',
                        'Stock.destination_name',
                        'Stock.item_code',
                        'Stock.item_name',
                        'Stock.item_name_2',
                        'Stock.unit',
                        "DATE_FORMAT(Stock.registration_date,'%Y-%m-%d') AS stock_registration_date",
                        'Stock.numbers_day',
                        'Stock.receipt_index_no',
                        'Stock.quantity',
                        'SUM(Stock.amount) AS amount',
                        'Stock.is_error',
                        'Stock.is_sold',
                        'Stock.is_contract',
                        'Stock.preview_comment',
                        // 'Stock.solution',
                        'Stock.flag',
                        'StockBusiInchargeComment.reason',
                        'StockBusiInchargeComment.settlement_date',
                        'StockBusiInchargeComment.remark',
                        'StockBusiInchargeComment.stock_id',
                        'StockBusiAdminComment.comment',
                        'StockBusiAdminComment.stock_id',
                        'StockAccInchargeComment.comment',
                        'StockAccInchargeComment.stock_id'

                    ),

                    'group' => array(
                        'Stock.layer_code',
                        'Stock.destination_name',
                        'Stock.period',
                       
                    ),

                    'order' => array(

                        'Stock.id' => 'ASC',
                        'Stock.layer_code' => 'ASC',
                        'Stock.destination_name' => 'ASC',
                        
                    )

                );
                $page = $this->Paginator->paginate('Stock');

                $count = $this->params['paging']['Stock']['count'];
                $pageno = $this->params['paging']['Stock']['page'];
                $this->Session->write('Page.pageCount', $pageno);
                if ($count != 0) {
                    $this->set('succCount', parent::getSuccessMsg('SS004', $count));
                } else {
                    $this->set('errCount', parent::getErrorMsg('SE001'));
                }
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage() . ' in file ' . _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
            }

            $fApprove = 0;
            $CancelApprove = 0;
            $RejectApprove = 0;
            $stock_array_approve = array();
            $stock_array_cancel = array();
            //added by Hein Htet Ko
            $stock_array_reject = array();
            // select all data but not flag != 0,1 and layer_code
            $ForApprove1 = $this->Stock->find(
                'all',
                array('conditions' => array(
                    "date_format(Stock.period,'%Y-%m')" => $period,
                    "Stock.layer_code" => $layer_code,
                    'NOT' => array(
                        array('flag' => '0'),
                        array('flag' => '1')
                    )
                ))
            );
            //check selected all data, if(flag == 4)?
            if (!empty($ForApprove1)) {
                foreach ($ForApprove1 as $value) {
                    $toApproveFlag = $value['Stock']['flag'];
                    $sid = $value['Stock']['id'];
                    if ($toApproveFlag == '4') {
                        $stock_array_approve[] = $sid;
                        $fApprove++;
                        $stock_array_reject[] = $sid;
                        $RejectApprove++;
                    } elseif ($toApproveFlag == '5') {
                        $stock_array_cancel[] = $sid;
                        $CancelApprove++;
                    } else {
                        break;
                    }
                }
            }

            $countApprove1      = count($ForApprove1);
            $stockArrApproveCount = count($stock_array_approve);
            $stockArrCancelCount  = count($stock_array_cancel);
            $stockArrRejectCount  = count($stock_array_reject);

            if ($countApprove1 == $stockArrApproveCount || $countApprove1 == $stockArrRejectCount) {
                $this->Session->write('STOCK_ARRAY', $stock_array_approve);
                $this->Session->write('STOCK_ARRAY_REJECT', $stock_array_reject);
            } elseif ($countApprove1 == $stockArrCancelCount) {
                $this->Session->write('STOCK_ARRAY_CANCEL', $stock_array_cancel);
            }

            $BAName = $this->Session->read('StockSelections_BA_NAME');;

            //get base_date, deadLine_date accourding to layer_code, period and max id
            $condi["date_format(Stock.period,'%Y-%m')"] = $period;
            $submission_deadline = "";
            $reference_date = "";
            $toShowDate = $this->Stock->find('all', array(
                'conditions' => $condi,
                'fields' => array(
                    'date_format(Stock.base_date,"%Y-%m-%d") as base_date',
                    'date_format(Stock.deadline_date,"%Y-%m-%d") as deadline_date',
                    'Stock.id'
                ),
                'order' => array('Stock.id DESC'),
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

            $condition1 = "";
            $condition2 = "";
            if ($layer_code == "") {
                $condition1 = array(
                    'flag' => 6,
                    'period' => $period . '-01'
                );

                $condition2 = array(
                    'Stock.flag' => array(5, 6),
                    'Stock.period' => $period . '-01'
                );
            } else {
                $condition1 = array(
                    'flag' => 6,
                    'period' => $period . '-01',
                    'layer_code' => $layer_code
                );

                $condition2 = array(
                    'Stock.flag' => array(5, 6),
                    'Stock.period' => $period . '-01',
                    'Stock.layer_code' => $layer_code

                );
            }

            $flag6_count = $this->Stock->find('count', array(
                'conditions' => $condition1,
                'group' => array(
                    'layer_code',
                    'destination_name',
                    'period'

                )
            ));
          
            $f5and6count = $this->Stock->find('count', array(
                'conditions' => $condition2,
                'group' => array(
                    'layer_code',
                    'destination_name',
                    'period'

                )
            ));

            #All permission allow to Admin when BA Code = '8000'
            // array_keys($this->paginate(), min($pets));  # array('cats') 
            $flagArr = [];
            foreach ($this->paginate() as $key => $sap_value) {
                array_push($flagArr, $sap_value['Stock']['flag']);
            }

            $minFlag = min($flagArr);
            $show_btn = array();
            foreach ($flag_list as $button => $flag) {
                $show[$button] = (in_array($minFlag, $flag)) ? '1' : '';
                if (!array_diff_assoc($show, $checkButtonType)) {
                    $show_btn[$button] = (in_array($minFlag, $flag)) ? '1' : '';
                } else {
                    $show_btn[$button] = '';
                }
                $show = array();
            }

            if (!array_filter($show_btn) && count(array_filter($checkButtonType)) == 1) {
                $show_btn = $checkButtonType;
            }

            if($show_btn['save']) $flag = '3';
            elseif($show_btn['request']) $flag = '4';
            elseif($show_btn['review']) $flag = '6';
            
            if(!empty($flag)) $saved_count = $Common->checkSavedCount('Stock', $period, $layer_code, $flag);

            $this->Session->write('SHOW_BTN', $show_btn);
            $this->Session->delete('SEARCH_QUERY');
            $this->set(compact('checkButtonType', 'show_btn'));
            $this->set('searchSaleRepre', "");
            $this->set('searchlogistics', "");
            $this->set('BAName', $BAName);
            $this->set('fApprove', $fApprove); //show approve btn
            $this->set('CancelApprove', $CancelApprove); // show approve cancel btn
            $this->set('RejectApprove', $RejectApprove); //added by Hein Htet Ko
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
            $this->redirect(array('controller' => 'StockSelections', 'action' => 'index'));
        }
    }

    public function SaveStockAddComments()
    {
        $Common = new CommonController;
        $errorMsg = "";
        $successMsg = "";
        $allChk = false;
        $layout = '';
        $period  = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $login_id = $this->Session->read('LOGIN_ID');
        $login_user_name = $this->Session->read('LOGIN_USER'); //get login id
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $pageno = $this->Session->read('Page.pageCount');
        $search_query = $this->Session->read('SEARCH_QUERY');
        $this->Session->delete('SEARCH_QUERY');
        $date = date('Y-m-d H:i:s');
        $datas = array();

        # added submission deadline in mail body (by khin hnin myo)
        $condi_deadline = array();
        $condi_deadline['Stock.flag >='] = '2';
        $condi_deadline["date_format(Stock.period,'%Y-%m')"] = $period;
        $condi_deadline['Stock.layer_code'] = $layer_code;
        $deadline_date = "";
        $date_foremail = $this->Stock->find('all', array(
            'conditions' => $condi_deadline,
            'fields' => array(
                'date_format(Stock.deadline_date,"%Y-%m-%d") as deadline_date'
            )
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
            for ($i = 0; $i < $count; $i++) {
                $save_id = $save_cmt[$i][0];
                $save_com1 = $save_cmt[$i][1];
                $save_com2 = $save_cmt[$i][2];
                $save_com3 = $save_cmt[$i][3];
                $save_status = $save_cmt[$i][4];

                for ($j = 0; $j < $cnt_rsl; $j++) {
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
            $sapDB = $this->Stock->getDataSource();
            $BicmDB = $this->StockBusiInchargeComment->getDataSource();
            $sapDB->begin();
            $BicmDB->begin();

            for ($i = 0; $i < $countSave; $i++) {
                $sap_id = $save_cmt[$i][0];
                $Reason = trim($save_cmt[$i][1]);
                $Settlement_date = trim($save_cmt[$i][2]);
                $Remarks = trim($save_cmt[$i][3]);
                $chk_status = $save_cmt[$i][4];
                $preview_comment = $save_cmt[$i][5];
                
                if (!empty($sap_id)) {
                    $checkAlreadyExistBICM = $this->StockBusiInchargeComment->find(
                        'all',
                        array('conditions' => array(
                            'stock_id' => $sap_id,
                            'StockBusiInchargeComment.flag' => '1'
                        ))
                    );
                    $chk_M_Sap = $this->Stock->find(
                        'all',
                        array('conditions' => array('id' => $sap_id))
                    );
                    $chkSapFlag = "";
                    if (!empty($chk_M_Sap)) {
                        $chkSapFlag = $chk_M_Sap[0]['Stock']['flag'];
                    }
                    if ($chk_status == 'check') {
                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                        if (!empty($getMatchFlag)) {
                            foreach ($getMatchFlag as $value) {
                                $matchSapId = $value['stocks']['id'];
                                //change flag 3 at tbl_m_sap.
                                $this->Stock->updateAll(
                                    array(
                                        "Stock.flag" => '3',
                                        "Stock.updated_date" => "'" . $date . "'",
                                        "Stock.updated_by" => "'" . $login_id . "'"
                                    ),
                                    array(
                                        "Stock.id" => $matchSapId,
                                        "Stock.flag" => '2'
                                    )
                                );
                            }
                        } else {
                            //change flag 3 at tbl_m_sap.
                            $this->Stock->updateAll(
                                array(
                                    "Stock.flag" => '3',
                                    "Stock.updated_date" => "'" . $date . "'",
                                    "Stock.updated_by" => "'" . $login_id . "'"
                                ),
                                array(
                                    "Stock.id" => $sap_id,
                                    "Stock.flag" => '2'
                                )
                            );
                        }

                        if (!empty($checkAlreadyExistBICM)) {
                            if (!empty($Reason) || !empty($Settlement_date) || !empty($Remarks)) {
                                if (!empty($Settlement_date)) {
                                    $Settlement_date = date('Y-m-d', strtotime($Settlement_date));
                                } else {
                                    $Settlement_date = "0000-00-00";
                                }
                                $Reason = $BicmDB->value($Reason, 'string');
                                $Remarks = $BicmDB->value($Remarks, 'string');

                                // when page is not reflax(flag is >=4), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '2' || $chkSapFlag == "3") {
                                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                        if (!empty($getMatchFlag)) {
                                            foreach ($getMatchFlag as $value) {
                                                $matchSapId = $value['stocks']['id'];

                                                $this->StockBusiInchargeComment->updateAll(
                                                    array(
                                                        "StockBusiInchargeComment.flag" => '1',
                                                        "reason" => $Reason,
                                                        "settlement_date" => "'" . $Settlement_date . "'",
                                                        "remark" => $Remarks,
                                                        "updated_date" => "'" . $date . "'",
                                                        "updated_by" => "'" . $login_id . "'"
                                                    ),
                                                    array(
                                                        "StockBusiInchargeComment.stock_id" => $matchSapId,
                                                        'NOT' => array("StockBusiInchargeComment.flag" => '0')
                                                    )
                                                );
                                            }
                                        } else {
                                            $this->StockBusiInchargeComment->updateAll(
                                                array(
                                                    "StockBusiInchargeComment.flag" => '1',
                                                    "reason" => $Reason,
                                                    "settlement_date" => "'" . $Settlement_date . "'",
                                                    "remark" => $Remarks,
                                                    "updated_date" => "'" . $date . "'",
                                                    "updated_by" => "'" . $login_id . "'"
                                                ),
                                                array(
                                                    "StockBusiInchargeComment.stock_id" => $sap_id,
                                                    'NOT' => array("StockBusiInchargeComment.flag" => '0')
                                                )
                                            );
                                        }
                                    }
                                }
                            } else {
                                // when page is not reflax(flag is >=4), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '2' || $chkSapFlag == "3") {
                                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                        if (!empty($getMatchFlag)) {
                                            foreach ($getMatchFlag as $value) {
                                                $matchSapId = $value['stocks']['id'];

                                                $this->StockBusiInchargeComment->updateAll(
                                                    array(
                                                        "StockBusiInchargeComment.flag" => '1',
                                                        "reason" => "''",
                                                        "settlement_date" => "''",
                                                        "remark" => "''",
                                                        "updated_date" => "'" . $date . "'",
                                                        "updated_by" => "'" . $login_id . "'"
                                                    ),
                                                    array(
                                                        "StockBusiInchargeComment.stock_id" => $matchSapId,
                                                        'NOT' => array("StockBusiInchargeComment.flag" => '0')
                                                    )
                                                );
                                            }
                                        } else {
                                            $this->StockBusiInchargeComment->updateAll(
                                                array(
                                                    "StockBusiInchargeComment.flag" => '1',
                                                    "reason" => "''",
                                                    "settlement_date" => "''",
                                                    "remark" => "''",
                                                    "updated_date" => "'" . $date . "'",
                                                    "updated_by" => "'" . $login_id . "'"
                                                ),
                                                array(
                                                    "StockBusiInchargeComment.stock_id" => $sap_id,
                                                    'NOT' => array("StockBusiInchargeComment.flag" => '0')
                                                )
                                            );
                                        }
                                    }
                                }
                            }
                        } else {
                            if (!empty($Reason) || !empty($Settlement_date) || !empty($Remarks)) {
                                if (!empty($Settlement_date)) {
                                    $Settlement_date = date('Y-m-d', strtotime($Settlement_date));
                                } else {
                                    $Settlement_date = "0000-00-00";
                                }
                                // when page is not reflax(flag is >=4), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '2' || $chkSapFlag == "3") {
                                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                        if (!empty($getMatchFlag)) {
                                            foreach ($getMatchFlag as $value) {
                                                $matchSapId = $value['stocks']['id'];

                                                $this->StockBusiInchargeComment->create();
                                                $this->StockBusiInchargeComment->save(array(
                                                    'stock_id' => $matchSapId,
                                                    'reason' => $Reason,
                                                    'settlement_date' => $Settlement_date,
                                                    'remark' => $Remarks,
                                                    'StockBusiInchargeComment.flag' => '1',
                                                    'created_by' => $login_id,
                                                    'updated_by' => $login_id,
                                                    'created_date' => $date,
                                                    'updated_date' => $date
                                                ));
                                            }
                                        } else {
                                            $this->StockBusiInchargeComment->create();
                                            $this->StockBusiInchargeComment->save(array(
                                                'stock_id' => $sap_id,
                                                'reason' => $Reason,
                                                'settlement_date' => $Settlement_date,
                                                'remark' => $Remarks,
                                                'StockBusiInchargeComment.flag' => '1',
                                                'created_by' => $login_id,
                                                'updated_by' => $login_id,
                                                'created_date' => $date,
                                                'updated_date' => $date
                                            ));
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        // change flag 2 at stock table when check box is not checked

                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                        if (!empty($getMatchFlag)) {
                            foreach ($getMatchFlag as $value) {
                                $matchSapId = $value['stocks']['id'];

                                $this->Stock->updateAll(
                                    array(
                                        "Stock.flag" => '2',
                                        "Stock.updated_date" => "'" . $date . "'",
                                        "Stock.updated_by" => "'" . $login_id . "'"
                                    ),
                                    array(
                                        "Stock.id" => $matchSapId,
                                        "Stock.flag" => '3'
                                    )
                                );
                            }
                        } else {
                            $this->Stock->updateAll(
                                array(
                                    "Stock.flag" => '2',
                                    "Stock.updated_date" => "'" . $date . "'",
                                    "Stock.updated_by" => "'" . $login_id . "'"
                                ),
                                array(
                                    "Stock.id" => $sap_id,
                                    "Stock.flag" => '3'
                                )
                            );
                        }

                        if (!empty($checkAlreadyExistBICM)) {
                            if (!empty($Reason) || !empty($Settlement_date) || !empty($Remarks)) {
                                if (!empty($Settlement_date)) {
                                    $Settlement_date = date('Y-m-d', strtotime($Settlement_date));
                                } else {
                                    $Settlement_date = "0000-00-00";
                                }
                                $Reason = $BicmDB->value($Reason, 'string');
                                $Remarks = $BicmDB->value($Remarks, 'string');
                                // when page is not reflax(flag is >=4), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '2' || $chkSapFlag == "3") {
                                        $this->StockBusiInchargeComment->updateAll(
                                            array(
                                                "StockBusiInchargeComment.flag" => '1',
                                                "reason" => $Reason,
                                                "settlement_date" => "'" . $Settlement_date . "'",
                                                "remark" => $Remarks,
                                                "updated_date" => "'" . $date . "'",
                                                "updated_by" => "'" . $login_id . "'"
                                            ),
                                            array(
                                                "StockBusiInchargeComment.stock_id" => $sap_id,
                                                'NOT' => array("StockBusiInchargeComment.flag" => '0')
                                            )
                                        );
                                    }
                                }
                            } else {
                                // when page is not reflax(flag is >=4), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '2' || $chkSapFlag == "3") {
                                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                        if (!empty($getMatchFlag)) {
                                            foreach ($getMatchFlag as $value) {
                                                $matchSapId = $value['stocks']['id'];

                                                $this->StockBusiInchargeComment->updateAll(
                                                    array(
                                                        "StockBusiInchargeComment.flag" => '0',
                                                        "reason" => "''",
                                                        "settlement_date" => "''",
                                                        "remark" => "''",
                                                        "updated_date" => "'" . $date . "'",
                                                        "updated_by" => "'" . $login_id . "'"
                                                    ),
                                                    array(
                                                        "StockBusiInchargeComment.stock_id" => $matchSapId
                                                    )
                                                );
                                            }
                                        } else {
                                            $this->StockBusiInchargeComment->updateAll(
                                                array(
                                                    "StockBusiInchargeComment.flag" => '0',
                                                    "reason" => "''",
                                                    "settlement_date" => "''",
                                                    "remark" => "''",
                                                    "updated_date" => "'" . $date . "'",
                                                    "updated_by" => "'" . $login_id . "'"
                                                ),
                                                array(
                                                    "StockBusiInchargeComment.stock_id" => $sap_id
                                                )
                                            );
                                        }
                                    }
                                }
                            }
                        } else {
                            if (!empty($Reason) || !empty($Settlement_date) || !empty($Remarks)) {
                                if (!empty($Settlement_date)) {
                                    $Settlement_date = date('Y-m-d', strtotime($Settlement_date));
                                } else {
                                    $Settlement_date = "0000-00-00";
                                }
                                // when page is not reflax(flag is >=4), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '2' || $chkSapFlag == "3") {
                                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                        if (!empty($getMatchFlag)) {
                                            foreach ($getMatchFlag as $value) {
                                                $matchStockId = $value['stocks']['id'];

                                                $this->StockBusiInchargeComment->create();
                                                $this->StockBusiInchargeComment->save(array(
                                                    'stock_id' => $matchStockId,
                                                    'reason' => $Reason,
                                                    'settlement_date' => $Settlement_date,
                                                    'remark' => $Remarks,
                                                    'StockBusiInchargeComment.flag' => '1',
                                                    'created_by' => $login_id,
                                                    'updated_by' => $login_id,
                                                    'created_date' => $date,
                                                    'updated_date' => $date
                                                ));
                                            }
                                        } else {
                                            $this->StockBusiInchargeComment->create();
                                            $this->StockBusiInchargeComment->save(array(
                                                'stock_id' => $sap_id,
                                                'reason' => $Reason,
                                                'settlement_date' => $Settlement_date,
                                                'remark' => $Remarks,
                                                'StockBusiInchargeComment.flag' => '1',
                                                'created_by' => $login_id,
                                                'updated_by' => $login_id,
                                                'created_date' => $date,
                                                'updated_date' => $date
                                            ));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $sapDB->rollback();
            $BicmDB->rollback();

            $errorMsg = parent::getErrorMsg('SE003');
            $this->Flash->set($errorMsg, array('key' => 'saveError'));
            $data = array(
                'content' => "",
                'invalid' => "",
                'error'   => $errorMsg,
            );
            $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
        }

        if ($_POST['mailSend']) {
            if ($this->sendEmailP3StockAddComments()) {

                $sapDB->commit();
                $BicmDB->commit();
                $successMsg = parent::getSuccessMsg("SS001", [__("正常")]);
                $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                $successMsg = parent::getSuccessMsg("SS018");
                $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
            }
        } else {
            $sapDB->commit();
            $BicmDB->commit();
            $successMsg = parent::getSuccessMsg("SS001", [__("正常")]);
            $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
        }


        if (!empty($search_query)) {
            if ($pageno > 1) {
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'searchSaleRepresentative', 'page' => $pageno, '?' => $search_query));
            } else
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'searchSaleRepresentative', '?' => $search_query));
        } else {
            if ($pageno > 1) {
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index', 'page' => $pageno));
            } else
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
        }
    }

    public function RequestStockAddComments()
    {
        $Common = new CommonController();
        #only allow ajax request

        $layout = '';
        $invalid_email = "";

        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $login_id = $this->Session->read('LOGIN_ID');
        $pageno = $this->Session->read('Page.pageCount');
        $search_query = $this->Session->read('SEARCH_QUERY');
        $this->Session->delete('SEARCH_QUERY');
        # added for submission deadline in mail body (by khin hnin myo)
        $condi_deadline = array();
        $condi_deadline['Stock.flag >='] = '2';
        $condi_deadline["date_format(Stock.period,'%Y-%m')"] = $period;
        $condi_deadline['Stock.layer_code'] = $layer_code;
        $deadline_date = "";

        $date_foremail = $this->Stock->find('all', array(
            'conditions' => $condi_deadline,
            'fields' => array(
                'date_format(Stock.deadline_date,"%Y-%m-%d") as deadline_date'
            )
        ));

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

            for ($i = 0; $i < $count; $i++) {
                $save_id = $save_cmt[$i][0];
                $save_com = $save_cmt[$i][1];
                $save_status = $save_cmt[$i][2];

                for ($j = 0; $j < $cnt_rsl; $j++) {
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
            $sapDB = $this->Stock->getDataSource();
            $BacDB = $this->StockBusiAdminComment->getDataSource();
            $sapDB->begin();
            $BacDB->begin();

            for ($i = 0; $i < $countSave; $i++) {
                $sap_id = $save_cmt[$i][0];
                $Comment = trim($save_cmt[$i][1]);

                $chk_status = $save_cmt[$i][2];

                $checkSapImportsModel = $this->Stock->find(
                    'all',
                    array(
                        'conditions' => array(
                            'id' => $sap_id,
                            'OR' => array(array('Stock.flag >=' => '5'), array('Stock.flag <=' => '2')),
                        )
                    )
                );

                if (empty($checkSapImportsModel)) {
                    if (!empty($sap_id)) {
                        $checkAlreadyExistBACM = $this->StockBusiAdminComment->find(
                            'all',
                            array('conditions' => array(
                                'stock_id' => $sap_id,
                                'StockBusiAdminComment.flag' => '1'
                            ))
                        );
                        if ($chk_status == 'check') {
                            $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                            if (!empty($getMatchFlag)) {
                                foreach ($getMatchFlag as $value) {
                                    $matchSapId = $value['stocks']['id'];

                                    $this->Stock->updateAll(
                                        array(
                                            "Stock.flag" => '4',
                                            "Stock.updated_date" => "'" . $date . "'",
                                            "Stock.updated_by" => "'" . $login_id . "'"
                                        ),
                                        array(
                                            "Stock.id" => $matchSapId,
                                            "Stock.flag" => '3'
                                        )
                                    );
                                }
                            } else {
                                $this->Stock->updateAll(
                                    array(
                                        "Stock.flag" => '4',
                                        "Stock.updated_date" => "'" . $date . "'",
                                        "Stock.updated_by" => "'" . $login_id . "'"
                                    ),
                                    array(
                                        "Stock.id" => $sap_id,
                                        "Stock.flag" => '3'
                                    )
                                );
                            }

                            if (!empty($checkAlreadyExistBACM)) {
                                if (!empty($Comment)) {
                                    $Comment = $BacDB->value($Comment, 'string');

                                    $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                    if (!empty($getMatchFlag)) {
                                        foreach ($getMatchFlag as $value) {
                                            $matchSapId = $value['stocks']['id'];

                                            $this->StockBusiAdminComment->updateAll(
                                                array(
                                                    "StockBusiAdminComment.flag" => '1',
                                                    "comment" => $Comment,
                                                    "updated_date" => "'" . $date . "'",
                                                    "updated_by" => "'" . $login_id . "'"
                                                ),
                                                array(
                                                    "StockBusiAdminComment.stock_id" => $matchSapId,
                                                    'NOT' => array("StockBusiAdminComment.flag" => '0')
                                                )
                                            );
                                        }
                                    } else {
                                        $this->StockBusiAdminComment->updateAll(
                                            array(
                                                "StockBusiAdminComment.flag" => '1',
                                                "comment" => $Comment,
                                                "updated_date" => "'" . $date . "'",
                                                "updated_by" => "'" . $login_id . "'"
                                            ),
                                            array(
                                                "StockBusiAdminComment.stock_id" => $sap_id,
                                                'NOT' => array("StockBusiAdminComment.flag" => '0')
                                            )
                                        );
                                    }
                                } else {
                                    $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                    if (!empty($getMatchFlag)) {
                                        foreach ($getMatchFlag as $value) {
                                            $matchSapId = $value['stocks']['id'];

                                            $this->StockBusiAdminComment->updateAll(
                                                array(
                                                    "StockBusiAdminComment.flag" => '1',
                                                    "comment" => "''",
                                                    "updated_date" => "'" . $date . "'",
                                                    "updated_by" => "'" . $login_id . "'"
                                                ),
                                                array(
                                                    "StockBusiAdminComment.stock_id" => $matchSapId,
                                                    'NOT' => array("StockBusiAdminComment.flag" => '0')
                                                )
                                            );
                                        }
                                    } else {
                                        $this->StockBusiAdminComment->updateAll(
                                            array(
                                                "StockBusiAdminComment.flag" => '1',
                                                "comment" => "''",
                                                "updated_date" => "'" . $date . "'",
                                                "updated_by" => "'" . $login_id . "'"
                                            ),
                                            array(
                                                "StockBusiAdminComment.stock_id" => $sap_id,
                                                'NOT' => array("StockBusiAdminComment.flag" => '0')
                                            )
                                        );
                                    }
                                }
                            } else {
                                if (!empty($Comment)) {
                                    $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                    if (!empty($getMatchFlag)) {
                                        foreach ($getMatchFlag as $value) {
                                            $matchSapId = $value['stocks']['id'];

                                            $this->StockBusiAdminComment->create();
                                            $this->StockBusiAdminComment->save(array(
                                                'stock_id' => $matchSapId,
                                                'comment' => $Comment,
                                                'flag' => '1',
                                                'created_by' => $login_id,
                                                'updated_by' => $login_id,
                                                'created_date' => $date,
                                                'updated_date' => $date
                                            ));
                                        }
                                    } else {
                                        $this->StockBusiAdminComment->create();
                                        $this->StockBusiAdminComment->save(array(
                                            'stock_id' => $sap_id,
                                            'comment' => $Comment,
                                            'flag' => '1',
                                            'created_by' => $login_id,
                                            'updated_by' => $login_id,
                                            'created_date' => $date,
                                            'updated_date' => $date
                                        ));
                                    }
                                }
                            }
                        } else {
                            $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                            if (!empty($getMatchFlag)) {
                                foreach ($getMatchFlag as $value) {
                                    $matchSapId = $value['stocks']['id'];

                                    // change flag 3 at tbl_m_sap.
                                    $this->Stock->updateAll(
                                        array(
                                            "Stock.flag" => '3',
                                            "Stock.updated_date" => "'" . $date . "'",
                                            "Stock.updated_by" => "'" . $login_id . "'"
                                        ),
                                        array(
                                            "Stock.id" => $matchSapId,
                                            "Stock.flag" => '4'
                                        )
                                    );
                                }
                            } else {
                                // change flag 3 at tbl_m_sap.
                                $this->Stock->updateAll(
                                    array(
                                        "Stock.flag" => '3',
                                        "Stock.updated_date" => "'" . $date . "'",
                                        "Stock.updated_by" => "'" . $login_id . "'"
                                    ),
                                    array(
                                        "Stock.id" => $sap_id,
                                        "Stock.flag" => '4'
                                    )
                                );
                            }


                            if (!empty($checkAlreadyExistBACM)) {
                                if (!empty($Comment)) {
                                    $Comment = $BacDB->value($Comment, 'string');

                                    $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                    if (!empty($getMatchFlag)) {
                                        foreach ($getMatchFlag as $value) {
                                            $matchSapId = $value['stocks']['id'];

                                            $this->StockBusiAdminComment->updateAll(
                                                array(
                                                    "flag" => '1',
                                                    "comment" => $Comment,
                                                    "updated_date" => "'" . $date . "'",
                                                    "updated_by" => "'" . $login_id . "'"
                                                ),
                                                array(
                                                    "StockBusiAdminComment.stock_id" => $matchSapId,
                                                    'NOT' => array("StockBusiAdminComment.flag" => '0')
                                                )
                                            );
                                        }
                                    } else {
                                        $this->StockBusiAdminComment->updateAll(
                                            array(
                                                "flag" => '1',
                                                "comment" => $Comment,
                                                "updated_date" => "'" . $date . "'",
                                                "updated_by" => "'" . $login_id . "'"
                                            ),
                                            array(
                                                "StockBusiAdminComment.stock_id" => $sap_id,
                                                'NOT' => array("StockBusiAdminComment.flag" => '0')
                                            )
                                        );
                                    }
                                } else {
                                    $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                    if (!empty($getMatchFlag)) {
                                        foreach ($getMatchFlag as $value) {
                                            $matchSapId = $value['stocks']['id'];
                                            $this->StockBusiAdminComment->updateAll(
                                                array(
                                                    "flag" => '0',
                                                    "comment" => "''",
                                                    "updated_date" => "'" . $date . "'",
                                                    "updated_by" => "'" . $login_id . "'"
                                                ),
                                                array(
                                                    "StockBusiAdminComment.stock_id" => $matchSapId,
                                                    'NOT' => array("StockBusiAdminComment.flag" => '0')
                                                )
                                            );
                                        }
                                    } else {
                                        $this->StockBusiAdminComment->updateAll(
                                            array(
                                                "flag" => '0',
                                                "comment" => "''",
                                                "updated_date" => "'" . $date . "'",
                                                "updated_by" => "'" . $login_id . "'"
                                            ),
                                            array(
                                                "StockBusiAdminComment.stock_id" => $sap_id,
                                                'NOT' => array("StockBusiAdminComment.flag" => '0')
                                            )
                                        );
                                    }
                                }
                            } else {
                                if (!empty($Comment)) {
                                    $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                    if (!empty($getMatchFlag)) {
                                        foreach ($getMatchFlag as $value) {
                                            $matchSapId = $value['stocks']['id'];

                                            $this->StockBusiAdminComment->create();
                                            $this->StockBusiAdminComment->save(array(
                                                'stock_id' => $matchSapId,
                                                'comment' => $Comment,
                                                'flag' => '1',
                                                'created_by' => $login_id,
                                                'updated_by' => $login_id,
                                                'created_date' => $date,
                                                'updated_date' => $date
                                            ));
                                        }
                                    } else {
                                        $this->StockBusiAdminComment->create();
                                        $this->StockBusiAdminComment->save(array(
                                            'stock_id' => $sap_id,
                                            'comment' => $Comment,
                                            'flag' => '1',
                                            'created_by' => $login_id,
                                            'updated_by' => $login_id,
                                            'created_date' => $date,
                                            'updated_date' => $date
                                        ));
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $errorFlag = $checkSapImportsModel[0]['Stock']['flag'];

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
                $condition['Stock.flag != '] = '0';
                $condition['Stock.flag > '] = '1';
                $condition["date_format(Stock.period,'%Y-%m')"] = $period;
                $condition['Stock.layer_code'] = $layer_code;


                $count = $this->Stock->find('all', array(
                    'conditions' => $condition,
                    'group' => array(
                        'Stock.layer_code',
                        'Stock.destination_name',
                        'Stock.period'
                    )

                ));

                //count for flag 4 column

                $countSapAddComments = count($count);

                $condi = array();
                $condi['Stock.flag >='] = '4';
                $condi["date_format(Stock.period,'%Y-%m')"] = $period;
                $condi['Stock.layer_code'] = $layer_code;
                $chkSapAddComments = $this->Stock->find('all', array(
                    'conditions' => $condi,
                    'group' => array(
                        'Stock.layer_code',
                        'Stock.destination_name',
                        'Stock.period'
                    )
                ));

                $countChkSapAddComments = count($chkSapAddComments);

                # check or uncheck all checkbox
                if ($countSapAddComments == $countChkSapAddComments) {

                    if ($_POST['mailSend']) {

                        if ($this->sendEmailP3StockAddComments()) {

                            $sapDB->commit();
                            $BacDB->commit();
                            $successMsg = parent::getSuccessMsg("SS008");
                            $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                            $successMsg = parent::getSuccessMsg("SS018");
                            $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                        }
                    } else {
                        $sapDB->commit();
                        $BacDB->commit();
                        $successMsg = parent::getSuccessMsg("SS008");
                        $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                    }
                } else {
                    $sapDB->commit();
                    $BacDB->commit();
                    $successMsg = parent::getSuccessMsg("SS008");
                    $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                    $data = array(
                        'content' => $successMsg,
                        'invalid' => "",
                        'error'   => ""
                    );
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE033');
                $this->Flash->set($errorMsg, array('key' => 'saveError'));
                $data = array(
                    'content' => "",
                    'invalid' => "",
                    'error'   => $errorMsg
                );
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $sapDB->rollback();
            $BacDB->rollback();
            $errorMsg = parent::getErrorMsg("SE011", [__("依頼")]);
            $errorMsg .= " " . $invalid_email;

            $this->Flash->set($errorMsg, array('key' => 'saveError'));
            $data = array(
                'content' => "",
                'invalid' => "",
                'error'   => $errorMsg
            );
        }
        if (!empty($search_query)) {
            if ($pageno > 1) {
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'searchSaleRepresentative', 'page' => $pageno, '?' => $search_query));
            } else
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'searchSaleRepresentative', '?' => $search_query));
        } else {
            if ($pageno > 1) {
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index', 'page' => $pageno));
            } else
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
        }
    }

    public function ApproveStockAddComments()
    {
        $Common = new CommonController();
        $errorMsg = "";
        $successMsg = "";
        $login_id = $this->Session->read('LOGIN_ID');
        $period   = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code  = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $date = date('Y-m-d H:i:s');
        $invalid_email = '';

        # added for submission deadline in mail body (by khin hnin myo)
        $condi_deadline = array();
        $condi_deadline['Stock.flag >='] = '2';
        $condi_deadline["date_format(Stock.period,'%Y-%m')"] = $period;
        $condi_deadline['Stock.layer_code'] = $layer_code;
        $deadline_date = "";

        $date_foremail = $this->Stock->find('all', array(
            'conditions' => $condi_deadline,
            'fields' => array(
                'date_format(Stock.deadline_date,"%Y-%m-%d") as deadline_date'
            )
        ));

        if (!empty($date_foremail)) {
            foreach ($date_foremail as $value) {
                $deadline_date = $value[0]['deadline_date'];
            }
        }

        $SID_ARRAY = $this->Session->read('STOCK_ARRAY');
        $this->Session->delete('STOCK_ARRAY');

        $sapDB = $this->Stock->getDataSource();
        $BmaDB = $this->StockBusiManagerApprove->getDataSource();

        $sapDB->begin();
        $BmaDB->begin();

        $getBACode = $this->Stock->find(
            'all',
            array(
                'conditions' => array(
                    'Stock.layer_code' => $layer_code,
                    'date_format(Stock.period,"%Y-%m")' => $period,
                    'OR' => array(
                        array('Stock.flag' => '2'),
                        array('Stock.flag' => '3')
                    )
                ),
                'fields' => array(
                    'Stock.layer_code',
                    'Stock.id',
                    'Stock.flag'
                )
            )
        );

        try {
            if (!empty($getBACode)) {
                $errorMsg = parent::getErrorMsg('SE040');
                $this->Flash->set($errorMsg, array('key' => 'saveError'));
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
            } else {
                if (!empty($SID_ARRAY)) {
                    foreach ($SID_ARRAY as $value) {

                        // change flag 5 at tbl_m_sap.
                        $this->Stock->updateAll(
                            array(
                                "Stock.flag" => '5',
                                "Stock.updated_date" => "'" . $date . "'",
                                "Stock.updated_by" => "'" . $login_id . "'"
                            ),
                            array(
                                "Stock.id" => $value,
                                "Stock.flag" => '4'
                            )
                        );

                        $this->StockBusiManagerApprove->create();
                        $this->StockBusiManagerApprove->saveAll(array(
                            'stock_id' => $value,
                            'approve_date' => date('Y-m-d'),
                            'flag' => '1',
                            'created_by' => $login_id,
                            'updated_by' => $login_id
                        ));
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE018');
                    $this->Flash->set($errorMsg, array('key' => 'saveError'));
                    $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
                }
            }
            if ($_POST['mailSend']) {

                if ($this->sendEmailP3StockAddComments()) {
                    $sapDB->commit();
                    $BmaDB->commit();
                    $successMsg = parent::getSuccessMsg("SS005");
                    $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                    $successMsg = parent::getSuccessMsg("SS018");
                    $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                }
            } else {
                $sapDB->commit();
                $BmaDB->commit();
                $successMsg = parent::getSuccessMsg("SS005");
                $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
            }
        } catch (Exception $e) {

            $sapDB->rollback();
            $BmaDB->rollback();
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $msg = parent::getErrorMsg("SE011", [__("依頼")]);
            $msg .= " " . $invalid_email;
            $this->Flash->set($msg, array('key' => 'saveError'));
            $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
        }

        $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
    }

    public function ApproveCancelStockAddComments()
    {

        $errorMsg = "";
        $successMsg = "";
        $login_id   = $this->Session->read('LOGIN_ID');
        $period     = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code    = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name    = $this->Session->read('StockSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $date = date('Y-m-d H:i:s');
        $invalid_email = '';

        # added for submission deadline in mail body (by khin hnin myo)
        $condi_deadline = array();
        $condi_deadline['Stock.flag >='] = '2';
        $condi_deadline["date_format(Stock.period,'%Y-%m')"] = $period;
        $condi_deadline['Stock.layer_code'] = $layer_code;
        $deadline_date = "";

        $date_foremail = $this->Stock->find('all', array(
            'conditions' => $condi_deadline,
            'fields' => array(
                'date_format(Stock.deadline_date,"%Y-%m-%d") as deadline_date'
            )
        ));

        if (!empty($date_foremail)) {
            foreach ($date_foremail as $value) {
                $deadline_date = $value[0]['deadline_date'];
            }
        }

        $stock_array_cancel = $this->Session->read('STOCK_ARRAY_CANCEL');
        $this->Session->delete('STOCK_ARRAY_CANCEL');

        $sapDB = $this->Stock->getDataSource();
        $BmaDB = $this->StockBusiManagerApprove->getDataSource();

        try {
            $sapDB->begin();
            $BmaDB->begin();
            //change flag 0 at SapBusiManagerApprove.

            if (!empty($stock_array_cancel)) {
                foreach ($stock_array_cancel as $value) {
                    // select flag is not 5, to show error msg.
                    $checkSapImportsModel = $this->Stock->find(
                        'all',
                        array('conditions' => array(
                            'id' => $value,
                            'NOT' => array('flag' => '5')
                        ))
                    );

                    if (!empty($checkSapImportsModel)) {
                        $errorMsg = parent::getErrorMsg('SE036');
                        $this->Flash->set($errorMsg, array('key' => 'saveError'));
                        $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
                    }
                }

                foreach ($stock_array_cancel as $value) {
                    $this->StockBusiManagerApprove->updateAll(
                        array(
                            "StockBusiManagerApprove.flag" => '0',
                            "StockBusiManagerApprove.updated_by" => "'" . $login_id . "'"
                        ),
                        array("StockBusiManagerApprove.stock_id" => $value)
                    );

                    // change (flag 4 at tbl_m_sap not use). new flow change flag 3
                    $this->Stock->updateAll(
                        array(
                            "Stock.flag" => '3',
                            "Stock.updated_date" => "'" . $date . "'",
                            "Stock.updated_by" => "'" . $login_id . "'"
                        ),
                        array(
                            "Stock.id" => $value,
                            "Stock.flag" => '5'
                        )
                    );
                }
                if ($_POST['mailSend']) {

                    if ($this->sendEmailP3StockAddComments()) {

                        $sapDB->commit();
                        $BmaDB->commit();
                        $successMsg = parent::getSuccessMsg("SS006", [__("正常")]);
                        $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                        $successMsg = parent::getSuccessMsg("SS018");
                        $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                    }
                } else {

                    $sapDB->commit();
                    $BmaDB->commit();
                    $successMsg = parent::getSuccessMsg("SS006", [__("正常")]);
                    $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE039');
                $this->Flash->set($errorMsg, array('key' => 'saveError'));
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
            }
        } catch (Exception $e) {
            $sapDB->rollback();
            $BmaDB->rollback();
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $msg = parent::getErrorMsg("SE011", [__("依頼")]);
            $msg .= " " . $invalid_email;
            $this->Flash->set($msg, array('key' => 'saveError'));
            $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
        }

        $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
    }
    //added by Hein Htet Ko
    public function RejectStockAddComments()
    {
        $errorMsg   = "";
        $successMsg = "";
        $date       = date('Y-m-d H:i:s');
        $login_id   = $this->Session->read('LOGIN_ID');
        $period     = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code    = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name    = $this->Session->read('StockSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');
        $invalid_email = '';

        # added for submission deadline in mail body (by khin hnin myo)
        $condi_deadline = array();
        $condi_deadline['Stock.flag >='] = '2';
        $condi_deadline["date_format(Stock.period,'%Y-%m')"] = $period;
        $condi_deadline['Stock.layer_code'] = $layer_code;
        $deadline_date = "";

        $date_foremail = $this->Stock->find('all', array(
            'conditions' => $condi_deadline,
            'fields' => array(
                'date_format(Stock.deadline_date,"%Y-%m-%d") as deadline_date'
            )
        ));

        if (!empty($date_foremail)) {
            foreach ($date_foremail as $value) {
                $deadline_date = $value[0]['deadline_date'];
            }
        }

        $stock_array_reject = $this->Session->read('STOCK_ARRAY_REJECT');

        $this->Session->delete('STOCK_ARRAY_REJECT');


        $sapDB = $this->Stock->getDataSource();
        $BmaDB = $this->StockBusiManagerApprove->getDataSource();
        try {
            $sapDB->begin();
            $BmaDB->begin();
            //change flag 0 at SapBusiManagerApprove.

            if (!empty($stock_array_reject)) {
                foreach ($stock_array_reject as $value) {
                    // select flag is not 4, to show error msg.
                    $checkSapImportsModel = $this->Stock->find(
                        'all',
                        array('conditions' => array(
                            'id' => $value,
                            'NOT' => array('flag' => '4')
                        ))
                    );

                    if (!empty($checkSapImportsModel)) {
                        $errorMsg = parent::getErrorMsg('SE036');
                        $this->Flash->set($errorMsg, array('key' => 'saveError'));
                        $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
                    }
                }

                foreach ($stock_array_reject as $value) {
                    $this->StockBusiManagerApprove->updateAll(
                        array(
                            "StockBusiManagerApprove.flag" => '0',
                            "StockBusiManagerApprove.updated_by" => "'" . $login_id . "'"
                        ),
                        array("StockBusiManagerApprove.stock_id" => $value)
                    );

                    // change (flag 4 at tbl_m_sap not use). new flow change flag 3
                    $this->Stock->updateAll(
                        array(
                            "Stock.flag" => '3',
                            "Stock.updated_date" => "'" . $date . "'",
                            "Stock.updated_by" => "'" . $login_id . "'"
                        ),
                        array(
                            "Stock.id" => $value,
                            "Stock.flag" => '4'
                        )
                    );
                }
                if ($_POST['mailSend']) {

                    if ($this->sendEmailP3StockAddComments()) {

                        $sapDB->commit();
                        $BmaDB->commit();
                        $successMsg = parent::getSuccessMsg("SS014");
                        $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                        $successMsg = parent::getSuccessMsg("SS018");
                        $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                    }
                } else {
                    $sapDB->commit();
                    $BmaDB->commit();
                    $successMsg = parent::getSuccessMsg("SS014");
                    $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE039');
                $this->Flash->set($errorMsg, array('key' => 'saveError'));
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
            }
        } catch (Exception $e) {
            $sapDB->rollback();
            $BmaDB->rollback();
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $msg = parent::getErrorMsg("SE011", [__("依頼")]);
            $msg .= " " . $invalid_email;
            $this->Flash->set($msg, array('key' => 'saveError'));
            $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
        }

        $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
    }


    public function ReviewStockAddComments()
    {

        $login_id = $this->Session->read('LOGIN_ID'); //get login id
        $period   = $this->Session->read('StockSelections_PERIOD_DATE');

        $login_user_name = $this->Session->read('LOGIN_USER');
        $pageno = $this->Session->read('Page.pageCount');
        $search_query = $this->Session->read('SEARCH_QUERY');
        $this->Session->delete('SEARCH_QUERY');
        $date = date('Y-m-d H:i:s');

        $data_array = $this->request->data('json_data');
        $save_cmt = json_decode($data_array);

        try {
            $sapDB = $this->Stock->getDataSource();
            $AicDB = $this->StockAccInchargeComment->getDataSource();
            $sapDB->begin();
            $AicDB->begin();

            for ($i = 0; $i < count($save_cmt); $i++) {
                $sap_id = $save_cmt[$i][0];
                $Comment = trim($save_cmt[$i][1]);

                $chk_status = $save_cmt[$i][2];

                if (!empty($sap_id)) {
                    $checkAlreadyHasAICM = $this->StockAccInchargeComment->find(
                        'all',
                        array('conditions' => array(
                            'stock_id' => $sap_id,
                            'StockAccInchargeComment.flag' => '1'
                        ))
                    );

                    $chk_M_Sap = $this->Stock->find(
                        'all',
                        array('conditions' => array('id' => $sap_id))
                    );

                    $chkSapFlag = "";
                    if (!empty($chk_M_Sap)) {
                        $chkSapFlag = $chk_M_Sap[0]['Stock']['flag'];
                    }

                    if ($chk_status == 'true') {
                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                        if (!empty($getMatchFlag)) {
                            foreach ($getMatchFlag as $value) {
                                $matchSapId = $value['stocks']['id'];

                                //change flag 6 at tbl_m_sap.
                                $this->Stock->updateAll(
                                    array(
                                        "Stock.flag" => '6',
                                        "Stock.updated_by" => "'" . $login_id . "'",
                                        "Stock.updated_date" => "'" . $date . "'"
                                    ),
                                    array(
                                        "Stock.id" => $matchSapId,
                                        'OR' => array(
                                            array("Stock.flag" => '5'),
                                            array("Stock.flag" => '6')
                                        )
                                    )
                                );
                            }
                        } else {

                            //change flag 6 at tbl_m_sap.
                            $this->Stock-> updateAll(
                                array(
                                    "Stock.flag" => '6',
                                    "Stock.updated_by" => "'" . $login_id . "'",
                                    "Stock.updated_date" => "'" . $date . "'"
                                ),
                                array(
                                    "Stock.id" => $sap_id,
                                    'OR' => array(
                                        array("Stock.flag" => '5'),
                                        array("Stock.flag" => '6')
                                    )
                                )
                            );
                        }

                        if (!empty($checkAlreadyHasAICM)) {
                            if (!empty($Comment)) {
                                // when page is not reflax(flag is >=6), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '5' || $chkSapFlag == "6") {
                                        $Comment = $AicDB->value($Comment, 'string');

                                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                        if (!empty($getMatchFlag)) {
                                            foreach ($getMatchFlag as $value) {
                                                $matchSapId = $value['stocks']['id'];

                                                $this->StockAccInchargeComment->updateAll(
                                                    array(
                                                        "StockAccInchargeComment.flag" => '1',
                                                        "comment" => $Comment,
                                                        "updated_date" => "'" . $date . "'",
                                                        "updated_by" => "'" . $login_id . "'"
                                                    ),
                                                    array(
                                                        "stock_id" => $matchSapId,
                                                        'NOT' => array("StockAccInchargeComment.flag" => '0')
                                                    )
                                                );
                                            }
                                        } else {
                                            $this->StockAccInchargeComment->updateAll(
                                                array(
                                                    "StockAccInchargeComment.flag" => '1',
                                                    "comment" => $Comment,
                                                    "updated_date" => "'" . $date . "'",
                                                    "updated_by" => "'" . $login_id . "'"
                                                ),
                                                array(
                                                    "stock_id" => $sap_id,
                                                    'NOT' => array("StockAccInchargeComment.flag" => '0')
                                                )
                                            );
                                        }
                                    }
                                }
                            } else {
                                // when page is not reflax(flag is >=6), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '5' || $chkSapFlag == "6") {
                                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                        if (!empty($getMatchFlag)) {
                                            foreach ($getMatchFlag as $value) {
                                                $matchSapId = $value['stocks']['id'];

                                                $this->StockAccInchargeComment->updateAll(
                                                    array(
                                                        "StockAccInchargeComment.flag" => '1',
                                                        "comment" => "''",
                                                        "updated_date" => "'" . $date . "'",
                                                        "updated_by" => "'" . $login_id . "'"
                                                    ),
                                                    array(
                                                        "stock_id" => $matchSapId,
                                                        'NOT' => array("StockAccInchargeComment.flag" => '0')
                                                    )
                                                );
                                            }
                                        } else {
                                            $this->StockAccInchargeComment->updateAll(
                                                array(
                                                    "StockAccInchargeComment.flag" => '1',
                                                    "comment" => "''",
                                                    "updated_date" => "'" . $date . "'",
                                                    "updated_by" => "'" . $login_id . "'"
                                                ),
                                                array(
                                                    "stock_id" => $sap_id,
                                                    'NOT' => array("StockAccInchargeComment.flag" => '0')
                                                )
                                            );
                                        }
                                    }
                                }
                            }
                        } else {
                            if (!empty($Comment)) {
                                // when page is not reflax(flag is >=6), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '5' || $chkSapFlag == "6") {
                                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                        if (!empty($getMatchFlag)) {
                                            foreach ($getMatchFlag as $value) {
                                                $matchSapId = $value['stocks']['id'];

                                                $this->StockAccInchargeComment->create();
                                                $this->StockAccInchargeComment->save(array(
                                                    'stock_id' => $matchSapId,
                                                    'comment' => $Comment,
                                                    'flag' => '1',
                                                    'created_by' => $login_id,
                                                    'updated_by' => $login_id,
                                                    'created_date' => $date,
                                                    'updated_date' => $date
                                                ));
                                            }
                                        } else {
                                            $this->StockAccInchargeComment->create();
                                            $this->StockAccInchargeComment->save(array(
                                                'stock_id' => $sap_id,
                                                'comment' => $Comment,
                                                'flag' => '1',
                                                'created_by' => $login_id,
                                                'updated_by' => $login_id,
                                                'created_date' => $date,
                                                'updated_date' => $date
                                            ));
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                        if (!empty($getMatchFlag)) {
                            foreach ($getMatchFlag as $value) {
                                $matchSapId = $value['stocks']['id'];

                                //change flag 5 at tbl_m_sap.
                                $this->Stock->updateAll(
                                    array(
                                        "Stock.flag" => '5',
                                        "Stock.updated_by" => "'" . $login_id . "'",
                                        "Stock.updated_date" => "'" . $date . "'"
                                    ),
                                    array(
                                        "Stock.id" => $matchSapId,
                                        'OR' => array(
                                            array("Stock.flag" => '5'),
                                            array("Stock.flag" => '6')
                                        )
                                    )
                                );
                            }
                        } else {

                            // change flag 5 at tbl_m_sap.
                            $this->Stock->updateAll(
                                array(
                                    "Stock.flag" => '5',
                                    "Stock.updated_by" => "'" . $login_id . "'",
                                    "Stock.updated_date" => "'" . $date . "'"
                                ),
                                array(
                                    "Stock.id" => $sap_id,
                                    'OR' => array(
                                        array("Stock.flag" => '5'),
                                        array("Stock.flag" => '6')
                                    )
                                )
                            );
                        }

                        if (!empty($checkAlreadyHasAICM)) {
                            if (!empty($Comment)) {
                                // when page is not reflax(flag is >=6), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '5' || $chkSapFlag == "6") {
                                        $Comment = $AicDB->value($Comment, 'string');

                                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                        if (!empty($getMatchFlag)) {
                                            foreach ($getMatchFlag as $value) {
                                                $matchSapId = $value['stocks']['id'];

                                                $this->StockAccInchargeComment->updateAll(
                                                    array(
                                                        "StockAccInchargeComment.flag" => '1',
                                                        "comment" => $Comment,
                                                        "updated_date" => "'" . $date . "'",
                                                        "updated_by" => "'" . $login_id . "'"
                                                    ),
                                                    array(
                                                        "stock_id" => $matchSapId,
                                                        'NOT' => array("StockAccInchargeComment.flag" => '0')
                                                    )
                                                );
                                            }
                                        } else {
                                            $this->StockAccInchargeComment->updateAll(
                                                array(
                                                    "StockAccInchargeComment.flag" => '1',
                                                    "comment" => $Comment,
                                                    "updated_date" => "'" . $date . "'",
                                                    "updated_by" => "'" . $login_id . "'"
                                                ),
                                                array(
                                                    "stock_id" => $sap_id,
                                                    'NOT' => array("StockAccInchargeComment.flag" => '0')
                                                )
                                            );
                                        }
                                    }
                                }
                            } else {

                                // when page is not reflax(flag is >=6), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '5' || $chkSapFlag == "6") {
                                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                        if (!empty($getMatchFlag)) {
                                            foreach ($getMatchFlag as $value) {
                                                $matchSapId = $value['stocks']['id'];

                                                $this->StockAccInchargeComment->updateAll(
                                                    array(
                                                        "StockAccInchargeComment.flag" => '1',
                                                        "comment" => "''",
                                                        "updated_date" => "'" . $date . "'",
                                                        "updated_by" => "'" . $login_id . "'"
                                                    ),
                                                    array(
                                                        "stock_id" => $matchSapId,
                                                        'NOT' => array("StockAccInchargeComment.flag" => '0')
                                                    )
                                                );
                                            }
                                        } else {
                                            $this->StockAccInchargeComment->updateAll(
                                                array(
                                                    "StockAccInchargeComment.flag" => '1',
                                                    "comment" => "''",
                                                    "updated_date" => "'" . $date . "'",
                                                    "updated_by" => "'" . $login_id . "'"
                                                ),
                                                array(
                                                    "stock_id" => $sap_id,
                                                    'NOT' => array("StockAccInchargeComment.flag" => '0')
                                                )
                                            );
                                        }
                                    }
                                }
                            }
                        } else {
                            if (!empty($Comment)) {
                                // when page is not reflax(flag is >=6), cmt can't update.
                                if (!empty($chkSapFlag)) {
                                    if ($chkSapFlag == '5' || $chkSapFlag == "6") {
                                        $getMatchFlag = $this->Stock->getMatchFlag($sap_id);

                                        if (!empty($getMatchFlag)) {
                                            foreach ($getMatchFlag as $value) {
                                                $matchSapId = $value['stocks']['id'];

                                                $this->StockAccInchargeComment->create();
                                                $this->StockAccInchargeComment->save(array(
                                                    'stock_id' => $matchSapId,
                                                    'comment' => $Comment,
                                                    'flag' => '1',
                                                    'created_by' => $login_id,
                                                    'updated_by' => $login_id,
                                                    'created_date' => $date,
                                                    'updated_date' => $date
                                                ));
                                            }
                                        } else {
                                            $this->StockAccInchargeComment->create();
                                            $this->StockAccInchargeComment->save(array(
                                                'stock_id' => $sap_id,
                                                'comment' => $Comment,
                                                'flag' => '1',
                                                'created_by' => $login_id,
                                                'updated_by' => $login_id,
                                                'created_date' => $date,
                                                'updated_date' => $date
                                            ));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($_POST['mailSend']) {

                if ($this->sendEmailP3StockAddComments()) {

                    $sapDB->commit();
                    $AicDB->commit();

                    $successMsg = parent::getSuccessMsg("SS008");
                    $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                    $successMsg = parent::getSuccessMsg("SS018");
                    $this->Flash->set($successMsg, array('key' => 'saveSuccess'));

                    // $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index'));
                }
            } else {
                $sapDB->commit();
                $AicDB->commit();

                $successMsg = parent::getSuccessMsg("SS008");
                $this->Flash->set($successMsg, array('key' => 'saveSuccess'));
                // $this->redirect(array('controller'=>'SapAddComments', 'action'=>'index'));
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $sapDB->rollback();
            $AicDB->rollback();

            $errorMsg = parent::getErrorMsg('SE003');
            $this->Flash->set($errorMsg, array('key' => 'saveError'));
            $data = array(
                'content' => "",
                'invalid' => "",
                'error'   => ""
            );
            $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
        }
        if (!empty($search_query)) {
            if ($pageno > 1) {
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'searchSaleRepresentative', 'page' => $pageno, '?' => $search_query));
            } else
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'searchSaleRepresentative', '?' => $search_query));
        } else {
            if ($pageno > 1) {
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index', 'page' => $pageno));
            } else
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
        }
    }

    public function searchSaleRepresentative()
    {
        $errorMsg = "";
        $successMsg = "";
        $this->layout = 'stocks';
        $Common = new CommonController;
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');

        $flag_list  = Setting::ADDCMT_FLAG;

        $data['role_id']        = $role_id;
        $data['period']         = $period;
        $data['layer_code']     = $layer_code;
        $data['page']           = $this->request->params['controller'];
        $data['permission']     = $permission;
        $data['flag_list']      = $flag_list;
        $data['modelName']      = 'Stock';
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
            if ((($layer_code == '' && $permission['limit'] == 0) || in_array($layer_code, array_keys($permission['layers'])))) {
                $checkButtonType[$action] = true;
            }
        }
        $condi = array();
        if (($period != null || $period != '')) {
            $role_id = $this->Session->read('ADMIN_LEVEL_ID');

            $conditions = array();
            $conditions['Stock.flag > '] = '1';
            $conditions["date_format(Stock.period,'%Y-%m')"] = $period;

            if ($layer_code != null || $layer_code != '') {
                $conditions['Stock.layer_code'] = $layer_code;
                $condi["Stock.layer_code"] = $layer_code;
            }

            $logistics = trim($this->request->query('receipt')); //search logistics_no by Khin Hnin Myo

            $logistics = str_replace(array('\\', '_', '%'), array('\\\\', '\\_', '\\%'), $logistics);

            $conditions['Stock.receipt_index_no LIKE'] = "%" . $logistics . "%";

            try {
                $this->paginate = array(
                    'limit' => Paging::TABLE_PAGING,
                    'conditions' => $conditions,
                    'joins' => array(
                        array(
                            'alias'      => 'StockBusiInchargeComment',
                            'table'      => 'stock_busi_incharge_comments',
                            'type'      => 'Left',
                            'conditions' => 'Stock.id = StockBusiInchargeComment.stock_id AND StockBusiInchargeComment.flag <> 0'
                        ),
                        array(
                            'alias'      => 'StockBusiAdminComment',
                            'table'      => 'stock_busi_admin_comments',
                            'type'      => 'Left',
                            'conditions' => 'Stock.id = StockBusiAdminComment.stock_id AND StockBusiAdminComment.flag <> 0'
                        ),
                        array(
                            'alias'      => 'StockAccInchargeComment',
                            'table'      => 'stock_acc_incharge_comments',
                            'type'      => 'Left',
                            'conditions' => 'Stock.id = StockAccInchargeComment.stock_id AND StockAccInchargeComment.flag <> 0'
                        )

                    ),
                    'fields' => array(
                        'Stock.id',
                        'Stock.layer_code',
                        'Stock.destination_name',
                        'Stock.item_code',
                        'Stock.item_name',
                        'Stock.item_name_2',
                        'Stock.unit',
                        "DATE_FORMAT(Stock.registration_date,'%Y-%m-%d') AS stock_registration_date",
                        'Stock.numbers_day',
                        'Stock.receipt_index_no',
                        'Stock.quantity',
                        'SUM(Stock.amount) AS amount',
                        'Stock.is_error',
                        'Stock.is_sold',
                        'Stock.is_contract',
                        'Stock.preview_comment',
                        // 'Stock.solution',
                        'Stock.flag',
                        'StockBusiInchargeComment.reason',
                        'StockBusiInchargeComment.settlement_date',
                        'StockBusiInchargeComment.remark',
                        'StockBusiInchargeComment.stock_id',
                        'StockBusiAdminComment.comment',
                        'StockBusiAdminComment.stock_id',
                        'StockAccInchargeComment.comment',
                        'StockAccInchargeComment.stock_id'

                    ),

                    'group' => array(
                        'Stock.layer_code',
                        'Stock.destination_name',
                        'Stock.period',
                        // 'Stock.item_code',
                        // 'Stock.item_name',
                        // 'Stock.posting_date',
                        // 'Stock.recorded_date',
                        // 'Stock.schedule_date',
                        // 'Stock.currency'
                    ),

                    'order' => array(

                        'Stock.id' => 'ASC',
                        'Stock.layer_code' => 'ASC',
                        'Stock.destination_name' => 'ASC',
                        // 'Stock.item_code' => 'ASC',
                        // 'Stock.item_name' => 'ASC',
                        // 'Stock.posting_date' => 'ASC',
                        // 'Stock.recorded_date' => 'ASC',
                        // 'Stock.schedule_date' => 'ASC'

                    )
                );

                $page = $this->Paginator->paginate('Stock');

                $count = $this->params['paging']['Stock']['count'];
                $pageno = $this->params['paging']['Stock']['page'];
                $this->Session->write('Page.pageCount', $pageno);
                if ($count != 0) {
                    $this->set('succCount', parent::getSuccessMsg('SS004', $count));
                } else {
                    $this->set('errCount', parent::getErrorMsg('SE001'));
                }
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage() . ' in file ' . _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
            }

            //for level5
            $fApprove = 0;
            $CancelApprove = 0;
            $stock_array_approve = array();
            $stock_array_cancel = array();
            // select all data but not flag != 0 and layer_code
            $ForApprove1 = $this->Stock->find(
                'all',
                array('conditions' => array(
                    "date_format(Stock.period,'%Y-%m')" => $period,
                    "Stock.layer_code" => $layer_code,
                    'NOT' => array(
                        array('flag' => '0'),
                        array('flag' => '1')
                    )
                ))
            );

            //check selected all data, if(flag == 4)?
            if (!empty($ForApprove1)) {
                foreach ($ForApprove1 as $value) {
                    $toApproveFlag = $value['Stock']['flag'];
                    $sid = $value['Stock']['id'];
                    if ($toApproveFlag == '4') {
                        $stock_array_approve[] = $sid;
                        $fApprove++;
                    } elseif ($toApproveFlag == '5') {
                        $stock_array_cancel[] = $sid;
                        $CancelApprove++;
                    } else {
                        break;
                    }
                }
            }

            $countApprove1 = count($ForApprove1);
            $stockArrApproveCount = count($stock_array_approve);
            $stockArrCancelCount  = count($stock_array_cancel);

            if ($countApprove1 == $stockArrApproveCount) {
                $this->Session->write('STOCK_ARRAY', $stock_array_approve);
            } elseif ($countApprove1 == $stockArrCancelCount) {
                $this->Session->write('STOCK_ARRAY_CANCEL', $stock_array_cancel);
            }

            //get name_jp

            $getBAName = $Common->getLayerThreeName($layer_code, date('Y-m-d', strtotime($period)));
            $BAName = $getBAName['name_jp'];
            //get base_date, deadLine_date accourding to layer_code, period and max id
            $condi["date_format(Stock.period,'%Y-%m')"] = $period;
            $submission_deadline = "";
            $reference_date = "";

            $toShowDate = $this->Stock->find('all', array(
                'conditions' => $condi,
                'fields' => array(
                    'date_format(Stock.base_date,"%Y-%m-%d") as base_date',
                    'date_format(Stock.deadline_date,"%Y-%m-%d") as deadline_date',
                    'Stock.id'
                ),
                'order' => array('Stock.id DESC'),
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

            $logistics = str_replace(array('\\\\', '\\_', '\\%'), array('\\', '_', '%'), $logistics);


            $condition1 = "";
            $condition2 = "";
            if ($layer_code == "") {
                $condition1 = array(
                    'flag' => 6,
                    'period' => $period . '-01'
                );

                $condition2 = array(
                    'Stock.flag' => array(5, 6),
                    'Stock.period' => $period . '-01'

                );
            } else {
                $condition1 = array(
                    'flag' => 6,
                    'period' => $period . '-01',
                    'layer_code' => $layer_code
                );

                $condition2 = array(
                    'Stock.flag' => array(5, 6),
                    'Stock.period' => $period . '-01',
                    'Stock.layer_code' => $layer_code
                );
            }

            $flag6_count = $this->Stock->find('count', array(
                'conditions' => $condition1,
                'group' => array(
                    'layer_code',
                    'destination_name',
                    // 'item_code',
                    // 'item_name',
                    'period'
                )
            ));
            $f5and6count = $this->Stock->find('count', array(
                'conditions' => $condition2,
                'group' => array(
                    'layer_code',
                    'destination_name',
                    // 'item_code',
                    // 'item_name',
                    'period'
                )
            ));


            $flagArr = [];
            foreach ($this->paginate() as $key => $stock_value) {
                array_push($flagArr, $stock_value['Stock']['flag']);
            }

            $minFlag = min($flagArr);
            $show_btn = array();
            foreach ($flag_list as $button => $flag) {
                $show[$button] = (in_array($minFlag, $flag)) ? '1' : '';
                if (!array_diff_assoc($show, $checkButtonType)) {
                    $show_btn[$button] = (in_array($minFlag, $flag)) ? '1' : '';
                } else {
                    $show_btn[$button] = '';
                }
                $show = array();
            }
            if (!array_filter($show_btn) && count(array_filter($checkButtonType)) == 1) {
                $show_btn = $checkButtonType;
            }

            $this->set(compact('checkButtonType', 'show_btn'));
            // $this->set('searchSaleRepre', $saleRepre);
            $this->set('searchlogistics', $logistics);
            $this->set('BAName', $BAName);
            $this->set('fApprove', $fApprove); //show approve btn
            $this->set('CancelApprove', $CancelApprove); //show approve cancel btn
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
            $this->redirect(array('controller' => 'StockSelections', 'action' => 'index'));
        }
    }
    /***
     ** @author Nu Nu Lwin
     ** @Date 06.08.2019
     ** New added From customer side
     ***/
    public function Download_Add_Comment()
    {
        $period  = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $show_btn = $this->Session->read('SHOW_BTN');

        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        // $searchRepre = $this->request->data('saleRepre');
        $logistics = $this->request->data('receipt');

        // $searchRepre = str_replace(array('\\', '_', '%'), array('\\\\', '\\_', '\\%'), $searchRepre);
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
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3);
        $objPHPExcel->getActiveSheet()->setShowGridlines(true);
        $objPHPExcel->setActiveSheetIndex(0);

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
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
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
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        $alignright = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );


        $objPHPExcel->getActiveSheet()->getStyle('A:H')->getAlignment()->setWrapText(true);
        if ($show_btn['save']) {
            $sheet->getStyle('A9:T9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle('A10:T10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');

            $objPHPExcel->getActiveSheet()->getStyle('A:U')->getAlignment()->setWrapText(true);
        }
        if ($show_btn['request'] || $show_btn['reject'] || $show_btn['approve']) {
            $sheet->getStyle('A9:V9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle('A10:V10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');

            $objPHPExcel->getActiveSheet()->getStyle('A:V')->getAlignment()->setWrapText(true);
        }
        if ($show_btn['approve_cancel'] || $show_btn['review'] || count(array_filter($show_btn)) < 1) {
            $sheet->getStyle('A9:X9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle('A10:X10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');

            $objPHPExcel->getActiveSheet()->getStyle('A:X')->getAlignment()->setWrapText(true);
        }


        $sheet->getColumnDimension('A')->setVisible(false);
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension("D")->setWidth(30);
        $sheet->getColumnDimension("E")->setWidth(15);
        $sheet->getColumnDimension("F")->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(30);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(15);
        $sheet->getColumnDimension('M')->setWidth(15);
        $sheet->getColumnDimension('N')->setWidth(10);
        $sheet->getColumnDimension('O')->setWidth(20);
        $sheet->getColumnDimension('P')->setVisible(false);

        $sheet->getColumnDimension('Q')->setWidth(20);
        $sheet->getColumnDimension('R')->setWidth(20);
        $sheet->getColumnDimension('S')->setWidth(20);
        $sheet->getColumnDimension('T')->setWidth(20);
        $sheet->getColumnDimension('U')->setWidth(20);
        $sheet->getColumnDimension('V')->setWidth(20);
        $sheet->getColumnDimension('W')->setWidth(20);
        $sheet->getColumnDimension('X')->setWidth(20);


        $excel_result = $this->Stock->Add_Comment_Excel($layer_code, $period, $logistics);


        if (!empty($excel_result)) {

            // Excel  Title(Account Review)
            $sheet->setCellValue('A1', __("コメント追加"));
            $sheet->mergeCells('A1:X1');
            $sheet->getStyle('A1:X1')->applyFromArray($aligncenter);

            //write layer_code,layer_name,period,etc..
            $sheet->setCellValue('B2', __("部署"));
            $sheet->getStyle('B2')->applyFromArray($alignleft);
            $sheet->getStyle('B2')->applyFromArray($border_dash);
            $sheet->setCellValue('B3', __("部署名"));
            $sheet->getStyle('B3')->applyFromArray($alignleft);
            $sheet->getStyle('B3')->applyFromArray($border_dash);
            $sheet->setCellValue('B4', __("対象月"));
            $sheet->getStyle('B4')->applyFromArray($alignleft);
            $sheet->getStyle('B4')->applyFromArray($border_dash);
            $sheet->setCellValue('B5', __("基準年月日"));
            $sheet->getStyle('B5')->applyFromArray($alignleft);
            $sheet->getStyle('B5')->applyFromArray($border_dash);
            $sheet->setCellValue('B6', __("提出期日"));
            $sheet->getStyle('B6')->applyFromArray($alignleft);
            $sheet->getStyle('B6')->applyFromArray($border_dash);

            $sheet->setCellValue('C2', $layer_code);
            $sheet->getStyle('C2')->applyFromArray($alignright);
            $sheet->getStyle('C2')->applyFromArray($border_dash);
            $sheet->setCellValue('C3', $layer_name);
            $sheet->getStyle('C3')->applyFromArray($alignleft);
            $sheet->getStyle('C3')->applyFromArray($border_dash);
            $sheet->setCellValue('C4', $period);
            $sheet->getStyle('C4')->applyFromArray($alignleft);
            $sheet->getStyle('C4')->applyFromArray($border_dash);
            $sheet->setCellValue('C5', $reference_date);
            $sheet->getStyle('C5')->applyFromArray($alignleft);
            $sheet->getStyle('C5')->applyFromArray($border_dash);
            $sheet->setCellValue('C6', $submission_deadline);
            $sheet->getStyle('C6')->applyFromArray($alignleft);
            $sheet->getStyle('C6')->applyFromArray($border_dash);

            //write table header
            $sheet->setCellValue('A9', __("事業領域"));
            $sheet->mergeCells('A9:A10');
            $sheet->getStyle('A9:A10')->applyFromArray($aligncenter);
            $sheet->getStyle('A9:A10')->applyFromArray($border_dash);

            $sheet->setCellValue('B9', __("保管場所"));
            $sheet->mergeCells('B9:B10');
            $sheet->getStyle('B9:B10')->applyFromArray($aligncenter);
            $sheet->getStyle('B9:B10')->applyFromArray($border_dash);

            $sheet->setCellValue('C9', __("品目コード"));
            $sheet->mergeCells('C9:C10');
            $sheet->getStyle('C9:C10')->applyFromArray($aligncenter);
            $sheet->getStyle('C9:C10')->applyFromArray($border_dash);

            $sheet->setCellValue('D9', __("品目テキスト"));
            $sheet->mergeCells('D9:D10');
            $sheet->getStyle('D9:D10')->applyFromArray($aligncenter);
            $sheet->getStyle('D9:D10')->applyFromArray($border_dash);

            $sheet->setCellValue('E9', __("品目名2"));
            $sheet->mergeCells('E9:E10');
            $sheet->getStyle('E9:E10')->applyFromArray($aligncenter);
            $sheet->getStyle('E9:E10')->applyFromArray($border_dash);

            $sheet->setCellValue('F9', __("単位"));
            $sheet->mergeCells('F9:F10');
            $sheet->getStyle('F9:F10')->applyFromArray($aligncenter);
            $sheet->getStyle('F9:F10')->applyFromArray($border_dash);

            $sheet->setCellValue('G9', __("入庫日"));
            $sheet->mergeCells('G9:G10');
            $sheet->getStyle('G9:G10')->applyFromArray($aligncenter);
            $sheet->getStyle('G9:G10')->applyFromArray($border_dash);

            $sheet->setCellValue('H9', __("滞留日数"));
            $sheet->mergeCells('H9:H10');
            $sheet->getStyle('H9:H10')->applyFromArray($aligncenter);
            $sheet->getStyle('H9:H10')->applyFromArray($border_dash);

            $sheet->setCellValue('I9', __("入庫Index No."));
            $sheet->mergeCells('I9:I10');
            $sheet->getStyle('I9:I10')->applyFromArray($aligncenter);
            $sheet->getStyle('I9:I10')->applyFromArray($border_dash);

            $sheet->setCellValue('J9', __("数量"));
            $sheet->mergeCells('J9:J10');
            $sheet->getStyle('J9:J10')->applyFromArray($aligncenter);
            $sheet->getStyle('J9:J10')->applyFromArray($border_dash);

            $sheet->setCellValue('K9', __("金額"));
            $sheet->mergeCells('K9:K10');
            $sheet->getStyle('K9:K10')->applyFromArray($aligncenter);
            $sheet->getStyle('K9:K10')->applyFromArray($border_dash);

            $sheet->setCellValue('L9', __("不完全品 有・無"));
            $sheet->mergeCells('L9:L10');
            $sheet->getStyle('L9:L10')->applyFromArray($aligncenter);
            $sheet->getStyle('L9:L10')->applyFromArray($border_dash);

            $sheet->setCellValue('M9', __("売り繋ぎ 済・未済"));
            $sheet->mergeCells('M9:M10');
            $sheet->getStyle('M9:M10')->applyFromArray($aligncenter);
            $sheet->getStyle('M9:M10')->applyFromArray($border_dash);

            $sheet->setCellValue('N9', __("契約 有・無"));
            $sheet->mergeCells('N9:N10');
            $sheet->getStyle('N9:N10')->applyFromArray($aligncenter);
            $sheet->getStyle('N9:N10')->applyFromArray($border_dash);

            $sheet->setCellValue('O9', __("経理コメント"));
            $sheet->mergeCells('O9:O10');
            $sheet->getStyle('O9:O10')->applyFromArray($aligncenter);
            $sheet->getStyle('O9:O10')->applyFromArray($border_dash);

            $sheet->setCellValue('P9', __("解決の為の具体的方策"));
            $sheet->mergeCells('P9:P10');
            $sheet->getStyle('P9:P10')->applyFromArray($aligncenter);
            $sheet->getStyle('P9:P10')->applyFromArray($border_dash);

            // 担当者コメント入力欄
            $sheet->setCellValue('Q9', __("担当者コメント入力欄"));
            $sheet->mergeCells('Q9:T9');
            $sheet->getStyle('Q9:T9')->applyFromArray($aligncenter);
            $sheet->getStyle('Q9:T9')->applyFromArray($border_dash);

            $sheet->setCellValue('Q10', __("確認完了"));
            $sheet->getStyle('Q10')->applyFromArray($aligncenter);
            $sheet->getStyle('Q10')->applyFromArray($border_dash);
            $sheet->setCellValue('R10', __("滞留理由"));
            $sheet->getStyle('R10')->applyFromArray($aligncenter);
            $sheet->getStyle('R10')->applyFromArray($border_dash);
            $sheet->setCellValue('S10', __("決済日"));
            $sheet->getStyle('S10')->applyFromArray($aligncenter);
            $sheet->getStyle('S10')->applyFromArray($border_dash);
            $sheet->setCellValue('T10', __("備考"));
            $sheet->getStyle('T10')->applyFromArray($aligncenter);
            $sheet->getStyle('T10')->applyFromArray($border_dash);

            $objPHPExcel->getActiveSheet()->getStyle('L9:L10')->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('M9:M10')->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('N9:N10')->getAlignment()->setWrapText(true);



            if (!$show_btn['save']) {

                //管理職
                $sheet->setCellValue('U9', __("管理職"));
                $sheet->mergeCells('U9:V9');
                $sheet->getStyle('U9:V9')->applyFromArray($aligncenter);
                $sheet->getStyle('U9:V9')->applyFromArray($border_dash);
                $sheet->setCellValue('U10', __("確認済"));
                $sheet->getStyle('U10')->applyFromArray($aligncenter);
                $sheet->getStyle('U10')->applyFromArray($border_dash);
                $sheet->setCellValue('V10', __("コメント入力欄"));
                $sheet->getStyle('V10')->applyFromArray($aligncenter);
                $sheet->getStyle('V10')->applyFromArray($border_dash);
            }

            if ($show_btn['review'] || $show_btn['approve_cancel'] || count(array_filter($show_btn)) < 1) {

                //経理担当者
                $sheet->setCellValue('W9', __("経理担当者"));
                $sheet->mergeCells('W9:X9');
                $sheet->getStyle('W9:X9')->applyFromArray($aligncenter);
                $sheet->getStyle('W9:X9')->applyFromArray($border_dash);
                $sheet->setCellValue('W10', __("確認欄"));
                $sheet->getStyle('W10')->applyFromArray($aligncenter);
                $sheet->getStyle('W10')->applyFromArray($border_dash);
                $sheet->setCellValue('X10', __("コメント入力欄"));
                $sheet->getStyle('X10')->applyFromArray($aligncenter);
                $sheet->getStyle('X10')->applyFromArray($border_dash);
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
                $period = $result['account_review_excel']['period'];
                $layer_code = $result['account_review_excel']['layer_code'];
                $destination_name = $result['account_review_excel']['destination_name'];
                $item_code = $result['account_review_excel']['item_code'];
                $item_name = $result['account_review_excel']['item_name'];
                $item_name_2 = $result['account_review_excel']['item_name_2'];
                $unit = $result['account_review_excel']['unit'];
                $registration_date = $result['account_review_excel']['registration_date'];
                $numbers_day = $result['account_review_excel']['numbers_day'];
                $receipt_index_no = $result['account_review_excel']['receipt_index_no'];
                $quantity = $result['account_review_excel']['quantity'];
                $amount = $result[0]['amount'];
                $is_error = $result['account_review_excel']['is_error'];
                $is_sold = $result['account_review_excel']['is_sold'];
                $is_contract = $result['account_review_excel']['is_contract'];
                // $reason = $result['account_review_excel']['reason'];
                // $solution = $result['account_review_excel']['solution'];
                $preview_comment = $result['account_review_excel']['preview_comment'];
                $flag = $result['account_review_excel']['flag'];
                $sap_schedule_date = $result['account_review_excel']['schedule_date'];
                $ba_inc_reason = $result['account_review_excel']['ba_inc_reason'];
                $settlement_date = $result['account_review_excel']['settlement_date'];




                if ($settlement_date == '0000-00-00') {
                    $settlement_date = '';
                }
                $remark = $result['account_review_excel']['remark'];
                $business_admin_comment = $result['account_review_excel']['business_admin_comment'];
                $acc_incharge_comment = $result['account_review_excel']['acc_incharge_comment'];
                // $acc_submgr_comment = $result['account_review_excel']['acc_submgr_comment'];

                // Calulating the difference in timestamps
                // $diff = strtotime($reference_date) - strtotime($sap_schedule_date);
                // 24 * 60 * 60 = 86400 seconds
                // $NoOfDays = round($diff / 86400); //abs(round($diff / 86400));

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
                        $sheet->getStyle('A' . $a . ':' . 'X' . $a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
                    } else {
                        $sheet->getStyle('A' . $a . ':' . 'X' . $a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                    }
                } elseif ($show_btn['approve'] || $show_btn['reject']) {
                    if ($flag == 4 || $flag == 5) {
                        $sheet->getStyle('A' . $a . ':' . 'V' . $a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                    } else {
                        $sheet->getStyle('A' . $a . ':' . 'V' . $a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
                    }
                } elseif ($show_btn['request']) {
                    if ($flag == 3 || $flag == 4) {
                        $sheet->getStyle('A' . $a . ':' . 'V' . $a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
                    } else {
                        $sheet->getStyle('A' . $a . ':' . 'V' . $a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                    }
                } elseif ($show_btn['save']) {
                    if ($flag == 2 || $flag == 3) {
                        $sheet->getStyle('A' . $a . ':' . 'T' . $a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
                    } else {
                        $sheet->getStyle('A' . $a . ':' . 'T' . $a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                    }
                } elseif ($flag >= 6) {
                    $sheet->getStyle('A' . $a . ':' . 'X' . $a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e8e8e8');
                } 

                $sheet->getStyle('K'.$a)->getNumberFormat()->setFormatCode('#,##0');

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $a, $layer_code);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $a, $destination_name);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $a, $item_code);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $a, $item_name);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('B' . $a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('C' . $a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $a)->applyFromArray($alignleft);
                /* added by Thura Moe */
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $a, $item_name_2);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $a, $unit);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $a, $registration_date);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $a, $numbers_day);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $a, $receipt_index_no);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $a, $quantity);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $a, $amount);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $a, $is_error);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $a, $is_sold);
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $a, $is_contract);
                $objPHPExcel->getActiveSheet()->setCellValue('O' . $a, $preview_comment);
                $objPHPExcel->getActiveSheet()->setCellValue('P' . $a, $solution);


                /* end */

                $objPHPExcel->getActiveSheet()->getStyle('A' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('B' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('C' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('J' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('J' . $a)->applyFromArray($alignright);
                /* added by Thura Moe */
                $objPHPExcel->getActiveSheet()->getStyle('E' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('E' . $a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('F' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('F' . $a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('G' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('G' . $a)->applyFromArray($alignleft);
                /* end */
                $objPHPExcel->getActiveSheet()->getStyle('H' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('H' . $a)->applyFromArray($alignright);

                $objPHPExcel->getActiveSheet()->getStyle('I' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('I' . $a)->applyFromArray($alignleft);


                $objPHPExcel->getActiveSheet()->getStyle('K' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('K' . $a)->applyFromArray($alignright);

                $objPHPExcel->getActiveSheet()->getStyle('L' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('L' . $a)->applyFromArray($alignleft);

                $objPHPExcel->getActiveSheet()->getStyle('M' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('M' . $a)->applyFromArray($alignleft);

                $objPHPExcel->getActiveSheet()->getStyle('N' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('N' . $a)->applyFromArray($alignleft);

                $objPHPExcel->getActiveSheet()->getStyle('O' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('O' . $a)->applyFromArray($alignleft);

                $objPHPExcel->getActiveSheet()->getStyle('P' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('P' . $a)->applyFromArray($alignleft);

                $objPHPExcel->getActiveSheet()->setCellValue('Q' . $a, $chk_busi_inc_confirm); //1
                $objPHPExcel->getActiveSheet()->getStyle('Q' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('Q' . $a)->applyFromArray($alignright);

                $objPHPExcel->getActiveSheet()->setCellValue('R' . $a, $ba_inc_reason);
                $objPHPExcel->getActiveSheet()->getStyle('R' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('R' . $a)->applyFromArray($alignleft);

                $objPHPExcel->getActiveSheet()->setCellValue('S' . $a, $settlement_date);
                $objPHPExcel->getActiveSheet()->getStyle('S' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('S' . $a)->applyFromArray($alignleft);

                $objPHPExcel->getActiveSheet()->setCellValue('T' . $a, $remark);
                $objPHPExcel->getActiveSheet()->getStyle('T' . $a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('T' . $a)->applyFromArray($alignright);

                if (!$show_btn['save']) {
                    $objPHPExcel->getActiveSheet()->setCellValue('U' . $a, $chk_busi_admin_confirm); //1
                    $objPHPExcel->getActiveSheet()->getStyle('U' . $a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('U' . $a)->applyFromArray($alignright);

                    $objPHPExcel->getActiveSheet()->setCellValue('V' . $a, $business_admin_comment);
                    $objPHPExcel->getActiveSheet()->getStyle('v' . $a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('V' . $a)->applyFromArray($alignright);
                }

                if ($show_btn['review'] || $show_btn['approve_cancel'] || count(array_filter($show_btn)) < 1) {
                    $objPHPExcel->getActiveSheet()->setCellValue('W' . $a, $chk_acc_inc_confirm); //1
                    $objPHPExcel->getActiveSheet()->getStyle('W' . $a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('W' . $a)->applyFromArray($alignright);

                    $objPHPExcel->getActiveSheet()->setCellValue('X' . $a, $acc_incharge_comment);
                    $objPHPExcel->getActiveSheet()->getStyle('X' . $a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('X' . $a)->applyFromArray($alignleft);
                }

                $a++;
            }

            $this->PhpExcel->output("StockAddComments" . ".xlsx");
            $this->autoLayout = false;
            $this->render('index');
        } else {
            $param = array(__("export"));
            $msg = parent::getErrorMsg("SE017", $param);
            $this->Session->write('EXCEL_ERR_MSG', $msg);
            $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
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

        $condi["date_format(Stock.period,'%Y-%m')"] = $period;
        if (!empty($layer_code)) {
            $condi["Stock.layer_code"] = $layer_code;
        }
        $toShowDate = $this->Stock->find('all', array(
            'conditions' => $condi,
            'fields' => array(
                'date_format(Stock.base_date,"%Y-%m-%d") as base_date',
                'date_format(Stock.deadline_date,"%Y-%m-%d") as deadline_date',
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

    /***
     ** @author Nu Nu Lwin
     ** @Date 01.07.2022
     ** New added From chemical
     #data pass from view ajax to controller to get mail data
     ***/
    public function getMailLists()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $language = $this->Session->read('Config.language');
        $Common     = new CommonController();
        $period     = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_name    = $this->Session->read('StockSelections_BA_NAME');
        $layer_code    = $_POST['layer_code'];
        $page       = $_POST['page'];
        $function   = $_POST['function'];
        //$language   = $_POST['language'];
        // $mails = $Common->getMailList($layer_code, $page, $function, $language, $layer_name, $period, Setting::LAYER_SETTING[1]);
        $mails = $Common->getMailList($layer_code, $page, $function, $language, $layer_name, $period, Setting::LAYER_SETTING['StockSelections']);
        
        return json_encode($mails);
    }

    public function sendEmailP3StockAddComments()
    {

        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $login_user_name = $this->Session->read('LOGIN_USER');

        $mail_template = 'common';
        $toEmail  = parent::formatMailInput($_POST['toEmail']);
        $ccEmail  = parent::formatMailInput($_POST['ccEmail']);
        $bccEmail = parent::formatMailInput($_POST['bccEmail']);

        $mail['subject']        = $_POST['mailSubj'];
        $mail['template_body']  = $_POST['mailBody'];

        if ($this->request->params['action'] == 'ReviewStockAddComments') {
            $url = '/StockAccountReviews?' . 'param=' . $period . '&ba=' . $layer_code;
        } else {
            $url = '/StockAddComments?' . 'param=' . $period . '&ba=' . $layer_code;
        }
        $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);

        if ($sentMail["error"]) {
            $msg = $sentMail["errormsg"];
            $this->Flash->set($msg, array('key' => 'saveError'));
            $invalid_email = parent::getErrorMsg('SE042');
            $this->Flash->set($invalid_email, array('key' => 'saveError'));
            $this->redirect(array('controller' => 'StockAddComments', 'action' => 'index'));
        } else {
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
