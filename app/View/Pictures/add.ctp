<style type="text/css">

	.jconfirm.jconfirm-material .jconfirm-box div.jconfirm-title-c {
		font-size: 19px;
	}

	button.btn_sumisho.file_sap {
		padding: 9px 8px !important;
	}

	#load{
     position: fixed;
     left: 0px;
     top: 0px;
     width: 100%;
     height: 100%;
     z-index: 9999;
     background: rgb(50, 50, 50) no-repeat 50% 50%;
     filter: alpha(opacity=10);
     background:url("<?php echo $this->webroot; ?>img/loading.gif") no-repeat center center rgba(0,0,0,0.45)
   }

   span.jconfirm-title {
	   font-size: 18px !important;
	}

	.jconfirm-box-container{
		margin-left: unset !important;
	}
</style>
<script type="text/javascript">
	
	var dupLength = 0;
	var nameLength = 0;
	var overlenCount = 0;
	var overSizes = 0;

	//when click model box of 'X' button, hidden loading.
	$(document).on('click', "div.jconfirm-closeIcon", function(event) {
     	document.getElementById('overlay').style.display="none";
   		document.getElementById('contents').style.visibility="hidden";
	});

	$(document).ready(function() {

	   	document.getElementById('overlay').style.display="none";
       	document.getElementById('contents').style.visibility="hidden";

      	var datacontent = []; // Create Globel raviable for duplicate "No" condition.

      /** when click Browse btn, clear all of view data.**/
		$(document).on("click", "#multiPicUpload", function(e) { 	
			//fixed error => not working on input type file if file name is same 
			$("#multiPicUpload").val('');
			$("#error").empty();
			$('#totalCount').empty();
			$('#upd-file-name').empty();
			$('.pic-remove').empty();
			$('#remove-id-hide').val('');
			$('#picSuccessMessage').empty();
			$('#sess_error').empty();
			
			datacontent = [];
			removeId = [];  //clear duplicate and remove of array.
			
		});

		//when save, pic size is less then 1KB, to show error msg.
		var PictureSize0 = "<?php if(!empty($PictureSize0)){echo $PictureSize0;}else{echo '';}  ?>";

		if(PictureSize0 == "PictureSize0"){

			$.ajax({

				url : "<?php echo $this->webroot ?>PictureUpload/SizeZeroError",
				type: 'post',
				dataType: 'json',
				success: function(data) {
				    
					var str = data.toString();
					var output =  str.split(',').join(" , ");
				    
				    $.confirm({
				        title: '<?php echo __("画像サイズは1KB未満にしないでください。"); ?>',
				        icon: 'fas fa-exclamation-circle',
				        type: 'blue',
				        typeAnimated: true,
				        animateFromElement: true,
				        closeIcon: true,
				        columnClass: 'medium',
				        animation: 'top',
				        draggable: false,
				        content: output,
				        buttons: {   
				                 ok: {
				                     text: "<?php echo __("はい"); ?>",
				                     btnClass: 'btn-info',
				                     action: function(){
				                          
				                        }
				                
				                    }
				                },
				        theme: 'material',
				        animation: 'rotateYR',
				        closeAnimation: 'rotateXR'
				    });

				},
				error: function(e) {
				    console.log(e);
				}

			}); 

		}

		var names = [];
		var uploadFiles = []; // all of upload pic
		var removeId = []; // duplicate and remove arr to store in hidden text.
		
		$("#PicRemoveLink").hide();

		$("#multiPicUpload").on("change", function(event) {
			
			$("#error").empty();
			
		 	if($(this).val() != '') {
		 		
		 		var files = event.originalEvent.target.files; //get picture files

		 		Object.keys(files).forEach(function (key) { // loop file 
		 			
		 			uploadFiles.push(files[key]);
		 			
		 		});

		 		/*** 
		 			$.map => Description: Translate all items in an array or object to new array of items.
		 			return file of name column!
				***/
				var overLengthName = []; // to show err msg for over 20 char:
				var namesArr = new Array(); // to check, are duplicated uploaded name 2 or more name?

		 		names = $.map(files, function(val) { 

		 			var nameCnt = val.name;
		 			var nameSplit = nameCnt.split(".");
		 			var nameLen = nameSplit[0].length;
		 			
		 			if(nameLen > 10){
						overLengthName.push(nameCnt);
						return val.name; 
					}else{
						
						var nameSplitValue = nameSplit[0];
		 				var leadingZeroName = pad(nameSplitValue,'10');
						namesArr.push(leadingZeroName);
							
							
						var namelastSplit = leadingZeroName+'.'+nameSplit[1]; 
						
						return namelastSplit; 
					}	
		 			
		 		});
				
		 		sizes = $.map(files, function(val) { 
		 			return val.size; 
		 		});
		 		var total = 0;
		 		nameLength = names.length; //count of pic name
		 		
		 		// if more then 20 pic, show error msg
		 		if(nameLength <= '100'){

		 			for(let i = 0; i < nameLength; i++){
		 				total += sizes[i];
		 			}
		 			
		 			if(total > 10485760) { //10485760(Byte)(10MB)

		 				overSizes = total;
						$("#upd-file-name").empty();  
		            	$("#totalCount").empty();
		            	$("#PicRemoveLink").empty();
		            	$("#sessionError").empty(); 

						var newbr = document.createElement("div");                      
			        	var a     = document.getElementById("error").appendChild(newbr);
			       		a.appendChild(document.createTextNode(errMsg(commonMsg.JSE031)));
			      		document.getElementById("error").appendChild(a);  
			      	                    
			      		errorFlag = false; 
			      		document.getElementById('contents').style.visibility="hidden";
			      		document.getElementById('overlay').style.display="hidden";  

					}else{

						var recipientsArray = namesArr.sort(); // alphabetical sort
						var duplicateArray = []; // if dupl, to store.

						for (var i = 0; i < recipientsArray.length - 1; i++) {
						   if (recipientsArray[i + 1] == recipientsArray[i]) {
						        duplicateArray.push(recipientsArray[i]); // duplicate pic
						   }
						}
						
						dupLength = duplicateArray.length;
						overlenCount = overLengthName.length;

						if(dupLength != '0'){ 
							//upload pic name duplicate error
							var newbr = document.createElement("div");                      
			           		var a  = document.getElementById("error").appendChild(newbr);
			             	a.appendChild(document.createTextNode(errMsg(commonMsg.JSE033)));
			            	document.getElementById("error").appendChild(a);

			            	$("#upd-file-name").empty();  
			            	$("#totalCount").empty();
			            	$("#PicRemoveLink").empty();
			            	$("#sessionError").empty();  
			            	

						}else if(overlenCount != '0'){
							//upload pic name over 20 error
							var newbr = document.createElement("div");                      
					        var a     = document.getElementById("error").appendChild(newbr);
					        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE036)));
					     		document.getElementById("error").appendChild(a);   
					       
					        $.each(overLengthName, function (index, value) {								
									
								var newbr = document.createElement("div");                      
						        var a     = document.getElementById("error").appendChild(newbr);
						        a.appendChild(document.createTextNode(errMsg(value)));
						     		document.getElementById("error").appendChild(a);   

								});
				                            
					      	$("#upd-file-name").empty();  
			            	$("#totalCount").empty();
			            	$("#PicRemoveLink").empty();
			            	$("#sessionError").empty(); 
			            	
						}else{ 
							
							// to check names are already exist in db
							var myJSONString = JSON.stringify(names);
				 		
					 		$.ajax({

					 			data : {myJSONString: myJSONString },
				                url  : "<?php echo $this->webroot ?>PictureUpload/OverwritePictureConfirm",
				                type : 'post',
				                dataType: 'json',
				                success: function(data) {

				                	datacontent = data.content;
				                	// if pic name is exist, return name array
				                	if(datacontent != "empty"){

				                		var str = datacontent.toString();              		
				                    	var output =  str.split(',').join(" , ");  
				                    	//confirm overwrite or not            	
				                 		$.confirm({
					                        title: '<?php echo __("この写真は既にアップロードされています。上書きしますか？"); ?>',
					                        icon: 'fas fa-exclamation-circle',
					                        type: 'blue',
					                        typeAnimated: true,
					                        animateFromElement: true,
					                        closeIcon: false,
					                        columnClass: 'medium',
					                        animation: 'top',
					                        draggable: false,
					                        content: output,
					                        buttons: {   									
										            ok: {									
										                text: '<?php echo __("はい");?>',		
										                btnClass: 'btn-info',
										                action: function(){	

										                	   datacontent = [];
										                	   removeId = [];	//clear 	
										                	   overSizes = 0;	
										                     console.log('the user clicked ok');	
										                    
										                	}							
										               							
														},     									
														cancel : {									
										                    text: '<?php echo __("いいえ");?>',			
										                    btnClass: 'btn-default',		
										                    action: function(){
									              				//dup arr name push in removeId 
																	removeId.push(datacontent);
																	picture_list(names,datacontent);
																	overSizes = 0;
				
															}	

														}											
													},	
					                        theme: 'material',
					                        animation: 'rotateYR',
					                        closeAnimation: 'rotateXR'

					                    });	
				                	}
				                },
				               error: function(e) {
				                    console.log(e);
				               }

				            }); 

					 		picture_list(names,'');

						}

					}

		 		}else{
		 			// if more then 20 pic, show error msg 
		 			var newbr = document.createElement("div");                      
	           		var a     = document.getElementById("error").appendChild(newbr);
	             	a.appendChild(document.createTextNode(errMsg(commonMsg.JSE035)));
	            	document.getElementById("error").appendChild(a);

	            	$("#upd-file-name").empty();  
	            	$("#totalCount").empty();
	            	$("#PicRemoveLink").empty();
	            	$("#sessionError").empty();  
		 		}

	        }else if($(this).val() == ''){
	            location.reload();
	        }

		});

		$(document).on("click", ".pic-remove", function(e) { //if remove click

			e.preventDefault(); //the default action of the event will not be triggered.

			var removePic = $(this).attr('href'); //get remove name
			
			for( var i = 0; i < uploadFiles.length; i++){ // loop with all pic length

			   if(uploadFiles[i]['name'] == removePic) {

			      uploadFiles.splice(i, 1); 
			      //splice() method returns the removed item(s) in an array
			   }

			   if(names[i] == removePic){
			   	names.splice(i,1); //splice removePic form names list
			   }
			}
			
			removeId.push(removePic); // to push hidden text
			
			if(datacontent.length != '0'){ // check duplicate name exist or not
				picture_list(names,datacontent);
			}else{
				picture_list(names,"");
			}	
		
		});

		/**
			This function prepare uploaded pic, remove link and total uploaded pic count.
		**/
		function picture_list(files,hidePicName) {
			
			$("#upd-file-name").empty();
		 	$("#totalCount").empty();
		 	$("#PicRemoveLink").empty(); 	
		 	overSizes = 0;
		 	
			var list = '';
	 		var count = 0; 
			var removeLik = '';

	 		names.forEach(function (item, index) { //loop with name list

				count++;
				
				//check duplicate name exit or not
				if(hidePicName.length != '0'){
					/** jQuery.inArray 
						Search for a specified value within an array and return its index (or -1 if not found).
					**/
					if(jQuery.inArray(item, hidePicName) !== -1) {
						//exist
						count--;

					} else {
						//not exist 
						list += "<span>"+item+"</span><br/>";
						removeLik += "<a href='"+item+"' class='pic-remove'>"+"<?php echo __('Remove'); ?>"+"</a><br/>";
						
					}
				}else{
					
					list += "<span>"+item+"</span><br/>";
					removeLik += "<a href='"+item+"' class='pic-remove'>"+"<?php echo __('Remove'); ?>"+"</a><br/>"; 
				}

			});
	 		
	 		$("#PicRemoveLink").append(removeLik);
			$("#upd-file-name").append(list);
			
			if(count != 0){
				
				var language = "<?php echo ($this->Session->check('Config.language'))? $this->Session->read('Config.language') : 'jpn'; ?>";

				if(language == 'eng'){
					var uploadCnt = "Total upload "+count+" files.";
					
				}else if(language == 'jpn'){
					var uploadCnt = "合計アップロード "+count+" ファイル。";
					
				}	
				
				$("#totalCount").append(uploadCnt);

			}else if(count == 0){
							
				location.reload(); //all remove
			}
            $("#PicRemoveLink").show();
            $("#remove-id-hide").val(removeId);
		}

		function pad(num, size) {
		    var s = num+"";
		    while (s.length < size) s = "0" + s;
		    return s;
		}


	});
	
	function Upload(){
	
		$("#error div").empty();
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';		
		document.getElementById("sessionError").innerHTML = '';

		document.getElementById('contents').style.visibility="visible";
      	document.getElementById('overlay').style.display="block"; 

		var picture = document.getElementById('multiPicUpload');
		
		var picturelength = picture.files.length;
		
		var errorFlag = true;
		var pictureNameArr = new Array();
		var allowedTypes = Array('gif','png' ,'jpg','jpeg','JPEG','JPG','PNG','GIF'); 
		
		if(overSizes > 10485760){
		
			 var newbr = document.createElement("div");                      
	         var a     = document.getElementById("error").appendChild(newbr);
	         a.appendChild(document.createTextNode(errMsg(commonMsg.JSE024)));
	         document.getElementById("error").appendChild(a);                      
	         errorFlag = false; 
	         document.getElementById('contents').style.visibility="hidden";
	         document.getElementById('overlay').style.display="none";
        
		}else if(overlenCount != '0'){ // picture name of length exceeded 20 characters
			
			var newbr = document.createElement("div");                      
	        var a     = document.getElementById("error").appendChild(newbr);
	        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE024)));
	        document.getElementById("error").appendChild(a);                      
	        errorFlag = false; 
	        document.getElementById('contents').style.visibility="hidden";
	        document.getElementById('overlay').style.display="none";
        
		}else if(dupLength != '0' || nameLength > 100){ //if upload file name is duplicate, show error
		
			var newbr = document.createElement("div");                      
	        var a     = document.getElementById("error").appendChild(newbr);
	        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE024)));
	        document.getElementById("error").appendChild(a);                      
	        errorFlag = false; 
	        document.getElementById('contents').style.visibility="hidden";
	        document.getElementById('overlay').style.display="none";  
          
		}else if(picturelength < '1') {
			
            var newbr = document.createElement("div");                      
            var a     = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE024)));
            document.getElementById("error").appendChild(a);                      
            errorFlag = false; 
            document.getElementById('contents').style.visibility="hidden";
            document.getElementById('overlay').style.display="none";  
             
        }else{
        
        	for(var i=0; i<picturelength; i++) {
        		

				var file_ext = picture.files[i].name.split('.').pop();
	
				var formatErr = inArray(file_ext, allowedTypes); 

				if( formatErr == false ){
					
					$("#upd-file-name").empty();
					$(".pic-remove").empty();
					$("#totalCount").empty();

					var newbr = document.createElement("div");                      
	        		var a     = document.getElementById("error").appendChild(newbr);
		          	a.appendChild(document.createTextNode(errMsg(commonMsg.JSE037,[file_ext])));
	         		document.getElementById("error").appendChild(a);  
	         	                    
	         		errorFlag = false; 
	         		document.getElementById('contents').style.visibility="hidden";
	         		document.getElementById('overlay').style.display="none";  
	         		break;
				}

			}
        }
        if(errorFlag) {
        
          	var isOkClicked = false; //prevent double click on ok button
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
			               		//prevent double click on ok button
								if(isOkClicked == false) { 
									isOkClicked = true;									
				              		document.forms[0].action = "<?php echo $this->webroot; ?>PictureUpload/SavePicture";
									document.forms[0].method = "POST";
									document.forms[0].submit();
									return true;	
									}					
			               		}									
							},     									
							cancel : {									
				                    text: '<?php echo __("いいえ");?>',			
				                    btnClass: 'btn-default',		
				                    action: function(){									
					                    	console.log('the user clicked cancel');	
					                    	document.getElementById('contents').style.visibility="hidden";
         									document.getElementById('overlay').style.display="none";			
				                    	}

			                		}									
							},									
		            theme: 'material',									
		            animation: 'rotateYR',									
		            closeAnimation: 'rotateXR'									
				}); 
            
        }

	}

	function inArray(needle, haystack) {
	   var length = haystack.length;
	   for(var i = 0; i < length; i++) {
	      if(haystack[i] == needle) return true;
	   }
	   return false;
	} 
	
