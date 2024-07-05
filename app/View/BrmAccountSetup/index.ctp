<?php       
  echo $this->Form->create(false,array('type'=>'post', 'name' =>'accountsetup' ,'id' =>'accountsetup', 'enctype'=>'multipart/form-data'));
  ?>
<style type="text/css">
	table.acc_setup {
		table-layout: fixed;
	}
	.container {
		margin-bottom: 50px;
	}
	.tab-content {
		margin-bottom: 3rem;
	}
	.remove_filelink {
		text-align: center;
	}
	.file_remove {
		border-bottom: 1px solid !important;
		color: blue !important;
		font-size: 14px !important;
		font-family: monospace !important;
	}
	a.file_remove :hover { 
		text-decoration: none !important;
	}
	.actual_file {
		background-color: #4CAF50;
		border: none;
		color: white;
		padding: 5px 25px;
		text-align: center;
		text-decoration: none;
		display: inline-block;
		font-size: 14px;
		border-radius: 5px;
	}

	.form-horizontal .control-label {
		text-align: left !important;
	}
	.negative {
		color: #f31515;
		text-align: right !important;
	}
	.string {
		text-align: left !important;
	}
	.number {
		text-align: right !important;
	}
	.mail {
		width: 120px;
	}
	.mrbrd_mail {
		width: 200px;
		padding: 7px 25px;
	}
	.row.line.adjust {
		padding-top: 50px;
	}
	.search{
		margin: 10px 0px 15px 0px;
	}
	.deleteall {
		float: right;
	}
	.no-data {
		padding-bottom: 30px;
	}
	.myDragClass, .sorted {
		background-color: #777 !important;
		color: #fff;
	}
	.modal-dialog
	{
		max-height:80%;
	}
	.goup_nav {
    	padding-top: 30px;
    	/*display: flex;*/
	}
	.goup_nav ul {
		display: flex;
	}
	.goup_nav li a {
	    font-weight: bold;
	    color: #000;
	    background: #eee;
	}
	.goup_nav li {
		width: 20%;
		/*display: flex;*/
	}
	#myTabContent {
    	padding: 15px;
	}
	#btn_search, #btn_copy{
		margin-top: -4px;
	}
	.popup_row {
	  padding-bottom: 30px;
	}
	div.goup_nav .nav > li > a{
		padding: 10px 7px;
	}
	select[multiple]{
		height: 34px;
	}
	.amsify-selection-area .amsify-selection-list{
		border-color:  #ccc !important;
	}
	.jconfirm-box-container {
      margin-left: unset !important;
   }
</style>

