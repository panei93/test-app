var TABLE_ONE = $("#table_one");
var TABLE_ONES = $("#table_one input.amount_input");
var TABLE_THREE = $("#table_three input.amount_input");

var TABLE_ONE_CLONE = $("#table_one");
$(document).ready(function () {

	$(window).on('beforeunload', () => {
		loadingPic()
	});

	document.onreadystatechange = pageLeaveLoading  = function () {
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
	
	//if no data
	if (NONE_LABOR_COST) {
		$("input[type=text]").prop("disabled", true);
	}
	checkNegative(); //make negative
	$("[data-toggle=popover]").popover(); //tooltik information
	$("#filter-btn").on("click", onFilterHandler); //filter event
	$("#target_year, #group_code").on("change", onFilterHandler);

	if ($(".tbl-wrapper").length) {
		$(".tbl-wrapper").floatingScroll();
	}

	if ($("#table_one").length > 0) {
		$("#table_one").floatThead({
			position: "absolute",
		});
	}
	// start - input event(khm)
	/*****
	 * Amount Input Event for table one
	 ****/
	//calculation
	TABLE_ONE.on("keyup", ".amount_input", onAmountChangeHandler);
	//add old value in attribute amount input
	TABLE_ONE.on("keyup", ".amount_input", keyupHandler);
	//Table Three is separated calculation
	TABLE_ONE.on("keyup", ".amount_input", tableThreeHandler);
	//when focus to input, if data is 0, remove data
	TABLE_ONE.on("focus", ".amount_input", focusHandler);
	//when focusout from input, if data is empty, add 0.00
	TABLE_ONE.on("focusout", ".amount_input", focusoutHandler);
	// when input in table one, update value in table three 
	TABLE_ONE.on("keyup", ".amount_input", tableThreeAddValue);
	//Table Three is separated calculation

	// end - input event(khm)
	/*****
	 * Amount Input Event for table three
	 ****/

	//calculation
	TABLE_THREE.on("keyup", onT3AmountChangeHandler);
	//add old value in attribute amount input
	TABLE_THREE.on("keyup", keyupHandler);
	//when focus to input, if data is 0, remove data
	TABLE_THREE.on("focus", focusHandler);
	//when focusout from input, if data is empty, add 0.00
	TABLE_THREE.on("focusout", t3FocusoutHandler);

	// start - for clone row(khm)
	$('#table_one').on('change','.position_name', function(){
		var newtrid = $(this).closest('tr').attr('id');
		
        var oldtrid = newtrid.replace("new_", "exist_");
        
        var code = this.value;
        var data = $("#"+code).attr('data');
        var datas = data.split('/');
        var pName = datas[0];
        var personnel = datas[1];
        var corporate = datas[2];
        
        var labor = $('tr#'+oldtrid+" .amount_input").attr('labor');
        var corpo = $('tr#'+oldtrid+" .amount_input").attr('corpo');
        
        $("tr#"+oldtrid+" #"+labor).text(personnel);
        $("tr#"+oldtrid+" #"+corpo).text(corporate);
        $("tr#"+oldtrid+" #"+labor).attr('personnel_cost', personnel);
        $("tr#"+oldtrid+" #"+corpo).attr('corporate_cost', corporate);
        $("tr#"+oldtrid+" #new_user").text($(".new_user").val());
        
        $('tr#'+oldtrid+" .amount_input").attr('position_code', code);
        $('tr#'+newtrid+" .amount_input").attr('position_code', code);

		$("tr#"+oldtrid+" .emp_tot").attr('position_code', code);
		$("tr#"+newtrid+" .emp_tot").attr('position_code', code);
    });

    $('#table_one').on('focusout','.new_user', function(){
        var newuser = this.value;
        var oldtrid = $(this).closest('tr').attr('id');
        var newtrid = oldtrid.replace("exist_", "new_");
        var current = $(this);
        hideToolTipError(current);
        $('#table_one .new_user').each(function() {
        	var remtrid = $(this).closest('tr').attr('id');
        	var olduser =  $(this).val();
        	if(remtrid != oldtrid && olduser == newuser) {
		    	showToolTipError(current, errMsg(commonMsg.JSE096));
			}
        });
        $('#table_one .user_name').each(function() {
			var remtrid = $(this).closest('tr').attr('id');
    		var olduser =  $.trim($(this).text());
    		if(olduser == newuser) {
		    	showToolTipError(current, errMsg(commonMsg.JSE096));
    		}
		});

        $("#"+oldtrid+" .amount_input").attr('new_user_name', current.val());
		$("#"+newtrid+" .amount_input").attr('new_user_name', current.val());
		$("#"+oldtrid+" .emp_tot").attr('new_user_name', current.val());
		$("#"+newtrid+" .emp_tot").attr('new_user_name', current.val());
		$("#"+oldtrid+" .comment_input").attr('id', "exist_comment_"+current.val().replace(/ /g, '_'));
		$("#"+newtrid+" .comment_input").attr('id', "new_comment_"+current.val().replace(/ /g, '_'));
    });

    $("#table_one").on('click', '.btn_add', function() {
        var tr = $('tr[id^="exist_row_"]:last');
        var prevnum = parseInt(tr.prop("id").match(/\d+/g), 10 );
        var num = parseInt(tr.prop("id").match(/\d+/g), 10 ) +1;
        // clone
        $("table#table_one tbody tr#new_row_"+prevnum).after(tr.clone().prop('id', 'exist_row_'+num ).appendTo("table#table_one"));
        
        var tr = $('tr[id^="new_row_"]:last');
        $("table#table_one tbody tr#exist_row_"+num).after(tr.clone().prop('id', 'new_row_'+num ).appendTo("table#table_one"));
        
        // clear the clone data
        $('tr#exist_row_'+num).each(function(){
			$(this).find('td input.new_user').val('');
			$(this).find('td input.amount_input').val('0.00');
			$(this).find('td.b_person_count').text('0.0000');
			$(this).find('td.unit_labor_cost').text('0.00');
			$(this).find('td.unit_corpo_cost').text('0.00');
			$(this).find('td.emp_tot').text('0.00000');
			$(this).find('td.emp_tot').removeClass('bg-color')
			$(this).find('td input.comment_input').val('');

			$(this).find('td input.amount_input').attr('val', '0.00');
			$(this).find('td.emp_tot').attr('val', '0.00000');

			$(this).find('td.b_person_count').attr('id', 'person_'+num);
			$(this).find('td.unit_labor_cost').attr('id', 'labor_'+num);
			$(this).find('td.unit_corpo_cost').attr('id', 'corpo_'+num);
			var empClass = $(this).find('td.emp_tot').attr('class');
			var emp_class = empClass.replace("emp_"+prevnum, "emp_"+num);
			$(this).find('td.emp_tot').attr('class', emp_class);
			
			$(this).find('td input.amount_input').attr('person', 'person_'+num);
			$(this).find('td input.amount_input').attr('labor', 'labor_'+num);
			$(this).find('td input.amount_input').attr('corpo', 'corpo_'+num);
			$(this).find('td input.amount_input').attr('emp', 'emp_'+num);
			$(this).find('td.emp_tot').attr('total_exist_id', 'total_'+num);

			var btnClass = $(this).find('td .clone_remove').attr('class');
			var btn_class = btnClass.replace("btn_add", "btn_remove");
			var btn_class = btn_class.replace("btn-success", "btn-danger");
			$(this).find('td .clone_remove').attr('class', btn_class);
			$(this).find('td .clone_remove').attr('value', '-');
		});

		$('tr#new_row_'+num).each(function(){
			$(this).find('td input.new_user').val('');
			$(this).find('td input.amount_input').val('0.00');
			$(this).find('td.b_person_count').text('0.0000');
			$(this).find('td.unit_labor_cost').text('0.00');
			$(this).find('td.unit_corpo_cost').text('0.00');
			$(this).find('td.emp_tot').text('0.00000');
			$(this).find('td.emp_tot').removeClass('bg-color')
			$(this).find('td input.comment_input').val('');

			$(this).find('td input.amount_input').attr('val', '0.00');
			$(this).find('td.emp_tot').attr('val', '0.00000');

			$(this).find('td.b_person_count').attr('id', 'person_'+num);
			$(this).find('td.unit_labor_cost').attr('id', 'labor_'+num);
			$(this).find('td.unit_corpo_cost').attr('id', 'corpo_'+num);
			var empClass = $(this).find('td.emp_tot').attr('class');
			var emp_class = empClass.replace("emp_"+prevnum, "emp_"+num);
			$(this).find('td.emp_tot').attr('class', emp_class);

			$(this).find('td input.amount_input').attr('person', 'person_'+num);
			$(this).find('td input.amount_input').attr('labor', 'labor_'+num);
			$(this).find('td input.amount_input').attr('corpo', 'corpo_'+num);
			$(this).find('td input.amount_input').attr('emp', 'emp_'+num);
			$(this).find('td.emp_tot').attr('total_new_id', 'total_'+num);
		});

		$("table#table_one tbody tr#exist_row_"+num+" .new_user").tooltip("destroy");
		$("table#table_one tbody tr#exist_row_"+num+" .new_user").css({"border-color": "#bbb"});
    });

    $("#table_one").on('click', '.btn_remove', function() {
        var tr = $('tr[id^="exist_row_"]:last');
        var trnum = parseInt(tr.prop("id").match(/\d+/g), 10 );
        $('#table_one #exist_row_'+trnum+' .amount_input').each(function(index, input) {
			var business_id = input.getAttribute('business_id');
			var removeValue = input.getAttribute('val');

			var total_business_node = $("td[total_business_id='" + business_id + "']");
			var totalValue = total_business_node.text();
			var lastResult = totalValue - removeValue;
			total_business_node.text(number_format(lastResult));

			var tb2_total = $("#exist_total_"+business_id);
			var totalValue = tb2_total.text();
			var lastResult = totalValue - removeValue;
			tb2_total.text(number_format(lastResult));

			var tb2_all_total = $("#table_two #exist_total");
			var totalValue = tb2_all_total.text();
			var lastResult = totalValue - removeValue;
			tb2_all_total.text(number_format(lastResult));
		});
		$('#table_one #exist_row_'+trnum+' .emp_tot').each(function(index, td) {
			var removeValue = td.getAttribute('val');
			var all_total = $("#all_total");
			var totalValue = all_total.text();
			var lastResult = totalValue - removeValue;
			all_total.text(number_format(lastResult));
		});
		$('#table_one #new_row_'+trnum+' .amount_input').each(function(index, input) {
			var business_id = input.getAttribute('business_id');
			var removeValue = input.getAttribute('val');

			var total_business_node = $("td[total_business_id='" + business_id + "']");
			var totalValue = total_business_node.text();
			var lastResult = totalValue - removeValue;
			total_business_node.text(number_format(lastResult));

			var tb2_total = $("#new_total_"+business_id);
			var totalValue = tb2_total.text();
			var lastResult = totalValue - removeValue;
			tb2_total.text(number_format(lastResult));

			var tb2_all_total = $("#table_two #new_total");
			var totalValue = tb2_all_total.text();
			var lastResult = totalValue - removeValue;
			tb2_all_total.text(number_format(lastResult));
		});
		$('#table_one #new_row_'+trnum+' .emp_tot').each(function(index, td) {
			var removeValue = td.getAttribute('val');
			var all_total = $("#all_total");
			var totalValue = all_total.text();
			var lastResult = totalValue - removeValue;
			all_total.text(number_format(lastResult));
		});
		
       	$('#table_one #exist_row_'+trnum+', #table_one #new_row_'+trnum).remove();

    });
    // end - for clone row(khm)
});

//table one amountChange
function onAmountChangeHandler() {
	validation($(this));
	checkUserPostion($(this));
	// checkNegative();
	//get data attributes
	var business_id = $(this).attr("business_id");
	var user_id = $(this).attr("user_id");
	var business_type = $(this).attr("business_type"); //new or exit
	
	var old_value = $(this).attr("val")
		? calculate_format($(this).attr("val"))
		: 0;
	var new_value =
		$(this).val() == "" || $(this).val() == "-" 
			? 0
			: calculate_format($(this).val());
	var diff_value = new_value - old_value;
	// start - added attr(khm)
	var position_code = $(this).attr("position_code");
	var person_id = $(this).attr("person");
	var labor_id = $(this).attr("labor");
	var corpo_id = $(this).attr("corpo");
	var emp_id = $(this).attr("emp");
	var currentRow = $(this).parent().parent();
	var thisId = currentRow.attr('id');

	var datas = {
		business_id,
		user_id,
		business_type,
		position_code,
		diff_value,
		person_id,
		labor_id,
		corpo_id,
		emp_id,
		currentRow,
		thisId
	};
	// end - added attr(khm)
	tableOneHandler(datas);
	tableTwoHandler(datas);

	if (user_id) {
		tableThreeHandler(datas);
	}
}

/**
 * For Table One
 **/
function tableOneHandler(params) {
	var datas = { ...params };
	var lid = '';
	
	if (datas.user_id) {
		//normal
		var total_user_node =
			datas.business_type == "2"
				? $("td[total_new_id='" + datas.user_id + "']")
				: $("td[total_exist_id='" + datas.user_id + "']");
	} else {
		//adjustment
		var total_user_node =
			datas.business_type == "2"
				? $("#new_adjustment")
				: $("#exist_adjustment");
		var other_adjustment = total_user_node.selector  == "#new_adjustment" ? $("#exist_adjustment").attr("val") : $("#new_adjustment").attr("val");
	}
	// start - replace attr(khm)
	if ((datas.position_code == '0'|| datas.position_code != '0' ) && datas.user_id == '0') {
		var total_user_node = '';
		var other_adjustment = '';
		var lid = datas.labor_id.split('_')[1];
		var total_user_node = datas.business_type == "2"
			? $("td[total_new_id='total_" + lid + "']")
			: $("td[total_exist_id='total_" + lid + "']");
	}
	var all_total_node = $("#all_total");
	var b_person_count_node = $("#"+datas.person_id);
	var unit_labor_cost_node = $("#"+datas.labor_id);
	var unit_corpo_cost_node = $("#"+datas.corpo_id);
	// var number_of_emp = $("." + datas.emp_id);
	// end - replace attr(khm)

	var total_business_node = $(
		"td[total_business_id='" + datas.business_id + "']"
	);

	/*var all_total_node = $("#all_total");
	var b_person_count_node = $("#b_person_count_" + datas.user_id);
	var unit_labor_cost_node = $("#unit_labor_cost_" + datas.user_id);
	var unit_corpo_cost_node = $("#unit_corpo_cost_" + datas.user_id);
	var number_of_emp = $(".number_of_emp_" + datas.user_id);*/
	var number_of_emp = $(".number_of_emp_" + datas.user_id);
	var emp_id = $("." + datas.emp_id);

	//get Total value
	var total_business = calculate_format(total_business_node.text());
	var total_user = calculate_format(number_format(total_user_node.text(), 5));
	var total_user_val = calculate_format(
		number_format(total_user_node.attr("val"), 5)
	); //for 4 decimal calculation
	var all_total = calculate_format(all_total_node.text());
	var b_person_count = calculate_format(b_person_count_node.text());
	var unit_labor_cost = calculate_format(unit_labor_cost_node.text());
	var unit_corpo_cost = calculate_format(unit_corpo_cost_node.text());

	//calculate
	var new_total_business = total_business + datas.diff_value;
	var new_total_user = total_user_val + datas.diff_value;
	var new_all_total = all_total + datas.diff_value;
	var new_total_user_val = total_user_val + datas.diff_value; //actual decimal place

	//b person count calculation, 1 or 0.5
	var cal_count = b_person_count_node.attr("cal_count");
	var new_b_person_count = b_person_count + datas.diff_value * cal_count;

	//push in table one
	total_business_node.text(number_format(new_total_business));
	total_user_node.text(number_format(new_total_user, 5));
	
	total_user_node.attr("val", new_total_user_val); //for decimal 4 place
	all_total_node.text(number_format(new_all_total, 5));
	b_person_count_node.text(number_format(new_b_person_count, 4));

	/**
	 * unit labor cost calculation
	 * ? labor_unit  = personnel_cost * person_count
	 * ? yearly_labor_cost = labor_unit * 12 + adjust_labor_cost
	 * ? unit_labor_cost=yearly_labor_cost/person_count/12
	 ***/
	if(lid != '') {
		// start - replace attr(khm)
		var new_total =
			calculate_format($("td[total_new_id='total_" + lid + "']").attr("val")) ||
			0;
		var exist_total =
			calculate_format(
				$("td[total_exist_id='total_" + lid + "']").attr("val")
			) || 0;
		// end - replace attr(khm)
	}else {
		var new_total =
			calculate_format($("td[total_new_id=" + datas.user_id + "]").attr("val")) ||
			0;
		var exist_total =
			calculate_format(
				$("td[total_exist_id=" + datas.user_id + "]").attr("val")
			) || 0;
	}
	
	var person_count = new_total + exist_total; //exist + new person count
	var personnel_cost = calculate_format(
		unit_labor_cost_node.attr("personnel_cost")
	);
	var adjust_labor_cost = calculate_format(
		unit_labor_cost_node.attr("adjust_labor_cost")
	);

	var labor_unit = personnel_cost * calculate_format(person_count);
	var yearly_labor_cost = labor_unit * 12 + adjust_labor_cost;
	var unit_labor_cost = yearly_labor_cost / person_count / 12;
	
	unit_labor_cost_node.text(
		number_format(isFinite(unit_labor_cost) ? unit_labor_cost : personnel_cost)
	);
	// unit_labor_cost_node.text((unit_labor_cost_node.text() == 0))
	/*****
	 * unit corpo cost calculation
	 * ? corpo_unit         = b_person_total X corporate_cost
	 * ? yearly_corpo_cost  = corpo_unit X 12 + adjust_corpo_cost
	 * ? unit_corpo_cost    = yearly_corpo_cost/person_count/12
	 *****/
	var b_person_total =
		new_b_person_count +
		calculate_format(unit_corpo_cost_node.attr("common_expense"));
	var corporate_cost = calculate_format(
		unit_corpo_cost_node.attr("corporate_cost")
	);
	var adjust_corpo_cost = calculate_format(
		unit_corpo_cost_node.attr("adjust_corpo_cost")
	);
	//var corpo_unit = b_person_total * corporate_cost;
	//var corpo_unit = new_total_user_val * corporate_cost;
	var corpo_unit = (new_total + exist_total) * corporate_cost;
	var yearly_corpo_cost = corpo_unit * 12 + adjust_corpo_cost;
	var unit_corpo_cost = ((yearly_corpo_cost / person_count / 12) == 0) ? corporate_cost : unit_corpo_cost;
	var empIds = datas.emp_id != undefined ? datas.emp_id.split('_') : [];
	var b_person_total_value = parseFloat($("#b_person_count_total_"+ datas.user_id).text());
	var new_b_person_total_value = parseFloat($("#person_total_"+empIds[empIds.length-1]).text());

	/* 
	* show red bg color when the total is greater than one 
	* OR 
	* Budget Personnel Result is greater than number of Personnel Total 
	*/
	if(person_count > 1 || (b_person_total_value < new_b_person_count) || (new_b_person_total_value < person_count)){
		emp_id.addClass("bg-color");
	} else {
		emp_id.removeClass("bg-color");
	}
	// var total_adjustment = Number(new_total_user_val) + Number(other_adjustment);
	var new_adjustment = $("#new_adjustment").attr("val");
	var exist_adjustment = $("#exist_adjustment").attr("val");
	// new_adjustment > 1 ? $("#new_adjustment").addClass("bg-color") : $("#new_adjustment").removeClass("bg-color");
	// exist_adjustment > 1 ? $("#exist_adjustment").addClass("bg-color") : $("#exist_adjustment").removeClass("bg-color");
	var total_adjustment = Number(new_adjustment) + Number(exist_adjustment);
	if(total_adjustment > 1){
		$("#new_adjustment").addClass("bg-color");
		$("#exist_adjustment").addClass("bg-color");
	} else {
		$("#new_adjustment").removeClass("bg-color");
		$("#exist_adjustment").removeClass("bg-color");
	}

	if (unit_corpo_cost_node.attr("common_expense") == 0)
		unit_corpo_cost_node.text(number_format(unit_corpo_cost));
}
/**
 * For Table Two
 **/
//!adjustment row is not calculate in this table
function tableTwoHandler(params) {
	var datas = { ...params };
	//select node
	var total_node;
	var all_total_node;
	//select only new row and exist row
	if (datas.business_type == "2") {
		total_node = $("#new_total_" + datas.business_id);
		all_total_node = $("#new_total");
	} else if (datas.business_type == "1") {
		total_node = $("#exist_total_" + datas.business_id);
		all_total_node = $("#exist_total");
	}

	if (total_node) {
		//get Total value
		var total_value = calculate_format(total_node.text());
		var all_total_value = calculate_format(all_total_node.text());
		//calculate
		var new_total_value = total_value + datas.diff_value;
		var new_all_total_value = all_total_value + datas.diff_value;
		//push in table two
		total_node.text(number_format(new_total_value, 4));
		all_total_node.text(number_format(new_all_total_value, 4));
	}
}

/**
 * For Table Three
 **/
function tableThreeHandler() {
	if ($(this).attr("position_id")) {
		let labor = [];
		let corpo = [];
		TABLE_ONE.map((index, input) => {
			let business_code = input.getAttribute("business_id");
			let user_id = input.getAttribute("user_id");
			let person_count = isNaN(calculate_format(input.getAttribute("val")))
				? 0
				: calculate_format(input.getAttribute("val"));
			let unit_corpo_cost = calculate_format(
				$("#unit_corpo_cost_" + user_id).text()
			);
			let unit_labor_cost = calculate_format(
				$("#unit_labor_cost_" + user_id).text()
			);

			if (user_id) {
				let oldLabor =
					labor[business_code] !== undefined ? labor[business_code] : 0;
				let oldCorpo =
					corpo[business_code] !== undefined ? corpo[business_code] : 0;

				labor[business_code] = unit_labor_cost * person_count * 12 + oldLabor;
				corpo[business_code] = unit_corpo_cost * person_count * 12 + oldCorpo;
			}
		});

		let totalLabor = 0;
		let totalCorpo = 0;
		let allTotal = 0;
		labor.forEach((val, code) => {
			
			$("#salary_per_pc_" + code).text(number_format(labor[code], 0)); //salary by unit_labor_cost
			$("#salary_per_pc_" + code).attr("val", number_format(labor[code], 4));

			$("#salary_per_cc_" + code).text(number_format(corpo[code], 0)); //salary by unit_corporate_cost
			$("#salary_per_cc_" + code).attr("val", number_format(corpo[code], 4));

			totalLabor += labor[code];
			totalCorpo += corpo[code];

			let adjOne = calculate_format(
				$(`input[business_id=${code}][row=1]`).val()
			);
			let adjTwo = calculate_format(
				$(`input[business_id=${code}][row=2]`).val()
			);
			let total = labor[code] + corpo[code] + adjOne + adjTwo;
			allTotal += total;
			$("#t3_total_" + code).text(number_format(total, 0)); //t3 total row
			$("#t3_total_" + code).attr("val", number_format(total, 4));
		});
		$("#total_salary_per_pc").text(number_format(totalLabor, 0)); //total salary by unit_labor_cost
		$("#total_salary_per_pc").attr("val", number_format(totalLabor, 4));
		$("#total_salary_per_cc").text(number_format(totalCorpo, 0)); // total salary by unit_corporate_cost
		$("#total_salary_per_cc").attr("val", number_format(totalCorpo, 4));
		$("#t3_all_total").text(number_format(allTotal, 0)); //t3 all total
		$("#t3_all_total").attr("val", number_format(allTotal, 4));

		checkNegative();
	}
}

/***
 * Add value in table three when input in table one
 ***/
function tableThreeAddValue(){
	let business_code = $(this).attr("business_id");
	if(business_code && $(this).attr("position_code")){{
		let salary_per_pc = 0, salary_per_cc = 0;
		$("#table_one input[business_id="+business_code+"]").each(function(val){
			let user_id = $(this).attr("user_id");
			// check users to calculate
			if(user_id){
				let unit_labor_cost, unit_corpo_cost;				
				// calculate format remove comma and space
				// D column
				if(user_id == 0){
					let labor_new_user_id = $(this).attr("labor");
					let corpo_new_user_id = $(this).attr("corpo");
					unit_labor_cost = calculate_format(
						$("#" + labor_new_user_id).text()
					);
					// E column
					unit_corpo_cost = calculate_format(
						$("#" + corpo_new_user_id).text()
					);
				}else{
					unit_labor_cost = calculate_format(
						$("#unit_labor_cost_" + user_id).text()
					);
					// E column
					unit_corpo_cost = calculate_format(
						$("#unit_corpo_cost_" + user_id).text()
					);
				}
				unit_labor_cost = (isNaN(unit_labor_cost) || unit_labor_cost == undefined)? 0 : unit_labor_cost;
	
				unit_corpo_cost = (isNaN(unit_corpo_cost) || unit_corpo_cost == undefined)? 0 : unit_corpo_cost;

				// sum total for the business
				salary_per_pc += unit_labor_cost * $(this).val();
				salary_per_cc += unit_corpo_cost * $(this).val();

			}
		});
		
		// display
		$("#salary_per_pc_"+business_code).html(number_format(salary_per_pc*12,0));
		$("#salary_per_cc_"+business_code).html(number_format(salary_per_cc*12,0));

		// inner value
		$("#salary_per_pc_"+business_code).attr('val', number_format(salary_per_pc*12,4));
		$("#salary_per_cc_"+business_code).attr('val', number_format(salary_per_cc*12,4));

		let t3_business_total = 0;
		// get input value
		$("#table_three input[business_id="+business_code+"]").each(function(){
			t3_business_total += calculate_format($(this).attr("val"));
		});

		t3_business_total = t3_business_total + (salary_per_pc + salary_per_cc)*12
		// display
		$("#t3_total_"+business_code).html(number_format(t3_business_total,0));

		// inner value
		$("#t3_total_"+business_code).attr('val', number_format(t3_business_total,4));
		
		let total_salary_per_pc = 0, total_salary_per_cc = 0, t3_all_total = 0;
		// total_salary_per_pc
		$("[id^='" + "salary_per_pc" + "']" ).each(function(val){
			total_salary_per_pc += calculate_format($(this).attr('val'));
		});

		// total_salary_per_cc
		$("[id^='" + "salary_per_cc" + "']" ).each(function(val){
			total_salary_per_cc += calculate_format($(this).attr('val'));
		});

		// t3_all_total
		$("[id^='" + "t3_total_" + "']" ).each(function(val){
			t3_all_total += calculate_format($(this).attr('val'));
		});

		// display
		$("#total_salary_per_pc").html(number_format(total_salary_per_pc,0));
		$("#total_salary_per_cc").html(number_format(total_salary_per_cc,0));
		$("#t3_all_total").html(number_format(t3_all_total,0));

		// inner value
		$("#total_salary_per_pc").attr('val', number_format(total_salary_per_pc,4));
		$("#total_salary_per_cc").attr('val', number_format(total_salary_per_cc,4));
		$("#t3_all_total").attr('val', number_format(t3_all_total,4));

	}

	}
}

/***
 * Input Calculation in Table Three
 ***/
function onT3AmountChangeHandler() {
	validation($(this));

	var business_id = $(this).attr("business_id");
	var row = $(this).attr("row");
	
	var old_value = $(this).attr("val")
		? calculate_format($(this).attr("val"))
		: 0;
	var new_value =
		$(this).val() == "" || $(this).val() == "-"
			? 0
			: calculate_format($(this).val());
	var diff_value = new_value - old_value;

	//select node
	var total_adjust_node = $("#total_adjust_" + row);
	var total_salary_node = $("#t3_total_" + business_id);
	var all_total_salary_node = $("#t3_all_total");

	//get Total value
	var total_adjust = calculate_format(total_adjust_node.attr("val"));
	//var total_salary = calculate_format(total_salary_node.text());
	var total_salary = calculate_format(total_salary_node.attr("val"));
	var all_total_salary = calculate_format(all_total_salary_node.attr("val"));

	//calculate
	var new_total_adjust = total_adjust + diff_value;
	var new_total_salary = total_salary + diff_value;
	var new_all_total_salary = all_total_salary + diff_value;

	//push in table three
	total_adjust_node.text(number_format(new_total_adjust, 0));
	total_adjust_node.attr("val", number_format(new_total_adjust, 4));

	total_salary_node.text(number_format(new_total_salary, 0));
	total_salary_node.attr("val", number_format(new_total_salary, 4));

	all_total_salary_node.text(number_format(new_all_total_salary, 0));
	all_total_salary_node.attr("val", number_format(new_all_total_salary, 4));
}

function calculate_format(num) {
	if (num != undefined) {
		let str = num.toString();
		return parseFloat(str.replace(/([,\s]+)/g, "")); //remove comma and space
	}
}
function number_format(num, deci_place = 2) {
	return accounting.formatNumber(num, deci_place, ",", "."); // 4,999.99
}

//loading picture
function loadingPic() {
	var ua = window.navigator.userAgent;
	var msie = ua.indexOf("MSIE ");

	if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
		var el = document.getElementById("imgLoading");
		var i = 0;
		var pics = [
			"<?php echo $this->webroot; ?>img/loading1.gif",
			"<?php echo $this->webroot; ?>img/loading2.gif",
			"<?php echo $this->webroot; ?>img/loading3.gif",
			"<?php echo $this->webroot; ?>img/loading4.gif",
		];

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

/*******
 * Input Events Common function
 *******/
function validation($this) {
	//if enter dot, turn 0.
	if ($this.val() == ".") {
		$this.val("0.");
	}

	//remove not number, minus, dot
	$this.val($this.val().replace(/[^0-9\.\-]/g, ""));

	//only 9 digits with 4 decimal places!
	var reg = /^-?[0-9]{0,9}(\.[0-9]{0,5})?$/;
	if (!reg.test($this.val())) {
		scrollText();
		$this.val("0.00");
		$("#success").empty();
		$("#successErrorMsg")
			.html(`<div class="error">${errMsg(commonMsg.JSE084)}</div>`)
			.show();

		return true;
	}
}

function keyupHandler(e) {
	// check user and position(khm)

	var reg = /^\-$/; //if enter minus only

	//if enter minus only or nothing, old val is 0
	if (reg.test($(this).val()) || $(this).val() == "") {
		$(this).attr("val", 0);
	} else {
		$(this).attr("val", calculate_format($(this).val()));
	}

	//if enter minus only, turn red
	if (reg.test($(this).val())) {
		$(this).addClass("negative");
	}
	checkNegative();
}

function focusHandler() {
	var val = calculate_format($(this).attr("val"));
	if (!val) {
		$(this).val("");
	} else {
		$(this).val($(this).attr("val"));
	}
}

function focusoutHandler() {
	var val = calculate_format($(this).val());
	var user_id = $(this).attr("user_id");
	if (!val) {
		$(this).val("0.00");
	} else {
		$(this).val(number_format(val));
	}
	checkNegative();
	var person_count =
		calculate_format($("td[total_new_id=" + user_id + "]").text()) +
		calculate_format($("td[total_exist_id=" + user_id + "]").text());
	$("#b_person_count_" + user_id).text(number_format(person_count, 4));
}

function t3FocusoutHandler() {
	var val = calculate_format($(this).val());
	if (!val) {
		$(this).val("0");
	} else {
		$(this).val(number_format(val, 0));
	}
	checkNegative();
}

function scrollText() {
	$("html, body").animate({ scrollTop: 0 }, "slow");
}

function checkNegative() {
	var td = $("table td"); //get all td in table
	var reg = /^\-/; //start with minus in input field

	//loop all td
	td.each((_, node) => {
		//get child in td
		if (node.childNodes.length == 1) {
			//not input field but number in td
			var textNode = node.childNodes[0];
			var num = calculate_format(textNode.textContent);
			if (!isNaN(num)) {
				if (num < 0 || reg.test(textNode.textContent)) {
					node.classList.add("negative");
				} else {
					node.classList.remove("negative");
				}
			}
		} else if (node.childNodes.length == 3) {
			//input field in td
			var inputNode = node.childNodes[1];
			var num = calculate_format(inputNode.value);

			if (!isNaN(num)) {
				if (num < 0 || reg.test(inputNode.value)) {
					inputNode.classList.add("negative");
				} else {
					inputNode.classList.remove("negative");
				}
			}
		}
	});
}

/***
 * Save, Download, Filter
 */
function onSaveHandler(btn = '') {
	// document.querySelector("#error").innerHTML = "";
	// document.querySelector("#success").innerHTML = "";
	$('#successErrorMsg').empty();

	var new_adjustment = $("#new_adjustment").attr("val");
	var exist_adjustment = $("#exist_adjustment").attr("val");
	var total_adjustment = Number(new_adjustment) + Number(exist_adjustment);
	var new_position_name = $('.position_name');
	var new_user_name = $('.new_user');
	let errorFlag = false;
	let exceededValArr = [];
	let negativeResult = [];
	if(btn == ''){
		$("#approved_flag").val("1");
		$("#confirm_message").val(SAVE_COMFIRM_MSG1);
	}else{
		$("#approved_flag").val("2");
		$("#confirm_message").val(COMFIRM_MSG);
	}

	var personCountTotal = document.querySelectorAll("td.b_person_count");
	var tdTotal = document.querySelectorAll("td.emp_tot");
	personCountTotal.forEach(function(td) {
		var empId =$(td).attr("id").split("_");
		empId = empId[empId.length - 1];
		let noOfEmp = document.querySelectorAll("td.number_of_emp_"+empId);
		let noOfNewEmp = document.querySelectorAll("td.emp_" + empId);
		let bPersonTotal = parseFloat($('td#b_person_count_total_'+empId).text());
		let bPersonNewTotal = parseFloat($('td#person_total_'+empId).text());

		tdTotal.forEach(function (td) {
			var existEmp = $(td).attr("total_exist_id");
			if(existEmp != undefined) existEmp.includes("_") ? newEmpId = existEmp : existEmp;
			if(newEmpId != undefined) {
					newEmpId = newEmpId.split("_");
					var newEmpId = newEmpId[newEmpId.length - 1];
			}
			var totalValue = existEmp != undefined ? parseFloat($("td#b_person_count_" + existEmp).text()) : 0;
			var newTotalValue = newEmpId != undefined ? parseFloat($("td#person_" + newEmpId).text()) : 0;
			if(totalValue > 1) {
				if(existEmp == empId) {
					exceededValArr.push(existEmp);
					noOfEmp.forEach(function(td){
						td.classList.add("bg-color");
					});
				}
			}
			// change red bg color when number of Personnel Total is greater than Budget Personnel Result for existing user
			if(totalValue > bPersonTotal){
				if(existEmp == empId) {
					negativeResult.push(existEmp);
					noOfEmp.forEach(function(td){
						td.classList.add("bg-color");
					});
				}
			}
			if(newTotalValue > 1) {
				if(newEmpId == empId){
					exceededValArr.push(existEmp);
					noOfNewEmp.forEach(function(td){
						td.classList.add("bg-color");
					});
				}
			}
			// change red bg color when number of Personnel Total is greater than Budget Personnel Result for new user
			if(newTotalValue > bPersonNewTotal){
				if(newEmpId == empId){
					negativeResult.push(existEmp);
					noOfNewEmp.forEach(function(td){
						td.classList.add("bg-color");
					});
				}
			}
		});
	});
	exceededValArr = [...new Set(exceededValArr)];
	negativeResult = [...new Set(negativeResult)];
	if(exceededValArr.length > 0) {
		$("#successErrorMsg")
		.html(`<div class="error">${errMsg(commonMsg.JSE093, [TOTAL_ERROR])}</div>`)
		.show();
		errorFlag = true;	
	} else if(negativeResult.length > 0){
		$("#successErrorMsg")
		.html(`<div class="error">${errMsg(commonMsg.JSE098)}</div>`)
		.show();
		errorFlag = true;
	} else if(new_user_name.val() != "" && new_position_name.val() == 0) {
		$("#successErrorMsg")
		.html(`<div class="error">${errMsg(commonMsg.JSE099)}</div>`)
		.show();
		errorFlag = true;
	} else if(new_user_name.val() == "" && new_position_name.val() != 0){
		$("#successErrorMsg")
		.html(`<div class="error">${errMsg(commonMsg.JSE001,[NEW_USER])}</div>`)
		.show();
		errorFlag = true;
	}else if(total_adjustment > 1){
		$("#new_adjustment").addClass("bg-color");
		$("#exist_adjustment").addClass("bg-color");
		$("#successErrorMsg")
		.html(`<div class="error">${errMsg(commonMsg.JSE093, [TOTAL_ERROR])}</div>`)
		.show();
		errorFlag = true;
	}
	let content, first_btn, sec_btn, btn_type1, btn_type2, save_type;
	if (!errorFlag) {
		$.ajax({
			type: "post",
			url: URL + "LaborCostDetails/checkSaveMerge",
			data: {
				target_year: $("#target_year").val(),
				layer_code: $("#group_code").val(),
			},
			dataType: "json",
			success: function (datas) {
				content = $("#confirm_message").val();
				first_btn = YES1;
				sec_btn = NO1;
				save_type = true;
				btn_type1 = "btn-info";
				btn_type2 = "btn-default";
				// if (!datas.row_count) {
				// 	content = $("#confirm_message").val();
				// 	first_btn = YES1;
				// 	sec_btn = NO1;
				// 	save_type = true;
				// 	btn_type1 = "btn-info";
				// 	btn_type2 = "btn-default";
				// } else {
				// 	content = SAVE_COMFIRM_MSG2;
				// 	first_btn = YES2;
				// 	sec_btn = NO2;
				// 	save_type = false;
				// 	btn_type1 = "btn-info";
				// 	btn_type2 = "btn-default";
				// }
				$.confirm({
					title: SAVE_COMFIRM_TITLE,
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
							btnClass: "btn-info",
							action: function () {
								if (!datas.row_count){
									loadingPic();
									/***
									 * Labor Cost Details(Table One)
									 */
									const formattedDataT1 = [];
									// loop amount_input in table one and get data all i want
									// TABLE_ONES.map((index, input) => {
									$('#table_one input.amount_input').each(function(index, input) {
										let laborCost,comment;
										let business_type = input.getAttribute("business_type");
										let user_id = input.getAttribute("user_id");
										let new_user_name = input.getAttribute("new_user_name");
										let position_code = input.getAttribute("position_code");
										let business_id = input.getAttribute("business_id");
										let person_count = input.getAttribute("val");
										let lcd_id = input.getAttribute("lcd_id");
										if(user_id == 0 && new_user_name != null) {
											comment = (business_type == 1) 
														? $("#exist_comment_" + new_user_name.replace(/ /g, "_")).val()
														: $("#new_comment_" + new_user_name.replace(/ /g, "_")).val();
											
										}else{
											comment = (business_type == 1) 
														? $("#exist_comment_" + user_id).val()
														: $("#new_comment_" + user_id).val();
														console.log(comment);
										}
										
										//if having data state, update data
										if (lcd_id) {
											laborCost = {
												id: lcd_id,
												bu_term_id: BU_TERM_ID,
												person_count: person_count,
												comment: comment,
												updated_by: LOGIN_ID,
											};
										} else {

											//initial state, add data
											// if(position_code != 0 && (new_user_name == '' || new_user_name == null)) {
											laborCost = {
												bu_term_id: BU_TERM_ID,
												target_year: $("#target_year").val(),
												layer_code: business_id,
												user_id: user_id,
												position_code: position_code,
												person_count: person_count,
												business_type: business_type,
												created_by: LOGIN_ID,
												updated_by: LOGIN_ID,
												new_user_name: (new_user_name == '') ? null : new_user_name,
												comment: comment,
											};
											}
										// }
										formattedDataT1.push(laborCost);
									});
									$("#labor-costs-input").val(JSON.stringify(formattedDataT1));
									/***
									 * Labor Cost Adjustment (Table Three)
									 */
									const formattedDataT3 = [];
									// loop amount_input in table three and get data all i want
									TABLE_THREE.map((index, input) => {
										let adjustment;
										//if having data state
										if (input.getAttribute("lca_id")) {
											adjustment = {
												bu_term_id: BU_TERM_ID,
												id: input.getAttribute("lca_id"),
												//adjust_amount: input.value,
												adjust_amount: input.getAttribute("val"),
												updated_by: LOGIN_ID,
											};
										} else {
											adjustment = {
												bu_term_id: BU_TERM_ID,
												target_year: $("#target_year").val(),
												layer_code: input.getAttribute("business_id"),
												adjust_name: input.getAttribute("adjust_name"),
												//adjust_amount: input.value,
												adjust_amount: input.getAttribute("val"),
												created_by: LOGIN_ID,
												updated_by: LOGIN_ID,
											};
										}
										formattedDataT3.push(adjustment);
									});
									$("#labor-adjustments-input").val(
										JSON.stringify(formattedDataT3)
									);
									/****
									 * Budget
									 ***/
									//total value and save in budget
									let target_year = $("#target_year").val();
									let group_code = $("#group_code").val();
									let lastRow = $("#table_three").find("tr").last().children(); //get last total row
									let budget = [];
									let line = [];
									//loop last row
									lastRow.map((i, cell) => {
										let amount = calculate_format(cell.getAttribute("val"));
										let line_code = cell.getAttribute("line_code");
										let business_code = cell.getAttribute("business_code");
										if (!isNaN(amount)) {
											//if not text cell
											if (line_code) {
												//if include line code attribute in cell, it is line and business cell
												line[line_code] = line[line_code]
													? line[line_code] + amount
													: amount;
												budget.push({
													bu_term_id: BU_TERM_ID,
													target_year: target_year,
													layer_code: business_code,
													amount: amount,
												});
											} else {
												//if not, it is group cell
												budget.push({
													bu_term_id: BU_TERM_ID,
													target_year: target_year,
													layer_code: group_code,
													amount: amount,
												});
											}
										}
									});
									line.map((amount, line_code) => {
										budget.push({
											bu_term_id: BU_TERM_ID,
											target_year: target_year,
											layer_code: line_code,
											amount: amount,
										});
									});
									$("#budget-amount").val(JSON.stringify(budget));
									/***
									 * Labor Cost
									 ***/
									let LC = {};
									let USER = {};
									let exist = $("td[total_exist_id]");
									let useridArr = [];
									exist.map((index, td) => {
										let user_id = td.getAttribute("total_exist_id");
										let exist_count = calculate_format(
											$("td[total_exist_id=" + user_id + "]").attr("val")
										);
										let new_count = calculate_format(
											$("td[total_new_id=" + user_id + "]").attr("val")
										);

										LC[user_id] = new_count + exist_count;
										if(user_id.indexOf('total_') != -1 && td.getAttribute("position_code") != 0) {
											var p_cnt = new_count + exist_count;
											LC[user_id] = td.getAttribute("user_id")+"_"+td.getAttribute("position_code")+"_"+td.getAttribute("new_user_name")+"_"+p_cnt;
											USER[td.getAttribute("new_user_name")] = td.getAttribute("user_id")+"_"+td.getAttribute("position_code")+"_"+td.getAttribute("new_user_name")+"_"+p_cnt;
											
										}else if(td.getAttribute("position_code") != 0) {
											USER[user_id] = new_count + exist_count;
										}

										useridArr.push(user_id);
									});
									let data = {
										group_code: group_code,
										lc: LC,
										USER: USER,
										useridArr: useridArr
									};
									
									$("#labor_cost").val(JSON.stringify(data));
									document.forms[1].action = URL + "LaborCostDetails/add";
									document.forms[1].submit();
									return true;
								}else {
									loadingPic();
									/***
									 * Labor Cost Details(Table One)
									 */
									const formattedDataT1 = [];
									// loop amount_input in table one and get data all i want
									let arr_len;
									if(OLD_TABLE_ONE.length == 0){
										arr_len = datas["updated_tableOne"].length;
									}else{
										arr_len = OLD_TABLE_ONE.length;

									}
									// TABLE_ONES.map((index, input) => {
									$('#table_one input.amount_input').each(function(index, input) {
										let laborCost, comment;
										let tmp_lcd_id;
										if(OLD_TABLE_ONE.length == 0 && datas["updated_tableOne"][index] != undefined){
											input.setAttribute('lcd_id', datas["updated_tableOne"][index]["LaborCostDetail"]["id"]);
											tmp_lcd_id = datas["updated_tableOne"][index]["LaborCostDetail"]["id"];
										}else{
											tmp_lcd_id = input.getAttribute('lcd_id');
										}
										if(index < arr_len && input.getAttribute('lcd_id')) {
											let business_type = input.getAttribute("business_type");
											let old_person_count =
												OLD_TABLE_ONE.length == 0
													? 0
													: OLD_TABLE_ONE[index]["LaborCostDetail"]["person_count"];
											let user_id = input.getAttribute("user_id");
											let person_count =
												old_person_count != input.getAttribute("val")
													? input.getAttribute("val")
													: datas["updated_tableOne"][index]["LaborCostDetail"]["person_count"];
													
											let old_comment =
												OLD_TABLE_ONE.length == 0
													? ""
													: OLD_TABLE_ONE[index]["LaborCostDetail"]["comment"];
											
											if(user_id == 0 && input.getAttribute("new_user_name") != null) {

												comment = (business_type == 1) 
															? $("#exist_comment_" + input.getAttribute("new_user_name").replace(/ /g, "_")).val()
															: $("#new_comment_" + input.getAttribute("new_user_name").replace(/ /g, "_")).val();
												comment = (comment != old_comment) ? comment : old_comment;
											}else{
												comment = (business_type == 1) 
															? $("#exist_comment_" + user_id).val()
															: $("#new_comment_" + user_id).val();
												comment = (comment != old_comment) ? comment : old_comment;
											}

											laborCost = {
												id: tmp_lcd_id,
												bu_term_id: BU_TERM_ID,
												person_count: person_count,
												comment: comment,
												updated_by: LOGIN_ID,
											};
											
										}else {
											let user_id = input.getAttribute("user_id");
											let business_type = input.getAttribute("business_type");
											let new_user_name = input.getAttribute("new_user_name");
											let position_code = input.getAttribute("position_code");
											let business_id = input.getAttribute("business_id");
											let person_count = input.getAttribute("val");
											
											if(!input.getAttribute('lcd_id') && user_id == 0 && position_code > 0 && new_user_name != null) {
												comment = (business_type == 1) 
														? $("#exist_comment_" + new_user_name.replace(/ /g, "_")).val()
														: $("#new_comment_" + new_user_name.replace(/ /g, "_")).val();
											
												laborCost = {
													bu_term_id: BU_TERM_ID,
													target_year: $("#target_year").val(),
													layer_code: business_id,
													user_id: user_id,
													position_code: position_code,
													person_count: person_count,
													business_type: business_type,
													created_by: LOGIN_ID,
													updated_by: LOGIN_ID,
													new_user_name: new_user_name,
													comment: comment,
												};
											}else {
												if(position_code == null && user_id == null) {
													laborCost = {
														bu_term_id: BU_TERM_ID,
														id: input.getAttribute('lcd_id'),
														target_year: $("#target_year").val(),
														layer_code: business_id,
														user_id: user_id,
														position_code: position_code,
														person_count: person_count,
														business_type: business_type,
														created_by: LOGIN_ID,
														updated_by: LOGIN_ID,
														new_user_name: new_user_name,
														comment: null,
													};
												}
											}
										}
										formattedDataT1.push(laborCost);
										
									});
									$("#labor-costs-input").val(JSON.stringify(formattedDataT1));
									/***
									 * Labor Cost Adjustment (Table Three)
									 */
									const formattedDataT3 = [];
									// loop amount_input in table three and get data all i want
									TABLE_THREE.map((index, input) => {
										
										let adjustment;
										let old_adj_amount =
											OLD_TABLE_TWO.length == 0
												? 0
												: OLD_TABLE_TWO[index]["LaborCostAdjustment"]["adjust_amount"];							
										let adjust_amount =
											old_adj_amount != input.getAttribute("val")
												? input.getAttribute("val")
												: datas["updated_tableTwo"][index]["LaborCostAdjustment"]["adjust_amount"];
										// to get correct value of lca_id
										Object.entries(datas["updated_tableTwo"]).map((tableTwo)=>{
											if(input.getAttribute('lca_id') == tableTwo[1]["LaborCostAdjustment"]["id"]){
												adjustment = {
													bu_term_id: BU_TERM_ID,
													id: input.getAttribute('lca_id'),
													adjust_amount: adjust_amount,
													updated_by: LOGIN_ID,
												};
											}
										});
										// adjustment = {
										// 	bu_term_id: BU_TERM_ID,
										// 	// id: input.getAttribute("lca_id"),
										// 	id: datas["updated_tableTwo"][index]["LaborCostAdjustment"]["id"],
										// 	//adjust_amount: input.value,
										// 	adjust_amount: adjust_amount,
										// 	updated_by: LOGIN_ID,
										// };
										formattedDataT3.push(adjustment);
									});
									$("#labor-adjustments-input").val(
										JSON.stringify(formattedDataT3)
									);
									/****
									 * Budget
									 ***/
									//total value and save in budget
									let target_year = $("#target_year").val();
									let group_code = $("#group_code").val();
									let lastRow = $("#table_three").find("tr").last().children(); //get last total row
									let budget = [];
									let line = [];
									//loop last row
									lastRow.map((i, cell) => {
										let amount = calculate_format(cell.getAttribute("val"));
										let line_code = cell.getAttribute("line_code");
										let business_code = cell.getAttribute("business_code");
										if (!isNaN(amount)) {
											//if not text cell
											if (line_code) {
												//if include line code attribute in cell, it is line and business cell
												line[line_code] = line[line_code]
													? line[line_code] + amount
													: amount;
												budget.push({
													bu_term_id: BU_TERM_ID,
													target_year: target_year,
													layer_code: business_code,
													amount: amount,
												});
											} else {
												//if not, it is group cell
												budget.push({
													bu_term_id: BU_TERM_ID,
													target_year: target_year,
													layer_code: group_code,
													amount: amount,
												});
											}
										}
									});
									line.map((amount, line_code) => {
										budget.push({
											bu_term_id: BU_TERM_ID,
											target_year: target_year,
											layer_code: line_code,
											amount: amount,
										});
									});
									$("#budget-amount").val(JSON.stringify(budget));
									/***
									 * Labor Cost
									 ***/
									let LC = {};let USER = {};let useridArr = [];
									let exist = $("td[total_exist_id]");
									exist.map((index, td) => {
										let user_id = td.getAttribute("total_exist_id");
										let exist_count = calculate_format(
											$("td[total_exist_id=" + user_id + "]").attr("val")
										);
										let new_count = calculate_format(
											$("td[total_new_id=" + user_id + "]").attr("val")
										);
										LC[user_id] = new_count + exist_count;
										if(user_id.indexOf('total_') != -1) {
											var p_cnt = new_count + exist_count;
											LC[user_id] = td.getAttribute("user_id")+"_"+td.getAttribute("position_code")+"_"+td.getAttribute("new_user_name")+"_"+p_cnt;
											USER[td.getAttribute("new_user_name")] = td.getAttribute("user_id")+"_"+td.getAttribute("position_code")+"_"+td.getAttribute("new_user_name")+"_"+p_cnt;
										}else {
											USER[user_id] = new_count + exist_count;
										}
										useridArr.push(user_id);
									});
									let data = {
										group_code: group_code,
										lc: LC,
										USER: USER,
										useridArr: useridArr
									};
									$("#labor_cost").val(JSON.stringify(data));
									document.forms[1].action = URL + "LaborCostDetails/add";
									document.forms[1].submit();
									return true;
								} 
								scrollText();								
							},
						},
						cancel: {
							text: sec_btn,
							btnClass: "btn-default",
							cancel: function () {
								return true;
							},
						},
					},
					theme: "material",
					animation: "rotateYR",
					closeAnimation: "rotateXR",
				});
			},
		});
	}
	scrollText();
}

