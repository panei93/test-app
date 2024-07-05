<?php
echo $this->Form->create(false, array('type' => 'post', 'id' => '', 'enctype' => 'multipart/form-data'));
?>
<style>
	body {
		font-family: KozGoPro-Regular;
	}


	.yearPicker table {
		/*z-index: 1999 !important;*/
		width: 250px !important;
	}

	.numbers {
		text-align: right !important;
	}

	.popup_row {
		padding-bottom: 30px;
	}

	#year {
		display: inline-block;
		width: 200px;
		vertical-align: middle;
	}

	#overwrite {
		display: none;
	}

	.jconfirm-box-container {
		margin-left: unset !important;
	}
	.btn-save.btn_sumisho {
		margin-right: 1.5rem;
	}
</style>
<?php

if (!empty($target_year)) {
	$search_year = $target_year;
} else {
	$search_year = '';
}

?>
<script>
	$(document).ready(function() {
		document.getElementById("hid_year").value = "";
		document.getElementById("hid_search").value = "SEARCHALL";
		/* float table header */
		if ($('#tbl_position').length > 0) {
			var $table = $('#tbl_position');
			$table.floatThead({
				responsiveContainer: function($table) {
					return $table.closest('.table-responsive');
				}
			});
		}
		/* when select term, to get term value and text */
		$("#term_select").change(function() {
			$("#error").empty();
			$("#success").empty();
			var target_year = $("#target_year").val();
			/* choosen term after choosen target_year */
			getFieldname(target_year);
		});

		/* to set year format */
		// $(".date").datepicker({
		//     format: "yyyy",
		//     viewMode: "years", 
		//     minViewMode: "years"
		// });

		// if have search year value , show copy button
		var search_year = "<?php echo $search_year; ?>";
		if (search_year != '') {
			$('#copy').prop('disabled', false);
			document.getElementById("hid_year").value = search_year;
		}

		$("#to_year").on('change', function() {
			var to_year = $('#to_year').val();
			if (to_year) {
				$.ajax({
					type: "POST",
					url: "<?php echo $this->webroot; ?>BrmPositions/getToYearData",
					data: {
						copy_year: to_year
					},
					dataType: 'json',
					beforeSend: function() {
						loadingPic();
					},

					success: function(data) {

						if (data.response_year) {
							$('#overwrite').show();
						} else {
							$('#overwrite').hide();
						}
						$('#overlay').hide();

					}
				});

			} else {
				$('#overwrite').hide();
			}

		});
		/* when choose year, to get field list in feild selectbox */
		$(".yearPicker").on("focusout", function() {
			// $("#error").empty();
			// $("#success").empty();
			var target_year = $("#target_year").val();
			var term_id = $("#term_id option:selected").val();
			var term = $("#term_id option:selected").text();
			var chk = true;
			if (term_id != "" && target_year != "") {
				if (!checkTermAndYear(term, target_year)) {
					$("#error").html(errMsg(commonMsg.JSE046)).show();
					chk = false;
					getFieldname(); /* remove field list */
				}
			}
			if (chk) {
				/* get field list in the dropdown with same year and same term */
				getFieldname(target_year);
			}
		});
		/* when choose field without choosing term and year, to show error message */
		$("#field_id").click(function() {
			$("#error").empty();
			// $("#success").empty();
			var target_year = $("#target_year").val();
			var err_msg = '';
			if (target_year == "") {
				err_msg += errMsg(commonMsg.JSE001, ['<?php echo __("年度") ?>']) + "<br/>";
			}
			$("#error").append(err_msg);
		});
	});
	/* Show only Field list of same term and same year */
	function getFieldname(target, field) {
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>BrmPositions/getFieldData",
			data: {
				target_year: target
			},
			dataType: 'json',
			success: function(data) {
				var html;
				html = "<option value=''>----- <?php echo __("選択") ?> ----- </option>";
				$.each(data, function(brm_field_id, field_name_jp) {
					if (field != brm_field_id) {
						html += "<option style='font-size: 14px;' value='" + brm_field_id + "'>" + field_name_jp + "</option>";
					} else {
						html += "<option style='font-size: 14px;' value='" + brm_field_id + "' selected='true'>" + field_name_jp + "</option>";
					}
				});
				$("#field_id").html(html);
			}
		});
	}

	/*year is greater than start year and year is smaller than end year*/
	function checkTermAndYear(term, year) {
		var term = term.split('~');
		var start = term[0];
		var end = term[1];
		if (year < start || year > end) {
			return false;
		}
		return true;
	}

	function click_SavePosition() {
		document.getElementById("error").innerHTML = "";
		document.getElementById("success").innerHTML = "";
		var target_year = document.getElementById("target_year").value;
		var field_id = document.getElementById("field_id").value;
		var position_name_jp = document.getElementById("position_name_jp").value;
		var unit_salary = document.getElementById("unit_salary").value;
		var display_no = document.getElementById("display_no").value;
		var chk = true;

		if (!checkNullOrBlank(target_year)) {
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("年度") ?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
		}
		if (!checkNullOrBlank(field_id)) {
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("職務") ?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
		}
		if (checkNullOrBlank(unit_salary)) {
			/* allow only 12 digits with 2 decimal place */
			var decimalOnly = /^\s*(\d{1,10})(\.\d{0,2})?\s*$/;
			if (!decimalOnly.test(unit_salary)) {
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE047, ['<?php echo __("単価") ?>'])));
				document.getElementById("error").appendChild(a);
				chk = false;
			}
		}
		if (!checkNullOrBlank(position_name_jp)) {
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("役職名（JP）") ?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
		}
		if (!checkNullOrBlank(display_no)) {
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("テーブル番号") ?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
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
							document.forms[0].action = "<?php echo $this->webroot; ?>BrmPositions/savePositionData";
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

	function Click_EditPosition(id) {
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>BrmPositions/editPositionData",
			data: {
				id: id
			},
			dataType: 'json',
			beforeSend: function() {
				loadingPic();
			},

			success: function(data) {
				var position_id = data['position_id'];
				var target_year = data['target_year'];
				var display_no = data['display_no'];
				var position_name_jp = data['position_name_jp'];
				var position_name_en = data['position_name_en'];
				var unit_salary = data['unit_salary'];
				var edit_flag = data['edit_flag'];
				var term = data['term'];
				var field_id = data['field_id'];
				var field_name_jp = data['field_name_jp'];

				$('#hid_updateId').val(position_id);
				$('#selected_data').val(term);
				$('#target_year').val(target_year);
				getFieldname(target_year, field_id);
				$('#position_name_jp').val(position_name_jp);
				$('#position_name_en').val(position_name_en);
				$('#unit_salary').val(unit_salary);
				if (edit_flag == 1) {
					$('#edit_flag').attr('checked', true);
				} else {
					$('#edit_flag').attr('checked', false);
				}
				$('#display_no option[value="' + display_no + '"]').prop('selected', true);
				$('#btn_save').hide();
				$('#btn_update').show();
				$('#overlay').hide();
			}
		});
	}

	function click_UpdatePosition() {
		document.getElementById("error").innerHTML = "";
		document.getElementById("success").innerHTML = "";
		var target_year = document.getElementById("target_year").value;
		var field_id = document.getElementById("field_id").value;
		var position_name_jp = document.getElementById("position_name_jp").value;
		var unit_salary = document.getElementById("unit_salary").value;
		var display_no = document.getElementById("display_no").value;
		//var hid_year = document.getElementById("hid_year").value;
		var path = window.location.pathname;
		var page = path.split("/").pop();
		document.getElementById('hid_page_no').value = page;
		var chk = true;

		if (!checkNullOrBlank(target_year)) {
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("年度") ?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
		}
		if (!checkNullOrBlank(field_id)) {
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("職務") ?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
		}
		if (checkNullOrBlank(unit_salary)) {
			var decimalOnly = /^\s*(\d{1,10})(\.\d{0,2})?\s*$/;
			if (!decimalOnly.test(unit_salary)) {
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE047, ['<?php echo __("単価") ?>'])));
				document.getElementById("error").appendChild(a);
				chk = false;
			}
		}
		if (!checkNullOrBlank(position_name_jp)) {
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("役職名（JP）") ?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
		}
		if (!checkNullOrBlank(display_no)) {
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("テーブル番号") ?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
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
							document.forms[0].action = "<?php echo $this->webroot; ?>BrmPositions/updatePositionData";
							document.forms[0].method = "POST";
							document.forms[0].submit();
							return true;
						}
					},
					cancel: {
						text: '<?php echo __("いいえ"); ?>',
						btnClass: 'btn-default',
						cancel: function() {
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
	}

	function Click_DeletePosition(id) {
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';
		document.getElementById("hid_deleteId").value = id;
		var path = window.location.pathname;
		var page = path.split("/").pop();
		document.getElementById('hid_page_no').value = page;
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
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmPositions/deletePositionData";
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

	function SearchData() {

		document.getElementById("error").innerHTML = "";
		document.getElementById("success").innerHTML = "";
		var year = document.getElementById("year").value;

		if (year == '') document.getElementById("hid_search").value = "SEARCHALL";
		else document.getElementById("hid_search").value = "";

		document.forms[0].action = "<?php echo $this->webroot; ?>BrmPositions/SearchData";
		document.forms[0].method = "POST";
		document.forms[0].submit();
		$("html, body").animate({
			scrollTop: 10
		}, "fast");
	}

	function popupscreen() {

		var target_year = document.getElementById('year').value;

		// $('#to_year').val('');
		$('#to_year option').removeAttr('selected').filter('[value=""]').attr('selected', true);
		$('#popuperror').hide();
		$('#overwrite').hide();

		if (target_year != '') {
			$('#from_year').val(target_year);
		}

	}

	function CopyData() {
		$('#popuperror').show();
		var from_year = document.getElementById('from_year').value;
		var to_year = document.getElementById('to_year').value;


		document.getElementById("popupsuccess").innerHTML = "";
		document.getElementById("popuperror").innerHTML = "";
		var chk = true;
		//number check		
		console.log(to_year);
		if (!checkNullOrBlank(to_year)) {
			var newbr = document.createElement("div");
			var a = document.getElementById("popuperror").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("コピー先（年）") ?>'])));
			document.getElementById("popuperror").appendChild(a);
			chk = false;
		}
		//year value and copy_year value is same
		if (checkNullOrBlank(from_year) && checkNullOrBlank(to_year)) {
			if (from_year == to_year) {
				var newbr = document.createElement("div");
				var a = document.getElementById("popuperror").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE060, ['<?php echo __("コピーするデータは同じであってはなりません") ?>'])));
				document.getElementById("popuperror").appendChild(a);
				chk = false;
			}
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
							document.forms[0].action = "<?php echo $this->webroot; ?>BrmPositions/CopyPositionMp";
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

	}

	function OverwirteData() {
		$('#popuperror').show();
		var from_year = document.getElementById('from_year').value;
		var to_year = document.getElementById('to_year').value;

		document.getElementById("popupsuccess").innerHTML = "";
		document.getElementById("popuperror").innerHTML = "";
		var chk = true;

		if (!checkNullOrBlank(to_year)) {
			var newbr = document.createElement("div");
			var a = document.getElementById("popuperror").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("コピー年度") ?>'])));
			document.getElementById("popuperror").appendChild(a);
			chk = false;
		}
		//year value and copy_year value is same
		if (checkNullOrBlank(from_year) && checkNullOrBlank(to_year)) {
			if (from_year == to_year) {
				var newbr = document.createElement("div");
				var a = document.getElementById("popuperror").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE060, ['<?php echo __("コピーするデータは同じであってはなりません") ?>'])));
				document.getElementById("popuperror").appendChild(a);
				chk = false;
			}
		}
		if (chk) {
			$("#popuperror").css("display", "none");
			$.confirm({
				title: '<?php echo __("上書き確認"); ?>',
				icon: 'fas fa-exclamation-circle',
				type: 'orange',
				typeAnimated: true,
				closeIcon: true,
				columnClass: 'medium',
				animateFromElement: true,
				animation: 'top',
				draggable: false,
				content: "<?php echo __("上書きしますか？"); ?>",
				buttons: {
					ok: {
						text: '<?php echo __("はい"); ?>',
						btnClass: 'btn-info',
						action: function() {
							loadingPic();
							document.forms[0].action = "<?php echo $this->webroot; ?>BrmPositions/OverwirteDataCopy";
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

	}
	/*  
	 *	Show hide loading overlay
	 *	@Zeyar Min  
	 */
	function loadingPic() {
		$("#overlay").show();
		$('.jconfirm').hide();
	}

	/* when show err msg or succ msg, scroll is top of the page */
	function scrollText() {
		var tes = $('#error').text();
		var tes1 = $('.success').text();
		if (tes) {
			$("html, body").animate({
				scrollTop: 20
			}, "fast");
		}
		if (tes1) {
			$("html, body").animate({
				scrollTop: 20
			}, "fast");
		}
	}
</script>
<div id="overlay">
	<span class="loader"></span>
</div>
<div class="register_container content">
	<div class="row" style="font-size: 1em;">
		<div class="heading_line_title">
			<h3><?php echo __('ポジション管理'); ?></h3>
			<hr>
		</div>
		<!-- for update -->
		<input type="hidden" name=hid_updateId id="hid_updateId">
		<!-- for delete -->
		<input type="hidden" name=hid_deleteId id="hid_deleteId">
		<!-- for selected text(term_name) -->
		<input type="hidden" name=selected_data id="selected_data">
		<!-- for page no. -->
		<input type="hidden" name=hid_page_no id="hid_page_no">
		<!-- for update/delete condition to get search year value -->
		<input type="hidden" name=hid_year id="hid_year">
		<input type="hidden" name=hid_search id="hid_search">

		<!-- show error msg and success msg -->
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.PositionmpOK")) ? $this->Flash->render("PositionmpOK") : ''; ?></div>
			<div class="error" id="error"><?php echo ($this->Session->check("Message.PositionmpFail")) ? $this->Flash->render("PositionmpFail") : ''; ?></div>
		</div>
		<!-- field group -->
		<div class="form-group row col-md-12">
			<div class="col-md-6">
				<label for="target_year" class="col-md-4 col-form-label required">
					<?php echo __('年度選択'); ?>
				</label>
				<div class="col-md-8 datepicker-years">
					<!-- <div class="input-group date datepicker" data-provide="datepicker" style="padding: 0px;"> -->
					<div class="input-group date yearPicker" data-provide="yearPicker" id="datepicker" style="padding: 0px;">
						<input type="text" class="form-control target" id="target_year" name="target_year" autocomplete="off" style="background-color: #fff;" readonly>
						<span class="input-group-addon">
							<span class="glyphicon glyphicon-calendar"></span>
						</span>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<label for="field_name" class="col-md-4 col-form-label required">
					<?php echo __('職務選択'); ?>
				</label>
				<div class="col-md-8" id="field_select">
					<select id="field_id" name="field_id" class="form-control">
						<option value="">----- <?php echo __("選択") ?> ----- </option>
					</select>
				</div>
			</div>
		</div>

		<div class="form-group row col-md-12">
			<div class="col-md-6">
				<label for="position_name_jp" class="col-md-4 col-form-label required">
					<?php echo __('役職名（JP）'); ?>
				</label>
				<div class="col-md-8">
					<input type="text" class="form-control" id="position_name_jp" name="position_name_jp" value="" maxlength="250" />
				</div>
			</div>
			<div class="col-md-6">
				<label for="position_name_en" class="col-md-4 col-form-label">
					<?php echo __('役職名（ENG）'); ?>
				</label>
				<div class="col-md-8">
					<input type="text" class="form-control" id="position_name_en" name="position_name_en" value="" maxlength="250" />
				</div>
			</div>
		</div>

		<div class="form-group row col-md-12">
			<div class="col-md-6">
				<label for="unit_salary" class="col-md-4 col-form-label">
					<?php echo __('単価'); ?>
				</label>
				<div class="col-md-8">
					<input type="text" class="form-control" id="unit_salary" name="unit_salary" value="" maxlength="250" />
				</div>
			</div>
			<div class="col-md-6">
				<label for="display_no" class="col-md-4 col-form-label required">
					<?php echo __('テーブル番号'); ?>
				</label>
				<div class="col-md-8">
					<select class="form-control" id="display_no" name="display_no" value="">
						<option value="">----- <?php echo __("選択") ?> ----- </option>
						<option value="1"><?php echo (TableOrder::Table_01); ?></option>
						<option value="2"><?php echo (TableOrder::Table_02); ?></option>
						<option value="4"><?php echo (TableOrder::Table_04); ?></option>
					</select>
				</div>
			</div>
		</div>

		<div class="form-group row col-md-12">
			<div class="col-md-6"></div>
			<div class="col-md-6">
				<div class="col-md-4"></div>
				<div class="col-md-8">
				<label for="edit_flag" class="col-form-label">
				<input type="checkbox" id="edit_flag" name="edit_flag" class="chk_editflag">&nbsp;&nbsp;<?php echo __('単価入力許可'); ?>
				</label>
					
				</div>
			</div>
		</div>
		<!-- btn group -->
		<div class="form-group row col-md-12" style="margin-bottom: 50px;">
			<div class="col-md-12 " style="text-align: end;" id="save">
				<input type="button" class="btn-save btn-success btn_sumisho" id="btn_save" name="btn_save" value="<?php echo __('保存'); ?>" onclick="click_SavePosition();">

				<input type="button" class="btn-save btn-success btn_sumisho" id="btn_update" name="btn_update" style="display: none;" value="<?php echo __('変更'); ?>" onclick="click_UpdatePosition();" style="margin-top: -1.5rem;">
			</div>
		</div>

		<!-- show total row count -->
		<?php if (!empty($succmsg)) { ?>
			<!-- search and copy -->
			<table width="100%">
				<tr>
					<td valign="bottom">
						<div class="pull-left msgfont" id="succc">
							<span>
								<?php echo ($succmsg); ?>
							</span>
						</div>
					</td>
					<td>
						<div class="pull-right align-top">
							<select class="form-control" id="year" name="year" value="">
								<option value="">---- <?php echo __("年度選択") ?> ----</option>
								<?php foreach ($years as $key => $year) : ?>
									<?php
									$year = $year['BrmPosition']['target_year'];
									if (!empty($target_year)) {
										if ($target_year == $year) {
											$select = 'selected';
										} else {
											$select = '';
										}
									}
									?>
									<option value="<?= $year ?>" <?= $select ?>>
										<?= $year ?>
									</option>
								<?php endforeach; ?>
							</select>
							<input type="button" class="btn btn-success btn_sumisho" value="<?php echo __('検索'); ?>" name="search" onclick="SearchData();">
							<input type="button" data-target="#myModal" data-toggle="modal" data-backdrop="static" data-keyboard="false" class="btn btn-success btn_sumisho" value="<?php echo __('コピー'); ?>" name="copy" id="copy" onclick="popupscreen();" disabled>
						</div>
					</td>
				</tr>
			</table>

		<?php } else if (!empty($errmsg)) { ?>
			<div class="row col-md-12  d-flex justify-content-center">
				<div></div>
				<div id="err" class="no-data"> <?php echo ($errmsg); ?></div>
				<div></div>
			</div>
			
		<?php } ?>
		<!-- show list table -->
		<?php if ($rowCount != 0) { ?>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">
					<div class="table-responsive tbl-wrapperd">
						<table class="table table-striped table-bordered acc_review" id="tbl_position" style="margin-top:10px;width: 100%;">
							<thead class="check_period_table">
								<tr>
									<th><?php echo __("年度"); ?></th>
									<th><?php echo __("職務名（JP）"); ?></th>
									<th><?php echo __("役職名（JP）"); ?></th>
									<th><?php echo __("役職名（ENG）"); ?></th>
									<th><?php echo __("単価"); ?></th>
									<th><?php echo __("テーブル番号"); ?></th>
									<th><?php echo __("単価入力許可"); ?></th>
									<th colspan="2"><?php echo __("アクション") ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								if (!empty($list)) foreach ($list as $datas) {
									$id = $datas['BrmPosition']['id'];
									$target_year = $datas['BrmField']['target_year'];
									$field_name_jp = $datas['BrmField']['field_name_jp'];
									$target_year = $datas['BrmPosition']['target_year'];
									$position_name_jp = $datas['BrmPosition']['position_name_jp'];
									$position_name_en = $datas['BrmPosition']['position_name_en'];
									$unit_salary = $datas['BrmPosition']['unit_salary'];
									$display_no = $datas['BrmPosition']['display_no'];
									if ($display_no == 1) {
										$display_no = TableOrder::Table_01;
									} elseif ($display_no == 2) {
										$display_no = TableOrder::Table_02;
									} elseif ($display_no == 4) {
										$display_no = TableOrder::Table_04;
									}
									$edit_flag = $datas['BrmPosition']['edit_flag'];
									if ($edit_flag == 1) {
										$edit_flag = 'ON';
									} else {
										$edit_flag = 'OFF';
									}
									$flag = $datas['BrmPosition']['flag'];

									if ($flag != 0) { ?>
										<tr style="text-align: left;">
											<td><?php echo $target_year; ?></td>
											<td><?php echo $field_name_jp; ?></td>
											<td><?php echo $position_name_jp; ?></td>
											<td><?php echo $position_name_en; ?></td>
											<td class="numbers"><?php echo number_format($unit_salary, 2); ?></td>
											<td><?php echo $display_no; ?></td>
											<td><?php echo $edit_flag; ?></td>
											<td width="100px" class="link" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
												<a class="" href="#" onclick="Click_EditPosition('<?php echo $id; ?>');" title="<?php echo __('編集'); ?>"><i class="fa-regular fa-pen-to-square"></i>
												</a>
											</td>
											<td width="100px" class="link" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
												<a class="delete_link" href="#" onclick="Click_DeletePosition('<?php echo $id; ?>');" title="<?php echo __('削除'); ?>"><i class="fa-regular fa-trash-can"></i>
												</a>
											</td>
										</tr>
								<?php }
								} ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
	<!-- show pagination -->
	<div class="row" style="clear:both;margin: 40px 0px;">
			<?php if ($rowCount > Paging::TABLE_PAGING) { ?>
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
<!-- PopUpBox  -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content contantbond">
			<div class="modal-header">
				<button type="button" class="close" id="clearData" data-dismiss="modal">&times;</button>
				<h3 class="modal-title"><?php echo __("コピー確認"); ?></h3>
			</div>
			<div class="modal-body">
				<!-- success,error -->
				<div class="success" id="popupsuccess"></div>
				<div class="error" id="popuperror"></div>
				<!-- end success,error -->
				<div class="table-responsive modal_tbl_wrapper">
					<div class="col-md-12">
						<div class="form-group popup_row">
							<label class="col-md-4 control-label rep_lbl"><?php echo __("コピー元（年）"); ?></label>
							<div class="col-sm-8">
								<input type="text" class="form-control" id="from_year" value="<?= $target_year ?>" name="from_year" disabled>
								<input type="hidden" name="hid_form_year" value="<?= $target_year ?>">
							</div>
						</div>

						<div class="form-group popup_row">
							<label class="col-md-4 control-label rep_lbl required"><?php echo __("コピー先（年）"); ?></label>
							<div class="col-sm-8">
								<select class="form-control" id="to_year" name="to_year" value="">
									<option value="">---- Select ----</option>
									<?php foreach ($copy_year_datas as $key => $copy_year) : ?>
										<option value="<?= $copy_year ?>">
											<?= $copy_year ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" id="term_copy" onclick="CopyData()" class="btn btn-success btn_sumisho" style="margin: unset;"><?php echo __('追加'); ?> </button>
				<button type="button" id="overwrite" onclick="OverwirteData()" class="btn btn-success btn_sumisho" style="margin: unset;"><?php echo __('上書き'); ?> </button>
			</div>
		</div>
	</div>
</div>
<!-- end popup -->
<?php
echo $this->Form->end();
?>