<?php
echo $this->Form->create(false, array('type' => 'post', 'class' => 'form-inline', 'id' => 'Accounts', 'name' => 'Accounts', 'enctype' => 'multipart/form-data'));
?>

<style>
#data_entry {
    display: none;
    min-width: 1900px;
}

#tg_year, #la_type {
    display :none;
}
.floatThead-container {
	z-index: 1000 !important;
}
table{
    word-break: break-all;
   
}
thead{
    /*position: sticky;*/
    top: 0;
}

/* .sort-link{
    color : black ;
} */

@media screen and (max-width: 767px) {
    .table-responsive {
        border: none !important;
    }

    table#sub_account {
        border: 1px solid #D3D3D3;
    }

    .goup_nav li {
        display: flex;
    }

   
}
</style>
<?php

if(!empty($searchData) ){
    $search_page = $searchData;
}else if (!empty($search_page) && $rowCount == 0 ) {
	$search_page = '';
} else {
	$search_page = '';
}
$brmPages =  array_slice(Setting::PAGE_NAME, -2);
$brmPages =  implode("','", $brmPages);
?>

<script type="text/javascript">

$(document).ready(function() {
    if ($('#acc_set').length > 0) {
        $("#acc_set").floatThead({position: 'absolute'});
    }
    $("#hid_page").val('');
    $('#hid_search').val("SEARCHALL");
    var search_page = "<?php echo $search_page; ?>";
    var spage_eng = "<?php echo $spage_eng;?>"
    var brmPages = ['<?php echo $brmPages; ?>'];

    if (search_page != '' && spage_eng != '' &&  $.inArray(spage_eng, brmPages) !== -1) {
        $('#copy').prop('disabled', false);
    }
    $('#hid_page').val(search_page);
    
    $("#datepicker").datepicker({
        format: "yyyy",
        viewMode: "years",
        minViewMode: "years",
        autoclose: true
    });

    /* float thead */
    /* end*/

    $("#page_name").on('change',function(){
        var select_id = $("#page_name").val();
        var layer_code = null;
        layerType(select_id,layer_code);
       
    });

    $('#display_order').keypress(function(event) {
        if (event.which < 48 || event.which > 57) {
        event.preventDefault();
        }
    });
    
        

//   $('th').hover(function() {
//         // This code is executed when the mouse enters the element
//         $(this).css('color', 'blue');
//     }, function() {
//         // This code is executed when the mouse leaves the element
//         $(this).css('color', 'black');
//     });
});
</script>

<div id="overlay">
    <span class="loader"></span>
