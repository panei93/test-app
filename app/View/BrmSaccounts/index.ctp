<style>
table {
	table-layout: fixed;
	border: none;
}
.emp_register_btn_popup{
text-align:right;
}
.emp_register{
z-index: 2;
margin-left: auto;
margin: 0px 2px;

}
.contantbond{
box-sizing: border-box;
}
.fl-scrolls {
	margin-bottom: 40px;/* modify floating scroll bar */
}
#tbl_acc {
	padding-left: 0px;
}
th{
	background-color: #D5EADD;
	vertical-align: middle !important;
}
table, th {
	text-align: center;
	padding: 10px 15px;
}

.nav > li > a {
	padding-left: 14px;
}
div#update {
	display: none;
}
#btn_update{
	display: none;
}
.table-responsive.modal_tbl_wrapper label {
	padding: 0px;
}
.align-right {
	text-align: right !important;
 }
 .jconfirm-box-container {
      margin-left: unset !important;
   }

@media (max-width:780px) { 
	.table-bordered {
	border: none;
	display: block;
	overflow-x: auto;
	white-space: nowrap;
	padding: 0;
	}
}
/*@media (max-width: 992px){
	.register {
	width:100% !important;
	}
}*/
@media (max-width: 992px){
	.registerpopup {
	width:700px;
	}
}
@media screen and (min-width: 768px) {
	.modal-dialog {
	width: 600px; /* New width for default modal */
	}
	.modal-sm {
	width: 900px; /* New width for small modal */

	}
}
	@media screen and (min-width: 992px) {
		.modal-lg {
		width: 500px; /* New width for large modal */
		}
	}



</style>
<script>

