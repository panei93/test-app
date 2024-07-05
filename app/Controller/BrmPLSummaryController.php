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
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'BrmTermSelection');
App::import('Controller', 'BrmBudgetSummary');

/**
 * PLSummary Controller
 *
 * @author Nu Nu Lwin
 * @date  20200629
 * @package app.Controller
 */
class BrmPLSummaryController extends AppController
{
	public $uses = array('BrmBudget', 'BrmSaccount', 'BrmAccount', 'BrmBudgetPrime', 'BrmExpected', 'Layer', 'BrmLogistic', 'BrmTerm', 'BrmActualResultSummary');
	public $components = array('Session', 'Flash', 'PhpExcel.PhpExcel');
	public $helpers = array('Paginator');

	public function beforeFilter()
	{
		parent::checkUserStatus();
		if ($this->Session->check('HEAD_DEPT_ID')) {
			$hq_id = $this->Session->read('HEAD_DEPT_ID');
		}
		if ($this->Session->check('HEAD_DEPT_CODE')) {
			$hq_code = $this->Session->read('HEAD_DEPT_CODE');
		}
		$Common     = new CommonController();
		$layer_code = $this->Session->read('SESSION_LAYER_CODE');
		$role_id    = $this->Session->read('ADMIN_LEVEL_ID');
		$login_id   = $this->Session->read('LOGIN_ID');
		$pagename   = $this->request->params['controller'];

		$permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
		$this->Session->write('PERMISSIONS', $permissions);
		$layers           = array_keys($permissions['index']['layers']);
		$user_parent_data = array_column($permissions['index']['parent_data'], 'L' . SETTING::LAYER_SETTING['topLayer']);

		if ((!in_array($layer_code, $layers) && $permissions['index']['limit'] > 0) || ($layer_code == "" && $permissions['index']['limit'] > 0)) {
			$errorMsg = parent::getErrorMsg('SE065');
			$this->Flash->set($errorMsg, array("key" => "TermError"));
			$this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
		}

		if ($permissions['index']['limit'] == 1 && !in_array($hq_code, $user_parent_data)) {
			$errorMsg = parent::getErrorMsg('SE016', 'PLサマリー');
			$this->Flash->set($errorMsg, array("key" => "TermError"));
			$this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
		}
	}

	public function index()
	{
		$this->layout  = 'phase_3_menu';
		$Common        = new CommonController;
		$TermSelection = new BrmTermSelectionController;
		$BudgetSummary = new BrmBudgetSummaryController;

		$user_level = $this->Session->read('ADMIN_LEVEL_ID');

		if ($this->Session->check('TERM_NAME')) {
			$budget_term = $this->Session->read('TERM_NAME');
		}
		if ($this->Session->check('TERM_ID')) {
			$term_id = $this->Session->read('TERM_ID');
		}
		if ($this->Session->check('SESSION_LAYER_CODE')) {
			$budget_layer_code = $this->Session->read('SESSION_LAYER_CODE');
		}
		if ($this->Session->check('HEAD_DEPT_ID')) {
			$head_dept_id   = $this->Session->read('HEAD_DEPT_ID');
			$head_name      = $this->Session->read('HEAD_DEPT_NAME');
			$head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
		}
		if ($this->Session->check('LOGIN_ID')) {
			$login_id = $this->Session->read('LOGIN_ID');
		}
		$language   = $this->Session->read('Config.language');
		$layer_type = $this->Session->read('LayerTypeData');
		if (empty($budget_term)) {
			$errorMsg = parent::getErrorMsg('SE093');
			$this->Flash->error($errorMsg);
			$this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
		}
		$permission = $this->Session->read('PERMISSIONS');
		$save_to_tmp = false;

		$today_date = date("Y/m/d");
		$read_limit = $permission['index']['limit'];
		$this->Layer->virtualFields['hlayer_name'] = ($language == 'eng' ? 'name_en' : 'name_jp');
		if ($read_limit == 0) {
			$headpts = $this->Layer->find('list', array(
				'fields'     => array('layer_code', 'hlayer_name'),
				'conditions' => array(
					'Layer.flag' => 1,
					'Layer.to_date >=' => $today_date,
					'Layer.type_order' => Setting::LAYER_SETTING['topLayer']
				),
			));
		} else {
			$user_hq = array_column($permission['index']['parent_data'], 'L' . SETTING::LAYER_SETTING['topLayer']);
			$headpts = $this->Layer->find('list', array(
				'fields'     => array('layer_code', 'hlayer_name'),
				'conditions' => array(
					'Layer.flag'       => 1,
					'Layer.to_date >=' => $today_date,
					'Layer.layer_code' => $user_hq
				),
			));
		}
		$result_arr = array();
		$dept_id = $this->Layer->find('all', array(
			'fields' => 'parent_id',
			'conditions' => array('Layer.flag' => 1, 'Layer.layer_code' => $budget_layer_code)
		));
		$deptId = json_decode($dept_id[0]['Layer']['parent_id'], true);
		if (!empty($headpts) || $save_to_tmp) {
			$budget_year = explode('~', $budget_term);
			$start_year	 = $budget_year[0];
			$years 		 = range($budget_year[0], $budget_year[1]);

			$calculateAmounts = $BudgetSummary->getBudgetSummary($term_id, $head_dept_code, $deptId['L2'], $budget_layer_code, 'PL', $start_year, $budget_year[1]);
			$result_arr = $calculateAmounts;
			$freeze_arr = $this->getFreezeArray($term_id, $start_year);
		}
		$Month_12 = $Common->get12Month($term_id);
		$dlayer_code = "";
		if (!empty($budget_layer_code)) {
			$dlayer_code = $this->Layer->find('all', array(
				'fields' => 'layer_code as dlayer_code',
				'conditions' => array(
					"layer_code IN (select JSON_EXTRACT(parent_id, '$.L" . SETTING::LAYER_SETTING['middleLayer'] . "') from layers where layer_code = " . $budget_layer_code . " AND flag = 1 AND to_date >= " . $today_date . ")",
					'Layer.flag' => 1,
					'to_date >=' => $today_date
				)
			))[0]['Layer']['dlayer_code'];
		}
		#Prepare Session data for excel download
		$head_code = $head_dept_code;

		$cache_name = 'pl_' . $term_id . '_' . $head_code . '_' . $login_id;
		$cache_data = array(
			'result_arr' => $result_arr,
			'freeze_arr' => $freeze_arr,
			'month_12' => $Month_12,
			'head_name' => $head_name,
			'budget_layer_code' => $budget_layer_code,
			'years' => $years,
		);
		Cache::write($cache_name, $cache_data);

		$this->set(compact('budget_term', 'result_arr', 'freeze_arr', 'budget_year', 'Month_12', 'years', 'head_code', 'dlayer_code', 'budget_layer_code', 'headpts', 'layer_type'));
		$this->render('index');
	}

