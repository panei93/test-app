<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'BrmAccounts');
App::import('Controller', 'Calculation');

/**
 * BudgetResultDifferences Controller
 *
 * @property BudgetResultDifference $BudgetResultDifference
 * @property PaginatorComponent $Paginator
 */
class BrmBudgetResultDifferenceController extends AppController
{

    /**
     * Components
     *
     * @var array
     */
    public $uses = array('LayerType','BrmActualResultSummary','BrmBudget');
    public $components = array('Paginator','PhpExcel.PhpExcel', 'Session');

    /**
     * Check Session before render page
     * @author Khin Hnin Myo (20200218)
     * @return void
     */
    public function beforeFilter()
    {
        $Common = New CommonController();
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id    = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id   = $this->Session->read('LOGIN_ID');
        $pagename   = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        $layers = array_keys($permissions['index']['layers']);
        if($permissions['index']['limit']< 0) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        } 
        if(!empty($this->request->query('target_month')) && !empty($this->request->query('term_id'))){
            $target_month 	= $this->request->query('target_month');
            $term_id 		= $this->request->query('term_id');
            $term_name 		= $this->request->query('term_name');
           
        }else{
            $target_month   = $this->Session->read('TARGETMONTH');
            $term_name      = $this->Session->read('TERM_NAME');
            $term_id        = $this->Session->read('TERM_ID');

        }
        if($target_month != '' && $term_id != '' && $term_name != ''){        
            $this->Session->write('TARGETMONTH', $target_month);
            $this->Session->write('TERM_ID', $term_id);
            $this->Session->write('TERM_NAME', $term_name);
        }else{
            if ($this->Session->read('Config.language') == 'eng') {
                $lang_name = 'en';
            } else {
                $lang_name = 'jp';
            }
            $top_layer_type     = Setting::LAYER_SETTING['topLayer'];
            $top_layer_name = $this->LayerType->find('list', array(
                'fields' => 'name_'.$lang_name,
                'conditions' => array(
                    'type_order' => $top_layer_type,
                )
            ))[$top_layer_type];
			$errorMsg = parent::getErrorMsg('SE132', $top_layer_name);
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        }
        
    }
    
    /**
     * index method
     *
     * @author Khin Hnin Myo (20200218),modify by SST 31.10.2022
     * @return void
     */
    public function index()
    {
        $this->layout = 'phase_3_menu';
        if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}
        $top_layer_type     = Setting::LAYER_SETTING['topLayer'];
        $middle_layer_type  = Setting::LAYER_SETTING['middleLayer'];
        $bottom_layer_type  = Setting::LAYER_SETTING['bottomLayer'];
        $top_layer_name = $this->LayerType->find('list', array(
            'fields' => 'name_'.$lang_name,
            'conditions' => array(
                'type_order' => $top_layer_type,
            )
        ))[$top_layer_type];
        $this->Session->write('TOP_LAYER_NAME',$top_layer_name);
        $middle_layer_name = $this->LayerType->find('list', array(
            'fields' => 'name_'.$lang_name,
            'conditions' => array(
                'type_order' => $middle_layer_type,
            )
        ))[$middle_layer_type];
        $this->Session->write('MIDDLE_LAYER_NAME',$middle_layer_name);
        $bottom_layer_name = $this->LayerType->find('list', array(
            'fields' => 'name_'.$lang_name,
            'conditions' => array(
                'type_order' => $bottom_layer_type,
            )
        ))[$bottom_layer_type];
        $this->Session->write('BOTTOM_LAYER_NAME',$bottom_layer_name);
        $tab = $top_layer_name;
        
        //$this->Session->write('TOP_LAYER_NAME',$tab);
        //$this->Session->write('TAB', $tab);
        # get data from session
        $admin_level_id = $this->Session->read('ADMIN_LEVEL_ID');
        $permission     = $this->Session->read('PERMISSIONS');
        #get read_limit 1 = all  or 2 = head
        $read_limit   = $permission['index']['limit'];
        if (empty($read_limit) || $read_limit >= '2') {
            //$errorMsg = parent::getErrorMsg('SE065');
            //$this->Flash->set($errorMsg, array("key"=>"TermError"));
            //$this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }
        $loginID        = $this->Session->read('LOGIN_ID'); #id
        $login_id       = $this->Session->read('LOGINID'); #login_id
        $target_month   = $this->Session->read('TARGETMONTH');
        $term_id        = $this->Session->read('TERM_ID');
        $term_name      = $this->Session->read('TERM_NAME');
            
        $id_array = "";
        $id_string = "0"; #for all head_quarter excel download
       
        if (!empty($this->params['url'])) {
            $tab = $this->params['url']['tab'];
            $tab = ($tab == '') ? $top_layer_name : $tab;
            //$click_id = $this->params['url']['id'];
            $click_layer_code = $this->params['url']['layer_code'];//layer_code
            //$parent_id = $click_id;//layer_code
            $parent_layer_code = $click_layer_code;//layer_code
            #for back link id
            $this->Session->write('BACK_LAYER_CODE', $click_layer_code);//layer_code
            if($tab == $top_layer_name){
                $this->Session->DELETE('BACK_TO_MIDDLE_LAYER');//layer_code
                $this->Session->DELETE('BACK_TO_BOTTOM_LAYER');//layer_code
                $this->Session->DELETE('BACK_TO_OTHER_LAYER');//layer_code

                $this->Session->DELETE('BACK_TO_MIDDLE_LAYER_NAME', $middle_layer_name);//layer_name
                $this->Session->DELETE('BACK_TO_BOTTOM_LAYER_NAME', $bottom_layer_name);//layer_name
            }else if($tab == $middle_layer_name){
                //$this->Session->write('BACK_TO_MIDDLE_LAYER', $click_id);//layer_code
                $this->Session->write('BACK_TO_MIDDLE_LAYER', $click_layer_code);//layer_code
                $this->Session->write('BACK_TO_MIDDLE_LAYER_NAME', $middle_layer_name);//layer_code
            }else if($tab == $bottom_layer_name){
                //$this->Session->write('BACK_TO_BOTTOM_LAYER', $click_id);//layer_code                
                $this->Session->write('BACK_TO_BOTTOM_LAYER', $click_layer_code);//layer_code
                $this->Session->write('BACK_TO_BOTTOM_LAYER_NAME', $bottom_layer_name);//layer_code
            }
            else{//may be logistic?
                $this->Session->write('BACK_TO_OTHER_LAYER', $click_layer_code);//layer_code
            }
        }        
       
        # Get user's BA related head_dept_id
        
        $select_data = [];
        # calculate amount
        $get_calculate = $this->getCacheData($tab, $target_month, $term_id, $id_array, $id_string, $click_layer_code, $term_name,$top_layer_type);
        $budget_result  = $get_calculate[0];
        $search_total   = $get_calculate[1];
        $start_month    = $get_calculate[2];
        $parent_name    = $get_calculate[3];
        $select_data    = $get_calculate[4];
        
        $extra_logistics = Cache::read('EXTRA_LOGIS');
        # show error msg if no data to show table
        if (empty($budget_result)) {
            $this->set('errmsg', parent::getErrorMsg('SE001'));
        }
        if ($parent_name == '' && $tab == 'Logistic') {
            $parent_name = $this->Layer->find('list', array(
                'conditions' => array(
                    'layer_code' => $parent_layer_code
                ),
                'fields' => array('layer_code','name_jp')
            ))[$parent_layer_code];
        }
        $this->set(compact('top_layer_name','middle_layer_name','bottom_layer_name','admin_level_id', 'target_month', 'start_month', 'term_id', 'term_name', 'id_string', 'id_array','budget_result', 'select_data', 'search_total', 'tab', 'limit', 'permit_head_id', 'parent_name','parent_layer_code', 'extra_logistics'));
        $this->render('index');
    }

    /**
     * SearchHQuarter method
     *
     * @author Khin Hnin Myo (20200218)
     * @return void
     *
     *
     */
    public function SearchBudgetResult()
    {
        $this->layout = 'phase_3_menu';
        # read the session
        $admin_level_id     = $this->Session->read('ADMIN_LEVEL_ID');
        $permission         = $this->Session->read('PERMISSION'); #permission
        $limit              = $permission['BudgetResultDifferenceReadLimit']; #read permission
        $id                 = $this->Session->read('LOGIN_ID'); #id
        $login_id           = $this->Session->read('LOGINID'); #login_id
        $target_month       = $this->Session->read('TARGETMONTH');
        $term_id            = $this->Session->read('TERM_ID');
        $term_name          = $this->Session->read('TERM_NAME');
        $top_layer_type     = $this->Session->read('TOP_LAYER_TYPE');
        $top_layer_name     = $this->Session->read('TOP_LAYER_NAME');
        $middle_layer_name  = $this->Session->read('MIDDLE_LAYER_NAME');
        $bottom_layer_name  = $this->Session->read('BOTTOM_LAYER_NAME');
        if ($this->request->is('post')) {
            $tab                = $this->request->data('tab');
            $parent_layer_code  = $this->request->data('hid_parent_layer');
            $select_id          = $this->request->data('multi_select_data');//layer_code
            $hq_id              = $this->request->data('hq_id');
            //$bk_dt_id           = $this->request->data('sh_dt_id'); #for back link, add by NuNuLwin (20200619)
            $sel                = $this->request->data('select_data'); # select data for searach fun:
            $hq_name            = $this->request->data('hq_name');
            
            if (!empty($select_id)) {
                $id_array = array_unique($select_id);
                $id_string = implode(',', $id_array);
            }
            //$get_calculate = $this->getCacheData($tab, $target_month, $term_id, $id_array, $id_string, $parent_id, $term_name,$top_layer_type);
            $get_calculate   = $this->getCacheData($tab, $target_month, $term_id, $id_array, $id_string, $parent_layer_code, $term_name,$top_layer_type);
            $budget_result = $get_calculate[0];
            $search_total  = $get_calculate[1];
            $start_month   = $get_calculate[2];
            $parent_name   = $get_calculate[3];
            $select_data   = $get_calculate[4];
            if (Cache::read('EXTRA_LOGIS') != '') {
                $extra_logistics = Cache::read('EXTRA_LOGIS');
            } else {
                $extra_logistics = Cache::read('NULL_LOGIS');
            }
            
            if (empty($budget_result)) {
                $this->set('errmsg', parent::getErrorMsg('SE001'));
            }
            //$select_data = $this->GetSelectData(explode(',', $sel), $tab,$parent_layer_code);
            $this->set(compact('parent_layer_code','top_layer_name','middle_layer_name','bottom_layer_name','admin_level_id', 'target_month', 'start_month', 'term_id', 'term_name', 'id_array', 'id_string', 'select_data', 'budget_result', 'search_total', 'tab', 'hq_id', 'hq_name','parent_name','extra_logistics'));
            
            $this->render('index');
        } else {
            $this->redirect(array(
                'controller' => 'BrmBudgetResultDifference',
                'action' => 'index'
            ));
        }
    }
    public function getPermissionDept($login_id='', $ba_code='')
    {
        if ($login_id=='') {
            $login_id 	= $this->Session->read('LOGIN_ID');
        }
        
        if ($ba_code == '') {
            $getBA = $this->User->find('list', array(
                    'fields' => 'layer_code',
                    'conditions' => array(
                        'id' => $login_id,
                        'flag' => 1
                    )
                ));
            # get ba code only
            $ba_code = $getBA[$login_id];
        }

        # get headquarter id by ba_code
        $getHq = $this->Layer->find('all', array(
            'fields' => array('head_dept_id','dept_id'),
            'conditions' => array(
                'ba_code' => $ba_code,
                'flag' => 1
            )
        ));
        return $getHq;
    }
    //public function getCacheData($tab, $target_month, $term_id, $id_array, $id_string, $id='null', $term_name,$top_layer_type)
    public function getCacheData($tab, $target_month, $term_id, $id_array, $id_string, $click_layer_code='null', $term_name, $top_layer_type)
    {   
        $top_layer_name     = $_SESSION['TOP_LAYER_NAME'];
        $middle_layer_name  = $_SESSION['MIDDLE_LAYER_NAME'];
        $bottom_layer_name  = $_SESSION['BOTTOM_LAYER_NAME'];
        $calculations = new CalculationController;
        $tgmonth = date('Ym', strtotime($target_month));

        //$cache_name = 'brd_'.$term_id.'_'.$tgmonth.'_'.$tab.'_'.$id;
        $cache_name = 'brd_'.$term_id.'_'.$tgmonth.'_'.$tab.'_'.$click_layer_code;
        if ($id_string!='0') {
            $search_id = trim(implode('_', $id_array));
            $cache_name = $cache_name.'_'.$search_id;
        }
        Cache::delete($cache_name);
        # Get data from cache
        // Cache::clear();
        $cache_data = Cache::read($cache_name);
        $now = date("Y-m-d h:i:sa");

        if (isset($cache_data['data']) && !empty($cache_data['data'])) {
            $last_save_time = $cache_data['last_save_time'];
            $check_update = $this->CheckUpdate($last_save_time);
        }
        if (empty($cache_data) || $check_update>0) {
            #Delete old cache data.
            Cache::delete($cache_name);

            //$get_calculate = $calculations->CalculateBRDAmt($tab, $target_month, $term_id, $id, $id_array, $id_string, $term_name,$top_layer_type,$top_layer_name,$middle_layer_name,$bottom_layer_name);
            $get_calculate = $calculations->CalculateBRDAmt($tab, $target_month, $term_id, $click_layer_code, $id_array, $id_string, $term_name,$top_layer_type,$top_layer_name,$middle_layer_name,$bottom_layer_name);
           
            #Prepare cache data
            $cache_data = array(
                'last_save_time' => $now,
                'data' => $get_calculate
            );
            #Save to cache if no data exist
            Cache::write($cache_name, $cache_data);
        }
        return $cache_data['data'];
    }
    public function CheckUpdate($last_save_time)
    {
        $check_actual = $this->BrmActualResultSummary->find('count', array(
                    'conditions' => array(
                        'updated_date >' => $last_save_time
                    )
                ));
        $check_budget = $this->BrmBudget->find('count', array(
                    'conditions' => array(
                        'updated_date >' => $last_save_time
                    )
                ));

        if ($check_actual>0 || $check_budget>0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function GetSelectData($sel, $tab,$parent_id=null)
    {   
        $top_layer_name     = $this->Session->read('TOP_LAYER_NAME');
        $top_layer_type     = Setting::LAYER_SETTING['topLayer'];
        $middle_layer_name  = $this->Session->read('MIDDLE_LAYER_NAME');
        $bottom_layer_type  = Setting::LAYER_SETTING['bottomLayer'];
        if ($tab == $top_layer_name){
            $fields = array('layer_code','name_jp');
            $conditions = array(
                'type_order' => $top_layer_type,
                'name_jp IN' => $sel,
                'flag' => 1
            );
            $order = " type_order ASC ";
        }else if($tab == $middle_layer_name){
           $fields = array('layer_code','name_jp');
            $conditions = array(
                'flag'=> 1,
                'OR' =>array(
                    'parent_id LIKE' => '%'.$parent_id.'%'
                )
                
            );
        }else{
            $fields = array('layer_code','name_jp');
            $conditions = array(
                'type_order' => $bottom_layer_type,
                'flag' => 1
            );
        }
        
        $data = $this->Layer->find('list', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'order' => array('type_order')
        ));
        
        return ($data);
    }

    /**
     * DownloadBudgetResult method
     *
     * @author Khin Hnin Myo (20200311)
     * @return void
     *
     *
     */
    public function DownloadBudgetResult()
    {
        $permission     = $this->Session->read('PERMISSIONS');
        //$limit          = $permission['BudgetResultDifferenceReadLimit'];
        $id             = $this->Session->read('LOGIN_ID'); #id
        $login_id       = $this->Session->read('LOGINID'); #login_id
        $target_month   = $this->Session->read('TARGETMONTH');
        $term_id        = $this->Session->read('TERM_ID');
        $term_name      = $this->Session->read('TERM_NAME');

        $tab                = $this->request->data('tab');
        $searched_head_id   = $this->request->data('txt_hid');
        $hq_id              = $this->request->data('hq_id');
        $hq_name            = $this->request->data('hq_name');
        $id_array           = explode(',', $searched_head_id);

        $click_layer_code   = $this->request->data('hid_parent_layer');
        
        $PHPExcel = $this->PhpExcel;
        $file_name = "BudgetResultDifference";
        $this->DownloadExcel($term_id, $term_name, $target_month, $hq_name, $click_layer_code, $file_name, $PHPExcel, $id_array, $tab, $searched_head_id);
        $this->render('index');
    }

    public function DownloadExcel($term_id, $term_name, $target_month, $hq_name, $click_layer_code, $file_name, $PHPExcel, $id_array, $tab, $searched_id='0', $save_to_tmp=false)
    {
        
        $tgmonth    = date('Ym', strtotime($target_month));
        $cache_name = 'brd_'.$term_id.'_'.$tgmonth.'_'.$tab.'_'.$click_layer_code;
        //$cache_name = 'brd_'.$term_id.'_'.$tgmonth.'_'.$tab.'_10001';
        if ($searched_id!='0') {
            $search_id = trim(implode('_', $id_array));
            $cache_name = $cache_name.'_'.$search_id;
        }
        $get_calculate = Cache::read($cache_name);
        $budget_result  = $get_calculate['data'][0];
        $search_total   = $get_calculate['data'][1];
        $start_month    = $get_calculate['data'][2];
        if (Cache::read('EXTRA_LOGIS') != '') {
            $extra_logistics = Cache::read('EXTRA_LOGIS');
        } else {
            $extra_logistics = Cache::read('NULL_LOGIS');
        }
        # get y/m from y-m
        $tm             = date("Y/m", strtotime($target_month));
        $tg_month       = date('n', strtotime($target_month));
        $last_year_tm   = date("Y/m", strtotime($target_month. "last day of - 1 year"));
        
        $start_month    = date("Y/m", strtotime($get_calculate['data'][2]));
        $last_year_sm   = date("Y/m", strtotime($get_calculate['data'][2]. "last day of - 1 year"));
        if (!$save_to_tmp) {
            $unit = $this->request->data('unit');
        } else {
            $unit = 1000000;
        }
        $unit = (!empty($unit))? $unit : 1000000;
        $objPHPExcel = $PHPExcel->createWorksheet()->setDefaultFont('Cambria', 12);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel ->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel ->getActiveSheet()->setShowGridlines(true);
        $objPHPExcel ->setActiveSheetIndex(0);
        
        $objPHPExcel->getActiveSheet()->setTitle(__('予算と結果の比較'));
        $sheet = $PHPExcel->getActiveSheet();
            
        $border_thin = array(
            'borders' => array(
                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
        ));
        $border_none = array(
            'borders' => array(
                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_NONE)
        ));
        $aligncenter = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $alignleft = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $alignright = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $title = array(
            'font'  => array(
                'bold'  => true
            )
        );

        $negative = array(
            'font'  => array(
                'color' => array(
                    'rgb' => 'FF0000'
            )

        ));
        $sheet->getColumnDimension('A')->setWidth(2);
        $objPHPExcel->getActiveSheet()->getStyle('B:X')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B2:X2')->applyFromArray($title);

        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(10);
        $sheet->getColumnDimension('K')->setWidth(10);
        $sheet->getColumnDimension('L')->setWidth(10);
        $sheet->getColumnDimension('M')->setWidth(2);

        $sheet->getColumnDimension('N')->setWidth(12);
        $sheet->getColumnDimension('O')->setWidth(10);
        $sheet->getColumnDimension('P')->setWidth(10);
        $sheet->getColumnDimension('Q')->setWidth(10);
        $sheet->getColumnDimension('R')->setWidth(10);
        $sheet->getColumnDimension('S')->setWidth(10);
        $sheet->getColumnDimension('T')->setWidth(10);
        $sheet->getColumnDimension('U')->setWidth(10);
        $sheet->getColumnDimension('V')->setWidth(10);
        $sheet->getColumnDimension('W')->setWidth(10);
        $sheet->getColumnDimension('X')->setWidth(10);

        $sheet->setCellValue('B2', __('ケミカル').' '.date("Y", strtotime($target_month)).__('Year').' '.date("m", strtotime($target_month)).__('月').' '.__('度実績・対予算比較表'));
        $sheet->mergeCells('B2:W2');
        $sheet->getStyle('B2:W2')->applyFromArray($aligncenter);

        $lastCol = ($tab == 'Logistic') ? 'J' : 'L';
        if (!empty($search_total)) {
            $sheet->getStyle('B4:'.$lastCol.'4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle('B5:'.$lastCol.'5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle('B6:'.$lastCol.'6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            # FIRST HEADER
            $sheet->setCellValue('B4', "");
            $sheet->getStyle('B4')->applyFromArray($border_thin);

            $sheet->setCellValue('B5', "");
            $sheet->getStyle('B5')->applyFromArray($border_thin);
            $sheet->mergeCells('B4:B5');
            
            $header = ($hq_name == '全社')? __($hq_name) : $hq_name;

            $sheet->setCellValue('C4', $header);
            $sheet->mergeCells('C4:'.$lastCol.'4');
            $sheet->getStyle('C4:'.$lastCol.'4')->applyFromArray($aligncenter);
            $sheet->getStyle('C4:'.$lastCol.'4')->applyFromArray($border_thin);

            # SECOND HEADER
            $sheet->setCellValue('C5', __("予算"));
            $sheet->getStyle('C5')->applyFromArray($border_thin);
            $sheet->getStyle('C5')->applyFromArray($aligncenter);

            $sheet->setCellValue('D5', __("月次予算"));
            $sheet->getStyle('D5')->applyFromArray($border_thin);
            $sheet->getStyle('D5')->applyFromArray($aligncenter);

            $sheet->setCellValue('E5', __("実績"));
            $sheet->getStyle('E5')->applyFromArray($border_thin);
            $sheet->getStyle('E5')->applyFromArray($aligncenter);

            $sheet->setCellValue('F5', __("対月次予算増減"));
            $sheet->getStyle('F5')->applyFromArray($border_thin);
            $sheet->getStyle('F5')->applyFromArray($aligncenter);

            $sheet->setCellValue('G5', __("累計予算"));
            $sheet->getStyle('G5')->applyFromArray($border_thin);
            $sheet->getStyle('G5')->applyFromArray($aligncenter);

            $sheet->setCellValue('H5', __("累計実績"));
            $sheet->getStyle('H5')->applyFromArray($border_thin);
            $sheet->getStyle('H5')->applyFromArray($aligncenter);

            $sheet->setCellValue('I5', __("対累計予算増減"));
            $sheet->getStyle('I5')->applyFromArray($border_thin);
            $sheet->getStyle('I5')->applyFromArray($aligncenter);

            $sheet->setCellValue('J5', __("達成率（%）対年間"));
            $sheet->getStyle('J5')->applyFromArray($border_thin);
            $sheet->getStyle('J5')->applyFromArray($aligncenter);

            $sheet->setCellValue('K5', __("前年同期実績"));
            $sheet->getStyle('K5')->applyFromArray($border_thin);
            $sheet->getStyle('K5')->applyFromArray($aligncenter);

            $sheet->setCellValue('L5', __("対前年同期増減"));
            $sheet->getStyle('L5')->applyFromArray($border_thin);
            $sheet->getStyle('L5')->applyFromArray($aligncenter);

            # THIRD HEADER
            $sheet->setCellValue('B6', __("勘定科目"));
            $sheet->getStyle('B6')->applyFromArray($aligncenter);
            $sheet->getStyle('B6')->applyFromArray($border_thin);

            $sheet->setCellValue('C6', __("年間"));
            $sheet->getStyle('C6')->applyFromArray($aligncenter);
            $sheet->getStyle('C6')->applyFromArray($border_thin);

            $sheet->setCellValue('D6', $tm);
            $sheet->getStyle('D6')->applyFromArray($aligncenter);
            $sheet->getStyle('D6')->applyFromArray($border_thin);

            $sheet->setCellValue('E6', $tm);
            $sheet->getStyle('E6')->applyFromArray($aligncenter);
            $sheet->getStyle('E6')->applyFromArray($border_thin);

            $sheet->setCellValue('F6', "");
            $sheet->getStyle('F6')->applyFromArray($aligncenter);
            $sheet->getStyle('F6')->applyFromArray($border_thin);

            $sheet->setCellValue('G6', $start_month.' ~ '.$tm);
            $sheet->getStyle('G6')->applyFromArray($aligncenter);
            $sheet->getStyle('G6')->applyFromArray($border_thin);

            $sheet->setCellValue('H6', $start_month.' ~ '.$tm);
            $sheet->getStyle('H6')->applyFromArray($aligncenter);
            $sheet->getStyle('H6')->applyFromArray($border_thin);

            $sheet->setCellValue('I6', "");
            $sheet->getStyle('I6')->applyFromArray($aligncenter);
            $sheet->getStyle('I6')->applyFromArray($border_thin);

            $sheet->setCellValue('J6', "");
            $sheet->getStyle('J6')->applyFromArray($aligncenter);
            $sheet->getStyle('J6')->applyFromArray($border_thin);

            $sheet->setCellValue('K6', $last_year_sm.' ~ '.$last_year_tm);
            $sheet->getStyle('K6')->applyFromArray($aligncenter);
            $sheet->getStyle('K6')->applyFromArray($border_thin);

            $sheet->setCellValue('L6', "");
            $sheet->getStyle('L6')->applyFromArray($aligncenter);
            $sheet->getStyle('L6')->applyFromArray($border_thin);
            
            # SIDE HEADER
            $st = 6;
            $total = 0;
            foreach ($search_total as $hqvalue) {
                $total++;
                $st++;
                //$sheet->setCellValue('B'.$st, $hqvalue['sub_acc_name_jp']);//name_jp
                $sheet->setCellValue('B'.$st, $hqvalue['name_jp']);//sub_acc_name_jp
                $sheet->getStyle('B'.$st)->applyFromArray($border_thin);
                $sheet->getStyle('B'.$st)->applyFromArray($alignleft);
                
                $budget = ($hqvalue['budget']/$unit);
                $sheet->setCellValue('C'.$st, $budget);
                $sheet->getStyle('C'.$st)->applyFromArray($border_thin);
                $sheet->getStyle('C'.$st)->applyFromArray($alignright);
                
                $monthly_budget = ($hqvalue['monthly_budget']/$unit);
                $sheet->setCellValue('D'.$st, $monthly_budget);
                $sheet->getStyle('D'.$st)->applyFromArray($border_thin);
                $sheet->getStyle('D'.$st)->applyFromArray($alignright);
                
                $result = ($hqvalue['monthly_result']/$unit);
                $sheet->setCellValue('E'.$st, $result);
                $sheet->getStyle('E'.$st)->applyFromArray($border_thin);
                $sheet->getStyle('E'.$st)->applyFromArray($alignright);
                
                $monthly_budget_change = ($hqvalue['monthly_budget_change']/$unit);
                $sheet->setCellValue('F'.$st, $monthly_budget_change);
                $sheet->getStyle('F'.$st)->applyFromArray($border_thin);
                $sheet->getStyle('F'.$st)->applyFromArray($alignright);
                
                $total_budget = ($hqvalue['total_budget']/$unit);
                $sheet->setCellValue('G'.$st, $total_budget);
                $sheet->getStyle('G'.$st)->applyFromArray($border_thin);
                $sheet->getStyle('G'.$st)->applyFromArray($alignright);
                
                $total_result = ($hqvalue['total_result']/$unit);
                $sheet->setCellValue('H'.$st, $total_result);
                $sheet->getStyle('H'.$st)->applyFromArray($border_thin);
                $sheet->getStyle('H'.$st)->applyFromArray($alignright);
                
                $total_result_change = ($hqvalue['total_budget_change']/$unit);
                $sheet->setCellValue('I'.$st, $total_result_change);
                $sheet->getStyle('I'.$st)->applyFromArray($border_thin);
                $sheet->getStyle('I'.$st)->applyFromArray($alignright);
                
                $rate = $hqvalue['achievement_by_year'];
                $sheet->setCellValue('J'.$st, $rate);
                $sheet->getStyle('J'.$st)->applyFromArray($border_thin);
                $sheet->getStyle('J'.$st)->applyFromArray($alignright);
                
                $result_last = ($hqvalue['yoy_result']/$unit);
                $sheet->setCellValue('K'.$st, $result_last);
                $sheet->getStyle('K'.$st)->applyFromArray($border_thin);
                $sheet->getStyle('K'.$st)->applyFromArray($alignright);
                
                $yoy_change = ($hqvalue['yoy_change']/$unit);
                $sheet->setCellValue('L'.$st, $yoy_change);
                $sheet->getStyle('L'.$st)->applyFromArray($border_thin);
                $sheet->getStyle('L'.$st)->applyFromArray($alignright);
            }
            $start = 4;
            $total = $total+3;
            $br = 1;
            $position = 0;
            foreach ($budget_result as $key => $value) {
                if (in_array($key, $extra_logistics)) {
                    $bg_color = 'FCF2EF';
                } else {
                    $bg_color = 'daeef3';
                }
                $position++;
                $row = 0;
                if ($row<$total) {
                    if (($position % 2) != 0) {
                        $endCol = ($tab == 'Logistic') ? 'V' : 'X';
                        $a = $start;
                        $one = $a+1;
                        # FIRST HEADER
                        $sheet->getStyle('N'.$a.':'.$endCol.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        
                        $sheet->setCellValue('N'.$a, "");
                        $sheet->getStyle('N'.$a)->applyFromArray($border_thin);

                        $sheet->setCellValue('N'.$one, "");
                        $sheet->getStyle('N'.$one)->applyFromArray($border_thin);
                        $sheet->mergeCells('N'.$a.':N'.$one);

                        $sheet->setCellValue('O'.$a, $key);
                        $sheet->mergeCells('O'.$a.':'.$endCol.$a);
                        $sheet->getStyle('O'.$a.':'.$endCol.$a)->applyFromArray($aligncenter);
                        $sheet->getStyle('O'.$a.':'.$endCol.$a)->applyFromArray($border_thin);

                        $row++;
                        $b = $start+$row;
                        # SECOND HEADER
                        $sheet->getStyle('N'.$b.':X'.$b)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        
                        $sheet->setCellValue('O'.$b, __("予算"));
                        $sheet->getStyle('O'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('O'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('P'.$b, __("月次予算"));
                        $sheet->getStyle('P'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('P'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('Q'.$b, __("実績"));
                        $sheet->getStyle('Q'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('Q'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('R'.$b, __("対月次予算増減"));
                        $sheet->getStyle('R'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('R'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('S'.$b, __("累計予算"));
                        $sheet->getStyle('S'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('S'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('T'.$b, __("累計実績"));
                        $sheet->getStyle('T'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('T'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('U'.$b, __("対累計予算増減"));
                        $sheet->getStyle('U'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('U'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('V'.$b, __("達成率（%）対年間"));
                        $sheet->getStyle('V'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('V'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('W'.$b, __("前年同期実績"));
                        $sheet->getStyle('W'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('W'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('X'.$b, __("対前年同期増減"));
                        $sheet->getStyle('X'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('X'.$b)->applyFromArray($aligncenter);

                        $row++;
                        $c = $start+$row;
                        # THIRD HEADER
                        $sheet->getStyle('N'.$c.':X'.$c)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        $sheet->setCellValue('N'.$c, __("勘定科目"));
                        $sheet->getStyle('N'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('N'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('O'.$c, __("年間"));
                        $sheet->getStyle('O'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('O'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('P'.$c, $tm);
                        $sheet->getStyle('P'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('P'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('Q'.$c, $tm);
                        $sheet->getStyle('Q'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('Q'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('R'.$c, "");
                        $sheet->getStyle('R'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('R'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('S'.$c, $start_month.' ~ '.$tm);
                        $sheet->getStyle('S'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('S'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('T'.$c, $start_month.' ~ '.$tm);
                        $sheet->getStyle('T'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('T'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('U'.$c, "");
                        $sheet->getStyle('U'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('U'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('V'.$c, "");
                        $sheet->getStyle('V'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('V'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('W'.$c, $last_year_sm.' ~ '.$last_year_tm);
                        $sheet->getStyle('W'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('W'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('X'.$c, "");
                        $sheet->getStyle('X'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('X'.$c)->applyFromArray($border_thin);

                        
                        foreach ($value as $vkey => $vvalue) {
                            $row++;
                            $d = $start+$row;
                            //$sheet->setCellValue('N'.$d, $vvalue['sub_acc_name_jp']);
                            $sheet->setCellValue('N'.$d, $vvalue['name_jp']);
                            $sheet->getStyle('N'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('N'.$d)->applyFromArray($alignleft);

                            $budget = ($vvalue['budget']/$unit);
                            
                            $sheet->setCellValue('O'.$d, $budget);
                            $sheet->getStyle('O'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('O'.$d)->applyFromArray($alignright);
                            
                            $monthly_budget = ($vvalue['monthly_budget']/$unit);
                            
                            $sheet->setCellValue('P'.$d, $monthly_budget);
                            $sheet->getStyle('P'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('P'.$d)->applyFromArray($alignright);
                            
                            $result = ($vvalue['monthly_result']/$unit);
                            
                            $sheet->setCellValue('Q'.$d, $result);
                            $sheet->getStyle('Q'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('Q'.$d)->applyFromArray($alignright);
                            
                            $monthly_budget_change = ($vvalue['monthly_budget_change']/$unit);
                            
                            $sheet->setCellValue('R'.$d, $monthly_budget_change);
                            $sheet->getStyle('R'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('R'.$d)->applyFromArray($alignright);
                            
                            $total_budget = ($vvalue['total_budget']/$unit);
                            
                            $sheet->setCellValue('S'.$d, $total_budget);
                            $sheet->getStyle('S'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('S'.$d)->applyFromArray($alignright);
                            
                            $total_result = ($vvalue['total_result']/$unit);
                            
                            $sheet->setCellValue('T'.$d, $total_result);
                            $sheet->getStyle('T'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('T'.$d)->applyFromArray($alignright);
                            
                            $total_budget_change = ($vvalue['total_budget_change']/$unit);
                            
                            $sheet->setCellValue('U'.$d, $total_budget_change);
                            $sheet->getStyle('U'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('U'.$d)->applyFromArray($alignright);
                            
                            $achievement_by_year = $vvalue['achievement_by_year'];
                            
                            $sheet->setCellValue('V'.$d, $achievement_by_year);
                            $sheet->getStyle('V'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('V'.$d)->applyFromArray($alignright);
                            
                            $yoy_result = ($vvalue['yoy_result']/$unit);
                            
                            $sheet->setCellValue('W'.$d, $yoy_result);
                            $sheet->getStyle('W'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('W'.$d)->applyFromArray($alignright);
                            
                            $yoy_change = ($vvalue['yoy_change']/$unit);
                            
                            $sheet->setCellValue('X'.$d, $yoy_change);
                            $sheet->getStyle('X'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('X'.$d)->applyFromArray($alignright);
                        }
                    } elseif (($position % 2) == 0) {
                        $start = $start+$total+$br;
                        $a = $start;
                        $one = $a+1;
                        # FIRST HEADER
                        $sheet->getStyle('B'.$a.':'.$lastCol.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        $sheet->setCellValue('B'.$a, "");
                        $sheet->getStyle('B'.$a)->applyFromArray($border_thin);

                        $sheet->setCellValue('B'.$one, "");
                        $sheet->getStyle('B'.$one)->applyFromArray($border_thin);
                        $sheet->mergeCells('B'.$a.':B'.$one);

                        $sheet->setCellValue('C'.$a, $key);
                        $sheet->mergeCells('C'.$a.':'.$lastCol.$a);
                        $sheet->getStyle('C'.$a.':'.$lastCol.$a)->applyFromArray($aligncenter);
                        $sheet->getStyle('C'.$a.':'.$lastCol.$a)->applyFromArray($border_thin);

                        $row++;
                        $b = $start+$row;
                        # SECOND HEADER
                        $sheet->getStyle('B'.$b.':L'.$b)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        $sheet->setCellValue('C'.$b, __("予算"));
                        $sheet->getStyle('C'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('C'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('D'.$b, __("月次予算"));
                        $sheet->getStyle('D'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('D'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('E'.$b, __("実績"));
                        $sheet->getStyle('E'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('E'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('F'.$b, __("対月次予算増減"));
                        $sheet->getStyle('F'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('F'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('G'.$b, __("累計予算"));
                        $sheet->getStyle('G'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('G'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('H'.$b, __("累計実績"));
                        $sheet->getStyle('H'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('H'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('I'.$b, __("対累計予算増減"));
                        $sheet->getStyle('I'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('I'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('J'.$b, __("達成率（%）対年間"));
                        $sheet->getStyle('J'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('J'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('K'.$b, __("前年同期実績"));
                        $sheet->getStyle('K'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('K'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('L'.$b, __("対前年同期増減"));
                        $sheet->getStyle('L'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('L'.$b)->applyFromArray($aligncenter);

                        $row++;
                        $c = $start+$row;
                        # THIRD HEADER
                        $sheet->getStyle('B'.$c.':L'.$c)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        $sheet->setCellValue('B'.$c, __("勘定科目"));
                        $sheet->getStyle('B'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('B'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('C'.$c, __("年間"));
                        $sheet->getStyle('C'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('C'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('D'.$c, $tm);
                        $sheet->getStyle('D'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('D'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('E'.$c, $tm);
                        $sheet->getStyle('E'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('E'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('F'.$c, "");
                        $sheet->getStyle('F'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('F'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('G'.$c, $start_month.' ~ '.$tm);
                        $sheet->getStyle('G'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('G'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('H'.$c, $start_month.' ~ '.$tm);
                        $sheet->getStyle('H'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('H'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('I'.$c, "");
                        $sheet->getStyle('I'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('I'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('J'.$c, "");
                        $sheet->getStyle('J'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('J'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('K'.$c, $last_year_sm.' ~ '.$last_year_tm);
                        $sheet->getStyle('K'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('K'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('L'.$c, "");
                        $sheet->getStyle('L'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('L'.$c)->applyFromArray($border_thin);

                        foreach ($value as $vkey => $vvalue) {
                            $row++;
                            $d = $start+$row;
                            $sheet->setCellValue('B'.$d, $vvalue['sub_acc_name_jp']);
                            $sheet->getStyle('B'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('B'.$d)->applyFromArray($alignleft);

                            $budget = ($vvalue['budget']/$unit);
                            
                            $sheet->setCellValue('C'.$d, $budget);
                            $sheet->getStyle('C'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('C'.$d)->applyFromArray($alignright);
                            
                            $monthly_budget = ($vvalue['monthly_budget']/$unit);
                            
                            $sheet->setCellValue('D'.$d, $monthly_budget);
                            $sheet->getStyle('D'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('D'.$d)->applyFromArray($alignright);
                            
                            $monthly_result = ($vvalue['monthly_result']/$unit);
                            
                            $sheet->setCellValue('E'.$d, $monthly_result);
                            $sheet->getStyle('E'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('E'.$d)->applyFromArray($alignright);
                            
                            $monthly_budget_change = ($vvalue['monthly_budget_change']/$unit);
                            
                            $sheet->setCellValue('F'.$d, $monthly_budget_change);
                            $sheet->getStyle('F'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('F'.$d)->applyFromArray($alignright);
                            
                            $total_budget = ($vvalue['total_budget']/$unit);
                            
                            $sheet->setCellValue('G'.$d, $total_budget);
                            $sheet->getStyle('G'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('G'.$d)->applyFromArray($alignright);
                            
                            $total_result = ($vvalue['total_result']/$unit);
                            
                            $sheet->setCellValue('H'.$d, $total_result);
                            $sheet->getStyle('H'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('H'.$d)->applyFromArray($alignright);
                            
                            $total_budget_change = ($vvalue['total_budget_change']/$unit);
                            
                            $sheet->setCellValue('I'.$d, $total_budget_change);
                            $sheet->getStyle('I'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('I'.$d)->applyFromArray($alignright);
                            
                            $achievement_by_year = $vvalue['achievement_by_year'];
                            
                            $sheet->setCellValue('J'.$d, $achievement_by_year);
                            $sheet->getStyle('J'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('J'.$d)->applyFromArray($alignright);
                            
                            $yoy_result = ($vvalue['yoy_result']/$unit);
                            
                            $sheet->setCellValue('K'.$d, $yoy_result);
                            $sheet->getStyle('K'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('K'.$d)->applyFromArray($alignright);
                            
                            $yoy_change = ($vvalue['yoy_change']/$unit);
                            
                            $sheet->setCellValue('L'.$d, $yoy_change);
                            $sheet->getStyle('L'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('L'.$d)->applyFromArray($alignright);
                        }
                    }
                }
            }
        } else {
            $start = 4;
            $total = $total+3  ;
            $br = 1;
            $position = 0;
            
            foreach ($budget_result as $key => $value) {
                if (in_array($key, $extra_logistics)) {
                    $bg_color = 'FCF2EF';
                } else {
                    $bg_color = 'daeef3';
                }
                $position++;
                $row = 0;
                if ($row<$total) {
                    if (($position % 2) == 0) {
                        $start = $start+$total+$br;
                        $a = $start;
                        $one = $a+1;
                        
                        # FIRST HEADER
                        $sheet->getStyle('N'.$a.':X'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        
                        $sheet->setCellValue('N'.$a, "");
                        $sheet->getStyle('N'.$a)->applyFromArray($border_thin);

                        $sheet->setCellValue('N'.$one, "");
                        $sheet->getStyle('N'.$one)->applyFromArray($border_thin);
                        $sheet->mergeCells('N'.$a.':N'.$one);
                        
                        $sheet->setCellValue('O'.$a, $key); #header
                        $sheet->mergeCells('O'.$a.':X'.$a);
                        $sheet->getStyle('O'.$a.':X'.$a)->applyFromArray($aligncenter);
                        $sheet->getStyle('O'.$a.':X'.$a)->applyFromArray($border_thin);

                        $row++;
                        $b = $start+$row;
                        # SECOND HEADER
                        $sheet->getStyle('N'.$b.':X'.$b)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        
                        $sheet->setCellValue('O'.$b, __("予算"));
                        $sheet->getStyle('O'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('O'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('P'.$b, __("月次予算"));
                        $sheet->getStyle('P'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('P'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('Q'.$b, __("実績"));
                        $sheet->getStyle('Q'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('Q'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('R'.$b, __("対月次予算増減"));
                        $sheet->getStyle('R'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('R'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('S'.$b, __("累計予算"));
                        $sheet->getStyle('S'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('S'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('T'.$b, __("累計実績"));
                        $sheet->getStyle('T'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('T'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('U'.$b, __("対累計予算増減"));
                        $sheet->getStyle('U'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('U'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('V'.$b, __("達成率（%）対年間"));
                        $sheet->getStyle('V'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('V'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('W'.$b, __("前年同期実績"));
                        $sheet->getStyle('W'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('W'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('X'.$b, __("対前年同期増減"));
                        $sheet->getStyle('X'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('X'.$b)->applyFromArray($aligncenter);

                        $row++;
                        $c = $start+$row;
                        # THIRD HEADER
                        $sheet->getStyle('N'.$c.':X'.$c)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        $sheet->setCellValue('N'.$c, __("勘定科目"));
                        $sheet->getStyle('N'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('N'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('O'.$c, __("年間"));
                        $sheet->getStyle('O'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('O'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('P'.$c, $tm);
                        $sheet->getStyle('P'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('P'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('Q'.$c, $tm);
                        $sheet->getStyle('Q'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('Q'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('R'.$c, "");
                        $sheet->getStyle('R'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('R'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('S'.$c, $start_month.' ~ '.$tm);
                        $sheet->getStyle('S'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('S'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('T'.$c, $start_month.' ~ '.$tm);
                        $sheet->getStyle('T'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('T'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('U'.$c, "");
                        $sheet->getStyle('U'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('U'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('V'.$c, "");
                        $sheet->getStyle('V'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('V'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('W'.$c, $last_year_sm.' ~ '.$last_year_tm);
                        $sheet->getStyle('W'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('W'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('X'.$c, "");
                        $sheet->getStyle('X'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('X'.$c)->applyFromArray($border_thin);

                        
                        foreach ($value as $vvalue) {
                            $row++;
                            $d = $start+$row;
                            $sheet->setCellValue('N'.$d, $vvalue['sub_acc_name_jp']);
                            $sheet->getStyle('N'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('N'.$d)->applyFromArray($alignleft);

                            $budget = ($vvalue['budget']/$unit);
                            
                            $sheet->setCellValue('O'.$d, $budget);
                            $sheet->getStyle('O'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('O'.$d)->applyFromArray($alignright);
                            
                            $monthly_budget = ($vvalue['monthly_budget']/$unit);
                            
                            $sheet->setCellValue('P'.$d, $monthly_budget);
                            $sheet->getStyle('P'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('P'.$d)->applyFromArray($alignright);
                            
                            $result = ($vvalue['monthly_result']/$unit);
                            
                            $sheet->setCellValue('Q'.$d, $result);
                            $sheet->getStyle('Q'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('Q'.$d)->applyFromArray($alignright);
                            
                            $monthly_budget_change = ($vvalue['monthly_budget_change']/$unit);
                            
                            $sheet->setCellValue('R'.$d, $monthly_budget_change);
                            $sheet->getStyle('R'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('R'.$d)->applyFromArray($alignright);
                            
                            $total_budget = ($vvalue['total_budget']/$unit);
                            
                            $sheet->setCellValue('S'.$d, $total_budget);
                            $sheet->getStyle('S'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('S'.$d)->applyFromArray($alignright);
                            
                            $total_result = ($vvalue['total_result']/$unit);
                            
                            $sheet->setCellValue('T'.$d, $total_result);
                            $sheet->getStyle('T'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('T'.$d)->applyFromArray($alignright);
                            
                            $total_budget_change = ($vvalue['total_budget_change']/$unit);
                            
                            $sheet->setCellValue('U'.$d, $total_budget_change);
                            $sheet->getStyle('U'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('U'.$d)->applyFromArray($alignright);
                            
                            $achievement_by_year = $vvalue['achievement_by_year'];
                            
                            $sheet->setCellValue('V'.$d, $achievement_by_year);
                            $sheet->getStyle('V'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('V'.$d)->applyFromArray($alignright);
                            
                            $yoy_result = ($vvalue['yoy_result']/$unit);
                            
                            $sheet->setCellValue('W'.$d, $yoy_result);
                            $sheet->getStyle('W'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('W'.$d)->applyFromArray($alignright);
                            
                            $yoy_change = ($vvalue['yoy_change']/$unit);
                            
                            $sheet->setCellValue('X'.$d, $yoy_change);
                            $sheet->getStyle('X'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('X'.$d)->applyFromArray($alignright);
                        }
                    } elseif (($position % 2) != 0) {
                        $a = $start;
                        $one = $a+1;
                        # FIRST HEADER
                        $sheet->getStyle('B'.$a.':L'.$a)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        $sheet->setCellValue('B'.$a, "");
                        $sheet->getStyle('B'.$a)->applyFromArray($border_thin);

                        $sheet->setCellValue('B'.$one, "");
                        $sheet->getStyle('B'.$one)->applyFromArray($border_thin);
                        $sheet->mergeCells('B'.$a.':B'.$one);

                        $sheet->setCellValue('C'.$a, $key);
                        $sheet->mergeCells('C'.$a.':L'.$a);
                        $sheet->getStyle('C'.$a.':L'.$a)->applyFromArray($aligncenter);
                        $sheet->getStyle('C'.$a.':L'.$a)->applyFromArray($border_thin);

                        $row++;
                        $b = $start+$row;
                        # SECOND HEADER
                        $sheet->getStyle('B'.$b.':L'.$b)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        $sheet->setCellValue('C'.$b, __("予算"));
                        $sheet->getStyle('C'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('C'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('D'.$b, __("月次予算"));
                        $sheet->getStyle('D'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('D'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('E'.$b, __("実績"));
                        $sheet->getStyle('E'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('E'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('F'.$b, __("対月次予算増減"));
                        $sheet->getStyle('F'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('F'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('G'.$b, __("累計予算"));
                        $sheet->getStyle('G'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('G'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('H'.$b, __("累計実績"));
                        $sheet->getStyle('H'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('H'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('I'.$b, __("対累計予算増減"));
                        $sheet->getStyle('I'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('I'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('J'.$b, __("達成率（%）対年間"));
                        $sheet->getStyle('J'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('J'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('K'.$b, __("前年同期実績"));
                        $sheet->getStyle('K'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('K'.$b)->applyFromArray($aligncenter);

                        $sheet->setCellValue('L'.$b, __("対前年同期増減"));
                        $sheet->getStyle('L'.$b)->applyFromArray($border_thin);
                        $sheet->getStyle('L'.$b)->applyFromArray($aligncenter);

                        $row++;
                        $c = $start+$row;
                        # THIRD HEADER
                        $sheet->getStyle('B'.$c.':L'.$c)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bg_color);
                        $sheet->setCellValue('B'.$c, __("勘定科目"));
                        $sheet->getStyle('B'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('B'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('C'.$c, __("年間"));
                        $sheet->getStyle('C'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('C'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('D'.$c, $tm);
                        $sheet->getStyle('D'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('D'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('E'.$c, $tm);
                        $sheet->getStyle('E'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('E'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('F'.$c, "");
                        $sheet->getStyle('F'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('F'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('G'.$c, $start_month.' ~ '.$tm);
                        $sheet->getStyle('G'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('G'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('H'.$c, $start_month.' ~ '.$tm);
                        $sheet->getStyle('H'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('H'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('I'.$c, "");
                        $sheet->getStyle('I'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('I'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('J'.$c, "");
                        $sheet->getStyle('J'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('J'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('K'.$c, $last_year_sm.' ~ '.$last_year_tm);
                        $sheet->getStyle('K'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('K'.$c)->applyFromArray($border_thin);

                        $sheet->setCellValue('L'.$c, "");
                        $sheet->getStyle('L'.$c)->applyFromArray($aligncenter);
                        $sheet->getStyle('L'.$c)->applyFromArray($border_thin);
                        
                        foreach ($value as $vvalue) {
                            $row++;
                            $d = $start+$row;
                            $sheet->setCellValue('B'.$d, $vvalue['sub_acc_name_jp']);
                            $sheet->getStyle('B'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('B'.$d)->applyFromArray($alignleft);

                            $budget = ($vvalue['budget']/$unit);
                            
                            $sheet->setCellValue('C'.$d, $budget);
                            $sheet->getStyle('C'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('C'.$d)->applyFromArray($alignright);
                            
                            $monthly_budget = ($vvalue['monthly_budget']/$unit);
                            
                            $sheet->setCellValue('D'.$d, $monthly_budget);
                            $sheet->getStyle('D'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('D'.$d)->applyFromArray($alignright);
                            
                            $monthly_result = ($vvalue['monthly_result']/$unit);
                            
                            $sheet->setCellValue('E'.$d, $monthly_result);
                            $sheet->getStyle('E'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('E'.$d)->applyFromArray($alignright);
                            
                            $monthly_budget_change = ($vvalue['monthly_budget_change']/$unit);
                            
                            $sheet->setCellValue('F'.$d, $monthly_budget_change);
                            $sheet->getStyle('F'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('F'.$d)->applyFromArray($alignright);
                            
                            $total_budget = ($vvalue['total_budget']/$unit);
                            
                            $sheet->setCellValue('G'.$d, $total_budget);
                            $sheet->getStyle('G'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('G'.$d)->applyFromArray($alignright);
                            
                            $total_result = ($vvalue['total_result']/$unit);
                            
                            $sheet->setCellValue('H'.$d, $total_result);
                            $sheet->getStyle('H'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('H'.$d)->applyFromArray($alignright);
                            
                            $total_budget_change = ($vvalue['total_budget_change']/$unit);
                            
                            $sheet->setCellValue('I'.$d, $total_budget_change);
                            $sheet->getStyle('I'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('I'.$d)->applyFromArray($alignright);
                            
                            $achievement_by_year = $vvalue['achievement_by_year'];
                            
                            $sheet->setCellValue('J'.$d, $achievement_by_year);
                            $sheet->getStyle('J'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('J'.$d)->applyFromArray($alignright);
                            
                            $yoy_result = ($vvalue['yoy_result']/$unit);
                            
                            $sheet->setCellValue('K'.$d, $yoy_result);
                            $sheet->getStyle('K'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('K'.$d)->applyFromArray($alignright);
                            
                            $yoy_change = ($vvalue['yoy_change']/$unit);
                            
                            $sheet->setCellValue('L'.$d, $yoy_change);
                            $sheet->getStyle('L'.$d)->applyFromArray($border_thin);
                            $sheet->getStyle('L'.$d)->applyFromArray($alignright);
                        }
                    }
                }
            }
        }
        #red color and diamond sign for negative number
        $col_alphabet = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X'];
        
        for ($i=2; $i < count($col_alphabet); $i++) {
            $column = $col_alphabet[$i];
            $lastRow = $sheet->getHighestRow();
            for ($row = 1; $row <= $lastRow; $row++) {
                $cell = (string)$sheet->getCell($column.$row)->getValue();
                if (substr($cell, -1) == '%') {
                    $cells = substr_replace($cell, '', -1);
                    $sheet->setCellValue($column.$row, $cells);
                    $objPHPExcel->getActiveSheet()
                            ->getStyle($column.$row)
                            ->getNumberFormat()
                            ->setFormatCode(
                                '"" 0\%;[Red]"▲" 0\%'
                            );
                } else {
                    $objPHPExcel->getActiveSheet()
                            ->getStyle($column.$row)
                            ->getNumberFormat()
                            ->setFormatCode(
                                '"" #,##0;[Red]"▲" #,##0'
                            );
                }
            }
        }
        if ($tab == 'Logistic') {
            #remove last year data columns
            $objPHPExcel->getActiveSheet()->removeColumn('X');
            $objPHPExcel->getActiveSheet()->removeColumn('W');
            $objPHPExcel->getActiveSheet()->removeColumn('L');
            $objPHPExcel->getActiveSheet()->removeColumn('K');
            $sheet->setCellValue('U'.$sheet->getHighestRow(), "");
            $sheet->getStyle('U'.$sheet->getHighestRow())->applyFromArray($border_none);
        }
        if ($save_to_tmp) {
            $PHPExcel->save($file_name);
        } else {
            $PHPExcel->output($file_name.'.xlsx');
        }

        $this->autoLayout = false;
    }
    
    
}
