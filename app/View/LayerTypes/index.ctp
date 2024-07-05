<?php
echo $this->Form->create(false, array('type' => 'post', 'class' => 'form-inline', 'id' => 'LayerTypes', 'name' => 'LayerTypes', 'enctype' => 'multipart/form-data'));
?>
<style>
	.yearPicker {
		z-index: 1999 !important;
	}

	.numbers {
		text-align: right !important;
	}

	option:disabled {
		background-color: #ddd;
	}

	.disable-links {
		pointer-events: none;
		/* background:#cccccc; */
		color: #cccccc
	}

	.orderSort:hover {
		cursor: move;
	}

	.jconfirm-box-container {
		margin-left: unset !important;
	}

	.register_form .btn-save-wpr {
		width: 70%;
	}
	.row-count {
		width: 100%;
		font-family: var(--font_family);
		font-weight: bold;
		color: green;
	}

	@media and (max-width: 576px) {
		.register_form .btn-save-wpr {
			width: 50%;
		}
	}
</style>
<script>
	var data_arr = [];
	$(document).ready(function() {

		// Start Order Sorting in Model Box - YarZarLinAung(17/06/2022)
		var data_list = <?php echo json_encode($list); ?>;
		var orderMap = {}
		$(".layer_setup tbody").sortable({
			items: 'tr.orderSort',
			helper: function(e, tr) {
				var $originals = tr.children();
				var $helper = tr.clone();
				$helper.children().each(function(index) {
					$(this).width($originals.eq(index).width())
					$(this).css({
						"background-color": "#e5ffff"
					});
				});
				return $helper;
			},
			distance: 5,
			delay: 100,
			opacity: 0.9,
			cursor: 'move',
			update: function(e, ui) {
				$('td.index', ui.item.parent()).each(function(i) {
					$(this).html(i + 1);
				});
				$('td.pri-id .primary-id', ui.item.parent()).each(function(i) {
					var priKey = $(this).val();
					orderMap[priKey] = i + 1;
				});
			}
		});
		// End Order Sorting in Model Box - YarZarLinAung(17/06/2022)

		/* float table header */
		$("input:radio[value='2']").prop('checked', true);
		if ($("#tbl_field").length > 0) {
			var $table = $("#tbl_field");
			$table.floatThead({
				position: "absolute"
			});
		}


		//hide show detail
		// $(".hide_showDetail").hide();

		$("#btn_save").click(function() {
			/* clear error or success message */
			$("#error").empty("");
			$("#success").empty("");

			/* get field values */
			let name_jp = $("#name_jp").val().trim();
			let name_en = $("#name_en").val().trim();
			let type_order = $("#type_order").val();

			let chk = true;
			if (!checkNullOrBlank(name_jp)) {
				$("#name_jp").val(name_jp);
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, ['<?php echo __("部署種類名 (JP)"); ?>']) + "</div>");
				chk = false;
			} else if (checkSpecialChar(name_jp)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE019, ['<?php echo __("部署種類名 (JP)"); ?>']) + "</div>");
				chk = false;
			}
			if (checkSpecialChar(name_en)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE019, ['<?php echo __("部署種類名 (ENG)"); ?>']) + "</div>");
				chk = false;
			}
			// if(order_list != 0){
			// 	if(!checkNullOrBlank(type_order)) {					
			// 		$("#error").append("<div>"+errMsg(commonMsg.JSE002,['<?php echo __("親部署"); ?>'])+"</div>");
			// 		chk = false;											
			// 	}
			// }


			if (chk) {
				$.confirm({
					title: "<?php echo __("保存確認"); ?>",
					icon: "fas fa-exclamation-circle",
					type: "green",
					typeAnimated: true,
					closeIcon: true,
					columnClass: "medium",
					animateFromElement: true,
					animation: "top",
					draggable: false,
					content: "<?php echo __("データを保存してよろしいですか。"); ?>",
					buttons: {
						ok: {
							text: "<?php echo __("はい"); ?>",
							btnClass: "btn-info",
							action: function() {
								loadingPic();
								document.forms[0].action = "<?php echo $this->webroot; ?>LayerTypes/saveLayerType";
								document.forms[0].method = "POST";
								document.forms[0].submit();
								scrollText();
								return true;
							}
						},
						cancel: {
							text: "<?php echo __("いいえ"); ?>",
							btnClass: "btn-default",
							cancel: function() {
								scrollText();
							}
						}
					},
					theme: "material",
					animation: "rotateYR",
					closeAnimation: "rotateXR"
				});
			}
			scrollText();
		});

		$("#btn_update").click(function() {
			$("#error").empty();
			$("#success").empty();

			let name_jp = $("#name_jp").val().trim();
			let name_en = $("#name_en").val().trim();
			let type_order = $("#type_order").val();
			let path = window.location.pathname;
			let page = path.split("/").pop();
			$("#hid_page_no").value = page;
			let chk = true;
			if (!checkNullOrBlank(name_jp)) {
				$("#name_jp").val(name_jp);
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, ['<?php echo __("部署名（JP）"); ?>']) + "</div>");
				chk = false;
			} else if (checkSpecialChar(name_jp)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE019, ['<?php echo __("部署名（JP）"); ?>']) + "</div>");
				chk = false;
			}
			if (!checkNullOrBlank(name_en)) {
				$("#name_en").val(name_en);
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, ['<?php echo __("部署名（ENG）"); ?>']) + "</div>");
				chk = false;
			} else if (checkSpecialChar(name_en)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE019, ['<?php echo __("部署名（ENG）"); ?>']) + "</div>");
				chk = false;
			}

			if (chk) {
				$.confirm({
					title: '<?php echo __("変更確認"); ?>',
					icon: 'fas fa-exclamation-circle',
					type: 'green',
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
								document.forms[0].action = "<?php echo $this->webroot; ?>LayerTypes/updateLayerType";
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
					closeAnimation: 'rotateXR',

				});
			}
			scrollText();
		});

		/* Edit set account pop up link*/
		$("#editAccountButton").click(function(e) {
			data_list.forEach(element => element.LayerType.type_order = orderMap[element.LayerType.id]);
			data_arr.push(JSON.stringify(data_list));

			$.confirm({
				title: "<?php echo __('保存確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'green',
				typeAnimated: true,
				animateFromElement: true,
				animation: 'top',
				draggable: false,
				closeIcon: true,
				content: "<?php echo __('データを保存してよろしいですか。'); ?>",
				buttons: {
					ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
						action: function() {
							$.ajax({
								type: "POST",
								url: "<?php echo $this->webroot; ?>LayerTypes/EditLayerSetup",
								data: {
									'data_arr': data_arr
								},
								dataType: 'json',
								success: function(data) {
									$("#adjust_order_popup").hide();
									scrollText();
									window.location.reload();

								},
								error: function(e) {
									alert('Something wrong! Please refresh the page.');
								}
							});

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


		});

	});

	function clickEdit(id) {
		$("#error").empty();
		$("#success").empty();

		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>LayerTypes/getEditData",
			data: {
				id: id
			},
			dataType: "json",
			beforeSend: function() {
				loadingPic();
			},
			success: function(data) {

				let layer_code = data["id"];
				let name_jp = data["name_jp"];
				let name_en = data["name_en"];
				let type_order = data["type_order"];
				let show_detail = data["show_detail"];

				/* set value according to layer id */
				$("#hid_update_id").val(layer_code);
				$("#name_jp").val(name_jp);
				$("#name_en").val(name_en);
				$("#type_order").val(type_order);
				$("#hid_type_order").val(type_order);
				if (show_detail == "1") $("input:radio[value='1']").prop('checked', true);
				else $("input:radio[value='2']").prop('checked', true);
				//$('#type_order').prop("disabled", true);
				/* layer order enable, disable */
				$("#type_order > option").each(function() {
					// alert("Outher"+this.value);
					if (this.value == type_order) {
						$(this).removeAttr("disabled");
					} else {

					}
				});

				/* button show or hide for update and save */
				$('#overlay').hide();
				$("#btn_save").hide();
				$("#btn_update").show();
			}
		});
	}

	function clickDelete(id) {
		$("#error").empty();
		$("#success").empty();

		$("#hid_delete_id").val(id);
		let path = window.location.pathname;
		let page = path.split("/").pop();
		$("#hid_page_no").val(page);

		$.confirm({
			title: "<?php echo __("削除確認"); ?>",
			icon: "fas fa-exclamation-circle",
			type: "red",
			typeAnimated: true,
			closeIcon: true,
			columnClass: "medium",
			animateFromElement: true,
			animation: "top",
			draggable: false,
			content: errMsg(commonMsg.JSE017),
			buttons: {
				ok: {
					text: "<?php echo __("はい"); ?>",
					btnClass: "btn-info",
					action: function() {
						loadingPic();
						document.forms[0].action = "<?php echo $this->webroot; ?>LayerTypes/removeLayerType";
						document.forms[0].method = "POST";
						document.forms[0].submit();
						return true;
					}
				},
				cancel: {
					text: "<?php echo __("いいえ"); ?>",
					btnClass: "btn-default",
					cancel: function() {
						console.log("User clicked CALCEL...");
						scrollText();
					}
				}
			},
			theme: "material",
			animation: "rotateYR",
			closeAnimation: "rotateXR"
		});
		scrollText();
	}

	/*  
	 * show hide loading overlay
	 *@Zeyar Min  
	 */
	function loadingPic() {
		$("#overlay").show();
		$('.jconfirm').hide();
	}

	function scrollText() {
		var tes = $('#error').text();
		var tes1 = $('.success').text();
		if (tes) {
			$("html, body").animate({
				scrollTop: 30
			}, "fast");
		}
		if (tes1) {
			$("html, body").animate({
				scrollTop: 30
			}, "fast");
		}
	}
