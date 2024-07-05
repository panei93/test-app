<style type="text/css">
	.tbl-wrapper{
		margin-bottom:20px;
	}
	.tbl-bu-analysis {
		table-layout: fixed;
		margin-top: 20px;
	}
	.blank-table-row td.show-hide-btn:before {
		content: '-';
		width: 20px;
	    display: inline-block;
	    height: 20px;
	    line-height: 20px;
	    background-color: #eee;
	    text-align: center;
	    position: absolute;
	    top: 0;
	    right: 5%;
	    z-index: 1;
   		color: #fff;
   		background-color: #d9534f;

	}
	.blank-table-row td.show-hide-btn.show-col:before {
		content: '+';
	    background-color: #4caf50;
	}
	.blank-table-row td.show-hide-btn:after {
		content: "";
		width: 95%;
	    display: inline-block;
	    height: 3px;
	    background-color: #eee;
	    position: absolute;
	    top: 10px;
	    right: 5%;
	}
	.blank-table-row, .blank-table-row td.show-hide-btn {
		background: #fff !important;
		border-bottom: none !important;
		border-top: none !important;
		border-left: none !important;
		border-right: none !important;
		height: 25px;
		position: relative;
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
		padding-left: 25px !important;
        vertical-align: top;
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
		z-index: 1000;
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
		z-index: 1000;
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0,0,0,0.2);
	}
	.blank-table-row td.show-hide-btn:hover {
		background-color: #FAFAFA !important;

	}

	.blank-table-row td.show-hide-btn:active {
	  transform: translateY(1px);
	  cursor: progress;
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
	#tbl_bu1 select.form-control{
		padding:6px;
		width: 100px;
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
</style>
<script type="text/javascript">
	$(document).ready(function () {
		$(window).on('beforeunload', () => {
			loadingPic()
		});
		$('#tbl_bu tr td.amount, #tbl_bu1 tr td.amount').each(function() {			
			var value = $(this).html();
			var amount = value.replace(/,/g, "");
			if(amount.indexOf('%') != -1){
				if(amount.slice(0,-1) < 0) $(this).addClass("negative");
			}else{
				if(amount < 0) {
					var t = amount.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
					pValue = (amount * (-1)).toString();
					var negValue = '-'+pValue.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
					$(this).html(negValue);
					$(this).addClass("negative");
				}
			}
			
		});
        if($('#tbl_bu').length > 0) { // check data is at least 1 row/column keep
			$('.tbl-wrapper').freezeTable({ 
				'columnNum' : 1,
				'columnKeep' : false,
				'freezeHead': true,			 
				'scrollBar': false,
		  	});
			setTimeout(function(){
	            $('.tbl-wrapper').freezeTable('resize');
	         }, 1000);
		}
		
		/* floating scroll */
		if($(".tbl-wrapper").length) {
			$(".tbl-wrapper").floatingScroll();
		}
        $(".table1 .name-field .arrow").click(function() {
	  		var rowLevel = $(this).parent('.name-field').attr('colspan');
	  		var getrowLevel = "1";
	  		if ($(this).parent('.name-field').hasClass('show-row')) {
	  			
	  			for (var i = rowLevel - 1; i > 0; i--) {
	  				
	  				$('.table1 .row-level-'+i).show();
	  				$('.table1 th.row-level-'+rowLevel).attr('colspan',1);

	  				if ($('.table1 .row-level-'+i+ ' .name-field').hasClass('show-row')) {
	  					
	  					getrowLevel = i;
	  					break
	  				}

	  			}
	  			$('.table1 .row-level-'+rowLevel+ ' .name-field').removeClass('show-row');
				var outer_width = $('th.bRight.layer').outerWidth()+'px';
				
			    $('.clone-column-table-wrap:first').css('width', outer_width);
			    $('.clone-head-table-wrap.clone-column-head-table-wrap:first').css('width', outer_width);
	  		} else {
	  			for (var i = rowLevel - 1; i > 0; i--) {
	  				$('.table1 .row-level-'+i).hide();
					$('.table1 tr.result').show();
	  			}
	  			$('.table1 th.row-level-'+rowLevel).attr('colspan',rowLevel);
	  			$('.table1 .row-level-'+rowLevel+ ' .name-field').addClass('show-row');
				var outer_width = $('th.bRight.layer').outerWidth()+'px';

			    $('.clone-column-table-wrap:first').css('width', outer_width);
			    $('.clone-head-table-wrap.clone-column-head-table-wrap:first').css('width', outer_width);
	  		}
	  	});
		$(".table2 .name-field .arrow").click(function() {
	  		var rowLevel = $(this).parent('.name-field').attr('colspan');
	  		var getrowLevel = "1";
	  		if ($(this).parent('.name-field').hasClass('show-row')) {
	  			
	  			for (var i = rowLevel - 1; i > 0; i--) {
	  				
	  				$('.table2 .row-level-'+i).show();
	  				$('.table2 th.row-level-'+rowLevel).attr('colspan',1);

	  				if ($('.table2 .row-level-'+i+ ' .name-field').hasClass('show-row')) {
	  					
	  					getrowLevel = i;
	  					break
	  				}

	  			}
	  			$('.table2 .row-level-'+rowLevel+ ' .name-field').removeClass('show-row');
				var outer_width = $('th.bRight.layer1').outerWidth()+'px';
	  		
			   $('.clone-column-table-wrap:last').css('width', outer_width);
			   $('.clone-head-table-wrap.clone-column-head-table-wrap:last').css('width', outer_width);
	  		} else {
	  			for (var i = rowLevel - 1; i > 0; i--) {
	  				$('.table2 .row-level-'+i).hide();
					$('.table2 tr.result').show();
	  			}
	  			$('.table2 th.row-level-'+rowLevel).attr('colspan',rowLevel);
	  			$('.table2 .row-level-'+rowLevel+ ' .name-field').addClass('show-row');
				var outer_width = $('th.bRight.layer1').outerWidth()+'px';
	  		
			   $('.clone-column-table-wrap:last').css('width', outer_width);
			   $('.clone-head-table-wrap.clone-column-head-table-wrap:last').css('width', outer_width);
	  		}
	  	});
        $(".table-responsive.tbl-wrapper").floatingScroll();
		$(".show-hide-btn").click(function() {
			var idName = $(this).attr('id');
	  		var recordColspan = $('#'+idName).attr('colspan') -1 ;
			
			if ($('#'+idName).hasClass('hide-col')) {
				if (idName == 'item-name') {
					$('.layer').attr('colspan',2);
					$('.'+idName+'-btn').attr('colspan',2);
				}else if(idName == 'profitability'){
					
					$('.profitability-cls').attr('colspan',12);
					$('.'+idName+'-btn').attr('colspan',12);
					$('.acc1').attr('colspan',6);
					$('.acc2').attr('colspan',6);
					$('.acc1Year').attr('colspan',1);
				}
				
				$('.row.table-responsive.tbl-wrapper .tbl-bu-analysis thead.check_period_table.bold-border-btm '+'.'+idName).hide();
		  		$('.row.table-responsive.tbl-wrapper .tbl-bu-analysis tbody '+'.'+idName).hide();
				$('.'+idName+'-btn').removeClass('hide-col');
	  			$('.'+idName+'-btn').addClass('show-col');
			}else{
				if (idName == 'item-name') {
					$('.layer').attr('colspan',5);
					$('.'+idName+'-btn').attr('colspan',5);
				}else if(idName == 'profitability'){
					
					$('.profitability-cls').attr('colspan',18);
					$('.'+idName+'-btn').attr('colspan',18);
					$('.acc1').attr('colspan',12);
					$('.acc2').attr('colspan',6);
					$('.acc1Year').attr('colspan',2);
				}
				$('.row.table-responsive.tbl-wrapper .tbl-bu-analysis thead.check_period_table.bold-border-btm '+'.'+idName).show();
		  		$('.row.table-responsive.tbl-wrapper .tbl-bu-analysis tbody '+'.'+idName).show();
				$('.'+idName+'-btn').removeClass('show-col');
	  			$('.'+idName+'-btn').addClass('hide-col');
			}
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
		var obj = new Object();
		obj.target_year = $('#target_year').val();
		obj.bu = $('#bu :selected').val();
		obj.group = $('#group :selected').val();
		var search = <?php echo json_encode($this->Session->read('SEARCH_LABOR_COST'));?>;
		//console.log(search);
		localStorage.setItem("SEARCH_LABOR_COST", (JSON.stringify(search)));
		$('#filter-btn').on('click', onFilterHandler);
		function onFilterHandler(){
			var obj = new Object();
			year = $("#target_year :selected").text();
			$('#download').val('');
			obj.target_year = year;
			obj.bu = $('#bu :selected').val();
			obj.group = $('#group :selected').val();
			localStorage.setItem("SEARCH_LABOR_COST", (JSON.stringify(obj)));
			var target_year = $('#target_year').val();
			const URL = "<?= $this->webroot; ?>";
			loadingPic();
			document.forms[0].action = URL + "BusinessAnalysis";
			document.forms[0].submit();
		}
		var bu = <?php echo json_encode($bu);?>;
		var group = <?php echo json_encode($group);?>;

		$("#target_year").change(function() {

			year = $(this).val();
			// set session
			var obj = new Object();
			obj.target_year = year;
			obj.bu = $("#bu option:eq(0)").val();
			obj.group = 'all';
			localStorage.setItem("SEARCH_LABOR_COST", (JSON.stringify(obj)));
			// update bu dropdown
			bu_list = bu[year];
			$('#bu').empty();
			var opt = '';
			$.each(bu_list, function(index, value) {
				opt += '<option value="'+index+'">'+value+'</option>';
				
			});
			$('#bu').append(opt);
			// update group dropdown
			group_list = group[year];
			$('#group').empty();
			var group_opt = '<option value="all">ALL</option>';
			$.each(group_list, function(index, value) {
				if(index == $("#bu option:eq(0)").text()){
					$.each(value, function(index1, value1) {
						if(nextLayer != ''){
							if(index1 == nextLayer) group_opt += '<option value="'+index1+'" selected>'+value1+'</option>';
						}else{
							group_opt += '<option value="'+index1+'">'+value1+'</option>';
						}
					});
				}
			});
			$('#group').append(group_opt);

		});
		
		selectedBU = $("#bu :selected").text();
		selectedGroup = '<?php echo $search_data['group']; ?>';
		nextLayer = '<?php echo $nextLayer[0];?>';
		
		var selection = <?php echo json_encode($this->Session->read('SELECTION'));?>;
		$("#bu").change(function() {
			selectedBUText = $("#bu :selected").text();
			selectedBUCode = $("#bu :selected").val();
			let selectedBU = `${selectedBUCode}/${selectedBUText}`;
			var search_laborCost = JSON.parse(localStorage.getItem('SEARCH_LABOR_COST'));
			year = search_laborCost.target_year;
			if(!year) year = $('#target_year').val();
			if(selection == 'SET') selectedGroup = <?php echo json_encode($this->Session->read('SELECTED_GROUP'));?>;
			else if(selection == 'NOT') selectedGroup = search_laborCost.group;
			// update group dropdown
			if(selectedBU == 'ALL') group_list = group[year];
			else group_list = group[year][selectedBU];
			
			$('#group').empty();
			if(selectedGroup == 'all') var group_opt = '<option value="all" selected>ALL</option>';
			else var group_opt = '<option value="all">ALL</option>';
			var group_opt = '<option value="all">ALL</option>';
			if(selectedBU == 'ALL'){
				$.each(group_list, function(index, value) {
					$.each(value, function(index1, value1) {
						group_opt += '<option value="'+index1+'">'+value1+'</option>';
					});
				});
			}else{
				$.each(group_list, function(index, value) {
					if(nextLayer != ''){
						if(selectedGroup != 'all' && index == nextLayer) group_opt += '<option value="'+index+'" selected>'+value+'</option>';
						else if(index == nextLayer) group_opt += '<option value="'+index+'">'+value+'</option>';
					}else{
						if(index == selectedGroup) group_opt += '<option value="'+index+'" selected>'+value+'</option>';
						else group_opt += '<option value="'+index+'">'+value+'</option>';
					}
					
				});
			}
			
			$('#group').append(group_opt);
		});	
		if(selectedBU != 'ALL') $("#bu").trigger('change');
    });	
	
	
	function loadingPic() {
		var ua = window.navigator.userAgent;
		var msie = ua.indexOf("MSIE ");

		if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
			var el = document.getElementById("imgLoading");
			var i = 0;
			var pics = [ "<?php echo $this->webroot; ?>img/loading1.gif",
               "<?php echo $this->webroot; ?>img/loading2.gif",
               "<?php echo $this->webroot; ?>img/loading3.gif" ,
               "<?php echo $this->webroot; ?>img/loading4.gif" ];

			function toggle() {
				el.src = pics[i]; // set the image
				i = (i + 1) % pics.length; // update the counter
			}
			setInterval(toggle, 250);
			$("#overlay").show();
		} else {
			$("#overlay").show();
		}
	}
</script>
<div id="overlay"><span class="loader"></span></div>
<div id="load"></div>
<div id="contents">
</div>
<?=
	$this->Form->create(false, array(
		'id' => 'filter_form',
		'autocomplete' => 'off', 
	));
?>
<input type="hidden" id="bu_analysis" name="bu_analysis" />
<div class="content" style="padding: 0px 30px;font-size: 1em !important;">
	<div class="errorSuccess">
        <div id="success"><?php echo $this->Flash->render("bu_success")?></div>
        <div id="error"><?php echo $this->Flash->render("bu_error"); ?></div>
    </div>
	<?php if(count($accountNames) > 0 && !$no_data){
		$buttonDisabled = 'disabled';
		$saveBtnDisabled = 'disabled';
		if($buFlag == 2) {
			$buttonDisabled = '';
			$saveBtnDisabled = '';
		}
		if($spreadsheetFlag == 2) $saveBtnDisabled = 'disabled';
		else if($spreadsheetFlag != 2) $saveBtnDisabled = '';
		if($search_data['group'] == 'all') {
			
			if($allowAll == 'allow'){
				if($buttonDisabled != 'disabled') $buttonDisabled = '';
				if($saveBtnDisabled != 'disabled') $saveBtnDisabled = '';
			}else if($allowAll == 'not'){
				if($buFlag == 2){
					$buttonDisabled = '';
					$saveBtnDisabled = '';
				}
				
			}
			
		}
		//if($search_data['group'] == 'all' && $parent_spreadsheetFlag == 1 && $spreadsheetFlag == 1) $buttonDisabled = 'disabled';
		?>
    <div class="form-group row">
        <fieldset class="">
            <div class="col-md-12 col-sm-12 heading_line_title">
                <h3><?php echo __("集計表"); ?></h3><hr>
            </div>
			<div class="row">
				<div class="form-group col-sm-3">
					<input type="text" class="form-control" value="<?php echo $this->Session->read('TERM_NAME');?>" disabled>
				</div>
				<div class="form-group col-sm-3">
					<select id="target_year" name="target_year" class="form-control">
                        <!-- <option value="">---- Select ----</option> -->
                        <?php foreach ($year as $value) : ?>
                            <option value="<?= $value ?>" <?php echo  ($value == $search_data['target_year']) ? 'selected' : ''; ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-sm-3">
					<select id="bu" name="bu" class="form-control" style="width: 100% !important;">
						<!-- <option value="all">ALL</option> -->
                        <?php foreach ($bu[$search_data['target_year']] as $key=>$value) : ?>
                            <option value="<?=$key ?>" <?php echo  ($key == $search_data['bu']) ? 'selected' : ''; ?> ><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
				</div>
				<div class="form-group col-sm-3">
					<select id="group" name="group" class="form-control" style="width: 100% !important;">
						<option value="all">ALL</option>
                        <?php foreach ($group[$search_data['target_year']] as $key=>$value) : ?>
							<?php foreach ($value as $key1=>$value1) : ?>
                            	<option value="<?=$key1 ?>" <?php echo  ($key1 == $search_data['group']) ? 'selected' : ''; ?> ><?= $value1 ?></option>
							<?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
				</div>
				<div class="form-group col-md-3">
					<button type="button" class="btn btn-success btn_sumisho_set" id="filter-btn">
                        <?php echo __("設定選択") ?>
                    </button>
                </div>
			</div>	
            <div class="form-row">
				<input type="hidden" name="download" id="download" value="">
				<input type="hidden" name="showRow" id="showRow" value="">
				<input type="hidden" name="s_showRow" id="s_showRow" value="">
            </div>
        </fieldset>
    </div>
	<div class="form-group row" style="text-align:right;">
		<div class="col-md-12" style="padding: 0px;">
		<button type="button" class="btn-save" onClick="onDownloadHandler()">
			<?php echo __("ダウンロード") ?> 
		</button>
		<?php 
		if($pageLimit['BusinessAnalysisSheetSaveLimit'] !== false){
			if($spreadsheetFlag != 2){
				if($allow_save == true){
			?>
				<button type="button" class="btn btn-save" onClick="onSaveHandler()" <?php echo $saveBtnDisabled; ?>>
					<?php echo __("一時保存") ?> 
				</button>
				<?php
				}
				 if($pageLimit['BusinessAnalysisSheetConfirmLimit'] !== false){
					if($allow_confirm == true){
					?>
				<button type="button" class="btn btn-save" onClick="onFinalConfirmHandler()" <?php echo $buttonDisabled; ?>>
					<?php echo __("最終確定") ?> 
				</button>
				<?php }
				} ?>
			<?php 
			}
			if($pageLimit['BusinessAnalysisSheetConfirm_cancelLimit'] !== false){
				if($spreadsheetFlag == 2){
					$cancelDisabled = '';
					//if($search_data['group'] != 'all' && $parent_spreadsheetFlag == 2) $cancelDisabled = 'disabled';
					if($allow_confirm_cancel == true){
					?>
					<button type="button" class="btn btn-save" onClick="onCancelConfirmHandler()" <?php echo $cancelDisabled; ?>>
						<?php echo __("確定解除") ?> 
					</button>
					<?php
					}
				}
			}
		}
		?>
		</div>
	</div>
	
	<?php 
	$first3Years = array_slice($resultYear, 0, 3, true);
	$last3Years = array_slice($resultYear, -3, 3, true);
	$colSpan = (count($lOrder)-1) + 3;
	if($search_data['group'] != 'all') $type_order = 3;
    else $type_order = 2;
	?>
    <div class="form-group row">
        <div class="col-lg-12 col-md-12">
            <div class="row table-responsive tbl-wrapper">
            <table class="tbl-bu-analysis bold-border table1" id="tbl_bu">
                <thead class="check_period_table bold-border-btm">
					<!-- <tr class="blank-table-row">
						<td colspan="5" class="bRight show-hide-btn item-name-btn hide-col" id="item-name"></td>
						<td colspan="18" class="bRight show-hide-btn profitability-btn hide-col" id="profitability"></td>
					</tr> -->
                    <tr class="bold-border-top bold-border-lft bold-border-rgt">
                        <th colspan="<?php echo $colSpan; ?>" class="bRight layer" style="min-width: 80px" ><?php echo __("ビジネス"); ?></th>
                        <th colspan="18" class="bRight profitability-cls" style="min-width: 80px" ><?php echo __("収益性（百万円）"); ?></th>
						<th class="bRight" style="min-width: 50px" ><?php echo __("成長性"); ?></th>
						<th colspan="3" class="bRight" style="min-width: 50px" ><?php echo __("グローバルケミカルへの貢献度"); ?></th>
						<th colspan="6" class="bRight" style="min-width: 50px" ><?php echo __("生産性"); ?></th>
					</tr>
                </thead>
				<tbody>
                <!-- <thead class="check_period_table bold-border-btm" rowspan="2"> -->
                    <tr class="bold-border-top bold-border-lft bold-border-rgt title check_period_table">
                    <td rowspan="2" colspan="<?php echo (count($lOrder)-1); ?>" class="" style="min-width: 80px" ><?php echo __("中計括り"); ?></td>
                    <td rowspan="2"  class="item-name" style="min-width: 80px" ><?php echo __("販売先or商品"); ?></td>
                    <td rowspan="2"  class="item-name" style="min-width: 80px" ><?php echo __("商品or販売先"); ?></td>
                    <td rowspan="2"  class="bRight item-name" style="min-width: 80px" ><?php echo __("形態"); ?></td>
                    <td colspan="12" class="bRight acc1" style="min-width: 80px" ><?php echo $accountNames[0].'/'.$accountNames[1]; ?></td>
                    <td colspan="6" class="bRight acc2" style="min-width: 80px" ><?php echo $accountNames[2]; ?></td> 
					<td class="bRight"><?php echo __("売総成長率(%)"); ?></td>
					<td colspan="3" class="bRight" style="min-width: 80px" ><?php echo $accountNames[3]; ?></td>  
					
					<td colspan="3" class="bRight" style="min-width: 80px" ><?php echo ($search_data['target_year']-1).__("人員 (人）"); ?></td>  
					<td colspan="3" class="" style="min-width: 80px" ><?php echo $search_data['target_year'].__("予算人員 (人）"); ?></td>  
					
                    </tr>
                <!-- </thead> -->
                <!-- <thead class="check_period_table bold-border-btm"> -->
				<tr class="bold-border-top bold-border-lft bold-border-rgt title check_period_table">
					
					<?php 
					foreach($resultYear as $key=>$value){
						$class = '';
						if($key == sizeof($resultYear)-1) $class = 'bRight';
					?>
						<td class="<?php echo $class; ?> acc1Year" colspan=2><?php echo $value; ?></td>
					<?php
					}?>
					<?php 
					foreach($resultYear as $key=>$value){
						$class = '';
						if($key == sizeof($resultYear)-1) $class = 'bRight';
					?>
						<td class="<?php echo $class; ?>" colspan=1><?php echo $value; ?></td>
					<?php
					}?>
					<td class="bRight"><?php echo __("売総成長率(%)"); ?></td>
					<?php
					foreach($last3Years as $key=>$value){
						$class = '';
						if($key == sizeof($resultYear)-1) $class = 'bRight';
					?>
					<td class="<?php echo $class; ?>"><?php echo $value; ?></td>
					<?php
					}
					?>
					<td><?php echo __("経営・管理"); ?></td>
					<td><?php echo __("営業 "); ?></td>
					<td class="bRight"><?php echo __("ｵﾍﾟﾚｰｼｮﾝ"); ?></td>
					<td><?php echo __("経営・管理"); ?></td>
					<td><?php echo __("営業 "); ?></td>
					<td class=""><?php echo __("ｵﾍﾟﾚｰｼｮﾝ"); ?></td>
					</tr>
				<!-- </thead> -->
                
                    <?php 
                    if(sizeof($prepare_layer) > 0){
						$accTotal = array();
						$acc2Total = array();
						$acc3Total = array();
						$emp = array(); 
						
                        foreach($prepare_layer as $pkey=>$pvalue){
							$colLevel = (count($lOrder)-1)-1;
							$typeOrder = 0;
							foreach($pvalue as $key=>$value){	
								$topAmount = $value['amount'];
								$topEmp = $value['emp'];
								$tfirstAmount = $topAmount[$accountNames[0]][$first3Years[0]] + $topAmount[$accountNames[0]][$first3Years[1]] + $topAmount[$accountNames[0]][$first3Years[2]];
								$tlastAmount = $topAmount[$accountNames[0]][$last3Years[3]] + $topAmount[$accountNames[0]][$last3Years[4]] + $topAmount[$accountNames[0]][$last3Years[5]];
								$tresAmount = $tlastAmount/$tfirstAmount;
								//if($value['Layer']['type_order'] == 2){
									foreach($resultYear as $yValue){
										$accTotal[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[0]][$yValue] : 0;
										$acc2Total[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[2]][$yValue] : 0;
										
									}
									foreach($last3Years as $yValue){
										$acc3Total[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[3]][$yValue] : 0;
									}
									foreach($topEmp as $eKey=>$eValue){
										$emp[$eKey][$search_data['target_year'] - 1] += ($value['type_order'] == $type_order) ? $eValue[$search_data['target_year'] - 1] : 0;
										$emp[$eKey][$search_data['target_year']] += ($value['type_order'] == $type_order) ? $eValue[$search_data['target_year']] : 0;
	
									}
									//pr($emp);
								//}
								
								
								?>
								<?php 
								$name_field = (!empty($prepare_layer)) ? 'name-field' : ''; 
								if($colLevel == 0) $cLevel = '';
								else $cLevel = 'col-level-'.$colLevel;
								$rowLevel = $value['rowLevel'];
								?>
								<tr class="row-level-<?php echo $rowLevel;?>" type_order="<?php echo $value['type_order'];?>">

									<?php
									if($rowLevel < (count($lOrder)-1)){
										$addTD = (count($lOrder)-1) - $rowLevel;
										for($i = $addTD;$i>0;$i--){
											?>
												<td class="bt-none bb-none <?php echo $cLevel; ?>"></td>
											<?php
											$cLevel = $cLevel - 1;
											//if($value['type_order'] < $typeOrder) $colLevel = $colLevel - 1;
											if($colLevel == 0) $cLevel = '';
											else $cLevel = 'col-level-'.$colLevel;
										}
									}
									
									?>
									<td colspan="<?php echo $rowLevel; ?>" class = "bb-none <?php echo($name_field).' '.$cLevel; ?>" ><span class="arrow"></span><?php echo $value['name_jp']; ?></td>
									
									<td class="item-name"><?php echo $value['item_1']; ?></td>
									<td class="item-name"><?php echo $value['item_2']; ?></td>
									<td class="bRight item-name"><?php echo $value['form']; ?></td>
									<td class="amount"><?php echo $topAmount[$accountNames[0]][$resultYear[0]] ? number_format($topAmount[$accountNames[0]][$resultYear[0]]) : 0;?></td>
									<td class="profitability amount"><?php echo number_format($topAmount[$accountNames[1]][$resultYear[0]], 1).'%';?></td>

									<td class="amount"><?php echo $topAmount[$accountNames[0]][$resultYear[1]] ? number_format($topAmount[$accountNames[0]][$resultYear[1]]) : 0;?></td>
									<td class="profitability amount"><?php echo number_format($topAmount[$accountNames[1]][$resultYear[1]], 1).'%';?></td>

									<td class="amount"><?php echo $topAmount[$accountNames[0]][$resultYear[2]] ? number_format($topAmount[$accountNames[0]][$resultYear[2]]) : 0;?></td>
									<td class="profitability amount"><?php echo number_format($topAmount[$accountNames[1]][$resultYear[2]], 1).'%';?></td>

									<td class="amount"><?php echo $topAmount[$accountNames[0]][$resultYear[3]] ? number_format($topAmount[$accountNames[0]][$resultYear[3]]) : 0;?></td>
									<td class="profitability amount"><?php echo number_format($topAmount[$accountNames[1]][$resultYear[3]], 1).'%';?></td>

									<td class="amount"><?php echo $topAmount[$accountNames[0]][$resultYear[4]] ? number_format($topAmount[$accountNames[0]][$resultYear[4]]) : 0;?></td>
									<td class="profitability amount"><?php echo number_format($topAmount[$accountNames[1]][$resultYear[4]], 1).'%';?></td>

									<td class="amount"><?php echo $topAmount[$accountNames[0]][$resultYear[5]] ? number_format($topAmount[$accountNames[0]][$resultYear[5]]) : 0;?></td>
									<td class="bRight profitability amount"><?php echo number_format($topAmount[$accountNames[1]][$resultYear[5]], 1).'%';?></td>

								<?php
									foreach($resultYear as $key=>$yVlaue){
										$class = '';
										if($key == sizeof($resultYear)-1) $class = 'bRight';
									?>
										<td class="<?php echo $class; ?> amount"><?php echo $topAmount[$accountNames[2]][$yVlaue] ? number_format($topAmount[$accountNames[2]][$yVlaue]) : 0; ?></td>
									<?php
									} 
								?>
								<td class="bRight amount percent"><?php echo (is_nan($tresAmount*100) || $tresAmount == INF) ? '0.0%' : number_format($tresAmount*100, 1).'%'; ?></td>
								<?php
								foreach($last3Years as $key=>$lValue){
										$class = '';
										if($key == sizeof($resultYear)-1) $class = 'bRight';
									?>
									<td class="<?php echo $class; ?> amount"><?php echo number_format($topAmount[$accountNames[3]][$lValue]); ?></td>
									<?php
									}
									?>
									<td class="amount"><?php echo $topEmp['経営・管理'][$search_data['target_year']-1] ? number_format($topEmp['経営・管理'][$search_data['target_year']-1], 0, '.', '') : '0'?></td>
									<td class="amount"><?php echo $topEmp['営業'][$search_data['target_year']-1] ? number_format($topEmp['営業'][$search_data['target_year']-1], 0, '.', ''): '0';?></td>
									<td class="bRight amount"><?php echo $topEmp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']-1] ? number_format($topEmp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']-1], 0, '.', ''): '0';?></td>
									<td class="amount"><?php echo $topEmp['経営・管理'][$search_data['target_year']] ? number_format($topEmp['経営・管理'][$search_data['target_year']], 0, '.', ''): '0';?></td>
									<td class="amount"><?php echo $topEmp['営業'][$search_data['target_year']] ? number_format($topEmp['営業'][$search_data['target_year']], 0, '.', ''): '0';?></td>
									<td class="amount"><?php echo $topEmp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']] ? number_format($topEmp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']], 0, '.', ''): '0';?></td>
								</tr>
								<?php 
								$colLevel = $colLevel + 1;
								$typeOrder = $value['type_order'];
							}	
                        } 
                    }
					//echo '<pre>';print_r($emp);echo '</pre>';
                    ?>
                    <tr class="row-level-2 result">
						<td colspan="<?php echo $colSpan; ?>" class="bRight layer"><?php echo __("合計");?></td>
						<?php 
						$i= 0;
						
						foreach($accTotal as $key=>$value){
							$class = '';
							if($i == sizeof($accTotal)-1) $class = 'bRight';
						?>
							<td colspan=2 class="<?php echo $class; ?> amount acc1Year"><?php echo number_format($value); ?></td>
						<?php
							$i++;
						}
						?>
						<?php 
						$i= 0;
						foreach($acc2Total as $value){
							$class = '';
							if($i == sizeof($acc2Total)-1) $class = 'bRight';
						?>
							<td class="<?php echo $class; ?> amount"><?php echo number_format($value); ?></td>
						<?php
							$i++;
						}
						?>
						<td class="bRight"></td>
						<?php 
						$i= 0;
						foreach($acc3Total as $value){
							$class = '';
							if($i == sizeof($acc3Total)-1) $class = 'bRight';
						?>
							<td class="<?php echo $class; ?> amount"><?php echo number_format($value); ?></td>
						<?php
							$i++;
						}
						?>
						
						
						<td class="amount"><?php echo number_format($emp['経営・管理'][$search_data['target_year']-1], 0, '.', ''); ?></td>
						<td class="amount"><?php echo number_format($emp['営業'][$search_data['target_year']-1], 0, '.', ''); ?></td>
						<td class="bRight amount"><?php echo number_format($emp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']-1], 0, '.', ''); ?></td>
						<td class="amount"><?php echo number_format($emp['経営・管理'][$search_data['target_year']], 0, '.', ''); ?></td>
						<td class="amount"><?php echo number_format($emp['営業'][$search_data['target_year']], 0, '.', ''); ?></td>
						<td class="amount"><?php echo number_format($emp['ｵﾍﾟﾚｰｼｮﾝ'][$search_data['target_year']], 0, '.', ''); ?></td>
					</tr>
                </tbody>
            </table>
            </div>
        </div>
		<?php 
		$resultYear1 = range($search_data['target_year']-1, $search_data['target_year']+2);
		?>
		<div class="form-row">
            <div class="col-lg-12 col-md-12">
				<div class="row table-responsive tbl-wrapper" style="margin-bottom: 50px;">
				<table class="tbl-bu-analysis bold-border table2" id ="tbl_bu1">
					<thead class="check_period_table bold-border-btm">
						<!-- <tr class="blank-table-row">
							<td colspan="5" class="bRight show-hide-btn item-name-btn hide-col" id="item-name1"></td>
							<td colspan="18" class="bRight show-hide-btn  hide-col" id=""></td>
						</tr> -->
						<tr class="bold-border-top bold-border-lft bold-border-rgt">
							<th colspan="<?php echo $colSpan; ?>" class="bRight layer1" style="min-width: 80px" ><?php echo __("ビジネス"); ?></th>
							<th colspan="19" class="bRight profitability-cls" style="min-width: 80px" ><?php echo __("資金効率（百万円）"); ?></th>
							<th colspan="4" class="bRight" style="min-width: 50px" ><?php echo __("取引意義【「＋」＝△、「－」＝▲】"); ?></th>
							<th colspan="<?php echo sizeof($hyoka_name_arr); ?>" class="bRight" style="min-width: 50px" ><?php echo __("取引リスク評価【0～4の５段階】"); ?></th>
							<th colspan="1" class="bRight" style="min-width: 50px" ><?php echo __("CSR上のリスク懸念"); ?></th>
							<th colspan="4" class="bRight" style="min-width: 50px" ><?php echo __("取引方針【〇】"); ?></th>
						</tr>
					</thead>
						<tr class="bold-border-top bold-border-lft bold-border-rgt title check_period_table">
							<td rowspan="2" colspan="<?php echo (count($lOrder)-1); ?>" class="" style="min-width: 80px" ><?php echo __("中計括り"); ?></td>
							<td rowspan="2"  class="" style="min-width: 80px" ><?php echo __("販売先or商品"); ?></td>
							<td rowspan="2"  class="" style="min-width: 80px" ><?php echo __("商品or販売先"); ?></td>
							<td rowspan="2"  class="bRight" style="min-width: 80px" ><?php echo __("形態"); ?></td>	
							<td colspan="4" class="bRight " style="min-width: 80px" ><?php echo $accountNames[4]; ?></td>
							<td colspan="4" class="bRight " style="min-width: 80px" ><?php echo $accountNames[5]; ?></td>
							<td colspan="4" class="bRight " style="min-width: 80px" ><?php echo $accountNames[6]; ?></td>
							<td colspan="4" class="bRight " style="min-width: 80px" ><?php echo $accountNames[7]; ?></td>
							<td colspan="3" class="bRight " style="min-width: 80px" ><?php echo __("ファクタリング考慮後ROIC (%)"); ?></td>
							<td rowspan="2" class="" style="min-width: 80px" ><?php echo __("シナジー（百万円）"); ?></td>
							<td rowspan="2" class="" style="min-width: 80px" ><?php echo __("商品競争力"); ?></td>
							<td colspan="2" class="bRight " style="min-width: 80px" ><?php echo __("最終製品"); ?></td>
							
							<?php 
							if(sizeof($hyoka_name_arr) > 0){
								$h=0;
								foreach($hyoka_name_arr as $key=>$hName){
								?>
									<td rowspan="2" class="<?php echo ($h == sizeof($hyoka_name_arr)-1) ? 'bRight' : ''; ?>" style="min-width: 80px" ><?php echo $hName; ?></td>
								<?php
									$h++;
								}
							}else{
								?>
								<td rowspan="2" class="<?php echo 'bRight'; ?>" style="min-width: 80px" ></td>
								<?php
							}
							?>
							
							<td rowspan="2" class="bRight" style="min-width: 80px" ><?php echo __("評価（Ａ～Ｅ、NA）のうちA・B・の該否"); ?></td>
							<td rowspan="2" class="" style="min-width: 110px" ><?php echo __("拡　大"); ?></td>
							<td rowspan="2" class="" style="min-width: 110px" ><?php echo __("維　持"); ?></td>
							<td rowspan="2" class="" style="min-width: 110px" ><?php echo __("縮小・撤退"); ?></td>
							<td rowspan="2" class="bRight" style="min-width: 110px" ><?php echo __("左記方針の取進め条件"); ?></td>
						</tr>
						<tr class="bold-border-top bold-border-lft bold-border-rgt title check_period_table">
						<?php 
						foreach($resultYear1 as $key=>$value){
							$class = '';
							if($key == sizeof($resultYear1)-1) $class = 'bRight';
						?>
							<td class="<?php echo $class; ?>"><?php echo $value; ?></td>
						<?php
						}?>
						<?php 
						foreach($resultYear1 as $key=>$value){
							$class = '';
							if($key == sizeof($resultYear1)-1) $class = 'bRight';
						?>
							<td class="<?php echo $class; ?>"><?php echo $value; ?></td>
						<?php
						}?>
						<?php 
						foreach($resultYear1 as $key=>$value){
							$class = '';
							if($key == sizeof($resultYear1)-1) $class = 'bRight';
						?>
							<td class="<?php echo $class; ?>"><?php echo $value; ?></td>
						<?php
						}?>
						<?php 
						foreach($resultYear1 as $key=>$value){
							$class = '';
							if($key == sizeof($resultYear1)-1) $class = 'bRight';
						?>
							<td class="<?php echo $class; ?>"><?php echo $value; ?></td>
						<?php
						}?>
						<?php 
						foreach($resultYear1 as $key=>$value){
							$class = '';
							if($key == sizeof($resultYear1)-1) $class = 'bRight';
							if($key != 0){
						?>
							<td class="<?php echo $class; ?>"><?php echo $value; ?></td>
						<?php
							}
						}?>
							<td class=" " style="min-width: 80px" ><?php echo __("競争力"); ?></td>
							<td class="bRight " style="min-width: 80px" ><?php echo __("成長性"); ?></td>
							
						</tr>
						<?php 
                    	if(sizeof($prepare_layer) > 0){
							$acc4Total = array();
							$acc5Total = array();
							$acc6Total = array();
							
							foreach($prepare_layer as $pkey=>$pvalue){
								$colLevel = (count($lOrder)-1)-1;
								$typeOrder = 0;
								foreach($pvalue as $key=>$value){
									$name_field = (!empty($prepare_layer)) ? 'name-field' : '';
									if($colLevel == 0) $cLevel = '';
									else $cLevel = 'col-level-'.$colLevel;

									$topAmount = $value['amount'];
									foreach($resultYear1 as $yValue){
										$acc4Total[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[4]][$yValue] : 0;
										$acc5Total[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[5]][$yValue] : 0;
										$acc6Total[$yValue] += ($value['type_order'] == $type_order) ? $topAmount[$accountNames[6]][$yValue] : 0;
										
									}
									$rowLevel = $value['rowLevel'];
									?>
									<tr class="row-level-<?php echo $rowLevel;?>">
										<?php
										if($rowLevel < (count($lOrder)-1)){
											$addTD = (count($lOrder)-1) - $rowLevel;
											for($i = $addTD;$i>0;$i--){
												?>
													<td class="bt-none bb-none <?php echo $cLevel; ?>"></td>
												<?php
												$cLevel = $cLevel - 1;
												if($colLevel == 0) $cLevel = '';
												else $cLevel = 'col-level-'.$colLevel;
											}
										}
										
										?>
										<td colspan="<?php echo $rowLevel; ?>" class = "bb-none <?php echo($name_field).' '.$cLevel; ?>" ><span class="arrow"></span><?php echo $value['name_jp']; ?></td>
									
										<td class="item-name"><?php echo $value['item_1']; ?></td>
										<td class="item-name"><?php echo $value['item_2']; ?></td>
										<td class="bRight item-name"><?php echo $value['form']; ?></td>
										<?php
										foreach($resultYear1 as $yKey=> $yValue){
											$class = '';
											if($yKey == 3) $class = 'bRight';
										?>
											<td class="amount <?php echo $class; ?>"><?php echo $topAmount[$accountNames[4]][$yValue] ? number_format($topAmount[$accountNames[4]][$yValue]) : 0;?></td>
										<?php
										}
										?>
										<?php
										foreach($resultYear1 as $yKey=>$yValue){
											$class = '';
											if($yKey == 3) $class = 'bRight';
										?>
											<td class="amount <?php echo $class; ?>"><?php echo $topAmount[$accountNames[5]][$yValue] ? number_format($topAmount[$accountNames[5]][$yValue]) : 0;?></td>
										<?php
										}
										?>
										<?php
										foreach($resultYear1 as $yKey=>$yValue){
											$class = '';
											if($yKey == 3) $class = 'bRight';
										?>
											<td class="amount <?php echo $class; ?>"><?php echo $topAmount[$accountNames[6]][$yValue] ? number_format($topAmount[$accountNames[6]][$yValue]) : 0;?></td>
										<?php
										}
										?>
										<?php
										foreach($resultYear1 as $yKey=>$yValue){
											$class = '';
											if($yKey == 3) $class = 'bRight';
										?>
											<td class="amount <?php echo $class; ?>"><?php echo $topAmount[$accountNames[7]][$yValue] ? number_format($topAmount[$accountNames[7]][$yValue]) : 0;?></td>
										<?php
										}
										?>
										<?php
										foreach($resultYear1 as $yKey=>$yValue){
											$class = '';
											if($yKey == 3) $class = 'bRight';
											if($yKey != 0){
										?>
											<td class="amount <?php echo $class; ?>"><?php echo $topAmount[$accountNames[7]][$yValue] ? number_format($topAmount[$accountNames[7]][$yValue]) : 0;?></td>
										<?php
											}
										}
										if($value['BudgetComp']['final_total']['add_triangle']) {
											$deli = $value['BudgetComp']['final_total']['deli_product'].'△'.$value['BudgetComp']['final_total']['deli_chg_product'];
											$indus = $value['BudgetComp']['final_total']['indus_fproduct'].'△'.$value['BudgetComp']['final_total']['indus_chg_fproduct'];
											$potential = '△'.$value['BudgetComp']['final_total']['final_potential'];
										}else{
											$deli = $value['BudgetComp']['final_total']['deli_product'].$value['BudgetComp']['final_total']['deli_chg_product'];
											$indus = $value['BudgetComp']['final_total']['indus_fproduct'].$value['BudgetComp']['final_total']['indus_chg_fproduct'];
											$potential = $value['BudgetComp']['final_total']['final_potential'];
										}
										
										?>
										<td class="amount"><?php echo $value['sgns']; ?></td>
										<td class="amount"><?php echo $deli; ?></td>
										<td class="amount"><?php echo $indus; ?></td>
										<td class="amount bRight"><?php echo $potential; ?></td>
										<?php 
										if(isset($value['approveLog']['flag'])){
											if($value['approveLog']['flag'] == 2) {
												$buttonDisabled = 'disabled';
												$saveBtnDisabled = 'disabled';
											}
											else {
												$buttonDisabled = '';
												$saveBtnDisabled = '';
											}
										}else $saveBtnDisabled = '';
										
										if($spreadsheetFlag == 2) {
											$buttonDisabled = 'disabled';
											//$saveBtnDisabled = 'disabled';
										}
										//if($buttonDisabled) $saveBtnDisabled = $buttonDisabled;
										if(sizeof($hyoka_name_arr) > 0){
											$i=0;
											foreach($hyoka_name_arr as $hVlaue){
												$class = '';
												if($i == sizeof($hyoka_name_arr)-1 ) $class='bRight';
											?>
											<td class="amount <?php echo $class; ?> hyoka"><?php echo $value['BudgetHyoka'][$hVlaue] ? $value['BudgetHyoka'][$hVlaue] : '-'; ?></td>
											<?php
												$i++;
											}
										}else{
											?>
											<td class="amount <?php echo $class; ?> hyoka"><?php echo '-'; ?></td>
											<?php
										}
										?>
										<td class="amount bRight"><?php echo $value['BudgetHyoka']['CSR'] ? $value['BudgetHyoka']['CSR'] : '-'; ?></td>
										<td>
											<select class="form-control" id="select_1_<?php echo $value['layer_code']; ?>_<?php echo $value['type_order']; ?>" name="select_1_<?php echo $value['layer_code']; ?>" <?php echo $saveBtnDisabled; ?>>
												<option value="0">**選択**</option>
												<option value="1" <?php if($value['TransactionPolicy']['expansion'] == '1') echo 'selected';?>>〇</option>
											</select>
										</td>
										<td>
											<select class="form-control" id="select_2_<?php echo $value['layer_code']; ?>_<?php echo $value['type_order']; ?>" name="select_2_<?php echo $value['layer_code']; ?>" <?php echo $saveBtnDisabled; ?>>
												<option value="0">**選択**</option>
												<option value="1" <?php if($value['TransactionPolicy']['maintain'] == '1') echo 'selected';?>>〇</option>
											</select>
										</td>
										<td>
											<select class="form-control" id="select_3_<?php echo $value['layer_code']; ?>_<?php echo $value['type_order']; ?>" name="select_3_<?php echo $value['layer_code']; ?>" <?php echo $saveBtnDisabled; ?>>
												<option value="0">**選択**</option>
												<option value="1" <?php if($value['TransactionPolicy']['withdraw'] == '1') echo 'selected';?>>〇</option>
											</select>
										</td>
										<td>
											<select class="form-control" id="select_4_<?php echo $value['layer_code']; ?>_<?php echo $value['type_order']; ?>" name="select_4_<?php echo $value['layer_code']; ?>" <?php echo $saveBtnDisabled; ?>>
												<option value="0">**選択**</option>
												<option value="1" <?php if($value['TransactionPolicy']['transactionTerm'] == '1') echo 'selected';?>>〇</option>
											</select>
										</td>
									</tr>
							<?php
									$colLevel = $colLevel + 1;
									$typeOrder = $value['type_order'];
								}
							}
						}
						?>
						<tr class="row-level-2 result">
							<td colspan="<?php echo $colSpan; ?>" class="bRight layer"><?php echo __("合計");?></td>
							<?php 
							$i= 0;
							
							foreach($acc4Total as $key=>$value){
								$class = '';
								if($i == sizeof($acc4Total)-1) $class = 'bRight';
							?>
								<td class="<?php echo $class; ?> amount"><?php echo number_format($value); ?></td>
							<?php
								$i++;
							}
							?>
							<?php 
							$i= 0;
							
							foreach($acc5Total as $key=>$value){
								$class = '';
								if($i == sizeof($acc5Total)-1) $class = 'bRight';
							?>
								<td class="<?php echo $class; ?> amount"><?php echo number_format($value); ?></td>
							<?php
								$i++;
							}
							?>
							<?php 
							$i= 0;
							
							foreach($acc6Total as $key=>$value){
								$class = '';
								if($i == sizeof($acc6Total)-1) $class = 'bRight';
							?>
								<td class="<?php echo $class; ?> amount"><?php echo number_format($value); ?></td>
							<?php
								$i++;
							}
							?>
							
							<td></td>
							<td></td>
							<td></td>
							<td class="bRight"></td>
							<td></td>
							<td></td>
							<td class="bRight"></td>
							<td></td>
							<td></td>
							<td></td>
							<td class="bRight"></td>
							<?php 
							$i = 0;
							foreach($hyoka_name_arr as $hVlaue){
								$class = '';
								if($i == sizeof($hyoka_name_arr)-1 ) $class='bRight';
							?>
							<td class="<?php echo $class; ?>"></td>
							<?php
							$i++;
							}
							?>
							<td class="bRight"></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<?php 
							if(sizeOf($hyoka_name_arr) == 0){?>
							<td></td>
							<?php
							}
							?>
						</tr>
					<tbody>

					</tbody>
				</table>
				</div>
			</div>
		</div>
    </div>
	<?php }else{
		if(count($accountNames) == 0){
		?>
			<div class="col-sm-12">
				<p class="no-data"><?php echo __("表示するアカウントはありません。"); ?></p>
			</div>
		<?php
		}else if($no_data){
		?>
			<div class="col-sm-12">
				<p class="no-data"><?php echo __("データが見つかりません！"); ?></p>
			</div>
		<?php
		}
		?>
		
	<?php
	}?>