function onConfirmHandler(){
	onSaveHandler('confirm');
}
function onConfirmCancelHandler(){
	$("#approved_flag").val("1");
	$.confirm({           
		title: SAVE_COMFIRM_TITLE,                  
		icon: 'fas fa-exclamation-circle',                  
		type: 'green',                  
		typeAnimated: true, 
		closeIcon: true,
		columnClass: 'medium',                
		animateFromElement: true,                 
		animation: 'top',                 
		draggable: false,                 
		content: CANCEL_COMFIRM_MSG,                 
		buttons: {                    
			ok: {                 
				text: YES1,                 
				btnClass: "btn-info",                 
				action: function(){
					document.forms[0].action = URL+"LaborCostDetails/changeApprovedLogFlag";
					document.forms[0].method = "POST";
					document.forms[0].submit();
					loadingPic(); 
					return true;
				}                 
			},                      
			cancel : {
				text: NO1,                  
				btnClass: "btn-default",                  
				cancel: function(){						
					return true;						
				}

			},                
		},                  
		theme: 'material',                  
		animation: 'rotateYR',                  
		closeAnimation: 'rotateXR'                  
	});
}

function onFilterHandler() {
	var target_year = $("#target_year").val();
	var group_code = $("#group_code").val();

	document.forms[0].action = URL + "LaborCostDetails";
	document.forms[0].submit();
}

