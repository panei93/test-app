<?php
class Setting extends Enum
{
    const TIMEZONE = 'Asia/Tokyo';
    const TAX = 0.31;
    //const BUDGET_DISABLE_ACCS = array('売上高','売上原価','俸給諸給与','業務委託費（派遣報酬料）','社内税金');
    const BUDGET_DISABLE_ACCS = array('売上高','売上原価','俸給諸給与','業務委託費（派遣報酬料）');
    const TRADING_DISABLE_HQS = array('コーポレート本部','内部監査室');

    const MP_TABLE_ORDER = array('一般','出向者','残業','派遣社員');
    //const BA_BUDGET_TAX = array('8028', '802C', '8001', '9000', '30001');
    const BA_BUDGET_TAX = array('8028', '802C', '8001', '9000');

    const FINANCE_DEPT = '8000';
    
    const MP_TYPE_RECORD = '1';
    const MP_TYPE_ADJUST	= '2';

    const TAX_REFUND_BA = '9000'; //ba code for 全社勘定（税還付）

    const INNER_PAY_ACCOUNT_CODE = 6108000000; //社内受払手数料

    const KPI_CODE = '0000000000'; //社内受払手数料

    const RESULT_EMP_COUNT = array(
        '2019' => array(
            '1' => 0,
            '2' => 0,
            '3' => 172.1,
            '4' => 242.1,
            '5' => 68.6,
            '6' => 27.4,
        ),
        '2020'=> array(
            '1' => 5.0,
            '2' => 71.8,
            '3' => 178.9,
            '6' => 30.5,
            '9' => 275.9,
        ),
    );
    const RESULT_ADJUSTMENT = array(
        '2019' => array(
            '3' => array(
                '売上高' => 76000000,
                '売上総利益' => 76000000,
                '営業利益' => 76000000,
                '税引前当期利益' => 76000000,
                '当期純利益' => 76000000,
            ),
            '9' => array(
                '営業外損益' => -144300000,
                '税引前当期利益' => -144300000,
                '法人税等' => 2700000,
                '当期純利益' => -141700000,
            )
        ),
    );

    #Phase 4
    const P4_APPROVE_STATUS = 3;
    const P4_SAVE_STATUS = 2;
    const P4_REJECT_STATUS = 1;

    const RELATED_BA = array('800X' => '801D');
    const HIDDEN_BA_P4 = array('8001','802F','9000','801C');

    const TAX_ACCNAME = '社内税金';

    const FIRST_HALF = array('month_1_amt','month_2_amt','month_3_amt','month_4_amt','month_5_amt','month_6_amt');

    const BUTTONS = array('Read','Save', 'Request','Reject','Approve','Review','Approve_Cancel', 'Request_Cancel');
    // const BUTTONS = array('Read','Save', 'Request','Reject','Approve','Review','Approve_Cancel', 'Send Mail BRD', 'Send Mail MR', 'Request_Cancel');
    //  const BUTTONS_JP = array('画面表示','保存', '依頼','拒否','承認','レビュー','承認キャンセル', '予実比較メール通知', '月次業績報告メール通知', 'リクエストキャンセル');
     const BUTTONS_JP = array('画面表示','保存', '依頼','拒否','承認','レビュー','承認キャンセル', 'リクエストキャンセル');

    #layer order limit
    const LAYER_ORDER_LIMIT = 4;

    #dept menu flag flows (Nu Nu Lwin)
    const PREVIEW_FLAG = array(
                    'Save' => array('1','2')
                );
    const ADDCMT_FLAG = array(
                    'save' => array('1'),
                    'save' => array('2'),
                    'request' => array('2'),
                    'request' => array('3'),
                    'approve' => array('4'),
                    'approve_cancel'=> array('5'),
                    'reject' => array('4'),
                    'review' => array('5')
                );
    const ACCREV_FLAG = array(
                    'save' => array('6'),
                    'approve' => array('7'),
                    'approve_cancel'=> array('8'),
                    'reject' => array('7'),
                );

    const LAYER_SETTING = array(
        #phasename => type_order
        'AssetSelections' => '3',
        'SapSelections' => '3',
        'StockSelections' => '3',
        'SampleSelections' => '3',
        'BuSelections' => '2',
        '3' => '3',
        'topLayer' => '1',
        'middleLayer' => '2',
        'bottomLayer' => '3',
        'User' => 4
    );
    const BU_LAYER_SETTING = array(
        'topLayer' => '3',
    );
    const PHASE_SELECTION = array(
        #phasename => menu_id
        'SapSelections' => '1',
        'Samples' => '2',
        'AssetSelections' => '3',
        'TermSelection' => '4',
        'BSelectionFinancial' => '5'
    );