</script>
<div id="overlay">
	<span class="loader"></span>
</div>
<div class="content register_container">
	<div class="register_form">
		<div class="row">
			<fieldset>
				<div class="col-md-12 col-sm-12 heading_line_title">
					<legend><?php echo __('部署種類名の管理'); ?></legend>
				</div>
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="success" id="success"><?php echo ($this->Session->check("Message.SuccessMsg")) ? $this->Flash->render("SuccessMsg") : ''; ?><?php echo ($this->Session->check("Message.LayerTypesSuccess")) ? $this->Flash->render("LayerTypesSuccess") : ''; ?></div>
					<div class="error" id="error"><?php echo ($this->Session->check("Message.ErrorMsg")) ? $this->Flash->render("ErrorMsg") : ''; ?><?php echo ($this->Session->check("Message.LayerTypesFail")) ? $this->Flash->render("LayerTypesFail") : ''; ?></div>
				</div>
				<div class="form-row">
					<!-- hidden field for delete -->
					<input type="hidden" name="hid_delete_id" id="hid_delete_id">
					<!-- hidden field for update -->
					<input type="hidden" name="hid_update_id" id="hid_update_id">
					<!-- for selected text(term_name) -->
					<!-- <input type="hidden" name=selected_data id="selected_data"> -->
					<!-- hidden field for page no. -->
					<input type="hidden" name="hid_page_no" id="hid_page_no">
					<input type="hidden" name="hid_type_order" id="hid_type_order">
					<input type="hidden" class="form-control" id="type_order" name="type_order" value="" />

					<div class="form-group col-sm-12 col-md-6">
						<label for="name_jp" class="col-sm-6 col-md-6 control-label required">
							<?php echo __("部署種類名 (JP)"); ?>
						</label>
						<input type="text" class="col-sm-6 col-md-6 form-control form_input" id="name_jp" name="name_jp" value="" maxlength="500" />
					</div>
					<div class="form-group col-sm-12 col-md-6">
						<label for="name_en" class="col-sm-6 col-md-6 control-label">
							<?php echo __("部署種類名 (ENG)"); ?>
						</label>
						<input type="text" class="col-sm-6 col-md-6 form-control form_input" id="name_en" name="name_en" value="" maxlength="500" />
					</div>

					<div class="form-group col-sm-12 col-md-6"></div>
					<!-- <div class="form-group col-sm-12 col-md-6 hide_showDetail">
						<label for="name_jp" class="col-sm-6 col-md-6 control-label required">
							<?php echo __("最終部署"); ?>
						</label>
						<label class="col-sm-6 col-md-6" for="show_detail_1">
							<input type="radio" name="show_detail_radio" id="show_detail_1" class="radio" value="1">&nbsp;<?php echo __('はい'); ?>
						</label>
						<label class="col-sm-6 col-md-6" for="show_detail_2">
							<input type="radio" name="show_detail_radio" id="show_detail_2" class="radio" value="2">&nbsp;<?php echo __('いいえ'); ?>
						</label>
					</div> -->
					<div class="form-group col-sm-12 col-md-6" style="margin-bottom: 40px;">
						<div class="submit btn-save-wpr d-flex justify-content-end">
							<input type="button" class="btn-save " id="btn_save" name="btn_save" value="<?php echo __('保存'); ?>">
							<input type="button" class="btn-save " id="btn_update" name="btn_save" style="display: none;" value="<?php echo __('変更'); ?>">
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-6 col-sm-6 col-xs-6 d-flex align-items-end">
			<?php if (!empty($succmsg)) { ?>
				<div class="row-count" id="succc" style="margin-bottom: 0.3rem;"><?php echo ($succmsg); ?></div>
			<?php } elseif (!empty($errmsg)) { ?>
				<div id="err" class="no-data"> <?php echo ($errmsg); ?></div>
			<?php } ?>

		</div>
		<div class="col-lg-6 col-sm-6 col-xs-6">
				<a href="#" style="float:right;border: 1px solid #3947db;padding: 8px;text-decoration: none;" class="glyphicon glyphicon-sort-by-attributes-alt" data-toggle="modal" data-target="#adjust_order_popup"><?php echo __('順番調整'); ?></a>
		</div>
	</div>
	<?php if ($rowCount != 0) { ?>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">
				<div class="table-responsive tbl-wrapperd">
					<table class="table table-striped table-bordered acc_review" id="tbl_field" style="margin-top:10px;width: 100%;">
						<thead class="check_period_table">
							<tr>
								<th><?php echo __("#"); ?></th>
								<th><?php echo __("部署種類名 (JP)"); ?></th>
								<th><?php echo __("部署種類名 (ENG)"); ?></th>
								<th><?php echo __("部署種類順番"); ?></th>
								<!-- <th><?php echo __("最終部署"); ?></th> -->
								<th colspan="3"><?php echo __("Action"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($list)) :
								$no = ($page - 1) * $limit + 1;
								foreach ($list as $datas) :
									$id = $datas['LayerType']['id'];
									$latest_id = $datas['LayerType']['id'];
									$name_jp = $datas['LayerType']['name_jp'];
									$name_en = $datas['LayerType']['name_en'];
									$type_order = $datas['LayerType']['type_order'];
									// if ($datas['LayerType']['show_detail']) {
									// 	$show_detail = 'Yes';
									// } else {
									// 	$show_detail = 'No';
									// }
									$removeDisable = 0;

									if (!empty($datas['Layer'])) {
										foreach ($datas['Layer'] as $val) {
											if ($val['flag'] == 1) {
												$removeDisable++;
											}
										}
									}

									$dis_style = $removeDisable == 0 ? "" : "cursor: not-allowed;pointer-events: none;opacity: 0.5;";

							?>


									<tr style="text-align: left;">
										<td><?php echo  $no; ?></td>
										<td><?php echo $name_jp; ?></td>
										<td><?php echo $name_en; ?></td>
										<td><?php echo $type_order; ?></td>
										<!-- <td><?php echo $show_detail; ?></td> -->
										<td style="word-break: break-all;text-align: center;width: fit-content; vertical-align:middle;font-size:1.3em !important; " class="link" style="word-break: break-all;text-align:center;vertical-align:middle;">
											<a class="edit_link" id="edit_link" href="#" onclick="clickEdit('<?php echo $id; ?>');" title="<?php echo __('編集'); ?>"><i class="fa-regular fa-pen-to-square"></i>
											</a>
										</td>

										<td style="word-break: break-all;text-align: center;width: fit-content; vertical-align:middle;font-size:1.3em !important; " class="link" style="word-break: break-all;text-align:center;vertical-align:middle;">

											<a class="delete_link" href="#" style='<?php echo $dis_style; ?>' <?php if ($removeDisable === 0) { ?> onclick="clickDelete('<?php echo $latest_id; ?>');" <?php } ?> title="<?php echo __('削除'); ?>"><i class="fa-regular fa-trash-can"></i>
											</a>

										</td>

									</tr>

							<?php $no++;
								endforeach;
							endif; ?>

						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php } ?>
	<?php if (!empty($datas)) { ?>
		<div class="col-md-12" style="padding: 10px;text-align: center;margin-bottom: 50px;">
			<div class="paging">
				<?php
				if ($rowCount > $limit) {
					echo $this->Paginator->first('<<');
					echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev disabled'));
					echo $this->Paginator->numbers(array('separator' => '', 'modulus' => 6));
					echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
					echo $this->Paginator->last('>>');
				}
				?>
			</div>
		</div>
	<?php } ?>
