<style>
#content {
    background: none;
}

.or {
    position: relative;
    width: 100%;
    height: 50px;
    color: #757575;
    line-height: 50px;
    text-align: center;
}

.or::before,
.or::after {
    position: absolute;
    width: 45%;
    height: 1px;
    top: 32px;
    background-color: rgba(217, 217, 217, 1);
    content: '';
}

.or::before {
    left: 0;
}

.or::after {
    right: 0;
}
div#error{
    /* margin: 0px 10px 0px 10px; */
    overflow: hidden;
  }

.msie-error {
    z-index: 10000;
    font-size: medium;
    text-align: center;
    top: 40%;
    left: 40%;
    /* border: 1px solid rgba(0, 166, 160, 1); */
    border-radius: 3px;
    background-color: none !important;
    background: none !important;
    margin-bottom: 0 !important;
}
.msie-error:empty {
    display: none;
}
.message {
    background: #fff6cc !important;
    color: #a63c06;
    margin-bottom: 1px !important;
    font-family: var(--font_family);
    border: none;
    border-radius: 1em;
    font-size: 1.5rem;
    font-weight: 500 !important;
    text-shadow: none;
    text-align: start;
    margin: 0;
    padding: 1em !important;
    box-shadow: none !important;
}
.password-group {
    position: relative;
    box-sizing: border-box;
}
.password-eye {
    display: none;
    position: absolute;
    top: 2.5rem;
    right: 1em;
    cursor: pointer;
    opacity: 0.7;
    width: 22px;
}
.form-group .password-input {
    padding-right: 3.5rem !important;
}
.password-input:focus .password-eye {
    display: block;
}
@media (max-width:780px) {
    .password-eye {
        width: 20px;
    }
}
</style>

<div id="overlay">
	<span class="loader"></span>
</div>

<div class="content login-content">
    <div class="row ">
        <div class="col-lg-12">           
            <?php $show_flag = ($this->Session->check('SHOW_FLAG'))? $this->Session->read('SHOW_FLAG') : 'true'; if($show_flag != 'false'): ?>
                <h2 class="login-header">
                <?php echo __("ログイン"); ?>
                </h2>
                <?php 
                endif;
                ?>
        </div>
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4"></div>
                <div class="col-lg-4">
                    <form class="login-form" action="" method="post">
                        <div class="error msie-error" id="msie-error"><?php $show_flag = ($this->Session->check('SHOW_FLAG'))?
                        $this->Session->read('SHOW_FLAG') : 'true'; if($show_flag == 'false'): 
                        echo __("ユーザーは多数のログイン ID を持っています。 ログインIDを選択してください。");
                        endif; ?></div>
                        <div class="error msie-error" id="msie-error"><?php echo ($this->Session->check("Message.notsupport"))? $this->Flash->render("notsupport") : '';?></div>
                        <div class="success login-success" id="success"><?php echo ($this->Session->check("Message.loginSuccess"))? $this->Flash->render("loginSuccess") : '';?></div>
                        <div class="error login-error" id="error"><?php echo ($this->Session->check("Message.loginError") && $show_flag != 'false')? $this->Flash->render("loginError") : '';?></div>
                        <?php $show_flag = ($this->Session->check('SHOW_FLAG'))? $this->Session->read('SHOW_FLAG') : 'true'; if($show_flag != 'false'): ?>
                        <div class="form-group login-form-group login-form-group-usercode">
                            <input type="text" class="form-control usercode-input" id="login_code" name="login_code"
                                placeholder="ID">
                        </div>
                        <div class="form-group login-form-group password-group">
                            <label for=""></label>
                            <input type="password" class="form-control password-input" id="password" name="password"
                                placeholder="Password">
                            <img src="<?php echo $this->webroot; ?>img/hide.png" alt="password eye" class="password-eye pw">
                        </div>
                        <div class="login-form-group form-group">
                            <input type="submit" id="login" class="btn btn-login" value="<?php echo __("ログイン");?>"/>
                        </div>
                        <div class="login-form-group form-group">
                            <a href="<?php echo $this->webroot; ?>Users/ForgotConfirm" style="text-decoration: none;"><?php echo __("パスワードを忘れた場合はこちら") ?></a>
                        </div>
                        <div class="fancy"><span><?php echo __("又は"); ?></span></div>
                        <div class="login-form-group form-group">
                            <button id="ssologin" type="submit"
                                class="btn btn-ssologin"><?php echo __("SSO ログイン"); ?>
                            </button>                                
                        </div>
                        <div class="login-form-group form-group">
                            <?php if($show_link == 'true'):?>
                                    <a href="<?php echo $this->webroot; ?>Logins/ssoLogin/?hang_out=1&&lang=<?php echo $lang; ?>" style="padding-top: 20px;"><?php echo __("別のアカウントでログインしますか？");?> </a>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="form-group login-form-group login-form-group-usercode">
                            <select id="sso_login_code" name="login_code" class="form-control form_input">
                                <option value="" selected=""><?php echo __("----- Select -----"); ?></option>
                                <?php foreach($login_codes as $login_code): ?>
                                    <option value="<?php echo $login_code;?>"><?php echo $login_code;?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="login-form-group form-group">
                            <!-- <button type="submit" id="ssologin" class="btn btn-ssologin ssoidlogin">
                              <?php echo __("ログイン");?>
                            </button> -->
                        </div>
                        <?php endif;?>
                    </form>
                </div>
                <div class="col-lg-4"></div>
            </div>
        </div>
    </div>