$(document).ready(function(){
	

	$('#sub_acc_groups').children('option').hide();
	$('#sub_acc_groups').children('option[value=""]').show();

	$('#sub_acc_id').on('change',function(){
		var sub_acc_id = $(this).val();
		setSubAcccountGroupName(sub_acc_id);
	})
	/* float thead */
	if($('#tbl_acc').length > 0) {
		var $table = $('#tbl_acc');
		$table.floatThead({
			position: 'absolute'
		});
	}
		
	if($(".tbl-wrapper").length) {
		$(".tbl-wrapper").floatingScroll();
	}


	$('#btn_save').show();

});
	/*
	@author ayezarnikyaw
	setSubAcccountGroupName
	*/
	function setSubAcccountGroupName(subacc_id){
		$.ajax({
			type : "POST",
			url : "<?php echo $this->webroot; ?>BrmSaccounts/getSubAccountGroupNames",
			data : {id : subacc_id},
			dataType : 'json',
			beforeSend: function() {
				loadingPic();
			},
			success : function(data){//console.log(data)
				var len = data.length;
				
				var html = "<option value= '' selected>---<?php echo __("選択") ?>--- </option>";

				$.each(data, function(id, name) {

					if (len == 1) {
						html += "<option value='"+name.id_pair+"' selected>"+name.name_pair+"</option>";
					} else {
						html += "<option value='"+name.id_pair+"'>"+name.name_pair+"</option>";
					}

				});
				$("#sub_acc_groups").html(html);
				$('#overlay').hide();
			}

		});
	}
	function saveData() {
	// body...
	document.getElementById("error").innerHTML = "";
	document.getElementById("success").innerHTML = "";

	var acc_id 		= document.getElementById("acc_id").value;
	var acc_code 	= document.getElementById("acc_code").value;
	var cl_acccode 	= document.getElementById("acc_code");
	var acc_name_jp	= document.getElementById("acc_name_jp").value;
	var acc_name_en	= document.getElementById("acc_name_en").value;
	var sub_acc_groups= document.getElementById("sub_acc_groups").value;
	var sub_acc_id	= document.getElementById("sub_acc_id").value;
	var acc_code4char = acc_code.length;
	var acc_4char 	= acc_code.value;
	chk				=	true;

		if(!checkNullOrBlank(acc_code)) { 
		var newbr = document.createElement("div");
		var a = document.getElementById("error").appendChild(newbr);
		a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("コード")?>'])));
		document.getElementById("error").appendChild(a); 
		chk = false;
		}
		if(acc_code4char!=''){
			if(acc_code4char < "10"){
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("10桁までの数字")?>'])));
			document.getElementById("error").appendChild(a); 
			chk = false;
				
			}
		}

		if(!checkNullOrBlank(acc_name_jp)) { 
		var newbr = document.createElement("div");
		var a = document.getElementById("error").appendChild(newbr);
		a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("勘定名称").("(").("JP").(")")?>'])));
		document.getElementById("error").appendChild(a);
		chk = false;
		}

		if(!checkNullOrBlank(sub_acc_groups)) { 
		var newbr = document.createElement("div");
		var a = document.getElementById("error").appendChild(newbr);
		a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002,['<?php echo __("グループ")?>'])));
		document.getElementById("error").appendChild(a);
		chk = false;
		}

		if(!checkNullOrBlank(sub_acc_id)) { 
		var newbr = document.createElement("div");
		var a = document.getElementById("error").appendChild(newbr);
		a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002,['<?php echo __("小勘定科目")?>'])));
		document.getElementById("error").appendChild(a);
		chk = false;
		}

		var path = window.location.pathname;
		var page = path.split("/").pop();
		if (page.indexOf("page:") !== -1) {
			document.getElementById('hid_page_no').value = page; 
		}
		// if (page.includes("page:")) {
		// document.getElementById('hid_page_no').value = page; 
		// }

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
								document.forms[0].action = "<?php echo $this->webroot; ?>BrmSaccounts/saveUserData";
								document.forms[0].method = "POST";
								document.forms[0].submit();
														
								return true;
							} 
						},
						cancel : {
								text: '<?php echo __("いいえ");?>',
								btnClass: 'btn-default',
								cancel: function(){ 
									console.log('the user clicked cancel'); 
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

function CreateSubAccount(){
	document.getElementById("err").innerHTML= "";
	document.getElementById("succ").innerHTML = "";
	var sub_acc_groups = document.getElementById("sub_acc_groups").value;
	var sub_acc_name_jp = document.getElementById("sub_acc_name_jp").value;
	var sub_acc_name_en = document.getElementById("sub_acc_name_en").value;
	// console.log(sub_acc_groups);
	// console.log(sub_acc_name_jp);
	// console.log(sub_acc_name_en);
	document.getElementById("page_name").value = "account_page";
	chk=true; 

	var path = window.location.pathname;
	var page = path.split("/").pop();
	if (page.indexOf("page:") !== -1) {
		document.getElementById('hid_page_no').value = page; 
	}
	if(!checkNullOrBlank(sub_acc_groups)) { 
		var newbr = document.createElement("div");
		var a = document.getElementById("err").appendChild(newbr);
		a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002,['<?php echo __("グルーピング")?>'])));
		document.getElementById("err").appendChild(a);
		chk = false;
	}
	if(!checkNullOrBlank(sub_acc_name_jp)) {
		var newbr = document.createElement("div");
		var a = document.getElementById("err").appendChild(newbr);
		a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("勘定科目名 (JP)")?>'])));
		document.getElementById("err").appendChild(a);
		chk = false;
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
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmAccounts/saveData";
						document.forms[0].method = "POST";
						document.forms[0].submit();
											 
						return true;
					} 
				},
				cancel : {
					text: '<?php echo __("いいえ");?>',
					btnClass: 'btn-default',
					cancel: function(){ 
						console.log('the user clicked cancel'); 
						 scrollText();
					}

				} 
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		}); 
	}
		// scrollText();
}
function scrollText() {
	var error = $('#error').text();
	var success = $('.success').text();
	if(error){
		$("html, body").animate({ scrollTop: 0 }, "slow");				
	}
	if(success){
		$("html, body").animate({ scrollTop: 0 }, "slow");				
	}
}
function EditAccountData(id){
	document.getElementById('error').innerHTML = '';
	document.getElementById('success').innerHTML = '';

	 $("#acc_code").prop("readonly", true);
	 $('#btn_save').hide();
	 $('#btn_update').show();
	$.ajax({
		type : "POST",
		url : "<?php echo $this->webroot;?>BrmSaccounts/editAccount",
		data : {id:id},
		dataType : "json",
		beforeSend: function() {
			loadingPic();
		},
		success: function(data) {//console.log(data);
			var acc_id=data['id'];
			var acc_code = data['acc_code'];
			var acc_name_jp = data['acc_name_jp'];
			var acc_name_en = data['acc_name_en'];
			var sub_acc_id = data['sub_acc_id'];
			var sub_acc_groups = JSON.parse(data['sub_acc_groups']);
			var id_pair = data['id_pairs'];
			var flag = data['flag'];
			var sub_acc_id_all = data['sub_acc_name'];
			var html = "<option value= ''selected>----- <?php echo __("選択") ?> ----- </option>";
			
			$("#acc_id").val(acc_id);
			$("#acc_code").val(acc_code);
			$("#acc_name_jp").val(acc_name_jp);
			$("#acc_name_en").val(acc_name_en);
			$('#sub_acc_id option[value="'+sub_acc_id+'"]').prop('selected', true);

			$.each(sub_acc_groups, function(id, name) {
				if(id_pair == name.id_pair) {
					//console.log('work');
					html += "<option value='"+name.id_pair+"' selected>"+name.name_pair+"</option>";
				}else{
					//console.log('not work');
					html += "<option value='"+name.id_pair+"'>"+name.name_pair+"</option>";
				}
			});

			$("#sub_acc_groups").html(html);
			$('#overlay').hide();
		}

	});
}