</div>
</div>
<!-- Layer setupPopUpBox  -->
<!-- Modal -->
<div class="modal fade" id="adjust_order_popup" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="col-sm-11 modal-title" id="exampleModalLabel"><?php echo __("部署種類の表示順番調整"); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" style="max-height: 500px; overflow-y: auto;">
				<div class="modal_tbl_wrapper">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="success" id="succ"></div>
						<div class="error" id="err" method="PopUp"></div>
					</div>
					<!-- Start Order Sorting Model Box - YarZarLinAung(17/06/2022) -->
					<table class="table table-bordered layer_setup">
						<thead class="check_period_table">
							<tr>
								<th><?php echo __("#"); ?></th>
								<th><?php echo __("部署種類名 (JP)"); ?></th>
								<th><?php echo __("部署種類名 (ENG)"); ?></th>
								<th><?php echo __("部署種類順番"); ?></th>
								<!-- <th><?php echo __("最終部署"); ?></th> -->
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($list)) : //pr(count($list));pr(count($disable_adjust_list));
								$no = ($page - 1) * $limit + 1;
								foreach ($list as $datas) :
									// if ($datas['LayerType']['show_detail']) {
									// 	$show_detail = 'Yes';
									// } else {
									// 	$show_detail = 'No';
									// }  ?>
									<?php if (in_array($datas['LayerType']['id'], $disable_adjust_list)) { ?>
										<tr style="text-align: left;background: darkgray;">
										<?php } else { ?>
										<tr class="orderSort" style="text-align: left;">
										<?php } ?>

										<td class="index"><?php echo  $no; ?></td>
										<td><?php echo $datas['LayerType']['name_jp']; ?></td>
										<td><?php echo $datas['LayerType']['name_en']; ?></td>
										<td class="pri-id">
											<input type="hidden" class="form-control primary-id" value="<?php echo $datas['LayerType']['id']; ?>">
											<?php echo $datas['LayerType']['type_order']; ?>
										</td>
										<!-- <td><?php echo $show_detail; ?></td> -->
										</tr>

								<?php $no++;
								endforeach;
							endif; ?>

						</tbody>
					</table>
					<!-- End Order Sorting Model Box - YarZarLinAung(17/06/2022) -->
					<div id="acc_copy" style="display: none;">
						<div class="col-md-12">
							<div class="form-group popup_row">
								<label class="col-md-5 control-label rep_lbl"><?php echo __("コピー元（年）"); ?></label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="copy_form_year" name="copy_from_year" disabled="" value="<?php echo $year; ?>">
									<input type="hidden" name="hidFromYear" value="<?php echo $year; ?>">
								</div>
							</div>
							<div class="form-group popup_row">
								<label class="col-md-5 control-label rep_lbl"><?php echo __("コピー元（本社）"); ?></label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="copy_from_hq" data-id="<?php echo $hq; ?>" name="copy_form_hq" disabled="" value="<?php echo $headDepartments[$hq]; ?>">
									<input type="hidden" name="hidFromHq" value="<?php echo $hq; ?>">
								</div>
							</div>
							<div class="form-group popup_row">
								<label class="col-md-5 control-label rep_lbl required"><?php echo __("コピー先（年）"); ?></label>
								<div class="col-sm-6">
									<select id="copy_to_year" name="copy_to_year" class="form-control">
									</select>
								</div>
							</div>
							<div class="form-group popup_row">
								<label class="col-md-5 control-label rep_lbl required"><?php echo __("コピー先（本社）"); ?></label>
								<div class="col-sm-6">
									<select id="copy_to_hq" name="copy_to_hq" class="form-control">
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class='' style="margin-bottom: 20px">
					<?php if (count($list) === count($disable_adjust_list)) { ?>
						<a class="btn disabled" aria-disabled="true" href="#" name="editAccountButton" style="float: right;margin-right: unset;">
						<?php } else { ?>
							<a class="btn btn-save-wpr btn-save" href="#" name="editAccountButton" id="editAccountButton" style="float: right;margin-right: unset;">
							<?php } ?>
							<?php echo __('Save Order'); ?>
							</a>
				</div>

			</div>
		</div>
	</div>
	<?php
	echo $this->Form->end();
	?>