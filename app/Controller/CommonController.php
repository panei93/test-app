<?php

/**
 * Author: Pan Ei Phyo
 * Common functions for all controller
 */
App::uses('AppController', 'Controller');
App::import('Controller', 'Permissions');
App::import('Controller', 'BrmTradingPlan');
App::import('Controller', 'BrmManpowerPlan');
App::import('Controller', 'BrmBudgetPlan');

class CommonController extends AppController
{
    public $uses = array(
        'User', 'Sample', 'Menu', 'SampleAccAttachment', 'Mail',
        'MailReceiver', 'SampleBusiAdminApprove', 'Layer', 'SampleAccRequest', 'SampleBusiManagerRequest',
        'BrmTerm', 'Permission', 'BrmMrApprove', 'RtaxFee',
        'FileAssetEvent', 'History', 'BrmField', 'BrmPosition', 'BrmLogistic',
        'BrmTermDeadline', 'BrmAccount', 'BrmSaccount', 'BrmAccountSetup', 'Sap',
        'BrmPosition', 'LayerType', 'BrmTermDeadline', 'BrmBudget', 'AccountSetting', 'Account', 'Sap', 'Sample', 'Stock', 'BuTerm', 'LcComment'
    );
    public $components = array('Session', 'Flash');
    public $helpers = array('Html', 'Form');

    public function getSalesManagerEmail($layer_code, $manager_level_id, $period = '')
    {
        $data = array();

        $searchID = $this->Layer->searchManagerIDInBA($layer_code, $period);

        if (empty($searchID)) {
            $data["msgCode"] = "SE058";
            $data["error"] = 1;
        } else {
            //match BA Code and Login ID
            $match_count = $this->User->find('count', array(
                'conditions' => array(
                    'login_id' => $searchID[0]["tbl_business_area"]["managers"],
                    'role_id' => $manager_level_id,
                    'layer_code' => $layer_code,
                    'flag' => 1
                )
            ));
            if ($match_count < 1) {
                $exact = $this->User->find('all', array(
                    'fields' => 'User.email',
                    'conditions' => array(
                        'role_id' => $manager_level_id,
                        'login_id' => $searchID[0]["tbl_business_area"]["managers"],
                        'flag' => 1
                    )
                ));
                $other = $this->User->find('all', array(
                    'fields' => 'DISTINCT (User.email)',
                    'conditions' => array(
                        'role_id' => $manager_level_id,
                        'layer_code' => $layer_code,
                        'flag' => 1
                    )
                ));
                $emails = array_merge($exact, $other);
            } else {
                $emails = $this->User->find('all', array(
                    'fields' => 'DISTINCT (User.email)',
                    'conditions' => array(
                        'role_id' => $manager_level_id,
                        'layer_code' => $layer_code,
                        'flag' => 1
                    )
                ));
            }
            foreach ($emails as $email) {
                $mail = $email['User']['email'];
                if ($mail == "" || empty($mail)) {
                    $data["msgCode"] = "SE059";
                    $data["error"] = 1;
                    return $data;
                } else {
                    array_push($data, $mail);
                }
            }
        }
        return $data;
    }

    public function getUserEmail($layer_code, $level_id)
    {
        $data = array();
        $eachMail = array();
        $emails = $this->User->find('list', array(
            'fields' => array('User.login_id', 'User.email'),
            'conditions' => array(
                'role_id' => $level_id,
                'layer_code' => $layer_code,
                'flag' => 1
            )
        ));

        if (count($level_id) > 1) {
            foreach ($level_id as $eachId) {
                if ($eachId == 2 || $eachId == 3 || $eachId == 4) {
                    $eachMail = $this->User->find('list', array(
                        'fields' => array('User.login_id', 'User.email'),
                        'conditions' => array(
                            'role_id' => $eachId,
                            'access_type' => array(1, 2, ''),
                            'flag' => 1
                        )
                    ));
                }
                $emails = array_merge($emails, $eachMail);
            }
        } elseif ($level_id == 2 || $level_id == 3 || $level_id == 4) {
            $eachMail = $this->User->find('list', array(
                'fields' => array('User.login_id', 'User.email'),
                'conditions' => array(
                    'role_id' => $level_id,
                    'access_type' => array(1, 2, ''),
                    'flag' => 1
                )
            ));
            $emails = array_merge($emails, $eachMail);
        }

        if (empty($emails)) {
            $emails = $this->User->find('list', array(
                'fields' => array('User.login_id', 'User.email'),
                'conditions' => array(
                    'role_id' => $level_id,
                    'flag' => 1
                )
            ));
        }
        $emails = array_filter($emails);
        if (isset($emails) || !empty($emails)) {
            $data = array_values(array_unique($emails));
            return $data;
        } else {
            return false;
        }
    }

    /** for phase_3
     * getEmail method
     * @author Khin Hnin Myo
     * @param  $level_id
     * @return $data
     *
     */
    public function getEmail($level_id)
    {
        $eachMail = array();
        $emails = array();


        if (count($level_id) > 1) {
            foreach ($level_id as $eachId) {
                if ($eachId == 2 || $eachId == 3 || $eachId == 4) {
                    $eachMail = $this->User->find('list', array(
                        'fields' => 'email',
                        'conditions' => array(
                            'role_id' => $eachId,
                            'access_type' => array(1, 3),
                            'flag' => 1
                        )
                    ));
                } else {
                    $eachMail = $this->User->find('list', array(
                        'fields' => 'email',
                        'conditions' => array(
                            'role_id' => $eachId,
                            'flag' => 1
                        )
                    ));
                }
                $emails = array_merge($emails, $eachMail);
            }
        } else {
            if ($level_id == 2 || $level_id == 3 || $level_id == 4) {
                $eachMail = $this->User->find('list', array(
                    'fields' => 'email',
                    'conditions' => array(
                        'role_id' => $level_id,
                        'access_type' => array(1, 3),
                        'flag' => 1
                    )

                ));
                $emails = array_merge($emails, $eachMail);
            } else {
                $eachMail = $this->User->find('list', array(
                    'fields' => 'email',
                    'conditions' => array(
                        'role_id' => $level_id,
                        'flag' => 1
                    )
                ));

                $emails = array_merge($emails, $eachMail);
            }
        }

        $emails = array_unique(array_filter($emails));

        return array_unique($emails);
    }

    /** for phase_3
     * getEmail method
     * @author PanEiPhyo (20200811)
     * @param  $level_id
     * @return $email
     *
     */
    public function getAllEmails($level_id)
    {
        $emails = array();

        # Get admin levels from setting file
        $level_2 = AdminLevel::ACCOUNT_MANAGER;
        $level_3 = AdminLevel::ACCOUNT_SECTION_MANAGER;
        $level_4 = AdminLevel::ACCOUNT_INCHARGE;
        $level_7 = AdminLevel::BUSINESS_INCHARGE;
        $level_10 = AdminLevel::BUDGET_INCHARGE;

        $today = date("Y-m-d");
        $disabled_bas = $this->Layer->find('list', array(
            'fields' => 'layer_code',
            'conditions' => array(
                'OR' => array(
                    'from_date >' => $today,
                    'to_date <' => $today,
                    'from_date' => '',
                    'to_date' => '',
                ),
                'flag' => 1
            )
        ));

        $mails = $this->User->find('list', array(
            'fields' => array('login_id', 'email'),
            'conditions' => array(
                'flag' => 1,
                'role_id' => $level_id,
                'NOT' => array(
                    'layer_code' => $disabled_bas
                ),
                'OR' => array(
                    array(
                        'role_id' => $level_10,
                        'email !=' => '',
                        # do not get the null mail for budget incharge
                    ),
                    array(
                        'role_id' => array($level_2, $level_3, $level_4),
                        'access_type' => array(1, 3),
                        # need to check access type in admin level 1,2,3
                    ),
                    array(
                        'NOT' => array(
                            'role_id' => array($level_2, $level_3, $level_4, $level_7, $level_10),
                        )
                        #Sales Incharge(7) is not relate in Phase 3
                    ),
                ),
            ),
            'order' => array('role_id ASC', 'login_id ASC')
        ));

        $data['no_mail'] = array_keys($mails, ""); #get login ids of null mail
        $data['emails'] = array_unique(array_filter($mails)); #get not null and unique mail

        return $data;
    }

    /** for phase_3
     * getEmailByBA method
     * @author PanEiPhyo (20200610)
     * @param  $layer_code,$level_id
     * @return $data
     *
     */
    public function getEmailByBA($layer_code, $level_id)
    {
        $eachMail = array();
        $emails = array();

        if (count($level_id) > 1) {
            foreach ($level_id as $eachId) {
                if ($eachId == 2 || $eachId == 3 || $eachId == 4) {
                    $eachMail = $this->User->find('list', array(
                        'fields' => 'email',
                        'conditions' => array(
                            'role_id' => $eachId,
                            'access_type' => array(1, 3),
                            'layer_code' => $layer_code,
                            'flag' => 1
                        )
                    ));
                } else {
                    $eachMail = $this->User->find('list', array(
                        'fields' => 'email',
                        'conditions' => array(
                            'role_id' => $eachId,
                            'layer_code' => $layer_code,
                            'flag' => 1
                        )
                    ));
                }
                $emails = array_merge($emails, $eachMail);
            }
        } else {
            if ($level_id == 2 || $level_id == 3 || $level_id == 4) {
                $eachMail = $this->User->find('list', array(
                    'fields' => 'email',
                    'conditions' => array(
                        'role_id' => $level_id,
                        'access_type' => array(1, 3),
                        'layer_code' => $layer_code,
                        'flag' => 1
                    )

                ));
                $emails = array_merge($emails, $eachMail);
            } else {
                $eachMail = $this->User->find('list', array(
                    'fields' => 'email',
                    'conditions' => array(
                        'role_id' => $level_id,
                        'layer_code' => $layer_code,
                        'flag' => 1
                    )
                ));

                $emails = array_merge($emails, $eachMail);
            }
        }

        $emails = array_unique(array_filter($emails));

        return array_unique($emails);
    }

    public function getAllEmailsByBA($layer_code)
    {
        $mails = $this->User->find('list', array(
            'conditions' => array(
                'flag' => 1,
                'layer_code' => $layer_code, //8000='財務経理部'
                'OR' => array(
                    array(
                        'role_id' => array(2, 3, 4),
                        'access_type' => array(1, 3),
                    ),
                    array(
                        'role_id !=' => array(2, 3, 4, 7), #Sales Incharge is not relate in Phase 3
                    )
                )
            ),
            'fields' => array('login_id', 'email')
        ));

        return $mails;
    }

    public function getAllBAOfSameHQ($head_dept_code)
    {
        $today_date = date("Y/m/d");
        $layer_list = $this->Layer->find('list', array(
            'fields' => 'layer_code',
            'conditions' => array(
                'Layer.flag'         => 1,
                'type_order'   => SETTING::LAYER_SETTING['bottomLayer'],
                "Layer.parent_id LIKE CONCAT('%\"L', " . Setting::LAYER_SETTING['topLayer'] . ", '\":\"'," . $head_dept_code . ",'\"%')",
                'to_date >='   => $today_date,
            )
        ));
        return $layer_list;
    }

    /** for phase_3
     * getEmailByLoginID method
     * @author PanEiPhyo (20200610)
     * @param  $login_id
     * @return $data
     *
     */
    public function getEmailByLoginID($login_id)
    {
        $emails = $this->User->find('list', array(
            'fields' => 'email',
            'conditions' => array(
                'login_id' => $login_id,
                'flag' => 1
            )
        ));


        $emails = array_unique(array_filter($emails));

        return $emails;
    }

    /** for phase_3
     * getEmailExceptCurrent method
     * @author PanEiPhyo (20200610)
     * @param  $login_id
     * @return $data
     *
     */
    public function getEmailExceptCurrent($level_id, $current_user_id)
    {
        $emails = $this->User->find('list', array(
            'fields' => 'email',
            'conditions' => array(
                'role_id' => $level_id,
                'login_id <>' => $current_user_id,
                'flag' => 1
            )
        ));


        $emails = array_unique(array_filter($emails));

        return $emails;
    }

