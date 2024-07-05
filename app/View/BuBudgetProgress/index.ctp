<?php
	echo $this->Form->create(false,array('type'=>'post', 'id' => 'bu_budget_progress', 'enctype'=> 'multipart/form-data', 'autocomplete'=>'off'));
?>
<style type="text/css">
    .register_container{
        padding: 0px 30px;
    }
    .tbl-wrapper{
		margin-bottom:20px;
	}
	.tbl-bu-analysis {
		margin-top: 20px;
		margin-bottom: 20px;
		width: 100%;
	}
	.tbl-bu-analysis tbody td.name-field .arrow:before {
		content: "▲";
		width: 20px;
	    display: inline-block;
	    height: 20px;
	    line-height: 20px;
	    background-color: #eee;
	    text-align: center;
	    position: absolute;
	    top: 0;
	    left: 0;
	}
	.tbl-bu-analysis tbody td.name-field.show-row .arrow:before {
		content: "▼";
	}
	.tbl-bu-analysis tbody td.name-field {
		position: relative;
		/* padding-left: 25px !important; */
        vertical-align: top;
	}
	.tbl-bu-analysis tbody td {
		border-left: 1px solid #A4A4A4 !important;
		/* border-bottom: 1px solid #A4A4A4 !important; */
	}
	.tbl-bu-analysis th {
		text-align: center;
		border-bottom: 1px solid #A4A4A4;
		border-right: 1px solid #A4A4A4;
	}
	.tbl-bu-analysis td{
		min-width: 50px;
		padding: 5px;
		border-top: 1px solid #A4A4A4;
		border-right: 1px solid #A4A4A4;
	}
	.tbl-bu-analysis tr.title td, .bRight.layer{
		text-align: center;
	}
	.tbl-bu-analysis th, .tbl-bu-analysis .name-field{
		padding: 5px;
		white-space: nowrap;
	}
	.tbl-bu-analysis th.month {
		width: 80px;
	}
 	.tbl-bu-analysis th.total {
		width: 90px;
	}
	.number {
		text-align: right;
	}
	
	.tbl-bu-analysis tbody {
		border-left: 3px solid #444;
		border-bottom: 2px solid #444;
		border-right: 3px solid #444;
	}
	.bold-border-btm {
		border-bottom: 2px solid #444;
	}
	.bold-border-top {
		border-top: 2px solid #444;
	}
	td.bold-border-lft,th.bold-border-lft {
		border-left: 2px solid #444 !important;
	}
	tr.bold-border-lft {
		border-left: 3px solid #444 !important;
	}
	.bold-border-rgt {
		border-right: 2px solid #444 !important;
	}
	.bdl-solid {
		border-left: 1px solid #A4A4A4 !important;
	}
	.b-none{
		border: none !important;
	}
	.bb-none{
		border-bottom: none !important;
	}
	.bt-none{
		border-top: none !important;
	}
	.negative {
		color: #f31515;
	}

	.disable, .freeze {
		cursor: none;
		pointer-events: none;
		background-color: #F9F9F9;
	}
	.talign-left {
		text-align: left !important;
	}
	.fl-scrolls {
	    z-index: 1;
		margin-bottom:40px;
	}
	.clone-column-table-wrap table.tbl-bu-analysis.bold-border, 
	.clone-column-head-table-wrap table.tbl-bu-analysis.bold-border{
		width: unset !important;
	}
	.clone-head-table-wrap{
		top: -20px !important;
		height: 181px !important;
	}
	.table2:first-of-type + .clone-head-table-wrap
	{
		top: -20px !important;
		height: 162px !important;
	}
	#overlay {
		display: none;
		z-index: 100000;
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0,0,0,0.2);
	}
	#overlay img {
		position: relative;
		top: 40%;
		left: 45%;
	}

	#load{
		z-index: 10000;
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0,0,0,0.2);
	}

	.col-level-1{

		max-width:150px;
	    word-wrap:break-word;
	}
    .row-level-1 .col-level-2{
        padding-left:20px;
    }
	tbody tr td.amount{
		min-width: 50px;
		text-align:right;
	}
	tr.bold-border-lft td{
		border-bottom: 1.5px solid #444 !important;
	}
	.bRight{
		border-right: 2px solid #444 !important;
	}
	select{
		height: 34px;
	}
	.amount.percent{
		min-width: 100px;
	}
	table th{
		height: 4rem !important
	}
	.row-level-2 .name-field{
		font-weight: bold;
	}

	.btn-complete {
		background-color: #00a6a0 !important;
		padding: 5px;
		color: #ffffff;
		/* transition: all 0.5s ease 0s; */
		background: linear-gradient(to right, #00a6a0 50%, #2a807f 50%);
		background-size: 200% 100%;
		background-position: 0 0;
		transition: background-position 0.3s ease-out;
		border: none;
		border-radius: 5px;
		line-height: 1em;
		min-width: 100px;
	}
	.btn-complete:disabled{
		opacity: 0.5;
	}
	.btn-complete:not([disabled]):hover {
		border: none;
		background-position: -100% 0;
		animation-name: hover-eff;
		animation-duration: 0.4s;
		animation-timing-function: linear;
		animation-iteration-count: 1;
	}
	@keyframes hover-eff {
		50% {
			-webkit-transform: scale(0.8);
			transform: scale(0.9);
			transition: 0.3s ease-out;
		}
		100% {
			-webkit-transform: scale(1);
			transform: scale(1);
			transition: 0.3s ease-out;
		}
	}

	.layer-cell {
		vertical-align: middle !important;
	}
	.item-name {
		text-align: center;
	}
	.success {
		background: var(--menutxthover);
		padding: 1rem 2rem;
		margin-bottom: 30px;
		font-family: var(--font_family);
		color: #035956;
		border-radius: 0.5rem;
	}

	.error {
		background: #ffd2d2;
		color: #ff3333;
		padding: 1rem 2rem;
		margin-bottom: 30px;
		font-family: var(--font_family);
		border-radius: 0.5rem;
	}
	.bg-lg-green {
		background-color: #d9fff5;
	}
	.bg-lg-red {
		background-color: #ffd2d2;
	}
</style>
<?php 
	$language   = $this->Session->read('Config.language');
?>
<div id="overlay">
	<span class="loader"></span>
</div>
<div id="load"></div>
<div id="contents"></div>
<div class="content register_container" style="font-size: 1em !important;">
    <div class="row">
        <div class="col-md-8 col-sm-8">				
            <h3>
                <?php  echo __("進捗管理(ビジネス総合分析表)"); ?>			
            </h3>
        </div>
        <div class="col-md-12 budget-form-hr" style="margin-top: -10px;">
			<hr>
		</div>
    </div>
	<!-- Error Area -->
	<div>
        <div class="success"><?php echo $this->Flash->render("success")?></div>
        <div class="error"><?php echo $this->Flash->render("error"); ?></div>
    </div>
    <!-- end Error Area  -->
	<div class="form-row">
		<div class="form-group col-md-3">
			<input id="target_yr" name="target_yr" class="form-control" value="<?php echo  $_SESSION['TERM_NAME']; ?>" disabled/>
		</div>
		<div class="form-group col-md-3">
			<input id="target_yr" name="target_yr" class="form-control" value="<?php echo  $_SESSION['BudgetTargetYear']; ?>" disabled/>
		</div>
	</div>
	<?php 
		$colSpan = (count($layerTypeOrder));
	?>
    <div class="form-group row">
        <div class="col-lg-12 col-md-12">
            <div class="row table-responsive tbl-wrapper">
				<input type="hidden" name="layer_code" id="layer_code">
				<input type="hidden" name="page_name" id="page_name">
				<?php  if(!empty($prepare_layer)) { ?>
					<table class="tbl-bu-analysis bold-border table1" id="tbl_bu">
						<thead class="check_period_table bold-border-btm" style="position: sticky;top:0;">
							<tr class="bold-border-top bold-border-lft bold-border-rgt">
								<th colspan="<?php echo $colSpan; ?>" class="bRight layer" style="min-width: 80px" ><?php echo __("部署"); ?></th>
								<th class="bRight" style="min-width: 50px" ><?php echo __("ステータス"); ?></th>
								<th colspan="" class="bRight" style="min-width: 50px" ><?php echo __("承認者"); ?></th>
								<th colspan="" class="bRight" style="min-width: 50px" ><?php echo __("承認日"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
								if(sizeof($prepare_layer) > 0) {
									foreach($prepare_layer as $pkey => $pvalue) {
										$colLevel = (count($lOrder) - 1) - 1;
										foreach($pvalue as $key => $value) {
											$name = $language == 'jpn' ? $value['name_jp'] : $value['name_en'];
											$parent_id = $value['parent_id'];
											$layer_code = $value['layer_code'];
											$child_complete = $value['child_complete'];
											$all_child_complete = $value['all_child_complete'];
											$cancel_complete = $value['cancel_complete'];
											$cancel_flag = $value['cancel_flag'];
											$completePermission = $value['complete_permission'];
							?>
									<tr class="row-level" type_order="<?php echo $value['type_order'];?>">
										<?php
										$colspan_cnt = 5;
										for ($i=1; $i < $value['type_order']-1 ; $i++) { 
										?>
											<td class="blank-cell"></td>
										<?php
										$colspan_cnt--;
										}
										?>
										<td colspan="<?php echo $colspan_cnt; ?>" class = "bb-none layer layer-cell" ><?php echo "$name"; ?></td>
										<td class="item-name <?= $cancel_flag == 1 ? 'bg-lg-red' : ($all_child_complete==1 ? '' : ($all_child_complete==2 || $child_complete==2 ? 'bg-lg-green' : '')) ?>">
											<?php
												if(($cancel_complete == 0 && $all_child_complete == 2) || $child_complete == 2){
											?>
												<?php if($all_child_complete != 1) echo __('入力完了'); ?>
											<?php
												}
												if($completePermission == 1 && ($all_child_complete == 1 || ($all_child_complete == 1 && $cancel_complete == 1))){
											?>
												<button type="button" class="btn-complete" onClick='completeClick("<?php echo $layer_code ?>",<?php echo $parent_id ?>, "<?php echo 'BuBudgetProgress';?>")'>
													<?php echo __('入力確定');?>
												</button>
											<?php 
												}
												// echo "comPer = $completePermission, allChild = $all_child_complete, cancelComp = $cancel_complete";
												if($cancel_flag == 1){
											?>
												<?php if($value['isChild'] || $completePermission != 1 || $all_child_complete != 1)  echo __('入力解除'); ?>
											<?php } ?>
										</td>
										<td class="item-name <?= $cancel_flag == 1 ? 'bg-lg-red' : ($all_child_complete==1 ? '' : ($all_child_complete==2 || $child_complete==2 ? 'bg-lg-green' : '')) ?>"><?php if($child_complete == 2 || $all_child_complete || $cancel_flag == 1) echo $value['user_name'];?></td>
										<td class="bRight item-name <?= $cancel_flag == 1 ? 'bg-lg-red' : ($all_child_complete==1 ? '' : ($all_child_complete==2 || $child_complete==2 ? 'bg-lg-green' : '')) ?>"><?php if($child_complete == 2 || $all_child_complete || $cancel_flag == 1) echo $value['updated_date'];?></td>
								</tr>
								<?php
									}
								?>
								<!-- <hr/> -->
							<?php
									}
								}
							//echo '<pre>';print_r($emp);echo '</pre>';
							?>
						</tbody>
					</table>
				<?php } else { ?>
					<div id="err" class="no-data">
						<?php echo ($errormessage); ?>
					</div>
				<?php } ?>
            </div>
        </div>
	</div>
</div>
<?php
	echo $this->Form->end();
?>
<script>
	$(document).ready(function () {
		$(window).on('beforeunload', () => {
			loadingPic()
		});

		document.onreadystatechange = function () {
			var state = document.readyState
			if (state == 'interactive') {
				document.getElementById('contents').style.visibility="hidden";

			} else if (state == 'complete') {
					setTimeout(function(){
					document.getElementById('interactive');
					document.getElementById('load').style.visibility="hidden";
					document.getElementById('contents').style.visibility="visible";
					
				  },1000);
			}
		}

		$(".table-responsive.tbl-wrapper").floatingScroll();
		// floating table head
		var $table = $('table.tbl-bu-analysis');
		$table.floatThead();
	});
	function completeClick(layer, parent_id, page_name) {
		document.querySelector(".error").innerHTML   = "";
		document.querySelector(".success").innerHTML = "";
		let parentID = JSON.stringify(parent_id);
		prepareData = { 
			layer_code: layer,
			parent_id: parentID
		};
		$('#layer_code').val(JSON.stringify(prepareData));
		$('#page_name').val(page_name);

		$.confirm({
            title: "<?php echo __('保存確認'); ?>",
            icon: 'fas fa-exclamation-circle',
            type: 'green',
            closeIcon: true,
            typeAnimated: true,
			columnClass: 'medium',
            animateFromElement: true,
            animation: 'top',
            draggable: false,
            content: "<?php echo __("データの入力を完了してもよろしいですか?"); ?>",
            buttons: {
                ok: {
                    text: "<?php echo __('はい'); ?>",
                    btnClass: 'btn-info',
                    action: function() {
                        loadingPic();
                        document.forms[0].action = "<?php echo $this->webroot; ?>BuBudgetProgress/completeBudget";
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
		
		scrollText();           
	}

	function scrollText(){
		var tes = $('#error').text();
		var tes1 = $('.success').text();
		if(tes) $("html, body").animate({ scrollTop: 0 }, "slow");
		if(tes1) $("html, body").animate({ scrollTop: 0 }, "slow");
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