</div>
<div class="content">
    <div class="register_form">
        <fieldset>
            <div class="heading_line_title">
                <legend>
                    <?php echo __('勘定科目設定管理'); ?>
                </legend>
            </div>

            <div class="success" id="success"><?php echo ($this->Session->check("Message.AccountsSuccess")) ? $this->Flash->render("AccountsSuccess") : ''; ?></div>
            <div class="error" id="error"><?php echo ($this->Session->check("Message.AccountsError")) ? $this->Flash->render("AccountsError") : ''; ?></div>

            <div class="row">
                <div class="form-group col-sm-12 col-md-6">
                    <label for="page_name" class="control-label required">
                        <?php echo __("ページ名"); ?>
                    </label>
                    <select class="form-control form_input" name="page_name" id="page_name">
                        <option value="" selected="">
                            <?php echo __("----- Select Page Name -----"); ?>
                            <?php foreach ($account_pages as $menu_id => $page_name): ?>
                        <option value="<?php echo $menu_id; ?>">
                            <?php echo $page_name; ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group col-sm-12 col-md-6">
                    <div class="col-md-3" style="padding-left: 0 !important;">
                        <label for="" class="control-label required" style="width: auto;">
                            <?php echo __("アカウントの種類"); ?>
                        </label>
                    </div>
                    <div class="col-md-3">
                        <label for="total" class="control-label " style="font-weight: 400; width: auto; ">
                            <?php echo __("合計"); ?>
                        </label>
                        <input type="radio" id="total" name="caccount">
                    </div>
                    <div class="col-md-3">
                        <label for="normal" class="control-label " style="font-weight: 400; width: auto; ">
                            <?php echo __("普通"); ?>
                        </label>
                        <input type="radio" id="normal" name="caccount">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-12 col-md-6">
                    <label for="" class="control-label required">
                        <?php echo __("表示順"); ?>
                    </label>
                    <input type="text" class="form-control form_input" id="display_order" name="display_order" value=""
                        maxlength="3" />
                </div>
                <div class="form-group col-sm-12 col-md-6">
                    <label for="" class="control-label required">
                        <?php echo __("勘定科目"); ?>
                    </label>
                    <select class="form-control form_input" name="acc_type" id="acc_type">
                        <option value="" selected=""><?php echo __("----- Select Account -----"); ?></option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-sm-12 col-md-6">
                    <label for="" class="control-label ">
                        <?php echo __("ラベル名"); ?>
                    </label>
                    <input type="text" class="form-control form_input" id="label_name" name="label_name" value="" maxlength="500" /> 
                </div>
                <div class="form-group col-sm-12 col-md-6" id="tg_year">
                    <label for="" class="control-label required">
                        <?php echo __("対象年度"); ?>
                    </label>
                    <div class="input-group date form_input" id="datepicker" data-provide="datepicker"
                        style="padding: 0px;">
                        <input type="text" class="form-control" id="target_year" name="target_year" maxlength="4"
                            value="" autocomplete="off" style="background-color: #fff;" readonly />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-12 col-md-6" id="la_type">
                    <label for="" class="control-label required">
                        <?php echo __('部署'); ?>
                    </label>
                    <select class="form-control form_input" name="layer_type" id="layer_type">
                        <option value="" selected=""><?php echo __("----- Select Layer -----"); ?></option>
                    </select>
                </div>
            </div>
            <div class="row" style="margin-bottom:2rem;">
                <div class="form-group col-sm-12 col-md-6"></div>
                <div class="form-group col-sm-12 col-md-6">
                    <div class="col-md-10 col-sm-12">
                        <input type="button" class="btn-save pull-right" id="btn_save" name="btn_save"
                            value="<?php echo __('保存'); ?>">
                        <input type="button" class="btn-save pull-right" id="btn_update" name="btn_save"
                            style="display: none;" value="<?php echo __('変更'); ?>">
                        <input type="hidden" id="hd_id" name="hd_id"/>
                       
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="row">
        <table width="100%">
            <tr>
            <?php if (!empty($accountSettings)) { ?>
                <td valign="bottom">
                    <div class="pull-left msgfont" style="margin-bottom:0 !important; margin-top:2rem">
                        <span>
                            <?php echo ($rowCount); ?>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="pull-right align-top">
                        <select class="form-control form_input" name="search_page_name" id="search_page_name">
                            <option value="" selected="">
                                <?php echo __("----- Select Page Name -----"); ?>
                                <?php foreach ($search_page_name as $menu_id => $page_name): ?>
                                <?php
                                        $page = $page_name;
                                        if (!empty($search_page)) {
                                            if ($search_page == $page) {
                                                $select = 'selected';
                                            } else {
                                                $select = '';
                                            }
                                        }
                                ?>
                            <option value="<?= $page_name ?>" <?= $select ?>>
                                <?php echo $page_name ?>
                            </option>

                            <?php endforeach ?>
                        </select>
                        <input type="button" class="btn btn-success btn_sumisho" value="<?php echo __('検索'); ?>" name="search"
                            onclick="SearchData();">
                        <!-- it is need to show when the forcecsat and budget are used. -->
                        <!-- <input type="button" data-target="#myModal" data-toggle="modal" data-backdrop="static" data-keyboard="false"
                            class="btn btn-success btn_sumisho" value="<?php echo __('コピー'); ?>" name="copy" id="copy"
                            onclick="popupscreen();" disabled> -->
                    </div>
                </td>
                <?php }?>
            </tr>
        </table>
    </div>
    <?php if (!empty($accountSettings)) { ?>
    
    <div class="row">
        <div style="width:100%;overflow-x: auto;">
            <table class="table table-striped table-bordered tbl_master" id="acc_set" style="white-space: unset;">
                <thead>
                    <tr>
                        <th style="width:65px">
                            <?php echo __('#')?>
                        </th>
                        <th>
                            <?php echo __('ページ名')?>
                        </th>
                        <!-- <th>
                            <?php echo __('対象年度')?>
                        </th>
                        <th>
                            <?php echo __('部署')?>
                        </th> -->
                        <th>
                            <?php echo __('勘定科目')?>
                        </th>
                        <th>
                            <?php echo __('ラベル名')?>
                        </th>
                        <th style="width:65px">
                            <?php echo __('表示順')?>
                        </th>
                        <th class="actions" colspan="2" style="width:130px">
                            <?php echo __('アクション')?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accountSettings as $accountSetting): ?>
                    <tr>
                        <td>
                            <?php echo h($start_num++); ?>&nbsp;
                        </td>
                        <td>
                            <?php if($this->Session->read('Config.language')=='eng'){echo h($accountSetting['Menus']['page_name']);}else{echo h($accountSetting['Menus']['page_name_jp']); }?>&nbsp;
                        </td>
                        <!-- <?php if(!empty($accountSetting['AccountSetting']['target_year'])){ ?>
                            <td>
                                <?php echo h($accountSetting['AccountSetting']['target_year']);?>&nbsp;
                            </td>
                       <?php } else {?>
                            <td style="text-align : center;">-</td>
                        <?php }?> -->
                        <!-- <?php if(!empty($accountSetting['AccountSetting']['layer_code'])){ ?>
                            <td>
                                <?php echo h($accountSetting['AccountSetting']['layer_code']);?>&nbsp;
                            </td>
                        <?php } else {?>
                            <td style="text-align : center;">-</td>
                        <?php }?> -->
                        <td>
                            <?php echo h($accountSetting['Accounts']['account_name']); ?>&nbsp;
                        </td>
                        <td>
                            <?php echo h($accountSetting['AccountSetting']['label_name']); ?>&nbsp;
                        </td>
                        <td>
                            <?php echo h($accountSetting['AccountSetting']['display_order']); ?>&nbsp;
                        </td>
                        <td
                            style="word-break: break-all;text-align: center;width: fit-content; vertical-align:middle;font-size:1.3em !important; ">
                            <a class="" href="#"
                                onclick="click_edit('<?php echo $accountSetting['AccountSetting']['id']; ?>');"
                                title="<?php echo __('編集'); ?> "><i class="fa-regular fa-pen-to-square"></i></a>
                        </td>
                        <td
                            style="word-break: break-all;text-align: center;width: fit-content; vertical-align:middle;font-size:1.3em !important; ">
                            <a class="" href="#" id="btn_submit_delete"
                                onclick="click_delete('<?php echo $accountSetting['AccountSetting']['id']; ?>');"
                                title="<?php echo __('削除'); ?>"><i class="fa-regular fa-trash-can"></i></a>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
            <input type="hidden" name=hid_page id="hid_page">
            <input type="hidden" name=hid_page_no id="hid_page_no">
            <input type="hidden" name=hid_search id="hid_search">
        </div>
    </div>
    <?php } else { ?>
    <div class="row">
        <div class="col-sm-12">
            <p class="no-data"><?php echo __("There is no data.");?></p>
        </div>
    </div>
    <?php } ?>
    <?php if (!empty($accountSettings)) { ?>
    <div class="row col-md-12 d-flex justify-content-center"
        style="padding: 10px;text-align: center;margin-bottom: 50px;">
        <div class="paging">
            <?php
            if ($query_count > $limit) {
            echo $this->Paginator->first('<<');
            echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev disabled'));
            echo $this->Paginator->numbers(array('separator' => '', 'modulus' => 6));
            echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
            echo $this->Paginator->last('>>');
            }
            ?>
        </div> 
    </div>
    <?php } ?>
