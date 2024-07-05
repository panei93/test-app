<style>
	.date-wrapper {
		margin: 15px;
		border: 1px solid #ccc;
		border-radius: 5px;
		margin-top: 30px;
		padding-top: 15px;
		padding-bottom: 10px;
	}

	.align_adjust {
		text-align: center !important;
		margin: 30px 0px;
	}

	.tbl_data_list td {
		vertical-align: middle;
		padding: 10px;
	}

	#term_tbl {
		padding-left: 0px;
		table-layout: fixed;
	}
	table,
	th {
		text-align: center;
		padding: 10px 15px;
	}

	/*.yearpicker, .datepicker {
	z-index: 1999 !important;
}*/

	/*.yearPicker {
	z-index: 2000 !important;
}*/
.zoom {
  zoom: 80%;
}


	#Inactive_status {
		color: #ff9b85;
	}

	.popup_row {
		padding-bottom: 50px;
	}

	.modal-content.contantbond {
		height: 308px;
	}

	.table-responsive {
		overflow-x: unset;
	}

	.amsify-selection-area .amsify-selection-list {
		border-color: #ccc !important;
	}

	.align-right {
		text-align: right;
	}

	.jconfirm-box-container {
		margin-left: unset !important;
	}

	.btn_sumisho {
		margin: 2rem 0 0 0;
	}

	@media only screen and (max-width: 991px) {
		.popup_row {
			padding-bottom: 0px !important;
		}
	}

	@media only screen and (min-width: 600px) {
		.amsify-selection-list {
			width: 300px !important;
		}
	}

	@media screen and (min-width: 1024px) {

		/* .amsify-selection-list {
} */
		#myModal.modal {
			z-index: 2100;
		}

		.floatThead-container {
			z-index: 1000 !important;
		}
	}
</style>

<div id="overlay">
	<span class="loader"></span>
</div>

