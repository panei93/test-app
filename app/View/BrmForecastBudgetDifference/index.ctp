<?php echo $this->Form->create(false,array('type'=>'post', 'name' =>'BrmForecastBudgetDifference' ,'id' =>'BrmForecastBudgetDifference', 'enctype'=>'multipart/form-data'));?>
<?php
    echo $this->element('autocomplete', array(
                        "to_level_id" => "",
                        "cc_level_id" => "",
                        "bcc_level_id" => "",
                        "submit_form_name" => "BrmForecastBudgetDifference",
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

  	.main-container{
  		padding-right: 15px;
  		padding-left: 15px;
  		padding-top: 15px;
  		padding-bottom: 15px;
  		margin-right: auto;
  		margin-left: auto;
  	}
  	table{
  		font-size: 12.4px;
  	}
  	#tbl_budget_forecast tr{
  		height: 50px;
  	}
	#tbl_budget_forecast th{
		text-align: center;
		vertical-align: middle;
		white-space: normal;
	}
	#tbl_budget_forecast td{
		padding: 0 0 0 0;
		word-wrap: break-word;
		white-space: normal;
		}
	#tbl_budget_forecast .tbl_first_col{
		padding-left: 10px;
		vertical-align: middle;
		width: 18%;
	}
	#tbl_budget_forecast .tbl_md_col{
		width: 10%;
		text-align: right;
		padding-right: 1%;
		vertical-align: middle;
		background-color: #F9F9F9;
		opacity: 1;
	}

	#tbl_budget_forecast .tbl_lst_col{		
		width: 50%;
	}	
	
	.head_style{
		font-weight: bold;
		font-size: 15px;
		text-decoration: underline;
	}
	.font_bold{
		font-weight: bold;
	}
	
	.text_md,.text_lst{
		padding:0 3px 0 3px;
		width: 100%;
		outline: none;
		border: none;
		border-radius: 0;
   	 	box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
   	 	resize: none;
	}
	.text_md,.text_lst{
		height: 57px;
	}
	/*.text_md_height{
		height: 117px;
	}	*/
	.negative {
		color: #f31515;
		text-align: left;
		text-decoration: none;
		font-weight: normal;
		background-color: #f2dede;
		padding:10px;
		margin-right:-15px;
		border-radius: 4px;
	}
	.adjust{
		padding-right: 30px;
	}
	label{
		padding-top: 10px;
	}
	.one{
		width: 150px;
	}
	#btn_approve_cancel{
		width: auto;
	}
	textarea{
		vertical-align: middle;
	}
	input,textarea{
		background-color: #D5F4FF;
		/*input field color */
	}
	input[disabled],textarea[disabled] {
		background-color: #F9F9F9;
	}
	.btn-success[disabled]{
		background-color: #5cb85c;
		border-color: #4cae4c;
		color: #fff;
	}

	/* to be scrollable tables*/
	.horizontal-scrollable > .row { 
            overflow-x: auto; 
            white-space: nowrap; 
        } 
          
    .horizontal-scrollable > .row > .col-md-6 { 
        display: inline-block; 
        float: none; 
    } 
    /* File Import */
    /*fieldset.scheduler-border{
		border: 1px solid #ccc !important;
		padding: 20px !important;
	}*/
