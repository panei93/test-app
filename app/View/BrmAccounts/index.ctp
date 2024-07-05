<style>
body {
    /* font-family: KozGoPro-Regular; */
}


#data_entry {
    display: none;
    min-width: 1900px;
}

.align-left {
    text-align: left !important;
}

.align-right {
    text-align: right !important;
}

.align-center {
    text-align: center !important;
}

.btn_approve_style {
    width: 150px;
    margin: 5px;
}

.acc-link-list,
.sale-link-list {
    margin-top: 6px;
}

.btn-delete {
    float: right;
    color: red;
    margin-top: -25px;
}

.btn-delete:hover {
    cursor: pointer;
}

a.acc-down-link,
a.sale-down-link {
    display: inline-block;
    width: 100px;
    margin-right: 40px;
    margin-left: 40px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
    color: #19b5fe;
    text-decoration: underline;
}

.fl-scrolls {
    margin-bottom: 40px;
    /* modify floating scroll bar */
}

input#btn_update_account {
    display: none;
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
    min-height: 62px;
}

.goup_nav li {
    width: 20%;
    /*display: flex;*/
}

#myTabContent {
    padding: 15px;
}

.jconfirm-box-container {
    margin-left: unset !important;
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
</style>
<div id="overlay">
    <span class="loader"></span>
</div>
<form class="form-horizontal" id="form1" method="post">
    <div class="content register_container">
        <h3><?php echo __('アカウント管理'); ?></h3>
        <hr>
        <?php //pr($this->Flash->render("AccountSuccess"));?>
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.AccountSuccess"))? $this->Flash->render("AccountSuccess") : '';?></div>
			<div class="error" id="error"><?php echo ($this->Session->check("Message.AccountError"))? $this->Flash->render("AccountError") : '';?></div>
		</div>


        <div class="row">
            <div class="col-sm-8">

                <!-- hidden value -->
                <input type="hidden" name="hid_page_no" id="hid_page_no" class="txtbox" value="">
                <input type="hidden" name="hid_g_code" id="hid_g_code" class="txtbox" value="">
                <!-- when account_code is duplicate, reshow user choosen data in ui -->
                <div class="form-group">
                    <label class="control-label col-sm-3 align-left required"><?php echo __("グループコード"); ?></label>
                    <div class="col-md-5 col-xs-12">
                        <select name="g_code" id="g_code" class="form-control">
                            <option value=''>---- <?php echo __("選択") ?> ----</option>
                            <?php foreach ($account_groups as $code => $name) : ?>
                            <?php if ($code == $request['g_code']) : ?>
                            <option value="<?php echo $code; ?>" selected><?php echo __($name); ?></option>
                            <?php else : ?>
                            <option value="<?php echo $code; ?>"><?php echo __($name); ?></option>
                            <?php endif ?>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3 align-left required"><?php echo __("勘定科目名(JP)"); ?></label>
                    <div class="col-md-5 col-xs-12">
                        <input type="text" id="name_jp" name="name_jp" class="form-control" maxlength="500" value="<?php
						if (!empty($request)) {
							echo h($request['name_jp']);
						}
						?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3 align-left"><?php echo __("勘定科目名(ENG)"); ?></label>
                    <div class="col-md-5 col-xs-12">
                        <input type="text" id="name_en" name="name_en" class="form-control" maxlength="250" value="<?php
							if (!empty($request)) {
								echo h($request['name_en']);
							}
							?>">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-8 align-right" style="height: 5rem;">
                        <!-- <input type="hidden" name="error_msg" id="error_msg" value="">	 -->
                        <!-- <input type="hidden" name="btn_name" id="btn_name" value="Save">-->
                        <input type="hidden" name="id_vl" id="id_vl" value="" />
                        <input type="hidden" name="deleteItem" id="deleteItem" value="" />
                        <input type="button" name="btn_save_account" id="btn_save_account"
                            class="btn-save btn-success btn_sumisho" onclick="click_Save();"
                            value="<?php echo __("保存"); ?>">
                        <input type="button" name="btn_update_account" id="btn_update_account"
                            class="btn-save btn-success btn_sumisho" onclick="click_Update();"
                            value="<?php echo __("変更"); ?>">
                    </div>
                </div>

            </div>

        </div>
        <!-- nav tab -->
        <div class="goup_nav">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <?php $itr_count = 0 ?>
                <?php foreach ($account_groups as $code => $name) : ?>
                <?php
					$itr_count++;
					?>
                <?php if ($itr_count == 1) : ?>
                <li class="nav-item active">
                    <a class="nav-link active" id="group<?php echo $code; ?>_tab" data-toggle="tab"
                        href="#group<?php echo $code; ?>" role="tab" aria-controls="group<?php echo $code; ?>"
                        aria-selected="true"><?php echo __($name); ?> </a>
                </li>
                <?php else : ?>
                <li class="nav-item">
                    <a class="nav-link" id="group<?php echo $code; ?>_tab" data-toggle="tab"
                        href="#group<?php echo $code; ?>" role="tab" aria-controls="group<?php echo $code; ?>"
                        aria-selected="true"><?php echo __($name); ?> </a>
                </li>
                <?php endif ?>
                <?php endforeach ?>
                <!-- <li class="nav-item">
		    <a class="nav-link" id="group02_tab" data-toggle="tab" href="#group02" role="tab" aria-controls="group02" aria-selected="false"><?php echo __("見込対予算増減一覧"); ?></a>
		  </li>
		  <li class="nav-item">
		    <a class="nav-link" id="group03_tab" data-toggle="tab" href="#group03" role="tab" aria-controls="group03" aria-selected="false"><?php echo __("月次業績報告"); ?></a>
		  </li>
		  <li class="nav-item">
		    <a class="nav-link" id="group04_tab" data-toggle="tab" href="#group04" role="tab" aria-controls="group04" aria-selected="false"><?php echo __("実績・対予算比較表"); ?></a>
		  </li>
		  <li class="nav-item">
		    <a class="nav-link" id="group05_tab" data-toggle="tab" href="#group05" role="tab" aria-controls="group05" aria-selected="false"><?php echo __("総括表"); ?></a>
		  </li> -->
            </ul>
            <div class="tab-content" id="myTabContent">
                <?php $itr_cnt = 0 ?>
                <?php foreach ($data_arr as $code => $sub_accs) : ?>
                <?php $itr_cnt++ ?>
                <?php if ($itr_cnt == 1) : ?>
                <div class="tab-pane fade active in" id="group<?php echo $code; ?>" role="tabpanel"
                    aria-labelledby="group<?php echo $code; ?>_tab">
                    <?php else : ?>
                    <div class="tab-pane fade" id="group<?php echo $code; ?>" role="tabpanel"
                        aria-labelledby="group<?php echo $code; ?>_tab">
                        <?php endif ?>

                        <?php if (!empty($sub_accs)) : ?>
                        <!-- || isset($sub_accs) -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="msgfont"><?php echo __("総行").' : ' . count($sub_accs) . ' '.__("行"); ?>
                                </div>
                                <div class="table-responsive tbl-wrapper">
                                    <table class="table table-striped table-bordered tbl_sumisho_inventory"
                                        id="sub_account">
                                        <thead>
                                            <tr>
                                                <th width="100px"><?php echo __("グループコード"); ?></th>
                                                <th width="100px"><?php echo __("勘定科目名(JP)"); ?></th>
                                                <th width="100px"><?php echo __("勘定科目名(ENG)"); ?></th>
                                                <th width="100px" colspan="2"><?php echo __("アクション"); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php

													$i = 1;
													foreach ($sub_accs as $row) {

													?>
                                            <tr>
                                                <td width="50px" class="align-left">
                                                    <?php echo h($row['BrmAccount']['group_code']); ?>
                                                </td>
                                                <td width="100px" class="align-left" style="word-break: break-all;">
                                                    <?php echo h($row['BrmAccount']['name_jp']); ?>
                                                </td>
                                                <td width="100px" class="align-left" style="word-break: break-all;">
                                                    <?php echo h($row['BrmAccount']['name_en']); ?>
                                                </td>
                                                <!-- <form class="form-horizontal"> -->

                                                <td width="100px" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
                                                    <input type="hidden" name="page" id="page2" value="" />
                                                    <a class="" href="#"
                                                        onclick="click_edit('<?php echo $row['BrmAccount']['id']; ?>');" title="<?php echo __('編集'); ?>"><i
                                                            class="fa-regular fa-pen-to-square" ></i>
                                                    </a>
                                                </td>
                                                <td width="100px" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">

                                                    <a class="delete_link" href="#" id="btn_submit_delete"
                                                        onclick="click_delete('<?php echo $row['BrmAccount']['id']; ?>');" title="<?php echo __('削除'); ?>"><i
                                                            class="fa-regular fa-trash-can" ></i>

                                                    </a>
                                                </td>
                                                <!-- </form> -->
                                            </tr>
                                            <?php
													}

													?>
                                        </tbody>

                                    </table>

                                </div>
                            </div>
                        </div>
                        <?php else : ?>
                        <div class="row">
                            <div class="col-sm-12">
                                <!-- <p class="no-data"><?php echo $no_data_group1; ?></p> -->
                                <p class="no-data"><?php echo 'Data does not exist in system.'; ?></p>
                            </div>
                        </div>
                        <?php endif ?>
                    </div>

                    <?php endforeach ?>
                </div>
            </div>
		</div>
	</div>
</form>
<!-- end nav tab -->

<br /><br /><br />
<script type="text/javascript">
$(document).ready(function() {

    /* float thead */
    if ($('table').length > 0) {
        $('table').floatThead({
            position: 'absolute'
        });
    }

    if ($(".tbl-wrapper").length) {
        $(".tbl-wrapper").floatingScroll();
    }
    /* end*/
    // document.getElementById("error").innerHTML = "";
	// document.getElementById("success").innerHTML = "";

});

function click_Save() {

    document.getElementById("error").innerHTML = "";
    document.getElementById("success").innerHTML = "";

    var g_code = document.getElementById("g_code").value;
    var name_jp = document.getElementById("name_jp").value;
    var name_en = document.getElementById("name_en").value;

    var chk = true;


    if (!checkNullOrBlank(g_code)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("グループコード") ?>'])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    if (!checkNullOrBlank(name_jp)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("勘定科目名(JP)") ?>'])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    // get_page("page1");


    if (chk) {
        $.confirm({
            title: '<?php echo __("保存確認"); ?>',
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
                    text: '<?php echo __("はい"); ?>',
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action = "<?php echo $this->webroot; ?>BrmAccounts/saveData";
                        document.forms[0].method = "POST";
                        document.forms[0].submit();

                        return true;
                    }
                },
                cancel: {
                    text: '<?php echo __("いいえ"); ?>',
                    btnClass: 'btn-default',
                    cancel: function() {
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
/**
	@author aznk
	click_Update
 */
function click_Update() {


    document.getElementById("error").innerHTML = "";
    document.getElementById("success").innerHTML = "";

    var g_code = document.getElementById("g_code").value;
    var name_jp = document.getElementById("name_jp").value;
    var name_en = document.getElementById("name_en").value;

    var chk = true;
    //put group code to hidden input text
    document.getElementById('hid_g_code').value = g_code;


    if (!checkNullOrBlank(name_jp)) {
        var newbr = document.createElement("div");
        var a = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("勘定科目名 (JP)") ?>'])));
        document.getElementById("error").appendChild(a);
        chk = false;
    }

    // get_page("page1");
    var path = window.location.pathname;
    var page = path.split("/").pop();
    console.log(page);
    if (page.indexOf("page:") !== -1) {
        document.getElementById('hid_page_no').value = page;
    }

    if (chk) {
        $.confirm({
            title: '<?php echo __("保存確認"); ?>',
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
                    text: '<?php echo __("はい"); ?>',
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action = "<?php echo $this->webroot; ?>BrmAccounts/updateData";
                        document.forms[0].method = "POST";
                        document.forms[0].submit();

                        return true;
                    }
                },
                cancel: {
                    text: '<?php echo __("いいえ"); ?>',
                    btnClass: 'btn-default',
                    cancel: function() {
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


function click_edit(id) {
    document.getElementById('error').innerHTML = '';
    document.getElementById('success').innerHTML = '';


    $.ajax({
        type: "POST",
        url: "<?php echo $this->webroot; ?>BrmAccounts/editData",
        data: {
            id: id
        },
        dataType: "json",
        beforeSend: function() {
            loadingPic();
        },
        success: function(data) {
            console.log(data)

            var id_vl = data['id_vl'];
            var g_code = data['group_code'];
            var name_jp = data['name_jp'];
            var name_en = data['name_en'];
            $("#id_vl").val(id_vl);
            $('#g_code').attr("disabled", true);
            $('#g_code option[value="' + g_code + '"]').prop('selected', true);
            $("#name_jp").val(name_jp);
            $("#name_en").val(name_en);
            $('#overlay').hide();
            $("#btn_update_account").show();
            $("#btn_save_account").hide();


        },

    });

}

function click_delete(id) {

    // document.getElementById('error').innerHTML = '';
    // document.getElementById('success').innerHTML = '';
    document.getElementById('deleteItem').value = id;
    // get_page("page2");

    $.confirm({
        title: '<?php echo __("削除確認"); ?>',
        icon: 'fas fa-exclamation-circle',
        type: 'red',
        typeAnimated: true,
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
                    document.forms[0].action = "<?php echo $this->webroot; ?>BrmAccounts/deleteData";
                    document.forms[0].submit();

                    return true;
                }
            },
            cancel: {
                text: '<?php echo __("いいえ"); ?>',
                btnClass: 'btn-default',
                cancel: function() {
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
</script>