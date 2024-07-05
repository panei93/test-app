<?php
echo $this->Form->create(false, array('type' => 'post', 'id' => 'Mail', 'name' => 'Mail', 'enctype' => 'multipart/form-data'));
?>
<style>
	body {
		/* font-family: KozGoPro-Regular; */
	}

	#img_arrow {
		/* position: absolute; */
		width: 20px;
		height: 20px;
	}

	#img_sub_arrow {
		width: 18px;
		height: 18px;
	}

	thead {
		/*position: sticky;*/
		top: 0;
	}

	li {
		list-style-type: none;
	}

	input[type="radio"] {
		margin: 0 10px 0 10px;
		margin-right: 3px;
	}

	input[type="checkbox"] {
		margin: 0 10px 0 10px;
		margin-right: 3px;
	}

	.align_adjust {
		text-align: center !important;
		margin: 30px 0px;
	}

	.adjust-position {
		width: 30%;
		word-wrap: break-word;
	}

	.box_container {
		width: 300px;
		height: 75px;
		overflow-y: auto;
	}

	.receiver_box_to,
	.receiver_box_cc,
	.receiver_box_bcc {
		margin-top: 1rem;
		background-color: #eee;
		height: auto;
		padding: 10px;
		position: relative;
		border-radius: 5px;
	}

	.receiver_add {
		cursor: pointer;
		/* background-color: #e48386; */
		padding: 5px 8px;
		font-size: 14px;
		font-weight: 600;
		line-height: 14px;
		color: #fff;
		border: none;
		width: 120%;
		box-shadow: 0px 2px 5px 1px rgba(0,0,0,0.5);
		border-radius: 5px;
	}

	.receiver_box .receiver_add {
		position: absolute;
		top: 0;
		right: 0;
	}

	ul.receiver_limit li div {
		display: inline-block;
		width: 80px;
	}

	.btn_addvar {
		display: inline-block;
		background-color: #5ab3cc;
		color: #fff;
		font-size: 12px;
		padding: 2px 10px;
		box-shadow: 1px 1px 1px #777;
		cursor: pointer;
		border-radius: 5px;
	}

	.btn_sub_addvar {
		display: inline-block;
		background-color: #00a6a0;
		color: #fff;
		font-size: 12px;
		padding: 2px 10px;
		box-shadow: 1px 1px 1px #777;
		cursor: pointer;
		border-radius: 5px;
	}

	.modal-header {
		padding: 9px 15px;
		border-bottom: 1px solid #eee;
		background-color: #30c6c1;
		-webkit-border-top-left-radius: 5px;
		-webkit-border-top-right-radius: 5px;
		-moz-border-radius-topleft: 5px;
		-moz-border-radius-topright: 5px;
		border-top-left-radius: 5px;
		border-top-right-radius: 5px;
	}

	.select2-container .select2-selection--single {
		height: 35px;
		line-height: 31px;
	}
	.select2-results__options,.select2-search__field{
		font-size: 12px;
	}

	.search_page,
	.search_phase {
		display: inline-block;
		width: 245px;
		vertical-align: middle;
	}

	.detail_link.disabled {
		pointer-events: none;
		color: #ccc;
	}

	.row {
		display: block;
		margin-right: 0px;
	}

	.select2-search--dropdown .select2-search__field {
		padding: initial;
		outline: none;
		line-height: 28px;
		padding-left: 6px;
		padding-right: 6px;
	}
	.m_body_btn_div {
		display: flex;
		flex-direction: column;
		position: absolute;
		margin-left:-35px;
	}
	.mail_body_btn {
		min-width: 7rem;
		display: inline-block;
		background-color: #5ab3cc;
		color: #fff;
		font-size: 12px;
		padding: 5px 10px;
		box-shadow: 1px 1px 1px #777;
		cursor: pointer;
		border-radius: 5px;
		margin-bottom: 0.5rem;
	}
	.tooltip {
		position: absolute;
		z-index: 9999;
		background-color: #ffd2d2;
		color: #ff3333;
		font-size: 14px;
		padding: 10px;
		border-radius: 4px;
		opacity: 0;
		transition: opacity 0.3s;
		width: fit-content;
		top: -40px !important;
		left: 5px !important;
		transition: .3s linear;
		animation: tooltips 1s ease-in-out infinite  alternate;
	}
	.tooltip.top {
		margin-top: 5px !important;
	}
	.tooltip.in {
		opacity: 1 !important;
	}
	.tooltip::after {
		content: "";
		position: absolute;
		border-style: solid;
		border-width: 10px;
		border-color: #ffd2d2 transparent transparent transparent;
		bottom: -18px;
		left: 7%;
		margin-left: -10px;
	}
	.tooltip-inner {
		font-size: 0.75rem;
		background-color: #ffd2d2;
		color: #ff3333;
		max-width: fit-content !important;
	}
	.tooltip-arrow {
		border-style: none !important;
	}
	.alert-danger {
		width: fit-content;
	}
	@keyframes tooltips {
		0%{
			transform: translateY(5px); 
		}
		100%{
			transform: translateY(1px); 
		}
	}