<div class='content  register_container' >

	<div class="row">
		<div class="col-md-12 col-sm-12 heading_line_title">
			<h3><?php echo __('期間管理'); ?></h3>
			<hr>
		</div>
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.UserSuccess")) ? $this->Flash->render("UserSuccess") : ''; ?></div>
			<div class="error" id="error"><?php echo ($this->Session->check("Message.UserError")) ? $this->Flash->render("UserError") : ''; ?></div>
		</div>
		<?php

		if (!empty($this->request->query)) {
			$term_id = h($this->request->query('term_id'));
			$budget_year = h($this->request->query('period_date'));
			$term_qry = h($this->request->query('term'));
			$budget_start_month = h($this->request->query('start_month'));
			$hdMode = h($this->request->query('hdMode'));
		} else {
			$term_id = '';
			$budget_year = '';
			$term_qry = '';
			$budget_start_month = '';
		}
		?>
		<?php echo $this->Form->create(false, array('type' => 'post')); ?>

		<!-- PopUpBox  -->
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content contantbond">
					<div class="modal-header">
						<button type="button" class="close" id="clearData" data-dismiss="modal">&times;</button>
						<h3 class="modal-title"><?php echo __("期間のコピー"); ?></h3>
					</div>
					<div class="modal-body">
						<!-- success,error -->
						<div class="success" id="popupsuccess"></div>
						<div class="error" id="popuperror"></div>
						<!-- end success,error -->
						<div class="table-responsive modal_tbl_wrapper">
							<div class="col-md-12">
								<div class="form-group popup_row">
									<label class="col-md-4 control-label rep_lbl required"><?php echo __("期間名の編集"); ?></label>
									<div class="col-md-8">
										<input class="form-control" type="text" id="period_name" name="period_name" maxlength="50" value="">
										<!-- hidden term_id -->
										<input type="hidden" id="term_id" name="term_id" value="">
									</div>
								</div>
								<?php

								if (!empty($head_department)) { ?>
									<div class="form-group" id="approved_hq">
										<label class="col-md-4 control-label"><?php echo __("レイヤの承認"); ?></label>
										<div class="col-md-8">
											<select multiple="multiple" id="head_dept" name="multi_head_dept[]" class="multiple-select form-control">
												<option value=''>---- <?php echo __("部署選択"); ?> ----</option>
											</select>
										</div>
									</div>
								<?php } ?>
								<div class="form-group col-md-12 col-sm-12 col-xs-12">
									<button type="button" id="term_copy" onclick="clickCopy(<?php echo $term_id; ?>)" class="btn btn-success copy pull-right mt-20"><?php echo __('コピー'); ?> </button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- end popup -->

		<div class="form-group row" style="padding-left: 15px;">
			<div class="col-md-6">
				<label for="from_date" class="col-sm-4 col-form-label required">
					<?php echo __("期間名"); ?>
				</label>
				<div class="col-sm-8">
					<input class="form-control" type="text" id="term_name" name="term_name" value="">
				</div>
			</div>

			<div class="col-md-6">
				<label for="object" class="col-sm-4 col-form-label required">
					<?php echo __("予算期間"); ?>
				</label>
				<div class="col-sm-8">
					<?php
					$term_no = ['1', '2', '3', '4', '5', '6', '7', '8']; ?>
					<select name="term" id="term" class="form-control" value="">
						<option value=''>---- <?php echo __("選択") ?> ----</option>
						<?php
						foreach ($term_no as $term) :
							if (!empty($term_qry)) {
								if ($term_qry == $term) {
									$select = 'selected';
								} else {
									$select = '';
								}
							}
						?>
							<option value="<?= $term ?>" <?php if (!empty($term)) {
																echo $select;
															} ?>>
								<?= h($term) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<div class="form-group row" style="padding-left: 15px;">
			<div class="col-md-6">
				<label for="from_date" class="col-sm-4 col-form-label required">
					<?php echo __("開始年度"); ?>
				</label>
				<div class="col-sm-8">
					<div class="input-group date yearPicker" data-provide="yearPicker" style="padding: 0px;">
						<input type="text" class="form-control" id="period_date" name="period_date" value="<?php if (!empty($budget_year)) {
																												echo $budget_year;
																											} ?>" oninput="return onlynum()" autocomplete="off" style="background-color: #fff;" readonly>
						<span class="input-group-addon" id="disable">
							<span class="glyphicon glyphicon-calendar" id="disable"></span>
						</span>
					</div>
					<input type="text" class="form-control year" id="edit_date" style="display: none;" />
				</div>
			</div>

			<div class="col-md-6">
				<label for="start_month" class="col-sm-4 col-form-label required">
					<?php echo __("予算開始月"); ?>
				</label>
				<div class="col-sm-8">
					<select name="start_month" id="start_month" class="form-control" value="">
						<option value=''>---- <?php echo __("選択") ?> ----</option>
						<?php
						$start_month = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];
						foreach ($start_month as $key => $month) :
							++$key;
							if (!empty($budget_start_month)) {
								if ($budget_start_month == $key) {
									$select = 'selected';
								} else {
									$select = '';
								}
							}

						?>
							<option value="<?= $key ?>" <?php if (!empty($budget_start_month)) {
															echo $select;
														} ?>>
								<?php
								echo __($month); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<div class="form-group row" style="padding-left: 15px;">
			<div class="col-md-6">
				<label for="deadline_date" class="col-md-12 col-form-label required">
					<?php echo __('提出期日'); ?>
				</label>
				<div class="date-wrapper">
					<div class="row">
						<?php foreach ($head_department as $id => $hq_name) : ?>
							<div class="col-md-6">
								<label for="deadline_date" class="col-md-12 col-form-label">
									<?php echo $hq_name; ?>
								</label>
								<div class="col-md-12 mb-20">
									<div class="input-group date datepicker" data-provide="datepicker" style="padding:0px;">
										<input type="text" class="deadline_hq_<?php echo ($id) ?> form-control" id="deadline_date<?php echo ($id); ?>" name="deadline_date[<?php echo ($id) ?>]" value="" autocomplete="off" style="background-color: #fff;" readonly />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
							</div>
						<?php endforeach ?>
					</div>
				</div>

			</div>
			<div class="col-md-6">
				<label for="forecast_period" class="col-sm-4 col-form-label">
					<?php echo __("実績ロック期間"); ?>
				</label>
				<div class="col-sm-8">
					<select name="forecast_period" id="forecast_period" class="form-control" value="">
						<option value=''>---- <?php echo __("選択") ?> ----</option>
					</select>
				</div>
				<div class="form-group row col-lg-12 col-md-12 col-sm-12 d-flex justify-content-end" style="margin-top: 3rem; padding: 0;">

					<input type="button" class="btn-save btn-success btn_sumisho" id="btn_edit" name="btn_edit" value="<?php echo __('変更'); ?>">

					<input type="hidden" name="hid_page_no" id="hid_page_no" class="txtbox" value="">

					<input type="button" class="btn-save btn-success btn_sumisho" id="btn_save" name="btn_save" value="<?php echo __('保存'); ?>">
				</div>
			</div>
		</div>
		<div class="form-group row col-lg-12 col-md-12 col-sm-12 mt-5"></div>
	</div>
	<!-- -------- For Headquarter_deadline_date ---------- -->
	<!-- -->
	<input type="hidden" name="hd_id" id="hd_id" value="<?php if (!empty($term_id)) {
															echo h($term_id);
														} ?>">

	<?php
	echo $this->Form->end();
	?>
	<?php if (!empty($succmsg)) { ?>
		<div id="succc" class="msgfont" style="padding-left: 15px;"> <?php echo ($succmsg); ?></div>
	<?php } elseif (!empty($errmsg)) { ?>
		<div id="err" class="no-data"> <?php echo ($errmsg); ?></div>
	<?php } ?>
	<!-- Table -->
	<?php if (!empty($all_term)) { ?>
		
		<div class="table-container content" style="margin-bottom: 5rem;">
			<table class="table table-bordered" id="term_tbl" style="white-space:unset; word-break: break-all; ">
				<thead>
					<tr>
						<th rowspan="2"><?php echo __("期間名"); ?></th>
						<th rowspan="2"><?php echo __("開始年度"); ?></th>
						<th rowspan="2"><?php echo __("予算終了年"); ?></th>
						<th rowspan="2"><?php echo __("期間"); ?></th>
						<th rowspan="2"><?php echo __("開始月"); ?></th>
						<th rowspan="2"><?php echo __("終了月"); ?></th>
						<th rowspan="2"><?php echo __("実績ロック期間"); ?></th>
						<th colspan="<?php echo (count($head_department)) ?>"><?php echo __("提出期日"); ?></th>
						<th colspan="4" rowspan="2"><?php echo __("アクション"); ?></th>
					</tr>
					<tr>
						<?php foreach ($head_department as $id => $hq_name) : ?>
							<th><?php echo $hq_name; ?></th>
						<?php endforeach ?>
					</tr>
				</thead>

				<?php if (!empty($all_term) && $number != 0) {
					foreach ($all_term as $term) {
						$flag 				= $term['BrmTerm']['flag'];
						$term_id 			= $term['BrmTerm']['id'];
						$budget_year 		= $term['BrmTerm']['budget_year'];
						$budget_end_year 	= $term['BrmTerm']['budget_end_year'];
						$terms 				= $term['BrmTerm']['term'];
						$term_name 			= $term['BrmTerm']['term_name'];
						$start_month 		= $term['BrmTerm']['start_month'];
						$forecast_period 	= $term['BrmTerm']['forecast_period'];
						$d_date 			= $term['BrmTermDeadline'];
						//pr($d_date);
						switch ($start_month) {
							case '1':
								$start = __("1月");
								break;
							case '2':
								$start = __("2月");
								break;
							case '3':
								$start = __("3月");
								break;
							case '4':
								$start = __("4月");
								break;
							case '5':
								$start = __("5月");
								break;
							case '6':
								$start = __("6月");
								break;
							case '7':
								$start = __("7月");
								break;
							case '8':
								$start = __("8月");
								break;
							case '9':
								$start = __("9月");
								break;
							case '10':
								$start = __("10月");
								break;
							case '11':
								$start = __("11月");
								break;
							case '12':
								$start = __("12月");
								break;
							default:
								$start = " ";
								break;
						}
						$end_month = $term['BrmTerm']['end_month'];

						switch ($end_month) {
							case '1':
								$end = __("1月");
								break;
							case '2':
								$end = __("2月");
								break;
							case '3':
								$end = __("3月");
								break;
							case '4':
								$end = __("4月");
								break;
							case '5':
								$end = __("5月");
								break;
							case '6':
								$end = __("6月");
								break;
							case '7':
								$end = __("7月");
								break;
							case '8':
								$end = __("8月");
								break;
							case '9':
								$end = __("9月");
								break;
							case '10':
								$end = __("10月");
								break;
							case '11':
								$end = __("11月");
								break;
							case '12':
								$end = __("12月");
								break;
							default:
								$end = " ";
								break;
						}

						if ($flag != 0) { ?>
							<tbody>
								<tr style="text-align: left;">
									<td><?php echo h($term_name); ?></td>
									<td class="align-right"><?php echo h($budget_year); ?></td>
									<td class="align-right"><?php echo h($budget_end_year); ?></td>
									<td class="align-right">
										<?php
										if ($terms == 1) {
											echo h($terms . ' ' . __("year"));
										} else {
											echo h($terms . ' ' . __("years"));
										}
										?>
									</td>
									<td><?php echo h($start); ?></td>
									<td><?php echo h($end); ?></td>
									<td class="align-right"><?php echo h($forecast_period); ?></td>
									<?php foreach ($head_department as $id => $hq_name) : ?>
										<?php
										$deadline = $d_date[$id - 1]['deadline_date'];
										$deadline_date = (empty($deadline) || $deadline == null || $deadline == '0000-00-00 00:00:00') ? '' : date("Y/m/d", strtotime($deadline));
										?>
										<td class="align-right"><?php echo ($deadline_date); ?></td>
									<?php endforeach ?>
									<?php $dis_style = ($flag == '3') ? "cursor: not-allowed;pointer-events: none;opacity: 0.5;" : ''; ?>
									<td width="100px" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
										<a class="" href="#" onclick="clickEdit(<?php echo $term_id; ?>)" style='<?php echo $dis_style; ?>' title='<?php echo __("編集"); ?>'><i class="fa-regular fa-pen-to-square" ></i>
										</a>
									</td>
									<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
										<a class="delete_link" href="#" onclick="clickDelete(<?php echo $term_id; ?>)" style='<?php echo $dis_style; ?>' title='<?php echo __("削除"); ?>'><i class="fa-regular fa-trash-can" ></i></a>
									</td>
									<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
										<a class=" <?php echo $disabled; ?>" data-target="#myModal" data-toggle="modal" data-backdrop="static" data-keyboard="false" href="#" onclick="popupscreen(<?php echo $term_id; ?>)" style='<?php echo $dis_style; ?>' title='<?php echo __("コピー"); ?>'><i class="fa-regular fa-copy" ></i></a>
									</td>
									<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
										<?php if ($flag == '1') : ?>
											<a class="act-color act_color" id="active_status" href="#" onclick="Click_Inactive('<?php echo $term_id; ?>');" title='<?php echo __("ACTIVE"); ?>'><i class="fa-regular fa-circle-check" ></i></a>
										<?php elseif ($flag == '3') : ?>
											<a class="inact-color" id="Inactive_status" href="#" onclick="Click_Active('<?php echo $term_id; ?>');" title='<?php echo __("INACTIVE"); ?>'><i class="fa-regular fa-circle-xmark" ></i></a>
										<?php endif; ?>
									</td>
								</tr>
							</tbody>
				<?php }
					}
				} ?>
			</table>
		</div>
			
	<?php } ?>
	<!-- Table End -->

	<div class="row" style="clear:both;margin: 40px 0px;">
		<?php if ($count > 50) { ?>
			<div class="col-sm-12" style="padding: 10px;text-align: center;">
				<div class="paging">
					<?php
					echo $this->Paginator->first('<<');
					echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev disabled'));
					echo $this->Paginator->numbers(array('separator' => '', 'modulus' => 6));
					echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
					echo $this->Paginator->last('>>');
					?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>


</div>
<script>
	$(document).ready(function() {

		// To disable period_date for edition condition
		$('#edit_date').hide();

		<?php if ($hdMode == 'Update') { ?>
			$('#btn_save').hide();
			$('#btn_edit').show();
		<?php } else {  ?>
			$('#btn_save').show();
			$('#btn_edit').hide();
		<?php } ?>

		/* float thead */
		// if($('#term_tbl').length > 0) {
		// 	var $table = $('#term_tbl');
		// 	$table.floatThead({
		// 		position: 'absolute'
		// 	});
		// }

		// if($(".tbl-wrapper").length) {
		// 	$(".tbl-wrapper").floatingScroll();
		// }
		if ($('#term_tbl').length > 0) {
			var $table = $('#term_tbl');
			$table.floatThead({
				responsiveContainer: function($table) {
					return $table.closest('.table-responsive');
				}
			});
		}
		/* end*/
		$("#clearData").click(function() {
			$('.amsify-select-clear').click();
			$('#popuperror').hide();
		});

		$(".amsify-select-clear").click(function(e) {
			e.preventDefault();
		});

		$('.datepicker').datepicker({
			format: 'yyyy/mm/dd',
			autoclose: true
		});
		$('.datepicker').click(function() {
			$('.datepicker').css("z-index", '2001 !important');
			$('.yearPicker').css("z-index", '1999 !important');
			$('.dropdown-menu').css("z-index", '1001 !important');
		});
		$('.yearPicker').click(function() {
			$('.yearPicker').css("z-index", '2001 !important');
			$('.datepicker').css("z-index", '1999 !important');
		});

		$('#start_month, .yearPicker').on('focusout', function() {

			var budget_year = $("#period_date").val();
			var start_month = $("#start_month").val();

			if (budget_year != "" && start_month != "") {

				var html;
				var forecast_period;

				var start_month = parseInt(start_month);
				var budget_year = parseInt(budget_year);

				html = "<option value=''>---- <?php echo __("選択") ?> ----</option>";

				for (var i = 1; i <= 12; i++) {

					if (start_month > 12) {

						start_month = 1;
						budget_year = budget_year + 1;
					}

					var formattedNumber = ("0" + start_month).slice(-2);
					forecast_period = budget_year + "/" + formattedNumber;


					html += "<option style='font-size: 14px;' value='" + budget_year + "-" + formattedNumber + "'>" + forecast_period + "</option>";

					start_month++;

				}

			} else html = "<option value=''>---- <?php echo __("選択") ?> ----</option>";

			$("#forecast_period").html(html);

		});

		/* when choose field without choosing term and year, to show error message */
		$("#forecast_period").on('focus', function() {
			$("#error").empty();
			$("#success").empty();

			var budget_year = $("#period_date").val();
			var start_month = $("#start_month").val();

			chk = true;


			if (!checkNullOrBlank(budget_year)) {
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("開始年度") ?>'])));
				document.getElementById("error").appendChild(a);
				chk = false;
			}

			if (!checkNullOrBlank(start_month)) {
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("予算開始月") ?>'])));
				document.getElementById("error").appendChild(a);
				chk = false;
			}

			if (chk) {

				getForecastPeriod(budget_year, start_month);
			}

		});


		$("#btn_save").click(function() {

			document.getElementById("error").innerHTML = "";
			document.getElementById("success").innerHTML = "";

			var budget_year = document.getElementById("period_date").value;

			var term = document.getElementById("term").value;

			var term_name = document.getElementById("term_name").value;

			var start_month = document.getElementById("start_month").value;

			// var deadline_date = document.getElementById("deadline_date").value;

			var forecast_period = document.getElementById("forecast_period").value;
			var chk = true;
			if (!checkNullOrBlank(term_name)) {
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("期間名") ?>'])));
				document.getElementById("error").appendChild(a);
				chk = false;
			}
			if (!checkNullOrBlank(budget_year)) {
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("開始年度") ?>'])));
				document.getElementById("error").appendChild(a);
				chk = false;
			} else {

				if (!/^[1-9]{1}[0-9]{3}$/.test(budget_year)) {

					var newbr = document.createElement("div");
					var a = document.getElementById("error").appendChild(newbr);
					a.appendChild(document.createTextNode(errMsg(commonMsg.JSE044, ['<?php echo __("開始年度") ?>'])));
					document.getElementById("error").appendChild(a);
					chk = false;

				}
			}

			if (!checkNullOrBlank(term)) {
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("期間") ?>'])));
				document.getElementById("error").appendChild(a);
				chk = false;
			}

			if (!checkNullOrBlank(start_month)) {
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("予算開始月") ?>'])));
				document.getElementById("error").appendChild(a);
				chk = false;
			}

			// if(!checkNullOrBlank(deadline_date)) {
			if (!deadlineValidation()) { //edit by khinhninmyo(02.04.2021/Fri)											
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("提出期日") ?>'])));
				document.getElementById("error").appendChild(a);
				chk = false;
			}

			var path = window.location.pathname;
			var page = path.split("/").pop();
			if (page.indexOf("page:") !== -1) {
				document.getElementById('hid_page_no').value = page;
			}

			if (chk) {
				$.confirm({
					title: '<?php echo __("保存確認"); ?>',
					icon: 'fas fa-exclamation-circle',
					type: 'blue',
					typeAnimated: true,
					closeIcon: true,
					columnClass: 'medium',
					animateFromElement: true,
					animation: 'top',
					draggable: false,
					content: "<?php echo __("データを保存してよろしいですか。"); ?>",
					buttons: {
						ok: {
							text: '<?php echo __("はい"); ?>',
							btnClass: 'btn-info',
							action: function() {
								loadingPic();
								document.forms[0].action = "<?php echo $this->webroot; ?>BrmTerms/saveTerm";
								document.forms[0].method = "POST";
								document.forms[0].submit();

								return true;
							}
						},
						cancel: {
							text: '<?php echo __("いいえ"); ?>',
							btnClass: 'btn-default',
							cancel: function() {
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

			scrollText();

		});

		$("#btn_edit").click(function() {

			document.getElementById("error").innerHTML = "";
			document.getElementById("success").innerHTML = "";

			var budget_year = document.getElementById("period_date").value;

			var term = document.getElementById("term").value;

			var term_name = document.getElementById("term_name").value;

			var start_month = document.getElementById("start_month").value;

			var hqid_str = '<?php echo implode(',', array_keys($head_department)); ?>';
			var hqid_cnt = '<?php echo count($head_department); ?>';
			var hqid_arr = hqid_str.split(',');

			var arr = [];
			for (var i = 0; i < hqid_cnt; i++) {
				var deadline_date = document.getElementById("deadline_date" + hqid_arr[i]).value;
				if (deadline_date != '') {
					arr.push(deadline_date);
				}
			}

			var chk = true;
			if (!checkNullOrBlank(term_name)) {
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("期間名") ?>'])));
				document.getElementById("error").appendChild(a);
				chk = false;
			}
			// if(!checkNullOrBlank(deadline_date)) {
			if (arr.length != hqid_cnt) { //edit by khinhninmyo(02.04.2021/Fri)											
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("提出期日") ?>'])));
				document.getElementById("error").appendChild(a);
				chk = false;
			}

			var path = window.location.pathname;
			var page = path.split("/").pop();
			if (page.indexOf("page:") !== -1) {
				document.getElementById('hid_page_no').value = page;
			}

			if (chk) {
				$.confirm({
					title: '<?php echo __("変更確認"); ?>',
					icon: 'fas fa-exclamation-circle',
					type: 'blue',
					typeAnimated: true,
					closeIcon: true,
					columnClass: 'medium',
					animateFromElement: true,
					animation: 'top',
					draggable: false,
					content: "<?php echo __("データを変更してよろしいですか。"); ?>",
					buttons: {
						ok: {
							text: '<?php echo __("はい"); ?>',
							btnClass: 'btn-info',
							action: function() {
								loadingPic();
								document.forms[0].action = "<?php echo $this->webroot; ?>BrmTerms/updateTerm";
								document.forms[0].method = "POST";
								document.forms[0].submit();

								return true;
							}
						},
						cancel: {
							text: '<?php echo __("いいえ"); ?>',
							btnClass: 'btn-default',
							cancel: function() {
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

			scrollText();

		});

	});

	function deadlineValidation() {
		var hqid_str = '<?php echo implode(',', array_keys($head_department)); ?>';
		var hqid_cnt = '<?php echo count($head_department); ?>';
		var hqid_arr = hqid_str.split(',');

		var arr = [];
		for (var i = 0; i < hqid_cnt; i++) {
			var deadline_date = document.getElementById("deadline_date" + hqid_arr[i]).value;
			if (deadline_date != '') {
				arr.push(deadline_date);
			}
		}
		if (arr.length == hqid_cnt) {
			return true;
		} else {
			return false;
		}

	}

	function popupscreen(id) {
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>BrmTerms/GetPopupValue",
			data: {
				id: id
			},
			dataType: 'json',
			success: function(data) {

				var term_id = data['term_id'];
				var term_name = data['term_name'];
				var approved_hq = data['approved_hq'];

				if (approved_hq.length == 0) {
					$('#approved_hq').hide();
				} else {
					var table_html = '';
					$.each(approved_hq, function(key, value) {
						hq_id = value['hq_id'];
						hq_name = value['hq_name'];

						table_html += '<option value="' + hq_id + '">' + hq_name + '</option>';
					});
					$("#head_dept").append(table_html);
					//multi select box plugin
					$('#head_dept').amsifySelect();
					$('#approved_hq').show();
				}

				$('#term_id').val(term_id);
				$('#period_name').val(term_name);


			}
		});


	}

	function clickEdit(id) {

		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';
		$('#btn_save').hide();
		$('#btn_edit').show();

		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>BrmTerms/editTerm",
			data: {
				id: id
			},
			dataType: 'json',
			beforeSend: function() {
				loadingPic();
			},
			success: function(data) {
				console.log(data);

				var term_id = data['id'];
				var budget_year = data['budget_year'];
				var term = data['term'];
				var term_name = data['term_name'];
				document.cookie = "TERMNAME = " + term_name + " ";
				var budget_start_month = data['start_month'];
				var forecast_period = data['forecast_period'];
				var deadline_dates = data['hq_deadlinedata'];


				$("#term_id").val(term_id);
				$(".yearPicker").hide();
				$("#edit_date").show();
				$('#period_date').val(budget_year);
				$('#edit_date').val(budget_year);
				$("#edit_date").prop("readonly", true);
				//document.getElementById("period_date").style.width = "400px";
				$('#term').val(term);
				$('#term_name').val(term_name);
				$('#start_month').val(budget_start_month);
				getForecastPeriod(budget_year, budget_start_month, forecast_period);

				let num = 1;
				$.each(deadline_dates, function(key, value) {
					$(".deadline_hq_" + num).val(value);
					num++;
				})
				$('#overlay').hide();

			}
		});


	}

	function getForecastPeriod(budget_year, start_month, forecast_period) {

		var html;
		var forecast_period;

		var budget_year = parseInt(budget_year);
		var start_month = parseInt(start_month);

		html = "<option value=''>---- <?php echo __("選択") ?> ----</option>";

		for (var i = 1; i <= 12; i++) {

			if (start_month > 12) {

				start_month = 1;
				budget_year = budget_year + 1;
			}

			var formatted_start_month = ("0" + start_month).slice(-2);
			f_period = budget_year + "/" + formatted_start_month;
			forecast_val = budget_year + "-" + formatted_start_month;

			if (forecast_period == forecast_val) {

				html += "<option style='font-size: 14px;' value='" + forecast_val + "' selected>" + f_period + "</option>";
			} else html += "<option style='font-size: 14px;' value='" + forecast_val + "'>" + f_period + "</option>";


			start_month++;

		}

		$("#forecast_period").html(html);

	}

	function clickDelete(id) {

		document.getElementById("error").innerHTML = "";
		document.getElementById("success").innerHTML = "";

		document.getElementById("term_id").value = id;

		var path = window.location.pathname;
		var page = path.split("/").pop();
		if (page.indexOf("page:") !== -1) {

			document.getElementById('hid_page_no').value = page;
		}

		$.confirm({
			title: '<?php echo __("削除確認"); ?>',
			icon: 'fas fa-exclamation-circle',
			type: 'red',
			typeAnimated: true,
			closeIcon: true,
			columnClass: 'medium',
			animateFromElement: true,
			animation: 'top',
			draggable: false,
			content: errMsg(commonMsg.JSE017),
			buttons: {
				ok: {
					text: '<?php echo __("はい"); ?>',
					btnClass: 'btn-info',
					action: function() {
						loadingPic();
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmTerms/deleteTerm";
						document.forms[0].method = "POST";
						document.forms[0].submit();

						return true;
					}
				},
				cancel: {
					text: '<?php echo __("いいえ"); ?>',
					btnClass: 'btn-default',
					cancel: function() {
						console.log('the user clicked cancel');
						scrollText();
					}

				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		});

		scrollText();

	}

	function clickCopy(term_id) {

		$('#popuperror').show();
		document.getElementById("popupsuccess").innerHTML = "";
		document.getElementById("popuperror").innerHTML = "";
		var chk = true;
		var period_name = document.getElementById("period_name").value;

		if (!checkNullOrBlank(period_name)) {
			var newbr = document.createElement("div");
			var a = document.getElementById("popuperror").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("期間名") ?>'])));
			document.getElementById("popuperror").appendChild(a);
			chk = false;
		}

		if (chk) {
			$("#popuperror").css("display", "none");
			$.confirm({
				title: '<?php echo __("コピー確認"); ?>',
				icon: 'fas fa-exclamation-circle',
				type: 'blue',
				typeAnimated: true,
				closeIcon: true,
				columnClass: 'medium',
				animateFromElement: true,
				animation: 'top',
				draggable: false,
				content: "<?php echo __("データをコピーしてよろしいですか。"); ?>",
				buttons: {
					ok: {
						text: '<?php echo __("はい"); ?>',
						btnClass: 'btn-info',
						action: function() {
							loadingPic();
							document.forms[0].action = "<?php echo $this->webroot; ?>BrmTerms/CopyAndClone";
							document.forms[0].method = "POST";
							document.forms[0].submit();

							return true;
						}
					},
					cancel: {
						text: '<?php echo __("いいえ"); ?>',
						btnClass: 'btn-default',
						cancel: function() {
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
		scrollText();
	}

	function Click_Active(term_id) {

		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';
		document.getElementById("term_id").value = term_id;

		var path = window.location.pathname;
		var page = path.split("/").pop();
		if (page.indexOf("page:") !== -1) {
			document.getElementById('hid_page_no').value = page;
		}

		$.confirm({
			title: '<?php echo __("アクティブの確認"); ?>',
			icon: 'fas fa-exclamation-circle',
			type: 'blue',
			typeAnimated: true,
			closeIcon: true,
			columnClass: 'medium',
			animateFromElement: true,
			animation: 'top',
			draggable: false,
			content: "<?php echo __("この期間をアクティブにしてもよろしいですか？"); ?>",
			buttons: {
				ok: {
					text: '<?php echo __("はい"); ?>',
					btnClass: 'btn-info',
					action: function() {
						loadingPic();
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmTerms/activeStatus";
						document.forms[0].method = "POST";
						document.forms[0].submit();
						return true;
					}
				},
				cancel: {
					text: '<?php echo __("いいえ"); ?>',
					btnClass: 'btn-default',
					action: function() {
						/* refresh load page*/
						/*document.forms[0].submit();							
						return false;*/
					}
				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		});
		scrollText();
	}

	function Click_Inactive(term_id) {

		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';
		document.getElementById("term_id").value = term_id;

		var path = window.location.pathname;
		var page = path.split("/").pop();
		if (page.indexOf("page:") !== -1) {
			document.getElementById('hid_page_no').value = page;
		}

		$.confirm({
			title: '<?php echo __("非アクティブの確認"); ?>',
			icon: 'fas fa-exclamation-circle',
			type: 'blue',
			typeAnimated: true,
			closeIcon: true,
			columnClass: 'medium',
			animateFromElement: true,
			animation: 'top',
			draggable: false,
			content: "<?php echo __("この期間を非アクティブにしますか？"); ?>",
			buttons: {
				ok: {
					text: '<?php echo __("はい"); ?>',
					btnClass: 'btn-info',
					action: function() {
						loadingPic();
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmTerms/inactiveStatus";
						document.forms[0].method = "POST";
						document.forms[0].submit();
						return true;
					}
				},
				cancel: {
					text: '<?php echo __("いいえ"); ?>',
					btnClass: 'btn-default',
					action: function() {
						/* refresh load page*/
						/*document.forms[0].submit();							
						return false;*/
					}
				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		});
		scrollText();
	}

	$(function() {
		$("input[name='period_date']").on('input', function(e) {
			$(this).val($(this).val().replace(/[^0-9]/g, ''));
		});
	});

	function scrollText() {

		var tes = $('#error').text();
		var tes1 = $('.success').text();
		if (tes) {
			$("html, body").animate({
				scrollTop: 0
			}, "slow");
		}
		if (tes1) {
			$("html, body").animate({
				scrollTop: 0
			}, "slow");
		}
	}

	function onlynum() {

		var budget_year = document.getElementById("period_date");
		var res = budget_year.value;

		if (res != '') {
			if (isNaN(res)) {
				document.getElementById("error").innerHTML = errMsg(commonMsg.JSE007, ['<?php echo __("開始年度") ?>']);
				document.getElementById("success").innerHTML = "";

				budget_year.value = "";

				return false;
			} else {
				document.getElementById('error').innerHTML = "";
				return true;
			}
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