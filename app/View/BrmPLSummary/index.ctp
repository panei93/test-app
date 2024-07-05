<?php echo $this->Form->create(false, array('url' => '', 'type' => 'post', 'class' => '', 'name' => 'plsummary_form', 'id' => 'plsummary_form'));
?>
<style type="text/css">
	.tbl-pl-summary {
		table-layout: fixed;
		margin-top: 20px;
	}

	.blank-table-row td.show-hide-btn:before {
		content: '-';
		width: 20px;
		display: inline-block;
		height: 20px;
		line-height: 20px;
		background-color: #eee;
		text-align: center;
		position: absolute;
		top: 0;
		right: 5%;
		z-index: 1;
		color: #fff;
		background-color: #d9534f;

	}

	.blank-table-row td.show-hide-btn.show-col:before {
		content: '+';
		background-color: #4caf50;
	}

	.blank-table-row td.show-hide-btn:after {
		content: "";
		width: 95%;
		display: inline-block;
		height: 3px;
		background-color: #eee;
		position: absolute;
		top: 10px;
		right: 5%;
	}

	.blank-table-row,
	.blank-table-row td.show-hide-btn {
		background: #fff !important;
		border-bottom: none !important;
		border-top: none !important;
		border-left: none !important;
		border-right: none !important;
		height: 25px;
		position: relative;
	}

	.tbl-pl-summary tbody td.name-field .arrow:before {
		content: "▲";
		width: 20px;
		display: inline-block;
		height: 20px;
		line-height: 20px;
		background-color: #eee;
		text-align: center;
		position: absolute;
		top: 0;
		left: 0;
	}

	.tbl-pl-summary tbody td.name-field.show-row .arrow:before {
		content: "▼";
	}

	.tbl-pl-summary tbody td.name-field {
		position: relative;
		padding-left: 25px !important;
	}

	.tbl-pl-summary th {
		text-align: center;
		border-bottom: 1px solid #A4A4A4;
		border-right: 1px dotted #A4A4A4;
	}

	.tbl-pl-summary td {
		padding: 5px;
		border-top: 1px dotted #A4A4A4;
		border-right: 1px dotted #A4A4A4;
	}

	.tbl-pl-summary th,
	.tbl-pl-summary .name-field {
		padding: 5px;
		white-space: nowrap;
	}

	.tbl-pl-summary th.month {
		width: 80px;
	}

	.tbl-pl-summary th.total {
		width: 90px;
	}

	.number {
		text-align: right;
	}

	.tbl-pl-summary tbody {
		border-left: 3px solid #444;
		border-bottom: 2px solid #444;
		border-right: 3px solid #444;
	}

	.bold-border-btm {
		border-bottom: 2px solid #444;
	}

	.bold-border-top {
		border-top: 2px solid #444;
	}

	td.bold-border-lft,
	th.bold-border-lft {
		border-left: 2px solid #444 !important;
	}

	tr.bold-border-lft {
		border-left: 3px solid #444 !important;
	}

	.bold-border-rgt {
		border-right: 2px solid #444 !important;
	}

	.bdl-solid {
		border-left: 1px solid #A4A4A4 !important;
	}

	.b-none {
		border: none !important;
	}

	.bb-none {
		border-bottom: none !important;
	}

	.bt-none {
		border-top: none !important;
	}

	.negative {
		color: #f31515;
	}

	.disable,
	.freeze {
		cursor: none;
		pointer-events: none;
		background-color: #F9F9F9;
	}

	.talign-left {
		text-align: left !important;
	}

	.fl-scrolls {
		z-index: 1;
	}

	.clone-column-table-wrap table.tbl-pl-summary.bold-border,
	.clone-column-head-table-wrap table.tbl-pl-summary.bold-border {
		width: unset !important;
	}

	.clone-head-table-wrap {
		top: -20px !important;
		height: 198px !important;
	}

	#load {
		z-index: 1000;
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0, 0, 0, 0.2);
	}

	.blank-table-row td.show-hide-btn:hover {
		background-color: #FAFAFA !important;

	}

	.blank-table-row td.show-hide-btn:active {
		transform: translateY(1px);
		cursor: progress;
	}

	.col-level-1 {

		max-width: 100px;
		word-wrap: break-word;
	}
	.btn_sumisho {
		margin-right: 15px;
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {
		//fixed column and header
		if ($('#tbl_pl').length > 0) { // check data is at least 1 row/column keep
			$('.tbl-wrapper').freezeTable({
				'columnNum': 1,
				'columnKeep': true,
				'freezeHead': true,
				'scrollBar': true,
			});
			setTimeout(function() {
				$('.tbl-wrapper').freezeTable('resize');
			}, 1000);
		}

		/* floating scroll */
		if ($(".tbl-wrapper").length) {
			$(".tbl-wrapper").floatingScroll();
		}

		/*Synchronize two scrollbars for one div of two table */
		$(".show-hide-btn").click(function() {

			var idName = $(this).attr('id');
			var recordColspan = $('#' + idName).attr('colspan') - 1;
			var totalColspan = $('#half-total-col').attr('colspan');

			if ($('#' + idName).hasClass('hide-col')) {

				var recordColspan = $('#' + idName).attr('colspan') - 1;
				var hideDiff = totalColspan - recordColspan;

				$('.row.table-responsive.tbl-wrapper .tbl-pl-summary thead.check_period_table.bold-border-btm ' + '.' + idName).hide();
				$('.row.table-responsive.tbl-wrapper .tbl-pl-summary tbody ' + '.' + idName).hide();
				if (idName == 'first-6m-col' || idName == 'last-6m-col') {
					console.log('1');
					$('.' + idName + '-btn').attr('colspan', 1);
					$('.half-total-col' + '-btn').attr('colspan', hideDiff);
					$('.yearly-header').attr('colspan', hideDiff);
					$('.yearly-header').css('min-width', hideDiff * 65 + 'px');

				} else {
					console.log('2');
					$('.row.table-responsive.tbl-wrapper .tbl-pl-summary thead.check_period_table.bold-border-btm ' + '.first-6m-col').hide();
					$('.row.table-responsive.tbl-wrapper .tbl-pl-summary tbody ' + '.first-6m-col').hide();
					$('.row.table-responsive.tbl-wrapper .tbl-pl-summary thead.check_period_table.bold-border-btm ' + '.last-6m-col').hide();
					$('.row.table-responsive.tbl-wrapper .tbl-pl-summary tbody ' + '.last-6m-col').hide();
					$('.first-6m-col-btn').hide();
					$('.last-6m-col-btn').hide();
					$('.' + idName + '-btn').attr('colspan', 1);
					$('.yearly-header').attr('colspan', 1);
					$('.yearly-header').css('min-width', 180 + 'px');
					$('.clone-column-table-wrap').css('width', '100% !important');
					$('.clone-head-table-wrap').css('width', '100% !important');

					$('.tbl-pl-summary').css('overflow', 'hidden');
				}
				$('.' + idName + '-btn').removeClass('hide-col');
				$('.' + idName + '-btn').addClass('show-col');

			} else {

				var recordColspan = parseInt($('#' + idName).attr('colspan')) + 6;
				var showDiff = parseInt(totalColspan) + parseInt(recordColspan);

				$('.row.table-responsive.tbl-wrapper .tbl-pl-summary thead.check_period_table.bold-border-btm ' + '.' + idName).show();
				$('.row.table-responsive.tbl-wrapper .tbl-pl-summary tbody ' + '.' + idName).show();
				if (idName == 'first-6m-col' || idName == 'last-6m-col') {

					$('.' + idName + '-btn').attr('colspan', 7);
					$('.half-total-col' + '-btn').attr('colspan', showDiff - 1);
					$('.yearly-header').attr('colspan', showDiff - 1);
					$('.yearly-header').css('min-width', showDiff * 65 + 'px');

				} else {

					$('.row.table-responsive.tbl-wrapper .tbl-pl-summary thead.check_period_table.bold-border-btm ' + '.first-6m-col').show();
					$('.row.table-responsive.tbl-wrapper .tbl-pl-summary tbody ' + '.first-6m-col').show();
					$('.row.table-responsive.tbl-wrapper .tbl-pl-summary thead.check_period_table.bold-border-btm ' + '.last-6m-col').show();
					$('.row.table-responsive.tbl-wrapper .tbl-pl-summary tbody ' + '.last-6m-col').show();
					$('.first-6m-col-btn').show();
					$('.last-6m-col-btn').show();
					$('.first-6m-col-btn').removeClass('show-col');
					$('.last-6m-col-btn').removeClass('show-col');
					$('.first-6m-col-btn').addClass('hide-col');
					$('.last-6m-col-btn').addClass('hide-col');
					$('.first-6m-col-btn').attr('colspan', 7);
					$('.last-6m-col-btn').attr('colspan', 7);
					$('.' + idName + '-btn').attr('colspan', 15);
					$('.yearly-header').attr('colspan', 15);

					$('.yearly-header').css('min-width', 15 * 65 + 'px');
					$('.tbl-pl-summary').css('width', 'auto');
					$('.tbl-pl-summary').css('overflow', 'auto');
					$('.fl-scrolls').css('margin-bottom', '40px');
				}
				$('.' + idName + '-btn').removeClass('show-col');
				$('.' + idName + '-btn').addClass('hide-col');
			}
		});

		$(".name-field .arrow").click(function() {

			var rowLevel = $(this).parent('.name-field').attr('colspan');
			var getrowLevel = "1";
			if ($(this).parent('.name-field').hasClass('show-row')) {

				for (var i = rowLevel - 1; i > 0; i--) {

					$('.row-level-' + i).show();
					$('th.row-level-' + rowLevel).attr('colspan', 1);

					if ($('.row-level-' + i + ' .name-field').hasClass('show-row')) {

						getrowLevel = i;
						break
					}

				}
				$('.row-level-' + rowLevel + ' .name-field').removeClass('show-row');

				var outer_width = $('td.bb-none.name-field').outerWidth() + 'px';

				$('.clone-column-table-wrap').css('width', outer_width);
				$('.clone-head-table-wrap.clone-column-head-table-wrap').css('width', outer_width);

			} else {

				for (var i = rowLevel - 1; i > 0; i--) {
					$('.row-level-' + i).hide();
				}
				$('th.row-level-' + rowLevel).attr('colspan', rowLevel);
				$('.row-level-' + rowLevel + ' .name-field').addClass('show-row');

				var outer_width = $('td.bb-none.name-field').outerWidth() + 'px';

				$('.clone-column-table-wrap').css('width', outer_width);
				$('.clone-head-table-wrap.clone-column-head-table-wrap').css('width', outer_width);
			}
		});

		$(".table-responsive.tbl-wrapper").floatingScroll();

		$('.tbl-years td').each(function() {
			MakeNegative($(this));
		});

		var hq_val = $("#headquarter").val();
		//show  session of layer_code in select box
		if (hq_val) {
			var budget_layer_code = "<?php echo $budget_layer_code; ?>";
			setBACode(hq_val, budget_layer_code, '');
			setDept(hq_val);
		}
		//headquarter of layer_code find in db          
		$('#headquarter').on('change', function() {
			var headquarter_val = $(this).val();
			setBACode(headquarter_val, '', '');
			setDept(headquarter_val);
		});

		//headquarter of layer_code find in db          
		$('#dept_select').on('change', function() {
			var headquarter_val = $('#headquarter').val();
			var dept_val = $(this).val();
			setBACode(headquarter_val, '', dept_val);
		});

		document.onreadystatechange = function() {
			var state = document.readyState
			if (state == 'interactive') {
				document.getElementById('contents').style.visibility = "hidden";

			} else if (state == 'complete') {
				setTimeout(function() {
					document.getElementById('interactive');
					document.getElementById('load').style.visibility = "hidden";
					document.getElementById('contents').style.visibility = "visible";

				}, 1000);
			}
		}

	});

	function setBACode(headquarter_val, b_code, dept_val) {

		var language = "<?php echo $language; ?>";
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>BrmPLSummary/getBACode",
			data: {
				headquarter_val: headquarter_val,
				dept_val: dept_val
			},
			dataType: 'json',
			beforeSend: function() {
				loadingPic();
			},
			success: function(datas) {

				var baData = datas['layer_list'];
				var errMsg = datas['errmsg'];

				if (errMsg != '') {
					$("#layer_code").html("<option value=''>--No Layer to select--</option>");
				} else {
					var len = baData.length;
					var html = "<option value=''>--Select Layer Name--</option>";
					for (i = 0; i < len; i++) {

						var layer_code = baData[i].bottomLayer.layer_code;
						var name_jp = baData[i].bottomLayer.name_jp;
						var name_en = baData[i].bottomLayer.name_en;
						var from_year_ba = baData[i][0]['from_date'];
						var select = "";

						if (b_code == layer_code) {
							select = "selected";
						} else {
							select = "";
						}
						//check language
						if (language == 'eng') {
							if (name_en) {
								html += "<option style='font-size: 14px;' id='" + from_year_ba + "' value='" + layer_code + "' " + select + ">" + layer_code + "/" + name_en + "</option>";

							} else {
								html += "<option style='font-size: 14px;' id='" + from_year_ba + "' value='" + layer_code + "' " + select + ">" + layer_code + "</option>";
							}
						} else {
							html += "<option style='font-size: 14px;' id='" + from_year_ba + "' value='" + layer_code + "' " + select + ">" + layer_code + "/" + name_jp + "</option>";
						}
					}
					$("#layer_code").html(html);
				}
				$('#overlay').hide();

			}
		});
	}

	function setDept(headquarter_val) {
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>BrmPLSummary/getDept",
			data: {
				headquarter_val: headquarter_val
			},
			dataType: 'json',
			beforeSend: function() {
				loadingPic();
			},
			success: function(datas) {
				var len = datas['dept_list'].length;
				var select = "";
				var dname = "<?php echo $layer_type[SETTING::LAYER_SETTING['middleLayer']]; ?>";
				var html = "<option value=','>--Select " + dname + " Name--</option>";
				for (i = 0; i < len; i++) {

					var department = datas['dept_list'][i].Layer.name_jp;
					var dept_id = datas['dept_list'][i].Layer.layer_code;
					var select_dept_id = $('#dept_id').val();

					if (dept_id == select_dept_id) {
						select = "selected";
					} else {
						select = "";
					}
					html += "<option value='" + dept_id + "," + department + "' " + select + ">" + department + "</option>";
				}
				$("#dept_select").html(html);
				$('#overlay').hide();
			}
		});
	}

	function MakeNegative(num) {

		if ((num.val().indexOf('-') == 0) || (num.html().indexOf('-') == 0)) {
			$(num).addClass('negative');

			num.val(function(i, v) { //index, current value
				return v;
			});

		} else {
			if ($(num).hasClass('negative')) {
				$(num).removeClass('negative')
			}
		}
	}

	function excelDownload() {

		document.forms['0'].action = "<?php echo $this->webroot; ?>BrmPLSummary/excelDownloadPLSummary";
		document.forms['0'].method = "POST";
		document.forms['0'].submit();

	}

	function clickSearch() {
		document.forms[0].action = "<?php echo $this->webroot; ?>BrmPLSummary/SearchPLSummary";
		document.forms[0].method = "POST";
		document.forms[0].submit();
		loadingPic();
		return true;
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
<div id="load"></div>
<div id="contents">
</div>

<div class="container register_container">
	<h3 class=""><?php echo __("PL Summary"); ?></h3>
	<hr>

	<div class="col-md-12">
		<div class="success" id="success"></div>
		<div class="error" id="error"></div>
	</div>
	<div class="form-group row">
		<div class="col-sm-6">
			<label for="budget_term" class="col-sm-4 col-form-label">
				<?php echo __('予算期間'); ?>
			</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="txt_budget_term" name="txt_budget_term" value="<?php echo $budget_term; ?>" readonly />
			</div>
		</div>
	</div>
	<div class="form-group row">
		<div class="col-sm-6">
			<label for="budget_term" class="col-sm-4 col-form-label">
				<?php echo __($layer_type[SETTING::LAYER_SETTING['topLayer']]); ?>
			</label>
			<div class="col-sm-8">

				<?php if (!empty($headpts)) { ?>
					<select id="headquarter" name="headquarter" class="form-control">
						<option value=",">-- Select Headquarter Name --</option>
						<?php
						foreach ($headpts as $code => $name) :
						?>
							<option value="<?php echo $code . ',' . $name; ?>" <?php if ($head_code == $code) {
																					echo 'selected';
																				} ?>>
								<?php
								echo h($name);
								?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php } else { ?>
					<select id="headquarter" name="headquarter" class="form-control">
						<option value=",">--Select Headquarter Name --</option>
					</select>
				<?php } ?>

			</div>
		</div>
	</div>
	<div class="form-group row">
		<div class="col-sm-6">
			<label for="budget_term" class="col-sm-4 col-form-label">
				<?php echo __($layer_type[SETTING::LAYER_SETTING['middleLayer']]); ?>
			</label>
			<div class="col-sm-8">
				<select id="dept_select" name="dept_select" class="form-control" value="">
					<option value="<?php echo ','; ?>">--Select <?php echo $layer_type[SETTING::LAYER_SETTING['middleLayer']]; ?> Name--</option>
				</select>

			</div>
		</div>
	</div>
	<div class="form-group row">
		<div class="col-sm-6">
			<label for="budget_term" class="col-sm-4 col-form-label">
				<?php echo __($layer_type[SETTING::LAYER_SETTING['bottomLayer']]); ?>
			</label>

			<div class="col-sm-8">
				<select id="layer_code" name="layer_code" class="layer_code form-control">
					<option value="<?php echo ''; ?>">--Select Layer Name --</option>
				</select>
			</div>
		</div>
	</div>
	<br>
	<div class="form-group row" >
		<div class="col-sm-6" style="text-align: end;">
			<input type="button" class="btn btn-success btn_sumisho" id="btn_search" name="btn_search" value="<?php echo __('Search'); ?> " onclick="clickSearch();">
		</div>
	</div>
	<?php if (empty($result_arr)) : ?>
		<div class='row'>
			<p class="no-data"><?php echo __("計算する為のデータがシステムにありません。"); ?></p>
		</div>
	<?php else : ?>
		<div class="form-group row" style="text-align: right;">
			<input type="button" name="btn_excel" id="btn_excel" class="btn btn-success" value="<?php echo __("Excel Download") ?>" onClick="excelDownload();">
		</div>

		<div class='row'>
			<div class="col-lg-12 col-md-12">
				<div class="row table-responsive tbl-wrapper">
					<table class="tbl-pl-summary bold-border" id="tbl_pl">
						<thead class="check_period_table bold-border-btm">
							<tr class="blank-table-row">
								<td colspan="6" class="b-none"></td>
								<?php foreach ($years as $each_year) : ?>
									<td colspan="15" class="show-hide-btn half-total-col-btn hide-col" id="half-total-col"></td>
								<?php endforeach ?>
							</tr>
							<tr class="blank-table-row">

								<th colspan="6" style="background-color:white;" class="b-none talign-left"><?php echo __('（単位：千円）'); ?></th>
								<?php foreach ($years as $each_year) : ?>
									<td colspan="7" class="show-hide-btn first-6m-col-btn hide-col" id="first-6m-col"></td>
									<td colspan="7" class="show-hide-btn last-6m-col-btn hide-col" id="last-6m-col"></td>
									<td class="b-none"></td>
								<?php endforeach ?>
							</tr>
							<tr class="bold-border-top bold-border-lft bold-border-rgt">
								<th rowspan="2" class="row-level-6" style="min-width: 80px"><?php echo __("PLサマリー"); ?></th>
								<th rowspan="2" class="bdl-solid row-level-5" style="min-width: 80px"><?php echo __("勘定科目"); ?></th>
								<th rowspan="2" class="bdl-solid row-level-4" style="min-width: 80px"><?php echo __($layer_type[SETTING::LAYER_SETTING['topLayer']]); ?></th>
								<th rowspan="2" class="bdl-solid row-level-3" style="min-width: 80px"><?php echo __($layer_type[SETTING::LAYER_SETTING['middleLayer']]); ?></th>
								<th rowspan="2" class="bdl-solid row-level-2" style="min-width: 50px"><?php echo __($layer_type[SETTING::LAYER_SETTING['bottomLayer']]); ?></th>
								<th rowspan="2" class="bdl-solid row-level-1" style="min-width: 140px"><?php echo __("取引"); ?></th>
								<?php foreach ($years as $each_year) : ?>
									<th colspan="15" class="bold-border-lft yearly-header" style="min-width: 1000px"> FY <?php echo $each_year; ?></th>
								<?php endforeach ?>
							</tr>
							<tr class="bold-border-lft bold-border-rgt">
								<?php foreach ($years as $each_year) : ?>
									<th class="first-6m-col month bold-border-lft"><?php echo __($Month_12[0]); ?></th>
									<th class="first-6m-col month"><?php echo __($Month_12[1]); ?></th>
									<th class="first-6m-col month"><?php echo __($Month_12[2]); ?></th>
									<th class="first-6m-col month"><?php echo __($Month_12[3]); ?></th>
									<th class="first-6m-col month"><?php echo __($Month_12[4]); ?></th>
									<th class="first-6m-col month"><?php echo __($Month_12[5]); ?></th>
									<th class="half-total-col total first_half"><?php echo __("上期"); ?></th>
									<th class="last-6m-col month"><?php echo __($Month_12[6]); ?></th>
									<th class="last-6m-col month"><?php echo __($Month_12[7]); ?></th>
									<th class="last-6m-col month"><?php echo __($Month_12[8]); ?></th>
									<th class="last-6m-col month"><?php echo __($Month_12[9]); ?></th>
									<th class="last-6m-col month"><?php echo __($Month_12[10]); ?></th>
									<th class="last-6m-col month"><?php echo __($Month_12[11]); ?></th>
									<th class="half-total-col total second_half"><?php echo __("下期"); ?></th>
									<th class="total whole_total bold-border-rgt"><?php echo __("通期"); ?></th>
								<?php endforeach ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($result_arr as $pl_name => $pl_data) : ?>
								<tr class="row-level-6">
									<?php $name_field = (!empty($pl_data['data'])) ? 'name-field' : ''; ?>
									<td colspan="6" class="bb-none <?php echo ($name_field) ?>"><span class="arrow"></span><?php echo $pl_name; ?></td>
									<?php foreach ($years as $each_year) : ?>
										<?php $loopcnt = 0; ?>
										<?php foreach ($pl_data['total'][$each_year] as $col => $monthly_amt) :
											$loopcnt++;
											$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
											$column_name = '';
											if ($loopcnt < 7) {
												$column_name = 'first-6m-col';
											} elseif ($loopcnt == 7 || $loopcnt == 14) {
												$column_name = 'half-total-col';
											} elseif ($loopcnt > 7 && $loopcnt < 14) {
												$column_name = 'last-6m-col';
											}
											$negative = ((round($monthly_amt / 1000)) >= 0) ? '' : 'negative';
										?>

											<td class="<?php echo ($negative . ' ' . $border . ' ' . $column_name);
														echo (' ' . $freeze_arr[$each_year][$col]); ?> number"><?php echo number_format(round($monthly_amt / 1000), 0); ?></td>
										<?php endforeach ?>
										<?php $copy_year0[] = $pl_data['total'][$each_year]; ?>
										<?php if (!isset($pl_data['total'][$each_year])) { ?>
											<?php $loopcnt = 0; ?>
											<?php foreach ($copy_year0['0'] as $col => $monthly_amt) :
												$loopcnt++;
												$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
												$column_name = '';
												if ($loopcnt < 7) {
													$column_name = 'first-6m-col';
												} elseif ($loopcnt == 7 || $loopcnt == 14) {
													$column_name = 'half-total-col';
												} elseif ($loopcnt > 7 && $loopcnt < 14) {
													$column_name = 'last-6m-col';
												}
												$negative = ((round($monthly_amt / 1000)) >= 0) ? '' : 'negative';
											?>

												<td class="<?php echo ($negative . ' ' . $border . ' ' . $column_name);
															echo (' ' . $freeze_arr[$each_year][$col]); ?> number"><?php echo '0'; ?></td>
											<?php endforeach ?>
										<?php } ?>
									<?php endforeach ?>

								</tr>
								<?php foreach ($pl_data['data'] as $sub_name => $sub_acc_data) : ?>
									<tr class="row-level-5">
										<?php $name_field = (!empty($sub_acc_data['sub_data'])) ? 'name-field' : ''; ?>
										<td class="bt-none bb-none col-level-6"></td>
										<td colspan="5" class="bb-none bdl-solid col-level-5 <?php echo ($name_field) ?>"><span class="arrow"></span><?php echo $sub_name; ?></td>
										<?php foreach ($years as $each_year) : ?>
											<?php $loopcnt = 0; ?>
											<?php foreach ($sub_acc_data['sub_total'][$each_year] as $col => $monthly_amt) :
												$loopcnt++;
												$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
												$column_name = '';
												if ($loopcnt < 7) {
													$column_name = 'first-6m-col';
												} elseif ($loopcnt == 7 || $loopcnt == 14) {
													$column_name = 'half-total-col';
												} elseif ($loopcnt > 7 && $loopcnt < 14) {
													$column_name = 'last-6m-col';
												}
												$negative = ((round($monthly_amt / 1000)) >= 0) ? '' : 'negative';
											?>

												<td class="<?php echo ($negative . ' ' . $border . ' ' . $column_name);
															echo (' ' . $freeze_arr[$each_year][$col]); ?> number"><?php echo number_format(round($monthly_amt / 1000), 0); ?></td>
											<?php endforeach ?>

											<?php $copy_year1[] = $sub_acc_data['sub_total'][$each_year]; ?>
											<?php if (!isset($sub_acc_data['sub_total'][$each_year])) { ?>

												<?php $loopcnt = 0; ?>
												<?php foreach ($copy_year1['0'] as $col => $monthly_amt) :
													$loopcnt++;
													$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
													$column_name = '';
													if ($loopcnt < 7) {
														$column_name = 'first-6m-col';
													} elseif ($loopcnt == 7 || $loopcnt == 14) {
														$column_name = 'half-total-col';
													} elseif ($loopcnt > 7 && $loopcnt < 14) {
														$column_name = 'last-6m-col';
													}
													$negative = ((round($monthly_amt / 1000)) >= 0) ? '' : 'negative';
												?>

													<td class="<?php echo ($negative . ' ' . $border . ' ' . $column_name);
																echo (' ' . $freeze_arr[$each_year][$col]); ?> number"><?php echo '0'; ?></td>
												<?php endforeach ?>

											<?php } ?>
										<?php endforeach ?>
									</tr>
									<?php foreach ($sub_acc_data['sub_data'] as $head_name => $head_data) : ?>
										<tr class="row-level-4">
											<?php $name_field = (!empty($head_data['hlayer_data'])) ? 'name-field' : ''; ?>
											<td class="bt-none bb-none col-level-6"></td>
											<td class="bt-none bb-none bdl-solid col-level-5"></td>
											<td colspan="4" class="bb-none bdl-solid col-level-4 <?php echo ($name_field);
																									echo (' ' . $freeze_arr[$each_year][$col]); ?>"><span class="arrow"></span><?php echo $head_name; ?></td>
											<?php foreach ($years as $each_year) : ?>
												<?php $loopcnt = 0; ?>
												<?php foreach ($head_data['hlayer_total'][$each_year] as $col => $monthly_amt) :
													$loopcnt++;
													$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
													$column_name = '';
													if ($loopcnt < 7) {
														$column_name = 'first-6m-col';
													} elseif ($loopcnt == 7 || $loopcnt == 14) {
														$column_name = 'half-total-col';
													} elseif ($loopcnt > 7 && $loopcnt < 14) {
														$column_name = 'last-6m-col';
													}
													$negative = ((round($monthly_amt / 1000)) >= 0) ? '' : 'negative';
												?>
													<td class="<?php echo ($negative . ' ' . $border . ' ' . $column_name);
																echo (' ' . $freeze_arr[$each_year][$col]); ?> number"><?php echo number_format(round($monthly_amt / 1000), 0); ?></td>
												<?php endforeach ?>

												<?php $copy_year2[] = $head_data['hlayer_total'][$each_year]; ?>
												<?php if (!isset($head_data['hlayer_total'][$each_year])) { ?>

													<?php $loopcnt = 0; ?>
													<?php foreach ($copy_year2['0'] as $col => $monthly_amt) :
														$loopcnt++;
														$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
														$column_name = '';
														if ($loopcnt < 7) {
															$column_name = 'first-6m-col';
														} elseif ($loopcnt == 7 || $loopcnt == 14) {
															$column_name = 'half-total-col';
														} elseif ($loopcnt > 7 && $loopcnt < 14) {
															$column_name = 'last-6m-col';
														}
														$negative = ((round($monthly_amt / 1000)) >= 0) ? '' : 'negative';
													?>
														<td class="<?php echo ($negative . ' ' . $border . ' ' . $column_name);
																	echo (' ' . $freeze_arr[$each_year][$col]); ?> number"><?php echo '0'; ?></td>
													<?php endforeach ?>
												<?php } ?>
											<?php endforeach ?>
										</tr>
										<?php foreach ($head_data['hlayer_data'] as $dept_name => $dept_data) : ?>
											<tr class="row-level-3">
												<?php $name_field = (!empty($dept_data['dlayer_data'])) ? 'name-field' : ''; ?>
												<td class="bt-none col-level-6 bb-none"></td>
												<td class="bt-none col-level-5 bb-none bdl-solid"></td>
												<td class="bt-none col-level-4 bb-none bdl-solid"></td>

												<td colspan="3" class="bb-none bdl-solid col-level-3 <?php echo ($name_field) ?>"><span class="arrow"></span><?php echo $dept_name; ?></td>
												<?php foreach ($years as $each_year) : ?>
													<?php $loopcnt = 0; ?>
													<?php foreach ($dept_data['dlayer_total'][$each_year] as $col => $monthly_amt) :
														$loopcnt++;
														$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
														$column_name = '';
														if ($loopcnt < 7) {
															$column_name = 'first-6m-col';
														} elseif ($loopcnt == 7 || $loopcnt == 14) {
															$column_name = 'half-total-col';
														} elseif ($loopcnt > 7 && $loopcnt < 14) {
															$column_name = 'last-6m-col';
														}
														$negative = ((round($monthly_amt / 1000)) >= 0) ? '' : 'negative';
													?>
														<td class="<?php echo ($negative . ' ' . $border . ' ' . $column_name);
																	echo (' ' . $freeze_arr[$each_year][$col]); ?> number"><?php echo number_format(round($monthly_amt / 1000), 0); ?></td>
													<?php endforeach ?>

													<?php $copy_year3[] = $dept_data['dlayer_total'][$each_year]; ?>
													<?php if (!isset($dept_data['dlayer_total'][$each_year])) { ?>
														<?php foreach ($copy_year3['0'] as $col => $monthly_amt) :
															$loopcnt++;
															$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
															$column_name = '';
															if ($loopcnt < 7) {
																$column_name = 'first-6m-col';
															} elseif ($loopcnt == 7 || $loopcnt == 14) {
																$column_name = 'half-total-col';
															} elseif ($loopcnt > 7 && $loopcnt < 14) {
																$column_name = 'last-6m-col';
															}
															$negative = ((round($monthly_amt / 1000)) >= 0) ? '' : 'negative';
														?>
															<td class="<?php echo ($negative . ' ' . $border . ' ' . $column_name);
																		echo (' ' . $freeze_arr[$each_year][$col]); ?> number"><?php echo '0'; ?></td>
														<?php endforeach ?>
													<?php } ?>
												<?php endforeach ?>
											</tr>
											<?php foreach ($dept_data['dlayer_data'] as $ba_name => $ba_data) : ?>
												<tr class="row-level-2">
													<?php $name_field = (!empty($ba_data['layer_data'])) ? 'name-field' : ''; ?>
													<td class="bt-none bb-none col-level-6"></td>
													<td class="bt-none bb-none col-level-5 bdl-solid"></td>
													<td class="bt-none bb-none col-level-4 bdl-solid"></td>
													<td class="bt-none bb-none col-level-3 bdl-solid"></td>

													<td colspan="2" class="bb-none bdl-solid col-level-2 <?php echo ($name_field) ?>"><span class="arrow"></span><?php echo $ba_name; ?></td>
													<?php foreach ($years as $each_year) : ?>
														<?php $loopcnt = 0; ?>
														<?php foreach ($ba_data['layer_total'][$each_year] as $col => $monthly_amt) :
															$loopcnt++;
															$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
															$column_name = '';
															if ($loopcnt < 7) {
																$column_name = 'first-6m-col';
															} elseif ($loopcnt == 7 || $loopcnt == 14) {
																$column_name = 'half-total-col';
															} elseif ($loopcnt > 7 && $loopcnt < 14) {
																$column_name = 'last-6m-col';
															}
															$negative = ((round($monthly_amt / 1000)) >= 0) ? '' : 'negative';
														?>
															<td class="<?php echo ($negative . ' ' . $border . ' ' . $column_name);
																		echo (' ' . $freeze_arr[$each_year][$col]); ?> number"><?php echo number_format(round($monthly_amt / 1000), 0); ?></td>
														<?php endforeach ?>

														<?php $copy_year4[] = $ba_data['layer_total'][$each_year]; ?>
														<?php if (!isset($ba_data['layer_total'][$each_year])) { ?>
															<?php $loopcnt = 0; ?>
															<?php foreach ($copy_year4['0'] as $col => $monthly_amt) :
																$loopcnt++;
																$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
																$column_name = '';
																if ($loopcnt < 7) {
																	$column_name = 'first-6m-col';
																} elseif ($loopcnt == 7 || $loopcnt == 14) {
																	$column_name = 'half-total-col';
																} elseif ($loopcnt > 7 && $loopcnt < 14) {
																	$column_name = 'last-6m-col';
																}
																$negative = ((round($monthly_amt / 1000)) >= 0) ? '' : 'negative';
															?>
																<td class="<?php echo ($negative . ' ' . $border . ' ' . $column_name);
																			echo (' ' . $freeze_arr[$each_year][$col]); ?> number"><?php echo '0'; ?></td>
															<?php endforeach ?>
														<?php } ?>
													<?php endforeach ?>
												</tr>
												<?php if (count($ba_data['layer_data']) > 1) : ?>
													<?php foreach ($ba_data['layer_data'] as $index_name => $index_data) : ?>
														<tr class="row-level-1">
															<td class="bt-none bb-none  col-level-6"></td>
															<td class="bt-none bb-none  col-level-5 bdl-solid"></td>
															<td class="bt-none bb-none  col-level-4 bdl-solid"></td>
															<td class="bt-none bb-none  col-level-3 bdl-solid"></td>
															<td class="bt-none bb-none  col-level-2 bdl-solid"></td>

															<td class="bb-none bdl-solid  col-level-1"><?php echo $index_name; ?></td>

															<?php foreach ($years as $year) { ?>

																<?php $loopcnt = 0; ?>

																<?php foreach ($index_data[$year] as $col => $monthly_amt) :
																	$loopcnt++;
																	$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
																	$column_name = '';
																	if ($loopcnt < 7) {
																		$column_name = 'first-6m-col';
																	} elseif ($loopcnt == 7 || $loopcnt == 14) {
																		$column_name = 'half-total-col';
																	} elseif ($loopcnt > 7 && $loopcnt < 14) {
																		$column_name = 'last-6m-col';
																	}
																	$negative = ((round($monthly_amt / 1000)) >= 0) ? '' : 'negative';
																?>
																	<td class="<?php echo ($negative . ' ' . $border . ' ' . $column_name);
																				echo (' ' . $freeze_arr[$year][$col]); ?> number"><?php echo number_format(round($monthly_amt / 1000), 0); ?></td>
																<?php endforeach ?>

																<?php $copy_year[] = $index_data[$year]; ?>

																<?php if (!isset($index_data[$year])) {
																	$loopcnt = 0;

																	foreach ($copy_year['0'] as $col => $monthly_amt) :
																		$monthly_amt = 0;
																		$loopcnt++;
																		$border = ($col == 'month_1_amt') ? 'bold-border-lft' : '';
																		$column_name = '';
																		if ($loopcnt < 7) {
																			$column_name = 'first-6m-col';
																		} elseif ($loopcnt == 7 || $loopcnt == 14) {
																			$column_name = 'half-total-col';
																		} elseif ($loopcnt > 7 && $loopcnt < 14) {
																			$column_name = 'last-6m-col';
																		}
																?>
																		<td class="<?php echo ($border . ' ' . $column_name);
																					echo (' ' . $freeze_arr[$year][$col]); ?> number"><?php echo '0'; ?></td>

																	<?php endforeach ?>

																<?php } ?>

															<?php } ?>

														</tr>
													<?php endforeach ?>
												<?php endif ?>
											<?php endforeach ?>
										<?php endforeach ?>
									<?php endforeach ?>
								<?php endforeach ?>

							<?php endforeach ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
<div class='hidden'>
	<input type="hidden" name="dept_id" id="dept_id" value="<?php echo ($dlayer_code); ?>">
</div>
<br>
<br><br>
<?php
echo $this->Form->end();
?>