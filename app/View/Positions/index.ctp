<?php
if (!empty($search_year)) {
    $search_year = $search_year;
} else {
    $search_year = '';
}
echo $this->Form->create(false, array('type' => 'post', 'id' => '', 'enctype' => 'multipart/form-data'));
?>
<style>
    body {
        font-family: KozGoPro-Regular;
    }

    .yearPicker table {
        /*z-index: 1999 !important;*/
        width: 250px !important;
    }

    /* .yearPicker table tr td span.active.active {
        background-color: #f09282 !important;
        background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#f09282), to(#f09282));
        background-image: -webkit-linear-gradient(top, #f09282, #f09282);
    } */

    .numbers {
        text-align: right !important;
    }

    .popup_row {
        padding-bottom: 30px;
    }

    #year {
        display: inline-block;
        width: 200px;
        vertical-align: middle;
    }

    #overwrite {
        display: none;
    }

    div.bootstrap-datetimepicker-widget {
        z-index: 2000000;
    }

    #myModal .modal-body,
    #myModal .modal-body .modal_tbl_wrapper {
        overflow-y: visible;
    }

    .btn_sumisho {
        height: 3.5rem;
    }
    .btn_sumisho:hover, .btn.btn-save{
        color:white;
    }
    .table {
        margin: 0;
    }
</style>