<script type="text/javascript">

	$(document).ready(function() { 
		var headDepartments = <?php echo json_encode($headDepartments); ?>;
		var headpt_dept = <?php echo json_encode($topLayerNames); ?>;
		var termYears = <?php echo json_encode($termYears); ?>;
		var year = '<?php echo $year; ?>';
		var hq = '<?php echo $hq; ?>';
		/* float thead */
		$.each(headDepartments, function(key, value){
			if($('#tbl_acc_setup_'+key).length > 0) {
				var $table = $('#tbl_acc_setup_'+key);
				$table.floatThead({
				      position: 'absolute'
				});
			}
		})
		
		if($(".tbl-wrapper").length) {
			$(".tbl-wrapper").floatingScroll();
		}
		/* end*/
		var sub_acc_arr = [];
		var acc_arr = [];
		var data_arr = [];
		var data_id = [];
		var dataObj = new Object;
		var galobal_sub_acc_id;

		$('#sub_acc_list.single-select').change(function() {
			var year 	= $('#year_choose').val();
			var head 	= $('#headquarter option:selected').text();

			$("#error").empty();
			$("#success").empty();

			var checkstatus = true;
			var check_head = /[abc]/;					
			
			if(!checkNullOrBlank(year)) {												
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("年度")?>'])));											
				document.getElementById("error").appendChild(a);											
				checkstatus = false;											
			}
			if(check_head.test(head)) {												
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("本部を選択")?>'])));											
				document.getElementById("error").appendChild(a);											
				checkstatus = false;											
			}

			if (checkstatus == true) {
				/* get account multipal name*/
				var year = year;
				var head = head;
				var year = year;
				var head = $('#headquarter option:selected').val();
				var sub_account =$(this).val();

				$("#error").html('');

				GetAccountName(sub_account,year,head);
			} else {
				$('#sub_acc_list.single-select').val('-----Select-----');
			}
		});
		$('#acc_list').change(function() {
			var str = '';
			$( "#acc_list > option").each(function() {
				if ($(this).attr('selected') == 'selected') {

					var id = $(this).val();
					var name = $(this).text();
					acc_arr[id] = name;
				}
			});
		});
		$('#multi_sub_acc_list').change(function() {

			//Work in IE
			$('#multi_sub_acc_list > option').each(function () {
				if ($(this).attr('selected') == 'selected') {
					var id = $(this).val();
					galobal_sub_acc_id = $(this).val();
					var name = $(this).text();
					sub_acc_arr[id] = name;
					console.log(id);
					console.log(name);
				}
			});

			//Not work in IE
			// var str = '';
			// $( "#multi_sub_acc_list option:selected" ).each(function() {
			// 	var id = $(this).val();
			// 	var name = $(this).text();
			// 	sub_acc_arr[id] = name;
			// });
		});

		$("#btn_setup").click(function(e){
			$("#editAccountButton").hide();
			$("#saveAccountButton").show();

			$('.acc_setup .sortable').html('');
			var table_html = '';
			data_arr = [];


			/* validation for account */
			document.getElementById("error").innerHTML   = "";
			document.getElementById("success").innerHTML = "";

			var setup_type = $('.set_acc_radio:checked').val();
			var year 	= $('#year_choose').val();
			var head 	= $('#headquarter option:selected').text();
			var sub_accs = sub_acc_arr;
			var sub_acc = $('#sub_acc_list.single-select option:selected').text();
			var sub_acc_val = $('#sub_acc_list.single-select option:selected').val();
			var accs 	= acc_arr;

			var checkstatus = true;
			var check_reg = /[a-z]/;
			var check_head = /[abc]/;

			if(!checkNullOrBlank(year)) {												
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("年度")?>'])));											
				document.getElementById("error").appendChild(a);											
				checkstatus = false;											
			}
			// else if (!(year >= term[0] && year <= term[1])){
			// 	//year check between term period of year
			// 	var newbr = document.createElement("div");											
			// 	var a = document.getElementById("error").appendChild(newbr);											
			// 	a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("期間の開始年と終了年の間で目標年")?>'])));											
			// 	document.getElementById("error").appendChild(a);											
			// 	checkstatus = false;	
			// }

			if(check_head.test(head)) {												
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __($type_order['LayerType']['name_'.$lang_name])?>'])));											
				document.getElementById("error").appendChild(a);											
				checkstatus = false;											
			}

			if(check_head.test(sub_accs) && setup_type == 2) {												
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("sub account")?>'])));											
				document.getElementById("error").appendChild(a);
				checkstatus = false;										
															
			}

			if(check_head.test(sub_acc) && check_head.test(accs) && setup_type == 1) {												
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("sub account")?>'])));											
				document.getElementById("error").appendChild(a);
				checkstatus = false;										
															
			}

			//Check Sub Account galobal_sub_acc_id
			if(sub_acc_val == ',' && galobal_sub_acc_id == null) {											
				var newbr = document.createElement("div");											
				var a = document.getElementById("error").appendChild(newbr);											
				a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("小勘定科目")?>'])));											
				document.getElementById("error").appendChild(a);											
				checkstatus = false;											
			}	

			//Check  Account galobal_sub_acc_id
			if($("input[name=set_acc_radio]:checked").val() == 1){	
				if(accs == '') {											
					var newbr = document.createElement("div");											
					var a = document.getElementById("error").appendChild(newbr);											
					a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("勘定科目")?>'])));											
					document.getElementById("error").appendChild(a);											
					checkstatus = false;											
				}	
			}		

			if(checkstatus) {
				document.getElementById("error").innerHTML   = "";											
				document.getElementById("success").innerHTML = "";

				//Adjust Sub Accounts only
				if (setup_type == 2) {
					var sub_accs = sub_acc_arr;
					var count = 1;

					$.each( sub_accs, function( key, value ) {
						var tmp_arr = {};
						if (value != null) {
							//Prepare Data
							tmp_arr['order'] = count;
							tmp_arr['year'] = year;
							tmp_arr['head_id'] = $('#headquarter option:selected').val();
							tmp_arr['sub_acc_id'] = key;

							//Prepare table rows
							table_html += "<tr id="+count+">";
							table_html += "<td>"+ count + "</td>";
							table_html += "<td>"+ year + "</td>";
							table_html += "<td>"+ head + "</td>";
							table_html += "<td>"+ value + "</td>";
							table_html += "</tr>";
							count++;

							//Push data to data array
							data_arr.push(JSON.stringify(tmp_arr));

						}
					});
				
				} else { //Adjust Accounts of 1 sub account
					var sub_acc_id = $('#sub_acc_list.single-select option:selected').val();
					var count = 1;
					$.each( accs, function( key, value ) {
						var tmp_arr = {};
						if (value != null) {
							//Prepare Data
							tmp_arr['order'] = count;
							tmp_arr['year'] = year;
							tmp_arr['head_id'] = $('#headquarter option:selected').val();
							tmp_arr['sub_acc_id'] = sub_acc_id;
							tmp_arr['acc_id'] = key;
							
							//Prepare table rows
							table_html += "<tr id="+count+">";
							table_html += "<td>"+ count + "</td>";
							table_html += "<td>"+ year + "</td>";
							table_html += "<td>"+ head + "</td>";
							table_html += "<td>"+ sub_acc + "</td>";
							table_html += "<td>"+ value + "</td>";
							table_html += "</tr>";

							count++;


							//Push data to data array
							data_arr.push(JSON.stringify(tmp_arr));
						}
					});
				}

				//Append table rows to table
				$('.acc_setup .sortable').append(table_html);
				$("#exampleModalScrollableTitle").show();
				$("#tbl_adjust_Popup").show();
				$("#copyModalScrollableTitle").hide();
				$("#copyAccountButton").hide();
				$(".modal-dialog-scrollable").css("width", '80%');
				//Show popup
				$("#adjust_order_popup").addClass("in");
				$("#adjust_order_popup").show();

				//Drag and Drop table setup
				setTableDnD();
				
			}
			scrollText();
		});
		
		/* Edit  data in account link */
		$("#EditOrder [href]").click(function(e){
			$("#saveAccountButton").hide();
			$("#editAccountButton").show();
			var id= $(this).data("value");
			$.ajax({
				type : "POST",
				url : "<?php echo $this->webroot; ?>BrmAccountSetup/getRelatedRow",
				data : {id : id},
				dataType : 'json',
				success : function(related_data){//console.log(related_data);

					$('.acc_setup .sortable').html('');
					var table_html = '';
					var len = related_data.length;
					data_arr = [];	

					var acc_code_get;
				
					var count = 1;
				
					var myObj = related_data;

					var acc_name_jp;
				
					for(var i in related_data){	

						if (related_data[i].BrmSaccount.name_jp != null) {
							acc_name_jp =related_data[i].BrmSaccount.name_jp;

						}else
						{
						  acc_name_jp= "";
						}
						var tmp_arr = {};
						var id = related_data[i].BrmAccountSetup.id;
						var hlayer_code = related_data[i].BrmAccountSetup.hlayer_code;
						var year = related_data[i].BrmAccountSetup.target_year;
						var brm_account_id = related_data[i].BrmAccountSetup.brm_account_id;
						var brm_saccount_id = related_data[i].BrmAccountSetup.brm_saccount_id;
						var brm_account_name = !!related_data[i].BrmAccount.name_jp === true ? related_data[i].BrmAccount.name_jp : "";

						//Prepare Data
						tmp_arr['id'] = id;
						tmp_arr['year'] = year;
						tmp_arr['hlayer_code'] = hlayer_code;
						tmp_arr['brm_account_id'] = brm_account_id;
						tmp_arr['brm_saccount_id'] = brm_saccount_id;
						tmp_arr['order'] = related_data[i].BrmAccountSetup.order;

						//Prepare table rows
						table_html += "<tr id="+count+">";
						table_html += "<td>"+count+"</td>";
						table_html += "<td>"+ year +"</td>";	
						table_html += "<td>"+related_data[i].Layer.name_jp +"</td>";
						table_html += "<td>"+ brm_account_name +"</td>";	

						if (acc_name_jp != '') {

							table_html += "<td>"+ acc_name_jp +"</td>";	
						}
					
						// table_html += "<td>"+ related_data[i].AccountSetupModel.order+"</td>";
						
						table_html += "</tr>";
						count++;
						//Push data to data array
						data_arr.push(JSON.stringify(tmp_arr));
					}
					//Append table rows to table
					$('.acc_setup .sortable').append(table_html);
					$("#exampleModalScrollableTitle").show();
					$("#tbl_adjust_Popup").show();
					$("#copyModalScrollableTitle").hide();
					$("#copyAccountButton").hide();
					$(".modal-dialog-scrollable").css("width", '80%');
					//Show popup
					$("#adjust_order_popup").addClass("in");
					$("#adjust_order_popup").show();

					
					
					setTableDnD();
										
				}//end related data		
			});		
		});
		// Author : Ei Thandar Kyaw on 09/10/2020
		// search account by year
		$("#btn_search").click(function(){
			document.forms[0].action = "<?php echo $this->webroot; ?>BrmAccountSetup/index";
									document.forms[0].method = "POST";
									document.forms[0].submit();    
		});
		// Author : Ei Thandar Kyaw on 09/10/2020
		// search account by hq
		$(".nav-link").click(function(){
		  //alert($(this).attr('data-id'));
		  $('#hq').val($(this).attr('data-id'));
		  $("#year option:selected").prop("selected", false);
		  document.forms[0].action = "<?php echo $this->webroot; ?>BrmAccountSetup/index";
									document.forms[0].method = "POST";
									document.forms[0].submit(); 
		});
		// Author : Ei Thandar Kyaw on 09/10/2020
		// popup when click copy button
		$("#btn_copy").click(function(){
			$("#saveAccountButton").hide();
			$("#editAccountButton").hide();
			$("#exampleModalScrollableTitle").hide();
			$("#copyModalScrollableTitle").show(); 
			$("#copyAccountButton").show(); 
			$('.acc_setup .sortable').html('');
			yearHtml = '';
			hqHtml = '';
			$('#copy_to_year').html('');
			$('#copy_to_hq').html('');
			yearHtml = '<option value="">--Select Year--</option>';
			$.each( termYears, function( key, value ) {
				yearHtml += '<option value='+value+'>'+value+'</option>';
			})
			$('#copy_to_year').append(yearHtml);
			hqHtml = '<option value="">--Select Layer--</option>';

		
			$.each( headpt_dept, function( key, value ) {//console.log(value);
				hqHtml += '<option value='+value['Layer']['layer_code']+'>'+value['Layer']['name_jp']+'</option>';
			})
			$('#copy_to_hq').append(hqHtml);
			$('#tbl_adjust_Popup').hide();
			$(".modal-dialog-scrollable").css("width", '45%');
			//Show popup
			$("#adjust_order_popup").addClass("in");
			$("#adjust_order_popup").show();
			
			$("#acc_copy").show();	  
		});
		// Author : Ei Thandar Kyaw on 12/10/2020
		// copy the account
		$("#copyAccountButton").click(function(e){
			copyFYear = $('#copy_form_year').val();
			copyFHq = $('#copy_from_hq').attr("data-id");

			copyYear = $('#copy_to_year').val();
			copyHq = $('#copy_to_hq').val();
			
			error = false;
			$("#popuperror").html("");
			$("#err").html("");
			if(copyYear == ""){
				error = true;
				//$("#err").append('Please fill Copy To(Year)!<br>');
				$("#err").append(errMsg(commonMsg.JSE001,['<?php echo __("コピー先（年）")?>'])+'<br>')
			}
			if(copyHq == ""){
				error = true;
				//$("#err").append('Please fill Copy To(Headquarter)!<br>');
				$("#err").append(errMsg(commonMsg.JSE001,['<?php echo __("コピー先（本社）")?>'])+'<br>')
			}
			if(copyYear != '' && copyHq != ''){
				
				if(copyFYear == copyYear && copyFHq == copyHq) {
					$("#err").append(errMsg(commonMsg.JSE060,['<?php echo __("コピーするデータは同じであってはなりません")?>']))
					error = true;
				}
			}
			if(!error){
				$("#err").innerHTML   = "";
				$("#succ").innerHTML = "";
				$.confirm({
			        title: "<?php echo __('コピー確認'); ?>",
			        icon: 'fas fa-exclamation-circle',
			        type: 'blue',
			        typeAnimated: true,
			        animateFromElement: true,
			        animation: 'top',
			        draggable: false,
			        content: "<?php echo __('アカウントデータをコピーしてもよろしいですか？'); ?>",
			        buttons: {   
			          ok: {
			            text: "<?php echo __('はい'); ?>",
			            btnClass: 'btn-info',
			            action: function(){
							loadingPic();
							document.forms[0].action = "<?php echo $this->webroot; ?>BrmAccountSetup/AccountCopy";
							document.forms[0].method = "POST";
							document.forms[0].submit(); 
							return true;

		            	}
			        },   
			          cancel : {
			            text: "<?php echo __('いいえ'); ?>",
			            btnClass: 'btn-default',
			            cancel: function(){

			            }
			          }
			        },
			        theme: 'material',
			        animation: 'rotateYR',
			        closeAnimation: 'rotateXR'
			      });
			}
		});
		// Author : Ei Thandar Kyaw on 28/10/2020
		// overwrite the account
		$("#overwriteAccountButton").click(function(e){
			error = false;
			copyFYear = $('#copy_form_year').val();
			copyFHq = $('#copy_from_hq').attr("data-id");

			copyYear = $('#copy_to_year').val();
			copyHq = $('#copy_to_hq').val();
			$("#err").html("");
			if(copyYear != '' && copyHq != ''){
				
				if(copyFYear == copyYear && copyFHq == copyHq) {
					$("#err").append(errMsg(commonMsg.JSE060,['<?php echo __("コピーするデータは同じであってはなりません")?>']))
					error = true;
				}
			}
			
			if(!error){
				$.confirm({
						title: "<?php echo __('上書き確認'); ?>",
						icon: 'fas fa-exclamation-circle',
						type: 'orange',
						typeAnimated: true,
						animateFromElement: true,
						animation: 'top',
						draggable: false,
						content: "<?php echo __('上書きしますか？'); ?>",
						buttons: {   
						ok: {
							text: "<?php echo __('はい'); ?>",
							btnClass: 'btn-info',
							action: function(){
								loadingPic();  
								document.forms[0].action = "<?php echo $this->webroot; ?>BrmAccountSetup/AccountOverwrite";
								document.forms[0].method = "POST";
								document.forms[0].submit();    
																	
								return true;					

							}
						},   
						cancel : {
							text: "<?php echo __('いいえ'); ?>",
							btnClass: 'btn-default',
							cancel: function(){

							}
						}
						},
						theme: 'material',
						animation: 'rotateYR',
						closeAnimation: 'rotateXR'
				});
			}
		});
		$('.set_acc_radio').change(function () {
			if ($(this).val() == 1) {
				$("#account_list").show();
				$(".single_sub_acc_list").show();
				$(".multiple_sub_acc_list").hide();
			} else {
				$("#account_list").hide();
				$(".single_sub_acc_list").hide();
				$(".multiple_sub_acc_list").show();
			}
			
		});
    
		/* float thead heade fixed when scroll */
		if($('#tbl_actual').length > 0) {
			var $table = $('#tbl_actual');
			$table.floatThead({
				position: 'absolute'
			});
		}

		if($(".tbl-wrapper").length) {
			$(".tbl-wrapper").floatingScroll();
		}
		/* end*/

		/* year picker setting */
		$('#yearPicker').datetimepicker({
			format      :   "YYYY",
			viewMode    :   "years", 
		});

		/* saveset account  */
		$("#saveAccountButton").click(function(e){
			$.confirm({
		        title: "<?php echo __('保存確認'); ?>",
		        icon: 'fas fa-exclamation-circle',
		        type: 'blue',
		        typeAnimated: true,
		        animateFromElement: true,
		        animation: 'top',
		        draggable: false,
		        content: "<?php echo __('データを保存してよろしいですか。'); ?>",
		        buttons: {   
		          ok: {
		            text: "<?php echo __('はい'); ?>",
		            btnClass: 'btn-info',
		            action: function(){

		              $.ajax({
		              type : "POST",
		              url : "<?php echo $this->webroot; ?>BrmAccountSetup/saveAccountSetup",
		              data : {data: data_arr},
		              dataType : 'json',
					  beforeSend: function() {
						loadingPic();
					  },
		              success : function(data){

		                 $("#adjust_order_popup").hide();
		                  if(data == ''){
		                    var json = data.error;
		                    document.getElementById("error").innerHTML = json;
		                     
		                  } else{
		                     //window.location.reload();
		                    var json = data;
		                    document.getElementById("success").innerHTML = json;
		                    $("html, body").animate({ scrollTop: 0 }, 'slow');
		                    refresh();
		                  }
						  $('#overlay').hide();         

		              },
		              error: function(e) {
		                alert('Something wrong! Please refresh the page.');
		              }
		           });
		            }
		          },   
		          cancel : {
		            text: "<?php echo __('いいえ'); ?>",
		            btnClass: 'btn-default',
		            cancel: function(){

		            }
		          }
	       		 },
		        theme: 'material',
		        animation: 'rotateYR',
		        closeAnimation: 'rotateXR'
			});		
		});

		function setTableDnD () {
			//Drag and Drop table setup
			$(".acc_setup").tableDnD({
				onDragClass: "myDragClass", //Drag style
				onDrop: function(table, row) { //When release the drag cursor
					var swap_arr = [];
					var sortedID = row.id;
					$('.acc_setup .sortable tr').each(function (i, lRow) {
						var order = lRow.id; //get old order
						lRow.id = i+1; //

						// if (order == sortedID) {
						// 	$(this).addClass('sorted');
						// 	console.log($(this).text());
						// }
						swap_arr[i] = data_arr[order-1]; //change order of data_arr
					});
					data_arr = swap_arr;
				},
				onDragStart: function(table, row) { //When drag start
					// alert('')
				}
			});
		}

		function refresh() {    
		    setTimeout(function () {
		        window.location.reload()
		    }, 2000);
		}
		/* Edit set account pop up link*/
		$("#editAccountButton").click(function(e){

			$.confirm({
	        title: "<?php echo __('保存確認'); ?>",
	        icon: 'fas fa-exclamation-circle',
	        type: 'blue',
	        typeAnimated: true,
	        animateFromElement: true,
	        animation: 'top',
	        draggable: false,
	        content: "<?php echo __('データを保存してよろしいですか。'); ?>",
	        buttons: {   
	          ok: {
	            text: "<?php echo __('はい'); ?>",
	            btnClass: 'btn-info',
	            action: function(){
		            $.ajax({
		              type : "POST",
		              url : "<?php echo $this->webroot; ?>BrmAccountSetup/EditAccountSetup",
		              data : {'data_arr': data_arr,'id':data_id},
		              dataType : 'json',
		              success : function(data){
		              	console.log(data);
		                $("#adjust_order_popup").hide();
		                scrollText();
			            window.location.reload();         

		              },
		              error: function(e) {
		                alert('Something wrong! Please refresh the page.');
		              }
		            });  

            	}
	        },   
	          cancel : {
	            text: "<?php echo __('いいえ'); ?>",
	            btnClass: 'btn-default',
	            cancel: function(){

	            }
	          }
	        },
	        theme: 'material',
	        animation: 'rotateYR',
	        closeAnimation: 'rotateXR'
	      });


		});

		/* multi select*/
		$('#multi_sub_acc_list').amsifySelect();

		/* set account pop up */

		$("#adjust_order_popup .close").click(function(e){
			$("#adjust_order_popup").removeClass("in");
			$("#adjust_order_popup").hide();
			$("#err").html("");
			$("#succ").html("");
			$("#acc_copy").hide();
			$("#overwriteAccountButton").hide();
		});
		$('#copy_to_year, #copy_to_hq').on('change', function() {
			$("#err").html('');
			copyToHq = $('#copy_to_hq').val();
			copyToYear = $('#copy_to_year').val();;
			
			if(copyToYear && copyToHq){
				$.ajax({
					type : "POST",
					url : "<?php echo $this->webroot; ?>BrmAccountSetup/checkData",
					data : {copyToHq : copyToHq, copyToYear : copyToYear},
					dataType : 'json',
					success : function(result){
						if(result.DataExist){
							$("#overwriteAccountButton").show();
						}else{
							$("#overwriteAccountButton").hide();
						}
					}	
				});	
			}else{
				$("#overwriteAccountButton").hide();
			}
		});

		/*  
		*	Show hide loading overlay
		*	@Zeyar Min  
		*/
		function loadingPic() { 
			$("#overlay").show();
			$('.jconfirm').hide();  
		}

	});
	function scrollText(){

		var error = $('#error').text();
		var success = $('.success').text();
		if(error){
			$("html, body").animate({ scrollTop: 0 }, "slow");				
		}
		if(success){
			$("html, body").animate({ scrollTop: 0 }, "slow");				
		}
	}

	function GetAccountName(sub_account,year,head){      
		$.ajax({
			type : "POST",
			url : "<?php echo $this->webroot; ?>BrmAccountSetup/getAccountName",
			data : {sub_account:sub_account, year:year, head:head},
			dataType : 'json',
			success : function(data){//console.log(data)

				var len = data.length;
				var html = "<option value= '' selected>----- <?php echo __("選択") ?> ----- </option>";
				for (i = 0; i < len; i++) {
					var id = data[i].BrmSaccount.id;
					var acc_name_jp = data[i].BrmSaccount.name_jp;
					html += "<option value='"+id+"'>"+acc_name_jp+"</option>";
				}
				$("#acc_list").html(html);
				$("#acc_list").amsifySelect();
			}

		});
	}

	/*Delete data sub account in tbl_account_set up*/
	function DeleteAccountSetup(id) {
		document.getElementById("delete_id").value = id;

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
				draggable: false,
				content: "<?php echo __('データを削除してよろしいですか。'); ?>",
				buttons: {   
					ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
						action: function(){
							document.forms[0].action = "<?php echo $this->webroot; ?>BrmAccountSetup/DeleteAccountSetup";
							document.forms[0].method = "POST";
							document.forms[0].submit();
							return false;
						}
					},   
					cancel : {
						text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
						cancel: function(){

						}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		}
	}
	

