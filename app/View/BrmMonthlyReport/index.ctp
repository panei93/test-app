<style type="text/css">
	.btn_group{
		padding-left: 165px;
	}
	#tbl_monthly_rp td,th{
		padding: 5px;
	}
	#tbl_next3_month_forecast th {
		padding: 5px;
	}
	
	#tbl_next3_month_forecast input {
		border:none;
		width:100%;
	    font-size: 14px;
	    border-radius: 0;
	   
	}

	#tbl_next3_month_forecast input:focus { 
		
		outline: none;
	}
	fieldset.scheduler-border{
		border: 1px solid #ccc !important;
		padding: 12px !important;
	}
	.line{
		
		margin-top: -35px;
    	margin-left: 3px;
	}
	@media only screen and (max-width: 1000px) {

        .btn_group {
            padding-left: 30px;
            margin-top: 55px;
        }
    }

    @media only screen and (max-width: 767px) {

        .btn_group {
            padding-left: 30px;
            margin-top: 20px;
        }
    }
    @media only screen and (max-width: 991px) {
     	.next3MonthFieldset{
     		padding-left: 15px !important;
     	}
       
    }
    .btn-delete {
		padding-right: 20px;
		padding-left: 4px;
		color: red;		
	}
	.btn-delete:hover {
		cursor: pointer;
	}
	.negative {
		color: #f31515;
		text-align: right;
	}
	.right{
		text-align: right;
	}
	
	.info{
		color: #f31515;
		font-size: 0.8em;
	}
	
	.row_span {
		background-color: #fff;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control, .total_table_row {
		background-color: #F9F9F9;
		opacity: 1;
	}
	.row{
		margin-right: 0px;
	}
	textarea {
		resize: vertical;
	}
	.textarea_wpr {
		width: 100%;
		overflow: hidden;
	}
	.ui-resizable-se {
		right: 32px;
		bottom: 29px;
	}
	.upload-div {
		margin-bottom: 10px;
	}
</style>
<?php

    echo $this->element('autocomplete', array(
                        "to_level_id" => "",
                        "cc_level_id" => "",
                        "bcc_level_id" => "",
                        "submit_form_name" => "monthly_report_form",
                        "MailSubject" => "",
                        "MailTitle"   => "",
                        "MailBody"    =>""
                     ));
