<style>
  .datepicker table tr td span.active.active{
    background-color:#f09282 !important;
    background-image: -webkit-gradient(linear,0 0,0 100%,from(#f09282),to(#f09282));
    background-image: -webkit-linear-gradient(top,#f09282,#f09282); 
  }
  @media (max-width:992px) { 
    .col-md-8.input-group.monthsPicker,
    select#layer_code {
        margin-bottom: 30px;
    }

    .col-md-9 {
      padding: 0;
    }

  }
  #msg{
    padding: 12px;
    margin-bottom: 30px;
    font-family: 'Aileron-Light';
  }
</style>
<script type="text/javascript">
    $(document).ready(function(){

        getDataList();

        $(".btn_sumisho").click(function(e){
            e.preventDefault();

            document.getElementById("error").innerHTML   = "";                        
            document.getElementById("success").innerHTML = "";

            var event = document.getElementById('event').value;
            var layer_code = document.getElementById('layer_code').value;
            var role_id = document.getElementById('role_id').value;

            var errorFlag = true;

            if(!checkNullOrBlank(event)) {
                var newbr = document.createElement("div");                      
                var a     = document.getElementById("error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002,['<?php echo __("イベント名"); ?>'])));
                document.getElementById("error").appendChild(a);                      
                errorFlag = false;                      
            }
            var errorMsg = $("#errMsg").val();
            if(errorMsg == 'error') {
                var newbr = document.createElement("div");                      
                var a     = document.getElementById("error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE080)));
                document.getElementById("error").appendChild(a);                      
                errorFlag = false;                      
            }
            if(errorFlag){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->webroot; ?>AssetSelections/add",
                    data: {event_id: event, layer_code: layer_code},
                    success: function(data) {
                        if(data == 1){
                            var newbr = document.createElement("div");                      
                            var a     = document.getElementById("success").appendChild(newbr);
                            a.appendChild(document.createTextNode(['<?php echo __("データ選択は成功！"); ?>']));
                            document.getElementById("success").appendChild(a); 
                        }else if(data == 0){
                            var newbr = document.createElement("div");                      
                            var a     = document.getElementById("error").appendChild(newbr);
                            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE038)));
                            document.getElementById("error").appendChild(a);                       
                        }
                    }
                });
            }
        });

        $("#event").change(function(){
            $("#hid_layer_code").val('');
            getDataList();
        });
    });
    function getDataList() {
        var ba = $("#hid_layer_code").val();
        var session_evtid = $("#session_evtid").val();
        var event = $("#event").val();
        var data = [];
        $.ajax({
            type: "POST",
            url: "<?php echo $this->webroot; ?>AssetSelections/getBa",
            data: {event_id: event},
            success: function(datas) {
                data = jQuery.parseJSON(datas);
                $('select#layer_code option[value!=""]').remove();
                if(data) {
                    $("#errMsg").val('');
                    $.each(data, function (key, val) {
                        var selected = '';
                        if(ba == key && event == session_evtid) selected = 'selected';
                        $('select#layer_code').append($('<option value="'+key+'" '+selected+'>'+key+'/'+val+'</option>'));
                    });
                }else {
                    if(event != '') {
                        $("#error").empty();
                        $("#success").empty();
                        $("#errMsg").val('error');
                        $("#error").html(errMsg(commonMsg.JSE080)).show();
                    }
                }
            }
        });
    }
</script>
<?php echo $this->Form->create(false,array('url'=>'add',
             'type'=>'post'));
             ?>                                     
<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12">
    <h3><?php echo __("固定資産実地点検");?></h3>
    <hr>
    <div class="success" id="success"></div>
    <div class="error" id="error"><?php echo ($this->Session->check("Message.Error"))? $this->Flash->render("Error") : '';?></div>
    <input type="hidden" id="hid_layer_code" name="hid_layer_code" value="<?php echo $layer_code ?>">
    <input type="hidden" id="session_evtid" name="session_evtid" value="<?php echo $event_id ?>">
    <input type="hidden" id="errMsg" name="errMsg">
    <div class="form_test">
        <fieldset class="scheduler-border">
            <legend class="scheduler-border"><?php echo __("基本選択");?></legend>
            <div class="form-group">
                <div class="col-md-12">
                    <p style="color: blue;padding-left: 15px;">
                        <?php echo __("イベントを入力してください。"); ?>
                    </p>
                </div>
                <div class="col-md-5">
                    <label for="postingDate" class="col-md-4 col-form-label required">
                    <?php echo __("イベント選択"); ?></label>
                    <?php $event =  $this->Session->read('EVENT_ID'); ?>
                    <div class="col-md-8">
                        <select  id="event" name="event" class="form-control">
                            <option value="" selected="">--Select Event Name --</option>
                            <?php foreach($event_name as $event_data => $name): 
                                $eventData = explode("/", $event_data);
                                $event_id = $eventData[0];
                                if($event == $event_id){
                                  $event_select = 'selected';
                                }else{
                                  $event_select = '';
                                }
                            ?>
                            <option value="<?=$event_id?>"<?php echo $event_select;?>>
                                <?php
                                echo h($name);
                                ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php 
                  $role_id = $this->Session->read('ADMIN_LEVEL_ID');
                ?>
                <input type="hidden" id="role_id" value="<?=$role_id?>">                      
                <div class="col-md-5">
                <label for="salesRepresentative" class="col-md-3 col-form-label">
                <?php echo __("部署選択");?></label>
                <div class="col-md-9">
                    <select  id="layer_code" name="layer_code" class="form-control">
                        <option value="">--Select Layer Name --</option>
                    </select>
                </div>
                </div>
                <div class="col-md-2" style="text-align: center;">
                    <button type="button" class="btn btn-success btn_sumisho" style="margin:unset"><?php echo __("設定選択");?> </button>
                </div>
            </div>
      </fieldset>
    </div>        
  </div>
</div>
<?php
    echo $this->Form->end();
?>