    /** for phase_3
     * getEmailByDept method
     * @author PanEiPhyo (20200528)
     * @param  $layer_code
     * @return $emails
     *
     */
    public function getEmailByDept($departments)
    {
        $data = array();
        $emails = array();

        $ba_by_dept = $this->Layer->find('list', array(
            'fields' => 'layer_code',
            'conditions' => array(
                'department' => $departments,
                'flag' => 1
            )
        ));
        $emails = $this->User->find('list', array(
            'fields' => 'email',
            'conditions' => array(
                'layer_code' => $ba_by_dept,
                'flag' => 1
            )
        ));
        if (isset($emails)) {
            return array_unique(array_filter($emails));
        } else {
            return false;
        }
    }

    /** for phase_3
     * getEmail same department by layer_code
     * @author Nu Nu Lwin
     * @param  $head_id,$dept_id,$level_id
     * @return $data
     * @11/05/2020
     */
    public function getEmailSameDept($head_id, $dept_id, $level_id)
    {
        $data = array();
        $eachMail = array();
        $emails = array();

        if ($dept_id != 0) {
            # get layer_code by headquarter id
            $getBa = $this->Layer->find('list', array(
                'fields' => 'layer_code',
                'conditions' => array(
                    'head_dept_id' => $head_id,
                    'dept_id' => $dept_id,
                    'flag' => 1
                )
            ));
        } else {
            # get layer_code by headquarter id
            $getBa = $this->Layer->find('list', array(
                'fields' => 'layer_code',
                'conditions' => array(
                    'head_dept_id' => $head_id,
                    'flag' => 1
                )
            ));
        }

        if (count($level_id) > 1) {
            foreach ($level_id as $eachId) {
                if ($eachId == 2 || $eachId == 3 || $eachId == 4) {
                    $eachMail = $this->User->find('list', array(
                        'fields' => 'DISTINCT (User.email)',
                        'conditions' => array(
                            'role_id' => $eachId,
                            'layer_code' => $getBa,
                            'access_type' => array(1, 3),
                            'flag' => 1
                        )
                    ));
                } else {
                    $eachMail = $this->User->find('list', array(
                        'fields' => 'DISTINCT (User.email)',
                        'conditions' => array(
                            'role_id' => $eachId,
                            'layer_code' => $getBa,
                            'flag' => 1
                        )
                    ));
                }

                $emails = array_merge($emails, $eachMail);
            }
        } else {
            if ($level_id == 2 || $level_id == 3 || $level_id == 4) {
                $eachMail = $this->User->find('list', array(
                    'fields' => 'DISTINCT (User.email)',
                    'conditions' => array(
                        'role_id' => $level_id,
                        'access_type' => array(1, 3),
                        'layer_code' => $getBa,
                        'flag' => 1
                    )

                ));
                $emails = array_merge($emails, $eachMail);
            } else {
                $eachMail = $this->User->find('list', array(
                    'fields' => 'DISTINCT (User.email)',
                    'conditions' => array(
                        'role_id' => $level_id,
                        'layer_code' => $getBa,
                        'flag' => 1
                    )
                ));
                $emails = array_merge($emails, $eachMail);
            }
        }

        $emails = array_unique(array_filter($emails));

        return array_unique($emails);
    }

    /**
     * Calculate month
     * @author PanEiPhyo (20200228)
     * @param $target_month
     * @return $month_col
     *
     */
    public function getMonthColumn($target_month, $term_id)
    {
        # Get term data by id
        $term_data = $this->BrmTerm->find('first', array(
            'fields' => array('start_month', 'end_month'),
            'conditions' => array(
                'id' => $term_id
            )
        ));
        # Get start month
        $start_month = $term_data['BrmTerm']['start_month'];
        # Get end month
        $end_month   = $term_data['BrmTerm']['end_month'];

        # Return target month if start month is 1 (Because number of target month and database column name will be same if start_month is 1)
        if ($start_month == 1) {
            return $target_month;
        } else {
            # Add 12(month) to protect minus number return.
            if ($target_month < $start_month) {
                $target_month = $target_month + 12;
            }
            return $target_month - $end_month;
        }
    }

    /**
     * Get month
     * @author PanEiPhyo (20200323)
     * @param $tartet_year, $term_id, $type(start or end)
     * @return start_month or end_month
     *
     */
    public function getMonth($target_year, $term_id, $type)
    {
        $month = '';
        # Get term data by id
        $term_data = $this->BrmTerm->find('first', array(
            'fields' => array('start_month', 'end_month'),
            'conditions' => array(
                'id' => $term_id
            )
        ));

        if ($type == 'start') {
            $month =  $term_data['BrmTerm']['start_month'];
        } else {
            $month =  $term_data['BrmTerm']['end_month'];
            #if($month < 12 ):eg - sm = 2022-01=>em = 2022-12 (no need to +1)
            if ($month < 12) $target_year = $target_year + 1;
        }
        $month = date("Y-m", strtotime($target_year . "-" . $month));
        return $month;
    }

    public function getUserPermission($user_level, $user_id)
    {
        $Permission = new PermissionsController();
        $data     = $Permission->getUserPermission($user_level, $user_id);

        return $data;
    }

    /**
     * get start month
     * @author Nu Nu Lwin (20200710)
     * @param $term_id
     * @return $start_month
     *
     */
    public function get12Month($term_id)
    {

        # Get term data by id
        $term_data = $this->BrmTerm->find('first', array(
            'fields' => array('start_month', 'end_month'),
            'conditions' => array(
                'id' => $term_id,
                'flag' => 1
            )
        ));

        # Get start month
        $start_month = $term_data['BrmTerm']['start_month'] - 1;
        $end_month   = $term_data['BrmTerm']['end_month'];

        $month_12 = array("1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月");

        $split = array_search($month_12[$start_month], $month_12);

        $a = array_slice($month_12, 0, $split);   // first part
        $b = array_slice($month_12, $split);

        return array_merge($b, $a);
    }
    /**
     * get start month
     * @author Aye Zar Ni Kyaw (20201026)
     * @param $term_id
     * @return $start_month
     *
     */
    public function get12DigitMonth($term_id)
    {

        # Get term data by id
        $term_data = $this->BrmTerm->find('first', array(
            'fields' => array('start_month', 'end_month'),
            'conditions' => array(
                'id' => $term_id,
                'flag' => 1
            )
        ));

        # Get start month
        $start_month = $term_data['BrmTerm']['start_month'] - 1;
        $end_month   = $term_data['BrmTerm']['end_month'];

        $month_12 = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");

        $split = array_search($month_12[$start_month], $month_12);

        $a = array_slice($month_12, 0, $split);   // first part
        $b = array_slice($month_12, $split);

        return array_merge($b, $a);
    }
    #check limit for save and approve
    public function checkLimit($limit, $layer_code, $user_id, $permission)
    {
        switch ($limit) {
            case '0':
                return 'true';
            case '1':
                if (in_array($layer_code, $this->getBACollection($user_id, $permission, $limit))) {
                    return 'true';
                } else {
                    return 'flase';
                }
                // no break
            case '2':
                if (in_array($layer_code, $this->getBACollection($user_id, $permission, $limit))) {
                    return 'true';
                } else {
                    return 'flase';
                }
                // no break
            case '3':
                if (in_array($layer_code, $this->getBACollection($user_id, $permission, $limit))) {
                    return 'true';
                } else {
                    return 'flase';
                }
                // no break
            default:
                return 'flase';
        }
    }

    #to get head dept and dept
    public function getBACollection($user_id, $permission, $limit)
    {
        $today_date = date("Y/m/d");

        $hq = array_column($permission['index']['parent_data'], 'L' . SETTING::LAYER_SETTING['topLayer']);
        $dep = array_column($permission['index']['parent_data'], 'L' . SETTING::LAYER_SETTING['middleLayer']);
        $ba =  array_column($permission['index']['parent_data'], 'L' . SETTING::LAYER_SETTING['bottomLayer']);
        $toDate = date("Y-m-d");

        if ($limit == 1) {
            $layer_collect = $this->BrmBudget->checkQuater($hq, $limit, $toDate);
            $layer_collection = array_column(array_column($layer_collect, 'Layers'), 'layer_code');
            return $layer_collection;
        } elseif ($limit == 2) {
            $layer_collect = $this->BrmBudget->checkQuater($dep, $limit, $toDate);
            $layer_collection = array_column(array_column($layer_collect, 'Layers'), 'layer_code');
            return $layer_collection;
        } else {
            $layer_collect = $this->BrmBudget->checkQuater($ba, $limit, $toDate);
            $layer_collection = array_column(array_column($layer_collect, 'Layers'), 'layer_code');
            return $layer_collection;
        }
    }

    public function getCodesPair($group_code, $sub_group_code = 'null')
    {
        $code_array = array();
        $codes_pair = array();

        # for sub account loop
        $sub_accs = $this->SubAccountModel->find('list', array(
            'fields' => array('sub_acc_name_jp'),
            'conditions' => array(
                'group_code' => $group_code,
                'flag' => 1
            )
        ));

        foreach ($sub_accs as $id => $sub_acc_name) {
            $conditions = array();

            if ($group_code != '01') {
                $conditions = array(
                    'AccountModel.pair_ids LIKE' => '%:' . $id . ',%',
                    'AccountModel.flag' => 1
                );
            } else {
                $conditions = array(
                    'AccountModel.sub_acc_id' => $id,
                    'AccountModel.flag' => 1
                );
            }

            $acc_codes = $this->AccountModel->find('list', array(
                'fields' => array('AccountModel.account_code'),
                'conditions' => $conditions
            ));

            if (empty($acc_codes)) {
                $codes_pair[$sub_acc_name] = $code_array;
            } else {
                $code_array = array_merge($code_array, array_values($acc_codes));
                if ($sub_acc_name == '当期利益' || $sub_acc_name == '税引後利益') {
                    $codes_pair[$sub_acc_name] = $code_array;
                } else {
                    $codes_pair[$sub_acc_name] = array_values($acc_codes);
                }
            }
        }

        return $codes_pair;
    }

    public function getPairedAccount($hqDepCode, $year)
    {
        $pair_accounts = array();

        # Get sub accounts of group 01 with display order
        // $account = $this->Layer->getAccountByLayer($head_dept_id, $year);

        $account = $this->BrmAccount->getAccountByHeadQuarter($hqDepCode, $year);

        # Get groub 02 sub accounts (for FBD page)
        $acc_data_g2 = $this->BrmAccount->find('list', array(
            'fields' => array('id', 'name_jp'),
            'conditions' => array(
                'BrmAccount.flag' => 1,
                'BrmAccount.group_code' => '02',
            ),
        ));

        # Get pair ids to pair two groups
        $account_pairs = $this->BrmSaccount->find('list', array(
            'fields' => array('brm_account_id', 'pair_ids'),
            'conditions' => array(
                'BrmSaccount.flag' => 1
            ),
        ));

        foreach ($account as $acc_data) {
            $id = $acc_data['brm_accounts']['id'];
            $acc_name = $acc_data['brm_accounts']['name_jp'];
            $type = $acc_data['brm_accounts']['type'];

            // foreach ($account as $acc_data) {
            //     $id = $acc_data['brm_accounts']['id'];
            //     $sub_acc_name = $acc_data['brm_accounts']['name_jp'];
            //     $type = $acc_data['brm_accounts']['type'];

            if ($type <= 1 && $acc_name != '＊販売管理費＊') {
                $id_pair = json_decode($account_pairs[$id], 1); #decoding json
                $pair_id = $id_pair['02']; #get group 2 id from pair

                #set data pair
                // $pair_accounts[$sacc_name] = (!empty($acc_data_g2[$pair_id])) ? $acc_data_g2[$pair_id] : $acc_name;
                $pair_accounts[$acc_name] = (!empty($acc_data_g2[$pair_id])) ? $acc_data_g2[$pair_id] : $acc_name;
            }
        }

        return $pair_accounts;
    }
    /**
     * get tax value by year
     * @author Ei Thandar Kyaw (20210210)
     * @param $preTaxProfit array
     * @return $year
     *
     */
    public function getTaxValue($preTaxProfit, $year)
    {
        $taxAmount = $this->RtaxFee->find('list', array(
            'fields' => array('target_year', 'rate'),
            'conditions' => array('target_year' => $year, 'flag' => 1)
        ));
        $tax = $taxAmount[$year];
        foreach ($preTaxProfit as $key => $value) {
            $profit = preg_replace("/[^-0-9\.]/", "", $value);
            $taxValue[$key] = ($profit < 0) ? (floor(-$profit * $tax)) : (-floor($profit * $tax));
        }
        return $taxValue;
    }

