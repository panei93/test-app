<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');
/**
 * LaborCostDetail Model
 *
 * @property User $User
 * @property Position $Position
 */
class LaborCostDetail extends AppModel {

	public function businessCodes($target_year, $group_code){
        $layers = ClassRegistry::init('Layer');

        $sub_query = 'Layer.id IN (select max(layers.id) from layers where flag = 1 and parent_id LIKE "%'.$group_code.'%" and (DATE_FORMAT(from_date,"%Y") <= "'.$target_year[0].'" OR DATE_FORMAT(to_date,"%Y") >= "'.$target_year[1].'") group by layers.layer_code order by layers.id DESC)';
        
        $businesses = $layers->find('all', [
            'conditions' => [
                'Layer.flag' => 1,
                'Layer.layer_type_id' => 5,
                'Layer.parent_id LIKE' => '%' . $group_code . '%',
                'DATE_FORMAT(Layer.from_date,"%Y-%m") <=' => $target_year[0],
                'DATE_FORMAT(Layer.to_date,"%Y-%m") >=' => $target_year[1],
                $sub_query
            ],
            'fields' => [
                'Layer.layer_code',
                'Layer.name_en',
                'Layer.name_jp',
                'Layer.parent_id'
            ],
            'group' => [
                'Layer.layer_code'
            ]
        ]);

        $business_codes = [];
        foreach ($businesses as $business) {
            $business_codes[] = $business['Layer']['layer_code'];
        }
        return [$businesses, $business_codes];
    }

