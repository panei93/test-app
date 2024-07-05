<style>
    .upload_file {
      width: 188px !important;
    }
    .link-list {
        display: -webkit-box;      /* OLD - iOS 6-, Safari 3.1-6 */
        display: -moz-box;         /* OLD - Firefox 19- (buggy but mostly works) */
        display: -ms-flexbox;      /* TWEENER - IE 10 */
        display: -webkit-flex;     /* NEW - Chrome */
        display: flex;             /* NEW, Spec - Opera 12.1, Firefox 20+ */
    }
    a.down-link {
        display: inline-block;
        width: 100px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #19b5fe;
        text-decoration: underline;
        text-align: left;
    }
    .btn-delete {
        color: red;
    }
    .btn-delete-ajax {
        float: right;
        color: red;
    }
    .btn-delete:hover, .btn-delete-ajax:hover {
        cursor: pointer;
    }
    .tbl-each {
        table-layout: fixed;
        min-width: 850px;
    }
    .tbl-each thead th {
        overflow-wrap: break-word;
        text-align: center;
    }
    .tbl-each tbody td {
        overflow-wrap: break-word;
    }
    .tbl-each tbody tr {
        height: 120px;
    }
    /* width large add in checklist*/
    .checklist_feedback {

        margin: 0px;
        width: 200px;

    }
    .line_full {
        padding: 0px !important;
    }
    .td_left {
        text-align: left !important;
    }
    .td_right {
        text-align: right !important;
    }
    .btn_style {
		width: 100px;
		margin: 0px;
	}
    .flex {
		justify-content: center;
		align-content: space-between;
	}
    .row {
		display: block ;
		margin-right: 0px;
	}
