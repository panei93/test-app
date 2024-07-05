<style type="text/css">
    .form-horizontal .control-label {
        text-align: left !important;
    }
    .stock_excelremove {
        cursor: pointer;
        /* margin-top: 0.3rem !important; */
        margin-left: -0.5rem;
        padding-left: 0 !important;
    }
    .temp-download {
        line-height: 2rem;
        padding-left: 3rem;
    }
    .upd-file-name {
        padding-left: 1rem;
        display: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<script type="text/javascript">
$(document).ready(function() {
    $('.datepicker').datepicker({
        autoclose: true,
    });
    $(".stock_excelremove").hide();
    var tmp;
    $("#uploadfile").change(function() {
        if ($(this).val() != '') {
            var file_name = $(this).prop('files')[0].name;
            $(".stock_excelremove").show();
            $('.upd-file-name').show();
            $("#upd-file-name").empty();
            $("#upd-file-name").html(file_name);
            $("#upd-file-name").hover(function() {
                $(this).attr('title', file_name);
            });
            tmp = $(this).prop('files');
        }else{
            $(this).prop('files', tmp);
           /* $("#uploadfile").val('');
            $(".stock_excelremove").hide();
            $('.upd-file-name').hide();
            $("#upd-file-name").empty();*/
        }
    });

    $("#stock_excelremove").on('click', function() {
         $("#uploadfile").val('');
         $(".stock_excelremove").hide();
         $('.upd-file-name').hide();
         $("#upd-file-name").empty();
    });


    document.getElementById('load').style.visibility = "hidden";
    document.getElementById('contents').style.visibility = "hidden";

    var SkipCheckBAcode = "<?php if(!empty($SkipCheckBAcode)){echo $SkipCheckBAcode;}else{echo '';}  ?>";
    var SkipAccSlicLine = "<?php if(!empty($SkipAccSlicLine)){echo $SkipAccSlicLine;}else{echo '';}  ?>";

    /** Please Register BA Code **/
    if (SkipCheckBAcode == "SkipCheckBAcode") {

        $.ajax({

            url: "<?php echo $this->webroot ?>StockImports/inform_SkipCheckBAcode",
            type: 'post',
            dataType: 'json',
            success: function(data) {

                var str = data.toString();
                var output = str.split(',').join(" , ");

                $.confirm({
                    title: '<?php echo __("部署を登録してください"); ?>',
                    icon: 'fas fa-exclamation-circle',
                    type: 'green',
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
                            action: function() {

                                if (SkipAccSlicLine == "SkipAccSlicLine") {

                                    $.ajax({

                                        url: "<?php echo $this->webroot ?>StockImports/inform_SkipSlicLine",
                                        type: 'post',
                                        dataType: 'json',
                                        success: function(data) {

                                            var str = data.toString();
                                            var output = str.split(',')
                                                .join("<br>");

                                            $.confirm({
                                                title: '<?php echo __("重複スキップ行"); ?>',
                                                icon: 'fas fa-exclamation-circle',
                                                type: 'green',
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

    } else if (SkipAccSlicLine == "SkipAccSlicLine") {

        $.ajax({

            url: "<?php echo $this->webroot ?>StockImports/inform_SkipSlicLine",
            type: 'post',
            dataType: 'json',
            success: function(data) {

                if ($.trim(data)) {
                    var str = data.toString();
                    var output = str.split(',').join("<br>");


                    $.confirm({
                        title: '<?php echo __("重複スキップ行"); ?>',
                        icon: 'fas fa-exclamation-circle',
                        type: 'green',
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

                            }
                        },
                        theme: 'material',
                        animation: 'rotateYR',
                        closeAnimation: 'rotateXR'
                    });
                } else {

                }

            },
            error: function(e) {
                console.log(e);
            }

        });

    }

});

/*  
    * show hide loading overlay
    *@Zeyar Min  
    */
function loadingPic() {
    $("#overlay").show();
    $('.jconfirm').hide();
}

function saveFile() {

    document.getElementById("success").innerHTML = '';
    document.getElementById("error").innerHTML = '';
    document.getElementById("messageContent").innerHTML = '';
    document.getElementById('contents').style.visibility = "visible";
    document.getElementById('load').style.visibility = "visible";

    var base_date = document.getElementById("refer_date").value;
    var errorFlag = true;
    var excelFile = document.getElementById('uploadfile').files.length;

    if (excelFile != '1') {

        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE024)));
        document.getElementById("error").appendChild(a);
        errorFlag = false;
        document.getElementById('contents').style.visibility = "hidden";
        document.getElementById('load').style.visibility = "hidden";

    }

    if (!checkNullOrBlank(base_date)) {

        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("基準年月日"); ?>'])));
        document.getElementById("error").appendChild(a);
        errorFlag = false;
        document.getElementById('contents').style.visibility = "hidden";
        document.getElementById('load').style.visibility = "hidden";
    }

    if (errorFlag) {

        getMail('Save');

    }
}

function getMail(func) {

    var layer_code = $("#layer_code").val();
    var page = 'StockImports';
    var data = [];

    $(".subject").text('');
    $(".body").html('');
    $("#mailSubj").val('');
    $("#mailBody").val('');

    $.ajax({
        type: 'post',
        url: "<?php echo $this->webroot; ?>StockImports/getMailLists",
        data: {
            layer_code: layer_code,
            page: page,
            function: func
        },
        dataType: 'json',
        beforeSend: function(){
            loadingPic();
        },
        success: function(data) {
            $('#data_search_form').attr('method', 'post');
            $("#mailSubj").val(data.subject);
            $("#mailBody").val(data.body);
            var mailSend = (data.mailSend == '') ? '0' : data.mailSend;
            $("#mailSend").val(mailSend);
            
            if (mailSend == 1) {
                if (data.mailType == 1) {

                    var To, CC, BCC;
                    if(data.to != undefined){
                        $.each(data.to, function(key, value) {
                            To = (To) ? To + ',' + value : value;

                        });
                    }
                    if(data.cc != undefined){
                        $.each(data.cc, function(key, value) {
                            CC = (CC) ? CC + ',' + value : value;

                        });
                    }
                    
                    $('#toEmail').val(To);
                    $('#ccEmail').val(CC);
                    $('#bccEmail').val(CC);
                    document.forms[0].action = "<?php echo $this->webroot; ?>StockImports/SaveExcelFile";
                    document.forms[0].method = "POST";
                    document.forms[0].submit();
                } else {
                    $('#overlay').hide();
                    $("#myPOPModal").addClass("in");
                    $("#myPOPModal").css({
                        "display": "block",
                        "padding-right": "17px"
                    });

                    if (data.to != undefined) {
                        $('.autoCplTo').show();
                        level_id = Object.keys(data.to);
                    }
                    if (data.cc != undefined) {
                        $('.autoCplCc').show();
                        cc_level_id = Object.keys(data.cc);
                    }
                    if (data.bcc != undefined) {
                        $('.autoCplBcc').show();
                        bcc_level_id = Object.keys(data.bcc);
                    }

                    $(".subject").text(data.subject);
                    $(".body").html(data.body);

                    
                    $('#StockImports').attr('action',
                    "<?php echo $this->webroot; ?>StockImports/SaveExcelFile");
                }
            } else {
                
                document.forms[0].action = "<?php echo $this->webroot; ?>StockImports/SaveExcelFile";
                document.forms[0].method = "POST";
                document.forms[0].submit();

            }
            // console.log("<?php echo $this->Session->check('Message.success') ?>");
            // let successMessage = "<?php echo $this->Session->check('Message.success') ?>";
            // let excelErrorMsg = "<?php $this->Session->check('Message.excelError') ?>";
            // let noSlipStateMsg = "<?php $this->Session->check('Message.noSlipState') ?>"
            // if( succMsg != '' || excelErrorMsg != '' || noSlipStateMsg != '') {
            //     $('#overlay').hide();
            // }
            return true;
        },
        error: function(e) {
            console.log('Something wrong! Please refresh the page.');
        }
    });
}
</script>

<body>
    <div id="load"></div>
    <div id="contents"></div>
</body>
<?php
    echo $this->element('autocomplete', array(
                        "to_level_id" => "",
                        "cc_level_id" => "",
                        "bcc_level_id" => "",
                        "submit_form_name" => "StockImports",
                        "MailSubject" => "",
                        "MailTitle"   => "",
                        "MailBody"    =>""
                     ));
?>

<?php echo $this->Form->create(false,array('url'=>'',
                                          'type'=>'post',
                                          'id' => 'StockImports',
                                          'class'=>'form-horizontal',                      
                                          'enctype' => 'multipart/form-data')); ?>

<input type="hidden" name="toEmail" id="toEmail" value="">
<input type="hidden" name="ccEmail" id="ccEmail" value="">
<input type="hidden" name="bccEmail" id="bccEmail" value="">
<input type="hidden" name="mailSubj" id="mailSubj">
<input type="hidden" name="mailTitle" id="mailTitle">
<input type="hidden" name="mailBody" id="mailBody">
<input type="hidden" name="mailSend" id="mailSend">

<div id="overlay">
    <span class="loader"></span>
</div>
<div class="row container register_container">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <h3 class=""><?php echo __('滞留在庫データインポート');?></h3>
        <hr>
        <div class="errorSuccess" id="messageContent">
            <?php if($this->Session->check('Message.noSlipState')): ?>
            <div class="error" id="sess_error">
                <?php echo $this->Flash->render("noSlipState"); ?>
            </div>
            <?php endif; ?>
            <?php if($this->Session->check('Message.success')): ?>
            <div class="success" id="sess_error">
                <?php echo $this->Flash->render("success"); ?>
            </div>
            <?php endif; ?>

            <?php if($this->Session->check('Message.excelError')): ?>
            <div class="error" id="sess_error">
                <?php echo $this->Flash->render("excelError"); ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="errorSuccess">
            <div class="success" id="success"><?php echo $successMsg;?></div>
            <div class="error" id="error"><?php echo $errorMsg;?></div>
        </div>
        <div class="row line">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4 control-label"><?php echo __("部署"); ?></label>
                    <div class="col-md-8">
                        <input class='form-control register' style="margin-bottom: 7px;" type="textbox" id='layer_code'
                            value='<?php echo $BA_Code; ?>' disabled="disabled">
                    </div>
                </div>
            </div>
        </div>
        <div class="row line">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4 control-label"><?php echo __("部署名"); ?></label>
                    <div class="col-md-8">
                        <input class='form-control register' style="margin-bottom: 7px;" type="textbox" id='layer_name'
                            value='<?php echo $BAName; ?>' disabled="disabled">
                    </div>
                </div>
            </div>
        </div>
        <div class="row line">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4 control-label"><?php echo __("対象月"); ?></label>
                    <div class="col-md-8">
                        <input class='form-control register' style="margin-bottom: 7px;" type="textbox"
                            id='target_month' value='<?php echo $target_month; ?>' disabled="disabled">
                    </div>
                </div>
            </div>
        </div>
        <div class="row line">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4 control-label required"><?php echo __("基準年月日"); ?></label>
                    <div class="col-md-8">
                        <div class="input-group date datepicker register" data-provide="datepicker"
                            style="padding: 0px;" data-date-format="yyyy-mm-dd">
                            <input type="text" class="form-control" id="refer_date" name="refer_date" value="" autocomplete="off"/>
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
        <div class="row line">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4 control-label"><?php echo __("提出期日"); ?></label>
                    <div class="col-md-8">
                        <div class="input-group date datepicker register" data-provide="datepicker"
                            style="padding: 0px;" data-date-format="yyyy-mm-dd">
                            <input type="text" class="form-control" id="submission_date" name="submission_date"
                                value="" autocomplete="off"/>
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>

        <div class="form_test">
            <div class="row">
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border"><?php echo __('滞留在庫データインポート');?></legend>
                    <div class="col-md-12">
                        <div class="row form-group">
                            <label class="col-sm-12 col-md-2">
                                <?php echo __('ファイル選択');?>
                            </label>
                            <div class="col-md-1" style="padding-right: 0;">
                                <label style="color: white;" id="browse" class="btn btn-success  <?php if($isApproved) echo 'disabled'; ?>"><?php echo __("アップロード");?>
					                <input type="file" name="uploadfile" id="uploadfile" class ="uploadfile" value="ssss"<?php if($isApproved) echo "disabled"?>>
				                </label>
                            </div>
                            <div class="col-md-2 upd-file-name">
                                <div class="row d-flex flex-row">
                                    <div class="col-md-10" style="overflow:hidden; text-overflow: ellipsis;">
                                        <span id="upd-file-name"></span>
                                    </div>
                                    <div class="col-md-2 stock_excelremove" id="stock_excelremove"><i class="fa-regular fa-circle-xmark" style="color: red;font-size:1.3rem;"></i></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <a href="<?php echo $this->webroot ?>templates/stockimports_template.xlsx" class="temp-download" ><u>Get Template <i class="fa-solid fa-file-arrow-down"></i></u> </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-2"></div>
                    </div>
                    <?php if($showSaveBtn) {?>
                    <div class="col-md-12" style="margin-top: 2rem;">
                        <div class="row mt-5">
                            <div class="col-md-2 "></div>
                            <div class="col-md-6 stock_upload_line" style="text-align: start;">
                                <button type="button" class="btn btn-success file_stock"
                                    onClick="saveFile();"><?php echo __('保存');?></button>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </fieldset>
            </div>
        </div>
        <br><br><br><br><br>
    </div>
</div>

<?php
echo $this->form->end();
?>