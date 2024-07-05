<style type="text/css">
	table.table-fixed {
		width: 100%;
		table-layout: fixed;
		border: 1px solid #fff;
	}
	div.table-wrapper {
		width: 100%;
		overflow-x: auto;
	}
	table th {
		padding: 5px 0px;
		text-align: center;
		vertical-align: middle !important;
	}
	table#table_one tr td {
		height: 30px !important;
		padding: 0px 8px;
	}
	/*table tr td.input-cell {
		position: relative;
	}*/
	table tr td.input-cell input, table tr td.input-cell select{
		/*position: absolute;*/
		position: relative;
		height: 36px !important;
		/*top: 0;*/
		background-color: #D5F4FF;
		border: none !important;
		border-radius: 0 !important;
		width: 100% !important;
		outline: none;
	}
	table tr td.input-cell .input-group-addon {
		display: none;
	}
	.select_section {
		padding: 0;
	}

	.string {
		text-align: left !important;
	}
	.number {
		text-align: right !important;
		/*padding-right: 10px;*/
		padding: 8px;
	}
	.type1 {
		background-color: #eee;
	}
	.tbl_textarea textarea {
		background-color: #D5F4FF;
		resize: vertical;
		/*overflow: hidden;*/
	}
	.ui-wrapper {
		padding: 0 !important;
		height: 100%;
	}
	.ui-resizable-se {
		right: -1px;
		bottom: 3px;
	}
	.ui-resizable {
		padding: 1px !important;
		width: 100% !important;
	}
	#table_eight tr td .month {
		padding: 0px !important;
	}
	#table_eight tr td .input-group .form-control {
		height: 31px !important;
	}
	#table_eight tr td .glyphicon {
		height: 16px !important;
	}
	#table_eight tr td input[type=button] {
		width: 30px !important;
		height: 27px !important;
		padding: 0px;
	}
	#table_eight .ui-resizable-se {
	    right: 2px;
	    bottom: -3px;
	    width: 9px;
	}
	#table_eight textarea {
		width: 100% !important;
	}
	.adjust-td { 
		padding: 0px 0px !important;

	}
	.create {
		padding: 0px !important;
	}
	.flex-item {
	   display: flex;
	
	}

	.color {
		color: transparent;
	}
	.btn_sumisho {
		margin: auto !important;
	}
	th {
		height: 0rem !important;
	}
</style>
<?php
    echo $this->element('autocomplete', array(
		"to_level_id" => "",
		"cc_level_id" => "",
		"bcc_level_id" => "",
		"submit_form_name" => "brm_summary",
		"MailSubject" => "",
		"MailTitle"   => "",
		"MailBody"    =>""
		));
?>
<?php
	echo $this->Form->create(false,array('type'=>'post','action'=>'', 'id' => 'brm_summary','name'=>'brm_summary', 'enctype'=> 'multipart/form-data', 'autocomplete'=>'off'));
