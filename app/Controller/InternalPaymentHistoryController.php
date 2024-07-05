<?php
App::uses('CakeText', 'Utility');
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');

class InternalPaymentHistoryController extends AppController
{
	public $helpers = array('Html', 'Form', 'Csv');
	public $uses = array('BrmBudgetPrime','BrmExpected','BrmSaccount','BrmActualResultSummary','BrmTerm'
							,'Layer','User');
	public $components = array('Session','Flash','Paginator', 'PhpExcel.PhpExcel');


	/**
	 * Check Session before render page
	 *
	 */
	public function beforeFilter()
	{
		parent::CheckSession();
		parent::checkUserStatus();
	
		$Common = New CommonController();
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
    //     $layers = array_keys($permissions['index']['layers']);
    //  //  pr($permissions);die;
    //     if((!in_array($layer_code, $layers)) || ($layer_code=="" && $permissions['index']['limit']>0)) {
    //         $errorMsg = parent::getErrorMsg('SE065');
    //         $this->Flash->set($errorMsg, array("key"=>"TermError"));
    //         $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
    //     }
       
	}
	/**
	 *
	 * index method
   * @author Aye Zar Ni Kyaw(2020_07_28)
	 *
	 */
	public function index()
	{

		$Common = new CommonController;
		$result_datas     = [];
		$payment_datas    = [];
		$pg_payment_datas = [];
		$toal_amount      = [];
		$get_ba_code      = [];

		$paging_limit 		= Paging::TABLE_PAGING;
		$this->layout 		= 'phase_3_menu';
		$term 				= $this->Session->read('TERM_NAME');
		$term_id 			= $this->Session->read('TERM_ID');
		$loginId 			= $this->Session->read('LOGIN_ID');
		$permission   		= $this->Session->read('PERMISSIONS');
		$this->Session->write('PERMISSIONS', $permission);
		$session_layer_code = $this->Session->read('SESSION_LAYER_CODE');
		$head_dept_code     = $this->Session->read('HEAD_DEPT_CODE');//10001
		$budget_ba_code 	= $this->Session->read('BUDGET_BA_CODE');//pr($budget_ba_code);die;
		$searched_ba 		= ($this->Session->check('PH_SEARCH_BA')) ? $this->Session->read('PH_SEARCH_BA') : '';
		$searched_logi 		= ($this->Session->check('PH_SEARCH_LOGI')) ? $this->Session->read('PH_SEARCH_LOGI') : '';
		$search_data   		= ($this->Session->check('SEARCH')) ? $this->Session->read('SEARCH') : '';

		if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}

		$target_year 		= $_GET['year'];
	
		$term_1st_year 		= substr($term, 0, 4); #get 1st year of selected term
		$read_limit   		= $permission['index']['limit'];
		
		$top_layer_id 		= $this->Session->read('TOP_LAYER');
		
		$searchBAlist 		= '';
		
