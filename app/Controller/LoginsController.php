<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * Logins Controller
 *
 * @property Login $Login
 * @property PaginatorComponent $Paginator
 */
class LoginsController extends AppController {

/**
 * Components
 *
 * @var array
 */
public $helpers = array('Html', 'Form', 'Session');
public $uses = array('User','LoginLog','Layer','PasswordHistory');
public $components = array('Session','Flash','RequestHandler');

	/**
	 * index Action
	 * Aye Zar Ni Kyaw
	 * @param NULL
	 */

	public function index($ssoInfo=array()) {
		$this->Session->write('SSO_INFO', $ssoInfo);
		$Common = new CommonController(); #To import CommonController
		$this->layout = "plain" ;
		
		#store language selection in cache
		Cache::write('lang', $this->Session->read('Config.language')??'jpn');
		
		if($ssoInfo['ssoFail']){
			$errorMsg = parent::getErrorMsg('SE120');
            $this->Flash->set($errorMsg, array("key"=>"loginError"));
			$this->redirect(array('controller' => 'Logins', 'action' => 'index'));
		}
		if(!empty($this->Session->read('SSO_INFO'))){
			#set language
			if (!empty($ssoInfo['Config.language'])) {
				$this->Session->write('Config.language', $ssoInfo['Config.language']);		
			}else{
				$this->Session->write('Config.language', 'jpn');
			}		
			#write sessions
			$login_codes = $this->User->find('list' , array(
				'fields' => array('login_code'),
				'conditions' => array(
					'flag' => 1,
					'email LIKE ' => '%'.$ssoInfo['name'].'%'
				),
				'order' => array('login_code')
			));
			if(!empty($login_codes)){
				$this->Session->write('SHOW_FLAG', 'false');
			}else{
				$test = true;
				$this->set('show_link', 'true');
				$this->set('lang', $ssoInfo['Config.language']);
				if($this->request->query('hang_out')==1){
					$this->Session->write('Config.language', $this->request->query('lang'));
					$this->redirect('/sso_web_app/module.php/core/authenticate.php?as=default-sp&logout');
				}else{
					$errorMsg = parent::getErrorMsg('SE120');
                	$this->Flash->set($errorMsg, array("key"=>"loginError"));
				}
			}			

		}
		$show_flag = ($this->Session->check('SHOW_FLAG'))? $this->Session->read('SHOW_FLAG') : 'true'; 
		if($show_flag != 'false'){
			if ($this->Session->read('Config.language') == "") {
				$this->Session->write('Config.language','jpn');
			}
			if ($this->request->is('post')) {
				
				$login_code = $this->request->data['login_code'];
				$password = md5($this->request->data['password']);
				
				if (strlen($login_code) <= 10 && strlen($password) <= 50) {
					// debug($login_code);exit;
					$user = $this->User->find('first', array(
						'conditions' => array('User.login_code' => $login_code,
											  'User.flag'  => 1,
											  'User.joined_date <= ' => date("Y-m-d"),
											  'User.resigned_date >= ' => date("Y-m-d"),
											),
											  'order' => 'id desc'

					));
				}
	
				//browser_get
				$user_agent = $_SERVER['HTTP_USER_AGENT'];
	
				$browser_name = '';
	
				if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) {
					$browser_name = 'Opera';
				} elseif (strpos($user_agent, 'Edge')) {
					$browser_name = 'Edge';
				} elseif (strpos($user_agent, 'Chrome')) {
					$browser_name = 'Chrome';
				} elseif (strpos($user_agent, 'Safari')) {
					$browser_name = 'Safari';
				} elseif (strpos($user_agent, 'Firefox')) {
					$browser_name = 'Firefox';
				} elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) {
					$browser_name = 'Internet Explorer';
				} else {
					$browser_name = 'Other';
				}
				//ip get
				$ip = $this->RequestHandler->getClientIp();
				if($login_code !== $user['User']['login_code']) {
					$errorMsg = parent::getErrorMsg('SE134');
					$this->Flash->set($errorMsg, array("key"=>"loginError"));
					return $this->redirect(array('controller' => 'Logins','action' => 'index'));
				} elseif($password !== $user["User"]["password"]) {
					$errorMsg = parent::getErrorMsg('SE005');
					$this->Flash->set($errorMsg, array("key"=>"loginError"));
					return $this->redirect(array('controller' => 'Logins','action' => 'index'));
				}

				do{
					//validate login_code and password
					if (!empty($user)) {
						
						//login_log table save
						$login_log = array(
							'login_code'  => $login_code,
							'ip'        => $ip,
							'browser'   => $browser_name,
							'status'    => 'success'
						);
						 
						$this->LoginLog->save($login_log);
						$this->Session->write('LOGIN_ID', $user ['User']['id']);
						$this->Session->write('LOGINID', $user ['User']['login_code']);
						$this->Session->write('LOGIN_USER', $user ['User']['user_name']);
						$this->Session->write('ADMIN_LEVEL_ID', $user ['User']['role_id']);
						$this->Session->write('ACCESS_TYPE', $user ['User']['access_type']);

						$Common = New CommonController();
        				$userPermission = $Common->getUserPermission($user['User']['role_id'], $user['User']['id']);
						$this->Session->write('PAGE_LIMITATION', $userPermission);
	
						# PanEiPhyo (20200423), get user permission and save to session
						// $permission = $Common->getUserPermission($user [0]['User']['role_id'], $user [0]['User']['id']);
	
						// $this->Session->write('PERMISSION', $permission);
						# End PanEiPhyo (20200423)
	
						$link = $this->Session->read("LINK");

						#to remove error,success message in user
						$this->Session->delete('Message');
						$this->Session->delete('Flash');
	
						if (!empty($link)) {
						   
							$this->redirect($link);
						} else {
							
							return $this->redirect(array('controller' => 'Menus','action' => 'index'));
						}
	
					} else {
						//login_log table save
						if (strlen($login_code) <= 10) {
							$login_log = array(
								'login_code'  => $login_code,
								'ip'        => $ip,
								'browser'   => $browser_name,
								'status'    => 'fail'
								);
							$this->LoginLog->save($login_log);
						}
						
						$conditions["PasswordHistory.login_code"]  = $login_code;
						$conditions["PasswordHistory.status"]      = 1;
	
						$pw_data = $this->PasswordHistory->find('first',
											array('conditions' => $conditions)
											);
					   
						$errorMsg = parent::getErrorMsg('SE005');
	
						if(!empty($pw_data)){
							if(!empty($pw_data['PasswordHistory']['remark'])){
	
								$this->Session->write('LOGINID', $pw_data['PasswordHistory']['login_code']);
								$this->redirect(array('controller' => 'Users', 'action' => 'ResetPassword'));
	
							}else{
	
								if($this->Session->check('ATTEMPT')){
									$attempt = $this->Session->read('ATTEMPT');
									
									if($attempt > 4){
	
										$expire_date = Date('Y-m-d');
										$update = array('id'=> $pw_data['PasswordHistory']['id'],
														'expire_date'=> $expire_date,
														'remark' => 'login_attempt_fail');
										$this->PasswordHistory->save($update);
									   
										$this->Session->write('LOGINID', $pw_data['PasswordHistory']['login_code']);
										$this->redirect(array('controller' => 'Users', 'action' => 'ResetPassword'));
									}else{										
										$this->Flash->set($errorMsg, array("key"=>"loginError"));
									}
									$attempt++;
									
								}else{
									$attempt = 2;
									$this->Flash->set($errorMsg, array("key"=>"loginError"));
								}
								$this->Session->write('ATTEMPT', $attempt);
							   
							}
						}else{
							$this->Flash->set($errorMsg, array("key"=>"loginError"));
							$this->redirect(array('controller' => 'Logins', 'action' => 'index'));
						}
						
					}
	
				}while (0);
	
			}
		}else{
			if($this->Session->check('SSO_LOGIN_CODE')){
				$this->goSSOUser($this->Session->read('SSO_LOGIN_CODE'));
			}
			$this->set('count_code', count($login_codes));
			if(count($login_codes) == 1){
				$this->set('login_code', array_shift(array_values($login_codes)));
			}
			$this->set('login_codes', $login_codes);

		}
		$this->render('index');		
	}
   
	/**
	 * Logout Action
	 * Aye Zar Ni Kyaw
	 * @param NULL
	 */
	public function Logout($sso=null)
	{
		$this->Session->destroy();
		if($sso == 'sso'){
			$prepare['ssoFail'] = true;
			$this->index($prepare);	
		}else{
			$this->redirect(
				array(
					'controller' => '/',
					'action' => 'index'
				)
			);
		}
	}
	 /**
	* Login with SSO
	* @author Hein Htet Ko
	* @param NULL
	*/
	public function ssoLogin()
	{
		try{
			require_once ('../webroot/simplesaml/lib/_autoload.php');
			$as = new SimpleSAML_Auth_Simple('default-sp');
			$as->requireAuth();

			$attributes = $as->getAttributes();
			$prepare = array();
			foreach($attributes as $key=>$value){
				$exploded = explode('/', $key);
				if(count($value) > 1) $prepare[end($exploded)] = $value;
				else $prepare[end($exploded)] = $value[0];
				
			}
			$prepare['sso_flag'] = 'yes';
			#read language from cache file
			$lang = Cache::read('lang');
			$language = empty($this->request->query('lang'))? $lang : $this->request->query('lang');
			$prepare['Config.language'] = $language;
		}catch(Exception $e){
			$errorMsg = parent::getErrorMsg('SE145');
            $this->Flash->set($errorMsg, array("key"=>"loginError"));
			CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
			return $this->redirect(array('controller' => 'Logins','action' => 'index'));
		}
		$this->index($prepare);		
		
	}

	public function getViewData(){
		$login_code = $this->request->data('login_code');
		$this->Session->write('SHOW_FLAG', 'false');
		$this->Session->write('SSO_LOGIN_CODE', $login_code);
		$this->index();
	}

     /**
    * validated SSO user login
    * @author Hein Htet Ko
    * @param login_code
    */
    public function goSSOUser($login_code){
        #get user data and permission by object_id
        $sso_user = $this->User->find(
			'first',
            array(
				'conditions' => array('login_code' => $login_code, 'flag' => 1),
				)
		);

		if($login_code !== $sso_user['User']['login_code']) {
			$errorMsg = parent::getErrorMsg('SE134');
			$this->Flash->set($errorMsg, array("key"=>"loginError"));
			return $this->redirect(array('controller' => 'Logins','action' => 'index'));
		} 
		
		if (!empty($sso_user)) {
            #get ip
            $ip = $this->RequestHandler->getClientIp();
            #get browser name
            $browser_name = $this->getBrowserName();
            $login_log = array(
                'login_code'  => $sso_user['User']['login_code'],
                'ip'        => $ip,
                'browser'   => $browser_name,
                'status'    => 'success'
            );
            $this->LoginLog->save($login_log);
            $this->Session->write('LOGIN_ID', $sso_user['User']['id']);
            $this->Session->write('LOGINID', $sso_user['User']['login_code']);
            $this->Session->write('LOGIN_USER', $sso_user['User']['user_name']);
            $this->Session->write('ADMIN_LEVEL_ID', $sso_user['User']['role_id']);
            $this->Session->write('ACCESS_TYPE', $sso_user['User']['access_type']);

            #get link that clicked before login
            $link =$this->Session->read("LINK");
			#to remove error,success message in user
			$this->Session->delete('Message');
			$this->Session->delete('Flash');
            if (!empty($link)) {
                return $this->redirect($link);
            } else {			
                return $this->redirect(array('controller' => 'Menus','action' => 'index'));
            }
        } else {
            #will forget if login user is not in azure user else will not forget(need to change object id in db)
            if(!empty($login_code)){
				$errorMsg = parent::getErrorMsg('SE120');
                $this->Flash->set($errorMsg, array("key"=>"loginError"));
            }
            return $this->redirect(array('controller' => 'Logins','action' => 'index'));
        }
    }

	/**
	* Get Browser Name
	* @author Hein Htet Ko
	* @param NULL
	* @return browser_name
	*/
	public function getBrowserName()
	{
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$browser_name = '';

		if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) {
			$browser_name = 'Opera';
		} elseif (strpos($user_agent, 'Edge')) {
			$browser_name = 'Edge';
		} elseif (strpos($user_agent, 'Chrome')) {
			$browser_name = 'Chrome';
		} elseif (strpos($user_agent, 'Safari')) {
			$browser_name = 'Safari';
		} elseif (strpos($user_agent, 'Firefox')) {
			$browser_name = 'Firefox';
		} elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) {
			$browser_name = 'Internet Explorer';
		} else {
			$browser_name = 'Other';
		}
		return $browser_name;
	}
}

