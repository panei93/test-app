<style>
	body {
		font-family: KozGoPro-Regular;
	}
	#data_entry {
		display: none;
		min-width: 1900px;
	}
	.align-left {
		text-align: left !important;
	}
	.align-right {
		text-align: right !important;
	}
	.align-center {
		text-align: center !important;
	}
	.btn_approve_style {
		width: 150px;
		margin: 5px;
	}
	.btn_style {
		width: 150px;
		margin: 5px;
	}
	.acc-link-list, .sale-link-list  {
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
	a.acc-down-link, a.sale-down-link {
		display: inline-block;
		width: 100px;
		margin-right: 40px;
		margin-left: 40px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		text-align: center;
		color: #19b5fe;
		text-decoration: underline;
	}
	.jconfirm-box-container {
      margin-left: unset !important;
   }
</style>
<?php	
	echo $this->element("autocomplete", array(
						"to_level_id" => "",
						"cc_level_id" =>"",
						"submit_form_name" => "",
						"MailSubject" => "",
						"MailTitle" => "",
						"MailBody" => ""));

 ?>

<div id="overlay">
	<span class="loader"></span>
</div>
<h3><?php echo __('部署によるデータ入力');?></h3>
<hr>
<div class="row">
	<div class="col-sm-12">
		<div class="errorSuccess">
	        <div class="success" id="success"><?php echo ($this->Session->check("Message.EntryOK"))? $this->Flash->render("EntryOK") : ''; ?></div>
	        <div class="error" id="error"><?php echo ($this->Session->check("Message.EntryFail"))? $this->Flash->render("EntryFail") : ''; ?></div>
		</div>
	</div>
</div>
<input type="hidden" name="data-action" id="data-action"/>
<form class="form-inline">
	<div class="row register_form">
		<div class="form-row col-sm-12 col-md-6 form-group">
			<label class="control-label"><?php echo __("対象月"); ?></label>
			<input type="text" class="form-control form_input" value="<?php echo $this->Session->read('SAMPLECHECK_PERIOD_DATE'); ?>" readonly>
		</div>
		<div class="form-row col-sm-12 col-md-6 form-group">
			<label class="control-label"><?php echo __("部署"); ?></label>
			<input type="text" class="form-control form_input" value="<?php echo $this->Session->read('SESSION_LAYER_CODE'); ?>" readonly>
		</div>
		<div class="form-row col-sm-12 col-md-6 form-group">
			<label class="control-label"><?php echo __("部署名"); ?></label>
			<input type="text" class="form-control form_input" value="<?php echo $this->Session->read('SAMPLECHECK_BA_NAME'); ?>" readonly>
		</div>
		<div class="form-row col-sm-12 col-md-6 form-group">
			<label class="control-label"><?php echo __("カテゴリー"); ?></label>
			<input type="text" class="form-control form_input" value="<?php echo $this->Session->read('SAMPLECHECK_CATEGORY'); ?>" readonly>
		</div>
	</div>
</form>
<div class="row">
	<div class="col-md-12">
		<?php 
		$i = 0;
		foreach ($buttons as $bname => $bstatus): ?>
			<?php $bclass = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $bname)); 
			if($bname == 'reject') $btn_name = 'Revert';
			if($bname == 'request') $btn_name = 'Request';
			if($bname == 'approve') $btn_name = 'Approve';
			if($bname == 'approve_cancel') $btn_name = 'Approve Cancel'; ?>
			<form action="<?php echo $this->webroot.'SampleDataEntry/fun_'.$bclass; ?>" method="post" name="<?php echo 'btn_form_'.$bclass; ?>" id="<?php echo 'btn_form_'.$bclass; ?>">
				<input type="hidden" name="request_data" class="request_data" value="" />				
				<input type="submit" name="<?php echo 'btn_'.$bclass;?>" id="<?php echo 'btn_'.$bclass;?>" class="btn-group btn-save pull-right <?php echo ($i == 0) ? '' : 'mr-10'; ?> mb-10" value="<?php echo __($btn_name); ?>" data-action="<?php echo $bclass;?>" data-status="<?php echo $bstatus;?>" >
			</form>
		<?php $i++;endforeach ?>	
	</div>
</div>

<?php 
	# disable checkbox except user level 6[Sales SubMgr] and 1[Admin]
	if(!empty($buttons)) {
		$chk_disable = '';
	} else {
		$chk_disable = 'disabled="disabled"';
	}