</div>

<?php echo $this->Form->end(); ?>
<script type="text/javascript">
	var finalConfirm = 'not';
	var TABLE_Test = $("#tbl_bu1 select");
	var onChangeData = [];
	const formattedData = [];
	var sel_bu = $('#bu :selected').val();
	var sel_group = $('#group :selected').val();
	var saveType = {save_type : "merge", finalConfirm : finalConfirm, bu :sel_bu, group : sel_group};
	formattedData.push({save_type : "merge", finalConfirm : finalConfirm, bu :sel_bu, group : sel_group});
	TABLE_Test.map((index, select) => {
		var id = select.getAttribute("id");
		var idArr = id.split("_");
		if(idArr[1] == '1'){
			var expansion = "", maintain = "", withdraw = "", transactionTerm = "";
		}
		
		if(idArr[1] == '4'){
			transaction = { 
				//save_type: "merge",
				layer_code: idArr[2],
				target_year: $('#target_year').val(),
				type_order: idArr[3],
				expansion: expansion,
				maintain: maintain,
				withdraw: withdraw,
				transactionTerm: transactionTerm,
			
			};
			formattedData.push(transaction);
			
		}
	});
	$("#tbl_bu1 select").change(function(){
		var sel_bu = $('#bu :selected').val();
		var sel_group = $('#group :selected').val();
		var arr = [];
		
		var id = this.getAttribute("id");
		var idArr = id.split("_");
		var data = [];
		$.each(formattedData, function (key, val) {
			
			if(val.layer_code == idArr[2]){
				if(idArr[1] == '1') formattedData[key]['expansion'] = $('#'+id).val();
				else if(idArr[1] == '2') formattedData[key]['maintain'] = $('#'+id).val();
				else if(idArr[1] == '3') formattedData[key]['withdraw'] = $('#'+id).val();
				else if(idArr[1] == '4') formattedData[key]['transactionTerm'] = $('#'+id).val();
			}
		});
		$('#bu_analysis').val(JSON.stringify(formattedData));
	});
	
	const SAVE_CONTENT = '<?php echo __("データを保存してよろしいですか。");?>';
	const SAVE_CONTENT_MERGE = '<?php echo __("データはすでに保存されています！ 上書きしますか、それとも結合しますか?");?>';


    const CONFIRM_CONTENT = "<?php echo __("最終確認でよろしいですか？"); ?>";
	const CONFIRM_CONTENT_MERGE = "<?php echo __("データはすでに保存されています！最終確認のために上書きしますか? それとも結合しますか?"); ?>";
	const YES = '<?php echo __("はい");?>';
    const NO = '<?php echo __("いいえ");?>';
	let target_year = $("#target_year").val();
	
	function onSaveHandler() {
		finalConfirm = 'not';
		saveHandler();
	}
	
	function saveHandler() {
		
		var sel_bu = $('#bu :selected').val();
		var sel_group = $('#group :selected').val();
		
		const URL = "<?= $this->webroot; ?>";
		var layerCodeArr = [];
		TABLE_Test.map((index, select) => {
			var id = select.getAttribute("id");
			var idArr = id.split("_");
			console.log(idArr[2]);
			layerCodeArr.push(idArr[2]);
		});
		
		if(finalConfirm == 'finalConfirm') content = CONFIRM_CONTENT;
		else content = SAVE_CONTENT;
		first_btn = '<?php echo __("はい");?>';
		sec_btn = '<?php echo __("いいえ");?>';
		save_type = true;
		btn_type1 = "btn-info";
		btn_type2 = "btn-default";
		$.confirm({
			title: '<?php echo __("保存確認");?>',
			icon: "fas fa-exclamation-circle",
			type: "green",
			typeAnimated: true,
			closeIcon: true,
			columnClass: "medium",
			animateFromElement: true,
			animation: "top",
			draggable: false,
			content: content,
			buttons: {
				ok: {
					text: first_btn,                  
						btnClass: btn_type1,                  
						action: function(){
							loadingPic();
							
							formattedData[0]['finalConfirm'] = finalConfirm;
							$('#bu_analysis').val(JSON.stringify(formattedData));
							document.forms[0].action = URL + "BusinessAnalysis/add";
							document.forms[0].submit();
							return true;
						}
				},
				cancel: {
					text: sec_btn,
					btnClass: 'btn-default',
					action: function(){}
				},
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		});
				
		
	}
	function onDownloadHandler(){
		var showRow = $("table.table1 td").hasClass("show-row");
		var s_showRow = $("table.table2 td").hasClass("show-row");
		var target_year = $('#target_year').val();
		var bu = $('#bu :selected').text();
		var group = $('#group :selected').text();
		$('#download').val('download_'+target_year+'_'+bu+'_'+group);
		$('#showRow').val(showRow);
		$('#s_showRow').val(s_showRow);
		// document.forms[0].action = "<?php echo $this->webroot; ?>BusinessAnalysis/DownloadSpreadsheet";
		// document.forms[0].method = "POST";
		// // document.forms[0].submit();  

		loadingPic();
		let fileName = '集計表_'+target_year+'_'+bu+'_'+group+'.xlsx';
		fetch("<?php echo $this->webroot; ?>BusinessAnalysis/DownloadSpreadsheet", {
			method: 'POST',
			body: new FormData(document.forms[0]),
		})
			.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}

					let disposition = response.headers.get('Content-Disposition');

					// Try to extract the filename from the Content-Disposition header
					if (disposition && disposition.indexOf('attachment') !== -1) {
						let filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
						let matches = filenameRegex.exec(disposition);
						if (matches != null && matches[1]) {
								fileName = decodeURIComponent(matches[1].replace(/['"]/g, ''));
						}
					}
					return response.blob();
			})
			.then(blob => {
					let blobUrl = URL.createObjectURL(blob);
					let link = document.createElement('a');
					link.href = blobUrl;
					link.download = fileName;

					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);

					$("#overlay").hide();
			})
			.catch(error => {
					$("#overlay").hide();
					console.error('Error during fetch operation:', error.message);
			});

		return true; 
	}
	
	
	function onFinalConfirmHandler(){
		finalConfirm = 'finalConfirm';
		saveHandler();
	}
	function onCancelConfirmHandler(){
		var sel_bu = $('#bu :selected').val();
		var sel_group = $('#group :selected').val();
		var target_year = $("#target_year").val();
		
		$.confirm({
			title: '<?php echo __("確定解除");?>',
			icon: "fas fa-exclamation-circle",
			type: "green",
			typeAnimated: true,
			closeIcon: true,
			columnClass: "medium",
			animateFromElement: true,
			animation: "top",
			draggable: false,
			content: "<?php echo __("確認をキャンセルしてもよろしいですか?"); ?>",
			buttons: {
				ok: {
					text: "<?php echo __("はい"); ?>",
					btnClass: "btn-info",
					action: function() {
						loadingPic();
						$('#bu_analysis').val(JSON.stringify(formattedData));
						document.forms[0].action = "<?php echo $this->webroot; ?>BusinessAnalysis/cancelConfirm";
						document.forms[0].method = "POST";
						document.forms[0].submit();			
						return true;
					}
				},
				cancel: {
					text: "<?php echo __("いいえ"); ?>",
					btnClass: "btn-default",
					cancel: function() {
					}
				}
			},
			theme: 'material',
			animation: 'rotateYR',
			closeAnimation: 'rotateXR'
		})
		
	}
</script>

