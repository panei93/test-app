<style type="text/css">
	body {
		font-family: KozGoPro-Regular;
	}
	/* .table_panel, #btn_update {
		display: none;
	} */
	#sample_reg {
		min-width: 1800px;
	}
	.td_center {
		text-align: center;
	}
	.td_left {
		text-align: left;
	}
	.td_right {
		text-align: right;
	}
	.link-list {
		margin-top: 6px;
	}
	.btn-delete {
		float: right;
		color: red;
		margin-top: -25px;
	}
	.btn-delete:hover {
		cursor: pointer;
	}
	a.down-link {
		display: inline-block;
		width: 100px;
		color: #19b5fe;
		margin-right: 40px;
		margin-left: 40px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		text-align: center;
		text-decoration: underline;
	}
	.btn_row_delete, .trash-color {
		border: none;
		background: none;
		color: #c81d25;
		cursor: pointer;
	}
	.btn_row_delete input{
		border: none;
		background: none;
	}
	.edit {
		display: block;
	}
	.copy {
		display: block;
		color: #05c46b;
	}
	.btn_style {
		width: 150px;
		margin: 5px;
	}
	.row {
		display: block;
		margin-right: 0px;
	}
</style>

<div id="overlay">
	<span class="loader"></span>
</div>
<h3><?php echo (__("サンプルデータ作成"));?></h3>
<hr>
<div class="errorSuccess">
	<div class="success" id="success"><?php echo ($this->Session->check("Message.sampleOK"))? $this->Flash->render("sampleOK") : ''; ?></div>
	<div class="error" id="error"><?php echo ($this->Session->check("Message.sampleFail"))? $this->Flash->render("sampleFail") : ''; ?></div>
