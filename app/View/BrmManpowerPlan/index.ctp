<style type="text/css">
	
	th{
		padding: 5px 0px;
	}
	td {
		padding: 0;
		text-align: right;
		height: 20px !important;
	}
	tbody,tbody tr.total_row {
		background-color: #fff;
	}
	#table_2 th.pos-col{
		border-right: none;
	}
	input.freeze {
		pointer-events: none;
		background-color: #F9F9F9 !important;
	}
	#table_2 td.price-col{
		border-left: none;
		color: #fff;
	}
	td.total-pos-col {
		padding: 5px;
		text-align: center;
		background-color: #fff;
		border-top: none;
	}
	td.whole-total-col {
		padding: 5px;
		text-align: center;
		background-color: #fff;
	}
	tfoot tr td {
		padding: 0;
		text-align: left;
		background-color: #fff;
	}
	td.price-col {
		padding: 5px;
		background-color: #fff;
	}
	th.field-col {
		background-color: #fff;
		text-align: center !important;
		border-bottom: none;
		padding: 0 !important;
	}
	td.total_amt {
		background-color: #fff;
	}
	td.pos-name-loan{
		width: 216px !important;
	}
	th.tbl-last{
		width: 235px !important;
	}
	th {
		text-align: center;
	}
	.manpower_table {
		width: 100%;
		table-layout: fixed;
		border-collapse: collapse;
	}
	.readonly, table.tbl_manpower_sub4 > tbody > tr > td:not(:first-child){
		background-color: #F9F9F9;
		opacity: 1;
		text-align:right;

	}
	.disable, .freeze {
		cursor: none;
		pointer-events: none;
		background-color: #F9F9F9;
	}
	.manpower_table tbody th{
		height: 30px !important;
		background-color: #ffffff;
	}
	.manpower_table .amount_input {
		text-align: right;
	    display: block;
		margin: 0;
		/*height: 100%;*/
		width: 100%;
		padding: 5px;
		box-sizing: border-box;
		border: 0px none;;
		outline-width: 0;
		text-align: right;
		background-color: #D5F4FF;
		cursor: auto;
	}

	.manpower_table input.unit,.manpower_table input.adjustment, tr.extraTotal td >input{
		padding: 6px 5px !important;
		border: 0px solid #ccc;
		background-color: rgb(255, 255, 153);
		text-align:right;
	}

	.fl-scrolls{
		z-index: 0 ;
	}
	.text{
		padding-left: 15px;
	}
	.pointer{
		pointer-events: none;
	}

	#load{
	  
		z-index: 1000;
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0,0,0,0.2);
	}

	tr.auto_amount_OT td {
		background-color: #fff !important;
	}

	table tr th, table tr td {
		border: 1px solid #bbb;
	}

	table thead tr th.gap {
		border-color: #fff;
		background-color: #fff;
	}
	.negative {
		color: #f31515;
	}
	tbody th{
		font-weight: normal;
		text-align: left;
		padding-left: 5px;
	}

	.jconfirm-box.jconfirm-type-yellow {
		border-top: solid 7px #F7C600;
	}

	.jconfirm-box.jconfirm-type-yellow > .jconfirm-title-c > .jconfirm-icon-c{
		color: #F7C600 !important;
	}
	input, td{
		font-size: 12px !important;

	}
	#btn_browse{
		padding: 5px 9px;
		color: white; 
		border: none
		
	}
	.tooltip-inner {
	    background: #ffd2d2;
	  	color: #ff3333;
	}
	.tooltip.top .tooltip-arrow {
	    border-top-color: #ffd2d2;
	}
	.tooltip.right .tooltip-arrow {
	    border-right-color: #ffd2d2;
	}
	.adjust{
		padding-bottom: 15px;
	}
	.one{
		width: 150px;
	}
	td:has(> input:disabled){
		background-color : #ffff !important;
	}
	
</style>

<script type="text/javascript">

	document.onreadystatechange = function () {
		var state = document.readyState
		if (state == 'interactive') {
			document.getElementById('contents').style.visibility="hidden";

		} else if (state == 'complete') {
				setTimeout(function(){
				document.getElementById('interactive');
				document.getElementById('load').style.visibility="hidden";
				document.getElementById('contents').style.visibility="visible";
				
			  },1000);
		}
	}

	function excelDownload(year){

		document.getElementById("error").innerHTML = "";
		document.getElementById("success").innerHTML =""; 
		document.forms['0'].action = "<?php echo $this->webroot; ?>BrmManpowerPlan/ManpowerExcelDownload/?year="+year;
		document.forms['0'].method = "POST";
		document.forms['0'].submit();
	  
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

	function loadingPic() {
		
		var ua = window.navigator.userAgent;
		var msie = ua.indexOf("MSIE ");
		
		if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
		
			var el = document.getElementById('imgLoading'); 
			var i  = 0;
			var pics = [   "<?php echo $this->webroot; ?>img/loading1.gif",
						   "<?php echo $this->webroot; ?>img/loading2.gif",
						   "<?php echo $this->webroot; ?>img/loading3.gif",
						   "<?php echo $this->webroot; ?>img/loading4.gif"];
		   
			function toggle() {
				el.src = pics[i];           // set the image
				i = (i + 1) % pics.length;  // update the counter
			}
			setInterval(toggle, 250);
			$("#overlay").show();
			
		}else{
			
			$("#overlay").show();
		}
		
	} 
   
</script>
<?php 
	echo $this->Form->create(false,array('url'=>'','id'=>'manpower','class'=>'','autocomplete'=>'off','enctype'=> 'multipart/form-data')); 
?>
<div id="overlay">
	<span class="loader"></span>
</div>
<input type="hidden" name="hid_new_uprice" id="hid_new_uprice">
<input type="hidden" name="hid_field_rate" id="hid_field_rate">
<input type="hidden" name="btn_type" id="btn_type">
<input type="hidden" id="year" name="year" value="<?php echo $_GET['year'];?>">
<div id="load"></div>
<div id="contents">
</div>