?>
<div class="row">
	<div class="col-sm-12">
		<div class="table-responsive">
			<table class="table table-striped table-bordered tbl_sumisho_inventory" id="data_entry">
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
						<th width="250px"><?php echo __("備考"); ?></th>
						<th width="100px" >
							<?php echo __("経理添付資料"); ?>
						</th>
						<th width="150px">
							<?php echo __("営業添付資料"); ?>
						</th>
						<!-- <?php if(($user_level == 1 && $sample_flag < 4) || ($user_level == 7 && $sample_flag < 4)) { ?>
						<?php } ?> -->
						<!-- Added by PanEiPhyo (20200305) -->
						<th width="40px">
							<input type="checkbox" id="chk_master" name="chk_master" <?php echo $chk_disable; ?>/>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$sample_flag = '';//to show/hide request button
						if(isset($data) && count($data) > 0) {
							$cnt = count($data);
							for($i=0; $i<$cnt; $i++) {
								$sid = $i+1;
								$id = h($data[$i]['id']);
								$layer_code = h($data[$i]['layer_code']);
								$category = h($data[$i]['category']);
								$incharge = h($data[$i]['incharge_name']);
								$proj_title = h($data[$i]['project_title']);
								$posting_date = h($data[$i]['posting_date']);
								$index = h($data[$i]['index_no']);
								$acc_item = h($data[$i]['account_item']);
								$dest_code = h($data[$i]['destination_code']);
								$dest_name = h($data[$i]['destination_name']);
								$amount = h($data[$i]['money_amt']);
								$amount = str_replace('.00', '', number_format($amount, 2, '.', ','));
								$request_doc = h($data[$i]['request_docu']);
								$remark = h($data[$i]['remark']);
								$sample_flag = h($data[$i]['flag']);
								$acc_file = $data[$i]['acc_file'];
								$sale_file = $data[$i]['sale_file'];
								if($sample_flag >= 3) {
									$chk_status = 'disabled="disabled" checked="checked"';
								} else {
									$chk_status = '';
								}
					?>
								<tr>
									<td width="50px" class="align-right" id="s_id">
										<?php echo $sid; ?>
									</td>
									<td width="100px">
										<?php echo $layer_code; ?>
									</td>
									<td width="100px">
										<?php echo $this->Session->read('SAMPLECHECK_BA_NAME'); ?>
									</td>
									<td width="100px" class="align-left">
										<?php echo $this->Session->read('SAMPLECHECK_PERIOD_DATE'); ?>
									</td>
									<td width="100px">
										<?php echo $category; ?>
									</td>
									<td width="150px">
										<?php echo $incharge; ?>
									</td>
									<td width="100px">
										<?php echo $proj_title; ?>
									</td>
									<td width="100px">
										<?php echo $posting_date; ?>
									</td>
									<td width="100px" id="index_no">
										<?php echo $index; ?>
									</td>
									<td width="100px">
										<?php echo $acc_item; ?>
									</td>
									<td width="100px">
										<?php echo $dest_code; ?>
									</td>
									<td width="100px">
										<?php echo $dest_name; ?>
									</td>
									<td width="100px" class="align-right">
										<?php echo $amount; ?>
									</td>
									<td width="250px">
										<?php echo nl2br($request_doc); ?>
									</td>
									<td width="250px">
										<?php echo nl2br($remark); ?>
									</td>
									<td width="100px">
										<!-- account file -->
										<div class="acc-show-list">
										<?php 
											$acc_count = count($acc_file);
											for($f=0; $f<$acc_count; $f++) {
												$attachment_id = h($acc_file[$f]['attachment_id']);
												$file_name = h($acc_file[$f]['file_name']);
												$url = h($acc_file[$f]['url']);
										?>
												<div class="acc-link-list" >
													<form name="acc-file-info-form" class="acc-file-info-form">
														<input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
														<input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
														<input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
														<a href="#" class="acc-down-link" data-toggle="tooltip" title="<?php echo $file_name; ?>" ><?php echo $file_name; ?></a>
													</form>
												</div>
										<?php } ?>
										</div>
										<!-- end of account file -->
									</td>
									<td width="150px" >
										<!-- sale file -->
										<?php
										if($chk_status != '') $disClass = 'disabled';
										else $disClass = '';
										if(!empty($buttons)) { 
											
										?>
										<form class="sale-upload-form" name="sale-upload-form" method="post" enctype="multipart/form-data" action="#">
											<input type="hidden" name="sample_data_id" id="sample_data_id" value="<?php echo $id;?>">
											<input type="hidden" name="sid" class="sid" value="<?php echo $sid; ?>">
											<label id="btn_browse" style="color: white;" class = "upload-div btn btn-sumisho <?php echo $disClass; ?>">Upload File
												<input type="file" class="upload_file" name="data[File][upload_file][]" <?php echo $disClass; ?>>
											</label>
										</form>
										<?php } ?>
										<div class="sale-show-list">
										<?php 
											$sale_count = count($sale_file);
											for($f=0; $f<$sale_count; $f++) {
												$attachment_id = h($sale_file[$f]['attachment_id']);
												$file_name = h($sale_file[$f]['file_name']);
												$url = h($sale_file[$f]['url']);
										?>
												<div class="sale-link-list">
													<form name="sale-file-info-form" class="sale-file-info-form">
														<input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
														<input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
														<input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
														<a href="#" class="sale-down-link" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file_name; ?></a>
														<?php //if(($user_level == 1 && $sample_flag < 4) || ($user_level == 7 && $sample_flag < 4) ) { ?>
														<?php if ((!empty($buttons['save']) || !empty($buttons['request']))&& $sample_flag < 4) { 
														if($disClass == ''){?>
														<div class="btn-delete"><span class="glyphicon glyphicon-remove-sign"></span></div>
														<?php }
														} ?>
													</form>
												</div>
										<?php } ?>
										</div>
										<!-- end of sale file -->
									</td>
									<!-- Added checkbox col by PanEiPhyo(20200305) -->
									<!-- <?php //if(($user_level == 1 && $sample_flag < 4) || ($user_level == 7 && $sample_flag < 4)) { ?> 
									<!-- <?php if((!empty($buttons['save']) || !empty($buttons['request']))&& $sample_flag < 4) { ?> 
									<?php } ?>								 -->
									<!-- Added checkbox col by PanEiPhyo(20200305) -->
									<td class="align-center">
										<input type="checkbox" class="chk_data" name="chk_data" <?php echo $chk_disable; echo $chk_status; ?> value="<?php echo $id; ?>"/>
									</td>
								</tr>
					<?php
							}
						}
					?>
				</tbody>		
			</table>
		</div>	
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<p class="no-data"><?php echo $no_data; ?></p>
	</div>