</div>
<!-- <div class="content register_container"> -->
	<?php	
	echo $this->element("autocomplete", array(
						"to_level_id" => "",
						"cc_level_id" =>"",
						"submit_form_name" => "",
						"MailSubject" => "",
						"MailTitle" => "",
						"MailBody" => ""));
						
						?>
	<form class="form-inline" method="post" action="<?php echo $this->webroot; ?>SampleRegistrations/saveSampleData" id="sample_reg_form" name="sample_reg_form"> 
		<input type="hidden" name="data-action" id="data-action">		
		<div class="row register_form">
			<input type="hidden" name="edit_sample_id" id="edit_sample_id">
			<div class="form-row">
			<div class="form-group col-md-6">
				<label for="layer_code" class="control-label">
					<?php echo __('部署');?>
				</label>
				<input type="text" class="form-control form_input" id="layer_code" name="layer_code" value="<?php echo $layer_code; ?>" readonly/>
			</div>
			<div class="form-group col-md-6">
				<label for="layer_name" class="control-label">
					<?php echo __('部署名');?>
				</label>
				<input type="text" class="form-control form_input" id="layer_name" name="layer_name" value="<?php echo $layer_name; ?>" readonly/>
			</div>
		
			<div class="form-group col-md-6">
				<label for="period" class="control-label">
					<?php echo __('対象月');?>
				</label>
				<input type="text" class="form-control form_input" id="period" name="period" value="<?php echo $period; ?>" readonly/>
			</div>
			<div class="form-group col-md-6">
				<label for="category" class="control-label">
					<?php echo __('カテゴリー');?>
				</label>
				<input type="text" class="form-control form_input" id="category" name="category" value="<?php echo $this->Session->read('SAMPLECHECK_CATEGORY'); ?>" readonly/>
			</div>
			<div class="form-group col-md-6">
				<label for="incharge_name" class="control-label required">
					<?php echo __('担当者');?>
				</label>
				<input type="text" class="form-control form_input" id="incharge_name" name="incharge_name" value="" />
			</div>
		
			<div class="form-group col-md-6">
				<label for="project_title" class="control-label required">
					<?php echo __('案件名');?>
				</label>
				<input type="text" class="form-control form_input" id="project_title" name="project_title" value="" />
			</div>
			<div class="form-group col-md-6">
				<label for="posting_date" class="control-label required">
					<?php echo __('計上日');?>
				</label>
				<div class="input-group date datepicker form_input" data-provide="datepicker">
					<input type="text" class="form-control" id="posting_date" name="posting_date" value="" autocomplete="off"/>
					<span class="input-group-addon">
						<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
			</div>

			<div class="form-group col-md-6">
				<label for="index_no" class="control-label required">
					<?php echo __('Index');?>
				</label>
				<input type="hidden" id="hid_index_no" name="hid_index_no" value="" />
				<input type="text" class="form-control form_input" id="index_no" name="index_no" maxlength="500"/>
			</div>
			<div class="form-group col-md-6">
				<label for="account_item" class="control-label required">
					<?php echo __('勘定科目');?>
				</label>
				<input type="text" class="form-control form_input" id="account_item" name="account_item" />
			</div>
		
			<div class="form-group col-md-6">
				<label for="destination_code" class="control-label required">
					<?php echo __('相手先');?>
				</label>
				<input type="text" class="form-control form_input" name="destination_code" id="destination_code" maxlength="10"/>
			</div>
			<div class="form-group col-md-6">
				<label for="destination_name" class="control-label required">
					<?php echo __('相手先名');?>
				</label>
				<input type="text" class="form-control form_input" id="destination_name" name="destination_name" value="" maxlength="500"/>
			</div>
		
			<div class="form-group col-md-6">
				<label for="money_amt" class="control-label required">
					<?php echo __('金額');?>
				</label>
				<input type="text" class="form-control form_input" id="money_amt" name="money_amt" maxlength="13"/>
			</div>

			<div class="form-group col-md-6">
				<label for="submission_deadline_date" class="control-label required">
					<?php echo __('提出期限');?>
				</label>
				<div class="input-group date datepicker form_input" data-provide="datepicker">
					<input type="text" class="form-control" id="submission_deadline_date" name="submission_deadline_date" value="" autocomplete="off"/>
					<span class="input-group-addon">
						<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
			</div>

			<div class="form-group col-md-6">
				<label for="request_docu" class="control-label required">
					<?php echo __('要求書類');?>
				</label>
				<textarea id="request_docu" name="request_docu" class="form-control form_input"></textarea>
			</div>
				
			<div class="form-group col-md-6">
				<label for="remark" class="control-label">
					<?php echo __('備考');?>
				</label>
				<textarea id="remark" name="remark" class="form-control form_input"></textarea>
			</div>

		

		</div>
	</div>
	</form>
	<div class="table_panel"> <!-- to show/hide based on table row -->
	<div class="row">
		<div class="col-md-12 text-right adjust">
			
				<?php foreach ($buttons as $bname => $bstatus){ ?>
				<?php if($bname!='save&send'){ ?>
					<?php $bclass = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $bname));
					if($bname == 'save') $btn_name = 'Save';
					if($bname == 'request') $btn_name = 'Request';
					if($bname == 'request_cancel') $btn_name = 'Request Cancel'; ?>
					<form action="<?php echo $this->webroot.'SampleRegistrations/fun_'.$bclass; ?>" method="post" name="<?php echo 'btn_form_'.$bclass; ?>" id="<?php echo 'btn_form_'.$bclass; ?>">
						<input type="submit" name="<?php echo 'btn_'.$bclass;?>" id="<?php echo 'btn_'.$bclass;?>" class="btn-group btn-save pull-right ml-10"  value="<?php echo __($btn_name); ?>" data-action="<?php echo $bclass;?>" data-status="<?php echo $bstatus;?>" >
					</form>
				<?php } } ?>
				<button type="button" id="btn_update" class="btn-save pull-right ml-10" style="display: none;">
					<?php echo __("変更"); ?>
				</button>
		</div>
	</div><br><br>
	
	<?php if(!empty($data)): ?>
		
			<div class="row">
				<div class="col-lg-12">
					<div class="table-responsive">
						<table class="table table-striped table-bordered tbl_sumisho_inventory" id="sample_reg">
							<thead>
								<tr>
									<th width="50px"><?php echo __("SID"); ?></th>
									<th width="100px"><?php echo __("部署"); ?></th>
									<th width="100px"><?php echo __("部署名"); ?></th>
									<th width="100px"><?php echo __("対象月"); ?></th>
									<th width="100px"><?php echo __("カテゴリー"); ?></th>
									<th width="150px"><?php echo __("担当者"); ?></th>
									<th width="100px"><?php echo __("案件名"); ?></th>
									<th width="100px"><?php echo __("計上日"); ?></th>
									<th width="100px"><?php echo __("Index"); ?></th>
									<th width="100px"><?php echo __("勘定科目"); ?></th>
									<th width="100px"><?php echo __("相手先"); ?></th>
									<th width="100px"><?php echo __("相手先名"); ?></th>
									<th width="100px"><?php echo __("金額"); ?></th>
									<th width="250px"><?php echo __("要求書類"); ?></th>
									<th width="250px"><?php echo __("提出期限"); ?></th>
									<th width="250px"><?php echo __("備考"); ?></th>
									<th width="100px" >
										<?php echo __("経理添付資料"); ?>
									</th>
									<?php if((!empty($buttons['save']) || !empty($buttons['request']))): ?>
										<th colspan="2" width="100px"></th>
									<?php endif ?>
								</tr>
							</thead>
							<tbody>
								<?php $sid = 1; ?>
								<?php foreach($data as $table_data): // pr($table_data);
									$sample_flag = $table_data['flag'];
								?>
									<tr>
										<td>
											<?php echo $table_data['sid']; ?>
											<input type="hidden" name="tbl_sample_id" class="tbl_sample_id" value="<?php echo $table_data['id']; ?>" >
										</td>
										<td><?php echo $table_data['layer_code']; ?></td>
										<td><?php echo $table_data['layer_name']; ?></td>
										<td><?php echo $table_data['period']; ?></td>
										<td><?php echo $table_data['category']; ?></td>
										<td><?php echo $table_data['incharge_name']; ?></td>
										<td><?php echo $table_data['project_title']; ?></td>
										<td><?php echo $table_data['posting_date']; ?></td>
										<td><?php echo $table_data['index_no']; ?></td>
										<td><?php echo $table_data['account_item']; ?></td>
										<td><?php echo $table_data['destination_code']; ?></td>
										<td><?php echo $table_data['destination_name']; ?></td>
										<td><?php echo $table_data['money_amt']; ?></td>
										<td><?php echo $table_data['request_docu']; ?></td>
										<td><?php echo $table_data['submission_deadline_date']; ?></td>
										<td><?php echo $table_data['remark']; ?></td>

										<td width="100px" style="text-align:center">
											<form class="upload-form" name="upload-form" method="post" enctype="multipart/form-data" action="#">
												<input type="hidden" name="sample_data_id" id="sample_data_id" value="<?php echo $table_data['id'];?>">
												<input type="hidden" name="sid" class="sid" value="<?php echo $sid; ?>">
												<?php if((!empty($buttons['save']) || !empty($buttons['request']))): ?>
													<!-- <label id="btn_browse">Upload File
														<input type="file" class="upload_file" name="data[File][upload_file][]">
													</label> -->
													<label style="color: white;" id="btn_browse" class = "upload-div btn btn-sumisho">Upload File
														<input type="file" name="data[File][upload_file][]" class ="upload_file">
													</label>
												<?php endif ?>
											</form>
											<div class="show-list">
												<?php foreach($table_data['file'] as $file): ?>
													<div class="link-list">
														<form name="file-info-form" class="file-info-form">
															<input type="hidden" name="download_url" class="download_url" value="<?php  echo $file['url']; ?>">
															<input type="hidden" name="download_file" class="download_file" value="<?php echo $file['file_name']; ?>">
															<input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $file['attachment_id']; ?>">
															<a href="#" class="down-link" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file['file_name']; ?></a>
															<?php 
															//in_array($user_level, $user_id_list['Save & Send Email'])&&
															?>
															<?php if ((!empty($buttons['save']) || !empty($buttons['request']))&& $sample_flag < 4) { ?>
															<div class="btn-delete"><span class="glyphicon glyphicon-remove-sign"></span></div>
															<?php } ?>
														</form>
													</div>
												<?php endforeach ?>
											</div>
										</td>
										<?php if((!empty($buttons['save']) || !empty($buttons['request']))): ?>
											<td width="100px" class="td_center">
												<a href="#" class="edit"><span class="glyphicon glyphicon-edit"></span> <?php echo __("編集"); ?></a><br/>
												<a href="#" class="copy"><span class="glyphicon glyphicon-copy"></span> <?php echo __("コピー"); ?></a>
											</td>
											<td width="100px" class="td_center">
												<form name="del-form" class="del-form" method="post" action="<?php echo $this->webroot; ?>SampleRegistrations/deleteSampleData">
													<input type="hidden" name="del_sample_id" class="del_sample_id" value="<?php echo $table_data['id']; ?>" >
													<span class="glyphicon glyphicon-trash trash-color btn_row_delete"><input type="submit" name="btn_row_delete" value="<?php echo __("削除"); ?>"></span>
												</form>
											</td>
										<?php endif ?>
									</tr>
									
									<?php $sid++; ?>
								<?php endforeach ?>
							</tbody>		
						</table>
					</div>	
				</div>
			</div>
			<br/><br/>
	
			<!-- only show admin level 1, 2 and 3 -->
			<!-- <div class="row">
				<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
	
					<?php
					   // if (in_array($user_level, $user_id_list['Request'])) {
							?>
						<form method="post" action="<?php //echo $this->webroot; ?>SampleRegistrations/registerDataRequest" id="sample_request_form" name="sample_request_form">
						<div class="form-group row" style="margin-bottom: 70px;">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >
								 <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="text-align: right;">
									<input type="submit" id="btn_request" name="btn_request" class="btn btn-success btn_sumisho" value="<?php //echo __("依頼"); ?>" data-action="Request">
								 </div>
							</div>
						</div>
						</form>		
					<?php
						//} ?>
					
				</div> -->
				<!-- <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
	
					<?php
						//if (in_array($user_level, $user_id_list['Request']) && $sample_flag >= 2) {
							?>
	
						<form method="post" action="<?php //echo $this->webroot; ?>SampleRegistrations/registerDataCancel" id="sample_request_form_cancle" name="sample_request_form_cancle">
						<div class="form-group row" style="margin-bottom: 70px;">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >
								 <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="text-align: left;">
									<input type="submit" id="btn_request_cancel" name="btn_request_cancel" class="btn btn-success" value="<?php //echo __("依頼キャンセル"); ?>" >
								 </div>
							</div>
						</div>
						</form>
					<?php
						//} ?>				
				</div> -->
				
			</div>	
		</div>
	<?php else : ?>
		<div class="row">
			<div class="col-sm-12">
				<p class="no-data"><?php echo $no_data; ?></p>
			</div>
		</div>
	<?php endif ?>
