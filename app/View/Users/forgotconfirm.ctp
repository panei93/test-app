<style>
    .save-btn-div {
        padding-right: 0;
        width: 45%;
        display: flex;
        justify-content: end;
    }
   #save_btn{
        /* margin-left: 19%; */
        /* margin-top: 2%; */
        /* padding-left: 1.5%; */
        /* padding-right: 1.5%; */
    }
    .form-control{
        width: 60%;
    }
    .forget{
       margin-bottom: 30px;
    }
</style>

<script>
    $(document).ready(function() {
      

    });

    function forgotConfirm(){
        document.getElementById("error").innerHTML   = "";                        
        document.getElementById("success").innerHTML = "";

        var login = document.getElementById("login").value;
        var email = document.getElementById("email").value;
        errorFlag = true; 
        
        if(!checkNullOrBlank(login)){
                var newbr = document.createElement("div");      
                var a = document.getElementById("error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("User ID"); ?>'])));
                document.getElementById("error").appendChild(a);
                errorFlag = false;
            }

            if(!checkNullOrBlank(email)){
                var newbr = document.createElement("div");      
                var a = document.getElementById("error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("Email"); ?>'])));
                document.getElementById("error").appendChild(a);
                errorFlag = false;
            } 

            // allow multiple email with comma 
            if(email !== null && email !== '') {
                var errorFlag_email = true;
                var emails = email.split(",");
                emails.forEach(function (email) {
                    if(!validateEmail(email.trim())){
                        errorFlag_email = false;
                    }
                });
                if(!errorFlag_email){
                    var newbr = document.createElement("div");      
                    var a = document.getElementById("error").appendChild(newbr);
                    a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __('valid email address'); ?>'])));
                    document.getElementById("error").appendChild(a);
                    errorFlag = false; 
                }
            }
            if(errorFlag){
                document.forms[0].action = "<?php echo $this->webroot; ?>Users/forgotConfirmPW";
                document.forms[0].method = "POST";
                document.forms[0].submit();
            }

    }
   
</script>
<div class="container">
    <div class="col-md-12 col-sm-12 heading_line_title">
        <h3><?php echo __("Forgot Password");?></h3>
        <hr>
        <br>
        <div class="success" id="success"><?php echo ($this->Session->check("Message.forgotConfirmSuccess"))? $this->Flash->render("forgotConfirmSuccess") : '';?></div>
        <div class="error" id="error"><?php echo ($this->Session->check("Message.forgotConfirmError"))? $this->Flash->render("forgotConfirmError") : '';?></div>
        <?php echo $this->Form->create(false,array('url'=>'ForgotConfirm','type'=>'post','id' => 'forgot-form'))?>
        <!-- hidden  data id for password reset-->
       
        <div class="col-md-12 forget">
            <div class="row">
                <div class="form-group">
                    <label class="col-sm-3 col-md-4 col-xs-12 required"><?php echo __("User ID");?></label>
                    <div class="col-md-9 col-xs-12 ">
                        <input class="form-control" id="login" name="login" type="text" value="">         
                    </div>
                </div>
            </div>                       
        </div>

        <div class="col-md-12 forget">
          <div class="row">
             <div class="form-group">
                <label class="col-sm-3 col-md-4 col-xs-12 required"><?php echo __("Email");?></label>
              <div class="col-md-9 col-xs-12 ">
                <input class="form-control" id="email" name="email" type="text" value="">         
              </div>
              </div>
          </div>                       
        </div>
        <div class="form-group col-md-12 col-sm-12 col-xs-12 save-btn-div">
          <input onclick="forgotConfirm();" type="button" value="<?php echo __("Next");?>" name="" class="btn btn-save" id="save_btn">
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
