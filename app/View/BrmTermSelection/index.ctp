<style>
	.btn_sumisho {
		float: right;
		margin: 25px 25px 0px 0px;
	}

	.datepicker table tr td span.active.active {
		background-color: #00a6a0 !important;
		background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#00a6a0), to(#035956));
		background-image: -webkit-linear-gradient(top, #00a6a0, #00a6a0);
	}

	@media (max-width:992px) {

		.input-group.monthsPicker {
			display: inline-table;
		}

	}

	#msg {
		padding: 12px;
		margin-bottom: 30px;
		font-family: 'Aileron-Light';
	}

	.input-group.monthsPicker {
		padding: 0px 15px;
	}
	.dropdown-menu.usetwentyfour {
		margin-left: 15px !important;
	}
</style>
<?php
//$budget_layer_code =  $this->Session->read('BUDGET_layer_code');
//$language = $this->Session->read('Config.language'); 
?>
<script type="text/javascript">
	$(document).ready(function() {
		var bottomLayerNames = <?php echo json_encode($bottomLayerNames); ?>;
		var bottom_type_order = <?php echo json_encode($bottom_type_order); ?>;
		var selectedTopLayer = "<?php echo $this->Session->read('TOP_LAYER') ? $this->Session->read('TOP_LAYER') : ''; ?>";
		var ba_code = "<?php echo $this->Session->read('BUDGET_BA_CODE'); ?>";

		$('#headquarter').on('change', function() {
			var topLayerId = $(this).val();
			var layerId = topLayerId.split(',');
			$("#ba_code").html('');
			var activeBottomLayer = [];
			var html = '';
			$.each(bottomLayerNames, function(key, value) {
				if (value['Layer']['parent_id']) {
					var pId = '"L' + layerId[0] + '":"' + layerId[1] + '"';
					result = value['Layer']['parent_id'].indexOf(pId)
					if (result != -1) {
						activeBottomLayer.push(bottomLayerNames[key]);
					}

				}
			});

			if (activeBottomLayer.length > 0) {
				html = "<option value=''>--Select " + bottom_type_order['LayerType']['name_en'] + " Name--</option>";
				$.each(activeBottomLayer, function(key, value) {
					select = '';
					html += "<option style='font-size: 14px;' id='" + value['0']['from_date'] + "' value='" + value['Layer']['id'] + "' " + select + ">" + value['Layer']['name_jp'] + "</option>";
					$("#ba_code").html(html);
				});
			} else if(topLayerId == ''){
				$("#ba_code").html("<option value=''>--Select " + bottom_type_order['LayerType']['name_en'] + " Name--</option>");
			}else {
				$("#ba_code").html("<option value=''>--No " + bottom_type_order['LayerType']['name_en'] + " to select--</option>");
			}
		});
		if (selectedTopLayer != '') {
			var layerId = selectedTopLayer.split(',');
			$("#ba_code").html('');
			var activeBottomLayer = [];
			var html = '';
			$.each(bottomLayerNames, function(key, value) {
				if (value['Layer']['parent_id']) {
					var pId = '"L' + layerId[0] + '":"' + layerId[1] + '"';
					result = value['Layer']['parent_id'].indexOf(pId)
					if (result != -1) {
						activeBottomLayer.push(bottomLayerNames[key]);
					}

				}
			});

			if (activeBottomLayer.length > 0) {
				html = "<option value=''>--Select " + bottom_type_order['LayerType']['name_en'] + " Name--</option>";
				$.each(activeBottomLayer, function(key, value) {
					select = '';
					if (value['Layer']['id'] == ba_code) select = "selected";
					html += "<option style='font-size: 14px;' id='" + value['0']['from_date'] + "' value='" + value['Layer']['id'] + "' " + select + ">" + value['Layer']['name_jp'] + "</option>";
					$("#ba_code").html(html);
				});
			} else $("#ba_code").html("<option value=''>--No " + bottom_type_order['LayerType']['name_en'] + " to select--</option>");
		}
		$(".btn_sumisho").click(function(e) {
			e.preventDefault();

			document.getElementById("error").innerHTML = "";
			document.getElementById("success").innerHTML = "";

			var term_id = document.getElementById('term').value;
			var head_dept_id = document.getElementById('headquarter').value;
			var targetmonth = document.getElementById('targetmonth').value;
			var ba_code = document.getElementById('ba_code').value;
			var from_ba_year = $('#ba_code').children(":selected").attr('id');

			if (from_ba_year) {
				from_ba_year = from_ba_year;
			} else {
				from_ba_year = '';
			}

			var admin_level_id = document.getElementById('admin_level_id').value;

			var errorFlag = true;

			if (!checkNullOrBlank(term_id)) {
				var newbr = document.createElement("div");
				var a = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("期間選択"); ?>'])));
				document.getElementById("error").appendChild(a);
				errorFlag = false;
			}


			if (errorFlag) {
				localStorage.setItem("FROM_BA_YEAR", from_ba_year);
				$.ajax({
					type: "POST",
					url: "<?php echo $this->webroot; ?>BrmTermSelection/add",
					data: {
						term_id: term_id,
						head_dept_id: head_dept_id,
						date: targetmonth,
						ba_code: ba_code
					},
					success: function(data) {
						if (data) {

							localStorage.setItem("PLAN", term_id + ',' + targetmonth);

							document.getElementById("success").innerHTML = "";
							var newbr = document.createElement("div");
							var a = document.getElementById("success").appendChild(newbr);
							a.appendChild(document.createTextNode(['<?php echo __("データ選択は成功！"); ?>']));
							document.getElementById("success").appendChild(a);
							document.getElementById("error").innerHTML = "";
							if ($("#flashMessage").length) {
								document.getElementById("flashMessage").innerHTML = "";
							}
						} else {
							var newbrr = document.createElement("div");
							var b = document.getElementById("error").appendChild(newbrr);
							b.appendChild(document.createTextNode(['<?php echo __("予算年度の開始日と終了日の間で目標月を選択してください!"); ?>']));
							document.getElementById("error").appendChild(b);
							document.getElementById("success").innerHTML = "";
							if ($("#flashMessage").length) {
								document.getElementById("flashMessage").innerHTML = "";
							}

						}
					}
				});

			}
		});
	});
