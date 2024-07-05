<style>
   
   .table > tbody > tr > td{
      
      vertical-align: middle;
   }
   @media (min-width: 767px) and (max-width: 991px) {
      .col-sm-4.col-form-label {
        font-size: small;
        text-justify: inter-word;
      }
      label.radio-inline {
         margin-left: 10px;
         
      }  

   }
   @media (max-width: 767px) {
      button#btn_search {
         margin-top: 20px !important;
      }
   }
   
   .datepicker {
     z-index: 1999 !important;
   }

   td.date-list div.datepicker {
      z-index: 0 !important;
   }

   .acc_preview {
     margin-top: 0px !important;
   }
   td, th{
      padding: 5px;
   }
   .fl-scrolls{
        z-index: 1 !important;
    }

   #load{
     
      z-index: 1000;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.2);
   }
   .row {
		display: block;
	}
   .jconfirm .jconfirm-box div.jconfirm-content-pane .jconfirm-content {
      overflow: hidden !important; 
   }
   input[type="text"]:disabled, textarea[disabled] {
      background-color: #eee !important;
   }

   /* edit mode toggle */
   .switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
   }
   .switch input { 
      opacity: 0;
      width: 0;
      height: 0;
   }
   .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      -webkit-transition: .4s;
      transition: .4s;
   }
   .slider:before {
      position: absolute;
      content: "";
      height: 26px;
      width: 26px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      -webkit-transition: .4s;
      transition: .4s;
   }
   input:checked + .slider {
      background-color: #2A807F;
   }
   input:focus + .slider {
      box-shadow: 0 0 1px #2A807F;
   }
   input:checked + .slider:before {
      -webkit-transform: translateX(26px);
      -ms-transform: translateX(26px);
      transform: translateX(26px);
   }
   /* Rounded sliders */
   .slider.round {
      border-radius: 7px;
   }
   .slider.round:before {
      border-radius: 19%;
   }
   .no-edit[readonly], select.no-edit[disabled] {
      background-color: #fff !important;
      border: none !important;
      cursor: context-menu;
      box-shadow: none !important;
      /* resize: none; */
      /* overflow:hidden; */
      outline: none !important;
      color: #333333 !important;
      /* appearance: none; */
   }
   textarea, textarea.change-edit-mode {
      padding-top: 23px;
      resize: horizontal;
   }
   .tooltip {
      background-color: #333;
      color: #fff;
      padding: 5px;
      border-radius: 5px;
   }

  .tooltip.error {
      background-color: #ff0000;
   }
   .no_edit_flag3[readonly] {
      cursor: not-allowed;
      box-shadow: none !important;
      color: #333333 !important;
   }
   .previewCommt {
      padding-top: 23px;
   }

</style>