		if ($read_limit == 0) {
			$limit0_con['Layer.flag'] = 1;
			if (!empty($session_layer_code)) {
				$limit0_con['Layer.layer_code'] = $session_layer_code;
			}
			//get head_department_id based on login_id's ba_code
			$getLayerCode = $this->Layer->find('list', array(
				'fields'     => array('Layer.layer_code'),
				'conditions' => $limit0_con,
				'group'      => array('Layer.layer_code'),
			));
		
			$searchBA = $this->Layer->find('list', array(
				'fields'     => array('Layer.layer_code','Layer.name_'.$lang_name),
				'conditions' => array( array('Layer.flag' => 1,'Layer.type_order'=>Setting::LAYER_SETTING['bottomLayer'])),
				'group'      => array('Layer.layer_code'),
			));
			$searchBAlist = $searchBA;
		
		} elseif ($read_limit == Setting::LAYER_SETTING['bottomLayer']) {

			$getLayerCode = explode(',',$this->User->find('all', array(
				'fields' => array('layer_code'),
				'conditions' => array(
					'User.id' => $loginId,
					'User.flag' => 1
				)
			))[0]['User']['layer_code']);

			if(!empty($session_layer_code) && !in_array($session_layer_code, $getLayerCode)){
				$errorMsg = parent::getErrorMsg('SE065');
				$this->Flash->error($errorMsg);
				$this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
			}

			$Resultlayer = $this->Layer->find('list', array(
				'fields' => array('Layer.layer_code','Layer.name_'.$lang_name),
				'conditions' => 
				array(
				
					'Layer.flag' => 1,
					'Layer.type_order'=>Setting::LAYER_SETTING['bottomLayer'],
					'Layer.layer_code IN' => $getLayerCode
						
						
				)
			));
			
		$searchBAlist = $Resultlayer;
		} 
		elseif ($read_limit == Setting::LAYER_SETTING['topLayer']) {
		
			$getLayerCode = explode(',',$this->User->find('all', array(
				'fields' => array('layer_code'),
				'conditions' => array(
					'User.id' => $loginId,
					'User.flag' => 1
				)
			))[0]['User']['layer_code']);
			
			// if(!empty($session_layer_code) && !in_array($session_layer_code, $getLayerCode)){
			// 	$errorMsg = parent::getErrorMsg('SE065');
			// 	$this->Flash->error($errorMsg);
			// 	$this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
			// }

			$getParentID = $this->Layer->find('list', array(
				'fields' => array('layer_code','parent_id'),
				'conditions' => array(
					'Layer.flag' => 1,
					'Layer.layer_code IN'=>$getLayerCode
				)
			));

			$getToplayerCode = [];
			$conditions =array('0');
		
			foreach($getParentID as $key=> $val){
				$getToplayerCode[$key]=str_replace('"', '',explode(':',(explode(',', $val)[Setting::LAYER_SETTING['topLayer']-1]))[1]);
				$conditions[] = array('Layer.parent_id LIKE' => '%'.$getToplayerCode[$key].'%');
			}
			if(!empty($head_dept_code) && !in_array($head_dept_code, $getToplayerCode)){
				$errorMsg = parent::getErrorMsg('SE065');
				$this->Flash->error($errorMsg);
				$this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
			}

			$Resultlayer = $this->Layer->find('list', array(
				'fields' => array('Layer.layer_code','Layer.name_'.$lang_name),
				'conditions' => 
				array(
				
					'Layer.flag' => 1,
					'Layer.type_order'=>Setting::LAYER_SETTING['bottomLayer'],
					'OR' => $conditions
						
				)
			));
			$searchBAlist = $Resultlayer;
		}
		
		
		$account_codes = $this->BrmSaccount->find('first', array(
					'conditions' => array(
						'BrmSaccount.name_jp'   => '社内受払手数料',
						'BrmAccount.group_code' => '01',
						'BrmSaccount.flag'      => 1,
					),
					'fields'    => 'BrmSaccount.account_code'
				   ));
		$acc_codes = !empty($account_codes)?$account_codes['BrmSaccount']['account_code']:'';
	//pr($acc_codes);die;
		#ba_name_jp
		$restrict_hqs = Setting::TRADING_DISABLE_HQS;
		$restrict_bas = $this->Layer->find('list', array(
			'fields'			=> 'layer_code',
			'conditions' 		=> array(
				'Layer.flag' 	=> 1,
				'name_jp' 		=> $restrict_hqs
			)
		));
		
		$ba_name_jp = $this->Layer->find(
			'list',
			array(
					  'fields'      => array('layer_code','name_jp'),
					  'conditions' =>array(
						'Layer.flag' => 1,
					  )
					 )
		);
	
		$modelName = ($target_year == $term_1st_year) ? 'BrmExpected' : 'BrmBudgetPrime';
	//	pr($modelName);die;
		$conditions 									= array();
		$resultcons 									= array();
		$resultmonths 									= array();
		$result_notes 									= array();
		$result_notes_pagin 							= array();
		$conditions[$modelName.".brm_term_id"] 			= $term_id;
		$conditions[$modelName.".target_year"] 			= $target_year;
		$conditions[$modelName.".destination NOT IN"] 	= array('','NUll');
		$conditions[$modelName.".account_code"] 		= $acc_codes;
		$conditions[$modelName.".flag"] 				= 1;

		if (!empty($session_layer_code) && empty($search_data)) {
			if( $read_limit == Setting::LAYER_SETTING['bottomLayer']) {
				$conditions['NOT']['OR'][$modelName.".layer_code"]     = $restrict_bas;
			}
			$conditions['OR'] = array(
				$modelName.".layer_code"  => $session_layer_code,
				$modelName.".destination" => $session_layer_code,
			);
			$resultcons['OR'] = array(
				'BrmActualResultSummary.layer_code'       => $session_layer_code,
				'BrmActualResultSummary.destination_code' => $session_layer_code,
			);
		}

