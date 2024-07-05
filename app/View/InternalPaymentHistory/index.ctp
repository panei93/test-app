<style>
	table {
		table-layout: fixed;
	}
	table tr td {
		padding: 5px !important;
	}

	/* input#btn_search { */
		/* text-align: center; */
    /* float: right;
    margin-right: 16px; */
	/* } */

  th{
  		background-color: #D5EADD;
  		text-align: center;
	}

	table tr.total {
		text-align: center;
		font-weight: bold;
		border-style: double;
		border-color: #ddd;
	}
  /* #table-scroll {
	height:500px;
	overflow:auto;  
	margin-top:20px;
  } */
  .zoom {
  zoom: 80%;
}
	@media (max-width: 768px){
		.col-sm-6 {
		  padding-bottom: 15px;
		}
	}
  @media (max-width: 820px){

  	#tbl_trading{
  	border: none;
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    padding: 0
  	}

  }
	.txt-align-right {
		text-align: right;
	}
	.negative{
		color: #f31515;
	}
</style>
<script>
	$(function() {
		//document.body.style.zoom = "80%";
        // if($('#tbl_trading').length > 0) {
        // var $table = $('#tbl_trading');
		// 	$table.floatThead({
		// 		responsiveContainer: function($table){
		// 			return $table.closest('.table-responsive');
		// 		}
		// 	});
    	// }
  	});
	function Search_data(){
	loadingPic();
      document.forms[0].action = "<?php echo $this->webroot; ?>InternalPaymentHistory/SearchDestination";
      document.forms[0].method = "POST";
      document.forms[0].submit();   
      return true;      

		scrollText();
	}
	function loadingPic() { 
			$("#overlay").show();
            $('.jconfirm').hide();  
	}
</script>
<div id="overlay">
	<span class="loader"></span>
