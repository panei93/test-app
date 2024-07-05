<?php

	$chooseLan=($this->Session->check('Config.language'))? $this->Session->read('Config.language') : 'jpn';
	 if($chooseLan=='jpn')
		$head = array("対象月","SID","事業領域","ビジネスエリア名","担当者","案件名","計上日","Index","勘定科目","相手先","相手先名","金額","備考","指摘事項","指摘事項ニ","指摘事項三","営業添付資料","RID","経理添付/入手資料","点検項目","備考","報告要否","提出期限","提出期限ニ","提出期限三","完了フラグ","インデックス宛先アカウント項目","アカウント添付書類","初期チェックリストの改善状況","2番目のチェックリストの改善状況");
	  else
		$head = array("Target Month","SID","Business Code","Business Area Name","In-Charge Person","Project Title","Recording Date","Index","Account Item","Destination","Destination Name","Amount","Remark","Business Attachment Documentation","RID","Accounting attachment/Obtained material","Inspection Items","Remark","Pointed Out Facts One","Pointed Out Facts Two","Pointed Out Facts Three","Report Optional","Submission Deadline One","Submission Deadline Two","Submission Deadline Three","Completion Flag","Index Destination Account Item","Account Attachment Documentation","Initial Checklist Improvement Situation","Second Checklist Improvement Situation");

	$this->CSV->addRow($head);
	
	if(!empty($data)){			

		for($i=0;$i<count($data);$i++){
		
			if(!empty($period) || $period!="")
				$this->CSV->addField($period);
			else
				$this->CSV->addField("");

			$sid = $data[$i]['sid'];
			if(!empty($sid) || $sid!="")
				$this->CSV->addField($sid);
			else
				$this->CSV->addField("");

			$layer_code=$data[$i]['layer_code'];
			if(!empty($layer_code) || $layer_code!="")
				$this->CSV->addField($layer_code);
			else
				$this->CSV->addField("");

			
			$layer_name= $data[$i]['name_jp'];
			if(!empty($layer_name) || $layer_name!="")
				$this->CSV->addField($layer_name);
			else
				$this->CSV->addField("");

			$incharge_person = $data[$i]['incharge_name'];
			if(!empty($incharge_person) || $incharge_person!="")
				$this->CSV->addField($incharge_person);
			else
				$this->CSV->addField("");

			$project_title = $data[$i]['project_title'];
			if(!empty($project_title) || $project_title!="")
				$this->CSV->addField($project_title);
			else
				$this->CSV->addField("");

			$posting_date = $data[$i]['posting_date'];
			if(!empty($posting_date) || $posting_date!="")
				$this->CSV->addField($posting_date);
			else
				$this->CSV->addField("");

			$index = $data[$i]['index_no'];
			if(!empty($index) || $index!="")
				$this->CSV->addField($index);
			else
				$this->CSV->addField("");

			$sample_flag = $data[$i]['flag'];


			$account_item = $data[$i]['account_item'];
			if(!empty($account_item) || $account_item!="")
				$this->CSV->addField($account_item);
			else
				$this->CSV->addField("");

			$destination = $data[$i]['destination_code'];
			if(!empty($destination) || $destination!="")
				$this->CSV->addField($destination);
			else
				$this->CSV->addField("");

			$destination_name = $data[$i]['destination_name'];
			if(!empty($destination_name) || $destination_name!="")
				$this->CSV->addField($destination_name);
			else
				$this->CSV->addField("");

			$amount_money = $data[$i]['money_amt'];
			
			if(!empty($amount_money) || $amount_money!="")
				$this->CSV->addField($amount_money);
			else
				$this->CSV->addField("");

			
			$remarks = $data[$i]['remark'];
			if(!empty($remarks) || $remarks!="")
				$this->CSV->addField($remarks);
			else
				$this->CSV->addField("");

			$account_file = $data[$i]['acc_attach_file'];
			$business_file = $data[$i]['busi_attach_file'];
			if(!empty($business_file) ){

				$busi_attach_file=$data[$i]["busi_attach_file"][0]["file_name"];
				$this->CSV->addField($busi_attach_file);
				
			}else
				$this->CSV->addField("");
			

			$this->CSV->addField($i+1);

			if(!empty($account_file)){
				
				$acc_attach_file=$data[$i]["acc_attach_file"][0]["file_name"];
				$this->CSV->addField($acc_attach_file);
			}else{
				
				$this->CSV->addField("");
			}	

			$InspectionItems="";
			if(!empty ($fill_data[$i]['test'])){

				if($fill_data[$i]['test']['question1'] == 1){
					$InspectionItems.="1:";
				}
				else{
					$InspectionItems.="0:";
				}
				if($fill_data[$i]['test']['question2'] == 1){
					$InspectionItems.="1:";
				}
				else{
					$InspectionItems.="0:";
				}
				if($fill_data[$i]['test']['question3'] == 1){
					$InspectionItems.="1:";
				}
				else{
					$InspectionItems.="0:";
				}
				if($fill_data[$i]['test']['question4'] == 1){
					$InspectionItems.="1:";
				}
				else{
					$InspectionItems.="0:";
				}
				if($fill_data[$i]['test']['question5'] == 1){
					$InspectionItems.="1:";
				}
				else{
					$InspectionItems.="0:";
				}
				if($fill_data[$i]['test']['question6'] == 1){
					$InspectionItems.="1:";
				}
				else{
					$InspectionItems.="0:";
				}
				if($fill_data[$i]['test']['question7'] == 1){
					$InspectionItems.="1";
				}
				else{
					$InspectionItems.="0";
				}
				$this->CSV->addField($InspectionItems);
			}
			else{
				$this->CSV->addField("");
			}				

			if(!empty ($fill_data[$i]['test'])){

				$remark = $fill_data[$i]['test']['test_result'];
				$remark = str_replace( array( "\\n", "\\r" ), array(" "," "),$remark);
				$this->CSV->addField($remark);


				$point_out1 = $fill_data[$i]['test']['point_out1'];
				$point_out1 = str_replace( array( "\\n", "\\r" ), array(" "," "),$point_out1);
				$this->CSV->addField($point_out1);

				$point_out2 = $fill_data[$i]['test']['point_out2'];
				$point_out2 = str_replace( array( "\\n", "\\r" ), array(" "," "),$point_out2);
				$this->CSV->addField($point_out2);

				$point_out3 = $fill_data[$i]['test']['point_out3'];
				$point_out3 = str_replace( array( "\\n", "\\r" ), array(" "," "),$point_out3);
				$this->CSV->addField($point_out3);

				$ReportOptional="0";
				if($fill_data[$i]['test']['report_necessary1'] == 1){ 
					$ReportOptional="1";
				}
				$this->CSV->addField($ReportOptional);

			}else{

				$this->CSV->addField("");
				$this->CSV->addField("");
				$this->CSV->addField("");
				$this->CSV->addField("");
				$this->CSV->addField("");
			}
			
			if(!empty($fill_data[$i]['0'])){

				$deadline_date = $fill_data[$i]['0']['deadline_date'];
				$this->CSV->addField($deadline_date);

				$deadline_date = $fill_data[$i]['0']['deadline_date2'];
				$this->CSV->addField($deadline_date);

				$deadline_date = $fill_data[$i]['0']['deadline_date3'];
				$this->CSV->addField($deadline_date);
			}else{

				$this->CSV->addField("");
				$this->CSV->addField("");
				$this->CSV->addField("");

			}				

			if(!empty ($fill_data[$i]['test'])){

				$test_result_finish="0";
				if($fill_data[$i]['test']['testresult_finish'] == 1){ 
					$test_result_finish="1";
				}
			}else{

				$test_result_finish="0";
			}
			
			$this->CSV->addField($test_result_finish);

			$IndexDestinationAccountItem="";

			if(!empty($checkListshow[$i]['sd'])){

				$index_no         = $checkListshow[$i]['sd']['index_no'];
				$IndexDestinationAccountItem.=$index_no.":";

				$destination_name = $checkListshow[$i]['sd']['destination_name'];                
              	$IndexDestinationAccountItem.=$destination_name.":";

                $account_item     = $checkListshow[$i]['sd']['account_item'];
                $IndexDestinationAccountItem.=$account_item;
                $this->CSV->addField($IndexDestinationAccountItem);
			}else{

				$this->CSV->addField("");
			}				

            $cnt_acc_attach_file = count($account_file);
            if (isset($account_file) && $cnt_acc_attach_file>0) {
                for($k=0; $k<$cnt_acc_attach_file; $k++) {
                    $file_name = $account_file[$k]['file_name'];

             		}
             	$this->CSV->addField($file_name);
         	}else{

         		$this->CSV->addField("");
         	}

         	$check_commenttext1="";

			if(!empty($checkListshow[$i]['ch'])){

				$check_commenttext1 = $checkListshow[$i]['ch']['improvement_situation1'];
			
				$this->CSV->addField($check_commenttext1);
			}else{

				$this->CSV->addField("");
			}

			$check_commenttext2="";

			if(!empty($checkListshow[$i]['ch'])){

				$check_commenttext2         = $checkListshow[$i]['ch']['improvement_situation2'];
		
				$this->CSV->addField($check_commenttext2);
			}else{

				$this->CSV->addField("");
			}

			$this->CSV->endRow();	
		}
	}			
	
	$filename='ExportReportDataList';
	echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $this->CSV->render($filename);
?>