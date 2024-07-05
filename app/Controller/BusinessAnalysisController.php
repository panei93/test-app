<?php

/**
 * ActualResults Controller
 *
 * @property ActualResult $ActualResult
 * @property PaginatorComponent $Paginator
 */
App::uses('AppController', 'Controller');
App::import('Controller', 'BudgetResult');
App::import('Controller', 'Common');
class BusinessAnalysisController extends AppController
{
    /**
     * Components
     *
     * @var array
     */

    public $components = array('Session','PhpExcel.PhpExcel');
    public $uses = array('Layer', 'LayerType', 'Budget', 'Account', 'EmpNum', 'BudgetSng', 'BudgetComp', 'TransactionPolicy', 'AccountSetting', 'BuApprovedLog');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkBuUrlSession($this->name);#checkurlsession
    }

    public function index()
    {
        $this->layout = 'buanalysis';
        $BUTopLayer = Setting::BU_LAYER_SETTING['topLayer'];
        $menuId = Setting::MENU_ID_LIST['BudgetResult'];
        $bu_menuId = Setting::MENU_ID_LIST['BusinessAnalysis'];
        if($this->Session->read('SELECTION') == 'SET'){
            $search_data['target_year'] = $this->Session->read('BudgetTargetYear');
            $search_data['bu'] = $this->Session->read('SELECTED_BU');
            $search_data['group'] = $this->Session->read('SELECTED_GROUP');
        }else if($this->Session->read('SELECTION') == 'NOT') {
            $search_data = $this->Session->read('SEARCH_LABOR_COST');
        }
       
        $pageLimit = $this->Session->read('PAGE_LIMITATION');
        $login_id = $this->Session->read('LOGIN_ID');
        $admin_level = $this->Session->read('ADMIN_LEVEL_ID');
        $yearListOnTerm = $this->Session->read('yearListOnTerm');
        $pagename = $this->request->params['controller'].'Sheet';
        $selectedTermYear = $_SESSION['yearListOnTerm'][$search_data['target_year']];
        $start_month = $selectedTermYear[0];
        $end_month = $selectedTermYear[1];
        $maxLimit = 2;
        $Common = New CommonController();
        //$layerPer = $Common->getPermissionsByRole($login_id, $admin_level, $pagename);
        $layerPer = $Common->getPermissionsByRoleForBU($login_id, $admin_level,$start_month, $end_month, $pagename, $maxLimit);
        // $pageLimit['LaborCostsSaveLimit'] = false;
        // get layer for all method(index, save, confirm, confirm_cancel)
        
        $readLayer = array();
        $saveLayer = array();
        $confirmLayer = array();
        $confirmCancelLayer = array();
        $nextLayer = array();
        $allLayer_per = array();
        foreach($layerPer['index']['layer'] as $code){
            foreach($layerPer['index']['all_layer'][$code][3] as $key=>$value){
                $readLayer[] = $value;
                $allLayer_per[] = $value;
            }
            foreach($layerPer['save']['all_layer'][$code][3] as $key=>$value){
                $saveLayer[] = $value;
            }
            foreach($layerPer['confirm']['all_layer'][$code][3] as $key=>$value){
                $confirmLayer[] = $value;
            }
            foreach($layerPer['confirm_cancel']['all_layer'][$code][3] as $key=>$value){
                $confirmCancelLayer[] = $value;
            }
            if($layerPer['index']['next_layers']) {
                $readLayer = array();
                $saveLayer = array();
                $confirmLayer = array();
                $confirmCancelLayer = array();
            }
            foreach($layerPer['index']['next_layers'][$maxLimit+1] as $key=>$value){
                $nextLayer[] = $value;
                $readLayer[] = $value;
                $saveLayer[] = $value;
                $confirmLayer[] = $value;
                $confirmCancelLayer[] = $value;
            }
        }
        //pr($layerPer);
        $allowAll = 'allow';
        if(sizeof($nextLayer) > 0 && sizeof($nextLayer) != sizeof($allLayer_per)) $allowAll = 'not';
        
        $term_id = $_SESSION['BU_TERM_ID'];
        if($this->request->is('post')){
            $this->Session->write('SELECTION', 'NOT');
            $search_data = $this->request->data;
            $this->Session->write('SEARCH_LABOR_COST.target_year', $this->request->data['target_year']);
            $this->Session->write('SEARCH_LABOR_COST.bu', $this->request->data['bu']);
            $this->Session->write('SEARCH_LABOR_COST.group', $this->request->data['group']);
        }
        // pr($search_data);
        $selected_bu = $search_data['bu'];
        $selected_group = $search_data['group'];
        $selected_year = $search_data['target_year'];
        
        $testYear = array();
        foreach($yearListOnTerm as $key=>$value){
            $testYear[] = $key;
        }

        foreach($testYear as $tyear){
            $t_layer = $this->Layer->find('all', array(
                'fields' => array('Layer.id', 'Layer.layer_code', 'Layer.name_en', 'Layer.layer_order'),
                'conditions' => array('Layer.layer_code' => $readLayer, 'Layer.flag' => '1', 'Layer.bu_status' => '1', 'Layer.type_order' => 3,'DATE_FORMAT(Layer.from_date, "%Y") <=' => $tyear, 'DATE_FORMAT(Layer.to_date, "%Y") >=' => $tyear),
                'order' => 'Layer.type_order,layer_order,layer_code ASC'
            ));
            $bu_layer = $this->Layer->find('all', array(
                'fields' => array('Layer.id', 'Layer.layer_code', 'Layer.name_en', 'Layer.layer_order'),
                'conditions' => array('Layer.layer_code' => $layerPer['index']['layer'], 'Layer.flag' => '1', 'Layer.bu_status' => '1', 'Layer.type_order' => 2,'DATE_FORMAT(Layer.from_date, "%Y") <=' => $tyear, 'DATE_FORMAT(Layer.to_date, "%Y") >=' => $tyear),
                'order' => 'Layer.type_order,layer_order,layer_code ASC'
            ));
            foreach($bu_layer as $bul_key => $bul_value){
                foreach($layerPer['index']['layer'] as $bu_value){
                    if($bu_value == $bul_value['Layer']['layer_code']){
                        $bu[$tyear][$bu_value] = $bul_value['Layer']['name_en'];
                    }
                }    
            }
            $testYear1[$tyear] = $t_layer;
        }
        foreach($testYear1 as $key=>$value){
            foreach($value as $vlaue1){
                $group_array[$key][$vlaue1['Layer']['id']] = $vlaue1['Layer']['layer_code'].'/'.$vlaue1['Layer']['name_en'];
            }
        }
        
        $parent_search_data['bu'] = $search_data['bu'];
        $parent_search_data['group'] = 'all';
        
        $buFlag = $this->getApproveLog($term_id, $menuId, $selected_year, $search_data);
        $parent_spreadsheetFlag = $this->getApproveLog($term_id, $bu_menuId, $selected_year, $parent_search_data);
        $spreadsheetFlag = $this->getApproveLog($term_id, $bu_menuId, $selected_year, $search_data);
        $prepare_parent = array();
        $year = array();
        // $bu = array();
        $group = array();
        $bu_layerCode = array();
        $group_layerCode = array();
        foreach($group_array as $key=>$value){
            $i = 0;
            foreach($value as $lKey=>$lValue){
                $parent_layer = $this->Layer->find('all', array(
                    'fields' => array('Layer.layer_code', 'Layer.parent_id', 'Layer.name_en'),
                    'conditions' => array('Layer.flag' => '1', 'Layer.id' => $lKey, 'DATE_FORMAT(Layer.from_date, "%Y") <=' => $key, 'DATE_FORMAT(Layer.to_date, "%Y") >=' => $key),
                    'order' => 'Layer.type_order, layer_order,layer_code ASC'
                ));
                $parents_json = json_decode($parent_layer[0]['Layer']['parent_id'], true);
                $parent_name = $this->Layer->find('all', array(
                    'fields' => array('Layer.name_en'),
                    'conditions' => array('Layer.flag' => '1', 'Layer.layer_code' => $parents_json['L2'], 'DATE_FORMAT(Layer.from_date, "%Y") <=' => $key, 'DATE_FORMAT(Layer.to_date, "%Y") >=' => $key),
                    'order' => 'Layer.type_order, layer_order,layer_code ASC'
                ));
                if(sizeof($parent_name) > 0) {
                    // $bu[$key][$parents_json['L2']] = $parents_json['L2'].'/'.$parent_name[0]['Layer']['name_en'];
                    $bu_layerCode[$key][$parents_json['L2']] = $parents_json['L2'];
                }
                $group[$key][$parents_json['L2'].'/'.$parent_name[0]['Layer']['name_en']][$parent_layer[0]['Layer']['layer_code']] = $parent_layer[0]['Layer']['name_en'];
                $group_layerCode[$key][$parents_json['L2']][] = $parent_layer[0]['Layer']['layer_code'];
                $i++;
            }
        }
        // if bu_status is false
        if(!array_key_exists($selected_bu, $bu[$search_data['target_year']])) {
            $selected_bu = array_key_first($bu[$selected_year]);
            $selected_group = 'all';

        }
        $no_data = false;
        if($selected_bu == '') $no_data = true;
        if(!in_array($selected_group, $group_layerCode[$search_data['target_year']][$selected_bu])) $selected_group = 'all';
        
        $allow_save = false;
        $allow_confirm = false;
        $allow_confirm_cancel = false;
        if($selected_group != 'all'){
            if(in_array($selected_group, $saveLayer)) $allow_save = true;
            if(in_array($selected_group, $confirmLayer)) $allow_confirm = true;
            if(in_array($selected_group, $confirmCancelLayer)) $allow_confirm_cancel = true;
            
            //if(in_array('S11601', $group_layerCode[$search_data['target_year']])) $allow_save = true;
            //if(in_array('S11601', $confirmCancelLayer)) $allow_confirm_cancel = true;
        }else if($selected_group == 'all'){
            $saveDiff = array_diff($saveLayer, $group_layerCode[$search_data['target_year']][$selected_bu]);
            $confirmDiff = array_diff($confirmLayer, $group_layerCode[$search_data['target_year']][$selected_bu]);
            $confirmCancelDiff = array_diff($confirmCancelLayer, $group_layerCode[$search_data['target_year']][$selected_bu]);
            if(sizeOf($saveDiff) == (sizeOf($saveLayer) - sizeOf($group_layerCode[$search_data['target_year']][$selected_bu]))) $allow_save = true;
            if(sizeOf($confirmDiff) == (sizeOf($confirmLayer) - sizeOf($group_layerCode[$search_data['target_year']][$selected_bu]))) $allow_confirm = true;
            if(sizeOf($confirmCancelDiff) == (sizeOf($confirmCancelLayer) - sizeOf($group_layerCode[$search_data['target_year']][$selected_bu]))) $allow_confirm_cancel = true;
            
        }
       
        //$selected_bu = array_key_first($bu[$selected_year]);
        //if($this->Session->read('SEARCH_LABOR_COST')['bu'] != '') $selected_bu = $this->Session->read('SEARCH_LABOR_COST')['bu'];
        //if($this->Session->read('SEARCH_LABOR_COST')['group'] != '') $selected_group = $this->Session->read('SEARCH_LABOR_COST')['group'];
        //$year = range(array_key_first($group_array), array_key_last($group_array));
        foreach($group_array as $key=>$value){
            $year[] = $key;

        }
        if($selected_group != 'all') $bu_typeOrder = $BUTopLayer;
        else $bu_typeOrder = $BUTopLayer - 1;
        $lOrder = $this->LayerType->find('list', array(
            'fields' => array('LayerType.type_order'),
            'conditions' => array('LayerType.flag' => '1', 'LayerType.type_order >='=> $bu_typeOrder),
            'order' => 'LayerType.type_order ASC'
        ));
        
        $field = array('Layer.layer_code', 'Layer.parent_id', 'Layer.name_jp', 'Layer.item_1', 'Layer.item_2', 'Layer.form', 'Layer.type_order', 'bu_status', 'Layer.layer_order');
        $condition_arr = array();
        
        $i = 0;
        foreach($lOrder as $value){
            if($selected_bu == 'all' && $selected_group == 'all'){
                $condition_arr = array(
                    'Layer.flag' => '1',
                    'Layer.bu_status' => '1',
                    'Layer.type_order'=>$value,
                    'DATE_FORMAT(Layer.from_date, "%Y") <=' => $selected_year,
                    'DATE_FORMAT(Layer.to_date, "%Y") >=' => $selected_year
                );
                
            }else if(($selected_bu == 'all' && $selected_group != 'all') || ($selected_bu != 'all' && $selected_group != 'all')){
                
                if($i == 0){
                    $condition_arr = array(
                        'Layer.layer_code' => $selected_group,
                        'Layer.flag' => '1',
                        'Layer.bu_status' => '1',
                        'Layer.type_order'=>$value,
                        'DATE_FORMAT(Layer.from_date, "%Y") <=' => $selected_year,
                        'DATE_FORMAT(Layer.to_date, "%Y") >=' => $selected_year
                    );
                }else{
                    $layerNo = $value - 1;
                    $condition_arr = array(
                        'Layer.parent_id LIKE' => '%'.$selected_group.'%',
                        'Layer.flag' => '1',
                        'Layer.bu_status' => '1',
                        'Layer.type_order'=>$value,
                        'DATE_FORMAT(Layer.from_date, "%Y") <=' => $selected_year,
                        'DATE_FORMAT(Layer.to_date, "%Y") >=' => $selected_year
                    );
                }
            }else if($selected_bu != 'all' && $selected_group == 'all'){
                if($layerPer['index']['next_layers'] ){
                    if($i == 0){
                        $condition_arr = array(
                            'Layer.layer_code' => $selected_bu,
                            'Layer.flag' => '1',
                            'Layer.bu_status' => '1',
                            'Layer.type_order'=>$value,
                            'DATE_FORMAT(Layer.from_date, "%Y") <=' => $selected_year,
                            'DATE_FORMAT(Layer.to_date, "%Y") >=' => $selected_year
                        );
                    }else{
                        $condition_arr = array(
                            'Layer.parent_id LIKE' => '%'.$nextLayer[0].'%',
                            'Layer.flag' => '1',
                            'Layer.bu_status' => '1',
                            'Layer.type_order'=>$value,
                            'DATE_FORMAT(Layer.from_date, "%Y") <=' => $selected_year,
                            'DATE_FORMAT(Layer.to_date, "%Y") >=' => $selected_year
                        );
                    }
                    
                }else{
                    if($i == 0){
                        $condition_arr = array(
                            'Layer.layer_code' => $selected_bu,
                            'Layer.flag' => '1',
                            'Layer.bu_status' => '1',
                            'Layer.type_order'=>$value,
                            'DATE_FORMAT(Layer.from_date, "%Y") <=' => $selected_year,
                            'DATE_FORMAT(Layer.to_date, "%Y") >=' => $selected_year
                    );
                    }else{
                        $layerNo = $value - 1;
                        $condition_arr = array(
                            'Layer.parent_id LIKE' => '%'.$selected_bu.'%',
                            'Layer.flag' => '1',
                            'Layer.bu_status' => '1',
                            'Layer.type_order'=>$value,
                            'DATE_FORMAT(Layer.from_date, "%Y") <=' => $selected_year,
                            'DATE_FORMAT(Layer.to_date, "%Y") >=' => $selected_year
                        );
                    }
                }
                
            }
            $allLayer[] = $this->Layer->find('all', array(
                'fields' => $field,
                'conditions' => $condition_arr,
                'order' => 'Layer.type_order, Layer.layer_order, Layer.layer_code ASC'
            ));
            $i++;
        }
        $nextLCode = array();
        //$i = 0;
        foreach($allLayer as $akey=>$avalue){
            if($akey == 0) {
                foreach($avalue as $key=>$value){
                    $lCode[$key] = $value['Layer']['layer_code'];
                    $prev_layerTypeOrder = $value['Layer']['type_order'];
                    $prev_parent = json_decode($value['Layer']['parent_id'], true);
                }
                $prepare_layer = $avalue;
            }
            if($akey != 0){

                foreach($avalue as $value){
                    $insert = array();
                    $nextLCode[] = $value['Layer']['layer_code'];
                    $parent_id = json_decode($value['Layer']['parent_id'], true);
                    if(in_array($parent_id['L'.$bu_typeOrder], $lCode)){
                        $arrKey = array_keys($lCode,$parent_id['L'.$bu_typeOrder]);
                        $lNo = $value['Layer']['type_order'];
                        if(in_array($parent_id['L'.($lNo-1)], $nextLCode)) {
                            $insert['Layer'.$lNo.'_'.$value['Layer']['layer_code']] = $value['Layer'];
                            if ($prev_layerTypeOrder == $value['Layer']['type_order'] && $prev_parent['L'.($lNo-1)] == $parent_id['L'.($lNo-1)]) {
                                $newInsert = $this->array_splice_after_key($prepare_layer[$arrKey[0]], 'Layer'.($lNo).'_'.$nextLCode[sizeof($nextLCode)-2], $insert);
                            }else{
                                $newInsert = $this->array_splice_after_key($prepare_layer[$arrKey[0]], 'Layer'.($lNo-1).'_'.$parent_id['L'.($lNo-1)], $insert);
                            }
                            
                            $prepare_layer[$arrKey[0]] = $newInsert;
                            
                        }
                        else {
                            $prepare_layer[$arrKey[0]]['Layer'.$lNo."_".$value['Layer']['layer_code']] = $value['Layer'];
                        }

                    }
                    $prev_layerTypeOrder = $value['Layer']['type_order'];
                    $prev_parent = json_decode($value['Layer']['parent_id'], true);
                }
                
            }
            
        }
        $current_year = date('Y');
       
        // year to show in table result
        $resultYear = range($search_data['target_year']-3, $search_data['target_year']+2);
        // 0_売上総利益 , 1_売上総利益率 , 2_税後利益　, 3_海外組織の売上総利益, 
        // 4_総資産（期中平均）, 5_Net運転資金（期中平均）, 6_FCF, 7_ファクタリング考慮前ROIC (%)
        
        $accountNames = array();
        $account = array();
		$account = $Common->getAccountByPage($this->params);
        foreach($account['AccountNameOnly'] as $key=>$value){
            $accountCode[] = $key;
            $accountNames[$value] = $key;
        }
        $empName = array('経営・管理', '営業', 'ｵﾍﾟﾚｰｼｮﾝ'); 
        $nearestBLayer = $bu_typeOrder;
        $bottomLayer = $bu_typeOrder + 1;
        $nearestBLayerName = $this->Layer->find('all', array(
            'fields' => array('Layer.layer_code', 'Layer.name_en', 'Layer.item_1', 'Layer.item_2', 'Layer.form', ),
            'conditions' => array('Layer.flag' => '1', 'Layer.type_order'=>$nearestBLayer, 'DATE_FORMAT(Layer.from_date, "%Y") <=' => $selected_year, 'DATE_FORMAT(Layer.to_date, "%Y") >=' => $selected_year),
            'order' => 'Layer.name_en ASC'
        ));
        $bottomLayerName = $this->Layer->find('all', array(
            'fields' => array('Layer.layer_code', 'Layer.parent_id', 'Layer.name_en', 'Layer.item_1', 'Layer.item_2', 'Layer.form'),
            'conditions' => array('Layer.flag' => '1', 'Layer.type_order'=>$bottomLayer, 'DATE_FORMAT(Layer.from_date, "%Y") <=' => $selected_year, 'DATE_FORMAT(Layer.to_date, "%Y") >=' => $selected_year),
            'order' => 'Layer.name_en ASC'
        ));
        if(count($account) > 0){
            // create layer with bottom layer and his nearest upper layer
            $layer = array();
            foreach($nearestBLayerName as $key=>$value){
                foreach($bottomLayerName as $bkey=>$bvalue){
                    $parent_id = json_decode($bvalue['Layer']['parent_id'], true);
                    $code = 'L'.$nearestBLayer;
                    if($parent_id[$code] == $value['Layer']['layer_code']){
                        $layer[$key]['topLayer'] = $value['Layer'];
                        //$layer[$key]['bottomLayer']['layer_code'] = $bvalue['Layer']['layer_code'];
                        $layer[$key]['bottomLayer'][] = $bvalue['Layer'];
                    }else{
                        $layer[$key]['topLayer'] = $value['Layer'];
                    }
                }
            }
            
            // get emp number
            $empYear = range($search_data['target_year']-1, $search_data['target_year']);
            $totalHyoka = array();
            $hyokaName = array();
            $BudgetHyoka = array();
            // add amount for each layer
            if($search_data['group'] != 'all') $layerTypeOrder = 3;
            else $layerTypeOrder = 2;
            $i = 0;
            foreach($prepare_layer as $pkey=>$pvalue){
                foreach($pvalue as $key=>$value){
                    $n0 = substr($key, 5);
                    $layerCode = $value['layer_code'];
                    $typeOrder = $value['type_order'];
                    
                    $amount = $this->Budget->find('all',
                    array(
                        'fields' => array('Budget.target_year', 'sum(amount) as amount', 'Budget.account_id'),
                        'conditions' => array('Budget.layer_code' => $layerCode, 'Budget.target_year IN'=>$resultYear, 'Budget.account_id IN'=>$accountCode, 'Budget.bu_term_id' => $term_id),
                        'group' => array('Budget.target_year', 'Budget.account_id'),
                    ));
                    if(sizeof($amount) > 0){
                        foreach($amount as $aKey=>$aValue){
                        
                            $code = $aValue['Budget']['account_id'];
                            $targetYear = $aValue['Budget']['target_year'];
                            $accountName = array_search($code, $accountNames);
                            $aAValue = $aValue[0]['amount']/1000;
                            //if(strpos($accountName,"（％）")) $aAValue = $aAValue*100;
                            $prepare_layer[$pkey]['Layer'.$n0]['amount'][$accountName][$targetYear] = $aAValue;
                            for($j = 1 ;$j <= $value['type_order']; $j++){
                                $parents_json = json_decode($value['parent_id'], true);
                                $parentLayer = 'Layer'.($value['type_order'] - $j).'_'.$parents_json['L'.($value['type_order'] - $j)];
                                if($j == $value['type_order']) $prepare_layer[$pkey]['Layer']['amount'][$accountName][$targetYear] += $aAValue;
                                else {
                                    if(isset($prepare_layer[$pkey][$parentLayer])) $prepare_layer[$pkey][$parentLayer]['amount'][$accountName][$targetYear] += $aAValue;
                                }
                                
                            }
                        }
                    }else{
                        foreach($accountNames as $aKey=>$avalue){
                            foreach($resultYear as $yvalue){
                                $prepare_layer[$pkey]['Layer'.$n0]['amount'][$aKey][$yvalue] = 0;
                            }
                        }
                    }
                    if($typeOrder == 2){
                        $bu_code = $layerCode;
                        $group_code = 0;
                    }else if($typeOrder == 3){
                        $parents_json = json_decode($value['parent_id'], true);
                        $bu_code = $parents_json['L2'];
                        $group_code = $layerCode;
                    }
                    $appCondition = array(
                        'menu_id' => $bu_menuId,
                        'bu_term_id' => $term_id,
                        'target_year' => $selected_year,
                        'department_code' => 0,
                        'bu_code' => $bu_code,
                        'group_code' => $group_code,
                        'line_code' => 0,
                        'business_code' => 0,
                        'sub_business_code' => 0,
                    );
                    //pr($appCondition);
                    $approveLog = $this->BuApprovedLog->find('all',
                        array(
                            'fields' => array('BuApprovedLog.flag'),
                            'conditions' => $appCondition,
                        )
                    );
                    if(sizeof($approveLog) > 0){
                        $prepare_layer[$pkey][$key]['approveLog'] = $approveLog[0]['BuApprovedLog'];
                    }
                    $empNum = $this->EmpNum->find('all',
                        array(
                            'fields' => array('EmpNum.layer_code', 'EmpNum.target_year', 'EmpNum.emp', 'EmpNum.emp_amt'),
                            'conditions' => array('EmpNum.layer_code' => $layerCode, 'EmpNum.target_year IN'=>$empYear, 'EmpNum.bu_term_id' => $term_id),
                        )
                    );
                    if(sizeof($empNum) > 0){
                        foreach($empNum as $ekey=>$evalue){
                            $prepare_layer[$pkey]['Layer'.$n0]['emp'][$evalue['EmpNum']['emp']][$evalue['EmpNum']['target_year']] = $evalue['EmpNum']['emp_amt'];
                            for($k = 1 ;$k <= $value['type_order']; $k++){
                                $parents_json = json_decode($value['parent_id'], true);
                                $parentLayer = 'Layer'.($value['type_order'] - $k).'_'.$parents_json['L'.($value['type_order'] - $k)];
                                if($k == $value['type_order']) $prepare_layer[$pkey]['Layer']['emp'][$evalue['EmpNum']['emp']][$evalue['EmpNum']['target_year']] += $evalue['EmpNum']['emp_amt'];
                                else {
                                    if(isset($prepare_layer[$pkey][$parentLayer])) $prepare_layer[$pkey][$parentLayer]['emp'][$evalue['EmpNum']['emp']][$evalue['EmpNum']['target_year']] += $evalue['EmpNum']['emp_amt'];
                                }
                                
                            }
                        }
                    }
                    $sngs = $this->BudgetSng->find('all',
                        array(
                            'fields' => array('BudgetSng.layer_code', 'BudgetSng.unit', 'SUM(sng_amt) as sngAmount'),
                            'conditions' => array('BudgetSng.target_year'=>$search_data['target_year'], 'BudgetSng.layer_code' => $layerCode, 'BudgetSng.sng_no IN'=>array(1,2,3), 'BudgetSng.bu_term_id' => $term_id),
                        )
                    );
                    if(sizeof($sngs) > 0){
                        foreach($sngs as $skey=>$svalue){
                            $prepare_layer[$pkey]['Layer'.$n0]['sgns'] = $svalue[0]['sngAmount'] ? $svalue[0]['sngAmount']/$svalue['BudgetSng']['unit'] : 0;
                            for($s = 1 ;$s <= $value['type_order']; $s++){
                                $parents_json = json_decode($value['parent_id'], true);
                                $parentLayer = 'Layer'.($value['type_order'] - $s).'_'.$parents_json['L'.($value['type_order'] - $s)];
                                if($s == $value['type_order']) $prepare_layer[$pkey]['Layer']['sgns'] += $svalue[0]['sngAmount'] ? $svalue[0]['sngAmount']/$svalue['BudgetSng']['unit'] : 0;
                                else {
                                    if(isset($prepare_layer[$pkey][$parentLayer])) $prepare_layer[$pkey][$parentLayer]['sgns'] += $svalue[0]['sngAmount'] ? $svalue[0]['sngAmount']/$svalue['BudgetSng']['unit'] : 0;
                                }
                                
                            }
                        }
                    }
                    
                    $BudgetResult = new BudgetResultController;
                    $comp = $BudgetResult->CalculateBudgetComp($term_id, $layerCode, $search_data['target_year']);
                    if($typeOrder != 4) $hyoka = $BudgetResult->BudgetHyoka($term_id, $layerCode, $search_data['target_year']);
                    $prepare_layer[$pkey]['Layer'.$n0]['BudgetComp'] = $comp;

                    foreach($comp['final_total'] as $cKey=>$cValue){
                        if($cKey == 'deli_product' || $cKey == 'indus_fproduct' || $cKey == 'deli_chg_product' || $cKey == 'indus_chg_fproduct' || $cKey == 'final_potential'){
                            if($cKey == 'deli_chg_product' || $cKey == 'indus_chg_fproduct' || $cKey == 'final_potential'){
                                $comp['final_total'][$cKey].'->';
                                $product = substr($comp['final_total'][$cKey], 3);
                                for($c = 1 ;$c <= $value['type_order']; $c++){
                                    $parents_json = json_decode($value['parent_id'], true);
                                    $parentLayer = 'Layer'.($value['type_order'] - $c).'_'.$parents_json['L'.($value['type_order'] - $c)];
                                    if($c == $value['type_order']) {
                                        $prepare_layer[$pkey]['Layer']['BudgetComp']['final_total'][$cKey] += floatval($product);
                                        $prepare_layer[$pkey]['Layer']['BudgetComp']['final_total']['add_triangle'] = true;
                                    }
                                    else {
                                        if(isset($prepare_layer[$pkey][$parentLayer])) {
                                            $prepare_layer[$pkey][$parentLayer]['BudgetComp']['final_total'][$cKey] += floatval($product);
                                            $prepare_layer[$pkey][$parentLayer]['BudgetComp']['final_total']['add_triangle'] = true;
                                        }
                                    }

                                    
                                }
                            }
                            else{
                                $product = $comp['final_total'][$cKey];
                                for($c = 1 ;$c <= $value['type_order']; $c++){
                                    $parents_json = json_decode($value['parent_id'], true);
                                    $parentLayer = 'Layer'.($value['type_order'] - $c).'_'.$parents_json['L'.($value['type_order'] - $c)];
                                    if($c == $value['type_order']) $prepare_layer[$pkey]['Layer']['BudgetComp']['final_total'][$cKey] += $comp['final_total'][$cKey];
                                    else {
                                        if(isset($prepare_layer[$pkey][$parentLayer])) $prepare_layer[$pkey][$parentLayer]['BudgetComp']['final_total'][$cKey] += $comp['final_total'][$cKey];
                                    }
                                    
                                }
                            }
                            
                        }
                    }
                    $BudgetHyoka = array();
                    foreach($hyoka as $hKey=>$hVlaue){
                        if($hVlaue['csr_record'] == '' && $layerCode == $hVlaue['layer_code']) {
                            //$hyokaCount++;
                            $hyokaName[] = $hVlaue['region'];
                            $BudgetHyoka['layerCode'] = $layerCode;
                            $BudgetHyoka[$hVlaue['region']] = $hVlaue['evaluation'];
                        }else $BudgetHyoka['CSR'] = ($hVlaue['csr_record'] == 1) ? '該' : '非' ;
                    }
                    if(sizeof($hyoka[0]) > 0) $prepare_layer[$pkey]['Layer'.$n0]['BudgetHyoka'] = $BudgetHyoka;
                    $transPolicy = $this->TransactionPolicy->find('all',
                        array(
                            'fields' => array('expansion', 'maintain', 'withdraw', 'transactionTerm'),
                            'conditions' => array('layer_code' => $layerCode, 'target_year'=>$search_data['target_year'], 'bu_term_id' => $term_id),
                        )
                    );
                    $prepare_layer[$pkey]['Layer'.$n0]['TransactionPolicy'] = $transPolicy[0]['TransactionPolicy'];
                    if($i != 0) $currentTypeOrder = $value['type_order'];
                    $i++;
                }
                
            }
            $hyoka_name = array_filter($hyokaName);
            $hyoka_name_arr = array_unique($hyoka_name);

            $name = $accountNames;
            $accountNames = array();
            foreach($name as $key=>$value){
                $accountNames[] = $key;
            }
        }
        foreach($prepare_layer[0] as $key=>$value){
            if($value['type_order'] == 4) unset($prepare_layer[0][$key]);
            if($value['type_order'] == 6) $prepare_layer[0][$key]['rowLevel'] = 1;
            else if($value['type_order'] == 5) $prepare_layer[0][$key]['rowLevel'] = 2;
            else if($value['type_order'] == 3) $prepare_layer[0][$key]['rowLevel'] = 3;
            else if($value['type_order'] == 2) $prepare_layer[0][$key]['rowLevel'] = 4;
        }
        
        if($this->request->data['download'] != ''){
            $name = $this->request->data['download'];
            $showRow = $this->request->data['showRow'];
            $s_showRow = $this->request->data['s_showRow'];
            $nameExplode = explode('_', $name);
            /*$buName = explode('/', $nameExplode[2]);
            $groupName = explode('/', $nameExplode[3]);
            $fileName = $nameExplode[1].'_';
            if($nameExplode[3] != 'ALL') $fileName .= $buName[1].'_'.$groupName[1];
            else $fileName .= $buName[1];*/
            $buName = $nameExplode[2];
            $groupName = $nameExplode[3];
            $fileName = $nameExplode[1].'_';
            if($nameExplode[3] != 'ALL') $fileName .= $buName.'_'.$groupName;
            else $fileName .= $buName;
            $this->spreadsheetExcel($fileName, $prepare_layer, $accountNames, $resultYear, $search_data, $hyoka_name_arr, $showRow, $s_showRow);

        }
        $this->set(compact('year', 'search_data', 'resultYear', 'accountNames', 'hyoka_name_arr', 'lOrder', 'prepare_layer', 'bu', 'group', 'buFlag', 'spreadsheetFlag', 'parent_spreadsheetFlag', 'pageLimit', 'allow_save', 'allow_confirm', 'allow_confirm_cancel', 'nextLayer', 'allowAll', 'no_data'));
        $this->render('index');
    }
    
    public function getApproveLog($term_id, $menuId, $selected_year, $search_data){
        $condition = array();
        $condition['menu_id'] = $menuId;
        $condition['bu_term_id'] = $term_id;
        $condition['target_year'] = $selected_year;
        $condition['department_code'] = 0;
        if($search_data['group'] == 'all') {
            $condition['bu_code'] = $search_data['bu'];
            $condition['group_code'] = 0;
        }
        else if($search_data['group'] != 'all'){
            $condition['bu_code'] = $search_data['bu'];
            $condition['group_code'] = $search_data['group'];
        }
        $condition['line_code'] = 0;
        $condition['business_code'] = 0;
        $condition['sub_business_code'] = 0;

        $current_flag = $this->BuApprovedLog->find('first', array(
            'conditions' => $condition,
            'fields' => array('flag')
        ))['BuApprovedLog']['flag'];
        return $current_flag;
        //pr($current_flag);exit;

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
    public function add(){
        
        $menuId = Setting::MENU_ID_LIST['BusinessAnalysis'];
        $target_year = $this->request->data['target_year'];
        $now = date('Y-m-d H:i:s');
        $login_id = $this->Session->read('LOGIN_ID');
        $term_id = $_SESSION['BU_TERM_ID'];
        
        if($this->Session->read('SEARCH_LABOR_COST.target_year')) {
            $search = $this->Session->read('SEARCH_LABOR_COST');
        }
        $bu = $search['bu'];
        $group = $search['group'];
        //pr($this->request->data);exit;
        // post data
        $bu_analysis = json_decode($this->request->data['bu_analysis']);
        $save_type = array_column(json_decode($this->request->data['bu_analysis'], true),'save_type')[0];
        $finalConfirm = array_column(json_decode($this->request->data['bu_analysis'], true),'finalConfirm')[0];
        $bu = array_column(json_decode($this->request->data['bu_analysis'], true),'bu')[0];
        $group = array_column(json_decode($this->request->data['bu_analysis'], true),'group')[0];
        // get db data 
        $layerCodeArr = array();
        $groupCode = array();
        $approve_array = array();
        foreach ($bu_analysis as $key=>$value) {
            $valArr = json_decode(json_encode($value), true);
            $layerCodeArr[] = $valArr['layer_code'];
            if($valArr['type_order'] == 2 || $valArr['type_order'] == 3) {
                if($finalConfirm == 'finalConfirm' && $group == 'all'){
                    if($valArr['type_order'] == 2) $con = array('menu_id' => $menuId,'bu_code'=>$bu, 'group_code' => 0);
                    else if($valArr['type_order'] == 3) $con = array('menu_id' => $menuId, 'bu_code'=>$bu, 'group_code' => $valArr['layer_code']);
                    $approvedFlag = $this->BuApprovedLog->find('all', array(
                        //'fields' => array('bu_code', 'group_code', 'flag'),
                        'conditions' => $con,
                    ));
                    
                    if(sizeOf($approvedFlag) > 0 && $approvedFlag[0]['BuApprovedLog']['flag'] == 1) $groupCode[] = $valArr['layer_code'];
                    if(sizeOf($approvedFlag) > 0 && $approvedFlag[0]['BuApprovedLog']['flag'] == 2) {
                        unset($approvedFlag[0]['BuApprovedLog']['id']);
                        $approve_array[] = $approvedFlag[0]['BuApprovedLog'];
                    }
                    else if(sizeOf($approvedFlag) == 0) $groupCode[] = $valArr['layer_code'];
                }else $groupCode[] = $valArr['layer_code'];
                
            }
            if($key != 0){
                # code...
                $value->bu_term_id = $term_id;
                $value->created_by = $login_id;
                $value->updated_by = $login_id;
                $value->created_date = $now;
                $value->updated_date = $now;
                $formattedData[$valArr['layer_code']] = json_decode(json_encode($value), true);
                //$formattedData[] = json_decode(json_encode($value), true);
            }
        }
        //pr($groupCode);exit;
        $flag = false;
        $alreadyConfrim = false;
        if($finalConfirm == 'finalConfirm'){
            $buTermId = $_SESSION['BU_TERM_ID'];
            //echo $bu.', '.$group;exit;
            $condition = array();
            $condition['target_year'] = $target_year;
            $condition['bu_term_id'] = $buTermId;
            $condition['menu_id'] = Setting::MENU_ID_LIST['BudgetResult'];
            $condition['department_code'] = 0;
            $condition['bu_code'] = $bu;
            if($group == 'all') $condition['group_code'] = 0;
            else $condition['group_code'] = $group;
            $condition['line_code'] = 0;
            $condition['business_code'] = 0;
            $condition['sub_business_code'] = 0;
            $condition['flag'] = 2;
            
            $flag = ($this->BuApprovedLog->find('count', array(
                'conditions' => $condition
            ))>0)? true : false;
            if($flag){
                $condition['menu_id'] = Setting::MENU_ID_LIST['BusinessAnalysis'];
                $flag = ($this->BuApprovedLog->find('count', array(
                    'conditions' => $condition
                ))==0)? true : false;
                if(!$flag) $alreadyConfrim = !$flag;
            }
        }else{
            $flag = true;
        }
        if($flag){
            
            if($save_type == 'merge'){
                $transArr = $this->TransactionPolicy->find('all',
                            array(
                                'fields' => array('layer_code','target_year','expansion', 'maintain', 'withdraw', 'transactionTerm', 'bu_term_id'),
                                'conditions' => array('target_year'=>$target_year, 'bu_term_id' => $term_id, 'layer_code' => $layerCodeArr),
                            )
                        );
                
                if(sizeof($transArr) > 0){
                    foreach($transArr as $key=>$value){
                        $transPolicy[$key] = $value['TransactionPolicy'];
                        $transPolicy[$key]['created_by'] = $login_id;
                        $transPolicy[$key]['updated_by'] = $login_id;
                        $transPolicy[$key]['created_date'] = $now;
                        $transPolicy[$key]['updated_date'] = $now;
                    }
                    $policyArr = array();
                    if(sizeof($formattedData) > 0){
                        foreach($transPolicy as $key=>$value){
                            $policyArr[] = $value['layer_code'];
                            if(isset($formattedData[$value['layer_code']]['expansion']) && $value['expansion'] != $formattedData[$value['layer_code']]['expansion']){
                                $transPolicy[$key]['expansion'] = $formattedData[$value['layer_code']]['expansion'];
                            }
                            if(isset($formattedData[$value['layer_code']]['maintain']) && $value['maintain'] != $formattedData[$value['layer_code']]['maintain']){
                                $transPolicy[$key]['maintain'] = $formattedData[$value['layer_code']]['maintain'];
                            }
                            if(isset($formattedData[$value['layer_code']]['withdraw']) && $value['withdraw'] != $formattedData[$value['layer_code']]['withdraw']){
                                $transPolicy[$key]['withdraw'] = $formattedData[$value['layer_code']]['withdraw'];
                            }
                            if(isset($formattedData[$value['layer_code']]['transactionTerm']) && $value['transactionTerm'] != $formattedData[$value['layer_code']]['transactionTerm']){
                                $transPolicy[$key]['transactionTerm'] = $formattedData[$value['layer_code']]['transactionTerm'];
                            }
                        }
                        $diff = array_diff($layerCodeArr, $policyArr);
                        if(array_diff($layerCodeArr, $policyArr)){
                            foreach($diff as $lKey=>$lValue){
                                $newK = sizeof($transPolicy);
                                if($lValue != ''){
                                    if(!$formattedData[$lValue]['expansion']) $formattedData[$lValue]['expansion'] = 0;
                                    if(!$formattedData[$lValue]['maintain']) $formattedData[$lValue]['maintain'] = 0;
                                    if(!$formattedData[$lValue]['withdraw']) $formattedData[$lValue]['withdraw'] = 0;
                                    if(!$formattedData[$lValue]['transactionTerm']) $formattedData[$lValue]['transactionTerm'] = 0;
                                    $transPolicy[$newK] = $formattedData[$lValue];
                                }
                            }
                            
                        }
                    }
                }else{
                    $i = 0;
                    
                    foreach($formattedData as $key=>$value){
                        $transPolicy[$i] = $value;
                        $transPolicy[$i]['expansion'] = $value['expansion'] ? $value['expansion'] : 0;
                        $transPolicy[$i]['maintain'] = $value['maintain'] ? $value['maintain'] : 0;
                        $transPolicy[$i]['withdraw'] = $value['withdraw'] ? $value['withdraw'] : 0;
                        $transPolicy[$i]['transactionTerm'] = $value['transactionTerm'] ? $value['transactionTerm'] : 0;
                        $i++;
                    }
                }
            }else{
                foreach($formattedData as $key=>$value){
                    $transPolicy[] = $value;
                }
            }
            $this->TransactionPolicy->deleteAll([
                'target_year' => $target_year,
                'bu_term_id' => $term_id,
                'layer_code' => $layerCodeArr
            ], false);
            $save = $this->TransactionPolicy->saveAll($transPolicy);

            if($group != 'all'){
                $conditions = array();
                $conditions['menu_id'] = $menuId;
                $conditions['bu_term_id'] = $term_id;
                $conditions['target_year'] = $target_year;
                $conditions['department_code'] = 0;
                $conditions['bu_code'] = $bu;
                if($group == 'all') $conditions['group_code'] = 0;
                else $conditions['group_code'] = $group;
                $conditions['line_code'] = 0;
                $conditions['business_code'] = 0;
                $conditions['sub_business_code'] = 0;
                
                $approve_id = $this->BuApprovedLog->find('first', array(
                    'conditions' => $conditions,
                    'fields' => 'id'
                ))['BuApprovedLog']['id'];
            }else if($group == 'all'){
                $this->BuApprovedLog->deleteAll([
                        'target_year' => $target_year,
                        'bu_term_id' => $term_id,
                        'bu_code' => $layerCodeArr[1],
                        'menu_id' => $menuId
                    ], false);

            }
            $flag = ($finalConfirm == 'finalConfirm') ?  2 : 1;
            $cancelFlag = 0;
            $login_id = $this->Session->read('LOGIN_ID');
            $approve_data = array();
            if($approve_id) $approve_data['id'] = $approve_id;
            $approve_data['menu_id'] = $menuId;
            $approve_data['bu_term_id'] = $term_id;
            $approve_data['target_year'] = $target_year;
            $approve_data['department_code'] = 0;
            $approve_data['bu_code'] = $bu;
            if($group == 'all') $approve_data['group_code'] = 0;
            else $approve_data['group_code'] = $group;
            $approve_data['line_code'] = 0;
            $approve_data['business_code'] = 0;
            $approve_data['sub_business_code'] = 0;
            $approve_data['flag'] = $flag;
            $approve_data['cancel_flag'] = $cancelFlag;
            if(!$approve_id){
                $approve_data['created_by'] = $login_id;
                $approve_data['updated_by'] = $login_id;
                $approve_data['created_date'] = date("Y-m-d H:i:s");
                $approve_data['updated_date'] = date("Y-m-d H:i:s");
            }else{
                $approve_data['updated_by'] = $login_id;
                $approve_data['updated_date'] = date("Y-m-d H:i:s");
            }
            
            $ApprovedDB = $this->BuApprovedLog->getDataSource();
            $ApprovedDB->begin();
            if($group == 'all'){
                //$approve_array = array();
                foreach($groupCode as $key=>$value){
                    if($value){
                        if($key == 0) {
                            $approve_data['bu_code'] = $value;
                            $approve_data['group_code'] = 0;
                        }else{
                            $approve_data['bu_code'] = $bu;
                            $approve_data['group_code'] = $value;
                        }
                        
                        $approve_array[] = $approve_data;
                    }

                }
                
                $approve_log = $this->BuApprovedLog->saveAll($approve_array);
            }else  $approve_log = $this->BuApprovedLog->saveAll($approve_data);

            $ApprovedDB->commit();
            if($save && $approve_log){
                if($finalConfirm == 'finalConfirm'){
                    $this->Flash->success(parent::getSuccessMsg('SS035'), array(
                        'key' => 'bu_success'
                    ));
                }else{
                    $this->Flash->success(parent::getSuccessMsg('SS001'), array(
                        'key' => 'bu_success'
                    ));
                }
                
            }else{
                $this->Flash->error(parent::getErrorMsg('SE003'), array(
                    'key' => 'bu_error'
                ));
            }
        }else{
            if($alreadyConfrim){
                $this->Flash->error(parent::getErrorMsg('SE162'), array(
                    'key' => 'bu_error'
                ));
            }else{
                $this->Flash->error(parent::getErrorMsg('SE161'), array(
                    'key' => 'bu_error'
                ));
            }
            
        }
        $this->redirect(['action'=>'index']);
    }
    public function checkSaveMerge(){
        parent::checkAjaxRequest($this);
        if ($this->request->is('post')) {
            $target_year = $this->request->data['target_year'];
            $layerCode = $this->request->data['layerCode'];
            $buTermId = $_SESSION['BU_TERM_ID'];
            $row_count = ($this->TransactionPolicy->find('count', array(
                'conditions' => array(
                    'target_year' => $target_year,
                    'bu_term_id' => $buTermId,
                    'layer_code' => $layerCode
                )
            ))>0)? true : false;
            echo json_encode($row_count);
        }
    }
    public function DownloadSpreadsheet(){
        $this->index();
    }
    public function spreadsheetExcel($file_name, $prepare_layer, $accountNames, $resultYear, $search_data, $hyoka_name_arr, $showRow, $s_showRow){
        $first3Years = array_slice($resultYear, 0, 3, true);
        $last3Years = array_slice($resultYear, -3, 3, true);
        if($search_data['group'] != 'all') $type_order = 3;
        else $type_order = 2;
        $header_color = 'E5FFFF';
        
        $PHPExcel = $this->PhpExcel;
        
        $objPHPExcel = $PHPExcel->createWorksheet()->setDefaultFont('Cambria', 12);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel ->getActiveSheet()->setShowGridlines(false);
        $objPHPExcel ->setActiveSheetIndex(0);
        $objPHPExcel ->getActiveSheet()->setTitle($file_name);

        $sheet = $PHPExcel->getActiveSheet();
        $BStyle = array(
            'borders' => array(
              'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
            )
          );
        $aligncenter = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );
        $alignright = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,

                )
        );
        $border_top = array(
            'borders' => array(
                'top' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                )
             )
        );
        $border_left = array(
            'borders' => array(
                'left' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                )
             )
        );
        $border_bottom_none = array(
            'borders' => array(
                'left' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                ),
                // 'right' => array(
                //   'style' => PHPExcel_Style_Border::BORDER_THIN,
                // ),
                'top' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                )
             )
          );
        $sheet->setCellValue('G1', '集計表');
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        // 1st table
        #header
        $startRow = 3;
        $sheet->setCellValue('A'.$startRow, __("ビジネス"));
        $sheet->mergeCells('A'.$startRow.':G'.$startRow);
        $sheet->setCellValue('H'.$startRow, __("収益性（百万円）"));
        $sheet->mergeCells('H'.$startRow.':Y'.$startRow);
        $sheet->setCellValue('Z'.$startRow, __("成長性"));
        $sheet->setCellValue('AA'.$startRow, __("グローバルケミカルへの貢献度"));
        $sheet->mergeCells('AA'.$startRow.':AC'.$startRow);
        $sheet->setCellValue('AD'.$startRow, __("生産性"));
        $sheet->mergeCells('AD'.$startRow.':AI'.$startRow);
        $sheet->getStyle('A'.$startRow.':AI'.$startRow)->applyFromArray($BStyle);
        $sheet->getStyle('A'.$startRow.':AI'.$startRow)->applyFromArray($aligncenter);
        $sheet->getStyle('A'.$startRow.':AI'.$startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        
        $startRow = $startRow + 1;
        $sheet->setCellValue('A'.$startRow, __("中計括り"));
        $sheet->mergeCells('A4:D5');
        $sheet->setCellValue('E'.$startRow, __("販売先or商品"));
        $sheet->setCellValue('F'.$startRow, __("商品or販売先"));
        $sheet->setCellValue('G'.$startRow, __("Form"));
        $sheet->mergeCells('E4:E5');
        $sheet->mergeCells('F4:F5');
        $sheet->mergeCells('G4:G5');
        //$sheet->mergeCells('B4:B5');
        //$sheet->mergeCells('C4:C5');
        //$sheet->mergeCells('D4:D5');
        $sheet->getStyle('A'.$startRow.':AI5')->applyFromArray($BStyle);
        $sheet->getStyle('A'.$startRow.':AI5')->applyFromArray($border_bottom_none);
        $sheet->getStyle('A'.$startRow.':AI5')->applyFromArray($aligncenter);
        
        $sheet->setCellValue('H'.$startRow, $accountNames[0].'/'.$accountNames[1]);
        $sheet->mergeCells('H'.$startRow.':S'.$startRow);
        $HSColumn = array('H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S');
        $HSIndex = 0;
        foreach($resultYear as $key=>$value){
            $sheet->setCellValue($HSColumn[$HSIndex].'5', $value);
            $sheet->mergeCells($HSColumn[$HSIndex].'5:'.$HSColumn[$HSIndex+1].'5');
            $HSIndex += 2;
        }
        $sheet->setCellValue('T'.$startRow, $accountNames[2]);
        $sheet->mergeCells('T'.$startRow.':Y'.$startRow);
        $TYColumn = array('T', 'U', 'V', 'W', 'X', 'Y');
        foreach($resultYear as $key=>$value){
            $sheet->setCellValue($TYColumn[$key].'5', $value);
        }
        $sheet->setCellValue('Z'.$startRow, __("売総成長率(%)"));
        $sheet->setCellValue('AA'.$startRow, $accountNames[3]);
        $sheet->mergeCells('AA'.$startRow.':AC'.$startRow);
        $sheet->setCellValue('Z5', __("売総成長率(%)"));
        $AAColumn = array('AA', 'AB', 'AC');
        
        foreach($last3Years as $key=>$value){
            $sheet->setCellValue($AAColumn[$key-3].'5', $value);
        }

        $sheet->setCellValue('AD'.$startRow, ($search_data['target_year']-1).__("人員 (人）"));
        $sheet->mergeCells('AD'.$startRow.':AF'.$startRow);
        $sheet->setCellValue('AG'.$startRow, $search_data['target_year'].__("予算人員 (人）"));
        $sheet->mergeCells('AG'.$startRow.':AI'.$startRow);
        $sheet->setCellValue('AD5', __("経営・管理"));
        $sheet->setCellValue('AE5', __("営業 "));
        $sheet->setCellValue('AF5', __("ｵﾍﾟﾚｰｼｮﾝ"));
        $sheet->setCellValue('AG5', __("経営・管理"));
        $sheet->setCellValue('AH5', __("営業 "));
        $sheet->setCellValue('AI5', __("ｵﾍﾟﾚｰｼｮﾝ"));
        $sheet->getStyle('A3:AI5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $row = 6;
        $lColumn = array('A', 'B', 'C', 'D');
        $accTotal = array();
        $acc2Total = array();
        $acc3Total = array();
        $emp = array(); 
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(5);
        $sheet->getColumnDimension('D')->setWidth(25);
        foreach($prepare_layer[0] as $key=>$value){
            
            //pr($value);
            $topAmount = $value['amount'];
            $topEmp = $value['emp'];
            $tfirstAmount = $topAmount[$accountNames[0]][$first3Years[0]] + $topAmount[$accountNames[0]][$first3Years[1]] + $topAmount[$accountNames[0]][$first3Years[2]];
            $tlastAmount = $topAmount[$accountNames[0]][$last3Years[3]] + $topAmount[$accountNames[0]][$last3Years[4]] + $topAmount[$accountNames[0]][$last3Years[5]];
            $tresAmount = $tlastAmount/$tfirstAmount;
            foreach($resultYear as $yValue){
                $accTotal[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[0]][$yValue] : 0;
                $acc2Total[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[2]][$yValue] : 0;
                
            }
            foreach($last3Years as $yValue){
                $acc3Total[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[3]][$yValue] : 0;
            }
            foreach($topEmp as $eKey=>$eValue){
                $emp[$eKey][$search_data['target_year'] - 1] += ($value['type_order'] == $type_order) ? $eValue[$search_data['target_year'] - 1] : 0;
                $emp[$eKey][$search_data['target_year']] += ($value['type_order'] == $type_order) ? $eValue[$search_data['target_year']] : 0;

            }
            
            if($value['rowLevel'] == 4) $excel_row = 0;
            else if($value['rowLevel'] == 3) $excel_row = 1;
            else if($value['rowLevel'] == 2) $excel_row = 2;
            else if($value['rowLevel'] == 1) $excel_row = 3;
            $rowDimension[$row] = $excel_row;
            $preIndex = $indexColumn;
            if($value['type_order'] > 4) {
                $indexColumn = $lColumn[$value['type_order']-3];
                
                $index = $value['type_order']-3;
                $pIndex = $index;
            }else {
                $indexColumn = $lColumn[$value['type_order']-2];
                $index = $value['type_order']-2;
                $pIndex = $index;
            }
            
            $sheet->getStyle($indexColumn.$row)->applyFromArray($border_bottom_none);
            for($i = $index; $i < 4; $i++){
                $sheet->getStyle($lColumn[$i].$row)->applyFromArray($border_top);
            }
            for($i = $pIndex; $i >= 1; $i--){
                $sheet->getStyle($lColumn[$i].$row)->applyFromArray($border_left);
            }
            
            // $sheet->setCellValue($indexColumn.$row, $value['layer_code'].'/'.$value['name_jp']);
            $sheet->setCellValue($indexColumn.$row, $value['name_jp']);
            $sheet->setCellValue('E'.$row, $value['item_1']);
            $sheet->setCellValue('F'.$row, $value['item_2']);
            $sheet->setCellValue('G'.$row, $value['form']);
            $sheet->setCellValue('H'.$row, $topAmount[$accountNames[0]][$resultYear[0]] ? ($topAmount[$accountNames[0]][$resultYear[0]]) : 0);
            // $sheet->setCellValue('I'.$row, $topAmount[$accountNames[1]][$resultYear[0]].'%');
            $sheet->setCellValue('I'.$row, $topAmount[$accountNames[1]][$resultYear[0]]/100);

            $sheet->setCellValue('J'.$row, $topAmount[$accountNames[0]][$resultYear[1]] ? ($topAmount[$accountNames[0]][$resultYear[1]]) : 0);
            // $sheet->setCellValue('K'.$row, $topAmount[$accountNames[1]][$resultYear[1]].'%');
            $sheet->setCellValue('K'.$row, $topAmount[$accountNames[1]][$resultYear[1]]/100);

            $sheet->setCellValue('L'.$row, $topAmount[$accountNames[0]][$resultYear[2]] ? ($topAmount[$accountNames[0]][$resultYear[2]]) : 0);
            // $sheet->setCellValue('M'.$row, $topAmount[$accountNames[1]][$resultYear[2]].'%');
            $sheet->setCellValue('M'.$row, $topAmount[$accountNames[1]][$resultYear[2]]/100);

            $sheet->setCellValue('N'.$row, $topAmount[$accountNames[0]][$resultYear[3]] ? ($topAmount[$accountNames[0]][$resultYear[3]]) : 0);
            // $sheet->setCellValue('O'.$row, $topAmount[$accountNames[1]][$resultYear[3]].'%');
            $sheet->setCellValue('O'.$row, $topAmount[$accountNames[1]][$resultYear[3]]/100);

            $sheet->setCellValue('P'.$row, $topAmount[$accountNames[0]][$resultYear[4]] ? ($topAmount[$accountNames[0]][$resultYear[4]]) : 0);
            // $sheet->setCellValue('Q'.$row, $topAmount[$accountNames[1]][$resultYear[4]].'%');
            $sheet->setCellValue('Q'.$row, $topAmount[$accountNames[1]][$resultYear[4]]/100);

            $sheet->setCellValue('R'.$row, $topAmount[$accountNames[0]][$resultYear[5]] ? ($topAmount[$accountNames[0]][$resultYear[5]]) : 0);
            // $sheet->setCellValue('S'.$row, $topAmount[$accountNames[1]][$resultYear[5]].'%');
            $sheet->setCellValue('S'.$row, $topAmount[$accountNames[1]][$resultYear[5]]/100);
            $tyColumn = array('T', 'U', 'V', 'W', 'X', 'Y');
            foreach($resultYear as $key=>$yVlaue){
                $sheet->setCellValue($tyColumn[$key].$row, $topAmount[$accountNames[2]][$yVlaue] ? $topAmount[$accountNames[2]][$yVlaue] : 0);
                $sheet->getStyle($tyColumn[$key].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
            }
            $sheet->setCellValue('Z'.$row, (is_nan($tresAmount*100) || $tresAmount == INF) ? '0' : $tresAmount);
            #for number format
            $no_decimal[$row] = array('H', 'J', 'L', 'N', 'P', 'R');
            $decimal[$row] = array('I', 'K', 'M', 'O', 'Q', 'S', 'Z');

            $AAtoACColumn = array('AA', 'AB', 'AC'); 
            $i = 0;
            foreach($last3Years as $key=>$lValue){
                $sheet->setCellValue($AAtoACColumn[$i].$row, $topAmount[$accountNames[3]][$lValue]);
                $sheet->getStyle($AAtoACColumn[$i].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                $i++;
            }
            $sheet->setCellValue('AD'.$row, $topEmp['経営・管理'][$search_data['target_year']-1] ? ($topEmp['経営・管理'][$search_data['target_year']-1]) : '0.0');
            $sheet->setCellValue('AE'.$row, $topEmp['営業'][$search_data['target_year']-1] ? ($topEmp['営業'][$search_data['target_year']-1]): '0.0');
            $sheet->setCellValue('AF'.$row, $topEmp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']-1] ? ($topEmp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']-1]): '0.0');
            $sheet->setCellValue('AG'.$row, $topEmp['経営・管理'][$search_data['target_year']] ? ($topEmp['経営・管理'][$search_data['target_year']]): '0.0');
            $sheet->setCellValue('AH'.$row, $topEmp['営業'][$search_data['target_year']] ? ($topEmp['営業'][$search_data['target_year']]): '0.0');
            $sheet->setCellValue('AI'.$row, $topEmp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']] ? ($topEmp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']]): '0.0');
            $sheet->getStyle('AD'.$row.':AI'.$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
            $sheet->getStyle('E'.$row.':AI'.$row)->applyFromArray($BStyle);
            $row++;
        }
        $this->getNumberFormatExcel($decimal, $no_decimal, $sheet);
        // Total
        $sheet->setCellValue('A'.$row, __("合計"));
        $sheet->mergeCells('A'.$row.':G'.$row);
        $rHSColumn = array('H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S');
        $i = 0;
        foreach($accTotal as $key=>$value){
            $sheet->setCellValue($rHSColumn[$i].$row, $value);
            $sheet->mergeCells($rHSColumn[$i].$row.':'.$rHSColumn[$i+1].$row);
            $sheet->getStyle($rHSColumn[$i].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
            $i += 2;
        }
        $rTYColumn = array('T', 'U', 'V', 'W', 'X', 'Y');
        $i = 0;
        foreach($acc2Total as $value){
            $sheet->setCellValue($rTYColumn[$i].$row, $value);
            $sheet->getStyle($rTYColumn[$i].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
            $i++;

        }
        
        $sheet->getStyle('A'.$row.':AI'.$row)->applyFromArray($BStyle);
        $sheet->getStyle('A'.$row.':G'.$row)->applyFromArray($aligncenter);
        $sheet->setCellValue('Z'.$row, '');
        $rAAtoACColumn = array('AA', 'AB', 'AC'); 
        $i = 0;
        foreach($acc3Total as $value){
            $sheet->setCellValue($rAAtoACColumn[$i].$row, $value);
            $sheet->getStyle($rAAtoACColumn[$i].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
            $i++;
        }
        $sheet->setCellValue('AD'.$row, ($emp['経営・管理'][$search_data['target_year']-1]));
        $sheet->setCellValue('AE'.$row, ($emp['営業'][$search_data['target_year']-1]));
        $sheet->setCellValue('AF'.$row, ($emp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']-1]));
        $sheet->setCellValue('AG'.$row, ($emp['経営・管理'][$search_data['target_year']]));
        $sheet->setCellValue('AH'.$row, ($emp['営業'][$search_data['target_year']]));
        $sheet->setCellValue('AI'.$row, ($emp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']]));
        $sheet->getStyle('AD'.$row.':AI'.$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
        if($showRow == 'true') $showRow = false;
        else $showRow = true;
        foreach ($rowDimension as $key_column => $level) {
            $objPHPExcel->getActiveSheet()
                            ->getRowDimension($key_column)
                                ->setOutlineLevel($level)
                                ->setVisible($showRow)
                                ->setCollapsed(true);
        }
        $sheet->getStyle('H4:AI'.$row)->applyFromArray($alignright);
        
        // 2nd table
        $resultYear1 = range($search_data['target_year']-1, $search_data['target_year']+2);
        $se_startRow = ($row + 3);
        $sheet->setCellValue('A'.$se_startRow, __("ビジネス"));
        $sheet->mergeCells('A'.$se_startRow.':G'.$se_startRow);
        $sheet->getStyle('A'.$se_startRow.':G'.$se_startRow)->applyFromArray($BStyle);
        $sheet->setCellValue('H'.$se_startRow, __("資金効率（百万円）"));
        $sheet->mergeCells('H'.$se_startRow.':Z'.$se_startRow);
        $sheet->getStyle('H'.$se_startRow.':Z'.$se_startRow)->applyFromArray($BStyle);
        $sheet->setCellValue('AA'.$se_startRow, __("取引意義【「＋」＝△、「－」＝▲】"));
        $sheet->mergeCells('AA'.$se_startRow.':AD'.$se_startRow);
        $sheet->getStyle('AA'.$se_startRow.':AD'.$se_startRow)->applyFromArray($BStyle);
        $sheet->setCellValue('AE'.$se_startRow, __("取引リスク評価【0～4の５段階】"));
        //$sheet->mergeCells('AE'.$se_startRow.':AI'.$se_startRow);
        //$sheet->getStyle('AE'.$se_startRow.':AI'.$se_startRow)->applyFromArray($BStyle);
        $sheet->getStyle('A'.$se_startRow.':AI'.$se_startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->getStyle('A'.$se_startRow.':AI'.$se_startRow)->applyFromArray($aligncenter);
        // $sheet->getStyle('A'.$se_startRow.':AK'.($se_startRow + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        // $sheet->getStyle('A'.$se_startRow.':AK'.$se_startRow)->applyFromArray($aligncenter);
        // $sheet->getStyle('A'.$se_startRow.':AK'.$se_startRow)->applyFromArray($BStyle);
        // $sheet->getStyle('A'.$se_startRow.':AK'.$se_startRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $se_startRow = $se_startRow + 1;
        //$sheet->getStyle('A'.$se_startRow.':AK'.($se_startRow + 1))->applyFromArray($BStyle);
        //$sheet->getStyle('A'.$se_startRow.':AK'.($se_startRow + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->setCellValue('A'.$se_startRow, __("中計括り"));
        $sheet->mergeCells('A'.$se_startRow.':D'.($se_startRow + 1));
        $sheet->setCellValue('E'.$se_startRow, __("販売先or商品"));
        $sheet->setCellValue('F'.$se_startRow, __("商品or販売先"));
        $sheet->setCellValue('G'.$se_startRow, __("Form"));
        $sheet->mergeCells('E'.$se_startRow.':E'.($se_startRow + 1));
        $sheet->mergeCells('F'.$se_startRow.':F'.($se_startRow + 1));
        $sheet->mergeCells('G'.$se_startRow.':G'.($se_startRow + 1));
        $sheet->setCellValue('H'.$se_startRow, $accountNames[4]);
        $sheet->setCellValue('L'.$se_startRow, $accountNames[5]);
        $sheet->setCellValue('P'.$se_startRow, $accountNames[6]);
        $sheet->setCellValue('T'.$se_startRow, $accountNames[7]);
        $sheet->setCellValue('X'.$se_startRow, __("ファクタリング考慮後ROIC (%)"));
        $sheet->setCellValue('AA'.$se_startRow, __("シナジー（百万円）"));
        $sheet->mergeCells('AA'.$se_startRow.':AA'.($se_startRow+1) );
        $sheet->setCellValue('AB'.$se_startRow, __("商品競争力"));
        $sheet->mergeCells('AB'.$se_startRow.':AB'.($se_startRow+1) );
        $sheet->setCellValue('AC'.$se_startRow, __("最終製品"));
        $sheet->mergeCells('AC'.$se_startRow.':AD'.$se_startRow);
        $sheet->setCellValue('AC'.($se_startRow+1), __("競争力"));
        $sheet->setCellValue('AD'.($se_startRow+1), __("成長性"));


        $sheet->mergeCells('H'.$se_startRow.':K'.$se_startRow );
        $sheet->mergeCells('L'.$se_startRow.':O'.$se_startRow);
        $sheet->mergeCells('P'.$se_startRow.':S'.$se_startRow);
        $sheet->mergeCells('T'.$se_startRow.':W'.$se_startRow );
        $sheet->mergeCells('X'.$se_startRow.':Z'.$se_startRow);
        $HZColumn = array('H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $HZIndex = 0;
        //echo $se_startRow;
        foreach($resultYear1 as $key=>$value){
            $sheet->setCellValue($HZColumn[$HZIndex].($se_startRow+1), $value);
            $HZColumn[$HZIndex].($se_startRow+1);
            $HZIndex++;
        }
        foreach($resultYear1 as $key=>$value){
            $sheet->setCellValue($HZColumn[$HZIndex].($se_startRow+1), $value);
            $HZIndex++;
        }
        foreach($resultYear1 as $key=>$value){
            $sheet->setCellValue($HZColumn[$HZIndex].($se_startRow+1), $value);
            $HZIndex++;
        }
        foreach($resultYear1 as $key=>$value){
            $sheet->setCellValue($HZColumn[$HZIndex].($se_startRow+1), $value);
            $HZIndex++;
        }
        $HZIndex--;
        foreach($resultYear1 as $key=>$value){
            if($key != 0)
            $sheet->setCellValue($HZColumn[$HZIndex].($se_startRow+1), $value);
            $HZIndex++;
        }
        
        $sheet->setCellValue('AE'.($se_startRow+1), __("評価（Ａ～Ｅ、NA）のうちA・B・の該否"));
        $AEColumn = array('E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O');
        if(sizeof($hyoka_name_arr) == 0){
            $i = 0;
            $sheet->mergeCells('A'.$AEColumn[0].$se_startRow.':AE'.($se_startRow + 1));
            $i++;
        }else{
            $i = 0;
            foreach($hyoka_name_arr as $value){
                if($i == 0) $start = $AEColumn[$i];
                $sheet->setCellValue('A'.$AEColumn[$i].($se_startRow), $value);
                $sheet->mergeCells('A'.$AEColumn[$i].($se_startRow).':A'.$AEColumn[$i].($se_startRow + 1));
                if($i == (sizeof($hyoka_name_arr) - 1)) {
                    $sheet->mergeCells('A'.$start.($se_startRow-1).':A'.$AEColumn[$i].($se_startRow-1));
                    $sheet->getStyle('A'.$start.($se_startRow-1).':A'.$AEColumn[$i].($se_startRow-1))->applyFromArray($BStyle);
                }
                $i++;
            }
        }
        $sheet->setCellValue('A'.$AEColumn[$i].($se_startRow-1), __("CSR上のリスク懸念"));
        $sheet->mergeCells('A'.$AEColumn[$i].($se_startRow-1).':A'.$AEColumn[$i].($se_startRow-1));
        $sheet->getStyle('A'.$AEColumn[$i].($se_startRow-1).':A'.$AEColumn[$i].($se_startRow-1))->applyFromArray($aligncenter);
        $sheet->getStyle('A'.$AEColumn[$i].($se_startRow-1).':A'.$AEColumn[$i].($se_startRow-1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->getStyle('A'.$AEColumn[$i].($se_startRow-1).':A'.$AEColumn[$i].($se_startRow-1))->applyFromArray($BStyle);

        $sheet->setCellValue('A'.$AEColumn[$i].($se_startRow), __("評価（Ａ～Ｅ、NA）のうちA・B・の該否"));
        $sheet->mergeCells('A'.$AEColumn[$i].$se_startRow.':A'.$AEColumn[$i].($se_startRow+1));
        $i++;
        $sheet->setCellValue('A'.$AEColumn[$i].($se_startRow-1), __("取引方針【〇】"));
        $sheet->mergeCells('A'.$AEColumn[$i].($se_startRow-1).':A'.$AEColumn[$i+3].($se_startRow-1));
        $sheet->getStyle('A'.$AEColumn[$i].($se_startRow-1).':A'.$AEColumn[$i+3].($se_startRow-1))->applyFromArray($aligncenter);
        $sheet->getStyle('A'.$AEColumn[$i].($se_startRow-1).':A'.$AEColumn[$i+3].($se_startRow-1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->getStyle('A'.$AEColumn[$i].($se_startRow-1).':A'.$AEColumn[$i+3].($se_startRow-1))->applyFromArray($BStyle);

        $sheet->setCellValue('A'.$AEColumn[$i].($se_startRow), __("拡　大"));
        $sheet->mergeCells('A'.$AEColumn[$i].$se_startRow.':A'.$AEColumn[$i].($se_startRow+1));
        $sheet->setCellValue('A'.$AEColumn[$i+1].($se_startRow), __("維　持"));
        $sheet->mergeCells('A'.$AEColumn[$i+1].$se_startRow.':A'.$AEColumn[$i+1].($se_startRow+1));
        $sheet->setCellValue('A'.$AEColumn[$i+2].($se_startRow), __("縮小・撤退"));
        $sheet->mergeCells('A'.$AEColumn[$i+2].$se_startRow.':A'.$AEColumn[$i+2].($se_startRow+1));
        $sheet->setCellValue('A'.$AEColumn[$i+3].($se_startRow), __("左記方針の取進め条件"));
        $sheet->mergeCells('A'.$AEColumn[$i+3].$se_startRow.':A'.$AEColumn[$i+3].($se_startRow+1));
        
        $sheet->getStyle('A'.$se_startRow.':A'.$AEColumn[$i+3].($se_startRow+1))->applyFromArray($aligncenter);
        $sheet->getStyle('A'.$se_startRow.':A'.$AEColumn[$i+3].($se_startRow+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($header_color);
        $sheet->getStyle('A'.$se_startRow.':A'.$AEColumn[$i+3].($se_startRow+1))->applyFromArray($BStyle);
        $row = $se_startRow + 2;
        $acc4Total = array();
        $acc5Total = array();
        $acc6Total = array();
        foreach($prepare_layer[0] as $key=>$value){
            $topAmount = $value['amount'];
            foreach($resultYear1 as $yValue){
                $acc4Total[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[4]][$yValue] : 0;
                $acc5Total[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[5]][$yValue] : 0;
                $acc6Total[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[6]][$yValue] : 0;
                
            }
            if($value['rowLevel'] == 4) $excel_row = 0;
            else if($value['rowLevel'] == 3) $excel_row = 1;
            else if($value['rowLevel'] == 2) $excel_row = 2;
            else if($value['rowLevel'] == 1) $excel_row = 3;
            $s_rowDimension[$row] = $excel_row;
            $preIndex = $indexColumn;
            if($value['type_order'] > 4) {
                $indexColumn = $lColumn[$value['type_order']-3];
                
                $index = $value['type_order']-3;
                $pIndex = $index;
            }else {
                $indexColumn = $lColumn[$value['type_order']-2];
                $index = $value['type_order']-2;
                $pIndex = $index;
            }
            
            $sheet->getStyle($indexColumn.$row)->applyFromArray($border_bottom_none);
            for($i = $index; $i < 4; $i++){
                $sheet->getStyle($lColumn[$i].$row)->applyFromArray($border_top);
            }
            for($i = $pIndex; $i >= 1; $i--){
                $sheet->getStyle($lColumn[$i].$row)->applyFromArray($border_left);
            }
            
            // $sheet->setCellValue($indexColumn.$row, $value['layer_code'].'/'.$value['name_jp']);
            $sheet->setCellValue($indexColumn.$row, $value['name_jp']);
            $sheet->setCellValue('E'.$row, $value['item_1']);
            $sheet->setCellValue('F'.$row, $value['item_2']);
            $sheet->setCellValue('G'.$row, $value['form']);
            $HZIndex = 0;
            foreach($resultYear1 as $yKey=> $yValue){
                $sheet->setCellValue($HZColumn[$HZIndex].$row, $topAmount[$accountNames[4]][$yValue] ? ($topAmount[$accountNames[4]][$yValue]) : 0);
                $sheet->getStyle($HZColumn[$HZIndex].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                $HZIndex++;
            }
            foreach($resultYear1 as $yKey=>$yValue){
                $sheet->setCellValue($HZColumn[$HZIndex].$row, $topAmount[$accountNames[5]][$yValue] ? ($topAmount[$accountNames[5]][$yValue]) : 0);
                $sheet->getStyle($HZColumn[$HZIndex].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                $HZIndex++;
            }
            foreach($resultYear1 as $yKey=>$yValue){
                $sheet->setCellValue($HZColumn[$HZIndex].$row, $topAmount[$accountNames[6]][$yValue] ? ($topAmount[$accountNames[6]][$yValue]) : 0);
                $sheet->getStyle($HZColumn[$HZIndex].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                $HZIndex++;
            }
            foreach($resultYear1 as $yKey=>$yValue){
                $sheet->setCellValue($HZColumn[$HZIndex].$row, $topAmount[$accountNames[7]][$yValue] ? ($topAmount[$accountNames[7]][$yValue]) : 0);
                $sheet->getStyle($HZColumn[$HZIndex].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                $HZIndex++;
            }
            $HZIndex--;
            foreach($resultYear1 as $yKey=>$yValue){
                if($yKey != 0)
                $sheet->setCellValue($HZColumn[$HZIndex].$row, $topAmount[$accountNames[7]][$yValue] ? ($topAmount[$accountNames[7]][$yValue]) : 0);
                $sheet->getStyle($HZColumn[$HZIndex].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
                $HZIndex++;
            }
            
            if($value['BudgetComp']['final_total']['add_triangle']) {
                $deli = $value['BudgetComp']['final_total']['deli_product'].'△'.$value['BudgetComp']['final_total']['deli_chg_product'];
                $indus = $value['BudgetComp']['final_total']['indus_fproduct'].'△'.$value['BudgetComp']['final_total']['indus_chg_fproduct'];
                $potential = '△'.$value['BudgetComp']['final_total']['final_potential'];
            }else{
                $deli = $value['BudgetComp']['final_total']['deli_product'].$value['BudgetComp']['final_total']['deli_chg_product'];
                $indus = $value['BudgetComp']['final_total']['indus_fproduct'].$value['BudgetComp']['final_total']['indus_chg_fproduct'];
                $potential = $value['BudgetComp']['final_total']['final_potential'];
            }
            $sheet->setCellValue('AA'.$row, $value['sgns']);
            $sheet->setCellValue('AB'.$row, $deli);
            $sheet->setCellValue('AC'.$row, $indus);
            $sheet->setCellValue('AD'.$row, $potential);
            
            if(sizeof($hyoka_name_arr) == 0){
                $i = 0;
                $sheet->setCellValue('AE'.$row, '-');
                $i++;
            }else{
                $i = 0;
                foreach($hyoka_name_arr as $hVlaue){
                    if($i == 0) $start = $AEColumn[$i];
                    if($value['BudgetHyoka'][$hVlaue] == 0) $b_hyoka = '-';
                    else $b_hyoka = $value['BudgetHyoka'][$hVlaue];
                    $sheet->setCellValue('A'.$AEColumn[$i].$row, $b_hyoka);
                    //$sheet->setCellValue('A'.$AEColumn[$i].$row, $value['BudgetHyoka'][$hVlaue] ? $value['BudgetHyoka'][$hVlaue] : '-');
                    $i++;
                }
            }
            $sheet->setCellValue('A'.$AEColumn[$i].$row, $value['BudgetHyoka']['CSR'] ? $value['BudgetHyoka']['CSR'] : '-');
            $expansion = '**選択**';
            $maintain = '**選択**'; 
            $withdraw = '**選択**';
            $transactionTerm = '**選択**';
            if($value['TransactionPolicy']['expansion'] == '1') $expansion = '〇';
            if($value['TransactionPolicy']['maintain'] == '1') $maintain = '〇';
            if($value['TransactionPolicy']['withdraw'] == '1') $withdraw = '〇';
            if($value['TransactionPolicy']['transactionTerm'] == '1') $transactionTerm = '〇';
            $sheet->setCellValue('A'.$AEColumn[$i+1].$row, $expansion);
            $sheet->setCellValue('A'.$AEColumn[$i+2].$row, $maintain);
            $sheet->setCellValue('A'.$AEColumn[$i+3].$row, $withdraw);
            $sheet->setCellValue('A'.$AEColumn[$i+4].$row, $transactionTerm);
            $sheet->getStyle('H'.$row.':AI'.$row)->applyFromArray($alignright);
            $sheet->getStyle('E'.$row.':A'.$AEColumn[$i+4].$row)->applyFromArray($BStyle);

            $row++;
        }

        // total
        $sheet->setCellValue('A'.$row, __("合計"));
        $sheet->mergeCells('A'.$row.':G'.$row);
        $i = 0;
        foreach($acc4Total as $key=>$value){
            $sheet->setCellValue($HZColumn[$i].$row, ($value));
            $sheet->getStyle($HZColumn[$i].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
            $i++;
        }
        foreach($acc5Total as $key=>$value){
            $sheet->setCellValue($HZColumn[$i].$row, ($value));
            $sheet->getStyle($HZColumn[$i].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
            $i++;
        }
        foreach($acc6Total as $key=>$value){
            $sheet->setCellValue($HZColumn[$i].$row, ($value));
            $sheet->getStyle($HZColumn[$i].$row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
            $i++;
        }
        $sheet->setCellValue($HZColumn[$i].$row, '');$i++;
        $sheet->setCellValue($HZColumn[$i].$row, '');$i++;
        $sheet->setCellValue($HZColumn[$i].$row, '');$i++;
        $sheet->setCellValue($HZColumn[$i].$row, '');$i++;
        $sheet->setCellValue($HZColumn[$i].$row, '');$i++;
        $sheet->setCellValue($HZColumn[$i].$row, '');$i++;
        $sheet->setCellValue($HZColumn[$i].$row, '');$i++;

        $sheet->setCellValue('AA'.$row, '');$i++;
        $sheet->setCellValue('AB'.$row, '');$i++;
        $sheet->setCellValue('AC'.$row, '');$i++;
        $sheet->setCellValue('AD'.$row, '');$i++;
        if(sizeof($hyoka_name_arr) == 0){
            $i = 0;
            $sheet->setCellValue('AE'.$row, '');
            $i++;
        }else{
            $i = 0;
            foreach($hyoka_name_arr as $hVlaue){
                if($i == 0) $start = $AEColumn[$i];
                $sheet->setCellValue('A'.$AEColumn[$i].$row, '');
                $i++;
            }
        }
        $sheet->setCellValue('A'.$AEColumn[$i].$row, '');$i++;
        $sheet->setCellValue('A'.$AEColumn[$i].$row, '');$i++;
        $sheet->setCellValue('A'.$AEColumn[$i].$row, '');$i++;
        $sheet->setCellValue('A'.$AEColumn[$i].$row, '');$i++;
        $sheet->getStyle('A'.$row.':A'.$AEColumn[$i].$row)->applyFromArray($BStyle);
        $sheet->getStyle('A'.$row)->applyFromArray($aligncenter);
        if($s_showRow == 'true') $s_showRow = false;
        else $s_showRow = true;
        foreach ($s_rowDimension as $key_column => $level) {
            $objPHPExcel->getActiveSheet()
                            ->getRowDimension($key_column)
                                ->setOutlineLevel($level)
                                ->setVisible($s_showRow)
                                ->setCollapsed(true);
        }
        if($search_data['group'] != 'all') $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setVisible(false);

        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');

        // Encode the filename using urlencode
        $encodedFileName = urlencode('集計表_' . $file_name . '.xlsx');
        header('Content-Disposition: attachment; filename="' . $encodedFileName . '"; charset=utf-8');

        $PHPExcel->output($encodedFileName);
        $this->autoLayout = false;
    }
    public function cancelConfirm(){
       
        $menuId = Setting::MENU_ID_LIST['BusinessAnalysis'];
        $target_year = $this->request->data['target_year'];
        $term_id = $_SESSION['BU_TERM_ID'];
        $bu = $this->request->data['bu'];
        $group = $this->request->data['group'];
        $conditions = array();
        $conditions['menu_id'] = $menuId;
        $conditions['bu_term_id'] = $term_id;
        $conditions['target_year'] = $target_year;
        $conditions['department_code'] = 0;
        $conditions['bu_code'] = $bu;
        if($group == 'all') $conditions['group_code'] = 0;
        else $conditions['group_code'] = $group;
        $conditions['line_code'] = 0;
        $conditions['business_code'] = 0;
        $conditions['sub_business_code'] = 0;

        $dupCondition = $conditions;
        $dupCondition['flag'] = 2;

        $flag = ($this->BuApprovedLog->find('count', array(
            'conditions' => $dupCondition
        ))>0)? true : false;
        if($flag){
            $bu_analysis = json_decode($this->request->data['bu_analysis']);
            $layerCodeArr = array();
            foreach ($bu_analysis as $key=>$value) {
                $valArr = json_decode(json_encode($value), true);
                $layerCodeArr[] = $valArr['layer_code'];
            }
            if($group == 'all'){
                $this->BuApprovedLog->deleteAll([
                        'target_year' => $target_year,
                        'bu_term_id' => $term_id,
                        'bu_code' => $layerCodeArr[1],
                        'menu_id' => $menuId
                    ], false);

            }else{
                $approve_id = $this->BuApprovedLog->find('first', array(
                    'conditions' => $conditions,
                    'fields' => 'id'
                ))['BuApprovedLog']['id'];
            }
            //pr($conditions);exit;
            $login_id = $this->Session->read('LOGIN_ID');
            $approve_data = array();
            if($group != 'all') $approve_data['id'] = $approve_id;
            $approve_data['department_code'] = 0;
            $approve_data['line_code'] = 0;
            $approve_data['business_code'] = 0;
            $approve_data['sub_business_code'] = 0;
            $approve_data['flag'] = 1;
            $approve_data['cancel_flag'] = 1;
            $approve_data['updated_by'] = $login_id;
            $approve_data['updated_date'] = date("Y-m-d H:i:s");
            //pr($approve_data);exit;
            
            $ApprovedDB = $this->BuApprovedLog->getDataSource();
            $ApprovedDB->begin();
            if($group == 'all'){
                $approve_array = array();
                foreach($layerCodeArr as $key=>$value){
                    if($value){
                        $approve_data['menu_id'] = $menuId;
                        $approve_data['bu_term_id'] = $term_id;
                        $approve_data['target_year'] = $target_year;
                        $approve_data['created_by'] = $login_id;
                        $approve_data['updated_by'] = $login_id;
                        $approve_data['created_date'] = date("Y-m-d H:i:s");
                        $approve_data['updated_date'] = date("Y-m-d H:i:s");
                        if($key == 1) {
                            $approve_data['bu_code'] = $value;
                            $approve_data['group_code'] = 0;
                        }else{
                            $approve_data['bu_code'] = $bu;
                            $approve_data['group_code'] = $value;
                        }
                        
                        $approve_array[] = $approve_data;
                    }
                }
                
                $approve_log = $this->BuApprovedLog->saveAll($approve_array);
            }else $approve_log = $this->BuApprovedLog->saveAll($approve_data);
            
            $ApprovedDB->commit();
            if($approve_log){
                $this->Flash->success(parent::getSuccessMsg('SS036'), array(
                    'key' => 'bu_success'
                ));
            }else{
                $this->Flash->error(parent::getErrorMsg('SE157'), array(
                    'key' => 'bu_error'
                ));
            }
        }else{
            $this->Flash->error(parent::getErrorMsg('SE163'), array(
                'key' => 'bu_error'
            ));
        }
        $this->redirect(['action'=>'index']);

    }

    public function getNumberFormatExcel($decimal, $no_decimal, $excel) {
        foreach ($no_decimal as $nod_row => $coldata) {
            foreach ($coldata as $nod_col) {
                $excel->getStyle($nod_col.$nod_row)->getNumberFormat()->setFormatCode('""#,##0;[Red]"-"#,##0');
            }
        }

        foreach ($decimal as $d_row => $coldata) {
            foreach ($coldata as $d_col) {
                $excel->getStyle($d_col.$d_row)->getNumberFormat()->setFormatCode('""#,##0.0%;[Red]"-"#,##0.0%');
            }
        }
    }
}
