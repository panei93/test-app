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
 * @author        Khin Hnin Myo
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');
App::import('Vendor', 'php-excel-reader/PHPExcel');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package     app.Controller
 * @link        http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */

define('UPLOAD_FILEPATH', ROOT); //server
define('UPLOAD_PATH', 'app'.DS.'temp');

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'Permissions');
# Imports the Google Cloud client library

class AssetImportsController extends AppController
{
    public $components = array('PhpExcel.PhpExcel','Flash');
    public $uses = array('Asset','AssetEvent','Layer','AssetRemove','AssetSold');

    public function beforeFilter()
    {
        parent::CheckSession();
        $Common = New CommonController();

        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
      
        if((!in_array($layer_code, array_keys($permissions['index']['layers']))) || ($layer_code=="" && $permissions['index']['limit']>0)) {
            $errorMsg = parent::getErrorMsg('SE072', __('イベントと部署'));
            $this->Flash->set($errorMsg, array("key"=>"Error"));
            $this->redirect(array('controller'=>'AssetSelections', 'action'=>'index'));
        }
    }
    
    public function index($errmessage = null)
    {
        $this->layout = 'fixedassets';
        $errorMsg   = "";
        $successMsg = "";
        $Common = New CommonController();
        $event_name = $this->Session->read('EVENT_NAME');// get event name
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        if ($this->Session->check('CheckBAcode')) {
            $this->Session->read('CheckBAcode');
                
            $this->Session->delete('CheckBAcode');
            $this->set('CheckBAcode', 'CheckBAcode');
        }

        if ($this->Session->check('SkipSameAssetno')) {
            $this->Session->read('SkipSameAssetno');
                
            $this->Session->delete('SkipSameAssetno');
            $this->set('SkipSameAssetno', 'SkipSameAssetno');
        }

        if ($this->Session->check('SkipSameAssetnoflag')) {
            $this->Session->read('SkipSameAssetnoflag');
                
            $this->Session->delete('SkipSameAssetnoflag');
            $this->set('SkipSameAssetnoflag', 'SkipSameAssetnoflag');
        }

        if ($this->Session->check('SkipSameAssetnoExcel')) {
            $this->Session->read('SkipSameAssetnoExcel');
                
            $this->Session->delete('SkipSameAssetnoExcel');
            $this->set('SkipSameAssetnoExcel', 'SkipSameAssetnoExcel');
        }
        $permissions = $this->Session->read('PERMISSIONS');
        $status = $this->AssetEvent->find('first',array(
            'conditions' => array(
                'AssetEvent.flag <>' => 0
            ),
        ));
        $status = (!empty($status['AssetEvent']['flag'])) ? $status['AssetEvent']['flag'] : 0;
       
        $buttons = $Common->getButtonLists($status,$layer_code,$permissions);
        $this->set('successMsg', $successMsg);
        $this->set('errorMsg', $errorMsg);
        $this->set('event_name', $event_name);
        $this->set('buttons', $buttons);
        $this->render('index');
    }

