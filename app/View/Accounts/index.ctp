<?php
echo $this->Form->create(false, array('type' => 'post', 'class' => 'form-inline', 'id' => 'Accounts', 'name' => 'Accounts', 'enctype' => 'multipart/form-data'));
?>
<style>
#data_entry {
    display: none;
    min-width: 1900px;
}

thead{
    /*position: sticky;*/
    top: 0;
}
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

.adjust-width{
    width:25% !important;
}
</style>

<script type="text/javascript">
const CATEGORY = {
    'NORMAL': 1,
    'TOTAL': 2,
};

$(document).ready(function() {

    /* float thead */
        if ($('#tbl_account').length > 0) {
            $("#tbl_account").floatThead({position: 'absolute'});
        }

    /* end*/

});
</script>

<div id="overlay">
    <span class="loader"></span>
</div>
<div class="content">
    <div class="register_form">
        <div class="row">
            <fieldset>
                <div class="col-md-12 col-sm-12 heading_line_title">
                    <legend><?php echo __('勘定科目管理'); ?></legend>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="success" id="success"><?php echo ($this->Session->check("Message.SuccessMsg")) ? $this->Flash->render("SuccessMsg") : ''; ?><?php echo ($this->Session->check("Message.AccountsSuccess")) ? $this->Flash->render("AccountsSuccess") : ''; ?></div>
                    <div class="error" id="error"><?php echo ($this->Session->check("Message.ErrorMsg")) ? $this->Flash->render("ErrorMsg") : ''; ?><?php echo ($this->Session->check("Message.AccountsError")) ? $this->Flash->render("AccountsError") : ''; ?></div>
                </div>
                <!-- <div class="form-row"> -->
                    <div class="form-group col-sm-12 col-md-6">
                        <label for="" class="control-label required adjust-width">
                            <?php echo __("勘定科目コード"); ?>
                        </label>
                        <input type="text" oninput="this.value=this.value.replace(/(?![0-9])./gmi,'')"
                            class="form-control form_input" id="acc_code" name="acc_code" value="" maxlength="500" />
                    </div>
                    <div class="form-group col-sm-12 col-md-6">
                        <label for="" class="control-label required adjust-width">
                            <?php echo __("勘定科目名"); ?>
                        </label>
                        <input type="text" class="form-control form_input" id="acc_name" name="acc_name" value=""
                            maxlength="500" />
                    </div>
                    <div class="form-group col-sm-12 col-md-6">
                        <label for="" class="control-label required adjust-width">
                            <?php echo __("計算タイプ"); ?>
                        </label>
                        <select class="form-control form_input" name="acc_category" id="acc_category">
                            <option value="" selected=""><?php echo __("----- Select Account Category -----"); ?>
                                <?php foreach (Setting::ACCOUNT_CATEGORY as $key => $value) : ?>
                            <option value="<?php echo $value['id']; ?>"><?php echo __(ucfirst(strtolower($value['description']))); ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-12 col-md-6">
                        <label for="" class="control-label adjust-width">
                            <?php echo __("ユニット"); ?>
                        </label>
                        <input type="text" class="form-control form_input" id="postfix" name="postfix" value=""
                            maxlength="1" oninput="this.value=this.value.replace(/^[ A-Za-z0-9_@./#&+-]*$/gmi,'')" />
                    </div>
                    <!-- <div class="form-row"> -->
                        <div class="form-group col-sm-12 col-md-6">
                            <label for="" class="control-label required adjust-width">
                                <?php echo __("テーブル種類"); ?>
                            </label>
                            <select class="form-control form_input" name="acc_type" id="acc_type">
                                <option value="" selected=""><?php echo __("----- Select Account Type -----"); ?>
                                    <?php foreach ($account_types as $key => $value) : ?>
                                <option value="<?php echo $value['AccountType']['id']; ?>">
                                    <?php echo $value['AccountType']['type_name']; ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-12 col-md-6">
                            <label for="" class="control-label adjust-width">
                                <?php echo __("メモ"); ?>
                            </label>
                            <input type="text" class="form-control form_input" id="memo" name="memo" value=""
                                maxlength="500" />
                        </div>
                    <!-- </div> -->
                <!-- </div>
                <div class="form-row"> -->
                <div class="form-group col-sm-12 col-md-6">
                    <!-- <label for="" class="control-label">
                        <?php echo __("Calculation Formula"); ?>
                    </label>
                    <input type="text" class="form-control form_input" id="formula" name="formula" value="" maxlength="500" /> -->
                </div>

                <div class="form-group col-sm-12 col-md-6">
                    <div class="col-md-10 col-sm-12">
                        <input type="button" class="btn-save pull-right" id="btn_save" name="btn_save"
                            value="<?php echo __('保存'); ?>">
                        <input type="button" class="btn-save pull-right" id="btn_update" name="btn_save"
                            style="display: none;" value="<?php echo __('変更'); ?>">
                        <input type="hidden" id="hd_base_param" name="hd_base_param" />
                    </div>
                </div>

                <!-- </div> -->
            </fieldset>
        </div>
    </div>




    <div class="display_div">
        <?php if (!empty($data)) { ?>
        <div class=" msgfont" id="total_row">
            <?= $count; ?>
        </div>

        <div style="width:100%;overflow-x: auto;">
            <table class="table table-striped table-bordered tbl_master" id="tbl_account"
                style="margin-top:10px;width: 100%;">
                <thead>
                    <tr>

                        <th class="w-40">#</th>
                        <th class="w-150"><?php echo __("勘定科目コード"); ?></th>
                        <th><?php echo __("勘定科目名"); ?></th>
                        <th><?php echo __("計算タイプ"); ?></th>
                        <th><?php echo __("ユニット"); ?></th>
                        <th><?php echo __("テーブル種類"); ?></th>
                        <th><?php echo __("メモ"); ?></th>
                        <th colspan="2" class="w-130"><?php echo __("アクション"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $num = 0;
                        foreach ($data as $result) :
                            $account_id         = $result['Account']['id'];
                            $account_code         = $result['Account']['account_code'];
                            $account_name         = $result['Account']['account_name'];
                            $account_type         = $result['Account']['account_type'];
                            $base_param         = $result['Account']['base_param'];
                            $type_name             = $result['account_types']['type_name'];
                            $display_number     = $result['Account']['display_number'];
                            $memo = $result['Account']['memo'];
                            $postfix = $result['Account']['postfix'];

                        ?>
                    <tr class="text-left">

                        <td><?php echo ++$num; ?></td>
                        <td><?php echo h($account_code); ?></td>
                        <td style="white-space : normal;"><?php echo h($account_name); ?></td>
                        <td><?php echo $account_type == 1 ? '普通' : '合計'; ?></td>
                        <td><?php echo $postfix; ?></td>
                        <td><?php echo $type_name; ?></td>
                        <td><?php echo $memo; ?></td>
                        <td
                            style="word-break: break-all;text-align: center;width: fit-content; vertical-align:middle;font-size:1.3em !important; ">
                            <a class="" href="#" onclick="click_edit('<?php echo $account_id; ?>');"
                                title="<?php echo __('編集'); ?> "><i class="fa-regular fa-pen-to-square"></i></a>
                        </td>
                        <td
                            style="word-break: break-all;text-align: center;width: fit-content; vertical-align:middle;font-size:1.3em !important; ">
                            <a class="" href="#" id="btn_submit_delete"
                                onclick="click_delete('<?php echo $account_id; ?>');" title="<?php echo __('削除'); ?>"><i
                                    class="fa-regular fa-trash-can"></i></a>
                        </td>
                    </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
            <input type="hidden" id='hd_acc_id' name='hd_acc_id' value="" />
        </div>
        <?php } else { ?>
        <div class="row">
            <div class="col-sm-12">
                <p class="no-data"><?php echo $no_data; ?></p>
            </div>
        </div>
        <?php } ?>
        <?php if (!empty($data)) { ?>
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

$("#btn_save").click(function() {


    var chk = true;

    /* clear error or success message */
    document.getElementById("success").innerHTML = "";
    document.getElementById("error").innerHTML = "";
    // $("#error").empty("");
    // $("#success").empty("");

    var acc_code = document.getElementById("acc_code").value.trim();
    var acc_name = document.getElementById("acc_name").value.trim();
    var acc_type = document.getElementById("acc_type").value;
    var acc_category = document.getElementById("acc_category").value;
    var postfix = document.getElementById("postfix").value.trim();
    var memo = document.getElementById("memo").value.trim();
    // var formula = document.getElementById("formula").value;
    // //var operator 		= formula.replace(/[^-+*/%=]/g, "").split("");

    // if (!!formula.match(/\(([^)]+)\)_lastyear/) === true) {
    //     var b_param_arr = formula.replace(formula.match(/\(([^)]+)\)_lastyear/)[0], "").split(/[,=+\-*\/%]/);
    // } else
    //     var b_param_arr = formula.split(/[,=+\-*\/%]/);


    // var b_param = b_param_arr.map((str, index) => {

    //         if (/\((.+?)\)/g.test(str) === false) {

    //             if (str.toLowerCase().includes('_lastyear') === false) {

    //                 return str.replace(/\D/g, "");
    //                 return num;
    //             }
    //         }
    //     })
    //     .filter(Number);

    // var final_b_param = [...new Set(b_param)];
    // document.getElementById("hd_base_param").value = final_b_param.length == 1 ? `"${final_b_param}"` :
    //     final_b_param.map(str => `"${str}"`);

    
    
    if (!checkNullOrBlank(acc_code)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("勘定科目コード") ?>'])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    if (!checkNullOrBlank(acc_name)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("勘定科目名") ?>'])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }


    if (!checkNullOrBlank(acc_category)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
            '<?php echo __("計算タイプ") ?>'
        ])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    if (!checkNullOrBlank(acc_type)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("テーブル種類") ?>'])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }



    if (chk) {
        $.confirm({
            title: '<?php echo __("保存確認"); ?>',
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
            content: "<?php echo __("データを保存してよろしいですか。"); ?>",
            buttons: {
                ok: {
                    text: '<?php echo __("はい"); ?>',
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action = "<?php echo $this->webroot; ?>Accounts/saveData";
                        document.forms[0].method = "POST";
                        document.forms[0].submit();

                        return true;
                    }
                },
                cancel: {
                    text: '<?php echo __("いいえ"); ?>',
                    btnClass: 'btn-default',
                    cancel: function() {
                        // console.log('the user clicked cancel');	
                        // scrollText();								
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

    /* clear error or success message */
    document.getElementById("success").innerHTML = "";
    document.getElementById("error").innerHTML = "";
    // $("#error").empty("");
    // $("#success").empty("");

    var acc_code = document.getElementById("acc_code").value.trim();
    var acc_name = document.getElementById("acc_name").value.trim();
    var acc_type = document.getElementById("acc_type").value;
    var acc_category = document.getElementById("acc_category").value;
    var postfix = document.getElementById("postfix").value.trim();
    var memo = document.getElementById("memo").value.trim();
    // var formula = document.getElementById("formula").value;

    // // var operator 		= formula.replace(/[^-+*/%=]/g, "").split("");
    // // console.log(operator);


    // if (!!formula.match(/\(([^)]+)\)_lastyear/) === true) {
    //     var b_param_arr = formula.replace(formula.match(/\(([^)]+)\)_lastyear/)[0], "").split(/[,=+\-*\/%]/);
    // } else
    //     var b_param_arr = formula.split(/[,=+\-*\/%]/);


    // var b_param = b_param_arr.map((str, index) => {

    //         if (/\((.+?)\)/g.test(str) === false) {

    //             if (str.toLowerCase().includes('_lastyear') === false) {

    //                 return str.replace(/\D/g, "");
    //                 return num;
    //             }
    //         }
    //     })
    //     .filter(Number);

    // var final_b_param = [...new Set(b_param)];
    // document.getElementById("hd_base_param").value = final_b_param.length == 1 ? `"${final_b_param}"` :
    //     final_b_param.map(str => `"${str}"`);

    if (!checkNullOrBlank(acc_code)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("勘定科目コード") ?>'])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    if (!checkNullOrBlank(acc_name)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("勘定科目名") ?>'])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    if (!checkNullOrBlank(acc_category)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
            '<?php echo __("計算タイプ") ?>'
        ])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    if (!checkNullOrBlank(acc_type)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("テーブル種類") ?>'])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }


    if (chk) {
        $.confirm({
            title: '<?php echo __("変更確認"); ?>',
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
            content: "<?php echo __("データを変更してよろしいですか。"); ?>",
            buttons: {
                ok: {
                    text: '<?php echo __("はい"); ?>',
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action =
                            "<?php echo $this->webroot; ?>Accounts/updateData";
                        document.forms[0].method = "POST";
                        document.forms[0].submit();

                        return true;
                    }
                },
                cancel: {
                    text: '<?php echo __("いいえ"); ?>',
                    btnClass: 'btn-default',
                    cancel: function() {
                        // console.log('the user clicked cancel');	
                        // scrollText();								
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
    // console.log("object")
    document.getElementById('error').innerHTML = '';
    document.getElementById('success').innerHTML = '';
    // $("#error").empty("");
    // $("#success").empty("");

    document.getElementById('hd_acc_id').value = id;

    $.ajax({
        type: "POST",
        url: "<?php echo $this->webroot; ?>Accounts/editData",
        data: {
            id: id
        },
        dataType: "json",
        beforeSend: function() {
            loadingPic();
        },
        success: function(data) {

            var id = data['id'];
            var account_code = data[0]['Account']['account_code'];
            var account_name = data[0]['Account']['account_name'];
            var account_type = data[0]['Account']['account_type'];
            var account_type_id = data[0]['account_types']['id'];
            var postfix = data[0]['Account']['postfix'];
            var memo = data[0]['Account']['memo'];
            // var calculation_formula = data[0]['Account']['calculation_formula'];

            $('#acc_type option[value=' + account_type_id + ']').prop('selected', true);
            $('#acc_name').val(account_name);
            $('#acc_code').val(account_code);
            $('#postfix').val(postfix);
            $('#memo').val(memo);
            //  $('#formula').val(calculation_formula);
            $('#acc_category option[value=' + account_type + ']').prop('selected', true);
            $('#overlay').hide();
            $('#btn_save').hide();
            $('#btn_update').show();
        },

    });

}

function click_delete(id) {

    document.getElementById('error').innerHTML = '';
    document.getElementById('success').innerHTML = '';
    // $("#error").empty("");
    // $("#success").empty("");
    document.getElementById('hd_acc_id').value = id;

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
                    document.forms[0].action = "<?php echo $this->webroot; ?>Accounts/deleteData";
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
</script>

<?php
echo $this->Form->end();
?>