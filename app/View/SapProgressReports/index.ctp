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
	}
	#tbl_report {
		width: 100%;
	}
</style>

<div class="content register_container" style="padding: 20px;font-size: 1em !important;">
	<div class="row">
		<div class="col-md-12 col-sm-12">				
			<h3>
				<?php echo __('進捗管理表');?>	
			</h3><hr>
		</div>
		<div class="col-sm-12">
			<div class="errorSuccess">
				<div class="success" id="success"><?php echo ($this->Session->check("Message.progressOK"))? $this->Flash->render("progressOK") : ''; ?></div>
				<div class="error" id="error"><?php echo ($this->Session->check("Message.progressFail"))? $this->Flash->render("progressFail") : ''; ?></div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-8">
			<form class="form-horizontal">
				<div class="form-group">
					<label class="control-label col-sm-2 align-left"><?php echo __("対象月"); ?></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" value="<?php echo $period; ?>" disabled>
					</div>
				</div>
			</form>
		</div>
	</div>
	<?php if(!empty($result)) { ?>
		<div class="text-right">
			<div class="">
				<form action="<?php echo $this->webroot; ?>SapProgressReports/progress_pdf" method="post" name="print_form" target="_blank">
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
				<div class="table-responsive">
					<table class="table table-striped table-bordered tbl_sumisho_inventory" id="tbl_report">
						<thead>
							<tr>
								<th width="150px" rowspan="2"><?php echo $result[0]['topLayer']; ?></th>
								<th width="150px" rowspan="2"><?php echo $result[0]['middleLayer']; ?></th>
								<th width="80px" rowspan="2"><?php echo $result[0]['bottomLayer']; ?></th>
								<th width="200px" rowspan="2"><?php echo $result[0]['bottomLayer'].' '.__("名"); ?></th>
								<th width="300px" colspan="3"><?php echo __("営業"); ?></th>
								<th width="300px" colspan="3"><?php echo __("財務経理部"); ?></th>
								<th width="100px" rowspan="2"><?php echo __("完了"); ?></th>
							</tr>
							<tr>
								<th width="100px"><?php echo __("担当者"); ?></th>
								<th width="100px"><?php echo __("管理職"); ?></th>
								<th width="100px"><?php echo __("責任者"); ?></th>
								<th width="100px"><?php echo __("担当者"); ?></th>
								<th width="100px"><?php echo __("管理職"); ?></th>
								<th width="100px"><?php echo __("責任者"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
								if(!empty($result)) {
									foreach ($result as $val) {
										$layer_code = $val['layer_code'];
										$head_dept = $val['head_dept'];
										$department = $val['department'];
										$name_jp = $val['name_jp'];
										$sale_incharge = ($val['sale_incharge'] == 'F')? __("済") : __("未");
										$sale_admin = ($val['sale_admin'] == 'F')?__("済") : __("未");
										$sale_manager = ($val['sale_manager'] == 'F')?__("済") : __("未");
										$sale_manager .= "<br/>";
										$sale_manager .= ($val['busi_approve_date'] != '')? $val['busi_approve_date'] : '';
										$acc_incharge = ($val['acc_incharge'] == 'F')?__("済") : __("未");
										$acc_admin = ($val['acc_admin'] == 'F')?__("済") : __("未");
										$acc_manager = ($val['acc_manager'] == 'F')?__("済") : __("未");
										$acc_manager .= "<br/>";
										$acc_manager .= ($val['acc_approve_date'] != '')? $val['acc_approve_date'] : '';
										$status = ($val['status'] == 'done')?__("完了") : '';
							?>
										<tr>
											<td><?php echo h($head_dept); ?></td>
											<td><?php echo h($department); ?></td>
											<td><?php echo h($layer_code); ?></td>
											<td><?php echo h($name_jp); ?></td>
											<td><?php echo $sale_incharge; ?></td>
											<td><?php echo $sale_admin; ?></td>
											<td><?php echo $sale_manager; ?></td>
											<td><?php echo $acc_incharge; ?></td>
											<td><?php echo $acc_admin; ?></td>
											<td><?php echo $acc_manager; ?></td>
											<td><?php echo $status; ?></td>
										</tr>
							<?php
									}
								} 
							?>
						</tbody>	
					</table>
				</div>	
			</div>
		</div>
	<?php } ?>
	<div class="row">
		<div class="col-sm-12">
			<p class="no-data"><?php echo $no_data; ?></p>
		</div>
	</div>
	<br/><br/><br/>
</div>

<script>
	$(document).ready(function() {
		/** sticky table header to list table **/
		if($('#tbl_report').length) {
			var $table = $('#tbl_report');
			$table.floatThead({
			    responsiveContainer: function($table){
			        return $table.closest('#tbl_report');
			    }
			});
		}
	});
</script>