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

   td.date_list div.datepicker {
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
   .no_edit[readonly], select.no_edit[disabled] {
      background-color: #fff !important;
      border: none !important;
      cursor: context-menu;
      box-shadow: none !important;
      /*resize: none;*/
      /*overflow:hidden;*/
      outline: none !important;
      color: #333333 !important;
   }
   .no_edit_flag3[readonly] {
      cursor: not-allowed;
      box-shadow: none !important;
      color: #333333 !important;
   }
   /*input[type="text"].no_edit:disabled {
      background-color: #fff !important;
      border: none !important;
      cursor: context-menu;
      box-shadow: none !important;
      outline: none !important;

   }*/
   textarea.chg_edit_mode {
      padding-top: 23px;
   }
   textarea {
      resize: horizontal;
   }
   .previewCommt {
      padding-top: 23px;
      width: 150px;
   }
</style>

<script>

   $(document).ready(function(){
      $(".datepicker").datepicker({
         todayHighlight: true
      });

      $("#save_btn").css({"display":"none"});
      $(".edit_mode").css({"display":"none"});
      if($('#tbl_previewAndEdit').length > 0) {
         $('.tbl-wrapper').freezeTable({ 
            'namespace' : 'tbl-freeze-table',
            'columnNum' : 5,
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

            $('.clone-column-table-wrap .preview_check').each(function() {

               if (!($(this).is(':disabled'))) {
                  $(this).prop('checked',true);
                  $('.clone-head-table-wrap #chk_acc_mgr_confirm').prop('checked',true);
               }
            });

         }else {
            // $('#save_and_mail-btn').hide();
            $('.clone-head-table-wrap #chk_acc_mgr_confirm, .clone-column-table-wrap #chk_acc_mgr_confirm').prop('checked',false);

            $('.clone-column-table-wrap .preview_check').each(function() {

               if (!($(this).is(':disabled'))) {
                  $(this).prop('checked',false);
                  $('.clone-head-table-wrap #chk_acc_mgr_confirm').prop('checked',false);
               }

            });
            
         }
      
      });

      $('.preview_check').click(function() {
         $('.clone-head-table-wrap .preview_check').prop('checked', true);

         checkToggle();

      });

      function checkToggle() {

         var isCheck = true;
         var disable = 0;
         var rows = 0;
         $('.clone-column-table-wrap .preview_check').each(function() {

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
      $(".chg_edit_mode").prop('readonly', true);
      $("select.chg_edit_mode").attr('disabled', true);
      $(".chg_edit_mode").addClass('no_edit');
      $(".chg_edit_mode").removeClass('form-control');
      
      $("#edit_mode").click(function() {
         if($(this).is(':checked')) {
            $(".chg_edit_mode").prop('readonly', false);
            $("select.chg_edit_mode").attr('disabled', false);
            $(".chg_edit_mode").removeClass('no_edit');
            $(".chg_edit_mode").addClass('form-control');
            $(".span_one").addClass('input-group-addon');
            $(".span_two").addClass('glyphicon glyphicon-calendar');
            $("td.date_list div").addClass('input-group date datepicker');
            $("td.date_list div").attr('data-provide', 'datepicker');
            $("td.date_list div").attr('data-date-format', 'yyyy-mm-dd');
            $(".clone-column-table-wrap .acc_preview tbody tr").each(function(i, tr){
               if($(this).find(".preview_check").is(':disabled')) {
                  $(this).find(".chg_edit_mode").prop('readonly', true);
                  $(this).find(".chg_edit_mode").addClass('no_edit_flag3');
               }
            });
            $(".acc_preview tbody tr").each(function(i, tr){
               if($(this).find(".preview_check").is(':disabled')) {
                  $(this).find(".chg_edit_mode").prop('readonly', true);
                  $(this).find("select.chg_edit_mode").prop('disabled', true);
                  $(this).find(".chg_edit_mode").addClass('no_edit_flag3');
                  $(this).find("td.date_list div").attr('data-provide', '');
               }
            });
         }else {
            $(this).find(".chg_edit_mode").removeClass('no_edit_flag3');
            $(".chg_edit_mode").prop('readonly', true);
            $("select.chg_edit_mode").attr('disabled', true);
            $(".chg_edit_mode").addClass('no_edit');
            $(".chg_edit_mode").removeClass('form-control');
            $(".span_one").removeClass('input-group-addon');
            $(".span_two").removeClass('glyphicon glyphicon-calendar');
            $("td.date_list div").removeClass('input-group date datepicker');
            $("td.date_list div").attr('data-provide', '');
            $("td.date_list div").attr('data-date-format', '');
         }
      });

      /* check date format */

      $("td").on('input','.posting_date, .recorded_date, .receipt, .schedule_date, .maturity_date', function() {$(this).val('');});

      $("td").on('change', '.schedule_date', function() {
         var baseDate = new Date($("#base_date").val());
         var scheduleDate = new Date(this.value);
         scheduleDate = (scheduleDate == 'Invalid Date') ? 0 : scheduleDate;
         var diffTime = Math.abs(baseDate - scheduleDate);
         var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
         if(baseDate < scheduleDate) diffDays = diffDays * (-1);
         var row_id = parseInt( this.id.match(/\d+/g), 10 );
         $("#num_day"+row_id).text(diffDays);
      });
   });

   function searchPreview(){

      document.getElementById("error").innerHTML   = '';
      document.getElementById("success").innerHTML   = '';
      document.getElementById("messageContent").innerHTML =  '';
      var chk = true;
      var form = document.getElementById("PreviewAndEdit");
      if(chk){ 
         form.action = "<?php echo $this->webroot; ?>SapAccountPreviews/searchPreview"; 
         form.method = "GET";
         form.submit();
         return true;   
      }
      scrollText();  
   }

   // delete SAP data in table
   function deleteSAP(sap_id){
      
      document.getElementById("error").innerHTML = "";
      document.getElementById("success").innerHTML ="";
      document.getElementById("messageContent").innerHTML =  '';

      var chk = true;
      document.getElementById('hid_del_sapId').value = sap_id;

      var path = window.location.pathname;
      var page = path.split("/").pop();
      document.getElementById('hid_page_no').value = page;
      

      if(chk) {
         var sap_id = $("#hid_del_sapId").val();
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
                        url: "<?php echo $this->webroot; ?>SapAccountPreviews/Delete_Preview",
                        type: 'post',
                        data: {'sap_id':sap_id},
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
   
   function click_save_btn(){

      document.getElementById("error").innerHTML = '';
      document.getElementById("success").innerHTML = '';
      document.getElementById("messageContent").innerHTML =  '';

      var DataArray = [];     
      var checkflag = true;
      var checkedCnt = 0;var uncheckedCnt = 0;
      $(".clone-column-table-wrap .acc_preview tbody tr").each(function(i, tr){
         
         var chk_status = false;
         var row_id = $(this).find('.preview_check').val();
         var text_value = $.trim($(this).find('.previewCommt').val());
         if(text_value == 'new add') {
            
         }
         
         var skip_or_not = $(this).find('.skipError').val();
         
         if ($(this).find('.preview_check').prop("checked") == true){
            var chk_status = true;
            
            if($('#flag'+row_id).val() == 1) checkedCnt++;
            
         }else {
            uncheckedCnt++;
         }
         var acc_name = $.trim($(this).find('.account_name').val());
         var dest_code = $.trim($(this).find('.dest_code').val());
         var dest_name = $.trim($(this).find('.dest_name').val());
         var logistics_no = $.trim($('#logi_no'+row_id).val());
         var posting_date = $.trim($('#posting_date'+row_id).val());
         var recorded_date = $.trim($('#recorded_date'+row_id).val());
         var receipt = $.trim($('#receipt'+row_id).val());
         var schedule_date = $.trim($('#schedule_date'+row_id).val());
         var maturity_date = $.trim($('#maturity_date'+row_id).val());
         var line_item_text = $.trim($('#line_item_text'+row_id).val());
         var sale_repre = $.trim($('#sale_repre'+row_id).val());
         var currency = $.trim($('#currency'+row_id).val());
         
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
               myDataRows.push(row_id,text_value,chk_status, acc_name, dest_code, dest_name, logistics_no, posting_date, recorded_date, receipt, schedule_date, sale_repre, currency, maturity_date, line_item_text);
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
                     getMail('Save', noSend);

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
      var page = 'SapAccountPreviews';
      var mail = {};
      $.ajax({
         type:'post',
         url: "<?php echo $this->webroot; ?>SapImports/getMailLists",
         data: {layer_code : layer_code, page: page, function: func},
         dataType: 'json',
         success: function(data) {
            console.log(data);
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
                  $('#PreviewAndEdit').attr('action', "<?php echo $this->Html->url(array('controller'=>'SapAccountPreviews','action'=>'SaveCheckAndComment')) ?>").submit();
                 
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
                  $('#PreviewAndEdit').attr('action', "<?php echo $this->Html->url(array('controller'=>'SapAccountPreviews','action'=>'SaveCheckAndComment')) ?>");

               }
            }else {
               loadingPic();
               document.forms[0].action = "<?php echo $this->webroot; ?>SapAccountPreviews/SaveCheckAndComment";
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

   function numberFormat(num, decPt, decStr = '') {
      var fixedPt = (decPt.toString().length) - 1;
      if(num == ''){
         num = 0;
         return num.toFixed(fixedPt);
      }else{
         if(num.toString().indexOf('.') != -1) {
            num = Math.round(num * decPt) / decPt;
            var numArr = num.toString().split('.');
            var value = numArr[0];
            var value = value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
            if(numArr[1]) return value+"."+numArr[1];
            else return value+decStr;
         }else{
            var value = num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
            return value+decStr;
         } 
      }
   }

   function removeComma(value) {
      retVal = value.replace(/\.0+$/,'');//Remove zero after decimal
      retVal = retVal.replace(/,/g, "");//Remove comma from value
      return retVal;
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

            $match_LogIdNo    = 'checked';
            $not_match_LogIdNo   = '';

            $match_Currency   = 'checked';
            $not_match_Currency = '';

            if($this->Session->check('SESSION_LAYER_CODE')) {
               $choose_ba = $this->Session->read('SESSION_LAYER_CODE');
            }

            # search data
            if(!empty($search_data)){ 
               $destination = $search_data['destination'];
               $logisticIndexNo = $search_data['logisticIndexNo'];
               $postingDate = $search_data['postingDate'];
               $salesRepresentative = $search_data['salesRepresentative'];
               $currency  = $search_data['currency'];
               $choose_ba = $search_data['choose_ba'];
               $match_status_SR = $search_data['optSRCondition'];
               $match_status_Des = $search_data['optDesCondition'];
               $match_status_LogIdNo = $search_data['optLogIdNoCondition'];
               $match_status_Currency = $search_data['optCurrencyCondition'];
               
               if($match_status_SR == 1) {
                  $match_SR = 'checked';
                  $not_match_SR = '';
               } else if($match_SR == 0) {
                  $match_SR = '';
                  $not_match_SR = 'checked';
               }

               if($match_status_Des == 1) {
                  $match_Des = 'checked';
                  $not_match_Des = '';
               } else if($match_Des == 0) {
                  $match_Des = '';
                  $not_match_Des = 'checked';
               }

               if($match_status_LogIdNo == 1) {
                  $match_LogIdNo = 'checked';
                  $not_match_LogIdNo = '';
               } else if($match_LogIdNo == 0) {
                  $match_LogIdNo = '';
                  $not_match_LogIdNo = 'checked';
               }

               if($match_status_Currency == 1) {
                  $match_Currency = 'checked';
                  $not_match_Currency = '';
               } else if($match_Currency == 0) {
                  $match_Currency = '';
                  $not_match_Currency = 'checked';
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
               <label for="salesRepresentative" class="col-sm-4 col-form-label">
                  <?php echo __('営業担当者');?>
               </label>
               <div class="col-sm-8">
                  <input type="text" class="form-control" id="salesRepresentative" name="salesRepresentative" value="<?php if(!empty($search_data)){echo htmlspecialchars($salesRepresentative);} ?>" />
               </div>
            </div>
            <div class="col-sm-4">
               <div class="col-sm-8">
                  <label class="radio-inline"><input type="radio" name="optSRCondition" value="1" <?php echo $match_SR; ?>><?php echo __('一致(=)'); ?></label>
                  <label class="radio-inline"><input type="radio" name="optSRCondition" value="0" <?php echo $not_match_SR; ?>><?php echo __('除外(≠)'); ?></label>
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
                  <?php echo __('相手先');?>
               </label>
               <div class="col-sm-8">
                  <input type="text" class="form-control" id="destination" name="destination" value="<?php if(!empty($search_data)){echo htmlspecialchars($destination);} ?>"/>     
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
               <label for="logisticIndexNo" class="col-sm-4 col-form-label">
                  <?php echo __('物流Index No.');?>
               </label>
               <div class="col-sm-8">
                  <input type="text" class="form-control" id="logisticIndexNo" name="logisticIndexNo" value="<?php if(!empty($search_data)){echo htmlspecialchars($logisticIndexNo);} ?>" />
               </div>
            </div>
            <div class="col-sm-4">
               <div class="col-sm-8">
                  <label class="radio-inline"><input type="radio" name="optLogIdNoCondition" value="1" <?php echo $match_LogIdNo; ?>><?php echo __('一致(=)'); ?></label>
                  <label class="radio-inline"><input type="radio" name="optLogIdNoCondition" value="0" <?php echo $not_match_LogIdNo; ?>><?php echo __('除外(≠)'); ?></label>
               </div>
            </div>   
         </div>
         <div class="form-group row">
            <div class="col-sm-4">
               <label for="BA_code" class="col-sm-4 col-form-label">
                  <?php echo __($this->session->read('SapSelections_code'));?>
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
            <div class="col-sm-4">
               <label for="currency" class="col-sm-4 col-form-label">
                  <?php echo __('通貨');?>
               </label>
               <div class="col-sm-8">
                  <?php
                     $yenSelected = '';
                     $jpySelected = '';
                     $usdSelected = '';
                     $idrSelected = '';
                     $euroSelected = '';
                     $thbSelected = '';
                     if(!empty($search_data)) {
                        switch ($currency) {
                           case "yen":
                              $yenSelected = 'selected="selected"';
                              break;
                           case "jpy":
                              $jpySelected = 'selected="selected"';
                              break;
                           case "usd":
                              $usdSelected = 'selected="selected"';
                              break;
                            case "idr":
                              $idrSelected = 'selected="selected"';
                              break;
                           case "eur":
                              $euroSelected = 'selected="selected"';
                              break;
                           case "thb":
                              $thbSelected = 'selected="selected"';
                              break;
                           default:
                              $showCurrency = "";
                        }
                     }

                  ?>
                  <select name="currency" id="currency" class="form-control">
                     <option value="">---<?php echo __("Select Currency"); ?>---</option>
                     <option value="yen" <?php echo $yenSelected; ?>>YEN</option>
                     <option value="jpy" <?php echo $jpySelected; ?>>JPY</option>
                     <option value="usd" <?php echo $usdSelected; ?>>USD</option>
                     <option value="idr" <?php echo $idrSelected; ?>>IDR</option>
                     <option value="eur" <?php echo $euroSelected; ?>>EUR</option>
                     <option value="thb" <?php echo $thbSelected; ?>>THB</option>
                  </select>
               </div>
            </div>
            <div class="col-sm-4">
               <div class="col-sm-8">
                  <label class="radio-inline"><input type="radio" name="optCurrencyCondition" value="1" <?php echo $match_Currency; ?>><?php echo __('一致(=)'); ?></label>
                  <label class="radio-inline"><input type="radio" name="optCurrencyCondition" value="0" <?php echo $not_match_Currency; ?>><?php echo __('除外(≠)'); ?></label>
               </div>
            </div>  
         </div>
         <div class="form-group row">
            <div class="col-sm-4">
               <label for="postingDate" class="col-sm-4 col-form-label">
                  <?php echo __('転記日付');?>
               </label>
               <div class="col-sm-8">
                  <div class="input-group date datepicker" data-provide="datepicker" style="padding: 0px;" data-date-format="yyyy-mm-dd">
                     <input type="text" class="form-control" id="postingDate" name="postingDate" value="<?php if(!empty($search_data)){echo htmlspecialchars($postingDate);} ?>" />
                     <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                     </span>
                  </div>
               </div>
            </div>
            <div class="col-sm-4 text-right" style="margin-left:-15px;">
               <button type="button" id="btn_search" class="btn btn-success btn_sumisho" onclick="searchPreview();">
                  <?php echo __("検索"); ?>
               </button>
            </div>
         </div>
         <?php if ($checkButtonType['Save'] && $showSaveBtn) { ?>
         <div class="form-group row edit_mode">
            <div class="col-sm-4">
               <label for="" class="col-sm-4 col-form-label">
                  <?php echo __('編集モード');?>
               </label>
               <div class="col-sm-8">
                  <label class="switch">
                     <input type="checkbox" id="edit_mode">
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
         <input type="button" class="btn btn-success btn_sumisho" id="save_btn" onClick = "click_save_btn();" value = "<?php echo __('保存');?>"> 
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
                        <th width="4%" style = 'text-align: center' for = "confirm"><?php echo __("確認対象"); ?><br/>
                           <input type="checkbox" name="chk_acc_mgr_confirm" id="chk_acc_mgr_confirm" checked="checked">
                        </th>
                        <th width="12%" for = "Comment"><?php echo __("コメント"); ?></th>
                        <th width="7%" for = "AccountName"><?php echo __("勘定コード名"); ?></th>
                        <th width="7%" for = "DestinationCode"><?php echo __("相手先コード"); ?></th>
                        <th width="8%" for = "Destination"><?php echo __("相手先名"); ?></th>
                        <th width="8%" for = "LogisticIndexNo"><?php echo __("物流Index No."); ?></th>
                        <th width="6%" for = "PostingDate"><?php echo __("転記日付"); ?></th>
                        <th width="6%" for = "RecordedDate"><?php echo __("計上基準日"); ?></th>
                        <th width="6%" for = "ReceiptDate"><?php echo __("入出荷年月日"); ?></th>
                        <th width="7%" for = "ScheduleDate"><?php echo __("決済予定日"); ?></th>
                        <th width="6%" for = "NoOfDays"><?php echo __("滞留日数"); ?></th>
                        <th width="6%" for = "maturityDate"><?php echo __("満期年月日"); ?></th>
                        <th width="6%" for = "lineText"><?php echo __("明細テキスト"); ?></th>
                        <th width="7%" for = "SalesRepresentative"><?php echo __("営業担当者"); ?></th>
                        <th width="5%" for = "currency"><?php echo __("通貨"); ?></th>
                        <th width="6%" for = "YenAmount"><?php echo __("円貨金額"); ?></th>
                        <th width="6%" for = "ForeignCurrencymAmount"><?php echo __("外貨金額"); ?></th>
                        <th width="6%" for = "delete" style="min-width: 3rem;"></th>
                        
                     </tr>
                  </thead>
                  <tbody> 
                     <?php
                     
                     if(!empty($SapImportsInfo)) foreach($SapImportsInfo as $row){
                        
                        $rno++;
                        $accountCode      = $row['Sap']['account_code'];
                        $accountName      = $row['Sap']['account_name'];
                        $destinationCode  = $row['Sap']['destination_code'];
                        $destination      = $row['Sap']['destination_name'];
                        $logisticIndexNo  = $row['Sap']['logistic_index_no'];

                        $postingDate      = $row['Sap']['posting_date'];
                        $postingDate = ($postingDate == '' || $postingDate == '0000-00-00' || $postingDate == '0000-00-00 00:00:00' || $posting_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($postingDate));
                        
                        $recordedDate     = $row['Sap']['recorded_date'];
                        $recordedDate = ($recordedDate == '' || $recordedDate == '0000-00-00' || $recordedDate == '0000-00-00 00:00:00' || $recordedDate == '-0001-11-30')? '' : date('Y-m-d',strtotime($recordedDate));

                        $receipt_shipment_date = $row['Sap']['receipt_shipment_date'];
                        $receipt_shipment_date = ($receipt_shipment_date == '' || $receipt_shipment_date == '0000-00-00' || $receipt_shipment_date == '0000-00-00 00:00:00' || $receipt_shipment_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($receipt_shipment_date));

                        $scheduleDate  = $row['Sap']['schedule_date'];
                        $scheduleDate = ($scheduleDate == '' || $scheduleDate == '0000-00-00' || $scheduleDate == '0000-00-00 00:00:00' || $scheduleDate == '-0001-11-30')? '' : date('Y-m-d',strtotime($scheduleDate));
                        
                        $NoOfDays   = $row['Sap']['numbers_day'];
                        $maturity_date = $row['Sap']['maturity_date'];
                        $maturity_date = ($maturity_date == '' || $maturity_date == '0000-00-00' || $maturity_date == '0000-00-00 00:00:00' || $maturity_date == '-0001-11-30')? '' : date('Y-m-d',strtotime($maturity_date));
                        $line_item_text = $row['Sap']['line_item_text'];
                        $SalesRepresentative = $row['Sap']['sale_representative'];
                        $yenAmount  = number_format($row['0']['jp_amount']);
                        
                        $flag    = $row['Sap']['flag'];
                        $sapId   = $row['Sap']['id'];
                        $previewComment = h($row['Sap']['preview_comment']);
                        //new added flow

                        $foreignAmount = number_format($row['0']['foreign_amount'],2);
                        $Currency = strtoupper($row['Sap']['currency']);
                        
                        $baCode   = $row['Sap']['layer_code'];
                        
                        //if has code of flag are equal or over flag 5, disable this code
                        $disBaCode = "";
                        if(!empty($overFlag5)){
                           
                           if(in_array($baCode,$overFlag5)){
                              $disBaCode = $baCode;
                           }
                           
                        }
                        
                        //added new feedback5 (base_date - settlement plan date)
                        // Calulating the difference in timestamps 
                        $diff = strtotime($base_date) - strtotime($scheduleDate);  
                        // 1 day = 24 hours 
                        // 24 * 60 * 60 = 86400 seconds 
                        $NoOfDays = round($diff / 86400);

                        if(($flag != '1' && $flag != '2')||($baCode == $disBaCode)){ $i++; ?>
                        
                           <tr>
                              <?php if($flag == '1'){ ?>

                                 <td style="word-wrap: break-word;text-align:center;"><input type="checkbox" name="preview_check" class="preview_check" value = "0" disabled=""><input type="hidden" name="skipError" class="skipError" value="<?php echo("skip"); ?>"></td>
                              <?php }else{ ?>
                                 <td style="word-wrap: break-word;text-align:center;"><input type="checkbox" name="preview_check" class="preview_check" checked="" value = "0" disabled=""><input type="hidden" name="skipError" class="skipError" value="<?php echo("not_skip"); ?>" ></td>
                              <?php } ?>

                              <td style="word-wrap: break-word;text-align:center;"> 
                                 <textarea rows="2" cols="2" class="form-control previewCommt" readonly=""><?php if(!empty($previewComment)) echo ($previewComment); ?></textarea>
                              
                              </td>
                        <?php }else{ ?>
                           <tr>
                           <?php if($flag == '1' && !empty($previewComment)){ ?>
                              <td style="word-wrap: break-word;text-align:center;">
                                 <input type="checkbox" name="preview_check" class ="preview_check" value = "<?php echo h($sapId);?>">
                                 <input type="hidden" name="skipError" class="skipError" value="<?php echo("not_skip"); ?>" >
                              </td>
                           <?php }else{ ?>
                              <td style="word-wrap: break-word;text-align:center;">
                                 <input type="checkbox" name="preview_check" class ="preview_check" checked="checked" value = "<?php echo h($sapId);?>">
                                 <input type="hidden" name="skipError" class="skipError" value="<?php echo("not_skip"); ?>" >
                              </td>
                           <?php } ?>
                        
                           <td style="word-wrap: break-word;text-align:center;"> 
                              <textarea rows="2" cols="2" class="form-control previewCommt" name="previewCommt" id = "previewCommt"><?php if(!empty($previewComment))echo ($previewComment);?></textarea>
                              
                           </td>
                        <?php } ?>

                           <td><textarea style="word-wrap: break-word;width: 120px;" class="form-control account_name chg_edit_mode" maxlength="500"><?php echo $accountName; ?></textarea>
                              <input type="hidden" name="account_code" class="account_code" value="<?php echo $accountCode; ?>">
                           </td>
                           <td><textarea style="word-wrap: break-word;width: 100px;" class="form-control dest_code chg_edit_mode" maxlength="10"><?php echo $destinationCode; ?></textarea></td>
                           <td><textarea style="word-wrap: break-word;width: 200px;" class="form-control dest_name chg_edit_mode" maxlength="500"><?php echo $destination; ?></textarea></td>
                           <td><textarea style="word-wrap: break-word;width: 120px;" id="<?php echo 'logi_no'.$sapId; ?>" class="form-control logi_no chg_edit_mode" maxlength="20"><?php echo $logisticIndexNo; ?></textarea></td>
                           <td class='date_list'>
                              <div>
                                 <input style="text-align:left;width: 110px;" type="text" class="form-control posting_date chg_edit_mode"  id="<?php echo 'posting_date'.$sapId; ?>" value="<?php echo $postingDate; ?>">
                                 <span class="span_one">
                                    <span class="span_two"></span>
                                 </span>
                              </div>
                           </td> 
                           <td class='date_list'>
                              <div>
                                 <input style="word-wrap: break-word;width: 110px;" id="<?php echo 'recorded_date'.$sapId; ?>" class="form-control recorded_date chg_edit_mode" value="<?php echo $recordedDate; ?>">
                                 <span class="span_one">
                                    <span class="span_two"></span>
                                 </span>
                              </div>
                           </td>
                           <td class='date_list'>
                              <div>
                                 <input style="word-wrap: break-word;width: 110px;" id="<?php echo 'receipt'.$sapId; ?>" class="form-control receipt chg_edit_mode" value="<?php echo $receipt_shipment_date; ?>">
                                 <span class="span_one">
                                    <span class="span_two"></span>
                                 </span>
                              </div>
                           </td>
                           <td class='date_list'>
                              <div>
                                 <input style="word-wrap: break-word;width: 110px;" id="<?php echo 'schedule_date'.$sapId; ?>" class="form-control schedule_date chg_edit_mode" value="<?php echo $scheduleDate; ?>">
                                 <span class="span_one">
                                    <span class="span_two"></span>
                                 </span>
                              </div>
                           </td>
                           <td style="word-wrap: break-word; text-align:right; width: 120px;" id="<?php echo 'num_day'.$sapId; ?>" class="num_day"><?php echo $NoOfDays; ?></td>
                           <td class='date_list'>
                              <div>
                                 <input style="word-wrap: break-word;width: 110px;" id="<?php echo 'maturity_date'.$sapId; ?>" class="form-control maturity_date chg_edit_mode" value="<?php echo $maturity_date; ?>">
                                 <span class="span_one">
                                    <span class="span_two"></span>
                                 </span>
                              </div>
                           </td>
                           <td><textarea style="word-wrap: break-word;width: 120px;" id="<?php echo 'line_item_text'.$sapId; ?>" class="form-control line_item_text chg_edit_mode"><?php echo $line_item_text; ?></textarea></td>
                           <td><textarea style="word-wrap: break-word;width: 120px;" id="<?php echo 'sale_repre'.$sapId; ?>" class="form-control sale_repre chg_edit_mode"><?php echo $SalesRepresentative; ?></textarea></td>
                           <td>
                              <select style="word-wrap: break-word;width: 80px;" name="currency" id="<?php echo 'currency'.$sapId; ?>" class="form-control currency chg_edit_mode">
                                 <option value="">---<?php echo __("Select Currency"); ?>---</option>
                                 <option value="yen" <?php if($Currency == 'YEN') echo 'selected';else echo ''; ?>>YEN</option>
                                 <option value="jpy" <?php if($Currency == 'JPY') echo 'selected';else echo ''; ?>>JPY</option>
                                 <option value="usd" <?php if($Currency == 'USD') echo 'selected';else echo ''; ?>>USD</option>
                                 <option value="idr" <?php if($Currency == 'IDR') echo 'selected';else echo ''; ?>>IDR</option>
                                 <option value="eur" <?php if($Currency == 'EUR') echo 'selected';else echo ''; ?>>EUR</option>
                                 <option value="thb" <?php if($Currency == 'THB') echo 'selected';else echo ''; ?>>THB</option>
                              </select>
                           </td>
                           <td style="word-wrap: break-word; text-align:right;width: 120px;" id="<?php echo 'yen_amt'.$sapId; ?>" class="yen_amt"><?php echo $yenAmount; ?></td>
                           <td style="word-wrap: break-word; text-align:right;width: 120px;" id="<?php echo 'foreign_amt'.$sapId; ?>" class="foreign_amt"><?php echo $foreignAmount;  ?></td>
                           <td style="display: none;"><input type="hidden" id="<?php echo 'flag'.$sapId; ?>" class="flag" value="<?php echo $flag;  ?>"></td>
                           <?php if(($flag != '1' && $flag != '2')||($baCode == $disBaCode)){?>
                              <td style="word-wrap: break-word; text-align:center;" aria-disabled="true">
                                 <a class="disabled" style="text-decoration: none;cursor: not-allowed;opacity: 0.5;font-size: 1.5rem;" title="<?php echo __('削除')?>"><i class="fa-regular fa-trash-can"></i></a>
                              </td>
                           <?php }else{?>
                              <td style="word-wrap: break-word; text-align:center;">
                                 <a class="" style="cursor:pointer;font-size: 1.5rem;" onclick="deleteSAP(<?php echo $sapId;?>)"; title="<?php echo __('削除')?>"><i class="fa-regular fa-trash-can"></i></a>
                              </td>
                           <?php  } ?>
                        </tr>
                     <?php } ?>
                  </tbody>
               </table>
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
         </div>
      </div>
   <?php } ?>
   <br><br>
   <div>
      <input type="hidden" name="hid_del_sapId" id="hid_del_sapId" value = "" >
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
         $(".edit_mode").css({"display":"block"});
      }
   });
</script>