</style>
<script>
	$(document).ready(function() {

		if ($('#tbl_field').length > 0) {
            $("#tbl_field").floatThead({position: 'absolute'});
        }
		// $("html, body").animate({ scrollTop: $(document).height() }, "slow");
		document.getElementById("hidSearch").value = "SEARCHALL";

		// read only mail subject and mail body
		let emailSubject = $('#m_subject');
		let emailBody = $('#email_body');
		emailSubject.prop('readonly', true);
		emailBody.prop('readonly', true);

		$('#button_type').amsifySelect();
		$('#admin_levels').amsifySelect();

		// check number of characters for mail_code
		var maxLength = 7; // maximum number of characters allowed
		$('#m_code').select2({
			tags: true,
			maximumInputLength: maxLength // set maximum input length for Select2
		}).on('select2:open', function() {
			$('.select2-search__field').attr('placeholder', '<?php echo __('新規追加'); ?>');
			$('.select2-search__field').on('keydown', function() {
			var length = $(this).val().length;
			var length = maxLength-length;
			if (length < 0) {
				$('.select2-search__field').tooltip({
					title: '<?php echo __("メールコードは 8 文字以内で入力してください。"); ?>',
					placement: 'top',
					trigger: 'manual'
				}).tooltip('show');
			} else {
				$('.select2-search__field').tooltip('hide'); // hide tooltip
			}
			});
		}).on('select2:close', function() {
			$('.select2-search__field').tooltip('dispose'); // dispose of tooltip when dropdown is closed
		});
		//add value when cursor starts
		jQuery.fn.extend({
			insertAtCaret: function(myValue) {
				return this.each(function(i) {
					if (document.selection) {
						this.focus();
						var sel = document.selection.createRange();
						sel.text = myValue;
						this.focus();
					} else if (this.selectionStart || this.selectionStart == '0') {
						var startPos = this.selectionStart;
						var endPos = this.selectionEnd;
						var scrollTop = this.scrollTop;
						this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
						this.focus();
						this.selectionStart = startPos + myValue.length;
						this.selectionEnd = startPos + myValue.length;
						this.scrollTop = scrollTop;
					} else {
						this.value += myValue;
						this.focus();
					}
				});
			}
		});

		//hide Admin column,Mail Info(to,cc,bcc) and mail type
		$('#mail_send_form input').on('change', function() {
			var mailFlag = $('input[name=mail_send]:checked', '#mail_send_form').val();
			if (mailFlag == 1) {
				$('.mail_setting').show();
				var mail_code = $('#m_code').val();
				if (mail_code == null) {
					$('span.select2-selection__rendered').attr('title', '----- Select Mail Code -----');
					$("#select2-m_code-container").html("----- Select Mail Code -----");
				}
			} else {
				$('.mail_setting').hide();
			}
		});

		//hide all recerver box in initial state
		$('.receiver_box_to').parent().parent('.rbox_wpr').hide();
		$('.receiver_box_cc').parent().parent('.rbox_wpr').hide();
		$('.receiver_box_bcc').parent().parent('.rbox_wpr').hide();

		//Added by Pan Ei Phyo (20220530)
		$('.m_body').on('click', '.btn_addvar', function function_name() {
			var paramName = "{" + $(this).attr("data-param") + "}";
			var area_box = $(".email_body");
			area_box.insertAtCaret(paramName);
		});
		$('.m_body').on('click', '.mail_body_btn', function () {
			var paramName = "{" + $(this).attr("data-param") + "}";
			var area_box = $(".email_body");
			area_box.insertAtCaret(paramName);
		});
		//for mail subject
		$('#mail_subject').on('click', '.btn_sub_addvar', function function_name() {
			var paramName = "{" + $(this).attr("data-param") + "}";
			var mail_subject = $("#m_subject");
			mail_subject.insertAtCaret(paramName);
		});
		//when click +to,+cc,+bcc buttons
		$(".receiver_add").click(function() {
			$('.receive_div_to').hide();
			$('.receive_div_cc').hide();
			$('.receive_div_bcc').hide();
			$('.btn_save.to').hide();
			$('.btn_save.cc').hide();
			$('.btn_save.bcc').hide();
			$('.btn_clear.to').hide();
			$('.btn_clear.cc').hide();
			$('.btn_clear.bcc').hide();

			//check classname and show related div
			if ($(this).hasClass("to")) {
				$('.receive_div_to').show();
				$('.btn_save.to').show();
				$('.btn_clear.to').show();
				$(".change_color").css("background", "#5ab3cc");
				$('.btn_save.to').css("background", "#5ab3cc");
				$('.btn_clear.to').css("background", "#67BFDA");
			} else if ($(this).hasClass("cc")) {
				$('.receive_div_cc').show();
				$('.btn_save.cc').show();
				$('.btn_clear.cc').show();
				$(".change_color").css("background", "#dcb56e");
				$('.btn_save.cc').css("background", "#dcb56e");
				$('.btn_clear.cc').css("background", "#F2D082");
			} else {
				$('.receive_div_bcc').show();
				$('.btn_save.bcc').show();
				$('.btn_clear.bcc').show();
				$(".change_color").css("background", "#cb99c9");
				$('.btn_save.bcc').css("background", "#cb99c9");
				$('.btn_clear.bcc').css("background", "#C09FC0");
			}
			//show modal box
			$("#myModal").modal('show');
		});

		//when click save receiver button in modal box
		$('.btn_save').on('click', function function_name(argument) {
			var boxClass = "";
			var formClass = "";
			var appendText = "";
			if ($(this).hasClass("to")) {
				boxClass = ".receiver_box_to";
				formClass = ".receiveto";
			} else if ($(this).hasClass("cc")) {
				boxClass = ".receiver_box_cc";
				formClass = ".receivecc";
			} else {
				boxClass = ".receiver_box_bcc";
				formClass = ".receivebcc";
			}
			$(boxClass).empty();
			//loop through all radio buttons
			$(formClass).each(function() {
				//get data if radio is checked
				if ($(this).is(':checked')) {
					var radioText = $(this).parent().text();
					var radioName = $(this).attr("name");
					var positName = $(this).closest("li").find("div").text();
					appendText += radioText + 'の' + positName + ', ';
				}
			});
			$(boxClass).append(appendText);
			if (!appendText) {
				$(boxClass).parent().parent('.rbox_wpr').hide();
			} else {
				$(boxClass).parent().parent('.rbox_wpr').show();
			}

		});

		//when click clear receiver button in modal box
		$('.btn_clear').on('click', function function_name(argument) {
			var boxClass = "";
			var formClass = "";
			if ($(this).hasClass("to")) {
				boxClass = ".receiver_box_to";
				formClass = ".receiveto";
			} else if ($(this).hasClass("cc")) {
				boxClass = ".receiver_box_cc";
				formClass = ".receivecc";
			} else {
				boxClass = ".receiver_box_bcc";
				formClass = ".receivebcc";
			}
			$(boxClass).empty();
			$(formClass).attr('checked', false);
			$(boxClass).parent().parent('.rbox_wpr').hide();

		});

		//change page name dropdown value according to phase
		$("#phase").change(function(evt, param1) {
			var phase_id = $('#phase').val();
			// clear the value and label of m_code
			// $('#m_code').val('').trigger('change');
			// $('#m_subject').val('');
			// $('#email_body').val('');
			//clear edit button type
			$("#edit_button_type").empty();
			$('#edit_button_type').append("<option value =''>" + '-----Select-----' + "</option>");
			$.ajax({
				type: "POST",
				url: "<?php echo $this->webroot; ?>MailFlowSettings/getPageName",
				data: {
					phase_id: phase_id,
					name: ""
				},
				dataType: "json",
				success: function(data) {
					if(data[0].length == 0 && data[1].length == 0 && data[2].length == 0) {
						$('.amsify-list li').removeClass('active');
						$('.amsify-selection-area .amsify-selection-list ul.amsify-list').empty();
						$('.amsify-selection-area .amsify-selection-label .amsify-label').text('-----Select-----');
						$('#edit_button_type').empty();
						$('#edit_button_type').append("<option value =''>" + '-----Select-----' + "</option>");
						// $('#m_subject').prop('disabled', true);/*set readonly input for mail subject*/
						// $('#m_subject').val('');
						// $('#email_body').prop('disabled', true);/*set readonly input for mail body*/
						// $('#email_body').val('');
					} else {
						//To clear all input
						$('.amsify-list li').removeClass('active');
						$('.amsify-selection-area .amsify-selection-list ul.amsify-list').empty();
						$('.amsify-selection-area .amsify-selection-label .amsify-label').text('-----Select-----');
					}
					if (data[0]) {
						$('#page_name').empty();
						$('#page_name').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
						$('#m_subject').prop('disabled', false);/*set readonly input for mail subject*/
						$('#email_body').prop('disabled', false);/*set readonly input for mail body*/
						$.each(data[0], function(i, value) {
							if (typeof param1 !== "undefined") {
								if (param1.page == value) $('#page_name').append("<option value ='" + value + "' selected = 'selected'>" + value + "</option>");
								else $('#page_name').append("<option value ='" + value + "'>" + value + "</option>");
							} else $('#page_name').append("<option value ='" + value + "'>" + value + "</option>");
						});
					} else {
						$('#page_name').empty();
						$('#page_name').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
						$('#edit_button_type').empty();
						$('#edit_button_type').append("<option value =''>" + '-----Select-----' + "</option>");
					}
					if (data[1]) { //for limitation show/hide
						$('input[type=radio][name=layer_name]').remove();
						$('.layer_class').empty();
						$.each(data[1], function(i, value) {
							if (typeof param1 !== "undefined") {
								if (param1.level == i) $('#limitation .layer_class').append("<label><input type=radio id= 'layer_" + i + "' name='layer_name' value='" + i + "' checked='checked'>" + value + "</label>");
								else $('#limitation .layer_class').append("<label><input type=radio id= 'layer_" + i + "' name='layer_name' value='" + i + "'>" + value+"</label>");
							} else $('#limitation .layer_class').append("<label><input type=radio id= 'layer_" + i + "' name='layer_name' value='" + i + "'>" + value + "</label>");
						});
					}
					if (data[2]) {//for mail subject and body show/hide
						if (typeof param1 !== "undefined") {
							$('#mail_subject .mail_vars').empty();
							$('.m_body .mail_vars').empty();
							$('.m_body .m_body_btn_div').empty();
							$.each(data[2], function(i, value) {
								$('#mail_subject .mail_vars').css('pointer-events', 'none').append("<div class='btn_sub_addvar' data-param='" + i + "' ><i class='fa-solid fa-circle-plus'></i>&nbsp;" + value + " </div> ");
								$('.m_body .mail_vars').css('pointer-events', 'none').append("<div class='btn_addvar' data-param='" + i + "' ><i class='fa-solid fa-circle-plus'></i>&nbsp;" + value + " </div> ");
							});
							$.each(data[3], function(i, value) {
								if(phase_id == i) {
									$.each(value, function(i, value) {
										$('.m_body .m_body_btn_div').css('pointer-events', 'none').append("<div class='mail_body_btn' data-param='" + i + "'><i class='fa-solid fa-circle-plus'></i>&nbsp;" + value + " </div> ");
									});
								}
							});
						} else {
							// check menu name on save
							$('#mail_subject .mail_vars').empty();
							$('.m_body .mail_vars').empty();
							$('.m_body .m_body_btn_div').empty();
							if($('#m_code').val() == '' || $('#m_code').val() != '') {
								$.each(data[2], function(i, value) {
									$('#mail_subject .mail_vars').css('pointer-events', 'none').append("<div class='btn_sub_addvar' data-param='" + i + "' ><i class='fa-solid fa-circle-plus'></i>&nbsp;" + value + " </div> ");
									$('.m_body .mail_vars').css('pointer-events', 'none').append("<div class='btn_addvar' data-param='" + i + "' ><i class='fa-solid fa-circle-plus'></i>&nbsp;" + value + " </div> ");
								});
								$.each(data[3], function(i, value) {
									if(phase_id == i) {
										$.each(value, function(i, value) {
											$('.m_body .m_body_btn_div').css('pointer-events', 'none').append("<div class='mail_body_btn' data-param='" + i + "'><i class='fa-solid fa-circle-plus'></i>&nbsp;" + value + " </div> ");
										});
									}
								});
							} else {
								$.each(data[2], function(i, value) {
									$('#mail_subject .mail_vars').append("<div class='btn_sub_addvar' data-param='" + i + "' ><i class='fa-solid fa-circle-plus'></i>&nbsp;" + value + " </div> ");
									$('.m_body .mail_vars').append("<div class='btn_addvar' data-param='" + i + "' ><i class='fa-solid fa-circle-plus'></i>&nbsp;" + value + " </div> ");
								});
								$.each(data[3], function(i, value) {
									if(phase_id == i) {
										$.each(value, function(i, value) {
											$('.m_body .m_body_btn_div').append("<div class='mail_body_btn' data-param='" + i + "'><i class='fa-solid fa-circle-plus'></i>&nbsp;" + value + " </div> ");
										});
									}
								});
							}
						}
					}
					let mail_receiver = $("input[name='mail_receiver']:checked").val();
					if(mail_receiver){
						$('#m_subject').prop('readonly', true);/*set readonly input for mail subject*/
						$('#email_body').prop('readonly', true);
					}
				}
			});
		});

		//change button type dropdown value according to pagename
		$('#pg_name').on('change', '#page_name', function function_name(evt, param) {
			var page_name = $('#page_name').val();
			if (typeof param !== "undefined") page_name = param.page;
			$.ajax({
				type: "POST",
				url: "<?php echo $this->webroot; ?>MailFlowSettings/getButtonType",
				data: {
					page_name: page_name
				},
				dataType: "json",
				beforeSend: function() {
					
				},
				success: function(data) {
					if (data) {
						$('#button_type').empty();
						$('#edit_button_type').empty();
						$('#button_type').append("<option value =''>" + '-----Select-----' + "</option>");
						$('#edit_button_type').append("<option value =''>" + '-----Select-----' + "</option>");
						$.each(data, function(i, value) {
							noRepVal = value;
							value = value.replace("_", " ");
							value = value.toLowerCase().replace(/\b[a-z]/g, function(letter) {
    							return letter.toUpperCase();
							});
							//add value to Button Types for Save and Edit
							if (typeof param !== "undefined") {
								if (param.btn == noRepVal) $('#edit_button_type').append("<option value ='" + i + "' selected = 'selected'>" + value + "</option>");
								else {
									$('#edit_button_type').append("<option value ='" + i + "'>" + value + "</option>");
									$('#button_type').append("<option value ='" + i + "'>" + value + "</option>");
								}
							} else {
								$('#button_type').append("<option value ='" + i + "'>" + value + "</option>");
								$('#edit_button_type').append("<option value ='" + i + "'>" + value + "</option>");
							}
						});
						if (typeof param == "undefined") {
							$('#button_type').amsifySelect();
							$('.amsify-selection-label').on('click', function(event) {
								// emptyProcess = true;
								$("#error").empty();
								let pageName = $("#page_name").val();
								if (!checkNullOrBlank(pageName)) {
									$("#error").append("<div>" + errMsg(commonMsg.JSE089) + "</div>");
									$("html, body").animate({
										scrollTop: 30
									}, "fast");
								}
							});
						}
						if(data == '') {
							$('#edit_button_type').empty();
							$(".amsify-select-clear").prop('disabled', true);
							$('.amsify-selection-area .amsify-selection-list ul.amsify-list li.amsify-list-item').append('<input type="checkbox" name="button_type[]_amsify" class="amsify-select-input" value="">');
							$('#edit_button_type').append("<option value =''>" + '-----Select-----' + "</option>");
						}
					} else {
						$(".amsify-select-clear").prop('disabled', true);
						$('#button_type').empty();
						$('#edit_button_type').empty();
						$('#button_type').append("<option value =''>" + '-----Select-----' + "</option>");
						$('#edit_button_type').append("<option value =''>" + '-----Select-----' + "</option>");
					}
				}
			});
		});

		//change page name dropdown value according to phase in search form
		$('#s_phase').change(function(event, param) {
			var phase_id = $('select[name="s_phase"] :selected').val();
			$.ajax({
				type: "POST",
				url: "<?php echo $this->webroot; ?>MailFlowSettings/getPageName",
				data: {
					phase_id: phase_id,
					name: "Search"
				},
				dataType: "json",
				success: function(data) {
					if (data[0]) {
						$('#s_page').empty();
						$('#s_page').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
						$.each(data[0], function(i, value) {
							if (param == value) $('#s_page').append("<option value ='" + value + "' selected = 'selected'>" + value + "</option>");
							else $('#s_page').append("<option value ='" + value + "'>" + value + "</option>");
						});
					} else {
						$('#s_page').empty();
						$('#s_page').append("<option value =''>" + '----- Select Page Name -----' + "</option>");
					}
				}
			});
		});

		//get phase and page name from search
		var s_page_name = $("#s_page_name").val();
		var s_phase_name = $("#s_phase_name").val();
		$("#s_phase").val(s_phase_name);
		$("#s_phase").trigger("change", s_page_name);

		//disable mail send button if choose button type 'Read'
		$('#button_type').on("change", function(event, param) {
			var button_type = [];
			$("#save_btn_show .amsify-list .active input").each(function(index) {
				button_type.push($(this).val());
			});
			var page_name = $('#page_name').val();
			if (button_type != null) {
				if (button_type.includes('Read')) {
					$("#mail_no_send").prop("checked", true);
					$("#mail_send").prop("disabled", true);
					$('.mail_setting').hide();
				} else {
					$("#mail_send").prop("disabled", false);
					//if mail send is chosen No, following position should be hidden
					if ($('input[name=mail_send]:checked', '#mail_send_form').val() == 0) {
						$('.mail_setting').hide();
					} else {
						$('.mail_setting').show();
					}
				}
			}
		});
		/* show error without selecting page name */
		$('.amsify-selection-label').on('click', function(event) {
			// emptyProcess = true;
			$("#error").empty();
			$("#success").empty();
			let pageName = $("#page_name").val();
			if (!checkNullOrBlank(pageName)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE089) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
			}
		});


		//change role position based on button type
		$('#edit_button_type').on("change", function(event, param) {
			var page_name = $('#page_name').val();
			var edit_button_type = $('#edit_button_type').val();
			if (edit_button_type == 'Read') {
				$("#mail_no_send").prop("checked", true);
				$("#mail_send").prop("disabled", true);
				$('.mail_setting').hide();
			} else {
				$("#mail_send").prop("disabled", false);
				//if mail send is chosen No, following position should be hidden
				if ($('input[name=mail_send]:checked', '#mail_send_form').val() == 0) {
					$('.mail_setting').hide();
				} else {
					$('.mail_setting').show();
				}
			}
		});
		//clear button for button type
		$('#save_btn_show').on('click', '.amsify-select-clear', function(event) {
			event.preventDefault();
		});
		//clear button for admin level
		$("#save_admin_show .amsify-select-clear").on('click', function(event) {
			event.preventDefault();
			var hidden_value = $("#hid_update_id").val();
			if (hidden_value) {
				clickEdit(hidden_value, "clear");
			}
		});
		//check other same value of radio button
		$('#dept_name input').on('change', function() {
			var dept_class_name = $("#dept_name input[type=radio]:checked").map(function() {
				return this.className;
			}).get();
			$.each(dept_class_name, function(index, dept_name) {
				var dept_check_value = $("input[type=radio][name=limit_" + dept_name + "]:checked").val();
				var others = $("." + dept_name + ":radio[value='" + dept_check_value + "']").not(this);
				others.prop('checked', true);
			});
		});

		//change mail subject and body based on mail code
		$('#m_code').on("change", function() {
			var mail_code = $('#m_code').val();
			var menu_name = $('#phase').val();
			$("#hid_receiver").val('');
			//check new text or not
			if (typeof mail_code === 'string' && mail_code.includes('id_')) {
				mail_code = mail_code.substring(3);
			}
			$.ajax({
				type: "POST",
				url: "<?php echo $this->webroot; ?>MailFlowSettings/getMailInfo",
				data: {
					mail_code: mail_code
				},
				dataType: "json",
				success: function(data) {
					if (data.length !== 0) {
						$("#m_subject").val(data['mail_subject']);
						$(".email_body").val('');
						$(".email_body").val($(".email_body").val() + data['mail_body']);
						$("input:radio[name=mail_receiver][value=" + data['mail_type'] + "]").prop('checked', true);
						$("#hid_receiver").val(data['mail_type']);
						$(".receive_div_to input[type=radio]").attr('checked', false);
						$(".receive_div_cc input[type=radio]").attr('checked', false);
						$(".receive_div_bcc input[type=radio]").attr('checked', false);
						$('.receiver_box_to').empty();
						$('.receiver_box_cc').empty();
						$('.receiver_box_bcc').empty();
						$('.receiver_box_to').parent().parent('.rbox_wpr').hide();
						$('.receiver_box_cc').parent().parent('.rbox_wpr').hide();
						$('.receiver_box_bcc').parent().parent('.rbox_wpr').hide();
						//loop for mail To list
						if (checkNullOrBlank(data['to_level_info'])) {
							$.each(data['to_level_info'], function(level, limit) {
								$("input:radio[id=receive_to_" + level + "][value=" + limit + "]").prop('checked', true);
							});
							$(".btn_save").trigger("click");
						} //loop for mail cc list
						if (checkNullOrBlank(data['cc_level_info'])) {
							$.each(data['cc_level_info'], function(level, limit) {
								$("input:radio[id=receive_cc_" + level + "][value=" + limit + "]").prop('checked', true);
							});
							$(".btn_save").trigger("click");
						} //loop for mail bcc list
						if (checkNullOrBlank(data['bcc_level_info'])) {
							$.each(data['bcc_level_info'], function(level, limit) {
								$("input:radio[id=receive_bcc_" + level + "][value=" + limit + "]").prop('checked', true);
							});
							$(".btn_save").trigger("click");
						}
						$('input[name=mail_receiver]').attr('disabled', true);
						$("#m_subject").attr('readonly', true);
						$(".email_body").attr('readonly', true);
						$('.btn_addvar').css('pointer-events', 'none');
						$('.btn_sub_addvar').css('pointer-events', 'none');
						$('.receiver_add').css('pointer-events', 'none');
					} else {
						$(".receive_div_to input[type=radio]").attr('checked', false);
						$(".receive_div_cc input[type=radio]").attr('checked', false);
						$(".receive_div_bcc input[type=radio]").attr('checked', false);
						$('.receiver_box_to').empty();
						$('.receiver_box_cc').empty();
						$('.receiver_box_bcc').empty();
						$('.receiver_box_to').parent().parent('.rbox_wpr').hide();
						$('.receiver_box_cc').parent().parent('.rbox_wpr').hide();
						$('.receiver_box_bcc').parent().parent('.rbox_wpr').hide();
						$("#m_subject").val('');
						$(".email_body").val('');
						$('input[name=mail_receiver]').attr('disabled', false);
						$("#m_subject").removeAttr('readonly');
						$(".email_body").removeAttr('readonly');
						$('.btn_addvar').css('pointer-events', 'auto');
						$('.btn_sub_addvar').css('pointer-events', 'auto');
						$('.receiver_add').css('pointer-events', 'auto');
						$('.mail_body_btn').css('pointer-events', 'auto');
					}
					if(mail_code == ''|| (mail_code.length > 0 && menu_name == '')){
						$('#m_subject').prop('readonly', true);
						$('#email_body').prop('readonly', true);
					}
				}
			});
		});

		//save function
		$("#save").click(function() {
			$("#error").empty("");
			$("#success").empty("");
			let receiver_list = [];
			let button_arr = [];
			let phase = $("#phase").val();
			let pageName = $("#page_name").val();
			let buttonType = $("#button_type").val();
			let mail_code = $('#m_code').val();
			let mail_subject = $('#m_subject').val();
			let mail_body = $('textarea.email_body').val();
			let mail_receiver = $("input[name='mail_receiver']:checked").val();
			let chk = true;
			$("#save_btn_show .amsify-list .active input").each(function(index) {
				button_arr.push($(this).val());
			});
			$(".receiver_limit input[type=radio]:checked").each(function() {
				receiver_list.push($(this).val());
			});

			if (!checkNullOrBlank(phase)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("メニュー名"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(pageName)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("ページ名"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(button_arr)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("処理"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(mail_code)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("メールコード"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			} else if(mail_code.length > 8) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE087, ['<?php echo __("メールコード"); ?>','<?php echo __(8); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(mail_subject)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, ['<?php echo __("メール件名"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			} else if(mail_subject.length > 255) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE087, ['<?php echo __("メール件名"); ?>','<?php echo __(255) ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(mail_body)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, ['<?php echo __("メール本文"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			} else if(mail_body.length > 255) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE087, ['<?php echo __("メール本文"); ?>','<?php echo __(255) ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(receiver_list)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("メール送信タイプ（To / Cc / Bcc）"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			var path = window.location.pathname;
			var page = path.split("/").pop();
			if (page.indexOf("page:") !== -1) {
				document.getElementById('hid_page_no').value = page;
			}
			if (chk == true) {
				//save to permission	
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
							btnClass: "btn-green",
							action: function() {
								loadingPic();
								document.forms[0].action = "<?php echo $this->webroot; ?>MailFlowSettings/saveMail";
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

		//update function
		$("#update").click(function() {
			$("#error").empty();
			$("#success").empty();
			let receiver_list = [];
			let hid_mail_send = $("#hid_mail_send").val();
			let phase = $("#phase").val();
			let pageName = $("#page_name").val();
			let buttonType = $("#edit_button_type").val();
			let mail_code = $('#m_code').val();
			let mail_subject = $('#m_subject').val();
			let mail_body = $('textarea.email_body').val();
			let mail_receiver = $("input[name='mail_receiver']:checked").val();
			let chk = true;
			let message = null;
			$("#hid_role_name").val(null);
			$(".receiver_limit input[type=radio]:checked").each(function() {
				receiver_list.push($(this).val());
			});

			if (!checkNullOrBlank(phase)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("メニュー名"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(pageName)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("ページ名"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(buttonType)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("処理"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(mail_code)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("メールコード"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			} else if(mail_code.length > 8) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE087, ['<?php echo __("メールコード"); ?>','<?php echo __(8); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(mail_subject)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, ['<?php echo __("メール件名"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(mail_body)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, ['<?php echo __("メール本文"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			if (!checkNullOrBlank(receiver_list)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("メール送信タイプ（To / Cc / Bcc）"); ?>']) + "</div>");
				$("html, body").animate({
					scrollTop: 30
				}, "fast");
				chk = false;
			}
			message = "<?php echo __("データを変更してよろしいですか。"); ?>";
			var path = window.location.pathname;
			var page = path.split("/").pop();
			if (page.indexOf("page:") !== -1) {
				document.getElementById('hid_page_no').value = page;
			}
			if (chk == true) {
				$.confirm({
					title: "<?php echo __("変更確認"); ?>",
					icon: "fas fa-exclamation-circle",
					type: "green",
					typeAnimated: true,
					closeIcon: true,
					columnClass: "medium",
					animateFromElement: true,
					animation: "top",
					draggable: false,
					content: message,
					buttons: {
						ok: {
							text: "<?php echo __("はい"); ?>",
							btnClass: "btn-green",
							action: function() {
								loadingPic();
								document.forms[0].action = "<?php echo $this->webroot; ?>MailFlowSettings/updateMail";
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
		//make textbox with select option
		$(".select_mail_code").select2({
			tags: true
		});
	});
	// hide column
	var coll = document.getElementsByClassName("collapsible");
	var i;
	for (i = 0; i < coll.length; i++) {
		coll[i].addEventListener("click", function() {
			this.classList.toggle("active");
			var content = this.nextElementSibling;
			if (content.style.display === "block") {
				content.style.display = "none";
			} else {
				content.style.display = "block";
			}
		});
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

	// Detail Function for Mail Data
	function clickDetail(mail_code) {
		$("#error").empty();
		$("#success").empty();
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>MailFlowSettings/getMailDetailData",
			data: {
				mail_code: mail_code
			},
			dataType: "json",
			beforeSend: function() {
				loadingPic();
			},
			success: function(data) {
				$(".email_to").val('');
				$(".email_cc").val('');
				$(".email_bcc").val('');
				$(".subject").val('');
				$(".body").val('');
				if (data.length != 0) {
					if (data['to'].length > 0) {
						$(".email_to").val(data['to']);
						$("#To").show();
					} else $("#To").hide();
					if (data['cc'].length > 0) {
						$(".email_cc").val(data['cc']);
						$("#Cc").show();
					} else $("#Cc").hide();
					if (data['bcc'].length > 0) {
						$(".email_bcc").val(data['bcc']);
						$("#Bcc").show();
					} else $("#Bcc").hide();
					$(".subject").val(data['mail_subject']);
					$(".body").val(data['mail_body']);
					$("#mailDetail").modal('show');
				}
				$('#overlay').hide();
			}
		});
	}

	//Edit Function for permission and mail list
	function clickEdit(idstr, type = null) {
		$("#error").empty();
		$("#success").empty();
		id = idstr.split(',')[0];
		mail_code = idstr.split(',')[1];
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>MailFlowSettings/getEditData",
			data: {
				id : id, mail_code : mail_code
			},
			dataType: "json",
			beforeSend: function() {
				loadingPic();
			},
			success: function(data) {
				$("#hid_update_id").val(data['id']);
				$("#phase").val(data['menu_name']);
				$("#phase").trigger("change", [{
					page: data['page'],
					level: data['limit']
				}]);
				$("#page_name").trigger("change", [{
					page: data['page'],
					btn: data['function']
				}]);
				$("#edit_button_type").val(data['function']);
				
				//end preparation for multi select edit
				$('[name=m_code]').val(data['mail_code']).trigger('change');
				
				var mailBody = data['mail_body'];
				$("#m_subject").val(data['mail_subject']);
				$(".email_body").val('');
				$(".email_body").val($(".email_body").val() + mailBody);
				$("input:radio[name=mail_receiver][value=" + data['mail_type'] + "]").prop('checked', true);
				$(".receive_div_to input[type=radio]").attr('checked', false);
				$(".receive_div_cc input[type=radio]").attr('checked', false);
				$(".receive_div_bcc input[type=radio]").attr('checked', false);
				$('.receiver_box_to').empty();
				$('.receiver_box_cc').empty();
				$('.receiver_box_bcc').empty();
				$('.receiver_box_to').parent().parent('.rbox_wpr').hide();
				$('.receiver_box_cc').parent().parent('.rbox_wpr').hide();
				$('.receiver_box_bcc').parent().parent('.rbox_wpr').hide();
				//loop for mail To list
				if (checkNullOrBlank(data['to_level_info'])) {
					$.each(data['to_level_info'], function(level, limit) {
						$("input:radio[id=receive_to_" + level + "][value=" + limit + "]").prop('checked', true);
					});
					$(".btn_save").trigger("click");
				} //loop for mail cc list
				if (checkNullOrBlank(data['cc_level_info'])) {
					$.each(data['cc_level_info'], function(level, limit) {
						$("input:radio[id=receive_cc_" + level + "][value=" + limit + "]").prop('checked', true);
					});
					$(".btn_save").trigger("click");
				} //loop for mail bcc list
				if (checkNullOrBlank(data['bcc_level_info'])) {
					$.each(data['bcc_level_info'], function(level, limit) {
						$("input:radio[id=receive_bcc_" + level + "][value=" + limit + "]").prop('checked', true);
					});
					$(".btn_save").trigger("click");
				}
				$("#save").hide();
				$("#save_btn_show").hide();
				//$("#save_admin_show").show();
				$("#update").show();
				$("#edit_btn_show").show();
				//$("#edit_admin_show").show();
				$('#overlay').hide();
			}
		});
	}

	//Delete Function for permission and mail list
	function clickDelete(id) {
		$("#error").empty();
		$("#success").empty();

		$("#hid_delete_id").val(id);
		var path = window.location.pathname;
		var page = path.split("/").pop();
		if (page.indexOf("page:") !== -1) {
			document.getElementById('hid_page_no').value = page;
		}
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
						document.forms[0].action = "<?php echo $this->webroot; ?>MailFlowSettings/deleteMail";
						document.forms[0].method = "POST";
						document.forms[0].submit();
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
		scrollText();
	}

	//search function for page and phase
	function SearchData() {
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';
		var s_page = document.getElementById('s_page').value;
		var s_phase = document.getElementById('s_phase').value;
		if (s_page == '' && s_phase == '') document.getElementById("hidSearch").value = "SEARCHALL";
		else document.getElementById("hidSearch").value = "";
		var chk = true;
		if (chk) {
			loadingPic();
			document.forms[0].action = "<?php echo $this->webroot; ?>MailFlowSettings/index";
			document.forms[0].method = "POST";
			document.forms[0].submit();
			$("html, body").animate({
				scrollTop: 10
			}, "fast");
			return true;
		}
	}
	/*  
	 * show hide loading overlay
	 *@Zeyar Min  
	 */
	function loadingPic() {
		$("#overlay").show();
		$('.jconfirm').hide();
	}

	function processClick() {
		$("#error").empty();
			let pageName = $("#page_name").val();
			if (!checkNullOrBlank(pageName)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE089) + "</div>");
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
	<div class="row register_form">
		<div class="col-md-12 col-md-12 heading_line_title">
			<h3><?php echo __("メール設定管理"); ?></h3>
			<hr>
		</div>

		<!-- hidden field for delete -->
		<input type="hidden" name="hid_delete_id" id="hid_delete_id">
		<!-- hidden field for update -->
		<input type="hidden" name="hid_update_id" id="hid_update_id">
		<!-- hidden field for mail send value -->
		<input type="hidden" name="hid_mail_send" id="hid_mail_send">
		<!-- hidden field for update role name -->
		<input type="hidden" name="hid_role_name" id="hid_role_name">
		<!-- hidden field for mail_receiver -->
		<input type="hidden" name="hid_receiver" id="hid_receiver">

		<div class="col-lg-12 col-md-12 col-md-12 col-xs-12">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.SuccessMsg")) ? $this->Flash->render("SuccessMsg") : ''; ?><?php echo ($this->Session->check("Message.MailSuccess")) ? $this->Flash->render("MailSuccess") : ''; ?></div>
			<div class="error" id="error"><?php echo ($this->Session->check("Message.ErrorMsg")) ? $this->Flash->render("ErrorMsg") : ''; ?><?php echo ($this->Session->check("Message.MailFail")) ? $this->Flash->render("MailFail") : ''; ?></div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="col-md-6">
					<div id="phase_form">
						<label for="phase" class="col-md-4 control-label required show_detail">
							<?php echo __("メニュー名"); ?>
						</label>
						<div class="col-md-8 show_detail" style="margin-bottom: 15px;">
							<select class="form-control" id="phase" name="phase">
								<option value="">----- Select Menu -----</option>
								<?php foreach ($phase as $phase) :
									if ($language == 'eng') $menu_name = $phase['menus']['menu_name_en'];
									else $menu_name = $phase['menus']['menu_name_jp']; ?>
									<option class="<?php echo $phase['menus']['id']; ?>" value="<?php echo $menu_name; ?>"><?php echo $menu_name; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					
					<div id="save_btn_show">
						<label for="button_type" class="col-md-4 control-label required show_detail">
							<?php echo __("処理"); ?>
						</label>
						<div class="col-md-8 show_detail" style="margin-bottom: 15px;">
							<select multiple="multiple" class="multiple-select form-control" id="button_type" name="button_type[]">
								<option value="">-----Select-----</option>
								<?php foreach ($button_type as $bt_type) : 
									$button_type = str_replace('_', ' ', $bt_type);?>
									<option value="<?php echo $bt_type; ?>"><?php echo $button_type; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div id="edit_btn_show" style="display:none">
						<label for="edit_button_type" class="col-md-4 control-label required show_detail">
							<?php echo __("処理"); ?>
						</label>
						<div class="col-md-8 show_detail" style="margin-bottom: 15px;">
							<select class="form-control" id="edit_button_type" name="edit_button_type" onclick="processClick()">
								<option value="">-----Select-----</option>
								<?php foreach ($button_type as $bt_type) : 
									$button_type = str_replace('_', ' ', $bt_type);?>
									<option value="<?php echo $bt_type; ?>"><?php echo $button_type; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					
					<div id="mail_subject">
						<label for="mail_subject" class="col-md-4 col-form-label required">
							<?php echo __("メール件名"); ?>
						</label>
						<div class="col-md-8 show_detail" style="margin-bottom: 15px;">
							<div class="mail_vars"></div>
							<input type="text" name="m_subject" id="m_subject" class="form-control" placeholder="mail subject">
						</div>
					</div>
					<div class="m_body">
						<label for="email_body" class="col-md-4 col-form-label required">
							<?php echo __("メール本文"); ?>
						</label>
						<div class="col-md-8 show_detail" style="margin-bottom: 15px;">
							<div class="mail_vars"></div>
							
							<textarea rows="5" id="email_body" name="email_body" class="form-control email_body" placeholder="mail body"></textarea>
						</div>
					</div>
					
				</div>
				<div class="col-md-1 m_body">
					<div style="height:190px;"></div>
					<div class="m_body_btn_div" id="m_body_btn_div"></div>
				</div>
				<div class="col-md-5">
					<div id="pg_name">
						<label for="page_name" class="col-md-4 control-label required show_detail">
							<?php echo __("ページ名"); ?>
						</label>
						<div class="col-md-8 show_detail" style="margin-bottom: 15px;">
							<select class="form-control" id="page_name" name="page_name">
								<option value="">----- Select Page Name -----</option>
							</select>
						</div>
					</div>
					<div id="mail_code">
						<label for="mail_code" class="col-md-4 col-form-label required">
							<?php echo __("メールコード"); ?>
						</label>
						<div class="col-md-8 show_detail" style="margin-bottom: 15px;">
							<span id="mail_code_error" style="display:none;color:red;">Maximum character limit reached!</span>
							<select class="select_mail_code form-control" id="m_code" name="m_code" style="width: 100%;">
								<option value="">----- Select Mail Code -----</option>
								<?php foreach ($mail_code as $mail_id => $code) : ?>
									<option value="<?php echo $code; ?>"><?php echo $code; ?></option>
								<?php endforeach ?>
							</select>
						</div>
					</div>
					<div id="mail_receiver">
						<label for="mail_receiver" class="col-md-4 col-form-label required">
							<?php echo __("Mail Receiver"); ?>
						</label>
						<div class="col-md-8 col-sm-12">
							<div class="row" id="m_receiver">
								<div class="col-md-5" style="white-space: nowrap;">
									<input type="radio" name="mail_receiver" id="add_receiver" value="1">
									<label for="add_receiver" style="font-weight: 400;"><?php echo __('自動送信'); ?></label>
								</div>
								<div class="col-md-7" style="white-space: nowrap;">
									<input type="radio" name="mail_receiver" id="fill_in_pop" value="2" checked="checked">
									<label for="fill_in_pop" style="font-weight: 400;"><?php echo __('マニュアル送信（POP)'); ?></label>
								</div>
							</div>
							<div class="row rec_add_btns mt-10 d-flex flex-row">
								<div class="col-md-4 col-sm-4">
									<div class="receiver_add to" style="background-color:#5ab3cc;"><i class="fa-solid fa-circle-plus"></i>&nbsp;To:</div>
								</div>
								<div class="col-md-4 col-sm-4">
									<div class="receiver_add cc" style="background-color:#dcb56e;"><i class="fa-solid fa-circle-plus"></i>&nbsp;Cc:</div>
								</div>
								<div class="col-md-4 col-sm-4">
									<div class="receiver_add bcc" style="background-color:#cb99c9;"><i class="fa-solid fa-circle-plus"></i>&nbsp;Bcc:</div>
								</div>
							</div>
						</div>
					</div>
					<div class="mt-20 rbox_wpr" style="display: none;">
						<label for="mail_body" class="col-md-4 col-form-label" style="margin-top: 1rem;">
							<?php echo __("宛先 To:"); ?>
						</label>
						<div class="col-md-8">
							<div class="receiver_box_to" style="background-color:#93d4e6;"></div>
						</div>
					</div>
					<div class="rbox_wpr" style="display: none;">
						<label for="mail_body" class="col-md-4 col-form-label " style="margin-top: 1rem;">
							<?php echo __("宛先 CC:"); ?>
						</label>
						<div class="col-md-8">
							<div class="receiver_box_cc" style="background-color:#f8e7b9;"></div>
						</div>
					</div>
					<div class="rbox_wpr" style="display: none;">
						<label for="mail_body" class="col-md-4 col-form-label " style="margin-top: 1rem;">
							<?php echo __("宛先 BCC:"); ?>
						</label>
						<div class="col-md-8">
							<div class="receiver_box_bcc" style="background-color:#e5cce4;"></div>
						</div>
					</div>
				</div>
				<div class="col-md-1"></div>
				<div class="col-md-6 mail_setting" style='display:none;'>
					
				</div>
			</div>
		</div>
		<input type="hidden" name="hid_row_count" id="hid_row_count" class="txtbox" value="<?php echo $rowCount ?>">
		<input type="hidden" name="hid_page_no" id="hid_page_no" class="txtbox" value="<?php echo $page ?>">
		<input type="hidden" name="hid_limit" id="hid_limit" class="txtbox" value="<?php echo $limit ?>">
		<div class="row">
			<div class="form-group align_adjust col-md-12" style="height: 4rem;">
				<div class="col-lg-12">
				<div class="col-lg-12 col-md-5 col-sm-12 d-flex justify-content-end" style="padding-right:0px;">
					<div class="row">
						<div class="col-md-6">
							<input type="button" class="btn-save btn-success btn_sumisho" id="save" name="save" value="<?php echo __('保存'); ?>">
							<input type="button" class="btn-save btn-success btn_sumisho" id="update" name="save" style="display: none;" value="<?php echo __('変更'); ?>">
						</div>
					</div>
				</div>
			</div>
		</div>

		</div>

		<!-- Mail Box Modal -->
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div id="popup-modal" class="modal-dialog modal-dialog-lg" role="document">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header change_color">
						<h5 class="modal-title" id="exampleModalLongTitle"><?php echo __("メール受信者設定"); ?></h5>
						<button type="button" class="close" id="closePop" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="col-md-12">
							<div class="errorMail" id="errorMail"></div>
						</div>
						<div class="receive_div_to">
							<ul class="receiver_limit">
								<?php foreach ($admin_levels as $position => $level_id) : ?>
									<?php foreach ($level_id as $level_ids => $values) : ?>
										<li>
											<div class="d-inline w-80 "><b><?php echo $position; ?></b></div><br/>
											<?php foreach ($layer_data as $layer_no => $layer_name) : ?>
												<?php if($layer_no >= $values) { ?>
													<label class="" style="font-weight: 400;"><input type="radio" id="receive_to_<?php echo($level_ids) ?>" class="receiveto <?php echo($department); ?>" name="receive_to[<?php echo($level_ids) ?>]" value="<?php echo($layer_no) ?>"><?php echo($layer_name) ?></label>
												<?php } ?>
											<?php endforeach ?>
										</li><br>
									<?php endforeach ?>
								<?php endforeach ?>
							</ul>
						</div>
						<div class="receive_div_cc">
							<ul class="receiver_limit">
								<?php foreach ($admin_levels as $position => $level_id) : ?>
									<?php foreach ($level_id as $level_ids => $values) : ?>
										<li>
											<div class="d-inline w-80"><b><?php echo $position; ?></b></div><br/>
											<?php foreach ($layer_data as $layer_no => $layer_name) : ?>
												<?php if($layer_no >= $values) { ?>
													<label style="font-weight: 400;"><input type="radio" id="receive_cc_<?php echo ($level_ids) ?>" class="receivecc <?php echo ($department); ?>" name="receive_cc[<?php echo ($level_ids) ?>]" value="<?php echo ($layer_no) ?>"><?php echo ($layer_name) ?></label>
												<?php } ?>
											<?php endforeach ?>
										</li><br>
									<?php endforeach ?>
								<?php endforeach ?>
							</ul>
						</div>
						<div class="receive_div_bcc">
							<ul class="receiver_limit">
								<?php foreach ($admin_levels as $position => $level_id) : ?>
									<?php foreach ($level_id as $level_ids => $values) : ?>
										<li>
											<div class="d-inline w-80"><b><?php echo $position; ?></b></div><br/>
											<?php foreach ($layer_data as $layer_no => $layer_name) : ?>
												<?php if($layer_no >= $values) { ?>
													<label style="font-weight: 400;"><input type="radio" id="receive_bcc_<?php echo ($level_ids) ?>" class="receivebcc <?php echo ($department); ?>" name="receive_bcc[<?php echo ($level_ids) ?>]" value="<?php echo ($layer_no) ?>"><?php echo ($layer_name) ?></label>
												<?php } ?>
											<?php endforeach ?>
										</li><br>
									<?php endforeach ?>
								<?php endforeach ?>
							</ul>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn  btn_clear to" style="background: #67BFDA !important;"><?php echo __('クリア'); ?></button>
						<button type="button" class="btn btn-secondary btn_clear cc"><?php echo __('クリア'); ?></button>
						<button type="button" class="btn btn-secondary btn_clear bcc"><?php echo __('クリア'); ?></button>
						<button type="button" class="btn btn-success btn_save to" data-dismiss="modal"><?php echo __('保存'); ?></button>
						<button type="button" class="btn btn-success btn_save cc" data-dismiss="modal"><?php echo __('保存'); ?></button>
						<button type="button" class="btn btn-success btn_save bcc" data-dismiss="modal"><?php echo __('保存'); ?></button>
					</div>
				</div>
			</div>
		</div>
		<!-- End Mail Box Modal -->

		<!-- Start Mail Detail Box Modal -->
		<div class="modal fade" id="mailDetail" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-sg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h3 class="modal-title"><?php echo __("メールデータ詳細リスト"); ?></h3>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-12">
									<div class="form-group" id="To" style="display:none">
										<label for="email_to" class="col-form-label">
											<?php echo "To:"; ?>
										</label>
										<textarea id="email_to" class="form-control email_to" autocomplete="off" rows="2" disabled></textarea>
									</div>
									<div class="form-group" id="Cc" style="display:none">
										<label for="email_cc" class="col-form-label">
											<?php echo "CC:"; ?>
										</label>
										<textarea id="email_cc" class="form-control email_cc" autocomplete="off" rows="2" disabled></textarea>
									</div>
									<div class="form-group" id="Bcc" style="display:none">
										<label for="email_bcc" class="col-form-label">
											<?php echo "BCC:"; ?>
										</label>
										<textarea id="email_bcc" class="form-control email_bcc" autocomplete="off" rows="2" disabled></textarea>
									</div>
									<div class="form-group">
										<label for="subject" class="col-form-label">
											<?php echo __("メール件名") . ":"; ?>
										</label>
										<textarea id="subject" class="form-control subject" autocomplete="off" rows="2" disabled></textarea>
									</div>
									<div class="form-group">
										<label for="body" class="col-form-label">
											<?php echo __("メール本文") . ":"; ?>
										</label>
										<textarea id="body" class="form-control body" autocomplete="off" rows="2" disabled></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Mail Detail Box Modal -->
		<br><br>
		<input type="hidden" name="hidSearch" id="hidSearch" value="">
		<!-- Row count message -->
			<!-- search text box-->
			<div class="col-md-12" style="height: 4rem;">
				<table style="width: 100%;">
					<tr>
						<?php if(!empty($succmsg)) {?>
							<td valign="bottom">
								<div class="pull-left msgfont" id="succc">
									<span>
										<?php echo ($succmsg); ?>
									</span>
								</div>
							</td>
						<?php } ?>
						<?php if(empty($errmsg)) { ?>
							<td>
								<div class="form-group pull-right">
									<select class="form-control search_phase" id="s_phase" name="s_phase" value="">
										<option value="">----- Select Menu -----</option>
										<?php foreach ($search_phase as $phase) : ?>
											<?php 
											if ($language == 'eng') $menu_name = $phase['menu']['menu_name_en'];
											else $menu_name = $phase['menu']['menu_name_jp'];
											if ($menu_name == $phase_name) {
												$select = 'selected';
											} else {
												$select = '';
											}
											?>
											<option class="<?php echo $phase['mails']['menu_id']; ?>" value="<?php echo $menu_name; ?>" <?= $select ?>><?php echo $menu_name; ?></option>
										<?php endforeach; ?>
									</select>
									<select class="form-control search_page" id="s_page" name="s_page" value="">
										<option value="">----- Select Page Name -----</option>
									</select>
									<input type="button" style="background-color:#e5ffff;color:white;" class="btn-save btn-success btn_sumisho" value="<?php echo __('検索'); ?>" name="search" onclick="SearchData();">
								</div>
							</td>
						<?php } ?>
					</tr>
				</table>
			</div>
			<input type="hidden" id="s_phase_name" name="s_phase_name" value="<?php echo $phase_name; ?>" />
			<input type="hidden" id="s_page_name" name="s_page_name" value="<?php echo $page_name; ?>" />
		<?php if (!empty($errmsg)) { ?>
			<div id="err" class="no-data">
				<?php echo ($errmsg); ?>
			</div>
		<?php } ?>

		<!-- show data list -->
		<?php if ($rowCount != 0) { ?>
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;overflow-x: auto;">
					<table class="table table-striped table-bordered acc_review" id="tbl_field" style="margin-top:10px;width: 100%;">
						<thead class="check_period_table">
							<tr>
								<th><?php echo __("#"); ?></th>
								<th><?php echo __("メニュー名"); ?></th>
								<th><?php echo __("ページ名"); ?></th>
								<th><?php echo __("処理"); ?></th>
								<th><?php echo __("メールコード"); ?></th>
								<th><?php echo __("メールの詳細"); ?></th>
								<th colspan="2"><?php echo __("Action"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($data_list)) : $no = ($page - 1) * $limit + 1;
								foreach ($data_list as $data) :
									$id = $data['Menu']['id'];
									$mail_code = $data['Menu']['mail_code'];
									$menu_name     = ($language == 'eng') ? $data['Menu']['menu_name_en'] : $data['Menu']['menu_name_jp'];
									$page_name     = ($language == 'eng') ? $data['Menu']['page_name'] : $data['Menu']['page_name_jp'];
									$function      = ($language == 'eng') ? $data['Menu']['method'] : $data['Menu']['method_jp'];
									$function = ucwords(str_replace('_', ' ', $function));
									$mail_code      = $data['Menu']['mail_code'];
									$idstr = $id.",".$mail_code;
							?>
									<tr style="text-align: left;">
										<td><?php echo($no); ?></td>
										<td><?php echo ($menu_name); ?></td>
										<td><?php echo ($page_name); ?></td>
										<td><?php echo ($function); ?></td>
										<td><?php echo ($mail_code); ?></td>
										<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class="link" style="word-break: break-all;text-align:center;vertical-align:middle;">
											<a class="detail_link <?php echo $disable ?>" id="detail_link" href="#" onclick="clickDetail('<?php echo $mail_code; ?>');"><i class="fa-solid fa-list-ul" title="<?php echo __('プレビュー'); ?>"></i></a>
										</td>
										<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class="link" style="word-break: break-all;text-align:center;vertical-align:middle;">
											<a class="edit_link" id="edit_link" href="#" onclick="clickEdit('<?php echo $idstr; ?>');"><i class="fa-regular fa-pen-to-square" title="<?php echo __('編集'); ?>"></i>
											</a>
										</td>
										<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class="link" style="word-break: break-all;text-align:center;vertical-align:middle;">
											<a class="delete_link" href="#" onclick="clickDelete('<?php echo $id; ?>');"><i class="fa-regular fa-trash-can" title="<?php echo __('削除'); ?>"></i>
											</a>
										</td>
									</tr>
							<?php $no++;
								endforeach;
							endif; ?>
						</tbody>
					</table>
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
			</div>
		<?php } ?>
		<!-- End display data list -->
	</div>
</div>
<?php
echo $this->Form->end();
?>