</div>

<!-- PopUpBox  -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content contantbond">
            <div class="modal-header">
                <button type="button" class="close" id="clearData" data-dismiss="modal">&times;</button>
                <h3 class="modal-title">
                    <?php echo __("コピー確認"); ?>
                </h3>
            </div>
            <div class="modal-body">
                <!-- success,error -->
                <div class="success" id="popupsuccess"></div>
                <div class="error" id="popuperror"></div>
                <!-- end success,error -->
                <div class="table-responsive modal_tbl_wrapper">
                    <div class="col-md-12" style="margin-bottom:1rem;">
                        <div class="form-group popup_row" style="width:100%">
                            <label class="col-md-4 control-label rep_lbl required">
                                <?php echo __("ページ名"); ?>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="from_page" value="<?= $search_page ?>"style="width:100%" name="from_page" disabled>
                                <input type="hidden" name="hid_from_page" value="<?= $search_page ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-bottom:1rem;">
                        <div class="form-group popup_row" style="width:100%">
                            <label for="from_year" class="col-md-4 control-label required">
                                <?php echo __("年から"); ?>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control form_input" name="from_year" id="from_year" style="width:100%" >
                                    <option value="" selected=""><?php echo __("----- Select -----"); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-bottom:1rem;">
                        <div class="form-group popup_row" style="width:100%">
                            <label for="from_layer_code" class="col-md-4 control-label required ">
                                <?php echo __("部署から"); ?>
                            </label>
                            <div class="col-sm-8" >
                                <select class="form-control form_input" name="from_layer_code" id="from_layer_code" style="width:100%">
                                    <option value="" selected=""><?php echo __("----- Select -----"); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-bottom:1rem;">
                        <div class="form-group popup_row" style="width:100%">
                            <label for="to_year" class="col-md-4 control-label required">
                                <?php echo __("年へ"); ?>
                            </label>
                            <div class="col-sm-8">
                            <select class="form-control form_input" name="to_year" id="to_year" style="width:100%" onchange="popUpChange()">
                                <option value="" selected=""><?php echo __("----- Select -----"); ?></option>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-bottom:1rem;">
                        <div class="form-group popup_row" style="width:100%">
                            <label for="to_layer_code" class="col-md-4 control-label required">
                                <?php echo __("部署に"); ?>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control form_input" name="to_layer_code" id="to_layer_code" style="width:100%"">
                                    <option value="" selected=""><?php echo __("----- Select -----"); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name=hid_to_layer_code id="hid_to_layer_code">

                <button type="button" id="term_copy" onclick="CopyData()" class="btn btn-success btn_sumisho"><?php
                    echo __('追加'); ?> </button>
                <button type="button" id="overwrite" onclick="OverwirteData()"
                    class="btn btn-success "><?php echo __('上書き'); ?> </button>
            </div>
        </div>
    </div>
