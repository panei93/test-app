
<style>
  .datepicker table tr td span.active.active {
		background-color: #00a6a0 !important;
		background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#00a6a0), to(#035956));
		background-image: -webkit-linear-gradient(top, #00a6a0, #00a6a0);
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
        
        if($("#period").val() != ''){
          getBA();
        }
        $('#layer_code').on('click', function() {
          $("#error").empty();
          $("#success").empty();
          var period = $("#period").val();
          if(period == "") {
              $("#error").html(errMsg(commonMsg.JSE002,['<?php echo __("期間"); ?>'])).show();
          }
        });
        $(".btn_sumisho").click(function(e){
            e.preventDefault();

            document.getElementById("error").innerHTML   = "";                        
            document.getElementById("success").innerHTML = "";

            var date = document.getElementById('period').value;
            var layer_code = document.getElementById('layer_code').value;
            var role_id = document.getElementById('role_id').value;
            var category = document.getElementById('category').value;

            var errorFlag = true;

            if(!checkNullOrBlank(date)) {
                var newbr = document.createElement("div");                      
                var a     = document.getElementById("error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002,['<?php echo __("期間"); ?>'])));
                document.getElementById("error").appendChild(a);                      
                errorFlag = false;                      
            }              
            //if(role_id == 8 || role_id == 5 || role_id == 6 || role_id == 7){
                if(!checkNullOrBlank(layer_code)) {
                    var newbr = document.createElement("div");                      
                    var a     = document.getElementById("error").appendChild(newbr);
                    a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002,['<?php echo __("部署"); ?>'])));
                    document.getElementById("error").appendChild(a);                      
                    errorFlag = false;                      
                }
            //}
            if(!checkNullOrBlank(category)) {
              var newbr = document.createElement("div");                      
                var a     = document.getElementById("error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002,['<?php echo __("カテゴリー"); ?>'])));
                document.getElementById("error").appendChild(a); 
              errorFlag = false;  
            }
            if(errorFlag){
                var newbr = document.createElement("div");                      
                var a     = document.getElementById("success").appendChild(newbr);
                a.appendChild(document.createTextNode(['<?php echo __("データ選択は成功！"); ?>']));
                document.getElementById("success").appendChild(a); 
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->webroot; ?>SampleSelections/add",
                    data: {period: date, layer_code: layer_code, category: category},
                    success: function(data) {

                    }
                });

            }
        });
        $('#period').on('focusout', function() {
            getBA();
        });
        function getBA(){
          var period = $("#period").val();
          var data = [];
          // var selLayerCode = $("#layer_code").val();
          var selLayerCode = "<?php echo $this->Session->read('SESSION_LAYER_CODE'); ?>";
            $.ajax({
                type: "POST",
                url: "<?php echo $this->webroot; ?>SampleSelections/getBa",
                data: {period: period},
                success: function(datas) {
                    data = jQuery.parseJSON(datas);
                    var language = data['language'];
                    $('select#layer_code option[value!=""]').remove();
                    if(data) {
                      var selected = '';
                
                      if(data) {
                        for(var code in data) {
                            var name = code+"/"+data[code];
                            var sel = '';
                            if(selLayerCode == code) sel = "selected";
                            if(code != 'language') $('select#layer_code').append($('<option value="'+code+'" '+sel+'>'+name+'</option>'));
                        }
                      }
                    }
                }
            });
        }
    }); 
</script>

