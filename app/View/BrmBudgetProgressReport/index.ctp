<style>
table.table-bordered{
	width: 100%;
}
thead{
    background-color: #D5EADD;
}
table.table-bordered thead tr th {
    text-align: center;
    vertical-align: middle;
  }
table thead tr td{
	padding-top:10px;
	padding-bottom:10px;
}
table thead tr td, table tr td.textCenter{
	text-align: center;
}
table tr td{
	border : 1px solid gray;
	padding-left:3px;
	padding-top:5px;
	padding-bottom:5px;
}
.gray{
	color:gray;
}
.content{
	padding-left:15px;
	padding-right:15px;
}
.totalLines{
	color:green;
}
.notApprove{
	background-color: yellow;
}

</style>
<script>
    $(document).ready(function(){
         
      /* float thead */
      if($('#tbl_budget_progress').length > 0) {
        var $table = $('#tbl_budget_progress');
        $table.floatThead({
              position: 'absolute'
        });
      }
      
	});

    function ApproveData(head_dept_id){
		
        $.confirm({
            title: "<?php echo __('承認の確認'); ?>",
            icon: 'fas fa-exclamation-circle',
            type: 'green',
            typeAnimated: true,
            animateFromElement: true,
            animation: 'top',
            draggable: false,
            content: "<?php echo __('全行を承認してよろしいですか。'); ?>",
            buttons: {   
                ok: {
                    text: "<?php echo __('はい'); ?>",
                  btnClass: 'btn-info',
                    action: function(){
					 loadingPic();
					 
					$('#indexForm').append('<input type="hidden" name="head_dept_id" value="'+head_dept_id+'" id="head_dept_id">');
                    document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetProgressReport/DataApprove";
                    document.forms[0].method = "POST";
                    document.forms[0].submit();
                    return true;

                    }
                },   
            cancel : {
                text: "<?php echo __('いいえ'); ?>",
                   btnClass: 'btn-default',
                cancel: function(){

                    }
                }
            },
           theme: 'material',
           animation: 'rotateYR',
           closeAnimation: 'rotateXR'
        });
    }

    function ApproveCancelData(head_dept_id){
        $.confirm({
            title: "<?php echo __('承認の確認をキャンセル'); ?>",
            icon: 'fas fa-exclamation-circle',
            type: 'green',
            typeAnimated: true,
            animateFromElement: true,
            animation: 'top',
            draggable: false,
            content: "<?php echo __('全行を承認キャンセルしてよろしいですか。'); ?>",
            buttons: {   
                ok: {
                    text: "<?php echo __('はい'); ?>",
                  btnClass: 'btn-info',
                    action: function(){
					loadingPic();
					$('#indexForm').append('<input type="hidden" name="head_dept_id" value="'+head_dept_id+'" id="head_dept_id">');
                    document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetProgressReport/DataApproveCancel";
                    document.forms[0].method = "POST";
                    document.forms[0].submit();
                    return true;
                    }
                },   
            cancel : {
                text: "<?php echo __('いいえ'); ?>",
                   btnClass: 'btn-default',
                cancel: function(){

                    }
                }
            },
           theme: 'material',
           animation: 'rotateYR',
           closeAnimation: 'rotateXR'
        });
    }

	function loadingPic() { // function expression closure to contain variables
		var ua = window.navigator.userAgent;
		var msie = ua.indexOf("MSIE ");
		
		if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet 
		 {
			//alert("ie");
			var el = document.getElementById('imgLoading'); 
			var i = 0;
				var pics = [ "<?php echo $this->webroot; ?>img/loading1.gif",
							 "<?php echo $this->webroot; ?>img/loading2.gif",
							 "<?php echo $this->webroot; ?>img/loading3.gif" ,
							 "<?php echo $this->webroot; ?>img/loading4.gif" ];
			 

				function toggle() {
						el.src = pics[i];           // set the image
						i = (i + 1) % pics.length;  // update the counter
				}
				setInterval(toggle, 250);
				$("#overlay").show();
			

		}else{
			//alert("other");
			// el.src = "<?php echo $this->webroot; ?>img/loading.gif";
			$("#overlay").show();
		}
	
	} 

</script>

<?php echo $this->Form->create(false,array(
 'type'=>'post',
 'class'=>'form-horizontal',                      
 'enctype' => 'multipart/form-data')); 
 ?>
<div id="overlay">
	<span class="loader"></span>
