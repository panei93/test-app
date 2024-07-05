<style>
	.form-row {
		flex-direction: column;
	}

	.save-row {
		margin-bottom: 2rem;
		height: 10vh !important;
	}

	.content {
		margin-bottom: 2rem;
	}

	.yearPicker {
		z-index: 1999 !important;
	}

	a .fa-trash-can {
		color: #bf0603;
	}


	.btn-save, .btn-update {
		transition: 0.3s ease-out;
	}

	table, th {
		height: 5rem !important;
	}

	thead {
		top: 0;
		z-index: 990 !important;
	}

	.table-container {
		top: 0;
	}

	.nav>li>a {
		padding-left: 14px;
	}

	div#update {
		display: none;
	}

	div#radio_premission label {
		padding: 0px;
	}

	div#radio_premission {
		margin-left: 0px;
	}

	div#permission_wpr {
		display: none;
	}

	.fix_table {
		height: 200px;
	}

	.ba-select-label {
		padding: 0;
	}

	.ba-select-div {
		padding: 0;
		width: 80%;
	}

	.amsify-selection-label {
		width: 90% !important;
		margin-left: 4px;
	}

	.register_form .btn-save-wpr {
		width: 80.5%;
		display: flex;
		justify-content: flex-end;
	}

	.update-btn {
		float: right;
	}

	.amsify-selection-list {
		margin-left: 5px;
		width: 90% !important;
	}

	.form-control.year-picker {
		padding: 0 !important;
		border: none !important;
		width: 60%;
	}

	.form-control .form-input {
		width: 100%;
	}

	.floatThead-wrapper {
		z-index: 990 !important;
	}

	@media (max-width:768px) {
		thead {
			top: 0;
			z-index: 990 !important;
		}

		.table-container {
			width: 100%;
			overflow-x: auto;
		}

		.table-bordered {
			border: none;
			display: block;
			white-space: nowrap;
			padding: 0;
			height: 40% !important;
		}

		table {
			min-width: 1483px;
		}

		.form-control.year-picker {
			padding: 0 !important;
			border: none !important;
			width: 100%;
		}

		.register_form .btn-save-wpr {
			width: 100%;
		}
	}

	@media (max-width: 992px) {
		.register {
			width: 100% !important;
		}

		.fix_table {
			height: auto;
		}

		#radio_premission {
			padding-left: 35px;
		}
	}

	#btn_browse {
		float: right !important;
		margin-top: 5px;
		margin-left: 15px;
	}

	.disabled a {
		pointer-events: none;
		color: #ccc;
	}

	.jconfirm.jconfirm-material .jconfirm-box {
		padding: 30px 10px 10px 14px;
	}

	#tbl_user .new {
		background-color: #f7d0d742;
	}
