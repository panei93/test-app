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

App::uses('Controller', 'Controller');
App::import('Controller', 'Common');
/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */

class StockSummaryDetailsController extends AppController
{
    public $uses = array('Stock');
    public $components = array('PhpExcel.PhpExcel', 'Session');
    
    
    /**
     * Check Session before render page
     *
     */
    public function beforeFilter()
    {
        parent::checkUserStatus();
        parent::CheckSession();
        parent::checkStockUrlSession();
        
        $role_id    = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id   = $this->Session->read('LOGIN_ID');
        $pagename   = $this->request->params['controller'];
        $session_layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $Common     = new CommonController();
        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        $layers      = array_keys($permissions['index']['layers']);
        
        if((!in_array($session_layer_code, $layers)) || ($session_layer_code=="" && $permissions['index']['limit']>0)) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"Error"));
            $this->redirect(array('controller'=>'SapSelections', 'action'=>'index'));
        }
    }
    
    public function index()
    {
        $this->layout = 'stocks';
        $errorMsg = "";
        $successMsg = "";
        
        if ($this->Session->check('successMsg')) {
            $successMsg = $this->Session->read('successMsg');
            $this->Session->delete('successMsg');
        }
        if ($this->Session->check('EXCEL_ERR_MSG')) {
            $errorMsg = $this->Session->read('EXCEL_ERR_MSG');
            $this->Session->delete('EXCEL_ERR_MSG');
        }
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $period = $this->Session->read('StockSelections_PERIOD_DATE');

        if ($this->Session->check('SESSION_LAYER_CODE')) {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        } else {
            $layer_code = '';
        }
        $Common = New CommonController();

        $flag_list  = Setting::ADDCMT_FLAG;
        
        /***
        @ Edit Base Date by Nu Nu Lwin
        @ 10-Jun-19 9:18 AM
        ***/
        $condi = array(); //get base_date and deadLine_date with condition
        if ($layer_code != null || $layer_code != '') {
            // Nu Nu Lwin add new $condi
            $condi["Stock.layer_code"] = $layer_code;
        }
        //get base_date, deadLine_date accourding to layer_code, period and max id
        $condi["date_format(Stock.period,'%Y-%m')"] = $period;
        $submission_deadline = ""; // base_date
        $deadLine_date = "";
        
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
                    $submission_deadline = $date[0]['base_date'];
                }
                if (isset($date[0]['deadline_date']) && $date[0]['deadline_date'] != 0) {
                    $deadLine_date = $date[0]['deadline_date'];
                }
            }
        }
        
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
    
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');

        $choose_year = Date("Y", strtotime($period));
        $choose_month = Date("m", strtotime($period));

        $temp= Date("Y-m", strtotime($period. "+ 1 Month"));
        $temp= Date("Y-m-d", strtotime($temp. "- 1 Day"));
        $prev_2_month = Date("Y-m-d", strtotime($temp. "last day of -2 Month"));//previous 2 month from selected date
        
        $start_year = Date("Y", strtotime($prev_2_month));
        //added by nunu accourding to customer feedback "2.1.2020"
        $prev_2_monthNew = Date("m", strtotime($prev_2_month));
        $start_year = Date("Y", strtotime($prev_2_month));
        //end//
        $prev_1_month = Date("Y-m-d", strtotime($temp. "last day of -1 Month"));

        
        $reference_date = date('Y-m-01', strtotime($period));
        
        $previous_month_30_result = $this->Stock->Search_prev2Month_30_60days($layer_code, $prev_2_month, $period);
        $previous_month_60_result = $this->Stock->Search_prev2Month_60days($layer_code, $prev_2_month, $period);
        
        
        if (!empty($previous_month_30_result)) {
            $this->set('previous_month_30_result', $previous_month_30_result);
        }
        if (!empty($previous_month_60_result)) {
            $this->set('previous_month_60_result', $previous_month_60_result);
        }
        
        $param = array();
        $param['choose_year']  = $start_year;
        $param['prev_month_2'] = $prev_2_monthNew;
        $param['choose_year_1']  = $choose_year;
        $param['choose_month'] = $choose_month;
        
        $sub_title = parent::getSuccessMsg('SS011', $param);
        
        $param_1 = array();
        $param_1['choose_year']  = $start_year;
        $param_1['prev_month_2'] = $prev_2_month;
        
        $div_title = parent::getSuccessMsg('SS012', $param_1);
        $div_title1 = parent::getSuccessMsg('SS013', $param_1);
        
        $this->set('prev_month_1', $prev_1_month);
        $this->set('prev_month_2', $prev_2_month);
        $this->set('choose_month', $choose_month);
        $this->set('choose_year', $choose_year);
        $this->set('period', $period);
        $this->set('layer_code', $layer_code);
        $this->set('layer_name', $layer_name);
        $this->set('submission_deadline', $submission_deadline);
        $this->set('deadLine_date', $deadLine_date);
        $this->set('sub_title', $sub_title);
        $this->set('div_title', $div_title);
        $this->set('div_title1', $div_title1);
        $this->set('errorMsg', $errorMsg);
        $this->set('noDataMsg', parent::getErrorMsg('SE001'));
        $this->render('index');
    }
    
    /**
     * Detail Report Download
     * @author - Aye Thandar Lwin
     */
    public function Download_Summary_Rpt()
    {
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $choose_year = Date("Y", strtotime($period));
        $choose_month = Date("m", strtotime($period));
        $temp= Date("Y-m", strtotime($period. "+ 1 Month"));
        $temp= Date("Y-m-d", strtotime($temp. "- 1 Day"));
        $prev_2_month = Date("Y-m-d", strtotime($temp. "last day of -2 Month"));//previous 2 month from selected date
        $start_year = Date("Y", strtotime($prev_2_month));
        //added by nunu accourding to customer feedback "2.1.2020"
        $prev_2_monthNew = Date("m", strtotime($prev_2_month));
        //end//
        $prev_1_month = Date("Y-m-d", strtotime($temp. "last day of -1 Month"));
        
        $reference_date = date('Y-m-01', strtotime($period));
        
        $objPHPExcel = $this->PhpExcel->createWorksheet()->setDefaultFont('Cambria', 12);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel ->getActiveSheet()->setShowGridlines(true);
        $objPHPExcel ->setActiveSheetIndex(0);
        
        $objPHPExcel->getActiveSheet()->setTitle('Summary');
        
        $sheet = $this->PhpExcel->getActiveSheet();
        
        $border_dash = array(
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                ));
        $border_none = array(
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_NONE)
                ));
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
        
                ),
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                )
        );
        $alignright = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        
                ),
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A:I')->getAlignment()->setWrapText(true);
        
        /***
        @ Edit Base Date by Nu Nu Lwin
        @ 10-Jun-19 9:53 AM
        ***/
        $condi = array(); //get base_date and deadLine_date with condition
        $submission_deadline = ""; // base_date
        $deadLine_date = "";

        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        if ($this->Session->check('SESSION_LAYER_CODE')) {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        } else {
            $layer_code = '';
        }
        if ($layer_code != null || $layer_code != '') {
            // Nu Nu Lwin add new $condi
            $condi["Stock.layer_code"] = $layer_code;
        }

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
                    $submission_deadline = $date[0]['base_date'];
                }
                if (isset($date[0]['deadline_date']) && $date[0]['deadline_date'] != 0) {
                    $deadLine_date = $date[0]['deadline_date'];
                }
            }
        }
        

        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        
        $start_row = 3;
        $end_row = $start_row + 4;
        
        // second sheet creation start
        
        $sheet->setTitle("Detail");
            
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension("D")->setWidth(20);
        $sheet->getColumnDimension("E")->setWidth(20);
        $sheet->getColumnDimension("F")->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        // $sheet->getColumnDimension('H')->setWidth(20);
        // $sheet->getColumnDimension('I')->setWidth(20);

        //add title by Thura Moe
        $sheet->setCellValue('A1', __("長期滞留債権報告書"));
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1:G1')->applyFromArray($aligncenter);
        //end title
        
        $sheet->getStyle('A17:G17')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        
        $sheet->mergeCells('A'.$start_row .':'.'A'.$end_row);
        $sheet->getStyle('A'.$start_row .':'.'A'.$end_row)->applyFromArray($border_dash);
        $sheet->mergeCells('B'.$start_row .':'.'B'.$end_row);
        $sheet->getStyle('B'.$start_row .':'.'B'.$end_row)->applyFromArray($border_dash);
        $sheet->mergeCells('C'.$start_row .':'.'C'.$end_row);
        $sheet->getStyle('C'.$start_row .':'.'C'.$end_row)->applyFromArray($border_dash);
        
        $sheet->setCellValue('A2', __("社長"));
        $sheet->getStyle('A2')->applyFromArray($aligncenter);
        $sheet->getStyle('A2')->applyFromArray($border_dash);
        $sheet->getStyle('A2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->setCellValue('A3', "（　   　      /　            ）");
        $sheet->setCellValue('A3', "（　   　      /　            ）");
        
        $sheet->setCellValue('B2', __("管理本部長"));//TL
        $sheet->getStyle('B2')->applyFromArray($aligncenter);
        $sheet->getStyle('B2')->applyFromArray($border_dash);
        $sheet->getStyle('B2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->setCellValue('B3', "（　   　      /　            ）");
        $sheet->setCellValue('B3', "（　   　      /　            ）");
        
        $sheet->setCellValue('C2', __("業務管理部長"));//担当者
        $sheet->getStyle('C2')->applyFromArray($aligncenter);
        $sheet->getStyle('C2')->applyFromArray($border_dash);
        $sheet->getStyle('C2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->setCellValue('C3', "（　   　      /　            ）");
        $sheet->setCellValue('C3', "（　   　      /　            ）");
        
        $sheet->setCellValue('A9', __("部署"));
        $sheet->getStyle('A9')->applyFromArray($alignleft);
        $sheet->getStyle('A9')->applyFromArray($border_dash);
        $sheet->setCellValue('B9', $layer_code);
        $sheet->getStyle('B9')->applyFromArray($alignleft);
        $sheet->getStyle('B9')->applyFromArray($border_dash);
        
        $sheet->setCellValue('A10', __("部署名"));
        $sheet->getStyle('A10')->applyFromArray($alignleft);
        $sheet->getStyle('A10')->applyFromArray($border_dash);
        $sheet->setCellValue('B10', $layer_name);
        $sheet->getStyle('B10')->applyFromArray($alignleft);
        $sheet->getStyle('B10')->applyFromArray($border_dash);
        
        $sheet->setCellValue('A11', __("対象月"));
        $sheet->getStyle('A11')->applyFromArray($alignleft);
        $sheet->getStyle('A11')->applyFromArray($border_dash);
        $sheet->setCellValue('B11', $period);
        $sheet->getStyle('B11')->applyFromArray($alignleft);
        $sheet->getStyle('B11')->applyFromArray($border_dash);

        //add new column
        $sheet->setCellValue('G11', __("財務経理部長"));//TL
        $sheet->getStyle('G11')->applyFromArray($alignright);
        $sheet->getStyle('G11')->applyFromArray($border_none);

        $sheet->setCellValue('G13', __("担当："));//TL
        $sheet->getStyle('G13')->applyFromArray($alignright);
        $sheet->getStyle('G13')->applyFromArray($border_none);
        //add new column end
        
        $sheet->setCellValue('A12', __("基準年月日"));
        $sheet->getStyle('A12')->applyFromArray($alignleft);
        $sheet->getStyle('A12')->applyFromArray($border_dash);
        $sheet->setCellValue('B12', $submission_deadline);
        $sheet->getStyle('B12')->applyFromArray($alignleft);
        $sheet->getStyle('B12')->applyFromArray($border_dash);
        

        $sheet->setCellValue('A13', __("提出期日"));
        $sheet->getStyle('A13')->applyFromArray($alignleft);
        $sheet->getStyle('A13')->applyFromArray($border_dash);
        $sheet->setCellValue('B13', $deadLine_date);
        $sheet->getStyle('B13')->applyFromArray($alignleft);
        $sheet->getStyle('B13')->applyFromArray($border_dash);
        
        $sheet->setCellValue('A14', __("【回収遅延・長期滞留債権　報告書】"));
        $sheet->mergeCells('A14:G14');
        $sheet->getStyle('A14:G14')->applyFromArray($aligncenter);
       
        if($this->Session->read('Config.language') == 'eng'){
            $sheet->setCellValue('A15', __($start_year." Year ".$prev_2_monthNew." Standard Last Day,Claims Remaining of ".$choose_year." Year ".$choose_month));
        }else{
            $sheet->setCellValue('A15', __($start_year."年".$prev_2_monthNew."月末日基準,".$choose_year."年".$choose_month."月末日時点債権残"));    
        }
        $sheet->mergeCells('A15:G15');
        $sheet->getStyle('A15:G15')->applyFromArray($aligncenter);
        
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        
        $sheet->setCellValue('A16', __('30日超60日以内')); //change by nunu
        $sheet->mergeCells('A16:C16');
        $sheet->setCellValue('A17', __("部署名"));
        $sheet->getStyle('A17')->applyFromArray($border_dash);
        $sheet->getStyle('A17')->applyFromArray($aligncenter);
        $sheet->setCellValue('B17', __("取引先名"));
        $sheet->getStyle('B17')->applyFromArray($border_dash);
        $sheet->getStyle('B17')->applyFromArray($aligncenter);
        $sheet->setCellValue('C17', __("品目コード"));
        $sheet->getStyle('C17')->applyFromArray($border_dash);
        $sheet->getStyle('C17')->applyFromArray($aligncenter);
        $sheet->setCellValue('D17', __("入庫Index No."));
        $sheet->getStyle('D17')->applyFromArray($border_dash);
        $sheet->getStyle('D17')->applyFromArray($aligncenter);
        $sheet->setCellValue('E17', __("金額"));
        $sheet->getStyle('E17')->applyFromArray($border_dash);
        $sheet->getStyle('E17')->applyFromArray($aligncenter);
        $sheet->setCellValue('F17', __("状況/滞留理由"));
        $sheet->getStyle('F17')->applyFromArray($border_dash);
        $sheet->getStyle('F17')->applyFromArray($aligncenter);
        $sheet->setCellValue('G17', __("入金日/入金予定日等"));
        $sheet->getStyle('G17')->applyFromArray($border_dash);
        $sheet->getStyle('G17')->applyFromArray($aligncenter);
        
        //start write data
        
        $count = 0;
        $index_count = 0;
        $prev_dest_code = '';
        $prev_dest_code_2 = '';
        $prev_merge_cell = '';
        $prev_logistic_index = '';
        
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        
        $choose_year = Date("Y", strtotime($period));
        $choose_month = Date("m", strtotime($period));
        $temp= Date("Y-m", strtotime($period. "+ 1 Month"));
        $temp= Date("Y-m-d", strtotime($temp. "- 1 Day"));
        $prev_2_month = Date("Y-m-d", strtotime($temp. "last day of -2 Month"));//previous 2 month from selected date
        $prev_1_month = Date("Y-m-d", strtotime($temp. "last day of -1 Month"));
        
        // $prev_month_2 = Date("m", strtotime($prev_2_month));
        // $prev_month_1 = Date("m", strtotime($prev_1_month));
        
        $a = 18;
        $previous_month_30_result = $this->Stock->Search_prev2Month_30_60days($layer_code, $prev_2_month, $period);
        $previous_month_60_result = $this->Stock->Search_prev2Month_60days($layer_code, $prev_2_month, $period);
        
        
        if (!empty($previous_month_30_result)) {
            foreach ($previous_month_30_result as $result4) {

                $layer_name = $result4['tbl_2']['name_jp'];
                $destination_name = $result4['tbl_2']['destination_name'];
                $item_code = $result4['tbl_2']['item_code'];
                $receipt_index_no = $result4['tbl_2']['receipt_index_no'];
                $money_amount = $result4['tmp']['amount'];
                $reason = $result4['tbl_2']['reason'];
                $settlement_date = $result4['tbl_2']['settlement_date'];		

                if ($settlement_date == '0000-00-00') {
                    $settlement_date = '';
                } else {
                    $settlement_date = $result4['tbl_2']['settlement_date'];
                }
               
                
                //MERGE LOGISTIC INDEX NO
                if ($prev_logistic_index != $receipt_index_no) {
                    if ($prev_logistic_index != '') { // FIRST row merge
                        $start_index = $a - $index_count;
                        $end_index = $a - 1;
                        $objPHPExcel->getActiveSheet()->mergeCells('D'.$start_index .':'.'D'.$end_index);
                        $objPHPExcel->getActiveSheet()->mergeCells('E'.$start_index .':'.'E'.$end_index);
                        $objPHPExcel->getActiveSheet()->getStyle('D'.$start_index .':'.'D'.$end_index)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('D'.$start_index .':'.'D'.$end_index)->applyFromArray($alignleft);
                        $objPHPExcel->getActiveSheet()->getStyle('E'.$start_index .':'.'E'.$end_index)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('E'.$start_index .':'.'E'.$end_index)->applyFromArray($alignright);
                    }
                    $index_count = 0; //merge row count
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$a, $receipt_index_no);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$a, $money_amount);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$a)->getNumberFormat()->setFormatCode('#,##0');
                    $prev_logistic_index = $receipt_index_no;
                }
                $index_count++;
                //MERGE DESTINATION CODE
                // if ($prev_dest_code != $item_code) {
                    // if ($prev_dest_code != '') { // FIRST row merge
                    //     $start = $a - $count;
                    //     $end = $a - 1;
                    //     $objPHPExcel->getActiveSheet()->mergeCells('B'.$start .':'.'B'.$end);
                    //     $objPHPExcel->getActiveSheet()->mergeCells('C'.$start .':'.'C'.$end);
                    //     $objPHPExcel->getActiveSheet()->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($border_dash);
                    //     $objPHPExcel->getActiveSheet()->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($alignleft);
                    //     $objPHPExcel->getActiveSheet()->getStyle('C'.$start .':'.'C'.$end)->applyFromArray($border_dash);
                    //     $objPHPExcel->getActiveSheet()->getStyle('C'.$start .':'.'C'.$end)->applyFromArray($alignleft);
                        
                    // }
                    // $count = 0; //merge row count
                    // $objPHPExcel->getActiveSheet()->setCellValue('B'.$a, $destination_name);
                    // $objPHPExcel->getActiveSheet()->setCellValue('C'.$a, $item_code);
                    // $objPHPExcel->getActiveSheet()->getStyle('B'.$a)->applyFromArray($border_dash);
                    // $objPHPExcel->getActiveSheet()->getStyle('C'.$a)->applyFromArray($border_dash);
        
                    // $prev_dest_code = $item_code;
                // }
                    
                // $count ++;//increase merge row count
                       
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$a, $layer_name);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$a)->applyFromArray($alignleft);
                
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$a, $destination_name);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$a)->applyFromArray($alignleft);

                $objPHPExcel->getActiveSheet()->setCellValue('C'.$a, $item_code);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$a)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$a)->applyFromArray($alignleft);
                
                    
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$a, $reason);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$a)->applyFromArray($border_dash);
                    
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$a, $settlement_date);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$a)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$a)->applyFromArray($border_dash);
                $a++;
            }
            
            //END ROW MERGE CELL for first table
            //merge for logistic_index_no
            $start_index = $a - $index_count;
            $end_index = $a - 1;
            $objPHPExcel->getActiveSheet()->mergeCells('D'.$start_index .':'.'D'.$end_index);
            $sheet->getStyle('D'.$start_index .':'.'D'.$end_index)->applyFromArray($border_dash);
            $sheet->getStyle('D'.$start_index .':'.'D'.$end_index)->applyFromArray($alignleft);
            
            $objPHPExcel->getActiveSheet()->mergeCells('E'.$start_index .':'.'E'.$end_index);
            $sheet->getStyle('E'.$start_index .':'.'E'.$end_index)->applyFromArray($alignright);
            $sheet->getStyle('E'.$start_index .':'.'E'.$end_index)->applyFromArray($border_dash);
            
            //merge for destination code
            // $start = $a - $count;
            // $end = $a - 1;
            // $objPHPExcel->getActiveSheet()->mergeCells('C'.$start .':'.'C'.$end);
            // $sheet->getStyle('C'.$start .':'.'C'.$end)->applyFromArray($border_dash);
            // $sheet->getStyle('C'.$start .':'.'C'.$end)->applyFromArray($alignleft);
            // $objPHPExcel->getActiveSheet()->mergeCells('B'.$start .':'.'B'.$end);
            // $sheet->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($alignleft);
            // $sheet->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($border_dash);
        }
                
                
        //SECOND TABLE DATA START
    
        $b = $a + 3; // start row = 21
        $sheet->setCellValue('A'.$b, __('60日超')); //changed by nunu
        $sheet->mergeCells('A'.$b.':'.'C'.$b);
        $b++;
        $objPHPExcel->getActiveSheet()->getStyle('A'.$b.':'.'G'.$b)->applyFromArray(
            array(
                        'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'daeef3')
                        )
                )
        );
        $sheet->setCellValue('A'.$b, __("部署名"));
        $sheet->getStyle('A'.$b)->applyFromArray($border_dash);
        $sheet->getStyle('A'.$b)->applyFromArray($aligncenter);
        $sheet->setCellValue('B'.$b, __("取引先名"));
        $sheet->getStyle('B'.$b)->applyFromArray($border_dash);
        $sheet->getStyle('B'.$b)->applyFromArray($aligncenter);
        $sheet->setCellValue('C'.$b, __("品目コード"));
        $sheet->getStyle('C'.$b)->applyFromArray($border_dash);
        $sheet->getStyle('C'.$b)->applyFromArray($aligncenter);
        $sheet->setCellValue('D'.$b, __("入庫Index No."));
        $sheet->getStyle('D'.$b)->applyFromArray($border_dash);
        $sheet->getStyle('D'.$b)->applyFromArray($aligncenter);
        $sheet->setCellValue('E'.$b, __("金額"));
        $sheet->getStyle('E'.$b)->applyFromArray($border_dash);
        $sheet->getStyle('E'.$b)->applyFromArray($aligncenter);
        $sheet->setCellValue('F'.$b, __("状況/滞留理由"));
        $sheet->getStyle('F'.$b)->applyFromArray($border_dash);
        $sheet->getStyle('F'.$b)->applyFromArray($aligncenter);
        $sheet->setCellValue('G'.$b, __("入金日/入金予定日等"));
        $sheet->getStyle('G'.$b)->applyFromArray($border_dash);
        $sheet->getStyle('G'.$b)->applyFromArray($aligncenter);
        $b++;
            
        $count = 0;
        $prev_dest_code_2 = '';
        $prev_logistic_index_2 = '';
        
        if (!empty($previous_month_60_result)) {
            foreach ($previous_month_60_result as $result5) {
                $layer_name = $result5['tbl_2']['name_jp'];
                $destination_name = $result5['tbl_2']['destination_name'];
                $item_code_2 = $result5['tbl_2']['item_code'];
                $receipt_index_no_2 = $result5['tbl_2']['receipt_index_no'];
                $money_amount_2 = $result5['tmp']['amount'];
                $reason = $result5['tbl_2']['reason'];
                $settlement_date = $result5['tbl_2']['settlement_date'];

                
                if ($settlement_date == '0000-00-00') {
                    $settlement_date = '';
                } else {
                    $settlement_date = $result5['tbl_2']['settlement_date'];
                }
                
                
                //MERGE LOGISTIC INDEX NO
                if ($prev_logistic_index_2 != $receipt_index_no_2) {
                    if ($prev_logistic_index_2 != '') { // FIRST row merge
                        $start_index = $b - $index_count;
                        $end_index = $b - 1;
                        $objPHPExcel->getActiveSheet()->mergeCells('D'.$start_index .':'.'D'.$end_index);
                        $objPHPExcel->getActiveSheet()->mergeCells('E'.$start_index .':'.'E'.$end_index);
                        $objPHPExcel->getActiveSheet()->getStyle('D'.$start_index .':'.'D'.$end_index)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('D'.$start_index .':'.'D'.$end_index)->applyFromArray($alignleft);
                        $objPHPExcel->getActiveSheet()->getStyle('E'.$start_index .':'.'E'.$end_index)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('E'.$start_index .':'.'E'.$end_index)->applyFromArray($alignright);
                    }
                    $index_count = 0; //merge row count
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$b, $receipt_index_no_2);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$b, $money_amount_2);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$b)->getNumberFormat()->setFormatCode('#,##0');
                        
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$b)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$b)->applyFromArray($border_dash);
                
                    $prev_logistic_index_2 = $receipt_index_no_2;
                }
                $index_count++;
                
                // MERGE DESTINATION CODE
                // if ($prev_dest_code_2 != $item_code_2) {
                    // if ($prev_dest_code_2 != '') { // FIRST row merge
                        
                    //     $start = $b - $count;
                    //     $end = $b - 1;
                    //     $objPHPExcel->getActiveSheet()->mergeCells('B'.$start .':'.'B'.$end);
                    //     $objPHPExcel->getActiveSheet()->mergeCells('C'.$start .':'.'C'.$end);
                    //     $objPHPExcel->getActiveSheet()->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($border_dash);
                    //     $objPHPExcel->getActiveSheet()->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($alignleft);
                    //     $objPHPExcel->getActiveSheet()->getStyle('C'.$start .':'.'C'.$end)->applyFromArray($border_dash);
                    //     $objPHPExcel->getActiveSheet()->getStyle('C'.$start .':'.'C'.$end)->applyFromArray($alignleft);
                        
                    // }
                //     $count = 0; //merge row count
                //     $objPHPExcel->getActiveSheet()->setCellValue('B'.$b, $destination_name);
                //     $objPHPExcel->getActiveSheet()->setCellValue('C'.$b, $item_code_2);
                //     $objPHPExcel->getActiveSheet()->getStyle('B'.$b)->applyFromArray($border_dash);
                //     $objPHPExcel->getActiveSheet()->getStyle('C'.$b)->applyFromArray($border_dash);
                        
                //     $prev_dest_code_2 = $item_code_2;
                // }
        
                // $count ++;//increase merge row count
        
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$b, $layer_name);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$b)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$b)->applyFromArray($alignleft);   
                
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$b, $destination_name);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$b)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$b)->applyFromArray($alignleft);

                $objPHPExcel->getActiveSheet()->setCellValue('C'.$b, $item_code_2);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$b)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$b)->applyFromArray($alignleft);
                    
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$b, $reason);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$b)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$b)->applyFromArray($border_dash);
                    
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$b, $settlement_date);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$b)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$b)->applyFromArray($border_dash);
                $b++;
            }

            /* add four box */
            $start_line = $b+2;
            $end_line = $b+6;
            $sheet->mergeCells('C'.$start_line .':'.'C'.$end_line);
            $sheet->mergeCells('D'.$start_line .':'.'D'.$end_line);
            $sheet->mergeCells('E'.$start_line .':'.'E'.$end_line);
            $sheet->mergeCells('F'.$start_line .':'.'F'.$end_line);
            $sheet->mergeCells('G'.$start_line .':'.'G'.$end_line);

            for ($i=$start_line; $i <=$end_line ; $i++) {
                $objPHPExcel->getActiveSheet()->getStyle('C'.$i)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('D'.$i)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$i)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$i)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$i)->applyFromArray($border_dash);
            }
            /* add four  box end */

            /* For excel print  setting start*/
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
            /*  For excel print setting end --- */

            //END ROW MERGE CELL for first table
            $start_index = $b - $index_count;
            $end_index = $b - 1;
            $objPHPExcel->getActiveSheet()->mergeCells('D'.$start_index .':'.'D'.$end_index);
            $sheet->getStyle('D'.$start_index .':'.'D'.$end_index)->applyFromArray($border_dash);
            $sheet->getStyle('D'.$start_index .':'.'D'.$end_index)->applyFromArray($alignleft);
            $objPHPExcel->getActiveSheet()->mergeCells('E'.$start_index .':'.'E'.$end_index);
            $sheet->getStyle('E'.$start_index .':'.'E'.$end_index)->applyFromArray($alignright);
            $sheet->getStyle('E'.$start_index .':'.'E'.$end_index)->applyFromArray($border_dash);
            
            // $start = $b - $count;
            // $end = $b - 1;
            // $objPHPExcel->getActiveSheet()->mergeCells('B'.$start .':'.'B'.$end);
            // $sheet->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($alignleft);
            // $sheet->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($border_dash);
            // $objPHPExcel->getActiveSheet()->mergeCells('C'.$start .':'.'C'.$end);
            // $sheet->getStyle('C'.$start .':'.'C'.$end)->applyFromArray($border_dash);
            // $sheet->getStyle('C'.$start .':'.'C'.$end)->applyFromArray($alignleft);
           
        }
        if (!empty($previous_month_30_result) || !empty($previous_month_60_result)) {
            $this->PhpExcel->output("StockSummaryDetails".".xlsx");
            $this->autoLayout = false;
            $this->render('index');
        } else {
            $param = array( __("export"));
            $msg = parent::getErrorMsg("SE017", $param);
            $this->Session->write('EXCEL_ERR_MSG', $msg);
            $this->redirect(array('controller'=>'StockSummaryDetails', 'action'=>'index'));
        }
    }
}
