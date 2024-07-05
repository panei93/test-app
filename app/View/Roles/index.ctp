<style type="text/css">
	.tbl-pl-summary {
		table-layout: fixed;
		margin-top: 20px;
		width:100%;
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
		border-top: 2px solid #444 !important;
	}

	td.bold-border-lft,
	th.bold-border-lft {
		border-left: 2px solid #444 !important;
	}

	tr.bold-border-lft {
		border-left: 3px solid #444 !important;
	}

	.bold-border-rgt {
		border-right: 3px solid #444 !important;
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
	.form-row {
		/* display: flex; */
		flex-direction: column;
	}
	/* .save-row {
		margin-bottom: 2rem;
		height: 10vh !important;
	} */
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
		padding: 10px 15px;
		/* font-size: 1.2rem !important; */

	}
	thead {
		position: sticky;
		top: 0;
		z-index: 1000;
	}
	.table-container {
		position: sticky;
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
	.amsify-selection-area{
		width: 60%!important;
		display: inline-block!important;
	}
	.amsify-selection-label {
		width: 100% !important;
	}

	.register_form .btn-save-wpr {
		width: 80.5%;
		display: flex;
		justify-content: flex-end;

	}
	.col-md-8 {
		max-width: 58.666667%;
	}
	.update-btn {
		float: right;
	}

	.form-control.year-picker {
		padding: 0 !important;
		border: none !important;
		width: 60%;
	}

	.form-control .form-input {
		width: 100%;
	}
	.lbl_txt{
		font-weight: 300;
	}
	.add_permission {
		cursor:pointer;
	}

	@media (max-width:768px) {
		thead {
			position: sticky;
			top: 0;
			z-index: 1000;
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
	.register_form .btn {
		margin-left: 0px;
	}
	.per_fieldset{
		background-color: #eeeeee;
		width: 60% !important;
		margin: auto;
    	margin-top: -20px
	}
	fieldset {
		min-width: 0%;
	}

	.per_legend {
		background-color: darkcyan;
		color: white;
		padding: 5px 10px;
	}
	.permission_list {
		margin-left: 154px;
		margin-top: -17px;
	}
	.amsify-selection-list {
		width: 57%!important;
	}
	.ul-style {
		list-style-image: linear-gradient(to left bottom, #81c6cc, cyan);
	}
	#permission_1,#permission_2 {
		margin-right: 5px;
	}
	.list_info{
		text-transform: capitalize;
	}
	.select-box {
		width: 17rem;
		padding-left: 0.3rem;
	}
	@media (max-width: 1080px) {
		.select-box {
			width: 15rem;
		}
	}
	.disabled_cross{
		color: #ccc !important;
		cursor: not-allowed !important;
	}
</style>
<script>
	$(document).ready(function() {
		//change page name dropdown value according to phase
		var pageNames = [];
		var layer_data = JSON.parse('<?php echo json_encode($layer_data); ?>');
		var s_role_name = $("#s_role_name").val();
		var s_menu_name = $("#s_menu_name").val();
		var s_page_name = $("#s_page_name").val();
		var role_id = $('select[name="s_role"] :selected').val();
		var menu_name = $('select[name="s_menu"] :selected').val();
		var page_name = $('select[name="s_page"] :selected').val();
		var query_count = <?php echo $query_count; ?>;

		if(query_count > 0){
			if(role_id.length == 0 ) {
				$('#s_menu').empty();
				$('#s_menu').append("<option value =''>" + '----- Select Menu Name -----' + "</option>");
				$('#s_page').empty();
				$('#s_page').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
			} 
			if(role_id.length > 0 && menu_name.length > 0) {
				$('.row-level-2 > .remove').empty();
				$('.row-level-2 > .remove').append('<a style="opacity: 0.4"><i class="fa-regular fa-trash-can" title="<?php echo __('削除'); ?>"></i></a>');
			}
			if(role_id.length > 0 && menu_name.length > 0 && page_name.length > 0) {
				$('.row-level-2 > .remove').empty();
				$('.row-level-1 > .remove').empty();
				$('.row-level-2 > .remove').append('<a style="opacity: 0.4"><i class="fa-regular fa-trash-can" title="<?php echo __('削除'); ?>"></i></a>');
				$('.row-level-1 > .remove').append('<a style="opacity: 0.4"><i class="fa-regular fa-trash-can" title="<?php echo __('削除'); ?>"></i></a>');
			}
		}
		
		$('#phase').change(function(evt, param1) {
			var phase_id = $('#phase').val();
			if(phase_id == '') {
				$('input[type=radio][name=read_layer]').remove();
				$('#read_action').empty();
				$.each(layer_data, function(i, value) {
					$('#read_action').append("<div class='col-md-6'><label class ='lbl_txt' for='read_layer_"+i+"' ><input class='read_limit' type=radio id= 'read_layer_" + i + "' name='read_layer' value='" + i + "'>" + value+"</div>");
				});
			}else if(phase_id == 'all'){
				$('#page_name').empty();
				$('#page_name').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
				$('#page_name').append("<option value ='all'>" + '<?php echo __("全て"); ?>' + "</option>");
				$.ajax({
					type: "POST",
					url: "<?php echo $this->webroot; ?>Roles/getLimitaion",
					data: {
						phase_id: phase_id,
					},
					dataType: "json",
					success: function(data) {

						$('input[type=radio][name=read_layer]').remove();
						$('#read_action').empty();
						$.each(data, function(i, value) {
							$('#read_action').append("<div class='col-md-6'><label class ='lbl_txt' for='read_layer_"+i+"' ><input class='read_limit' type=radio id= 'read_layer_" + i + "' name='read_layer' value='" + i + "'>" + value+"</div>");
						});
					}
				});
			}else{
				$.ajax({
					type: "POST",
					url: "<?php echo $this->webroot; ?>Roles/getPageName",
					data: {
						phase_id: phase_id,
						name: ""
					},
					dataType: "json",
					success: function(data) {
						pageNames = data[0];
						if (data[0]) {
							$('#page_name').empty();
							$('#page_name').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
							$('#page_name').append("<option value ='all'>" + '<?php echo __("全て"); ?>' + "</option>");
							$.each(data[0], function(i, value) {
								if (typeof param1 !== "undefined") {
									if (param1.page == value) $('#page_name').append("<option value ='" + value + "' selected = 'selected'>" + value + "</option>");
									else $('#page_name').append("<option value ='" + value + "'>" + value + "</option>");
								} else $('#page_name').append("<option value ='" + value + "'>" + value + "</option>");
							});
						} else {
							$('#page_name').empty();
							$('#page_name').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
						}
						if (data[1]) { //for read_action show/hide
							$('input[type=radio][name=read_layer]').remove();
							$('#read_action').empty();
							$.each(data[1], function(i, value) {
								if (typeof param1 !== "undefined") {
									if (param1.level == i) $('#read_action').append("<div class='col-md-6'><label class ='lbl_txt' for='read_layer_"+i+"' ><input class='read_limit' type='radio' id= 'read_layer_" + i + "' name='read_layer' value='" + i + "' checked='checked' onclick='hello()'>" + value+"</div>");
									else $('#read_action').append("<div class='col-md-6'><label class ='lbl_txt' for='read_layer_"+i+"' ><input type='radio' id= 'read_layer_" + i + "' name='read_layer' value='" + i + "'>" + value+"</div>");
								} else $('#read_action').append("<div class='col-md-6'><label class ='lbl_txt' for='read_layer_"+i+"' ><input class='read_limit' type='radio' id= 'read_layer_" + i + "' name='read_layer' value='" + i + "'>" + value+"</div>");
							});
						} 
					}
				});
			}	
		});
		//change button type dropdown value according to pagename
		var flag_button_type = 1;
		$('#pg_name').on('change', '#page_name', function function_name(evt, param) {
			$('#save_btn_show').css('textTransform', 'capitalize');
			var phase_id = $('#phase').val();
			var page_name = $('#page_name').val();
			$("#read_action input:radio").attr("checked", false);
			if (typeof param !== "undefined") page_name = param.page;
			var button_type = <?php echo json_encode($button_type); ?>;
			var layer_data = <?php echo json_encode($layer_data); ?>;
			var language = '<?php echo $language; ?>';
			if(phase_id == 'all' && page_name == 'all'){
				flag_button_type = 0;
				$('#button_type').empty();
				$('#button_type').append("<option value =''>" + '----- Select Action Type -----' + "</option>");
				$.each(button_type, function(i, value) {
					value = value.replace(/ /g, '');
					value1 = value.replace(/_/g, ' ');
					value = value.replace(/Read/g, 'Index');
					if (language == 'eng'){
						// if(value1 == 'Index')
						// value1 = 'Read';
						if(value1 == 'Reject')
						value1 = 'Revert';
						if(value1 == 'Review')
						value1 = 'Check';
					}else{
						if(value1 == '拒否')
						value1 = '差し戻し';
						if(value1 == 'レビュー')
						value1 = '確認欄';
					}
					//add value to Button Types for Save and Edit
					$('#button_type').append("<option value ='" + value + "'>" + value1 + "</option>");
				});
				if (typeof param == "undefined") $('#button_type').amsifySelect();
			}else{
				if(page_name == 'all') page_Names = pageNames;
				else page_Names = page_name;
				var language = '<?php echo $language; ?>';
				
				$.ajax({
					type: "POST",
					url: "<?php echo $this->webroot; ?>Roles/getButtonType",
					data: {
						page_name: page_Names
					},
					dataType: "json",
					beforeSend: function() {
					
					},
					success: function(data) {
						if (Object.keys(data).length != 0) {
							flag_button_type = 0;
							var bType = [];
							j = 0
							$.each(data, function(i, value) {
								if(j == 0) bType.push(value);
								else {
									if($.inArray(value, bType) == -1) bType.push(value);
								}
								j++;
							});
							$('#button_type').empty();
							$('#button_type').append("<option value =''>" + '----- Select Action Type -----' + "</option>");
							
							if(page_name == 'all') {
								data = bType;
								$.each(data, function(i, value) {
									
									if (value != null && value.indexOf('_') != -1) value1 = value.replace(/_/g, ' ');
									else value1 = value;
									//add value to Button Types for Save and Edit
									value = phase_id+'_'+value;
									if (language == 'eng'){
										if(value1 == 'index')
										value1 = 'Read';
										if(value1 == 'reject')
										value1 = 'Revert';
										if(value1 == 'review')
										value1 = 'Check';
									}else{
										if(value1 == '拒否')
										value1 = '差し戻し';
										if(value1 == 'レビュー')
										value1 = '確認欄';
									}
									$('#button_type').append("<option value ='" + value + "'>" + value1 + "</option>");
								});
							}else{
								$.each(data, function(i, value) {
									value1 = value.replace(/_/g, ' ');
									if (language == 'eng'){
										if(value1 == 'index')
										value1 = 'Read';
										if(value1 == 'reject')
										value1 = 'Revert';
										if(value1 == 'review')
										value1 = 'Check';
									}else{
										if(value1 == '拒否')
										value1 = '差し戻し';
										if(value1 == 'レビュー')
										value1 = '確認欄';
									}
									//add value to Button Types for Save and Edit
									$('#button_type').append("<option value ='" + i + "'>" + value1 + "</option>");
								});
							}
							if (typeof param == "undefined") $('#button_type').amsifySelect();
						} else {
							$('#button_type').empty();
							$('#save_btn_show .amsify-list .amsify-list-item').removeClass('active');
							$("#save_btn_show .amsify-label").text('-----Select-----');
							$("#save_btn_show .amsify-list").empty();
							$('#button_type').append("<option value =''>" + '----- Select Button Type -----' + "</option>");
							flag_button_type = 1;
						}
						$('#overlay').hide();
					}
				});
			}
			
		});

		//change menu name dropdown value according to phase in search form
		$('#s_role').change(function(event, param) {
			$('#s_menu').empty();
			$('#s_menu').append("<option value =''>" + '----- Select Menu Name -----' + "</option>");
			$('#s_page').empty();
			$('#s_page').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
			var role_id = $('select[name="s_role"] :selected').val();
			$.ajax({
				type: "POST",
				url: "<?php echo $this->webroot; ?>Roles/getMenuName",
				data: {
					role_id: role_id,
					name: "Search"
				},
				dataType: "json",
				success: function(data) {
					if (data) {
						$('#s_menu').empty();
						$('#s_menu').append("<option value =''>" + '----- Select Menu Name -----' + "</option>");
						$.each(data, function(i, value) {
							if (param == value) $('#s_menu').append("<option value ='" + value + "' selected = 'selected'>" + value + "</option>");
							else $('#s_menu').append("<option value ='" + value + "'>" + value + "</option>");
						});
					} else {
						$('#s_menu').empty();
						$('#s_menu').append("<option value =''>" + '----- Select Menu Name -----' + "</option>");
					}
					if(data.length == 0) {
						$('#s_page').empty();
						$('#s_page').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
					}
				}
			});
		});
		//change page name dropdown value according to phase in search form
		$('#s_menu').change(function(event, param) {
			$('#s_page').empty();
			$('#s_page').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
			var role_id = $('select[name="s_role"] :selected').val();
			var menu_name = $('select[name="s_menu"] :selected').val();
			$.ajax({
				type: "POST",
				url: "<?php echo $this->webroot; ?>Roles/getPageName",
				data: {
					role_id: role_id,
					menu_name: menu_name,
					name : 'Search'
				},
				dataType: "json",
				success: function(data) {
					if (data) {
						$('#s_page').empty();
						$('#s_page').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
						$.each(data, function(i, value) {
							if (param == value) {
								$('#s_page').append("<option value ='" + value + "' selected = 'selected'>" + value + "</option>");
							}
							else $('#s_page').append("<option value ='" + value + "'>" + value + "</option>");
						});
					} else {
						$('#s_page').empty();
						$('#s_page').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
					}
				}
			});
		});
		$("#s_page").change(function(event, param){
			var page_name = $('select[name="s_page"] :selected').val();
			// $("#s_page_name").val(page_name);
		});
		$("#button_type").change(function(event, param){
			$('input[name=layer_name]').attr("disabled",false);
		});

		//get role , menu name and page name from search
		
		// var s_role = $("#s_role").val(s_role_name);
		// $("#s_role").trigger("change", s_menu_name);
		// $("#s_menu").trigger("change", s_page_name);


		//when click clear receiver button in modal box
		$('.btn_clear,#closePop').on('click', function function_name(argument) {
			$("#phase").find('option:selected').removeAttr("selected");
			$("#pg_name").find('option:selected').removeAttr("selected");
			$('#page_name').empty();
			$('#page_name').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
			$("#read_action input:radio").attr("checked", false);
			$("#limitation input:radio").attr("checked", false);
			$("#save_btn_show").hide();
			$("#limitation").hide();
			$('#save_btn_show .amsify-list .amsify-list-item').removeClass('active');
			$("#save_btn_show .amsify-label").text('-----Select-----');
			$("#save_btn_show .amsify-list").empty();			

		});
		
		$('#permission_1').click(function(e) {
			$("#error").empty();
			$("#success").empty();
			$('#per_list').hide();
		});
		$('#permission_2').click(function(e) {
			$("#error").empty();
			$("#success").empty();
			$('#per_list').show();
		});
		$('.btn_save').click(function(e) {
			e.preventDefault();
			$("#hd_phase").val('');
			$("#hd_page_name").val('');
				
			let chk           = true;
			let button_arr    = [];
			let button_arr_val= [];
			let phase         = $("#phase").val();
			let pg_name       = $("#page_name").val();
			let layer_val     = $("input[name='layer_name']:checked").val();
			let read_layer     = $("input[name='read_layer']:checked").val();
			if($("#read_limit").val() > read_layer || $("#read_limit").val() == ''){
				$("#read_limit").val(read_layer);
			}
			let layer_name    = $("input[name='layer_name']:checked").parent('label').text();
			let read_layer_name    = $("input[name='read_layer']:checked").parent('label').text();
			if(phase != "" && pg_name != ""){
				$("#hd_phase").val(phase);
				$("#hd_page_name").val(pg_name);
			}
			$("#errorPermission").empty("");

			if(phase != 'all' && pg_name != 'all'){
				let read_id;
				$.ajax({
					type: "POST", 
					url: "<?php echo $this->webroot; ?>Roles/getReadMenuId",
					dataType: "json", 
					data: {phase : phase, page_name : pg_name},
					async: false,
					success: function(data){
						read_id = data.Menu.id;
					}
				});
				button_arr.push(read_id);
				let language    = '<?php echo $language;?>';
				if(language == 'eng') button_arr_val.push("Read");
				else button_arr_val.push("画面表示");
			}else{
				// button_arr.push(phase+"_"+"<?php echo strtolower(__('画面表示'));?>");
				// button_arr_val.push(" "+"<?php echo strtolower(__('画面表示'));?>");
				button_arr.push("all_index");
				button_arr_val.push("<?php echo __('画面表示');?>");
			}
			
			
			$("#save_btn_show .amsify-list .active input").each(function(index) {
				button_arr.push($(this).val());
				button_arr_val.push($(this).parent().text());			
			});
			if (!checkNullOrBlank(phase)) {
				$("#errorPermission").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("メニュー名"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(pg_name)) {
				$("#errorPermission").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("ページ名"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			// if (!checkNullOrBlank(button_arr)) {
			// 	$("#errorPermission").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("処理"); ?>']) + "</div>");
			// 	$("html, body").animate({
			// 		scrollTop: 30
			// 	}, "fast");
			// 	chk = false;
			// }
			if (!checkNullOrBlank(read_layer)) {
				$("#errorPermission").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("読み取りアクション"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (button_arr.length > 1 && !checkNullOrBlank(layer_val)) {
				$("#errorPermission").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("アクション"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (chk) {
				var appendText = "";var buttonText = "";var temp_array = [];var temp_value = [];
				var pageName = $.trim(pg_name.split(" ").join(""));
				var language = '<?php echo $language; ?>';
				if (language == 'eng') 
					var phase_name = $.trim(phase.split(" ").join("")).replace(/[_\W]+/g, "");
				else
					var phase_name = phase;
				var class_name = "ul ."+pageName+" li";
				if ($('ul').hasClass(phase_name) && $("ul").hasClass(pageName) ) {
					$(class_name).each( function() {
						var arr        = $.trim($(this).text()).split(' ');
						var arr_value  = arr[arr.length-1].trim();
						// alert(arr_value);
						// var arr_value  = "Read";
						var temp       = $(this).attr("class");
						temp_array.push(arr_value);
						temp_value.push(temp);
					});
				}
				$.each(button_arr_val, function(i, value) {
					value = $.trim(value);
					let delete_flag = "" ;
					button_arr[i] = button_arr[i].replace(/ /g, '_');
					var function_name = (i==0)?"ActionDelete('"+button_arr[i]+read_layer+"',"+"\'"+phase_name+"\'"+","+"\'"+pageName+"\')":"ActionDelete('"+button_arr[i]+layer_val+"',"+"\'"+phase_name+"\'"+","+"\'"+pageName+"\')";
					if(value == "Read" || value == "画面表示"){
						delete_flag = "disabled_cross" ;
						function_name = "";
					}
					if(temp_array.length > 0) {
						if(jQuery.inArray(value, temp_array) == -1 ) {
							if(i == 0)
							buttonText += "<li id='"+button_arr[i]+read_layer+"' class='"+button_arr[i]+"_"+read_layer+"'>"+ read_layer_name+" の "+value+" &nbsp;<a class='delete_action' href='#' onclick="+function_name+"><i class='glyphicon glyphicon glyphicon-remove "+delete_flag+"' style='color:red;'></i></a></li>";
							else
							buttonText += "<li id='"+button_arr[i]+layer_val+"' class='"+button_arr[i]+"_"+layer_val+"'>"+ layer_name+" の "+value+" &nbsp;<a class='delete_action' href='#' onclick="+function_name+"><i class='glyphicon glyphicon glyphicon-remove' style='color:red;'></i></a></li>";
							
						} else {
							var index_value = '.'+temp_value[temp_array.indexOf(value)];
							$(index_value).remove();
							if(i == 0)
							buttonText += "<li id='"+button_arr[i]+read_layer+"' class='"+button_arr[i]+"_"+read_layer+"'>"+ read_layer_name+" の "+value+" &nbsp;<a class='delete_action' href='#' onclick="+function_name+"><i class='glyphicon glyphicon glyphicon-remove "+delete_flag+"' style='color:red;'></i></a></li>";
							else
							buttonText += "<li id='"+button_arr[i]+layer_val+"' class='"+button_arr[i]+"_"+layer_val+"'>"+ layer_name+" の "+value+" &nbsp;<a class='delete_action' href='#' onclick="+function_name+"><i class='glyphicon glyphicon glyphicon-remove' style='color:red;'></i></a></li>";
						}
					} else {
						if(i == 0)
						buttonText += "<li id='"+button_arr[i]+read_layer+"' class='"+button_arr[i]+"_"+read_layer+"'>"+ read_layer_name+" の "+value+" &nbsp;<a class='delete_action' href='#' onclick="+function_name+"><i class='glyphicon glyphicon glyphicon-remove "+delete_flag+"' style='color:red;'></i></a></li>";
						else
						buttonText += "<li id='"+button_arr[i]+layer_val+"' class='"+button_arr[i]+"_"+layer_val+"'>"+ layer_name+" の "+value+" &nbsp;<a class='delete_action' href='#' onclick="+function_name+"><i class='glyphicon glyphicon glyphicon-remove' style='color:red;'></i></a></li>";
					} 
				});
				

				if($('li').hasClass('phase_all') && $('li').hasClass('page_all') || (pageName == 'all' && phase_name == 'all')) $(".list_info").empty("");
				if($('ul').hasClass(phase_name)) {
					if($('li').hasClass('page_all') || pageName == 'all') {
						$('ul.'+phase_name).empty("");
					}
				}
				//search pageName to append text
				if($('ul').hasClass(phase_name) && $('ul').hasClass(pageName)) {

					appendText += buttonText;
					var pageName    = "."+pageName;
					$(pageName).append(appendText);
				} else {
					
					let language    = '<?php echo $language;?>';
					if($('ul').hasClass(phase_name)){
						if (language == 'eng') if(pg_name == 'all') pg_name = pg_name+' Page';
						else if(pg_name == 'all') pg_name = '全ページ';
						
						phase_name  = '.'+phase_name;
						appendText += '<b><li class="page_'+pageName+'">'+pg_name+'</li></b><ul class="'+pageName+'">';
						appendText += buttonText;
						$(phase_name).append(appendText);
					} else {
						if (language == 'eng') {
							if(pg_name == 'all') pg_name = pg_name+' Page';
							if(phase == 'all') phase = phase+' Menu';
						}else {
							if(pg_name == 'all') pg_name = '全ページ';
							if(phase == 'all') phase = '全メニュー';
						}
						appendText += '<ul class="ul-style"><b><li class="phase_'+phase_name+'">'+phase+'</li></b><ul class="'+phase_name+'"><b><li class="page_'+pageName+'">'+pg_name+'</li></b><ul class="'+pageName+'">';
						appendText += buttonText+"</ul></ul></ul>";
						$('.list_info').append(appendText);
					}
				}
				//clear selection
				$("#phase").find('option:selected').removeAttr("selected");
				$("#pg_name").find('option:selected').removeAttr("selected");
				$('#page_name').empty();
				$('#page_name').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
				$("#read_action input:radio").attr("checked", false);
				$("#limitation input:radio").attr("checked", false);
				$("#save_btn_show").hide();
				$("#limitation").hide();
				// $('#save_btn_show .amsify-list .amsify-list-item').removeClass('active');
				// $("#save_btn_show .amsify-label").text('-----Select-----');
				// $("#save_btn_show .amsify-list").empty();	
			} else {
				return false;
			}
		});

		$('#button_type').amsifySelect();	
		$('#save_btn_show').on('click', '.amsify-select-clear', function(event) {
			event.preventDefault();
		});
		$(".add_permission").click(function() {
			$("#error").empty();
			$("#success").empty();
			$("#errorPermission").empty();
			$("#phase option").removeAttr("selected");
			$("#page_name option").removeAttr("selected");
			$("#button_type option").removeAttr('selected');
			$("input[name=layer_name]:checked").removeAttr("checked");
			if($(".amsify-selection-area .amsify-selection-list .amsify-list .amsify-list-item ").hasClass("active")){
				$(".amsify-selection-area .amsify-selection-label .amsify-label").empty();
				$(".amsify-selection-area .amsify-selection-label .amsify-label").append("----- Select -----");
				$(".amsify-selection-area .amsify-selection-list .amsify-list .amsify-list-item ").removeClass("active");
			}
			$("#myModal").modal({
				backdrop: 'static',
				keyboard: false
			});
			$("#myModal").modal('show');
		});
		$("#save_btn_show").hide();
		$("#limitation").hide();
		$("#read_action").on("click", ".read_limit", function() {//check box
			let menu_name = $('#phase option:selected').attr('value');
			let menu_id = $('#phase option:selected').attr('class');
			let page_name = $('#page_name option:selected').val();
			let read_limit,count_per;
			let start_limit = $(".read_limit:checked").val();
			let layer_data = <?php echo json_encode($layer_data); ?>;
			if(tmp_role_id != "" && tmp_role_id != undefined){//check edit mode or not
				$.ajax({
						type: "POST", 
						url: "<?php echo $this->webroot; ?>Roles/getPermissionReadLimit",
						dataType: "json", 
						data: {
							role_id : tmp_role_id,
							menu_id : menu_id,
							page_name : page_name
						},
						async: false,
						success: function(data){
							count_per = 2;
							if(menu_name == 'all'){
								read_limit = Math.min(...(Object.values(data).map(Number)));
							}else if(menu_name != 'all' && page_name == 'all'){
								read_limit = Math.min(...(Object.values(data).map(Number)));
							}else{
								read_limit = data[page_name];
								count_per = data['count'];
							}
						}
				});
			}
			
			if(((count_per == 1)? true : start_limit <= read_limit	)|| read_limit == undefined){
				$("#errorPermission").empty();
				var end_limit = null;
				var id_orig_diz;
				$.ajax({
					type: "POST", 
					url: "<?php echo $this->webroot; ?>Roles/getLimitaion",
					dataType: "json", 
					async: false,
					success: function(data){
						end_limit = Object.keys(data).pop();
					}
				});
				
				$("#save_btn_show").show();
				$("#limitation").show();			
	
				html = "";
				for(order = start_limit; order <= end_limit; order++){
						html += "<div class='col-md-6'>";
						 html += "<label for='layer_ "+ order+"' style='font-weight:300;'>";
						 html += "<input type='radio' name='layer_name' id='layer_"+order+"' value='"+order+"'/>";
						 html += layer_data[order];
						 html += "</label>";
						 html += "</div>";
				}		
				$("#limitation > div").html(html);
				if(flag_button_type == 1){
					$('input[name=layer_name]').attr("disabled",true);
				}else{
					$('input[name=layer_name]').attr("disabled",false);	
				}
				$(".btn_save").attr('disabled',false);

			}else{
				// alert(change_per);
				// if(change_per > 1){
					let layer_name = $("#read_layer_"+read_limit).parent("label").text();
					let error_message = (read_limit == 0)?errMsg(commonMsg.JSE091, [layer_name]):errMsg(commonMsg.JSE090, [layer_name]);
					$("#errorPermission").empty();
					$("#errorPermission").append("<div>" + error_message + "</div>");
					$("html, body").animate({
						scrollTop: 30
					}, "fast");
					$(".btn_save").attr('disabled',true);
				// }
			}



		});
		// alert(change_per);

	});
	//search function for page and phase
	function SearchData() {
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';
		var s_role  = document.getElementById('s_role').value;
		var s_menu  = document.getElementById('s_menu').value;
		var s_page  = document.getElementById('s_page').value;
		// if (s_role == '' && s_menu == '' && s_page == '') document.getElementById("hid_search").value = "SEARCHALL";
		// else document.getElementById("hid_search").value = "";
		var chk = true;
		// console.log("s_role "+s_role+" menu "+s_menu+" s_page "+s_page);
		// console.log('search  '+ document.getElementById("hid_search").value);
		// return false;
		if (chk) {
			loadingPic();
			document.forms[1].action = "<?php echo $this->webroot; ?>Roles/index";
			document.forms[1].method = "GET";
			document.forms[1].submit();
			$("html, body").animate({
				scrollTop: 10
			}, "fast");
			return true;
		}
	}

	function ActionDelete(id,phase_name,page_name) {
		var id          = "#"+id;
		var phase_name1 = '.phase_'+phase_name;
		var phase_name  = '.'+phase_name;
		var page_name1  = '.page_'+page_name;
		var page_name   = '.'+page_name+':last';
		// last is for all phase, all page class name state
		$(id).remove();
		if ($(page_name).find("li").length == 0) {
			$(page_name).remove();
			$(page_name1).remove();
		}
		if ($(phase_name).find("li").length == 0) {
			$(phase_name).remove();
			$(phase_name1).remove();
		}

	}

	function roleSave() {
		document.querySelector("#error").innerHTML = "";
		document.querySelector("#success").innerHTML = "";
		var per_array = [];
		$('.delete_action').each(function(){
			var li = $(this).closest("li").attr('class');
			li = li.replace(/Read/g, 'Index');
			per_array.push(li);
		});
		let check_permission = $("input[name='permission_choose']:checked").val();
		let roleName         = document.querySelector("#role_name").value;
		
		let errorFlag = true;
		if (!checkNullOrBlank(roleName)) {
			let newbr = document.createElement("div");
			let a = document.querySelector("#error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("ロール名"); ?>'])));
			document.querySelector("#error").appendChild(a);
			errorFlag = false;
		}
		if (check_permission != 1) {
			if (!checkNullOrBlank(per_array)) {
				let newbr = document.createElement("div");
				let a = document.querySelector("#error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("権限"); ?>'])));
				document.querySelector("#error").appendChild(a);
				errorFlag = false;
			}
		}
		var path = window.location.pathname;
		var page = path.split("/").pop();
		if (page.indexOf("page:") !== -1) {
			document.getElementById('hid_page_no').value = page;
		}
		/*Changed by PanEiPhyo (20200313), check layer_code null for Sales TL and Sales Incharge*/
		if (errorFlag) {
			$('#permission_list').val(per_array.join(','));
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
							document.forms[0].action = "<?php echo $this->webroot; ?>Roles/add";
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
	var tmp_role_id;
	// var change_per;
	function editClick(id,id_array) {
		document.querySelector("#error").innerHTML = '';
		document.querySelector("#success").innerHTML = '';
		tmp_role_id = id;
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>Roles/edit",
			data: {
				id: id,id_array:id_array
			},
			dataType: 'json',
			beforeSend: function() {
				loadingPic();
			},
			success: function(data) {
				// change_per = data['count'];
				var appendText  = "";var buttonText = "";
				let id          = data['Role']['id'];
				let read_limit  = data['Role']['read_limit'];
				let role_name   = data['Role']['role_name'];
				let action_name = data[0]['action_name'].split(",");
				let menu_id     = data[0]['menu_id'].split(",");
				let limitation  = data[0]['limitation'].split(",");
				let page_name   = data['Menu']['page_name_jp'];
				let language    = '<?php echo $language;?>';
				if (language == 'eng'){
					page_name   = $.trim(data['Menu']['page_name'].split(" ").join(""));
					var menu_name   = $.trim(data[0]['menu_name'].split(" ").join("")).replace(/[_\W]+/g, "");
				}else{
					
					var menu_name   = data[0]['menu_name'];
				} 
				$("#role_name").val(role_name);
				$('#primary_id').val(id);
				$('#read_limit').val(read_limit);
				$('#menu_id').val(data[0]['menu_id']);
				$.each(action_name, function(i, value) {
					value = value.replace(/_/g, ' ');
					if(language == "eng"){
						value = value.replace(/index/g, 'Read');
						value = value.replace(/reject/g, 'Revert');
						value = value.replace(/review/g, 'Check');
					}else{
						value = value.replace(/拒否/g, '差し戻し');
						value = value.replace(/レビュー/g, '確認欄');
					}
					let delete_flag = "" ;
					var function_name = "ActionDelete("+menu_id[i]+limitation[i]+","+"\'"+menu_name+"\'"+","+"\'"+page_name+"\')";
					if(value.indexOf("Read") !== -1 || value.indexOf("画面表示") !== -1){
						delete_flag = "disabled_cross" ;
						function_name = "";
					}
					buttonText += "<li id='"+menu_id[i]+limitation[i]+"' class='"+menu_id[i]+"_"+limitation[i]+"'>"+ value+"&nbsp;<a class='delete_action' href='#' onclick="+function_name+"><i class='glyphicon glyphicon glyphicon-remove "+delete_flag+"' style='color:red;'></i></a></li>";
				});
				appendText += '<ul class="ul-style"><b><li class="phase_'+menu_name+'">'+data[0]['menu_name']+'</li></b><ul class="'+menu_name+'"><b><li class="page_'+page_name+'">'+data[0]['page_name']+'</li></b><ul class="'+page_name+'">';
				appendText += buttonText+"</ul></ul></ul>";
				$(".list_info").empty("");
				$('.list_info').append(appendText);
				$('#save').hide();
				$('#update').show();
				$('#overlay').hide();
				$('#permission_radio').hide();
				$('#per_list').show();
			}
		});
	}

	function roleUpdate() {
		let roleName = document.querySelector("#role_name").value;
		var per_array = [];let errorFlag = true;
		$('.delete_action').each(function(){
			var li = $(this).closest("li").attr('class');
			per_array.push(li);
		});

		if (!checkNullOrBlank(roleName)) {
			let newbr = document.createElement("div");
			let a = document.querySelector("#error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("ロール名"); ?>'])));
			document.querySelector("#error").appendChild(a);
			errorFlag = false;
		}
		if (!checkNullOrBlank(per_array)) {
			let newbr = document.createElement("div");
			let a = document.querySelector("#error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("権限"); ?>'])));
			document.querySelector("#error").appendChild(a);
			errorFlag = false;
		}
		var path = window.location.pathname;
		var page = path.split("/").pop();
		if (page.indexOf("page:") !== -1) {
			document.getElementById('hid_page_no').value = page;
		}
		if (errorFlag) {
			$('#permission_list').val(per_array.join(','));
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
							document.forms[0].action = "<?php echo $this->webroot; ?>Roles/add";
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

	function deleteClick(id,id_array) {
		document.querySelector("#error").innerHTML   = '';
		document.querySelector("#success").innerHTML = '';
		let errorFlag = true;

		var path = window.location.pathname;
		var page = path.split("/").pop();
		if (page.indexOf("page:") !== -1) {
			document.getElementById('hid_search_page_no').value = page;
		}
		if (errorFlag) {
			$('#id').val(id);
			$('#id_array').val(id_array);
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
							document.forms[1].action = "<?php echo $this->webroot; ?>Roles/delete";
							document.forms[1].method = "POST";
							document.forms[1].submit();
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
	/*
	* 	prevent enter key press
	*	@Zeyar Min
	*/
	function stopRKey(evt) {
		var evt = (evt) ? evt : ((event) ? event : null);
		var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
		if ((evt.keyCode == 13) && (node.type=="text"))  {return false;}
	}

	document.onkeypress = stopRKey;
</script>
<div id="overlay">
	<span class="loader"></span>
</div>
<div class="content">
	<form action="Roles/add" class="form-inline form-zero" id="" method="post" accept-charset="utf-8">
		<div class="register_form">
			<div style="display:none;">
				<!-- <input type="hidden" name="_method" value="POST" /> -->
				<input type="hidden" name="permission_list" id="permission_list" />
			</div>
			<fieldset>
				<legend><?php echo __('役割と権限の管理'); ?></legend>
				<div class="success" id="success"><?php echo ($this->Session->check("Message.RoleSuccess")) ? $this->Flash->render("RoleSuccess") : ''; ?></div>
				<div class="error" id="error"><?php echo ($this->Session->check("Message.RoleError")) ? $this->Flash->render("RoleError") : ''; ?></div>
				<div class="form-row d-flex flex-column">
					<!-- Form Group 1 -->
					<div class="form-group col-md-7">
						<label for="role_name" class="control-label required">
							<?php echo __('ロール名'); ?>
						</label>
						<input class="form-control form_input" id="role_name" name="role_name" type="text" maxlength="200" value="" autocomplete="off"/>
					</div>
					<div class="form-group col-md-8" id="permission_radio">
						<label for="limitation" class="control-label required">
							<?php echo __('アクション'); ?>
						</label>
						<label for="<?php echo "permission_1"; ?>" style='font-weight:300;width:25%;'><input type="radio" name="permission_choose" id="<?php echo "permission_1"; ?>" value="<?php echo "1"; ?>" /><?php echo __("すべての許可");  ?></label>
						<label for="<?php echo "permission_2"; ?>" style='font-weight:300;width:25%;'><input type="radio" name="permission_choose" id="<?php echo "permission_2"; ?>" value="<?php echo "2"; ?>" /><?php echo __("権限を選択"); ?></label>
					</div>
					<div class="form-group col-md-7" id="per_list" style="display:none;">
						<label for="permission" class="control-label required">
							<?php echo __('権限'); ?>
						</label>
						<!-- <div class="permission_list"> -->
							<fieldset class='per_fieldset'>
								<legend class='per_legend' style='font-size:14px;'>
								<div class="add_permission">
									<span class="btn-label"><i class="glyphicon glyphicon glyphicon-plus-sign"></i></span>&nbsp;<?php echo __('新しい権限を追加'); ?>
								</div>
								</legend>
								<div class="list_info">	
								</div>
							</fieldset>
						<!-- </div> -->
					</div>
		
					<!-- Form Group 2 -->
					<div class="form-group col-md-7">
					</div>
					<!-- Form Group 3 empty column-->
					<div class="form-group col-md-7">
						<div class="submit btn-save-wpr" id="save">
						<input onclick="roleSave()" type="button" value="<?php echo __("保存"); ?>" class="btn-save" />
						</div>
						<!-- update -->
						<div class="submit btn-save-wpr " id="update">
							<input type="hidden" name="primary_id" id="primary_id" value="">
							<input type="hidden" name="read_limit" id="read_limit" class="txtbox" value="">
							<input type="hidden" name="menu_id" id="menu_id" value="">
							<input onclick="roleUpdate()" type="button" value="<?php echo __("変更"); ?>" class="btn-save update-btn" />
						</div>
					</div>

					<!-- Form Group 4 buttons -->
					<div class="form-group col-md-7">
						<input type="hidden" name="hid_row_count" id="hid_row_count" class="txtbox" value="<?php echo $query_count ?>">
						<input type="hidden" name="hid_page_no" id="hid_page_no" class="txtbox" value="<?php echo $page ?>">
						<input type="hidden" name="hid_limit" id="hid_limit" class="txtbox" value="<?php echo $limit ?>">
						<input type="hidden" name="hid_s_role" id="hid_s_role" class="txtbox" value="<?php echo $role ?>">
						<input type="hidden" name="hid_s_menu" id="hid_s_menu" class="txtbox" value="<?php echo $menu ?>">
						<input type="hidden" name="hid_s_page" id="hid_s_page" class="txtbox" value="<?php echo $pg_name ?>">
					</div>
				</div>
			</fieldset>
			<!-- Permission Box Modal -->
				<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
					<div id="popup-modal" class="modal-dialog modal-dialog-lg" role="document">
						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header change_color" style='background-color:#81c6cc;'>
								<h5 class="modal-title" id="exampleModalLongTitle"><?php echo __("権限設定"); ?></h5>
								<button type="button" class="close" id="closePop" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class='modal-body'>
								<div class="col-md-12">
									<div class="error" id="errorPermission"></div>
								</div>
								<div class = 'row'>
									<div class="col-md-12">
										<div class="form-group col-md-12" id="phase_form">
											<label for="phase" class="control-label required show_detail">
												<?php echo __("メニュー名"); ?>
											</label>
											
											<select class="form-control form_input" id="phase" name="phase">
												<option value="">----- Select Menu -----</option>
												<option value="all"><?php echo __("全て"); ?></option>
												<?php foreach ($phase as $phase_id=>$phase) :?>
													<option class="<?php echo $phase_id; ?>" value="<?php echo $phase; ?>"><?php echo $phase; ?></option>
												<?php endforeach; ?>
											</select>
										</div>

										<div class="form-group col-md-12" id="pg_name">
											<label for="page_name" class="control-label required show_detail">
												<?php echo __("ページ名"); ?>
											</label>
											
											<select class="form-control form_input" id="page_name" name="page_name">
												<option value="">----- Select Page Name -----</option>
											</select>
										</div>
										<div class="row col-md-10" id="read_limitations" style='margin-left:auto;'>
											<label for="read_action" class="control-label required">
												<?php echo __('Read Action'); ?>
											</label>
											<div class="col-md-10 layer_class" style="margin-top: -19px;margin-left: 100px;" id="read_action">
												<?php if (!empty($layer_data)) {
													for ($order = 0; $order < count($layer_data); $order++):?>
														<div class="col-md-6">
															<label for="<?php echo "read_layer_" . $order; ?>" style='font-weight:300;'><input class="read_limit" type="radio" name="read_layer" id="<?php echo "read_layer_" . $order; ?>" value="<?php echo $order; ?>" /><?php echo $layer_data[$order];  ?></label>
														</div>
												<?php endfor;
												} ?>
											</div>
										</div>
										<div class="form-group col-md-12" id="save_btn_show">
											<label for="button_type" class="control-label show_detail">
												<?php echo __("処理"); ?>
											</label>
											<select multiple="multiple" class="form-control form_input" id="button_type" name="button_type[]">
												<option value="">-----Select-----</option>
												<option value="">-----Select-----</option>
												
											</select>
										</div>
										<div class="row col-md-10" id="limitation" style='margin-left:auto;'>
											<label for="limitation" class="control-label">
												<?php echo __('アクション'); ?>
											</label>
											<div class="col-md-10 layer_class" style="margin-top: -19px;margin-left: 100px;">
											</div>
										</div>
									</div>
								</div>
							</div>			
						<div class="modal-footer">
							<input type="hidden" id="hd_phase" name="hd_phase" />
							<input type="hidden" id="hd_page_name" name="hd_page_name" />
							<button type="button" class="btn btn-secondary btn_clear"><?php echo __('クリア'); ?></button>
							<button type="button" class="btn btn-success btn_save" data-dismiss="modal"><?php echo __('保存'); ?></button>
						</div>
						</div>
					</div>
				</div>
			<!-- End Permission Box Modal -->
		</div>
	</form>
	<br><br>
	<!-- Row count message -->
	<form action="post" class="form-one" accept-charset="utf-8">
		<?php if (empty($errmsg) || !empty($role)) { ?>
				<div class="col-md-12" style="padding: 0;">
					<input type="hidden" name="id" id="id" />
					<input type="hidden" name="id_array" id="id_array" />
					<input type="hidden" name="hid_search" id="hid_search" value="">
					<input type="hidden" name="hid_search_row_count" id="hid_search_row_count" class="txtbox" value="<?php echo $query_count ?>">
					<input type="hidden" name="hid_search_page_no" id="hid_search_page_no" class="txtbox" value="<?php echo $page ?>">
					<input type="hidden" name="hid_search_limit" id="hid_search_limit" class="txtbox" value="<?php echo $limit ?>">
					<!-- <input type="hidden" name="s_role_name" id="s_role_name" value="<?php $role ?>">
					<input type="hidden" name="s_menu_name" id="s_menu_name" value="<?php $menu ?>">
					<input type="hidden" name="s_page_name" id="s_page_name" value="<?php $pg_name ?>"> -->
					<table width="100%">
						<tr>
							<!-- < ?php if($query_count > 0) {?> -->
								<div style="width: 50%">
									<td valign="bottom">
										<div class="pull-left msgfont" id="succc" style="margin-bottom: 0">
											<span><?= $count ?></span>
										</div>
									</td>
								</div>
							<!-- < ?php } ?>
							< ?php } else { ?> -->
								<!-- <div style="width: 50%">
									<td valign="bottom">
										<div class="pull-left msgfont" id="succc" style="margin-bottom: 0">
											<span><?= " " ?></span>
										</div>
									</td>
								</div> -->
							<!-- < ?php } ?> -->
							<div class="form-group pull-right col-md-8" style="width: 50%">
								<td class="select-box">
									<select class="form-control search_role" id="s_role" name="s_role" value="" style="width: 100%">
										<option value="">----- Select Role -----</option>
										<?php foreach ($role_list as $role_id => $role_name) : ?>
											<?php
											if ($role_id == $role) {
												$select = 'selected';
											} else {
												$select = '';
											}
											?>
											<option class="<?php echo $role_id; ?>" value="<?php echo $role_id; ?>" <?= $select ?>><?php echo $role_name; ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td class="select-box">
									<select class="form-control search_menu" id="s_menu" name="s_menu" value="" style="width: 100%">
										<option value="">----- Select Menu Name -----</option>
										<?php foreach ($menu_list as $menu_id => $menu_name) : ?>
											<?php
											if ($menu_name == $menu) {
												$select = 'selected';
											} else {
												$select = '';
											}
											?>
											<option class="<?php echo $menu_id; ?>" value="<?php echo $menu_name; ?>" <?= $select ?>><?php echo $menu_name; ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td class="select-box">
									<select class="form-control search_page" id="s_page" name="s_page" value="" style="width: 100%">
										<option value="">----- Select Page Name -----</option>
										<?php pr($page_list); foreach ($page_list as $page_id => $pagename) : ?>
											<?php
											if ($pagename == $pg_name) {
												$select = 'selected';
											} else {
												$select = '';
											}
											?>
											<option class="<?php echo $page_id; ?>" value="<?php echo $pagename; ?>" <?= $select ?>><?php echo $pagename; ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td class="select-box" style="width:8%;">
									<input type="button" style="background-color:#e5ffff;" class="btn btn-success btn_sumisho pull-right" value="<?php echo __('検索'); ?>" name="search" onclick="SearchData();">	
								</td>
							</div>
						</tr>
					</table>
				</div>
		<?php } ?>
		<?php if (!empty($resultData)) { ?>
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;padding-left:1px;padding-right:1px;">
					<table class="tbl-pl-summary bold-border" id="tbl_pl">
						<thead class="check_period_table bold-border-btm">
							<tr class="bold-border-top bold-border-lft bold-border-rgt">
								<th class=""><?php echo __("ロール名"); ?></th>
								<th class="bdl-solid"><?php echo __('メニュー名'); ?></th>
								<th class="bdl-solid"><?php echo __('ページ名'); ?></th>
								<th class="bdl-solid"><?php echo __('処理'); ?></th>
								<th colspan="2" class="bdl-solid" style="width:100px;"><?php echo __("アクション"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
								
							foreach($resultData as $key=>$value){
								$id_arrays = '';
								$id_arrays_menu = array();
								foreach($value as $key2=>$value2){
									$id_menu = '';
									
									foreach($value2 as $key3=>$value3){
										$roleId = $value3['Role']['id'];
										$id_menu .= $value3[0]['id_array'].',';
										$id_arrays .= $value3[0]['id_array'].',';
									}

									$id_arrays_menu[$key][$key2] = substr($id_menu, 0, -1);;

								}
							?>
								<tr class="row-level-2">
									<td colspan="5" class="bb-none name-field">
										<?php echo $key; ?>
									</td>
									<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='remove'>
										<a class="delete_link" href="#" onclick="deleteClick('<?php echo $roleId;?>','<?php echo substr($id_arrays, 0, -1);?>')"><i class="fa-regular fa-trash-can" title="<?php echo __('削除'); ?>"></i></a>
									</td>
								</tr>
								<?php
								foreach($value as $key2=>$value2){
								?>
								<tr class="row-level-1">
									<td class="bt-none bb-none col-level-2"></td>
									<td colspan="4" class="bb-none">
										<?php echo $key2; ?>
									</td>
									<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='remove'>
										<a class="delete_link" href="#" onclick="deleteClick('<?php echo $roleId;?>','<?php echo $id_arrays_menu[$key][$key2];?>')"><i class="fa-regular fa-trash-can" title="<?php echo __('削除'); ?>"></i></a>
									</td>
								</tr>
								<?php
									foreach($value2 as $key3=>$value3){
										$page_name = ($language == 'eng') ? $value3['Menu']['page_name'] : $value3['Menu']['page_name_jp'];
										$action_type = ($language == 'eng') ? explode(",",$value3[0]['name_en']) : explode(",",$value3[0]['name_jp']);
										?>
										<tr>
											<td class="bt-none bb-none  col-level-1"></td>
											<td class="bt-none bb-none  col-level-1"></td>
											<td>
												<?php echo $key3; ?>
											</td>
											<td>
												<ul class="ul-style">
													<?php foreach ($action_type as $action): 
														$action = explode(' ',$action);
														$action_name = array_pop($action);
														if ($language == 'eng'){
															if($action_name == 'index')
															$action_name = 'Read';
															if($action_name == 'reject')
															$action_name = 'Revert';
															if($action_name == 'review')
															$action_name = 'Check';
														}else{
															if($action_name == '拒否')
															$action_name = '差し戻し';
															if($action_name == 'レビュー')
															$action_name = '確認欄';
														}
														$action_name = str_replace('_', ' ', $action_name);
														$action = implode(' ',$action).' '.$action_name;
													?>
														<li style="text-transform:capitalize">
														<?= h($action) ?></li>
													<?php endforeach; ?>
												</ul>
											</td>
											<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='edit'>
												<a class="" href="#" onclick="editClick('<?php echo $value3['Role']['id'];?>','<?php echo $value3[0]['id_array']; ?>')" title="<?php echo __('編集'); ?>"><i class="fa-regular fa-pen-to-square"></i>
												</a>
											</td>
											<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='remove'>
												<a class="delete_link" href="#" onclick="deleteClick('<?php echo $value3['Role']['id'];?>','<?php echo $value3[0]['id_array'];?>')"><i class="fa-regular fa-trash-can" title="<?php echo __('削除'); ?>"></i></a>
											</td>
										</tr>
										<?php
									}
									?>
								<?php
								}
								?>
							<?php
							}
							?>
							
						</tbody>
					</table>
				
			</div>
		<?php } ?>
	</form>
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
	<br><br>
</div>
<?php if (!empty($errmsg) && empty($role)) { ?>
	<div id="err" class="no-data" style="margin-top: 5rem;">
		<?php echo ($errmsg); ?>
	</div>
<?php } ?>
<?php if (!empty($errmsg) && !empty($role) && $query_count == 0) { ?>
	<div id="err" class="no-data" style="margin-top: 5rem;">
		<?php echo __("データが見つかりません！") ?>
	</div>
<?php } ?>