		if (!empty($session_layer_code) && !empty($search_data) &&  $read_limit == Setting::LAYER_SETTING['bottomLayer']) {
			
			$conditions['OR'][$modelName.".layer_code"] 						= $getLayerCode;
			$resultcons['OR']['BrmActualResultSummary.layer_code'] 				= $getLayerCode;
			$conditions['OR'][$modelName.".destination"] 						= $getLayerCode;
			$resultcons['OR']['BrmActualResultSummary.destination_code'] 		= $getLayerCode;
		}

		if (empty($session_layer_code) && !empty($getLayerCode)) {
			if ($read_limit == Setting::LAYER_SETTING['bottomLayer']) {
				$conditions['NOT']['OR'][$modelName.".layer_code"]            	= $restrict_bas;
			}
			$conditions['OR'][$modelName.".layer_code"]                       	= $getLayerCode;
			$conditions['OR'][$modelName.".destination"]                     	= $getLayerCode;
			$resultcons['OR']['BrmActualResultSummary.layer_code']            	= $getLayerCode;
			$resultcons['OR']['BrmActualResultSummary.destination_code']      	= $getLayerCode;
		}

		if (!empty($searched_ba)) {
			$conditions['OR'][$modelName.".layer_code"] 						= $searched_ba;
			$resultcons['OR']['BrmActualResultSummary.layer_code'] 				= $searched_ba;
			$conditions['OR'][$modelName.".destination"] 						= $searched_ba;
			$resultcons['OR']['BrmActualResultSummary.destination_code'] 		= $searched_ba;
		}

		if (!empty($searched_logi)) {
			$conditions[$modelName.".logistic_index_no"] = $searched_logi;
		}