    public function getTargetYearByMonth($target_month, $term_id)
    {
        $year = date("Y", strtotime($target_month));
        $start_month = $this->getMonth($year, $term_id, 'start');
        $end_month = $this->getMonth($year, $term_id, 'end');
        $last_year = date("Y", strtotime($start_month . "last day of - 1 year"));

        $target_year = ($target_month >= $start_month && $target_month <= $end_month) ? $year : $last_year;

        return $target_year;
    }

    /**
     * CheckRefEvent
     * @author khinhninmyo(2021/06/17)
     * @return ids
     * for phase_4
     **/
    public function CheckRefEvent($event_id, $ref = "")
    {
        $ids = array();
        $conditions = array();

        $conditions['FileAssetEvent.flag <>'] = 0;
        if ($ref != "") {
            $conditions['FileAssetEvent.reference_event_id'] = $event_id;
        } else {
            $conditions['FileAssetEvent.id'] = $event_id;
        }

        $ref_id = $this->FileAssetEvent->find('first', array(
            'fields' => array('id', 'reference_event_id'),
            'conditions' => $conditions
        ))['FileAssetEvent']['reference_event_id'];
        $ids['event'] = $event_id;
        if ($ref_id != '') {
            $ids['ref'] = $ref_id;
        }

        return $ids;
    }
    /**
     * save history
     * @author EiThandarKyaw(2022/05/16)
     * @return true
     **/
    // public function saveHistory($data, $modelName)
    // {   
    //     pr(count($data));
    //     pr(count($data, COUNT_RECURSIVE));
    //     pr(count($data) == count($data, COUNT_RECURSIVE));
    //     if (count($data) == count($data, COUNT_RECURSIVE)) {
    //         pr($modelName);
    //         pr($data['id']);
    //         die();
    //         $oldData = $this->$modelName->find('all', array(
    //             'conditions' => array(
    //                 $modelName.'.id' => $data['id'],
    //                 $modelName.'.flag' => 1
    //                 )
    //             ));
    //         pr($oldData);die();
    //         foreach ($data as $key=>$value) {
    //             if ($key != 'id' && $key != 'org_id' && $key != 'page_name' && $key != 'created_by' && $key != 'created_date'  && $key != 'table_name') {
    //                 if (array_key_exists($key, $oldData[0][$modelName]) && $value != $oldData[0][$modelName][$key]) {
    //                     $changeValueArr[$key]['old_value'] = $oldData[0][$modelName][$key];
    //                     $changeValueArr[$key]['new_value'] = $value;
    //                 }
    //             }
    //         }
    //         $i = 0;
    //         if (sizeof($changeValueArr) > 0) {
    //             foreach ($changeValueArr as $key=>$value) {
    //                 $hisArr[$i]['org_id'] = $data['id'];
    //                 $hisArr[$i]['page_name'] = $data['page_name'];
    //                 $hisArr[$i]['table_name'] = $data['table_name'];
    //                 $hisArr[$i]['changed_column'] = $key;
    //                 $hisArr[$i]['old_value'] = $changeValueArr[$key]['old_value'];
    //                 $hisArr[$i]['new_value'] = $changeValueArr[$key]['new_value'];
    //                 $hisArr[$i]['created_by'] = $data['created_by'];
    //                 $hisArr[$i]['created_date'] = $data['created_date'];
    //                 $i++;
    //             }
    //             //echo '<pre>';print_r($hisArr);echo '</pre>';exit;
    //         }
    //     } else {

    //         foreach ($data as $dKey=>$dValue) {
    //             if ($modelName == 'BrmTermDeadline') {
    //                 $condition[$modelName.'.brm_term_id'] = $dValue['id'];
    //                 $condition[$modelName.'.hlayer_code'] = $dValue['head_department_id'];
    //             } else {
    //                 $condition[$modelName.'.id'] = $dValue['id'];
    //                 $condition[$modelName.'.flag'] = 1;
    //             }
    //             $oldData[] = $this->$modelName->find('all', array(
    //                 'conditions' => $condition
    //             ))[0][$modelName];

    //             pr($dValue);
    //             foreach ($dValue as $key=>$value) {
    //                 if ($key != 'id' && $key != 'org_id' && $key != 'page_name' && $key != 'created_by' && $key != 'created_date' && $key != 'table_name' && $key != 'head_department_id') {
    //                     if ($modelName == 'BrmTermDeadline') {
    //                         if (array_key_exists($key, $oldData[0]) && strtotime($value) != strtotime($oldData[$dKey][$key])) {
    //                             $changeColumn = $key;
    //                             $changeValueArr[$dKey][$key]['old_value'] = $oldData[$dKey][$key];
    //                             $newValue = str_replace("/", "-", $value);
    //                             $changeValueArr[$dKey][$key]['new_value'] = $newValue.' 00:00:00';
    //                         }
    //                     } else {
    //                         if (array_key_exists($key, $oldData[0]) && $value != $oldData[$dKey][$key]) {
    //                             $changeColumn = $key;
    //                             $changeValueArr[$dKey][$key]['old_value'] = $oldData[$dKey][$key];
    //                             $changeValueArr[$dKey][$key]['new_value'] = $value;
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         //echo '<pre>old';print_r($oldData);echo '</pre>';exit;
    //         $i = 0;
    //         pr($changeValueArr);
    //         if (sizeof($changeValueArr) > 0) {
    //             foreach ($changeValueArr as $key=>$value) {
    //                 if ($modelName == 'BrmTermDeadline') {
    //                     $data[$key]['id'] = $oldData[$key]['id'];
    //                 }
    //                 $hisArr[$i]['org_id'] = $data[$key]['id'];
    //                 $hisArr[$i]['page_name'] = $data[$key]['page_name'];
    //                 $hisArr[$i]['table_name'] = $data[$key]['table_name'];
    //                 $hisArr[$i]['changed_column'] = $changeColumn;
    //                 $hisArr[$i]['old_value'] = $value[$changeColumn]['old_value'];
    //                 $hisArr[$i]['new_value'] = $value[$changeColumn]['new_value'];
    //                 $hisArr[$i]['created_by'] = $data[$key]['created_by'];
    //                 $hisArr[$i]['created_date'] = $data[$key]['created_date'];
    //                 $i++;
    //             }
    //         }
    //     }
    //     pr($hisArr);die();
    //     //echo '<pre>change';print_r($changeValueArr);echo '</pre>';
    //     //echo '<pre>his->';print_r($hisArr);echo '</pre>';exit;
    //     if ($hisArr && $this->History->saveAll($hisArr)) {
    //         return true;
    //     }
    // }

    /**
     * filterBAList
     * @author Khin Hnin Myo(2022/05/26)
     * @param $inc_id(auto incre id), $logid(login id)
     * @return Array
     **/
    public function filterBAList($inc_id, $logid, $date, $language = '', $page = '')
    {

        $userData = $this->User->find('first', array('conditions' => array('User.id' => $inc_id)));

        $baArr = explode('/', $userData['User']['layer_code']);
        $level = $userData['User']['role_id'];
        $date = date("Y-m", strtotime($date));
        // $phase = Setting::PHASE_SELECTION[$page];#phase id
        $setting_layer = Setting::LAYER_SETTING[$page]; #wanna use layer(eg-2 => 2,3,4,...)
        $limit = $this->Permission->find('first', array(
            'fields' => array('Permission.limit'),
            'conditions' => array(
                'Permission.role_id' => $level,
                'Menu.page_name' => $page
            ),
            'contain' => 'Menu.page_name',
            'order' => 'Permission.limit DESC'
        ))['Permission']['limit'];

        $_SESSION['CHECKLIMIT'] = $limit;
        $listArr = array();
        if ($setting_layer >= $limit) { #setting layer lower and permission layer higher
            $type_order = $setting_layer;
            $conditions = array();
            $conditions['Layer.flag'] = 1;
            $conditions['DATE_FORMAT(Layer.from_date, "%Y-%m") <='] = $date;
            $conditions['DATE_FORMAT(Layer.to_date, "%Y-%m") >='] = $date;
            $conditions['Layer.type_order ='] = $type_order;
            if ($page == 'SampleSelections') $conditions['Layer.object ='] = 1;
            if ($limit != 0) $conditions['Layer.layer_code'] = $baArr;
            // $conditions['Layer.layer_code'] = $baArr;
            $name = ($language == 'eng') ? 'Layer.name_en' : 'Layer.name_jp';
            $listArr = $this->Layer->find('list', array(
                'fields' => array('Layer.layer_code', $name),
                'conditions' => $conditions,
                'order' => array('Layer.type_order', 'Layer.id')
            ));
            // $lists = array_merge(array_column($datas, 'Layer'), array_column($datas, 'layers'));

            // foreach ($lists as $value) {
            //     if(!empty($value['layer_code']) && $value['type_order'] == $setting_layer) $listArr[$value['layer_code']] = $value;
            //     //elseif(!empty($value['type_order'])) $listArr['no_code'][$value['type_order']] = $value;
            // }
        }
        return $listArr;
    }
    /**
     * filterBAList
     * @author Nu Nu Lwin(2022/06/07)
     * @param
     * @return Array
     **/
    public function getLayerThreeName($code, $page = null, $date = '')
    {
        // pr($code);
        // die();

        // pr($page);

        $conditions['Layer.flag'] = 1;
        $conditions['Layer.layer_code'] = $code;
        $conditions['Layer.type_order'] =  Setting::LAYER_SETTING[$page]; #wanna use layer(eg-2 => 2,3,4,...)
        $conditions['Layer.from_date <='] = $date;
        $conditions['Layer.to_date >='] = $date;

        $listArr = array();
        $data = $this->Layer->find('first', array(
            'conditions' => $conditions,
            'fields' => array('layer_code', 'name_en', 'name_jp', 'managers')
        ))['Layer'];
        return $data;
    }

    /**
     * select parent_id of data
     * @author Nu Nu Lwin(2022/06/10)
     * @param
     * @return Array
     **/
    public function parentData($parent_id)
    {
        $vars = json_decode($parent_id, true);
        $conditions['Layer.flag'] = 1;
        $conditions['Layer.layer_code'] = $vars;
        $data = $this->Layer->find('all', array(
            'conditions' => $conditions,
            'fields' => array('name_en', 'name_jp', 'type_order')
        ));
        $datas = array_column($data, 'Layer');

        $data_list = array();
        foreach ($datas as $value) {
            $hq_dept = ($value['type_order'] == '1') ? 'headquarter' : (($value['type_order'] == '2') ? 'department' : 'BA');
            $data_list[$hq_dept] = $value;
        }

        return $data_list;
    }

