<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
define('MODEL', 'LaborCostDetail');
class LaborCostDetailsController extends AppController
{
    
    public $uses = ['LaborCostDetail', 'LaborCost', 'Layer', 'LaborCostAdjustment', 'Position', 'Budget', 'LcComment', 'BuApprovedLog'];
    public $components = ['Session', 'Flash','PhpExcel.PhpExcel'];
    public $helpers = ['Html', 'Form', 'Session'];

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkBuUrlSession($this->name);

        #get required params
        $login_id   = $this->Session->read('LOGIN_ID');
        $role_id    = $this->Session->read('ADMIN_LEVEL_ID');
        if(!empty($this->Session->check('SEARCH_LABOR_COST.target_year'))) {
            $t_y = $this->Session->read('SEARCH_LABOR_COST.target_year');
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

        $this->Session->write('LCD.INDEX', $permissions['index']['layer']);
        $this->Session->write('LCD.COMMENT', $permissions['comment']['layer']);
        $this->Session->write('LCD.SAVE', $permissions['save']['layer']);
        $this->Session->write('LCD.CONFIRM', $permissions['confirm']['layer']);
        $this->Session->write('LCD.CONFIRM_CANCEL', $permissions['confirm_cancel']['layer']);

        if(!in_array($this->Session->read('SELECTED_GROUP'), $permissions['index']['layer'])) {
            $error_msg = parent::getErrorMsg('SE065');
            $this->Flash->set($error_msg, array('key'=>'BuError'));
            $this->redirect(array('controller'=>'BUSelections', 'action'=>'index'));
        }
        
    }

    public function index()
    {   
        $this->layout = 'buanalysis';
        
        /****
         * 1. Get request value and store in session for ui filter 
         ****/
        //current year in initial state
        $budget_year = $_SESSION['BudgetTargetYear'];
        $target_year = empty($this->Session->read('SEARCH_LABOR_COST.target_year'))? $budget_year : $this->Session->read('SEARCH_LABOR_COST.target_year');
        $group_code = empty($this->Session->read('SEARCH_LABOR_COST.layer_code'))? $this->Session->read('SELECTED_GROUP') : $this->Session->read('SEARCH_LABOR_COST.layer_code');
        
        if($this->request->is('post')){
            $target_year = $this->request->data['target_year'];
            $group_code = $this->request->data['group_code'];

            $this->Session->write('SEARCH_LABOR_COST.target_year', $target_year);
            $this->Session->write('SEARCH_LABOR_COST.layer_code', $group_code);
        }
        
        $term_list = $_SESSION['yearListOnTerm'];
        // Remove the first key and value
        unset($term_list[key($term_list)]);
        //wirite it back to session with new name
        $this->Session->write('TERM_YEAR_LIST', $term_list);

        #comment with popup
        $comment = $this->LcComment->find('first', array(
            'fields' => ['LcComment.id', 'LcComment.comment', 'LcComment.updated_date' ,'users.user_name'],
            'conditions' => [
                'LcComment.page_name' => 'LaborCostDetails',
                'LcComment.target_year' => $target_year,
                'LcComment.layer_code' => $group_code,
                'LcComment.bu_term_id' => $_SESSION['BU_TERM_ID']
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
        #comment with popup

        $years = array_keys($term_list);
        $errormsg = '';
        $buTermId = $_SESSION['BU_TERM_ID'];
        if(empty($years)) {
            $errormsg = parent::getErrorMsg('SE149');
            $this->set(compact('errormsg'));
        }else {
        //group codes for filter selct box
        $groups = $this->getLayerList($term_list[$target_year]);
        
        if(empty($group_code) || !in_array($group_code, array_keys($groups))){
            $group_code = array_keys($groups)[0];
        }
            
        list($businesses, $business_codes) = $this->LaborCostDetail->businessCodes($term_list[$target_year], $group_code);
        $users = $this->LaborCostDetail->checkUser($term_list[$target_year], $group_code, $buTermId);
        if($businesses && $users && $groups){
            //get labour costs
            //term id
            $laborCosts = $this->LaborCostDetail->getLaborCost($target_year, $group_code, $buTermId);

            foreach($laborCosts as $key=>$value){
                if($value['user_id'] != 0) {
                    $position_data = $this->Position->getUserPosition($value['user_id'], $target_year);
                }else {
                    $position_data[0]['positions']['personnel_cost'] = $value['personnel_cost'];
                    $position_data[0]['positions']['corporate_cost'] = $value['corporate_cost'];
                }
                
                $laborCosts[$key]['personnel_cost'] = $position_data[0]['positions']['personnel_cost'];
                $laborCosts[$key]['corporate_cost'] = $position_data[0]['positions']['corporate_cost'];
            }
            $userId = array();
            foreach($laborCosts as $value){
                $userId[] = $value['user_id'];
            }
            //LCDs means Labor Cost Details, LCA means Labor Cost Adjustments;
            list($tableOne, $totalTableOne) = $this->_formatDataT1($target_year, $laborCosts, $business_codes);//table one
            list($tableTwoNew, $tableTwoExist) = $this->_formatDataT2($target_year, $business_codes, $userId);//table two
            list($personnelCost, $corporateCost, $laborCostAdjustments, $totalLCA) = $this->_formatDataT3($target_year, $business_codes, $group_code);//table three    
            list($old_tableOne, $old_tableTwo) = $this->getTableDatas($target_year, $group_code);
            $old_tableOne = json_encode($old_tableOne);
            $old_tableTwo = json_encode($old_tableTwo);

            $position_lists = $this->PositionData($target_year);

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
                    'target_year' => $target_year,
                    'group_code' => $group_code,
                    'line_code' => '0',
                    'business_code' => '0',
                    'sub_business_code' => '0'
                )
            ))['BuApprovedLog']['flag'];
            $approved_flag = empty($approved_flag)? 1 : $approved_flag;
            $completed_flag =  $this->BuApprovedLog->find('first', array(
                'fields' => array('flag'),
                'conditions' => array(
                    'menu_id' => Setting::MENU_ID_LIST['BudgetResult'],
                    'bu_term_id' => $buTermId,
                    'group_code' => $group_code,
                    // 'line_code' => '0',
                    // 'business_code' => '0',
                    // 'sub_business_code' => '0',
                )
            ))['BuApprovedLog']['flag'];
            $completed_flag = empty($completed_flag)? 1 : $completed_flag;
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
        $this->Session->write('SELECTION', 'SET');
        #check layer code exists or not
        list($disabledCommentBtn, $disabledSaveBtn, $disabledConfirmBtn, $disabledConfirmCancelBtn) = $this->checkLayerCodeExist();

            $this->set(compact(
                'tableOne', 
                'totalTableOne', 
                'tableTwoNew', 
                'tableTwoExist', 
                'old_tableOne',
                'old_tableTwo',
                'personnelCost', 
                'corporateCost', 
                'laborCostAdjustments',
                'totalLCA',
                'businesses', 
                'groups', 
                'years',
                'position_lists',
                'comment',
                'target_year',
                'approved_flag',
                'completed_flag',
                'showReadBtn',
                'showCommentBtn',
                'showSaveBtn',
                'showConfirmBtn',
                'showConfirmCancelBtn',
                'disabledCommentBtn',
                'disabledSaveBtn',
                'disabledConfirmBtn',
                'disabledConfirmCancelBtn'
            ));
        }else{
            if(!$groups){
                $errMsg = ['error' => true, 'errMsg' => parent::getErrorMsg('SE153')];

            }elseif(!$businesses) {
                $errMsg = ['error' => true, 'errMsg' => parent::getErrorMsg('SE151')];
            }elseif(!$users) {
                $errMsg = ['error' => true, 'errMsg' => parent::getErrorMsg('SE133', __('ユーザー'))];
            }
            $old_tableOne = json_encode([]);
            $old_tableTwo = json_encode([]);
            $this->set(compact(
                'old_tableOne',
                'old_tableTwo',
                'errMsg',
                'groups', 
                'years',
                'comment'
            ));
        }
        }
    }

