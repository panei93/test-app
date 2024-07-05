<style>
	body {
		margin-bottom: 50px;
	}

	.goup_nav {
		padding-top: 30px;
		/*display: flex;*/
	}

	.goup_nav ul {
		display: flex;
	}

	.goup_nav li a {
		font-weight: bold;
		color: #000;
		background: #eee;
	}

	.goup_nav li {
		width: 20%;
		/*display: flex;*/
	}

	#myTabContent {
		padding: 15px;
	}

	.trash-can {
		color: #d00000;
	}

	.trash-can:hover {
		text-decoration: none;
		color: #9d0208;
	}

	@media screen and (max-width: 767px) {

		/*.table-responsive {
         border: none !important; 
      }
      
      table#sub_account {
    	border: 1px solid #D3D3D3;
		}*/
		.goup_nav li {
			display: flex;
		}
	}

	.line {

		margin-bottom: 0px;
		margin-left: 3px;
	}

	.download_link {
		padding-top: 7px;
	}

	.btn_excel {
		color: #5cb85c;
		background-color: #ffffff;
		padding-top: 4px;
		border: 1px solid transparent;
		padding-right: 6px;
		padding-bottom: 0px;
	}

	.task_list {
		padding-bottom: 10px;
	}

	.folder {
		position: relative;
		margin: 0px 5px;
		padding-left: 20px;
		line-height: 2.5rem;
		clear: both;
	}

	.file {
		margin: 5px;
		line-height: 2.5rem;
		clear: both;
	}

	.folder:hover,
	.file:hover {
		background-color: #eee;
		/* cursor: pointer; */
	}

	.folder span.download-btn,
	.folder span.delete-btn,
	.folder span.display-dtime,
	.file span.download-btn,
	.file span.delete-btn,
	.file span.display-dtime,
	.file span.display-file {
		float: right;
		margin: 0px 10px;
	}
	.folder span.download-btn:hover,
	.folder span.delete-btn:hover,
	.file span.download-btn:hover,
	.file span.delete-btn:hover {
		cursor: pointer;
	}

	span.delete-btn {
		padding: 0;
	}
	.folder .arrow:hover {
		cursor: pointer;
	}

	.folder .arrow:before {
		content: '▼';
		position: absolute;
		left: 0;
		bottom: 0;
	}

	.folder.in .arrow:before {
		content: '▶';
		position: absolute;
		left: 0;
		bottom: 0;
	}

	.download-btn {
		color: #5a4ebe;
	}

	.delete-btn {
		padding-left: 10px;
		color: #d9534f;
	}

	.display-dtime {
		color: gray;
	}

	.display-file {
		color: gray;
	}

	#tbl_acc .blank-cell th {
		background-color: #a3bbc9;
		/*color: #fff;*/
	}

	/*	#tbl_acc tr td {
		padding: 10px;
	}*/
	.input-group.from_date,
	.input-group.to_date {
		display: inline-table;
	}

	.input-group.from_date,
	.input-group.to_date {
		padding: 0px 15px;
	}

	.adjust-padding {
		padding-left: 20px;
	}

	@media screen and (min-width: 437px) and (max-width: 1000px) {
		.adjust-padding {
			padding-left: 15px;
		}
	}

	.input-group .form-control {
		position: static;
	}

	.cursor-pointer {
		cursor: pointer;
	}

	.folder_list {
		border: 1px solid #ddd;
		padding: 20px;
		box-shadow: -6px -3px 10px #ddd;
		margin-top: 20px;
		margin-bottom: 7rem;
	}

	.folder_list .form-group .input-group {
		display: block;
	}

	.folder_list .form-group .input-group .form-control {
		min-width: 20%;
		width: auto;
	}

	#backup_btn {
		padding-left: 7px !important;
	}
</style>
<?php echo $this->Form->create(false, array('type' => 'post', 'name' => 'BrmBackupFile', 'id' => 'BrmBackupFile', 'enctype' => 'multipart/form-data'));
?>

<div id="overlay">
	<span class="loader"></span>