</div>
<!-- end popup -->
</div>


<script>
function scrollText() {
    var tes = $('#error').text();
    var tes1 = $('.success').text();
    if (tes) {
        $("html, body").animate({
            scrollTop: 0
        }, "slow");
    }
    if (tes1) {
        $("html, body").animate({
            scrollTop: 0
        }, "slow");
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
var layer_year = false;

$("#btn_save").click(function() {

    var chk = true;

    /* clear error or success message */
    document.getElementById("success").innerHTML = "";
    document.getElementById("error").innerHTML = "";

    var page_name = $('#page_name').val();
    var target_year = $('#target_year').val();
    var layer_type = $("#layer_type").val();
    var acc_type = $("#acc_type").val();
    var label_name = $("#label_name").val();
    var display_order = $("#display_order").val();
    
    if (!checkNullOrBlank(page_name)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("Page Name") ?>'])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }
    if(layer_year){
        if (!checkNullOrBlank(target_year)) {
            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("Target Year") ?>'])));
            document.getElementById("error").appendChild(a);
            chk = false;
        }

        if (!checkNullOrBlank(layer_type)) {
            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("Layer Code") ?>'])));
            document.getElementById("error").appendChild(a);
            chk = false;
        }
    }
    if (!checkNullOrBlank(acc_type)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, [
            '<?php echo __("Account") ?>'
        ])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    if (!checkNullOrBlank(display_order)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
            '<?php echo __("Display Order") ?>'
        ])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }
    
    if(!$("input[name='caccount']").is(':checked')){
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, [
            '<?php echo __("Account Type") ?>'
        ])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    if (chk) {
        $.confirm({
            title: '<?php echo __("保存確認"); ?>',
            icon: 'fas fa-exclamation-circle',
            type: 'blue',
            boxWidth: '30%',
            useBootstrap: false,
            typeAnimated: true,
            closeIcon: true,
            columnClass: 'medium',
            animateFromElement: true,
            animation: 'top',
            draggable: false,
            content: "<?php echo __("データを保存してよろしいですか。"); ?>",
            buttons: {
                ok: {
                    text: '<?php echo __("はい"); ?>',
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action =
                            "<?php echo $this->webroot; ?>AccountSettings/saveData";
                        document.forms[0].method = "POST";
                        document.forms[0].submit();

                        return true;
                    }
                },
                cancel: {
                    text: '<?php echo __("いいえ"); ?>',
                    btnClass: 'btn-default',
                    cancel: function() {
                        								
                    }

                }
            },
            theme: 'material',
            animation: 'rotateYR',
            closeAnimation: 'rotateXR'
        });
    }
    scrollText();

});