</style>
<script>
    $(document).ready(function() {
        /* tooltip */
        setTableHeight("checkfeedshow");//adjust table for first tab

        $("[data-toggle='tooltip']").tooltip({
            trigger : 'hover'
        });

        $('.nav-tabs a[href="#checkfeedshow"]').tab('show');

        setTableHeight("checkfeedshow");

        <?php if($tab1ShowEnable == true) { ?>
            $("#second_tab_hide").hide();
        <?php } 
        if($tab1ShowDisable == true) {?>
            //$('#checkfeedshow :input').attr('disabled','disabled');

        <?php }
        if($tab2ShowEnable == true) { ?>
            $("#second_tab_hide").show();
            $('.nav-tabs a[href="#checkfeedshide"]').tab('show');
            //$('#checkfeedshow :input').attr('disabled','disabled');
            $('.download_url').prop('disabled',false);
            $('.download_file').prop('disabled',false);
            $('.attachment_id').prop('disabled',false);
   
            setTableHeight("checkfeedshide");
        <?php }
        # if all condition is false, then hide tab 2
        if($tab1ShowDisable == false && $tab1ShowEnable == false && $tab2ShowEnable == false) {?>
            $("#second_tab_hide").hide();
        <?php } ?>

        $(document).on('click', "#first_tab", function() {
            setTableHeight("checkfeedshow");
        });

        $(document).on('click', "#second_tab_hide", function() {
            setTableHeight("checkfeedshide");
        });

        function setTableHeight(divID) {
            /* set improve status report table based on result report table [second tab] */
            var table_height = [];
            $("#"+divID).children().find('.tbl-each').each(function(index, val) {
                var height = $(this).height();
                table_height.push(height);
            });

            $("#"+divID).children().find('.tbl-improve').each(function(i,val){
                $(this).height(table_height[i]);
            });
        }

        $('.upload_file').change(function() {
            var isDuplicate = false;
            $(this).closest('.upload-div').wrap('<form class="file-upload-form"></form>');
            var found = '';
            var warn = '';
            var list = [];
            var row = $(this).closest('tr');
            var formdata = new FormData($(this).closest(".file-upload-form")[0]);
            var get_sample_id = $(this).closest('.upload-div').find('.check_sample_id').val();
            var s_id = $(this).closest('.upload-div').find('.sid').val();
            var fileToUpload = $(this).prop('files')[0].name;
            $.ajax({
                url: "<?php echo $this->webroot; ?>SampleChecklists/checkDuplicateFile",
                type: "post",
                data: {'file_name':fileToUpload,'sample_id':get_sample_id, 's_id':s_id},
                success: function(data) {
                    var data = JSON.parse(data);
                    if(data['isDuplicate'] == 'Yes') {
                        isDuplicate = true;
                    } else {
                        isDuplicate = false;
                    }
                    showPopupBox(isDuplicate, warn, formdata, row)
                },
                error: function(e) {
                    console.log(e);
                }
            });
            
            $(this).val('');
        });

        function showPopupBox(isDuplicate, warn, formdata, row) {
            if(isDuplicate == true) {
                $.confirm({
                    title: "<?php echo __('アップロード確認'); ?>",
                    icon: 'fas fa-exclamation-circle',
                    type: 'green',
                    typeAnimated: true,
                    closeIcon: true,
                    columnClass: 'medium',
                    animateFromElement: true,
                    animation: 'top',
                    draggable: false,  
                    content: errMsg(commonMsg.JSE005),
                    buttons: {
                        ok: {
                            text: "<?php echo __('はい'); ?>",
                            btnClass: 'btn-info',
                            action:function(){
                                uploadFileAjax(formdata, row, 'update');
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
                $.confirm({
                    title: "<?php echo __('アップロード確認'); ?>",
                    icon: 'fas fa-exclamation-circle',
                    type: 'green',
                    typeAnimated: true,
                    closeIcon: true,
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
                                uploadFileAjax(formdata, row, 'save');
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
            }
        }
        
        function uploadFileAjax(formdata, row, action) {
           
            document.getElementById("error").innerHTML = '';
            document.getElementById("success").innerHTML = "";
            //tooltip work for after ajax load
            $(document).ajaxComplete(function (){ 
                $("[data-toggle='tooltip']").tooltip({
                    trigger : 'hover'
                });
            })

            formdata.append('action',action);//to decide save or update

            $.ajax({
                type: "post",
                url: "<?php echo $this->webroot; ?>SampleChecklists/uploadAccountFile",
                processData: false,
                contentType: false,
                data: formdata,
                beforeSend: function() {
                    loadingPic(); 
                },
                success: function(data) {
                    console.log(data);
                    $(".upload_file").empty();
                    var data = JSON.parse(data);
                    if(data['error'] != undefined && data['error'] != '') {
                        var err = data['error'];
                        $("#error").html(err);
                        $("#error").show();
                        $("#success").hide();
                        $("html, body").animate({ scrollTop: 0 }, 'slow');             

                    }
                    if(data['file_name'] != undefined && data['file_name'] != '') {
                        var file_name = data['file_name']['name'];
                        var file_url = data['file_name']['url'];
                        var attach_id = data['file_name']['attach_id'];
                        var sample_id = data['file_name']['sample_id'];

                        var html = '';
                        html += '<div class="link-list">';
                        html += '<input type="hidden" name="download_url" class="download_url" value="'+file_url+'">';
                        html += '<input type="hidden" name="download_file" class="download_file" value="'+file_name+'">';
                        html += '<input type="hidden" name="attachment_id" class="attachment_id" value="'+attach_id+'">';
                        html += '<a href="#" class="down-link" id="tooltip-inner" data-toggle="tooltip" data-placement="top"  title="'+file_name+'">'+file_name+'</a>';

                      //here write tooltip

                      //remove $user_level==7 condition
                        <?php //if($user_level == 7) { ?>
                        html += '<div class="btn-delete-ajax"><span class="glyphicon glyphicon-remove-sign"></span></div>';
                        <?php //} ?>
                        html += '</div>';
                        if(action == 'update') html = '';
                        $('div').find('.show-upload-file-'+sample_id).append(html);
                        var success = data['file_name']['success'];
                        $("#success").html(success);
                    }
                    $("#overlay").hide();
                }
            });
        }
        /* download file */
        $(document).on('click','.down-link', function(e) {
            e.preventDefault();
            $('.download_url').prop('disabled',false);
            $('.download_file').prop('disabled',false);
            $('.attachment_id').prop('disabled',false);
            $(this).closest('.link-list').wrap('<form method="post" id="upd-form" action="<?php echo $this->webroot; ?>SampleChecklists/download_object_from_cloud"></form>');
            $(this).closest('form').submit();
        });

        $(document).on('click', '.btn-delete, .btn-delete-ajax', function(e) {
            e.preventDefault();
            $(this).closest('.link-list').wrap('<form method="post" class="del-file-form" action="<?php echo $this->webroot; ?>SampleChecklists/delete_object_from_cloud"></form>');
            var click = $(this).closest('.link-list');
            $.confirm({
                title: "<?php echo __('確認欄'); ?>",
                icon: 'fas fa-exclamation-circle',
                type: 'red',
                typeAnimated: true,
                closeIcon: true,
                columnClass: 'medium',
                animateFromElement: true,
                animation: 'top',
                draggable: false,  
                content: "<?php echo __("データを削除してよろしいですか。");?>",
                buttons: {
                    ok: {
                        text: "<?php echo __('はい'); ?>",
                        btnClass: 'btn-info',
                        action:function(){
                            click.parent('.del-file-form').submit();
                            loadingPic(); 
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
        });

        var txtareaCnt = 0;
        var disabledCnt = 0;
        $("#comm_id_row tr.comm_row_fill").each(function() {
            txtareaCnt++;
            var isComfill = $(this).find('.checklist_feedback');
            if(isComfill.is(':disabled'))  {disabledCnt++;}
        });
        if(txtareaCnt == disabledCnt) {$('#save_checklist').prop('disabled', true);}
        else {  $('#save_checklist').prop('disabled', false);}

        var txtareaCnt = 0;
        var disabledCnt = 0;
        var flag = false;
        $("#comm2_id_row tr.comm2_row_fill").each(function() {
            flag = true;
            txtareaCnt++;
            var isComfill = $(this).find('.checklist_feedback');
            if(isComfill.is(':disabled'))  {disabledCnt++;}
        });
        if(txtareaCnt == disabledCnt && flag == true) {$('#save_checklist').prop('disabled', true);}
        else {  $('#save_checklist').prop('disabled', false);}
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
                console.log(data);
            },
        });
    }
    function reduceMailSetup(data_action, tab2 = ''){
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
                $("#check_and_feedback_form").attr("method","POST");
                $("#check_and_feedback_form").attr("action", "<?php echo $this->webroot; ?>SampleChecklists/"+data_action+"CheckList"+tab2);  
                //loadingPic(); 
                return true;  

            }else{
                /* set mails if not pop up */
                $("#toEmail").val(toMails);
                $("#ccEmail").val(ccMails);
                $("#bccEmail").val(bccMails);
                document.forms[0].action = "<?php echo $this->webroot; ?>SampleChecklists/"+data_action+"CheckList"+tab2;
                document.forms[0].method = "POST";
                document.forms[0].submit();
                loadingPic(); 
                return true;  
            }
        }else {
            /*normal save*/
            document.forms[0].action = "<?php echo $this->webroot; ?>SampleChecklists/"+data_action+"CheckList"+tab2;
            document.forms[0].method = "POST";
            document.forms[0].submit();
            loadingPic(); 
            return true;  
        }
    }
    function saveCheckComment() {
        var data_action = $("#data_action_save").val();
        getMailSetup(data_action);
        var arr_complete = [];
        var errorFlag = false;
        document.getElementById("error").innerHTML = '';
        
        $("#comm_id_row tr.comm_row_fill").each(function() {

            var isComfill = $(this).find('.checklist_feedback');
        
            if(isComfill.is(':disabled') == false){

                var isComfill = $.trim($(this).find('.checklist_feedback').val());
            
                if(!checkNullOrBlank(isComfill) || isComfill == undefined) {
                    arr_complete.push(false);

                }else{
                    arr_complete.push(true);
                }
            } 
        });
        
        if(arr_complete.indexOf(false) == -1) {  // if all line is check
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
                            var inputs = document.getElementsByClassName('checklist_feedback');
                            for(var i = 0; i < inputs.length; i++) {
                                inputs[i].disabled = false;
                            }
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
        } else{
            var err_msg = errMsg(commonMsg.JSE001,['<?php echo __("改善状況")?>']);
              
            $("#success").empty();
            $("#error").empty();
            $("#error").append(err_msg);
            $("html, body").animate({ scrollTop: 0 }, 'slow');

        }//conform if close
    }
    function reviewCheckComment(){
        var data_action = $("#data_action_review").val();
        getMailSetup(data_action);
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
    }
    function approveCheckComment(){
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
    }
    function rejectCheckComment(){
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
            content: "<?php echo __("すべてのデータを拒否してもよろしいですか？") ?>" ,
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
    }
    function approve_cancelCheckComment(){
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
    }
    function saveCheckCommentSecTab(){ 
        data_action = $("#data_action_save").val();
        tab2 = 'Tab2';
        getMailSetup(data_action);
        var arr_complete = [];
        var errorFlag = true;
        document.getElementById("error").innerHTML = '';
    
        $("#comm2_id_row tr.comm2_row_fill").each(function() {
    
            var isCom2fill = $(this).find('.checklist_feedback');
      
            if(isCom2fill.is(':disabled') == false){

                var isCom2fill = $.trim($(this).find('.checklist_feedback').val());
            
                if (!checkNullOrBlank(isCom2fill) || isCom2fill == undefined) {
                    arr_complete.push(false);

                }else{
                    arr_complete.push(true);
                }
            } 
            errorFlag = false ;
        });
  
        if(arr_complete.indexOf(false) == -1) {  // if all line is check
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
                            var inputs = document.getElementsByClassName('checklist_feedback');
                            for(var i = 0; i < inputs.length; i++) {
                                inputs[i].disabled = false;
                            }
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
        }else{
          var err_msg = errMsg(commonMsg.JSE001,['<?php echo __("改善状況")?>']);
          
          $("#success").empty();
          $("#error").empty();
          $("#error").append(err_msg);
          $("html, body").animate({ scrollTop: 0 }, 'slow');

        }//conform if close
    }
    function reviewCheckCommentSecTab(){ 
        data_action = $("#data_action_review").val();
        getMailSetup(data_action);
        var tab2 = 'Tab2';
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
    }
    function approveCheckCommentSecTab(){
        data_action = $("#data_action_approve").val();
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
    }
    function rejectCheckCommentSecTab(){
        data_action = $("#data_action_reject").val();
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
            content: "<?php echo __("すべてのデータを拒否してもよろしいですか？") ?>" ,
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
    }
    function approve_cancelCheckCommentSecTab(){
        data_action = $("#data_action_approve_cancel").val();
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
</script>

<?php 
echo $this->element("autocomplete", array(
        "to_level_id" => "",
        "cc_level_id" =>"",
        "submit_form_name" => "check_and_feedback_form",
        "MailSubject" => "",
        "MailTitle" => "",
        "MailBody" => ""));
#check popup show approve and reject state 
$check_list_count = count($check_list_tab1);
$flg_reject = false;
$flg_cancel = false;
for($i=0; $i<$check_list_count ;$i++) {
    if ($checkListshowdata[$i]['ch']['flag'] == 2){    
        $flg_reject = true;
    } else if ($checkListshowdata[$i]['ch']['flag'] == 3){    
        $flg_cancel = true;   
    } 
}
               
#check Tab one deadline date and  tab two deadline date 
if (!empty($mail_deadline_date1) && !empty($mail_deadline_date2)){
        $deadline_date = $mail_deadline_date2 ;
    }else{
       $deadline_date = $mail_deadline_date1 ;
    }
?>
<!-- add pop mail choose end-->
<div id="overlay">
    <span class="loader"></span>
</div>
<?php echo $this->form->create(false,array('url'=>'',
    'type'=>'post',
    'class'=>'form-horizontal cf-form',
    'name'=>'check_and_feedback_form',
    'id'=>'check_and_feedback_form' ,                     
    'enctype' => 'multipart/form-data')); 
 ?>
<input type="hidden" name="toEmail" id="toEmail">
<input type="hidden" name="ccEmail" id="ccEmail">
<input type="hidden" name="mailSubj" id="mailSubj">
<input type="hidden" name="mailTitle" id="mailTitle">
<input type="hidden" name="mailBody" id="mailBody">
<input type="hidden" name="mailSend" id="mailSend">
<h3><?php echo __("チェックリストとフィードバック"); ?></h3>
<hr>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="success" id="success"><?php echo ($this->Session->check("Message.cbSuccess"))? $this->Flash->render("cbSuccess") : '';?></div>            
        <div class="error" id="error"><?php echo ($this->Session->check("Message.cbFail"))? $this->Flash->render("cbFail") : '';?></div>                  
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
    <ul class="nav nav-tabs">
        <li  class="active" id="first_tab">
            <a href="#checkfeedshow" data-toggle="tab"><?php echo __("1回"); ?></a>
        </li>
        <li id="second_tab_hide"><a href="#checkfeedshide" data-toggle="tab"><?php echo __("2回"); ?></a>
        </li>
    </ul> 
    <div class="form_test "><!--form_test -->   
        <div class="row"><!-- start first row tab -->
            <div class="tab-content "><!--tab-content nav bar start -->
                <div class="tab-pane active" id="checkfeedshow"><!-- tab nav start1 -->
                    <div class="col-md-6 col-xs-12"><!-- left col-md-6 -->
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?php echo __("サンプルチェック結果報告"); ?> </legend>
                            <div class="row"> <!-- period, ba row start -->
                                <div class="col-xs-12">
                                    <div class="row">
                                        <label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("対象月"); ?></label>
                                        <div class="col-md-8 col-xs-12 ">
                                            <input class="form-control" type="text" value="<?php echo $this->Session->read('SAMPLECHECK_PERIOD_DATE'); ?> " readonly="" >
                                        </div>
                                    </div><br/>
                                    <div class="row">
                                        <label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("部署"); ?></label>
                                        <div class="col-md-8 col-xs-12 ">
                                            <input class="form-control " type="text" value="<?php echo $this->Session->read('SESSION_LAYER_CODE'); ?>" readonly="">
                                        </div>
                                    </div><br/>
                                    <div class="row">
                                        <label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("部署名"); ?></label>
                                        <div class="col-md-8 col-xs-12 ">
                                            <input class="form-control " type="text" value="<?php echo $this->Session->read('SAMPLECHECK_BA_NAME'); ?>" readonly="">
                                        </div>
                                    </div> <br/>
                                    <div class="row">
                                        <label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("カテゴリー"); ?></label>
                                        <div class="col-md-8 col-xs-12 ">
                                            <input class="form-control " type="text" value="<?php echo $this->Session->read('SAMPLECHECK_CATEGORY'); ?>" readonly="">
                                        </div>
                                    </div> 
                                </div>
                            </div><!-- end period, ba row start -->  

                            <div class="row check_list_feed"><!-- Table row-->
                                <div class="col-md-12 col-xs-12">
                                    <div class="table-responsive">
                                    <?php if (isset($checkListshowdata)): ?>                   

                                    <?php
                                        $cnt = count($checkListshowdata); 
                                        for($i=0; $i<$cnt; $i++) {
                                            $point_out1       = h($checkListshowdata[$i]['tr']['point_out1']);
                                            $destination_name = h($checkListshowdata[$i]['sd']['destination_name']);
                                            $index_no         = h($checkListshowdata[$i]['sd']['index_no']);
                                            $account_item     = h($checkListshowdata[$i]['sd']['account_item']);
                                            $deadline_date    = h($checkListshowdata[$i][0]['deadline_date1']);
                                            $report_necessary = h($checkListshowdata[$i]['tr']['report_necessary1']);
                                            $check_sample_id  = h($checkListshowdata[$i]['tr']['sample_id']);
                                            $id               = h($checkListshowdata[$i]['tr']['id']);
                                            $sample_flag      = h($checkListshowdata[$i]['sd']['flag']);
                                            $account_file     = h($checkListshowdata[$i]['acc_file']);
                                            $busi_attach_file_dataentry = h($checkListshowdata[$i]['busi_attach_file_dataentry']);
                                            $check_list_flag  = h($checkListshowdata[$i]['ch']['flag']);

                                    ?>

                                        <table class="table table-bordered tbl-each">
                                            <thead>
                                                <tr>
                                                    <th class="test_line" style="width:40px;">RID</th>
                                                    <th class="test_line_no" width="170"><?php echo __("Index");?><br/>  <?php echo __("相手先");?><br/><?php echo __("勘定科目"); ?></th>
                                                    <th class="test_line" style="width:180px;" ><?php echo __("指摘事項"); ?></th>
                                                    <th class="test_line_check" style="width:50px;"><?php echo __("報告要否"); ?></th>
                                                    <th class="test_line" style="width:130px;"><?php echo __("提出期限"); ?></th>
                                                    <th class="test_line" style="width:180px;"><?php echo __("経理添付資料"); ?></th>
                                                    <th class="test_line" style="width:180px;"><?php echo __("営業添付資料"); ?><br><?php echo __("（データ入力）"); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="test_line_no" style="width:40px;text-align: right;"><?php echo $i+1 ;?></td>
                                                    <td class="td_left" width="170">
                                                      <?php
                                                        echo $index_no."<br/>";
                                                        echo $destination_name."<br/>";
                                                        echo $account_item;
                                                      ?>
                                                    </td>
                                                    <td class="td_left" style="width:180px;"><?php echo nl2br($point_out1) ;?> <input type="hidden" name="point_out1" value="<?php echo $point_out1 ;?>"></td>
                                                    <td class="test_line" style="width:100px;"> <input type="checkbox" disabled name="test_checkperiod" class="test_line" <?php if($report_necessary == '1') {echo 'checked="checked"';}?>>
                                                      <?php //if ($report_necessary == '1'): ?>

                                                        <input type="hidden" name="chk_sample_id[]" value="<?php echo $check_sample_id;?>">
                                                        <input type="hidden" name="check_result_id[]" value="<?php echo $id ;?>">


                                                        <?php //endif ?>                            

                                                    </td>
                                                    <td  class="td_left" style="width:130px;"><?php if ($deadline_date != 0) { echo $deadline_date; } else { echo '';} ?>
                                                    </td>
                                                    <td lass="test_line" style="width:180px;">
                                                      <?php 
                                                        $cnt_acc_attach_file = count($account_file);
                                                        if (isset($account_file) && $cnt_acc_attach_file>0) {
                                                            for($k=0; $k<$cnt_acc_attach_file; $k++) {
                                                               $attachment_id = h($account_file[$k]['attachment_id']);
                                                               $file_name = h($account_file[$k]['file_name']);
                                                               $url = h($account_file[$k]['url']);
                                                            ?>
                                                            <div class="link-list">
                                                                <input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
                                                                <input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
                                                                <input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
                                                                <a href="#" class="down-link" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file_name; ?></a>
                                                            </div>
                                                        <?php }} ?>                   
                                                    </td>
                                                    <td  class="test_line" width="180">
                                                        <?php 
                                                        $cnt_busi_file1 = count($busi_attach_file_dataentry);
                                                        if (isset($busi_attach_file_dataentry) && $cnt_busi_file1 > 0) {

                                                            for($f=0; $f<$cnt_busi_file1; $f++) {
                                                                $attachment_id = h($busi_attach_file_dataentry[$f]['attachment_id']);
                                                                $file_name = h($busi_attach_file_dataentry[$f]['file_name']);
                                                                $url = h($busi_attach_file_dataentry[$f]['url']);      
                                                            ?>
                                                        <div class="link-list">
                                                            <!-- <form name="file-info-form" class="file-info-form"> -->
                                                            <input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
                                                            <input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
                                                            <input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
                                                            <a href="#" class="down-link" data-toggle="tooltip" download title="<?php echo $file_name; ?>"><?php echo $file_name; ?> </a>
                                                            <!-- remove user_level==7 condition -->
                                                            <?php if( $sample_flag != 10 && $tab1ShowEnable == true && ($check_list_flag == 1 || empty($check_list_flag)) ) { ?>
                                                                <div class="btn-delete"><span class="glyphicon glyphicon-remove-sign"></span></div>
                                                            <?php } ?>
                                                            
                                                        </div>
                                                        <?php }  } ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                    <?php endif ?>               
                                    </div>
                                </div>
                            </div><!-- Table end row-->
                        <!-- -----rifht column table -->
                        </fieldset><br/><br/>
                    </div> <!--  left col-md-6  end-->
                    <div class="col-md-6 col-xs-12"><!-- right col-md-6  start-->
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?php echo __("改善状況報告"); ?> </legend>
                            <div style="margin-bottom: 90px;"></div>
                            <div class="row check_list_tabbut" ><!-- butttom group header start -->
                                <?php if(!empty($check_list_tab1)) {     
                                    $check_list_count = count($check_list_tab1);

                                    $review_flg = false;
                                    $approve_flg = false;
                                    $approve_cancel_flg = false;

                                    for($i=0; $i<$check_list_count ;$i++) {
                                        if ($checkListshowdata[$i]['ch']['flag'] == 1){

                                        $review_flg = true;
                                        } else if ($checkListshowdata[$i]['ch']['flag'] == 2){

                                            $approve_flg = true;
                                        } else if ($checkListshowdata[$i]['ch']['flag'] == 3){

                                            $approve_cancel_flg = true;     
                                        } 
                                    }
                                }
                                ?>
                                <!-- khin -->
                                <div class="col-md-12">
                                    <?php 
                                        foreach($buttons as $bname => $cstatus){
                                            if($cstatus) {
                                                $action_function = str_replace(' ', '', $bname);
                                                if($bname == 'save')    $btn_name  = 'Save';
                                                if($bname == 'reject')  $btn_name  = 'Revert';
                                                if($bname == 'review')  $btn_name  = ' Check ';
                                                if($bname == 'approve') $btn_name  = 'Approve';
                                                if($bname == 'approve_cancel') {
                                                    $btn_name = 'Approve Cancel'; 
                                                    $btn_style = '';
                                                }else {
                                                    $btn_style = 'btn_style';
                                                }
                                    ?>
                                    <div></div>
                                    <div>
                                        <input type="hidden" name = "data_action" id = "data_action_<?php echo strtolower($action_function); ?>" value="<?php echo $action_function; ?>">
                                        <input type="button"  class="btn btn-success pull-right <?php echo $btn_style; ?>" id="<?php echo strtolower($action_function) ?>_checklist" onClick = "<?php echo strtolower($action_function); ?>CheckComment();" value = "<?php echo __($btn_name);?>"><br><br>    
                                    </div>
                                    <?php }}?> 
                                </div>
                                
                            </div>
                            <div class="col-md-12 col-xs-12"><!-- test resulr data table -->
                                <?php
                                $cnt = count($checkListshowdata);
                                $check_cm = count($checkCommentfist);
                                for($i=0;$i <$cnt;$i++ ) {
                                    $check_commenttext = h($checkListshowdata[$i]['ch']['improvement_situation1']);
                                    $report_necessary = h($checkListshowdata[$i]['tr']['report_necessary1']);
                                    $sample_flag      = h($checkListshowdata[$i]['sd']['flag']);
                                    $chk_list_flag    = h($checkListshowdata[$i]['ch']['flag']);
                                    $sample_id_chk    = h($checkListshowdata[$i]['tr']['sample_id']);
                                    $busi_attach_file_checklist = h($checkListshowdata[$i]['busi_attach_file_checklist']);     
                                ?>
                                <table class="table table-bordered tbl-improve" id="comm_id_row">
                                    <thead>
                                        <tr>
                                            <th><?php echo __("改善状況"); ?></th>
                                            <th><?php echo __("営業添付資料"); ?><br/><?php echo __("（チェックリスト）"); ?></th>

                                            <!-- edited by sandi font size add in comment -->
                                            <th style="font-size: 13px;"><?php echo __("営業添付資料"); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="comm_row_fill">         
                                            <th style='background-color:white;' class="line_full">
                                                <div class="imp_comment"><textarea class="checklist_feedback" id="checknew_commentfirst" name="checknew_comment[]"<?php if($report_necessary == '0' || $sample_flag == 10 || $check_list_flag > 1){echo 'disabled="disabled"';}?>><?php echo  $check_commenttext;?></textarea>
                                                </div>
                                            </th>
                                            <td>
                                                <!-- 営業添付資料（チェックリスト）file -->
                                                <?php 
                                                    $cnt_busi_file2 = count($busi_attach_file_checklist);
                                                if (isset($busi_attach_file_checklist) && $cnt_busi_file2 > 0) {

                                                for($f=0; $f<$cnt_busi_file2; $f++) {
                                                    $attachment_id = h($busi_attach_file_checklist[$f]['attachment_id']);
                                                    $file_name = h($busi_attach_file_checklist[$f]['file_name']);
                                                    $url = h($busi_attach_file_checklist[$f]['url']);      
                                                ?>
                                                    <div class="link-list">
                                                        <!-- <form name="file-info-form" class="file-info-form"> -->
                                                        <input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
                                                        <input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
                                                        <input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
                                                        <a href="#" class="down-link" data-toggle="tooltip" download title="<?php echo $file_name; ?>"><?php echo $file_name; ?> </a>
                                                        <!-- remove $user_level==7 condition -->
                                                        <?php if( $sample_flag != 10 && $tab1ShowEnable == true && ($check_list_flag == 1 || empty($check_list_flag)) ) { ?>
                                                            <div class="btn-delete"><span class="glyphicon glyphicon-remove-sign"></span></div>
                                                        <?php } ?>
                                                        <!-- </form> -->
                                                    </div>
                                                <?php }  } ?>
                                                <div class="show-upload-file-<?php echo $sample_id_chk;?>"></div>
                                                <!-- 営業添付資料（チェックリスト）file end-->
                                            </td>
                                            <td style="text-align: center;">
                                                <!-- remove user_level==7 condition -->
                                                <?php   if($sample_flag != 10 && ($chk_list_flag == 1 || empty($chk_list_flag)) && $tab1ShowEnable == true) {?>
                                                <div class="upload-div">
                                                    <input type="hidden" name="check_sample_id" class="check_sample_id" value="<?php echo $sample_id_chk;?>">
                                                    <input type="hidden" name="sid" class="sid" value="<?php echo $i+1; ?>">
                                                    <label id="btn_browse">Upload File
                                                        <input type="file" class="upload_file"  name="data[File][upload_file][]">
                                                    </label>
                                                </div>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table> 
                                <?php } ?>
                            </div><!-- test resulr data ta ble end -->
                        </fieldset><br/><br/>
                    </div>
                    <!---- right col-md-6  end-----> 
                </div><!-------------------------- tab nav end1---------------------------------------------- -->
                <div class="tab-pane" id="checkfeedshide"><!-- tab nav2 second hide start -->
                    <div class="row"><!-- tab second row  start -->
                        <div class="col-md-6 col-xs-12"><!-- left col-md-6 -->
                            <fieldset class="scheduler-border">
                                <legend class="scheduler-border"><?php echo __("サンプルチェック結果報告"); ?> </legend>
                                <div class="row"> <!-- period, ba row start -->
                                    <div class="col-xs-12">
                                        <div class="row">
                                            <label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("対象月"); ?></label>
                                            <div class="col-md-8 col-xs-12 ">
                                                <input class="form-control" type="text" value="<?php echo $this->Session->read('SAMPLECHECK_PERIOD_DATE'); ?> " readonly="" >
                                            </div>
                                        </div><br/>
                                        <div class="row">
                                            <label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("部署"); ?></label>
                                            <div class="col-md-8 col-xs-12 ">
                                                <input class="form-control " type="text" value="<?php echo $this->Session->read('SESSION_LAYER_CODE'); ?>" readonly="">
                                            </div>
                                        </div><br/>
                                        <div class="row">
                                            <label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("部署名"); ?></label>
                                            <div class="col-md-8 col-xs-12 ">
                                                <input class="form-control " type="text" value="<?php echo $this->Session->read('SAMPLECHECK_BA_NAME'); ?>" readonly="">
                                            </div>
                                        </div> <br/>
                                        <div class="row">
                                            <label class="col-sm-12 col-md-4 col-xs-12"><?php echo __("カテゴリー"); ?></label>
                                            <div class="col-md-8 col-xs-12 ">
                                                <input class="form-control " type="text" value="<?php echo $this->Session->read('SAMPLECHECK_CATEGORY'); ?>" readonly="">
                                            </div>
                                        </div> 
                                    </div>
                                </div><!-- end period, ba row start -->       
                                <div class="row check_list_feed"><!-- Table row-->
                                    <div class="col-md-12 col-xs-12">
                                        <div class="table-responsive">
                                            <!-- show from test result table -->
                                            <?php if (isset($SecondCheckListshow)): ?>                   

                                            <?php

                                            $ch_secondtab = count($SecondCheckListshow);
                                            for($i=0; $i<$ch_secondtab; $i++) {
                                                $secondoint_out2        = h($SecondCheckListshow[$i]['tr']['point_out2']);
                                                $improvement_2          = h($SecondCheckListshow[$i]['ch']['improvement_situation2']);
                                                $seconddestination_name = h($SecondCheckListshow[$i]['sd']['destination_name']);
                                                $second_index_no        = h($SecondCheckListshow[$i]['sd']['index_no']);
                                                $second_sample_flag     = h($SecondCheckListshow[$i]['sd']['flag']);
                                                $second_acc_item        = h($SecondCheckListshow[$i]['sd']['account_item']);
                                                $seconddeadline_date    = h($SecondCheckListshow[$i][0]['deadline_date2']);
                                                $secondreport_necessary = h($SecondCheckListshow[$i]['tr']['report_necessary2']);
                                                $secondsample_id        = h($SecondCheckListshow[$i]['tr']['sample_id']);
                                                $secondresult_id        = h($SecondCheckListshow[$i]['tr']['id']);
                                                $account_file           = h($SecondCheckListshow[$i]['acc_file']);
                                                $business_file_dataentry = h($SecondCheckListshow[$i]['busi_attach_file_dataentry']);
                                                $business_file_checklist = h($SecondCheckListshow[$i]['busi_attach_file_checklist']);
                                                $check_list_flag       = h($SecondCheckListshow[$i]['ch']['flag']);
                                            ?> 
                                            <table class="table table-bordered tbl-each ">
                                            <thead>
                                                <tr>
                                                    <th class="test_line" style="width:40px;">RID</th>
                                                    <th class="test_line_no" width="170"><?php echo __("Index");?><br/><?php echo __("相手先");?><br/><?php echo __("勘定科目"); ?></th>
                                                    <th class="test_line" style="width:180px;" ><?php echo __("指摘事項"); ?></th>
                                                    <th class="test_line_check" style="width:50px;"><?php echo __("報告要否"); ?></th>
                                                    <th class="test_line" style="width:130px;"><?php echo __("提出期限"); ?></th>
                                                    <th class="test_line" style="width:180px;"><?php echo __("経理添付資料"); ?></th>
                                                    <th class="test_line" style="width:180px;"><?php echo __("営業添付資料"); ?><br/><?php echo __("（データ入力）"); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="test_line_no" width="40px" style="text-align: right;"><?php echo $i+1 ;?></td>
                                                    <td class="td_left" width="170px">
                                                        <?php
                                                          echo $second_index_no."<br/>";
                                                          echo $seconddestination_name."<br/>";
                                                          echo $second_acc_item;
                                                        ?>
                                                    </td>
                                                    <td class="td_left" width="180px"><?php echo nl2br($secondoint_out2) ;?> <input type="hidden" name="check_sample_id" value=""></td>
                                                
                                                    <td class="test_line" width="50px"> <input type="checkbox" disabled name="test_checkperiod" class="test_line" <?php if($secondreport_necessary == '1') {echo 'checked="checked"';}?>>
                                                  
                                                        <input type="hidden" name="ch_two_sampleid[]" value="<?php echo $secondsample_id ;?>">
                                                        <input type="hidden" name="ch_two_resultid[]" value="<?php echo $secondresult_id ;?>">
                                                                      
                                                        <!-- show from test result table end -->
                                                    </td>
                                                    <td  class="td_left" width="130px"><?php if ($seconddeadline_date != 0) { echo $seconddeadline_date; } else { echo ''; } ?></td>
                                                    <td lass="test_line" width="180px"><?php 
                                                        $cnt_acc_attach_file = count($account_file);
                                                        if (isset($account_file) && $cnt_acc_attach_file > 0) {
                                                        for($q=0; $q<$cnt_acc_attach_file; $q++) {
                                                            $attachment_id = h($account_file[$q]['attachment_id']);
                                                            $file_name = h($account_file[$q]['file_name']);
                                                            $url = h($account_file[$q]['url']);
                                                        ?>
                                                        <div class="link-list">
                                                            <!-- <form name="acc-file-info-form" class="acc-file-info-form"> -->
                                                            <input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
                                                            <input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
                                                            <input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
                                                            <a href="#" class="down-link" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file_name; ?></a>
                                                            <!-- </form> -->
                                                        </div>
                                                        <?php }} ?> 
                                                    </td>
                                                    <td  class="test_line" width="180px"><?php 
                                                        $cnt_busi_file1 = count($business_file_dataentry);
                                                        if(isset($business_file_dataentry) && $cnt_busi_file1>0) {

                                                        for($f=0; $f<$cnt_busi_file1; $f++) {
                                                            $attachment_id = h($business_file_dataentry[$f]['attachment_id']);
                                                            $file_name = h($business_file_dataentry[$f]['file_name']);
                                                            $url = h($business_file_dataentry[$f]['url']);          
                                                        ?>
                                                        <div class="link-list">
                                                            <!-- <form name="busi-file-info-form" class="busi-file-info-form"> -->
                                                            <input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
                                                            <input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
                                                            <input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
                                                            <a href="#" class="down-link" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file_name; ?></a>
                                                            <!-- remove $user_level==7 condition -->
                                                            <?php if( $second_sample_flag == 7 && ($check_list_flag == 4 || $check_list_flag == 1 || empty($check_list_flag))  ) { ?>
                                                                <div class="btn-delete"><span class="glyphicon glyphicon-remove-sign"></span></div>
                                                              <?php } ?>
                                                            <!-- </form> -->
                                                        </div>
                                                        <?php }} ?>
                                                    </td>                    
                                                </tr>
                                            </tbody>
                                            </table>
                                            <?php } ?>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                    <!-- old buttom save -->
                                </div><!-- Table end row-->
                                <!-- -----rifht column table -->
                            </fieldset><br/><br/>

                        </div> <!--  left col-md-6  end-->
                        <!-- -----------------------------------6column-------------------------- -->
                        <div class="col-md-6 col-xs-12"><!-- right col-md-6  start-->
                            <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?php echo __("改善状況報告"); ?> </legend>
                            <div style="margin-bottom: 90px;"></div>
                            <div class="row check_list_tabbut"><!-- butttom group header start -->
                                <?php      
                                $check_list_count = count($check_list_tab1);

                                $review_flg = false;
                                $approve_flg = false;
                                $approve_cancel_flg = false;

                                for($i=0; $i<$check_list_count ;$i++) {
                                    if ($checkListshowdata[$i]['ch']['flag'] == 1 ){

                                        $review_flg = true;
                                    } else if ($checkListshowdata[$i]['ch']['flag'] == 2 ){

                                        $approve_flg = true;  
                                    } else if ($checkListshowdata[$i]['ch']['flag'] == 3 ){

                                        $approve_cancel_flg = true;  
                                    } 
                                }?>
                                <!-- khin -->
                                <div class="col-md-12">
                                    <?php
                                        $app = false;
                                        foreach($sec_buttons  as $bname => $cstatus){
                                            if ($cstatus) {   
                                                $action_function = str_replace(' ', '', $bname);
                                                if($bname == 'save')    $btn_name  = 'Save';
                                                if($bname == 'reject')  $btn_name  = 'Revert';
                                                if($bname == 'review')  $btn_name  = ' Check ';
                                                if($bname == 'approve') {
                                                    $app = true;
                                                    $btn_name  = 'Approve';
                                                }
                                                if($bname == 'approve_cancel') {
                                                    $app = true;
                                                    $btn_name = 'Approve Cancel'; 
                                                    $btn_style = '';
                                                }else {
                                                    $btn_style = 'btn_style';
                                                }
                                    ?>
                                        <div>
                                            <input type="hidden" name = "data_action" id = "data_action_<?php echo strtolower($action_function); ?>" value="<?php echo $action_function; ?>">
                                            <input type="button"  class="btn btn-success btn pull-right <?php echo $btn_style; ?>" id="<?php echo strtolower($action_function) ?>_checklist" onClick = "<?php echo strtolower($action_function); ?>CheckCommentSecTab();" value = "<?php echo __($btn_name);?>"><br><br>
                                        </div>
                                        <?php } }?> 
                                </div>
         
                            </div>
                        <!-- </div><br/> -->
                        <div class="col-md-12 col-xs-12"><!-- test resulr data table -->
                        <?php $ch_com_second = count($SecondCheckListshow);
 
                            for($i=0; $i<$ch_com_second; $i++){       
                            $secondreport_necessary = h($SecondCheckListshow[$i]['tr']['report_necessary2']); 
                            $secondsample_id     = h($SecondCheckListshow[$i]['tr']['sample_id']);
                            $improvement_2       = h($SecondCheckListshow[$i]['ch']['improvement_situation2']);
                            $second_sample_flag = h($SecondCheckListshow[$i]['sd']['flag']); 
                            $check_list_flag   = h($SecondCheckListshow[$i]['ch']['flag']);
                            $business_file_checklist = h($SecondCheckListshow[$i]['busi_attach_file_checklist']);

                        ?> 
                            <table class="table table-bordered tbl-improve" id="comm2_id_row">
                                <thead>
                                    <tr>
                                        <th><?php echo __("改善状況"); ?></th>
                                        <th><?php echo __("営業添付資料"); ?><br/><?php echo __("（チェックリスト）"); ?></th>
                                        <th><?php echo __("営業添付資料"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="comm2_row_fill">
                                        <th style ='background-color:white;'class="line_full">
                                            <div class="imp2_comment">
                                                <!-- <textarea class="checklist_feedback" id ="checknew_two" name="checknew_commenttwo[]"<?php if($secondreport_necessary == '0' || $second_sample_flag == 10 || $check_list_flag > 1 ){echo 'disabled="disabled"';}?>><?php echo $improvement_2;?></textarea> -->
                                                <textarea class="checklist_feedback" id ="checknew_two" name="checknew_commenttwo[]"<?php if($secondreport_necessary == '0' || $second_sample_flag == 10 || $btn_name == 'Approve' || $btn_name == 'Approve Cancel' || $app == true){echo 'disabled="disabled"';}?>><?php echo $improvement_2;?></textarea>
                                            </div>
                                        </th>
                                        <td>
                                            <!-- 営業添付資料（チェックリスト）file -->
                                            <?php 
                                                $cnt_busi_file2 = count($business_file_checklist);
                                                if(isset($business_file_checklist) && $cnt_busi_file2>0) {

                                                    for($f=0; $f<$cnt_busi_file2; $f++) {
                                                        $attachment_id = h($business_file_checklist[$f]['attachment_id']);
                                                        $file_name = h($business_file_checklist[$f]['file_name']);
                                                        $url = h($business_file_checklist[$f]['url']);
                                                     
                                                    ?>
                                                <div class="link-list">
                                                    <!-- <form name="busi-file-info-form" class="busi-file-info-form"> -->
                                                    <input type="hidden" name="download_url" class="download_url" value="<?php  echo $url; ?>">
                                                    <input type="hidden" name="download_file" class="download_file" value="<?php echo $file_name; ?>">
                                                    <input type="hidden" name="attachment_id" class="attachment_id" value="<?php echo $attachment_id; ?>">
                                                    <a href="#" class="down-link" data-toggle="tooltip" title="<?php echo $file_name; ?>"><?php echo $file_name; ?></a>
                                                        <!-- remove $user_level==7 condition -->
                                                        <?php if( $second_sample_flag == 7 && ($check_list_flag == 4 || $check_list_flag == 1 || empty($check_list_flag))  ) { ?>
                                                            <div class="btn-delete"><span class="glyphicon glyphicon-remove-sign"></span></div>
                                                        <?php } ?>
                                                    <!-- </form> -->
                                                </div>
                                            <?php }} ?>
                                            <div class="show-upload-file-<?php echo $secondsample_id;?>"></div>
                                            <!-- 営業添付資料（チェックリスト）file end-->
                                        </td>
                                        <td style="text-align: center;"> 
                                            <!-- remove $user_level==7 condition -->
                                            <?php   if($second_sample_flag != 10 && ($check_list_flag == 4 || $check_list_flag == 1) && $secondreport_necessary == 1 && $second_sample_flag == 7) {?>
                                            <div class="upload-div">
                                                <input type="hidden" name="check_sample_id" class="check_sample_id" value="<?php echo $secondsample_id;?>">
                                                <input type="hidden" name="sid" class="sid" value="<?php echo $i+1; ?>">
                                                <label id="btn_browse">Upload File
                                                <input type="file" class="upload_file"  name="data[File][upload_file][]">
                                                </label>
                                            </div>
                                            <?php } ?>
                                            
                                        </td>

                                    </tr>
                                    <!-- show from checklist table end -->
                                </tbody>
                            </table>
                            <?php } ?>
                            <!-- remove end condition-->
                        </div><!-- test resulr data table end -->
                        <div class="row"><!-- buttom save -->
                            <div class="col-md-12 "><!-- buttom save -->

                                <?php 
                                if(!empty($SecondCheckListshow)) {
                                $check_list_flag = $SecondCheckListshow[0]['ch']['flag'];
                                $sd_flag_7 = false;
                                $cnt = count($SecondCheckListshow);
                                for($i=0; $i<$cnt; $i++) {
                                    $sd_flag = $SecondCheckListshow[$i]['sd']['flag'];
                                    if($sd_flag == 7) {
                                            $sd_flag_7 = true;
                                        }
                                    }
                                    //remove $user_level==7 condition
                                    if( ($check_list_flag == 4 || $check_list_flag == 1) && $isAllFinishSecTab == false && $sd_flag_7 == true) { 
                                ?>

                                <input style="display: none;" type="button"  class="btn btn-success btn pull-right" id="save_checklisttwo" onClick = "saveCheckCommentTab2();" value = "<?php echo __('保存');?>">
                                <?php } }?>

                            </div><!-- buttom save -->
                        </div><!-- buttom save end -->
                        </div><!---- right col-md-6  end-----> 
                    </div><!-- tab second row  end -->
                </div><!-- tab nav2 second hide end----------------------------------------------------------- -->

            </div><!-- end row tab --> 

        </div>  <!--start first row tab  -->
        <?php echo $this->Form->end();?>
    </div><!--  col-12 end -->
</div><!--  row end -->