</script>

<?php echo $this->Form->create(false, array(
	'url' => 'add',
	'type' => 'post'
));
?>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12">
		<h3><?php echo __("予算実績管理"); ?></h3>
		<hr>
		<div class="success" id="success"></div>
		<div class="error" id="error"><?php echo ($this->Session->check("Message.TermError")) ? $this->Flash->render("TermError") : ''; ?></div>

		<div class="form_test">
			<fieldset class="scheduler-border">
				<legend class="scheduler-border"><?php echo __("予算実績管理"); ?></legend>
				<div class="form-group row">
					<div class="col-md-12">
						<p style="color: blue;padding-left: 15px;">
							<?php echo __("期間と本部を選択してから、[選択の設定]ボタンをクリックしてください。"); ?>
						</p>
					</div>
					<div class="col-md-12">
						<div class="col-md-6">
							<label for="term" class="col-md-4 col-form-label required">
								<?php echo __("期間選択"); ?></label>
							<div class="col-md-8">

								<select id="term" name="term" class="form-control">
									<option value=""> --Select Term Name --</option>
									<?php

									foreach ($terms as $name) :

										$term_name = $name['BrmTerm']['term_name'];
										$temp_var =  ($term_name == '' || empty($term_name)) ? $name['BrmTerm']['budget_year'] . '~' . $name['BrmTerm']['budget_end_year'] : $term_name;
										$term_range = $name['BrmTerm']['budget_year'] . '~' . $name['BrmTerm']['budget_end_year'];
									?>
										<option value="<?php echo $name['BrmTerm']['id'] . "," . $term_range; ?>" <?php if ($this->Session->read('TERM_ID') == $name['BrmTerm']['id']) {
																													echo 'selected';
																												} ?>><?php echo h($temp_var); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<div class="col-md-6">
							<input type="hidden" name="admin_level_id" id="admin_level_id" value="<?php if (!empty($admin_level_id)) echo $admin_level_id; ?>">
							<label for="headquarter" class="col-md-4 col-form-label">
								<?php echo $type_order['LayerType']['name_' . $lang_name].__('選択'); ?></label>
							<?php

							if (!empty($topLayerNames)) { ?>
								<div class="col-md-8">
									<select id="headquarter" name="headquarter" class="form-control">
										<option value="">-- <?php echo 'Select ' . $type_order['LayerType']['name_en']; ?> --</option>
										<?php
										foreach ($topLayerNames as $name) :
										?>
											<option value="<?php echo $name['Layer']['type_order'] . ',' . $name['Layer']['layer_code'] . ',' . $name['Layer']['id'] . ',' . $name['Layer']['name_jp']; ?>" <?php if ($this->Session->read('HEAD_DEPT_ID') == $name['Layer']['id']) {
																																																	echo 'selected';
																																																} ?>>
												<?php
												echo h($name['Layer']['name_jp']);
												?>


											</option>
										<?php endforeach; ?>
									</select>
								</div>
							<?php } else { ?>
								<div class="col-md-8">

									<select id="headquarter" name="headquarter" class="form-control">
										<option value="">--<?php echo 'Select ' . $type_order['LayerType']['name_en']; ?> --</option>

									</select>
								</div>

							<?php } ?>
						</div>
					</div>

					<div class="col-md-12" style="margin-top: 15px">
						<div class="col-md-6">
							<label for="targetmonth" class="col-md-4 col-form-label">
								<?php echo __("対象月"); ?></label>

							<div class="col-md-8 input-group monthsPicker">
								<input type="text" class="form-control" name="targetmonth" id="targetmonth" value="<?php echo $this->Session->read('TARGETMONTH'); ?>" autocomplete="off" style="background-color: #fff;" readonly/>
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>

						</div>
						<div class="col-md-6">
							<label for="salesRepresentative" class="col-md-4 col-form-label">
								<?php echo $bottom_type_order['LayerType']['name_' . $lang_name].__('選択'); ?></label>
							<div class="col-md-8">
								<select id="ba_code" name="ba_code" class="layer_code form-control">
									<option value="">--<?php echo 'Select ' . $bottom_type_order['LayerType']['name_en']; ?> --</option>
								</select>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<button type="button" class="btn btn-success btn_sumisho"><?php echo __("設定選択"); ?> </button>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
</div>
<?php
echo $this->Form->end();
?>