<script>
    $(document).ready(function() {
        document.getElementById("hid_search").value = "SEARCHALL";
        /* float table header */
        if ($('#tbl_position').length > 0) {
            var $table = $('#tbl_position');
            $table.floatThead({
                responsiveContainer: function($table) {
                    return $table.closest('.table-responsive');
                }
            });
        }

        $(".yearpicker").datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years",
            autoclose: true,
        });
        //year picker in modal show under modal bottom line, so custom id and custom option
        $('#picker-in-modal').datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years",
            autoclose: true,
            container: '#myModal',
        });

        // if have search year value , show copy button
        var search_year = "<?php echo $search_year; ?>";
        if (search_year != '') {
            $('#copy').prop('disabled', false);
        }

        $("#to_year").on('change', function() {
            var to_year = $('#to_year').val();
            if (to_year) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->webroot; ?>Positions/getToYearData",
                    data: {
                        copy_year: to_year
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        loadingPic();
                    },

                    success: function(data) {
                        if (data.response_year) {
                            $('#overwrite').show();
                        } else {
                            $('#overwrite').hide();
                        }
                        $('#overlay').hide();
                    }
                });

            } else {
                $('#overwrite').hide();
            }

        });
    });

    function validateFormData() {

        document.getElementById("error").innerHTML = "";
        document.getElementById("success").innerHTML = "";
        var target_year = document.getElementById("target_year").value;
        var position_type = document.getElementById("position_type").value;
        var position_name = document.getElementById("position_name").value;
        var personnel_cost = document.getElementById("personnel_cost").value;
        var corporate_cost = document.getElementById("corporate_cost").value;
        var chk = true;
        if (!checkNullOrBlank(target_year)) {
            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo ucfirst(__('年度')) ?>'])));
            document.getElementById("error").appendChild(a);
            chk = false;
        }
        if (!checkNullOrBlank(position_type)) {
            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("役職種類") ?>'])));
            document.getElementById("error").appendChild(a);
            chk = false;
        }
        if (checkNullOrBlank(personnel_cost)) {
            /* allow only 12 digits with 2 decimal place */
            var decimalOnly = /^\s*(\d{1,10})(\.\d{0,2})?\s*$/;
            if (!decimalOnly.test(personnel_cost)) {
                var newbr = document.createElement("div");
                var a = document.getElementById("error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE047, ["<?php echo __('人件費') ?>"])));
                document.getElementById("error").appendChild(a);
                chk = false;
            }
        }
        if (checkNullOrBlank(corporate_cost)) {
            /* allow only 12 digits with 2 decimal place */
            var decimalOnly = /^\s*(\d{1,10})(\.\d{0,2})?\s*$/;
            if (!decimalOnly.test(corporate_cost)) {
                var newbr = document.createElement("div");
                var a = document.getElementById("error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE047, ["<?php echo __('コーポレート 経費割当') ?>"])));
                document.getElementById("error").appendChild(a);
                chk = false;
            }
        }
        if (!checkNullOrBlank(position_name)) {
            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("役職名") ?>'])));
            document.getElementById("error").appendChild(a);
            chk = false;
        }
        return chk;
    }

    function click_SavePosition() {
        var chk = validateFormData();

        if (chk) {
            $.confirm({
                title: '<?php echo __("保存確認"); ?>',
                icon: 'fas fa-exclamation-circle',
                type: 'green',
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
                                "<?php echo $this->webroot; ?>Positions/savePositionData";
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

    function Click_EditPosition(id) {
        document.getElementById("error").innerHTML = '';
        document.getElementById("success").innerHTML = '';
        $.ajax({
            type: "POST",
            url: "<?php echo $this->webroot; ?>Positions/editPositionData",
            data: {
                id: id
            },
            dataType: 'json',
            beforeSend: function() {
                loadingPic();
            },

            success: function(data) {
                let {
                    id,
                    target_year,
                    position_code,
                    position_name,
                    personnel_cost,
                    corporate_cost,
                    edit_flag,
                    position_type,
                    field_name_jp
                } = data;

                $('#hid_updateId').val(id);
                $('#target_year').val(target_year);
                $('#position_name').val(position_name);
                $('#personnel_cost').val(personnel_cost);
                $('#corporate_cost').val(corporate_cost);
                if (edit_flag == 1) {
                    $('#edit_flag').attr('checked', true);
                } else {
                    $('#edit_flag').attr('checked', false);
                }
                $('#position_type option[value="' + position_type + '"]').prop('selected', true);
                $('#btn_save').hide();
                $('#btn_update').show();
                $('#overlay').hide();
            }
        });
    }

    function click_UpdatePosition() {

        var path = window.location.pathname;
        var page = path.split("/").pop();
        document.getElementById('hid_page_no').value = page;
        var chk = validateFormData();

        if (chk) {
            $.confirm({
                title: '<?php echo __("変更確認"); ?>',
                icon: 'fas fa-exclamation-circle',
                type: 'green',
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
                                "<?php echo $this->webroot; ?>Positions/updatePositionData";
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
                closeAnimation: 'rotateXR',

            });
        }
        scrollText();
    }

    function Click_DeletePosition(id) {
        document.getElementById("error").innerHTML = '';
        document.getElementById("success").innerHTML = '';
        document.getElementById("hid_deleteId").value = id;
        var path = window.location.pathname;
        var page = path.split("/").pop();
        document.getElementById('hid_page_no').value = page;
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
            content: errMsg(commonMsg.JSE017),
            buttons: {
                ok: {
                    text: '<?php echo __("はい"); ?>',
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action = "<?php echo $this->webroot; ?>Positions/deletePositionData";
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
        scrollText();
    }

    function SearchData() {

        document.getElementById("error").innerHTML = "";
        document.getElementById("success").innerHTML = "";
        var year = document.getElementById("year").value;

        // document.getElementById("hid_search").value = year ? '' : 'SEARCHALL';
        var query = '';
        if (year) {
            query = '?year=' + year;
        }
        document.forms[0].action = "<?php echo $this->webroot; ?>Positions" + query;
        document.forms[0].method = "POST";
        document.forms[0].submit();
        $("html, body").animate({
            scrollTop: 10
        }, "fast");
    }

    function popupscreen() {

        var target_year = document.getElementById('year').value;

        // $('#to_year').val('');
        $('#to_year option').removeAttr('selected').filter('[value=""]').attr('selected', true);
        $('#popuperror').hide();
        $('#overwrite').hide();

        if (target_year != '') {
            $('#from_year').val(target_year);
        }

    }

    function CopyData() {
        $('#popuperror').show();
        var from_year = document.getElementById('from_year').value;
        var to_year = document.getElementById('to_year').value;


        document.getElementById("popupsuccess").innerHTML = "";
        document.getElementById("popuperror").innerHTML = "";
        var chk = true;
        //number check		
        if (!checkNullOrBlank(to_year)) {
            var newbr = document.createElement("div");
            var a = document.getElementById("popuperror").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("コピー先（年）") ?>'])));
            document.getElementById("popuperror").appendChild(a);
            chk = false;
        }
        //year value and copy_year value is same
        if (checkNullOrBlank(from_year) && checkNullOrBlank(to_year)) {
            if (from_year == to_year) {
                var newbr = document.createElement("div");
                var a = document.getElementById("popuperror").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE060, [
                    '<?php echo __("コピーするデータは同じであってはなりません") ?>'
                ])));
                document.getElementById("popuperror").appendChild(a);
                chk = false;
            }
        }
        if (chk) {
            $("#popuperror").css("display", "none");
            $.confirm({
                title: '<?php echo __("コピー確認"); ?>',
                icon: 'fas fa-exclamation-circle',
                type: 'green',
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
                            document.forms[0].action = "<?php echo $this->webroot; ?>Positions/CopyPositions";
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

    }

    function OverwirteData() {
        $('#popuperror').show();
        var from_year = document.getElementById('from_year').value;
        var to_year = document.getElementById('to_year').value;

        document.getElementById("popupsuccess").innerHTML = "";
        document.getElementById("popuperror").innerHTML = "";
        var chk = true;

        if (!checkNullOrBlank(to_year)) {
            var newbr = document.createElement("div");
            var a = document.getElementById("popuperror").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("コピー年度") ?>'])));
            document.getElementById("popuperror").appendChild(a);
            chk = false;
        }
        //year value and copy_year value is same
        if (checkNullOrBlank(from_year) && checkNullOrBlank(to_year)) {
            if (from_year == to_year) {
                var newbr = document.createElement("div");
                var a = document.getElementById("popuperror").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE060, [
                    '<?php echo __("コピーするデータは同じであってはなりません") ?>'
                ])));
                document.getElementById("popuperror").appendChild(a);
                chk = false;
            }
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
                                "<?php echo $this->webroot; ?>Positions/OverwirteDataCopy";
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

    }
    /*  
     *	Show hide loading overlay
     *	@Zeyar Min  
     */
    function loadingPic() {
        $("#overlay").show();
        $('.jconfirm').hide();
    }

    /* when show err msg or succ msg, scroll is top of the page */
    function scrollText() {
        var tes = $('#error').text();
        var tes1 = $('.success').text();
        if (tes) {
            $("html, body").animate({
                scrollTop: 20
            }, "fast");
        }
        if (tes1) {
            $("html, body").animate({
                scrollTop: 20
            }, "fast");
        }
    }
</script>
<div id="overlay">
    <span class="loader"></span>
</div>
<div class="content">
    <div class="register_form">
        <fieldset class="form-inline">
            <div class="col-md-12 col-sm-12 heading_line_title" style="padding: 0px;">
                <legend><?php echo __('人員単価管理'); ?></legend>
            </div>
            <!-- for update -->
            <input type="hidden" name="hid_updateId" id="hid_updateId">
            <!-- for delete -->
            <input type="hidden" name="hid_deleteId" id="hid_deleteId">
            <!-- for selected text(term_name) -->
            <input type="hidden" name="selected_data" id="selected_data">
            <!-- for page no. -->
            <input type="hidden" name="hid_page_no" id="hid_page_no">
            <!-- for update/delete condition to get search year value -->
            <input type="hidden" name="hid_search" id="hid_search">

            <!-- show error msg and success msg -->
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="success" id="success"><?php echo ($this->Session->check("Message.PositionsOK")) ? $this->Flash->render("PositionsOK") : ''; ?><?php echo ($this->Session->check("Message.PositionsOK")) ? $this->Flash->render("PositionsOK") : ''; ?></div>
                <div class="error" id="error"><?php echo ($this->Session->check("Message.PositionsFail")) ? $this->Flash->render("PositionsFail") : ''; ?><?php echo ($this->Session->check("Message.PositionsFail")) ? $this->Flash->render("PositionsFail") : ''; ?></div>
            </div>

            <!-- field group -->
            <div class="form-row">
                <!--form group 1-->
                <div class="form-group col-md-6">
                    <label for="target_year" class="required control-label">
                        <?php echo ucfirst(__('年度')); ?>
                    </label>
                    <!-- <div class="input-group date form_input datepicker" data-provide="datepicker" style="padding:0px;"> -->
                    <div class="input-group form_input date yearPicker" data-provide="yearPicker" style="padding:0px;">
                        <input type="text" class="form-control yearPicker" id="target_year" name="target_year" style="background-color: #fff;" readonly>
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
                <!--form group 2-->
                <div class="form-group col-md-6">
                    <label for="field_name" class="control-label required">
                        <?php echo __('役職種類'); ?>
                    </label>
                    <select id="position_type" name="position_type" class="form-control form_input">
                        <option value="">----- Select Job Type -----</option>
                        <?php foreach (PositionType::Types as $index => $field_name) : ?>
                            <option value="<?= $index ?>"><?= $field_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!--form group 3-->
                <div class="form-group col-md-6">
                    <label for="position_name" class="control-label required">
                        <?php echo __('役職名'); ?>
                    </label>
                    <input type="text" class="form-control form_input" id="position_name" name="position_name" value="" maxlength="250" />
                </div>
                <!--form group 4-->
                <div class="form-group col-md-6">
                    <label for="unit_salary" class="control-label">
                        <?php echo __('人件費'); ?>
                    </label>
                    <input type="text" class="form-control form_input" id="personnel_cost" name="personnel_cost" value="" maxlength="250" />

                </div>
                <!--form group 5-->
                <div class="form-group col-md-6">
                    <label for="display_no" class="control-label">
                        <?php
                        echo __(
                            'コーポレート 経費割当'
                        );
                        ?>
                    </label>
                    <input type="text" class="form-control form_input" id="corporate_cost" name="corporate_cost" value="" maxlength="250" />
                </div>
            </div>

            <!-- <div class="form-group row">
				<div class="col-sm-6">
					<label for="edit_flag" class="col-sm-4 col-form-label">
						<?php echo __('単価入力許可'); ?>
					</label>
					<div class="col-sm-8">
						<input type="checkbox" id="edit_flag" name="edit_flag" class="chk_editflag">
					</div>
				</div>-->
            <!-- btn group -->
            <div class="form-group col-md-6" style="margin-bottom: 40px;">
                <div class="col-md-10 col-sm-12" id="save">
                    <input type="button" class="btn-save pull-right" id="btn_save" name="btn_save" value="<?php echo __('保存'); ?>" onclick="click_SavePosition();">

                    <input type="button" class="btn-save pull-right" id="btn_update" name="btn_update" style="display: none;" value="<?php echo __('変更'); ?>" onclick="click_UpdatePosition();">
                </div>
            </div>
        </fieldset>
    </div>

    <!-- show total row count -->
    <?php if (!empty($succmsg)) { ?>
        <!-- search and copy -->
        <table width="100%">
            <tr>
                <td valign="bottom">
                    <div class="pull-left msgfont" id="succc" style="padding-left:15px">
                        <span>
                            <?php echo ($succmsg); ?>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="pull-right align-top" style="padding-right: 15px">
                        <select class="form-control" id="year" name="year" value="">
                            <option value="">----- Select Year -----</option>
                            <?php foreach ($years as $key => $year) : ?>
                                <?php
                                $year = $year['Position']['target_year'];
                                if (!empty($search_year)) {
                                    if ($search_year == $year) {
                                        $select = 'selected';
                                    } else {
                                        $select = '';
                                    }
                                }
                                ?>
                                <option value="<?= $year ?>" <?= $select ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="button" class="btn-save" value="<?php echo __('検索'); ?>" name="search" onclick="SearchData();">
                        <input type="button" data-target="#myModal" data-toggle="modal" data-backdrop="static" data-keyboard="false" class="btn btn-save" value="<?php echo __('コピー'); ?>" name="copy" id="copy" onclick="popupscreen();" disabled>
                    </div>
                </td>
            </tr>
        </table>

    <?php } else if (!empty($errmsg)) { ?>
        <div id="err" class="no-data"> <?php echo ($errmsg); ?></div>
    <?php } ?>
    <!-- show list table -->
    <?php if ($rowCount != 0) { ?>
        <div class="table-responsive content">
            <table class="table table-bordered table-container" id="tbl_position" style="width: 100%;white-space: unset;">
                <thead class="check_period_table">
                    <tr>
                        <th><?php echo __("年度"); ?></th>
                        <th><?php echo __("役職種類"); ?></th>
                        <th><?php echo __("役職名"); ?></th>
                        <th><?php echo __("人件費(単位：千円）"); ?></th>
                        <th><?php echo __("コーポレート経費割当(単位：千円）"); ?></th>
                        <th><?php echo __("年間人件費"); ?></th>
                        <th><?php echo __("年間割掛"); ?></th>
                        <!-- <th><?php echo __("単価入力許可"); ?></th> -->
                        <th colspan="2"><?php echo __("アクション") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($list)) foreach ($list as $datas) {
                        $id = $datas['Position']['id'];
                        $target_year = $datas['FieldModel']['target_year'];
                        $field_name_jp = PositionType::Types[$datas['Position']['position_type']];
                        $target_year = $datas['Position']['target_year'];
                        $position_name = $datas['Position']['position_name'];
                        $personnel_cost = $datas['Position']['personnel_cost'];
                        $corporate_cost = $datas['Position']['corporate_cost'];
                        $annual_salary_labor = $datas['Position']['annual_salary_labor'];
                        $annual_salary_corporate = $datas['Position']['annual_salary_corporate'];

                        $edit_flag = $datas['Position']['edit_flag'];
                        if ($edit_flag == 1) {
                            $edit_flag = 'ON';
                        } else {
                            $edit_flag = 'OFF';
                        }
                        $flag = $datas['Position']['flag'];

                        if ($flag != 0) { ?>
                            <tr style="text-align: left;">
                                <td><?php echo $target_year; ?></td>
                                <td width="150px"><?php echo $field_name_jp; ?></td>
                                <td width="300px"><?php echo $position_name; ?></td>
                                <td class="numbers"><?php echo number_format($personnel_cost, 2); ?></td>
                                <td class="numbers"><?php echo number_format($corporate_cost, 2); ?></td>
                                <td class="numbers"><?php echo number_format($personnel_cost * 12, 2); ?></td>
                                <td class="numbers"><?php echo number_format($corporate_cost * 12, 2); ?></td>
                                <!-- <td><?php echo $edit_flag; ?></td> -->
                                <td width="65px" class="link" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
                                    <a class="" href="#" onclick="Click_EditPosition('<?php echo $id; ?>');" title="<?php echo __('編集'); ?>"><i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                </td>
                                <td width="65px" class="link" style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;">
                                    <a class="" href="#" onclick="Click_DeletePosition('<?php echo $id; ?>');" title="<?php echo __('削除'); ?>"><i class="fa-regular fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <!-- show pagination -->
    <div class="row" style="clear:both;margin-bottom: 50px;">
        <?php if ($rowCount > Paging::TABLE_PAGING) { ?>
            <div class="col-sm-12" style="padding: 10px;text-align: center;">
                <div class="paging">
                    <?php
                    echo $this->Paginator->first('<<');
                    echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev disabled'));
                    echo $this->Paginator->numbers(array('separator' => '', 'modulus' => 6));
                    echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
                    echo $this->Paginator->last('>>');
                    ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<!-- PopUpBox  -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content contantbond">
            <div class="modal-header">
                <button type="button" class="close" id="clearData" data-dismiss="modal">&times;</button>
                <h3 class="modal-title"><?php echo __("コピー確認"); ?></h3>
            </div>
            <div class="modal-body">
                <!-- success,error -->
                <div class="success" id="popupsuccess"></div>
                <div class="error" id="popuperror"></div>
                <!-- end success,error -->
                <div class="table-responsive modal_tbl_wrapper">
                    <div class="col-md-12">
                        <div class="form-group popup_row">
                            <label class="col-md-4 control-label rep_lbl"><?php echo __("コピー元（年）"); ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="from_year" value="<?= $target_year ?>" name="from_year" disabled>
                                <input type="hidden" name="hid_form_year" value="<?= $target_year ?>">
                            </div>
                        </div>

                        <div class="form-group popup_row">
                            <label class="col-md-4 control-label rep_lbl required"><?php echo __("コピー先（年）"); ?></label>
                            <div class="col-sm-8">
                                <div class="input-group date" id="picker-in-modal" data-provide="yearPicker" style="padding: 0px;">
                                    <input type="text" class="form-control" id="to_year" name="to_year">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="term_copy" onclick="CopyData()" class="btn-save"><?php echo __('追加'); ?> </button>

                <button type="button" id="overwrite" onclick="OverwirteData()" class="btn-save"><?php echo __('上書き'); ?> </button>
            </div>
        </div>
    </div>
</div>
<!-- end popup -->
<?php
echo $this->Form->end();
?>