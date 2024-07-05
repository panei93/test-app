<?php

use DataValidation;
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
define('MODEL', 'LaborCost');
/**
 * LaborCostsController
 */
class LaborCostsController extends AppController
{

/**
 * Components
 *
 * @var array
 */
    public $components = array('Session','PhpExcel.PhpExcel');
    public $uses = array('Layer','Position','LaborCost','LaborCostDetail','LcComment', 'BuApprovedLog');
    public $helpers = array('Html', 'Form', 'Csv');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkBuUrlSession($this->name);

        #get required params
        $login_id   = $this->Session->read('LOGIN_ID');
        $role_id    = $this->Session->read('ADMIN_LEVEL_ID');
        if(!empty($this->Session->check('SEARCH_LABOR_COST.target_year'))) {
            $t_y = $this->Session->read('SEARCH_LABOR_COST')['target_year'];
        }else {
            // $t_y = date("Y");
            $t_y = $_SESSION['BudgetTargetYear'];
        }
        $selectedTermYear = $_SESSION['yearListOnTerm'][$t_y];
        $start_month = $selectedTermYear[0];
        $end_month = $selectedTermYear[1];
        $page_name   = $this->request->params['controller'];
        
        $Common = new CommonController();
        $permissions = $Common->getPermissionsByRoleForBU($login_id, $role_id, $start_month, $end_month, $page_name);

        $this->Session->write('LC.INDEX', $permissions['index']['layer']);
        $this->Session->write('LC.COMMENT', $permissions['comment']['layer']);
        $this->Session->write('LC.SAVE', $permissions['save']['layer']);
        $this->Session->write('LC.CONFIRM', $permissions['confirm']['layer']);
        $this->Session->write('LC.CONFIRM_CANCEL', $permissions['confirm_cancel']['layer']);