	public function SearchPLSummary()
	{
		$this->layout = 'phase_3_menu';

		$Common = new CommonController;
		$TermSelection = new BrmTermSelectionController;
		$BudgetSummary = new BrmBudgetSummaryController;

		$user_level = $this->Session->read('ADMIN_LEVEL_ID');

		if ($this->Session->check('TERM_NAME')) {
			$budget_term = $this->Session->read('TERM_NAME');
		}
		if ($this->Session->check('TERM_ID')) {
			$term_id = $this->Session->read('TERM_ID');
		}
		if ($this->Session->check('LOGIN_ID')) {
			$login_id = $this->Session->read('LOGIN_ID');
		}

		if ($this->request->is('post')) {
			$head_dept_code = explode(',', $this->request->data('headquarter'))[0];
			$head_code 		= $head_dept_code;
			$this->Session->write('SearchHeadID', $head_code);
			$dlayer_code 	= explode(',', $this->request->data('dept_select'))[0];

			$budget_layer_code = $this->request->data('layer_code');

			$budget_year 	= explode('~', $budget_term);
			$start_year 	= $budget_year[0];
			$years 			= range($budget_year[0], $budget_year[1]);
			$result_arr 	= array();

			$permission = $this->Session->read('PERMISSIONS');
			$today_date = date("Y/m/d");
			$layer_type = $this->Session->read('LayerTypeData');
			$read_limit = $permission['index']['limit'];
			$this->Layer->virtualFields['hlayer_name'] = ($language == 'eng' ? 'name_en' : 'name_jp');
			if ($read_limit == 0) {
				$headpts = $this->Layer->find('list', array(
					'fields'     => array('layer_code', 'hlayer_name'),
					'conditions' => array(
						'Layer.flag' => 1,
						'Layer.to_date >=' => $today_date,
						'Layer.type_order' => Setting::LAYER_SETTING['topLayer']
					),
				));
			} else {
				$user_hq = array_column($permission['index']['parent_data'], 'L' . SETTING::LAYER_SETTING['topLayer']);
				$headpts = $this->Layer->find('list', array(
					'fields'     => array('layer_code', 'hlayer_name'),
					'conditions' => array(
						'Layer.flag'       => 1,
						'Layer.to_date >=' => $today_date,
						'Layer.layer_code' => $user_hq
					),
				));
			}

			if (!empty($headpts)) {
				$head_dept_code = (empty($head_dept_code)) ? ((count($headpts) == 1) ? current($headpts) : '') : $head_dept_code;

				$calculateAmounts = $BudgetSummary->getBudgetSummary($term_id, $head_dept_code, $dlayer_code, $budget_layer_code, 'PL', $start_year, $budget_year[1]);
				$result_arr = $calculateAmounts;
				$freeze_arr = $this->getFreezeArray($term_id, $start_year);
			}
			$Month_12 = $Common->get12Month($term_id);
			$this->set(compact('budget_term', 'result_arr', 'freeze_arr', 'budget_year', 'Month_12', 'years', 'head_code', 'dlayer_code', 'budget_layer_code', 'headpts', 'layer_type'));

			$this->Session->write($SEARCHED_HEAD_ID, explode(',', $this->request->data('headquarter'))[1]);
			$this->Session->write($SEARCHED_DEPT_ID, explode(',', $this->request->data('dept_select'))[1]);
			$this->Session->write($SEARCHED_BA_CODE, $budget_layer_code);

			$this->render('index');
		} else {
			$this->redirect(array(
				'controller' => 'BrmPLSummary',
				'action' => 'index'
			));
		}
	}

