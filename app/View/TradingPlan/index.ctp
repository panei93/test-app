<?php
	echo $this->Form->create(false,array('type'=>'post', 'id' => 'trading_plan', 'enctype'=> 'multipart/form-data', 'autocomplete'=>'off'));
?>

<style>
	.trading-table thead th{
		border: 1px solid #bbb;
		border-bottom: double #bbb;
		text-align: center;
	}
	.tbl-header {
		background: #d5eadd;
		padding: 10px;
	}
	.solid-border{
		border: 1px solid #bbb;
	}

	tbody tr td{
		border: 1px solid #bbb;
	}

	tr td.noBorder, tr th.noBorder{
		border: none !important;
		text-align: left;
		background-color: #fff;
	}

	tr td.foot-border{
		border-style: none none none solid;
		border-width: 1px;
	}

	.trading-table tr td.total_field {
		padding: 0px 5px;
		text-align: right;
		background-color: #fff;
		font-size: 12px !important;
	}
	.trading-table tr td.colorFill, .trading-table tr td input.colorFill {
		background-color: #f5f5f5 !important;
	}
	.trading-table td input[type=text]{
		padding:0 4px 0 4px;
		width: 100%;
		outline: none;
		border: none;
		border-radius: 0;
		box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
		resize: none;
	}

	.input-table td input[type=text]{
		padding:0 4px 0 4px;
		width: 100%;
		outline: none;
		border: none;
		border-radius: 0;
		box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
		resize: none;
	}

	.vbottom {
		margin-top: 20px;
	}
	.align-right {
		text-align: right !important;
		padding: 0px;
		margin-bottom: 20px;
	}

	@media screen and (max-width: 600px) {
		.vbottom {
			margin-top: 30px;
		}
		table.trading-table{
			display: block;
			overflow-x: auto; 
		}
	}
	@media screen and (max-width: 1025px) {
		table.input_table {
			display: block;
			overflow-x: auto;
		}
	}
	@media screen and (max-width: 1057px) {
		.col-sm-3.vbottom {
			padding-bottom: 30px;
		}

	}
	@media (max-width: 768px){
		.row.align-right {
			text-align: left !important;
		}
	}
	table {
		table-layout: fixed;
	}
	.trading-table {
		width: 100%;
		margin-bottom: 50px;
	}
	.trading-table .gap {
		width: 20px;
		border: none !important;
		position: relative;
	}
	.trading-table .gap .clone_copy, .clone_remove {
		width: 100%;
	    position: absolute;
	    top: 0;
	    left: 0;
	    padding: 0;
	    margin: 0;
	    height: 30px;
	    border-radius: 0;
	}
	.trading-table tr td.account_name {
		background-color: #fff;
		padding: 0px 5px;
		min-width: 210px;
		width: 210px !important;
	}

	.trading-table .gap {
		width: 20px;
		min-width: 20px;
		border: none !important;
		position: relative;
	}

	.trading-table tr td {
		/*min-width: 82px;*/
		width: 82px;
		height: 25px !important; /* old 20px */
		vertical-align: middle;
	}

	.trading-table tr.double-border td {
		border-top: double;
		border-bottom: double;
		border-left: double;
		border-color: #bbb;
	}
	.trading-table tr th {
		/*min-width: 75px;*/
		border-bottom: double;
		border-color: #bbb
	}
	.tbl_ajust {
		min-width: 65px !important;
	} 
	.trading-table .amount_input, .trading-table .kpi_amount_input, .trading-table .kpi_unit_input,.home_payment_fee {
		text-align: right;
		display: block;
		margin-bottom: 2px;
		height: 25px;
		width: 100%;
		border: none;
		padding: 5px;
		box-sizing: border-box;
		border: none;
		background-color: #D5F4FF;
		outline-width: 0; 
		font-size: 12px !important;
	}
	.trading-table .kpi_unit_input {
		width: 100% !important;
		float: right;
		border: 1px solid #bbb;
		border-radius: 5px;
		text-align: left;
		height: 33px !important;
		margin-bottom: 0;
	}
	.home_payment_fee{
		width: 90px !important;
		float: left;
		border: 1px solid #bbb;
		border-radius: 5px;
		text-align: left;
		height: 33px !important;
		margin-bottom: 0;
	}
	input.logistic_index {
		width: 243px !important;
		display: inline;
		background-color: #D5F4FF;
		position: relative;
	}
	select.logistic_index {
		width: 243px !important;
		display: inline;
		background-color: #D5F4FF;
		height: 29px;
		font-size: 12px;
		position: relative;
	}
	select.select_box {
		/*width: 40%; old */
		width: 80px;
		float: right;
		background-color: #D5F4FF;
		height: 33px;
		font-size: 12px;
	}
	.warning_text {
		font-family: meiryo;
		color: #f31515;
	}
	.negative {
		color: #f31515;
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
	.none {
		background:none !important;
	}
</style>
<div id="overlay">
	<span class="loader"></span>
</div>
<input type="hidden" id="disabled_excel_row" name="disabled_excel_row" value="<?php echo($disable) ?>">
<div class="container register_container">
	<div class="col-md-12" style="font-size: 0.95em;"> 
		<div class="row heading_line_title">
			<h3><?php echo __($_GET['year']."年度 "); ?><?php  echo __("取引計画フォーム") ?></h3>
			<div class="budget-form-hr">
		        <hr>
		        <span><?php echo __('水色セル内を入力して下さい'); ?></span>
		    </div>
		</div>
		<div class="row">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.UserSuccess"))? $this->Flash->render("UserSuccess") : '';?></div>            
			<div class="error" id="error"><?php echo ($this->Session->check("Message.UserError"))? $this->Flash->render("UserError") : '';?></div>
		</div>
		<div class="row">
			<div class="col-sm-9">
				<div class="form-group row">
					<div class="col-md-8 col-sm-12">
						<label for="object" class="col-md-4 col-form-label">
							<?php echo __("予算期間");?>
						</label>
						<div class="col-md-8">
							<input type="text" class="form-control" id="budget_term" name="budget_term" value="<?php echo $forecast_term; ?>" readonly/>
							<input type="hidden" id="term_id" name="term_id" value="<?php echo $term_id; ?>">
						</div>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-8 col-sm-12">
						<label for="object" class="col-md-4 col-form-label">
							<?php echo __("BAコード");?>
						</label>
						<div class="col-md-8">
							<input type="text" class="form-control" id="business_code" name="business_code" value="<?php echo $budget_BA; ?>" readonly/>
							<input type="hidden" id="ba" name="ba" value="<?php echo $budget_BA; ?>">
						</div>
					</div>					
				</div>
				<div class="form-group row">
					<div class="col-md-8 col-sm-12">
						<label for="deadline_date" class="col-md-4 col-form-label">
							<?php echo __("提出期日");?>
						</label>
						<div class="col-md-8">
							<input type="text" class="form-control" id="deadline_date" name="deadline_date" value="<?php echo $deadline_date; ?>" readonly/>
						</div>
					</div>
				</div>
				<input type="hidden" class="form-control" id="filling_date" name="filling_date" value="<?php echo $trade_filling_date; ?>" />
				<input type="hidden" id="year" name="year" value="<?php echo $_GET['year'];?>">
				<input type="hidden" id="logistic_index_no" name="logistic_index_no">
			</div>
			<div class="table-group">
			<?php if ($errormsg != ''): ?>
				<div id="err" class="col-md-12 no-data"> <?php echo ($errormsg);?></div>
			<?php else: ?>
				<div class="col-sm-12 col-md-12 text-right adjust">
					<div class="row align-right">
						<label id="btn_browse">Upload File
							<input type="file" name="trading_upload" class ="trading_upload">
						</label>
						<input type="button" name="btn_excel_download" id="btn_excel_download" class="btn btn-success btn_approve_style" value="<?php echo __("Excel Download"); ?>">

						<input type="button" name="btn_save" id="btn_save" class="btn btn-success btn_approve_style btn_sumisho" value="<?php echo __("保存"); ?>">
					</div>
				</div>
				<div class="col-md-12 align-right">
					<input type="button" name="btn_add_new_tab" id="btn_add_new_tab" class="btn btn-success" value="<?php echo __("テーブル追加"); ?>">
				</div>

				<?php if (!empty($trade_data)): ?>
					<div id="table-wpr">
						<!-- Summary Table -->
						<table class="trading-table" id="totalTable">
							<thead>
								<tr>
									<th class="w-20 noBorder"></th>
									<th colspan="2" class="w-210 noBorder"></th>
									<th class="noBorder" colspan="7" height="20px"></th>
									<th class="tbl-header"  rowspan="2" ><?php echo $short_year; ?><?php echo __('年度') ?></br><?php echo __('上期') ?></th>
									<th class="noBorder" colspan="6"></th>
									<th class="tbl-header"  rowspan="2" ><?php echo $short_year; ?><?php echo __('年度') ?></br><?php echo __('下期') ?></th>
									<th  class="tbl-header" rowspan="2" ><?php echo $short_year; ?><?php echo __('年度') ?></br><?php echo __('年間') ?></th>
								</tr>
								<tr>
									<td colspan="4" class="noBorder"><strong><?php echo $short_year; ?><?php echo __('年度 取引計画') ?></strong> <?php echo __('（単位：千円）') ?></td>

									<?php foreach ($months as $each_month): ?>
										<th class="tbl-header tbl_ajust"><?php echo __($each_month); ?></th>
									<?php endforeach ?>
								</tr>
								<tr>
									<td colspan="18" class="noBorder"><span style="font-weight: bold;"><?php echo __('取引合計'); ?></span></td>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($trade_data['total'] as $sub_acc_name => $sub_acc_datas): ?>
									<tr class="double-border">
									<td colspan="4" class="account_name"> <?php echo $sub_acc_name; ?> </td>
									<?php $result_month = $start_month; ?>
									<?php foreach ($sub_acc_datas['total']['amount'] as $month_name => $each_month):
										$sub_id = (!empty($sub_acc_datas['total']['sub_id'])) ? $sub_acc_datas['total']['sub_id'] : 3;
										
										$negclass = ($each_month < 0) ? 'negative' : '';
										$month = str_replace("月", "", $month_name);
										
										$bgclass = ($forecast_month >= $result_month && strpos($month_name,'月') && $chkyr) ? ' colorFill' : '';
										if($bgclass != '') $result_month = date("Y-m", strtotime($result_month. "last day of + 1 Month"));
 										?>
										<td class="total_field <?php echo $negclass.$bgclass; ?>" id="sum_<?php echo $month_name."_".$sub_id; ?>"><?php echo number_format($each_month,1); ?></td>
										<!-- hidden summary sub amt -->
										<td style="display: none;" class="total_field <?php echo $negclass.$bgclass; ?>" id="hid_sum_<?php echo $month_name.'_'.$sub_id; ?>">
											<?php echo number_format($each_month,3); ?>
										</td>
										
									<?php endforeach ?>
									</tr>
									<?php foreach ($sub_acc_datas['data'] as $acc_name => $value):
										$code = $value['code']; ?>
										<tr>
											<td class="gap"></td>
											<td colspan="3" class="account_name"><?php echo $acc_name; ?></td>
											<?php $result_month = $start_month; ?>
											<?php foreach ($value['amount'] as $month=>$each_month):

												$negclass = ($each_month < 0) ? 'negative' : '';
												$mon = str_replace("月", "", $month);
												$bgclass = ($forecast_month >= $result_month && strpos($month,'月') && $chkyr) ? ' colorFill' : '';
												if($bgclass != '') $result_month = date("Y-m", strtotime($result_month. "last day of + 1 Month"));?>
												<td class="total_field <?php echo $negclass.$bgclass ?>" id="sum_<?php echo $month."_".$sub_id."_".$code; ?>"><?php echo number_format($each_month,1); ?></td>
												<!-- hidden summary acc amt -->
												<td style="display: none;" class="total_field <?php echo $negclass.$bgclass; ?>" id="hid_sum_<?php echo $month.'_'.$sub_id.'_'.$code; ?>">
													<?php echo number_format($each_month,3); ?>
												</td>
											<?php endforeach ?>
											
										</tr>
									<?php endforeach;
								endforeach ?>
							</tbody>
						</table>
						<!-- End Summary Table -->
					<?php 
						$tradecnt = 0;
						$disabled = (count($trade_data['record'])==1) ? 'disabled' : '';
						
					foreach ($trade_data['record'] as $index_no => $trades):
						$tradecnt++;$logi_disabled = '';$del_disabled = '';
						if(!empty($result_index_no[$index_no])) {
							$logi_disabled = 'disabled';}
						if(!empty($result_index_no[$index_no]) || count($trade_data['record']) == 1) {
							$del_disabled = 'disabled';}
						 ?>
						

						<table class="trading-table sub-tbl" id="table_<?php echo $tradecnt ?>">
							<thead>
								<tr>
									<th class="w-20 noBorder"></th>
									<th colspan="2" class="w-210 noBorder"></th>
									<th class="noBorder" colspan="15" height="20px">
										<input type="button" data="<?php echo($index_no) ?>" class="btn btn-danger btn-sm pull-right delete_table_btn" name="trade[<?php echo($tradecnt) ?>][delete_table_btn]" value="<?php echo __('テーブル削除') ?>" <?php echo $del_disabled; ?>/>
									</th>
									
								</tr>
								<tr>
									<td colspan="18" class="noBorder">
										<span style="font-weight: bold;"><?php echo __('取引') ?></span>
										
										<select id="<?php echo('logistic_index_'.$tradecnt);  ?>" class="logistic_index form-control" 
										name="trade[<?php echo($tradecnt) ?>][<?php echo('logistic_index'); ?>]"  <?php echo($logi_disabled); ?>>
											<option value="" selected>---- Select Logistic Index ----</option>
											<?php foreach($logistic_data as $name) : ?>
												<option value="<?php echo $name;?>" <?php if(trim($index_no) == trim($name)){?> selected <?php } ?> ><?php echo $name; ?>
												</option>
											<?php endforeach ?>
										</select>
										
										<!-- hidden logistic -->
										<input type="hidden" id="hid_<?php echo('logistic_index_'.$tradecnt); ?>"  class="logistic_index form-control" name="hid_trade[<?php echo($tradecnt) ?>][<?php echo('logistic_index'); ?>]" value="<?php echo($index_no); ?>">
										<span class="warning_text"><?php echo __('⇒取引名をタブより選択して下さい。新規追加する際は財務経理部へご連絡下さい。') ?></span>
									</td>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($trades['trading'] as $sub_acc_name => $sub_acc_datas): ?>
									<!-- Sub Account Total eg.(売上高) -->
									<tr class="double-border">
									<td colspan="4" class="account_name"> <?php echo $sub_acc_name; ?> </td>
									<?php 
									$sub_id = (!empty($sub_acc_datas['total']['sub_id'])) ? $sub_acc_datas['total']['sub_id'] : 3;
									
									foreach ($sub_acc_datas['total']['amount'] as $month_name => $each_month):
										$negclass = ($each_month < 0) ? 'negative' : ''; ?>

										<td class="total_field <?php echo $negclass ?>" id="sum_<?php echo $tradecnt."_".$month_name."_".$sub_id; ?>"><?php echo number_format($each_month, 1); ?></td>
										<!-- hidden sub acc amt -->
										<td style="display: none;" class="total_field <?php echo $negclass ?>" id="hid_sum_<?php echo $tradecnt."_".$month_name."_".$sub_id; ?>">
											<?php echo number_format($each_month, 3); ?>
										</td>
									<?php endforeach ?>
									</tr>
									
									<?php $cnt=0; foreach ($sub_acc_datas['data'] as $acc_name => $value): ?>
										<!-- Account eg.(売上高（仕切) -->
										
										<?php 
											$acc_code = $value['code'];
											if (strpos($acc_name, '社内受払手数料') !== false):
											$acc_name_explode = explode(',', $acc_name);
											$acc_name = $acc_name_explode[0];
											$user_input = $acc_name_explode[1];
											
											foreach ($value['amount'] as $des => $amount) {$cnt++; ?>
										
										<tr class="input_row" id="<?php echo("tr_".$cnt); ?>">
											<?php if($cnt == 1)  {?>
											<td class="gap">
											
												<input type="button" class="btn btn-success clone_copy" id="<?php echo('table_'.$tradecnt.'btn_addrow_'.$cnt)?>" name="<?php echo('table_'.$tradecnt.'btn_addrow_'.$cnt);?>" value="<?php echo("+") ?>">
											
											</td>
											<?php }else {?>
											<td class="gap">
												<input type="button" class="btn btn-danger clone_remove" id="<?php echo('table_'.$tradecnt.'btn_addrow_'.$cnt)?>" name="<?php echo('table_'.$tradecnt.'btn_addrow_'.$cnt);?>" value="<?php echo("-") ?>" >
											
											</td>
											<?php } ?>
											<td class="account_name" style="border-right: none;padding: 0px 1px !important;letter-spacing: -1px;"><?php echo $acc_name; ?></td>
											<td style="border-left: none;">
												
												<input class="input home_payment_fee<?php echo($bgclass); ?>" id="<?php echo('fee_name_'.$tradecnt.'_tr_'.$cnt); ?>" name="trade[<?php echo($tradecnt) ?>][data][<?php echo($sub_id) ?>][<?php echo($acc_code) ?>][<?php echo('tr_'.$cnt); ?>][user_input]" value="<?php echo($user_input); ?>" <?php echo $user_disable; ?>>
												<!-- hidden home_payment_fee -->
												<input type="hidden" class="form-control home_payment_fee" id="hid_<?php echo('fee_name_'.$tradecnt.'_tr_'.$cnt); ?>" name="hid_trade[<?php echo($tradecnt) ?>][data][<?php echo($sub_id) ?>][<?php echo($acc_code) ?>][<?php echo('tr_'.$cnt); ?>][user_input]" value="<?php echo($user_input); ?>">
											</td>
											<td style="border-left: none;">
												<!-- khin -->
												<?php $dis = '';
												if($des != 0 && $chkyr) $dis = 'disabled';
												else $dis = '';
												?>
												<select class="select_box form-control select_destination" name="trade[<?php echo($tradecnt) ?>][data][<?php echo($sub_id) ?>][<?php echo($acc_code) ?>][<?php echo('tr_'.$cnt); ?>][destination]" id="<?php echo('select'.$tradecnt.'_tr_'.$cnt); ?>" <?php echo $dis; ?>>
													<option value="" selected>Select BA</option>
													<?php 
														foreach($destination as $ba => $name) : 
															?>
															<option value="<?php echo $name;?>" <?php if($ba == $des){?> selected <?php } ?> ><?php echo $name; ?>
															</option>
													<?php endforeach ?>
												</select>
												<!-- hidden destination -->
												<input type="hidden" class="form-control hid select_destination" name="hid_trade[<?php echo($tradecnt) ?>][data][<?php echo($sub_id) ?>][<?php echo($acc_code) ?>][<?php echo('tr_'.$cnt); ?>][destination]" id="hid_<?php echo('select'.$tradecnt.'_tr_'.$cnt); ?>" value="<?php echo($des); ?>">
											</td>
											<?php $result_month = $start_month;
												 ?>
											<?php foreach ($amount as $month => $each_amount):
												$negclass = ($each_amount < 0) ? 'negative' : '';
												if (($month == 'first_half') || ($month == 'second_half') || ($month == 'whole_total')):?>

												<td class="total_field <?php echo $negclass ?> " id="<?php echo "sum_".$tradecnt."_".$month."_".$sub_id."_".$acc_code."_tr_".$cnt; ?>"><?php echo number_format($each_amount,1); ?>
												</td>
												<!-- hidden acc total amt -->
												<td style="display: none;" class="total_field <?php echo $negclass ?> " id="hid_<?php echo "sum_".$tradecnt."_".$month."_".$sub_id."_".$acc_code."_tr_".$cnt; ?>">
													<?php echo number_format($each_amount,3); ?>
												</td>
											<?php else: 
												if($forecast_month >= $result_month && $chkyr) {
													$sameID = ' rec_'.$tradecnt."_".$month."_".$sub_id."_".$acc_code."_tr_".$cnt;
													$result_month = date("Y-m", strtotime($result_month. "last day of + 1 Month"));
													$disabled = 'disabled';
													$bgclass = ' colorFill ';
													$color_fill = $bgclass;
													$hid_each_amount = 0;
												} else {
													$sameID = '';
													$disabled = '';
													$bgclass = '';
													$color_fill = 'color_fill';
													$hid_each_amount = $each_amount;
												}
												
											?>
												
												<td class="<?php echo $color_fill ?>">
													<input id="<?php echo 'rec_'.$tradecnt."_".$month."_".$sub_id."_".$acc_code."_tr_".$cnt; ?>" type="" name="trade[<?php echo($tradecnt) ?>][data][<?php echo($sub_id) ?>][<?php echo($acc_code) ?>][<?php echo('tr_'.$cnt); ?>][<?php echo($month) ?>]" max="9" class="amount_input <?php echo $bgclass.$negclass.$sameID; ?>" value="<?php echo number_format($each_amount,1); ?>" <?php echo $disabled ?>>
													<!-- hidden acc amt -->
													<input id="hid_<?php echo 'rec_'.$tradecnt."_".$month."_".$sub_id."_".$acc_code."_tr_".$cnt; ?>" type="hidden" name="hid_trade[<?php echo($tradecnt) ?>][data][<?php echo($sub_id) ?>][<?php echo($acc_code) ?>][<?php echo('tr_'.$cnt); ?>][<?php echo($month) ?>]" max="9" class="amount_input <?php echo $bgclass.$negclass.$sameID; ?>" value="<?php echo number_format($hid_each_amount,3); ?>">
												</td>
											<?php endif;
										endforeach ?>
										</tr>
											
										<?php } ?>
										<?php else: ?>
										<?php foreach ($value['amount'] as $des => $amount) { ?>
										<tr class="input_row">
											<td class="gap">
											</td>
											<td colspan="3" class="account_name"><?php echo $acc_name; ?>
											</td>
											<td style="display: none;"></td>
											<?php $result_month = $start_month; ?>
											<?php foreach ($amount as $month => $each_amount): ?> 
											<?php $negclass = ($each_amount < 0) ? 'negative' : '';?>
											<?php if (($month == 'first_half') || ($month == 'second_half') || ($month == 'whole_total')): ?>
												<td class="total_field <?php echo $negclass ?>" id="sum_<?php echo $tradecnt."_".$month."_".$sub_id."_".$acc_code; ?>"><?php echo number_format($each_amount,1); ?>
												</td>
												<!-- hidden total amt -->
												<td style="display: none;" class="total_field <?php echo $negclass ?>" id="hid_sum_<?php echo $tradecnt."_".$month."_".$sub_id."_".$acc_code; ?>">
													<?php echo number_format($each_amount,3); ?>
												</td>
											<?php else: 
												
													$mon = str_replace("月", "", $month);
												
													if($forecast_month >= $result_month && $chkyr) {
														$sameID = ' rec_'.$tradecnt."_".$month."_".$sub_id."_".$acc_code;
														$result_month = date("Y-m", strtotime($result_month. "last day of + 1 Month"));
														$disabled = 'disabled';
														$bgclass = ' colorFill ';
														$color_fill = $bgclass;
														$hid_each_amount = 0;
													} else {
														$sameID = '';
														$disabled = '';
														$bgclass = '';
														$color_fill = 'color_fill';
														$hid_each_amount = $each_amount;
													} ?>
												<td class="<?php echo $color_fill ?>">
													<input id="rec_<?php echo $tradecnt."_".$month."_".$sub_id."_".$acc_code; ?>" type="" name="trade[<?php echo($tradecnt) ?>][data][<?php echo($sub_id) ?>][<?php echo($acc_code) ?>][<?php echo($month) ?>]" max="9" class="amount_input <?php echo $bgclass.$negclass.$sameID; ?>" value="<?php echo number_format($each_amount,1); ?>" <?php echo $disabled ?>>
													<!-- hidden acc amt -->
													<input id="hid_rec_<?php echo $tradecnt."_".$month."_".$sub_id."_".$acc_code; ?>" type="hidden" name="hid_trade[<?php echo($tradecnt) ?>][data][<?php echo($sub_id) ?>][<?php echo($acc_code) ?>][<?php echo($month) ?>]" max="9" class="amount_input <?php echo $bgclass.$negclass.$sameID; ?>" value="<?php echo number_format($hid_each_amount,3); ?>">
												</td>
											<?php endif ?>
											<?php endforeach ?>
										</tr>
										<?php } ?>
										<?php endif ?>
										
									<?php endforeach ?>

								<?php endforeach ?>
								<!-- KPI -->
								<tr class="double-border input_row">
									<td class="gap"></td>
									<td class="account_name" colspan = "2" style="border-right: none;">KPI</td>
									<td style="border-left: none;">
										<?php
											$kpi_unit = (!empty($trades['kpi'])) ? array_keys($trades['kpi']) : '';
										?>
										<input id="<?php echo('kpi_unit_input'.$tradecnt); ?>" class="kpi_unit_input" type="" name="trade[<?php echo($tradecnt) ?>][kpi_unit]" value="<?php echo $kpi_unit[0]; ?>">
										<!-- hidden kpi unit -->
										<input id="hid_<?php echo('kpi_unit_input'.$tradecnt); ?>" class="kpi_unit_input" type="hidden" name="hid_trade[<?php echo($tradecnt) ?>][kpi_unit]" value="<?php echo $kpi_unit[0]; ?>">
									</td>
									</td>
									<?php foreach ($table_months as $month): ?>
										<?php $kpi_amt = (!empty($trades['kpi'][$kpi_unit[0]]['amount'][$month])) ? $trades['kpi'][$kpi_unit[0]]['amount'][$month] : 0;
										if ($month == 'first_half'): ?>
											<td class="noBorder"></td>
										<?php elseif($month == 'second_half'): ?>
											<td colspan="2" class="noBorder">
											</td>
											<?php break;
										else: 
											$negclass = ($kpi_amt < 0) ? 'negative' : '';?>

											<td class="color_fill"><input id="rec_<?php echo $tradecnt."_".$month."_0_0000000000"; ?>" type="" name="trade[<?php echo($tradecnt) ?>][data][0][0000000000][<?php echo($month) ?>]" max="9" class="kpi_amount_input <?php echo $negclass ?>" value="<?php echo number_format($kpi_amt,1); ?>">
											<!-- hidden kpi amt -->
											<input id="hid_rec_<?php echo $tradecnt."_".$month."_0_0000000000"; ?>" type="hidden" name="hid_trade[<?php echo($tradecnt) ?>][data][0][0000000000][<?php echo($month) ?>]" max="9" class="kpi_amount_input <?php echo $negclass ?>" value="<?php echo number_format($kpi_amt,3); ?>">
											</td>
										<?php endif ?>
									<?php endforeach ?>
								</tr>
								
							</tbody>
						</table>
					<?php endforeach ?>
					</div>
				<?php endif ?>

			<?php endif ?>
			</div>
		</div>
		<br>
	</div>

	<div id='tbl_Addnew'> 
	</div>
	<br><br><br>
	<input type="hidden" id="year" name="year" value="<?php echo $_GET['year'];?>">
	<input type="hidden" id="logistic_index_no" name="logistic_index_no">
		
</div>
<?php
	echo $this->Form->end();
?>

<script type="text/javascript">

	let approved_BA = <?php echo json_encode($approved_BA); ?>;
	let approveHQ = <?php echo json_encode($approveHQ); ?>;
	let language = <?php echo json_encode($language); ?>;
	let page = "<?php if(!empty($page)){echo $page;}else{echo '';}  ?>";
	
	if(approved_BA != '' || page == 'Disabled' || approveHQ != ''){

		$('input[type="text"], .amount_input, .delete_table_btn, #btn_add_new_tab, .kpi_unit_input, .kpi_amount_input, .select_destination, .clone_copy, .clone_remove, .home_payment_fee').each(function () {
			$(this).prop('disabled', true);
		})

		$('.logistic_index, #btn_save,.trading_upload').prop('disabled', true);
		$("#btn_browse").css({"opacity": 0.5, "cursor": "not-allowed",
							  "background-color": "#D5EADD", "color": "#000"});
		$('#filling_date').prop('disabled', true);
		$('input[type="text"], .amount_input, .kpi_unit_input, .kpi_amount_input, .home_payment_fee').css('background-color', '#F9F9F9');
		$('.color_fill').css('background-color', '#F9F9F9');
	}else {
		$('.color_fill').css('background-color', '#D5F4FF');
	}

	$(document).ready(function(){
		/*var $check_actual = "<?php if(!empty($chk)){echo $chk;}else{echo '';}  ?>";
		if($check_actual == "nosame"){        
            $.confirm({           
				title: '<?php echo __("警告メッセージ");?>',                  
				icon: 'fas fa-exclamation-triangle',                  
				type: 'orange',                  
				typeAnimated: true, 
				closeIcon: true,
				columnClass: 'medium',                
				animateFromElement: true,                 
				animation: 'top',                 
				draggable: false,                 
				content: errMsg(commonMsg.JSE052),
				buttons: {                    
					ok: {
						text: '<?php echo __("OK");?>',                 
						btnClass: 'btn-info',  
					}            
				},                  
				theme: 'material',                  
				animation: 'rotateYR',                  
				closeAnimation: 'rotateXR'                  
			});
        }*/

		$('.datepicker').datepicker({
		   format: 'yyyy/mm/dd'
 		});

		/* file upload */
 		$(document).on('change', '.trading_upload', function() {
 			console.log(this.files[0].size);
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
									document.forms[0].action = "<?php echo $this->webroot; ?>TradingPlan/UploadFile";
									document.forms[0].method = "POST";
									document.forms[0].submit(); 
					          	}
							},	  
							cancel: {
						       	text: "<?php echo __('いいえ'); ?>",
								btnClass: 'btn-default',
						       	action: function(){}
							}
						},
						theme: 'material',
						animation: 'rotateYR',
						closeAnimation: 'rotateXR'
					});
							   
				}  
	        } 			
 			
 		});		
 		// clone the tr row account_code = 6108000000
		$('.table-group').on('click','.clone_copy', function() { 
			
			var $table = $(this).closest('table')[0]['id'];
			// var $body = $(this).closest('tbody')[0]['id'];
			var $div = $('table[id^="table_"]:last')[0]['id'];
			var $tr = $('#'+$table+' tr[id^="tr_"]:last');
			// var num = $('#'+$table+' tr[id^="tr_"]').length+1;
			var prevnum = parseInt( $tr.prop("id").match(/\d+/g), 10 );
			var num = parseInt( $tr.prop("id").match(/\d+/g), 10 ) +1;
			if(CheckLimit(num)) {
				// $tr.clone().prop('id', 'tr_'+num ).appendTo("#"+$table+" #"+$body);
				$("#"+$table+' #'+$tr[0]['id']).after($tr.clone().prop('id', 'tr_'+num ).appendTo("#"+$table));
							
				$('#'+$table+' #tr_'+num).each(function() {

					$(this).find('input[type=button]').each(function(){
						
						var btn_id = $(this).prop('id');
						var btnId = btn_id.replace($table+"btn_addrow_"+prevnum, $table+"btn_addrow_"+num);
						$(this).prop('id', btnId);
		
						var btn_class = $(this).attr('class');
						var btnClass = btn_class.replace("btn btn-success clone_copy", "btn btn-danger clone_remove");
						$(this).attr('class', btnClass);
						$(this).prop('disabled', false);

						$('.clone_remove').attr('value', '-');

						var btn_name = $(this).attr('name');
						var btnName = btn_name.replace($table+"btn_addrow_"+prevnum, $table+"btn_addrow_"+num); 
						$(this).attr('name', btnName);
					});

					$(this).find('.select_destination').each(function(){

						var sel_id = $(this).prop('id');
						var selId = sel_id.replace("tr_"+prevnum, "tr_"+num);
						$(this).prop('id', selId);

						var sel_name = $(this).attr('name');
						var selName = sel_name.replace("tr_"+prevnum, "tr_"+num);
						$(this).attr('name', selName);
						$(this).prop('disabled', false);
					});
					//Loop and set 0 to all inputs
					$(this).find('.amount_input').each(function(){
						$(this).removeClass('negative');
						$(this).css({"border": "none"});
						var in_id = $(this).prop('id');
						var inId = in_id.replace("tr_"+prevnum, "tr_"+num);
						$(this).prop('id', inId);
						
						var in_name = $(this).attr('name');
						var inName = in_name.replace("[tr_"+prevnum+"]", "[tr_"+num+"]");
						$(this).attr('name', inName);

						var in_class = $(this).prop('class');
						var inClass = in_class.replace("tr_"+prevnum, "tr_"+num);
						$(this).prop('class', inClass);

						$(this).val(formatNumber(0)); //set 0 to input value
					});
					$(this).find('.home_payment_fee').each(function(){
						
						var fee_id = $(this).prop('id');
						var feeId = fee_id.replace("tr_"+prevnum, "tr_"+num);
						$(this).prop('id', feeId);

						var fee_name = $(this).attr('name');
						var feeName = fee_name.replace("tr_"+prevnum, "tr_"+num);
						$(this).attr('name', feeName);
						$(this).val('');
						$(this).prop('disabled', false);
						$(this).removeClass('colorFill');
						$(this).css('background-color', '#D5F4FF');

					});
					$('#'+$table+' #tr_'+num+' td select.select_destination').val('');

				});

				$('#'+$table+' #tr_'+num+' td select.select_destination').val('');
				$('#'+$table+' #tr_'+num+' input.select_destination').val(''); // hidden destination

			}
			
			//Loop and set 0 to all total fields
			$('#'+$table+' #tr_'+num+' td.total_field').each(function(idx, val){
				$(this).removeClass('negative');
				var id = $(this).prop('id');
				var newID = id.replace("tr_"+prevnum, "tr_"+num);
				$(this).prop('id', newID);
				$(this).html(formatNumber(0));
			});
		});

		// delete the clone tr
		$('.table-group').on('click','.clone_remove', function() {
			var currentTbl = $(this).closest('table')[0]['id'];
			var currentTr = $(this).closest('tr')[0]['id']; // tr_2
			var idArr = [];
			var colName = "";
			$('#'+currentTbl+' #'+currentTr+' td .amount_input').each(function(a,b) {
				// b = rec_1_4月_2_6108000000_tr_2
				var id = b['id'];
				var idArr = PrepareForId(id);
				$.each(idArr, function(key, value) {
					var removeVal = $('#'+id).val().replace(/,/g, ''); 
					var totalVal = $(value).text().replace(/,/g, '');
					var total = (parseFloat(totalVal) - parseFloat(removeVal)).toFixed(1);
					$(value).html(formatNumber(total));
					MakeNegative($(value));
				});
			});

			var currentVal = [];
			var trid = $(this).closest('tr')[0]['id'];
   			var tblid =  $(this).closest('table')[0]['id'];
   			var id = '#'+tblid+' #'+trid;
			var homepayment = $(id+' .input.home_payment_fee').val(); // cur home payment
			var destination = $(id+' select_box.select_destination').val(); // cur destination
			currentVal.push([homepayment,destination]);
			var homepay_id = $(id+' .input.home_payment_fee').attr('id');
			desti = getDestArray(tblid, (this.id), homepay_id); // selected destionation array
			if(arrayInArray(currentVal,desti)){
				desti = currentValDelete(currentVal,desti);
			}

			$('#'+currentTbl+' #'+currentTr).remove();
			
		});

		// When place cursor in input field
		$('.table-group').on('focus','.amount_input, .kpi_amount_input',function(event){
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
		$(".table-group").on('input','.amount_input, .kpi_amount_input', function(e) { 
			$(this).val($(this).val().replace(/[^0-9\.\-]/g, ''));
			var hidId = "#hid_"+(this.id);
			$(hidId).val($(this).val()); // when input amt, auto input in hid field
		}); 

		// when focus out, set number to format number eg : 1,000.000
		$('.table-group').on('focusout','.amount_input, .kpi_amount_input',function(event){
			// In IE, when values is change, no change value(change function is not working). 
			var ua = window.navigator.userAgent;
			var msie = ua.indexOf("MSIE ");
			if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)){
				if($(this).val() == '') {
					$(this).trigger('change');
					MakeNegative($(this));
				}
			}
			$(this).val(function(index,value){
				value = value.replace(/,/g, "");
				if(value == "" || value == "-"){
					$(this).removeClass('negative');
					value=0;
				}else{
					return formatNumber(value);
				}
				return formatNumber(value);
				
			});
		});

		$(".table-group").on('paste', '.amount_input, .kpi_amount_input', function(e){

			var $this = $(this);
			if (window.clipboardData && window.clipboardData.getData) { // IE
				var trid = $(this).closest('tr')[0]['id'];
		   		var tblid =  $(this).closest('table')[0]['id'];
	   			var id = '#'+tblid+' #'+trid;
				text = window.clipboardData.getData('Text');
				var x = $this.closest('td').index(),
				y = $this.closest('tr').index(),
				obj = {};

				//text = v.trim('\r\n');
				$.each(text.split('\r\n'), function(i2, v2){
					if(v2 != "") {
						$.each(v2.split('\t'), function(i3, v3){
							var row = y+i2, col = x+i3;

							if(trid == "") {
								v3 = (v3 === "") ? "0.0" : v3.replace(/,/g, '');
							}else {
								var destination = $(id+' .select_destination').val();
								if(destination != "") {
									v3 = (v3 === "") ? "0.0" : v3.replace(/,/g, '');
								}else {
									v3 = "0.0";
								}
							}
							v3 = parseFloat(v3).toFixed(1);
							v3 = v3.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); //number format with comma
							$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').data('val', $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val());
							$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val(v3).trigger('change').focusin();
							$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val(v3).trigger('change').focusout();
							//copy value to paste ui of input field
							$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val(v3).trigger('change');	
						});
					}
				});

				return false;         	

		    }else{
		    	//other browser
		  		var trid = $(this).closest('tr')[0]['id'];
		  		var tblid =  $(this).closest('table')[0]['id'];
	    		var id = '#'+tblid+' #'+trid;
				$.each(e.originalEvent.clipboardData.items, function(i, v){
					
					if (v.type === 'text/plain'){
						
						v.getAsString(function(text){
							var x = $this.closest('td').index(),
							y = $this.closest('tr').index(),
							obj = {};
							
							// text = text.split('\r\n');
							$.each(text.split('\n'), function(i2, v2){//row
								if(v2 != "") {
									$.each(v2.split('\t'), function(i3, v3){//col
										
										var row = y+i2, col = x+i3;
									
										if(trid == "") {
											v3 = (v3 === "") ? "0.0" : v3.replace(/,/g, '');
										}else {
											var destination = $(id+' .select_destination').val();
											if(destination != "") {
												v3 = (v3 === "") ? "0.0" : v3.replace(/,/g, '');
											}else {
												v3 = "0.0";
											}
										}
										v3hid = parseFloat(v3).toFixed(3);
										v3 = parseFloat(v3).toFixed(1);
										
										// v3 = parseFloat(v3).toFixed(1);
										v3 = v3.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); //number format with comma
										v3hid = v3hid.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
										$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').data('val', $this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input').val());
										//type="hidden"
										$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input[type="hidden"]').val(v3hid).trigger('change').focusin();
										$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input[type="hidden"]').val(v3hid).trigger('change').focusout();
										//copy value to paste ui of input field
										$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input[type="hidden"]').val(v3hid).trigger('change');
										//type=""
										$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input[type=""]').val(v3).trigger('change').focusin();
										$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input[type=""]').val(v3).trigger('change').focusout();
										//copy value to paste ui of input field
										$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input[type=""]').val(v3).trigger('change');
									});
								}
								
							});
													
						});
					}
				});
				return false; 
			}
		});

		$('.table-group').on('keyup','.amount_input',function(event){
			$(this).trigger('change');
			$(this).data('val', $(this).val());
			MakeNegative($(this));
			CheckBANull($(this)); // if no choose destination(ba_code), show err msg
		});
		$('.table-group').on('keyup','.kpi_amount_input',function(event){
			MakeNegative($(this));
		});
		$(function() { 
	        $("input[name='filling_date']").on('input', function(e) { 
	            $(this).val($(this).val().replace(/[^0-9]/g, '')); 
	        }); 
    	});
		
		$('#btn_add_new_tab').click(function(){ //Clone the table
			
			// get the last DIV which ID starts with ^= "table"
			var $div = $('table[id^="table_"]:last');
			// Read the Number from that DIV's ID (i.e: 3 from "table_3")
			// And increment that number by 1
			var prevnum = parseInt( $div.prop("id").match(/\d+/g), 10 );
			var num = parseInt( $div.prop("id").match(/\d+/g), 10 ) +1;

			var $table = $div.clone().prop('id', 'table_'+num ).appendTo('#table-wpr');
			
			$('#table_'+num+' tr td').each(function(index, value) {

				//Loop and set 0 to all inputs
				$(this).find('.amount_input, .kpi_amount_input').each(function(idx, val){
					$(this).removeClass('negative');
					var id = $(this).prop('id'); //get id
					var newID = id.replace("rec_"+prevnum, "rec_"+num); //replace new number to id
					$(this).prop('id', newID); //replace new id
					
					var name = $(this).attr('name'); //get name
					var newName = name.replace("trade["+prevnum+"]", "trade["+num+"]"); //replace new number to name
					$(this).attr('name', newName); //replace with new name

					var class_name = $(this).prop('class');
					var check_cn = (class_name.substring(class_name.indexOf("rec") - 1)).trim();
					if(id == check_cn || check_cn.indexOf(id) != -1) {
						var newClass = class_name.replace("rec_"+prevnum, "rec_"+num);
						$(this).prop('class', newClass);
					}
					$(this).val(formatNumber(0)); //set 0 to input value
				});
				// clone logistic_index id
				$(this).find('.logistic_index').each(function(){
					
					var log_id = $(this).prop('id');
					var logID = log_id.replace("logistic_index_"+prevnum, "logistic_index_"+num);
					$(this).prop('id', logID);
					var log_name = $(this).attr('name'); //get name
					var logName = log_name.replace("trade["+prevnum+"]", "trade["+num+"]"); //replace new number to name
					$(this).attr('name', logName);
					if(logID.split("_")[0] == 'hid') $("#"+logID).val('');

					$(this).attr('disabled', false);
				});
				// clone kpi_unit_input id
				$(this).find('.kpi_unit_input').each(function(){
					var unit_id = $(this).prop('id');
					var unitID = unit_id.replace("kpi_unit_input"+prevnum, "kpi_unit_input"+num);
					$(this).prop('id', unitID);
					var unit_name = $(this).attr('name'); //get name
					var unitName = unit_name.replace("trade["+prevnum+"]", "trade["+num+"]"); //replace new number to name
					$(this).attr('name', unitName);
				});
				// clone select id
				$(this).find('.select_destination').each(function(){
					
					var sel_id = $(this).prop('id');
					var selId = sel_id.replace("select"+prevnum, "select"+num);
					$(this).prop('id', selId);

					var sel_name = $(this).attr('name');
					var selName = sel_name.replace("trade["+prevnum+"]", "trade["+num+"]");
					$(this).attr('name', selName);
					$(this).prop('disabled', false);
					if(selId.split("_")[0] == 'hid') $("#"+selId).val('');
				
				});
				// clone button id
				$(this).find('input[type=button]').each(function(){
					
					var btn_id = $(this).prop('id');
					var but = btn_id.split("_");
					var cnt = but[but.length - 1];
					if(but[but.length - 1] > 1) {
						$('#table_'+num+' tbody #tr_'+cnt).remove();
					}
					var btnId = btn_id.replace($div.prop("id"), "table_"+num);
					$(this).prop('id', btnId);
					
					var btn_name = $(this).attr('name');
					var btnName = btn_name.replace($div.prop("id"), "table_"+num); 
					$(this).attr('name', btnName);
					
				});
				$(this).find('.home_payment_fee').each(function(){
					
					var fee_id = $(this).prop('id');
					var feeId = fee_id.replace("fee_name_"+prevnum, "fee_name_"+num);
					$(this).prop('id', feeId);

					var fee_name = $(this).attr('name');
					var feeName = fee_name.replace("trade["+prevnum+"]", "trade["+num+"]");
					$(this).attr('name', feeName);
				
				})
			});
			
			$('#table_'+num+' tr td.total_field').each(function(idx, val){
				$(this).removeClass('negative');
				var id = $(this).prop('id');
				var newID = id.replace("sum_"+prevnum, "sum_"+num);
				$(this).prop('id', newID);
				$(this).html(formatNumber(0));
			});
			
			var tblCount = $('table[id^="table_"]').length;
			if (tblCount > 1) {
				$('.delete_table_btn').each(function () {
					$(this).prop('disabled', false);
				})
			}
			$('table select.logistic_index[disabled]').each(function() {
				var tbl_id = $(this).closest('table').attr('id');
				$("#"+tbl_id+' .delete_table_btn').prop('disabled', true);
			});
			$('#table_'+num+' .delete_table_btn').prop('disabled',false);
			// Set null to logistic index input
			$('#table_'+num+' tr td select.logistic_index').val('');

			if(language === 'eng') {
				$('#table_'+num+' tr td input.delete_table_btn').val('Delete Table');
			}else $('#table_'+num+' tr td input.delete_table_btn').val('テーブル削除');

			$('#table_'+num+' tr td select.select_destination').val('');

			$('#table_'+num+' tr td input.home_payment_fee').val('');

			$('#table_'+num+' tr td input.kpi_unit_input').val('');

			$('#table_'+num+' tr td .kpi_amount_input').val(formatNumber(0));

			// Jump to added table
			$('html, body').animate({
				scrollTop: $("#table_"+num).offset().top
			}, 1000);

		});
		$('.sub-tbl '+' tr td input[disabled]').each(function(index, value) {
			var id = value.id;
			var arr = [];
			if(id.indexOf("_tr_") != -1) {
				var i = id.split('_')[1];
				var x = id.split('_')[6];
				var input = $('#table_'+i+' tbody #tr_'+x+' .amount_input[disabled]');
				for(var y=0;y<input.length; y++) {
					var inputId = input[y]['id'];
					var inputVal = $('#'+inputId).val();
				
					if(inputVal != 0.0) {
						arr.push(inputVal);						
					}
				}
				var destination = $('#table_'+i+' tbody #tr_'+x+' #select'+i+'_tr_'+x).val();
				var user_input = $('#table_'+i+' tbody #tr_'+x+' .home_payment_fee').val();
				var logistic = $('#table_'+i+' tr td select.logistic_index').val();
				
				if(approved_BA == '') {
					if(typeof arr !== 'undefined' && arr.length === 0) {
						$('#table_'+i+' tbody #tr_'+x+' #select'+i+'_tr_'+x).prop('disabled', false);
						$('#table_'+i+' tbody #tr_'+x+' #select'+i+'_tr_'+x).prop('disabled', false);
						$('#table_'+i+' tbody #tr_'+x+' .home_payment_fee').prop('disabled', false);
						$('#table_'+i+' tbody #tr_'+x+' .home_payment_fee').css('background-color', '#D5F4FF');
						$('#table_'+i+' tbody #tr_'+x+' .home_payment_fee').css('cursor', 'allowed');
						$('#table_'+i+' tbody #tr_'+x+' #hid_select'+i+'_tr_'+x).val(0);
						if(destination == '') {
							$('#table_'+i+' tbody #tr_'+x+' #hid_select'+i+'_tr_'+x).val(0);
						}else $('#table_'+i+' tbody #tr_'+x+' #hid_select'+i+'_tr_'+x).val(destination);

					}else {
						$('#table_'+i+' tbody #tr_'+x+' #select'+i+'_tr_'+x).prop('disabled', true);
						$('#table_'+i+' tbody #tr_'+x+' .clone_remove').prop('disabled', true);
						
						if(destination == '') {
							$('#table_'+i+' tbody #tr_'+x+' #hid_select'+i+'_tr_'+x).val(1);
						}else if(logistic == '取引無し' && user_input == '' && (destination == '8000' || destination.split('/')[0] == '8000')) {
							$('#table_'+i+' tbody #tr_'+x+' .home_payment_fee').prop('disabled', true);
							$('#table_'+i+' tbody #tr_'+x+' .home_payment_fee').css('background-color', '#eee');
						}
					}
				}
			}
		});
		$("#btn_save").click(function(){

			var chk = true;	
			var chkLog = [];
			var chkDestination = [];var logarr = [];var log = [];
			var destinationHomeDuplicate = [];
			var tableId = [];
			
			document.getElementById("error").innerHTML   = "";                      
			document.getElementById("success").innerHTML = "";

			var $div = $('table[id^="table_"]:last');
			var prevnum = parseInt( $div.prop("id").match(/\d+/g), 10 );
			var filling_date = document.getElementById('filling_date').value;

			for(var i=1; i<=prevnum; i++){
				var destinatinoHomePair = [];
				var logistic = $('#table_'+i+' tr td select.logistic_index').val();
				if(!checkNullOrBlank(logistic)) {		

					chkLog.push(i);					

				}else {
					if($.inArray(logistic, logarr) !== -1) {
						log.push(logistic);
					}else {
						logarr.push(logistic);
					}
				}

				var trcnt = $('#table_'+i+' tbody select[id^="select"]').length;
				for(var x=1; x<=trcnt; x++) {
					var destination = $('#table_'+i+' tbody #tr_'+x+' #select'+i+'_tr_'+x).val();
					var homePay = $('#table_'+i+' tbody #tr_'+x+' #fee_name_'+i+'_tr_'+x).val();
					var input = $('#table_'+i+' tbody #tr_'+x+' .amount_input');
					for(var y=0;y<input.length; y++) {
						var inputId = $('#table_'+i+' tbody #tr_'+x+' .amount_input')[y]['id'];
						var inputVal = $('#'+inputId).val();
						var user_input = $('#table_'+i+' tbody #tr_'+x+' #fee_name_'+i+'_tr_'+x).val();// id="fee_name_1_tr_1"
						if(user_input != undefined){ 
							var destination = $('#table_'+i+' tbody #tr_'+x+' #select'+i+'_tr_'+x).val();
							var hid_des = $('#table_'+i+' tbody #tr_'+x+' #hid_select'+i+'_tr_'+x).val();
							if(inputVal != 0.0 && destination == '') {
								if(hid_des != 1) {
									chkDestination.push(logistic);
									$.unique(chkDestination);
								}
							}
						}
					}
					if(destination != ''){
						destinatinoHomePair.push({'id' : i, 'home_destination': homePay+'_'+destination, 'logistic' :logistic});
					}
				}
				destinationHomeDuplicate[logistic] = destinatinoHomePair;
			}
			if(!checkNullOrBlank(filling_date)) {

				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("提出日")?>'])));											
				document.getElementById("error").appendChild(a);											
				chk = false;											
			}
			if(chkLog.length !== 0){

				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE050,[chkLog.join(',')])));
				document.getElementById("error").appendChild(a);										
				chk = false;
			}
			if(chkDestination.length !== 0){

				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE051,[chkDestination.join(',')])));
				document.getElementById("error").appendChild(a);										
				chk = false;
			}
			if(log.length !== 0){

				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE055,[log.join(',')])));
				document.getElementById("error").appendChild(a);										
				chk = false;
			}
			var arr = Object.keys(destinationHomeDuplicate).map(function (key) { return destinationHomeDuplicate[key]; });
			
			$.each(arr, function (h_key, h_value) {
				var temp = [];
				$.each(h_value, function (key, value) {
					if($.inArray(value.home_destination, temp) === -1) {
						temp.push(value.home_destination);
					}else{
						tableId.push(value.logistic);
					}
				});
			});
			$.unique(tableId);
			if(tableId.length > 0){
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);
				
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE075,[tableId.join(',')])));
				document.getElementById("error").appendChild(a);										
				chk = false;
			}
			if(chk) { 
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
								document.forms[0].action = "<?php echo $this->webroot; ?>TradingPlan/saveTradingData";
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

						},                
					},                  
					theme: 'material',                  
					animation: 'rotateYR',                  
					closeAnimation: 'rotateXR'                  
				});
			}

		});

		$("#btn_excel_download").click(function(){
			// loadingPic();
			document.forms[0].action = "downloadExcelData";   
			document.forms[0].method = "POST";
			document.forms[0].submit();
			return true;
		});
		
		$('.table-group').on('focusin','.amount_input', function(){
			// $(this).data('val', $(this).val()); //Save current value before change
			if(this.id.split("_")[0]!='hid') {
				// var hid_val = ($("#hid_"+this.id).val() == 0) ? '' : ($("#hid_"+this.id).val()).replace(/\.00+$/,'');
				var hid_val = ($("#hid_"+this.id).val() == 0) ? '' : parseFloat($("#hid_"+this.id).val().replace(/,/g, ""));
				// use parsefloat(eg:1.560=>1.56)
			}else {
				// var hid_val = ($("#"+this.id).val() == 0) ? '' : ($("#"+this.id).val()).replace(/\.00+$/,'');
				var hid_val = ($("#"+this.id).val() == 0) ? '' : parseFloat($("#"+this.id).val().replace(/,/g, ""));
			}
			$(this).val(hid_val);
			$(this).data('val', $(this).val());
		});
		
		$('.table-group').on('change','.amount_input', function(){
			var decimalOnly =/^\s*-?(\d{0,7})(\.\d{0,3})?\s*$/;
			var $hidId = "#hid_"+(this.id);
			if(decimalOnly.test($(this).val().replace(/,/g, "")) == false) {
				
				$('#success').empty();
                $("#error").html(errMsg(commonMsg.JSE061)).show();
			    scrollText();
			    MakeNegative($(this));
			    $(this).val('0.0');
			    $($hidId).val('0.00');
			    $(this).trigger('change');
            } else {
            	
				MakeNegative($(this));
				var prev = ($(this).data('val')!='') ? parseFloat($(this).data('val').replace(/,/g, '')) : 0; //get previous value that was saved in 'focusin' state

				prev = isNaN(prev)? 0 : prev; 
				if(this.id.split("_")[0] != "hid") {
					var $hidId = "#hid_"+(this.id);
				}else {
					var $hidId = "#"+(this.id);
				}

				// var current = ($(this).val()=='' || $(this).val()== '-') ? 0 : parseFloat($(this).val().replace(/,/g, '')); //get current value
				var current = ($($hidId).val()=='' || $($hidId).val()== '-') ? 0 : parseFloat($($hidId).val().replace(/,/g, '')); //get current value
				current = isNaN(current)? 0 : current;
				var diff = current - prev; //get difference 
				
				var id = $(this).prop('id'); //rec_1_6月_1_6002000000
				var class_name = $(this).prop('class');
				var check_cn = (class_name.substring(class_name.indexOf("rec") - 1)).trim();

				var idArr = PrepareForId(id); //prepare to get field' s id
				if(id == check_cn || check_cn.indexOf(id) != -1) {
					idArr.length = 8;
				}
				
				//Loop array and add chkanged amount
				$.each(idArr, function(index, value) {

					//get old value and replace comma
					// var oldVal = $(value).html().replace(',','');// old code from kzt
					// var oldVal = $(value).text().replace(/,/g, '');//total

					var hidId = value.replace('#', '#hid_');
					// var oldVal = $(hidId).val();//get hidden total
					var oldVal = $(hidId).text().replace(/,/g, '');//get hidden total
					var calVal = parseFloat(oldVal) + diff;

					/*$(value).html(formatNumber(calVal));		
					$(hidId).html(calVal);//set hidden total						
					
					if (calVal.toFixed(3) < 0) {
						$(value).addClass('negative');
					} else {
						if($(value).hasClass('negative')){
							$(value).removeClass('negative')
						}
					}*/
					if (!$(value).hasClass("colorFill")) {
						
						$(value).html(formatNumber(calVal));

						if (calVal < 0) {
							$(value).addClass('negative');
						} else {
							if($(value).hasClass('negative')){
								$(value).removeClass('negative')
							}
						}
					}
					if (!$(hidId).hasClass("colorFill")) {
						$(hidId).html(calVal);//set hidden total
						
					}
				});
			}

		});
		$(".table-group").on('change','input[type=hidden]:not([readonly]).amount_input', function(e) { 
			$(this).val($(this).val().replace(/,/g, ""));
		});
		var desti = [];
		$('.table-group').on('change','.select_box.select_destination, .input.home_payment_fee', function(){

			var currentVal = [];
			var trid = $(this).closest('tr')[0]['id'];
   			var tblid =  $(this).closest('table')[0]['id'];
   			var id = '#'+tblid+' #'+trid;
			var homepayment = $(id+' .input.home_payment_fee').val(); // cur home payment
			var destination = $(id+' .select_box.select_destination').val(); // cur destination
			currentVal.push([homepayment,destination]);
			var homepay_id = $(id+' .input.home_payment_fee').attr('id');
			desti = getDestArray(tblid, (this.id), homepay_id); // selected destionation array
			if($(this).attr('class') == 'input home_payment_fee'){
				
				var conflitTime = 0;
				jQuery.grep(desti, function(value) {
					
				   	if (JSON.stringify(value) === JSON.stringify(currentVal[0])){
				   		conflitTime++;
				   		if(conflitTime >= 2){

							$(id+' .input.home_payment_fee').val('');
							$(id+' .input.home_payment_fee').tooltip({
			                	trigger: "change",
			                    placement: "right",
			                    template: '<div class="tooltip tooltip-error" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
			                }).attr("data-original-title", errMsg(commonMsg.JSE054,[homepayment+' ( '+destination+' )']));
			                
			                $(id+' .input.home_payment_fee').tooltip("show");
			                $(id+' .input.home_payment_fee').css({"border": "1px","border-style": "groove","border-color": "#f31515"});
					    	return false;
							
				   		}else{
				   			$(id+' .input.home_payment_fee').tooltip("destroy");
							$(id+' .input.home_payment_fee').css({"border-color": "#bbb"});
				   		}
				   	}
				});

			}else{
				if(arrayInArray(currentVal, desti)) {

					$(id+' .select_box.select_destination').val('');
					$(id+' .select_box.select_destination').tooltip({
	                	trigger: "change",
	                    placement: "right",
	                    template: '<div class="tooltip tooltip-error" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
	                }).attr("data-original-title", errMsg(commonMsg.JSE054,[homepayment+' ( '+destination+' )']));
	                
	                $(id+' .select_box.select_destination').tooltip("show");
	                $(id+' .select_box.select_destination').css({"border": "1px","border-style": "groove","border-color": "#f31515"});
			    	return false;
				}else {
					
					$(id+' .select_box.select_destination').tooltip("destroy");
					$(id+' .select_box.select_destination').css({"border-color": "#bbb"});
				}
			}
		
		});
		var delTableId = 0;
		//Delete table
		$('.table-group').on('click','.delete_table_btn', function() {
			var parentTable = $(this).closest('table');
			delTableId = parentTable.attr('id').split('_');
			$.confirm({           
				title: '<?php echo __("削除確認");?>',                  
				icon: 'fas fa-exclamation-circle',                  
				type: 'green',                  
				typeAnimated: true, 
				closeIcon: true,
				columnClass: 'medium',                
				animateFromElement: true,                 
				animation: 'top',                 
				draggable: false,                 
				content: errMsg(commonMsg.JSE017),               
				buttons: {                    
					ok: {                 
						text: '<?php echo __("はい");?>',                 
						btnClass: 'btn-info',                 
						action: function(){ 
							parentTable.prop('id', 'delete_tbl');
							parentTable.prop('class', 'delete_tbl');
							// total table amount - remove table amount
							$('#delete_tbl .amount_input').each(function(i,v) {
								var class_name = $(this).prop('class');
								var check_cn = (class_name.substring(class_name.indexOf("rec") - 1)).trim();
								var id = v['id'];
								var idArr = PrepareForId(id);
								if(id == check_cn || check_cn.indexOf(id) != -1) idArr.length = 8;
								
								$.each(idArr, function(key, value) {
									var tothidId = value.replace('#', '#hid_'); // total id
									if(id.split("_")[0] != "hid") {
										if($(tothidId).html() !== undefined){
											var removeVal = $('#hid_'+id).val().replace(/,/g, '');
											var totalVal = $(tothidId).html().replace(/,/g, '');
											var total = (parseFloat(totalVal) - parseFloat(removeVal));
											$(value).html(formatNumber(total));
											$(tothidId).html(total);
										}
									}
								});
							});
							// when delete table, reorder the table id
							$('.sub-tbl').each(function(a,b) {
								var num = a+1;
								var oldid = this.id;
								var prevnum = parseInt( oldid.match(/\d+/g), 10 );
								
								var newID = oldid.replace("table_"+prevnum, "table_"+num);
								$(this).prop('id', newID);
								$('#table_'+num+' tr td').each(function(index, value) {

									$(this).find('.amount_input, .kpi_amount_input').each(function(idx, val){
										
										var id = $(this).prop('id');
										var newID = id.replace("rec_"+prevnum, "rec_"+num); 
										$(this).prop('id', newID);
										
										var name = $(this).attr('name');
										var newName = name.replace("trade["+prevnum+"]", "trade["+num+"]");
										$(this).attr('name', newName); 

										var class_name = $(this).prop('class');
										var check_cn = (class_name.substring(class_name.indexOf("rec") - 1)).trim();
										if(id == check_cn || check_cn.indexOf(id) != -1) {
											var newClass = class_name.replace("rec_"+prevnum, "rec_"+num);
											$(this).prop('class', newClass);
										}
										
									});
									
									$(this).find('.logistic_index').each(function(){
										var log_id = $(this).prop('id');
										var logID = log_id.replace("logistic_index_"+prevnum, "logistic_index_"+num);
										$(this).prop('id', logID);
										var log_name = $(this).attr('name');
										var logName = log_name.replace("trade["+prevnum+"]", "trade["+num+"]");
										$(this).attr('name', logName);
									});
									
									$(this).find('.kpi_unit_input').each(function(){
										var unit_id = $(this).prop('id');
										var unitID = unit_id.replace("kpi_unit_input"+prevnum, "kpi_unit_input"+num);
										$(this).prop('id', unitID);
										var unit_name = $(this).attr('name'); 
										var unitName = unit_name.replace("trade["+prevnum+"]", "trade["+num+"]");
										$(this).attr('name', unitName);
									});
									$(this).find('.home_payment_fee').each(function(){
										
										var sel_id = $(this).prop('id');
										var selId = sel_id.replace("fee_name_"+prevnum, "fee_name_"+num);
										$(this).prop('id', selId);

										var sel_name = $(this).attr('name');
										var selName = sel_name.replace("trade["+prevnum+"]", "trade["+num+"]");
										$(this).attr('name', selName);
									
									});
									$(this).find('.select_destination').each(function(){
										
										var sel_id = $(this).prop('id');
										var selId = sel_id.replace("select"+prevnum, "select"+num);
										$(this).prop('id', selId);

										var sel_name = $(this).attr('name');
										var selName = sel_name.replace("trade["+prevnum+"]", "trade["+num+"]");
										$(this).attr('name', selName);
									
									});
									
									$(this).find('input[type=button]').each(function(){
										
										var btn_id = $(this).prop('id');
										var btnId = btn_id.replace("table_"+prevnum, "table_"+num);
										$(this).prop('id', btnId);
										
										var btn_name = $(this).attr('name');
										var btnName = btn_name.replace("table_"+prevnum, "table_"+num); 
										$(this).attr('name', btnName);
										
									});
								});
								
								$('#table_'+num+' tr td.total_field').each(function(idx, val){
									
									var id = $(this).prop('id');
									var newID = id.replace("sum_"+prevnum, "sum_"+num);
									$(this).prop('id', newID);
									
								});
							});
							 
							parentTable.empty();
							var tblCount = $('table[id^="table_"]').length; //get table count

							if (tblCount == 1) { //check if only one table
								//disable the button if only one table
								$('.delete_table_btn').each(function () {
									$(this).prop('disabled', true);
								})
							}
							
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

					},                
				},                  
				theme: 'material',                  
				animation: 'rotateYR',                  
				closeAnimation: 'rotateXR'                  
			});

		});
		// set logistics, kpi_unit_input, destination in hidden fields
		$('.table-group').on('change','.logistic_index, .kpi_unit_input, .select_destination, .home_payment_fee', function(){
			var id = this.id;
			$("#hid_"+id).val($(this).val());
		});
		// logistics dropdown list
		/*$('.table-group').on('click','.logistic_index', function() {
			var logi_dd_list = <?php echo json_encode($logistic_data); ?>;
			var selected_logi = [];var result = [];
			$('.table-group').find('.logistic_index').each(function(){
				selected_logi[$(this).val()] = $(this).val();
				
			});
			
			for(var logi in logi_dd_list) {
		    	if (typeof selected_logi[logi_dd_list[logi]] === 'undefined') {
		      		result.push(logi_dd_list[logi]);
		    	}
		  	}
		  	
			$('#'+this.id).children('option[value!="'+$('#'+this.id).val()+'"]').remove();
			$('#'+this.id).children('option[value=""').remove();
			$('#'+this.id).append($('<option value="">---- Select Logistic Index ----</option>'));
			$.unique(result);
			Object.keys(result).forEach(key => {
			 	var option = document.createElement("option");
				option.text = result[key];
				option.value = result[key];

				$('#'+this.id).append(option);
			});
			
		});*/
		$('.table-group').on('mouseenter','.logistic_index', function() {
			var logi_dd_list = <?php echo json_encode($logistic_data); ?>;
			var selected_logi = [];var result = [];
			$('.table-group').find('.logistic_index').each(function(){
				selected_logi[$(this).val()] = $(this).val();
			});
			if($("#hid_"+this.id).val() != '') result.push($("#hid_"+this.id).val());
			for(var logi in logi_dd_list) {
				
		    	if (typeof selected_logi[logi_dd_list[logi]] === 'undefined') {
		      		result.push(logi_dd_list[logi]);
		    	}
		  	}

		  	$('select#'+this.id+' option[value!=""]').remove();
		  	
			for(var re in result) {
				
				if($("#hid_"+this.id).val() == result[re]) {
					var sel = 'selected';
					
				}else {
					var sel = '';
				}
				
				$('#'+this.id).append($('<option value="'+result[re]+'"' +sel+' >'+result[re]+'</option>'));
			}
			var select = $('select#'+this.id);
  			select.html(select.find('option').sort(function(x, y) {
		    	return $(x).text() > $(y).text() ? 1 : -1;
		  	}));
					
		});
	});
	
	function PrepareForId(id) {
		var colName = '';
		var idArr = [];
		var arr = id.split("_"); //split into array	
		if(arr[0] != 'hid') {
			if(arr[5] == "tr") {
				var tr = "_tr_"+arr[6];
			}else {	
				var tr = "";
			}
			var table_no = arr[1];	
			var month = arr[2];
			var sub_acc_id = arr[3];
			var acc_code = arr[4];
			var allMonth = <?php echo json_encode($months); ?>; //get all month

			if ( allMonth.indexOf(month) < 6 ) {
				colName = 'first_half';
			} else {
				colName = 'second_half';
			}
			
			idArr = PrepareForLoop(table_no, month, sub_acc_id, acc_code, colName, idArr, tr);
		}
		return idArr;
	}

	function PrepareForLoop(table_no, month, sub_acc_id, acc_code, colName, idArr, tr) {
		// For Each Table
		
		idArr.push('#sum_'+table_no+'_'+month+'_'+sub_acc_id);//sum_1_6月_1
		idArr.push('#sum_'+table_no+'_'+month+'_'+3);//sum_1_6月_3(売上総利益)
		idArr.push('#sum_'+table_no+'_'+colName+'_'+sub_acc_id+'_'+acc_code+tr);//sum_1_first_half_1_6002000000
		idArr.push('#sum_'+table_no+'_whole_total_'+sub_acc_id+'_'+acc_code+tr);//sum_1_whole_total_1_6002000000

		idArr.push('#sum_'+table_no+'_'+colName+'_'+sub_acc_id);//sum_1_first_half_1			
		idArr.push('#sum_'+table_no+'_whole_total_'+sub_acc_id);//sum_1_whole_total_1
		idArr.push('#sum_'+table_no+'_'+colName+'_'+3);//sum_1_first_half_3(売上総利益)
		idArr.push('#sum_'+table_no+'_whole_total_'+3);//sum_1_first_half_3(売上総利益)

		//For Summary Table
		idArr.push('#sum_'+month+'_'+sub_acc_id+'_'+acc_code);//sum_6月_1_6002000000
		idArr.push('#sum_'+month+'_'+sub_acc_id);//sum_6月_1
		idArr.push('#sum_'+month+'_'+3);//sum_6月_3(売上総利益)
		idArr.push('#sum_'+colName+'_'+sub_acc_id+'_'+acc_code);//sum_first_half_1_6002000000
		idArr.push('#sum_whole_total_'+sub_acc_id+'_'+acc_code);//sum_whole_total_1_6002000000
		idArr.push('#sum_'+colName+'_'+sub_acc_id);//sum_first_half_1
		idArr.push('#sum_whole_total_'+sub_acc_id);//sum_whole_total_1
		idArr.push('#sum_'+colName+'_'+3);//sum_first_half_3(売上総利益)
		idArr.push('#sum_whole_total_'+3);//sum_first_half_3(売上総利益)
		return idArr;
	}

	function formatNumber(num) {
		if(num == ''){
			num = 0;
			return num.toFixed(1);
		}else{
			if(num.toString().indexOf('.') != -1) {
				num = Math.round(num * 10) / 10;
				var numArr = num.toString().split('.');
				var value = numArr[0];
				var value = value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
				if(numArr[1]) {
					return value+"."+numArr[1];
				}else {
					return value+".0";
				}
			}else{
				var value = num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
				return value+'.0';
			} 
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
     
    function CheckBANull(val) {

       	var trid = val.closest('tr')[0]['id'];
       	var tblid = val.closest('table')[0]['id'];
       	var inputid = val.closest('.amount_input')[0]['id'];
       	var hidID = ("#hid_"+inputid);
       	if(trid != "") {
			var des = $('#'+tblid+' #'+trid+' .select_destination').val();
			var hid_des = $('#'+tblid+' #'+trid+' .hid.select_destination').val();
			if(des == "" && hid_des != 1) {
                $("#" + inputid).tooltip({
                	trigger: "focus",
                    placement: "top",
                    template: '<div class="tooltip tooltip-error" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                }).attr("data-original-title", errMsg(commonMsg.JSE002,['<?php echo __("相手先")?>']));
                $("#" + inputid).tooltip("show");
                val.css({"border": "1px","border-style": "groove","border-color": "#f31515"});
				val.val(val.val().replace(val.val(), ''));
				$(hidID).val($(hidID).val().replace($(hidID).val(), ''));
			    val.trigger('change');
			    val.trigger('focusin');
		    	return false;
			}else {
				$("#" + inputid).tooltip("destroy");
				val.css({"border": "0px"});
			}
		}
    } 
    
    function CheckLimit(num) {
    	if(num > 10) {
    		$("#error").empty();
			$("#success").empty();
			$("#error").html(errMsg(commonMsg.JSE053,['(10)'])).show();
		   	scrollText();
		   	return false;
		}else {
			return true;
		}
    }

    function scrollText(){
    	var tes1 = $('#error').text();
    	var tes2 = $('#success').text();
		if(tes1){
			$("html, body").animate({ scrollTop: 0 }, "slow");				
		}
		if(tes2){
			$("html, body").animate({ scrollTop: 0 }, "slow");				
		}
   	}

   	function getDestArray(tblid, selectDisid, selectHomeid) {
		var destin = [];
		var idDis = '#'+tblid+' .select_destination';
		var idHomeFee = '#'+tblid+' .home_payment_fee';
		var cnt = 0;

		$(idDis).each(function(i, v){
			
			var hValue = [];
			var dValue = [];

			if(selectDisid != v['id']) { // without current select destination

				var ddd = $('#'+v['id']).val();

				$(idHomeFee).each(function(a, b){
					
					var hval = $('#'+b['id']).val();
					
					hValue.push(hval);
					
				});
				
				dValue.push(hValue[cnt]);
				dValue.push(ddd);
			}

			destin.push(dValue);
			
			cnt++;	
		});
		
		return destin;
	}
	function arrayInArray(needle, haystack) {
		
		let i = 0, len = haystack.length;
		for(; i<len; i++ ){
			
				if(haystack[i].toString() === needle['0'].toString()){
					
					return true;
				}
				
		}
		 
		return false;
	}
	function currentValDelete(needle, haystack){

		var newArray = [];
		let i = 0, len = haystack.length;
		for(; i<len; i++ ){
		
			if(haystack[i].toString() !== needle['0'].toString()){
				newArray.push(haystack[i]);
			}
			
		}

		return newArray;
	}

</script>
</body>
</html>