    //formatted data for table one
    public function _formatDataT1($target_year, $laborCosts, $business_codes){
        
        $new_user = $laborCosts[0];
        $new_user['id'] = '0';
        $new_user['layer_code'] = $laborCosts[0]['layer_code'];
        $new_user['position_code'] = '0';
        $new_user['user_id'] = '0';
        $new_user['adjust_name'] = '';
        $new_user['person_count'] = '0';
        $new_user['b_person_count'] = '0';
        $new_user['common_expense'] = '0';
        $new_user['b_person_total'] = '0';
        $new_user['adjust_labor_cost'] = '0.00';
        $new_user['adjust_corpo_cost'] = '0.00';
        $new_user['unit_labor_cost'] = '0.00';
        $new_user['unit_corpo_cost'] = '0.00';
        $new_user['user_name'] = '';
        $new_user['position_name'] = '';
        $new_user['personnel_cost'] = '0.00';
        $new_user['corporate_cost'] = '0.00';
        $new_user['person_cnt'] = 'person_cnt_'.(count($laborCosts)+1);
        array_push($laborCosts, $new_user);
        
        //For Initial State Data
        $initialState = [];
        foreach ($business_codes as $business_code) {
            $initialState[$business_code] = 0; 
        }
        $initialState['total'] = 0;
        $initialState['comment'] = '';
        /****
         * Total Row
         ***/
        $userId = array();
        foreach($laborCosts as $value){
            $userId[] = $value['user_id'];
        }
        //term id
        $buTermId = $_SESSION['BU_TERM_ID'];
        $totalLaborCostDetails = $this->LaborCostDetail->find('all', [
            'fields' => [
                'SUM(person_count) as total_person_count',
                'layer_code'
             ],
            'group' => 'layer_code',
            'conditions' => [
                'LaborCostDetail.bu_term_id' => $buTermId,
                'LaborCostDetail.target_year' => $target_year,
                'LaborCostDetail.layer_code' => $business_codes,
                'OR'=>array(
                    array('LaborCostDetail.user_id' => $userId),
                    array('LaborCostDetail.user_id = "0" AND LaborCostDetail.new_user_name IS NOT NULL'),
                    array('LaborCostDetail.user_id IS NULL')
                ),
                'LaborCostDetail.flag' => 1
            ] 
        ]);
        $formattedTotalLCD = [];
        foreach($business_codes as $code){
            $formattedTotalLCD[$code] = 0;
        }
        $formattedTotalLCD['all_total'] = 0;
        //Total Row
        
        if($totalLaborCostDetails){//having data state
            $allTotal = 0;
            foreach($business_codes as $business_code){
                
                
                foreach($totalLaborCostDetails as $tLCD){
                    if($business_code == $tLCD['LaborCostDetail']['layer_code']){
                        $allTotal += $tLCD[0]['total_person_count'];
                        $formattedTotalLCD[$business_code] = $tLCD[0]['total_person_count'];
                    }
                }
            }
            $formattedTotalLCD['all_total'] = $allTotal;
        }
        //end total row
        
        /***
         * person count row
         ***/

        $laborCostDetails = $this->LaborCostDetail->find('all', [
            'conditions' => [
                'LaborCostDetail.bu_term_id' => $buTermId,
                'LaborCostDetail.target_year' => $target_year,
                'LaborCostDetail.layer_code' => $business_codes,
                'OR'=>array(
                    array('LaborCostDetail.user_id' => $userId),
                    array('LaborCostDetail.user_id = "0" AND LaborCostDetail.new_user_name IS NOT NULL'),
                    array('LaborCostDetail.user_id IS NULL'),
                ),
                'LaborCostDetail.flag' => 1,
            ],
            'group' => ['LaborCostDetail.bu_term_id','LaborCostDetail.target_year','LaborCostDetail.position_code','LaborCostDetail.user_id','LaborCostDetail.new_user_name','LaborCostDetail.layer_code','LaborCostDetail.business_type']
        ]);
        
        $formattedLaborCost = [];
        foreach ($laborCosts as $index => $laborCost) {   
            $laborCost['exist_hours'] = $initialState; 
            $laborCost['new_hours'] = $initialState; 
            $formattedLaborCost[] = $laborCost;
            // $formattedLaborCost[$index]['exist_hours'] = $initialState;
            // $formattedLaborCost[$index]['new_hours'] = $initialState;
        }
        
        $formattedLaborCost['adjustment']['exist_hours'] = $initialState;
        $formattedLaborCost['adjustment']['new_hours'] = $initialState;
        //comment is not include in adjustment row
        if(array_key_exists('comment',$formattedLaborCost['adjustment']['exist_hours'])){
            array_pop($formattedLaborCost['adjustment']['exist_hours']);
        }
        if(array_key_exists('comment',$formattedLaborCost['adjustment']['new_hours'])){
            array_pop($formattedLaborCost['adjustment']['new_hours']);
        }
        
        //end person count row
        if(!$laborCostDetails){
            return [$formattedLaborCost, $formattedTotalLCD];
        }
        
        foreach ($formattedLaborCost as $key => $LC) {
            if($LC['id'] !== '0') {
                foreach ($laborCostDetails as $value) {
                    $id = $value['LaborCostDetail']['id'];
                    $business_type = $value['LaborCostDetail']['business_type'];
                    $business_code = $value['LaborCostDetail']['layer_code'];
                    $person_count = $value['LaborCostDetail']['person_count'];
                    $user_id = $value['LaborCostDetail']['user_id'];
                    $comment = $value['LaborCostDetail']['comment'];
                    $new_user_name = $value['LaborCostDetail']['new_user_name'];
                    $position_code = $value['LaborCostDetail']['position_code'];
                    

                    if($business_type == 1) {
                        
                        if($key === 'adjustment' && $position_code === NULL && $user_id === NULL && strlen($new_user_name) === 0) {
                            # ajustment row for position = NULL, user = NULL, new_user = NULL
                            $formattedLaborCost[$key]['exist_ids'][$business_code] = $id;
                            $formattedLaborCost[$key]['exist_hours'][$business_code] = $person_count;
                            $formattedLaborCost[$key]['exist_hours']['total'] += $person_count;
                            $formattedLaborCost[$key]['exist_hours']['comment'] = $comment;

                        }elseif($key !== 'adjustment' && $position_code > 0 && $user_id > 0 && strlen($new_user_name) === 0 && $user_id === $LC['user_id']) {
                            # normal row for position != 0, user != 0, new_user = ''
                            $formattedLaborCost[$key]['exist_ids'][$business_code] = $id;
                            $formattedLaborCost[$key]['exist_hours'][$business_code] = $person_count;
                            $formattedLaborCost[$key]['exist_hours']['total'] += $person_count;
                            $formattedLaborCost[$key]['exist_hours']['comment'] = $comment;

                        }elseif($key !== 'adjustment' && $position_code > 0 && $user_id === '0' && strlen($new_user_name) > 0 && trim($new_user_name) === trim($LC['user_name'])) {
                            # new row for position != 0, user = 0, new_user != ''
                            $formattedLaborCost[$key]['exist_ids'][$business_code] = $id;
                            $formattedLaborCost[$key]['exist_hours'][$business_code] = $person_count;
                            $formattedLaborCost[$key]['exist_hours']['total'] += $person_count;
                            $formattedLaborCost[$key]['exist_hours']['comment'] = $comment;

                        }
                        
                    }
                    elseif($business_type == 2) {
                        if($key === 'adjustment' && $position_code === NULL && $user_id === NULL && strlen($new_user_name) === 0) {
                            # ajustment row for position = NULL, user = NULL, new_user = NULL
                            $formattedLaborCost[$key]['new_ids'][$business_code] = $id;
                            $formattedLaborCost[$key]['new_hours'][$business_code] = $person_count;
                            $formattedLaborCost[$key]['new_hours']['total'] += $person_count;
                            $formattedLaborCost[$key]['new_hours']['comment'] = $comment;

                        }elseif($key !== 'adjustment' && $position_code > 0 && $user_id > 0 && strlen($new_user_name) === 0 && $user_id === $LC['user_id']) {
                            # normal row for position != 0, user != 0, new_user = ''
                            $formattedLaborCost[$key]['new_ids'][$business_code] = $id;
                            $formattedLaborCost[$key]['new_hours'][$business_code] = $person_count;
                            $formattedLaborCost[$key]['new_hours']['total'] += $person_count;
                            $formattedLaborCost[$key]['new_hours']['comment'] = $comment;

                        }elseif($key !== 'adjustment' && $position_code > 0 && $user_id === '0' && strlen($new_user_name) > 0 && trim($new_user_name) === trim($LC['user_name'])) {
                            # new row for position != 0, user = 0, new_user != ''
                            $formattedLaborCost[$key]['new_ids'][$business_code] = $id;
                            $formattedLaborCost[$key]['new_hours'][$business_code] = $person_count;
                            $formattedLaborCost[$key]['new_hours']['total'] += $person_count;
                            $formattedLaborCost[$key]['new_hours']['comment'] = $comment;

                        }
                    }
                }
            }
        }

        if(array_key_exists('comment',$formattedLaborCost['adjustment']['exist_hours'])){
            array_pop($formattedLaborCost['adjustment']['exist_hours']);
        }
        if(array_key_exists('comment',$formattedLaborCost['adjustment']['new_hours'])){
            array_pop($formattedLaborCost['adjustment']['new_hours']);
        }
        
        return [$formattedLaborCost, $formattedTotalLCD];
    }

    //formatted data for table two
    public function _formatDataT2($target_year, $business_codes, $userId, $user = array()){
        //Exist Total Row
        //term id
        
        $group_code = $_SESSION['SEARCH_LABOR_COST']['layer_code'];
        $laborCosts = $this->LaborCostDetail->getLaborCost($target_year, $group_code, $_SESSION['BU_TERM_ID']);
        
        $userId = array();
        foreach($laborCosts as $value){
            $userId[] = $value['user_id'];
        }
        
        $buTermId = $_SESSION['BU_TERM_ID'];
        $totalExist = $this->LaborCostDetail->find('all', [
            'fields' => [
                'SUM(person_count) as total_person_count',
                'layer_code'
             ],
            'group' => 'layer_code',
            'conditions' => [
                'LaborCostDetail.bu_term_id' => $buTermId,
                'LaborCostDetail.target_year' => $target_year,
                'LaborCostDetail.layer_code' => $business_codes,
                'LaborCostDetail.business_type' => 1,
                'OR'=>array(
                    array('LaborCostDetail.user_id' => $userId),
                    array('LaborCostDetail.user_id = "0" AND LaborCostDetail.new_user_name IS NOT NULL'),
                    array('LaborCostDetail.user_id IS NULL')
                )
            ] 
        ]);
        $formattedTotalExist = [];
        foreach($business_codes as $code){
            $formattedTotalExist[$code] = 0;
        }
        $formattedTotalExist['total'] = 0;
        if($totalExist){//having data state
            $allTotal = 0;
            foreach ($business_codes as $business_code) {
                foreach($totalExist as $te){
                    if($business_code == $te['LaborCostDetail']['layer_code']){
                        $allTotal += $te[0]['total_person_count'];
                        $formattedTotalExist[$business_code] = $te[0]['total_person_count'];
                    }
                }
            }
            $formattedTotalExist['total'] = $allTotal;
        }
        //New Total Row
        $totalNew = $this->LaborCostDetail->find('all', [
            'fields' => [
                'SUM(person_count) as total_person_count',
                'layer_code'
             ],
            'group' => 'layer_code',
            'conditions' => [
                'LaborCostDetail.bu_term_id' => $buTermId,
                'LaborCostDetail.target_year' => $target_year,
                'LaborCostDetail.layer_code' => $business_codes,
                'LaborCostDetail.business_type' => 2,
                'OR'=>array(
                    array('LaborCostDetail.user_id' => $userId),
                    array('LaborCostDetail.user_id IS NULL')
                )
            ] 
        ]);

        $formattedTotalNew = [];
        foreach($business_codes as $code){
            $formattedTotalNew[$code] = 0;
        }
        $formattedTotalNew['total'] = 0;
        if($totalNew){//having data state
            $allTotal = 0;
            foreach ($business_codes as $business_code) {
                foreach($totalNew as $tn){
                    if($business_code == $tn['LaborCostDetail']['layer_code']){
                        $allTotal += $tn[0]['total_person_count'];
                        $formattedTotalNew[$business_code] = $tn[0]['total_person_count'];
                    }
                }
            }
            $formattedTotalNew['total'] = $allTotal;
        }

        // debug([$formattedTotalExist, $formattedTotalNew]);exit;
        return [$formattedTotalNew, $formattedTotalExist];
    }

