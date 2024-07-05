
	<?php       
	echo $this->Form->create(false,array('type'=>'post', 'name' =>'actual_result_form' ,'id' => 'actual_result_form', 'enctype'=> 'multipart/form-data'));
	?>
	<?php
    echo $this->element('autocomplete', array(
                        "to_level_id" => "",
                        "cc_level_id" => "",
                        "bcc_level_id" => "",
                        "submit_form_name" => "actual_result_form",
                        "MailSubject" => "",
                        "MailTitle"   => "",
                        "MailBody"    =>""
                     ));
?>


	<input type="hidden" name="toEmail"  id="toEmail" value="">
	<input type="hidden" name="ccEmail"  id="ccEmail" value="">
	<input type="hidden" name="bccEmail"  id="bccEmail" value="">
	<input type="hidden" name="mailSubj"  id="mailSubj">
	<input type="hidden" name="mailTitle" id="mailTitle">
	<input type="hidden" name="mailBody" id="mailBody">
	<input type="hidden" name="mailSend" id="mailSend">

	<style type="text/css">
		.remove_filelink {
			text-align: center;
			margin-left: -2rem;
		}
		.upd-file-name {
			margin-left: 15px;
		}
		.file_remove {
			/* border-bottom: 1px solid !important; */
			display: none;
			color: red !important;
			cursor: pointer;			
			margin-left: 1rem;
		}
		a.file_remove :hover { 
			text-decoration: none !important;
		}
		.actual_file {
			background-color: #f09282;
			border: none;
			color: white;
			padding: 5px 25px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			font-size: 14px;
			border-radius: 5px;
		}
		
		.form-horizontal .control-label {
			text-align: left !important;
		}
		.negative {
			color: #f31515;
			text-align: right !important;
		}
		.string {
			text-align: left !important;
		}
		.number {
			text-align: right !important;
		}
		.mail {
			width: 120px;
		}
		.mrbrd_mail {
			width: 230px;
			padding: 7px 25px;
		}

		.row.line.adjust {
			padding-top: 50px;
		}
		.search{
			margin: 10px 0px 15px 0px;
		}
		.total_count {
				display: inline-block;
		}
		.no-data {
			padding-bottom: 30px;
		}
		.popup_row {
			padding-bottom: 50px;
		}
		.modal-dialog.modal-lg {
			width: 100%;
			padding: 10px 150px;
		}
		.popup_nodata {
			text-align: center;
			color: red;
		}
		@media only screen and (max-width: 991px){
				.popup_row {
					padding-bottom: 0px !important;
				}
				.modal-dialog.modal-lg {
					width: 100%;
					padding: 10px 50px;
				}
			.rep_lbl{
				padding-top: 10px;
			}
		}

		/* css for tooltip */
		.tooltip-inner {
		    background: #ffd2d2;
		  	color: #ff3333;
		  
		}
		.tooltip.bottom .tooltip-arrow {
		    border-bottom-color: #ffd2d2;
		   
		}

		/* css for ui-autocomplete */
		.ui-autocomplete {
            max-height: 192px;
            overflow-y: auto;
            /* prevent horizontal scrollbar */
            overflow-x: hidden;
        } 
		.btn_sumisho{
			margin: 0px 0px 10px 10px
		}
		.temp-download {
			line-height: 2.5rem;
		}
		
	</style>

	<script type="text/javascript">

		$(document).ready(function() { 
			
			/* float thead heade fixed when scroll */
			if($('#tbl_actual').length > 0) {
				var $table = $('#tbl_actual');
				$table.floatThead({
							position: 'absolute'
				});
			}
				
			if($(".tbl-wrapper").length) {
				$(".tbl-wrapper").floatingScroll();
			}

			//date time picker
			$('.datepicker').datepicker({
            	format: 'yyyy/mm/dd'
        	});
        	
			/* end*/

			/* logistic index autocomplete by HHK */
			function split( val ) {
				return val.split( /,\s*/ );
			}
			function extractLast( term ) {
				return split( term ).pop();
			}
			$("#logistic_index").autocomplete({
				source: function (request, response) {
					
					var b_code = $('#b_code').val();
					var acc_code = $('#acc_code').val();
					var t_month = $('#t_month').val();
					var logistic_index = $('#logistic_index').val();
					var search_val = extractLast( logistic_index );

					if (search_val != "") {
						var search_data = {
							searchValue : search_val,
							b_code : b_code,
							acc_code : acc_code,
							t_month : t_month
						};
						$.ajax({
							url: "<?php echo $this->Html->url(array('controller'=>'BrmActualResult','action'=>'autoCompleteLogistic')) ?>",
							dataType: "json",
							type: "post",
							data: search_data,
							success: function (data) {
													
								if (data != "") {
									response(data);
									$("#logistic_index").tooltip("destroy");
								}
							},
							error: function () {
								$("#logistic_index").tooltip({
					        			trigger: "focus",
					        			placement: "bottom",
				        				template: '<div class="tooltip tooltip-error" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
				        		}).attr("data-original-title", "No match found!");
				        		$("#logistic_index").tooltip("show");
								$(".ui-autocomplete").empty();
							}
						});	 
					} else {
						$(".ui-autocomplete").empty();
						$("#logistic_index").tooltip("destroy");
					}
				},
				autoFocus: true,
				select: function( event, ui ) {
					event.preventDefault();

					var terms = split( this.value );
					// remove the current input
					terms.pop();
					// add the selected item
					terms.push( ui.item.value );
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );

					this.value = terms.join( "" );
					
					return false;
				},
				focus: function (event, ui) {
					event.preventDefault();
					return false;
				}
			}).on('focus', function() { 
				$(".ui-autocomplete").empty();
				$("#logistic_index").tooltip("destroy");
				$(this).keydown(); });
			
			
			$(".file_remove").hide();

			$("#uploadfile").change(function() {
				if ($(this).val() != '') {

				var file_name = $(this).prop('files')[0].name;

				$(".file_remove").show();
				$("#upd-file-name").html(file_name);

				}

			});
			$(".file_remove").on('click', function() { 
				$("#uploadfile").val('');
				$(".file_remove").hide();
				$("#upd-file-name").empty(); 
			})
			$("#clearSearch").on('click', function() { 
					$('#tbl_search').empty();
					$('#b_code option').removeAttr('selected').filter('[id=selected]').attr('selected', true);
					$('#acc_code option').removeAttr('selected').filter('[id=selected]').attr('selected', true);
					$('#t_month option').removeAttr('selected').filter('[id=selected]').attr('selected', true);
					$('#logistic_index').val('');

			});

			$('#emailButton').click(function () {
					$(dialog).dialog("open");
					$('#emailButton').removeAttr('href');
			});
			var checkba = "<?php if(!empty($regba)){echo $regba;}else{echo '';}  ?>";
			var checkacc = "<?php if(!empty($regacc)) {echo $regacc;}else {echo '';} ?>";
			var checkdupdata = "<?php if(!empty($dupdata)){echo $dupdata;}else{echo '';}  ?>";
			var checkLogisticNo = <?php echo json_encode($regLogisticNo);?>;
			var bottomLayerName = "<?php echo $this->Session->read('LayerTypeData')[Setting::LAYER_SETTING['bottomLayer']]; ?>";
			if(checkba == "REGBA") {
				
				$.ajax({

					url : "<?php echo $this->webroot ?>BrmActualResult/CheckBA",
					type: 'post',
					dataType: 'json',
					success: function(data) {
						
						var str = data.toString();
						var output =  str.split(',').join(" , ");
						
						$.confirm({
							title: bottomLayerName+' <?php echo __("を登録してください"); ?>',
							icon: 'fas fa-exclamation-circle',
							type: 'green',
							typeAnimated: true,
							animateFromElement: true,
							closeIcon: true,
							columnClass: 'medium',
							animation: 'top',
							draggable: false,
							content: output,
							buttons: {   
								ok: {
									text: "<?php echo __("はい"); ?>",
									btnClass: 'btn-info',
									action: function() {
										if(checkacc == "REGACC") {
											$.ajax({

												url : "<?php echo $this->webroot ?>BrmActualResult/CheckAcc",
												type: 'post',
												dataType: 'json',
												success: function(data) {
												 
													var str = data.toString();
													var output =  str.split(',').join(" , ");
													
													$.confirm({
														title: '<?php echo __("勘定科目コードを登録してください"); ?>',
														icon: 'fas fa-exclamation-circle',
														type: 'green',
														typeAnimated: true,
														animateFromElement: true,
														closeIcon: true,
														columnClass: 'medium',
														animation: 'top',
														draggable: false,
														content: output,
														buttons: {   
															ok: {
																text: "<?php echo __("はい"); ?>",
																btnClass: 'btn-info',
																action: function() {
																	if(checkLogisticNo != null){
																		logisticName = ''

																		$.each(checkLogisticNo, function(key, value){
																			logisticName += value+'.<br> ';
																		})
																		logisticName = logisticName.slice(0, -2);

																		$.confirm({
																			title: '<?php echo __("取引キーを登録してください"); ?>',
																			icon: 'fas fa-exclamation-circle',
																			type: 'green',
																			typeAnimated: true,
																			animateFromElement: true,
																			closeIcon: true,
																			columnClass: 'medium',
																			animation: 'top',
																			draggable: false,
																			content: logisticName,
																			buttons: {   
																				ok: {
																					text: "<?php echo __("はい"); ?>",
																					btnClass: 'btn-info',
																					action: function() {
																						if(checkdupdata == "DUPLICATEDATA") {
																							alert(checkdupdata);
																							$.ajax({

																								url : "<?php echo $this->webroot ?>BrmActualResult/SkipSameData",
																								type: 'post',
																								dataType: 'json',
																								success: function(data) {
																									
																									var str = data.toString();
																									var output =  str.split(',').join("<br>");
																									
																									$.confirm({
																										title: '<?php echo __("重複スキップ行"); ?>',
																										icon: 'fas fa-exclamation-circle',
																										type: 'green',
																										typeAnimated: true,
																										animateFromElement: true,
																										closeIcon: true,
																										columnClass: 'medium',
																										animation: 'top',
																										draggable: false,
																										content: output,
																										buttons: {   
																											ok: {
																												text: "<?php echo __("はい"); ?>",
																												btnClass: 'btn-info',
																											} 

																										},
																										theme: 'material',
																										animation: 'rotateYR',
																										closeAnimation: 'rotateXR'
																																								
																									});
																								},
																								error: function(e) {
																									console.log(e);
																								}       
																							});
																						}
																					}
																				} 

																			},
																			theme: 'material',
																			animation: 'rotateYR',
																			closeAnimation: 'rotateXR'
																																		
																		});
																	}
																	
																}
															} 

														},
														theme: 'material',
														animation: 'rotateYR',
														closeAnimation: 'rotateXR'
																												 
													});
												},
												error: function(e) {
													console.log(e);
												}       
											});
										}else if(checkdupdata == "DUPLICATEDATA") {
											if(checkLogisticNo != null){
												logisticName = ''

												$.each(checkLogisticNo, function(key, value){
													logisticName += value+'.<br> ';
												})
												logisticName = logisticName.slice(0, -2);

												$.confirm({
													title: '<?php echo __("取引キーを登録してください"); ?>',
													icon: 'fas fa-exclamation-circle',
													type: 'green',
													typeAnimated: true,
													animateFromElement: true,
													closeIcon: true,
													columnClass: 'medium',
													animation: 'top',
													draggable: false,
													content: logisticName,
													buttons: {   
														ok: {
															text: "<?php echo __("はい"); ?>",
															btnClass: 'btn-info',
															action: function() {
																$.ajax({

																	url : "<?php echo $this->webroot ?>BrmActualResult/SkipSameData",
																	type: 'post',
																	dataType: 'json',
																	success: function(data) {
																		
																		var str = data.toString();
																		var output =  str.split(',').join("<br>");
																		
																		$.confirm({
																			title: '<?php echo __("重複スキップ行"); ?>',
																			icon: 'fas fa-exclamation-circle',
																			type: 'green',
																			typeAnimated: true,
																			animateFromElement: true,
																			closeIcon: true,
																			columnClass: 'medium',
																			animation: 'top',
																			draggable: false,
																			content: output,
																			buttons: {   
																				ok: {
																					text: "<?php echo __("はい"); ?>",
																					btnClass: 'btn-info',
																				} 

																			},
																			theme: 'material',
																			animation: 'rotateYR',
																			closeAnimation: 'rotateXR'
																																	
																		});
																	},
																	error: function(e) {
																		console.log(e);
																	}       
																	});
															}
														} 

													},
													theme: 'material',
													animation: 'rotateYR',
													closeAnimation: 'rotateXR'
																												
												});
											}
											
										}
									}
								}

							},
							theme: 'material',
							animation: 'rotateYR',
							closeAnimation: 'rotateXR'
						});
					},
					error: function(e) {
						console.log(e);
					}       
				});
			}else if (checkacc == 'REGACC') {
				$.ajax({

					url : "<?php echo $this->webroot ?>BrmActualResult/CheckAcc",
					type: 'post',
					dataType: 'json',
					success: function(data) {
					 
						var str = data.toString();
						var output =  str.split(',').join(" , ");
						
						$.confirm({
							title: '<?php echo __("勘定科目コードを登録してください"); ?>',
							icon: 'fas fa-exclamation-circle',
							type: 'green',
							typeAnimated: true,
							animateFromElement: true,
							closeIcon: true,
							columnClass: 'medium',
							animation: 'top',
							draggable: false,
							content: output,
							buttons: {   
								ok: {
									text: "<?php echo __("はい"); ?>",
									btnClass: 'btn-info',
									action: function() {if(checkLogisticNo != null){
										logisticName = ''

										$.each(checkLogisticNo, function(key, value){
											logisticName += value+'.<br> ';
										})
										logisticName = logisticName.slice(0, -2);

										$.confirm({
											title: '<?php echo __("取引キーを登録してください"); ?>',
											icon: 'fas fa-exclamation-circle',
											type: 'green',
											typeAnimated: true,
											animateFromElement: true,
											closeIcon: true,
											columnClass: 'medium',
											animation: 'top',
											draggable: false,
											content: logisticName,
											buttons: {   
												ok: {
													text: "<?php echo __("はい"); ?>",
													btnClass: 'btn-info',
													action: function() {
														if(checkdupdata == "DUPLICATEDATA") {
															$.ajax({

																url : "<?php echo $this->webroot ?>BrmActualResult/SkipSameData",
																type: 'post',
																dataType: 'json',
																success: function(data) {
																	
																	var str = data.toString();
																	var output =  str.split(',').join("<br>");
																	
																	$.confirm({
																		title: '<?php echo __("重複スキップ行"); ?>',
																		icon: 'fas fa-exclamation-circle',
																		type: 'green',
																		typeAnimated: true,
																		animateFromElement: true,
																		closeIcon: true,
																		columnClass: 'medium',
																		animation: 'top',
																		draggable: false,
																		content: output,
																		buttons: {   
																			ok: {
																				text: "<?php echo __("はい"); ?>",
																				btnClass: 'btn-info',
																			} 

																		},
																		theme: 'material',
																		animation: 'rotateYR',
																		closeAnimation: 'rotateXR'
																																
																	});
																},
																error: function(e) {
																	console.log(e);
																}       
															});
														}
													}
												} 

											},
											theme: 'material',
											animation: 'rotateYR',
											closeAnimation: 'rotateXR'
																										
										});
									}

										
									}
								}

							},
							theme: 'material',
							animation: 'rotateYR',
							closeAnimation: 'rotateXR'
						});
					},
					error: function(e) {
						console.log(e);
					}       
				});
			}else if(checkdupdata == "DUPLICATEDATA") {

				$.ajax({

					url : "<?php echo $this->webroot ?>BrmActualResult/SkipSameData",
					type: 'post',
					dataType: 'json',
					success: function(data) {
						
						var str = data.toString();
						var output =  str.split(',').join("<br>");
						
						$.confirm({
							title: '<?php echo __("重複スキップ行"); ?>',
							icon: 'fas fa-exclamation-circle',
							type: 'green',
							typeAnimated: true,
							animateFromElement: true,
							closeIcon: true,
							columnClass: 'medium',
							animation: 'top',
							draggable: false,
							content: output,
							buttons: {   
								ok: {
									text: "<?php echo __("はい"); ?>",
									btnClass: 'btn-info',
									action: function(){
										if(checkLogisticNo != null){
											logisticName = ''

											$.each(checkLogisticNo, function(key, value){
												logisticName += value+'.<br> ';
											})
											logisticName = logisticName.slice(0, -2);

											$.confirm({
												title: '<?php echo __("取引キーを登録してください"); ?>',
												icon: 'fas fa-exclamation-circle',
												type: 'green',
												typeAnimated: true,
												animateFromElement: true,
												closeIcon: true,
												columnClass: 'medium',
												animation: 'top',
												draggable: false,
												content: logisticName,
												buttons: {   
													ok: {
														text: "<?php echo __("はい"); ?>",
														btnClass: 'btn-info',
													} 

												},
												theme: 'material',
												animation: 'rotateYR',
												closeAnimation: 'rotateXR'
																											
											});
											}
										

									}

								} 

							},
							theme: 'material',
							animation: 'rotateYR',
							closeAnimation: 'rotateXR'
																					
						});
					},
					error: function(e) {
						console.log(e);
					}       
				});
			}else if(checkLogisticNo != null){
				logisticName = ''

				$.each(checkLogisticNo, function(key, value){
					logisticName += value+'.<br> ';
				})
				logisticName = logisticName.slice(0, -2);

				$.confirm({
					title: '<?php echo __("取引キーを登録してください"); ?>',
					icon: 'fas fa-exclamation-circle',
					type: 'green',
					typeAnimated: true,
					animateFromElement: true,
					closeIcon: true,
					columnClass: 'medium',
					animation: 'top',
					draggable: false,
					content: logisticName,
					buttons: {   
						ok: {
							text: "<?php echo __("はい"); ?>",
							btnClass: 'btn-info',
						} 

					},
					theme: 'material',
					animation: 'rotateYR',
					closeAnimation: 'rotateXR'
																				
				});
			}

			
		});
		
		function clickSaveActualFile(){

			document.getElementById("successmsg").innerHTML = "";
			document.getElementById("errormsg").innerHTML   = "";    
			document.getElementById("ErrorImport").innerHTML= "";

			var actualFile = document.getElementById('uploadfile').files.length;

			var submission_deadline_date = $.trim($("#submission_deadline_date").val());
			
			var actualChk = true;
					
			if(actualFile != '1') {

				var newbr = document.createElement("div");                      
				var a     = document.getElementById("errormsg").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE024)));
				document.getElementById("errormsg").appendChild(a);                      
				actualChk = false;  

			}


			if(!checkNullOrBlank(submission_deadline_date)) {                       
				var newbr = document.createElement("div");                      
				var a = document.getElementById("errormsg").appendChild(newbr);                      
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("提出期限")?>'])));                     
				document.getElementById("errormsg").appendChild(a);                      
				actualChk = false;                      
			} 
				 
			if(actualChk) {   
				var isOkClicked = false;           
				$.confirm({                     
					title: '<?php echo __("保存確認");?>',                                  
					icon: 'fas fa-exclamation-circle',                                  
					type: 'green',                                  
					typeAnimated: true, 
					closeIcon: true,
					columnClass: 'medium',                              
					animateFromElement: true,                                   
					animation: 'top',                                   
					draggable: false,                                
					content: "<?php echo __("データを保存してよろしいですか。"); ?>",                                   
					buttons: {                                      
						ok: {                                   
							text: '<?php echo __("はい");?>',                                 
							btnClass: 'btn-info',                                   
							action: function(){   
								if(isOkClicked == false) { 

									isOkClicked = true;          
									loadingPic();
									document.forms[0].action = "<?php echo $this->webroot; ?>BrmActualResult/SaveActualFile";
									document.forms[0].method = "POST";
									document.forms[0].submit();    
																												 
									return true;    
																										
								}           
							}                        
						},                                      
						cancel : {                                  
							text: '<?php echo __("いいえ");?>',                                    
							btnClass: 'btn-default',                                    
							cancel: function(){                                 
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

		/* Delete for Result data*/
		function Click_Delete(id){  
			
			document.getElementById("successmsg").innerHTML = "";
			document.getElementById("errormsg").innerHTML   = "";    
			document.getElementById("ErrorImport").innerHTML= "";
			document.getElementById("hid_id").value = id;  
					
			var chk = true; 

			var account_name;

			var path = window.location.pathname;
			var page = path.split("/").pop();
			document.getElementById('hid_page_no').value = page;      

			if(chk) {                       
				$.confirm({                     
					title: '<?php echo __("削除確認");?>',                    
					icon: 'fas fa-exclamation-circle',                    
					type: 'red',                    
					typeAnimated: true, 
					closeIcon: true,
					columnClass: 'medium',                  
					animateFromElement: true,                   
					animation: 'top',                   
					draggable: false,
					content: '<?php echo __("データを削除してよろしいですか。");?>',

					buttons: {                      
												ok: {                   
														text: '<?php echo __("はい");?>',                   
														btnClass: 'btn-info',                   
														action: function(){ 
														loadingPic();                  
																document.forms[0].action = "<?php echo $this->webroot; ?>BrmActualResult/DeleteResultData";
											document.forms[0].method = "POST";
											document.forms[0].submit();
											
											return true;
														}                   
								},                        
								cancel : {                    
														text: '<?php echo __("いいえ");?>',                    
														btnClass: 'btn-default',                    
														cancel: function(){                   
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

		function scrollText(){
			var tes = $('#error').text();
			var tes1 = $('.success').text();
			if(tes){
				$("html, body").animate({ scrollTop: 0 }, "slow");        
			}
			if(tes1){
				$("html, body").animate({ scrollTop: 0 }, "slow");        
			}
		} 

		function BrdMailSending(){
			var layer_code = '';
			var page = 'BrmActualResult';
			var func = 'SendMailBRD';
			var data = [];
			
			$(".subject").text('');
			$(".body").html('');
			$("#mailSubj").val('');
			$("#mailBody").val(''); 
			$.ajax({
				type:'post',
				url: "<?php echo $this->webroot; ?>BrmActualResult/getMailLists",
				data:{layer_code : layer_code, page: page, function: func},
				dataType: 'json',
				success: function(data) {
					$("#mailSubj").val(data.subject);
					$("#mailBody").val(data.body);
					var mailSend = (data.mailSend == '') ? '0' : data.mailSend;
					$("#mailSend").val(mailSend);
					var to = data.to;
					var cc = data.cc;
					
					if(mailSend == 1) {

						if(data.mailType == 1) {

							var To, CC, BCC;
							if(Object.keys(to).length > 0){
								$.each( to, function( key, value ) {
									To = (To)? To+','+value : value;
									
								});
							}
							if(typeof data.cc != "undefined" && Object.keys(cc).length > 0){
								$.each( cc, function( key, value ) {
									CC = (CC)? CC+','+value : value;
									
								});
							}
							
							$('#toEmail').val(To);
							$('#ccEmail').val(CC);
							$('#bccEmail').val(CC);
							document.forms[0].action = "<?php echo $this->webroot; ?>BrmActualResult/BRDMailSending";
							document.forms[0].method = "POST";
							document.forms[0].submit();
						
						}else {

							$("#myPOPModal").addClass("in");
							$("#myPOPModal").css({"display":"block","padding-right":"17px"});

							if(data.to != undefined){
								$('.autoCplTo').show();
								level_id = Object.keys(data.to);
							} 
							if(data.cc != undefined) {
								$('.autoCplCc').show();
								cc_level_id = Object.keys(data.cc);
							}
							if(data.bcc != undefined) {
								$('.autoCplBcc').show();
								bcc_level_id = Object.keys(data.bcc);
							} 

							$(".subject").text(data.subject);
							$(".body").html(data.body);
							$('#actual_result_form').attr('method','post');
							$('#actual_result_form').attr('action', "<?php echo $this->webroot; ?>BrmActualResult/BRDMailSending");
							
						}
					}else{
                    
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmActualResult/BRDMailSending";
						document.forms[0].method = "POST";
						document.forms[0].submit();

					}
					return true;
				},
				error: function(e) {
					console.log('Something wrong! Please refresh the page.');
				}
				
			});
			
		 
		}

		function MrMailSending(){
			var layer_code = '';
			var page = 'BrmActualResult';
			var func = 'SendMailMR';
			var data = [];
			
			$(".subject").text('');
			$(".body").html('');
			$("#mailSubj").val('');
			$("#mailBody").val(''); 
			$.ajax({
				type:'post',
				url: "<?php echo $this->webroot; ?>BrmActualResult/getMailLists",
				data:{layer_code : layer_code, page: page, function: func},
				dataType: 'json',
				success: function(data) {
					$("#mailSubj").val(data.subject);
					$("#mailBody").val(data.body);
					var mailSend = (data.mailSend == '') ? '0' : data.mailSend;
					$("#mailSend").val(mailSend);
					var to = data.to;
					var cc = data.cc;
					
					if(mailSend == 1) {

						if(data.mailType == 1) {

							var To, CC, BCC;
							if(Object.keys(to).length > 0){
								$.each( to, function( key, value ) {
									To = (To)? To+','+value : value;
									
								});
							}
							if(typeof data.cc != "undefined" && Object.keys(cc).length > 0){
								$.each( cc, function( key, value ) {
									CC = (CC)? CC+','+value : value;
									
								});
							}
							
							$('#toEmail').val(To);
							$('#ccEmail').val(CC);
							$('#bccEmail').val(CC);
							document.forms[0].action = "<?php echo $this->webroot; ?>BrmActualResult/MRMailSending";
							document.forms[0].method = "POST";
							document.forms[0].submit();
						
						}else {

							$("#myPOPModal").addClass("in");
							$("#myPOPModal").css({"display":"block","padding-right":"17px"});

							if(data.to != undefined){
								$('.autoCplTo').show();
								level_id = Object.keys(data.to);
							} 
							if(data.cc != undefined) {
								$('.autoCplCc').show();
								cc_level_id = Object.keys(data.cc);
							}
							if(data.bcc != undefined) {
								$('.autoCplBcc').show();
								bcc_level_id = Object.keys(data.bcc);
							} 

							$(".subject").text(data.subject);
							$(".body").html(data.body);
							$('#actual_result_form').attr('method','post');
							$('#actual_result_form').attr('action', "<?php echo $this->webroot; ?>BrmActualResult/MRMailSending");
							
						}
					}else{
                    
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmActualResult/MRMailSending";
						document.forms[0].method = "POST";
						document.forms[0].submit();

					}
					return true;
				},
				error: function(e) {
					console.log('Something wrong! Please refresh the page.');
				}
				
			});
			
		}
		function searchActualData(){

			var b_code = document.getElementById("b_code").value;
			var acc_code = document.getElementById("acc_code").value;
			var t_month = document.getElementById("t_month").value;
			var logistic_index = document.getElementById("logistic_index").value;
			$.ajax({
					type: "POST",
					url: "<?php echo $this->webroot; ?>BrmActualResult/Search",
					data : {b_code : b_code,acc_code : acc_code,t_month : t_month, logistic_index : logistic_index},
					dataType : 'json',
					beforeSend: function(){
							// loadingPic();
								$("#overlay").show();
					},
					success : function(datas){

						var actual_data = datas['actual_data'];
						// var code_ba = datas['code_ba'];
						// var code_acc = datas['code_acc'];
						// var month_t = datas['month_t'];
						var query_count = datas['query_count'];
						var len = actual_data.length;
						var html ="";

						if(!query_count == 0){
								html +="<div class='col-md-12'>";
								html +="<p class='total_count'>";
								html +="<span class='msgfont'>";
								html +="<?php echo __('総行'); ?>"+ " : "  + query_count + " " + "<?php echo __('行'); ?>";
								html +="</span>";
								html +="</p>";
								html +="<p class='total_count' style='float:right;'>";
								html +="<button type='button' onclick='deleteall();' class='btn btn-danger btn_sumisho'><?php echo __('一括削除');?> </button>";
								html +="</p>";
								html +="</div>";
								html += "<table class='table table-bordered' id='tbl_actual'><thead class='check_period_table'><tr> <th width='50px'><?php echo __('対象月'); ?></th><th width='50px'><?php echo __('勘定コード名'); ?></th><th width='50px'>"+actual_data[0]['LayerName']['bottom']+"<?php echo ' '.__("コード");?></th><th width='100px'>"+actual_data[0]['LayerName']['top']+"</th><th width='100px'><?php echo __('取引検索キー'); ?></th><th width='50px'><?php echo __('相手先コード'); ?></th><th width='50px'><?php echo __('提出期限'); ?></th><th width='50'><?php echo __('金額'); ?></th><th width='30px'><?php echo __('通貨'); ?></th></tr> </thead>";

						}else{
							html +="<div class='popup_nodata'><?php echo __('データが見つかりません！'); ?></div>";
						}


						 for (var i = 0; i < len; i++) {
							    var destination_code="";
								var target_month = actual_data[i].BrmActualResult.target_month;
								var account_code = actual_data[i].BrmActualResult.account_code;
								var ba_code = actual_data[i].BrmActualResult.layer_code;
								var head_dept_name = actual_data[i].layer.name_jp;
								var transaction_key = actual_data[i].BrmActualResult.transaction_key ? actual_data[i].BrmActualResult.transaction_key : '';

								if(actual_data[i].BrmActualResult.destination_code != null)
								var destination_code = actual_data[i].BrmActualResult.destination_code;

								var submission_deadline_date = actual_data[i].BrmActualResult.submission_deadline_date;
								var amount = actual_data[i][0]['amount'];
								var currency = actual_data[i].BrmActualResult.currency;

								html+="<tr>";
								html+= "<td>"+target_month+"</td>";
								html+= "<td>"+account_code+"</td>";
								html+= "<td>"+ba_code+"</td>";
								html+= "<td>"+head_dept_name+"</td>";
								html+= "<td>"+transaction_key+"</td>";
								html+= "<td>"+destination_code+"</td>";
								html+= "<td>"+submission_deadline_date+"</td>";
								html+= "<td>"+amount+"</td>";
								html+= "<td>"+currency+"</td>";
								html+="</tr>";


						 }
							html +="</table>";
							$('#myModal #tbl_search').html(html);


					},
					 complete:function(){
						// Hide image container
						$("#overlay").hide();
					}
			});

		}
		function deleteall(){
			var b_code = document.getElementById("b_code").value;
			var acc_code = document.getElementById("acc_code").value;
			var t_month = document.getElementById("t_month").value;

			var chk = true;
			if(chk) {                       
				$.confirm({                     
					title: '<?php echo __("削除確認");?>',                    
					icon: 'fas fa-exclamation-circle',                    
					type: 'red',                    
					typeAnimated: true, 
					closeIcon: true,
					columnClass: 'medium',                  
					animateFromElement: true,                   
					animation: 'top',                   
					draggable: false,                   
					content: '<?php echo __("データを削除してよろしいですか。");?>',

					buttons: {                      
												ok: {                   
														text: '<?php echo __("はい");?>',                   
														btnClass: 'btn-info',                   
														action: function(){ 
															$.ajax({
																	type: "POST",
																	url: "<?php echo $this->webroot; ?>BrmActualResult/DeleteAll",
																	data : {b_code : b_code,acc_code : acc_code,t_month : t_month},
																	success: function(data) {
																		loadingPic();
																		document.forms[0].action = "<?php echo $this->webroot; ?>BrmActualResult";
																		document.forms[0].method = "POST";
																		document.forms[0].submit();
																		scrollText(); 
																		return true;

																	}
															});
														}                   
								},                        
								cancel : {                    
														text: '<?php echo __("いいえ");?>',                    
														btnClass: 'btn-default',                    
														cancel: function(){                   
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

		function loadingPic() { // function expression closure to contain variables
			var ua = window.navigator.userAgent;
			var msie = ua.indexOf("MSIE ");
			
			if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet 
			 {
				//alert("ie");
				var el = document.getElementById('imgLoading'); 
				var i = 0;
					var pics = [ "<?php echo $this->webroot; ?>img/loading1.gif",
								 "<?php echo $this->webroot; ?>img/loading2.gif",
								 "<?php echo $this->webroot; ?>img/loading3.gif" ,
								 "<?php echo $this->webroot; ?>img/loading4.gif" ];
				 

					function toggle() {
							el.src = pics[i];           // set the image
							i = (i + 1) % pics.length;  // update the counter
					}
					setInterval(toggle, 250);
					$("#overlay").show();
				

			}else{
				//alert("other");
				// el.src = "<?php echo $this->webroot; ?>img/loading.gif";
				$("#overlay").show();
			}
		
		} 
		

	</script>
	<div id="overlay">
		<span class="loader"></span>
	</div>

	<div class="content">                     
		<div class="row" >
			<div class="col-lg-12 col-md-12 col-sm-12">
				<h3 class=""><?php echo __("実績のインポート");?></h3>
				<hr>
				<div class="success" id="successmsg"><?php echo ($this->Session->check("Message.ImportSuccess"))? $this->Flash->render("ImportSuccess") : '';?></div>
				<div class="error" id="errormsg"></div>
				<div class="errorSuccess" id="ErrorImport">
					<?php if($this->Session->check('Message.ImportError')): ?>
						<div class="error" id="">
							<?php echo $this->Flash->render("ImportError"); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-md-6">

				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("期間"); ?></label>
					<div class="col-md-8">
						<input class ='form-control' style="margin-bottom: 20px;" type = "textbox" id='term' name='term' value ='<?php echo $term; ?>' disabled="disabled" >   
						<input type="hidden" name="term_id" value="<?php echo($term_id) ?>">  
					</div>
				</div>

				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo $type_order['LayerType']['name_'.$lang_name]; ?></label>
					<div class="col-md-8">
						<input class ='form-control' style="margin-bottom: 20px;" type = "textbox" id='headquarter' name='headquarter' value = '<?php echo $headquarter; ?>' disabled="disabled" >  
						<input type="hidden" name="hq_id" value="<?php echo($hq_id) ?>"> 
					</div>
				</div>

				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("提出期限"); ?><span class="" style="color: red;">*</span></label>
					<div class="col-md-8">
						<div class="input-group date datepicker" data-provide="datepicker" style="padding: 0px !important;">
							<input type="text" class="form-control" id="submission_deadline_date" name="submission_date" value="" />
							<span class="input-group-addon">
								<span class="glyphicon glyphicon-calendar"></span>
							</span>
						</div>
						<input type="hidden" name="hq_id" value="<?php echo($hq_id) ?>"> 
					</div>
				</div>
				<?php if($showSaveBtn) {?>
					<div class="col-md-12">
						<button type="button" class="btn btn-success btn_sumisho" onClick = "clickSaveActualFile();" style="float: right; margin: 20px 0px"><?php echo __('保存');?></button>
					</div>
				<?php } ?>
			</div>
			<div class="col-md-6">
				<div class="row">
					<div class = "col-md-4 form-group">
						<label style="color:white" id="btn_browse" class="control-label"><?php echo __('ブラウズ');?>
							<input type="file" name="uploadfile" id="uploadfile" class="btn uploadfile">
						</label>
					</div>
					<div class="col-md-3">
						<div class="row">
							<div class="col-md-12 mt-3">
								<a href="<?php echo $this->webroot ?>templates/brmactualresult_template.xlsx" class="temp-download" ><u>Get Template <i class="fa-solid fa-file-arrow-down"></i></u> </a>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<span class="upd-file-name" id="upd-file-name"></span>
					<span class="file_remove"><i class="fa-regular fa-circle-xmark" style="color: red;"></i></span>
					<!-- <div class="col-md-8 col-xs-12 remove_filelink" id = "remove_filelink" style="text-align: start;">
						<a href="#" class="file_remove"  id="file_remove"><i class="fa-regular fa-circle-xmark" style="color: red;font-size:1.3rem;"></i></a>
					</div> -->
				</div>

			</div>
			
		</div>
	 
	<!-- show data with table (edited by ma sandi - Start)  -->
	<?php if(!empty($actual_data)){ ?>
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12">
			<?php if($showSaveBtn) {?>
			 	<button type="button" data-target="#myModal" data-toggle="modal" data-backdrop="static" data-keyboard="false" class="ml-10 pull-right btn btn-success btn_sumisho"><?php echo __('検索');?></button>
			<?php } ?>
				<!-- mail sending button for BRD & MonthlyReport(edited by Lone Lay) -->
			<?php if($showSendMailMRBtn) {?>
				<input type="button" class="ml-10 pull-right btn btn-success mrbrd_mail" value="<?php echo __('メール送信(業績報告)');?>" onclick = "MrMailSending();">
			<?php } ?>
			<?php if($showSendMailBtn) {?>
				<input type="button" class="pull-right btn btn-success mrbrd_mail" value="<?php echo __('メール送信(予実比較)');?>" onclick = "BrdMailSending();">
			<?php } ?>
		</div>
		<div class="col-lg-12 col-md-12 col-sm-12 msgfont" id="total_row">
			<?=$count;?>
		</div>
		<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">

						<div class="table-responsive tbl-wrapper" style="overflow-x:auto;">
							<!-- width: 1100px; min-width: 1140px;/old desing -->
							<table class="table table-striped table-bordered" id="tbl_actual">    
										<thead class="check_period_table">
												
														<th width="50px"><?php echo __("対象月"); ?></th>
														<th width="80px"><?php echo __("勘定コード名"); ?></th>
														<th width="50px"><?php echo $bottomLayer['LayerType']['name_'.$lang_name].' '.__("コード"); ?></th>
														<th width="100px"><?php echo $type_order['LayerType']['name_'.$lang_name]; ?></th>
														<th width="50px"><?php echo __("取引検索キー"); ?></th>
														<th width='10px'><?php echo __('相手先コード'); ?></th>
														<th width="50px"><?php echo __("提出期限"); ?></th>
														<th width="75px"><?php echo __("金額"); ?></th>
														<th width="15px"><?php echo __("通貨"); ?></th>
														<th width="30px"><?php echo __("Action"); ?></th>
												</tr>
										</thead>
										<tbody>

											 <?php if(!empty($actual_data))
												foreach($actual_data as $datas ) { 
												$head_id = $datas['BrmActualResult']['hlayer_code'];
												$actual_result_id = $datas['BrmActualResult']['id'];
												$target_month     = $datas['BrmActualResult']['target_month'];
												$account_code     = $datas['BrmActualResult']['account_code'];
												$transaction_key  = $datas['BrmActualResult']['transaction_key'];
												$destination_code = $datas['BrmActualResult']['destination_code'];
												$submission_deadline_date  = $datas['BrmActualResult']['submission_deadline_date'];
												$ba_code          = $datas['BrmActualResult']['layer_code'];
												$head_dep_name    = $hq_list[$head_id];
												$amount           = $datas['BrmActualResult']['amount'];
												$amount           = str_replace('.00', '', number_format($amount, 2, '.', ','));
												$currency         = $datas['BrmActualResult']['currency'];                          
												 ?>   
												<tr style="text-align: center;">                  
														<td class='string' width="50px" style="word-break: break-all;"><?php echo ($target_month); ?></td>  
														<td class='string' width="80px" style="word-break: break-all;"><?php echo h($account_code); ?></td>
														<td class='string' width="50px" style="word-break: break-all;"><?php echo h($ba_code); ?></td>  
														<td class='string' width="100px" style="word-break: break-all;"><?php echo $head_dep_name; ?></td>
														<td class='string' width="50px" style="word-break: break-all;"><?php echo h($transaction_key); ?></td>
														<td class='string' width="10px" style="word-break: break-all;"><?php echo h($destination_code); ?></td>
														<td class='string' width="50px" style="word-break: break-all;"><?php echo h($submission_deadline_date); ?></td>

														<?php if($amount<0) {?>
														<td class='negative' width="75px" style="word-break: break-all;"><?php echo h($amount); ?></td>
														<?php }else {?>
														<td class='number' width="75px" style="word-break: break-all;"><?php echo h($amount); ?></td>
														<?php  }?>
														<td class='string' width="15px" style="word-break: break-all;"><?php echo h($currency); ?></td>
														<td class='string' width="30px" class="link" style="word-break: break-all;">              
														<a class="glyphicon glyphicon-trash" href="#" onclick="Click_Delete('<?php echo $datas['BrmActualResult']['id'] ?>');"><?php echo __('削除'); ?>
										</a>
										</td>
												</tr> 
											<?php  }?> 
												<input type="hidden" name="hid_id" id="hid_id" class="txtbox" value ="<?php if(!empty($datas['BrmActualResult']['id'])) echo $datas['BrmActualResult']['id']; ?>"> 
												<input type="hidden" name="hid_page_no" id="hid_page_no" class="txtbox" value ="">

												 <input type="hidden" name="account_code" id="account_code" class="txtbox" value ="<?php if(!empty($datas['AccountModel']['account_code'])) echo $datas['AccountModel']['account_code']; ?>"> 
										</tbody>                            
								</table>
						</div>
					</div>
				</div>
				<div class="row" style="clear:both;margin: 40px 0px;">
					<div class="col-sm-12" style="padding: 10px;text-align: center;">
						<div class="paging">
							<?php
							if ($query_count > Paging::TABLE_PAGING) {
								echo $this->Paginator->first('<<');
								echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev disabled'));
								echo $this->Paginator->numbers(array('separator'=>'', 'modulus'=>6));
								echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
								echo $this->Paginator->last('>>');
							}
							?>
						</div>
					</div> 
					<?php }else{ ?>
						<div id="err" class="no-data"> <?php echo ($errmsg); ?></div>
					<?php } ?>
				</div>
		</div><!-- content show data end -->
	</div>
		<!-- show data with table (edited by ma sandi - End)  -->
	</div>
<!-- PopUpBox  -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog">

	<div class="modal-dialog modal-lg">
		<div class="modal-content contantbond">
			<div class="modal-header">
				<button type="button" class="close" id="clearSearch" data-dismiss="modal">&times;</button>
				<h3 class="modal-title"><?php echo __("実績のインポート"); ?></h3>
			</div>
			<div class="modal-body">
				<div class="table-responsive modal_tbl_wrapper">		
					<div class="popup_row">
						<div class="col-md-12">
							<div class="col-md-6">
								<div class = "form-group">
									<label class="col-md-4 control-label rep_lbl"><?php echo $bottomLayer['LayerType']['name_'.$lang_name].' '.__("コード"); ?></label>
									<div class="col-md-8" style="margin-bottom: 20px"> 
									 	<select id="b_code" name="b_code" class="form-control">
											<option id="selected" value="">--Select <?php echo $bottomLayer['LayerType']['name_'.$lang_name]; ?>--</option>
											<?php
											$language = $this->Session->read('Config.language');
												foreach($b_name as $b_name): 
											?>
												<option value=<?=$b_name['Layer']['layer_code']?>>
													<?php 
														$slash = empty($b_name['Layer']['name_en'])?'':'/';
														if($language == 'eng'){
															echo $b_name['Layer']['layer_code'].$slash.$b_name['Layer']['name_en'];
														}else{
															echo $b_name['Layer']['layer_code'].$slash.$b_name['Layer']['name_jp'];
														}
													?>
												</option>
											<?php endforeach;?>
										</select> 
									</div>
								</div>								
							</div>
							<div class="col-md-6">
								<div class = "form-group">
									<label class="col-md-4 control-label"><?php echo __("対象月"); ?></label>
									<div class="col-md-8" style="margin-bottom: 20px">
									 <select id="t_month" name="t_month" class="form-control">
												 <option id="selected" value="">--Select Target Month--</option>
													<?php
														foreach($t_month as $t_month): 
													 ?>                     
													<option value=<?=$t_month?>>
														<?=$t_month?>
													</option>
												 <?php endforeach;?>
									</select> 
									</div>
								</div>
							</div>						
						</div>
						<div class="col-md-12">
							<div class="col-md-6">
								<div class = "form-group">
									<label class="col-md-4 control-label"><?php echo __("勘定コード名"); ?></label>
									<div class="col-md-8" style="margin-bottom: 20px">
										<select id="acc_code" name="acc_code" class="form-control">
													 <option id="selected" value="">--Select Account Code--</option>
														<?php
															foreach($acc_code as $acc_code): 
														 ?>
														<option value=<?=$acc_code?>>
															<?=$acc_code?>
														</option>
													 <?php endforeach;?>
										</select> 
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class = "form-group">
									<label for="logistic_index" class="col-md-4 control-label rep_lbl"><?php echo __("取引検索キー"); ?></label>
									<div class="col-md-8" style="margin-bottom: 20px"> 
										<input type="text" id="logistic_index" name="logistic_index" class="form-control">
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 text-center">
							<div class="form-group col-md-12 col-sm-12 col-xs-12 emp_register_btn_popup" id="save">
								<button type="button" id="search" onclick="searchActualData();" class="ml-10 btn btn-success btn_sumisho"><?php echo __('検索');?> </button>
							</div>
						</div>
					</div>
 
					<!-- html append table -->
					<div id='tbl_search'> 
					</div>

				</div>
			</div>
		</div>
	</div>
</div> 

<!-- end popup -->
<?php
echo $this->form->end();
?>
