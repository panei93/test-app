<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * BrmBudgetSummary Controller
 *
 * @property BrmBudgetSummary $BrmBudgetSummary
 * @property PaginatorComponent $Paginator
 */
class BrmBudgetSummaryController extends AppController
{

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

	public $uses = array('BrmBudgetSummary','BrmForecastSummary','BrmTerm','BrmBudget', 'BrmActualResultSummary','BrmAccount','BrmSaccount', 'Layer','BrmLogistic');

	/**
	 * getBudgetSummary method
	 *
	 * @author Pan Ei Phyo (20220208)
	 * @return data array
	 */
	public function getBudgetSummary($term_id, $head_dept_code, $dlayer_code, $budget_layer_code, $type, $start_year, $end_year)
	{	
		#Prepare array to return
		$data 		= [];
		$group_code = '';
		
		$notrading_hqs 	= Setting::TRADING_DISABLE_HQS;
		$disbudget_acc 	= Setting::BUDGET_DISABLE_ACCS;
		$first_6_months	= Setting::FIRST_HALF;
		$sale_accs		= array_slice($disbudget_acc, 0, 2); #売上高、売上原価
		$term_year      = range($start_year, $end_year);
		$login_id = $_SESSION['LOGIN_ID'];
		
		$group = array('target_year','logistic_index_no');
		$conditions = array();
		$conditions['brm_term_id'] = $term_id;
		
		if(!empty($head_dept_code)){
		 	$conditions['topLayer.layer_code']   = $head_dept_code;
			array_push($group,'topLayer.layer_code');
		}
		if(!empty($dlayer_code)){
		 	$conditions['middleLayer.layer_code']  = $dlayer_code;
			array_push($group,'middleLayer.layer_code');
		}
		if(!empty($budget_layer_code)){
		 	$conditions['bottomLayer.layer_code'] = $budget_layer_code;
			array_push($group,'bottomLayer.layer_code');
		}

		if ($type == 'FBD') {
			array_push($group, 'brm_account_id_2');
			$group_code = '02';
			$gp_code = '02';
			
		} elseif ($type == 'PL') {
			array_push($group, 'brm_account_id_2');
			array_push($group, 'brm_account_id_6');
			$group_code = '06';
			$gp_code = '02';
			
		} else {
			array_push($group, 'brm_account_id_5');
			$group_code = '05';
			$gp_code = '05';
			
		}
		$today_date     = date("Y-m-d");
		$acc_formulas 	= $this->getAccounts($gp_code);
		$total_accs 	= $this->getTotalAccounts($gp_code);
       	
		#Get summary data from view
		$budget_summ = $this->BrmBudgetSummary->find('all',array(
			'fields' => array('BrmBudgetSummary.target_year','topLayer.layer_code as hlayer_code','topLayer.name_jp as hlayer_name','middleLayer.layer_code as dlayer_code','middleLayer.name_jp as dlayer_name','bottomLayer.layer_code as blayer_code','bottomLayer.name_jp as blayer_name','BrmBudgetSummary.layer_code','BrmBudgetSummary.logistic_index_no','BrmBudgetSummary.brm_account_id_2','BrmBudgetSummary.brm_account_id_5','BrmBudgetSummary.brm_account_id_6','BrmBudgetSummary.brm_account_name_jp_2','BrmBudgetSummary.brm_account_name_jp_5','BrmBudgetSummary.brm_account_name_jp_6',
				#Add SUM on purpose, because i want to separate amounts in [0] index.
				'SUM(month_1_amt) as month_1_amt',
				'SUM(month_2_amt) as month_2_amt',
				'SUM(month_3_amt) as month_3_amt',
				'SUM(month_4_amt) as month_4_amt',
				'SUM(month_5_amt) as month_5_amt',
				'SUM(month_6_amt) as month_6_amt',
				'SUM(first_half) as first_half',
				'SUM(month_7_amt) as month_7_amt',
				'SUM(month_8_amt) as month_8_amt',
				'SUM(month_9_amt) as month_9_amt',
				'SUM(month_10_amt) as month_10_amt',
				'SUM(month_11_amt) as month_11_amt',
				'SUM(month_12_amt) as month_12_amt',
				'SUM(second_half) as second_half',
				'SUM(whole_total) as whole_total',
			),
			'joins' => array(
		    	array('table' => 'layers',
					  'alias' => 'bottomLayer',
					  'type' => 'LEFT',
					  'conditions' => array(
						'BrmBudgetSummary.layer_code = bottomLayer.layer_code AND bottomLayer.flag = 1 AND bottomLayer.to_date >= '.$today_date)
				),
				array('table' => 'layers',
					  'alias' => 'middleLayer',
					  'type' => 'LEFT',
					  'conditions' => array(
						'middleLayer.flag = 1',
						'middleLayer.to_date >= '=> $today_date,
						'middleLayer.type_order' => Setting::LAYER_SETTING['middleLayer'],
					)
				),
				array('table' => 'layers',
					  'alias' => 'topLayer',
					  'type' => 'LEFT',
					  'conditions' => array(
						'topLayer.flag = 1',
						'topLayer.to_date >= '=> $today_date,
						'topLayer.type_order' => Setting::LAYER_SETTING['topLayer'],
					)
				),
		    ),
			'conditions' => $conditions,
			'group' => $group,
			'order' => array (
				'topLayer.layer_code ASC','middleLayer.layer_code ASC','BrmBudgetSummary.layer_code ASC','BrmBudgetSummary.logistic_index_no ASC','BrmBudgetSummary.brm_account_id_2 ASC','BrmBudgetSummary.brm_account_id_5 ASC','BrmBudgetSummary.brm_account_id_6 ASC','BrmBudgetSummary.target_year ASC'
			)
		)); 
		
		#Get summary data from view
		$forecast_summ = $this->BrmForecastSummary->find('all',array(
			'fields' => array('BrmForecastSummary.target_year','topLayer.layer_code as hlayer_code','topLayer.name_jp as hlayer_name','middleLayer.layer_code as dlayer_code','middleLayer.name_jp as dlayer_name','bottomLayer.layer_code as blayer_code','bottomLayer.name_jp as blayer_name','BrmForecastSummary.layer_code','BrmForecastSummary.logistic_index_no','BrmForecastSummary.brm_account_id_2','BrmForecastSummary.brm_account_id_5','BrmForecastSummary.brm_account_id_6','BrmForecastSummary.brm_account_name_jp_2','BrmForecastSummary.brm_account_name_jp_5','BrmForecastSummary.brm_account_name_jp_6',
				#Add SUM on purpose, because i want to separate amounts in [0] index.
				'SUM(month_1_amt) as month_1_amt',
				'SUM(month_2_amt) as month_2_amt',
				'SUM(month_3_amt) as month_3_amt',
				'SUM(month_4_amt) as month_4_amt',
				'SUM(month_5_amt) as month_5_amt',
				'SUM(month_6_amt) as month_6_amt',
				'SUM(first_half) as first_half',
				'SUM(month_7_amt) as month_7_amt',
				'SUM(month_8_amt) as month_8_amt',
				'SUM(month_9_amt) as month_9_amt',
				'SUM(month_10_amt) as month_10_amt',
				'SUM(month_11_amt) as month_11_amt',
				'SUM(month_12_amt) as month_12_amt',
				'SUM(second_half) as second_half',
				'SUM(whole_total) as whole_total',
			),
			'joins' => array(
            	array('table' => 'layers',
					  'alias' => 'bottomLayer',
					  'type' => 'LEFT',
					  'conditions' => array(
						'BrmForecastSummary.layer_code = bottomLayer.layer_code AND bottomLayer.flag = 1 AND bottomLayer.to_date >= '.$today_date)
				),
				array('table' => 'layers',
					  'alias' => 'middleLayer',
					  'type' => 'LEFT',
					  'conditions' => array(
						'middleLayer.flag = 1',
						'middleLayer.to_date >= '=> $today_date,
						'middleLayer.type_order' => Setting::LAYER_SETTING['middleLayer'],
					)
				),
				array('table' => 'layers',
					  'alias' => 'topLayer',
					  'type' => 'LEFT',
					  'conditions' => array(
						'topLayer.flag = 1',
						'topLayer.to_date >= '=> $today_date,
						'topLayer.type_order' => Setting::LAYER_SETTING['topLayer'],
					)
				),
            ),
			'conditions' => $conditions,
			'group' => $group,
			'order' => array (
				'topLayer.layer_code ASC','middleLayer.layer_code ASC','BrmForecastSummary.layer_code ASC','BrmForecastSummary.logistic_index_no ASC','BrmForecastSummary.brm_account_id_2 ASC','BrmForecastSummary.brm_account_id_5 ASC','BrmForecastSummary.brm_account_id_6 ASC','BrmForecastSummary.target_year ASC'
			)
		));
		
		#Get summary datas with hq, dept, ba, index and acc
		$summarized = $this->SummaryFrame($term_id, $term_year, $head_dept_code, $dlayer_code, $budget_layer_code, $start_year, $end_year, $notrading_hqs, $sale_accs);
		$summary = $summarized[0];
		$budget_zero = $summarized[1];
		
		#Get result summary data
		$result_summ = $this->getResultSummary($term_id, $start_year, $group_code, $head_dept_code, $dlayer_code, $budget_layer_code);
		
		$result_data = $result_summ['results'];
		$result_cols = $result_summ['columns'];

		$last_budget = $this->getLastYearBudget($term_id, $start_year, $group_code, $head_dept_code, $dlayer_code, $budget_layer_code);
		#Get last year result for summary last year column
		if($type == 'SM') {
			$last_result = $this->getLastYearResult($term_id, $start_year, $group_code, $head_dept_code, $dlayer_code, $budget_layer_code);
		}
		#fill 0 for default results
		$result_zero = array_fill_keys(array_values($result_cols), 0);

		$budget_datas = array_merge($forecast_summ,$budget_summ,$summary);
		#loop through budget data
		foreach ($budget_datas as $budget_data) {
			$each_budget 	= (!empty($budget_data['BrmBudgetSummary'])) ? $budget_data['BrmBudgetSummary'] : ((!empty($budget_data['BrmForecastSummary'])) ? $budget_data['BrmForecastSummary'] : $budget_data['Summary']);
			
			$hlayer_name    = $budget_data['topLayer']['hlayer_name'];
			$hlayer_code    = $budget_data['topLayer']['hlayer_code'];
			$department 	= $budget_data['middleLayer']['dlayer_name'];
			$dlayer_code    = $budget_data['middleLayer']['dlayer_code'];
			$layer_name_jp	= $budget_data['bottomLayer']['blayer_name'];
			$year 			= $each_budget['target_year'];
			$layer_code     = $each_budget['layer_code'];
			$index_name = trim($each_budget['logistic_index_no']);
			$accname_2 	= $each_budget['brm_account_name_jp_2'];
			$accname_5 	= $each_budget['brm_account_name_jp_5'];
			$accname_6 	= $each_budget['brm_account_name_jp_6'];
			$accid_6 	= $each_budget['brm_account_id_6'];
			$accid_2 	= $each_budget['brm_account_id_2'];
			$accid_5 	= $each_budget['brm_account_id_5'];
			$main_acc 	= (($type == 'PL') || ($type == 'FBD')) ? $accname_2 : $accname_5;
			$main_id 	= ($type == 'PL')? $accid_6 : (($type == 'FBD') ? $accid_2 : $accid_5);
			$sub_acc 	= ($type == 'PL')? $accname_6 : (($type == 'FBD') ? $accname_2 : $accname_5);

			$ba_name = $layer_code.' / '.$layer_name_jp;
			$index_name = (in_array($hlayer_name, Setting::TRADING_DISABLE_HQS)) ? '' : $index_name;
			#Get autochange accounts for auto sum
			$autosum_accs = explode(',', $acc_formulas[$main_acc]);
			
			$monthly_budget = $budget_data[0];
			$monthly_result = $result_data[$hlayer_code][$dlayer_code][$layer_code][$index_name][$sub_acc];
			
			if (!empty($result_cols) && $year==$start_year) {
				if (count($result_data[$hlayer_code][$dlayer_code][$layer_code][$index_name]) >= 1) {
					unset($result_data[$hlayer_code][$dlayer_code][$layer_code][$index_name][$sub_acc]);
				} else {
					unset($result_data[$hlayer_code][$dlayer_code][$layer_code][$index_name]);
				}
				if (empty($monthly_result)) {
					$monthly_result = $result_zero;
				}else {
					$monthly_result = array_replace($result_zero, $monthly_result);
				}
				foreach ($monthly_result as $rs_month => $rs_amount) {
					$bg_value = $monthly_budget[$rs_month];
					$rs_value = $rs_amount;

					$diff = $rs_value - $bg_value;
					$monthly_budget[$rs_month] = $rs_amount;
					if (in_array($rs_month, $first_6_months)) {
						$monthly_budget['first_half'] += $diff;
					} else {
						$monthly_budget['second_half'] += $diff;
					}
					$monthly_budget['whole_total'] += $diff;
				}
				$monthly_budget = array_replace($budget_zero, $monthly_budget);
			}
			
			if ($main_acc != '') {
				if ($type == 'FBD') { #For FBD Page
					$data[$accname_2][$start_year]['forecast'] += 0;
					$data[$accname_2][$start_year]['budget'] = $last_budget[$accname_2];
					if ($year == $start_year) {
						$data[$accname_2][$year]['forecast'] += $monthly_budget['whole_total'];
						$data[$accname_2][$year]['budget'] = $last_budget[$accname_2];
					} else {
						$data[$accname_2][$year] += $monthly_budget['whole_total'];
					}

					if (!empty($autosum_accs)) {
						foreach ($autosum_accs as $each_totalacc) {
							if ($each_totalacc != '') {
								if ($year == $start_year) {
									$data[$each_totalacc][$year]['forecast'] += $monthly_budget['whole_total'];
									$data[$each_totalacc][$year]['budget'] = $last_budget[$each_totalacc];
								} else {
									$data[$each_totalacc][$year] += $monthly_budget['whole_total'];
								}
							}
						}
					}
				} elseif ($type == 'PL') { #For PL Summary Page
					#loop and insert data monthly
					foreach ($monthly_budget as $month_name => $month_value) {
						
						if (!in_array($accname_2, $total_accs)) {
							#If HQ has trading plan form
							if ((!in_array($hlayer_name, $notrading_hqs)) && (in_array($accname_2, $sale_accs)) ) {
								if($index_name == '') $index_name = '取引無し';
								$data[$accname_2]['data'][$accname_6]['sub_data'][$hlayer_name]['hlayer_data'][$department]['dlayer_data'][$ba_name]['layer_data'][$index_name][$year][$month_name] += $month_value;

							}
							$data[$accname_2]['data'][$accname_6]['sub_data'][$hlayer_name]['hlayer_data'][$department]['dlayer_data'][$ba_name]['layer_total'][$year][$month_name] += $month_value;
							$data[$accname_2]['data'][$accname_6]['sub_data'][$hlayer_name]['hlayer_data'][$department]['dlayer_total'][$year][$month_name] += $month_value;
							$data[$accname_2]['data'][$accname_6]['sub_data'][$hlayer_name]['hlayer_total'][$year][$month_name] += $month_value;
							$data[$accname_2]['data'][$accname_6]['sub_total'][$year][$month_name] += $month_value;
						}

						$data[$accname_2]['total'][$year][$month_name] += $month_value;

						if (!empty($autosum_accs)) {
							foreach ($autosum_accs as $each_totalpl) {
								if ($each_totalpl != '') {
									$data[trim($each_totalpl)]['total'][$year][$month_name] += $month_value;
								}
							}
						}

					}
				} else { #For Summary Page
					if ($year == $start_year) {
						$data[$accname_5][($year-1)]['result'] = $last_result[$accname_5];
						$data[$accname_5][$year]['budget'] = $last_budget[$accname_5];
						$data[$accname_5][$year]['forecast'] += $monthly_budget['whole_total'];
						$data[$accname_5][$year]['f_b_diff'] = $data[$accname_5][$year]['forecast'] - $data[$accname_5][$year]['budget'];
					} else {
						
						$data[$accname_5][$year]['first_half'] += $monthly_budget['first_half'];
						$data[$accname_5][$year]['whole_total'] += $monthly_budget['whole_total'];
						if($year == ($start_year+1)) {
							$data[$accname_5][$year]['f_b_diff'] = $data[$accname_5][$year]['whole_total'] - $data[$accname_5][($year-1)]['forecast'];
						}else {
							$data[$accname_5][$year]['f_b_diff'] = $data[$accname_5][$year]['whole_total'] - $data[$accname_5][($year-1)]['whole_total'];
						}
					}
					
					if (!empty($autosum_accs)) {
						foreach ($autosum_accs as $each_totalacc) {
							if ($each_totalacc != '') {
								$data[trim($each_totalacc)]['type'] = 1;
								if ($year == $start_year) {
									$data[trim($each_totalacc)][($start_year-1)]['result'] = $last_result[trim($each_totalacc)];
									$data[trim($each_totalacc)][$year]['budget'] = $last_budget[trim($each_totalacc)];
									$data[trim($each_totalacc)][$year]['forecast'] += $monthly_budget['whole_total'];
									$data[trim($each_totalacc)][$year]['f_b_diff'] =$data[trim($each_totalacc)][$year]['forecast'] - $data[trim($each_totalacc)][$year]['budget'];
								} else {
									
									$data[trim($each_totalacc)][$year]['first_half'] += $monthly_budget['first_half'];
									$data[trim($each_totalacc)][$year]['whole_total'] += $monthly_budget['whole_total'];
									if($year == ($start_year+1)) {
										$data[trim($each_totalacc)][$year]['f_b_diff'] = $data[trim($each_totalacc)][$year]['whole_total'] - $data[trim($each_totalacc)][($year-1)]['forecast'];
									}else {
										$data[trim($each_totalacc)][$year]['f_b_diff'] = $data[trim($each_totalacc)][$year]['whole_total'] - $data[trim($each_totalacc)][($year-1)]['whole_total'];
									}
								}
							}
						}
					}
				}
			}
		}
		#Order array by accs
		if(!empty($data)){
			$data = array_replace($acc_formulas, $data);
		}
		
		return $data;
	}
	/**
	 * SummaryFrame method
	 *
	 * @author 
	 * @return summ_res array
	 */
	public function SummaryFrame($term_id, $term_year, $head_dept_code = '', $dlayer_code = '', $budget_layer_code = '', $start_year, $end_year, $notrading_hqs, $sale_accs) {
		#conditions for layer data
		$con .= (!empty($head_dept_code))? " and topLayer.layer_code = '".$head_dept_code."' " : "";
		$con .= (!empty($dlayer_code))? " and middleLayer.layer_code = '".$dlayer_code."' " : "";
		$con .= (!empty($budget_layer_code))? " and bottomLayer.layer_code = '".$budget_layer_code."' " : "";

		#column array (eg. month_1_amt)
		for($i=0;$i<15;$i++) {
			$column_name[] = ($i == 6) ? 'first_half' : ((($i == 13) ? 'second_half' : ((($i == 14)? 'whole_total' : (($i < 6) ? 'month_'.($i+1).'_amt' : 'month_'.($i).'_amt')))));
		}
		#date start_month and today_date to check layer_data
		$Common = new CommonController();
		$start_month =  date("Y-m-d", strtotime($Common->getMonth($start_year, $term_id, 'start')));
		$today_date = date("Y-m-d");

		#fill 0 for default summary frame amt
		$budget_zero = array_fill_keys(array_values($column_name), 0);
		
		foreach ($term_year as $i => $year) {
			$sql .= "(SELECT 
					brm_terms.budget_year + ".$i." as budget_year,
					brm_terms.budget_end_year,
					topLayer.layer_code as hlayer_code,
					topLayer.name_jp as hlayer_name,
					middleLayer.layer_code as dlayer_code,
					middleLayer.name_jp as dlayer_name,
					bottomLayer.layer_code as blayer_code,
					bottomLayer.name_jp as blayer_name,
					logistic.index_name
					FROM brm_terms
					LEFT JOIN layers as bottomLayer ON bottomLayer.flag = 1 AND bottomLayer.to_date >= '".$today_date."' AND bottomLayer.type_order = ".Setting::LAYER_SETTING['bottomLayer']."
					LEFT JOIN layers as middleLayer ON middleLayer.flag = 1 AND middleLayer.to_date >= '".$today_date."' AND middleLayer.type_order = ".Setting::LAYER_SETTING['middleLayer']."
					LEFT JOIN layers as topLayer ON topLayer.flag = 1 AND topLayer.to_date >= '".$today_date."' AND topLayer.type_order = ".Setting::LAYER_SETTING['topLayer']."
					LEFT JOIN 
					(SELECT index_name,target_year,logistic_order,brm_logistics.layer_code,flag FROM `brm_logistics`) as logistic ON (logistic.flag = '1' AND logistic.layer_code=bottomLayer.layer_code AND logistic.target_year between brm_terms.budget_year and brm_terms.budget_end_year)
					WHERE brm_terms.flag=1 ".$con."  and brm_terms.budget_year = '".$start_year."' and brm_terms.budget_end_year ='".$end_year."'
					GROUP BY 
					brm_terms.budget_year,brm_terms.budget_end_year,topLayer.layer_code,middleLayer.layer_code,bottomLayer.layer_code, logistic.index_name
					ORDER BY
					brm_terms.budget_year,
					brm_terms.budget_end_year,
					topLayer.layer_code,
					middleLayer.layer_code,
					bottomLayer.layer_code,
					logistic.logistic_order)";
			$sql = ($year != end($term_year)) ? $sql.'UNION' : $sql.";";	
		}
		#ba data
		$layer_data = array_column($this->BrmBudgetSummary->query($sql), '0');
		#account data with groupcode-02,05,06
		$acc = $this->BrmBudgetSummary->query("SELECT brm_account_id_2, brm_account_name_jp_2,brm_account_id_5, brm_account_name_jp_5,brm_account_id_6, brm_account_name_jp_6 FROM brm_saccounts
            LEFT JOIN 
                (SELECT accname2.name_jp as brm_account_name_jp_2, brm_account_pairs.brm_account_id as brm_account_id_2, brm_account_pairs.account_code as account_code, brm_account_pairs.group_code as group_code
                  FROM `brm_account_pairs`
                  INNER JOIN `brm_accounts` as accname2
                  ON (`brm_account_pairs`.`brm_account_id` = `accname2`.`id` AND
                  `accname2`.`flag` = '1')
                ) as acc2 ON (
                `acc2`.`account_code` = `brm_saccounts`.`account_code` AND
                `acc2`.`group_code` = '02'
            )LEFT JOIN 
                (SELECT accname5.name_jp as brm_account_name_jp_5, brm_account_pairs.brm_account_id as brm_account_id_5, brm_account_pairs.account_code as account_code, brm_account_pairs.group_code as group_code
                  FROM `brm_account_pairs`
                  INNER JOIN `brm_accounts` as accname5
                  ON (`brm_account_pairs`.`brm_account_id` = `accname5`.`id` AND
                  `accname5`.`flag` = '1')
                ) as acc5 ON (
                `acc5`.`account_code` = `brm_saccounts`.`account_code` AND
                `acc5`.`group_code` = '05'
            )LEFT JOIN 
                (SELECT accname6.name_jp as brm_account_name_jp_6, brm_account_pairs.brm_account_id as brm_account_id_6, brm_account_pairs.account_code as account_code, brm_account_pairs.group_code as group_code
                  FROM `brm_account_pairs`
                  INNER JOIN `brm_accounts` as accname6
                  ON (`brm_account_pairs`.`brm_account_id` = `accname6`.`id` AND
                  `accname6`.`flag` = '1')
                ) as acc6 ON (
                `acc6`.`account_code` = `brm_saccounts`.`account_code` AND
                `acc6`.`group_code` = '06'
            ) GROUP BY brm_account_id_2,brm_account_id_5,brm_account_id_6"
        );
		
