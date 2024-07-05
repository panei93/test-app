
<?php
    echo $this->Html->css('LaborCostDetail/style.css');
    $budget_year = $_SESSION['BudgetTargetYear'];
    $TARGET_YEAR = empty($this->Session->read('SEARCH_LABOR_COST.target_year'))? $budget_year : $this->Session->read('SEARCH_LABOR_COST.target_year');
    $GROUP_CODE = empty($this->Session->read('SEARCH_LABOR_COST.layer_code'))? $this->Session->read('SELECTED_GROUP') : $this->Session->read('SEARCH_LABOR_COST.layer_code');
    $POSITION_CONSTANT = array_keys(PositionType::PositionConstant);
    // debug($tableOne);
?>

<div class="content register_container" style="font-size: 1em !important;">
<?php if(!empty($comment) && strlen(trim($comment['LcComment']['comment'])) > 0) : ?>
        <div class="noti-msg d-flex flex-column justify-content-between align-items-center">
            <div class="comment-message"><?php echo $comment['LcComment']['comment']; ?></div>
            <div style="margin:0.5rem 0 0 0;text-align:end">
                <?php 
                    $translatedComment = __("<b>{$comment['users']['user_name']}</b>"); 
                    $translatedDate = __("{$comment['LcComment']['updated_date']}");
                    echo __("%s が %s にコメントしました。", $translatedComment, $translatedDate);
                ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
		<div class="col-md-8 col-sm-8">				
			<h3>
                <?php echo __("ビジネス別人員表"); ?>		
			</h3>
		</div>
		<div class="col-md-4 col-sm-4">				
            <span  style="display: flex; float:right;"
				class="glyphicon glyphicon-info-sign" 
				data-container="body" 
				data-toggle="popover" 
				data-placement="left" 
				data-content="
                    <div id='info-text'>
                        <p>【入力事項】</p>
                        <ol>
                            <li>案件毎の人員配置を明確化（人員数を入力）※小数点以下第5位まで入力可能</li>
                            <li>案件のうち、上段が既存取引、下段が新規取引</li>
                            <li>経営指導料は案件毎に直接入力</li>
                            <li>過去分については実績と合致させる。差異は異動調整箇所にて調整。</li>
                            <li>行を挿入した場合は、中計括りのビジネス毎の合計（既存・新規・人件費・割当経費）にも加算すること。</li>
                        </ol>
                    </div>
                " 
                data-html='true'
            ></span>
		</div>		
		<div class="col-md-12 budget-form-hr" style="margin-top: -10px;">
			<hr>
		</div>
	</div>
    <!-- Error Area -->
    <div id="successErrorMsg">
        <div><?php echo $this->Flash->render("lcd_success")?></div>
        <div><?php echo $this->Flash->render("lcd_error"); ?></div>
    </div>
    <!-- end Error Area  -->

    <!-- Filter Area -->
    <?=
        $this->Form->create(false, array(
            'id' => 'filter_form',
            'autocomplete' => 'off', 
            // 'enctype' => 'multipart/form-data'
        ));
    ?>
    <?php if(!empty($errormsg)) : ?>
        <div class="col-sm-12">
            <p class="no-data"><?php echo $errormsg; ?></p>
        </div>
    <?php else : ?>
        <div class="form-group row">
            <div class="col-sm-3">
                <input type="text" class="form-control" value="<?php if($this->Session->check('TERM_NAME')) echo $this->Session->read('TERM_NAME');?>" readonly="">
            </div>
            <div class="col-sm-3">
                <select class="form-control" name="target_year" id="target_year">
                    <?php foreach ($years as $value) : ?>
                    <option 
                        value="<?= $value; ?>" 
                        <?= $TARGET_YEAR == $value ? 'selected' : '' ?>
                    >
                        <?= $value; ?> <?php echo __("年度")?>
                    </option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-3">
                <select class="form-control" name="group_code" id="group_code">
                    <?php if(count($groups)<1): ?>
                    <option value=''>---Select---</option>
                    <?php endif; ?>
                    <?php foreach ($groups as $key => $value) : ?>
                    <option 
                        value="<?= $key; ?>" 
                        <?= $GROUP_CODE == $key ? 'selected' : '' ?>
                    >
                        <?= $value; ?>
                    </option>
                    <?php endforeach ?>
                </select>
            </div>
            <input type="hidden" name="users" id="users" />
            <input type="hidden" name="user" id="user" />
            <input type="hidden" name="approved_flag_1" id="approved_flag_1" value="1">
            <div class="col-sm-3">
                <button
                    type="button" 
                    class="btn btn-success btn_sumisho_set"
                    id="filter-btn" 
                >
                    <?php echo __("設定選択") ?>
                </button>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="laborCostDetailsCommentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="closeBtnClick()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title" id="exampleModalCenterTitle">
                    <?php 
                    if(!empty($comment)) :
                        echo __("コメントの編集");
                    else:
                        echo __("コメント追加");
                    endif;?>
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="error-msg"></div>
                    <input type="hidden" name="page_name" id="page_name" value="LaborCostDetails">
                    <textarea class="form-control" id="lcd_comment" name="lcd_comment" rows="10" placeholder="0/500" style="resize: none;" maxlength="500"><?php echo str_replace('<br />', "", $comment['LcComment']['comment']); ?></textarea>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="update_id" name="update_id" value="<?php echo $comment['LcComment']['id']; ?>">
                    <button type="button" class="btn btn-secondary <?php echo $comment ? '' : 'save'; ?>" id="closeButton" data-dismiss="modal" onClick="closeBtnClick()"><?php echo __("閉じる") ?></button>
                    <button type="button" class="btn btn-primary" onClick="onAddCommentHandler()"><?php echo empty($comment) ? __('追加') : __('変更'); ?></button>
                </div>
                </div>
            </div>
        </div>
    <?php echo $this->Form->end(); ?>
    <!-- end filter area  -->
    <input type="hidden" name="confirm_message" id="confirm_message" value="">	
    <!-- Content Area  -->
    <?php if ($errMsg) : ?>
        <!-- if data not found -->
        <div class="col-sm-12">
            <p class="no-data"><?php echo $errMsg['errMsg']; ?></p>
        </div>
    <?php else : ?>
        <!-- if data found -->
        <?=
            $this->Form->create(false, array(
                'autocomplete' => 'off', 
                // 'enctype' => 'multipart/form-data'
            ));
        ?>
            <input type="hidden" name="labor_cost_details_datas" id='labor-costs-input' />
            <input type="hidden" name="labor_cost_adjustments_datas" id='labor-adjustments-input' />
            <input type="hidden" name="budget_amount" id='budget-amount' />
            <input type="hidden" name="labor_cost" id="labor_cost" />
            <input type="hidden" name="approved_flag" id="approved_flag" value="1">
        <?php echo $this->Form->end(); ?>
         <!-- Download and Save Button  -->
        <div class="row" style="text-align:right;">
            <?php
            
            $current_yr = date("Y");
            $budget_year = $_SESSION['BudgetTargetYear'];

            // if($target_year == $current_yr || $target_year == ($current_yr + 1)) $readonly = '';
            // else $readonly = '';
            if($target_year < ($budget_year-1) || $approved_flag == 2 || $completed_flag == 2){
                $readonly = 'disabled';
            }else{
                $readonly = '';
            }
            if($tableOne[0]['none_labor_cost']): ?>
                <div class="col-md-12">
                    <span class="alert alert-danger" role="alert">
                        <?php echo __("予算人員表で役職を登録していないためこの画面の操作ができません。"); ?>
                    </span>

                    <?php if($showCommentBtn) : ?>
                    <?php if(empty($comment)) : ?>
                        <button type="button" class="btn btn-success btn_sumisho_set" data-toggle="modal" data-target="#laborCostDetailsCommentModal" <?php if($disabledCommentBtn)echo "disabled"; ?>>
                            <?php echo __("コメント追加") ?>
                        </button>
                    <?php else : ?>
                        <button type="button" class="btn btn-success btn_sumisho_set" data-toggle="modal" data-target="#laborCostDetailsCommentModal" <?php if($disabledCommentBtn)echo "disabled"; ?>>
                            <?php echo __("コメントの編集") ?>
                        </button>
                    <?php endif; ?>  
                    <?php endif; ?>  
                    
                    <?php if($showReadBtn) : ?>
                    <button type="button" class="btn btn-success btn_sumisho_set" disabled>
                        <?php echo __("一括ダウンロード") ?>
                    </button>
                    <?php endif; ?>

                    <?php if($showSaveBtn) : ?>
                    <button type="button" class="btn btn-success btn_sumisho_set" disabled <?php if($disabledSaveBtn)echo "disabled"; ?>>
                        <?php echo __("一時保存") ?>
                    </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="col-md-12">
                    <?php if($showCommentBtn) : ?>
                    <?php if(empty($comment)) : ?>
                        <button type="button" class="btn btn-success btn_sumisho_set" data-toggle="modal" data-target="#laborCostDetailsCommentModal" <?php if($disabledCommentBtn)echo "disabled"; ?>>
                            <?php echo __("コメント追加") ?>
                        </button>
                    <?php else : ?>
                        <button type="button" class="btn btn-success btn_sumisho_set" data-toggle="modal" data-target="#laborCostDetailsCommentModal" <?php if($disabledCommentBtn)echo "disabled"; ?>>
                            <?php echo __("コメントの編集") ?>
                        </button>
                    <?php endif; ?>  
                    <?php endif; ?> 

                    <?php if($showReadBtn) : ?>
                    <button type="button" class="btn btn-success btn_sumisho_set" onClick="onDownloadHandler()">
                        <?php echo __("一括ダウンロード") ?>
                    </button>
                    <?php endif; ?> 

                    <?php if($showSaveBtn) : ?>
                    <button type="button" class="btn btn-success btn_sumisho_set" onClick="onSaveHandler()" <?php echo $readonly; ?> <?php if($disabledSaveBtn)echo "disabled"; ?>>
                        <?php echo __("一時保存") ?>
                    </button>
                    <?php endif; ?> 
                    <?php 
                    if($target_year < ($budget_year-1) || $completed_flag == 2){	
                        // pr($search_data['target_year']);
                        $disabled_btn = 'disabled';
                    }?>
                    <?php if($approved_flag == 1 && $showConfirmBtn): ?>
                    <button type="button" class="btn btn-success btn_sumisho_set" onClick="onConfirmHandler()" <?php echo $disabled_btn; ?> <?php if($disabledConfirmBtn)echo "disabled"; ?>>
                        <?php echo __("確定") ?>
                    </button>
                    <?php elseif($approved_flag == 2 && $showConfirmCancelBtn): ?>
                    <button type="button" class="btn btn-success btn_sumisho_set" onClick="onConfirmCancelHandler()" <?php echo $disabled_btn; ?> <?php if($disabledConfirmCancelBtn)echo "disabled"; ?>>
                        <?php echo __("確定解除") ?>
                    </button>
                     <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <!--End Download and Save Button  -->
        
        <!-- Table Title -->
        <div class="row text">
            <h4><?php echo __("（千円）"); ?></h4>
        </div>
        <!--End Table Title -->

        <div class="table_container table-responsive tbl-wrapper">
            <!-- Table One (User inputs)  -->
            <table id="table_one" class="bu_analysis">
                <thead>
                    <tr>
                        <th class="check" style="text-align: center;">#</th>
                        <th><?php echo __('氏名'); ?></th>
                        <th><?php echo __('予算人員数'); ?></th>
                        <th class="hidden-column"><?php echo __('予算人員数'); ?></th>
                        <th><?php echo __('人件費単価'); ?></th>
                        <th><?php echo __('ｺｰﾎﾟﾚｰﾄ経費割当単価'); ?></th>
                        <th><?php echo __('ﾋﾞｼﾞﾈｽ別'); ?></th>
                        <?php foreach ($businesses as $business) : ?>
                            <th><?= $business['Layer']['name_jp'] ?></th>
                        <?php endforeach; ?>
                        <th class="x-bold-border top-bold-border">
                            <?php echo __('人員数 合計'); ?>
                        </th>
                        <th><?php echo __('コメント'); ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($tableOne as $key => $laborCost) : 
                        $hr = $laborCost['exist_hours']['total'] + $laborCost['new_hours']['total'];
                        $row_cnt = 'row_'.$key;
                        $person_cnt = 'person_'.$key;
                        $labor_cnt = 'labor_'.$key;
                        $corpo_cnt = 'corpo_'.$key;
                        $emp_cnt = 'emp_'.$key;
                        $tot_cnt = 'total_'.$key;
                        ?>
                        <?php if($key !== 'adjustment'): ?>
                            
                            <?php if($laborCost['user_id'] == 0 && $laborCost['position_code'] != '') {
                                $person_id = $person_cnt;
                                $person_total_id = 'person_total_'.$key;
                                $labor_id = $labor_cnt;
                                $corpo_id = $corpo_cnt;
                                $emp_id = $emp_cnt;
                                $total_new_id = $tot_cnt;
                                $total_exist_id = $tot_cnt;
                                $new_user = $laborCost['user_name'];
                            }else {
                                $person_id = 'b_person_count_'.$laborCost['user_id'];
                                $person_total_id = 'b_person_count_total_'.$laborCost['user_id'];
                                $labor_id = 'unit_labor_cost_'.$laborCost['user_id'];
                                $corpo_id = 'unit_corpo_cost_'.$laborCost['user_id'];
                                $emp_id = 'number_of_emp_'.$laborCost['user_id'];
                                $total_new_id = $laborCost['user_id'];
                                $total_exist_id = $laborCost['user_id'];
                                $new_user = '';
                            } ?>
                        <!-- New Row -->
                        <tr class = "no-tborder" id="<?php echo 'exist_'.$row_cnt; ?>">
                            <!-- row span 2 -->
                            <?php if(!empty($laborCost['user_name'])) { ?>
                            <td class="rowspanned" rowspan="2" style="text-align: center;"><?php echo $key +1; ?></td>
                            <td class="rowspanned text user_name" rowspan="2">
                            <?= $laborCost['user_name'] ?>
                            </td>
                            <?php }else { ?>
                            <!-- khin -->
                            <td class="blank-cell">
                                <input type="button" class="btn_add btn btn-success clone_remove" name="" value="<?php echo '+' ?>" <?php echo $readonly; ?>>
                            </td>
                            <td>
                                <input type="text" name="" class="new_user form-control" placeholder="New User" value="" <?php echo $readonly; ?>>
                            </td>
                            <?php } ?>
                            <td class="rowspanned number b_person_count_total" 
                                rowspan="2" 
                                id="<?= $person_total_id; ?>" 
                            >   
                                <?= number_format($laborCost['b_person_total'], 4) ?>
                            </td>
                            <td class="rowspanned number b_person_count hidden-column" 
                                rowspan="2" 
                                id="<?= $person_id; ?>" 
                                cal_count=<?= in_array($laborCost['position_name'], $POSITION_CONSTANT) ? 0.5 : 1 ?>
                            >   
                                <!-- <?= number_format($laborCost['b_person_count'], 4) ?> -->
                                <?= number_format($hr, 4) ?>
                            </td>

                            <!-- Calculate Unit Labor Cost  -->
                            <?php 
                                
                                $unit_labor_cost = $laborCost['personnel_cost'];
                            ?>
                            <td class="rowspanned number unit_labor_cost" 
                                rowspan="2" 
                                id="<?= $labor_id; ?>" 
                                personnel_cost="<?= $laborCost['personnel_cost'] ?>" 
                                adjust_labor_cost="<?= $laborCost['adjust_labor_cost'] ?>"
                            >   
                                <?= number_format($unit_labor_cost, 2); ?>
                            </td>
                            <!--End Calculate Unit Labor Cost  -->

                            <!-- Calculate Unit Corpo Cost  -->
                            <?php 
                                
                                $unit_corpo_cost = $laborCost['corporate_cost'];
                            ?>
                            <td class="rowspanned number unit_corpo_cost" 
                                rowspan="2" 
                                id="<?= $corpo_id; ?>" 
                                corporate_cost="<?= $laborCost['corporate_cost'] ?>" 
                                adjust_corpo_cost="<?= $laborCost['adjust_corpo_cost'] ?>" 
                                common_expense="<?= $laborCost['common_expense'] ?>"
                            >   
                                <?= number_format($unit_corpo_cost, 2); ?>
                            </td>
                            <!-- End Calculate Corpo Labor Cost  -->

                            <!--end row span 2 -->
                            <td class="text">
                                <?php echo __("既存"); ?>
                            </td>
                            
                            <!-- loop of new hours and exist hours -->
                            <?php foreach ($laborCost['exist_hours'] as $key => $value) : ?>
                                <?php 
                                if($key != 'comment'){
                                    $personnelCost[$key] += $value * $laborCost['personnel_cost']; 
                                    $corporateCost[$key] += $value * $laborCost['corporate_cost'];
                                }
                                
                                ?>
                                <?php if($key == 'total'): ?>

                                <td 
                                    class="emp_tot number x-bold-border <?=$emp_id?>" 
                                    total_exist_id="<?= $total_exist_id; ?>" 
                                    val="<?= $value ?>"
                                    new_user_name="<?=$laborCost['user_name'];?>"
                                    position_code="<?=$laborCost['position_code'];?>"
                                >
                                    <?= number_format($value, 5) ?>
                                </td>
                                <?php elseif ($key == 'comment'): ?>
                                <td>
                                    <?php if($laborCost['user_id'] == 0 && !empty($laborCost['user_name'])){ 
                                        $exist_cmt_id = "exist_comment_".str_replace(' ', '_', $laborCost['user_name']);
                                    }else {
                                        $exist_cmt_id = "exist_comment_".$laborCost['user_id'];
                                    } ?>
                                    <input 
                                        id="<?= $exist_cmt_id ?>" 
                                        class="comment_input" 
                                        type="text" 
                                        value="<?= $value ?>" <?php echo $readonly; ?>
                                    >
                                </td>
                                <?php else: ?>
                                <td>
                                    <input 
                                        lcd_id="<?= $laborCost['exist_ids'][$key] ?>" 
                                        class="amount_input" 
                                        type="text" 
                                        business_id="<?= $key ?>" 
                                        user_id="<?= $laborCost['user_id'] ?>" 
                                        position_code="<?= $laborCost['position_code'] ?>"
                                        business_type="1" 
                                        value="<?= number_format($value, 2) ?>"
                                        val="<?= $value ?>"
                                        person="<?=$person_id?>"
                                        labor="<?=$labor_id?>"
                                        corpo="<?=$corpo_id?>"
                                        emp="<?=$emp_id?>"
                                        new_user_name="<?=$new_user?>" <?php echo $readonly; ?>
                                    >
                                </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <!-- End loop of new hours and exist hours -->
                            
                        </tr>
                        <!-- End New Row  -->
                        <!-- Exist Row -->
                        <tr class="bb-dotted" id="<?php echo 'new_'.$row_cnt; ?>">
                            <!-- khin -->
                            <?php if(empty($laborCost['user_name'])) { ?>
                            <td class="blank-cell"></td>
                            <td>
                                <select class="form-control position_name" <?php echo $readonly; ?>>
                                    <option value="0"><?php echo '--Select--' ?></option>
                                    <?php foreach ($position_lists as $poscode => $posgp) { 
                                        $posname = explode('/', $posgp)[0];
                                    ?>
                                        <option id="<?php echo $poscode; ?>" value="<?php echo $poscode; ?>" data=<?php echo $posgp ?>><?php echo $posname; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <?php } ?>
                            <td class="text">
                                <?php echo __("新規"); ?>
                            </td>
                            <?php foreach ($laborCost['new_hours'] as $key => $value) : ?>
                                <?php 
                                if($key != 'comment'){
                                    $personnelCost[$key] += $value * $laborCost['personnel_cost']; 
                                    $corporateCost[$key] += $value * $laborCost['corporate_cost']; 
                                }
                                ?>
                                <?php if($key == 'total'): ?>

                                <td 
                                    class="emp_tot number x-bold-border <?php echo $emp_id ?>" 
                                    total_new_id="<?= $total_new_id; ?>"  
                                    val="<?= $value ?>"
                                    new_user_name="<?=$laborCost['user_name'];?>"
                                    position_code="<?=$laborCost['position_code'];?>"
                                >
                                    <?= number_format($value, 5) ?>
                                </td>
                                <?php elseif($key == 'comment'): ?>
                                <td>
                                    <?php if($laborCost['user_id'] == 0 && !empty($laborCost['user_name'])){ 
                                        $new_cmt_id = "new_comment_".str_replace(' ', '_', $laborCost['user_name']);
                                    }else {
                                        $new_cmt_id = "new_comment_".$laborCost['user_id'];
                                    } ?>
                                    <input 
                                        id="<?= $new_cmt_id ?>" 
                                        class="comment_input" 
                                        type="text" 
                                        value="<?= $value ?>" <?php echo $readonly; ?>
                                    >
                                </td>
                                <?php else: ?>
                                <td>
                                    <input 
                                        lcd_id="<?= $laborCost['new_ids'][$key] ?>" 
                                        class="amount_input" 
                                        type="text" 
                                        business_id="<?= $key ?>" 
                                        user_id="<?= $laborCost['user_id'] ?>" 
                                        position_code="<?= $laborCost['position_code'] ?>" 
                                        business_type="2" 
                                        value="<?= number_format($value, 2) ?>" 
                                        val="<?= $value ?>"
                                        person="<?=$person_id?>"
                                        labor="<?=$labor_id?>"
                                        corpo="<?=$corpo_id?>"
                                        emp="<?=$emp_id?>"
                                        new_user_name="<?=$new_user?>" <?php echo $readonly; ?>
                                    >
                                </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            
                        </tr>
                        <!-- End Exist row  -->
                        <?php else: ?>
                        <!-- Adjustment due to transfer Row -->
                        <!-- Exist Row -->
                        <tr>
                            <!-- khin -->
                            <td class="blank-cell"></td>
                            <td class="text" colspan="4" rowspan="2">
                                <?php echo __('異動による調整額'); ?>
                            </td>
                            <td class="text">
                                <?php echo __("既存"); ?>
                            </td>
                            <?php foreach ($laborCost['exist_hours'] as $key => $value) : ?>
                                <?php if($key != 'total'): ?>
                                <td>
                                    <input 
                                        lcd_id="<?= $laborCost['exist_ids'][$key] ?>" 
                                        class="amount_input" 
                                        type="text" 
                                        business_id="<?= $key ?>" 
                                        business_type="1"
                                        value="<?= number_format($value, 2) ?>" 
                                        val="<?= $value ?>" <?php echo $readonly; ?>
                                    >
                                </td>
                                <?php else: ?>
                                <td class="number x-bold-border" id="exist_adjustment" val="<?= $value ?>">
                                    <?= number_format($value, 5) ?>
                                </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            
                            <td class="blank">

                            </td>
                        </tr>
                        <!-- New Row -->
                        <tr>
                            <td class="blank-cell"></td>
                            <td class="text">
                                <?php echo __("新規"); ?>
                            </td>
                            <?php foreach ($laborCost['new_hours'] as $key => $value) : ?>
                                <?php if($key != 'total'): ?>
                                <td>
                                    <input 
                                        lcd_id="<?= $laborCost['new_ids'][$key] ?>" 
                                        class="amount_input" 
                                        type="text" 
                                        business_id="<?= $key ?>" 
                                        business_type="2"
                                        value="<?= number_format($value, 2) ?>" 
                                        val="<?= $value ?>" <?php echo $readonly; ?>
                                    >
                                </td>
                                <?php else: ?>
                                <td class="number x-bold-border" id="new_adjustment" val="<?= $value ?>">
                                    <?= number_format($value, 5) ?>
                                </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            
                            <td class="blank">

                            </td>
                        </tr>
                        <!-- End Adjustment due to transfer Row -->
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <!-- Total Per business Row -->
                    <tr class="total_row">
                        <td  class="blank-cell"></td>
                        <td class="text" colspan="5">
                            <?php echo __("合　　　計"); ?>
                        </td>
                        <?php foreach ($totalTableOne as $key => $value) : ?>
                            <?php if($key != 'all_total'): ?>
                            <td 
                                class="number" 
                                total_business_id="<?= $key ?>"
                            >
                                <?= number_format($value, 2) ?>
                            </td>
                            <?php else: ?>
                            <!-- All Total  -->
                            <td class="number x-bold-border bottom-bold-border" id="all_total">
                                <?= number_format($value, 5) ?>
                            </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <td class="blank">

                        </td>
                    </tr>
                    <!-- End Total Per business row  -->
                </tbody>
            </table>
            <!--Table One  -->

            <!--Table Two (total user input except adjustment row)  -->
            <table id="table_two">
                <tbody>
                    <!-- Total New Row -->
                    <tr>
                        <td  class="blank-cell"></td>
                        <td class="blank">

                        </td>
                        <td class="blank">

                        </td>
                        <td class="blank">

                        </td>

                        <td rowspan="2" class="text bottom-bold-border x-bold-border top-bold-border">
                            <?php echo __("合計"); ?>
                        </td>

                        <td class="text">
                            <?php echo __("既存"); ?>
                        </td>
                        <?php foreach ($tableTwoExist as $key => $value) : ?>
                            <?php if($key != 'total'): ?>
                            <td class="number" id="exist_total_<?= $key ?>">
                                <?= number_format($value, 4) ?>
                            </td>
                            <?php else: ?>
                            <td class="number top-bold-border x-bold-border" id="exist_total">
                                <?= number_format($value, 4) ?>
                            </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        
                        <td class="blank">

                        </td>
                    </tr>
                    <!--End Total New Row -->
                    <!--Total Exist Row -->
                    <tr>
                        <td colspan="4" class="blank">

                        </td>
                        <td class="text">
                            <?php echo __("新規"); ?>
                        </td>
                        <?php foreach ($tableTwoNew as $key => $value) : ?>
                            <?php if($key != 'total'): ?>
                            <td class="number" id="new_total_<?= $key ?>">
                                <?= number_format($value, 4) ?>
                            </td>
                            <?php else: ?>
                            <td class="number bottom-bold-border x-bold-border" id="new_total">
                                <?= number_format($value, 4) ?>
                            </td>
                            <?php endif; ?>
                        <?php endforeach; ?>

                    </tr>
                    <!-- End Total Exist row  -->
                </tbody>
            </table>
            <!-- End Table Two  -->

            <!-- Table Three (calculate salary per personnel_cost and corporate_cost)  -->
            <table id="table_three">
                <tbody>
                    <!-- title row -->
                    <tr class="t3_header">
                        <td  class="blank-cell"></td>
                        <td class="blank">

                        </td>
                        <td class="blank">

                        </td>
                        <td class="blank">

                        </td>
                        <td class="blank">

                        </td>
                        <td class="blank" style="border-right: none;">

                        </td>
                        <?php foreach ($businesses as $business) : ?>
                            
                            <td class="text">
                                <?= $business['Layer']['name_jp'] ?>
                            </td>
                        <?php endforeach; ?>
                       
                        <td class="text top-bold-border x-bold-border">
                            <?php echo __("合計"); ?>
                        </td>
                        <td class="blank">

                        </td>

                    </tr>
                    <!--End title Row -->
                    <!--Personnel Cost per hour Row -->
                    <tr>
                        <td class="blank">

                        </td>
                        <td colspan="5" class="text">
                            <span>
                                <?php echo __("人件費"); ?>
                            </span>
                            <span style="float: right">
                                <?php echo __("関数済"); ?>
                            </span>
                        </td>
                        <?php $total= 0;
                            foreach ($personnelCost as $key => $value) : ?>
                            <?php
                                $value1 = $value * 12;
                                if($key != 'total') $total += $value1;
                                $totalLCA[$key] += $value1;
                                if($key != 'total'): ?>
                            <td class="number bg-th" id="salary_per_pc_<?= $key ?>" val=<?= number_format($value1, 4) ?>>
                                <?= number_format($value1);?>
                            </td>
                            <?php else: ?>
                            <td class="number x-bold-border" id="total_salary_per_pc" val=<?= number_format($total, 4) ?>>
                                <?= number_format($total);?>
                            </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        

                    </tr>
                   <!--End Personnel Cost per hour Row -->
                    <!--Corporate Cost per hour Row -->
                    <tr>
                        <td class="blank">

                        </td>
                        <td colspan="5" class="text">
                            <span>
                                <?php echo __("割当経費"); ?>
                            </span>
                            <span style="float: right">
                                <?php echo __("関数済"); ?>
                            </span>
                        </td>
                        <?php $total= 0;
                            foreach ($corporateCost as $key => $value) : ?>
                            <?php 
                                $value1 = $value * 12;
                                if($key != 'total') $total += $value1;
                                $totalLCA[$key] += $value1;
                                if($key != 'total'): ?>
                            <td class="number bg-th" id="salary_per_cc_<?= $key ?>" val=<?= number_format($value1, 4) ?>>
                                <?= number_format($value1) ?>
                            </td>
                            <?php else: ?>
                            <td class="number x-bold-border" id="total_salary_per_cc" val=<?= number_format($total, 4) ?>>
                                <?= number_format($total) ?>
                            </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                    <!-- End Corporate Cost per hour Row  -->
                    
                    <!-- ********
                        Adjustment Row 
                    ********** -->
                    <?php foreach ($laborCostAdjustments as $adjust_name => $values): ?>
                        <?php $i++ ?>
                        <tr>
                            <td class="blank">

                            </td>
                            <td colspan="5" class="text">
                                <?php echo __($adjust_name); ?>
                            </td>
                            <?php foreach ($values['hours'] as $key => $value): ?>   
                                <?php if($key != 'total'): ?>
                                    <td>
                                        <input 
                                            lca_id="<?= $values['ids'][$key] ?>" 
                                            class="amount_input" 
                                            type="text" 
                                            business_id="<?= $key ?>" 
                                            adjust_name="<?= $adjust_name ?>" 
                                            value="<?= number_format($value) ?>" 
                                            row="<?= $i ?>" 
                                            val="<?= $value ?>" <?php echo $readonly; ?>
                                        >
                                    </td>
                                <?php else: ?>
                                    <td class="number x-bold-border" id="total_adjust_<?= $i ?>" val=<?= number_format($value, 4) ?>>
                                        <?= number_format($value) ?>
                                    </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <!-- End adjustment Row -->
                    <!--Total Row -->
                    <tr class="total_row">
                        <td class="blank">

                        </td>
                        <td colspan="5" class="text">
                            <span>
                                <?php echo __("人件費合計"); ?>
                            </span>
                            <span style="float: right;">
                                <?php echo __("単位：千円"); ?>
                            </span>
                        </td>
                        <?php 
                            //Line code calculation for budget
                            $i = 0;
                            foreach ($totalLCA as $key => $value) : 
                        ?>
                            <?php 
                                
                                $line_code = json_decode($businesses[$i]['Layer']['parent_id'])->L4; 
                                $i += 1;
                            ?>
                            <?php if($key != 'total'): ?>
                            <td 
                                class="number" 
                                id="t3_total_<?= $key ?>" 
                                line_code="<?= $line_code ?>" 
                                business_code="<?= $key ?>" 
                                val=<?= number_format($value, 4) ?>
                            >
                                <?= number_format($value) ?>
                            </td>

                            <?php else: ?>
                            <td 
                                class="number bottom-bold-border x-bold-border" 
                                id="t3_all_total" 
                                val=<?= number_format($value, 4) ?>
                            >
                                <?= number_format($value) ?>
                            </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        

                    </tr>
                    <!-- End Total row  -->
                </tbody>
            </table>
            <!--End Table Three  -->
        </div>

    <?php endif ?>
    <?php endif ?>
    <!-- End Content Area  -->
</div>
<br>
<br>

<!-- Loading  -->
<div id="overlay">
    <span class="loader"></span>
</div>
<!-- End Loading  -->
<div id="load"></div>
<div id="contents"></div>

<script>
    const NONE_LABOR_COST = <?= $tableOne[0]['none_labor_cost'] ? 1 : 0 ?>;
    const LOGIN_ID = <?= $this->Session->read('LOGIN_ID') ?>;
    const URL = "<?= $this->webroot; ?>";
    const SAVE_COMFIRM_TITLE = '<?php echo __("保存確認");?>';
    const SAVE_COMFIRM_MSG1 = '<?php echo __("データを保存してよろしいですか。"); ?>';; 
    const SAVE_COMFIRM_MSG2 = '<?php echo __("データはすでに保存されています！ 上書きしますか、それとも結合しますか?"); ?>'; 
    const COMFIRM_MSG = '<?php echo __('確定してもよろしいですか？'); ?>'; 
    const CANCEL_COMFIRM_MSG = '<?php echo __("確定をキャンセルしてもよろしいですか?"); ?>'; 
    const YES1 = '<?php echo __("はい");?>';
    const YES2 = '<?php echo __("上書き");?>';
    const NO1 = '<?php echo __("いいえ");?>';
    const NO2 = '<?php echo __("結合");?>';
    const SELECTED_GROUP_CODE = "<?= $GROUP_CODE ?>";
    const POSITION_CONSTANT = Object.keys(<?php echo  json_encode(PositionType::PositionConstant) ?>);
    const OLD_TABLE_ONE = JSON.parse('<?php echo $old_tableOne; ?>');
    const OLD_TABLE_TWO = JSON.parse('<?php echo $old_tableTwo; ?>');
    const USER_POS = '<?php echo __("氏名 と 等級"); ?>';
    const BU_TERM_ID = '<?php echo $_SESSION["BU_TERM_ID"];?>';
    const BTN_NAME = "<?php echo __('コメント追加');?>";
    const TOTAL_ERROR = "<?php echo __('人員数 合計') ?>";
    const NEW_USER = "<?php echo __('新ユーザー名') ?>"

</script>

<!-- http://openexchangerates.github.io/accounting.js/ -->
<?= $this->Html->script('LaborCostDetail/accounting.min.js') ?>
<?= $this->Html->script('LaborCostDetail/custom.js') ?>