	public function getDept()
	{
		#only allow ajax request
		parent::checkAjaxRequest($this);

		$headquarter_val = explode(',', $this->request->data('headquarter_val'));
		$conditions = array();
		$conditions["Layer.flag !="]      = 0;
		$conditions["Layer.type_order"]   = Setting::LAYER_SETTING['middleLayer'];
		if (!empty($headquarter_val)) {$this->log(print_r("aaa",true),LOG_DEBUG);
			$head_dept_code  = $headquarter_val[0];
			array_push($conditions, "Layer.parent_id LIKE CONCAT('%\"L', " . Setting::LAYER_SETTING['topLayer'] . ", '\":\"'," . $head_dept_code . ",'\"%')");
		}
		$data['dept_list'] = $this->Layer->find('all', array(
			'fields'	 => array('layer_code', 'name_jp'),
			'conditions' => $conditions,
			'order' 	 => array('Layer.id ASC'),
			'group'		 => array('layer_code')
		));
		echo json_encode($data);
	}

	/**
	 * get layer list based on headquarter and department value
	 * @author WaiWaiMoe
	 * @date 10/27/2022
	 */
	public function getBACode($hlayer_code = '', $dlayer_code = '', $permission = '', $user_id = '')
	{
		if ($hlayer_code == '' && $dlayer_code == '' && $permission == '' && $user_id == '') {
			#only allow ajax request
			parent::checkAjaxRequest($this);
			$headquarter_val = explode(',', $this->request->data('headquarter_val'));
			$hlayer_code = $headquarter_val[0];

			$dept_val = explode(',', $this->request->data('dept_val'));
			$dlayer_code  = $dept_val[0];
			#get permission from session
			$permission = $this->Session->read('PERMISSIONS');
			#get user id
			$user_id = $this->Session->read('LOGIN_ID');
			$ajax_call = true;
		}
		$today_date   = date("Y/m/d");
		$user_hq_data = null;
		#get read_limit 0 or 1 or 2 or 3 or 4
		$readLimit    = $permission['index']['limit'];
		if ($readLimit != 0) {
			$user_hq_data = array_column($permission['index']['parent_data'], 'L' . SETTING::LAYER_SETTING['topLayer']);
		}
		$data = array();
		if ($readLimit == 0 || ($readLimit == SETTING::LAYER_SETTING['topLayer'] && in_array($hlayer_code, $user_hq_data))) {
			$data['errmsg'] = '';

			$conditions = array();
			$middleLayerJoin = '';
			$conditions["Layer.flag ="]           = 1;
			$conditions["Layer.to_date >="]       = $today_date;
			$conditions["Layer.layer_code"]       = $hlayer_code;
			$conditions["bottomLayer.flag ="]     = 1;
			$conditions["bottomLayer.to_date >="] = $today_date;
			$conditions["bottomLayer.type_order"] = Setting::LAYER_SETTING['bottomLayer'];
			if (!empty($dlayer_code)) {
				array_push($conditions, "bottomLayer.parent_id LIKE CONCAT('%\"L', " . Setting::LAYER_SETTING['topLayer'] . ", '\":\"'," . $hlayer_code . ",'\"%','L'," . Setting::LAYER_SETTING['middleLayer'] . ", '\":\"'," . $dlayer_code . ",'\"%')");
			} else {
				if (!empty($hlayer_code)) {
					array_push($conditions, "bottomLayer.parent_id LIKE CONCAT('%\"L', " . Setting::LAYER_SETTING['topLayer'] . ", '\":\"'," . $hlayer_code . ",'\"%')");
				}
			}
			$data['layer_list'] = $this->Layer->find('all', array(
				'fields'     => array('bottomLayer.layer_code', 'bottomLayer.name_jp', 'bottomLayer.name_en', 'YEAR(bottomLayer.from_date) as from_date'),
				'conditions' => $conditions,
				'joins'      => array(
					array(
						'table' => 'layers',
						'alias' => 'bottomLayer',
					)
				),
				'order' 	 => array('bottomLayer.layer_code ASC')
			));
		} else {
			$data['errmsg'] = 'You dont have permission';
			$data['layer_list'] = '';
		}
		if ($ajax_call == true) {
			echo json_encode($data);
		} else {
			return $data;
		}
	}

