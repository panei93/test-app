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
	.dest_code_1{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.dest_name_1{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.dest_name{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.log_index_no{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.jp_amount{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.log_index_no_1{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
	.jp_amount_1{
	    vertical-align: middle !important;
	    border-top: 1px solid #ddd;
	}
</style>
<script>
	$(document).ready(function(){
		if($('#tbl_manager_comment').length > 0) {
			var $table = $('#tbl_manager_comment');
			$table.floatThead({
			    responsiveContainer: function($table){
			        return $table.closest('.table-responsive');
			    }
			});
		}
		if($('#longterm_debt2').length > 0) {
			var $table = $('#longterm_debt2');
			$table.floatThead({
			    responsiveContainer: function($table){
			        return $table.closest('.table-responsive');
			    }
			});
		}
		mergeCell();
		mergeCell_2();
	});
	function Download_Report(){
		document.forms[0].action = "<?php echo $this->webroot; ?>StockSummaryDetails/Download_Summary_Rpt";
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;
	}
	
</script>
<div class = 'container register_container'>
	<body>
	    <div id="load"></div>
	    <div id="contents">          
	    </div>
	</body>
	<h3 class=""><?php echo __('長期滞留債権報告書') ?></h3>
	<hr>
	 <div id="error" class="error"><?php echo $errorMsg; ?></div>

	<div class ="form-group col-md-12 col-sm-12 col-xs-12">
		<div class = "col-md-6 line">
			<div class = "form-group">
				<table class = "table table-bordered">
					<thead class="check_period_table">
						<tr>
						    <th width='35%;' style="vertical-align: middle;text-align: center;"><?php echo __("社長"); ?></th>
						    <th style="vertical-align: middle;text-align: center;"><?php echo __("管理本部長"); ?></th>
						    <th style="vertical-align: middle;text-align: center;"><?php echo __("業務管理部長"); ?></th>  
						  </tr>
					  </thead>
					  <tr>
					    <td width="25%;" style="text-align: center;"> （　　/　　）</td>
					    <td style="text-align: center;"> （　　/　　）</td> 
					    <td style="text-align: center;"> （　　/　　）</td> 
					  </tr>
				</table>
			</div>
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
					<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='deadLine_date' value = '<?php echo $deadLine_date; ?>' disabled="disabled" >     
				</div>
			</div>
		</div>
	</div>
		<?php if(!empty($previous_month_30_result) || !empty($previous_month_60_result)): ?>
		<div class="text-right">
			<input type="submit" value="<?php echo __('Excelダウンロード');?>" class="emp_register but_register" id="fileImport" onClick = "Download_Report();" >
		</div>
		<?php endif; ?>
		<div class="col-md-12 col-sm-12">
	    	<h5 class="h5-heading" style="padding-bottom:0px;"><?php echo __('【回収遅延・長期滞留債権　報告書】') ?></h5>
			<h5 class="h5-heading"><?php echo $sub_title; ?></h5>
		</div>
		<div><?php echo $div_title; ?></div>
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">	
			<div class="row">
	            <div>
	                <div class="">
						<div class="table-responsive" >
							<table class="table table-bordered" style="margin-top:10px;width:100%;" id="tbl_manager_comment">
								<thead class="check_period_table">								
										<th width="5%;" class="table-middle" style="vertical-align: middle;text-align:center;" ><?php echo __("部署名"); ?></th>
										<!-- <th width="15%" class="table-middle" style="vertical-align: middle;text-align:center;" ><?php echo __("勘定科目"); ?></th> -->
										<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("取引先名"); ?></th>
										<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("品目コード"); ?></th>
										<th width="6%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("入庫Index No."); ?></th>
										<!-- <th width="5%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("期日"); ?></th> -->
										<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("金額"); ?></th>
										<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("状況/滞留理由"); ?></th>
										<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("入金日/入金予定日等"); ?></th>
									</tr>								
								</thead>
								<tbody>
									<?php foreach($previous_month_30_result as $result4){
										
										$layer_name = h($result4['tbl_2']['name_jp']);
										$destination_name = h($result4['tbl_2']['destination_name']);
										$item_code = h($result4['tbl_2']['item_code']);
										$receipt_index_no = h($result4['tbl_2']['receipt_index_no']);
										$money_amount = h($result4['tmp']['amount']);
										$reason = h($result4['tbl_2']['reason']);
										$settlement_date = h($result4['tbl_2']['settlement_date']);		

										if($settlement_date == '0000-00-00' || $settlement_date == ''){
											$settlement_date = '';
										}else{
											$settlement_date = h(date("Y-m-d", strtotime($result4['tbl_2']['settlement_date'])));
										}
										
									?>
									<tr>
										<td style="width: 5%; word-wrap: break-word;"><?php echo $layer_name; ?></td>
										<td style="width: 5%; word-wrap: break-word;" class = "dest_name"><?php echo $destination_name; ?></td>
										<td style="width: 5%; word-wrap: break-word;" class = "dest_code"><?php echo $item_code; ?></td>
										<td style="width: 6%; word-wrap: break-word;" class = "log_index_no"><?php echo $receipt_index_no; ?></td>
										<td style="width: 10%; word-wrap: break-word;text-align:right" class = "jp_amount"><?php echo number_format($money_amount); ?></td>
										<td style="width: 10%; word-wrap: break-word;"><?php echo $reason; ?></td>
										<td style="width: 10%; word-wrap: break-word;"><?php echo $settlement_date; ?></td>
									</tr>
									<?php }?>
								</tbody>								
							</table>
						</div>
					</div>		
				</div>	
			</div>
		</div><br><br><br><br><br>
		<div><?php echo $div_title1; ?></div> 	
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">	
			<div class="row">
	            <div>
	                <div class="">
						<div class="table-responsive" >
							<table class="table table-bordered" style="margin-top:10px;width:100%;" id="longterm_debt2">
								<thead class="check_period_table">								
										<th width="5%;" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("部署名"); ?></th>
										<!-- <th width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("勘定科目"); ?></th> -->
										<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("取引先名"); ?></th>
										<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("品目コード"); ?></th>
										<th width="6%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("入庫Index No."); ?></th>
										<!-- <th width="5%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("期日"); ?></th> -->
										<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("金額"); ?></th>
										<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("状況/滞留理由"); ?></th>
										<th width="4%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("入金日/入金予定日等"); ?></th>
									</tr>								
								</thead>
								<tbody>
									<?php foreach($previous_month_60_result as $result5){
									
										$layer_name = h($result5['tbl_2']['name_jp']);
										$destination_name = h($result5['tbl_2']['destination_name']);
										$item_code = h($result5['tbl_2']['item_code']);
										$receipt_index_no = h($result5['tbl_2']['receipt_index_no']);
										$money_amount = h($result5['tmp']['amount']);
										$reason = h($result5['tbl_2']['reason']);
										$settlement_date = h($result5['tbl_2']['settlement_date']);	
										if($settlement_date == '0000-00-00' || $settlement_date == ''){
											$settlement_date = '';
										}else{
											$settlement_date = h(date("Y-m-d", strtotime($result5['tbl_2']['settlement_date'])));
										}
										
										?>
									<tr>
										<td style="width: 5%; word-wrap: break-word;"><?php echo $layer_name; ?></td>
										<td style="width: 5%; word-wrap: break-word;" class = "dest_name_1"><?php echo $destination_name; ?></td>
										<td style="width: 5%; word-wrap: break-word;" class = "dest_code_1"><?php echo $item_code; ?></td>
										<td style="width: 6%; word-wrap: break-word;" class = "log_index_no_1"><?php echo $receipt_index_no; ?></td>
										<td style="width: 10%; word-wrap: break-word;text-align:right" class = "jp_amount_1"><?php echo number_format($money_amount); ?></td>
										<td style="width: 10%; word-wrap: break-word;"><?php echo $reason; ?></td>
										<td style="width: 10%; word-wrap: break-word;"><?php echo $settlement_date; ?></td>
									</tr>
									<?php }?>
								</tbody>								
							</table>
						</div>
					</div>		
				</div>	
			</div>
		</div>
		
</div>
<?php
    echo $this->Form->end();
?>
<script>
//Table column merge
 	function mergeCell() {
	    var rowSpan = 1;
	    var topMatchTd;
	    var previousValue = "";
	    
	    // $(".dest_name_1").each(function(){
	    //     if($(this).text() == previousValue)
	    //     {
	    //       rowSpan++;
	    //       $(topMatchTd).attr('rowspan',rowSpan);
	    //       $(topMatchTd).siblings('.dest_name_1').attr('rowspan',rowSpan);
	    //       $(this).siblings('.dest_name_1').remove();
	    //       $(this).remove();
	    //     }
	    //     else
	    //     {
	    //       topMatchTd = $(this);
	    //       rowSpan = 1;
	    //     }           
	    //     previousValue = $(this).text();
	    // });  
		// $(".dest_code_1").each(function(){
	    //     if($(this).text() == previousValue)
	    //     {
	    //       rowSpan++;
	    //       $(topMatchTd).attr('rowspan',rowSpan);
	    //       $(topMatchTd).siblings('.dest_code_1').attr('rowspan',rowSpan);
	    //       $(this).siblings('.dest_code_1').remove();
	    //       $(this).remove();
	    //     }
	    //     else
	    //     {
	    //       topMatchTd = $(this);
	    //       rowSpan = 1;
	    //     }           
	    //     previousValue = $(this).text();
	    // });  
	    $(".log_index_no_1").each(function(){
	        if($(this).text() == previousValue)
	        {
	          rowSpan++;
	          $(topMatchTd).attr('rowspan',rowSpan);
	          $(topMatchTd).siblings('.log_index_no_1').attr('rowspan',rowSpan);
	          $(this).siblings('.log_index_no_1').remove();
	          
	          $(topMatchTd).siblings('.jp_amount_1').attr('rowspan',rowSpan);
	          $(this).siblings('.jp_amount_1').remove();
	         
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
	    
	    // $(".dest_name").each(function(){
	    //     if($(this).text() == previousValue)
	    //     {
	    //       rowSpan++;
	    //       $(topMatchTd).attr('rowspan',rowSpan);
	      
	    //       $(topMatchTd).siblings('.dest_name').attr('rowspan',rowSpan);
	    //       $(this).siblings('.dest_name').remove();

	         
	    //       $(this).remove();
	    //     }
	    //     else
	    //     {
	    //       topMatchTd = $(this);
	    //       rowSpan = 1;
	    //     }           
	    //     previousValue = $(this).text();
	    // });   

		// $(".dest_code").each(function(){
	    //     if($(this).text() == previousValue)
	    //     {
	    //       rowSpan++;
	    //       $(topMatchTd).attr('rowspan',rowSpan);
	      
	    //       $(topMatchTd).siblings('.dest_code').attr('rowspan',rowSpan);
	    //       $(this).siblings('.dest_code').remove();

	         
	    //       $(this).remove();
	    //     }
	    //     else
	    //     {
	    //       topMatchTd = $(this);
	    //       rowSpan = 1;
	    //     }           
	    //     previousValue = $(this).text();
	    // });  

	    $(".log_index_no").each(function(){
	        if($(this).text() == previousValue)
	        {
	          rowSpan++;
	          $(topMatchTd).attr('rowspan',rowSpan);

	          $(topMatchTd).siblings('.log_index_no').attr('rowspan',rowSpan);
	          $(this).siblings('.log_index_no').remove();
	          
	          $(topMatchTd).siblings('.jp_amount').attr('rowspan',rowSpan);
	          $(this).siblings('.jp_amount').remove();
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
</script>