function UpdateInfo(){
	document.getElementById('error').innerHTML = '';
	document.getElementById('success').innerHTML = '';
	var acc_code = document.getElementById("acc_code").value;
	var acc_name_jp= document.getElementById("acc_name_jp").value;
	var acc_name_en= document.getElementById("acc_name_en").value;
	var sub_acc_id = document.getElementById("sub_acc_id").value;
	var group_data = document.getElementById("sub_acc_groups").value;
	var acc_id = document.getElementById("acc_id").value;
	chk=true;

		if(!checkNullOrBlank(acc_code)) { 
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("コード")?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
		}
		if(!checkNullOrBlank(acc_name_jp)) { 
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("勘定名称").("(").("JP").(")")?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
		}
		if(!checkNullOrBlank(group_data)) { 
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002,['<?php echo __("グルーピンク")?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
		}
		if(!checkNullOrBlank(sub_acc_id)) { 
			var newbr = document.createElement("div");
			var a = document.getElementById("error").appendChild(newbr);
			a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002,['<?php echo __("勘定科目名")?>'])));
			document.getElementById("error").appendChild(a);
			chk = false;
		}

		var path = window.location.pathname;
		var page = path.split("/").pop();
		if (page.indexOf("page:") !== -1) {
			document.getElementById('hid_page_no').value = page; 
		}
		// if (page.includes("page:")) {
		// document.getElementById('hid_page_no').value = page; 
		// }

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
				content: "<?php echo __("データを保存してよろしいですか。"); ?>", 
				buttons: {
						ok: { 
							text: '<?php echo __("はい");?>', 
							btnClass: 'btn-info', 
							action: function(){
								loadingPic(); 
								document.forms[0].action = "<?php echo $this->webroot; ?>BrmSaccounts/updateAccountData";
								document.forms[0].method = "POST";
								document.forms[0].submit();
															
								return true;
							} 
						},
						cancel : {
							text: '<?php echo __("いいえ");?>',
							btnClass: 'btn-default',
							cancel: function(){ 
								console.log('the user clicked cancel'); 
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

function AccDelete(id){

	document.getElementById("error").innerHTML = '';
	document.getElementById("success").innerHTML = '';

	document.getElementById("id").value = id;
	var errorFlag = true;

	var path = window.location.pathname;
	var page = path.split("/").pop();
	if (page.indexOf("page:") !== -1) {
		document.getElementById('hid_page_no').value = page; 
	}

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
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmSaccounts/delete";
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
    /*  
	*	Show hide loading overlay
	*	@Zeyar Min  
	*/
	function loadingPic() { 
		$("#overlay").show();
		$('.jconfirm').hide();  
	}

</script>
<body>
<div id="overlay">
	<span class="loader"></span>
</div>
</body>
 <form method="post">
<div class="content register_container">
	<!-- <?php $language = $this->Session->read('Config.language');?> -->
	<div class="row">
		<div class="col-md-12 col-sm-12 heading_line_title">
			<h3><?php echo __('サブアカウント管理');?></h3>
			<hr>
		</div>
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.SubAccountSuccess"))? $this->Flash->render("SubAccountSuccess") : '';?></div>
			<div class="error" id="error"><?php echo ($this->Session->check("Message.SubAccountFail"))? $this->Flash->render("SubAccountFail") : '';?></div>
		</div>
			
		<!-- hiddendata id for account_register-->
		<input type="hidden" id="id" name="id"> 
		<input type="hidden" id="acc_id" name="acc_id">

		<!-- <div class="row"> -->
			<div class="col-md-12">
				<div class="row form-group">
					<div class="col-md-6">
						<label class="col-md-4 required"><?php echo __("勘定コード");?></label>
						<div class="col-md-8 ">
						<!-- create and update-->
						 
							<input class="form-control " type="hidden" id="acc_masterid" name="acc_masterid" value="<?=$id?>" >
							<input class="form-control" type="text" id="acc_code" name="acc_code" maxlength="10" oninput="return onlyN()" oninput="return test()" value="<?php
							if(!empty($request)){
								echo h($request['acc_code']);
							}
							?>">
						</div> 
					</div>
				</div>

				<div class="row form-group">
					<div class="col-md-6">
						<label class="col-sm-12 col-md-4 col-xs-10 required"><?php echo __("小勘定科目名 (JP)");?></label>
						<div class="col-md-8 col-xs-12 ">
						<!-- create and update-->
						<input class="form-control" type="text" id="acc_name_jp" name="acc_name_jp"value="<?php
							if(!empty($request)){
								echo h($request['acc_name_jp']);
							}
							?>">
						</div>
					</div>
					<div class="col-md-6">
						<label class="col-sm-12 col-md-4 col-xs-10"><?php echo __("小勘定科目名 (ENG)");?></label>
						<div class="col-md-8 col-xs-12 ">
							<!-- create and update-->
							<input class="form-control" type="text" id="acc_name_en" name="acc_name_en"value="<?php
							if(!empty($request)){
								echo h($request['acc_name_jp']);
							}
							?>">

						</div>
					</div>
				</div>
				
				<div class="row form-group">
					<div class = "col-md-6">
						<label class="col-sm-12 col-md-4 col-xs-10 required"><?php echo __("勘定科目");?></label>
						<div class="col-md-8 col-xs-12 ">
							<div class = "input-group">
								<select name="sub_acc_id" id="sub_acc_id" class="form-control">
									 <option value="" selected="">---<?php echo __("選択") ?>---
									 </option>
									 <?php $s_id = $request['sub_acc_id']; ?>
									 <?php foreach ($group_1_data as $g1_id => $g1_name): ?>
										<?php $select = ($s_id == $g1_id) ? 'selected' : ''; ?>
										<option value=<?php echo "$g1_id"; ?> <?=$select?>><?php echo __($g1_name); ?></option>
									<?php endforeach ?>
								</select>
						 
								<span class = "input-group-btn">
									<input onclick="" type="button" value="<?php echo __("+");?>" data-target="#myModal" data-toggle="modal" data-backdrop="static" data-keyboard="false" name="" class="btn btn-success" style="padding:7px 12px;">
								</span>
						
							</div>
						</div>
					</div>
					
					<div class = "col-md-6">
						<label class="col-sm-12 col-md-4 col-xs-10 required"><?php echo __("勘定科目グルーピング");?></label>
						<div class="col-md-8 col-xs-12 ">
								<select name="sub_acc_groups" id="sub_acc_groups" class="form-control">
									 <option value="" selected="">---<?php echo __("選択") ?>---
									 </option>
								</select>
						 
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12 form-group">
					<div class="col-md-12 align-right" style="margin-bottom: 40px;">
					
					
						<input type="button" class="btn-save btn-success btn_sumisho" id="btn_update" name="btn_update"value = "<?php echo __('変更');?>" onclick = "UpdateInfo();">
					
						<input type="button" class="btn-save btn-success btn_sumisho" id="btn_save" name="btn_save"value = "<?php echo __('保存');?>" onclick = "saveData();">
					</div>
				</div>
					<!-- end add group_code aznk -->
					<!-- 勘定科目 -->
			</div>
		<!-- </div> -->
				
				<!-- end 小勘定科目 -->
	</div>
		<!-- update -->
		<?php if(!empty($all_accmaster)){?>
		<div class="msgfont" id="total_row">
		<?=$count;?>
		</div>

		<div style="margin-bottom: 100px;" >
			<table class="table table-bordered tbl_sumisho_inventory tbl_data_list" id="tbl_acc">
			<thead>
				<!-- <tr class="blank-cell">
					<th class="w-90 blank-cell"></th>
					<th class="w-200 blank-cell"></th>
					<th class="blank-cell"></th>
					<th class="blank-cell"></th>
					<th class="blank-cell"></th>
					<th class="blank-cell"></th>
					<th class="blank-cell"></th>
					<th class="blank-cell"></th>
					<th class="blank-cell"></th>
					<th class="w-75 blank-cell"></th>
					<th class="w-75 blank-cell"></th>
				</tr> -->
				<tr>
					<th rowspan="2"><?php echo __("勘定科目コード");?></th>
					<th rowspan="2"><?php echo __("小勘定科目名 (JP)");?></th>
					<th rowspan="2"><?php echo __("小勘定科目名 (ENG)");?></th>
					<th colspan="6"><?php echo __("勘定科目グループ");?></th>
					<th colspan="2" rowspan="2"><?php echo __("アクション");?></th>
				</tr>
				<tr>
					<?php foreach ($account_groups as $group_name): ?>
						<th><?php echo __($group_name); ?></th>
					<?php endforeach ?>
				</tr>
			</thead>
			<tbody>
			
					<?php 
					foreach ($all_accmaster as $result)://pr($result);
					$value= $result['SubAccountModel']['sub_acc_code']."--".$result['BrmSaccount']['code']; ?>
					<tr style="text-align: left;">
					<td style="word-break: break-all;"><?php echo h($result['BrmSaccount']['account_code']);?></td>
					<td style="word-break: break-all;"><?php echo h($result['BrmSaccount']['name_jp']);?></td>
					<td style="word-break: break-all;"><?php echo h($result['BrmSaccount']['name_en']);?></td>
					<td style="word-break: break-all;"><?php echo ($result['BrmAccount']['name_jp']);?></td>
					<?php $group_acc_names = $pair_accounts[$result['BrmSaccount']['id']] ?>
					<?php 
					foreach ($group_acc_names as $acc_name): ?>
						<td style="word-break: break-all;"><?php echo ($acc_name);?></td>
					<?php endforeach ?>

					<td width="80px" align="left" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
						<a class="" href="#" style="word-break: break-all;" onclick="EditAccountData(<?php echo h($result['BrmSaccount']['id']); ?>)" title='<?php echo __("編集");?>'>
							<i class="fa-regular fa-pen-to-square"></i>
						</a>
					</td>
					<td width="80px" align="left" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
						<a class="delete_link" href="#" onclick="AccDelete(<?=h($result['BrmSaccount']['id'])?>);" title='<?php echo __("削除");?>'><i class="fa-regular fa-trash-can" ></i></a>
					</td>
					<?php endforeach;?>
				</tr>
			</tbody>
			</table>
	<?php } ?>
	<?php if(!empty($all_accmaster)){ ?>
		
		<div class="paging" style="padding: 10px;text-align: center;">
		<?php
			echo $this->Paginator->first('<<');
			echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev page disabled '));
			echo $this->Paginator->numbers(array('separator'=>'', 'modulus'=>6,'currentTag' => 'a', 'currentClass' => 'active'));
			echo $this->Paginator->next(' >', array(), null, array('class' => 'next page disabled'));
			echo $this->Paginator->last('>>');
		?>
		
		<?php }else{?>
		<div class="row">
		<div class="col-sm-12">
			<p class="no-data"><?php echo $no_data; ?></p>
		</div>
		</div>
		<?php } ?>
	</div>
	<input type="hidden" name="page_name" id="page_name" class="txtbox" value ="">
	<input type="hidden" name="hid_page_no" id="hid_page_no" class="txtbox" value ="">
	</div>
	<!--end row -->
</div>
<!--end container -->

<!-- PopUpBox-->
<form>
 <div class="modal fade" id="myModal" tabindex="-1" role="dialog">
<div class="modal-dialog modal-lg">

	<div class="modal-content contantbond">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" onclick="myPopup()">&times;</button>
		<h3 class="modal-title"><?php echo __("勘定科目作成"); ?></h3>
	</div>
	<div class="modal-body">
		<div class="table-responsive modal_tbl_wrapper">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

			<div class="success" id="succ"></div>
			<div class="error" id="err" method="PopUp"></div>
			</div>
		<table class="table table-bordered" id="tbl_data_detail_modal" style="text-align: center;">
			<div class="col-md-12 ">
				<!-- <div class="row"> -->
					 <div class="form-group">
						<label class="col-md-4 registerpopup required"><?php echo __("グループ");?></label>
						<div class="col-md-8">
						<!-- create and update-->
						<select name="g_code" id="g_code" class="form-control">
							<option value='' selected="">---- <?php echo __("選択") ?> ----</option>
							<?php foreach ($account_groups as $group_code => $group_name): ?>
								<option value="<?php echo($group_code) ?>"><?php echo __($group_name); ?>
							<?php endforeach ?>
						</select>
						</div>
					</div>
				<!-- </div> -->
			</div>
		</table>
 
		<table class="table table-bordered" id="tbl_data_detail_modal" style="text-align: center;">
			<div class="col-md-12">
				<!-- <div class="row"> -->
					 <div class="form-group">
						<label class="col-sm-4 col-md-4 col-xs-4 registerpopup required"><?php echo __("勘定科目名 (JP)");?></label>
						<div class="col-sm-8 col-md-8 col-xs-8 ">
						<!-- create and update-->
						<input class="form-control registerpopup" type="text" id="sub_acc_name_jp" name="sub_acc_name_jp" value="<?php 
						if(!empty($request)){
						echo h($request['login_id']);
						}
						?>">

						</div>
					</div>
				<!-- </div> -->
			</div><!--col-md-6 end -->
		</table>

		<table class="table table-bordered" id="tbl_data_detail_modal" style="text-align: center;">
			<div class="col-md-12">
				<!-- <div class="row"> -->
					 <div class="form-group">
						<label class="col-sm-4 col-md-4 col-xs-4 registerpopup"><?php echo __("勘定科目名 (ENG)");?></label>
						<div class="col-sm-8 col-md-8 col-xs-8 ">
						<!-- create and update-->
						<input class="form-control registerpopup" type="text" id="sub_acc_name_en" name="sub_acc_name_en" value="<?php echo __("");?>" >

						</div>
					</div>
				<!-- </div> -->
			</div><!--col-md-6 end -->
		 </table>


		<div class="col-md-12">
			<!-- <div class="row"> -->
			<div class="form-group col-md-12 col-sm-12 col-xs-12 emp_register_btn_popup" id="save">
				<input onclick="CreateSubAccount();" type="button" value="<?php echo __("追加");?>" name="" class="emp_register but_register">
			</div>
			 <!-- </div> -->
		 </div>
		</div>
	</div>
	</div>
</div>
</div>
</form>

 <!-- PopUpBoxEnd-->
<?php
	echo $this->Form->end();
?>

</div>

</div>
</div>
</div>


<script> 
	$(function() { 
		$("input[name='sub_acc_code']").on('input', function(e) { 
			$(this).val($(this).val().replace(/[^0-9]/g, '')); 
		}); 
	}); 
	function onlynum() { 
	//var fm = document.getElementById("form1"); 
		var sub_acc_code = document.getElementById("sub_acc_code"); 
		var res = sub_acc_code.value; 

		if (res != '') { 
			if (isNaN(res)) { 
				// Set input value empty 
				 document.getElementById('err').innerHTML = "Please Fill Number Only!"; 
				 sub_acc_code.value = "";
				// Reset the form 
				//fm.reset(); 
				return false; 
			} else { 
			document.getElementById('err').innerHTML = ""; 
				return true;
			} 
		} 
	} 

	$(function() { 
		$("input[name='acc_code']").on('input', function(e) { 
			$(this).val($(this).val().replace(/[^0-9]/g, '')); 
		}); 
	}); 
	
	function onlyN() { 
	//var fm = document.getElementById("form1"); 
		var acc_code = document.getElementById("acc_code"); 
		var res = acc_code.value; 

		if (res != '') { 
			if (isNaN(res)) { 
				// Set input value empty 
				 document.getElementById('success').innerHTML="";
				 document.getElementById('error').innerHTML = "Please Fill Number Only!"; 
				 acc_code.value = "";
				// Reset the form 
				//fm.reset(); 

				return false; 
			} else { 
			document.getElementById('error').innerHTML = ""; 
				return true;
			} 
		} 
	}

	function myPopup() {
	document.getElementById("err").innerHTML= '';
	}

</script>

