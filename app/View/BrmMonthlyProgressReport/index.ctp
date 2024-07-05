<style>

  table, th {
    text-align: center;
  }
  #total_row {
    padding-top: 50px;
  }

</style>
<script>
    $(document).ready(function(){
         
      /* float thead */
      if($('#tbl_monthly_progress').length > 0) {
        var $table = $('#tbl_monthly_progress');
        $table.floatThead({
              position: 'absolute'
        });
      }
      
});
</script>
<div class="container register_container">
    <div class="row">
        <div class="col-md-12 col-sm-12 heading_line_title">

            <h3><?php echo __("月次業績報告進捗");?></h3>
            <hr>
            <?php echo $this->Form->create(false,array('url'=>'add',
             'type'=>'post',
             'class'=>'form-horizontal',                      
             'enctype' => 'multipart/form-data')); 
             ?>

            <div class="row form-group">
                <div class="col-md-5">
                  <label class="col-md-4 "><?php echo __("期間");?></label>
	               <div class="col-md-8">
                    <input class="form-control register" type="text" id="term" name="term" value="<?php echo $budget_term; ?>" readonly>
                  </div>
                </div>                       
            </div><!--end row form-group-->
            <div class="row form-group">
                <div class="col-md-5">
                  <label class="col-md-4 "><?php echo __("対象月");?></label>
                  <div class="col-md-8">
                     <input class="form-control register" type="text" id="target_month" name="target_month" value="<?php echo $target_month; ?>" readonly>
                  </div>
                </div>                       
            </div><!--end row form-group-->

              <?php if(!empty($layer_list)){ ?>
                  <div class="msgfont" id="total_row">
                    <?=$count;?>
                  </div>
                  <div class="table-responsive" style="margin-bottom: 100px;">
                      <table class="table table-bordered" id="tbl_monthly_progress">
                        <thead>
                          <tr>
                            <th><?php echo __("期間");?></th>
                            <th><?php echo __("対象月");?></th>
                            <th><?php echo __($layer_type[SETTING::LAYER_SETTING['topLayer']]);?></th>
                            <th><?php echo __($layer_type[SETTING::LAYER_SETTING['middleLayer']]);?></th>
                            <th><?php echo __($layer_type[SETTING::LAYER_SETTING['topLayer']]." ");echo __("承認");?></th>
                            <th><?php echo __($layer_type[SETTING::LAYER_SETTING['middleLayer']]." ");echo __("承認");?></th>
                          </tr>
                        </thead>
                        <tbody>
                      <?php
                      if (isset($layer_list)) {
                            foreach ($layer_list as $each_ba) {
                              $head_id = $each_ba['Layer']['hlayer_code'];
                              $dept_id = $each_ba['second_layer']['dlayer_code'];
                              echo "<tr style='text-align: left'>";
                              echo "<td>".h($budget_term)."</td>";
                              echo "<td>".h($target_month)."</td>";
                              echo "<td>".
                              h($each_ba['Layer']['hlayer'])."</td>";
                              echo "<td>".
                              h($each_ba['second_layer']['dlayer'])."</td>";
                              if (isset($dept_approved[$head_id][$dept_id])) {

                                $date=date_create($dept_approved[$head_id][$dept_id]);
                                $dept_approved_date = date_format($date,"Y/m/d");
                                echo "<td>".
                                h($dept_approved_date)."</td>";
                              } else {
                                echo "<td style='background-color: yellow'></td>";
                              }
                              if (isset($head_approved[$head_id])) {

                                $date=date_create($head_approved[$head_id]);
                                $head_approved_date = date_format($date,"Y/m/d");
                                echo "<td>".
                                h($head_approved_date)."</td>";
                              } else {
                                echo "<td style='background-color: yellow'></td";
                              }
                              echo "</tr>";
                            }
                          }
                      ?>      
                        </tbody>
                      </table>
                  </div>
              <?php }else{?>
              <div class="row">
                <div class="col-sm-12">
                  <p class="no-data"><?php echo $no_data; ?></p>
                </div>
              </div>
              <?php } ?>

    <?php echo $this->Form->end(); ?>
    </div>
  </div>
</div>