<style>

	.align-left {
		text-align: left !important;
	}
	.align-right {
		text-align: right !important;
	}
	.vbottom {
		margin-top: 230px;
	}
	.align-center {
		text-align: center !important;
	}
	/*#tbl_data_list {
		min-width: 2150px;
	}*/
	.tbl_data_list td {
		vertical-align: middle;
	}
	.btn_approve_style {
		width: 150px;
		margin: 5px;
	}
	.btn-delete {
		text-decoration: underline;
		color: blue;
		cursor: pointer;
	}
	.fl-scrolls {
		margin-bottom: 40px;/* modify floating scroll bar */
		z-index: 1;
	}
	@media screen and (max-width: 600px) {
		.vbottom {
			margin-top: 30px;
		}
	}
	/* Begin Edit for STATUS BCMM Sandi*/
	.status-style {
		cursor: pointer;
		color: #19b5fe;
    	text-decoration: underline;
	}	
	.modal_tbl_wrapper {
		max-height: 400px;
		overflow: scroll;
	}
	.mdl_thead_style {
		text-align: center;
		vertical-align: middle !important;
	}

	/* End Edit for STATUS BCMM Sandi*/
	#modelPrint {
		display: flex;
	    align-items: center;
	    justify-content: center;
	}

	.jconfirm-box-container {
      margin-left: unset !important;
   }
   .acc_review{
	margin-top:0px;
   }
</style>
<?php 
echo $this->element('autocomplete', array(
						"to_level_id" => "",
						"cc_level_id" => "",
						"bcc_level_id" => "",
						"submit_form_name" => "data_search_form",
						"MailSubject" => "",
                		"MailTitle"   => "",
                		"MailBody"    => ""
						));

?>
<div id="overlay">
	<span class="loader"></span>
</div>

<h3><?php echo __("実地調査票【固定資産】"); ?></h3><hr>
<div class="row">
	<div class="col-sm-12">
		<div class="success" id="success"><?php echo ($this->Session->check("Message.assetsOK"))? $this->Flash->render("assetsOK") : ''; ?></div>
		<div class="error" id="error"><?php echo ($this->Session->check("Message.assetsFail"))? $this->Flash->render("assetsFail") : ''; ?></div>
	</div>

</div>
<?php
    if (!empty($this->request->query)) {
        $sec_key_name = h($this->request->query('sec_key_name'));
        $intsall_location = h($this->request->query('intsall_location'));
        $physical_check = h($this->request->query('physical_check'));
        $label_check = h($this->request->query('label_check'));
        $label_number = h($this->request->query('label_number'));
        $picture_check = h($this->request->query('picture_check'));
        $hdStatus = h($this->request->query('hdStatus'));
    	
    } else {
        $sec_key_name = '';
        $intsall_location = '';
        $physical_check = '';
        $label_check = '';
        $label_number = '';
        $picture_check = '';
        $hdStatus = '';
        $new_status_chk = '';
    }
    # Disable all input except Search for user level(1,2,3,4,5)
    $disable_inputs = '';
    /*if ($user_level != 7 && $user_level != 6 && $user_level != 3 && $user_level != 4 && $user_level != 1) {
        $disable_inputs = 'disabled="disabled"';
    }*/
    if (!$checkState['canSave']) {
        $disable_inputs = 'disabled="disabled"';
    }

?>
<form name="data_search_form" id="data_search_form" method="get" action="<?php echo $this->Html->url(array('controller'=> 'Assets','action' => 'index')); ?>">
	<input type="hidden" name="toEmail" id="toEmail" value="">
	<input type="hidden" name="ccEmail" id="ccEmail" value="">
	<input type="hidden" name="bccEmail" id="bccEmail" value="">
	<input type="hidden" name="mailSubj" id="mailSubj">
    <input type="hidden" name="mailBody" id="mailBody">
    <input type="hidden" name="dataArr" id="dataArr">
    <input type="hidden" name="path" id="path">
    <input type="hidden" name="mailSend" id="mailSend">

	<div class="row">
		<div class="col-sm-9">
			<div class="form-group row">
				<div class="col-sm-5">
					<label for="layer_code" class="col-sm-4 col-form-label">
						<?php echo __('部署コード');?>
					</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" name="layer_code" id="layer_code" value="<?php echo $this->Session->read('SESSION_LAYER_CODE'); ?>" disabled>	
					</div>
				</div>
				<div class="col-sm-5">
					<label for="event_name" class="col-sm-4 col-form-label">
						<?php echo __('イベント名');?>
					</label>
					<div class="col-sm-8">
					 	<input type="text" class="form-control" id="event_name" name="event_name" value="<?php echo $this->Session->read('EVENT_NAME'); ?>" disabled />
					</div>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-sm-5">
					<label for="sec_key_name" class="col-sm-4 col-form-label">
						<?php echo __('第2キー名称');?>
					</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" name="sec_key_name" id="sec_key_name" value="<?php echo $sec_key_name; ?>" />	
					</div>
				</div>
				<div class="col-sm-5">
					<label for="intsall_location" class="col-sm-4 col-form-label">
						<?php echo __('設置場所');?>
					</label>
					<div class="col-sm-8">
					 	<input type="text" class="form-control" id="intsall_location" name="intsall_location" value="<?php echo $intsall_location; ?>" />
					</div>
				</div>
			</div>
			
			<div class="form-group row">
				<div class="col-sm-5">
					<label for="label_check" class="col-sm-4 col-form-label">
						<?php echo __('ラベル確認');?>
					</label>
					<div class="col-sm-8">
						<select class="form-control" id="label_check" name="label_check">
                            <?php
                                $lbl_check = ($label_check == 1)? 'selected="selected"':'';
                                $lbl_uncheck = ($label_check == 2)? 'selected="selected"':'';
                            ?>
							<option value="">--- Select ---</option>
							<option value="1" <?php echo $lbl_check; ?>>Check</option>
							<option value="2" <?php echo $lbl_uncheck; ?>>Uncheck</option>
						</select>
					</div>
				</div>
				<div class="col-sm-5">
					<label for="label_number" class="col-sm-4 col-form-label">
						<?php echo __('ラベル番号');?>
					</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" name="label_number" id="label_number" value="<?php echo $label_number; ?>">	
					</div>
				</div>
			</div>
			<div class="form-group row">
				
				<div class="col-sm-5">
					<label for="physical_check" class="col-sm-4 col-form-label">
						<?php echo __('現物確認欄');?>
					</label>
					<div class="col-sm-8">
						<select class="form-control" name="physical_check" id="physical_check">
							<?php
                                $phy_check = ($physical_check == 1)? 'selected="selected"':'';
                                $phy_uncheck = ($physical_check == 2)? 'selected="selected"':'';
                            ?>
							<option value="">--- Select ---</option>
							<option value="1" <?php echo $phy_check; ?>>Check</option>
							<option value="2" <?php echo $phy_uncheck; ?>>Uncheck</option>
						</select>	
					</div>
				</div>
				<div class="col-sm-5">
					<label for="picture_check" class="col-sm-4 col-form-label">
						<?php echo __('画像');?>
					</label>
					<div class="col-sm-8">
						<select class="form-control" name="picture_check" id="picture_check">
							<?php
                                $pic_all = ($picture_check == 0)? 'selected="selected"':'';
                                $pic_exit = ($picture_check == 1)? 'selected="selected"':'';
                                $pic_not_exit = ($picture_check == 2)? 'selected="selected"':'';
                            ?> 
							<option value="0" <?php echo $pic_all; ?>>--- All ---</option>
							<option value="1" <?php echo $pic_exit; ?>>Exist</option>
							<option value="2" <?php echo $pic_not_exit; ?>>Not Exist</option>


						</select>
					</div>
				</div>
			</div>
		<div class="form-group row">
			<div class="col-sm-5">
				<label class="col-sm-4 col-form-label">
						<?php echo __('状態');?>
				</label>
				<div class="col-sm-8">
					<?php
                        $strIndex1="";$strIndex2="";$strIndex3="";$strIndex4="";$strIndex5="";
                        $index1=strpos($hdStatus, '1');#New
                        if ($index1>-1) {
                            $strIndex1='checked';
                        }
                        $index2=strpos($hdStatus, '2');#Finish
                        if ($index2>-1) {
                            $strIndex2='checked';
                        }
                        $index3=strpos($hdStatus, '3');#Move
                        if ($index3>-1) {
                            $strIndex3='checked';
                        }
                        $index4=strpos($hdStatus, '4');#Lost
                        if ($index4>-1) {
                            $strIndex4='checked';
                        }
                        $index5=strpos($hdStatus, '5');#Sold
                        if ($index5>-1) {
                            $strIndex5='checked';
                        }
                        
                    ?>
					<input type="checkbox" name="new_chk" class="new_chk" id="new_chk" value="1"
					<?php echo $strIndex1; ?> ><?php echo __("新規");?>
					<input type="checkbox" name="already_chk" class="already_chk" id="already_chk" value="2" <?php echo $strIndex2; ?> ><?php echo __("済");?>
					<input type="checkbox" name="move_chk"  class="move_chk" id="move_chk" value="3" <?php echo $strIndex3; ?> ><?php echo __("移動");?>
					<input type="checkbox" name="lost_chk"  class="lost_chk" id="lost_chk" value="4" <?php echo $strIndex4; ?> ><?php echo __("除却");?>
					<input type="checkbox" name="sold_chk"  class="sold_chk" id="sold_chk" value="5" <?php echo $strIndex5; ?> ><?php echo __("売却");?>
				</div>
				<input type="hidden" id="hdStatus" name="hdStatus" class="hdStatus" value="" />
			</div>
			<div class="col-sm-5 align-right">
				<div class="col-sm-12" style="margin-top: 10px;">
					<input type="submit" class="btn btn-success btn_sumisho btn_search" value="<?php echo __("検索"); ?>">
				</div>
			</div>
		</div>
		</div>
	</div>
