<style>
    #update {
        /* display: none; */
    }
</style>
<div id="overlay">
    <span class="loader"></span>
</div>
<?php

use Zend\Cache\Storage\Adapter\Session;

echo $this->Form->create(false, array('type' => 'post', 'id' => '', 'enctype' => 'multipart/form-data'));
?>
<div class="content">
    <div class="register_form">

        <div style="display:none;">
            <input type="hidden" name="_method" value="POST" />
            <input type="hidden" name="id" id="id" />
            <input type="hidden" name="hiddenDeletedId" id="hiddenDeletedId" />
            <input type="hidden" name="hiddenRefTerm" id="hiddenRefTerm">
            <input type="hidden" name="hiddenPageNo" id="hiddenPageNo" />
            <input type="hidden" name="hiddenTotalPageCount" value="<?= intval($this->Paginator->counter('{:pages}')) ?>" id="hiddenTotalPageCount" />
            <input type="hidden" name="hiddenRecordCount" value="<?= $this->Paginator->params()['current']; ?>" id="hiddenRecordCount" />
            <input type="hidden" name="hiddenTotalRecordCount" value="">
        </div>

        <fieldset class="form-inline">
            <legend><?php echo __('期間管理'); ?></legend>
            <div class="errorSuccess">
                <div class="success" id="success"><?php echo $this->Flash->render("buterm_success") ?><?php echo ($this->Session->check("Message.BUTermsSuccess")) ? $this->Flash->render("BUTermsSuccess") : ''; ?><?php echo ($this->Session->check("Message.BUTermsSuccess")) ? $this->Flash->render("BUTermsSuccess") : ''; ?></div>
                <div class="error" id="error"><?php echo $this->Flash->render("buterm_error"); ?><?php echo ($this->Session->check("Message.BUTermsFail")) ? $this->Flash->render("BUTermsFail") : ''; ?><?php echo ($this->Session->check("Message.BUTermsFail")) ? $this->Flash->render("BUTermsFail") : ''; ?></div>
            </div>

            <div class="form-row">
                <!-- Form Group 1 -->
                <div class="form-group col-md-6">
                    <label class="required control-label" for="term_name"><?php echo __("期間名"); ?></label>
                    <!-- create and update-->
                    <!-- added value to prevent injections -->
                    <input class="form-control form_input" type="text" id="term_name" name="term_name" value="<?php echo h($term_name); ?>"/>
                </div>
                <!-- Form Group 2 -->
                <div class="form-group col-md-6">
                    <label class="control-label required" for="budget_year"><?php echo __("年度"); ?></label>
                    <div class="input-group date form_input datepicker" id="datepicker" data-provide="datepicker" style="padding:0px;">
                        <input type="text" class="form-control" id="budget_year" name="budget_year" value="" autocomplete="off" style="background-color: #fff;" readonly />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                    <input type="text" class="form-control form_input" id="update_budget_year" name="update_budget_year" value="" autocomplete="off" readonly />
                </div>
                <!-- Form Group 3 -->
                <div class="form-group col-md-6">
                    <label class="control-label required" for="start_month"><?php echo __("開始月"); ?></label>

                    <select class="form-control form_input" type="text" id="start_month" name="start_month" value="">
                        <option value=""><?php echo __("----- Select Start Month -----"); ?></option>
                        <?php
                        foreach ($startMonth as $key => $value) :
                        ?>
                            <option value="<?php echo $key; ?>">
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach ?>
                    </select>

                </div>
                <!-- Form Group 4 -->
                <div class="form-group col-md-6">
                    <label class="control-label" for="ref_term"><?php echo __("参照期間"); ?></label>
                    <!-- create and update-->
                    <select class="form-control form_input" type="text" id="ref_term" name="ref_term" value="" onchange="">
                        <option value=0><?php echo __("--- Select Reference Term ---"); ?></option>
                        <?php foreach ($ref_term as $key => $value) : ?>
                            <option value="<?php echo $value['BuTerm']['id']; ?>">
                                <?php echo $value['BuTerm']['term_name']; ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <!-- Form Group 5 -->
                <div class="form-group col-md-6">
                </div>
                <!-- Form Group 6 -->
                <div class="form-group col-md-6">
                    <div class="submit btn-save-wpr" id="save" style="width: 81%">
                        <input onclick="saveTerm();" type="button" value="<?php echo __("保存"); ?>" class="btn-save pull-right" />
                    </div>
                    <!-- update -->
                    <div class="submit btn-save-wpr d-flex justify-content-end" id="update" style="width: 81%">
                        <input type="hidden" name="update_id" id="update_id" value="">
                        <input onclick="updateTerm();" type="button" value="<?php echo __("変更"); ?>" class="btn-save update-btn pull-right" />
                    </div>
                </div>
            </div>

        </fieldset>
    </div>
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
        </tr>
    </table>