</div>
<div class="row">

	<div class="col-sm-12 align-center">
		<?php
			// if($user_level == 1 || $user_level == 5 || $user_level == 8) {
				// if($sample_flag == 3) {
				// if($showBtnApprove == true) {
		?>			<!-- <div class="col-sm-6" align="right">
						<form method="post" action="<?php echo $this->webroot; ?>SampleDataEntry/dataEntryApprove" name="approve-form" id="approve-form">
							<input type="submit" name="btn_approve" id="btn_approve" class="btn btn-success btn_approve_style btn_sumisho" value="<?php echo __("承認"); ?>">
						</form>
					</div> -->
					<!-- <div class="col-sm-6" align="left">
						<form method="post" action="<?php echo $this->webroot; ?>SampleDataEntry/dataEntryReject" name="reject_form" id="reject_form"> -->
							<!-- <input type="hidden" name="toEmail" id="toEmail" value=""> -->
							<!-- <input type="hidden" name="ccEmail" id="ccEmail">
							<input type="hidden" name="mailSubj" id="mailSubj">
							<input type="hidden" name="mailTitle" id="mailTitle">
							<input type="hidden" name="mailBody" id="mailBody"> -->
							<!--<input type="submit" name="btn_reject" id="btn_reject" class="btn btn-success btn_approve_style btn_sumisho" value="<?php echo __("差し戻し"); ?>">
						</form>
					</div> -->
				
		<?php
				// }
				// if($sample_flag == 4) {
				// if($showBtnApproveCancel == true) {
		?>
					<!-- <form method="post" action="<?php echo $this->webroot; ?>SampleDataEntry/dataEntryApproveCancel" name="approve-cancel-form" id="approve-cancel-form">
						<input type="hidden" name="toEmail" id="toEmail" value=""> -->
						<!-- <input type="hidden" name="ccEmail" id="ccEmail">
						<input type="hidden" name="mailSubj" id="mailSubj">
						<input type="hidden" name="mailTitle" id="mailTitle">
						<input type="hidden" name="mailBody" id="mailBody"> -->
						<!-- <input type="submit" name="btn_approve_cancel" id="btn_approve_cancel" class="btn btn-success btn_approve_style btn_sumisho" value="<?php echo __("承認キャンセル"); ?>">
					</form> -->
		<?php
			// 	}
			// }
		?>
	</div>
