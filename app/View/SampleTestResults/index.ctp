
<style>

	.ques_chk {
	  	vertical-align: middle;
	  	float: right;
	}
	.chk_center{
		float: center;
		margin: 0 auto;
		position: relative;
		vertical-align:middle;
	}
	
	.btn-delete {
		float: right;
		color: red;
		margin-top:-25px;
	}
	.btn-delete:hover {
		cursor: pointer;
	}
	
	a.acc-attach-down-link, a.busi-attach-down-link {
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
	
	 #approve_btn{
 	 	display: none;
	}
	#approve_btn_cancel{
 	 	display: none;
	}

	.btn_style {
		width: 150px;
		margin-right: 0px;
	}
	.container {
    	max-width: 2140px;
	}
	.flex {
		display: flex;
		justify-content: right;
		padding: 0px;
	}
	.flex-item + .flex-item {
		margin-left: 10px;
	}
	.row {
		display: block ;
		margin-right: 0px;
	}
</style>
<script>

var mailType, mailSend, mailSubject, mailBody, toLevelId, toMails, ccLevelId, ccMails, bccLevelId, bccMails, data_action;
	$(document).ready(function() {
		
		$("html, body").animate({ scrollTop: 0 }, 'slow');		
		/* tooltip */	
		$("[data-toggle='tooltip']").tooltip({
			trigger : 'hover'
		});
		$('.datepicker').datepicker({
		    format: 'yyyy-mm-dd'
		});

		<?php
			if($enable_flag == 'ReadOnly'){?>
				$(".ques_chk").attr( "disabled" ,"disabled");
				$(".txt_remark").prop("disabled", true);
				$(".point_out").prop("disabled", true);
				$(".rpt_necessity").prop("disabled", true);
				$(".completion").prop("disabled", true);
				$('.datepicker').css({"pointer-events" : "none"});
				$(".deadline_date").prop("disabled", true);
		<?php	}
		?>
		<?php 
		//if($role_id == 1 || $role_id == 2){ 
		// if(in_array($role_id, $admin_list)){ 
			for($i =0; $i< count($data);$i++){
				$flag = $data[$i]['flag'];
				if($flag >= 5){
			?>
			$(".ques_chk").attr( "disabled" ,"disabled");
			$(".txt_remark").prop("disabled", true);
			$(".point_out").prop("disabled", true);			
			$(".rpt_necessity").prop("disabled", true);
			$(".completion").prop("disabled", true);
			$('.datepicker').css({"pointer-events" : "none"});
			$(".deadline_date").prop("disabled", true);
		<?php }
			}
		// }
		if(!empty($data)){
			
			// if($role_id == 1 || $role_id == 2){
			// if(in_array($role_id, $admin_list)){
				for($i =0; $i< count($data);$i++){
					$flag = $data[$i]['flag'];
					
					if(($flag == 6 || $flag == 9) && $approve_btn){ 
			?>	
				document.getElementById("btn_approve").style.display='block';	
				document.getElementById("btn_reject").style.display='block';
							 
			<?php
						break;
					}
				}
				
				for($i =0; $i< count($data);$i++){
					$flag = $data[$i]['flag'];
					if(($flag == 7 || $flag == 10)){ 
			?>			
					document.getElementById("btn_approve_cancel").style.display='block';
						
			<?php
						break;
					}
				}
			// }
			//  if($role_id == 4){
				for($i =0; $i< count($data);$i++){
					$flag = $data[$i]['flag'];
					if($flag != 4 && $flag != 5 ){ 
			?>		
				$(".ques_chk").attr( "disabled" ,"disabled");
				$(".txt_remark").prop("disabled", true);
				$(".point_out").prop("disabled", true);			
				$(".rpt_necessity").prop("disabled", true);
				$(".completion").prop("disabled", true);
				$('.datepicker').css({"pointer-events" : "none"});
				$(".deadline_date").prop("disabled", true);	
				//document.getElementById("save_btn").style.display='none';	 
			<?php
						break;
					}
				}
			// }
			// if($role_id == 3){
			// if(in_array($role_id, $admin_list)){
				for($i =0; $i< count($data);$i++){
					$flag = $data[$i]['flag'];
					if($flag != 5 && $flag != 6 && $flag != 9){
				?>
					// document.getElementById("save_review_btn").style.display='none';	 
				<?php
					break;
				}
					if($flag >= 10 && $flag >= 7 ){
						?>
							$(".ques_chk").attr( "disabled" ,"disabled");
							$(".txt_remark").prop("disabled", true);
							$(".point_out").prop("disabled", true);			
							$(".rpt_necessity").prop("disabled", true);
							$(".completion").prop("disabled", true);
							$('.datepicker').css({"pointer-events" : "none"});
							$(".deadline_date").prop("disabled", true);
						<?php
						break;
					}
				}
			// }
		}
		if(isset($fill_data)){
			
			$cnt_data = count($fill_data);
			
			for($i=0;$i<$cnt_data;$i++){
				//$sample_id = $data[$i]['id'];pr($data);
				$remark = h($fill_data[$i]['test']['test_result']);
				$remark = str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ), $remark);
				$point_out = h($fill_data[$i]['test']['point_out1']);
				$point_out = str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ),$point_out);
				$deadline_date = $fill_data[$i]['0']['deadline_date'];
			
				if($deadline_date == '0000-00-00'){
					$deadline_date = '';
				}else{
					$deadline_date = $deadline_date;
				}
			?>  
			var review = '<?php echo $checkButtonType['review']; ?>';
			var save = '<?php echo $checkButtonType['save']; ?>';
			if(save == 1){
				$(".completion").prop("disabled", true);
			}
			// disable one check box for  Report Optional and Completion Flag
				$("#test_result_<?php echo $i;?> tbody tr").each(function(i, tr){ 
					var rpt_necessity = $(this).find(".rpt_necessity");
					var completion = $(this).find(".completion");
					$(this).find('.completion').change(function() {
						if(this.checked) {
							if(rpt_necessity.is(':checked')) rpt_necessity.prop( "checked", false)
							rpt_necessity.prop("disabled", true);
						}else{
							rpt_necessity.prop("disabled", false);
						}
							
					});
					if(review == 1){
						$(this).find('.rpt_necessity').change(function() {
							if(this.checked) {
								if(completion.is(':checked')) completion.prop( "checked", false)
								completion.prop("disabled", true);
							}else{
								completion.prop("disabled", false);
							}
						});
					
					}
					
				});
			
				$("#test_result_<?php echo $i;?> tbody tr").each(function(i, tr){
					
					<?php
					foreach($questions as $key=>$value){
						$no = $value['questions']['id'];
						if($fill_data[$i]['test']['question'.$no] == 1){ 
					?>
							$("#test_result_<?php echo $i;?> #ques_chk<?php echo $no?>").prop( "checked", true );
					<?php
						}
					}
					?>
					<?php if($fill_data[$i]['test']['report_necessary1'] == 1){ ?>
						$("#test_result_<?php echo $i;?> .rpt_necessity").prop( "checked", true );
					<?php } ?>
					<?php if($fill_data[$i]['test']['testresult_finish'] == 1){ ?>
						$("#test_result_<?php echo $i;?> .completion").prop( "checked", true );
					<?php } ?>
						$("#test_result_<?php echo $i;?> .txt_remark").html("<?php echo $remark;?>");
						$("#test_result_<?php echo $i;?> .point_out").html("<?php echo $point_out;?>");
						$("#test_result_<?php echo $i;?> .deadline_date").val("<?php echo $deadline_date;?>");
						
				});
		<?php 
				}	
		}

		
		?>
		
		console.log(save);
		var review = '<?php echo $checkButtonType['review']; ?>';
		var save = '<?php echo $checkButtonType['save']; ?>';
		if(review == 1){
			
			$(".ques_chk").prop("disabled", false);
			$(".txt_remark").prop("disabled", false);
			$(".point_out").prop("disabled", false);			
			$(".rpt_necessity").prop("disabled", false);
			$(".completion").prop("disabled", false);
			$('.datepicker').css({"pointer-events" : "visible"});
			$(".deadline_date").prop("disabled", false);
		}
		
		/* download account attachment file */
		$('.acc-attach-down-link').click( function(e) {
			e.preventDefault();
			$(this).closest('.acc-attach-list').wrap('<form method="post" class="acc-attach-down-link" action="<?php echo $this->webroot; ?>SampleTestResults/download_object_from_cloud"></form>');
			$(this).closest('form').submit();
		});
		/* download business attachment file */
		$('.busi-attach-down-link').click( function(e) {
			e.preventDefault();
			$(this).closest('.link-list').wrap('<form method="post" class="busi-attach-down-link" action="<?php echo $this->webroot; ?>SampleTestResults/download_object_from_cloud"></form>');
			$(this).closest('form').submit();
		});
		

		/* file upload */
		$(document).on('change', '.upload_file', function() {
				
			var found = '';
			var warn = '';
			var list = [];
			$(this).closest('.upd-div').wrap('<form class="file-upload-form" enctype="multipart/form-data"></form>');
			var row = $(this).closest('tr');
			// var formdata = new FormData($(this).parent('label').parent(".upload-form")[0]);
			var formdata = new FormData($(this).parent('label').parent('.upd-div').parent(".file-upload-form")[0]);
			var fileToUpload = $(this).prop('files')[0].name;
			row.find(".acc-attach-list").each(function(i, val){
				var name = $.trim($(this).find('a.acc-attach-down-link').text());
				list.push(name);
			});
			var list_len = list.length;
			for(var i=0; i<list_len; i++) {
				var text = list[i];
				if(text == fileToUpload) {
					found = text;
				}
			}
			/*overwrite confirm box*/
			if(found != '') {
				$.confirm({
					title: "<?php echo __('確認欄'); ?>",
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
							text: "<?php echo __('はい');?>",
							btnClass: 'btn-info',
				          	action:function(){
				          		uploadFileAjax(formdata, row, 'update');
				          	}
						},	  
						cancel: {
					       	text: "<?php echo __('いいえ');?>",
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
					content: "<?php echo __("ファイルをアップロードしてよろしいでしょうか。");?>",
					buttons: {
				        ok: {
							text: "<?php echo __('はい');?>",
							btnClass: 'btn-info',
				          	action:function(){
								uploadFileAjax(formdata, row, 'save');
				          	}
						},	  
						cancel: {
					       	text: "<?php echo __('いいえ');?>",
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
				url: "<?php echo $this->webroot; ?>SampleTestResults/uploadSampleTestResultsFile",
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

		/* save button */
		$("#btn_save").click(function(e) {
			e.preventDefault();
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);
			var DataArray = [];	
			var isFillData = false;//true;
			var err_msg = '';
			document.getElementById("error").innerHTML = '';
			document.getElementById("success").innerHTML = '';
			<?php
				$cnt_data = count($data);
				for($i=0;$i<$cnt_data;$i++){
					$sample_id = $data[$i]['id'];				
				?>
				
				var inspect_ques = [];
				
				$("#test_result_<?php echo $i;?> tbody tr").each(function(i, tr){ 

					$.each($("#test_result_<?php echo $i;?> input[name='ques_chk']:checked"), function(){            

						//inspect_ques.push($(this).val());
						var key = $(this).val() - 1;
						inspect_ques[key] = $(this).val();
					});
					$.each($("#test_result_<?php echo $i;?> input[name='ques_chk']:not(:checked)"), function(){            
						var key = $(this).val() - 1;
						inspect_ques[key] = 0;

					});
					var remark = $(this).find('.txt_remark').val();				
					var point_out = $(this).find('.point_out').val();
					if($(this).find('.rpt_necessity').is(':checked')){
						var report_necessity = true;
					}else{
						var report_necessity = false;
					}
					var submission_deadline = $(this).find('.deadline_date').val();
					if($(this).find('.completion').is(':checked')){
						var completion_flag = true;
					}else{
						var completion_flag = false;
					}
					if(review){
				
						if(report_necessity == false && completion_flag == false){
							isFillData = true;
							//add by thuramoe
							
							err_msg = errMsg(commonMsg.JSE025);
							$("#error").empty();
							$("#error").append(err_msg);
							$("html, body").animate({ scrollTop: 0 }, 'slow');
							//end
							return false;
							
						}	 	
					}

					
					var uniqueQues = inspect_ques.filter(function(item, i, sites) {
						return i == sites.indexOf(item);
					});
					
					if(save){
						if((uniqueQues.length == 1 && uniqueQues[0] == 0) && (point_out == '') && (remark == '') && (submission_deadline == '') && (report_necessity == '')){
							isFillData = true;
							err_msg = errMsg(commonMsg.JSE018);
							$("#error").empty();
							$("#error").append(err_msg);
							$("html, body").animate({ scrollTop: 0 }, 'slow');
							return false;
						}
						if(report_necessity != '' && submission_deadline == ''){
							
							err_msg = errMsg(commonMsg.JSE001,['<?php echo __("Submission Deadline"); ?>']);
							$("#error").empty();
							$("#error").append(err_msg);
							$("html, body").animate({ scrollTop: 0 }, 'slow');
							isFillData = true;
							return false;
						}
					}else if(review){
							//add by trm
							if(completion_flag == false && report_necessity == true && submission_deadline == ''){
								err_msg = errMsg(commonMsg.JSE001,['<?php echo __("Submission Deadline"); ?>']);
								$("#error").empty();
								$("#error").append(err_msg);
								$("html, body").animate({ scrollTop: 0 }, 'slow');
								isFillData = true;
								return false;
							}
							//end
					} 
					
					var myDataRows = [];
					
					myDataRows.push(<?php echo $sample_id;?>,inspect_ques,remark,point_out,report_necessity,submission_deadline,completion_flag);
						
					DataArray.push(myDataRows);
					
				});
			<?php }?>
			
			if(isFillData == false){
				var myJSONString = JSON.stringify(DataArray);
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
									reduceMailSetup(data_action, myJSONString);
									
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
		
		});
	
		/* review button */
		$("#btn_review").click(function(e) {
			e.preventDefault();
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);
			var DataArray = [];	
			var isFillData = false;//true;
			var err_msg = '';
			document.getElementById("error").innerHTML = '';
			document.getElementById("success").innerHTML = '';
			<?php
				$cnt_data = count($data);
				for($i=0;$i<$cnt_data;$i++){
					$sample_id = $data[$i]['id'];				
				?>
				
				var inspect_ques = [];
				
				$("#test_result_<?php echo $i;?> tbody tr").each(function(i, tr){ 
					$.each($("#test_result_<?php echo $i;?> input[name='ques_chk']:checked"), function(){            

						var key = $(this).val() - 1;
						inspect_ques[key] = $(this).val();
						});
						$.each($("#test_result_<?php echo $i;?> input[name='ques_chk']:not(:checked)"), function(){            
						var key = $(this).val() - 1;
						inspect_ques[key] = 0;

					});
					var remark = $(this).find('.txt_remark').val();				
					var point_out = $(this).find('.point_out').val();
					if($(this).find('.rpt_necessity').is(':checked')){
						var report_necessity = true;
					}else{
						var report_necessity = false;
					}
					var submission_deadline = $(this).find('.deadline_date').val();
					if($(this).find('.completion').is(':checked')){
						var completion_flag = true;
					}else{
						var completion_flag = false;
					}
					
				
					if(report_necessity == false && completion_flag == false){
						isFillData = true;
						//add by thuramoe
						
						err_msg = errMsg(commonMsg.JSE025);
						$("#error").empty();
						$("#error").append(err_msg);
						$("html, body").animate({ scrollTop: 0 }, 'slow');
						//end
						return false;
						
					}	 	
					
					if(completion_flag == false && report_necessity == true && submission_deadline == ''){
						err_msg = errMsg(commonMsg.JSE001,['<?php echo __("Submission Deadline"); ?>']);
						$("#error").empty();
						$("#error").append(err_msg);
						$("html, body").animate({ scrollTop: 0 }, 'slow');
						isFillData = true;
						return false;
					}
				
					
					
					var myDataRows = [];
					
					myDataRows.push(<?php echo $sample_id;?>,inspect_ques,remark,point_out,report_necessity,submission_deadline,completion_flag);
						
					DataArray.push(myDataRows);
					
				});
			<?php }?>
			
			if(isFillData == false){
				var myJSONString = JSON.stringify(DataArray);
				$.confirm({
					title: "<?php echo __('確認 確認'); ?>",
					icon: 'fas fa-exclamation-circle',
					type: 'green',
					typeAnimated: true,
					closeIcon: true,
					columnClass: 'medium',
					animateFromElement: true,
					animation: 'top',
					draggable: false,
					content: "<?php echo __("すべてのデータを確認しますか。"); ?>",
					buttons: {   
							ok: {
								text: "<?php echo __('はい');?>",
								btnClass: 'btn-info',
								action: function(){
									var self = this;
									reduceMailSetup(data_action, myJSONString);
									
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

		});	

		/* approve button */
		$("#btn_approve").click(function(e) {
			e.preventDefault();
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);
			// alert('arrived');
			document.getElementById("error").innerHTML = '';
			document.getElementById("success").innerHTML = '';

			var DataArray = [];	
			
			<?php
				$cnt_data = count($data);
				for($i=0;$i<$cnt_data;$i++){
					$sample_id = $data[$i]['id'];				
				?>
				$("#test_result_<?php echo $i;?> tbody tr").each(function(i, tr){ 
				/* if($(this).find('.completion').is(':checked')){
					var completion_flag = true;
				}else{
					var completion_flag = false;
				} */
					var myDataRows = [];
						
					myDataRows.push(<?php echo $sample_id;?>);
					
					DataArray.push(myDataRows);
					
				});
			<?php }?>
			var myJSONString = JSON.stringify(DataArray);
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
							text: "<?php echo __('はい'); ?>",
							btnClass: 'btn-info',
							action: function(){
								var self = this;
								reduceMailSetup(data_action, myJSONString);
							}
					},	  
				cancel : {
						text: "<?php echo __('いいえ'); ?>",
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
		});

		/* reject button */
		$("#btn_reject").click(function(e) {
			e.preventDefault();
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);
			document.getElementById("error").innerHTML = '';
			document.getElementById("success").innerHTML = '';
			var DataArray = [];	
			
			<?php
				$cnt_data = count($data);
				for($i=0;$i<$cnt_data;$i++){
					$sample_id = $data[$i]['id'];				
				?>
					var myDataRows = [];
						
					myDataRows.push(<?php echo $sample_id;?>);
					
					DataArray.push(myDataRows);
					
				
			<?php }?>
			var myJSONString = JSON.stringify(DataArray);

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
							action: function(){
								var self = this;
								reduceMailSetup(data_action, myJSONString);
								// return $.ajax({
								// 		data : {myJSONString: myJSONString },
								// 		url: "<?php //echo $this->Html->url(array('controller'=> 'TestResult','action' => 'Acc_RejectState')); ?>",
								// 		dataType: 'json',
								// 		method: 'post',
								// 	}).done(function (result) {
								// 		if(result.error){
								// 			var json = result.msg;
								// 			document.getElementById("error").innerHTML = json;
											
								// 		} else{
								// 			// window.location.reload();
								// 			var json = result.msg;
								// 			document.getElementById("success").innerHTML = json;
								// 			document.getElementById("approve_btn").style.display='none';
								// 			document.getElementById("reject_btn").style.display='none';
								// 		} 
								// 		// window.location.reload();										

								// 	}).fail(function(){
								// 		console.log('fail');
										
								// 	});



							}
					},	  
				cancel : {
						text: "<?php echo __('いいえ'); ?>",
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
		});

		/* approve cancel button */
		$("#btn_approve_cancel").click(function(e) {
			e.preventDefault();
			$("#data-action").val($(this).attr("data-action"));
			data_action = $("#data-action").val();
			getMailSetup(data_action);
			document.getElementById("error").innerHTML = '';
			document.getElementById("success").innerHTML = '';
			var DataArray = [];	
			
			<?php
				$cnt_data = count($data);
				for($i=0;$i<$cnt_data;$i++){
					$sample_id = $data[$i]['id'];				
				?>
					var myDataRows = [];
						
					myDataRows.push(<?php echo $sample_id;?>);
					
					DataArray.push(myDataRows);
					
				
			<?php }?>
			var myJSONString = JSON.stringify(DataArray);

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
							text: "<?php echo __('はい'); ?>",
							btnClass: 'btn-info',
							action: function(){
								var self = this;
								reduceMailSetup(data_action, myJSONString);
							
							}
					},	  
				cancel : {
						text: "<?php echo __('いいえ'); ?>",
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
		});

	});
	
	/* Save and Email mail buttom admin level 3 when save finished*/	
	function  click_sent_mail_btn (){

		var DataArray = [];	
		var isFillData = false;//true;
		var err_msg = '';
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';
		<?php
			$cnt_data = count($data);
			for($i=0;$i<$cnt_data;$i++){
				$sample_id = $data[$i]['id'];				
			?>
			
			var inspect_ques = [];
	           
			$("#test_result_<?php echo $i;?> tbody tr").each(function(i, tr){ 

				$.each($("#test_result_<?php echo $i;?> input[name='ques_chk']:checked"), function(){            

		            inspect_ques.push($(this).val());
		
		        });
				
				 var remark = $(this).find('.txt_remark').val();				
				 var point_out = $(this).find('.point_out').val();
				 if($(this).find('.rpt_necessity').is(':checked')){
					 var report_necessity = true;
				 }else{
					 var report_necessity = false;
				 }
				 var submission_deadline = $(this).find('.deadline_date').val();
				 if($(this).find('.completion').is(':checked')){
					 var completion_flag = true;
				 }else{
					 var completion_flag = false;
				 }
				//  <?php 
				//  if($role_id == 3){
				// // if(in_array($role_id, $admin_list)){
				// ?>
			
				 if(report_necessity == false && completion_flag == false){
					isFillData = true;
					//add by thuramoe
				 	
					err_msg = errMsg(commonMsg.JSE025);
					$("#error").empty();
					$("#error").append(err_msg);
					$("html, body").animate({ scrollTop: 0 }, 'slow');
					//end
					return false;
					
				}	 	
				// <?php //}?>
				// <?php 
				// if($role_id == 4)
				// {?>
				
					if((inspect_ques == '') && (point_out == '') && (remark == '') && (submission_deadline == '') && (report_necessity == '')){
						isFillData = true;
						err_msg = errMsg(commonMsg.JSE018);
						$("#error").empty();
						$("#error").append(err_msg);
						$("html, body").animate({ scrollTop: 0 }, 'slow');
						return false;
					}
					if(report_necessity != '' && submission_deadline == ''){
						
						err_msg = errMsg(commonMsg.JSE001,['<?php echo __("Submission Deadline"); ?>']);
						$("#error").empty();
						$("#error").append(err_msg);
						$("html, body").animate({ scrollTop: 0 }, 'slow');
						isFillData = true;
						return false;
					}
				// <?php //}else{
				// }else if($role_id == 3){
				// 	?>
						//add by trm
						if(completion_flag == false && report_necessity == true && submission_deadline == ''){
							err_msg = errMsg(commonMsg.JSE001,['<?php echo __("Submission Deadline"); ?>']);
							$("#error").empty();
							$("#error").append(err_msg);
							$("html, body").animate({ scrollTop: 0 }, 'slow');
							isFillData = true;
							return false;
						}
						//end
				// <?php //}?>
				
				 var myDataRows = [];
				
				 myDataRows.push(<?php echo $sample_id;?>,inspect_ques,remark,point_out,report_necessity,submission_deadline,completion_flag);
					
				 DataArray.push(myDataRows);
				
			 });
		<?php }?>
		
		if(isFillData == false){
			var myJSONString = JSON.stringify(DataArray);
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
			            text: "<?php echo __('はい'); ?>",
			          	btnClass: 'btn-info',
			            action: function(){
			            	var self = this;
			            	$("#myPOPModal").addClass("in");
							$("#myPOPModal").css({"display":"block","padding-right":"17px"});
							$("#test_result_form").attr('action', '<?php echo $this->webroot; ?>SampleTestResults/Ajax_SaveDataSentMail');
							$("#test_result_form").find("#myJSONString").val(myJSONString);

				        }
				       },

				  cancel : {
				       	text: "<?php echo __('いいえ');?>",
				           btnClass: 'btn-default',
				       	cancel: function(){
				          
				       		}
				       	 }
				    	},
				   theme: 'material',
				   animation: 'rotateYR',
				   closeAnimation: 'rotateXR'
			});
		} 
	}
	/* test result data */

	


	/* reduce mail set up */
	function reduceMailSetup(data_action, jsonData){

		submitForm = "<?php echo '#btn_form_'; ?>"+data_action;
		/* create required fields */
		$("<input>").attr({type: "hidden", id: "toEmail", name: "toEmail"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "ccEmail", name: "ccEmail"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "bccEmail", name: "bccEmail"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "mailSubj", name: "mailSubj"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "mailBody", name: "mailBody"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "myJSONString", name: "myJSONString"}).appendTo(submitForm);
		$("<input>").attr({type: "hidden", id: "mailSend", name: "mailSend", value: mailSend}).appendTo(submitForm);


		$("#myJSONString").val(jsonData);
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
				// alert('arrived');
				$(submitForm).attr("action", "<?php echo $this->webroot?>SampleTestResults/fun_"+data_action);
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

	/* get mail content by function */
	function getMailSetup(data_action){
		let page = "<?php echo $page;?>";
		$.ajax({
			url: "<?php echo $this->webroot; ?>Common/getMailContent",
			type: "POST",
			data: {data_action : data_action, page : page,selection_name:'SampleSelections'},
			dataType: "json",
			success: function(data) {
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
			},
		});
	}


</script>

<div id="overlay">
	<span class="loader"></span>
</div>
<h3><?php echo __("テスト結果作成") ?></h3>
<hr>

<?php 

// if ($role_id == 4) {

//  	echo $this->element('autocomplete_body', array(
// 					"level_id" => AdminLevel::ACCOUNT_SECTION_MANAGER,
// 					"submit_form_name" => "test_result_form",
// 					"MailSubject" => "【サンプルチェック】".$layer_name."テスト結果作成完了 通知",
// 	                "MailTitle"   => "経理TL各位<br/><br/>",
// 	                "MailBody"    =>"当月の".$layer_name."のサンプルチェックテスト結果の作成が完了しました。<br/>データを確認の上、承認を行ってください。<br/><br/>"));
// }

	echo $this->element("autocomplete", array(
		"to_level_id" => "",
		"cc_level_id" =>"",
		"submit_form_name" => "",
		"MailSubject" => "",
		"MailTitle" => "",
		"MailBody" => ""));

?>
<?php //echo $this->Form->create(false,array('url'=>'','type'=>'post','id'=>'','class'=>'','name'=>'test_result_form','id'=>'test_result_form')); 
	?>
	<!-- <input type="hidden" name="toEmail" id="toEmail">
	<input type="hidden" name="ccEmail" id="ccEmail">
	<input type="hidden" name="mailSubj" id="mailSubj">
    <input type="hidden" name="mailTitle" id="mailTitle">
    <input type="hidden" name="mailBody" id="mailBody"> -->
	<!-- <input type="hidden" name="myJSONString" id="myJSONString"> -->
	<div class = 'content register_container'>
		<!-- <div class="row"> -->
		<div class="errorsuccess">
			<div class="success" id="success"><?php echo $successMsg; ?><?php echo ($this->Session->check("Message.testresultOK"))? $this->Flash->render("testresultOK") : ''; ?></div>
			<div class="error" id="error"><?php echo $errorMsg;?><?php echo ($this->Session->check("Message.testResultFail"))? $this->Flash->render("testResultFail") : ''; ?></div>
		</div>
		<!-- </div> -->
		<div class ="row line">
			<div class = "col-md-6">
				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("対象月"); ?></label>
				    <div class="col-md-8">
						<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='dept_incharge' value = '<?php echo $period;?>' disabled="disabled" >     
					</div>
				</div>
			</div>
		
			<div class = "col-md-6" >
				<!-- <div class="row">
					<?php //if(!empty($data))if($role_id == 1 || $role_id == 2){?>
					<div class="col-md-8">
						<div class = "form-group">
					      	<div class="col-md-12">
								 <input type ="button" class="btn btn-success btn_approve_style btn_sumisho approve_btn" id="approve_btn" onClick = "click_approve_btn();" value = "<?php //echo __('承認');?>">
							</div>
						</div>						
					</div>
					<?php //}?> -->
				<?php 
				/* Data check For reject  when flag 6 or 9 show reject buttom*/
				// $data_count = count($data);
				// for ($i=0; $i <$data_count ; $i++) { 					
				// 	$flag_check = $data[$i]['flag'];
				// } 
				//if(!empty($data))if((($flag_check == 9 || $flag_check == 6) && ($role_id == 2 || $role_id ==1 ) && ($flag != 7 && $flag != 10 )) ){?>

					<!-- <div class="col-md-4">
						<div class = "form-group">
					      	<div class="col-md-12">
								 <input type ="button" class="btn btn-success btn_approve_style btn_sumisho approve_btn" id="reject_btn" onClick = "click_reject_btn();" value = "<?php //echo __('差し戻し');?>">
							</div>
						</div>	
					</div>	 -->
							
				<?php //}?><!--  row end column 12 -->
				<!-- </div> -->
			</div>
			
		</div>
		<div class ="row line">
			<div class = "col-md-6">
				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("部署"); ?></label>
				      <div class="col-md-8">
						<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='target_month' value = '<?php echo $layer_code;?>' disabled="disabled" >     
					</div>
				</div>
			</div>
		</div>
		<div class ="row line">
			<div class = "col-md-6">
				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("部署名"); ?></label>
				      <div class="col-md-8">
						<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='target_month' value = '<?php echo $layer_name;?>' disabled="disabled" >     
					</div>
				</div>
			</div>
			<?php //if(!empty($data))if($role_id == 1 || $role_id == 2){?>

			<!-- <div class = "col-md-6" >
				<div class = "form-group">
				      <div class="col-md-12">
						  <input type ="button" class="emp_register but_register approve_cancel" id="approve_btn_cancel" onClick = "click_approve_cancel();" value = "<?php //echo __('承認キャンセル');?>">
					</div>
				</div>
			</div> -->
			<?php// }?>
		</div>
		<div class ="row line">
			<div class = "col-md-6">
				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("カテゴリー"); ?></label>
				      <div class="col-md-8">
						<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='target_month' value = '<?php echo $category;?>' disabled="disabled" >     
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">
			
		<div class="flex col-md-12 col-sm-12 col-xs-12">
			<?php 
				foreach($checkButtonType as $key=>$button){
					if($button) {
						$action_function = str_replace(' ', '', $key);
						if($action_function == 'save') $btn_name = 'Save';
						if($action_function == 'review') $btn_name = ' Check ';
						if($action_function == 'approve') $btn_name = 'Approve';
						if($action_function == 'approve_cancel') $btn_name = 'Approve Cancel';
						if($action_function == 'reject') $btn_name = 'Revert';

			?>
				<form action="<?php echo $this->webroot.'SampleTestResults/fun_'.$action_function; ?>" method="post" name="<?php echo 'btn_form_'.$action_function; ?>" id="<?php echo 'btn_form_'.$action_function; ?>" class="flex-item">
				<input type="hidden" name="data-action" id="data-action"/>
				<input type="submit" name="<?php echo 'btn_'.strtolower($action_function);?>" id="<?php echo 'btn_'.strtolower($action_function);?>" class="btn btn-save" value="<?php echo __($btn_name); ?>" data-action="<?php echo $action_function;?>"></form>
			<?php }} ?>
		</div>
			<div class="row">
			 	<div class="table-wpr table-responsive" style="overflowtable table-bordered overflow-x:auto; ">
			 	
			 	<?php for($i=0;$i<count($data);$i++){?>
			 	
				 	<table class="table table-bordered acc_review" style="margin-top:10px;width:100%;table-layout:auto;" id="">
				 		<thead class="check_period_table">
				 		<tr>
				 			<th  width="3%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("SID"); ?></th>
							<th  width="5%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("部署"); ?></th>
							<th  width="7%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("担当者"); ?></th>
							<th  width="10%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("案件名"); ?></th>
							<th  width="8%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("計上日"); ?></th>
							<th  width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("Index"); ?></th>
							<th  width="10%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("勘定科目"); ?></th>
							<th  width="10%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("相手先"); ?></th>
							<th  width="10%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("相手先名"); ?></th>
							<th  width="8%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("金額"); ?></th>
							<th  width="10%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("備考"); ?></th>
							<th  width="14%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("営業添付資料"); ?></th>
						</tr>
						
						</thead>
						<tbody>
							<?php if(isset($data) && count($data) > 0) {
								$show_btn = true;
								$id = h($data[$i]['id']);
								$sid = h($data[$i]['sid']);
								$incharge_person = h($data[$i]['incharge_name']);
								$project_title = h($data[$i]['project_title']);
								$posting_date = h($data[$i]['posting_date']);
								$index = h($data[$i]['index_no']);
								$sample_flag = h($data[$i]['flag']);
								$account_item = h($data[$i]['account_item']);
								$destination = h($data[$i]['destination_code']);
								$destination_name = h($data[$i]['destination_name']);
								$amount_money = h($data[$i]['money_amt']);
								$amount_money = str_replace('.00', '', number_format($amount_money, 2, '.', ','));
								$remarks = h($data[$i]['remark']);
								$account_file = $data[$i]['acc_attach_file'];
								$business_file = $data[$i]['busi_attach_file'];
								$flag = $data[$i]['flag'];
								if($flag ==1 || $flag == 2 || $flag ==3){
									$show_btn = false;
								}
								
							}?>
							<tr class = ''>
								<td style="width: 3%; word-wrap: break-word;text-align: right;"><?php echo $i+1; ?></td>
								<td style="width: 5%; word-wrap: break-word;"><?php echo $layer_code; ?></td>
								<td style="width: 7%; word-wrap: break-word;"><?php echo $incharge_person; ?></td>
								<td style="width: 10%; word-wrap: break-word;"><?php echo $project_title; ?></td>
								<td style="width: 5%; word-wrap: break-word;"><?php echo $posting_date; ?></td>
								<td style="width: 5%; word-wrap: break-word;"><?php echo $index; ?></td>
								<td style="width: 10%; word-wrap: break-word;"><?php echo $account_item; ?></td>
								<td style="width: 10%; word-wrap: break-word;"><?php echo $destination; ?></td>
								<td style="width: 10%; word-wrap: break-word;"><?php echo $destination_name; ?></td>
								<td style="width: 8%; word-wrap: break-word;text-align: right;"><?php echo $amount_money; ?></td>
								<td style="width: 10%;word-wrap: break-word;min-width: 160px;max-width: 160px;white-space:normal;"><?php echo nl2br($remarks); ?></td>
								<td style="width: 15%;min-width: 160px;max-width: 160px;">
									<?php //if($role_id == 1) { ?>
									<?php //if(in_array($role_id, $admin_list)) { ?>
									<form class="upload-form" name="upload-form" method="post" enctype="multipart/form-data" action="#">
										<input type="hidden" name="sample_data_id" id="sample_data_id" value="<?php echo $id;?>">
										<input type="hidden" name="sid" class="sid" value="<?php echo $sid; ?>">
									</form>
									<?php //} ?>
									<div class="show-list">
									<?php 
										$cnt_busi_file = count($business_file);
										for($f=0; $f<$cnt_busi_file; $f++) {
											$attachment_id = h($business_file[$f]['attachment_id']);
											$file_name = h($business_file[$f]['file_name']);
											$url = h($business_file[$f]['url']);
									?>
											<div class="link-list">
												
												<input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
												<input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
												<input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
												<a href="#" class="busi-attach-down-link" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file_name; ?></a>
											
											</div>
									<?php } ?>
									</div>
								</td>
							</tr>
						</tbody>
				 	</table>
				 	<table class="table table-bordered acc_review" style="margin-top:10px;width:100%;" id="test_result_<?php echo $i;?>">
				 		<thead class="check_period_table">
					 		<tr>
								<th width="3%" class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("RID"); ?></th>
								<th width="3%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("経理添付/入手資料"); ?></th>
								<th width="20%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("点検項目"); ?></th>
								<th width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("備考"); ?></th>
								<th width="15%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("指摘事項"); ?></th>
								<th width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("報告要否"); ?></th>
								<th width="14%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("提出期限"); ?></th>
								<?php //if($role_id == 1 || $role_id == 2 || $role_id == 3){?>
								<?php //if(in_array($role_id, $admin_list)){?>
								<th  width="5%" class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("完了フラグ"); ?></th>
								<?php //}?>
							</tr>
						</thead>
						<tbody>
							<td style="width: 3%; word-wrap: break-word;text-align: right;"><?php echo $i+1; ?></td>
							<td style="width: 3%; word-wrap: break-word; text-align: center;">
							
								<?php 
								//if($role_id == 4) 
								//{ 
									if($sample_flag == '4' || $sample_flag == '5'){ ?>
										<div class="upd-div">
									
											<input type="hidden" name="sample_data_id" id="sample_data_id" value="<?php echo $id;?>">
											<input type="hidden" name="sid" class="sid" value="<?php echo $sid; ?>">
											<label id="btn_browse">Upload File
											<input type="file" class="upload_file" name="data[File][upload_file][]">
											</label>
								
										</div>
									<?php } ?>
								<?php //} ?>
								<div class="show-list">
								<?php 
									$cnt_acc_attach_file = count($account_file);
									for($q=0; $q<$cnt_acc_attach_file; $q++) {
										$attachment_id = h($account_file[$q]['attachment_id']);
										$file_name = h($account_file[$q]['file_name']);
										$url = h($account_file[$q]['url']);
									?>
										<div class="acc-attach-list">
											
											<input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
											<input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
											<input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
											<a href="#" class="acc-attach-down-link" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file_name; ?></a>
											<?php 
											//if($role_id == 4 ) 
											//{ 
												if($sample_flag == '4' || $sample_flag == '5'){?>
												<div class="btn-delete"><span class="glyphicon glyphicon-remove-sign"></span></div>
											<?php } ?>
										<?php //} ?>
											
										</div>
								<?php } ?>
								</div>
							</td>
							<td width="23%">
								<?php
								foreach($questions as $key=>$value){
									$no = $value['questions']['id'];
								?>
									<?php echo $value['questions']['question']; ?><input type="checkbox" name="ques_chk" class="ques_chk" id="ques_chk<?php echo $no?>"  value="<?php echo $no?>"></br>
								<?php
								}
								?>
								
							</td>
							<td><textarea class="form-control txt_note txt_remark" style="width: 100%" maxlength= '1000' rows="5"></textarea></td>
							<td><textarea class="form-control txt_note point_out" style="width: 100%" maxlength= '1000' rows="5"></textarea></td>
							<td style="width:5%;text-align:center;padding-top:60px;">
								<input type="checkbox" name="rpt_necessity" class="rpt_necessity" id="rpt_necessity" value="1">
							</td>
							<td style="width: 14%;">
								<div class="input-group date datepicker" data-provide="datepicker">
									<input type="text" class="form-control deadline_date" id="deadline_date" name="deadline_date" value="" />
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</td>
							<?php //if($btn_name == 'Review' || $btn_name == 'Reject' || $btn_name == 'Approve Cancel'){?>
							<td style="width:5%;text-align:center;padding-top:60px;">
								<input type="checkbox" name="completion" class="completion" id="completion" value="1">
							</td>
							<?php //}?>
						</tbody>
				 	</table>
				 	<?php }?><!--  end table loop -->
				 	</div>
				 	<?php 
						// if(!empty($data)) if($role_id == 4 && $save_btn_show && $show_btn){?>
					 	<!-- <div class="form-group col-md-6 col-sm-12 col-xs-12 emp_register_butrow" style="text-align: right;">
				     		<input type="button" class="btn btn_save but_register" id="save_btn" onClick = "click_save_btn();" value = "<?php //echo __('保存');?>">
				 		</div> -->
			 		<?php //}?>
					
			 	
			</div>
			<div class="row">
				<div class="col-sm-12">
					<p class="no-data"><?php echo $not_exist_data; ?></p>
				</div>
			</div> 	
		</div>
	</div><!-- end container -->
<?php
    //echo $this->Form->end();
?>

<script>

	/* delete file */
	$('.btn-delete').click(function(e) {
		
		e.preventDefault();
		$(this).closest('.acc-attach-list').wrap('<form method="post" class="del-tr-file-form" action="<?php echo $this->webroot; ?>SampleTestResults/delete_object_from_cloud"></form>');
		var del_clicked = $(this).closest('.acc-attach-list');
		
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
			content: "<?php echo __("データを削除してよろしいですか。");?>",
			buttons: {
		        ok: {
					text: "<?php echo __('はい');?>",
					btnClass: 'btn-info',
		          	action:function(){
		          		del_clicked.parent('.del-tr-file-form').submit();
						loadingPic(); 
						
		          	}
				},	  
				cancel: {
			       	text: "<?php echo __('いいえ');?>",
					btnClass: 'btn-default',
			       	action: function(){}
				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
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
</script>