<style type="text/css">
	.table-bordered > tbody > tr > td{
		
		vertical-align: middle;
	}
	.input-group .form-control {
		position:initial !important;
	}

	.jconfirm-box.jconfirm-hilight-shake.jconfirm-type-red.jconfirm-type-animated{
	   	width: max-content;
	}
	.add_comment_textarea {

    	box-sizing:border-box;
    	display:block;
    	line-height:1.5;
    	border-radius: 4px;
    	font:13px Tahoma, cursive;
		transition:box-shadow 0.5s ease;
		box-shadow:0 4px 6px rgba(0,0,0,0.1);
		font-smoothing:subpixel-antialiased;
	}

	.jconfirm .jconfirm-box div.jconfirm-title-c .jconfirm-title{
		font-size: 0.95em;
	}

	input#saleRepre {
    	width: 80% !important;
	}
	input#logistics {
    	width: 80% !important;
	}
	input#SRSearch{
		width: 90px;
	}
	.input-group .form-control{
		z-index:0;
	}

	@media only screen and (max-width: 991px) {
		input#saleRepre {
    		width: 60% !important;
		}
		input#logistics {
    		width: 60% !important;
		}
		input#SRSearch,input#btn_approve  {

		    margin-left: 15px;
		}
		
	}
	.datepicker.dropdown-menu{
		z-index: 1200 !important;
	}
	input#addComtCmt2 {
	    font-size: 11px;
		width: 100px;
	}

	.msg-warn {
		color: blue;
	}
	td, th{
      padding: 5px;
   	}
   	.fl-scrolls{
        z-index: 1 !important;
    	}

    	.no_of_day{
    		text-align: right;
    	}
    	.yen_amt{
    		text-align: right;
    	}
  
    	.show_level_4,.show_level_6_5{
    		text-align: center;
    	}
    	

	.align-right {
		text-align: right !important;
	}
	.jconfirm .jconfirm-box div.jconfirm-content-pane .jconfirm-content {
      overflow: hidden !important; 
   }