	/**
	 * excel download export method
	 *
	 * @author NuNuLwin (20200713)
	 * @throws NotFoundException
	 * @param no param
	 * @return void
	 */
	public function excelDownloadPLSummary()
	{
		$Common = new CommonController;
		$user_level = $this->Session->read('ADMIN_LEVEL_ID');
		if ($this->Session->check('LOGIN_ID')) {
			$login_id = $this->Session->read('LOGIN_ID');
		}
		if ($this->Session->check('TERM_NAME')) {
			$budget_term = $this->Session->read('TERM_NAME');
		}
		if ($this->Session->check('TERM_ID')) {
			$term_id = $this->Session->read('TERM_ID');
		}
		if ($this->Session->check('HEAD_DEPT_ID')) {
			$head_dept_id   = $this->Session->read('HEAD_DEPT_ID');
			$head_name      = $this->Session->read('HEAD_DEPT_NAME');
			$head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
		} else {
			$head_name = '全社';
		}
		// if ($this->Session->check('SearchHeadID')) {
		// 	$head_dept_id = $this->Session->read('SearchHeadID');
		// }
		$PHPExcel = $this->PhpExcel;
		$file_name = 'BrmPLSummary';

		if (empty($budget_term)) {
			$errorMsg = parent::getErrorMsg('SE073');
			$this->Flash->error($errorMsg);
			$this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
		}

		$this->DownloadExcel($term_id, $budget_term, $head_name, $head_dept_code, $file_name, $PHPExcel, $login_id);

		$this->redirect(array('controller' => 'BrmPLSummary', 'action' => 'index'));
	}

	public function DownloadExcel($term_id, $budget_term, $headquarter, $head_dept_code, $file_name, $PHPExcel, $login_id, $save_to_tmp = false)
	{
		$layer_type = $_SESSION['LayerTypeData'];
		#Start Excel Preparation
		$objPHPExcel = $PHPExcel->createWorksheet()->setDefaultFont('ＭＳ Ｐゴシック', 10);
		$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3);
		$objPHPExcel->getActiveSheet()->setShowGridlines(false);
		$objPHPExcel->setActiveSheetIndex(0);
		
		$cell_name = "C1";
		$objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
		
		$objPHPExcel->getActiveSheet()->setTitle('BrmPLSummary');
		
