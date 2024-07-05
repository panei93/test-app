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

include_once 'Component/Enum.php';
include_once 'Component/AdminLevel.php';
include_once 'Component/AccountGroup.php';
include_once 'Component/question.php';
include_once 'Component/CloudStorageInfo.php';
include_once 'Component/BackupFormInfo.php';
include_once 'Component/BusinessInchargeReason.php';
include_once 'Component/Email.php';
include_once 'Component/TableOrder.php';
include_once 'Component/Paging.php';
include_once 'Component/Setting.php';
include_once 'Component/SSOInfo.php';
include_once 'Component/PositionType.php';

App::uses('Controller', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('CakeEmail', 'Network/Email');


# Imports the Google Cloud client library
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\Exception\GoogleException;
use Zend\Http\PhpEnvironment\Request;
use Magium\ActiveDirectory\ActiveDirectory;
use Magium\Configuration\Config\Repository\ArrayConfigurationRepository;
use Zend\Psr7Bridge\Psr7ServerRequest;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    public $uses = array('User', 'Layer', 'Layers', 'Layer', 'AssetEvent', 'PasswordHistory');
    public $components = array('Session', 'Flash', 'RequestHandler');

    protected $settingMenuArrays = array(
        "Users", "LayerTypes", "Layers",
        "LayerChart", "Roles", "MailFlowSettings",
        "Accounts", "AccountSettings", "Position",
        "Rexchanges", "Rtaxfees"
    );

    /**
     *
     * @author Thura Moe
     *
     */

    public function beforeRender()
    {
        $this->response->disableCache(); //don't show previous page after logout
        if ($this->Session->check('Config.language')) {
            Configure::write('Config.language', $this->Session->read('Config.language'));
        } else {
            Configure::write('Config.language', 'jpn');
        }
    }

    /**
     *
     * Config for SSO OAuth
     * @author Hein Htet Ko
     * @return ad_object
     */

    public function configSSO()
    {
        $config = [
            'authentication' => [
                'ad' => [
                    'client_id' => SSOInfo::client_id,
                    'client_secret' => SSOInfo::client_secret,
                    'enabled' => '1',
                    'directory' => 'common',
                    'return_url' => SSOInfo::return_url
                ]
            ]
        ];

        $request = new Request();
        $ad = new ActiveDirectory(
            new ArrayConfigurationRepository($config),
            Psr7ServerRequest::fromZend($request)
        );
        return $ad;
    }

    /**
     * Change System Language File
     *
     * @author
     *
     * @param LanguageName
     * @return null
     */
    public function changeSystemLanguage($language)
    {
        if ($language == 'jpn') {
            $this->Session->write('Config.language', 'jpn');
        } else {
            $this->Session->write('Config.language', 'eng');
        }
        Configure::write('Config.language', $this->Session->read('Config.language'));
    }

    /**
     * Change Language Action with ajax
     *
     * @author
     *
     * @param language
     * @return json
     */
    public function changeLanguage($language = null)
    {
        $this->autoRender = false;
        $this->request->allowMethod('ajax');
        #only allow ajax request
        $this->checkAjaxRequest($this);
        $this->changeSystemLanguage($this->request->data['language']);

        $data = array(
            'content' => $this->Session->read('Config.language'),
            'error' => ''
        );
        return json_encode($data);
    }

    /**
     * check login user is already delete or not
     *
     * @author - Thura Moe
     *
     */
    public function checkUserStatus()
    {
        $id = $this->Session->read('LOGIN_ID');

        $find = $this->User->find('all', array(
            'conditions' => array(
                'User.id' => $id,
                'User.flag' => 1
            )
        ));


        if (empty($find)) {
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $this->Session->write('LINK', $actual_link);

            $this->redirect(array('controller' => 'Logins', 'action' => 'index'));
        }
    }


    /**
     * Check period and layer_code session for sample check menu
     *
     * @author - Thura Moe
     *
     */
    public function sampleCheckSession()
    {
        $period = $this->Session->check('SAMPLECHECK_PERIOD_DATE');
        $layer_code = $this->Session->check('SESSION_LAYER_CODE');
        $layer_name = $this->Session->check('SAMPLECHECK_BA_NAME');
        if (($period == false) || ($layer_code == false) || ($layer_name == false)) {
            $this->redirect(array('controller' => 'SampleSelections', 'action' => 'index'));
        }
    }

    /**
     * Check BA Code for sample check menu error message show in menu page.
     *
     * @author - Sandi khaing 30.9.2019
     *
     */
    public function baCodeCheckSession()
    {
        $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        if ($layer_code == false) {
            $msg = $this->getErrorMsg("SE044");
            $this->Flash->set($msg, array('key' => 'Error'));
            $this->redirect(array('controller' => 'sampleCheckMenu', 'action' => 'index'));
        }
    }

    /**
     * Check period and layer_code session for debt menu
     *
     * @author - Thura Moe
     *
     */
    public function SapSelectionsSession()
    {
        $period = $this->Session->check('SapSelections_PERIOD_DATE');
        $layer_code = $this->Session->check('SESSION_LAYER_CODE');
        $layer_name = $this->Session->check('SapSelections_BA_NAME');
        if (($period == false) || ($layer_code == false) || ($layer_name == false)) {
            $this->redirect(array('controller' => 'SapSelections', 'action' => 'index'));
        }
    }

    /**
     * Check period session for debt menu
     *
     * @author - Thura Moe
     *
     */
    public function SapSelectionsCheckPeriod()
    {
        $period = $this->Session->check('SapSelections_PERIOD_DATE');
        if ($period == false) {
            $this->redirect(array('controller' => 'SapSelections', 'action' => 'index'));
        }
    }


    /**
     * Check Session Data
     *
     * @author - Aye Thandar Lwin
     *
     */
    public function CheckSession()
    {
        $role_id = $this->Session->check('ADMIN_LEVEL_ID');

        if (!$role_id) {
            $this->redirect(array('controller' => 'Logins', 'action' => 'index'));
            // $this->redirect(array('controller' => 'Logins', 'action' => 'ssoLogin'));
        }
    }
    /**
     * Check Session Data
     *
     * @author - Aye Zar Ni Kyaw
     *
     */
    public function CheckPhase4EventName()
    {
        if ($this->request->query('eventId')) {
            $this->Session->write('PHASE4_EVENT_ID', $this->request->query('eventId'));
        }
        $event_id = $this->Session->read('PHASE4_EVENT_ID');

        if (empty($event_id)) {
            $errorMsg = $this->getErrorMsg('SE072', __('イベント名'));
            $this->Flash->set($errorMsg, array("key" => "Error"));
            $this->redirect(array('controller' => 'BSelectionFinancial', 'action' => 'index'));
        }
    }

    /**
     * Check Session Data of Assets
     * Check event is active or inactive
     * @author - Thura Moe
     *
     */
    public function CheckFixedAssetSelection()
    {
        $event = $this->Session->check('EVENT_NAME');
        $event_id = $this->Session->read('EVENT_ID');
        //query straing add in event_id
        if (empty($event_id)) {
        }
        $status = $this->AssetEvent->find('all', array(
            'conditions' => array(
                'id' => $event_id,
                'flag' => 1
            )
        ));
        if ($event == false || empty($status)) {
            $this->redirect(array('controller' => 'AssetSelections', 'action' => 'index'));
        }
    }

    /**
     * Check Session of phase 3
     *
     * @author - Khin Hnin Myo
     *
     */
    public function CheckAdminSession()
    {

        # PanEiPhyo (20200424), for permission check
        $permission = $this->Session->read('PERMISSION');
        $current_controller = $this->request->params['controller'];
        $current_method = $this->request->params['action'];

        $db_action = $this->getDbActionName($current_method);

        switch ($current_controller) {
            case 'ForecastBudgetDifference':
                $current_controller = 'BudgetingSystem';
                break;
        }

        if ($permission[$current_controller . $db_action] != 1) {
            $errorMsg = $this->getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key" => "TermError"));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }
        # End PanEiPhyo (20200424)

        // $admin_level = $this->Session->read('ADMIN_LEVEL_ID');

        // if(($admin_level != AdminLevel::ADMIN) && ($admin_level != AdminLevel::ACCOUNT_MANAGER) && ($admin_level != AdminLevel::ACCOUNT_SECTION_MANAGER) && ($admin_level != AdminLevel::ACCOUNT_INCHARGE) && ($admin_level != AdminLevel::BUSINESS_MANAGER) && ($admin_level != AdminLevel::BUSINESS_ADMINISTRATIOR) && ($admin_level != AdminLevel::DEPUTY_GENERAL_MANAGER) && ($admin_level != AdminLevel::GENERAL_MANAGER) && ($admin_level != AdminLevel::BUDGET_INCHARGE) && ($admin_level != AdminLevel::BUDGET_MANAGER) && ($admin_level != AdminLevel::BUDGET_PRESIDENT) && ($admin_level != AdminLevel::BUDGET_CHIEF_OFFICER) && ($admin_level != AdminLevel::BUDGET_AUDIT) && ($admin_level != AdminLevel::BUDGET_BOARD_MEMBER) && ($admin_level != AdminLevel::BUDGET_MANAGING_DIRECTOR)) {

        // 		$this->redirect(array('controller' => 'Logins', 'action' => 'logout'));
        // }
    }


    /**
     * Connect to google cloud storage
     * @author - Thura Moe
     * @return storage, bucketName
     */
    public function connect_to_google_cloud_storage()
    {
        # Your Google Cloud Platform project ID
        $projectId = CloudStorageInfo::PROJECT_ID;
        $keyFilePath = APP . CloudStorageInfo::KEY_FILE_PATH;

        # Instantiates a client
        $storage = new StorageClient([
            'projectId' => $projectId,
            'keyFilePath' => $keyFilePath
        ]);

        # The name of the bucket
        $bucketName = CloudStorageInfo::BUCKET_NAME;

        return array($storage, $bucketName);
    }

    /**
     * Get db action name by methods
     *
     * @author - PanEiPhyo (20200424)
     *
     */
    public function getDbActionName($method)
    {
        switch ($method) {
            case 'index':
                return 'Read';
                break;

            default:
                return 'Read';
                break;
        }
    }

    public function checkAccessTypeTest()
    {
        $id = $this->Session->read('LOGIN_ID');
        $find = $this->User->find('all', array(
            'conditions' => array(
                'id' => $id,
                'flag' => 1,
                'access_type' => array(1, 3)
            )
        ));
        if (empty($find)) {
            $this->redirect(array('controller' => 'Menu', 'action' => 'index'));
        }
    }

    public function CheckOnlyAdminSession()
    {
        if ($this->Session->check('ADMIN_LEVEL_ID')) {
            $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        }
        if ($role_id != '1') {
            $this->redirect(array('controller' => 'BSelectionFinancial', 'action' => 'index'));
        }
    }
    /**
     * Common Success Message Arrays
     *
     * @author
     * Date: 20/02/2019
     *
     */
    private static $successMsg = array(
        "SS001" => "Data has been saved successfully!",
        "SS002" => "Data is Updated Successfully!",
        "SS003" => "Data  has been deleted successfully!",
        "SS004" => "Total Row : %s row(s).",
        "SS005" => "All of Data Approve Successfully!",
        "SS006" => "Approve is successfully canceled!",
        "SS007" => "File Upload Successfully!",
        "SS008" => "Your request was successfully submitted!",
        "SS009" => "%s rows is successfully saved!",
        "SS010" => "Data selection is success!",
        "SS011" => "%s Year %s Standard Last Day,Claims Remaining of %s Year %s",
        "SS012" => "Within 30 days and 60 days",
        "SS013" => "More than 60 days",
        "SS014" => "All data have been successfully reverted!",
        "SS015" => "Files have been successfully uploaded: %s pictures!",
        "SS016" => " %s is deactivated!",
        "SS017" => " %s is activated!",
        "SS018" => "Email has been sent successfully!",
        "SS019" => "%s of data are successfully saved!",
        "SS020" => "%s of data are successfully requested!",
        "SS021" => "%s of data are successfully rejected!",
        "SS022" => "%s of data are successfully Approved!",
        "SS023" => "%s of data are successfully Approve canceled!",
        "SS024" => "Your request was successfully Canceled!",
        "SS025" => "%s has been copied successfully!",
        "SS026" => "%s tables has been saved successfully!",
        "SS027" => "Data has been successfully backup!",
        "SS028" => "File has been successfully deleted!",
        "SS029" => "The email notification of completed user registration has been sent successfully!",
        "SS030" => " Your password will expire within %s days. You need to reset your password.",
        "SS031" => "File has been downloaded successfully!",
        "SS032" => "Data overwrite successfully.",
        "SS033" => "Data has been successfully completed!",
        "SS034" => "Data has been successfully cancel completed!",
        "SS035" => "Data has been successfully confirmed!",
        "SS036" => "Data has been successfully cancel confirmed!",
    );
    private static $successMsgJPN = array(
        "SS001" => "正常に保存されました！",
        "SS002" => "正常に更新されました！",
        "SS003" => "正常に削除されました！",
        "SS004" => "総行 : %s 行 ",
        "SS005" => "正常に全ての行が承認されました！",
        "SS006" => "正常にキャンセルの承認がされました！",
        "SS007" => "正常にファイルがアップロードされました！",
        "SS008" => "依頼成功しました！",
        "SS009" => "%s 行を保存しました！",
        "SS010" => "データ選択は成功しました！",
        "SS011" => "%s 年 %s 月末日基準、%s 年 %s 月末日時点債権残",
        "SS012" => "30日超60日以内",
        "SS013" => "60日超",
        "SS014" => "すべてのデータが正常に元に戻されました！",
        "SS015" => "ファイルが正常にアップロードされました: %s 枚の画像",
        "SS016" => " %s は非アクティブ化されています！",
        "SS017" => " %s がアクティブになりました！",
        "SS018" => "メールが正常に送信されました！",
        "SS019" => "%s のデータが正常に保存されました！",
        "SS020" => "%s のデータが正常にリクエストされました！",
        "SS021" => "%s のデータが正常に拒否されました！",
        "SS022" => "%s のデータが正常に承認されました！",
        "SS023" => "%s の承認がキャンセルされました！",
        "SS024" => "依頼成功キャンセルしました！",
        "SS025" => "%s が正常にコピーされました！",
        "SS026" => "%s テーブルが正常に保存されました！",
        "SS027" => "データは正常にバックアップされました！",
        "SS028" => "ファイルが正常に削除されました！",
        "SS029" => "ユーザー登録完了の通知メールが正常に送信されました！",
        "SS030" => "パスワードは、あと%s日で有効期限が切れます。<br>パスワードを変更するには、パスワードリセットをクリック下さい。",
        "SS031" => "ファイルが正常にダウンロードされました。",
        "SS032" => "データの上書きに成功しました。",
        "SS033" => "無事にデータが完成しました！",
        "SS034" => "無事にデータのキャンセルが完了しました！",
        "SS035" => "データは正常に確定されました！",
        "SS036" => "確定はキャンセルされました！",


    );

    /**
     * Common Error Message Arrays
     *
     * @author
     * Date: 20/02/2019
     */
    private static $errorMsg = array(
        "SE001" => "Data does not exist in system.",
        "SE002" => "%s already exists!",
        "SE003" => "Data cannot be saved!",
        "SE004" => "Please fill information completely!",
        "SE005" => "User Name or Password is invalid!",
        "SE006" => "Sample data registration is only allow 6 lines!",
        "SE007" => "Data can't be delete!",
        "SE008" => "Please choose file to upload!",
        "SE009" => "Fail to delete file!",
        "SE010" => "Can't request because of data is already approve!",
        "SE011" => "Your %s is failed!",
        "SE012" => "Can't download file!",
        "SE013" => "File extension is not allowed: %s",
        "SE014" => "File size can't over 10MB",
        "SE015" => "File Upload Failed!",
        "SE016" => "You don't have permission to %s!",
        "SE017" => "There is no data to %s!",
        "SE018" => "Data is already approved!",
        "SE019" => "Can't save because data has been already requested!",
        "SE020" => "File size can't over 10MB!",
        "SE021" => "File format is invalid!",
        "SE022" => "File header format is invalid!",
        "SE023" => "File data format is invalid at row %s and col %s!",
        "SE024" => "Please exactly insert Layer!",
        "SE025" => "Please insert only number of Account Slip No. and Account State No. in row %s and column %s!",
        "SE026" => "Can't request. Please upload files for selected records.",
        "SE027" => "Can't upload file because of data is already approved!",
        "SE028" => "Can't delete file because of data is already approved!",
        "SE029" => "Can't upload file because of data is already requested!",
        "SE030" => "Can't delete file because of data is already requested!",
        "SE031" => "Business Area Code is already deleted, please login again!",
        "SE032" => "Business Area cannot be deleted.This Business Area is already previewed by Account Manager!",
        "SE033" => "All of data had been already approved!",
        "SE034" => "All of data already reviewed!",
        "SE035" => "BA code cannot be deleted because this BA code is processing state!",
        "SE036" => "There is no data to Approve Cancellation!",
        "SE037" => "This User is Already Deleted",
        "SE038" => "This BA is Already Deleted",
        "SE039" => "Data is already approved cancel!",
        "SE040" => "Can't approve!",
        "SE041" => "All of data have not completely requested.",
        "SE042" => "Invalid Email Address!",
        "SE043" => "Can't Approve. Please upload files for all records.",
        "SE044" => "Please choose BA Name!",
        "SE045" => "This event cannot be done as INACTIVE event because it is in processing state!",
        "SE046" => "All of data had been already deleted!",
        "SE047" => "This picture is already deleted!",
        "SE048" => "There is no data contained in the input file.",
        "SE049" => "The number of records to be downloaded must not exceed 160 records!",
        "SE050" => "This data is already deleted!",
        "SE051" => "The importing process is running by another user!",
        "SE052" => "This Reference name cannot be choice because it is in INACTIVE State !",
        "SE053" => "This event is referenced by another event.",
        "SE054" => "There is no data in %s.",
        "SE055"  => "Work is not completed for the entire BA. If labeling is not possible, enter the reason!",
        "SE056"  => "Work is not completed for the entire BA. If there is no actual item, please enter a comment!",
        "SE057"  => "Work is not completed for the entire BA. If there is no actual item, please enter a comment. If labeling is not possible, enter the reason!",
        "SE058"     => "There is a BA which manager ID is not registered.Contact the accounting department and request to register manager ID into the BA master.",
        "SE059"     => "There is a user whose email address is not registered.Contact the accounting department and request to register an e-mail address into the user master.",
        "SE060"     => "Can not access this link because Event and BA has been selected. Please choose %s %s !",
        "SE061"     => "Can not access this link because Period and BA has been selected. Please choose %s %s !",
        "SE062"     => "Event is already inactive.",
        "SE063"     => "Event is already active.",
        "SE064"     => "There are Manager ID registered in BA is not Sales Manager's.Contact the accounting department and request to update the correct manager ID into the BA master.",
        "SE065" => "You don't have permission!",
        "SE066" => "Please upload files !",
        "SE067" => "Email cannot send, Please upload files for all records!",
        "SE068" => "There is no data to Reject!",
        "SE069" => "Sub Account Code already exits!",
        "SE070" => "Account Code already exits!",
        "SE071" => "This uploaded file is already deleted!",
        "SE072" => "Please choose %s!",
        "SE073" => "Please choose Head Department!",
        "SE074" => "Input month is not in the term!",
        "SE075" => "Data is not enough at row %s!",
        "SE076" => "Please remove password from excel file!",
        "SE077"     => "Can not access this link because Term, Target Month and Headquarter has been selected. Please choose %s ,%s and %s!",
        "SE078"     => "Can not access this link because Term and Target Month has been selected. Please choose Term: %s and Target Month: %s!",
        "SE079" => "The net income (forecast) are over length.",
        "SE080" => "Please fill Budget Term and Target Month!",
        "SE081" => "Please fill Budget Term and Target Month and Head Department!",
        "SE082" => "Please choose target month between start date and end date of budget year!",
        "SE083" => "Data is cannot be request cancel!.file is already uploaded.",
        "SE084" => "Can't download the file because it does not exist on the cloud server.",
        "SE085" => "Please fill Target Month!",
        "SE086" => "Please fill BA Code",
        "SE087"     => "The email address for user %s is not registered.Contact the accounting department and request to register e-mail address into the user master.",
        "SE088"     => "The email addresses for user %s are not registered.Contact the accounting department and request to register e-mail address into the user master.",
        "SE089" => "Can't approve cancel!",
        "SE090" => "There is no budget data to approve!",
        "SE091" => "Can't Approve becasue transaction total is not equal to actual result amount!",
        "SE092" => "Can't Copy the term data!",
        "SE093" => "Please fill Budget Term",
        "SE094" => "Year,Business Area Name and Trading Code already exits!",
        "SE095" => "This data is already used in Trading Plan",
        "SE096" => " %s cannot be deleted because this %s is processing state!",
        "SE097" => "There is no user to send email according to this BA!",

        "SE098" => "%s is not match in import file and display!",
        "SE099" => "Please fill only %s at %s%s in imported file!",
        "SE100" => "Excel of %s are not match with view! ",
        "SE101" => "File header format of Year %s at %s is invalid!",
        "SE102" => "File header format of %s is invalid!",
        "SE103" => "Please check sub account name in import file!",
        "SE104" => "There is no logistic data!",
        "SE105" => "%s (%s) is already choosen.Please choose another one in %s!",
        "SE106" => "%s has been registered as %s in other years",
        "SE107" => "BA code is not match!",
        "SE108" => "Please fill at %s%s of %s in imported file!",
        "SE109" => "There is no event!",
        "SE110" => "There is no department!",
        "SE111"    => "Please choose Department Name!",
        "SE112" => "Some headquarters cannot backup!",
        "SE113" => "%s can't be backup because it has been already backup!",
        "SE114" => "Can't backup files!",
        "SE115" => "Please check the internet connection!",
        "SE116" => "Files can't be deleted!",
        "SE117" => "Can't save because all of data has been already approved!",
        "SE118" => "Please don't change %s at %s!",
        "SE119" => "Please fill destination(BA) and textbox of [社内受払手数料] in table %s!",
        "SE120" => "User is not registered in System.",
        "SE121" => "Login Id is invalid!",
        "SE122" => "Login Id and e-mail address is not matched!",
        "SE123" => "User Name is Invalid!",
        "SE124" => "Too many failed login attempt. Please reset your password!",
        "SE125" => "Layer cannot be deleted because it has been used in Layer Master!",
        "SE126" => "Data cannot be deleted because it is already used from other layers！",
        "SE127" => "Data cannot be saved because layer order %s does not exist in system!",
        "SE128" => "Set the %s after the %s !",
        "SE129" => "There is no setting for email!",
        "SE130" => "You can't delete! %s is used in %s.",
        "SE131" => "Please register for layer code.",
        "SE132" => "Please fill Budget Term and Target Month and %s!",
        "SE133" => "There is no %s in the system to calculate.",
        "SE134" => "This User does not exist!",
        "SE135" => "Mail setting is already exist.",
        "SE136" => "Mail setting for %s is already exist.",
        "SE137" => "There is an error in copying.",
        "SE138" => "All data have been copied.",
        "SE139" => "The original data do not exist.",
        "SE140" => "Data can't copy.",
        "SE141" => "There is an error in overwriting.",
        "SE142" => "Data can't overwrite.",
        "SE143" => "The original data do not exist.",
        "SE144" => "This data is already used for user.",
        "SE145" => "SSO User Login failed!",
        "SE146" => "Please fill %s!",
        "SE147" => "%s' s names and parent names are same with the unexpired layer!",
        "SE148" => "Data already exist!",
        "SE149" => "Labor cost has not been registered yet, so there is nothing to show!",
        "SE150" => "New joined date must be greater than old joined date.",
        "SE151" => "No business has been set up for this group.",
        "SE152" => "Failed to create password for user ID %s",
        "SE153" => "There are no layers registered in this target year, so there is nothing to show!",
        "SE154" => "Datas inputting can't complete!",
        "SE155" => "Can't completed cancel!",
        "SE156" => "New user name already exists!",
        "SE157" => "Data cannot be cancel confirmed!",
        "SE158" => "This layer has already been completed.",
        "SE159" => "You can't complete this layer. Please make sure all layers are completed.",
        "SE160" => "Please choose Term Name, BU and Group!",
        "SE161" => "Data cannot be confirmed!",
        "SE162" => "Data already confirmed!",
        "SE163" => "Data already cancel confirmed!",
        "SE164" => "Data cannot be complete!",
        "SE165" => "Data already complete!",
        "SE166" => "Data already cancel complete!",
        "SE167" => "Reference Term cannot be updated. There are already copy data in other tables.",
        "SE168" => "This layer can't canceled complete because its parent layer' s is completed!",
        "SE169" => "This layer can't complete because its child layer' s is canceled complete!",
    );

    private static $errorMsgJPN = array(
        "SE001" => "データが見つかりません！",
        "SE002" => "%s は既に存在しています！",
        "SE003" => "データを保存できません！",
        "SE004" => "SID: %s のデータを完全入力してください！",
        "SE005" => "ユーザー名かパスワードが無効です！",
        "SE006" => "サンプル作成は6行まで可能です！",
        "SE007" => "データを削除できません！",
        "SE008" => "アップロードするファイルを選択してください！",
        "SE009" => "ファイル削除に失敗しました！",
        "SE010" => "そのデータは依頼できません。既に承認されています！",
        "SE011" => "%s 失敗！",
        "SE012" => "ファイルをダウンロードできません！",
        "SE013" => "ファイル拡張子は許可されていません！",
        "SE014" => "ファイルサイズは10MBを超えることはできません！",
        "SE015" => "ファイルのアップロードに失敗しました！",
        "SE016" => "%s に対する許可がありません！",
        "SE017" => "%s にデータがありません！",
        "SE018" => "データは 既に承認されています！",
        "SE019" => "対象データは既に依頼しています。保存することが出来ません！",
        "SE020" => "ファイルサイズは10MBを超えることはできません！",
        "SE021" => "ファイルが無効なフォーマットです！",
        "SE022" => "ファイルヘッダーが無効なフォーマットです！",
        "SE023" => "ファイルデータが無効なフォーマットです。行 : %s と %s ！",
        "SE024" => "部署を正しく入力してください！",
        "SE025" => "会計伝票NOと会計明細№を正しく入力してください, 行 : %s 、列 : %s ！",
        "SE026" => "依頼できません。選択したレコードのファイルをアップロードしてください。",
        "SE027" => "データが既に承認されているため、ファイルをアップロードできません！",
        "SE028" => "データが既に承認されているため、ファイルを削除できません！",
        "SE029" => "データが既に依頼されているため、ファイルをアップロードできません！",
        "SE030" => "データが既に依頼されているため、ファイルを削除できません！",
        "SE031" => "BAコードは既に削除されています。もう一度ログインしてください！",
        "SE032" => "事業領域は削除できません。この事業領域は既に使用されています！",
        "SE033" => "すべてのデータが既に承認されています！",
        "SE034" => "すべてのデータが既に保存されています！",
        "SE035" => "BAコードを削除できません。このBAコードは処理中です！",
        "SE036" => "承認キャンセルのするためのデータがありません！",
        "SE037" => "このユーザーは既に削除されています！",
        "SE038" => "このBAは既に削除されています！",
        "SE039" => "このデータは既にキャンセルの承認をされています！",
        "SE040" => "承認できない！",
        "SE041" => "すべてのデータが完全に要求されているわけではありません。",
        "SE042" => "無効なメールアドレス！",
        "SE043" => "承認できません。 全レコードのファイルをアップロードしてください。",
        "SE044" => "BA名前を選択してください！。",
        "SE045" => "このイベントを非アクティブできません。このイベント は処理中です。",
        "SE046" => "すべてのデータはすでに削除されています。",
        "SE047" => "この画像は削除されています。",
        "SE048" => "入力ファイルにデータが含まれていません。",
        "SE049" => "ダウンロードするレコードの数は、160レコードを超えてはなりません",
        "SE050" => "このデータは既に削除されています！",
        "SE051" => "インポートプロセスは別のユーザーによって実行されています",
        "SE052" => "この参照名は、INACTIVE状態にあるため選択できません!",
        "SE053" => "このイベントは別のイベントから参照されています。",
        "SE054" => "%sにデータはありません。",
        "SE055"  => "BA全体では作業が未完了です。ラベル貼付が不可な場合は理由を記入してください！",
        "SE056"  => "BA全体では作業が未完了です。現物がない場合はコメントを記入してください！",
        "SE057"  => "BA全体では作業が未完了です。現物がない場合はコメントを記入してください。ラベル貼付が不可な場合は理由を記入してください！",
        "SE058"    => "部長IDが登録されていないBAがあります。経理部に連絡してBAマスタに部長IDの登録を依頼してください。",
        "SE059"    => "メールアドレスが登録されていないユーザがいます。経理部に連絡してユーザマスタにメールアドレスの登録を依頼してください。",
        "SE060"    => "イベントとBAが選択されているため、このリンクにアクセスできません。%s %s を選択してください！",
        "SE061"    => "イベントと期間が選択されているため、このリンクにアクセスできません。%s %s を選択してください！",
        "SE062"     => "イベントはすでに非アクティブになっています。",
        "SE063"     => "イベントはすでにアクティブになっています。",
        "SE064"     => "BAに登録されている部長IDは営業の部長IDになっていません。経理部に連絡してBAマスタに正しい部長IDの変更を依頼してください。",
        "SE065" => "あなたには許可がありません！",
        "SE066" => "アップロードしてください。 !",
        "SE067" => "メールを送信できません。すべての記録のファイルをアップロードしてください！",
        "SE068" => "拒否の承認をするためのデータがありません！",
        "SE069" => "サブアカウントコードはすでに存在します!",
        "SE070" => "会計コードは既に存在します。",
        "SE071" => "このアップロードされたファイルはすでに削除されています！",
        "SE072" => " %s を選んでください！",
        "SE073" => "本部を選択してください！",
        "SE074" => "会計年度は予算期間と合っていません！",
        "SE075" => "%s 行目にデータが足りません!",
        "SE076" => "Excelファイルからパスワードを削除してください!",
        "SE077"     => "期間と対象月と本部が選択されているため、このリンクにアクセスできません。 %s , %s と %s を 選択してください！",
        "SE078" => "期間と対象月が選択されているため、このリンクにアクセスできません。 期間：%s と 対象月：%s を 選択してください！",
        "SE079" => "純利益（予測）が長すぎます。",
        "SE080" => "期間と対象月 を選択してください！",
        "SE081" => "期間と対象月と本部 を選択してください！",
        "SE082" => "予算年度の開始日と終了日の間で目標月を選択してください！",
        "SE083" => "依頼 キャンセルできません。ファイルはすでにアップロードされています！",
        "SE084" => "クラウドサーバーに存在しないため、ファイルをダウンロードできません。",
        "SE085" => "対象月 を選択してください！",
        "SE086" => "事業領域 を選択してください！",
        "SE087"     => "ユーザ %s にメールアドレスが登録されてません。経理部に連絡してユーザマスタにメールアドレスの登録を依頼してください。",
        "SE088"     => "ユーザ %s にメールアドレスが登録されてません。経理部に連絡してユーザマスタにメールアドレスの登録を依頼してください。",
        "SE089" => "承認キャンセルができませんでした!",
        "SE090" => "承認するための予算データがありません！",
        "SE091" => "取引合計が実際の結果金額と等しくないため、承認できません！",
        "SE092" => "期間のコピーができませんでした！",
        "SE093" => "期間 を選択してください！",
        "SE094" => "年、事業領域名、取引コードは既に存在します！",
        "SE095" => "このデータはすでにトレーディングプランで使用されています",
        "SE096" => " %s を削除できません。この %s は処理中です！",
        "SE097" => "メールを送るためのユーザーがありません！",

        "SE098" => "インポートファイルの%sはシステム上の表示と一致しません！",
        "SE099" => "インポートされたファイルで %s のみを 行%s%s に入力してください！",
        "SE100" => "エクセルの %s は画面と同じではありません。",
        "SE101" => "年度 %s の %s のファイルヘッダー形式が無効です！",
        "SE102" => "%s のファイルヘッダー形式が無効です！",
        "SE103" => "インポートファイルのサブアカウント名を確認してください！",
        "SE104" => "取引のデータがありません！",
        "SE105" => "%s (%s) すでに選択されています。%s別のものを選択してください！",
        "SE106" => "%s は他の年度で %s として登録されてます",
        "SE107" => "BAコードが一致しません!",
        "SE108" => "インポートしたファイルで%s%sに%sを入力してください！",
        "SE109" => "選択されたイベントはありません!",
        "SE110" => "部署がありません！",
        "SE111"    => "部署名を選択してください！",
        "SE112" => "一部の本社はバックアップできません！",
        "SE113" => "%sはすでにバックアップされているため、バックアップできません!",
        "SE114" => "ファイルをバックアップできません！",
        "SE115" => "インターネット接続を確認してください！",
        "SE116" => "ファイルは削除できません！",
        "SE117" => "データがすでに承認されているため、保存できません！",
        "SE118" => "Please don't change %s at %s!",
        "SE119" => "Please fill destination(BA) and textbox of [社内受払手数料] in table %s!",
        "SE120" => "ユーザーが登録されていません！",
        "SE121" => "ログインIDが無効です！",
        "SE122" => "ログインIDとメールアドレスが一致していません！",
        "SE123" => "ユーザー名が無効です！",
        "SE124" => "ログイン試行の失敗が多すぎます。あなたのパスワードをリセットしてください！",
        "SE125" => "部署マスターで使用されている可能性があるため、削除できません！",
        "SE126" => "他の部署から既に使用されているため、削除できません！",
        "SE127" => "部署オーダー %s がシステムに存在しないため、データを保存できません！",
        "SE128" => "%s を %s の後にしてください！",
        "SE129" => "メールの設定はありません！",
        "SE130" => "%sは %sに 利用されているため、削除できません。",
        "SE131" => "部署を登録してください。",
        "SE132" => "予算期間と目標月と %s を記入してください!",
        "SE133" => "計算する為の %s がシステムにありません。",
        "SE134" => "このユーザーは存在しません。",
        "SE135" => "メールは既に設定されています。",
        "SE136" => "%sのためメールが 既に設定されています。",
        "SE137" => "コピーに誤りがあります。",
        "SE138" => "すべてのデータがコピーされました。",
        "SE139" => "元のデータは存在しません。",
        "SE140" => "データはコピーできません。",
        "SE141" => "上書きエラーです。",
        "SE142" =>  "データは上書きできません。",
        "SE143" => "元のデータは存在しません。",
        "SE144" => "このデータはすでにユーザーに使用されています。",
        "SE145" => "SSO ユーザーのログインに失敗しました!",
        "SE146" => "%s を記入してください！",
        "SE147" => "%s の名前と親の名前が、有効期限が切れていない部署と同じです！",
        "SE148" => "データは既に存在しています",
        "SE149" => "人件費はまだ登録されていないので、表示するものは何もありません!",
        "SE150" => "新しい結合日は古い結合日よりも後の日付である必要があります。",
        "SE151" => "このグループに対するビジネスが設定されていません。",
        "SE152" => "ユーザー ID %s のパスワードの作成に失敗しました",
        "SE153" => "この対象年度には部署が登録されていないため、表示するものがありません！",
        "SE154" => "データの入力が完了できません!",
        "SE155" => "キャンセルが完了できません!",
        "SE156" => "新しいユーザー名はすでに存在します。",
        "SE157" => "データはキャンセル不可です！",
        "SE158" => "このレイヤーはすでに完成しています。",
        "SE159" => "この層を完了することはできません。 すべてのレイヤーが完了していることを確認してください。",
        "SE160" => "期間名、BU、グループを選択してください。",
        "SE161" => "データが確認できません！",
        "SE162" => "データは確認済み!",
        "SE163" => "データキャンセル確認済み!",
        "SE167" => "参照用語は更新できません。他のテーブルに既にコピーされたデータがあります。",
        "SE168" => "親レイヤーの が完了しているため、このレイヤーは完了をキャンセルできません！",
        "SE169" => "子レイヤーの がキャンセルされているため、このレイヤーは完了できません！"
    );

    /**
     * Get Common Error Message
     *
     * @author
     *
     * @param errorMessageID
     * @param arrayDynamicValues
     */
    public function getErrorMsg($errorMsgID, $arrayDynamicValues = array())
    {
        $msg = "";
        if ($this->Session->read('Config.language') == 'eng') {
            $msg = AppController::$errorMsg[$errorMsgID];
        } else {
            $msg = AppController::$errorMsgJPN[$errorMsgID];
        }
        return vsprintf($msg, $arrayDynamicValues);
    }

    /**
     * Get Common Success Message
     *
     * @author
     *
     * @param successMessageID
     * @param arrayDynamicValues
     */
    public function getSuccessMsg($successMsgID, $arrayDynamicValues = array())
    {
        $msg = "";

        if ($this->Session->read('Config.language') == 'eng') {
            $msg = AppController::$successMsg[$successMsgID];
        } else {
            $msg = AppController::$successMsgJPN[$successMsgID];
        }
        return vsprintf($msg, $arrayDynamicValues);
    }

    /**
     * search for sending email function,
     * @author Pan Ei Phyo
     * @return boolean
     *
     */
    public function sendEmail($layer_code, $layer_name, $period, $login_user_name, $toEmail, $ccEmail, $mail_template, $mail, $url)
    {
        $email = new CakeEmail();
        $email->config('smtp');
        $email_from = Email::FROM_EMAIL;
        $base_url = "";

        $current_time = $this->getCurrentTime();

        try {
            if ($url != "" || !empty($url)) {
                $base_url = Router::url($url, true);
            }

            $toEmail = $this->formatEmailToArray($toEmail);

            if ($ccEmail != "") {
                $ccEmail = $this->formatEmailToArray($ccEmail);

                $email->template($mail_template, 'common_layout')
                    ->emailFormat('html')
                    ->viewVars(array(
                        'subject'     => $mail['subject'],
                        'title'     => $mail['template_title'],
                        'body'         => $mail['template_body'],
                        'user_name' => $login_user_name,
                        'layer_code'     => $layer_code,
                        'layer_name'     => $layer_name,
                        'period'     => $period,
                        'link'         => $base_url
                    ))
                    ->to($toEmail)
                    ->cc($ccEmail)
                    ->from($email_from)
                    ->replyTo($email_from)
                    ->subject($mail['subject'])
                    ->send();
            } else {
                $email->template($mail_template, 'common_layout')
                    ->emailFormat('html')
                    ->viewVars(array(
                        'subject'     => $mail['subject'],
                        'title'     => $mail['template_title'],
                        'body'         => $mail['template_body'],
                        'user_name' => $login_user_name,
                        'layer_code'     => $layer_code,
                        'layer_name'     => $layer_name,
                        'period'     => $period,
                        'link'         => $base_url
                    ))
                    ->to($toEmail)
                    ->from($email_from)
                    ->replyTo($email_from)
                    ->subject($mail['subject'])
                    ->send();
            }
            $data = array(
                "error" => 0,
                "errormsg" => ""
            );
        } catch (Exception $e) {
            CakeLog::write('email', 'Mail Failed at ' . $current_time . '	Subject: ' . $mail['subject'] . '	From:' . $email_from . '	To: ' . implode(',', $toEmail) . '	CC: ' . implode(',', $ccEmail) . '	BCC: ' . implode(',', $bccEmail) . 'Error contents:' . $e->getMessage());
            $data = array(
                "error" => 1,
                "errormsg" => $e->getMessage()
            );
        }
        if (!$data['error']) {
            CakeLog::write('email', 'Mail Successed at ' . $current_time . '	Subject: ' . $mail['subject'] . '	From:' . $email_from . '	To: ' . implode(',', $toEmail) . '	CC: ' . implode(',', $ccEmail) . '	BCC: ' . implode(',', $bccEmail));
        }
        return $data;
    }

    # for phase 3 mail sending function (Khin Hnin Myo)
    public function sendEmailP3($target_month, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url)
    {
        $email = new CakeEmail();
        $email->config('smtp');
        $email_from = Email::FROM_EMAIL;
        $base_url = "";

        $toEmail = array_values($toEmail); # make keys in order (0,1,2,3...)
        $bccEmail = array_values($bccEmail); # make keys in order (0,1,2,3...)
        $ccEmail = array_values($ccEmail); # make keys in order (0,1,2,3...)

        $current_time = $this->getCurrentTime();

        try {
            $toEmail = $this->formatEmailToArray($toEmail); #change to array for some ',' separated mails
            $mail['template_body'] =   str_replace("\n", "<br>", $mail['template_body']);
            if ($url != "" || !empty($url)) {
                $base_url = Router::url($url, true);
            }

            if (!empty($bccEmail) && !empty($ccEmail)) {
                $bccEmail = $this->formatEmailToArray($bccEmail); #change to array for some ',' separated mails
                $ccEmail = $this->formatEmailToArray($ccEmail); #change to array for some ',' separated mails

                $total_cnt = count($toEmail) + count($bccEmail) + count($ccEmail);
                $divider = ceil($total_cnt / Email::MAIL_LIMIT);

                if ($divider < count($toEmail)) {
                    $toEmails = array_chunk($toEmail, ceil(count($toEmail) / $divider));
                }
                if ($divider < count($bccEmail)) {
                    $bccEmails = array_chunk($bccEmail, ceil(count($bccEmail) / $divider));
                }
                if ($divider < count($ccEmail)) {
                    $ccEmails = array_chunk($ccEmail, ceil(count($ccEmail) / $divider));
                }
                for ($i = 0; $i < $divider; $i++) {
                    $to     = (!empty($toEmails)) ? $toEmails[$i] : $toEmail;
                    $bcc     = (!empty($bccEmails)) ? $bccEmails[$i] : $bccEmail;
                    $cc     = (!empty($ccEmails)) ? $ccEmails[$i] : $ccEmail;

                    #Sending Mail
                    $email->template($mail_template, 'common_layout')
                        ->emailFormat('html')
                        ->viewVars(array(
                            'subject'     => $mail['subject'],
                            'title'     => $mail['template_title'],
                            'body'         => $mail['template_body'],
                            'user_name' => $login_user_name,
                            'period'     => $target_month,
                            'link'         => $base_url
                        ))
                        ->to($to)
                        ->cc($cc)
                        ->bcc($bcc)
                        ->from($email_from)
                        ->replyTo($email_from)
                        ->subject($mail['subject'])
                        ->send();

                    CakeLog::write('email', 'Mail Successed at ' . $current_time . '	Subject: ' . $mail['subject'] . '	From:' . $email_from . '	To: ' . implode(',', $toEmail) . '	CC: ' . implode(',', $ccEmail) . '	BCC: ' . implode(',', $bccEmail));
                }
            } elseif (!empty($ccEmail)) {
                $ccEmail = $this->formatEmailToArray($ccEmail); #change to array for some ',' separated mails

                $total_cnt = count($toEmail) + count($ccEmail); #get total email count
                $divider = ceil($total_cnt / Email::MAIL_LIMIT); #decide loop count for mail send because email can't exceed 100 in one time

                if ($divider < count($toEmail)) { #divide mails into pieces
                    $toEmails = array_chunk($toEmail, ceil(count($toEmail) / $divider));
                }
                if ($divider < count($ccEmail)) { #divide mails into pieces
                    $ccEmails = array_chunk($ccEmail, ceil(count($ccEmail) / $divider));
                }
                for ($i = 0; $i < $divider; $i++) {
                    $to = (!empty($toEmails)) ? $toEmails[$i] : $toEmail;
                    $cc = (!empty($ccEmails)) ? $ccEmails[$i] : $ccEmail;

                    #Sending Mail
                    $email->template($mail_template, 'common_layout')
                        ->emailFormat('html')
                        ->viewVars(array(
                            'subject'     => $mail['subject'],
                            'title'     => $mail['template_title'],
                            'body'         => $mail['template_body'],
                            'user_name' => $login_user_name,
                            'period'     => $target_month,
                            'link'         => $base_url
                        ))
                        ->to($to)
                        ->cc($cc)
                        ->from($email_from)
                        ->replyTo($email_from)
                        ->subject($mail['subject'])
                        ->send();
                    CakeLog::write('email', 'Mail Successed at ' . $current_time . '	Subject: ' . $mail['subject'] . '	From:' . $email_from . '	To: ' . implode(',', $toEmail) . '	CC: ' . implode(',', $ccEmail));
                }
            } elseif (!empty($bccEmail)) {
                $bccEmail = $this->formatEmailToArray($bccEmail); #change to array for some ',' separated mails

                $total_cnt = count($toEmail) + count($bccEmail);
                $divider = ceil($total_cnt / Email::MAIL_LIMIT);

                if ($divider < count($toEmail)) {
                    $toEmails = array_chunk($toEmail, ceil(count($toEmail) / $divider));
                }
                if ($divider < count($bccEmail)) {
                    $bccEmails = array_chunk($bccEmail, ceil(count($bccEmail) / $divider));
                }
                for ($i = 0; $i < $divider; $i++) {
                    $to = (!empty($toEmails)) ? $toEmails[$i] : $toEmail;
                    $bcc = (!empty($bccEmails)) ? $bccEmails[$i] : $bccEmail;

                    #Sending Mail
                    $email->template($mail_template, 'common_layout')
                        ->emailFormat('html')
                        ->viewVars(array(
                            'subject'     => $mail['subject'],
                            'title'     => $mail['template_title'],
                            'body'         => $mail['template_body'],
                            'user_name' => $login_user_name,
                            'period'     => $target_month,
                            'link'         => $base_url
                        ))
                        ->to($to)
                        ->bcc($bcc)
                        ->from($email_from)
                        ->replyTo($email_from)
                        ->subject($mail['subject'])
                        ->send();

                    CakeLog::write('email', 'Mail Successed at ' . $current_time . '	Subject: ' . $mail['subject'] . '	From:' . $email_from . '	To: ' . implode(',', $toEmail) . '	BCC: ' . implode(',', $bccEmail));
                }
            } else {
                $divider = ceil(count($toEmail) / Email::MAIL_LIMIT);
                $toEmails = array_chunk($toEmail, ceil(count($toEmail) / $divider));

                for ($i = 0; $i < $divider; $i++) {
                    $to = (!empty($toEmails)) ? $toEmails[$i] : $toEmail;

                    #Sending Mail
                    $result = $email->template($mail_template, 'common_layout')
                        ->emailFormat('html')
                        ->viewVars(array(
                            'subject'     => $mail['subject'],
                            'title'     => $mail['template_title'],
                            'body'         => $mail['template_body'],
                            'user_name' => $login_user_name,
                            'period'     => $target_month,
                            'link'         => $base_url
                        ))
                        ->to($to)
                        ->from($email_from)
                        ->replyTo($email_from)
                        ->subject($mail['subject'])
                        ->send();
                    CakeLog::write('email', 'Mail Successed at ' . $current_time . '	Subject: ' . $mail['subject'] . '	From:' . $email_from . '	To: ' . implode(',', $toEmail));
                }
            }
            $data = array(
                "error" => 0,
                "errormsg" => ""
            );
        } catch (Exception $e) {
            CakeLog::write('email', 'Mail Failed at ' . $current_time . '	Subject: ' . $mail['subject'] . '	From:' . $email_from . '	To: ' . implode(',', $toEmail) . '	CC: ' . implode(',', $ccEmail) . '	BCC: ' . implode(',', $bccEmail) . 'Error contents:' . $e->getMessage());
            $data = array(
                "error" => 1,
                "errormsg" => $e->getMessage()
            );
        }
        return $data;
    }

    /**
     * search email for autocomplete form,
     * @author Pan Ei Phyo
     * @return data
     *
     */
    public function autoCompleteCall()
    {
        // $this->autoRender = false;
        // $this->request->allowMethod('ajax');
        #only allow ajax request
        $this->checkAjaxRequest($this);

        if ($this->request->is('post')) {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $searchTerm     = $this->request->data['searchValue'];
            $pattern = '/^' . $searchTerm . '/';

            $searchLevel     = $this->request->data['levelId'];
            $email_data     = array();
            $email_data2     = array();
            $loopCount         = 0;
            $tmp_arr = array();

            $condition_arr = array();
            if (empty($layer_code) || $layer_code == "") {
                $condition_arr = array(
                    'email LIKE'    => '%' . $searchTerm . '%',
                    'role_id' => $searchLevel,
                    'flag'            => 1
                );
            } else {
                if ($searchLevel == '3') {
                    $condition_arr = array(
                        'email LIKE'    => '%' . $searchTerm . '%',
                        'role_id' => $searchLevel,
                        'flag'            => 1
                    );
                } else {
                    $condition_arr = array(
                        'email LIKE'    => '%' . $searchTerm . '%',
                        'role_id' => $searchLevel,
                        'layer_code LIKE'        => '%' . $layer_code . '%',
                        'flag'            => 1
                    );
                }
            }

            $email_data = $this->User->find('all', array(
                'fields' => 'DISTINCT (email)',
                'conditions' => $condition_arr
            ));

            if (count($searchLevel) > 1) {
                if (in_array(4, $searchLevel) || in_array(3, $searchLevel) || in_array(2, $searchLevel)) {
                    foreach ($searchLevel as $level) {
                        if ($level == 4 || $level == 3 || $level == 2) {
                            $condition_arr = array(
                                'email LIKE'    => '%' . $searchTerm . '%',
                                'role_id' => $level,
                                'flag'            => 1
                            );
                            $email_data2 = $this->User->find('all', array(
                                'fields' => 'DISTINCT (email)',
                                'conditions' => $condition_arr
                            ));
                            $tmp_arr = array_merge($tmp_arr, $email_data2);
                        }
                    }
                } elseif (in_array(5, $searchLevel) && in_array(8, $searchLevel) && in_array(7, $searchLevel)) {
                    $tmp_arr = $this->Layer->find('all', array(
                        'fields' => 'DISTINCT User.email',
                        'joins' => array(
                            array(
                                'table' => 'tbl_user',
                                'alias' => 'User',
                                'type'  =>  'left',
                                'conditions' => array(
                                    'Layer.managers = User.login_id',
                                    'User.flag' => 1,
                                    'email LIKE'    => '%' . $searchTerm . '%',
                                )
                            )
                        ),
                        'conditions' => array(
                            'Layer.layer_code' => $layer_code,
                            'Layer.flag' => 1,
                        )
                    ));
                }
            } else {
                if ($searchLevel == 4 || $searchLevel == 3 || $searchLevel == 2) {
                    $condition_arr = array(
                        'email LIKE'    => '%' . $searchTerm . '%',
                        'role_id' => $searchLevel,
                        'flag'            => 1
                    );
                    $email_data2 = $this->User->find('all', array(
                        'fields' => 'DISTINCT (email)',
                        'conditions' => $condition_arr
                    ));
                    $tmp_arr = array_merge($tmp_arr, $email_data2);
                }
            }
            if ($tmp_arr != "") {
                $email_data = array_merge($email_data, $tmp_arr);
                $email_data = array_unique($email_data, SORT_REGULAR);
            }

            $i = 0;
            foreach ($email_data as $email) {
                $str[$i] = $email["User"]["email"];
                $i++;
            }

            if (!empty($str)) {
                $str = $this->formatEmailToArray($str);

                if (is_array($str)) {
                    foreach ($str as $index => $eachMail) {
                        if (!preg_match($pattern, $eachMail)) {
                            unset($str[$index]);
                        }
                    }
                } else {
                    if (preg_match($pattern, $str)) {
                        $str = array($str);
                    } else {
                        return false;
                    }
                }
                if (empty($str)) {
                    return false;
                }

                // Return results as json encoded array
                return json_encode($str);
            } else {
                return false;
            }
        }
    }

    public function autoComplete()
    {
        #only allow ajax request
        $this->checkAjaxRequest($this);

        if ($this->request->is('post')) {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $searchTerm     = $this->request->data['searchValue'];
            $pattern = '/^' . $searchTerm . '/';

            $searchLevel    = $this->request->data['levelId'];
            $email_data     = array();
            $email_data2    = array();
            $loopCount      = 0;
            $tmp_arr = array();

            $condition_arr = array();
            if (!empty($layer_code)) {
                $code = $this->lastLayerCode($layer_code);
                $code_query = ("(layer_code LIKE '%" . join("%' OR layer_code LIKE '%", $code) . "%')");
                $condition_arr = array(
                    'email LIKE' => '%' . $searchTerm . '%',
                    'role_id' => $searchLevel,
                    'User.flag' => 1,
                    '0' => $code_query
                );
            } else {
                $condition_arr = array(
                    'email LIKE' => '%' . $searchTerm . '%',
                    'role_id' => $searchLevel,
                    'User.flag' => 1
                );
            }

            $email_data = $this->User->find('all', array(
                'fields' => 'DISTINCT (email)',
                'conditions' => $condition_arr
            ));
            $email_data = array_unique($email_data, SORT_REGULAR);
            $email_data = array_column(array_column($email_data, 'User'), 'email');

            if (!empty($email_data)) {
                $str = $this->formatEmailToArray($email_data);
                if (is_array($str)) {
                    foreach ($str as $index => $eachMail) {
                        if (!preg_match($pattern, $eachMail)) {
                            unset($str[$index]);
                        }
                    }
                } else {
                    if (preg_match($pattern, $str)) {
                        $str = array($str);
                    } else {
                        return false;
                    }
                }
                if (empty($str)) {
                    return false;
                }

                // Return results as json encoded array
                return json_encode($str);
            } else {
                return false;
            }
        }
    }
    public function lastLayerCode($code)
    {
        $type_order = $this->Layers->find('first', array(
            'fields' => array('MAX(type_order) as type_order'),
            'conditions' => array(
                'flag' => '1'
            )
        ))[0]['type_order'];
        $data = array_column(array_column($this->Layer->find('all', array(
            'fields' => array('layers.layer_code'),
            'conditions' => array(
                'Layer.layer_code' => $code,
                'Layer.flag' => 1
            ),
            'joins' => array(
                array(
                    'table' => 'layers',
                    'alias' => 'layers',
                    'conditions' => array(
                        "layers.flag = 1 AND layers.type_order = '" . $type_order . "' AND  layers.parent_id LIKE CONCAT('%\"L', Layer.type_order, '\":\"',Layer.id,'\"%')"
                    )
                )
            )
        )), 'layers'), 'code');
        return $data;
    }

    /**
     * Change inputed mail string to array
     * @author Pan Ei Phyo
     * @param mail string
     * @return email array
     *
     */
    public function formatMailInput($mailStr)
    {
        $mailArr = explode(",", $mailStr);
        $mailArr = array_filter($mailArr);

        $lastIdx = count($mailArr) - 1;
        if ((empty($mailArr[$lastIdx])) || $mailArr[$lastIdx] == " ") {
            unset($mailArr[$lastIdx]);
        }

        return $mailArr;
    }

    /**
     * Change inputed mail string to array
     * @author Pan Ei Phyo
     * @param $email array
     * @return $email
     *
     */
    public function formatEmailToArray($email)
    {
        $cnt = count($email);
        $tmp = array();
        if ($cnt == 1 && is_string($email) == true) {
            if (strpos($email, ',') == true) {
                $email = str_replace(' ', '', $email);
                $email = explode(',', $email);
                $email = array_filter($email);
            } elseif (strpos($email, ' ') == true) {
                $email = explode(' ', $email);
                $email = array_filter($email);
            }
        } else {
            $count = 0;
            #Loop email
            foreach ($email as $index => $each) {
                #If "," exist in array value
                if (strpos($each, ',') !== false) {

                    #Seprate into array if comma exist and set this array to $tmp
                    $tmp = array_merge($tmp, explode(',', $each));

                    #Remove comma include value from array
                    unset($email[$index]);
                }
                $count++;
            }

            #Merge two array again
            $email = array_merge($email, $tmp);
        }

        #If more than one value
        if (count($email) > 1) {

            #Remove duplicate array
            $email = array_unique($email, SORT_REGULAR);
        }

        return $email;
    }

    public function checkAccessType()
    {
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $access_type = $this->Session->read('ACCESS_TYPE');

        if ($role_id != 1) {
            if (!empty($access_type) && $access_type != '1' && $access_type != '2') {
                $this->redirect(array('controller' => 'Logins', 'action' => 'logout'));
            }
        }
    }

    public function getCurrentTime()
    {
        $dt = new DateTime("now", new DateTimeZone('Asia/Yangon'));
        return $dt->format('m/d/Y, H:i:s');
    }
    /**
     * for sending email function,
     * @author Ei Thandar Kyaw
     * @return boolean
     *
     */
    public function sendEmailFileAttach($mail_template, $mail, $mailAddres, $url)
    {
        $email = new CakeEmail();
        $email->config('smtp');
        $email_from = Email::FROM_EMAIL;
        $current_time = $this->getCurrentTime();
        $toEmail = $this->formatEmailToArray($mailAddres['toEmail']);
        $ccEmail = $this->formatEmailToArray($mailAddres['ccEmail']);

        if ($url != "" || !empty($url)) {
            $base_url = Router::url($url, true);
        }
        try {
            if ($ccEmail == '') {
                $email->template($mail_template, 'common_layout')
                    ->emailFormat('html')
                    ->viewVars(array(
                        'subject'     => $mail['subject'],
                        'title'     => $mail['template_title'],
                        'body'         => $mail['template_body'],
                        'link'         => $base_url
                    ))
                    ->to($toEmail)
                    ->from($email_from)
                    ->replyTo($email_from)
                    ->subject($mail['subject'])
                    ->send();
                $data = array(
                    "error" => 0,
                    "errormsg" => ""
                );
            } else {
                $email->template($mail_template, 'common_layout')
                    ->emailFormat('html')
                    ->viewVars(array(
                        'subject'     => $mail['subject'],
                        'title'     => $mail['template_title'],
                        'body'         => $mail['template_body'],
                        'link'         => $base_url
                    ))
                    ->to($toEmail)
                    ->cc($ccEmail)
                    ->from($email_from)
                    ->replyTo($email_from)
                    ->subject($mail['subject'])
                    ->send();
                $data = array(
                    "error" => 0,
                    "errormsg" => ""
                );
            }
        } catch (Exception $e) {
            CakeLog::write('email', 'Mail Failed at ' . $current_time . '	Subject: ' . $mail['subject'] . '	From:' . $email_from . '	To: ' . implode(',', $toEmail) . '	CC: ' . implode(',', $ccEmail) . '	BCC: ' . implode(',', $bccEmail) . 'Error contents:' . $e->getMessage());
            $data = array(
                "error" => 1,
                "errormsg" => $e->getMessage()
            );
        }
        if (!$data['error']) {
            CakeLog::write('email', 'Mail Successed at ' . $current_time . '	Subject: ' . $mail['subject'] . '	From:' . $email_from . '	To: ' . implode(',', $toEmail) . '	CC: ' . implode(',', $ccEmail) . '	BCC: ' . implode(',', $bccEmail));
        }
        // pr($data);
        // exit;
        return $data;
    }

    /**
     * checkAjaxRequest
     * @author HeinHtetKo(2021/12/03)
     **/
    public function checkAjaxRequest($_this)
    {
        try {
            $controller_name = $_this->request->params['controller'];
            $_this->autoRender = false;
            $_this->request->allowMethod('ajax');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            return $_this->redirect(array('controller' => $controller_name, 'action' => 'index'));
        }
    }
    /**
     * check login user is expired or not or over login 5times?
     * If expired, redirect to Reset Password page.
     * @author Nu Nu Lwin(2022/05/27)
     **/
    public function checkExpiredUser()
    {
        $login_id   = $this->Session->read('LOGINID');
        $expire_day = $this->PasswordHistory->find(
            'all',
            array(
                'conditions' => array(
                    'login_code' => $login_id,
                    'status' => '1',
                    'PasswordHistory.expire_date <=' => date("Y-m-d")
                )
            )
        );

        if (!empty($expire_day)) {
            $redirect_arr = (!empty($expire_day['remark'])) ? array('controller' => 'Users', 'action' => 'ResetPassword') : array('controller' => 'Users', 'action' => 'ResetPassword', '?' => 'exp');

            $this->redirect($redirect_arr);
        }
        return true;
    }
    /** 
     * checkSampleUrlSession method
     * @author Khin Hnin Myo
     * @param  $selection, $layer_code, $period
     * @return void
     *
     */
    public function checkSampleUrlSession()
    {
        $this->checkUserStatus(); #checkusersession
        #from url
        $period = $this->request->query('period');
        $layer_code = $this->request->query('ba');
        $category = $this->request->query('category');
        $pagename = $this->request->params['controller'];

        #from session
        $status = true;
        if (empty($period)) {
            $period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            if (empty($period)) {
                $status = false;
                $errorMsg = $this->getErrorMsg("SE072", [__("期間")]);
                $this->Flash->set($errorMsg, array("key" => "Error"));
            }
        }
        if (empty($layer_code)) {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            if (empty($layer_code)) {
                $status = false;
                $errorMsg = $this->getErrorMsg("SE072", [__("部署")]);
                $this->Flash->set($errorMsg, array("key" => "Error"));
            }
        }
        if (empty($category)) {
            $category = $this->Session->read('SAMPLECHECK_CATEGORY');
            if (empty($category)) {
                $status = false;
                $errorMsg = $this->getErrorMsg("SE072", [__("カテゴリー")]);
                $this->Flash->set($errorMsg, array("key" => "Error"));
            }
        }
        if (!$status) {
            $this->redirect(array('controller' => 'SampleSelections', 'action' => 'index'));
        }

        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');

        $Common = new CommonController();
        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);

        $Common->UrlSession('SampleSelections', $layer_code, $period, $category);

        if (!$this->Session->check('SAMPLECHECK_PERIOD_DATE') || !$this->Session->check('SESSION_LAYER_CODE') || ($layer_code == "" && $permissions['index']['limit'] > 0) || !$this->Session->check('SAMPLECHECK_CATEGORY')) {
            $errorMsg = $this->getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key" => "Error"));
            $this->redirect(array('controller' => 'SampleSelections', 'action' => 'index'));
        }
    }

    /** 
     * checkSampleUrlSession method
     * @author Khin Hnin Myo
     * @param  $selection, $layer_code, $period
     * @return void
     *
     */
    public function checkSapUrlSession($page = '')
    {
        $this->checkUserStatus();

        $period         = $this->request->query('param');
        $layer_code     = $this->request->query('ba');

        $id             = $this->Session->read('LOGIN_ID'); //inc_id
        $login_id       = $this->Session->read('LOGINID'); //login_id
        $role_id        = $this->Session->read('ADMIN_LEVEL_ID');
        $pagename       = $this->request->params['controller'];
        $status = true;
        if (empty($period)) {
            $period = $this->Session->read('SapSelections_PERIOD_DATE');
            if (empty($period)) {
                $status = false;
                $errorMsg = $this->getErrorMsg("SE072", [__("期間")]);
                $this->Flash->set($errorMsg, array("key" => "Error"));
            }
        }
        if (empty($layer_code)) {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            if (empty($layer_code) && $page == '') {
                $status = false;
                $errorMsg = $this->getErrorMsg("SE072", [__("部署")]);
                $this->Flash->set($errorMsg, array("key" => "Error"));
            }
        }
        if (!$status) {
            $this->redirect(array('controller' => 'SapSelections', 'action' => 'index'));
        }

        $Common = new CommonController();
        $permissions = $Common->getPermissionsByRole($id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        $layers = array_keys($permissions['index']['layers']);

        $Common->UrlSession('SapSelections', $layer_code, $period);
        if ($page == '') {
            if ((!in_array($layer_code, $layers)) || ($layer_code == "" && $permissions['index']['limit'] > 0)) {
                $errorMsg = $this->getErrorMsg('SE065');
                $this->Flash->set($errorMsg, array("key" => "Error"));
                $this->redirect(array('controller' => 'SapSelections', 'action' => 'index'));
            }
        }
    }

    /** 
     * checkStockUrlSession method
     * @author Hein Htet Ko
     * @return void
     *
     */
    public function checkStockUrlSession($page = '')
    {
        $this->checkUserStatus();

        $period         = $this->request->query('param');
        $layer_code     = $this->request->query('ba');

        $id             = $this->Session->read('LOGIN_ID'); //inc_id
        $login_id       = $this->Session->read('LOGINID'); //login_id
        $role_id        = $this->Session->read('ADMIN_LEVEL_ID');
        $pagename       = $this->request->params['controller'];
        $status = true;
        if (empty($period)) {
            $period = $this->Session->read('StockSelections_PERIOD_DATE');
            if (empty($period)) {
                $status = false;
                $errorMsg = $this->getErrorMsg("SE072", [__("期間")]);
                $this->Flash->set($errorMsg, array("key" => "Error"));
            }
        }
        if (empty($layer_code) && $page == '') {
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            if (empty($layer_code)) {
                $status = false;
                $errorMsg = $this->getErrorMsg("SE072", [__("部署")]);
                $this->Flash->set($errorMsg, array("key" => "Error"));
            }
        }
        if (!$status) {
            $this->redirect(array('controller' => 'StockSelections', 'action' => 'index'));
        }

        $Common = new CommonController();
        $permissions = $Common->getPermissionsByRole($id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        // $layers = array_keys($permissions['index']['layers']);

        // $Common->UrlSession('StockSelections', $layer_code, $period);
        // if((!in_array($layer_code, $layers)) || ($layer_code == "" && $permissions['index']['limit'] > 0)) {
        //     $errorMsg = $this->getErrorMsg('SE065');
        //     $this->Flash->set($errorMsg, array("key"=>"Error"));
        //     $this->redirect(array('controller'=>'StockSelections', 'action'=>'index'));
        // }
    }

    public function checkBuUrlSession($controllerName)
    {
        $this->checkUserStatus();
        #from url
        $period = $this->request->query('term_name');
        $bu = $this->request->query('bu_input');
        $group = $this->request->query('group_input');
        #from session
        $status = true;
        if (empty($period)) {
            $period = $this->Session->read('TERM_ID');
            if (empty($period)) {
                $status = false;
                $errorMsg = $this->getErrorMsg("SE072", [__("期間名")]);
                $this->Flash->set($errorMsg, array("key" => "BuError"));
            }
        }

        if ($controllerName !== "BuBudgetProgress") {

            if (empty($bu)) {
                $bu = $this->Session->read('SELECTED_BU');
                if (empty($bu)) {
                    $status = false;
                    $errorMsg = $this->getErrorMsg("SE072", [__("ビジネスユニット")]);
                    $this->Flash->set($errorMsg, array("key" => "BuError"));
                }
            }
            if (empty($group)) {
                $group = $this->Session->read('SELECTED_GROUP');
                if (empty($group)) {
                    $status = false;
                    $errorMsg = $this->getErrorMsg("SE072", [__("グループ")]);
                    $this->Flash->set($errorMsg, array("key" => "BuError"));
                }
            }
        }

        if (!$status) {
            $this->redirect(array('controller' => 'BUSelections', 'action' => 'index'));
        }

        #go to bu selection if not have read permissions
        $page_limitation = $this->Session->read('PAGE_LIMITATION');

        $readLimit = $controllerName . 'ReadLimit'; //LaborCostsReadLimit

        if ($page_limitation[$readLimit] === false) { // 0
            $error_msg = $this->getErrorMsg('SE065');
            $this->Flash->error($error_msg);
            $this->redirect(array('controller' => 'BUSelections', 'action' => 'index'));
        }

        #reset Session for target year and layer code
        if ($controllerName !== "LaborCosts" && $controllerName !== "LaborCostDetails") {
            $this->resetSession();
        }
    }

    /** 
     * check Session Data For Setting Management
     * @author Kaung Htet San
     * @param  $controllerName
     * @return void
     */
    public function checkSettingSession($controllerName)
    {

        $this->checkUserStatus(); #check user status first

        $commonController = new CommonController();

        $role_id = $this->Session->read('ADMIN_LEVEL_ID'); # Get login role id (18)
        $pagename = $this->request->params['controller']; # Get current Controller name (Users)

        /** Commented session storage for permission    
         * 
         *  $login_id = $this->Session->read('LOGIN_ID'); #1758
         * 
         *  $permissions = $commonController->getPermissionsByRole($login_id, $role_id, $pagename);
         *  $this->Session->write('PERMISSIONS', $permissions);
         */
        
        $menus = $commonController->getMenuByRoleWithoutLayout($role_id, $pagename);

        $settingMenuArrays = $this->settingMenuArrays; # Get the Setting Management Page list array

        # Filter and sort the order of user's accessable menu pages
        $sortedMenuArrays = array_intersect($settingMenuArrays, $menus);

        # Check if the current Controller Name is in the user's accessable meny page list or nots
        $checkHavePermissionOrNot = in_array($controllerName, $menus);

        if (!$checkHavePermissionOrNot) { # if not inlucded, redirect back with error message

            # this loop can replaces with hard codes with index zero, 
            # but I just don't want the hard codes and loop with check condition
            foreach ($sortedMenuArrays as $sortedMenu) {

                if ($sortedMenu) {

                    $this->redirect(array('controller' => $sortedMenu, 'action' => 'index'));
                    break;
                }
            }
        }
    }

    /** 
     * resetSession method
     * @author Hein Htet Ko
     * @return void
     *
     */
    public function resetSession()
    {
        $this->Session->write('SEARCH_LABOR_COST.target_year', $this->Session->read('BudgetTargetYear'));
        $this->Session->write('SEARCH_LABOR_COST.layer_code', $this->Session->read('SELECTED_GROUP'));
    }
}