$("#btn_update").click(function() {

    var chk = true;
    var path = window.location.pathname;
    var page = path.split("/").pop();
    $('#hid_page_no').val(page);

    /* clear error or success message */
    document.getElementById("success").innerHTML = "";
    document.getElementById("error").innerHTML = "";
    var hd_id = $('#hd_id').val();
    var page_name = $('#page_name').val();
    var target_year = $('#target_year').val();
    var layer_type = $("#layer_type").val();
    var acc_type = $("#acc_type").val();
    var label_name = $("#label_name").val();
    var display_order = $("#display_order").val();

    if (!checkNullOrBlank(page_name)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("Page Name") ?>'])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }
    if(layer_year){
        if (!checkNullOrBlank(target_year)) {
            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("Target Year") ?>'])));
            document.getElementById("error").appendChild(a);
            chk = false;
        }

        if (!checkNullOrBlank(layer_type)) {
            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __(Setting::LAYER_SETTING['bottomLayer']) ?>'])));
            document.getElementById("error").appendChild(a);
            chk = false;
        }
    }
    if (!checkNullOrBlank(acc_type)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, [
            '<?php echo __("Account") ?>'
        ])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    if (!checkNullOrBlank(display_order)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
            '<?php echo __("Display Order") ?>'
        ])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    if(!$("input[name='caccount']").is(':checked')){
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, [
            '<?php echo __("Account Type") ?>'
        ])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    if (chk) {
        $.confirm({
            title: '<?php echo __("変更確認"); ?>',
            icon: 'fas fa-exclamation-circle',
            type: 'blue',
            boxWidth: '30%',
            useBootstrap: false,
            typeAnimated: true,
            closeIcon: true,
            columnClass: 'medium',
            animateFromElement: true,
            animation: 'top',
            draggable: false,
            content: "<?php echo __("データを変更してよろしいですか。"); ?>",
            buttons: {
                ok: {
                    text: '<?php echo __("はい"); ?>',
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action =
                            "<?php echo $this->webroot; ?>AccountSettings/saveData";
                        document.forms[0].method = "POST";
                        document.forms[0].submit();

                        return true;
                    }
                },
                cancel: {
                    text: '<?php echo __("いいえ"); ?>',
                    btnClass: 'btn-default',
                    cancel: function() {
                        								
                    }

                }
            },
            theme: 'material',
            animation: 'rotateYR',
            closeAnimation: 'rotateXR'
        });
    }
    scrollText();

});



function click_edit(id) {
    document.getElementById('error').innerHTML = '';
    document.getElementById('success').innerHTML = '';
    // $("#error").empty("");
    // $("#success").empty("");
    $('#hd_id').val(id);
    
    $.ajax({
        type: "POST",
        url: "<?php echo $this->webroot; ?>AccountSettings/edit",
        data: {
            id: id
        },
        dataType: "json",
        success: function(data) {
            
            var id = data['AccountSetting']['id'];
            var page_name = data['AccountSetting']['menu_id'];
            var target_year = data['AccountSetting']['target_year'];
            var account_code = data['Accounts']['account_code'];
            var label_name = data['AccountSetting']['label_name'];
            var account_id = data['Accounts']['id'];
            var display_order = data['AccountSetting']['display_order'];
            var layer_code = data['AccountSetting']['layer_code'];
            var acc_type = data['Accounts']['account_type'];

            if(acc_type == 2){
                var totalAcc = 2;
                var noAcc = '';
            } else { 
                noAcc = 1;
                totalAcc = '';
            }
            layerType(page_name,layer_code);
            checkAccount(account_id,totalAcc,noAcc);
            $('#page_name option[value=' + page_name + ']').prop('selected', true);
            $('#display_order').val(display_order);
            $('#label_name').val(label_name);
            $('#target_year').val(target_year);
            $('#layer_type').val(account_code);

            $('#overlay').hide();
            $('#btn_save').hide();
            $('#btn_update').show();
        },

    });

}

