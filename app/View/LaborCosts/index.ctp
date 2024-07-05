<?php
	echo $this->Form->create(false,array('type'=>'post', 'id' => 'labor_cost', 'enctype'=> 'multipart/form-data', 'autocomplete'=>'off'));
?>

<style>
	.btn_sumisho_set{
		min-width: 100px;
		background-color: var(--btncolor1);
		color: #fff;
		/* margin: 10px 0px 10px 10px; */
	}
	.btn_sumisho_add{
		min-width: 50px;
		background-color: var(--btncolor1);
		color: #fff;
	}
	.budget_personal_tbl thead th{	
		border: 1px solid #ddd;
		text-align: center !important;
	}
	.tbl-header {
		padding: 10px;
	}
	.select-header {
		width: 130px !important;
	}
	tbody tr td{
		/* padding: 5px; */
		border: 1px solid #ddd;
	}

	.budget_personal_tbl tr td.total_field {
		/* padding: 0px 5px; */
		text-align: right;
		background-color: #fff;
		font-size: 1em !important;
	}
	.budget_personal_tbl tr td.colorFill{
		background-color: #f5f5f5 !important;
	}
	.budget_personal_tbl td.personnelCost input[type=text]:read-only{
		width: 70px !important;
	}
	.budget_personal_tbl td input[type=text], td input[type=button] {
		padding:0 4px 0 4px;
		width: 100% !important;
		height: 28px !important;
		outline: none;
		border: none;
		border-radius: 0;
		/* box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
		resize: none; */
	}
	.budget_personal_tbl td input[type=text]:not(.adjust_name):not(.otherAdjustName){
		text-align: right;
	}	
	.budget_personal_tbl select:not(.form-control){
		height: 28px !important;
		width: 150px !important;
		background-color: rgb(255, 255, 204);
		border: 1px;
	}
	/*SST 25.8.2022 for input column cell style */
	.input_cell{
		height: 100% !important;
		width: 100% !important;
		display:inline-block;
		position:relative;
		background-color: rgb(255, 255, 204);
	}
	.budget_personal_tbl {
		width: 100%;
		margin-bottom: 50px;
	}
	.budget_personal_tbl thead {
		height: 50px;
		position: sticky;
		top: 0; /* Don't forget this, required for the stickiness */
		z-index: 800 !important;
	}
	.budget_personal_tbl .gap {
		width: 20px;
		height: 100% !important;
		position: relative;
	}
	.budget_personal_tbl .gap .clone_copy, .clone_remove {
		width: 100%;
		position: absolute;
		top: 0;
		left: 0;
		padding: 0;
		margin: 0;
		height: 100%;
		border-radius: 0;
	}	
	.budget_personal_tbl tr th {
		/*min-width: 75px;*/
		/* border-bottom: double; */
		border-color: #ddd
	}
	.negative {
		color: #f31515;
	}
	span.glyphicon-info-sign{
		float:right !important;
		margin-top: 20px;
		margin-top: 20px;
		font-size: 35px;
		color: #F7C600;  
		cursor: pointer;
	}
	#info-text{
		color:#808080;
	}
	#info-text ol{
		margin-bottom: 5px;
		
	}
	.total{
		font-weight: bold;
	}
	.total-name{
		text-align: center !important;
	}
	ol {
		margin-left: 0;
		margin-top: 0.7rem;
		padding-left: 1.5rem;
	}
	.popover {
		/* top: 50px !important; */
		max-width: 25% !important;
	}
	#load{
		z-index: 10000;
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0,0,0,0.2);
	}

	@media screen and (min-width: 768px) and (max-width: 992px) {
		.popover {
			max-width: 50% !important;
		}
	}
	@media screen and (max-width: 576px) {
		.popover {
			max-width: 70% !important;
		}
	}
	#btn_excel_download, #btn_save, #btn_confirm, #btn_cancel_confirm{
		height : 32px;
		font-size : 14px !important;
	}
	.bg-color {
		background-color: #ffd2d2;
		color: #ff3333;
	}
</style>
<!-- add css for add comment -->
<?php echo $this->Html->css('LaborCostDetail/style.css');?>
<div id="overlay">
	<span class="loader"></span>