<?php echo $this->Form->create(false, array('url'=>'add',
             'type'=>'post'));
             ?>                                        
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <h3><?php echo __("サンプルチェック");?></h3>
        <hr>
        <div class="success" id="success"></div>
        <div class="error" id="error"><?php echo ($this->Session->check("Message.Error"))? $this->Flash->render("Error") : '';?></div>

        <!-- added by Khin Hnin Myo (show message from message table) -->
        <?php if ($show_msg!=null) {?>    
          <div class="alert alert-success" id="msg">
            <?php echo nl2br(htmlspecialchars($show_msg)); ?>
          </div>
        <?php } ?>  
        
        <div class="form_test">
            <fieldset class="scheduler-border">
                <legend class="scheduler-border"><?php echo __("基本期間");?></legend>
                <div class="form-group row">
                    <div class="col-md-12">
                      <p style="color: blue;padding-left: 15px;"> 
                        <?php echo __("期間、部署、カテゴリを選択し、セット選択ボタンをクリックしてください。"); ?>
                      </p>
                    </div>
                    <div class="form-group col-md-5">
                        <label for="postingDate" class="col-sm-4 col-form-label required">
                      <?php echo __("期間選択");?></label>                                    
                      <?php $period =  $this->Session->read('SAMPLECHECK_PERIOD_DATE');
                        if (!empty($period)) { ?>
                          <div class="col-md-8 input-group monthsPicker">
                            <input type="text" class="form-control monthsPicker" name="period" id="period" value="<?=$period?>"/>
                          <span class="input-group-addon">
                          <span class="glyphicon glyphicon-calendar"></span>
                          </span>
                         </div>
                        <?php } else { ?>
                          <div class="col-md-8 input-group monthsPicker">
                            <input type="text" class="form-control monthsPicker" name="period" id="period" value=""/>
                          <span class="input-group-addon">
                          <span class="glyphicon glyphicon-calendar"></span>
                          </span>
                         </div>
                       <?php } ?>                                     
                    </div>
                    <?php
                      $role_id = $this->Session->read('ADMIN_LEVEL_ID');
                    ?>
                    <input type="hidden" id="role_id" value="<?=$role_id?>">
                    <div class="form-group col-md-5">
                        <label for="salesRepresentative" class="col-sm-3 col-form-label required">
                         <?php echo __("部署選択");?></label>
                         <?php
                          $sample_layer_code =  $this->Session->read('SESSION_LAYER_CODE');
                          $language = $this->Session->read('Config.language');
                            unset($layer_name['no_code']);
                          if (!empty($sample_layer_code)) { ?>
                            <div class="col-md-9">
                                <select  id="layer_code" name="layer_code" class="form-control">
                                  <option value="">--Select Layer Name --</option>
                                  <?php 
                                  foreach ($layer_name as $code => $name):
                                    if ($sample_layer_code == $code) {
                                        $select = 'selected';
                                    } else {
                                        $select = '';
                                    }
                                    ?>
                                    <option value="<?=$code?>" <?php echo $select;?>>
                                    <?php
                                    echo h($code.'/'.$name);
                                    ?>

                                    </option>
                                  <?php endforeach; ?>
                                </select>
                            </div>
                          <?php } else { ?>
                            <div class="col-md-9">
                                <select  id="layer_code" name="layer_code" class="form-control">
                                  <option value="" selected="">--Select Layer Name --</option>
                                  <?php foreach ($layer_name as $code => $name): ?>
                                    <option value="<?=$code?>"><?php
                                    echo h($code.'/'.$name);
                                    ?>

                                    </option>
                                  <?php endforeach; ?>
                                </select>
                            </div>
                          <?php } ?>
                    </div>
                    <?php
                    $sample_category =  $this->Session->read('SAMPLECHECK_CATEGORY');
                    $cat_a_selected = '';
                    $cat_b_selected = '';
                    if($sample_category == 'A') $cat_a_selected = 'selected';
                    else if ($sample_category == 'B') $cat_b_selected = 'selected';
                    ?>
                    <div class="form-group col-md-5">
                        <label for="category" class="col-sm-4 col-form-label required">
                      <?php echo __("カテゴリー");?></label>                                    
                        <div class="col-md-8 input-group">
                          <select  id="category" name="category" class="form-control" style="border-radius:4px;">
                            <option value="">--Select Category --</option>
                            <option value="A" <?php echo $cat_a_selected; ?>>A</option>
                            <option value="B" <?php echo $cat_b_selected; ?>>B</option>
                          </select>
                        </div>                                 
                    </div>
                    <div class="col-md-5 samplecheck" style="text-align: right;padding-right:30px;">
                        <button type="button"  class="btn btn-success btn_sumisho"><?php echo __("設定選択");?> </button>
                    </div>
                </div>
            </fieldset>
        </div>        
    </div>
</div>
<?php
    echo $this->Form->end();
?>