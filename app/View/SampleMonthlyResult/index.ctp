<style>
	table, th, td {
	    text-align: center;

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
		document.forms[0].action = "<?php echo $this->webroot; ?>SampleMonthlyResult/excel_download";
		document.forms[0].method = "POST";
		document.forms[0].submit();			
		return true;		

		
	}
</script>
<form method="post" action="excel_download">
<?php 
	$period = $this->Session->read('SAMPLECHECK_PERIOD_DATE');
?> 
<h3><?php echo __("進捗管理(サマリー版)");?></h3>
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
					<th><?php echo __($header);?></th>
				<?php endforeach;}?>
				<th><?php echo __("部署");?></th>
				<th><?php echo __("カテゴリー");?></th>
				<th><?php echo __("サンプル数");?></th>
				<th><?php echo __("リザルト数");?></th>
				<th><?php echo __("完了数");?></th>
				<th><?php echo __("ペンディング");?></th>			
			</tr>
		</thead>
		<tbody>
			<?php foreach($query_result as $result):?>
				<?php ?>
			<tr>
				<?php $val_arr = explode(',',$result['summary']['layers_group_name']);
						foreach($val_arr as $val):
				?>
				<td class="td_left"><?=$val?></td>
				<?php
						endforeach;
				?>
				<td class="td_left"><?=$result['summary']['name_tmp']?></td>
				<td class="td_left"><?=$result['summary']['layer_code']?></td>
				<td class=""><?=$result['summary']['category']?></td>
				<td class="td_right"><?=$result['summary']['sample_number']?></td>
				<td class="td_right"><?=$result['summary']['result_number']?></td>
				<td class="td_right"><?=$result['summary']['completions']?></td>
				<?php 
					if($result['summary']['sample_number'] == '' && $result['summary']['completions'] == ''){
						$pending = '';
					}else{
						$pending = $result['summary']['sample_number']-$result['summary']['completions'];
					}
				?>
				<td class="td_right"><?=$pending?></td>
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
