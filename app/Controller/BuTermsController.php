<?php
App::uses('CakeText', 'Utility');
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');

class BuTermsController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('BuTerm', 'RefTermChangeLog', 'Layer', 'Position', 'LaborCost', 'LaborCostDetail', 'BudgetComp', 'BudgetHyoka', 'BudgetPoint', 'BudgetSng', 'Budget', 'EmpNum', 'LaborCostDetail', 'LaborCost', 'SettlementTerm', 'TransactionPolicy');
    public $components = array('Session', 'Flash', 'Paginator', 'PhpExcel.PhpExcel');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
    }

    # table list that is connected with bu_terms table
    private $tables = array(
        "budget_points",
        "budget_comps",
        "budget_hyokas",
        "budget_sngs",
        "emp_nums",
        "labor_costs",
        "labor_cost_details",
        "settlement_terms",
        "transaction_policy",
        "budgets"
    );

    # tables of model lists
    private $table_list = array(
        'BudgetPoint',
        'BudgetComp',
        'BudgetHyoka',
        'BudgetSng',
        'EmpNum',
        'LaborCost',
        'LaborCostDetail',
        'SettlementTerm',
        'TransactionPolicy',
        'Budget',
    );

    # month array to calculate
    public $months = array(
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    );

    /**
     * index method
     * @author Zeyar Min
     */

    public function index()
    {
        $this->Session->write('LAYOUT', 'buanalysis');
        $this->layout = 'buanalysis';
        $errormsg   = "";
        $successMsg = "";

        $conditions = [];
        $conditions["BuTerm.flag"] = 1;

        $ref_term = $this->BuTerm->find('all', array(
            'fields' => array('id', 'term_name'),
            'conditions' => array(
                'flag' => 1
            ),
        ));

        $this->paginate = array(
            'limit' => Paging::TABLE_PAGING,
            'conditions' => $conditions,
            'joins' => array(
                array(
                    'table' => 'bu_terms',
                    'alias' => 'RefTermName',
                    'type'  =>  'left',
                    'conditions' => array(
                        'RefTermName.id = BuTerm.ref_term'
                    )
                )
            ),
            'fields' => array(
                'BuTerm.id',
                'BuTerm.term_name',
                'BuTerm.budget_year',
                'BuTerm.start_month',
                'BuTerm.end_month',
                'BuTerm.ref_term',
                'BuTerm.flag',
                'RefTermName.term_name as ref_term'
            ),
            'order' => 'BuTerm.budget_year',
            'group' => 'BuTerm.id'
        );

        $termsList = $this->Paginator->paginate('BuTerm');

        #total row msg
        $rowCount = $this->params['paging']['BuTerm']['count'];

        if (!$rowCount) {
            $this->set('errmsg', parent::getErrorMsg('SE001'));
            $this->set('succmsg', "");
        } else {
            $this->set('succmsg', parent::getSuccessMsg('SS004', $rowCount));
            $this->set('errmsg', "");
        }

        $startMonth = $this->months;
        $this->Session->write('SELECTION', 'SET');
        $this->set(compact('termsList', 'ref_term', 'rowCount', 'startMonth'));
        return $this->render('index');
    }

    /**
     * Save and Update Terms method
     * @author Zeyar Min (Modified Kaung Htet San)
     */
    public function saveUpdateTerm()
    {

        if ($this->request->is('post')) {

            $this->layout = 'buanalysis';

            # superrr clean code to get many request data by destructuring the array
            list(
                $update_id, $term_name, $ref_term,
                $budget_year, $update_budget_year, $start_month,
                $end_month, $created_by, $updated_by,
                $created_date, $updated_date, $action
            ) =  $this->getFormRequestData();

            if ((int)$update_id) { # Update_Term

                # first fetch the old record with update_id
                $oldTerm = $this->BuTerm->find('first', array(

                    'conditions' => array(
                        'BuTerm.id' => $update_id,
                        'BuTerm.flag' => 1
                    ),

                    'fields' => array('ref_term', 'id')
                ));

                # check the old term is exists or not, if not redirect with error message
                if (!$oldTerm) {

                    $errorMsg = parent::getErrorMsg('SE050');
                    $this->Flash->set($errorMsg, array("key" => "BUTermsFail"));

                    $this->redirect(array(
                        'controller' => 'BuTerms',
                        'action' => $action
                    ));
                }

                # get the old term's reference term id
                $oldTerm_Ref_term_Id = (int)$oldTerm['BuTerm']['ref_term'];

                /**
                 * check if old term's ref term id and new ref_term are equal or not
                 * if equal, 0. if not, 1
                 */

                $flagToUpdate_Ref_term = ($oldTerm_Ref_term_Id != $ref_term) ? 1 : 0;

                /**
                 * check if there is already an term_name with different id or not 
                 * If there is term_name with diff id, it means cannot update with that request's name
                 * Getting all query of Bu_Term that is different with update_id
                 */

                $duplicated_update = $this->BuTerm->find('first', array(
                    'conditions' => array(
                        'BuTerm.id !=' => $update_id,
                        'BuTerm.term_name' => $term_name,
                        'BuTerm.flag' => 1
                    )
                ));

                if (!$duplicated_update) { # not other duplicated terms with that update term name condition

                    $oldRecordCreatedId = $oldTerm['BuTerm']['created_by'];
                    $oldCreatedDate = $oldTerm['BuTerm']['created_date'];

                    try {

                        $attachDB = $this->BuTerm->getDataSource();
                        $saveTerm['id'] = $update_id;
                        $saveTerm['term_name'] = trim($term_name);
                        $saveTerm['ref_term'] = $ref_term;

                        # if want to update budget year is not same with old budget year, it means we need to update the budget year
                        $saveTerm['budget_year'] = ($update_budget_year !== $budget_year) ? $update_budget_year : $budget_year;

                        $saveTerm['start_month'] = $start_month;
                        $saveTerm['end_month'] = $end_month;
                        $saveTerm['flag'] = 1;

                        $saveTerm['created_by'] = $oldRecordCreatedId;
                        $saveTerm['updated_by'] = $updated_by;

                        $saveTerm['created_date'] = $oldCreatedDate;
                        $saveTerm['updated_date'] = $updated_date;

                        $status = $this->BuTerm->save($saveTerm);

                        if ($status) {

                            // need to make decision to let the update with flag = 1 + count condition for 10 table
                            if ($flagToUpdate_Ref_term) {

                                # making query to get the target year of ref term
                                $checkRefTermYear = $this->BuTerm->find('first', array(
                                    'conditions' => array(
                                        'id' => $saveTerm['ref_term'],
                                        'flag' => 1
                                    ),
                                    'fields' => 'budget_year'
                                ));

                                # get the target year
                                $wantToRefTermYear = $checkRefTermYear["BuTerm"]["budget_year"];

                                # this is requested new term's target year
                                $targetYear =  $saveTerm['budget_year'];

                                # check with method if there are years that can be copied or not
                                $canCopyYears = $this->calculateBackAndFrontYear($wantToRefTermYear, $targetYear);

                                # if not empty array and the flag is also 1, we can copy the mutual year
                                if ($canCopyYears) {

                                    # copy the term data with new ref_term_id
                                    # pass to second parameter => 1 to make a flag between update copy and create copy.
                                    # if flag = 1, means update and copy at the same time

                                    $copyResult = $this->copyTermData($saveTerm['ref_term'], 1, null, $action, $canCopyYears);

                                    /**
                                     *   if (!$copyResult) {
                                     *       $attachDB->rollback();
                                     *   }
                                     */
                                }
                            }

                            # GET update_id's record id from $db_ref_term
                            $id = $oldTerm["BuTerm"]["id"];

                            # GET old ref_term id from $db_ref_term
                            $oldRefTermId = $oldTerm["BuTerm"]["ref_term"];

                            /**
                             * Checking if the ref_term_id is updated or not
                             * If updated, add the data to the record table as history
                             */

                            if ($oldRefTermId !== $ref_term) {

                                $record = [];
                                $record["created_id"] = $this->Session->read('LOGIN_ID');
                                $record["bu_term_id"] = $id;
                                $record["old_ref"] = $oldRefTermId;
                                $record["new_ref"] = $saveTerm['ref_term'];
                                $record["updated_date"] = date("Y-m-d H:i:s");
                                $record["created_date"] = date("Y-m-d H:i:s");

                                $this->RefTermChangeLog->save($record);
                            }

                            $attachDB->commit();

                            $msg = parent::getSuccessMsg('SS002');
                            $this->Flash->set($msg, array('key' => 'BUTermsSuccess'));
                            $this->redirect(array('controller' => 'BuTerms', 'action' => $action));
                        } else {

                            $attachDB->rollback();
                            $msg = parent::getErrorMsg('SE003');
                            $this->Flash->set($msg, array('key' => 'BUTermsFail'));
                            $this->redirect(array('controller' => 'BuTerms', 'action' => $action));
                        }
                    } catch (Exception $e) {

                        $attachDB->rollback();

                        $msg = parent::getErrorMsg('SE011', __("変更"));

                        $this->Flash->set($msg, array('key' => 'BUTermsFail'));

                        CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());

                        $this->redirect(array(
                            'controller' => 'BuTerms',
                            'action' => $action
                        ));
                    }
                } else {

                    $msg = parent::getErrorMsg('SE002', __("期間名"));

                    $this->Flash->set($msg, array('key' => 'BUTermsFail'));

                    $this->redirect(array(
                        'controller' => 'BuTerms',
                        'action' => $action
                    ));
                }
            } else { # Create_Term

                # Checking if there is already duplicated term_name in the bu_tems tables or not
                $duplicated_term = $this->BuTerm->find('first', array(
                    'conditions' => array(
                        'term_name' => $term_name,
                        'flag' => 1
                    )
                ));

                if (empty($duplicated_term)) { # nope! no duplicate term_name condition

                    try {

                        $attachDB = $this->BuTerm->getDataSource();

                        # make an associative array to save form data
                        $saveTerm['term_name'] = trim($term_name);
                        $saveTerm['ref_term'] = $ref_term;
                        $saveTerm['budget_year'] = $budget_year;
                        $saveTerm['start_month'] = $start_month;
                        $saveTerm['end_month'] = $end_month;
                        $saveTerm['flag'] = 1;
                        $saveTerm['created_by'] = $created_by;
                        $saveTerm['updated_by'] = $updated_by;
                        $saveTerm['created_date'] = $created_date;
                        $saveTerm['updated_date'] = $updated_date;

                        $status = $this->BuTerm->save($saveTerm);

                        if ($ref_term != 0) { # example => 2  it means ref_terms is used

                            # making query to get the target year of ref term

                            $checkRefTermYear = $this->BuTerm->find('first', array(
                                'conditions' => array(
                                    'id' => $ref_term,
                                    'flag' => 1
                                ),
                                'fields' => 'budget_year'
                            ));

                            # get the target year
                            $wantToRefTermYear = $checkRefTermYear["BuTerm"]["budget_year"];

                            # this is requested new term's target year
                            $targetYear =  $saveTerm['budget_year'];

                            $canCopyYears = $this->calculateBackAndFrontYear($wantToRefTermYear, $targetYear);

                            if ($canCopyYears) { # if not empty array we can copy the mutual year

                                $this->copyTermData($ref_term, 0, null, $action, $canCopyYears);
                            }
                        }
                        if ($status) {

                            $attachDB->commit();
                            $msg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($msg, array('key' => 'BUTermsSuccess'));
                            $this->redirect(array('controller' => 'BuTerms', 'action' => $action));
                        } else {

                            $attachDB->rollback();
                            $msg = parent::getErrorMsg('SE003');
                            $this->Flash->set($msg, array('key' => 'BUTermsFail'));
                            $this->redirect(array('controller' => 'BuTerms', 'action' => $action));
                        }
                    } catch (Exception $e) {

                        $attachDB->rollback();

                        $msg = parent::getErrorMsg('SE011', __("保存"));

                        $this->Flash->set($msg, array('key' => 'BUTermsFail'));

                        CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());

                        $this->redirect(array(
                            'controller' => 'BuTerms',
                            'action' => $action
                        ));
                    }
                } else {

                    $msg = parent::getErrorMsg('SE002', __("期間名"));
                    $this->Flash->set($msg, array('key' => 'BUTermsFail'));
                    $this->redirect(array(
                        'controller' => 'BuTerms',
                        'action' => $action
                    ));
                }
            }
        }
    }

    /**
     * custom end month calculation method
     * Zeyar Min
     * @param $start_month, $num_of_months
     */
    private function customEndMonthFromStartMonth($start_month, $num_of_months = 12)
    {
        # Calculate the end month's numeric value
        $end_month = ($start_month + $num_of_months - 1) % 12;

        # Handle the special case for December
        return $end_month === 0 ? $end_month = 12 : $end_month;
    }

    /**
     * custom calculation the mutual years between two array of years
     * 
     * @author Kaung Htet San
     * @param mixed existingYear, targetYear
     */
    private function calculateBackAndFrontYear($existingYear, $targetYear)
    {
        # note! use debug() both of arrays for better understanding

        $toCheckExistingYearArr = array();

        for ($i = 1; $i < 7; $i++) {

            if ($i < 4) {
                $toCheckExistingYearArr[] = $existingYear - $i;
            } else {
                $toCheckExistingYearArr[] = $existingYear + $i - 4;
            }
        }

        $toCheckNewYearArr = array();

        for ($i = 1; $i < 7; $i++) {

            if ($i < 4) {
                $toCheckNewYearArr[] = $targetYear - $i;
            } else {
                $toCheckNewYearArr[] = $targetYear + $i - 4;
            }
        }

        # filter the year that includes in both of array
        $unsortedYears = array_intersect($toCheckNewYearArr, $toCheckExistingYearArr);

        # sort the unorded years
        sort($unsortedYears);

        return $unsortedYears;
    }

    /**
     * Get the term data method
     * @author Zeyar Min
     * @return null
     */
    public function getTerm()
    {
        parent::checkAjaxRequest($this);

        $edit_id = $this->request->data('id');

        $position_data = $this->BuTerm->find('first', array(
            'conditions' => array(
                'BuTerm.id' => $edit_id,
                'BuTerm.flag' => 1
            )
        ));

        $terms = $this->BuTerm->find("all", array(
            'conditions' => array(
                'BuTerm.id !=' => $edit_id,
                'BuTerm.flag' => 1
            ),
            'fields' => array('term_name', 'id'),
        ));

        $response = $position_data['BuTerm'];
        $response['term_id'] = $response['id'];
        $response['terms'] = $terms;

        foreach ($this->table_list as $tableName) {

            $childRow = $this->$tableName->find('first', array(
                'conditions' => array(
                    $tableName . '.bu_term_id' => $edit_id,
                )
            ));

            CakeLog::write('info', print_r($childRow, true));

            # set flag 1 or 0 based on childRows' counts
            $response["flag"] = $childRow ? 1 :  0;

            CakeLog::write('info',  $response["flag"]);

            break; // reason for this is even the first table count is greater than zero it means there is 100% referenced
        }

        echo json_encode($response);
    }

    /**
     * Delete term data method
     * @author Zeyar Min
     */
    public function deleteTerm()
    {
        if ($this->request->is('post')) {

            # page_no is like => page:3 or page:2 
            $page_no = $this->request->data('hiddenPageNo');

            # making route based on page name
            # example => if page_no is just BuTerms we go back to just index, if not we go back like this index/page:2...
            $action = ($page_no === "BuTerms") ? 'index/' : 'index/' . $page_no;

            #check the record's id that we want to delet is exists or not 
            $wantToDeleteId = $this->request->data["hiddenDeletedId"];

            $wantToDeleteRecord = $this->BuTerm->find('first', array(
                'fields' => 'term_name',
                'conditions' => array(
                    'BuTerm.id' => $wantToDeleteId,
                    'BuTerm.flag' => 1
                )
            ));

            # if there is no record with that id we redirect with error message
            if (!$wantToDeleteRecord) {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array("key" => "BUTermsFail"));
                $this->redirect(array(
                    'controller' => 'BuTerms',
                    'action' => $action
                ));
            };

            $deleted_id = (int)$this->request->data('hiddenDeletedId');
            $login_id = $this->Session->read('LOGIN_ID');

            try {

                # check if there are records that are referencing this current wanted to delete term's ID as the ref_term

                $related_ref = $this->BuTerm->find('all', array(
                    'fields' => array('term_name', 'ref_term'),
                    'conditions' => array(
                        'BuTerm.id !=' => $deleted_id,
                        'BuTerm.ref_term' => $deleted_id,
                        'BuTerm.flag' => 1
                    )
                ));

                if (!$related_ref) { # if there is no records, we are cool to delete

                    $tables = array_combine($this->table_list, $this->tables);

                    # loop the array of table and insert the wanted to copy data


                    foreach ($tables as $modelName => $tableName) {

                        $conditions = [$modelName . ".bu_term_id" => $wantToDeleteId];

                        $copy_datas = [];

                        $copy_datas = $this->$modelName->find('all', array(
                            'conditions' => $conditions
                        ));

                        if ($copy_datas) {

                            # to make the amount to chunk the array based on the length of array
                            $chunkCount = ceil(count($copy_datas) / 10); #8

                            # chunk the array into the arry of arrays to insert data faster
                            # [ array length 50] => [ [10], [10], [10], [10], [10] ] 

                            $chunkedData = array_chunk($copy_datas,  $chunkCount);

                            $count = (int)count($chunkedData);

                            while ($count) {

                                $offset = 0;

                                $this->$modelName->query("DELETE FROM $tableName WHERE bu_term_id = $deleted_id LIMIT $chunkCount");

                                $count -= 1;
                                $offset += $chunkCount;
                            }
                        }
                    }


                    $delete_term = array(
                        'id' => $deleted_id,
                        'flag' => 0,
                        'updated_by' => $login_id,
                        'updated_date' => date("Y-m-d H:i:s")
                    );

                    $delete_status = $this->BuTerm->save($delete_term);

                    if ($delete_status) {

                        $msg = parent::getSuccessMsg("SS003");
                        $this->Flash->set($msg, array('key' => 'BUTermsSuccess'));

                        # this condition is when number of paginated count is 1 and lets say page is 3
                        # we good to the page 2;
                        if ($this->request->data['hiddenRecordCount'] == 1) {

                            # reducing page count -1. So we can go to page 2 from page 3
                            $toPage = $this->request->data['hiddenTotalPageCount'] - 1;

                            # this condition is when page num is zero,
                            # we don't go like this => index/page:0 
                            # we go like this => index
                            if ($toPage === 0 || $toPage === 1) {
                                $this->redirect(array(
                                    'controller' => 'BuTerms',
                                    'action' => 'index'
                                ));
                            }
                            # this condition is when page num is not zero,
                            $this->redirect(array(
                                'controller' => 'BuTerms',
                                'action' => 'index/' . "page:" . $toPage
                            ));
                        }

                        # this is not for paginated count is 1 condition
                        $this->redirect(array(
                            'controller' => 'BuTerms',
                            'action' => $action
                        ));
                    } else {
                        $errorMsg = parent::getErrorMsg('SE050');
                        $this->Flash->set($errorMsg, array("key" => "BUTermsFail"));
                        $this->redirect(array(
                            'controller' => 'BuTerms',
                            'action' => $action
                        ));
                    }
                }
            } catch (Exception $e) {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array('key' => 'BUTermsFail'));
                CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                $this->redirect(array(
                    'controller' => 'BuTerms',
                    'action' => $action
                ));
            }
        }
    }

    /**
     * Copy term data method
     * 
     * @author Kaung Htet San
     * 
     * @date 11/24/2023
     * 
     * @return boolean
     * 
     * @param mixed $ref_term => Id of the term that we wanted to refrence
     * @param mixed $flag => If 1 => update copy, zero or false => create copy
     * @param mixed $action => routes => page:2 or page:3, etc
     * @param mixed $updateTermId => Id of wanted to update term
     * @param mixed $canCopyYears =>  Array of sorted years that can copy from ref_term's data
     */

    private function copyTermData($ref_term, $flag, $updateTermId, $action, $canCopyYears)
    {
        $updated_by = $this->Session->read('LOGIN_ID');

        # this condition is for checking where updateTermId is exists or not
        if ($updateTermId) {
            $last_term_id = $updateTermId;
        } else {
            # get all bu_terms records by desc order
            $term_datas = $this->BuTerm->find('all', array(
                'fields' => 'id,term_name,flag',
                'order' => 'id DESC'
            ));

            # why hard code index zero ? well we order desc and index zero will become biggest id
            $last_term_id = $term_datas[0]['BuTerm']['id']; #example 3
        }
        $copy_term_id = $last_term_id;
        /*if ($flag) { # for update method copy
            $copy_term_id = $last_term_id; #example 3
        } else { # for create method copy
            $copy_term_id = $last_term_id + 1; #example 3+1 => 4
        }*/

        try {

            # loop the array of table and insert the wanted to copy data
            foreach ($this->table_list as $tableName) {

                $copy_datas = [];

                if ($tableName === "Budget") {

                    array_unshift($canCopyYears, $canCopyYears[0] - 1);
                }

                $copy_datas = $this->$tableName->find('all', array(
                    'conditions' => array(
                        $tableName . '.target_year IN' => $canCopyYears,
                        $tableName . '.bu_term_id' => $ref_term,
                    )
                ));

                if ($copy_datas && $copy_datas[0][$tableName]['id'] != '') {

                    $attachDB = $this->$tableName->getDataSource();
                    $attachDB->begin();

                    # to make the amount to chunk the array based on the length of array
                    $chunkCount = ceil(count($copy_datas) / 10);

                    # chunk the array into the arry of arrays to insert data faster
                    # [ array length 50] => [ [10], [10], [10], [10], [10] ] 

                    $chunkedData = array_chunk($copy_datas,  $chunkCount);

                    foreach ($chunkedData as $chunk) {

                        $save_data = [];

                        foreach ($chunk as $each_data) {

                            $tmp = [];

                            unset($each_data[$tableName]['id']);
                            unset($each_data[$tableName]['created_date']);
                            unset($each_data[$tableName]['updated_date']);

                            $tmp = $each_data[$tableName];

                            foreach ($each_data[$tableName] as $field => $value) {

                                # commented this because I think it is not necessary to check with conditions

                                # if (!empty($value) || $value != '') {

                                $tmp[$field] = $value;

                                #}
                            }

                            $tmp['bu_term_id'] = $copy_term_id;
                            $tmp['updated_by'] = $updated_by;
                            $tmp["updated_date"] = date("Y-m-d H:i:s");
                            $tmp["created_date"] = date("Y-m-d H:i:s");
                            $save_data[] = $tmp;
                        }

                        #Save new data
                        $save = $this->$tableName->saveAll($save_data);

                        if (!$save) {

                            $attachDB->rollback();

                            throw new Exception("can't copy data for " . $tableName, 1);

                            # temporarily added return if one table is not in condition, we break the loop for all table
                            return false;
                        }
                    }

                    $attachDB->commit();
                } else {

                    # this condition is to skip the loop if current table's wanted to copy records are empty due to some conditions
                    continue;
                }
            }

            # added boolean to return condition in 11/29/2023
            return true;
        } catch (Exception $e) {

            foreach ($this->table_list as $tableName) {
                
                $this->$tableName->deleteAll(array(
                    'bu_term_id' => $copy_term_id,
                ), false);
                /*$attachDB = $this->$tableName->getDataSource();
                $attachDB->rollback();*/
            }
            $this->BuTerm->deleteAll(array(
                'id' => $copy_term_id,
            ), false);
            
            $errorMsg = parent::getErrorMsg('SE092');
            $this->Flash->set($errorMsg, array("key" => "BUTermsFail"));

            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'BuTerms', 'action' => $action));
        }
    }

    /**
     * Store the form request data and return as array
     * 
     * @author Kaung Htet San
     * @date 11/29/2023
     * @return array
     */

    private function getFormRequestData(): array
    {
        $request_data = $this->request->data;

        # just form data nothing special
        $update_id = $request_data['update_id'];

        $term_name = h($request_data['term_name']);
        $ref_term = $request_data['hiddenRefTerm'];

        # current budget year
        $budget_year = $request_data['budget_year'];

        # want to update budget year
        $update_budget_year = $request_data['update_budget_year'];

        $start_month = intval($request_data['start_month']);
        $end_month = $this->customEndMonthFromStartMonth($start_month, 12);

        $created_by = $this->Session->read('LOGIN_ID');
        $updated_by = $this->Session->read('LOGIN_ID');

        $created_date = date("Y-m-d H:i:s");
        $updated_date = date("Y-m-d H:i:s");

        #example => page:2 or page:3 or BuTerms
        $page_no = $request_data['hiddenPageNo'];

        # making route based on page name
        # example => if page_no is just BuTerms we go back to just index, if not we go back like this index/page:2...
        $action = ($page_no === "BuTerms") ? 'index/' : 'index/' . $page_no;

        return [$update_id, $term_name, $ref_term, $budget_year, $update_budget_year, $start_month,  $end_month, $created_by, $updated_by, $created_date, $updated_date, $action];
    }
}
