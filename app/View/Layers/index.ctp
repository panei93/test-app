<?php
echo $this->Form->create(false, array('type' => 'post', 'id' => 'Layers', 'name' => 'Layers', 'enctype' => 'multipart/form-data'));
?>
<script type="text/javascript">
	$(document).ready(function() {
		//Hide overlay
		$("#overlay").hide();

		/* float thead */
		if ($('table').length > 0) {
			$('table').floatThead({
				position: 'absolute'
			});
		}
		// $(".layer_option").hide();
		$(".layer_select_div").show();
		$(".layer_select_edit_div").hide();


		if ($(".tbl-wrapper").length) {
			$(".tbl-wrapper").floatingScroll();
		}

		/* date picker settings */
		$(".datepicker").datepicker({
			format: "yyyy-mm-dd",
			autoclose: true,
			forceParse: false,
            todayHighlight:true,
		});

		

		//Select tab according to layer_no
		var layer_no = '<?php echo $layer_no; ?>';
		$(".layer_list").each(function() {
			var order_no = $(this).attr("data-lorder");
			if (layer_no == order_no) {
				$(this).addClass("clicked");
			} else {
				$(this).removeClass("clicked");
			}
		});

		$(".layer_list").on('click', function function_name() {
			//make tab active
			$(".layer_list").each(function() {
				$(this).removeClass("clicked");
			});
			$(this).addClass("clicked");

			//change URL according to clicked layer order
			var order_no = $(this).attr("data-lorder");
			// window.history.pushState('obj', 'newtitle', origin+'/index?layer='+order_no);
			window.location.href = "<?php echo $this->webroot; ?>Layers/index?layer=" + order_no;
			return false;
		});

		// $("#code").on("keydown", function(e) {
		// 	if (($(this).get(0).selectionStart === 0 && (e.keyCode < 35 || e.keyCode > 40)) || ($(this).get(0).selectionStart === 1 && $(this).get(0).selectionEnd === 1 && e.keyCode === 8)) {
		// 		return false;
		// 	}
		// });

		// 	$("#code").bind("contextmenu", function(e) {
		// 	e.preventDefault();
		// 	});

		//Save Data
		$("#btn_save").click(function() {
			/* clear error or success message */
			$("#error").empty("");
			$("#success").empty("");

			/* get field values */
			let name_jp = $("#name_jp").val().trim();
			let name_en = $("#name_en").val().trim();
			let item_1 = $("#item_1").val().trim();
			let item_2 = $("#item_2").val().trim();
			let form = $("#form").val().trim();

			let show_detail = $("#show_detail").val();
			let ids_list;
			let type_order = <?php echo ($layer_no) ?>;
			let layers = <?php echo (json_encode($layers)) ?>;
			let layer_group_name = layers[type_order];
			let parent_group_name = layers[type_order-1] + "<?php echo ' ' . __('名').' '; ?>";

			let from_date = $("#from_date").val();
			let parent_from_date = $("#hid_from_date").val();
			let to_date = $("#to_date").val();
			let parent_to_date = $("#hid_to_date").val();
			if ($("#required_id").val() != "") {
				ids_list = $("#required_id").val().split(",");
			}
			let layerOrder = $("#layer_order").val();
            layerOrder = layerOrder == '' ? 1000 : layerOrder;
            // $("#layer_order").val(layerOrder);

			let chk = true;

			if (!checkNullOrBlank(name_jp)) {
				$("#name_jp").val(name_jp);
				let tmp = layer_group_name + "<?php echo ' ' . __('名'); ?> (JP)";
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, [tmp]) + "</div>");
				chk = false;
			}

			// if (!checkNullOrBlank(name_en)) {
			// 	$("#name_en").val(name_en);
			// 	let tmp = layer_group_name + "<?php echo ' ' . __('名'); ?> (ENG)";
			// 	$("#error").append("<div>" + errMsg(commonMsg.JSE001, [tmp]) + "</div>");
			// 	chk = false;
			// }

			var prev_layer_code = type_order - 1;
			var languageName = document.getElementById('language_name').value;
			var language =  languageName === 'eng' ? languageName.toUpperCase() :  languageName.slice(0, -1).toUpperCase();
			// layerCheck(layers);

			let layer_order =$(".clicked").val() -1;
			let layer_check = $("#layer_name_L"+ layer_order).val();
			if(layer_order != 0){
				if (!checkNullOrBlank(layer_check) ) {
						$("#error").append("<div>" + errMsg(commonMsg.JSE002, [layers[layer_order] + "<?php echo ' ' . __('名'); ?> ("+language+")"]) + "</div>");
						chk = false;
					}
			}
		
			$.each(layers, function(index, layer) {
				
				if(index < prev_layer_code){
						
					if (!checkNullOrBlank($("#layer_name_L" + index).val()) && !!layer === true) {
						$("#error").append("<div>" + errMsg(commonMsg.JSE002, [layer + "<?php echo ' ' . __('名'); ?> ("+language+")"]) + "</div>");
						chk = false;
					}
				}

			});

			if (!checkNullOrBlank(from_date)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("開始日"); ?>']) + "</div>");
				chk = false;
			}
			if (!checkNullOrBlank(to_date)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("終了日"); ?>']) + "</div>");
				chk = false;
			}else{
				var currentDate = new Date();
				if (to_date != '' &&
				new Date(to_date) < new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate())) {
						
					$("#error").append("<div>" + errMsg(commonMsg.JSE079, ['<?php echo __("終了日"); ?>', '<?php echo __("終了日"); ?>']) + "</div>");
					chk = false;
				} 
			}

			//check parent layer group code's from date and to date
			if (!(new Date(from_date)>=new Date(parent_from_date) && new Date(to_date)<=new Date(parent_to_date)) && checkNullOrBlank(from_date) && checkNullOrBlank(to_date) && checkNullOrBlank(parent_from_date) && checkNullOrBlank(parent_to_date)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE092, [parent_group_name])  + "</div>");
				chk = false;
			}

			let code = $("#code").val().trim();
			/* check code only character and number */
			// if (checkNullOrBlank(code) && !checkCharacterNumberOnly(code)) {
			// 	$("#error").append("<div>" + errMsg(commonMsg.JSE081, ['<?php echo __('コード'); ?>']) + "</div>");
			// 	chk = false;
			// }

			/* check validation if has show detail */
			if (show_detail == 1) {
				let manager_code = $("#manager_code").val();
				let object = $("#object").val();

				if (!checkNullOrBlank(manager_code)) {
					$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("部長ID"); ?>']) + "</div>");
					chk = false;
				}
				if (!checkNullOrBlank(object)) {
					$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("サンプルチェック対象"); ?>']) + "</div>");
					chk = false;
				}
			}


			if ($("#required_id").val() != "") {
				if (ids_list.length !== 0) {
					$.each(ids_list, function(index, sub_layer_code) {
						let tmp_value = $("#" + sub_layer_code).val();
						let tmp_name = $("label[for=" + sub_layer_code + "]").html();
						if (!checkNullOrBlank(tmp_value)) {
							$("#error").append("<div>" + errMsg(commonMsg.JSE002, [tmp_name]) + "</div>");
							chk = false;
						}

					});
				}
			}
			/*var sub_busi_show_hide = '<?php echo $sub_busi_show_hide ?>';
			if(sub_busi_show_hide) {
				let bu_status = $("#bu_status").val();
				if (!checkNullOrBlank(bu_status)) {
					$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("画面上表示"); ?>']) + "</div>");
					chk = false;
				}
			}*/
			if (chk) {
				/* set default code value if null */

				if (!checkNullOrBlank(code)) {
					$("#code").val("<?php echo $default_code; ?>");
					code = $("#code").val().trim();
				}
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
								document.forms[0].action = "<?php echo $this->webroot; ?>Layers/saveLayer";
								document.forms[0].method = "POST";
								document.forms[0].submit();
								scrollText();
								// common_component();							
								return true;
							}
						},
						cancel: {
							text: "<?php echo __("いいえ"); ?>",
							btnClass: "btn-default",
							cancel: function() {
								scrollText();
								// common_component();	
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

		//Edit Data
		$("#btn_update").click(function() {
			$("#error").empty();
			$("#success").empty();
			let name_jp = $("#name_jp").val().trim();
			let name_en = $("#name_en").val().trim();
			let from_date = $("#from_date").val();
			let parent_from_date = $("#hid_from_date").val();
			let to_date = $("#to_date").val();
			let parent_to_date = $("#hid_to_date").val();
			let check_parent_id = $("#hid_parent_id").val();
			// let check_has_child = $("#hid_has_child").val();
			let show_detail = $("#show_detail").val();
			let item_1 = $("#item_1").val().trim();
			let item_2 = $("#item_2").val().trim();
			let form = $("#form").val().trim();
			let ids_list;
			let type_order = <?php echo ($layer_no) ?>;
			let layers = <?php echo (json_encode($layers)) ?>;
			let layer_group_name = layers[type_order];
			let parent_group_name = layers[type_order-1] + "<?php echo ' ' . __('名').' '; ?>";
			let layerOrder = $("#layer_order").val();
            layerOrder = layerOrder == '' ? 1000 : layerOrder;
            // $("#layer_order").val(layerOrder);

			let chk = true;
			if ($("#required_id").val() != "") {
				ids_list = $("#required_id").val().split(",");
			}
			if (!checkNullOrBlank(name_jp)) {
				$("#name_jp").val(name_jp);
				let tmp = layer_group_name + "<?php echo ' ' . __('名'); ?> (JP)";
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, [tmp]) + "</div>");
				chk = false;
			}

			// if (!checkNullOrBlank(name_en)) {
			// 	$("#name_en").val(name_en);
			// 	let tmp = layer_group_name + "<?php echo ' ' . __('名'); ?> (ENG)";
			// 	$("#error").append("<div>" + errMsg(commonMsg.JSE001, [tmp]) + "</div>");
			// 	chk = false;
			// }

			var prev_layer_code = type_order - 1;
			var languageName = document.getElementById('language_name').value;
			var language =  languageName == 'eng' ? languageName.toUpperCase() :  languageName.slice(0, -1).toUpperCase();
			if($(".clicked").val() > 1) {
				let layer_order =$(".clicked").val() -1;
				let layer_check = $("#layer_name_L"+ layer_order).val();
				
				if (!checkNullOrBlank(layer_check) ) {
						$("#error").append("<div>" + errMsg(commonMsg.JSE002, [layers[layer_order] + "<?php echo ' ' . __('名'); ?> ("+language+")"]) + "</div>");
						chk = false;
					}

				$.each(layers, function(index, layer) {
							
					if(index < prev_layer_code){
						if (!checkNullOrBlank($(".layer_select_edit_div > #layer_name_L" + index).val())) {
							$("#error").append("<div>" + errMsg(commonMsg.JSE002, [layer + "<?php echo ' ' . __('名'); ?> ("+language+")"]) + "</div>");
							chk = false;
						}
					}

				});
			}
			if (!checkNullOrBlank(from_date)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, ['<?php echo __("開始日"); ?>']) + "</div>");
				chk = false;
			}
			if (!checkNullOrBlank(to_date)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, ['<?php echo __("終了日"); ?>']) + "</div>");
				chk = false;
			}else{
				var currentDate = new Date();
				if (to_date != '' &&
				new Date(to_date) < new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate())) {
						
					$("#error").append("<div>" + errMsg(commonMsg.JSE079, ['<?php echo __("終了日"); ?>', '<?php echo __("終了日"); ?>']) + "</div>");
					chk = false;
				} 
			}
			
			let code = $("#code").val().trim();
			/* check code only character and number */
			// if (checkNullOrBlank(code) && !checkCharacterNumberOnly(code)) {
			// if (checkNullOrBlank(code) && !checkCharacterNumberOnly(code)) {
			// 	$("#error").append("<div>" + errMsg(commonMsg.JSE081, ['<?php echo __('コード'); ?>']) + "</div>");
			// 	chk = false;
			// }

			/* check validation if has show detail */
			if (show_detail == 1) {
				let manager_code = $("#manager_code").val();
				let object = $("#object").val();
				if (!checkNullOrBlank(manager_code)) {
					$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("部長ID"); ?>']) + "</div>");
					chk = false;
				}

				if (!checkNullOrBlank(object)) {
					$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("サンプルチェック対象"); ?>']) + "</div>");
					chk = false;
				}
			}

			/* check parent layer group code's from date and to date */
			if(check_parent_id == '' || check_parent_id == null){
				// if (!(new Date(from_date)<=new Date($("#hid_fromdate").val()) && new Date(to_date)>=new Date($("#hid_todate").val())) && checkNullOrBlank(from_date) && checkNullOrBlank(to_date) && check_has_child == 'true') {
				// 	$("#error").append("<div>" + errMsg(commonMsg.JSE093)  + "</div>");
				// 	chk = false;					
				// }
			}else{
			if (!(new Date(from_date)>=new Date(parent_from_date) && new Date(to_date)<=new Date(parent_to_date)) && checkNullOrBlank(from_date) && checkNullOrBlank(to_date) && checkNullOrBlank(parent_from_date) && checkNullOrBlank(parent_to_date)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE092, [parent_group_name])  + "</div>");
				chk = false;
				}
			}
			
			var sub_busi_show_hide = '<?php echo $sub_busi_show_hide ?>';
			if(sub_busi_show_hide) {
				let bu_status = $("#bu_status").val();
				if (!checkNullOrBlank(bu_status)) {
					$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("BU 概要レイヤー状態"); ?>']) + "</div>");
					chk = false;
				}
			}
			if (chk) {
				/* set default code value if null */
				if (!checkNullOrBlank(code)) {
					$("#code").val($("#code").prop("placeholder"));
					code = $("#code").val().trim();
				}
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
								document.forms[0].action = "<?php echo $this->webroot; ?>Layers/saveLayer";
								document.forms[0].method = "POST";
								document.forms[0].submit();
								// common_component();	
								return true;
							}
						},
						cancel: {
							text: '<?php echo __("いいえ"); ?>',
							btnClass: 'btn-default',
							cancel: function() {
								
								scrollText();
								// common_component();								
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

		$("#to_date").on("change", function(e) {
			var currentDate = new Date();
			if ($(this).val() != '' &&
				new Date($(this).val()) < new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate())) {
				$("#error").empty("");
				$("#success").empty();
				$("#error").append("<div>" + errMsg(commonMsg.JSE079, ['<?php echo __("終了日"); ?>', '<?php echo __("終了日"); ?>']) + "</div>");
				// $('#btn_update').attr("disabled", 'disabled');
				// $('#btn_save').attr("disabled", 'disabled');
			} else {
				// $("#error").empty("");
				// $("#success").empty();
				// $("#btn_update").removeAttr('disabled');
				// $("#btn_save").removeAttr('disabled');
			}
		});

		$('.layer_select').on('change', function() {
			let id_txt = $(this).attr('id');	
			let layer_name = id_txt.substr(id_txt.length - 2);
			let layer_code = $(this).val();
			let next_layer = parseInt(layer_name.substr(layer_name.length - 1)) + 1;

			getParentData(layer_name,layer_code,next_layer,'save');
		});

		$('.layer_select_edit').on('change', function() {
			let id_txt = $(this).attr('id');	
			let layer_name = id_txt.substr(id_txt.length - 2);
			let layer_code = $(this).val();
			
			let next_layer = parseInt(layer_name.substr(layer_name.length - 1)) + 1;
			
			getParentData(layer_name,layer_code,next_layer,'edit');
		});

	});

	function getParentData(layer_name,layer_code,next_layer,mode){
		let layers = <?php echo (json_encode($layers)) ?>;
		var last_layer = "L"+(<?php echo ($layer_no); ?> - 1);
		
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>Layers/getChildData",
			data: {
				layer_name: layer_name,
				layer_code: layer_code,
				next_layer: next_layer
			},
			dataType: 'json',
			success: function(datas) {
				if (datas.length == 0) {
					$('#layer_name_'+last_layer+' option[value=""]').prop('selected', true);
				}else {
					datas['value'][last_layer] = layer_code;
					$.each(datas['value'], function(key, value) {
						$('#layer_name_'+key+' option[value="'+value+'"]').prop('selected', true);
					});
					$("#hid_from_date").val(datas['from_date']);
					$("#hid_to_date").val(datas['to_date']);
				}
			}
			
		});
	}

	function clickEdit(id, haveChild) {
		$("#error").empty();
		$("#success").empty();
		$(".layer_option").show();
		/* set value according to headquarter id */
		$("#hid_update_id").val(id);
		let show_detail = $("#show_detail").val();
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>Layers/getEditData",
			data: {
				id: id,
				haveChild: haveChild
			},
			dataType: "json",
			beforeSend: function() {
            			loadingPic(); 
            		},
			success: function(data) {
				if(data['check_bu_status']) {
					$("#bu_status").attr('disabled', true);
				}else {
					$("#bu_status").attr('disabled', false);
				}
				$("#hid_from_date").val(data['from_date']);
				$("#hid_to_date").val(data['to_date']);
				$("#hid_parent_id").val(data['parent_id']);
				// $("#hid_has_child").val(data['has_child']);
				//Show error message if expired
				if (data['expired'] == true) $("#error").append("<div>" + errMsg(commonMsg.JSE079, ['<?php echo __("終了日"); ?>', '<?php echo __("終了日"); ?>']) + "</div>");
				
				$(".layer_select_div").hide();
				$(".layer_select_edit_div").show();
				let layer_data = data["data_list"];
				let dropdown_layer_data = data["layer"]['layer_data'];
				let parent_data = (layer_data["parent_id"] == null || layer_data["parent_id"] == "") ? "" : jQuery.parseJSON(layer_data["parent_id"]);
				var L = 1;
				let type_name = (data["layer_type_name"] != undefined) ? data["layer_type_name"] : '';
				let layerOrder = data["data_list"]["layer_order"];
				
				var languageName = document.getElementById('language_name').value;
				if(type_name != '') {
					for(var i =0;i<dropdown_layer_data.length;i++){
						let d_html = '';
						
						$.each(type_name[i],function (key,value) {
							d_html += "<option value=''>----- Select "+value+" -----</option>";
						})
						$.each(dropdown_layer_data[i],function(key,value){
							
							d_html += "<option value='" + key + "' >" + key+"/"+value + "</option>";
		
						})
						var layer_no = '<?php echo $layer_no; ?>';
						if(L != (layer_no - 1)) $(".layer_select_edit_div > #layer_name_L" +L).attr('readonly', true);
						$(".layer_select_edit_div > #layer_name_L" +L).empty();
						$(".layer_select_edit_div > #layer_name_L" +L).append(d_html);
						d_html = "";
						
						L++;
					}
				}
				let id = layer_data["id"];
				let code = layer_data["layer_code"];
				let name_jp = layer_data["name_jp"];
				let name_en = layer_data["name_en"];
				let from_date = layer_data["from_date"];
				let to_date = layer_data["to_date"];
				let parent_id = layer_data["parent_id"];
				let item_1 = layer_data["item_1"];
				let item_2 = layer_data["item_2"];
				let form = layer_data["form"];
				$(".from.datepicker").datepicker("setDate",from_date);
                $(".to.datepicker").datepicker("setDate",to_date);
				$("#hid_code").val(code);
				$("#code").val(code);
				$("#exist_code_id").val(data['exist_code']);
				$("#code").prop('placeholder', code);
				/*not allow to edit any condition(khin-28/03/2023)*/
				$("#code").prop('readonly', true);
				// if (data["code_readonly"] == 1) {
				// 	$("#code").prop('readonly', true);
				// }else $("#code").prop('readonly', false);
				$("#name_jp").val(name_jp);
				$("#name_en").val(name_en);
				$("#from_date").val(from_date);
				$("#to_date").val(to_date);
				$("#hid_fromdate").val(from_date);
				$("#hid_todate").val(to_date);
				$("#hid_parents").val(parent_id);
				$("#org_id").val(layer_data["layer_code"]);
				$("#item_1").val(item_1);
				$("#item_2").val(item_2);
				$("#form").val(form);
				$("#layer_order").val(layerOrder);
				
				if (parent_data != null && parent_data != "") {
					
					$arr = [];
					$.each(parent_data, function(key, value) {
						$arr.push(value);
					})
	
					$arr.map((x,index)=>{
						
						let j = index+1;
						
						$('#layer_name_L' + j + ' option[value="' + x + '"]').prop('selected', true);
					});
				}
		
				if (show_detail == 1) {
					let managers = layer_data["managers"];
					let object = layer_data["object"];
					$("#manager_code").val(managers);
					$("#object").val(object);
				}
				var sub_busi_show_hide = '<?php echo $sub_busi_show_hide; ?>';
				if (sub_busi_show_hide) {
					let bu_status = layer_data["bu_status"];
					$("#bu_status").val(bu_status);
				}
				/* button show or hide for update and save */
				$('#overlay').hide();
				$("#btn_save").hide();
				$("#btn_update").show();
				var currentDate = new Date();
				var current_month = currentDate.getMonth() + 1;
				var current_date = currentDate.getFullYear() + '-' + current_month + '-' + currentDate.getDate();

				var toDate = new Date(data["to_date"]);
				var to_month = toDate.getMonth() + 1;
				to_date = toDate.getFullYear() + '-' + to_month + '-' + toDate.getDate();

				// if (data["to_date"] != '' && new Date(to_date) < new Date(current_date)) $('#btn_update').attr("disabled", 'disabled');
				// else $("#btn_update").removeAttr('disabled');
			}
		});
	}

	function clickDelete(id) {
		$("#error").empty();
		$("#success").empty();

		$("#hid_delete_id").val(id);
		let path = window.location.pathname;
		let front = path.split("?")[0];
		let page = front.split("/").pop();
		
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
						document.forms[0].action = "<?php echo $this->webroot; ?>Layers/removeLayer";
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

	function viewHistory(id) {
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>Layers/getHistoryData",
			data: {
				id: id
			},
			dataType: "json",
			success: function(data) {
				$('.history').html('');
				var table_html = '';
				table_html += '<tr>';
				table_html += '<th><?php echo __('部署名'); ?></th>';
				table_html += '<th><?php echo __('開始日'); ?></th>';
				table_html += '<th><?php echo __('終了日'); ?></th>';
				table_html += '</tr>';
				var name = data['0']['lan_name'];
				$.each(data, function(key, value) {
					table_html += '<tr>';

					var padding = 2;
					table_html += '<td>';
					$.each(value['parent_data'], function(k, v) {
						table_html += '<li style="list-style-type: none;"><i class="fa fa-caret-right" aria-hidden="true" style="padding-left: '+padding+'px;"></i>&nbsp;' + v + '</li>';
						padding += 40;
					});
					
					table_html += '<li style="list-style-type: none;"><i class="fa fa-caret-right" aria-hidden="true" style="padding-left: '+padding+'px;"></i>&nbsp;' + value['Layer'][name] + '</li></td>';
					
					table_html += '<td style="vertical-align:bottom;text-align:center;">' + value['Layer']['from_date'] + '</td>';
					table_html += '<td style="vertical-align:bottom;text-align:center;">' + value['Layer']['to_date'] + '</td>';
					table_html += '</tr>';

				});

				$('.history').append(table_html);
			}
		});

	}


    /*  
	* show hide loading overlay
	*@Zeyar Min  
	*/
	function loadingPic() { 
			$("#overlay").show();
            $('.jconfirm').hide();  
	}

	// function endLoadingPic() { 
	// 		$("#overlay").hide();
    //         $('.jconfirm').show();  
	// }

	/* check value character and number only */
	function checkCharacterNumberOnly(value) {
		var engstr = /^[a-zA-Z0-9]*$/;
		if (engstr.test(value)) {
			return true;
		}
		return false;
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

<style type="text/css">
	.layer_tab {
		margin-left: 0;
		margin-bottom: 0px;
		padding-left:15px;
	}

	.layer_tab li {
		padding: 10px 15px;
		border-top-left-radius: 3px !important;
		border-top-right-radius: 3px !important;
		border-bottom-left-radius: 0 !important;
		border-bottom-right-radius: 0 !important;
		display: table-cell;
		cursor: pointer;
		background-color: #eee;
		/*border-left: 0px;*/
	}

	.layer_tab li:hover {
		background-color: #fff;
	}

	.layer_tab li.clicked {
		background-color: #fff;
	}

	.layer_div {
		margin-left:15px;
		border: 1px solid #ddd;
		padding: 40px 20px;
	}

	.history_link.disabled {
		pointer-events: none;
		color: #ddd;
	}

	#view_history_popup.modal {
		z-index: 2100;
	}

	.jconfirm-box-container {
      margin-left: unset !important;
   }
    .modal-dialog {
   		width: 70% !important;
    }
    .layer_select_div select[readonly], .layer_select_edit_div select[readonly] {
		pointer-events: none;
   	}
