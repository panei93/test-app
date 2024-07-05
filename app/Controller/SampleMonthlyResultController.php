<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');


class SampleMonthlyResultController extends AppController
{
    public $uses = array('Sample','SampleTestResult');
    public $components = array('PhpExcel.PhpExcel','Session','Flash','Paginator');
    public $helpers = array('Html', 'Form');

    public function beforeFilter()
    {
        // parent::CheckSession();
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
            $condition['Sample.period'] = $period;
            $this->paginate = array(
                        'limit' => Paging::TABLE_PAGING,
                        'conditions' => $condition,
                        'order' => array('Sample.id' => 'asc')
                );

            $query_result = $this->Paginator->paginate('SampleTestResult');
            $language    = $this->Session->read('Config.language');
            $Common  = New CommonController();
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $header_list = $Common->getLayerNameOnCode($layer_code,$language);
            if (!empty($query_result)) {
               // $query_count = $this->SampleTestResult->paginateCount($condition);
                $query_count = count($query_result);
                $pageCount  = $this->params['paging']['SampleTestResult']['pageCount'];
                $count = parent::getSuccessMsg('SS004', $query_count);
                
                $this->set(compact('query_result', 'count', 'pageCount', 'header_list'));
                $this->render('index');
            } else {
                $no_data = parent::getErrorMsg("SE001");
                $this->set(compact('no_data', 'query_result'));
                $this->render('index');
            }
        } catch (NotFoundException $e) {
            $this->set('query_result', array());
            CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $this->redirect(array("controller" => "SampleMonthlyResult",
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
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $language     = $this->Session->read('Config.language');
        $query_result = $this->SampleTestResult->SampleMonthlyResult($period);
        #get header list to show
        $Common       = New CommonController(); 
        $header_list  = $Common->getLayerNameOnCode($layer_code,$language);
        $column       = chr(ord("B")+(count($header_list)-1));
        $end_column   = chr(ord($column)+12);
        $col_count    = count($header_list)+12;
        $obj = $this->PhpExcel->createWorksheet()->setDefaultFont('Calibri', 12);
        $obj->setActiveSheetIndex(0);
        $obj->getActiveSheet()->setTitle('ProgressManagement(summary)');
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
        // $obj->getActiveSheet()->getColumnDimension('B:'.$end_column)->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("D")->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("E")->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("F")->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("I")->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("J")->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("K")->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("L")->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("M")->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("L")->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("O")->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("P")->setWidth(10);
        // $obj->getActiveSheet()->getColumnDimension("Q")->setWidth(10);
        $val = 'B';
        for($i=0; $i<$col_count; $i++) {
            $obj->getActiveSheet()->getColumnDimension($val)->setWidth(10);
            $val = chr(ord($val)+1);
        }

        $objDrawing = new PHPExcel_Worksheet_Drawing();

        //Table Start
        $obj->getActiveSheet()->mergeCells('B1:'.$end_column.'1');
        $obj->getActiveSheet()->getStyle('B1:'.$end_column.'1')->applyFromArray($aligncenterheader);
        $obj->getActiveSheet()->getStyle('B1:'.$end_column.'1')->getFont()->setSize(18)->setBold(true);
        $obj->getActiveSheet()->setCellValue('B1', __('進捗管理(サマリー版)'));

        $obj->getActiveSheet()->setCellValue('B3', __('対象月'));
        $obj->getActiveSheet()->getStyle('B3')->getFont()->setBold(true);

        $obj->getActiveSheet()->setCellValue('C3', $period);

        // $obj->getActiveSheet()->mergeCells('B5:C5');
        // $obj->getActiveSheet()->getStyle('B5:C5')->getFont()->setBold(true);
        // $obj->getActiveSheet()->setCellValue('B5', '本部');
        // $obj->getActiveSheet()->getStyle('B5:C5')->applyFromArray($aligncenter);

        // $obj->getActiveSheet()->mergeCells('D5:E5');
        // $obj->getActiveSheet()->getStyle('D5:E5')->getFont()->setBold(true);
        // $obj->getActiveSheet()->setCellValue('D5', '部');
        // $obj->getActiveSheet()->getStyle('D5:E5')->applyFromArray($aligncenter);

        // $obj->getActiveSheet()->setCellValue('F5', 'BA');
        // $obj->getActiveSheet()->getStyle('F5')->getFont()->setBold(true);
        // $obj->getActiveSheet()->getStyle('F5')->applyFromArray($aligncenter);

        $head_start = "B";
        foreach($header_list as $header) {
            $obj->getActiveSheet()->mergeCells($head_start.'5:'.chr(ord($head_start)+1).'5');
            $obj->getActiveSheet()->getStyle($head_start.'5:'.chr(ord($head_start)+1).'5')->getFont()->setBold(true);
            $obj->getActiveSheet()->setCellValue($head_start.'5', $header);
            $obj->getActiveSheet()->getStyle($head_start.'5:'.chr(ord($head_start)+1).'5')->applyFromArray($aligncenter);
            $head_start = chr(ord($head_start)+2);
        }

        $obj->getActiveSheet()->setCellValue($head_start.'5', __('部署'));
        $obj->getActiveSheet()->getStyle($head_start.'5')->getFont()->setBold(true);
        $obj->getActiveSheet()->getStyle($head_start.'5')->applyFromArray($aligncenter);

        $obj->getActiveSheet()->setCellValue((chr(ord($head_start)+1)).'5', __('カテゴリー'));
        $obj->getActiveSheet()->getStyle((chr(ord($head_start)+1)).'5')->getFont()->setBold(true);
        $obj->getActiveSheet()->getStyle((chr(ord($head_start)+1)).'5')->applyFromArray($aligncenter);

        $obj->getActiveSheet()->mergeCells((chr(ord($head_start)+2)).'5:'.(chr(ord($head_start)+3)).'5');
        $obj->getActiveSheet()->getStyle((chr(ord($head_start)+2)).'5:'.(chr(ord($head_start)+3)).'5')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue((chr(ord($head_start)+2)).'5', __('サンプル数'));
        $obj->getActiveSheet()->getStyle((chr(ord($head_start)+2)).'5:'.(chr(ord($head_start)+3)).'5')->applyFromArray($aligncenter);

        $obj->getActiveSheet()->mergeCells((chr(ord($head_start)+4)).'5:'.(chr(ord($head_start)+5)).'5');
        $obj->getActiveSheet()->getStyle((chr(ord($head_start)+4)).'5:'.(chr(ord($head_start)+5)).'5')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue((chr(ord($head_start)+4)).'5', __('リザルト数'));
        $obj->getActiveSheet()->getStyle((chr(ord($head_start)+4)).'5:'.(chr(ord($head_start)+5)).'5')->applyFromArray($aligncenter);

        $obj->getActiveSheet()->mergeCells((chr(ord($head_start)+6)).'5:'.(chr(ord($head_start)+7)).'5');
        $obj->getActiveSheet()->getStyle((chr(ord($head_start)+6)).'5:'.(chr(ord($head_start)+7)).'5')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue((chr(ord($head_start)+6)).'5', __('完了数'));
        $obj->getActiveSheet()->getStyle((chr(ord($head_start)+6)).'5:'.(chr(ord($head_start)+7)).'5')->applyFromArray($aligncenter);

        $obj->getActiveSheet()->mergeCells((chr(ord($head_start)+8)).'5:'.(chr(ord($head_start)+9)).'5');
        $obj->getActiveSheet()->getStyle((chr(ord($head_start)+8)).'5:'.(chr(ord($head_start)+9)).'5')->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValue((chr(ord($head_start)+8)).'5', __('ペンディング'));
        $obj->getActiveSheet()->getStyle((chr(ord($head_start)+8)).'5:'.(chr(ord($head_start)+9)).'5')->applyFromArray($aligncenter);
    
        
    
        array_pop($header_list);
        $row_num = 6;
        // pr($query_result);
        // die();
        foreach ($query_result as $result) {
            // $head_department = $result['summary']['head_department'];
            // $department		=$result['summary']['department'];
            $header_name = explode(",",$result['summary']['layers_group_name']);
            $layer_code		=$result['summary']['layer_code'];
            $category		=$result['summary']['category'];
            $name_jp		=$result['summary']['name_tmp'];
            $sample_number	=$result['summary']['sample_number'];
            $result_number	=$result['summary']['result_number'];
            $completions	=$result['summary']['completions'];

            if ($result['summary']['sample_number'] == '' && $result['summary']['completions'] == '') {
                $pending = '';
            } else {
                $pending = $result['summary']['sample_number']-$result['summary']['completions'];
            }

            $head_start = "B";$k = 0;
            foreach ($header_list as $key=>$header) {
                $obj->getActiveSheet()->mergeCells($head_start.$row_num.':'.chr(ord($head_start)+1).$row_num);
                $obj->getActiveSheet()->setCellValue($head_start.$row_num, $header_name[$k]);
                $obj->getActiveSheet()->getStyle($head_start.$row_num.':'.chr(ord($head_start)+1).$row_num)->applyFromArray($alignleft);
                $head_start = chr(ord($head_start)+2);
                $k++;
            }

            $obj->getActiveSheet()->mergeCells($head_start.$row_num.':'.chr(ord($head_start)+1).$row_num);
            $obj->getActiveSheet()->setCellValue($head_start.$row_num, $name_jp);
            $obj->getActiveSheet()->getStyle($head_start.$row_num.':'.chr(ord($head_start)+1).$row_num)->applyFromArray($alignleft);

            // $obj->getActiveSheet()->mergeCells('D'.$row_num.':E'.$row_num);
            // $obj->getActiveSheet()->setCellValue('D'.$row_num, $department);
            // $obj->getActiveSheet()->getStyle('D'.$row_num.':E'.$row_num)->applyFromArray($alignleft);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+2).$row_num, $layer_code);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+2).$row_num, $layer_code)->applyFromArray($alignleft);

            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+3).$row_num, $category);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+3).$row_num, $category)->applyFromArray($aligncenter);
            // $obj->getActiveSheet()->mergeCells('G'.$row_num.':I'.$row_num);
            // $obj->getActiveSheet()->setCellValue('G'.$row_num, $name_jp);
            // $obj->getActiveSheet()->getStyle('G'.$row_num.':I'.$row_num)->applyFromArray($alignleft);

