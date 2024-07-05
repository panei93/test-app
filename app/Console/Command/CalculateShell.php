<?php 
App::uses('Enum', 'Controller/Component');
App::uses('Setting', 'Controller/Component');
class CalculateShell extends AppShell {
    public $uses = array('Layer','Message','BrmBudget','BrmMainBudget','BrmActualResultSummary','BrmActualResult','BrmMainResult','BrmForecastSummary','BrmBudgetSummary','BrmBudgetPrime','BrmExpected','BrmCronLog', 'BrmLogistic');
    public function main() {

        $mainbudget = $this->BrmMainBudget->getDataSource();
        $mainresult = $this->BrmMainResult->getDataSource();

        try {
            $logid_arr = [];
            
            $cron_log = $this->BrmCronLog->find('all',array(
                'fields' => array('id','brm_term_id','hlayer_code','layer_code'),
                'conditions' => array(
                    'BrmCronLog.flag' => 1
                ),
                'group' => array('brm_term_id'),
            ));
            
            if (count($cron_log) > 0) {
                
                $mainbudget->begin();
                $mainresult->begin();
                
                foreach ($cron_log as $log_data) {
                    $conditions = [];

                    $id         = $log_data['BrmCronLog']['id'];
                    $term_id    = $log_data['BrmCronLog']['brm_term_id'];
                    $hlayer_code = $log_data['BrmCronLog']['hlayer_code'];
                    $layer_code    = $log_data['BrmCronLog']['layer_code'];

                    $conditions['hlayer_code'] = $hlayer_code;
                    if ($layer_code != 0) {
                        $conditions['layer_code'] = $layer_code;
                    } else {
                        $conditions['layer_code'] = $this->Layer->find('list',array(
                            'fields' => array('Layer.layer_code'),
                            'conditions' => array(
                                'flag' => 1,
                                'Layer.parent_id LIKE ' => '%'.$hlayer_code.'%',
                                'Layer.type_order' => Setting::LAYER_SETTING['bottomLayer']
                            )
                        ));
                    }
                    
                    if ($term_id != 0) { #having term_id means this is budget data
                        $conditions['term_id'] = $term_id;
                        $this->Message->query("update brm_budgets set logistic_index_no='' where logistic_index_no is NULL;");
                        $this->BrmMainBudget->query("DELETE FROM `brm_main_budgets` WHERE brm_term_id=".$term_id.";");
                        $this->BrmMainBudget->query("INSERT INTO `brm_main_budgets` (brm_term_id, brm_account_id, brm_account_name_jp, target_month, hlayer_name, hlayer_code, dlayer_name, dlayer_code, layer_name_jp, layer_code, index_name, transaction_key, amount) 
                            SELECT
                            `BudgetModel`.`brm_term_id`,
                            `SubAccountModel`.`id` AS `sub_acc_id`, 
                            `SubAccountModel`.`name_jp`, 
                            `BudgetModel`.`target_month`,
                            layersHQ.name_jp,
                            layersHQ.layer_code,
                            layersDEPT.name_jp,
                            layersDEPT.layer_code,
                            `Layer`.`name_jp`,
                            `BudgetModel`.`layer_code`,
                            `LogisticModel`.`index_name`, 
                            `BudgetModel`.`logistic_index_no` as transaction_key, 
                            (SUM(amount)) AS  `amount` 
                            FROM `brm_budgets` AS `BudgetModel` 
                            left JOIN `brm_saccounts` AS `AccountModel` ON (
                                `AccountModel`.`account_code` = `BudgetModel`.`account_code` AND
                                `AccountModel`.`flag` = 1
                            ) left JOIN `brm_accounts` AS `SubAccountModel` ON (
                                `SubAccountModel`.`id` = `AccountModel`.`brm_account_id` AND
                                `SubAccountModel`.`flag` = 1
                            ) left JOIN `layers` AS `Layer` ON (
                                `Layer`.`layer_code` = `BudgetModel`.`layer_code` AND
                                `Layer`.`flag` = 1
                            ) left JOIN `layers` as `layersHQ` ON (
                                Layer.parent_id LIKE CONCAT('%', `layersHQ`.`layer_code`, '%') AND `layersHQ`.`type_order` = ".Setting::LAYER_SETTING['topLayer']." AND 
                                `layersHQ`.`flag` = 1 AND Layer.flag=1
                            )LEFT JOIN `layers` as `layersDEPT` ON (
                                Layer.parent_id LIKE CONCAT('%', `layersDEPT`.`layer_code`, '%')  AND `layersDEPT`.`type_order` = ".Setting::LAYER_SETTING['middleLayer']." AND
                                `layersDEPT`.`flag` = 1 AND Layer.flag=1
                            ) left JOIN (select distinct index_name,`brm_logistics`.`index_no`,layer_code,flag from `brm_logistics` group by layer_code)as `LogisticModel` ON (
                                (`BudgetModel`.`logistic_index_no` = `LogisticModel`.`index_no` OR `BudgetModel`.`logistic_index_no` = index_name) AND
                                `BudgetModel`.`logistic_index_no` != '' AND
                                `BudgetModel`.`layer_code` = `LogisticModel`.`layer_code` AND
                                `LogisticModel`.`flag` = 1
                            )
                            WHERE `BudgetModel`.`account_code` != 0 AND `BudgetModel`.`brm_term_id` =".$term_id."
                            GROUP BY 
                            `BudgetModel`.`brm_term_id`, 
                            `BudgetModel`.`target_month`, 
                            `BudgetModel`.`layer_code`, 
                            `BudgetModel`.`logistic_index_no`, 
                            `AccountModel`.`brm_account_id`  
                            ORDER BY 
                            `BudgetModel`.`brm_term_id` ASC, 
                            `BudgetModel`.`target_month` ASC, 
                            `layersHQ`.`layer_code` ASC, 
                            `layersDEPT`.`layer_code` ASC, 
                            `BudgetModel`.`layer_code` ASC, 
                            `AccountModel`.`brm_account_id` ASC,
                            `BudgetModel`.`logistic_index_no` ASC;"
                        );
                        $this->out("Successfully update budget! ".date("Y/m/d h:i:s"));
                        CakeLog::write('debug', 'Successfully update budget! '.date("Y/m/d h:i:s").'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    } else {
                        $this->Message->query("update brm_actual_result_summary set transaction_key='' where transaction_key is NULL;");
                        $this->BrmMainResult->query("DELETE FROM `brm_main_results`");
                        $this->BrmMainResult->query("INSERT INTO `brm_main_results` (id, brm_account_id, brm_account_name_jp, target_month, hlayer_name, hlayer_code, dlayer_name, dlayer_code, layer_name_jp, layer_code, index_name, transaction_key, submission_deadline_date, amount) 
                            SELECT
                            ROW_NUMBER() OVER(ORDER BY (SELECT 1)) AS id,
                            `SubAccountModel`.`id` AS `sub_acc_id`, 
                            `SubAccountModel`.`sub_acc_name_jp`, 
                            `ActualResultSummaryModel`.`target_month`,
                            `BusinessAreaModel`.`head_department`,
                            `BusinessAreaModel`.`head_dept_id`,
                            `BusinessAreaModel`.`department`,
                            `BusinessAreaModel`.`dept_id`,
                            `BusinessAreaModel`.`ba_name_jp`,
                            `ActualResultSummaryModel`.`ba_code`,
                            `LogisticModel`.`index_name`, 
                            `ActualResultSummaryModel`.`transaction_key` as transaction_key, 
                            (SUM(amount)) AS  `amount` 
                            FROM `tbl_actual_result` AS `ActualResultSummaryModel` 
                            left JOIN `tbl_account` AS `AccountModel` ON (
                                `AccountModel`.`account_code` = `ActualResultSummaryModel`.`account_code` AND
                                `AccountModel`.`flag` = 1
                            ) left JOIN `tbl_sub_account` AS `SubAccountModel` ON (
                                `SubAccountModel`.`id` = `AccountModel`.`sub_acc_id` AND
                                `SubAccountModel`.`flag` = 1
                            ) left JOIN `tbl_business_area` AS `BusinessAreaModel` ON (
                                `BusinessAreaModel`.`ba_code` = `ActualResultSummaryModel`.`ba_code` AND
                                `BusinessAreaModel`.`flag` = 1
                            ) left JOIN (select distinct index_name,`brm_logistics`.`index_no`,ba_code,flag from `tbl_logistic` group by ba_code)as `LogisticModel` ON (
                                (`ActualResultSummaryModel`.`transaction_key` = `LogisticModel`.`index_no` OR `ActualResultSummaryModel`.`transaction_key` = index_name) AND
                                `ActualResultSummaryModel`.`transaction_key` != '' AND
                                    `ActualResultSummaryModel`.`ba_code` = `LogisticModel`.`ba_code` AND
                                    `LogisticModel`.`flag` = 1
                            )
                            WHERE `ActualResultSummaryModel`.`account_code` != 0
                            GROUP BY 
                            `ActualResultSummaryModel`.`target_month`, 
                            `ActualResultSummaryModel`.`ba_code`, 
                            `ActualResultSummaryModel`.`transaction_key`, 
                            `AccountModel`.`sub_acc_id`  
                            ORDER BY 
                            `ActualResultSummaryModel`.`target_month` ASC, 
                            `BusinessAreaModel`.`head_dept_id` ASC, 
                            `BusinessAreaModel`.`dept_id` ASC, 
                            `ActualResultSummaryModel`.`ba_code` ASC, 
                            `AccountModel`.`sub_acc_id` ASC,
                            `ActualResultSummaryModel`.`transaction_key` ASC;"
                        );
                        $this->out("Successfully update result! ".date("Y/m/d h:i:s"));
                        CakeLog::write('debug', 'Successfully update result! '.date("Y/m/d h:i:s").'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    }

                    $id_arr = $this->BrmCronLog->find('list',array(
                        'field' => array('id'),
                        'conditions' => array(
                            'flag' => 1,
                            'brm_term_id' => $term_id,
                        )
                    ));
                    
                    $this->BrmCronLog->updateAll(
                        array('BrmCronLog.flag' => 0),
                        array('BrmCronLog.id' => $id_arr)
                    );
                }
                     
                $mainbudget->commit();
                $mainresult->commit();
                
                Cache::clear();
            }


        } catch (Exception $e) {
            $this->out("Update failed!".$e->getMessage());
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $mainbudget->rollback();
            $mainresult->rollback();
        }  
    }
}

 ?>