</div>
<div id="load"></div>
<div id="contents"></div>
<div class="content register_container" style="font-size: 1em !important;">
	<?php if(!empty($comment) && strlen(trim($comment['LcComment']['comment'])) > 0) : ?>
		
        <div class="noti-msg d-flex flex-column justify-content-between align-items-center">
            <div class="comment-message"><?php echo $comment['LcComment']['comment']; ?></div>
            <div style="margin:0.5rem 0 0 0;text-align:end">
				<?php 
					$translatedComment = __("<b>{$comment['users']['user_name']}</b>"); 
					$translatedDate = __("{$comment['LcComment']['updated_date']}");
					echo __("%s が %s にコメントしました。", $translatedComment, $translatedDate);
				?>
			</div>
        </div>
    <?php endif; ?>
	<div class="row">
		<div class="col-md-8 col-sm-8">				
			<h3>
				<?php  echo __("予算人員表"); ?>			
			</h3>
		</div>
		<div class="col-md-4 col-sm-4">				
			<span style="display: flex; float:right;"
				class="glyphicon glyphicon-info-sign" 
				data-container="body" 
				data-toggle="popover" 
				data-placement="left" 
				data-content="
					<div id='info-text'>
						<p>【入力事項】</p>
						<ol>
							<li>氏名、区分、人員、共通費免除②（シニアパートナー等、必要に応じ）を入力。</li>
							<li>トレーニーは、各人で条件が異なるため、実態に合わせて修正。</li>
							<li>嘱託がいる場合、人件費単価と人件費（年間）を入力。</li>
							<li>派遣社員がいる場合、人件費はビジネス毎のシートの業務委託費に入力。（この表では、コーポレート経費割当のみを計算）</li>
							<li>人数が多く行が不足する場合は、必要に応じ増やす。</li>
							<li>人件費（年間）とコーポレート経費割当（年間）について、過去分は実績と合致させる。差異は異動による差異調整箇所にて調整。</li>
							<li>経営指導料は、ビジネス別人員表にて入力</li>
							<li>予算人員作成時（12月）には在籍していたが、異動により4月から不在の人はコーポレート経費のみ賦課されているため、「人員」は0人とし、コーポレート割掛単価に入力する。<br/>（コーポレート経費（年間）に直接入力することも可）</li>
							<li>出向者戻入は、人件費（年間）に直接入力（マイナスで入力）</li>
							<li> 期中で異動した人は、在籍月数/12カ月の人員を入力すれば、自動計算される（差異調整にしてもよいが、その場合、人員数も調整のこと）</li>
						</ol>
					</div>
				" 
				data-html='true'>
			</span>
		</div>
		<input type="hidden" name="confirm_message" id="confirm_message" value="">	
		<div class="col-md-12 budget-form-hr" style="margin-top: -10px;">
			<hr>
		</div>
	</div>
	<!-- Error Area -->
	 <div id="successErrorMsg">
        <div><?php echo $this->Flash->render("lc_success")?></div>
        <div><?php echo $this->Flash->render("lc_error"); ?></div>
    </div>
    <!-- end Error Area  -->
	<div class="form-group row">
		<div class="col-md-12" style="font-size: 1em;padding:0px"> 	
			<div class="col-md-12">
				<div class="success" id="success"><?php echo __($successMsg);?></div>            
				<div class="error" id="error"></div>
			</div>
			<?php if(empty($year_list)) : ?>
			<div id="err" class="col-md-12 no-data"> <?php echo ($errormsg);?></div>
			<?php else : ?>
			<div class="col-md-12">
				<div class="form-group row">
					<div class="col-sm-3">
						<input type="text" class="form-control" value="<?php if($this->Session->check('TERM_NAME')) echo $this->Session->read('TERM_NAME');?>" readonly="">
					</div>
					<div class="col-sm-3">
						<select id="target_yr" name="target_yr" class="form-control">
							<?php 
								foreach($year_list as $ylist => $value) {
							?>
								<option value = "<?php echo $ylist; ?>" <?php if($ylist == $search_data['target_year']) echo 'selected="selected"';?>><?php echo $ylist; ?> <?php echo __("年度")?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-sm-3">
						<select id="layer_code" name="layer_code" class="form-control">
							<?php if(count($layer_code_list)<1): ?>
							<option value=''>---Select---</option>
							<?php endif; ?>
							<?php foreach($layer_code_list as $lcode_list) { 
								$layer_code =$lcode_list['Layer']['layer_code'] ;
								$layer_name =($lang_name=='en') ? $lcode_list['Layer']['name_en'] : $lcode_list['Layer']['name_jp']; ?>
									<option value = "<?php echo $layer_code; ?>" <?php if($layer_code == $search_data['layer_code']) echo 'selected="selected"';?>><?php echo __($layer_name)?></option>
								<?php } ?>
							</select>
					</div>
					<div class="col-sm-3">
						<button type="button" class="btn btn-success btn_sumisho_set" onclick="btn_set()"><?php echo __("設定選択");?> </button>
					</div>
				</div>
				<div class="table-group">
				<?php if ($errormsg != ''){ ?>
					<div id="err" class="col-md-12 no-data"> <?php echo ($errormsg);?></div>
				<?php }else{
					if($search_data['target_year'] < ($current_yr-1) || $approved_flag == 2 || $completed_flag == 2){
						$readonly = 'disabled';
					}else{
						$readonly = '';
					}
					// if($search_data['target_year'] == $current_yr || $search_data['target_year'] == ($current_yr + 1))
						
					// };
					?>				
					<?php if (!empty($result_data_list)){ ?>
						<div class="col-sm-12 col-md-12 text-right adjust" style="padding:0px;">
							<div class="col-sm-12 col-md-12" style="padding:0px;">
							<?php if($showCommentBtn) : ?>
							<?php if(empty($comment)) : ?>
								<button type="button" class="btn btn-success btn_sumisho_set" data-toggle="modal" data-target="#laborCostCommentModal" <?php if($disabledCommentBtn)echo "disabled"; ?>>
									<?php echo __("コメント追加") ?>
								</button>
							<?php else : ?>
								<button type="button" class="btn btn-success btn_sumisho_set" data-toggle="modal" data-target="#laborCostCommentModal" <?php if($disabledCommentBtn)echo "disabled"; ?>>
									<?php echo __("コメントの編集") ?>
								</button>
							<?php endif; ?>  
							<?php endif; ?>
							<?php if($showReadBtn) : ?>
							<input type="button" name="btn_excel_download" id="btn_excel_download" class="btn btn-success btn_sumisho_set" value="<?php echo __("一括ダウンロード"); ?>">
							<?php endif; ?>
							<?php if($showSaveBtn) : ?>
							<input type="button" name="btn_save" id="btn_save" class="btn btn-success btn_sumisho_set" value="<?php echo __("一時保存"); ?>" <?php echo $readonly; ?> <?php if($disabledSaveBtn)echo "disabled"; ?>>
							<?php endif; ?>
							<?php
							if($search_data['target_year'] < ($current_yr-1) || $completed_flag == 2){
								
								$disabled_btn = 'disabled';
							}
							if($approved_flag == 1 && $showConfirmBtn): ?>
							<input type="button" name="btn_confirm" id="btn_confirm" class="btn btn-success btn_sumisho_set" value="<?php echo __("確定"); ?>" <?php echo $disabled_btn; ?> <?php if($disabledConfirmBtn)echo "disabled"; ?>>
							<?php elseif($approved_flag == 2 && $showConfirmCancelBtn): ?>
							<input type="button" name="btn_cancel_confirm" id="btn_cancel_confirm" class="btn btn-success btn_sumisho_set" value="<?php echo __("確定解除"); ?>" <?php echo $disabled_btn; ?> <?php if($disabledConfirmCancelBtn)echo "disabled"; ?>>
							<?php endif; ?>
							<input type="hidden" name="approved_flag" id="approved_flag" value="1">
							</div>
						</div>
						<div class="col-sm-12 col-md-12 row text">
							<h4><?php echo __("単位：千円"); ?></h4>
						</div>
						<div id="table-wpr">
							<!-- Budget Staffing Table -->
							<table class="budget_personal_tbl bu_analysis" id="budget_personal_tbl">
								<thead>
									<tr>
										<th class="tbl-header" ><?php echo __("#");?></th>
										<th class="tbl-header select-header"><?php echo __("氏名");?></th>
										<th class="tbl-header select-header"><?php echo __("等級");?></th>
										<!-- <th class="tbl-header"><?php echo __("人件費");?></th>
										<th class="tbl-header"><?php echo __("コーポレート費");?></th>
										<th class="tbl-header"><?php echo __("人員");?></th> -->
										<th class="tbl-header"><?php echo __("予算人員①");?></th>
										<th class="tbl-header"><?php echo __("共通費 免除②");?></th>
										<th class="tbl-header"><?php echo __("予算人員数（①+②）");?></th>
										<th class="tbl-header"><?php echo __("人件費単価");?></th>
										<th class="tbl-header"><?php echo __("ｺｰﾎﾟﾚｰﾄ経費割当単価");?></th>
										<th class="tbl-header"><?php echo __("人件費（年間）");?></th>
										<th class="tbl-header"><?php echo __("人件費単価（割戻）");?></th>
										<th class="tbl-header"><?php echo __("差異調整");?></th>
										<th class="tbl-header"><?php echo __("ｺｰﾎﾟﾚｰﾄ経費（年間）");?></th>
										<th class="tbl-header"><?php echo __("ｺｰﾎﾟﾚｰﾄ経費割当単価（割戻）");?></th>
										<th class="tbl-header"><?php echo __("差異調整");?></th>
									</tr>
								</thead>
								<tbody>
									<?php $num=1;$other_adjust=0; $adjust=0;?>								
									<?php  foreach($result_data_list as $list){  
										$user_id 	  		= $list['User']['id']?$list['User']['id']:'0';
										$user_name    		= $list['User']['user_name'];									
										// $position_id  		= $list['LaborCost']['position_id'];
										$position_code  		= $list['LaborCost']['position_code'];
										$position_name  	= $list['Position']['position_name'];
										$personnel_cost  	= number_format($list['Position']['personnel_cost'],2);
										$corporate_cost  	= number_format($list['Position']['corporate_cost'],2);
										$person_count  		= number_format($list['LaborCost']['person_count'],4);
										$b_person_count  	= number_format($list['LaborCost']['b_person_count'],4);
										$common_expense  			= number_format($list['LaborCost']['common_expense'],4);
										$b_person_total  			= number_format($list['LaborCost']['b_person_total'],4);
										$labor_unit  				= $list['LaborCost']['labor_unit'];
										$corpo_unit  				= $list['LaborCost']['corp_unit'];
										$yearly_labor_cost  		= $list['LaborCost']['yearly_labor_cost'];
										if(isset($list['LaborCost']['hid_unit_labor_cost'])) $unit_labor_cost  			= ($list['LaborCost']['b_person_count'] != 0.0000) ? $list['LaborCost']['unit_labor_cost'] : 0;
										else $unit_labor_cost 		= $list['LaborCost']['unit_labor_cost'];
										$hid_unit_labor_cost  		= $list['LaborCost']['hid_unit_labor_cost'];
										$adjust_labor_cost  		= number_format($list['LaborCost']['adjust_labor_cost']);
										$yearly_corpo_cost  		= $list['LaborCost']['yearly_corpo_cost'];
										$unit_corpo_cost 		= $list['LaborCost']['unit_corpo_cost'];
										if(isset($list['LaborCost']['hid_unit_corpo_cost'])) $unit_corpo_cost  		    = ($list['LaborCost']['b_person_count'] != 0) ? $list['LaborCost']['unit_corpo_cost'] : 0;
										else $unit_corpo_cost 		= $list['LaborCost']['unit_corpo_cost'];
										$hid_unit_corpo_cost  		= $list['LaborCost']['hid_unit_corpo_cost'];
										$adjust_corpo_cost  	    = number_format($list['LaborCost']['adjust_corpo_cost']);	
										$adjust_name  	    		= $list['LaborCost']['adjust_name'];
										$labor_id = $list['LaborCost']['labor_id'];
										if($adjust_name == 0 || $adjust_name==null || $adjust_name =='異動による差異調整'){
											$input_id = $user_id;
										}
										if(($position_code == 0 && $user_id == 0) ||  ($position_code != 0 && $user_id != 0)) {
											$unit_labor_cost = $list['LaborCost']['unit_labor_cost'];
											$unit_corpo_cost = $list['LaborCost']['unit_corpo_cost'];
										}
									?>	
									<?php if(($adjust_name != '異動による差異調整') && ($user_id == 0 || $user_id == '' || $user_id == NULL) && $user_name != ''){
										
										$username = "Labor".$labor_id;
										$input_id = $username;
										?> 
										<tr>
											<input type="hidden" id="<?php echo $username;?>_unit_labor_cost" value="<?php echo $hid_unit_labor_cost; ?>">
											<input type="hidden" id="<?php echo $username;?>_unit_corpo_cost" value="<?php echo $hid_unit_corpo_cost; ?>">
											<td style="text-align:center;"><?php echo $num;?></td>
											<td style="padding-left: 5px;"><?php echo $user_name;?></td>
											<td id="<?php echo $username;?>_position" style="padding-left: 5px;"><?php echo $position_name;?>
											<input type="hidden" id="<?php echo $username;?>_position_list" name="<?php echo $username;?>_position_list" value="<?php echo $position_code;?>"/>
											</td>
											
											<input type="hidden" id="<?php echo $username;?>_personnelCost" value="<?php if($personnel_cost!=""){echo $personnel_cost;}else{echo '0';} ?>" readonly>
											<input type="hidden" id="<?php echo $username;?>_corporateCost" value="<?php if($corporate_cost !=""){echo $corporate_cost;}else{echo '0';} ?>" readonly>
											
											
									<?php }							
									else if(($adjust_name !='異動による差異調整') && ($user_id !=0 || $user_id =='' )){//pr($position_list);//if 
										$input_id = $user_id;?> 
										<tr>
											<input type="hidden" id="<?php echo $user_id;?>_unit_labor_cost" value="<?php echo $hid_unit_labor_cost; ?>">
											<input type="hidden" id="<?php echo $user_id;?>_unit_corpo_cost" value="<?php echo $hid_unit_corpo_cost; ?>">
											<td style="text-align:center;"><?php echo $num;?></td>
											<td style="padding-left: 5px;"><?php echo $user_name;?></td>
											<td id="<?php echo $user_id;?>_position" style="padding-left: 5px;"><?php echo $position_name;?>
											<input type="hidden" id="<?php echo $user_id;?>_position_list" name="<?php echo $user_id;?>_position_list" value="<?php echo $position_code;?>"/>
											</td>
											<!-- <td id="<?php echo $user_id;?>_position">
											<?php //echo  $position_list;?>
											<select id="<?php echo $user_id;?>_position_list" name="<?php echo $user_id;?>_position_list" class="" onchange="salaryByPosition('<?php echo $user_id;?>')">
												<option value="0"></option>																				
											<?php foreach ($position_list as $pos_list) {?>		
													<option value = "<?php echo $pos_list['Position']['id']; ?>" <?php if($pos_list['Position']['id'] ==$position_id) echo 'selected="selected"';?>><?php echo $pos_list['Position']['position_name']; ?></option>
													<?php } ?>							
											</select>
											</td> -->
											<input type="hidden" id="<?php echo $user_id;?>_personnelCost" value="<?php if($personnel_cost!=""){echo $personnel_cost;}else{echo '0';} ?>" readonly>
											<input type="hidden" id="<?php echo $user_id;?>_corporateCost" value="<?php if($corporate_cost !=""){echo $corporate_cost;}else{echo '0';} ?>" readonly>
											<!-- <td id="" class="personnelCost">
											</td>
											<td id="">
											</td> -->
											
									<?php }else if($adjust_name =='異動による差異調整'){$adjust=1;?> 
										<tr class="adjust">
											<td><input type="text" id="adjust_name" class="adjust_name" name="adjust_name" value="<?php echo __($adjust_name);?>" readonly></td>
										
									<?php }else{
										$other_adjust ++;
										$input_id = $user_id.$other_adjust;
										if($other_adjust==1){ ?>
										<tr class="tr_clone" id="clone_adjust">
											<td class="gap">											
												<input type="button" class="btn-success clone_copy_add btn_sumisho_add"  id="cloneCopy" name="" value="<?php echo("+") ?>" <?php echo $readonly; ?>>
											</td>
										<?php 	}else{ ?>
										<tr class="tr_clone" id="clone_adjust_<?php echo $other_adjust;?>">
											<td class="gap">											
												<input type="button" class="btn-danger clone_remove"  style="width:100%" id="cloneCopy_adjust" name="" value="<?php echo("-") ?>" <?php echo $readonly; ?>>
											</td>
										<?php } ?> 	
										<td><input data-value="<?php echo $labor_id; ?>" class="input_cell otherAdjustName" type="text" id="<?php echo $input_id;?>_otherAdjustName" name="<?php echo $input_id;?>_otherAdjustName" value="<?php echo __($adjust_name);?>" <?php echo $readonly; ?>></td>						
									<?php } //row chk end?> 
											<input name="<?php echo $input_id;?>_personCount"  type="hidden" class="personCount" id="<?php echo $input_id;?>_personCount" value="<?php echo $person_count;?>" readonly>
											<!-- <td>
											</td> -->
											<td><input name="<?php echo $input_id;?>_bPersonCount" type="text" class="input_cell bPersonCount" id="<?php echo $input_id;?>_bPersonCount" value="<?php echo $b_person_count;?>" <?php echo $readonly; ?>></td>
											<td><input name="<?php echo $input_id;?>_commonExpense" type="text" class="input_cell commonExpense" id="<?php echo $input_id;?>_commonExpense" value="<?php echo $common_expense;?>" <?php echo $readonly; ?>></td>
											<td><input name="<?php echo $input_id;?>_bPersonTotal" type="text" class="bPersonTotal" id="<?php echo $input_id;?>_bPersonTotal" value="<?php echo $b_person_total;?>" <?php echo empty($readonly)? 'readonly' : $readonly; ?> ></td>
											<td>
												<input name="" 	type="text" class="laborUnitTxt" id="<?php echo $input_id;?>_laborUnitTxt" value="<?php echo number_format($labor_unit);?>" <?php echo empty($readonly)? 'readonly' : $readonly; ?>>
												<input name="<?php echo $input_id;?>_laborUnit" type="hidden" class="laborUnit" id="<?php echo $input_id;?>_laborUnit" 	value="<?php echo $labor_unit;?>">
											</td>
											<td>
												<input name="" 	type="text" class="corpoUnitTxt" id="<?php echo $input_id;?>_corpoUnitTxt" value="<?php echo number_format($corpo_unit);?>" <?php echo empty($readonly)? 'readonly' : $readonly; ?>>
												<input name="<?php echo $input_id;?>_corpoUnit" type="hidden" class="corpoUnit" id="<?php echo $input_id;?>_corpoUnit" 	value="<?php echo $corpo_unit;?>">
											</td>
											<td>
												<input name="" type="text" class="yearlyLaborCostTxt" id="<?php echo $input_id;?>_yearlyLaborCostTxt" value="<?php echo number_format($yearly_labor_cost);?>"  <?php echo empty($readonly)? 'readonly' : $readonly; ?>>
												<input name="<?php echo $input_id;?>_yearlyLaborCost" 		type="hidden" class="yearlyLaborCost" 				id="<?php echo $input_id;?>_yearlyLaborCost" value="<?php echo $yearly_labor_cost;?>"  <?php echo $readonly; ?>>
											</td>
											<td>
												<input name="" 	type="text" class="unitLaborCostTxt" id="<?php echo $input_id;?>_unitLaborCostTxt" 	value="<?php echo number_format($unit_labor_cost);?>"  <?php echo empty($readonly)? 'readonly' : $readonly; ?>>
												<input name="<?php echo $input_id;?>_unitLaborCost" type="hidden" class="unitLaborCost" id="<?php echo $input_id;?>_unitLaborCost" 	value="<?php echo $unit_labor_cost;?>">
											</td>
											<td><input name="<?php echo $input_id;?>_adjustLaborCost" 		type="text" class="input_cell adjustLaborCost" 	id="<?php echo $input_id;?>_adjustLaborCost" value="<?php echo $adjust_labor_cost;?>" <?php echo $readonly; ?>></td>
											<td>
												<input name="" type="text" class="yearlyCorpoCostTxt" id="<?php echo $input_id;?>_yearlyCorpoCostTxt" value="<?php echo number_format($yearly_corpo_cost);?>"  <?php echo empty($readonly)? 'readonly' : $readonly; ?>>
												<input name="<?php echo $input_id;?>_yearlyCorpoCost" type="hidden" class="yearlyCorpoCost" id="<?php echo $input_id;?>_yearlyCorpoCost" value="<?php echo $yearly_corpo_cost;?>">
											</td>
											<td>
												<input name="" 	type="text" class="unitCorpoCostTxt" id="<?php echo $input_id;?>_unitCorpoCostTxt" value="<?php echo number_format($unit_corpo_cost);?>"  <?php echo empty($readonly)? 'readonly' : $readonly; ?>>
												<input name="<?php echo $input_id;?>_unitCorpoCost" type="hidden" class="unitCorpoCost" id="<?php echo $input_id;?>_unitCorpoCost" 	value="<?php echo $unit_corpo_cost;?>">
											</td>
											<td><input name="<?php echo $input_id;?>_adjustCorpoCost" 		type="text" class="input_cell adjustCorpoCost" 				id="<?php echo $input_id;?>_adjustCorpoCost" value="<?php echo $adjust_corpo_cost;?>" <?php echo $readonly; ?>></td>

										</tr>
									<?php  $num++; } ?>
									<?php //for initial condition ?>
										<?php if($adjust==0){ ?>
											<tr class="adjust">
												<td><input type="text" id="adjust_name" class="adjust_name" name="adjust_name" value="<?php echo __("異動による差異調整");?>" readonly></td>
												<input name="0_personCount" type="hidden" class="personCount" id="0_personCount" value="0.0000">
												<!-- <td>
												</td> -->
												<td><input name="0_bPersonCount"  type="text" class="input_cell bPersonCount" id="0_bPersonCount" 	value="0.0000" <?php echo $readonly; ?>></td>
												<td><input name="0_commonExpense" type="text" class="input_cell commonExpense" 	id="0_commonExpense" value="0.0000" <?php echo $readonly; ?>></td>
												<td><input name="0_bPersonTotal" type="text" class="bPersonTotal" id="0_bPersonTotal" value="0.0000" <?php echo $readonly; ?>></td>
												<td><input name="0_laborUnit" type="text" class="laborUnit"  id="0_laborUnit" value="0" <?php echo $readonly; ?>></td>
												<td><input name="0_corpoUnit" 		type="text" class="corpoUnit" 					id="0_corpoUnit" 		value="0" <?php echo $readonly; ?>></td>
												<td><input name="0_yearlyLaborCost" 	type="text" class="yearlyLaborCost" 				id="0_yearlyLaborCost" 	value="0" <?php echo $readonly; ?>></td>
												<td><input name="0_unitLaborCost" 	type="text" class="unitLaborCost" 				id="0_unitLaborCost" 	value="0" <?php echo $readonly; ?>></td>
												<td><input name="0_adjustLaborCost" 	type="text" class="input_cell adjustLaborCost" 	id="0_adjustLaborCost"  value="0" <?php echo $readonly; ?>></td>
												<td><input name="0_yearlyCorpoCost" 	type="text" class="yearlyCorpoCost" 				id="0_yearlyCorpoCost" 	value="0" <?php echo $readonly; ?>></td>
												<td><input name="0_unitCorpoCost" 	type="text" class="unitCorpoCost" 				id="0_unitCorpoCost" 	value="0" <?php echo $readonly; ?>></td>
												<td><input name="0_adjustCorpoCost" 	type="text" class="adjustCorpoCost" 				id="0_adjustCorpoCost" 	value="0" <?php echo $readonly; ?>></td>
											</tr>			
										<?php } ?>
										<?php if($other_adjust==0){ ?>
											<tr class="tr_clone" id="clone_adjust">
												<td class="gap">											
													<input type="button" class="btn-success clone_copy_add btn_sumisho_add"  style="width:100%" id="cloneCopy" name="" value="<?php echo("+") ?>" <?php echo $readonly; ?>>
												</td>
												<td><input class="input_cell otherAdjustName" type="text" id="01_otherAdjustName" name="01_otherAdjustName" value="" <?php echo $readonly; ?>></td>
												<input name="01_personCount" type="hidden" class="personCount" id="01_personCount" value="0.0000" <?php echo $readonly; ?>>
												<!-- <td>
												</td> -->
												<td><input name="01_bPersonCount" 	type="text" class="input_cell bPersonCount"  				id="01_bPersonCount" 	value="0.0000" <?php echo $readonly; ?>></td>
												<td><input name="01_commonExpense" 	type="text" class="input_cell commonExpense" 	id="01_commonExpense" 	value="0.0000" <?php echo $readonly; ?>></td>
												<td><input name="01_bPersonTotal" 	type="text" class="bPersonTotal" 				id="01_bPersonTotal" 	value="0.0000" <?php echo $readonly; ?>></td>
												<td><input name="01_laborUnit" 		type="text" class="laborUnit" 					id="01_laborUnit" 		value="0" <?php echo $readonly; ?>></td>
												<td><input name="01_corpoUnit" 		type="text" class="corpoUnit" 					id="01_corpoUnit" 		value="0" <?php echo $readonly; ?>></td>
												<td><input name="01_yearlyLaborCost" 	type="text" class="yearlyLaborCost" 				id="01_yearlyLaborCost" 	value="0" <?php echo $readonly; ?>></td>
												<td><input name="01_unitLaborCost" 	type="text" class="unitLaborCost" 				id="01_unitLaborCost" 	value="0" <?php echo $readonly; ?>></td>
												<td><input name="01_adjustLaborCost" 	type="text" class="input_cell adjustLaborCost" 	id="01_adjustLaborCost"  value="0" <?php echo $readonly; ?>></td>
												<td><input name="01_yearlyCorpoCost" 	type="text" class="yearlyCorpoCost" 				id="01_yearlyCorpoCost" 	value="0" <?php echo $readonly; ?>></td>
												<td><input name="01_unitCorpoCost" 	type="text" class="unitCorpoCost" 				id="01_unitCorpoCost" 	value="0" <?php echo $readonly; ?>></td>
												<td><input name="01_adjustCorpoCost" 	type="text" class="adjustCorpoCost" 				id="01_adjustCorpoCost" 	value="0" <?php echo $readonly; ?>></td>												
											</tr>
										<?php } ?>					
									<tr class="total">
										<td colspan="3" class="total-name"><?php echo __("合　計");?></td>
										<input type="hidden" id="total_person_count" class="total_person_count" value="0" disabled>
										<!-- <td class="total_field">
										</td> -->
										<td class="total_field"><input type="text" id="total_b_person_count" class="total_b_person_count" value="0" disabled></td>
										<td class="total_field"><input type="text" id="total_common_expense" class="total_common_expense" value="0" disabled></td>
										<td class="total_field"><input type="text" id="total_b_person_total" class="total_b_person_total" value="0" disabled></td>
										<td class="total_field"><input type="text" id="total_labor_unit" class="total_labor_unit" value="0" disabled></td>
										<td class="total_field"><input type="text" id="total_corpo_unit" class="total_corpo_unit" value="0" disabled></td>
										<td class="total_field"><input type="text" id="total_yearly_labor_cost" class="total_yearly_labor_cost" value="0" disabled></td>
										<td class="total_field"><input type="text" id="total_unit_labor_cost" class="total_unit_labor_cost" value="0" disabled></td>
										<td class="total_field"><input type="text" id="total_adjust_labor_cost" class="total_adjust_labor_cost" value="0" disabled></td>
										<td class="total_field"><input type="text" id="total_yearly_corpo_cost" class="total_yearly_corpo_cost" value="0" disabled></td>
										<td class="total_field"><input type="text" id="total_unit_corpo_cost" class="total_unit_corpo_cost" value="0" disabled></td>
										<td class="total_field"><input type="text" id="total_adjust_corpo_cost" class="total_adjust_corpo_cost" value="0" disabled></td>									
									</tr>
								</tbody>
							</table>
							<!-- End LaborCost Table -->					
						</div>
					<?php } //end of not empty data?>
				<?php }//end of error msg chk?>
				</div><!-- table-group end -->
			</div>
			<?php endif ?>
			<br>
		</div>
	</div>
	<input type="hidden" name="json_data" id="json_data">
	<input type="hidden" name="old_data" id="old_data">
	<input type="hidden" id="hd_position_constant" name="hd_position_constant" value="0">
	<input type="hidden" id="hd_other_adjust_ctn" name="hd_other_adjust_ctn" value="">	
	<input type="hidden" id="hd_other_adjust_name" name="hd_other_adjust_name" value="">	
	<br><br><br>
	 <!-- Modal -->
	 <div class="modal fade" id="laborCostCommentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="closeBtnClick()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title" id="exampleModalCenterTitle">
					<?php 
					if(!empty($comment)) :
						echo __("コメントの編集");
					else:
						echo __("コメント追加");
					endif;?>
					</h5>
                </div>
                <div class="modal-body">
                    <div class="error-msg"></div>
                    <input type="hidden" name="page_name" id="page_name" value="LaborCosts">
                    <textarea class="form-control" id="lcd_comment" name="lcd_comment" rows="10" placeholder="0/500" style="resize: none;" maxlength="500"><?php echo str_replace('<br />', "", $comment['LcComment']['comment']); ?></textarea>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="update_id" name="update_id" value="<?php echo $comment['LcComment']['id']; ?>">
					<button type="button" class="btn btn-secondary <?php echo $comment ? '' : 'save'; ?>" id="closeButton" data-dismiss="modal" onClick="closeBtnClick()"><?php echo __("閉じる") ?></button>
                    <button type="button" class="btn btn-primary" onClick="onAddCommentHandler()"><?php echo empty($comment) ? __('追加') : __('変更'); ?></button>
                </div>
                </div>
            </div>
        </div>	
</div>
<?php
	echo $this->Form->end();
?>
<?= $this->Html->script('LaborCostDetail/accounting.min.js') ?>
<script type="text/javascript">
	$(document).ready(function(){

		$(window).on('beforeunload', () => {
			loadingPic()
		});

		document.onreadystatechange = pageLeaveLoading  = function () {
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

		$("#budget_personal_tbl .personCount").each(function () {
			var currentTrClass = $(this).closest('tr').attr('class'); 
			var pCountIdName = $(this).attr("id");
			var pCountId = pCountIdName.split("_")[0];
			var bPersonTotal = parseFloat($('#'+pCountId+'_bPersonCount').val().replace(/([,\s]+)/g, '')) + parseFloat($('#'+pCountId+'_commonExpense').val().replace(/([,\s]+)/g, ''));
			//console.log(parseFloat($('#'+pCountId+'_bPersonCount').val().replace(/([,\s]+)/g, '')));
			if(currentTrClass!="adjust" && currentTrClass!="tr_clone") $('#'+pCountId+'_bPersonTotal').val(number_format(bPersonTotal, 4));
        });
		$('[data-toggle="popover"]').popover(); 
		$(".adjust td:nth-child(1)").attr('colspan',3);//column span for adjust name
		$(".tr_clone td:nth-child(2)").attr('colspan',2);//for column span other adjust name
		$('.adjust').find('input[type=text]').not('.adjust_name').attr('readonly', false);//remove read only for adjust
		$('.adjust').find('input[type=text]').not('.adjust_name').addClass("input_cell");//add class for adjust font color
		$('.tr_clone').find('input[type!=button]').attr('readonly', false);//remove read only for other adjust
		$('.tr_clone').find('input[type!=button]').addClass("input_cell");//add class for other adjust font color		
		var cloned_row_count = $('#budget_personal_tbl tbody tr.tr_clone').length;//get row count of clone by class name/tr_clone
		document.getElementById("hd_other_adjust_ctn").value =cloned_row_count;	
		calculateVerticalTotal();
		chkNegativeValue();

		$("#target_yr, #layer_code").change(function (){
			btn_set();
		});

		// $(".user_position_pcount").each(function(index, element) { 
		// 	let user_id = $(element).attr('data-user-id');
		// 	$(element).onload = salaryByPosition(user_id);
		// });
	});
	
	function calculate_format(num){
		var pFloat = parseFloat(num.replace(/([,\s]+)/g, ''));
		return isNaN(pFloat) ? 0 : pFloat;
	}
	function number_format(num, deci_place) {
		return accounting.formatNumber(num, deci_place, ",", "."); // 4,999.99
	}	
	//cursor
	$("#budget_personal_tbl").on('focusin', ':input:not([readonly])',function() {
		var id = $("#"+this.id);
		id.data('oldVal', id.val());//store old value before next cursor
		var value = id.val().replace(/\,|\.0+$/g,'');
		value = (value == 0)? '' : value;
		id.val(value);
	});
	
	$("#budget_personal_tbl").on('focusout',':input:not([readonly])',function() {
		var class_name_arr1 = ["laborUnitTxt","corpoUnitTxt","yearlyLaborCostTxt","unitLaborCostTxt","adjustLaborCost","yearlyCorpoCostTxt","unitCorpoCostTxt","adjustCorpoCost"];
		var class_name_arr2 = ["personCount","bPersonCount","commonExpense","bPersonTotal"];		
		var id = $("#"+this.id);
		if(id.attr('id').split("_")[1] !="otherAdjustName"){//after _otherAdjustName
			if(jQuery.inArray(id.attr('id').split("_")[1], class_name_arr1) != -1){
				var hd_id  = this.id.slice(0, -3);//0_laborUnitTxt to 0_laborUnit //for hidden
				$(this).val(($(this).val() == '') ? '0' : formatNumber($(this).val()));//as input value decimal place and format
				$("#"+hd_id).val($(this).val());//set new value to hidden
			}else if(jQuery.inArray(id.attr('id').split("_")[1], class_name_arr2) != -1){//4th dec place
				$(this).val(($(this).val() == '') ? '0.0000' : number_format($(this).val(),4));
			}
			calculateVerticalTotal();
		}
	});
	//number format
	function formatNumber(num){
		if(num == ''){
			num = 0;
			return num.toFixed(1);
		}else{
			if(num.toString().indexOf('.') != -1) {
				var numArr = num.toString().split('.');
				var value = numArr[0];
				var value = value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
				if(numArr[1]) {
					return value+"."+numArr[1];
				}else {
					return value;
				}
			}else{
				var value = num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
				return value;
			} 
		}
    }
	function chkNegativeValue(){
		//for negative value, nan, infinity , change color
		$('#budget_personal_tbl').find('input[type=text]').each(function() {
			var input_id = this.id;
			var input_value = parseFloat(this.value);
			
			var input_name = input_id.split("_")[1];//after _
			if(input_name !='otherAdjustName' && input_id !='adjust_name'){
					//$("#"+input_id).addClass("negative");
					if((input_value < 0)){//if negative value, set color red						
						//if(input_name !='otherAdjustName' && input_id !='adjust_name'){
							$("#"+input_id).addClass("negative");
						//}
					}else if(isNaN(input_value) || !isFinite(input_value)){				
						//if(input_name !='otherAdjustName' && input_id !='adjust_name'){
							$("#"+input_id).val("0");
							//$("#"+input_id).addClass("negative");
						//}
						
					}else{
						$("#"+input_id).removeClass("negative");
					}
			}
			
		});
	}
    //for setting by SST 19.8.2022
	function btn_set(){
		// loadingPic();
		$("#error").empty();
	    $("#success").empty();
		document.forms[0].action = "<?php echo $this->webroot; ?>LaborCosts/showPersonalBudgetList";   
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;

	}
	//position on change
	function salaryByPosition(user_id){		
		var target_year = $('#target_yr').val();
		// var position_id = $("#"+user_id+"_position_list").val();
		var position_code = $("#"+user_id+"_position_list").val();		
		$.ajax({			
			type: "POST",
			url: "<?php echo $this->Html->url( array (
						'controller' => 'LaborCosts',
						'action' => 'getPositionSalary'
						)
					);
				?>",
			data: {
				// 'position_id': position_id,
				'position_code': position_code,
				'target_year': target_year,
				'user_id': user_id
				
			},
			dataType: 'json',
			success: function(result) {	
				var json=[] = result.position_salary_content;
				if(json.length !=0){
					var personnel_cost = json[0]['Position']['personnel_cost'];
					var corporate_cost = json[0]['Position']['corporate_cost'];				
				}else{
					var personnel_cost = "0.00";
					var corporate_cost = "0.00";				
				}
				$("#"+user_id+"_personnelCost").val(personnel_cost);
				$("#"+user_id+"_corporateCost").val(corporate_cost);
				$("#"+user_id+"_personCount").val(parseFloat(result.person_count_from_detail).toFixed(4));				
				$("#hd_position_constant").val(result.position_constant);
				calculationFunction(user_id);
				calculateVerticalTotal();
			}
		});
		
	}
	
	function calculationFunction(user_id){
		var personnel_cost			= calculate_format($("#"+user_id+"_personnelCost").val());
		if (isNaN(personnel_cost) || !isFinite(personnel_cost)) {
			personnel_cost=0;
		}	
		var corporate_cost          =calculate_format($("#"+user_id+"_corporateCost").val());
		if (isNaN(corporate_cost) || !isFinite(corporate_cost)) {
			corporate_cost=0;
		}
		var person_count 			=calculate_format($("#"+user_id+"_personCount").val());
		var hd_position_constant 	=calculate_format($("#hd_position_constant").val());	
		if(hd_position_constant==0 || hd_position_constant==null){
			var b_person_count = calculate_format($("#"+user_id+"_bPersonCount").val());
		}else{
			var b_person_count 			=parseFloat(person_count * hd_position_constant);
			$("#"+user_id+"_bPersonCount").val(calculate_format(parseFloat(b_person_count).toFixed(4)));//parseFloat(b_person_count).toFixed(4)
			$("#hd_position_constant").val('0');//set 0 after pos change
		}
		var common_expense          =calculate_format($("#"+user_id+"_commonExpense").val());		
		var b_person_total          =parseFloat(b_person_count + common_expense);
		$("#"+user_id+"_bPersonTotal").val(number_format(b_person_total,4));//parseFloat(b_person_total).toFixed(4)
		
		var labor_unit              =parseFloat(personnel_cost * person_count);	
		$("#"+user_id+"_laborUnitTxt").val(number_format(labor_unit,0));////parseFloat(labor_unit).toFixed(4)
		$("#"+user_id+"_laborUnit").val(number_format(labor_unit,4));////parseFloat(labor_unit).toFixed(4)

		//var corpo_unit              =parseFloat(corporate_cost * b_person_total);
		//var adjust_labor_cost       =calculate_format($("#"+user_id+"_adjustLaborCost").val(),0);
		var unitCorpoCost = calculate_format($("#"+user_id+"_unit_corpo_cost").val());
		var bPersonTotal = calculate_format($("#"+user_id+"_bPersonTotal").val());
        $("#"+user_id+"_corpoUnitTxt").val(number_format(bPersonTotal * unitCorpoCost,0));
		$("#"+user_id+"_corpoUnit").val(number_format(bPersonTotal * unitCorpoCost,4));
		var unitLaborCostTxt = calculate_format($("#"+user_id+"_laborUnit").val());
		var yearly_labor_cost = parseFloat(unitLaborCostTxt * 12) + calculate_format($("#"+user_id+"_adjustLaborCost").val());
		//var yearly_labor_cost       =parseFloat((labor_unit * 12) + adjust_labor_cost);//yearly_labor_cost=labor_unit*12+adjust_labor_cost
		$("#"+user_id+"_yearlyLaborCostTxt").val(number_format(yearly_labor_cost,0));
		$("#"+user_id+"_yearlyLaborCost").val(number_format(yearly_labor_cost,4));

		var unit_labor_cost         =parseFloat(yearly_labor_cost/person_count/12);			
		$("#"+user_id+"_unitLaborCostTxt").val(number_format(unit_labor_cost,0));
		if(!$.isNumeric( unit_labor_cost )) unit_labor_cost = 0;
		$("#"+user_id+"_unitLaborCost").val(number_format(unit_labor_cost,4));
		
		var unitCorpoUnitTxt = calculate_format($("#"+user_id+"_corpoUnit").val());
		var yearly_corpo_cost = parseFloat(unitCorpoUnitTxt * 12);
		//var adjust_corpo_cost = calculate_format($("#"+user_id+"_adjustCorpoCost").val(),0);
        //var yearly_corpo_cost = parseFloat((corpo_unit * 12) + adjust_corpo_cost);		
		$("#"+user_id+"_yearlyCorpoCostTxt").val(number_format(yearly_corpo_cost,0));	
		$("#"+user_id+"_yearlyCorpoCost").val(number_format(yearly_corpo_cost,4));	

		var unit_corpo_cost = parseFloat(yearly_corpo_cost/person_count/12);	
		if(unit_corpo_cost != -Infinity){
			$("#"+user_id+"_unitCorpoCostTxt").val(number_format(unit_corpo_cost,0));
			if(!$.isNumeric( unit_corpo_cost )) unit_corpo_cost = 0;
			$("#"+user_id+"_unitCorpoCost").val(number_format(unit_corpo_cost,4));
		}else{
			$("#"+user_id+"_unitCorpoCostTxt").val("0");
			$("#"+user_id+"_unitCorpoCost").val("0");
		}		
		
		chkNegativeValue();
	}
	$("#budget_personal_tbl").on('change', 'input',function() {	console.log('**');
		///check input only allow numbers,negative and decimal
		var currentTrClass = $(this).closest('tr').attr('class'); 
		var input_id = this.id;
		var resultID = input_id.split('_')[0];//before _		
		var input_name = input_id.split("_")[1];//after _
		var input_value = $(this).val();		
		let regexp = new RegExp(/^-?[0-9]\d*(\.\d+)?$/);//allow numbers,negative nums and decimal		
		if(input_name !='otherAdjustName'){
			if(regexp.test(input_value)==false){
				$(this).val('0');
			}
			if(input_name == 'bPersonCount') $("#"+resultID+"_personCount").val(number_format(input_value, 4));
			//calculate as formula (inot include adjust)
			if(input_name == 'bPersonCount' && currentTrClass!="adjust" && currentTrClass!="tr_clone"){
				var unitLaborCost = calculate_format($("#"+resultID+"_unit_labor_cost").val());
				var unitCorpoCost = calculate_format($("#"+resultID+"_unit_corpo_cost").val());
				var bPersonTotal = calculate_format($("#"+resultID+"_bPersonTotal").val());
				$('#'+resultID+'_laborUnitTxt').val(number_format(input_value * unitLaborCost, 0));
				$("#"+resultID+"_laborUnit").val(number_format(input_value * unitLaborCost,4));
				$('#'+resultID+'_corpoUnitTxt').val(number_format(bPersonTotal * unitCorpoCost, 0));
				$("#"+resultID+"_corpoUnit").val(number_format(bPersonTotal * unitCorpoCost,4));
				var unitLaborCostTxt = calculate_format($("#"+resultID+"_laborUnitTxt").val());
				var yearly_labor_cost = parseFloat(unitLaborCostTxt * 12);
				$("#"+resultID+"_yearlyLaborCostTxt").val(number_format(yearly_labor_cost,0));
				$("#"+resultID+"_yearlyLaborCost").val(number_format(yearly_labor_cost,4));

				
			}	
			
			if(currentTrClass!="adjust" && currentTrClass!="tr_clone"){
				
				calculationFunction(resultID);
			}	
			
			calculateVerticalTotal();
			chkNegativeValue();
			
		}
		let bPersonCount = $('#'+resultID+'_bPersonCount');
		let bPersonTotalCount = $('#'+resultID+'_bPersonTotal');
		if(input_name == 'bPersonCount'){
			// change bg color of value greater than 1
			if(bPersonCount.val() > 1){
				bPersonCount.addClass('bg-color');
				bPersonTotalCount.removeClass('bg-color');
			} else{
				bPersonCount.removeClass('bg-color');
				bPersonTotalCount.val() < 0 ? bPersonTotalCount.addClass('bg-color') : bPersonTotalCount.removeClass('bg-color');
			}
		}else if(input_name == 'commonExpense'){
			if(bPersonTotalCount.val() < 0){
				// change bg color of negative value of total
				bPersonCount.val() > 1 ? bPersonTotalCount.removeClass('bg-color') : bPersonTotalCount.addClass('bg-color');
			}else {
				bPersonTotalCount.removeClass('bg-color');
			}
		}else{
			bPersonCount.removeClass('bg-color');
			bPersonTotalCount.removeClass('bg-color');
		}
	});
	
	//for total
	function calculateVerticalTotal(){
		var total_person_count		=0;
		var total_b_person_count	=0;
		var total_common_expense    =0;
		var total_b_person_total    =0;
		var total_labor_unit		=0;
		var total_corpo_unit		=0;
		var total_yearly_labor_cost	=0;
		var total_unit_labor_cost	=0;
		var total_adjust_labor_cost	=0;
		var total_yearly_corpo_cost	=0;
		var total_unit_corpo_cost	=0;
		var total_adjust_corpo_cost	=0;

		$("#budget_personal_tbl .personCount").each(function () {
            var person_count_value = calculate_format($(this).val());
			total_person_count += parseFloat(person_count_value);
			
        });
		$("#budget_personal_tbl .bPersonCount").each(function () {
            var b_person_count_value = calculate_format($(this).val());
			total_b_person_count += parseFloat(b_person_count_value);
			
        });
		$("#budget_personal_tbl .commonExpense").each(function () {
            var common_expense_value = calculate_format($(this).val());
		    total_common_expense += parseFloat(common_expense_value);
			
        });
		$("#budget_personal_tbl .bPersonTotal").each(function () {
            var b_person_total_value = calculate_format($(this).val());
		    total_b_person_total += parseFloat(b_person_total_value);
			
        });
		$("#budget_personal_tbl .laborUnit").each(function () {
            var labor_unit_value = calculate_format($(this).val());
			total_labor_unit += parseFloat(labor_unit_value);
			
        });
		$("#budget_personal_tbl .corpoUnit").each(function () {
            var corpo_unit_value = calculate_format($(this).val());
			total_corpo_unit += parseFloat(corpo_unit_value);
			
        });
		$("#budget_personal_tbl .yearlyLaborCost").each(function () {
            var yearly_labor_cost_value = calculate_format($(this).val());
		    total_yearly_labor_cost += parseFloat(yearly_labor_cost_value);
			
        });
		$("#budget_personal_tbl .unitLaborCost").each(function () {
            var unit_labor_cost_value = (isNaN( calculate_format($(this).val()))) ? 0 :  calculate_format($(this).val());
			total_unit_labor_cost += parseFloat(unit_labor_cost_value);
			
        });
		$("#budget_personal_tbl .adjustLaborCost").each(function () {
            var adjust_labor_cost_value = calculate_format($(this).val());
		    total_adjust_labor_cost += parseFloat(adjust_labor_cost_value);
			
        });
		$("#budget_personal_tbl .yearlyCorpoCost").each(function () {
            var yearly_corpo_cost_value = calculate_format($(this).val());
		    total_yearly_corpo_cost += parseFloat(yearly_corpo_cost_value);
			
        });
		$("#budget_personal_tbl .unitCorpoCost").each(function () {
            var unit_corpo_cost_value = calculate_format($(this).val());
			total_unit_corpo_cost += parseFloat(unit_corpo_cost_value);
			
        });
		$("#budget_personal_tbl .adjustCorpoCost").each(function () {
            var adjust_corpo_cost_value = calculate_format($(this).val());
		    total_adjust_corpo_cost += parseFloat(adjust_corpo_cost_value);
			
        });				
		$(".total_person_count").val(number_format(parseFloat(total_person_count),4));
		$(".total_b_person_count").val(number_format(parseFloat(total_b_person_count),4));
		$(".total_common_expense").val(number_format(parseFloat(total_common_expense),4));
		$(".total_b_person_total").val(number_format(parseFloat(total_b_person_total),4));
		$(".total_labor_unit").val(number_format(Math.round(total_labor_unit),0));
		$(".total_corpo_unit").val(number_format(Math.round(total_corpo_unit),0));
		$(".total_yearly_labor_cost").val(number_format(Math.round(total_yearly_labor_cost),0));
		$(".total_unit_labor_cost").val(number_format(Math.round(total_unit_labor_cost),0));
		$(".total_adjust_labor_cost").val(number_format(Math.round(total_adjust_labor_cost),0));
		$(".total_yearly_corpo_cost").val(number_format(Math.round(total_yearly_corpo_cost),0));
		$(".total_unit_corpo_cost").val(number_format(Math.round(total_unit_corpo_cost),0));
		$(".total_adjust_corpo_cost").val(number_format(Math.round(total_adjust_corpo_cost),0));
		chkNegativeValue();
	}
	// var i=1;
	$(".clone_copy_add").click(function(){
		//ok clone 
		let i = $('.tr_clone').length;
		i++;
		var $tr    = $(this).closest('.tr_clone');
		var newClass='new_adjust'+i;
		var $clone = $tr.clone();
		var newId='clone_adjust_'+i;
		$(".tr_clone:last").after($clone);
		$clone.attr('id', newId);		
		$clone.find('td').each(function(){
            var el = $(this).find(':first-child');		
			var id = el.attr('id') || null;
			if(id !=null){//test by SST to replace by order in input text box id
				var adjust_input_id = id.split("_")[1];//after _
				if(adjust_input_id !== undefined){
					el.attr('id','0'+i+'_'+adjust_input_id);//replace textbox id
					console.log(adjust_input_id);
					el.attr('name','0'+i+'_'+adjust_input_id);//replace textbox name

				}
				
			}			
			var col_input_class = el.attr('class');
			if(id=="cloneCopy") {//button    
                el.attr('id','cloneCopy'+i);
				el.attr('class','btn-danger clone_remove');
                el.attr('value', '-');
            }else{//input field
				if(id.split("_")[1]=='otherAdjustName'){//if name set blank value after clone
					el.attr('value', '');
					/* when clone a row, the data of the cloned row is included */
					$("#"+"0"+i+"_otherAdjustName").val("");	
				}else{
					el.attr('value', '0');
					/* when clone a row, the data of the cloned row is included */
					$("#"+"0"+i+"_personCount").val("0.0000");
					$("#"+"0"+i+"_bPersonCount").val("0.0000");
					$("#"+"0"+i+"_commonExpense").val("0.0000");
					$("#"+"0"+i+"_bPersonTotal").val("0.0000");

					$("#"+"0"+i+"_laborUnit").val("0");
					$("#"+"0"+i+"_corpoUnit").val("0");
					$("#"+"0"+i+"_yearlyLaborCost").val("0");
					$("#"+"0"+i+"_unitLaborCost").val("0");
					$("#"+"0"+i+"_adjustLaborCost").val("0");
					$("#"+"0"+i+"_yearlyCorpoCost").val("0");
					$("#"+"0"+i+"_unitCorpoCost").val("0");
					$("#"+"0"+i+"_adjustCorpoCost").val("0");
				}					
			}
        });
		rearrangeClonedId();
		/* when clone a row, the data of the cloned row is included */
		$("#"+"0"+i+"_personCount").val("0.0000");
		$("#"+"0"+i+"_bPersonCount").val("0.0000");
		$("#"+"0"+i+"_commonExpense").val("0.0000");
		$("#"+"0"+i+"_bPersonTotal").val("0.0000");

		$("#"+"0"+i+"_laborUnit").val("0");
		$("#"+"0"+i+"_corpoUnit").val("0");
		$("#"+"0"+i+"_yearlyLaborCost").val("0");
		$("#"+"0"+i+"_unitLaborCost").val("0");
		$("#"+"0"+i+"_adjustLaborCost").val("0");
		$("#"+"0"+i+"_yearlyCorpoCost").val("0");
		$("#"+"0"+i+"_unitCorpoCost").val("0");
		$("#"+"0"+i+"_adjustCorpoCost").val("0");
		calculateVerticalTotal();
		
	});

	// delete the clone tr
	$('.table-group').on('click','.clone_remove', function() {
		var currentTbl = $(this).closest('table').attr('id');	
		var currentTr  = $(this).closest('tr').attr('id');
		//update flag to 0 in labor_cost tbl
		$('#'+currentTr).find('td').each (function() {
			var td_name = $(this).find(':first-child').attr('name');
			if(td_name.split("_")[1]=="otherAdjustName"){
				var td_value = $(this).find(':first-child').attr('value');
				$("#hd_other_adjust_name").val(td_value);
				var target_year 			= $('#target_yr').val();
				var layer_code 				= $("#layer_code").val();	
				var hd_other_adjust_name 	= $("#hd_other_adjust_name").val();

				$.ajax({			
					type: 'POST',
					url: '<?php echo $this->Html->url( array (
								'controller' => 'LaborCosts',
								'action' => 'deleteOtherAdjust'
								)
							);
						?>',
					data: {
						'target_year': target_year,
						'layer_code': layer_code,
						'hd_other_adjust_name': hd_other_adjust_name,

						
					},
					dataType: 'json',
					success: function(result) {
						console.log(result);
					},
					error: function(e) {
						alert("An error occurred");
					}
				});

			}			
		});

		$('#'+currentTbl+' #'+currentTr).remove();
		// i=i-1;
		rearrangeClonedId();
		calculateVerticalTotal();
		
	});
	//re arrange id after clone and remove
	function rearrangeClonedId(){
		var ctn=1;
		$('.tr_clone').each(function(){//loop tr by class			
			$(this).attr("id", "clone_adjust"+ctn); //arrange row by asc
			$(this).find('td').each (function() {
				// loop td
				var td_data = $(this).find(':first-child');				
				var td_id = td_data.attr('id');
				//for hidden
				var td_data2 = $(this).find(':last-child');				
				var td_id2 = td_data2.attr('id');
				var td_class = td_data.attr('class');
				var chkId = td_id.substr(0,9);
				if(chkId !="cloneCopy"){
					td_data.attr('id','0'+ctn+'_'+td_id.split("_")[1]);
					td_data.attr('name','0'+ctn+'_'+td_id.split("_")[1]);
					//for hidden
					td_data2.attr('id','0'+ctn+'_'+td_id2.split("_")[1]);
					td_data2.attr('name','0'+ctn+'_'+td_id2.split("_")[1]);
				}				
			});			
			ctn ++;
		});
		var cloned_row_count = $('#budget_personal_tbl tbody tr.tr_clone').length;//get row count of clone by class name/tr_clone
		document.getElementById("hd_other_adjust_ctn").value =cloned_row_count;
	}
	
    /*  
	*	Show hide loading overlay
	*	@Zeyar Min  
	*/
	function loadingPic() { 
		$("#overlay").show();
		$('.jconfirm').hide();  
	}

	function saveConfirm(){
		$("#error").empty();
	    $("#success").empty();
		let target_year = $("#target_yr").val();
		let layer_code = $("#layer_code").val();
		let content, first_btn, sec_btn, btn_type1, btn_type2, save_type;
		$.ajax({
			type:'post',
			url: "<?php echo $this->webroot; ?>LaborCosts/checkSaveMerge",
			data:{target_year : target_year, layer_code : layer_code},
			dataType: 'json',
			success: function(row) {
				content = $("#confirm_message").val();
				first_btn = '<?php echo __("はい");?>';
				sec_btn = '<?php echo __("いいえ");?>';
				save_type = true;
				btn_type1 = "btn-info";
				btn_type2 = "btn-default";
				var chk = true;
				var dataArr = [];
				var oldData = [];
				var myJSONString;
				$('.tr_clone').each(function(){
					var input_value;
					var chk_name="";
					$(this).find('td').each(function() {
						var td_data 	= $(this).find(':first-child');				
						var td_id 		= td_data.attr('id');
						var chkId 		= td_id.substr(0,9);
						if(td_id.split("_")[1]=="otherAdjustName"){
							chk_name = td_data.val();
							if(chk_name == null || chk_name == "") {
								var dataValue = $("#"+td_id).attr('data-value');
								dataArr.push(dataValue);		
							}
						}

						if(chk_name ==null || chk_name ==""){
							if(chkId !="cloneCopy"){
								input_value=td_data.val();
								if(input_value !=0){
									chk = false;
								}
							}
						}

					});
				});
				// var myJSONString = JSON.stringify(dataArr);

				if(!chk){
					$("#error").append("<div>"+errMsg(commonMsg.JSE001,['<?php echo __("調整名"); ?>'])+"</div>");//adjustment name
					$("html, body").animate({ scrollTop: 30 }, "fast");					
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
						content: content,                 
						buttons: {                    
							ok: {                 
								text: first_btn,                 
								btnClass: btn_type1,                 
								action: function(){
									if(!row){
										dataArr.push({"save_type" : "overwrite"});
										myJSONString = JSON.stringify(dataArr);
										oldData = JSON.stringify(<?php echo $old_data;?>);
										$('#json_data').val(myJSONString);
										$('#old_data').val(oldData);
									}else{
										dataArr.push({"save_type" : "merge"});
										myJSONString = JSON.stringify(dataArr);
										oldData = JSON.stringify(<?php echo $old_data;?>);
										$('#json_data').val(myJSONString);
										$('#old_data').val(oldData);
									}
									document.forms[0].action = "<?php echo $this->webroot; ?>LaborCosts/savePersonalBudget";   
									document.forms[0].method = "POST";
									document.forms[0].submit();
									loadingPic(); 
									return true;
									
								}                 
							},                      
							cancel : {
								text: sec_btn,                  
								btnClass: btn_type2,                  
								cancel: function(){
										return true;									
								}
							},                
						},                  
						theme: 'material',                  
						animation: 'rotateYR',                  
						closeAnimation: 'rotateXR'                  
					});
				}
			}
		});
	}
	
	$("#btn_save").click(function(){
		let saveErrorFlag = false;
		let budgetValues = [];
		let negativeValues = [];
		let bPersonCount = document.querySelectorAll("td input.bPersonCount");
		let bPersonTotal = document.querySelectorAll("td input.bPersonTotal");
		$("#successErrorMsg").empty();
		bPersonCount.forEach(function(td) {
			if(td.value > 1){
				document.getElementById(td.id).classList.add('bg-color');
				budgetValues.push(td.value);
			}else{
				document.getElementById(td.id).classList.remove('bg-color');
			}
		});
		bPersonTotal.forEach(function(td){
			if(td.value < 0){
				document.getElementById(td.id).classList.add('bg-color');
				negativeValues.push(td.value);
			}else{
				document.getElementById(td.id).classList.remove('bg-color');
			}
		});
		if(budgetValues.length > 0){
			$("#successErrorMsg")
			.html(`<div class="error">${errMsg(commonMsg.JSE093,['<?php echo __("予算人員①"); ?>'])}</div>`)
			.show();
			saveErrorFlag = true;
		}else if(negativeValues.length > 0){
			$("#successErrorMsg")
			.html(`<div class="error">${errMsg(commonMsg.JSE097)}</div>`)
			.show();
			saveErrorFlag = true;
		}

		if(!saveErrorFlag) {
			$("#confirm_message").val('<?php echo __("データを保存してよろしいですか。"); ?>');
			$("#approved_flag").val("1");
			saveConfirm();
		}
	});
	$("#btn_confirm").click(function(){
		let confirmErrorFlag = false;
		let budgetValues = [];
		let negativeValues = [];
		let bPersonCount = document.querySelectorAll("td input.bPersonCount");
		let bPersonTotal = document.querySelectorAll("td input.bPersonTotal");
		$("#successErrorMsg").empty();
		bPersonCount.forEach(function(td) {
			if(td.value > 1){
				document.getElementById(td.id).classList.add('bg-color');
				budgetValues.push(td.value);
			}else{
				document.getElementById(td.id).classList.remove('bg-color');
			} 
		});
		if(budgetValues.length > 0){
			$("#successErrorMsg")
			.html(`<div class="error">${errMsg(commonMsg.JSE093,['<?php echo __("予算人員①"); ?>'])}</div>`)
			.show();
			confirmErrorFlag = true;
		}
		bPersonTotal.forEach(function(td){
			if(td.value < 0){
				document.getElementById(td.id).classList.add('bg-color');
				negativeValues.push(td.value);
			}else{
				document.getElementById(td.id).classList.remove('bg-color');
			}
		});
		if(negativeValues.length > 0){
			$("#successErrorMsg")
			.html(`<div class="error">${errMsg(commonMsg.JSE097)}</div>`)
			.show();
			confirmErrorFlag = true;
		}
		if(!confirmErrorFlag){
			$("#confirm_message").val("<?php echo __('確定してもよろしいですか？'); ?>");
			$("#approved_flag").val("2");
			saveConfirm();
		}
	});
	$("#btn_cancel_confirm").click(function(){
		$("#approved_flag").val("1");
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
			content: '<?php echo __("確定をキャンセルしてもよろしいですか?"); ?>',                 
			buttons: {                    
				ok: {                 
					text: '<?php echo __("はい");?>',                 
					btnClass: "btn-info",                 
					action: function(){
						document.forms[0].action = "<?php echo $this->webroot; ?>LaborCosts/changeApprovedLogFlag";   
						document.forms[0].method = "POST";
						document.forms[0].submit();
						loadingPic(); 
						return true;
					}                 
				},                      
				cancel : {
					text: '<?php echo __("いいえ");?>',                  
					btnClass: "btn-default",                  
					cancel: function(){						
						return true;						
					}

				},                
			},                  
			theme: 'material',                  
			animation: 'rotateYR',                  
			closeAnimation: 'rotateXR'                  
		});	
	});
	$("#btn_excel_download").click(function(){
		$("#error").empty();
	    $("#success").empty();
		// document.forms[0].action = "<?php echo $this->webroot; ?>LaborCosts/downloadPersonalBudget";   
		// document.forms[0].method = "POST";
		// document.forms[0].submit();

		loadingPic();
		let fileName = '予算人員表_.xlsx';
		fetch("<?php echo $this->webroot; ?>LaborCosts/downloadPersonalBudget", {
			method: 'POST',
			body: new FormData(document.forms[0]),
		})
			.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					
					// $("#overlay").hide();
					let disposition = response.headers.get('Content-Disposition');

					// Try to extract the filename from the Content-Disposition header
					if (disposition && disposition.indexOf('attachment') !== -1) {
						let filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
						let matches = filenameRegex.exec(disposition);
						if (matches != null && matches[1]) {
								fileName = decodeURIComponent(matches[1].replace(/['"]/g, ''));
						}
					}
					return response.blob();
			})
			.then(blob => {
					let blobUrl = URL.createObjectURL(blob);
					let link = document.createElement('a');
					link.href = blobUrl;
					link.download = fileName;

					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);

					$("#overlay").hide();
			})
			.catch(error => {
					$("#overlay").hide();
					console.error('Error during fetch operation:', error.message);
			});
		return true;
	});

	function onAddCommentHandler() {
	// document.querySelector("#error").innerHTML = "";
	// document.querySelector("#success").innerHTML = "";
	let comment = document.querySelector("#lcd_comment").value;
	let btn_name = $("button[data-target=#laborCostCommentModal]").text();
	let errorFlag = true;
	if (comment.length == 0 && btn_name.trim() == "<?php echo __('コメント追加');?>") {
		$(".error-msg")
			.html(`<div class="error">${errMsg(commonMsg.JSE001, ["<?php echo __('コメント') ?>"])}</div>`)
			.show();
		errorFlag = false;
	} else if (comment.length > 500) {
		$(".error-msg")
			.html(`<div class="error">${errMsg(commonMsg.JSE094)}</div>`)
			.show();
		errorFlag = false;
	}
	if(errorFlag) {
		$('#laborCostCommentModal').modal('hide');
		loadingPic();
		document.forms[0].action = "<?= $this->webroot; ?>Common/saveAndUpdateComment";
		document.forms[0].method = "POST";
		document.forms[0].submit();	
	}
	}

	function closeBtnClick() {
	document.querySelector(".error-msg").innerHTML = "";
	let saveBtn = document.querySelector("#closeButton").classList.contains('save');
		if(saveBtn) {
			document.querySelector("#lcd_comment").value = "";
		} else {
		document.querySelector("#lcd_comment").value = document.querySelector(".comment-message").textContent;
	}
	}
	
</script>

</body>
</html>
