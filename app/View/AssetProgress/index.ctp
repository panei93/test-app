<style>
	.align-left {
		text-align: left !important;
	}
	.align-right {
		text-align: right !important;
	}
	.align-center {
		text-align: center !important;
	}
	.table tbody tr > td.not-done, td.not-approve {
		background-color: #f7c8c8;
		margin: 0px;
		padding: 0px;}
	#tbl_report {
		width: 100%;
	}
	.pinkcolor
	{
		background-color: pink;
	}
	.but_progress
	{
		margin-bottom: 10px;
	}
	.fl-scrolls {
		margin-bottom: 40px;/* modify floating scroll bar */
	}
	.acc_review{
		margin-top:0px;
    }
</style>

<script>
	$(document).ready(function() {
		/** sticky table header to list table **/
		if($('#tbl_report').length) {
			var $table = $('#tbl_report');
				$table.floatThead({
				  position: 'absolute'
			});
		}

		if($(".tbl-wrapper").length) {
			$(".tbl-wrapper").floatingScroll();
		}

	});
</script>

<h3><?php echo __('進捗管理表');?></h3>
<hr>

<div class="row">
	<div class="col-sm-8">
		<form class="form-horizontal">
			<div class="form-group">
				<label class="control-label col-sm-2 align-left"><?php echo __("イベント名"); ?></label>
				<div class="col-sm-4">
					<input type="text" class="form-control" value="<?php echo $eventname_session ;?>" disabled>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<?php if (!empty($EventSuccess)) {?> 
			<div id="succc" class="msgfont"> <?php echo($EventSuccess);?></div>
		<?php } elseif (!empty($errorMsg)) {?>
			<div id="err" class="no-data"> <?php echo($errorMsg); ?></div>
		<?php }?>                
	</div>
</div>
<?php if ($pg_data!=0) { ?>
<div class="text-right">
	<div class="" >
		<form action="<?php echo $this->webroot; ?>AssetProgress/progressChart_pdf" method="post" name="print_form" target="_blank">
			<input type="submit" name="btn_print" id="btn_print" class="btn btn-success btn_sumisho" value="<?php echo __("印刷"); ?>">
		</form>
	</div>
</div>	
<div class="row">
	<div class="col-sm-12">
		<div class="msgfont" id="error_msg">
			<?php
                echo $total_rows;
            ?>
		</div>	
		<div class="table-responsive tbl-wrapper">
			<table class="table table-striped table-bordered acc_review" id="tbl_report">
				<thead>
					<tr>
						<?php foreach ($header as $value) {?>
							<th><?php echo __($value); ?></th>
						<?php } ?>
						<th width="150px"><?php echo __("Layer Code"); ?></th>
						<!-- <th width="300px"><?php echo __("Layer Name"); ?></th> -->
						<th width="300px"><?php echo __("担当者欄追"); ?></th>
						<th width="150px"><?php echo __("営業部長"); ?><br>(Approved)</th>
					</tr>
				</thead>
				<tbody>					
					<?php 
                    if  (!empty($progress_data)) {
                        foreach ($progress_data as $code => $value) { ?>
							<tr>                       
							    
								<?php //pr($header);
									if(!empty($header))
										foreach($layers as $order => $name) {
	                        				if(!empty($value[$order])){?><td class="align-left"><?php echo h($value[$order]); ?></td>
	                        	<?php } }?>
								<td class="align-left"><?php echo h($value[$code]); ?></td>
								<td class="align-left"><?php echo h($code); ?></td>
								
								<td class="align-left"><?php echo h($value['user_name']); ?></td>

								<?php if (!empty($value['appDate'])) {
									$style = '';
									$appDate = $value['appDate'];
								} else { 
									$style = "background-color: pink;";
									$appDate = '';
								}?>
								<td class="align-left" style='<?php echo $style; ?>'><?php echo h($appDate);?></td></td> 
								
							</tr>
							<?php 
                        }
                    }?>
		
				</tbody>	
			</table>
		</div>	
	</div>
</div>
<?php } ?>
<br>
<br>