</div>
<br/>
<script>
	$(document).ready(function() {

		var data_arr = [];
		var mailType, mailSend, mailSubject, mailBody, toLevelId, toMails, ccLevelId, ccMails, bccLevelId, bccMails, data_action;

		/* date picker setting */
		$('.datepicker').datepicker({
		    format: 'yyyy-mm-dd'
		});

		var tbl_row = $("#sample_reg tbody tr").length;
		if(tbl_row > 0) {
			/* show table if data is exists */
			$(".table_panel").show();
		}

		/* tooltip */
		$("[data-toggle='tooltip']").tooltip({
			trigger : 'hover'
		});

		/* request */
		$("#btn_request").click(function(e) {
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);
			e.preventDefault();
			$.confirm({
				title: "<?php echo __('依頼確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'green',
				typeAnimated: true,
				closeIcon: true,
				columnClass: 'medium',
				animateFromElement: true,
				animation: 'top',
				draggable: false,  
				content: "<?php echo __("データを依頼してよろしいですか。"); ?>",
				buttons: {
			        ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
			          	action:function(){
							reduceMailSetup(data_action);
			          	}
					},	  
					cancel: {
				       	text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
				       	action: function(){}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		});

		/* add line by line (max 6 rows) */
		$("#btn_save").click(function(e) {

			document.getElementById('error').innerHTML = '';
        	document.getElementById('success').innerHTML = '';
			e.preventDefault();
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);
			$isValid = checkValidation();
			var err_msg = $isValid[0];
			var validate = $isValid[1];
			var indexNo = $('#index_no').val();
			$('#hid_index_no').val(indexNo);
			if(validate == true) {
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
					content: "<?php echo __("データを保存してよろしいですか。"); ?>",
					buttons: {
						ok: {
							text: "<?php echo __('はい'); ?>",
							btnClass: 'btn-info',
				          	action:function(){
								reduceMailSetup(data_action);
				          	}
						},	  
						cancel: {
					       	text: "<?php echo __('いいえ'); ?>",
							btnClass: 'btn-default',
					       	action: function(){}
						}
					},
					theme: 'material',
					animation: 'rotateYR',
					closeAnimation: 'rotateXR'
				});
			} else {
				$("#error").empty();
				$("#success").empty();
				$("#error").append(err_msg);
				$("#error").show();
				$("html, body").animate({ scrollTop: 0 }, 'slow');
			}
 		});

 		/* save and email buttom no data Insert New save (add line by line (max 6 rows) */
		$("#btn_savemail").click(function(e) {
			document.getElementById('error').innerHTML = '';
        	document.getElementById('success').innerHTML = '';
			var data_valid ;
			var data_valid = "<?php echo count($data); ?>";				
			$("#data-action").val($(this).attr("data-action"));			
			data_action = $("#data-action").val();			
			e.preventDefault();
			$isValid = checkValidation();
			var err_msg = $isValid[0];
			var validate = $isValid[1];
			getMailSetup(data_action);		
			if(validate == true  && data_valid !='NUll') {
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
					content: "<?php echo __("データを保存してよろしいですか。"); ?>",
					buttons: {
						ok: {
							text: "<?php echo __('はい'); ?>",
							btnClass: 'btn-info',
				          	action:function(){
								if(mailType == 2){
									$("#myPOPModal").addClass("in");
									$("#myPOPModal").css({"display":"block","padding-right":"17px"});
									level_id = toLevelId;
									$(".subject").text(mailSubject);
									$(".body").text(mailBody);
									$('#sample_reg_form').attr('method','post');
									$('#sample_reg_form').attr('action', "<?php echo $this->Html->url(array('controller'=>'SampleRegistrations','action'=>'saveAndEmailSampleData')) ?>");
								}else{
									$('<input>').attr({
										type: 'hidden',
										id: 'toEmail',
										name: 'toEmail'
									}).appendTo('#sample_reg_form');
									$('#toEmail').val(toMails);
									$('#sample_reg_form').submit();	
									loadingPic(); 
								}	
					          
				          	}
						},	  
						cancel: {
					       	text: "<?php echo __('いいえ'); ?>",
							btnClass: 'btn-default',
					       	action: function(){}
						}
					},
					theme: 'material',
					animation: 'rotateYR',
					closeAnimation: 'rotateXR'
				});
			}else if(validate == false && data_valid > 0)
			{  
				saveandemailUpdateUser();

			}
			else {
				$("#error").empty();
				$("#success").empty();
				$("#error").append(err_msg);
				$("#error").show();
				$("html, body").animate({ scrollTop: 0 }, 'slow');
			}
 		});
 		/* save and email end*/

 		/* save and email buttom with  have data use for user updated id (add line by line (max 6 rows) */
 		function saveandemailUpdateUser(){
			
			document.getElementById('error').innerHTML = '';
        	document.getElementById('success').innerHTML = '';
			var validate = true;
			if(validate == true) {
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
					content: "<?php echo __("データを依頼してよろしいですか。"); ?>",
					buttons: {
						ok: {
							text: "<?php echo __('はい'); ?>",
							btnClass: 'btn-info',
				          	action:function(){
								if(mailType == 2){			          		
									$("#myPOPModal").addClass("in");
									$("#myPOPModal").css({"display":"block","padding-right":"17px"});
									level_id = toLevelId;
									$(".subject").text(mailSubject);
									$(".body").text(mailBody);
									$('#sample_reg_form').attr('method','post');
									$('#sample_reg_form').attr('action', "<?php echo $this->Html->url(array('controller'=>'SampleRegistrations','action'=>'saveAndEmailUpdateID')) ?>");
								}else{
									$('<input>').attr({
										type: 'hidden',
										id: 'toEmail',
										name: 'toEmail'
									}).appendTo('#sample_reg_form');
									$('#toEmail').val(toMails);
									$('#sample_reg_form').submit();
									loadingPic(); 

								}
				          	}
						},	  
						cancel: {
					       	text: "<?php echo __('いいえ'); ?>",
							btnClass: 'btn-default',
					       	action: function(){}
						}
					},
					theme: 'material',
					animation: 'rotateYR',
					closeAnimation: 'rotateXR'
				});
			} else {
				$("#error").empty();
				$("#success").empty();
				$("#error").append(err_msg);
				$("#error").show();
				$("html, body").animate({ scrollTop: 0 }, 'slow');
			}
 		};
 		/* save and email end*/		

 		/* Request cancle*/

 		/* request */
		$("#btn_requestcancel").click(function(e) {
			$("#data-action").val($(this).attr("data-action"));			
			data_action = $("#data-action").val();
			getMailSetup(data_action);
			e.preventDefault();
			$.confirm({
				title: "<?php echo __('リクエストのキャンセル確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'green',
				typeAnimated: true,
				closeIcon: true,
				columnClass: 'medium',
				animateFromElement: true,
				animation: 'top',
				draggable: false,  
				content: "<?php echo __("リクエストデータをキャンセルしてもよろしいですか。"); ?>",
				buttons: {
			        ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
			          	action:function(){
							reduceMailSetup(data_action);
			          	}
					},	  
					cancel: {
				       	text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
				       	action: function(){}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		});


 		function checkValidation() {
 			var err_msg = '';
			var validate = true;
			var layer_code = $("#layer_code").val();
			var incharge = $.trim($("#incharge_name").val());
			var proj_title = $.trim($("#project_title").val());
			var index = $.trim($("#index_no").val());
			var post_date = $.trim($("#posting_date").val());
			var acc_item = $.trim($("#account_item").val());
			var dest_code = $.trim($("#destination_code").val());
			var dest_name = $.trim($("#destination_name").val());
			var amount = $.trim($("#money_amt").val());
			var request_doc = $.trim($("#request_docu").val());
			var submission_deadline_date = $.trim($("#submission_deadline_date").val());
			var remark = $.trim($("#remark").val());
			
			/* check validation */
			if(!checkNullOrBlank(layer_code)){
				err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("Business Area");?>'])+"<br/>";
				validate = false;
			}
			if(!checkNullOrBlank(incharge)){
				err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("担当者");?>'])+"<br/>";
				validate = false;
			}
			if(!checkNullOrBlank(proj_title)){
				err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("案件名");?>'])+"<br/>";
				validate = false;
			}
			if(!checkNullOrBlank(post_date)){
				err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("計上日");?>'])+"<br/>";
				validate = false;
			}
			if(!checkNullOrBlank(index)){
				err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("Index");?>'])+"<br/>";
				validate = false;
			}
			if(!checkNullOrBlank(acc_item)){
				err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("勘定科目");?>'])+"<br/>";
				validate = false;
			}
			if(!checkNullOrBlank(dest_code)){
				err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("相手先");?>'])+"<br/>";
				validate = false;
			}
			if(!checkNullOrBlank(dest_name)){
				err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("相手先名");?>'])+"<br/>";
				validate = false;
			}
			if(!checkNullOrBlank(amount)){
				err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("金額");?>'])+"<br/>";
				validate = false;
			} else if(!isDecimalPosNeg(amount)) {
				err_msg += errMsg(commonMsg.JSE014,['<?php echo __("金額");?>'])+"<br/>";
				validate = false;
			}
			if(!checkNullOrBlank(request_doc)){
				err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("要求書類");?>'])+"<br/>";
				validate = false;
			}
			if(!checkNullOrBlank(submission_deadline_date)){
				err_msg += 	errMsg(commonMsg.JSE001,['<?php echo __("提出期限");?>'])+"<br/>";
				validate = false;
			}
			return [err_msg, validate];
 		}

		/* get mail content by function */
		function getMailSetup(data_action){
			let page = "<?php echo $page;?>";
			let period = "<?php echo $period;?>";
			$.ajax({
				url: "<?php echo $this->webroot; ?>Common/getMailContent",
				type: "POST",
				data: {data_action : data_action,page : page,selection_name:'SampleSelections'},
				dataType: "json",
				success: function(data) {
					if(data.mailSend == 1){
						mailType = data.mailType;
						mailSend = data.mailSend;
						mailSubject = data.subject;
						mailBody = data.body;
						toLevelId = Object.keys(data.to);
						ccLevelId = Object.keys(data.cc);
						bccLevelId = Object.keys(data.bcc);
						toMails = Object.values(data.to);
						ccMails = Object.values(data.cc);
						bccMails = Object.values(data.bcc);						
					}else{
						mailSend = data.mailSend;
					}
				},
			});
		}

		/* reduce mail set up */
	function reduceMailSetup(data_action){
		submitForm = "<?php echo '#btn_form_'; ?>"+data_action;
		$("<input>").attr({type: "hidden", name: "incharge_name", value: $("#incharge_name").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", name: "project_title", value: $("#project_title").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", name: "posting_date", value: $("#posting_date").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", name: "index_no", value: $("#index_no").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", name: "hid_index_no", value: $("#hid_index_no").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", name: "account_item", value: $("#account_item").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", name: "destination_code", value: $("#destination_code").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", name: "destination_name", value: $("#destination_name").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", name: "money_amt", value: $("#money_amt").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", name: "request_docu", value: $("#request_docu").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", name: "submission_deadline_date", value: $("#submission_deadline_date").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", name: "remark", value: $("#remark").val()}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "toEmail", name: "toEmail"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "ccEmail", name: "ccEmail"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "bccEmail", name: "bccEmail"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "mailSubj", name: "mailSubj"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "mailBody", name: "mailBody"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "mailSend", name: "mailSend", value: mailSend}).appendTo(submitForm);
		if(mailSend == 1){	
			/* set mail content */
			$("#mailSubj").val(mailSubject);
			$("#mailBody").val(mailBody);
			/* if pop up */
			if(mailType == 2){
				$("#myPOPModal").addClass("in");
				$("#myPOPModal").css({"display":"block","padding-right":"17px"});
				/*assign value into global variable */
				level_id = toLevelId;
				cc_level_id = ccLevelId;
				bcc_level_id = bccLevelId;
	
				/* set mail content to display */
				$(".subject").text(mailSubject);
				$(".body").text(mailBody);
				/* set mail box to show or hide */
				if(toLevelId != "" && toLevelId != undefined){
					$(".autoCplTo").show();
				}
				if(bccLevelId != "" && bccLevelId != undefined){
					$(".autoCplBcc").show();
				}
				if(ccLevelId != "" && ccLevelId != undefined){
					$(".autoCplCc").show();
				}
	
				/* set form action */	
				$(submitForm).attr("method","post");
				$(submitForm).attr("action", "<?php echo $this->webroot?>SampleRegistrations/fun_"+data_action);
			}else{
				/* set mails if not pop up */
				$("#toEmail").val(toMails);
				$("#ccEmail").val(ccMails);
				$("#bccEmail").val(bccMails);
				$("<?php echo '#btn_form_'; ?>"+data_action).submit();	
				loadingPic(); 
			}
		}else{
			$("<?php echo '#btn_form_'; ?>"+data_action).submit();	
			loadingPic(); 
		}
	}

 		/* copy data */
		$(document).on('click', '.copy', function() {
			var row = $(this).closest('tr');
			var get_id = $(this).closest('tr').find('.tbl_sample_id').val();

			$.ajax({
				url: '<?php echo $this->webroot; ?>SampleRegistrations/copyingData',
				type: 'post',
				data: {row_id:get_id},
				beforeSend: function() {
					loadingPic(); 
				},
				success: function(data) {
				
					window.location.reload();
				},
				error: function(err) {
					console.log(err);
				}
			});
			
			/* clear error and success */
			$("#error, #success").empty();
		});

		/* edit data */
		$(document).on('click', '.edit', function() {
			var row = $(this).closest('tr');
			var layer_code = $.trim(row.find('td:eq(1)').text());
			var category = $.trim(row.find('td:eq(4)').text());
			var incharge = $.trim(row.find('td:eq(5)').text());
			var proj_title = $.trim(row.find('td:eq(6)').text());
			var post_date = $.trim(row.find('td:eq(7)').text());
			var index = $.trim(row.find('td:eq(8)').text());
			var acc_item = $.trim(row.find('td:eq(9)').text());
			var dest_code = $.trim(row.find('td:eq(10)').text());
			var dest_name = $.trim(row.find('td:eq(11)').text());
			var amount = $.trim(row.find('td:eq(12)').text());
			amount = amount.split(',').join('');
			var request_doc = $.trim(row.find('td:eq(13)').text());
			var submission_deadline_date = $.trim(row.find('td:eq(14)').text());
			var remark = $.trim(row.find('td:eq(15)').text());
			var get_id = $(this).closest('tr').find('.tbl_sample_id').val();
			$("#index_no").val($.trim(row.find('td:eq(8)').text()))
			$("#hid_index_no").val($.trim(row.find('td:eq(8)').text()))
			var search_data = {
				searchValue : index,
				layer_code : layer_code,
			};
			$.ajax({
				url: "<?php echo $this->Html->url(array('controller'=>'SampleRegistrations','action'=>'getLogistics')) ?>",
				dataType: "json",
				type: "post",
				data: search_data,
				success: function (data) {
					if (data != "") {
						$("#index_no").val(data[0]);
					}
				},
				error: function () {
					
					$("#errorMail").text('No match found!');
					$(".ui-autocomplete").empty();
				}
			});	
			$("#edit_sample_id").val(get_id);
			$("#category").val(category);
			$("#incharge_name").val(incharge);
			$("#project_title").val(proj_title);
			$("#posting_date").val(post_date);
			$("#account_item").val(acc_item);
			$("#destination_code").val(dest_code);
			$("#destination_name").val(dest_name);
			$("#money_amt").val(amount);
			$("#request_docu").val(request_doc);
			$("#submission_deadline_date").val(submission_deadline_date);
			$("#remark").val(remark);
			/* hide add button and show update button */
			$("#btn_save").hide();
			// $("#btn_savemail").hide();
			$("#btn_update").show();

			/* clear error and success */
			$("#error, #success").empty();
			$("#error").hide();
		});

		/* update */ 
		$("#btn_update").click(function(e) {
			document.getElementById('error').innerHTML = '';
        	document.getElementById('success').innerHTML = '';
			e.preventDefault();
			$isValid = checkValidation();
			var err_msg = $isValid[0];
			var validate = $isValid[1];
			if(validate == true) {
				$.confirm({
					title: "<?php echo __('変更確認'); ?>",
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
				        confirm: {
							text: "<?php echo __('はい'); ?>",
							btnClass: 'btn-info',
				          	action:function(){
				          		$('#sample_reg_form').attr('action', '<?php echo $this->webroot; ?>SampleRegistrations/updateSampleData');
								$('#sample_reg_form').attr('method', 'post');
								$('#sample_reg_form').submit();
								// loadingPic(); 
				          	}
						},	  
						cancel: {
					       	text: "<?php echo __('いいえ'); ?>",
							btnClass: 'btn-default',
					       	action: function(){}
						}
					},
					theme: 'material',
					animation: 'rotateYR',
					closeAnimation: 'rotateXR'
				});
			} else {
				$("#error").empty();
				$("#success").empty();
				$("#error").append(err_msg);
				$("#error").show();
				$("html, body").animate({ scrollTop: 0 }, 'slow');
			}
		});

		/* row delete */
		$(document).on('click', '.btn_row_delete', function(e) {
			document.getElementById('error').innerHTML = '';
        	document.getElementById('success').innerHTML = '';
			e.preventDefault();
			var clicked = $(this).closest('.del-form');
			$.confirm({
				title: "<?php echo __('削除確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'red',
				typeAnimated: true,
				closeIcon: true,
				columnClass: 'medium',
				animateFromElement: true,
				animation: 'top',
				draggable: false,  
				content: "<?php echo __("データを削除してよろしいですか。"); ?>",
				buttons: {
			        ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
			          	action:function(){
			          		clicked.submit();
							loadingPic(); 
			          	}
					},	  
					cancel: {
				       	text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
				       	action: function(){}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		});

 		/* file upload */
 		$(".upload_file").change(function() {
			var found = '';
			var warn = '';
			var list = [];
			var row = $(this).closest('tr');
			var formdata = new FormData($(this).parent('label').parent('.upload-form')[0]);
			var fileToUpload = $(this).prop('files')[0].name;
			
			row.find(".link-list").each(function(i, val){
				var name = $.trim($(this).find('a.down-link').text());
				list.push(name);
			});
			var list_len = list.length;
			for(var i=0; i<list_len; i++) {
				var text = list[i];
				if(text == fileToUpload) {
					found = text;
				}
			}
			if(found != '') {
				$.confirm({
					title: "<?php echo __('アップロード確認'); ?>",
					icon: 'fas fa-exclamation-circle',
					type: 'green',
					typeAnimated: true,
					closeIcon: true,
					columnClass: 'medium',
					animateFromElement: true,
					animation: 'top',
					draggable: false,  
					content: errMsg(commonMsg.JSE005),
					buttons: {
				        ok: {
							text: "<?php echo __('はい'); ?>",
							btnClass: 'btn-info',
				          	action:function(){
				          		uploadFileAjax(formdata, row, 'update');
				          	}
						},	  
						cancel: {
					       	text: "<?php echo __('いいえ'); ?>",
							btnClass: 'btn-default',
					       	action: function(){}
						}
					},
					theme: 'material',
					animation: 'rotateYR',
					closeAnimation: 'rotateXR'
				});
				
			} else {
				warn = true;
			}
			
			if(warn) {
				$.confirm({
					title: "<?php echo __('アップロード確認'); ?>",
					icon: 'fas fa-exclamation-circle',
					type: 'green',
					typeAnimated: true,
					closeIcon: true,
					columnClass: 'medium',
					animateFromElement: true,
					animation: 'top',
					draggable: false,  
					content: "<?php echo __("ファイルをアップロードしてよろしいでしょうか。") ?>",
					buttons: {
				        ok: {
							text: "<?php echo __('はい'); ?>",
							btnClass: 'btn-info',
				          	action:function(){
								uploadFileAjax(formdata, row, 'save');
				          	}
						},	  
						cancel: {
					       	text: "<?php echo __('いいえ'); ?>",
							btnClass: 'btn-default',
					       	action: function(){}
						}
					},
					theme: 'material',
					animation: 'rotateYR',
					closeAnimation: 'rotateXR'
				});
			}
			$(this).val('');
		});

		function uploadFileAjax(formdata, row, action) {
			document.getElementById('error').innerHTML = '';
        	document.getElementById('success').innerHTML = '';
			var ajax_error = errMsg(commonMsg.JSE027);//if user is logout
			formdata.append('action',action);//to decide save or update
			$.ajax({
				type: "post",
				url: "<?php echo $this->webroot; ?>SampleRegistrations/uploadAccountFile",
				processData: false,
				contentType: false,
				data: formdata,
				dataType: 'json',
				beforeSend: function() {
					loadingPic(); 
				},
				success: function(data) {
		
					$(".upload_file").empty();
					if(data['error'] != undefined && data['error'] != '') {
						var err = data['error'];
						$("#error").html(err);
						$("#error").show();
						$("#success").hide();
						$("html, body").animate({ scrollTop: 0 }, 'slow');
					}
					if(data['file_name'] != undefined && data['file_name'] != '') {
						window.location.reload();
					}
					$("#overlay").hide();
				},
				error: function(xhr, status, error) { 
					alert("Can't upload file")
					window.location.reload();
				}
			});
		}

		/* download file */
		$('.down-link').click( function(e) {
			e.preventDefault();
			$(this).closest('.file-info-form').attr('action', '<?php echo $this->webroot; ?>SampleRegistrations/download_object_from_cloud');
			$(this).closest('.file-info-form').attr('method', 'post');
			$(this).closest('.file-info-form').submit();
		});

		$('.btn-delete').click(function(e) {
			document.getElementById('error').innerHTML = '';
        	document.getElementById('success').innerHTML = '';
			e.preventDefault();
			var del_clicked = $(this).closest('.file-info-form');
			$.confirm({
				title: "<?php echo __('削除確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'red',
				typeAnimated: true,
				closeIcon: true,
				columnClass: 'medium',
				animateFromElement: true,
				animation: 'top',
				draggable: false,  
				content: "<?php echo __("データを削除してよろしいですか。") ?>",
				buttons: {
			        ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
			          	action:function(){
							del_clicked.attr('action', '<?php echo $this->webroot; ?>SampleRegistrations/delete_object_from_cloud');
							del_clicked.attr('method', 'post');
							del_clicked.submit();
							loadingPic(); 
			          	}
					},	  
					cancel: {
				       	text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
				       	action: function(){}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		});

	});
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
		function extractLast( indexNo ) {
			return split( indexNo ).pop();
		}
		$("#index_no").autocomplete({
			
			source: function (request, response) {
				var indexNo = $('#index_no').val();
				var layer_code = '<?php echo $layer_code; ?>';
				var search_val = extractLast( indexNo );
				if (search_val != "") {
					var search_data = {
						searchValue : search_val,
						layer_code : layer_code,
					};
					
					$.ajax({
						url: "<?php echo $this->Html->url(array('controller'=>'SampleRegistrations','action'=>'getLogistics')) ?>",
						dataType: "json",
						type: "post",
						data: search_data,
						success: function (data) {
							if (data != "") {
								response(data);
								$("#errorMail").text('');
							}else{
								$('#hid_index_no').val(indexNo);
							}
						},
						error: function () {
							$("#errorMail").text('No match found!');
							$(".ui-autocomplete").empty();
						}
					});	 
				}
			},
			multiselect: false,
			autoFocus: true,
			select: function( event, ui ) {
				event.preventDefault();
				var selValue = ui.item.value;
				this.value = selValue
				var splitVal = selValue.split("/");
				$('#hid_index_no').val(splitVal[0]);
				return false;
			},
			focus: function (event, ui) {
				var indexNo = $('#index_no').val();
				$('#hid_index_no').val(indexNo);
				event.preventDefault();
				return false;
			}
		});
</script>


