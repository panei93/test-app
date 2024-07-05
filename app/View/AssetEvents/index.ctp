

<style>
	body{
		font-family: KozGoPro-Regular;
	}

	#Inactive_status{
		color: palevioletred;
	}

	.act_color{
		color: palevioletred;
	}

	.fl-scrolls {
		margin-bottom: 40px;/* modify floating scroll bar */
	}

	.success{
		word-wrap: break-word;
	}

	.error{
		word-wrap: break-word;
	}

	#event_tbl {
		min-width: 700px;
	}
	@media screen and (max-width: 767px){
		.table-responsive {   
			border: none; 
		}
	}
	.row {
		display: block;
		margin-right: 0px;
	}
	#btn_update {
        display: none;
    }
	.disabled{
		pointer-events: none;
		cursor: default;
		opacity: 0.5;
	}
</style>
<script>
	$(document).ready(function(){			
		/* float thead */
		if($('#event_tbl').length > 0) {
			var $table = $('#event_tbl');
			$table.floatThead({
			      position: 'absolute'
			});
		}
			
		if($(".tbl-wrapper").length) {
			$(".tbl-wrapper").floatingScroll();
		}
		/* end*/
		var save_event_name = "<?php echo $save_event_name; ?>";
		if(save_event_name != '') {
			$('#btn_save').hide();
			$('#btn_update').show();
			document.getElementById("hid_id").value = '<?php echo $hid_id; ?>';	
		}else{
			$('#btn_save').show();
			$('#btn_update').hide();
		}
	});
    function loadingPic() { 
		$("#overlay").show();
		//$('.jconfirm').hide();  
	}
	function checkDate(value1,value2){
		if(value1 == ''){
			return true;
		}
		return false;
	}

	function click_Save(event) {
		
		document.getElementById("error").innerHTML   = "";											
		document.getElementById("success").innerHTML = "";

		var event_name = $.trim($("#event_name").val());		
		var event_reference = document.getElementById("event_reference").value;
		//var regex =/[^a-zA-Z0-9\-\_\/]/;
		var isOkClicked = false; //prevent double click on ok button

		var chk = true;														

		if(!checkNullOrBlank(event_name)) {												
			var newbr = document.createElement("div");											
			var a = document.getElementById("error").appendChild(newbr);											
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("イベント名")?>'])));
							
			document.getElementById("error").appendChild(a);											
			chk = false;

		}
		var url = window.location.href;
		if(url == undefined) {
			url = '';
		}
		if(chk) {	
			$.confirm({ 					
				title: '<?php echo __('保存確認'); ?>',									
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
						text: '<?php echo __("はい");?>',									
						btnClass: 'btn-info',									
						action: function(){	
						//prevent double click on ok button
							if(isOkClicked == false) { 
								isOkClicked = true;									
								//getMail('save', url, true);
								//SST 1.12.2022 
								loadingPic();
								document.forms['save_asset_event'].action = "<?php echo $this->webroot; ?>AssetEvents/saveData";
								document.forms['save_asset_event'].method = "POST";
								document.forms['save_asset_event'].submit();

							}						
						}								
					},     									
					cancel : {									
						text: '<?php echo __("いいえ");?>',									
						btnClass: 'btn-default',									
						cancel: function(){									
							//console.log('the user clicked cancel');	
							scrollText();								
						}

					}									
				},									
				theme: 'material',									
				animation: 'rotateYR',									
				closeAnimation: 'rotateXR'									
			});                   										
		}

		if (event.keyCode == 13 || event.which == 13) {
		       
		        event.preventDefault();

   		}
		scrollText();											
	}

	function getMail(func, path = '', chkState = '') {
		var layer_code = "<?php echo $this->Session->read('SESSION_LAYER_CODE'); ?>";
		var page = 'AssetEvents';
		var language = <?php echo json_encode($language); ?>;
		
		func = func.replace(" ", "");
		var form_action = func+"Data";
		
		if(chkState) {
			$.ajax({
				type:'post',
				url: "<?php echo $this->webroot; ?>Assets/getMailLists",
				data:{layer_code : layer_code, page: page, function: func, language: language},
				dataType: 'json',
				success: function(data) {
					var mailSend = (data.mailSend == '') ? '0' : data.mailSend;
					$("#mailSend").val(mailSend);
					if(mailSend == 1) {	
						$("#mailSubj").val(data.subject);
						$("#mailBody").val(data.body);
						
						if(data.mailType == 1) {
							//default
							if(data.to != undefined) var toEmail = Object.values(data.to);
							if(data.cc != undefined) var ccEmail = Object.values(data.cc);
							if(data.bcc != undefined) var bccEmail = Object.values(data.bcc);
							
							$('#toEmail').val(toEmail);
							$('#ccEmail').val(ccEmail);
							$('#bccEmail').val(bccEmail);
							loadingPic();
							document.forms['save_asset_event'].action = "<?php echo $this->webroot; ?>AssetEvents/"+form_action;
							document.forms['save_asset_event'].method = "POST";
							document.forms['save_asset_event'].submit();
						} else {
							//popup
							$("#myPOPModal").addClass("in");
							$("#myPOPModal").css({"display":"block","padding-right":"17px"});
							
							if(data.to != undefined) $('.autoCplTo').show();
							if(data.cc != undefined) $('.autoCplCc').show();
							if(data.bcc != undefined) $('.autoCplBcc').show();

							if(data.to != undefined) level_id = Object.keys(data.to);
							if(data.cc != undefined) cc_level_id = Object.keys(data.cc);
							if(data.bcc != undefined) bcc_level_id = Object.keys(data.bcc);
							
							$(".subject").text(data.subject);
							$(".body").html(data.body);

							$('#save_asset_event').attr('method','post');
							$('#save_asset_event').attr('action', "<?php echo $this->webroot; ?>AssetEvents/"+form_action);
						}
					}else {
						document.forms['save_asset_event'].action = "<?php echo $this->webroot; ?>AssetEvents/"+form_action;
						document.forms['save_asset_event'].method = "POST";
						document.forms['save_asset_event'].submit();
					}

				},
				error: function(e) {
					//console.log('Something wrong! Please refresh the page.');
				}
			});
		}else {
			loadingPic();
			document.forms['save_asset_event'].action = "<?php echo $this->webroot; ?>AssetEvents/"+form_action;
			document.forms['save_asset_event'].method = "POST";
			document.forms['save_asset_event'].submit();
		}		
	}

	function Click_Edit(event_id){

		document.getElementById("error").innerHTML   = '';
		document.getElementById("success").innerHTML   = '';
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>AssetEvents/getEvent",
			data: {id:event_id},
			dataType: 'json', 
			beforeSend: function() {
				loadingPic();
			}, 

			success: function(data) {

				var hid_id = data['id'];
				var event_name = data['event_name'];
				var event_reference = data['event_reference'];
				var event_ref_data = data['Event_ref_data'];
				$('#event_reference').empty();
				$('#event_reference').append($("<option></option>").attr("value","").text("-----Select-----"));
				$.each(event_ref_data, function(index, value) {
					$('#event_reference').append($("<option></option>").attr("value",index).text(value));
				});
				$("#hid_id").val(event_id);
				$("#event_name").val(event_name);              
				$('#event_reference option[value="'+event_reference+'"]').prop('selected', true);
				$('#btn_save').hide();
				$('#btn_update').show();
				$('#overlay').hide();

			},
			//error: function(ts) { alert(ts.responseText) }

		});

	}

	function Click_update() {

		document.getElementById("error").innerHTML =  '';
		document.getElementById("success").innerHTML =  '';

		var event_name = document.getElementById("event_name").value;		
		var event_reference = document.getElementById("event_reference").value;			

		var chk = true;														

		if(!checkNullOrBlank(event_name)) {												
			var newbr = document.createElement("div");											
			var a = document.getElementById("error").appendChild(newbr);											
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("イベント名")?>'])));											
			document.getElementById("error").appendChild(a);											
			chk = false;											
		}	
		
		
		var path = window.location.pathname;
		var page = path.split("/").pop();
		document.getElementById('hid_page_no').value = page; 
		if(chk) {											
			$.confirm({										
				title: '<?php echo __('変更確認'); ?>',									
				icon: 'fas fa-exclamation-circle',									
				type: 'blue',									
				typeAnimated: true,			
				closeIcon: true,
				columnClass: 'medium',						
				animateFromElement: true,									
				animation: 'top',									
				draggable: false,									
				content: "<?php echo __('データを変更してよろしいですか。'); ?>",	

				buttons:{   									
					ok: {									
						text: '<?php echo __("はい");?>',									
						btnClass: 'btn-info',									
						action: function(){									
							document.forms['save_asset_event'].action = "<?php echo $this->webroot; ?>AssetEvents/saveData";
							document.forms['save_asset_event'].method = "POST";
							document.forms['save_asset_event'].submit(); 
							loadingPic(); 
							return true;							
						}									
					},     									
					cancel : {									
						text: '<?php echo __("いいえ");?>',									
						btnClass: 'btn-default',									
						cancel: function(){									
							//console.log('the user clicked cancel');	
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

	function Click_Delete(event_id){	
		
		document.getElementById("error").innerHTML   = '';
		document.getElementById("success").innerHTML   = '';

		document.getElementById("hid_id").value = event_id;	

		var chk = true;		


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
				content: errMsg(commonMsg.JSE017),									
				buttons: {   										
					ok: {										
						text: '<?php echo __("はい");?>',										
						btnClass: 'btn-info',										
						action: function(){										
							document.forms['save_asset_event'].action = "<?php echo $this->webroot; ?>AssetEvents/DeleteData";
							document.forms['save_asset_event'].method = "POST";
							document.forms['save_asset_event'].submit();
							loadingPic(); 
							return true;
						}										
					},     										
					cancel : {										
						text: '<?php echo __("いいえ");?>',										
						btnClass: 'btn-default',										
						cancel: function(){										
							//console.log('the user clicked cancel');	
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

	function Click_Active(event_id){
		
		document.getElementById("error").innerHTML   = '';
		document.getElementById("success").innerHTML   = '';
		document.getElementById("hid_id").value = event_id;	
		var chk = true;	

		var isOkClicked = false; //prevent double click on ok button


		var path = window.location.pathname;
		var page = path.split("/").pop();
		document.getElementById('hid_page_no').value = page;			

		if(chk) {												
			$.confirm({											
				title: '<?php echo __("非アクティブの確認");?>',										
				icon: 'fas fa-exclamation-circle',										
				type: 'blue',										
				typeAnimated: true,	
				closeIcon: true,
				columnClass: 'medium',									
				animateFromElement: true,										
				animation: 'top',										
				draggable: false,										
				content: "<?php echo __("このイベントを無効にしますか？"); ?>",									
				buttons: {   										
					ok: {										
						text: '<?php echo __("はい");?>',										
						btnClass: 'btn-info',										
						action: function(){	
						//prevent double click on ok button
						if(isOkClicked == false) { 
								isOkClicked = true;	
								loadingPic(); 								
							document.forms['save_asset_event'].action = "<?php echo $this->webroot; ?>AssetEvents/activeStatusChange";
							document.forms['save_asset_event'].method = "POST";
							document.forms['save_asset_event'].submit();
							
							return true;
							}
						}										
					},     										
					cancel : {										
						text: '<?php echo __("いいえ");?>',										
						btnClass: 'btn-default',										
						action: function(){	
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
		}
		scrollText();
		$("#error").empty();	


	}

	function Click_Inactive(event_id){
		
		document.getElementById("error").innerHTML   = '';
		document.getElementById("success").innerHTML   = '';
		var isOkClicked = false; //prevent doublic click on ok button


		document.getElementById("hid_id").value = event_id;	

		var chk = true;		


		var path = window.location.pathname;
		var page = path.split("/").pop();
		document.getElementById('hid_page_no').value = page;			

		if(chk) {												
			$.confirm({											
				title: '<?php echo __("能動的を確認");?>',											
				icon: 'fas fa-exclamation-circle',										
				type: 'blue',										
				typeAnimated: true,	
				closeIcon: true,
				columnClass: 'medium',									
				animateFromElement: true,										
				animation: 'top',										
				draggable: false,										
				content: "<?php echo __("このイベントをアクティブにしますか？"); ?>",					
				buttons: {   										
					ok: {										
						text: '<?php echo __("はい");?>',										
						btnClass: 'btn-info',										
						action: function(){	
						//prevent double click on ok button
						if(isOkClicked == false) { 
								isOkClicked = true;	
							loadingPic(); 									
							document.forms['save_asset_event'].action = "<?php echo $this->webroot; ?>AssetEvents/InActiveStatusChange";
							document.forms['save_asset_event'].method = "POST";
							document.forms['save_asset_event'].submit();
							

							return true;
							}	
						}									
					},     										
					cancel : {										
						text: '<?php echo __("いいえ");?>',										
						btnClass: 'btn-default',										
						 action: function(){
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
		}
		scrollText();
		$("#error").empty();


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
	/*  
	*	Show hide loading overlay
	*	@Zeyar Min  
	*/
	function loadingPic() { 
		$("#overlay").show();
		$('.jconfirm').hide();  
	}
	function getEvent(id){
		$.ajax({
            type: "POST",
            url: "<?php echo $this->webroot; ?>AssetEvents/getEvent",
            data: {
                id: id
            },
            dataType: 'json',
            beforeSend: function() {
                loadingPic();
            },
			success: function(data) {
				$("#event_name").val(data.event_name);
				$("#event_reference").val(data.event_reference);
				$('#overlay').hide();
			}
		});
	}
</script>
<?php 
	$ref_id = $save_event_ref;
 ?>

<div id="overlay">
	<span class="loader"></span>
</div>
<input type="hidden" name="toEmail" id="toEmail" value="">
<input type="hidden" name="ccEmail" id="ccEmail" value="">
<input type="hidden" name="bccEmail" id="bccEmail" value="">
<input type="hidden" name="mailSubj" id="mailSubj">
<input type="hidden" name="mailBody" id="mailBody">
<input type="hidden" name="path" id="path">
<input type="hidden" name="mailSend" id="mailSend">
<div class="content register_container">
<?php 	
echo $this->element("autocomplete", array(
					"to_level_id" => "",
					"cc_level_id" =>"",
					"submit_form_name" => "save_asset_event",
					"MailSubject" => "",
					"MailTitle" => "",
					"MailBody" => ""));
?>
<?php
echo $this->Form->create(false,array('type'=>'post', 'class' => 'form-inline', 'name'=>'save_asset_event','id'=>'save_asset_event', 'enctype'=> 'multipart/form-data', 'onkeydown'=>"return event.key != 'Enter';"));
?>
	<div class="register_form">
		<div class="row">
			<fieldset>
				<div class="col-md-12 col-sm-12 heading_line_title">
					<legend><?php echo __('イベント管理'); ?></legend>
				</div>
				
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="success" id="success"><?php echo ($this->Session->check("Message.EventSuccess"))? $this->Flash->render("EventSuccess") : '';?></div>						
					<div class="error" id="error"><?php echo ($this->Session->check("Message.EventError"))? $this->Flash->render("EventError") : '';?></div>
				</div>
				
				<div class="form-row">
					<div class="form-group col-sm-12 col-md-6">
						<label for="object" class="control-label required">
							<?php echo __('イベント名');?>
						</label>
						<input type="text" class="form-control form_input" id="event_name" name="event_name"  value="<?php echo htmlspecialchars($save_event_name); ?>" maxlength="500"/>
					</div>
					<div class="form-group col-sm-12 col-md-6">
						<label for="event_reference" class="control-label">
							<?php echo __('参照イベント');?>
						</label>
						<select name="event_reference" id="event_reference" class="form-control form_input" >
							<option value="" selected=""><?php echo __("-----Select-----"); ?></option>	
									
							<?php 
							foreach($Event_ref_data as $evid => $evname):
				
								if($ref_id == $evid) {
									$selected = 'selected'; 
								} else {
									$selected ='';
								}
				
								?>
								<option value="<?php echo h($evid);?>" <?php echo $selected ; ?>><?php echo h($evname); ?>
									
								</option>
							<?php endforeach; ?>
						</select>	
					</div>
					<?php if(!empty($buttons)){?>
						<div class="col-md-6 " style="max-width:90.8%">
							<input type="button" class="btn-save btn-save-wpr btn-save pull-right" id="btn_save" name="btn_save"  value = "<?php echo __('保存');?>"   onclick = "click_Save(this);">
							<input type="button" class="btn-save btn-save-wpr btn-save pull-right" id="btn_update" name="btn_update"  value = "<?php echo __('編集');?>"   onclick = "Click_update(this);">
						</div>
					<?php } ?>
				</div>
			</fieldset>
		</div>
	</div>
	
	<!-- data does not have show message -->
	<?php if(!empty($succmsg)) {?> 
		<div id="succc" class="msgfont"> <?php echo ($succmsg);?></div>
	<?php }else if(!empty($errmsg)) {?>
		<div id="error" class="no-data"> <?php echo ($errmsg); ?></div>
	<?php }?>
	<!-- data does not have show message end -->

	<?php if($rowcount!=0) { ?>	
		<div class="row">

			<!-- message show data -->
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="success" id="success"><?php echo ($this->Session->check("Message.EventSuccess"))? $this->Flash->render("EventSuccess") : '';?></div>						
				<div class="error" id="error"><?php echo ($this->Session->check("Message.EventError"))? $this->Flash->render("EventError") : '';?></div>
			</div>
			
			<!-- message data -->
			<div class="col-md-12" style="overflow: auto";>
				<div class="table-responsive tbl-wrapper" >
					<table class="table table-bordered tbl_sumisho_inventory" id="event_tbl">

						<thead>
							<tr>
								<th width="150px"  style="text-align: center;"><?php echo __("イベント名"); ?></th>
								<th width="150px" style="text-align: center;"><?php echo __("参照イベント"); ?></th><!-- event reference number -->
								<th width="60px" colspan="3" style="min-width: 10rem;"><?php echo __("アクション"); ?></th>
							</tr>
						</thead>
						<tbody>  			

							<?php 
						
							if(isset($list)) 
							$cnt = count($list);

							for($i=0; $i<$cnt; $i++) {
								$disabled = '';
								$event_name       = $list[$i]['AssetEvent']['event_name'];
								$event_id         = h($list[$i]['AssetEvent']['id']);
								$flag             = h($list[$i]['AssetEvent']['flag']);
								$referencename    = h($Event_ref_data[$list[$i]['AssetEvent']['reference_event_id']]);
								if($flag == 2 || in_array($event_id, $refIds)) $disabled = 'disabled';
								if($flag != 'null')  
									{ ?>                    
										<tr>                  
											<td width="150px" style="word-break: break-all; text-align: left;"><?php echo h($event_name); ?></td>  
											<td width="150px" style="word-break: break-all;text-align: left;">

												<?php echo $referencename; ?>
											</td>

											
											<td width="20px" style="word-break: break-all;text-align: center;vertical-align:middle;font-size:1.3em !important;" class='edit'>
												<a class="<?php echo $disabled; ?>" href="#" onclick="Click_Edit(<?= h($event_id) ?>)" title='<?php echo __("編集");?>'><i class="fa-regular fa-pen-to-square" ></i>
												</a>
											</td>
											<td width="20px" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='remove'>
												<a class="<?php echo $disabled; ?>" href="#" onclick="Click_Delete(<?= h($event_id) ?>)" title='<?php echo __("削除");?>'><i class="fa-regular fa-trash-can"></i></a>
											</td>
											<td width="20px" style="word-break: break-all;text-align: center;vertical-align:middle;font-size:1.3em !important;" class=''>
											<?php 
											
											if ($flag =='1') { ?>
											<a class="glyphicon glyphicon-edit" id="active_status" href="#" onclick="Click_Active('<?php echo $event_id;?>');" title="<?php echo __('ACTIVE');?> ">   
											</a>
											<?php } else { ?>
											<a class="glyphicon glyphicon-edit" class="act_color" id="Inactive_status" href="#" onclick="Click_Inactive('<?php echo $event_id;?>');" title="<?php echo __('INACTIVE'); ?>">
											</a>      
											<?php } ?>              
											
													
										</td>

								</tr>   
							<?php  } }	?>

							<input type="hidden" name="hid_id" id="hid_id" class="txtbox" value="">	
							<input type="hidden" name="hid_page_no" id="hid_page_no" class="txtbox" value ="">
						</tbody>                            
					</table>
				</div>
			</div>
		</div>
	<?php } ?>

	<div class="row" style="clear:both;margin: 40px 0px;">
		<!-- Paginator check show 50 upper data -->
		<?php if($rowcount>50) {?>
		
			<div class="col-sm-12" style="padding: 10px;text-align: center;">
				<div class="paging">
					<?php
					echo $this->Paginator->first('<<');
					echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev disabled'));
					echo $this->Paginator->numbers(array('separator'=>'', 'modulus'=>6));
					echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
					echo $this->Paginator->last('>>');
					?>
				</div>
			</div> 
		<?php } ?>
		
	</div>
</div>

<?php
echo $this->Form->end();
?>