</style>
<script type="text/javascript">
	/**** using all of clone-head-table-wrap and clone-column-table-wrap are occure due to freeze table. ****/
	$(document).ready(function(){
	
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';

		var show_btn = <?php echo json_encode($show_btn); ?>;

		$(window).scroll(function() {
			var height = $(".clone-head-table-wrap").height();
			var language = "<?php echo ($this->Session->check('Config.language'))? $this->Session->read('Config.language') : 'jpn'; ?>";
			
			if(show_btn['save'] && language == 'eng')  {
				height = "123px";
			} else if (show_btn['save'] && language == 'jpn') {
				height = "103px";		
			} else if (show_btn['request']) {
				height = "110px";
			} else if (show_btn['approve'] || show_btn['approve_cancel']) {
				height = "107px";
			}
			$(".clone-head-table-wrap").height(height);
		});

		//fixed column and header
		if($('#tbl_AddComment').length > 0) { // check data is at least 1 row/column keep
			$('.tbl-wrapper').freezeTable({ 
				'columnNum' : 4,
				'columnKeep': true,
				'freezeHead': true,	 
				'scrollBar' : true,
			 	});

			setTimeout(function(){
	            $('.tbl-wrapper').freezeTable('resize');
	         }, 1000);
		}
		/* floating scroll */
		if($("div.table-responsive.tbl-wrapper").length) {
			$(".tbl-wrapper").floatingScroll();
		}

		$('#layer_code').val("<?php echo $BA_CODE; ?>");
		$('#layer_name').val("<?php echo $BAName; ?>");
		$("#target_month").val("<?php echo $target_month;?>");
		$("#refer_date").val("<?php echo $reference_date;?>");
		$("#submission_date").val("<?php echo $submission_deadline;?>");

		var tableMinWidth;
		/*Adjust Zoom in Zoom out screen width*/
		var windowWidth = $('div.msgfont').width();
		
		if(windowWidth > tableMinWidth){
			$("#tbl_AddComment, .table-bordered").css({"min-width":windowWidth+"px"});
		}

		$(window).resize(function() {
			var windowWidth = $('div.msgfont').width();
			if(windowWidth > tableMinWidth){
				$("#tbl_AddComment, .table-bordered").css({"min-width":windowWidth+"px"});
			}else{
				$("#tbl_AddComment, .table-bordered").css({"min-width":tableMinWidth+"px"});
			}
		});
		
		scrollText();

		var flag6count 		= '<?php echo $flag6_count ?>';
		var allcount 		= '<?php echo $f5and6count; ?>';
		var checkedCount	= 0;

		var cloneColCnt 	= $('.clone-column-table-wrap .chk_addComt3.chk3:checked').length;
		var cloneHeadCnt 	= $('.clone-head-table-wrap .chk_addComt3.chk3:checked').length;
		var allCnt 			= $('.chk_addComt3.chk3:checked').length;
		var allCheckedCnt 	= allCnt - (Number(cloneColCnt) + Number(cloneHeadCnt));

    	var cloneColCnt 	= $('.clone-column-table-wrap .chk_addComt3.chk3').length;
		var cloneHeadCnt 	= $('.clone-head-table-wrap .chk_addComt3.chk3').length;
		var allCnt 			= $('.chk_addComt3.chk3').length;
		var actualCnt 		= allCnt - (Number(cloneColCnt) + Number(cloneHeadCnt));

	  	$(".chk_addComt3.chk3, #chk_acc_mgr_confirm").change(function(){

		  	var cloneColNcCnt 	= $('.clone-column-table-wrap .chk_addComt3.chk3').not(':checked').length;
			var cloneHeadNcCnt 	= $('.clone-head-table-wrap .chk_addComt3.chk3').not(':checked').length;
			var notCheckCnt 		= $('.chk_addComt3.chk3').not(':checked').length;
			var actualNcCnt 		= notCheckCnt - (cloneColNcCnt + cloneHeadNcCnt);

		    if(this.checked != true)
			    {
			    	if (this.className == "chk_addComt3 chk3" ) {
			    		checkedCount--;
			    	} else {
			    		checkedCount = -(allCheckedCnt);
			    	}
					
			    } else {
			    	if (this.className == "chk_addComt3 chk3" ) {
			    		checkedCount++;
			    	} else {
			    		checkedCount = actualCnt-allCheckedCnt;
			    	}

					if ((Number(checkedCount)+Number(flag6count)) >= allcount && actualNcCnt==0) {
						
						$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('checked',true);
					} 
			    }
			   
		});

		$("#load").hide();
		$('#btn_approve_cancel').hide();
		$('#btn_approve').hide();
		/*added by Hein Htet Ko*/
		$('#btn_reject').hide();
		$('.show_level_4').show();
    	document.getElementById('contents').style.visibility="hidden";

		if(show_btn['save']){
			$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').click(function() {

				if($(this).is(':checked')) {	

					$('.chk_addComt1').each(function() {

						if (!($(this).is(':disabled'))) {
							$(this).prop('checked',true);
							$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('checked',true);
				    }
					});

				} else {
					
					$('.chk_addComt1').each(function() {

						if (!($(this).is(':disabled'))) {
							$(this).prop('checked',false);
							$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('checked',false);
				    	}
					});
					
				}
			});
			
			$('.clone-column-table-wrap .chk_addComt1, .clone-head-table-wrap .chk_addComt1,  .chk_addComt1').click(function() {
				$('.clone-column-table-wrap .chk_addComt1, .clone-head-table-wrap .chk_addComt1').prop('checked', true);
				checkToggle('1');
			});

			checkToggle('1');

			$('.show_level_6_5').hide();
			$('.show_level_4').hide();
			$("#tbl_AddComment, .table-bordered").css({"min-width":"1500px"});
			tableMinWidth = '1500';
		
		}else if(show_btn['request']){
			$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').click(function() {

				if($(this).is(':checked')) {		

					$('.chk_addComt2').each(function() {

						if (!($(this).is(':disabled'))) {
							$(this).prop('checked',true);
							$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('checked',true);
				    	}

					});
					
				} else {
					
					$('.chk_addComt2').each(function() {

						if (!($(this).is(':disabled'))) {
							$(this).prop('checked',false);
							$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('checked',false);
				    }

					});
					
				}
			});
			
			$('.clone-column-table-wrap .chk_addComt2, .clone-head-table-wrap .chk_addComt2,  .chk_addComt2').click(function() {
				$('.clone-column-table-wrap .chk_addComt2, .clone-head-table-wrap .chk_addComt2').prop('checked', true);
				checkToggle('2');
				
			});

			$('.show_level_4').hide();
			$("#tbl_AddComment, .table-bordered").css({"min-width":"1856px"});
			tableMinWidth = '1856';

		}else if(show_btn['review']){
			$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').click(function() {

				if($(this).is(':checked')) {		

					$('.chk_addComt3').each(function() {

						if (!($(this).is(':disabled'))) {

							$(this).prop('checked',true);
							$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('checked',true);
				    	}

					});	
					
				} else {
					
					$('.chk_addComt3').each(function() {

						if (!($(this).is(':disabled'))) {
							$(this).prop('checked',false);
							$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('checked',false);
				    	}

					});
					
				}
			});
			
			$('.chk_addComt3').click(function() {
				$('.clone-column-table-wrap .chk_addComt3, .clone-head-table-wrap .chk_addComt3').prop('checked', true);
				checkToggle('3');
			});

			checkToggle('3');
			$("#tbl_AddComment, .table-bordered").css({"min-width":"1900px"});
			tableMinWidth = '1900';
			if(show_btn['approve_cancel']) {
				$('#btn_approve_cancel').show();
			}
		}else if(show_btn['approve']){
			
			$('#btn_approve').show();
			/*added by Hein Htet Ko*/
			$('#btn_reject').show();
			$('.show_level_4').hide();
		}else {
			$("#tbl_AddComment, .table-bordered").css({"min-width":"1900px"});
			tableMinWidth = '1900';
		}

		$("td").on('input','.settle_date', function() {$(this).val('');});
	});

	function searchSR(){

		var form = document.getElementById("AddCmtSearch");
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';	
		document.getElementById("messageContent").innerHTML = '';
      
		form.action = "<?php echo $this->webroot; ?>SapAddComments/searchSaleRepresentative";	
		form.method = "GET";
		form.submit();
    		return true;	
		
		scrollText();	
	}
	function SaveAddComment(){

		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';	
		document.getElementById("messageContent").innerHTML = '';

		var DataArray = [];		
		var checkflag = false;
		var err = false;
		var hiddenArray = [];
		var checkedCnt = 0;var uncheckedCnt = 0;
		$('#tbl_AddComment tbody tr').each(function(i, tr){
			
			var chk_status  = false;
			var row_id 		  = $(this).find('.chk_addComt1').val();
			var text_value1 = $(this).find('#addComtCmt1').val();
			var text_value2 = $(this).find('#addComtCmt2').val();
			var text_value3 = $(this).find('#addComtCmt3').val();
			
			if ($(this).find('.chk_addComt1').prop("checked") == true){
				
				var chk_status = true;	
				if($('#flag'+row_id).val() == 2) checkedCnt++;
			}else {
				uncheckedCnt++;
			}
			//no comment in each rows when check, show error msg 
			if( chk_status == true && text_value1 == ''){
				
				document.getElementById("error").innerHTML = errMsg(commonMsg.JSE023);
				checkflag = false;
				err = true;
				scrollText();
				return false;

			}
			
			var myDataRows  = [];

			//if already flag 3, can update flag 2
			var hiddenFlag3 = $(this).find('.hiddenFlag3').val();
			var myhidden = [];

			if(hiddenFlag3 != undefined){

				myhidden.push(hiddenFlag3,text_value1,text_value2,text_value3,false);
				hiddenArray.push(myhidden);

			}
			
			if ($(this).find('.chk_addComt1').prop("checked") == true){
				
				var chk_status = true;	
				
			}

			if(row_id != '0'){
		 		
		 		if(chk_status == true){
		 			myDataRows.push(row_id,text_value1,text_value2,text_value3,"check");
					
				}else{
					
					myDataRows.push(row_id,text_value1,text_value2,text_value3,"not_check");
					
				}

			 	DataArray.push(myDataRows);
			 	checkflag = true;
			 	
			}
			 	
		});
		
		if(!err){
			if (DataArray === undefined || DataArray.length == 0) {

				if(hiddenArray === undefined || hiddenArray.length == 0){
			
					document.getElementById("error").innerHTML = errMsg(commonMsg.JSE028);
				 	checkflag = false;
					scrollText();
					return false;
				}else{
					checkflag = true;
				}

			}
		}

		var arrayConcat = DataArray.concat(hiddenArray);
		var myJSONString = JSON.stringify(arrayConcat);

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
				            text: "<?php echo __('はい'); ?>",
				          	btnClass: 'btn-info',
				          	action: function(){
				          		var noSend = checkMailSendOrNot(checkedCnt, uncheckedCnt);
			                    // loadingPics();
			                    $('#json_data').val(myJSONString);
			                    getMail('save', noSend);

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
		}
		
		scrollText();
	}
	function RequestAddComment(){
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';	
		document.getElementById("messageContent").innerHTML = '';

		var DataArray = [];	
		var checkflag = false;
		var hiddenArray = [];
		var checkedCnt = 0;var uncheckedCnt = 0;
		$('#tbl_AddComment tbody tr').each(function(i, tr){
			
			var chk_status = false;
			var row_id = $(this).find('.chk_addComt2').val();
			var text_value = $(this).find('#addComtCmt4').val();
			//if already flag 4, can update flag 3
			var hiddenFlag4 = $(this).find('.hiddenFlag4').val();
			var myhidden = [];

			if(hiddenFlag4 != undefined){
				myhidden.push(hiddenFlag4,text_value,false);
				hiddenArray.push(myhidden);
			}
			
			if($(this).find('.chk_addComt2').prop("checked") == true){
				
				var chk_status = true;		
				if($('#flag'+row_id).val() == 3) checkedCnt++;
			}else {
				uncheckedCnt++;
			}

			var myDataRows = [];
			if(row_id != '0'){
				if(chk_status == true){
					myDataRows.push(row_id,text_value,"check");
				}else{
					myDataRows.push(row_id,text_value,"not_check");
				}
				
			 	DataArray.push(myDataRows);
			 	checkflag = true;
			 	
			}
	
		});
		if (DataArray === undefined || DataArray.length == 0) {
			
			if(hiddenArray === undefined || hiddenArray.length == 0){
			
				document.getElementById("error").innerHTML = errMsg(commonMsg.JSE028);
			 	checkflag = false;
				scrollText();
				return false;
			}else{
				checkflag = true;
			}	
		}
		var arrayConcat = DataArray.concat(hiddenArray);
		
		var myJSONString = JSON.stringify(arrayConcat);
		
		if(checkflag){
			
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
			   	content: "<?php echo __('データを依頼してよろしいですか。'); ?>",
			   	buttons: {   
			        ok: {
			            text: "<?php echo __('はい'); ?>",
			          	btnClass: 'btn-info',
			            action: function(){
			            	 	
				            var noSend = checkMailSendOrNot(checkedCnt, uncheckedCnt);
		                    $('#json_data').val(myJSONString);
		                    getMail('request', noSend);

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
		}
		scrollText();
	}
	function ApproveAddComment(){

		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';	
		document.getElementById("messageContent").innerHTML = '';
		document.getElementById('contents').style.visibility="visible";
        document.getElementById('load').style.visibility="visible"; 

		$.confirm({
		    title: '<?php echo __("承認確認"); ?>',
		    icon: 'fas fa-exclamation-circle',
		    type: 'green',
		    typeAnimated: true,
		    columnClass: 'medium',
		    closeIcon: true,
		    animateFromElement: true,
		    animation: 'top',
		    draggable: false,
		    content: errMsg(commonMsg.JSE022),
		    buttons: {   
		        ok: {
		            text: "<?php echo __("はい"); ?>",
		            btnClass: 'btn-info',
		            action: function(){
		            	getMail('approve');
		            }
		        },
		        cancel : {
		        	text: "<?php echo __("いいえ"); ?>",
		            btnClass: 'btn-default',
		        	action: function(){
			            document.getElementById('load').style.visibility="hidden";
    					document.getElementById('contents').style.visibility="hidden";
			        }
		        }
		        
		    },
		    theme: 'material',
		    animation: 'rotateYR',
		    closeAnimation: 'rotateXR'
		}); 

		scrollText();
	}
	function ApproveCancelAddComment(){
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';	
		document.getElementById("messageContent").innerHTML = '';
		document.getElementById('contents').style.visibility="visible";
        document.getElementById('load').style.visibility="visible"; 

		$.confirm({
		    title: '<?php echo __("承認キャンセル確認"); ?>',
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
		            text: "<?php echo __("はい"); ?>",
		            btnClass: 'btn-info',
		            action: function(){
		            	 getMail('approve_cancel');
		            }
		        },
		        cancel : {
		        	text: "<?php echo __("いいえ"); ?>",
		            btnClass: 'btn-default',
		        	action: function(){
			         	document.getElementById('load').style.visibility="hidden";
        				document.getElementById('contents').style.visibility="hidden";
			        }
		        }
		        
		    },
		    theme: 'material',
		    animation: 'rotateYR',
		    closeAnimation: 'rotateXR'
		});
	}
	function RejectAddComment(){
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';	
		document.getElementById("messageContent").innerHTML = '';
		document.getElementById('contents').style.visibility="visible";
        document.getElementById('load').style.visibility="visible"; 
		$.confirm({
		    title: '<?php echo __('拒否を確認'); ?>',
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
		            text: "<?php echo __("はい"); ?>",
		            btnClass: 'btn-info',
		            action: function(){
		            	// loadingPics();
						getMail('reject');
		            }
		        },
		        cancel : {
		        	text: "<?php echo __("いいえ"); ?>",
		            btnClass: 'btn-default',
		        	action: function(){
			         	document.getElementById('load').style.visibility="hidden";
        				document.getElementById('contents').style.visibility="hidden";
			        }
		        }
		        
		    },
		    theme: 'material',
		    animation: 'rotateYR',
		    closeAnimation: 'rotateXR'
		});
	}
	function ReviewAddComment(){
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';	
		document.getElementById("messageContent").innerHTML = '';

		var DataArray = [];	
		var checkflag = false;
		var checkedCnt = 0;var uncheckedCnt = 0;
		$('#tbl_AddComment tbody tr').each(function(i, tr){
			
			var chk_status = false;
			var row_id 		= $(this).find('.chk_addComt3').val();
			var text_value = $(this).find('#addComtCmt5').val();
			
			if ($(this).find('.chk_addComt3').prop("checked") == true){
				
				var chk_status = true;		
				if($('#flag'+row_id).val() == 5) checkedCnt++;
			}else if($(this).find('.chk_addComt3').prop("checked") == false){
				var chk_status = false;
				uncheckedCnt++;
			}
			
			var myDataRows = [];
		
			if(row_id != '0'){

				myDataRows.push(row_id,text_value,chk_status);
			 	DataArray.push(myDataRows);
			 	checkflag = true;
			 
			}
	
		});

		var myJSONString = JSON.stringify(DataArray);
		
		if(checkflag){
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
				content: "<?php echo __("すべてのデータを確認しますか。") ?>",
				buttons: {   
				    ok: {
				        text: "<?php echo __('はい'); ?>",
				      	btnClass: 'btn-info',
				      	
				        action: function(){
				        	var noSend = checkMailSendOrNot(checkedCnt, uncheckedCnt);
				        	// loadingPics();
			                $('#json_data').val(myJSONString);
			                getMail('review', noSend);
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
		}
		scrollText();
	}
	function Excel_download_btn(){

		document.forms[0].action = "<?php echo $this->webroot; ?>SapAddComments/Download_Add_Comment";
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;
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
   	function superScrollText(){
    	$("html, head, body").animate({ scrollTop: 0 }, "fast");
    }
    function getMail(func, noSend = false) {
        
        var layer_code = $("#layer_code").val();
        
        var page = 'SapAddComments';
        var data = [];
        
        $("#mailSubj").val('');
        $("#mailBody").val('');

        var submitForm = (func == 'save')? 'SaveSapAddComments' : (
			( func == 'request')? 'RequestSapAddComments' : (
				( func == 'approve')? 'ApproveSapAddComments' : (
					(func == 'approve_cancel')? 'ApproveCancelSapAddComments' :(
						(func == 'reject')? 'RejectSapAddComments': 
							(func == 'review')? 'ReviewSapAddComments': ''
					)
				)
			)
		);
       
        $.ajax({
            type:'post',
            url: "<?php echo $this->webroot; ?>SapAddComments/getMailLists",
            data:{layer_code : layer_code, page: page, function: func},
            dataType: 'json',
            success: function(data) {

                var mailSend = (data.mailSend == '' || noSend) ? '0' : data.mailSend;
               	$("#mailSend").val(mailSend);
                if(mailSend == 1) { 

                	$('#data_search_form').attr('method','post');
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
                  		loadingPics();
	          			document.forms['add_comment_form'].action = "<?php echo $this->webroot; ?>SapAddComments/"+submitForm;
						document.forms['add_comment_form'].method = "POST";
						document.forms['add_comment_form'].submit();
						return true;
	                   
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
						$("#add_comment_form").attr('action', '<?php echo $this->webroot; ?>SapAddComments/'+submitForm);

	                }
                }else{
                	loadingPics();
                	document.forms['add_comment_form'].action = "<?php echo $this->webroot; ?>SapAddComments/"+submitForm;
					document.forms['add_comment_form'].method = "POST";
					document.forms['add_comment_form'].submit();
					return true;
                }
               
                return true;
            },
            error: function(e) {
                console.log('Something wrong! Please refresh the page.');
            }
        });        
    }
    function checkToggle(cls) {

		var isCheck = true;
		var disable = 0;
		var rows = 0;

		$('.clone-column-table-wrap .chk_addComt'+cls+', .clone-head-table-wrap .chk_addComt'+cls+', .chk_addComt'+cls).each(function() {
			rows++;

			if($(this).is(':checked') == false) {
					isCheck = false;
				}
			if($(this).is(':disabled')){
				disable++; 
			}
		});

		if(rows == disable){

			$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop( "disabled", true);
			
		}

		if(isCheck == false) {
			$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('checked', false);
		} else {
			$('.clone-head-table-wrap #chk_acc_mgr_confirm, #chk_acc_mgr_confirm').prop('checked', true);
		}
	}
	function loadingPics() {
	    $("#overlay").show();
	    $('.jconfirm').hide();
	}
	function checkMailSendOrNot(checkedCnt, uncheckedCnt) {
		var savedCnt = <?php echo json_decode(($saved_count == '') ? 0 : $saved_count); ?>;
		var rowCnt = <?php echo json_decode(($count == '') ? 0 : $count); ?>;
		var noSend = ((savedCnt == rowCnt && uncheckedCnt > 0) || (savedCnt+checkedCnt != rowCnt)) ? true : false;
		return noSend;
	}

</script>
<?php
    echo $this->element('autocomplete', array(
        "to_level_id" => "",
        "cc_level_id" => "",
        "bcc_level_id" => "",
        "submit_form_name" => "add_comment_form",
        "MailSubject" => "",
        "MailTitle"   => "",
        "MailBody"    =>""
    ));
?>
<div id="overlay">
   <span class="loader"></span>
</div>
<div id="load"></div>
<div id="contents"></div>

<div class = 'container register_container'>
 	<div class="row" style="margin-left: 10px;">
 		<h3><?php echo __('コメント追加');?></h3>
 		<hr>
 	</div>	
 	<div class="errorSuccess">   
        <div class="success" id="success"></div>
        <div class="error" id="error"></div>   
	</div>
	<div class="errorSuccess" id="messageContent">

        <?php if($this->Session->check('Message.saveSuccess')): ?>
            <div class="success" id="sess_error">
                <?php echo $this->Flash->render("saveSuccess"); ?>
            </div>
        <?php endif; ?>
        
        <?php if($this->Session->check('Message.saveError')): ?>
            <div class="error" id="sess_error">
                <?php echo $this->Flash->render("saveError"); ?>
            </div>
        <?php endif; ?>
    </div> 
	<div class ="row line">
		<div class = "col-md-6">
			<div class = "form-group">
				<label class="col-md-4 control-label"><?php echo __("部署"); ?></label>
			    <div class="col-md-8">
					<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='layer_code' value = '' disabled="disabled" >     
				</div>
			</div>
		</div>
		<form id="AddCmtSearch">
			<div class = "col-md-6">
				<div class = "form-group">
					<label class="col-md-4 control-label"><?php echo __("営業担当者"); ?></label>
				    <div class="col-md-6">
						<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='saleRepre' name="saleRepre" value = '<?php if(!empty('$searchSaleRepre')){echo h($searchSaleRepre);} ?>'>   
					</div>
				</div>		
			</div>
	</div>
	<div class ="row line">
		<div class = "col-md-6">
			<div class = "form-group">
				<label class="col-md-4 control-label"><?php echo __("部署名"); ?></label>
			    <div class="col-md-8">
					<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='layer_name' value = '' disabled="disabled" >     
				</div>
			</div>
		</div>
		<div class = "col-md-6">
			<div class = "form-group">
				<label class="col-md-4 control-label"><?php echo __("物流Index No."); ?></label>
			    <div class="col-md-6">
					<input class ='form-control register' style="margin-bottom: 7px;" type = "textbox" id='logistics' name="logistics" value = '<?php if(!empty('$searchlogistics')){echo h($searchlogistics);} ?>'>   
				</div> 
			</div>
		</div>
		</form>
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
		<div class = "col-md-6">
			<div class = "form-group">
				<div class="col-md-10 text-right" style="margin-left:-56px;">
				<input type="button" class="btn btn-success" id="SRSearch" onClick = "searchSR();" value = "<?php echo __('検索');?>"> 
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
		<div class = "col-md-3"></div>
		<div class = "col-md-3 align-right">
			
		</div>
	</div>
	<div class ="row line">
		<div class = "col-md-6">
			<div class = "form-group">
				<label class="col-md-4 control-label"><?php echo __("提出期日"); ?></label>
			    <div class="col-md-8">
					<input class ='form-control register' type = "textbox" id='submission_date' value = '' disabled="disabled">     
				</div>
			</div>
		</div>
		<div class = "col-md-4"></div>
		<div class = "col-md-2">
			
		</div>
	</div>
	<div class="col-md-12 col-sm-12 col-xs-12 text-right" style="margin-bottom:10px;margin-left:15px;">
		<?php if($show_btn['save']){ ?>
			<input type="button" class="btn btn_save but_register" id="save_btn" onClick = "SaveAddComment();" value = "<?php echo __('保存');?>">
		<?php }else if($show_btn['request']){ ?>
			<input type="button" class="btn btn_save but_register" id="save_btn" onClick = "RequestAddComment();" value = "<?php echo __('依頼');?>">
		<?php }else if($show_btn['review']){?>
			<input type="button" class="btn btn_save but_register" id="save_btn" onClick = "ReviewAddComment();" value = "<?php echo __('確認');?>">
		<?php } ?>
		<?php if($countApprove != 0){ 
					
			if($CancelApprove == $countApprove){ 
				
				if($show_btn['approve_cancel']){ ?>

					<input type="button" class="btn btn-success but_register" id="btn_approve_cancel" name="btn_approve_cancel" onclick = "ApproveCancelAddComment();" value = "<?php echo __("承認キャンセル"); ?>" >						
		<?php }}}?>
		<?php if($countApprove != 0){

		if($fApprove == $countApprove){ 

			if($show_btn['approve']){?>


			<input type="button" class="btn btn-success btn_sumisho" id="btn_approve" name="btn_approve" onclick = "ApproveAddComment();" value = "<?php echo __("承認"); ?>" style="width: 90px;">
		<?php }
			if($show_btn['reject']){ ?>

			<input type="button" class="btn btn-success" id="btn_reject" name="btn_reject" onclick = "RejectAddComment();" value = "<?php echo __("差し戻し"); ?>" style="width: 90px;">

		<?php }}} ?>
		<?php if($count != 0){ ?>
		<input type="button" class="btn btn_save but_register" id="excel_download_btn" onClick = "Excel_download_btn();" value = "<?php echo __('Excelダウンロード');?>">
		<?php } ?>
	</div>
	<br>
	<div class="" id="error_msg">
		<div class="msgfont">
			<?php if(!empty($succCount)){ 
				echo $succCount;?>
				<label style = "float:right;height:0px;margin-bottom: 15px;"><?php echo __("※注1：債権はプラス、債務はマイナスで表示しております");?></label>
			<?php } ?>
		</div>
		<?php if(!empty($errCount)){?>
			<div id="err" class="no-data" ><?php echo $errCount; ?>
				
			</div><?php }?>

	</div>	
	<?php echo $this->Form->create(false,array('url'=>'','type'=>'post','class'=>'','name'=>'add_comment_form','id'=>'add_comment_form')); 
	?>
	<input type="hidden" name="toEmail" 	id="toEmail" value="">
	<input type="hidden" name="ccEmail" 	id="ccEmail" value="">
	<input type="hidden" name="mailSubj" 	id="mailSubj">
	<input type="hidden" name="mailTitle" 	id="mailTitle">
	<input type="hidden" name="mailBody" 	id="mailBody">
	<input type="hidden" name="json_data" 	id="json_data">
	<input type="hidden" name="mailSend" 	id="mailSend">

	<?php if(($count)!=0){?>

		<?php $rno = 0; $i7 = 0; $i6 = 0; $i4 = 0;?> <!--for save button show hide -->
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="table-responsive tbl-wrapper">
					<table class="table-bordered" id="tbl_AddComment" style="min-width: 1900px;">
						<thead class="check_period_table">
							<tr >
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "6%" <?php } else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "6%" <?php } ?> class="table-middle" style="vertical-align : middle; text-align:center;" ><?php echo __("勘定コード名"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "3%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review'] ){?> width = "6%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("相手先コード"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "3%" <?php } else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "6%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("相手先名"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="1%" <?php }else if($show_btn['save']){?> width = "6%" <?php } else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "6%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("物流Index No."); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "5%" <?php } else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "5%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("転記日付"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "5%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "5%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("計上基準日"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "5%" <?php } else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "5%" <?php } ?>class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("入出荷年月日"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "5%" <?php } else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "5%" <?php } ?>class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("決済予定日"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "3%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "4%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("滞留日数"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "5%" <?php } else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "5%" <?php } ?>class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("満期年月日"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "6%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "5%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("明細テキスト"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "6%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "5%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("営業担当者"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="2%" <?php }else if($show_btn['save']){?> width = "5%" <?php } else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "5%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("円貨金額"); ?>
									
								</th>
								<th rowspan="2" <?php if($show_btn['approve']){ ?>width="4%" <?php }else if($show_btn['save']){?> width = "10%" <?php } else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "8%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("経理コメント"); ?>
								</th>
								<th colspan="4" <?php if($show_btn['approve']){ ?>width="14%" <?php }else if($show_btn['save']){?> width = "21%" <?php } else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "24%" <?php } ?>class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("担当者コメント入力欄"); ?>
									<!-- default width=20% -->
								</th>
								<?php if(!$show_btn['save']){ ?>
									<th colspan="2" <?php if($show_btn['approve']){ ?>width="6%" <?php }else if($show_btn['save']){?> width = "25%" <?php } else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "12%" <?php } ?> class="table-middle show_level_6_5" style="vertical-align : middle;text-align:center;" ><?php echo __("管理職"); ?>
									</th>
								<?php } if($show_btn['review'] || count(array_filter($show_btn)) < 1){ ?>
									<th colspan="2" <?php if($show_btn['approve']){ ?>width="6%" <?php }else if($show_btn['save']){?> width = "25%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "6%" <?php } ?> class="table-middle show_level_4" style="vertical-align : middle;text-align:center;" >
										<?php echo __("経理担当者"); ?> 
									
									</th>
								<?php } ?>
							</tr>
							<tr> 
								<th <?php if($show_btn['approve']){ ?>width="1.5%" <?php }else if($show_btn['save']){?> width = "4%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "3%" <?php } ?> class="table-middle " style="vertical-align : middle;text-align:center;" ><?php echo __("確認完了"); ?>

									<?php if($show_btn['save']){ ?>
									
									<input type="checkbox" name="chk_acc_mgr_confirm" id="chk_acc_mgr_confirm">
									<?php } ?>
								</th>
								<th <?php if($show_btn['approve']){ ?>width="4%" <?php }else if($show_btn['save']){?> width = "10%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "9%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("滞留理由"); ?></th>
								<th <?php if($show_btn['approve']){ ?>width="4%" <?php }else if($show_btn['save']){?> width = "9%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "8%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("決済日"); ?></th>
								<th <?php if($show_btn['approve']){ ?>width="4%" <?php }else if($show_btn['save']){?> width = "9%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){?> width = "8%" <?php } ?> class="table-middle" style="vertical-align : middle;text-align:center;" ><?php echo __("備考"); ?></th>

								<?php if(!$show_btn['save']){ ?>

									<th <?php if($show_btn['approve']){ ?>width="1%" <?php }else if($show_btn['save']){?> width = "3%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){ ?> width = "3%" <?php } ?> class="table-middle show_level_6_5" style="vertical-align : middle;text-align:center;" ><?php echo __("確認済"); ?>
										<?php if($show_btn['request']){ ?>
										<input type="checkbox" name="chk_acc_mgr_confirm" id="chk_acc_mgr_confirm">
										<?php } ?>
									</th>
									<th <?php if($show_btn['approve']){ ?>width="4%" <?php }else if($show_btn['save']){?> width = "7%" <?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel'] || $show_btn['review']){ ?> width = "8%" <?php } ?> class="table-middle show_level_6_5" style="vertical-align : middle; text-align:center;" ><?php echo __("コメント入力欄"); ?></th>
								<?php }if($show_btn['review'] || count(array_filter($show_btn)) < 1) {?>
									<th <?php if($show_btn['approve']){ ?>width="2%" <?php }else{?> width = "3%" <?php } ?> class="table-middle show_level_4" style="vertical-align : middle; text-align:center;" ><?php echo __("確認欄");?>
										<br>
										<?php if($show_btn['review']){ ?>

										<input type="checkbox" name="chk_acc_mgr_confirm" id="chk_acc_mgr_confirm" checked="checked">
										<?php } ?>
									</th>
									<th <?php if($show_btn['approve']){ ?>width="4%" <?php }else{?> width = "7%" <?php } ?> class="table-middle show_level_4" style="vertical-align : middle; text-align:center;" ><?php echo __("コメント"); ?></th>
								<?php } ?>
							</tr>						
						</thead>
						<tbody>
							<?php 
							$BIR_1 = BusinessInchargeReason::BIR_1;
							$BIR_2 = BusinessInchargeReason::BIR_2;
							$BIR_3 = BusinessInchargeReason::BIR_3;
							$BIR_4 = BusinessInchargeReason::BIR_4;
							$BIR_5 = BusinessInchargeReason::BIR_5;
							$BIR_6 = BusinessInchargeReason::BIR_6;
							$BIR_7 = BusinessInchargeReason::BIR_7;
							$BIR_8 = BusinessInchargeReason::BIR_8;
							$BIR_9 = BusinessInchargeReason::BIR_9;
							$BIR_10 = BusinessInchargeReason::BIR_10;
							$BIR_11 = BusinessInchargeReason::BIR_11;
							$BIR_12 = BusinessInchargeReason::BIR_12;
							
							if(!empty($page)) foreach($page as $row){
								
								$rno++;
								$sap_id 			= $row['Sap']['id'];			
								$accountCode 		= $row['Sap']['account_code'];
								$account_name 		= $row['Sap']['account_name'];
								$Destination_code 	= $row['Sap']['destination_code'];
								$Destination 		= $row['Sap']['destination_name'];
								$Logistics_Index 	= $row['Sap']['logistic_index_no'];
								$Posting_Date 		= $row['Sap']['posting_date'];
								$Posting_Date = ($Posting_Date == '' || $Posting_Date == '0000-00-00' || $Posting_Date == '0000-00-00 00:00:00' || $Posting_Date == '-0001-11-30')? '' : date('Y-m-d',strtotime($Posting_Date));
								
								$Recorded_date = $row['Sap']['recorded_date'];
								$Recorded_date = ($Recorded_date == '' || $Recorded_date == '0000-00-00' || $Recorded_date == '0000-00-00 00:00:00' || $Recorded_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($Recorded_date));
								
								$receipt_date = $row['Sap']['receipt_shipment_date'];
								$receipt_date = ($receipt_date == '' || $receipt_date == '0000-00-00' || $receipt_date == '0000-00-00 00:00:00' || $receipt_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($receipt_date));
								
								$schedule_date = $row['0']['sap_schedule_date'];
								$schedule_date = ($schedule_date == '' || $schedule_date == '0000-00-00' || $schedule_date == '0000-00-00 00:00:00' || $schedule_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($schedule_date));

								$yen_amount = number_format($row['0']['jp_amount']);
								$maturity_date =  $row['Sap']['maturity_date'];
								$maturity_date = ($maturity_date == '' || $maturity_date == '0000-00-00' || $maturity_date == '0000-00-00 00:00:00' || $maturity_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($maturity_date));

								$line_item_text = $row['Sap']['line_item_text'];

								$sale_representative = $row['Sap']['sale_representative'];
								$flag = $row['Sap']['flag'];
								$busi_incharge_reason = h($row['SapBusiInchargeComment']['reason']);
								//added new feedback5 (base_date - settlement plan date)
							   // Calulating the difference in timestamps 
							   $diff = strtotime($reference_date) - strtotime($schedule_date);  
							  
							   
							   $numbers_day = round($diff / 86400); 

								$showBIR_1 = "";
								$showBIR_2 = "";
								$showBIR_3 = "";
								$showBIR_4 = "";
								$showBIR_5 = "";
								$showBIR_6 = "";
								$showBIR_7 = "";
								$showBIR_8 = "";
								$showBIR_9 = "";
								$showBIR_10 = "";
								$showBIR_11 = "";
								$showBIR_12 = "";
							
								switch ($busi_incharge_reason) {
								    case $BIR_1:
								        $showBIR_1 = 'selected="selected"';
								        break;
								    case $BIR_2:
								        $showBIR_2 = 'selected="selected"';
								        break;
								    case $BIR_3:
								        $showBIR_3 = 'selected="selected"';
								        break;
								    case $BIR_4:
								        $showBIR_4 = 'selected="selected"';
								        break;
								    case $BIR_5:
								        $showBIR_5 = 'selected="selected"';
								        break;
								    case $BIR_6:
								        $showBIR_6 = 'selected="selected"';
								        break;
								    case $BIR_7:
								        $showBIR_7 = 'selected="selected"';
								        break;
								    case $BIR_8:
								        $showBIR_8 = 'selected="selected"';
								        break;
								    case $BIR_9:
								        $showBIR_9 = 'selected="selected"';
								        break;
								    case $BIR_10:
								        $showBIR_10 = 'selected="selected"';
								        break;
								    case $BIR_11:
								        $showBIR_11 = 'selected="selected"';
								        break;
								    case $BIR_12:
								        $showBIR_12 = 'selected="selected"';
								        break;
								    default:
								        $showBIR = "";
								}
								$busi_incharge_settlement_date = $row['SapBusiInchargeComment']['settlement_date'];
								if($busi_incharge_settlement_date == "0000-00-00" || $busi_incharge_settlement_date == ""){
									$busi_incharge_settlement_date = "";
								}else {
									$busi_incharge_settlement_date = date('Y-m-d', strtotime($busi_incharge_settlement_date));
								}
								
								$busi_incharge_remark = trim(h($row['SapBusiInchargeComment']['remark']));
								$bac_comment = trim(h($row['SapBusiAdminComment']['comment']));
								$aic_comment = trim(h($row['SapAccInchargeComment']['comment']));
								$BICflag  = $row['SapBusiInchargeComment']['sap_id'];
								$BACFlag  = $row['SapBusiAdminComment']['sap_id'];	
								$AICFlag  = $row['SapAccInchargeComment']['sap_id'];
								$prevCmt  = trim(h($row['Sap']['preview_comment']));
								$Currency = $row['Sap']['currency'];

								switch ($Currency) {
								    case "1":
								        $showCurrency = "JPY";
								        break;
								    case "2":
								        $showCurrency = "USD";
								        break;
								    case "3":
								        $showCurrency = "IDR";
								        break;
								    case "4":
								        $showCurrency = "EURO";
								        break;
								    case "5":
								        $showCurrency = "THB";
								        break;
								    default:
								        $showCurrency = "";
								}
								
								if($show_btn['save']){

									if($flag != 2 && $flag != 3){ $i7++;
									?>
										<tr class = 'flag_chk_color'>
											<td style="display: none;">
												<input type="hidden" name="flag" class="flag" id="<?php echo 'flag'.$sap_id; ?>" value="<?php echo $flag; ?>">
											</td>
											<td style=" word-wrap: break-word;" class="account_name"><?php echo $account_name; ?>
												<input type="hidden" name="account_code" class="account_code" value="<?php echo $accountCode; ?>">
											</td>
											<td style="word-wrap: break-word;" class="dest_code"><?php echo $Destination_code; ?>
											</td>
											<td style="word-wrap: break-word;" class="dest"><?php echo $Destination; ?>
											</td>
											<td style="word-wrap: break-word;" class="logistics_no"><?php echo $Logistics_Index; ?>
											</td>
											<td style="word-wrap: break-word;" class="Posting_Date"><?php echo $Posting_Date; ?>
											</td>
											<td style="word-wrap: break-word;" class="Recorded_date"><?php echo $Recorded_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="Receipt_date"><?php echo $receipt_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="schedule_date"><?php echo $schedule_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="no_of_day"><?php echo  $numbers_day; ?>
											</td>
											<td style="word-wrap: break-word;" class="maturity_date"><?php echo $maturity_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="line_item_text"><?php echo $line_item_text; ?>
											</td>
											<td style="word-wrap: break-word;" class="sale_repres"><?php echo $sale_representative; ?>
											</td>
											<td style="word-wrap: break-word;" class="yen_amt"><?php echo $yen_amount; ?>
											</td>
											<td style="word-wrap: break-word;">
												<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo $prevCmt; ?></textarea>
											</td>
											<td style="word-wrap: break-word; text-align: center;">
												<input type="checkbox" disabled="" name ="chk_addComt1" class ="chk_addComt1" value= "0" <?php if($flag == '1'){ }else{ ?> checked="" <?php } ?>>
											</td>
											
											<td style="word-wrap: break-word;">
												<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo trim($busi_incharge_reason); ?></textarea>
												
											</td>
											<td style="word-wrap: break-word; margin-top: 16px;">
												<div class="input-group date datepicker" data-provide="" data-date-format="yyyy-mm-dd">
													<input type ="text" name="addComtCmt2" id = "addComtCmt2" value = "<?php echo trim($busi_incharge_settlement_date); ?>" class="form-control" readonly="">

													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</td>

											<td style="word-wrap: break-word;">
												<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo trim($busi_incharge_remark); ?></textarea>
												
											</td>
											<td style="word-wrap: break-word;" class="show_level_6_5">
												<input type="checkbox" readonly="">
											</td>
											<td style="word-wrap: break-word;" class="show_level_6_5"><?php echo $yen_amount; ?>
											</td>
											
											<td style="word-wrap: break-word;" class="show_level_4">
												<input type="checkbox" class="">
											</td>
											<td style="word-wrap: break-word; width: 8%;" class="show_level_4"><input type ="text" name="" id = "j" value = "" class="form-control" readonly="">
											</td>
										</tr>
									<?php }else{  ?>
										<tr>
											<td style="display: none;">
												<input type="hidden" name="flag" class="flag" id="<?php echo 'flag'.$sap_id; ?>" value="<?php echo $flag; ?>">
											</td>
											<td style=" word-wrap: break-word;" class="account_name"><?php echo $account_name; ?>
												<input type="hidden" name="account_code" class="account_code" value="<?php echo $accountCode; ?>">
											</td>
											<td style="word-wrap: break-word;" class="dest_code"><?php echo $Destination_code; ?>
											</td>
											<td style="word-wrap: break-word;" class="dest"><?php echo $Destination; ?>
											</td>
											<td style="word-wrap: break-word;" class="logistics_no"><?php echo $Logistics_Index; ?>
											</td>
											<td style="word-wrap: break-word;" class="Posting_Date"><?php echo $Posting_Date; ?>
											</td>
											<td style="word-wrap: break-word;" class="Recorded_date"><?php echo $Recorded_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="Receipt_date"><?php echo $receipt_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="schedule_date"><?php echo $schedule_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="no_of_day"><?php echo  $numbers_day; ?>
											</td>
											<td style="word-wrap: break-word;" class="maturity_date"><?php echo $maturity_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="line_item_text"><?php echo $line_item_text; ?>
											</td>
											<td style="word-wrap: break-word;" class="sale_repres"><?php echo $sale_representative; ?>
											</td>
											<td style="word-wrap: break-word;" class="yen_amt"><?php echo $yen_amount; ?>
											</td>
											<td style="word-wrap: break-word;">
												<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo $prevCmt; ?></textarea>
											</td>
											<?php if(!empty($BICflag)=='1' && $flag == '2'){ ?>
												<td style="word-wrap: break-word;text-align: center;">
													<input type="checkbox" class = "chk_addComt1 chk1" name = "chk_addComt1" value = "<?php echo $sap_id;?>" class="test_line">
												</td>
											<?php }else{ ?>
												<td style="word-wrap: break-word; text-align: center;">
													<input type="checkbox" class = "chk_addComt1 chk1" name = "chk_addComt1" value = "<?php echo $sap_id;?>" class="test_line" <?php if($flag >= 3){ ?> checked="" <?php }else{} ?> >
												</td>

												<?php if($flag == '3'){ ?>
														<input type="hidden" class = "hiddenFlag3" name = "hiddenFlag3" value = "<?php echo $sap_id;?>">
													<?php }?>

											<?php }?>

											<td style="word-wrap: break-word; width: 8%;">

												<select class="form-control retention_reason" id="addComtCmt1" name="addComtCmt1">
													<option value="">--- <?php echo __("Select"); ?> ---</option>
													<option value="<?php echo $BIR_1; ?>" <?php echo $showBIR_1; ?>><?php echo $BIR_1; ?></option>
													<option value="<?php echo $BIR_2; ?>" <?php echo $showBIR_2; ?>><?php echo $BIR_2; ?></option>
													<option value="<?php echo $BIR_3; ?>" <?php echo $showBIR_3; ?>><?php echo $BIR_3; ?></option>
													<option value="<?php echo $BIR_4; ?>" <?php echo $showBIR_4; ?>><?php echo $BIR_4; ?></option>
													<option value="<?php echo $BIR_5; ?>" <?php echo $showBIR_5; ?>><?php echo $BIR_5; ?></option>
													<option value="<?php echo $BIR_6; ?>" <?php echo $showBIR_6; ?>><?php echo $BIR_6; ?></option>
													<option value="<?php echo $BIR_7; ?>" <?php echo $showBIR_7; ?>><?php echo $BIR_7; ?></option>
													<option value="<?php echo $BIR_8; ?>" <?php echo $showBIR_8; ?>><?php echo $BIR_8; ?></option>
													<option value="<?php echo $BIR_9; ?>" <?php echo $showBIR_9; ?>><?php echo $BIR_9; ?></option>
													<option value="<?php echo $BIR_10; ?>" <?php echo $showBIR_10; ?>><?php echo $BIR_10; ?></option>
													<option value="<?php echo $BIR_11; ?>" <?php echo $showBIR_11; ?>><?php echo $BIR_11; ?></option>
													<option value="<?php echo $BIR_12; ?>" <?php echo $showBIR_12; ?>><?php echo $BIR_12; ?></option>
												</select>

											</td>

											<td style="word-wrap: break-word; margin-top: 16px;">
												<div class="input-group date datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd">
													<input type ="text" name="addComtCmt2" id = "addComtCmt2" value = "<?php echo trim($busi_incharge_settlement_date); ?>" class="form-control settle_date">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
												
											</td>
											<td style="word-wrap: break-word;">

												<textarea rows="3" cols="2" class="form-control add_comment_textarea incharge_remark" name="addComtCmt3" id = "addComtCmt3"><?php echo $busi_incharge_remark; ?></textarea>

											</td>
											<td style="word-wrap: break-word;" class="show_level_6_5"><input type="checkbox" checked="">
											</td>
											<td style="word-wrap: break-word;" class="show_level_6_5"><?php echo $yen_amount; ?>
											</td>
											
											<td style="word-wrap: break-word;" class="show_level_4"><input type="checkbox" class="" checked="">
											</td>
											<td style="word-wrap: break-word;" class="show_level_4"><input type ="text" name="" id = "k" value = "" class="form-control">
											</td>
										</tr>

									<?php } ?>
								
								<?php }else if($show_btn['review'] || $show_btn['approve_cancel']){ 

										if($flag != 5 && $flag != 6){ $i4++;
										?>
											<tr class = 'flag_chk_color'>
												<td style="display: none;">
													<input type="hidden" name="flag" class="flag" id="<?php echo 'flag'.$sap_id; ?>" value="<?php echo $flag; ?>">
												</td>
												<td style=" word-wrap: break-word;" class="account_name"><?php echo $account_name; ?>
													<input type="hidden" name="account_code" class="account_code" value="<?php echo $accountCode; ?>">
												</td>
												<td style="word-wrap: break-word;" class="dest_code"><?php echo $Destination_code; ?>
												</td>
												<td style="word-wrap: break-word;" class="dest"><?php echo $Destination; ?>
												</td>
												<td style="word-wrap: break-word;" class="logistics_no"><?php echo $Logistics_Index; ?>
												</td>
												<td style="word-wrap: break-word;" class="Posting_Date"><?php echo $Posting_Date; ?>
												</td>
												<td style="word-wrap: break-word;" class="Recorded_date"><?php echo $Recorded_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="Receipt_date"><?php echo $receipt_date; ?>
											</td>
												<td style="word-wrap: break-word;" class="schedule_date"><?php echo $schedule_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="no_of_day"><?php echo  $numbers_day; ?>
												</td>
												<td style="word-wrap: break-word;" class="maturity_date"><?php echo $maturity_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="line_item_text"><?php echo $line_item_text; ?>
												</td>
												<td style="word-wrap: break-word;" class="sale_repres"><?php echo $sale_representative; ?>
												</td>
												<td style="word-wrap: break-word;" class="yen_amt"><?php echo $yen_amount; ?>
												</td>
												<td style="word-wrap: break-word;">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo $prevCmt; ?></textarea>
												</td>
												<td style="word-wrap: break-word; text-align: center;">
													<input type="checkbox" disabled="" name ="chk_addComt1" class="chk_addComt1" value= "0"
													<?php if($flag >= 3){ ?> checked="" <?php }else{} ?> >
												</td>
												</td>
												<td style="word-wrap: break-word;">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo 
													trim($busi_incharge_reason); ?></textarea>
														
												</td>
												<td style="word-wrap: break-word; margin-top: 16px;">
													<div class="input-group date datepicker" data-provide="" data-date-format="yyyy-mm-dd">
														<input type ="text" name="addComtCmt2" id = "addComtCmt2" value = "<?php echo trim($busi_incharge_settlement_date); ?>" class="form-control" readonly="">
														<span class="input-group-addon">
															<span class="glyphicon glyphicon-calendar"></span>
														</span>
													</div>
												</td>

												<td style="word-wrap: break-word;">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo 
													trim($busi_incharge_remark); ?></textarea>

												</td>
												<td style="word-wrap: break-word;" class="show_level_6_5"><input type="checkbox" <?php if($flag >= 4){ ?> checked="" <?php }else{} ?> disabled="">
												</td>
												<td style="word-wrap: break-word;" class="show_level_6_5">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" name="" id = "" readonly=""><?php if(!empty($bac_comment))echo $bac_comment; ?></textarea>
													
												</td>
												<td style="word-wrap: break-word;" class="show_level_4"><input type="checkbox" value = "0" name="chk_addComt3" class="chk_addComt3" disabled="" <?php if($flag >= 6){ ?> checked="" <?php }else{} ?>>
												</td>

												<td style="word-wrap: break-word;" class="show_level_4">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php if(!empty($aic_comment))echo $aic_comment; ?></textarea>
													
												</td>
											</tr>

									<?php }else{ ?>

											<tr>
												<td style="display: none;">
													<input type="hidden" name="flag" class="flag" id="<?php echo 'flag'.$sap_id; ?>" value="<?php echo $flag; ?>">
												</td>
												<td style=" word-wrap: break-word;" class="account_name"><?php echo $account_name; ?>
													<input type="hidden" name="account_code" class="account_code" value="<?php echo $accountCode; ?>">
													
												</td>
												<td style="word-wrap: break-word;" class="dest_code"><?php echo $Destination_code; ?>
												</td>
												<td style="word-wrap: break-word;" class="dest"><?php echo $Destination; ?>
												</td>
												<td style="word-wrap: break-word;" class="logistics_no"><?php echo $Logistics_Index; ?>
												</td>
												<td style="word-wrap: break-word;" class="Posting_Date"><?php echo $Posting_Date; ?>
												</td>
												<td style="word-wrap: break-word;" class="Recorded_date"><?php echo $Recorded_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="Receipt_date"><?php echo $receipt_date; ?>
											</td>
												<td style="word-wrap: break-word;" class="schedule_date"><?php echo $schedule_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="no_of_day"><?php echo  $numbers_day; ?>
												</td>
												<td style="word-wrap: break-word;" class="maturity_date"><?php echo $maturity_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="line_item_text"><?php echo $line_item_text; ?>
												</td>
												<td style="word-wrap: break-word;" class="sale_repres"><?php echo $sale_representative; ?>
												</td>
												<td style="word-wrap: break-word;" class="yen_amt"><?php echo $yen_amount; ?>
												</td>
												<td style="word-wrap: break-word;">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo $prevCmt; ?></textarea>
												</td>
												<td style="word-wrap: break-word; text-align: center;">
													<input type="checkbox" class = "chk_addComt1" name = "chk_addComt1" value = "<?php echo $sap_id;?>" class="test_line" checked="" disabled="disabled">
												</td>
												</td>
												<td style="word-wrap: break-word;">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" name="addComtCmt1" id = "addComtCmt1" disabled=""><?php echo 
													trim($busi_incharge_reason);?></textarea>
													
												</td>

												<td style="word-wrap: break-word; margin-top: 16px;">
													<div class="input-group date datepicker" data-provide="" data-date-format="yyyy-mm-dd">
														<input type ="text" name="addComtCmt2" id = "addComtCmt2" value = "<?php echo trim($busi_incharge_settlement_date); ?>" class="form-control" readonly="">
														<span class="input-group-addon">
															<span class="glyphicon glyphicon-calendar"></span>
														</span>
													</div>
												</td>
												<td style="word-wrap: break-word;">

													<textarea rows="3" cols="2" class="form-control add_comment_textarea" name="addComtCmt3" id = "addComtCmt3" readonly=""><?php echo trim($busi_incharge_remark); ?></textarea>
													
												</td>
												<td style="word-wrap: break-word; text-align: center;" class="show_level_6_5">
													<input type="checkbox" class = "chk_addComt2" name = "chk_addComt2" value = "<?php echo $sap_id;?>" checked="" disabled=''>
												
												</td>
												<td style="word-wrap: break-word;" class="show_level_6_5">	
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" name="" id = "" readonly=""><?php if(!empty($bac_comment))echo $bac_comment; ?></textarea>
													
												</td>
												<td style="word-wrap: break-word;" class="show_level_4">
													<?php if($flag =='5'){ ?>
													<input type="checkbox" value = "<?php echo $sap_id;?>" name="chk_addComt3" class="chk_addComt3 chk3">
												<?php }else if($flag =='6'){ ?>
													<input type="checkbox" value = "<?php echo $sap_id;?>" checked= "" name="chk_addComt3" class="chk_addComt3 chk3">
												<?php } ?>
													
												</td>
												<td style="word-wrap: break-word;" class="show_level_4">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea acc_cmt" name="addComtCmt5" id = "addComtCmt5"><?php if(!empty($aic_comment) && $aic_comment != ''){echo $aic_comment;} ?></textarea>
													
												</td>
											</tr>

									<?php } ?>

								<?php }else if($show_btn['request'] || $show_btn['approve'] || $show_btn['approve_cancel']){
									
									if(($flag != 3 && $flag != 4) || $show_btn['approve'] || $show_btn['approve_cancel']){ $i6++;

									?>
										<tr class = 'flag_chk_color'>
											<td style="display: none;">
												<input type="hidden" name="flag" class="flag" id="<?php echo 'flag'.$sap_id; ?>" value="<?php echo $flag; ?>">
											</td>
											<td style=" word-wrap: break-word;" class="account_name"><?php echo $account_name; ?>
												<input type="hidden" name="account_code" class="account_code" value="<?php echo $accountCode; ?>">
											</td>
											<td style="word-wrap: break-word;" class="dest_code"><?php echo $Destination_code; ?>
											</td>
											<td style="word-wrap: break-word;" class="dest"><?php echo $Destination; ?>
											</td>
											<td style="word-wrap: break-word;" class="logistics_no"><?php echo $Logistics_Index; ?>
											</td>
											<td style="word-wrap: break-word;" class="Posting_Date"><?php echo $Posting_Date; ?>
											</td>
											<td style="word-wrap: break-word;" class="Recorded_date"><?php echo $Recorded_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="Receipt_date"><?php echo $receipt_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="schedule_date"><?php echo $schedule_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="no_of_day"><?php echo  $numbers_day; ?>
											</td>
											<td style="word-wrap: break-word;" class="maturity_date"><?php echo $maturity_date; ?>
											</td>
											<td style="word-wrap: break-word;" class="line_item_text"><?php echo $line_item_text; ?>
											</td>
											<td style="word-wrap: break-word;" class="sale_repres"><?php echo $sale_representative; ?>
											</td>
											<td style="word-wrap: break-word;" class="yen_amt"><?php echo $yen_amount; ?>
											</td>
											<td style="word-wrap: break-word;">
												<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo $prevCmt; ?></textarea>
											</td>
											<td style="word-wrap: break-word; text-align: center;">
												<input type="checkbox" disabled="" name ="chk_addComt1" class="chk_addComt1" value= "0" <?php if($flag <= '2'){ }else{ ?> checked="" <?php } ?> >
											</td>
											</td>
											<td style="word-wrap: break-word; width: 8%;">
												<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo $busi_incharge_reason; ?></textarea>
																								
											</td>

											<td style="word-wrap: break-word; margin-top: 16px;">
												<div class="input-group date datepicker" data-provide="" data-date-format="yyyy-mm-dd">
													<input type ="text" name="addComtCmt2" id = "addComtCmt2" value = "<?php echo trim($busi_incharge_settlement_date); ?>" class="form-control" readonly="">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</td>
											
											<td style="word-wrap: break-word;width: 8%;">
												<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo $busi_incharge_remark; ?></textarea>
												
											</td>
											<td style="word-wrap: break-word;text-align: center;" class="show_level_6_5">
												<input type="checkbox" disabled="" name ="chk_addComt2" class="chk_addComt2" value= "0" disabled=""  <?php if($flag <= '2'){ }else{ ?> checked="" <?php } ?>>
												
											</td>
											<td style="word-wrap: break-word;" class="show_level_6_5">

												<textarea rows="3" cols="2" class="form-control add_comment_textarea" name="" id = "" readonly=""><?php if(!empty($bac_comment))echo $bac_comment; ?></textarea>

											</td>
											
										</tr>

									<?php }else{ ?>

											<tr>
												<td style="display: none;">
													<input type="block" name="flag" class="flag" id="<?php echo 'flag'.$sap_id; ?>" value="<?php echo $flag; ?>">
												</td>
												<td style=" word-wrap: break-word;" class="account_name"><?php echo $account_name; ?>
													<input type="hidden" name="account_code" class="account_code" value="<?php echo $accountCode; ?>">

												</td>
												<td style="word-wrap: break-word;" class="dest_code"><?php echo $Destination_code; ?>
												</td>
												<td style="word-wrap: break-word;" class="dest"><?php echo $Destination; ?>
												</td>
												<td style="word-wrap: break-word;" class="logistics_no"><?php echo $Logistics_Index; ?>
												</td>
												<td style="word-wrap: break-word;" class="Posting_Date"><?php echo $Posting_Date; ?>
												</td>
												<td style="word-wrap: break-word;" class="Recorded_date"><?php echo $Recorded_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="Receipt_date"><?php echo $receipt_date; ?>
											</td>
												<td style="word-wrap: break-word;" class="schedule_date"><?php echo $schedule_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="no_of_day"><?php echo  $numbers_day; ?>
												</td>
												<td style="word-wrap: break-word;" class="maturity_date"><?php echo $maturity_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="line_item_text"><?php echo $line_item_text; ?>
												</td>
												<td style="word-wrap: break-word;" class="sale_repres"><?php echo $sale_representative; ?>
												</td>
												<td style="word-wrap: break-word;" class="yen_amt"><?php echo $yen_amount; ?>
												</td>
												<td style="word-wrap: break-word;">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo $prevCmt; ?></textarea>
												</td>
												<td style="word-wrap: break-word; text-align: center;">
													<input type="checkbox" class = "chk_addComt1" name = "chk_addComt1" value = "<?php echo $sap_id;?>" checked="" disabled="disabled">
												</td>
												</td>
												<td style="word-wrap: break-word;">

													<textarea rows="3" cols="2" class="form-control add_comment_textarea" name="addComtCmt1" id = "addComtCmt1" disabled=""><?php echo trim($busi_incharge_reason);?></textarea>
													
												</td>

												<td style="word-wrap: break-word; margin-top: 16px;">
													<div class="input-group date datepicker" data-provide="" data-date-format="yyyy-mm-dd">
														<input type ="text" name="addComtCmt2" id = "addComtCmt2" value = "<?php echo trim($busi_incharge_settlement_date); ?>" class="form-control" readonly="">
														<span class="input-group-addon">
															<span class="glyphicon glyphicon-calendar"></span>
														</span>
													</div>
												</td>

												<td style="word-wrap: break-word;">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" name="addComtCmt3" id = "addComtCmt3" readonly=""><?php echo trim($busi_incharge_remark); ?></textarea>
													
												</td>
												<td style="word-wrap: break-word;text-align: center;" class="show_level_6_5">
													<?php if(!empty($BACFlag)== '1' && $flag == '3'){ ?>
														<input type="checkbox" class = "chk_addComt2 chk2" name = "chk_addComt2" value = "<?php echo $sap_id;?>">
													<?php }else{ ?>
														<input type="checkbox" class = "chk_addComt2 chk2" name = "chk_addComt2" value = "<?php echo $sap_id;?>" <?php if($flag >= 4){ ?> checked="" <?php }else{} ?>>
													<?php } ?>
														<?php if($flag == '4'){ ?>
															<input type="hidden" class = "hiddenFlag4" name = "hiddenFlag4" value = "<?php echo $sap_id;?>">
														<?php }?>
												</td>
												<td style="word-wrap: break-word;" class="show_level_6_5">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea input_cmt" name="addComtCmt4" id = "addComtCmt4"><?php if(!empty($bac_comment))echo $bac_comment; ?></textarea>
													
												</td>
												
											</tr>

									<?php } ?>
								
								<?php }else { ?> <!-- end level 4 or 1 -->
											<tr class = 'flag_chk_color'>
												<td style="display: none;">
													<input type="hidden" name="flag" class="flag" id="<?php echo 'flag'.$sap_id; ?>" value="<?php echo $flag; ?>">
												</td>
												<td style=" word-wrap: break-word;" class="account_name"><?php echo $account_name; ?>
													<input type="hidden" name="account_code" class="account_code" value="<?php echo $accountCode; ?>">
												</td>
												<td style="word-wrap: break-word;" class="dest_code"><?php echo $Destination_code; ?>
												</td>
												<td style="word-wrap: break-word;" class="dest"><?php echo $Destination; ?>
												</td>
												<td style="word-wrap: break-word;" class="logistics_no"><?php echo $Logistics_Index; ?>
												</td>
												<td style="word-wrap: break-word;" class="Posting_Date"><?php echo $Posting_Date; ?>
												</td>
												<td style="word-wrap: break-word;" class="Recorded_date"><?php echo $Recorded_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="Receipt_date"><?php echo $receipt_date; ?>
											</td>
												<td style="word-wrap: break-word;" class="schedule_date"><?php echo $schedule_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="no_of_day"><?php echo  $numbers_day; ?>
												</td>
												<td style="word-wrap: break-word;" class="maturity_date"><?php echo $maturity_date; ?>
												</td>
												<td style="word-wrap: break-word;" class="line_item_text"><?php echo $line_item_text; ?>
												</td>
												<td style="word-wrap: break-word;" class="sale_repres"><?php echo $sale_representative; ?>
												</td>
												<td style="word-wrap: break-word;" class="yen_amt"><?php echo $yen_amount; ?>
												</td>
												<td style="word-wrap: break-word;">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo $prevCmt; ?></textarea>
												</td>
												<td style="word-wrap: break-word; text-align: center;">
													<input type="checkbox" disabled="" name ="chk_addComt1" class="chk_addComt1" value= "0"
													<?php if($flag >= 3){ ?> checked="" <?php }else{} ?> >
												</td>
												</td>
												<td style="word-wrap: break-word;">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo 
													trim($busi_incharge_reason); ?></textarea>
														
												</td>
												<td style="word-wrap: break-word; margin-top: 16px;">
													<div class="input-group date datepicker" data-provide="" data-date-format="yyyy-mm-dd">
														<input type ="text" name="addComtCmt2" id = "addComtCmt2" value = "<?php echo trim($busi_incharge_settlement_date); ?>" class="form-control" readonly="">
														<span class="input-group-addon">
															<span class="glyphicon glyphicon-calendar"></span>
														</span>
													</div>
												</td>

												<td style="word-wrap: break-word;">
													<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php echo 
													trim($busi_incharge_remark); ?></textarea>

												</td>
												<?php if(!$show_btn['save']){ ?>
													<td style="word-wrap: break-word;" class="show_level_6_5"><input type="checkbox" <?php if($flag >= 4){ ?> checked="" <?php }else{} ?> disabled="">
													</td>
													<td style="word-wrap: break-word;" class="show_level_6_5">
														<textarea rows="3" cols="2" class="form-control add_comment_textarea" name="" id = "" readonly=""><?php if(!empty($bac_comment))echo $bac_comment; ?></textarea>
														
													</td>

												<?php }if($show_btn['review'] || count(array_filter($show_btn)) < 1){ ?>

													<td style="word-wrap: break-word;" class="show_level_4"><input type="checkbox" value = "0" name="chk_addComt3" class="chk_addComt3" disabled="" <?php if($flag >= 6){ ?> checked="" <?php }else{} ?>>
													</td>

													<td style="word-wrap: break-word;" class="show_level_4">
														<textarea rows="3" cols="2" class="form-control add_comment_textarea" readonly=""><?php if(!empty($aic_comment))echo $aic_comment; ?></textarea>
												<?php } ?>	
												</td>
											</tr>
								<?php } ?>
							<?php } ?>  <!-- end foreach-->
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php if(($count)> 50){ ?>
			<div class="row" style="clear:both;margin: 40px 0px;">
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
				</div> 
			</div>
		<?php } ?>
		
		
	<?php } ?>
	<br><br><br>

	<?php echo $this->Form->end(); ?>	 
</div>


