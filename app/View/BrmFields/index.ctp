<?php
echo $this->Form->create(false,array('type'=>'post', 'id' => 'BrmFields', 'name'=> 'Field', 'enctype'=> 'multipart/form-data'));
?>
<style>
	body {
		font-family: KozGoPro-Regular;
	}
	.yearPicker {
	  	z-index: 1999 !important;
	}
	.numbers {
		text-align: right !important;
	}
	.jconfirm-box-container {
      margin-left: unset !important;
   }
   .btn_sumisho {
	margin-right: 1.5rem;
   }
	
</style>
<script>
	$(document).ready(function() {
		/* float table header */
		if($('#tbl_field').length > 0) {
			var $table = $('#tbl_field');
			$table.floatThead({
			    responsiveContainer: function($table){
			        return $table.closest('.table-responsive');
			    }
			});
		}
		
	});

	/* year is greater than start year and year is smaller than end year */
	function checkTermAndYear(term,year){
		var term = term.split('~');
		var start = term[0];
		var end = term[1];
		if(year<start || year>end) {
			return false;
		}
		return true;
	}
	/* allow 6 digit and 2 decimal point */
	function isDigitNoAndDecimalNo(value){
	    var decimalOnly =/^\s*(\d{1,4})(\.\d{0,2})?\s*$/;
	    if(decimalOnly.test(value)) {
	        return true;
	    }
	    return false;
	}

	function click_SaveField() {
		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = "";
		var target_year = document.getElementById("target_year").value;
		var field_name_jp = document.getElementById("field_name_jp").value;
		var overtime_rate = document.getElementById("overtime_rate").value;
		var chk = true;		
		if(!checkNullOrBlank(target_year)) {			
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("年度")?>'])));	
			document.getElementById("error").appendChild(a);				
			chk = false;											
		}
		if(!checkNullOrBlank(field_name_jp)) {					
			var newbr = document.createElement("div");						
			var a = document.getElementById("error").appendChild(newbr);	
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("職務名（JP）")?>'])));
			document.getElementById("error").appendChild(a);				
			chk = false;											
		}
		if(checkNullOrBlank(overtime_rate)) {					
			if(!isDigitNoAndDecimalNo(overtime_rate)) {
				var newbr = document.createElement("div");						
				var a = document.getElementById("error").appendChild(newbr);	
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE048,['<?php echo __("Overtime Rate")?>'])));
				document.getElementById("error").appendChild(a);				
				chk = false;											
			}
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
		                loadingPic();
		                document.forms[0].action = "<?php echo $this->webroot; ?>BrmFields/saveFieldData";
						document.forms[0].method = "POST";
						document.forms[0].submit();    
						scrollText();         							
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

	function Click_EditField(id){
		document.getElementById("error").innerHTML   = '';
		document.getElementById("success").innerHTML   = '';
	    $.ajax({
	        type: "POST",
	        url: "<?php echo $this->webroot; ?>BrmFields/editFieldData",
	        data: {id:id},
	        dataType: 'json',
			beforeSend: function() {
				loadingPic();
			}, 
	        success: function(data) {  
	        	var field_id = data['field_id'];
	        	var target_year = data['target_year'];
	        	var field_name_jp = data['field_name_jp'];
	        	var field_name_en = data['field_name_en'];
	        	var overtime_rate = data['overtime_rate'];
	        	
	        	$("#hid_updateId").val(field_id);
	        	$("#target_year").val(target_year);
	        	$("#field_name_jp").val(field_name_jp);
	        	$("#field_name_en").val(field_name_en);
	        	$("#overtime_rate").val(overtime_rate);
	        	$("#btn_save").hide();
        		$("#btn_update").show();
				$('#overlay').hide();
            }
     	});	
	}

	function click_UpdateField() {
		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = "";
		var target_year = document.getElementById("target_year").value;
		var field_name_jp = document.getElementById("field_name_jp").value;
		var overtime_rate = document.getElementById("overtime_rate").value;
		var path = window.location.pathname;
	    var page = path.split("/").pop();
	    document.getElementById('hid_page_no').value = page;
		var chk = true;													
		if(!checkNullOrBlank(target_year)) {			
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("年度")?>'])));	
			document.getElementById("error").appendChild(a);				
			chk = false;											
		}
		if(!checkNullOrBlank(field_name_jp)) {					
			var newbr = document.createElement("div");						
			var a = document.getElementById("error").appendChild(newbr);	
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("職務名（JP）")?>'])));
			document.getElementById("error").appendChild(a);				
			chk = false;											
		}
		
		if(checkNullOrBlank(overtime_rate)) {					
			if(!isDigitNoAndDecimalNo(overtime_rate)) {
				var newbr = document.createElement("div");						
				var a = document.getElementById("error").appendChild(newbr);	
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE048,['<?php echo __("Overtime Rate")?>'])));
				document.getElementById("error").appendChild(a);				
				chk = false;											
			}
		}
		
		if(chk) {											
			$.confirm({										
				title: '<?php echo __("変更確認");?>',			
				icon: 'fas fa-exclamation-circle',		
				type: 'blue',									
				typeAnimated: true,			
				closeIcon: true,
				columnClass: 'medium',						
				animateFromElement: true,									
				animation: 'top',									
				draggable: false,									
				content: "<?php echo __("データを変更してよろしいですか。"); ?>",	
				buttons:{   									
		            ok: {									
		                text: '<?php echo __("はい");?>',
		                btnClass: 'btn-info',
		                action: function(){	
		                	loadingPic(); 								
			                document.forms[0].action = "<?php echo $this->webroot; ?>BrmFields/updateFieldData";
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
	            closeAnimation: 'rotateXR',

			});
		} 
		scrollText();											
	}

	function Click_DeleteField(id){
		document.getElementById("error").innerHTML   = '';
		document.getElementById("success").innerHTML   = '';
		document.getElementById("hid_deleteId").value = id;	
		var path = window.location.pathname;
	    var page = path.split("/").pop();
		document.getElementById('hid_page_no').value = page;				
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
                		document.forms[0].action = "<?php echo $this->webroot; ?>BrmFields/deleteFieldData";
						document.forms[0].method = "POST";
						document.forms[0].submit();
						return true;
                    }										
 				},     										
				cancel : {										
                    text: '<?php echo __("いいえ");?>',				
                    btnClass: 'btn-default',		
                    cancel: function(){										
                   // console.log('the user clicked cancel');	
                    scrollText();									
                    }										
                }										
			},										
            theme: 'material',										
            animation: 'rotateYR',										
            closeAnimation: 'rotateXR'										
		});                   											
		scrollText();	
	}

    /*  
	*	Show hide loading overlay
	*	@Zeyar Min  
	*/
	function loadingPic() { 
		$("#overlay").show();
		$('.jconfirm').hide();  
	}
	
	function scrollText(){
    	var tes = $('#error').text();
    	var tes1 = $('.success').text();
		if(tes){
			$("html, body").animate({ scrollTop: 30 }, "fast");				
		}
		if(tes1){
			$("html, body").animate({ scrollTop: 30 }, "fast");				
		}
   	}