        #prepare summary array with acc data and ba data
        $summary = [];$summ_res = [];$index_null = [];
        foreach ($acc as $acclist) {
        	foreach ($layer_data as $layer_list) {
        		$index_name = $layer_list['index_name'];
				$summary['Summary']['target_year']  = $layer_list['budget_year'];
				$summary['topLayer']['hlayer_name']    = $layer_list['hlayer_name'];
				$summary['topLayer']['hlayer_code']    = $layer_list['hlayer_code'];
				$summary['middleLayer']['dlayer_name']    = $layer_list['dlayer_name'];
				$summary['middleLayer']['dlayer_code']    = $layer_list['dlayer_code'];
				$summary['bottomLayer']['blayer_name']    = $layer_list['blayer_name'];
				$summary['bottomLayer']['blayer_code']    = $layer_list['blayer_code'];
				$summary['Summary']['layer_code']   = $layer_list['blayer_code'];
				$summary['Summary']['brm_account_id_2']      = $acclist['acc2']['brm_account_id_2'];
                $summary['Summary']['brm_account_id_5']      = $acclist['acc5']['brm_account_id_5'];
                $summary['Summary']['brm_account_id_6']      = $acclist['acc6']['brm_account_id_6'];
                $summary['Summary']['brm_account_name_jp_2'] = $acclist['acc2']['brm_account_name_jp_2'];
                $summary['Summary']['brm_account_name_jp_5'] = $acclist['acc5']['brm_account_name_jp_5'];
                $summary['Summary']['brm_account_name_jp_6'] = $acclist['acc6']['brm_account_name_jp_6'];
                $summary['Summary']['logistic_index_no'] = '';
                $summary[0] = $budget_zero;
                if ((!in_array($layer_list['hlayer_name'], $notrading_hqs)) && (in_array($acclist['acc2']['brm_account_name_jp_2'], $sale_accs)) ) {#for 売上高、売上原価
					if(!in_array($summary, $index_null)) {
						array_push($index_null, $summary);
						array_push($summ_res, $summary);
					}
					$summary['Summary']['logistic_index_no'] = $index_name;
				}
               	if(!in_array($summary, $summ_res)) array_push($summ_res, $summary);
            }
        } 
        return array($summ_res, $budget_zero);
	}
	/**
	 * getResultSummary method
	 *
	 * @author Pan Ei Phyo (20220208)
	 * @return data array
	 */
	public function getResultSummary($term_id, $s_year, $group_code, $head_dept_code='', $dlayer_code='', $budget_layer_code='')
	{
		$Common = new CommonController();
		#Prepare array
		$result_data = [];
		$result_colm = [];
		$conditions = [];

		#Get forecast period(eg: 2020-05) to show actual result data till to this period
		$forecast_period = $this->BrmTerm->getForecastPeriod($term_id);
		#Get startmonth of term
		$start_month = $Common->getMonth($s_year, $term_id, 'start');

		$getAccountCode = $this->BrmSaccount->find('list',array(
			'fields'=>array('BrmSaccount.account_code'),
			'conditions' =>array(
				'BrmSaccount.brm_account_id In (1,2)',
				'BrmSaccount.flag = 1'
			),
			'group' => array('BrmSaccount.account_code')
		));
		
		#Define conditions check
		if ($head_dept_code != '') {
			$conditions['topLayer.layer_code'] = $head_dept_code;
		}
		if ($dlayer_code != '') {
			$conditions['middleLayer.layer_code'] = $dlayer_code;
		}
		if ($budget_layer_code != '') {
			$conditions['BrmActualResultSummary.layer_code'] = $budget_layer_code;
		}
		$conditions['BrmActualResultSummary.target_month >='] = $start_month;
		$conditions['BrmActualResultSummary.target_month <='] = $forecast_period;
		$today_date = date("Y/m/d");
		
		#Set virtual fields for cakephp query
		$this->BrmActualResultSummary->virtualFields['result_total'] = 'SUM(amount)';
		
		#Get result data by list
		$results = $this->BrmActualResultSummary->find('all', array(
			'fields' => array('topLayer.layer_code as hlayer_code','topLayer.name_jp as hlayer_name','middleLayer.layer_code as dlayer_code','bottomLayer.layer_code as blayer_code','BrmLogistic.index_name','BrmAccount.name_jp','BrmActualResultSummary.target_month','BrmActualResultSummary.result_total'),
			'joins' => array(
				array('table' => 'layers',
					  'alias' => 'bottomLayer',
					  'type' => 'LEFT',
					  'conditions' => array(
						'BrmActualResultSummary.layer_code = bottomLayer.layer_code AND bottomLayer.flag = 1 AND bottomLayer.to_date >= '.$today_date)
				),
				array('table' => 'layers',
					  'alias' => 'middleLayer',
					  'type' => 'LEFT',
					  'conditions' => array(
						'middleLayer.flag = 1',
						'middleLayer.to_date >= '=> $today_date,
						'middleLayer.type_order' => Setting::LAYER_SETTING['middleLayer'],
					)
				),
				array('table' => 'layers',
					  'alias' => 'topLayer',
					  'type'  => 'LEFT',
					  'conditions' => array(
						'topLayer.flag = 1',
						'topLayer.to_date >= '=> $today_date,
						'topLayer.type_order' => Setting::LAYER_SETTING['topLayer'],
					)
				),
				array(
					'alias' => 'BrmLogistic',
					'table' => sprintf("(select distinct index_name,index_no,layer_code,target_year,flag from `brm_logistics` where flag=1 group by layer_code,target_year,index_no) "),
					'type' => 'left',
					'conditions' => array(
						'OR' => array(
						 	0 => 'BrmActualResultSummary.transaction_key = BrmLogistic.index_no',
							 'AND' => array(
							 	0 => 'BrmLogistic.index_no = ""',
							 	1 => 'BrmActualResultSummary.transaction_key = BrmLogistic.index_name'
							),
						),
						1 => array('BrmActualResultSummary.layer_code = BrmLogistic.layer_code'),
						2 => array('BrmLogistic.flag = 1'),
						3 => array('BrmLogistic.target_year ='.$s_year),
						4 => array('BrmActualResultSummary.account_code' =>$getAccountCode),
					),
				),
				array(
					'alias' => 'AccountPairModel',
					'table' => 'brm_account_pairs',
					'type' => 'left',
					'conditions' => array(
						0 => 'AccountPairModel.account_code = BrmActualResultSummary.account_code',
						1 => 'AccountPairModel.group_code = '.$group_code,
					),
				),
				array(
					'alias' => 'BrmAccount',
					'table' => 'brm_accounts',
					'type' => 'left',
					'conditions' => array(
						0 => 'BrmAccount.id = AccountPairModel.brm_account_id',
						1 => 'BrmAccount.flag = 1',
					),
				),
			),
			'conditions' => $conditions,
			'group' => array('topLayer.layer_code','middleLayer.layer_code','bottomLayer.layer_code','BrmLogistic.index_name','AccountPairModel.brm_account_id','BrmActualResultSummary.target_month'),
			'order' => 'BrmActualResultSummary.target_month ASC','BrmActualResultSummary.layer_code ASC','AccountPairModel.brm_account_id ASC',
		));
		
		$month = $start_month;
		$count = 1;
		while ($month <= $forecast_period) {
			$column_name = 'month_'.$count.'_amt';
			$result_colm[$month] = $column_name;

			$count++;
			$month = date("Y-m", strtotime($month. "last day of + 1 months"));
		}
		
		#Loop through result list and prepare data to return
		foreach ($results as $result) {
			$hlayer_code 	= $result['topLayer']['hlayer_code'];
			$dlayer_code 	= $result['middleLayer']['dlayer_code'];
			$layer_code 	= $result['bottomLayer']['blayer_code'];
			$index 	        =  trim($result['BrmLogistic']['index_name']);
			$account        = $result['BrmAccount']['name_jp'];
			$target_month   = $result['BrmActualResultSummary']['target_month'];
			$result_total   = $result['BrmActualResultSummary']['result_total'];

			$column = $result_colm[$target_month];

			$monthly_amt = (!empty($result_total)) ? $result_total : 0;
			$hlayer_name       = $result['topLayer']['hlayer_name'];
			if(in_array($hlayer_name, Setting::TRADING_DISABLE_HQS)) {
				$index = '';
				$result_data[$hlayer_code][$dlayer_code][$layer_code][$index][$account][$column] += $monthly_amt;
			}else {
				$result_data[$hlayer_code][$dlayer_code][$layer_code][$index][$account][$column] = $monthly_amt;
			}
		}
		$data = array('results'=>$result_data, 'columns'=>array_values($result_colm));
		return $data;
	}

	/**
	 * getLastYearBudget method
	 *
	 * @author Pan Ei Phyo (20220208)
	 * @return data array
	 */
	public function getLastYearBudget($term_id, $s_year, $group_code, $head_dept_code='', $dlayer_code='', $budget_layer_code='')
	{
		$Common     = new CommonController();
		$today_date = date("Y/m/d");
		#Prepare array
		$budget_data = [];
		$conditions = [];

		$acc_formulas 	= $this->getAccounts($group_code);
		$total_accs 	= $this->getTotalAccounts($group_code);

		#Get startmonth of term
		$start_month = $Common->getMonth($s_year, $term_id, 'start');
		$end_month = $Common->getMonth($s_year, $term_id, 'end');

		#Define conditions check
		if ($head_dept_code != '') {
			$conditions['topLayer.layer_code'] = $head_dept_code;
			$group = array('topLayer.layer_code','AccountPairModel.brm_account_id');
		}
		if ($dlayer_code != '') {
			$conditions['middleLayer.layer_code'] = $dlayer_code;
			$group = array('middleLayer.layer_code','AccountPairModel.brm_account_id');
		}
		if ($budget_layer_code != '') {
			$conditions['BrmBudget.layer_code'] = $budget_layer_code;
			$group = array('bottomLayer.layer_code','AccountPairModel.brm_account_id');
		}
		$conditions['BrmBudget.target_month >='] = $start_month;
		$conditions['BrmBudget.target_month <='] = $end_month;
		$conditions['BrmBudget.flag'] = 1;
		$conditions['BrmBudget.brm_term_id'] = $term_id;
		
		#Set virtual fields for cakephp query
		$this->BrmBudget->virtualFields['budget_total'] = 'SUM(amount)';
		
		#Get result data by list
		$budgets = $this->BrmBudget->find('list', array(
			'fields' => array('BrmAccount.name_jp','BrmBudget.budget_total'),
			'joins' => array(
				array('table' => 'layers',
					  'alias' => 'bottomLayer',
					  'type' => 'LEFT',
					  'conditions' => array(
						'BrmBudget.layer_code = bottomLayer.layer_code AND bottomLayer.flag = 1 AND bottomLayer.to_date >= '.$today_date)
				),
				array('table' => 'layers',
					  'alias' => 'middleLayer',
					  'type' => 'LEFT',
					  'conditions' => array(
						'middleLayer.flag = 1',
						'middleLayer.to_date >= '=> $today_date,
						'middleLayer.type_order' => Setting::LAYER_SETTING['middleLayer'],
					)
				),
				array('table' => 'layers',
					  'alias' => 'topLayer',
					  'type'  => 'LEFT',
					  'conditions' => array(
						'topLayer.flag = 1',
						'topLayer.to_date >= '=> $today_date,
						'topLayer.type_order' => Setting::LAYER_SETTING['topLayer'],
					)
				),
				array(
					'alias' => 'AccountPairModel',
					'table' => 'brm_account_pairs',
					'type' => 'left',
					'conditions' => array(
						0 => 'AccountPairModel.account_code = BrmBudget.account_code',
						1 => 'AccountPairModel.group_code = '.$group_code,
					),
				),
				array(
					'alias' => 'BrmAccount',
					'table' => 'brm_accounts',
					'type' => 'left',
					'conditions' => array(
						0 => 'BrmAccount.id = AccountPairModel.brm_account_id',
						1 => 'BrmAccount.flag = 1',
					),
				)
			),
			'conditions' => $conditions,
			'group' => $group,
			'order' => 'AccountPairModel.brm_account_id ASC',
		));
		
		#Loop through result list and prepare data to return
		foreach ($acc_formulas as $acc_name => $formula) {
			$acc_value = (!empty($budgets[$acc_name])) ? $budgets[$acc_name] : 0;
			$budget_data[$acc_name] += $acc_value;
			$autosum_accs = explode(',', $formula);
			if (!empty($autosum_accs) && !in_array($acc_name, $total_accs)) {
				foreach ($autosum_accs as $each_totalacc) {
					$budget_data[trim($each_totalacc)] += $acc_value;
				}
			}
		}
		return $budget_data;
	}

	public function getLastYearResult($term_id, $s_year, $group_code,$head_dept_code='', $dlayer_code='', $budget_layer_code='')
	{
		$Common         = new CommonController();
		$today_date     = date("Y/m/d");
		$acc_formulas 	= $this->getAccounts($group_code);
		$total_accs 	= $this->getTotalAccounts($group_code);

		$start_month    = $Common->getMonth($s_year, $term_id, 'start');
        $end_month      = $Common->getMonth($s_year, $term_id, 'end');

        $last_year_start = date("Y-m", strtotime($start_month. "last day of - 1 year"));
        $last_year_end   = date("Y-m", strtotime($end_month. "last day of - 1 year"));
        if ($head_dept_code != '') {
			$conditions['topLayer.layer_code'] = $head_dept_code;
			$group = array('topLayer.layer_code','AccountPairModel.brm_account_id');
		}
		if ($dlayer_code != '') {
			$conditions['middleLayer.layer_code'] = $dlayer_code;
			$group = array('middleLayer.layer_code','AccountPairModel.brm_account_id');
		}
		if ($budget_layer_code != '') {
			$conditions['BrmActualResultSummary.layer_code'] = $budget_layer_code;
			$group = array('bottomLayer.layer_code','AccountPairModel.brm_account_id');
		}
		$conditions['BrmActualResultSummary.target_month >='] = $last_year_start;
		$conditions['BrmActualResultSummary.target_month <='] = $last_year_end;
		
        #Set virtual fields for cakephp query
		$this->BrmActualResultSummary->virtualFields['result_total'] = 'SUM(amount)';
		
		#Get result data by list
		$last_results = $this->BrmActualResultSummary->find('list', array(
			'fields' => array('BrmAccount.name_jp','BrmActualResultSummary.result_total'),
			'joins' => array(
				array('table' => 'layers',
					  'alias' => 'bottomLayer',
					  'type'  => 'LEFT',
					  'conditions' => array(
						'BrmActualResultSummary.layer_code = bottomLayer.layer_code AND bottomLayer.flag = 1 AND bottomLayer.to_date >= '.$today_date)
				),
				array('table' => 'layers',
					  'alias' => 'middleLayer',
					  'type'  => 'LEFT',
					  'conditions' => array(
						'middleLayer.flag = 1',
						'middleLayer.to_date >= '=> $today_date,
						'middleLayer.type_order' => Setting::LAYER_SETTING['middleLayer'],
					)
				),
				array('table' => 'layers',
					  'alias' => 'topLayer',
					  'type'  => 'LEFT',
					  'conditions' => array(
						'topLayer.flag = 1',
						'topLayer.to_date >= '=> $today_date,
						'topLayer.type_order' => Setting::LAYER_SETTING['topLayer'],
					)
				),
				array(
					'alias' => 'AccountPairModel',
					'table' => 'brm_account_pairs',
					'type' => 'left',
					'conditions' => array(
						0 => 'AccountPairModel.account_code = BrmActualResultSummary.account_code',
						1 => 'AccountPairModel.group_code = '.$group_code,
					),
				),
				array(
					'alias' => 'BrmAccount',
					'table' => 'brm_accounts',
					'type' => 'left',
					'conditions' => array(
						0 => 'BrmAccount.id = AccountPairModel.brm_account_id',
						1 => 'BrmAccount.flag = 1',
					),
				)
			),
			'conditions' => $conditions,
			'group' => $group,
			'order' => 'BrmActualResultSummary.target_month ASC','BrmActualResultSummary.layer_code ASC','AccountPairModel.brm_account_id ASC',
		));
		#Loop through result list and prepare data to return
		foreach ($acc_formulas as $acc_name => $formula) {
			$acc_value = (!empty($last_results[$acc_name])) ? $last_results[$acc_name] : 0;
			
			$last_result_data[$acc_name] += $acc_value;
			$autosum_accs = explode(',', $formula);
			if (!empty($autosum_accs) && !in_array($acc_name, $total_accs)) {
				foreach ($autosum_accs as $each_totalacc) {
					$last_result_data[trim($each_totalacc)] += $acc_value;
				}
			}
		}

		return $last_result_data;
	}
	/**
	 * getAccounts method
	 *
	 * @author Pan Ei Phyo (20220211)
	 * @return data array
	 */
	// public function getAccounts()
	public function getAccounts($group_code)
	{
		$accounts = $this->BrmAccount->find('list',array(
			'fields' => array('name_jp', 'auto_changed'),
			'conditions' => array(
				'flag' => 1,
				'group_code' => $group_code
			),
		));

		return $accounts;

	}

	/**
	 * getAccounts method
	 *
	 * @author Pan Ei Phyo (20220214)
	 * @return data array
	 */
	public function getTotalAccounts($group_code)
	{
		$accounts = $this->BrmAccount->find('list',array(
			'fields' => array('name_jp'),
			'conditions' => array(
				'flag' => 1,
				'group_code' => $group_code,
				'type <>' => 0
			),
		));

		return $accounts;

	}
	
}
