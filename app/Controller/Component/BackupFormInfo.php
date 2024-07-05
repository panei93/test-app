<?php
class BackupFormInfo extends Enum
{
	const BH = 'Budget Hearing';
	const BH_JPN = '予算ヒアリング';
	const PLAN = 'Plan Form';
	const PLAN_JPN = '計画フォーム';
	const WC = 'Whole Company';
	const WC_JPN = '全社';
	const BRD = 'Budget & Result Difference';
	const BRD_JPN = '予実比較';
	const MR = 'Monthly Report';
	const MR_JPN = '月次業績報告';
	const PL_SUM = 'PL Summary';
	const PL_SUM_JPN = 'PLサマリー';
	const SUM_TBL = 'Summary Table';
	const SUM_TBL_JPN = '総括表';
	const FBD = 'Forecast & Budget Difference';
	const FBD_JPN = '見込増減一覧';
	const TPF = 'Trading Plan Form';
	const TPF_JPN = '取引計画フォーム';
	const MPF = 'Manpower Plan Form';
	const MPF_JPN = '人員計画フォーム';
	const FF = 'Forecast Form';
	const FF_JPN = '見込フォーム';
	const BF = 'Budget Form';
	const BF_JPN = '予算フォーム';
	const FBF = 'Forecast Budget Form';
	const FBF_JPN = '見込・予算フォーム';


	const TYPE_LIST = array(
		'01' => 'Budget Hearing',
		'02' => 'Plan Form',
		'03' => 'Budget & Result Difference',
		'04' => 'Monthly Report');

	const TYPE_LIST_JPN = array(
		'01' => '予算ヒアリング', 
		'02' => '計画フォーム', 
		'03' => '予実比較', 
		'04' => '月次業績報告'
	);

	const SUB_TYPE_LIST = array(
		'Budget Hearing' => array('PL Summary', 'Summary Table'),
		'Plan Form' => array('Forecast & Budget Difference', 'Trading Plan Form', 'Manpower Plan Form', 'Forecast Budget Form')
	);
	const SUB_TYPE_LIST_JPN = array(
		'予算ヒアリング' => array('PLサマリー', '総括表'),
		'計画フォーム' => array('予測と予算の違い', '取引計画フォーム', 'マンパワープランフォーム', '予測 予算フォーム')
	);
}

?>