<?php } else if (!empty($errmsg)) { ?>
    <div id="err" class="no-data"> <?php echo ($errmsg); ?></div>
<?php } ?>

<!-- show list table -->
<?php if (!empty($termsList)) { ?>
    <div class="table-responsive content" style="margin-bottom: 2rem;">
        <table class="table table-bordered table-container" id="tbl_user" style="white-space: unset;">
            <thead>
                <tr style=" vertical-align: middle;">
                    <th><?php echo __("期間名"); ?></th>
                    <th><?php echo __("年度"); ?></th>
                    <th><?php echo __("開始月"); ?></th>
                    <th><?php echo __("終了月"); ?></th>
                    <th><?php echo __("参照期間"); ?></th>
                    <th colspan="2" style="max-width: 12rem;"><?php echo __("アクション"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($termsList as $result) :
                    $start_month_num = $result['BuTerm']['start_month'];
                    $end_month_num = $result['BuTerm']['end_month'];
                ?>
                    <tr>
                        <td style="vertical-align:middle;"><?= h($result['BuTerm']['term_name']) ?></td>
                        <td style="vertical-align:middle;"><?= h($result['BuTerm']['budget_year']) ?></td>
                        <td style="vertical-align:middle;"><?= h($startMonth[$start_month_num]) ?></td>
                        <td style="vertical-align:middle;"><?= h($startMonth[$end_month_num]) ?></td>
                        <td style="vertical-align:middle;"><?= h($result['RefTermName']['ref_term']) ?></td>
                        <td style="word-break: break-all;text-align: center;vertical-align:middle;font-size:1.3em !important;" class='edit'>
                            <a class="" href="#" onclick="editTerm(<?= h($result['BuTerm']['id']) ?>)" title='<?php echo __("編集"); ?>'><i class="fa-regular fa-pen-to-square"></i>
                            </a>
                        </td>
                        <td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='remove'>
                            <a class="" href="#" onclick="deleteTerm(<?= h($result['BuTerm']['id']) ?>)" title='<?php echo __("削除"); ?>'><i class="fa-regular fa-trash-can"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php } ?>

<!-- show pagination -->
<div class="row" style="clear:both;margin: 2rem 0px;">
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
<?php
echo $this->Form->end();
?>


<script type="text/javascript">

    $(document).ready(function() {
        
        $(window).on('beforeunload', () => {
			loadingPic()
		});

        $('#update').hide();
        $('#update_budget_year').hide();
        $("#datepicker").datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years",
            autoclose: "true"
        });
    });

    function saveTerm() {

        document.querySelector("#error").innerHTML = "";
        document.querySelector("#success").innerHTML = "";
        let path = window.location.pathname;
        let page = path.split("/").pop();
        document.getElementById('hiddenPageNo').value = page;
        let error = true;
        let termName = document.querySelector("#term_name").value;
        let budgetYear = document.querySelector("#budget_year").value;
        let startMonth = document.querySelector("#start_month").value;
        let ref_term = document.querySelector("#ref_term").value;
        document.querySelector("#hiddenRefTerm").value = ref_term;

        if (!checkNullOrBlank(termName)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("期間名"); ?>'])));
            document.querySelector("#error").appendChild(a);
            error = false;
        }
        if (!checkNullOrBlank(budgetYear)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("年度"); ?>'])));
            document.querySelector("#error").appendChild(a);
            error = false;
        }
        if (!checkNullOrBlank(startMonth)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("開始月"); ?>'])));
            document.querySelector("#error").appendChild(a);
            error = false;
        }

        if (error) {
            $.confirm({
                title: '<?php echo __("保存期間"); ?>',
                icon: 'fas fa-exclamation-circle',
                type: 'green',
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
                            document.forms[0].action = "<?php echo $this->webroot; ?>BuTerms/saveUpdateTerm";
                            document.forms[0].method = "POST";
                            document.forms[0].submit();
                            loadingPic();
                            return true;
                        }
                    },
                    cancel: {
                        text: '<?php echo __("いいえ"); ?>',
                        btnClass: 'btn-default',
                        cancel: function() {
                            //console.log('the user clicked cancel'); 
                            scrollText();
                        }

                    },
                },
                theme: 'material',
                animation: 'rotateYR',
                closeAnimation: 'rotateXR'
            });
        }
    }

    function updateTerm() {

        document.querySelector("#error").innerHTML = "";
        document.querySelector("#success").innerHTML = "";
        let path = window.location.pathname;
        let page = path.split("/").pop();
        document.getElementById('hiddenPageNo').value = page;

        let error = true;
        let termName = document.querySelector("#term_name").value;
        let startMonth = document.querySelector("#start_month").value;
        let budgetYear = document.querySelector("#update_budget_year").value;
        let ref_term = document.querySelector("#ref_term").value;
        document.querySelector("#hiddenRefTerm").value = ref_term;

        if (!checkNullOrBlank(termName)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("期間名"); ?>'])));
            document.querySelector("#error").appendChild(a);
            error = false;
        }
        if (!checkNullOrBlank(startMonth)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("開始月"); ?>'])));
            document.querySelector("#error").appendChild(a);
            error = false;
        }
        if (!checkNullOrBlank(budgetYear)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("年度"); ?>'])));
            document.querySelector("#error").appendChild(a);
            error = false;
        }

        if (error) {

            $.confirm({
                title: '<?php echo __("変更期間"); ?>',
                icon: 'fas fa-exclamation-circle',
                type: 'green',
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
                            document.forms[0].action = "<?php echo $this->webroot; ?>BuTerms/saveUpdateTerm";
                            document.forms[0].method = "POST";
                            document.forms[0].submit();
                            loadingPic();
                            return true;
                        }
                    },
                    cancel: {
                        text: '<?php echo __("いいえ"); ?>',
                        btnClass: 'btn-default',
                        cancel: function() {
                            //console.log('the user clicked cancel'); 
                            scrollText();
                        }

                    },
                },
                theme: 'material',
                animation: 'rotateYR',
                closeAnimation: 'rotateXR'
            });
        }
    }

    function editTerm(id) {
        document.getElementById("error").innerHTML = '';
        document.getElementById("success").innerHTML = '';
        $.ajax({
            type: "POST",
            url: "<?php echo $this->webroot; ?>BuTerms/getTerm",
            data: {
                id: id
            },
            dataType: 'json',
            beforeSend: function() {
                loadingPic();
            },
            success: function(data) {

                // destructure the data object
                let {
                    id,
                    term_name,
                    budget_year,
                    start_month,
                    ref_term,
                    term_id,
                    terms,
                    flag
                } = data;


                // this situation when response data is empty we show error message for a while and refresh the page
                if (!term_id) {

                    setTimeout(function() {
                        location.reload(true);
                    }, 1500);

                    $("#error").text("Data is already deleted!");

                }

                $('#update_id').val(id);
                $('#ref_term').empty();
                $('#term_name').val(term_name);
                $('#budget_year').val(budget_year);
                $('#update_budget_year').val(budget_year);
                $('#start_month option[value="' + start_month + '"]').prop('selected', true);


                // when other 10 tables are referencing that term_id and records exists, disable ref_term_id updating
                if (flag) {

                    $('#ref_term').prop('disabled', true);
                    $('#ref_term').empty();

                    let ref_term_id = parseInt(ref_term);

                    if (!ref_term_id) {
                        
                        // ref term_id is zero and child records are present
                        var option = $("<option></option>");
                        option.val(0).text("--- Select Reference Term ---")
                        $('#ref_term').append(option)

                    } else { // ref term_id is not zero and child records are present

                        var option = $("<option></option>");

                        let actual_ref_term_name = terms.filter((element) => {
                            return element.BuTerm.id == ref_term_id;
                        });

                        let ref_term_name = actual_ref_term_name[0].BuTerm.term_name;

                        option.val(ref_term_id).text(ref_term_name);

                        $("#ref_term").append(option);
                    }
                } else { // this condition happens, when there is no child records referencing term_id

                    $('#ref_term').prop('disabled', false);
                    $('#ref_term').empty();

                    let ref_term_id = parseInt(ref_term);

                    var option = $("<option></option>");
                    option.val(0).text("--- Select Reference Term ---")
                    $('#ref_term').append(option)

                    if (!ref_term_id) { // ref term_id is zero

                        // make dummy option 
                        // <option> --- Select Reference Term --- </option>

                        $.each(terms, function(index, value) {

                            let newOption = $("<option></option>");

                            newOption.val(value.BuTerm.id).text(value.BuTerm.term_name);

                            $("#ref_term").append(newOption);
                        });

                    } else { // ref term_id is not zero 

                        $.each(terms, function(index, value) {

                            let newOption = $("<option></option>");

                            //console.log(value.BuTerm.id);

                            if (ref_term_id == value.BuTerm.id) {

                                newOption.val(value.BuTerm.id).text(value.BuTerm.term_name).prop("selected", true);

                            } else {

                                newOption.val(value.BuTerm.id).text(value.BuTerm.term_name);

                            }

                            $("#ref_term").append(newOption);
                        });

                        // var option = $("<option></option>");

                        // let actual_ref_term_name = terms.filter((element) => {
                        //     return element.BuTerm.id == ref_term_id;
                        // });

                        // let ref_term_name = actual_ref_term_name[0].BuTerm.term_name;

                        // option.val(ref_term_id).text(ref_term_name);

                        // $("#ref_term").append(option);
                    }
                }

                $('#update_budget_year').show();
                $('#datepicker').hide();
                $('#save').hide();
                $('#update').show();
                $('#overlay').hide();
            }
        });
    }

    function deleteTerm(id) {
        document.getElementById("error").innerHTML = '';
        document.getElementById("success").innerHTML = '';
        document.getElementById("hiddenDeletedId").value = id;

        let path = window.location.pathname;
        let page = path.split("/").pop();
        let hiddenRecordCount = document.getElementById("hiddenRecordCount").value;
        let hiddenTotalPageCount = document.getElementById("hiddenTotalPageCount").value;

        document.getElementById('hiddenPageNo').value = page;
        $.confirm({
            title: '<?php echo __("期間確認"); ?>',
            icon: 'fas fa-exclamation-circle',
            type: 'red',
            typeAnimated: true,
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
                        document.forms[0].action = "<?php echo $this->webroot; ?>BuTerms/deleteTerm";
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

    /*  
     *	Show hide loading overlay
     *	@Zeyar Min  
     */
    function loadingPic() {
        $("#overlay").show();
        $('.jconfirm').hide();
    }
    
</script>