		$sheet = $PHPExcel->getActiveSheet();
		
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
					$border_laft = array(
						'borders' => array(
							'laft' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
								)
								)
							);
							
							$border_bottom_none = array(
								'borders' => array(
				'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				),
				'right' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				),
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				)
			)
		);
		$border_top_none = array(
			'borders' => array(
				'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				),
				'right' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				),
				'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
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
					
			)
		);
		$alignright = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			)
		);
		$aligntop = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
			)
		);
		$negative = array(
			'font'  => array(
				'color' => array('rgb' => 'FF0000')
				)
			);

			$disableColor = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'F9F9F9')
				)
			);
			$sheet->getColumnDimension('A')->setWidth(2);
			$sheet->getColumnDimension('B')->setWidth(15);
			$sheet->getColumnDimension('C')->setWidth(15);
		$sheet->getColumnDimension('D')->setWidth(15);
		$sheet->getColumnDimension('E')->setWidth(15);
		$sheet->getColumnDimension('F')->setWidth(20);
		$sheet->getColumnDimension('G')->setWidth(15);
		
		#End Excel Preparation
		
		$cache_name = 'pl_' . $term_id . '_' . $head_dept_code . '_' . $login_id;
		if (Cache::read($cache_name)) {
			$cache_data = Cache::read($cache_name);

			$result_arr = $cache_data['result_arr'];
			$Month_12 	= $cache_data['month_12'];
			$freeze_arr = $cache_data['freeze_arr'];
			$each_years = $cache_data['years'];

			$head_id = (!$save_to_tmp) ? $this->Session->read('SEARCHED_HEAD_ID') : '';
			$dept_id = (!$save_to_tmp) ? $this->Session->read('SEARCHED_DEPT_ID') : '';
			$ba_code = (!$save_to_tmp) ? $this->Session->read('SEARCHED_BA_CODE') : '';
		} else {
			$this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
		}

		if (!empty($result_arr)) {
			$sheet->setCellValue('C1', __("PLサマリー"));
			$sheet->mergeCells('C1:L1');
			$sheet->getStyle('C1:L1')->applyFromArray($aligncenter);

			$sheet->setCellValue('B2', __("予算期間"));
			$sheet->getStyle('B2')->applyFromArray($alignleft);
			$sheet->getStyle('B2')->applyFromArray($border_dash);

			$sheet->setCellValue('C2', $budget_term);
			$sheet->getStyle('C2')->applyFromArray($alignleft);
			$sheet->getStyle('C2')->applyFromArray($border_dash);

			$sheet->setCellValue('B3', __($layer_type[SETTING::LAYER_SETTING['topLayer']]));
			$sheet->getStyle('B3')->applyFromArray($alignleft);
			$sheet->getStyle('B3')->applyFromArray($border_dash);

			$sheet->setCellValue('C3', $headquarter);
			$sheet->getStyle('C3')->applyFromArray($alignleft);
			$sheet->getStyle('C3')->applyFromArray($border_dash);

			$sheet->setCellValue('B7', __('（単位：千円）'));
			$excel_row = 8;

			$sheet->setCellValue('B' . $excel_row, __("PLサマリー"));
			$sheet->mergeCells('B' . $excel_row . ':B' . ($excel_row + 1));
			$sheet->getStyle('B' . $excel_row . ':B' . ($excel_row + 1))->applyFromArray($aligncenter);
			$sheet->getStyle('B' . $excel_row . ':B' . ($excel_row + 1))->applyFromArray($border_dash);

			$sheet->setCellValue('C' . $excel_row, __("勘定科目"));
			$sheet->mergeCells('C' . $excel_row . ':C' . ($excel_row + 1));
			$sheet->getStyle('C' . $excel_row . ':C' . ($excel_row + 1))->applyFromArray($aligncenter);
			$sheet->getStyle('C' . $excel_row . ':C' . ($excel_row + 1))->applyFromArray($border_dash);

			$sheet->setCellValue('D' . $excel_row, __($layer_type[SETTING::LAYER_SETTING['topLayer']]));
			$sheet->mergeCells('D' . $excel_row . ':D' . ($excel_row + 1));
			$sheet->getStyle('D' . $excel_row . ':D' . ($excel_row + 1))->applyFromArray($aligncenter);
			$sheet->getStyle('D' . $excel_row . ':D' . ($excel_row + 1))->applyFromArray($border_dash);

			$sheet->setCellValue('E' . $excel_row, __($layer_type[SETTING::LAYER_SETTING['middleLayer']]));
			$sheet->mergeCells('E' . $excel_row . ':E' . ($excel_row + 1));
			$sheet->getStyle('E' . $excel_row . ':E' . ($excel_row + 1))->applyFromArray($aligncenter);
			$sheet->getStyle('E' . $excel_row . ':E' . ($excel_row + 1))->applyFromArray($border_dash);

			$sheet->setCellValue('F' . $excel_row, $layer_type[SETTING::LAYER_SETTING['bottomLayer']]);
			$sheet->mergeCells('F' . $excel_row . ':F' . ($excel_row + 1));
			$sheet->getStyle('F' . $excel_row . ':F' . ($excel_row + 1))->applyFromArray($aligncenter);
			$sheet->getStyle('F' . $excel_row . ':F' . ($excel_row + 1))->applyFromArray($border_dash);

			$sheet->setCellValue('G' . $excel_row, __("取引"));
			$sheet->mergeCells('G' . $excel_row . ':G' . ($excel_row + 1));
			$sheet->getStyle('G' . $excel_row . ':G' . ($excel_row + 1))->applyFromArray($aligncenter);
			$sheet->getStyle('G' . $excel_row . ':G' . ($excel_row + 1))->applyFromArray($border_dash);

			$column = 'G';
			$excel_row += 1;
			$columnDimension = [];
			$rowDimension = [];

			for ($j = 0; $j < count($each_years); $j++) {
				$index = 0;
				for ($i = 0; $i < 15; $i++) {
					if ($i == 1) {
						$col = $column;
						$step = 15; // number of columns to step by
						for ($k = 0; $k < $step; $k++) {
							$years_column = $col++;
							$sheet->getColumnDimension($years_column)->setWidth(12);
						}
						$sheet->setCellValue($column . ($excel_row - 1), 'FY' . __($each_years[$j]));
						$sheet->mergeCells($column . ($excel_row - 1) . ':' . ($years_column) . ($excel_row - 1));
						$sheet->getStyle($column . ($excel_row - 1) . ':' . ($years_column) . ($excel_row - 1))->applyFromArray($aligncenter);
						$sheet->getStyle($column . ($excel_row - 1) . ':' . ($years_column) . ($excel_row - 1))->applyFromArray($border_dash);
					}

					$column++;

					if ($i == 6) {
						$sheet->setCellValue($column . $excel_row, __('上期'));
						$columnDimension[$column] = 1;
					} elseif ($i == 13) {
						$sheet->setCellValue($column . $excel_row, __('下期'));
						$columnDimension[$column] = 1;
					} elseif ($i == 14) {
						$sheet->setCellValue($column . $excel_row, __('通期'));
					} else {
						$sheet->setCellValue($column . $excel_row, __($Month_12[$index]));
						$index++;
						$columnDimension[$column] = 2;
					}

					$sheet->mergeCells($column . $excel_row . ':' . $column . $excel_row);
					$sheet->getStyle($column . $excel_row . ':' . $column . $excel_row)->applyFromArray($aligncenter);
					$sheet->getStyle($column . $excel_row . ':' . $column . $excel_row)->applyFromArray($border_dash);
				}
				$sheet->getStyle('B' . ($excel_row - 1) . ':' . $column . ($excel_row - 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
				$sheet->getStyle('B' . $excel_row . ':' . $column . $excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
				$objPHPExcel->getActiveSheet()->getStyle('B:' . $column)->getAlignment()->setWrapText(true);
			}

			foreach ($result_arr as $pl_name => $pl_data) {

				$excel_row += 1;
				$pl_row = $excel_row;
				$sheet->setCellValue('B' . $excel_row, $pl_name);
				$sheet->mergeCells('B' . $excel_row . ':G' . $excel_row);
				$sheet->getStyle('B' . $excel_row . ':G' . $excel_row)->applyFromArray($alignleft);
				$sheet->getStyle('B' . $excel_row . ':G' . $excel_row)->applyFromArray($border_bottom_none);

				$column = 'G';
				for ($i = 0; $i < count($each_years); $i++) {
					foreach ($pl_data['total'] as $year => $total) {

						if ($year == $each_years[$i]) {
							foreach ($total as $key => $each_amt) {
								$each_amt = (!empty($each_amt) || $each_amt != '') ? number_format($each_amt / 1000) : '0';

								$column++;
								$sheet->setCellValue($column . $excel_row, $each_amt);
								//$sheet->mergeCells($column.$excel_row.':'.$column.$excel_row);
								foreach ($freeze_arr as $freeze_year => $freeze_val) {
									if ($year == $freeze_year) {
										foreach ($freeze_val as $free_key => $value) {
											if ($key == $free_key) {
												$sheet->getStyle($column . $excel_row . ':' . $column . $excel_row)->applyFromArray($disableColor);
											}
										}
									}
								}
							}
						}
					}
				}
				foreach ($pl_data['data'] as $account_name => $acc_data) {

					$excel_row += 1;
					$acc_row = $excel_row;
					$sheet->setCellValue('C' . $excel_row, $account_name);
					$sheet->mergeCells('C' . $excel_row . ':G' . $excel_row);
					$sheet->getStyle('C' . $excel_row . ':G' . $excel_row)->applyFromArray($alignleft);
					$sheet->getStyle('C' . $excel_row . ':G' . $excel_row)->applyFromArray($border_bottom_none);

					$column = 'G';

					for ($i = 0; $i < count($each_years); $i++) {
						foreach ($acc_data['sub_total'] as $year => $total) {
							if ($year == $each_years[$i]) {
								foreach ($total as $key => $each_amt) {
									$each_amt = (!empty($each_amt) || $each_amt != '') ? number_format($each_amt / 1000) : '0';

									$column++;
									$sheet->setCellValue($column . $excel_row, $each_amt);

									$rowDimension[$excel_row] = 1;

									foreach ($freeze_arr as $freeze_year => $freeze_val) {
										if ($year == $freeze_year) {
											foreach ($freeze_val as $free_key => $value) {
												if ($key == $free_key) {
													$sheet->getStyle($column . $excel_row . ':' . $column . $excel_row)->applyFromArray($disableColor);
												}
											}
										}
									}
								}
							}
						}
					}

					foreach ($acc_data['sub_data'] as $head => $head_data) {

						$excel_row += 1;
						$head_row = $excel_row;
						$sheet->setCellValue('D' . $excel_row, $head);
						$sheet->mergeCells('D' . $excel_row . ':G' . $excel_row);
						$sheet->getStyle('D' . $excel_row . ':G' . $excel_row)->applyFromArray($alignleft);
						$sheet->getStyle('D' . $excel_row . ':G' . $excel_row)->applyFromArray($border_bottom_none);
						$column = 'G';
						for ($i = 0; $i < count($each_years); $i++) {
							foreach ($head_data['hlayer_total'] as $year => $total) {
								if ($year == $each_years[$i]) {
									foreach ($total as $key => $each_amt) {
										$each_amt = (!empty($each_amt) || $each_amt != '') ? number_format($each_amt / 1000) : '0';

										$column++;
										$sheet->setCellValue($column . $excel_row, $each_amt);

										$rowDimension[$excel_row] = 2;

										foreach ($freeze_arr as $freeze_year => $freeze_val) {
											if ($year == $freeze_year) {
												foreach ($freeze_val as $free_key => $value) {
													if ($key == $free_key) {
														$sheet->getStyle($column . $excel_row . ':' . $column . $excel_row)->applyFromArray($disableColor);
													}
												}
											}
										}
									}
								}
							}
						}

						foreach ($head_data['hlayer_data'] as $dept => $dept_data) {

							$excel_row += 1;
							$dept_row = $excel_row;
							$sheet->setCellValue('E' . $excel_row, $dept);
							$sheet->mergeCells('E' . $excel_row . ':G' . $excel_row);
							$sheet->getStyle('E' . $excel_row . ':G' . $excel_row)->applyFromArray($alignleft);
							$sheet->getStyle('E' . $excel_row . ':G' . $excel_row)->applyFromArray($border_bottom_none);

							$column = 'G';

							for ($i = 0; $i < count($each_years); $i++) {
								foreach ($dept_data['dlayer_total'] as $year => $total) {
									if ($year == $each_years[$i]) {
										foreach ($total as $key => $each_amt) {
											$each_amt = (!empty($each_amt) || $each_amt != '') ? number_format($each_amt / 1000) : '0';

											$column++;
											$sheet->setCellValue($column . $excel_row, $each_amt);

											$rowDimension[$excel_row] = 3;

											foreach ($freeze_arr as $freeze_year => $freeze_val) {
												if ($year == $freeze_year) {
													foreach ($freeze_val as $free_key => $value) {
														if ($key == $free_key) {
															$sheet->getStyle($column . $excel_row . ':' . $column . $excel_row)->applyFromArray($disableColor);
														}
													}
												}
											}
										}
									}
								}
							}

							foreach ($dept_data['dlayer_data'] as $layer => $layer_data) {

								$excel_row += 1;
								$layer_row = $excel_row;
								$sheet->setCellValue('F' . $excel_row, $layer);
								$sheet->mergeCells('F' . $excel_row . ':G' . $excel_row);
								$sheet->getStyle('F' . $excel_row . ':G' . $excel_row)->applyFromArray($alignleft);
								$sheet->getStyle('F' . $excel_row . ':G' . $excel_row)->applyFromArray($border_bottom_none);

								$column = 'G';
								for ($i = 0; $i < count($each_years); $i++) {
									foreach ($layer_data['layer_total'] as $year => $total) {
										if ($year == $each_years[$i]) {
											foreach ($total as $key => $each_amt) {
												$each_amt = (!empty($each_amt) || $each_amt != '') ? number_format($each_amt / 1000) : '0';

												$column++;
												$sheet->setCellValue($column . $excel_row, $each_amt);

												$rowDimension[$excel_row] = 4;

												foreach ($freeze_arr as $freeze_year => $freeze_val) {
													if ($year == $freeze_year) {
														foreach ($freeze_val as $free_key => $value) {
															if ($key == $free_key) {
																$sheet->getStyle($column . $excel_row . ':' . $column . $excel_row)->applyFromArray($disableColor);
															}
														}
													}
												}
											}
										}
									}
								}

								foreach ($layer_data['layer_data'] as $transaction => $transaction_data) {

									if (!empty($transaction)) {
										$excel_row += 1;
										$sheet->setCellValue('G' . $excel_row, $transaction);
										$sheet->mergeCells('G' . $excel_row . ':G' . $excel_row);
										$sheet->getStyle('G' . $excel_row . ':G' . $excel_row)->applyFromArray($alignleft);
										$sheet->getStyle('G' . $excel_row . ':G' . $excel_row)->applyFromArray($border_bottom_none);
										$column = 'G';
										for ($i = 0; $i < count($each_years); $i++) {
											foreach ($transaction_data as $year => $total) {
												if ($year == $each_years[$i]) {
													foreach ($total as $key => $each_amt) {
														$each_amt = (!empty($each_amt) || $each_amt != '') ? number_format($each_amt / 1000) : '0';

														$column++;
														$sheet->setCellValue($column . $excel_row, $each_amt);

														$rowDimension[$excel_row] = 5;

														foreach ($freeze_arr as $freeze_year => $freeze_val) {
															if ($year == $freeze_year) {
																foreach ($freeze_val as $free_key => $value) {
																	if ($key == $free_key) {
																		$sheet->getStyle($column . $excel_row . ':' . $column . $excel_row)->applyFromArray($disableColor);
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
								if (($layer_row + 1) < $excel_row) {
									$sheet->mergeCells('F' . ($layer_row + 1) . ':F' . $excel_row);
									$sheet->getStyle('F' . ($layer_row + 1) . ':F' . $excel_row)->applyFromArray($border_top_none);
								}
							}
							if (($dept_row + 1) < $excel_row) {
								$sheet->mergeCells('E' . ($dept_row + 1) . ':E' . $excel_row);
								$sheet->getStyle('E' . ($dept_row + 1) . ':E' . $excel_row)->applyFromArray($border_top_none);
							}
						}

						if (($head_row + 1) < $excel_row) {
							$sheet->mergeCells('D' . ($head_row + 1) . ':D' . $excel_row);
							$sheet->getStyle('D' . ($head_row + 1) . ':D' . $excel_row)->applyFromArray($border_top_none);
						}
					}

					if (($acc_row + 1) < $excel_row) {
						$sheet->mergeCells('C' . ($acc_row + 1) . ':C' . $excel_row);
						$sheet->getStyle('C' . ($acc_row + 1) . ':C' . $excel_row)->applyFromArray($border_top_none);
					}
				}

				if (($pl_row + 1) < $excel_row) {
					$sheet->mergeCells('B' . ($pl_row + 1) . ':B' . $excel_row);
					$sheet->getStyle('B' . ($pl_row + 1) . ':B' . $excel_row)->applyFromArray($border_top_none);
				}
			}
			$sheet->getStyle('B' . $excel_row . ':G' . $excel_row)->applyFromArray($border_dash);

			$letter = 'G';
			//$column++;
			while ($letter !== $column) {
				$letter++;
				$lastRow = $sheet->getHighestRow();

				for ($row = 10; $row <= $lastRow; $row++) {
					$cell = (string)$sheet->getCell($letter . $row)->getValue();
					$cell = (empty($cell)) ? '0' : $cell;
					$replace_cell = str_replace(",", "", $cell);
					$sheet->setCellValue($letter . $row, $replace_cell);
					$sheet->getStyle($letter . $row)->applyFromArray($alignright);
					$sheet->getStyle($letter . $row)->applyFromArray($border_dash);
					$sheet->getStyle($letter . $row)->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
				}
			}
			foreach ($columnDimension as $key_column => $level) {
				$objPHPExcel->getActiveSheet()
					->getColumnDimension($key_column)
					->setOutlineLevel($level)
					->setVisible(false)
					->setCollapsed(true);
			}
			foreach ($rowDimension as $key_column => $level) {
				$objPHPExcel->getActiveSheet()
					->getRowDimension($key_column)
					->setOutlineLevel($level)
					->setVisible(false)
					->setCollapsed(true);
			}
		}

		$this->autoLayout = false;
		if ($save_to_tmp) {
			$PHPExcel->save($file_name);
		} else {
			$PHPExcel->output($file_name . ".xlsx");
		}
	}

	public function getFreezeArray($term_id, $start_year)
	{

		$Common = new CommonController;

		$forecastPeriod = $this->BrmTerm->getForecastPeriod($term_id);

		$start_month = $Common->getMonth($start_year, $term_id, 'start');

		$intervalEnd = date("Y-m", strtotime($forecastPeriod . "last day of + 1 Month")); #$forecastPeriod+1month

		$interval = DateInterval::createFromDateString('1 months');

		$lockPeriods = new DatePeriod(new DateTime($start_month), $interval, new DateTime($intervalEnd));

		foreach ($lockPeriods as $target_month) {

			$target_month = $target_month->format('Y-m');
			$tg_month = date('n', strtotime($target_month));
			$col = $Common->getMonthColumn($tg_month, $term_id);
			$month_col['month_' . $col . '_amt'] = "freeze";

			$freeze_arr[$start_year] = $month_col;
		}
		return $freeze_arr;
	}
}