		if ($target_year == $term_1st_year) {

			$first_6_months = array('month_1_amt','month_2_amt','month_3_amt','month_4_amt','month_5_amt','month_6_amt');
			$total_months   = array('first_half','second_half','whole_total');

			#Get forecast period(eg: 2020-05) to show actual result data till to this period
			$forecastPeriod = $this->BrmTerm->getForecastPeriod($term_id);
			
			$start_month = $Common->getMonth($term_1st_year, $term_id, 'start');
			
			$resultcons['BrmActualResultSummary.destination_code !='] 		= $acc_codes;
			$resultcons['BrmActualResultSummary.account_code'] 				= $acc_codes;
			$resultcons['BrmActualResultSummary.target_month >='] 			= $start_month;
			$resultcons['BrmActualResultSummary.target_month <='] 			= $forecastPeriod;
			$resultcons['BrmActualResultSummary.destination_code NOT IN'] 	= array('','NUll');

			$this->BrmActualResultSummary->virtualFields['result_total'] 	= 'SUM(amount)';
			$this->BrmActualResultSummary->virtualFields['ba_sets'] 		= "CONCAT(BrmActualResultSummary.layer_code, '/', BrmActualResultSummary.destination_code, '/', TRIM(BrmLogistic.index_name))";
			$actualResultData = $this->BrmActualResultSummary->find('list', array(
				'fields' 		=> array('BrmActualResultSummary.ba_sets','BrmActualResultSummary.result_total','BrmActualResultSummary.target_month'),
				'conditions' 	=> $resultcons,
				'joins' => array(
					array(
						'alias' => 'BrmLogistic',
						'table' => sprintf("(select distinct index_name,index_no,layer_code,target_year,flag from `brm_logistics` where flag=1 group by layer_code,target_year,index_no) "),
						'type'  => 'left',
						'conditions' => array(
							0 => 'BrmActualResultSummary.transaction_key = index_no',
							1 => array('BrmActualResultSummary.layer_code = BrmLogistic.layer_code'),
							2 => array('BrmLogistic.flag = 1'),
							3 => array('BrmLogistic.target_year ='.$term_1st_year),
						),
					)
				),
				'group' => array('BrmActualResultSummary.ba_sets','BrmActualResultSummary.target_month'),
				'order' => 'BrmActualResultSummary.target_month ASC','BrmActualResultSummary.ba_sets ASC',
			));
			//pr($actualResultData);die;
			$result_month = $start_month;
			while ($result_month <= $forecastPeriod) {
				$tg_month = date('n', strtotime($result_month));
				$col = $Common->getMonthColumn($tg_month, $term_id);
				$idx_name = 'month_'.$col.'_amt';

				array_push($resultmonths, $idx_name);
			
				$result_datas[$idx_name] = $actualResultData[$result_month];
			
				$result_month = date("Y-m", strtotime($result_month. "last day of + 1 months"));
			}

		}
		$destination_filter = [];
		// paginate
		try {
			//pr($conditions);
			$tradings_datas = $this->$modelName->find('all',array(
				'conditions' => $conditions,
				'order' => 'layer_code ASC', 'logistic_index_no ASC', 'destination ASC', 'kpi_unit ASC',
			));
		//pr($tradings_datas);die;
			foreach ($tradings_datas as $trdata) {
				$tmp = [];

				$ba_code 				= $trdata[$modelName]['layer_code'];
				$logistic_index_no 		= trim($trdata[$modelName]['logistic_index_no']);
				$destination 			= $trdata[$modelName]['destination'];
				$kpi_unit 				= $trdata[$modelName]['kpi_unit'];

				$result_key 			= $ba_code.'/'.$destination.'/'.$logistic_index_no;
				$note 					= $result_key.'/'.$kpi_unit;

				$tmp['layer_code'] 		= $ba_code;
				$tmp['ba_name'] 		= $ba_name_jp[$ba_code];
				$tmp['logistic_index_no'] 	= $logistic_index_no;
				$tmp['destination'] 	= $destination;
				$tmp['destination_name'] = $ba_name_jp[$destination];
				$tmp['kpi_unit'] 		= $kpi_unit;
				$tmp['month_1_amt'] 	= round($trdata[$modelName]['month_1_amt']/1000,1);
				$tmp['month_2_amt'] 	= round($trdata[$modelName]['month_2_amt']/1000,1);
				$tmp['month_3_amt'] 	= round($trdata[$modelName]['month_3_amt']/1000,1);
				$tmp['month_4_amt'] 	= round($trdata[$modelName]['month_4_amt']/1000,1);
				$tmp['month_5_amt'] 	= round($trdata[$modelName]['month_5_amt']/1000,1);
				$tmp['month_6_amt'] 	= round($trdata[$modelName]['month_6_amt']/1000,1);
				$tmp['month_7_amt'] 	= round($trdata[$modelName]['month_7_amt']/1000,1);
				$tmp['month_8_amt'] 	= round($trdata[$modelName]['month_8_amt']/1000,1);
				$tmp['month_9_amt'] 	= round($trdata[$modelName]['month_9_amt']/1000,1);
				$tmp['month_10_amt'] 	= round($trdata[$modelName]['month_10_amt']/1000,1);
				$tmp['month_11_amt'] 	= round($trdata[$modelName]['month_11_amt']/1000,1);
				$tmp['month_12_amt'] 	= round($trdata[$modelName]['month_12_amt']/1000,1);
				
				if(!in_array($logistic_index_no.'/'.$destination, $destination_filter)) {
					if (!empty($resultmonths) && !in_array($note, $result_notes)) {
						$rs_total = 0;
						foreach ($resultmonths as $rsmonth) {
							$tmp[$rsmonth] = (!empty($result_datas[$rsmonth][$result_key])) ? round($result_datas[$rsmonth][$result_key]/1000,1) : 0;
							$rs_total += $tmp[$rsmonth];
						}

						if ($rs_total > 0) {
							array_push($result_notes, $note);
						}
					}
					array_push($destination_filter, $logistic_index_no.'/'.$destination);
				}
				
				$toal_amount['month_1_amt'] += $tmp['month_1_amt'];
				$toal_amount['month_2_amt'] += $tmp['month_2_amt'];
				$toal_amount['month_3_amt'] += $tmp['month_3_amt'];
				$toal_amount['month_4_amt'] += $tmp['month_4_amt'];
				$toal_amount['month_5_amt'] += $tmp['month_5_amt'];
				$toal_amount['month_6_amt'] += $tmp['month_6_amt'];
				$toal_amount['month_7_amt'] += $tmp['month_7_amt'];
				$toal_amount['month_8_amt'] += $tmp['month_8_amt'];
				$toal_amount['month_9_amt'] += $tmp['month_9_amt'];
				$toal_amount['month_10_amt'] += $tmp['month_10_amt'];
				$toal_amount['month_11_amt'] += $tmp['month_11_amt'];
				$toal_amount['month_12_amt'] += $tmp['month_12_amt'];
				
				$payment_datas[] = $tmp;
			}

			//pr($conditions);die;
			$this->paginate  = array(
				'limit' => $paging_limit,
				'conditions' => $conditions,
				'order' => 'layer_code ASC'
			);
			$paginated_datas = h($this->Paginator->paginate($modelName, array(), array('layer_code', 'logistic_index_no', 'destination', 'kpi_unit')));
			
			if (count($payment_datas) <= $paging_limit) {
				$pg_payment_datas = $payment_datas;		
			} else {
				foreach ($paginated_datas as $trdata) {
					$tmp = [];

					$ba_code 				= $trdata[$modelName]['layer_code'];
					$logistic_index_no 		= trim($trdata[$modelName]['logistic_index_no']);
					$destination 			= $trdata[$modelName]['destination'];
					$kpi_unit 				= $trdata[$modelName]['kpi_unit'];

					$result_key 			= $ba_code.'/'.$destination.'/'.$logistic_index_no;
					$note 					= $result_key.'/'.$kpi_unit;

					$tmp['layer_code'] 			= $ba_code;
					$tmp['ba_name'] 			= $ba_name_jp[$ba_code];
					$tmp['logistic_index_no'] 	= $logistic_index_no;
					$tmp['destination'] 		= $destination;
					$tmp['destination_name'] 	= $ba_name_jp[$destination];
					$tmp['kpi_unit'] 		= $kpi_unit;
					$tmp['month_1_amt'] 	= round($trdata[$modelName]['month_1_amt']/1000,1);
					$tmp['month_2_amt'] 	= round($trdata[$modelName]['month_2_amt']/1000,1);
					$tmp['month_3_amt'] 	= round($trdata[$modelName]['month_3_amt']/1000,1);
					$tmp['month_4_amt'] 	= round($trdata[$modelName]['month_4_amt']/1000,1);
					$tmp['month_5_amt'] 	= round($trdata[$modelName]['month_5_amt']/1000,1);
					$tmp['month_6_amt'] 	= round($trdata[$modelName]['month_6_amt']/1000,1);
					$tmp['month_7_amt'] 	= round($trdata[$modelName]['month_7_amt']/1000,1);
					$tmp['month_8_amt'] 	= round($trdata[$modelName]['month_8_amt']/1000,1);
					$tmp['month_9_amt'] 	= round($trdata[$modelName]['month_9_amt']/1000,1);
					$tmp['month_10_amt'] 	= round($trdata[$modelName]['month_10_amt']/1000,1);
					$tmp['month_11_amt'] 	= round($trdata[$modelName]['month_11_amt']/1000,1);
					$tmp['month_12_amt'] 	= round($trdata[$modelName]['month_12_amt']/1000,1);

					if (!empty($resultmonths) && !in_array($note, $result_notes_pagin)) {
						$rs_total = 0;
						foreach ($resultmonths as $rsmonth) {
							$tmp[$rsmonth] = (!empty($result_datas[$rsmonth][$result_key])) ? round($result_datas[$rsmonth][$result_key]/1000,1) : 0;
							$rs_total += $tmp[$rsmonth];
						}

						if ($rs_total > 0) {
							array_push($result_notes_pagin, $note);
						}
					}

					$pg_payment_datas[] = $tmp;
				}
			}


			$cache_name = 'internal_payment_'.$term_id.'_'.$target_year.'_'.$loginId;
			$cache_data = array(
				'tradings_datas'	=> $payment_datas,
				'total_amounts'		=> $toal_amount,
				'term_name'			=> $term,
				'target_year'		=> $target_year,
			);
			Cache::write($cache_name, $cache_data);
			
			//count
			$query_count = count($tradings_datas);
			if ($query_count == 0) {
				$errmsg = parent::getErrorMsg('SE001', $query_count);
				$this->set('errmsg', $errmsg);
			} else {
				$count = parent::getSuccessMsg('SS004', $query_count);
				$this->set('count', $count);
				$this->set('query_count', $query_count);
			}
		
			$this->Session->delete('PH_SEARCH_BA');
			$this->Session->delete('PH_SEARCH_LOGI');
		
			$searched_ba = empty($search_data)? $session_layer_code: $searched_ba;
		
			$this->Session->delete('SEARCH');
			$this->set(compact('term', 'term_id', 'target_year', 'pg_payment_datas', 'modelName', 'ba_name_jp', 'toal_amount', 'searched_ba','searched_logi','searchBAlist','searchDeslist','session_layer_code'));
			return $this->render('index');
		} catch (Exception $e) {
			CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
			$this->redirect(array(
				'controller' => 'InternalPaymentHistory', 
				'action' => 'index',
				'?' => array(
				'year' => $target_year,
				)
			));
		}
	}
	/**
	 *
	 * SearchDestination method
	 *  @author Aye Zar Ni Kyaw(2020_07_29)
	 *
	 */
	public function SearchDestination()
	{
		$this->layout = 'phase_3_menu';
	
		$target_year = $this->request->data('target_year');
		$source_bcode = trim($this->request->data('source_bcode'));
		$logistic_index_no = trim($this->request->data('logistic_index_no'));
		$permission   		= $this->Session->read('PERMISSIONS');
		//pr($permission);die;
		$this->Session->write('PERMISSIONS', $permission);
		$this->Session->write('PH_SEARCH_BA',$source_bcode);
		$this->Session->write('PH_SEARCH_LOGI',$logistic_index_no);
		$this->Session->write('SEARCH','search');

		$this->redirect(array(
			'controller' => 'InternalPaymentHistory', 
			'action' => 'index',
			'?' => array(
			'year' => $target_year,
			)
		));
	}
	public function excelData()
	{
		$this->layout = null;
		$requestData = $this-> request-> data;
		$this->autoLayout = false;

		$term 			= $this->Session->read('TERM_NAME');
		$term_id 		= $this->Session->read('TERM_ID');
		$target_year 	= $requestData['target_year'];
		$loginId 		= $this->Session->read('LOGIN_ID');

		$cache_name 	= 'internal_payment_'.$term_id.'_'.$target_year.'_'.$loginId;
		$cache_data 	=Cache::read($cache_name);
		$tradings_datas = $cache_data['tradings_datas'];
		$target_year 	= $cache_data['target_year'];
		$term_name 		= $cache_data['term_name'];
		$total_amounts 	= $cache_data['total_amounts'];

		$objWorkSheet = $this->PhpExcel->createWorksheet()->setDefaultFont('Calibri', 12);
		$BStyle = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => '808080'),
				),
			),
		);
		$centerHorizontalAlign_style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);
		$topVerticalAlign_style = array(
			'alignment' => array(
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
			)
		);
   
		$objWorkSheet->getActiveSheet()->getStyle("A3")->applyFromArray($centerHorizontalAlign_style);
		$objWorkSheet->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
	
		$sheet = $this->PhpExcel->getActiveSheet();
		$objWorkSheet->getActiveSheet()->mergeCells('A3:R3');
		$sheet->setCellValue('A3', $target_year." ".__("受払履歴"));
		$colName = array("A", "B", "C", "D", "E", "F", "G","H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R");
		$title = array("ソース", "取引 / 理由", "相手先", "4月", "5月", "6月", "7月", "8月", "9月", "上期","10月", "11月", "12月", "1月", "2月", "3月", "下期", "年間");
	
		$sheet->setCellValue("R1", $term_name);
		$sheet->setCellValue("Q1", __("期間"));
		foreach ($title as $key=>$value) {
			$objWorkSheet->getActiveSheet()->getStyle($colName[$key])->applyFromArray($topVerticalAlign_style);
			$sheet->getStyle($colName[$key])->getNumberFormat()->setFormatCode('#,##0.0;[Red]-#,##0.0');
			$objWorkSheet->getActiveSheet()->getStyle($colName[$key]."5")->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle($colName[$key]."5")->applyFromArray($centerHorizontalAlign_style);
			$objWorkSheet->getActiveSheet()->getStyle($colName[$key]."5")->getFont()->setBold(true);
			$objWorkSheet->getActiveSheet()->getStyle($colName[$key]."5")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e5ffff');
			$sheet->setCellValue($colName[$key]."5", __($value));
		}
		$objWorkSheet->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objWorkSheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objWorkSheet->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objWorkSheet->getActiveSheet()->getColumnDimension('D')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('E')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('G')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('H')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('I')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('J')->setWidth(12);
		$objWorkSheet->getActiveSheet()->getColumnDimension('K')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('L')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('M')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('N')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('O')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('P')->setWidth(10);
		$objWorkSheet->getActiveSheet()->getColumnDimension('Q')->setWidth(12);
		$objWorkSheet->getActiveSheet()->getColumnDimension('R')->setWidth(12);
		$DTotal = $ETotal = $FTotal = $GTotal = $HTotal = $ITotal = $JTotal = $KTotal = $LTotal = $MTotal = $NTotal = $OTotal = $OTotal = $QTotal = $RTotal ='';
	
		foreach ($tradings_datas as $key=>$value) {
			$row = $key + 6;

	  		$source_ba = $value['ba_code']."\n".$value['ba_name'];
			$sheet->setCellValue('A'.$row, $source_ba);
			$objWorkSheet->getActiveSheet()->getStyle('A'.$row)->getAlignment()->setWrapText(true);

			if (!empty($value['kpi_unit'])) {
				$sheet->setCellValue('B'.$row, $value['logistic_index_no'].'/'.$value['kpi_unit']);
			} else {
				$sheet->setCellValue('B'.$row, $value['logistic_index_no']);
			}
			$objWorkSheet->getActiveSheet()->getStyle('A'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('B'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('C'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('D'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('E'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('F'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('G'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('H'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('I'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('J'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('K'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('L'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('M'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('N'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('O'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('P'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('Q'.$row)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle('R'.$row)->applyFromArray($BStyle);
			

			$dest_ba = $value['destination']."\n".$value['destination_name'];

			$sheet->setCellValue('C'.$row, $dest_ba);
			$objWorkSheet->getActiveSheet()->getStyle('C'.$row)->getAlignment()->setWrapText(true);
	  
			$first_half = "D".$row."+"."E".$row."+"."F".$row."+"."G".$row."+"."H".$row."+"."I".$row;
			$second_half = "K".$row."+"."L".$row."+"."M".$row."+"."N".$row."+"."O".$row."+"."P".$row;
			$whole_total = "J".$row."+"."Q".$row;
			$month1 = $value['month_1_amt'];
			$month2 = $value['month_2_amt'];
			$month3 = $value['month_3_amt'];
			$month4 = $value['month_4_amt'];
			$month5 = $value['month_5_amt'];
			$month6 = $value['month_6_amt'];
			$month7 = $value['month_7_amt'];
			$month8 = $value['month_8_amt'];
			$month9 = $value['month_9_amt'];
			$month10 = $value['month_10_amt'];
			$month11 = $value['month_11_amt'];
			$month12 = $value['month_12_amt'];
	  
			$sheet->setCellValue('D'.$row, "=ROUND(".$month1.", 1)");
			$sheet->setCellValue('E'.$row, "=ROUND(".$month2.", 1)");
			$sheet->setCellValue('F'.$row, "=ROUND(".$month3.", 1)");
			$sheet->setCellValue('G'.$row, "=ROUND(".$month4.", 1)");
			$sheet->setCellValue('H'.$row, "=ROUND(".$month5.", 1)");
			$sheet->setCellValue('I'.$row, "=ROUND(".$month6.", 1)");
			$sheet->setCellValue('J'.$row, '=ROUND(SUM('.$first_half.'), 1)');
			$sheet->setCellValue('K'.$row, "=ROUND(".$month7.", 1)");
			$sheet->setCellValue('L'.$row, "=ROUND(".$month8.", 1)");
			$sheet->setCellValue('M'.$row, "=ROUND(".$month9.", 1)");
			$sheet->setCellValue('N'.$row, "=ROUND(".$month10.", 1)");
			$sheet->setCellValue('O'.$row, "=ROUND(".$month11.", 1)");
			$sheet->setCellValue('P'.$row, "=ROUND(".$month12.", 1)");
			$sheet->setCellValue('Q'.$row, '=ROUND(SUM('.$second_half.'), 1)');
			$sheet->setCellValue('R'.$row, '=ROUND(SUM('.$whole_total.'), 1)');
			if ($key == 0 || $key == sizeof($tradings_datas) - 1) {
				$DTotal .= 'D'.$row.':';
				$ETotal .= 'E'.$row.':';
				$FTotal .= 'F'.$row.':';
				$GTotal .= 'G'.$row.':';
				$HTotal .= 'H'.$row.':';
				$ITotal .= 'I'.$row.':';
				$JTotal .= 'J'.$row.':';
				$KTotal .= 'K'.$row.':';
				$LTotal .= 'L'.$row.':';
				$MTotal .= 'M'.$row.':';
				$NTotal .= 'N'.$row.':';
				$OTotal .= 'O'.$row.':';
				$PTotal .= 'P'.$row.':';
				$QTotal .= 'Q'.$row.':';
				$RTotal .= 'R'.$row.':';
			}
		}
		$DTotal = substr($DTotal, 0, -1);
		$ETotal = substr($ETotal, 0, -1);
		$FTotal = substr($FTotal, 0, -1);
		$GTotal = substr($GTotal, 0, -1);
		$HTotal = substr($HTotal, 0, -1);
		$ITotal = substr($ITotal, 0, -1);
		$JTotal = substr($JTotal, 0, -1);
		$KTotal = substr($KTotal, 0, -1);
		$LTotal = substr($LTotal, 0, -1);
		$MTotal = substr($MTotal, 0, -1);
		$NTotal = substr($NTotal, 0, -1);
		$OTotal = substr($OTotal, 0, -1);
		$PTotal = substr($PTotal, 0, -1);
		$QTotal = substr($QTotal, 0, -1);
		$RTotal = substr($RTotal, 0, -1);
		//echo $DTotal;exit;
		$totalRow = $row + 1;
		foreach ($colName as $key => $value) {
			$objWorkSheet->getActiveSheet()->getStyle($colName[$key].$totalRow)->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getStyle($colName[$key].$totalRow)->getFont()->setBold(true);
		}
		$objWorkSheet->getActiveSheet()->mergeCells('A'.$totalRow.':C'.$totalRow);
		$objWorkSheet->getActiveSheet()->getStyle("A".$totalRow)->applyFromArray($centerHorizontalAlign_style);
		$sheet->setCellValue('A'.$totalRow, __('累計'));
		$sheet->setCellValue('D'.$totalRow, '=ROUND(SUM('.$DTotal.'), 1)');
		$sheet->setCellValue('E'.$totalRow, '=ROUND(SUM('.$ETotal.'), 1)');
		$sheet->setCellValue('F'.$totalRow, '=ROUND(SUM('.$FTotal.'), 1)');
		$sheet->setCellValue('G'.$totalRow, '=ROUND(SUM('.$GTotal.'), 1)');
		$sheet->setCellValue('H'.$totalRow, '=ROUND(SUM('.$HTotal.'), 1)');
		$sheet->setCellValue('I'.$totalRow, '=ROUND(SUM('.$ITotal.'), 1)');
		$sheet->setCellValue('J'.$totalRow, '=ROUND(SUM('.$JTotal.'), 1)');
		$sheet->setCellValue('K'.$totalRow, '=ROUND(SUM('.$KTotal.'), 1)');
		$sheet->setCellValue('L'.$totalRow, '=ROUND(SUM('.$LTotal.'), 1)');
		$sheet->setCellValue('M'.$totalRow, '=ROUND(SUM('.$MTotal.'), 1)');
		$sheet->setCellValue('N'.$totalRow, '=ROUND(SUM('.$NTotal.'), 1)');
		$sheet->setCellValue('O'.$totalRow, '=ROUND(SUM('.$OTotal.'), 1)');
		$sheet->setCellValue('P'.$totalRow, '=ROUND(SUM('.$PTotal.'), 1)');
		$sheet->setCellValue('Q'.$totalRow, '=ROUND(SUM('.$QTotal.'), 1)');
		$sheet->setCellValue('R'.$totalRow, '=ROUND(SUM('.$RTotal.'), 1)');

		$fileName ='Payment History_'.$target_year;
		$this->PhpExcel->output($fileName.'.xlsx');
	}
	/**public function SearchListBA($term_id,$target_year,$loginId,$head_dept_id){

		$cache_name = 'internal_payment_'.$term_id.'_'.$target_year.'_'.$loginId;
		$cache_data = Cache::read($cache_name);
		
		$internal_payment_data = $cache_data['tradings_datas'];

		$searchBAlist = [];
		$searchDeslist = [];
		
		foreach ($internal_payment_data as $key => $value) {
			
			$searchBAlist[$value['ba_code']] = $value['ba_name'];
			$searchDeslist[$value['destination']] = $value['destination_name'];
		}

		$cache_name = 'internal_payment_search_dropdown'.$term_id.'_'.$target_year.'_'.$loginId.'_'.$head_dept_id;
		$searchDropDownlist = array('BA' => $searchBAlist, 'Destination' => $searchDeslist);
		Cache::write($cache_name, $searchDropDownlist);

		return $searchDropDownlist;
	}**/
}