function click_delete(id) {

    document.getElementById('error').innerHTML = '';
    document.getElementById('success').innerHTML = '';
    var path = window.location.pathname;
    var page = path.split("/").pop();
    $('#hid_page_no').val(page);
    // $("#error").empty("");
    // $("#success").empty("");
    $('#hd_id').val(id);

    $.confirm({
        title: '<?php echo __("削除確認"); ?>',
        icon: 'fas fa-exclamation-circle',
        type: 'red',
        typeAnimated: true,
        boxWidth: '30%',
        useBootstrap: false,
        closeIcon: true,
        columnClass: 'medium',
        animateFromElement: true,
        animation: 'top',
        draggable: false,
        content: "<?php echo __("データを削除してよろしいですか。"); ?>",
        buttons: {
            ok: {
                text: '<?php echo __("はい"); ?>',
                btnClass: 'btn-info',
                action: function() {
                    loadingPic();
                    document.forms[0].method = "POST";
                    document.forms[0].action = "<?php echo $this->webroot; ?>AccountSettings/deleteData";
                    document.forms[0].submit();

                    return true;
                }
            },
            cancel: {
                text: '<?php echo __("いいえ"); ?>',
                btnClass: 'btn-default',
                cancel: function() {
                    //.log('the user clicked cancel');	
                    scrollText();
                }

            }
        },
        theme: 'material',
        animation: 'rotateYR',
        closeAnimation: 'rotateXR'
    });

}

function SearchData() {

    document.getElementById("error").innerHTML = "";
    document.getElementById("success").innerHTML = "";
    var page_name = $("#search_page_name").val();

    if (page_name == '') $("#hid_search").val('SEARCHALL');
		else $("#hid_search").val('');
        
    var query = '';
    if (page_name) {
        query = '?page_name=' + page_name;
    }

    document.forms[0].action = "<?php echo $this->webroot; ?>AccountSettings" + query;
    document.forms[0].method = "POST";
    document.forms[0].submit();
    $("html, body").animate({
        scrollTop: 10
    }, "fast");
}

function popupscreen() {

    var page_name = $('#search_page_name').val();

    // $('#select_page option').removeAttr('selected').filter('[value=""]').attr('selected', true);
    $('#popuperror').hide();
    $('#overwrite').hide();

    $.ajax({
        type: "POST",
        url: "<?php echo $this->webroot; ?>AccountSettings/getYearLayer",
        data: {
            page_name: page_name,
        },
        dataType: "json",
        success: function(data) {
            if(data.from_range_year != ''){
                $('#from_year').empty();
                $('#from_year').append("<option value =''>" + '----- Select -----' + "</option>");
                $.each(data.from_range_year, function(key, value) {
                    $('#from_year').append("<option value ='" + value['AccountSetting']['year'] + "'>" + value['AccountSetting']['year'] + "</option>");
                });
            }
            if(data.from_layer_type != ''){
                $('#from_layer_code').empty();
                $('#from_layer_code').append("<option value =''>" + '----- Select -----' + "</option>");
                $.each(data.from_layer_type, function(key, value) {
                    $('#from_layer_code').append("<option value ='" + key + "'>" + key+'/'+value + "</option>");
                });
            }
           
            if(data.to_range_year != ''){
                $('#to_year').empty();
                $('#to_year').append("<option value =''>" + '----- Select -----' + "</option>");
                $.each(data.to_range_year, function(key, value) {
                    $('#to_year').append("<option value ='" + value + "'>" + value + "</option>");
                });
            }
        },
    });
}

