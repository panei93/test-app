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
 * @author Nu Nu Lwin
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');
App::import('Controller', 'Common');

    
class StockAccountPreviewsController extends AppController
{
    public $uses = array('Stock','Layer','StockBusiInchargeComment');
    public $components = array('Paginator');
    public $helpers = array('Paginator');

    /**
     * Check Session before render page
     *
     */
    public function beforeFilter()
    {
        
        parent::checkUserStatus();
        parent::CheckSession();
        parent::checkAccessType();
        parent::checkStockUrlSession();

        $period     = $this->request->query('param');
        $layer_code = $this->request->query('ba');
        $role_id    = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id   = $this->Session->read('LOGIN_ID');
        $pagename   = $this->request->params['controller'];
        $session_layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $Common     = new CommonController();
        
        $count_period = strlen($period);
        if ($count_period == 10) {
            $period = substr($period, 0, -3);
        }
        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        $layers      = array_keys($permissions['index']['layers']);

        if((!in_array($session_layer_code, $layers)) || ($session_layer_code=="" && $permissions['index']['limit']>0)) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"Error"));
            $this->redirect(array('controller'=>'StockSelections', 'action'=>'index'));
        }
        
        if (!empty($this->Session->read('StockSelections_PERIOD_DATE'))) {
            if (!empty($period) || !empty($layer_code)) {
                if ($period != $this->Session->read('StockSelections_PERIOD_DATE') || $layer_code != $this->Session->read('SESSION_LAYER_CODE')) {
                    $param = array();
                    $param['period']  = __("期間")." : ".$period;
                    if ($layer_code!="") {
                        $param['layer_code'] = __("と BA : ").$layer_code;
                    } else {
                        $param['layer_code'] = "";
                    }                    
                    $errorMsg = parent::getErrorMsg('SE061', $param);
                    $this->Flash->set($errorMsg, array("key"=>"Error"));
                    $this->redirect(array('controller'=>'StockSelections', 'action'=>'index'));
                }
            }
        } else {
            if (!empty($period) || !empty($layer_code)) {
                $this->Session->write('StockSelections_PERIOD_DATE', $period);
                $this->Session->write('SESSION_LAYER_CODE', $layer_code);
                if (empty($layer_name)) { #Added by NuNuLwin 28/07/2020
                   
                    $getBAName = $Common->getLayerThreeName($layer_code,date('Y-m-d', strtotime($period)));
                    $layer_name = $getBAName['name_jp'];
                }
            } else {
                $this->redirect(array('controller' => 'StockSelections', 'action' => 'index'));
            }
        }
        
    }

    public function index($errmessage = null)
    {

        $this->layout = 'stocks';
        $errorMsg   = "";
        $successMsg = "";
        $count = "";
        $showSaveBtn = false;
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $conditions = array();
        $condi = array(); //get base_date and deadLine_date with condition

        $Common = New CommonController();

        $flag_list  = Setting::ADDCMT_FLAG;
        $data['role_id']        = $role_id;
        $data['period']         = $period;
        $data['layer_code']     = $layer_code;
        $data['page']           = 'StockAccountPreviews';
        $data['flag_list']      = $flag_list;
        $data['modelName']      = 'Stock';

        $permissions = $this->Session->read('PERMISSIONS');
        $save_permt = $permissions['save'];
        $save_layer = array_keys($save_permt['layers']);
        if (($layer_code == '' && $save_permt['limit']==0) || in_array($layer_code, $save_layer)) {
            $showSaveBtn = true;
        }

        if ($period != null || $period != '') {
            $conditions["date_format(Stock.period,'%Y-%m')"] = $period;

            if ($layer_code != null || $layer_code != '') {
                $conditions["Stock.layer_code"] = $layer_code;
                $condi["Stock.layer_code"] = $layer_code;
            }
            $conditions['Stock.flag != '] = '0';

            $this->paginate = array(
                                'limit' => Paging::TABLE_PAGING,
                                'conditions' => $conditions,
                                'fields' => array(
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
                                            // 'Stock.amount',
                                            'SUM(Stock.amount) AS amount',
                                            'Stock.is_error',
                                            'Stock.is_sold',
                                            'Stock.is_contract',
                                            'Stock.preview_comment',
                                            // 'Stock.reason',
                                            // 'Stock.solution',
                                            'Stock.flag',
                                            'Stock.base_date',
                                            'Stock.deadline_date',
                                            'Stock.created_by',
                                            'Stock.updated_by',
                                            'Stock.created_date',
                                            'Stock.created_date',
                                            'Stock.updated_date',
                                            'MIN(Stock.flag) AS min_flag'
                                        ),
                                'group' => array(
                                            'Stock.layer_code',
                                            'Stock.destination_name',
                                            'Stock.period',
                                        ),

                                'order' => array(
                                            
                                            'Stock.id' => 'ASC',
                                            'Stock.layer_code' =>'ASC',
                                            'Stock.destination_name' => 'ASC',
                                            'Stock.receipt_index_no' => 'ASC',
                                            'Stock.created_date' => 'ASC',
                                        )

                            );
            /** if not choose layer_code or not, get all or one layer_code. For eigyo bucho approve(AD-5)**/
            $getBACode = $this->Stock->find('all',array(
                        'conditions' => $conditions,
                        'fields' => array('Stock.layer_code'),
                        'group' => array('Stock.layer_code'))
            );

            $baCodeArr = array();
            if (!empty($getBACode)) {
                foreach ($getBACode as $value) {
                    $checkFlagBaCode = $value['Stock']['layer_code'];
                
                    // layer_code of flag >=5, can't import this layer_code again.
                    $ba_flag = $this->Stock->find('all', array(
                                                'conditions' => array('Stock.layer_code' => $checkFlagBaCode,
                                                                                            'Stock.flag >='=> '5'),
                                                'fields' => array('Stock.layer_code',
                                                                                    'Stock.period'),
                                                'group' => array('Stock.layer_code')));
    
                    if (!empty($ba_flag)) {
                        foreach ($ba_flag as $value) {
                            $ba5 = $value['Stock']['layer_code'];
                            $periodOver5 = date_create($value['Stock']['period']);
                            
                            $formatPeriodOver5 = date_format($periodOver5, "Y-m");
                        
                            if ($formatPeriodOver5 == $period) {
                                array_push($baCodeArr, $ba5);
                            }
                        }
                    }
                }

                $this->set('overFlag5', $baCodeArr);
            }
            
            $base_date = "";
            $deadLine_date = "";
            
            try {
                
                $StockImportsInfo = $this->Paginator->paginate('Stock');

                //get base_date, deadLine_date accourding to layer_code, period and max id
                $condi["date_format(Stock.period,'%Y-%m')"] = $period;

                $toShowDate = $this->Stock->find('all', array(
                                            'conditions' => $condi,
                                            'fields' => array('date_format(Stock.base_date,"%Y-%m-%d") as base_date',
                                                'date_format(Stock.deadline_date,"%Y-%m-%d") as deadline_date',
                                                'Stock.id'),
                                            'order' => array('Stock.id DESC'),
                                            'limit' => 1,
                                    ));
            
                if (!empty($toShowDate)) {
                    foreach ($toShowDate as $date) {
                        if (isset($date[0]['base_date']) && $date[0]['base_date'] != 0) {
                            $base_date = $date[0]['base_date'];
                        }
                        if (isset($date[0]['deadline_date']) && $date[0]['deadline_date'] != 0) {
                            $deadLine_date = $date[0]['deadline_date'];
                        }
                    }
                }
            
                $count = $this->params['paging']['Stock']['count'];
                $pageno = $this->params['paging']['Stock']['page'];
                $this->Session->write('Page.pageCount', $pageno);
                if ($count != 0) {
                    $this->set('succCount', parent::getSuccessMsg('SS004', $count));
                } else {
                    $this->set('errCount', parent::getErrorMsg('SE001'));
                }
                
                $this->set("StockImportsInfo", $StockImportsInfo);
              
            } catch (Exception $e) {
               
                CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
                $this->render('index');
            }

            $get_ba = $this->getLayerGroupCode();

            if (empty($layer_name)) {
              
                $getBAName = $Common->getLayerThreeName($layer_code,date('Y-m-d', strtotime($period)));
                $layer_name = $getBAName['name_jp'];
            }
            
            // $minFlag = $this->paginate()['0']['0']['min_flag'];
            
            $flag_list  = Setting::PREVIEW_FLAG;
            $min_flag = min(array_column(array_column($this->paginate(), '0'), 'min_flag'));
           
            foreach($flag_list as $button => $flag){
                
                $checkButtonType[$button] = (in_array($min_flag,$flag))? '1': '';
                
            }
            $saved_count = $Common->checkSavedCount('Stock', $period, $layer_code, '2');

            $this->Session->delete('SEARCH_QUERY');
            $this->set('checkButtonType', $checkButtonType);
            $this->set('showSaveBtn', $showSaveBtn);
            $this->set('all_BA', $get_ba);
            $this->set('base_date', $base_date);
            $this->set('deadLine_date', $deadLine_date);
            $this->set('target_month', $period);
            $this->set('PERIOD', $period);
            $this->set('code', $layer_code);
            $this->set('count', $count);
            $this->set('successMsg', $successMsg);
            $this->set('errorMsg', $errorMsg);
            $this->set('saved_count', $saved_count);
            $this->render('index');
        } else {
            $this->redirect(array('controller' => 'StockSelections', 'action' => 'index'));
        }
    }

    # get ba code and name (By Thura Moe)
    # Edit by Nu Nu Lwin (2022/06/08) change Layer to Layer
    public function getLayerGroupCode()
    {
        $period  = $this->Session->read('StockSelections_PERIOD_DATE');
        $period  = date('Y-m-d', strtotime($period));

        $conditions['Layer.flag']             = 1;
        $conditions['Layer.type_order']       = Setting::LAYER_SETTING['StockSelections'];#wanna use layer(eg-2 => 2,3,4,...)
        $conditions['from_date <=']     = $period;
        $conditions['to_date >=']       = $period;

        $get_data = $this->Layer->find('all', array(
            'conditions' => $conditions,
            'fields' => array('id','layer_code','name_jp'),
            'order' =>array('Layer.layer_code' => 'ASC')
        ));
        $get_data = array_column($get_data, 'Layer');

        return $get_data;
    }

    public function searchPreview()
    {
        $errorMsg   = "";
        $successMsg = "";
        $this->layout = 'stocks';
        $role_id                = $this->Session->read('ADMIN_LEVEL_ID');
        $period                 = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code             = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name 			= $this->Session->read('StockSelections_BA_NAME');
        $destination            = $this->request->query('destination');
        $registrationDate       = $this->request->query('registrationDate');
        $recIndexNo    = $this->request->query('recIndexNo');
        $this->Session->write('SEARCH_QUERY', $this->request->query);

        $Common = New CommonController();
        
        $flag_list  = Setting::PREVIEW_FLAG;
        $data['role_id'] = $role_id;
        $data['period']         = $period;
        $data['layer_code']        = $layer_code;
        $data['page']           = 'StockAccountPreviews';
        $data['flag_list']      = $flag_list;
        $data['modelName']      = 'Stock';
        $checkButtonType = $Common->checkButtonType($data);
        $permissions = $this->Session->read('PERMISSIONS');
        $save_permt = $permissions['save'];
        $save_layer = array_keys($save_permt['layers']);
        if (($layer_code == '' && $save_permt['limit']==0) || in_array($layer_code, $save_layer)) {
            $showSaveBtn = true;
        }
        # add condition (By Thura Moe)
        $selected_ba = $this->request->query('choose_ba');
        $selected_ba = ($selected_ba == '') ? $layer_code : $selected_ba;
        if (!empty($selected_ba)) {
            $getBAName = $Common->getLayerThreeName($layer_code,date('Y-m-d', strtotime($period)));
            $layer_name = $getBAName['name_jp'];
        }
        # new add condition (By Nu Nu Lwins)// 1->match, 0->not match
        $match_status_regDate   = $this->request->query('');
        $match_status_Des 	    = $this->request->query('optDesCondition');
        $match_status_RegIndexNo 	= $this->request->query('optRegIndexNoCondition');
        $condi = array(); //get base_date and deadLine_date with condition

        $search_data = array(
            'destination'     => $destination,
            'choose_ba' 			=> $selected_ba,
            'registrationDate' => $registrationDate,
            'recIndexNo' => $recIndexNo,
            'optDesCondition' => $match_status_Des,
            'optRegIndexNoCondition' => $match_status_RegIndexNo,
        );

        $conditions = array();
        $base_date  = "";
        $deadLine_date = "";

        if ($period != null || $period != '') {
            $conditions["date_format(Stock.period,'%Y-%m')"] = $period;
        }
        //cheack if there is search condition
        if ($registrationDate != '' || $registrationDate != null) {
            $registrationDate = date('Y-m-d', strtotime($registrationDate));
            $conditions['Stock.registration_date'] = $registrationDate;
        }

        if ($destination != '' || $destination != null) {
            if ($match_status_Des == 1) {
                $conditions['Stock.destination_name LIKE'] = "%".$destination."%";
            } else {
                $conditions['Stock.destination_name NOT LIKE'] = "%".$destination."%";
            }
        }

        if ($recIndexNo != '' || $recIndexNo != null) {
            if ($match_status_RegIndexNo == 1) {
                $conditions['Stock.receipt_index_no LIKE'] = "%".$recIndexNo."%";
            } else {
                $conditions['Stock.receipt_index_no NOT LIKE'] = "%".$recIndexNo."%";
            }
        }
        # modify condition (By Thura Moe)
        if ($selected_ba != null || $selected_ba != '') {
            $conditions["Stock.layer_code"] = $selected_ba;
            $condi["Stock.layer_code"] = $selected_ba;
        }
        $conditions['Stock.flag != '] = '0';
    
        $this->paginate = array(
                        'limit' => Paging::TABLE_PAGING,
                        'conditions' => $conditions,
                        'fields' => array(
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
                                            // 'Stock.amount',
                                            'SUM(Stock.amount) AS amount',
                                            'Stock.is_error',
                                            'Stock.is_sold',
                                            'Stock.is_contract',
                                            'Stock.preview_comment',
                                            // 'Stock.reason',
                                            // 'Stock.solution',
                                            'Stock.flag',
                                            'Stock.base_date',
                                            'Stock.deadline_date',
                                            'Stock.created_by',
                                            'Stock.updated_by',
                                            'Stock.created_date',
                                            'Stock.created_date',
                                            'Stock.updated_date',
                                            'MIN(Stock.flag) AS min_flag'
                                        ),
                        'group' => array(
                                            'Stock.layer_code',
                                            'Stock.destination_name',
                                            'Stock.period',
                                        ),
                        'order' => array(
                                            
                                            'Stock.id' => 'ASC',
                                            'Stock.layer_code'=>'ASC',
                                            'Stock.receipt_index_no' => 'ASC',
                                            'Stock.base_date' => 'ASC',
                                            'Stock.deadline_date' => 'ASC'
                                            
                                        )

                        );

      
        $getBACode = $this->Stock->find('all', array(
                                                    'conditions' => $conditions,
                                                    'fields' => array('Stock.layer_code'),
                                                    'group' => array('Stock.layer_code')));

        $baCodeArr = array();
        if (!empty($getBACode)) {
            foreach ($getBACode as $value) {
                $checkFlagBaCode = $value['Stock']['layer_code'];
            
                // layer_code of flag >=5, can't import this layer_code again.
                $ba_flag = $this->Stock->find('all', array(
                                            'conditions' => array('Stock.layer_code' => $checkFlagBaCode,'Stock.flag >='=> '5'),
                                            'fields' => array('Stock.layer_code', 'Stock.period'),
                                            'group' => array('Stock.layer_code')));
            
                if (!empty($ba_flag)) {
                    foreach ($ba_flag as $value) {
                        $ba5 = $value['Stock']['layer_code'];
                        $periodOver5 = date_create($value['Stock']['period']);
                        
                        $formatPeriodOver5 = date_format($periodOver5, "Y-m");
                    
                        if ($formatPeriodOver5 == $period) {
                            array_push($baCodeArr, $ba5);
                        }
                    }
                }
            }
            $this->set('overFlag5', $baCodeArr);
        }
        try {
            $StockImportsInfo = $this->Paginator->paginate('Stock');

            //get base_date, deadLine_date accourding to layer_code, period and max id
            $condi["date_format(Stock.period,'%Y-%m')"] = $period;

            $toShowDate = $this->Stock->find('all', array(
                                            'conditions' => $condi,
                                            'fields' => array('date_format(Stock.base_date,"%Y-%m-%d") as base_date',
                                                'date_format(Stock.deadline_date,"%Y-%m-%d") as deadline_date',
                                                'Stock.id'),
                                            'order' => array('Stock.id DESC'),
                                            'limit' => 1,
                                    ));

            if (!empty($toShowDate)) {
                foreach ($toShowDate as $date) {
                    if (isset($date[0]['base_date']) && $date[0]['base_date'] != 0) {
                        $base_date = $date[0]['base_date'];
                    }
                    if (isset($date[0]['deadline_date']) && $date[0]['deadline_date'] != 0) {
                        $deadLine_date = $date[0]['deadline_date'];
                    }
                }
            }
            
            $count = "";
            $count = $this->params['paging']['Stock']['count'];
            $pageno = $this->params['paging']['Stock']['page'];
            $this->Session->write('Page.pageCount', $pageno);
            $minFlag = $this->paginate()['0']['0']['min_flag'];
            foreach($flag_list as $button => $flag){   
                $checkButtonType[$button] = (in_array($minFlag,$flag))? '1': '';         
            }
            if ($count != 0) {
                $this->set('succCount', parent::getSuccessMsg('SS004', $count));
            } else {
                $this->set('errCount', parent::getErrorMsg('SE001'));
            }
            $this->set('count', $count);
            $this->set("StockImportsInfo", $StockImportsInfo);
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $this->redirect(array('controller'=>'StockAccountPreviews', 'action'=>'searchPreview','?'=> $this->request->query));
        }

        # get business area data (By Thura Moe)
        $get_ba = $this->getLayerGroupCode();

        $this->set('checkButtonType', $checkButtonType);
        $this->set('showSaveBtn',$showSaveBtn);
        $this->set('all_BA', $get_ba);
        $this->set('base_date', $base_date);
        $this->set('deadLine_date', $deadLine_date);
        $this->set('target_month', $period);
        $this->set('PERIOD', $period);
        $this->set('code', $layer_code);
        $this->set('layer_name', $layer_name);
        $this->set("search_data", $search_data);
        $this->set('successMsg', $successMsg);
        $this->set('errorMsg', $errorMsg);
        $this->render('index');
    }

    public function Delete_Preview()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $errorMsg   = "";
        $successMsg = "";
        $response = array();
        $stock_id = $this->request->data["stock_id"];
        $date = date('Y-m-d H:i:s');
        $user_id = $this->Session->read('LOGIN_ID'); //get login id
        
        try {
            
            // 18/jun/19
            $StockDB = $this->Stock->getDataSource();
            $StockDB->begin();
            //change flag 0 at Stock.
            /* field and condition to update */
            $flag = $StockDB->value(0, 'string');
            $updated_by = $StockDB->value($user_id, 'string');
            $updated_date = $StockDB->value($date, 'string');

            $getMatchFlag = $this->Stock->getMatchFlagPreview($stock_id);
            if (!empty($getMatchFlag)) {
                foreach ($getMatchFlag as $value) {
                    $matchStockId = $value['stocks']['id'];

                    $del_preview = $this->Stock->updateAll(
                        array("Stock.flag"=>$flag,
                                   "Stock.updated_date"=> $updated_date,
                                   "Stock.updated_by"=> $updated_by),
                        array("Stock.id"=>$matchStockId,
                                  'OR' => array(
                                            array('Stock.flag'=>1),
                                            array('Stock.flag'=>2)
                                        )
                            )
                    );

                    //check cmt is has or not in tbl-busi-incharge table. If has, change flag to 0.
                    $this->StockBusiInchargeComment->AdminDelFlagBusiIncharge($matchStockId, $user_id);
                }
            } else {
                $del_preview = $this->Stock->updateAll(
                    array("Stock.flag"=>$flag,
                              "updated_date"=> $updated_date,
                              "updated_by"=> $updated_by),
                    array("Stock.id"=>$stock_id,
                              'OR' => array(
                                        array('Stock.flag'=>1),
                                        array('Stock.flag'=>2)
                                    )
                            )
                );

                $this->StockBusiInchargeComment->AdminDelFlagBusiIncharge($stock_id, $user_id);
            }

            $StockDB->commit();
            $successMsg = parent::getSuccessMsg('SS003');
            $this->Flash->set($successMsg, array('key'=>'deleteSuccess'));
            $response = array(
                'status' => 'success'
            );
        } catch (Exception $e) {
            $StockDB->rollback();
            $errorMsg = parent::getErrorMsg('SE007');
            $this->Flash->set($errorMsg, array('key'=>'saveError'));
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $response = array(
                'status' => 'fail'
            );
        }
        return json_encode($response);
    }

    public function SaveCheckAndComment()
    {
        
        $Common = new CommonController();
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $user_id = $this->Session->read('LOGIN_ID'); //get login id
        $login_user_name = $this->Session->read('LOGIN_USER');
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $search_query = $this->Session->read('SEARCH_QUERY');
        $this->Session->delete('SEARCH_QUERY');
        if (!empty($this->Session->read('StockSelections_BA_NAME'))) {
            $layer_name = $this->Session->read('StockSelections_BA_NAME');
        } else {
           
            $getBAName = $Common->getLayerThreeName($layer_code,date('Y-m-d', strtotime($period)));
            $layer_name = $getBAName['name_jp'];
           
        }

        $data_array = $this->request->data('json_data');
       
        $save_cmt = json_decode($data_array, true);
       
        $date = date('Y-m-d H:i:s');
        $chkCount = 0;
        $pageno = $this->Session->read('Page.pageCount');
        try {
            $StockDB = $this->Stock->getDataSource();
            $StockDB->begin();

            for ($i =0; $i < count($save_cmt);$i++) {
                $Stock_id       = $save_cmt[$i][0];
                $preCommit      = trim($save_cmt[$i][1]);
                $chk_status     = $save_cmt[$i][2];
                $layer_code     = "'".trim($save_cmt[$i][3])."'";
                $dest_name      = "'".trim($save_cmt[$i][4])."'";
                $item_code      = "'".trim($save_cmt[$i][5])."'";
                $item_name      = "'".trim($save_cmt[$i][6])."'";
                $item_name2     = "'".trim($save_cmt[$i][7])."'";
                $unit           = "'".trim($save_cmt[$i][8])."'";
                $reg_date       = strtotime($save_cmt[$i][9]) ? "'".trim($save_cmt[$i][9])."'" : "'0000-00-00'";
                $no_of_days     = "'".trim($save_cmt[$i][10])."'";
                $receipt_index  = "'".trim($save_cmt[$i][11])."'";
                $quantity       = "'".trim($save_cmt[$i][12])."'";
                $is_error       = "'".trim($save_cmt[$i][13])."'";
                $is_sold        = "'".trim($save_cmt[$i][14])."'";
                $is_contract    = "'".trim($save_cmt[$i][15])."'";
                
                $getBACode = $this->Stock->find('all', array(
                                                    'conditions' => array('Stock.id'=>$Stock_id),
                                                    'fields' => array('Stock.layer_code')
                                                    ));
                
                if (!empty($getBACode)) {
                    $baCode = $getBACode['0']['Stock']['layer_code'];
                    
                    // layer_code of flag >=5 and period, can't import this layer_code again.
                    $baFlag = $this->Stock->find('all', array(
                                            'conditions' => array('Stock.layer_code' => $baCode,
                                                                        'Stock.flag >='=> '5',
                                                                        "date_format(Stock.period,'%Y-%m')"=>$period),
                                            'fields' => array('Stock.layer_code',
                                                                                'Stock.period'),
                                            'group' => array('Stock.layer_code')));
                                         
                    if (empty($baFlag)) {
                        /**check layer_code.If BusinessArea tbl of flag = 0, to show error.**/
                        $flag = ($chk_status) ? $StockDB->value(2, 'string') : $StockDB->value('1', 'string');
                        $updated_by = $StockDB->value($user_id, 'string');
                        $updated_date = $StockDB->value($date, 'string');
                        $getMatchFlag = $this->Stock->getMatchFlagPreview($Stock_id);
                        
                        $BAFlag1 = $this->Layer->find(
                            'all',
                            array('conditions'=> array('Layer.flag'=>'1',
                                                    'Stock.id'=>$Stock_id,
                                                    'Layer.type_order' => Setting::LAYER_SETTING['StockSelections'],
                                                    'Layer.from_date <=' => $period.'-01',
                                                    'Layer.to_date >=' => $period.'-01'),
                                'joins'=> array(
                                                array('table' => 'stocks',
                                                    'alias' => 'Stock',
                                                    'type' => 'LEFT',
                                                    'conditions' => array(
                                                    'Stock.layer_code = Layer.layer_code'))))
                        );
                        
                        if (!empty($BAFlag1)) {
                            // change flag 2 at m_Stock.
                            /* field and condition to update */
                            
                            $getMatchFlag = $this->Stock->getMatchFlagPreview($Stock_id);
                            $preview_comment = $StockDB->value($preCommit, 'string');
                            $updated_by = $StockDB->value($user_id, 'string');
                            $updated_date = $StockDB->value($date, 'string');
                            if (!empty($getMatchFlag)) {
                                foreach ($getMatchFlag as $value) {
                                    $matchStockId = $value['stocks']['id'];
                                    
                                    $this->Stock->updateAll(
                                        array("Stock.flag"=>$flag,
                                                    "Stock.layer_code" => $layer_code,
                                                    "Stock.destination_name" => $dest_name,
                                                    "Stock.item_code" => $item_code,
                                                    "Stock.item_name" => $item_name,
                                                    "Stock.item_name_2" => $item_name2,
                                                    "Stock.unit" => $unit,
                                                    "Stock.registration_date" => $reg_date,
                                                    "Stock.numbers_day" => $no_of_days,
                                                    "Stock.receipt_index_no" => $receipt_index,
                                                    "Stock.quantity" => $quantity,
                                                    "Stock.is_error" => $is_error,
                                                    "Stock.is_sold" => $is_sold,
                                                    "Stock.is_contract" => $is_contract,
                                                    "Stock.preview_comment" => $preview_comment,
                                                    "Stock.updated_date"=> $updated_date,
                                                    "Stock.updated_by"=> $updated_by),
                                        array("Stock.id"=>$matchStockId,
                                                    'OR'=>array(array("Stock.flag"=>'1'),
                                                                array("Stock.flag"=>'2')
                                                            )
                                                    )
                                    );
                                }
                            } else {
                                $this->Stock->updateAll(
                                    array("Stock.flag"=>$flag,
                                        "Stock.layer_code" => $layer_code,
                                        "Stock.destination_name" => $dest_name,
                                        "Stock.item_code" => $item_code,
                                        "Stock.item_name" => $item_name,
                                        "Stock.item_name_2" => $item_name2,
                                        "Stock.unit" => $unit,
                                        "Stock.registration_date" => $reg_date,
                                        "Stock.numbers_day" => $no_of_days,
                                        "Stock.receipt_index_no" => $receipt_index,
                                        "Stock.quantity" => $quantity,
                                        "Stock.is_error" => $is_error,
                                        "Stock.is_sold" => $is_sold,
                                        "Stock.is_contract" => $is_contract,
                                        "Stock.preview_comment" => $preview_comment,
                                        "Stock.updated_date"=> $updated_date,
                                        "Stock.updated_by"=> $updated_by),
                                    array("Stock.id"=>$Stock_id,
                                                'OR'=>array(array("Stock.flag"=>'1'),
                                                            array("Stock.flag"=>'2')
                                                        )
                                                )
                                );
                            }
                        } else {
                            $errorMsg = parent::getErrorMsg('SE031');
                            $this->Flash->set($errorMsg, array('key'=>'saveError'));
                        }
                        $chkCount ++;
                    } else {
                        //layer_code is already approved.
                        $errorMsg = parent::getErrorMsg('SE018');
                        $this->Flash->set($errorMsg, array('key'=>'saveError'));
                        $this->redirect(array('controller'=>'StockAccountPreviews', 'action'=>'index'));
                    }
                }
            }
            
            if ($chkCount != 0) {
                
                if($_POST['mailSend']) {

                    $mail_template = 'common';
              
                    $toEmail = parent::formatMailInput($_POST['toEmail']);
                    $ccEmail = parent::formatMailInput($_POST['ccEmail']);
                    
                    $mail['subject']        = $_POST['mailSubj'];
                    $mail['template_body']  = $_POST['mailBody'];
                    $url = '/StockAddComments?param='.$period.'&ba='.$layer_code;

                    $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                    if ($sentMail["error"]) {

                        $StockDB->rollback();

                        $msg = $sentMail["errormsg"];
                        $this->Flash->set($msg, array('key'=>'saveError'));
                        $invalid_email = parent::getErrorMsg('SE042');
                        $this->Flash->set($invalid_email, array('key'=>'saveError'));
                        $this->redirect(array('controller'=>'StockAccountPreviews', 'action'=>'index'));
                    } 

                    $StockDB->commit();
                    $successMsg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($successMsg, array('key'=>'deleteSuccess'));
                    $msg = parent::getSuccessMsg("SS018");
                    $this->Flash->set($msg, array('key'=>'deleteSuccess'));
               

                }else{
                    $StockDB->commit();
                    $successMsg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($successMsg, array('key'=>'deleteSuccess'));
                }
                
                
            } else {
                $StockDB->commit();
                $successMsg = parent::getSuccessMsg('SS001');
                $this->Flash->set($successMsg, array('key'=>'deleteSuccess'));
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $StockDB->rollback();
            
            $errorMsg = parent::getErrorMsg('SE003');
            $this->Flash->set($errorMsg, array('key'=>'saveError'));
        }
        
        if(!empty($search_query)) {
            if($pageno > 1) {
                $this->redirect(array('controller'=>'StockAccountPreviews', 'action'=>'searchPreview', 'page' => $pageno, '?' => $search_query));
            }else
                $this->redirect(array('controller'=>'StockAccountPreviews', 'action'=>'searchPreview', '?' => $search_query));
        }else {
            if($pageno > 1) {
                $this->redirect(array('controller'=>'StockAccountPreviews', 'action'=>'index', 'page' => $pageno));
            }else
                $this->redirect(array('controller'=>'StockAccountPreviews', 'action'=>'index'));
        }
        
    }
    
}
