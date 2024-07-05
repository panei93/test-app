<?php
	echo $this->Form->create(false,array('type'=>'post', 'id' => 'budgetresultdiff', 'enctype'=> 'multipart/form-data'));
?>
<style type="text/css">
	.table_bg_red thead {
		background-color: #fcf2ef;
	}
	#b_r_compare, th, td {
		height: 20px !important;
		letter-spacing: -1px;

	}
	.one {
		width: 150px;
	}
	.negative {
		color: #f31515;
		text-align: right !important;
	}
	.string {
		text-align: left !important;
		white-space: nowrap;
	}
	.number, #achieve {
		text-align: right !important;
	}
	.tabcontent {
		padding: 6px 12px;
		border: 1px solid #ccc;
		/*border-top: none;*/
	}
	.back{
		float: right;
		padding-right: 10px;
	}
</style>
<script type="text/javascript">				
	$(document).ready(function() {
		// MakeNegative();	
		$('select').amsifySelect();

		$("button.amsify-select-clear").click(function(){
			
			document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetResultDifference/SearchBudgetResult";
			document.forms[0].method = "POST";
			document.forms[0].submit();
			return true;
		   
		});

		var defaultUnit = 1000000;
		CalculateWithUnit(defaultUnit);
		$("input[name='unit']").on('input', function(e) {
			alert('when');
	        $(this).val($(this).val().replace(/[^0-9]/g, ''));
			var unit = $(this).val();
			CalculateWithUnit(unit);
	    });
		

	});
	function CalculateWithUnit(unit) {
		var defaultUnit = 1000000;
		$('td.number, td:not([id="achieve"]).negative').each(function(index, value) {
			var num = parseInt(this.id);if(this.id == 'achieve'){
				//console.log("num - "+this.id);
			}
			var result_with_unit = (unit != '')? (num/unit) : (num/defaultUnit);
			$(this).text((result_with_unit.toFixed()).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
		});
		MakeNegative();
	}
	function MakeNegative() {

		TDs = document.getElementsByTagName('td');
		for (var i=0; i<TDs.length; i++) {

			var temp = TDs[i];

			if (temp.firstChild.nodeValue.indexOf('-') == 0) {

				temp.innerHTML = temp.innerHTML.replace('-','▲');

				temp.className = "negative";
				
			}

		}
	}
	function scrollText(){
    	var tes1 = $('#error').text();
    	var tes2 = $('#success').text();
		if(tes1){
			$("html, body").animate({ scrollTop: 0 }, "slow");				
		}
		if(tes2){
			$("html, body").animate({ scrollTop: 0 }, "slow");				
		}
   	}
	function clickExcelDownload() {
		
		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = "";
		document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetResultDifference/DownloadBudgetResult";
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;
	}
	function clickBackToBottomLayer(back_layer_code,back_layer_name){
		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = "";
		document.getElementById("hid_hq").value = back_layer_code;
		document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetResultDifference/?tab="+back_layer_name+"&layer_code="+back_layer_code;
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;
	}
	function clickBackToMiddleLayer(back_layer_code,back_layer_name){
		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = "";
		document.getElementById("hid_hq").value = back_layer_code;
		document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetResultDifference/?tab="+back_layer_name+"&layer_code="+back_layer_code;
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;
	}
	function clickHQ(layer_code) {
	    document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = "";
		document.getElementById("hid_hq").value = layer_code;
		var layer_name 				= document.getElementById("tab").value;
        var top_layer_name     		= document.getElementById("t_layer_name").value;
		var middle_layer_name     	= document.getElementById("m_layer_name").value;
		var bottom_layer_name     	= document.getElementById("b_layer_name").value;
        if(layer_name == top_layer_name){
            var next_layer_name = middle_layer_name;
        }else if(layer_name == middle_layer_name){
            var next_layer_name = bottom_layer_name;
        }else if(layer_name == bottom_layer_name){
            var next_layer_name = "Logistic";
        }		
		document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetResultDifference/?tab="+next_layer_name+"&layer_code="+layer_code;
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;

	}
	function clickIndex() {   		
	   	document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetResultDifference/index";
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;
	}
	function clickSearch() {
		document.getElementById("error").innerHTML   = "";
		document.getElementById("success").innerHTML = "";
		document.forms[0].action = "<?php echo $this->webroot; ?>BrmBudgetResultDifference/SearchBudgetResult";
		document.forms[0].method = "POST";
		document.forms[0].submit();               							
		return true;				
	}
	function loadingPic() { // function expression closure to contain variables
		var ua = window.navigator.userAgent;
   		var msie = ua.indexOf("MSIE ");
   		
		if(msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet 
	   {
	    
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

<div id="overlay">
	<span class="loader"></span>
</div>
<div>
	<div class="row" style="font-size: 0.95em;">
		<div class="col-md-12 col-sm-12 heading_line_title">
			<?php 
				$year = date("Y",strtotime($target_month));
				$month = date("m",strtotime($target_month));

			?>
			<h3>
				<?php echo (__('ケミカル').' '.$year.' '.__('年').' '.$month.' '.__('月').' '.__('度実績・対予算比較表')); ?>
			</h3>
			
			<hr>
		</div>
		<!-- start show error msg and success msg from controller  -->
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.BudgetAndResultSuccess"))? $this->Flash->render("BudgetAndResultSuccess") : '';?></div>						
			<div class="error" id="error"><?php echo ($this->Session->check("Message.BudgetAndResultError"))? $this->Flash->render("BudgetAndResultError") : '';?></div>									
		</div>
		<!-- end show error and success msg from controller -->
	</div>
	<div>
		<?php if($tab == $middle_layer_name):?>
				<a href="#" class = "back" onclick="clickIndex();"><?php echo __("$top_layer_name"." に戻る≫"); ?></a>
		<?php elseif($tab == $bottom_layer_name): //pr('middle');?>
				<a href="#" class = "back" onclick="clickBackToMiddleLayer('<?php echo $this->Session->read("BACK_TO_MIDDLE_LAYER");?>','<?php echo $this->Session->read("BACK_TO_MIDDLE_LAYER_NAME");?>');"><?php echo __("$middle_layer_name"." に戻る≫"); ?></a>
				<a href="#" class = "back" onclick="clickIndex();"><?php echo __("$top_layer_name"."本部に戻る≫"); ?></a>		
		<?php elseif($tab != $top_layer_name): ?>
			<a href="#" class = "back" onclick="clickBackToBottomLayer('<?php echo $this->Session->read("BACK_TO_BOTTOM_LAYER");?>','<?php echo $this->Session->read("BACK_TO_BOTTOM_LAYER_NAME");?>');"><?php echo __("$bottom_layer_name"." に戻る≫"); ?></a>
			<a href="#" class = "back" onclick="clickBackToMiddleLayer('<?php echo $this->Session->read("BACK_TO_MIDDLE_LAYER");?>','<?php echo $this->Session->read("BACK_TO_MIDDLE_LAYER_NAME");?>');"><?php echo __("$middle_layer_name"." に戻る≫"); ?></a>
			<a href="#" class = "back" onclick="clickIndex();"><?php echo __("$top_layer_name"."本部に戻る≫"); ?></a>		
		
		<?php endif ?>
		<?php //pr('no where');?>
		&nbsp;&nbsp;
	</div>
	<!-- start hidden field -->
	<input type="hidden" name="hid_hq" id="hid_hq" value="">	
	<input type="hidden" name="txt_hid" id="txt_hid" value="<?php echo($id_string); ?>">
	<input type="hidden" name="tab" id="tab" value="<?php echo($tab); ?>">
	
    <!--SST 28.10.2022 -->
	<input type="hidden" name="t_layer_name" id="t_layer_name" value="<?php echo $this->Session->read("TOP_LAYER_NAME");?>">
	<input type="hidden" name="m_layer_name" id="m_layer_name" value="<?php echo $this->Session->read("MIDDLE_LAYER_NAME");?>">
	<input type="hidden" name="b_layer_name" id="b_layer_name" value="<?php echo $this->Session->read("BOTTOM_LAYER_NAME");?>">
	<input type="hidden" name="hid_parent_layer" id="hid_parent_layer" value="<?php echo($parent_layer_code); ?>">	

	<!-- end hidden field -->
	<div class="tabcontent">
		<br><br>
		<div class="form-group row">
			<div class="col-sm-6">
				<label class="col-sm-4 col-form-label">
					<?php echo __('期間');?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="term_name" value="<?php if($term_name != "") echo($term_name); ?>" readonly="readonly"/>
				</div>
			</div>
			<?php if(!empty($select_data)) {?>
			<div class="col-sm-6 noshow">
				<label class="col-sm-4 col-form-label">
					<?php echo __($tab);?>
				</label>
				<div class="col-sm-8">
					<select multiple="multiple" name="multi_select_data[]" id="hq_select" class="form-control" value="<?php if(!empty($id_array)) echo($id_string); ?>">
						<option></option>
						<?php
						foreach($select_data as $id => $name){ ?>
						<option value="<?php echo $id;?>" <?php if(in_array($id, $id_array)) {?>selected <?php }?>><?php echo $name; ?>
						</option>
						<?php } ?>
					</select>
					
				</div>
			</div>
			<?php } ?>
		</div>
		<div class="form-group row">
			<div class="col-sm-6">
				<label class="col-sm-4 col-form-label">
					<?php echo __('対象月');?>
				</label>
				<div class="col-sm-8">
				 	<input type="text" class="form-control" id="target_month" name="target_month" value="<?php if($target_month!="") echo($target_month); ?>" readonly="readonly"/>
				 </div>
			</div>
			
		</div>
		<?php //pr($select_data);pr(implode(',', array_keys($select_data)));?>
		<input type="hidden" name="select_data" value="<?php echo(implode(',', $select_data)) ?>">
		<div class="row" style="display: none; height: 0px;">
			<div class="col-md-6" style="text-align: left;">
				<label class="col-sm-1 col-form-label">
					<?php echo __('Unit');?>
				</label>
				<div class="col-sm-2 fill_unit">
					<input type="text" class="form-control" name="unit" class="unit" value="<?php if($unit != "") echo($unit); ?>" />
				</div>
			</div>
		</div>
		<?php if(!empty($budget_result)) { ?>    
		<div class="row">
			<div class="col-md-12" style="text-align: right;">			
				<input type="button" class="btn btn-success btn_sumisho one" id="btn_download" name="btn_download"  value = "<?php echo __('Excel Download');?>" onclick = "clickExcelDownload();">
				<input type="button" class="btn btn-success btn_sumisho" id="btn_search" name="btn_search" value = "<?php echo __('Search');?>" onclick = "clickSearch();">
			</div>
		</div>
		<?php if(!empty($search_total)){?>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">
				<div class="table-responsive tbl-wrapper">
					<table class="table table-bordered acc_review tbl-fixed " style="margin-top:10px;width: 100%;" id="b_r_compare">
				        <thead class="check_period_table">
				          	<tr>
					          	<th rowspan="2" class="w-170"></th>
					          		<?php $colspan= ($tab != 'Logistic') ? '10' : '8' ?>
					            	<th colspan="<?php echo($colspan) ?>"><?php echo __($parent_name); ?></th> 
					            	<input type="hidden" name="hq_id" value="<?php echo($parent_id); ?>"><input type="hidden" name="hq_name" value="<?php echo($parent_name); ?>"></th> 
				          	</tr>
				          	<tr>
					          	<th style="width: 9%;"><?php echo __("予算"); ?></th>
					          	<th style="width: 9%;"><?php echo __("月次予算"); ?></th>
					          	<th style="width: 9%;"><?php echo __("実績"); ?></th>
					          	<th style="width: 9%;"><?php echo __("対月次予算増減"); ?></th>
					          	<th style="width: 9%;"><?php echo __("累計予算"); ?></th>
					          	<th style="width: 9%;"><?php echo __("累計実績"); ?></th>
					          	<th style="width: 9%;"><?php echo __("対累計予算増減"); ?></th>
					          	<th style="width: 9%;"><?php echo __("達成率（%）対年間"); ?></th>
					          	<?php if ($tab != 'Logistic'): ?>
						          	<th style="width: 9%;"><?php echo __("前年同期実績"); ?></th>
						          	<th style="width: 9%;"><?php echo __("対前年同期増減"); ?></th>
					          	<?php endif ?>
				          	</tr>
				          	<tr>
				          		<th><?php echo __("勘定科目"); ?></th>
				          		<th><?php echo __("年間"); ?></th>
				          		<th><?php echo __(date("Y/m",strtotime($target_month))); ?></th>
				          		<th><?php echo __(date("Y/m",strtotime($target_month))); ?></th>
				          		<th><?php echo __(""); ?></th>
				          		<th><?php echo __(date("Y/m",strtotime($start_month)).' ~ </br>'.date("Y/m",strtotime($target_month))); ?></th>
				          		<th><?php echo __(date("Y/m",strtotime($start_month)).' ~ </br>'.date("Y/m",strtotime($target_month))); ?></th>
				          		<th><?php echo __(""); ?></th>
				          		<th><?php echo __(""); ?></th>

				          		<?php if ($tab != 'Logistic'): ?>
					          		<th><?php echo __(date("Y/m", strtotime($start_month. "last day of - 1 year")).' ~ </br>'.date("Y/m",strtotime($target_month. "last day of - 1 year"))); ?></th>
					          		<th><?php echo __(""); ?></th>
				          		<?php endif ?>
				          	</tr>
				        </thead>
	        			<tbody> 
							<?php foreach ($search_total as $value) { 
								//$sub_acc_name_jp = $value['sub_acc_name_jp'];
								$sub_acc_name_jp 		= $value['name_jp'];//from brm_accounts
								$budget 				= $value['budget'];
								$monthly_budget 		= $value['monthly_budget'];
								$monthly_result 		= $value['monthly_result'];
								$monthly_budget_change 	= $value['monthly_budget_change'];
								$total_budget 			= $value['total_budget'];
								$total_result 			= $value['total_result'];
								$total_budget_change 	= $value['total_budget_change'];
								$achievement_by_year 	= $value['achievement_by_year'];
								$yoy_result 			= $value['yoy_result'];
								$yoy_change 			= $value['yoy_change'];
							?>
				        	<tr class="one">
								<th class="string"><?php echo $sub_acc_name_jp; ?></th>
								<td class="number" id="<?php echo $budget; ?>"></td>
								<td class="number" id="<?php echo $monthly_budget;?>"></td>
								<td class="number" id="<?php echo $monthly_result;?>"></td>
								<td class="number" id="<?php echo $monthly_budget_change;?>"></td>
								<td class="number" id="<?php echo $total_budget;?>"></td>
								<td class="number" id="<?php echo $total_result;?>"></td>
								<td class="number" id="<?php echo $total_budget_change;?>"></td>
								<td id="achieve"><?php echo $achievement_by_year;?></td>
								<?php if ($tab != 'Logistic'): ?>
									<td class="number" id="<?php echo $yoy_result;?>"></td>
									<td class="number" id="<?php echo $yoy_change;?>"></td>
								<?php endif ?>
							</tr>
							<?php } ?>
	        			</tbody>                          
		    		</table>
				</div>
			</div>
		</div>
		<?php } ?>
	    
		<?php foreach ($budget_result as $key => $value) {?>
		<div class="row">

			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 30px;">
				<div class="table-responsive tbl-wrapper">
					<?php $table_bg = (in_array($key, $extra_logistics)) ? 'table_bg_red' : '' ?>
					<table class="table table-bordered acc_review tbl-fixed  <?php echo($table_bg) ?>" style="margin-top:10px;width: 100%;min-width:1000px;" id="b_r_compare">
				        <thead class="check_period_table">
				          	<tr>
					          	<th rowspan="2" class="w-170"></th>
								<?php if($tab == $top_layer_name || $tab == $middle_layer_name || $tab == $bottom_layer_name) {?>
					          		<th colspan="10"><a href="#" onclick="clickHQ('<?php echo current($value)['layer_code'];?>');//echo current($value)['id'];?>');"><?php echo $key; ?></a></th>	
					            <?php }else if($tab == 'Logistic') { ?>
					            	<th colspan="8"><?php echo $key; ?></th>	
					            <?php }else{ ?>
					            	<!-- for hq : not allow to click -->
					            	<th colspan="10"><?php echo $key; ?></th>
					        	<?php } ?>
				          	</tr>
				          	<tr>
					          	<th style="width: 9%;"><?php echo __("予算"); ?></th>
					          	<th style="width: 9%;"><?php echo __("月次予算"); ?></th>
					          	<th style="width: 9%;"><?php echo __("実績"); ?></th>
					          	<th style="width: 9%;"><?php echo __("対月次予算増減"); ?></th>
					          	<th style="width: 9%;"><?php echo __("累計予算"); ?></th>
					          	<th style="width: 9%;"><?php echo __("累計実績"); ?></th>
					          	<th style="width: 9%;"><?php echo __("対累計予算増減"); ?></th>
					          	<th style="width: 9%;"><?php echo __("達成率（%）対年間"); ?></th>
					          	<?php if ($tab != 'Logistic'): ?>
						          	<th style="width: 9%;"><?php echo __("前年同期実績"); ?></th>
						          	<th style="width: 9%;"><?php echo __("対前年同期増減"); ?></th>
					          	<?php endif ?>
				          	</tr>
				          	<tr>
				          		<th><?php echo __("勘定科目"); ?></th>
				          		<th><?php echo __("年間"); ?></th>
				          		<th><?php echo __(date("Y/m",strtotime($target_month))); ?></th>
				          		<th><?php echo __(date("Y/m",strtotime($target_month))); ?></th>
				          		<th><?php echo __(""); ?></th>
				          		<th><?php echo __(date("Y/m",strtotime($start_month)).' ~ </br>'.date("Y/m",strtotime($target_month))); ?></th>
				          		<th><?php echo __(date("Y/m",strtotime($start_month)).' ~ </br>'.date("Y/m",strtotime($target_month))); ?></th>
				          		<th><?php echo __(""); ?></th>
				          		<th><?php echo __(""); ?></th>
				          		<?php if ($tab != 'Logistic'): ?>
					          		<th><?php echo __(date("Y/m", strtotime($start_month. "last day of - 1 year")).' ~ </br>'.date("Y/m",strtotime($target_month. "last day of - 1 year"))); ?></th>
					          		<th><?php echo __(""); ?></th>
				          		<?php endif ?>
				          	</tr>
				        </thead>
		        		<tbody> 
							<?php foreach ($value as $values) { 
								//$sub_acc_name_jp = $values['sub_acc_name_jp'];
								$sub_acc_name_jp 		= $values['name_jp'];//from brm_accounts
								$budget 				= $values['budget'];
								$monthly_budget 		= $values['monthly_budget'];
								$monthly_result 		= $values['monthly_result'];
								$monthly_budget_change 	= $values['monthly_budget_change'];
								$total_budget 			= $values['total_budget'];
								$total_result 			= $values['total_result'];
								$total_budget_change 	= $values['total_budget_change'];
								$achievement_by_year 	= $values['achievement_by_year'];
								$yoy_result 			= $values['yoy_result'];
								$yoy_change 			= $values['yoy_change'];
							?>
				        	<tr class="one">
								<th class="string"><?php echo $sub_acc_name_jp; ?></th>
								<td class="number" id="<?php echo $budget; ?>"></td>
								<td class="number" id="<?php echo $monthly_budget;?>"></td>
								<td class="number" id="<?php echo $monthly_result;?>"></td>
								<td class="number" id="<?php echo $monthly_budget_change;?>"></td>
								<td class="number" id="<?php echo $total_budget;?>"></td>
								<td class="number" id="<?php echo $total_result;?>"></td>
								<td class="number" id="<?php echo $total_budget_change;?>"></td>
								<td id="achieve"><?php echo $achievement_by_year;?></td>
								<?php if ($tab != 'Logistic'): ?>
									<td class="number" id="<?php echo $yoy_result;?>"></td>
									<td class="number" id="<?php echo $yoy_change;?>"></td>
								<?php endif ?>
							</tr>
						<?php } ?>
		        		</tbody>                          
			    	</table>
		    	</div> 
			</div>
		</div><br>
		<?php }//end budget_result foreach ?>
		<?php }else { //empty budget_result
		if(!empty($errmsg)) {?>
			<br><br><br>
			<div id="err" class="no-data"> <?php echo ($errmsg); ?></div>
		<?php }
		} ?>
	</div><!-- tabcontent end -->
</div>
<?php
	echo $this->Form->end();
?>