        if(!in_array($this->Session->read('SELECTED_GROUP'), $permissions['index']['layer'])) {
            $error_msg = parent::getErrorMsg('SE065');
            $this->Flash->set($error_msg, array('key'=>'BuError'));
            $this->redirect(array('controller'=>'BUSelections', 'action'=>'index'));
        }
    }
    

    /**
     * index method
     *
     * @author SST 17.8.2022
     * @return void
     */
    public function index()
    {   $this->layout = 'buanalysis';
        $errormsg   = "";
        $successMsg = "";
        $result_data_list = array();
        $rlist ="";
        // if ($this->Session->check('LABOR_COST_SUCCESS_MSG')) {
        //     $successMsg = $this->Session->read('LABOR_COST_SUCCESS_MSG');
        //     $this->Session->delete('LABOR_COST_SUCCESS_MSG');
        // } 
        #get language extension
		if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}
        //SST 17.8.2022
        // $current_yr = date("Y");
        $current_yr = $_SESSION['BudgetTargetYear'];
        
        //for target yr and position drop down
        $year_list = $_SESSION['yearListOnTerm'];
        $buTermId = $_SESSION['BU_TERM_ID'];
        // Remove the first key and value
        unset($year_list[key($year_list)]);
        //wirite it back to session with new name
        $this->Session->write('TERM_YEAR_LIST', $year_list);
        
        if(empty($year_list)) {
            $errormsg = parent::getErrorMsg('SE149');
        }else {
            if(!empty($this->Session->check('SEARCH_LABOR_COST.target_year'))) {
                $t_y = $this->Session->read('SEARCH_LABOR_COST')['target_year'];
            }else {
                $t_y = $current_yr;
            }
            //for gp name drop down    
            // $layer_code_list = $this->Layer->getLayerCodeList($year_list[$t_y]);
            #get required params
            $login_id   = $this->Session->read('LOGIN_ID');
            $role_id    = $this->Session->read('ADMIN_LEVEL_ID');
            $selectedTermYear = $_SESSION['yearListOnTerm'][$t_y];
            $start_month = $selectedTermYear[0];
            $end_month = $selectedTermYear[1];
            $page_name   = $this->request->params['controller'];
            $Common = new CommonController();
            $permissions = $Common->getPermissionsByRoleForBU($login_id, $role_id, $start_month, $end_month, $page_name);
            $layer_code_list = $this->Layer->getLayerCodeListByPermissions(implode("','",  $permissions['index']['layer']));
            
            $this->Session->write('YEAR_LIST', $year_list);
            
            if (count($this->Session->read('SEARCH_LABOR_COST'))==2) {
                $search_data = $this->Session->read('SEARCH_LABOR_COST');
            }else{
                $search_data = array (
                    'target_year' => $current_yr,
                    'layer_code'  => $this->Session->read('SELECTED_GROUP')
                );
                $this->Session->write('SEARCH_LABOR_COST', $search_data);
            }
            
            // $user_list = $this->LaborCostDetail->checkUser($search_data['target_year'],$search_data['layer_code']);
            $user_list = $this->LaborCostDetail->checkUser($year_list[$search_data['target_year']],$search_data['layer_code'], $buTermId);
            $prepared_data = $this->preparedData($user_list,$search_data);
            if(!empty($prepared_data)){
                $result_data_list = $prepared_data;
            }
            if(empty($result_data_list) || empty($layer_code_list)){
                $errormsg = parent::getErrorMsg('SE153');
            }
            $comment = $this->LcComment->find('first', array(
                'fields' => ['LcComment.id', 'LcComment.comment', 'LcComment.updated_date' ,'users.user_name'],
                'conditions' => [
                    'LcComment.bu_term_id' => $_SESSION['BU_TERM_ID'],
                    'LcComment.target_year' => $search_data['target_year'],
                    'LcComment.layer_code' => $search_data['layer_code'],
                    'LcComment.page_name' => 'LaborCosts'
                ],
                'joins' => [
                    [
                        'table' => 'users',
                        'type'  =>  'left',
                        'conditions' => [
                            'users.id = LcComment.updated_by'
                        ]                
                    ]
                ],
            ));
            !empty($comment) ? $comment['LcComment']['comment'] = nl2br($comment['LcComment']['comment']) : $comment;
            # date only format
            // !empty($comment) ? $comment['LcComment']['updated_date'] = date('Y-m-d', strtotime($comment['LcComment']['updated_date'])) : $comment;
        }

        #read only value to lock
        #get menu id
        $Common = new CommonController();
        $menu_id = $Common->getMenuId($this);
        if(!empty($menu_id)){
            $approved_flag =  $this->BuApprovedLog->find('first', array(
                'fields' => array('flag'),
                'conditions' => array(
                    'menu_id' => $menu_id,
                    'bu_term_id' => $buTermId,
                    'target_year' => $search_data['target_year'],
                    'group_code' => $search_data['layer_code'],
                    'line_code' => '0',
                    'business_code' => '0',
                    'sub_business_code' => '0'
                )
            ))['BuApprovedLog']['flag'] ?? 1;
            $completed_flag =  $this->BuApprovedLog->find('first', array(
                'fields' => array('flag'),
                'conditions' => array(
                    'menu_id' => Setting::MENU_ID_LIST['BudgetResult'],
                    'bu_term_id' => $buTermId,
                    'group_code' => $search_data['layer_code'],
                    // 'line_code' => '0',
                    // 'business_code' => '0',
                    // 'sub_business_code' => '0',
                )
            ))['BuApprovedLog']['flag'] ?? 1;
        }

        #get show/hide limits
        $readLimit = $this->name.'ReadLimit';
        $commentLimit = $this->name.'CommentLimit';
        $saveLimit = $this->name.'SaveLimit';
        $confirmLimit = $this->name.'ConfirmLimit';
        $confirmCancelLimit = $this->name.'Confirm_cancelLimit';
        
        if($this->Session->check('PAGE_LIMITATION')){
            $page_limitation = $this->Session->read('PAGE_LIMITATION');
            $showReadBtn = ($page_limitation[$readLimit] !== false) ? true : false;
            $showCommentBtn = ($page_limitation[$commentLimit] !== false) ? true : false;
            $showSaveBtn = ($page_limitation[$saveLimit] !== false) ? true : false;
            $showConfirmBtn = ($page_limitation[$confirmLimit] !== false) ? true : false;
            $showConfirmCancelBtn = ($page_limitation[$confirmCancelLimit] !== false) ? true : false;
        }

        #check layer code exists or not
        list($disabledCommentBtn, $disabledSaveBtn, $disabledConfirmBtn, $disabledConfirmCancelBtn) = $this->checkLayerCodeExist();
        $old_data = json_encode($result_data_list);       
        $this->Session->write('SELECTION', 'SET');
        $this->set(compact('current_yr','year_list','layer_code_list','search_data', 'errormsg','successMsg','lang_name','result_data_list','comment', 'approved_flag', 'completed_flag', 'showReadBtn', 'showCommentBtn', 'showSaveBtn', 'showConfirmBtn', 'showConfirmCancelBtn', 'disabledCommentBtn', 'disabledSaveBtn', 'disabledConfirmBtn', 'disabledConfirmCancelBtn', 'old_data'));
        $this->render('index');
    }
     /**
     * prepare data to show, download
     * @author SST 23.9.2022
     * @return data array
     */
    public function preparedData($user_list,$search_data){
        $result_data_list = array();
        $data =array();
        $buTermId = $_SESSION['BU_TERM_ID'];
        $checkDetail = $this->LaborCostDetail->getDetailData($search_data['target_year'], $buTermId);
        if(!empty($user_list)){
            foreach($user_list as $user){
                $user_id        = $user['User']['id'];
                $user_name      = $user['User']['user_name'];
                $labor_cost = $this->LaborCost->getLaborCostData($search_data,$user_id, $buTermId);
                if(!empty($labor_cost)){//data already exist in labor_cost tbl
                    
                    $data =array();
                    $target_year        = $labor_cost['0']['LaborCost']['target_year'];
                    $adjust_name        = $labor_cost['0']['LaborCost']['adjust_name']; 
                    // $position_id        = $labor_cost['0']['LaborCost']['position_id'];
                    $position_code        = $labor_cost['0']['LaborCost']['position_code'];
                    $chk_flag=false;
                    if(!empty($checkDetail)){//if data exist in labor_cost_detail tbl, get person count
                        $position_salary = $this->Position->getPositionBudgetData($position_code,$target_year);
                        
                        if(!empty($position_salary)){
                            $personnel_cost = $position_salary[0]['Position']['personnel_cost'];
                            $corporate_cost = $position_salary[0]['Position']['corporate_cost'];
                            $positionConstant =array();
                            $p_name = $position_salary[0]['Position']['position_name'];//use 0index bcoz 1user has 1 position
                            $positionConstant = PositionType::PositionConstant[$p_name];
                            if($positionConstant !=null || $positionConstant !=""){
                                $positionConstant=0.5;
                            }else{
                                $positionConstant=1;
                            }
                        }else{
                            $personnel_cost =0;
                            $corporate_cost =0;
                        } 
                        
                        
                    }else{//data not exist in labor_cost_detail, only exist in labor_cost tbl
                        $chk_flag ==false;
                        
                    }  
                    if($chk_flag ==false){
                        // $person_count = 0;
                        $person_count = $labor_cost[0]['LaborCost']['person_count'];        
                        $b_person_count     = $labor_cost[0]['LaborCost']['b_person_count'];        
                        $common_expense     = $labor_cost[0]['LaborCost']['common_expense'];
                        $b_person_total     = $labor_cost[0]['LaborCost']['b_person_total'];
                        $labor_unit         = $labor_cost[0]['LaborCost']['labor_unit'];
                        $corpo_unit         = $labor_cost[0]['LaborCost']['corp_unit'];
                        $yearly_labor_cost  = $labor_cost[0]['LaborCost']['yearly_labor_cost'];
                        $unit_labor_cost    = $labor_cost[0]['LaborCost']['unit_labor_cost'];
                        $adjust_labor_cost  = $labor_cost[0]['LaborCost']['adjust_labor_cost'];
                        $yearly_corpo_cost  = $labor_cost[0]['LaborCost']['yearly_corpo_cost'];
                        $unit_corpo_cost    = $labor_cost[0]['LaborCost']['unit_corpo_cost'];
                        $adjust_corpo_cost  = $labor_cost[0]['LaborCost']['adjust_corpo_cost'];
                        $personnel_cost = $labor_cost[0]['Position']['personnel_cost'];
                        $corporate_cost = $labor_cost[0]['Position']['corporate_cost'];

                        #khin
                        $b_person_total = $person_count + $common_expense;//J-予算人員数（①+②） 
                        $labor_unit = $personnel_cost * $person_count;//K-人件費単価
                        $corpo_unit = $b_person_total * $corporate_cost;//L-ｺｰﾎﾟﾚｰﾄ経費割当単価 
                        $yearly_labor_cost = ($labor_unit * 12) + $adjust_labor_cost;//M-人件費（年間）
                        if($person_count == 0) {
                            $unit_labor_cost = 0;
                        }else {
                            $unit_labor_cost = $yearly_labor_cost / $person_count / 12;//人件費単価（割戻）
                        }
                        
                        $yearly_corpo_cost = ($corpo_unit * 12) + $adjust_corpo_cost;//P-ｺｰﾎﾟﾚｰﾄ経費（年間）
                        if($person_count == 0) {
                            $unit_corpo_cost = 0;
                        }else {
                            $unit_corpo_cost = $yearly_corpo_cost / $person_count / 12;//Q-ｺｰﾎﾟﾚｰﾄ経費割当単価（割戻）
                        }
                    }        
                                               
                    $data['User']['id']                 	= $user_id;
                    $data['User']['user_name']          	= $user_name;
                    $data['Position']['position_name']		= $labor_cost['0']['Position']['position_name'];
                    $data['Position']['personnel_cost']		= $labor_cost['0']['Position']['personnel_cost'];
                    $data['Position']['corporate_cost']		= $labor_cost['0']['Position']['corporate_cost'];
                    // $data['LaborCost']['position_id']   	= $position_id;
                    $data['LaborCost']['position_code']   	= $position_code;
                    $data['LaborCost']['person_count']		= $person_count;
                    $data['LaborCost']['b_person_count']	= $b_person_count;
                    $data['LaborCost']['common_expense']	= $common_expense;
                    $data['LaborCost']['b_person_total']	= $b_person_total;
                    $data['LaborCost']['labor_unit']    	= round($labor_unit,4);////$personnel_cost * $person_count
                    $data['LaborCost']['corp_unit']    	    = $corpo_unit;
                    $data['LaborCost']['yearly_labor_cost'] = $yearly_labor_cost;
                    $data['LaborCost']['unit_labor_cost']  	= $unit_labor_cost;
                    $data['LaborCost']['adjust_labor_cost']	= $adjust_labor_cost;
                    $data['LaborCost']['yearly_corpo_cost']	= $yearly_corpo_cost;
                    $data['LaborCost']['unit_corpo_cost']   = $unit_corpo_cost;
                    $data['LaborCost']['adjust_corpo_cost'] = $adjust_corpo_cost;
                    $data['LaborCost']['adjust_name']       = $adjust_name;
                }else{//only user_data,
                    $data['User']['id']                 	= $user_id;
                    $data['User']['user_name']          	= $user_name;
                    $data['Position']['position_name']		= $user['Position']['position_name'];
                    $data['Position']['personnel_cost']		= $user['Position']['personnel_cost'];
                    $data['Position']['corporate_cost']		= $user['Position']['corporate_cost'];
                    // $data['LaborCost']['position_id']   	= "0";
                    $data['LaborCost']['position_code']   	= $user['Position']['position_code'];
                    $data['LaborCost']['person_count']		= "0.0000";
                    $data['LaborCost']['b_person_count']	= "0.0000";
                    $data['LaborCost']['common_expense']	= "0.0000";
                    $data['LaborCost']['b_person_total']	= "0.0000";
                    $data['LaborCost']['labor_unit']    	= "0";
                    $data['LaborCost']['corp_unit']    	= "0";
                    $data['LaborCost']['yearly_labor_cost'] = "0";
                    $data['LaborCost']['unit_labor_cost']  	= "0";
                    $data['LaborCost']['adjust_labor_cost']	= "0";
                    $data['LaborCost']['yearly_corpo_cost']	= "0";
                    $data['LaborCost']['unit_corpo_cost']   = "0";
                    $data['LaborCost']['adjust_corpo_cost'] = "0";
                    $data['LaborCost']['adjust_name']       = null;
                    $personnel_cost = $user['Position']['personnel_cost'];
                    $corporate_cost = $user['Position']['corporate_cost'];
                }   
                $data['LaborCost']['hid_unit_labor_cost']  	= $personnel_cost;
                $data['LaborCost']['hid_unit_corpo_cost']  	= $corporate_cost;      
                //return $data;
                array_push($result_data_list,$data);
            }
        }
        $laborCostNewRows = $this->LaborCost->find('all', [
            'conditions' => [
                'LaborCost.target_year' => $search_data['target_year'],
                'LaborCost.layer_code' => $search_data['layer_code'],
                array('LaborCost.user_id = "0" AND LaborCost.new_user_name IS NOT NULL')
            ],
            'joins' =>[
                [
                    'table' => 'positions',
                    'alias' => 'Position',
                    'type'  =>  'left',
                    'conditions' => [
                        'Position.position_code = LaborCost.position_code',
                        'Position.target_year' => $search_data['target_year']

                    ]                
                ],
            ],
            'fields' => [
                'LaborCost.*', 
                'Position.position_name',
                'Position.personnel_cost',
                'Position.corporate_cost',
                'Position.position_code',
            ],
            'group' => ["LaborCost.id"],
            'order' => ["LaborCost.id"],
        ]);
        foreach($laborCostNewRows as $key=>$value){
            $person_count       = $value['LaborCost']['person_count'];
            $personnel_cost = $value['Position']['personnel_cost']; 
            $corporate_cost = $value['Position']['corporate_cost'];
            $laborCostNewRows[$key]['LaborCost']['hid_unit_labor_cost']  	= $personnel_cost;
            $laborCostNewRows[$key]['LaborCost']['hid_unit_corpo_cost']  	= $corporate_cost;   
            $laborCostNewRows[$key]['LaborCost']['labor_unit'] = $personnel_cost * $person_count;
            $laborCostNewRows[$key]['LaborCost']['b_person_total'] = $person_count + $value['LaborCost']['common_expense'];
            $laborCostNewRows[$key]['LaborCost']['corp_unit'] = $corporate_cost * ($person_count + $value['LaborCost']['common_expense']);

            $adjust_labor_cost  = $value['LaborCost']['adjust_labor_cost'];
            $adjust_corpo_cost  = $value['LaborCost']['adjust_corpo_cost'];
            $laborCostNewRows[$key]['LaborCost']['yearly_labor_cost']  = (round($laborCostNewRows[$key]['LaborCost']['labor_unit'],4) * 12) + $adjust_labor_cost;
            if($person_count == 0) {
                $laborCostNewRows[$key]['LaborCost']['unit_labor_cost'] = 0;
            }else {
                $laborCostNewRows[$key]['LaborCost']['unit_labor_cost']  = $laborCostNewRows[$key]['LaborCost']['yearly_labor_cost'] / $person_count / 12;
            }
            $laborCostNewRows[$key]['LaborCost']['yearly_corpo_cost']  = ($laborCostNewRows[$key]['LaborCost']['corp_unit'] * 12) + $adjust_corpo_cost;
            if($person_count == 0) {
                $laborCostNewRows[$key]['LaborCost']['unit_corpo_cost'] = 0;
            }else {
                $laborCostNewRows[$key]['LaborCost']['unit_corpo_cost']  = $laborCostNewRows[$key]['LaborCost']['yearly_corpo_cost'] / $person_count / 12;
            }
        }
        //for Adjust user_id=0,position_id!=0,user!=''
        if(!empty($laborCostNewRows)){
            $adjust_data = array();
            foreach($laborCostNewRows as $new_row){
                $adjust_data['User']['id'] = 0;
                $adjust_data['User']['user_name'] = $new_row['LaborCost']['new_user_name'];
                $adjust_data['Position']['position_name'] = $new_row['Position']['position_name'];
                $adjust_data['Position']['personnel_cost'] = $new_row['Position']['personnel_cost'];
                $adjust_data['Position']['corporate_cost'] = $new_row['Position']['corporate_cost'];
                $adjust_data['LaborCost']['position_code'] = $new_row['Position']['position_code'];
                $adjust_data['LaborCost']['person_count'] = $new_row['LaborCost']['person_count'];
                $adjust_data['LaborCost']['b_person_count'] = $new_row['LaborCost']['b_person_count'];
                $adjust_data['LaborCost']['common_expense'] = $new_row['LaborCost']['common_expense'];   
                $adjust_data['LaborCost']['b_person_total'] = $new_row['LaborCost']['b_person_total'];   
                $adjust_data['LaborCost']['labor_unit'] = round($new_row['LaborCost']['labor_unit']);        
                $adjust_data['LaborCost']['corp_unit'] = round($new_row['LaborCost']['corp_unit']);     
                $adjust_data['LaborCost']['yearly_labor_cost'] = round($new_row['LaborCost']['yearly_labor_cost']);
                $adjust_data['LaborCost']['unit_labor_cost'] = round($new_row['LaborCost']['unit_labor_cost']);   
                $adjust_data['LaborCost']['adjust_labor_cost'] = round($new_row['LaborCost']['adjust_labor_cost']); 
                $adjust_data['LaborCost']['yearly_corpo_cost'] = round($new_row['LaborCost']['yearly_corpo_cost']); 
                $adjust_data['LaborCost']['unit_corpo_cost'] = round($new_row['LaborCost']['unit_corpo_cost']);   
                $adjust_data['LaborCost']['adjust_corpo_cost'] = round($new_row['LaborCost']['adjust_corpo_cost']); 
                $adjust_data['LaborCost']['adjust_name'] = $new_row['LaborCost']['adjust_name'];
                $adjust_data['LaborCost']['labor_id'] = $new_row['LaborCost']['id'];
                $adjust_data['LaborCost']['hid_unit_labor_cost']   = $new_row['Position']['personnel_cost'];
                $adjust_data['LaborCost']['hid_unit_corpo_cost']   = $new_row['Position']['corporate_cost'];

                array_push($result_data_list,$adjust_data);
            }
        }
        //for Adjust user_id=0,position_id=0
        $labor_cost_adjust = $this->LaborCost->getLaborCostData($search_data,$user_id=0, $buTermId, $position_id=0);
        if(!empty($labor_cost_adjust)){
            $adjust_data = array();
            foreach($labor_cost_adjust as $adjust){
                $adjust_data['User']['id']                 	    = 0;
                $adjust_data['User']['user_name']          	    = null;
                $adjust_data['Position']['position_name']		= null;
                $adjust_data['Position']['personnel_cost']		= 0;
                $adjust_data['Position']['corporate_cost']		= 0;
                // $adjust_data['LaborCost']['position_id']   	    = 0;
                $adjust_data['LaborCost']['position_code']   	    = 0;
                $adjust_data['LaborCost']['person_count']		= $adjust['LaborCost']['person_count'];		
                $adjust_data['LaborCost']['b_person_count']	   	= $adjust['LaborCost']['b_person_count'];	
                $adjust_data['LaborCost']['common_expense']	   	= $adjust['LaborCost']['common_expense'];	
                $adjust_data['LaborCost']['b_person_total']		= $adjust['LaborCost']['b_person_total'];	
                $adjust_data['LaborCost']['labor_unit']    		= round($adjust['LaborCost']['labor_unit']);    	
                $adjust_data['LaborCost']['corp_unit']    		= round($adjust['LaborCost']['corp_unit']);    	
                $adjust_data['LaborCost']['yearly_labor_cost'] 	= round($adjust['LaborCost']['yearly_labor_cost']);
                $adjust_data['LaborCost']['unit_labor_cost']  	= round($adjust['LaborCost']['unit_labor_cost']); 	
                $adjust_data['LaborCost']['adjust_labor_cost']	= round($adjust['LaborCost']['adjust_labor_cost']);	
                $adjust_data['LaborCost']['yearly_corpo_cost']	= round($adjust['LaborCost']['yearly_corpo_cost']);	
                $adjust_data['LaborCost']['unit_corpo_cost']   	= round($adjust['LaborCost']['unit_corpo_cost']);   
                $adjust_data['LaborCost']['adjust_corpo_cost'] 	= round($adjust['LaborCost']['adjust_corpo_cost']); 
                $adjust_data['LaborCost']['adjust_name']       	= $adjust['LaborCost']['adjust_name'];
                $adjust_data['LaborCost']['labor_id']        = $adjust['LaborCost']['id'];       
                array_push($result_data_list,$adjust_data);
            }
        }
        return $result_data_list;
    }
    public function showPersonalBudgetList(){
        $this->layout = 'buanalysis';
        $errormsg   = "";
        $successMsg = "";
        $search_data      = array();
        $result_data_list = array();
        $buTermId = $_SESSION['BU_TERM_ID'];
         #get language extension
		if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}
        // $current_yr = date("Y");
        $current_yr = $_SESSION['BudgetTargetYear'];

        if ($this->Session->check('YEAR_LIST')) {
            $year_list = $this->Session->read('YEAR_LIST');
        }
        // $layer_code_list = $this->Layer->getLayerCodeList($year_list[$this->request->data['target_yr']]);
        
        //for target yr and position drop down
        if ($this->request->is('POST')){            
            $target_year            = $this->request->data['target_yr'];
            #get required params
            $login_id   = $this->Session->read('LOGIN_ID');
            $role_id    = $this->Session->read('ADMIN_LEVEL_ID');
            $selectedTermYear = $_SESSION['yearListOnTerm'][$target_year];
            $start_month = $selectedTermYear[0];
            $end_month = $selectedTermYear[1];
            $page_name   = $this->request->params['controller'];
            $Common = new CommonController();
            $permissions = $Common->getPermissionsByRoleForBU($login_id, $role_id, $start_month, $end_month, $page_name);
            $layer_code_list = $this->Layer->getLayerCodeListByPermissions(implode("','",  $permissions['index']['layer']));
            $lcode_list = array_column(array_column($layer_code_list, 'Layer'), 'layer_code');
            $selected_layer_code    = (empty($this->request->data['layer_code']) || !in_array($this->request->data['layer_code'],$lcode_list))?$layer_code_list[0]['Layer']['layer_code']:$this->request->data['layer_code'];
            $search_data = array (
                'target_year' => $target_year,
                'layer_code'  => $selected_layer_code
            );
            $this->Session->write('SEARCH_LABOR_COST',$search_data);
        }
        else{
            if ($this->Session->check('SEARCH_LABOR_COST')) {
                $search_data = $this->Session->read('SEARCH_LABOR_COST');
            }
        }
        // $user_list = $this->LaborCostDetail->checkUser($search_data['target_year'],$search_data['layer_code']);
        $user_list = $this->LaborCostDetail->checkUser($year_list[$search_data['target_year']],$search_data['layer_code'], $buTermId);
        
        $prepared_data = $this->preparedData($user_list,$search_data);
        
        if(!empty($prepared_data)){
            $result_data_list = $prepared_data;
        }
        if(empty($result_data_list) || empty($layer_code_list)){
            $errormsg = parent::getErrorMsg('SE153');
        }
        $comment = $this->LcComment->find('first', array(
            'fields' => ['LcComment.id', 'LcComment.comment', 'LcComment.updated_date' ,'users.user_name'],
            'conditions' => [
                'LcComment.bu_term_id' => $_SESSION['BU_TERM_ID'],
                'LcComment.target_year' => $search_data['target_year'],
                'LcComment.layer_code' => $search_data['layer_code'],
                'LcComment.page_name' => 'LaborCosts'
            ],
            'joins' => [
                [
                    'table' => 'users',
                    'type'  =>  'left',
                    'conditions' => [
                        'users.id = LcComment.updated_by'
                    ]                
                ]
            ],
        ));
        !empty($comment) ? $comment['LcComment']['comment'] = nl2br($comment['LcComment']['comment']) : $comment;

        #read only value to lock
        #bu term id
        $buTermId = $_SESSION['BU_TERM_ID'];
        #get menu id
        $Common = new CommonController();
        $menu_id = $Common->getMenuId($this);
        if(!empty($menu_id)){
            $approved_flag =  $this->BuApprovedLog->find('first', array(
                'fields' => array('flag'),
                'conditions' => array(
                    'menu_id' => $menu_id,
                    'bu_term_id' => $buTermId,
                    'target_year' => $search_data['target_year'],
                    'group_code' => $search_data['layer_code'],
                    'line_code' => '0',
                    'business_code' => '0',
                    'sub_business_code' => '0'
                )
            ))['BuApprovedLog']['flag'] ?? 1;
            $completed_flag =  $this->BuApprovedLog->find('first', array(
                'fields' => array('flag'),
                'conditions' => array(
                    'menu_id' => Setting::MENU_ID_LIST['BudgetResult'],
                    'bu_term_id' => $buTermId,
                    'group_code' => $search_data['layer_code'],
                    // 'line_code' => '0',
                    // 'business_code' => '0',
                    // 'sub_business_code' => '0',
                )
            ))['BuApprovedLog']['flag'] ?? 1;
        }
        #get show/hide limits
        $readLimit = $this->name.'ReadLimit';
        $commentLimit = $this->name.'CommentLimit';
        $saveLimit = $this->name.'SaveLimit';
        $confirmLimit = $this->name.'ConfirmLimit';
        $confirmCancelLimit = $this->name.'Confirm_cancelLimit';
        
        if($this->Session->check('PAGE_LIMITATION')){
            $page_limitation = $this->Session->read('PAGE_LIMITATION');
            $showReadBtn = ($page_limitation[$readLimit] !== false) ? true : false;
            $showCommentBtn = ($page_limitation[$commentLimit] !== false) ? true : false;
            $showSaveBtn = ($page_limitation[$saveLimit] !== false) ? true : false;
            $showConfirmBtn = ($page_limitation[$confirmLimit] !== false) ? true : false;
            $showConfirmCancelBtn = ($page_limitation[$confirmCancelLimit] !== false) ? true : false;
        }

        #check layer code exists or not
        list($disabledCommentBtn, $disabledSaveBtn, $disabledConfirmBtn, $disabledConfirmCancelBtn) = $this->checkLayerCodeExist();
        $old_data = json_encode($result_data_list);

        $this->set(compact('current_yr','year_list','layer_code_list','search_data', 'errormsg','successMsg','lang_name','result_data_list','comment', 'approved_flag', 'completed_flag', 'showReadBtn', 'showCommentBtn', 'showSaveBtn', 'showConfirmBtn', 'showConfirmCancelBtn', 'disabledCommentBtn', 'disabledSaveBtn', 'disabledConfirmBtn', 'disabledConfirmCancelBtn', 'old_data'));
        $this->render('index');
    }
     /**
     * get salary data by position change
     * @author SST 17.8.2022
     * @return data
     */
    public function getPositionSalary(){
        $this->autoRender = false; 			
		$this->request->allowMethod('ajax'); 
		$layout = ''; 
        $person_count =0;
        // $position_id = $this->request->data['position_id'] ;
        $position_code = $this->request->data['position_code'] ;
        $target_year = $this->request->data['target_year'] ;
        $user_id     = $this->request->data['user_id'] ;
        
        $position_salary                = array();
        $detail_person_count            = array();
        // $position_salary = $this->Position->getPositionBudgetData($position_id,$target_year);
        $position_salary = $this->Position->getPositionBudgetData($position_code,$target_year);
        $detail_person_count = $this->LaborCostDetail->getPersonCount($target_year,$user_id);
        if(!empty($detail_person_count)){
            $person_count = $detail_person_count[0][0]['total_person_count'];
        }
        $positionConstant =array();
        $p_name = $position_salary[0]['Position']['position_name'];//use 0index bcoz 1user has 1 position
        $positionConstant = PositionType::PositionConstant[$p_name];
        if($positionConstant !=null || $positionConstant !=""){
            $positionConstant=0.5;
        }else{
            $positionConstant=1;
        }
        $data = array(
			'position_salary_content' => $position_salary,
            'person_count_from_detail' => $person_count,
            'position_constant' => $positionConstant
		);
		echo json_encode ($data);
    }
     /**
     * save 
     * @author SST 17.8.2022
     * @return message
     */
    public function savePersonalBudget(){
        $successMsg ="";
        $result_data_list = array();
        $request_data = array();
        // if ($this->Session->check('RESULT_DATA_LIST')) {
        //     $result_data_list = $this->Session->read('RESULT_DATA_LIST');
        // }
        $request_data = $this->request->data;
        $result_data_list = json_decode($request_data['old_data'], true);
        $buTermId = $_SESSION['BU_TERM_ID'];
        $search_data = array (
            'target_year' => $request_data['target_yr'],
            'layer_code'  => $request_data['layer_code']
        );
        //$this->Session->write('SEARCH_LABOR_COST',$search_data);
        #get complete flag
        $completed_flag =  $this->BuApprovedLog->find('first', array(
            'fields' => array('flag'),
            'conditions' => array(
                'menu_id' => Setting::MENU_ID_LIST['LaborCosts'],
                'bu_term_id' => $buTermId,
                'department_code' => '0',
                'bu_code' => '0',
                'target_year' => $search_data['target_year'],
                'group_code' => $search_data['layer_code'],
                'line_code' => '0',
                'business_code' => '0',
                'sub_business_code' => '0',
            )
        ))['BuApprovedLog']['flag'];
        if($completed_flag == 2){
            $errorMsg = parent::getErrorMsg('SE161');
            $this->Flash->error($errorMsg, array('key'=>'lc_error'));
            $this->redirect(array("action" => "index"));
        }
        $adj_name_arr = array();
        foreach($result_data_list as $data_list){
            $user_id = $data_list['User']['id'];
            $adjust_name= $data_list['LaborCost']['adjust_name'];
            $adjust_ctn=0;
            $user = $data_list['User']['user_name'];

            // only for adjustment
            if($user_id == 0 && $data_list['LaborCost']['adjust_name'] != '0' && $data_list['LaborCost']['adjust_name'] != '' && $data_list['LaborCost']['adjust_name'] != NULL){
                $adj_name_arr[] = $data_list;
            }else{
                $this->chekAndSaveUPdate($request_data,$user_id,$adjust_name,$adjust_ctn,$data_list, $user);
            }
            // $position_code = isset($request_data[$user_id.'_position_list'])?$request_data[$user_id.'_position_list']:'0';            
           
            // $old_position_code = $old_labor_cost_detail_position[0]['labor_cost_details']['position_code'];
            
            // if($position_code != $old_position_code){
                          
                // $position_code = isset($request_data[$user_id.'_position_list'])?$request_data[$user_id.'_position_list']:'0';
                
                // $old_position_code = $old_labor_cost_detail_position[0]['labor_cost_details']['position_code'];

                // if($position_code != $old_position_code){
                //     $this->LaborCostDetail->updatePositionId($position_code,$request_data['target_yr'],$user_id);
                // }
            // }
        }

        $hd_other_adjust_ctn = $request_data['hd_other_adjust_ctn'];
        for ($adjust_ctn =0; $adjust_ctn <=$hd_other_adjust_ctn; $adjust_ctn++) {//start from 0 to include original adjust
            #get old data
            $old_data = $adj_name_arr[$adjust_ctn];
            $adjust_user_id='0';
            if($adjust_ctn==0){//original adjust
                $adjust_name = $request_data['adjust_name'];     
            }
            else{//other adjust
                $adjust_name = trim($request_data[$adjust_user_id.$adjust_ctn.'_otherAdjustName']);                                      
            }
            if($adjust_name !=null && $adjust_name !="" && $adjust_name !="0"){ 
                $this->chekAndSaveUPdate($request_data,$adjust_user_id,$adjust_name,$adjust_ctn,$old_data); 
            }else{
                $json_data = json_decode($request_data['json_data'], true);
                #remove latest index bcoz message added
                array_pop($json_data);
                // $update = $this->LaborCost->updateAll(
                //     array('flag' => 0),
                //     array('id' => $json_data)
                // );
               
            }
        }
        #check save or confirm
        $approved_flag = $this->request->data('approved_flag');

        #get menu id
        $Common = new CommonController();
        $menu_id = $Common->getMenuId($this);

        if($approved_flag == 1){
            $successMsg = parent::getSuccessMsg('SS001');
        }elseif($approved_flag == 2){
            $successMsg = parent::getSuccessMsg('SS035');
        }

        #save into bu_approved_logs
        $get_log_id =  $this->BuApprovedLog->find('first', array(
            'fields' => array('id'),
            'conditions' => array(
                'menu_id' => $menu_id,
                'bu_term_id' => $buTermId,
                'target_year' => $search_data['target_year'],
                'department_code' => '0',
                'bu_code' => '0',
                'group_code' => $search_data['layer_code'],
                'line_code' => '0',
                'business_code' => '0',
                'sub_business_code' => '0',
            )
        ))['BuApprovedLog']['id'];
        if(empty($get_log_id) && $approved_flag == 2){
            $this->BuApprovedLog->save(
                [
                    'menu_id' => $menu_id,
                    'bu_term_id' => $buTermId,
                    'target_year' => $search_data['target_year'],
                    'department_code' => '0',
                    'bu_code' => '0',
                    'group_code' => $search_data['layer_code'],
                    'line_code' => '0',
                    'business_code' => '0',
                    'sub_business_code' => '0',
                    'flag' => $approved_flag,
                    'created_by' => $this->Session->read('LOGIN_ID'),
                    'updated_by' => $this->Session->read('LOGIN_ID'),
                ]
            );
        }elseif(!empty($get_log_id) && $approved_flag == 2){
            $this->BuApprovedLog->save(
                [
                    'id' => $get_log_id,
                    'menu_id' => $menu_id,
                    'bu_term_id' => $buTermId,
                    'target_year' => $search_data['target_year'],
                    'department_code' => '0',
                    'bu_code' => '0',
                    'group_code' => $search_data['layer_code'],
                    'line_code' => '0',
                    'business_code' => '0',
                    'sub_business_code' => '0',
                    'flag' => $approved_flag,
                    'updated_by' => $this->Session->read('LOGIN_ID'),
                ]
            );

        }
        // $this->Session->write('LABOR_COST_SUCCESS_MSG', $successMsg);
        $this->Flash->success($successMsg, array('key'=>'lc_success'));
        $this->redirect(array("action" => "index"));
    }
    /**
     * check data and save/update 
     * @author SST 17.8.2022
     */
    public function chekAndSaveUPdate($request_data,$user_id,$adjust_name,$adjust_ctn,$old_data=null, $userName = null){

        $buTermId = $_SESSION['BU_TERM_ID'];
        $save_type = array_column(json_decode($request_data['json_data'], true),'save_type')[0];
        $current_time = date('Y-m-d H:i:s', time());       
        if ($this->Session->check('LOGIN_ID')) {
            $login_user_id = $this->Session->read('LOGIN_ID');
        }else{
            $login_user_id =1;
        }
        $updated_data = $this->LaborCost->getExistDataByTargetYrAndUserId($request_data['target_yr'],$request_data['layer_code'],$user_id,$adjust_name, $buTermId, $userName);
        if(!empty($updated_data)){//already exist data overwrite
            $id = $updated_data[0]['LaborCost']['id'];
            $save_budget_data['id']                 = $id;
            $save_budget_data['created_by']         = $updated_data[0]['LaborCost']['created_by'];//must be login user id
            $save_budget_data['created_date']       = $updated_data[0]['LaborCost']['created_date'];
            
        }else{
            $save_budget_data['created_by']         = $login_user_id;//must be login user id
            $save_budget_data['created_date']       = $current_time;
        }
        if($adjust_name == '' || $adjust_name ==null || $adjust_name == '0' || $adjust_name =="異動による差異調整"){
            $input_id    = $user_id;

        }else{//other adjust
            $input_id       = $user_id.$adjust_ctn;
        }
        if($user_id == '0' && $updated_data[0]['LaborCost']['new_user_name']!='') {
            $input_id    = "Labor".$updated_data[0]['LaborCost']['id'];
        }
        if($input_id!=null && $input_id!="" && $save_type == 'merge'){
            // $position_id = isset($request_data[$input_id.'_position_list'])?$request_data[$input_id.'_position_list']:'0';            
            $position_code = isset($request_data[$input_id.'_position_list'])?$request_data[$input_id.'_position_list']:'0';   
            $save_budget_data['target_year']           = $request_data['target_yr'];
            // $save_budget_data['position_id']           = $position_id;
            $save_budget_data['position_code']           = $position_code;
            $save_budget_data['user_id']               = ($user_id !=null || $user_id !="")?$user_id:'0';
            $save_budget_data['layer_code']            = $request_data['layer_code'];
            if($adjust_name == '' || $adjust_name ==null || $adjust_name == '0'){
               
                $save_budget_data['adjust_name'] = NULL;

                $save_budget_data['person_count'] = ($old_data['LaborCost']['person_count'] != $request_data[$input_id.'_personCount'])?($request_data[$input_id.'_personCount']!=null || $request_data[$input_id.'_personCount']!="")?str_replace( ',', '',$request_data[$input_id.'_personCount']):'0' : $updated_data[0]['LaborCost']['person_count'];

                $save_budget_data['b_person_count'] = ($old_data['LaborCost']['b_person_count'] != $request_data[$input_id.'_bPersonCount'])?(($request_data[$input_id.'_bPersonCount']!=null || $request_data[$input_id.'_bPersonCount']!="")?str_replace( ',', '',$request_data[$input_id.'_bPersonCount']):'0'):$updated_data[0]['LaborCost']['b_person_count'];

                $save_budget_data['common_expense'] = ($old_data['LaborCost']['common_expense'] != $request_data[$input_id.'_commonExpense'])?($request_data[$input_id.'_commonExpense']!=null || $request_data[$input_id.'_commonExpense']!="")?str_replace( ',', '',$request_data[$input_id.'_commonExpense']):'0':$updated_data[0]['LaborCost']['common_expense'];

                $save_budget_data['b_person_total'] = $save_budget_data['b_person_count']+$save_budget_data['common_expense'];

                $save_budget_data['labor_unit'] = round($save_budget_data['person_count']*$old_data['Position']['personnel_cost'],4);

                $save_budget_data['corp_unit'] = $save_budget_data['b_person_total']*$old_data['Position']['corporate_cost'];

                $save_budget_data['adjust_labor_cost'] = ($old_data['LaborCost']['adjust_labor_cost'] != $request_data[$input_id.'_adjustLaborCost'])?($request_data[$input_id.'_adjustLaborCost']!=null || $request_data[$input_id.'_adjustLaborCost']!="")?str_replace( ',', '',$request_data[$input_id.'_adjustLaborCost']):'0':$updated_data[0]['LaborCost']['adjust_labor_cost'];

                $save_budget_data['yearly_labor_cost'] = ($save_budget_data['labor_unit']*12)+$save_budget_data['adjust_labor_cost'];

                $save_budget_data['unit_labor_cost'] = ($save_budget_data['person_count']!= 0)?($save_budget_data['yearly_labor_cost']/$save_budget_data['person_count'])/12:0;

                $save_budget_data['adjust_corpo_cost']     = ($old_data['LaborCost']['adjust_corpo_cost'] != $request_data[$input_id.'_adjustCorpoCost'])?($request_data[$input_id.'_adjustCorpoCost']!=null || $request_data[$input_id.'_adjustCorpoCost']!="")?str_replace( ',', '',$request_data[$input_id.'_adjustCorpoCost']):'0':$updated_data[0]['LaborCost']['adjust_corpo_cost'];

                $save_budget_data['yearly_corpo_cost'] = ($save_budget_data['corp_unit']*12)+$save_budget_data['adjust_corpo_cost'];

                $save_budget_data['unit_corpo_cost'] =  ($save_budget_data['person_count']!= 0)?($save_budget_data['yearly_corpo_cost']/$save_budget_data['person_count'])/12:0;

            }elseif($adjust_name != '' && $adjust_name !=null && $adjust_name !='0'){
                if($old_data == null){
                    $old_data['LaborCost']['person_count'] = 0;
                    $old_data['LaborCost']['b_person_count'] = 0;
                    $old_data['LaborCost']['common_expense'] = 0;
                    $old_data['LaborCost']['b_person_total'] = 0;
                    $old_data['LaborCost']['labor_unit'] = 0;
                    $old_data['LaborCost']['corp_unit'] = 0;
                    $old_data['LaborCost']['adjust_labor_cost'] = 0;
                    $old_data['LaborCost']['yearly_labor_cost'] = 0;
                    $old_data['LaborCost']['unit_labor_cost'] = 0;
                    $old_data['LaborCost']['adjust_corpo_cost'] = 0;
                    $old_data['LaborCost']['yearly_corpo_cost'] = 0;
                    $old_data['LaborCost']['unit_corpo_cost'] = 0;
                }
                $save_budget_data['adjust_name'] = ($old_data['LaborCost']['adjust_name'] != $adjust_name)?($adjust_name!=null || $adjust_name!="")?$adjust_name:NULL:$updated_data[0]['LaborCost']['adjust_name'];
                
                $save_budget_data['person_count'] = ($old_data['LaborCost']['person_count'] != $request_data[$input_id.'_personCount'])?($request_data[$input_id.'_personCount']!=null || $request_data[$input_id.'_personCount']!="")?str_replace( ',', '',$request_data[$input_id.'_personCount']):'0' : $updated_data[0]['LaborCost']['person_count'];

                // $save_budget_data['person_count'] = '0';

                $save_budget_data['b_person_count'] = ($old_data['LaborCost']['b_person_count'] != $request_data[$input_id.'_bPersonCount'])?(($request_data[$input_id.'_bPersonCount']!=null || $request_data[$input_id.'_bPersonCount']!="")?str_replace( ',', '',$request_data[$input_id.'_bPersonCount']):'0'):$updated_data[0]['LaborCost']['b_person_count'];

                $save_budget_data['common_expense'] = ($old_data['LaborCost']['common_expense'] != $request_data[$input_id.'_commonExpense'])?($request_data[$input_id.'_commonExpense']!=null || $request_data[$input_id.'_commonExpense']!="")?str_replace( ',', '',$request_data[$input_id.'_commonExpense']):'0':$updated_data[0]['LaborCost']['common_expense'];

                $save_budget_data['b_person_total'] = ($old_data['LaborCost']['b_person_total'] != $request_data[$input_id.'_bPersonTotal'])?($request_data[$input_id.'_bPersonTotal'] !=null || $request_data[$input_id.'_bPersonTotal']!="")?str_replace( ',', '',$request_data[$input_id.'_bPersonTotal']):'0':$updated_data[0]['LaborCost']['b_person_total'];

                
                $save_budget_data['labor_unit'] = ($old_data['LaborCost']['labor_unit'] != $request_data[$input_id.'_laborUnit'])?($request_data[$input_id.'_laborUnit']!=null || $request_data[$input_id.'_laborUnit']!="")?str_replace( ',', '',$request_data[$input_id.'_laborUnit']):'0':$updated_data[0]['LaborCost']['labor_unit'];
                
               
                $save_budget_data['corp_unit'] = ($old_data['LaborCost']['corp_unit'] != $request_data[$input_id.'_corpoUnit'])?($request_data[$input_id.'_corpoUnit']!=null || $request_data[$input_id.'_corpoUnit']!="")?str_replace( ',', '',$request_data[$input_id.'_corpoUnit']):'0':$updated_data[0]['LaborCost']['corp_unit'];
                

                $save_budget_data['adjust_labor_cost'] = ($old_data['LaborCost']['adjust_labor_cost'] != $request_data[$input_id.'_adjustLaborCost'])?($request_data[$input_id.'_adjustLaborCost']!=null || $request_data[$input_id.'_adjustLaborCost']!="")?str_replace( ',', '',$request_data[$input_id.'_adjustLaborCost']):'0':$updated_data[0]['LaborCost']['adjust_labor_cost'];

                $save_budget_data['yearly_labor_cost'] = ($old_data['LaborCost']['yearly_labor_cost'] != $request_data[$input_id.'_yearlyLaborCost'])?($request_data[$input_id.'_yearlyLaborCost']!=null || $request_data[$input_id.'_yearlyLaborCost']!="")?str_replace( ',', '',$request_data[$input_id.'_yearlyLaborCost']):'0':$updated_data[0]['LaborCost']['yearly_labor_cost'];

                $save_budget_data['unit_labor_cost'] = ($old_data['LaborCost']['unit_labor_cost'] != $request_data[$input_id.'_unitLaborCost'])?($request_data[$input_id.'_unitLaborCost']!=null || $request_data[$input_id.'_unitLaborCost']!="")?str_replace( ',', '',$request_data[$input_id.'_unitLaborCost']):'0':$updated_data[0]['LaborCost']['unit_labor_cost'];

                $save_budget_data['adjust_corpo_cost'] = ($old_data['LaborCost']['adjust_corpo_cost'] != $request_data[$input_id.'_adjustCorpoCost'])?($request_data[$input_id.'_adjustCorpoCost']!=null || $request_data[$input_id.'_adjustCorpoCost']!="")?str_replace( ',', '',$request_data[$input_id.'_adjustCorpoCost']):'0':$updated_data[0]['LaborCost']['adjust_corpo_cost'];

                $save_budget_data['yearly_corpo_cost'] = ($old_data['LaborCost']['yearly_corpo_cost'] != $request_data[$input_id.'_yearlyCorpoCost'])?($request_data[$input_id.'_yearlyCorpoCost']!=null || $request_data[$input_id.'_yearlyCorpoCost']!="")?str_replace( ',', '',$request_data[$input_id.'_yearlyCorpoCost']):'0':$updated_data[0]['LaborCost']['yearly_corpo_cost'];
                
                $save_budget_data['unit_corpo_cost'] = ($old_data['LaborCost']['unit_corpo_cost'] != $request_data[$input_id.'_unitCorpoCost'])?($request_data[$input_id.'_unitCorpoCost']!=null || $request_data[$input_id.'_unitCorpoCost']!="")?str_replace( ',', '',$request_data[$input_id.'_unitCorpoCost']):'0':$updated_data[0]['LaborCost']['unit_corpo_cost'];
            }
            $save_budget_data['bu_term_id']            = $buTermId;
            $save_budget_data['flag']                  = "1";
            $save_budget_data['updated_by']            = $login_user_id;
            $save_budget_data['updated_date']          = $current_time;
            #database not allow empty
            if(in_array('',$save_budget_data)){
                $save_budget_data = array_replace($save_budget_data,
                array_fill_keys(
                    array_keys($save_budget_data, ''),
                    '0'
                )
            );
            }
              
            $this->LaborCost->saveAll($save_budget_data);
        }elseif($input_id!=null && $input_id!="" && $save_type == 'overwrite'){
            $save_budget_data['bu_term_id']            = $buTermId;
            // $position_id = isset($request_data[$input_id.'_position_list'])?$request_data[$input_id.'_position_list']:'0';            
            $position_code = isset($request_data[$input_id.'_position_list'])?$request_data[$input_id.'_position_list']:'0';            
            $save_budget_data['target_year']           = $request_data['target_yr'];
            // $save_budget_data['position_id']           = $position_id;
            $save_budget_data['position_code']           = $position_code;
            $save_budget_data['user_id']               = ($user_id !=null || $user_id !="")?$user_id:'0';
            $save_budget_data['layer_code']            = $request_data['layer_code'];
            $save_budget_data['adjust_name']           = ($adjust_name!=null || $adjust_name!="")?$adjust_name:NULL;
            //$save_budget_data['person_count']          = '0';
            $save_budget_data['person_count']          = ($request_data[$input_id.'_personCount']!=null || $request_data[$input_id.'_personCount']!="")?str_replace( ',', '',$request_data[$input_id.'_personCount']):'0';
            $save_budget_data['b_person_count']        = ($request_data[$input_id.'_bPersonCount']!=null || $request_data[$input_id.'_bPersonCount']!="")?str_replace( ',', 'a',$request_data[$input_id.'_bPersonCount']):'0';
            $save_budget_data['common_expense']        = ($request_data[$input_id.'_commonExpense']!=null || $request_data[$input_id.'_commonExpense']!="")?str_replace( ',', '',$request_data[$input_id.'_commonExpense']):'0';
            $save_budget_data['b_person_total']        = ($request_data[$input_id.'_bPersonTotal'] !=null || $request_data[$input_id.'_bPersonTotal']!="")?str_replace( ',', '',$request_data[$input_id.'_bPersonTotal']):'0';
            $save_budget_data['labor_unit']            = ($request_data[$input_id.'_laborUnit']!=null || $request_data[$input_id.'_laborUnit']!="")?str_replace( ',', '',$request_data[$input_id.'_laborUnit']):'0';
            $save_budget_data['corp_unit']             = ($request_data[$input_id.'_corpoUnit']!=null || $request_data[$input_id.'_corpoUnit']!="")?str_replace( ',', '',$request_data[$input_id.'_corpoUnit']):'0';
            $save_budget_data['yearly_labor_cost']     = ($request_data[$input_id.'_yearlyLaborCost']!=null || $request_data[$input_id.'_yearlyLaborCost']!="")?str_replace( ',', '',$request_data[$input_id.'_yearlyLaborCost']):'0';
            $save_budget_data['unit_labor_cost']       = ($request_data[$input_id.'_unitLaborCost']!=null || $request_data[$input_id.'_unitLaborCost']!="")?str_replace( ',', '',$request_data[$input_id.'_unitLaborCost']):'0';
            $save_budget_data['adjust_labor_cost']     = ($request_data[$input_id.'_adjustLaborCost']!=null || $request_data[$input_id.'_adjustLaborCost']!="")?str_replace( ',', '',$request_data[$input_id.'_adjustLaborCost']):'0';
            $save_budget_data['yearly_corpo_cost']     = ($request_data[$input_id.'_yearlyCorpoCost']!=null || $request_data[$input_id.'_yearlyCorpoCost']!="")?str_replace( ',', '',$request_data[$input_id.'_yearlyCorpoCost']):'0';
            $save_budget_data['unit_corpo_cost']       = ($request_data[$input_id.'_unitCorpoCost']!=null || $request_data[$input_id.'_unitCorpoCost']!="")?str_replace( ',', '',$request_data[$input_id.'_unitCorpoCost']):'0';
            $save_budget_data['adjust_corpo_cost']     = ($request_data[$input_id.'_adjustCorpoCost']!=null || $request_data[$input_id.'_adjustCorpoCost']!="")?str_replace( ',', '',$request_data[$input_id.'_adjustCorpoCost']):'0';
            $save_budget_data['flag']                  = "1";
            $save_budget_data['updated_by']            = $login_user_id;
            $save_budget_data['updated_date']          = $current_time; 

            $this->LaborCost->saveAll($save_budget_data);
        }
    }
    /**
     * download excel file
     * @author SST 13.9.2022
     */
    public function downloadPersonalBudget(){
        $this->autoRender = false;
		$layout = '';
        $target_year            = $this->request->data['target_yr'] ;
        $selected_layer_code    = $this->request->data['layer_code'] ;
        $year_list = $this->Session->read('TERM_YEAR_LIST');
        $buTermId = $_SESSION['BU_TERM_ID'];
        if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}
        $layerName = $this->Layer->getLayerName($selected_layer_code);
        $layer_gp_name =($lang_name=='en') ? $layerName[0]['Layer']['name_en'] : $layerName[0]['Layer']['name_jp']; 

        $search_data = array (
            'target_year' => $target_year,
            'layer_code'  => $selected_layer_code
        );

        $file_name  = ('予算人員表').'_'.$selected_layer_code.$layer_gp_name;
        $sheet_name = __('予算人員表');
        // $current_yr = date("Y");
        $current_yr = $_SESSION['BudgetTargetYear'];
        // $download_year = array();
        $download_year = array_keys($year_list);
        // $download_year[]=date('Y', strtotime($current_yr.' -2 year'));
        // $download_year[]=date('Y', strtotime($current_yr.' -1 year'));
        // $download_year[]=$current_yr;
        // $download_year[]=date('Y', strtotime($current_yr.' +1 year'));
        // $download_year[]=date('Y', strtotime($current_yr.' +2 year'));
        // $download_year[]=date('Y', strtotime($current_yr.' +3 year'));
        $objWorkSheet = $this->PhpExcel->createWorksheet()->setDefaultFont('Calibri', 12);			
        $count = 0;
        $first_year = true;//to check first year or not
        foreach($download_year as $year){
            $result_data_list = array();
            $search_data = array (
                'target_year' => $year,
                'layer_code'  => $selected_layer_code
            );
            $user_list = $this->LaborCostDetail->checkUser($year_list[$search_data['target_year']],$search_data['layer_code'], $buTermId);
            
            $prepared_data = $this->preparedData($user_list,$search_data);

            if(!empty($prepared_data)){
                $result_data_list = $prepared_data;
            }
            if($count == 0) {
                // For 1st Sheet
                $this->PhpExcel->setSheetName($year.'_'.$sheet_name);
                $count++;
            } else {
                $this->PhpExcel->addSheet($year.'_'.$sheet_name);
            }
            $BStyle = array(
                    'borders' => array(
                            'outline' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                    )
            );
            $HStyle = array(
                    'borders' => array(
                            'horizontal' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                            )
            );
            
            $aligncenter = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                ));	

            $alignLeft = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                ));

            $bgColor = array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFFCC')
                )
                );

            $fontstyleArray = array(
                'font'  => array(
                    'color' => array('rgb' => 'F31515')
                ));
            if(sizeof($result_data_list) == 0){
                $sheet = $this->PhpExcel->getActiveSheet($year);                
                $sheet->setCellValue('B2', "< ".$file_name." >")
                ->setCellValue('B4', $year.__('年度'));
                $sheet->setCellValue('B6', __("計算する為のデータがシステムにありません。"));
                $sheet->mergeCells('B6:R6');
                $sheet->getStyle('B6:R6')->applyFromArray([
                    'font'  => array(
                        'size'  => 18,
                        'color' => ['rgb' => 'FF0000']
                    ),
                ]);
                
            }else{
                /** Page Setup For Print **/
                $objWorkSheet->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
                $objWorkSheet->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objWorkSheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objWorkSheet->getActiveSheet($year)->setShowGridlines(false);
                $sheet = $this->PhpExcel->getActiveSheet($year);                
                $sheet->setCellValue('B2', "< ".$file_name." >")
                ->setCellValue('B4', $year.__('年度'))
                ->setCellValue('B5','#')
                ->setCellValue('C5',__('氏名'))
                ->setCellValue('D5',__('役職'))
                ->setCellValue('E5',__('人件費'))
                ->setCellValue('F5',__('コーポレート費'))
                ->setCellValue('G5',__('人員'))
                ->setCellValue('H5',__('予算人員①'))
                ->setCellValue('I5',__('共通費 免除②'))
                ->setCellValue('J5',__('予算人員数（①+②）'))
                ->setCellValue('K5',__('人件費単価'))
                ->setCellValue('L5',__('ｺｰﾎﾟﾚｰﾄ経費割当単価'))
                ->setCellValue('M5',__('人件費（年間）'))
                ->setCellValue('N5',__('人件費単価（割戻）'))
                ->setCellValue('O5',__('差異調整'))
                ->setCellValue('P5',__('ｺｰﾎﾟﾚｰﾄ経費（年間）'))
                ->setCellValue('Q5',__('ｺｰﾎﾟﾚｰﾄ経費割当単価（割戻）'))
                ->setCellValue('R5',__('差異調整'));
                
                $objWorkSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
                $objWorkSheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(false);
                $objWorkSheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
                $objWorkSheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(false);
                $objWorkSheet->getActiveSheet()->getColumnDimension('B')->setWidth(8);
                $objWorkSheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(false);
                $objWorkSheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $objWorkSheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(false);
                $objWorkSheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $objWorkSheet->getActiveSheet()->getStyle("B2")->getFont()->setBold(true);
                $objWorkSheet->getActiveSheet()->getStyle("B4")->getFont()->setBold(true);
                $objWorkSheet->getActiveSheet()->getStyle("B5:R5")->getFont()->setBold(true);
                $objWorkSheet->getActiveSheet()->getStyle("B5:R5")->getAlignment()->setWrapText(true);
                $objWorkSheet->getActiveSheet()->getStyle("B5:R5")->applyFromArray($aligncenter);
                
                $sheet->getStyle("B5:R5")->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'bbbbbb')
                        )
                    )
                );
                
                /** cell merge for header **/
                $objWorkSheet->getActiveSheet()->mergeCells('B5:B6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('C5:C6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('D5:D6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('E5:E6'); 			
                $objWorkSheet->getActiveSheet()->mergeCells('F5:F6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('G5:G6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('H5:H6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('I5:I6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('J5:J6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('K5:K6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('L5:L6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('M5:M6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('N5:N6'); 			
                $objWorkSheet->getActiveSheet()->mergeCells('O5:O6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('P5:P6'); 
                $objWorkSheet->getActiveSheet()->mergeCells('Q5:Q6');  
                $objWorkSheet->getActiveSheet()->mergeCells('R5:R6');  

                $objWorkSheet->getActiveSheet()->getStyle("B5:R6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("B5:R6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("C5:C6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("C5:C6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("D5:D6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("D5:D6")->applyFromArray($BStyle);
                
                $objWorkSheet->getActiveSheet()->getStyle("E5:E6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("E5:E6")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("F5:F6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("F5:F6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("G5:G6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("G5:G6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("G5:G6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("H5:H6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("H5:H6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("H5:H6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("I5:I6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("I5:I6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("I5:I6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("J5:J6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("J5:J6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("J5:J6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("K5:K6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("K5:K6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("K5:K6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("L5:L6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("L5:L6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("L5:L6")->applyFromArray($BStyle);


                $objWorkSheet->getActiveSheet()->getStyle("M5:M6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("M5:M6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("M5:M6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("N5:N6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("N5:N6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("N5:N6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("N5:N6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("N5:N6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("N5:N6")->applyFromArray($HStyle);

                $objWorkSheet->getActiveSheet()->getStyle("O5:O6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("O5:O6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("O5:O6")->applyFromArray($HStyle);

                $objWorkSheet->getActiveSheet()->getStyle("P5:P6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("P5:P6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("P5:P6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("Q5:Q6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("Q5:Q6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("Q5:Q6")->applyFromArray($BStyle);

                $objWorkSheet->getActiveSheet()->getStyle("R5:R6")->applyFromArray($aligncenter);
                $objWorkSheet->getActiveSheet()->getStyle("R5:R6")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("R5:R6")->applyFromArray($BStyle);
                $row_num=7;//data fill start from B7
                $num=1;
                $other_adjust=0; $adjust=0;
                $total_budget_person_count = 0;
                $total_common_expense = 0;
                $total_budget_person = 0;
                $total_labor_unit = 0;
                $total_corporate_unit = 0;
                $total_yearly_labor_cost = 0;
                $total_unit_labor_cost = 0;
                $total_adjust_cost = 0;
                $total_yearly_corporate_cost = 0;
                $total_unit_corporate_cost = 0;
                $total_adjust_corpo_cost = 0;

                foreach($result_data_list as $list){
                    $user_id 	  		= $list['User']['id'];
                    $user_name    		= $list['User']['user_name'];									
                    // $position_id  		= $list['LaborCost']['position_id'];
                    $position_code  		= $list['LaborCost']['position_code'];
                    $position_name  	= $list['Position']['position_name'];
                    $personnel_cost  	= empty($list['LaborCost']['hid_unit_labor_cost'])? 0 : $list['LaborCost']['hid_unit_labor_cost'];
                    $corporate_cost  	= empty($list['LaborCost']['hid_unit_corpo_cost'])? 0 : $list['LaborCost']['hid_unit_corpo_cost'];
                    $person_count  		= $list['LaborCost']['person_count'];
                    $b_person_count  	= $list['LaborCost']['b_person_count'];
                    $common_expense  	= $list['LaborCost']['common_expense'];
                    $b_person_total  	= $list['LaborCost']['b_person_total'];
                    $labor_unit  		= $list['LaborCost']['labor_unit'];
                    $corpo_unit  		= $list['LaborCost']['corp_unit'];
                    $yearly_labor_cost  = $list['LaborCost']['yearly_labor_cost'];
                    $unit_labor_cost  	= $list['LaborCost']['unit_labor_cost'];
                    $adjust_labor_cost  = $list['LaborCost']['adjust_labor_cost'];
                    $yearly_corpo_cost  = $list['LaborCost']['yearly_corpo_cost'];
                    $unit_corpo_cost  	= $list['LaborCost']['unit_corpo_cost'];
                    $adjust_corpo_cost  = $list['LaborCost']['adjust_corpo_cost'];
                    $adjust_name  	    = ($list['LaborCost']['adjust_name'] == '0') ? '' : $list['LaborCost']['adjust_name'];
                    $adjust_cell_chk    = false;
                    // $cal_unit_labor_cost = $yearly_labor_cost/$person_count/12;
                    // $excel_unit_labor_cost = is_int($cal_unit_labor_cost) ? $cal_unit_labor_cost : 0;
                    // $cal_unit_corpo_cost = $yearly_corpo_cost/$person_count/12;
                    // $excel_unit_corpo_cost = is_int($cal_unit_corpo_cost) ? $cal_unit_corpo_cost : 0;

                    $total_budget_person_count += $b_person_count;
                    $total_common_expense += $common_expense;
                    $total_budget_person += $b_person_total;
                    $total_labor_unit += $personnel_cost * $person_count;
                    $total_corporate_unit += $b_person_total * $corporate_cost;
                    $total_yearly_labor_cost += $labor_unit * 12 + $adjust_labor_cost;
                    $total_unit_labor_cost += $unit_labor_cost;
                    $total_adjust_cost += $adjust_labor_cost;
                    $total_yearly_corporate_cost += $corpo_unit * 12 + $adjust_corpo_cost;
                    $total_unit_corporate_cost += $unit_corpo_cost;
                    $total_adjust_corpo_cost += $adjust_corpo_cost;
                    
                    if($adjust_name !="" && $adjust_name !='-'){//sdjust
                        $adjust_cell_chk=true;
                        $adjust=1;
                        $sheet->setCellValue('B'.$row_num, $adjust_name);
                        $objWorkSheet->getActiveSheet()->mergeCells("B$row_num:F$row_num");
                        if($adjust_name !='異動による差異調整'){
                            $other_adjust=1; 
                            if($first_year){
                                $sheet->getStyle("B$row_num:F$row_num")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                            }else{
                                $sheet->getStyle("B$row_num:F$row_num")->applyFromArray($bgColor);
                            }
                        }
                        //$objWorkSheet->getActiveSheet()->getStyle("B$row_num:F$row_num")->applyFromArray($alignLeft);
                    }else{
                        $sheet->setCellValue('B'.$row_num, $num);  //B7 num
                        $sheet->setCellValue('C'.$row_num, $user_name); 
                        
                        // if($position_id !=null && $position_id !=""){
                        if($position_code !=null && $position_code !=""){
                            $sheet->setCellValue('D'.$row_num, $position_name); 
                        }else{
                            $sheet->setCellValue('D'.$row_num, ""); 
                        }
                        // $sheet->getStyle('D'.$row_num)->applyFromArray($bgColor);
                        $sheet->setCellValue('E'.$row_num, $personnel_cost);
                        $sheet->setCellValue('F'.$row_num, $corporate_cost);  
                        if($first_year){
                            $sheet->getStyle("H$row_num")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                        }else{
                            $sheet->getStyle('H'.$row_num)->applyFromArray($bgColor);
                        }
                    } 
                    $sheet->setCellValue('G'.$row_num, $person_count,4); 
                    $sheet->setCellValue('H'.$row_num, $b_person_count,4); 
                    $sheet->setCellValue('I'.$row_num, $common_expense,4); 
                    if($first_year){
                        $sheet->getStyle("I$row_num")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                    }else{
                        $sheet->getStyle('I'.$row_num)->applyFromArray($bgColor); 
                    }
                    if($adjust_cell_chk==false){        
                        $sheet->setCellValue('J'.$row_num, $b_person_total,4);//$b_person_total予算人員数（①+②）
                        $sheet->setCellValue('K'.$row_num, $personnel_cost * $person_count, 4); //$labor_unit)=$personnel_cost * $person_count;
                        $sheet->setCellValue('L'.$row_num, $b_person_total * $corporate_cost);//$corpo_unit=$b_person_total * $corporate_cost;
                        $sheet->setCellValue('M'.$row_num, $labor_unit * 12 + $adjust_labor_cost);//$yearly_labor_cost=$labor_unit * 12 + $adjust_labor_cost;
                        //$sheet->setCellValue('M'.$row_num, "=ROUND((K$row_num*12)+O$row_num,4)");//$yearly_labor_cost=$labor_unit * 12 + $adjust_labor_cost;
                        $sheet->setCellValue('N'.$row_num, $unit_labor_cost);//$unit labour cost=$yearly_labor_cost/$person_count/12
                        //$sheet->setCellValue('N'.$row_num,'=ROUND(IFERROR(M'.$row_num.'/G'.$row_num.'/12,'."0".'),'."4".')');//$unit labour cost=$yearly_labor_cost/$person_count/12
                        $sheet->setCellValue('O'.$row_num, $adjust_labor_cost); //$adjust_labor_cost=($unit_labor_cost/$person_count/12)-$yearly_labor_cost
                        $sheet->setCellValue('P'.$row_num, $corpo_unit * 12 + $adjust_corpo_cost);//$yearly_corpo_cost=$corpo_unit * 12;                           
                        //$sheet->setCellValue('P'.$row_num, "=ROUND((L$row_num*12)+R$row_num,4)");//$yearly_corpo_cost=$corpo_unit * 12;                           
                        $sheet->setCellValue('Q'.$row_num, $unit_corpo_cost);//$unit_corpo_cost=$yearly_corpo_cost/$person_count/12
                        //$sheet->setCellValue('Q'.$row_num, '=ROUND(IFERROR(P'.$row_num.'/G'.$row_num.'/12,'."0".'),'."4".')');//$unit_corpo_cost=$yearly_corpo_cost/$person_count/12
                        $sheet->setCellValue('R'.$row_num, $adjust_corpo_cost);//$adjust_corpo_cost=($unit_corpo_cost * $person_count * 12)-$yearly_corpo_cost
                        $objWorkSheet->getActiveSheet()->getStyle('B'.$row_num)->applyFromArray($aligncenter);
                        if($first_year){
                            $sheet->getStyle("G$row_num:R$row_num")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                        }
                    }else{
                        //if adjust, no need formula
                        $sheet->setCellValue('J'.$row_num,$b_person_total);//$b_person_total予算人員数（①+②）
                        $sheet->setCellValue('K'.$row_num,$labor_unit); //$labor_unit)=$personnel_cost * $person_count;
                        $sheet->setCellValue('L'.$row_num,$corpo_unit);//$corpo_unit=$b_person_total * $corporate_cost;
                        $sheet->setCellValue('M'.$row_num,$yearly_labor_cost);//$yearly_labor_cost=$labor_unit * 12;
                        $sheet->setCellValue('N'.$row_num,$unit_labor_cost);//$unit labour cost=$yearly_labor_cost/$person_count/12
                        $sheet->setCellValue('O'.$row_num,$adjust_labor_cost); //$adjust_labor_cost=($unit_labor_cost/$person_count/12)-$yearly_labor_cost
                        $sheet->setCellValue('P'.$row_num,$yearly_corpo_cost);//$yearly_corpo_cost=$corpo_unit * 12;                           
                        $sheet->setCellValue('Q'.$row_num,$unit_corpo_cost);//$unit_corpo_cost=$yearly_corpo_cost/$person_count/12
                        $sheet->setCellValue('R'.$row_num, $adjust_corpo_cost);//$adjust_corpo_cost=($unit_corpo_cost * $person_count * 12)-$yearly_corpo_cost
                        if($first_year){
                            $sheet->getStyle("G$row_num:R$row_num")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                        }else{
                            $sheet->getStyle("G$row_num:R$row_num")->applyFromArray($bgColor);
                        }
                    }
                    if($first_year){
                        $sheet->getStyle("G$row_num:R$row_num")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                    }else{
                        $sheet->getStyle('O'.$row_num)->applyFromArray($bgColor);
                        $sheet->getStyle('R'.$row_num)->applyFromArray($bgColor);
                    }
                    

                    $objWorkSheet->getActiveSheet()->getStyle('B'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('C'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('D'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('E'.$row_num)->applyFromArray($BStyle);                
                    $objWorkSheet->getActiveSheet()->getStyle('F'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('G'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('H'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('I'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('J'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('K'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('L'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('M'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('N'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('O'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('P'.$row_num)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('Q'.$row_num)->applyFromArray($BStyle); 
                    $objWorkSheet->getActiveSheet()->getStyle('R'.$row_num)->applyFromArray($BStyle); 

                    //font color red for minus value
                    $cellValueG = $objWorkSheet->getActiveSheet()->getCell("G$row_num")->getFormattedValue();
                    $cellValueH = $objWorkSheet->getActiveSheet()->getCell("H$row_num")->getFormattedValue();
                    $cellValueI = $objWorkSheet->getActiveSheet()->getCell("I$row_num")->getFormattedValue();
                    $cellValueJ = $objWorkSheet->getActiveSheet()->getCell("J$row_num")->getFormattedValue();
                    $cellValueK = $objWorkSheet->getActiveSheet()->getCell("K$row_num")->getFormattedValue();
                    $cellValueL = $objWorkSheet->getActiveSheet()->getCell("L$row_num")->getFormattedValue();
                    $cellValueM = $objWorkSheet->getActiveSheet()->getCell("M$row_num")->getFormattedValue();
                    $cellValueN = $objWorkSheet->getActiveSheet()->getCell("N$row_num")->getFormattedValue();
                    $cellValueO = $objWorkSheet->getActiveSheet()->getCell("O$row_num")->getFormattedValue();
                    $cellValueP = $objWorkSheet->getActiveSheet()->getCell("P$row_num")->getFormattedValue();
                    $cellValueQ = $objWorkSheet->getActiveSheet()->getCell("Q$row_num")->getFormattedValue();
                    $cellValueR = $objWorkSheet->getActiveSheet()->getCell("R$row_num")->getFormattedValue();

                    $sheet->getStyle('G'.$row_num)->getNumberFormat()->setFormatCode('#,#0.0000');//4th decimal place
                    $sheet->getStyle('H'.$row_num)->getNumberFormat()->setFormatCode('#,#0.0000');//4th decimal place
                    $sheet->getStyle('I'.$row_num)->getNumberFormat()->setFormatCode('#,#0.0000');//4th decimal place
                    $sheet->getStyle('J'.$row_num)->getNumberFormat()->setFormatCode('#,#0.0000');//4th decimal place
                    $sheet->getStyle('K'.$row_num)->getNumberFormat()->setFormatCode('#,##0');//comma
                    $sheet->getStyle('L'.$row_num)->getNumberFormat()->setFormatCode('#,##0');//comma
                    $sheet->getStyle('M'.$row_num)->getNumberFormat()->setFormatCode('#,##0');//comma
                    $sheet->getStyle('N'.$row_num)->getNumberFormat()->setFormatCode('#,##0');//comma                
                    $sheet->getStyle('O'.$row_num)->getNumberFormat()->setFormatCode('#,##0');//comma
                    $sheet->getStyle('P'.$row_num)->getNumberFormat()->setFormatCode('#,##0');//comma                
                    $sheet->getStyle('Q'.$row_num)->getNumberFormat()->setFormatCode('#,##0');//comma
                    
                    if($cellValueG < 0){
                        $sheet->getStyle('G'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    if($cellValueH < 0){
                        $sheet->getStyle('H'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    if($cellValueI < 0){
                        $sheet->getStyle('I'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    if($cellValueJ < 0){
                        $sheet->getStyle('J'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    if($cellValueK < 0){
                        $sheet->getStyle('K'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    if($cellValueL < 0){
                        $sheet->getStyle('L'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    if($cellValueM < 0){
                        $sheet->getStyle('M'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    if($cellValueN < 0){
                        $sheet->getStyle('N'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    if($cellValueO < 0){
                        $sheet->getStyle('O'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    if($cellValueP < 0){
                        $sheet->getStyle('P'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    if($cellValueQ < 0){
                        $sheet->getStyle('Q'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    if($cellValueR < 0){
                        $sheet->getStyle('R'.$row_num)->applyFromArray($fontstyleArray); 
                    }
                    
                    $num++;
                    $row_num++;
                }//end data list
                
                if($adjust==0){
                    //if no adjust data add 2 row for adjust
                    $adjust_cell_chk=true;
                    $adjust_row=$row_num;
                    $sheet->setCellValue('B'.$adjust_row, '異動による差異調整');
                    $objWorkSheet->getActiveSheet()->mergeCells("B$adjust_row:F$adjust_row");
                    if($first_year){
                        $sheet->getStyle("G$row_num:R$row_num")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                    }else{
                        $sheet->getStyle("G$adjust_row:R$adjust_row")->applyFromArray($bgColor);                
                    }
                    //border for adjust row
                    $objWorkSheet->getActiveSheet()->getStyle('G'.$adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('H'.$adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('I'.$adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('J'.$adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('K'.$adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('L'.$adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('M'.$adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('N'.$adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('O'.$adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('P'.$adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('Q'.$adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('R'.$adjust_row)->applyFromArray($BStyle);                
                }
                if($other_adjust==0){
                    //if no adjust data add 2 row for adjust
                    $adjust_cell_chk=true;
                    if($adjust==0){
                        $other_adjust_row=$row_num+1;
                    }else{
                        $other_adjust_row=$row_num;
                    }                
                    $sheet->setCellValue('B'.$other_adjust_row, '');
                    $objWorkSheet->getActiveSheet()->mergeCells("B$other_adjust_row:F$other_adjust_row");
                    if($first_year){
                        $sheet->getStyle("G$row_num:R$row_num")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                    }else{
                        $sheet->getStyle("B$other_adjust_row:R$other_adjust_row")->applyFromArray($bgColor); 
                    }
                    $objWorkSheet->getActiveSheet()->getStyle("B$other_adjust_row:F$other_adjust_row")->applyFromArray($BStyle);
                    $last_row=$other_adjust_row;
                    //border for adjust cell
                    $objWorkSheet->getActiveSheet()->getStyle('G'.$other_adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('H'.$other_adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('I'.$other_adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('J'.$other_adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('K'.$other_adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('L'.$other_adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('M'.$other_adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('N'.$other_adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('O'.$other_adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('P'.$other_adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('Q'.$other_adjust_row)->applyFromArray($BStyle);
                    $objWorkSheet->getActiveSheet()->getStyle('R'.$other_adjust_row)->applyFromArray($BStyle);
                }
                //for total
                if($adjust!=0 && $other_adjust!=0){
                    $last_row=$row_num-1;
                }
                $total_row = $last_row + 1;
                $objWorkSheet->getActiveSheet()->getStyle("B7:R$last_row")->getAlignment()->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objWorkSheet->getActiveSheet()->getStyle("B7:R$last_row")->applyFromArray($BStyle);
                $sheet->setCellValue('B'.$total_row, __('合　計'));
                $objWorkSheet->getActiveSheet()->mergeCells("B$total_row:F$total_row");
                $objWorkSheet->getActiveSheet()->getStyle("B$total_row:F$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("B$total_row:F$total_row")->getFont()->setBold(true);
                $sheet->setCellValue('G'.$total_row, "=SUM(G7:G$last_row)");//
                $sheet->setCellValue('H'.$total_row, $total_budget_person_count);//
                $sheet->setCellValue('I'.$total_row, $total_common_expense);//
                $sheet->setCellValue('J'.$total_row, $total_budget_person);//
                $sheet->setCellValue('K'.$total_row, $total_labor_unit);//
                $sheet->setCellValue('L'.$total_row, $total_corporate_unit);//
                $sheet->setCellValue('M'.$total_row, $total_yearly_labor_cost);//
                $sheet->setCellValue('N'.$total_row, $total_unit_labor_cost);//
                $sheet->setCellValue('O'.$total_row, $total_adjust_cost);//
                $sheet->setCellValue('P'.$total_row, $total_yearly_corporate_cost);//
                $sheet->setCellValue('Q'.$total_row, $total_unit_corporate_cost);//
                $sheet->setCellValue('R'.$total_row, $total_adjust_corpo_cost);//

                $objWorkSheet->getActiveSheet()->getStyle("B$total_row:R$total_row")->getFont()->setBold(true);
                //border for total
                $objWorkSheet->getActiveSheet()->getStyle("G$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("H$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("I$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("J$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("K$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("L$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("M$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("N$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("O$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("P$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("Q$total_row")->applyFromArray($BStyle);
                $objWorkSheet->getActiveSheet()->getStyle("R$total_row")->applyFromArray($BStyle);	
                
                // font color red for negative total vlaues
                $totalCellValueG = $objWorkSheet->getActiveSheet()->getCell("I$total_row")->getFormattedValue();
                $totalCellValueH = $objWorkSheet->getActiveSheet()->getCell("H$total_row")->getFormattedValue();
                $totalCellValueI = $objWorkSheet->getActiveSheet()->getCell("I$total_row")->getFormattedValue();
                $totalCellValueJ = $objWorkSheet->getActiveSheet()->getCell("J$total_row")->getFormattedValue();
                $totalCellValueK = $objWorkSheet->getActiveSheet()->getCell("K$total_row")->getFormattedValue();
                $totalCellValueL = $objWorkSheet->getActiveSheet()->getCell("L$total_row")->getFormattedValue();
                $totalCellValueM = $objWorkSheet->getActiveSheet()->getCell("M$total_row")->getFormattedValue();
                $totalCellValueN = $objWorkSheet->getActiveSheet()->getCell("N$total_row")->getFormattedValue();
                $totalCellValueO = $objWorkSheet->getActiveSheet()->getCell("O$total_row")->getFormattedValue();
                $totalCellValueP = $objWorkSheet->getActiveSheet()->getCell("P$total_row")->getFormattedValue();
                $totalCellValueQ = $objWorkSheet->getActiveSheet()->getCell("Q$total_row")->getFormattedValue();
                $totalCellValueR = $objWorkSheet->getActiveSheet()->getCell("R$total_row")->getFormattedValue();

                if($totalCellValueG < 0){
                    $sheet->getStyle('G'.$total_row)->applyFromArray($fontstyleArray); 
                }
                if($totalCellValueH < 0){
                    $sheet->getStyle('H'.$total_row)->applyFromArray($fontstyleArray); 
                }
                if($totalCellValueI < 0){
                    $sheet->getStyle('I'.$total_row)->applyFromArray($fontstyleArray); 
                }
                if($totalCellValueJ < 0){
                    $sheet->getStyle('J'.$total_row)->applyFromArray($fontstyleArray); 
                }
                if($totalCellValueK < 0){
                    $sheet->getStyle('K'.$total_row)->applyFromArray($fontstyleArray); 
                }
                if($totalCellValueL < 0){
                    $sheet->getStyle('L'.$total_row)->applyFromArray($fontstyleArray); 
                }
                if($totalCellValueM < 0){
                    $sheet->getStyle('M'.$total_row)->applyFromArray($fontstyleArray); 
                }
                if($totalCellValueN < 0){
                    $sheet->getStyle('N'.$total_row)->applyFromArray($fontstyleArray); 
                }
                if($totalCellValueO < 0){
                    $sheet->getStyle('O'.$total_row)->applyFromArray($fontstyleArray); 
                }
                if($totalCellValueP < 0){
                    $sheet->getStyle('P'.$total_row)->applyFromArray($fontstyleArray); 
                }
                if($totalCellValueQ < 0){
                    $sheet->getStyle('Q'.$total_row)->applyFromArray($fontstyleArray); 
                }
                if($totalCellValueR < 0){
                    $sheet->getStyle('R'.$total_row)->applyFromArray($fontstyleArray); 
                }

                $sheet->getStyle('G'.$total_row)->getNumberFormat()->setFormatCode('#,#0.0000');//4th decimal place
                $sheet->getStyle('H'.$total_row)->getNumberFormat()->setFormatCode('#,#0.0000');//4th decimal place
                $sheet->getStyle('I'.$total_row)->getNumberFormat()->setFormatCode('#,#0.0000');//4th decimal place
                $sheet->getStyle('J'.$total_row)->getNumberFormat()->setFormatCode('#,#0.0000');//4th decimal place
                $sheet->getStyle('K'.$total_row)->getNumberFormat()->setFormatCode('#,##0');//comma
                $sheet->getStyle('L'.$total_row)->getNumberFormat()->setFormatCode('#,##0');//comma
                $sheet->getStyle('M'.$total_row)->getNumberFormat()->setFormatCode('#,##0');//comma
                $sheet->getStyle('N'.$total_row)->getNumberFormat()->setFormatCode('#,##0');//comma                
                $sheet->getStyle('O'.$total_row)->getNumberFormat()->setFormatCode('#,##0');//comma
                $sheet->getStyle('P'.$total_row)->getNumberFormat()->setFormatCode('#,##0');//comma                
                $sheet->getStyle('Q'.$total_row)->getNumberFormat()->setFormatCode('#,##0');//comma            
                #hide columns
                $objWorkSheet->getActiveSheet()->getColumnDimension('E')->setCollapsed(false);
                $objWorkSheet->getActiveSheet()->getColumnDimension('E')->setVisible(false);
                $objWorkSheet->getActiveSheet()->getColumnDimension('F')->setCollapsed(false);
                $objWorkSheet->getActiveSheet()->getColumnDimension('F')->setVisible(false);
                $objWorkSheet->getActiveSheet()->getColumnDimension('G')->setCollapsed(false);
                $objWorkSheet->getActiveSheet()->getColumnDimension('G')->setVisible(false);
                
            }
            if($first_year) {
                $sheet = $objWorkSheet->getActiveSheet();
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setInsertRows(true);
                $sheet->getProtection()->setInsertColumns(true);    

                $first_year = false;// set false at the end of the loop 
            }
        }
        $objWorkSheet->setActiveSheetIndexByName($current_yr.'_'.$sheet_name);// ko hein's original code :)

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');

        // Encode the filename using urlencode
        $encodedFileName = urlencode($file_name . '.xlsx');
        header('Content-Disposition: attachment; filename="' . $encodedFileName . '"; charset=utf-8');
        $this->PhpExcel->output(urlencode($file_name . '.xlsx'));
        // $this->PhpExcel->output($file_name.".xlsx"); // ko hein's original code :)
    }
    public function deleteOtherAdjust(){
        $this->autoRender = false; 			
		$this->request->allowMethod('ajax'); 
		$layout = ''; 
        $current_time = date('Y-m-d H:i:s', time()); 
        $target_year                = $this->request->data['target_year'] ;
        $hd_other_adjust_name       = trim($this->request->data['hd_other_adjust_name']) ;
        $layer_code                 = $this->request->data['layer_code'] ;
        $user_id=0;
        $buTermId = $_SESSION['BU_TERM_ID'];
        $getData = $this->LaborCost->getExistDataByTargetYrAndUserId($target_year,$layer_code,$user_id,$hd_other_adjust_name,$buTermId);
        $update_adjust_data=array();
        if ($this->Session->check('LOGIN_ID')) {
            $login_user_id = $this->Session->read('LOGIN_ID');
        }else{
            $login_user_id =1;
        }
        if(!empty($getData)){//already exist data overwrite
            foreach($getData as $data){
                $id = $data['LaborCost']['id'];
                $update_adjust_data['id'] = $id;
                $update_adjust_data['target_year']           = $target_year;
                // $update_adjust_data['position_id']           = $data['LaborCost']['position_id'];
                $update_adjust_data['position_code']           = $data['LaborCost']['position_code'];
                $update_adjust_data['user_id']               = $data['LaborCost']['user_id'];
                $update_adjust_data['layer_code']            = $data['LaborCost']['layer_code'];
                $update_adjust_data['adjust_name']           = $data['LaborCost']['adjust_name'];
                $update_adjust_data['person_count']          = $data['LaborCost']['person_count'] ;     
                $update_adjust_data['b_person_count']        = $data['LaborCost']['b_person_count'];    
                $update_adjust_data['common_expense']        = $data['LaborCost']['common_expense'];    
                $update_adjust_data['b_person_total']        = $data['LaborCost']['b_person_total'];    
                $update_adjust_data['labor_unit']            = $data['LaborCost']['labor_unit'];        
                $update_adjust_data['corp_unit']             = $data['LaborCost']['corp_unit'];        
                $update_adjust_data['yearly_labor_cost']     = $data['LaborCost']['yearly_labor_cost']; 
                $update_adjust_data['unit_labor_cost']       = $data['LaborCost']['unit_labor_cost'];   
                $update_adjust_data['adjust_labor_cost']     = $data['LaborCost']['adjust_labor_cost']; 
                $update_adjust_data['yearly_corpo_cost']     = $data['LaborCost']['yearly_corpo_cost']; 
                $update_adjust_data['unit_corpo_cost']       = $data['LaborCost']['unit_corpo_cost'];   
                $update_adjust_data['adjust_corpo_cost']     = $data['LaborCost']['adjust_corpo_cost'];
                $update_adjust_data['flag']                  = "0";
                $update_adjust_data['created_by']            = $data['LaborCost']['created_by'];
                $update_adjust_data['updated_by']            = $login_user_id;
                $update_adjust_data['created_date']          = $data['LaborCost']['created_date'];                
                $update_adjust_data['updated_date']          = $current_time;  
                $update= $this->LaborCost->saveAll($update_adjust_data); 
                if($update==true){
                    $data = array(
                        'result' => true
                    );
                }else{
                    $data = array(
                        'result' => false
                    );
                }
            }            
            
        } else{
            $data = array(
                'result' => false
            );
        }
		echo json_encode ($data);
    }

    public function checkSaveMerge(){
        parent::checkAjaxRequest($this);
        if ($this->request->is('post')) {
            $target_year = $this->request->data['target_year'];
            $layer_code = $this->request->data['layer_code'];
            $buTermId = $_SESSION['BU_TERM_ID'];
            $row_count = ($this->LaborCost->find('count', array(
                'conditions' => array(
                    'target_year' => $target_year,
                    'layer_code' => $layer_code,
                    'bu_term_id' => $buTermId
                )
            ))>0)? true : false;
            echo json_encode($row_count);
        }

    }

    /** 
     * changeApprovedLogFlag method
     * @author Hein Htet Ko
     * @return message
     *
     */
    public function changeApprovedLogFlag(){
        if ($this->request->is('post')) {
            #get menu id, bu term id, target year, layer code
            $Common = new CommonController();
            $menu_id = $Common->getMenuId($this);
            $buTermId = $_SESSION['BU_TERM_ID'];
            $search_data = $this->Session->read('SEARCH_LABOR_COST');
            #get complete flag
            $completed_flag =  $this->BuApprovedLog->find('first', array(
                'fields' => array('flag'),
                'conditions' => array(
                    'menu_id' => Setting::MENU_ID_LIST['BudgetResult'],
                    'bu_term_id' => $buTermId,
                    'group_code' => $search_data['layer_code'],
                    // 'line_code' => '0',
                    // 'business_code' => '0',
                    // 'sub_business_code' => '0',
                )
            ))['BuApprovedLog']['flag'];
            $completed_flag_2 =  $this->BuApprovedLog->find('first', array(
                'fields' => array('flag'),
                'conditions' => array(
                    'menu_id' => Setting::MENU_ID_LIST['LaborCosts'],
                    'bu_term_id' => $buTermId,
                    'department_code' => '0',
                    'bu_code' => '0',
                    'target_year' => $search_data['target_year'],
                    'group_code' => $search_data['layer_code'],
                    'line_code' => '0',
                    'business_code' => '0',
                    'sub_business_code' => '0',
                )
            ))['BuApprovedLog']['flag'];
            if($completed_flag == 2 || $completed_flag_2 == 1){
                $errorMsg = parent::getErrorMsg('SE157');
                $this->Flash->error($errorMsg, array('key'=>'lc_error'));
                $this->redirect(array("action" => "index"));
            }
  
            #update flag 2 to 1
            $changed_flag = $this->BuApprovedLog->updateAll(
                    array('flag' => $this->request->data('approved_flag')),
                    array('menu_id' => $menu_id, 'bu_term_id' => $buTermId, 'target_year' => $search_data['target_year'], 'group_code' => $search_data['layer_code'], 'line_code' => '0', 'business_code' => '0', 'sub_business_code' => '0')
            );
            if($changed_flag){
                $successMsg = parent::getSuccessMsg('SS036');
                $this->Flash->success($successMsg, array('key'=>'lc_success'));
                $this->redirect(array("action" => "index"));
            }
        }


    }

    /** 
     * checkLayerCodeExist method
     * @author Hein Htet Ko
     * @return $disabledCommentBtn, $disabledSaveBtn, $disabledConfirmBtn, $disabledConfirmCancelBtn
     *
     */
    public function checkLayerCodeExist(){
        if ($this->Session->check('SEARCH_LABOR_COST')) {
            $search_data = $this->Session->read('SEARCH_LABOR_COST');
            if($this->Session->check('LC.COMMENT')){
                $commentLayers = $this->Session->read('LC.COMMENT');
                $disabledCommentBtn = (!in_array($search_data['layer_code'], $commentLayers))? true : false;
            }
            if($this->Session->check('LC.SAVE')){
                $saveLayers = $this->Session->read('LC.SAVE');
                $disabledSaveBtn = (!in_array($search_data['layer_code'], $saveLayers))? true : false;
            }
            if($this->Session->check('LC.CONFIRM')){
                $confirmLayers = $this->Session->read('LC.CONFIRM');
                $disabledConfirmBtn = (!in_array($search_data['layer_code'], $confirmLayers))? true : false;
            }
            if($this->Session->check('LC.CONFIRM_CANCEL')){
                $confirmCancelLayers = $this->Session->read('LC.CONFIRM_CANCEL');
                $disabledConfirmCancelBtn = (!in_array($search_data['layer_code'], $confirmCancelLayers))? true : false;
            }
        }
        return [$disabledCommentBtn, $disabledSaveBtn, $disabledConfirmBtn, $disabledConfirmCancelBtn];
    }

}

