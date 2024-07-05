<!-- Modal -->

<div class="modal fade" id="myPOPModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
	<div id="popup-modal" class="modal-dialog modal-dialog-lg" role="document">
		<!--Modal content-->
	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="exampleModalLongTitle"><?php echo __("Send Mail"); ?></h5>
			<button type="button" class="close" id="closePop" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-12" style="height: 2rem;">
					<div class="errorMail" id="errorMail"></div>
				</div> 
				<div class="col-md-12">
					<div class="form-group autoCplTo">
						<label for="autoCompleteTo" class="col-form-label">
						<?php echo __("To:"); ?>
						</label>
						<textarea id="autoCompleteTo" class="form-control" autocomplete="off" rows="2"></textarea>
					</div>
					<div class="form-group autoCplCc">
						<label for="autoCompleteCC" class="col-form-label">
						<?php echo __("CC:"); ?>
						</label>
						<textarea id="autoCompleteCC" class="form-control" autocomplete="off" rows="2"></textarea>
					</div>
					<div class="form-group autoCplBcc">
						<label for="autoCompleteBcc" class="col-form-label">
						<?php echo __("BCC:"); ?>
						</label>
						<textarea id="autoCompleteBcc" class="form-control" autocomplete="off" rows="2"></textarea>
					</div>
					<div class="form-group">
						<label for="autoCompletetext" class="col-form-label">
						<?php echo __("Subject:"); ?>
						</label>
						<div class="mail_content subject"><?php echo($MailSubject); ?></div>
					</div>
					<div class="form-group">
						<label for="autoCompletetext" class="col-form-label">
						<?php echo __("Body:"); ?>
						</label>
						<textarea class="mail_content body" rows="4" cols="79" disabled><?php echo($MailBody); ?></textarea>
					</div>
				</div>
				
			</div>
		</div>
		<div class="modal-footer" >
			<button type="button" name="btn_mail_ok" id="btn_mail_ok" class="btn btn-secondary btn-success btn-lg" style="padding: 10px 40px;"><?php echo __("OK") ?></button>
		</div>
	</div>
	</div>
</div>