<div class="container register_container">
	<h3 class=""> <?php echo __($_GET['year']."年度 "); ?><?php echo __("年度人員計画"); ?></h3>
	<div class="budget-form-hr">
		<hr>
		<span><?php echo __('水色セル内を入力して下さい'); ?></span>
	</div>
	<br>
	<div class="errorSuccess">
		<div class="success" id="success"><?php echo ($this->Session->check("Message.mp_success"))? $this->Flash->render("mp_success") : ''; ?></div>
		<div class="error" id="error"><?php echo ($this->Session->check("Message.mp_error"))? $this->Flash->render("mp_error") : ''; ?></div>
	</div>
	<div class="col-sm-6">
		<div class="form-group row">
			<label for="budget_term" class="col-sm-4 col-form-label">
				<?php echo __('Budget Term');?>
			</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="txt_budget_term" name="txt_budget_term" value="<?php echo $budget_term; ?>" readonly/>
			</div>
		</div>
		<div class="form-group row">
			<label for="lbl_business_code" class="col-sm-4 col-form-label">
				<?php echo $layer['LayerType']['name_'.$lang_name];?>
			</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="txt_business_code" name="txt_business_code" value = "<?php echo $ba_name_code; ?>" readonly/>
			</div>
		</div>
		<div class="form-group row">
			<label for="deadline_date" class="col-sm-4 col-form-label">
				<?php echo __('提出期日');?>
			</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="deadline_date" name="deadline_date" value="<?php echo  $deadline_date; ?>" readonly="readonly" />
			</div>
		</div>
		
		<input type="hidden" class="form-control" name="filling" id="filling" value="<?php echo $mp_data['filling_date']; ?>"/>
	</div>

	<input type="hidden" name="hidden_approve" id="hidden_approve" value = "<?php echo $approveBA; ?>">
	<?php if($no_data == "no_data"): ?>
		<div class="col-sm-12">
			<p class="no-data"><?php echo __("計算する為のデータがシステムにありません。"); ?></p>
		</div>
	<?php else: ?>
		<div class="form-group row" style="text-align:right;">
			
			<div class="col-sm-12 col-md-12 text-right adjust">
				<div class="">
					<label style="color: white;" id="btn_browse" class = "upload-div btn btn-sumisho">Upload File
						<input type="file" name="manpower_upload" class ="manpower_upload">
					</label>
					<input type="button" name="btn_excel" id="btn_excel" class="btn btn-success btn_sumisho" value="<?php echo __("Excel Download") ?>" onClick = "excelDownload(<?php echo $target_year;?>);">
					<input type="button" name="btn_save" id="btn_save" class="btn btn-success btn_sumisho" value="<?php echo __("Save") ?>">
				</div>		
			</div>
			<!-- <div class="col-sm-12 col-sm-12 text-right adjust">
				<div class="row">
					<label id="btn_browse" class="btn btn-success btn_sumisho one btn_necessary"><?php echo __("一括アップロード"); ?>
						<input type="file" name="btn_upload_bulk_file" class = "uploadbulkfile" data-id = "0" data-id-head = "<?php echo $head_data['head_dept_id']; ?>" id="btn_upload_bulk_file">	        	
					</label> -->
					<!-- Excel Download -->
					<!-- <input type="button" id="btn_bulk_excel_download" name="btn_bulk_excel_download" class="btn btn-success btn_sumisho one" value="<?php echo __("一括ダウンロード"); ?>" >
					<input type="button" name="btn_save" id="btn_save" class="btn btn-success btn_sumisho" value="<?php echo __("Save") ?>">
				</div>		
			</div> -->
		</div>
		<!-- Title -->
		<div class="row text"><h4><?php echo __("人員計画")."（".$target_year.__("年度").")"; ?></h4></div>
		<div class="row" style="overflow: hidden;">
			<!-- Loop and show 4 table -->
			<?php foreach ($mp_data['data'] as $display_no => $manpower): ?>
				<div class="col-lg-12 col-md-12 mb-20">
					<table class="manpower_table" id="table_<?php echo $display_no ?>">
						<thead class="check_period_table">
							<tr>
								<th rowspan="2" class="w-20 no-border gap"></th>
									<th class="no-border gap w-170"></th>
								<?php if ($manpower['header'] == ''): ?>
									<th class="w-80"><?php echo __("単価"); ?></th>
								<?php else: ?>
									<th class="w-80 no-border gap"></th>
								<?php endif ?>
								<th colspan="6"><?php echo $budget_year.__("年度上期"); ?></th>
								<th><?php echo __("上期"); ?></th>
								<th colspan = "6"><?php echo $budget_year.__("年度下期"); ?></th>
								<th style="vertical-align : middle;text-align:center;"><?php echo __("下期"); ?></th>
								<th style="vertical-align : middle;text-align:center;"><?php echo __("年度"); ?></th>
							</tr>
							<tr>
								<?php if ($manpower['header'] == ''): ?>
									<th class="no-border gap w-170"></th>
									<th class = "w-80 month"><?php echo __('円/人月'); ?></th>
								<?php else: ?>
									<th colspan="2" class="no-border gap w-210"><?php echo __($manpower['header']); ?></th>
								<?php endif ?>
								<th class = "w-80 month"><?php echo __($Month_12[0]); ?></th>
								<th class = "w-80 month"><?php echo __($Month_12[1]); ?></th>
								<th class = "w-80 month"><?php echo __($Month_12[2]); ?></th>
								<th class = "w-80 month"><?php echo __($Month_12[3]); ?></th>
								<th class = "w-80 month"><?php echo __($Month_12[4]); ?></th>
								<th class = "w-80 month"><?php echo __($Month_12[5]); ?></th>
								<th class = "w-80 middle_month"><?php echo __("平均"); ?></th>
								<th class = "w-80 month"><?php echo __($Month_12[6]); ?></th>
								<th class = "w-80 month"><?php echo __($Month_12[7]); ?></th>
								<th class = "w-80 month"><?php echo __($Month_12[8]); ?></th>
								<th class = "w-80 month"><?php echo __($Month_12[9]); ?></th>
								<th class = "w-80 month"><?php echo __($Month_12[10]); ?></th>
								<th class = "w-80 month"><?php echo __($Month_12[11]); ?></th>
								<th class = "w-80 end_month"><?php echo __("平均"); ?></th>
								<th class="w-80"><?php echo __("平均"); ?></th>
								
							</tr>
						</thead>
						<tbody>
							<?php foreach ($manpower['table_data'] as $field_name => $field_data): ?>

								<?php foreach ($field_data['sub_data'] as $position_name => $position_data): ?>
									<tr>
										<?php if ($field_name == $position_name): ?>
											<th colspan="2" class="field-col p-5" style="text-align: left;"> <?php echo $field_name; ?></th>
										<?php else: ?>
											<th class="field-col"> <?php echo $field_name; ?></th>

											<?php 
												$pos = $position_name;

												$position_name = ( ($ba_code == '8003/人事部' || $ba_code == '8003') && strpos($position_name, '（新人）') == true) ? str_replace('（新人）', '（新人・他部署）', $pos) : $pos;
											?>

											<th class="pos-col"><?php echo $position_name; ?></th>
										<?php endif ?>
										<?php $name = ($display_no != 3) ? "manpower[".$field_data['field_id']."][".$position_data['position_id']."][unit_salary]" : "manpower_ot[".$position_data['field_id']."][overtime_rate]"; ?>
										<?php if ($position_data['edit_permit'] == true): ?>
											<td class="price-col no-padding">

												<input type="text" class="amount_input unit unit_<?php echo $position_data['position_id'] ?>" name="<?php echo($name) ?>" value = "<?php echo $position_data['unit_price'];?>">
											</td>
										<?php else: ?>
											<td class="price-col"><?php echo $position_data['unit_price']; ?></td>
											<span>
												<input type="hidden" class="amount_input" name="<?php echo($name) ?>" value = "<?php echo $position_data['unit_price'];?>">
											</span>
											
										<?php endif ?>
										<?php $monthCnt =0; $i=0;?>
										<?php foreach ($position_data['monthly_amt'] as $month_col => $month_amt ): ?>
											<?php 
												$monthCnt ++;
												$percent = ($monthCnt < 8) ? $position_data['percentage']['first_half'] : $position_data['percentage']['secnd_half'];
												$month_amt = (!empty($month_amt) || $month_amt != '') ? number_format($month_amt,2) : '0.00';
											?>
											<?php if ($month_col != '1st_half_total' && $month_col != '2nd_half_total' && $month_col != 'sub_total'): ?>
												<td class="<?=$Month_12digit[$i]?>">
												<?php $i++; ?>
												<?php if ($display_no == 3): ?>

													<input class="amount_input <?php echo $month_col; ?>" id="<?php echo($display_no.'-'.$position_data['field_id'].'-0-'.$month_col) ?>" type="text" name="manpower_ot[<?php echo $position_data['field_id'] ?>][monthly_amt][<?php echo $month_col ?>]" value = "<?php echo $month_amt; ?>" data-percent="1">
												<?php else: ?>
													<input class="amount_input <?php echo $month_col; ?>" id="<?php echo($display_no.'-'.$field_data['vir_field_id'].'-'.$position_data['position_id'].'-'.$month_col) ?>" type="text" data-percent='<?php echo($percent) ?>' name="manpower[<?php echo $field_data['field_id'] ?>][<?php echo $position_data['position_id'] ?>][monthly_amt][<?php echo $month_col ?>]" value = "<?php echo $month_amt; ?>">
												<?php endif ?>
												</td>
											<?php else: ?>

												<?php if ($display_no == 3): ?>
													<td class="p-5 total_amt" id="<?php echo($display_no.'-'.$position_data['field_id'].'-0-'.$month_col) ?>"><?php echo $month_amt; ?></td>
												<?php else: ?>
													<td class="p-5 total_amt" id="<?php echo($display_no.'-'.$field_data['vir_field_id'].'-'.$position_data['position_id'].'-'.$month_col) ?>"><?php echo $month_amt; ?></td>
												<?php endif ?>

												
											<?php endif ?>
										<?php endforeach ?>
									</tr>								
								<?php endforeach ?>
									
								<?php if (isset($field_data['sub_total'])):
									foreach ($field_data['sub_total'] as $total_name => $total_value): ?>
										<tr class="total_row">
											
											<td colspan="3" class="total-pos-col"> <?php echo $total_name; ?></td>
											<?php foreach ($total_value as $tmonth_col => $tmonth_amt ): ?>
												<?php 
													$tmonth_amt = (!empty($tmonth_amt) || $tmonth_amt != '') ? number_format($tmonth_amt,2) : '0.00';

													$extra_class = ($total_name == '管理職（含役員）小計') ? $display_no.'-'.$manpower['table_data'][$field_name_first]['field_id'].'-'.$tmonth_col : '';

												?>

												<td class="p-5" id="<?php echo($display_no.'-'.$field_data['field_id'].'-'.$tmonth_col) ?>"><?php echo $tmonth_amt; ?></td>
												<span>
													<input class="hidden-input" id="hin-<?php echo($display_no.'-'.$field_data['field_id'].'-'.$tmonth_col) ?>" type="hidden" name="manpower_subtot[<?php echo $total_name; ?>][<?php echo $tmonth_col; ?>]" value="<?php echo $tmonth_amt; ?>">
												</span>
												
											<?php endforeach ?>
										</tr>
									<?php endforeach ?>
								<?php endif ?>

							<?php endforeach ?>
							<?php foreach ($manpower['table_total'] as $tname => $tvalue): ?>
									<tr class="total_row">

										<td colspan="3" class="total-pos-col"> <?php echo $tname; ?></td>
										<?php foreach ($tvalue as $tmonth => $tamt ): ?>
											<?php 
												/*$tamt = (!empty($tamt) || $tamt != '') ? number_format($tamt,2) : '0.00';*/
												$id = (strpos($tname, '金額') == true) ? 'amount-'.$display_no.'-'.$tmonth : 'person-'.$display_no.'-'.$tmonth;
												$id = (strpos($tname, '手入力') == true) ? 'manualamt-'.$display_no.'-'.$tmonth : $id;
												$id = ($tname == '合計（A+B）') ? 'amount-tot-'.$display_no.'-'.$tmonth : $id;
											?>
											<?php if (($display_no == 3 || $display_no == 4) && (strpos($tname, '手入力') == true) && $tmonth != '1st_half_total' && $tmonth != '2nd_half_total' && $tmonth != 'sub_total'): 
												$tamt = (!empty($tamt) || $tamt != '') ? number_format($tamt) : '0';
											?>

												<td>
													<input type="text" class="amount_input adjustment" name="adjustment[<?php echo($display_no) ?>][adjust][<?php echo($tmonth) ?>]" id="<?php echo($id); ?>" value = "<?php echo $tamt;?>">

												</td>

											<?php else: 
												if((strpos($tname, '金額（小計）') == true)){

													$tamt = (!empty($tamt) || $tamt != '') ? number_format($tamt) : '0';

												}elseif((strpos($tname, '（A+B）') == true)){

													$tamt = (!empty($tamt) || $tamt != '') ? number_format($tamt) : '0';

												}elseif((strpos($tname, '金額（手入力）') == true)){

													$tamt = (!empty($tamt) || $tamt != '') ? number_format($tamt) : '0';

												}else{

													$tamt = (!empty($tamt) || $tamt != '') ? number_format($tamt,2) : '0.00';
												}
											?>

												<td class="p-5" id="<?php echo($id) ?>"><?php echo $tamt; ?></td>
												<?php if ($display_no == 4 && (strpos($tname, '手入力') == false) && (strpos($tname, '金額（小計）') == false)): ?>
													<span>
														<input type="hidden" class="hidden-input" id="hin-<?php echo($id) ?>" name="manpower_subtot[<?php echo $tname; ?>][<?php echo $tmonth; ?>]" value="<?php echo $tamt; ?>">
													</span>
												
												<?php endif ?>
											<?php endif ?>
										<?php endforeach ?>
									</tr>
							<?php endforeach ?>
							<!-- total salary row (wla) -->
							<?php foreach ($manpower['table_total_salary'] as $tname => $tvalue): ?>
									<tr class="total_salary_row">

										<td colspan="3" class="total-pos-col"><?php echo __("社員金額合計（単位 千円）"); ?></td>
										<?php foreach ($tvalue as $tmonth => $tamt ): ?>
												<td id=<?= 'salary-'.$display_no.'-'.$tmonth ?> class="p-5">
													<?php echo number_format($tamt,1); ?>
												</td>	
										<?php endforeach ?>
									</tr>
							<?php endforeach ?>
							<!--end total salary row (wla) -->
						</tbody>
						<tfoot>
							<?php foreach ($manpower['footer'] as $footer): ?>
								<tr>
									<td colspan="18" class="no-border <?php echo $footer['text_color'] ?>"><?php echo $footer['text'] ?></td>
								</tr>
							<?php endforeach ?>
						</tfoot>
					</table>
				</div>
			<?php endforeach ?>

			<!-- For total table -->
			<div class="col-lg-12 col-md-12 mb-40">
				<table class="manpower_table total_table">
					<thead class="check_period_table">
						<tr>
							<th rowspan="2" class="w-20 no-border gap"></th>
							<th class="no-border gap w-170"></th>
							<th class="w-80 no-border gap"></th>
							<th colspan="6" class="no-border gap"></th>
							<th class="no-border gap"></th>
							<th colspan = "6" class="no-border gap"></th>
							<th class="no-border gap"></th>
							<th class="no-border gap"></th>
						</tr>
						<tr>

							<th colspan="2" class="no-border gap w-210"><?php echo __("人件費合計 （単位 千円）"); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[0]); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[1]); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[2]); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[3]); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[4]); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[5]); ?></th>
							<th class = "w-80 middle_month"><?php echo __("上期").__("平均"); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[6]); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[7]); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[8]); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[9]); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[10]); ?></th>
							<th class = "w-80 month"><?php echo __($Month_12[11]); ?></th>
							<th class = "w-80 end_month"><?php echo __("下期").__("平均"); ?></th>
							<th class="w-80"><?php echo __("年度").__("平均"); ?></th>
							
						</tr>
					</thead>
					<tbody>
						<?php foreach ($mp_data['total'] as $tname => $tvalue): ?>
							<?php if ($tname != '社員人件費（手入力）' && $tname != '社員人件費（合計）'): ?>
								<?php $tname_title = ($tname=='派遣社員人件費合計') ? $tname.'（C＋D）' : $tname ?>
								
								<tr><td colspan="18" style="border: none;"></td></tr>
								<tr class="total_row">
									<td colspan="3" class="whole-total-col"> <?php echo $tname_title; ?></td>
									<?php foreach ($tvalue['monthly_amt'] as $tmonth => $tamt ): ?>
										<?php 
											$tamt_text = (!empty($tamt) || $tamt != '') ? number_format($tamt/1000,1) : '0.0'; 
											$negative = ($tamt_text < 0) ? 'negative' : '';
										?>
										<td class="p-5 <?php echo($negative) ?>" id="<?php echo('text_'.$tname.'_'.$tmonth) ?>"><?php echo $tamt_text; ?></td>
										
										<!-- hidden amt -->
										<td style = 'display: none;' class="p-5 <?php echo($mp_data['freeze'][$tmonth]) ?> <?php echo($negative) ?>" id="<?php echo($tname."_".$tmonth) ?>"><?php echo (!empty($tamt) || $tamt != '') ? number_format($tamt/1000,3) : '0.00'; ?></td>

										<?php if ($tname != '社員人件費（小計）'): ?>
											<span>
												<input type="hidden" name="manpower_total[<?php echo $accounts[$tname] ?>][<?php echo $tmonth ?>]" id="hin-<?php echo($tname."_".$tmonth) ?>" value="<?php echo $tamt_text ?>">
											</span>
										<?php endif ?>
									<?php endforeach ?>
								</tr>
								<?php if ($tname == '社員人件費（小計）'): ?>
									<tr class="total_row">
										<td colspan="3" class="whole-total-col"> <?php echo '社員人件費（手入力）'; ?></td>
										<?php foreach ($mp_data['total']['社員人件費（手入力）']['monthly_amt'] as $tmonth => $tamt ): ?>
											<?php 
												$tamt_text = (!empty($tamt) || $tamt != '') ? number_format($tamt/1000,1) : '0.0'; 
												$negative = ($tamt_text < 0) ? 'negative' : '';
											?>
											<?php if ($tmonth != '1st_half_total' && $tmonth != '2nd_half_total' && $tmonth != 'sub_total'): ?>
												<td>
													<input type="text" class="amount_input adjustment total <?php echo($mp_data['freeze'][$tmonth]) ?> <?php echo($negative) ?> pointIn"  id="<?php echo("manualamt-0-".$tmonth)?>" value = "<?php echo $tamt_text;?>">

													<!-- hidden amt -->
													<input type = "hidden" class="amount_input adjustment total <?php echo($mp_data['freeze'][$tmonth]) ?> <?php echo($negative) ?> pointHidden" id="<?php echo("manualamt-0-".$tmonth."hidden") ?>" name="adjustment[0][adjust][<?php echo($tmonth) ?>]" value = "<?php echo (!empty($tamt) || $tamt != '') ? number_format($tamt/1000,3) : '0.00'; ?>">
													
												</td>
											<?php else: ?>
												<td class="p-5 <?php echo($negative) ?>" id="<?php echo("manualamt-0-".$tmonth) ?>">
													<?php echo $tamt_text;?>
												</td>
											<?php endif ?>
										<?php endforeach ?>
									</tr>
									<tr class="total_row">
										<td colspan="3" class="whole-total-col"> <?php echo '社員人件費（合計）'; ?></td>
										<?php foreach ($mp_data['total']['社員人件費（合計）']['monthly_amt'] as $tmonth => $tamt ): ?>
											<?php 
												$tamt_text = (!empty($tamt) || $tamt != '') ? number_format($tamt/1000,1) : '0.0';

												$negative = ($tamt_text < 0) ? 'negative' : '';
											?>
											<td class="p-5 <?php echo($negative) ?>" id="<?php echo("text_社員人件費（合計）_".$tmonth) ?>"><?php echo $tamt_text; ?></td>
											<!-- hidden amt -->
											<td style="display: none;" class="p-5 <?php echo($mp_data['freeze'][$tmonth]) ?> <?php echo($negative) ?>" id="<?php echo("社員人件費（合計）_".$tmonth) ?>"><?php echo (!empty($tamt) || $tamt != '') ? number_format($tamt/1000,3) : '0.00'; ?></td>
											<span>
												<input type="hidden" name="manpower_total[<?php echo $accounts['社員人件費（合計）'] ?>][<?php echo $tmonth ?>]" id="hin-<?php echo("社員人件費（合計）_".$tmonth) ?>" value="<?php echo $tamt_text ?>">
											</span>
										<?php endforeach ?>
									</tr>
								<?php endif ?>
							<?php endif ?>
							
						<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
		
	<?php endif ?>
	
	<input type="hidden" id = "hidden_term_id" name = "hidden_term_id" value = "<?php echo $term_id; ?>">
	<input type="hidden" id = "hidden_head_dept_id" name = "hidden_head_dept_id" value = "<?php echo $head_dept_id; ?>">
	<input type ="hidden" id = "hidden_ba_code" name = "hidden_ba_code" value = "<?php echo $ba_code; ?>">
	<input type ="hidden" id = "hidden_file_name" name = "hidden_file_name" >

