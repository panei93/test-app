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
				<div class="col-md-12">
					<div class="errorMail" id="errorMail"></div>
				</div> 
				<div class="col-md-12">
					<div class="form-group">
						<label for="autoCompleteTo" class="col-form-label">
						<?php echo __("To:"); ?>
						</label>
						<textarea id="autoCompleteTo" class="form-control" autocomplete="off" rows="2"></textarea>
					</div>
					<div class="form-group">
						<label for="autoCompleteCC" class="col-form-label">
						<?php echo __("CC:"); ?>
						</label>
						<textarea id="autoCompleteCC" class="form-control" autocomplete="off" rows="2"></textarea>
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
						<div class="mail_content body"><?php echo($MailTitle.$MailBody); ?>
						</div>
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

	$("#closePop").on('click',function () {
		$("#myPOPModal").fadeOut("slow");
		$("#myPOPModal").css({"display":"none","padding-right":0});
		$("#autoCompleteTo").val('');
		$("#autoCompleteCC").val('');
		$("#errorMail").text('');
	});
	$("button#btn_mail_ok").on('click',function(){
		
		$("#errorMail").empty();
		var success = true;
		var submitForm = "#<?php echo $submit_form_name ?>";

		var autoCompleteTo = document.getElementById("autoCompleteTo").value;
		var autoCompleteCC = document.getElementById("autoCompleteCC").value;

		if(!checkNullOrBlank(autoCompleteTo)) {

			var newbr = document.createElement("div");					
			var a	= document.getElementById("errorMail").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("mail recipient"); ?>'])));
			a.style.color = "red";
			a.style.textAlign = "left";
			document.getElementById("errorMail").appendChild(a);					
			success = false;	

		}
		if(success){
			$(submitForm).find("#toEmail").val(autoCompleteTo);
			$(submitForm).find("#ccEmail").val(autoCompleteCC);
			$(submitForm).find("#mailSubj").val(<?php echo json_encode($MailSubject) ?>);
			$(submitForm).find("#mailTitle").val(<?php echo json_encode($MailTitle) ?>);
			$(submitForm).find("#mailBody").val(<?php echo json_encode($MailBody) ?>);

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
		$("#errorMail").text('');

	});

	function split( val ) {
		return val.split( /,\s*/ );
	}
	function extractLast( term ) {
		return split( term ).pop();
	}
	
	var level_id;
	$("#autoCompleteTo")
		.autocomplete({
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
					url: "<?php echo $this->Html->url(array('controller'=>'App','action'=>'autoCompleteCall')) ?>",
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

	var cc_level_id;
	$("#autoCompleteCC")
		.autocomplete({
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
					url: "<?php echo $this->Html->url(array('controller'=>'App','action'=>'autoCompleteCall')) ?>",
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
						// var index=autoCompleteTo.lastIndexOf(search_val);
						// $('#autoCompleteTo').val(autoCompleteTo.substring(0,index))
						// ;
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