    //formatted data for table three
    public function _formatDataT3($target_year, $business_codes, $group_code){
        //For Initial State Data
        $initialState = [];
        $personCost = [];
        $corporateCost = [];
        foreach ($business_codes as $business_code) {
            $initialState[$business_code] = 0;
            $personCost[$business_code] = 0; 
            $corporateCost[$business_code] = 0;
        }
        $initialState['total'] = 0;
        $personCost['total'] = 0;
        $corporateCost['total'] = 0;
        /***
         * Total Salary per working hour
         ***/
        //get lcd data with unit salary
        //term id
        $buTermId = $_SESSION['BU_TERM_ID'];
        $laborCostDetails = $this->LaborCostDetail->find('all', [
            'joins' => [
                [
                    'table' => 'positions',
                    'type'  =>  'left',
                    'conditions' => [
                        // 'positions.id = LaborCostDetail.position_id'
                        'positions.position_code = LaborCostDetail.position_code'
                    ]                
                ]
            ],
            'conditions' => [
                'LaborCostDetail.bu_term_id' => $buTermId,
                'LaborCostDetail.target_year' => $target_year,
                'LaborCostDetail.layer_code' => $business_codes,
                'LaborCostDetail.user_id is not NULL'
            ],
            'fields' => [
                'LaborCostDetail.id', 
                'LaborCostDetail.user_id',
                // 'LaborCostDetail.position_id', 
                'LaborCostDetail.position_code', 
                'LaborCostDetail.person_count', 
                'LaborCostDetail.layer_code', 
                'positions.personnel_cost',
                'positions.corporate_cost',
            ]
        ]);

        if($laborCostDetails){//having data state, working hour * unit salary(personnel cost, corporate cost)
            //term id
            $laborCosts = $this->LaborCostDetail->getLaborCost($target_year, $group_code, $buTermId);
            foreach($laborCosts as $laborCost){
                $laborCostByUser[$laborCost['user_id']] = [
                    'person_count' => $laborCost['person_count'],
                    'b_person_count' => $laborCost['b_person_count'],
                    'common_expense' => $laborCost['common_expense'],
                    'adjust_labor_cost' => $laborCost['adjust_labor_cost'],
                    'adjust_corpo_cost' => $laborCost['adjust_corpo_cost'],
                ];
            }
            // debug($laborCostByUser);exit;
            foreach ($business_codes as $business_code) {
                foreach ($laborCostDetails as $lcd){
                    if($business_code == $lcd['LaborCostDetail']['layer_code']){
                        
                        $user_id = $lcd['LaborCostDetail']['user_id'];
                        $person_count = $laborCostByUser[$user_id]['person_count'];
                        $labor_unit = $lcd['positions']['personnel_cost'] * $person_count;
                        
                        $yearly_labor_cost = ($labor_unit * 12) + $laborCostByUser[$user_id]['adjust_labor_cost'];

                        $unit_lc = ($yearly_labor_cost / floatval($person_count)) / 12;

                        $unit_labor_cost = is_infinite($unit_lc) || is_nan($unit_lc) ? 0 : $unit_lc;

                        $personCost[$business_code] += ($lcd['LaborCostDetail']['person_count'] * $unit_labor_cost) * 12; 

                        $b_person_total = $laborCostByUser[$user_id]['b_person_count'] + $laborCostByUser[$user_id]['common_expense'];
                        $corpo_unit = $lcd['positions']['corporate_cost'] * $b_person_total;
                        $yearly_corpo_cost = ($corpo_unit * 12) + $laborCostByUser[$user_id]['adjust_corpo_cost'];
                        $unit_corpo_cost = ($yearly_corpo_cost / $person_count) / 12;
                        $unit_corpo_cost =  is_infinite($unit_corpo_cost) || is_nan($unit_corpo_cost) ? 0 : $unit_corpo_cost;
                        $corporateCost[$business_code] += ($lcd['LaborCostDetail']['person_count'] * $unit_corpo_cost) * 12;

                        /***
                         * trace debug
                         */
                        // if($lcd['LaborCostDetail']['person_count']){
                        //     debug([
                        //         'b_person_total' => $b_person_total,
                        //         'person_cost' => $lcd['positions']['personnel_cost'],
                        //         'person_count' => $person_count,
                        //         'corpo_unit' => $corpo_unit,
                        //         'yearly' => $yearly_corpo_cost,
                        //         'unit_corpo_cost' => $unit_corpo_cost
                        //     ]);
                        // }

                        #no need calculation bcos calculate in display
                        $personCost[$business_code] = 0;
                        $corporateCost[$business_code] = 0;
                    }
                }
            }
            $personCost['total'] = array_sum($personCost);
            
            $corporateCost['total'] = array_sum($corporateCost);
        }else{//initial state, all value is Zero......
            
            $personCost = $initialState; 
            $corporateCost = $initialState; 
        }
        //End Total Salary per working hour
        
        /***
         * Adjustment Row
         ***/
        //lca means labor cost adjustments
        $LCAs = $this->LaborCostAdjustment->find('all', [
            'conditions' => [
                'bu_term_id' => $buTermId,
                'target_year' => $target_year,
                'layer_code' => $business_codes,
                'flag' => 1
            ]
        ]);               
        foreach($business_codes as $bCode){
            $labor_cost_adjustment[$bCode] = '';
        }
        $formattedLCAs = [
            '経営指導料'=> ['hours' => $labor_cost_adjustment, 'ids' => $labor_cost_adjustment],
            '異動による調整額' => ['hours' => $labor_cost_adjustment, 'ids' => $labor_cost_adjustment]
        ];
        if($LCAs){
            foreach($business_codes as $code){//having data state
                foreach ($LCAs as  $LCA){
                    
                    if(
                        $LCA['LaborCostAdjustment']['adjust_name'] == '経営指導料' && 
                        $LCA['LaborCostAdjustment']['layer_code'] == $code
                    ){
                        $formattedLCAs['経営指導料']['hours'][$code] = $LCA['LaborCostAdjustment']['adjust_amount'];
                        $formattedLCAs['経営指導料']['ids'][$code] = $LCA['LaborCostAdjustment']['id'];   
                    }
                    elseif (
                        $LCA['LaborCostAdjustment']['adjust_name'] == '異動による調整額' && 
                        $LCA['LaborCostAdjustment']['layer_code'] == $code
                    ) {
                        $formattedLCAs['異動による調整額']['hours'][$code] = $LCA['LaborCostAdjustment']['adjust_amount'];
                        $formattedLCAs['異動による調整額']['ids'][$code] = $LCA['LaborCostAdjustment']['id'];   
                    }
                }
            } 
            $formattedLCAs['経営指導料']['hours']['total'] = array_sum($formattedLCAs['経営指導料']['hours']);
            $formattedLCAs['異動による調整額']['hours']['total'] = array_sum($formattedLCAs['異動による調整額']['hours']);
        }else{//intial state
            $formattedLCAs['経営指導料']['hours'] = $initialState;
            $formattedLCAs['異動による調整額']['hours'] = $initialState;
        }
        //end adjustment row

        //All total
        $totalArr[] = $formattedLCAs['経営指導料']['hours'];
        $totalArr[] = $formattedLCAs['異動による調整額']['hours'];
        $totalArr[] = $personCost;
        $totalArr[] = $corporateCost;

        $totalT3 = [];
        foreach ($totalArr as $values) {
            foreach ($values as $code => $value) {
                $totalT3[$code] += $value;
            }
        }
        return [$personCost, $corporateCost, $formattedLCAs, $totalT3];
    }