function CopyData() {
    $('#popuperror').show();
    var from_page = $('#from_page').val();
    var from_year = $('#from_year').val();
    var to_year = $('#to_year').val();
    var from_layer_code = $('#from_layer_code').val();
    var to_layer_code = $('#to_layer_code').val();

    document.getElementById("popupsuccess").innerHTML = "";
    document.getElementById("popuperror").innerHTML = "";
    var chk = true;
    //number check		
    if (!checkNullOrBlank(from_year)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("popuperror").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("年から") ?>'])));
        document.getElementById("popuperror").appendChild(a);
        chk = false;
    }
    if (!checkNullOrBlank(to_year)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("popuperror").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("年へ") ?>'])));
        document.getElementById("popuperror").appendChild(a);
        chk = false;
    }
    if (!checkNullOrBlank(from_layer_code)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("popuperror").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("部署から ") ?>'])));
        document.getElementById("popuperror").appendChild(a);
        chk = false;
    }
    if (!checkNullOrBlank(to_layer_code)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("popuperror").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("部署に") ?>'])));
        document.getElementById("popuperror").appendChild(a);
        chk = false;
    }
    
    if (chk) {
        $("#popuperror").css("display", "none");
        $.confirm({
            title: '<?php echo __("コピー確認"); ?>',
            icon: 'fas fa-exclamation-circle',
            type: 'blue',
            typeAnimated: true,
            boxWidth: '30%',
            useBootstrap: false,
            closeIcon: true,
            columnClass: 'medium',
            animateFromElement: true,
            animation: 'top',
            draggable: false,
            content: "<?php echo __("データをコピーしてよろしいですか。"); ?>",
            buttons: {
                ok: {
                    text: '<?php echo __("はい"); ?>',
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action =
                            "<?php echo $this->webroot; ?>AccountSettings/CopyAccountSetting";
                        document.forms[0].method = "POST";
                        document.forms[0].submit();

                        return true;
                    }
                },
                cancel: {
                    text: '<?php echo __("いいえ"); ?>',
                    btnClass: 'btn-default',
                    cancel: function() {
                      
                    }

                }
            },
            theme: 'material',
            animation: 'rotateYR',
            closeAnimation: 'rotateXR'
        });
    }
}

function OverwirteData() {

    var from_page = $('#from_page').val();
    var from_year = $('#from_year').val();
    var to_year = $('#to_year').val();
    var from_layer_code = $('#from_layer_code').val();
    var to_layer_code = $('#to_layer_code').val();
    console.log(from_year,',',to_year,',',from_layer_code,',',to_layer_code);


    document.getElementById("popupsuccess").innerHTML = "";
    document.getElementById("popuperror").innerHTML = "";
    var chk = true;
    //number check		
    if (!checkNullOrBlank(from_year)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("popuperror").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("年から") ?>'])));
        document.getElementById("popuperror").appendChild(a);
        chk = false;
    }
    if (!checkNullOrBlank(to_year)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("popuperror").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("年へ") ?>'])));
        document.getElementById("popuperror").appendChild(a);
        chk = false;
    }
    if (!checkNullOrBlank(from_layer_code)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("popuperror").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("部署から ") ?>'])));
        document.getElementById("popuperror").appendChild(a);
        chk = false;
    }
    if (!checkNullOrBlank(to_layer_code)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("popuperror").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("部署に") ?>'])));
        document.getElementById("popuperror").appendChild(a);
        chk = false;
    }
    if (chk) {
        $("#popuperror").css("display", "none");
        $.confirm({
            title: '<?php echo __("上書き確認"); ?>',
            icon: 'fas fa-exclamation-circle',
            type: 'orange',
            typeAnimated: true,
            boxWidth: '30%',
            useBootstrap: false,
            closeIcon: true,
            columnClass: 'medium',
            animateFromElement: true,
            animation: 'top',
            draggable: false,
            content: "<?php echo __("上書きしますか？"); ?>",
            buttons: {
                ok: {
                    text: '<?php echo __("はい"); ?>',
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action =
                            "<?php echo $this->webroot; ?>AccountSettings/OverwirteDataCopy";
                        document.forms[0].method = "POST";
                        document.forms[0].submit();

                        return true;
                    }
                },
                cancel: {
                    text: '<?php echo __("いいえ"); ?>',
                    btnClass: 'btn-default',
                    cancel: function() {
                     
                    }

                }
            },
            theme: 'material',
            animation: 'rotateYR',
            closeAnimation: 'rotateXR'
        });
    }
}

$("input[name='caccount']").click(function() {
    if ($('#total').is(':checked')) {
        var totalAcc = 2;
    } else {
        totalAcc = '';
    }

    if ($('#normal').is(':checked')) {
        var noAcc = 1
    } else {
        noAcc = '';
    }
    var id=null;
    checkAccount(id,totalAcc,noAcc);
    
});

