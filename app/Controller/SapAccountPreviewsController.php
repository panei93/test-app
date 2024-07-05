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

    
class SapAccountPreviewsController extends AppController
{
    public $uses = array('Sap','Layer','SapBusiInchargeComment');
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
        parent::checkSapUrlSession();

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
            $this->redirect(array('controller'=>'SapSelections', 'action'=>'index'));
        }
        if (!empty($this->Session->read('SapSelections_PERIOD_DATE'))) {
            if (!empty($period) || !empty($layer_code)) {
                if ($period != $this->Session->read('SapSelections_PERIOD_DATE') || $layer_code != $this->Session->read('SESSION_LAYER_CODE')) {
                    $param = array();
                    $param['period']  = __("期間")." : ".$period;
                    if ($layer_code!="") {
                        $param['layer_code'] = __("と BA : ").$layer_code;
                    } else {
                        $param['layer_code'] = "";
                    }                    
                    $errorMsg = parent::getErrorMsg('SE061', $param);
                    $this->Flash->set($errorMsg, array("key"=>"Error"));
                    $this->redirect(array('controller'=>'SapSelections', 'action'=>'index'));
                }
            }
        } else {
            if (!empty($period) || !empty($layer_code)) {
                $this->Session->write('SapSelections_PERIOD_DATE', $period);
                $this->Session->write('SESSION_LAYER_CODE', $layer_code);
                if (empty($layer_name)) { #Added by NuNuLwin 28/07/2020
                   
                    $getBAName = $Common->getLayerThreeName($layer_code,date('Y-m-d', strtotime($period)));
                    $layer_name = $getBAName['name_jp'];
                }
            } else {
                $this->redirect(array('controller' => 'SapSelections', 'action' => 'index'));
            }
        }
        
    }

    public function index($errmessage = null)
    {
        $this->layout = 'retentionclaimdebt';
        $errorMsg   = "";
        $successMsg = "";
        $count = "";
        $showSaveBtn = false;
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('SapSelections_BA_NAME');
        $conditions = array();
        $condi = array(); //get base_date and deadLine_date with condition

        $Common = New CommonController();

        $flag_list  = Setting::ADDCMT_FLAG;
        $data['role_id']        = $role_id;
        $data['period']         = $period;
        $data['layer_code']     = $layer_code;
        $data['page']           = 'SapAccountPreviews';
        $data['flag_list']      = $flag_list;
        $data['modelName']      = 'Sap';

        $permissions = $this->Session->read('PERMISSIONS');
        $save_permt = $permissions['save'];
        $save_layer = array_keys($save_permt['layers']);

        if(($layer_code == '' && $save_permt['limit'] == 0) || in_array($layer_code, $save_layer)) {
            $showSaveBtn = true;
        }
        if ($period != null || $period != '') {
            $conditions["date_format(Sap.period,'%Y-%m')"] = $period;

            if ($layer_code != null || $layer_code != '') {
                $conditions["Sap.layer_code"] = $layer_code;
                $condi["Sap.layer_code"] = $layer_code;
            }
            
            $conditions['Sap.flag != '] = '0';
            $this->paginate = array(
                'limit' => Paging::TABLE_PAGING,
                'conditions' => $conditions,
                'fields' => array(
                    'Sap.account_code',
                    'Sap.account_name',
                    'Sap.destination_code',
                    'Sap.destination_name',
                    'Sap.logistic_index_no',
                    'Sap.posting_date',
                    'Sap.recorded_date',
                    'Sap.receipt_shipment_date',
                    'Sap.schedule_date',
                    'Sap.numbers_day',
                    'Sap.maturity_date',
                    'Sap.line_item_text',
                    'Sap.sale_representative',
                    'SUM(Sap.jp_amount) AS jp_amount',
                    'Sap.flag',
                    'Sap.id',
                    'Sap.preview_comment',
                    'SUM(Sap.foreign_amount) AS foreign_amount',
                    'Sap.currency',
                    'Sap.layer_code',
                    'Sap.base_date', // new added feedback5
                    'Sap.deadline_date',
                    'MIN(Sap.flag) as min_flag'
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
                    'Sap.layer_code' =>'ASC',
                    'Sap.account_code' => 'ASC',
                    'Sap.destination_code' => 'ASC',
                    'Sap.logistic_index_no' => 'ASC',
                    'Sap.posting_date' => 'ASC',
                    'Sap.recorded_date' => 'ASC',
                    'Sap.receipt_shipment_date' => 'ASC',
                    'Sap.schedule_date' => 'ASC'      
                )
            );
        
            /** if not choose layer_code or not, get all or one layer_code. For eigyo bucho approve(AD-5)**/
            $getBACode = $this->Sap->find('all',array(
                'conditions' => $conditions,
                'fields' => array('Sap.layer_code'),
                'group' => array('Sap.layer_code'))
            );

            $baCodeArr = array();
            if (!empty($getBACode)) {
                foreach ($getBACode as $value) {
                    $checkFlagBaCode = $value['Sap']['layer_code'];
                
                    // layer_code of flag >=5, can't import this layer_code again.
                    $ba_flag = $this->Sap->find('all', array(
                        'conditions' => array(
                            'Sap.layer_code' => $checkFlagBaCode,
                            'Sap.flag >=' => '5'
                        ),
                        'fields' => array('Sap.layer_code', 'Sap.period'),
                        'group' => array('Sap.layer_code')
                    ));

                    if (!empty($ba_flag)) {
                        foreach ($ba_flag as $value) {
                            $ba5 = $value['Sap']['layer_code'];
                            $periodOver5 = date_create($value['Sap']['period']);
                            
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
                
                $SapImportsInfo = $this->Paginator->paginate('Sap');

                //get base_date, deadLine_date accourding to layer_code, period and max id
                $condi["date_format(Sap.period,'%Y-%m')"] = $period;

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
                            $base_date = $date[0]['base_date'];
                        }
                        if (isset($date[0]['deadline_date']) && $date[0]['deadline_date'] != 0) {
                            $deadLine_date = $date[0]['deadline_date'];
                        }
                    }
                }
            
                $count = $this->params['paging']['Sap']['count'];
                $pageno = $this->params['paging']['Sap']['page'];
                $this->Session->write('Page.pageCount', $pageno);
                if ($count != 0) {
                    $this->set('succCount', parent::getSuccessMsg('SS004', $count));
                } else {
                    $this->set('errCount', parent::getErrorMsg('SE001'));
                }
                
                $this->set("SapImportsInfo", $SapImportsInfo);
              
            } catch (Exception $e) {
               
                CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
                $this->render('index');
            }

            $get_ba = $this->getLayerGroupCode();

            if (empty($layer_name)) {
              
                $getBAName = $Common->getLayerThreeName($layer_code,date('Y-m-d', strtotime($period)));
                $layer_name = $getBAName['name_jp'];
            }
            
            $minFlag = $this->paginate()['0']['0']['min_flag'];
            
            $flag_list  = Setting::PREVIEW_FLAG;
            $min_flag = min(array_column(array_column($this->paginate(), '0'), 'min_flag'));
            $max_flag = max(array_column(array_column($this->paginate(), '0'), 'min_flag'));
            
            foreach($flag_list as $button => $flag){
                $checkButtonType[$button] = (in_array($min_flag,$flag) && $max_flag < 5)? '1': '';
            }
            $saved_count = $Common->checkSavedCount('Sap', $period, $layer_code, '2');
            
            $this->Session->delete('SEARCH_QUERY');
            $this->set('checkButtonType', $checkButtonType);
            $this->set('showSaveBtn', $showSaveBtn);
            $this->set('all_BA', $get_ba);
            $this->set('base_date', $base_date);
            $this->set('deadLine_date', $deadLine_date);
            $this->set('target_month', $period);
            $this->set('PERIOD', $period);
            $this->set('code', $layer_code);
            $this->set('layer_name', $layer_name);
            $this->set('count', $count);
            $this->set('successMsg', $successMsg);
            $this->set('errorMsg', $errorMsg);
            $this->set('saved_count', $saved_count);
            $this->render('index');
        } else {
            $this->redirect(array('controller' => 'SapSelections', 'action' => 'index'));
        }
    }

    # get ba code and name (By Thura Moe)
    # Edit by Nu Nu Lwin (2022/06/08) change Layer to Layer
    public function getLayerGroupCode()
    {
        $period  = $this->Session->read('SapSelections_PERIOD_DATE');
        $period  = date('Y-m-d', strtotime($period));

        $conditions['Layer.flag']             = 1;
        $conditions['Layer.type_order']       = Setting::LAYER_SETTING['SapSelections'];#wanna use layer(eg-2 => 2,3,4,...)
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
        $this->layout = 'retentionclaimdebt';
        $role_id                = $this->Session->read('ADMIN_LEVEL_ID');
        $period                 = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_code             = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name 			= $this->Session->read('SapSelections_BA_NAME');
        $destination            = $this->request->query('destination');
        $logisticIndexNo        = $this->request->query('logisticIndexNo');
        $postingDate            = $this->request->query('postingDate');
        $salesRepresentative    = $this->request->query('salesRepresentative');
        $currency 		        = $this->request->query('currency');
        $LowerCurrency          = strtolower($currency);
        $this->Session->write('SEARCH_QUERY', $this->request->query);
        
        $Common = New CommonController();
        
        $flag_list  = Setting::PREVIEW_FLAG;
        $data['role_id'] = $role_id;
        $data['period']         = $period;
        $data['layer_code']        = $layer_code;
        $data['page']           = 'SapAccountPreviews';
        $data['flag_list']      = $flag_list;
        $data['modelName']      = 'Sap';
        // $checkButtonType = $Common->checkButtonType($data);
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
        $match_status_SR 		= $this->request->query('optSRCondition');
        $match_status_Des 	    = $this->request->query('optDesCondition');
        $match_status_LogIdNo 	= $this->request->query('optLogIdNoCondition');
        $match_status_Currency 	= $this->request->query('optCurrencyCondition');
        $condi = array(); //get base_date and deadLine_date with condition

        $search_data = array(
            'destination'     => $destination,
            'logisticIndexNo' => $logisticIndexNo,
            'postingDate'     => $postingDate,
            'salesRepresentative' => $salesRepresentative,
            'currency' 				=> $currency,
            'choose_ba' 			=> $selected_ba,
            'optSRCondition' 	=> $match_status_SR,
            'optDesCondition' => $match_status_Des,
            'optLogIdNoCondition' 	=> $match_status_LogIdNo,
            'optCurrencyCondition' 	=> $match_status_Currency
        );

        $conditions = array();
        $base_date  = "";
        $deadLine_date = "";

        if ($period != null || $period != '') {
            $conditions["date_format(Sap.period,'%Y-%m')"] = $period;
        }
        //cheack if there is search condition
        if ($destination != '' || $destination != null) {
            if ($match_status_Des == 1) {
                $conditions['Sap.destination_name LIKE'] = "%".$destination."%";
            } else {
                $conditions['Sap.destination_name NOT LIKE'] = "%".$destination."%";
            }
        }

        if ($logisticIndexNo != '' || $logisticIndexNo != null) {
            if ($match_status_LogIdNo == 1) {
                $conditions['Sap.logistic_index_no LIKE'] = "%".$logisticIndexNo."%";
            } else {
                $conditions['Sap.logistic_index_no NOT LIKE'] = "%".$logisticIndexNo."%";
            }
        }

        if ($postingDate != '' || $postingDate != null) {
            $postingDate = date('Y-m-d', strtotime($postingDate));
            $conditions['Sap.posting_date'] = $postingDate;
        }

        if ($salesRepresentative != '' || $salesRepresentative != null) {
            if ($match_status_SR == 1) {
                $conditions['Sap.sale_representative LIKE'] = "%".$salesRepresentative."%";
            } else {
                $conditions['Sap.sale_representative NOT LIKE'] = "%".$salesRepresentative."%";
            }
        }

        if ($currency != '') {
            if ($match_status_Currency == 1) {
                $conditions['Sap.currency'] = $LowerCurrency;
            } else {
                $conditions['Sap.currency != '] = $LowerCurrency;
            }
        }
        # modify condition (By Thura Moe)
        if ($selected_ba != null || $selected_ba != '') {
            $conditions["Sap.layer_code"] = $selected_ba;
            $condi["Sap.layer_code"] = $selected_ba;
        }
    
        $conditions['Sap.flag != '] = '0';

        $this->paginate = array(
            'limit' => Paging::TABLE_PAGING,
            'conditions' => $conditions,
            'fields' => array('Sap.account_code',
                'Sap.account_name',
                'Sap.destination_code',
                'Sap.destination_name',
                'Sap.logistic_index_no',
                'Sap.posting_date',
                'Sap.recorded_date',
                'Sap.receipt_shipment_date',
                'Sap.schedule_date',
                'Sap.numbers_day',
                'Sap.maturity_date',
                'Sap.line_item_text',
                'Sap.sale_representative',
                'SUM(Sap.jp_amount) AS jp_amount',
                'Sap.flag',
                'Sap.id',
                'Sap.preview_comment',
                'SUM(Sap.foreign_amount) AS foreign_amount',
                'Sap.currency',
                'Sap.layer_code',
                'Sap.base_date', // new added feedback5
                'Sap.deadline_date',
                'MIN(Sap.flag) AS min_flag'
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
            )
        );

      
        $getBACode = $this->Sap->find('all', array(
            'conditions' => $conditions,
            'fields' => array('Sap.layer_code'),
            'group' => array('Sap.layer_code')
        ));

        $baCodeArr = array();
        if (!empty($getBACode)) {
            foreach ($getBACode as $value) {
                $checkFlagBaCode = $value['Sap']['layer_code'];
            
                // layer_code of flag >=5, can't import this layer_code again.
                $ba_flag = $this->Sap->find('all', array(
                    'conditions' => array(
                        'Sap.layer_code' => $checkFlagBaCode,
                        'Sap.flag >=' => '5'
                    ),
                    'fields' => array('Sap.layer_code','Sap.period'),
                    'group' => array('Sap.layer_code')
                ));
            
                if (!empty($ba_flag)) {
                    foreach ($ba_flag as $value) {
                        $ba5 = $value['Sap']['layer_code'];
                        $periodOver5 = date_create($value['Sap']['period']);
                        
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
            $SapImportsInfo = $this->Paginator->paginate('Sap');

            //get base_date, deadLine_date accourding to layer_code, period and max id
            $condi["date_format(Sap.period,'%Y-%m')"] = $period;

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
                        $base_date = $date[0]['base_date'];
                    }
                    if (isset($date[0]['deadline_date']) && $date[0]['deadline_date'] != 0) {
                        $deadLine_date = $date[0]['deadline_date'];
                    }
                }
            }
            
            $count = "";
            $count = $this->params['paging']['Sap']['count'];
            $pageno = $this->params['paging']['Sap']['page'];
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
            $this->set("SapImportsInfo", $SapImportsInfo);
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $this->redirect(array('controller'=>'SapAccountPreviews', 'action'=>'searchPreview','?'=> $this->request->query));
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
        $sap_id = $this->request->data["sap_id"];
        $date = date('Y-m-d H:i:s');
        $user_id = $this->Session->read('LOGIN_ID'); //get login id
        
        try {
            
            // 18/jun/19
            $sapDB = $this->Sap->getDataSource();
            $sapDB->begin();
            //change flag 0 at tbl_m_sap.
            /* field and condition to update */
            $flag = $sapDB->value(0, 'string');
            $updated_by = $sapDB->value($user_id, 'string');
            $updated_date = $sapDB->value($date, 'string');

            $getMatchFlag = $this->Sap->getMatchFlagPreview($sap_id);
                            
            if (!empty($getMatchFlag)) {
                foreach ($getMatchFlag as $value) {
                    $matchSapId = $value['saps']['id'];

                    $del_preview = $this->Sap->updateAll(
                        array("Sap.flag" => $flag,
                           "updated_date" => $updated_date,
                           "updated_by" => $updated_by),
                            array("Sap.id" => $matchSapId,
                            'OR' => array(
                                array('Sap.flag' => 1),
                                array('Sap.flag' => 2)
                            )
                        )
                    );

                    //check cmt is has or not in tbl-busi-incharge table. If has, change flag to 0.
                    $this->SapBusiInchargeComment->AdminDelFlagBusiIncharge($matchSapId, $user_id);
                }
            } else {
                $del_preview = $this->Sap->updateAll(
                    array("Sap.flag" => $flag,
                        "updated_date" => $updated_date,
                        "updated_by" => $updated_by),
                        array("Sap.id" => $sap_id,
                        'OR' => array(
                            array('Sap.flag' => 1),
                            array('Sap.flag' => 2)
                        )
                    )
                );

                $this->SapBusiInchargeComment->AdminDelFlagBusiIncharge($sap_id, $user_id);
            }

            $sapDB->commit();
            $successMsg = parent::getSuccessMsg('SS003');
            $this->Flash->set($successMsg, array('key'=>'deleteSuccess'));
            $response = array(
                'status' => 'success'
            );
        } catch (Exception $e) {
            $sapDB->rollback();
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
        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        $search_query = $this->Session->read('SEARCH_QUERY');
        $this->Session->delete('SEARCH_QUERY');
        if (!empty($this->Session->read('SapSelections_BA_NAME'))) {
            $layer_name = $this->Session->read('SapSelections_BA_NAME');
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
            $sapDB = $this->Sap->getDataSource();
            $sapDB->begin();
            $empty_date = '0000-00-00';
            for ($i =0; $i < count($save_cmt);$i++) {
                $sap_id = $save_cmt[$i][0];
                $pre_comment = trim($save_cmt[$i][1]);
                $chk_status = $save_cmt[$i][2];
                $account_name = "'".trim($save_cmt[$i][3])."'";
                $dest_code = "'".trim($save_cmt[$i][4])."'";
                $dest_name = "'".trim($save_cmt[$i][5])."'";
                $logistics_no = "'".trim($save_cmt[$i][6])."'";
                $posting_date = trim($save_cmt[$i][7]);
                $posting_date = (!empty($posting_date)) ? "'".$posting_date."'" : $empty_date;
                $recorded_date = trim($save_cmt[$i][8]);
                $recorded_date = (!empty($recorded_date)) ? "'".$recorded_date."'" : $empty_date;
                $receipt_shipment_date = trim($save_cmt[$i][9]);
                $receipt_shipment_date = (!empty($receipt_shipment_date)) ? "'".$receipt_shipment_date."'" : $empty_date;
                $schedule_date = trim($save_cmt[$i][10]);
                $schedule_date = (!empty($schedule_date)) ? "'".$schedule_date."'" : $empty_date;
                $sale_repre = "'".trim($save_cmt[$i][11])."'";
                $currency = "'".trim($save_cmt[$i][12])."'";
                $maturity_date = trim($save_cmt[$i][13]);
                $maturity_date = (!empty($maturity_date)) ? "'".$maturity_date."'" : $empty_date;
                $line_item_text = "'".trim($save_cmt[$i][14])."'";
                
                $getBACode = $this->Sap->find('all', array(
                    'conditions' => array(
                        'Sap.id' => $sap_id
                    ),
                    'fields' => array('Sap.layer_code')
                ));
                
                if (!empty($getBACode)) {
                    $baCode = $getBACode['0']['Sap']['layer_code'];
                    
                    // layer_code of flag >=5 and period, can't import this layer_code again.
                    $baFlag = $this->Sap->find('all', array(
                        'conditions' => array(
                            'Sap.layer_code' => $baCode,
                            'Sap.flag >=' => '5',
                            "date_format(Sap.period,'%Y-%m')" => $period
                        ),
                        'fields' => array('Sap.layer_code', 'Sap.period'),
                        'group' => array('Sap.layer_code')
                    ));
                    
                    if(empty($baFlag)) {
                        $flag = ($chk_status) ? $sapDB->value(2, 'string') : $sapDB->value('1', 'string');
                        $preview_comment = $sapDB->value($pre_comment, 'string');
                        $updated_by = $sapDB->value($user_id, 'string');
                        $updated_date = $sapDB->value($date, 'string');
                        $getMatchFlag = $this->Sap->getMatchFlagPreview($sap_id);

                        $BAFlag1 = $this->Layer->find('all', array(
                            'conditions'=> array(
                                'Layer.flag' => '1',
                                'Sap.id' => $sap_id,
                                'Layer.type_order' => Setting::LAYER_SETTING['SapSelections'],
                                'Layer.from_date <=' => $period.'-01',
                                'Layer.to_date >=' => $period.'-01'
                            ),
                            'joins'=> array(
                                array(
                                    'table' => 'saps',
                                    'alias' => 'Sap',
                                    'type' => 'LEFT',
                                    'conditions' => array(
                                        'Sap.layer_code = Layer.layer_code'
                                    )
                                )
                            )
                        ));
                        if (!empty($BAFlag1)) {
                            if (!empty($getMatchFlag)) {
                                foreach ($getMatchFlag as $value) {
                                    $matchSapId = $value['saps']['id'];
                                    
                                    $this->Sap->updateAll(array(
                                        "Sap.flag" => $flag,
                                        "Sap.preview_comment" => $preview_comment,
                                        "Sap.account_name" => $account_name,
                                        "Sap.destination_code" => $dest_code,
                                        "Sap.destination_name" => $dest_name,
                                        "Sap.logistic_index_no" => $logistics_no,
                                        "Sap.posting_date" => $posting_date,
                                        "Sap.recorded_date" => $recorded_date,
                                        "Sap.receipt_shipment_date" => $receipt_shipment_date,
                                        "Sap.schedule_date" => $schedule_date,
                                        "Sap.sale_representative" => $sale_repre,
                                        "Sap.currency" => $currency,
                                        "Sap.maturity_date" => $maturity_date,
                                        "Sap.line_item_text" => $line_item_text,
                                        "Sap.updated_date" => $updated_date,
                                        "Sap.updated_by" => $updated_by),
                                        array(
                                            "Sap.id" => $matchSapId,
                                            'OR'=>array(
                                                array("Sap.flag"=>'1'),
                                                array("Sap.flag"=>'2')
                                            )
                                        )
                                    );
                                }
                            }else {
                                $this->Sap->updateAll(array(
                                    "Sap.flag" => $flag,
                                    "Sap.preview_comment" => $preview_comment,
                                    "Sap.account_name" => $account_name,
                                    "Sap.destination_code" => $dest_code,
                                    "Sap.destination_name" => $dest_name,
                                    "Sap.logistic_index_no" => $logistics_no,
                                    "Sap.posting_date" => $posting_date,
                                    "Sap.recorded_date" => $recorded_date,
                                    "Sap.receipt_shipment_date" => $receipt_shipment_date,
                                    "Sap.schedule_date" => $schedule_date,
                                    "Sap.sale_representative" => $sale_repre,
                                    "Sap.currency" => $currency,
                                    "Sap.maturity_date" => $maturity_date,
                                    "Sap.line_item_text" => $line_item_text,
                                    "Sap.updated_date" => $updated_date,
                                    "Sap.updated_by" => $updated_by),
                                    array(
                                        "Sap.id" => $sap_id,
                                        'OR'=>array(
                                            array("Sap.flag"=>'1'),
                                            array("Sap.flag"=>'2')
                                        )
                                    )
                                );
                            }
                        }else {
                            $errorMsg = parent::getErrorMsg('SE031');
                            $this->Flash->set($errorMsg, array('key'=>'saveError'));
                        }
                        $chkCount ++;
                        

                    }else {
                        $errorMsg = parent::getErrorMsg('SE018');
                        $this->Flash->set($errorMsg, array('key'=>'saveError'));
                        $this->redirect(array('controller'=>'SapAccountPreviews', 'action'=>'index'));
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
                    $url = '/SapAddComments?param='.$period.'&ba='.$layer_code;

                    $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                    if ($sentMail["error"]) {

                        $sapDB->rollback();

                        $msg = $sentMail["errormsg"];
                        $this->Flash->set($msg, array('key'=>'saveError'));
                        $invalid_email = parent::getErrorMsg('SE042');
                        $this->Flash->set($invalid_email, array('key'=>'saveError'));
                        $this->redirect(array('controller'=>'SapAccountPreviews', 'action'=>'index'));
                    } 

                    $sapDB->commit();
                    $successMsg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($successMsg, array('key'=>'deleteSuccess'));
                    $msg = parent::getSuccessMsg("SS018");
                    $this->Flash->set($msg, array('key'=>'deleteSuccess'));
               

                }else{
                    $sapDB->commit();
                    $successMsg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($successMsg, array('key'=>'deleteSuccess'));
                }
                
                
            } else {
                $sapDB->commit();
                $successMsg = parent::getSuccessMsg('SS001');
                $this->Flash->set($successMsg, array('key'=>'deleteSuccess'));
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $sapDB->rollback();
            
            $errorMsg = parent::getErrorMsg('SE003');
            $this->Flash->set($errorMsg, array('key'=>'saveError'));
        }
        
        if(!empty($search_query)) {
            if($pageno > 1) {
                $this->redirect(array('controller'=>'SapAccountPreviews', 'action'=>'searchPreview', 'page' => $pageno, '?' => $search_query));
            }else
                $this->redirect(array('controller'=>'SapAccountPreviews', 'action'=>'searchPreview', '?' => $search_query));
        }else {
            if($pageno > 1) {
                $this->redirect(array('controller'=>'SapAccountPreviews', 'action'=>'index', 'page' => $pageno));
            }else
                $this->redirect(array('controller'=>'SapAccountPreviews', 'action'=>'index'));
        }
        
    }
    
}