    public function add(){
        /**
         * Save Labor Cost Detail
         */
        //term id
        $buTermId = $_SESSION['BU_TERM_ID'];
        if(empty($this->Session->read('SEARCH_LABOR_COST.target_year'))){
            $this->Session->write('SEARCH_LABOR_COST.target_year', json_decode($this->request->data['labor_cost_details_datas'])[0]->target_year);
        }
        if(empty($this->Session->read('SEARCH_LABOR_COST.layer_code'))){
            $this->Session->write('SEARCH_LABOR_COST.layer_code', json_decode($this->request->data['labor_cost'])[0]->group_code);
        }
        $target_year = empty($this->Session->read('SEARCH_LABOR_COST.target_year'))? $this->request->data['target_year']: $this->Session->read('SEARCH_LABOR_COST.target_year');
        $group_code = empty($this->Session->read('SEARCH_LABOR_COST.layer_code'))? $this->request->data['group_code'] : $this->Session->read('SEARCH_LABOR_COST.layer_code');
        
        #get complete flag
        $completed_flag =  $this->BuApprovedLog->find('first', array(
        'fields' => array('flag'),
        'conditions' => array(
            'menu_id' => Setting::MENU_ID_LIST['LaborCostDetails'],
            'bu_term_id' => $buTermId,
            'department_code' => '0',
            'bu_code' => '0',
            'target_year' => $target_year,
            'group_code' => $group_code,
            'line_code' => '0',
            'business_code' => '0',
            'sub_business_code' => '0',
        )
        ))['BuApprovedLog']['flag'];        
        if($completed_flag == 2){
            $errorMsg = parent::getErrorMsg('SE161');
            $this->Flash->error($errorMsg, array('key'=>'lcd_error'));
            $this->redirect(array("action" => "index"));
        }        
        $unformatT1 = json_decode($this->request->data['labor_cost_details_datas']);
        $formattedT1 = [];
        foreach ($unformatT1 as $value) {
            # code...
            $lcd_tmp_arr = json_decode(json_encode($value), true);
            if(empty($lcd_tmp_arr['id'])){#register users
                if($lcd_tmp_arr['position_code'] != 0 && $lcd_tmp_arr['new_user_name'] == '')
                {
                    $formattedT1[] = $lcd_tmp_arr;
                }elseif($lcd_tmp_arr['position_code'] > 0 && $lcd_tmp_arr['new_user_name'] != ''){#new users
                    $duplicate_user = $this->LaborCostDetail->find('count',array(
                        'conditions' => array(
                            'bu_term_id' => $buTermId,
                            'target_year' => $lcd_tmp_arr['target_year'],
                            'new_user_name' => trim($lcd_tmp_arr['new_user_name']),
                        )
                    ));
                    if($duplicate_user > 0) {
                        $this->Flash->error(parent::getErrorMsg('SE156'), array(
                            'key' => 'lcd_error'
                        ));
                        $this->redirect(['action'=>'index']);
                    }

                    $formattedT1[] = $lcd_tmp_arr;
                }elseif($lcd_tmp_arr['new_user_name'] == '' && $lcd_tmp_arr['position_code'] == '' && count($lcd_tmp_arr)>0){#adjustment cell
                    $formattedT1[] = $lcd_tmp_arr;
                }
            }else{
                $formattedT1[] = $lcd_tmp_arr;
            }
        }
        /**
         * Save Labor Cost Adjustment
         */
        $unformatT3 = json_decode($this->request->data['labor_cost_adjustments_datas']);
        $formattedT3 = [];
        foreach ($unformatT3 as $value) {
            # code...
            $formattedT3[] = json_decode(json_encode($value), true);
        }

        /**
         * Save Budget
         */
        $unformatBudget = json_decode($this->request->data['budget_amount']);
        $formattedBudget = [];
        // $target_year = $unformatBudget[0]->target_year;

        $now = date('Y-m-d H:i:s');
        $login_id = $this->Session->read('LOGIN_ID');
        $account = $this->LaborCostDetail->getAccountCodeForBudget();
        $account_code = array_values($account)[0];
        $account_id = array_keys($account)[0];
        $layer_codes = [];
        foreach ($unformatBudget as $value) {
            # code...
            $value->account_id = $account_id;
            $value->account_code = $account_code;
            $value->created_by = $login_id;
            $value->updated_by = $login_id;
            $value->created_date = $now;
            $value->updated_date = $now;
            $value->unit = Setting::BU_UNIT;
            $layer_codes[] = $value->layer_code;
            $formattedBudget[] = json_decode(json_encode($value), true);
        }

        $this->Budget->deleteAll([
            'bu_term_id' => $buTermId,
            'target_year' => $target_year,
            'layer_code' => $layer_codes,
            'account_code' => $account_code
        ], false);

        /***
         * Save Labor Cost
         */
        $data = json_decode($this->request->data['labor_cost']);
        $group_code = $data->group_code;
        $lc = json_decode(json_encode($data->lc), true);//user_id => person_count
        // $userId = array_keys($lc);//user_id array
        $userId = json_decode(json_encode($data->useridArr), true);
        $halfPos = array_keys(PositionType::PositionConstant);//half position name array
        $new_user = $userId;
        $user = json_decode(json_encode($data->USER), true);
        foreach ($userId as $idkey => $ids) {
            if(strpos($ids, "total_") !== false) {
                $ids = '0';
            }
            $userId[$idkey] = $ids;
        }
        //term_id
        $laborCosts = $this->LaborCost->find('all', [
            'joins' =>[
                [
                    'table' => 'positions',
                    'alias' => 'Position',
                    'type'  =>  'left',
                    'conditions' => [
                        'Position.position_code = LaborCost.position_code'
                    ]                
                ],
            ],
            'fields' => [
                'LaborCost.*', 
                'Position.position_name',
            ],
            'conditions' => [
                'LaborCost.bu_term_id' => $buTermId,
                'LaborCost.layer_code' => $group_code,
                'LaborCost.target_year' => $target_year,
                'OR' => array(
                    'LaborCost.adjust_name IS NULL',
                    'LaborCost.adjust_name' => '',
                    'LaborCost.adjust_name' => '0'
                ),
                'OR'=>array(
                    array('LaborCost.user_id' => $userId),
                    array('LaborCost.user_id = "0" AND LaborCost.new_user_name IS NOT NULL'),
                    array('LaborCost.user_id IS NULL')
                ),
            ],
            'group' => ["LaborCost.id"],
            'order' => ["LaborCost.id", "LaborCost.new_user_name"],
        ]);
        $formattedLC = [];
        $login_user_id = $this->Session->read('LOGIN_ID');
        $current_time = date('Y-m-d H:i:s', time());
        if(sizeof($laborCosts) == 0){ 
            foreach($new_user as $key=>$value){
                if(strpos($value, "total_") === false) {
                    $position_data = $this->Position->getUserPosition($value, $target_year);
                }else {
                    $data_lc = array_values($user);
                    $data_user = explode('_', $data_lc[$key]);
                    
                    $value = '0';
                    
                    $position_data = $this->Position->find('all', array(
                        'conditions' => array(
                            'flag' => 1,
                            'position_code' => $data_user[1],
                            'target_year' => $target_year
                        )
                    ));
                    
                    $position_data[0]['positions']['position_code'] = $position_data[0]['Position']['position_code'];
                    $position_data[0]['positions']['position_name'] = $position_data[0]['Position']['position_name'];
                    $new_user_name = $data_user[2];
                    $laborCosts[$key]['LaborCost']['new_user_name'] = $new_user_name;

                }
                //term id
                $laborCosts[$key]['LaborCost']['bu_term_id'] = $buTermId;
                $laborCosts[$key]['LaborCost']['target_year'] = $target_year;
                $laborCosts[$key]['LaborCost']['position_code'] = $position_data[0]['positions']['position_code'];
                $laborCosts[$key]['LaborCost']['user_id'] = $value;
                $laborCosts[$key]['LaborCost']['layer_code'] = $group_code;
                $laborCosts[$key]['LaborCost']['adjust_name'] = '';
                $laborCosts[$key]['LaborCost']['person_count'] = 0;
                $laborCosts[$key]['LaborCost']['b_person_count'] = 0;
                $laborCosts[$key]['LaborCost']['common_expense'] = 0;
                $laborCosts[$key]['LaborCost']['b_person_total'] = 0;
                $laborCosts[$key]['LaborCost']['labor_unit'] = 0.00;
                $laborCosts[$key]['LaborCost']['corp_unit'] = 0.00;
                $laborCosts[$key]['LaborCost']['yearly_labor_cost'] = 0.00;
                $laborCosts[$key]['LaborCost']['unit_labor_cost'] = 0.00;

                $laborCosts[$key]['LaborCost']['adjust_labor_cost'] = 0.00;
                $laborCosts[$key]['LaborCost']['yearly_corpo_cost'] = 0.00;
                $laborCosts[$key]['LaborCost']['unit_corpo_cost'] = 0.00;
                $laborCosts[$key]['LaborCost']['adjust_corpo_cost'] = 0.00;

                $laborCosts[$key]['LaborCost']['flag'] = 1;
                $laborCosts[$key]['LaborCost']['created_by'] = $login_user_id;
                $laborCosts[$key]['LaborCost']['updated_by'] = $login_user_id;
                $laborCosts[$key]['LaborCost']['created_date'] = $current_time;
                $laborCosts[$key]['LaborCost']['updated_date'] = $current_time;
                $laborCosts[$key]['Position']['position_name'] = $position_data[0]['positions']['position_name'];
                
                if($new_user_name == 'null') {
                    unset($laborCosts[$key]);
                }
                if($data_user[0] == 'null' && $data_user[1] == 'null' && ($data_user[2] == '' || $data_user[2] ==' null')) {
                    unset($laborCosts[$key]);
                }
            }
        }
        else {
            $saved_laborcost = array_column(array_column($laborCosts, 'LaborCost'), 'id');
            $saved_newuser = array_filter(array_column(array_column($laborCosts, 'LaborCost'), 'new_user_name'));

            if(count($user) > count($saved_newuser)) {
                $new_rows = [];
                foreach ($user as $userNameId => $userValue) {
                    if($userNameId != '' && strpos($userValue, "_") !== false) {
                        $data_user = explode('_', $userValue);
                        $u_id = 0;
                        $p_code = $data_user[1];
                        $u_name = $data_user[2];
                        $p_cnt = $data_user[3];

                        if(!in_array($u_name, $saved_newuser)) {
                            $position_data = $this->Position->find('first', array(
                                'conditions' => array(
                                    'flag' => 1,
                                    'position_code' => $p_code,
                                    'target_year' => $target_year
                                )
                            ));
                            
                            $new_rows['LaborCost']['bu_term_id'] = $buTermId;
                            $new_rows['LaborCost']['target_year'] = $target_year;
                            $new_rows['LaborCost']['position_code'] = $p_code;
                            $new_rows['LaborCost']['new_user_name'] = $u_name;
                            $new_rows['LaborCost']['user_id'] = $u_id;
                            $new_rows['LaborCost']['layer_code'] = $group_code;
                            $new_rows['LaborCost']['adjust_name'] = '';
                            $new_rows['LaborCost']['person_count'] = '0';
                            $new_rows['LaborCost']['b_person_count'] = '0';
                            $new_rows['LaborCost']['common_expense'] = '0';
                            $new_rows['LaborCost']['b_person_total'] = '0';
                            $new_rows['LaborCost']['labor_unit'] = '0.00';
                            $new_rows['LaborCost']['corp_unit'] = '0.00';
                            $new_rows['LaborCost']['yearly_labor_cost'] = '0.00';
                            $new_rows['LaborCost']['unit_labor_cost'] = '0.00';

                            $new_rows['LaborCost']['adjust_labor_cost'] = '0.00';
                            $new_rows['LaborCost']['yearly_corpo_cost'] = '0.00';
                            $new_rows['LaborCost']['unit_corpo_cost'] = '0.00';
                            $new_rows['LaborCost']['adjust_corpo_cost'] = '0.00';

                            $new_rows['LaborCost']['flag'] = 1;
                            $new_rows['LaborCost']['created_by'] = $login_user_id;
                            $new_rows['LaborCost']['updated_by'] = $login_user_id;
                            $new_rows['LaborCost']['created_date'] = $current_time;
                            $new_rows['LaborCost']['updated_date'] = $current_time;
                            $new_rows['Position']['position_name'] = $position_data['Position']['position_name'];
                            array_push($laborCosts, $new_rows);
                        }   
                    }
                }
            }
        }
        foreach ($laborCosts as $keys => $laborCost) {
            if($laborCost['LaborCost']['labor_unit'] == 0){
                if($laborCost['LaborCost']['position_code'] > 0) {
                    $user_id = $laborCost['LaborCost']['user_id'];
                    $newUser = $laborCost['LaborCost']['new_user_name'];
                    $p_count = $lc[$user_id];
                    
                    if($user_id == 0 && $newUser != '') {
                        $data_lc = $user[$newUser];
                        $p_count = explode('_', $data_lc)[3];
                    }
                    $laborCost['LaborCost']['bu_term_id'] = $buTermId;
                    $laborCost['LaborCost']['person_count'] = $p_count;
                    $laborCost['LaborCost']['b_person_count'] = in_array($laborCost['Position']['position_name'], $halfPos) ? $p_count * 0.5 : $p_count;
                    $formattedLC[] = $laborCost['LaborCost'];
                }
                
            }
        }

        $lcd_db = $this->LaborCostDetail->getDataSource();
        $lca_db = $this->LaborCostAdjustment->getDataSource();
        $bg_db = $this->Budget->getDataSource();
        $lc_db =  $this->LaborCost->getDataSource();
        $bal_db =  $this->BuApprovedLog->getDataSource();
        try{
            $lcd_db->begin();
            $lca_db->begin();
            $bg_db->begin();
            $lc_db->begin();
            $bal_db->begin();

            $saveT1 = $this->LaborCostDetail->saveMany(array_filter($formattedT1));
            $saveT3 = $this->LaborCostAdjustment->saveMany($formattedT3);
            $bg = $this->Budget->saveAll($formattedBudget);
            if(sizeof($formattedLC) > 0) $LC = $this->LaborCost->saveAll($formattedLC);

            if($saveT1 && $saveT3 && $bg && $LC){

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
                        'target_year' => $target_year,
                        'department_code' => '0',
                        'bu_code' => '0',
                        'group_code' => $group_code,
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
                            'target_year' => $target_year,
                            'department_code' => '0',
                            'bu_code' => '0',
                            'group_code' => $group_code,
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
                            'target_year' => $target_year,
                            'department_code' => '0',
                            'bu_code' => '0',
                            'group_code' => $group_code,
                            'line_code' => '0',
                            'business_code' => '0',
                            'sub_business_code' => '0',
                            'flag' => $approved_flag,
                            'updated_by' => $this->Session->read('LOGIN_ID'),
                        ]
                    );
    
                }
    