</form>

<br/>

<?php if ($count > 0) {  ?>
	<div class="text-right">
		<div class="">
			<form name="asset_action_form" id="asset_action_form" method="post">
				<?php //pr($checkState);
				if ($checkState['canSave']) { ?>
					<input type="button" name="btn_save" id="btn_save" class="btn btn-success btn_sumisho" value="<?php echo __("保存") ?>">
				<?php }

				if($checkState['canRej']) { ?>				
					<input type="button" name="btn_reject" id="btn_reject" class="btn btn-success btn_approve_style btn_sumisho" value="<?php echo __("差し戻し"); ?>">
				<?php }

				if($checkState['canApp']) { ?>	
					<input type="button" name="btn_approve" id="btn_approve" class="btn btn-success btn_approve_style btn_sumisho" value="<?php echo __("承認"); ?>">
				<?php }

				if($checkState['canCancel']) { ?>
					<input type="submit" name="btn_approve_cancel"  id="btn_approve_cancel" class="btn btn-success btn_approve_style btn_sumisho" value="<?php echo __("承認キャンセル"); ?>">
				<?php }

				if ($total_rows<20) { ?>

					<input type="button" name="btn_pdf" id="btn_pdf" class="btn btn-success" value="<?php echo __("固定資産調査票ダウンロード") ?>">

				<?php } else { ?>

					<input type="button" name="btn_pdf" class="btn btn-success" value="<?php echo __("固定資産調査票ダウンロード") ?>" data-target="#myPDFModal" data-toggle="modal" data-backdrop="static" data-keyboard="false" >

				<?php } ?>
				
				<input type="button" name="btn_excel_download" id="btn_excel_download" class="btn btn-success" value="<?php echo __("貼付不可一覧表ダウンロード") ?>">
				<input type="hidden" id="hddTotalRow" name="hddTotalRow" class="hddTotalRow" value=""  />
				<input type="hidden" id="hddImage" name="hddImage" class="hddImage" value=""  />
			</form>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="msgfont"><?php echo $row_count; ?></div>
			<div class="table-responsive tbl-wrapper required-css">
				<table class="table table-striped table-bordered acc_review tbl_data_list"  style="min-width: 2150px;">
					<thead>
						<tr>
							<th width="100px"><?php echo __("画像"); ?></th>
							<th width="100px"><?php echo __("資産番号"); ?></th>
							<th width="300px"><?php echo __("資産名称"); ?></th>
							<th width="100px"><?php echo __("取得年月日"); ?></th>
							<th width="120px">
								<?php echo __("第1キーコード"); ?><br/>
								(<?php echo __("部署コード"); ?>)
							</th>
							<th width="120px">
								<?php echo __("第1キー名称"); ?><br/>
								(<?php echo __("部署名"); ?>)
							</th>
							<th width="120px">
								<?php echo __("第2キー名称"); ?><br/>
								(<?php echo __("種類"); ?>)
							</th>
							<th width="120px"><?php echo __("当月末帳簿価額"); ?></th>
							<th>
								<?php echo __("状態"); ?>
							</th>
							<th width="120px"><?php echo __("数量"); ?></th>
							<th width="120px"><?php echo __("設置場所"); ?></th>
							<th width="90px">
								<?php echo __("現物確認欄"); ?><br/>
								<input type="checkbox" class ="chk_physical_master" name="chk_physical_master" <?php echo $disable_inputs; ?>>
							</th>
							<th width="200px"><?php echo __("確認事項に関するコメント"); ?></th>
							<th width="100px"><?php echo __("ラベル番号"); ?></th>
							<th width="90px">
								<?php echo __("ラベル確認欄"); ?><br/>
								<input type="checkbox" class ="chk_label_master" name="chk_label_master" <?php echo $disable_inputs; ?>>
							</th>
							<th width="200px"><?php echo __("ラベル貼付不可理由"); ?></th>
						
							<?php if ($user_level == 1 && $data[0]['Asset']['flag'] != 4 ) { ?>
								<th></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php
                            // $showSaveReqBtn = false;
                            for ($i=0; $i<$count; $i++) {
                                $asset_id = $data[$i]['Asset']['id'];
                                $event_id = $data[$i]['Asset']['event_id'];
                                $tbl_layer_code = $data[$i]['Asset']['layer_code'];
                                $name_jp = $data[$i]['Asset']['layer_name'];
                                $sec_key_name = $data[$i]['Asset']['2nd_key_name'];
                                $amount = $data[$i]['Asset']['amount'];
                                $asset_no = $data[$i]['Asset']['asset_no'];
                                $asset_name = $data[$i]['Asset']['asset_name'];
                                $quantity = $data[$i]['Asset']['quantity'];
                                $acq_date = $data[$i]['Asset']['acq_date'];
                                $place_name = $data[$i]['Asset']['place_name'];
                                $photo = $data[$i]['pic']['real_path'];
                                $photo = (empty($photo))? $this->webroot.'app/webroot/img/no_image.png':$photo;
                                $label_no = $data[$i]['Asset']['label_no'];
                                $asset_flag = $data[$i]['Asset']['flag'];
                                $asset_status = $data[$i]['Asset']['status'];
                                if ($asset_status == 1) {
                                    $status = __("新規");
                                } elseif ($asset_status == 2) {
                                    $status = __("済");
                                } elseif ($asset_status == 3) {
                                    $status = __("移動");
                                } elseif ($asset_status == 4) {
                                    $status = __("除却");
                                } elseif ($asset_status == 5) {
                                    $status = __("売却");
                                } else {
                                    $status = '';
                                }
                                # 新規 	- new  1
                                # 済	- already  2
                                # 移動 	- move  3
								# 除却 	- lost  4
                                # 売却 	- sold 5
                                # data for no reference event_id
                                $physical_chk_not_ref = $data[$i]['Asset']['not_ref_physical_chk'];
                                $label_chk_not_ref = $data[$i]['Asset']['not_ref_label_chk'];
                                $cmt_not_ref_comment = $data[$i]['cmt_not_ref']['cmt_not_ref_comment'];
                                $cmt_not_ref_remark = $data[$i]['cmt_not_ref']['cmt_not_ref_remark'];

                                # data for reference event_id with same asset_no
                                $physical_chk_ref = $data[$i]['ref_event_data']['physical_chk_ref'];
                                $label_chk_ref = $data[$i]['ref_event_data']['label_chk_ref'];
                                $cmt_ref_comment = $data[$i]['ref_event_data']['cmt_ref_comment'];
                                $cmt_ref_remark = $data[$i]['ref_event_data']['cmt_ref_remark'];
                                $assetstatus = $data[$i]['Asset']['asset_status'];#khin hnin myo
                                # if tbl_m_asset flag is 1, then show reference data
                                if ($asset_flag == 1) {
                                    if ($physical_chk_ref == 1) {
                                        $phy_status = "checked='checked'";
                                    } else {
                                        $phy_status = "";
                                    }
                                    if ($label_chk_ref == 1) {
                                        $lbl_status = "checked='checked'";
                                        $disable_remark = "disabled='disabled'";
                                    } else {
                                        $lbl_status = "";
                                        $disable_remark = "";
                                    }
                                    $comment = nl2br(h($cmt_ref_comment));
                                    $remark = nl2br(h($cmt_ref_remark));
                                } else {
                                    if ($physical_chk_not_ref == 1) {
                                        $phy_status = "checked='checked'";
                                    } else {
                                        $phy_status = "";
                                    }
                                    if ($label_chk_not_ref == 1) {
                                        $lbl_status = "checked='checked'";
                                        $disable_remark = "disabled='disabled'";
                                    } else {
                                        $lbl_status = "";
                                        $disable_remark = "";
                                    }
                                    $comment = nl2br(h($cmt_not_ref_comment));
                                    $remark = nl2br(h($cmt_not_ref_remark));
                                }
                                $comment = str_replace('<br />', "", $comment);
                                $remark = str_replace('<br />', "", $remark);

                                # show save/request btn before flag 3
                                # change color of already requested data for user level(1,5)
                                # disable inputs for already requested data of user level(7,6)
                                if ($asset_flag >= 3) {
                                    $requested_color = "flag_chk_color";
                                    $disable_requested = 'disabled="disabled"';
                                } else {
                                    // $showSaveReqBtn = true;
                                    $requested_color = "";
                                    $disable_requested = '';
                                } ?>
									<tr class="<?php echo $requested_color; ?>">
										<td width="100px"><img src="<?php echo $photo; ?>"></td>
										<td width="100px" class="align-right">
											<input type="hidden" name="asset_id" class="asset_id" value="<?php echo $asset_id; ?>" />
											<input type="hidden" name="hddAsset_number" class="hddAsset_number" value="<?php echo $asset_no; ?>" />
											<?php echo $asset_no; ?>
										</td>
										<td width="300px"><?php echo $asset_name; ?></td>
										<td width="100px" class="align-right"><?php echo $acq_date; ?></td>
										<td width="120px" class="align-left">
											<input type="hidden" name="hddBA" class="hddBA" value="<?php echo $tbl_layer_code; ?>" />
											<?php echo $tbl_layer_code; ?></td>
										<td idth="120px" class="align-left"><?php echo $name_jp; ?></td>
										<td idth="120px" class="align-left"><?php echo $sec_key_name; ?></td>
										<td idth="120px" class="align-right"><?php echo number_format($amount); ?></td>
										<!-- Begin edit for status BCMM Sandi  -->
										<td width="120px" class="align-left"><a href="#" data-target="#myModal" data-toggle="modal" data-backdrop="static" data-keyboard="false" class="link status-style"><?php echo $status; ?></a></td>
										<!-- End edit for status BCMM Sandi   -->
										<td width="120px" class="align-right"><?php echo $quantity; ?></td>
										<td width="120px" class="align-left"><?php echo $place_name; ?></td>
										<td width="90px" class="align-center">
											 <input type="checkbox" name="physical_chk"  class="physical_chk" <?php echo $phy_status;
                                echo " ";
                                echo $disable_inputs;
                                echo " ";
                                echo $disable_requested; ?>>	
										</td>
										<td width="200px" class="align-left">
											 <textarea name="asset_cmt" rows="4" class="asset_cmt  form-control" <?php echo $disable_inputs;
                                echo " ";
                                echo $disable_requested; ?>><?php echo $comment; ?></textarea>
										</td>
										<td width="100px" class="align-right"><?php echo $label_no; ?></td>
										<td width="90px" class="align-center">
											 <input type="checkbox" name="label_chk"  class="label_chk" <?php echo $lbl_status;
                                echo " ";
                                echo $disable_inputs;
                                echo " ";
                                echo $disable_requested; ?>>
										</td>
										<td width="200px" class="align-left">
											 <textarea name="asset_remark" rows="4" class="asset_remark form-control" <?php echo $disable_remark;
                                echo " ";
                                echo $disable_inputs;
                                echo " ";
                                echo $disable_requested; ?>><?php echo $remark; ?></textarea>
										</td>
										
										<?php if ($user_level == 1) { ?>
											<?php if ($asset_flag != 4) { ?>
												<td class="align-center" width='80'>
													<div class='btn-delete' title="<?php echo __("削除"); ?>"><span class="glyphicon glyphicon-trash"></span></div>
												</td>
											<?php } ?>
										<?php } ?>
									</tr>
						<?php
                            }
                        ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	
	<br/>
	<?php if ($total_pages > 1) { ?>
		<div class="row">
			<div class="col-sm-12" style="padding: 10px;text-align: center;">
				<div class="paging">
					<?php
                        echo $this->Paginator->first('<<');
                        echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev page disabled '));
                        echo $this->Paginator->numbers(array('separator'=>'', 'modulus'=>6,'currentTag' => 'a', 'currentClass' => 'active'));
                        echo $this->Paginator->next(' >', array(), null, array('class' => 'next page disabled'));
                        echo $this->Paginator->last('>>');
                    ?>
				</div>
			</div>
		</div>
	<?php } ?>
	
<?php } else { ?>
	<div class="row">
		<div class="col-sm-12">
			<p class="no-data"><?php echo $no_data; ?></p>
		</div>
	</div>
<?php } ?>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg">
		<!--Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title"><?php echo __("固定資産詳細リスト"); ?></h3>
			</div>          
			<div class="modal-body">
				<div class="table-responsive modal_tbl_wrapper">
					<table class="table table-bordered tbl_data_list" id="tbl_data_detail_modal" style="text-align: center;">
						<!--Need Paginate count-->
						<thead style="background-color: #d5eadd;">
							<th width="100px" class="mdl_thead_style"><?php echo __("資産番号"); ?></th>
							<th width="150px" class="mdl_thead_style"><?php echo __("資産名称"); ?></th>
							<th width="100px" class="mdl_thead_style"><?php echo __("第1キーコード");echo "<br/>"; echo "(".__("部署コード").")"; ?></th>
							<th width="150px" class="mdl_thead_style"><?php echo __("第1キー名称"); echo "<br/>"; echo "(".__("部署名").")"; ?></th>
							<th width="100px" class="mdl_thead_style"><?php echo __("数量"); ?></th>
							<th width="100px" class="mdl_thead_style"><?php echo __("差異数量"); ?></th>
							<th width="100px" class="mdl_thead_style"><?php echo __("資産状態区分"); ?></th>
							<th width="100px" class="mdl_thead_style"><?php echo __("イベント名"); ?></th>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div> 

<!-- End edit for status BCMM Sandi -->
<br/><br/><br/>
<!-- for pdf model -->

<div class="modal fade" id="myPDFModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg">
		<!--Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title"><?php echo __("ダウンロード制限を選択してください"); ?></h3>
			</div> 
			       
			<div class="modal-body">
				<div class="table-responsive">
					<div class="col-sm-12">
						<div class="errorPdf" id="errorPdf"></div>
					</div> 
					<br/>
					<?php
                        $totalPage = ceil($total_rows / 20);
                    ?>
					<form name="asset_action_form1" id="asset_action_form1" method="post">
						<div class="col-md-6">
							<div class="col-md-4">
								<label for="pdfLmtFrom" class="col-form-label" style="font-size: 13px !important;">
									<?php echo __("From"); ?>
								</label>
								
							</div>
							<div class="col-md-8">
								<select class="form-control" id="pdfFrom" name="pdfFrom">
									<option value="" ><?php echo "--Select--"; ?></option>
									<?php for ($p = 1; $p <= $totalPage; $p++) { ?>
										<option value="<?php echo $p; ?>" ><?php echo "$p"; ?></option>
									<?php } ?>     	
				            	</select>	
							</div>
							
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<label for="pdfLmtTo" class="col-form-label" style="font-size: 13px !important;">
									<?php echo __("To"); ?>
								</label>
							</div>
							<div class="col-md-8">
								<select class="form-control" id="pdfTo" name="pdfTo">
									<option value="" ><?php echo "--Select--"; ?></option>
									<?php for ($p = 1; $p <= $totalPage; $p++) { ?>
										<option value="<?php echo $p; ?>" ><?php echo "$p"; ?></option>
									<?php } ?>     	
				            	</select>	
							</div>
							
						</div>	
						<br>
						<br>
						<br>
						<div class="col-md-12" id = "modelPrint">		
					   		<input type="button" name="btn_pdf" id="btn_pdf" class="btn btn-success center" value="<?php echo __("Print") ?>">
					    </div>
					    <input type="hidden" id="hddTotalRow1" name="hddTotalRow1" class="hddTotalRow1" value=""  />
					    <input type="hidden" id="hddImage1" name="hddImage1" class="hddImage1" value=""  />
					    <br>
						<br>
						<br>
					</form>
				</div>
			</div>
		</div>
	</div>
</div> 


<script>
	
	$(document).ready(function() {

		if ($('table').length > 0) {
            $('table').floatThead({
                position: 'absolute'
            });
        }

        if ($(".tbl-wrapper").length) {
            $(".tbl-wrapper").floatingScroll();
        }

		//fixed column and header
	    // if($('.tbl_data_list').length > 0) {
	    //    // check data is at least 1 row/column keep
	    //     $('.tbl-wrapper').freezeTable({ 
	    //         'namespace' : 'tbl-freeze-table',
	    //         'columnNum' : 1,
	    //         'columnKeep': true,
	    //         'freezeHead': true,   
	    //         'scrollBar' : true,
	    //     });

	    //     setTimeout(function(){
	    //         $('.tbl-wrapper').freezeTable('resize');
	    //     }, 1000);
	        
	    // }
	   
	      /* floating scroll */
	    if($(".tbl-wrapper").length) {
	        $(".tbl-wrapper").floatingScroll();
	    }
		
		arrStatus=[];
		
		$(".btn_search").click(function() {
			
			if(document.getElementById("new_chk").checked)
			{
				arrStatus.push(1);
			}
			else
			{
				for( var i = 0; i < arrStatus.length; i++){ 
	   				if ( arrStatus[i] === 1) {
		     			arrStatus.splice(i, 1); 
		     			i--;
	   				}
				}
			}
			if(document.getElementById("already_chk").checked)
			{
				arrStatus.push(2);
			}
			else
			{
				for( var i = 0; i < arrStatus.length; i++){ 
	   				if ( arrStatus[i] === 2) {
		     			arrStatus.splice(i, 1); 
		     			i--;
	   				}
				}
			}
			if(document.getElementById("move_chk").checked)
			{
				arrStatus.push(3);
			}
			else
			{
				for( var i = 0; i < arrStatus.length; i++){ 
	   				if ( arrStatus[i] === 3) {
		     			arrStatus.splice(i, 1); 
		     			i--;
	   				}
				}
			}
			if(document.getElementById("lost_chk").checked)
			{
				arrStatus.push(4);
			}
			else
			{
				for( var i = 0; i < arrStatus.length; i++){ 
	   				if ( arrStatus[i] === 4) {
		     			arrStatus.splice(i, 1); 
		     			i--;
	   				}
				}
			}
			if(document.getElementById("sold_chk").checked)
			{
				arrStatus.push(5);
			}
			else
			{
				for( var i = 0; i < arrStatus.length; i++){ 
	   				if ( arrStatus[i] === 5) {
		     			arrStatus.splice(i, 1); 
		     			i--;
	   				}
				}
			}
			document.getElementById("hdStatus").value= arrStatus.toString();
			loadingPic();
			
			}
		);
		function loadingPic() { 
			$("#overlay").show();
			$('.jconfirm').hide();  
		}
		
		/* save */
		$("#btn_save").click(function() {
			// var url = $(this).attr('href');
			var url = window.location.href;
			if(url == undefined) {
				url = '';
			}
			var result = prepareToSave();
			var status = result[0];
			var data = result[1];
			var chkState = result[2];
			
			$("#dataArr").val(JSON.stringify(data));
			$("#path").val(url);
			if(status) {
				$.confirm({
					title: "<?php echo __('保存確認'); ?>",
					icon: 'fas fa-exclamation-circle',
					type: 'blue',
					typeAnimated: true,
					closeIcon: true,
					columnClass: 'medium',
					animateFromElement: true,
					animation: 'top',
					draggable: false,
					content: errMsg(commonMsg.JSE009),
					buttons: {   
						ok: {
							text: "<?php echo __('はい');?>",
							btnClass: 'btn-info',
							action: function(){
								getMail('Save', url, chkState);
							}
						},     
						cancel : {
							text: '<?php echo __("いいえ");?>',
							btnClass: 'btn-default',
							cancel: function(){}
						}
					},
					theme: 'material',
					animation: 'rotateYR',
					closeAnimation: 'rotateXR'
				});
			} else {
				var message="";
				
				$("#error").empty();
				$("#success").empty();
				$("#error").append(message);
				window.scroll({
				  top: 0,
				  behavior: 'smooth',
				});
			}
		});
		
		function prepareToSave() {
			var asset_id = '';
			var physical = '';
			var label = '';
			var remark = '';
			var comment = '';
			var data = [];
			var canSave = true;//no error
			var phyStatus=0;
			var lblStatus=0;
			var chkState = false;
			
			//$(".tbl-w rapper > .tbl_data_list tbody tr").each(function() {
			$(".tbl_data_list tbody tr").each(function() {
				var elemPhysical = $(this).find('.physical_chk');
				var elemLabel = $(this).find('.label_chk');
				
				asset_id = $.trim($(this).find('.asset_id').val());
				
				//if uncheck at phycial check box and empty remark, show error
				if(elemPhysical.is(":disabled")==false && elemLabel.is(":disabled")==false) {
					
					if(elemPhysical.is(":checked")) {
						physical = 1;
					} else{
						physical = 2;
					}
					if(elemLabel.is(":checked")) {
						label = 1;
					} else {
						label = 2;
					}
					var elemRemark = $(this).find('.asset_remark');
					
					if(elemRemark.is(":disabled") == false) {
						remark = $.trim(elemRemark.val());
					} else {
						remark = '';
					}
					comment = $.trim($(this).find('.asset_cmt').val());
					
					if (comment == "" && physical == 2) {
		                phyStatus = 1;
		            }
		            if (remark == "" && label == 2) {
		                lblStatus = 1;
		            }

					data.push({
						'asset_id':asset_id,
						'physical_check':physical,
						'label_check':label,
						'remark':remark,
						'comment':comment
					});
				}
			});
			var len = data.length;
			if(len == 0 && canSave == true) {
				canSave = false;
			}
			
			var checkFACnt = <?php echo json_encode($checkFARowcnt); ?>;
			if(phyStatus == 0 && lblStatus == 0 && (len == checkFACnt || checkFACnt == 0)) {
				chkState = true;

			}
			// return [canSave, data,phyORLbl1,phyORLbl2];
			return [canSave, data, chkState];
		}
		

		/* Approve */
		$("#btn_approve").click(function(e) {
			e.preventDefault();
			var chkState = "<?php echo $checkState['canApp']; ?>";
			var url = '';
		
			$.confirm({
				title: "<?php echo __('承認確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'blue',
				typeAnimated: true,
				closeIcon: true,
				columnClass: 'medium',
				animateFromElement: true,
				animation: 'top',
				draggable: false,  
				content: "<?php echo __("全行を承認してよろしいですか。") ?>",
				buttons: {
			        ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
			          	action:function(){
			      			getMail('Approve',url,chkState);
			          	}
					},
					cancel: {
				       	text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
				       	action: function(){}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		});
		
		function getMail(func, path = '', chkState = '') {
			var layer_code = $("#layer_code").val();
			var page = 'Assets';
			var language = <?php echo json_encode($language); ?>;
			
			func = func.replace(" ", "");
			var form_action = (func == 'approve_cancel') ? "approveCancelAsset" : func+"Asset";
			
			if(chkState) {
				$.ajax({
					type:'post',
					url: "<?php echo $this->webroot; ?>Assets/getMailLists",
					data:{layer_code : layer_code, page: page, function: func, language: language},
					dataType: 'json',
					success: function(data) {
						var mailSend = (data.mailSend == '') ? '0' : data.mailSend;
						$("#mailSend").val(mailSend);
						if(mailSend == 1) {	
							$("#mailSubj").val(data.subject);
							$("#mailBody").val(data.body);
							
							if(data.mailType == 1) {
								//default
								if(data.to != undefined) var toEmail = Object.values(data.to);
					        	if(data.cc != undefined) var ccEmail = Object.values(data.cc);
					        	if(data.bcc != undefined) var bccEmail = Object.values(data.bcc);
								
								$('#toEmail').val(toEmail);
		            			$('#ccEmail').val(ccEmail);
								$('#bccEmail').val(bccEmail);
								loadingPic();
		            			document.forms[0].action = "<?php echo $this->webroot; ?>Assets/"+form_action;
								document.forms[0].method = "POST";
								document.forms[0].submit();
							}else {
								//popup
								$("#myPOPModal").addClass("in");
					        	$("#myPOPModal").css({"display":"block","padding-right":"17px"});
					        	
					        	if(data.to != undefined) $('.autoCplTo').show();
					        	if(data.cc != undefined) $('.autoCplCc').show();
					        	if(data.bcc != undefined) $('.autoCplBcc').show();

								if(data.to != undefined) level_id = Object.keys(data.to);
								if(data.cc != undefined) cc_level_id = Object.keys(data.cc);
								if(data.bcc != undefined) bcc_level_id = Object.keys(data.bcc);
								
								$(".subject").text(data.subject);
								$(".body").html(data.body);

								$('#data_search_form').attr('method','post');
								$('#data_search_form').attr('action', "<?php echo $this->webroot; ?>Assets/"+form_action);
							}
						}else {
							loadingPic();
							document.forms[0].action = "<?php echo $this->webroot; ?>Assets/"+form_action;
							document.forms[0].method = "POST";
							document.forms[0].submit();
						}

					},
					error: function(e) {
						console.log('Something wrong! Please refresh the page.');
					}
				});
			}else {
				loadingPic();
				document.forms[0].action = "<?php echo $this->webroot; ?>Assets/"+form_action;
				document.forms[0].method = "POST";
				document.forms[0].submit();
			}		
		}
		/* Approve */
		$("#btn_approve_cancel").click(function(e) {
			e.preventDefault();
			var chkState = "<?php echo $checkState['canCancel']; ?>";
			var url = '';
			$.confirm({
				title: "<?php echo __('承認キャンセル確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'blue',
				typeAnimated: true,
				closeIcon: true,
				columnClass: 'medium',
				animateFromElement: true,
				animation: 'top',
				draggable: false,  
				content: "<?php echo __("全行を承認キャンセルしてよろしいですか。") ?>",
				buttons: {
			        ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
			          	action:function(){						
			          		getMail('approve_cancel',url,chkState);
			          	}
					},
					cancel: {
				       	text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
				       	action: function(){}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		});

		/* Reject */ 
		$("#btn_reject").click(function(e) {
			e.preventDefault();
			var chkState = "<?php echo $checkState['canRej']; ?>";
			var url = '';
			
			$.confirm({
				title: "<?php echo __('拒否を確認'); ?>",
				icon: 'fas fa-exclamation-circle',
				type: 'blue',
				typeAnimated: true,
				closeIcon: true,
				columnClass: 'medium',
				animateFromElement: true,
				animation: 'top',
				draggable: false,  
				content: "<?php echo __("すべてのデータを拒否してもよろしいですか？") ?>",
				buttons: {
			        ok: {
						text: "<?php echo __('はい'); ?>",
						btnClass: 'btn-info',
			          	action:function(){
							getMail('Reject',url,chkState);
			          	}
					},
					cancel: {
				       	text: "<?php echo __('いいえ'); ?>",
						btnClass: 'btn-default',
				       	action: function(){}
					}
				},
				theme: 'material',
				animation: 'rotateYR',
				closeAnimation: 'rotateXR'
			});
		});

		/* When printing PDF, show loading animation */
		$("#btn_pdf").click(function() {
			$("#error").empty();
			$("#errorPdf").empty();
			loadingPic(); 

			setCookie('downloadStarted', 0, 100); //set cookie to 0 when start
			setTimeout(checkDownloadCookie, 1000); //Initiate the loop to check the cookie.
			
			var total_rows = <?php echo "$total_rows"; ?>;
			console.log(total_rows);
			if(total_rows <= 20){
				document.getElementById("hddTotalRow").value = total_rows;
				document.getElementById("hddImage").value = document.getElementById("picture_check").value;
				$('#asset_action_form').attr('action', "<?php echo $this->Html->url(array('controller'=>'Assets','action'=>'dataListPdf')) ?>").submit();
			}
			else{
				
				var from,to;
				from=$("#pdfFrom" ).val();
				to=$("#pdfTo" ).val();
				
				if(from!=""){
					
				 	from = parseInt($( "#pdfFrom" ).val());
				}
				else{
					
				  	from = $("#pdfFrom" ).val();
				}

				if(to!=""){
					
				 	to =  parseInt($( "#pdfTo" ).val());
				}
				else{
					
					to =  $( "#pdfTo" ).val();
				}
				
				var errorFlag = true;
				document.getElementById("hddTotalRow1").value = total_rows;
				document.getElementById("hddImage1").value = document.getElementById("picture_check").value;
				if(!checkNullOrBlank(from) && !checkNullOrBlank(to))
				{
					document.getElementById("errorPdf").innerHTML   = "";
			        var newbr = document.createElement("div");                      
			        var a     = document.getElementById("errorPdf").appendChild(newbr);
			        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("From"); ?>'])));
			        a.style.color = "red";
			        a.style.textAlign = "left";
			        var newbr1 = document.createElement("div"); 
			        var b     = document.getElementById("errorPdf").appendChild(newbr1);
			        b.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("To"); ?>'])));
			        b.style.color = "red";
			        b.style.textAlign = "left";

			        document.getElementById("errorPdf").appendChild(a); 
			        document.getElementById("errorPdf").appendChild(b);                      
			        errorFlag = false; 
			        $("#overlay").hide(); 
				}
				else if(!checkNullOrBlank(from)) {
					document.getElementById("errorPdf").innerHTML   = "";
			        var newbr = document.createElement("div");                      
			        var a     = document.getElementById("errorPdf").appendChild(newbr);
			        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("From"); ?>'])));
			        a.style.color = "red";
			        a.style.textAlign = "left";

			        document.getElementById("errorPdf").appendChild(a);                      
			        errorFlag = false; 
			        $("#overlay").hide();                     
			    }
			    else if(!checkNullOrBlank(to)) {
			    	document.getElementById("errorPdf").innerHTML   = "";
			        var newbr = document.createElement("div");                      
			        var a     = document.getElementById("errorPdf").appendChild(newbr);
			        a.style.color = "red";
			        a.style.textAlign = "left";
			        a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001,['<?php echo __("To"); ?>'])));
			       
			        document.getElementById("errorPdf").appendChild(a); 

			        errorFlag = false;  
			        $("#overlay").hide();                    
			    }

			   else
			    {
			   
			   	 if(from > to){
			   	 	
			    	document.getElementById("errorPdf").innerHTML   = "";
			    	var newbr = document.createElement("div");	
					var a = document.getElementById("errorPdf").appendChild(newbr);	
					a.appendChild(document.createTextNode(errMsg(commonMsg.JSE041)));	
					document.getElementById("errorPdf").appendChild(a);
					a.style.color = "red";
			        a.style.textAlign = "left";
					errorFlag = false; 
					$("#overlay").hide();  
			    	
			    }		    
			    else{
			    	if((to - from + 1) > 8){
						document.getElementById("errorPdf").innerHTML   = "";
				    	var newbr = document.createElement("div");                      
				        var a     = document.getElementById("errorPdf").appendChild(newbr);
				        a.appendChild(document.createTextNode((commonMsg.JSE042)));
				        a.style.color = "red";
				        a.style.textAlign = "left";
				        document.getElementById("errorPdf").appendChild(a);     
				    	errorFlag = false; 
				    	$("#overlay").hide();     
			    	
			    	}  
			    }
			    
			   }

			    if(errorFlag){
			    	$('#asset_action_form1').attr('action', "<?php echo $this->Html->url(array('controller'=>'Assets','action'=>'dataListPdf')) ?>").submit();
			    }
				
			}
			 
		});

		var downloadTimeout;
		var checkDownloadCookie = function() {
			if (getCookie("downloadStarted") == 1) {
				// if download complete reset cookie
				setCookie("downloadStarted", "false", 100);
				$('#overlay').hide();
				 $('#myPDFModal').modal('hide');

			} else {
				//Re-run this function in 1 second.
				downloadTimeout = setTimeout(checkDownloadCookie, 1000); 
			}
		};
		function setCookie(cname, cvalue, exp) {
			var d = new Date();
			d.setTime(d.getTime() + (exp * 1000));
			var expires = "expires="+d.toUTCString();
			document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
		}
		function getCookie(cname) {
			var name = cname + "=";
			var ca = document.cookie.split(';');
			for(var i = 0; i < ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == ' ') {
					c = c.substring(1);
				}
				if (c.indexOf(name) == 0) {
					return c.substring(name.length, c.length);
				}
			}
			return "";
		}

		/* Download Excel */
		$("#btn_excel_download").click(function() {
			$("#error").empty();
			//loadingPic(); 
			
			$.ajax({
				type:'POST',
				url:"<?php echo $this->Html->url(array('controller'=>'Assets','action'=>'uncheckDataExcelDownload')) ?>",
				data: {},
				dataType:'json'
			}).done(function(data){
				if(data.status == true) {
					var today = new Date().toJSON().slice(0,10).replace(/-/g,'_');
					var file_name = "AssetLabelUncheckManagementList_"+today+".xlsx";

					$("#overlay").hide();
					// IE10+ : (has Blob, but not a[download] or URL)
					if (navigator.msSaveBlob) { 
						var dataBlob = dataUriToBlob(data.file);
						return navigator.msSaveBlob(dataBlob, file_name);
					} else {
						// for Chrome, Firefox 
						var $a = $("<a>");
						$a.attr("href",data.file);
						$("body").append($a);
						$a.attr("download",file_name);
						$a[0].click();
						$a.remove();
					}
				} else {
					$.confirm({
						title: "<?php echo __('警告メッセージ'); ?>",
						icon: 'fas fa-exclamation-circle',
						type: 'orange',
						typeAnimated: true,
						closeIcon: true,
						columnClass: 'medium',
						animateFromElement: true,
						animation: 'top',
						draggable: false,  
						content: data.msg,
						buttons: {
							ok: {
								text: "<?php echo __('はい'); ?>",
								btnClass: 'btn-info',
								action:function(){
									$("#overlay").hide();
								}
							}
						},
						theme: 'material',
						animation: 'rotateYR',
						closeAnimation: 'rotateXR',
						closeIcon: function() {
							$("#overlay").hide();
						}
					});
				}
			});
		});
		// prepare blob object to download data in IE10+
		function dataUriToBlob(dataUri) {
			if (!(/base64/).test(dataUri))
				throw new Error("Supports only base64 encoding.");
			var parts = dataUri.split(/[:;,]/),
				type = parts[1],
				binData = atob(parts.pop()),
				mx = binData.length,
				uiArr = new Uint8Array(mx);
			for(var i = 0; i<mx; ++i)
				uiArr[i] = binData.charCodeAt(i);
			return new Blob([uiArr], {type: type});
		}

		/* delete row */
		$(".btn-delete").click(function(e) {
			var isOkClicked = false; //prevent doublic click on ok button
			var asset_id = $(this).closest('tr').find('.asset_id').val();
			var del_layer_code = $(this).closest('tr').find('.hddBA').val();
			var del_asset_no = $(this).closest('tr').find('.hddAsset_number').val();
			loadingPic(); 

			$.confirm({
	            title: '<?php echo __("削除確認");?>',
	            icon: 'fas fa-exclamation-circle',
	            type: 'red',
	            typeAnimated: true,
	            closeIcon: true,
				columnClass: 'medium',
	            animateFromElement: true,
	            animation: 'top',
	            draggable: false,
	            content: errMsg(commonMsg.JSE017),
	            buttons: {   
	                ok: {
	                	text: "<?php echo __('はい');?>",
			          	btnClass: 'btn-info',
			            action: function(){
							//prevent double click on ok button
							if(isOkClicked == false) { 
								isOkClicked = true;
								$.ajax({
									url: "<?php echo $this->Html->url(array('controller'=> 'Assets','action' => 'deleteAsset')); ?>",
									type: 'post',
									data : {asset_id: asset_id,hddAsset_number:del_asset_no,hddBA:del_layer_code},
									success: function(data) {									
										window.location.href = data;
									},
									error: function(e) {
										console.log('fail');
									}
								});
							}
						}
			    	},     
	                cancel : {
	                    text: '<?php echo __("いいえ");?>',
	                    btnClass: 'btn-default',
	                    action: function(){
							$("#overlay").hide();
	                    }
	                }
	            },
	            theme: 'material',
	            animation: 'rotateYR',
				closeAnimation: 'rotateXR',
				closeIcon: function() {
					$("#overlay").hide();
				}
	        });
		});
		
		function toggleCheck(status) {
			var physicalAllCheck = true;
			var labelAllCheck = true;
			var isAllDisable = true;
			//$(".tbl-wrapper > .tbl_data_list tbody tr").each(function() {
			$(".tbl_data_list tbody tr").each(function() {
				var physical = $(this).find('.physical_chk');
				
				var label = $(this).find('.label_chk');
				if(status == 'user-click') {
					if(physical.is(":disabled") == false) {
						if(physical.is(":checked") == false) {
							physicalAllCheck = false;
						}
					}
					if(physical.is(":disabled") == false) {
						if(label.is(":checked") == false) {
							labelAllCheck = false;
						}
					}
				} else if (status == 'form-load') {
					if(physical.is(":checked") == false) {
						physicalAllCheck = false;
					}
					if(label.is(":checked") == false) {
						labelAllCheck = false;
					}
					if(physical.is(":disabled") == false) {
						isAllDisable = false;
					}
				}
			});
			
			if(physicalAllCheck) {
				$(".chk_physical_master").prop('checked',true);
			} else {
				$(".chk_physical_master").prop('checked',false);
			}
			if(labelAllCheck) {
				$(".chk_label_master").prop('checked',true);
			} else {
				$(".chk_label_master").prop('checked',false);
			}
		
			if(status == 'form-load') {	
				if(isAllDisable) {
					$(".chk_label_master").prop('disabled',true);
					$(".chk_physical_master").prop('disabled',true);
				} else {
					$(".chk_label_master").prop('disabled',false);
					$(".chk_physical_master").prop('disabled',false);
				}
			}
		}
		toggleCheck('form-load');

		/* check all physical check column */
		$(".chk_physical_master").click(function() {
			if($(this).is(":checked") == true) {
				$(".chk_physical_master").prop('checked', true);
			} else {
				$(".chk_physical_master").prop('checked', false);
			}
			autoCheckAll($(this), 'physical_chk');
		});
		/* check all label check column */
		$(".chk_label_master").click(function() {
			if($(this).is(":checked") == true) {
				$(".chk_label_master").prop('checked', true);
			} else {
				$(".chk_label_master").prop('checked', false);
			}
			autoCheckAll($(this), 'label_chk');
		});
		function autoCheckAll(clickObj, className) {
			if(clickObj.is(":checked")) {
				$("."+className).not(":disabled").prop('checked', true);
				//disable remark when label check box is checked
				if(className == 'label_chk') {
					
					$('.asset_remark').not(":disabled").prop('disabled', true);
				}
				else if(className == 'physical_chk') {
					
					$('.asset_cmt').not(":disabled").prop('disabled', false);
				}
			} else {
				$("."+className).not(":disabled").prop('checked', false);
				//enable remark when label check box is checked
				if(className == 'label_chk') $("."+className).not(":disabled").closest('tr').find('.asset_remark').prop('disabled', false);
				if(className == 'physical_chk') $("."+className).not(":disabled").closest('tr').find('.asset_cmt').prop('disabled', false);
			}
		}

		/* check/uncheck to physical master checkbox */
		$(".physical_chk").click(function() {
			if($(this).is(":checked") == false) {
				$(".chk_physical_master").prop('checked', false);
				$(this).closest('tr').find('.asset_cmt').prop('disabled', false);//disable remark
			} else {
				toggleCheck('user-click');
				$(this).closest('tr').find('.asset_cmt').prop('disabled', false);//enable remark
			}
		});
		/* check/uncheck to label master checkbox */
		$(".label_chk").click(function() {
			if($(this).is(":checked") == false) {
				$(".chk_label_master").prop('checked', false);
				$(this).closest('tr').find('.asset_remark').prop('disabled', false);//disable remark
			} else {
				toggleCheck('user-click');
				
				$(this).closest('tr').find('.asset_remark').prop('disabled', true);//enable remark
			}
		});

		/* Begin edit for status BCMM Sandi */

		/* Open modal box to show Fixed Assets Data List */
		
		$(".link").on("click",function(){
			var asset_no = $.trim($(this).closest('tr').find('td:eq(1)').text());
			$.ajax({
				type: 'post',
				url: "<?php echo $this->Html->url(array('controller'=>'Assets', 'action'=>'dataList')); ?>",data : {asset_no : asset_no} ,
				dataType: 'json',
				success: function(data){
					var datacontent = data.content;
					$('#tbl_data_detail_modal').find("tbody tr").remove();
					var sizeOfRow=data.content.length+data.content1.length;
					var asset_no =datacontent[0].asset_no;
					var asset_name =datacontent[0].asset_name;
					var layer_code =datacontent[0].layer_code;
					var st="<tr><td style='vertical-align: middle;' rowspan="+sizeOfRow+' width="150px">'+asset_no+'</td>'+"<td width='250px' style='vertical-align: middle;' rowspan="+sizeOfRow+'>'+asset_name+'</td>';
					var fc="";
					if(!(data.content1=="")) 
					{
						fc='color="#3498DB"';
					}

					for(var i=0;i<datacontent.length;i++) {
						asset_no =datacontent[i].asset_no;
						asset_name =datacontent[i].asset_name;
						layer_code =datacontent[i].layer_code;
						var name_jp =datacontent[i].layer_name;
						var event_name =datacontent[i].event_name;
						var diff_qty=datacontent[i].diff_qty;
						var qty=datacontent[i].quantity;
						var assetstatus=datacontent[i].assetstatus;
						var status = "";
						if(assetstatus == 2) {
							status = ('<?php echo __("除却済"); ?>');
						} else if(assetstatus == 3) {
							status = ('<?php echo __("売却済"); ?>');
						} 
						
						if(i==0)
						st+='<td width="100px"><font '+fc+'>'+layer_code+'</font></td>'+'<td width="150px"><font '+fc+'>'+name_jp+'</font></td>';
						else
						st='<tr><td width="100px"><font '+fc+'>'+layer_code+'</font></td>'+'<td width="150px"><font '+fc+'>'+name_jp+'</font></td>';
						if(!(data.content1=="")) 
						{
							st+='<td width="100px"><font '+fc+'>'+qty+'</font></td>'+'<td width="100px"><font '+fc+'>'+diff_qty+'</font></td>'+'<td width="100px"><font '+fc+'>'+status+'</font></td>'+'<td width="100px"><font '+fc+'>'+event_name+'</font></td></tr>';
						}
						else
						{
							st+='<td width="100px"><font '+fc+'>'+qty+'</font></td>'+'<td width="100px"><font '+fc+'>'+''+'</font></td>'+'<td width="100px"><font '+fc+'>'+''+'</font></td>'+'<td width="100px"><font '+fc+'>'+event_name+'</font></td></tr>';
						}
						$('#tbl_data_detail_modal').append(st);
					}
					
					var datacontent1 = data.content1;
					if(!(datacontent1=="")) {
						for(var i=0;i<datacontent1.length;i++) {
							var asset_no1 =datacontent1[i].asset_no;
							var asset_name1 =datacontent1[i].asset_name;
							var layer_code1 =datacontent1[i].layer_code;
							var name_jp1 =datacontent1[i].name_jp;
							var event_name1 =datacontent1[i].event_name;
							/* add in popupmodel quality*/
							var qty1=datacontent1[i].quantity;
							st='<tr><td width="100px">'+layer_code1+'</td>'+'<td width="150px">'+name_jp1+'</td>';
							st+='<td width="100px">'+qty1+'<td width="100px">'+" "+'</td>'+'<td width="100px">'+" "+'</td>'+'<td width="100px">'+event_name1+'</td></tr>';
							$('#tbl_data_detail_modal').append(st);
						}
					}
				},
				error: function(res){
					console.log("Error in somewhere");
				}
			});
		});

		/* Make header float inside modal box */
		$("#myModal").on('shown.bs.modal', function(){
			var $table = $("#tbl_data_detail_modal");
			$table.floatThead({
				position: 'absolute',
				scrollContainer: true
			});
			$table.trigger('reflow');//re-align table header
		});
		/* To realign float header, while modal box open and resize windows */
		$(window).resize(function() {
			var $table = $("#tbl_data_detail_modal");
			$table.trigger('reflow');
		});
		/*Remove table body when close modal box */
		$("#myModal").on('hidden.bs.modal', function(){
			$("#tbl_data_detail_modal").find('tbody').text('');
		});
		$("#myPDFModal").on('hidden.bs.modal', function(){
			$("#errorPdf").text('');
			$("#pdfFrom").prop('selectedIndex', 0);
			$("#pdfTo").prop('selectedIndex', 0);
			
		});
		/* End edit for status BCMM Sandi */

	});
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
			$("#overlay").show();
		}
		
	} 
</script>