    const ACCOUNT_CATEGORY = array(
       
        'NORMAL' => array(
            'id' => 1,
            'description' => "NORMAL",
        ),
        'TOTAL' => array(
            'id' => 2,
            'description' => "TOTAL",
        ),
    );

    const OPERATOR = array(
        'PLUS' => array(
            'id' => 1,
            'description' => "+",
        ),
        'MINUS' => array(
            'id' => 2,
            'description' => "-",
        ),
        'MULTIPLY' => array(
            'id' => 3,
            'description' => "*",
        ),
        'DIVIDE' => array(
            'id' => 4,
            'description' => "/",
        ),
        'EQUAL' => array(
            'id' => 5,
            'description' => "=",
        ),
    );

    const BTN_STATUS = array(
        // 'button' => ['step','status'],
        'save' => [1,1],
        'save&send' => [1,1],
        'request' => array(2,2),
        'request_cancel' => [2,1],
        'request_cancel' => [2,1],
        'upload' => [3,2],
        'request1' => [3,3],
        'reject' => [4,2],
        'approve' => [4,5],
        'approve_cancel' => [5,2],
        'reject' => [3,1],
        'approve_cancel' => [4,1],

    );

    const BU_UNIT = 1000;
    
    const LIMIT_YEAR = '2020';
    const MANAGER_LIST = array(1,2,5,8);
    #for trade account
    const SALE_ACCOUNT = array(
        array('売上高', '売上原価'),
        '売上総利益',
        '社内受払手数料'
    );

    const SHEET_LIST = array(
		'取引計画フォーム' => 'TP',
		'人員計画フォーム' => 'MP',
		'見込フォーム' => 'BP',
		'予算フォーム' => 'BP',
	);

    const TOTAL_COLUMN = array('first_half', 'second_half', 'whole_total');

    // const PAGE_NAME = array('BusinessAnalysisSheet','ForecastForms','LaborCostDetails','LaborCosts','BrmBudgetPlan','BrmTradingPlan');
    const PAGE_NAME = array('BusinessAnalysisSheet','ForecastForms','BrmBudgetPlan','BrmTradingPlan');

    const ACC_TYPE = array(
        '1' => '【取引採算】',
        '2' => '売上総利益成長率／対前年比',
        '3' => '【グローバルケミカルへの貢献度】',
        '4' => '【資金効率】',
        '5' => 'No Name 1',
        '6' => 'No Name 2',
        '7' => 'No Name 3',
        '8' => 'No Name 4',
        '9' => '【予算人員】　　(実人員数）',
        '10' => 'No Name 5'
    );

    const TOTAL_SALES_PER_PERSON_ACC = array('売上総利益', '予算人員合計');

    const PERSONAL_EXPENSES = '人件費　(ﾋﾞｼﾞﾈｽ別人員表）';

    const MENU_NAME_JP = array('滞留債権' => array('period'=>'期間', 'deadline'=>'提出期日'), 'サンプルチェック' => array('period'=>'期間', 'deadline'=>'提出期日'));
    const MENU_NAME_EN = array( 'Retention Claim Debt'  => array('period'=>'Period', 'deadline'=>'Deadline'),  'Sample Check'  => array('period'=>'Period', 'deadline'=>'Deadline'));

    const MODEL_FOR_DEADLINE = array('Sap', 'Sample', 'Stock');
    const FIELD_FOR_DEADLINE = array('deadline_date', 'submission_deadline_date', 'deadline_date');

    const LAYER_TYPE_LIMIT = 4;

    #set mail send limit in one time
    const MAIL_SEND_LIMIT = 150;

    #set sleep time in seconds if more mail send limit exceeded
    const SLEEP_TIME = 30;

    #get account id by account code for Labor Cost Detail
    const ACCOUNT_NAME_LCD = '　人件費　(ﾋﾞｼﾞﾈｽ別人員表）';
    const ACCOUNT_CODE_LCD = 620;

    #remove line from budget form' s layer list
    const REMOVE_LAYER = 0;#line order

    #interest cost account
    const INTEREST_COST = array(
        '9929' => '短期　社内賦課金利         (%)',
        '9930' => '長期　社内賦課金利         (%)',
        '9931' => 'ファクタリングコスト   (%)'
    );

    const BU_BUDGET_MAX_LAYER = array('2');

    const MENU_ID_LIST = array(
        'LaborCosts' => 6,
        'LaborCostDetails' => 8,
        'BudgetResult' => 10,
        'BusinessAnalysis' => 12
    );

    const BU_SHOW_HIDE = array('6');

    const LAYER_PERMIT_ROLES = array(
        5 => 'グループ長', 
        6 => 'ライン長'
    );
}