</div>
<div>
	<h3 style="margin-left: 10px;"><?php echo __($usedName['formName']); ?></h3>
	<hr>
	<div class="success" id="success"><?php echo ($this->Session->check("Message.BudgetProgressReportSuccess"))? $this->Flash->render("BudgetProgressReportSuccess") : '';?></div>
	<div class="error" id="error"><?php echo ($this->Session->check("Message.BudgetProgressReportError"))? $this->Flash->render("BudgetProgressReportError") : '';?></div>
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-sm-12">
					<label for="term" class="col-sm-4 col-form-label">
						<?php echo __($usedName['term']);?>
					</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="term" name="term" value="<?php echo $term; ?>" readonly="readonly"/>
					</div>
				</div>
			</div>
		</div>
	</div>
	

	
	<?php if($no_data == "no_data"): ?>
        <div class="col-sm-12">
            <p class="no-data"><?php echo __("データが見つかりません！"); ?></p>
        </div>
    <?php else: ?>
	<div class="content table-responsive">
		<div class="msgfont" id="total_row">
            <?=$count;?>
          </div>
          <div class="table-responsive" style="margin-bottom: 100px;">
			<table class="table table-bordered" id="tbl_budget_progress">
				<thead>
					<tr>
						<th><?php echo __($usedName['headQuarter']); ?></th>
						<th><?php echo __($usedName['department']); ?></th>
						<th><?php echo __($usedName['baName']) ?></td>
						<th><?php echo __($usedName['HQapproveDate']); ?></th>
						<th><?php echo __($usedName['HQauthorizer']); ?></th>
						<th><?php echo __($usedName['approveDate']); ?></th>
						<th><?php echo __($usedName['authorizer']); ?></th>	
						<th><?php echo __($usedName['tradingPlan']); ?></th>
						<th><?php echo __($usedName['manpowerPlan']); ?></th>
						<th><?php echo __($usedName['forecast']).'/'.__($usedName['budget']).' '.__($usedName['planForm']); ?></th>
						<th><?php echo __($usedName['f&bDifference']); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php
						foreach($ba_list as $each_ba){//pr($each_ba);
							if (in_array($each_ba['hq_name'], $restrict_hqs) ) $showTradingPlanLink = false;
							else $showTradingPlanLink = true;
							
							$flag = 0;
							$termRange = range($each_ba['budget_start_yr'], $each_ba['budget_end_yr']);
							$termName = $each_ba['budget_start_yr'].'-'.$each_ba['budget_end_yr'];
							$headDept = $each_ba['hq_name'];
							$layer_code = $each_ba['layer_code'];
							$baName = $each_ba['ba_name'];
							$headDeptId = $each_ba['hq_id'];
							$termId = $each_ba['term_id'];
							$dept = $each_ba['dept_name'];

						?>
						<tr class="<?php echo $value['tbl_budget_log']['id']; ?>">
							<td><?php echo $each_ba['hq_name']; ?></td>
							<td><?php echo $each_ba['dept_name'];?></td>
							<td><?php echo $each_ba['ba_name'];?></td>
							<td class="textCenter <?php if($each_ba['hq_approve_date'] == '') echo 'notApprove'?>"><?php echo $each_ba['hq_approve_date']; ?></td>
							<td class="<?php if($each_ba['hq_approver'] == '') echo 'notApprove'?>"><?php echo $each_ba['hq_approver']; ?></td>
							<td class="textCenter <?php if($each_ba['ba_approve_date'] == '') echo 'notApprove'?>"><?php echo $each_ba['ba_approve_date']; ?></td>
							<td class="<?php if($each_ba['ba_approver'] == '') echo 'notApprove'?>"><?php echo $each_ba['ba_approver']; ?></td>
							<td class="<?php if($each_ba['hq_approver'] == '' && $each_ba['ba_approver'] == '') echo 'notApprove'?>">
							<?php
							if(($each_ba['ba_approver'] != '' || $each_ba['hq_approver'] != '') && $showTradingPlanLink){
								foreach($termRange as $year){
								?>
									<a href="<?php echo $this->webroot; ?>BrmTradingPlan/?year=<?php echo $year?>&code=<?php echo $layer_code;?>&hq=<?php echo $headDeptId;?>&term=<?php echo $termId; ?>"><?php echo $year; ?></a><br>
								<?php
								}

							}
							 ?>
							 </td>
							<td class="<?php if($each_ba['hq_approver'] == '' && $each_ba['ba_approver'] == '') echo 'notApprove'?>">
							<?php
							if($each_ba['ba_approver'] != '' || $each_ba['hq_approver'] != ''){
								foreach($termRange as $year){
								?>
									<a href="<?php echo $this->webroot; ?>BrmManpowerPlan/?year=<?php echo $year?>&termName=<?php echo $termName; ?>&code=<?php echo $layer_code;?>&hq=<?php echo $headDeptId;?>"><?php echo $year; ?></a><br>
								<?php
								}

							}
							?>
							
							</td>
							<td class="<?php if($each_ba['ba_approver'] == '' && $each_ba['hq_approver'] == '') echo 'notApprove'?>">
							<?php
							if($each_ba['ba_approver'] != '' || $each_ba['hq_approver'] != ''){
								foreach($termRange as $key=>$year){
									if($key == 0) {
										$type = $usedName['forecast'];
										$budget = 'forecast';
									}else{
										
										$type = $usedName['budget'];
										$budget = 'budget';
									}
									?>
									<a href="<?php echo $this->webroot; ?>BrmBudgetPlan/?year=<?php echo $year?>&<?php echo $budget; ?>&code=<?php echo $layer_code;?>&hq=<?php echo $headDeptId;?>&term=<?php echo $termId; ?>"><?php echo $year; ?>
									<span class='gray'>(
									<?php
									echo __($type);
									?>
									)
									</span>
									</a><br>
									<?php
								}

							}
							 ?>
							</td>
							<td class="<?php if($each_ba['ba_approver'] == '' && $each_ba['hq_approver'] == '') echo 'notApprove'?>">
							<?php 
							if($each_ba['ba_approver'] != '' || $each_ba['hq_approver'] != ''){
							?>
							<a href="<?php echo $this->webroot; ?>BrmForecastBudgetDifference/?code=<?php echo $layer_code;?>&hq=<?php echo $headDeptId;?>&term=<?php echo $termId; ?>" ><?php echo __($usedName['f&bDifference']); ?></a>
							<?php } ?>
							</td>
						</tr>
						<?php
						}
					?>
				</tbody>
			</table>
			<?php echo $levl16; ?>
		  </div>
		</div>
	</div>
	<?php endif ?>	
</div>
<?php echo $this->Form->end();?>