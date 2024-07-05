<?php

	$chooseLan=($this->Session->check('Config.language'))? $this->Session->read('Config.language') : 'jpn';
	 if($chooseLan=='jpn')
		$head = array("対象月","SID","部署","部署名","カテゴリー","担当者","案件名","計上日","Index","勘定科目","相手先","相手先名","金額","備考","指摘事項","指摘事項ニ","指摘事項三","営業添付資料","RID","経理添付/入手資料","点検項目","備考","報告要否","提出期限","提出期限ニ","提出期限三","完了フラグ","インデックス宛先アカウント項目","アカウント添付書類","初期チェックリストの改善状況","2番目のチェックリストの改善状況");
	  else
		$head = array("Target Month","SID","Layer","Layer Name","Category","In-Charge Person","Project Title","Recording Date","Index","Account Item","Destination","Destination Name","Amount","Remark","Business Attachment Documentation","RID","Accounting attachment/Obtained material","Inspection Items","Remark","Pointed Out Facts One","Pointed Out Facts Two","Pointed Out Facts Three","Report Optional","Submission Deadline One","Submission Deadline Two","Submission Deadline Three","Completion Flag","Index Destination Account Item","Account Attachment Documentation","Initial Checklist Improvement Situation","Second Checklist Improvement Situation");

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

			$category= $data[$i]['category'];
			if(!empty($category) || $category!="")
				$this->CSV->addField($category);
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
			$acc_attach_file = '';
			$busi_attach_file = '';
			if(!empty($business_file) ){
				foreach($business_file as $bKey=>$bValue){
					if(strpos($bValue['url'],"SampleDataEntry")){
						$busi_attach_file .= $bValue["file_name"].',';
					}
					$busi_attach_file = substr($busi_attach_file, 0, -1);
				}		
			}
			$this->CSV->addField($busi_attach_file);
			$this->CSV->addField($i+1);

			if(!empty($account_file)){
				foreach($account_file as $aKey=>$aValue){
					if(strpos($aValue['url'],"SampleTestResults")){
						$acc_attach_file .= $aValue["file_name"].',';
					}
				}
				$acc_attach_file = substr($acc_attach_file, 0, -1);
				
			}
			
			$this->CSV->addField($acc_attach_file);

			$sampleId = $data[$i]['id'];
			$InspectionItems="";
			foreach($fill_data as $fkey=>$fvalue){
				if($fvalue['samples']['id'] == $sampleId){
					foreach($fvalue['question'] as $key=>$value){
						$no = 'question'.$value['questions']['id'];
						if($fvalue['test'][$no] == 1) $InspectionItems.="1-";
						else $InspectionItems.="0-";
					}
				
				}
			}
			
			$InspectionItems = substr($InspectionItems, 0, -1);	
			if($InspectionItems != "") $this->CSV->addField($InspectionItems);
			else $this->CSV->addField("");			
			$remark = '';
			$point_out1 = '';
			$point_out2 = '';
			$point_out3 = '';
			$ReportOptional="0";
			$deadline_date1 = '';
			$deadline_date2 = '';
			$deadline_date3 = '';
			$test_result_finish="0";
			
			foreach($fill_data as $fkey=>$fvalue){
				if($fvalue['samples']['id'] == $sampleId){
					$remark = $fvalue['test']['test_result'];
					$remark = str_replace( array( "\\n", "\\r" ), array(" "," "),$remark);
					

					$point_out1 = $fvalue['test']['point_out1'];
					$point_out1 = str_replace( array( "\\n", "\\r" ), array(" "," "),$point_out1);

					$point_out2 = $fvalue['test']['point_out2'];
					$point_out2 = str_replace( array( "\\n", "\\r" ), array(" "," "),$point_out2);

					$point_out3 = $fvalue['test']['point_out3'];
					$point_out3 = str_replace( array( "\\n", "\\r" ), array(" "," "),$point_out3);

					
					if($fvalue['test']['report_necessary1'] == 1){ 
						$ReportOptional="1";
					}

					$deadline_date1 = ($fvalue['0']['deadline_date'] == '0000-00-00') ? '' : $fvalue['0']['deadline_date'] ;
					
					$deadline_date2 = ($fvalue['0']['deadline_date2'] == '0000-00-00') ? '' : $fvalue['0']['deadline_date2'];
					
					$deadline_date3 = ($fvalue['0']['deadline_date3'] == '0000-00-00') ? '' : $fvalue['0']['deadline_date3'];
					
					if($fvalue['test']['testresult_finish'] == 1){ 
						$test_result_finish="1";
					}
				}
			}
			$this->CSV->addField($remark);
			$this->CSV->addField($point_out1);
			$this->CSV->addField($point_out2);
			$this->CSV->addField($point_out3);
			$this->CSV->addField($ReportOptional);
			$this->CSV->addField($deadline_date1);
			$this->CSV->addField($deadline_date2);
			$this->CSV->addField($deadline_date3);
			$this->CSV->addField($test_result_finish);


			$IndexDestinationAccountItem="";
			$check_commenttext1="";
			$check_commenttext2="";
			foreach($checkListshow as $cKey=>$cVlaue){
				if($cVlaue['tr']['sample_id'] == $sampleId){
					$index_no         = $cVlaue['sd']['index_no'];
					$IndexDestinationAccountItem.=$index_no.",";

					$destination_name = $cVlaue['sd']['destination_name'];                
					$IndexDestinationAccountItem.=$destination_name.",";

					$account_item     = $cVlaue['sd']['account_item'];
					$IndexDestinationAccountItem.=$account_item;
					$check_commenttext1 = $cVlaue['ch']['improvement_situation1'];
					$check_commenttext2 =$cVlaue['ch']['improvement_situation2'];
				}
			}
			$this->CSV->addField($IndexDestinationAccountItem);			

			$file_name = '';
			$cnt_acc_attach_file = count($account_file);
			if(isset($account_file) && $cnt_acc_attach_file>0){
				foreach($account_file as $aKey=>$aValue){
					if(strpos($aValue['url'],"SampleRegistrations")){
						$file_name .= $aValue["file_name"].',';
					}
				}
				$file_name = substr($file_name, 0, -1);
				
			}
			$this->CSV->addField($file_name);
			$this->CSV->addField($check_commenttext1);
			$this->CSV->addField($check_commenttext2);
			$this->CSV->endRow();	
		}
	}			
	
	$filename='ExportReportDataList';
	echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $this->CSV->render($filename);
?>