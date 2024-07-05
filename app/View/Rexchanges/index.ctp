<style>

.date-wrapper {
    margin: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-top: 30px;
    padding: 15px 30px;
}


#btn_update {
    display: none;
}
</style>

<div id="overlay">
    <span class="loader"></span>
</div>

<div class='content'>

    <div class="row" style="font-size: 0.95em;">
        <div class="col-md-12 col-sm-12 heading_line_title">
            <h3><?php echo __('為替レート管理'); ?></h3>
            <hr>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="success" id="success"><?php echo ($this->Session->check("Message.UserSuccess")) ? $this->Flash->render("UserSuccess") : ''; ?></div>
            <div class="error" id="error"><?php echo ($this->Session->check("Message.UserError")) ? $this->Flash->render("UserError") : ''; ?></div>
        </div>
    </div>

    <?php echo $this->Form->create(false, array('type' => 'post')); ?>
    <div class="form-group row">
        <div class="col-md-6">
            <label for="from_date" class="col-sm-4 col-form-label required">
                <?php echo __("対象年度"); ?>
            </label>
            <div class="col-sm-8">
                <div class="input-group date " id="datepicker" data-provide="datepicker" style="padding: 0px;">
                    <input type="text" class="form-control" id="target_year" name="target_year" maxlength="4" value="" autocomplete="off" style="background-color: #fff;" readonly/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-6">
            <label for="deadline_date" class="col-md-12 col-form-label required">
                <?php echo __('通貨'); ?>
            </label>
            <div class="date-wrapper">
                <div class="row">
                    <label for="main_currency" class="col-sm-4 col-form-label required">
                        <?php echo __('主要通貨'); ?>
                    </label>
                    <div class="col-sm-4">
                        <select class="form-control form_input" name="main_type" id="main_type" onchange="MainCurrencyCode()">
                            <option value=""><?php echo __("----- Select -----"); ?>
                                <?php foreach ($currency as $key => $value) : ?>
                            <option value="<?php echo $value['Currency']['id']; ?>" >
                                <?php echo $value['Currency']['country']; ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <div class="input-group">
                            <input type="text" class="form-control" id="main_amount" name="main_amount" value="" disabled>
                            <span class="input-group-addon " id="main_code"></span>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top: 20px;">
                    <label for="main_currency" class="col-sm-4 col-form-label required">
                        <?php echo __('外貨'); ?>
                    </label>
                    <div class="col-sm-4">
                        <select class="form-control form_input" name="exchange_type" id="exchange_type" onchange="ExchangeCurrencyCode()">
                            <option value="" selected=""><?php echo __("----- Select -----"); ?>
                                <?php foreach ($currency as $key => $value) : ?>
                            <option value="<?php echo $value['Currency']['id']; ?>">
                                <?php echo $value['Currency']['country']; ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <div class="input-group ">
                            <input type="text" class="form-control"  id="exchange_amount"
                                name="exchange_amount" value="">
                            <span class="input-group-addon" id="exchange_code" value=""></span>
                        </div>
                    </div>
                </div>
                <!-- <div class="row" style="margin-top: 20px;">
                    <label for="main_currency" class="col-sm-4 col-form-label required">
                        <?php echo __('Total Currency'); ?>
                    </label>
                    <div class="col-sm-8" style="justify-content: center; display: flex;">
                        <div class="input-group col-sm-6">
                            <input type="text" class="form-control" id="total_amount" name="total_amount" value="">
                            <span class="input-group-addon " id="total_code" value=""></span>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-6">
            <div class="form-group row col-sm-12 justify-content-end" style="padding: 0;">

                <input type="button" class="btn-save btn-success btn_sumisho" id="btn_update" name="btn_update" onclick="UpdateData()"
                    value="<?php echo __('変更'); ?>">

                <input type="hidden" name="hid_page_no" id="hid_page_no" class="txtbox" value="">
                <input type="hidden" name="hd_id" id="hd_id" class="txtbox" value="">

                <input type="button" class="btn-save btn-success btn_sumisho" id="btn_save" name="btn_save" onclick="RegisterData()"
                    value="<?php echo __('保存'); ?>">
            </div>
        </div>
    </div>

    <?php echo $this->Form->end(); ?>

    <?php if (!empty($datas)) { ?>
    <div class="msgfont" id="total_row">
        <?= $count; ?>
    </div>
    <div class="table-container rtaxFees index" style="margin-bottom: 5rem;">
        <table cellpadding="0" cellspacing="0" class="table table-bordered" id="rex">
            <thead>
                <tr>
                    <th><?php echo __("#"); ?></th>
                    <th><?php echo __("対象年度"); ?></th>
                    <th><?php echo __("主要通貨額"); ?></th>
                    <th><?php echo __("外貨金額"); ?></th>
                    <th colspan="2" style="min-width: 10rem;"><?php echo __("アクション"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?>
                <?php foreach ($datas as $exchange) : ?>
                <tr>
                    <td style="vertical-align:middle;"><?= h($no+=1) ?></td>
                    <td style="vertical-align:middle;"><?= h($exchange['Rexchange']['target_year']) ?></td>
                    <td style="vertical-align:middle;">1 <?= h($exchange['Rexchange']['main_currency_code']) ?></td>
                    <td style="vertical-align:middle;"><?= h($exchange['Rexchange']['rate']) ?> <?= h($exchange['Rexchange']['ex_currency_code']) ?></td>
                    <td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;"
                        class='edit'>
                        <a class="" href="#"
                            onclick="Edit(<?= h($exchange['Rexchange']['id']) ?>)"><i class="fa-regular fa-pen-to-square" title="<?php echo __('編集');?>"></i>
                        </a>
                    </td>
                    <td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;"
                        class='remove'>
                        <a class="delete_link" href="#"
                            onclick="Delete(<?= h($exchange['Rexchange']['id']) ?>)"><i class="fa-regular fa-trash-can" title="<?php echo __('削除'); ?>"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="col-md-12" style="padding: 10px;text-align: center;margin-bottom: 50px;">
            <div class="paging">
                <?php
                if ($query_count > Paging::TABLE_PAGING) {
                    echo $this->Paginator->first('<<');
                    echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev disabled'));
                    echo $this->Paginator->numbers(array('separator' => '', 'modulus' => 6));
                    echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
                    echo $this->Paginator->last('>>');
                }
                ?>
            </div>
        </div>
    </div>
    <?php }else{?>
        <div id="err" class="no-data"> <?php echo ($noDataMsg); ?></div>
   <?php } ?>


</div>

<script>
$(document).ready(function() {
    if ($('#rex').length > 0) {
        $("#rex").floatThead({position: 'absolute'});
    }
    $("#datepicker").datepicker({
        format: "yyyy",
        viewMode: "years",
        minViewMode: "years",
        autoclose: true
    });
})

function MainCurrencyCode(){
	let id = document.querySelector('#main_type').value;

	if (id != ""){
		$.ajax({
				type: "POST",
				url: "<?php echo $this->webroot; ?>Rexchanges/getMainCurrencyCode",
				data: {
					id:id
				},
				dataType: "json",
				success: function(data) {
					var code = data[0]['Currency']['currency_code'];
					$("#main_code").text(code);
					$('#main_amount').val(1);
                    $('#overlay').hide();
				},

		});
	} else {
		$("#main_code").text('');
		$('#main_amount').val('');			
	}
}

function ExchangeCurrencyCode(){
	let id = document.querySelector('#exchange_type').value;
	if(id != ""){
		$.ajax({
				type: "POST",
				url: "<?php echo $this->webroot; ?>Rexchanges/getExchangeCurrencyCode",
				data: {
					id: id
				},
				dataType: "json",
				success: function(data) {
					var code = data[0]['Currency']['currency_code'];
					$("#exchange_code").text(code);
                    $("#exchange_code").val(code);
                    $("#overlay").hide();
				
				},

			});
	} else {
		$("#exchange_code").text('');
		$('#exchange_amount').val('');
	}
}

function RegisterData(){
    document.querySelector("#error").innerHTML = '';
    document.querySelector("#success").innerHTML = '';

    let target_year = document.querySelector('#target_year').value;
    let main_amount = document.querySelector('#main_amount').value;
    let exchange_amount = document.querySelector('#exchange_amount').value;
    let code = document.querySelector('#exchange_code').value;
    let main_type = document.querySelector('#main_type').value;
    let exchange_type = document.querySelector('#exchange_type').value;
    errorFlag = true;
    // main_amount = parseInt(main_amount);
    // exchange_amount = parseInt(exchange_amount);
    
    if (!checkNullOrBlank(target_year)) {

        let newbr = document.createElement("div");
        let a = document.querySelector("#error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
            '<?php echo __("対象年度"); ?>'
        ])));
        document.querySelector("#error").appendChild(a);
        errorFlag = false;
    }
    if (!checkNullOrBlank(main_type)) {

        let newbr = document.createElement("div");
        let a = document.querySelector("#error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, [
            '<?php echo __("主要通貨"); ?>'
        ])));
        document.querySelector("#error").appendChild(a);
        errorFlag = false;
    }
    if (!checkNullOrBlank(exchange_type)) {

        let newbr = document.createElement("div");
        let a = document.querySelector("#error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, [
            '<?php echo __("外貨"); ?>'
        ])));
        document.querySelector("#error").appendChild(a);
        errorFlag = false;
    }
    
    if (isNaN(main_amount) || !checkNullOrBlank(main_amount)) {

        let newbr = document.createElement("div");
        let a = document.querySelector("#error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007, [
            '<?php echo __("主要通貨額"); ?>'
        ])));
        document.querySelector("#error").appendChild(a);
        errorFlag = false;
    }
    
    if (isNaN(exchange_amount) || !checkNullOrBlank(exchange_amount) || !isNumberDots(exchange_amount)) {
        
        let newbr = document.createElement("div");
        let a = document.querySelector("#error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007, [
            '<?php echo __("外貨金額"); ?>'
        ])));
        document.querySelector("#error").appendChild(a);
        errorFlag = false;
    }
    // if (!isNumberDots(exchange_amount)) {
    //     let newbr = document.createElement("div");
    //     let a = document.querySelector("#error").appendChild(newbr);
    //     a.appendChild(document.createTextNode(errMsg(
    //         '<?php echo __("外貨金額"); ?>'
    //     )));
    //     document.querySelector("#error").appendChild(a);
    //     errorFlag = false;
    // }

    

    if (errorFlag) {
        $.confirm({
            title: "<?php echo __('保存確認'); ?>",
            icon: 'fas fa-exclamation-circle',
            type: 'green',
            boxWidth: '30%',
            closeIcon: true,
            useBootstrap: false,
            typeAnimated: true,
            animateFromElement: true,
            animation: 'top',
            draggable: false,
            content: "<?php echo __("データを保存してよろしいですか。"); ?>",
            buttons: {
                ok: {
                    text: "<?php echo __('はい'); ?>",
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action = "<?php echo $this->webroot; ?>Rexchanges/add";
                        document.forms[0].method = "POST";
                        document.forms[0].submit();
                        return false;
                    }
                },
                cancel: {
                    text: "<?php echo __('いいえ'); ?>",
                    btnClass: 'btn-default',
                    btnId: 'btn_cancel',
                    cancel: function(msg) {
                        animation: scrollText();
                        $("#btn_cancel").html(msg);
                        $('html, body').animate({
                            scrollTop: 0
                        }, 0);
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

function Edit(id) {
    document.querySelector("#error").innerHTML = '';
    document.querySelector("#success").innerHTML = '';
    $.ajax({
        type: "POST",
        url: "<?php echo $this->webroot; ?>Rexchanges/edit",
        data: {
            id: id
        },
        dataType: 'json',
        beforeSend: function() {
            loadingPic();
        },
        success: function(data) {

            let hd_id = data['hd_id'];
            let target_year = data['target_year'];
            let rate = data['rate'];
            let main_country = data['main_country'];
            let main_code = data['main_currency_code']
            let ex_country = data['exchange_country']
            let ex_code = data['exchange_currency_code'];
            $("#hd_id").val(hd_id);
            $("#target_year").val(target_year);
            $("#target_year").prop( "disabled", true );
            $('#exchange_type option[value="' + ex_country + '"]').prop('selected', true);
            $("#exchange_amount").val(rate);
            $("#exchange_code").text(ex_code);
            $('#main_type option[value="' + main_country + '"]').prop('selected', true);
            $("#main_code").text(main_code);
            $("#main_amount").val(1);
            $("span").unbind("click");
            $('#btn_save').hide();
            $('#btn_update').show();
            $('#overlay').hide();

        }
    });

}

function UpdateData(){
    document.querySelector("#error").innerHTML = '';
    document.querySelector("#success").innerHTML = '';

    let target_year = document.querySelector('#target_year').value;
    let main_type = document.querySelector('#main_type').value;
    let exchange_type = document.querySelector('#exchange_type').value;
    let main_amount = document.querySelector('#main_amount').value;
    let exchange_amount = document.querySelector('#exchange_amount').value;
    let code = document.querySelector('#exchange_code').value;
    errorFlag = true;
    main_amount = parseInt(main_amount);
    exchange_amount = parseInt(exchange_amount);

    if (!checkNullOrBlank(target_year)) {

        let newbr = document.createElement("div");
        let a = document.querySelector("#error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
            '<?php echo __("対象年度"); ?>'
        ])));
        document.querySelector("#error").appendChild(a);
        errorFlag = false;
    }
    if (!checkNullOrBlank(main_type)) {

        let newbr = document.createElement("div");
        let a = document.querySelector("#error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, [
            '<?php echo __("主要通貨"); ?>'
        ])));
        document.querySelector("#error").appendChild(a);
        errorFlag = false;
    }
    if (!checkNullOrBlank(exchange_type)) {

        let newbr = document.createElement("div");
        let a = document.querySelector("#error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, [
            '<?php echo __("外貨"); ?>'
        ])));
        document.querySelector("#error").appendChild(a);
        errorFlag = false;
    }

    if (isNaN(main_amount) || !checkNullOrBlank(main_amount)) {

        let newbr = document.createElement("div");
        let a = document.querySelector("#error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007, [
            '<?php echo __("主要通貨額"); ?>'
        ])));
        document.querySelector("#error").appendChild(a);
        errorFlag = false;
    }

    if (isNaN(exchange_amount) || !checkNullOrBlank(exchange_amount)) {

        let newbr = document.createElement("div");
        let a = document.querySelector("#error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE007, [
            '<?php echo __("外貨金額"); ?>'
        ])));
        document.querySelector("#error").appendChild(a);
        errorFlag = false;
    }else if (!validateNumberOnly(exchange_amount)) {
        let newbr = document.createElement("div");
        let a = document.querySelector("#error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(
            '<?php echo __("外貨金額"); ?>'
        )));
        document.querySelector("#error").appendChild(a);
        errorFlag = false;
    }

    if (errorFlag) {
        $.confirm({
            title: "<?php echo __('変更確認'); ?>",
            icon: 'fas fa-exclamation-circle',
            type: 'green',
            boxWidth: '30%',
            closeIcon: true,
            useBootstrap: false,
            typeAnimated: true,
            animateFromElement: true,
            animation: 'top',
            draggable: false,
            content: "<?php echo __("データを変更してよろしいですか。"); ?>",
            buttons: {
                ok: {
                    text: "<?php echo __('はい'); ?>",
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action = "<?php echo $this->webroot; ?>Rexchanges/update";
                        document.forms[0].method = "POST";
                        document.forms[0].submit();
                        return false;
                    }
                },
                cancel: {
                    text: "<?php echo __('いいえ'); ?>",
                    btnClass: 'btn-default',
                    btnId: 'btn_cancel',
                    cancel: function(msg) {
                        animation: scrollText();
                        $("#btn_cancel").html(msg);
                        $('html, body').animate({
                            scrollTop: 0
                        }, 0);
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

function Delete(id) {

document.querySelector("#error").innerHTML = '';
document.querySelector("#success").innerHTML = '';
document.querySelector("#hd_id").value = id;

let errorFlag = true;

let path = window.location.pathname;
let page = path.split("/").pop();
document.querySelector('#hid_page_no').value = page;
if (errorFlag) {
    $.confirm({
        title: "<?php echo __('削除確認'); ?>",
        icon: 'fas fa-exclamation-circle',
        type: 'red',
        boxWidth: '30%',
        closeIcon: true,
        useBootstrap: false,
        typeAnimated: true,
        animateFromElement: true,
        animation: 'top',
        draggable: false,
        content: "<?php echo __('データを削除してよろしいですか。'); ?>",
        buttons: {
            ok: {
                text: "<?php echo __('はい'); ?>",
                btnClass: 'btn-info',
                action: function() {
                    loadingPic();
                    document.forms[0].action = "<?php echo $this->webroot; ?>Rexchanges/delete";
                    document.forms[0].method = "POST";
                    document.forms[0].submit();
                    return true;
                }
            },
            cancel: {
                text: "<?php echo __('いいえ'); ?>",
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