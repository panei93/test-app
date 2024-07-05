<style>
    .reset {
        margin-bottom: 30px;
    }

    .link_a {
        float: right;
    }
    
    .link_a > a:hover {
        text-decoration: none;
    }
    
    .form-control {
        width: 60%;
    }

    .closeEye {
        top: -24px;
        margin-left: 458px;
        cursor: pointer;
    }

    .note {
        font-size: 0.9em;
        color: #ff3333;
        padding-left: 43px;
        margin-bottom: 20px;
    }

    .jconfirm .jconfirm-box div.jconfirm-content-pane .jconfirm-content {
        overflow: hidden;
    }
    .save-btn-col {
        width: 75% !important;
    }
</style>

<script>
    $(document).ready(function() {

        var reset_condition = document.getElementById("reset_condition").value;

        $('.link_a').hide();
        if (reset_condition == 'user_master_rows') {
            $('.link_a').show();
        }

        $('.closeEye').click(function() {

            const eyeAdd = ($(this).hasClass('glyphicon-eye-close')) ? 'glyphicon glyphicon-eye-open' : 'glyphicon glyphicon-eye-close';
            const eyeRemove = ($(this).hasClass('glyphicon-eye-close')) ? 'glyphicon glyphicon-eye-close' : 'glyphicon glyphicon-eye-open';
            $(this).removeClass(eyeRemove).addClass(eyeAdd);

            const pw = ($(this).hasClass('pw')) ? '#password' : '#confirm_psw';

            const password = document.querySelector(pw);
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
        });
    });

    function ResetPasswordUpdate() {
        document.getElementById("error").innerHTML = "";
        document.getElementById("success").innerHTML = "";

        var password = document.getElementById("password").value;
        var confirm_password = document.getElementById("confirm_psw").value;
        errorFlag = true;
        // alert(HasMixedCase(password));

        // if (checkSpecialChar(password)) {

        //     var newbr = document.createElement("div");
        //     var a = document.getElementById("error").appendChild(newbr);
        //     a.appendChild(document.createTextNode(errMsg(commonMsg.JSE019, ['<?php echo __("新しいパスワード"); ?>'])));
        //     document.getElementById("error").appendChild(a);
        //     errorFlag = false;

        // } else
        if (!checkNullOrBlank(password)) {

            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("新しいパスワード"); ?>'])));
            document.getElementById("error").appendChild(a);
            errorFlag = false;

        } else if (password.length < 12) {

            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE011, ['<?php echo __("新しいパスワード"); ?>'])));
            document.getElementById("error").appendChild(a);
            errorFlag = false;

        // } else if (password.length > 20) {
            // var newbr = document.createElement("div");
            // var a = document.getElementById("error").appendChild(newbr);
            // a.appendChild(document.createTextNode(errMsg(commonMsg.JSE079, ['<?php echo __("新しいパスワード"); ?>'])));
            // document.getElementById("error").appendChild(a);
            // errorFlag = false;

        } else if (!HasMixedCase(password)) {

            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE010)));
            document.getElementById("error").appendChild(a);
            errorFlag = false;

        } 
        // else if (!englishCharacterNumberOnly(password)) {
            // var newbr = document.createElement("div");
            // var a = document.getElementById("error").appendChild(newbr);
            // a.appendChild(document.createTextNode(errMsg(commonMsg.JSE081, ['<?php echo __("新しいパスワード"); ?>'])));
            // document.getElementById("error").appendChild(a);
            // errorFlag = false;
        // }


        if (!checkNullOrBlank(confirm_password)) {
            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("新しいパスワード（確認）"); ?>'])));
            document.getElementById("error").appendChild(a);
            errorFlag = false;
        }

        if (password != confirm_password) {
            if (password !== confirm_password) {
                var newbr = document.createElement("div");
                var a = document.getElementById("error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE016)));
                document.getElementById("error").appendChild(a);
                errorFlag = false;
            }
        }

        if (errorFlag) {
            $.confirm({
                title: "<?php echo __('保存確認'); ?>",
                icon: 'fas fa-exclamation-circle',
                type: 'green',
                typeAnimated: true,
                boxWidth: '30%',
                useBootstrap: false,
                animateFromElement: true,
                animation: 'top',
                draggable: false,
                content: "<?php echo __("データを保存してよろしいですか。"); ?>",
                buttons: {
                    ok: {
                        text: "<?php echo __('はい'); ?>",
                        btnClass: 'btn-info',
                        action: function() {
                            document.forms[0].action = "<?php echo $this->webroot; ?>Users/ResetPasswordUpdate";
                            document.forms[0].method = "POST";
                            document.forms[0].submit();
                            return false;
                        }
                    },
                    cancel: {
                        text: "<?php echo __('いいえ'); ?>",
                        btnClass: 'btn-default',
                        cancel: function() {
                            console.log('the user clicked cancel');
                        }
                    }
                },
                theme: 'material',
                animation: 'rotateYR',
                closeAnimation: 'rotateXR'
            });
        }

    }

    function checkSpecialChar(value) {

        var regex_symbols = /[-!$%^&*()_+|~=`{}\[\]:\/;<>?,.@# ]/;
        if (regex_symbols.test(value)) {
            return true;
        }
        return false;
    }
</script>
<div class="container">
    <div class="col-md-12 col-sm-12 heading_line_title">
        <h3><?php echo __("パスワードリセット"); ?></h3>
        <hr>
        <div class="link_a"><a href="<?= $this->webroot; ?>Users"><i class="fa-regular fa-circle-left"></i>&nbsp;<?php echo __('戻る'); ?></a></div>
        <br>
        <div class="success" id="success"><?php echo ($this->Session->check("Message.PasswordSuccess")) ? $this->Flash->render("PasswordSuccess") . "<a href='" . Router::url('/', true) . "Logins/logout'>" . __("ここから") . "</a>" . __("ログインしてください") : ''; ?></div>
        <div class="error" id="error"><?php echo ($this->Session->check("Message.PasswordError")) ? $this->Flash->render("PasswordError") : ''; ?></div>
        <?php echo $this->Form->create(false, array('url' => 'ResetPassword', 'type' => 'post', 'id' => 'reset-form')) ?>

        <div class="row" style="margin-bottom: 10px;margin-top: -30px;">
            <div class="row note" style="padding-top: 20px">
                <p class="slidein">
                    <?php if (!empty($expire)) {  ?>
                        <span class="glyphicon glyphicon-info-sign"></span>
                    <?php echo __($expire) . "<br> <br>";
                    } ?>
                </p>
            </div>
            <div class="row note">
                <p><?php echo __("※12文字以上の文字、数字、特殊文字"); ?></p>
                <p><?php echo __("※パスワードは大文字、小文字、特殊文字、数字をそれぞれ1文字以上含む必要があります"); ?></p>
            </div>
        </div>
        <div class="col-md-12 reset">
            <div class="row">
                <div class="form-group">
                    <label class="col-sm-3 col-md-4 col-xs-12 required"><?php echo __("新しいパスワード"); ?></label>
                    <div class="col-md-9 col-xs-12 ">
                        <input class="form-control" id="password" name="password" type="password" value="" autoComplete="new-password">
                        <span class="glyphicon glyphicon-eye-close closeEye pw"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 reset">
            <div class="row">
                <div class="form-group">
                    <label class="col-sm-3 col-md-4 col-xs-12 required"><?php echo __("新しいパスワード（確認）"); ?></label>
                    <div class="col-md-9 col-xs-12 ">
                        <input class="form-control" id="confirm_psw" name="confirm_psw" type="password" value="" autoComplete="new-password">
                        <span class="glyphicon glyphicon-eye-close closeEye cpw"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="form-group">
                    <div class="col-md-9 save-btn-col">
                        <div style="width:60%;margin-bottom:5rem;">
                            <input onclick="ResetPasswordUpdate();" type="button" value="<?php echo __("保存"); ?>" name="" class="btn btn-save pull-right" id="save_btn">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- hidden  data id for password reset-->
        <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
        <input type="hidden" id="reset_condition" name="reset_condition" value=<?php echo $reset_condition; ?>>
        <?php echo $this->Form->end(); ?>
    </div>
</div>