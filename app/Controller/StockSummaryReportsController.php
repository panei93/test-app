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

class StockSummaryReportsController extends AppController
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
            $this->redirect(array('controller'=>'StockSelections', 'action'=>'index'));
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
        
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        
        if ($this->Session->check('SESSION_LAYER_CODE')) {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        } else {
            $layer_code = '';
        }
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        
        $Common = New CommonController();

        $flag_list  = Setting::ADDCMT_FLAG;

        $choose_year = Date("Y", strtotime($period));
        $choose_month = Date("m", strtotime($period));
        $prev_2_month = Date("Y-m-d", strtotime($period. "- 2 Month"));//previous 2 month from selected date
        $prev_1_month = Date("Y-m-d", strtotime($period. "- 1 Month"));
        
        $prev_month_2 = Date("m", strtotime($prev_2_month));
        $prev_month_1 = Date("m", strtotime($prev_1_month));
        
        $reference_date = date('Y-m-01', strtotime($period));

        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $condi["date_format(Stock.period,'%Y-%m')"] = $period;
        if (!empty($layer_code)) {
            $condi["Stock.layer_code"] = $layer_code;
        }
        $basedate = "";
        $deadline_date="";
        $submission_deadline ="";
        
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
                    $deadline_date = $date[0]['deadline_date'];
                }
            }
        }
        //Number ofa day calculate
        
        // creation of First Table Design and Data
        $result_one = $this->Stock->Search_equalOver_1Million($layer_code, $period);

        
        if (!empty($result_one)) {
            foreach ($result_one as $row) {
                $destination_code[] = $row['tmp']['destination_name'];
                $dest_one = '"'.implode('","', $destination_code).'"';
            }
            
            $search_result_one = $this->Stock->result_one_data($dest_one, $layer_code, $period);

            $total_amt_result = $this->Stock->sum_result_one($dest_one, $layer_code, $period);
            $count_one = count($total_amt_result);
            foreach ($search_result_one as $key => $val) {
                $desti_code = $val['stocks']['destination_name'];
                    
                for ($j=0;$j<$count_one;$j++) {
                    $destination_code = $total_amt_result[$j]['stocks']['destination_name'];
                    $total_amount = $total_amt_result[$j][0]['total_amt'];
                    
                    if ($desti_code == $destination_code) {
                        $search_result_one[$key]["amountofMoney"] = $total_amount;
                    }
                }
            }
            
            
            $this->set('search_result_one', $search_result_one);
        }
        
        //Creation of Second Table Design with Data
        
        $result_two =$this->Stock->Search_under_1Million_morethan30days($layer_code, $period);

        if (!empty($result_two)) {
            foreach ($result_two as $row) {
                $destination_code_two[] = $row['tmp']['destination_name'];
                $dest_one = '"'.implode('","', $destination_code_two).'"';
            }
            $search_result_two = $this->Stock->result_two_data($dest_one, $layer_code, $period);
            $total_amt_result_two = $this->Stock->sum_result_two($dest_one, $layer_code, $period);
            $count_two = count($total_amt_result_two);
            
            foreach ($search_result_two as $key => $val) {
                $desti_code = $val['stocks']['destination_name'];
            
                for ($j=0;$j<$count_two;$j++) {
                    $destination_code = $total_amt_result_two[$j]['stocks']['destination_name'];
                    $total_amount = $total_amt_result_two[$j][0]['total_amt'];
            
                    if ($desti_code == $destination_code) {
                        $search_result_two[$key]["amountofMoney"] = $total_amount;
                    }
                }
            }
            $this->set('search_result_two', $search_result_two);
        }
        // creation of Third Table Design with Data
        $result_three =$this->Stock->Search_under_1Million_lessthan30days($layer_code, $period);
       
        if (!empty($result_three)) {
            foreach ($result_three as $row) {
                $destination_code_three[] = $row['tmp']['destination_name'];
                $dest_one = '"'.implode('","', $destination_code_three).'"';
            }
            $search_result_three = $this->Stock->result_three_data($dest_one, $layer_code, $period);
            $total_amt_result_three = $this->Stock->sum_result_three($dest_one, $layer_code, $period);
            $cnt_three = count($total_amt_result_three);
            
            foreach ($search_result_three as $key => $val) {
                $desti_code = $val['stocks']['destination_name'];
            
                for ($q=0;$q<$cnt_three;$q++) {
                    $dest_code_three = $total_amt_result_three[$q]['stocks']['destination_name'];
                    $total_amount_three = $total_amt_result_three[$q][0]['total_amt'];
            
                    if ($desti_code == $dest_code_three) {
                        $search_result_three[$key]["amountofMoney"] = $total_amount_three;
                    }
                }
            }
            $this->set('search_result_three', $search_result_three);
        }
        
        $this->set('prev_month_1', $prev_month_1);
        $this->set('choose_month', $choose_month);
        $this->set('choose_year', $choose_year);
        $this->set('period', $period);
        $this->set('layer_code', $layer_code);
        $this->set('layer_name', $layer_name);
        $this->set('submission_deadline', $submission_deadline);

        $this->set('deadline_date', $deadline_date);
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
        $this->layout = 'stock';
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        
        $reference_date = date('Y-m-01', strtotime($period));
        
        //add base date(sumission dead line) add
        $condi["date_format(Stock.period,'%Y-%m')"] = $period;
        $submission_deadline = "";
        $reference_date = "";
        $deadline_date ="";
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
                    $deadline_date = $date[0]['deadline_date'];
                }
            }
        }
                
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
        $aligncenter = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
        );
        $alignleft_cmt = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
        
                ),
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
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
        $objPHPExcel->getActiveSheet()->getStyle('A16:H16')->applyFromArray(
            array(
                        'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'daeef3')
                    )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A:H')->getAlignment()->setWrapText(true);
        
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        if ($this->Session->check('SESSION_LAYER_CODE')) {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        } else {
            $layer_code = '';
        }
        $layer_name = $this->Session->read('StockSelections_BA_NAME');
        
        $search_result_one = '';
        
        // creation of First Table Design and Data
        $result_one = $this->Stock->Search_equalOver_1Million($layer_code, $period);

        if (!empty($result_one)) {
            foreach ($result_one as $row) {
                $destination_code[] = $row['tmp']['destination_name'];
                $dest_one = '"'.implode('","', $destination_code).'"';
            }
        
            $search_result_one = $this->Stock->result_one_data($dest_one, $layer_code, $period);
            $total_amt_result = $this->Stock->sum_result_one($dest_one, $layer_code, $period);

            
            $count_one = count($total_amt_result);
            
            foreach ($search_result_one as $key => $val) {
                $desti_code = $val['stocks']['destination_name'];
                
                for ($j=0;$j<count($total_amt_result);$j++) {
                    $destination_code = $total_amt_result[$j]['stocks']['destination_name'];
                    $total_amount = $total_amt_result[$j][0]['total_amt'];
                    
                    if ($desti_code == $destination_code) {
                        $search_result_one[$key]["amountofMoney"] = $total_amount;
                    }
                }
            }
        }
        
        //Creation of Second Table Design with Data
        
        $result_two =$this->Stock->Search_under_1Million_morethan30days($layer_code, $period);
        if (!empty($result_two)) {
            foreach ($result_two as $row) {
                $destination_code_two[] = $row['tmp']['destination_name'];
                $dest_one = '"'.implode('","', $destination_code_two).'"';
            }

            
            $search_result_two = $this->Stock->result_two_data($dest_one, $layer_code, $period);
            $total_amt_result_two = $this->Stock->sum_result_two($dest_one, $layer_code, $period);
            $count_two = count($total_amt_result_two);

            
            foreach ($search_result_two as $key => $val) {
                $desti_code = $val['stocks']['destination_name'];
                
                for ($j=0;$j<$count_two;$j++) {
                    $destination_code = $total_amt_result_two[$j]['stocks']['destination_name'];
                    $total_amount = $total_amt_result_two[$j][0]['total_amt'];
            
                    if ($desti_code == $destination_code) {
                        $search_result_two[$key]["amountofMoney"] = $total_amount;
                    }
                }
            }
        }        
        
        // creation of Third Table Design with Data
        $result_three =$this->Stock->Search_under_1Million_lessthan30days($layer_code, $period);
        
        if (!empty($result_three)) {
            foreach ($result_three as $row) {
                $destination_code_three[] = $row['tmp']['destination_name'];
                $dest_one = '"'.implode('","', $destination_code_three).'"';
            }
            $search_result_three = $this->Stock->result_three_data($dest_one, $layer_code, $period);
            $total_amt_result_three = $this->Stock->sum_result_three($dest_one, $layer_code, $period);
            $cnt_three = count($total_amt_result_three);

            
            foreach ($search_result_three as $key => $val) {
                $desti_code = $val['stocks']['destination_name'];
            
                for ($q=0;$q<$cnt_three;$q++) {
                    $dest_code_three = $total_amt_result_three[$q]['stocks']['destination_name'];
                    $total_amount_three = $total_amt_result_three[$q][0]['total_amt'];
            
                    if ($desti_code == $dest_code_three) {
                        $search_result_three[$key]["amountofMoney"] = $total_amount_three;
                    }
                }
            }
        }


        $sheet->getColumnDimension('A')->setVisible(false);
        // $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension("D")->setWidth(20);
        $sheet->getColumnDimension("E")->setWidth(20);
        $sheet->getColumnDimension("F")->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        
        $sheet->setCellValue('A1', __("【滞留債権リスト 速報版】"));
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1:H1')->applyFromArray($aligncenter);
        
        $start_row = 3;
        $end_row = $start_row + 4;
        
        $count = 0;
        $prev_dest_code = '';
        $prev_merge_cell = '';
        
        // $sheet->getStyle('A3:B3')->applyFromArray($border_dash);
        // $sheet->getStyle('A4:B4')->applyFromArray($border_dash);
        $sheet->getStyle('A5:B5')->applyFromArray($border_dash);
        $sheet->getStyle('A6:B6')->applyFromArray($border_dash);
        // $sheet->getStyle('A7:B7')->applyFromArray($border_dash);
        // $sheet->getStyle('A7:H7')->applyFromArray($aligncenter);
        
        $sheet->mergeCells('B'.$start_row .':'.'B'.$end_row);
        $sheet->mergeCells('C'.$start_row .':'.'C'.$end_row);
        $sheet->getStyle('B'.$start_row .':'.'B'.$end_row)->applyFromArray($border_dash);
        $sheet->getStyle('C'.$start_row .':'.'C'.$end_row)->applyFromArray($border_dash);
        
        $sheet->mergeCells('F'.$start_row .':'.'F'.$end_row);
        $sheet->getStyle('F'.$start_row .':'.'F'.$end_row)->applyFromArray($border_dash);
        $sheet->mergeCells('G'.$start_row .':'.'G'.$end_row);
        $sheet->getStyle('G'.$start_row .':'.'G'.$end_row)->applyFromArray($border_dash);
        $sheet->mergeCells('H'.$start_row .':'.'H'.$end_row);
        $sheet->getStyle('H'.$start_row .':'.'H'.$end_row)->applyFromArray($border_dash);

        
        $sheet->setCellValue('B2', __("管理本部長"));
        $sheet->getStyle('B2')->applyFromArray($aligncenter);
        $sheet->getStyle('B2')->applyFromArray($border_dash);
        $sheet->getStyle('B2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        
        $sheet->setCellValue('C2', __("業務管理部長"));
        $sheet->getStyle('C2')->applyFromArray($aligncenter);
        $sheet->getStyle('C2')->applyFromArray($border_dash);
        $sheet->getStyle('C2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->setCellValue('B3', "（　　/　　）");
        $sheet->setCellValue('C3', "（　　/　　）");
        
        $sheet->setCellValue('F2', __("部長"));
        $sheet->getStyle('F2')->applyFromArray($aligncenter);
        $sheet->getStyle('F2')->applyFromArray($border_dash);
        $sheet->getStyle('F2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->setCellValue('F3', "（　　/　　）");
        $sheet->setCellValue('F3', "（　　/　　）");
        
        $sheet->setCellValue('G2', "TL");
        $sheet->getStyle('G2')->applyFromArray($aligncenter);
        $sheet->getStyle('G2')->applyFromArray($border_dash);
        $sheet->getStyle('G2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->setCellValue('G3', "（　　/　　）");
        $sheet->setCellValue('G3', "（　　/　　）");
        
        $sheet->setCellValue('H2', __("担当者"));
        $sheet->getStyle('H2')->applyFromArray($aligncenter);
        $sheet->getStyle('H2')->applyFromArray($border_dash);
        $sheet->getStyle('H2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
        $sheet->setCellValue('H3', "（　　/　　）");
        $sheet->setCellValue('H3', "（　　/　　）");
        
        $sheet->setCellValue('B9', __("部署"));
        $sheet->getStyle('B9')->applyFromArray($alignleft);
        $sheet->getStyle('B9')->applyFromArray($border_dash);
        $sheet->setCellValue('C9', $layer_code);
        $sheet->getStyle('C9')->applyFromArray($alignleft);
        $sheet->getStyle('C9')->applyFromArray($border_dash);
        
        $sheet->setCellValue('B10', __("部署名"));
        $sheet->getStyle('B10')->applyFromArray($alignleft);
        $sheet->getStyle('B10')->applyFromArray($border_dash);
        $sheet->setCellValue('C10', $layer_name);
        $sheet->getStyle('C10')->applyFromArray($alignleft);
        $sheet->getStyle('C10')->applyFromArray($border_dash);
        
        $sheet->setCellValue('B11', __("対象月"));
        $sheet->getStyle('B11')->applyFromArray($alignleft);
        $sheet->getStyle('B11')->applyFromArray($border_dash);
        $sheet->setCellValue('C11', $period);
        $sheet->getStyle('C11')->applyFromArray($alignleft);
        $sheet->getStyle('C11')->applyFromArray($border_dash);
        
        $sheet->setCellValue('B12', __("基準年月日"));
        $sheet->getStyle('B12')->applyFromArray($alignleft);
        $sheet->getStyle('B12')->applyFromArray($border_dash);
        $sheet->setCellValue('C12', $submission_deadline);
        $sheet->getStyle('C12')->applyFromArray($alignleft);
        $sheet->getStyle('C12')->applyFromArray($border_dash);

        $sheet->setCellValue('B13', __("提出期日"));
        $sheet->getStyle('B13')->applyFromArray($alignleft);
        $sheet->getStyle('B13')->applyFromArray($border_dash);
        $sheet->setCellValue('C13', $deadline_date);
        $sheet->getStyle('C13')->applyFromArray($alignleft);
        $sheet->getStyle('C13')->applyFromArray($border_dash);
        
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);

        
        
        $sheet->setCellValue('B15', __("①滞留額100万円以上の取引先"));
        $sheet->mergeCells('B15:E15');
        // $sheet->setCellValue('A16', __("相手先コード"));
        // $sheet->getStyle('A16')->applyFromArray($border_dash);
        // $sheet->getStyle('A16')->applyFromArray($aligncenter);
        $sheet->setCellValue('B16', __("相手先名"));
        $sheet->getStyle('B16')->applyFromArray($border_dash);
        $sheet->getStyle('B16')->applyFromArray($aligncenter);
        $sheet->setCellValue('C16', __("部署"));
        $sheet->getStyle('C16')->applyFromArray($border_dash);
        $sheet->getStyle('C16')->applyFromArray($aligncenter);
        $sheet->setCellValue('D16', __("部署名"));
        $sheet->getStyle('D16')->applyFromArray($border_dash);
        $sheet->getStyle('D16')->applyFromArray($aligncenter);
        $sheet->setCellValue('E16', __("滞留日数"));
        $sheet->getStyle('E16')->applyFromArray($border_dash);
        $sheet->getStyle('E16')->applyFromArray($aligncenter);
        $sheet->setCellValue('F16', __("合計金額"));
        $sheet->getStyle('F16')->applyFromArray($border_dash);
        $sheet->getStyle('F16')->applyFromArray($aligncenter);
        $sheet->setCellValue('G16', __("金額"));
        $sheet->getStyle('G16')->applyFromArray($border_dash);
        $sheet->getStyle('G16')->applyFromArray($aligncenter);
        $sheet->setCellValue('H16', __("コメント"));
        $sheet->getStyle('H16')->applyFromArray($border_dash);
        $sheet->getStyle('H16')->applyFromArray($aligncenter);
     
        //start write data //
        $a = 17;

        if (!empty($search_result_one) || !empty($search_result_two) || !empty($search_result_three)) {
            if (!empty($search_result_one)) {
                foreach ($search_result_one as $row) {
                    $admin_comment = $row['busi_admin_cmt']['busi_admin_comment'];
                    $busi_admin_comment_array = preg_split("/\,/", $admin_comment);
                    $busi_admin_comment = h($busi_admin_comment_array['0']);

                    $inc_comment = $row['acc_inc_cmt']['acc_inc_comment'];
                    $acc_inc_comment_array = preg_split("/\,/", $inc_comment);
                    $acc_incharge_comment = h($acc_inc_comment_array[0]);

                    $submanager_comment = $row['acc_submgr_cmt']['acc_submanager_comment'];
                    $acc_submanager_comment_array = preg_split("/\,/", $submanager_comment);
                    $acc_submanager_comment = h($acc_submanager_comment_array[0]);
                            
                    // $destination_code = $row['saps']['destination_code'];
                    $destination_name =$row['stocks']['destination_name'];
                    $business_code = $row['stocks']['layer_code'];
                    $business_name = $row['stocks']['name_jp'];
                    // $schedule_date = $row['0']['schedule_date'];

                    //calculate number of days
                    // $diff = strtotime($submission_deadline) - strtotime($schedule_date);
                    // $numberofdays = round($diff / 86400);

                    $numberofdays = $row['stocks']['numbers_day'];
                    $amountofMoney = $row['amountofMoney'];
                    $jp_amount    = $row['stocks']['amount'];
                    $busi_inc_remark = $row['busi_inc_cmt']['remark'];
                    $busi_inc_settlement_date = $row['busi_inc_cmt']['settlement_date'];
                    if ($busi_inc_settlement_date == '0000-00-00' || '') {
                        $busi_inc_settlement_date = '';
                    }
                    $busi_inc_reason = $row['busi_inc_cmt']['reason'];
                    $select1 = (!empty($busi_inc_reason) && !empty($busi_inc_settlement_date)) ? '/' : '';
                    $select2 = (!empty($busi_inc_settlement_date) && !empty($busi_inc_remark)) ? '/' : '';

                    $add_cmt1 = $busi_inc_reason.$select1.$busi_inc_settlement_date.$select2.$busi_inc_remark;

                    $add_cmt1 = (!empty($add_cmt1)) ? '-'.$add_cmt1 : $add_cmt1;
                    
                    $add_cmt2 = (!empty($busi_admin_comment)) ? PHP_EOL.'-'.$busi_admin_comment : '';

                    $add_cmt3 = (!empty($acc_incharge_comment)) ? PHP_EOL.'-'.$acc_incharge_comment : '';

                    $rev_cmt4 = (!empty($acc_submanager_comment)) ? PHP_EOL.'-'.$acc_submanager_comment : '';
                    $first_tbl_cmt = $add_cmt1.$add_cmt2.$add_cmt3.$rev_cmt4;

                    if ($prev_dest_code != $destination_name) {
                        if ($prev_dest_code != '') { // FIRST row merge
                            $start = $a - $count;
                            $end = $a - 1;
                            // $objPHPExcel->getActiveSheet()->mergeCells('A'.$start .':'.'A'.$end);
                            $objPHPExcel->getActiveSheet()->mergeCells('F'.$start .':'.'F'.$end);
                            $objPHPExcel->getActiveSheet()->mergeCells('B'.$start .':'.'B'.$end);
                        
                            // $objPHPExcel->getActiveSheet()->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($border_dash);
                            // $objPHPExcel->getActiveSheet()->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($alignleft);
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($border_dash);
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($alignright);
                            $objPHPExcel->getActiveSheet()->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($border_dash);
                            $objPHPExcel->getActiveSheet()->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($alignleft);
                        }
                        $count = 0; //merge row count
                        // $objPHPExcel->getActiveSheet()->setCellValue('A'.$a, $destination_code);
                        // $objPHPExcel->getActiveSheet()->getStyle('A'.$a)->applyFromArray($border_dash);
                        // $objPHPExcel->getActiveSheet()->getStyle('A'.$a)->applyFromArray($alignleft);
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$a, $destination_name);
                        $objPHPExcel->getActiveSheet()->getStyle('B'.$a)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('B'.$a)->applyFromArray($alignleft);
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$a, $amountofMoney);
                        $objPHPExcel->getActiveSheet()->getStyle('F'.$a)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('F'.$a)->applyFromArray($alignright);
                        $objPHPExcel->getActiveSheet()->getStyle('F'.$a)->getNumberFormat()->setFormatCode('#,##0');
                        $prev_dest_code = $destination_name;
                    }
                
                    $count ++;//increase merge row count
                
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$a, $business_code);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$a)->applyFromArray($alignleft);
                    
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$a)->applyFromArray($alignleft);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$a)->applyFromArray($border_dash);
                
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$a, $business_name);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$a)->applyFromArray($alignleft);
                
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$a, $numberofdays);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$a)->applyFromArray($alignleft);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$a)->applyFromArray($border_dash);
                    
                    // $objPHPExcel->getActiveSheet()->setCellValue('F'.$a, $jp_amount);
                    // $objPHPExcel->getActiveSheet()->getStyle('F'.$a)->applyFromArray($alignright);
                    // $objPHPExcel->getActiveSheet()->getStyle('F'.$a)->applyFromArray($border_dash);

                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$a, $jp_amount);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$a)->applyFromArray($alignright);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$a)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$a)->getNumberFormat()->setFormatCode('#,##0');
                
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$a, $first_tbl_cmt);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$a)->applyFromArray($alignleft_cmt);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$a)->applyFromArray($border_dash);
                    $a++;
                }
                //END ROW MERGE CELL for first table
                $start = $a - $count;
                $end = $a - 1;
                // $objPHPExcel->getActiveSheet()->mergeCells('A'.$start .':'.'A'.$end);
                // $sheet->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($border_dash);
                // $sheet->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->mergeCells('B'.$start .':'.'B'.$end);
                $sheet->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($alignleft);
                $sheet->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->mergeCells('F'.$start .':'.'F'.$end);
                $sheet->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($alignright);
                $sheet->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$start .':'.'F'.$end)->getNumberFormat()->setFormatCode('#,##0');
            }
            // excel export for second table
            $b = $a + 3;
            $sheet->setCellValue('B'.$b, __("②滞留額100万円未満、滞留日数30日以上の取引先"));
            $sheet->mergeCells('B'.$b.':'.'E'.$b);
            $b++;
            $objPHPExcel->getActiveSheet()->getStyle('B'.$b .':'.'H'.$b)->applyFromArray(
                array(
                    'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'daeef3')
                    )
            )
            );
        
            // $sheet->setCellValue('A'.$b, __("相手先コード"));
            // $sheet->getStyle('A'.$b)->applyFromArray($border_dash);
            // $sheet->getStyle('A'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('B'.$b, __("相手先名"));
            $sheet->getStyle('B'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('B'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('C'.$b, __("部署"));
            $sheet->getStyle('C'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('C'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('D'.$b, __("部署名"));
            $sheet->getStyle('D'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('D'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('E'.$b, __("滞留日数"));
            $sheet->getStyle('E'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('E'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('F'.$b, __("合計金額"));
            $sheet->getStyle('F'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('F'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('G'.$b, __("金額"));
            $sheet->getStyle('G'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('G'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('H'.$b, __("コメント"));
            $sheet->getStyle('H'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('H'.$b)->applyFromArray($aligncenter);
           
            $b++;
        
            $count = 0;
            $prev_dest_code = '';
            if (!empty($search_result_two)) {
                foreach ($search_result_two as $rowTwo) {
                    $admin_comment = $rowTwo['busi_admin_cmt']['busi_admin_comment'];
                    $busi_admin_comment_array = preg_split("/\,/", $admin_comment);
                    $busi_admin_comment = h($busi_admin_comment_array['0']);

                    $inc_comment = $rowTwo['acc_inc_cmt']['acc_inc_comment'];
                    $acc_inc_comment_array = preg_split("/\,/", $inc_comment);
                    $acc_incharge_comment = h($acc_inc_comment_array[0]);

                    $submanager_comment = $rowTwo['acc_submgr_cmt']['acc_submanager_comment'];
                    $acc_submanager_comment_array = preg_split("/\,/", $submanager_comment);
                    $acc_submanager_comment = h($acc_submanager_comment_array[0]);

                    // $destination_code = $rowTwo['saps']['destination_code'];
                    $destination_name =$rowTwo['stocks']['destination_name'];
                    $business_code = $rowTwo['stocks']['layer_code'];
                    $business_name = $rowTwo['stocks']['name_jp'];
                    // $schedule_date = $rowTwo['0']['schedule_date'];
                    //calculate number of days
                    // $diff = strtotime($submission_deadline) - strtotime($schedule_date);
                    // $numberofdays = round($diff / 86400);
                    $numberofdays = $rowTwo['stocks']['numbers_day'];
                    $amountofMoney = $rowTwo['amountofMoney'];
                    $jp_amount = $rowTwo['stocks']['amount'];
                    // $comment = $rowTwo['stocks']['preview_comment'];
                    $busi_inc_remark = $rowTwo['busi_inc_cmt']['remark'];
                    $busi_inc_settlement_date = $rowTwo['busi_inc_cmt']['settlement_date'];
                    if ($busi_inc_settlement_date == '0000-00-00' || '') {
                        $busi_inc_settlement_date = '';
                    }
                    $busi_inc_reason = $rowTwo['busi_inc_cmt']['reason'];
                    $select1 = (!empty($busi_inc_reason) && !empty($busi_inc_settlement_date)) ? '/' : '';
                    $select2 = (!empty($busi_inc_settlement_date) && !empty($busi_inc_remark)) ? '/' : '';

                    $add_cmt1 = $busi_inc_reason.$select1.$busi_inc_settlement_date.$select2.$busi_inc_remark;

                    $add_cmt1 = (!empty($add_cmt1)) ? '-'.$add_cmt1 : $add_cmt1;
                    
                    $add_cmt2 = (!empty($busi_admin_comment)) ? PHP_EOL.'-'.$busi_admin_comment : '';

                    $add_cmt3 = (!empty($acc_incharge_comment)) ? PHP_EOL.'-'.$acc_incharge_comment : '';

                    $rev_cmt4 = (!empty($acc_submanager_comment)) ? PHP_EOL.'-'.$acc_submanager_comment : '';
                    $sec_tbl_cmt = $add_cmt1.$add_cmt2.$add_cmt3.$rev_cmt4;
                
                    if ($prev_dest_code != $destination_name) {
                        if ($prev_dest_code != '') { // FIRST row merge
                            $start = $b - $count;
                            $end = $b - 1;
                            // $objPHPExcel->getActiveSheet()->mergeCells('A'.$start .':'.'A'.$end);
                            $objPHPExcel->getActiveSheet()->mergeCells('B'.$start .':'.'B'.$end);
                            $objPHPExcel->getActiveSheet()->mergeCells('F'.$start .':'.'F'.$end);
                            // $objPHPExcel->getActiveSheet()->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($border_dash);
                            // $objPHPExcel->getActiveSheet()->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($alignleft);
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($border_dash);
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($alignright);
                            $objPHPExcel->getActiveSheet()->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($border_dash);
                            $objPHPExcel->getActiveSheet()->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($alignleft);
                        }
                        $count = 0; //merge row count
                        // $objPHPExcel->getActiveSheet()->setCellValue('A'.$b, $destination_code);
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$b, $amountofMoney);
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$b, $destination_name);
                        $objPHPExcel->getActiveSheet()->getStyle('B'.$b)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('B'.$b)->applyFromArray($alignleft);
                        // $objPHPExcel->getActiveSheet()->getStyle('A'.$b)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('F'.$b)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('F'.$b)->getNumberFormat()->setFormatCode('#,##0');
                
                        $prev_dest_code = $destination_name;
                    }
                    
                    $count ++;//increase merge row count
                    
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$b, $business_code);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$b)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$b)->applyFromArray($alignleft);
                
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$b)->applyFromArray($alignleft);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$b)->applyFromArray($border_dash);
                    
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$b, $business_name);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$b)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$b)->applyFromArray($alignleft);
                    
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$b, $numberofdays);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$b)->applyFromArray($alignleft);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$b)->applyFromArray($border_dash);
                

                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$b, $jp_amount);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$b)->applyFromArray($alignright);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$b)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$b)->getNumberFormat()->setFormatCode('#,##0');
                
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$b, $sec_tbl_cmt);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$b)->applyFromArray($alignleft_cmt);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$b)->applyFromArray($border_dash);
                    $b++;
                }
                //END ROW MERGE CELL for second table
                $start = $b - $count;
                $end = $b - 1;
                // $objPHPExcel->getActiveSheet()->mergeCells('A'.$start .':'.'A'.$end);
                // $sheet->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($border_dash);
                // $sheet->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->mergeCells('B'.$start .':'.'B'.$end);
                $objPHPExcel->getActiveSheet()->mergeCells('F'.$start .':'.'F'.$end);
                $sheet->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($border_dash);
                $sheet->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($alignleft);
                $sheet->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($alignright);
                $sheet->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$start .':'.'F'.$end)->getNumberFormat()->setFormatCode('#,##0');
            }
            // excel export for third table
            $b = $b + 3;
            
            $sheet->setCellValue('B'.$b, __("③滞留額100万円未満、滞留日数30日未満の取引先"));
            $sheet->mergeCells('B'.$b.':'.'E'.$b);
            $b++;
            $objPHPExcel->getActiveSheet()->getStyle('A'.$b .':'.'G'.$b)->applyFromArray(
                array(
                            'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'daeef3')
                            )
                    )
            );
            
            // $sheet->setCellValue('A'.$b, __("相手先コード"));
            // $sheet->getStyle('A'.$b)->applyFromArray($border_dash);
            // $sheet->getStyle('A'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('B'.$b, __("相手先名"));
            $sheet->getStyle('B'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('B'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('C'.$b, __("部署"));
            $sheet->getStyle('C'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('C'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('D'.$b, __("部署名"));
            $sheet->getStyle('D'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('D'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('E'.$b, __("滞留日数"));
            $sheet->getStyle('E'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('E'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('F'.$b, __("合計金額"));
            $sheet->getStyle('F'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('F'.$b)->applyFromArray($aligncenter);
            $sheet->setCellValue('G'.$b, __("金額"));
            $sheet->getStyle('G'.$b)->applyFromArray($border_dash);
            $sheet->getStyle('G'.$b)->applyFromArray($aligncenter);
            
            $b++;
            
            $count = 0;
            $prev_dest_code = '';
            
            if (!empty($search_result_three)) {
                foreach ($search_result_three as $rowThree) {
                    // $destination_code = $rowThree['saps']['destination_code'];
                    $destination_name =$rowThree['stocks']['destination_name'];
                    $business_code = $rowThree['stocks']['layer_code'];
                    $business_name = $rowThree['LayerGroup']['name_jp'];
                    // $schedule_date = $rowThree['0']['schedule_date'];
                    //calculate number of day
                    // $diff = strtotime($submission_deadline) - strtotime($schedule_date);
                    // $numberofdays = round($diff / 86400);
                    $numberofdays = $rowThree['stocks']['numbers_day'];            
                    $amountofMoney = $rowThree['amountofMoney'];
                    $jp_amount = $rowThree['0']['jp_amount'];
                    // $comment = $rowThree['saps']['preview_comment'];
                        
                    if ($prev_dest_code != $destination_name) {
                        if ($prev_dest_code != '') { // FIRST row merge
                            $start = $b - $count;
                            $end = $b - 1;
                            
                            // $objPHPExcel->getActiveSheet()->mergeCells('A'.$start .':'.'A'.$end);
                            $objPHPExcel->getActiveSheet()->mergeCells('B'.$start .':'.'B'.$end);
                            $objPHPExcel->getActiveSheet()->mergeCells('F'.$start .':'.'F'.$end);
                            // $objPHPExcel->getActiveSheet()->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($border_dash);
                            // $objPHPExcel->getActiveSheet()->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($alignleft);
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($border_dash);
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($alignright);
                            $objPHPExcel->getActiveSheet()->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($border_dash);
                            $objPHPExcel->getActiveSheet()->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($alignleft);
                        }
                        $count = 0; //merge row count
                        // $objPHPExcel->getActiveSheet()->setCellValue('A'.$b, $destination_code);
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$b, $amountofMoney);
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$b, $destination_name);
                        $objPHPExcel->getActiveSheet()->getStyle('B'.$b)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('B'.$b)->applyFromArray($alignleft);
                        // $objPHPExcel->getActiveSheet()->getStyle('A'.$b)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('F'.$b)->applyFromArray($border_dash);
                        $objPHPExcel->getActiveSheet()->getStyle('F'.$b)->getNumberFormat()->setFormatCode('#,##0');
                        $prev_dest_code = $destination_name;
                    }
                
                    $count ++;//increase merge row count
                
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$b, $business_code);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$b)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$b)->applyFromArray($alignleft);
                        
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$b)->applyFromArray($alignleft);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$b)->applyFromArray($border_dash);
                
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$b, $business_name);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$b)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$b)->applyFromArray($alignleft);
                
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$b, $numberofdays);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$b)->applyFromArray($alignleft);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$b)->applyFromArray($border_dash);

                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$b, $jp_amount);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$b)->applyFromArray($alignright);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$b)->applyFromArray($border_dash);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$b)->getNumberFormat()->setFormatCode('#,##0');
                    
                    $b++;
                }
                
                //END ROW MERGE CELL for third table
                $start = $b - $count;
                $end = $b - 1;
                
                // $objPHPExcel->getActiveSheet()->mergeCells('A'.$start .':'.'A'.$end);
                // $sheet->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($border_dash);
                // $sheet->getStyle('A'.$start .':'.'A'.$end)->applyFromArray($alignleft);
                $objPHPExcel->getActiveSheet()->mergeCells('B'.$start .':'.'B'.$end);
                $sheet->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($alignleft);
                $sheet->getStyle('B'.$start .':'.'B'.$end)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->mergeCells('G'.$start .':'.'G'.$end);
                $sheet->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($alignright);
                $sheet->getStyle('F'.$start .':'.'F'.$end)->applyFromArray($border_dash);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$start .':'.'F'.$end)->getNumberFormat()->setFormatCode('#,##0');
            }
            $this->PhpExcel->output("StockSummaryReports".".xlsx");
            $this->autoLayout = false;
            $this->render('index');
        } else {
            $param = array( __("export"));
            $msg = parent::getErrorMsg("SE017", $param);
            $this->Session->write('EXCEL_ERR_MSG', $msg);
            $this->redirect(array('controller'=>'StockSummaryReports', 'action'=>'index'));
        }
    }
}