    public function Save_CSV_File()
    {
        if ($this->request->is('post')) {
            
            # Global Variable for error message
            $ERROR_VALUE = "";
            $errorMsg = "";
            $successMsg = "";

            $role_id = $this->Session->read('ADMIN_LEVEL_ID');
            $login_id = $this->Session->read('LOGIN_ID');#get login id
        
            $event_name = $this->Session->read('EVENT_NAME');#get event name
            $event_id = $this->Session->read('EVENT_ID');#get event id
            #get reference_id from tbl_event
            $get_ref = $this->AssetEvent->find('list', array(
                'fields' => array('id','reference_event_id'),
                'conditions' => array(
                    'flag' => 1,
                    'id' => $event_id
                )
            ));
            $get_ref_id = $get_ref[$event_id];
            #get data from tbl_m-asset
            
            $getdata = $this->checkDatatoCal($get_ref_id);
            $tbl_m_asset = $getdata[0];
            $tbl_m_asset_remove = $getdata[1];
            $tbl_m_asset_sold = $getdata[2];

            #get concurrency_status that apply parallel multi user
            $concurrency_check = $this->AssetEvent->find('count',array(
                'conditions' => array('id' => $event_id, 'concurrency_status' => 1, 'flag' => 1)
            ));
            if($concurrency_check < 0) { #if concurrency_status is 1 show error msg
                $errorMsg = parent::getErrorMsg('SE051');
                $this->Flash->set($errorMsg, array('key'=>'excelError'));

                CakeLog::write('debug', ' The importing process is running by another user! '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
            } else { #if concurrency_status is 0, update concurrency_status is 1 in tbl_events
                #change concurrency_status is 0 to 1
                $update_concurrency_1 = $this->AssetEvent->Update_Concurrency_1($event_id);

                #get name, type, tmp_name, error, size of file                  
                $file = $this->request->params['form']['uploadfile'];

                $uploadPath = APP . 'tmp';#file path
                    
                $date = date('Y-m-d H:i:s');#for updated id and created id

                $CheckBAcode = array();
                $SkipSameAssetno = array();
                $removedDuplicateAsset = array();
                $storedDuplicateAsset = array();
                $SkipSameAssetnoExcel = array();
                $header_check = false;
                
                $check_reference = $this->AssetEvent->find('first',array(
                    'conditions' => array(
                        'reference_event_id'=>$event_id,
                        'flag !=' => '0' 
                    )
                ));
                
                if (empty($check_reference)) {
                    if (!empty($file)) {
                        if ($file['error'] == 0) {
                            $file_name  = $file['name'];
                            $file_type  = $file['type'];
                            $file_loc   = $file['tmp_name'];
                            $file_size  = $file['size'];
                            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                            
                            $fp = $this->getFilePointerUTF8($file_loc);
                            
                            if ($ext == "csv") {
                                if ($file_size <= 1048576) { #access file size is 1 Megabytes (MB)
                                    $header = fgetcsv($fp);
                                    $head_count = count($header);
                                    
                                    #Check header count match or not
                                    if ($head_count == 289) {
                                        if (ltrim($header[0]) == '会社コード'
                                            && ltrim($header[1]) == '会社名称'
                                            && ltrim($header[2]) == '第1キーコード(種類)'
                                            && ltrim($header[3]) == '第1キー名称(種類)'
                                            && ltrim($header[4]) == '第2キーコード(未指定)'
                                            && ltrim($header[5]) == '第2キー名称(未指定)'
                                            && ltrim($header[6]) == '第3キーコード(未指定)'
                                            && ltrim($header[7]) == '第3キー名称(未指定)'
                                            && ltrim($header[8]) == '第4キーコード(未指定)'
                                            && ltrim($header[9]) == '第4キー名称(未指定)'
                                            && ltrim($header[10]) == '第5キーコード(未指定)'
                                            && ltrim($header[11]) == '第5キー名称(未指定)'
                                            && ltrim($header[12]) == '資産番号・親'
                                            && ltrim($header[13]) == '資産番号・枝'
                                            && ltrim($header[14]) == '資産管理区分'
                                            && ltrim($header[15]) == '資産状態区分'
                                            && ltrim($header[16]) == '資産名称コード'
                                            && ltrim($header[17]) == '資産名称'
                                            && ltrim($header[18]) == '資産名称カナ'
                                            && ltrim($header[19]) == '本部'
                                            && ltrim($header[20]) == '本部名称'
                                            && ltrim($header[21]) == '部'
                                            && ltrim($header[22]) == '部名称'
                                            && ltrim($header[23]) == 'チーム'
                                            && ltrim($header[24]) == 'チーム名称'
                                            && ltrim($header[25]) == '事業領域'
                                            && ltrim($header[26]) == '事業領域名称'
                                            && ltrim($header[27]) == '設置場所コード'
                                            && ltrim($header[28]) == '設置場所名称'
                                            && ltrim($header[29]) == '配賦コード'
                                            && ltrim($header[30]) == '配賦名称'
                                            && ltrim($header[31]) == '種類コード'
                                            && ltrim($header[32]) == '種類名称'
                                            && ltrim($header[33]) == '構造用途コード'
                                            && ltrim($header[34]) == '構造用途名称'
                                            && ltrim($header[35]) == '分類コード'
                                            && ltrim($header[36]) == '分類名称'
                                            && ltrim($header[37]) == '取得年月日'
                                            && ltrim($header[38]) == '稼働年月日'
                                            && ltrim($header[39]) == '除売却年月日'
                                            && ltrim($header[40]) == '移動年月日'
                                            && ltrim($header[41]) == '数量'
                                            && ltrim($header[42]) == '数量単位'
                                            && ltrim($header[43]) == '面積'
                                            && ltrim($header[44]) == '面積単位'
                                            && ltrim($header[45]) == '中古区分'
                                            && ltrim($header[46]) == '取得価額(会社帳簿)'
                                            && ltrim($header[47]) == '償却方法(会社帳簿)'
                                            && ltrim($header[48]) == '耐用年数(会社帳簿)'
                                            && ltrim($header[49]) == '償却率(会社帳簿)'
                                            && ltrim($header[50]) == '償却月数(会社帳簿)'
                                            && ltrim($header[51]) == '残存月数(会社帳簿)'
                                            && ltrim($header[52]) == '期首帳簿価額(会社帳簿)'
                                            && ltrim($header[53]) == '償却計算基礎額(会社帳簿)'
                                            && ltrim($header[54]) == '残存率(会社帳簿)'
                                            && ltrim($header[55]) == '残存価額(会社帳簿)'
                                            && ltrim($header[56]) == '期首普通償却累計額(会社帳簿)'
                                            && ltrim($header[57]) == '期首増加償却累計額(会社帳簿)'
                                            && ltrim($header[58]) == '期首特別償却累計額(会社帳簿)'
                                            && ltrim($header[59]) == '当期普通償却額(当月累計)(会社帳簿)'
                                            && ltrim($header[60]) == '当期増加償却額(当月累計)(会社帳簿)'
                                            && ltrim($header[61]) == '当期特別償却額(当月累計)(会社帳簿)'
                                            && ltrim($header[62]) == '当期任意償却額(会社帳簿)'
                                            && ltrim($header[63]) == '固定資産見積変更年月日(会社帳簿)'
                                            && ltrim($header[64]) == '当月末帳簿価額(会社帳簿)'
                                            && ltrim($header[65]) == '増減帳簿価額(会社帳簿)'
                                            && ltrim($header[66]) == '増加償却率(会社帳簿)'
                                            && ltrim($header[67]) == '遊休開始年月(会社帳簿)'
                                            && ltrim($header[68]) == '遊休復帰年月(会社帳簿)'
                                            && ltrim($header[69]) == '初年度計算区分(会社帳簿)'
                                            && ltrim($header[70]) == '償却完了(会社帳簿)'
                                            && ltrim($header[71]) == '償却切替年度(会社帳簿)'
                                            && ltrim($header[72]) == '残存償却開始年度(会社帳簿)'
                                            && ltrim($header[73]) == '残存償却月数(会社帳簿)'
                                            && ltrim($header[74]) == '見積変更後帳簿価額(会社帳簿)'
                                            && ltrim($header[75]) == '取得価額(税法帳簿)'
                                            && ltrim($header[76]) == '償却方法(税法帳簿)'
                                            && ltrim($header[77]) == '耐用年数(税法帳簿)'
                                            && ltrim($header[78]) == '償却率(税法帳簿)'
                                            && ltrim($header[79]) == '償却月数(税法帳簿)'
                                            && ltrim($header[80]) == '残存月数(税法帳簿)'
                                            && ltrim($header[81]) == '期首帳簿価額(税法帳簿)'
                                            && ltrim($header[82]) == '償却計算基礎額(税法帳簿)'
                                            && ltrim($header[83]) == '残存率(税法帳簿)'
                                            && ltrim($header[84]) == '残存価額(税法帳簿)'
                                            && ltrim($header[85]) == '期首普通償却累計額(税法帳簿)'
                                            && ltrim($header[86]) == '期首増加償却累計額(税法帳簿)'
                                            && ltrim($header[87]) == '期首特別償却累計額(税法帳簿)'
                                            && ltrim($header[88]) == '当期普通償却額(当月累計)(税法帳簿)'
                                            && ltrim($header[89]) == '当期増加償却額(当月累計)(税法帳簿)'
                                            && ltrim($header[90]) == '当期特別償却額(当月累計)(税法帳簿)'
                                            && ltrim($header[91]) == '当期任意償却額(税法帳簿)'
                                            && ltrim($header[92]) == '固定資産見積変更年月日(税法帳簿)'
                                            && ltrim($header[93]) == '当月末帳簿価額(税法帳簿)'
                                            && ltrim($header[94]) == '増減帳簿価額(税法帳簿)'
                                            && ltrim($header[95]) == '増加償却率(税法帳簿)'
                                            && ltrim($header[96]) == '遊休開始年月(税法帳簿)'
                                            && ltrim($header[97]) == '遊休復帰年月(税法帳簿)'
                                            && ltrim($header[98]) == '初年度計算区分(税法帳簿)'
                                            && ltrim($header[99]) == '償却完了(税法帳簿)'
                                            && ltrim($header[100]) == '償却切替年度(税法帳簿)'
                                            && ltrim($header[101]) == '残存償却開始年度(税法帳簿)'
                                            && ltrim($header[102]) == '残存償却月数(税法帳簿)'
                                            && ltrim($header[103]) == '見積変更後帳簿価額(税法帳簿)'
                                            && ltrim($header[104]) == '取得価額(第3帳簿)'
                                            && ltrim($header[105]) == '償却方法(第3帳簿)'
                                            && ltrim($header[106]) == '耐用年数(第3帳簿)'
                                            && ltrim($header[107]) == '償却率(第3帳簿)'
                                            && ltrim($header[108]) == '償却月数(第3帳簿)'
                                            && ltrim($header[109]) == '残存月数(第3帳簿)'
                                            && ltrim($header[110]) == '期首帳簿価額(第3帳簿)'
                                            && ltrim($header[111]) == '償却計算基礎額(第3帳簿)'
                                            && ltrim($header[112]) == '残存率(第3帳簿)'
                                            && ltrim($header[113]) == '残存価額(第3帳簿)'
                                            && ltrim($header[114]) == '期首普通償却累計額(第3帳簿)'
                                            && ltrim($header[115]) == '期首増加償却累計額(第3帳簿)'
                                            && ltrim($header[116]) == '期首特別償却累計額(第3帳簿)'
                                            && ltrim($header[117]) == '当期普通償却額(当月累計)(第3帳簿)'
                                            && ltrim($header[118]) == '当期増加償却額(当月累計)(第3帳簿)'
                                            && ltrim($header[119]) == '当期特別償却額(当月累計)(第3帳簿)'
                                            && ltrim($header[120]) == '当期任意償却額(第3帳簿)'
                                            && ltrim($header[121]) == '固定資産見積変更年月日(第3帳簿)'
                                            && ltrim($header[122]) == '当月末帳簿価額(第3帳簿)'
                                            && ltrim($header[123]) == '増減帳簿価額(第3帳簿)'
                                            && ltrim($header[124]) == '増加償却率(第3帳簿)'
                                            && ltrim($header[125]) == '遊休開始年月(第3帳簿)'
                                            && ltrim($header[126]) == '遊休復帰年月(第3帳簿)'
                                            && ltrim($header[127]) == '初年度計算区分(第3帳簿)'
                                            && ltrim($header[128]) == '償却完了(第3帳簿)'
                                            && ltrim($header[129]) == '償却切替年度(第3帳簿)'
                                            && ltrim($header[130]) == '残存償却開始年度(第3帳簿)'
                                            && ltrim($header[131]) == '残存償却月数(第3帳簿)'
                                            && ltrim($header[132]) == '見積変更後帳簿価額(第3帳簿)'
                                            && ltrim($header[133]) == '取得価額(第4帳簿)'
                                            && ltrim($header[134]) == '償却方法(第4帳簿)'
                                            && ltrim($header[135]) == '耐用年数(第4帳簿)'
                                            && ltrim($header[136]) == '償却率(第4帳簿)'
                                            && ltrim($header[137]) == '償却月数(第4帳簿)'
                                            && ltrim($header[138]) == '残存月数(第4帳簿)'
                                            && ltrim($header[139]) == '期首帳簿価額(第4帳簿)'
                                            && ltrim($header[140]) == '償却計算基礎額(第4帳簿)'
                                            && ltrim($header[141]) == '残存率(第4帳簿)'
                                            && ltrim($header[142]) == '残存価額(第4帳簿)'
                                            && ltrim($header[143]) == '期首普通償却累計額(第4帳簿)'
                                            && ltrim($header[144]) == '期首増加償却累計額(第4帳簿)'
                                            && ltrim($header[145]) == '期首特別償却累計額(第4帳簿)'
                                            && ltrim($header[146]) == '当期普通償却額(当月累計)(第4帳簿)'
                                            && ltrim($header[147]) == '当期増加償却額(当月累計)(第4帳簿)'
                                            && ltrim($header[148]) == '当期特別償却額(当月累計)(第4帳簿)'
                                            && ltrim($header[149]) == '当期任意償却額(第4帳簿)'
                                            && ltrim($header[150]) == '固定資産見積変更年月日(第4帳簿)'
                                            && ltrim($header[151]) == '当月末帳簿価額(第4帳簿)'
                                            && ltrim($header[152]) == '増減帳簿価額(第4帳簿)'
                                            && ltrim($header[153]) == '増加償却率(第4帳簿)'
                                            && ltrim($header[154]) == '遊休開始年月(第4帳簿)'
                                            && ltrim($header[155]) == '遊休復帰年月(第4帳簿)'
                                            && ltrim($header[156]) == '初年度計算区分(第4帳簿)'
                                            && ltrim($header[157]) == '償却完了(第4帳簿)'
                                            && ltrim($header[158]) == '償却切替年度(第4帳簿)'
                                            && ltrim($header[159]) == '残存償却開始年度(第4帳簿)'
                                            && ltrim($header[160]) == '残存償却月数(第4帳簿)'
                                            && ltrim($header[161]) == '見積変更後帳簿価額(第4帳簿)'
                                            && ltrim($header[162]) == '購入先コード'
                                            && ltrim($header[163]) == '購入先名称'
                                            && ltrim($header[164]) == '貸出先コード'
                                            && ltrim($header[165]) == '貸出先名称'
                                            && ltrim($header[166]) == '建仮資産番号・親'
                                            && ltrim($header[167]) == '建仮資産番号・枝'
                                            && ltrim($header[168]) == '棚卸台帳作成年月日'
                                            && ltrim($header[169]) == '管理分類コード'
                                            && ltrim($header[170]) == '備考1'
                                            && ltrim($header[171]) == '備考2'
                                            && ltrim($header[172]) == '取得時稟議決裁番号'
                                            && ltrim($header[173]) == '取得時摘要'
                                            && ltrim($header[174]) == '圧縮コード'
                                            && ltrim($header[175]) == '圧縮名称'
                                            && ltrim($header[176]) == '圧縮区分'
                                            && ltrim($header[177]) == '圧縮額'
                                            && ltrim($header[178]) == '期首圧縮残高'
                                            && ltrim($header[179]) == '期首圧縮認容額'
                                            && ltrim($header[180]) == '圧縮基礎額'
                                            && ltrim($header[181]) == '圧縮残存価額'
                                            && ltrim($header[182]) == '圧縮認容額'
                                            && ltrim($header[183]) == '改定後圧縮額'
                                            && ltrim($header[184]) == '消費税額'
                                            && ltrim($header[185]) == '償却超過額'
                                            && ltrim($header[186]) == '償却不足額'
                                            && ltrim($header[187]) == '特償コード'
                                            && ltrim($header[188]) == '特償名称'
                                            && ltrim($header[189]) == '特別償却区分'
                                            && ltrim($header[190]) == '特償/割増率(分子)'
                                            && ltrim($header[191]) == '特償/割増率(分母)'
                                            && ltrim($header[192]) == '別表対象区分'
                                            && ltrim($header[193]) == '償却資産区分'
                                            && ltrim($header[194]) == '相手勘定科目コード'
                                            && ltrim($header[195]) == '相手勘定科目名称'
                                            && ltrim($header[196]) == '相手補助科目コード'
                                            && ltrim($header[197]) == '相手補助科目名称'
                                            && ltrim($header[198]) == '合併受入資産'
                                            && ltrim($header[199]) == '原始取得年月日'
                                            && ltrim($header[200]) == 'グループコード'
                                            && ltrim($header[201]) == 'グループ名称'
                                            && ltrim($header[202]) == 'シナリオコード'
                                            && ltrim($header[203]) == 'シナリオ名称'
                                            && ltrim($header[204]) == '主要資産区分'
                                            && ltrim($header[205]) == '除去債務計上区分'
                                            && ltrim($header[206]) == '契約番号'
                                            && ltrim($header[207]) == '契約番号名称'
                                            && ltrim($header[208]) == '確認'
                                            && ltrim($header[209]) == '当年申告年'
                                            && ltrim($header[210]) == '当年申告地コード'
                                            && ltrim($header[211]) == '当年申告地名称'
                                            && ltrim($header[212]) == '当年申告種類'
                                            && ltrim($header[213]) == '当年申告取得価額'
                                            && ltrim($header[214]) == '当年申告耐用年数'
                                            && ltrim($header[215]) == '当年申告増加償却率'
                                            && ltrim($header[216]) == '当年特例非課税コード'
                                            && ltrim($header[217]) == '当年特例非課税名称'
                                            && ltrim($header[218]) == '当年特例率(分子)'
                                            && ltrim($header[219]) == '当年特例率(分母)'
                                            && ltrim($header[220]) == '当年総務省申告種類'
                                            && ltrim($header[221]) == '当年総務省申告細目'
                                            && ltrim($header[222]) == '前年申告年'
                                            && ltrim($header[223]) == '前年申告地コード'
                                            && ltrim($header[224]) == '前年申告地名称'
                                            && ltrim($header[225]) == '前年申告種類'
                                            && ltrim($header[226]) == '前年申告取得価額'
                                            && ltrim($header[227]) == '前年申告耐用年数'
                                            && ltrim($header[228]) == '前年申告増加償却率'
                                            && ltrim($header[229]) == '前年特例非課税コード'
                                            && ltrim($header[230]) == '前年特例非課税名称'
                                            && ltrim($header[231]) == '前年特例率(分子)'
                                            && ltrim($header[232]) == '前年特例率(分母)'
                                            && ltrim($header[233]) == '前年1月1日帳簿価額'
                                            && ltrim($header[234]) == '前年評価額'
                                            && ltrim($header[235]) == '最終申告年'
                                            && ltrim($header[236]) == '前年総務省申告種類'
                                            && ltrim($header[237]) == '前年総務省申告細目'
                                            && ltrim($header[238]) == '期首減損累計額(会社帳簿)'
                                            && ltrim($header[239]) == '当期減損額(会社帳簿)'
                                            && ltrim($header[240]) == '償却計算残存価額(会社帳簿)'
                                            && ltrim($header[241]) == '減損後帳簿価額(会社帳簿)'
                                            && ltrim($header[242]) == '減損前耐用年数(会社帳簿)'
                                            && ltrim($header[243]) == '減損前償却月数(会社帳簿)'
                                            && ltrim($header[244]) == '減損評価年月日(会社帳簿)'
                                            && ltrim($header[245]) == '原始取得価額(会社帳簿)'
                                            && ltrim($header[246]) == '改定後取得価額(会社帳簿)'
                                            && ltrim($header[247]) == '改定前耐用年数(会社帳簿)'
                                            && ltrim($header[248]) == '改定年月日(会社帳簿)'
                                            && ltrim($header[249]) == '期首減損累計額(税法帳簿)'
                                            && ltrim($header[250]) == '当期減損額(税法帳簿)'
                                            && ltrim($header[251]) == '償却計算残存価額(税法帳簿)'
                                            && ltrim($header[252]) == '減損後帳簿価額(税法帳簿)'
                                            && ltrim($header[253]) == '減損前耐用年数(税法帳簿)'
                                            && ltrim($header[254]) == '減損前償却月数(税法帳簿)'
                                            && ltrim($header[255]) == '減損評価年月日(税法帳簿)'
                                            && ltrim($header[256]) == '原始取得価額(税法帳簿)'
                                            && ltrim($header[257]) == '改定後取得価額(税法帳簿)'
                                            && ltrim($header[258]) == '改定前耐用年数(税法帳簿)'
                                            && ltrim($header[259]) == '改定年月日(税法帳簿)'
                                            && ltrim($header[260]) == '期首減損累計額(第3帳簿)'
                                            && ltrim($header[261]) == '当期減損額(第3帳簿)'
                                            && ltrim($header[262]) == '償却計算残存価額(第3帳簿)'
                                            && ltrim($header[263]) == '減損後帳簿価額(第3帳簿)'
                                            && ltrim($header[264]) == '減損前耐用年数(第3帳簿)'
                                            && ltrim($header[265]) == '減損前償却月数(第3帳簿)'
                                            && ltrim($header[266]) == '減損評価年月日(第3帳簿)'
                                            && ltrim($header[267]) == '原始取得価額(第3帳簿)'
                                            && ltrim($header[268]) == '改定後取得価額(第3帳簿)'
                                            && ltrim($header[269]) == '改定前耐用年数(第3帳簿)'
                                            && ltrim($header[270]) == '改定年月日(第3帳簿)'
                                            && ltrim($header[271]) == '期首減損累計額(第4帳簿)'
                                            && ltrim($header[272]) == '当期減損額(第4帳簿)'
                                            && ltrim($header[273]) == '償却計算残存価額(第4帳簿)'
                                            && ltrim($header[274]) == '減損後帳簿価額(第4帳簿)'
                                            && ltrim($header[275]) == '減損前耐用年数(第4帳簿)'
                                            && ltrim($header[276]) == '減損前償却月数(第4帳簿)'
                                            && ltrim($header[277]) == '減損評価年月日(第4帳簿)'
                                            && ltrim($header[278]) == '原始取得価額(第4帳簿)'
                                            && ltrim($header[279]) == '改定後取得価額(第4帳簿)'
                                            && ltrim($header[280]) == '改定前耐用年数(第4帳簿)'
                                            && ltrim($header[281]) == '改定年月日(第4帳簿)'
                                            && ltrim($header[282]) == '帳表ID'
                                            && ltrim($header[283]) == '出力時間'
                                            && ltrim($header[284]) == '出力条件(処理月度)'
                                            && ltrim($header[285]) == '出力条件(資産管理区分)'
                                            && ltrim($header[286]) == '出力条件(資産状態区分)'
                                            && ltrim($header[287]) == '出力条件(全ての増減履歴を出力する)'
                                            && ltrim($header[288]) == '出力条件(出力キー)'
                                            
                                        ) {
                                            $header_check = true;
                                        } else {
                                            
                                            $errorMsg = parent::getErrorMsg('SE022');
                                            $this->Flash->set($errorMsg, array('key'=>'excelError'));

                                            #update concurrency_status is 1 to 0 in tbl_event
                                            $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);


                                            CakeLog::write('debug', ' File header format Invalid. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                                        }
                                    } else {
                                        $errorMsg = parent::getErrorMsg('SE021');
                                        
                                        $this->Flash->set($errorMsg, array('key'=>'excelError'));

                                        #update concurrency_status is 1 to 0 in tbl_event
                                        $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);

                                        CakeLog::write('debug', ' File format Invalid(no. of header(289)). In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                        $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                                    }
                                } else {
                                    CakeLog::write('debug', 'file size over 1MB. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                    $errorMsg = parent::getErrorMsg('SE020');
                                    $this->Flash->set($errorMsg, array('key'=>'excelError'));

                                    #update concurrency_status is 1 to 0 in tbl_event
                                    $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);

                                    $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                                }
                            } else {
                                $errorMsg = parent::getErrorMsg('SE013', $ext);
                                $this->Flash->set($errorMsg, array('key'=>'excelError'));

                                #update concurrency_status is 1 to 0 in tbl_event
                                $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);

                                CakeLog::write('debug', ' File Extension is Invalid. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                            }
                        } else {
                            $errorMsg = parent::getErrorMsg('SE015');
                            $this->Flash->set($errorMsg, array('key'=>'excelError'));

                            #update concurrency_status is 1 to 0 in tbl_event
                            $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);

                            CakeLog::write('debug', ' File Format is Invalid. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                            $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                        }
                    } else {
                        $errorMsg = parent::getErrorMsg('SE015');
                        $this->Flash->set($errorMsg, array('key'=>'excelError'));

                        #update concurrency_status is 1 to 0 in tbl_event
                        $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);

                        CakeLog::write('debug', ' File Format is Invalid. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                        $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE053');
                    $this->Flash->set($errorMsg, array('key'=>'excelError'));

                    #update concurrency_status is 1 to 0 in tbl_event
                    $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);

                    CakeLog::write('debug', ' This event is referenced by another event. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                    $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                }
                if($header_check) {
                    $one = array(); $two = array(); $three = array();
                    $data = array();
                    $data_one = array();
                    $data_removed = array();
                    $data_sold = array();
                    $get_data_asset = array();
                    $get_database_removed = array();
                    $get_database_removed_date = array();
                    $get_data_removed_ba = array();
                    $get_data_removed_quantity = array();
                    $get_database_sold_date = array();

                    $data_removed_ref = array();
                    $data_sold_ref = array();
                    $status = "";
                    $normal = array();
                    $a = 0;
                    $row = 1;
                    $loopNo=-1;
                    if (!empty($fp)) {
                        while (($handle = fgetcsv($fp)) !== false) {
                            $row++;
                            $asset_no1 = $handle[12];#M
                            $asset_no1 = str_pad($asset_no1, 7, "0", STR_PAD_LEFT);
                            $asset_no2 = $handle[13];#N
                            $asset_no2 = str_pad($asset_no2, 3, "0", STR_PAD_LEFT);
                            $asset_no = $asset_no1.$asset_no2;
                            $asset_name = $handle[17];#R
                            $asset_status = $handle[15];#P
                            $layer_code = $handle[25];#Z
                            $name_jp = $handle[26];#AA
                            $place_code = $handle[27];#AB
                            $place_code = str_pad($place_code, 5, "0", STR_PAD_LEFT);
                            $place_name = $handle[28];#AC
                            $type_code = $handle[31];#AF
                            $type_name = $handle[32];#AG
                            $acq_date = $handle[37];#AL
                            $lost_date = $handle[39];#AN
                            $move_date = $handle[40];#AO
                            $quantity = $handle[41];#AP
                            $amount = $handle[64];#BM
                            $label_no = $asset_no1."-".$asset_no2;

                            if (($asset_status == "通常") || (strpos($asset_status, '通常') == true)) {
                                $asset_status = "1";
                            }
                            
                            if (($asset_status == "除却") || ($asset_status == "除却済") || (strpos($asset_status, '除却') == true)) {
                                $asset_status = "2";
                            }
                            
                            if (($asset_status == "売却") || ($asset_status == "売却済") || (strpos($asset_status, '売却') == true)) {
                                $asset_status = "3";
                            }

                            # validation for input field from csv (Start)
                            $asset_no_true = true;
                            $layer_code_true = true;
                            $place_code_true = true;
                            $type_code_true = true;
                            $acq_date_true = true;
                            $quantity_true = true;
                            $amount_true = true;
                                
                            if (!empty($asset_no) && !empty($asset_no1) && !empty($asset_no2)) {
                                $asset_no_length = mb_strlen(trim($asset_no));

                                if ($asset_no_length <= '12') {
                                    $asset_no_true = true;

                                    if (!empty($layer_code)) {
                                        $layer_code_length = mb_strlen(trim($layer_code));

                                        if ($layer_code_length <= '6') {
                                            $layer_code_true = true;

                                            if (!empty($place_code)) {
                                                $place_code_length = mb_strlen(trim($place_code));

                                                if ($place_code_length <= '15') {
                                                    $place_code_true = true;

                                                    if (!empty($type_code)) {
                                                        //type code is equal to 2nd key code
                                                        $type_code_length = mb_strlen(trim($type_code));

                                                        if ($type_code_length <= '12') {
                                                            $type_code_true = true;

                                                            if (!empty($acq_date)) {
                                                                $acq_date_length = mb_strlen(trim($acq_date));

                                                                if ($acq_date_length <= '15') {
                                                                    $acq_date_true = true;

                                                                    if (isset($quantity)) {
                                                                        $removeComma = str_replace(',', '', $quantity);

                                                                        if (preg_match('/^[0-9]+$/', $removeComma)) {
                                                                            $quantity_true = true;

                                                                            $quantity_length = mb_strlen(trim($quantity));

                                                                            if ($quantity_length <= '11') {
                                                                                $quantity_true = true;

                                                                                if (!empty($amount)) {
                                                                                    $removeComma = str_replace(',', '', $amount);
                                                                                        
                                                                                    if (preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/', $removeComma)) {
                                                                                        $amount_true = true;
                                                                                    } else {
                                                                                        $amount_true = false;

                                                                                        CakeLog::write('debug', ' Invalid amount format error occur '.$amount.' will be "float Format", at BM and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                                                        global $ERROR_VALUE;
                                                                                        $ERROR_VALUE = "BM";
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                $quantity_true = false;

                                                                                CakeLog::write('debug', ' Invalid quantity format error occur. '.$quantity.' will be integer length <=11, at col AP and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                                                global $ERROR_VALUE;
                                                                                $ERROR_VALUE = "AP";
                                                                            }
                                                                        } else {
                                                                            $quantity_true = false;

                                                                            CakeLog::write('debug', ' Invalid Integer format error occur '.$quantity.' will be "Integer Format[0-9]", at AP and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                                            global $ERROR_VALUE;
                                                                            $ERROR_VALUE = "AP";
                                                                        }
                                                                    } else {
                                                                        $quantity_true = false;

                                                                        CakeLog::write('debug', ' quantity is empty at col AP and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                                        global $ERROR_VALUE;
                                                                        $ERROR_VALUE = "AP";
                                                                    }
                                                                } else {
                                                                    $acq_date_true = false;

                                                                    CakeLog::write('debug', ' Invalid acq_date format error occur. '.$acq_date.' will be string length <=15, at col AL and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                                    global $ERROR_VALUE;
                                                                    $ERROR_VALUE = "AL";
                                                                }
                                                            }
                                                        } else {
                                                            $type_code_true = false;

                                                            CakeLog::write('debug', ' Invalid type_code format error occur. '.$type_code.' will be string length <=12, at col AF and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                            global $ERROR_VALUE;
                                                            $ERROR_VALUE = "AF";
                                                        }
                                                    }
                                                } else {
                                                    $place_code_true = "false";

                                                    CakeLog::write('debug', ' Invalid place_code format error occur. '.$place_code.' will be string length <=15, at col AB and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                                    global $ERROR_VALUE;
                                                    $ERROR_VALUE = "AB";
                                                }
                                            }
                                        } else {
                                            $layer_code_true = false;

                                            CakeLog::write('debug', ' Invalid layer_code format error occur. '.$layer_code.' will be string length <=6, at col Z and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                            global $ERROR_VALUE;
                                            $ERROR_VALUE = "Z";
                                        }
                                    } else {
                                        $layer_code_true = false;
                                            
                                        CakeLog::write('debug', ' layer_code is empty at col Z and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                        global $ERROR_VALUE;
                                        $ERROR_VALUE = "Z";
                                    }
                                } else {
                                    $asset_no_true = false;

                                    CakeLog::write('debug', ' Invalid asset_no format error occur. '.$asset_no.' will be string length <=12, at col M and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                    global $ERROR_VALUE;
                                    $ERROR_VALUE = "M";
                                }
                            } else {
                                $asset_no_true = false;

                                CakeLog::write('debug', ' asset_no is empty at col M and row '.$row.'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                global $ERROR_VALUE;
                                $ERROR_VALUE = "M";
                            }
                            # validation for input field from csv (End)

                            # remove duplicate asset_no, layer_code, asset_status and lost_date (Start)
                            $isDuplicate = false;
                            $cnt = count($storedDuplicateAsset);
                            
                            if ($cnt == 0) {
                                #added place_code in removing duplicate rows(2021/01/07)
                                $storedDuplicateAsset[] = array(
                                    'asset_no' => $asset_no,
                                    'layer_code' => $layer_code,
                                    'asset_status' => $asset_status,
                                    'lost_date' => $lost_date,
                                    // 'place_code' => $place_code
                                );
                            } else {
                                for ($i=0; $i<$cnt; $i++) {
                                    $dup_ass_no = $storedDuplicateAsset[$i]['asset_no'];
                                    $dup_layer_code = $storedDuplicateAsset[$i]['layer_code'];
                                    $dup_asset_status = $storedDuplicateAsset[$i]['asset_status'];
                                    $dup_date = $storedDuplicateAsset[$i]['lost_date'];
                                    // $dup_place_code = $storedDuplicateAsset[$i]['place_code'];

                                    if ($dup_ass_no == $asset_no && $dup_layer_code == $layer_code && $dup_asset_status == $asset_status && $dup_date == $lost_date) {
                                        $isDuplicate = true;
                                    }
                                }
                                $storedDuplicateAsset[] = array(
                                        'asset_no' => $asset_no,
                                        'layer_code' => $layer_code,
                                        'asset_status' => $asset_status,
                                        'lost_date' => $lost_date,
                                        // 'place_code' => $place_code
                                    );
                            }
                            if ($isDuplicate == true) {
                                $SkipSameAssetnoExcel[] = __("行 ").("<b>").$row.("</b>").__(" の 資産番号 : ").("<b>").$asset_no.("</b>").__(" と 事業領域 ").("<b>").$layer_code.("</b>").__(" がCSVで重複しています。");
                                    
                                $this->Session->write('SkipSameAssetnoExcel', 'SkipSameAssetnoExcel');
                                continue;
                            } else {
                                array_push($removedDuplicateAsset, $asset_no);
                            }
                            # remove duplicate asset_no, layer_code, asset_status and lost_date (End)
                          
                            if ($asset_no_true && $layer_code_true && $place_code_true && $type_code_true && $acq_date_true && $quantity_true && $amount_true) {
                                $con = [];
                                $con['Layer.flag'] = 1;
                                $con['Layer.layer_code'] = $layer_code;
                                $get_ba = $this->checkDatatoValidate($con, 'Layer');
                              
                                $con1['Asset.flag'] = 4;
                                $con1['asset_event_id'] = $event_id;
                                $con1['Asset.layer_code'] = $layer_code;
                                $flag_four = $this->checkDatatoValidate($con1, 'Asset');
                              
                                if(!empty($get_ba) && empty($flag_four)) {

                                    $cond = [];
                                   // $cond['asset_event_id'] = $get_ref_id;
                                    $cond['asset_no'] = $asset_no;
                                    $cond['layer_code'] = $layer_code;
                                  
                                    $status = "";
                                    if (!in_array($layer_code."/".$asset_no, $normal) && $asset_status != "1" && $get_ref_id == 0) {#no have normal and have remove/sold
                                        $status = ($asset_status == "2") ? "4" : (($asset_status == "3") ? "5" : "1");
                                        $asset_status = "1";
                                    }
                                    
                                    if($asset_status == "1") {
                                        $con_nor = [];
                                        $con_nor = $cond;
                                       // unset($con_nor['flag']);
                                       $con_nor['Asset.flag !='] = 0;
                                       // $con_nor['asset_no'] = $asset_no;
                                     
                                    
                                        $check_asset = $this->checkDatatoValidate($con_nor, 'Asset');
                                            
                                        if(empty($check_asset)) {
                                            #calculation here
                                            #added new asset(no have this asset in ref event)
                                            if(!empty($tbl_m_asset) && $get_ref_id != 0) {
                                                if(empty($tbl_m_asset[$layer_code."/".$asset_no])) $status = "1";#New
                                                else {
                                                    $normalQty = $tbl_m_asset[$layer_code."/".$asset_no];
                                                    $diffQty = $quantity -  $normalQty;

                                                    if($diffQty == 0) $status = "2";#Already
                                                    elseif($diffQty > 0) $status = "3";#Move
                                                    else {
                                                        $one[$layer_code."/".$asset_no] = array(
                                                            "asset_event_id" => $event_id,
                                                            "layer_code" => $layer_code,
                                                            "layer_name" => $name_jp,
                                                            "asset_no" => $asset_no,
                                                            "asset_name" => $asset_name,
                                                            "quantity" => $quantity,
                                                            "acq_date" => $acq_date,
                                                            "place_code" => $place_code,
                                                            "place_name" => $place_name,
                                                            "2nd_key_code" => $type_code,
                                                            "2nd_key_name" => $type_name,
                                                            "lost_date" => $lost_date,
                                                            "label_no" => $label_no,
                                                            "amount" => $amount,
                                                            "status" => ($status == "")? "1" : $status,
                                                            "diff_qty" => ($status == "1")? "0": $diffQty,
                                                            "flag" => 1,
                                                            "created_by" => $login_id,
                                                            "updated_by" => $login_id,
                                                            "created_date" => $date,
                                                            "updated_date" => $date,
                                                            "asset_status" => ($status == "4") ? "2" : (($status == "5") ? "3" : "1"),
                                                        );
                                                        continue;
                                                    }
                                                }
                                            
                                            }
                                            $data[] = array(
                                                "asset_event_id" => $event_id,
                                                "layer_code" => $layer_code,
                                                "layer_name" => $name_jp,
                                                "asset_no" => $asset_no,
                                                "asset_name" => $asset_name,
                                                "quantity" => $quantity,
                                                "acq_date" => $acq_date,
                                                "place_code" => $place_code,
                                                "place_name" => $place_name,
                                                "2nd_key_code" => $type_code,
                                                "2nd_key_name" => $type_name,
                                                "lost_date" => $lost_date,
                                                "label_no" => $label_no,
                                                "amount" => $amount,
                                                "status" => ($status == "")? "1" : $status,
                                                "diff_qty" => ($status == "1")? "0": $diffQty,
                                                "flag" => 1,
                                                "created_by" => $login_id,
                                                "updated_by" => $login_id,
                                                "created_date" => $date,
                                                "updated_date" => $date,
                                                "asset_status" => ($status == "4") ? "2" : (($status == "5") ? "3" : "1"),
                                            );
                                            if($status == "") $normal[] = $layer_code."/".$asset_no;


                                        }else {

                                            #duplicate=>have more than one
                                            $SkipSameAssetno[] = __("行 ").("<b>").$row.("</b>").__(" の 資産番号 : ").("<b>").$asset_no.("</b>").__(" は 既に存在します。");
                                                
                                            $this->Session->write('SkipSameAssetno', 'SkipSameAssetno');
                                        }
                                    }
                                    elseif($asset_status == "2") {
                                        $con_rem = [];
                                        $con_rem = $cond;
                                        // unset($con_rem['flag']);
                                        // $con_rem['asset_no'] = $asset_no;
                                        $con_rem['remove_date'] = $lost_date;
                                        $check_asset_removed = $this->checkDatatoValidate($con_rem, 'AssetRemove');
                                        if(empty($check_asset_removed)) {
                                            #calculation here
                                            if(!empty($tbl_m_asset_remove) && $get_ref_id != 0) {
                                                if(empty($tbl_m_asset_remove[$layer_code."/".$asset_no])) {

                                                    $status = "4";#Lost
                                                    #no remove data and have normal data only in ref event
                                                    #have remove only and no have normal cur event
                                                    #added new flow (20201229)
                                                    #sold/Remove condition
                                                    if(!empty($tbl_m_asset[$layer_code."/".$asset_no]) && empty($one)) {
                                                        $status = "4";#Lost
                                                        $asset_status = "2";
                                                        $diffQty = $quantity;
                                                        $one[$layer_code."/".$asset_no] = array(
                                                            "asset_event_id" => $event_id,
                                                            "layer_code" => $layer_code,
                                                            "layer_name" => $name_jp,
                                                            "asset_no" => $asset_no,
                                                            "asset_name" => $asset_name,
                                                            "quantity" => $quantity,
                                                            "acq_date" => $acq_date,
                                                            "place_code" => $place_code,
                                                            "place_name" => $place_name,
                                                            "2nd_key_code" => $type_code,
                                                            "2nd_key_name" => $type_name,
                                                            "lost_date" => $lost_date,
                                                            "label_no" => $label_no,
                                                            "amount" => $amount,
                                                            "status" => ($status == "")? "1" : $status,
                                                            "diff_qty" => ($status == "1")? "0": $diffQty,
                                                            "flag" => 1,
                                                            "created_by" => $login_id,
                                                            "updated_by" => $login_id,
                                                            "created_date" => $date,
                                                            "updated_date" => $date,
                                                            "asset_status" => ($status == "4") ? "2" : (($status == "5") ? "3" : "1"),
                                                        );

                                                    }
                                                }
                                                else {
                                                    if(in_array($layer_code."/".$asset_no, $two)) {#if rem_diffQty==0, check data with same ba, asset_no
                                                        $status = "4";#Lost
                                                    }else {
                                                        $data_removed[] = array(
                                                            "asset_event_id" => $event_id,
                                                            "layer_code" => $layer_code,
                                                            "asset_no" => $asset_no,
                                                            "quantity" => $quantity,
                                                            "remove_date" => $lost_date,
                                                            "asset_status" => $asset_status
                                                        );
                                                        $removeQty = $tbl_m_asset_remove[$layer_code."/".$asset_no];
                                                        $rem_diffQty = $quantity -  $removeQty;

                                                        if($rem_diffQty != 0) $status = "4";#Lost
                                                        else {
                                                            $two[] = $layer_code."/".$asset_no;
                                                            continue;
                                                        }
                                                    }
                                                }
                                                if($status != '' && !empty($one)) {
                                                    $one[$layer_code."/".$asset_no]['status'] = $status;
                                                    array_push($data, array_values($one)[0]);
                                                    $one = [];
                                                }
                                            }
                                            $data_removed[] = array(
                                                "asset_event_id" => $event_id,
                                                "layer_code" => $layer_code,
                                                "asset_no" => $asset_no,
                                                "quantity" => $quantity,
                                                "remove_date" => $lost_date,
                                                "asset_status" => $asset_status
                                            );
                                        }
                                    }
                                    else {
                                        $con_sold = [];
                                        $con_sold = $cond;
                                        // unset($con_sold['flag']);
                                        // $con_sold['asset_no'] = $asset_no;
                                        $con_sold['sold_date'] = $lost_date;
                                        $check_asset_sold = $this->checkDatatoValidate($con_sold, 'AssetSold');
                                        if(empty($check_asset_sold)) {
                                            if(!empty($tbl_m_asset_sold) && $get_ref_id != 0) {
                                                if(empty($tbl_m_asset_sold[$layer_code."/".$asset_no])) {

                                                    $status = "4";#Lost
                                                    #no sold data and have normal data only in ref event
                                                    #have sold only and no have normal cur event
                                                    if(!empty($tbl_m_asset[$layer_code."/".$asset_no]) && empty($one)) {
                                                        $status = "5";#Lost(may be 3)
                                                        $asset_status = "2";
                                                        $diffQty = $quantity;
                                                        $one[$layer_code."/".$asset_no] = array(
                                                            "asset_event_id" => $event_id,
                                                            "layer_code" => $layer_code,
                                                            "layer_name" => $name_jp,
                                                            "asset_no" => $asset_no,
                                                            "asset_name" => $asset_name,
                                                            "quantity" => $quantity,
                                                            "acq_date" => $acq_date,
                                                            "place_code" => $place_code,
                                                            "place_name" => $place_name,
                                                            "2nd_key_code" => $type_code,
                                                            "2nd_key_name" => $type_name,
                                                            "lost_date" => $lost_date,
                                                            "label_no" => $label_no,
                                                            "amount" => $amount,
                                                            "status" => ($status == "")? "1" : $status,
                                                            "diff_qty" => ($status == "1")? "0": $diffQty,
                                                            "flag" => 1,
                                                            "created_by" => $login_id,
                                                            "updated_by" => $login_id,
                                                            "created_date" => $date,
                                                            "updated_date" => $date,
                                                            "asset_status" => ($status == "4") ? "2" : (($status == "5") ? "3" : "1"),
                                                        );
                                                    }
                                                }
                                                else {
                                                    if(in_array($layer_code."/".$asset_no, $three)) {#if rem_diffQty==0, check data with same ba, asset_no
                                                        $status = "4";#Lost
                                                    }else {
                                                        $data_sold[] = array(
                                                            "asset_event_id" => $event_id,
                                                            "layer_code" => $layer_code,
                                                            "asset_no" => $asset_no,
                                                            "quantity" => $quantity,
                                                            "sold_date" => $lost_date,
                                                            "asset_status" => $asset_status
                                                        );
                                                        $soldQty = $tbl_m_asset_sold[$layer_code."/".$asset_no];
                                                        $sold_diffQty = $quantity -  $soldQty;

                                                        if($sold_diffQty != 0) $status = "4";#Lost(may be 3)
                                                        else {
                                                            $three[] = $layer_code."/".$asset_no;
                                                            continue;
                                                        }
                                                    }
                                                }
                                                if($status != '' && !empty($one)) {
                                                    $one[$layer_code."/".$asset_no]['status'] = $status;
                                                    array_push($data, array_values($one)[0]);
                                                    $one = [];
                                                }
                                            }
                                            $data_sold[] = array(
                                                "asset_event_id" => $event_id,
                                                "layer_code" => $layer_code,
                                                "asset_no" => $asset_no,
                                                "quantity" => $quantity,
                                                "sold_date" => $lost_date,
                                                "asset_status" => $asset_status
                                            );
                                        }
                                    }
                                
                                    if(!empty($one)) {
                                        $status = "3";#same of data diff remove/sold qty == 0 or no have next same data

                                        $removed_count = $this->AssetRemove->find('count', array(
                                            'conditions' => array(
                                                'asset_event_id'=>$get_ref_id,
                                                'asset_no'=>$asset_no,
                                                'layer_code'=>$layer_code
                                            )
                                        ));
                                        $sold_count = $this->AssetSold->find('count', array(
                                            'conditions' => array(
                                                'asset_event_id'=>$get_ref_id,
                                                'asset_no'=>$asset_no,
                                                'layer_code'=>$layer_code
                                            )
                                        ));
                                        #remove/sold hmr ta khu hma ma shi diff_qty
                                        if ($removed_count == 0 && $sold_count == 0) {
                                            $status = "1"; #added new for status=New by KHinHninMyo(20210121)
                                        }
                                        $one[$layer_code."/".$asset_no]['status'] = $status;
                                        array_push($data, array_values($one)[0]);
                                        $one = [];
                                    }
                                }else {
                                    #imported ba is not register
                                    if(empty($get_ba)) {
                                        if (!in_array($layer_code, $CheckBAcode, true)) array_push($CheckBAcode, $layer_code);               
                                        $this->Session->write('CheckBAcode', 'CheckBAcode');
                                    }
                                    #flag 4 event(approved)
                                    if(!empty($flag_four)) {
                                        $SkipSameAssetnoflag[] = __("BA").("<b>").$layer_code.("</b>").__(" は、行目で既にビジネスマネージャーによって承認済み ").("<b>").$row.("</b>");
                                        $this->Session->write('SkipSameAssetnoflag', 'SkipSameAssetnoflag');
                                    }
                                }

                            } else {
                                $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);

                                global $ERROR_VALUE;
                                $param_1 = array();
                                $param_1['row']  = '( '.$row.' )';
                                $param_1['col'] = '( '.$ERROR_VALUE.' )';

                                $errorMsg = parent::getErrorMsg('SE023', $param_1);
                                $this->Flash->set($errorMsg, array('key'=>'excelError'));

                                $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                            }
                        }
                        
                        #for only header and no data contain
                        if ($row <= 1) {
                            $errorMsg = parent::getErrorMsg('SE048');
                            $this->Flash->set($errorMsg, array('key'=>'excelError'));

                            #update concurrency_status is 1 to 0 in tbl_event
                            $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);

                            CakeLog::write('debug', ' Data do not contained in file. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                            $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                        }
                        
                        if (!empty($data) || !empty($data_removed) || !empty($data_sold)) {
                            $normalDB = $this->Asset->getDataSource();
                            $removeDB = $this->AssetRemove->getDataSource();
                            $soldDB = $this->AssetSold->getDataSource();
                    
                            try {
                                $normalDB->begin();
                                $removeDB->begin();
                                $soldDB->begin();
                              
                                    
                                $this->Asset->saveAll(($data));
                                $this->AssetRemove->saveAll($data_removed);
                                $this->AssetSold->saveAll($data_sold);

                                $a = count($data);
                                
                                $normalDB->commit();
                                $removeDB->commit();
                                $soldDB->commit();
                            } catch (Exception $e) {
                                $normalDB->rollback();
                                $removeDB->rollback();
                                $soldDB->rollback();

                                $errorMsg = parent::getErrorMsg('SE015');
                                $this->Flash->set($errorMsg, array('key'=>'excelError'));

                                #update concurrency_status is 1 to 0 in tbl_event
                                $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);

                                CakeLog::write('debug', ' cannot saving process into database. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                                $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                            }
                        }
                        #update concurrency_status is 1 to 0 in tbl_event
                        $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);


                        if (!empty($CheckBAcode)) {
                            $this->Session->write('CHECKBACODE', $CheckBAcode);
                        }
                        
                        if (!empty($SkipSameAssetno)) {
                            $this->Session->write('SKIPSAMEASSETNO', $SkipSameAssetno);
                        }
                        
                        if (!empty($SkipSameAssetnoflag)) {
                            $this->Session->write('SKIPSAMEASSETNOFLAG', $SkipSameAssetnoflag);
                        }
                        
                        if (!empty($SkipSameAssetnoExcel)) {
                            $this->Session->write('SKIPSAMEASSETNOEXCEL', $SkipSameAssetnoExcel);
                        }
                                
                        //show no. of saved data

                        if ($a > 0) {
                            $successMsg = parent::getSuccessMsg('SS009', $a);
                            
                            $this->Flash->set($successMsg, array("key"=>"UserSuccess"));

                            $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                        } else {
                            $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                        }

                        fclose($fp);
                    } else {
                        $errorMsg = parent::getErrorMsg('SE022');
                        $this->Flash->set($errorMsg, array('key'=>'excelError'));

                        #update concurrency_status is 1 to 0 in tbl_event
                        $update_concurrency_0 = $this->AssetEvent->Update_Concurrency_0($event_id);

                        CakeLog::write('debug', 'file format Invalid. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                        $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
                    }
                }
            }
        } else {
            $this->redirect(array('controller'=>'AssetImports','action'=>'index'));
        }
    }

    public function checkDatatoCal($ref_id) {
        $models = array('Asset', 'AssetRemove', 'AssetSold');
        $data = array();
        foreach ($models as $key => $model) {
            $asset_status = $key+1;
            $name = "asset_".$asset_status;

            $flag = [];
            if($model == 'Asset') $flag['flag !='] = 0;
            
            $this->$model->virtualFields['keyCols'] = 'CONCAT(layer_code, "/", asset_no)';
            $this->$model->virtualFields['valCols'] = 'quantity';
            
            $name = $this->$model->find('list', array(
                'fields' => array('keyCols','valCols'),
                'conditions' => array(
                    'asset_event_id' => $ref_id,
                    'asset_status' => $asset_status,
                    $flag
                )
            ));
           array_push($data, $name);
        }
        return $data;
    }

    public function checkDatatoValidate($conditions, $model) {
        return $this->$model->find('first', array(
            'conditions' => $conditions
        ));
    }

    public function getFilePointerUTF8($target_file)
    {
        $current_locale = setlocale(LC_ALL, '0'); // Backup current locale.
   
        setlocale(LC_ALL, 'ja_JP.UTF-8');

        // Read the file content in SJIS-Win.
        $content = file_get_contents($target_file);
  
        // Convert file content to SJIS-Win.
        // $enc = mb_detect_encoding($content, mb_list_encodings(), true);
        // if($enc != 'SJIS')
        // $content = mb_convert_encoding($content, "SJIS", $enc);
        $content = mb_convert_encoding($content, "UTF-8", 'SJIS-win');
        // $content = utf8_encode($content);
    
        // Save the file as UTF-8 in a temp location.
        $fp = tmpfile();
        fwrite($fp, $content);
        rewind($fp);

        setlocale(LC_ALL, $current_locale); // Restore the backed-up locale.

        return $fp;
    }

    public function Check_BAcode()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = false;

        $check_bacode = $this->Session->read('CHECKBACODE');
        $this->Session->delete('CHECKBACODE');
        
        echo json_encode($check_bacode);
    }

    public function Skip_SameAssetno()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = false;

        $check_assetno = $this->Session->read('SKIPSAMEASSETNO');
        $this->Session->delete('SKIPSAMEASSETNO');
        
        echo json_encode($check_assetno);
    }
    public function Skip_SameAssetnoflag()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = false;

        $check_assetnoflag = $this->Session->read('SKIPSAMEASSETNOFLAG');
        $this->Session->delete('SKIPSAMEASSETNOFLAG');
        
        echo json_encode($check_assetnoflag);
    }

    public function Skip_SameAssetnoExcel()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = false;

        $check_assetnoexcel = $this->Session->read('SKIPSAMEASSETNOEXCEL');
        $this->Session->delete('SKIPSAMEASSETNOEXCEL');
        
        echo json_encode($check_assetnoexcel);
    }
}