?>
<script type="text/javascript">		
	$(document).ready(function() {
		$("label#btn_browse").css('color','#fff !important');
		var limit = '<?php echo $create_permit; ?>';
		if(limit == 'off'){
			/*If createlimit is off, disable upload btn*/
			$("input#summ_file_upload").prop('disabled', true);
			$("label#btn_browse").css('background-color','#D5EADD !important');
			$("label#btn_browse").css('cursor','not-allowed');
			
		}
		/* when focus input, remove 0.0 and cursor only in input field */
		$('#firstDiv input[type=text]').focus(function(event){
			$(this).val(function(index,value){
				var val = (value == 0 || value == 0.0)? "" : value;
				return val;
			});
		});
		/* allow number only */
		$("#firstDiv").on('input','.number', function(e) { 
			$(this).val($(this).val().replace(/[^0-9\.\-]/g, '')); 
			var decimalOnly =/^\s*-?(\d{0,9})(\.\d{0,3})?\s*$/;
			var hidId = "#hid_"+(this.id);
			if(decimalOnly.test($(this).val().replace(/,/g, "")) == false) {
				
				$('#success').empty();
                $("#error").html(errMsg(commonMsg.JSE061)).show();
			    scrollText();
			    MakeNegative($(this));
			   	var pre = $(this).data('val');
			   	var oldhid = (pre != "" || pre != 0) ? pre : '0.0';
			   	$(hidId).val(oldhid);
			   	$(this).val(oldhid);
			    $(this).trigger('change');
			    // $('input').blur();
			}else {
				$('#hid_'+this.id).val($(this).val()); // for hidden field
			}
		});
		/* When place cursor in input field */
		$('#firstDiv').on('focus','.number',function(event){
			var retVal = "";
			$(this).val(function(index,value){
				if(value != 0){
					//Remove zero after decimal
					retVal = value.replace(/\.0+$/,'');

					//Remove comma from value
					retVal = retVal.replace(/,/g, "");
				}
				return retVal;
			});
		});
		/* when focus out, set number to format number eg : 1,000.000 */
		$('#firstDiv').on('focusout','.number', function(event){
			$(this).val(function(index,value){
				if(value == "" || value == "-"){
					MakeNegative($(this));
					value=0;
				}else{
					MakeNegative($(this));
					return formatNumber(value);
				}
				return formatNumber(value);
				
			});
		});

		$('.number').each(function() {
			var val = $.trim($(this).text());
			
 			if (parseFloat($.trim($(this).text())) < 0) {
				$(this).addClass('negative');
			}
			if (parseFloat($.trim($(this).val())) < 0) {
				$(this).addClass('negative');
			}
		});

		/* If IE, adjust textarea */
		var ua = window.navigator.userAgent;
		var msie = ua.indexOf("MSIE ");
		if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {	
			var three = $('.tbl_textarea').width();
			
			$('textarea').each(function() {
				var parentwidth = $(this).parent().width();
				$(this).resizable({
			    	maxWidth: parentwidth
				});
			});

		}	   	
		$('#table_eight').on('change', '.select_section', function() {
			$('#table_eight .amt').trigger('change');
		});
		$('#table_eight').on('change', '.amt', function() {
			var tbody_id = $(this).closest('tbody')[0]['id'];
			var cnt_row = $('#table_eight #'+tbody_id+' tr[id^="tr_"]').length;
			var year = tbody_id.substr(6);
			var current_id = this.id;
			var amount = 0;
			var pre = 0;
			var cnt;
			for(cnt = 1; cnt <= cnt_row; cnt++) {
				var section_name = $('#'+tbody_id+' #select_'+cnt).val();
				if(section_name == "有形固定資産" || section_name == "無形固定資産") {
					amount += checkisNaN('#hid_amtofmoney_'+cnt+'_'+year);
				}
			}
			
			if(amount < 0) {
				$('#totofmoney_'+year).addClass('negative');
				$('#investment_1_'+year).addClass('negative');
			}else {
				$('#totofmoney_'+year).removeClass('negative');
				$('#investment_1_'+year).removeClass('negative');
			}
			$('#totofmoney_'+year).html(formatNumber(amount)); // total 
			$('#hid_totofmoney_'+year).html(amount); // total hidden

			$('#investment_1_'+year).html(formatNumber(amount)); // investment_1_(td) for tbl_9
			
			$('#hid_investment_1_'+year).html(amount); // hidden for tbl_9
			calculateNine(('investment_1_'+year).split("_"), pre); // tbl_9' s calculation
		});
		
		$('#table_eight').on('click','.copyRow', function() { 
			var body = $(this).closest('tbody')[0]['id'];
			var year = body.substr(6);
			var tbody ='#table_eight #'+body;
			tr = $(tbody+' #tr_1'); // was copied row
			trid = $(tbody+' tr[id^="tr_"]');
			oldid = trid.length; // last id
			newid = oldid+1; // copy new id
			if(CheckRowLimit(trid.length)){

				removeBtn = '<input type="button" class="btn btn-danger removeRow" id="btn_remove_1" name="btn_remove"  value = "<?php echo ('-');?>">';

				$(tbody+' #tr_'+oldid).after(tr.clone().prop('id', 'tr_'+newid).appendTo(tbody));
				
				$(tbody+' #tr_'+newid).each(function() {
					$(this).find('td.investment').each(function(){
						var yrid = $(this).prop('id');
						var yrId = yrid.replace("year_1", "year_"+newid);
						$(tbody+' #year_'+newid).text('');
						$(tbody+' #year_'+newid).html(removeBtn);
						$(this).prop('id', yrId);

						var btnId = 'btn_remove_1'.replace("btn_remove_1", "btn_remove_"+newid);
						$(tbody+' #tr_'+newid+' td input.removeRow').prop('id', btnId);
					});

					$(this).find('td.investment .select_section').each(function(){
						var seid = $(this).prop('id');
						var seId = seid.replace("select_1", "select_"+newid);
						$(this).prop('id', seId);
						$(this).prop('value', '');

						var selname = $(this).attr('name');
						var selName = selname.replace("[tr_1]", "[tr_"+newid+"]");
						$(this).attr('name', selName);
					});
					$(this).find('td.investment .content').each(function(){
						var coid = $(this).prop('id');
						var coId = coid.replace("detail_1", "detail_"+newid);
						$(this).prop('id', coId);
						$(this).prop('value', '');

						var coname = $(this).attr('name');
						var coName = coname.replace("[tr_1]", "[tr_"+newid+"]");
						$(this).attr('name', coName);
					});
					$(this).find('td.investment .period').each(function(){
						var peid = $(this).prop('id');
						var peId = peid.replace("date_1", "date_"+newid);
						$(this).prop('id', peId);
						$(this).prop('value', year+'-00');
						$(this).addClass('color');

						var pename = $(this).attr('name');
						var peName = pename.replace("[tr_1]", "[tr_"+newid+"]");
						
						$(this).attr('name', peName);
					});	
					$(this).find('td.investment .lease_period').each(function(){
						var peid = $(this).prop('id');
						var peId = peid.replace("lease_1", "lease_"+newid);
						$(this).prop('id', peId);
						$(this).prop('value', '');
						
						var pename = $(this).attr('name');
						var peName = pename.replace("[tr_1]", "[tr_"+newid+"]");
						$(this).attr('name', peName);
					});	
					$(this).find('td.investment .amt').each(function(){
						console.log($(this));
						$(this).removeClass('negative');
						var amtid = $(this).prop('id');
						var amtId = amtid.replace("amtofmoney_1", "amtofmoney_"+newid);
						$(this).prop('id', amtId);
						$(this).val(formatNumber(0));

						var pename = $(this).attr('name');
						var peName = pename.replace("[tr_1]", "[tr_"+newid+"]");console.log(peName);
						$(this).attr('name', peName); 
					});				
				});
			}
		});

		$('#table_eight').on('click','.removeRow', function() {
			var body = $(this).closest('tbody')[0]['id'];
			var yr = body.substr(6);
			var currentTr = $(this).closest('tr')[0]['id']; // tr_2
			var selectId = currentTr.replace("tr", "select");
			$('#table_eight #'+body+' #'+currentTr+' td .amt').each(function(a,b) {
				var id = b['id'];
				if(id.split("_")[0] == "hid") {
					var removeVal = $('#'+id).val().replace(/,/g, ""); 
					var totalVal = $('#hid_totofmoney_'+yr).text().replace(/,/g, "");
					var section_name = $('#tbody_'+yr+' #'+currentTr+' .select_section').val();
					if(section_name == "有形固定資産" || section_name == "無形固定資産") {
						var total = (parseFloat(totalVal) - parseFloat(removeVal));
						
						if(total >= 0) {
							$('#totofmoney_'+yr).removeClass('negative');
							$('#investment_1_'+yr).removeClass('negative');
						}
						$('#totofmoney_'+yr).html(formatNumber(total));
						$('#investment_1_'+yr).html(formatNumber(total)); // investment_1_(td) for tbl_9
						$('#investment_1_'+yr).val(formatNumber(total)); // hidden for tbl_9

						$('#hid_totofmoney_'+yr).html(total); // hidden total
					}
				}
			});
			$('#table_eight #'+body+' #'+currentTr).remove();
		});

		$('#table_eight ').on('focusin', '.period', function() {
			$(this).removeClass('color');
			$('.period').datepicker({
	            autoclose: true,
	            format: "yyyy-mm",
	            viewMode: "months", 
	            minViewMode: "months",
	        });
        });
		
		$('#table_nine tbody tr td.input-cell input').on('change', function() {
			var prev = ($(this).data('val')!='') ? parseFloat($(this).data('val')) : 0; //get previous value that was saved in 'focusin' state
			prev = isNaN(prev)? 0 : prev; 
			
			var id = $(this).prop('id');
			var arr = id.split("_");
			
			calculateNine(arr, prev);

		});
		
		$('#btn_browse').on('change','.uploadfile',function(e) {
       		if($(this).val() != '') {
	       		$.confirm({
					title: "<?php echo __('アップロード確認'); ?>",
					icon: 'fas fa-exclamation-circle',
					type: 'green',
					typeAnimated: true,
					closeIcon: false,
					columnClass: 'medium',
					animateFromElement: true,
					animation: 'top',
					draggable: false,  
					content: "<?php echo __("ファイルをアップロードしてよろしいでしょうか。");?>",
					buttons: {
						ok: {
							text: "<?php echo __('はい'); ?>",
							btnClass: 'btn-info',
							action:function(){ 			
								$('#overlay').show();						
								document.forms[0].action = "<?php echo $this->webroot; ?>BrmSummary/SaveSummary";
								document.forms[0].method = "POST";
								document.forms[0].submit();  
							}
						},    
						action: {
							text: "<?php echo __('いいえ'); ?>",
							btnClass: 'btn-default',
							action: function(){
					
							}
						}
					},
					theme: 'material',
					animation: 'rotateYR',
					closeAnimation: 'rotateXR'
				});
			}
		}); 

		$('#firstDiv').on('focusin', '.number', function(){
			var hid_val = ($("#hid_"+this.id).val() == 0) ? '' : ($("#hid_"+this.id).val()).replace(/\.00+$/,'');
			var hidval = hid_val.replace(/,/g, "");
			$(this).val(hidval);
			/* Save current value before change */
			$(this).data('val', hidval);
		});
	});

 	function calculateNine(arr, prev) {
 		var amounts = 0;var amt_1_2 = 0;var amt_1_2_3 = 0;
 		var last_year = '<?php echo $last_year; ?>';
	 	var tot_id = '#total_'+arr[0]+'_'+arr[2];
 		var tot_1_2 = '#total_1_2_'+arr[2];
 		var tot_1_2_3 = '#total_1_2_3_'+arr[2];

 		var hid_tot_id = '#hid_total_'+arr[0]+'_'+arr[2];
 		var hid_tot_1_2 = '#hid_total_1_2_'+arr[2];
 		var hid_tot_1_2_3 = '#hid_total_1_2_3_'+arr[2];

		var getId = $('[id^='+arr[0]+'][id$='+arr[2]+']');
		for(var i = 1; i <= getId.length; i++) {
			if((i == 1 && getId[0]['id'] != "investment_1_"+last_year)) {
				var amount = parseFloat($('#hid_'+arr[0]+'_'+i+'_'+arr[2]).text().replace(/,/g, ""));
				amounts = isNaN(amount)? 0 : amount;
			}else{
				amounts += checkisNaN('#hid_'+arr[0]+'_'+i+'_'+arr[2]);
			}
		}
		
		$(tot_id).text(formatNumber(amounts)); // total of 1.営業キャッシュフロー / 2.投資キャッシュフロー
		$(hid_tot_id).text(amounts);

		amt_1_2 = parseFloat($('#hid_total_operation_'+arr[2]).text().replace(/,/g, "")) + parseFloat($('#hid_total_investment_'+arr[2]).text().replace(/,/g, ""));
		$(tot_1_2).text(formatNumber(amt_1_2)); // フリーキャッシュフロー（1+2）
		$(hid_tot_1_2).text(amt_1_2);

		//3.財務キャッシュフロー(配当（前期損益）)
		if(getId[0]['id'].indexOf('financial') != -1 || arr[2] == last_year) {
			amt_1_2_3 = checkisNaN('#hid_financial'+'_1_'+arr[2]) + parseFloat($(hid_tot_1_2).text().replace(/,/g, ""));
		}else {	
			amt_1_2_3 = parseFloat($('#hid_financial'+'_1_'+arr[2]).text().replace(/,/g, "")) + parseFloat($(hid_tot_1_2).text().replace(/,/g, ""));

		}
		$(tot_1_2_3).text(formatNumber(amt_1_2_3)); // 3.キャッシュの増減（1+2+3）
		$(hid_tot_1_2_3).text(amt_1_2_3);

		(amounts < 0)? $(tot_id).addClass('negative') : $(tot_id).removeClass('negative');
		(amt_1_2 < 0)? $(tot_1_2).addClass('negative') : $(tot_1_2).removeClass('negative');
		(amt_1_2_3 < 0)? $(tot_1_2_3).addClass('negative') : $(tot_1_2_3).removeClass('negative');
		
 	}

 	function CheckRowLimit(num) {
    	if(num > 49) {
			$("#error").html(errMsg(commonMsg.JSE053,['(50)'])).show();
		   scrollText();
		   return false;
		}else {
			return true;
		}
    }

	function formatNumber(num) {
		if(num == ''){
			num = 0;
			return num.toFixed(1);
		}else{
			if(num.toString().indexOf('.') != -1) {
				num = Math.round(num * 10) / 10;
				var numArr = num.toString().split('.');
				var value = numArr[0];
				var value = value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
				if(numArr[1]) {
					return value+"."+numArr[1];
				}else {
					return value+".0";
				}
			}else{
				var value = num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
				return value+".0";
			} 
		}
	}

	function checkisNaN(id) {
		var val = $(id).val().replace(/,/g, "");
		var chk = isNaN(parseFloat(val)) ? 0 : parseFloat(val);

		return Math.round(chk*10000)/10000;
	}

	function MakeNegative(num) {

        if (num.val().indexOf('-') == 0) {   
            $(num).addClass('negative');
          
            	num.val(function(i, v) {
                return v;
            });
            
        }else{
            if($(num).hasClass('negative')){
                $(num).removeClass('negative')
            }
        }
    } 

	function clickSaveSummary() {
		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = "";
		var $row = $(this).closest("tr");
		var $text = $row.find("td:first-child").text();
		
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
						loadingPic();                                                                   
						getMail('SaveSummary','save');                        
					}                                   
				},                                      
				cancel : {                                  
					text: '<?php echo __("いいえ");?>',
					btnClass: 'btn-default',            
					cancel: function(){                                 
						console.log('User clicked cancel'); 
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

	function clickApproveSummary() {
		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = "";
		var $row = $(this).closest("tr");
		var $text = $row.find("td:first-child").text();
		$.confirm({                     
			title: '<?php echo __("承認確認");?>',                            
			icon: 'fas fa-exclamation-circle',                     
			type: 'green',                                  
			typeAnimated: true, 
			closeIcon: true,
			columnClass: 'medium',                              
			animateFromElement: true,                                   
			animation: 'top',                                   
			draggable: false,                                   
			content: "<?php echo __('全行を承認してよろしいですか。'); ?>",         
			buttons: {                                      
				ok: {                                   
					text: '<?php echo __("はい");?>',
					btnClass: 'btn-info',                                   
					action: function(){
						loadingPic();                                  
						getMail('ApproveAndCancelSummary','approve');                        
					}                                   
				},                                      
				cancel : {                                  
					text: '<?php echo __("いいえ");?>',
					btnClass: 'btn-default',            
					cancel: function(){                                 
						console.log('User clicked cancel'); 
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

	function clickApproveCancelSummary() {
		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = "";
		var $row = $(this).closest("tr");
		var $text = $row.find("td:first-child").text();
		$.confirm({                     
			title: '<?php echo __("承認キャンセル確認");?>',                            
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
						loadingPic();                             
						getMail('ApproveAndCancelSummary','approve_cancel');                        
					}                                   
				},                                      
				cancel : {                                  
					text: '<?php echo __("いいえ");?>',
					btnClass: 'btn-default',            
					cancel: function(){                                 
						console.log('User clicked cancel'); 
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

	function getMail(form_action,func) {
		$.ajax({
			type:'post',
			url: "<?php echo $this->webroot; ?>BrmSummary/getMailLists",
			data:{page: 'BrmSummary', function: func},
			dataType: 'json',
			success: function(data) {
				var mailSend = (data.mailSend == '') ? '0' : data.mailSend;
				$("#mailSend").val(mailSend);
				if(mailSend == 1) {	
					$("#mailSubj").val(data.subject);
					$("#mailBody").val(data.body);
					if (data.mailType == 1) {
						//default
						if(data.to != undefined) var toEmail = Object.values(data.to);
						if(data.cc != undefined) var ccEmail = Object.values(data.cc);
						if(data.bcc != undefined) var bccEmail = Object.values(data.bcc);
						
						$('#toEmail').val(toEmail);
						$('#ccEmail').val(ccEmail);
						$('#bccEmail').val(bccEmail);
						loadingPic(); 
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmSummary/"+form_action;
						document.forms[0].method = "POST";
						document.forms[0].submit();
						return true;
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
			
						/* set form action */	
						$('#brm_summary').attr('action', "<?php echo $this->webroot; ?>BrmSummary/"+form_action);
					}
				} else {
					document.forms[0].action = "<?php echo $this->webroot; ?>BrmSummary/"+form_action;
					document.forms[0].method = "POST";
					document.forms[0].submit();
					return true;
				}
			},
			error: function(e) {
				console.log('Something wrong! Please refresh the page.');
			}
		});		
	}
	
	function prepareData(func,myJSONString = '',mail_data) {
		form_action = func+"Head";
		mail_info   = JSON.stringify(mail_data);
		if (form_action == 'approve_cancelHead') {
			document.forms[0].action = "<?php echo $this->webroot; ?>BrmMonthlyReport/"+form_action;
			document.forms[0].method = "POST";
			document.forms[0].submit();
			return true; 
		} else {
			return $.ajax({
			data : {myJSONString: myJSONString,mail_info:mail_info},
			url: "<?php echo $this->webroot; ?>BrmMonthlyReport/"+form_action,
			dataType: 'json',
			method: 'post',
			beforeSend: function() {
				
			}
			}).done(function (result) {
					loadingPic();
					scrollText();    
			}).fail(function(){
				console.log('fail');
				scrollText();
				window.location.reload();
			});
		}
	}

	function clickExcelSum() {
		document.forms[0].action = "<?php echo $this->webroot; ?>BrmSummary/ExcelSummary";
		document.forms[0].method = "POST";
		document.forms[0].submit();                                     
		return true;          
	}

	function scrollText(){
		var tes1 = $('#error').text();
		var tes2 = $('#success').text();
		if(tes1){
			$("html, body").animate({ scrollTop: 0 }, "slow");				
		}
		if(tes2){
			$("html, body").animate({ scrollTop: 0 }, "slow");				
		}
	}
</script>	
<div id="overlay">
	<span class="loader"></span>
</div>
<input type="hidden" name="toEmail" id="toEmail" value="">
<input type="hidden" name="ccEmail" id="ccEmail" value="">
<input type="hidden" name="bccEmail" id="bccEmail" value="">
<input type="hidden" name="mailSubj" id="mailSubj">
<input type="hidden" name="mailBody" id="mailBody">
<input type="hidden" name="mailSend" id="mailSend">
<div class="container register_container">
	<div class="row" style="font-size: 0.95em;">
		<div class="col-md-12 col-sm-12 heading_line_title">
			<h3>
				<?php foreach ($term_year as $tkey => $tyear) {
					if($tkey == 0) {
						$th = $tyear." ".__("見込");
					}else {
						$th = " / ".$tyear." ".__("予算");
					}
					echo $th;
				} ?>
			</h3>
			
			<hr>
		</div>
		<!-- start show error msg and success msg from controller  -->
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.SummarySuccess"))? $this->Flash->render("SummarySuccess") : '';?></div>						
			<div class="error" id="error"><?php echo ($this->Session->check("Message.SummaryError"))? $this->Flash->render("SummaryError") : '';?></div>
												
		</div>
		<!-- end show error and success msg from controller -->
		<div class="form-group row">
			<div class="col-sm-6">
				<label class="col-sm-4 col-form-label">
					<?php echo __('期間');?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="term_name" value="<?php if($term_name != "") echo($term_name); ?>" readonly="readonly"/>
				</div>
			</div>	
			
		</div>	
		<div class="form-group row">
			<div class="col-sm-6">
				<label class="col-sm-4 col-form-label">
					<?php echo __($layer_type[SETTING::LAYER_SETTING['topLayer']]);?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="headquarter" value="<?php if($headquarter != "") echo($headquarter); ?>" readonly="readonly"/>
				</div>
			</div>   
		</div>
		<div class="form-group row">
			<div class="col-sm-6">
				<label class="col-sm-4 col-form-label">
					<?php echo __($layer_type[SETTING::LAYER_SETTING['bottomLayer']]);?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="layer_code" value="<?php if($layer_code != "") echo $layer_code.'/'.$layer_name; ?>" readonly="readonly"/>
				</div>
			</div>   
		</div>
		<div class="form-group row">
			<div class="col-sm-6">
				<label class="col-sm-4 col-form-label">
					<?php echo __('提出日');?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="created_date" value="<?php if($created_date != "") echo($created_date); ?>" readonly="readonly"/>
				</div>
			</div>
			<div class="col-md-6" style="text-align: right;">
				<div style="margin-right: 15px;">
					<?php if ($approve_flag == 1 && $layer_code == ''): ?>
						<label id="btn_browse" class="control-label btn" style="height: 35px !important;"><?php echo __('Upload File');?>
							<input type="file" name="summ_file_upload" id="summ_file_upload" class="uploadfile">
						</label>
					<?php endif ?>

					<input type="button" class="btn btn-success btn_sumisho" style="width: 150px;" id="btn_download" name="btn_download"  value ="<?php echo __('Excel Download');?>" onclick = "clickExcelSum();">
					<?php 
						$disable_save = ($create_permit == 'off' || $approve_flag == 2 || $layer_code != '') ? 'disabled' : '';
						$disable_approve = ($approve_permit == 'off' || $layer_code != '') ? 'disabled' : '';
					?>
					<?php if ($approve_flag == 1): ?>
						<input type="button" class="btn btn-success btn_sumisho" id="btn_save" name="btn_save"  value = "<?php echo __('Save');?>" onclick = "clickSaveSummary();" >
						<input type="button" class="btn btn-success btn_sumisho" id="btn_approve" name="btn_approve"  value = "<?php echo __('Approve');?>" onclick = "clickApproveSummary();" <?php echo "$disable_approve"; ?>>
						<input type="hidden" name="button_type" value="approve">
					<?php else: ?>
						<input type="button" class="btn btn-success" id="btn_approve_cancel" name="btn_approve_cancel"  value = "<?php echo __('Approve Cancel');?>" onclick = "clickApproveCancelSummary();" <?php echo "$disable_approve"; ?>>
						<input type="hidden" name="button_type" value="approve_cancel">
					<?php endif ?>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row" id="firstDiv">
		<!-- 1st table -->
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 summary" style="margin-top:20px; margin-bottom: 30px;">
			<label for="one"><?php echo "1．".__("単体Ｐ／Ｌ（収益はﾌﾟﾗｽ表示、費用はﾏｲﾅｽ表示にて記載。）"); ?></label>
			<span class="pull-right"><?php echo __('(単位：百万円）'); ?></span>
			<div class="table-wrapper scrollable">
				<table class="table-bordered table-fixed" id="table_one">
					<thead class="check_period_table">
						<tr class="blank-cell">
							<th class="blank-cell w-170"></th>
							<th class="blank-cell w-70"></th>
							<th class="blank-cell w-70"></th>
							<th class="blank-cell w-70"></th>
							<th class="blank-cell w-80"></th>
							<?php foreach ($bg_year_arr as $budget_year): ?>
								<th class="blank-cell w-30"></th>
								<th class="blank-cell w-70"></th>
								<th class="blank-cell w-70"></th>
								<th class="blank-cell w-80" ></th>
							<?php endforeach ?>
						</tr>
						<tr>
							<th style="border-bottom: none !important;"></th>
							<th><?php echo ($last_year).__("年度"); ?></th>
							<th colspan="2"><?php echo $fc_year.__("年度"); ?></th>
							<th rowspan="3"><?php echo __("見込対予算比"); ?></th>
							<?php foreach ($bg_year_arr as $budget_year): ?>
								<th class="blank-cell w-40"></th>
								<th colspan="2"><?php echo $budget_year.__("年度"); ?></th>
								<?php if ($budget_year-1 == $fc_year): ?>
									<th rowspan="3"><?php echo __("前年見込比"); ?></th>
								<?php else: ?>
									<th rowspan="3"><?php echo __("前年予算比"); ?></th>
								<?php endif ?>
							<?php endforeach ?>
						</tr>
						<tr>
							<th style="border-top: none !important;border-bottom: none !important;"></th>
							<th rowspan="2"><?php echo __("実績"); ?></th>				
							<th rowspan="2"><?php echo __("予算"); ?></th>
							<th rowspan="2"><?php echo __("見込"); ?></th>
							<?php foreach ($bg_year_arr as $budget_year): ?>
								<th class="blank-cell w-40"></th>
								<th colspan="2"><?php echo __("予算案"); ?></th> 
							<?php endforeach ?>
						</tr>
						<tr>
							<th style="border-top: none !important;"></th>
							<?php foreach ($bg_year_arr as $budget_year): ?>
								<th class="blank-cell w-40"></th>
								<th><?php echo __("上半期"); ?></th>
								<th><?php echo __("年間"); ?></th>
							<?php endforeach ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($table_one_acc['forecast'] as $sub_acc_name_jp => $forecast_data): ?>
							<?php
								$sub_id = $forecast_data['sub_acc_id'];
								$type = $forecast_data['type'];
								$result_amt = number_format($forecast_data['result_amt']);
								$budget_amt = number_format($forecast_data['budget_amt']);
								$expected_amt = number_format($forecast_data['expected_amt']);
								$expected_budget_diff = number_format($forecast_data['expected_budget_diff']);
							?>
							<tr class="type<?php echo($type) ?>">
								<td><?php echo $sub_acc_name_jp; ?>
								</td>
								<td class="number" name="data"<?php '[$sub_acc_name_jp][last_year_result]' ?>><?php echo $result_amt; ?></td>
								<td class="number"><?php echo $budget_amt; ?></td>
								<td class="number"><?php echo $expected_amt; ?></td>
								<td class="number type1"><?php echo $expected_budget_diff; ?></td>
								<?php foreach ($table_one_acc['budget'] as $budget_year => $budget): ?>
									<td class="w-40 blank-cell"></td>
									<td class="number"><?php echo number_format($budget[$sub_acc_name_jp]['half_budget']); ?></td>
									<td class="number"><?php echo number_format($budget[$sub_acc_name_jp]['yearly_budget']); ?></td>
									<td class="number type1"><?php echo number_format($budget[$sub_acc_name_jp]['difference']); ?></td>
									
								<?php endforeach ?>
							</tr>
						<?php endforeach ?>
						<tr class="blank-cell" style="height: 30px"></tr>
						<tr>
							<td><?php echo __(' 在籍人数（月平均）'); ?>
								</td>
							<td class="number" name="data"<?php '[$sub_acc_name_jp][last_year_result]' ?>><?php echo $table_one_psc['forecast']['last_result']; ?></td>
							<td class="number"><?php echo $table_one_psc['forecast']['budget']; ?></td>
							<td class="number"><?php echo $table_one_psc['forecast']['forecast']; ?></td>
							<td class="number type1"><?php echo $table_one_psc['forecast']['diff']; ?></td>

							<?php foreach ($table_one_psc['budget'] as $budget_year => $budget): ?>
									<td class="w-40 blank-cell"></td>
									<td class="number"><?php echo $budget['first_half']; ?></td>
									<td class="number"><?php echo $budget['total']; ?></td>
									<td class="number type1"><?php echo $budget['diff']; ?></td>
									
								<?php endforeach ?>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		
		<!-- 2nd, 3rd, 4th, 5th, 6th, 7th table -->
		<div class="col-sm-12 col-xs-12 tbl_textarea">
			<label for="two"><?php echo "2．".__("単体Ｐ／Ｌ増減説明"); ?></label>
			<div class="table-responsive table-fixed" style="margin-bottom: 30px;">
				<table class="table-fixed">
					<thead class="check_period_table">
						<tr>
							<th style="width: <?php echo (100/count($table_two).'%'); ?>"><?php echo $fc_year.__("年度")." ".__("見込の主な内容及び、予算比増減説明"); ?></th>
							<?php foreach ($bg_year_arr as $year): ?>
								<th style="width: <?php echo (100/count($table_two).'%'); ?>"><?php echo $year.__("年度")." ".__("予算案の主な内容及び、前年見込比増減説明"); ?></th>
							<?php endforeach ?>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="no-border valign-top h-200">
								<textarea style="height: 100%" class="form-control txtarea_three" name="year_pl_cmt[<?php echo($fc_year); ?>]" <?php echo $disable_save; ?>><?php echo trim($table_two[$fc_year]); ?></textarea>
							</td>
							<?php foreach ($bg_year_arr as $year): ?>
								<td class="no-border valign-top h-200">
									<textarea style="height: 100%" class="form-control txtarea_three" name="year_pl_cmt[<?php echo($year); ?>]" <?php echo $disable_save; ?>><?php echo trim($table_two[$year]); ?></textarea>
								</td>
							<?php endforeach ?>
						</tr>
					</tbody>
				</table>
			</div>

			<?php $cmt=2; foreach ($table_three_seven as $header => $values) { $cmt++;?>
			<div class="table-responsive tbl-wrapper mb-30">
				<label for="three"><?php echo __($header); ?></label>
				<textarea class="form-control txtarea_three" name="<?php echo('comment'.$cmt); ?>" rows="10" <?php echo $disable_save; ?>><?php echo trim($values); ?></textarea>
			</div>
			<?php } ?>
		</div>
		
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">
			<div class="row">
				<!-- 8th table -->
				<div class="col-lg-7 col-md-7 col-sm-12 col-xs-12 table-responsive table-fixed tbl-wrapper">
					<label for="eight"><?php echo "8. ".__("設備投資"); ?></label>
					<table class="table-bordered table-fixed" id="table_eight">
						<thead class="check_period_table">
							<tr>
								<th class="" style="border-bottom: none;width: 35px;"><?php echo __("年度"); ?></th>
								<th class="w-110" style="border-bottom: none;"><?php echo __("区分"); ?></th>
								<th style="border-bottom: none;"><?php echo __("内容"); ?></th>
								<th class="w-140" style="border-bottom: none;"><?php echo __("購入日"); ?></th>
								<th class="w-100" style="border-bottom: none;"><?php echo __("リース期間"); ?></th>
								<th class="w-80" style="border-bottom: none;"><?php echo __("金額"); ?></th>
							</tr>
							<tr>
								<th style="border-top: none;"></th>
								<th style="border-top: none;"></th>
								<th style="border-top: none;"></th>
								<th style="border-top: none;"></th>
								<th style="border-top: none;"></th>
								<th style="border-top: none;"></th>
							</tr>
						</thead>
						
						<?php foreach ($table_eight as $target_year => $eight_values) { ?>
							<tbody id="<?php echo('tbody_'.$target_year); ?>">
								<?php $eight=0; foreach ($eight_values['data'] as $eight_key => $eight_value) {$eight++;
									$sec_name = $eight_value['section_name'];
									$detail = $eight_value['detail'];
									$purchase_date = $eight_value['purchase_date'];
									$purchase_date = ($purchase_date[7] == '-') ? (substr($purchase_date, 0, -1)) : $purchase_date;
									$purchase_date =  ($purchase_date == '') || ($purchase_date == '0000-00') ? '' : date("Y-m", strtotime($purchase_date));
									$lease_period = $eight_value['lease_period'];
									$amount = number_format($eight_value['amount']/1000, 1);
									// $hid_amt = number_format($eight_value['amount']/1000, 3);
									$hid_amt = ($eight_value['amount']/1000);
								 ?>
								<tr id="<?php echo('tr_'.$eight); ?>">
									<?php if($eight == 1) { ?>
									<td class="investment" id="<?php echo('year_'.$eight); ?>"><?php echo $target_year; ?>
									</td>
									<?php }else { ?>
										<td class="investment" id="<?php echo('year_'.$eight); ?>"><input type="button" class="btn btn-danger removeRow" id="btn_remove_1" name="btn_remove"  value = "<?php echo ('-');?>" <?php echo $disable_save; ?>>
									</td>
									<?php } ?>
									<td class="input-cell investment">
										<select id="<?php echo('select_'.$eight); ?>" class="form-control select_section" name="investment[<?php echo($target_year); ?>][tr_<?php echo($eight);?>][section_name]" <?php echo $disable_save; ?>>
											<option value=""><?php echo "--- Select ---"; ?></option>
											<?php foreach ($section_data as $div_key => $div_value) {?>
												<option value="<?php echo($div_value) ?>"<?php if($div_value == $sec_name) {?> selected <?php } ?>><?php echo $div_value; ?></option>
											<?php } ?>
										</select>
									</td>
									<td class="investment" style="overflow: hidden;">
										<textarea rows="2" class="form-control content" name="investment[<?php echo($target_year); ?>][tr_<?php echo($eight);?>][detail]" style="background-color: #D5F4FF;white-space: normal;padding: 0px 0px 0px 6px !important;border: none;height: 36px;outline: none;" id="<?php echo('detail_'.$eight); ?>" <?php echo $disable_save; ?>><?php echo trim($detail); ?></textarea>
									</td>
									<td class="input-cell investment">
											<input type="text" class="period month <?php if(empty($purchase_date))echo('color');else echo('');?>" id="<?php echo('date_'.$eight); ?>" name="investment[<?php echo($target_year); ?>][tr_<?php echo($eight);?>][purchase_date]" value="<?php if(!empty($purchase_date)){ echo $purchase_date;}else{echo($target_year.'-00');} ?>" style="padding-left: 6px !important;display: inline-table;" <?php echo $disable_save; ?>>
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
									</td>
									<td class="input-cell investment">
										<input type="text" class="lease_period" id="<?php echo('lease_'.$eight); ?>" name="investment[<?php echo($target_year); ?>][tr_<?php echo($eight);?>][lease_period]" value="<?php echo($lease_period); ?>" style="padding-left: 6px !important;" <?php echo $disable_save; ?>>
									</td>
									<td class="input-cell investment">
										<input type="text" class="number amt" id="<?php echo('amtofmoney_'.$eight.'_'.$target_year); ?>" name="investment[<?php echo($target_year); ?>][tr_<?php echo($eight);?>][amt]" value="<?php echo($amount); ?>" <?php echo $disable_save; ?>>
										<!-- hidden	amount -->
										<input type="hidden" class="number amt" id="<?php echo('hid_amtofmoney_'.$eight.'_'.$target_year); ?>" name="investment[<?php echo($target_year); ?>][tr_<?php echo($eight);?>][hid_amt]" value="<?php echo($hid_amt); ?>" <?php echo $disable_save; ?>>
									</td>
								</tr> 
								<?php } ?>
								<tr>
									<td class="adjust-td"><input type="button" class="btn btn-success copyRow" name="btn_add"  value = "<?php echo ('+');?>" <?php echo $disable_save; ?>></td>
									<td colspan="4" class="string type1"><?php echo __("投資キャッシュフローに影響する有形・無形固定資産の計"); ?></td>
									<td class="number type1" id="<?php echo('totofmoney_'.$target_year); ?>" ><?php echo number_format($eight_values['total']/1000, 1); ?></td>
									<!-- hidden total -->
									<td style="display: none;" class="number type1" id="<?php echo('hid_totofmoney_'.$target_year); ?>">
										<?php echo number_format($eight_values['total']/1000, 3); ?>
									</td>
								</tr>
							</tbody>
						<?php } ?>
					   
					</table>
				</div>
				
				<!-- 9th table -->
				<div class="col-lg-5 col-md-5 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
					<label for="nine"><?php echo "9. ".__("キャッシュフロー"); ?></label>
					<div class="table-responsive flex-item">
					<table class="table-bordered table-fixed" id="table_nine">
						<thead class="check_period_table">
							<tr>
								<th class="blank-cell w-160"></th>
								<?php foreach ($all_years as $year): ?>
									<?php 
										$title = '予算';
										if ($year == $last_year) {
											$title = '実績';
										} elseif ($year == $fc_year) {
											$title = '見込';
										}
									 ?>
									<th><?php echo ($year).__($title); ?></th>
								<?php endforeach ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($table_nine as $flow => $flow_data): ?>
								<tr>
									<th colspan="<?php echo(count($all_years)+1) ?>" class="string check_period_table" style="<?php echo($flow_data['border']); ?>padding-left: 5px !important;"><?php echo __($flow); ?></th>
								</tr>
								<?php foreach ($flow_data as $subflow => $subflow_data): ?>
								<tr>
									<th class="pl-40 string" style="<?php echo($subflow_data['border']); ?>"><?php echo __($subflow); ?></th>
									<?php foreach ($all_years as $year): ?>
										<?php if ($admin_level_id != 1 && $year == $last_year && strpos($subflow_data['id'], 'total') === false && $subflow != '税後利益'): ?>
											<td class="number" style="<?php echo($subflow_data['border']); ?>" id="<?php echo($subflow_data['id'].'_'.$year); ?>" ><?php echo number_format($subflow_data['amounts'][$year]/1000, 1); ?></td>
											<td style="display: none;"><input type="text " name="cashflow[<?php echo($year); ?>][<?php echo($flow) ?>][<?php echo($subflow) ?>]" id="<?php echo($subflow_data['id'].'_'.$year); ?>"  value="<?php echo $subflow_data['amounts'][$year]/1000; ?>" <?php echo $disable_save; ?>></td>
										<?php elseif ( $subflow_data['input'] == true || ($year == $last_year && ($subflow== '固定資産' || $subflow== '配当（前期損益）') && $admin_level_id == 1)): ?>
											<td class="input-cell" style="<?php echo($subflow_data['border']); ?>"><input type="text" class="number" id="<?php echo($subflow_data['id'].'_'.$year); ?>"  value="<?php echo number_format($subflow_data['amounts'][$year], 1); ?>" <?php echo $disable_save; ?>>
												<!-- hidden sub flow -->
												<input type="hidden" name="cashflow[<?php echo($year); ?>][<?php echo($flow) ?>][<?php echo($subflow) ?>]" class="number" id="hid_<?php echo($subflow_data['id'].'_'.$year); ?>"  value="<?php echo ($subflow_data['amounts'][$year]); ?>"></td>
										<?php else: ?>
											<td class="number" id="<?php echo($subflow_data['id'].'_'.$year); ?>" ><?php echo number_format($subflow_data['amounts'][$year], 1); ?></td>
											<!-- hidden total -->
											<td style="display: none;" class="number" id="hid_<?php echo($subflow_data['id'].'_'.$year); ?>"><?php echo number_format($subflow_data['amounts'][$year], 3); ?></td>

										<?php endif ?>
									<?php endforeach ?>
								</tr>
								<?php endforeach ?>
								
							<?php endforeach ?>
						</tbody>
					</table>
				   </div>
				</div>
			</div>
		</div>
	</div>

</div>

<br><br>
<?php
	echo $this->Form->end();
?>
