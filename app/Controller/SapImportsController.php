<?php

App::uses('Controller', 'Controller');
App::import('Vendor', 'php-excel-reader/PHPExcel');
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

define('UPLOAD_FILEPATH', ROOT); //server
define('UPLOAD_PATH', 'app'.DS.'temp');


class SapImportsController extends AppController
{
    public $components = array('PhpExcel.PhpExcel','Flash');
    public $uses = array('Sap','Layer');

    public function beforeFilter()
    {
        parent::checkUserStatus();
        parent::CheckSession();
        parent::checkAccessType();
        parent::checkSapUrlSession('import');

        $Common = New CommonController();

        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        $layers = array_keys($permissions['index']['layers']);

        // if((!in_array($layer_code, $layers)) || ($layer_code=="" && $permissions['index']['limit']>0)) {
        //     $errorMsg = parent::getErrorMsg('SE065');
        //     $this->Flash->set($errorMsg, array("key"=>"Error"));
        //     $this->redirect(array('controller'=>'SapSelections', 'action'=>'index'));
        // }
        
        
    }
    
    public function index()
    {
        $Common = new CommonController();
        $this->layout = 'retentionclaimdebt';
        $errorMsg   = "";
        $successMsg = "";
        $showSaveBtn = false;

        $period = $this->Session->read('SapSelections_PERIOD_DATE');
        
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');

        $data['role_id'] = $role_id;
        $data['period']         = $period;
        $data['layer_code']     = $layer_code;
        $data['page']           = 'SapImports';
        $data['flag_list']      = '';
        $data['modelName']      = 'Sap';
        
        $permissions = $this->Session->read('PERMISSIONS');
        $save_permt = $permissions['save'];
        $save_layer = array_keys($save_permt['layers']);
        if (($layer_code == '' && $save_permt['limit']==0) || in_array($layer_code, $save_layer)) {
            $showSaveBtn = true;
        }

        if (!empty($period)) {
            $BAName = "";
            if ($layer_code != null || $layer_code != '') {
                
                $getBAName = $Common->getLayerThreeName($layer_code,'SapSelections',date("Y-m-d", strtotime($period)));
                $BAName = $getBAName['name_jp'];
            }

            if ($this->Session->check('SKIPNOREGCODEROW')) {
                $this->set('skipNoRegCode', 'skipNoRegCode');
            }

            if ($this->Session->check('SKIPDATAROW')) {
                $this->set('skipDataRow', 'skipDataRow');
            }
            
            $this->set('showSaveBtn', $showSaveBtn);
            $this->set('target_month', $period);
            $this->set('PERIOD', $period);
            $this->set('BA_Code', $layer_code);
            $this->set('BAName', $this->Session->read('SapSelections_BA_NAME'));
            $this->set('successMsg', $successMsg);
            $this->set('errorMsg', $errorMsg);
            $this->render('index');
        } else {
            $this->redirect(array('controller' => 'SapSelections', 'action' => 'index'));
        }
    }