    public function checkUsers($target_year, $group_code){
        $layers = ClassRegistry::init('Layer');
        $users = ClassRegistry::init('User');
        $sub_businesses =  $layers->find('all', [
            'conditions' => [
                'Layer.flag' => 1,
                'Layer.layer_type_id' => 6,
                'Layer.parent_id LIKE' => '%' . $group_code . '%',
            ],
            'fields' => [
                'layer_code',
                'name_en',
                'name_jp',
                'parent_id'
            ],
        ]);
        
        foreach ($sub_businesses as $sub_business) {
            $subbusiness_codes[]['User.layer_code LIKE'] = '%' . $sub_business['Layer']['layer_code'] . '%';
        }
        $subbusiness_codes[]['User.role_id'] = 1;

        
        $users =  $users->find('all', [
            'conditions' => [
                'User.flag' => 1, 
                'User.joined_date <=' => "$target_year-11-31",
                'User.resigned_date >=' => "$target_year-01-01",
                'OR' => $subbusiness_codes,
            ],
            'order' => ["User.id" => "asc"],
        ]);

        return $users;
    }
    public function checkUser($target_year, $group_code, $term_id=null, $model=null){
        $layers = ClassRegistry::init('Layer');
        //$usersModel = ClassRegistry::init('User');
        // $lcd = ClassRegistry::init('LaborCostDetail');
        $model_name = ($model == null) ? MODEL : $model;
        $labor = ClassRegistry::init($model_name);
        $layers_list = $layers->find('list',[
            'fields' => ['layer_code'],
            'conditions' => [
                'Layer.flag' => 1,
                'Layer.parent_id LIKE' => '%' . $group_code . '%',                
                'DATE_FORMAT(Layer.from_date,"%Y-%m") <=' => $target_year[1],
                'DATE_FORMAT(Layer.to_date,"%Y-%m") >=' => $target_year[0]
            ]
        ]);
        array_push($layers_list,$group_code);
        $labor_user = $labor->find('list',[
            'fields' => ['user_id'],
            'conditions' => [
                // 'target_year ' => $target_year,
                'target_year' => explode('-',$target_year[0])[0],
                'layer_code like "%'.$group_code. '%"',
                'bu_term_id' => $term_id,
            ],
            'group' => ['user_id']
        ]);
        #array filter for empty values, strlen is for not to remove 0 value
        $labor_user = implode(",",array_filter($labor_user, 'strlen'));
        if(!empty($labor_user)){
           $tmp_condition = 'User.id IN ('.$labor_user.')';
        }else{
            $tmp_condition = '';
        }
        if(!empty($layers_list)) {
            if(!empty($labor_user)){
                $sql ='SELECT User.*, Position.*, LaborCostDetail.id  from users AS User INNER JOIN positions AS Position ON User.position_code = Position.position_code  LEFT JOIN labor_cost_details AS LaborCostDetail ON (LaborCostDetail.user_id = User.id AND LaborCostDetail.target_year = "'.explode('-',$target_year[0])[0].'" AND LaborCostDetail.layer_code LIKE "%'.$group_code.'%" AND LaborCostDetail.flag = 1)
                WHERE User.id IN ('.$labor_user. ') AND User.flag = 1 AND DATE_FORMAT(User.joined_date,"%Y-%m") <="'.$target_year[1].'" AND DATE_FORMAT(User.resigned_date,"%Y-%m") >="'.$target_year[0].'" AND User.layer_code LIKE "%'.$group_code.'%" AND Position.target_year = "'.explode('-',$target_year[0])[0].'" AND Position.flag = 1 GROUP BY User.login_code ORDER BY LaborCostDetail.id, User.user_name';
                
            }else{
                //$sql ='SELECT User.*, Position.*, LaborCostDetail.id  from users AS User INNER JOIN positions AS Position ON User.position_code = Position.position_code  LEFT JOIN labor_cost_details AS LaborCostDetail ON (LaborCostDetail.user_id = User.id AND LaborCostDetail.position_code = User.position_code AND LaborCostDetail.target_year = "'.explode('-',$target_year[0])[0].'" AND LaborCostDetail.layer_code LIKE "%'.$group_code.'%" AND LaborCostDetail.flag = 1 )
                //WHERE User.flag = 1 AND DATE_FORMAT(User.joined_date,"%Y-%m") <="'.$target_year[1].'" AND DATE_FORMAT(User.resigned_date,"%Y-%m") >="'.$target_year[0].'" AND User.layer_code LIKE "%'.$group_code.'%" AND Position.target_year = "'.explode('-',$target_year[0])[0].'" AND Position.flag = 1 GROUP BY User.login_code ORDER BY LaborCostDetail.id, User.user_name';
                $sql ='SELECT User.*, Position.*  from users AS User INNER JOIN positions AS Position ON User.position_code = Position.position_code  
                WHERE User.flag = 1 AND DATE_FORMAT(User.joined_date,"%Y-%m") <="'.$target_year[1].'" AND DATE_FORMAT(User.resigned_date,"%Y-%m") >="'.$target_year[0].'" AND User.layer_code LIKE "%'.$group_code.'%" AND Position.target_year = "'.explode('-',$target_year[0])[0].'" AND Position.flag = 1 GROUP BY User.login_code ORDER BY User.user_name';
            }
            $users = $this->query($sql);


        }else {
            $users = [];
        }
        return $users;
    }
    public function latestGroupCode($target_year){
        $layer = ClassRegistry::init('Layer');

        //group codes for filter selct box
        $groupCode = $layer->find('first', [
            'conditions' => [
                'Layer.flag' => 1,
                'Layer.layer_type_id' => 3,
            ],
            'fields' => [
                'layer_code',
            ],
            'order' => ['Layer.id' => 'asc']//actually firstest id
        ]);

        return $groupCode['Layer']['layer_code'];
    }
	//get user, position, person cost, corporate cost in Labor_Cost. But if not in labor cost, get them from User
    public function getLaborCost($target_year, $group_code, $term_id=null){
        $buTermId = $_SESSION['BU_TERM_ID']; 
        $laborCost = ClassRegistry::init('LaborCost');
        $lcd = ClassRegistry::init('LaborCostDetail');
        $model_name = ($model == null) ? MODEL : $model;
        $labor = ClassRegistry::init($model_name);
        $year_arr = $_SESSION['yearListOnTerm'][$target_year];
        $labor_user = []; 
        #new code for registered users
        $users = $this->checkUser($year_arr, $group_code, $buTermId);
        foreach($users as $user_value){
            $labor_user[] = $user_value['User']['id'];
        }
        # old code for registered users
        // $labor_user = $labor->find('list',[
        //     'fields' => ['user_id'],
        //     'conditions' => [
        //         // 'target_year ' => $target_year,
        //         'layer_code like "%'.$group_code. '%"',
        //         'bu_term_id' => $buTermId,
        //     ],
        //     'group' => ['user_id']
        // ]);
        #array filter for empty values, strlen is for not to remove 0 value
        $labor_user = implode(",",array_filter($labor_user, 'strlen'));
        if(!empty($labor_user)){
           $tmp_condition = 'User.id IN ('.$labor_user.')';
        }else{
            $tmp_condition = '';
        }
                
        $register_users = $laborCost->find('all',[
            'joins' =>[
                [
                    'table' => 'users',
					'alias' => 'User',
                    'type'  =>  'left',
                    'conditions' => [
                        'DATE_FORMAT(User.joined_date,"%Y-%m") <=' => "$year_arr[1]",
                        'DATE_FORMAT(User.resigned_date,"%Y-%m") >=' => "$year_arr[0]",
                        'User.flag' => '1',
                        'OR' => [
                            $tmp_condition,
                            'User.layer_code like "%'.$group_code.'%"'
                        ],
                        'LaborCost.user_id = User.id',
                     ]
                ],
                [
                    'table' => 'positions',
					'alias' => 'Position',
                    'type'  =>  'left',
                    'conditions' => [
                        'LaborCost.position_code = Position.position_code',
                        'Position.target_year' => $target_year,
                        'Position.flag' => 1,
                    ]                
                ],
                [
                    'table' => 'labor_cost_details',
					'alias' => 'LaborCostDetail',
                    'type'  =>  'left',
                    'conditions' => [
                        'LaborCostDetail.user_id = LaborCost.user_id',
                        // 'LaborCostDetail.position_code = User.position_code',
                        'LaborCostDetail.flag' => '1'
                    ]               
                ]
                
            ],
            'fields' => [
                'LaborCostDetail.*', 
                'LaborCost.id', 
                // 'LaborCost.layer_code', 
                // 'LaborCost.position_code', 
                'LaborCost.user_id',
                // 'LaborCost.person_count',
                'LaborCost.b_person_count', 
                'LaborCost.common_expense',
                'LaborCost.b_person_total',
                'LaborCost.adjust_labor_cost',
                'LaborCost.adjust_corpo_cost',
                'LaborCost.unit_labor_cost',
                'LaborCost.unit_corpo_cost',
                // 'User.user_name',
                'User.user_name AS user_name',
                'Position.position_name',
                'Position.personnel_cost',
                'Position.corporate_cost'
            ],
            'group' => ['User.user_name'],
            'order' => ['LaborCostDetail.id', 'User.user_name'],
            'conditions' => [
                $tmp_condition,
                'LaborCostDetail.bu_term_id' => $buTermId,
                'LaborCostDetail.target_year' => $target_year,
                'LaborCostDetail.layer_code like "%'.$group_code.'%"',
				'(LaborCost.adjust_name IS NULL OR LaborCost.adjust_name = "" OR LaborCost.adjust_name = "0")',
                'LaborCost.flag' => '1',
                'LaborCost.target_year' => $target_year,
            ]     
        ]);
        $new_users = $laborCost->find('all',[
            'joins' =>[
                [
                    'table' => 'positions',
					'alias' => 'Position',
                    'type'  =>  'inner',
                    'conditions' => [
                        'LaborCost.position_code = Position.position_code',
                        'Position.target_year' => $target_year,
                        'Position.flag' => 1,
                    ]                
                ],
                [
                    'table' => 'labor_cost_details',
					'alias' => 'LaborCostDetail',
                    'type'  =>  'left',
                    'conditions' => [                        
                        'LaborCostDetail.new_user_name = LaborCost.new_user_name',
                        'LaborCostDetail.position_code = LaborCost.position_code',
                        'LaborCostDetail.user_id = 0',
                        'LaborCostDetail.flag' => '1'
                    ]               
                ]
                
            ],
            'fields' => [
                'LaborCostDetail.*', 
                'LaborCost.id', 
                // 'LaborCost.layer_code', 
                // 'LaborCost.position_code', 
                // 'LaborCost.user_id',
                // 'LaborCost.person_count',
                'LaborCost.b_person_count', 
                'LaborCost.common_expense',
                'LaborCost.b_person_total',
                'LaborCost.adjust_labor_cost',
                'LaborCost.adjust_corpo_cost',
                'LaborCost.unit_labor_cost',
                'LaborCost.unit_corpo_cost',
                // 'User.user_name',
                'LaborCost.new_user_name AS user_name',
                'Position.position_name',
                'Position.personnel_cost',
                'Position.corporate_cost'
            ],
            'group' => ['LaborCost.id'],
            'order' => ['LaborCostDetail.id'],
            'conditions' => [
                'LaborCost.bu_term_id' => $buTermId,
                'LaborCostDetail.bu_term_id' => $buTermId,
                'LaborCostDetail.target_year' => $target_year,
                'LaborCostDetail.layer_code like "%'.$group_code.'%"',
				'(LaborCost.adjust_name IS NULL OR LaborCost.adjust_name = "" OR LaborCost.adjust_name = "0")',
                'LaborCost.new_user_name IS NOT NULL',
                'LaborCost.flag' => '1',
                'LaborCost.target_year' => $target_year
            ]     
        ]);
        $laborCosts = array_merge($register_users,$new_users);
        #b_person_total for LCD' s 予算人員数 column
        $laborCost->virtualFields['b_person_total'] = '(LaborCost.b_person_count + LaborCost.common_expense)';
        $b_person_total = $laborCost->find('list', array(
            'conditions' => array(
                'flag' => 1,
                'layer_code LIKE' => '%'.$group_code.'%',
                'bu_term_id' => $buTermId,
                'target_year' => $target_year,
                'user_id <>' => 0 
            ),
            'group' => array('user_id'),
            'fields' => array('user_id', 'b_person_total')
        ));
        
        $formattedLaborCost = [];$number = 0;
        if(!$laborCosts){
            foreach ($users as $user) {$number ++;
                #b_person_total for LCD' s 予算人員数 column
                $budget_person_total = $b_person_total[$user['User']['id']];
                $formattedLaborCost[] = [
                    // 'position_id' => NULL,
                    // 'position_code' => NULL,
                    // 'position_name' => NULL,
                    'position_code' => $user['User']['position_code'],
                    'position_name' => $user['User']['position_name'],
                    'user_id' => $user['User']['id'],
                    'user_name' => $user['User']['user_name'],
                    'person_count' => 0,
                    'b_person_count' => '0.00',
                    'common_expense' => 0,
                    'b_person_total' => ($budget_person_total) ? $budget_person_total : 0,
                    'adjust_labor_cost' => 0,
                    'adjust_corpo_cost' => 0,
                    'unit_labor_cost' => $user['Position']['personnel_cost'],
                    'unit_corpo_cost' => $user['Position']['corporate_cost'],
                    'personnel_cost' => $user['Position']['personnel_cost'],
                    'corporate_cost' => $user['Position']['corporate_cost'],
                    'none_labor_cost' => false,//for amount input field disabled
                    'number' => $number
               ];
            }
        }else{
            
            foreach ($laborCosts as $key => $value) {$number ++;
                #b_person_total for LCD' s 予算人員数 column
                $budget_person_total = $b_person_total[$value['LaborCost']['user_id']];
                $laborCosts[$key]['LaborCost']['unit_labor_cost'] = $value['Position']['personnel_cost'];
                $laborCosts[$key]['LaborCost']['unit_corpo_cost'] = $value['Position']['corporate_cost'];
                $laborCosts[$key]['LaborCost']['b_person_total'] = ($budget_person_total) ? $budget_person_total : 0;
                $laborCosts[$key]['LaborCost']['number'] = $number;
            }
            foreach ($laborCosts as $index => $laborCost) {    
                $formattedLaborCost[] = call_user_func_array('array_merge', $laborCosts[$index]);

            }

        }
        return $formattedLaborCost;
    }

