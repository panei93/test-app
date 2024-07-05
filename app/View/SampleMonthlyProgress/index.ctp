<style>
	table, th, td {
	    text-align: center;
		vertical-align: middle !important;
	}

	th {
	    background-color: #D5EADD;
	}
	input#fileImport {
	    margin-bottom: 30px;
	}
	.td_center {
		text-align: center;
	}
	.td_left {
		text-align: left;
	}
	.td_right {
		text-align: right;
	}

	@media (max-width:555px) { 
		.table-bordered {
		    border: none;
		    display: block;
		    overflow-x: auto;
		    white-space: nowrap;
		    padding: 0;
		}
	}
	.btn_right {
		float: right;
		margin-right: -15px;
	}
</style>
<script>
	function ExcelDownload(){
		document.forms[0].action = "<?php echo $this->webroot; ?>SampleMonthlyProgress/excel_download";
		document.forms[0].method = "POST";
		document.forms[0].submit();			
		return true;		
	
	}
</script>
<form method="post" action="excel_download">
<?php 
	$period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
?> 
<h3><?php echo __("進捗管理(詳細版)");?></h3>
<hr>

<div class="form-group">
	<label class="col-md-2"><?php echo __("対象月");?></label>
	<div class="col-md-4">
		<input class="form-control register" style="margin-bottom: 7px;" type="textbox" id="layer_code" value="<?=$period;?>" readonly>
	</div>
</div>

<!-- empty -->
<?php if(empty($query_result)){ ?>
<div class="col-sm-12">
	<p class="no-data"><?=$no_data?></p>
</div>
<?php } ?>
<!-- no empty -->
<?php if(!empty($query_result)){ ?>
<div class="form-group col-md-12 col-sm-12 col-xs-12">
  <input onclick="ExcelDownload();" type="button" value="<?php echo __("Excelダウンロード");?>" name="" class="emp_register but_register btn_right" id="fileImport">
	</div>
<div style="margin: 150px 0px;">
	<div class="msgfont" id="total_row">
		<?=$count;?>
	</div>	
	<table class="table table-bordered acc_review">
		<thead>
			<tr>
				<?php if(!empty($header_list)) { foreach($header_list as $header):?>
					<th rowspan="2"><?php echo __($header);?></th>
				<?php endforeach;} else {?>
					<th rowspan="2"><?php echo __("本部");?></th>
					<th rowspan="2"><?php echo __("部");?></th>
				<?php } ?>
				<th rowspan="2"><?php echo __("部署");?></th>
				<th rowspan="2"><?php echo __("カテゴリー");?></th>
				<th rowspan="2"></th>
				<th colspan="3"><?php echo __("財務経理部");?></th>
				<th colspan="3"><?php echo __("営業");?></th>
	
			</tr>			
			<tr>

				<th><?php echo __("担当者");?></th>
				<th><?php echo __("管理職");?></th>
				<th><?php echo __("責任者");?></th>
				<th><?php echo __("担当者");?></th>
				<th><?php echo __("管理職");?></th>
				<th><?php echo __("責任者");?></th>	
			</tr>
		</thead>
		<tbody>
			<?php array_pop($header_list);foreach($query_result as $result):?>
			<tr>
				<?php $layer_gp_name = explode(",",$result['sample_acc_incharge_data']['head_name']);
				
				$field_name = ($this->Session->read('Config.language')=='jpn')?'name_jp':'name_en';
				$i = 0;foreach($header_list as $key=>$header): ?>
					<td rowspan="5" class="td_left"><?=$layer_gp_name[$i]?></td>
				<?php $i++;endforeach; ?>
				<td rowspan="5" class="td_left"><?=$result['sample_acc_incharge_data'][$field_name]?></td>
				<td rowspan="5" class="td_left"><?=$result['sample_acc_incharge_data']['layer_code']?></td>
				<td rowspan="5" class=""><?=$result['sample_acc_incharge_data']['category']?></td>
				<td class="td_left"><?php echo __("サンプル作成");?></td>
				<td class="td_right"><?=$result['sample_acc_incharge_data']['sample_acc_incharge_num']?></td>
				<td class="td_right"><?=$result['sample_acc_sub_manager_data']['sample_acc_sub_manager_num']?></td>
				<td class="td_right"><?=$result['sample_acc_manager_data']['sample_acc_manager_num']?></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr class="table_line">
				<td class="td_left"><?php echo __("データ入力");?></td>
				<td></td>
				<td></td>
				<td></td>
				<td class="td_right"><?=$result['data_bus_incharge_data']['data_bus_incharge_num']?></td>
				<td class="td_right"><?=$result['data_bus_sub_manager_data']['data_bus_sub_manager_num']?></td>
				<td class="td_right"><?=$result['data_bus_manager_data']['data_bus_manager_num']?></td>
			</tr>			
			<tr class="table_line">
				<td class="td_left"><?php echo __("テスト結果作成");?></td>
				<td class="td_right"><?=$result['result_acc_incharge_data']['result_acc_incharge_num']?></td>
				<td class="td_right"><?=$result['result_acc_sub_manager_data']['result_acc_sub_manager_num']?></td>
				<td class="td_right"><?=$result['result_acc_manager_data']['result_acc_manager_num']?></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr class="table_line">
				<td class="td_left"><?php echo __("フィードバック");?></td>
				<td></td>
				<td></td>
				<td></td>
				<td class="td_right"><?=$result['check_bus_incharge_data']['check_bus_incharge_num']?></td>
				<td class="td_right"><?=$result['check_bus_sub_manager_data']['check_bus_sub_manager_num']?></td>
				<td class="td_right"><?=$result['check_bus_manager_data']['check_bus_manager_num']?></td>
			</tr>
			<tr class="table_line">
				<td class="td_left"><?php echo __("改善状況報告");?></td>
				<td class="td_right"><?=$result['wrap_acc_incharge_data']['wrap_acc_incharge_num']?></td>
				<td class="td_right"><?=$result['wrap_acc_sub_manager_data']['wrap_acc_sub_manager_num']?></td>
				<td class="td_right"><?=$result['wrap_acc_manager_data']['wrap_acc_manager_num']?></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>			
			<?php endforeach;?>
		</tbody>
	</table>
	<!-- paging -->
	<?php if($pageCount>1){ ?> 
	    <div class="col-md-12" style="padding: 10px;text-align: center;margin-bottom: 20px;">
	        <div class="paging">
	        <?php
	            echo $this->Paginator->first('<<');
	            echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev disabled'));
	            echo $this->Paginator->numbers(array('separator'=>'', 'modulus'=>6));
	            echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
	            echo $this->Paginator->last('>>');
	        ?>

	        </div>
	    </div>
    <?php } ?>
	<?php } ?>
</div>
</form>