</div> 

 <div class = 'container register_container'>

 	<div class="row" style="margin-bottom: 30px;">
		<div class="col-md-12 col-sm-12 heading_line_title">
	    	<h3><?php echo __('社内受払履歴'); ?></h3>
	    	<hr>
	    </div>
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="success" id="success"><?php echo ($this->Session->check("Message.InternalPaySuccess"))? $this->Flash->render("InternalPaySuccess") : '';?></div>						
			<div class="error" id="error"><?php echo ($this->Session->check("Message.InternalPayError"))? $this->Flash->render("InternalPayError") : '';?></div>
		</div>
		<?php	echo $this->Form->create(false,array('type'=>'post')); ?>
		<div class="form-group row">
			<div class="col-sm-6">
				<label  class="col-sm-4 col-form-label">
					<?php echo __("期間");?>
				</label>
				<div class="col-sm-8">
					<input class="form-control" type="text" id="term" name="term" value="<?=$term;?>" readonly>
					<input type="hidden" name="term_id" value="<?=$term_id?>">
				</div>
			</div>

			<div class="col-sm-6">
				<label for="object" class="col-sm-4 col-form-label">
					<?php echo __("部署");?>
				</label>
				 <div class="col-sm-8">
				 	<?php //pr($searchBAlist);
					 	if(!empty($searched_ba)){
					 		$source_bcode = $searched_ba;
					 		
					 	}else{
					 		$source_bcode = '';
					 	}
					 	
				 	?>
					<select id="source_bcode" name="source_bcode" class="form-control">
						<option value="">-- <?php echo __("部署選択"); ?> --</option>
						<?php foreach ($searchBAlist as $key => $value) {?>
							<option value="<?php echo $key; ?>" <?php if($key == $source_bcode){ echo 'selected';}?>>
								<?php
									echo $key.'/'.$value;
							 	?>
								</option>
						<?php } ?>
					</select>
				 </div>
			</div>
		</div>

		<div class="form-group row">
			<div class="col-sm-6">
				<label for="from_date" class="col-sm-4 col-form-label">
					<?php echo __("対象年度");?>
				</label>
				<div class="col-sm-8">
					<input class="form-control" type="text" id="target_year" name="target_year" value="<?=$target_year?>"  readonly>
				</div>
			</div>
			<div class="col-sm-6">
				<label for="object" class="col-sm-4 col-form-label">
					<?php echo __("取引");?>
				</label>
				 <div class="col-sm-8">
				 	<?php
					 	if(!empty($searched_logi)){
					 		$logistic_index_no = $searched_logi;
					 	}elseif(!empty($logistic_index_no)){
					 		$logistic_index_no = $logistic_index_no;
					 	}else{
					 		$logistic_index_no = '';
					 	}
				 	?>
					<input class="form-control" type="text" id="logistic_index_no" name="logistic_index_no" value="<?=$logistic_index_no?>" >
				 </div>
			</div>
		</div>
	</div>
	<?php 
	$marginL = '';
	if(!empty($pg_payment_datas)) $marginL = 'margin-right:-15px';?>
	<div class="text-right adjust" style="<?php echo $marginL; ?>">
		<?php if(!empty($pg_payment_datas)) {?>
		<input type="button" id="btn_excel_download" name="btn_excel_download" class="btn btn-success" value="<?php echo __("Excelダウンロード"); ?>" >
		<?php } ?>
		<input type="button" class="btn btn-success btn_sumisho" id="btn_search" name="btn_search" value = "<?php echo __('検索');?>" onclick="Search_data();">
	</div>

	<?php //pr($pg_payment_datas);?>
	<?php if(!empty($pg_payment_datas)){ ?>
		<div class="msgfont" id="total_row">
		<?=$count;?>
		</div>	
  	<div class="row">
		<div id="table-scroll">
			<table class="table table-bordered tbl_master zoom" id="tbl_trading" style="white-space:unset;">
				<thead>
					<tr>
						<th class="w-120"><?php echo __("ソース"); ?></th>
						<th class="w-140"><?php echo __("取引")." / ".__("理由"); ?></th>
						<th class="w-120"><?php echo __("相手先"); ?></th>
						<th><?php echo __("4月"); ?></th>
						<th><?php echo __("5月"); ?></th>
						<th><?php echo __("6月"); ?></th>
						<th><?php echo __("7月"); ?></th>
						<th><?php echo __("8月"); ?></th>
						<th><?php echo __("9月"); ?></th>
						<th><?php echo __("上期"); ?></th>
						<th><?php echo __("10月"); ?></th>
						<th><?php echo __("11月"); ?></th>
						<th><?php echo __("12月"); ?></th>
						<th><?php echo __("1月"); ?></th>
						<th><?php echo __("2月"); ?></th>
						<th><?php echo __("3月"); ?></th>
						<th><?php echo __("下期"); ?></th>
						<th><?php echo __("年間"); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($pg_payment_datas as $result):?>
					<tr>
						<?php 
							$first_half = $result['month_1_amt']+$result['month_2_amt']+$result['month_3_amt']+$result['month_4_amt']+$result['month_5_amt']+$result['month_6_amt'];
							$second_half = $result['month_7_amt']+$result['month_8_amt']+$result['month_9_amt']+$result['month_10_amt']+$result['month_11_amt']+$result['month_12_amt'];
							$whole_total = $first_half + $second_half;
						 ?>
						 <td> <?php echo $result['layer_code'].'<br/>'.$result['ba_name'] ?></td>
						<td>
							<?php if(!empty($result['kpi_unit'])){ ?>
								<?=h($result['logistic_index_no'].' / '.$result['kpi_unit'])?>
							<?php }else{ ?>
								<?=h($result['logistic_index_no'])?>
							<?php } ?>
						</td>
						
						<td><?php echo ($result['destination'].'<br/>'.$result['destination_name']); ?></td>
							<?php
								$month_1_amt = $result['month_1_amt'];
								$month_2_amt = $result['month_2_amt'];
								$month_3_amt = $result['month_3_amt'];
								$month_4_amt = $result['month_4_amt'];
								$month_5_amt = $result['month_5_amt'];
								$month_6_amt = $result['month_6_amt'];
								$first_half  = $month_1_amt+$month_2_amt+$month_3_amt+$month_4_amt+$month_5_amt+$month_6_amt;
								$month_7_amt = $result['month_7_amt'];
								$month_8_amt = $result['month_8_amt'];
								$month_9_amt = $result['month_9_amt'];
								$month_10_amt = $result['month_10_amt'];
								$month_11_amt = $result['month_11_amt'];
								$month_12_amt = $result['month_12_amt'];
								$second_half = $month_7_amt+$month_8_amt+$month_9_amt+$month_10_amt+$month_11_amt+$month_12_amt;
								$whole_total = $first_half+$second_half;
							?>
						<td class="txt-align-right <?php if($month_1_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_1_amt,1))?></td>
						<td class="txt-align-right <?php if($month_2_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_2_amt,1))?></td>
						<td class="txt-align-right <?php if($month_3_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_3_amt,1))?></td>
						<td class="txt-align-right <?php if($month_4_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_4_amt,1))?></td>
						<td class="txt-align-right <?php if($month_5_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_5_amt,1))?></td>
						<td class="txt-align-right <?php if($month_6_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_6_amt,1))?></td>
						<td class="txt-align-right <?php if($first_half < 0) echo 'negative'; ?>"><?=h(number_format($first_half,1))?></td>
						<td class="txt-align-right <?php if($month_7_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_7_amt,1))?></td>
						<td class="txt-align-right <?php if($month_8_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_8_amt,1))?></td>
						<td class="txt-align-right <?php if($month_9_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_9_amt,1))?></td>
						<td class="txt-align-right <?php if($month_10_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_10_amt,1))?></td>
						<td class="txt-align-right <?php if($month_11_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_11_amt,1))?></td>
						<td class="txt-align-right <?php if($month_12_amt < 0) echo 'negative'; ?>"><?=h(number_format($month_12_amt,1))?></td>
						<td class="txt-align-right <?php if($second_half < 0) echo 'negative'; ?>"><?=h(number_format($second_half,1))?></td>
						<td class="txt-align-right <?php if($whole_total < 0) echo 'negative'; ?>"><?=h(number_format($whole_total,1))?></td>
					</tr>
					<?php endforeach;?>
					<?php
						$amt1 = $toal_amount['month_1_amt'];
						$amt2 = $toal_amount['month_2_amt'];
						$amt3 = $toal_amount['month_3_amt'];
						$amt4 = $toal_amount['month_4_amt'];
						$amt5 = $toal_amount['month_5_amt'];
						$amt6 = $toal_amount['month_6_amt'];
						$amt7 = $toal_amount['month_7_amt'];
						$amt8 = $toal_amount['month_8_amt'];
						$amt9 = $toal_amount['month_9_amt'];
						$amt10 = $toal_amount['month_10_amt'];
						$amt11 = $toal_amount['month_11_amt'];
						$amt12 = $toal_amount['month_12_amt'];
						$first_half_total = $amt1+$amt2+$amt3+$amt4+$amt5+$amt6;
						$second_half_total = $amt7+$amt8+$amt9+$amt10+$amt11+$amt12;;
						$yearly_total = $first_half_total+$second_half_total;
					?>
					<tr class="total">
						<td colspan="3"><?= __('累計')?></td>
						<td class="txt-align-right <?php if($amt1 < 0) echo 'negative'; ?>"><?=h(number_format($amt1,1))?></td>
						<td class="txt-align-right <?php if($amt2 < 0) echo 'negative'; ?>"><?=h(number_format($amt2,1))?></td>
						<td class="txt-align-right <?php if($amt3 < 0) echo 'negative'; ?>"><?=h(number_format($amt3,1))?></td>
						<td class="txt-align-right <?php if($amt4 < 0) echo 'negative'; ?>"><?=h(number_format($amt4,1))?></td>
						<td class="txt-align-right <?php if($amt5 < 0) echo 'negative'; ?>"><?=h(number_format($amt5,1))?></td>
						<td class="txt-align-right <?php if($amt5 < 0) echo 'negative'; ?>"><?=h(number_format($amt6,1))?></td>
						<td class="txt-align-right <?php if($first_half_total < 0) echo 'negative'; ?>"><?=h(number_format($first_half_total,1))?></td>
						<td class="txt-align-right <?php if($amt7 < 0) echo 'negative'; ?>"><?=h(number_format($amt7,1))?></td>
						<td class="txt-align-right <?php if($amt8 < 0) echo 'negative'; ?>"><?=h(number_format($amt8,1))?></td>
						<td class="txt-align-right <?php if($amt9 < 0) echo 'negative'; ?>"><?=h(number_format($amt9,1))?></td>
						<td class="txt-align-right <?php if($amt10 < 0) echo 'negative'; ?>"><?=h(number_format($amt10,1))?></td>
						<td class="txt-align-right <?php if($amt11 < 0) echo 'negative'; ?>"><?=h(number_format($amt11,1))?></td>
						<td class="txt-align-right <?php if($amt12 < 0) echo 'negative'; ?>"><?=h(number_format($amt12,1))?></td>
						<td class="txt-align-right <?php if($second_half_total < 0) echo 'negative'; ?>"><?=h(number_format($second_half_total,1))?></td>
						<td class="txt-align-right <?php if($yearly_total < 0) echo 'negative'; ?>"><?=h(number_format($yearly_total,1))?></td>
					</tr> 
				</tbody>
			</table>
		</div>
    <div class="col-md-12" style="padding: 10px;text-align: center;margin-bottom: 30px;">
      <div class="paging">
      <?php
	 
	  	if ($query_count > 50) {
          echo $this->Paginator->first('<<');
          echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev disabled'));
          echo $this->Paginator->numbers(array('separator'=>'', 'modulus'=>6));
          echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
          echo $this->Paginator->last('>>');
		}
      ?>
      </div>
    </div> 
     <?php }else{ ?>
     <div id="err" class="no-data"> <?php echo ($errmsg); ?></div>
      <?php } ?>		
	</div>
	<?php echo $this->Form->end();?>
</div>
<script>
	$("#btn_excel_download").click(function(){
		
		//console.log('excelData');
		document.forms[0].action = "<?php echo $this->webroot; ?>InternalPaymentHistory/excelData";
		document.forms[0].method = "POST";
		document.forms[0].submit();
		return true;	

	});
</script>