?>
<?php echo $this->Form->create(false,array('url'=>'/BrmMonthlyReport','type'=>'post','class'=>'','name'=>'monthly_report_form','id'=>'monthly_report_form','enctype' => 'multipart/form-data')); ?>
<input type="hidden" name="toEmail" id="toEmail" value="">
<input type="hidden" name="ccEmail" id="ccEmail" value="">
<input type="hidden" name="bccEmail" id="bccEmail" value="">
<input type="hidden" name="mailSubj" id="mailSubj">
<input type="hidden" name="mailBody" id="mailBody">
<input type="hidden" name="dataArr" id="dataArr">
<input type="hidden" name="path" id="path">
<input type="hidden" name="mailSend" id="mailSend">
<div class="container register_container">
	<div id="overlay">
		<span class="loader"></span>
	</div>
	<div class="row">
		<h3 style="margin-left: 10px;"><?php echo __("Monthly Report"); ?></h3>
		<hr>
		<div class="col-md-12">
			<div class="success" id="success"><?php echo $successMsg;?></div>
			<div class="error" id="error"><?php echo $errorMsg;?></div>
		</div>
		<div class="errorSuccess">
	        <div class="success" id="success"><?php echo ($this->Session->check("Message.mty_rp_ok"))? $this->Flash->render("mty_rp_ok") : ''; ?></div>
	        <div class="error" id="error"><?php echo ($this->Session->check("Message.mty_rp_fail"))? $this->Flash->render("mty_rp_fail") : ''; ?></div>
		</div>
		<div class="form-group row">
			<div class="col-sm-6">
				<label for="budget_term" class="col-sm-4 col-form-label">
					<?php echo __('期間');?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="txt_budget_term" name="txt_budget_term" value="<?php echo $budget_term; ?>" readonly/>
				</div>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-6">
				<label for="lbl_terget_month" class="col-sm-4 col-form-label">
					<?php echo __('対象月');?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="txt_terget_month" name="txt_terget_month" value="<?php echo $target_month; ?>" readonly/>
				</div>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-6">
				<label for="lbl_headquarter" class="col-sm-4 col-form-label">
					<?php echo __($layer_types[SETTING::LAYER_SETTING['topLayer']]);?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="txt_headquarter" name="txt_headquarter" value="<?php echo $headquarter; ?>" readonly/>
				</div>
			</div>
		</div>

		<?php 

			$MM1 = $master_array[0]['next_1m'];
			$MM2 = $master_array[0]['next_2m'];
			$MM3 = $master_array[0]['next_3m'];
			$flag_arr = $master_array[0]['flag_arr'];
	
			$hq_approve_finish = (isset($flag_arr[0])) ? $flag_arr[0] : 0 ;
			$head_data = $master_array[0]['head_data'];
			$head_dept_code = $head_data['hlayer_code'];

			
		?>

		<?php if (empty($head_data)): ?>
			<div class="col-sm-12">
				
				<p class="no-data"><?php echo __("計算する為のデータがシステムにありません。"); ?></p>
			</div>
		<!-- All headdepartments will show for Finance Department's user even it has been approved or not  -->
		<!-- If not Finance Person, show headdepartment data after approve finished -->
		
		<?php elseif ($read_limit == 0 || in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code'))|| $hq_approve_finish == '2'): ?>
			
			<div class="row" style="margin-bottom: 20px;">
				<div style="text-align: right;">
					<input type="button" name="btn_excel" id="btn_excel" class="btn btn-success" value="<?php echo __("Excel Download") ?>" onClick = "excelDownload();">	
				</div>
			</div>
			
			<?php 
				#Headquarter 
				if(!empty($head_data)){
					?>
					<!-- Total Result Overview Head null validation when data does not have-->
					<?php 
					if (!empty($head_data['overview_cmt'])) {	

				    	$head_data_overview_cmt = $head_data['overview_cmt'];
					}
					if ($head_data_overview_cmt =='NULL') {
						$head_data_overview_cmt ="";
					}				
					?>
					
					<div class="headquarterFirstSession">
						<button type="button" class="btn btn-success drop_down_arrow <?php echo("drop_down_arrow_".$head_data['hlayer_code']); ?>"  data-id="<?php echo($head_data['hlayer_code']) ?>" data-toggle="collapse" data-target="<?php echo '#div_headquarter_'.$head_data['hlayer_code']; ?>"><i class='fas fa-chevron-up' style='font-size:18px'></i></button>&nbsp;&nbsp;

						<label style="font-size: 1.1em;"><?php echo $head_data['hlayer_name']; ?></label><hr class="line">
						<div id="<?php echo 'div_headquarter_'.$head_data['hlayer_code']; ?>" class="collapse in">
							<div class="row">
								<div class="col-md-9"></div>
								<div class="col-md-3 head_btn_group" style="text-align: right;">
									
									<?php  
									$get_flag = (isset($flag_arr[0])) ? $flag_arr[0] : 0 ; # for dept 0
									?>

									<!-- Approve -->

									<?php if ($get_flag == '0' || $get_flag == "1"): ?>
										<?php if ($create_limit=='0' || (($create_limit == SETTING::LAYER_SETTING['topLayer'] || $create_limit== SETTING::LAYER_SETTING['middleLayer']) && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code'))))):  ?>

											<input type="button" name="btn_save" id="btn_save" class="btn btn-success" value="<?php echo __("Save") ?>" onClick = "saveHead('0','<?php echo $head_data['hlayer_name']; ?>')">&nbsp;&nbsp;
											<?php if (($approve_limit == '0' || ($approve_limit == SETTING::LAYER_SETTING['topLayer'] && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code')))))&& $approvePermit == "Yes"): ?>
												<input type="button" name="btn_approve	" id="btn_approve" class="btn btn-success" value="<?php echo __("Approve") ?>" onClick = "approveHead('0','<?php echo $head_data['hlayer_name']; ?>')">&nbsp;&nbsp;
											<?php endif ?>
										<?php endif ?>
										
									<?php elseif ($get_flag=="2"): ?>
											
											<?php if (($approve_limit=='0' || ($approve_limit== SETTING::LAYER_SETTING['topLayer'] && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code'))))) && $approvePermit == "Yes" || ($get_flag == "2" && $approvePermit == "No")): ?>
												<input type="button" name="btn_approve_cnl" id="btn_approve_cnl" class="btn btn-success" value="<?php echo __("Approve Cancel") ?>" onClick = "approveCancelHead('0','<?php echo $head_data['hlayer_name']; ?>')">
											<?php endif ?>
									<?php endif ?>

								</div>
							</div>

							<?php $h_year = 0; ?> <!--#For "3. 今後3ヶ月の業績予想と年間見込" of year "D" -->
							<div class="row" style="padding: 30px 11px 11px 15px;">
								<label for="lbl_achievements"><?php echo __('1. 業績');?></label>
							</div>
						   	<div class="table-responsive tbl-wrapper">
								<table class="table-bordered" id="tbl_monthly_rp" style="min-width:1000px; width:100%;">
									<thead class="check_period_table">
										<tr>
											
											<th colspan="2" rowspan="2" style="width: 20%"></th>
											<th colspan="5" class="table-middle" style="vertical-align : middle;text-align:center; padding: 5px 0px;"><?php echo __("当月"); ?></th>
											<th colspan="5" class="table-middle" style="vertical-align : middle;text-align:center; padding: 5px 0px;"><?php echo __("累計"); ?></th>
											<th colspan="2" class="table-middle" style="vertical-align : middle;text-align:center; padding: 5px 0px;"><?php echo __("Budget"); ?></th>
											
										</tr>
										<tr>
										
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("Budget"); ?></th>
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("実績"); ?></th>
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("予実比"); ?></th>				
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("前年実績"); ?></th>				
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("前年同月比"); ?></th>				
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("Budget"); ?></th>
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("実績"); ?></th>
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("予実比"); ?></th>
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("前年実績"); ?></th>
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("前年同月比"); ?></th>
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("年間"); ?></th>	
											<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("進捗率"); ?></th>
											
										</tr>
									</thead>
									<tbody>

										<?php  
											
										foreach ($head_data['data'] as $key => $each_sub) {  	
											foreach ($each_sub as $sub_key => $sub_data) {
												
												if($sub_key == "本部合計"){?>
													<tr class="total_table_row">
														<th class="row_span" rowspan="<?php echo count($each_sub); ?>" class="table-middle" style="vertical-align: middle;word-wrap: break-word; width: 11%;"><?php echo ($key); ?></th>
														<td class="table-middle" style="word-wrap: break-word; vertical-align: middle;"><?php echo $sub_key;?></td>
												<?php } else {?>
													<tr>
														<td class="table-middle" style="word-wrap: break-word; vertical-align: middle; padding-left: 15px;"><?php echo $sub_key;?></td>
												<?php } ?>

														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo number_format(round($sub_data['tm_budget']/1000000)); ?></td>
														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo number_format(round($sub_data['tm_result']/1000000)); ?></td>
														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo number_format(round($sub_data['tm_ratio']/1000000)); ?></td>
														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo number_format(round($sub_data['tm_previous_y_r']/1000000)); ?></td>
														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo number_format(round($sub_data['tm_yoy_change']/1000000)); ?></td>
														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo number_format(round($sub_data['total_tm_budget']/1000000)); ?></td>
														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo number_format(round($sub_data['total_tm_result']/1000000)); ?></td>
														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo number_format(round($sub_data['total_ratio']/1000000)); ?></td>
														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo number_format(round($sub_data['previous_y_r']/1000000)); ?></td>
														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo number_format(round($sub_data['yoy_change']/1000000)); ?></td>
														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo number_format(round($sub_data['yearly_budget']/1000000)); ?></td>
														<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo $sub_data['achieve_rate']; ?></td>
													</tr>

										
										<?php 
											}
										}
										 ?>
					
									</tbody>
								</table>
							</div>
							
							<div class="row">
								<div class="col-md-6" style="padding-left: 28px;">
									<div class="row" style="padding: 16px 11px 11px 2px;">
										<label for="lbl_tolResOvrview"><?php echo __('2. 単月・累計実績　概況説明');?></label>
									</div>
									<div class="row">
										<?php if ($get_flag == '0' || $get_flag == "1"): ?>
											<?php if ($create_limit=='0' || (($create_limit== SETTING::LAYER_SETTING['topLayer'] || $create_limit== SETTING::LAYER_SETTING['middleLayer']) && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code'))))): ?>
												<textarea class="form-control txtara_ovrview_0 resizable_txtarea" id = "txtara_ovrview_0" rows="12" cols="40" maxlength="2500" placeholder= "単月・累計実績 概況説明を入力してください。"><?php echo h($head_data_overview_cmt); ?></textarea>
											<?php else: ?>
											<textarea class="form-control txtara_ovrview_0 resizable_txtarea" readonly id = "txtara_ovrview_0" rows="12" cols="40" maxlength="2500" placeholder= "単月・累計実績 概況説明を入力してください。"><?php echo h($head_data_overview_cmt); ?></textarea>
											<?php endif ?>
										<?php else: ?>											
											<textarea class="form-control txtara_ovrview_0 resizable_txtarea" readonly id = "txtara_ovrview_0" rows="12" cols="40" maxlength="2500" placeholder= "単月・累計実績 概況説明を入力してください。"><?php echo h($head_data_overview_cmt); ?></textarea>
										<?php endif ?>
									</div>
									
								</div>
								<div class="col-md-6 next3MonthFieldset" style="padding-left: 28px;">
									<div class="row" style="padding: 16px 11px 11px 15px;">
										<label for="lbl_next3MonthForecast"><?php echo __('3. 今後3ヶ月の業績予想と年間見込');?></label>
									</div>
									<fieldset class="scheduler-border form-control">
										<br>
										<div class="table-responsive tbl-wrapper">
											<table class="table-bordered" id="tbl_next3_month_forecast" style="min-width:300px;" >
												<thead class="check_period_table">
													<tr>
														<th class=""  style="vertical-align : middle;text-align:center; width: 15%;"></th>
														<th class=""  style="vertical-align : middle;text-align:center; width: 15%;"><?php echo __($MM1); ?></th>
														<th class=""  style="vertical-align : middle;text-align:center; width: 15%;"><?php echo __($MM2); ?></th>
														<th class=""  style="vertical-align : middle;text-align:center; width: 15%;"><?php echo __($MM3); ?></th>		
														<th class=""  style="vertical-align : middle;text-align:center; width: 15%;"><?php echo __("年間"); ?></th>
													</tr>	
												</thead>
												<tbody>
													<tr>

														<td class=""  style="vertical-align : middle;text-align:left;width: 15%; padding: 5px;"><?php echo __("当期利益(予算)");?>
															
														</td>
														<td class="right" id= "month_1_budget_0" style="padding: 0px 5px; vertical-align : middle; width: 15%;"><?php echo number_format($head_data['annual_budget']['h_next_budget']); ?></td>
														<td class="right" id= "month_2_budget_0" style="padding: 0px 5px; vertical-align : middle; width: 15%;"><?php echo number_format($head_data['annual_budget']['h_next2month_budget']); ?></td>
														<td class="right" id= "month_3_budget_0" style="padding: 0px 5px; vertical-align : middle; width: 15%;"><?php echo number_format($head_data['annual_budget']['h_next3month_budget']); ?></td>
														<td class="right" id= "year_budget_0" style="padding: 0px 5px; vertical-align : middle; width: 15%;"><?php echo number_format($head_data['annual_budget']['h_yearly_budget']); ?></td>

													</tr>

													<?php  

													$h_month1 = $head_data['forecast']['month1_forecast'];
													$h_month2 = $head_data['forecast']['month2_forecast'];
													$h_month3 = $head_data['forecast']['month3_forecast'];
													$h_yearly = $head_data['forecast']['yearly_forecast'];

													if(!empty($h_month1)){
														$h_month1 = number_format($h_month1);
														$h_month1 = str_replace('-','▲',$h_month1);
														
													}
													if(!empty($h_month2)){
														$h_month2 = number_format($h_month2);
														$h_month2 = str_replace('-','▲',$h_month2);
													}
													if(!empty($h_month3)){
														$h_month3 = number_format($h_month3);
														$h_month3 = str_replace('-','▲',$h_month3);
													}
													if(!empty($h_yearly)){
														$h_yearly = number_format($h_yearly);
														$h_yearly = str_replace('-','▲',$h_yearly);
													}
													?>
												
													<tr>
														<td class=""  style="vertical-align : middle;text-align:left; width: 15%;">
															<?php echo __("当期利益(予測)"); ?> 
															<br/>
															<span class="info">
																<?php echo __("※百万円単位"); ?>
															</span>
														</td>
														<?php if ($get_flag == '0' || $get_flag == "1"): ?>
															<?php if ($create_limit=='0' || (($create_limit== SETTING::LAYER_SETTING['topLayer'] || $create_limit== SETTING::LAYER_SETTING['middleLayer']) && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code'))))): ?>
																<td class=""  style="width: 15%;"> 
																	<input type="text" class = "forecast form-control right" id = "<?php echo("month_1_0") ?>" value = "<?php echo $h_month1; ?>" maxlength = "6">
																</td>
																<td class=""  style="vertical-align : middle; width: 15%;">
																	<input type="text" class = "forecast form-control right" id = "<?php echo("month_2_0") ?>" value = "<?php echo $h_month2; ?>" maxlength = "6">
																</td>
																<td class=""  style="width: 15%;">
																	<input type="text" class = "forecast form-control right" id = "<?php echo("month_3_0") ?>" value = "<?php echo $h_month3; ?>" maxlength = "6">
																</td>
																<td class=""  style="width: 15%;">
																	<input type="text" class = "forecast form-control right" id = "<?php echo("Year_0") ?>" value = "<?php echo $h_yearly; ?>" maxlength = "7">
																</td>
															<?php else: ?>
																<td class=""  style="width: 15%;">
																	<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_1_0") ?>" value = "<?php echo $h_month1; ?>" maxlength = "6">
																</td>
																<td class=""  style="vertical-align : middle; width: 15%;">
																	<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_2_0") ?>" value = "<?php echo $h_month2; ?>" maxlength = "6">
																</td>
																<td class=""  style="width: 15%;">
																	<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_3_0") ?>" value = "<?php echo $h_month3; ?>" maxlength = "6">
																</td>
																<td class=""  style="width: 15%;">
																	<input type="text" readonly class = "forecast form-control right" id = "<?php echo("Year_0") ?>" value = "<?php echo $h_yearly; ?>" maxlength = "7">
																</td>
															<?php endif ?>
														<?php else: ?>	
															<td class=""  style="width: 15%;">
																<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_1_0") ?>" value = "<?php echo $h_month1; ?>" maxlength = "6">
															</td>
															<td class=""  style="vertical-align : middle; width: 15%;">
																<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_2_0") ?>" value = "<?php echo $h_month2; ?>" maxlength = "6">
															</td>
															<td class=""  style="width: 15%;">
																<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_3_0") ?>" value = "<?php echo $h_month3; ?>" maxlength = "6">
															</td>
															<td class=""  style="width: 15%;">
																<input type="text" readonly class = "forecast form-control right" id = "<?php echo("Year_0") ?>" value = "<?php echo $h_yearly; ?>" maxlength = "7">
															</td>										
														<?php endif ?>
													</tr>

												</tbody>
											</table>
											<br>
											<div class="textarea_wpr">
												<?php 
												 $headforecastRemark = $head_data['forecast']['remark'];
												?>
												<?php if ($get_flag == '0' || $get_flag == "1"): ?>
													<?php if ($create_limit=='0' || (($create_limit== SETTING::LAYER_SETTING['topLayer'] || $create_limit== SETTING::LAYER_SETTING['middleLayer']) && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code'))))): ?>
														<textarea class="form-control textarea_fc" rows="3" id="<?php echo("annual_prospects_0") ?>" maxlength="2500" placeholder= "コメントを入力してください。"><?php if($headforecastRemark != "NULL"){echo h($headforecastRemark);}?></textarea>
													<?php else: ?>
														<textarea class="form-control textarea_fc" readonly rows="3" id="<?php echo("annual_prospects_0") ?>" maxlength="2500" placeholder= "コメントを入力してください。"><?php if($headforecastRemark != "NULL"){echo h($headforecastRemark);}?></textarea>
													<?php endif ?>
												<?php else: ?>											
													<textarea class="form-control textarea_fc" readonly rows="3" id="<?php echo("annual_prospects_0") ?>" maxlength="2500" placeholder= "コメントを入力してください。"><?php if($headforecastRemark != "NULL"){echo h($headforecastRemark);}?></textarea>
												<?php endif ?>
											</div>
										</div>
									</fieldset>

								</div>
							</div>
							<?php if ($get_flag == '0' || $get_flag == "1"): ?>
								<?php if (($create_limit=='0' || (($create_limit== SETTING::LAYER_SETTING['topLayer'] || $create_limit==SETTING::LAYER_SETTING['middleLayer']) && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code')))))): ?>
									<div class="form-group row" style="padding-left: 13px;">					
										<label for="tolResOvrview" style="padding-bottom: 8px;"><?php echo __('4. 添付資料');?></label>
										
										<fieldset class="scheduler-border form-control" style="width: 80%;">
											<br>
											<div class="row">
												
												<div class="col-md-2 upload-div">
													<label style="color:white" id="btn_browse">Upload File

														<input type="file" name="uploadfile_0" class = "uploadfile uploadfile_0" data-id = "0" data-id-head = "<?php echo $head_data['hlayer_code']; ?>">
					                    	
							                    	</label>
							                    </div>
													
												<div class="col-md-9 ">
													<div class ="upd-file-name upd-file_0">
														<?php if(!empty($head_data['attached'])){
															foreach ($head_data['attached'] as $value) {
																?>
																<a href="#" class="down-link" name = "down-link" data-toggle = "tooltip" data-id = "0" data-value = "<?php echo $value; ?>" ><?php echo $value; ?></a> 
																
																	<span class="glyphicon glyphicon-remove-sign btn-delete" data-id = "0" data-id-head = "<?php echo $head_data['hlayer_code']; ?>" data-value = "<?php echo $value; ?>">
																	</span>		
																
																									
															<?php
															}
														} ?>
													</div>
												</div>
											</div>  
										</fieldset>
									</div>
								<?php else: ?>
									<?php if(!empty($head_data['attached'])){?>
										<div class="form-group row" style="padding-left: 13px;">					
											<label for="tolResOvrview" style="padding-bottom: 8px;"><?php echo __('4. 添付資料');?></label>
											
											<fieldset class="scheduler-border form-control" style="width: 80%;">
												<br>
												<div class="row">
															
													<div class="col-md-9 ">
														<div class ="upd-file-name upd-file_0">
															<?php if(!empty($head_data['attached'])){
																foreach ($head_data['attached'] as $value) {
																	?>
																	<a href="#" class="down-link" name = "down-link" data-toggle = "tooltip" data-id = "0" data-value = "<?php echo $value; ?>" ><?php echo $value; ?></a> 
																		&nbsp;
																									 
																<?php
																}
															} ?>
														</div>
													</div>
												</div>  
											</fieldset>
										</div>
									<?php } ?>
								<?php endif ?>
							<?php else: ?>
								<?php if(!empty($head_data['attached'])){?>
									<div class="form-group row" style="padding-left: 13px;">					
										<label for="tolResOvrview" style="padding-bottom: 8px;"><?php echo __('4. 添付資料');?></label>
										
										<fieldset class="scheduler-border form-control" style="width: 80%;">
											<br>
											<div class="row">
														
												<div class="col-md-9 ">
													<div class ="upd-file-name upd-file_0">
														<?php if(!empty($head_data['attached'])){
															foreach ($head_data['attached'] as $value) {
																?>
																<a href="#" class="down-link" name = "down-link" data-toggle = "tooltip" data-id = "0" data-value = "<?php echo $value; ?>" ><?php echo $value; ?></a> 
																	&nbsp;
															<?php
															}
														} ?>
													</div>
												</div>
											</div>  
										</fieldset>
									</div>
								<?php } ?>										
							<?php endif ?>
						</div>
					</div>
					<br><br>
				<?php }?>
				
				<!-- #Department Looping -->
				<?php
					
					$js_data = array(array('id'=>'0','name'=>$head_data['hlayer_name']));

					$d_year = 0; #For "3. 今後3ヶ月の業績予想と年間見込" of year "D" 

					if(!empty($master_array[0]['dept_data'])){
						$dept_data = $master_array[0]['dept_data'];
						
						foreach($dept_data as $each_dept => $d_value) {
							
							$each_dept_id 	= $d_value['id'];
							$each_dept_data = $d_value['data'];
							$each_budget 	= $d_value['annual_budget'];
							
							#For Javascript to Save 
							if(!empty($each_dept_data)){

								$js_dept = array();
								$js_dept['id'] 	 = $each_dept_id;
								$js_dept['name'] = $each_dept;
								array_push($js_data, $js_dept);

							}		
							
						?>

						<div class="div_dept" style="margin-left: 40px;">
							<button type="button" class="btn btn-success drop_down_dept <?php echo("drop_down_dept_".$each_dept_id); ?>" data-id="<?php echo($each_dept_id) ?>" data-toggle="collapse" data-target="<?php echo("#".$each_dept_id); ?>"><i class='fas fa-chevron-down' style='font-size:18px'></i></button>&nbsp;&nbsp;
							<label style="font-size: 1.1em;"><?php echo ($each_dept); ?></label><hr class="line">
							<div id="<?php echo($each_dept_id); ?>" class="collapse">
								<?php if(!empty($each_dept_data)){
									$get_flag = (isset($flag_arr[$each_dept_id])) ? $flag_arr[$each_dept_id] : 0 ;
									?>
									<div class="row">
										<div class="col-md-9"></div>
										<div class="col-md-3 dept_btn_group" style="text-align: right;">
											
											<?php if($hq_approve_finish != '2'): ?>
												<?php if ($get_flag == '0' || $get_flag == "1"): ?>
													<?php if ($create_limit=='0' || ($create_limit== SETTING::LAYER_SETTING['topLayer'] && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code')))) || ($create_limit== SETTING::LAYER_SETTING['middleLayer'] && (in_array($each_dept_id, array_column(array_column($get_permit_codes, 'second_layer'),'dlayer_code'))))): ?>
														<input type="button" name="btn_save" id="btn_save" class="btn btn-success" value="<?php echo __("Save") ?>" onClick = "saveHead('<?php echo($each_dept_id); ?>','<?php echo $each_dept; ?>')">&nbsp;&nbsp;
														<?php if ($approve_limit=='0' || ($approve_limit == SETTING::LAYER_SETTING['middleLayer'] && (in_array($each_dept_id, array_column(array_column($get_permit_codes, 'second_layer'),'dlayer_code'))))): ?>
															<input type="button" name="btn_approve" id="btn_approve" class="btn btn-success" value="<?php echo __("Approve") ?>" onClick = "approveHead('<?php echo($each_dept_id); ?>','<?php echo $each_dept; ?>')">&nbsp;&nbsp;
														<?php endif ?>
													<?php endif ?>
												<?php elseif ($get_flag=="2"): ?>	
													<?php if ($approve_limit=='0' || ($approve_limit== SETTING::LAYER_SETTING['middleLayer'] && (in_array($each_dept_id, array_column(array_column($get_permit_codes, 'second_layer'),'dlayer_code'))))): ?>
														<input type="button" name="btn_approve_cnl" id="btn_approve_cnl" class="btn btn-success" value="<?php echo __("Approve Cancel") ?>" onClick = "approveCancelHead('<?php echo($each_dept_id); ?>','<?php echo($each_dept); ?>')">
													<?php endif ?>
												<?php endif ?>
											<?php endif ?>
										</div>
									</div>

									<div class="row" style="padding: 30px 11px 11px 15px;">
										<label for="achievements"><?php echo __('1. 業績');?></label>
									</div>
								   	<div class="table-responsive tbl-wrapper">
										<table class="table-bordered" id="tbl_monthly_rp" style="min-width:1000px; width:100%;">
											
											<thead class="check_period_table">
												<tr>
													
													<th colspan="2" rowspan="2" style="width: 21%"></th>
													<th colspan="5" class="table-middle" style="vertical-align : middle;text-align:center;"><?php echo __("当月"); ?></th>
													<th colspan="5" class="table-middle" style="vertical-align : middle;text-align:center;"><?php echo __("累計"); ?></th>
													<th colspan="2" class="table-middle" style="vertical-align : middle;text-align:center;"><?php echo __("Budget"); ?></th>
													
												</tr>
												<tr>
												
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("Budget"); ?></th>
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("実績"); ?></th>
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("予実比"); ?></th>				
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("前年実績"); ?></th>				
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("前年同月比"); ?></th>				
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("Budget"); ?></th>
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("実績"); ?></th>
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("予実比"); ?></th>
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("前年実績"); ?></th>
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("前年同月比"); ?></th>
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("年間"); ?></th>	
													<th class="table-middle" style="vertical-align : middle;text-align:center; width: 6%;"><?php echo __("進捗率"); ?></th>
													
												</tr>
											</thead>
											<tbody>
												<?php 									
												
													foreach($d_value as $key => $value){
														
														foreach ($value as $sub_accname => $data_value) {
															
															foreach ($data_value as $ba_key => $ba_data) {
																$tm_budget 			= number_format(round($ba_data['tm_budget']/1000000));
																$tm_result 			= number_format(round($ba_data['tm_result']/1000000));
																$tm_ratio 			= number_format(round($ba_data['tm_ratio']/1000000));
																$tm_previous_y_r 	= number_format(round($ba_data['tm_previous_y_r']/1000000));
																$tm_yoy_change 		= number_format(round($ba_data['tm_yoy_change']/1000000));
																$total_tm_budget 	= number_format(round($ba_data['total_tm_budget']/1000000));
																$total_tm_result 	= number_format(round($ba_data['total_tm_result']/1000000));
																$total_ratio 		= number_format(round($ba_data['total_ratio']/1000000));
																$previous_y_r 		= number_format(round($ba_data['previous_y_r']/1000000));
																$yoy_change 		= number_format(round($ba_data['yoy_change']/1000000));
																$yearly_budget 		= number_format(round($ba_data['yearly_budget']/1000000));
																$achieve_rate 		= $ba_data['achieve_rate'];

																if($ba_key == "部合計"){?>
																	<tr class="total_table_row">
																		<td class="table-middle row_span" rowspan="<?php echo count($data_value); ?>" style="word-wrap: break-word; vertical-align: middle;"><?php echo $sub_accname; ?></td>
																		<td class="table-middle" style="word-wrap: break-word; vertical-align: middle;"><?php echo $ba_key; ?></td>
																<?php } else { ?>
																	<tr>
																		<td class="table-middle" style="word-wrap: break-word; vertical-align: middle;
																			padding-left: 15px;"><?php echo $ba_key; ?></td>
																<?php } ?>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($tm_budget); ?></td>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($tm_result); ?></td>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($tm_ratio); ?></td>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($tm_previous_y_r); ?></td>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($tm_yoy_change); ?></td>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($total_tm_budget); ?></td>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($total_tm_result); ?></td>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($total_ratio); ?></td>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($previous_y_r); ?></td>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($yoy_change); ?></td>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($yearly_budget); ?></td>
																		<td class="table-middle right" style="word-wrap: break-word; vertical-align: middle;"><?php echo ($achieve_rate); ?></td>
																	</tr>

																	<?php 
															}
														}	
														
													}										
														
												?>									
											</tbody>
										</table>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="padding-left: 28px;">
											<div class="row" style="padding: 16px 11px 11px 2px;">
												<label for="tolResOvrview"><?php echo __('2. 単月・累計実績　概況説明');?></label>
											</div>
											<div class="row">

												<?php 
													$overview_cmt = ($d_value['overview_cmt'] != "NULL")? $d_value['overview_cmt']:"";
												?>	
												<?php if ($get_flag == '0' || $get_flag == "1"): ?>
													<?php if ($create_limit=='0' || ($create_limit== SETTING::LAYER_SETTING['topLayer'] && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code')))) || ($create_limit==SETTING::LAYER_SETTING['middleLayer'] && (in_array($each_dept_id, array_column(array_column($get_permit_codes, 'second_layer'),'dlayer_code'))))): ?>
														<textarea class="form-control resizable_txtarea_dept" id = "<?php echo('txtara_ovrview_'.$each_dept_id); ?>" placeholder= "単月・累計実績 概況説明を入力してください。" rows="12" cols="40"><?php echo $overview_cmt; ?></textarea>
													<?php else: ?>
														<textarea class="form-control resizable_txtarea_dept" id = "<?php echo('txtara_ovrview_'.$each_dept_id); ?>" placeholder= "単月・累計実績 概況説明を入力してください。" rows="12" cols="40" readonly><?php echo $overview_cmt; ?></textarea>
													<?php endif ?>
												<?php else: ?>											
													<textarea class="form-control resizable_txtarea_dept" id = "<?php echo('txtara_ovrview_'.$each_dept_id); ?>" placeholder= "単月・累計実績 概況説明を入力してください。" rows="12" cols="40" readonly><?php echo $overview_cmt; ?></textarea>
												<?php endif ?>
											</div>
											
										</div>
										<div class="col-md-6 next3MonthFieldset" style="padding-left: 28px;">
											<div class="row" style="padding: 16px 11px 11px 15px;">
												<label for="next3MonthForecast"><?php echo __('3. 今後3ヶ月の業績予想と年間見込');?></label>
											</div>
											<fieldset class="scheduler-border form-control">
												<br>
												<div class="table-responsive tbl-wrapper">
													<table class="table-bordered" id="tbl_next3_month_forecast" style="min-width:300px;" >
														<thead class="check_period_table">
															<tr>
																<th class=""  style="vertical-align : middle;text-align:center; width: 17%;"></th>
																<th class=""  style="vertical-align : middle;text-align:center; width: 15%;"><?php echo __($MM1); ?></th>
																<th class=""  style="vertical-align : middle;text-align:center; width: 15%;"><?php echo __($MM2); ?></th>
																<th class=""  style="vertical-align : middle;text-align:center; width: 15%;"><?php echo __($MM3); ?></th>		
																<th class=""  style="vertical-align : middle;text-align:center; width: 15%;"><?php echo __("年間"); ?></th>
															</tr>	
														</thead>
														<tbody>
															<tr>
																<td class=""  style="vertical-align : middle; width: 15%; padding: 5px;"><?php echo __("当期利益(予算)");?>
																	</td>
																<td class="right" id = "<?php echo("month_1_budget_".$each_dept_id); ?>"  style="padding: 0px 5px; vertical-align : middle; width: 15%;"><?php echo number_format($each_budget['d_next_budget']); ?></td>
																<td class="right" id = "<?php echo("month_2_budget_".$each_dept_id); ?>"  style="padding: 0px 5px; vertical-align : middle; width: 15%;"><?php echo number_format($each_budget['d_next2month_budget']); ?></td>
																<td class="right"  id = "<?php echo("month_3_budget_".$each_dept_id); ?>" style="padding: 0px 5px; vertical-align : middle; width: 15%;"><?php echo number_format($each_budget['d_next3month_budget']); ?></td>
																<td class="right"  id = "<?php echo("year_budget_".$each_dept_id); ?>" style="padding: 0px 5px; vertical-align : middle; width: 15%;"><?php echo number_format($each_budget['d_yearly_budget']); ?></td>
															</tr>
															<tr>
																<td class="" style="vertical-align : middle; width: 15%;"> 
																	<?php echo __("当期利益(予測)"); ?> <br>
																	<span class="info">
																	<?php echo __("※百万円単位"); ?>
																	</span>
																</td>
																<?php 

																$d_month1 = $d_value['forecast']['month1_forecast'];
																$d_month2 = $d_value['forecast']['month2_forecast'];
																$d_month3 = $d_value['forecast']['month3_forecast'];
																$d_yearly = $d_value['forecast']['yearly_forecast'];

																if(!empty($d_month1)){
																	$d_month1 = number_format($d_month1);
																	$d_month1 = str_replace('-','▲',$d_month1);

																}
																if(!empty($d_month2)){
																	$d_month2 = number_format($d_month2);
																	$d_month2 = str_replace('-','▲',$d_month2);
																}
																if(!empty($d_month3)){
																	$d_month3 = number_format($d_month3);
																	$d_month3 = str_replace('-','▲',$d_month3);
																}
																if(!empty($d_yearly)){
																	$d_yearly = number_format($d_yearly);
																	$d_yearly = str_replace('-','▲',$d_yearly);
																}
																?>
																<?php if ($get_flag == '0' || $get_flag == "1"): ?>
																	<?php if ($create_limit=='0' || ($create_limit==SETTING::LAYER_SETTING['topLayer'] && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code')))) || ($create_limit==SETTING::LAYER_SETTING['middleLayer'] && (in_array($each_dept_id, array_column(array_column($get_permit_codes, 'second_layer'),'dlayer_code'))))): ?>
																		<td class=""  style="vertical-align : middle; width: 15%;">
																			<input type="text" class = "forecast form-control right" id = "<?php echo("month_1_".$each_dept_id); ?>" value = "<?php echo $d_month1; ?>" maxlength = "6">
																		</td>
																		<td class=""  style="vertical-align : middle; width: 15%;">
																			<input type="text" class = "forecast form-control right" id = "<?php echo("month_2_".$each_dept_id); ?>" value = "<?php echo $d_month2; ?>" maxlength = "6">
																		</td>
																		<td class=""  style="vertical-align : middle; width: 15%;">
																			<input type="text" class = "forecast form-control right" id = "<?php echo("month_3_".$each_dept_id); ?>" value = "<?php echo $d_month3; ?>" maxlength = "6">
																		</td>
																		<td class=""  style="vertical-align : middle; width: 15%;">
																			<input type="text" class = "forecast form-control right" id = "<?php echo("Year_".$each_dept_id); ?>" value = "<?php echo $d_yearly; ?>" maxlength = "7">
																		</td>
																	<?php else: ?>
																		<td class=""  style="vertical-align : middle; width: 15%;">
																			<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_1_".$each_dept_id); ?>" value = "<?php echo $d_month1; ?>" maxlength = "6">
																		</td>
																		<td class=""  style="vertical-align : middle; width: 15%;">
																			<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_2_".$each_dept_id); ?>" value = "<?php echo $d_month2; ?>" maxlength = "6">
																		</td>
																		<td class=""  style="vertical-align : middle; width: 15%;">
																			<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_3_".$each_dept_id); ?>" value = "<?php echo $d_month3; ?>" maxlength = "6">
																		</td>
																		<td class=""  style="vertical-align : middle; width: 15%;">
																			<input type="text" readonly class = "forecast form-control right" id = "<?php echo("Year_".$each_dept_id); ?>" value = "<?php echo $d_yearly; ?>" maxlength = "7">
																		</td>
																	<?php endif ?>
																<?php else: ?>	
																	<td class=""  style="vertical-align : middle; width: 15%;">
																		<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_1_".$each_dept_id); ?>" value = "<?php echo $d_month1; ?>" maxlength = "6">
																	</td>
																	<td class=""  style="vertical-align : middle; width: 15%;">
																		<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_2_".$each_dept_id); ?>" value = "<?php echo $d_month2; ?>" maxlength = "6">
																	</td>
																	<td class=""  style="vertical-align : middle; width: 15%;">
																		<input type="text" readonly class = "forecast form-control right" id = "<?php echo("month_3_".$each_dept_id); ?>" value = "<?php echo $d_month3; ?>" maxlength = "6">
																	</td>
																	<td class=""  style="vertical-align : middle; width: 15%;">
																		<input type="text" readonly class = "forecast form-control right" id = "<?php echo("Year_".$each_dept_id); ?>" value = "<?php echo $d_yearly; ?>" maxlength = "7">
																	</td>										
																<?php endif ?>

															</tr>

														</tbody>
													</table>
													<br>
													<div class="textarea_wpr">
														
														<?php 
														$deptforecastRemark = $d_value['forecast']['remark']; ?>

														<?php if ($get_flag == '0' || $get_flag == "1"): ?>
															<?php if ($create_limit=='0' || ($create_limit==SETTING::LAYER_SETTING['topLayer'] && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code')))) || ($create_limit==SETTING::LAYER_SETTING['middleLayer'] && (in_array($each_dept_id, array_column(array_column($get_permit_codes, 'second_layer'),'dlayer_code'))))): ?>
																<textarea class="form-control textarea_fc_dept" placeholder= "コメントを入力してください。" rows="3"  id = "<?php echo("annual_prospects_".$each_dept_id) ?>"><?php if($deptforecastRemark != "NULL"){echo h($deptforecastRemark);}?></textarea>
															<?php else: ?>
																<textarea class="form-control textarea_fc_dept" placeholder= "コメントを入力してください。" rows="3" id = "<?php echo("annual_prospects_".$each_dept_id) ?>" readonly><?php if($deptforecastRemark != "NULL"){echo h($deptforecastRemark);}?></textarea>
															<?php endif ?>
														<?php else: ?>											
															<textarea class="form-control textarea_fc_dept" placeholder= "コメントを入力してください。" rows="3" id = "<?php echo("annual_prospects_".$each_dept_id) ?>" readonly><?php if($deptforecastRemark != "NULL"){echo h($deptforecastRemark);}?></textarea>
														<?php endif ?>
													</div>
												</div>
											</fieldset>

										</div>
									</div>
									<?php 
									$attached = $d_value['attached']; ?>

									<?php if ($get_flag == '0' || $get_flag == "1"): ?>
										<?php if ($create_limit=='0' || ($create_limit==SETTING::LAYER_SETTING['topLayer'] && (in_array($head_dept_code, array_column(array_column($get_permit_codes, 'Layer'),'hlayer_code')))) || ($create_limit==SETTING::LAYER_SETTING['middleLayer'] && (in_array($each_dept_id, array_column(array_column($get_permit_codes, 'second_layer'),'dlayer_code'))))): ?>
											<div class="form-group row" style="padding-left: 13px;">
												<label for="tolResOvrview" style="padding-bottom: 8px;"><?php echo __('4. 添付資料');?></label>
												<fieldset class="scheduler-border form-control" style="width: 80%;">
													<br>
													<div class="row">
														<div class="col-md-2 upload-div">
															<label style="color:white" id="btn_browse">Upload File
					                    						<input type="file" name="<?php echo("uploadfile_".$each_dept_id) ?>" class ="uploadfile <?php echo("uploadfile_".$each_dept_id) ?>" data-id = "<?php echo $each_dept_id; ?>" data-id-head = "<?php echo $head_data['hlayer_code']; ?>">
					                    					</label>
						               					 </div>
														<div class="col-md-9">
															<div class="upd-file-name <?php echo("upd-file_".$each_dept_id) ?>">
																<?php if(!empty($attached)){
																	foreach ($attached as $each_file) {
																	?>
																		<a href="#" class="down-link" name="down-link" data-toggle="tooltip" data-id="<?php echo $each_dept_id; ?>" data-value = "<?php echo $each_file; ?>" ><?php echo $each_file; ?></a>
																		<span class="glyphicon glyphicon-remove-sign btn-delete" data-id="<?php echo $each_dept_id; ?>" data-id-head = "<?php echo $head_data['hlayer_code']; ?>" data-value = "<?php echo $each_file; ?>"></span>
																	<?php
																		}
																		
																} ?>
															</div>
														</div>
													</div>  
												</fieldset>
											</div>
										<?php else: #readonly?>
											<?php if (!empty($attached)): ?>
												<div class="form-group row" style="padding-left: 13px;">
													<label for="tolResOvrview" style="padding-bottom: 8px;"><?php echo __('4. 添付資料');?></label>
													
													<fieldset class="scheduler-border form-control" style="width: 80%;">
														<br>
														<div class="row">
															
															<div class="col-md-9">
																<div class="upd-file-name <?php echo("upd-file_".$each_dept_id) ?>">
																	<?php if(!empty($attached)){
																		
																				foreach ($attached as $each_file) {
																					
																				?>
																				<a href="#" class="down-link" name="down-link" data-toggle="tooltip" data-id="<?php echo $each_dept_id; ?>" data-value = "<?php echo $each_file; ?>" ><?php echo $each_file; ?></a>
																				 &nbsp;
																			<?php
																				}
																		
																	} ?>
																</div>
															</div>
														</div>  
													</fieldset>
												</div>
											<?php endif ?>
										<?php endif ?>

									<?php else: ?>
										<?php if (!empty($attached)): ?>
											<div class="form-group row" style="padding-left: 13px;">
												<label for="tolResOvrview" style="padding-bottom: 8px;"><?php echo __('4. 添付資料');?></label>
												
												<fieldset class="scheduler-border form-control" style="width: 80%;">
													<br>
													<div class="row">
														
														<div class="col-md-9">
															<div class="upd-file-name <?php echo("upd-file_".$each_dept_id) ?>">
																<?php if(!empty($attached)){
																	
																	foreach ($attached as $each_file) {
																		
																	?>
																	<a href="#" class="down-link" name="down-link" data-toggle="tooltip" data-id="<?php echo $each_dept_id; ?>" data-value = "<?php echo $each_file; ?>" ><?php echo $each_file; ?></a>
																	 &nbsp;
																<?php
																	}
																	
																} ?>
															</div>
														</div>
													</div>  
												</fieldset>
											</div>
										<?php endif ?>										
									<?php endif ?>
										
								<?php }else{ ?>
										<div class="col-sm-12">
											<p class="no-data">Data does not exist in system to calculate this department.</p>
										</div>
										<br>
								<?php } ?>

							</div>
						</div>
						<br><br>	
							
					<?php } 
						
						json_encode($js_data);#When save, to get data by department id.
					}else{?>
					<div class="col-sm-12">
						<!-- <p class="no-data">Data does not exist in system to calculate departments.</p> -->
					</div>
				<?php } ?>

			<input type="hidden" name="hidden_dept_id" id="hidden_dept_id" >
			<input type="hidden" name="hidden_head_id" id="hidden_head_id" >
			<input type="hidden" name="hidden_file_name" id="hidden_file_name" >

			<br>	
			<br>
			<br>	
		<?php else: ?>
			<div class="col-sm-12">
				<p class="no-data"><?php echo __($headquarter."のデータが承認されていません。"); ?></p>
			</div>
		<?php endif ?>
	</div>
</div>
<?php echo $this->Form->end(); ?>	

<script type="text/javascript">

	$(document).ready(function(){

		var ua = window.navigator.userAgent;
   		var msie = ua.indexOf("MSIE ");

	   if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet Explorer,
	   {
			var width1 = $('.scheduler-border').width();
	      $('.textarea_fc').width(width1);
			$('.textarea_fc').height('74px');

			$('.textarea_fc_dept').width(width1-18);
			$('.textarea_fc_dept').height('74px');

			var width2 = $('.resizable_txtarea').parent().width();

      	$('.resizable_txtarea').width(width2);
      	$('.resizable_txtarea').height('293px');

      	$('.resizable_txtarea_dept').width(width2-27);
      	$('.resizable_txtarea_dept').height('275px');

      	$(".textarea_fc").resizable({
      		maxWidth: width1+24
      	});
      	$(".textarea_fc_dept").resizable({
      		maxWidth: width1
      	});
      	$(".resizable_txtarea").resizable({
      		maxWidth: width2+24
      	});
      	$(".resizable_txtarea_dept").resizable({
      		maxWidth: width2+24
      	});

	   }

		$("#hidden_dept_id").val('');
		$("#hidden_file_name").val('');

		MakeNegative(); //for red color negative number 

		
		// For Headquarter drop down icon
	  	$(".drop_down_arrow").click(function() {
	  		
	  		var dataID = $(this).attr('data-id');
			var record = '.drop_down_arrow_'+dataID;

			var ar_ex = $(record).attr('aria-expanded');
			
		  	if( ar_ex == "true" ){

		  		$(record).find('.fas').removeClass('fa-chevron-up');
				$(record).find('.fas').addClass('fa-chevron-down');

		  	}else
			if( ar_ex == "false"){
		  		
		  		$(record).find('.fas').removeClass('fa-chevron-down');
				$(record).find('.fas').addClass('fa-chevron-up');
		  	}else{
		  		
		  		$(record).find('.fas').removeClass('fa-chevron-up');
				$(record).find('.fas').addClass('fa-chevron-down');
		  	}

	  	});

     	// For Department drop down icon
		$('.drop_down_dept').click(function() {
			
			var dataID = $(this).attr('data-id');
			var record = '.drop_down_dept_'+dataID;
			
		  	if(($(record).attr('aria-expanded')) == "true" || ($(record).attr('aria-expanded')) == "undefined" ){

		  		$(record).find('.fas').removeClass('fa-chevron-up');
				$(record).find('.fas').addClass('fa-chevron-down');

		  	}else{
		  		
		  		$(record).find('.fas').removeClass('fa-chevron-down');
				$(record).find('.fas').addClass('fa-chevron-up');
		  	}

	  	});

		// Upload Multi File
		$('.upload-div').on('change','.uploadfile',function(e) {

	  		$("#error").empty();
	  		$("#success").empty();
	  		$("div#success.success").empty();
	  		$("#mty_rp_failMessage").empty();
	  		
            if($(this).val() != '') {
            	
            	var dept_id 	= $(this).attr('data-id');
            	var head_id 	= $(this).attr('data-id-head');
                var file_name 	= $(this).prop('files')[0].name;  
                var file_size 	= $(this).prop('files')[0].size;  
                var myFile 		= $(this).prop('files');
                var errorFlag   = true;
				
                $("#hidden_dept_id").val(dept_id);
                $("#hidden_head_id").val(head_id);

              	if(file_size >= 10485760){ // check 10MB

              		var newbr = document.createElement("div");                      
		        	var a     = document.getElementById("error").appendChild(newbr);
		       		a.appendChild(document.createTextNode(errMsg(commonMsg.JSE031)));
		      		document.getElementById("error").appendChild(a);  
		      		errorFlag = false;
		      		scrollText();
              	}
                        	
              	if(errorFlag){

              		$.ajax({
				        url: "<?php echo $this->webroot; ?>BrmMonthlyReport/checkDuplicateUploadFile",
				        type: "post",
				        data : {'file_name': file_name,'dept_id':dept_id,'head_id':head_id},
				        success: function(data) {
				        	
				          	var data = JSON.parse(data);

					        showPopupBox(data,file_name,myFile,dept_id,head_id)
				        },
				        error: function(e) {
				          console.log(e);
				        }
				    });
              	}
               
		    } 
		   	
		});
		

	  	// Uploaded File delete
		$('.upd-file-name').on('click','.btn-delete',function(e) {
			$("#error").empty();
	  		$("#success").empty();
	  		$("div#success.success").empty();
	  		$("#mty_rp_failMessage").empty();

			e.preventDefault();
		
			var dept_id  = $(this).attr('data-id');
			var head_id = $(this).attr('data-id-head');
			var fileName = $(this).attr('data-value');
			
			$("#hidden_dept_id").val(dept_id);
			$("#hidden_head_id").val(head_id);
			$("#hidden_file_name").val(fileName);
			//$("#overlay").show();
			loadingPic();
			$.confirm({
				title: "<?php echo __('削除確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'red',
				typeAnimated: true,
				closeIcon: false,
				columnClass: 'medium',
				animateFromElement: true,
				animation: 'top',
				draggable: false,  
				content: "<?php echo __("データを削除してよろしいですか。") ?>",
				buttons: {
			        ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
			          	action:function(){
							
							$.ajax({
						        url: "<?php echo $this->webroot; ?>BrmMonthlyReport/delete_upload_file",
						        type: "post",
						        data : {'file_name': fileName,'dept_id':dept_id,'head_id':head_id},
						        success: function(data) {

						        	$(".upd-file_"+dept_id).empty();
						        	var data = jQuery.parseJSON(data);
						        	if(data != 'error'){
						        				
						        		$.each(data, function (key,value) {
							        		var filename = value.BrmMrAttachment.file_name;
							        		
							        		var html = "<a href='#' class='down-link' name = 'down-link' data-toggle = 'tooltip' data-id = '"+dept_id+"' data-value = '"+filename+"' >"+filename+"</a><span class='glyphicon glyphicon-remove-sign upd-file-name btn-delete' data-id = '"+dept_id+"' data-value = '"+filename+"'></span>";

								            	$(".upd-file_"+dept_id).append(html);
								            	
								            return true;
							            });
							            $("#overlay").hide();
										location.reload();
						        	}else{
						        		scrollText();
						        		location.reload();
						        		
						        	}

						        },
						        error: function(e) {
						          console.log(e);
						        }
						    });
			          	}
					},	  
					action: {
				       	text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
				       	action: function(){
				       		$("#overlay").hide();
				       	}
					}
					
				},
				
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		});

		/* download file */
		$('.upd-file-name').on('click','.down-link',function(e) {
					
			$("#error").empty();
	  		$("#success").empty();
	  		$("div#success.success").empty();

			e.preventDefault();

			var dept_id  = $(this).attr('data-id');
			var head_id  = $(this).attr('head-id');
			var fileName = $(this).attr('data-value');
			
			$("#hidden_dept_id").val(dept_id);
			$("#hidden_head_id").val(head_id);
			$("#hidden_file_name").val(fileName);
			document.forms[0].action = "<?php echo $this->webroot; ?>BrmMonthlyReport/download_file_from_cloud";
			document.forms[0].method = "POST";
			document.forms[0].submit();
			return true;
		});
		// Add comma for number format and change value eg: 2.5 => 3, 2.3 => 2
		$('input.forecast').focusout(function(event) { //keyup
			// skip for arrow keys
			if(event.which >= 37 && event.which <= 40) return;

			// format number
			$(this).val(function(index, value) {

				if(!value){
					return value; 
				} 
				
				var chk_minus = value.charAt(0);
				if(chk_minus == '-' || chk_minus == '▲'){

					$(this).addClass('negative');
					var roundValue = Math.round(value.replace(/[-▲]/g, ''));
					var showValue = roundValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
					return (isNaN(roundValue))? '' : '▲'+showValue; 
					
				}else{
					$(this).removeClass('negative');
					var roundValue = Math.round(value);
					var showValue = roundValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
					return (isNaN(roundValue))? '' : showValue; 
				}
				
			});
		});
		// remove comma for number format
		$('input.forecast').focus(function(event) { //keyup
			// skip for arrow keys
			
			if(event.which >= 37 && event.which <= 40) return;

				// format number
				$(this).val(function(index, value) {

					return value.replace(/,/g, '');
					

			});
		});

		
		$('input.forecast').each(function(index) { //keyup
			
			// skip for arrow keys
			if(event.which >= 37 && event.which <= 40) return;

				// format number
				$(this).val(function(index, value) {
					
					var chk_minus = value.charAt(0);
					if(chk_minus == '-' || chk_minus == '▲'){
						
						$(this).addClass('negative');
						return value;
					}else{
						return value;
					}
					
			});
		});
		
	});

	//if upload file is already exist=>show duplicate confirm popup , else => show save confirm popup
	function showPopupBox(duplicate,file_name,myFile,dept_id,head_id){

		if(duplicate == "no_duplicate"){

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
							var file = $('.uploadfile_'+dept_id).prop('files')[0];
							
							var formData = new FormData();
							
							formData.append(dept_id, file);
							formData.append(head_id, file);
							
							$.ajax({
								url: '<?php echo $this->webroot; ?>BrmMonthlyReport/saveUploadFile',
								type: 'POST',
								processData: false,  //required to upload file
								contentType: false,  // required
								data: formData,
								
								beforeSend: function(){
									//$("#overlay").show();
									loadingPic();
								},
								error:function(){
									$("#overlay").hide();
									console.log("error");
								},
								success: function(data){

									var html = "<a href='#' class='down-link' name = 'down-link' data-toggle = 'tooltip' data-id = '"+dept_id+"' data-value = '"+file_name+"' >"+file_name+"</a><span class='glyphicon glyphicon-remove-sign btn-delete' data-id = '"+dept_id+"' data-value = '"+file_name+"'></span>";

									$(".upd-file_"+dept_id).append(html);
									
									$("#overlay").hide();
								
									fileClear();
									location.reload();
									return true;
								}
							});
						}
					},    
					action: {
						text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
						action: function(){
							fileClear();
						}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});

		}else if(duplicate == "duplicate"){

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
				content: "<?php echo __("この写真は既にアップロードされています。上書きしますか？");?>",
				buttons: {
					ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
						action:function(){
							
							var file = $('.uploadfile_'+dept_id).prop('files')[0];
							var formData = new FormData();

							formData.append(dept_id, file);
							formData.append(head_id, file);

							$.ajax({
								url: '<?php echo $this->webroot; ?>BrmMonthlyReport/overwirteUploadFile',
								type: 'POST',
								processData: false,  ///required to upload file
								contentType: false,  /// required
								data: formData,
								beforeSend: function(){
									//$("#overlay").show();
									loadingPic();
								},
								error:function(){
									$("#overlay").hide();
									console.log("error");
								},
								success: function(data){
									
									$("#overlay").hide();
									fileClear();
									
									return true;
								}
							});
						}
					},    
					action: {
						text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
						action: function(){
							fileClear();
						}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});

		}
	}
	function fileClear(){
		$('.uploadfile').val('');

	}

	function excelDownload(){

		document.getElementById("error").innerHTML = "";
		document.getElementById("success").innerHTML =""; 
		$("div#success.success").empty();

		document.forms[0].action = "<?php echo $this->webroot; ?>BrmMonthlyReport/excelDownloadMonthlyReport";
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;
	}



	function saveHead(dept_id,name)
	{
		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = ""; 
		//loadingPic();
		$("div#success.success").empty();
		$("div#error.error").empty();
		$('#hidden_dept_id').val(dept_id);
		$('#hidden_file_name').val(name);

    	var errorFlag = true;
		var dataPushArray = {};
		var myData = new Array();

		var dataLoop = {};       
    
   		var deptId = <?php echo json_encode($js_data); ?>;
			
		for(var i=0; i<deptId.length; i++){

	      	var eachDeptId 	= deptId[i]['id'];	        
        	if(dept_id == eachDeptId){
        		var eachDeptName = deptId[i]['name'];
        	}
	    }
       
        var budgetMonth1Dept 	= document.getElementById('month_1_budget_'+dept_id).innerHTML;

        var budgetMonth2Dept 	= document.getElementById('month_2_budget_'+dept_id).innerHTML;
        
        var budgetMonth3Dept 	= document.getElementById('month_3_budget_'+dept_id).innerHTML;
         
        var budgetYearDept 	    = document.getElementById('year_budget_'+dept_id).innerHTML;
      
        var ovrviewDept 	= document.getElementById('txtara_ovrview_'+dept_id).value;
        var month1 			= document.getElementById('month_1_'+dept_id).value.replace(/,/g, '');
        var month2 			= document.getElementById('month_2_'+dept_id).value.replace(/,/g, '');
        var month3 			= document.getElementById('month_3_'+dept_id).value.replace(/,/g, '');
        var yearDept 		= document.getElementById('Year_'+dept_id).value.replace(/,/g, '');
      	
        month1 = month1.replace(/▲/g,'-');
        month2 = month2.replace(/▲/g,'-');
        month3 = month3.replace(/▲/g,'-');
        yearDept = yearDept.replace(/▲/g,'-');
       
       	var annualProspects = document.getElementById('annual_prospects_'+dept_id).value;

   		if(checkNullOrBlank(month1)){

        	if (!isDecimalPosNeg(month1)) {
    			var newbr = document.createElement("div");
	            var a = document.getElementById("error").appendChild(newbr);
	            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007,['<?php echo $MM1.__(" at ")?>'+eachDeptName])));
	            
	            document.getElementById("error").appendChild(a);
	            errorFlag = false;
	            
			}
       	}
       	if(checkNullOrBlank(month2)){

        	if (!isDecimalPosNeg(month2)) {
				var newbr = document.createElement("div");
	            var a = document.getElementById("error").appendChild(newbr);
	            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007,['<?php echo $MM2.__(" at ")?>'+eachDeptName])));
	            
	            document.getElementById("error").appendChild(a);
	            errorFlag = false;
	           
			}
       	}
       	if(checkNullOrBlank(month3)){
        	if (!isDecimalPosNeg(month3)) {
				var newbr = document.createElement("div");
	            var a = document.getElementById("error").appendChild(newbr);
	            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007,['<?php echo $MM3.__(" at ")?>'+eachDeptName])));
	            
	            document.getElementById("error").appendChild(a);
	            errorFlag = false;
			}
       	}
       	if(checkNullOrBlank(yearDept)){
        	if (!isDecimalPosNeg(yearDept)) {
    			var newbr = document.createElement("div");
	            var a = document.getElementById("error").appendChild(newbr);
	            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007,['<?php echo __(" at Year")?>'+eachDeptName])));
	            
	            document.getElementById("error").appendChild(a);
	            errorFlag = false;
			}
       	}
       	scrollText();
       	if(errorFlag == true){
      		dataLoop['name']			= eachDeptName;
      		dataLoop['dept_id'] 		= dept_id;
	       	dataLoop['ovrviewDept'] 	= ovrviewDept;
	       	dataLoop['month1'] 			= month1;
	       	dataLoop['month2'] 			= month2;
	       	dataLoop['month3'] 			= month3;
	       	dataLoop['yearDept'] 		= yearDept;
	       	dataLoop['annualProspects']  = annualProspects;
	       	dataLoop['budgetMonth1Dept'] = budgetMonth1Dept;
	       	dataLoop['budgetMonth2Dept'] = budgetMonth2Dept;
	       	dataLoop['budgetMonth3Dept'] = budgetMonth3Dept;
	       	dataLoop['budgetYearDept']   = budgetYearDept;
	       	
	       	myData.push(dataLoop);

			var myJSONString = JSON.stringify(myData);
	    	$.confirm({
			   	title: "<?php echo __('保存確認'); ?>",
			   	icon: 'fas fa-exclamation-circle',
			   	type: 'green',
			   	typeAnimated: true,
			   	closeIcon: false,
			   	columnClass: 'medium',
			   	animateFromElement: true,
			   	animation: 'top',
			   	draggable: false,
			   	content: errMsg(commonMsg.JSE009),
			   	buttons: {   
			        ok: {
			            text: "<?php echo __('はい');?>",
			          	btnClass: 'btn-info',
			            action: function(){
							getMail('save',myJSONString);
			          	}
			       	},	  
			    cancel : {
			       	text: "<?php echo __('いいえ');?>",
			            btnClass: 'btn-default',
			       	action: function(){
			       			$("#overlay").hide();
			        		console.log('the user clicked cancel');
			       		}
			       	}
			    },
			   	theme: 'material',
			  	animation: 'rotateYR',
			   	closeAnimation: 'rotateXR'
			});
		}else{
			$("#overlay").hide();
		}
	}
	
	function approveHead(dept_id,name){

		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = ""; 

		
		$("div#success.success").empty();
		$("div#error.error").empty();
		$('#hidden_dept_id').val(dept_id);
		$('#hidden_file_name').val(name);

    	var errorFlag = true;
		var dataPushArray = {};
		var myData = new Array();

		var dataLoop = {};       
    
   		var deptId = <?php echo json_encode($js_data); ?>;
			
		for(var i=0; i<deptId.length; i++){

	        var eachDeptId 	= deptId[i]['id'];	        
	        if(dept_id == eachDeptId){
	        	var eachDeptName = deptId[i]['name'];
	        }
	    }
       
        var budgetMonth1Dept 	= document.getElementById('month_1_budget_'+dept_id).innerHTML;

        var budgetMonth2Dept 	= document.getElementById('month_2_budget_'+dept_id).innerHTML;
        
        var budgetMonth3Dept 	= document.getElementById('month_3_budget_'+dept_id).innerHTML;
         
        var budgetYearDept 	    = document.getElementById('year_budget_'+dept_id).innerHTML;
         
        var ovrviewDept 	= document.getElementById('txtara_ovrview_'+dept_id).value;
        var month1 			= document.getElementById('month_1_'+dept_id).value.replace(/,/g, '');
        var month2 			= document.getElementById('month_2_'+dept_id).value.replace(/,/g, '');
        var month3 			= document.getElementById('month_3_'+dept_id).value.replace(/,/g, '');
        var yearDept 		= document.getElementById('Year_'+dept_id).value.replace(/,/g, '');
      
        month1 = month1.replace(/▲/g,'-');
        month2 = month2.replace(/▲/g,'-');
        month3 = month3.replace(/▲/g,'-');
        yearDept = yearDept.replace(/▲/g,'-');

       	var annualProspects = document.getElementById('annual_prospects_'+dept_id).value;

   		if(checkNullOrBlank(month1)){

        	if (!isDecimalPosNeg(month1)) {

    			var newbr = document.createElement("div");
	            var a = document.getElementById("error").appendChild(newbr);
	            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007,['<?php echo $MM1.__(" at ")?>'+eachDeptName])));
	            
	            document.getElementById("error").appendChild(a);
	            errorFlag = false;
	            
			}
       	}    
       	if(checkNullOrBlank(month2)){
        	if (!isDecimalPosNeg(month2)) {
				var newbr = document.createElement("div");
	            var a = document.getElementById("error").appendChild(newbr);
	            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007,['<?php echo $MM2.__(" at ")?>'+eachDeptName])));
	            document.getElementById("error").appendChild(a);
	            errorFlag = false; 
			}
       	}
       	if(checkNullOrBlank(month3)){
        	if (!isDecimalPosNeg(month3)) {
				var newbr = document.createElement("div");
	            var a = document.getElementById("error").appendChild(newbr);
	            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007,['<?php echo $MM3.__(" at ")?>'+eachDeptName])));
	            
	            document.getElementById("error").appendChild(a);
	            errorFlag = false;
			}
       	}
       	if(checkNullOrBlank(yearDept)){
        	if (!isDecimalPosNeg(yearDept)) {
    			var newbr = document.createElement("div");
	            var a = document.getElementById("error").appendChild(newbr);
	            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007,['<?php echo __(" at Year")?>'+eachDeptName])));
	            
	            document.getElementById("error").appendChild(a);
	            errorFlag = false;
			}
       	}
       	scrollText();
       	if(errorFlag == true){
      		dataLoop['name']			= eachDeptName;
      		dataLoop['dept_id'] 		= dept_id;
	       	dataLoop['ovrviewDept'] 	= ovrviewDept;
	       	dataLoop['month1'] 			= month1;
	       	dataLoop['month2'] 			= month2;
	       	dataLoop['month3'] 			= month3;
	       	dataLoop['yearDept'] 		= yearDept;
	       	dataLoop['annualProspects']  = annualProspects;
	       	dataLoop['budgetMonth1Dept'] = budgetMonth1Dept;
	       	dataLoop['budgetMonth2Dept'] = budgetMonth2Dept;
	       	dataLoop['budgetMonth3Dept'] = budgetMonth3Dept;
	       	dataLoop['budgetYearDept']   = budgetYearDept;
	       	
	       	myData.push(dataLoop);

			var myJSONString = JSON.stringify(myData);
	    	$.confirm({
			   	title: "<?php echo __('承認確認'); ?>",
			   	icon: 'fas fa-exclamation-circle',
			   	type: 'green',
			   	typeAnimated: true,
			   	closeIcon: false,
			   	columnClass: 'medium',
			   	animateFromElement: true,
			   	animation: 'top',
			   	draggable: false,
			   	content: "<?php echo __("全行を承認してよろしいですか。"); ?>",
			   	buttons: {   
			        ok: {
			            text: "<?php echo __('はい');?>",
			          	btnClass: 'btn-info',
			            action: function(){
			                getMail('approve',myJSONString);
			          	}
			       	},	  
			    cancel : {
			       	text: "<?php echo __('いいえ');?>",
			            btnClass: 'btn-default',
			       	action: function(){
			       			$("#overlay").hide();
			        		console.log('the user clicked cancel');
			       		}
			       	}
			    },
			   	theme: 'material',
			  	animation: 'rotateYR',
			   	closeAnimation: 'rotateXR'
			});

		}else{
			$("#overlay").hide();
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
	
	function approveCancelHead(dept_id,name){

		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = ""; 

		
		$("div#success.success").empty();
		$("div#error.error").empty();
		$('#hidden_dept_id').val(dept_id);
		$('#hidden_file_name').val(name);
		
		$.confirm({
		   	title: "<?php echo __('承認キャンセル確認'); ?>",
		   	icon: 'fas fa-exclamation-circle',
		   	type: 'green',
		   	typeAnimated: true,
		   	closeIcon: false,
		   	columnClass: 'medium',
		   	animateFromElement: true,
		   	animation: 'top',
		   	draggable: false,
		   	content: "<?php echo __("全行を承認キャンセルしてよろしいですか。"); ?>",
		   	buttons: {   
		        ok: {
		            text: "<?php echo __('はい');?>",
		          	btnClass: 'btn-info',
		            action: function(){
						getMail('approve_cancel',''); 
		          	}
		       	},	  
		    cancel : {
		       	text: "<?php echo __('いいえ');?>",
		            btnClass: 'btn-default',
		       	action: function(){
		        		console.log('the user clicked cancel');
		        		$("#overlay").hide();
		       		}
		       	}
		    },
		   	theme: 'material',
		  	animation: 'rotateYR',
		   	closeAnimation: 'rotateXR'
		});
	}

	function scrollText(){
      
		var tes  = $('#error').text();
		var tes1 = $('#success').text();
		if(tes){
			$("html, body").animate({ scrollTop: 0 }, 400);          
		}
		if(tes1){
			$("html, body").animate({ scrollTop: 0 }, 400);          
		}
  	}

	function getMail(func,myJSONString = '') {	
		func = func.replace(" ", "");
		$.ajax({
			type:'post',
			url: "<?php echo $this->webroot; ?>BrmMonthlyReport/getMailLists",
			data:{page: 'BrmMonthlyReport', function: func},
			dataType: 'json',
			success: function(data) {
				var mailSend = (data.mailSend == '') ? '0' : data.mailSend;
				var mail_data = {};  
				$("#mailSend").val(mailSend);
				mail_data['mailSend'] = mailSend;
				if(mailSend == 1) {	
					$("#mailSubj").val(data.subject);
					$("#mailBody").val(data.body);
					mail_data['mailSubj'] = data.subject;
					mail_data['mailBody'] = data.body;
					if (data.mailType == 1) {
						//default
						if(data.to != undefined) var toEmail = Object.values(data.to);
						if(data.cc != undefined) var ccEmail = Object.values(data.cc);
						if(data.bcc != undefined) var bccEmail = Object.values(data.bcc);
						
						mail_data['toEmail']  = toEmail;
						mail_data['ccEmail']  = ccEmail;
						mail_data['bccEmail'] = bccEmail;
						$('#toEmail').val(toEmail);
						$('#ccEmail').val(ccEmail);
						$('#bccEmail').val(bccEmail);
						loadingPic();
						prepareData(func,myJSONString,mail_data);
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
						$("#myPOPModal #btn_mail_ok").on('click',function(){
							mail_data['mailSubj'] = data.subject;
							mail_data['mailBody'] = data.body;
							mail_data['toEmail']  = $("#autoCompleteTo").val();
							mail_data['ccEmail']  = $("#autoCompleteCC").val();
							mail_data['bccEmail'] = $("#autoCompleteBcc").val();
							if((data.to != undefined && mail_data['toEmail'] != '') || data.cc != undefined || data.bcc != undefined) {
								prepareData(func,myJSONString,mail_data);
							} 
						});
					}
				} else {
					prepareData(func,myJSONString,mail_data);
				}
			},
			error: function(e) {
				console.log('Something wrong! Please refresh the page.');
			}
		});		
	}
	
	function prepareData(func,myJSONString = '',mail_data) {
		form_action = func+"Head";
		mail_info   = JSON.stringify(mail_data);
		if (form_action == 'approve_cancelHead') {
			document.forms[0].action = "<?php echo $this->webroot; ?>BrmMonthlyReport/"+form_action;
			document.forms[0].method = "POST";
			document.forms[0].submit();
			return true; 
		} else {
			return $.ajax({
			data : {myJSONString: myJSONString,mail_info:mail_info},
			url: "<?php echo $this->webroot; ?>BrmMonthlyReport/"+form_action,
			dataType: 'json',
			method: 'post',
			beforeSend: function() {
				loadingPic();
			}
			}).done(function (result) {
					
					scrollText();   
					window.location.reload(); 
			}).fail(function(){
				console.log('fail');
				scrollText();
				window.location.reload();
			});
		}
	}
  	//author: Pan (25.3.2020)
  	function MakeNegative() {

        TDs = document.getElementsByTagName('td');
        for (var i=0; i<TDs.length; i++) {
            var temp = TDs[i];
           	
            if (temp.firstChild.nodeValue.indexOf('-') == 0) 
            {	
        		$(temp).addClass('negative');

        		var changeVal  = temp.innerHTML.replace('-','▲');
        		temp.innerHTML = changeVal;
        		
        	}
        }
    }

    function clickMailSending() {
   		loadingPic();
		document.forms[0].action = "<?php echo $this->webroot; ?>BrmMonthlyReport/MailSending";
		document.forms[0].method = "POST";
		document.forms[0].submit();
    }

    function loadingPic() { // function expression closure to contain variables
		var ua = window.navigator.userAgent;
   		var msie = ua.indexOf("MSIE ");
   		
		if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet 
	   	{
	    
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
			$("#overlay").show();
		}
	} 
</script>