<script type="text/javascript">
	var level_id;
	var cc_level_id;
	var bcc_level_id;
	var submitForm;

    $('.autoCplTo').hide();
	$('.autoCplBcc').hide();
    $('.autoCplCc').hide();

	$("#closePop").on('click',function () {
		$("#myPOPModal").fadeOut("slow");
		$("#myPOPModal").css({"display":"none","padding-right":0});
		$("#autoCompleteTo").val('');
		$("#autoCompleteCC").val('');
		$("#autoCompleteBcc").val('');
		$("#errorMail").text('');
	});
	$("button#btn_mail_ok").on('click',function(){
		$("#errorMail").empty();
		var success = false;
		<?php if(!empty($submit_form_name)) :?>
			submitForm = "#<?php echo $submit_form_name ?>";
		<?php endif ?>
		let count = 0;
		var autoCompleteTo = document.getElementById("autoCompleteTo").value;
		var autoCompleteCC = document.getElementById("autoCompleteCC").value;
		var autoCompleteBcc = document.getElementById("autoCompleteBcc").value;
		var style = $('.autoCplTo').attr('style');

			
		
		var autocompleteToArr = autoCompleteTo.split(",");
		var autocompleteCCArr = autoCompleteCC.split(",");
		var autocompleteBccArr = autoCompleteBcc.split(",");
		var autoCompleteTo = $.map(autocompleteToArr, function(value) {
					return $.trim(value);
				});
		var autoCompleteCC = $.map(autocompleteCCArr, function(value) {
					return $.trim(value);
				});
		var autoCompleteBcc = $.map(autocompleteBccArr, function(value) {
					return $.trim(value);
				});
		if(style.indexOf("none") == -1) {
			
			

			if(!checkNullOrBlank(autoCompleteTo)) {
				var newbr = document.createElement("div");					
				var a	= document.getElementById("errorMail").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("mail recipient"); ?>'])));
				a.style.color = "red";
				a.style.textAlign = "left";
				document.getElementById("errorMail").appendChild(a);
			}else success = true;

			$.each(autoCompleteTo,function(key,value){
				
				if(value != ''){
					if (!validateEmail(value)) {
						count +=1;
					}
				}		
			});
			if(count > 0){
				let newbr = document.createElement("div");
				let a = document.querySelector("#error").appendChild(newbr);
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
						'<?php echo __('Validate Email Format At To'); ?>'
					])));
				a.style.color = "red";
				a.style.textAlign = "left";
				document.querySelector("#errorMail").appendChild(a);
				success = false;
			} else {
				count = 0;
			}

			if(autoCompleteCC != '') {
				// console.log('here');return;
				if(!checkNullOrBlank(autoCompleteCC)) {
					var newbr = document.createElement("div");					
					var a	= document.getElementById("errorMail").appendChild(newbr);
					a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("mail recipient"); ?>'])));
					a.style.color = "red";
					a.style.textAlign = "left";
					document.getElementById("errorMail").appendChild(a);
				}else success = true;

				$.each(autoCompleteCC,function(key,value){
				
					if(value != ''){
						if (!validateEmail(value)) {
							count +=1;
						}
					}		
				});
				if(count > 0){
					let newbr = document.createElement("div");
					let a = document.querySelector("#error").appendChild(newbr);
					a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
							'<?php echo __('Validate Email Format At Cc'); ?>'
						])));
					a.style.color = "red";
					a.style.textAlign = "left";
					document.querySelector("#errorMail").appendChild(a);
					success = false;
				} else {
					count = 0;
				}
			}

			if(autoCompleteBcc != ''){
				if(!checkNullOrBlank(autoCompleteBcc)) {
					var newbr = document.createElement("div");					
					var a	= document.getElementById("errorMail").appendChild(newbr);
					a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("mail recipient"); ?>'])));
					a.style.color = "red";
					a.style.textAlign = "left";
					document.getElementById("errorMail").appendChild(a);
				}else success = true;

				$.each(autoCompleteBcc,function(key,value){
				
					if(value != ''){
						if (!validateEmail(value)) {
							count +=1;
						}
					}		
				});
				if(count > 0){
					let newbr = document.createElement("div");
					let a = document.querySelector("#error").appendChild(newbr);
					a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
							'<?php echo __('Validate Email Format At Bcc'); ?>'
						])));
					a.style.color = "red";
					a.style.textAlign = "left";
					document.querySelector("#errorMail").appendChild(a);
					success = false;
				} else {
					count = 0;
				}
			}		
			
		}else success = true;

		if(success){
			
			$(submitForm).find("#toEmail").val(autoCompleteTo);
			$(submitForm).find("#ccEmail").val(autoCompleteCC);
			$(submitForm).find("#bccEmail").val(autoCompleteBcc);
			$(submitForm).submit();
			$("#myPOPModal").fadeOut("slow");
			$("#myPOPModal").css({"display":"none","padding-right":0});
			loadingPic(); 
			/* loading working for IE start*/
			(function() {	 // function expression closure to contain variables
				var i = 0;
				var pics = [ "<?php echo $this->webroot; ?>img/loading1.gif",
							 "<?php echo $this->webroot; ?>img/loading2.gif",
							 "<?php echo $this->webroot; ?>img/loading3.gif" ,
							 "<?php echo $this->webroot; ?>img/loading4.gif" ];
				var el = document.getElementById('imgLoading');	// el doesn't change
				function toggle() {
					el.src = pics[i];			 // set the image
					i = (i + 1) % pics.length;	// update the counter
				}
				setInterval(toggle, 100);
			})(); 
			/* loading working for IE end*/
		}

	});

	$("#callPop").on('click',function(){
		
		$("#autoCompleteTo").val('');
		$("#autoCompleteCC").val('');
		$("#autoCompleteBcc").val('');
		$("#errorMail").text('');

	});

	function split( val ) {
		return val.split( /,\s*/ );
	}
	function extractLast( term ) {
		return split( term ).pop();
	}
	
	
	$("#autoCompleteTo").autocomplete({

		source: function (request, response) {
			if(!level_id){
				level_id = JSON.parse('<?php echo json_encode($to_level_id); ?>');
			}
			
			var autoCompleteTo = $('#autoCompleteTo').val();
			var search_val = extractLast( autoCompleteTo );
			if (search_val != "") {

				var search_data = {
					searchValue : search_val,
					levelId : level_id,
				};
				$.ajax({
					url: "<?php echo $this->Html->url(array('controller'=>'App','action'=>'autoComplete')) ?>",
					dataType: "json",
					type: "post",
					data: search_data,
					success: function (data) {
						if (data != "") {
							response(data);
							$("#errorMail").text('');
						}
					},
					error: function () {
						$("#errorMail").text('No match found!');
						$(".ui-autocomplete").empty();
					}
				});	 
			} else {
				$(".ui-autocomplete").empty();
				$("#errorMail").text('');
			}
		},
		multiselect: true,
		autoFocus: true,
		select: function( event, ui ) {
			event.preventDefault();

			var terms = split( this.value );
			// remove the current input
			terms.pop();
			// add the selected item
			terms.push( ui.item.value );
			// add placeholder to get the comma-and-space at the end
			terms.push( "" );

			this.value = terms.join( "," );
			
			return false;
		},
		focus: function (event, ui) {
			event.preventDefault();
			return false;
		}

	});

	$("#autoCompleteCC").autocomplete({

		source: function (request, response) {
			if(!cc_level_id){
				cc_level_id = JSON.parse('<?php echo json_encode($cc_level_id); ?>');
			}
			var autoCompleteCC = $('#autoCompleteCC').val();
			var search_val = extractLast( autoCompleteCC );
			
			if (search_val != "") {

				var search_data = {
					searchValue : search_val,
					levelId : cc_level_id,
				};
				$.ajax({
					url: "<?php echo $this->Html->url(array('controller'=>'App','action'=>'autoComplete')) ?>",
					dataType: "json",
					type: "post",
					data: search_data,
					success: function (data) {
						if (data != "") {
							response(data);
							$("#errorMail").text('');
						}
					},
					error: function () {
						$("#errorMail").text('No match found!');
						$(".ui-autocomplete").empty();
						
					}
				});	 
			} else {
				$(".ui-autocomplete").empty();
				$("#errorMail").text('');
			} 
		},
		multiselect: true,
		autoFocus: true,
		select: function( event, ui ) {
			event.preventDefault();

			var terms = split( this.value );
			// remove the current input
			terms.pop();
			// add the selected item
			terms.push( ui.item.value );
			// add placeholder to get the comma-and-space at the end
			terms.push( "" );

			this.value = terms.join( "," );
			
			return false;
		},
		focus: function (event, ui) {
			event.preventDefault();
			return false;
		}
	});
	
	$("#autoCompleteBcc").autocomplete({

		source: function (request, response) {
			if(!bcc_level_id){
				bcc_level_id = JSON.parse('<?php echo json_encode($bcc_level_id); ?>');
			}
			var autoCompleteBcc = $('#autoCompleteBcc').val();
			var search_val = extractLast( autoCompleteBcc );
			
			if (search_val != "") {

				var search_data = {
					searchValue : search_val,
					levelId : bcc_level_id,
				};
				$.ajax({
					url: "<?php echo $this->Html->url(array('controller'=>'App','action'=>'autoComplete')) ?>",
					dataType: "json",
					type: "post",
					data: search_data,
					success: function (data) {
						if (data != "") {
							response(data);
							$("#errorMail").text('');
						}
					},
					error: function () {
						$("#errorMail").text('No match found!');
						$(".ui-autocomplete").empty();
						
					}
				});	 
			} else {
				$(".ui-autocomplete").empty();
				$("#errorMail").text('');
			} 
		},
		multiselect: true,
		autoFocus: true,
		select: function( event, ui ) {
			event.preventDefault();

			var terms = split( this.value );
			// remove the current input
			terms.pop();
			// add the selected item
			terms.push( ui.item.value );
			// add placeholder to get the comma-and-space at the end
			terms.push( "" );

			this.value = terms.join( "," );
			
			return false;
		},
		focus: function (event, ui) {
			event.preventDefault();
			return false;
		}
	});
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
