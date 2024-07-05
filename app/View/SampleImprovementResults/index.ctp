<?php echo $this->Form->create(false,array('url'=>'','type'=>'post','class'=>'','name'=>'formwrapup','id'=>'formwrapup','enctype' => 'multipart/form-data')); 
?>
<style type="text/css">
	.disable_calendar {
		pointer-events : none;
	}
	.down-link {
		color: #19b5fe;
		text-decoration: underline;
	}
	.test_line {
		vertical-align: middle !important;
		white-space:nowrap; 
		text-overflow: ellipsis;

	}
	.warpnew_com
	{
		width: 100%;
	}
	.flex {
		display: grid;
		justify-content: center;
		align-content: space-between;
	}
	.row {
		display: block ;
		margin-right: 0px;
	}
	.btn_style {
		width: 100px;
	}
</style>

<script>
 
  	$(document).ready(function(){

	    <?php

	      if(($tab_2_report_times == 2 || $tab_2_report_times == 3) && !empty($tab_2_show) && $tab_chk_flag == 3) {
	    ?> 
	        document.getElementById("wp_psecond").style.display = "block";
	        $('.nav-tabs a[href="#Warpsecondhide"]').tab('show');
	        //$('#Warpsecondshow :input').attr('disabled','disabled');
	        $('.download_url').attr('disabled',false);
	        $('.download_file').attr('disabled',false);
	        $('.attachment_id').attr('disabled',false);
	        $("#Warpsecondshow .datepicker").css({"pointer-events":"none"});
	    <?php
	      } else { ?>  
	        document.getElementById("wp_psecond").style.display = "none"; 
	    <?php } ?>

	    $('.datepicker').datepicker({
	          format: 'yyyy-mm-dd'
	      });

      	/* download file */
      	$(document).on('click','.down-link', function(e) {
        	e.preventDefault();
		$(this).closest('.link-list').wrap('<form method="post" id="upd-form" action="<?php echo $this->webroot; ?>SampleImprovementResults/download_object_from_cloud"></form>');
			$(this).closest('form').submit();
		});
		<?php if (isset($warpUpdataShow))
		{
			$btnName = '';
			foreach ($button_list as $key=>$button) { 
				if($button) {
					$action_function = str_replace(' ', '', $key);
					if($action_function == 'save') $btnName = 'Save';
					if($action_function == 'review') $btnName = 'Review';
				}
			};
			$cnt_data = count($warpUpdataShow);
			for($i=0;$i<$cnt_data;$i++){
				?>
				$("tbody#imporvement_result_<?php echo $i;?>").each(function(i, tr){ 
					var warp_rp2_check = $(this).find(".warp_rp2_check");
					var wap_finshedtab = $(this).find(".wap_finshedtab");
					$(this).find('.wap_finshedtab').change(function() {
						if(this.checked) {
							if(warp_rp2_check.is(':checked')) warp_rp2_check.prop( "checked", false)
							warp_rp2_check.prop("disabled", true);
						}else{
							warp_rp2_check.prop("disabled", false);
						}
							
					});
					<?php 
					if($btnName == 'Review'){
					?>
					$(this).find('.warp_rp2_check').change(function() {
						if(this.checked) {
							if(wap_finshedtab.is(':checked')) wap_finshedtab.prop( "checked", false)
							wap_finshedtab.prop("disabled", true);
						}else{
							wap_finshedtab.prop("disabled", false);
						}
					});
					<?php } ?>
				});
				<?php
			}
			//r($warpUpdataShow);
		} ?> 
  	});

  	function getMailSetup(data_action){
        let page = "<?php echo $page;?>";
        $.ajax({
            url: "<?php echo $this->webroot; ?>Common/getMailContent",
            type: "POST",
            data: {data_action : data_action, page : page,selection_name:'SampleSelections'},
            dataType: "json",
            success: function(data) {
                mailSend = data.mailSend;
                $("#mailSend").val(mailSend);
                mailType = data.mailType;
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

    function reduceMailSetup(data_action, tab2 = ''){
    	let page = "<?php echo $page;?>";
    	
        if(mailSend == 1) {
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
                $("#formwrapup").attr("method","POST");
                $("#formwrapup").attr("action", "<?php echo $this->webroot; ?>"+page+"/"+data_action+page+tab2); 

            }else{
                /* set mails if not pop up */
                $("#toEmail").val(toMails);
                $("#ccEmail").val(ccMails);
                $("#bccEmail").val(bccMails);
                document.forms[0].action = "<?php echo $this->webroot; ?>"+page+"/"+data_action+page+tab2;
                document.forms[0].method = "POST";
                document.forms[0].submit();
                loadingPic(); 
                return true;  
            }
        }else {
            /*normal save*/
            document.forms[0].action = "<?php echo $this->webroot; ?>"+page+"/"+data_action+page+tab2;
            document.forms[0].method = "POST";
            document.forms[0].submit();
			loadingPic(); 
            return true;  
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
    /* first tab */
  	function saveWrapupData(){ 
    	var data_action = $("#data_action_save").val();
        getMailSetup(data_action);
		var canSave = false;
		var arr_fill = [];
		$("#wap_hidetab tbody tr.chk_place").each(function() {
			var isFillPO = false;
			var isChkReport = false;
			var isChkCompleted = false;
			var point_out = $(this).find('.warpnew_com');
			var optReport = $(this).find('.warp_rp2_check');
			var deadline_date = $(this).find('.warp_Com2');
			if(point_out.is(':disabled') == false && optReport.is(':disabled') == false && deadline_date.is(':disabled') == false) 
			{
			
			}
		});

		if(arr_fill.indexOf(false) == -1) {
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
						action:function(){
							reduceMailSetup(data_action);
							// document.forms[0].action = "<?php echo $this->webroot; ?>SampleImprovementResults/insertToTestresult";
							// document.forms[0].method = "POST";
							// document.forms[0].submit();
							// return true;
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
			var err_msg = errMsg(commonMsg.JSE015);
			$("#success").empty();
			$("#error").empty();
			$("#error").append(err_msg);
			$("html, body").animate({ scrollTop: 0 }, 'slow');
		}
  	}

  	function reviewWrapupData() {
  		var data_action = $("#data_action_review").val();
        getMailSetup(data_action);
		var canReview = false;
		var arr_flag = [];
		$("#wap_hidetab tbody tr.chk_place").each(function() {
		var isChkReport = false;
		var isChkCompleted = false;
		var optReport = $(this).find('.warp_rp2_check');
		var optComplete = $(this).find('.wap_finshedtab');
		if(optReport.is(':disabled') == false && optComplete.is(':disabled') == false) {
			if(optReport.is(':checked')) {
			isChkReport = true;
			}
			if(optComplete.is(':checked')) {
			isChkCompleted = true;
			}
			if(isChkReport == true || isChkCompleted == true) {
			arr_flag.push(true);
			} else {
			arr_flag.push(false);
			}
		}
		});
		
		if(arr_flag.indexOf(false) == -1) { // if all line is check
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
		var err_msg = errMsg(commonMsg.JSE025);
			$("#success").empty();
			$("#error").empty();
			$("#error").append(err_msg);
			$("html, body").animate({ scrollTop: 0 }, 'slow');
		}
	}

	function approveWrapupData(){
		var data_action = $("#data_action_approve").val();
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
		content: errMsg(commonMsg.JSE022),
		buttons: {   
			ok: {
				text: "<?php echo __('はい'); ?>",
				btnClass: 'btn-info',
				action: function(){
					reduceMailSetup(data_action);
				// document.forms[0].action = "<?php echo $this->webroot; ?>SampleImprovementResults/ApproveCheck";
				// document.forms[0].method = "POST";
				// document.forms[0].submit();
				// return true;
				}
			},   
			cancel : {
			text: "<?php echo __('いいえ'); ?>",
			btnClass: 'btn-default',
			cancel: function(){}
			}
		},
		theme: 'material',
		animation: 'rotateYR',
		closeAnimation: 'rotateXR'
		});
	}

	function approve_cancelWrapupData(){
		var data_action = $("#data_action_approve_cancel").val();
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
		content: errMsg(commonMsg.JSE012),
		buttons: {   
			ok: {
				text: "<?php echo __('はい'); ?>",
				btnClass: 'btn-info',
				action: function(){
					reduceMailSetup(data_action);
				// document.forms[0].action = "<?php echo $this->webroot; ?>SampleImprovementResults/warpApproveflagCancle";
				// document.forms[0].method = "POST";
				// document.forms[0].submit();
				// return true;
				}
			},   
			cancel : {
			text: "<?php echo __('いいえ'); ?>",
			btnClass: 'btn-default',
			cancel: function(){}
			}
		},
		theme: 'material',
		animation: 'rotateYR',
		closeAnimation: 'rotateXR'
		});
	}

	function rejectWrapupData() {
		var data_action = $("#data_action_reject").val();
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
					action: function(){
						reduceMailSetup(data_action);
						// document.forms[0].action = "<?php echo $this->webroot; ?>SampleImprovementResults/Reject_flag_first";
						// document.forms[0].method = "POST";
						// document.forms[0].submit();
						// return true;
					}
				},   
				cancel : {
					text: "<?php echo __('いいえ'); ?>",
					btnClass: 'btn-default',
					cancel: function(){}
				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		});
	}
	/* second tab */
	function saveWrapupDataSecTab(){
		var data_action = $("#data_action_save").val();
        getMailSetup(data_action);
        var tab2 = 'Tab2';
		var canSave = false;
		var arr_fill = [];
		$("#sec_tab_table tbody tr.sec_fill_row").each(function() {
		var isFillPO = false;
		var isChkReport = false;
		var isChkCompleted = false;
		var point_out = $(this).find('.warpnew_comment');
		var deadline_date = $(this).find('.warpnew_Cmttwo');
		if(point_out.is(':disabled') == false && deadline_date.is(':disabled') == false) {
			
		}
		});
		
		if(arr_fill.indexOf(false) == -1) {
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
						action:function(){
							reduceMailSetup(data_action, tab2);
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
		var err_msg = errMsg(commonMsg.JSE015);
			$("#success").empty();
			$("#error").empty();
			$("#error").append(err_msg);
			$("html, body").animate({ scrollTop: 0 }, 'slow');
		}
	}
	function reviewWrapupDataSecTab() {
		var data_action = $("#data_action_review").val();
        getMailSetup(data_action);
        var tab2 = 'Tab2';
		var canReview = false;
		var arr_complete = [];
		$("#sec_tab_table tbody tr.sec_fill_row").each(function() {
		var isChkCompleted = false;
		var optComplete = $(this).find('.wap_finshedtabtwo');
		if(optComplete.is(':disabled') == false) {
			console.log('not disabled');
			if(optComplete.is(':checked')) {
			isChkCompleted = true;
			}
			arr_complete.push(isChkCompleted);
		}
		});

		if(arr_complete.indexOf(false) == -1) { // if all line is check
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
					text: "<?php echo __('はい'); ?>",
					btnClass: 'btn-info', 
						action:function(){
							reduceMailSetup(data_action, tab2);
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
		var err_msg = errMsg(commonMsg.JSE002,['<?php echo __("完了フラグ")?>']);
			$("#success").empty();
			$("#error").empty();
			$("#error").append(err_msg);
			$("html, body").animate({ scrollTop: 0 }, 'slow');
		}
	}
	function approveWrapupDataSecTab() {
		var data_action = $("#data_action_approve").val();
        getMailSetup(data_action);
        var tab2 = 'Tab2';
       
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
				 	reduceMailSetup(data_action, tab2);
				}
			},   
			cancel : {
			text: "<?php echo __('いいえ'); ?>",
			btnClass: 'btn-default',
			cancel: function(){}
			}
		},
		theme: 'material',
		animation: 'rotateYR',
		closeAnimation: 'rotateXR'
		});
	}
	function rejectWrapupDataSecTab() {
		var data_action = $("#data_action_reject").val();
        getMailSetup(data_action);
        var tab2 = 'Tab2';
        
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
						reduceMailSetup(data_action, tab2);
					}
				},   
				cancel : {
					text: "<?php echo __('いいえ'); ?>",
					btnClass: 'btn-default',
					cancel: function(){}
				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		});
	}
	function approve_cancelWrapupDataSecTab() {
		var data_action = $("#data_action_approve_cancel").val();
        getMailSetup(data_action);
        var tab2 = 'Tab2';
        
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
					reduceMailSetup(data_action, tab2);
				}
			},   
			cancel : {
			text: "<?php echo __('いいえ'); ?>",
			btnClass: 'btn-default',
			cancel: function(){}
			}
		},
		theme: 'material',
		animation: 'rotateYR',
		closeAnimation: 'rotateXR'
		});
	}
	/* old */
  	
	
	
	function secondCancleview_stage(){   
		document.forms[0].action = "<?php echo $this->webroot; ?>SampleImprovementResults/warpCancleStage";
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true; 
	}

	// flage change sampel data 9 and text
	function wap_finshedreview() {

		var canReview = false;
		var arr_flag = [];
		$("#wap_hidetab tbody tr.chk_place").each(function() {
		var isChkReport = false;
		var isChkCompleted = false;
		var optReport = $(this).find('.warp_rp2_check');
		var optComplete = $(this).find('.wap_finshedtab');
		if(optReport.is(':disabled') == false && optComplete.is(':disabled') == false) {
			if(optReport.is(':checked')) {
			isChkReport = true;
			}
			if(optComplete.is(':checked')) {
			isChkCompleted = true;
			}
			if(isChkReport == true || isChkCompleted == true) {
			arr_flag.push(true);
			} else {
			arr_flag.push(false);
			}
		}
		});
		
		if(arr_flag.indexOf(false) == -1) { // if all line is check
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
				btnClass: 'btn-info', action:function(){
					document.forms[0].action = "<?php echo $this->webroot; ?>SampleImprovementResults/finishedReport";
					document.forms[0].method = "POST";
					document.forms[0].submit();
					return true;
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
		var err_msg = errMsg(commonMsg.JSE025);
			$("#success").empty();
			$("#error").empty();
			$("#error").append(err_msg);
			$("html, body").animate({ scrollTop: 0 }, 'slow');
		}
	}

	function thirdApproveValue(){

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
				document.forms[0].action = "<?php echo $this->webroot; ?>SampleImprovementResults/ApproveCheck";
				document.forms[0].method = "POST";
				document.forms[0].submit();
				return true;
				}
			},   
			cancel : {
			text: "<?php echo __('いいえ'); ?>",
			btnClass: 'btn-default',
			cancel: function(){}
			}
		},
		theme: 'material',
		animation: 'rotateYR',
		closeAnimation: 'rotateXR'
		});
	}

	function thirdFlagdenied(){
		
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
				document.forms[0].action = "<?php echo $this->webroot; ?>SampleImprovementResults/warpApproveflagCancle";
				document.forms[0].method = "POST";
				document.forms[0].submit();
				return true;
				}
			},   
			cancel : {
			text: "<?php echo __('いいえ'); ?>",
			btnClass: 'btn-default',
			cancel: function(){}
			}
		},
		theme: 'material',
		animation: 'rotateYR',
		closeAnimation: 'rotateXR'
		});
	}

	

	

	

	function Rejectflagfirst() {
		
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
						document.forms[0].action = "<?php echo $this->webroot; ?>SampleImprovementResults/Reject_flag_first";
						document.forms[0].method = "POST";
						document.forms[0].submit();
						return true;
					}
				},   
				cancel : {
					text: "<?php echo __('いいえ'); ?>",
					btnClass: 'btn-default',
					cancel: function(){}
				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		});
	}

	

	// function SendEmailfirst(){ 
    	
	// 	var canSave = false;
	// 	var arr_fill = [];
	// 	$("#wap_hidetab tbody tr.chk_place").each(function() {
	// 		var isFillPO = false;
	// 		var isChkReport = false;
	// 		var isChkCompleted = false;
	// 		var point_out = $(this).find('.warpnew_com');
	// 		var optReport = $(this).find('.warp_rp2_check');
	// 		var deadline_date = $(this).find('.warp_Com2');
	// 		if(point_out.is(':disabled') == false && optReport.is(':disabled') == false && deadline_date.is(':disabled') == false) 
	// 		{
			
	// 		}
	// 	});

	// 	if(arr_fill.indexOf(false) == -1) {
	// 		$.confirm({
	// 			title: "<?php echo __('保存確認'); ?>",
	// 			icon: 'fas fa-exclamation-circle',
	// 			type: 'green',
	// 			typeAnimated: true,
	// 			closeIcon: true,
	// 			columnClass: 'medium',
	// 			animateFromElement: true,
	// 			animation: 'top',
	// 			draggable: false,  
	// 			content: errMsg(commonMsg.JSE009),
	// 			buttons: {
	// 				ok: {
	// 				text: "<?php echo __('はい'); ?>",
	// 				btnClass: 'btn-info', action:function(){
	// 					$("#myPOPModal").addClass("in");
	// 					$("#myPOPModal").css({"display":"block","padding-right":"17px"});
						
	// 					$("#formwrapup").attr('action', '<?php echo $this->webroot; ?>SampleImprovementResults/Send_Email_first');
	// 					return true;

	// 					}
	// 				},    
	// 			cancel: {
	// 					text: "<?php echo __('いいえ'); ?>",
	// 				btnClass: 'btn-default',
	// 					action: function(){}
	// 			}
	// 			},
	// 			theme: 'material',
	// 			animation: 'rotateYR',
	// 			closeAnimation: 'rotateXR'
	// 		});
	// 	} else {
	// 		var err_msg = errMsg(commonMsg.JSE015);
	// 		$("#success").empty();
	// 		$("#error").empty();
	// 		$("#error").append(err_msg);
	// 		$("html, body").animate({ scrollTop: 0 }, 'slow');
	// 	}
 //  	}

  	function SendEmailsecond(){ 
    	
		var canSave = false;
		var arr_fill = [];
		$("#wap_hidetab tbody tr.chk_place").each(function() {
			var isFillPO = false;
			var isChkReport = false;
			var isChkCompleted = false;
			var point_out = $(this).find('.warpnew_com');
			var optReport = $(this).find('.warp_rp2_check');
			var deadline_date = $(this).find('.warp_Com2');
			if(point_out.is(':disabled') == false && optReport.is(':disabled') == false && deadline_date.is(':disabled') == false) 
			{
			
			}
		});

		if(arr_fill.indexOf(false) == -1) {
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
					btnClass: 'btn-info', action:function(){
						$("#myPOPModal").addClass("in");
						$("#myPOPModal").css({"display":"block","padding-right":"17px"});
						
						$("#formwrapup").attr('action', '<?php echo $this->webroot; ?>SampleImprovementResults/Send_Email_second');
						return true;

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
			var err_msg = errMsg(commonMsg.JSE015);
			$("#success").empty();
			$("#error").empty();
			$("#error").append(err_msg);
			$("html, body").animate({ scrollTop: 0 }, 'slow');
		}
  	}

</script>
<div id="overlay">
	<span class="loader"></span>
</div>
<?php echo $this->element("autocomplete", array(
        "to_level_id" => "",
        "cc_level_id" =>"",
        "submit_form_name" => "formwrapup",
        "MailSubject" => "",
        "MailTitle" => "",
        "MailBody" => "")); ?>
<div class="row">
  	<div class="col-md-12 col-sm-12">
		<div class="row">
    		<div class="col-md-12 col-sm-12">
        		<h3><?php echo __("改善結果報告") ?></h3>
      			<hr>
    		</div>
    	</div>
	    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	    	<div class="success" id="success"><?php echo ($this->Session->check("Message.UserSuccess"))? $this->Flash->render("UserSuccess") : '';?></div>            
	    	<div class="error" id="error"><?php echo ($this->Session->check("Message.UserError"))? $this->Flash->render("UserError") : '';?></div>                  
	    </div>
    	<input type="hidden" name="mailSubj" id="mailSubj">
        <input type="hidden" name="mailTitle" id="mailTitle">
        <input type="hidden" name="mailBody" id="mailBody">
        <input type="hidden" name="mailSend" id="mailSend">
        <input type="hidden" name="toEmail" id="toEmail">
       	<input type="hidden" name="ccEmail" id="ccEmail">
       	<input type="hidden" name="bccEmail" id="bccEmail">
	    <div id="exTab2" class="container-fluid"> 
	      	<ul class="nav nav-tabs">
	        	<li class="active">
	          		<a  href="#Warpsecondshow" data-toggle="tab"><?php echo __("1回"); ?> </a>
	        	</li>
	        	<li id="wp_psecond"><a href="#Warpsecondhide" data-toggle="tab"><?php echo __("2回"); ?></a>
	        	</li>
	      	</ul>
			
	     	<div class="tab-content "><br/>
	     		<!-- 1st tab -->
	        	<div class="tab-pane active" id="Warpsecondshow">
	          		<div class="row" id="warp_datahide">
		           		<fieldset class="scheduler-border">
		            		<legend class="scheduler-border"><?php echo __("要約"); ?> </legend>
				            <div class="row ">

				              	<div class="col-md-6 col-xs-12">
					                <div class="row">
										<label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("対象月"); ?>:</label>
										<div class="col-md-4 col-xs-12 ">
											<input class="form-control " id="focusedInput"  name="last_name" type="text" value="<?php echo $this->Session->read('SAMPLECHECK_PERIOD_DATE'); ?> " readonly="" >
										</div>
					                </div>
					                <div class="row">
										<label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("部署"); ?>:</label>
										<div class="col-md-4 col-xs-12 warpbutline">
											<input class="form-control " id="focusedInput"  name="last_name" type="text" value="<?php echo $this->Session->read('SESSION_LAYER_CODE'); ?>" readonly="">

										</div>
					                </div> 
					                <div class="row">
					                  	<label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("部署名"); ?>:</label>
					                  	<div class="col-md-4 col-xs-12 warpbutline">
					                     	<input class="form-control " id="focusedInput"  name="last_name" type="text" value="<?php echo $this->Session->read('SAMPLECHECK_BA_NAME'); ?>" readonly="">
					                    
					                  	</div>
					                </div> 
									<div class="row">
					                  	<label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("カテゴリー"); ?>:</label>
					                  	<div class="col-md-4 col-xs-12 warpbutline">
					                     	<input class="form-control " id="focusedInput"  name="last_name" type="text" value="<?php echo $this->Session->read('SAMPLECHECK_CATEGORY'); ?>" readonly="">
					                    
					                  	</div>
					                </div> 
				              	</div>
				              	<div class="flex col-md-4 col-xs-12"></div>
				              	<div class="flex col-md-2 col-xs-12">
				              		<?php 
								    foreach ($button_list as $key=>$button) { 
										if($button) {
											$action_function = str_replace(' ', '', $key);
											if($action_function == 'save') $btn_name = 'Save';
											if($action_function == 'review') $btn_name = ' Check ';
											if($action_function == 'approve') $btn_name = 'Approve';
											if($action_function == 'reject') $btn_name = 'Revert';
											if($action_function == 'approve_cancel') {
												$btn_name = 'Approve Cancel';
												$btn_style = '';
												$style = 'margin-left: 71px !important;';

											}else {
												$btn_style = 'btn_style';
												$style = 'margin-left: 107px !important;';
											}

								    ?> 
					                
					                	<input type="hidden" name = "data_action" id = "data_action_<?php echo strtolower($action_function); ?>" value="<?php echo $action_function; ?>">
		                                <input type="button" style="<?php echo $style; ?>" class="btn btn-success pull-right  <?php echo $btn_style; ?>" id="<?php echo strtolower($action_function) ?>_checklist" onClick = "<?php echo strtolower($action_function); ?>WrapupData();" value = "<?php echo __($btn_name);?>"><br>
							           	
					                
				                	<?php } }?>
					            </div>
					        </div>

					        <div class="row formwarpline">
					           	<div class="col-md-12 table-responsive">
									<table class="table table-bordered"  id="wap_hidetab">
										<?php if (isset($warpUpdataShow)): ?>  
											<?php 
											$btn_save_hide = false;

											$cnt = count($warpUpdataShow);
											for($i=0; $i<$cnt; $i++) {
												$account_item     = $warpUpdataShow[$i]['sd']['account_item'];
												$destination_name = $warpUpdataShow[$i]['sd']['destination_name'];
												$index_no         = $warpUpdataShow[$i]['sd']['index_no'];
												$sap_flag         = $warpUpdataShow[$i]['sd']['flag'];

												$point_out1       = h($warpUpdataShow[$i]['tr']['point_out1']);
												$point_out2       = h($warpUpdataShow[$i]['tr']['point_out2']);
												$deadline_date1   = $warpUpdataShow[$i]['0']['deadline_date1'];
												$deadline_date2   = $warpUpdataShow[$i]['0']['deadline_date2'];
												$report_necessary = $warpUpdataShow[$i]['tr']['report_necessary1'];
												$report_2         = $warpUpdataShow[$i]['tr']['report_necessary2'];
												$warp_sample_id   = $warpUpdataShow[$i]['ch']['sample_id'];
												$warp_result_id   = $warpUpdataShow[$i]['ch']['result_id'];
												$id               = $warpUpdataShow[$i]['ch']['id'];
												$warpsample_flag  = $warpUpdataShow[$i]['ch']['flag'];
												$imp_situation1  = $warpUpdataShow[$i]['ch']['improvement_situation1'];
												$account_file = $warpUpdataShow[$i]['acc_file'];
												$business_file = $warpUpdataShow[$i]['busi_attach_file'];
												$rp_time   =  $warpUpdataShow[$i]['tr']['report_times'];
												$test_rsl_flag = $warpUpdataShow[$i]['tr']['flag'];

												if($test_rsl_flag == 2) {
													$btn_save_hide = true;
												}
												?>
												<thead class="check_period_table" >
													<tr>
														<th class="test_line" valign="top">RID</th>
														<th class="test_line"><?php echo __("Index");?><br/><?php echo __("相手先");?><br/><?php echo __("勘定科目"); ?></th>
														<th class="test_line"><?php echo __("指摘事項"); ?></th>
														<th class="test_line_check test_line"><?php echo __("報告要否"); ?></th>
														<th class="test_line"><?php echo __("提出期限"); ?></th>

														<th class="test_line"><?php echo __("経理添付資料"); ?></th>
													</tr>
												</thead>
												<tbody id="imporvement_result_<?php echo $i; ?>">
													<tr>
														<td style="text-align: right !important;" class="test_line" disabled><?php echo $i+1 ;?></td>
														<input type="hidden" name="<?php echo $i+1 ;?>" value="1">
														<td class="test_line" style="text-align: left !important;" ><?php
														echo $index_no."<br/>";
														echo $destination_name."<br/>";
														echo $account_item;
														?></td>

														<td class="test_line" style="text-align: left !important;" > <?php echo nl2br($point_out1) ;?></td>
														<td class="test_line"><input type="checkbox" name="report_necessary" disabled <?php if($report_necessary == '1') {echo 'checked="checked"';}?>></td>
														<td class="test_line" style="text-align: left !important;" ><?php if ($deadline_date1 != 0){echo $deadline_date1;}else{echo '';}  ?></td>
														<td class="test_line">

														<?php 
														if (!empty($account_file)) {
														$cnt_acc_attach_file = count($account_file);
														for($q=0; $q<$cnt_acc_attach_file; $q++) {
														$attachment_id = h($account_file[$q]['attachment_id']);
														$file_name = h($account_file[$q]['file_name']);
														$url = h($account_file[$q]['url']);


														?>
														<div class="link-list" style="text-align: center !important;">

														<input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
														<input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
														<input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
														<a href="#" class="down-link" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file_name; ?></a>


														</div>
														<?php }} ?>   
														</td>     

													</tr>
													<tr>
														<td colspan="5" class="warp_upline2"><span class="wapup_subtitle "><?php echo __("改善状況"); ?></span></td>
														<td><span><?php echo __("営業添付資料"); ?></span></td>
													</tr>
													<tr>
														<td class="warp_upline2" colspan="5" ><textarea class="warpnew_comment" name="warp_Commnet[]" disabled=""><?php echo $imp_situation1 ;?></textarea></td>
														<td colspan ="2">

															<?php 
															if (!empty($business_file)) {
																$cnt_acc_attach_file = count($business_file);
																for($q=0; $q<$cnt_acc_attach_file; $q++) {
																$attachment_id = h($business_file[$q]['attachment_id']);
																$file_name = h($business_file[$q]['file_name']);
																$url = h($business_file[$q]['url']);


																?>
																<div class="link-list" style="text-align: center !important;">
																	<input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
																	<input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
																	<input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
																	<a href="#" class="down-link" alt="<?php echo $file_name; ?>" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file_name; ?></a>
																</div>
															<?php }} ?>   
																
														</td>                
													</tr>
													<!-- uppper -->

													<tr class="wp_ch_line">
														<td colspan="2"><span class="wapup_subtitle"><?php echo __("指摘事項"); ?></span></td>
														<td colspan="1"><span class="wapup_subtitle"><?php echo __("報告要否"); ?></span></td>
														<td colspan="2"><span class="wapup_subtitle"><?php echo __("提出期限"); ?></span></td>
														<td colspan="1"><span class="wapup_subtitle"><?php echo __("完了フラグ"); ?></span></td>

													</tr>
													<tr class="chk_place">
														<?php 
														 	$com_flag_disa = '';
															if($sap_flag == 10 || ($sap_flag > 5 && $warpsample_flag > 3 && $test_rsl_flag > 1)){
																$disable_calendar = "disable_calendar";
																$disabled = "disabled";
																$com_flag_disa = 'disabled';
															} 
															else {
																$disable_calendar = "";
																$disabled = '';
																if($sap_flag == 8 && $warpsample_flag == 3 && $test_rsl_flag == 3 && $report_2 != 1) {
																	$com_flag_disa = 'disabled';
																}
															}                        
														?>
														<td colspan="2"><textarea class="warpnew_com" rows="4" cols="50"  value=""  name="warp_Commentone[]" <?php echo $disabled; ?>><?php echo $point_out2; ?></textarea></td>
														<td colspan="1" class="wp_ch_line"><input type="checkbox" <?php if($report_2 == 1) {echo 'checked="checked"';} if($warpUpdataShow[$i]['sd']['flag'] == 6){echo 'checked="checked"';} ?> <?php echo $disabled; ?> class="warp_rp2_check"  name="warp_rp2_check[]"value="<?php echo $warp_sample_id;?>" ></td> 
														<td colspan="2"><span>
															<div class="input-group date datepicker <?php echo $disable_calendar; ?>"  data-provide="datepicker" >
															<input <?php echo $disabled; ?> type="text" class="form-control warp_Com2" name="warp_Commenttwo[]" value="<?php if ($deadline_date2 != 0){echo $deadline_date2;}else{echo '';}  ?>">
															<span class="input-group-addon">
															<span class="glyphicon glyphicon-calendar"></span>
															</span>
															</div></span>
														</td> 
														<input type="hidden" name="tab1_select_sample_id[]" value="<?php echo $warp_sample_id;?>">
														<input type="hidden" name="warp_sample_id[]" value="<?php echo $warp_sample_id;?>" <?php echo $disabled; ?>>
														<input type="hidden" name="warp_result_id[]" value="<?php echo $warp_result_id;?>" <?php echo $disabled; ?>>
														<td class="wp_ch_line">
															<span><input type="checkbox" name="wap_finshedtab[]" <?php if($warpUpdataShow[$i]['sd']['flag'] == 9 || $warpUpdataShow[$i]['sd']['flag'] == 10){echo 'checked="checked"';}?> class= "wap_finshedtab" value="<?php echo $warp_sample_id;?>" <?php echo $com_flag_disa; ?>></span>
														</td>

													</tr> 
												</tbody>
											<?php } ?>
										<?php endif ?>
									</table>
					          	</div>
							</div>
		     			</fieldset>
	   				</div>
	 			</div>
	 			<!-- 2nd tab -->
				<div class="tab-pane" id="Warpsecondhide">
				   	<div class="row">
					    <div class="col-md-12">
							<fieldset class="scheduler-border">
								<legend class="scheduler-border"><?php echo __("要約"); ?></legend>
								<div class="row ">
									<div class="col-md-6 col-xs-12">
										<div class="row">
											<label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("対象月"); ?>:</label>
											<div class="col-md-4 col-xs-12 ">
												<input class="form-control " id="focusedInput"  name="last_name" type="text" value="<?php echo $this->Session->read('SAMPLECHECK_PERIOD_DATE'); ?>" readonly="" >
											</div>
										</div>
										<div class="row">
											<label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("部署"); ?></label>
											<div class="col-md-4 col-xs-12 warpbutline">
												<input class="form-control " id="focusedInput"  name="last_name" type="text" value="<?php echo $this->Session->read('SESSION_LAYER_CODE'); ?>" readonly="">

											</div>
										</div>
										<div class="row">
											<label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("部署名"); ?>:</label>
											<div class="col-md-4 col-xs-12 warpbutline">
												<input class="form-control " id="focusedInput"  name="last_name" type="text" value=" <?php echo $this->Session->read('SAMPLECHECK_BA_NAME'); ?>" readonly="">

											</div>
										</div>
										<div class="row">
											<label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("カテゴリー"); ?>:</label>
											<div class="col-md-4 col-xs-12 warpbutline">
												<input class="form-control " id="focusedInput"  name="last_name" type="text" value=" <?php echo $this->Session->read('SAMPLECHECK_CATEGORY'); ?>" readonly="">

											</div>
										</div>
									</div>
									<?php 

										$warp_list_count = count($warp_list_tab1);

										$review_flg = false;
										$approve_flg = false;
										$reject_flg = false;
										$approve_cancel_flg = true;

										for($i=0; $i<$warp_list_count ;$i++) {
											if ($warpUpdataShow[$i]['sd']['flag'] == 9 || $warpUpdataShow[$i]['sd']['flag'] == 5){

												$review_flg = true;
											} 
											if ($warpUpdataShow[$i]['sd']['flag'] == 9 ||  $warpUpdataShow[$i]['sd']['flag'] == 6 ){
												$approve_flg = true;
											}
											if ($warpUpdataShow[$i]['sd']['flag'] == 9 ||  $warpUpdataShow[$i]['sd']['flag'] == 6 ){
												$reject_flg = true;
											}
											if($warpUpdataShow[$i]['sd']['flag'] != 10){
												$approve_cancel_flg = false;
											}

										}
									?>
									<div class="flex col-md-4 col-xs-12"></div>
				              		<div class="flex col-md-2 col-xs-12">
					              		<?php
										$app = false;
									    foreach ($button_list_sec as $key=>$button) { 
									    	if($button) {
												$action_function = str_replace(' ', '', $key);
												if($action_function == 'save') $btn_name = 'Save';
												if($action_function == 'review') $btn_name = ' Check ';
												if($action_function == 'approve') {
													$app = true;
													$btn_name = 'Approve';
												}
												if($action_function == 'reject') $btn_name = 'Revert';
												if($action_function == 'approve_cancel') {
													$app = true;
													$btn_name = 'Approve Cancel';
													$btn_style = '';
													$style = 'margin-left: 71px !important;';

												}else {
													$btn_style = 'btn_style';
													$style = 'margin-left: 107px !important;';
												}
									    ?> 
						                
						                	<input type="hidden" name = "data_action" id = "data_action_<?php echo strtolower($action_function); ?>" value="<?php echo $action_function; ?>">
			                                <input type="button" style="<?php echo $style; ?>"  class="btn btn-success pull-right <?php echo $btn_style; ?>" id="<?php echo strtolower($action_function) ?>_checklist" onClick = "<?php echo strtolower($action_function); ?>WrapupDataSecTab();" value = "<?php echo __($btn_name);?>"><br>
					                	<?php } }?>
					            	</div>
									
								</div><!-- end buttom and  ba row -->

								<div class="row formwarpline"><!-- form row -->

								<div class="col-md-12 table-responsive">
									<table class="table table-bordered " id="sec_tab_table">

										<?php if (isset($second_timeDataShow)): ?>                   

											<?php
											$cnt = count($second_timeDataShow);
											for($i=0; $i<$cnt; $i++) {
												$sec_account_item     = $second_timeDataShow [$i]['sd']['account_item'];
												$sec_destination_name = $second_timeDataShow [$i]['sd']['destination_name'];
												$sec_index_no         = $second_timeDataShow [$i]['sd']['index_no'];
												$sap_flag2            = $second_timeDataShow[$i]['sd']['flag'];
												$sec_point_out2       = h($second_timeDataShow [$i]['tr']['point_out2']);
												$point_out3           = h($second_timeDataShow [$i]['tr']['point_out3']);
												$sec_deadline_date2   = $second_timeDataShow [$i]['0']['deadline_date2'];
												$deadline_date3       = $second_timeDataShow [$i]['0']['deadline_date3'];
												$sec_report_nec_2 	  = $second_timeDataShow [$i]['tr']['report_necessary2'];
												$sec_report_nec_1 	  = $second_timeDataShow [$i]['tr']['report_necessary1'];
												$sec_warp_sample_id   = $second_timeDataShow [$i]['ch']['sample_id'];
												$sec_warp_result_id   = $second_timeDataShow [$i]['ch']['result_id'];
												$sec_id               = $second_timeDataShow [$i]['ch']['id'];
												$sec_warpsample_flag  = $second_timeDataShow [$i]['ch']['flag'];
												$sec_imp_situation2   = $second_timeDataShow [$i]['ch']['improvement_situation2'];
												$account_file  = $second_timeDataShow[$i]['acc_file'];
												$business_file = $second_timeDataShow[$i]['busi_attach_file'];

												?>  

												<thead class="check_period_table">
													<tr>
														<th class="test_line">RID</th>
														<th class="test_line"><?php echo __("Index");?><br/><?php echo __("相手先");?><br/><?php echo __("勘定科目"); ?></th>
														<th class="test_line"><?php echo __("指摘事項"); ?></th>
														<th class="test_line_check test_line"><?php echo __("報告要否"); ?></th>
														<th class="test_line"><?php echo __("提出期限"); ?></th>

														<th class="test_line"><?php echo __("経理添付資料"); ?></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td style="text-align: right !important;" class="test_line" disabled><?php echo $i+1 ;?></td>

														<td class="test_line" style="text-align: left !important;">
															<?php
															echo $sec_index_no."<br/>";
															echo $sec_destination_name."<br/>";
															echo $sec_account_item;
															?>
														</td>

														<td class="test_line" style="text-align: left !important;"><?php echo nl2br($sec_point_out2) ;?></td>
														<td class="test_line"><input type="checkbox" name="report_necessary" disabled <?php if($sec_report_nec_1 == 1) {echo 'checked="checked"';}?>></td>
														<td class="test_line" style="text-align: left !important;"><?php if ($sec_deadline_date2 != 0){echo $sec_deadline_date2;}else{echo '';}?></td>
														<td class="test_line">
															<?php 
															if (!empty($account_file)) {
																$cnt_acc_attach_file = count($account_file);
																for($q=0; $q<$cnt_acc_attach_file; $q++) {
																	$attachment_id = h($account_file[$q]['attachment_id']);
																	$file_name = h($account_file[$q]['file_name']);
																	$url = h($account_file[$q]['url']);


																	?>
																	<div class="link-list" style="text-align: center !important;">

																	<input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
																	<input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
																	<input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
																	<a href="#" class="down-link"  alt="<?php echo $file_name; ?>" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file_name; ?></a>

																	</div>
															<?php }} ?>   
														</td>     

													</tr>
													<tr >
														<td colspan="5" class="warp_upline2"><span class="wapup_subtitle "><?php echo __("改善状況"); ?></span></td>
														<td><span><?php echo __("営業添付資料"); ?></span></td>
													</tr>
													<tr>
														<td class="warp_upline2" colspan="5" ><textarea class="warpnew_comment" name="warp_Commnet[]" disabled=""><?php echo $sec_imp_situation2 ;?></textarea></td>
														<td colspan ="2">
														<?php 
														if (!empty($business_file)) {
															$cnt_busi = count($business_file);
															for($q=0; $q<$cnt_busi; $q++) {
																$attachment_id = h($business_file[$q]['attachment_id']);
																$file_name = h($business_file[$q]['file_name']);
																$url = h($business_file[$q]['url']);


																?>
																<div class="link-list" style="text-align: center !important;">
																	<input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
																	<input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
																	<input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
																	<a href="#" class="down-link" alt="<?php echo $file_name; ?>" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file_name; ?></a>
																</div>
														<?php }} ?>   
														
														</td>                
													</tr>
													<!-- uppper -->

													<tr class="wp_ch_line">
														<td colspan="2"><span class="wapup_subtitle"><?php echo __("指摘事項"); ?></span></td>
														<td colspan="1"><span class="wapup_subtitle"><?php echo __("報告要否"); ?></span></td>
														<td colspan="2"><span class="wapup_subtitle"><?php echo __("提出期限"); ?></span></td>
														<td colspan="1"><span class="wapup_subtitle"><?php echo __("完了フラグ"); ?></span></td>

													</tr>

													<tr class="sec_fill_row">
														<?php 
														if($sap_flag2 == 10) {
														// remove || $user_level == 2 || $user_level == 1 condition
															$disable_calendar = "disable_calendar";
															$disabled = "disabled";
														} else {
															$disable_calendar = "";
															$disabled = '';
														}
														if($sec_report_nec_2 == 1) {
															$checked = 'checked="checked"';
														} else {
															$checked = '';
														}
														if($app == true) $disabled = "disabled";
														?>
														
														<td colspan="2"><textarea class="warpnew_comment" rows="4" cols="50" value=""  name="warp_Cmttone[]" <?php echo $disabled; ?>><?php echo $point_out3; ?></textarea></td>
														<td colspan="1" class="wp_ch_line"><input type="checkbox" name="warp_rp2_check2" disabled></td>
														<td colspan="2">
															<span>
																<div class="input-group date datepicker <?php echo $disable_calendar; ?>"   data-provide="datepicker">
																	<input type="text" class="form-control warpnew_Cmttwo" name="warp_Cmttwo[]" <?php echo $disabled; ?> value="<?php if ($deadline_date3 != 0){echo $deadline_date3;}else{echo '';}  ?>">
																	<span class="input-group-addon">
																		<span class="glyphicon glyphicon-calendar"></span>
																	</span>
																</div>
															</span>
														</td>
														<?php
														if($sap_flag2 == 10) {
															$disable_id = "disabled='disabled'";
														} else {
															$disable_id = "";
														}
														?>
														<input type="hidden" name="sec_warp_sample_id[]" value="<?php echo $sec_warp_sample_id;?>" <?php echo $disable_id; ?>>
														<input type="hidden" name="sec_warp_result_id[]" value="<?php echo $sec_warp_result_id ;?>" <?php echo $disable_id; ?>>
														<input type="hidden" name="warp2sampleall[]" value="<?php echo $sec_warp_sample_id ;?>">

														<?php 
														if($second_timeDataShow[$i]['sd']['flag'] == 9 || $second_timeDataShow[$i]['sd']['flag'] == 10) {
															$chk_complete = 'checked="checked"';
														} else {
															$chk_complete = '';
														}
														if($second_timeDataShow[$i]['sd']['flag'] == 10  || $second_timeDataShow[$i]['sd']['flag'] > 5 ) { //remove || $user_level == 2 condition
															$chk_disable = 'disabled="disabled"';
														} else {
															$chk_disable = '';
														}

														?>
														<td class="wp_ch_line"><span><input type="checkbox" required  name="wap_finshedtabtwo[]" <?php echo $chk_complete." ".$chk_disable;  ?> class ="wap_finshedtabtwo" value="<?php echo $sec_warp_sample_id;?>"></span>
														</td>
													</tr> 
												</tbody>
											<?php } ?>
										<?php endif ?>     

									</table>
								</div>

								</div><!-- form end row -->
							</fieldset>
						</div>
					</div>
				</div>
				<br/><br/>
			</div>
		</div>
	</div>
</div>

<?php
echo $this->Form->end();?>