</style>
<script>
	function saveFBD(){
		var errorFlag = true;

		$('#error').html('');
		
		var filling_date = $('#filling_date').val();
		if(filling_date==''){
			errorFlag = false;
			var newElement = '<div>'+errMsg(commonMsg.JSE001,['<?php echo __("提出日")?>'])+'</div>';
			$('#error').append(newElement);
			$('#success').hide();
			scrollText();

		}
  
		if(errorFlag){
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
								
								var layer_code = '<?php echo $this->Session->read('SESSION_LAYER_CODE'); ?>';
								
								var page = 'BrmForecastBudgetDifference';
								var func = 'save';
								var data = [];
								
								$(".subject").text('');
								$(".body").html('');
								$("#mailSubj").val('');
								$("#mailBody").val(''); 
								$.ajax({
									type:'post',
									url: "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/getMailLists",
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
												document.forms[0].action = "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/saveData";
												document.forms[0].method = "POST";
												document.forms[0].submit();
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
												
												$('#BrmForecastBudgetDifference').attr('method','post');
												$('#BrmForecastBudgetDifference').attr('action', "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/saveData");
											}
										}else{
											loadingPic();
											document.forms[0].action = "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/saveData";
											document.forms[0].method = "POST";
											document.forms[0].submit();
											
										}
					
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

	};
</script>

<!-- <input id="txtChar" type="text" name="txtChar" class="format" /> -->
<div id="overlay">
	<span class="loader"></span>
</div>
<div class="main-container">
	<div class="row" style="padding-bottom: 50px;">		
			<h3>
				<?php echo __('見込対予算増減一覧');?>
			</h3>	    	
	    	<hr>
		<div class="col-sm-12">
			<div class="errorSuccess"><div class="success" id="success"><?php echo($this->Session->check("Message.FbdsSaveSuccess"))?$this->Flash->render("FbdsSaveSuccess") : ''; ?></div>
			</div>		
		</div>
		<div class="col-sm-12">
			<div class="errorSuccess"><div class="error" id="error"><?php echo($this->Session->check("Message.FbdfSave"))?$this->Flash->render("FbdfSave") : ''; ?></div>
			</div>		
		</div>
	</div>
	   	<div class="row">
			<div class="col-sm-6">
				<div class="form-group row">
						<div class="col-sm-12">
							<label for="budget_term" class="col-sm-4 col-form-label">
								<?php echo __('期間');?>
							</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" id="budget_term" value="<?php if(!empty($this->Session->read('TERM_NAME')))
								{$year=explode('~',$this->Session->read('TERM_NAME'))[0];echo $this->Session->read('TERM_NAME');}?>" readonly="readonly"/>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-12">
							<label for="ba_code" class="col-sm-4 col-form-label">
								<?php echo $layer['LayerType']['name_'.$lang_name];?>
							</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" id="ba_code" value="<?php if (!empty($full_ba_name)){echo $full_ba_name;}?>" readonly="readonly" name="ba_code"/>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-12">
							<label for="deadline_date" class="col-sm-4 col-form-label">
								<?php echo __('提出期日');?>
							</label>
							<div class="col-sm-8">
									<input type="text" class="form-control" id="deadline_date" name="deadline_date" value="<?php echo($fbdData['deadline_date']) ?>" readonly="readonly"/>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-12">
							<label for="filling_date" class="col-sm-4 col-form-label required">
								<?php echo __('提出日');?>
							</label>
							<div class="col-sm-8">
									<div class="input-group date datepicker" data-provide="datepicker" style="padding:0px;">

									<input type="text" class="form-control" id="filling_date" name="filling_date" value="<?php echo($fbdData['filling_date']) ?>" autocomplete="off"/>
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
									</div>
							</div>
						</div>
					</div>
			</div>
			
			<div class="col-sm-6">
				<div class="head_style negative">				※<?php echo __('予算比が100万円を超える科目に関して、増減要因を記載下さい。').'<br>※'.
				__('人員増減に関しては、特記事項がある場合のみ備考欄に記載下さい。');?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-12 col-md-12 text-right">
		<div class="row">
			<label style="color:white;" id="btn_browse" class="btn btn-success btn_sumisho">Upload File
				<input type="file" name="uploadfile" class = "uploadfile" data-id = "0" data-id-head = "<?php echo $head_data['head_dept_id']; ?>" id="upload_file">	        	
			</label> 
			<!-- Excel Download -->
			<input type="button" id="btn_excel_download" name="btn_excel_download" class="btn btn-success btn_sumisho one" value="<?php echo __("Excelダウンロード"); ?>" >

			<!-- Save -->
			<input type="button" id="btn_save" name="btn_save" class="btn btn-success btn_sumisho" value="<?php echo __("保存"); ?>" <?php if($createLimit != 'true') echo 'disabled="disabled"';?> onclick = "saveFBD();">

			<!-- Approve -->
			<input type="button" id="btn_approve" name="btn_approve" class="btn btn-success btn_sumisho" value="<?php echo __("承認"); ?>" <?php if($approveLimit != 'true') echo 'disabled="disabled"';?>>
			

			<!-- Approve Cancel -->
			<?php
			if(!empty($_GET['term'])){
				$_SESSION['TERM_ID'] = $_GET['term'];
			} 
			if(!empty($_GET['hq'])){
				$_SESSION['HEAD_DEPT_ID'] = $_GET['hq'];
			}
			if(!empty($_GET['code'])){
				$_SESSION['BUDGET_BA_CODE'] = $_GET['code'];
			}
			?>
			<input type="button" id="btn_approve_cancel" name="btn_approve_cancel" class="btn btn-success btn_sumisho" value="<?php echo __("承認キャンセル"); ?>" >			
		</div>
	</div>
	
	<?php if (!empty($fbdData)): ?>
		<div class="row">
			<?php $accloop = 0; $jobloop = 0; ?>
			<div class="col-md-12 table_wpr">
				<div class="horizontal-scrollable">
					<div class="row"><div class="topScroll" style="height:20px;"></div></div>
					<div class="row" id="scrollWidth">
						<?php foreach ($fbdData['acc'] as $accountData): ?>
							<?php $accloop ++; ?>
							<div class="col-md-6">
								<table class="table table-bordered" id="tbl_budget_forecast">
									<div class="head_style"> <?php echo $accloop.'. '.str_replace(' ', '', $accountData['year1_title']).' '.__(' 対 ').' '.str_replace(' ', '', $accountData['year2_title']);?></div>
									<div class="font_bold text-right"><?php echo __('（単位：千円）');?></div>
									<thead class="check_period_table">
										<tr>
											<th><?php echo __('科目');?></th>
											<th><?php echo $accountData['year1_title'];?></th>
											<th><?php echo $accountData['year2_title'];?></th>
											<th><?php echo __('差異');?></th>
											<th><?php echo __('増減要因');?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($accountData['data'] as $sub_acc_name => $value): ?>
											<tr>
												<?php 
													$negative1 = ($value['year1'] < 0) ? 'negative' : '';
													$negative2 = ($value['year2'] < 0) ? 'negative' : '';
													$negative3 = ($value['difference'] < 0) ? 'negative' : '';
												?>
												<td class="tbl_first_col"><?php echo h($sub_acc_name);?>
												</td>
												<td class="tbl_md_col <?php echo($negative1) ?>">
													<?php echo($value['year1']) ?>
												</td>
												<td class="tbl_md_col <?php echo($negative2) ?>">
													<?php echo($value['year2']) ?>
												</td>
												<td class="tbl_md_col <?php echo($negative3) ?>">
													<?php echo($value['difference']) ?>
												</td>
												<td class="tbl_lst_col">
													<textarea class="text_lst <?php echo 'tbl1'.'_col_'.$row_no.'up';?>" name="factors[acc][<?php echo $sub_acc_name?>][<?php echo $value['factor_year'];?>]"><?php echo $value['factor']; ?></textarea>
												</td>
											</tr>
										<?php endforeach ?>
									</tbody>
								</table>
							</div>
								
						<?php endforeach ?>
					</div>
				</div>
			</div>

			<div class="col-md-12 table_wpr mb-40">
				<div class="horizontal-scrollable">
					<div class="row">
						<?php foreach ($fbdData['job'] as $jobData): ?>
							<?php $accloop ++; ?>
							<div class="col-md-6">
								
								<table class="table table-bordered" id="tbl_budget_forecast">
									<div class="head_style"> <?php echo $accloop.'. '.__('人員増減').' '.str_replace(' ', '', $jobData['year1_title']).' '.__(' 対 ').' '.str_replace(' ', '', $jobData['year2_title']);?></div>
									<div class="font_bold text-right"><?php echo '('.__('単位：人').')';?></div>
									<thead class="check_period_table">
										<tr>
											<th><?php echo __('職掌');?></th>
											<th><?php echo $jobData['year1_title'];?></th>
											<th><?php echo $jobData['year2_title'];?></th>
											<th><?php echo __('差異');?></th>
											<th><?php echo __('備考');?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($jobData['data'] as $field_name => $fvalue): ?>
											<tr>
												<?php $negative1 = ($fvalue['year1'] < 0) ? 'negative' : '' ?>
												<?php $negative2 = ($fvalue['year2'] < 0) ? 'negative' : '' ?>
												<?php $negative3 = ($fvalue['difference'] < 0) ? 'negative' : '' ?>
												<td class="tbl_first_col">
													<?php echo h($field_name);?>
												</td>
												<!-- Manpower table data -->
												<td class="tbl_md_col <?php echo($negative1) ?>">
													<?php echo($fvalue['year1']) ?>
												</td>
												<td class="tbl_md_col <?php echo($negative2) ?>">
													<?php echo($fvalue['year2']) ?>
												</td>
												<td class="tbl_md_col <?php echo($negative3) ?>">
													<?php echo($fvalue['difference']) ?>
												</td>
												<td class="tbl_lst_col">
													<textarea class="text_lst <?php echo 'tbl3'.'_col_'.$row_no.'up';?>" name="factors[job][<?php echo $field_name?>][<?php echo $fvalue['factor_year'];?>]"><?php echo($fvalue['factor']) ?></textarea>
												</td>
											</tr>
											
										<?php endforeach ?>
									</tbody>
								</table>
							</div>
						<?php endforeach ?>
					</div>
				</div>
			</div>
		</div>
	<?php else: ?>	
		<div class="row">
			<div class="col-sm-12">
				<div id='err' class="no-data">
				 Data does not exist in system.
				</div>
			</div>
		</div>
	<?php endif ?>

</div>

<?php echo $this->Form->end();?>

<script type="text/javascript">

	$(document).ready(function(){
		
		$('.datepicker').datepicker({
		    format: 'yyyy/mm/dd'
		});
		//to remove horizontal scroll
		$('body').css('overflow-x','hidden');	

		$('.tbl_md_col').each(function(index) { //keyup
			
			// skip for arrow keys
			if(event.which >= 37 && event.which <= 40) return;

				// format number
				$(this).text(function(index, value) {
					value = $.trim(value);					
					return value;
					
			});
		});

		

		/* download data as excel file */
		$('#btn_excel_download').click(function(){

			document.getElementById("error").innerHTML   = "";
			document.getElementById("success").innerHTML = "";
			document.forms[0].action = "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/downloadForecastBudget";
			document.forms[0].method = "POST";
			document.forms[0].submit();
			return true;

		});

		//for copy/paste of input field
		$('textarea').on('paste', function(e){
			var $this = $(this);
			var x = $this.closest('td').index(),
			y = $this.closest('tr').index(),
			obj = {};
			if (window.clipboardData && window.clipboardData.getData) { // IE

			v = window.clipboardData.getData('Text');
			$.each(v.split('\r\n'), function(i2, v2){
			var row = y+i2, col = 4;
			obj['cell-'+row+'-'+col] = v2;
			$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') textarea').val(v2).trigger('change');
			});

			return false;

			}else{
			//other browser
			$.each(e.originalEvent.clipboardData.items, function(i, v){
			if (v.type === 'text/plain'){
			v.getAsString(function(text){
				$.each(text.split('\r\n'), function(i2, v2){
					var row = y+i2, col = 4;
					obj['cell-'+row+'-'+col] = v2;
					$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') textarea').val(v2).trigger('change');
				});
			});
			  }
			});
			return false;
			}

			 });

		// $('#success').hide();
		// $('#error').hide();

		$('#btn_approve').hide();
		$('#btn_approve_cancel').hide();
		$('#btn_save').hide();
		var showApprove = '<?php echo $showApprove; ?>';
		var showCancelApprove = '<?php echo $showCancelApprove; ?>';
		var HQ_approve_Disable = '<?php echo $HQ_approve_Disable; ?>';
		var showSave = '<?php echo $showSave; ?>';
		if(showApprove) $('#btn_approve').show();
		if(showCancelApprove) {
			$('#btn_approve_cancel').show();
			<?php if($approveLimit!='true'){ ?>
				$('#btn_approve_cancel').prop('disabled',true);
			<?php } ?>
			if(HQ_approve_Disable=='true'){
				$('#btn_approve_cancel').prop('disabled',true);
			}
		}
		if(showSave) $('#btn_save').show();
		//hq approved in summary
		if(HQ_approve_Disable == 'true') {
			$('#btn_approve, #btn_save, label input, textarea').prop('disabled',true);
			$("label#btn_browse").css('background-color','#D5EADD !important');
			$("label#btn_browse").css('color','#fff !important');
			$("label#btn_browse").css('cursor','not-allowed');
			$("label#btn_browse").css('opacity','.65');
		} 

		//adding when approve cancel, not input enable
		//By HHK
		if($('#btn_approve_cancel').is(":visible")==true){
			$('textarea.text_lst').prop('readonly',true);
			$('#filling_date').unbind();
			$('#filling_date').prop('readonly',true);
			$('label').removeClass('required');
			$('.datepicker').removeClass('input-group');
			$('.input-group-addon').hide();
			$('textarea').css('background-color','#F9F9F9');
			$('label input').prop('disabled',true);
			$("label#btn_browse").css('background-color','#D5EADD !important');
			$("label#btn_browse").css('color','#fff !important');
			$("label#btn_browse").css('cursor','not-allowed');
			$("label#btn_browse").css('opacity','.65');
		}
		<?php if($createLimit!='true'){ ?>
			$('textarea.text_lst').prop('readonly',true);
			$('#filling_date').unbind();
			$('#filling_date').prop('readonly',true);
			$('label').removeClass('required');
			$('.datepicker').removeClass('input-group');
			$('.input-group-addon').hide();
			$('textarea').css('background-color','#F9F9F9');
			$('label input').prop('disabled',true);
			$("label#btn_browse").css('background-color','#D5EADD !important');
			$("label#btn_browse").css('color','#fff !important');
			$("label#btn_browse").css('cursor','not-allowed');
			$("label#btn_browse").css('opacity','.65');
		<?php } ?>

		'use strict';
	    $(".row").scroll(function(){
	        $(".row")
	            .scrollLeft($(this).scrollLeft());
	    });

	    /* to get scrollable width*/
	    var scroll = document.getElementById("scrollWidth");
	    $(".topScroll").width(scroll.scrollWidth);

	    if(<?php echo count($fbdData['acc']);?> <= 2 || <?php echo count($fbdData['job']);?> <= 2){
	    	$(".table_wpr").children().removeClass('horizontal-scrollable');
	    }

	    $(".uploadfile").change(function() {
			document.getElementById("success").innerHTML = "";
			document.getElementById("error").innerHTML   = "";    
			
			var file_name = $("#upload_file").prop('files')[0].name;
			var file_size = $("#upload_file").prop('files')[0].size;
			var fbdFile = document.getElementById('upload_file').files.length;

			
			var fbdChk = true;
						
			/* Check File Choose */		
			if(fbdFile != '1') {

				var newbr = document.createElement("div");                      
				var a     = document.getElementById("error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE024)));
				document.getElementById("error").appendChild(a);                      
				fbdChk = false;  

			} 

		 
			if(fbdChk) {   
				$.confirm({                     
					title: '<?php echo __('アップロード確認'); ?>',                                  
					icon: 'fas fa-exclamation-circle',                                  
					type: 'green',                                  
					typeAnimated: true, 
					closeIcon: true,
					columnClass: 'medium',                              
					animateFromElement: true,                                   
					animation: 'top',                                   
					draggable: false,                                
					content: "<?php echo __('ファイルをアップロードしてよろしいでしょうか。');?>",                                   
					buttons: {                                      
						ok: {                                   
							text: '<?php echo __("はい");?>',                                 
							btnClass: 'btn-info',                                   
							action: function(){   
									// $("#upd-file-name").html(file_name);
									// isOkClicked = true;          
									loadingPic();
									document.forms[0].action = "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/saveFBDFile";
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

			});
	   

			
		
	});

	
	

	// Author : Ei Thandar Kyaw on 07/08/2020
	// save data to budget summary table when click Approve button
	$("#btn_approve").click(function(){
		var errorFlag = true;
		/* to send data to controller, edited By Hein Htet Ko */
		$('#error').html('');
		var filling_date = $('#filling_date').val();
		if(filling_date==''){
			errorFlag = false;
			var newElement = '<div>'+errMsg(commonMsg.JSE001,['<?php echo __("提出日")?>'])+'</div>';
			$('#error').append(newElement);
			$('#success').hide();
			scrollText();

		}
  
		var budget_term = $('#budget_term').val();
		var ba_code = $('#ba_code').val();
		var deadline_date = $('#deadline_date').val();
		var filling_date = $('#filling_date').val();
		if(errorFlag){
			$.confirm({ 					
			title: '<?php echo __("承認の確認");?>',									
			icon: 'fas fa-exclamation-circle',									
			type: 'green',									
			typeAnimated: true,	
			closeIcon: true,
			columnClass: 'medium',								
			animateFromElement: true,									
			animation: 'top',									
			draggable: false,									
			content: "<?php echo __("全行を承認してよろしいですか。"); ?>",									
			buttons: {   									
			            ok: {									
			                text: '<?php echo __("はい");?>',									
			                btnClass: 'btn-info',									
			                action: function(){
								
								var layer_code = '<?php echo $this->Session->read('SESSION_LAYER_CODE'); ?>';
								
								var page = 'BrmForecastBudgetDifference';
								var func = 'approve';
								var data = [];
								
								$(".subject").text('');
								$(".body").html('');
								$("#mailSubj").val('');
								$("#mailBody").val(''); 
								$.ajax({
									type:'post',
									url: "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/getMailLists",
									data:{layer_code : layer_code, page: page, function: func},
									dataType: 'json',
									success: function(data) {
										$("#mailSubj").val(data.subject);
										$("#mailBody").val(data.body);
										var mailSend = (data.mailSend == '') ? '0' : data.mailSend;
										$("#mailSend").val(mailSend);
										var to = data.to;
										var cc = data.cc;
										console.log(data);
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
												document.forms[0].action = "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/approveFBDData";
												document.forms[0].method = "POST";
												document.forms[0].submit();
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
												
												$('#BrmForecastBudgetDifference').attr('method','post');
												$('#BrmForecastBudgetDifference').attr('action', "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/approveFBDData");
											}
										}else{
											loadingPic();
											document.forms[0].action = "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/approveFBDData";
											document.forms[0].method = "POST";
											document.forms[0].submit();
											
										}
					
									}
								});
				                // loadingPic(); 
			                	// document.forms[0].action = "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/approveFBDData";
								// document.forms[0].method = "POST";
								// document.forms[0].submit();									
				                		
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

	});

	/* Approve Cancel function */
	$("#btn_approve_cancel").click(function(){
			$.confirm({ 					
			title: '<?php echo __("承認の確認をキャンセル");?>',									
			icon: 'fas fa-exclamation-circle',									
			type: 'green',									
			typeAnimated: true,	
			closeIcon: true,
			columnClass: 'medium',								
			animateFromElement: true,									
			animation: 'top',									
			draggable: false,									
			content: "<?php echo __("全行を承認キャンセルしてよろしいですか。"); ?>",									
			buttons: {   									
			            ok: {									
			                text: '<?php echo __("はい");?>',									
			                btnClass: 'btn-info',									
			                action: function(){	
				                								
								// document.forms[0].action = "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/approveCancel";
								// document.forms[0].method = "POST";
								// document.forms[0].submit();		
								var layer_code = '<?php echo $this->Session->read('SESSION_LAYER_CODE'); ?>';
								
								var page = 'BrmForecastBudgetDifference';
								var func = 'approve_cancel';
								var data = [];
								
								$(".subject").text('');
								$(".body").html('');
								$("#mailSubj").val('');
								$("#mailBody").val(''); 
								$.ajax({
									type:'post',
									url: "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/getMailLists",
									data:{layer_code : layer_code, page: page, function: func},
									dataType: 'json',
									success: function(data) {
										$("#mailSubj").val(data.subject);
										$("#mailBody").val(data.body);
										var mailSend = (data.mailSend == '') ? '0' : data.mailSend;
										$("#mailSend").val(mailSend);
										var to = data.to;
										var cc = data.cc;
										console.log(data);
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
												document.forms[0].action = "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/approveCancel";
												document.forms[0].method = "POST";
												document.forms[0].submit();
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
												
												$('#BrmForecastBudgetDifference').attr('method','post');
												$('#BrmForecastBudgetDifference').attr('action', "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/approveCancel");
											}
										}else{
											loadingPic();
											document.forms[0].action = "<?php echo $this->webroot; ?>BrmForecastBudgetDifference/approveCancel";
											document.forms[0].method = "POST";
											document.forms[0].submit();
											
										}
					
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
	/* scroll text */
	function scrollText(){
      
		var tes  = $('#error').text();
		var tes1 = $('#success').text();
		if(tes){
			$("html, body").animate({ scrollTop: 0 }, 400);          
		}
		if(tes1){
			$("html, body").animate({ scrollTop: 0 }, 400);          
		}
  	}
	
</script>