    /**
     * filterBAList
     * @author Khin Hnin Myo(2022/05/26)
     * @param $inc_id(auto incre id), $logid(login id)
     * @return Array
     **/
    public function getMailList($layer_code = '', $page, $function, $language, $layer_name = '', $period, $setting_layer)
    {
        $getMail = $temp = $mail_temp = [];
        $getMail = array(
            'subject' => '',
            'body' => '',
            'mailType' => '',
            'mailSend' => '',
        );
        $mail_sub = $mail_bdy = "";
        $period  = date('Y-m', strtotime($period));
        $layerFields = array(
            'Menu.mail_flag',
            'Menu.mail_code',
            'MailReceiver.mail_send_type',
            'MailReceiver.role_id',
            'MailReceiver.mail_limit',
            'Mail.mail_type',
            'Mail.mail_subject',
            'Mail.mail_body',
            'User.login_code',
            'User.email',
            'User.layer_type_order',
            'Layer.layer_code',
            'LayerType.name_en'
        );
        $group_fields = array(
            'MailReceiver.mail_send_type',
            'MailReceiver.role_id',
            'MailReceiver.mail_limit',
            'Mail.mail_type',
            'Mail.mail_subject',
            'Mail.mail_body',
            'User.email',
            'LayerType.type_order',
            'Layer.layer_code'

        );
        if ($language == 'eng') {
            array_push($layerFields, 'LayerType.name_en as layer_type_name', 'Layer.name_en as layer_group_name');
            $this->Layer->virtualFields['layer_name'] = "Layer2.name_en";
        } else {
            array_push($layerFields, 'LayerType.name_jp as layer_type_name', 'Layer.name_en as layer_group_name');
            $this->Layer->virtualFields['layer_name'] = "Layer2.name_jp";
        }

        if ($function == 'approvecancel') {
            $function = 'approve_cancel';
        } elseif ($function == 'requestcancel') {
            $function = 'request_cancel';
        }

        $layer_query = '(SELECT l2.* FROM layers l1 JOIN layers l2 ON (l1.parent_id LIKE CONCAT("%", l2.layer_code, "%") OR l1.layer_code = l2.layer_code) WHERE l1.layer_code = "' . $layer_code . '" AND DATE_FORMAT(l1.from_date, "%Y-m") <= "' . $period . '" AND DATE_FORMAT(l1.to_date, "%Y-m") >= "' . $period . '" AND DATE_FORMAT(l2.from_date, "%Y-m") <= "' . $period . '" AND DATE_FORMAT(l2.to_date, "%Y-m") >= "' . $period . '")';

        $mails = $this->Permission->find('all', array(
            'conditions' => array(
                'Permission.role_id' => $_SESSION['ADMIN_LEVEL_ID'],
                'Menu.page_name' => $page,
                'Menu.mail_flag' => 'ON',
                'Menu.mail_code !=' => '',
                'Menu.mail_code !=' => NULL,
                'Menu.flag' => 1
            ),
            'joins' => array(
                array(
                    'table' => 'mails',
                    'alias' => 'Mail',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Mail.mail_code = Menu.mail_code AND Mail.flag = 1'
                    )
                ),
                array(
                    'table' => 'mail_receivers',
                    'alias' => 'MailReceiver',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'MailReceiver.mail_id = Mail.id AND Mail.flag = 1'
                    )
                ),
                array(
                    'table' => 'users',
                    'alias' => 'User',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'User.role_id = MailReceiver.role_id AND IF(MailReceiver.mail_limit = 0, User.layer_type_order <= Menu.layer_no, User.layer_type_order = MailReceiver.mail_limit) AND DATE_FORMAT(User.joined_date, "%Y-m") <= "' . $period . '" AND DATE_FORMAT(User.resigned_date, "%Y-m") >= "' . $period . '" AND User.flag = 1'
                    )
                ),
                array(
                    'table' => $layer_query,
                    'alias' => 'Layer',
                    'type' => 'right',
                    'conditions' => array(
                        "FIND_IN_SET(Layer.layer_code, REPLACE(User.layer_code, '/', ',')) AND Layer.flag = 1"
                    )
                ),
                array(
                    'table' => 'layer_types',
                    'alias' => 'LayerType',
                    'type' => 'left',
                    'conditions' => array(
                        "LayerType.id = Layer.layer_type_id and LayerType.flag = 1"
                    )
                )
            ),
            'fields' => $layerFields,
            'group' => $group_fields
        ));

        $replace_data = $this->ReplaceTypeToLayer($layer_code, $period);
        $period_deadline = $this->getPeriodDeadline($page, date('Y-m-d', strtotime($period)), $layer_code);
        $lcode_list = array_filter(array_column(array_column($mails, 'Layer'), 'layer_code'));

        if (count($lcode_list) > 0) {
            $mail_send = 0;
            foreach ($mails as $key => $value) {
                $type = $value['MailReceiver']['mail_send_type']; #to/ cc
                $level = $value['MailReceiver']['role_id'];
                $mail_limit = $value['MailReceiver']['mail_limit'];

                $mail_type = $value['Mail']['mail_type']; #popup/normal
                $mail_subject = $value['Mail']['mail_subject'];
                $mail_body = str_replace("(_*_)", "\r\n", $value['Mail']['mail_body']);

                $mailAdd = $value['User']['email'];
                $layer_code     = $value['Layer']['layer_code'];

                $sbj = ($mail_sub != '') ? $mail_sub : $mail_subject;
                $bdy = ($mail_bdy != '') ? $mail_bdy : $mail_body;

                #replace {layer-name} to name(hq, dept, ba, gp, ...)
                foreach ($replace_data as $type_key => $replace_name) {
                    $sbj = str_replace($type_key, $replace_name, '' . $sbj);
                    $bdy = str_replace($type_key, $replace_name, '' . $bdy);
                }
                #replace {period}{deadline} to date
                foreach ($period_deadline as $k => $val) {
                    if (!empty($val)) {
                        $sbj = str_replace($k, date('Y-m-d', strtotime($period_deadline[$k])), $sbj);
                        $bdy = str_replace($k, date('Y-m-d', strtotime($period_deadline[$k])), $bdy);
                    } else {
                        $sbj = str_replace($k, '', $sbj);
                        $bdy = str_replace($k, '', $bdy);
                        $bdy = str_replace('提出期限', '', $bdy);
                        $bdy = str_replace('提出期日', '', $bdy);
                        $bdy = str_replace('：', '', $bdy);
                    }
                }
                $mail_sub  = $sbj;
                $mail_bdy  = $bdy;
                $getMail['subject'] = $mail_sub;
                $getMail['body']    = $mail_bdy;

                if (!empty($type)) {
                    if (!in_array($type . "_" . $level . "_" . $mailAdd, $temp)) {
                        array_push($temp, $type . "_" . $level . "_" . $mailAdd);
                        $mail_temp[$type][$level][$key] = $mailAdd;
                        $getMail[$type][$level]         = implode(",", $mail_temp[$type][$level]);
                    }
                }
                $getMail['mailType']  = $mail_type;
                $mail_send = 1;
                $getMail['mailSend']  = $mail_send;
            }
        }
        return $getMail;
    }
    /**
     * ReplaceTypeToLayer
     * @author Khin Hnin Myo(2023/07/14)
     * @param $layer_code, $period
     * @return $replace_data
     **/
    public function ReplaceTypeToLayer($layer_code, $period)
    {
        $this->Layer->virtualFields['layer_type'] = "CONCAT('{',LOWER(LayerType.name_en),'}')";
        $replace_data = $this->Layer->find('list', array(
            'conditions' => array(
                'Layer.flag' => 1,
                'Layer.layer_code' => $layer_code,
                'DATE_FORMAT(Layer.from_date, "%Y-m") <=' => $period,
                'DATE_FORMAT(Layer.to_date, "%Y-m") >=' => $period
            ),
            'joins' => array(
                array(
                    'table' => 'layers',
                    'alias' => 'Layer2',
                    'type' => 'LEFT',
                    'conditions' => array(
                        '(Layer.parent_id LIKE CONCAT("%", Layer2.layer_code, "%") OR Layer.layer_code = Layer2.layer_code) AND DATE_FORMAT(Layer2.from_date, "%Y-m") <= "' . $period . '" AND DATE_FORMAT(Layer2.to_date, "%Y-m") >= "' . $period . '" AND Layer2.flag = 1'
                    )
                ),
                array(
                    'table' => 'layer_types',
                    'alias' => 'LayerType',
                    'type' => 'left',
                    'conditions' => array(
                        "LayerType.id = Layer2.layer_type_id and LayerType.flag = 1"
                    )
                )
            ),
            'fields' => array('layer_type', 'layer_name'),
            'group' => array('Layer2.layer_code')
        ));
        return $replace_data;
    }
    /**
     * getPeriodDeadline
     * @author Khin Hnin Myo
     * @param $page, $period, $layer_code
     * @return $period_deadline
     **/
    public function getPeriodDeadline($page, $period, $layer_code)
    {
        $menu_name = $this->Menu->find('first', array(
            'fields' => array('menu_name_en'),
            'conditions' => array(
                'page_name' => $page
            )
        ))['Menu']['menu_name_en'];

        $phase_name = array('Retention Claim Debt', 'Sample Check', 'Inventory');
        $model = array_combine($phase_name, Setting::MODEL_FOR_DEADLINE)[$menu_name];
        $field = array_combine($phase_name, Setting::FIELD_FOR_DEADLINE)[$menu_name];

        $deadline = $this->$model->find('first', array(
            'fields' => $field,
            'conditions' => array(
                'period' => $period,
                'layer_code' => $layer_code,
                'flag <>' => 0
            )
        ))[$model][$field];

        $period_deadline['{deadline}'] = date('Y-m-d', strtotime($deadline));
        $period_deadline['{period}'] = $period;
        return $period_deadline;
    }
    /**
     * getMailData
     * @author PanEiPhyo(20220915)
     * @param $inc_id(auto incre id), $logid(login id)
     * @return Array
     **/
    public function getMailData($role_id, $layer_code = '', $page, $function, $language, $layer_name = '', $period, $setting_layer)
    {
        $check_limit = $_SESSION['CHECKLIMIT'];
        $this->LayerType->virtualFields['layers'] = "CONCAT('{',LOWER(name_en),'}')";
        $layers = $this->LayerType->find('list', array(
            'fields' => array('type_order', 'layers'),
            'conditions' => array(
                'LayerType.flag' => 1
            )
        ));

        $getmailflag = $this->Menu->find('first', array(
            'fields' => array('Permission.mail_send', 'Permission.mail_id', 'Menu.layer_no'),
            'conditions' => array(
                'Menu.page_name' => $page,
                'Menu.method' => $function,
                'Menu.flag' => 1,
            ),
            'joins' => array(
                array(
                    'table' => 'permissions',
                    'alias' => 'Permission',
                    'type' => 'left',
                    'conditions' => array(
                        'Permission.menu_id = Menu.id AND Permission.role_id = ' . $role_id
                    )
                ),
            )
        ));

        $setting_layer = $getmailflag['Menu']['layer_no'];
        $mail_status = $getmailflag['Permission']['mail_send'];
        $mail_id = $getmailflag['Menu']['layer_no'];

        if ($mail_status == 1) {
            $getMail = $temp = $mail_temp = [];
            $mail_sub = $mail_bdy = "";
            $oflayer = explode("!", (join("の!", $layers)) . "の");

            $layerFields = array(
                'Permission.mail_send',
                'MailReceiver.mail_send_type',
                'MailReceiver.role_id',
                'MailReceiver.mail_limit',
                'Mails.mail_type',
                'Mails.mail_subject',
                'Mails.mail_body',
                'User.email',
                'layer_group.name_jp',
                'layer_group1.layer_code',
            );
            $group_fields = array(
                'MailReceiver.mail_send_type',
                'MailReceiver.role_id',
                'MailReceiver.mail_limit',
                'Mails.mail_type',
                'Mails.mail_subject',
                'Mails.mail_body',
                'layer_group1.layer_code',
                'User.email'
            );
            $period = date("Y-m-d", strtotime($period));

            $layerJoin1 = '';
            $layerJoin2 = '';

            if (!empty($layer_code)) {
                //$setting_layer = Setting::LAYER_SETTING[1];
                $sql = ($check_limit != 0) ? "User.layer_code = layers.layer_code AND layers.flag = 1 AND  layers.parent_id LIKE CONCAT('%\"L', Layer.type_order, '\":\"',Layer.layer_code,'\"%')" : "layers.flag = 1  AND Layer.parent_id LIKE CONCAT('%\"L', layers.type_order, '\":\"',layers.layer_code,'\"%')";
                $layerJoin1 = array(
                    'table' => 'layers',
                    'alias' => 'Layer',
                    'type' => 'left',
                    'conditions' => array(
                        'Layer.layer_code = "' . $layer_code . '" AND Layer.from_date <= "' . $period . '" AND Layer.to_date >= "' . $period . '" AND Layer.type_order = "' . $setting_layer . '" AND Layer.flag = 1'
                    )
                );
                $layerJoin2 = array(
                    'table' => 'layers',
                    'alias' => 'layers',
                    'type' => 'left',
                    'conditions' => array(
                        $sql
                    )
                );
                if ($language == 'eng') array_push($layerFields, 'Layer.name_en as layer_group1_name', 'Layer.type_order', 'layers.name_en as layer_group2_name', 'layers.type_order');
                else array_push($layerFields, 'Layer.name_jp as layer_group1_name', 'Layer.type_order', 'layers.name_jp as layer_group2_name', 'layers.type_order');
                array_push($group_fields, 'layers.type_order');
            }

            $mails = $this->Mail->find('all', array(
                'conditions' => array(
                    'Mail.id'     => $mail_id,
                ),
                'joins' => array(
                    array(
                        'table' => 'mail_receivers',
                        'alias' => 'MailReceiver',
                        'type' => 'left',
                        'conditions' => array(
                            'Mail.id = MailReceiver.mail_id AND MailReceiver.flag = 1'
                        )
                    ),
                    array(
                        'table' => 'layers',
                        'alias' => 'layer_group',
                        'type' => 'left',
                        'conditions' => array(
                            ' MailReceiver.mail_limit = layer_group.type_order AND layer_group.flag = 1'
                        )
                    ),
                    array(
                        'table' => 'layers',
                        'alias' => 'layer_group1',
                        'type' => 'left',
                        'conditions' => array(
                            "layer_group1.flag = 1 AND  layer_group1.parent_id LIKE CONCAT('%\"L', MailReceiver.mail_limit, '\":\"',layer_group.layer_code,'\"%') AND layer_group1.type_order = " . end(array_keys($layers))
                        )
                    ),
                    array(
                        'table' => 'users',
                        'alias' => 'User',
                        'type' => 'left',
                        'conditions' => array(
                            'if(layer_group1.layer_code != null ,User.layer_code like layer_group1.layer_code,1=1) AND User.flag = 1 AND MailReceiver.role_id = User.role_id'
                        )
                    ),
                    $layerJoin1,
                    $layerJoin2,
                ),
                'fields' => $layerFields,
                'group'  => $group_fields,
            ));

            foreach ($mails as $key => $value) {
                $mail_send = $value['Permission']['mail_send']; #mail send or not (1 or 0)

                $type = $value['MailReceiver']['mail_send_type']; #to/ cc
                $level = $value['MailReceiver']['role_id'];
                $mail_limit = $value['MailReceiver']['mail_limit'];

                $mail_type = $value['Mails']['mail_type']; #popup/normal
                $mail_subject = $value['Mails']['mail_subject'];
                $mail_body = $value['Mails']['mail_body'];

                $mailAdd = $value['User']['email'];
                $layer_gp_name     = ($value['Layer']['layer_group1_name'] != '') ? $value['Layer']['layer_group1_name'] : $layer_name;
                $sec_layer_gp_name = $value['layers']['layer_group2_name'];
                $type_order       = $value['Layer']['type_order'];
                $sec_type_order   = $value['layers']['type_order'];
                $sbj = ($mail_sub != '') ? $mail_sub : $mail_subject;
                $bdy = ($mail_bdy != '') ? $mail_bdy : $mail_body;

                if (!empty($layer_code)) {
                    #replace {layer-name} to name(hq, dept, ba, gp, ...)
                    $sbj = str_replace($layers[$type_order], $layer_gp_name, $sbj);
                    $bdy = str_replace($layers[$type_order], $layer_gp_name, $bdy);
                    $sbj = str_replace($layers[$sec_type_order], $sec_layer_gp_name, $sbj);
                    $bdy = str_replace($layers[$sec_type_order], $sec_layer_gp_name, $bdy);
                } else {
                    #remove {layer-name}の
                    $sbj = str_replace($oflayer, '', $sbj);
                    $bdy = str_replace($oflayer, '', $bdy);
                    #remove {layer-name}
                    $sbj = str_replace($layers, '', $sbj);
                    $bdy = str_replace($layers, '', $bdy);
                }
                $mail_sub  = $sbj;
                $mail_bdy = $bdy;
                $getMail['subject'] = $mail_sub;
                $getMail['body']    = $mail_bdy;

                if (!empty($type)) {
                    if (!in_array($type . "_" . $level . "_" . $mailAdd, $temp)) {
                        array_push($temp, $type . "_" . $level . "_" . $mailAdd);
                        $mail_temp[$type][$level][$key] = $mailAdd;
                        $getMail[$type][$level]         = implode(",", $mail_temp[$type][$level]);
                    }
                }
                $getMail['mailType']  = $mail_type;
                $getMail['mailSend']  = $mail_send;
            }
        }

        return $getMail;
    }

    /**
     * check state that are read or save or app or rej or cancel
     * @author Nu Nu Lwin(2022/06/14)
     * @param
     * @return Array
     **/
    public function checkButtonType($data)
    {
        $user_level = $data['role_id'];
        $current_controller  = $data['page'];
        $PermissionController = new PermissionsController();
        $permits = $PermissionController->checkPermission($user_level, $current_controller);

        $ret['Read'] = $ret['Save'] = $ret['Request'] = $ret['Approve'] = $ret['Approve Cancel'] = $ret['Reject'] = $ret['Review'] = false;

        foreach ($permits as $Check) {

            if (strpos($Check, "index")) {
                $ret['Read'] = true;
            }

            if ((strpos($Check, "save"))) {
                $ret['Save'] = true;
            }

            if (strpos($Check, 'request')) {
                $ret['Request'] = true;
            }

            if (strpos($Check, "approve")) {
                $ret['Approve'] = true;
            }

            if (strpos($Check, "approve cancel")) {
                $ret['Approve Cancel'] = true;
            }

            if (strpos($Check, "reject")) {
                $ret['Reject'] = true;
            }
            if (strpos($Check, "review")) {
                $ret['Review'] = true;
            }
        }

        return $ret;
    }


    /**
     * get admin level id
     * @author Hein Htet Ko(2022/06/16)
     * @param $page, $fname=array(), $phase
     * @return Array
     **/
    public function getAdminLevelID($page, $fname = array(), $phase)
    {
        #phase is menu id
        $id_list = $this->Permission->find('list', array(
            'fields' => array('id', 'role_id', 'Menu.method'),
            'conditions' => array(
                'Menu.page_name' => $page,
                'Menu.method' => $fname,
                'menu_id' => $phase,
                'contain' => 'Menu.page_name'
            )
        ));
        return $id_list;
    }

    /**
     * get button list
     * @author Hein Htet Ko(2022/06/21)
     * @param $page, $phase
     * @return Array
     **/
    public function getButtonList($page, $role_id, $function = '%')
    {
        #button type 
        $button_list = $this->Permission->find('all', array(
            'fields' => array('Menu.method', 'role_id', 'limit'),
            'conditions' => array(
                'Menu.page_name' => $page,
                'Permission.role_id' => $role_id
            ),
            'contain' => 'Menu.page_name',
            'order' => array('Menu.method')
        ));
        return $button_list;
    }

    public function getMailContent()
    {
        parent::checkAjaxRequest($this);
        if ($this->request->is('POST')) {
            if ($this->Session->read('Config.language') == 'eng') {
                $language = 'eng';
            } else {
                $language = 'jp';
            }
            $page = $this->request->data('page');
            $selection_name = $this->request->data('selection_name');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $layer_name = $this->Session->read('SAMPLECHECK_BA_NAME');
            $role_id = $this->Session->read('ADMIN_LEVEL_ID');
            $period  = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
            #add space and remove space before words
            $function = trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', $this->request->data('data_action')));
            $get_mails = $this->getMailList($layer_code, $page, $function, $language, $layer_name, $period, Setting::LAYER_SETTING[$selection_name]); //layer setting according to menu id
            if (!array_key_exists('to', $get_mails)) {
                $get_mails['to'] = '';
            }
            if (!array_key_exists('cc', $get_mails)) {
                $get_mails['cc'] = '';
            }
            if (!array_key_exists('bcc', $get_mails)) {
                $get_mails['bcc'] = '';
            }
            if (($get_mails['to'] != "") || ($get_mails['cc'] != "") || ($get_mails['bcc'] != "")) {
                $get_mails['mailSend'] == 1;
            } else {
                $get_mails['mailSend'] == 0;
            }
            echo json_encode($get_mails);
        }
    }

    /*
    * getLastLayer
    * @author Khin Hnin Myo(2022/06/27)
    * @param 
    * @return $last_layer(return layer number - eg:1,2,3,4,...)
    **/
    public function getLastLayer()
    {
        $last_layer = $this->Layer->find('first', array(
            'fields' => array('MAX(Layer.type_order) as type_order'),
            'conditions' => array(
                'Layer.flag' => '1'
            )
        ))[0]['type_order'];
        return $last_layer;
    }

    /**
     * get layer name list based on requested code
     * @author WaiWaiMoe
     * @date 2022/06/30
     * @param $code
     * @return Array
     **/
    public function getLayerNameOnCode($code, $language, $order = '')
    {

        $name = ($language == 'eng' ? 'name_en' : 'name_jp');
        // $type_order = $this->Layer->find('first',array(
        //     'fields' => array('Layer.type_order'),
        //     'conditions' => array(
        //         'Layer.layer_code' => $code,
        //         'Layer.flag' => 1
        //     )
        // ))['Layer']['type_order'];


        // $result = $this->LayerType->find('list', array(
        //     'fields' => array($name),
        //     'conditions' => array(
        //         'LayerType.type_order <' => $type_order,
        //         'LayerType.flag' => 1
        //     )
        // ));

        $result = $this->LayerType->find('list', array(
            'fields' => array($name),
            'conditions' => array(
                'LayerType.type_order IN' => array(Setting::LAYER_SETTING['topLayer'], Setting::LAYER_SETTING['middleLayer'], Setting::LAYER_SETTING['bottomLayer']),
                'LayerType.flag' => 1
            ),
            'order' => array(
                'type_order'
            )
        ));
        return $result;
    }

    /**
     * get layer list based on role & page
     * @author PanEiPhyo (20220912)
     * @param $code
     * @return Array
     **/
    public function getPermissionsByRole($user_id, $role_id, $page_name)
    {
        $layer_no    = $this->getMenuLayer($page_name);
        $today_date  = date("Y/m/d");
        $permissions = $this->Menu->find('all', array(
            'fields' => array('Menu.id', 'Menu.method', 'Permission.limit', 'Menu.base_status', 'Menu.next_status'),
            'conditions' => array(
                'Menu.page_name' => $page_name,
                'Menu.flag' => 1
            ),
            'joins' => array(
                array(
                    'table' => 'permissions',
                    'alias' => 'Permission',
                    'type'  =>  'left',
                    'conditions' => array(
                        'Permission.menu_id = Menu.id',
                        'Permission.role_id' => $role_id,
                    )
                ),
            ),
        ));
        $user_data = $this->User->find('first', array(
            'fields' => array('User.layer_code', 'User.login_code', 'User.role_id'),
            'conditions' => array(
                'User.id' => $user_id,
                'User.flag' => '1'
            )
        ));

        $user_layer = explode("/", $user_data['User']['layer_code']);
        $user_role  = $user_data['User']['role_id'];
        $user_code  = $user_data['User']['login_code'];
        if (in_array($user_role, SETTING::MANAGER_LIST)) {
            $layer_list = $this->Layer->find('list', array(
                'fields'    => array('Layer.layer_code'),
                'conditions' => array(
                    'managers'   => $user_code,
                    'Layer.to_date >=' => $today_date,
                    'Layer.flag' => '1',
                    'to_date >=' => date('Y-m-d')
                )
            ));
            if (!empty($layer_list))
                $user_layer = array_merge($user_layer, $layer_list);
        }

        // $permission = call_user_func_array('array_merge', $permissions);
        $btn_status = Setting::BTN_STATUS;

        foreach ($permissions as $permission) {
            $limit = ($permission['Permission']['limit'] != null) ? $permission['Permission']['limit'] : 1;
            //$layer_list = $this->getLayerByLimit($limit, $layer_no, $user_layer,$user_role);
            $layer_list = $this->getLayerByLimit($limit, $layer_no, $user_layer, $page_name);
            $data[$permission['Menu']['method']] = array(
                'base_status' => $permission['Menu']['base_status'],
                'next_status' => $permission['Menu']['next_status'],
                'limit'       => $limit,
                'layers'      => $layer_list['layer_data'],
                'parent_data' => !empty($layer_list['parent_data']) ? $layer_list['parent_data'] : null,
            );
        }
        return $data;
    }
    public function getMenuByRole($role_id, $page_name, $layout = '')
    {
        $rem_selection = ($layout != 'buanalysis') ? 'AND menus.page_name != Menu.page_name' : '';
        
        $data = array_column(array_column($this->Menu->find('all', array(
            'fields' => array('menus.page_name'),
            'conditions' => array(
                'Menu.flag' => 1,
                'Menu.page_name' => $page_name,
                'Permission.role_id' => $role_id
            ),
            'joins' => array(
                array(
                    'table' => 'menus',
                    'alias' => 'menus',
                    'type' => 'left',
                    'conditions' => array(
                        'menus.menu_name_en = Menu.menu_name_en ' . $rem_selection . ' AND menus.flag = 1'
                    )
                ),
                array(
                    'table' => 'permissions',
                    'alias' => 'Permission',
                    'type' => 'left',
                    'conditions' => array(
                        'Permission.menu_id = menus.id AND menus.flag = 1'
                    )
                )
            ),
            'group' => array('menus.page_name'),
            'order' => array('menus.id')
        )), 'menus'), 'page_name');
        return $data;
    }


    public function getMenuByRoleWithoutLayout($role_id, $page_name)
    {
        $data = array_column(array_column($this->Menu->find('all', array(
            'fields' => array('menus.page_name'),
            'conditions' => array(
                'Menu.flag' => 1,
                'Menu.page_name' => $page_name,
                'Permission.role_id' => $role_id
            ),
            'joins' => array(
                array(
                    'table' => 'menus',
                    'alias' => 'menus',
                    'type' => 'left',
                    'conditions' => array(
                        'menus.menu_name_en = Menu.menu_name_en AND menus.flag = 1'
                    )
                ),
                array(
                    'table' => 'permissions',
                    'alias' => 'Permission',
                    'type' => 'left',
                    'conditions' => array(
                        'Permission.menu_id = menus.id AND menus.flag = 1'
                    )
                )
            ),
            'group' => array('menus.page_name'),
            'order' => array('menus.id')
        )), 'menus'), 'page_name');

        return $data;
    }


    /**
     * get layer list based on limit
     * @author PanEiPhyo (20220912)
     * @param $code
     * @return Array
     **/
    public function getLayerByLimit($limit, $layer_no, $user_layer, $page_name)
    {
        $layer_arr = $layer_array = [];
        if ($_SESSION['Config']['language'] == 'eng') {
            $name = 'Layer.name_en';
        } else {
            $name = 'Layer.name_jp';
        }
        if ($limit <= $layer_no) {
            if ($limit == 0) {
                if ($page_name == 'SampleSelections') {
                    $layers = $this->Layer->find('list', array(
                        'fields' => array('Layer.layer_code', $name),
                        'conditions' => array(
                            'flag' => 1,
                            'type_order' => $layer_no,
                            'object' => 1,
                            'to_date >=' => date('Y-m-d')
                        )
                    ));
                } else {
                    $layers = $this->Layer->find('list', array(
                        'fields' => array('Layer.layer_code', $name),
                        'conditions' => array(
                            'flag' => 1,
                            'type_order' => $layer_no,
                            'to_date >=' => date('Y-m-d')
                        )
                    ));
                }

                $layer_arr['layer_data'] = $layers;
            } else {
                if ($page_name == 'SampleSelections') {
                    $layers = $this->Layer->find('list', array(
                        'fields' => array('Layer.layer_code', $name),
                        'conditions' => array(
                            'flag' => 1,
                            'type_order' => $layer_no,
                            'object' => 1,
                            'to_date >=' => date('Y-m-d')
                        )
                    ));
                } else {
                    $layers = $this->Layer->find('list', array(
                        'fields' => array('Layer.layer_code', 'Layer.parent_id'),
                        'conditions' => array(
                            'flag' => 1,
                            'type_order' => $layer_no,
                            'layer_code' => $user_layer,
                            'to_date >=' => date('Y-m-d')
                        )
                    ));
                }
                $res = [];
                foreach ($layers as $layer_code => $parent_ids) {
                    $res[$layer_code] = json_decode($parent_ids, true);
                    $parent_layer = json_decode($parent_ids, true)["L" . $limit];
                    // $parent_id = '"L'.$limit.'":"'.$parent_layer.'"';
                    $parent_id = ($limit == $layer_no) ? $parent_ids : '"L' . $limit . '":"' . $parent_layer . '"';
                    $layers = $this->Layer->find('list', array(
                        'fields' => array('Layer.layer_code', $name),
                        'conditions' => array(
                            'flag' => 1,
                            'parent_id LIKE' => '%' . $parent_id . '%',
                            'type_order' => $layer_no,
                            'to_date >=' => date('Y-m-d')
                        )
                    ));
                    $layer_array = $layer_array + $layers;
                }
                $layer_arr['layer_data'] = $layer_array;
                $layer_arr['parent_data'] = $res;
            }
        }
        return $layer_arr;
    }

    public function getMenuLayer($page_name)
    {
        $layer_no = $this->Menu->find('first', array(
            'fields' => array('Menu.layer_no'),
            'conditions' => array(
                'page_name' => $page_name
            ),
        ))['Menu']['layer_no'];
        return $layer_no;
    }

    public function getButtonLists($status, $layer_code, $permissions)
    {
        $buttons = [];
        # check user permission
        unset($permissions['index']);
        foreach ($permissions as $action => $permission) {
            if (($permission['base_status'] == $status) && (($layer_code == '' && $permission['limit'] == 0) || in_array($layer_code, array_keys($permission['layers'])))) {
                $buttons[$action] = $permission['next_status'];
            }
        }

        return $buttons;
    }
    /**
     * combineAsExcelSheets
     * @author Hein Htet Ko(20220808)
     * @return void
     **/
    public function combineAsExcelSheets($term_id, $budget_term, $head_dept_code, $headquarter, $login_id = null, $plan_this, $save_into_tmp = false)
    {

        $TP = new BrmTradingPlanController();
        $MP = new BrmManpowerPlanController();
        $BP = new BrmBudgetPlanController();
        $controller_name = $plan_this->params['controller'];
        $today_date = date('Y/m/d');
        $budget_year = explode('~', $budget_term);
        $layer_code = $plan_this->Session->read('SESSION_LAYER_CODE');
        $ba_name = $plan_this->Session->read('BUDGET_BA_NAME');

        #get start year
        $get_from_year = $this->Layer->find('first', array(
            'fields' => array('YEAR(Layer.from_date) as from_date'),
            'conditions' => array(
                'Layer.layer_code' => $layer_code,
                'Layer.flag' => 1
            )
        ))[0]['from_date'];

        if ($budget_year[0] > $get_from_year) {
            $start_year = $budget_year[0];
        } else {
            $start_year = $get_from_year;
        }
        $end_year = $budget_year[1];
        if ($controller_name == 'BrmForecastBudgetDifference') {
            $years = range($start_year, $end_year);
            $_SESSION['count_years'] = count($years);
        } else {
            $years = [!empty($plan_this->Session->read('YEAR')) ? $plan_this->Session->read('YEAR') : $plan_this->request->data('year')];
            $_SESSION['count_years'] = count($years);
        }
        $layer_code = $plan_this->Session->read('SESSION_LAYER_CODE');
        $ba_name = $plan_this->Session->read('BUDGET_BA_NAME');
        $term = $this->BrmTerm->find('first', array('conditions' => array('flag' => '1', 'id' => $term_id)));
        $term_name = $term['BrmTerm']['term_name'];
        $term_name =  ($term_name == '' || empty($term_name)) ? $term['BrmTerm']['budget_year'] . '~' . $term['TermModel']['budget_end_year'] : $term_name;
        $PHPExcel = $plan_this->PhpExcel;
        $language = $plan_this->Session->read('Config.language');

        if ($plan_this->Session->check('PERMISSIONS')) {
            $permission = $plan_this->Session->read('PERMISSIONS');
        } else {
            $adminLevelId = $this->getUserData($login_id)['User']['role_id'];
            $permission = $this->getUserPermission($adminLevelId, $login_id);
        }

        // $head_dept_code = $plan_this->Session->read('HEAD_DEPT_CODE');
        $ba_list = $plan_this->Layer->find('list', array(
            'fields' => array('Layer.layer_code', 'Layer.name_jp'),
            'conditions' => array(
                'Layer.flag' => 1,
                'Layer.to_date >=' => $today_date,
                'Layer.parent_id LIKE ' => '%' . $head_dept_code . '%',
            ),
            'order' => array('Layer.layer_code ASC')
        ));

        $one_time_download = ($save_into_tmp) ? false : true;
        #clear session due to one time download
        unset($_SESSION['objworksheet']);
        unset($_SESSION['active_index']);
        unset($_SESSION['total_years']);
        $createLimit = $permission['save']['limit'];
        //echo '<pre>';print_r($permission);echo '</pre>';
        foreach ($years as $each_year) {
            #forecast or budget
            if ($each_year == $budget_year[0]) {
                $form_type = 'forecast';
                $sheet_name_bp = $each_year . '_見込フォーム';
            } else {
                $form_type = 'budget';
                $sheet_name_bp = $each_year . '_予算フォーム';
            }
            #define sheet name
            if ($save_into_tmp) {
                $folder_dir = $headquarter . '/計画フォーム_' . $layer_code . '-' . $ba_name;
                $real_file_name = $each_year . '_計画フォーム_' . $layer_code . $ba_name . '.xlsx';
                $file_path = APP . '/tmp/' . $folder_dir . '/' . $real_file_name;
                $file_name_tp = [$file_path, $each_year . '_取引計画フォーム'];
                $file_name_mp = [$file_path, $each_year . '_人員計画フォーム'];
                $file_name_bp = array($file_path, $sheet_name_bp);
            } else {
                $file_name_tp = $each_year . '_取引計画フォーム';
                $file_name_mp = $each_year . '_人員計画フォーム';
                $file_name_bp =  $sheet_name_bp;
            }
            #Trading Plan Save cache & download
            if (!in_array($headquarter, Setting::TRADING_DISABLE_HQS)) {
                $tr_cache_name = 'trading_plan_' . $term_id . '_' . $each_year . '_' . (explode('/', $layer_code))[0] . '_' . $login_id;
                if (!empty(Cache::read($tr_cache_name))) {
                    $tr_data = Cache::read($tr_cache_name);
                } else {
                    $tr_data = $TP->getTradingDataAndCaching($each_year, $term_id, $budget_term, $head_dept_code, $layer_code, $ba_name, $login_id);
                }

                $approved_BA = $tr_data['approved_BA'];
                $approveHQ = $tr_data['approveHQ'];
                $createlimit = $permission['BudgetingSystemCreateLimit'];
                $createLimit = $this->checkLimit($createlimit, $layer_code, $login_id, $permission);

                $ba = $plan_this->Session->read('SESSION_LAYER_CODE') . '/' . $ba_name;

                # disabled/enabled(input field and button )
                if ($createLimit == 1) {
                    $page = 'Enabled';
                } else {
                    $page = 'Disabled'; # no action and read only
                }
                # for excel disable
                if (!empty($approved_BA) || !empty($approveHQ)) {
                    $approved = 'Approved';
                }
                $disable = explode('_', $approved . '_' . $page);
                $hq_name = null;
                if (!empty($tr_data['logistic_data'])) {
                    $TP->DownloadExcel($term_id, $start_year . '~' . $end_year, $ba, $each_year, $login_id, $file_name_tp, $PHPExcel, $save_into_tmp, $disable, $hq_name, $one_time_download);
                }
            }

            $hqDeadline = $_SESSION['HEADQUARTER_DEADLINE'];
            $mp_cache_name = 'manpower_plan_' . $term_id . '_' . $each_year . '_' . (explode('/', $layer_code))[0] . '_' . $login_id;
            if (!Cache::read($mp_cache_name)) {
                $mpData = $MP->getManpowerData($term_id, $budget_term, $head_dept_code, $layer_code, $each_year, $login_id, $permission, $hqDeadline);
            }

            if ($mpData != 'no_data') {
                $MP->DownloadExcel($term_id, $start_year . '~' . $end_year, $head_dept_code, $layer_code, $each_year, $login_id, $file_name_mp, $PHPExcel, $save_into_tmp, $one_time_download);
            } else {
                if (($key = array_search($file_name_mp, $upload_file_arr)) !== false) {
                    unset($upload_file_arr[$key]);
                }
            }
            $bp_cache_name = 'budget_plan_' . $term_id . '_' . $each_year . '_' . $layer_code . '_' . $login_id;
            if (!Cache::read($bp_cache_name)) {
                $tmp_BP = $BP->getBudgetData($term_id, $term, $head_dept_code, $headquarter, $layer_code, $ba_name, $each_year, $login_id, $save_into_tmp, $form_type);
            }
            $BP->DownloadExcel($term_id, $budget_term, $headquarter, $head_dept_code, $layer_code, $each_year, $form_type, $login_id, $file_name_bp, $PHPExcel, '', $save_into_tmp);
        }

        unset($_SESSION['UPLOAD_FILE_ARR']);
        $plan_this->Session->write('UPLOAD_FILE_ARR', $real_file_name);
    }
    /** 
     * getUserData method
     * @author Hein Htet Ko(20221021)
     * @param  $login_id
     * @return $data
     *
     */
    public function getUserData($id)
    {
        $data = $this->User->find('first', array(
            'conditions' => array(
                'id' => $id,
                'flag' => 1
            )
        ));

        return $data;
    }
    /** 
     * get account based on page method
     * @author Ei Thandar Kyaw (20230126)
     * @param  $request_uri
     * @return $accountData
     *
     */
    public function getAccountByPage($request_uri, $year = null, $layer_code = null)
    {
        $controller = $request_uri['controller'];
        $menu = $this->Menu->find('first', array(
            'conditions' => array(
                'page_name LIKE' => '%' . $controller . '%',
                'method'   => 'index',
                'flag' => 1
            )
        ));
        if ($year != null && $layer_code !=  null) {
            $condition = array(
                'menu_id' => $menu['Menu']['id'],
                'target_year' => $year,
                'AccountSetting.layer_code' => $layer_code,
                'AccountSetting.flag' => 1,
                'Account.flag' => 1
            );
        } else {
            $condition = array(
                'menu_id' => $menu['Menu']['id'],
                'AccountSetting.flag' => 1,
                'Account.flag' => 1
            );
        }

        $data = $this->AccountSetting->find('all', array(
            'fields' => array(
                'AccountSetting.id', 'AccountSetting.account_id', 'AccountSetting.label_name', 'AccountSetting.display_order',
                'Account.id', 'Account.account_code', 'Account.account_name', 'Account.account_type',
                'Account.base_param', 'Account.calculation_formula', 'Account.flag',
                'Account.created_by', 'Account.updated_by', 'Account.created_date', 'Account.updated_date'
            ),
            'conditions' => $condition,
            'joins' => array(
                array(
                    'table' => 'accounts',
                    'alias' => 'Account',
                    'type' => 'left',
                    'conditions' => array(
                        'AccountSetting.account_id = Account.id AND Account.flag = 1'
                    )
                ),
            ),
            'order' => 'Account.id ASC'
        ));
        $account_name = $this->Account->find('all', array(
            'fields' => array('Account.id', 'Account.account_name', 'AccountSetting.label_name'),
            'conditions' => $condition,
            'joins' => array(
                array(
                    'table' => 'account_settings',
                    'alias' => 'AccountSetting',
                    'type' => 'left',
                    'conditions' => array(
                        'AccountSetting.account_id = Account.id AND Account.flag = 1'
                    )
                ),
            ),
            'order' => 'AccountSetting.display_order'
        ));
        $accountName = array();
        foreach ($account_name as $value) {
            $name = $value['Account']['account_name'];
            if ($value['AccountSetting']['label_name'] != '') $name = $value['AccountSetting']['label_name'];
            $accountName[$value['Account']['id']] = $name;
        }

        $result = array();
        //if($controller == 'BrmBudgetPlan'){
        $result['AccountNameOnly'] = $accountName;
        $result['Account'] = $data;
        //}else $result['Account'] = $data;
        return $result;
    }
    // public function importBulkExcelFile($file)
    // {
    //     App::import('Vendor', 'php-excel-reader/PHPExcel');
    //     $file_name 		= $file['name'];
    //     $file_path 		= $file['tmp_name'];
    //     $extension 		= pathinfo($file_name, PATHINFO_EXTENSION);
    //     $error = (empty($file)) ? parent::getErrorMsg('SE015') : ($file['error'] != 0) ? parent::getErrorMsg('SE015') : ($file['size'] >= 10485760) ? 	parent::getErrorMsg('SE020') : (!($extension == 'xlsx' || $extension == 'xls')) ? parent::getErrorMsg('SE013', $extension) : false;
    //     $sheet_list = Setting::SHEET_LIST;
    //     $TP = new BrmTradingPlanController();
    //     $MP = new BrmManpowerPlanController();
    //     $BP = new BrmBudgetPlanController();

    //     try {
    //         if(!$error){
    //             #read main file
    //             $objReader = PHPExcel_IOFactory::createReader('Excel2007');
    //             $objReader->setReadDataOnly(true);
    //             if ($objReader->canRead($file_path)) {
    //                 $objPHPExcel   = $objReader->load($file_path);
    //                 $all_sheets = $objPHPExcel->getSheetNames();
    //                 $message_arr = array();
    //                 foreach($all_sheets as $single_sheet){
    //                     if($single_sheet != 'DestinationList'){
    //                         $sheet_arr = explode('_', $single_sheet);
    //                         $import_year = $sheet_arr[0];
    //                         $objWorksheet = $objPHPExcel->getSheetByName($single_sheet);
    //                         $controller_obj = ${$sheet_list[$sheet_arr[1]]};
    //                         unset($_SESSION['TRADE_DATA']);
    //                         if(!empty($controller_obj) && array_key_exists($sheet_arr[1], $sheet_list)){
    //                             $message_arr[$single_sheet] = $controller_obj->importSheet($import_year, $objWorksheet);
    //                             unset($_SESSION['TRADE_DATA']);
    //                             clearstatcache();                                                    
    //                         }
    //                         // else{
    //                         //     return ['error' => parent::getErrorMsg('SE021')];
    //                         // }
    //                     }    


    //                 }

    //             return $message_arr;
    //             }
    //         }else{
    //             return ['error' => $error];
    //         }
    //     } catch (Expression $e) {
    //         return ['error' => parent::getErrorMsg('SE015')];
    //     }
    // }


    #git branch merge
    /** 
     * UrlSession method
     * @author Khin Hnin Myo
     * @param  $selection, $layer_code, $period
     * @return void
     *
     */
    public function UrlSession($selection, $layer_code, $period, $category = '')
    {
        $login_id = $_SESSION['LOGIN_ID'];
        $get_login_id = $_SESSION['LOGINID'];
        $lan['language'] = $_SESSION['Config']['language'];
        $role_id = $_SESSION['ADMIN_LEVEL_ID'];

        $filterBaList = $this->filterBAList($login_id, $get_login_id, date("Y-m-d", strtotime($period)), $lan['language'], $selection);

        $layer_name = (array_key_exists($layer_code, $filterBaList)) ? (($lan['language'] == 'eng') ? $filterBaList[$layer_code] : $filterBaList[$layer_code]) : "";

        $_SESSION['SAMPLECHECK_PERIOD_DATE'] = $period;
        $_SESSION['SESSION_LAYER_CODE'] = $layer_code;
        $_SESSION['SAMPLECHECK_BA_NAME'] = $layer_name;
        $_SESSION['SAMPLECHECK_CATEGORY'] = $category;

        $_SESSION[$selection . '_PERIOD_DATE'] = $period;
        $_SESSION[$selection . '_BA_NAME'] = $layer_name;

        $show_menu_lists = $this->getMenuByRole($role_id, $selection);
        $_SESSION['MENULISTS'] = $show_menu_lists;
    }

    public function checkRolePermission($menus, $menu_name)
    {
        $role_id = $_SESSION['ADMIN_LEVEL_ID'];
        $permission = array_column($this->Permission->find('all', array(
            'conditions' => array(
                'Permission.role_id' => $role_id,
                'Menu.' . $menu_name => array_keys($menus)
            ),
            // 'group' => array('Menu.menu_name_en'),
            'order' => array('Menu.id')
        )), 'Menu');

        $permissions = [];
        $check_value = [];
        foreach ($permission as $value) {
            if (!in_array($check_value, $value[$menu_name])) {
                array_push($check_value, $value[$menu_name]);

                if ($value[$menu_name] == 'Setting Management' || $value[$menu_name] == '設定管理') $value['page_name'] = 'Users';

                if ($value[$menu_name] == 'BU Analysis' || $value[$menu_name] == 'ビジネス総合分析') $value['page_name'] = 'BUSelections';

                if ($value[$menu_name] == 'Retention Claim Debt' || $value[$menu_name] == '滞留債権') $value['page_name'] = 'SapSelections';

                if ($value[$menu_name] == 'Sample Check' || $value[$menu_name] == 'サンプルチェック') $value['page_name'] = 'SampleSelections';

                if ($value[$menu_name] == 'Inventory' || $value[$menu_name] == '滞留在庫') $value['page_name'] = 'StockSelections';

                $permissions[$value[$menu_name]] = $value['page_name'];
            }
        }

        return $permissions;
    }

    /** 
     * saveUserHistory method
     * @author Hein Htet Ko
     * @param  $old_data, $new_data
     * @return boolean true or false
     *
     */
    public function saveUserHistory($old_data, $new_data)
    {
        #collect changed columns
        $changed_rows = array();
        $latest_row = $old_data[0]['User'];
        $changed_columns = array_diff_assoc($new_data, $latest_row);
        // pr($old_data);pr($new_data);exit;
        #remove unnecessary columns
        $updated_date = array_pop($changed_columns);
        $updated_by = array_pop($changed_columns);

        #skip layer_code, joined_date, resigned_date, layer_type_order not to change
        $skip_columns = array('layer_code', 'joined_date', 'position_code', 'resigned_date', 'layer_type_order');
        $his_columns = array_slice($skip_columns, 0, 3);
        foreach ($old_data as $index => $each_row) {
            foreach ($changed_columns as $changed_key => $changed_value) {
                if (!in_array($changed_key, $skip_columns)) { #to update rows
                    $each_row['User'][$changed_key] = $changed_value;
                } else {
                    #only latest row and not all skip columns
                    if ($index == 0) {
                        if ($changed_key == 'joined_date') {
                            $join_date_only = $changed_value;
                        }
                        // if(!empty(array_diff($his_columns, array_keys($changed_columns)))){       
                        //     # two column changes
                        // $each_row['User'][$changed_key] = $changed_value;
                        // }
                        // else{
                        # all three column changes
                        // if((strtotime($old_data[$index]['User']['joined_date']) >= strtotime($new_data['joined_date']))){
                        //     $each_row['User'][$changed_key] = $changed_value;
                        // }
                        // }
                        if ((strtotime($old_data[$index]['User']['joined_date']) >= strtotime($new_data['joined_date']))) {
                            $each_row['User'][$changed_key] = $changed_value;
                        }
                    } else {
                        if (!empty(array_diff($his_columns, array_keys($changed_columns))) && $index == 1 && $changed_key == 'joined_date') { #only before latest row
                            $each_row['User']['resigned_date'] = date('Y-m-d H:i:s', strtotime('-1 day', strtotime($changed_value)));
                        }
                    }
                }
            }
            $changed_rows[] = $each_row['User'];
        }
        if ((array_key_exists('layer_code', $changed_columns) || array_key_exists('position_code', $changed_columns)) && (array_key_exists('joined_date', $changed_columns) && strtotime($new_data['joined_date']) > strtotime($changed_rows[0]['joined_date']))) {
            #new row
            array_shift($new_data);
            $new_data['password'] = $latest_row['password'];
            $new_data['created_by'] = $updated_by;
            $new_data['created_date'] = $updated_date;

            #add new row 
            $changed_rows[] = $new_data;

            #subtract one day from joined date
            $changed_rows[0]['resigned_date'] = date('Y-m-d H:i:s', strtotime('-1 day', strtotime($new_data['joined_date'])));
        } else {
            if (!empty($join_date_only)) {
                $changed_rows[0]['joined_date'] = $join_date_only;
            }
        }
        return $changed_rows;
    }

    public function checkSavedCount($model_name, $period, $layer_code, $flag)
    {

        if ($model_name == 'Stock') {
            $group = array(
                'layer_code',
                'destination_name',
                'period'

            );
        } else {
            $group = array(
                'layer_code',
                'account_code',
                'destination_code',
                'logistic_index_no',
                'posting_date',
                'recorded_date',
                'receipt_shipment_date',
                'schedule_date',
                'period',
                'currency'
            );
        }
        return $this->$model_name->find('count', array(
            'conditions' => array(
                "date_format(period,'%Y-%m')" => $period,
                "layer_code" => $layer_code,
                "flag" => $flag
            ),
            'fields' => array('id', 'flag'),
            'group' => $group
        ));
    }

    public function getYearMonth($target_year, $term_id, $type)
    {
        $month = '';
        # Get term data by id
        $term_data = $this->BuTerm->find('first', array(
            'fields' => array('start_month', 'end_month'),
            'conditions' => array(
                'id' => $term_id
            )
        ));

        if ($type == 'start') {
            $month =  $term_data['BuTerm']['start_month'];
        } else {
            $month =  $term_data['BuTerm']['end_month'];
            #if($month < 12 ):eg - sm = 2022-01=>em = 2022-12 (no need to +1)
            if ($month < 12) $target_year = $target_year + 1;
        }
        $month = date("Y-m", strtotime($target_year . "-" . $month));
        return $month;
    }

    public function firstGpCode($year, $start_month, $end_month, $type_order, $name)
    {
        $this->Layer->virtualFields['layer_name'] = 'CONCAT(Layer.layer_code, "_/_", Layer.' . $name . ', IF(Layer.form = "" OR Layer.form IS NULL, "", CONCAT("_/_", Layer.form)), IF(Layer.item_1 = "" OR Layer.item_1 IS NULL, "", CONCAT("_/_", Layer.item_1)), IF(Layer.item_2 = "" OR Layer.item_2 IS NULL, "", CONCAT("_/_", Layer.item_2)))';
        $datas = $this->Layer->find('list', array(
            'conditions' => array(
                'Layer.flag' => 1,
                'DATE_FORMAT(Layer.from_date, "%Y-m") <=' => $start_month,
                'DATE_FORMAT(Layer.to_date, "%Y-m") >=' => $end_month,
                'Layer.type_order' => $type_order
            ),
            'fields' => array('layer_code', 'layer_name'),
            'order' => array('Layer.layer_order', 'Layer.layer_code')
        ));
        return $datas;
    }
    /* 
    * Add Comments 
    */
    public function saveAndUpdateComment()
    {
        $update_id = $this->request->data('update_id');
        $comment = $this->request->data('lcd_comment');
        $comment = htmlspecialchars($comment);
        $page_name = $this->request->data('page_name');
        $login_id = $this->Session->read('LOGIN_ID');
        $bu_term_id = $_SESSION['BU_TERM_ID'];
        $target_year = $page_name == 'LaborCosts' ? $this->request->data('target_yr') : $this->request->data('target_year');
        $layer_code = $this->request->data('layer_code') ? $this->request->data('layer_code') : $this->request->data('group_code');
        $date = new DateTime('now', new DateTimeZone(Setting::TIMEZONE));
        $date = $date->format('Y-m-d H:i:s');
        $msg_var = ($page_name == 'LaborCosts') ? 'lc' : 'lcd';
        if ($this->request->is('post')) {
            try {
                if (!empty($update_id)) {
                    $data['id'] = $update_id;
                    $data['bu_term_id'] = $bu_term_id;
                    $data['target_year'] = $target_year;
                    $data['layer_code'] = $layer_code;
                    $data["page_name"] = $page_name;
                    $data["comment"] = $comment;
                    $data["flag"] = 1;
                    $data["updated_by"] = $login_id;
                    $data["updated_date"] = $date;

                    $this->LcComment->create();
                    $status = $this->LcComment->save($data);

                    if ($status) {
                        $msg = parent::getSuccessMsg('SS002');
                        $this->Flash->success($msg, array('key' => $msg_var . '_success'));
                        $this->redirect(array('controller' => $page_name, 'action' => 'index'));
                    } else {
                        $msg = parent::getErrorMsg('SE003');
                        $this->Flash->error($msg, array('key' => $msg_var . '_error'));

                        $this->redirect(array('controller' => $page_name, 'action' => 'index'));
                    }
                } else {

                    $data['bu_term_id'] = $bu_term_id;
                    $data['target_year'] = $target_year;
                    $data['layer_code'] = $layer_code;
                    $data["page_name"] = $page_name;
                    $data["comment"] = $comment;
                    $data["flag"] = 1;
                    $data["created_by"] = $login_id;
                    $data["updated_by"] = $login_id;
                    $data["created_date"] = $date;
                    $data["updated_date"] = $date;

                    $this->LcComment->create();
                    $status = $this->LcComment->save($data);

                    if ($status) {
                        $msg = parent::getSuccessMsg('SS001');
                        $this->Flash->success($msg, array('key' => $msg_var . '_success'));
                        $this->redirect(array('controller' => $page_name, 'action' => 'index'));
                    } else {
                        $msg = parent::getErrorMsg('SE003');
                        $this->Flash->error($msg, array('key' => $msg_var . '_error'));

                        $this->redirect(array('controller' => $page_name, 'action' => 'index'));
                    }
                }
            } catch (Exception $e) {
                $msg = parent::getErrorMsg('SE003');
                $this->Flash->error($msg, array('key' => $msg_var . '_error'));
                CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                $this->redirect(array('controller' => $page_name, 'action' => 'index'));
            }
        }
    }
    /** 
     * getMenuId method
     * @author Hein Htet Ko
     * @return menu_id
     *
     */
    public function getMenuId($obj)
    {
        $page_name = $obj->name;
        $menu_id =  $this->Menu->find('first', array(
            'fields' => array('id'),
            'conditions' => array(
                'page_name' => $page_name,
                'flag' => 1,
            )
        ))['Menu']['id'];
        return $menu_id;
    }
    public function getPermissionsByRoleForBU($login_id, $role_id, $start_month, $end_month, $pagename, $maxLimit = 0)
    {
        if ($maxLimit == 0) $maxLimit = $this->getMenuLayer($pagename);
        $user_data = $this->User->find('first', array(
            'fields' => array('User.layer_code', 'User.login_code', 'User.role_id', 'User.layer_type_order'),
            'conditions' => array(
                'User.id' => $login_id,
                'User.flag' => '1'
            )
        ));
        $user_layer = explode("/", $user_data['User']['layer_code']);
        $user_role  = $user_data['User']['role_id'];
        $user_code  = $user_data['User']['login_code'];
        $user_layer_order  = $user_data['User']['layer_type_order'];
        $permissions = $this->Menu->find('all', array(
            'fields' => array('Menu.id', 'Menu.method', 'Permission.limit', 'Menu.base_status', 'Menu.next_status'),
            'conditions' => array(
                'Menu.page_name' => $pagename,
                'Menu.flag' => 1
            ),
            'joins' => array(
                array(
                    'table' => 'permissions',
                    'alias' => 'Permission',
                    'type'  =>  'right',
                    'conditions' => array(
                        'Permission.menu_id = Menu.id',
                        'Permission.role_id' => $role_id,
                    )
                ),
            ),
        ));
        $permission = [];
        $order = array('Layer.type_order', 'Layer.layer_order', 'Layer.layer_code');
        foreach ($permissions as $controller => $datas) {
            $method = $datas['Menu']['method'];
            $limit = $datas['Permission']['limit'];

            if ($limit != '') {
                $conditions = [];
                $conditions['DATE_FORMAT(Layer.from_date,"%Y-%m") <='] = $end_month;
                $conditions['DATE_FORMAT(Layer.to_date,"%Y-%m") >='] = $start_month;
                $conditions['Layer.flag'] = 1;
                $conditions['Layer.bu_status'] = 1;
                $conditions['Layer.type_order'] = $limit;
                $layer_array = [];
                $final_layer = [];
                $next_layers = [];
                if ($limit == 0) {
                    $layer_array = [];
                    $conditions['Layer.type_order'] = $maxLimit;
                    $final_layer = $this->Layer->find('list', array(
                        'fields' => array('Layer.layer_code'),
                        'conditions' => $conditions,
                        'order' => $order
                    ));
                } else {
                    if ($user_layer_order == $limit) {
                        $conditions['Layer.layer_code'] = $user_layer;
                        $layers = $this->Layer->find('list', array(
                            'fields' => array('Layer.layer_code'),
                            'conditions' => $conditions,
                            'order' => $order
                        ));
                        $layer_array = $layer_array + $layers;
                    } elseif ($user_layer_order < $limit) { #child
                        foreach ($user_layer as $code) {
                            $conditions['Layer.parent_id LIKE '] = '%' . $code . '%';
                            $layers = $this->Layer->find('list', array(
                                'fields' => array('Layer.layer_code'),
                                'conditions' => $conditions,
                                'order' => $order
                            ));
                            $layer_array = $layer_array + $layers;
                        }
                    } elseif ($user_layer_order > $limit) { #parent
                        $conditions['Layer.layer_code'] = $user_layer;
                        unset($conditions['Layer.type_order']);
                        $layers = $this->Layer->find('list', array(
                            'fields' => array('Layer.layer_code', 'Layer.parent_id'),
                            'conditions' => $conditions,
                            'order' => $order
                        ));
                        foreach ($layers as $codes => $parent) {
                            $layers = json_decode($parent, true)["L" . $limit];
                            $layer_array[] = $layers;
                        }
                    }
                }
                $conditions = [];
                $conditions['DATE_FORMAT(Layer.from_date,"%Y-%m") <='] = $end_month;
                $conditions['DATE_FORMAT(Layer.to_date,"%Y-%m") >='] = $start_month;
                $conditions['Layer.flag'] = 1;
                $conditions['Layer.bu_status'] = 1;
                $conditions['Layer.type_order'] = $maxLimit;
                foreach ($layer_array as $value) {
                    if ($maxLimit == $limit) {
                        $conditions['Layer.layer_code'] = $value;
                        $layers = $this->Layer->find('list', array(
                            'fields' => array('Layer.layer_code'),
                            'conditions' => $conditions,
                            'order' => $order
                        ));
                        $final_layer = $final_layer + $layers;
                    } elseif ($maxLimit > $limit) {
                        $conditions['Layer.parent_id LIKE '] = '%' . $value . '%';
                        $layers = $this->Layer->find('list', array(
                            'fields' => array('Layer.layer_code'),
                            'conditions' => $conditions,
                            'order' => $order
                        ));
                        $final_layer = $final_layer + $layers;
                    } elseif ($maxLimit < $limit) {
                        $conditions['Layer.layer_code'] = $value;
                        unset($conditions['Layer.type_order']);
                        $layers = $this->Layer->find('list', array(
                            'fields' => array('Layer.layer_code', 'Layer.parent_id'),
                            'conditions' => $conditions,
                            'order' => $order
                        ));
                        foreach ($layers as $codes => $parent) {
                            $layers = json_decode($parent, true)["L" . $maxLimit];
                            $final_layer[] = $layers;
                            $next_chk = json_decode($parent, true)["L" . ($maxLimit + 1)];
                            if (!empty($next_chk)) $next_layers[] = $next_chk;
                            else $next_layers[] = $codes;
                        }
                    }
                }
                $permission[$method]['limit'] = $limit;
                $permission[$method]['layer_no'] = $maxLimit;
                $permission[$method]['user_layer_order'] = $user_layer_order;
                $permission[$method]['layer'] = array_unique($final_layer);
                $all_cond = [];
                $all_merge_layers = [];
                $all_cond['DATE_FORMAT(Layer.from_date,"%Y-%m") <='] = $end_month;
                $all_cond['DATE_FORMAT(Layer.to_date,"%Y-%m") >='] = $start_month;
                $all_cond['Layer.flag'] = 1;
                $all_cond['Layer.bu_status'] = 1;
                foreach ($final_layer as $bu_layer) {
                    $all_cond['Layer.parent_id LIKE '] = '%' . $bu_layer . '%';
                    $all_layers = $this->Layer->find('list', array(
                        'fields' => array('Layer.id', 'Layer.layer_code', 'Layer.type_order'),
                        'conditions' => $all_cond,
                        'order' => $order
                    ));
                    $all_merge_layers[$bu_layer] = $all_layers;
                }
                if (!empty($next_layers)) {
                    $permission[$method]['next_layers'][$maxLimit + 1] = $next_layers;
                }
                $permission[$method]['all_layer'] = $all_merge_layers;
                $permission[$method]['all_layer'][$maxLimit] = $permission[$method]['layer'];
            }
        }
        return $permission;
    }
}
