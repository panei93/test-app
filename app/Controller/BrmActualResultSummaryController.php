<?php

/**
 * ActualResults Controller
 *
 * @property ActualResult $ActualResult
 * @property PaginatorComponent $Paginator
 */
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');

class BrmActualResultSummaryController extends AppController
{

/**
 * Components
 *
 * @var array
 */

    public $components = array('Paginator','PhpExcel.PhpExcel','Flash');
    public $uses = array('BrmActualResultSummary');

    //khin
    public function getMonthlyResultByIndex($ba_code, $month, $account_code, $index_no)
    {
        $ba_code = str_replace("'", '', $ba_code);
        $account_code = explode(',', str_replace("'", '', $account_code));
        $index_no = explode(',', str_replace("'", '', $index_no));

        $this->BrmActualResultSummary->virtualFields['total'] = 'SUM(amount)';
        $data = $this->BrmActualResultSummary->find('all', array(
            'fields' => 'total',
            'conditions' => array(
                'ba_code' => $ba_code,
                'account_code' => $account_code,
                'target_month' => $month,
                'transaction_key' => $index_no
            ),
        ));

        
        if (isset($data[0]['BrmActualResultSummary']['total'])) {
            return $data[0]['BrmActualResultSummary']['total'];
        } else {
            return 0;
        }
    }

    public function getYearlyResultByIndex($ba_code, $start_month, $end_month, $account_code, $index_no)
    {
        $ba_code = str_replace("'", '', $ba_code);
        $account_code = explode(',', str_replace("'", '', $account_code));
        $index_no = explode(',', str_replace("'", '', $index_no));

        $this->BrmActualResultSummary->virtualFields['total'] = 'SUM(amount)';
        $data = $this->BrmActualResultSummary->find('all', array(
            'fields' => 'total',
            'conditions' => array(
                'ba_code' => $ba_code,
                'account_code' => $account_code,
                'target_month >=' => $start_month,
                'target_month <=' => $end_month,
                'transaction_key' => $index_no
            ),
        ));

        
        if (isset($data[0]['BrmActualResultSummary']['total'])) {
            return $data[0]['BrmActualResultSummary']['total'];
        } else {
            return 0;
        }
    }
}