</style>

<input type="hidden" id="hid_from_date" value=""/>
<input type="hidden" id="hid_to_date" value=""/>
<input type="hidden" id="hid_parent_id" value=""/>
<!-- <input type="text" id="hid_has_child" value=""/> -->
<div id="overlay">
	<span class="loader"></span>
</div>
<div class="content register_container">
	<div class="register_form">
		<div class="row" style="font-size: 0.95em;">
			<div class="col-md-12 col-sm-12 heading_line_title">
				<h3><?php echo __("事業領域管理"); ?></h3>
				<hr>
			</div>
		</div>

		<!-- hidden field for delete -->
		<input type="hidden" name="hid_delete_id" id="hid_delete_id">
		<!-- hidden field for update -->
		<input type="hidden" name="hid_update_id" id="hid_update_id">
		<input type="hidden" name="hid_fromdate" id="hid_fromdate">
		<input type="hidden" name="hid_todate" id="hid_todate">
		<input type="hidden" name="hid_parents" id="hid_parents">
		<input type="hidden" name="exist_code_id" id="exist_code_id">
		<!-- hidden field for page no. -->
		<input type="hidden" name=hid_page_no id="hid_page_no">
		<!-- hidden field for required id -->
		<input type="hidden" name="required_id" id="required_id">
		<!-- hidden field for show detail -->
		<input type="hidden" name="show_detail" id="show_detail" value="<?php echo $show_detail ?>">
		<!-- hidden field for layer order -->
		<input type="hidden" name="type_order" id="type_order" value="<?php echo ($layer_no) ?>">
		<input type="hidden" name="layer_type_id" id="layer_type_id" value="<?php echo ($layer_type_id) ?>">
		<input type="hidden" name="org_id" id="org_id" value="">

		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 0;">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.SuccessMsg")) ? $this->Flash->render("SuccessMsg") : ''; ?><?php echo ($this->Session->check("Message.LayerGroupSuccess")) ? $this->Flash->render("LayerGroupSuccess") : ""; ?></div>
			<div class="error" id="error"><?php echo ($this->Session->check("Message.ErrorMsg")) ? $this->Flash->render("ErrorMsg") : ''; ?><?php echo ($this->Session->check("Message.LayerGroupFail")) ? $this->Flash->render("LayerGroupFail") : ""; ?></div>
		</div>
		
		<?php if (count($layers) > 0) : ?>
			<ul class="layer_tab list-group list-group-horizontal list-inline">
				<?php foreach ($layer_tab as $type_order => $layer) : ?>
					<li class="list-group-item layer_list noselect" data-lorder="<?php echo ($type_order) ?>" value="<?php echo ($type_order) ?>" ><?php echo ($layer) ?></li>
				<?php endforeach ?>
			</ul>
			<div class="tab_<?php echo ($type_order) ?> layer_div" id="layer_div">
				<!-- <div class="form-group row" id="append_row"> -->
					<div class="row">
				<div class="col-md-6">
					<!-- code -->
					<label for="code" class="col-md-5 control-label show_detail">
						<?php echo __('コード'); ?>
					</label>
					<div class="col-md-7 show_detail" style="margin-bottom: 15px;">
						<input type="text" class="form-control" id="code" name="code" value="<?php echo $default_code; ?>" maxlength="500" placeholder="<?php echo $default_code; ?>" />
					</div>

					<!-- name jp -->
					<label for="name_jp" style="margin-bottom: 15px;" class="col-md-5 control-label required">
						<?php echo $layers[$layer_no] . ' ' . __('名'); ?> (JP)
					</label>
					<div class="col-md-7" style="margin-bottom: 15px;">
						<input type="text" class="form-control" id="name_jp" name="name_jp" value="" maxlength="500" />
					</div>
					
					<!-- name en -->
					<label for="name_en" class="col-md-5 control-label">
						<?php echo $layers[$layer_no] . ' ' . __('名'); ?> (ENG)
					</label>
					<div class="col-md-7" style="margin-bottom: 15px;">
						<input type="text" class="form-control" id="name_en" name="name_en" value="" maxlength="500" />
					</div>
				
					<!-- layer -->
					<?php 
					$language = $this->Session->read('Config.language');
				
					if (isset($parents)) : ?>
						<?php foreach ($cur_layer_prev as $layer_no => $layer_data) : ?>

							<label for="layer_name_<?php echo ($layer_no + 1) ?>" style="margin-bottom: 15px;"  class="col-md-5 control-label <?php if(end($parents)['LayerType']['id'] != $layer_data['LayerType']['id']) : echo 'required'; endif;?>">
								<?php echo $layer_data['LayerType']['layerTypeName'] . ' ' . __('名'); ?> 
								<?php  if ($language == 'eng') { echo "(ENG)";}else{echo "(JP)";}?>
							</label>
							<div class="col-md-7 layer_select_div" style="margin-bottom: 15px;">
								<select class="form-control layer_select" id="layer_name_L<?php echo ($layer_no + 1) ?>" name="save_parent_id[L<?php echo ($layer_data['LayerType']['type_order']) ?>]" >
									<option value="">----- <?php echo __("Select");?> <?php echo $layer_data['LayerType']['layerTypeName']; ?> -----</option>
									<?php foreach ($layer_data['LayerType']['layers'] as $layer_code => $layer_name) : ?>
										<option class="<?php if($layer_data['LayerType']['type_order'] == 1){ echo "layer_option_order_1"; } else {echo "layer_option";} ?>" 
												value="<?php echo $layer_code; ?>"><?php echo $layer_code."/".$layer_name; ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="col-md-7 layer_select_edit_div" style="margin-bottom: 15px;display: none;">
							<?php //<?php echo ($layer_no + 1) ?>

								<select class="form-control layer_select_edit" id="layer_name_L<?php echo ($layer_no + 1) ?>" name="parent_id[L<?php echo ($layer_data['LayerType']['type_order']) ?>]">
									<option value="">----- <?php echo __("Select");?> <?php echo $layer_data['LayerType']['layerTypeName']; ?> -----</option>
								</select>
							</div>
						<?php endforeach ?>
						

						<?php foreach ($parents as $layer_no => $layer_data) : ?>
					
							<label for="layer_name_<?php echo ($layer_no + 1) ?>" style="margin-bottom: 15px;" class="col-md-5 control-label required">
								<?php echo $layer_data['LayerType']['layerTypeName'] . ' ' . __('名'); ?> 
								<?php  if ($language == 'eng') { echo "(ENG)";}else{echo "(JP)";}?>
							</label>

							<div class="col-md-7 layer_select_div" style="margin-bottom: 15px;">
								<select class="form-control layer_select" id="layer_name_L<?php echo ($layer_no + 1) ?>" name="save_parent_id[L<?php echo ($layer_data['LayerType']['type_order']) ?>]" readonly>
									<option value="">----- <?php echo __("Select");?> <?php echo $layer_data['LayerType']['layerTypeName']; ?> -----</option>
									<?php foreach ($layer_data['LayerType']['layers'] as $layer_code => $layer_name) : ?>
										<option class="<?php if($layer_data['LayerType']['type_order'] == 1){ echo "layer_option_order_1"; } else {echo "layer_option";} ?>" 
												value="<?php echo $layer_code; ?>"><?php echo $layer_code."/".$layer_name; ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="col-md-7 layer_select_edit_div" style="margin-bottom: 15px;display: none;">
							<?php //<?php echo ($layer_no + 1) ?>

								<select class="form-control layer_select_edit" id="layer_name_L<?php echo ($layer_no + 1) ?>" name="parent_id[L<?php echo ($layer_data['LayerType']['type_order']) ?>]">
									<option value="">----- <?php echo __("Select");?> <?php echo $layer_data['LayerType']['layerTypeName']; ?> -----</option>
								</select>
							</div>
						<?php endforeach ?>
					<?php endif ?>

					<input type="hidden" id="language_name" name="language_name" value="<?php echo $language; ?>"  />	

				</div> 

				<div class="col-md-6">
					
					<!-- from date -->
					<label for="from_date" class="col-md-5 control-label required show_detail">
						<?php echo __("開始日"); ?>
					</label>
					<div class="col-md-7 show_detail" style="margin-bottom: 15px;">
						<div class="input-group date from datepicker" data-provide="datepicker" style="padding: 0px;">
							<input type="text" class="form-control" id="from_date" name="from_date" value="" autocomplete="off" style="background-color: #fff;" readonly>
							<span class="input-group-addon">
								<span class="glyphicon glyphicon-calendar"></span>
							</span>
						</div>
					</div>
					
					<!-- to date -->
					<label for="to_date" class="col-md-5 control-label required show_detail">
						<?php echo __("終了日"); ?>
					</label>
					<div class="col-md-7 show_detail" style="margin-bottom: 15px;">
						<div class="input-group date to datepicker" data-provide="datepicker" style="padding: 0px;">
							<input type="text" class="form-control" id="to_date" name="to_date" value="" autocomplete="off" style="background-color: #fff;" readonly>
							<span class="input-group-addon">
								<span class="glyphicon glyphicon-calendar"></span>
							</span>
						</div>
					</div>

					<!-- item_1 -->
					<label for="item_1" class="col-md-5 control-label">
						<?php echo __("Item 1"); ?>
					</label>
					<div class="col-md-7" style="margin-bottom: 15px;">
						<input type="text" class="form-control" id="item_1" name="item_1" value="" autocomplete="off">
					</div>

					<!-- item_2 -->
					<label for="item_2" class="col-md-5 control-label">
						<?php echo __("Item 2"); ?>
					</label>
					<div class="col-md-7" style="margin-bottom: 15px;">
						<input type="text" class="form-control" id="item_2" name="item_2" value="" autocomplete="off">
					</div>

					<!-- form -->
					<label for="form" class="col-md-5 control-label">
						<?php echo __("Form"); ?>
					</label>
					<div class="col-md-7" style="margin-bottom: 15px;">
						<input type="text" class="form-control" id="form" name="form" value="" autocomplete="off">
					</div>
					<label for="layer_order" class="col-md-5 control-label">
						<?php echo __("部署順番"); ?>
					</label>
					<div class="col-md-7" style="margin-bottom: 15px;">
						<input type="text" class="form-control" id="layer_order" name="layer_order" value="" autocomplete="off" maxlength="4">
					</div>
					<!-- show/hide sub_business in BU Summary -->
					<?php if ($sub_busi_show_hide) { ?>
						<label for="bu_status" class="col-md-5 control-label">
							<?php echo __("画面上表示"); ?>
						</label>
						<div class="col-md-7" style="margin-bottom: 15px;">
							<select class="form-control" id="bu_status" name="bu_status">
								<option value="1">TRUE</option>
								<option value="0">FALSE</option>
							</select>
						</div>
					<?php } ?>

					<?php if ($show_detail) : ?>
						<label for="manager_code" class="col-md-5 control-label required show_detail">
							<?php echo __("部長ID"); ?>
						</label>
						<div class="col-md-7 show_detail" style="margin-bottom: 15px;">
							<select class="form-control" id="manager_code" name="manager_code">
								<option value="">----- <?php echo __("Select").' '.__("部長ID");?> -----</option>
								<?php foreach ($managerss as $managers => $login_id) : ?>
									<option value="<?php echo $login_id; ?>"><?php echo $login_id; ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<label for="object" class="col-md-5 control-label required show_detail">
							<?php echo __("サンプルチェック対象"); ?>
						</label>
						<div class="col-md-7 show_detail" style="margin-bottom: 15px;">
							<select class="form-control" id="object" name="object">
								<option value="">----- <?php echo __("Select").' '.__("サンプルチェック対象");?> -----</option>
								<option value="1">TRUE</option>
								<option value="0">FALSE</option>
							</select>
						</div>
					<?php endif; ?>
				
				</div>
				</div>

				<div class="form-group row" style="margin-bottom: 40px;margin-right : 0px;">
					<div class="col-md-12 " id="save">
						<input type="button" class="btn-save-wpr btn-save pull-right"  id="btn_save" name="btn_save" value="<?php echo __('保存'); ?>">
						<input type="button" class="btn-save-wpr btn-save pull-right" id="btn_update" name="btn_save" style="display: none;margin-right:unset;" value="<?php echo __('変更'); ?>">
					</div>
				</div>

				<?php if (count($list) > 0) : ?>
					<div class="msgfont" id="succc">
						<?php if (!empty($succmsg)) { ?>
							<div class="msgfont" id="succc"><?php echo ($succmsg); ?></div>
						<?php } ?>
					</div>
					<?php 
					
					$layerArr = [];
					foreach($layers as $key=>$val){
						array_push($layerArr,$val);
						?>
						<?php } 
						?>
					<div class="table-responsive tbl-wrapperd required-css">
						<table class="table table-striped table-bordered acc_review" id="tbl_id" style="margin-top:10px;width: 100%;">
							<thead>
								<tr>
									<?php foreach ($title_fields as $table_title) : ?>
										<?php if ($table_title == 'PARENT ID') : ?>
											<?php foreach ($cur_layer_prev as $layer_no => $layer_data) : ?>
												<th>
													<?php echo $layerArr[$layer_no] . ' ' . __('名'); ?> 
													<?php  if ($language == 'eng') { echo "(ENG)";}else{echo "(JP)";}?>
												</th>
											<?php endforeach ?>
											<?php foreach ($parents as $layer_no => $layer_data) : ?>
												<th>
													<?php echo $layerArr[$layer_no] . ' ' . __('名'); ?> 
													<?php  if ($language == 'eng') { echo "(ENG)";}else{echo "(JP)";}?>
												</th>
											<?php endforeach ?>
										<?php else : ?>
											<th><?php echo __($table_title) ?></th>
										<?php endif ?>
									<?php endforeach ?>
									<th colspan="3"><?php echo __("アクション");?></th>
								</tr>
							</thead>
							<tbody>
								<?php $numb = 1 ?>
								<?php foreach ($list as $layerg_list) : ?>
									<tr>
										<td><?php echo ($numb) ?></td>
										<?php foreach ($layerg_list['layer_data'] as $key => $value) : ?>
											<?php if ($key != 'id' &&  $key != 'parent_id') : ?>
												
												<?php $value = ($key == "object") ? (($value) ? 'TRUE' : 'FALSE') : $value ?>
												<?php $value = ($key == "bu_status") ? (($value) ? 'TRUE' : 'FALSE') : $value ?>
												<td><?php echo ($value) ?></td>
											<?php else : ?>
												<?php if ($key == "parent_id" && $value != "") : ?>
													<?php $value = json_decode($value, true);
														$lno = count($value);
														$k = 0;
													?>
													<td><?php echo ($cur_layer_prev[($lno-1)]['LayerType']['layers'][$value['L'.$lno]]) ?></td>
													<?php unset($value['L'.$lno]);
													foreach ($value as $pkey => $pvalue) : ?>
														<td><?php echo ($parents[$k]['LayerType']['layers'][$pvalue]) ?></td>
														<?php $k++; ?>
													<?php endforeach ?>
												<?php else : ?>
												<?php endif ?>
											<?php endif ?>
										<?php endforeach ?>
										<?php  $haveChild = $layerg_list['exist_child'] !== 0 ? true : false ; ?>
										<td style="word-break: break-all;text-align: center;width: fit-content; vertical-align:middle;font-size:1.3em !important; ">
											<a class='edit_link' id='edit_link' href='#' onclick="clickEdit(<?php echo ($layerg_list['layer_data']['id']) ?>, <?php echo $haveChild; ?>)" ; title='<?php echo __("編集");?>'><i class="fa-regular fa-pen-to-square"></i></a>
										</td>
										<?php  $dis_style = $layerg_list['exist_child'] !== 0 ? "cursor: not-allowed;pointer-events: none;opacity: 0.5" : "" ; ?>
										<td style="word-break: break-all;text-align: center;width: fit-content; vertical-align:middle;font-size:1.3em !important; ">
											<a class='delete_link' href='#' style='<?php echo $dis_style; ?>' onclick="clickDelete(<?php echo ($layerg_list['layer_data']['id']) ?>)" ; title='<?php echo __("削除");?>'><i class="fa-regular fa-trash-can"></i></a>
										</td>
										<td style="word-break: break-all;text-align: center;width: fit-content; vertical-align:middle;font-size:1.3em !important; ">
											<?php $disable = ($layerg_list['history_count']) > 0 ? "" : "disabled" ?>
											<a class='history_link <?php echo ($disable) ?>' data-target='#view_history_popup' data-toggle='modal' data-backdrop='static' data-keyboard='false' href='#' onclick="viewHistory(<?php echo ($layerg_list['layer_data']['id']) ?>)" ; title='<?php echo __("履歴");?>'><i class="fa-solid fa-list-ul"></i></a>
										</td>
									</tr>
									<?php $numb++; ?>
								<?php endforeach ?>
							</tbody>
						</table>
					</div>

					<!-- for data list -->
				<?php else : ?>
					<div id="err" class="no-data"> <?php echo ($errmsg); ?></div>
				<?php endif; ?>
			</div>
			<!-- for layers -->
		<?php else : ?>
			<div id="err" class="no-data"> <?php echo ($errmsg); ?></div>
		<?php endif; ?>
	</div>
</div>
<?php if (!empty($list)) { ?>
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
</div><!-- Modal -->
<div class="modal fade" id="view_history_popup" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content contantbond">
			<div class="modal-header">
				<button type="button" class="close" id="clearData" data-dismiss="modal">&times;</button>
				<h5 class="modal-title" id="exampleModalScrollableTitle"><?php echo __("履歴"); ?></h5>
			</div>
			<div class="modal-body" style="max-height: 500px; overflow-y: auto;">
				<div class="modal_tbl_wrapper">
					<table class="table table-striped table-bordered" id="tbl_history_Popup">
						<tbody class="sortable history">
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>