</div>
<br>
<br>
<?php echo $this->Form->end(); ?>

<script type="text/javascript">

	$(document).ready(function(){
		var rspan = 1;
		var cspan = 1;
		var prevTD = "";
		var prevTDVal = "";
		var firstSave = false;
		//khin (change unit price)
		var position_list = [];var position_lists = [];var field_lists = [];
		var check_salary = <?php echo json_encode($compare_unit_price); ?>;
		var appBA = <?php echo json_encode($approveBA); ?>;
		var btn_cache = <?php echo json_encode($btn_cache); ?>;
		var btn_cache_resave = <?php echo json_encode($btn_cache_resave); ?>;
		var term_id = <?php echo json_encode($term_id); ?>;
		var target_year = <?php echo json_encode($target_year); ?>;
		var head_dept_id = <?php echo json_encode($head_dept_id); ?>;
		var ba_code = <?php echo json_encode($ba_code); ?>;
		var remove_check = term_id+'_'+target_year+'_'+head_dept_id+'_'+ba_code;
		
		if(Object.keys(check_salary).length > 0 && appBA == 0 && btn_cache != 'Save'){
			remove_salary = <?php echo json_encode($compare_unit_price); ?>;
			var rem_list = JSON.parse(localStorage.getItem(remove_check));
			if(rem_list != undefined || rem_list != null) {
				for (let [key, value] of Object.entries(check_salary)) {
					for (let [keys, values] of Object.entries(rem_list)) {
						if(key == keys && value == values) {
							delete check_salary[key];
						}
					}
				}
			}
			for (let [key, value] of Object.entries(check_salary)) {
			  	var position_name = key.split('_')[3];
			  	var position_id = key.split('_')[2];
			  	var old_unit = value.split('_')[0];
			  	var new_unit = value.split('_')[1];
			  	// var last_arr = 'Unit Price of '+position_name+' changes from '+old_unit+' to '+new_unit;
			  	var last_arr = errMsg(commonMsg.JSE077, [position_name, old_unit, new_unit]);
			  	position_list.push(last_arr);
			  	var f_name = key.split('_')[1];
			  	var f_id = key.split('_')[0];
			  	if(f_name == position_name && f_id == position_id){
			  		field_lists.push(key+'_'+new_unit);
			  	}else{
			  		position_lists.push(key+'_'+new_unit);
			  	}
			  	
			}
			
			$("#hid_new_uprice").val(position_lists.join('/'));
			$("#hid_field_rate").val(field_lists.join('/'));
			var pos_name_list = position_list.join('</br>');
			if(pos_name_list){
				$.confirm({           
				title: '<?php echo __("単価が変更されました！"); ?>',                 
				icon: 'fas fa-exclamation-circle',                  
				type: 'yellow',                  
				typeAnimated: true, 
				closeIcon: true,
				columnClass: 'medium',                
				animateFromElement: true,                 
				animation: 'top',                 
				draggable: false,                 
				content: pos_name_list+'</br>'+errMsg(commonMsg.JSE078),    
				buttons: {                    
					ok: {                 
						text: '<?php echo __("はい");?>',                 
						btnClass: 'btn-info',                 
						action: function(){ 
							loadingPic();               
							document.forms[0].action = "<?php echo $this->webroot; ?>BrmManpowerPlan/saveManpower";
							document.forms[0].method = "POST";
							document.forms[0].data = JSON.stringify(check_salary);
							document.forms[0].submit();    
														
							return true;
						}                 
					},                      
					cancel : {
						text: '<?php echo __("いいえ");?>',                  
						btnClass: 'btn-default',                    
						action: function(){
							localStorage.setItem(remove_check, JSON.stringify(remove_salary));
							scrollText();
						}

					},                
				},                  
				theme: 'material',                  
				animation: 'rotateYR',                  
				closeAnimation: 'rotateXR'                  
			});
			}	
        }else if(btn_cache_resave == 'ReSave'){
    		$('#btn_type').val('Save');
    		loadingPic();                   
			document.forms[0].action = "<?php echo $this->webroot; ?>BrmManpowerPlan/saveManpower";
			document.forms[0].method = "POST";
			document.forms[0].submit(); 
			return true;
        }
		//Rowspan for same data, PanEiPhyo(20200923)
		$(".manpower_table tbody tr th:first-child").each(function() { //for each first td in every tr
			var $this = $(this);
			if ($this.text() == prevTDVal) { // check value of previous td text
				rspan++;
				if (prevTD != "") {
					prevTD.attr("rowspan", rspan); // add attribute to previous td
					prevTD.removeClass("oneP");
					$this.remove(); // remove current td
				}
			} else {
				prevTD	= $this; // store current td 
				prevTDVal  = $this.text();
				prevTD.addClass("oneP");
				rspan	= 1;
			}
		});
		//fixed column and header at top of screen
		$("#table_1").freezeHeader();
		$("#table_2").freezeHeader();
		$("#table_3").freezeHeader();
		$("#table_4").freezeHeader();
		
		var approveBA = $('#hidden_approve').val();
		
		if(approveBA == '1'){
			/*If ba_code is approved, disable all input*/
			$("input[type=text]").prop('disabled',true);
			$("#btn_save").prop('disabled', true);
			$(".uploadbulkfile").prop('disabled', true);
			$(".manpower_upload").prop('disabled', true);
			$("input#btn_excel").prop('disabled', false);
			$("input#btn_bulk_excel_download").prop('disabled', false);
			// $("label#btn_browse,.uploadbulkfile,#btn_save").css('background-color','#D5EADD !important');
			// $("label#btn_browse,.uploadbulkfile").css('color','#fff !important');
			$("label#btn_browse,.uploadbulkfile").css({"opacity": 0.5, "cursor": "not-allowed", "background-color": "#D5EADD", "color": "#000"});
			
		}

		$('.datepicker').datepicker({
			format: 'yyyy/mm/dd',
		});
		var previosValue = 0;
		
		/*Nu Nu Lwin 20210407 if unit salary is null, show error msg */
		$('table.manpower_table:not(.total_table) .amount_input').on('keyup',function(event){
			
			CheckUnitSalary($(this));
			$(this).trigger('change');
			$(this).data('val', $(this).val());
			
		});
		$('table.manpower_table.total_table .amount_input').on('keyup',function(event){
			
			$(this).trigger('change');
			previosValue = $(this).val();
			CheckUnitSalary($(this));
			
		});

		// When place cursor in input field
		$('.manpower_table').on('focus','.amount_input',function(event){
			$(this).tooltip("destroy");
			$(this).css({"border": "0px"});
			var retVal = "";
			$(this).val(function(index,value){
				if(value != 0){
					//Remove zero after decimal
					retVal = value.replace(/\.0+$/,'');

					//Remove comma from value
					retVal = retVal.replace(/,/g, "");
				}

				return retVal;
			});

		});
		
		// When insert the value to input field
		$(".amount_input").on('input', function(e) { 
			
			if ($(this).closest('table').attr('id') === undefined) {

				$(this).val($(this).val().replace(/[^0-9\.\-]/g, '')); 
				
			}else{

				if(($(this).hasClass('unit')) || ($(this).hasClass('adjustment'))){
					$(this).val($(this).val().replace(/[^0-9\-]/g, '')); 
				}else{
					$(this).val($(this).val().replace(/[^0-9\.\-]/g, '')); 
				}
			}

			if($(this).hasClass('pointIn')){

				var pointInId =  $(this).attr('id');
				var decimalVal = parseFloat($(this).val().replace(',', ''));
				decimalVal = (decimalVal == '' || isNaN(decimalVal))? '0': decimalVal;
				$('input#'+pointInId+'hidden.pointHidden').val(decimalVal);
			}
		}); 

		// when focus out, set number to format number eg : 1,000.000 and check negative value
		$('.amount_input').on('focusout', function(event){
			// in IE, if value is empty when change, change function not work and so, call change function in focusout
			var ua = window.navigator.userAgent;
			var msie = ua.indexOf("MSIE ");
			
			if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)){
				if($(this).val() == '') {
					$(this).trigger('change');
				}
			}

			if ($(this).closest('table').attr('id') === undefined) {

				$(this).val(function(index,value){
					value = value.replace(/,/g, "");
		            if(value == "" || value == "-"){
						$(this).removeClass('negative');
						value=0;
					}else{
						return formatNumber(value,2);
					}
					return formatNumber(value,2);
					
				});
			}else{
				if(($(this).hasClass('unit')) || ($(this).hasClass('adjustment'))){
					
					$(this).val(function(index,value){
						value = value.replace(/,/g, "");
			            if(value == "" || value == "-"){
							$(this).removeClass('negative');
							value=0;
						}else{
							return roundFormatNumber(value);
						}
						return roundFormatNumber(value);
						
					});
				}else{
					
					$(this).val(function(index,value){
						value = value.replace(/,/g, "");
			            if(value == "" || value == "-"){
							$(this).removeClass('negative');
							value=0;
						}else{
							return formatNumber(value,2);
						}
						return formatNumber(value,2);
						
					});
				}
			}
			//for saveWholeNum. eg: type = 1.56 , show pointIn = 1.6 and pointHidden = 1.56
			if($(this).hasClass('pointIn')){

				var pointInId =  $(this).attr('id');
				var decimalVal = $('input#'+pointInId+'hidden.pointHidden').val();
				
				decimalVal = decimalVal.replace(/,/g, '');

				var tamt = (decimalVal != '0.00' || decimalVal != '') ? (Math.round(decimalVal * 10) / 10) : '0.0'; 
			
				$(this).val(formatNumber(tamt,1));

			}
			
		});

	    $('.amount_input').on('paste', function(e){
			
			var $this = $(this);
			var tableId = $(this).closest('table').attr('id');
			if (window.clipboardData && window.clipboardData.getData) { // IE

				v = window.clipboardData.getData('Text');
						
				var rowspan = $this.closest('table tbody tr').find("th:first").attr("rowspan");
				var yellow_index = $this.closest('table tbody tr').find("td:first").text();
				var show_1_point = '';	

				if(rowspan == undefined){
					if(yellow_index.search('手入力') != -1){
						var x = $this.closest('td').index()+2;
						//for saveWholeNum 
						show_1_point =(yellow_index.search('社員人件費（手入力') != -1)? '1': '2';
					}else{
						var x = $this.closest('td').index()+1;
					}
					var	y = $this.closest('tr').index();
				}else{
					var x = $this.closest('td').index(),
						y = $this.closest('tr').index();
				}
				
				text = v;
				$.each(text.split('\r\n'), function(i2, v2){

					var firstVal = 0;
					if(v2){
						$.each(v2.split('\t'), function(i3, v3){

							firstVal++;
							var row = y+i2, col = (x+i3)-2;

							v3 = v3.replace(/,/g, ""); //remove comma
						  	if(!v3){ //if blank value
								var v3_Fixed1 = '0.0';
								var v3_Fixed2 = '0.0';
								var v3_Fixed0 = '0';
							}else{
								
								var v3_Fixed1 = parseFloat(v3).toFixed(1);
								v3_Fixed1 = v3_Fixed1.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
								
								var v3_Fixed2 = parseFloat(v3).toFixed(2);
								v3_Fixed2 = v3_Fixed2.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

								var v3_Fixed0 = parseFloat(v3).toFixed(0);
								v3_Fixed0 = v3_Fixed0.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
								

							}

	                        $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').data('val', $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val());

							 if(show_1_point == '1'){
	                        	
								if(firstVal == '1'){
	                        		$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').val(v3_Fixed2).trigger('change');
	                        	
	                        	}else{
	                        	
									$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').val(v3_Fixed1).trigger('change');
	                        	}

								$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled]).pointHidden').val(v3_Fixed2);
								
	                        }else if(show_1_point == '2'){

	                        	$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').val(v3_Fixed0).trigger('change');

	                        }else{
								var oneP = $this.closest('table tbody tr').find("th:first").attr("class");
								if(oneP.indexOf("oneP") != -1) col = col - 1;
	                        	$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').val(v3_Fixed2).trigger('change');
	                        }
	                        fValue = v3;
							inputNotHidden = $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])');

							if(inputNotHidden.length) {
								inputNotHidden.focus();
								inputNotHidden.val(fValue);
							
							}		   

						});	
					}
												
				});
				
				return false;           

			}else{
				//other browser
				$.each(e.originalEvent.clipboardData.items, function(i, v){
					if (v.type === 'text/plain'){

						v.getAsString(function(text){
						
							var rowspan = $this.closest('table tbody tr').find("th:first").attr("rowspan");
							var yellow_index = $this.closest('table tbody tr').find("td:first").text();
							var show_1_point = '';	
							if( rowspan == undefined){
								
								if(yellow_index.search('手入力') != -1){

									var x = $this.closest('td').index()+2;
									//for saveWholeNum 
									show_1_point =(yellow_index.search('社員人件費（手入力') != -1)? '1': '2';
									
									
								}else{
									var x = $this.closest('td').index()+1;
								}

								var	y = $this.closest('tr').index();
								
							}else{
								var x = $this.closest('td').index(),
									y = $this.closest('tr').index();
							}
							
							$.each(text.split('\n'), function(i2, v2){
								var firstVal = 0;
								if(v2){
									$.each(v2.split('\t'), function(i3, v3){
										firstVal++;
										var row = y+i2, col = (x+i3)-2;
										
										v3 = v3.replace(/,/g, "");
										
				                        if(!v3){ //if blank value
											var v3_Fixed1 = '0.0';
											var v3_Fixed2 = '0.0';
											var v3_Fixed0 = '0';
										}else{
											
											var v3_Fixed1 = parseFloat(v3).toFixed(1);
											v3_Fixed1 = v3_Fixed1.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
											
											var v3_Fixed2 = parseFloat(v3).toFixed(2);
											v3_Fixed2 = v3_Fixed2.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

											var v3_Fixed0 = parseFloat(v3).toFixed(0);
											v3_Fixed0 = v3_Fixed0.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
											

										}

				                        $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').data('val', $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val());

				                        var thisVal = $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').data('val', $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').id);

				                        if(CheckUnitSalary(thisVal) == false){

				                        	return false;
				                        }
										
				                        if(show_1_point == '1'){
				                        	$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').focusin();
				                        	if(firstVal == '1'){
												
				                        		$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').val(v3_Fixed2).trigger('change');
												//$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').blur();
												
											}else{
				                        	
												$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').val(v3_Fixed1).trigger('change');
				                        	}

											$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled]).pointHidden').val(v3_Fixed2);
											
				                        }else if(show_1_point == '2'){
				                        	$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').focusin();
				                        	$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').val(v3_Fixed0).trigger('change');

				                        }else{
											var oneP = $this.closest('table tbody tr').find("th:first").attr("class");
											if(oneP.indexOf("oneP") != -1)col = col - 1;
											$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').focusin();
				                        	$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])').val(v3_Fixed2).trigger('change');
				                        }
										
										fValue = v3;
										inputNotHidden = $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input.amount_input:not([disabled])');
										
										if(inputNotHidden.length) {
											inputNotHidden.focus();
											inputNotHidden.val(fValue);
										
										}		                         
									});	
								}					
									
							});

						});
					}
				});
				
				return false;       
			}
			
			
		});
		
	   //Added by PanEiPhyo (20200925)
		$('.amount_input').on('focusin', function(){

			$(this).data('val', $(this).val()); //Save current value before change
			
			//for saveWholeNum
			if($(this).hasClass('pointIn')){

				var pointInId =  $(this).attr('id');
				var pointHiddenval = ($('input#'+pointInId+'hidden.pointHidden').val() == '0.00')? $(this).val() :$('input#'+pointInId+'hidden.pointHidden').val();
				
				$(this).val(pointHiddenval);
				$('input#'+pointInId+'hidden.pointHidden').data('val', pointHiddenval); //Save current value before change
				previosValue = pointHiddenval;

			}
		});

		//Added by PanEiPhyo (20200925)
		$('.amount_input').on('change', function(){
			
			var id 	= $(this).prop('id'); //DisplayNo_FieldID_PositionID_Month
			if($('#'+id).hasClass('pointIn')){
				var decimalOnly =/^\s*-?(\d{0,7})(\.\d{0,3})?\s*$/;	

			}else{

				var decimalOnly =/^\s*-?(\d{0,7})(\.\d{0,2})?\s*$/;	
			}
			
			if(decimalOnly.test($(this).val().replace(/,/g, "")) == false) { //Show error 
				
			    scrollText();
				$('#success').empty();
				if($('#'+id).hasClass('pointIn')){

                	$("#error").html(errMsg(commonMsg.JSE061)).show();
                	$('input#'+id+'hidden.pointHidden').val('0.00');
				}else{

                	$("#error").html(errMsg(commonMsg.JSE056)).show();
				}
			    $(this).val('0.00');
			    $(this).trigger('change');

            }else{
				
            	MakeNegative($(this));
				var unitPrice = 0;
				var idArrAmt = [];
				var idArrPrc = [];
				var total_name = [];
				var half_month = '';
				var first_6_month = ['month_1_amt','month_2_amt','month_3_amt','month_4_amt','month_5_amt','month_6_amt'];

				var id 	= $(this).prop('id'); //DisplayNo_FieldID_PositionID_Month
				var arr = id.split("-"); //split into array

				if (!($('#'+id).hasClass('adjustment'))) {
				
					var disno 	= arr[0]; //get display (table) number
					var fieldId = arr[1]; //get field ID
					var positID = arr[2]; //get position ID
					var month_col = arr[3]; //get column name
					var percent = parseFloat($(this).attr('data-percent'));
					
					//get unit salary (or) unit OT rate
					var priceTag = $(this).closest('tr').find('.price-col').html();
					
					if($(priceTag+' input').val()) {
						
						unitPrice = $('.unit_'+positID).val();
					
					} else {
						unitPrice = priceTag;
					}

					//when copy rows are more than ui row.
					if(unitPrice != undefined ){
						unitPrice = unitPrice.replace(/,/g, "");				
					}
					
					unitPrice = parseFloat(unitPrice);
					var prev = ($(this).data('val')!='') ? parseFloat($(this).data('val').replace(/,/g, "")) : 0; //get previous value that was saved in 'focusin' state
					prev = isNaN(prev)? 0 : prev;
					var current = ($(this).val()=='' || $(this).val()== '-') ? 0 : parseFloat($(this).val().replace(/,/g, "")); //get current value
					
					var amtDiff = (current*unitPrice*percent) - (prev*unitPrice*percent); //get amount difference 
					amtDiff = (disno == 2) ? amtDiff*(-1) : amtDiff;
					var pcnDiff = current - prev; //get person count difference
					
					if (disno == 4) {
						total_name = ['派遣社員人件費合計','社員＋派遣人件費合計'];
					} else {
						total_name = ['社員人件費（合計）','社員人件費（小計）','社員＋派遣人件費合計'];

					}

					if(first_6_month.indexOf(month_col) !== -1){
						half_month = '1st_half_total';
					} else {
						half_month = '2nd_half_total';
					}

					CalculateAmount(disno+'-'+fieldId+'-'+month_col,pcnDiff,1);//1-2-month_1_amt
					CalculateAmount('person-'+disno+'-'+month_col,pcnDiff,1);//person-1-month_1_amt
					CalculateAmount('amount-'+disno+'-'+month_col,amtDiff,1);//amount_-month_1_amt
					CalculateAmount('amount-tot-'+disno+'-'+month_col,amtDiff,1);//amount_-month_1_amt
					
					CalculateTotal(disno+'-'+fieldId+'-'+positID+'-'+half_month,pcnDiff,half_month);//1-2-1-1st_half_total
					
					CalculateTotal(disno+'-'+fieldId+'-'+half_month,pcnDiff,half_month);//1-2-1st_half_total
					CalculateTotal('person-'+disno+'-'+half_month,pcnDiff,half_month);//person-1-1st_half_total
					CalculateTotal('amount-'+disno+'-'+half_month,amtDiff,half_month);//person-1-1st_half_total
					CalculateTotal('amount-tot-'+disno+'-'+half_month,amtDiff,half_month);//person-1-1st_half_total

					//if not ot, calculation for total salary (wla)
					if(disno != 3){
						CalculateAmount('salary-1-'+month_col,amtDiff/1000,1);
						CalculateTotal('salary-1-'+half_month,amtDiff/1000,half_month);
					}
					
				} else {

					var prefix = arr[0];
					var disno 	= arr[1]; //get display (table) number
					var month_col = arr[2]; //get column name
					
					if($('#'+id).hasClass('pointIn')){
						var prev;
			
						var previous = (previosValue!="" && previosValue != undefined) ? parseFloat(previosValue.replace(/,/g,"")) : 0; //get previous value that was saved in 'focusin' state
						if(isNaN(previous)){
							prev = 0;
						}
						else{
							prev = (previosValue!="" && previosValue != undefined) ? parseFloat(previosValue.replace(/,/g,"")) : 0; //get previous value that was saved in 'focusin' state
						}
						
					}else{

						var prev = ($(this).data('val')!='') ? parseFloat($(this).data('val').replace(/,/g, "")) : 0; //get previous value that was saved in 'focusin' state **remove .replace(/,/g, "")
						
					}

					prev = isNaN(prev)? 0 : prev;
					
					var current = ($(this).val()=='' || $(this).val()== '-') ? 0 : parseFloat($(this).val().replace(/,/g, "")); //get current value
					
					var amtDiff = current - prev; 
				
					if (disno == 4) {
						total_name = ['派遣社員人件費合計','社員＋派遣人件費合計'];
					} else if (disno == 0) {
						total_name = ['社員人件費（合計）','社員＋派遣人件費合計'];
						setTimeout(function(){ 
							CalculateAmount('hin-俸給諸給与_'+month_col,amtDiff,1);
							CalculateTotal('hin-俸給諸給与_'+half_month,amtDiff,half_month);
						}, 100);
						amtDiff = amtDiff * 1000;
					} else {
						total_name = ['社員人件費（合計）','社員人件費（小計）','社員＋派遣人件費合計'];

					}

					if(first_6_month.indexOf(month_col) !== -1){
						half_month = '1st_half_total';
					} else {
						half_month = '2nd_half_total';
					}

					if (disno != 0) {
						CalculateAmount('amount-tot-'+disno+'-'+month_col,amtDiff,1);
						CalculateTotal('amount-tot-'+disno+'-'+half_month,amtDiff,half_month);
					}

					CalculateTotal(prefix+'-'+disno+'-'+half_month,amtDiff,half_month);				
					CalculateTotal('hin-'+disno+'-'+half_month,amtDiff,half_month);
				}

				$.each(total_name, function(index, value) {
					var id = '#'+value+'_'+month_col;

					if ($(id).length && $(id).hasClass('freeze')==false) {
						
						CalculateAmount(value+'_'+month_col,amtDiff/1000,1);
						CalculateTotal(value+'_'+half_month,amtDiff/1000,half_month);
					}

				});
				
            }

		});
	
		
		//Save data
	   	$("#btn_save").click(function(){
			
			$('#success').empty();
			$('#error').empty();
			var manpowerChk = true;
			$('#btn_type').val('Save');
			if(manpowerChk) { 
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
								document.forms[0].action = "<?php echo $this->webroot; ?>BrmManpowerPlan/saveManpower";
								document.forms[0].method = "POST";
								document.forms[0].submit();    
															
								return true;
							}                 
						},                      
						cancel : {
							text: '<?php echo __("いいえ");?>',                  
							btnClass: 'btn-default',                  
							cancel: function(){                 
								scrollText();                
							}

						},                
					},                  
					theme: 'material',                  
					animation: 'rotateYR',                  
					closeAnimation: 'rotateXR'                  
				});
			}

		});

		$('.amount_input, td').each(function() {
			MakeNegative($(this));
		});

		//added by HHK
		if($('.amount_input').attr('disabled'))
			$('.amount_input').parent().css('background-color', '#F9F9F9');
		else
			$('.amount_input').parent().css('background-color', '#D5F4FF');
			$('.adjustment, .unit').parent().css('background-color', 'rgb(255, 255, 153)');
		//ba_code of from_date donot start,column of months will be disable
		// ayezarnikyaw(20201023)
	    var from_ba_date = "<?php echo $from_ba_date; ?>";
	    var ba_date = from_ba_date.split('-');
	    	ba_months = ba_date[1];

	    var t_year = "<?php echo $this->Session->read('TARG_YEAR'); ?>";
	    var month_12 = <?php echo json_encode($Month_12digit); ?>;

	    var new_arr = [];
	    var i;	    
		for (i = 0; i < month_12.length; i++) {
			new_arr.push(month_12[i]);
		  if (month_12[i] == 12) { break; }
	 
		}

		$.each(new_arr , function(index, val) { 
		  if(ba_months > val && t_year== ba_date[0]){
		  	value = "." + val + " input";

		  	 $(value).prop('disabled', true);
		  	 /*mozilla for td*/
		  	 $('td.'+val).css('background-color', '#F9F9F9');		  	 
		  }
		});

	  
	});

	function CalculateAmount(idName,difference,divider) {
		if(idName.indexOf("hin-俸給諸給与") !== -1){
			idName.replace('hin-俸給諸給与','社員人件費（合計）');
		}
		
		if ($('#'+idName).length) {
			var oldVal = 0;
			if ($('#'+idName).hasClass('amount_input') || $('#'+idName).hasClass('total_input')) {
				oldVal = $('#'+idName).val().replace(/,/g, "");
			} else {
				oldVal = $('#'+idName).text().replace(/,/g, "");
			}

			//get old idName and replace comma
			oldVal = parseFloat(oldVal);
			
			var calVal = oldVal + (difference/divider);
			
			if ($('#'+idName).hasClass('amount_input') || $('#'+idName).hasClass('total_input')) {
				$('#'+idName).val(formatNumber(calVal.toFixed(2),2));

			} else {
				
				if(idName.indexOf("amount") !== -1){
					$('#'+idName).html(roundFormatNumber(Math.round(calVal)));
					
				}else{

					//In total_amount, decimal place is 1,in other cell decimal place is 2 for per month(wla)
					var val = idName.includes('salary') ? formatNumber(calVal.toFixed(1), 1) : formatNumber(calVal.toFixed(2), 2)

					$('#'+idName).html(val);
					$('#text_'+idName).html(formatNumber(calVal.toFixed(2),2)); // Add for savewholevalue hidden text 
					
				}
			}

			if ($('#hin-'+idName).length) {
				$('#hin-'+idName).val(formatNumber(calVal.toFixed(2),2));
			}

			if (calVal < 0) {
				$('#'+idName).addClass('negative');
				$('#text_'+idName).addClass('negative');
			} else {
				if($('#'+idName).hasClass('negative')){
					$('#'+idName).removeClass('negative');
					$('#text_'+idName).removeClass('negative');
				}
			}
		}

	}

	function CalculateTotal(idName,difference,columnName) {
	
		if ($('#'+idName).length) {
			var divider_half = 6;
			var divider_whole = 12;
			
			var first6Month = ['month_1_amt','month_2_amt','month_3_amt','month_4_amt','month_5_amt','month_6_amt'];
		
			var second6Month = ['month_7_amt','month_8_amt','month_9_amt','month_10_amt','month_11_amt','month_12_amt'];
			var halfMonth = [];
			var calVal1 = 0;
			var calVal2 = 0;
			var calVal = 0;

			$.each(first6Month, function(index, value) {
				var idName1 = idName.replace(columnName,value);
				
				var oldVal = 0;
				if ($('#'+idName1).hasClass('amount_input')) {
					if($('#'+idName1).hasClass('pointIn')){

						oldVal = $('input#'+idName1+'hidden.pointHidden').val().replace(/,/g, "");
						
					}else{

						oldVal = $('#'+idName1).val().replace(/,/g, "");
						
					}
					if(oldVal == ''){
						oldVal = 0;
					}
				} else {
					oldVal = $('#'+idName1).text().replace(/,/g, "");
				}
				//社員人件費（小計）_1st_half_total

				calVal1 += parseFloat(oldVal);

			});
			
			if(isNaN(calVal1)){
				calVal1 = 0;
			}
			
			$.each(second6Month, function(index, value) {
				var idName2 = idName.replace(columnName,value);
				var oldVal = 0;
				if ($('#'+idName2).hasClass('amount_input')) {
					if($('#'+idName2).hasClass('pointIn')){
						oldVal = $('input#'+idName2+'hidden.pointHidden').val().replace(/,/g, "");
						
					}else{

						oldVal = $('#'+idName2).val().replace(/,/g, "");
					}
					if(oldVal == ''){
						oldVal = 0;
					}
				} else {
					oldVal = $('#'+idName2).text().replace(/,/g, "");
				}
				calVal2 += parseFloat(oldVal);
			});

			if(isNaN(calVal2)){
				calVal2 = 0;
			}
			
			if (columnName == '1st_half_total') {
				calVal = calVal1/divider_half;

			} else {
				calVal = calVal2/divider_half;
			}

			//if ($('#'+idName).hasClass('amount_input')) {
			if(idName.indexOf("amount") !== -1 || (idName.indexOf("manualamt") !== -1 && idName.indexOf("-0-") == -1 )){
				$('#'+idName).html(roundFormatNumber(Math.round(calVal.toFixed(2))));
				$('#text_'+idName).html(roundFormatNumber(Math.round(calVal.toFixed(2))));
				$('#hin-'+idName).val(roundFormatNumber(Math.round(calVal.toFixed(2))));
				
			} else {
				//In total_amount, decimal place is 1,in other cell decimal place is 2 for 1st half and 2nd half(wla)
				var val = idName.includes('salary') ? formatNumber(calVal.toFixed(1), 1) : formatNumber(calVal.toFixed(2), 2)

				$('#'+idName).html(val);
				$('#text_'+idName).html(formatNumber(calVal.toFixed(2),2));
				$('#hin-'+idName).val(formatNumber(calVal.toFixed(2),2));
				
			
			}

			if (calVal < 0) {
				$('#'+idName).addClass('negative');
				$('#text'+idName).addClass('negative');
			} else {
				if($('#'+idName).hasClass('negative')){
					$('#'+idName).removeClass('negative')
					$('#text'+idName).removeClass('negative')
				}
			}
			

			var totalID = idName.replace(columnName,'sub_total');
			var totalCalVal = (calVal1+calVal2)/divider_whole;
			
			if(idName.indexOf("amount") !== -1 || (idName.indexOf("manualamt") !== -1 && idName.indexOf("-0-") == -1 )){
				$('#'+totalID).html(roundFormatNumber(Math.round(totalCalVal)));
				$('#text_'+totalID).html(roundFormatNumber(Math.round(totalCalVal)));
				$('#hin-'+totalID).val(roundFormatNumber(Math.round(totalCalVal)));
			} else {
				//In total_amount, decimal place is 1,in other cell decimal place is 2 for 1st half and 2nd half(wla)
				var val = idName.includes('salary') ? formatNumber(totalCalVal.toFixed(1), 1) : formatNumber(totalCalVal.toFixed(2), 2);

				$('#'+totalID).html(val);
				$('#text_'+totalID).html(formatNumber(totalCalVal.toFixed(2),2));
				$('#hin-'+totalID).val(formatNumber(totalCalVal.toFixed(2),2));
				
			}

			if (calVal < 0) {
				$('#'+totalID).addClass('negative');
				$('#text_'+totalID).addClass('negative');
			} else {
				if($('#'+totalID).hasClass('negative')){
					$('#'+totalID).removeClass('negative')
					$('#text_'+totalID).removeClass('negative')
				}
			}
		}

	}

	function formatNumber(num,pointPos) {
			
		pointPos = (pointPos == '2')? '.00' : '.0';
		if(num == ''){
			num = 0;
			return num.toFixed(2);
		}else{
			if(num.toString().indexOf('.') != -1) {
				num = Math.round(num * 100) / 100;
				var numArr = num.toString().split('.');
				var value = numArr[0];
				var value = value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
				if(numArr[1]) {
					return value+"."+numArr[1];
				}else {
					return value+pointPos;
				}
			}else{
				var value = num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
				return value+pointPos;
			} 
		}
		
	}

	function roundFormatNumber(num) {

		if(num == ''){

			var value = 0;
			
		}else{
			
			var value = num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
			
		}
		return value;
		
	}
	function MakeNegative(num) {

        if ((num.val().indexOf('-') == 0) || (num.html().indexOf('-') == 0)) 
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
    /*Nu Nu Lwin 20210812 if unit salary is null, show error msg. Use copy paste and manural type */
    function CheckUnitSalary(thisVal) {

    	if(thisVal.hasClass('unit') == false){

			if(thisVal.attr('id') !== undefined){
    		
				var idSpt = thisVal.attr('id').split("-");
				if($('.unit_'+idSpt[2]).hasClass('unit') == true){
					if($('.unit_'+idSpt[2]).val() == 0 || $('.unit_'+idSpt[2]).val() == ''){
						
						thisVal.val('');
						thisVal.tooltip({
		                	trigger: "change",
		                    placement: "right",
		                    template: '<div class="tooltip tooltip-error" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
		                }).attr("data-original-title", errMsg(commonMsg.JSE001, ['<?php echo __("単価")?>']));
		                
		                thisVal.tooltip("show");
		                thisVal.css({"border": "1px","border-style": "groove","border-color": "#f31515"});
		               	return false;
					}else{

						thisVal.tooltip("destroy");
						thisVal.css({"border": "0px"});
						return true;
					}
				}
			}
		}
    }
    $('.upload-div').on('change','.manpower_upload',function(e) {

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

          	var term_id = $('#hidden_term_id').val();
            var head_dept_id = $('#hidden_head_dept_id').val();
            var ba_code = $('#hidden_ba_code').val();

            if(errorFlag){
	        	
				showPopupBox(file_name,myFile,head_dept_id);
				   
			}  
        }

        //if upload file is already exist=>show duplicate confirm popup , else => show save confirm popup
	   	function showPopupBox(file_name,myFile,head_id){
	   	
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
							document.forms[0].action = "<?php echo $this->webroot; ?>BrmManpowerPlan/saveUploadFile";
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
	   
	}); 

	/* download bulk file */
	$('#btn_bulk_excel_download').click(function(){
			document.getElementById("error").innerHTML   = "";
			document.getElementById("success").innerHTML = "";
			document.forms[0].action = "<?php echo $this->webroot; ?>BrmManpowerPlan/downloadBulkExcelDownload";
			document.forms[0].method = "POST";
			document.forms[0].submit();
			return true;
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
								document.forms[0].action = "<?php echo $this->webroot; ?>BrmManpowerPlan/saveBulkExcelFile";
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

</script>