</div>
<br/><br/><br/>
<script>
	var mailType, mailSend, mailSubject, mailBody, toLevelId, toMails, ccLevelId, ccMails, bccLevelId, bccMails, data_action;
	$(document).ready(function() {


		/* check master checkbox when page loading */
		function toggleCheck() {
			var chk_data = true;
			var is_disable = true;
			$("#data_entry tbody tr").each(function() {
				var each_chk = $(this).find('.chk_data');
				if(each_chk.is(":checked") == false) {
					chk_data = false;
				}
				if(each_chk.is(":disabled") == false) {
					is_disable = false;
				}
			});
			// check/uncheck
			if(chk_data) {
				$("#chk_master").prop('checked',true);
			} else {
				$("#chk_master").prop('checked',false);
			}
			// enable/disabled
			if(is_disable) {
				$("#chk_master").prop('disabled',true);
			} else {
				$("#chk_master").prop('disabled',false);
			}
		}
		toggleCheck();
		/* check all rows */
		$("#chk_master").click(function() {
			var chk_master = $(this);
			$("#data_entry tbody tr").each(function() {
				var row = $(this).find(".chk_data");
				if(row.is(":disabled") == false) {
					if(chk_master.is(":checked")) {
						row.prop('checked', true);
					} else {
						row.prop('checked', false);
					}
				}
			});			
		});
		/* check/uncheck to master checkbox */
		$(".chk_data").click(function() {
			if($(this).is(":checked") == false) {
				$("#chk_master").prop('checked', false);
			} else {
				toggleCheck();
			}
		});

		//Added by PanEiPhyo (20200305),to show checkbox for level 4
		$("#chk_l7_master").click(function() {
			var chk_master = $(this);
			$("#data_entry tbody tr").each(function() {
				var row = $(this).find(".chk_l7_data");
				if(row.is(":disabled") == false) {
					if(chk_master.is(":checked")) {
						row.prop('checked', true);
					} else {
						row.prop('checked', false);
					}
				}
			});			
		});
		$(".chk_l7_data").click(function() {
			if($(this).is(":checked") == false) {
				$("#chk_l7_master").prop('checked', false);
			} else {
				toggleCheck();
			}
		});

		/* hide table if nothing to show */
		var table = $("#data_entry tbody tr").length;
		if(table > 0) {
			$("#data_entry").show();
		}

		/* tooltip */
		$("[data-toggle='tooltip']").tooltip({
			trigger : 'hover'
		});

		/* file upload */
 		$(document).on('change', '.upload_file', function() {
			var found = '';
			var warn = '';
			var list = [];
			var row = $(this).closest('tr');
			var formdata = new FormData($(this).parent('label').parent(".sale-upload-form")[0]);
			var fileToUpload = $(this).prop('files')[0].name;

			row.find(".sale-link-list").each(function(i, val){
				var name = $.trim($(this).find('a.sale-down-link').text());
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
			formdata.append('action',action);//to decide save or update
			$.ajax({
				type: "post",
				url: "<?php echo $this->webroot; ?>SampleDataEntry/uploadSalesFile",
				processData: false,
				contentType: false,
				data: formdata,
				beforeSend: function() {
					loadingPic(); 
				},
				success: function(data) {
					$(".upload_file").empty();
					var data = JSON.parse(data);
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
				}
			});
		}

		/* download account attachment file */
		$('.acc-down-link').click( function(e) {
			e.preventDefault();
			$(this).closest('.acc-file-info-form').attr('action', '<?php echo $this->webroot; ?>SampleDataEntry/download_object_from_cloud');
			$(this).closest('.acc-file-info-form').attr('method', 'post');
			$(this).closest('.acc-file-info-form').submit();
		});
		/* download sale attachment file */
		$('.sale-down-link').click( function(e) {
			e.preventDefault();
			$(this).closest('.sale-file-info-form').attr('action', '<?php echo $this->webroot; ?>SampleDataEntry/download_object_from_cloud');
			$(this).closest('.sale-file-info-form').attr('method', 'post');
			$(this).closest('.sale-file-info-form').submit();
		});

		/* delete file */
		$('.btn-delete').click(function(e) {
			e.preventDefault();
			var del_clicked = $(this).closest('.sale-file-info-form');
			$.confirm({
				title: "<?php echo __('確認欄'); ?>",
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
							del_clicked.attr('action', '<?php echo $this->webroot; ?>SampleDataEntry/delete_object_from_cloud');
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


		/* approve cancel button click */
		$("#btn_approvecancel").click(function(e) {
			e.preventDefault();
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);
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
				content: "<?php echo __("全行を承認キャンセルしてよろしいですか。") ?>",
				buttons: {
			        ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
			          	action:function(){
			          		//pop up model show for mail
			          		// $("#myPOPModal").addClass("in");
							// $("#myPOPModal").css({"display":"block","padding-right":"17px"});
			          		//$("#overlay").show();
							//$("#approve-cancel-form").submit();
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

		/* Reject button click */
		$("#btn_reject").click(function(e) {
			e.preventDefault();
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);
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
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
			          	action:function(){
							reduceMailSetup(data_action);
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
		});
		
		/* approve */
		$("#btn_approve").click(function(e) {
			e.preventDefault();
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);
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
				content: "<?php echo __("全行を承認してよろしいですか。") ?>",
				buttons: {
			        ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
			          	action:function(){
							 reduceMailSetup(data_action);
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
		});

		/* request */
		$("#btn_request").click(function(e) {
			e.preventDefault();
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);
			var sample_id = [];
			$("#data_entry tbody tr").each(function() {
				var obj = $(this).find('.chk_data');
				if(obj.is(":disabled") == false) {
					if(obj.is(":checked")) {
						sample_id.push(obj.val());
					}
				}
			});
			var len = sample_id.length;
			if(len > 0) {
				$(".request_data").val(JSON.stringify(sample_id));
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
				var msg = errMsg(commonMsg.JSE034);
				$("#error").html(msg);
				$("#success").empty();
			}
		});

		/* email sent    */
		$("#btn_save").click(function(e) {	
			e.preventDefault();
			//Added By PanEiPhyo (20200305), for level 4 file upload
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);			
			var sample_id = [];
			var mailData = $('.mail_content.body').html();
			$("#data_entry tbody tr").each(function() {
				var obj = $(this).find('.chk_l7_data');
				if(obj.is(":disabled") == false) {
					if(obj.is(":checked")) {
						var sid = $(this).find('s_id').context.rowIndex;
						var index = $(this).find('#index_no').html();
						console.log(index);
						sample_id[sid] = obj.val();
						mailData += 'SID'+sid+' <'+index+'>, ';
					}
				}
			});
			$('.mail_content.body').html(mailData.slice(0,-2));

			console.log(sample_id);
			var len = sample_id.length;
			if(len > 0) { //end, PanEiPhyo (20200305)
				$(".request_data").val(JSON.stringify(sample_id));
				$.confirm({
					title: "<?php echo __('確認メールの送信'); ?>",
					icon: 'fas fa-exclamation-circle',
					type: 'green',
					typeAnimated: true,
					closeIcon: true,
					columnClass: 'medium',
					animateFromElement: true,
					animation: 'top',
					draggable: false,  
					content: "<?php echo __("メールを送信してもよろしいですか？") ?>",
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

			//Added by PanEiPhyo (20200305)
			} else {
				var msg = errMsg(commonMsg.JSE034);
				$("#error").html(msg);
				$("#success").empty();
			}
			//End PanEiPhyo (20200305)
		});

	});

	

	/* reduce mail set up */
	function reduceMailSetup(data_action){
		
		var lastRequest = '<?php echo $lastRequest; ?>';
		submitForm = "<?php echo '#btn_form_'; ?>"+data_action;
		/* create required fields */
		$("<input>").attr({type: "hidden", id: "toEmail", name: "toEmail"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "ccEmail", name: "ccEmail"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "bccEmail", name: "bccEmail"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "mailSubj", name: "mailSubj"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "mailBody", name: "mailBody"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "mailSend", name: "mailSend", value: mailSend}).appendTo(submitForm);

		if(!$("#chk_master").is(":checked") && data_action == 'request' && lastRequest == 'no') mailSend = 0;
		
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
				$(submitForm).attr("method","POST");
				$(submitForm).attr("action", "<?php echo $this->webroot?>SampleDataEntry/fun_"+data_action);
			}else{
				/* set mails if not pop up */
				$("#toEmail").val(toMails);
				$("#ccEmail").val(ccMails);
				$("#bccEmail").val(bccMails);
				$("<?php echo '#btn_form_'; ?>"+data_action).submit();	
				loadingPic(); 
			}
		}else{console.log(data_action);
			$("<?php echo '#btn_form_'; ?>"+data_action).submit();	
			loadingPic(); 
		}

	}

	/* get mail content by function */
	function getMailSetup(data_action){
			let page = "<?php echo $page;?>";
			$.ajax({
				url: "<?php echo $this->webroot; ?>Common/getMailContent",
				type: "POST",
				data: {data_action : data_action, page : page,selection_name:'SampleSelections'},
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

	function loadingPic() { // function expression closure to contain variables
		var ua = window.navigator.userAgent;
   		var msie = ua.indexOf("MSIE ");
   		
		if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet 
	   {
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
			// el.src = "<?php echo $this->webroot; ?>img/loading.gif";
			$("#overlay").show();
		}
		
		} 
</script>