<style>
body{
	background: url('<?php echo $this->webroot; ?>img/backgroud/gold_bg1.png') no-repeat center center fixed; 
	-webkit-background-size: cover;
	-moz-background-size: cover;
	-o-background-size: cover;
	background-size: cover;
}
div#error{
  margin: 0px 10px 0px 10px;
  overflow: hidden;
}
.login-container{
	  position: absolute;
    margin-top: 10%;
    width:100% !important;
    margin-bottom: 10%;
    z-index:30;
    margin: 0 auto;

}

input:focus {
  outline: none;
}
.login-form-2{
  width:350px;
  padding-top: 3%;
  margin-top: 10% !important;
  background: #c5484c;
  margin: 0 auto;
}
.login-form-2 h3{
	margin:3px;
	font-family:Comic sans MS;
	font-size:35px;
	font-weight: bold;
  text-align: center;
  color: #fff;
}
.login-container form{
  padding: 10%;
  margin: 0 auto;
}
.login-txt
{
  width: 100%;
  height: 40px;
  border: none;
  border-bottom: 1px solid #fff;
  background: transparent;
  color: #fff;
  margin-bottom: 13px;
}

.login-form-2 .btnSubmit{
	margin-top: 30px;
    font-weight: 700;
    width: 100%;
    height:40px;
    color: #FFFFFF;
    background-color: #5f5f5f;
    border-radius: 1rem;
    padding: 1.5%;
    border: none;
    cursor: pointer;
    box-shadow: 0 5px 8px 0 rgba(0, 0, 0, 0.2), 0 9px 26px 0 rgba(0, 0, 0, 0.19);
    
}
 .overlay {  
 		position: fixed;  
		z-index: 10;  
		top: 0;  
 		left: 0; 
 		width: 100%; 
 		height: 100%;  
 		background: black; 
 		opacity: 0.1;  
 		}
.contactinfo {
    padding: 0px 10px 30px 10px;
}

a{
  color: white !important;
}
a:hover{
  color: #000 !important;
}
::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
  color: #fff;
  opacity: 1; /* Firefox */
}

:-ms-input-placeholder { /* Internet Explorer 10-11 */
  color: #fff;
}

::-ms-input-placeholder { /* Microsoft Edge */
  color: #fff;
 }

 @media screen and (max-width: 360px) {
  .login-form-2{
    width: 90%;
    
  }
}

</style>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/system_logo.png">
<?php

    echo $this->Html->script('jquery.min.js');
    echo $this->Html->script('moment.min.js');
    echo $this->Html->script('bootstrap-datetimepicker.js');
    echo $this->Html->script('script.js');
    echo $this->Html->script('commonMessage');
    echo $this->Html->script('sprintf');
    echo $this->Html->css('style.css');
    echo $this->Html->css('bootstrap.min.css');
    
    if($this->Session->read('Config.language') == 'eng') {    
      echo $this->Html->script('commonMessage');
    }
    else {
      echo $this->Html->script('commonMessage-jp');
    }
  ?>
 <script>
$(document).ready(function(){
  $("#loginButton").click(function(e){
    e.preventDefault();
    document.getElementById("error").innerHTML   = "";                        
    document.getElementById("success").innerHTML = "";
    errorFlag = true;
    var login_id = document.getElementById("login_id").value;
    var password = document.getElementById("password").value;

      if(!checkNullOrBlank(login_id)) {

          var newbr = document.createElement("div");                      
          var a     = document.getElementById("error").appendChild(newbr);
          a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("ユーザID"); ?>'])));
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

        document.forms[0].action = "<?php echo $this->webroot; ?>Login/index";
        document.forms[0].method = "POST";
        document.forms[0].submit();   
        return true;
      }
   });
});
 </script>
  <title>FINANCIIO</title>
 <div class="overlay"></div>
<div class="container login-container">
	<div class="row" >  
		<div class="login-form-2">
			<!-- <h3>Sumitomo</h3> -->
         
      <div class="col-sm-12">
        <div class="contactinfo">
          <ul class="nav nav-pills">
            <li><img src="<?php echo $this->webroot;?>img/system_logo.png" width="100"></li>   
            <li><a href="javascript:changeLanguage('jpn')">
            <img src="<?php echo $this->webroot; ?>img/japan.png" height="20px" width="20px" alt="JPN"><?php echo __("Japan")?></a></li>  
            <li><a href="javascript:changeLanguage('eng')">
            <img src="<?php echo $this->webroot; ?>img/english.png" height="20px" width="20px" alt="ENG">
            <?php echo __("English")?></a></li>          
          </ul>
        </div>    
      </div> 
      <div class="success" id="success"><?php echo ($this->Session->check("Message.loginSuccess"))? $this->Flash->render("loginSuccess") : '';?></div>
      <div class="error" id="error"><?php echo ($this->Session->check("Message.loginError"))? $this->Flash->render("loginError") : '';?></div> 
		  <form id="my_form">
				<div class="form-group">
					<input type="text" name="login_id" id="login_id" class="login-txt" placeholder="<?php echo __("ID");?>" value="" autofocus/>
				</div>
				<div class="form-group">
					<input type="password" name="password" id="password" class="login-txt" placeholder="<?php echo __("パスワード");?>" value="" />
				</div>
				<div class="form-group">
					<input type="submit" id="loginButton" class="btnSubmit" value="<?php echo __("ログイン");?>"/>
				</div>
			 </form>
		</div>
	</div>
</div>