<script>

   $(document).ready(function(){
      $(".datepicker").datepicker({
         todayHighlight: true,
         autoclose: true
      });

      $("#save_btn").css({"display":"none"});
      $(".edit-mode").css({"display":"none"});
      if($('#tbl_previewAndEdit').length > 0) {
         $('.tbl-wrapper').freezeTable({ 
            'namespace' : 'tbl-freeze-table',
            'columnNum' : 7,
            'columnKeep': true,
            'freezeHead': true,   
            'scrollBar' : true,
            });

         setTimeout(function(){
            $('.tbl-wrapper').freezeTable('resize');
         }, 1000);
        
      }
   
      /* floating scroll */
      if($(".tbl-wrapper").length) {
         $(".tbl-wrapper").floatingScroll();
      }
      
      if($('html').scrollTop() != 0){
         $('html, body').animate({ scrollTop: 0 }, 'slow');
      }
     
      document.getElementById("error").innerHTML =  '';
      document.getElementById("success").innerHTML =  '';

      $('.clone-column-table-wrap #chk_acc_mgr_confirm, .clone-head-table-wrap #chk_acc_mgr_confirm').click(function() {
      
         if($(this).is(':checked')) {
            // $('#save_and_mail-btn').show();    
            $('.clone-head-table-wrap #chk_acc_mgr_confirm, .clone-column-table-wrap #chk_acc_mgr_confirm').prop('checked',true);

            $('.clone-column-table-wrap .preview-check').each(function() {

               if (!($(this).is(':disabled'))) {
                  $(this).prop('checked',true);
                  $('.clone-head-table-wrap #chk_acc_mgr_confirm').prop('checked',true);
               }
            });

         }else {
            // $('#save_and_mail-btn').hide();
            $('.clone-head-table-wrap #chk_acc_mgr_confirm, .clone-column-table-wrap #chk_acc_mgr_confirm').prop('checked',false);

            $('.clone-column-table-wrap .preview-check').each(function() {

               if (!($(this).is(':disabled'))) {
                  $(this).prop('checked',false);
                  $('.clone-head-table-wrap #chk_acc_mgr_confirm').prop('checked',false);
               }

            });
            
         }
      
      });

      $('.preview-check').click(function() {
         $('.clone-head-table-wrap .preview-check').prop('checked', true);

         checkToggle();

      });

      function checkToggle() {

         var isCheck = true;
         var disable = 0;
         var rows = 0;
         $('.clone-column-table-wrap .preview-check').each(function() {

            rows++;

            if($(this).is(':checked') == false) {
               isCheck = false;
            }
            
            if ($(this).is(':disabled')) {
               
               disable++;
               
            }
         })
         
         if(rows == disable){
            
            $('.clone-column-table-wrap #chk_acc_mgr_confirm, .clone-head-table-wrap #chk_acc_mgr_confirm').prop( "disabled", true);
         }

         if(isCheck == false) {
            $('.clone-column-table-wrap #chk_acc_mgr_confirm, .clone-head-table-wrap #chk_acc_mgr_confirm').prop('checked', false);
            
         } else {
            $('.clone-column-table-wrap #chk_acc_mgr_confirm, .clone-head-table-wrap #chk_acc_mgr_confirm').prop('checked', true);
         }
      }

      checkToggle();

      /* edit mode on/off */
      $(".change-edit-mode").prop('readonly', true);
      $("select.change-edit-mode").attr('disabled', true);
      $(".change-edit-mode").addClass('no-edit');
      $(".change-edit-mode").removeClass('form-control');
      
      $("#editMode").click(function() {
         if($(this).is(':checked')) {
            $(".change-edit-mode").prop('readonly', false);
            $("select.change-edit-mode").attr('disabled', false);
            $(".change-edit-mode").removeClass('no-edit');
            $(".change-edit-mode").addClass('form-control');
            $(".span_one").addClass('input-group-addon');
            $(".span_two").addClass('glyphicon glyphicon-calendar');
            $("td.date-list div").addClass('input-group date datepicker');
            $("td.date-list div").attr('data-provide', 'datepicker');
            $("td.date-list div").attr('data-date-format', 'yyyy-mm-dd');
            $(".clone-column-table-wrap .acc_preview tbody tr").each(function(i, tr){
               if($(this).find(".preview-check").is(':disabled')) {
                  $(this).find(".change-edit-mode").prop('readonly', true);
                  $(this).find(".change-edit-mode").addClass('no_edit_flag3');
               }
            });
            $(".acc_preview tbody tr").each(function(i, tr){
               if($(this).find(".preview-check").is(':disabled')) {
                  $(this).find(".change-edit-mode").prop('readonly', true);
                  $(this).find("select.change-edit-mode").prop('disabled', true);
                  $(this).find(".change-edit-mode").addClass('no_edit_flag3');
                  $(this).find("td.date-list div").attr('data-provide', '');
               }
            });
         }else {
            $(this).find(".change-edit-mode").removeClass('no_edit_flag3');
            $(".change-edit-mode").prop('readonly', true);
            $("select.change-edit-mode").attr('disabled', true);
            $(".change-edit-mode").addClass('no-edit');
            $(".change-edit-mode").removeClass('form-control');
            $(".span_one").removeClass('input-group-addon');
            $(".span_two").removeClass('glyphicon glyphicon-calendar');
            $("td.date-list div").removeClass('input-group date datepicker');
            $("td.date-list div").attr('data-provide', '');
            $("td.date-list div").attr('data-date-format', '');
         }
      });
      $('.registration-date').on('input', function() {{$(this).val('');}});

      /* check date format */
      $("td").on('input','.reg-date', function() {$(this).val('');});

      let itemCode = $('.item-code');
      let noOfDay = $('.no-of-day');
      let quantity = $('.quantity');

      noOfDay.on('input', function(event) {
         let noOfDayInput = this.value;
         this.value = noOfDayInput.replace(/\D/g, '');
         if(noOfDayInput.length > 10) {
            let noOfDayVal = noOfDayInput.substring(0, 10);
            this.value = noOfDayVal;
         }
      });
      quantity.on('input',function(event) {
         let quantityInput = this.value;
         this.value = quantityInput.replace(/\D/g, '');
         if(quantityInput.length > 10) {
            let quantityVal = quantityInput.substring(0, 10);
            this.value = quantityVal;
         }
      });
      itemCode.on('input',function(event) {
         let itemCodeInput = event.target.value;
         // event.target.value = itemCodeInput.replace(/\D/g, '');
         if(itemCodeInput.length > 20) {
            let itemCodeVal = itemCodeInput.substring(0, 20);
            itemCode.val(itemCodeVal);
         }
      });

   });

   function searchPreview(){

      document.getElementById("error").innerHTML   = '';
      document.getElementById("success").innerHTML   = '';
      document.getElementById("messageContent").innerHTML =  '';
      var chk = true;
      var form = document.getElementById("PreviewAndEdit");
      if(chk){ 
         form.action = "<?php echo $this->webroot; ?>StockAccountPreviews/searchPreview"; 
         form.method = "GET";
         form.submit();
         return true;   
      }
      scrollText();  
   }

   // delete Stock data in table
   function deleteStock(stock_id){
      document.getElementById("error").innerHTML = "";
      document.getElementById("success").innerHTML ="";
      document.getElementById("messageContent").innerHTML =  '';

      var chk = true;
      document.getElementById('hid_del_stockId').value = stock_id;

      var path = window.location.pathname;
      var page = path.split("/").pop();
      document.getElementById('hid_page_no').value = page;
      

      if(chk) {
         var stock_id = $("#hid_del_stockId").val();
         $.confirm({
            title: '<?php echo __("削除確認"); ?>',
            icon: 'fas fa-exclamation-circle',
            type: 'red',
            typeAnimated: true,
            animateFromElement: true,
            animation: 'top',
            draggable: false,
            content: errMsg(commonMsg.JSE017),
            buttons: {   
               ok: {
                  text: "<?php echo __("はい"); ?>",
                  btnClass: 'btn-info',
                  action: function(){
                     $.ajax({
                        url: "<?php echo $this->webroot; ?>StockAccountPreviews/Delete_Preview",
                        type: 'post',
                        data: {'stock_id':stock_id},
                        success: function(data) {
                           window.location.reload();
                        },
                        error: function(e) {
                           alert("Please refresh the page!");
                        }
                     });
                  }
               },
               cancel : {
                  text: "<?php echo __("いいえ"); ?>",
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
   
   function clickSaveBtn(){

      document.getElementById("error").innerHTML = '';
      document.getElementById("success").innerHTML = '';
      document.getElementById("messageContent").innerHTML =  '';

      var DataArray = [];     
      var checkflag = true;
      var checkedCnt = 0;
      var uncheckedCnt = 0;
      
      $(".clone-column-table-wrap .acc_preview tbody tr").each(function(i, tr){
         
         var chk_status = false;
         var row_id = $(this).find('.preview-check').val();
         var skip_or_not = $(this).find('.skipError').val();
         var text_value = $.trim($(this).find('.previewCommt').val());
         
         if ($(this).find('.preview-check').prop("checked") == true){
            var chk_status = true;
            if($('#flag'+row_id).val() == 1) checkedCnt++;
            
         } else {
            uncheckedCnt++;
         }

         let previewCommt  = $.trim($(this).find('#previewCommt').val());
         let layerCode     = '<?php echo ($this->Session->read('SESSION_LAYER_CODE')); ?>';
         let destName      = $.trim($(this).find('.dest-name').val());
         let itemCode      = $.trim($(this).find('.item-code').val());
         let itemName      = $.trim($(this).find('.item-name').val());
         let itemName2     = $.trim($(this).find('.item-name-2').val());
         let unit          = $.trim($(this).find('.unit').val());
         let regDate       = $.trim($('#regDate'+row_id).val());
         let numOfDays     = $.trim($('#noOfDays'+row_id).val()) == '' ? 0 : $.trim($('#noOfDays'+row_id).val());
         let receiptIndex  = $.trim($('#receipt-index'+row_id).val());
         let quantity      = $.trim($('#quantity'+row_id).val()) == '' ? 0 : $.trim($('#quantity'+row_id).val());
         let isError       = $.trim($('#is-error'+row_id).val());
         let isSold        = $.trim($('#is-sold'+row_id).val());
         let isContract    = $.trim($('#is-contract'+row_id).val());
         // let reason        = $.trim($('#reason'+row_id).val());
         // let solution      = $.trim($('#solution'+row_id).val());
         
         //no comment in each rows when not check, show error msg 
         //"skip" means although this row of flag is 1, code is approved. 
         if(skip_or_not == "not_skip"){
            if( chk_status == false && text_value == ""){
               document.getElementById("error").innerHTML = errMsg(commonMsg.JSE030);
               checkflag = false;
               return false;
            }
         }
         
         var myDataRows = [];
         if( chk_status == true || text_value != ''){ //check or text
            if(row_id != '0'){
               myDataRows.push(row_id,text_value,chk_status,layerCode,destName,itemCode,itemName,itemName2,unit, regDate,numOfDays,receiptIndex,quantity,isError,isSold,isContract);
               DataArray.push(myDataRows);
            }
         }

      });
      
      var myJSONString = JSON.stringify(DataArray);
      var savedCnt = <?php echo json_decode(($saved_count == '') ? 0 : $saved_count); ?>;
      var rowCnt = <?php echo json_decode(($count == '') ? 0 : $count); ?>;
      var noSend = ((savedCnt == rowCnt && uncheckedCnt > 0) || (savedCnt+checkedCnt != rowCnt)) ? true : false;

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
                  text: '<?php echo __("はい");?>',
                  btnClass: 'btn-info',
                  action: function(){
                     $('#json_data').val(myJSONString);
                     getMail('Save',noSend);
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

   function getMail(func, noSend = false) {

      var layer_code = $("#choose_ba").val();
      var page = 'StockAccountPreviews';
      var mail = {};
      $.ajax({
         type:'post',
         url: "<?php echo $this->webroot; ?>StockImports/getMailLists",
         data: {layer_code : layer_code, page: page, function: func},
         dataType: 'json',
         success: function(data) {
            
            var mailSend = (data.mailSend == '' || noSend) ? '0' : data.mailSend;
            $("#mailSend").val(mailSend);
            
            if(mailSend == 1) { 

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
                  loadingPic();
                  $('#PreviewAndEdit').attr('action', "<?php echo $this->Html->url(array('controller'=>'StockAccountPreviews','action'=>'SaveCheckAndComment')) ?>").submit();
                 
               }else{

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
                  $('#PreviewAndEdit').attr('action', "<?php echo $this->Html->url(array('controller'=>'StockAccountPreviews','action'=>'SaveCheckAndComment')) ?>");

               }
            }else {
            	loadingPic();
               document.forms[0].action = "<?php echo $this->webroot; ?>StockAccountPreviews/SaveCheckAndComment";
               document.forms[0].method = "POST";
               document.forms[0].submit();
            }
           
         },
         error: function(e) {
             console.log('Something wrong! Please refresh the page.');
         }

      });
      return mail;
   }
   function scrollText(){
      
      var tes = $('#error').text();
      var tes1 = $('.success').text();
      if(tes){
         $("html, body").animate({ scrollTop: 0 }, 300);          
      }
      if(tes1){
         $("html, body").animate({ scrollTop: 0 }, 300);          
      }
   }

   function loadingPic() {
      $("#overlay").show();
      $('.jconfirm').hide();
   }
</script>
<?php
   echo $this->element('autocomplete', array(
                        "to_level_id" => "",
                        "cc_level_id" => "",
                        "bcc_level_id" => "",
                        "submit_form_name" => "PreviewAndEdit",
                        "MailSubject" => "",
                        "MailTitle"   => "",
                        "MailBody"    =>""
                     ));
?>
<div id="overlay">
   <span class="loader"></span>
</div>
<div class="container register_container">
   <form name="PreviewAndEdit" id="PreviewAndEdit" method="post">
      <div class="row">
         <h3 style="padding-left: 15px;"><?php echo __('経理プレビュー');?></h3>
         <hr>
         <div class="errorSuccess">    
            <div class="success" id="success"><?php echo $successMsg;?></div>
            <div class="error" id="error"><?php echo $errorMsg;?></div>
            <div class="success" id="sucss"></div>
            <div class="error" id="err"></div>   
         </div>
         <div class="errorSuccess" id="messageContent">

            <?php if($this->Session->check('Message.deleteSuccess')): ?>
               <div class="success" id="sess_error">
                  <?php echo $this->Flash->render("deleteSuccess"); ?>
               </div>
            <?php endif; ?>  

            <?php if($this->Session->check('Message.saveError')): ?>
               <div class="error" id="sess_error">
                  <?php echo $this->Flash->render("saveError"); ?>
               </div>
            <?php endif; ?>      
         </div>
         <?php 
            # default checked
            $match_SR      = 'checked';
            $not_match_SR  = '';

            $match_Des     = 'checked';
            $not_match_Des    = '';

            $match_RegIndexNo    = 'checked';
            $not_match_RegIndexNo   = '';

            $match_Currency   = 'checked';
            $not_match_Currency = '';

            if($this->Session->check('SESSION_LAYER_CODE')) {
               $choose_ba = $this->Session->read('SESSION_LAYER_CODE');
            }

            # search data
            if(!empty($search_data)){
               $destination = $search_data['destination'];
               $choose_ba = $search_data['choose_ba'];
               $registrationDate = $search_data['registrationDate'];
               $recIndexNo = $search_data['recIndexNo'];
               $match_status_Des = $search_data['optDesCondition'];
               $match_status_RegIndexNo = $search_data['optRegIndexNoCondition'];
               

               if($match_status_Des == 1) {
                  $match_Des = 'checked';
                  $not_match_Des = '';
               } else if($match_Des == 0) {
                  $match_Des = '';
                  $not_match_Des = 'checked';
               }

               if($match_status_RegIndexNo == 1) {
                  $match_RegIndexNo = 'checked';
                  $not_match_RegIndexNo = '';
               } else if($match_RegIndexNo == 0) {
                  $match_RegIndexNo = '';
                  $not_match_RegIndexNo = 'checked';
               }
            }
         ?>
         <div class="form-group row">
            <div class="col-sm-4">
               <label for="Target_Month" class="col-sm-4 col-form-label">
                  <?php echo __('対象月');?>
               </label>
               <div class="col-sm-8">
                  <input type="text" class="form-control" id="target_month" name="target_month" value="<?php echo $target_month; ?>" disabled="" />   
               </div>
            </div>
            <div class="col-sm-4">
               <label for="registrationDate" class="col-sm-4 col-form-label">
                  <?php echo __('入庫日');?>
               </label>
               <div class="col-sm-8">
                  <div class="input-group date datepicker" data-provide="datepicker" style="padding: 0px;" data-date-format="yyyy-mm-dd">
                     <input type="text" class="form-control registration-date" id="registrationDate" name="registrationDate" value="<?php if(!empty($search_data)){echo htmlspecialchars($registrationDate);} ?>" autocomplete="off" />
                     <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                     </span>
                  </div>
               </div>
            </div>
         </div>
         <div class="form-group row">
            <div class="col-sm-4">
               <label for="Base_Date " class="col-sm-4 col-form-label">
                  <?php echo __('基準年月日');?>
               </label>
               <div class="col-sm-8">
                  <input type="text" class="form-control" id="base_date" name="base_date" value="<?php echo $base_date; ?>" disabled="" />   
               </div>
            </div>
            <div class="col-sm-4">
               <label for="destination" class="col-sm-4 col-form-label">
                  <?php echo __('相手先名');?>
               </label>
               <div class="col-sm-8">
                  <input type="text" class="form-control destination" id="destination" name="destination" value="<?php if(!empty($search_data)){echo htmlspecialchars($destination);} ?>"/>     
               </div>
            </div>
            <div class="col-sm-4">
               <div class="col-sm-8">
                  <label class="radio-inline"><input type="radio" name="optDesCondition" value="1" <?php echo $match_Des; ?>><?php echo __('一致(=)'); ?></label>
                  <label class="radio-inline"><input type="radio" name="optDesCondition" value="0" <?php echo $not_match_Des; ?>><?php echo __('除外(≠)'); ?></label>
               </div>
            </div>      
         </div>
         <div class="form-group row">
            <div class="col-sm-4">
               <label for="deadLine_date" class="col-sm-4 col-form-label">
                  <?php echo __('提出期日');?>
               </label>
               <div class="col-sm-8">
                  <input type="text" class="form-control" id="deadLine_date" name="deadLine_date" value="<?php echo $deadLine_date; ?>" disabled="" />   
               </div>
            </div>      
            <div class="col-sm-4">
               <label for="recIndexNo" class="col-sm-4 col-form-label">
                  <?php echo __('レシートインデックス番号');?>
               </label>
               <div class="col-sm-8">
                  <input type="text" class="form-control" id="logisticIndexNo" name="recIndexNo" value="<?php if(!empty($search_data)){echo htmlspecialchars($recIndexNo);} ?>" />
               </div>
            </div>
            <div class="col-sm-4">
               <div class="col-sm-8">
                  <label class="radio-inline"><input type="radio" name="optRegIndexNoCondition" value="1" <?php echo $match_RegIndexNo; ?>><?php echo __('一致(=)'); ?></label>
                  <label class="radio-inline"><input type="radio" name="optRegIndexNoCondition" value="0" <?php echo $not_match_RegIndexNo; ?>><?php echo __('除外(≠)'); ?></label>
               </div>
            </div>   
         </div>
         <div class="form-group row">
            <div class="col-sm-4">
               <label for="BA_code" class="col-sm-4 col-form-label">
                  <?php echo __($this->session->read('StockSelections_code'));?>
               </label>
               <div class="col-sm-8">
                  
                  <select class="form-control" id="choose_ba" name="choose_ba" disabled="">
                     <option value="">--- <?php echo __("Select Layer Name"); ?> ---</option>
                     <?php
                        if(!empty($all_BA)) {
                           $len = count($all_BA);
                           for($i=0; $i<$len; $i++) {
                              $each_code = $all_BA[$i]['layer_code'];
                              $each_name_jp = $all_BA[$i]['name_jp'];
                              if($each_code == $choose_ba) {
                                 $selected = "selected";
                              } else {
                                 $selected = "";
                              }
                     ?>
                     <option value="<?php echo $each_code; ?>" <?php echo $selected; ?>><?php echo $each_code.'/'.$each_name_jp; ?></option>
                     <?php
                           }
                        }
                     ?>
                  </select>
               </div>
            </div>
         </div>
         <div class="form-group row">
            <div class="col-sm-4"></div>
            <div class="col-sm-4 text-right" style="margin-left:-15px; margin-bottom: 1rem;">
               <button type="button" id="btn_search" class="btn btn-success btn_sumisho" onclick="searchPreview();">
                  <?php echo __("検索"); ?>
               </button>
            </div>
         </div> 
         <?php if ($checkButtonType['Save'] && $showSaveBtn) { ?>
            <div class="form-group row edit-mode">
               <div class="col-sm-4">
                  <label for="" class="col-sm-4 col-form-label">
                     <?php echo __('Edit Mode');?>
                  </label>
                  <div class="col-sm-8">
                     <label class="switch">
                        <input type="checkbox" id="editMode">
                        <span class="slider round"></span>
                     </label>
                  </div>
               </div>
            </div>  
         <?php } ?>     
      </div>
      <input type="hidden" name="json_data" id="json_data" value = "" >
      <input type="hidden" name="toEmail" id="toEmail" value="">
      <input type="hidden" name="ccEmail" id="ccEmail" value="">
      <input type="hidden" name="bccEmail" id="bccEmail" value="">
      <input type="hidden" name="mailSubj" id="mailSubj">
      <input type="hidden" name="mailBody" id="mailBody">
      <input type="hidden" name="mailSend" id="mailSend">
  </form>
  <div class="text-right" style="float:right;">
      <?php if ($checkButtonType['Save'] && $showSaveBtn) { ?>
         <input type="button" class="btn btn-success btn_sumisho" id="save_btn" onClick = "clickSaveBtn();" value = "<?php echo __('保存');?>">
         
         <?php  } ?>
   </div>
   <div id="error_msg">
      <div class="msgfont"><?php if(!empty($succCount)){echo $succCount;}?></div>
      <?php if(!empty($errCount)){?>
            <div id="err" class="no-data" ><?php echo $errCount; ?>  
            </div>
      <?php } ?>
   </div>   
   <?php echo $this->Form->create(false,array('url'=>'','id'=>'preview_edit_form','class'=>'preview&edit_form','name'=>'preview_edit_form')); 
   ?>
   <?php if(($count)!=0){ $rno = 0; $i = 0;?>
      <div class="row">
         <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;"> 
            <div class="table-responsive tbl-wrapper" style="overflow-x:auto;">
            
               <table class="table-bordered acc_preview" style="min-width: 1754px;" id="tbl_previewAndEdit">
            
                  <thead class="check_period_table ">
                     <tr>
                        <th width="15%" style ="text-align: center;" for = "confirm"><div style="width:3rem;"></div><?php echo __("確認対象"); ?><br/>
                           <input type="checkbox" name="chk_acc_mgr_confirm" id="chk_acc_mgr_confirm" checked="checked">
                        </th>
                        <th width="15%" for = "Comment"><?php echo __("コメント"); ?></th>
                        <!-- <th width="5%" for = "LayerCode"><?php echo __("部署"); ?></th> -->
                        <th width="15%" for = "Destination"><?php echo __("相手先名"); ?></th>
                        <th width="15%" for = "itemCode"><?php echo __("品目コード"); ?></th>
                        <th width="15%" for = "itemName"><?php echo __("品目テキスト"); ?></th>
                        <th width="15%" for = "itemName2"><?php echo __("品目名2"); ?></th>
                        <th width="15%" for = "unit"><?php echo __("単位"); ?></th>
                        <th width="15%" for = "RegistrationDate"><?php echo __("入庫日"); ?></th>
                        <th width="15%" for = "NoOfDays"><?php echo __("滞留日数"); ?></th>
                        <th width="15%" for = "receiptIndexNo"><?php echo __("レシートインデックス番号"); ?></th>
                        <th width="15%" for = "quantity"><?php echo __("数量"); ?></th>
                        <th width="15%" for = "amount"><?php echo __("金額"); ?></th>
                        <th width="15%" for = "isError"><?php echo __("不完全品 有・無"); ?></th>
                        <th width="15%" for = "isSold"><?php echo __("売り繋ぎ 済・未済"); ?></th>
                        <th width="15%" for = "isContract"><?php echo __("契約 有・無"); ?></th>
                        <!-- <th width="30%" for = "reason" ><?php echo __("事由"); ?></th> -->
                        <!-- <th width="30%" for = "solution" ><?php echo __("解決"); ?></th> -->
                        <th width="15%" for = "delete" style="min-width: 4rem;"></th>
                     </tr>
                  </thead>
                  <tbody> 
                     <?php 
                     if(!empty($StockImportsInfo)) foreach($StockImportsInfo as $row){
                        
                        $rno++;
                        $layerCode        = $row['Stock']['layer_code'];
                        $destination      = $row['Stock']['destination_name'];
                        $itemCode         = $row['Stock']['item_code'];
                        $itemName         = $row['Stock']['item_name'];
                        $itemName2        = $row['Stock']['item_name_2'];
                        $unit             = $row['Stock']['unit'];
                        $NoOfDays         = $row['Stock']['numbers_day'] == 0 ? '' : $row['Stock']['numbers_day'];
                        $receiptIndexNo   = $row['Stock']['receipt_index_no'];
                        $quantity         = $row['Stock']['quantity'] == 0 ? '' : $row['Stock']['quantity'];
                        $amount           = number_format($row['0']['amount']);
                        $isError          = $row['Stock']['is_error'];
                        $isSold           = $row['Stock']['is_sold'];
                        $isContract       = $row['Stock']['is_contract'];
                        $previewComment   = $row['Stock']['preview_comment'];
                        $reason           = $row['Stock']['reason'];
                        $solution         = $row['Stock']['solution'];
                        $registrationDate     = $row['Stock']['registration_date'];
                        if($registrationDate != "0000-00-00" && $registrationDate != "" ){
                           $registrationDate = date('Y-m-d', strtotime($registrationDate));
                        }else{
                           $registrationDate = "";
                        }
                        $deadlineDate  = $row['Stock']['deadline_date'];

                        if($deadlineDate != "0000-00-00 00:00:00" && $deadlineDate != "" ){
                           $deadlineDate = date('Y-m-d', strtotime($deadlineDate));
                        }else{
                           $deadlineDate = "";
                        }
                        
                        $flag    = $row['Stock']['flag'];
                        $stockId   = $row['Stock']['id'];
                        //new added flow

                        $baCode   = $row['Stock']['layer_code'];
                        
                        //if has code of flag are equal or over flag 5, disable this code
                        $disBaCode = "";
                        if(!empty($overFlag5)){
                           
                           if(in_array($baCode,$overFlag5)){
                              $disBaCode = $baCode;
                           }
                           
                        }

                        //added new feedback5 (base_date - settlement plan date)
                        // Calulating the difference in timestamps 
                        $diff = strtotime($base_date) - strtotime($deadlineDate);  
                        
                        // 1 day = 24 hours 
                        // 24 * 60 * 60 = 86400 seconds 
                        // $NoOfDays = round($diff / 86400); 
                        
                        if(($flag != '1' && $flag != '2')||($baCode == $disBaCode)){ $i++; ?>
                        
                           <tr> <!-- style="background-color: #e8e8e8;" -->
                              <?php if($flag == '1'){ ?>

                                 <td style="word-wrap: break-word;text-align:center;"><input type="checkbox" name="preview_check" class="preview-check" value = "0" disabled=""><input type="hidden" name="skipError" class="skipError" value="<?php echo("skip"); ?>"></td>
                              <?php }else{ ?>
                                 <td style="word-wrap: break-word;text-align:center;"><input type="checkbox" name="preview_check" class="preview-check" checked="" value = "0" disabled=""><input type="hidden" name="skipError" class="skipError" value="<?php echo("not_skip"); ?>" ></td>
                              <?php } ?>
                              <td style="word-wrap: break-word;text-align:center;"> 
                                 <textarea rows="2" cols="2" class="form-control previewCommt" style="width:10rem;padding-top: 23px;" readonly=""><?php if(!empty($previewComment)) echo ($previewComment); ?></textarea>
                              </td>
                        <?php }else{ ?>
                           <tr>
                           <?php if($flag == '1' && !empty($previewComment)){ ?>
                              <td style="word-wrap: break-word;text-align:center;">
                                 <input type="checkbox" name="preview_check" class ="preview-check" value = "<?php echo h($stockId);?>">
                                 <input type="hidden" name="skipError" class="skipError" value="<?php echo("not_skip"); ?>" >
                              </td>
                           <?php }else{ ?>
                              <td style="word-wrap: break-word;text-align:center;">
                                 <input type="checkbox" name="preview_check" class ="preview-check" checked="checked" value = "<?php echo h($stockId);?>">
                                 <input type="hidden" name="skipError" class="skipError" value="<?php echo("not_skip"); ?>" >
                              </td>
                           <?php } ?>
                           <td style="word-wrap: break-word;text-align:center;"> 
                              <textarea rows="2" cols="2" class="form-control previewCommt" name="previewCommt" id = "previewCommt" style="width:10rem;padding-top: 23px;" maxlength="50"><?php if(!empty($previewComment))echo ($previewComment);?></textarea>
                           </td>
                        <?php } ?>

                           <!-- <td>
                              <textarea style="word-wrap: break-word;text-align:center;width: 120px;" class="form-control layer-code change-edit-mode"><?php echo $layerCode; ?></textarea>
                           </td> -->
                           <td>
                              <textarea style="word-wrap: break-word;;width: 10rem;" class="form-control dest-name change-edit-mode" maxlength="50"><?php echo $destination; ?></textarea>
                           </td>
                           <td>
                              <textarea style="word-wrap: break-word;width: 10rem;" class="form-control item-code change-edit-mode" maxlength="20"><?php echo $itemCode; ?></textarea>
                           </td>
                           <td>
                              <textarea style="word-wrap: break-word;width: 10rem;" class="form-control item-name change-edit-mode" maxlength="50"><?php echo $itemName; ?></textarea>
                           </td>
                           <td>
                              <textarea style="word-wrap: break-word;width: 10rem;" class="form-control item-name-2 change-edit-mode" maxlength="50" id="<?php echo 'itemName2'.$stockId; ?>"><?php echo $itemName2; ?></textarea>
                           </td>
                           <td>
                              <textarea style="word-wrap: break-word;width: 5rem;" id="<?php echo 'unit'.$stockId; ?>" class="form-control unit change-edit-mode" maxlength="4"><?php echo $unit; ?></textarea>
                           </td>
                           <td class='date-list'>
                              <div>
                                 <input style="text-align:left;width: 7rem; padding: 5px;" type="text" class="form-control reg-date change-edit-mode"  id="<?php echo 'regDate'.$stockId; ?>" value="<?php echo $registrationDate; ?>">
                                 <span class="span_one">
                                    <span class="span_two"></span>
                                 </span>
                              </div>
                           </td> 
                           <td>
                              <textarea style="word-wrap: break-word;width: 10rem;" id="<?php echo 'noOfDays'.$stockId; ?>" class="form-control no-of-day change-edit-mode"><?php echo $NoOfDays; ?></textarea>
                           </td>
                           <td>
                              <textarea style="word-wrap: break-word;width: 10rem;" id="<?php echo 'receipt-index'.$stockId; ?>" class="form-control receipt-index change-edit-mode" maxlength="20"><?php echo $receiptIndexNo; ?></textarea>
                           </td>
                           <td>
                              <textarea style="word-wrap: break-word;width: 10rem;" id="<?php echo 'quantity'.$stockId; ?>" class="form-control quantity change-edit-mode"><?php echo $quantity; ?></textarea>
                           </td>
                           <td style="word-wrap: break-word;width: 10rem;text-align:right;" id="<?php echo 'amount'.$stockId; ?>" class="amount">
                              <?php echo $amount; ?>
                           </td>
                           <td>
                              <select class="form-control is-error change-edit-mode" style="word-wrap: break-word;width: 5rem;" name="" id="<?php echo 'is-error'.$stockId; ?>" >
                                 <option value="有" <?php if($isError == '有') echo 'selected';else echo ''; ?>><?php echo ("有"); ?></option>
                                 <option value="無" <?php if($isError == '無') echo 'selected';else echo ''; ?>><?php echo ("無"); ?></option>
                              </select>
                           </td>
                           <td>
                              <select style="word-wrap: break-word;width:5rem;" name="is_sold" id="<?php echo 'is-sold'.$stockId; ?>" class="form-control is-sold change-edit-mode">
                                 <option value="済" <?php if($isSold == '済') echo 'selected';else echo ''; ?>><?php echo ("済") ?></option>
                                 <option value="未済" <?php if($isSold == '未済') echo 'selected';else echo ''; ?>><?php echo ("未済") ?></option>
                              </select>
                           </td>
                           <td>
                              <select style="word-wrap: break-word;width: 5rem;" name="is_contract" id="<?php echo 'is-contract'.$stockId; ?>" class="form-control is-contract change-edit-mode">
                                 <option value="有" <?php if($isContract == '有') echo 'selected';else echo ''; ?>><?php echo ("有") ?></option>
                                 <option value="無" <?php if($isContract == '無') echo 'selected';else echo ''; ?>><?php echo ("無") ?></option>
                              </select>
                           </td>
                           <!-- <td>
                              <textarea style="width: 20rem;height:auto;" id="<?php echo 'reason'.$stockId; ?>" class="form-control reason change-edit-mode"><?php echo $reason; ?></textarea>
                           </td> -->
                           <!-- <td>
                              <textarea style="width: 20rem;height:auto;" id="<?php echo 'solution'.$stockId; ?>" class="form-control solution change-edit-mode"><?php echo $solution; ?></textarea>
                           </td> -->
                           <td style="display: none;">
                              <input type="hidden" id="<?php echo 'flag'.$stockId; ?>" class="flag" value="<?php echo $flag;  ?>">
                           </td>
                           <?php if(($flag != '1' && $flag != '2')||($baCode == $disBaCode)){?>
                              <td style="word-wrap: break-word; text-align:center;" aria-disabled="true">
                                 <a class="disabled" style="text-decoration: none;cursor: not-allowed;opacity: 0.5;font-size: 1.5rem;" title="<?php echo __('削除')?>"><i class="fa-regular fa-trash-can"></i></a>
                              </td>
                           <?php }else{?>
                              <td style="word-wrap: break-word; text-align:center;">
                                 <a class="" style="cursor:pointer;font-size: 1.5rem;" onclick="deleteStock(<?php echo $stockId;?>)"; title="<?php echo __('削除')?>"><i class="fa-regular fa-trash-can"></i></a>
                              </td>
                           <?php  } ?>
                        </tr>
                     <?php } ?>
                  </tbody>
               </table>
            </div>   

            <?php if(($count) > Paging::TABLE_PAGING){ ?>
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
         </div>
      </div>
   <?php } ?>
   <br><br>
   <div>
      <input type="hidden" name="hid_del_stockId" id="hid_del_stockId" value = "" >
      <input type="hidden" name="hid_page_no" id="hid_page_no" value = "" > 
   </div>
</div>
<br><br>   
<?php echo $this->Form->end(); ?>
<script>
   $(document).ready(function(){
      var rno = '<?php echo $rno; ?>';   
      var i = '<?php echo $i; ?>';   
      if(rno != i){
         $("#save_btn").css({"display":"block"});
         $(".edit-mode").css({"display":"block"});
      }
   });
</script>