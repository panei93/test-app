<?php echo $this->Form->create(false,array('url'=>'','type'=>'post','class'=>'','name'=>'account_review_form','id'=>'account_review_form')); 
?>

<style>
	.table .table-bordered .acc_review td.table-middle, .table th {
    	min-width: 100px;    	
	}
	.table tr.show-data{
		background-color:#BEC6CE;
	}
	.acc_mgr_commt {
	  	width: 250px;
	    height: 50px;
	}
  	td, th{
      padding: 5px;
   }
   .fl-scrolls{
        z-index: 1 !important;
    }
    .jconfirm .jconfirm-box div.jconfirm-content-pane .jconfirm-content {
    	overflow: hidden !important; 
    }
</style>

<script>

	$(function(){

		//fixed column and header
		if($('#tbl_manager_comment').length > 0) { // check data is at least 1 row/column keep
			$('.tbl-wrapper').freezeTable({ 
				'columnNum' : 4,
				'columnKeep' : true,
				'freezeHead': true,			 
				'scrollBar': true,
		  	});
			setTimeout(function(){
	            $('.tbl-wrapper').freezeTable('resize');
	         }, 1000);
		}
		
		var show_btn = <?php echo json_encode($show_btn); ?>;

	  	// end fixed column and header	  	
		$("#load").hide();
		document.getElementById('contents').style.visibility="hidden";	

		/* floating scroll */
		if($(".tbl-wrapper").length) {
			$(".tbl-wrapper").floatingScroll();
		}
         
		/* merge same column for show_data class name*/		

		if($('html').scrollTop() != 0){
			$('html, body').animate({ scrollTop: 0 }, 'slow');
		}
	
		/* checkbox check/uncheck */
		/* clone-column-table-wrap, clone-head-table-wrap use because of freeze column*/
		$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').click(function() {

			if($(this).is(':checked')) {	

				$('.acc_mgr_confirm').each(function() {

					if (!($(this).is(':disabled'))) {
						$(this).prop('checked',true);
						$('.tbl-wrapper #chk_acc_mgr_confirm, .clone-head-table-wrap #chk_acc_mgr_confirm').prop('checked',true);
			    	}
				});

			} else {
				
				$('.acc_mgr_confirm').each(function() {

					if (!($(this).is(':disabled'))) {
						$(this).prop('checked',false);
						$('.tbl-wrapper #chk_acc_mgr_confirm,.clone-head-table-wrap #chk_acc_mgr_confirm').prop('checked',false);
			    	}
				});
				
			}
			
		});
	
		$('.acc_mgr_confirm').click(function() {
			$('.clone-column-table-wrap .acc_mgr_confirm,.clone-head-table-wrap .acc_mgr_confirm').prop('checked', true);//to check all derived table
			checkToggle();
		});
		
		function checkToggle() {
			var isCheck = true;
			var disable = true;
			$('.clone-column-table-wrap .acc_mgr_confirm, .clone-head-table-wrap .acc_mgr_confirm, .acc_mgr_confirm').each(function() {
				if($(this).is(':checked') == false) {
					isCheck = false;
				}
				if($(this).is(':disabled') == false) {
					disable = false;
				}
			});
			
			if(isCheck == false) {
				$('.clone-head-table-wrap #chk_acc_mgr_confirm, .clone-column-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('checked', false);
			} else {
				$('.clone-head-table-wrap #chk_acc_mgr_confirm, .clone-column-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('checked', true);
			}
			if(disable == false) {
				$('.clone-head-table-wrap #chk_acc_mgr_confirm, .clone-column-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('disabled', false);
			} else {
				$('.clone-head-table-wrap #chk_acc_mgr_confirm, .clone-column-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('disabled', true);
			}
		}
		
		
		$("#target_month").val("<?php echo $PERIOD;?>");
		$("#refer_date").val("<?php echo $reference_date?>");
		$("#submission_date").val("<?php echo $submission_deadline?>");
		
		checkToggle();
		// <?php if($role_id == 1){?>
		// 	$('.acc_mgr_commt').prop('disabled', true);
		// 	$(".acc_mgr_confirm").attr("disabled", true);
		// 	$(".clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm").attr("disabled", true);
		// <?php }?>
			
	});

   
	function click_save_btn(){
		
		var DataArray = [];		
		var checkflag = false;
		var isAllRowDisable = true; // to check all row is disable or not
		
		document.getElementById("error").innerHTML =  '';
		document.getElementById("success").innerHTML =  ''; 
		var chkArr = [];
		$('#tbl_manager_comment tbody tr').each(function(i, tr){ 
			 var myDataRows = [];
			 var chk_status = false;
			 var acc_mgr_check = $(this).find('.acc_mgr_confirm').val();
			 
			 if ($(this).find('.acc_mgr_confirm').is(':disabled') == false) {
			 	if($(this).find('.acc_mgr_confirm').is(':checked')){
				 	var chk_status = true;
				 	chkArr.push(chk_status);				 	
				 }
			 	var text_value=$(this).find('.acc_mgr_commt').val();
			 	if( chk_status == true || text_value != '')
				{
					myDataRows.push(acc_mgr_check,text_value,chk_status);
					DataArray.push(myDataRows);
					checkflag = true;
				}
				// if some row is enable, then
				isAllRowDisable = false;
			}
			 
			if(chk_status == false && text_value == ''){
				$("#error").append(errMsg(commonMsg.JSE030));
				checkflag = false;	
				$("html, body").animate({ scrollTop: 0 }, 'slow');	
				return false;		
			}
			
		});
		 
		var myJSONString = JSON.stringify(DataArray);
		
		if(checkflag){
			$.confirm({
				   title: "<?php echo __('保存確認'); ?>",
				   icon: 'fas fa-exclamation-circle',
				   type: 'green',
				   typeAnimated: true,
				   closeIcon: true,
				   columnClass: 'medium',
				   animateFromElement: true,
				   animation: 'top',
				   draggable: false,
				   content: errMsg(commonMsg.JSE009),
				   buttons: {   
				        ok: {
				            text: "<?php echo __('はい');?>",
				          	btnClass: 'btn-info',
				            action: function(){
				            		var self = this;									
									$('#json_data').val(myJSONString);
									getMail('save', 'saveSapAccountReviews', chkArr.length);		                                  		               
					          	}
				       },	  
				  cancel : {
				       	text: "<?php echo __('いいえ');?>",
				           btnClass: 'btn-default',
				       	cancel: function(){
				           console.log('the user clicked cancel');
				       		}
				       	 }
				    	},
				   theme: 'material',
				   animation: 'rotateYR',
				   closeAnimation: 'rotateXR'
			});
		} else {
			if(isAllRowDisable) {
				$("#error").append(errMsg(commonMsg.JSE028));
				$("html, body").animate({ scrollTop: 0 }, 'slow');
			}
		}
	}
	
	function click_approve_btn(){
		var DataArray = [];		
		var checkflag = true;

		document.getElementById("error").innerHTML =  '';
		document.getElementById("success").innerHTML =  ''; 
		document.getElementById('contents').style.visibility="visible";
        document.getElementById('load').style.visibility="visible"; 
		
		$('#tbl_manager_comment tbody tr').each(function(i, tr){

			if ($(this).prop('checked')==true){ 

			 var myDataRows = [];
			 var acc_mgr_commt = $(this).find('.acc_mgr_commt').val();
			 var acc_mgr_check = $(this).find('.acc_mgr_confirm').val();
			 
			 myDataRows.push(acc_mgr_check,acc_mgr_commt);
			 DataArray.push(myDataRows);
			}
		});
		var chk = true;	
		if(chk) {
			$.confirm({
				   title: "<?php echo __('承認確認'); ?>",
				   icon: 'fas fa-exclamation-circle',
				   type: 'green',
				   typeAnimated: true,
				   closeIcon: true,
				   columnClass: 'medium',
				   animateFromElement: true,
				   animation: 'top',
				   draggable: false,
				   content: errMsg(commonMsg.JSE022),
				   buttons: {   
				        ok: {
				            text: "<?php echo __('はい');?>",
				          	btnClass: 'btn-info',
				          	action: function(){								
								$('#json_data').val(JSON.stringify(DataArray));
								getMail('approve', 'ApproveSapAccountReviews');
			            }
				       },	  
				  cancel : {
				       	text: "<?php echo __('いいえ');?>",
				           btnClass: 'btn-default',
				       	cancel: function(){
				           console.log('the user clicked cancel');
				       		}
				       	 }
				    	},
				   theme: 'material',
				   animation: 'rotateYR',
				   closeAnimation: 'rotateXR'
			});
			
		}    
	}
	function click_approve_cancel(){
		
		var chk = true;
		if(chk) {
			$.confirm({
				   title: "<?php echo __('承認キャンセル確認'); ?>",
				   icon: 'fas fa-exclamation-circle',
				   type: 'green',
				   typeAnimated: true,
				   closeIcon: true,
				   columnClass: 'medium',
				   animateFromElement: true,
				   animation: 'top',
				   draggable: false,
				   content: errMsg(commonMsg.JSE012),
				   buttons: {   
				        ok: {
				            text: "<?php echo __('はい');?>",
				          	btnClass: 'btn-info',
				          	action: function(){						
								$('#json_data').val("");
								getMail('approve_cancel', 'ApproveCancelSapAccountReviews');
		                        return true;		          		
			            	}
				            
				       },	  
				  cancel : {
				       	text: "<?php echo __('いいえ');?>",
				           btnClass: 'btn-default',
				       	cancel: function(){
				           console.log('the user clicked cancel');
				       		}
				       	 }
				    	},
				   theme: 'material',
				   animation: 'rotateYR',
				   closeAnimation: 'rotateXR'
			});
			
		}    
	}
	function click_approve_reject(){
		
		var chk = true;
		if(chk) {
			$.confirm({
				   title: "<?php echo __('拒否を確認'); ?>",
				   icon: 'fas fa-exclamation-circle',
				   type: 'green',
				   typeAnimated: true,
				   closeIcon: true,
				   columnClass: 'medium',
				   animateFromElement: true,
				   animation: 'top',
				   draggable: false,
				   content: "<?php echo __("すべてのデータを拒否してもよろしいですか？") ?>",
				   buttons: {   
				        ok: {
				            text: "<?php echo __('はい');?>",
				          	btnClass: 'btn-info',
				          	action: function(){								
								$('#json_data').val("");
								getMail('reject', 'RejectAccountReveiw');
		                        return true;
			            	}
				            
				       },	  
				  cancel : {
				       	text: "<?php echo __('いいえ');?>",
				           btnClass: 'btn-default',
				       	cancel: function(){
				           console.log('the user clicked cancel');
				       		}
				       	 }
				    	},
				   theme: 'material',
				   animation: 'rotateYR',
				   closeAnimation: 'rotateXR'
			});
			
		}    
	}
		
	function Excel_download_btn(){
		document.forms[0].action = "<?php echo $this->webroot; ?>SapAccountReviews/Download_Account_Review";
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;
	}
	
	function getMail(func,myRouteString, chkArrLen = 0) {

		var layer_code = $("#dept_name").val();
		var page = 'SapAccountReviews';
		var mail = {};
		$.ajax({
			type:'post',
			url: "<?php echo $this->webroot; ?>SapImports/getMailLists",
			data: {layer_code : layer_code, page: page, function: func},
			dataType: 'json',
			success: function(data) {
				var rowCount = <?php echo json_encode($page_count); ?>;
				var chkCount = <?php echo json_encode($chk_count); ?>;
				
				var mailSend = (data.mailSend == '' || (func == 'save' && rowCount != (chkCount+chkArrLen))) ? '0' : data.mailSend;
	            $("#mailSend").val(mailSend);
				if(mailSend == 1) { 

					$("#mailSubj").val(data.subject);
					$("#mailBody").val(data.body);			
					if(data.mailType == 1) {
						
						if(data.to != undefined) {
		                    var toEmail = Object.values(data.to);
		                    $("#toEmail").val(toEmail);
		                }
		                if(data.cc != undefined) {
		                    var ccEmail = Object.values(data.cc);
		                    $("#ccEmail").val(ccEmail);
		                }
		                if(data.bcc != undefined) {
		                    var bccEmail = Object.values(data.bcc);
		                    $("#bccEmail").val(bccEmail);
		                }
		            loadingPic();
						document.forms['account_review_form'].action = "<?php echo $this->webroot; ?>SapAccountReviews/"+myRouteString;
						document.forms['account_review_form'].method = "POST";
						document.forms['account_review_form'].submit();
						return true;
						
					}else{

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
						console.log(myRouteString);
						$("#account_review_form").attr('action', '<?php echo $this->webroot; ?>SapAccountReviews/'+myRouteString);
					}
				}else{
					loadingPic();
					document.forms['account_review_form'].action = "<?php echo $this->webroot; ?>SapAccountReviews/"+myRouteString;
					document.forms['account_review_form'].method = "POST";
					document.forms['account_review_form'].submit();
					return true;	
				}
				
			},
			error: function(e) {
				console.log('Something wrong! Please refresh the page.');
			}

		});
		return mail;
	}

	function loadingPic() {
    	$("#overlay").show();
    	$('.jconfirm').hide();
	}
</script>
<?php
   echo $this->element('autocomplete', array(
                        "to_level_id" => "",
                        "cc_level_id" => "",
                        "bcc_level_id" => "",
                        "submit_form_name" => "account_review_form",
                        "MailSubject" => "",
                        "MailTitle"   => "",
                        "MailBody"    =>""
                     ));
?>
<body>
	<div id="overlay">
   		<span class="loader"></span>
	</div>
    <div id="load"></div>
    <div id="contents"></div>

</body>
<?php $show_confirm = '';?>
<div class = 'container register_container'>	
	<input type="hidden" name="json_data" id="json_data" value = "" >
	<input type="hidden" name="toEmail" id="toEmail" value="">
	<input type="hidden" name="ccEmail" id="ccEmail" value="">
	<input type="hidden" name="bccEmail" id="bccEmail" value="">
	<input type="hidden" name="mailSubj" id="mailSubj">
	<input type="hidden" name="mailBody" id="mailBody">
	<input type="hidden" name="mailSend" 	id="mailSend"> 
 	<h3 class=""><?php echo __("経理レビュー"); ?></h3><hr>
 		
 	<!-- Please fill comment in each uncheck row of red border box. -->
 		
 	<div id="success" class="success"><?php echo $successMsg; ?><?php echo ($this->Session->check("Message.save_success"))? $this->Flash->render("save_success") : ''; ?><?php echo ($this->Session->check("Message.del_success"))? $this->Flash->render("del_success") : ''; ?></div>
	<div id="error" class="error" ><?php echo $errorMsg; ?><?php echo ($this->Session->check("Message.acc_review_del_fail"))? $this->Flash->render("acc_review_del_fail") : ''; ?><?php echo ($this->Session->check("Message.approve_update_fail"))? $this->Flash->render("approve_update_fail") : '' ;?><?php echo ($this->Session->check("Message.save_fail"))? $this->Flash->render("save_fail") : ''; ?><?php echo ($this->Session->check("Message.review_del_fail"))? $this->Flash->render("review_del_fail") : ''; ?></div>
	<div id="sucss" class="success"></div>
	<div id="err" class="error" ></div>
	<div class ="row line">
		<div class = "col-md-6">
			<div class = "form-group">
				<label class="col-md-4 control-label"><?php echo __("部署"); ?></label>
			      <div class="col-md-8">
					<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='dept_name' value = '<?php echo $BA_CODE;?>' disabled="disabled" >     
				</div>
			</div>
		</div>
	</div>
	<div class ="row line">
		<div class = "col-md-6">
			<div class = "form-group">
				<label class="col-md-4 control-label"><?php echo __("部署名"); ?></label>
			      <div class="col-md-8">
					<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='dept_name' value = '<?php echo $layer_name;?>' disabled="disabled" >     
				</div>
			</div>
		</div>
	</div>
	<div class ="row line">
		<div class = "col-md-6">
			<div class = "form-group">
				<label class="col-md-4 control-label"><?php echo __("対象月"); ?></label>
			      <div class="col-md-8">
					<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='target_month' value = '' disabled="disabled" >     
				</div>
			</div>
		</div>
	</div>
	<div class ="row line">
		<div class = "col-md-6">
			<div class = "form-group">
				<label class="col-md-4 control-label"><?php echo __("基準年月日"); ?></label>
			      <div class="col-md-8">
					<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='refer_date' value = '' disabled="disabled" >     
				</div>
			</div>
		</div>
	
	</div>
	<div class ="row line">
		<div class = "col-md-6">
			<div class = "form-group">
				<label class="col-md-4 control-label"><?php echo __("提出期日"); ?></label>
			      <div class="col-md-8">
					<input class ='form-control register' type = "textbox" id='submission_date' value = '' disabled="disabled" >     
				</div>
			</div>
		</div>
	</div>
	<?php if(!empty($page)){?>
		<div class="row text-right">
			<?php if($show_btn['save']){ 
				$show_confirm = 'show';?>
				<input type="button" class="btn btn_save but_register" id="save_btn" onClick = "click_save_btn();" value = "<?php echo __('保存');?>">
			<?php }?>
			<?php if($show_btn['reject']){?>
		     	<input type ="button" class="emp_register but_register " id="reject_btn" onClick = "click_approve_reject();" value = "<?php echo __('差し戻し');?>">
		    <?php }if($show_btn['approve']){?>
				<input type ="button" class="emp_register but_register " id="approve_btn" onClick = "click_approve_btn();" value = "<?php echo __('承認');?>">
			<?php }?>
			<?php if($show_btn['approve_cancel']){?>
				<input type="button" class="emp_register but_register" id="approve_btn_cancel" onClick = "click_approve_cancel();" value = "<?php echo __('承認キャンセル');?>">
			<?php }?>
				<input type="button" class="emp_register btn btn_save but_register" id="excel_download_btn" onClick = "Excel_download_btn();" value = "<?php echo __('Excelダウンロード');?>">
		</div>
	 <?php }?>
	<div class="row">
		<?php if(!empty($page)){?>
            <div id="succc" class="msgfont" style="padding-top:18px;height:0px;">
               <?php echo $row_succ_Msg; ?><label style = "float:right;height:0px;margin-bottom: 15px;"><?php echo __("※注1　：　債権はプラス、債務はマイナスで表示しております");?></label>
            </div>
    	<?php }?>
	<div id="err" class="no-data" ><?php echo $row_no_Msg; ?></div>
	<div class="table-responsive tbl-wrapper" style="overflowtable table-bordered acc_review-x:auto;">
		<?php if(!empty($page)){
			$row_cnt = 0; $chk_flg = 0; ?>
			<table class="table table-bordered" style="margin-top:0px;" id="tbl_manager_comment">
				<thead class="check_period_table">
					<tr>
						<th rowspan="2" width="10%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("勘定コード名"); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("相手先コード"); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("相手先名"); ?></th>
		 				<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("物流Index No."); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("転記日付"); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("計上基準日"); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("入出荷年月日"); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("決済予定日"); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("滞留日数"); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("満期年月日"); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("明細テキスト"); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("営業担当者"); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("円貨金額"); ?></th>
						<th rowspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("経理コメント"); ?></th>
						<th colspan="4" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("担当者コメント入力欄"); ?></th>
						<th colspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("管理職"); ?></th>
						<th colspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("経理担当者"); ?></th>
						<th colspan="2" width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("経理管理者"); ?></th>
						
					</tr>
					<tr>
						<th width="5%" class="" style="vertical-align : middle;text-align:center;" ><?php echo __("確認完了"); ?></th>
						<th width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("滞留理由"); ?></th>
						<th width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("決済日"); ?></th>
						<th width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("備考"); ?></th>
						<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("確認済"); ?></th>
						<th width="15%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("コメント入力欄"); ?></th>
						<th width="5%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("確認欄"); ?></th>
						<th width="15%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("コメント入力欄"); ?></th>
						<th width="8%" class="table-middle" style="vertical-align : middle;text-align:center;" >
							<?php echo __("確認欄"); ?><br/>
							<input width="5%" type="checkbox" name="chk_acc_mgr_confirm" id="chk_acc_mgr_confirm" checked="checked">
						</th>
						<th width="15%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("コメント入力欄"); ?></th>
					</tr>
					
				</thead>
				<tbody>
					<?php foreach($page as $row){ 
						
						$merge_flag = "";
						$row_cnt++;
						$sap_id = $row['Sap']['id'];
						$account_code = $row['Sap']['account_code'];
						$account_name = h($row['Sap']['account_name']);
						$Destination_code = h($row['Sap']['destination_code']);
						$Destination = h($row['Sap']['destination_name']);
						$Logistics_Index = h($row['Sap']['logistic_index_no']);

						$Posting_Date = $row['Sap']['posting_date'];
						$Posting_Date = ($Posting_Date == '' || $Posting_Date == '0000-00-00' || $Posting_Date == '0000-00-00 00:00:00' || $Posting_Date == '-0001-11-30')? '' : date('Y-m-d',strtotime($Posting_Date));

						$Recorded_date = $row['Sap']['recorded_date'];
						$Recorded_date = ($Recorded_date == '' || $Recorded_date == '0000-00-00' || $Recorded_date == '0000-00-00 00:00:00' || $Recorded_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($Recorded_date));
						
						$receipt_date = $row['Sap']['receipt_shipment_date'];
						$receipt_date = ($receipt_date == '' || $receipt_date == '0000-00-00' || $receipt_date == '0000-00-00 00:00:00' || $receipt_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($receipt_date));
						
						$schedule_date = $row['0']['sap_schedule_date'];
						$schedule_date = ($schedule_date == '' || $schedule_date == '0000-00-00' || $schedule_date == '0000-00-00 00:00:00' || $schedule_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($schedule_date));

						$numbers_day = h($row['Sap']['numbers_day']);
						$currency = $row['Sap']['currency'];
						$yen_amount = $row['0']['jp_amount'];
						$preview_comment = h($row['Sap']['preview_comment']);
						$maturity_date = $row['Sap']['maturity_date'];
						$maturity_date = ($maturity_date == '' || $maturity_date == '0000-00-00' || $maturity_date == '0000-00-00 00:00:00' || $maturity_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($maturity_date));
						$line_item_text = h($row['Sap']['line_item_text']);
						$sale_representative = h($row['Sap']['sale_representative']);
						$flag = $row['Sap']['flag'];
						$busi_incharge_reason = h($row['SapBusinessInchargeComment']['reason']);
						
						$busi_incharge_settlement_date = $row['SapBusinessInchargeComment']['settlement_date'];
						$busi_incharge_settlement_date = ($busi_incharge_settlement_date == '' || $busi_incharge_settlement_date == '0000-00-00' || $busi_incharge_settlement_date == '0000-00-00 00:00:00' || $busi_incharge_settlement_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($busi_incharge_settlement_date));
						
						$busi_incharge_remark = h($row['SapBusinessInchargeComment']['remark']);
						$busi_admin_comment = h($row['SapBusinessAdminComment']['business_admin_comment']);
						$acc_incharge_comment = h($row['SapAccountInchargeComment']['acc_incharge_comment']);
						$acc_submgr_comment = h($row['SapAccSubManagerComment']['acc_submgr_comment']);
						
						# checkbox conditions
						if($flag >= 3) {
							$chk_busi_inc_confirm = "checked='checked'";
						} else {
							$chk_busi_inc_confirm = "";
						}
						if($flag >= 4) {
							$chk_busi_admin_confirm = "checked='checked'";
						} else {
							$chk_busi_admin_confirm = "";
						}
						if($flag >= 6) {
							$chk_acc_inc_confirm = "checked='checked'";
						} else {
							$chk_acc_inc_confirm = "";
						}
						
						// Calulating the difference in timestamps
						$diff = strtotime($reference_date) - strtotime($schedule_date);
						
						// 1 day = 24 hours
						// 24 * 60 * 60 = 86400 seconds
						$NoOfDays = round($diff/86400);//abs(round($diff / 86400));
						
						if($flag != 6 && $flag != 7 && $flag != 8){
							$merge_flag = 'same';
							$chk_flg++; ?>
							<tr class = 'flag_chk_color'>

								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;"class="show_account_name"><?php echo $account_name; ?>
								<input type="hidden" name="show_account_code" class="show_account_code" value="<?php echo $account_code;?>">
								<!-- account_code -->
								</td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_dest_code"><?php echo $Destination_code; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_dest_name"><?php echo $Destination; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_logistics_no"><?php echo $Logistics_Index; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_posting_date"><?php echo $Posting_Date; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_recorded_date"><?php echo $Recorded_date; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_receipt_date"><?php echo $receipt_date; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_schedule_date"><?php echo $schedule_date; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:right;" class="show_no_days"><?php echo $NoOfDays; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_maturity_date"><?php echo $maturity_date; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_line_item_text"><?php echo $line_item_text; ?>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_sale_man"><?php echo $sale_representative; ?>
								<input type="hidden" class="show_currency" value="<?php echo $currency;?>">
								<input type="hidden" class="show_flag" value="<?php echo $merge_flag; ?>"></td>
								<td style="width: 15%; word-wrap: break-word;text-align:right;vertical-align:middle;" class="show_yen_amt"><?php echo number_format($yen_amount); ?>
								</td>
								<td style="width:15%; word-wrap: break-word;text-align:left;vertical-align:middle;"><?php echo $preview_comment; ?></td>
								<td style="text-align:center;vertical-align:middle;"><input type="checkbox" name="busi_inc_confirm" class="test_line" <?php echo $chk_busi_inc_confirm; ?> disabled></td>
								<td style="text-align:left;width: 15%; word-wrap: break-word;vertical-align:middle;"><?php echo nl2br($busi_incharge_reason); ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;"><?php echo nl2br($busi_incharge_settlement_date); ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;"><?php echo nl2br($busi_incharge_remark); ?></td>
								<td style="text-align:center;vertical-align:middle;"><input type="checkbox" name="busi_admin_confirm" class="test_line" <?php echo $chk_busi_admin_confirm; ?> disabled></td>
								<td style="width: 8%; word-wrap: break-word;"><?php echo nl2br($busi_admin_comment); ?></td>
								<td style="text-align:center;vertical-align:middle;"><input type="checkbox" name="acc_inc_confirm" class="test_line" id="chk_confirm" <?php echo $chk_acc_inc_confirm; ?> disabled></td>
								<td style="text-align:left;width: 15%; word-wrap: break-word;"><?php echo nl2br($acc_incharge_comment);?></td>
								<?php if($acc_submgr_comment != '' && $flag == 6){?>

									<td style="vertical-align:middle;text-align:center;"><input type="checkbox" name="acc_mgr_confirm" class="acc_mgr_confirm" value = <?php echo h($sap_id);?>></td>
								<?php }else if($flag > 6){?>
									<td style="vertical-align:middle;text-align:center;"><input type="checkbox" name="acc_mgr_confirm" class="acc_mgr_confirm" checked value = <?php echo h($sap_id);?>></td>
								<?php }	
									else{?>							
										<td style="vertical-align:middle;text-align:center;"><input type="checkbox" name="acc_mgr_confirm" class="acc_mgr_confirm" disabled value = <?php echo h($sap_id);?>></td>
								<?php }?>
								<td style="text-align:center;vertical-align:middle;"><textarea name="acc_mgr_commt" id = "acc_mgr_commt" class="form-control acc_mgr_commt" disabled ><?php echo str_replace('\r\n', "\r\n",$acc_submgr_comment);?></textarea></td>
								
								<?php if($flag == 7 || $flag == 8){?>
									<td style="vertical-align:middle;text-align:center;"><input type="checkbox" name="acc_mgr_confirm" class="acc_mgr_confirm" checked="" value = <?php echo h($sap_id);?> disabled></td><!-- acc_submgr_confirm -->
								<?php } else {?>
									<td></td>
								<?php }?>								
										
								
							</tr>
						<?php } else {?>
							
							<tr class="show_data">
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_account_name"><?php echo $account_name; ?>
								<input type="hidden" name="show_account_code" class="show_account_code" value="<?php echo $account_code;?>">
								</td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align: left;" class="show_dest_code"><?php echo $Destination_code; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align: left;" class="show_dest_name"><?php echo $Destination; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align: left;" class="show_logistics_no"><?php echo $Logistics_Index; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_posting_date"><?php echo $Posting_Date; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_recorded_date"><?php echo $Recorded_date; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_receipt_date"><?php echo $receipt_date; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_schedule_date"><?php echo $schedule_date; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:right;" class="show_no_days"><?php echo $NoOfDays; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_maturity_date"><?php echo $maturity_date; ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_line_item_text"><?php echo $line_item_text; ?>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;" class="show_sale_man"><?php echo $sale_representative; ?></td>
								<input type="hidden" class="show_currency" value="<?php echo $currency;?>">
								<input type="hidden" class="show_flag" value="<?php echo $merge_flag; ?>">
								<td style="width: 15%; word-wrap: break-word;text-align:right;vertical-align:middle;" class="show_yen_amt"><?php echo number_format($yen_amount); ?>
								</td>
								<td style="width:15%; word-wrap: break-word;text-align:left;vertical-align:middle;"><?php echo $preview_comment; ?></td>
								<td style="text-align:center;vertical-align:middle;"><input type="checkbox" name="busi_inc_confirm" class="test_line" <?php echo $chk_busi_inc_confirm; ?> disabled></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;"><?php echo nl2br($busi_incharge_reason); ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;"><?php echo nl2br($busi_incharge_settlement_date); ?></td>
								<td style="width: 15%; word-wrap: break-word;vertical-align:middle;text-align:left;"><?php echo nl2br($busi_incharge_remark); ?></td>
								<td style="text-align:center;vertical-align:middle;"><input type="checkbox" name="busi_admin_confirm" class="test_line" <?php echo $chk_busi_admin_confirm; ?> disabled></td>
								<td style="width: 8%; word-wrap: break-word;vertical-align:middle;text-align:left;"><?php echo nl2br($busi_admin_comment); ?></td>
								<td style="text-align:center;vertical-align:middle;"><input type="checkbox" name="acc_inc_confirm" class="test_line" id="chk_confirm" <?php echo $chk_acc_inc_confirm; ?> disabled></td>
								<td style="text-align:left;width: 15%; word-wrap: break-word;vertical-align:middle;"><?php echo nl2br($acc_incharge_comment); ?></td>
								
								<?php 
								
									if($flag == 8 || !$show_btn['save']) {
										$disable_el = 'disabled';
									} else {
										$disable_el = '';
									}
								?>
								<?php if($acc_submgr_comment != '' && $flag == 6){?>
									
									<td style="vertical-align:middle;text-align:center;"><input type="checkbox" name="acc_mgr_confirm" class="acc_mgr_confirm" value = <?php echo h($sap_id);?>></td>
								<?php }else if($flag > 6){?>
									<td style="vertical-align:middle;text-align:center;"><input type="checkbox" name="acc_mgr_confirm" class="acc_mgr_confirm" checked value = "<?php echo h($sap_id);?>" <?php echo $disable_el; ?>></td>
								<?php }	
								else{?>							
									<td style="vertical-align:middle;text-align:center;"><input type="checkbox" name="acc_mgr_confirm" class="acc_mgr_confirm" value = <?php echo h($sap_id);?>></td>
								<?php }?>
								
								<td style="text-align:center;vertical-align:middle;"><textarea name="acc_mgr_commt" id = "acc_mgr_commt" class="form-control acc_mgr_commt" <?php echo $disable_el; ?>><?php echo str_replace('\r\n', "\r\n",$acc_submgr_comment);?></textarea></td>
								
							</tr>
						<?php }?>
					<?php }?>
				</tbody>								
			</table>
			
		<?php }?> <!-- check empty -->
	</div><!-- end row -->
	</div><!-- end table div -->
	<?php if(!empty($page)){?>
	<?php if($page_count>50){?>
	<div class="col-sm-12" style="padding: 10px;text-align: center;">
		<div class="paging">
		<?php
			echo $this->Paginator->first('<<');
		    echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev page disabled '));
		    echo $this->Paginator->numbers(array('separator'=>'', 'modulus'=>6,'currentTag' => 'a', 'currentClass' => 'active'));
		    echo $this->Paginator->next(' >', array(), null, array('class' => 'next page disabled'));
		    echo $this->Paginator->last('>>');
		?>
		</div>
		<?php }?>
	</div><br/><br/><br/><br/><br/>
	
 	 <?php }?>

<!-- check not empty -->
<div>
	<input type="hidden" name="hidMgrComment" id="hidMgrComment">
	<input type="hidden" name="hid_del_sapId" id="hid_del_sapId" value = "" >
	<input type="hidden" name="hid_show_confirm" id="hid_show_confirm" value = "<?php echo $show_confirm;?>" >
</div>
</div><!-- container end -->
<?php
    echo $this->Form->end();
?>