</div>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12">
		<h3><?php echo __("Backup Master"); ?></h3>
		<hr>
		<div class="success" id="success"><?php echo ($this->Session->check("Message.successBackup")) ? $this->Flash->render("successBackup") : ''; ?></div>
		<div class="error" id="error"><?php echo ($this->Session->check("Message.errorBackup")) ? $this->Flash->render("errorBackup") : ''; ?></div>
	</div>
</div>

<div class="form_test">
	<input type="hidden" name="search_termid" value="<?php echo $search_termid ?>">
	<input type="hidden" name="search_headid" value="<?php echo $search_headid ?>">
	<input type="hidden" name="search_type" value="<?php echo $search_type ?>">
	<input type="hidden" name="search_smonth" value="<?php echo $search_smonth ?>">
	<input type="hidden" name="search_emonth" value="<?php echo $search_emonth ?>">

	<fieldset class="scheduler-border">
		<legend class="scheduler-border"><?php echo __("Backup Master"); ?></legend>
		<div class="row">
			<div class="form-group col-md-6 col-sm-12">
				<label for="term_id" class="col-md-4 col-sm-12 required"><?php echo __("期間選択"); ?></label>
				<div class="col-md-8 col-sm-12">
					<select id="term_id" name="term_id" class="form-control">
						<option value=""> -- Select Term --</option>
						<?php if (!empty($term_list)) :
							foreach ($term_list as $term_id => $term) :
						?>
								<?php $select = ($term_id == $search_termid) ? 'selected' : ''; ?>
								<option value="<?php echo $term_id; ?>" <?php echo $select ?>>
									<?php echo $term['display_term_name']; ?>
								</option>
						<?php endforeach;
						endif; ?>
					</select>
				</div>
			</div>
			<div class="form-group col-md-6 col-sm-12">
				<label for="type" class="col-md-4 col-sm-12 required"><?php echo __("種類を選択"); ?></label>
				<div class="col-md-8 col-sm-12">
					<select id="type" name="type" class="form-control">
						<option value=""> -- Select Type --</option>
						<?php if (!empty($type_list)) :
							foreach ($type_list as $type_no => $type) :
						?>
								<?php $select = ($type_no == $search_type) ? 'selected' : ''; ?>
								<option value="<?php echo $type_no; ?>" <?php echo $select ?>>
									<?php echo __($type); ?>
								</option>
						<?php endforeach;
						endif; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="form-group col-md-6 col-sm-12">
				<label for="hq_select" class="col-md-4 col-sm-12"><?php echo __('本部を選択'); ?></label>
				<div class="col-md-8 col-sm-12">
					<select multiple="multiple" name="headquarters[]" id="hq_select" class="form-control" value="<?php if (!empty($search_headid)) {
																														echo (implode(',', $search_headid));
																													} ?>">
						<option></option>
						<option value="<?php echo implode(',', array_keys($head_dept_list)); ?>"><?php echo __('All'); ?></option>
						<?php foreach ($head_dept_list as $head_dept_id => $head_dept_name) : ?>
							<?php $select = (in_array($head_dept_id, $search_headid)) ? 'selected' : ''; ?>
							<option value="<?php echo $head_dept_id; ?>" optgroup <?php echo ($select) ?>><?php echo __($head_dept_name); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="form-group col-md-6 col-sm-12 text-right adjust-padding">
				<div class="col-md-4 col-sm-12 input-group from_date" style="margin-bottom:15px;">
					<?php
					$sm_value = (!empty($search_smonth)) ? $search_smonth : '';
					$em_value = (!empty($search_emonth)) ? $search_emonth : '';
					?>
					<input type="text" class="form-control" name="from_month" id="from_month" value="<?php echo $sm_value ?>" placeholder="Start Month" title="<?php echo __('開始月'); ?>" autocomplete="off" />
					<span class="input-group-addon">
						<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
				<div class="col-md-4 col-sm-12 input-group to_date">
					<input type="text" class="form-control" name="to_month" id="to_month" value="<?php echo $em_value ?>" placeholder="End Month" title="<?php echo __('終了月'); ?>" autocomplete="off" />
					<span class="input-group-addon">
						<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-6"></div>
			<div class="col-lg-6">
				<div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12 text-right">
					<button type="button" class="btn btn-success btn_sumisho" id="search_btn">
						<?php echo __("検索"); ?>
					</button>
					<?php if ($user_level == AdminLevel::ADMIN) : ?>
						<button type="button" class="btn btn-success btn_sumisho" id="backup_btn">
							<?php echo __("バックアップ"); ?>
						</button>
					<?php endif ?>
				</div>
			</div>
		</div>
	</fieldset>
