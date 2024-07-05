<?php
/**
 *	SapProgressReportsController
 *	@author Thura Moe
 *
 **/

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');

class StockProgressReportsController extends AppController
{
    public $uses = array('User', 'Stock', 'LayerType');
    public $components = array('Session','Flash');

    public function beforeFilter()
    {
        parent::checkStockUrlSession('progress');
    }
    
    public function index()
    {
        $this->layout = 'stocks';
        $result = [];
        $period = $this->Session->read('StockSelections_PERIOD_DATE');
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        
        $Common = New CommonController();

        $flag_list  = Setting::ADDCMT_FLAG;
        $stock_data = $this->Stock->getBACodeFlag($period);
        
        $complete_ba = $this->Stock->getCompleteBACode($period);

        $count = count($stock_data);
        if (empty($stock_data)) {
            $no_data = parent::getErrorMsg("SE001");
        } else {
            $no_data = "";
            $result = $this->__prepareApprovedData($stock_data, $complete_ba);
        }
       
        $total_rows = parent::getSuccessMsg('SS004', $count);
        $this->set(compact('period', 'result', 'no_data', 'total_rows'));
    }

    public function __prepareApprovedData($stock_data, $complete_ba)
    {
        # prepare data
        # F -> "Finish", NF -> "Not Finish"
        $Common = new CommonController();
        $prepare = [];
        $complete_ba_arr = [];
        $count = count($stock_data);
        $count_complete = count($complete_ba);
        if ($count_complete > 0) {
            for ($j=0; $j<$count_complete; $j++) {
                $complete_ba_arr[] = $complete_ba[$j]['stocks']['layer_code'];
            }
        }
        
        for ($i=0; $i<$count; $i++) {
            $done = '';
            $layer_code = $stock_data[$i]['tmp']['layer_code'];
            $max_flag = $stock_data[$i]['tmp']['max_flag'];
            $min_flag = $stock_data[$i]['tmp']['min_flag'];
            $name_jp = $stock_data[$i]['tmp']['name_jp'];

            $parent_id = $stock_data[$i]['tmp']['parent_id'];
            $busi_approve_date = $stock_data[$i]['busi_approve']['approve_date'];
            $acc_approve_date = $stock_data[$i]['acc_approve']['approve_date'];

            $parent_data = $Common->parentData($parent_id);
            $head_dept = $parent_data['headquarter']['name_jp'];
            $department = $parent_data['department']['name_jp'];

            if (in_array($layer_code, $complete_ba_arr)) {
                $min_flag = 'complete';
            }
            switch ($min_flag) {
                case 'complete': //all data is already complete
                    $sale_incharge = 'F';
                    $sale_admin = 'F';
                    $sale_manager = 'F';
                    $acc_incharge = 'F';
                    $acc_admin = 'F';
                    $acc_manager = 'F';
                    $done = 'done';
                    break;
                case 8:
                    $sale_incharge = 'F';
                    $sale_admin = 'F';
                    $sale_manager = 'F';
                    $acc_incharge = 'F';
                    $acc_admin = 'F';
                    $acc_manager = 'F';
                    $done = 'done';
                    break;
                case 7:
                    $sale_incharge = 'F';
                    $sale_admin = 'F';
                    $sale_manager = 'F';
                    $acc_incharge = 'F';
                    $acc_admin = 'F';
                    $acc_manager = 'NF';
                    break;
                case 6:
                    $sale_incharge = 'F';
                    $sale_admin = 'F';
                    $sale_manager = 'F';
                    $acc_incharge = 'F';
                    $acc_admin = 'NF';
                    $acc_manager = 'NF';
                    break;
                case 5:
                    $sale_incharge = 'F';
                    $sale_admin = 'F';
                    $sale_manager = 'F';
                    $acc_incharge = 'NF';
                    $acc_admin = 'NF';
                    $acc_manager = 'NF';
                    break;
                case 4:
                    $sale_incharge = 'F';
                    $sale_admin = 'F';
                    $sale_manager = 'NF';
                    $acc_incharge = 'NF';
                    $acc_admin = 'NF';
                    $acc_manager = 'NF';
                    break;
                case 3:
                    $sale_incharge = 'F';
                    $sale_admin = 'NF';
                    $sale_manager = 'NF';
                    $acc_incharge = 'NF';
                    $acc_admin = 'NF';
                    $acc_manager = 'NF';
                    break;
                default:
                    $sale_incharge = 'NF';
                    $sale_admin = 'NF';
                    $sale_manager = 'NF';
                    $acc_incharge = 'NF';
                    $acc_admin = 'NF';
                    $acc_manager = 'NF';
                    break;
            }
            $language = $this->Session->read('Config.language');
            $this->LayerType->virtualFields['layer_type_name'] = $language == 'eng' ? 'name_en' : 'name_jp';
            $layer_type = $this->LayerType->find('list',array(
                'fields' => array('type_order','layer_type_name'),
                'conditions' => array(
                    'type_order' => array(SETTING::LAYER_SETTING['topLayer'],SETTING::LAYER_SETTING['middleLayer'],SETTING::LAYER_SETTING['bottomLayer']),
                    'flag' => 1,
                ),
                'order' => 'type_order ASC'
            ));
           
            $prepare[] = array(
                'layer_code' => $layer_code,
                'head_dept' => $head_dept,
                'department' => $department,
                'name_jp' => $name_jp,
                'sale_incharge' => $sale_incharge,
                'sale_admin' => $sale_admin,
                'sale_manager' => $sale_manager,
                'acc_incharge' => $acc_incharge,
                'acc_admin' => $acc_admin,
                'acc_manager' => $acc_manager,
                'status' => $done,
                'acc_approve_date' => $acc_approve_date,
                'busi_approve_date' => $busi_approve_date,
                'topLayer' => $layer_type[SETTING::LAYER_SETTING['topLayer']],
                'middleLayer' => $layer_type[SETTING::LAYER_SETTING['middleLayer']],
                'bottomLayer' => $layer_type[SETTING::LAYER_SETTING['bottomLayer']],
            );
            
        }
        return $prepare;
    }

    public function progress_pdf()
    {
        # check user has permission to access this method
        // $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        // if ($user_level != 1 && $user_level != 4 && $user_level != 3 && $user_level != 2) {
        //     $this->redirect(array('controller'=>'Login', 'action'=>'Logout'));
        // }
        if ($this->request->is('post')) {
            $period = $this->Session->read('StockSelections_PERIOD_DATE');
            $get_data = $this->Stock->getBACodeFlag($period);
            $complete_ba = $this->Stock->getCompleteBACode($period);
            $data = $this->__prepareApprovedData($get_data, $complete_ba);
            $this->set(compact('data', 'period'));
        } else {
            $this->redirect(array('controller'=>'StockProgressReports', 'action'=>'index'));
        }
    }
}
