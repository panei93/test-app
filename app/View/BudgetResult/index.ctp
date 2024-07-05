 <?php
    echo $this->Form->create(false, array('type' => 'post', 'id' => '', 'enctype' => 'multipart/form-data', 'autocomplete' => 'off'));
    ?>
 <style type="text/css">
     table.table-fixed {
         width: 100%;
         table-layout: fixed;
         border: 1px solid #fff;
     }

     div.table-wrapper {
         width: 100%;
         overflow-x: auto;
     }

     div.tbl-wrappers {
         width: 100%;
         overflow: hidden !important;
     }

     .yearPicker th {
         border: 0px !important;
     }

     table th {
         height: 33px !important;
         border: 1px solid #ddd !important;
     }

     #budget_points th,
     #budget_issues th {
         padding-left: 5px;
     }

     .ui-wrapper {
         padding: 0 !important;
         height: 100%;
     }

     .ui-resizable-se {
         right: -1px;
         bottom: 3px;
     }

     .ui-resizable {
         padding: 1px !important;
         width: 100% !important;
     }

     textarea {
         width: 100% !important;
     }

     td textarea,
     td select {
         background: rgb(255, 255, 204) !important;
         font-size: 12px !important;
     }

     #budget_points textarea {
         resize: vertical;
     }

     td input[type=""] {
         background: rgb(255, 255, 204) !important;
         outline: none !important;
         border: none !important;
         border-radius: 0 !important;
         box-shadow: inset 0 0 0 !important;
         border-top: 0 !important;
         font-size: 12px !important;
     }

     td input[disabled],
     td textarea[disabled],
     td select[disabled] {
         background-color: #fff !important;
         border-style: none !important;
         cursor: auto !important;
         box-shadow: none !important;
     }

     #budgets td input[readonly] {
         cursor: pointer !important;
         background-color: #d5f4ff !important;
         border-style: none !important;
         box-shadow: none !important;
     }

     .btn[disabled] {
         transform: none;
     }

     #budgets td,
     #budgets input,
     #budgets_total td,
     #budgets_total input,
     #tax_exchange input {
         height: 28px !important;
     }

     .btn_add_comps,
     .btn_remove_comps {
         width: 88px !important;
     }

     .l_padding {
         height: 35px !important;
         text-align: left;
         padding-left: 5px;
     }

     .number {
         text-align: right !important;
         padding: 8px;
     }

     .negative {
         color: #f31515;
     }

     .txt_disa {
         height: 36px !important;
     }

     td input.change_bg_color {
         background: #d5f4ff !important;
         outline: none !important;
         border: none !important;
         border-radius: 0 !important;
         box-shadow: inset 0 0 0 !important;
         border-top: 0 !important;
         font-size: 12px !important;
     }

     td.td_header {
         padding: 5px 0px;
         background-color: #e5ffff;
         vertical-align: middle !important;
         text-align: center;
         font-size: 0.9em !important;
         font-weight: bold;
     }

     #load {
         z-index: 1000;
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(0, 0, 0, 0.2);
     }

     .form_disabled[disabled] {
         background-color: #eee !important;
     }

     textarea.td_color {
        resize: none;
        background: #fff !important;
        text-decoration: none !important;
        border-style: none !important;
        border: none !important;
        border-radius: 0px !important;
        box-shadow: none !important;
        height: 108px;
        cursor: unset;
    }

    td.chg_col1 {
        text-align: center !important;
    }

    @media only screen and (max-width: 1280px) {
        td.chg_col1 {
            text-align: left !important;
            padding-left: 10px !important;
        }

        td.chg_col1 {
            visibility: hidden;
            font-size: 1px !important;
        }
        
        td.chg_col1::before {
            content: '本取引があることで明らかに認められる（期待される）\a下記各分野のｼﾅｼﾞｰ（ｲﾝﾊﾟｸﾄ)の合計額';
            visibility: visible;
            white-space: pre-wrap;
            font-size: 1rem !important;
        }
    }
 </style>
 <script type="text/javascript">
     $(document).ready(function() {

        $(window).on('beforeunload', () => {
			loadingPic()
		});

         var save_hide = '<?php echo $save_hide; ?>';
         var approve_hide = '<?php echo $approve_hide; ?>';
         var cancel_hide = '<?php echo $cancel_hide; ?>';

         if (!save_hide) {
             $("#btn_save").show();
         }
         if (!approve_hide) {
             $("#btn_approve").show();
         }
         if (!cancel_hide) {
             $("#btn_app_cancel").show();
         }
         /* #when loading, not allow any action */
         document.onreadystatechange = function() {
             var state = document.readyState;
             if (state == 'interactive') {
                 document.getElementById('contents').style.visibility = "hidden";
             } else if (state == 'complete') {
                 setTimeout(function() {
                     document.getElementById('interactive');
                     document.getElementById('load').style.visibility = "hidden";
                     document.getElementById('contents').style.visibility = "visible";

                 }, 1000);
             }
         }

         /* #freeze header */
         if ($('#budgets').length > 0) {
             $('.freeze').freezeTable({
                 'namespace': 'tbl-freeze-table',
                 'freezeHead': true
             });
             setTimeout(function() {
                 $('.freeze').freezeTable('resize');
             }, 1000);
         }

         checkTDinput(<?php echo json_encode($select_ids); ?>); /* check input disabled or not */

         makingNegativeReady(); /* make negative when reload */

        /* minus and decimal not to more than one */
        $("input.number").on('keyup', function(){
            if($(this).val().length > 1 && $(this).val().slice($(this).val().length - 1, $(this).val().length) == '-'){
                $(this).val($(this).val().slice(0,$(this).val().length - 1));
            }
            this.value = this.value
            .replace(/-?\d+[^0-9.]+/g,'')        // minus only start at the start
            .replace(/(\-.*)\-/g, '$1')         // minus can't exist more than once
            .replace(/(\..*)\./g, '$1');        // decimal can't exist more than once
        });
        $("input.number").on('keypress', function(){
            if($(this).val().length > 1 && $(this).val().slice($(this).val().length - 1, $(this).val().length) == '-'){
                $(this).val($(this).val().slice(0,$(this).val().length - 1));
            }
            this.value = this.value
            .replace(/-?\d+[^0-9.]+/g,'')        // minus only start at the start
            .replace(/(\-.*)\-/g, '$1')          // minus can't exist more than once
            .replace(/(\..*)\./g, '$1');         // decimal can't exist more than once
        });
        $("input.number").on('change', function(){
            if($(this).val() == '-' || $(this).val() == '.' || $(this).val() == '.-' || $(this).val() == '-.'){
                $(this).val(0);
            }
            if($(this).val().length > 1 && $(this).val().slice($(this).val().length - 1, $(this).val().length) == '-'){
                $(this).val($(this).val().slice(0,$(this).val().length - 1));
            }
        });

         var show_tmp_btn = '<?php echo $show_tmp_btn; ?>';
         if (show_tmp_btn) {
             $("#btn_save").attr('disabled', false);
         } else {
             $("#btn_save").attr('disabled', true);
         }
         var show_complete_btn = '<?php echo $show_complete_btn; ?>';
         if (show_complete_btn) {
             $("#btn_approve").attr('disabled', false);
         } else {
             $("#btn_approve").attr('disabled', true);
         }

         $('#budgets td input[readonly]').on('click', function() {
             var inp = this.id;
             $("#" + inp).prop('readonly', false); //change td to input
             $("#" + inp).addClass('change_bg_color');
             var name = $("#hid_" + inp).attr('name');
             $("#" + inp).attr('name', name);
             $("#" + inp).focus();
             $('<input>').attr({
                 type: 'hidden',
                 id: 'dbl_' + inp,
                 name: 'dbl_edit_flag[]',
                 value: 'dbl_' + inp,
                 class: 'form-control'
             }).appendTo($(this));
             $("#" + inp).attr('data', "2_1");
         });

         $('.yearPicker').on('focusout', function() {
             setLayer();
         });

         $('.selectClass').on('change', function() {
             /* auto select layer */
             var chosenid = this.id;
             var selId = $("#" + this.id);
             var selectedVal = $("#" + this.id).val();
             var selectedTxt = $("#" + this.id + " :selected").text();
             var ids = <?php echo json_encode($_SESSION['SELECT_IDS']); ?>;

             var selectedList = [];
             var pair_id = '';
             var remove_id = 0;
             $.each(ids, function(key, value) {
                 var id = value.split('/')[0];
                 var un_remove_id = '';
                 if (id == chosenid) {
                     pair_id = value;
                     remove_id = Number(key) + 1;
                 }
                 if (remove_id <= key) {
                     if (remove_id != 0) $('#' + id + ' option[value!="0"]').remove();
                 }
                 var selected = $("#" + id + " :selected").val();
                 if (selected.indexOf("-----") == -1) {
                     selectedList.push(selected);
                 }

             });
             layerList(this.id, selectedVal, selectedTxt, selectedList, pair_id);
         });

         $("table").on('focusin', 'input:not(.percent)', function() {
             var id = $("#" + this.id);
             if (id.prop('readonly')) {
                 id.val(id.val());
             } else {
                 var value = id.val().replace(/\,|\.0+$/g, '');
                 value = (value == 0) ? '' : parseFloat(value).toString();
                 id.val(value);
             }
         });

         /* when focusin, show real amount(hidden field) in input */
         $("#budgets").on('focusin', 'input:not(.percent)', function() {
             var id = $("#" + this.id);
             if (id.prop('readonly')) {
                 id.val(id.val());
             } else {
                 var value = $("#hid_" + this.id).val().replace(/\,|\.0+$/g, '');
                 value = (value == 0) ? '' : parseFloat(value).toString();
                 id.val(value);
             }
         });

         /* tax and exchange */
         $("#tax_exchange").on('change', '.number', function() {
             var year = this.id.split("_")[0];
             var formula = calcu_arr[year];
             calculatedBudget(year, formula, year);
             MakeNeg($("#budgets input.number"));
         });

         $("#tax_exchange, #budgets, #budgets_total, #budget_comps").on('focusout', '.percent', function() {
             var m_b_year = '<?php echo $manual_budget_year; ?>';
             var c_year = (this.id).split('_')[0];
             var value = $(this).val();
             if ($(this).closest('td').attr('class') != undefined) {
                 if ($(this).closest('td').attr('class').indexOf('settlement') != -1 || $(this).closest('input').attr('class').indexOf("percent")) {
                     $(this).val((value == '') ? '0%' : value + '%');
                 } else {
                     if (value != '' && value.indexOf('.') === -1) var value = value + '.0';
                     $(this).val((value == '') ? '0.0%' : value + '%');
                 }
             } else {
                 if (value != '' && value.indexOf('.') === -1) var value = value + '.0';
                 $(this).val((value == '') ? '0.0%' : value + '%');
             }

             /*if(m_b_year == c_year) {
             	$(this).val($(this).val().replace(/\%+$/g,''));
             }*/
         });

         $("#tax_exchange, #budgets, #budgets_total, #budget_comps").on('focusin', '.percent', function() {
             if ($("#" + this.id).prop('readonly')) {
                 $(this).val($(this).val());
             } else {
                 var val = $(this).val().replace(/\%+$/g, '');
                 var val = val.replace(/,/g, '');
                 val = (val == 0) ? '' : parseFloat(val).toString();
                 $(this).val(val);
             }
         });

         $("#tax_exchange, #budget_comps").on('focusout', '.change_td', function() {
             $(this).val(($(this).val() == '') ? '0.0' : checkisNaNval($(this).val().replace(/,/g, ''), 1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,'));
         });
         $("#budgets_total tr td input, #budgets tr td.acc_amt input[type=''], #tax_exchange tr td input, #budget_sngs tr td input, #budget_comps tr td input").on('input', function() {
             $(this).val($(this).val().replace(/[^0-9\.\-]/g, ''));
         });
         /* budgets calculation */
         let calcu_arr = <?php echo json_encode($calcu_arr); ?>;
         let end_yr = <?php echo json_encode(end($next_years)); ?>;
         var tmp_save_datas_overwrite = [];
         var tmp_save_datas_merge = [];
         let factor_calculate = <?php echo json_encode($factor_calulate); ?>;
         let jq_nxt_yrs = <?php echo json_encode($jq_nxt_yrs); ?>;
         let yrStr = [];
         $.each(jq_nxt_yrs, function(i, v) {
             yrStr.push(v.toString());
         });
         $("#budgets tr td.acc_amt input[type='']").on('change', function() {
             $(this).val($(this).val().replace(/[^0-9\.\-]/g, '')); //allow decimal
             var year = this.id.split("_")[0]; //input year
             var nextyear = parseInt(this.id.split("_")[0]) + 1;
             var accId = this.id.split("_")[1]; //input acc_id
             var calVal = $("#" + year + "_" + accId).val().replace(/,/g, ''); //input amt
             var hidinputId = $("#hid_" + year + "_" + accId);
             var hidcalVal = calVal;
             hidinputId.val(hidcalVal); //when input, auto fill to hidden input
             $(this).val((calVal == '') ? 0 : calVal);
             var formula = calcu_arr[year]; //get current year formula
             formula[accId] = hidcalVal; //assign current amt to formula
             calculatedBudget(year, formula, "#" + year + "_" + accId); //calculate the budget_amt with formula
             //calculate last_year from formula
             if (end_yr > nextyear) calculatedBudget(nextyear, calcu_arr[nextyear], "#" + year + "_" + accId);
             calculatedBudget(year, formula, "#" + year + "_" + accId);
             saleTotal(tot_sales_formula); //total sale per person
             if (jQuery.inArray(year, yrStr) !== -1) {
                 calculatedFactor(year, factor_calculate[year], "#" + year + "_" + accId)
             }
             var id = this.id;
             var budget = $("#hid_" + id).val();
             save_datas = id + "_" + budget;
             tmp_save_datas_merge.push(save_datas);
             $("#budgets tr td.acc_amt input[type='']").each(function(idx, val) {
                 var id = this.id;
                 var budget = $("#hid_" + id).val();
                 save_datas = id + "_" + budget;
                 tmp_save_datas_overwrite.push(save_datas);
             });
         });

         $("#budgets tr td.acc_amt input").on('focusout', function() {
             var percent = $(this).attr('data-percent');
             var acc_name = $(this).attr('data-account');
             if(percent != '' || acc_name.indexOf('ヶ月') != -1) {
                var decimal = 1;
             }else {
                var decimal = '';
             }
             $(this).val(checkisNaNval($(this).val().replace(/,/g, ''), decimal).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,'));
         });

         /* employee calculation */
         $('#budgets_total').on('change', '.employee_td', function() {
             var id = $(this).find('input.number')[0]['id'];
             var year = id.split("_")[0];
             var employee = id.split("_")[1];
             var empTotal = 0;
             $("#budgets_total .employee_td input[id^=" + year + "_]").each(function() {
                 empTotal += checkisNaN("#" + this.id);
             });
             $("#" + year + "_emptot").val(empTotal.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,'));
             saleTotal(tot_sales_formula); // tot_sales_calculation
             MakeNeg($("#budgets_total input[id^=" + year + "_]"));
         });

         $("#budgets_total").on('focusout', '.employee', function() {
             $(this).val(($(this).val() == '') ? '0.00' : checkisNaNval($(this).val().replace(/,/g, ''), 2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,'));
         });

         /* total sale per person calculation */
         let tot_sales_formula = <?php echo json_encode($tot_sales_formula); ?>;
         saleTotal(tot_sales_formula);

         /* budget_sng calculation */
         $("#budget_sngs tr td input").on('change', function() {
             var sngs_id = $("#" + this.id);
             var sngs_val = $("#" + this.id).val();
             var total_amount = 0;
             $("#budget_sngs input:not(#sngs_total)").each(function() {
                 total_amount += checkisNaN("#" + this.id);
             });
             $("#sngs_total").val(total_amount);
             MakeNeg($("#budget_sngs input"));
         });

         $("#budget_sngs input").on('focusout', function() {
             $(this).val(($(this).val() == '') ? '0' : $(this).val());
         });

         /* budget_comps calculation */
         $(".btn_add_comps").on('click', function() {
             var tr = $('tr[id^="ptr_"]:last');
             var prevnum = parseInt(tr.prop("id").match(/\d+/g), 10);
             var num = parseInt(tr.prop("id").match(/\d+/g), 10) + 1;

             $("table#budget_comps tbody tr#ptr_" + prevnum).after(tr.clone().prop('id', 'ptr_' + num).appendTo("table#budget_comps"));
             $('tr#ptr_' + num + ' #num_' + prevnum).prop('id', 'num_' + num);
             $('tr#ptr_' + num + ' td#num_' + num).text(num);

             $('tr#ptr_' + num).each(function() {
                 $(this).find('td input.percent').each(function() {
                     var id = $(this).prop('id');
                     var Id = id.replace("td_" + prevnum, "td_" + num);
                     $(this).prop('id', Id);
                     $(this).val('0%');

                     var name = $(this).prop('name');
                     var Name = name.replace("[" + prevnum + "]", "[" + num + "]");
                     $(this).prop('name', Name);

                 });
                 $(this).find('td input.change_td').each(function() {
                     var id = $(this).prop('id');
                     var Id = id.replace("td_" + prevnum, "td_" + num);
                     $(this).prop('id', Id);
                     $(this).val('0.0');

                     var name = $(this).prop('name');
                     var Name = name.replace("[" + prevnum + "]", "[" + num + "]");
                     $(this).prop('name', Name);
                 });
                 $(this).find('td textarea').each(function() {
                     var id = $(this).prop('id');
                     var Id = id.replace("td_" + prevnum, "td_" + num);
                     $(this).prop('id', Id);
                     $(this).val('');

                     var name = $(this).prop('name');
                     var Name = name.replace("[" + prevnum + "]", "[" + num + "]");
                     $(this).prop('name', Name);
                 });
                 $(this).find('td input[type=checkbox]').each(function() {
                     var id = $(this).prop('id');
                     var Id = id.replace("checkbox_" + prevnum, "checkbox_" + num);
                     $(this).prop('id', Id);
                     $(this).prop('disabled', false);
                 });
                 $(this).find('td.number').each(function() {
                     var id = $(this).prop('id');
                     var Id = id.replace("td_" + prevnum, "td_" + num);
                     $(this).prop('id', Id);
                     $(this).text('');
                 });
             });
         });

         $(".btn_remove_comps").on('click', function() {
             $("input[type=checkbox]").each(function() {
                 if ($(this).is(':checked')) {
                     var id = this.id;
                     var num = this.id.split('_')[1];
                     $("#ptr_" + num).remove();
                 }
             });
             var trId = $("tr[id^=ptr_]");
             var newID = 0;
             $(trId).each(function() {
                 newID++;
                 var trID = this.id;
                 var prevnum = parseInt(trID.match(/\d+/g), 10);
                 var newTR = trID.replace(prevnum, newID);
                 $("#" + trID).prop('id', newTR);
                 var tdID = $("#" + newTR + ' td');
                 $(tdID).each(function() {
                     var tdid = this.id;
                     var newTD = tdid.replace(prevnum, newID);
                     $("#" + tdid).prop('id', newTD);
                     $("#" + newTD).text(newID);
                 });
                 var inID = $("#" + newTR + ' td input');
                 $(inID).each(function() {
                     var inid = this.id;
                     var newIN = inid.replace(prevnum, newID);
                     $("#" + inid).prop('id', newIN);
                 });
                 var taID = $("#" + newTR + ' td textarea');
                 $(taID).each(function() {
                     var taid = this.id;
                     var newTA = taid.replace(prevnum, newID);
                     $("#" + taid).prop('id', newTA);
                 });
             });
             $("#budget_comps .number").trigger('change');
             $(".btn_remove_comps").prop('disabled', true);
         });

         $("#budget_comps").on('change', '.number', function() {
             var cnt_row = $('#budget_comps tr td[id^="num_"]').length;
             var cnt;
             var sale_ratio_total = deli_share_total = industry_share_total = saledeli_chg_total = industry_chg_total = market_chg_total = growth_pot_total = deli_product = deli_chg_product = indus_fproduct = indus_chg_fproduct = 0;
             for (cnt = 1; cnt <= cnt_row; cnt++) {
                 var sales_ratio_td = checkisNaN("#sales_ratio_td_" + cnt);
                 var deli_share_td = checkisNaN("#deli_share_td_" + cnt);
                 var saledeli_chg_td = checkisNaN("#deli_share_change_td_" + cnt);
                 var industry_share_td = checkisNaN("#industry_share_td_" + cnt);
                 var industry_chg_td = checkisNaN("#industry_chg_td_" + cnt);
                 var market_change_td = checkisNaN("#market_size_change_td_" + cnt);
                 var growth_pot_td = checkisNaN("#growth_pot_td_" + cnt);
                 sale_ratio_total += sales_ratio_td;
                 deli_share_total += deli_share_td * sales_ratio_td;
                 saledeli_chg_total += saledeli_chg_td * sales_ratio_td;
                 industry_share_total += industry_share_td * sales_ratio_td;
                 industry_chg_total += industry_chg_td * sales_ratio_td;
                 market_chg_total += market_change_td * sales_ratio_td;
                 growth_pot_total += growth_pot_td * sales_ratio_td;
                 $("#growth_pot_td_" + cnt).val(checkisNaNval(industry_chg_td + market_change_td + industry_chg_td * market_change_td, 1));
             }

             //data
             $("#sale_ratio_total").val(checkisNaNval(sale_ratio_total) + '%');
             $("#deli_share_total").val(checkisNaNval(deli_share_total / sale_ratio_total, 1) + '%');
             $("#saledeli_chg_total").val(checkisNaNval(saledeli_chg_total / sale_ratio_total, 1));
             $("#industry_share_total").val(checkisNaNval(industry_share_total / sale_ratio_total, 1) + '%');
             $("#industry_chg_total").val((checkisNaNval(industry_chg_total / sale_ratio_total) == -0) ? 0 : checkisNaNval(industry_chg_total / sale_ratio_total));
             $("#market_chg_total").val((checkisNaNval(market_chg_total / sale_ratio_total) == -0) ? 0 : checkisNaNval(market_chg_total / sale_ratio_total));
             $("#growth_pot_total").val((checkisNaNval(growth_pot_total / sale_ratio_total) == -0) ? 0 : checkisNaNval(growth_pot_total / sale_ratio_total));

             // grand_final
             deli_product = checkisNaNval((deli_share_total / sale_ratio_total) * 10 / 100);
             deli_chg_product = checkisNaNval(saledeli_chg_total / sale_ratio_total);
             indus_fproduct = checkisNaNval((industry_share_total / sale_ratio_total) * 10 / 100);
             indus_chg_fproduct = checkisNaNval(industry_chg_total / sale_ratio_total);

             deli_chg_product = maketri(deli_chg_product);
             indus_chg_fproduct = maketri(indus_chg_fproduct);
             $("#deli_product").val(deli_product);
             $("#deli_chg_product").val(deli_chg_product);
             $("#indus_fproduct").val(indus_fproduct);
             $("#indus_chg_fproduct").val(indus_chg_fproduct);

             // final
             var delivery = deli_product +" "+ deli_chg_product;
             var industry = indus_fproduct +" "+ indus_chg_fproduct;
             var potential = checkisNaNval(growth_pot_total / sale_ratio_total);
             potential = maketri(potential);
             $("#delivery").val(delivery);
             $("#industry").val(industry);
             $("#potential").val(potential);
             MakeNeg($("#budget_comps input.number"));
         });

         $("#budget_comps").on('click', 'input[type=checkbox]', function() {
             var numberOfChecked = $('#budget_comps input:checkbox:checked').length;
             if (numberOfChecked > 0) $(".btn_remove_comps").prop('disabled', false);
             else $(".btn_remove_comps").prop('disabled', true);
         });

         /* budget_hyokas */
         $(".btn_add_hyokas").on('click', function() {
             var tr = $('tr[id^="atr_"]:last');
             var prevnum = parseInt(tr.prop("id").match(/\d+/g), 10);
             var num = parseInt(tr.prop("id").match(/\d+/g), 10) + 1;

             $("table#budget_hyokas tbody tr#atr_" + prevnum).after(tr.clone().prop('id', 'atr_' + num).appendTo("table#budget_hyokas"));

             $('tr#atr_' + num + ' #td_textarea_' + prevnum).prop('id', 'td_textarea_' + num);
             $('tr#atr_' + num + ' #td_select_' + prevnum).prop('id', 'td_select_' + num);
             $('tr#atr_' + num).each(function() {
                 $(this).find('td input[type=checkbox]').each(function() {
                     var id = $(this).prop('id');
                     var ID = id.replace("checkhyoka_" + prevnum, "checkhyoka_" + num);
                     $(this).prop('id', ID);
                     $(this).prop('disabled', false);
                     $(this).attr('checked', false);
                 });
                 $(this).find('td textarea').each(function() {
                     var name = $(this).prop('name');
                     var Name = name.replace("[" + prevnum + "]", "[" + num + "]");
                     $(this).prop('name', Name);
                     $(this).prop('readonly', '');
                     $(this).removeClass('td_color');
                     $(this).attr('style', 'height: 108px;resize: none;');
                     $(this).text('');
                 });
                 $(this).find('td select').each(function() {
                     var name = $(this).prop('name');
                     var Name = name.replace("[" + prevnum + "]", "[" + num + "]");
                     $(this).prop('name', Name);
                     $(this).val('0');
                 });
             });
             $("table#budget_hyokas tr#atr_" + num + " td #td_textarea_" + num).val('');
         })

         $(".btn_remove_hyokas").on('click', function() {
             $("#budget_hyokas input[type=checkbox]").each(function() {
                 if ($(this).is(':checked')) {
                     var id = this.id;
                     var num = this.id.split('_')[1];
                     $("#atr_" + num).remove();
                 }
             });
             var newnum = 0;
             $("#budget_hyokas tr[id^=atr_]").each(function() {
                 newnum++;
                 var tr = this.id;
                 var prevnum = parseInt(tr.match(/\d+/g), 10);
                 var newtr = tr.replace('atr_' + prevnum, 'atr_' + newnum);
                 $("#" + tr).prop('id', newtr);

                 $(this).find('td input[type=checkbox]').each(function() {
                     var chkbox = $(this).prop('id');
                     var newchk = chkbox.replace('checkhyoka_' + prevnum, 'checkhyoka_' + newnum);
                     $(this).prop('id', newchk);
                 });

                 $(this).find('td textarea').each(function() {
                     var txtid = $(this).prop('id');
                     var newtxt = txtid.replace('td_textarea_' + prevnum, 'td_textarea_' + newnum);
                     $(this).prop('id', newtxt);

                     var txtname = $(this).prop('name');
                     var newtxtname = txtname.replace('[' + prevnum + ']', '[' + newnum + ']');
                     $(this).prop('name', newtxtname);
                 });

                 $(this).find('td select').each(function() {
                     var selid = $(this).prop('id');
                     var newsel = selid.replace('td_select_' + prevnum, 'td_select_' + newnum);
                     $(this).prop('id', newsel);

                     var selname = $(this).prop('name');
                     var newselname = selname.replace('[' + prevnum + ']', '[' + newnum + ']');
                     $(this).prop('name', newselname);
                 });

             });
             $(".btn_remove_hyokas").prop('disabled', true);
         });

         /* when checked the checkbox, deletion btn enabled */
         $("#budget_hyokas").on('click', 'input[type=checkbox]', function() {
             var numberOfChecked = $('#budget_hyokas input:checkbox:checked').length;
             if (numberOfChecked > 0) $(".btn_remove_hyokas").prop('disabled', false);
             else $(".btn_remove_hyokas").prop('disabled', true);
         });

        /* check percent total in settlement table process */
        $(".settleClass").on('change', 'input', function() {
            $("#error").empty();
            $("#success").empty();
            var settleVal = $(this).val();
            var trid = $(this).closest('tr')[0]['id'];
            var splitId = trid.split("_");
            var firstId = splitId[0];var secId = splitId[1];var thirdId = splitId[2];
            var total = 0;
            for (var i = 1; i < 4; i++) {
                var prepareId = firstId+"_"+secId+"_"+i;
                total += parseFloat($("tr#"+prepareId+" input").val());
            }
            if(secId == '25') {
                var title = '回収条件';

            }else {
                var title = '支払条件';
            }
            if(total > 100) {
                $(this).val(0);
                 $("#error").html(errMsg(commonMsg.JSE100,['%'])).show();
                scrollText();
                return false;
            }else {
                return true;
            }  
        });

        /* type only one decimal place */
        for(let cr = 1; cr <= 3; cr++){
            $("#com_ratio_25_"+cr).on('keyup', handleKeyUp);
            $("#com_ratio_27_"+cr).on('keyup', handleKeyUp);
            $("#com_ratio_25_"+cr).on('keypress', handleKeyPress);
            $("#com_ratio_27_"+cr).on('keypress', handleKeyPress);
        }
        function handleKeyUp(e) {
            this.value = this.value
            .replace(/[^\d.]/g, '')             // numbers and decimals only
            .replace(/(\..*)\./g, '$1')         // decimal can't exist more than once
            .replace(/(\.[\d]{1})./g, '$1');    // not more than 1 digit after decimal
        }
        function handleKeyPress(e) {
            this.value = this.value
            .replace(/[^\d.]/g, '')             // numbers and decimals only
            .replace(/(\..*)\./g, '$1')         // decimal can't exist more than once
            .replace(/(\.[\d]{1})./g, '$1');    // not more than 1 digit after decimal
        }
         /* paste process */
         /*$("#budgets").on('paste', '.number', function(e){
         	var $this = $(this);
         	$.each(e.originalEvent.clipboardData.items, function(i, v){
         		if (v.type === 'text/plain'){
         			v.getAsString(function(text){
         				var x = $this.closest('td').index(),
         				y = $this.closest('tr').index(),
         				obj = {};
         				$.each(text.split('\r\n'), function(i2, v2){//row
         					if(v2 != "") {
         						$.each(v2.split('\t'), function(i3, v3){//col
         							var row = y+i2, col = x+i3;
         							v3 = (v3 === "") ? 0 : v3.replace(/,/g, '');

         							$this.closest('table tbody').find('tr:eq('+row+') td.acc_type_one:eq('+col+') input[type=""]').val(v3).trigger('change');
         							$this.closest('table tbody').find('tr:eq('+row+') td:eq('+col+') input[type="hidden"]').val(v3)	;
         						});
         					}
         				});
         										
         			});
         		}
         	});
         	return false; 
         });*/

         /* selection process */
         $("#btn_selection").click(function() {
             setLayer();
         });
         var settlement_datas = []; /* for settlement table */
         var emp_datas = []; /* for employee table */
         /* save process */
         $("#btn_save, #btn_approve").click(function() {
            var btn_name = this.id;
            document.getElementById("error").innerHTML = "";
            document.getElementById("success").innerHTML = "";
            var ret_flag = showErrorMsg("#budget_hyokas", "td textarea", errMsg(commonMsg.JSE001,['分野・領域']));
            if(ret_flag) {
                var ids = <?php echo json_encode($_SESSION['SELECT_IDS']); ?>;
                var lastSelected = '';
                $.each(ids, function(key, value) {
                 var id = value.split('/')[0];
                 var selected = $("#" + id + " :selected").val();
                 if (selected.indexOf("-----") == -1 && selected != "0") {
                     lastSelected = selected;
                 }
                });
                var target_year = $("#current_year").val();
                var fun_name = 'SaveApproveBudget';
                var input_locked = '<?php echo $input_locked; ?>';

                $.ajax({
                 type: 'post',
                 url: "<?php echo $this->webroot; ?>BudgetResult/MergeOrOverwrite",
                 data: {
                     target_year: target_year,
                     lastSelected: lastSelected
                 },
                 dataType: 'json',
                 success: function(result) {
                     $("#btn_name").val(btn_name);
                     if (btn_name == 'btn_approve' && result == 0) {
                         // var content = '<?php echo __("データの入力を完了してもよろしいですか?"); ?>';
                         var firstText = '<?php echo __("はい"); ?>';
                         var secText = '<?php echo __("いいえ"); ?>';
                         var checked = false;
                         var btnClass1 = 'btn-info';
                         var btnClass2 = 'btn-default';
                     } else if (result == 0 || input_locked == 'locked') {
                         // var content = '<?php echo __("データを保存してよろしいですか。"); ?>';
                         var firstText = '<?php echo __("はい"); ?>';
                         var secText = '<?php echo __("いいえ"); ?>';
                         var checked = false;
                         var btnClass1 = 'btn-info';
                         var btnClass2 = 'btn-default';
                     } else if (result > 0) {
                         // var content = '<?php echo __("データはすでに保存されています！ 上書きしますか、それとも結合しますか?"); ?>';
                         // var content = '<?php echo __("データを保存してよろしいですか。"); ?>';
                         // var firstText = '<?php echo __("Overwrite"); ?>';
                         var firstText = '<?php echo __("はい"); ?>';
                         var secText = '<?php echo __("いいえ"); ?>';
                         var checked = true;
                         var btnClass1 = 'btn-info';
                         var btnClass2 = 'btn-default';
                     }
                     if (btn_name == 'btn_approve') {
                         var content = '<?php echo __("データの入力を完了してもよろしいですか?"); ?>';
                     } else {
                         var content = '<?php echo __("データを保存してよろしいですか。"); ?>';
                     }
                     $("#budgets_total tr[id^=settlement_]").each(function() {
                         var current_year = '<?php echo $current_year; ?>';
                         var trId = this.id;
                         var txtarea = $("#" + trId + " textarea").val();
                         var input = $("#" + trId + " input").val();
                         var trno = trId.replace('settlement_', '');
                         var setCollect = trno + '/' + txtarea + '/' + input;
                         settlement_datas.push(setCollect);
                     });
                     var setJSONString = JSON.stringify(settlement_datas);
                     $('#hid_settlement').val(setJSONString);

                     $("#budgets_total .employee_td input").each(function() {
                         var curId = this.id;
                         var splitId = this.id.split('_');
                         var year = splitId[0];
                         var emp_position = splitId[1];
                         var emp_count = $("#" + curId).val();
                         var empCollect = year + '/' + emp_position + '/' + emp_count;
                         emp_datas.push(empCollect);
                     });
                     var empJSONString = JSON.stringify(emp_datas);
                     $('#hid_employee').val(empJSONString);

                     $('#json_data').val();
                     $.confirm({
                         title: '<?php echo __("保存確認"); ?>',
                         icon: 'fas fa-exclamation-circle',
                         type: 'green',
                         typeAnimated: true,
                         closeIcon: true,
                         columnClass: 'medium',
                         animateFromElement: true,
                         animation: 'top',
                         draggable: false,
                         content: content,
                         buttons: {
                             ok: { //overwrite                        
                                 text: firstText,
                                 btnClass: btnClass1,
                                 action: function() {
                                     /*$('#process').val("overewrite");
                                     var myJSONString = JSON.stringify(tmp_save_datas_overwrite);
                                     $('#json_data').val(myJSONString);
                                     loadingPic();                              
                                     gotoFunction(fun_name);*/
                                     if (checked) {
                                         $('#process').val("merge");
                                         loadingPic();
                                         var myJSONString = JSON.stringify(tmp_save_datas_merge);
                                         $('#json_data').val(myJSONString);
                                         gotoFunction(fun_name);
                                     } else {
                                         $('#process').val("overewrite");
                                         var myJSONString = JSON.stringify(tmp_save_datas_overwrite);
                                         $('#json_data').val(myJSONString);
                                         loadingPic();
                                         gotoFunction(fun_name);
                                     }
                                 }
                             },
                             cancel: { //merge                               
                                 text: secText,
                                 btnClass: btnClass2,
                                 action: function() {
                                     /*if(checked) {
                                     	$('#process').val("merge");
                                     	loadingPic();
                                     	var myJSONString = JSON.stringify(tmp_save_datas_merge);
                                     	$('#json_data').val(myJSONString);
                                     	gotoFunction(fun_name);
                                     }else {
                                     	console.log('User clicked cancel');
                                     	scrollText();
                                     }*/
                                     console.log('User clicked cancel');
                                     scrollText();
                                 }
                             }
                         },
                         theme: 'material',
                         animation: 'rotateYR',
                         closeAnimation: 'rotateXR'
                     });
                 },
                 error: function(e) {
                     console.log('Something wrong! Please refresh the page.');
                 }
                });
                scrollText();
            }
         });

         /* approve process */
         $("#btn_app_cancel").click(function() {
             document.getElementById("error").innerHTML = "";
             document.getElementById("success").innerHTML = "";
             $.confirm({
                 title: '<?php echo __("承認キャンセル確認"); ?>',
                 icon: 'fas fa-exclamation-circle',
                 type: 'green',
                 typeAnimated: true,
                 closeIcon: true,
                 columnClass: 'medium',
                 animateFromElement: true,
                 animation: 'top',
                 draggable: false,
                 content: "<?php echo __("入力が完了したデータをキャンセルしてもよろしいですか？"); ?>",
                 buttons: {
                     ok: {
                         text: '<?php echo __("はい"); ?>',
                         btnClass: 'btn-info',
                         action: function() {
                             loadingPic();
                             gotoFunction("AppCancelBudget");
                         }
                     },
                     cancel: {
                         text: '<?php echo __("いいえ"); ?>',
                         btnClass: 'btn-default',
                         cancel: function() {
                             console.log('User clicked cancel');
                             scrollText();
                         }

                     }
                 },
                 theme: 'material',
                 animation: 'rotateYR',
                 closeAnimation: 'rotateXR'
             });
             scrollText();
         });

         /* download process */
         $("#btn_download").click(function() {
            gotoFunction("DownloadBudget");
            //onDownloadHandler();
         });
     });

     function showErrorMsg(table, find, error_msg) {
        var ret_flag = true;
        $(table).find(find).each(function() {
            var id = $(this).prop('id');
            if(id != '') {
                var txt = $("#"+id).val();console.log(id+" = "+txt);
                if(txt == '') {
                    ret_flag = false;
                    $("#error").html(error_msg).show();
                    scrollText();
                    return ret_flag;
                }
            }
        });
        return ret_flag;
     }
     function gotoFunction(fun_name) {

        // loadingPic();
        // document.forms[0].action = "<?php echo $this->webroot; ?>BudgetResult/"+fun_name;
		// document.forms[0].method = "POST";
		// document.forms[0].submit();    
        // return true;
        
        loadingPic(); // Show loading image before the API call
        if(fun_name != "DownloadBudget") {
            document.forms[0].action = "<?php echo $this->webroot; ?>BudgetResult/"+fun_name;
            document.forms[0].method = "POST";
            document.forms[0].submit();    
        
        }else {
         let fileName = 'ビジネス総合分析表_.xlsx';

         fetch("<?php echo $this->webroot; ?>BudgetResult/"+fun_name, {
                 method: 'POST',
                 body: new FormData(document.forms[0]),
             })
             .then(response => {
                // console.log('response =>',response);return;
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

                 $("#overlay").hide(); // Hide loading image after API call is finished
             })
             .catch(error => {
                 $("#overlay").hide(); // Hide loading image in case of an error
                 console.error('Error during fetch operation:', error.message);
             });
        }
         return true;
        
        }

     function setLayer() {
         var ids = <?php echo json_encode($_SESSION['SELECT_IDS']); ?>;
         var selectedTxt = [];
         $.each(ids, function(key, value) {
             var id = value.split('/')[0];
             var selectedtxt = $("#" + id + " :selected").text();
             if (selectedtxt.indexOf("-----") == -1) {
                 selectedTxt.push(selectedtxt.replace(/\//g, '_'));
             }
         });
         $("#selected_txt").val(selectedTxt[selectedTxt.length - 1]);
         checkTDinput(<?php echo json_encode($select_ids); ?>);
         gotoFunction("index");
     }

     function layerList(selId, selectedVal, selectedTxt, selectedList, pair_id) {
         $.ajax({
             type: 'post',
             url: "<?php echo $this->webroot; ?>BudgetResult/FilterLayerList",
             data: {
                 selId: selId,
                 selectedVal: selectedVal,
                 selectedTxt: selectedTxt,
                 selectedList: selectedList
             },
             dataType: 'json',
             success: function(data_list) {
                 var layer_type_list = <?php echo json_encode(array_values($_SESSION['SELECT_IDS'])); ?>;
                 var layer_gp_list = data_list;
                 var form = item_1 = item_2 = '';
                 if (selectedVal == "0") {
                     /* if choose value = 0, no clear parent datas */
                     $.each(layer_type_list, function(a, b) {
                         var name = b.split("/")[0];
                         if (name == selId) {
                             var prevKey = layer_type_list[a - 1];
                             var nextKey = layer_type_list[a + 1];

                             if (prevKey != undefined) {
                                 var lastSelectedVal = $('#' + prevKey.split("/")[0] + ' option:selected').val();
                                 var layers_name = (layer_gp_list[prevKey][lastSelectedVal]).split("_/_");
                                 form = layers_name[2];
                                 item_1 = layers_name[3];
                                 item_2 = layers_name[4];
                             }
                             /* if havn't child data, clear old datas from dropdown */
                             if (nextKey != undefined) {
                                 var ids = nextKey.split("/")[0];
                                 $('#' + ids + ' option[value!="0"]').remove();
                             }
                         }
                     });
                 } else {
                     var index = jQuery.inArray(pair_id, layer_type_list);
                     for (var i = index; i < layer_type_list.length; i++) {
                         var keyWithSelect = layer_type_list[i];
                         var ids = keyWithSelect.split("/")[0];
                         if (i > index) {
                             $('#' + ids + ' option[value!="0"]').remove();
                         }
                         var layerArr = layer_gp_list[keyWithSelect];
                         if (layerArr != undefined) {
                             $.each(layerArr, function(codes, layers) {
                                 var layers_name = layers.split('_/_');
                                 if (ids != selId) {
                                     layers_name.length = 2;
                                    //  $('#' + ids).append($('<option value="' + codes + '">' + layers_name.join("/") + '</option>'));
                                     $('#' + ids).append($('<option value="' + codes + '">' + layers_name[1] + '</option>'));
                                 } else {
                                     if (selectedVal == codes) {
                                         form = layers_name[2];
                                         item_1 = layers_name[3];
                                         item_2 = layers_name[4];
                                     }
                                 }
                             });
                         } else {
                             $('#' + ids + ' option[value!="0"]').remove();
                         }
                     }
                 }
                 $("#form").val(form);
                 $("#item_1").val(item_1);
                 $("#item_2").val(item_2);
             },
             error: function(e) {
                 console.log('Something wrong! Please refresh the page.');
             }
         });
     }

     function calculatedBudget(year, formula, currId) {

         let limitYear = <?php echo Setting::LIMIT_YEAR; ?>;
         $.each(formula, function(id, data_formula) {
             var postfix = $("#" + year + "_" + id).attr('data-percent');
             var calfor = $("#" + year + "_" + id).attr('data-formula');
             var edited = $("#" + year + "_" + id).attr('data');
             var acc_name = $("#" + year + "_" + id).attr('data-account');
             //percent col amt * 100
             var percent = (edited != "2_1" && postfix != '' && postfix != undefined && (calfor != undefined || calfor != '')) ? '*100' : '*1';
             var addp = (postfix != '' && postfix != undefined && (calfor == undefined || calfor == '')) ? '/100' : '/1';
             //calculation account formula using eval()
             var calculated_amt = (isNaN(eval(data_formula)) || eval(data_formula) == Infinity || eval(data_formula) == -Infinity) ? 0 : (eval(data_formula + percent + addp));
             var adj = 1;
             if (addp == '/100') {
                 var checking_nohid = $("#budgets #" + year + "_" + id).val().replace('%', '');
                 if ((calculated_amt * 100) == checking_nohid) {
                     addp = '/1';
                     adj = 100;
                 }
             }
             $("#budgets #hid_" + year + "_" + id).val((year < limitYear) ? $("#budgets #hid_" + year + "_" + id).val() : eval(calculated_amt + addp));

             if(postfix != "" || acc_name.indexOf('ヶ月') !== -1) {
                var decimal = 1;
             }else {
                var decimal = '';
             }
             
             $("#budgets #" + year + "_" + id).val((year < limitYear) ? parseFloat($("#budgets #hid_" + year + "_" + id).val()).toFixed(decimal).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + postfix : (calculated_amt * adj).toFixed(decimal).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + postfix);
             //calculation for budget total table
             // MakeNeg($("#budgets #"+year+"_"+id), "#budgets_total ");
             MakeNeg($("#budgets #" + year + "_" + id));
         });
     }

     function calculatedFactor(year, formula, currId) {
         $.each(formula, function(id, data_formula) {
             var postfix = $("#" + year + "_" + id).attr('data-percent');
             var calfor = $("#" + year + "_" + id).attr('factor-formula');
             var acc_id = '"' + id + '"';

             //percent col amt * 100
             var percent = (calfor != acc_id && postfix != '' && postfix != undefined) ? '*100' : '*1';
             // decimal point -if % 1 point, if not non decimal
             var decimal = (postfix != '' && postfix != undefined) ? 1 : 0;

             //calculation account formula using eval()
             var calculated_amt = (isNaN(eval(data_formula)) || eval(data_formula) == Infinity || eval(data_formula) == -Infinity) ? 0 : (eval(data_formula + percent));
             $("#budgets_total #hid_" + year + "_" + id).val(calculated_amt);
             var result = calculated_amt.toFixed(decimal).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + postfix;

             $("#budgets_total #" + year + "_" + id).val(result);
             MakeNeg($("#budgets_total #" + year + "_" + id), "#budgets_total ");
         });
     }

     function saleTotal(tot_sales_formula) {
         $.each(tot_sales_formula, function(ids, formula) {
             var results = checkisNaNval(eval(formula), 1);
             $('<input>').attr({
                 type: 'hidden',
                 id: ids,
                 name: ids,
                 value: results
             }).appendTo('#budgets_total');
             $("#" + ids).val(results);
         });
         MakeNeg($(".tot_sale_per_person input.number"));
     }

     function PersonnelExpenses(item, lastId) {
         var years = <?php echo json_encode(array_values($yr_list)); ?>;
         var limitYear = <?php echo Setting::LIMIT_YEAR; ?>;
         if (lastId == item && eval(lastId) != '0') {
             years.forEach((yr, index) => {
                 $("input.expense_class").prop('readonly', false);
             });
         } else {
             years.forEach((yr, index) => {
                 $("input.expense_class").prop('readonly', true);
                 if (yr < limitYear) $("input.expense_class").prop('readonly', false);
             });
         }
     }

     function makingNegativeReady() {
         MakeNeg($("#budgets input.number"), "#budgets_total ");
         MakeNeg($("#budgets_total .employee_sales input.number"));
         MakeNeg($("#budget_sngs input.number"));
         MakeNeg($("#budget_comps input.number"));
     }

     function MakeNeg(input, table = '') {
         for (var i = 0; i < input.length; i++) {
             var id = "#" + input[i]['id'];
             var percent = $(id).attr('data-percent');
             var val = $(id).val().replace(/,/g, '');
             val = (percent != '') ? checkisNaNval(val, 1) : checkisNaNval(val);
             if (val.indexOf('-') != -1) {
                 $(id).addClass("negative");
             } else {
                 $(id).removeClass("negative");
             }
             if (table != '') {
                 var fVal = $(table + id).val();
                 if (fVal != undefined) {
                     var fVal = $(table + id).val().replace(/,/g, '');
                     var fval = fVal.replace(/%/g, '');
                     var hidfVal = fval;
                     if (id.indexOf('#hid_') != -1) {
                         $(table + id).val(hidfVal);
                     } else {
                         if (fval.indexOf('-') != -1) {
                            //  $(table + id).val(fval.replace(fval, '(' + (fval.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')).replace('-', '') + percent + ')'));
                             $(table + id).addClass("negative");
                         } else {
                             fval = fval.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                             $(table + id).val(fval + percent);
                             $(table + id).removeClass("negative");
                         }
                     }
                 }
             }
         }
     }

     function checkTDinput(select_ids) {
         var limitYear = <?php echo Setting::LIMIT_YEAR; ?>;
         var curYear = <?php echo json_encode($current_year); ?>;
         var yearList = <?php echo json_encode($yr_list); ?>;
         var input_locked = <?php echo json_encode($input_locked); ?>;
         var form_disabled = <?php echo json_encode($form_disabled); ?>;

         selectVal = [];
         var lastId = select_ids[select_ids.length - 1]; /* sub_business layer */
         select_ids.forEach((item, index) => {
             if (selectVal.length < 1 && eval(item) != '0') {
                 selectVal.push(item);
             }
             /*if(selectVal.length > 0) {
				$("table#budgets input[type='']").prop('readonly', true);
				$(".btn_remove_comps, .btn_remove_hyokas, #checkbox_1, #checkhyoka_1, #btn_approve").prop('disabled', true);
				$("#tax_exchange input, table tr td textarea, .hyoka_select, #csr_record, .btn_add_comps, .btn_add_hyokas").prop('disabled', false);
				$("table .acc_type_one input").prop('readonly', false);
				$('.acc_type_one').css('background-color', '#FFFFCC');
				$('td.l_yr_cls').css('background-color', '#FFFFCC');
				// /* if sub_business layer is selected, '人件費　(ﾋﾞｼﾞﾈｽ別人員表）' will input 
				// PersonnelExpenses(item, lastId);
    			
			}*/
             /* '人件費　(ﾋﾞｼﾞﾈｽ別人員表）' must be disabled input under any circumstances. */
             // $("input.expense_class").prop('disabled', true);
         });
         $(".btn_remove_comps, .btn_remove_hyokas, #checkbox_1").prop('disabled', true);
         for (var i = 1; i <= 5; i++) {
            $("#checkhyoka_"+i).prop('disabled', true);
         }
         var hyoka_btn = '<?php echo $show_hyoka_btn ?>';
         
         if(hyoka_btn) {
            $(".btn_add_hyokas, .btn_remove_hyokas").show();
        }else {
            $(".btn_add_hyokas, .btn_remove_hyokas").hide();
        }
         
         if (form_disabled == 'form_disabled') {
             $("table#budgets input[type=''], td textarea, td.acc_type_one input, td select, .btn_add_comps, .btn_add_hyokas").prop('disabled', true);
             $("table#budgets input[type=''], td textarea, td.acc_type_one input, td select").addClass('form_disabled');
             $('.acc_type_one, td.l_yr_cls').css('background-color', '#eee');
         } else if (input_locked == 'locked') {
             $("table#budgets input[type='']").prop('disabled', true);
             $('.acc_type_one').css('background-color', '#FFFFFF');
             $('td.l_yr_cls').css('background-color', '#FFFFFF');
         } else {
             $("table#budgets input[type='']").prop('disabled', false);
             $('.acc_type_one').css('background-color', '#FFFFCC');
             $('td.l_yr_cls').css('background-color', '#FFFFCC');
             /* sub business layer can edit - 18-09-2023 */
             if (eval(lastId) != '0') {
                 $("input.expense_class").prop('readonly', false);
             }
         }
         $.each(yearList, function(key, yr) {
             if (yr < limitYear) {
                 var id = yr + '_';
                 $("table#budgets input[id^='" + id + "']").prop('readonly', false);
             }
         });
         $("input.interest_costs").prop('readonly', false);
         $("input.interest_costs").prop('disabled', true);
         $('.interest_costs').css('background-color', '#FFFFFF');

     }

     function maketri(number) {
         if (number.indexOf('-') == 0) {
             var num = number.replace('-', '▲');
         } else {
             var num = '△' + number;
         }
         return num;
     }

     function checkisNaN(id) {
         var val = $(id).val().replace(/,/g, "").replace(/\%+$/g, '');
         var chk = isNaN(parseFloat(val)) ? 0 : parseFloat(val);

         return Math.round(chk * 10000) / 10000;
     }

     function checkisNaNval(value, decimal = '') {
         var chk = isNaN(parseFloat(value)) ? 0 : ((parseFloat(value) == Number.NEGATIVE_INFINITY) ? 0 : ((parseFloat(value) == Number.POSITIVE_INFINITY) ? 0 : parseFloat(value)));

         return (Math.round(chk * 10000) / 10000).toFixed(decimal);
     }

     function ROUND(num, decimals) {
         var result = round(num, decimals);
         if (Math.sign(num) == -1) {
             var num = num * (-1);
             var result = round(num, decimals) * (-1);
         }

         return result;
     }

     function scrollText() {
         var tes = $('#error').text();
         var tes1 = $('.success').text();
         if (tes) $("html, body").animate({
             scrollTop: 0
         }, "slow");
         if (tes1) $("html, body").animate({
             scrollTop: 0
         }, "slow");
     }
     /*  
      *	Show hide loading overlay
      *	@Zeyar Min  
      */
     function loadingPic() {
         $("#overlay").show();
         $('.jconfirm').hide();
     }
 </script>
 <div id="overlay">
     <span class="loader"></span>
 </div>
 <div id="load"></div>
 <div id="contents"></div>
 <div class="content register_container" style="padding: 20px;font-size: 1em !important;">
     <div class="row">
         <div class="col-md-12 col-sm-12 heading_line_title">
             <h3><?php echo __("ビジネス総合分析表"); ?></h3>
             <hr>
         </div>
         <!-- start show error msg and success msg from controller  -->
         <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
             <div class="success" id="success"><?php echo ($this->Session->check("Message.BRSuccess")) ? $this->Flash->render("BRSuccess") : ''; ?></div>
             <div class="error" id="error"><?php echo ($this->Session->check("Message.BRError")) ? $this->Flash->render("BRError") : ''; ?></div>
             <!-- end show error and success msg from controller -->
             <input type="hidden" name="current_year" id="current_year" value=<?php echo $current_year; ?>>
             <input type="hidden" name="selected_txt" id="selected_txt" value="<?php echo $selectedTxt; ?>">
             <input type="hidden" name="input_locked" value="<?php echo $input_locked; ?>">
             <input type="hidden" name="json_data" id="json_data">
             <input type="hidden" name="btn_name" id="btn_name">
             <input type="hidden" name="target_year" value="<?php echo $current_year; ?>">
             <input type="hidden" name="process" id="process">
             <input type="hidden" name="hid_settlement" id="hid_settlement">
             <input type="hidden" name="hid_employee" id="hid_employee">
             <div class="row">
                 <div class="form-group row">
                     <div class="col-sm-3">
                         <input type="" name="" class="form-control" value="<?php echo $_SESSION['TERM_NAME']; ?>" readonly>
                     </div>
                 </div>
             </div>
             <div class="row">
                 <div class="form-group row">
                     <div class="col-sm-3">
                         <input type="" name="" class="form-control" value="<?php echo $current_year . ' ' . __('Year'); ?>" readonly>
                     </div>

                     <?php
                        $upper_layer = array_keys($layers_list)[0];
                        $second_layer = array_keys($layers_list)[1];

                        $first_two_layer[$upper_layer] = $layers_list[$upper_layer];
                        $first_two_layer[$second_layer] = $layers_list[$second_layer];

                        $first_box = explode('/', array_keys($first_two_layer)[0])[0];
                        foreach ($first_two_layer as $layers => $layer_gp_list) {
                            $ids = explode('/', $layers)[0];
                            $layers_name = explode('/', $layers)[1];
                        ?>
                         <div class="col-sm-3">
                             <select id="<?php echo $ids; ?>" name="sel_name[]" class="form-control selectClass">
                                 <?php if ($ids != $first_box) {
                                        $layer_gp_list = $layers_list[$layers];
                                    ?>
                                     <option value="0"><?php echo "----- " . __($layers_name) . " " . __("名") . " -----"; ?></option>
                                 <?php } else {
                                        $layer_gp_list = $layers_list[$layers];
                                    }

                                    foreach ($layer_gp_list as $layer_code => $layer_gp_name) {

                                        $ar = array_slice(explode("_/_", $layer_gp_name), 0, 2);
                                        $layer_gp_name = $ar[1];
                                    ?>
                                     <option value='<?php echo $layer_code; ?>' <?php if (in_array($layer_code, $layerlist)) { ?> selected <?php } ?>><?php echo __($layer_gp_name); ?></option>
                                 <?php } ?>
                             </select>
                         </div>
                     <?php  } ?>
                 </div>
             </div>
         </div>
         <?php if (!$returnMsg['error']) { ?>
             <div class="form-group row">
                 <?php
                    unset($layers_list[$upper_layer]);
                    unset($layers_list[$second_layer]);
                    $layer_cnt = 2; #start gp layer
                    foreach ($layers_list as $layers => $layer_gp_list) {
                        $layer_cnt++;
                        $ids = explode('/', $layers)[0];
                        $layers_name = explode('/', $layers)[1];
                    ?>
                     <div class="col-sm-3">
                         <select id="<?php echo $ids; ?>" name="sel_name[]" class="form-control selectClass">
                             <?php if ($ids != $first_box) {
                                    $layer_gp_list = $layers_list[$layers];
                                ?>
                                 <option value="0"><?php echo "----- " . __($layers_name) . " " . __("名") . " -----"; ?></option>
                             <?php } else {
                                    $layer_gp_list = $layers_list[$layers];
                                }

                                foreach ($layer_gp_list as $layer_code => $layer_gp_name) {

                                    $ar = array_slice(explode("_/_", $layer_gp_name), 0, 2);
                                    $layer_gp_name = $ar[1];
                                ?>
                                 <option value='<?php echo $layer_code; ?>' <?php if (in_array($layer_code, $layerlist)) { ?> selected <?php } ?>><?php echo __($layer_gp_name); ?></option>
                             <?php } ?>
                         </select>
                     </div>
                 <?php  } ?>
             </div>
             <div class="form-group row">
                 <div class="col-sm-3">
                     <input type="" name="form" id="form" class="form-control txt_disa" value="<?php if (!empty($form)) echo $form; ?>" placeholder="形態" readonly>
                 </div>
                 <div class="col-sm-3">
                     <input type="" name="item_1" id="item_1" class="form-control txt_disa" value="<?php if (!empty($item_1)) echo $item_1; ?>" placeholder="内訳① (販売先 or 商品）" readonly>
                 </div>
                 <div class="col-sm-3">
                     <input type="" name="item_2" id="item_2" class="form-control txt_disa" value="<?php if (!empty($item_2)) echo $item_2; ?>" placeholder="内訳② (商品 or 販売先）" readonly>
                 </div>
                 <div class="col-sm-3">
                     <input type="button" name="" class="btn btn-success txt_disa" id="btn_selection" value="<?php echo __('設定選択'); ?>">
                 </div>
             </div>
             <!-- tax_exchange -->
             <div class="row">
                 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8" style="margin-top:20px; margin-bottom: 30px;">
                     <div class="table-wrapper scrollable">
                         <table class="table-bordered table-fixed bu_analysis " id="tax_exchange">
                             <thead class="">
                                 <tr>
                                     <th style="width: 220px;" class="blank-cell"></th>
                                     <?php foreach ($yr_list as $head => $yrs) { ?>
                                         <th style="<?php if ($head == 'hidden_title') echo 'display:  none;' ?>"><?php echo $head; ?></th>
                                     <?php } ?>
                                     <th class="blank-cell"></th>
                                 </tr>
                                 <tr>
                                     <th class="blank-cell"></th>
                                     <?php foreach ($yr_list as $head => $yrs) { ?>
                                         <th style="<?php if ($head == 'hidden_title') echo 'display:  none;' ?>"><?php echo $yrs; ?></th>
                                     <?php } ?>
                                     <th class="blank-cell"></th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <tr>
                                     <td style="text-align:left;padding-left: 5px;"><?php echo __("為替換算レート：○○円/US\$"); ?></td>
                                     <?php foreach ($exchanges as $exyr => $exchage) { ?>
                                         <?php $rex_name = ($exyr == $yr_list['hidden_title']) ? '' : 'rexchanges[' . $exyr . ']'; ?>
                                         <td style="<?php if ($exyr == $yr_list['hidden_title']) echo 'display:  none;' ?>"><input type="" disabled name="<?php echo $rex_name; ?>" class="form-control number change_td" id="<?php echo $exyr ?>_exchanges" value="<?php echo $exchage; ?>"></td>
                                     <?php } ?>
                                 </tr>
                                 <tr>
                                     <td style="text-align:left;padding-left: 5px;"><?php echo __("税率"); ?></td>
                                     <?php foreach ($tax_fees as $taxyr => $tax) { ?>
                                         <?php $rtx_name = ($taxyr == $yr_list['hidden_title']) ? '' : 'rtaxFees[' . $taxyr . ']'; ?>
                                         <td style="<?php if ($taxyr == $yr_list['hidden_title']) echo 'display:  none;' ?>"><input type="" disabled name="<?php echo $rtx_name; ?>" class="form-control number percent" id="<?php echo $taxyr ?>_Taxfees" value="<?php echo $tax . '%'; ?>"></td>
                                     <?php } ?>
                                 </tr>
                             </tbody>
                         </table>
                     </div>
                 </div>
                 <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4" style="margin-top:90px; ">
                     <div class="col-md-2"></div>
                     <div class="col-md-10" style="text-align: right; margin-left: 20%;">
                         <input type="button" class="btn btn-success btn_sumisho" id="btn_download" name="btn_save" value="<?php echo __('ダウンロード'); ?>">
                         <?php if (!$show_app_cancel_btn) { ?>
                             <input style="display: none;" type="button" class="btn btn-success btn_sumisho" id="btn_save" name="btn_save" value="<?php echo __('一時保存'); ?>" <?php echo "$disable_save"; ?>>
                             <input style="display: none;" type="button" class="btn btn-success btn_sumisho" id="btn_approve" name="btn_save" value="<?php echo __('入力確定'); ?>" <?php echo "$disable_save"; ?>>
                         <?php } else { ?>
                             <input style="display: none;" type="button" class="btn btn-success btn_sumisho" id="btn_app_cancel" name="btn_save" value="<?php echo __('入力解除'); ?>" <?php if ($cancel_disabled) echo "disabled";
                                                                                                                                                                                    else echo ""; ?>>
                         <?php } ?>
                     </div>
                 </div>
             </div>
             <!-- 単位：（百万円） -->
             <div class="row">
                 <div class="col-md-8"><span class="pull-left"><?php echo __('単位：（百万円）'); ?></span></div>
             </div>
             <!-- budgets -->
             <div class="row">
                 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8" style="margin-top:20px; margin-bottom: 30px;">

                     <label for="one"><?php echo __("【取引採算】"); ?></label>
                     <div class="tbl-wrappers freeze">
                         <table class="table-bordered table-fixed " id="budgets">
                             <thead class="">
                                 <tr>
                                     <th rowspan="2" width="30"><?php echo "No."; ?></th>
                                     <th rowspan="2" width="200" style="border-bottom: 0;"></th>
                                     <?php foreach ($yr_list as $head => $yrs) { ?>
                                         <th style="<?php if ($head == 'hidden_title') echo 'display: none;' ?>"><?php echo $head; ?></th>
                                     <?php } ?>
                                 </tr>
                                 <tr>
                                     <?php foreach ($yr_list as $head => $yrs) { ?>
                                         <th style="<?php if ($head == 'hidden_title') echo 'display: none;' ?>"><?php echo $yrs; ?></th>
                                     <?php } ?>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php $numbers = 0;
                                    foreach ($budget_accounts as $acc_type_name => $type_datas) {
                                        if ($acc_type_name != '【取引採算】') {
                                            if (strpos($acc_type_name, 'No Name') !== false)
                                                $acc_type_name = ''; ?>
                                         <tr>
                                             <td class="l_padding" colspan="8" style="border: 0;"><?php echo $acc_type_name; ?></td>
                                         </tr>
                                     <?php }
                                        foreach ($type_datas as $acc_name => $acc_datas) {
                                            /*$expense_class = ($acc_name == Setting::PERSONAL_EXPENSES || in_array($acc_name, Setting::INTEREST_COST)) ? 'expense_class' : '';*/
                                            $expense_class = '';
                                            if ($acc_name == Setting::PERSONAL_EXPENSES) {
                                                $expense_class = 'expense_class';
                                            } elseif (in_array($acc_name, Setting::INTEREST_COST)) {
                                                $expense_class = 'interest_costs';
                                            }
                                            $numbers++; ?>
                                         <tr>
                                             <td class="l_padding"><?php echo $numbers; ?></td>
                                             <td class="l_padding"><?php echo $acc_name; ?></td>
                                             <?php foreach ($acc_datas as $year => $datas) {
                                                    if ($year == $yr_list['hidden_title']) {
                                                        $non_display = 'display: none;';
                                                    } else {
                                                        $non_display = '';
                                                    }
                                                    $amount = (is_nan($datas['calculated_amt']) || is_infinite($datas['calculated_amt'])) ? 0 : $datas['calculated_amt'];
                                                    if ($datas['postfix'] != '') {
                                                        $percent = $datas['postfix'];
                                                        $pctClass = 'percent';
                                                        $addp = 100;
                                                    } else {
                                                        $percent = '';
                                                        $pctClass = '';
                                                        $addp = 1;
                                                    }
                                                    if ($datas['account_type'] == 2 && $addp == 100) {
                                                        $addp = 1;
                                                    }
                                                    if (in_array($acc_name, Setting::INTEREST_COST)) {
                                                        $addp = 100;
                                                    }
                                                    if ($datas['account_type'] == 2) {
                                                        $disabled = 'readonly';
                                                        $acc_class = '';
                                                    } else {
                                                        $disabled = '';
                                                        $acc_class = 'acc_type_one';
                                                    }

                                                    $input_id = $year . '_' . $datas['account_id'];
                                                    $input_name = ($year != $yr_list['hidden_title']) ? "budget[" . $year . "][" . $datas['account_id'] . "]" : "";
                                                    $data_formula = ($datas['formula'] != '') ? $datas['formula'] : '';
                                                    $data_percent = ($datas['postfix'] != '') ? $datas['postfix'] : '';

                                                    $l_yr_class = ($year <= $manual_budget_year) ? 'l_yr_cls' : '';
                                                    if($percent != '' || strpos($acc_name, 'ヶ月') !== false) {
                                                        $value = number_format($amount * $addp, 1) . $percent;
                                                        $data_account = $acc_name;
                                                    }else {
                                                        $value = number_format($amount * $addp);
                                                        $data_account = '';
                                                    }

                                                    if ($year <= $manual_budget_year) {
                                                        $amount = $datas['amount'];
                                                        
                                                        if($percent != '' || strpos($acc_name, 'ヶ月') !== false) {
                                                            $value = number_format($datas['amount'], 1) . $percent;
                                                        }else {
                                                            $value = number_format($datas['amount']);
                                                        }
                                                    }

                                                    $account_code = $datas['account_code'];

                                                    $acc_in_name = ($year != $yr_list['hidden_title']) ? "budgetAccount[" . $year . "][" . $datas['account_id'] . "]" : "";
                                                ?>
                                                 <td class="cal-td acc_amt <?php echo $acc_class; ?> <?php echo $l_yr_class; ?>" style="<?php echo $non_display; ?>background-color: #d5f4ff;">
                                                     <input class="form-control number <?php echo $pctClass; ?> <?php echo $expense_class ?>" type="" id="<?php echo $input_id; ?>" factor-formula='<?php echo $datas["factor_formula"] ?>' data-formula='<?php echo $data_formula; ?>' data-percent="<?php echo $data_percent ?>" data="<?php echo $datas['account_type'] . '_' . $datas['dbl_edit_flag'] ?>" data-account ="<?php echo $data_account ?>" value="<?php echo $value; ?>" <?php echo $disabled; ?>>
                                                     <input type="hidden" class="form-control number" id="hid_<?php echo $input_id . '_' . $account_code; ?>" name="<?php echo $acc_in_name; ?>" value="<?php echo $account_code; ?>">
                                                     <!-- hidden input -->
                                                     <input type="hidden" class="form-control number" id="hid_<?php echo $input_id; ?>" name="<?php echo $input_name; ?>" value="<?php echo $amount; ?>">
                                                 </td>

                                             <?php } ?>
                                             <td class="blank-cell" style="font-size: 10px !important;"><?php echo $datas['memo']; ?></td>
                                         </tr>
                                 <?php }
                                    } ?>
                             </tbody>
                         </table>
                     </div>
                 </div>

                 <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4" style="margin-top:15px; margin-bottom: 30px;">
                     <div class="tbl-wrappers freeze">
                         <table class="table-bordered table-fixed bu_analysis" id="budgets_total">
                             <thead class="">
                                 <tr>
                                     <th class="blank-cell"></th>
                                     <th style="font-size: 0.9em !important;" colspan="4" style="border-bottom: 0;"><?php echo "修正後（ファクタリング考慮後）" ?></th>
                                 </tr>
                                 <tr>
                                     <th class="blank-cell"></th>
                                     <?php foreach ($next_years as $head => $yrs) { ?>
                                         <th style="font-size: 0.9em !important;"><?php echo $head; ?></th>
                                     <?php } ?>
                                 </tr>
                                 <tr>
                                     <th class="blank-cell"></th>
                                     <?php foreach ($next_years as $head => $yrs) { ?>
                                         <th><?php echo $yrs; ?></th>
                                     <?php } ?>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php foreach ($budget_accounts as $acc_type_name => $type_datas) {
                                        if ($acc_type_name == '【取引採算】') { ?>
                                         <?php foreach ($type_datas as $acc_name => $acc_datas) {
                                            ?>
                                             <tr>
                                                 <td class="blank-cell"></td>
                                                 <?php foreach ($acc_datas as $year => $datas) {
                                                        if (in_array($year, $next_years)) {
                                                            $style = '';
                                                        } else {
                                                            $style = 'display: none;';
                                                        }
                                                        $amount = (is_nan($datas['factor_calculated_amt']) || is_infinite($datas['factor_calculated_amt'])) ? 0 : $datas['factor_calculated_amt'];

                                                    ?>
                                                     <td class="cal-td acc_amt" style="<?php echo $style; ?>">
                                                         <input factor-formula='<?php echo $datas["factor_formula"] ?>' class="form-control number" type="" name="" id="<?php echo $year . '_' . $datas['account_id']; ?>" value="<?php if ($datas['postfix'] != '') echo number_format($amount, 1) . $datas['postfix'];
                                                                                                                                                                                                                                    else echo number_format($amount); ?>" disabled>
                                                         <input type="hidden" id="hid_<?php echo $year . '_' . $datas['account_id']; ?>" value="<?php echo $amount; ?>">
                                                     </td>
                                                 <?php } ?>
                                             </tr>
                                         <?php }
                                        } elseif ($acc_type_name == '売上総利益成長率／対前年比') {
                                            $acc_type_name = ''; ?>
                                         <tr>
                                             <td colspan="4" style="border: 0;height: 80px;text-align:left;padding-left: 5px;"><?php echo $acc_type_name; ?></td>
                                         </tr>
                                         <?php /*$yr = date('y', strtotime($current_year)).'年10月末';*/
                                            $yr = substr($current_year, strlen($current_year) - 2) . '年8月末';
                                            ?>
                                         <tr>
                                             <td class="td_header"><?php // echo $yr; ?></td>
                                             <td colspan="3" class="td_header"><?php echo "決済条件"; ?></td>
                                             <td class="td_header"><?php echo "構成比率"; ?></td>
                                         </tr>
                                         <tr id="settlement_25_1" class="settleClass">
                                             <td rowspan="3" style="text-align: center;"><?php echo "回収条件→No.26"; ?></td>
                                             <td colspan="3" class="acc_type_one ">
                                                 <textarea style="height: 28px; " name="settlement[<?php echo $current_year ?>][25_1][settlement]" class="form-control"><?php echo $settlements['25_1']['sett_cmt']; ?></textarea>
                                             </td>
                                             <td class="acc_type_one settlement">
                                                 <input type="" id="com_ratio_25_1" name="settlement[<?php echo $current_year ?>][25_1][composition_ratio]" class="settlement_persent form-control percent number" value="<?php echo ($settlements['25_1']['composition_ratio'] == 0) ? '0%' : $settlements['25_1']['composition_ratio'] . '%'; ?>">
                                             </td>
                                         </tr>
                                         <tr id="settlement_25_2" class="settleClass">
                                             <td colspan="3" class="acc_type_one ">
                                                 <textarea style="height: 28px; " name="settlement[<?php echo $current_year ?>][25_2][settlement]" class="form-control "><?php echo $settlements['25_2']['sett_cmt']; ?></textarea>
                                             </td>
                                             <td class="acc_type_one settlement">
                                                 <input type="" id="com_ratio_25_2" name="settlement[<?php echo $current_year ?>][25_2][composition_ratio]" class="settlement_persent form-control percent number" value="<?php echo ($settlements['25_2']['composition_ratio'] == 0) ? '0%' : $settlements['25_2']['composition_ratio'] . '%'; ?>">
                                             </td>
                                         </tr>
                                         <tr id="settlement_25_3" class="settleClass">
                                             <td colspan="3" class="acc_type_one ">
                                                 <textarea style="height: 28px; " name="settlement[<?php echo $current_year ?>][25_3][settlement]" class="form-control"><?php echo $settlements['25_3']['sett_cmt']; ?></textarea>
                                             </td>
                                             <td class="acc_type_one settlement">
                                                 <input type="" id="com_ratio_25_3" name="settlement[<?php echo $current_year ?>][25_3][composition_ratio]" class="settlement_persent form-control percent number" value="<?php echo ($settlements['25_3']['composition_ratio'] == 0) ? '0%' : $settlements['25_3']['composition_ratio'] . '%'; ?>">
                                             </td>
                                         </tr>
                                         <tr id="settlement_27_1" class="settleClass">
                                             <td rowspan="3" style="text-align: center;"><?php echo "支払条件→No.28" ?></td>
                                             <td colspan="3" class="acc_type_one ">
                                                 <textarea style="height: 28px; " name="settlement[<?php echo $current_year ?>][27_1][settlement]" class="form-control"><?php echo $settlements['27_1']['sett_cmt']; ?></textarea>
                                             </td>
                                             <td class="acc_type_one settlement">
                                                 <input type="" id="com_ratio_27_1" name="settlement[<?php echo $current_year ?>][27_1][composition_ratio]" class="settlement_persent form-control percent number" value="<?php echo ($settlements['27_1']['composition_ratio'] == 0) ? '0%' : $settlements['27_1']['composition_ratio'] . '%'; ?>">
                                             </td>
                                         </tr>
                                         <tr id="settlement_27_2" class="settleClass">
                                             <td colspan="3" class="acc_type_one ">
                                                 <textarea style="height: 28px; " name="settlement[<?php echo $current_year ?>][27_2][settlement]" class="form-control"><?php echo $settlements['27_2']['sett_cmt']; ?></textarea>
                                             </td>
                                             <td class="acc_type_one settlement">
                                                 <input type="" id="com_ratio_27_2" name="settlement[<?php echo $current_year ?>][27_2][composition_ratio]" class="settlement_persent form-control percent number" value="<?php echo ($settlements['27_2']['composition_ratio'] == 0) ? '0%' : $settlements['27_2']['composition_ratio'] . '%'; ?>">
                                             </td>
                                         </tr>
                                         <tr id="settlement_27_3" class="settleClass">
                                             <td colspan="3" class="acc_type_one ">
                                                 <textarea style="height: 28px; " name="settlement[<?php echo $current_year ?>][27_3][settlement]" class="form-control"><?php echo $settlements['27_3']['sett_cmt']; ?></textarea>
                                             </td>
                                             <td class="acc_type_one settlement">
                                                 <input type="" id="com_ratio_27_3" name="settlement[<?php echo $current_year ?>][27_3][composition_ratio]" class="settlement_persent form-control percent number" value="<?php echo ($settlements['27_3']['composition_ratio'] == 0) ? '0%' : $settlements['27_3']['composition_ratio'] . '%'; ?>">
                                             </td>
                                         </tr>
                                     <?php } elseif ($acc_type_name == 'No Name 1') {
                                            $acc_type_name = ''; ?>
                                         <tr>
                                             <td class="l_padding" colspan="4" style="border: 0;"><?php echo $acc_type_name; ?></td>
                                         </tr>
                                         <tr>
                                             <td class="blank-cell"></td>
                                             <td colspan="4" style="border-bottom: 0;" class="td_header"><?php echo "修正後（ファクタリング考慮後）" ?></td>
                                         </tr>
                                         <?php foreach ($type_datas as $acc_name => $acc_datas) {
                                            ?>
                                             <tr>
                                                 <td class="blank-cell"></td>
                                                 <?php foreach ($acc_datas as $year => $datas) {
                                                        if (in_array($year, $next_years)) {
                                                            $style = '';
                                                        } else {
                                                            $style = 'display: none;';
                                                        }

                                                        $amount = (is_nan($datas['factor_calculated_amt']) || is_infinite($datas['factor_calculated_amt'])) ? 0 : $datas['factor_calculated_amt'];
                                                    ?>
                                                     <td class="cal-td acc_amt" style="<?php echo $style; ?>">
                                                         <input factor-formula='<?php echo $datas["factor_formula"] ?>' class="form-control number" type="" name="" id="<?php echo $year . '_' . $datas['account_id']; ?>" value="<?php if ($datas['postfix'] != '') echo number_format($amount, 1) . $datas['postfix']; else echo number_format($amount); ?>" disabled>
                                                         <input type="hidden" id="hid_<?php echo $year . '_' . $datas['account_id']; ?>" value="<?php echo $amount; ?>">
                                                     </td>
                                                 <?php } ?>
                                             </tr>
                                         <?php }
                                        } elseif ($acc_type_name == 'No Name 2') { ?>
                                         <tr style="height: 20px !important;">
                                             <td class="blank-cell"></td>
                                         </tr>
                                         <tr style="height: 105px !important;">
                                             <td class="blank-cell"></td>
                                             <td colspan="4" class="blank-cell"><?php echo "数式はファクタリングを毎月実施する前提。<br/>与信の関係上、毎月実施しない場合は平均を直接入力"; ?></td>
                                         </tr>
                                     <?php  } elseif ($acc_type_name == 'No Name 3') { ?>
                                         <!-- <tr><td colspan="4" style="border: 0;text-align:left;padding-left: 5px;"></td></tr> -->
                                         <tr>
                                             <td class="blank-cell">
                                                 </th>
                                             <td class="td_header" style="font-size: 0.9em !important;" colspan="4" style="border-bottom: 0;"><?php echo "修正後（ファクタリング考慮後）" ?></td>
                                         </tr>
                                         <?php foreach ($type_datas as $acc_name => $acc_datas) { ?>
                                             <tr>
                                                 <td class="blank-cell"></td>
                                                 <?php foreach ($acc_datas as $year => $datas) {
                                                        if (in_array($year, $next_years)) {
                                                            $style = '';
                                                        } else {
                                                            $style = 'display: none;';
                                                        }

                                                        $amount = (is_nan($datas['factor_calculated_amt']) || is_infinite($datas['factor_calculated_amt'])) ? 0 : $datas['factor_calculated_amt'];
                                                    ?>
                                                     <td class="cal-td acc_amt" style="<?php echo $style; ?>">
                                                         <input factor-formula='<?php echo $datas["factor_formula"] ?>' class="form-control number" type="" name="" id="<?php echo $year . '_' . $datas['account_id']; ?>" value="<?php if ($datas['postfix'] != '') echo number_format($amount, 1) . $datas['postfix'];
                                                                                                                                                                                                                                    else echo number_format($amount); ?>" disabled>
                                                         <input type="hidden" id="hid_<?php echo $year . '_' . $datas['account_id']; ?>" value="<?php echo $amount; ?>">
                                                     </td>
                                                 <?php } ?>
                                             </tr>
                                         <?php  }
                                        } elseif ($acc_type_name == '【予算人員】　　(実人員数）') { ?>
                                         <tr>
                                             <td colspan="2" class="blank-cell"></td>
                                             <td colspan="3" style="border: 0;padding-top: 70px;text-align:left;color: red;"><?php echo "※合致するよう割り振る（ﾋﾞｼﾞﾈｽ別人員表と合致させること。）" ?></td>
                                         </tr>
                                         <tr>
                                             <td colspan="2" class="blank-cell"></td>
                                             <th colspan="3" style="height: 35px;text-align:center;"><?php echo "【生産性】人員数"; ?></th>
                                         </tr>
                                         <tr class="employee_sales">
                                             <th colspan="2" class="blank-cell"></th>
                                             <th></th>
                                             <?php $emp_years = $next_years;
                                                $emp_year = array_slice($emp_years, 0, 2);
                                                foreach ($emp_year as $key => $value) { ?>
                                                 <th style="height: 35px;text-align:center;"><?php echo $value; ?></th>
                                             <?php } ?>
                                         </tr>
                                         <tr class="employee_sales">
                                             <td colspan="2" class="blank-cell"></td>
                                             <td style="padding-left: 5px;"><?php echo "経営・管理"; ?></td>
                                             <?php foreach ($emp_year as $key => $value) { ?>
                                                 <td class="acc_type_one employee_td"><input type="" name="employee[<?php echo $value; ?>][経営・管理]" id="<?php echo $value . '_経営・管理'; ?>" class="form-control number employee" value="<?php echo $employee[$value]['経営・管理']; ?>"></td>
                                             <?php } ?>
                                         </tr>
                                         <tr class="employee_sales">
                                             <td colspan="2" class="blank-cell"></td>
                                             <td style="padding-left: 5px;"><?php echo "営業"; ?></td>
                                             <?php foreach ($emp_year as $key => $value) { ?>
                                                 <td class="acc_type_one employee_td"><input type="" name="employee[<?php echo $value; ?>][営業]" id="<?php echo $value . '_営業'; ?>" class="form-control number employee" value="<?php echo $employee[$value]['営業']; ?>"></td>
                                             <?php } ?>
                                         </tr>
                                         <tr class="employee_sales">
                                             <td colspan="2" class="blank-cell"></td>
                                             <td style="padding-left: 5px;"><?php echo "ｵﾍﾟﾚｰｼｮﾝ"; ?></td>
                                             <?php foreach ($emp_year as $key => $value) { ?>
                                                 <td class="acc_type_one employee_td"><input type="" name="employee[<?php echo $value; ?>][ｵﾍﾟﾚｰｼｮﾝ]" id="<?php echo $value . '_ｵﾍﾟﾚｰｼｮﾝ'; ?>" class="form-control number employee" value="<?php echo $employee[$value]['ｵﾍﾟﾚｰｼｮﾝ']; ?>"></td>
                                             <?php } ?>
                                         </tr>
                                         <tr class="employee_sales">
                                             <td colspan="2" class="blank-cell"></td>
                                             <td style="padding-left: 5px;"><?php echo "合計"; ?></td>
                                             <?php foreach ($emp_year as $key => $value) { ?>
                                                 <td><input type="" name="<?php echo $value . '_emptot'; ?>" id="<?php echo $value . '_emptot'; ?>" class="form-control number" value="<?php echo number_format($employee[$value]['合計'], 2); ?>" disabled></td>
                                             <?php } ?>
                                         </tr>
                                         <tr>
                                             <td colspan="5" style="border: 0;height: 35px;text-align:center;"></td>
                                         </tr>
                                         <tr>
                                             <th class="blank-cell"></th>
                                             <?php foreach ($next_years as $key => $value) { ?>
                                                 <th style="height: 35px;text-align:center;"><?php echo $value; ?></th>
                                             <?php } ?>
                                         </tr>

                                         <tr class="tot_sale_per_person">
                                             <td style="border: 0;height: 35px;text-align:center;"><?php echo "一人当り売総"; ?></td>

                                             <?php foreach ($next_years as $key => $value) { ?>
                                                 <td><input type="" name="" class="form-control number" id="<?php echo $value . '_totSales' ?>" data-formula="<?php echo $tot_sales_per_person[$value . '_totSales']; ?>" disabled>
                                                 </td>
                                             <?php } ?>
                                         </tr>
                                 <?php  }
                                    } ?>
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>
             <!-- budget_issues-->
             <div class="row">
                 <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top:20px; margin-bottom: 30px;">
                     <div class="tbl-wrappers">
                         <table class="table-bordered table-fixed bu_analysis" id="budget_issues">
                             <thead class="">
                                 <tr>
                                     <th style="text-align: left;"><?php echo __("本ビジネスの論点整理"); ?></th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <tr>
                                     <td class="no-border valign-top h-200"><textarea class="form-control" name="point[<?php echo $current_year; ?>][issue]" style="height: 100%"><?php echo $budget_points['issue']; ?></textarea></td>
                                 </tr>
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>
             <!-- budget_points-->
             <div class="row">
                 <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top:20px; margin-bottom: 30px;">
                     <div class="tbl-wrappers">
                         <table class="table-bordered table-fixed bu_analysis" id="budget_points">
                             <thead class="">
                                 <tr>
                                     <th style="text-align: left;"><?php echo __("本ビジネス概要・取引背景・経緯"); ?></th>

                                 </tr>
                             </thead>
                             <tbody>
                                 <tr>
                                     <td class="no-border valign-top h-200"><textarea class="form-control" name="point[<?php echo $current_year; ?>][overview]" style="height: 100%"><?php echo $budget_points['overview']; ?></textarea></td>
                                 </tr>
                             </tbody>
                             <thead class="">
                                 <tr>
                                     <th style="text-align: left;"><?php echo __("BUにおける本ビジネスの位置づけ、目指す姿及び戦略"); ?></th>

                                 </tr>
                             </thead>
                             <tbody>
                                 <tr>
                                     <td class="no-border valign-top h-200"><textarea class="form-control" name="point[<?php echo $current_year; ?>][vision]" style="height: 100%"><?php echo $budget_points['vision']; ?></textarea></td>
                                 </tr>
                             </tbody>
                             <thead class="">
                                 <tr>
                                     <th style="text-align: left;"><?php echo __("当社の機能"); ?></th>

                                 </tr>
                             </thead>
                             <tbody>
                                 <tr>
                                     <td class="no-border valign-top h-200"><textarea class="form-control" name="point[<?php echo $current_year; ?>][feature]" style="height: 100%"><?php echo $budget_points['feature']; ?></textarea></td>
                                 </tr>
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>
             <!-- budget_hyokas -->
             <div class="row">
                 <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top:20px; margin-bottom: 30px;">
                     <label for="one"><?php echo __("【本取引の中期経営計画達成を阻害しかねない主なリスク、課題・留意点と対応策】　"); ?></label>
                     <div class="tbl-wrappers">
                         <table class="table-bordered table-fixed bu_analysis" id="budget_hyokas">
                             <thead>
                                 <tr>
                                     <th style="width: 3%;"></th>
                                     <th style="width: 17%;"><?php echo __("分野・領域"); ?></th>
                                     <th style="width: 30%;"><?php echo __("主なリスク、課題、留意点"); ?></th>
                                     <th style="width: 28%;"><?php echo __("対応策、モニタリング方法等"); ?></th>
                                     <th style="width: 5%;"><?php echo __("評価"); ?></th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php $i = 0;
                                    foreach ($budget_hyokas as $value) {
                                        if ($value['csr_record'] == '') {
                                            $specific_reg = $_SESSION['SPECIFIC_REGION'];
                                            if(in_array($value['region'], $specific_reg)) {
                                                $tdcolor = 'td_color';
                                                $readonly = 'readonly';
                                                $style = '';
                                                $not_allowed_edit = '';
                                            }elseif($show_hyoka_btn) {
                                                $tdcolor = '';
                                                $readonly = '';
                                                $style = 'height: 108px;resize: none;';
                                            }
                                            $i++; ?>
                                         <tr id="<?php echo 'atr_' . ($i); ?>">
                                             <td><input type="checkbox" name="" id="<?php echo 'checkhyoka_' . ($i); ?>" style="margin:-1px 10px 0px;height: 108px;"></td>
                                             <td><textarea class="form-control <?php echo $tdcolor ?>" name="hyoka[<?php echo $current_year ?>][<?php echo $i; ?>][region]" id="<?php echo 'td_textarea_' . ($i); ?>" style="<?php echo $style; ?>" <?php echo $readonly ?>><?php echo $value['region']; ?></textarea></td>
                                             <td><textarea class="form-control" name="hyoka[<?php echo $current_year ?>][<?php echo $i; ?>][major_note]" id="<?php echo 'td_textarea_' . ($i); ?>" style="height: 108px;resize: none;"><?php echo $value['major_note']; ?></textarea></td>
                                             <td><textarea class="form-control" name="hyoka[<?php echo $current_year ?>][<?php echo $i; ?>][monitor]" id="<?php echo 'td_textarea_' . ($i); ?>" style="height: 108px;resize: none;"><?php echo $value['monitor']; ?></textarea></td>
                                             <td><select class="form-control hyoka_select" name="hyoka[<?php echo $current_year ?>][<?php echo $i; ?>][evaluation]" id="<?php echo 'td_select_' . ($i); ?>" style="height: 108px;">
                                                     <?php for ($s = 0; $s < 5; $s++) { ?>
                                                         <option value="<?php echo ($s) ?>" <?php if ($value['evaluation'] == $s) { ?> selected <?php } ?>><?php echo $s; ?></option>
                                                     <?php } ?>
                                                 </select></td>
                                         </tr>
                                 <?php }
                                    } ?>
                                 <tr style="height: 108px;">
                                     <td colspan="2" style="padding-left:10px"><?php echo __("CSR上のリスク懸念"); ?></td>
                                     <td><textarea class="form-control" name="hyoka[<?php echo $current_year; ?>][CSR][major_note]" style="height: 108px;resize: none;"><?php if ($budget_hyokas[$i]['csr_record'] != '') echo $budget_hyokas[$i]['major_note'] ?></textarea></td>
                                     <td><textarea class="form-control" name="hyoka[<?php echo $current_year; ?>][CSR][monitor]" style="height: 108px;resize: none;"><?php if ($budget_hyokas[$i]['csr_record'] != '') echo $budget_hyokas[$i]['monitor'] ?></textarea></td>
                                     <td><select class="form-control" name="hyoka[<?php echo $current_year; ?>][CSR][csr_record]" id="csr_record" style="height: 108px;">
                                             <option value="1" <?php if ($budget_hyokas[$i]['csr_record'] == 1) { ?> selected <?php } ?>><?php echo __("該"); ?></option>
                                             <option value="2" <?php if ($budget_hyokas[$i]['csr_record'] == 2) { ?> selected <?php } ?>><?php echo __("非"); ?></option>
                                         </select></td>
                                 </tr>
                                 <tr>
                                     <td colspan="4" class="blank-cell"></td>
                                     <td class="blank-cell" style="text-align: center;"><?php echo __("↑"); ?></td>
                                 </tr>
                                 <tr>
                                     <td colspan="2" class="blank-cell" style="text-align: left;">
                                         <button type="button" class="btn btn-success btn_sumisho btn_add_hyokas" ><?php echo __("領域追加"); ?> </button>
                                         <button type="button" class="btn btn-danger btn_remove_hyokas" ><?php echo __("領域削除"); ?> </button>
                                     </td>
                                     <td colspan="3" class="blank-cell" style="text-align: right;"><span style="position:relative;top:-0.7rem;"><?php echo __("CSRセルフチェックリストで評価（Ａ～Ｅ、NA）のうちA・B・Eが一つでもあれば記入"); ?></span></td>
                                 </tr>
                             </tbody>
                         </table><br>
                     </div>
                 </div>
             </div>
             <!-- budget_sngs -->
             <div class="row">
                 <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top:20px; margin-bottom: 30px;">
                     <div class="table-wrapper scrollable">
                         <table class="table-bordered table-fixed bu_analysis" id="budget_sngs">
                             <thead class="">
                                 <tr>
                                     <th colspan="7" class="blank-cell" style="text-align: left !important;"><?php echo __("１．	シナジー"); ?></th>
                                     </th>
                                     <th colspan="3" class="blank-cell" style="text-align: left !important;"><?php echo __("シナジー効果の評価"); ?></th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <tr>
                                     <td class="chg_col1" colspan="7" style="height: 50px;"><?php echo "本取引があることで明らかに認められる（期待される）下記各分野のｼﾅｼﾞｰ（ｲﾝﾊﾟｸﾄ)の合計額" ?></td>
                                     <td><input type="" name="" class="form-control number" id="sngs_total" value="<?php echo ($budget_sngs['total_amount']); ?>" disabled></td>
                                     <td class="blank-cell"><span class="pull-left"><?php echo __("百万円<br/>") . ("(a+b+c)"); ?></span></td>
                                 </tr>
                             </tbody>
                             <thead class="">
                                 <tr>
                                     <th class="blank-cell" style="text-align: left;padding-top: 30px !important;"><?php echo __("【具体的内容】"); ?></th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <tr>
                                     <td colspan="14" class="blank-cell" style="padding-top: 30px !important;text-align: left;"><?php echo "○" . __("収益増加・・・新規顧客基盤獲得、取引ｼｪｱ拡大、対取引先関係強化等に伴う価格交渉力向上、取引拡大など「正の期待値」（金額は税引前ﾍﾞｰｽ）"); ?></td>
                                     <td class="blank-cell"><span class="pull-right"><?php echo __("(百万円)"); ?></span></td>
                                 </tr>
                                 <tr>
                                     <td colspan="2" class="blank-cell" style="text-align: center;"><?php echo __("if any ") . __("具体的内容"); ?></td>
                                     <td colspan="12"><textarea name="sng_cmt[<?php echo $current_year ?>][1][comment]" class="form-control" id="budget_sngs_cmt"><?php echo __($budget_sngs[0]['sng_cmt']); ?></textarea>
                                     </td>
                                     <td class="acc_type_one"><input type="" name="sng_cmt[<?php echo $current_year ?>][1][amount]" class="form-control number" style="height: 54px;" id="budget_sngs_amta" value="<?php echo $budget_sngs[0]['sng_amt'] / $budget_sngs[0]['unit']; ?>"></td>
                                     <td class="blank-cell"><span><?php echo __("a)"); ?></span></td>
                                 </tr>
                                 <tr>
                                     <td colspan="16" class="blank-cell" style="padding-top: 10px !important;text-align: left;"><?php echo "○" . __("収益増加・・・新規顧客基盤獲得、取引ｼｪｱ拡大、対取引先関係強化等に伴う価格交渉力向上、取引拡大など「正の期待値」（金額は税引前ﾍﾞｰｽ）"); ?></td>
                                 </tr>
                                 <tr>
                                     <td colspan="2" class="blank-cell" style="text-align: center;"><?php echo __("if any ") . __("具体的内容"); ?></td>
                                     <td colspan="12"><textarea name="sng_cmt[<?php echo $current_year ?>][2][comment]" class="form-control" id="budget_sngs_cmt"><?php echo __($budget_sngs[1]['sng_cmt']); ?></textarea>
                                     </td>
                                     <td class="acc_type_one"><input type="" name="sng_cmt[<?php echo $current_year ?>][2][amount]" class="form-control number" style="height: 54px;" id="<?php echo 'budget_sngs_amtb' ?>" value="<?php echo $budget_sngs[1]['sng_amt'] / $budget_sngs[1]['unit']; ?>"></td>
                                     <td class="blank-cell"><span><?php echo __("b)"); ?></span></td>
                                 </tr>
                                 <tr>
                                     <td colspan="16" class="blank-cell" style="padding-top: 10px !important;text-align: left;"><?php echo "○" . __("コスト削減 ・・・ 共同配送/施設共用によるｺｽト引下げ、共通業務の集約化等によるｺｽﾄ削減（本取引を縮小・撤退した時に他取引に与える負のｲﾝﾊﾟｸﾄ/税引前ﾍﾞｰｽ）"); ?></td>
                                 </tr>
                                 <tr>
                                     <td colspan="2" class="blank-cell" style="text-align: center;"><?php echo __("if any ") . __("具体的内容"); ?></td>
                                     <td colspan="12"><textarea name="sng_cmt[<?php echo $current_year ?>][3][comment]" class="form-control" id="budget_sngs_cmt"><?php echo __($budget_sngs[2]['sng_cmt']); ?></textarea>
                                     </td>
                                     <td class="acc_type_one"><input type="" name="sng_cmt[<?php echo $current_year ?>][3][amount]" class="form-control number" style="height: 54px;" id="budget_sngs_amtc" value="<?php echo $budget_sngs[2]['sng_amt'] / $budget_sngs[2]['unit']; ?>"></td>
                                     <td class="blank-cell"><span><?php echo __("c)"); ?></span></td>
                                 </tr>
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>
             <!-- budget_comps -->
             <div class="row">
                 <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top:20px; margin-bottom: 30px;">
                     <div class="col-md-6"></div>
                     <div class="col-md-6"><span style="color: #006699;font-size: 10px;" class="pull-right"><?php echo __('【「傾向」を表す数値の入力方法】　「納入ｼｪｱ」「業界内ｼｪｱ」「市場規模」の増減割合【19年度実績→23年度計画】を、「●割増/減」で入力（小数点第一位まで）して加重平均し、少数点以下を四捨五入して整数化'); ?></span></div>
                     <label for="one"><?php echo __("２．「商品の競争力」及び「最終製品の競争力と成長段階」"); ?></label>
                     <div class="tbl-wrappers">
                         <table class="table-bordered table-fixed bu_analysis" id="budget_comps">
                             <thead class="">
                                 <tr>
                                     <th colspan="2" width="100px"></th>
                                     <th colspan="4"><?php echo __("当社販売商品　（構成比、納入ｼｪｱは") . ($current_year - 1) . __("年度実績）"); ?></th>
                                     <th colspan="6"><?php echo __("最終製品　（業界内ｼｪｱは") . ($current_year - 1) . __("年度実績）"); ?></th>
                                 </tr>
                                 <tr>
                                     <th colspan="2"></th>
                                     <th><?php echo __("販売構成比"); ?></th>
                                     <th><?php echo __("取引先"); ?></th>
                                     <th><?php echo __("当社納入ｼｪｱ"); ?></th>
                                     <th><?php echo __("納入ｼｪｱ増減"); ?></th>
                                     <th><?php echo __("最終製品名"); ?></th>
                                     <th><?php echo __("最終需要家(業界)"); ?></th>
                                     <th><?php echo __("業界内ｼｪｱ"); ?></th>
                                     <th><?php echo __("業界内ｼｪｱ増減"); ?></th>
                                     <th><?php echo __("市場規模増減"); ?></th>
                                     <th><?php echo __("成長性"); ?></th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php foreach ($budget_comps['comp'] as $i => $bcomps) { ?>
                                     <tr id="<?php echo 'ptr_' . ($i + 1); ?>">
                                         <td><input type="checkbox" name="" id="<?php echo 'checkbox_' . ($i + 1); ?>" style="margin:-1px 16px 0px;"></td>
                                         <td style="text-align:left;padding-left: 5px;" id="<?php echo 'num_' . ($i + 1); ?>"><?php echo $i + 1; ?></td>
                                         <td class="acc_type_one "><input type="" name="comps[<?php echo $current_year; ?>][<?php echo ($i + 1); ?>][sales_ratio]" class="form-control number percent" id="<?php echo 'sales_ratio_td_' . ($i + 1); ?>" value="<?php echo $bcomps['sales_ratio'] . '%'; ?>"></td>
                                         <td><textarea class="form-control" name="comps[<?php echo $current_year; ?>][<?php echo ($i + 1); ?>][customer]" style="height: 34px;" id="<?php echo 'customer_td_' . ($i + 1); ?>"><?php echo $bcomps['customer'] ?></textarea></td>
                                         <td class="acc_type_one "><input type="" name="comps[<?php echo $current_year; ?>][<?php echo ($i + 1); ?>][deli_share]" class="form-control number percent" id="<?php echo 'deli_share_td_' . ($i + 1); ?>" value="<?php echo $bcomps['deli_share'] . '%'; ?>"></td>
                                         <td class="acc_type_one "><input type="" name="comps[<?php echo $current_year; ?>][<?php echo ($i + 1); ?>][deli_share_change]" class="form-control number change_td" id="<?php echo 'deli_share_change_td_' . ($i + 1); ?>" value="<?php echo number_format($bcomps['deli_share_change'], 1); ?>"></td>
                                         <td><textarea class="form-control" name="comps[<?php echo $current_year; ?>][<?php echo ($i + 1); ?>][product_name]" style="height: 34px;" id="<?php echo 'product_name_td_' . ($i + 1); ?>"><?php echo $bcomps['product_name']; ?></textarea></td>
                                         <td><textarea class="form-control" name="comps[<?php echo $current_year; ?>][<?php echo ($i + 1); ?>][industry]" style="height: 34px;" id="<?php echo 'industry_td_' . ($i + 1); ?>"><?php echo $bcomps['industry']; ?></textarea></td>
                                         <td class="acc_type_one "><input type="" name="comps[<?php echo $current_year; ?>][<?php echo ($i + 1); ?>][industry_share]" class="form-control number percent" id="<?php echo 'industry_share_td_' . ($i + 1); ?>" value="<?php echo $bcomps['industry_share'] . '%'; ?>"></td>
                                         <td class="acc_type_one "><input type="" name="comps[<?php echo $current_year; ?>][<?php echo ($i + 1); ?>][industry_share_change]" class="form-control number change_td" id="<?php echo 'industry_chg_td_' . ($i + 1); ?>" value="<?php echo number_format($bcomps['industry_share_change'], 1); ?>"></td>
                                         <td class="acc_type_one "><input type="" name="comps[<?php echo $current_year; ?>][<?php echo ($i + 1); ?>][market_size_change]" class="form-control number change_td" id="<?php echo 'market_size_change_td_' . ($i + 1); ?>" value="<?php echo number_format($bcomps['market_size_change'], 1); ?>"></td>
                                         <td><input type="" name="comps[<?php echo $current_year; ?>][<?php echo ($i + 1); ?>][growth_pot]" class="form-control number change_td growth_pot_td" id="<?php echo 'growth_pot_td_' . ($i + 1); ?>" value="<?php echo $bcomps['growth_pot_each'] ?>" data="<?php echo 'industry_chg_td_' . ($i + 1) . '+market_size_change_td_' . ($i + 1) . '+(industry_chg_td_' . ($i + 1) . '*market_size_change_td_' . ($i + 1) . ')' ?>" disabled></td>
                                     </tr>
                                 <?php } ?>
                                 <tr class="grand_total">
                                     <td colspan="2" style="text-align:left;padding-left: 35px;height: 35px;"><?php echo "累計"; ?></td>
                                     <td><input type="" name="" id="sale_ratio_total" class="form-control number" value="<?php echo $budget_comps['grand_total']['sales_ratio']; ?>" disabled></td>
                                     <td><?php echo __("（加重平均）"); ?></td>
                                     <td><input type="" name="" id="deli_share_total" class="form-control number" value="<?php echo $budget_comps['grand_total']['sale_deli']; ?>" disabled></td>
                                     <td><input type="" name="" id="saledeli_chg_total" class="form-control number change_td" value="<?php echo $budget_comps['grand_total']['sale_deli_chg']; ?>" disabled></td>
                                     <td></td>
                                     <td><?php echo __("（加重平均）"); ?></td>
                                     <td><input type="" name="" id="industry_share_total" class="form-control number" value="<?php echo $budget_comps['grand_total']['sale_indus']; ?>" disabled></td>
                                     <td><input type="" name="" id="industry_chg_total" class="form-control number" value="<?php echo $budget_comps['grand_total']['sale_indus_chg']; ?>" disabled></td>
                                     <td><input type="" name="" id="market_chg_total" class="form-control number" value="<?php echo $budget_comps['grand_total']['market_size_chg']; ?>" disabled></td>
                                     <td><input type="" name="" id="growth_pot_total" class="form-control number" value="<?php echo $budget_comps['grand_total']['growth_pot']; ?>" disabled></td>
                                 </tr>
                                 <tr>
                                     <td colspan="12" style="height: 35px;" class="blank-cell"></td>
                                 </tr>
                                 <tr class="final_total">
                                     <td class="blank-cell">
                                         <button type="button" class="btn btn-success btn_add_comps"><?php echo __("領域追加"); ?> </button>
                                     </td class="blank-cell">
                                     <td style="padding-left: 70px !important;" class="blank-cell">
                                         <button type="button" class="btn btn-danger btn_remove_comps"><?php echo __("領域削除"); ?> </button>
                                     </td>
                                     <td class="blank-cell"></td>
                                     <td class="blank-cell" style="text-align:right;"><?php echo __("当社納入 <br>「商品の 競争力」"); ?></td>
                                     <td><input type="" name="" id="deli_product" class="form-control number" value="<?php echo $budget_comps['final_total']['deli_product']; ?>" disabled></td>
                                     <td><input type="" name="" id="deli_chg_product" class="form-control number" value="<?php echo $budget_comps['final_total']['deli_chg_product']; ?>" disabled></td>
                                     <td class="blank-cell"></td>
                                     <td class="blank-cell" style="text-align:right;"><?php echo __("最終製品の　<br>「競争力」"); ?></td>
                                     <td><input type="" name="" id="indus_fproduct" class="form-control number" value="<?php echo $budget_comps['final_total']['indus_fproduct']; ?>" disabled></td>
                                     <td><input type="" name="" id="indus_chg_fproduct" class="form-control number" value="<?php echo $budget_comps['final_total']['indus_chg_fproduct']; ?>" disabled></td>
                                     <td class="blank-cell"></td>
                                     <td class="blank-cell"></td>
                                 </tr>
                                 <tr>
                                     <td colspan="4" class="blank-cell"></td>
                                     <td class="blank-cell" style="text-align:right;"><?php echo __("組合せで表記"); ?></td>
                                     <td><input type="" name="" class="form-control number" id="delivery" value="<?php echo ($budget_comps['final_total']['deli_product']) . " " . $budget_comps['final_total']['deli_chg_product']; ?>" disabled></td>
                                     <td class="blank-cell"></td>
                                     <td class="blank-cell"></td>
                                     <td class="blank-cell" style="text-align:right;"><?php echo __("組合せで表記"); ?></td>
                                     <td><input type="" name="" class="form-control number" id="industry" value="<?php echo ($budget_comps['final_total']['indus_fproduct'] . " " . $budget_comps['final_total']['indus_chg_fproduct']); ?>" disabled></td>
                                     <td class="blank-cell" style="text-align:right;"><?php echo __("最終製品の<br>「成長性」"); ?></td>
                                     <td class="cal-td"><input type="" name="" id="potential" class="form-control number" value="<?php echo $budget_comps['final_total']['final_potential'] ?>" disabled></td>
                                 </tr>
                                 <tr>
                                     <td colspan="4" class="blank-cell"></td>
                                     <td colspan="2" class="blank-cell"><?php echo "（　「＋」＝△、　「－」＝▲　）"; ?></td>
                                     <td class="blank-cell"></td>
                                     <td class="blank-cell"></td>
                                     <td colspan="2" class="blank-cell"><?php echo "（　「＋」＝△、　「－」＝▲　）"; ?></td>
                                     <td colspan="2" class="blank-cell"><?php echo "（　「＋」＝△、　「－」＝▲　）"; ?></td>
                                 </tr>
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>
         <?php } else { ?>
             <div class="col-sm-12">
                 <p class="no-data"><?php echo $returnMsg['errorMsg']; ?></p>
             </div>
         <?php } ?>
     </div>
 </div>
 <br><br>
 <?php
    echo $this->Form->end();
    ?>