                //update success msg
                $this->Flash->success($successMsg, array(
                    'key' => 'lcd_success'
                ));
        
            }else{
                $lcd_db->rollback();
                $lca_db->rollback();
                $bg_db->rollback();
                $lc_db->rollback();
                $bal_db->rollback();
                $this->Flash->error(parent::getErrorMsg('SE003'), array(
                    'key' => 'lcd_error'
                ));
            }

            $lcd_db->commit();
            $lca_db->commit();
            $bg_db->commit();
            $lc_db->commit();
            $bal_db->commit();

        }catch (Exception $e){
            $lcd_db->rollback();
            $lca_db->rollback();
            $bg_db->rollback();
            $lc_db->rollback();
            $bal_db->rollback();

            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $this->Flash->error(parent::getErrorMsg('SE003'), array(
                'key' => 'lcd_error'
            ));
        }
        //if filter data in session, got to filter page
        $query_params = '';
        if($this->Session->read('SEARCH_LABOR_COST')){
            $session = $this->Session->read('SEARCH_LABOR_COST');
            $target_year = $session['target_year'];
            $group_code = $session['group_code'];
            $query_params = '?target_year='.$target_year.'&group_code='.$group_code;
        }
        $this->redirect(['action'=>'index'.$query_params]);
    }
    public function getGroupCode()
    {
        parent::checkAjaxRequest($this);
        $target_year = $this->request->data('target_year');
        
        $data = $this->LaborCost->groupCodeList($target_year);
    
        echo json_encode($data);
    }

    public function download()
    {  
        $users = json_decode($this->request->data['users']);
        foreach ($users as $ukey => $uvalue) {
            if(strpos($uvalue, "total_") !== false) {
                unset($users[$ukey]);
            }
        }

        //initial state is current year, filter state is filter year
        $budget_year = $_SESSION['BudgetTargetYear'];
        $target_year = $this->request->data['target_year'] ? $this->request->data['target_year'] : $budget_year;
        $latest_group_code = $this->LaborCostDetail->latestGroupCode($target_year);
        $group_code = $this->request->data['group_code'] ? $this->request->data['group_code'] : $latest_group_code;

        $term_list = $this->Session->read('TERM_YEAR_LIST');
        //get labour costs
        list($businesses, $business_codes) = $this->LaborCostDetail->businessCodes($term_list[$target_year], $group_code);
        foreach(array_keys($term_list) as $target_year):
        $years[] = ['target_year' => $target_year];
        endforeach;
        // $years[] = ['target_year' => $target_year - 1];
        // $years[] = ['target_year' => +$target_year];
        // $years[] = ['target_year' => $target_year + 1];
        // $years[] = ['target_year' => $target_year + 2];
        // $years[] = ['target_year' => $target_year + 3];

        $first_year = true;
        $PHPExcel  = $this->PhpExcel;
        #Start Excel Preparation
        $objPHPExcel = $PHPExcel->createWorksheet()->setDefaultFont('Calibri', 11);
        foreach($years as $index => $value){
            if($index > 0){
                $objPHPExcel->createSheet();
            }
            $objPHPExcel->setActiveSheetIndex($index);
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3);
            $sheet->setShowGridlines(true);
            $sheet->getProtection()->setPassword('*****');
            $sheet->getProtection()->setSheet(true);
            $sheet->getProtection()->setInsertRows(true);
            $sheet->getProtection()->setInsertColumns(true);
            $this->_createExcel($term_list[$value['target_year']], $group_code, $business_codes, $sheet, $users, $user, $first_year);
            // $this->_createExcel(2009, $group_code, $business_codes, $sheet);
            if($first_year) {
                $first_year = false;
            }
        }
        if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}
        $layerName = $this->Layer->getLayerName($group_code);
        $layer_gp_name =($lang_name=='en') ? $layerName[0]['Layer']['name_en'] : $layerName[0]['Layer']['name_jp']; 

        // $file_name  = __('ビジネス別人員表').'_'.$group_code.$layer_gp_name;
        $file_name  = $group_code.$layer_gp_name;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');

        // Encode the filename using urlencode
        $encodedFileName = urlencode('ビジネス別人員表_' . $file_name . '.xlsx');
        header('Content-Disposition: attachment; filename="' . $encodedFileName . '"; charset=utf-8');

        $objPHPExcel->output($encodedFileName); 
        //$objPHPExcel->output($file_name.'.xlsx'); original code ¬_¬

        $query_params = '';
        if($this->Session->read('SEARCH_LABOR_COST')){
            $session = $this->Session->read('SEARCH_LABOR_COST');
            $target_year = $session['target_year'];
            $group_code = $session['group_code'];
            $query_params = '?target_year='.$target_year.'&group_code='.$group_code;
        }
        $this->redirect(['action'=>'index'.$query_params]);
    }

    public function _createExcel($target_year, $group_code, $business_codes, $sheet, $users, $user, $first_year){

        $sheet->setTitle('【'. explode('-',$target_year[0])[0] .'年度】');
        $styles = $this->_excelStyles();
        //Read Me
        /*$sheet->setCellValue('B4', __('【入力事項】'));
        $sheet->setCellValue('B5', __('１．案件毎の人員配置を明確化（人員数を入力）'));
        $sheet->mergeCells('B5:F5');
        $sheet->setCellValue('B6', __('２．案件のうち、上段が既存取引、下段が新規取引'));
        $sheet->mergeCells('B6:F6');
        $sheet->setCellValue('B7', __('３．経営指導料は案件毎に直接入力（８９行、172行）'));
        $sheet->mergeCells('B7:F7');
        $sheet->setCellValue('B8', __('４．予算人員表は、'.explode('-',$target_year[0])[0].'年度は実績と合致させる。差異は、異動調整箇所にて調整。'));
        $sheet->mergeCells('B8:H8');

        for ($rhide = 4; $rhide < 9; $rhide++) { 
            $sheet->getRowDimension($rhide)->setVisible(false);
        }

        $sheet->getStyle('B4:H8')->applyFromArray([
            'font'  => array(
                'color' => array('rgb' => '632523'),
                'size'  => 12,
            )
        ]);*/

        $sheet->setCellValue('B2', __('【'. explode('-',$target_year[0])[0] .'年度】'));
        $sheet->mergeCells('B2:C2');
        $sheet->getStyle('B2:C2')->applyFromArray([
            'font'  => array(
                'size'  => 18,
            )
        ]);

        list($constHeader, $tableOne, $totalTableOne) = $this->_formatExcelT1($target_year, $group_code, $users, $user);//table one
        
        //if No Business Code
        if(!$constHeader || !$tableOne){
            $sheet->setCellValue('B5', __("計算する為のデータがシステムにありません。"));
            $sheet->mergeCells('B5:H5');
            $sheet->getStyle('B5:H5')->applyFromArray([
                'font'  => array(
                    'size'  => 18,
                    'color' => ['rgb' => 'FF0000']
                ),
            ]);
            return true;
        }

        $startRow = 3;//1, 2, 3, 4
        $startCol = 1;
        $column = 1;

        //table one
        $columnLetter = PHPExcel_Cell::stringFromColumnIndex(count($constHeader)-1);//for b person count
        $bPersonCount = [];
        $bpcRow = $startRow + 2;
        $Pos_Constant = array_keys(PositionType::PositionConstant);

        foreach ($tableOne as $value) {
            if($value['user_id']){
                $bPersonCount[$value['user_id']][] = $columnLetter.$bpcRow;
                $bPersonCount[$value['user_id']][] = in_array($value['position_name'], $Pos_Constant) ? 0.5 : 1;
                $bpcRow++;
            }else if($value['position_code'] > 0) {
                $bPersonCount[$value['user_name']][] = $columnLetter.$bpcRow;
                $bPersonCount[$value['user_name']][] = in_array($value['position_name'], $Pos_Constant) ? 0.5 : 1;
                $bpcRow++;
            }
        }
        
        $sumPerEmployee = [];//formula for total per employee
        $sumPerProject = [];//formula for total per project
        $allTotal = [];//formula all total
        $allTotalPerColumn = []; // total of each column
        $notLayer = ['number', 'user_name','b_person_count','unit_labor_cost','unit_corpo_cost','business_type','comment'];// key is not layer

        //table two
        $sumPerNewProject = [];//formula per new project for table two
        $sumPerOldProject = [];//formula per old project for table two

        //table three
        //?(old working hour + new working hour) * personnel cost
        $sumPerPerson = [];
        //?(old working hour + new working hour) * corporate cost
        $sumPerCorporate = [];

        /****
         * Table One
         ***/

         //set header
        foreach ($constHeader as $key => $header) {
            $sheet->setCellValueByColumnAndRow($column, $startRow, $header);
            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);

            $mergeRow = $startRow +1;
            $mergeCell = $columnLetter . $startRow . ':'. $columnLetter . $mergeRow;
            $sheet->mergeCells($mergeCell);
            $sheet->getStyle($mergeCell)->applyFromArray($styles['text_align_center']);
            $sheet->getColumnDimension($columnLetter)->setWidth(12);
            $sheet->getStyle($mergeCell)->applyFromArray($styles['border_thin']);
            if($column < 6){
                $sheet->getStyle($mergeCell)->applyFromArray($styles['double_border_right']);
            }
            if($key == 'total'){
                $sheet->getStyle($mergeCell)->applyFromArray([
                    'borders' => [
                        'top' => [
                            'style' => PHPExcel_Style_Border::BORDER_THICK
                        ],
                        'left' => [
                            'style' => PHPExcel_Style_Border::BORDER_THICK
                        ],
                        'right' => [
                            'style' => PHPExcel_Style_Border::BORDER_THICK
                        ]
                    ]
                ]);
            }
            $column++;
        }
        $startRow += 1;
        
        #remove comment column because it is only necessary at header
        array_pop($constHeader);
        //set working hour cell
        foreach ($tableOne as $t1) {

            $column = $startCol;
            $startRow += 1;
            $sumPerEmployee[0] = 'G'.$startRow;//start cell for total employee working hour formula
            
            //loop column
            foreach ($constHeader as $key => $header) {

                if(!in_array($key, $notLayer)){
                    $allTotalPerColumn[$key][] += $t1[$key];
                }
    
                //4 constant cells and merge them
                if($column < 6){
                    if($t1['business_type'] == 1){
                        if($key == 'number' && $column < 2 && $t1['adjust_name'] != '異動による調整額' ) {
                            $sheet->setCellValueByColumnAndRow($column, $startRow, $t1[$key]);
                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 

                            $mergeRow = $startRow +1;
                            $mergeCell = $columnLetter . $startRow . ':'. $columnLetter . $mergeRow;
                            $sheet->mergeCells($mergeCell);
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['text_align_center']);
                            // $sheet->getStyle($mergeCell)->applyFromArray($styles['border_thin']);
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['double_border_right']);
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['bottom_dot_custom']);

                        }
                        //user working hour cell 
                        elseif($t1['user_id']){
                            $sheet->setCellValueByColumnAndRow($column, $startRow, $t1[$key]);
                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 

                            $mergeRow = $startRow +1;
                            $mergeCell = $columnLetter . $startRow . ':'. $columnLetter . $mergeRow;
                            $sheet->mergeCells($mergeCell);
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['text_align_left']);
                            // $sheet->getStyle($mergeCell)->applyFromArray($styles['border_thin']);
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['double_border_right']);

                            if($key == 'unit_labor_cost' || $key == 'unit_corpo_cost'){
                                $sheet->getStyle($mergeCell)->applyFromArray($styles['number_align']);
                                $sheet->getStyle($mergeCell)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');
                            }
    
                            else if($key == 'b_person_count'){
                                // $formula = '=(' . $bPersonCount[$t1['user_id']][0] . '+' . $bPersonCount[$t1['user_id']][2] . ') * ' .$bPersonCount[$t1['user_id']][1];
                                $sheet->setCellValue($columnLetter.$startRow, $t1['b_person_total']);
                                $sheet->getStyle($mergeCell)->applyFromArray($styles['text_align_center']);
                                $sheet->getStyle($mergeCell)->getNumberFormat()->setFormatCode('""#,####0.0000;[Red]"-"#,####0.0000');
                            }
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['bottom_dot_custom']);

                        }
                        elseif($t1['user_id'] == 0 && $t1['position_code']) {
                            
                            $sheet->setCellValueByColumnAndRow($column, $startRow, $t1[$key]);
                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 

                            $mergeRow = $startRow +1;
                            $mergeCell = $columnLetter . $startRow . ':'. $columnLetter . $mergeRow;
                            $sheet->mergeCells($mergeCell);
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['text_align_left']);
                            // $sheet->getStyle($mergeCell)->applyFromArray($styles['border_thin']);
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['double_border_right']);

                            if($key == 'unit_labor_cost' || $key == 'unit_corpo_cost'){
                                $sheet->getStyle($mergeCell)->applyFromArray($styles['number_align']);
                                $sheet->getStyle($mergeCell)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');
                            }
                            else if($key == 'b_person_count'){
                                // $formula = '=(' . $bPersonCount[$t1['user_name']][0] . '+' . $bPersonCount[$t1['user_name']][2] . ') * ' .$bPersonCount[$t1['user_name']][1];
                                $sheet->setCellValue($columnLetter.$startRow, $t1['b_person_total']);
                                $sheet->getStyle($mergeCell)->applyFromArray($styles['text_align_center']);
                                $sheet->getStyle($mergeCell)->getNumberFormat()->setFormatCode('""#,####0.0000;[Red]"-"#,####0.0000');
                            }
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['bottom_dot_custom']);
                        }
                        //adjustment cell
                        elseif(!$t1['user_id'] && $key == 'user_name'){
                            $sheet->setCellValueByColumnAndRow($column, $startRow, $t1['adjust_name']);
        
                            $mergeRow = $startRow +1;
                            $mergeCol = $column + 3;
                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                            $mergeColumnLetter = PHPExcel_Cell::stringFromColumnIndex($mergeCol); 
                            $mergeCell = $columnLetter . $startRow . ':'. $mergeColumnLetter . $mergeRow;

                            $sheet->mergeCells($mergeCell);
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['text_align_left']);
                            // $sheet->getStyle($mergeCell)->applyFromArray($styles['border_thin']);
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['bottom_dot_custom']);
                            $sheet->getStyle($mergeCell)->applyFromArray($styles['double_border_right']);

                        }

                    }
                }
                //1 const cell, changable cell (businesss) and total cell
                elseif ($column > 5 ) {
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    //1 const cell (business type)
                    if($key == 'business_type'){
                        $business_type = $t1['business_type'] == 1 ? '既存' : '新規';
                        $sheet->setCellValueByColumnAndRow($column, $startRow, $business_type);
                        // $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                        if($t1['business_type'] == 2){                                
                            $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['bottom_border_dot']);
                        }else{
                            $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['top_border_dot']);
                        }
                        $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['double_border_right']);
                    }
                    //changable cell (business)
                    else{
                        
                        //input cell
                        if($key != 'total'){
                            $sheet->setCellValueByColumnAndRow($column, $startRow, $t1[$key]);
                            if($first_year){
                                $sheet->getStyle($columnLetter.$startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                            } else {
                                $sheet->getStyle($columnLetter.$startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffcc');
                                $sheet->getStyle($columnLetter.$startRow)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                            }

                            //style
                            if($t1['business_type'] == 2){

                                $sumPerNewProject[$key][] = $columnLetter.$startRow;
                                $reduceRow = $startRow - 1;//can't get data of lower cell in merge Cell, So get upper cell
                                $sumPerPerson[$key][] = '(D'.$reduceRow . '*' . $columnLetter.$startRow . ')';
                                $sumPerCorporate[$key][] = '(E' . $reduceRow . '*' . $columnLetter . $startRow . ')';
                                // $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['top_border_dot']);
                                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['bottom_border_dot']);

                            }else{
                                $sumPerOldProject[$key][] = $columnLetter.$startRow;
                                
                                
                                $sumPerPerson[$key][] = '(D'.$startRow . '*' . $columnLetter.$startRow . ')';
                                $sumPerCorporate[$key][] = '(E' . $startRow . '*' . $columnLetter . $startRow . ')';
                                
                                // $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['bottom_border_dot']);
                                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['top_border_dot']);
                            }

                            $sumPerEmployee[1] = $columnLetter.$startRow;//end cell for total employee working hour formula
                            $sumPerProject[$key][] = $columnLetter.$startRow;
                            $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00'); 
                        }

                        //total cell
                        else{
                            $allTotal[$key][] = $columnLetter.$startRow;
                            // $formula = '=SUM(' . implode(':',$sumPerEmployee) . ')';
                            $sheet->setCellValue($columnLetter.$startRow, $t1['total']);
                            if($t1['business_type'] == 2){
                                
                                // $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['top_border_dot']);
                                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['bottom_border_dot']);

                            }else{
                                
                                // $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['bottom_border_dot']);
                                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['top_border_dot']);
                            }
                            $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                                'borders' => [
                                    'left' => [
                                        'style' => PHPExcel_Style_Border::BORDER_THICK
                                    ],
                                    'right' => [
                                        'style' => PHPExcel_Style_Border::BORDER_THICK
                                    ]
                                ]
                            ]);
                            $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0.00000;[Red]"-"#,##0.00000'); 
                        }    
                    }

                    
                }
                
                $column++;
            }
            if(isset($t1['comment'])){
                $sheet->setCellValueByColumnAndRow($column, $startRow, $t1['comment']);
                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                // debug($columnLetter.$startRow);
                // $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                if($first_year){
                    $sheet->getStyle($columnLetter.$startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                } else {
                    $sheet->getStyle($columnLetter.$startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffcc');
                    $sheet->getStyle($columnLetter.$startRow)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                }
                if($t1['business_type'] == 2){                                
                    // $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['top_border_dot']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['bottom_border_dot']);
                }else{                    
                    // $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['bottom_border_dot']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['top_border_dot']);
                }
            }
        }
        // debug($sumPerCorporate);exit;
        //total in table one
        $column = $startCol;
        $startRow += 1;
        foreach ($constHeader as $key => $header) {
            if($key == 'user_name'){
                $sheet->setCellValueByColumnAndRow($column, $startRow, '合　　　計');

                $mergeCol = $column + 4;
                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                $mergeColumnLetter = PHPExcel_Cell::stringFromColumnIndex($mergeCol); 
                $mergeCell = $columnLetter . $startRow . ':'. $mergeColumnLetter . $startRow;
    
                $sheet->mergeCells($mergeCell);
                $sheet->getStyle($mergeCell)->getFont()->setBold(true);
                $sheet->getStyle($mergeCell)->applyFromArray($styles['text_align_left']);
                $sheet->getStyle($mergeCell)->applyFromArray($styles['border_thin']);
                $sheet->getStyle($mergeCell)->applyFromArray($styles['double_border_right']);
                $sheet->getRowDimension($startRow)->setRowHeight(25);
            }elseif($column > 5){
                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['number_align']);
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                $sheet->getStyle($columnLetter.$startRow)->getFont()->setBold(true);

                if($key == 'total'){
                    // $formula = '=(' . implode('+', $allTotal[$key]) . ')';
                    $sheet->setCellValueByColumnAndRow($column, $startRow, array_sum($allTotalPerColumn[$key]));
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'left' => [
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ],
                            'right' => [
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ]
                        ]
                    ]);
                    $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0.00000;[Red]"-"#,##0.00000');
                }else{
                    // $formula = '=(' . implode('+', $sumPerProject[$key]) . ')';
                    
                    $sheet->setCellValueByColumnAndRow($column, $startRow, array_sum($allTotalPerColumn[$key]));
                    $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');
                }
                
            }
            $column++;
        }
        //end table one.....

        /****
         * Table Two
         ***/
        list($tableTwoNew, $tableTwoExist) = $this->_formatDataT2(explode('-',$target_year[0])[0], $business_codes, $users, $user);//table two
        $column = $startCol;
        $startRow += 2;

        $existTotal[] = 'G'.$startRow;
        foreach ($constHeader as $key => $header) {
            if($column > 4){
                if($key == 'unit_corpo_cost'){
                    $sheet->setCellValueByColumnAndRow($column, $startRow, '合計');

                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                    $mergeRow = $startRow + 1;
                    $mergeCell = $columnLetter . $startRow . ':'. $columnLetter . $mergeRow;
                    $sheet->mergeCells($mergeCell);
                    $sheet->getStyle($mergeCell)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter . $startRow)->applyFromArray($styles['border_thick']);
                    
                }
                else if ($key == 'business_type') {
                    $sheet->setCellValueByColumnAndRow($column, $startRow, '既存');

                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['double_border_right']);
                }
                else{
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['number_align']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                    $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0.0000;[Red]"-"#,##0.0000');

                    if($key == 'total'){
                        // $formula = '=SUM(' . implode(':', $existTotal) . ')';
                        // $sheet->setCellValueByColumnAndRow($column, $startRow, $formula);
                        $sheet->setCellValueByColumnAndRow($column, $startRow, $tableTwoExist[$key]);
                        $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                            'borders' => [
                                'left' => [
                                    'style' => PHPExcel_Style_Border::BORDER_THICK
                                ],
                                'right' => [
                                    'style' => PHPExcel_Style_Border::BORDER_THICK
                                ],
                                'top' => [
                                    'style' => PHPExcel_Style_Border::BORDER_THICK
                                ]
                            ]
                        ]);
                    }else{
                        // $formula = '=(' . implode('+', $sumPerOldProject[$key]) . ')';
                        // $sheet->setCellValueByColumnAndRow($column, $startRow, $formula);
                        $sheet->setCellValueByColumnAndRow($column, $startRow, $tableTwoExist[$key]);
                        $existTotal[1] = $columnLetter.$startRow;
                    }
                } 
                
            }
            $column++;
        }       

        $column = $startCol;
        $startRow += 1; 
        

        $newTotal[] = 'G'. $startRow;
        foreach ($constHeader as $key => $header) {
            
            if($column > 4){
                if($key == 'unit_corpo_cost'){
                    // $sheet->setCellValueByColumnAndRow($column, $startRow, '合計');

                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                    $mergeRow = $startRow + 1;
                    // $mergeCell = $columnLetter . $startRow . ':'. $columnLetter . $mergeRow;
                    // $sheet->mergeCells($mergeCell);
                    // $sheet->getStyle($mergeCell)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter . $startRow)->applyFromArray($styles['border_thick']);
                }
                elseif ($key == 'business_type') {
                    $sheet->setCellValueByColumnAndRow($column, $startRow, '新規');

                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['double_border_right']);
                }
                else{
                    
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['number_align']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                    $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0.0000;[Red]"-"#,##0.0000');

                    if($key == 'total'){
                        // $formula = '=SUM(' . implode(':', $newTotal) . ')';
                        // $sheet->setCellValueByColumnAndRow($column, $startRow, $formula);
                        $sheet->setCellValueByColumnAndRow($column, $startRow, $tableTwoNew[$key]);
                        $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                            'borders' => [
                                'left' => [
                                    'style' => PHPExcel_Style_Border::BORDER_THICK
                                ],
                                'right' => [
                                    'style' => PHPExcel_Style_Border::BORDER_THICK
                                ],
                                'bottom' => [
                                    'style' => PHPExcel_Style_Border::BORDER_THICK
                                ]
                            ]
                        ]);
                    }else{
                        // $formula = '=(' . implode('+', $sumPerNewProject[$key]) . ')';
                        // $sheet->setCellValueByColumnAndRow($column, $startRow, $formula);
                        $sheet->setCellValueByColumnAndRow($column, $startRow, $tableTwoNew[$key]);
                        $newTotal[1] = $columnLetter.$startRow;
                    }
                }   

            }
            $column++;
        }

        /******
         * Table Three
         ****/
        list($personnelCost, $corporateCost, $laborCostAdjustments, $totalLCA) = $this->_formatDataT3(explode('-',$target_year[0])[0], $business_codes, $group_code);
        
        $startRow += 2;
        $column = $startCol;
        //Header
        foreach ($constHeader as $key => $header) {
            foreach($tableOne as $t1key => $laborCost){
                if(!in_array($key, $notLayer)){
                    $personnelCost[$key] += $laborCost[$key] * $laborCost['personnel_cost']; 
                    $corporateCost[$key] += $laborCost[$key] * $laborCost['corporate_cost'];
                }
            }
           
            if($column > 6){
                $sheet->setCellValueByColumnAndRow($column, $startRow, $header);
                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
    
                $mergeRow = $startRow +1;
                $mergeCell = $columnLetter . $startRow . ':'. $columnLetter . $mergeRow;
                $sheet->mergeCells($mergeCell);
                $sheet->getStyle($mergeCell)->applyFromArray($styles['text_align_center']);
                $sheet->getColumnDimension($columnLetter)->setWidth(12);
                $sheet->getStyle($mergeCell)->applyFromArray($styles['border_thin']);
            }
            if($key == 'total'){
                // debug($columnLetter.$startRow);
                $sheet->getStyle($mergeCell)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'style' => PHPExcel_Style_Border::BORDER_THICK
                        ],
                        'right' => [
                            'style' => PHPExcel_Style_Border::BORDER_THICK
                        ],
                        'top' => [
                            'style' => PHPExcel_Style_Border::BORDER_THICK
                        ]
                    ]
                ]);
            }
            $column++;
        }
        // calculated total personnel cost and corporate cost
        $perTotal= 0;
        foreach ($personnelCost as $perKey => $value){
            $personnelCost[$perKey] = $value * 12;
            if($perKey != 'total') $perTotal += $personnelCost[$perKey];
            $totalLCA[$perKey] += $personnelCost[$perKey];
        }
        $corTotal= 0;
        foreach ($corporateCost as $corKey => $value){
            $corporateCost[$corKey] = $value * 12;
            if($corKey != 'total') $corTotal += $corporateCost[$corKey];
            $totalLCA[$corKey] += $corporateCost[$corKey];
        }

        //first row (labor cost per working hour)
        $startRow += 2;
        $column = $startCol;
        // $personTotal = [];
        // $finalAllTotal = [];
        foreach ($constHeader as $key => $header) {
            if($column > 2 && $column < 7){
                if($key == 'b_person_count'){
                    $sheet->setCellValueByColumnAndRow($column, $startRow, '割当経費');
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
        
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'left' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                }

                elseif($key == 'business_type'){
                    $sheet->setCellValueByColumnAndRow($column, $startRow, '関数済');
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
        
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'right' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                }
                //for only border
                else{
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                }
                
            }
            elseif($column > 6){
                
                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['number_align']);
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');

                if($key == 'total'){
                    // $formula = '=(' . implode('+', $personTotal) . ')';
                    // $sheet->setCellValueByColumnAndRow($column, $startRow, $formula);
                    $sheet->setCellValueByColumnAndRow($column, $startRow, $perTotal);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'left' => [
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ],
                            'right' => [
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ],
                        ]
                    ]);
                    $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                }else{
                    // $formula = '=(' . implode('+', $sumPerPerson[$key]) . ') * 12';
                    // debug($formula);exit;
                    // $sheet->setCellValueByColumnAndRow($column, $startRow, $formula);
                    $sheet->setCellValueByColumnAndRow($column, $startRow, $personnelCost[$key]);
                    $sheet->getStyle($columnLetter.$startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('c5d9f1');

                    // $personTotal[] = $columnLetter.$startRow;
                }
                // $finalAllTotal[$key][] = $columnLetter.$startRow;
            }
            
            $column++;
        }

        //second row (corporate cost per working hour)
        $startRow += 1;
        $column = $startCol;
        // $corporateTotal = [];
        foreach ($constHeader as $key => $header) {
            if($column > 2 && $column < 7){
                if($key == 'b_person_count'){
                    $sheet->setCellValueByColumnAndRow($column, $startRow, '割当経費');
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
        
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'left' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                }

                elseif($key == 'business_type'){
                    $sheet->setCellValueByColumnAndRow($column, $startRow, '関数済');
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
        
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'right' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);


                }
                //for only border
                else{
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                }
                
            }
            elseif($column > 6){
                
                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['number_align']);
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                if($key == 'total'){
                    // $formula = '=(' . implode('+', $corporateTotal) . ')';
                    // $sheet->setCellValueByColumnAndRow($column, $startRow, $formula);
                    $sheet->setCellValueByColumnAndRow($column, $startRow, $corTotal);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'left' => [
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ],
                            'right' => [
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ],
                        ]
                    ]);
                    $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                }
                else{
                    // $formula = '=(' . implode('+', $sumPerCorporate[$key]) . ') * 12';
                    // $sheet->setCellValueByColumnAndRow($column, $startRow, $formula);
                    $sheet->setCellValueByColumnAndRow($column, $startRow, $corporateCost[$key]);
                    $sheet->getStyle($columnLetter.$startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('c5d9f1'); 

                    // $corporateTotal[] = $columnLetter.$startRow;
                }    
                // $finalAllTotal[$key][] = $columnLetter.$startRow;
            }
            $column++;
        }

        //third row (adjust first row)
        $startRow += 1;
        $column = $startCol;
        // $firstAdjTotal = [];
        foreach ($constHeader as $key => $header) {
            if($column > 2 && $column < 7){
                if($key == 'b_person_count'){
                    $sheet->setCellValueByColumnAndRow($column, $startRow, '経営指導料');
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
        
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'left' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                }
                //for only border
               else{
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                }
                
            }
            elseif($column > 6){
                
                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['number_align']);
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                if($key != 'total'){
                    $sheet->setCellValueByColumnAndRow($column, $startRow, $laborCostAdjustments['経営指導料']['hours'][$key]);
                    if($first_year){
                        $sheet->getStyle($columnLetter.$startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                    } else {
                        $sheet->getStyle($columnLetter.$startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffcc');
                        $sheet->getStyle($columnLetter.$startRow)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                    }
                     
                    // $firstAdjTotal[] = $columnLetter.$startRow;
                }
                else{

                    // $formula = '=(' . implode('+', $firstAdjTotal) . ')';
                    // $sheet->setCellValueByColumnAndRow($column, $startRow, $formula);
                    $sheet->setCellValueByColumnAndRow($column, $startRow, $laborCostAdjustments['経営指導料']['hours']['total']);
                    
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'left' => [
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ],
                            'right' => [
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ],
                        ]
                    ]);
                    $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                }
                // $finalAllTotal[$key][] = $columnLetter.$startRow;
            }
            $column++;
        }

        //fourth row (adjust second row)
        $startRow += 1;
        $column = $startCol;
        // $secAdjTotal = [];
        foreach ($constHeader as $key => $header) {
            if($column > 2 && $column < 7){
                if($key == 'b_person_count'){
                    $sheet->setCellValueByColumnAndRow($column, $startRow, '異動による調整額');
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
        
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'left' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                }
                //for only border
               else{
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                }
                
            }
            elseif($column > 6){
                
                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['number_align']);
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                if($key != 'total'){
                    $sheet->setCellValueByColumnAndRow($column, $startRow, $laborCostAdjustments['異動による調整額']['hours'][$key]);
                    if($first_year){
                        $sheet->getStyle($columnLetter.$startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f9f9f9');
                    } else {
                        $sheet->getStyle($columnLetter.$startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffcc');
                        $sheet->getStyle($columnLetter.$startRow)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                    }                     
                    // $secAdjTotal[] = $columnLetter.$startRow;
                }else{
                    // $formula = '=(' . implode('+', $secAdjTotal) . ')';
                    // $sheet->setCellValueByColumnAndRow($column, $startRow, $formula);
                    $sheet->setCellValueByColumnAndRow($column, $startRow, $laborCostAdjustments['異動による調整額']['hours']['total']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'left' => [
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ],
                            'right' => [
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            ],
                        ]
                    ]);
                    $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                }
                // $finalAllTotal[$key][] = $columnLetter.$startRow;
            }
            $column++;
        }

        //final row (Total)
        $startRow += 1;
        $column = $startCol;
        foreach ($constHeader as $key => $header) {
            if($column > 2 && $column < 7){
                if($key == 'b_person_count'){
                    $sheet->setCellValueByColumnAndRow($column, $startRow, '人件費合計');
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
        
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'left' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                    $sheet->getStyle($columnLetter.$startRow)->getFont()->setBold(true);
                }

                elseif($key == 'business_type'){
                    $sheet->setCellValueByColumnAndRow($column, $startRow, '単位：千円');
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
        
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['text_align_left']);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'right' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                    $sheet->getStyle($columnLetter.$startRow)->getFont()->setBold(true);
                }
                //for only border
                else{
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                        'borders' => [
                            'top' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ],
                            'bottom' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ]);
                    
                }
                
            }
            elseif($column > 6){
                // $formula = '=(' . implode('+', $finalAllTotal[$key]) . ')';
                // $sheet->setCellValueByColumnAndRow($column, $startRow, $formula);
                $sheet->setCellValueByColumnAndRow($column, $startRow, $totalLCA[$key]);
                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column); 
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['number_align']);
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray($styles['border_thin']);
                $sheet->getStyle($columnLetter.$startRow)->getFont()->setBold(true);
                $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
            }

            if($key == 'total'){
                // debug($columnLetter.$startRow);
                $sheet->getStyle($columnLetter.$startRow)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'style' => PHPExcel_Style_Border::BORDER_THICK
                        ],
                        'right' => [
                            'style' => PHPExcel_Style_Border::BORDER_THICK
                        ],
                        'bottom' => [
                            'style' => PHPExcel_Style_Border::BORDER_THICK
                        ]
                    ]
                ]);
                $sheet->getStyle($columnLetter.$startRow)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
            }
            $column++;
        }
    }
    
    public function _formatExcelT1($target_year, $group_code, $users, $user){
        $buTermId = $_SESSION['BU_TERM_ID'];

        //get labour costs
        //term id
        $laborCosts = $this->LaborCostDetail->getLaborCost(explode('-',$target_year[0])[0], $group_code, $buTermId);

        foreach($laborCosts as $key=>$value){
            if($value['user_id'] != 0) {
                $position_data = $this->Position->getUserPosition($value['user_id'], explode('-',$target_year[0])[0]);
            }else {
                $position_data[0]['positions']['personnel_cost'] = $value['personnel_cost'];
                $position_data[0]['positions']['corporate_cost'] = $value['corporate_cost'];
            }
            
            $laborCosts[$key]['personnel_cost'] = $position_data[0]['positions']['personnel_cost'];
            $laborCosts[$key]['corporate_cost'] = $position_data[0]['positions']['corporate_cost'];
        }

        if(sizeof($laborCosts) == 0) {
            foreach($users as $key=>$value){
                $position_data = $this->Position->getUserPosition($value, explode('-',$target_year[0])[0]);        
                //$laborCosts[$key]['LaborCost']['target_year'] = explode('-',$target_year[0])[0];
                if(sizeof($position_data) > 0){
                    $laborCosts[$key]['position_code'] = $position_data[0]['positions']['position_code'];
                    $laborCosts[$key]['position_name'] = $position_data[0]['positions']['position_name'];
                    $laborCosts[$key]['user_id'] = $value;
                    $laborCosts[$key]['user_name'] = $position_data[0]['User']['user_name'];
                    $laborCosts[$key]['person_count'] = 0;
                    $laborCosts[$key]['b_person_count'] = 0;
                    $laborCosts[$key]['common_expense'] = 0;
                    $laborCosts[$key]['b_person_total'] = 0;
                    $laborCosts[$key]['adjust_labor_cost'] = 0;
                    $laborCosts[$key]['adjust_corpo_cost'] = 0;
                    $laborCosts[$key]['unit_labor_cost'] = $position_data[0]['positions']['personnel_cost'];
                    $laborCosts[$key]['unit_corpo_cost'] = $position_data[0]['positions']['corporate_cost'];
                    $laborCosts[$key]['personnel_cost'] = $position_data[0]['positions']['personnel_cost'];
                    $laborCosts[$key]['corporate_cost'] = $position_data[0]['positions']['corporate_cost'];
                    $laborCosts[$key]['none_labor_cost'] = false;
                }
            }
        }

        list($businesses, $business_codes) = $this->LaborCostDetail->businessCodes($target_year, $group_code);
        //if users and business is not create in this target year
        if(empty($laborCosts) || empty($businesses)){
            return [ NULL, NULL, NULL];
        }

        //unchange header
        $constHeader = [
            'number' => '#',
            'user_name' => '氏   名',
            'b_person_count' => '予算人員数',
            'unit_labor_cost' => '人件費単価',
            'unit_corpo_cost' => 'ｺｰﾎﾟﾚｰﾄ経費割当単価',
            'business_type' => 'ﾋﾞｼﾞﾈｽ別'
        ];
        //change header
        foreach ($businesses as $business) {
            $constHeader[$business['Layer']['layer_code']] = $business['Layer']['name_jp'];
        }
        //latest total
        $constHeader['total'] = '合計';
        $constHeader['comment'] = 'コメント';

        //Initial Format
        $initialState = [];
        foreach ($business_codes as $business_code) {
            $initialState[$business_code] = 0; 
        }
        $initialState['total'] = 0;
        $formattedLaborCost = [];
        foreach ($laborCosts as $laborCost) { 
            // $format = call_user_func_array('array_merge', $laborCost);
            
            $formattedLaborCost[] = array_merge($laborCost,['business_type' => 1, 'comment' => ''] ,$initialState);//array merge
            $formattedLaborCost[] = array_merge($laborCost,['business_type' => 2, 'comment' => ''] , $initialState);
        }
        $formattedLaborCost[] = ['adjust_name' => '異動による調整額', 'business_type' => 1] + $initialState;
        $formattedLaborCost[] = ['adjust_name' => '異動による調整額', 'business_type' => 2] + $initialState;
        //End Initial Format

        
        //get labor cost details
        $laborCostDetails = $this->LaborCostDetail->find('all', [
            'conditions' => [
                'target_year' => explode('-',$target_year[0])[0],
                'layer_code' => $business_codes,
                'bu_term_id' => $buTermId,
                'flag' => '1'
            ] 
        ]);

        //Total Row
        $totalLaborCostDetails = $this->LaborCostDetail->find('all', [
            'fields' => [
                'SUM(person_count) as total_person_count',
                'layer_code'
             ],
            'group' => 'layer_code',
            'conditions' => [
                'LaborCostDetail.bu_term_id' => $buTermId,
                'LaborCostDetail.target_year' => explode('-',$target_year[0])[0],
                'LaborCostDetail.layer_code' => $business_codes,
                'LaborCostDetail.flag' => '1'
            ] 
        ]);

        $formattedTotalLCD = [];
        if($totalLaborCostDetails){//having data state
            
            $allTotal = 0;
            foreach($totalLaborCostDetails as $tLCD){
                $allTotal += $tLCD[0]['total_person_count'];
                $formattedTotalLCD[$tLCD['LaborCostDetail']['layer_code']] = $tLCD[0]['total_person_count'];
            }
            $formattedTotalLCD['total'] = $allTotal;
        }else{//initial state
            foreach($business_codes as $code){
                $formattedTotalLCD[$code] = 0;
            }
            $formattedTotalLCD['total'] = 0;
        }
        //end total row

        //if lcd is empty, return initial format
        if(!$laborCostDetails){
            return [ $constHeader, $formattedLaborCost, $formattedTotalLCD];
        }
        
        // having data state format
        foreach ($formattedLaborCost as $key => $LC) {
            
            foreach ($laborCostDetails as $value) {
                $business_type = $value['LaborCostDetail']['business_type'];
                $business_code = $value['LaborCostDetail']['layer_code'];
                $person_count = $value['LaborCostDetail']['person_count'];
                $user_id = $value['LaborCostDetail']['user_id'];
                $new_user_name = $value['LaborCostDetail']['new_user_name'];
                $position_code = $value['LaborCostDetail']['position_code'];
                
                if($user_id === $LC['user_id'] && $business_type == $LC['business_type']){
                    
                    //not ajustment row
                    if($user_id && ($new_user_name === '' || $new_user_name === NULL)){
                        
                        $comment = $value['LaborCostDetail']['comment'];
                        $formattedLaborCost[$key]['comment'] = $comment;

                        //calculation for unit labor cost
                        //$labor_unit = $LC['personnel_cost'] * $LC['person_count'];
                        $labor_unit = $LC['personnel_cost'];
                        $yearly_labor_cost = ($labor_unit * 12) + $LC['adjust_labor_cost'];
                        //$unit_labor_cost = ($yearly_labor_cost / $LC['person_count']) / 12;
                        //$unit_labor_cost =  is_infinite($unit_labor_cost) || is_nan($unit_labor_cost) ? 0 : $unit_labor_cost;
                        $unit_labor_cost = $LC['personnel_cost'];
                        $formattedLaborCost[$key]['unit_labor_cost'] = $unit_labor_cost;

                        //calculation for unit corpo cost
                        $b_person_total = $LC['b_person_count'] + $LC['common_expense'];
                        //$corpo_unit = $LC['corporate_cost'] * $b_person_total;
                        $corpo_unit = $LC['corporate_cost'];
                        // echo $laborCost['b_person_total'];
                        $yearly_corpo_cost = ($corpo_unit * 12) + $LC['adjust_corpo_cost'];
                        //$unit_corpo_cost = ($yearly_corpo_cost / $LC['person_count']) / 12;
                        //$unit_corpo_cost =  is_infinite($unit_corpo_cost) || is_nan($unit_corpo_cost) ? 0 : $unit_corpo_cost;
                        $unit_corpo_cost = $LC['corporate_cost'];
                        $formattedLaborCost[$key]['unit_corpo_cost'] = $unit_corpo_cost;
                        $formattedLaborCost[$key][$business_code] = $person_count;
                        $formattedLaborCost[$key]['total'] += $person_count;
                    }elseif(!$user_id && $new_user_name === $LC['user_name']) {
                        $comment = $value['LaborCostDetail']['comment'];
                        $formattedLaborCost[$key]['comment'] = $comment;

                        $labor_unit = $LC['personnel_cost'];
                        $yearly_labor_cost = ($labor_unit * 12) + $LC['adjust_labor_cost'];
                        
                        $unit_labor_cost = $LC['personnel_cost'];
                        $formattedLaborCost[$key]['unit_labor_cost'] = $unit_labor_cost;

                        $b_person_total = $LC['b_person_count'] + $LC['common_expense'];
                        
                        $corpo_unit = $LC['corporate_cost'];
                        
                        $yearly_corpo_cost = ($corpo_unit * 12) + $LC['adjust_corpo_cost'];
                        
                        $unit_corpo_cost = $LC['corporate_cost'];
                        $formattedLaborCost[$key]['unit_corpo_cost'] = $unit_corpo_cost;
                        
                        $formattedLaborCost[$key][$business_code] = $person_count;
                        
                        $formattedLaborCost[$key]['total'] += $person_count;
                    }               
                }

            }   
        }
        return [ $constHeader, $formattedLaborCost, $formattedTotalLCD];
    }

    public function _excelStyles(){
        $styles = [
            'double_border_right' => [
                'borders' => [
                    'right' => [
                        'style' => PHPExcel_Style_Border::BORDER_DOUBLE
                    ]
                ]
            ],
            'border_thin' => [
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    ]
                ]
            ],
            'border_thick' => [
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    ]
                ]
            ],
            'border_bold' => [
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    ]
                ]
            ],
            'top_border_dot' => [
                'borders' => array(
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                    'left' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                    'right' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                    'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_DOTTED
                    )
                ),
            ],

            'bottom_border_dot' => [
                'borders' => array(
                    'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                    'left' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                    'right' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_DOTTED
                    )
                ),
            ],
            'bottom_dot_custom' => [
                'borders' => array(
                    'left' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_DOTTED
                    )
                ),
            ],
            'number_align' => [
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ]
            ],
            'text_align_left' => [
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ]
            ],
            'text_align_center' => [
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ]
            ],
            'negative' => [
                'font'  => [
                    'color' => ['rgb' => 'FF0000']
                ]
            ],

            'textColor' =>[
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['rgb' => 'D5F4FF']
                ]
            ],

            'disableColor' =>[
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F9F9F9']
                ]
            ],

            'yellowColor' =>[
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['rgb' => 'FFFF99']
                ]
            ],
        ];

        return $styles;
    }


    public function checkSaveMerge(){
        parent::checkAjaxRequest($this);
        //term id
        $buTermId = $_SESSION['BU_TERM_ID'];
        if ($this->request->is('post')) {
            $target_year = $this->request->data['target_year'];
            $layer_code = $this->request->data['layer_code'];
            $row_count = ($this->LaborCostDetail->find('count', array(
                'conditions' => array(
                    'bu_term_id' => $buTermId,
                    'target_year' => $target_year,
                    'flag' => '1',
                    'layer_code like "%'.$layer_code.'%"',
                )
            ))>0)? true : false;
            
            list($tableOne, $tableTwo) = $this->getTableDatas($target_year, $layer_code);
            $datas = ['row_count' => $row_count, 'updated_tableOne' => $tableOne, 'updated_tableTwo' => $tableTwo];                
            echo json_encode($datas);
        }

    }
    public function getTableDatas($target_year, $layer_code){
        //term id
        $buTermId = $_SESSION['BU_TERM_ID'];
        $tableOne = $this->LaborCostDetail->find('all', array(
            'conditions' => array(
                'bu_term_id' => $buTermId,
                'target_year' => $target_year,
                'flag' => '1',
                'layer_code like "%'.$layer_code.'%"',
            )
        ));
        $tableTwo = $this->LaborCostAdjustment->find('all', [
            'conditions' => [
                'bu_term_id' => $buTermId,
                'target_year' => $target_year,
                'flag' => '1',
                'layer_code like "%'.$layer_code.'%"',
            ]
        ]);
        return [$tableOne, $tableTwo];

    }
    public function getLayerList($target_year) {
        $groups = [];
        // $group_datas = $this->Layer->getLayerCodeList($target_year);
        // $group_datas = $this->Layer->getLayerCodeListByPermissions(implode("','", $this->Session->read('LCD.INDEX')));
        #get required params
        $login_id   = $this->Session->read('LOGIN_ID');
        $role_id    = $this->Session->read('ADMIN_LEVEL_ID');
        $start_month = $target_year[0];
        $end_month = $target_year[1];
        $page_name   = $this->request->params['controller'];
        $Common = new CommonController();
        $permissions = $Common->getPermissionsByRoleForBU($login_id, $role_id, $start_month, $end_month, $page_name);
        $group_datas = $this->Layer->getLayerCodeListByPermissions(implode("','", $permissions['index']['layer']));
       

        foreach ($group_datas as $group_values) {
            $lcode = $group_values['Layer']['layer_code'];
            $name = ($_SESSION['Config']['language'] == 'eng') ? $group_values['Layer']['name_en'] : $group_values['Layer']['name_jp'];
            // $groups[$lcode] = $lcode."/".$name;
            $groups[$lcode] = $name;
        }
        return $groups;
    }
    public function PositionData($target_year) {
        $this->Position->virtualFields['position_gp'] = 'CONCAT(Position.position_name, "/", Position.personnel_cost, "/", Position.corporate_cost)';
        $position_datas = $this->Position->find('list', array(
            'conditions' => array(
                'Position.flag' => 1,
                'Position.target_year' => $target_year,
            ),
            'fields' => array('position_code', 'position_gp')
        ));
        return $position_datas;
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
            $target_year = $this->Session->read('SEARCH_LABOR_COST.target_year');
            $group_code = $this->Session->read('SEARCH_LABOR_COST.layer_code');
            #get complete flag
            $completed_flag =  $this->BuApprovedLog->find('first', array(
                'fields' => array('flag'),
                'conditions' => array(
                    'menu_id' => Setting::MENU_ID_LIST['BudgetResult'],
                    'bu_term_id' => $buTermId,
                    'group_code' => $group_code,
                    // 'line_code' => '0',
                    // 'business_code' => '0',
                    // 'sub_business_code' => '0',
                )
            ))['BuApprovedLog']['flag'];
            $completed_flag_2 =  $this->BuApprovedLog->find('first', array(
                'fields' => array('flag'),
                'conditions' => array(
                    'menu_id' => Setting::MENU_ID_LIST['LaborCostDetails'],
                    'bu_term_id' => $buTermId,
                    'department_code' => '0',
                    'bu_code' => '0',
                    'target_year' => $target_year,
                    'group_code' => $group_code,
                    'line_code' => '0',
                    'business_code' => '0',
                    'sub_business_code' => '0',
                )
            ))['BuApprovedLog']['flag'];
            if($completed_flag == 2 || $completed_flag_2 == 1){
                $errorMsg = parent::getErrorMsg('SE157');
                $this->Flash->error($errorMsg, array('key'=>'lcd_error'));
                $this->redirect(array("action" => "index"));
            }  
            #update flag 2 to 1
            $changed_flag = $this->BuApprovedLog->updateAll(
                    array('flag' => $this->request->data('approved_flag_1')),
                    array('menu_id' => $menu_id, 'bu_term_id' => $buTermId, 'target_year' => $target_year, 'group_code' => $group_code, 'line_code' => '0', 'business_code' => '0', 'sub_business_code' => '0')
            );
            if($changed_flag){
                $successMsg = parent::getSuccessMsg('SS036');
                $this->Flash->success($successMsg, array(
                    'key' => 'lcd_success'
                ));
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
            if($this->Session->check('LCD.COMMENT')){
                $commentLayers = $this->Session->read('LCD.COMMENT');
                $disabledCommentBtn = (!in_array($search_data['layer_code'], $commentLayers))? true : false;
            }
            if($this->Session->check('LCD.SAVE')){
                $saveLayers = $this->Session->read('LCD.SAVE');
                $disabledSaveBtn = (!in_array($search_data['layer_code'], $saveLayers))? true : false;
            }
            if($this->Session->check('LCD.CONFIRM')){
                $confirmLayers = $this->Session->read('LCD.CONFIRM');
                $disabledConfirmBtn = (!in_array($search_data['layer_code'], $confirmLayers))? true : false;
            }
            if($this->Session->check('LCD.CONFIRM_CANCEL')){
                $confirmCancelLayers = $this->Session->read('LCD.CONFIRM_CANCEL');
                $disabledConfirmCancelBtn = (!in_array($search_data['layer_code'], $confirmCancelLayers))? true : false;
            }
        }
        return [$disabledCommentBtn, $disabledSaveBtn, $disabledConfirmBtn, $disabledConfirmCancelBtn];
    }
}