</script>
<div id="overlay">
	<span class="loader"></span>
</div>
<div class="content register_container">
	<div class="row" style="font-size: 1em;">
		<div class="col-md-12 col-sm-12 heading_line_title">
	    	<h3><?php echo __('フィールド管理');?></h3>
	    	<hr>
	    </div>
		<!-- for delete -->
		<input type="hidden" name="hid_deleteId" id="hid_deleteId">
		<!-- for update -->
		<input type="hidden" name="hid_updateId" id="hid_updateId">
		<!-- for selected text(term_name) -->
		<input type="hidden" name=selected_data id="selected_data">
		<!-- for page no. -->
		<input type="hidden" name=hid_page_no id="hid_page_no">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.SuccessMsg"))? $this->Flash->render("SuccessMsg") : '';?><?php echo ($this->Session->check("Message.FieldOK"))? $this->Flash->render("FieldOK") : '';?></div>						
			<div class="error" id="error"><?php echo ($this->Session->check("Message.ErrorMsg"))? $this->Flash->render("ErrorMsg") : '';?><?php echo ($this->Session->check("Message.FieldFail"))? $this->Flash->render("FieldFail") : '';?></div>								
		</div>
		<div class="form-group row col-md-12">
			<div class="col-md-6">
				<label for="target_year" class="col-sm-4 col-form-label required">
					<?php echo __('年度選択');?>
				</label>
				<div class="col-sm-8">
					<div class="input-group date yearPicker" data-provide="yearPicker" style="padding: 0px;">
					 	<input type="text" class="form-control target" id="target_year" name="target_year" value="" autocomplete="off" >
					 	<span class="input-group-addon">
							<span class="glyphicon glyphicon-calendar"></span>
						</span>
					</div>
				</div>
			</div>
			
			<div class="col-sm-6">
				<label for="field_name_en" class="col-sm-4 col-form-label">
					<?php echo __('職務名（EN）');?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="field_name_en" name="field_name_en" value="" maxlength="250"/>
				</div>
			</div>
		</div>

		<div class="form-group row col-md-12">
			
			<div class="col-md-6">
				<label for="field_name_jp" class="col-sm-4 col-form-label required">
					<?php echo __('職務名（JP）');?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="field_name_jp" name="field_name_jp" value="" maxlength="500"/>
				</div>
			</div>
			<div class="col-md-6">
				<label for="overtime_rate" class="col-sm-4 col-form-label">
					<?php echo __('残業代');?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="overtime_rate" name="overtime_rate" value=""/>
				</div>
			</div>
		</div>

		<div class="form-group row col-md-12" style="margin-bottom: 40px;">
			<div class="col-md-12 " style="text-align: end;" id="save">
			 	<input type="button" class="btn-save btn-success btn_sumisho" id="btn_save" name="btn_save"  value = "<?php echo __('保存');?>" onclick = "click_SaveField();">
			</div>
			<div class="col-md-12 " style="text-align: end;" id="update">
			 	<input type="button" class="btn-save btn-success btn_sumisho" id="btn_update" name="btn_save" style="display: none;" value = "<?php echo __('変更');?>" onclick = "click_UpdateField();">
			</div>
		</div>

		<?php if(!empty($succmsg)) {?>
			<div class="msgfont" id="succc"><?php echo ($succmsg);?></div>	
		<?php }else if(!empty($errmsg)) {?>
			<div class="row col-md-12  d-flex justify-content-center">
				<div></div>
				<div id="err" class="no-data center"> <?php echo ($errmsg); ?></div>
				<div></div>
			</div>
		
		<?php }?>
	
		<?php if($rowCount != 0) { ?>	
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">
				<div class="table-responsive tbl-wrapperd">
					<table class="table table-striped table-bordered acc_review" id="tbl_field" style="margin-top:10px;width: 100%;">
				        <thead class="check_period_table">
				            <tr>
				                <th><?php echo __("Year"); ?></th>
				                <th><?php echo __("Field Name (JP)"); ?></th>
				                <th><?php echo __("Field Name (EN)"); ?></th>
				                <th><?php echo __("Overtime Rate"); ?></th>
				                <th colspan="2"><?php echo __("Action"); ?></th>
				            </tr>
				        </thead>
		        		<tbody>
				            <?php if(!empty($list)) foreach($list as $datas) {
				            	$id = $datas['BrmField']['id'];
				            	$term_id = $datas['BrmField']['term_id'];
				            	$term_name = $datas['TermModel']['budget_year'].'~'.$datas['TermModel']['budget_end_year'];
				            	$target_year = $datas['BrmField']['target_year'];
				            	$field_name_jp = $datas['BrmField']['field_name_jp'];
				            	$field_name_en = $datas['BrmField']['field_name_en'];
				            	$overtime_rate = $datas['BrmField']['overtime_rate'];
				            	$flag = $datas['BrmField']['flag'];
				               	if($flag !=0 ) { ?>                    
				            <tr style="text-align: left;">
				                <td><?php echo $target_year; ?></td>
				                <td><?php echo $field_name_jp; ?></td>  
				                <td><?php echo $field_name_en; ?></td>
				                <td class="numbers"><?php echo number_format($overtime_rate,2); ?></td>
				                <td width="100px" class="link" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">              
				                    <a class="" href="#" onclick="Click_EditField('<?php echo $id;?>');" title="<?php echo __('編集');?>"><i class="fa-regular fa-pen-to-square"></i>
				                    </a>               
				                </td>
				                <td width="100px" class="link" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">              
									<a class="delete_link" href="#" onclick="Click_DeleteField('<?php echo $id;?>');" title="<?php echo __('削除'); ?>"><i class="fa-regular fa-trash-can"></i>
						            </a>
				                </td>
				            </tr>   
		           			<?php } }?>
		        		</tbody>                            
		    		</table>
				</div>
			</div>
		</div>
		<?php } ?>
		
	</div>
	<div class="row" style="clear:both;margin: 40px 0px;">
			<?php if($rowCount > 50) {?>
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


