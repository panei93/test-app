 <?php echo $this->Form->create(false,array('url'=>'','type'=>'post','id'=>'','class'=>'')); ?>

<style>
	table, th, td {
  		vertical-align: middle !important;
	}
	.line {
			margin-top:15px;
		}
	.dest_code{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.dest_code_2{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.dest_code_3{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.total_money{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.dest_name_1{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.dest_name_2{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.dest_name_3{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	tbody {
    height: 100px;       /* Just for the demo          */
    overflow-y: auto;    /* Trigger vertical scroll    */
    overflow-x: hidden;  /* Hide the horizontal scroll */
}
</style>
<script>
	$(document).ready(function(){

		/* float thead */
		if($('#tbl_manager_comment').length > 0) {
			var $table = $('#tbl_manager_comment');
			$table.floatThead({
			    responsiveContainer: function($table){
			        return $table.closest('.table-responsive');
			    }
			});
		}
		if($('#summary_rpt_2').length > 0) {
			var $table = $('#summary_rpt_2');
			$table.floatThead({
			    responsiveContainer: function($table){
			        return $table.closest('.table-responsive');
			    }
			});
		}
		if($('#summary_rpt_3').length > 0) {
			var $table = $('#summary_rpt_3');
			$table.floatThead({
			    responsiveContainer: function($table){
			        return $table.closest('.table-responsive');
			    }
			});
		}
		
		mergeCell();
		mergeCell_2();
		mergeCell_3();
		
		function mergeCell() {
		    var rowSpan = 1;
		    var topMatchTd;
		    var previousValue = "";
		    
		    $(".dest_code").each(function(){
		        if($(this).text() == previousValue)
		        {
		          rowSpan++;
		          $(topMatchTd).attr('rowspan',rowSpan);
		          $(topMatchTd).siblings('.total_money').attr('rowspan',rowSpan);		       
		          $(this).siblings('.total_money').remove();
		        
		          $(topMatchTd).siblings('.dest_name_1').attr('rowspan',rowSpan);
		          $(this).siblings('.dest_name_1').remove();
		         	
		            $(this).remove();
		        }
		        else
		        {
		          topMatchTd = $(this);
		          rowSpan = 1;
		        }           
		        previousValue = $(this).text();
		    });
		   
		}
		function mergeCell_2() {
		    var rowSpan = 1;
		    var topMatchTd;
		    var previousValue = "";
		    
		    $(".dest_code_2").each(function(){
		        if($(this).text() == previousValue)
		        {
		          rowSpan++;
		          $(topMatchTd).attr('rowspan',rowSpan);
		          $(topMatchTd).siblings('.total_money').attr('rowspan',rowSpan);
		         // console.log($(topMatchTd).siblings('.prev_commt'));
		          $(this).siblings('.total_money').remove();
		          $(topMatchTd).siblings('.dest_name_2').attr('rowspan',rowSpan);
		          $(this).siblings('.dest_name_2').remove();
		          $(this).remove();
		        }
		        else
		        {
		          topMatchTd = $(this);
		          rowSpan = 1;
		        }           
		        previousValue = $(this).text();
		    });     
		}
		function mergeCell_3() {
		    var rowSpan = 1;
		    var topMatchTd;
		    var previousValue = "";
		    
		    $(".dest_code_3").each(function(){
		        if($(this).text() == previousValue)
		        {
		          rowSpan++;
		          $(topMatchTd).attr('rowspan',rowSpan);
		          $(topMatchTd).siblings('.total_money').attr('rowspan',rowSpan);
		         // console.log($(topMatchTd).siblings('.prev_commt'));
		          $(this).siblings('.total_money').remove();
		          $(topMatchTd).siblings('.dest_name_3').attr('rowspan',rowSpan);
		          $(this).siblings('.dest_name_3').remove();
		          $(this).remove();
		        }
		        else
		        {
		          topMatchTd = $(this);
		          rowSpan = 1;
		        }           
		        previousValue = $(this).text();
		    });     
		}
	});
	function Download_Report(){
		document.forms[0].action = "<?php echo $this->webroot; ?>SapSummaryReports/Download_Summary_Rpt";
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;
	}
	
</script>
<div class = 'container register_container'>

	<h3 class=""><?php echo __('速報版') ?></h3>
	<hr>
	
	<div id="error" class="error"><?php echo $errorMsg; ?></div>
	<div class ="form-group col-md-12 col-sm-12 col-xs-12">
		<div class = "col-md-6 line">
			<div class = "form-group">
				<table class = "table table-bordered">
					<thead class="check_period_table">
						<tr>
						    <th style="text-align: center;"><?php echo __("管理本部長"); ?></th>
						    <th style="text-align: center;"><?php echo __("業務管理部長"); ?></th> 
						  </tr>
					  </thead>
					  <tr>
					    <td style="text-align: center;"> （　　/　　）</td>
					    <td style="text-align: center;"> （　　/　　）</td> 
					  </tr>
				</table>
			</div>
		</div>

		<div class = "col-md-6 line">
			<div class = "form-group">
				<table class = "table table-bordered">
					<thead class="check_period_table">
					<tr>
					    <th style="text-align: center;"><?php echo __("部長"); ?></th>
					    <th style="text-align: center;">TL</th> 
					    <th style="text-align: center;"> <?php echo __("担当者"); ?></th> 
					  </tr>
					  </thead>
					  <tr>
					    <td style="text-align: center;"> （　　/　　）</td>
					    <td style="text-align: center;"> （　　/　　）</td> 
					    <td style="text-align: center;"> （　　/　　）</td> 
					  </tr>
				</table>
			</div>
		</div>
		<div class ="row line">
			<div class = "col-md-6">
				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("部署"); ?></label>
				      <div class="col-md-8">
						<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='target_month' value = '<?php echo $layer_code;?>' disabled="disabled" >     
					</div>
				</div>
			</div>
		</div>
		<div class ="row line">
			<div class = "col-md-6">
				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("部署名"); ?></label>
				      <div class="col-md-8">
						<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='target_month' value = '<?php echo $layer_name;?>' disabled="disabled" >     
					</div>
				</div>
			</div>
		</div>
			<div class ="row line">
			<div class = "col-md-6">
				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("対象月"); ?></label>
				      <div class="col-md-8">
						<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='dept_incharge' value = '<?php echo $period; ?>' disabled="disabled" >     
					</div>
				</div>
			</div>
		</div>
		<div class ="row line">
			<div class = "col-md-6">
				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("基準年月日"); ?></label>
				      <div class="col-md-8">
						<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='target_month' value = '<?php echo $submission_deadline; ?>' disabled="disabled" >     
					</div>
				</div>
			</div>
		</div>

		<div class ="row line">
			<div class = "col-md-6">
				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("提出期日"); ?></label>
				      <div class="col-md-8">
						<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='target_month' value = '<?php echo $deadline_date; ?>' disabled="disabled" >     
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php if(!empty($search_result_one) || !empty($search_result_two) || !empty($search_result_three)): ?>
	<div class="text-right">
		<input type="submit" value="<?php echo __('Excelダウンロード');?>" class="emp_register but_register" id="fileImport" onClick = "Download_Report();" >
	</div>
	<?php endif; ?>
	<div><?php echo __("①滞留額100万円以上の取引先"); ?></div>
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">	
		<div class="row">
	        <div>
	            <div class="">
					<div class="table-responsive" >
						<table class="table table-bordered" style="margin-top:10px;width:100%;" id="tbl_manager_comment">
							<thead class="check_period_table">
								<tr>						
									<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("相手先コード"); ?></th>
									<th width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("相手先名"); ?></th>
									<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("部署"); ?></th>
									<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("部署名"); ?></th>
									<th width="6%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("決済予定日"); ?></th>
									<th width="5%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("滞留日数"); ?></th>
									<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("合計金額"); ?></th>
									<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("金額"); ?></th>
									<th width="15%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("コメント"); ?></th>
								</tr>								
							</thead>
							<tbody>
								<?php 
								
									
									foreach($search_result_one as $row){
									
									$admin_comment = $row['busi_admin_cmt']['busi_admin_comment'];
									$busi_admin_comment_array = preg_split ("/\,/", $admin_comment);
									$busi_admin_comment = h($busi_admin_comment_array['0']);

									$inc_comment = $row['acc_inc_cmt']['acc_inc_comment'];
									$acc_inc_comment_array = preg_split ("/\,/", $inc_comment);
									$acc_incharge_comment = h($acc_inc_comment_array[0]);

									$submanager_comment = $row['acc_submgr_cmt']['acc_submanager_comment'];
									$acc_submanager_comment_array = preg_split ("/\,/", $submanager_comment);
									$acc_submanager_comment = h($acc_submanager_comment_array[0]);


									$destination_code = h($row['saps']['destination_code']);
									$destination_name = h($row['saps']['destination_name']);
									$business_code = h($row['saps']['layer_code']);
									$business_name = h($row['saps']['name_jp']);
									$schedule_date = h($row['0']['schedule_date']);
									if($schedule_date == '0000-00-00' || $schedule_date == ''){
										$schedule_date = '';
									}else {
										$schedule_date = date('Y-m-d', strtotime($schedule_date));
									}
									$diff = strtotime($submission_deadline) - strtotime($schedule_date);										
						            $numberofdays = round($diff / 86400);
									//$numberofdays = h($row['saps']['numbers_day']);
									$jp_amount = h($row['saps']['jp_amount']);
									$amountofMoney = h($row['amountofMoney']);
									$busi_inc_remark = h($row['busi_inc_cmt']['remark']);
									$busi_inc_settlement_date = h($row['busi_inc_cmt']['settlement_date']);
									if($busi_inc_settlement_date == '0000-00-00' || $busi_inc_settlement_date == ''){
										$busi_inc_settlement_date = '';
									}else {
										$busi_inc_settlement_date = date('Y-m-d', strtotime($busi_inc_settlement_date));
									}
									$busi_inc_reason = h($row['busi_inc_cmt']['reason']);
									$select1 = (!empty($busi_inc_reason) && !empty($busi_inc_settlement_date)) ? '/' : '';
									$select2 = (!empty($busi_inc_settlement_date) && !empty($busi_inc_remark)) ? '/' : '';

									$add_cmt1 = $busi_inc_reason.$select1.$busi_inc_settlement_date.$select2.$busi_inc_remark;

									$add_cmt1 = (!empty($add_cmt1)) ? '-'.$add_cmt1 : $add_cmt1;
									
									$add_cmt2 = (!empty($busi_admin_comment)) ? '<br> -'.$busi_admin_comment : '';

									$add_cmt3 = (!empty($acc_incharge_comment)) ? '<br> -'.$acc_incharge_comment : '';

									$rev_cmt4 = (!empty($acc_submanager_comment)) ? '<br> -'.$acc_submanager_comment : '';					
								?>
								<tr>
									<td style="width: 5%; word-wrap: break-word;" class="dest_code"><?php echo $destination_code; ?></td>
									<td style="width:15%; word-wrap: break-word;" class="dest_name_1"><?php echo $destination_name; ?></td>
									<td style="width: 5%; word-wrap: break-word;"><?php echo $business_code; ?></td>
									<td style="width: 5%; word-wrap: break-word;"><?php echo $business_name; ?></td>
									<td style="width: 6%; word-wrap: break-word;vertical-align: middle;important!"><?php echo $schedule_date; ?></td>
									<td style="width: 5%; word-wrap: break-word;text-align: right;"><?php echo $numberofdays; ?></td>
									<td style="width: 5%; word-wrap: break-word;text-align:right" class="total_money"><?php echo number_format($amountofMoney); ?></td>
									<td style="width: 5%; word-wrap: break-word;text-align:right"><?php echo number_format($jp_amount); ?></td>
									<td style="width: 5%; word-wrap: break-word;" class="prev_commt">
										<?php echo $add_cmt1; ?>
										<?php echo $add_cmt2; ?>
										<?php echo $add_cmt3; ?>
										<?php echo $rev_cmt4; ?>
									</td>
								</tr>
								<?php }?>	
							</tbody>								
						</table>
					</div>
				</div>		
			</div>	
		</div>
	</div><br><br><br><br><br>
	<div><?php echo __("②滞留額100万円未満、滞留日数30日以上の取引先"); ?></div>
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">	
		<div class="row">
	        <div>
	            <div class="">
					<div class="table-responsive" >
						<table class="table table-bordered" style="margin-top:10px;width:100%;" id="summary_rpt_2">
							<thead class="check_period_table">
								<tr>							
									<th width="5%;" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("相手先コード"); ?></th>
									<th width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("相手先名"); ?></th>
									<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("部署"); ?></th>
									<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("部署名"); ?></th>
									<th width="6%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("決済予定日"); ?></th>
									<th width="5%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("滞留日数"); ?></th>
									<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("合計金額"); ?></th>
									<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("金額"); ?></th>
									<th width="15%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("コメント"); ?></th>
								</tr>								
							</thead>
							<tbody>
								<?php 
								foreach($search_result_two as $rowTwo){

									$admin_comment = $rowTwo['busi_admin_cmt']['busi_admin_comment'];
									$busi_admin_comment_array = preg_split ("/\,/", $admin_comment);
									$busi_admin_comment = h($busi_admin_comment_array['0']);

									$inc_comment = $rowTwo['acc_inc_cmt']['acc_inc_comment'];
									$acc_inc_comment_array = preg_split ("/\,/", $inc_comment);
									$acc_incharge_comment = h($acc_inc_comment_array[0]);

									$submanager_comment = $rowTwo['acc_submgr_cmt']['acc_submanager_comment'];
									$acc_submanager_comment_array = preg_split ("/\,/", $submanager_comment);
									$acc_submanager_comment = h($acc_submanager_comment_array[0]);

									$destination_code_2 = h($rowTwo['saps']['destination_code']);
									$destination_name = h($rowTwo['saps']['destination_name']);
									$business_code = h($rowTwo['saps']['layer_code']);
									$business_name = h($rowTwo['saps']['name_jp']);
									$schedule_date = h($rowTwo['0']['schedule_date']);
									if($schedule_date == '0000-00-00' || $schedule_date == ''){
										$schedule_date = '';
									}else {
										$schedule_date = date('Y-m-d', strtotime($schedule_date));
									}
									
									//calculate number of days
									$diff = strtotime($submission_deadline) - strtotime($schedule_date);										
						            $numberofdays = round($diff / 86400);
									$amountofMoney = h($rowTwo['amountofMoney']);
									
									$jp_amount = h($rowTwo['saps']['jp_amount']);

									$busi_inc_remark = h($rowTwo['busi_inc_cmt']['remark']);
									
									$busi_inc_settlement_date = h($rowTwo['busi_inc_cmt']['settlement_date']);
									if($busi_inc_settlement_date == '0000-00-00' || $busi_inc_settlement_date == ''){
										$busi_inc_settlement_date = '';
									}else {
										$busi_inc_settlement_date = date('Y-m-d', strtotime($busi_inc_settlement_date));
									}
									$busi_inc_reason = h($rowTwo['busi_inc_cmt']['reason']);
									
									$select1 = (!empty($busi_inc_reason) && !empty($busi_inc_settlement_date)) ? '/' : '';
									$select2 = (!empty($busi_inc_settlement_date) && !empty($busi_inc_remark)) ? '/' : '';

									$add_cmt1 = $busi_inc_reason.$select1.$busi_inc_settlement_date.$select2.$busi_inc_remark;
									$add_cmt1 = (!empty($add_cmt1)) ? '-'.$add_cmt1 : $add_cmt1;
									
									$add_cmt2 = (!empty($busi_admin_comment)) ? '<br> -'.$busi_admin_comment : '';

									$add_cmt3 = (!empty($acc_incharge_comment)) ? '<br> -'.$acc_incharge_comment : '';

									$rev_cmt4 = (!empty($acc_submanager_comment)) ? '<br> -'.$acc_submanager_comment : '';
								?>
								<tr>
									<td style="width: 5%; word-wrap: break-word;" class="dest_code_2"><?php echo $destination_code_2; ?></td>
									<td style="width:15%; word-wrap: break-word;" class="dest_name_2"><?php echo $destination_name; ?></td>
									<td style="width: 5%; word-wrap: break-word;"><?php echo $business_code; ?></td>
									<td style="width: 5%; word-wrap: break-word;"><?php echo $business_name; ?></td>
									<td style="width: 6%; word-wrap: break-word;"><?php echo $schedule_date; ?></td>
									<td style="width: 5%; word-wrap: break-word;text-align:right;"><?php echo $numberofdays; ?></td>
									<td style="width: 4%; word-wrap: break-word;text-align:right" class="total_money"><?php echo number_format($amountofMoney); ?></td>
									<td style="width: 5%; word-wrap: break-word;text-align:right"><?php echo number_format($jp_amount); ?></td>
									<td style="width: 5%; word-wrap: break-word;" class="prev_commt">
										<?php echo $add_cmt1; ?>
										<?php echo $add_cmt2; ?>
										<?php echo $add_cmt3; ?>
										<?php echo $rev_cmt4; ?>
									</td>
								</tr>
								<?php }?>
							</tbody>								
						</table>
					</div>
				</div>		
			</div>	
		</div>
	</div><br><br><br><br><br>
	<div><?php echo __("③滞留額100万円未満、滞留日数30日未満の取引先"); ?></div>
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">	
		<div class="row">
	        <div>
	            <div class="">
					<div class="table-responsive" >
						<table class="table table-bordered" style="margin-top:10px;width:100%;" id="summary_rpt_3">
							<thead class="check_period_table">	
								<tr>							
									<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("相手先コード"); ?></th>
									<th width="15%" class="table-middle" style="vertical-align: middle;text-align:center;" ><?php echo __("相手先名"); ?></th>
									<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("部署"); ?></th>
									<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("部署名"); ?></th>
									<th width="6%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("決済予定日"); ?></th>
									<th width="5%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("滞留日数"); ?></th>
									<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("合計金額"); ?></th>
									<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("金額"); ?></th>
								</tr>								
							</thead>
							<tbody>
								<?php 
									
									foreach($search_result_three as $rowThree) {
									
									$destination_code_3 = $rowThree['saps']['destination_code'];
									$destination_name =$rowThree['saps']['destination_name'];
									$business_code = $rowThree['saps']['layer_code'];
									$business_name = $rowThree['LayerGroup']['name_jp'];
									$schedule_date = $rowThree['0']['schedule_date'];
									if($schedule_date == '0000-00-00' || $schedule_date == ''){
										$schedule_date = '';
									}else {
										$schedule_date = date('Y-m-d', strtotime($schedule_date));
									}
									//calculate
									$diff = strtotime($submission_deadline) - strtotime($schedule_date);										
						            $numberofdays = round($diff / 86400);
									$jp_amount = h($rowThree['0']['jp_amount']);
									$amountofMoney = $rowThree['amountofMoney'];

								?>
								
								<tr>
									<td style="width: 5%; word-wrap: break-word;" class="dest_code_3"><?php echo $destination_code_3; ?></td>
									<td style="width: 15%; word-wrap: break-word;" class="dest_name_3"><?php echo $destination_name; ?></td>
									<td style="width: 5%; word-wrap: break-word;"><?php echo $business_code; ?></td>
									<td style="width: 5%; word-wrap: break-word;"><?php echo $business_name; ?></td>
									<td style="width: 6%; word-wrap: break-word;"><?php echo $schedule_date; ?></td>
									<td style="width: 5%; word-wrap: break-word;text-align:right;" ><?php echo $numberofdays; ?></td>
									<td style="width: 4%; word-wrap: break-word;text-align:right" class="total_money"><?php echo number_format($amountofMoney); ?></td>
									<td style="width: 5%; word-wrap: break-word;text-align:right"><?php echo number_format($jp_amount); ?></td>
								</tr>
								<?php } ?>
							</tbody>								
						</table>
					</div>
				</div>		
			</div>	
		</div>
	</div><!-- end upper form -->
	
	
</div>
<?php
    echo $this->Form->end();
?>