    public function getAccountCodeForBudget(){
        $account = ClassRegistry::init('Account');
        $account = $account->find('list', [
            'conditions' => [
                // 'account_name' => '人件費　(ﾋﾞｼﾞﾈｽ別人員表）'
                'OR' =>[
                    'account_name' => Setting::ACCOUNT_NAME_LCD,
                    'account_code' => Setting::ACCOUNT_CODE_LCD
                ]
            ],
            'fields' => [
                'id', 'account_code'
            ]
        ]);
        
        return $account;
    }
	/**
     * get total person count for LaborCost position change function
     *
     * @author SST 17.8.2022
     * @return data
     */
    public function getPersonCount($target_year,$user_id){
        $param = array();
        $sql  = "";
        $sql .= " SELECT SUM(LaborCostDetail.person_count) as total_person_count";
        $sql .= " FROM labor_cost_details as LaborCostDetail";
        $sql .= " WHERE LaborCostDetail.target_year=:target_year";
        $sql .= " AND LaborCostDetail.user_id=:user_id";
        $sql .= " AND LaborCostDetail.flag=:flag";
        $sql .= " GROUP By LaborCostDetail.user_id,LaborCostDetail.target_year";
        $param['target_year']   = $target_year;
        $param['user_id']       = $user_id;
        $param['flag']          = 1;
        $data = $this->query($sql, $param);        
        return $data;
    }
    /**
     * to check data
     *
     * @author SST 22.9.2022
     * @return data
     */
    public function getDetailData($target_year, $buTermId){
        $sql  = "";
        $sql .= " SELECT * ";
        $sql .= " FROM labor_cost_details";
        $sql .= " WHERE target_year=$target_year";
        $sql .= " AND bu_term_id =$buTermId";
        $sql .= " AND flag=1";
        $data = $this->query($sql);  
        return $data;
    }
    /**
     * get data to check position_id same or not in save/update 
     * @author SST 29.9.2022
     * @param $target_year,$user_id
     * @return data
     */
    public function getLaborCostDetailUserPosition($target_year,$user_id){
        $sql  = "";
        $sql .= " SELECT * ";
        $sql .= " FROM labor_cost_details";
        $sql .= " WHERE target_year=$target_year";
        $sql .= " AND user_id=$user_id";
        $sql .= " AND flag=1";
        $data = $this->query($sql);  
        return $data;
    }
    /**
     * update position_id   *
     * @author SST 29.9.2022
     * @param $position_id,$target_year,$user_id
     * @return boolean
     */
    public function updatePositionId($position_code,$target_year,$user_id){
        $sql  = "";
        $sql  = "SET SQL_SAFE_UPDATES=0;";
        $sql .= " UPDATE labor_cost_details";
        $sql .= " SET position_code=$position_code";
        $sql .= " WHERE target_year=$target_year";
        $sql .= " AND user_id=$user_id";
        $sql .= " AND flag=1";
        $data = $this->query($sql); 
        return true;
    }
}