function onDownloadHandler() {
	let users = [];let user = [];
	let exist = $("td[total_exist_id]");
	exist.map((index, td) => {
		let user_id = td.getAttribute("total_exist_id");
		users.push(user_id);
		let exist_count = calculate_format(
			$("td[total_exist_id=" + user_id + "]").attr("val")
		);
		let new_count = calculate_format(
			$("td[total_new_id=" + user_id + "]").attr("val")
		);
		var p_cnt = new_count + exist_count;
		user.push(td.getAttribute("user_id")+"_"+td.getAttribute("position_code")+"_"+td.getAttribute("new_user_name")+"_"+p_cnt);
	});
	$("#users").val(JSON.stringify(users));
	$("#user").val(JSON.stringify(user));
	// original code for excel download ^_~
	// document.forms[0].action = URL + "LaborCostDetails/download";
	// document.forms[0].method = "POST";
	// document.forms[0].submit();

	loadingPic();
	let fileName = 'ビジネス別人員表_.xlsx';
	fetch(URL + "LaborCostDetails/download", {
		method: 'POST',
		body: new FormData(document.forms[0]),
	})
		.then(response => {
			// console.log(response);return;
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
				let blobUrl = window.URL.createObjectURL(blob);
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

function onAddCommentHandler() {
	// document.querySelector("#error").innerHTML = "";
	// document.querySelector("#success").innerHTML = "";
	let comment = document.querySelector("#lcd_comment").value;
	let btn_name = $("button[data-target=#laborCostDetailsCommentModal]").text();
	let errorFlag = false;
	if (comment.length == 0 && btn_name.trim() == BTN_NAME) {
		$(".error-msg")
			.html(`<div class="error">${errMsg(commonMsg.JSE095)}</div>`)
			.show();
		errorFlag = true;
	} else if (comment.length > 500) {
		$(".error-msg")
			.html(`<div class="error">${errMsg(commonMsg.JSE094)}</div>`)
			.show();
		errorFlag = true;
	}
	if(!errorFlag) {
		$('#laborCostDetailsCommentModal').modal('hide');
		loadingPic();
		document.forms[0].action = URL + "Common/saveAndUpdateComment";
		document.forms[0].method = "POST";
		document.forms[0].submit();	
	}
}
function closeBtnClick() {
	document.querySelector(".error-msg").innerHTML = "";
	let saveBtn = document.querySelector("#closeButton").classList.contains('save');
	if(saveBtn) {
		document.querySelector("#lcd_comment").value = "";
	} else {
		document.querySelector("#lcd_comment").value = document.querySelector(".comment-message").textContent;
	}
}

function scrollText() {
	let successpage = $("#successErrorMsg").text();
	let errorpage = $("#successErrorMsg").text();

	if (successpage) {
		$("html, body").animate(
			{
				scrollTop: 0,
			},
			"slow"
		);
	}
	if (errorpage) {
		$("html, body").animate(
			{
				scrollTop: 0,
			},
			"slow"
		);
	}
}

function checkUserPostion($this) {
	if($this.closest('tr').attr('id') != undefined) {
		var remtrid = $this.closest('tr').attr('id').split('_')[2];
		var userID = $("#exist_row_"+remtrid+" .new_user");
		var posID = $("#new_row_"+remtrid+" .position_name option:selected");
		var user = userID.val();
		var position = posID.val();
		if(position == "0" || user == "") {
			showToolTipError($this, errMsg(commonMsg.JSE001, [USER_POS]));
		}else {
			hideToolTipError($this);
		}
	}
}

function showToolTipError(current, errCode) {
	current.val('');
	current.tooltip({
    	trigger: "change",
        placement: "right",
        template: '<div class="tooltip tooltip-error" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
    }).attr("data-original-title", errCode);
        
    current.tooltip("show");
    current.css({"border": "1px","border-style": "groove","border-color": "#f31515"});
	return false;
}

function hideToolTipError(current) {
	current.tooltip("destroy");
	current.css({"border": "0px","border-color": "#bbb"});
}
function closeBtnClick() {
    document.querySelector(".error-msg").innerHTML = "";
    let saveBtn = document.querySelector("#closeButton").classList.contains('save');
    if(saveBtn) {
        document.querySelector("#lcd_comment").value = "";
    } else {
        document.querySelector("#lcd_comment").value = document.querySelector(".comment-message").textContent;
    }
}