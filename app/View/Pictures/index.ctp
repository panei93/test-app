<style>

	.table .table-bordered .picture_list td.table-middle, .table th {
    	min-width: 100px;    	
	}
	.fl-scrolls{
        z-index: 1 !important;
    }
    th{
  		background-color: #D5EADD;
	}
	table, td {
	  text-align: left;
	  vertical-align: middle !important;	 
	}
	th{
		text-align: center;
	  vertical-align: middle !important;
	}
	.fl-scrolls {
		margin-bottom: 40px;/* modify floating scroll bar */
	}
	
	.jconfirm-box-container{
		margin-left: unset !important;
	}

	.custom-row{
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 10px;
	}

	.custom-row .form-group{
		margin-right: 10px;
	}

	.custom-row .custom-msgfont{
		font-family: var(--font_family);
		font-weight: bold;
		color: green;
	}

	/*** 
	** Style for multiple picutre upload
	***/
	.jconfirm.jconfirm-material .jconfirm-box div.jconfirm-title-c {
		font-size: 19px;
	}

	button.btn_sumisho.file_sap {
		padding: 8px 8px !important;
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
	//gloabal variable for picutre upload
	var dupLength = 0;
	var nameLength = 0;
	var overlenCount = 0;
	var overSizes = 0;
	//end gloabal variable for picutre upload

	$(function() {  

		$('[data-toggle="tooltip"]').tooltip();

		/* float thead */
		if($('#tbl_picturelist').length > 0) {
			var $table = $('#tbl_picturelist');
		    $('table').floatThead({
		    position: 'absolute'
		    	
		  });
		}

		if($(".tbl-wrapper").length) {
			$(".tbl-wrapper").floatingScroll();
		}

		$("#status").val("<?php echo $search_data['status']?>");
		$("#asset_no").val("<?php echo $search_data['asset_no_form']?>");
		$("#check_img").val("<?php echo $search_data['check_img']?>");

		$(".paging a").click(function(e) {
			var url = $(this).attr('href');
			if(url == undefined) {
				return false;
			}
			loadingPic(); 
		});

		/***
		 * Multiple Picture Upload function in document ready
		 */
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
		//end Multiple Picture Upload function in document ready

		/****
		 * Single Picture Upload function in document ready
		 ***/
		//if click upload image link, open image upload dialog box
		$('a.img_upload_url').click(function(){ 
			cancel_upload_img();
			let asset_no = $(this).attr('id');
			$('#hid_asset_no').val(asset_no);
			$('#img_upload').trigger('click'); 
		});

		//validation for single upload image
		$('#img_upload').change(function(event){
			let asset_no = $('#hid_asset_no').val();
			let picture = document.getElementById('img_upload');

			//if cancel img selection dialog box
			if(!picture.files[0]){
				cancel_upload_img();
				return false;
			}
			//check size
			let img_size = picture.files[0].size;
			if(img_size > 10485760) { //10485760(Byte)(10MB) 

				var newbr = `<div>${errMsg(commonMsg.JSE031)}</div>`;                      
				document.getElementById("error").innerHTML = newbr;
								
				errorFlag = false; 
				document.getElementById('contents').style.visibility="hidden";
				document.getElementById('overlay').style.display="hidden";  
				window.scrollTo(0, 0);
				cancel_upload_img();
				return false;
			}

			//check type
			let img_ext = picture.files[0].name.split('.').pop();
			let allowedTypes = ['gif','png' ,'jpg','jpeg','JPEG','JPG','PNG','GIF'];
			if(!allowedTypes.includes(img_ext)){  

				var newbr = `<div>${errMsg(commonMsg.JSE037, [img_ext])}</div>`;                      
				document.getElementById("error").innerHTML = newbr;
				
				errorFlag = false; 
				document.getElementById('contents').style.visibility="hidden";
				document.getElementById('overlay').style.display="hidden";  
				window.scrollTo(0, 0);
				cancel_upload_img();
				return false;
			}

			if($('#' + asset_no).siblings().length < 1){
				$('#' + asset_no)
				.text(asset_no + '.' + img_ext)
				.after(` <i class="fa fa-check-square text-success fa-lg" style="cursor: pointer" onclick="upload_img()"></i> <i class="fa fa-times-square text-danger fa-lg" style="cursor: pointer" onclick="cancel_upload_img()"></i>`);
			}
		});
		//end single picture upload function
	});
  
	function PictureDelete(id){
		var ok_click_flg = false;
		document.getElementById("error").innerHTML   = '';
		document.getElementById("success").innerHTML   = '';

		document.getElementById("picture_id").value = id;
		var errorFlag = true;

		var path = window.location.pathname;
		var page = path.split("/").pop();
		document.getElementById('hid_page_no').value = page;

		if(errorFlag) {
			
			$.confirm({
				title: "<?php echo __('削除確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'red',
				typeAnimated: true,
				animateFromElement: true,
				animation: 'top',
				closeIcon: true,
				columnClass: 'medium',
				draggable: false,
				content: "<?php echo __('データを削除してよろしいですか。'); ?>",
				buttons: {   
				    ok: {
				        text: "<?php echo __('はい'); ?>",
				      btnClass: 'btn-info',
				         action: function(){
							loadingPic();
				             if(ok_click_flg == false){
								loadingPic(); 
				            	 ok_click_flg = true;
				                 document.forms[1].action = "<?php echo $this->webroot; ?>Pictures/delete";
				                 document.forms[1].method = "POST";
				                 document.forms[1].submit();
				             }
				        }
				   	},   
					cancel : {
				    text: "<?php echo __('いいえ'); ?>",
				       btnClass: 'btn-default',
				       action: function(){
							$("#overlay").hide();
				   		}
				   	}
				  },
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		}
	}

	function SearchPicture(){
		let check_img = $("#check_img").val();
		let status = $("#status").val();
		let asset_no = $("#asset_no").val();

		loadingPic(); 
		
		//if filter in 3 form empty condition, post to index
		//else filter with something value in form condition, post to search method
		let url = (!check_img && !status && !asset_no) ? "<?php echo $this->webroot; ?>Pictures" : "<?php echo $this->webroot; ?>Pictures/search_pictureList" ;

		document.forms[1].action = url;		
		document.forms[1].method = "POST";
		document.forms[1].submit();

		return true;	
	}
	/*  
	*	Show hide loading overlay
	*	@Zeyar Min  
	*/
	function loadingPic() { 
		$("#overlay").show();
		$('.jconfirm').hide();  
	}

	/****
	 * Multiple Picutre upload functions
	 */
	//when click model box of 'X' button, hidden loading.
	$(document).on('click', "div.jconfirm-closeIcon", function(event) {
     	document.getElementById('overlay').style.display="none";
   		document.getElementById('contents').style.visibility="hidden";
	});

	
	function Upload(){

		var errorFlag = validate_img_upload();

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

	function validate_img_upload(){
		var picture = document.getElementById('multiPicUpload');
		var picturelength = picture.files.length;

		$("#error div").empty();
		document.getElementById("error").innerHTML = '';
		document.getElementById("success").innerHTML = '';		
		document.getElementById("sessionError").innerHTML = '';

		document.getElementById('contents').style.visibility="visible";
      	document.getElementById('overlay').style.display="block"; 
		
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

		return errorFlag;
	}

	function inArray(needle, haystack) {
	   var length = haystack.length;
	   for(var i = 0; i < length; i++) {
	      if(haystack[i] == needle) return true;
	   }
	   return false;
	} 
	//end Multiple Picutre upload functions

	/****
	 * Single Picture upload functions
	 */
	function upload_img(){
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

							document.getElementById('contents').style.visibility="visible";
      						document.getElementById('overlay').style.display="block"; 

							let picture = document.getElementById('img_upload');	
			
							//prevent double click on ok button
							if(isOkClicked == false) { 
								isOkClicked = true;									
								document.forms[2].action = "<?php echo $this->webroot; ?>PictureUpload/upload_img";
								document.forms[2].method = "POST";
								document.forms[2].submit();
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

	function cancel_upload_img(){
		let asset_no = $('#hid_asset_no').val();
		$('#img_upload').val('');
		$('#' + asset_no).text('Upload Image');
		$('#' + asset_no).siblings().remove();

	}
	//End single picture upload functions
</script>
 
<div id="overlay">
	<span class="loader"></span>
</div>

<h3><?php echo __("画像リスト");?></h3><hr>

<div class="row">
	<div class="col-sm-12">
		<div class="success" id="success"><?php echo ($this->Session->check("Message.PictureListSuccess"))? $this->Flash->render("PictureListSuccess") : '';?></div>
		<div class="error" id="error"><?php echo ($this->Session->check("Message.PictureListError"))? $this->Flash->render("Error") : '';?><?php echo ($this->Session->check("Message.PictureListDeleteFail"))? $this->Flash->render("PictureListDeleteFail") : '';?></div>
	</div>
</div>

<!-- Multiple image upload Area -->
<div id="contents"></div>
<!-- form 0  -->
<?php echo $this->Form->create(false,array('url'=>'','id'=>'upd-form','class'=>'', 'enctype' => 'multipart/form-data')); ?>

<div>

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
	<div class="multi-upload-container">

		<label style="color:white" id="btn_browse"><?php echo __("Browse"); ?> 
			<input type="file" name="multiPicUpload[]" accept="image/x-png,image/gif,image/jpeg" id="multiPicUpload" multiple  >
		</label>             

		<button type="button" class="btn_sumisho file_sap" onClick = "Upload();"><?php echo __('アップロード');?></button>

		<span id = "totalCount"></span>
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
<!-- end form 0  -->
<!-- End Multiple image upload Area  -->

<!-- Form 1 -->
<?php echo $this->Form->create(false,array('url'=>'index','type'=>'post')); ?>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >

		<!-- Area upper table  -->
		<div class="custom-row">
			<!-- Show data count area  -->
			<div class="custom-msgfont" id="total_row"><?=$count;?></div>
			<!-- End Show data count area  -->

			<!-- filter area  -->
			<div class="form-inline">

				<div class="form-group">
					<label for="check_img">
					<?php echo __('Image Status');?>
					</label>
					
					<select class="form-control" id="check_img" name="check_img">
						<option value="">--- <?php echo __("Select Image Status"); ?> ---</option>
						<option value="1"><?php echo __("Image Exist"); ?></option>
						<option value="2"><?php echo __("Image not Exist"); ?></option>
					</select>
				</div>

				<div class="form-group">
					<label for="status">
					<?php echo __('Status');?>
					</label>
					
					<select class="form-control" id="status" name="status">
						<option value="">--- <?php echo __("Select Status"); ?> ---</option>
						<option value="1"><?php echo __("Exist"); ?></option>
						<option value="2"><?php echo __("Not Exist"); ?></option>
					</select>
				</div>

				<div class="form-group">
					<label for="asset_no">
					<?php echo __('資産番号');?>
					</label>
					<input type="text" class="form-control" name="asset_no" id="asset_no" value="" />	
				</div>
				
				<input type ="button" class="btn" id="search" onClick = "SearchPicture();" value = "<?php echo __('Search');?>" />
			</div>
			<!--End filter area  -->
		</div>
		<!--End area upper table  -->

		<!-- table area  -->
		<?php if(!empty($picturelist)){ ?>

		<div class="table-responsive tbl-wrapper">
			<table class="table table-bordered tbl_sumisho_inventory tbl_picturelist" id="tbl_picturelist">
				<thead>
					<tr>
					<th><?php echo __("数");?></th>
					<th><?php echo __("画像");?></th>
					<th width="15%"><?php echo __("画像の名前");?></th>
					<th><?php echo __("資産番号");?></th>
					<th><?php echo __("資産名称");?></th>
					<th><?php echo __("資産番号と同じ");?></th>
					<th></th>      
					</tr>
				</thead>
				<tbody>
					<?php foreach ($picturelist as $value): ?> 
						
						<tr>
							<td style="text-align: right;">
								<?=h($value['pictures']['number'])?>
							</td>
							<td style="text-align: center;">
								<a href='#' class='img-deco'><img src="<?= $value['pictures']['url'] ?>" /></a>
							</td> 
							<td>
								<?php if($value['pictures']['picture_name']): ?>
									<?= $value['pictures']['picture_name'].".".$value['pictures']['picture_type'] ?>
								<?php else: ?>
									<div class="single-upload-container">
										<a href="javascript:void(0)" id="<?= $value['pictures']['asset_no'] ?>" class="img_upload_url">Upload Image</a>
									</div>
								<?php endif; ?>
							</td>
							<td>
								<?= $value['pictures']['asset_no'] ? $value['pictures']['asset_no'] : 'N/A' ?>
							</td>
							<td>
								<?= $value['pictures']['asset_name'] ? $value['pictures']['asset_name'] : 'N/A' ?>
							</td>
							<td>
								<?= $value['pictures']['asset_no'] ? 'Exist' : 'Not Exist' ?>
							</td>
							<td style="text-align: center;">
						
								<a class="glyphicon glyphicon-trash" href="#" onclick="PictureDelete(<?=h($value['pictures']['id']);?>)"><?php echo __("削除");?></a>
						
							</td>

						<?php endforeach; ?>
						</tr>
				</tbody>    		
			</table>
		</div>

		<?php } else { ?>
		<div class="row">
			<div class="col-sm-12">
				<p class="no-data"><?=$no_data?></p>
			</div>
		</div>
		<?php }?>
		<!-- end table area  -->
	</div>
</div>

</br></br></br>
<!-- pagination -->

<?php if(!empty($picturelist)){ if($query_count > 20){?>
	<div class="col-md-12" style="padding: 10px;text-align: center;margin-bottom: 50px;">
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
<?php }
}?>


<div>
	<input type="hidden" id="picture_id" name="picture_id">
	<input type="hidden" name="hid_page_no" id="hid_page_no" value = "" >       
</div>
<?php
    echo $this->Form->end();
?>
<!-- End Form 1 -->

<!-- Form 2 -->
<?php echo $this->Form->create(false,array('url'=>'','id'=>'img_upload_form','class'=>'', 'enctype' => 'multipart/form-data')); ?>
	<input type="file" name="img" accept="image/x-png,image/gif,image/jpeg" id="img_upload"  >
	<input type="hidden" name="hid_asset_no" id="hid_asset_no" />
<?php echo $this->Form->end(); ?>
<!--End Form 2 -->
