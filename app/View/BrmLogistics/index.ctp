<style>
	.tbl_data_list td {
		vertical-align: middle;
		padding: 10px;
	}

	.yearpicker {
		z-index: 11000 !important;
	}

	.table-responsive {
		overflow-x: unset;
	}
	.align-right {
		text-align: right;
	}
	.popup_row {
		padding-bottom: 30px;
	}
	.search_year{
		display: inline-block;
		width: 200px;
		vertical-align: middle;
	}
	.tooltip-inner {
	    background: #ffd2d2;
	  	color: #ff3333;
	}
	.tooltip.top .tooltip-arrow {
	    border-top-color: #ffd2d2;
	}
	.jconfirm-box-container {
      margin-left: unset !important;
   }

</style>

<div id="overlay">
	<span class="loader"></span>
</div>

<div class='content register_container'>

 	<div class="row" style="font-size: 1em;">
 		<div class="col-md-12 col-sm-12 heading_line_title">
			<h3><?php echo __('取引管理'); ?></h3>
			<hr>
		</div>
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.UserSuccess"))? $this->Flash->render("UserSuccess") : '';?></div>						
			<div class="error" id="error"><?php echo ($this->Session->check("Message.UserError"))? $this->Flash->render("UserError") : '';?></div>
		</div>
		<?php echo $this->Form->create(false,array('type'=>'post')); ?>
		<div class="form-group row col-md-12">
			<div class="col-md-6">
				<label for="target_year" class="col-sm-4 col-form-label required">
				<?php echo __('年度');?>
				</label>
				<div class="col-sm-8">
					<div class="input-group date yearPicker" data-provide="yearPicker">
						<input type="text" class="form-control" id="target_year" name="target_year" value="" autocomplete="off" style="background-color: #fff;" readonly/>
						<span class="input-group-addon">
						<span class="glyphicon glyphicon-calendar" ></span>
						</span>
					</div>
					<input type="text" class="form-control year" style="display: none;" />
				</div>
			</div>
			<div class="col-md-6">
				<label for="ba_name" class="col-sm-4 col-form-label required">
				<?php echo __("部署名");?>
				<?php  if ($this->Session->read('Config.language') == 'eng') { echo "(ENG)";}else{echo "(JP)";}?>
				</label>
				<div class="col-sm-8">
					<select class="form-control" id="ba_name" name="ba_name" class="form-control">
						<option value="">---- <?php echo __("部署選択");?> ----</option>
						<?php 
						foreach($select_BA as $code => $ba_name): ?>
							<option value="<?php echo $code; ?>" >
							<?php
								echo h($ba_name);
							?>
							</option>
						<?php endforeach; ?>
					</select>
					<input type="hidden" id="hidden_ba" name="hidden_ba"/>
				</div>
			</div>
		</div>
		<div class="form-group row col-md-12">
			<div class="col-sm-6 col-md-6">
				<label for="index_no" class="col-sm-4 col-form-label">
					<?php echo __("取引コード");?>
				</label>
				<div class="col-sm-8">
					<input class="form-control" maxlength="12" type="text" id="index_no" name="index_no" value="" >
				</div>
			</div>
			<div class="col-sm-6 col-md-6">
				<label for="" class="col-sm-4 col-form-label required">
					<?php echo __("取引名");?>
				</label>
				<div class="col-sm-8">
					<input class="form-control" type="text" id="index_name" name="index_name" value="" >
				</div>
			</div>
		</div>
		<div class="form-group row col-md-12">
			<div class="col-sm-6 col-md-6">
				<label for="index_no" class="col-sm-4 col-form-label">
					<?php echo __("表示順"); ?>
				</label>
				<div class="col-sm-8">
					<input class="form-control" type="text" id="order" name="order" value="" maxlength="3">
				</div>
			</div>
		</div>
		<input type="hidden" name="logistic_id" id="logistic_id" value="">
		<input type="hidden" name="hid_btn" id="hid_btn" value="">
		<input type="hidden" name="hid_page_no" id="hid_page_no" class="txtbox" value ="">
		<div class="form-group row col-sm-12 justify-content-end">
			<input type="button" class="btn-save btn-success btn_sumisho" id="btn_save_edit" name="btn_save_edit"  value = "<?php echo __('保存');?>" style="margin-right: 2rem; margin-top: -1.5rem;">
		</div>
 	</div>
 	<br><br>

 	<?php 
 		if(!empty($this->request->query)) {
 			$srh_year = $this->request->query('year');
 			$srh_layer_code = $this->request->query('layer_code');
			$srh_layer_code = str_replace("@","&",$srh_layer_code);
			 
 		} else {
 			$srh_year = $year;
 			$srh_layer_code = $layer_code;
 		}
 	?>

 	<!-- show total row count -->
	<?php if(!empty($succmsg)) {?>
		<!-- search and copy -->
		<table width="100%">
				<tr>
					<td valign="bottom">
						<div class="pull-left msgfont" id="succc">
							<span>
								<?php echo ($succmsg);?>
							</span>							
						</div>
					</td>
					<td>
						<div class="form-group pull-right">
							<select class="form-control search_year" id="year" name="year" value="">

							<!-- select year -->
							<option value="">---- <?php echo __("年度選択");?> ---- </option>     

								<?php if($get_year): ?>
									<?php foreach ($get_year as $each_year): ?>
									<?php
										if(!empty($srh_year)){
											if($srh_year == $each_year){
												$select = 'selected';
											}else{
												$select = '';
											}
										}
									?>
										<option value="<?=$each_year?>" <?=$select?>>
											<?=$each_year?>								
										</option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
							
							<select class="form-control search_year" id="layer_code" name="layer_code" value="">

								<!-- select layer -->
								<option value="">---- <?php echo __("部署選択");?> ---- </option>
								<?php if($getba): ?>
									<?php foreach ($getba as $each_ba): ?>
										<?php
											$baCode = explode('/', $each_ba)[0];
											if(!empty($srh_layer_code)){
												if($srh_layer_code == $baCode){
													$select = 'selected';
												}else{
													$select = '';
												}
											}
										?>
										<option value="<?=$baCode?>" <?=$select?>>
											<?=$each_ba?>								
										</option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>	
							<input type="hidden" name="hidSearch" id="hidSearch" value="">
				 			<input type="button" class="btn btn-success btn_sumisho" value ="<?php echo __('検索');?>" name="search" onclick="SearchData();">
							<input type="button" data-target="#myModal" data-toggle="modal" data-backdrop="static" data-keyboard="false"  class="btn btn-success btn_sumisho" value ="<?php echo __('コピー');?>" name="copy" id="copy" onclick="popupscreen();" disabled>
		 				</div>
					</td>
				</tr>
		</table>
	<?php }else if(!empty($errmsg)) {?>
	<div id="err" class="no-data"> <?php echo ($errmsg); ?></div>
	<?php }?>

	<?php if(!empty($log_data)) { ?>
 	<div class="row">
		<div class="col-sm-12" style="margin-top: 10px">
			<div class="table-responsive tbl-wrapperd">
				<table class="table table-striped table-bordered tbl_sumisho_inventory tbl_data_list" id="logistic_tbl">
					<thead>
						<tr>
							<th width="150px"><?php echo __("年度"); ?></th>
							<th width="150px"><?php echo __("部署コード"); ?></th>
							<th width="300px"><?php echo __("部署名");?></th>
							<th width="150px"><?php echo __("取引コード"); ?></th>
							<th width="200px"><?php echo __("取引名"); ?></th>
							<th width="100px"><?php echo __("表示順"); ?></th>
							<th width="150px" colspan="2"><?php echo __("アクション"); ?></th>
						</tr>
					</thead> 
					<tbody>
						<?php foreach ($log_data as $logistic) :

							$logistic_id 	= $logistic['id'];
							$year 			= $logistic['year'];
							$index_no 		= $logistic['index_no'];
							$index_name 	= $logistic['index_name'];
							$layer_code 	= $logistic['layer_code'];
							$ba_name 		= $logistic['ba_name'];
							$order 			= $logistic['logistic_order'];
						?>
						<tr style="text-align: left;">
							<td><?php echo h($year); ?></td>
							<td><?php echo $layer_code; ?></td>
							<td style="word-break: break-all;"><?php echo $ba_name; ?></td>
							<td><?php echo h($index_no); ?></td>
							<td><?php echo $index_name; ?></td>
							<td><?php echo h($order); ?></td>
							<td width="120px" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
								<a class="" href="#" onclick="clickEdit(<?php echo $logistic_id; ?>)" title='<?php echo __("編集");?>'><i class="fa-regular fa-pen-to-square"></i>
								</a>
							</td>
							<td width="120px" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
								<a class="delete_link" href="#" onclick="clickDelete(<?php echo $logistic_id; ?>)" title='<?php echo __("削除");?>'><i class="fa-regular fa-trash-can"></i></a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php } ?>

	<div class="row" style="clear:both;margin: 40px 0px;">
		<?php if($count>50) {?>
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
 	<!-- PopUpBox  -->
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content contantbond">
				<div class="modal-header">
					<button type="button" class="close" id="clearData" data-dismiss="modal">&times;</button>
					<h3 class="modal-title"><?php echo __("コピー取引"); ?></h3>
				  </div>
				  <div class="modal-body">
						<!-- success,error -->
						<div class="success" id="popupsuccess"></div>            
						<div class="error" id="popuperror"></div>
						<!-- end success,error -->
						<div class="table-responsive modal_tbl_wrapper">
						  <div class = "form-group popup_row">
							<label class="col-md-5 control-label rep_lbl"><?php echo __("コピー元（年）"); ?></label>
							<div class="col-sm-7 datepicker-years">
							 	<input type="text" class="form-control" id="from_year" value="" name="from_year" disabled>
							 	<input type="hidden" name="hid_from_year" value="<?=$year?>">
							</div>
						  </div>
						  <div class = "form-group popup_row">
							<label class="col-md-5 control-label rep_lbl"><?php echo __("コピー元（部署）"); ?></label>
							<div class="col-sm-7 datepicker-years">
							 	<input type="text" class="form-control" id="from_ba" value="" name="from_ba" disabled>
							 	<input type="hidden" name="hid_from_code" value="<?=$layer_code?>">
							</div>
						  </div>

						  <div class = "form-group popup_row">
							<label class="col-md-5 control-label rep_lbl required"><?php echo __("コピー先（年）"); ?></label>
							<div class="col-sm-7">
								<select class="form-control" id="to_year" name="to_year" value="">
									<!-- select year -->
									<option value="">---- <?php echo __("年度選択");?> ---- </option> 
									<?php foreach ($copy_year_datas as $copy_year): ?>
										<option value="<?=$copy_year?>">
											<?=$copy_year?>								
										</option>
									<?php endforeach; ?>
								</select>	
							</div>					
						  </div>
						  <div class = "form-group popup_row">
							<label class="col-md-5 control-label rep_lbl required"><?php echo __("コピー先（部署）"); ?></label>
							<div class="col-sm-7">
								<select class="form-control" id="to_ba" name="to_ba" value="">
									<!-- select layer -->
									<option value="">---- <?php echo __("部署選択");?> ---- </option>
									<?php foreach ($select_BA as $copy_layer_code => $copy_ba): ?>
										<option value="<?=$copy_layer_code?>">
											<?=$copy_ba?>								
										</option>
									<?php endforeach; ?>
								</select>	
							</div>					
						  </div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="row col-sm-12 justify-content-end">
				      		<button type="button" id="copy" onclick="CopyData()" class="btn btn-success btn_sumisho"><?php echo __('追加');?> </button>
				      		<button type="button" id="overwrite" onclick="OverwriteData()" class="btn btn-success btn_sumisho" style="display: none;margin-bottom: auto;"><?php echo __('上書き');?> </button>
			      		</div>
		      		</div>
			</div>
		</div>
	</div> 
	<!-- end popup -->
 <?php echo $this->Form->end(); ?>
 <script>

 	$('document').ready(function(){
		document.getElementById("hidSearch").value = "SEARCHALL";

		/* float table header */
		if($('#logistic_tbl').length > 0) {
			var $table = $('#logistic_tbl');
			$table.floatThead({
			    responsiveContainer: function($table){
			        return $table.closest('.table-responsive');
			    }
			});
		}

		$("#year").change(function() {
			$("#layer_code").empty();
			$("#error").empty();
            $("#success").empty();
            var target_year = $("#year").val();
            if(target_year != "") {
                getBAcode(target_year);
            }else{
				html = "<option value=''>---- <?php echo __("部署選択");?> ----</option>";
				$("#layer_code").html(html);
            }
		});

		$("#layer_code").focus(function() {

			$("#error").empty();
        	$("#success").empty();
        	var target_year = $("#year").val();
        	var err_msg = '';
        	if(target_year == "") {
        		// $("#error").html(errMsg(commonMsg.JSE002,['<?php echo __("年度"); ?>']));
        		// $("#error").append(err_msg);
        		// $("html, body").animate({ scrollTop: 30 }, "fast");
        		// $("#layer_code").html(html);
        		// $("#layer_code option:selected").attr('disabled','disabled');
        		$("#layer_code").tooltip({
        			trigger: "focus",
        			placement: "top",
        			template: '<div class="tooltip tooltip-error" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
        		}).attr("data-original-title", errMsg(commonMsg.JSE002,['<?php echo __("年度")?>']));
        		$("#layer_code").tooltip("show");
        	}else $("#layer_code").tooltip("destroy");

		});

		function getBAcode(target) {//console.log(target)
			$.ajax({
		        type: "POST",
		        url: "<?php echo $this->webroot; ?>BrmLogistics/getBAcode",
		        data: {target_year:target},
		        dataType: 'json', 
				beforeSend: function() {
        loadingPic();
    }, 

		        success: function(data) {
					var html;
				//	select layer
					html = "<option value=''>---- <?php echo __("部署選択");?> ----</option>";
					$.each(data, function(layer_code,ba_name) {
	    				
	    				html += "<option style='font-size: 14px;' value='"+ba_name+"'>"+ba_name+"</option>";

					});
					$("#layer_code").html(html);
					$('#overlay').hide();

	            }
	     	});
		}

	    $('#to_ba, #to_year').change(function(){

	    	var to_year = $("#to_year").val();
			var to_ba = $("#to_ba").val();

			if(to_year != "" && to_ba != ""){

				$.ajax({
					type: "POST",
					url: "<?php echo $this->webroot; ?>BrmLogistics/getOverwrite",
					data: {to_year : to_year, to_ba : to_ba },
					dataType: 'json', 
					beforeSend: function() {
        loadingPic();
    }, 

					success: function(data) {

						if(data){

							$("#overwrite").show();

						}else $("#overwrite").hide();
						$('#overlay').hide();

					}
				});
			}else $("#overwrite").hide();

	    });

		var copy_btn_mode = "<?php echo $btn_copy_mode; ?>";
		if(copy_btn_mode) {
			$('#copy').prop('disabled',false); 
		}
	});

 	$("#btn_save_edit").click(function(){

		document.getElementById("error").innerHTML   = "";											
		document.getElementById("success").innerHTML = "";

		var target_year = document.getElementById("target_year").value;

		var ba_name = document.getElementById("ba_name").value;

		var index_no = document.getElementById("index_no").value;

		var index_name = $.trim(document.getElementById("index_name").value);

		var order = document.getElementById("order").value;

		var button = document.getElementById("btn_save_edit").value;

		var logistic_id = document.getElementById("logistic_id").value;
		
		var chk = true;	

		$('#hid_btn').val(button);

 		if (button == '保存' || button == 'Save') {
 			
			if(!checkNullOrBlank(target_year)) {												
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("年度")?>'])));											
				document.getElementById("error").appendChild(a);											
				chk = false;

			}else{

				if (!/^[1-9]{1}[0-9]{3}$/.test(target_year)) {

					var newbr = document.createElement("div");											
					var a = document.getElementById("error").appendChild(newbr);											
					a.appendChild(document.createTextNode(errMsg(commonMsg.JSE044,['<?php echo __("年度")?>'])));											
					document.getElementById("error").appendChild(a);											
					chk = false;

				}
			}

			if(!checkNullOrBlank(ba_name)) {											
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("部署名")?>'])));											
				document.getElementById("error").appendChild(a);											
				chk = false;											
			}

			if(!checkNullOrBlank(index_name)) {													
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("取引名")?>'])));											
				document.getElementById("error").appendChild(a);											
				chk = false;											
			}
			
			if(checkSpecialChar(index_name[0])) {													
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE019,['<?php echo __("最初の文字")?>'])));											
				document.getElementById("error").appendChild(a);											
				chk = false;											
			}
 		}

		var path = window.location.pathname;
		var page = path.split("/").pop();
		if (page.indexOf("page:") !== -1) {
			document.getElementById('hid_page_no').value = page; 
		}
		if(chk) {	
			$.confirm({ 					
				title: '<?php echo __("保存確認");?>',									
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
									var logistic_order = '';
									<?php 
									foreach ($logistic_data as $data): 
										?>
									var year = "<?php echo $data['BrmLogistic']['target_year'];?>";
									var layer_code = "<?php echo $data['BrmLogistic']['layer_code'];?>";
									var logistic_index_name = "<?php echo $data['BrmLogistic']['index_name'];?>";
									if(target_year == year && ba_name == layer_code && index_name == logistic_index_name){
										logistic_order = "<?php echo $data['BrmLogistic']['logistic_order'];?>";
									}
									<?php endforeach ?>
									if(order == '' || order == logistic_order || logistic_order == 0 || logistic_order == 1000){
										loadingPic();
										document.forms[0].action = "<?php echo $this->webroot; ?>BrmLogistics/saveAndEditLogistic";
										document.forms[0].method = "POST";
										document.forms[0].submit();
										return true;
									}else{
										$.confirm({ 					
											title: "<?php echo __('警告メッセージ'); ?>",
											icon: 'fas fa-exclamation-circle',
											type: 'orange',
											typeAnimated: true,
											closeIcon: true,
											columnClass: 'medium',
											animateFromElement: true,
											animation: 'top',
											draggable: false,  
											content: "<?php echo __("この表示順に変更してもよろしいですか？ 現在の順番 : "); ?>"+logistic_order,									
											buttons: {   									
														ok: {									
															text: '<?php echo __("はい");?>',									
															btnClass: 'btn-info',									
															action: function(){
																loadingPic();																									
																document.forms[0].action = "<?php echo $this->webroot; ?>BrmLogistics/saveAndEditLogistic";
																document.forms[0].method = "POST";
																document.forms[0].submit();
																return true;	
															}									
														},     									
														cancel : {									
																text: '<?php echo __("いいえ");?>',									
																btnClass: 'btn-default',									
																action: function(){									
																//console.log('the user clicked cancel');	
																loadingPic();
																$('#order').val(logistic_order);
																document.forms[0].action = "<?php echo $this->webroot; ?>BrmLogistics/saveAndEditLogistic";
																document.forms[0].method = "POST";
																document.forms[0].submit();
																return true;								
																}

															}									
													},									
											theme: 'material',									
											animation: 'rotateYR',									
											closeAnimation: 'rotateXR'									
										}); 
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

		scrollText();

	});

	function clickEdit(id){

		document.getElementById("error").innerHTML   = '';
		document.getElementById("success").innerHTML   = '';

		$('#btn_save_edit').val('<?php echo __('変更');?>');

		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>BrmLogistics/editDeletLogistic",
			data: {id : id},
			dataType: 'json', 
			beforeSend: function() {
        loadingPic();
    }, 

			success: function(data) {

					var logistic_id = data['id'];
					var year = data['target_year'];
					var logistic_index_no = data['index_no'];
					var logistic_index_name = data['index_name'];
					var layer_code = data['ba_name'];
					var order = (data['logistic_order'] != 1000) ? data['logistic_order'] : '';

				if (data['log_check'] == 1) {

					$("#logistic_id").val(logistic_id);
					$('#target_year').val(year);
					$('.year').hide();
					$(".yearPicker").show();
					$('#index_no').val(logistic_index_no);
					$('#index_name').val(logistic_index_name);
					$("#index_name").prop("readonly", false);
					$('#ba_name option[value="'+layer_code+'"]').prop('selected', true);
					$("#ba_name").removeAttr("disabled");
					$('#hidden_ba').val(layer_code);
					$("#order").val(order);

				}else{

					$("#logistic_id").val(logistic_id);
					$('#target_year').val(year);
					$('.year').val(year);
					$(".year").prop("readonly", true);
					$(".year").show();
					$(".yearPicker").hide();
					$(".input-group-addon").addClass("unclickable-span");
					$('#index_no').val(logistic_index_no);
					$('#index_name').val(logistic_index_name);
					$("#index_name").prop("readonly", true);
					$('#ba_name option[value="'+layer_code+'"]').prop('selected', true);
					$('#ba_name').attr('disabled','disabled');
					$('#hidden_ba').val(layer_code);
					$("#order").val(order);
				}
				$('#overlay').hide();
			}
		});


	}

	function clickDelete(id){

		document.getElementById("error").innerHTML   = '';
        document.getElementById("success").innerHTML   = '';

        document.getElementById("logistic_id").value = id;

        var path = window.location.pathname;
    	var page = path.split("/").pop();
    	if (page.indexOf("page:") !== -1) {

    		document.getElementById('hid_page_no').value = page; 
    	}
    	var btn_mode = 'Delete';
    	$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>BrmLogistics/editDeletLogistic",
			data: {id : id, btn_mode : btn_mode},
			dataType: 'json', 
			beforeSend: function() {
        loadingPic();
    }, 

			success: function(data) {

				if(data['log_check'] == 1){

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
						                loadingPic(); 									
						                document.forms[0].action = "<?php echo $this->webroot; ?>BrmLogistics/deleteLogistic";
			    						document.forms[0].method = "POST";
			    						document.forms[0].submit();    
			    						        							
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
			
					scrollText();
				}else{

					$("#error").html(errMsg(commonMsg.JSE059));
				}
				$('#overlay').hide();

			}
		});

	}

	function SearchData(){

		document.getElementById("error").innerHTML   = '';
        document.getElementById("success").innerHTML   = '';

		var layer_code = document.getElementById('layer_code').value;
		var year = document.getElementById('year').value;
		var chk = true;
		
		if(layer_code == '' && year == '')document.getElementById("hidSearch").value = "SEARCHALL";
		else document.getElementById("hidSearch").value = "";
		if(year != "") {

			if(!checkNullOrBlank(layer_code)) {	

				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("部署コード")?>'])));											
				document.getElementById("error").appendChild(a);
				$("html, body").animate({ scrollTop: 30 }, "fast");											
				chk = false;											
			}
		}

		if(chk) {

		   	 document.forms[0].action = "<?php echo $this->webroot; ?>BrmLogistics/index";
		     document.forms[0].method = "POST";
		     document.forms[0].submit();
			 $("html, body").animate({ scrollTop: 10 }, "fast");	  
			 return true;
		}
		
	}

	function popupscreen(){
		var year = document.getElementById('year').value;
		var layer_code = document.getElementById('layer_code').value;

		$('#to_year').val('');
		$('#to_ba').val('');
		$('#popuperror').hide();

		if(year != ''){
			$('#from_year').val(year);
			$('#hid_from_year').val(year);
			$('#from_ba').val(layer_code);
		}

	}

	function CopyData(){
		$('#popuperror').show();
		var from_year = document.getElementById('from_year').value;
		var from_ba = document.getElementById('from_ba').value;
		var to_year = document.getElementById('to_year').value;
		var to_ba = document.getElementById('to_ba').value;


		document.getElementById("popupsuccess").innerHTML   = "";	
		document.getElementById("popuperror").innerHTML = "";
		var chk = true;		

		if(!checkNullOrBlank(to_year)) {											
			var newbr = document.createElement("div");	
			var a = document.getElementById("popuperror").appendChild(newbr);											
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("年度")?>'])));					
			document.getElementById("popuperror").appendChild(a);		
			chk = false;										
		}
		if(!checkNullOrBlank(to_ba)) {											
			var newbr = document.createElement("div");	
			var a = document.getElementById("popuperror").appendChild(newbr);											
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("部署コード")?>'])));					
			document.getElementById("popuperror").appendChild(a);		
			chk = false;										
		}
		//year value and copy_year value is same
		if(checkNullOrBlank(to_year) && checkNullOrBlank(to_ba)){
			if(from_year == to_year && from_ba == to_ba){
				var newbr = document.createElement("div");	
				var a = document.getElementById("popuperror").appendChild(newbr);									
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE060,['<?php echo __("コピーするデータは同じであってはなりません")?>'])));
				document.getElementById("popuperror").appendChild(a);		
				chk = false;				
			}			
		}

		if(chk){
			$("#popuperror").css("display","none");
			$.confirm({ 					
				title: '<?php echo __("コピー確認");?>',									
				icon: 'fas fa-exclamation-circle',									
				type: 'blue',									
				typeAnimated: true,	
				closeIcon: true,
				columnClass: 'medium',								
				animateFromElement: true,									
				animation: 'top',									
				draggable: false,									
				content: "<?php echo __("データをコピーしてよろしいですか。"); ?>",									
				buttons: {   									
							ok: {									
								text: '<?php echo __("OK");?>',									
								btnClass: 'btn-info',									
								action: function(){	
								loadingPic(); 									
								document.forms[0].action = "<?php echo $this->webroot; ?>BrmLogistics/CopyLogistic";
								document.forms[0].method = "POST";
								document.forms[0].submit();    
																	
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

	}

	function OverwriteData(){
		var from_year = document.getElementById('from_year').value;
		var from_ba = document.getElementById('from_ba').value;
		var to_year = document.getElementById('to_year').value;
		var to_ba = document.getElementById('to_ba').value;

		document.getElementById("popupsuccess").innerHTML   = "";	
		document.getElementById("popuperror").innerHTML = "";
		var chk = true;	
		//year value and copy_year value is same
		if(checkNullOrBlank(to_year) && checkNullOrBlank(to_ba)){
			if(from_year == to_year && from_ba == to_ba){
				$('#popuperror').show();
				var newbr = document.createElement("div");	
				var a = document.getElementById("popuperror").appendChild(newbr);									
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE060,['<?php echo __("コピーするデータは同じであってはなりません")?>'])));
				document.getElementById("popuperror").appendChild(a);		
				chk = false;				
			}			
		}
		if(chk){
			$.ajax({
				type: "POST",
				url: "<?php echo $this->webroot; ?>BrmLogistics/existedCode",
				data: {to_year : to_year, to_ba : to_ba},
				dataType: 'json', 
				beforeSend: function() {
        loadingPic();
    }, 

				success: function(data) {

					chk = data['chk_log'];
					var err_msg = "";

					if(chk){

						trade_name = data['arr_code'].toString();
						$("#popuperror").show();
						err_msg += errMsg(commonMsg.JSE058,[trade_name])+"<br/>";
						$("#popuperror").append(err_msg);

					}else{

						$.confirm({ 					
							title: '<?php echo __("上書き確認");?>',			
							icon: 'fas fa-exclamation-circle',									
							type: 'orange',									
							typeAnimated: true,	
							closeIcon: true,
							columnClass: 'medium',								
							animateFromElement: true,									
							animation: 'top',									
							draggable: false,									
							content: "<?php echo __("上書きしますか？"); ?>",									
							buttons: {   									
										ok: {									
											text: '<?php echo __("はい");?>',									
											btnClass: 'btn-info',									
											action: function(){	
											loadingPic(); 									
											document.forms[0].action = "<?php echo $this->webroot; ?>BrmLogistics/OverwriteLogistic";
											document.forms[0].method = "POST";
											document.forms[0].submit();    
																				
											return true;						
											}									
										},    									
										cancel : {									
												text: '<?php echo __("いいえ");?>',									
												btnClass: 'btn-default',									
												cancel: function(){									
												scrollText();								
												}

											}									
									},									
							theme: 'material',									
							animation: 'rotateYR',									
							closeAnimation: 'rotateXR'									
						}); 
					}
					$('#overlay').hide();
					
				}
			});
		}

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

	$(function() { 
		$("input[name='target_year']").on('input', function(e) { 
			$(this).val($(this).val().replace(/[^0-9]/g, '')); 
		}); 
	});
	$(function() { 
		$("input[name='order']").on('input', function(e) { 
			$(this).val($(this).val().replace(/[^0-9]/g, '')); 
			if($(this).val() == 0) $(this).val('');
		}); 
	});
	// $(function() { 
	// 	$("input[name='index_no']").on('input', function(e) { 
	// 		indexNo = $(this).val();
	// 		regEx = /[一-龠]+|[ぁ-ゔ]+|[ァ-ヴー]+[々〆〤]+/;
	// 		// (/^[\.a-zA-Z0-9,~`!@#$%\^&*()+=\-\[\]\\';,/{}|\\":<>\? ]*$/.test(indexNo))? $(this).val(indexNo) : $(this).val("");
	// 		(regEx.test(indexNo))? $(this).val("") : $(this).val(indexNo);
	// 	}); 
	// });
	 
 </script>