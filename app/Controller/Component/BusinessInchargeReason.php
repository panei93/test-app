<?php
class BusinessInchargeReason extends Enum
{
  const BIR_1 = '先方処理遅延';
  const BIR_2 = '基準日ズレ';
  const BIR_3 = '消込忘れ';
  const BIR_4 = '入金済';
  const BIR_5 = '支払済';
  const BIR_6 = 'DueDate入力ミス';
  const BIR_7 = '別紙にて回答';
  const BIR_8 = '請求書発行漏れ';
  const BIR_9 = '先方請求書紛失';
  const BIR_10 = '支払い処理漏れ';
  const BIR_11 = '請求書待ち(未入手)';
  const BIR_12 = 'その他(備考に記載)';
  
}

?>