<style>
	.form-row {
		/* display: flex; */
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


	.btn-save {
		transition: 0.3s ease-out;
	}

	table,
	th {
		/*padding: 10px 15px;*/
		/* font-size: 1.2rem !important; */
		height: 5rem !important;
	}

	thead {
		/*position: sticky;*/
		top: 0;
		z-index: 990 !important;
	}

	.table-container {
		/*position: sticky;*/
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

	/*.heading_line_title{
        height: 440.99px;
        margin: 0px;
    }*/
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
			/*position: sticky;*/
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
		if ($('#rtax').length > 0) {
            $("#rtax").floatThead({position: 'absolute'});
        }
		$("#show_up_only").hide();
		/* 
		 * Zeyar Min
		 * year picker
		 */
		$("#datepicker").datepicker({
			format: "yyyy",
			viewMode: "years",
			minViewMode: "years",
			autoclose: true //to close picker once year is selected
		});
		//add percentage sign to input value
		/*$("input[name='percentage']").on('input', function() {
			$(this).val(function(i, v) {
				return v.replace('%', '') + '%';
			});
		});*/
	});

	function FeesRateSave() {
		document.querySelector("#error").innerHTML = "";
		document.querySelector("#success").innerHTML = "";

		let targetYear = document.querySelector("#target_year").value;
		let taxFeesRates = document.querySelector("#fee_rates").value;
		// let taxFeesRates = taxFeesRatesVal.substring(0, taxFeesRatesVal.length - 1);

		let errorFlag = true;

		if (!checkNullOrBlank(targetYear)) {

			let newbr = document.createElement("div");
			let a = document.querySelector("#error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("対象年度"); ?>'])));
			document.querySelector("#error").appendChild(a);
			errorFlag = false;
		}

		if (!checkNullOrBlank(taxFeesRates)) {

			let newbr = document.createElement("div");
			let a = document.querySelector("#error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("税率"); ?>'])));
			document.querySelector("#error").appendChild(a);
			errorFlag = false;
		}
		if (taxFeesRates.indexOf(' ') >= 0) {
			let newbr = document.createElement("div");
			let a = document.querySelector("#error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE063, ['<?php echo __("税率") ?>'])));
			document.querySelector("#error").appendChild(a);
			errorFlag = false;
		}
		if (isNaN(taxFeesRates)) {
			let newbr = document.createElement("div");
			let a = document.querySelector("#error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007, ['<?php echo __("税率") ?>'])));
			document.querySelector("#error").appendChild(a);
			errorFlag = false;
		}


		/*Changed by PanEiPhyo (20200313), check layer_code null for Sales TL and Sales Incharge*/
		if (errorFlag) {
			$.confirm({
				title: "<?php echo __('保存確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'green',
				boxWidth: '30%',
				useBootstrap: false,
				typeAnimated: true,
				animateFromElement: true,
				animation: 'top',
				draggable: false,
				content: "<?php echo __("データを保存してよろしいですか。"); ?>",
				buttons: {
					ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
						action: function() {
							loadingPic();
							document.forms[0].action = "<?php echo $this->webroot; ?>RtaxFees/add";
							document.forms[0].method = "POST";
							document.forms[0].submit();
							return false;
						}
					},
					cancel: {
						text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
						btnId: 'btn_cancel',
						cancel: function(msg) {
							animation: scrollText();
							$("#btn_cancel").html(msg);
							$('html, body').animate({
								scrollTop: 0
							}, 0);
						}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		}
		scrollText();
	}

	function EditClick(id) {
		document.querySelector("#error").innerHTML = '';
		document.querySelector("#success").innerHTML = '';

		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>RtaxFees/editData",
			data: {
				id: id
			},
			dataType: 'json',
			beforeSend: function() {
				loadingPic();
			},
			success: function(data) {
				console.log(data);
				let id = data['id'];
				let target_year = data['target_year'];
				let rate = data['rate'];

				$("#target_year").val(target_year);
				$(".year-picker").hide();
				$("#show_up_only").show();
				$("#show_up_only").val(target_year);
				// $("#fee_rates").val(rate + '%');
				$("#fee_rates").val(rate);
				$('#primary_id').val(id);

				$('#save').hide();
				$('#update').show();
				$('#overlay').hide();
			}

		})
	}

	function FeesRateUpdate() {
		let targetYear = document.querySelector("#target_year").value;
		let taxFeesRates = document.querySelector("#fee_rates").value;
		// let taxFeesRates = taxFeesRatesVal.substring(0, taxFeesRatesVal.length - 1);

		let errorFlag = true;

		if (!checkNullOrBlank(targetYear)) {

			let newbr = document.createElement("div");
			let a = document.querySelector("#error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("対象年度"); ?>'])));
			document.querySelector("#error").appendChild(a);
			errorFlag = false;
		}

		if (!checkNullOrBlank(taxFeesRates)) {

			let newbr = document.createElement("div");
			let a = document.querySelector("#error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("税率"); ?>'])));
			document.querySelector("#error").appendChild(a);
			errorFlag = false;
		}
		if (taxFeesRates.indexOf(' ') >= 0) {
			let newbr = document.createElement("div");
			let a = document.querySelector("#error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE063, ['<?php echo __("税率") ?>'])));
			document.querySelector("#error").appendChild(a);
			errorFlag = false;
		}
		if (isNaN(taxFeesRates)) {
			let newbr = document.createElement("div");
			let a = document.querySelector("#error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007, ['<?php echo __("税率") ?>'])));
			document.querySelector("#error").appendChild(a);
			errorFlag = false;
		}


		if (errorFlag) {
			$.confirm({
				title: "<?php echo __('変更確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'green',
				typeAnimated: true,
				animateFromElement: true,
				animation: 'top',
				draggable: false,
				boxWidth: '30%',
				useBootstrap: false,
				content: "<?php echo __('データを変更してよろしいですか。'); ?>",
				buttons: {
					ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
						action: function() {
							loadingPic();
							document.forms[0].action = "<?php echo $this->webroot; ?>RtaxFees/add";
							document.forms[0].method = "POST";
							document.forms[0].submit();
							return false;
						}
					},
					cancel: {
						text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
						cancel: function() {
							console.log('the user clicked cancel');
						}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		}
		return true;
	}

	function DeleteClick(id) {

		document.querySelector("#error").innerHTML = '';
		document.querySelector("#success").innerHTML = '';
		document.querySelector("#id").value = id;

		let errorFlag = true;

		let path = window.location.pathname;
		let page = path.split("/").pop();
		// document.querySelector('#hid_page_no').value = page;

		if (errorFlag) {
			$.confirm({
				title: "<?php echo __('削除確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'red',
				boxWidth: '30%',
				useBootstrap: false,
				typeAnimated: true,
				animateFromElement: true,
				animation: 'top',
				draggable: false,
				content: "<?php echo __('データを削除してよろしいですか。'); ?>",
				buttons: {
					ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
						action: function() {
							loadingPic();
							document.forms[0].action = "<?php echo $this->webroot; ?>RtaxFees/delete";
							document.forms[0].method = "POST";
							document.forms[0].submit();
							return true;
						}
					},
					cancel: {
						text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
						cancel: function() {

						}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		}
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
	/*  
	 *	Show hide loading overlay
	 *	@Zeyar Min  
	 */
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
		<form action="RtaxFees/add" class="form-inline" id="UsersIndexForm" method="post" accept-charset="utf-8">
			<div style="display:none;">
				<input type="hidden" name="_method" value="POST" />
				<input type="hidden" name="id" id="id" />
			</div>
			<fieldset>
				<legend><?php echo __('税率管理'); ?></legend>
				<div class="success" id="success"><?php echo ($this->Session->check("Message.UserSuccess")) ? $this->Flash->render("UserSuccess") : ''; ?><?php echo ($this->Session->check("Message.SuccessMsg")) ? $this->Flash->render("SuccessMsg") : ''; ?></div>
				<div class="error" id="error"><?php echo ($this->Session->check("Message.Error")) ? $this->Flash->render("Error") : ''; ?></div>
				<div class="form-row">
					<!-- Form Group 1 -->
					<div class=" form-group col-md-6">
						<label for="target_year" class="control-label required">
							<?php echo __('対象年度'); ?>
						</label>
						<div class="form-control form-input year-picker">
							<div class="input-group date form-input" id="datepicker" data-provide="yearPicker">
								<input type="text" class="form-control target" id="target_year" name="target_year" value="" autocomplete="off" style="background-color: #fff;" readonly>
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
						<input type="text" class="form-control target form_input" id="show_up_only" name="target_year" value="" autocomplete="off" style="background-color: #fff;" disabled>
					</div>
					<!-- Form Group 2 -->
					<div class="form-group col-md-6">
						<label class="control-label required"><?php echo __("税率"); ?></label>
						<div class="input-group form_input">
							<input type="text" name="percentage" class="form-control" id='fee_rates' maxlength="5" aria-label="Amount (rounded to the nearest dollar)">
							<span class="input-group-addon">%</span>
						</div>
					</div>
					<!-- Form Group 3 empty column-->
					<div class="form-group col-md-6">
					</div>

					<!-- Form Group 4 buttons -->
					<div class="form-group col-md-6 save-row">
						<div class="submit btn-save-wpr" id="save">
							<input onclick="FeesRateSave();" type="button" value="<?php echo __("保存"); ?>" class="btn-save" />
						</div>
						<!-- update -->
						<div class="submit btn-save-wpr " id="update">
							<input type="hidden" name="primary_id" id="primary_id" value=""/>
							<input onclick="FeesRateUpdate();" type="button" value="<?php echo __("変更"); ?>" class="btn-save update-btn" />
						</div>
					</div>
				</div>
			</fieldset>
		</form>
	</div>

	<?php if (!empty($datas)) { ?>
		<div class="msgfont" id="total_row" style="padding-left: 15px;">
			<?= $count ?>
		</div>
		<div class="table-container rtaxFees index" style="padding-left: 15px;margin-bottom: 5rem;">
			<table cellpadding="0" cellspacing="0" class="table table-bordered" id="rtax">
				<thead>
					<tr>
						<th><?php echo __("#"); ?></th>
						<th><?php echo __("対象年度"); ?></th>
						<th><?php echo __("税率"); ?></th>
						<th colspan="2" style="min-width: 10rem;"><?php echo __("アクション"); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $index = 0;
					foreach ($datas as $rtaxFee) :  $index++; ?>
						<tr>
							<td style="vertical-align:middle;"><?= h($index) ?></td>
							<td style="vertical-align:middle;"><?= h($rtaxFee['RTaxFee']['target_year']) ?></td>
							<td style="vertical-align:middle;"><?= h($rtaxFee['RTaxFee']['rate']) ?>%</td>
							<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='edit'>
								<a class="" href="#" onclick="EditClick(<?= h($rtaxFee['RTaxFee']['id']) ?>)" title="<?php echo __('編集'); ?>"><i class="fa-regular fa-pen-to-square"></i>
								</a>
							</td>
							<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='remove'>
								<a class="delete_link" href="#" onclick="DeleteClick(<?= h($rtaxFee['RTaxFee']['id']) ?>)" title="<?php echo __('削除'); ?>"><i class="fa-regular fa-trash-can"></i></a>
							</td>
							<!-- <?php $rs_disable = (empty($result['User']['password'])) ? 'disabled' : '' ?> -->
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="col-md-12" style="padding: 10px;text-align: center;margin-bottom: 50px;">
				<div class="paging">
					<?php
					if ($query_count > Paging::TABLE_PAGING) {
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
        <div id="err" class="no-data"><?php echo ($noDataMsg); ?></div>
   <?php } ?>

</div>


<!-- <div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Rtax Fee'), array('action' => 'add')); ?></li>
	</ul>
</div> -->