</style>
<script>
	$(document).ready(function() {
		if ($('#interestcost').length > 0) {
            $("#interestcost").floatThead({position: 'absolute'});
        }

        $('#update').hide();
		$('#show_up_only').hide();
		
		$('#rate').on('keyup', function(){
			if(!isDecimal(this.value)) {
				$(this).val('');
			}
		});

		$('#datepicker').datepicker({
			format: "yyyy",
			viewMode: "years",
			minViewMode: "years",
			autoclose: true //to close picker once year is selected
		});

		$('#save').click(function() {
			document.getElementById("error").innerHTML   = '';
         	document.getElementById("success").innerHTML   = '';

         	$('#mode').val('save');
         	if(checkValidation()) {
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
		            content: "<?php echo __("データを保存してよろしいですか。");?>",
		            buttons: {
		               	ok: {
		                  	text: "<?php echo __('はい'); ?>",
		                  	btnClass: 'btn-info',
		                  	action:function(){
		                     	loadingPic();                          
		                     	document.forms[0].action = "<?php echo $this->webroot; ?>InterestCost/interestSaveUpdate";
		                     	document.forms[0].method = "POST";
		                     	document.forms[0].submit();
		                     	return true;
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
		});

		$('#update').click(function() {
			document.getElementById("error").innerHTML   = '';
         	document.getElementById("success").innerHTML   = '';

         	$('#mode').val('update');
         	if(checkValidation()) {
		        $.confirm({
		            title: "<?php echo __('変更確認'); ?>",
		            icon: 'fas fa-exclamation-circle',
		            type: 'green',
		            typeAnimated: true,
		            closeIcon: false,
		            columnClass: 'medium',
		            animateFromElement: true,
		            animation: 'top',
		            draggable: false,  
		            content: "<?php echo __("データを変更してよろしいですか。");?>",
		            buttons: {
		               	ok: {
		                  	text: "<?php echo __('はい'); ?>",
		                  	btnClass: 'btn-info',
		                  	action:function(){
		                     	loadingPic();                          
		                     	document.forms[0].action = "<?php echo $this->webroot; ?>InterestCost/interestSaveUpdate";
		                     	document.forms[0].method = "POST";
		                     	document.forms[0].submit();
		                     	return true;
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
		});
	});
	
	function EditInterest(id) {
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';
		
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>InterestCost/editInterest",
			data: {id: id},
			dataType: 'json',
			beforeSend: function() {
				loadingPic();
			},
			success: function(data) {
				
				let id = data['InterestCost']['id'];
				let target_year = data['InterestCost']['target_year'];
				let account_code = data['InterestCost']['account_code']
				let rate = data['InterestCost']['rate'];
				
				$(".year-picker").hide();
				$("#show_up_only").show();
				$("#edit_id").val(id);
				$("#show_up_only").val(target_year);
				$("#rate").val(rate);
				$('#account_code option[value="' + account_code + '"]').prop('selected', true);

				$('#save').hide();
				$('#update').show();
				$('#overlay').hide();
			}

		});
	}

	function DeleteInterest(id) {
		document.getElementById("error").innerHTML   = '';
        document.getElementById("success").innerHTML   = '';
        document.getElementById("edit_id").value   = id;

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
            content: "<?php echo __("データを削除してよろしいですか。");?>",
            buttons: {
               	ok: {
                  	text: "<?php echo __('はい'); ?>",
                  	btnClass: 'btn-info',
                  	action:function(){
                     	loadingPic();                          
                     	document.forms[0].action = "<?php echo $this->webroot; ?>InterestCost/interestDelete";
                     	document.forms[0].method = "POST";
                     	document.forms[0].submit();
                     	return true;
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

	function checkValidation() {
		var err_msg = '';
		var validate = true;

		$("#hid_target_year").val(($("#target_year").val() == '' || $("#target_year").val() == undefined) ? $("#show_up_only").val() : $("#target_year").val());
		
		var target_year = $("#hid_target_year").val();
		
		var account_code = ($("#account_code").val() == '0') ? '' : $("#account_code").val();
		var rate = $.trim($("#rate").val());
		
		if(!checkNullOrBlank(target_year)){
			err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("対象年度");?>'])+"<br/>";
			validate = false;
		}
		if(!checkNullOrBlank(account_code)){
			err_msg += 	errMsg(commonMsg.JSE002,['<?php echo __("勘定科目コード");?>'])+"<br/>";
			validate = false;
		}
		if(!checkNullOrBlank(rate)){
			err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("料金");?>'])+"<br/>";
			validate = false;
		}
		if(!validate) {
			$("#error").empty();
			$("#success").empty();
			$("#error").append(err_msg);
			$("#error").show();
			$("html, body").animate({ scrollTop: 0 }, 'slow');
		}
		return validate;
	}
	
	function scrollText() {
		let successpage = $('#error').text();
		let errorpage = $('.success').text();

		if (successpage) {
			$("html, body").animate({
				scrollTop: 0
			}, "slow");
		}
		if (errorpage) {
			$("html, body").animate({
				scrollTop: 0
			}, "slow");
		}
	}

	function loadingPic() {
		$("#overlay").show();
		$('.jconfirm').hide();
	}
</script>
<div id="overlay">
	<span class="loader"></span>
</div>
<div class="content">
	<div class="register_form">
		<form action="InterestRates/add" class="form-inline" id="UsersIndexForm" method="post" accept-charset="utf-8">
			<div style="display: none;">
				<input type="text" id="edit_id" name="edit_id">
				<input type="text" id="hid_target_year" name="target_year">
				<input type="text" id="mode" name="mode">
			</div>
			<fieldset>
				<legend><?php echo __('金利'); ?></legend>
			   <div class="success" id="success"><?php echo ($this->Session->check("Message.interestOK"))? $this->Flash->render("interestOK") : ''; ?></div>
			   <div class="error" id="error"><?php echo ($this->Session->check("Message.interestFail"))? $this->Flash->render("interestFail") : ''; ?></div>
				<div class="form-row">
					
					<div class=" form-group col-md-6">
						<label for="target_year" class="control-label required">
							<?php echo __('対象年度'); ?>
						</label>
						<div class="form-control form-input year-picker">
							<div class="input-group date form-input" id="datepicker" data-provide="yearPicker">
								<input type="text" class="form-control target" id="target_year" value="" autocomplete="off" style="background-color: #fff;" readonly>
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
						<input type="text" class="form-control target form_input" id="show_up_only" value="" autocomplete="off" style="background-color: #fff;" disabled>
					</div>
					
					<div class="form-group col-md-6">
						<label class="control-label required"><?php echo __("勘定科目コード"); ?></label>
						<div class="input-group form_input">
							<select class="form-control" id="account_code" name="account_code">
								<option value="0"><?php echo '----- Select Account Code -----'; ?></option>
								<?php foreach ($interest_costs as $account_code => $account_name) { ?>
									<option value="<?php echo $account_code; ?>"><?php echo $account_code.' / '.$account_name; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					
					<div class="form-group col-md-6">
						<label class="control-label required"><?php echo __("料金"); ?></label>
						<div class="input-group form_input">
							<input type="text" name="rate" class="form-control" id='rate' maxlength="5" aria-label="Amount (rounded to the nearest dollar)">
							<span class="input-group-addon">%</span>
						</div>
					</div>

					<div class="form-group col-md-6 save-row">
						<div class="submit btn-save-wpr">
							<input type="button" value="<?php echo __("保存"); ?>" class="btn-save" id="save" />
						</div>
						
						<div class="submit btn-save-wpr ">
							<input type="button" value="<?php echo __("変更"); ?>" class="btn-save update-btn"  id="update" />
						</div>
					</div>
				</div>
			</fieldset>
		</form>
	</div>

	<?php if (!empty($datas)) { ?>
		<div class="msgfont" style="padding-left: 15px;">
			<?php echo $row_count; ?>
		</div>
		<div class="table-container index" style="padding-left: 15px;margin-bottom: 5rem;">
			<table cellpadding="0" cellspacing="0" class="table table-bordered" id="interestcost">
				<thead>
					<tr>
						<th width="50px"><?php echo __("#"); ?></th>
						<th><?php echo __("対象年度"); ?></th>
						<th><?php echo __("勘定コード名"); ?></th>
						<th><?php echo __("料金"); ?></th>
						<th colspan="2" style="min-width: 10rem;"><?php echo __("アクション"); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $no = 0;
					if($pageno > 1) $no = Paging::TABLE_PAGING * ($pageno - 1);
					foreach ($datas as $interestCost) :  $no++; ?>
						<tr>
							<td style="vertical-align:middle;"><?php echo $no; ?></td>
							<td style="vertical-align:middle;"><?php echo $interestCost['InterestCost']['target_year']; ?></td>
							<td style="vertical-align:middle;"><?php echo $interestCost['InterestCost']['account_code']." / ".$interest_costs[$interestCost['InterestCost']['account_code']]; ?></td>
							<td style="vertical-align:middle;"><?php echo $interestCost['InterestCost']['rate']; ?>%</td>
							<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='edit'>
								<a class="" id='edit' href="#" onClick="EditInterest(<?php echo $interestCost['InterestCost']['id'] ?>)" data-id="<?php echo $interestCost['InterestCost']['id'] ?>" title="<?php echo __('編集'); ?>"><i class="fa-regular fa-pen-to-square"></i>
								</a>
							</td>
							<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='remove'>
								<a class="delete_link" href="#" onclick="DeleteInterest(<?php echo $interestCost['InterestCost']['id'] ?>)" title="<?php echo __('削除'); ?>"><i class="fa-regular fa-trash-can"></i></a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="col-md-12" style="padding: 10px;text-align: center;margin-bottom: 50px;">
				<div class="paging">
					<?php
					if ($count > Paging::TABLE_PAGING) {
						echo $this->Paginator->first('<<');
						echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev disabled'));
						echo $this->Paginator->numbers(array('separator' => '', 'modulus' => 6));
						echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
						echo $this->Paginator->last('>>');
					}
					?>
				</div>
			</div>
		</div>
		<?php }else{?>
        <div id="err" class="no-data"><?php echo ($no_data); ?></div>
   <?php } ?>
</div>