</script>

<div id="overlay">
    <span class="loader"></span>
</div>
<div id="contents"></div>

<?php echo $this->Form->create(false,array('url'=>'','id'=>'upd-form','class'=>'', 'enctype' => 'multipart/form-data')); 
?>

<div class="container">
	<div class="row">
		<h3> <?php echo __("写真のアップロード"); ?></h3>
		<hr>
	</div>
	<br>
	<div class="errorSuccess">
      <div class="success" id="success"><?php echo $successMsg;?></div>
      <div class="error" id="error"><?php echo $errorMsg;?></div>
      <div id = "sessionError">
     	<?php if($this->Session->check('Message.picSuccess')): ?>
            <div class="success" id="sess_error">
               <?php echo $this->Flash->render("picSuccess"); ?>
            </div>
         <?php endif; ?>
         <?php if($this->Session->check('Message.picError')): ?>
            <div class="error" id="sess_error">
               <?php echo $this->Flash->render("picError"); ?>
            </div>
         <?php endif; ?>
      </div>      
   	</div>
	<br>
	<div class="row">
		<div class="col-md-2">
	      <label style="color:white" id="btn_browse"><?php echo __("Browse"); ?> 
	        	<input type="file" name="multiPicUpload[]" accept="image/x-png,image/gif,image/jpeg" id="multiPicUpload" multiple  >
	      </label>             
		</div>
		<div class="col-md-3 col-xs-5" id = "totalCount"></div>
		<div class="col-md-7">
			<button type="button" class="btn_sumisho file_sap" onClick = "Upload();"><?php echo __('アップロード');?></button>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col-md-3 col-xs-5"><div id="upd-file-name"></div></div>
      <div class="col-md-4 col-xs-4" id = "PicRemoveLink"></div> 
	</div>
	<br><br>
   <input type="hidden" name="upd-hide" value="" id="upd-hide">
   <input type="hidden" name="remove-id-hide" value="" id="remove-id-hide">
   <div id = "upd-hide"></div>
</div>

<?php echo $this->Form->end(); ?>
