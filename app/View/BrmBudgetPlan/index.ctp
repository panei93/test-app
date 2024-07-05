<?php
	echo $this->Form->create(false,array('type'=>'post', 'action'=>'','id' => 'budgetresultdiff', 'name' => 'budgetresultdiff' ,'enctype'=> 'multipart/form-data'));
?>
<style type="text/css">
	.table {
		border-collapse: collapse;
		margin-bottom:40px;
	}

	.table, .table td, .table th {
		border: 1px solid #ccc !important;
	}

	table tr td:first-child {
		padding: 5px !important;
	}
	.align_right{
		text-align: right;
	}
	#b_r_compare, th, td {
		height: 20px !important;
		letter-spacing: -1px;

	}
	.one {
		width: 25px;
	}
	.negative {
		color: #f31515;
		text-align: right !important;
	}
	.top_box {
		background-color: white;
		text-align: right !important;
	}
	.string {
		text-align: left !important;
		white-space: nowrap;
	}
	.number {
		text-align: right !important;
	}
	.tabcontent {
		padding: 6px 12px;
		border: 1px solid #ccc;
		border-top: none;
	}
	.right{
		text-align: right;
	}
	.text_months{
		padding:0 4px 0 4px;
		width: 100%;
		outline: none;
		border: none;
		border-radius: 0;
		box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
		resize: none;
		height: 23px;
	}
	.row{
		margin-right: 0px;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control, .total_table_row {
		background-color: #F9F9F9;
		opacity: 1;
	}
	.color{
		margin: 0px;
		border: 1px solid lightgrey !important;
		vertical-align: middle !important;
	}
	.one{
		width: 150px;
	}
	.font_bold{
		font-weight: bold;
	}
	table.budget_table tr td.align_right{
		padding-right: 4px;
		padding-left: 4px;
	}
	table.budget_table tr td {
		padding: 0px;
		vertical-align: middle;
	}
	table.budget_table thead tr th {
		text-align: center;
	}
	.bgcolor{
		background-color: #D5F4FF;
	}
	.amount_input {
		outline:none;
		text-align: right;
		/*position: absolute;
		top:0;
		left:0;*/
		display: block;
		margin: 0;
		height: 100%;
		width: 100%;
		border: none;
		padding: 5px;
		box-sizing: border-box;
		border: none;
		text-align: right;
		background-color: #D5F4FF;
	}
	.amount_input.disabled, .taxDisabled {
		background-color: #F9F9F9;
	}
	.total_field {
		text-align: right;
		padding: 5px !important;
	}
	.negative {
		color: #f31515;
	}

	.adjust{
		padding-bottom: 15px;
	}
	.one_btn{
		width: 150px;
	}

</style>
<script type="text/javascript">
var subAccount = <?php echo json_encode($subAccount); ?>;
var tax = <?php echo $tax; ?>;
var taxEditabled = <?php if($taxEditabled) echo 'true'; else echo 'false'; ?>;

	$(document).ready(function(){
		// calculation of type 1 and 2 when page load for edit case
		var subAccId = 0;
		var firstAcc = 0;
		$.each(subAccount, function(accKey, accValue){
			if(accKey == 0) firstAcc = accValue['brm_accounts']['id'];
			for(colNo = 1; colNo <= 13; colNo++){
				if(colNo != 7){
					subAccId =  accValue['brm_accounts']['id'];
					if($("#subId_"+subAccId+"_"+colNo).val()) {
						$("#subId_"+subAccId+"_"+colNo).change();
					}
				}
			}
		});
		$("#subId_"+firstAcc+"_13").change();
		
	$('input[type=text]:not([readonly])').focus(function(event){
		$(this).val(function(index,value){
			if(value == 0.0){
				value="";
			}else{
				return value;
			}
			return value;

		});

	});

	// when focus out, set number to format number eg : 1,000.0
	$('input[type=text]:not([readonly])').focusout(function(event){
		if(this.id != 'filling_date'){
			$(this).val(function(index,value){
				if(value == ""){
					value=0.0;
				}else{
					return formatNumber(value, false);
				}
				return formatNumber(value, false);
				
			});
		}
		

	});
});
// Author : Ei Thandar Kyaw on 17/07/2020
function formatNumber(num, copy) {
	if(num == ""){
		num = 0;
		return num.toFixed(1);
	}else{
		if(num.toString().indexOf(".") != -1) {
			if(!copy) num = Math.round(num * 10) / 10;
			var numArr = num.toString().split(".");
			var value = numArr[0];
			var value = value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
			if(numArr[1]) return value+"."+numArr[1];
			else return value+".0";
		}else{
			var value = num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
			return value+'.0';
		}	
	}
	
}

</script>
<?php
    echo $this->element('autocomplete', array(
		"to_level_id" => "",
		"cc_level_id" => "",
		"bcc_level_id" => "",
		"submit_form_name" => "budgetresultdiff",
		"MailSubject" => "",
		"MailTitle"   => "",
		"MailBody"    =>""
		));
?>
<div id="overlay">
	<span class="loader"></span>
</div>
<input type="hidden" name="toEmail" id="toEmail" value="">
<input type="hidden" name="ccEmail" id="ccEmail" value="">
<input type="hidden" name="bccEmail" id="bccEmail" value="">
<input type="hidden" name="mailSubj" id="mailSubj">
<input type="hidden" name="mailBody" id="mailBody">
<input type="hidden" name="mailSend" id="mailSend">
<div class="container register_container">
	<div class="heading_line_title">
		<h3><?php echo __($_GET['year']."年度 "); ?><?php echo __($usedName['formName']); ?></h3>
		<hr>
	</div>
	<div class="row" style="font-size: 0.95em;">
		<!-- start show error msg and success msg from controller  -->
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.FYBudgetSuccess"))? $this->Flash->render("FYBudgetSuccess") : "";?></div>						
			<div class="error" id="error"><?php echo ($this->Session->check("Message.FYBudgetError"))? $this->Flash->render("FYBudgetError") : "";?></div>									
		</div>
		<!-- end show error and success msg from controller -->
	</div>
	<!-- end show tab -->

	<div class="row" style="padding-bottom: 20px;">	
		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-sm-12">
					<label for="term" class="col-sm-4 col-form-label">
						<?php echo __($usedName['term']);?>
					</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="term" name="term" value="<?php echo $term; ?>" readonly="readonly"/>
					</div>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-sm-12">
					<label for="ba_code" class="col-sm-4 col-form-label">
						<?php echo __($layer_types[SETTING::LAYER_SETTING['bottomLayer']]);?>
					</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="ba_code" name="ba_code" value="<?php echo $layer_code.'/'.$ba_name; ?>" readonly="readonly"/>
					</div>
				</div>
			</div>
			<?php
			
			$fillingDate = "";
			if(isset($filling_date) && $filling_date != '' && $filling_date != '0000-00-00 00:00:00'){
				$date=date_create($filling_date);
				$fillingDate = date_format($date,"Y/m/d");
			}
			$deadlineDate = "";
			if(isset($deadline_date) && $deadline_date != '' && $deadline_date != '0000-00-00 00:00:00'){
				$dDate=date_create($deadline_date);
				$deadlineDate = date_format($dDate,"Y/m/d");
			}
			?>
			<div class="form-group row">
				<div class="col-sm-12">
					<label for="deadline_date" class="col-sm-4 col-form-label">
						<?php echo __('提出期日');?>
					</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="deadline_date" name="deadline_date" value="<?php echo  $deadlineDate; ?>" readonly="readonly" />
					</div>
				</div>
			</div>
			<input type="hidden" class="form-control" id="filling_date" name="filling_date" value="<?php echo  $fillingDate; ?>"/>	
		</div>
	</div>
	<?php if (!isset($no_data)): ?>
		<div class="row mb-20">
			<div class="col-sm-12 col-md-12 text-right adjust">
				<label style="color: white;" id="browse"class="btn btn-success btn_sumisho <?php if($isApproved) echo 'disabled'; ?>"><?php echo __("ファイルをアップロードする");?>
					<input type="file" name="budget_upload" class ="budget_upload" value="ssss"<?php if($isApproved) echo "disabled"?>>
				</label>
				<input type="button" id="btn_excel_download" name="btn_excel_download" class="btn btn-success btn_sumisho" value="<?php echo __("Excelダウンロード"); ?>" >
				<input type="button" id="btn_save" name="btn_save" class="btn btn-success btn_sumisho" value="<?php echo __("保存"); ?>" <?php if($isApproved) echo "disabled"?> >
			</div>
			<input type="hidden" id="year" name="year" value="<?php echo $_GET['year'];?>">
			<div class="col-sm-12 col-sm-12 text-right adjust">
				<div class="row">
					<!-- <label style="color: white;" class="<?php if($isApproved) echo 'disabled'; ?> btn one_btn"><?php echo __("一括アップロード"); ?>
						<input type="file" name="btn_upload_bulk_file" class = "uploadbulkfile" data-id = "0" data-id-head = "<?php echo $head_data['head_dept_id']; ?>" id="btn_upload_bulk_file" <?php if($isApproved || $check_account_setup == "not_exit") echo "disabled"?>>	        	
					</label> -->
					<!-- Excel Download -->
					<!-- <input type="button" id="btn_bulk_excel_download" name="btn_bulk_excel_download" class="btn btn-success btn_sumisho one_btn" value="<?php echo __("一括ダウンロード"); ?>" > -->
					
				</div>		
			</div>
		</div>
		<div>
			<div class="table-responsive tbl-wrapper container-fluid">
				<div class="col-sm-12">
					<div class="font_bold text-right">(単位：千円)</div>
				</div>
				<table class="table table-bordered budget_table">
					<thead class="check_period_table">
						<tr>
							<th rowspan="4"></th>
							<th colspan="14"><?php echo $_GET['year']." ".__($usedName['year']); ?></th>
							
							<th style="width: 9%;" rowspan="5"><?php echo __("年間"); ?></th>

						</tr>
						<tr>
							<th colspan="14"><?php echo __($usedName['name']); ?></th>
						</tr>
						<tr>
							<th colspan="7"><?php echo __("上半期"); ?></th>
							<th colspan="7"><?php echo __("下半期"); ?></th>
							
						</tr>
						<tr>
							<th style="width: 5%;"><?php echo __($Months[0]); ?></th>
							<th style="width: 5%;"><?php echo __($Months[1]); ?></th>
							<th style="width: 5%;"><?php echo __($Months[2]); ?></th>
							<th style="width: 5%;"><?php echo __($Months[3]); ?></th>
							<th style="width: 5%;"><?php echo __($Months[4]); ?></th>
							<th style="width: 5%;"><?php echo __($Months[5]); ?></th>
							<th style="width: 6%;"><?php echo __("上半期計"); ?></th>
							<th style="width: 5%;"><?php echo __($Months[6]); ?></th>
							<th style="width: 5%;"><?php echo __($Months[7]); ?></th>
							<th style="width: 5%;"><?php echo __($Months[8]); ?></th>
							<th style="width: 5%;"><?php echo __($Months[9]); ?></th>
							<th style="width: 5%;"><?php echo __($Months[10]); ?></th>
							<th style="width: 5%;"><?php echo __($Months[11]); ?></th>
							<th style="width: 9%;"><?php echo __("下半期計"); ?></th>
						</tr>
						
					</thead>
					<tbody >

						<?php if (isset($budgetData)): ?>
							<?php $readonly = ($isApproved) ? 'readonly' : ""; ?>
							<?php foreach ($budgetData as $subacc_name => $value): ?>
								<tr class="type_<?php echo $value['type'] ?>">
									<?php 
									
									$nameBold = ($value['type'] == 1 || $value['type'] == 2 || ($value['type'] == 3 && $taxEditabled == false)) ? 'font_bold' : "" 
									?>
									<td class="<?php echo $nameBold; ?> " id="<?php echo $subacc_name; ?>"><?php echo $subacc_name; ?></td>
									<?php foreach ($value['amount'] as $field => $amount): ?>
										<?php 
											$t_amount = (!empty($amount)) ? ((strpos($amount,'%') == true) ? $amount : number_format($amount,3)) : 0.0;
											//$amount = (!empty($amount)) ? $amount : 0;
											$amount = (!empty($amount)) ? ((strpos($amount,'%') == true) ? $amount : number_format($amount,1)) : 0.0;
											$negclass = ($amount < 0) ? 'negative' : "";
											$taxDisabled = '';
											if($value['type'] == 3 && isset($value['disable'.$field])) $taxDisabled = 'taxDisabled';
										?>
										<?php if ($nameBold == 'font_bold'): ?>
											<?php if ($field == 'first_half' || $field == 'second_half' || $field == 'whole_total'): ?>
												<?php if ($field == 'whole_total'): ?>
													<input type="hidden" id="hid_<?php echo $subacc_name."_".$field ?>" name="hid_budget_total[<?php echo($subacc_name) ?>]" class="total_field hid_<?php echo $subacc_name."_".$field ?>" value="<?php echo $value['hid_amount'][$field]; ?>">
													<?php else: ?>
													<input data-type="<?php echo $value['type'] ?>" data-field="<?php echo($field) ?>" data-calfield='<?php echo($value['calculation_method']) ?>' data-autochange="<?php echo($value['auto_changed']) ?>" type="hidden" class="amount_input hid_<?php echo $subacc_name."_".$field ?> <?php echo $negclass ?> <?php echo $value['disable']; ?> <?php echo $value['disable'.$field]; ?>" value="<?php echo $value['hid_amount'][$field]; ?>" <?php echo $value['disable']; ?> <?php echo $value['disable'.$field]; ?>>
												<?php endif ?>
												<td data-type="<?php echo $value['type'] ?>" data-calfield='<?php echo($value['calculation_method']) ?>' class="total_field <?php echo $subacc_name."_".$field ?> <?php echo $negclass ?>"><?php echo $amount; ?></td>
											<?php else: ?>
												<td data-type="<?php echo $value['type'] ?>" data-calfield='<?php echo($value['calculation_method']) ?>' class="total_field <?php echo $subacc_name."_".$field ?> <?php echo $negclass.' '.$taxDisabled; ?>"><?php echo $amount; ?>
												</td>
												<input data-type="<?php echo $value['type'] ?>" data-field="<?php echo($field) ?>" data-calfield='<?php echo($value['calculation_method']) ?>' data-autochange="<?php echo($value['auto_changed']) ?>" type="hidden" class="amount_input hid_<?php echo $subacc_name."_".$field ?> <?php echo $negclass ?> <?php echo $value['disable']; ?> <?php echo $value['disable'.$field]; ?>" value="<?php echo $value['hid_amount'][$field]; ?>" <?php echo $value['disable']; ?> <?php echo $value['disable'.$field]; ?>>
												<?php
												if($subacc_name == '税引前利益'){
													//$profit[$field] = $amount;
													$profit[$field] = $t_amount;
													$taxSubAccountName = $subacc_name;
												}else if($subacc_name == '社内税金'){	
												?>
													<input type="hidden" name="beforeTax[<?php echo($value['acc_id']); ?>][<?php echo $field; ?>]" value="<?php echo ($taxDisabled) ? $amount : $profit[$field]; ?>" class="hid_<?php echo $taxSubAccountName."_".$field ?>">
												<?php
												}
												?>
											<?php endif ?>
										<?php else: ?>
											<?php if ($field == 'first_half' || $field == 'second_half' || $field == 'whole_total'): ?>
												<?php if ($field == 'whole_total'): ?>
													<input type="hidden" id="hid_<?php echo $subacc_name."_".$field ?>" name="hid_budget_total[<?php echo($subacc_name) ?>]" class="total_field hid_<?php echo $subacc_name."_".$field ?>" value="<?php echo $value['hid_amount'][$field]; ?>>">
												<?php else: ?>
														<input type="hidden" id="hid_<?php echo $subacc_name."_".$field ?>" class="total_field hid_<?php echo $subacc_name."_".$field ?>" value="<?php echo $value['hid_amount'][$field]; ?>">
												<?php endif ?>
												<td data-type="<?php echo $value['type'] ?>" data-calfield='<?php echo($value['calculation_method']) ?>' data-autochange="<?php echo($value['auto_changed']) ?>" class="total_field <?php echo $subacc_name."_".$field ?> <?php echo $negclass ?>"><?php echo $amount; ?></td>
											<?php else: ?>
												<td>
													<input data-type="<?php echo $value['type'] ?>" data-field="<?php echo($field) ?>" data-calfield='<?php echo($value['calculation_method']) ?>' data-autochange="<?php echo($value['auto_changed']) ?>" type="" name="budget[<?php echo($value['acc_id']) ?>][<?php echo($field) ?>]" class="amount_input <?php echo $subacc_name."_".$field ?> <?php echo $negclass ?> <?php echo $value['disable']; ?> <?php echo $value['disable'.$field]; ?>" value="<?php echo $amount; ?>" <?php echo $value['disable']; ?> <?php echo $value['disable'.$field]; ?>>
													<input data-type="<?php echo $value['type'] ?>" data-field="<?php echo($field) ?>" data-calfield='<?php echo($value['calculation_method']) ?>' data-autochange="<?php echo($value['auto_changed']) ?>" type="hidden" name="hid_budget[<?php echo($value['acc_id']) ?>][<?php echo($field) ?>]" class="amount_input hid_<?php echo $subacc_name."_".$field ?> <?php echo $negclass ?> <?php echo $value['disable']; ?> <?php echo $value['disable'.$field]; ?>" value="<?php echo $value['hid_amount'][$field]; ?>" <?php echo $value['disable']; ?> <?php echo $value['disable'.$field]; ?>>
												</td>
											<?php endif ?>
										<?php endif ?>
									<?php endforeach ?>
								</tr>
							<?php endforeach ?>
						<?php endif ?>
						<?php
						// Author : Ei Thandar Kyaw on 17/07/2020
						
						?>
					</tbody>
							  
				</table>
				
				<input type="hidden" id="year" name="year" value="<?php echo $_GET['year'];?>">
				<input type="hidden" id="formType" name="formType" value="<?php if(isset($_GET['budget'])) echo 'budget'; else echo 'forecast'; ?>">
				<input type="hidden" id="code" name="code" value="<?php echo $_GET['code'];?>">
				<input type="hidden" id="hq" name="hq" value="<?php echo $_GET['hq'];?>">
				<input type="hidden" id="termId" name="termId" value="<?php echo $_GET['term'];?>">
			</div>
			<br>
		</div>
	<?php elseif($no_data == "no_data"): ?>
		<div class="col-sm-12">
			<p class="no-data"><?php echo __("表示するアカウントはありません。"); ?></p>
		</div>
	<?php endif ?>	
</div>
<?php
	echo $this->Form->end();
?>
<script type="text/javascript">
	//$('#year').val(year);
	// Author : Ei Thandar Kyaw on 17/07/2020 , Edited by PanEiPhyo (20200911)
	// description : when type value in input field, will check autochanged field and read autochanged field and will calculate based on calculation_method and type
	// event for input type value
	// type is check calculation (add/percent)
	// autoChanged is to change other fields when change input value
	// currow is current row
	// col is current column
	$(document).ready(function(){
		isApproved = '<?php echo $isApproved; ?>';
		if(!isApproved){
			$('.datepicker').datepicker({
			    format: 'yyyy/mm/dd'
	 		});
		}
		//Added by PanEiPhyo (20200911)
		//When place cursor in input field
		$('.budget_table').on('focus','.amount_input', function(event){
			
			var retVal = "";
			var classAtt = $(this).attr('class');
			var className = classAtt.split(" ");
			$(this).val($(".hid_"+className[1]).val());
			$(this).val(function(index,value){
				if(value != 0){
					//Remove zero after decimal
					retVal = value.replace(/\.0+$/,"");

					//Remove comma from value
					retVal = retVal.replace(/,/g,"");
					//retVal = retVal.replace(',',"");
				}
				return retVal;
			});

		});

		//When insert the value to input field
		$(".amount_input").on('input', function(e) { 
			$(this).val($(this).val().replace(/[^0-9\.\-]/g, "")); 
			var classAtt = $(this).attr('class');
			var className = classAtt.split(" ");
			var dataType = $(this).attr('data-type');
			if(dataType == 3) $(".hid_"+className[1]).val( Math.floor($(this).val()).toFixed(1));
			else $(".hid_"+className[1]).val($(this).val());
		}); 

		// when focus out, set number to format number eg : 1,000.000
		$('.amount_input').on('focusout',function(event){
			// in IE, if value is empty when change, change function not work and so, call change function in focusout
			var ua = window.navigator.userAgent;
			var msie = ua.indexOf("MSIE ");
			
			if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)){
				if($(this).val() == '') {
					$(this).trigger('change');
				}
			}
			
			$(this).val(function(index,value){
				value = value.replace(/,/g, "");
				if(value == "" || value == "-"){
					$(this).removeClass('negative');
					value=0;
				}else{
					if($(this).attr('data-type') == 3) value = Math.floor(value).toFixed(1);
					return formatNumber(value, false);
				}
				if($(this).attr('data-type') == 3) value = Math.floor(value).toFixed(1);
				return formatNumber(value, false);
				
			});
			if(autoFocus) autoFocus = false;

		});

		$('.amount_input').on('keyup',function(event){
			if(autoFocus){
				$(this).trigger('change');
				previosValue = $(this).val();
			}
			
			//decimalOnly =/^\s*-?(\d{0,4})(\.\d{0,1})?\s*$/; 
			decimalOnly =/^\s*-?(\d{0,7})(\.\d{0,3})?\s*$/;
			if(decimalOnly.test($(this).val())) {
				$(this).css({"border": "0px"});
				$(this).removeClass('decimalErr');
			}else{
				//$(this).css({"border": "1px","border-style": "groove","border-color": "#f31515"});
				//$(this).addClass('decimalErr');
			}
			MakeNegative($(this));

		});
		var previosValue = 0;
		var inputNotHidden = '';
		var fValue = 0;
		var autoFocus = false;
		//Added by PanEiPhyo (20200911)
		$('.amount_input').on('focusin', function(){
			$(this).data('val', $(this).val()); //Save current value before change
			var classAtt = $(this).attr('class');
			var className = classAtt.split(" ");
			//previosValue = $("."+className[1]).val();
			previosValue = $(".hid_"+className[1]).val();
		});

		//Added by PanEiPhyo (20200911)
		$('.amount_input:not([type=hidden])').on('change', function(){
			var classAtt = $(this).attr('class');
			var className = classAtt.split(" ");
			decimalOnly =/^\s*-?(\d{0,7})(\.\d{0,3})?\s*$/;
			if(decimalOnly.test($(this).val().replace(/,/g, "")) == false) {
				$("#error").html(errMsg(commonMsg.JSE061)).show();
			    scrollText();
				// $(this).val('0.0');
				// $(".hid_"+className[1]).val('0.0');
				/* when decimal error state, refill the amount (khin) */
				var oldDigit = '';
				if($(this).val().split('.').length > 1) {//after decimal digit limited
			    	oldDigit = '.'+$(this).val().split('.')[1].substring(0, 3);
			    	$(this).val($(this).val().split('.')[0]+oldDigit);
			    }else {//before decimal digit limited
			    	$(this).val($(this).val().substring(0, 7));
			    }
				$(".hid_"+className[1]).val($(this).val());
				$(this).trigger('change');
			}else{
				//if($(this).val()){
					MakeNegative($(this));
					//var curAcc = $(this).closest('tr').find('td:first-child').html();
					var curAcc = $(this).closest('tr').find('td:first-child').attr('id');

					var colName = "";
					var colArr = [];
					var prev;
			
					var previous = (previosValue!="" && previosValue != undefined) ? parseFloat(previosValue.replace(/,/g,"")) : 0; //get previous value that was saved in 'focusin' state
					if(isNaN(previous)){
						prev = 0;
					}
					else{
						prev = (previosValue!="" && previosValue != undefined) ? parseFloat(previosValue.replace(/,/g,"")) : 0; //get previous value that was saved in 'focusin' state
					}
					//var current = ($(this).val()=="" || $(this).val()== '-') ? 0 : parseFloat($(this).val().replace(/,/g,"")); //get current value
					//var current = ($(this).val()=="") ? 0 : parseFloat($(this).val().replace(/,/g,"")); //get current value
					if(className[1].indexOf('hid_') != -1) className[1] = className[1].substring(4);
					
					var current = ($(".hid_"+className[1]).val() =="") ? 0 : parseFloat($(".hid_"+className[1]).val().replace(/,/g,"")); //get current value
					var diff = current - prev; //get difference
					var colName = $(this).attr('data-field'); //get column name (eg. month_1_amt, month_2_amt,...)
			
					//prepare change column array (eg. [0]:'month_1_amt',[1]:'first_half',[2]:'whole_total')

					colArr[0] = colName;
					if ((colName == 'month_1_amt') || (colName == 'month_2_amt') || (colName == 'month_3_amt') || (colName == 'month_4_amt') || (colName == 'month_5_amt') || (colName == 'month_6_amt')) {
						colArr[1] = 'first_half';
					} else {
						colArr[1] = 'second_half';
					}
					colArr[2] = 'whole_total';

					var autochange = $(this).attr('data-autochange'); //get autochange accounts
					var autochange = autochange.replace(' ','');
					var myAccArr = autochange.split(","); //set autochange accounts in array
					$.each(colArr, function(colIndex, colValue) {
						if (colIndex != 0) { //if not current input, calculate for total fields
							
							var hidCurClassName = ".hid_"+curAcc+"_"+colValue;
							var hidCuroldVal = $(hidCurClassName).val().replace(/,/g,"");
							hidCurCalVal = (parseFloat(hidCuroldVal) + diff);
							$(hidCurClassName).val( hidCurCalVal.toFixed(3));

							var curClassName = "."+curAcc+"_"+colValue;
							//var curoldVal = $(curClassName).html().replace(/,/g,"");
							//curCalVal = (parseFloat(curoldVal) + diff).toFixed(1);
							curCalVal = (parseFloat(hidCuroldVal) + diff).toFixed(1);
							$(curClassName).html(formatNumber(curCalVal, false));

							if (curCalVal < 0) {
								$(curClassName).addClass('negative');
							} else {
								if($(curClassName).hasClass('negative')){
									$(curClassName).removeClass('negative')
								}
							}
					
						}
						//var accArr = myAccArr.filter((v, i, a) => a.indexOf(v) === i);
						var accArr = myAccArr;
						$.each(accArr, function(index, value) {
							setValue = true;
							var value = value.replace(' ','');
							var value = value.replace('	','');

							var className = value+"_"+colValue;
							var type = $("."+className).attr('data-type');
							
							var calVal = 0;
							var hid_calVal = 0;
							var calculateFields = $("."+className).attr('data-calfield');
							
							if(calculateFields != undefined){
							
								var calculateFields = jQuery.parseJSON(calculateFields);
								var calculateFields = calculateFields['field'];
								if (type == '1') { // '+' operator

									var oldVal = $("."+className).html().replace(/,/g,"");
									var hid_oldVal = $(".hid_"+className).val().replace(/,/g,"");
									calVal = (parseFloat(hid_oldVal) + diff).toFixed(1);
									
									hid_calVal = (parseFloat(hid_oldVal) + diff).toFixed(3);
									
									if(calculateFields.length == 2){
										setValue = false;
										if($("."+calculateFields[0]+"_"+colValue).val())
										var value1 = parseFloat($("."+calculateFields[0]+"_"+colValue).val().replace(/,/g,"")) || 0;
										else var value1 = parseFloat($("."+calculateFields[0]+"_"+colValue).html().replace(/,/g,"")) || 0;
										if($("."+calculateFields[1]+"_"+colValue).html()) var value2 = parseFloat($("."+calculateFields[1]+"_"+colValue).html().replace(/,/g,"")) || 0;
										else var value2 = parseFloat($("."+calculateFields[1]+"_"+colValue).val().replace(/,/g,"")) || 0;
										calVal = (parseFloat(value1) + parseFloat(value2)).toFixed(1);
										setTimeout(function(){
											if($("."+calculateFields[0]+"_"+colValue).val())
											var value1 = parseFloat($("."+calculateFields[0]+"_"+colValue).val().replace(/,/g,"")) || 0;
											else var value1 = parseFloat($("."+calculateFields[0]+"_"+colValue).html().replace(/,/g,"")) || 0;
											if($("."+calculateFields[1]+"_"+colValue).html()) {
												value2 = $("."+calculateFields[1]+"_"+colValue).html().replace(/,/g,"");
											}else {
												value2 = $("."+calculateFields[1]+"_"+colValue).val().replace(/,/g,"");
											}
											if(value1 == '') value1 = 0;
											if(value2 == '') value2 = 0;
											calVal = (parseFloat(value1) + parseFloat(value2)).toFixed(1);
											calVal = formatNumber(calVal, false);
											$("."+className).html( calVal );
											$("#hid_"+className).val( calVal );
											if (calVal != 0 && parseFloat(calVal.replace(/,/g,"")) < 0) {
												$("."+className).addClass('negative');
											} else {
												if($("."+className).hasClass('negative')){
													$("."+className).removeClass('negative')
												}
											}
											if($(".hid_"+calculateFields[0]+"_"+colValue).val())
											var hid_value1 = parseFloat($(".hid_"+calculateFields[0]+"_"+colValue).val().replace(/,/g,"")) || 0;
											else var hid_value1 = parseFloat($(".hid_"+calculateFields[0]+"_"+colValue).html().replace(/,/g,"")) || 0;
											if($(".hid_"+calculateFields[1]+"_"+colValue).html()) var hid_value2 = parseFloat($(".hid_"+calculateFields[1]+"_"+colValue).html().replace(/,/g,"")) || 0;
											else var hid_value2 = parseFloat($(".hid_"+calculateFields[1]+"_"+colValue).val().replace(/,/g,"")) || 0;
											
											if($(".hid_"+calculateFields[1]+"_"+colValue).html()) {
												hid_value2 = $(".hid_"+calculateFields[1]+"_"+colValue).html().replace(/,/g,"");
											}else {
												hid_value2 = $(".hid_"+calculateFields[1]+"_"+colValue).val().replace(/,/g,"");
											}
											if(hid_value1 == '') hid_value1 = 0;
											if(hid_value2 == '') hid_value2 = 0;
											hid_calVal = (parseFloat(hid_value1) + parseFloat(hid_value2)).toFixed(3);
											
											//hid_calVal = formatNumber(hid_calVal);
											$(".hid_"+className).val(hid_calVal);
										}, 100);
									}
									
									calVal = formatNumber(calVal, false);
								} else {

									if (type == '2') { // '/' operator
										if(colValue == 'first_half' || colValue == 'second_half' || colValue == 'whole_total') var value1 = parseFloat($(".hid_"+calculateFields[0]+"_"+colValue).val().replace(/,/g,"")) || 0;
										else var value1 = parseFloat($(".hid_"+calculateFields[0]+"_"+colValue).val().replace(/,/g,"")) || 0;
										var value2 = parseFloat($(".hid_"+calculateFields[1]+"_"+colValue).val().replace(/,/g,"")) || 0;
									
										
										setTimeout(function(){ 
											
											var value2 = parseFloat($(".hid_"+calculateFields[1]+"_"+colValue).val().replace(/,/g,"")) || 0;
											calVal = value2/value1;
											calVal = (!isNaN(calVal) && calVal !== Infinity && calVal !== -Infinity) ? Math.round(parseFloat(calVal*100))+'%' : 0+'%';
											
											$("."+className).html( calVal );
											$("#hid_"+className).val( calVal );
										}, 100);
										
									} else if (type == '3') { // type =3 , multiply with tax
									
										if(taxEditabled == true){
											setValue = false;
										}else{

										
											var value1 = parseFloat($("."+calculateFields[0]+"_"+colValue).html().replace(/,/g,"")) || 0;
											var hid_value1 = parseFloat($(".hid_"+calculateFields[0]+"_"+colValue).val().replace(/,/g,"")) || 0;
											//var tax = tax;
											
											calVal = value1 * tax;
											if(calVal < 0) calVal = (!isNaN(calVal) && calVal !== Infinity) ? -Math.ceil(calVal) : 0;
											else calVal = (!isNaN(calVal) && calVal !== Infinity) ? -Math.floor(calVal) : 0;
											
											hid_calVal = hid_value1 * tax;
											//hid_calVal = (!isNaN(hid_calVal) && hid_calVal !== Infinity) ? -hid_calVal : 0;
											hid_calVal = calVal.toFixed(1);
											
											
											$("."+className).val( formatNumber(calVal, false) ).trigger('change');
											//$(".hid_"+className).val(formatNumber(calVal));
											//hid_calVal = hid_calVal.toFixed(3);
											//$(".hid_"+className).val(hid_calVal);
											$(".hid_"+className).val(hid_calVal);

											if(colValue == 'first_half'){
												calVal = parseFloat($("."+value+"_month_1_amt").html().replace(/,/g,"")) || 0;
												calVal += parseFloat($("."+value+"_month_2_amt").html().replace(/,/g,"")) || 0;
												calVal += parseFloat($("."+value+"_month_3_amt").html().replace(/,/g,"")) || 0;
												calVal += parseFloat($("."+value+"_month_4_amt").html().replace(/,/g,"")) || 0;
												calVal += parseFloat($("."+value+"_month_5_amt").html().replace(/,/g,"")) || 0;
												calVal += parseFloat($("."+value+"_month_6_amt").html().replace(/,/g,"")) || 0;
												
												hid_calVal = calVal.toFixed(1);
											}else if(colValue == 'second_half'){
												calVal = parseFloat($("."+value+"_month_7_amt").html().replace(/,/g,"")) || 0;
												calVal += parseFloat($("."+value+"_month_8_amt").html().replace(/,/g,"")) || 0;
												calVal += parseFloat($("."+value+"_month_9_amt").html().replace(/,/g,"")) || 0;
												calVal += parseFloat($("."+value+"_month_10_amt").html().replace(/,/g,"")) || 0;
												calVal += parseFloat($("."+value+"_month_11_amt").html().replace(/,/g,"")) || 0;
												calVal += parseFloat($("."+value+"_month_12_amt").html().replace(/,/g,"")) || 0;
												
												hid_calVal = calVal.toFixed(1);
											}
											else if(colValue == 'whole_total'){
												var first = parseFloat($("."+value+"_first_half").html().replace(/,/g,"")) || 0;
												var sec = parseFloat($("."+value+"_second_half").html().replace(/,/g,"")) || 0;
												calVal = (first + sec).toFixed(1);
												var hid_first = parseFloat($(".hid_"+value+"_first_half").val().replace(/,/g,"")) || 0;
												var hid_sec = parseFloat($(".hid_"+value+"_second_half").val().replace(/,/g,"")) || 0;
												//hid_calVal = (hid_first + hid_sec).toFixed(3);
												hid_calVal = calVal;
												//$(".hid_"+className).val(hid_calVal);
												$(".hid_"+className).val(calVal);

											}
											
											calVal = formatNumber(calVal, false);
										}
									}
								}
								
								if(setValue){
									
									$(".hid_"+className).val( hid_calVal );
									$("."+className).html( calVal );
									//$("#hid_"+className).val( calVal );

									if (calVal != 0 && parseFloat(calVal.replace(/,/g,"")) < 0) {
										$("."+className).addClass('negative');
									} else {
										if($("."+className).hasClass('negative')){
											$("."+className).removeClass('negative')
										}
									}
								}
							}
						});
					});
				//}
			}
			
		});

		$('.amount_input').on('paste', function(e){

		 	var $this = $(this);
			if (window.clipboardData && window.clipboardData.getData) { // IE
			    	 v = window.clipboardData.getData('Text');
				                var x = $this.closest('td').index(),
				                    y = $this.closest('tr').index(),
				                    obj = {};
				                var disableRowCount = 0;
				                //text = v.trim('\r\n');
				                text = v;
				                $.each(text.split('\n'), function(i2, v2){
				                	var disableColCount = 0;
				                	if(v2.length > 0){
					                    $.each(v2.split('\t'), function(i3, v3){
					                        var row = y+i2, col = x+i3;
					                        showValue = v3;
					                        v3 = v3.replace(/,/g,"");
					                        obj['cell-'+row+'-'+col] = v3;
					                        if(v3 == ''  || v3 == 0) v3 = '0.0';
					                        if($this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').is(':disabled')){
					                        	disableRowCount = 1;	
					                        	
					                        }
					                        if($this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+')').hasClass('total_field')){
					                        	disableColCount = 1;
					                        }
					                        row = row + disableRowCount;
					                        col = col + disableColCount;
											if(col >= 9 && col <=14) col = col - 1;
											else if(col == 15) col = col - 2;
											v3 = formatNumber(v3, true);
											previosValue = $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input').val();
											if($this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val() > 0)
											$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').data('val', $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val());
											$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([type=hidden])').focusin();
											$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').val(v3).trigger('change');
											//if(i2 == 0 && i3 == 0) {
												fValue = v3;
												inputNotHidden = $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])');
											//}
											if(inputNotHidden.length) {
												inputNotHidden.focus();
												inputNotHidden.val(fValue);
												autoFocus = true;
											}
											//inputNotHidden = '';
											//$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val(showValue);
					                    });
				                	}
				                });

				    return false;         	

		    }else{
		    	//other browser
				    $.each(e.originalEvent.clipboardData.items, function(i, v){
				        if (v.type === 'text/plain'){

				            v.getAsString(function(text){
				                var x = $this.closest('td').index(),
				                    y = $this.closest('tr').index(),
				                    obj = {};
				                var disableRowCount = 0;
				                //text = text.trim('\r\n');
				                $.each(text.split('\n'), function(i2, v2){
				                	var disableColCount = 0;
				                	if(v2.length > 0){
					                    $.each(v2.split('\t'), function(i3, v3){
					                        var row = y+i2, col = x+i3;
					                        showValue = v3;
					                        v3 = v3.replace(/,/g,"");
					                        obj['cell-'+row+'-'+col] = v3;
					                        if(v3 == '' || v3 == 0) v3 = '0.0';
					                        if($this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').is(':disabled')){	      disableRowCount = 1;	
					                        }
					                        if($this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+')').hasClass('total_field')){
					                        	disableColCount = 1;
					                        }
					                        row = row + disableRowCount;
					                        col = col + disableColCount;
											if(col >= 9 && col <=14) col = col - 1;
											else if(col == 15) col = col - 2;
											v3 = formatNumber(v3, true);
											previosValue = $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input').val();
											if($this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val() > 0)
											$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').data('val', $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val());
											
											$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([type=hidden])').focusin();
											$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').val(v3).trigger('change'); 
											$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').blur();
											
											//if(i2 == 0 && i3 == 0) {
												fValue = v3;
												inputNotHidden = $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])');
											//}
											//if(showValue != '')$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val(showValue);

											if(inputNotHidden.length) {
												inputNotHidden.focus();
												inputNotHidden.val(fValue);
												autoFocus = true;
											}
											//inputNotHidden = '';
					                    });
						            }
				                });
				            });
				        }
				    });
			    	return false;    	
		    }

		});

    });

	// Author : Ei Thandar Kyaw on 17/07/2020
	// save data when click Save button
	$("#btn_save").click(function(){
		var errorFlag = false;
		
		if(!errorFlag){
			$.confirm({ 					
				title: '<?php echo __("保存確認");?>',									
				icon: 'fas fa-exclamation-circle',									
				type: 'green',									
				typeAnimated: true,	
				closeIcon: true,
				columnClass: 'medium',								
				animateFromElement: true,									
				animation: 'top',									
				draggable: false,									
				content: "<?php echo __("データを保存してよろしいですか。"); ?>",									
				buttons: {   									
							ok: {									
								text: '<?php echo __("はい");?>',									
								btnClass: 'btn-info',									
								action: function(){	
								loadingPic();																                                                                  
								getMail('saveData','save');                        															 																		
								}									
							},     									
							cancel : {									
									text: '<?php echo __("いいえ");?>',									
									btnClass: 'btn-default',									
									cancel: function(){									
									 scrollText();								
									}

								}									
						},									
				theme: 'material',									
				animation: 'rotateYR',									
				closeAnimation: 'rotateXR'									
			}); 
		}

	});

	// Author : Ei Thandar Kyaw on 29/07/2020
	// excel data when click Excel button
	$("#btn_excel_download").click(function(){
		document.forms[0].action = "excelData";		
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;	

	});
	// Read a page's GET URL variables and return them as an associative array.
	
	function loadingPic() { // function expression closure to contain variables
		var ua = window.navigator.userAgent;
		var msie = ua.indexOf("MSIE ");
		
		if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet 
	   {
		//alert("ie");
		var el = document.getElementById('imgLoading');	
		var i = 0;
		var pics = [ "<?php echo $this->webroot; ?>img/loading1.gif",
			   "<?php echo $this->webroot; ?>img/loading2.gif",
			   "<?php echo $this->webroot; ?>img/loading3.gif" ,
			   "<?php echo $this->webroot; ?>img/loading4.gif" ];
	   

		function toggle() {
			el.src = pics[i];           // set the image
			i = (i + 1) % pics.length;  // update the counter
		}
		setInterval(toggle, 250);
		$("#overlay").show();
			

		}else{
			//alert("other");
			// el.src = "<?php echo $this->webroot; ?>img/loading.gif";
			$("#overlay").show();
		}
		
	}
	function MakeNegative(num) {

	   if (num.val().indexOf('-') == 0) 
		{   
			$(num).addClass('negative');
		  
			num.val(function(i, v) { //index, current value
				return v;
			});
			
		}else{
			if($(num).hasClass('negative')){
				$(num).removeClass('negative')
			}
		}
	}
	function scrollText(){

		var error = $('#error').text();
		var success = $('.success').text();
		if(error){
			$("html, body").animate({ scrollTop: 0 }, "slow");              
		}
		if(success){
			$("html, body").animate({ scrollTop: 0 }, "slow");              
		}
	}
	$('#browse').on('change','.budget_upload',function(e) {

		if($(this).val() != '') {

			var file_name 	= this.files[0].name; 
			var file_size 	= this.files[0].size;  
			var myFile 		= this.files;
			var errorFlag   = true;

			if(file_size >= 10485760){ // check 10MB

				var newbr = document.createElement("div");                      
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE031)));
				document.getElementById("error").appendChild(a);  
				errorFlag = false;
				scrollText();
			}

			if(errorFlag){
				
				showPopupBox();
				
			}  
		}
	}); 
	//if upload file is already exist=>show duplicate confirm popup , else => show save confirm popup
	function showPopupBox(){
	   	
		   $.confirm({
			title: "<?php echo __('アップロード確認'); ?>",
			icon: 'fas fa-exclamation-circle',
			type: 'green',
			typeAnimated: true,
			closeIcon: false,
			columnClass: 'medium',
			animateFromElement: true,
			animation: 'top',
			draggable: false,  
			content: "<?php echo __("ファイルをアップロードしてよろしいでしょうか。");?>",
			buttons: {
				ok: {
					text: "<?php echo __('はい'); ?>",
					btnClass: 'btn-info',
					action:function(){
						loadingPic(); 									
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetPlan/saveUploadFile";
						document.forms[0].method = "POST";
						document.forms[0].submit();  
					}
				},    
				action: {
					text: "<?php echo __('いいえ'); ?>",
					btnClass: 'btn-default',
					action: function(){
			
					}
				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		});

		   
	   }

	   /* download bulk file */
	$('#btn_bulk_excel_download').click(function(){
			document.getElementById("error").innerHTML   = "";
			document.getElementById("success").innerHTML = "";
			let check_account_setup = "<?php echo $check_account_setup;?>";
			
			if(check_account_setup == "exit"){
				document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetPlan/downloadBulkExcelDownload";
				document.forms[0].method = "POST";
				document.forms[0].submit();
				return true;
			}else{

				let newbr = document.createElement("div");                      
				let a     = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE083)));
				document.getElementById("error").appendChild(a);
			}
		});
	/* upload bulk file */
	$(".uploadbulkfile").change(function() {
		document.getElementById("success").innerHTML = "";
		document.getElementById("error").innerHTML   = "";  
		
		var file_name = $("#btn_upload_bulk_file").prop('files')[0].name;
		var file_size = $("#btn_upload_bulk_file").prop('files')[0].size;
		var fbdFile = document.getElementById('btn_upload_bulk_file').files.length;

		
		var fbdChk = true;
					
		/* Check File Choose */		
		if(fbdFile != '1') {

			var newbr = document.createElement("div");                      
			var a     = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE024)));
			document.getElementById("error").appendChild(a);                      
			fbdChk = false;  

		} 

		
		if(fbdChk) {   
			$.confirm({                     
				title: '<?php echo __('アップロード確認'); ?>',                                  
				icon: 'fas fa-exclamation-circle',                                  
				type: 'green',                                  
				typeAnimated: true, 
				closeIcon: true,
				columnClass: 'medium',                              
				animateFromElement: true,                                   
				animation: 'top',                                   
				draggable: false,                                
				content: "<?php echo __('ファイルをアップロードしてよろしいでしょうか。');?>",                                   
				buttons: {                                      
					ok: {                                   
						text: '<?php echo __("はい");?>',                                 
						btnClass: 'btn-info',                                   
						action: function(){        
								loadingPic();
								document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetPlan/saveBulkExcelFile";
								document.forms[0].method = "POST";
								document.forms[0].submit();										 
								return true;    
						}                        
					},                                      
					cancel : {                                  
						text: '<?php echo __("いいえ");?>',                                    
						btnClass: 'btn-default',                                    
						cancel: function(){                                 
							console.log('the user clicked cancel'); 
							scrollText();                              
						}

					}                                   
				},                                  
				theme: 'material',                                  
				animation: 'rotateYR',                                  
				closeAnimation: 'rotateXR'                                  
			});                                                         
		}

	});

	function getMail(form_action,func) {
		$.ajax({
			type:'post',
			url: "<?php echo $this->webroot; ?>BrmBudgetPlan/getMailLists",
			data:{page: 'BrmBudgetPlan', function: func},
			dataType: 'json',
			success: function(data) {
				
				var mailSend = (data.mailSend == '') ? '0' : data.mailSend;
				$("#mailSend").val(mailSend);
				if(mailSend == 1) {	
					$("#mailSubj").val(data.subject);
					$("#mailBody").val(data.body);
					if (data.mailType == 1) {
						//default
						if(data.to != undefined) var toEmail = Object.values(data.to);
						if(data.cc != undefined) var ccEmail = Object.values(data.cc);
						if(data.bcc != undefined) var bccEmail = Object.values(data.bcc);
						
						$('#toEmail').val(toEmail);
						$('#ccEmail').val(ccEmail);
						$('#bccEmail').val(bccEmail);
						loadingPic(); 
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetPlan/"+form_action;
						document.forms[0].method = "POST";
						document.forms[0].submit();
						return true;
					} else {
						//popup
						$("#myPOPModal").addClass("in");
						$("#myPOPModal").css({"display":"block","padding-right":"17px"});
						
						if(data.to != undefined) $('.autoCplTo').show();
						if(data.cc != undefined) $('.autoCplCc').show();
						if(data.bcc != undefined) $('.autoCplBcc').show();

						if(data.to != undefined) level_id = Object.keys(data.to);
						if(data.cc != undefined) cc_level_id = Object.keys(data.cc);
						if(data.bcc != undefined) bcc_level_id = Object.keys(data.bcc);
						
						$(".subject").text(data.subject);
						$(".body").html(data.body);
						console.log( "<?php echo $this->webroot; ?>BrmBudgetPlan/"+form_action);
						/* set form action */	
						$('#budgetresultdiff').attr('action', "<?php echo $this->webroot; ?>BrmBudgetPlan/"+form_action);
					}
				} else {
					document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetPlan/"+form_action;
					document.forms[0].method = "POST";
					document.forms[0].submit();
					return true;
				}
			},
			error: function(e) {
				console.log('Something wrong! Please refresh the page.');
			}
		});		
	}
	
</script>
