<?php
    class PositionType extends Enum
    {
        const Types = [
            1 => 'ＳＳＣ社員',
            2 => 'ＳC出向者',
            3 => 'その他'
        ];
        //SST 26.8.2022,constant value for position change in LaborCostForm        
        const PositionConstant =[
            // '事務職' =>'22', 
            // '派遣社員' =>'23',
            // 'ｼﾆｱﾊﾟｰﾄﾅｰ（事務職相当）' =>'24',
            // '嘱託（事務職）' =>'25',
            // 'SC事務職' =>'34',
            //'SSCにおける役員' =>'22', 
            //'3等級' =>'23',
            //'5等級' =>'24',
            //'2等級' =>'25',
            //'役員' =>'34',
        ];
    }
?>