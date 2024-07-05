<?php
echo $this->Form->create(false, array('type' => 'post', 'class' => 'form-inline', 'id' => 'LayerChart', 'name' => 'LayerChart', 'enctype' => 'multipart/form-data'));
?>
<script>
   $(document).ready(function() {
      $(".tree-container").addClass('overflow');
      $("#browser").treeview(test);
   });
   
   function test(element) {
     //console.log($(element));
   }
</script>
<div class="col-md-12 col-sm-12 heading_line_title">
   <legend><h3><?php echo __('部署チャート'); ?></h3></legend>
</div>
<?php if(empty($no_data)) { ?>
   <div class="tree-container">
      <div id="browser" class='root'>
         <span class='title' id="firstNode" style="background-color:#88e3e3 !important;"><?php echo "FINANCIIO" ?></span>
         <div class='level'>
            <?php foreach ($lists[0] as $v1) { ?>
            <div class='item'>
               <span class='title' id="<?php echo explode('/',$v1)[0]; ?>" style="background-color:#E5FFFF !important;"><?php echo $v1; ?></span>
               <div class='level ' style="<?php  if(!empty($lists[1][$v1])) echo 'display: block;';else echo 'display: none;';?>">
                  <?php foreach ($lists[1][$v1] as $v2) { ?>
                  <div class='item'>
                     <span class="title" id="<?php echo explode('/',$v2)[0]; ?>" style="background-color:#00A6A0 !important;"><?php echo $v2; ?></span>
                     <div class='level' style="<?php  if(!empty($lists[2][$v2])) echo 'display: block;';else echo 'display: none;';?>">
                        <?php foreach ($lists[2][$v2] as $v3) { ?>
                        <div class='item'>
                           <span class="title" id="<?php echo explode('/',$v3)[0]; ?>" style="background-color:#FFFFCC !important;"><?php echo $v3; ?></span>
                           <div class='level ' style="<?php  if(!empty($lists[3][$v3])) echo 'display: block;';else echo 'display: none;';?>">
                              <?php foreach ($lists[3][$v3] as $v4) { ?>
                              <div class='item'>
                                 <span class="title" id="<?php echo explode('/',$v4)[0]; ?>" style="background-color:#E7B4A6 !important;"><?php echo $v4; ?></span>
                                 <div class='level ' style="<?php  if(!empty($lists[4][$v4])) echo 'display: block;';else echo 'display: none;';?>">
                                    <?php foreach ($lists[4][$v4] as $v5) { ?>
                                    <div class='item'>
                                       <span class="title" id="<?php echo explode('/',$v5)[0]; ?>" style="background-color: #edb879 !important;"><?php echo $v5; ?></span>
                                       <div class='level ' style="<?php  if(!empty($lists[5][$v5])) echo 'display: block;';else echo 'display: none;';?>">
                                          <?php foreach ($lists[5][$v5] as $v6) { ?>
                                          <div class='item'>
                                             <span class="title" id="<?php echo explode('/',$v6)[0]; ?>" style="background-color:#e4c19a !important;"><?php echo $v6; ?></span>
                                          </div>
                                          <?php } ?>
                                       </div>
                                    </div>
                                    <?php } ?>
                                 </div>
                              </div>
                              <?php } ?>
                           </div>
                        </div>
                        <?php } ?>
                     </div>
                  </div>
                  <?php } ?>
               </div>
            </div>
            <?php } ?>
         </div>
      </div>
   </div>
<?php }else {?>
   <div class="no-data"><?php echo ($no_data); ?></div>
<?php } ?>
</br></br></br>
<?php
echo $this->Form->end();
?>