</div>
<button id="hide_btn">hide_btn<button>
<script>
$(document).ready(function() {

    $(".password-input").focus(showPassword);
    $(".password-input").keyup(showPassword);
    $('.password-eye').click(function() {
        // toggle eye image
        let src = ($(this).attr('src') === '<?php echo $this->webroot; ?>img/hide.png') ? '<?php echo $this->webroot; ?>img/show.png' : '<?php echo $this->webroot; ?>img/hide.png';
        $(this).attr('src', src);
        // toggle input type
        const pw = ($(this).hasClass('pw')) ? '#password' : '';
        const password = document.querySelector(pw);
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
    });
   
    $("#login").click(function(e) {
        e.preventDefault();
        document.getElementById("error").innerHTML = "";
        document.getElementById("success").innerHTML = "";

        errorFlag = true;
        var login_code = document.getElementById("login_code").value;
        var password = document.getElementById("password").value;

        if(!checkNullOrBlank(login_code)) {
        
        var newbr = document.createElement("div");                      
        var a     = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("ユーザID"); ?>'])));
        document.getElementById("error").appendChild(a);
                  
        errorFlag = false;          
      }
      if(login_code.indexOf(' ') >= 0) {
        var newbr = document.createElement("div");                      
        var a     = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE063,['<?php echo __("ユーザID"); ?>'])));
        document.getElementById("error").appendChild(a);                      
        errorFlag = false;                      
      }
      if(!checkNullOrBlank(password)) {
        
        var newbr = document.createElement("div");                      
        var a     = document.getElementById("error").appendChild(newbr);
        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("パスワード"); ?>'])));
        document.getElementById("error").appendChild(a);                      
        errorFlag = false;                      
      }
      if(errorFlag){
        document.forms[0].action = "<?php echo $this->webroot; ?>Logins";
        document.forms[0].method = "POST";
        document.forms[0].submit();
        return true;
      }
    });
    $("#sso_login_code").on("change", function(){
        let errorFlag = true;
        document.getElementById("error").innerHTML = "";
        document.getElementById("success").innerHTML = "";
        let login_code = document.getElementById("sso_login_code").value;
        if (!checkNullOrBlank(login_code)) {
            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
                '<?php echo __("ユーザID"); ?>'
            ])));
            document.getElementById("error").appendChild(a);
            errorFlag = false;
        }
        if (login_code.indexOf(' ') >= 0) {
            var newbr = document.createElement("div");
            var a = document.getElementById("error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE063, [
                '<?php echo __("ユーザID"); ?>'
            ])));
            document.getElementById("error").appendChild(a);
            errorFlag = false;
        }
        if (errorFlag) {
            document.forms[0].action = "<?php echo $this->webroot; ?>Logins/getViewData";
            document.forms[0].method = "POST";
            document.forms[0].submit();
            return true;
        }
    });
    function loadingPic() { 
			$("#overlay").show();
            $('.jconfirm').hide();  
	}

    $("#ssologin").click(function(e) {
        e.preventDefault();
        document.getElementById("error").innerHTML = "";
        document.getElementById("success").innerHTML = "";
        let showFlag =
            <?php echo ($this->Session->check('SHOW_FLAG'))? $this->Session->read('SHOW_FLAG') : true; ?>;
            
        let browserName = "<?php echo ($this->Session->check('browser_name'))?>";
        if (showFlag) {
            window.location.href = "<?php echo SSOInfo::sso_app_link;?>";
        }
    });
    $("#hide_btn").click(function(){
        // loadingPic();
        $("#sso_login_code").val("<?php echo $login_code;?>");
        document.forms[0].action = "<?php echo $this->webroot; ?>Logins/getViewData";
        document.forms[0].method = "POST";
        document.forms[0].submit();
        return true;
    });
    var count_code = "<?php echo $count_code;?>";
    if(count_code == 1){
        $("#hide_btn").click();
        $(".login-content").hide();
        loadingPic();
    }
    $("#hide_btn").hide();
});
const showPassword = function() {
    var passValue = $(this).val();
    if(passValue.length == 0) {
        $('.password-eye').css('display', 'none');
    } else {
        $('.password-eye').css('display', 'block'); 
    }
}
</script>