    public function SaveExcelFile()
    {
        $Common = new CommonController();

        if ($this->request->is('post')) {

            //Global Variable for error message
            $ERROR_VALUE = "";
            $errorMsg = "";
            $successMsg = "";
            $role_id = $this->Session->read('ADMIN_LEVEL_ID');
            $user_id = $this->Session->read('LOGIN_ID'); //get login id
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $login_id = $this->Session->read('LOGIN_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $layer_name = $this->Session->read('SapSelections_BA_NAME');
            $period  = $this->Session->read('SapSelections_PERIOD_DATE');
            $period  = date('Y-m-d', strtotime($period));
            $base_date = $this->request->data['refer_date'];
            $deadLine_date = $this->request->data['submission_date'];
            $empty_date = '0000-00-00';
            $file = $this->request->params['form']['uploadfile'];
            $uploadPath = APP . 'tmp'; // file path
            
            $data = array();
            $dataError = array();
            $skipNoRegCode = array();
            $skipDataRow = array();
            $header = true;
            $start_col = 'A';
            $end_col = 'T';
            $header_list = array('部署','勘定科目コード','勘定科目名','相手先コード','相手先名','品目名','INDEX NO','数量','単価','通貨','外貨','円貨','滞留日数','転記日','計上基準日','入出荷年月日','決済予定日','満期年月日','明細テキスト','営業担当');
            if (!empty($file)) {
                if ($file['error'] == 0) {
                    if ($file['size'] <= 10485760) { //10 Megabytes (MB)

                        $file_name  = $file['name'];
                        $file_type  = $file['type'];
                        $file_loc   = $file['tmp_name'];
                        $file_size  = $file['size'];

                        $file_type =  mb_convert_encoding($file_type, "SJIS", "UTF-8");
                        $file_loc  =  mb_convert_encoding($file_loc, "SJIS", "UTF-8");
                        $file_size =  mb_convert_encoding($file_size, "SJIS", "UTF-8");

                        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                        $fileName = "temporary.".$ext;
                        $tempPath = $uploadPath .DS. $fileName;

                        /** save under tmp/ **/
                        if (move_uploaded_file($file_loc, $tempPath)) {
                            if ($ext =="xlsx" || $ext == "xls") {
                                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                                $objReader->setReadDataOnly(true);

                                if ($objReader->canRead($tempPath)) {
                                    $objPHPExcel   = $objReader->load($tempPath);
                                    $objWorksheet  = $objPHPExcel->getActiveSheet();
                                    $highestRow    = $objWorksheet->getHighestRow();
                                    $highestColumn = $objWorksheet->getHighestColumn();

                                    if ($highestRow == 1) {
                                        $highestRow = $highestRow +1;
                                    }
                                    
                                    for ($row = 1; $row <= $highestRow; $row++) {
                                        $rowData = $objWorksheet->rangeToArray($start_col . $row . ':' . $end_col . $row, null, true, false);
                                        $worksheets[] = $rowData;
                                    }
                                    #check header
                                    $header_excel = $worksheets[0][0];
                                    for ($j=0; $j<23; $j++) {
                                        if($header_list[$j] != ltrim($header_excel[$j])) {
                                            if(@ file_get_contents($tempPath)) unlink($tempPath);
                                            $errorMsg = parent::getErrorMsg('SE022');
                                            $this->Flash->set($errorMsg, array('key'=>'excelError'));
                                            $this->redirect(array('controller'=>'SapImports','action'=>'index'));
                                        }
                                    }
                                    $i = 0;
                                    $data = array();
                                    for ($row = 3; $row <= $highestRow; ++$row) {
                                        $layer_code = $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();#A
                                        $layer_code_true = true;
                                        if (!empty($layer_code)) {
                                            $layer_code_length = mb_strlen(trim($layer_code));
                                            if ($layer_code_length > '6') {
                                                $layer_code_true = false;
                                                $this->writeDebugLog(' Invalid layer_code format error occur. '.$layer_code.' will be string length <=6, at col A and row '.$row, __LINE__);
                                            }
                                        }else {
                                            $this->writeDebugLog(' Layer code is empty, at col A and row '.$row, __LINE__);
                                            $errorMsg = parent::getErrorMsg('SE024');
                                            $this->Flash->set($errorMsg, array('key'=>'noSlipState'));
                                            $this->redirect(array('controller'=>'SapImports','action'=>'index'));  
                                        }

                                        $account_code = $objWorksheet->getCellByColumnAndRow(1, $row)->getValue();#B
                                        $account_code_true = true;
                                        if (!empty($account_code)) {
                                            $account_codeLength = mb_strlen(trim($account_code));
                                            if ($account_codeLength > '10') {
                                                $account_code_true = false;
                                                $this->writeDebugLog(' Invalid account_code format error occur '.$account_code.' will be string length <=10 at col B and row '.$row, __LINE__);
                                            }
                                        }
                                        
                                        $account_name = $objWorksheet->getCellByColumnAndRow(2, $row)->getValue();#C
                                        $account_name_true = true;
                                        if (!empty($account_name)) {
                                            $account_nameLength = mb_strlen(trim($account_name));
                                            if ($account_nameLength > '500') {
                                                $account_name_true = false;
                                                $this->writeDebugLog(' Invalid account_name format error occur.'.$account_name.' will be string length <= 500, at col C and row '.$row, __LINE__);
                                            }
                                        }

                                        $destination_code = $objWorksheet->getCellByColumnAndRow(3, $row)->getValue();#D
                                        $destination_code_true = true;
                                        if (!empty($destination_code)) {
                                            $destination_codeLength = mb_strlen(trim($destination_code));
                                            if ($destination_codeLength > '10') {
                                                $destination_code_true = false;
                                                $this->writeDebugLog(' Invalid destination_code format error occur '.$destination_code.' will be string length <=10, at col D and row '.$row, __LINE__);
                                            }
                                        }

                                        $destination_name = $objWorksheet->getCellByColumnAndRow(4, $row)->getValue();#E
                                        $destination_name_true = true;
                                        if (!empty($destination_name)) {
                                            $destination_nameLength = mb_strlen(trim($destination_name));
                                            if ($destination_nameLength > '500') {
                                                $destination_name_true = false;
                                                $this->writeDebugLog(' Invalid destination_name format error occur '.$destination_name.' will be string length <= 500, at col E and row '.$row, __LINE__);
                                            }
                                        }

                                        $item_name = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();#F

                                        $logistic_index_no = $objWorksheet->getCellByColumnAndRow(6, $row)->getValue();#G
                                        $logistic_index_true = true;
                                        if (!empty($logistic_index_no)) {
                                            $logistic_indexLength = mb_strlen(trim($logistic_index_no));
                                            if ($logistic_indexLength > '20') {
                                                $logistic_index_true = false;
                                                $this->writeDebugLog(' Invalid logistic_index_no format error occur '.$logistic_index_no.' will be string length <= 20, at col G and row '.$row.' length '.$logistic_indexLength, __LINE__);
                                            }
                                        }

                                        $quantity = $objWorksheet->getCellByColumnAndRow(7, $row)->getValue();#H
                                        $qty = true;
                                        if (!empty($quantity)) {
                                            $removeComma = str_replace(',', '', $quantity);
                                            if (!preg_match('/^[0-9]+$/', $removeComma)) {
                                                $removePoint = (int)$removeComma;
                                                $removePoint = strlen((string)$removePoint);
                                                if ($removePoint > '12') {
                                                    $qty = false;
                                                    $this->writeDebugLog(' Invalid quantity format error occur. '.$quantity.' decimal(16,4), at col H and row '.$row, __LINE__);
                                                }elseif (!preg_match('/^[0-9]+\.[0-9]{1,4}$/', $removeComma)) {
                                                    $qty = false;
                                                    if (is_numeric($quantity)) {
                                                        $this->writeDebugLog(' Invalid quantity format error occur. '.number_format($quantity, 4, '.', '').' will be decimal(16,4), at col H and row '.$row, __LINE__);
                                                    } else {
                                                        $this->writeDebugLog(' Invalid quantity format error occur. '.$quantity.' will be decimal(16,4), at col H and row '.$row, __LINE__);
                                                    }
                                                }
                                            }
                                        }

                                        $unit_price = $objWorksheet->getCellByColumnAndRow(8, $row)->getValue();#I
                                        $unit_pric = true;
                                        if (!empty($unit_price)) {
                                            $removeComma = str_replace(',', '', $unit_price);
                                            if (!preg_match('/^[0-9]+$/', $removeComma)) {
                                                $removePoint = (int)$removeComma;
                                                $removePoint = strlen((string)$removePoint);
                                                if ($removePoint > '14') {
                                                    $unit_pric = false;
                                                    $this->writeDebugLog(' Invalid unit price format error occur. '.$unit_price.' will be decimal(17,3), at col I and row '.$row, __LINE__);
                                                }elseif (!preg_match('/^[0-9]+\.[0-9]{1,3}$/', $removeComma)) {
                                                    $unit_pric = false;
                                                    if (is_numeric($unit_price)) {
                                                        $this->writeDebugLog(' Invalid unit price format error occur. '.number_format($unit_price, 3, '.', '').' will be decimal(17,3), at col I and row '.$row, __LINE__);
                                                    } else {
                                                        $this->writeDebugLog(' Invalid unit price format error occur. '.number_format($unit_price, 3, '.', '').' will be decimal(17,3), at col I and row '.$row, __LINE__);
                                                    }
                                                }
                                            }
                                        }

                                        $currency = $objWorksheet->getCellByColumnAndRow(9, $row)->getValue();#J

                                        $foreign_currency = $objWorksheet->getCellByColumnAndRow(10, $row)->getValue();#K

                                        $jp_amount = $objWorksheet->getCellByColumnAndRow(11, $row)->getValue();#L
                                        $jp_amt = true;
                                        if (!empty($jp_amount)) {
                                            if (is_numeric($jp_amount)) {
                                                $jp_amt_Len = mb_strlen(trim($jp_amount));
                                                $removeComma = str_replace(',', '', $jp_amount);
                                                if ($jp_amt_Len > 14) {
                                                    $jp_amt = false;
                                                    $this->writeDebugLog(' Invalid jp_amount format error occur. '.$currency.' will be interger length <=14, at col L and row '.$row, __LINE__);
                                                }elseif (!preg_match("/^-?[0-9]+$/", $removeComma)) {
                                                    $jp_amt = false;
                                                    $this->writeDebugLog(' Invalid jp_amount format error occur. '.$currency.' will be interger length <=14, at col L and row '.$row, __LINE__);
                                                }
                                            } else {
                                                $jp_amt = false;
                                                $this->writeDebugLog(' Invalid jp_amount format error occur. '.$currency.' will be interger length <=14, at col L and row '.$row, __LINE__);
                                            }
                                        }

                                        $numbers_day = $objWorksheet->getCellByColumnAndRow(12, $row)->getValue();#M
                                        $numbers = true;
                                        if (!empty($numbers_day)) {
                                            $numbers = $this->validateInterger($numbers_day, 'M', $row);
                                        }

                                        $posting_date = \PHPExcel_Style_NumberFormat::toFormattedString($objWorksheet->getCellByColumnAndRow(13, $row)->getValue(), 'DD-MM-YYYY');#N
                                        $posting = true;
                                        if (!empty($posting_date)) {
                                            $posting = $this->validateDate($posting_date, 'N(posting_date)', $row);
                                            $posting_date = date('Y-m-d', strtotime($posting_date));
                                        }

                                        $recorded_date = \PHPExcel_Style_NumberFormat::toFormattedString($objWorksheet->getCellByColumnAndRow(14, $row)->getValue(), 'DD-MM-YYYY');#O
                                        $recorded = true;
                                        if (!empty($recorded_date)) {
                                            $recorded = $this->validateDate($recorded_date, 'O(recorded_date)', $row);
                                            $recorded_date = date('Y-m-d', strtotime($recorded_date));
                                        }

                                        $receipt_shipment_date = \PHPExcel_Style_NumberFormat::toFormattedString($objWorksheet->getCellByColumnAndRow(15, $row)->getValue(), 'DD-MM-YYYY');#P
                                        $r_s_date = true;
                                        if (!empty($receipt_shipment_date)) {
                                            $r_s_date = $this->validateDate($receipt_shipment_date, 'P(receipt_shipment_date)', $row);
                                            $receipt_shipment_date = date('Y-m-d', strtotime($receipt_shipment_date));
                                        }

                                        $schedule_date = \PHPExcel_Style_NumberFormat::toFormattedString($objWorksheet->getCellByColumnAndRow(16, $row)->getValue(), 'DD-MM-YYYY');#Q
                                        $schedule = true;
                                        if (!empty($schedule_date)) {
                                            $schedule = $this->validateDate($schedule_date, 'Q(schedule_date)', $row);
                                            $schedule_date = date('Y-m-d', strtotime($schedule_date));
                                        }

                                        $maturity_date = \PHPExcel_Style_NumberFormat::toFormattedString($objWorksheet->getCellByColumnAndRow(17, $row)->getValue(), 'DD-MM-YYYY');#R
                                        $maturity = true;
                                        if (!empty($maturity_date)) {
                                            $maturity = $this->validateDate($maturity_date, 'Z(maturity_date)', $row);
                                            $maturity_date = date('Y-m-d', strtotime($maturity_date));
                                        }

                                        $line_item_text = $objWorksheet->getCellByColumnAndRow(18, $row)->getValue();#S
                                        $sale_representative = $objWorksheet->getCellByColumnAndRow(19, $row)->getValue();#T
                                        #show error message
                                        global $ERROR_VALUE;
                                        if(!$layer_code_true) $ERROR_VALUE = "A"; 
                                        elseif(!$account_code_true) $ERROR_VALUE = "B"; 
                                        elseif(!$account_name_true) $ERROR_VALUE = "C"; 
                                        elseif(!$destination_code_true) $ERROR_VALUE = "D"; 
                                        elseif(!$destination_name_true) $ERROR_VALUE = "E"; 
                                        elseif(!$logistic_index_true) $ERROR_VALUE = "G"; 
                                        elseif(!$qty) $ERROR_VALUE = "H"; 
                                        elseif(!$unit_pric) $ERROR_VALUE = "I"; 
                                        elseif(!$jp_amt) $ERROR_VALUE = "L"; 
                                        elseif(!$numbers) $ERROR_VALUE = "M"; 
                                        elseif(!$posting) $ERROR_VALUE = "N"; 
                                        elseif(!$recorded) $ERROR_VALUE = "O"; 
                                        elseif(!$r_s_date) $ERROR_VALUE = "P"; 
                                        elseif(!$schedule) $ERROR_VALUE = "Q"; 
                                        elseif(!$maturity) $ERROR_VALUE = "R";
                                        
                                        if($ERROR_VALUE == "") {
                                            $IsHascode = $Common->getLayerThreeName($layer_code,'SapSelections',$period);
                                            
                                            if(empty($IsHascode)) {
                                                #to insert in Layer
                                                if (!in_array($layer_code, $skipNoRegCode, true)) {
                                                    array_push($skipNoRegCode, $layer_code);
                                                }
                                            }else {
                                                $layer_flag = $this->Sap->find('count', array(
                                                    'conditions' => array(
                                                        'Sap.layer_code' => $layer_code,
                                                        'Sap.flag >=' => '5',
                                                        "Sap.period" => $period
                                                    )
                                                ));
                                                if($layer_flag < 1) {
                                                    $i++;
                                                    $date = date('Y-m-d H:i:s');
                                                    $data[] = array(
                                                        "period" => $period,
                                                        "base_date" => $base_date,
                                                        "deadline_date" => $deadLine_date,
                                                        "layer_code" => $layer_code,
                                                        "account_code" => $account_code,
                                                        "account_name" => $account_name,
                                                        "destination_code" => $destination_code,
                                                        "destination_name" => $destination_name,
                                                        "item_name" => $item_name,
                                                        "logistic_index_no" => $logistic_index_no,
                                                        "quantity" => $quantity,
                                                        "unit_price" => $unit_price,
                                                        "currency" => $currency,
                                                        "foreign_amount" => $foreign_currency,
                                                        "jp_amount" => $jp_amount,
                                                        "numbers_day" => $numbers_day,
                                                        "posting_date" => (!empty($posting_date))? $posting_date : $empty_date,
                                                        "recorded_date" => (!empty($recorded_date))? $recorded_date : $empty_date,
                                                        "receipt_shipment_date" => (!empty($receipt_shipment_date))? $receipt_shipment_date : $empty_date,
                                                        "schedule_date" => (!empty($schedule_date))? $schedule_date : $empty_date,
                                                        "maturity_date" => $maturity_date,
                                                        "line_item_text" => $line_item_text,
                                                        "sale_representative" => $sale_representative,
                                                        "flag" => 1,
                                                        "created_by" => $user_id,
                                                        "updated_by" => $user_id,
                                                        "created_date" => $date,
                                                        "updated_date" => $date
                                                    );
                                                }else {
                                                    $skipDataRow[] = __("行目で部長によってこのコードを承認済み ").$row;
                                                }
                                            }                                            
                                        }else {
                                            $this->showError($tempPath, $row);
                                        }
                                    }
                                    #show warning message with confirm box
                                    if(!empty($skipNoRegCode)) {
                                        $this->Session->write('SKIPNOREGCODEROW', $skipNoRegCode);
                                    }
                                    if(!empty($skipDataRow)) {
                                        $this->Session->write('SKIPDATAROW', $skipDataRow);
                                    }
                                    
                                    if (!empty($data)) {
                                        $attachDB = $this->Sap->getDataSource();
                                        try {
                                            if($_POST['mailSend']) {
                                                $mail_template = 'common';
                                                $mail['subject']        = $_POST['mailSubj'];
                                                $mail['template_title'] = $_POST['mailTitle'];
                                                $mail['template_body']  = $_POST['mailBody'];
                                                $to_email  = $_POST['toEmail'];
                                                $cc_email  = $_POST['ccEmail']; 
                                                $bcc_email = $_POST['bccEmail']; 
                                                $url = '';
                                                $toEmail = parent::formatMailInput($to_email);
                                                $ccEmail = parent::formatMailInput($cc_email);
                                                $bccEmail = parent::formatMailInput($bcc_email);
                                                
                                                $sendMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                                
                                                if ($sendMail["error"]) {
                                                    $msg = $sendMail["errormsg"];
                                                    $this->Flash->set($msg, array('key'=>'excelError'));
                                                    $invalid_email = parent::getErrorMsg('SE042');
                                                    $this->Flash->set($invalid_email, array('key'=>'excelError'));
                                                } else {
                                                    $this->Sap->saveAll($data);
                                                    $attachDB->commit();
                                                    $attachDB->begin();
                                                    $successMsg = parent::getSuccessMsg('SS009', $i);
                                                    $this->Flash->set($successMsg, array('key'=>'success'));

                                                    $successMsg = parent::getSuccessMsg("SS018");
                                                    $this->Flash->set($successMsg, array('key'=>'success'));
                                                } 
                                            }else{

                                                $this->Sap->saveAll($data);
                                                $attachDB->commit();
                                                $attachDB->begin();
                                                $successMsg = parent::getSuccessMsg('SS009', $i);
                                                $this->Flash->set($successMsg, array('key'=>'success'));
                                            }
                                            
                                        } catch (Exception $e) {
                                            $attachDB->rollback();
                                            $this->writeDebugLog($e->getMessage().' in file ', __LINE__);
                                            if (@ file_get_contents($tempPath)) {
                                                unlink($tempPath);
                                            }
                                            $errorMsg = parent::getErrorMsg('SE015');
                                            $this->Flash->set($errorMsg, array('key'=>'excelError'));
                                        }
                                    }elseif(!$this->Session->check('SKIPNOREGCODEROW') && !$this->Session->check('SKIPDATAROW')) {
                                        #for only header and no data contain
                                        $this->writeDebugLog(' Data do not contained in file. ', __LINE__);
                                        $errorMsg = parent::getErrorMsg('SE048');
                                        $this->Flash->set($errorMsg, array('key'=>'excelError'));
                                        $this->redirect(array('controller'=>'SapImports','action'=>'index'));
                                    }
                                } else {
                                    $this->writeDebugLog('cannot upload file under tmp', __LINE__);
                                    $errorMsg = parent::getErrorMsg('SE015');
                                    $this->Flash->set($errorMsg, array('key'=>'excelError'));
                                }
                            } else {
                                if (@ file_get_contents($tempPath)) {
                                    unlink($tempPath);
                                }
                                
                                $errorMsg = parent::getErrorMsg("SE013", $ext);
                                $this->Flash->set($errorMsg, array('key'=>'excelError'));
                            }
                        } else {
                            $this->writeDebugLog('cannot upload file under tmp.', __LINE__);
                            $errorMsg = parent::getErrorMsg('SE015');
                            $this->Flash->set($errorMsg, array('key'=>'excelError'));
                        }
                    } else {
                        CakeLog::write('debug', 'file size over 10MB. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                        $errorMsg = parent::getErrorMsg('SE020');
                        $this->Flash->set($errorMsg, array('key'=>'excelError'));
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE015');
                    $this->Flash->set($errorMsg, array('key'=>'excelError'));
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE015');
                $this->Flash->set($errorMsg, array('key'=>'excelError'));
            }
            
        }
        $this->redirect(array('controller'=>'SapImports','action'=>'index'));
    }

    public function showError($tempPath, $row) {
        if (@ file_get_contents($tempPath)) {
            unlink($tempPath);
        }

        global $ERROR_VALUE;
        $param_1 = array();
        $param_1['row']  = $row;
        $param_1['col'] = $ERROR_VALUE;

        $errorMsg = parent::getErrorMsg('SE023', $param_1);
        $this->Flash->set($errorMsg, array('key'=>'noSlipState'));
        $this->redirect(array('controller'=>'SapImports','action'=>'index'));
    }

    public function writeDebugLog($err_txt, $line_no) {
        CakeLog::write('debug', $err_txt.$row.'. In file '. __FILE__ . ' on line ' . $line_no . ' within the class ' . get_class());

    }

    public function validateDate($date, $col, $row, $format = 'd-m-Y')
    {
        global $ERROR_VALUE;
        $d = DateTime::createFromFormat($format, $date);
        if ($d) {
            return true;
        } else {
            $this->writeDebugLog(' Invalid Date format error occur '.$date.' will be "Date Format (DD-MM-YYYY)", at col '.$col.' and row '.$row, __LINE__);
            $ERROR_VALUE = $col;
            return false;
        }
    }

    public function validateInterger($integ, $col, $row)
    {
        global $ERROR_VALUE;
        $removeComma = str_replace(',', '', $integ);
        if (preg_match('/^[0-9]+$/', $removeComma)) {
            return true;
        } else {
            $this->writeDebugLog(' Invalid Integer format error occur '.$integ.' will be "Integer Format[0-9]", at col '.$col.' and row '.$row, __LINE__);
            $ERROR_VALUE = $col;
            return false;
        }
    }

    public function inform_skipNoRegCode()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = false;

        $inform_bacode = $this->Session->read('SKIPNOREGCODEROW');
        $this->Session->delete('SKIPNOREGCODEROW');
        
        echo json_encode($inform_bacode);
    }
    
    public function inform_skipDataRow()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = false;

        $inform_slicline = $this->Session->read('SKIPDATAROW');
        $this->Session->delete('SKIPDATAROW');
        
        echo json_encode($inform_slicline);
    }
    #data pass from view ajax to controller to get mail data
    public function getMailLists() {
        #only allow ajax request
        $language = $this->Session->read('Config.language');
        parent::checkAjaxRequest($this);
        $Common     = New CommonController();
        $period     = $this->Session->read('SapSelections_PERIOD_DATE');
        $layer_name    = $this->Session->read('SapSelections_BA_NAME');
        $layer_code    = $_POST['layer_code']; //($_POST['layer_code'])? $_POST['layer_code'] : '';
        $page       = $_POST['page'];
        $function   = $_POST['function'];
        
        $mails = $Common->getMailList($layer_code, $page, $function, $language, $layer_name, $period, Setting::LAYER_SETTING['SapSelections']);
       
        return json_encode($mails);
    }
}
