<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');

class SampleMonthlyProgressController extends AppController
{
    public $uses = array('Sample','Layer','SampleTestResult');
    public $components = array('PhpExcel.PhpExcel','Session','Flash','Paginator');
    public $helpers = array('Html', 'Form');

    public function beforeFilter()
    {
        // parent::checkUserStatus();
        // parent::CheckSession();
        // parent::checkAccessType();
        // parent::sampleCheckSession();
        $Common = New CommonController();

        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);

        // if((!in_array($layer_code, array_keys($permissions['index']['layers']))) || ($layer_code=="" && $permissions['index']['limit']>0)) {
        //     $errorMsg = parent::getErrorMsg('SE065');
        //     $this->Flash->set($errorMsg, array("key"=>"Error"));
        //     $this->render("index");
        // }
    }
    
    /**
     *
     * @author Aye Zar Ni Kyaw
     *
     * @param request data
     * @return view
     */
    public function index()
    {
        $this->layout = 'samplecheck';
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');

        // $paginate
        try {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $Common  = New CommonController();
            $language    = $this->Session->read('Config.language');
            #get header list to show 
            $header_list = $Common->getLayerNameOnCode($layer_code,$language);
            
            $check       = ($language == 'eng' ? 1 : 0);
            $condition['period'] = $period;
            $condition['check']       = $check;
            $this->paginate = array(
                        'limit' => Paging::TABLE_PAGING,
                        'conditions' => $condition,
                        'order' => array('Sample.id' => 'asc')
                );
            
            $query_result = $this->Paginator->paginate('Sample');
            if (!empty($query_result)) {
                $query_count = $this->Sample->paginateCount($condition);
                $pageCount  = $this->params['paging']['Sample']['pageCount'];
                $count = parent::getSuccessMsg('SS004', $query_count);

                $this->set(compact('query_result', 'count', 'pageCount','header_list'));
                $this->render('index');
            } else {
                $no_data = parent::getErrorMsg("SE001");
                $this->set(compact('no_data', 'query_result'));
                $this->render('index');
            }
        } catch (NotFoundException $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $this->set('query_result', array());
            $this->redirect(array("controller" => "SampleMonthlyProgress",
                    "action" => "index"));
        }
    }
    /**
     *
     * @author Aye Zar Ni Kyaw
     *
     * @param request data
     * @return view
     */

    public function excel_download()
    {
        $period  = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $layer_code   = $this->Session->read('SESSION_LAYER_CODE');
        $language     = $this->Session->read('Config.language');
        #get header list to show
        $Common       = New CommonController(); 
        $header_list  = $Common->getLayerNameOnCode($layer_code,$language);
        $check        = ($language == 'eng' ? 1 : 0);
        $query_result = $this->Sample->MonnthlyProgress($period,$check);
        $column       = chr(ord("B")+(count($header_list)-1));
        $end_column   = chr(ord($column)+9);
        $col_count    = count($header_list)+9;
        
        $obj = $this->PhpExcel->createWorksheet()->setDefaultFont('Calibri', 12);
        $obj->setActiveSheetIndex(0);
        $obj->getActiveSheet()->setTitle('ProgressManagement(detail)');
        $obj->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $obj->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $obj->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $obj->getActiveSheet()->setShowGridlines(true);

        $obj->getActiveSheet()->getStyle('B5:'.$end_column.'5')->applyFromArray(
            array(
                            'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'daeef3')
                            )
                    )
        );
        $obj->getActiveSheet()->getStyle('B6:'.$end_column.'6')->applyFromArray(
            array(
                            'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'daeef3')
                            )
                    )
        );
        $aligncenter = array(
                    'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ),
                    'borders' => array(
                            'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN)
        ));
        $aligncenterheader = array(
                    'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ),
                    'borders' => array(
                            'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_NONE)
        ));
        $alignleft = array(
                    'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ),
                    'borders' => array(
                            'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN)
        ));
        $alignright = array(
                    'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ),
                    'borders' => array(
                            'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN)
        ));
        $obj->getActiveSheet()->getStyle('A:'.$end_column)->getAlignment()->setWrapText(true);

        $obj->getActiveSheet()->getColumnDimension('A')->setWidth(0);
        $val = 'B';
        for($i=0; $i<$col_count; $i++) {
            $obj->getActiveSheet()->getColumnDimension($val)->setWidth(15);
            $val = chr(ord($val)+1);
        }
        $objDrawing = new PHPExcel_Worksheet_Drawing();

        //Table Start
        $obj->getActiveSheet()->mergeCells('B1:'.$end_column.'1');
        $obj->getActiveSheet()->getStyle('B1:'.$end_column.'1')->applyFromArray($aligncenterheader);
        $obj->getActiveSheet()->getStyle('B1:'.$end_column.'1')->getFont()->setSize(18)->setBold(true);
        $obj->getActiveSheet()->setCellValue('B1', __('進捗管理(詳細版)'));

        $obj->getActiveSheet()->setCellValue('B3', __('対象月'));
        $obj->getActiveSheet()->getStyle('B3')->getFont()->setBold(true);

        $obj->getActiveSheet()->setCellValue('C3', $period);
        $head_start = "B";
        foreach($header_list as $header) {
            $obj->getActiveSheet()->mergeCells($head_start.'5:'.$head_start.'6');
            $obj->getActiveSheet()->getStyle($head_start.'5:'.$head_start.'6')->getFont()->setBold(true);
            $obj->getActiveSheet()->setCellValue($head_start.'5', $header);
            $obj->getActiveSheet()->getStyle($head_start.'5:'.$head_start.'6')->applyFromArray($aligncenter);
            $head_start = chr(ord($head_start)+1);
        }

        $obj->getActiveSheet()->mergeCells($head_start.'5:'.$head_start.'6');
        $obj->getActiveSheet()->getStyle($head_start.'5:'.$head_start.'6')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue($head_start.'5', __('部署'));
        $obj->getActiveSheet()->getStyle($head_start.'5:'.$head_start.'6')->applyFromArray($aligncenter);

        $obj->getActiveSheet()->mergeCells(chr(ord($head_start)+1).'5:'.chr(ord($head_start)+1).'6');
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+1).'5:'.chr(ord($head_start)+1).'6')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+1).'5', __('カテゴリー'));
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+1).'5:'.chr(ord($head_start)+1).'6')->applyFromArray($aligncenter);

        $obj->getActiveSheet()->mergeCells(chr(ord($head_start)+2).'5:'.chr(ord($head_start)+2).'6');
        $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+2).'5', '');
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+2).'5:'.chr(ord($head_start)+2).'6')->applyFromArray($aligncenter);

        // $obj->getActiveSheet()->mergeCells(chr(ord($head_start)+2).'5:'.chr(ord($head_start)+2).'6');
        // $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+2).'5', '');
        // $obj->getActiveSheet()->getStyle(chr(ord($head_start)+2).'5:'.chr(ord($head_start)+2).'6')->applyFromArray($aligncenter);
        
        $obj->getActiveSheet()->mergeCells(chr(ord($head_start)+3).'5:'.chr(ord($head_start)+5).'5');
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+3).'5:'.chr(ord($head_start)+5).'5')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+3).'5', __('財務経理部'));
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+3).'5:'.chr(ord($head_start)+5).'5')->applyFromArray($aligncenter);

        $obj->getActiveSheet()->mergeCells(chr(ord($head_start)+6).'5:'.chr(ord($head_start)+8).'5');
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+6).'5:'.chr(ord($head_start)+8).'5')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+6).'5', __('営業'));
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+6).'5:'.chr(ord($head_start)+8).'5')->applyFromArray($aligncenter);

        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+3).'6')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+3).'6', __('担当者'));
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+3).'6')->applyFromArray($aligncenter);
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+3).'6')->getAlignment()->setWrapText(true);

        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+4).'6')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+4).'6', __('管理職'));
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+4).'6')->applyFromArray($aligncenter);

        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+5).'6')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+5).'6', __('責任者'));
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+5).'6')->applyFromArray($aligncenter);
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+5).'6')->getAlignment()->setWrapText(true);

        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+6).'6')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+6).'6', __('担当者'));
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+6).'6')->applyFromArray($aligncenter);
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+6).'6')->getAlignment()->setWrapText(true);

        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+7).'6')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+7).'6', __('管理職'));
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+7).'6')->applyFromArray($aligncenter);

        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+8).'6')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+8).'6', __('責任者'));
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+8).'6')->applyFromArray($aligncenter);
        $obj->getActiveSheet()->getStyle(chr(ord($head_start)+8).'6')->getAlignment()->setWrapText(true);

        
    
        $start = 7;
        $end   = 11;
        $value1 = 7;
        $value2 = 8;
        $value3 = 9;
        $value4 = 10;
        $value5 = 11; 
        array_pop($header_list);   
        foreach ($query_result as $result) {
            //$head_department  = $result['sample_acc_incharge_data']['head_department'];
            //$department       = $result['sample_acc_incharge_data']['department'];
            $header_name        = explode(",",$result['sample_acc_incharge_data']['head_name']);
            $layer_code		= $result['sample_acc_incharge_data']['layer_code'];
            $name_jp		= ($language == 'eng') ? $result['sample_acc_incharge_data']['name_en'] : $result['sample_acc_incharge_data']['name_jp'];
            $category		= $result['sample_acc_incharge_data']['category'];
            $sample_acc_incharge_num = $result['sample_acc_incharge_data']['sample_acc_incharge_num'];
            $sample_acc_sub_manager_num  =  $result['sample_acc_sub_manager_data']['sample_acc_sub_manager_num'];
            $sample_acc_manager_num	= $result['sample_acc_manager_data']['sample_acc_manager_num'];

            $data_bus_incharge_num = $result['data_bus_incharge_data']['data_bus_incharge_num'];
            $data_bus_sub_manager_num = $result['data_bus_sub_manager_data']['data_bus_sub_manager_num'];
            $data_bus_manager_num = $result['data_bus_manager_data']['data_bus_manager_num'];
            $result_acc_incharge_num = $result['result_acc_incharge_data']['result_acc_incharge_num'];
            $result_acc_sub_manager_num	= $result['result_acc_sub_manager_data']['result_acc_sub_manager_num'];
            $result_acc_manager_num	= $result['result_acc_manager_data']['result_acc_manager_num'];

            $check_bus_incharge_num = $result['check_bus_incharge_data']['check_bus_incharge_num'];
            $check_bus_sub_manager_num = $result['check_bus_sub_manager_data']['check_bus_sub_manager_num'];
            $check_bus_manager_num = $result['check_bus_manager_data']['check_bus_manager_num'];

            $wrap_acc_incharge_num = $result['wrap_acc_incharge_data']['wrap_acc_incharge_num'];
            $wrap_acc_sub_manager_num = $result['wrap_acc_sub_manager_data']['wrap_acc_sub_manager_num'];
            $wrap_acc_manager_num = $result['wrap_acc_manager_data']['wrap_acc_manager_num'];
            $head_start = "B";
            $k=0;
            foreach ($header_list as $key=>$header) {
                $obj->getActiveSheet()->mergeCells($head_start.$start.':'.$head_start.$end);
                $obj->getActiveSheet()->setCellValue($head_start.$start, $header_name[$k]);
                $obj->getActiveSheet()->getStyle($head_start.$start.':'.$head_start.$end)->applyFromArray($alignleft);
                $head_start = chr(ord($head_start)+1);
                $k++;
            }
            // die();          

            $obj->getActiveSheet()->mergeCells($head_start.$start.':'.$head_start.$end);
            $obj->getActiveSheet()->setCellValue($head_start.$start, $name_jp);
            $obj->getActiveSheet()->getStyle($head_start.$start.':'.$head_start.$end)->applyFromArray($alignleft); 

            $obj->getActiveSheet()->mergeCells(chr(ord($head_start)+1).$start.':'.chr(ord($head_start)+1).$end);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+1).$start, $layer_code);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+1).$start.':'.chr(ord($head_start)+1).$end)->applyFromArray($alignleft);
            $obj->getActiveSheet()->mergeCells(chr(ord($head_start)+2).$start.':'.chr(ord($head_start)+2).$end);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+2).$start, $category);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+2).$start.':'.chr(ord($head_start)+2).$end)->applyFromArray($alignleft);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+3).$value1, __('サンプル作成'));
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+3).$value1)->applyFromArray($alignleft);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+3).$value2, __('データ入力'));
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+3).$value2)->applyFromArray($alignleft);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+3).$value3, __('テスト結果作成'));
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+3).$value3)->applyFromArray($alignleft);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+3).$value4, __('フィードバック'));
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+3).$value4)->applyFromArray($alignleft);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+3).$value5, __('改善状況報告'));
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+3).$value5)->applyFromArray($alignleft);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+4).$value1, $sample_acc_incharge_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+5).$value1, $sample_acc_sub_manager_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+6).$value1, $sample_acc_manager_num);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+4).$value1.':'.chr(ord($head_start)+9).$value1)->applyFromArray($alignright);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+7).$value2, $data_bus_incharge_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+8).$value2, $data_bus_sub_manager_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+9).$value2, $data_bus_manager_num);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+4).$value2.':'.chr(ord($head_start)+9).$value2)->applyFromArray($alignright);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+4).$value3, $result_acc_incharge_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+5).$value3, $result_acc_sub_manager_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+6).$value3, $result_acc_manager_num);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+4).$value3.':'.chr(ord($head_start)+9).$value3)->applyFromArray($alignright);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+7).$value4, $check_bus_incharge_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+8).$value4, $check_bus_sub_manager_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+9).$value4, $check_bus_manager_num);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+4).$value4.':'.chr(ord($head_start)+9).$value4)->applyFromArray($alignright);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+4).$value5, $wrap_acc_incharge_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+5).$value5, $wrap_acc_sub_manager_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+6).$value5, $wrap_acc_manager_num);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+4).$value5.':'.chr(ord($head_start)+9).$value5)->applyFromArray($alignright);

            $value1 += 5;
            $value2 += 5;
            $value3 += 5;
            $value4 += 5;
            $value5 += 5;
            $start  += 5;
            $end    += 5;
        }         
        $sheet = $this->PhpExcel->getActiveSheet();
        $this->PhpExcel->output("ProgressManagement(detail)".".xlsx");
        $this->autoLayout = false;
        $this->render('index');
    }
}