            $obj->getActiveSheet()->mergeCells(chr(ord($head_start)+4).$row_num.':'.chr(ord($head_start)+5).$row_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+4).$row_num, $sample_number);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+4).$row_num.':'.chr(ord($head_start)+5).$row_num)->applyFromArray($alignright);

            $obj->getActiveSheet()->mergeCells(chr(ord($head_start)+6).$row_num.':'.chr(ord($head_start)+7).$row_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+6).$row_num, $result_number);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+6).$row_num.':'.chr(ord($head_start)+7).$row_num)->applyFromArray($alignright);

            $obj->getActiveSheet()->mergeCells(chr(ord($head_start)+8).$row_num.':'.chr(ord($head_start)+9).$row_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+8).$row_num, $completions);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+8).$row_num.':'.chr(ord($head_start)+9).$row_num)->applyFromArray($alignright);

            $obj->getActiveSheet()->mergeCells(chr(ord($head_start)+10).$row_num.':'.chr(ord($head_start)+11).$row_num);
            $obj->getActiveSheet()->setCellValue(chr(ord($head_start)+10).$row_num, $pending);
            $obj->getActiveSheet()->getStyle(chr(ord($head_start)+10).$row_num.':'.chr(ord($head_start)+11).$row_num)->applyFromArray($alignright);


            $row_num++;
        }
        
        $sheet = $this->PhpExcel->getActiveSheet();

        $this->PhpExcel->output("ProgressManagement(summary)".".xlsx");
        $this->autoLayout = false;
        $this->render('index');
    }
}
