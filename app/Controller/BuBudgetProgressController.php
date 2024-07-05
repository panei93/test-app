<?php

use Beta\Microsoft\Graph\Model\ConditionalAccessRoot;
use Zend\Validator\InArray;

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * Roles Controller
 *
 * @property BuBudgetProgress $BudgetProgress
 * @property PaginatorComponent $Paginator
 */
class BuBudgetProgressController extends AppController
{
    /**
     * Components
     *
     * @var array
     */
    public $components = array('Session','PhpExcel.PhpExcel');
    public $uses = array('Layer', 'LayerType','User','BuBudgetProgress');
    public $helpers = array('Html', 'Form', 'Csv');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        parent::checkBuUrlSession($this->name);#checkurlsession
        parent::checkExpiredUser();
    }


    /**
     * method for budget progress form load
     * 
     * @date    09-18-2023
     * @author  Zeyar Min
     * @param   void
     * @return  void 
     */
    public function index()
    {
        $this->Session->write('LAYOUT', 'buanalysis');
		$this->layout = 'buanalysis';
        $Common = New CommonController();
        $login_id = $this->Session->read('LOGIN_ID');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $pagename = $this->request->params['controller'];
		$language   = $this->Session->read('Config.language');
        $targetYear = $_SESSION['BudgetTargetYear'];
        $buTypeOrder = Setting::BU_BUDGET_MAX_LAYER[0];
        $term_id = $_SESSION['BU_TERM_ID'];
        $menu_id = Setting::MENU_ID_LIST['BudgetResult'];
        $start_month = $_SESSION['yearListOnTerm'][$targetYear][0];
        $end_month = $_SESSION['yearListOnTerm'][$targetYear][1];

        // if($pagename == 'BuBudgetProgress') $pagename = 'Progress Management(Budget)';
        $permissions = $Common->getPermissionsByRoleForBU($login_id, $role_id, $start_month, $end_month, $pagename, $buTypeOrder);

        $relatedLayer = array_values($permissions['index']['next_layers']);
        if(isset($permissions['index']['next_layers'])) $relatedType = $permissions['index']['limit'];

        $layerRead = isset($permissions['index']['next_layers']) ? $relatedLayer[0] : $permissions['index']['layer'];
        $layerComplete = isset($permissions['complete']) ? array_values($permissions['complete']['layer']) : '';
        $readPermission[] = $permissions['index']['all_layer'];
        $completePermission[] = $permissions['complete']['all_layer'];
        $check_all_layer = [];
        #check complete permission for button show-hide
        if(!empty($completePermission)){
            foreach($completePermission as $cpKey => $cpValue){
                foreach($cpValue as $cpKey1 => $cpValue1){
                    if($cpKey1 != $buTypeOrder){
                        foreach($cpValue1 as $cpKey2 => $cpValue2){
                            $preCompletePermission[] = $cpValue2;
                            $check_all_layer[$cpKey1][$cpKey2] = $cpValue2;
                        }
                    } else {
                        $preCompletePermission[] = $cpValue1;
                    }
                }
            }    
        }

        $layerTypeOrder = $this->LayerType->find('list', array(
            'fields' => array('LayerType.type_order'),
            'conditions' => array('LayerType.flag' => '1', 'LayerType.type_order >='=> $buTypeOrder),
            'order' => 'LayerType.type_order ASC'
        ));

        $field = ['Layer.layer_code', 'Layer.parent_id', 'Layer.name_jp', 'Layer.name_en', 'Layer.item_1', 'Layer.item_2', 'Layer.form', 'Layer.type_order','Layer.layer_order'];

        $condition_arr = [];
        
        foreach($layerTypeOrder as $value){
            if($value == $buTypeOrder){
                $condition_arr = array(
                    'Layer.layer_code' => $permissions['index']['layer'],
                    'Layer.flag' => '1',
                    'Layer.bu_status' => '1',
                    'Layer.type_order'=>$value,
                    'DATE_FORMAT(Layer.from_date,"%Y-%m") <=' => $end_month,
                    'DATE_FORMAT(Layer.to_date,"%Y-%m") >=' => $start_month
                );
                $maxLayerValue = $this->Layer->find('all', array(
                    'fields' => $field,
                    'conditions' => $condition_arr,
                    'order' => array('Layer.type_order', 'Layer.layer_order', 'Layer.layer_code')
                ));
                if(!empty($maxLayerValue)) $allLayer[] = $maxLayerValue;
            } else if($value == $relatedType){
                $condition_arr = array(
                    'Layer.layer_code' => $layerRead,
                    'Layer.flag' => '1',
                    'Layer.bu_status' => '1',
                    'Layer.type_order'=>$value,
                    'DATE_FORMAT(Layer.from_date,"%Y-%m") <=' => $end_month,
                    'DATE_FORMAT(Layer.to_date,"%Y-%m") >=' => $start_month
                );
                $layerValue = $this->Layer->find('all', array(
                    'fields' => $field,
                    'conditions' => $condition_arr,
                    'order' => array('Layer.type_order', 'Layer.layer_order', 'Layer.layer_code')
                ));
                if(!empty($layerValue)) $allLayer[] = $layerValue;
            } else {
                foreach($layerRead as $lrKey => $lrValue){
                    $condition_arr = array(
                        'Layer.parent_id LIKE' => "%{$lrValue}%",
                        'Layer.flag' => '1',
                        'Layer.bu_status' => '1',
                        'Layer.type_order'=>$value,
                        'DATE_FORMAT(Layer.from_date,"%Y-%m") <=' => $end_month,
                        'DATE_FORMAT(Layer.to_date,"%Y-%m") >=' => $start_month
                    );
                    $layerValue = $this->Layer->find('all', array(
                        'fields' => $field,
                        'conditions' => $condition_arr,
                        'order' => array('Layer.type_order', 'Layer.layer_order', 'Layer.layer_code')
                    ));
                    if(!empty($layerValue)) $allLayer[] = $layerValue;
                }
            }
            unset($condition_arr);
        }
        
        $nextLayerCode = array();
        $getParent = array();
        $i = 0;
        # arrange $allLayer array to get respective parent and child order
        foreach($allLayer as $akey=>$avalue){
            if($akey == 0) {
                foreach($avalue as $key=>$value){
                    $lCode[$key] = $value['Layer']['layer_code'];
                    $getParent[$i] = $value['Layer']['parent_id'];
                    $prev_layerTypeOrder = $value['Layer']['type_order'];
                    $prev_parent = json_decode($value['Layer']['parent_id'], true);
                    $i++;
                }
                $prepare_layer = $avalue;
            }
            
            if($akey != 0){
                foreach($avalue as $value){
                    $insert = array();
                    $nextLayerCode[] = $value['Layer']['layer_code'];
                    $parent_id = json_decode($value['Layer']['parent_id'], true);
                    $getParent[$i] = $value['Layer']['parent_id'];
                    if(in_array($parent_id['L'.$buTypeOrder], $lCode)){
                        $arrKey = array_keys($lCode,$parent_id['L'.$buTypeOrder]);
                        $lNo = $value['Layer']['type_order'];
                        if(in_array($parent_id['L'.($lNo-1)], $nextLayerCode)) {
                            $insert['Layer'.$lNo.'_'.$value['Layer']['layer_code']] = $value['Layer'];
                            if ($prev_layerTypeOrder == $value['Layer']['type_order'] && $prev_parent['L'.($lNo-1)] == $parent_id['L'.($lNo-1)]) {
                                $newInsert = $this->array_splice_after_key($prepare_layer[$arrKey[0]], 'Layer'.($lNo).'_'.$nextLayerCode[sizeof($nextLayerCode)-2], $insert);
                            }else{
                                $newInsert = $this->array_splice_after_key($prepare_layer[$arrKey[0]], 'Layer'.($lNo-1).'_'.$parent_id['L'.($lNo-1)], $insert);
                            }
                            $prepare_layer[$arrKey[0]] = $newInsert;
                        }
                        else $prepare_layer[$arrKey[0]]['Layer'.$lNo."_".$value['Layer']['layer_code']] = $value['Layer'];
                    }
                    $prev_layerTypeOrder = $value['Layer']['type_order'];
                    $prev_parent = json_decode($value['Layer']['parent_id'], true);
                    $i++;
                }
            }
        }
        //check it is child or not
        foreach($prepare_layer as $pkey=>$pvalue){
            foreach($pvalue as $key=>$value){
                $p_id = '"L'.$value['type_order'].'":"'.$value['layer_code'].'"';
                if(!$this->array_search_partial($getParent, $p_id)) $prepare_layer[$pkey][$key]['isChild'] = true;
            }
            
        }
        # check it is save or complete or cancel state
        foreach($prepare_layer as $pre_key => $pre_value) {
            foreach($pre_value as $key1 => $value1){
                $parent = json_decode($value1['parent_id'], true);
                $parent_count = count($parent) + 1;
                $parent["L$parent_count"] = $value1['layer_code'];
                $currentParent = $parent[max(array_keys($parent))];
                
                $bu_code = !empty($parent['L2']) ? $parent['L2'] : 0;
                $group_code = !empty($parent['L3']) ? $parent['L3'] : 0;
                $line_code = !empty($parent['L4']) ? $parent['L4'] : 0;
                $business_code = !empty($parent['L5']) ? $parent['L5'] : 0;
                $sub_business_code = !empty($parent['L6']) ? $parent['L6'] : 0;

                $logConditions = [
                    'BuBudgetProgress.menu_id' => $menu_id,
                    'BuBudgetProgress.bu_term_id' => $term_id,
                    'BuBudgetProgress.target_year' => $targetYear,
                    'BuBudgetProgress.bu_code' => $bu_code,
                    'BuBudgetProgress.group_code' => $group_code,
                    'BuBudgetProgress.line_code' => $line_code,
                    'BuBudgetProgress.business_code' => $business_code,
                    'BuBudgetProgress.sub_business_code' => $sub_business_code,
                    'OR' => [
                        'BuBudgetProgress.flag' => 2,
                        'BuBudgetProgress.cancel_flag' => 1
                    ]
                ];
                $approveData = $this->BuBudgetProgress->find('all', array(
                    'fields' => array('User.user_name', 'BuBudgetProgress.*'),
                    'conditions' => $logConditions,
                    'joins' => array(
                        array(
                            'table' => 'users',
                            'alias' => 'User',
                            'type' => 'LEFT',
                            'conditions' => array(
                                'User.id = BuBudgetProgress.updated_by'
                            )
                        ),
                    )
                ));
                if(!empty($approveData)) {
                    isset($prepare_layer[$pre_key][$key1]['isChild']) ? $prepare_layer[$pre_key][$key1]['child_complete'] = $approveData[0]['BuBudgetProgress']['flag'] : ($approveData[0]['BuBudgetProgress']['cancel_flag'] == 1 ? $prepare_layer[$pre_key][$key1]['cancel_complete'] = 1 : $prepare_layer[$pre_key][$key1]['all_child_complete'] = $approveData[0]['BuBudgetProgress']['flag']);
                    $prepare_layer[$pre_key][$key1]['cancel_flag'] = $approveData[0]['BuBudgetProgress']['cancel_flag'];
                    $prepare_layer[$pre_key][$key1]['user_name'] = $approveData[0]['User']['user_name'];
                    $prepare_layer[$pre_key][$key1]['updated_date'] = $approveData[0]['BuBudgetProgress']['updated_date'];
                }
            }
        }
        
        $completedChild = [];
        $allChildLayer = [];
        # to check all child layer of each parent complete or not
        foreach($prepare_layer as $preKey => $preValue) {
            foreach($preValue as $preKey1 => $preValue1){
                $currentParent = json_decode($preValue1['parent_id'], true);
                $maxParentLayer = max(array_keys($currentParent));
                if($maxParentLayer != 'L1' && ($preValue1['child_complete'] == 2 || $preValue1['all_child_complete'] == 2)){
                    # get completed child layer of each parent
                    $completedChild[$currentParent[$maxParentLayer]][] = $preValue1['layer_code'];
                }
                if($maxParentLayer != 'L1') {
                    #get all child layer of each parent
                    $allChildLayer[$currentParent[$maxParentLayer]][] = 'Layer'.$preValue1['type_order']."_".$preValue1['layer_code'];
                }
            }
            # check all child complete or not
            foreach($completedChild as $ccKey => $ccValue){
                foreach($allChildLayer as $aclKey => $aclValue){
                    if($ccKey == $aclKey && count($ccValue) == count($aclValue)){
                        $explodeValue = explode("_", $aclValue[0]);
                        $typeOrderNum = substr($explodeValue[0], -1) - 1;
                        if($typeOrderNum == $buTypeOrder){
                            if(!isset($prepare_layer[$preKey]['Layer']['isChild']) && !isset($prepare_layer[$preKey]['Layer']['all_child_complete'])) $prepare_layer[$preKey]['Layer']['all_child_complete'] = true;
                        } else {
                            if(!isset($prepare_layer[$preKey]['Layer'.$typeOrderNum."_".$aclKey]['isChild']) && !isset($prepare_layer[$preKey]['Layer'.$typeOrderNum."_".$aclKey]['all_child_complete'])) $prepare_layer[$preKey]['Layer'.$typeOrderNum."_".$aclKey]['all_child_complete'] = true;
                        }
                    }
                }
            }
            unset($completedChild);
            unset($allChildLayer);
        }
        # show hide complete button if it have complete permission
        if(!empty($preCompletePermission)){
            foreach($prepare_layer as $pre_key => $pre_value) {
                foreach($pre_value as $key1 => $value1){
                    foreach($preCompletePermission as $pcpKey => $pcpValue){
                        foreach($pcpValue as $pcpKey1 => $pcpValue1){ 
                            if($pcpValue1 == $value1['layer_code']){
                                $create_key = $value1['type_order']+1;
                                if($value1['type_order'] == $buTypeOrder){
                                    // $prepare_layer[$pre_key]['Layer']['complete_permission'] = true;
                                    #check complete or not(khin)
                                    $complete_layer_count = $this->countCompleteLayer($menu_id, $term_id, $pcpValue1, $check_all_layer[$pcpValue1][$create_key]);
                                    if($complete_layer_count) {
                                        $prepare_layer[$pre_key]['Layer']['complete_permission'] = true;
                                    }
                                    #check complete or not(khin)
                                } else {
                                    $prepare_layer[$pre_key]['Layer'.$value1['type_order']."_".$pcpValue1]['complete_permission'] = true;
                                }
                            }
                        }
                    }
                }
            } 
        }

        $this->Session->write('SELECTION', 'SET');
        $this->set('errormessage', parent::getErrorMsg('SE001'));
        $this->set(compact('year', 'layerTypeOrder', 'prepare_layer', 'rowColSpan', 'approveData', 'readCompleteLimit', 'relatedType'));
        $this->render('index');
    }

    /**
     * method for summary progress form load
     * 
     * @date    09-20-2023
     * @author  Zeyar Min
     * @param   void
     * @return  void 
     */
    public function summary() {
        $this->Role->recursive = 0;
        $this->Session->write('LAYOUT', 'buanalysis');
		$this->layout = 'buanalysis';
		parent::CheckSession();
		parent::checkUserStatus();
		parent::checkExpiredUser();
		$language   = $this->Session->read('Config.language');
        $targetYear = $_SESSION['BudgetTargetYear'];
        $term_id = $_SESSION['BU_TERM_ID'];
        $menu_id = Setting::MENU_ID_LIST['BusinessAnalysis'];
        $budget_menu = Setting::MENU_ID_LIST['BudgetResult'];
        $buTypeOrder = Setting::BU_BUDGET_MAX_LAYER[0];
        $Common = New CommonController();
        $start_month = $_SESSION['yearListOnTerm'][$targetYear][0];
        $end_month = $_SESSION['yearListOnTerm'][$targetYear][1];
        $login_id = $this->Session->read('LOGIN_ID');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $pagename = $this->request->params['controller'].'Summary';
        $layerPer = $Common->getPermissionsByRoleForBU($login_id, $role_id, $start_month, $end_month, $pagename, $buTypeOrder);

        if(isset($layerPer['complete']['next_layers'])){
            $perLayerArr = array_values($layerPer['complete']['layer']);
            $permittedLayer = $perLayerArr[0];
            $checkAllChild = $this->Layer->find('list', array(
                'fields' => array('Layer.layer_code'),
                'conditions' => array('Layer.flag' => '1', 'Layer.Layer_code LIKE'=> "%{$permittedLayer}%", 'Layer.type_order' => $buTypeOrder+1),
                'order' => 'Layer.Layer_code'
            ));
        }
        
        $readLayer = array();
        //$readLayer
        foreach($layerPer['index']['layer'] as $code){
            $readLayer[$layerPer['index']['layer_no']][] = $code;
            foreach($layerPer['index']['all_layer'][$code][3] as $key=>$value){
                $readLayer[($layerPer['index']['layer_no'])+1][] = $value;
            }
            
        }
        
        if($layerPer['index']['next_layers']) {
            $readLayer = array();
            $readLayer[$layerPer['index']['layer_no']][] = $code;
            $readLayer[$buTypeOrder + 1] = $layerPer['index']['next_layers'][$buTypeOrder + 1];
        }
        
        $layerTypeOrder = $this->LayerType->find('list', array(
            'fields' => array('LayerType.type_order'),
            'conditions' => array('LayerType.flag' => '1', 'LayerType.type_order in'=> array(2,3)),
            'order' => 'LayerType.type_order ASC'
        ));

        $field = ['Layer.layer_code', 'Layer.parent_id', 'Layer.name_jp', 'Layer.name_en', 'Layer.item_1', 'Layer.item_2', 'Layer.form', 'Layer.type_order'];

        $condition_arr = [];
        
        foreach($layerTypeOrder as $value){
            $condition_arr = array(
                'Layer.flag' => '1',
                'Layer.bu_status' => '1',
                'Layer.type_order'=>$value,
                //'Layer.layer_code'=>$readLayer[$value],
                'DATE_FORMAT(Layer.from_date,"%Y-%m") <=' => $start_month,
                'DATE_FORMAT(Layer.to_date,"%Y-%m") >=' => $end_month
            );
            $allLayer[] = $this->Layer->find('all', array(
                'fields' => $field,
                'conditions' => $condition_arr,
                'order' => array('Layer.type_order', 'Layer.layer_order', 'Layer.layer_code')
            ));
            
        }
        $nextLayerCode = array();
        $getParent = array();
        $i = 0;
        foreach($allLayer as $akey=>$avalue){
            if($akey == 0) {
                foreach($avalue as $key=>$value){
                    $lCode[$key] = $value['Layer']['layer_code'];
                    $getParent[$i] = $value['Layer']['parent_id'];
                    $i++;
                }
                $prepare_layer = $avalue;
            }
            if($akey != 0){
                foreach($avalue as $value){
                    $insert = array();
                    $nextLayerCode[] = $value['Layer']['layer_code'];
                    $parent_id = json_decode($value['Layer']['parent_id'], true);
                    $getParent[$i] = $value['Layer']['parent_id'];
                    if(in_array($parent_id['L'.$buTypeOrder], $lCode)){
                        $arrKey = array_keys($lCode,$parent_id['L'.$buTypeOrder]);
                        $lNo = $value['Layer']['type_order'];
                        if(in_array($parent_id['L'.($lNo-1)], $nextLayerCode)) {
                            $insert['Layer'.$lNo.'_'.$value['Layer']['layer_code']] = $value['Layer'];
                            $newInsert = $this->array_splice_after_key($prepare_layer[$arrKey[0]], 'Layer'.($lNo-1).'_'.$parent_id['L'.($lNo-1)], $insert);
                            $prepare_layer[$arrKey[0]] = $newInsert;
                        }
                        else $prepare_layer[$arrKey[0]]['Layer'.$lNo."_".$value['Layer']['layer_code']] = $value['Layer'];
                    }
                    $i++;
                }
            }
        }
        // child or not
        foreach($prepare_layer as $pkey=>$pvalue){
            foreach($pvalue as $key=>$value){
                $p_id = '"L'.$value['type_order'].'":"'.$value['layer_code'].'"';
                //if(!$this->array_search_partial($getParent, $p_id)) $prepare_layer[$pkey][$key]['isChild'] = true;
            }
            
        }
        
        foreach($prepare_layer as $pre_key => $pre_value) {
            foreach($pre_value as $key1 => $value1){
                $parent = json_decode($value1['parent_id'], true);
                $parent_count = count($parent) + 1;
                $parent["L$parent_count"] = $value1['layer_code'];
                $currentParent = $parent[max(array_keys($parent))];
                
                $bu_code = !empty($parent['L2']) ? $parent['L2'] : 0;
                $group_code = !empty($parent['L3']) ? $parent['L3'] : 0;

                $logConditions = [
                    'BuBudgetProgress.menu_id' => $menu_id,
                    'BuBudgetProgress.bu_term_id' => $term_id,
                    'BuBudgetProgress.target_year' => $targetYear,
                    'BuBudgetProgress.bu_code' => $bu_code,
                    'BuBudgetProgress.group_code' => $group_code,
                    'OR' => [
                        'BuBudgetProgress.flag' => 2,
                        'BuBudgetProgress.cancel_flag' => 1
                    ]
                ];
                $approveData = $this->BuBudgetProgress->find('all', array(
                    'fields' => array('User.user_name', 'BuBudgetProgress.*'),
                    'conditions' => $logConditions,
                    'joins' => array(
                        array(
                            'table' => 'users',
                            'alias' => 'User',
                            'type' => 'LEFT',
                            'conditions' => array(
                                'User.id = BuBudgetProgress.updated_by'
                            )
                        ),
                    )
                ));
                if(!empty($approveData)) {
                    $prepare_layer[$pre_key][$key1]['flag'] = $approveData[0]['BuBudgetProgress']['flag'];
                    $prepare_layer[$pre_key][$key1]['completed'] = $approveData[0]['BuBudgetProgress']['flag'];
                    $prepare_layer[$pre_key][$key1]['cancel_flag'] = $approveData[0]['BuBudgetProgress']['cancel_flag'];
                    $prepare_layer[$pre_key][$key1]['show_btn'] = $approveData[0]['BuBudgetProgress']['cancel_flag'] == 1 ? true : false;
                    $prepare_layer[$pre_key][$key1]['user_name'] = $approveData[0]['User']['user_name'];
                    $prepare_layer[$pre_key][$key1]['updated_date'] = $approveData[0]['BuBudgetProgress']['updated_date'];    
                }
                $budgetCondition = [
                    'BuBudgetProgress.menu_id' => $budget_menu,
                    'BuBudgetProgress.bu_term_id' => $term_id,
                    'BuBudgetProgress.target_year' => $targetYear,
                    'BuBudgetProgress.bu_code' => $bu_code,
                    'BuBudgetProgress.group_code' => $group_code,
                    'BuBudgetProgress.line_code' => 0,
                    'BuBudgetProgress.business_code' => 0,
                    'BuBudgetProgress.sub_business_code' => 0,
                    'BuBudgetProgress.flag' => 2
                ];
                $budgetComplete = $this->BuBudgetProgress->find('all', array(
                    'conditions' => $budgetCondition,
                ));
                # show complete button if user permission is COMPLETE
                if(!empty($budgetComplete) && isset($layerPer['complete'])) {
                    $prepare_layer[$pre_key][$key1]['show_btn'] = true;
                }
                if(!isset($layerPer['complete'])) {
                    $prepare_layer[$pre_key][$key1]['show_btn'] = false;
                }
            }
        }

        # according to the limited child layer permission
        if(isset($layerPer['complete']['next_layers']) && count($checkAllChild) != count($readLayer[$buTypeOrder+1])){
            foreach($prepare_layer as $preKey => $preValue) {
                # hide complete button when permission is not for all layers
                $prepare_layer[$preKey]['Layer']['show_btn'] = false;
            }
            $summaryCompleteChild = [
                'BuBudgetProgress.menu_id' => $menu_id,
                'BuBudgetProgress.bu_term_id' => $term_id,
                'BuBudgetProgress.target_year' => $targetYear,
                'BuBudgetProgress.bu_code' => $layerPer['complete']['layer'][0],
                'BuBudgetProgress.group_code' => array_values($checkAllChild),
                'BuBudgetProgress.line_code' => 0,
                'BuBudgetProgress.business_code' => 0,
                'BuBudgetProgress.sub_business_code' => 0,
                'BuBudgetProgress.flag' => 2
            ];

            # query count for all completed child layers
            $summChildComplete = $this->BuBudgetProgress->find('count', array(
                'conditions' => $summaryCompleteChild,
            ));
            # check all child layers completed or not 
            if(count($checkAllChild) === $summChildComplete){
                foreach($prepare_layer as $preKey => $preValue) {
                    # show complete button when all existing layers completed
                    if($preValue['Layer']['layer_code'] == $layerPer['complete']['layer'][0]) $prepare_layer[$preKey]['Layer']['show_btn'] = true;
                }
            }

            $budgetCompleteChild = [
                'BuBudgetProgress.menu_id' => $budget_menu,
                'BuBudgetProgress.bu_term_id' => $term_id,
                'BuBudgetProgress.target_year' => $targetYear,
                'BuBudgetProgress.bu_code' => $layerPer['complete']['layer'][0],
                'BuBudgetProgress.cancel_flag' => 1 
            ];
            $budChildComplete = $this->BuBudgetProgress->find('all', array(
                'conditions' => $budgetCompleteChild,
            ));
            if(!empty($budChildComplete)){
                foreach($prepare_layer as $preKey => $preValue) {
                    foreach($preValue as $preKey1 => $preValue1){
                        # show complete button when all existing child layers completed
                        foreach($budChildComplete as $comKey => $comLayer){
                            foreach($comLayer as $compLayer){
                                $completedLayer = $compLayer['sub_business_code'] != '0' ? $compLayer['sub_business_code'] : ($compLayer['business_code'] != '0' ? $compLayer['business_code'] : ($compLayer['line_code'] != '0' ? $compLayer['line_code'] : ($compLayer['group_code']!= '0' ? $compLayer['group_code'] : $compLayer['bu_code'])));
                                if($prepare_layer[$preKey][$preKey1]['layer_code'] == $completedLayer){
                                    $prepare_layer[$preKey][$preKey1]['show_btn'] = false;
                                }
                            }
                        }
                    }
                }
            }
        }
        # all child layer permission
        if(!isset($layerPer['complete']['next_layers'])){
            $completeChild = array_values($layerPer['complete']['layer']);
            $budgetCompleteChild = [
                'BuBudgetProgress.menu_id' => $budget_menu,
                'BuBudgetProgress.bu_term_id' => $term_id,
                'BuBudgetProgress.target_year' => $targetYear,
                'BuBudgetProgress.bu_code' => $completeChild,
                'BuBudgetProgress.cancel_flag' => 1 
            ];
            $budChildComplete = $this->BuBudgetProgress->find('all', [
                'conditions' => $budgetCompleteChild,
            ]);
            if(!empty($budChildComplete)){
                foreach($prepare_layer as $preKey => $preValue) {
                    foreach($preValue as $preKey1 => $preValue1){
                        # show complete button when all existing layers completed
                        foreach($budChildComplete as $comKey => $comLayer){
                            foreach($comLayer as $compLayer){
                                $completedLayer = $compLayer['sub_business_code'] != '0' ? $compLayer['sub_business_code'] : ($compLayer['business_code'] != '0' ? $compLayer['business_code'] : ($compLayer['line_code'] != '0' ? $compLayer['line_code'] : ($compLayer['group_code']!= '0' ? $compLayer['group_code'] : $compLayer['bu_code'])));
                                if($prepare_layer[$preKey][$preKey1]['layer_code'] == $completedLayer){
                                    $prepare_layer[$preKey][$preKey1]['show_btn'] = false;
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->Session->write('SELECTION', 'SET');
        $this->set('errormessage', parent::getErrorMsg('SE001'));
        $this->set(compact('year', 'layerTypeOrder', 'prepare_layer', 'rowColSpan', 'budgetComplete', 'readLayer'));
        $this->render('summary');
    }

    public function array_search_partial($arr, $keyword) {
        foreach($arr as $index => $string) {
            if (strpos($string, $keyword) !== FALSE)
                return $index;
        }
    }

    public function array_splice_after_key($array, $key, $array_to_insert)
    {
        $key_pos = array_search($key, array_keys($array));
        foreach($array_to_insert as $ati_key => $ati_value){
            foreach($array as $ar_key => $ar_value){
                #check the layer_order for same parent_id
                if($ati_value['parent_id'] == $ar_value['parent_id'] && $ati_value['layer_order'] >= $ar_value['layer_order']){
                    $key_pos = array_search($ar_key, array_keys($array));
                }
            }
        }
        if($key_pos !== false){
            $key_pos++;
            $second_array = array_splice($array, $key_pos); 
            $array = array_merge($array, $array_to_insert, $second_array);
        }
        return $array;
    }

    /**
     * complete respective layer
     * 
     * @date    09-28-2023
     * @author  Zeyar Min
     * @param   void
     * @return  void 
     */
    public function completeBudget(){
        $login_id = $this->Session->read('LOGIN_ID');
        $data = [];
        $today = date("Y-m-d H:i:s");
        $page_name = $this->request->data['page_name'];
        $targetYear = $_SESSION['BudgetTargetYear'];
        $term_id = $_SESSION['BU_TERM_ID'];
        $buTypeOrder = Setting::BU_BUDGET_MAX_LAYER[0];
        $menu_id = $page_name == 'BuBudgetProgress' ? Setting::MENU_ID_LIST['BudgetResult'] : Setting::MENU_ID_LIST['BusinessAnalysis'];
        $req_data = json_decode($this->request->data['layer_code'], true);
        $layer_code = $req_data['layer_code'];
        $parent_id = json_decode($req_data['parent_id'], true);
        $max_parent = max(array_keys(json_decode($req_data['parent_id'], true)));
        $bu_code = !empty($parent_id['L2']) ? $parent_id['L2'] : 0;
        $group_code = !empty($parent_id['L3']) ? $parent_id['L3'] : 0;
        $line_code = !empty($parent_id['L4']) ? $parent_id['L4'] : 0;
        $business_code = !empty($parent_id['L5']) ? $parent_id['L5'] : 0;
        $sub_business_code = !empty($parent_id['L6']) ? $parent_id['L6'] : 0;
        if($max_parent == "L1") {$bu_code = $layer_code;$check_layer = 'bu_code';}
        if($max_parent == "L2") {$group_code = $layer_code;$check_layer = 'group_code';}
        if($max_parent == "L3") {$line_code = $layer_code;$check_layer = 'line_code';}
        if($max_parent == "L4") {$business_code = $layer_code;$check_layer = 'business_code';}
        if($max_parent == "L5") {$sub_business_code = $layer_code;$check_layer = 'sub_business_code';}
        $check_layer == 'bu_code' ? $check_child = 'group_code' : ($check_layer == 'group_code' ? $check_child = 'line_code' : ($check_layer == 'line_code' ? $check_child = 'business_code' :  $check_child = 'sub_business_code'));

        $checkComplete = [
            'BuBudgetProgress.menu_id' => $menu_id,
            'BuBudgetProgress.bu_term_id' => $term_id,
            'BuBudgetProgress.target_year' => $targetYear,
            'BuBudgetProgress.bu_code' => $bu_code,
            'BuBudgetProgress.group_code' => $group_code,
            'BuBudgetProgress.line_code' => $line_code,
            'BuBudgetProgress.business_code' => $business_code,
            'BuBudgetProgress.sub_business_code' => $sub_business_code,
        ];
        if($page_name == 'BuBudgetProgress'){
            # to check the child layers cancel or not
            $allChildComplete = [
                'BuBudgetProgress.menu_id' => $menu_id,
                'BuBudgetProgress.bu_term_id' => $term_id,
                'BuBudgetProgress.target_year' => $targetYear,
                'BuBudgetProgress.'.$check_layer => $layer_code,
                'BuBudgetProgress.cancel_flag' => 1,
            ];    
        }
        $conditions = $page_name == 'BuBudgetProgress' ? ['OR' => [$checkComplete, $allChildComplete]] : $checkComplete;
        try {
            $checkAllComplete = $this->BuBudgetProgress->find('all', ['conditions' => $conditions]);
            if($checkAllComplete[0]['BuBudgetProgress']['flag'] == 2) {
                $msg = parent::getErrorMsg('SE158');
                $this->Flash->set($msg, ['key'=>'error']);
                if($page_name == 'BuBudgetProgress') $this->redirect(['action'=>'index']);
                if($page_name == 'Summary') $this->redirect(['action'=>'Summary']);
            }
            if($page_name == 'BuBudgetProgress'){
                if($checkAllComplete[0]['BuBudgetProgress']['cancel_flag'] == 1 && $checkAllComplete[0]['BuBudgetProgress'][$check_child] != '0'){
                    $msg = parent::getErrorMsg('SE159');
                    $this->Flash->set($msg, ['key'=>'error']);
                    if($page_name == 'BuBudgetProgress') $this->redirect(['action'=>'index']);
                }        
            }
            if(empty($checkAllComplete)){
                #save new row
                $tempData = [];
                $tempData['menu_id'] = $menu_id;
                $tempData['bu_term_id'] = $term_id;
                $tempData['target_year'] = $targetYear;
                $tempData['department_code'] = 0;
                $tempData['bu_code'] = $bu_code;
                $tempData['group_code'] = $group_code;
                $tempData['line_code'] = $line_code;
                $tempData['business_code'] = $business_code;
                $tempData['sub_business_code'] = $sub_business_code;
                $tempData['flag'] = 2;
                $tempData['cancel_flag'] = 0;
                $tempData['created_by'] = $login_id;
                $tempData['updated_by'] = $login_id;
                $tempData['created_date'] = $today;
                $tempData['updated_date'] = $today;
                $data[] = $tempData;

                if($page_name == 'Summary' && $max_parent == 'L1'){
                    $checkChildLayer = $this->BuBudgetProgress->find('all', [
                        'conditions' => [
                            'BuBudgetProgress.menu_id' => $menu_id,
                            'BuBudgetProgress.bu_term_id' => $term_id,
                            'BuBudgetProgress.target_year' => $targetYear,
                            'BuBudgetProgress.bu_code' => $layer_code,
                        ]
                    ]);
                    $childLayer = $this->Layer->find('list',[
                        'fields' => ['layer_code'],
                        'conditions' => [
                            'Layer.layer_code LIKE' => "%$layer_code%",
                            'Layer.type_order' => 3
                        ],
                        'order' => ['layer_code']
                    ]);
                    $childLayer = array_values($childLayer);

                    if(empty($checkChildLayer)){
                        $childData = [];
                        foreach($childLayer as $childKey => $childValue){
                            $childData['menu_id'] = $menu_id;
                            $childData['bu_term_id'] = $term_id;
                            $childData['target_year'] = $targetYear;
                            $childData['department_code'] = 0;
                            $childData['bu_code'] = $bu_code;
                            $childData['group_code'] = $childValue;
                            $childData['line_code'] = $line_code;
                            $childData['business_code'] = $business_code;
                            $childData['sub_business_code'] = $sub_business_code;
                            $childData['flag'] = 2;
                            $childData['cancel_flag'] = 0;
                            $childData['created_by'] = $login_id;
                            $childData['updated_by'] = $login_id;
                            $childData['created_date'] = $today;
                            $childData['updated_date'] = $today;
                            $data[] = $childData;
                        }
                    } else {
                        # some existing row
                        $existGroup = [];
                        foreach($childLayer as $cLayer){
                            foreach($checkChildLayer as $ccKey => $ccValue){
                                if($cLayer == $ccValue['BuBudgetProgress']['group_code']) $existGroup[] = $cLayer;
                            }
                        }
                        $newGroup = array_diff($childLayer, $existGroup);
                        foreach($checkChildLayer as $ccKey => $ccValue){
                            if($ccValue['BuBudgetProgress']['flag'] == 1){
                                # existing row
                                unset($ccValue['BuBudgetProgress']['updated_by']);
                                unset($ccValue['BuBudgetProgress']['updated_date']);
                                $ccValue['BuBudgetProgress']['flag'] = 2;
                                $ccValue['BuBudgetProgress']['cancel_flag'] = 0;
                                $ccValue['BuBudgetProgress']['updated_by'] = $login_id;
                                $ccValue['BuBudgetProgress']['updated_date'] = $today;
                                $data[] = $ccValue['BuBudgetProgress'];
                            }
                        }
                        foreach($newGroup as $newLayer){
                            #new row
                            $childData['menu_id'] = $menu_id;
                            $childData['bu_term_id'] = $term_id;
                            $childData['target_year'] = $targetYear;
                            $childData['bu_code'] = $bu_code;
                            $childData['group_code'] = $newLayer;
                            $childData['flag'] = 2;
                            $childData['cancel_flag'] = 0;
                            $childData['created_by'] = $login_id;
                            $childData['updated_by'] = $login_id;
                            $childData['created_date'] = $today;
                            $childData['updated_date'] = $today;
                            $data[] = $childData;
                        }
                    }
                }

                $saveStatus = $this->BuBudgetProgress->saveAll($data);
                if(!$saveStatus){
                    $msg = parent::getErrorMsg('SE154');
                    $this->Flash->set($msg, ['key'=>'error']);
                    if($page_name == 'BuBudgetProgress') $this->redirect(['action'=>'index']);
                    if($page_name == 'Summary') $this->redirect(['action'=>'Summary']);
                }
    
            } else {
                #update existing row
                $tempData['id'] = $checkAllComplete[0]['BuBudgetProgress']['id'];
                $tempData['menu_id'] = $menu_id;
                $tempData['bu_term_id'] = $term_id;
                $tempData['target_year'] = $targetYear;
                $tempData['department_code'] = 0;
                $tempData['bu_code'] = $bu_code;
                $tempData['group_code'] = $group_code;
                $tempData['line_code'] = $line_code;
                $tempData['business_code'] = $business_code;
                $tempData['sub_business_code'] = $sub_business_code;
                $tempData['flag'] = 2;
                $tempData['cancel_flag'] = 0;
                $tempData['updated_by'] = $login_id;
                $tempData['updated_date'] = $today;
                $data[] = $tempData;
                

                if($page_name == 'Summary' && $max_parent == 'L1'){
                    $checkChildLayer = $this->BuBudgetProgress->find('all', [
                        'conditions' => [
                            'BuBudgetProgress.menu_id' => $menu_id,
                            'BuBudgetProgress.bu_term_id' => $term_id,
                            'BuBudgetProgress.target_year' => $targetYear,
                            'BuBudgetProgress.group_code LIKE' => "%$layer_code%",
                        ]
                    ]);
                    $childLayer = $this->Layer->find('list',[
                        'fields' => ['layer_code'],
                        'conditions' => [
                            'Layer.layer_code LIKE' => "%$layer_code%",
                            'Layer.type_order' => $buTypeOrder+1
                        ],
                        'order' => ['layer_code']
                    ]);
                    $childLayer = array_values($childLayer);

                    if(!empty($checkChildLayer)){
                        $existGroup = [];
                        foreach($childLayer as $cLayer){
                            foreach($checkChildLayer as $ccKey => $ccValue){
                                if($cLayer == $ccValue['BuBudgetProgress']['group_code']) $existGroup[] = $cLayer;
                            }
                        }
                        $newGroup = array_diff($childLayer, $existGroup);
                        foreach($checkChildLayer as $ccKey => $ccValue){
                            if($ccValue['BuBudgetProgress']['flag'] == 1){
                                # existing row
                                unset($ccValue['BuBudgetProgress']['updated_by']);
                                unset($ccValue['BuBudgetProgress']['updated_date']);
                                $ccValue['BuBudgetProgress']['flag'] = 2;
                                $ccValue['BuBudgetProgress']['cancel_flag'] = 0;
                                $ccValue['BuBudgetProgress']['updated_by'] = $login_id;
                                $ccValue['BuBudgetProgress']['updated_date'] = $today;
                                $data[] = $ccValue['BuBudgetProgress'];
                            }
                        }
                        foreach($newGroup as $newLayer){
                            #new row
                            $childData['menu_id'] = $menu_id;
                            $childData['bu_term_id'] = $term_id;
                            $childData['target_year'] = $targetYear;
                            $childData['bu_code'] = $bu_code;
                            $childData['group_code'] = $newLayer;
                            $childData['flag'] = 2;
                            $childData['cancel_flag'] = 0;
                            $childData['created_by'] = $login_id;
                            $childData['updated_by'] = $login_id;
                            $childData['created_date'] = $today;
                            $childData['updated_date'] = $today;
                            $data[] = $childData;
                        }
                    } else {
                        foreach($childLayer as $cLayer){
                            #new row
                            $childData['menu_id'] = $menu_id;
                            $childData['bu_term_id'] = $term_id;
                            $childData['target_year'] = $targetYear;
                            $childData['bu_code'] = $bu_code;
                            $childData['group_code'] = $cLayer;
                            $childData['flag'] = 2;
                            $childData['cancel_flag'] = 0;
                            $childData['created_by'] = $login_id;
                            $childData['updated_by'] = $login_id;
                            $childData['created_date'] = $today;
                            $childData['updated_date'] = $today;
                            $data[] = $childData;
                        }
                    }
                }

                $saveStatus = $this->BuBudgetProgress->saveAll($data);
                if(!$saveStatus){
                    $msg = parent::getErrorMsg('SE154');
                    $this->Flash->set($msg, ['key'=>'error']);
                    if($page_name == 'BuBudgetProgress') $this->redirect(['action'=>'index']);
                    if($page_name == 'Summary') $this->redirect(['action'=>'Summary']);
                }
            }    
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $msg = parent::getErrorMsg('SE154');
            $this->Flash->set($msg, ['key'=>'error']);
            if($page_name == 'BuBudgetProgress') $this->redirect(['action'=>'index']);
            if($page_name == 'Summary') $this->redirect(['action'=>'Summary']);
        }

        $msg = parent::getSuccessMsg('SS033');
        $this->Flash->set($msg, ['key'=>'success']);
        if($page_name == 'BuBudgetProgress') $this->redirect(['action'=>'index']);
        if($page_name == 'Summary') $this->redirect(['action'=>'Summary']);
    }

    /**
     * countCompleteLayer method
     * checking complete or not
     * @param $menu_id, $term_id, $code, $code_lists
     * @author Khin Hnin Myo
     * @return $show_flag
     */
    public function countCompleteLayer($menu_id, $term_id, $code, $code_lists) {
        $show_flag = false;
        $complete_layer_count = $this->BuBudgetProgress->find('count', array(
            'conditions' => array(
                'BuBudgetProgress.menu_id' => $menu_id,
                'BuBudgetProgress.bu_term_id' => $term_id,
                'BuBudgetProgress.target_year' => $_SESSION['BudgetTargetYear'],
                'BuBudgetProgress.flag' => 2,
                'BuBudgetProgress.bu_code' => $code,
                'BuBudgetProgress.group_code' => $code_lists,
                'BuBudgetProgress.line_code' => '0',
                'BuBudgetProgress.business_code' => '0',
                'BuBudgetProgress.sub_business_code' => '0',
            )
        ));
        if($complete_layer_count == count($code_lists)) {
            $show_flag = true;
        }
        return $show_flag;
    }
}