</script>
<div id="overlay">
	<span class="loader"></span>
</div>
<input type="hidden" name="delete_id" id="delete_id" value="">
<input type="hidden" name="hid_page_no" id="hid_page_no" class="txtbox" value ="">
<input type="hidden" name="hq" id="hq" value="<?php echo $hq; ?>">
<div class="content register_container"> 
	<div class="row" ><!-- row start -->
		<div class="col-lg-12 col-md-12 col-sm-12">
			<h3 class=""><?php echo __("アカウント設定管理");?></h3>
			<hr>
			<div class="success" id="success"><?php echo ($this->Session->check("Message.AccountSetupSuccess"))? $this->Flash->render("AccountSetupSuccess") : '';?></div>            
			<div class="error" id="error"><?php echo ($this->Session->check("Message.AccountsetupError"))? $this->Flash->render("AccountsetupError") : '';?></div>   
		</div>
		<div class="col-lg-12 col-md-12 col-sm-12">
			<div class="col-md-6 col-sm-6">
				<div class="form-group row">
					<label for="year_choose" class="col-sm-4 col-form-label required">
					<?php echo __('年度');?>
					</label>
					<div class="col-sm-8">
						<div class="input-group date yearPicker" data-provide="yearPicker" id='target_year'>
							<input type="text" class="form-control" id="year_choose" name="year_choose" value="" />
							<span class="input-group-addon">
							<span class="glyphicon glyphicon-calendar" ></span>
							</span>
						</div>
					</div>
				</div>
				<div class="form-group row"><?php //pr($type_order); ?>
					<input type="hidden" name="admin_level_id" id="admin_level_id" value="<?php if(!empty($admin_level_id))echo $admin_level_id; ?>">
						<label for="headquarter" class="col-sm-4 col-form-label" >
						<?php echo __('選択').' '.$type_order['LayerType']['name_'.$lang_name]; ?></label>
					<?php
						
					if(!empty($topLayerNames)){ ?>
						<div class="col-md-8">
							<select  id="headquarter" name="headquarter" class="form-control">
								<option value="">-- <?php echo 'Select '.$type_order['LayerType']['name_en']; ?> --</option>
								<?php 
								foreach($topLayerNames as $name): 
									?>
									<option value="<?php echo $name['Layer']['type_order'].','.$name['Layer']['layer_code'].','.$name['Layer']['id'].','.$name['Layer']['name_jp'];?>"  <?php if($this->Session->read('HEAD_DEPT_ID')==$name['Layer']['id']){ echo 'selected';}?>>
									<?php
									echo h($name['Layer']['name_jp']);
									?>
									

									</option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php }else{?>
						<div class="col-md-9">
								
							<select  id="headquarter" name="headquarter" class="form-control">
								<option value="">--<?php echo 'Select '.$type_order['LayerType']['name_en']; ?> --</option>
								
							</select>
						</div>

					<?php }?>
				</div>   
				<!-- <div class="form-group row">
					<label for="headquarter" class="col-sm-4 col-form-label required">
					<?php echo __('本部を選択');?>
					</label>
					<div class="col-sm-8">
						<select  id="headquarter" name="headquarter" class="form-control">
							<option value=",">--Select Headquarter Name--</option>
							<?php 
							foreach($headpt_dept as $name): 
							?>
								<option value="<?php echo $name['Layer']['id']?>">
								<?php
									echo h($name['Layer']['name_jp']);
								?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div> -->
			</div>
			<div class="col-sm-6 col-md-6">
				<div class="form-group row">
					<label for="budget_term" class="col-sm-4 col-form-label">
						<?php echo __('科目設定');?>
					</label>
					<div class="col-md-8">
						<div class="row" id="radio_set_acc">
							<label class="col-sm-6 col-md-6 col-xs-6" for="set_acc_1">
							<?php echo __('設定する'); ?>
								<input type="radio" name="set_acc_radio" id="set_acc_1" class="radio set_acc_radio"  value="1">
							</label>
							<label class="col-sm-6 col-md-6 col-xs-6" for="set_acc_2"> 
							<?php echo __('設定しない'); ?>
								<input type="radio" name="set_acc_radio" id="set_acc_2" class="radio set_acc_radio"  value="2" checked>
							</label>
						</div>
					</div>
				</div>
				<div class="form-group row multiple_sub_acc_list">
					<label for="multi_sub_acc_list" class="col-sm-4 col-form-label required">
						<?php echo __('小勘定科目');?>
					</label>
				
					 
					<div class="col-sm-8">                 
						<select multiple="multiple" name="sub_acc_list" id="multi_sub_acc_list" class="multiple-select form-control sub_acc_list" value="<?php if(!empty($id_array)) echo($id_string); ?>">
						<option style="height: 23px; padding-top: 2px; font-family: KozGoPro-Regular; padding-top: 2px; border-color:  #ccc; ">-----Select-----</option>                  
						<?php 
						foreach($sub_accs_name as $id => $name){ ?>
							<option value="<?php echo $id;?>" <?php if(in_array($id, $id_array)) {?>selected <?php }?>><?php echo $name; ?>
							</option>
						<?php } ?>      
						</select>
					</div>
				</div>
				<div class="form-group row single_sub_acc_list" style="display: none;">
					<label for="sub_acc_list" class="col-sm-4 col-form-label required">
						<?php echo __('小勘定科目');?>
					</label>
					<div class="col-sm-8">                 
						<select name="sub_acc_list" id="sub_acc_list" class="form-control sub_acc_list single-select" value="<?php if(!empty($id_array)) echo($id_string); ?>">                  
							<option value=",">-----Select-----</option> 
							<?php
							foreach($trade_accs as $id => $name){ ?>
								<option value="<?php echo $id;?>" <?php if(in_array($id, $id_array)) {?>selected <?php }?>><?php echo $name; ?>
								</option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="form-group row" id="account_list" style="display: none;">
					<label for="acc_list" class="col-sm-4 col-form-label required">
						<?php echo __('Account');?>
					</label>
					<div class="col-sm-8">                 
						<select multiple name="acc_list"  id="acc_list" class="multiple-select form-control">
							<option value="" selected=""><?php echo __("----- Select -----"); ?>
							</option>
							<?php
							foreach($get_acc_data as $id => $name){ ?>
								<option value="<?php echo $id;?>" <?php if(in_array($id, $id_array)) {?>selected <?php }?>><?php echo $name; ?>
								</option>
							<?php } ?> 
						</select>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-12 col-md-12 d-flex justify-content-end" >
						<input type="button" name="btn_setup"  id="btn_setup" class="btn-save btn-success btn_sumisho align-right" value="<?php echo __("設定"); ?>">
					</div>
				</div>
			</div>
		</div>       
	</div><!-- row end -->
	<div class = "goup_nav">
		<?php 
		if(count($data_arr) > 0){
		?>
			<ul class="nav nav-tabs" id="myTab" role="tablist">
				<?php $itr_count = 0 ?>
				<?php foreach ($headDepartments as $key => $value): ?>
					<?php 
						$active = '';
						if($hq != '' && $hq == $key){
							$active = 'active';
						}else{
							if($hq == '') $itr_count++;
						}
					?>
					<?php if ($itr_count == 1): ?>
						<li class="nav-item active">
							<a class="nav-link active" id="group<?php echo $key; ?>_tab" data-toggle="tab" href="#group<?php echo $key; ?>" role="tab" aria-controls="group<?php echo $key; ?>" aria-selected="true" data-id="<?php echo $key; ?>"><?php echo __($value); ?>	</a>
						</li>
					<?php else: ?>
						<li class="nav-item <?php echo $active; ?>">
							<a class="nav-link <?php echo $active; ?>" id="group<?php echo $key; ?>_tab" data-toggle="tab" href="#group<?php echo $key; ?>" role="tab" aria-controls="group<?php echo $key; ?>" aria-selected="true" data-id="<?php echo $key; ?>"><?php echo __($value); ?>	</a>
						</li>
					<?php endif ?>
				<?php endforeach ?>
			</ul>
			
			<div class="col-sm-12 " align="right" style="float: right; margin-top: 20px;">
				
				<select  id="year" name="year" class="form-control" style="display: inline; width: 150px;">
					<option value="">--Select Year--</option>
					<?php
					foreach($yearsArr as $value): 
						if($year == $value) $selected = 'selected="selected"';
						else $selected = '';
					?>
						<option <?php echo $selected; ?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
					<?php endforeach; ?>
				</select>
				
				<input type="button" name="btn_search" style="margin-top: 7px !important;"  id="btn_search" class="btn btn-success btn_sumisho" value="<?php echo __("検索"); ?>">
				<input type="button" name="btn_copy" style="margin-top: 7px !important;"  id="btn_copy" class="btn btn-success btn_sumisho" value="<?php echo __("コピー"); ?>" <?php if($year == '') echo 'disabled'; ?>>
			</div>
			<div class="tab-content" id="myTabContent">
				<?php $itr_cnt = 0;
				$showNoData = false; ?>
				<?php foreach ($data_arr as $id => $accs): //pr($accs);?>
					<?php 
					if($hq == '') $itr_cnt++;
					else {
						$active = '';
						if($isActived == '') $isActived = '';
						foreach($accs as $datas ) {
							if($hq == $datas['BrmAccountSetup']['hlayer_code']){
								$active = 'active in';
								$isActived = 'active';
								
							}
						}
					}
					?>
					<?php if ($itr_cnt == 1): ?>
					<div class="tab-pane fade active in" id="group<?php echo $id; ?>" role="tabpanel" aria-labelledby="group<?php echo $id; ?>_tab">
					<?php else: ?>
					<div class="tab-pane fade <?php echo $active; ?>" id="group<?php echo $id; ?>" role="tabpanel" aria-labelledby="group<?php echo $id; ?>_tab">
					<?php endif ?>
					<?php if (!empty($accs) || isset($accs) ): //pr($type_order);?>
						<div class="row">
							<div class="col-md-12">
								<div class="msgfont"><?php echo __("総行").' : '.count($accs). ' '.__("行"); ?></div>
								<div  class="table-responsive tbl-wrapper">	
									<table class="table table-striped table-bordered acc_setup" id="tbl_acc_setup_<?php echo $id; ?>">    
										<thead class="check_period_table">
											<tr>
												<th class="w-80"><?php echo __("年度"); ?></th>
												<th class="w-220"><?php echo $type_order['LayerType']['name_'.$lang_name]; ?></th>
												<th><?php echo __("勘定科目"); ?></th>
												<th><?php echo __("小勘定科目"); ?></th>
												<th class="w-80" ><?php echo __("順番"); ?></th>
												<th class="w-200" colspan="2"><?php echo __("アクション"); ?></th>
											</tr> 
										</thead>
										<tbody>
										<?php 

										if(!empty($accs)) 
											foreach($accs as $datas ) {//pr($datas);
												$setup_id = $datas['BrmAccountSetup']['id'];
												$target_year_data = $datas['BrmAccountSetup']['target_year'];
												$setup_id = $datas['BrmAccountSetup']['id'];
												$head_dept_data     = $datas['Layer']['name_jp'];
												$sub_acc_name_data    = $datas['BrmAccount']['name_jp'];
												$acount_name_data    = $datas['BrmSaccount']['name_jp'];
												$display_no_data  = $datas['BrmAccountSetup']['order'];

												$sub_order_data  = $datas['BrmAccountSetup']['sub_order'];

												?>
												<tr style="text-align: center;"> 
													<td class='string' width="50px" style="word-break: break-all;"><?php echo $target_year_data ?></td>
													<td class='string' width="80px" style="word-break: break-all;"><?php echo $head_dept_data ?></td> 
													<td class='string' width="80px" style="word-break: break-all;"><?php echo $sub_acc_name_data ?></td>
													<td class='string' width="80px" style="word-break: break-all;"><?php echo $acount_name_data ?></td>

													<td class='string' width="20px" style="word-break: break-all;"><?php echo $display_no_data ;?><?php if ($sub_order_data !='0') {
															echo ".";echo $sub_order_data;
														}else
														{
															echo "";
														} ?></td>
													<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" width="50px" class="link" style="word-break: break-all;">  
														<div id="EditOrder">          
															<a  href="#" class="glyphicon" data-value =<?php echo $setup_id; ?> title="<?php echo __('順番調整'); ?>"><i class="fa-regular fa-pen-to-square"></i></a>
															<input type="hidden" name="setup_id" id="setup_id" value="<?=$setup_id?>">
														</div>
													</td>  
													<td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" width="50px" style="word-break: break-all;">
														<a class="delete_link" href="#" onclick="DeleteAccountSetup(<?php echo $setup_id; ?>);" title="<?php echo __('削除'); ?>"><i class="fa-regular fa-trash-can"></i></a>
													</td>
												</tr>
											<?php }?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					<?php else:?>
						<div class="row">
							<div class="col-sm-12">
								<p class="no-data"><?php echo $no_data_group1; ?></p>
							</div>
						</div>
					<?php endif ?>
					</div>	
				<?php endforeach ?>	
				<?php
				if($hq != '' && $isActived == ''){
				?>
				<div class="col-sm-12">
					<p class="no-data"><?php echo __("表示するアカウントはありません。"); ?></p>
				</div>
				<?php
				}
				?>
			</div>
		<?php }else{
		?>
			<div class="row">
						<div class="col-sm-12">
							<p class="no-data"><?php echo $no_data_group1; ?></p>
						</div>
					</div>
		<?php
		} ?>
		
	</div>
</div>


 <!-- Account setupPopUpBox  -->
<!-- Modal -->
<div class="modal fade" id="adjust_order_popup" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable" role="document" style="width: 80%">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalScrollableTitle"><?php echo __("勘定科目表示順番の調整"); ?></h5>
				<h5 class="modal-title" id="copyModalScrollableTitle" style="display: none;"><?php echo __("アカウントのコピー"); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" style="max-height: 500px; overflow-y: auto;">
				<div class="modal_tbl_wrapper">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="success" id="succ"></div>            
						<div class="error" id="err" method="PopUp"></div>                  
					</div>
					<table class="table table-striped table-bordered acc_setup" id="tbl_adjust_Popup">   
						<tbody class="sortable">
						</tbody>
					</table>
					<div id="acc_copy" style="display: none;">
						<div class="col-md-12">
							<div class = "form-group popup_row">
								<label class="col-md-5 control-label rep_lbl"><?php echo __("コピー元（年）"); ?></label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="copy_form_year" name="copy_from_year" disabled="" value="<?php echo $year; ?>">
									<input type="hidden" name="hidFromYear" value="<?php echo $year; ?>">
								</div>
							</div>
							<div class = "form-group popup_row">
								<label class="col-md-5 control-label rep_lbl"><?php echo __("コピー元（本社）"); ?></label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="copy_from_hq" data-id="<?php echo $hq; ?>" name="copy_form_hq" disabled="" value="<?php echo $headDepartments[$hq]; ?>">
									<input type="hidden" name="hidFromHq" value="<?php echo $hq; ?>">
								</div>
							</div>
							<div class = "form-group popup_row">
								<label class="col-md-5 control-label rep_lbl required"><?php echo __("コピー先（年）"); ?></label>
								<div class="col-sm-6">
									<select  id="copy_to_year" name="copy_to_year" class="form-control">
									</select>
								</div>
							</div>
							<div class = "form-group popup_row">
								<label class="col-md-5 control-label rep_lbl required"><?php echo __("コピー先（本社）"); ?></label>
								<div class="col-sm-6">
									<select  id="copy_to_hq" name="copy_to_hq" class="form-control">
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div style="margin-bottom: 100px">
					<button type="button" name="saveAccountButton" id="saveAccountButton" class="btn btn-success btn_sumisho" style="float: right;"><?php echo __('Save Order');?> </button>
					<button type="button" name="editAccountButton" id="editAccountButton" class="btn btn-success" style="float: right;"><?php echo __('Save Order');?> </button>
					<button type="button" name="overwriteAccountButton" id="overwriteAccountButton" class="btn btn-success btn_sumisho" style="float: right;display: none;"><?php echo __('Overwrite');?> </button>
					<button type="button" name="copyAccountButton" id="copyAccountButton" class="btn btn-success btn_sumisho" style="float: right;display: none; margin-right: 3px; "><?php echo __('Add');?> </button>
				</div>
		</div>
		</div>
	</div>
</div>
 <!-- Account setupPopUpBox End  -->
  <?php
  echo $this->form->end();
  ?>