</div>
<?php if ($user_level == AdminLevel::ADMIN) : ?>
	<div class="task_list">
		<?php if (!empty($task_list)) : ?>
			<table class="table table-bordered tbl_data_list" id="tbl_acc">
				<caption>バックアップ予定のデータ一覧</caption>
				<thead>
					<tr class="blank-cell">
						<th class="text-center"><?php echo __('期間名'); ?></th>
						<th class="text-center"><?php echo __('本部'); ?></th>
						<th class="text-center"><?php echo __('フォーム種類'); ?></th>
						<th class="text-center"><?php echo __('開始月'); ?></th>
						<th class="text-center"><?php echo __('終了月'); ?></th>
						<th class="text-center"><?php echo __('作成者'); ?></th>
						<th class="text-center"><?php echo __('作成日'); ?></th>
						<th class="text-center"><?php echo __('アクション'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($task_list as $task) : ?>
						<tr>
							<td><?php echo $task['term_name']; ?></td>
							<td><?php echo $task['head_dept_name']; ?></td>
							<td><?php echo $task['file_type']; ?></td>
							<td class="text-center"><?php echo (empty($task['start_month']) ? '&#8722;' : (new DateTime($task['start_month']))->format('Y-m')); ?></td>
							<td class="text-center"><?php echo (empty($task['end_month']) ? '&#8722;' : (new DateTime($task['end_month']))->format('Y-m')); ?></td>
							<td><?php echo $task['created_user']; ?></td>
							<td><?php echo $task['created_date']; ?></td>
							<td style="text-align: center;">
								<a class="trash-can" href="#" onclick="Delete_Queued_Row(<?= h($task['backup_id']) ?>)"><i class="fa-regular fa-trash-can"></i><?php echo __("削除"); ?></a>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>
	</div>
<?php endif ?>


<div class="folder_list">
	<?php if (!empty($folder_list)) : ?>
		<?php $step_1_count = 0; ?>
		<?php foreach ($folder_list[$archive_folder] as $term_name => $each_term) : ?>
			<?php $step_1_count++; ?>
			<div id="step_1_<?php echo ($step_1_count) ?>" class="folder">
				<span class="arrow"><?php echo $term_name; ?></span>

				<div class="pull-right">
					<span data-url="<?php echo ($archive_folder . '/' . $term_name) ?>" class="download-btn">
						<i class="fa-solid fa-cloud-arrow-down"></i>
					</span>
					<?php if ($user_level == AdminLevel::ADMIN) : ?>
						<span data-url="<?php echo ($archive_folder . '/' . $term_name) ?>" class="delete-btn">
							<i class="fa-regular fa-trash-can"></i>
						</span>
					<?php endif ?>
				</div>
			</div>
			<div id="step_1_<?php echo ($step_1_count) ?>_wpr" style="display:none" class="ml-30">
				<?php $step_2_count = 0; ?>
				<?php foreach ($each_term as $type_name => $each_type) : ?>
					<?php $step_2_count++; ?>
					<div id="step_2_<?php echo ($step_1_count) ?>_<?php echo ($step_2_count) ?>" class="folder">
						<span class="arrow"><?php echo $type_name; ?></span>

						<span data-url="<?php echo ($archive_folder . '/' . $term_name . '/' . $type_name) ?>" class="download-btn">
							<i class="fa-solid fa-cloud-arrow-down"></i>
						</span>
						<?php if ($user_level == AdminLevel::ADMIN) : ?>
							<span data-url="<?php echo ($archive_folder . '/' . $term_name . '/' . $type_name) ?>" class="delete-btn">
								<i class="fa-regular fa-trash-can"></i>
							</span>
						<?php endif ?>
					</div>
					<div id="step_2_<?php echo ($step_1_count) ?>_<?php echo ($step_2_count) ?>_wpr" style="display:none" class="ml-60">
						<?php $step_3_count = 0; ?>
						<?php foreach ($each_type as $folder_1 => $data_1) : ?>
							<?php $step_3_count++; ?>
							<?php if (is_array($data_1)) : ?>
								<div id="step_3_<?php echo ($step_1_count) ?>_<?php echo ($step_2_count) ?>_<?php echo ($step_3_count) ?>" class="folder">
									<span class="arrow"><?php echo $folder_1; ?></span>

									<span data-url="<?php echo ($archive_folder . '/' . $term_name . '/' . $type_name . '/' . $folder_1) ?>" class="download-btn">
										<i class="fa-solid fa-cloud-arrow-down"></i>
									</span>
									<?php if ($user_level == AdminLevel::ADMIN) : ?>
										<span data-url="<?php echo ($archive_folder . '/' . $term_name . '/' . $type_name . '/' . $folder_1) ?>" class="delete-btn">
											<i class="fa-regular fa-trash-can"></i>
										</span>
									<?php endif ?>
								</div>
								<div id="step_3_<?php echo ($step_1_count) ?>_<?php echo ($step_2_count) ?>_<?php echo ($step_3_count) ?>_wpr" style="display:none" class="ml-90">
									<?php $step_4_count = 0; ?>
									<?php foreach ($data_1 as $folder_2 => $data_2) : ?>
										<?php $step_4_count++; ?>
										<?php if (is_array($data_2)) : ?>
											<div id="step_4_<?php echo ($step_1_count) ?>_<?php echo ($step_2_count) ?>_<?php echo ($step_3_count) ?>_<?php echo ($step_4_count) ?>" class="folder">
												<span class="arrow"><?php echo $folder_2; ?></span>
												<span data-url="<?php echo ($archive_folder . '/' . $term_name . '/' . $type_name . '/' . $folder_1 . '/' . $folder_2) ?>" class="download-btn">
													<i class="fa-solid fa-cloud-arrow-down"></i>
												</span>
												<?php if ($user_level == AdminLevel::ADMIN) : ?>
													<span data-url="<?php echo ($archive_folder . '/' . $term_name . '/' . $type_name . '/' . $folder_1 . '/' . $folder_2) ?>" class="delete-btn">
														<i class="fa-regular fa-trash-can"></i>
													</span>
												<?php endif ?>
											</div>
										<?php else : ?>
											<div class="file">
												<?php
												$data_2 = (strpos($data_2, '@&@')) ? explode('@&@', $data_2) : $data_2;
												echo $data_2[0];
												?>
												<span data-url="<?php echo ($archive_folder . '/' . $term_name . '/' . $type_name . '/' . $folder_1 . '/' . $data_2[0]) ?>" class="download-btn">
													<i class="fa-solid fa-cloud-arrow-down"></i>
												</span>
												<input type="hidden" name="download_file[<?php echo $archive_folder ?>][<?php echo $term_name ?>][<?php echo $type_name ?>][<?php echo $folder_1 ?>][]" value="<?php echo $data_2[0] ?>">
												<?php if ($user_level == AdminLevel::ADMIN) : ?>
													<span data-url="<?php echo ($archive_folder . '/' . $term_name . '/' . $type_name . '/' . $folder_1 . '/' . $data_2[0]) ?>" class="delete-btn">
														<i class="fa-regular fa-trash-can"></i>
													</span>
												<?php endif ?>
												&emsp;
												<span class="display-file">
													<i class="fas fa-file-alt" aria-hidden="true" color="green"></i>
													<?php echo $data_2[2]; ?>
												</span>
												&emsp;
												<span class="display-dtime">
													<i class="far fa-calendar-alt" aria-hidden="true"></i>
													<?php echo $data_2[1]; ?>
												</span>
											</div>
										<?php endif ?>
									<?php endforeach ?>
								</div>
							<?php else : ?>
								<div class="file">
									<?php
									$data_1 = (strpos($data_1, '@&@')) ? explode('@&@', $data_1) : $data_1;
									echo $data_1[0];
									?>
									<span data-url="<?php echo ($archive_folder . '/' . $term_name . '/' . $type_name . '/' . $data_1[0]) ?>" class="download-btn">
										<i class="fa-solid fa-cloud-arrow-down"></i>
									</span>
									<input type="hidden" name="download_file[<?php echo $archive_folder ?>][<?php echo $term_name ?>][<?php echo $type_name ?>][]" value="<?php echo $data_1[0] ?>">
									<?php if ($user_level == AdminLevel::ADMIN) : ?>
										<span data-url="<?php echo ($archive_folder . '/' . $term_name . '/' . $type_name . '/' . $data_1[0]) ?>" class="delete-btn">
											<i class="fa-regular fa-trash-can"></i>
										</span>
									<?php endif ?>
									&emsp;
									<span class="display-file">
										<i class="fas fa-file-alt" aria-hidden="true" color="green"></i>
										<?php echo $data_1[2]; ?>
									</span>
									&emsp;
									<span class="display-dtime">
										<i class="far fa-calendar-alt" aria-hidden="true"></i>
										<?php echo $data_1[1]; ?>
									</span>
								</div>
							<?php endif ?>
						<?php endforeach ?>
					</div>
				<?php endforeach ?>
			</div>
		<?php endforeach ?>
	<?php else : ?>
		<div class="row">
			<div class="col-sm-12">
				<div class="no-data">
					There is no folder list to display.
				</div>
			</div>
		</div>
	<?php endif ?>
</div>

<input type="hidden" id="url" name="url">
<input type="hidden" id="backup_id" name="backup_id">
<?php echo $this->Form->end();  ?>

<script type="text/javascript">
	$(document).ready(function() {
		// $('div.folder:contains("202004")').parents().show();
		// $('div.folder:contains("202004")').parent('.folder').addClass('in');
		$("#hq_select").amsifySelect();
		$(".amsify-label").css({
			"font-size": "14px",
			"font-family": "Helvetica"
		});
		// $(".fa").css({"font-size" : "14px", "font-weight" : "900", "margin-right" : "-7px", "margin-top" : "6px"});

		// $(".from_date input:text").prop("disabled",true);
		// $(".to_date input:text").prop("disabled",true);

		// $(".adjust-padding").hide();

		var search_hqcnt = ('<?php echo count($search_headid); ?>');
		var all_hqcnt = ('<?php echo count($head_dept_list); ?>');

		if (search_hqcnt == 0) $(".amsify-label").text("-- Select Headquarters --");
		else if (search_hqcnt >= all_hqcnt) $(".amsify-label").text("ALL");
		var stype = $("#type").val();
		if (stype == '03' || stype == '04') {
			Check_OnChange(stype, true);
		} else {
			$(".from_date input:text").prop("disabled", true);
			$(".to_date input:text").prop("disabled", true);
			$(".adjust-padding").hide();
		}

		$(".folder .arrow").on("click", function() {
			var folderId = $(this).parent('.folder').prop("id");
			var blockId = folderId + '_wpr';
			if ($(this).parent('.folder').hasClass('in')) {
				$("#" + blockId).hide();
				$(this).parent('.folder').removeClass('in');
			} else {
				$("#" + blockId).show();
				$(this).parent('.folder').addClass('in');
			}
		});

		$(".download-btn").on("click", function() {
			var url = $(this).attr("data-url");
			Click_Download(url);
		});

		$(".delete-btn").on("click", function() {
			var url = $(this).attr("data-url");
			Click_Delete(url);
		})

		$("button.amsify-select-clear").click(function() {
			event.preventDefault();
			$(".amsify-label").text("-- Select Headquarters --");

			return true;

		});

		$("#hq_select").on("change", function() {
			if ($(".amsify-label").text() == "-----Select-----") {
				$(".amsify-label").text("-- Select Headquarters --");
			}

			if ($(".active input").val() == "<?php echo implode(',', array_keys($head_dept_list)); ?>") {
				$('.amsify-list li').removeClass('active');
				$('.amsify-list li').addClass('active');
				$(".amsify-label").text("ALL");
			}
		});

		// For Headquarter drop down icon
		$(".drop_down_arrow").click(function() {

			var dataID = $(this).attr('data-id');
			var record = '.drop_down_arrow_' + dataID;
			var ar_ex = $(record).attr('aria-expanded');
			// alert(ar_ex);
			if (ar_ex == "true") {
				$(record).find('.fas').removeClass('fa-chevron-up');
				$(record).find('.fas').addClass('fa-chevron-down');
			} else if (ar_ex == "false") {
				$(record).find('.fas').removeClass('fa-chevron-down');
				$(record).find('.fas').addClass('fa-chevron-up');
			} else {
				$(record).find('.fas').removeClass('fa-chevron-down');
				$(record).find('.fas').addClass('fa-chevron-up');
			}

		});

		/* float thead */
		// if($('#tbl_head').length > 0) {
		//     var $table = $('#tbl_head');
		//     $table.floatThead({
		//         responsiveContainer: function($table){
		//             return $table.closest('.table-responsive');
		//         }
		//     });
		// }


	});

	$("#term_id").on("change", function() {
		let type = $("#type").val();
		Check_OnChange(type);
	});

	$("#type").on("change", function() {
		let term_id = $("#term_id").val();
		Check_OnChange(term_id);
	});

	$("#backup_btn").click(function() {
		$("#success").empty();
		$("#error").empty();

		let term_id = $("#term_id").val();
		let type = $("#type").val();
		let hq_select = $("#hq_select").val();
		let start_month = $("#from_month").val();
		let end_month = $("#to_month").val();
		let from_disabled, to_disabled;

		// for unselected head-department
		/*if(hq_select == '' || hq_select == null){
			$("#hq_select").val("<?php //echo implode(',', array_keys($head_dept_list));
									?>");
		}*/
		let error_flag = true;

		if (!checkNullOrBlank(term_id)) {
			$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("期間"); ?>']) + "</div>");
			error_flag = false;
		}

		if (!checkNullOrBlank(type)) {
			$("#error").append("<div>" + errMsg(commonMsg.JSE002, ['<?php echo __("タイプ"); ?>']) + "</div>");
			error_flag = false;
		}

		from_disabled = $("#from_month").attr("disabled");
		to_disabled = $("#to_month").attr("disabled");

		if (typeof from_disabled == 'undefined' || from_disabled == false && typeof to_disabled == 'undefined' || to_disabled == false) {
			if (!checkNullOrBlank(start_month)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, ['<?php echo __("開始月"); ?>']) + "</div>");
				error_flag = false;
			}

			if (!checkNullOrBlank(end_month)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE001, ['<?php echo __("終了月"); ?>']) + "</div>");
				error_flag = false;
			}

			if (new Date(start_month) > new Date(end_month)) {
				$("#error").append("<div>" + errMsg(commonMsg.JSE071) + "</div>");
				error_flag = false;
			}
		}

		let head_dept_list = [];
		$(".amsify-list .active input").each(function(index) {
			head_dept_list.push($(this).val());
		});

		if (head_dept_list.length == 0) {
			<?php foreach (array_keys($head_dept_list) as $value) : ?>
				head_dept_list.push("<?php echo $value; ?>");
			<?php endforeach ?>
		}

		if (error_flag) {
			let exit_flag = false;
			<?php foreach ($task_list as $task) : ?>
				if (term_id == "<?php echo $task['term_id']; ?>") {
					if (type == "<?php echo $task['type_no']; ?>") {
						if (jQuery.inArray("<?php echo $task['head_dept_id']; ?>", head_dept_list) !== -1) {
							exit_flag = true;
						}
					}
				}
			<?php endforeach ?>
			if (exit_flag) {
				$.confirm({
					title: "<?php echo __('バックアップの確認'); ?>",
					icon: "fas fa-exclamation-circle",
					type: "orange",
					typeAnimated: true,
					closeIcon: true,
					columnClass: 'medium',
					animateFromElement: true,
					animation: 'top',
					draggable: false,
					content: errMsg(commonMsg.JSE070),
					buttons: {
						ok: {
							text: "OK",
							btnClass: 'btn-orange',
							action: function() {
								loadingPic();
								document.forms[0].action = "<?php echo $this->webroot ?>BrmBackupFile/backup";
								document.forms[0].submit();
								scrollText();
								return true;
							}
						},
						cancel: {
							text: '<?php echo __("いいえ"); ?>',
							btnClass: 'btn-default',
							cancel: function() {
								console.log('the user clicked cancel');
								scrollText();
							}

						}
					},
					theme: 'material',
					animation: 'rotateYR',
					closeAnimation: 'rotateXR'
				});
			} else {
				document.forms[0].action = "<?php echo $this->webroot ?>BrmBackupFile/backup";
				document.forms[0].submit();
				loadingPic();
				return true;
			}
		}
	});

	$("#search_btn").click(function() {
		$("#success").empty();
		$("#error").empty();

		let term_id = $("#term_id").val();
		let type = $("#type").val();
		let hq_select = $("#hq_select").val();
		let start_month = $("#from_month").val();
		let end_month = $("#to_month").val();

		// for unselected head-department
		if (hq_select == '' || hq_select == null) {
			$("#hq_select").val("<?php echo implode(',', array_keys($head_dept_list)); ?>");
		}
		let head_dept_list = [];

		$(".amsify-list .active input").each(function(index) {
			head_dept_list.push($(this).val());
		});

		if (head_dept_list.length == 0) {
			<?php foreach (array_keys($head_dept_list) as $value) : ?>
				head_dept_list.push("<?php echo $value; ?>");
			<?php endforeach ?>
		}
		$("#hq_select").val(head_dept_list);
		document.forms[0].action = "<?php echo $this->webroot ?>BrmBackupFile/searchArchiveData";
		document.forms[0].method = "POST";
		document.forms[0].submit();

	});

	function Click_Download(url) {
		$("#url").val(url);
		$.confirm({
			title: "<?php echo __('ダウンロードの確認'); ?>",
			icon: "fas fa-exclamation-circle",
			type: 'green',
			typeAnimated: true,
			closeIcon: true,
			columnClass: 'medium',
			animateFromElement: true,
			animation: 'top',
			draggable: false,
			content: "<?php echo __("Are you sure to download?"); ?>",
			buttons: {
				ok: {
					text: '<?php echo __("はい"); ?>',
					btnClass: 'btn-green',
					action: function() {
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmBackupFile/Download_Archive";
						document.forms[0].method = "POST";
						document.forms[0].submit();
						scrollText();
						return true;
					}
				},
				cancel: {
					text: '<?php echo __("いいえ"); ?>',
					btnClass: 'btn-default',
					cancel: function() {
						console.log('the user clicked cancel');
						scrollText();
					}

				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		});

	}

	function Click_Delete(url) {
		$("#url").val(url);
		$.confirm({
			title: "<?php echo __('削除確認'); ?>",
			icon: "fas fa-exclamation-circle",
			type: "red",
			typeAnimated: true,
			closeIcon: true,
			columnClass: 'medium',
			animateFromElement: true,
			animation: 'top',
			draggable: false,
			content: errMsg(commonMsg.JSE017),
			buttons: {
				ok: {
					text: '<?php echo __("はい"); ?>',
					btnClass: 'btn-red',
					action: function() {
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmBackupFile/Delete_File";
						document.forms[0].method = "POST";
						document.forms[0].submit();
						loadingPic();
						scrollText();
						return true;
					}
				},
				cancel: {
					text: '<?php echo __("いいえ"); ?>',
					btnClass: 'btn-default',
					cancel: function() {
						console.log('the user clicked cancel');
						scrollText();
					}

				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		});

	}

	function Delete_Queued_Row(id) {
		$("#backup_id").val(id);
		$.confirm({
			title: "<?php echo __('削除確認'); ?>",
			icon: "fas fa-exclamation-circle",
			type: "red",
			typeAnimated: true,
			closeIcon: true,
			columnClass: 'medium',
			animateFromElement: true,
			animation: 'top',
			draggable: false,
			content: errMsg(commonMsg.JSE017),
			buttons: {
				ok: {
					text: '<?php echo __("はい"); ?>',
					btnClass: 'btn-red',
					action: function() {
						loadingPic();
						document.forms[0].action = "<?php echo $this->webroot; ?>BrmBackupFile/Delete_Queued_Row";
						document.forms[0].method = "POST";
						document.forms[0].submit();
						scrollText();
						return true;
					}
				},
				cancel: {
					text: '<?php echo __("いいえ"); ?>',
					btnClass: 'btn-default',
					cancel: function() {
						console.log('the user clicked cancel');
						scrollText();
					}

				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		});
	}

	function Check_OnChange(term_type, search_func = false) {
		let term_id = $("#term_id").val();
		let type = $("#type").val();
		let min_date, max_date;
		let search_smonth = ('<?php echo $search_smonth; ?>');
		let search_emonth = ('<?php echo $search_emonth; ?>');
		if (checkNullOrBlank(term_type)) {
			if (type == "03" || type == "04") {
				<?php foreach ($term_list as $term_id => $term) : ?>
					if (term_id == <?php echo $term_id; ?>) {
						min_date = moment(new Date("<?php echo $term_list[$term_id]['min_date']; ?>")).format("YYYY-MM");
						max_date = moment(new Date("<?php echo $term_list[$term_id]['max_date']; ?>")).format("YYYY-MM");
					}
				<?php endforeach ?>

				if (min_date && max_date) {

					$(".from_date input:text").prop("disabled", false);
					$(".to_date input:text").prop("disabled", false);

					$(".adjust-padding").show();

					$(".from_date").datetimepicker({
						format: "YYYY-MM",
						minDate: new Date(min_date),
						maxDate: new Date(max_date),
					});
					$(".to_date").datetimepicker({
						format: "YYYY-MM",
						minDate: new Date(min_date),
						maxDate: new Date(max_date),
					});
					$(".from_date")
						.data("DateTimePicker")
						.options({
							'minDate': min_date,
							'maxDate': max_date
						});
					$(".to_date")
						.data("DateTimePicker")
						.options({
							'minDate': min_date,
							'maxDate': max_date
						});
					if ($(".from_date input:text").empty()) {
						$(".from_date input:text").val(moment(new Date(min_date)).format("YYYY-MM"));
					}
					if ($(".to_date input:text").empty()) {
						$(".to_date input:text").val(moment(new Date(max_date)).format("YYYY-MM"));
					}
					if (search_smonth && search_func) $(".from_date input:text").val(moment(new Date(search_smonth)).format("YYYY-MM"));
					if (search_emonth && search_func) $(".to_date input:text").val(moment(new Date(search_emonth)).format("YYYY-MM"));
				} else {
					$(".from_date input:text").val("");
					$(".to_date input:text").val("");
					$(".adjust-padding").hide();
					$(".from_date input:text").prop("disabled", true);
					$(".to_date input:text").prop("disabled", true);
					$("#error").empty();
				}

			} else {
				$(".from_date input:text").val("");
				$(".to_date input:text").val("");
				$(".adjust-padding").hide();
				$(".from_date input:text").prop("disabled", true);
				$(".to_date input:text").prop("disabled", true);
				$("#error").empty();
			}
		}
	}

	function scrollText() {
		var tes = $('#error').text();
		var tes1 = $('#success').text();
		if (tes) {
			$("html, body").animate({
				scrollTop: 0
			}, 400);
		}
		if (tes1) {
			$("html, body").animate({
				scrollTop: 0
			}, 400);
		}
	}

	/*  
	 * show hide loading overlay
	 *@Zeyar Min  
	 */
	function loadingPic() {
		$("#overlay").show();
		$('.jconfirm').hide();
	}
</script>