function checkAccount(check,totalAcc,noAcc){
    $.ajax({
        type: "POST",
        url: "<?php echo $this->webroot; ?>AccountSettings/choiceAccount",
        data: {
            total: totalAcc,
            normal: noAcc,
            check: check
        },
        dataType: "json",
        success: function(data) {
            
            if(data.check.length == 0){
                $('#acc_type').empty();
                $('#acc_type').append("<option value =''>" + '----- Select Account -----' + "</option>");
                $.each(data.result, function(key, value) {
                    $('#acc_type').append("<option value ='" + value['accounts']['id'] + "'>" +
                        value['accounts']['account_name'] + "</option>");
                });

            } else {

                if(data.total == 2){
                    $('#total').prop('checked', true);
                } else {
                    $('#normal').prop('checked', true);
                }
                $('#acc_type').empty();
                $('#acc_type').append("<option value =''>" + '----- Select Account -----' + "</option>");
                $.each(data.result, function(key, value) {
                    $('#acc_type').append("<option value ='" + value['accounts']['id'] + "'>" +
                        value['accounts']['account_name'] + "</option>");
                });
                $('#acc_type option[value=' + data.check + ']').prop('selected', true);
            }
            
        },
    });
}
function layerType(id,layer_code){
    if(id){
        $.ajax({
            type: "POST",
            url: "<?php echo $this->webroot; ?>AccountSettings/getlayers",
            data: { menu_id : id,  layer_code: layer_code},
            dataType: 'json',
            success: function(data){
               
                if(data == null){
                    $("#tg_year").hide();
                    $("#la_type").hide();
                    $("#target_year").val(null);
                    $("#layer_type").val(null);
                    layer_year = false;
                } else {
                    layer_year = true;
                    $("#tg_year").show();
                    $("#la_type").show();
                    
                    if(data.layer_code.length == 0){
                        $('#layer_type').empty();
                        $('#layer_type').append("<option value =''>" + '----- Select -----' + "</option>");
                        $.each(data.layer_type, function(key, value) {
                        $('#layer_type').append("<option value ='" + key + "'>" +
                            key+'/'+value + "</option>");
                        
                        });
                    } else {
                        $('#layer_type').empty();
                        $.each(data.layer_type, function(key, value) {
                        $('#layer_type').append("<option value ='" + key + "'>" +
                            key+'/'+value + "</option>");
                        
                        });
                        $('#layer_type option[value=' + data.layer_code + ']').prop('selected', true);

                       
                    }
                    
                }
                
            }
        })
    }
} 


function popUpChange(){
    document.getElementById("popupsuccess").innerHTML = "";
    document.getElementById("popuperror").innerHTML = "";
    var page_name = $("#search_page_name").val();
    var from_year = $('#from_year').val();
    var from_layer_code = $('#from_layer_code').val();
    var to_year = $('#to_year').val();
    var to_layer = $('#to_layer_code').val();
    $('#hid_to_layer_code').val(to_layer);
    var hid_to_layer = $('#hid_to_layer_code').val();
    
    $.ajax({
        type: "POST",
        url: "<?php echo $this->webroot; ?>AccountSettings/getYearLayer",
        data: {
            page_name: page_name,
            from_year: from_year,
            from_layer_code: from_layer_code,
            to_year : to_year
        },
        dataType: 'json',
        success: function(data){
          
            if(data.to_layer_type != ''){
                $('#to_layer_code').empty();
                $('#to_layer_code').append("<option value =''>" + '----- Select -----' + "</option>");
                $.each(data.to_layer_type, function(key, value) {
                    
                    $('#to_layer_code').append("<option value ='" + key + "'>" + key+'/'+value + "</option>");
                });
            }
            
        }

    })
}

$('#to_year').on('change',function(){
  
    var to_year = $('#to_year').val();
    var to_layer = $('#to_layer_code').val();
    if(to_layer != ''){
        to_layer = '';
    }

    $.ajax({
        type : "POST",
        url: "<?php echo $this->webroot; ?>AccountSettings/OverwriteCheck",
        data : {
            to_year : to_year, to_layer : to_layer
        },
        dataType : 'json',
        success : function(data){

            if(data.check){
                $('#overwrite').show();
            }  else {
                $('#overwrite').hide();
                $('#to_layer_code').val('');   

            }
        }
    });
    
});

$('#to_layer_code').on('change',function(){
  
  var to_year = $('#to_year').val();
  var to_layer = $('#to_layer_code').val();
  var hid_to_layer = $('#hid_to_layer_code').val();


  $.ajax({
      type : "POST",
      url: "<?php echo $this->webroot; ?>AccountSettings/OverwriteCheck",
      data : {
          to_year : to_year, to_layer : to_layer
      },
      dataType : 'json',
      success : function(data){

          if(data.check){
              $('#overwrite').show();
          }  else {
              $('#overwrite').hide();
          }
          
      }
  });
  
});


</script>

<?php
echo $this->Form->end();
?>