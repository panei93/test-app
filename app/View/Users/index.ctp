<style>
    table,
    th {
        /*padding: 10px 15px;*/
        /* font-size: 1.2rem !important; */
        /* height: 5rem !important; */

    }

    thead {
        /*position: sticky;*/
        top: 0;
        z-index: 1000;
    }

    .table-container {
        /*position: sticky;*/
        top: 0;

    }

    .nav>li>a {
        padding-left: 14px;
    }

    div#update {
        display: none;
    }

    div#radio_premission label {
        padding: 0px;
    }

    div#radio_premission {
        margin-left: 0px;
    }

    /*.heading_line_title{
        height: 440.99px;
        margin: 0px;
    }*/
    div#permission_wpr {
        display: none;
    }

    .fix_table {
        height: 200px;
    }

    .ba-select-label {
        padding: 0;
    }

    .layer-select-div {
        padding: 0;
        width: 80%;
    }

    .amsify-selection-label {
        width: 100% !important;
        border-top-right-radius: 4px !important;
        border-bottom-right-radius: 4px !important;
    }

    .register_form .btn-save-wpr {
        width: 80.5%;
        display: flex;
        justify-content: flex-end;

    }

    .update-btn {
        float: right;
    }

    .amsify-selection-list {
        margin-left: 5px;
        width: 100% !important;
    }


    @media (max-width:450px) {
        thead {
            /*position: sticky;*/
            top: 0;
            z-index: 1000;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        .table-bordered {
            border: none;
            display: block;
            white-space: nowrap;
            padding: 0;
            height: 40% !important;
        }

        table {
            min-width: 1483px;
        }
        .register_form .btn-save-wpr {
            width: 65%;
        }

    }

    @media (max-width: 992px) {
        .register {
            width: 100% !important;
        }

        .fix_table {
            height: auto;
        }

        #radio_premission {
            padding-left: 35px;
        }
    }

    #btn_browse {
        float: right !important;
        margin-top: 5px;
        margin-left: 15px;
    }

    .disabled a {
        pointer-events: none;
        color: #ccc;
    }

    .jconfirm.jconfirm-material .jconfirm-box {
        padding: 30px 10px 10px 14px;
    }

    #tbl_user .new {
        background-color: #f7d0d742;
    }
    .form-row .col-md-12{
        margin-top:10px;
        margin-bottom:30px;
    }
    .amsify-selection-area .amsify-selection-label .amsify-label  {
        white-space: nowrap; 
        width: 95%; 
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .amsify-selection-area .amsify-selection-list {
        z-index: 1100;
        margin-top:34px;
        margin-left: -1px;

    }
    /* .datepicker {
        z-index: 1100 !important;
    } */
    .register_form .form-group select[multiple]{
        height: 34px;
        border-radius: 4px;
        width: 100%;

    }
    /*
    *user history detail
    */
    #view_history_popup.modal {
		z-index: 2100;
	}
    #view_history_popup > .modal-dialog {
        width: 70% !important;
    }
    .history_link.disabled {
		pointer-events: none;
		color: #ddd;
	}
    .msgfont {
        margin-bottom: 0 !important;
    }
    .create-bulk-pw {
        white-space: nowrap;
        /* background-color: #ffffff !important; */
        padding: 10px 15px;
        /* transition: all 0.5s ease 0s; */
        background: linear-gradient(to right, #ffffff 50%, #2a807f 50%);
        background-size: 200% 100%;
        background-position: 0 0;
        transition: background-position 0.3s ease-out;
        border: 1px solid #00a6a0;
        border-radius: 5px;
        line-height: 1em;
        min-width: 100px;
        color: #00a6a0;
        height: fit-content;
    }
    .create-bulk-pw:not([disabled]):hover {
        color: #fff;
        border: none;
        background-position: -100% 0;
        /* animation-name: hover-eff; */
        animation-duration: 0.4s;
        animation-timing-function: linear;
        animation-iteration-count: 1;
    }
    .create-bulk-pw:focus {
        outline: none;
    }
    .table {
        margin: 0;
    }
</style>
<script>
    var pre_type_order = '';
    $(function() {
        /* HHK */
        //$("#layer_code").amsifySelect();
        $(".amsify-label").css({
            "font-size": "14px",
            "font-family": "Helvetica"
        });
        let emptyPassword = <?php echo $empty_password; ?>;
        if(emptyPassword == 0) {
            $(".create-bulk-pw").prop("disabled", true);
        }
        
        $('#role_name').trigger('change');
        
        // mail sent error message box
        let mailError = <?php echo($session_mail_error);?>;
        if(mailError.length > 0) {
            // $('#mailSentError').modal('show');
            $.ajax({
                url: "<?php echo $this->webroot ?>Users/MailSentError",
                type: 'post',
                dataType: 'json',
                success: function(data) {
                    var str = data.toString();
                    var output = str.split(',').join("<br>");
                    var textContent = `<div style='font-size:16px;text-align:start;'>${output}</div>`;
                    $.confirm({
                        title: '<?php echo __("Mail Sent Error"); ?>',
                        icon: 'fas fa-exclamation-circle',
                        type: 'red',
                        boxWidth: '35%',
                        useBootstrap: false,
                        typeAnimated: true,
                        animateFromElement: true,
                        closeIcon: true,
                        columnClass: 'medium',
                        animation: 'top',
                        draggable: false,
                        content: textContent,
                        buttons: {
                            ok: {
                                text: "<?php echo __("はい"); ?>",
                                btnClass: 'btn-info',
                            }
                        },
                        theme: 'material',
                        animation: 'rotateYR',
                        closeAnimation: 'rotateXR'
                    });
                },
                error: function(e) {
                    console.log(e);
                }
            });
        }
        if(localStorage.getItem('SEARCH_FLAG') != 1 ) {
            localStorage.setItem("SELECTED_LAYER", "");
            localStorage.setItem("SEARCH_FLAG", 0)
        } else {
            localStorage.setItem("SEARCH_FLAG", 0)
        }

        $("button.amsify-select-clear").click(function() {
            event.preventDefault();
            $('.amsify-list li').removeClass('active');
            localStorage.setItem("SELECTED_LAYER", "");
            return true;
        });
        // Zeyar Min 
        // change data format of bootstrap datepicker
        $('.datepicker').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            forceParse: false,
            todayHighlight:true,
            // clearBtn: true,
        });
        var user_layer_code = <?php echo json_encode($user_layer_code); ?>;
        var opt = '<option value="0">-----Select-----</option>';
        opt += '<option value="0"></option>';
        $('#layer_code').append(opt);
        $("#layer_code").amsifySelect();
        
        $("#type_order").change(function(evt, param1) {
            var selectedLayerCode = localStorage.getItem("SELECTED_LAYER");
            selectedLayerCode = selectedLayerCode != "" ? selectedLayerCode.split(',') : "";
            if(pre_type_order != '') localStorage.setItem("SELECTED_LAYER", "");
            var type_order = $('#type_order').val();
            pre_type_order = $('#type_order').val();
            var options = '';
            options += '<option value="0"></option>';
            if(type_order != ""){
                var layer_code = [];
                $.each(user_layer_code, function(index, value) {
                    if(index == type_order) layer_code = value;
                });
                $.each(layer_code, function(index, value) {
                    options += '<option value="'+index+'">'+index+'/'+value+'</option>';
                });
                $('#layer_code').empty();
                $('#layer_code').append(options);
                $("#layer_code").amsifySelect();
            }else{
                $('.amsify-list li').removeClass('active');
                $("#layer_code").show();
                $(".amsify-selection-area").hide();
                $('#layer_code').empty();
                options += '<option value="0"></option>';
                $('#layer_code').append(options);
                $("#layer_code").amsifySelect();
            }

            if(selectedLayerCode.length > 0) {
                //prepare data for multi select edit
                let baCodeName = '<?php echo $check_array; ?>';
                $("#ba_code .amsify-label").text('-----Select-----');
                var text_layer = '';
                $("#ba_code .amsify-list input").each(function(index) {
                    var exist_layer = $(this).val();
                    var list = $(this);
                    $.each(selectedLayerCode, function(index, value) {
                        list.closest("#ba_code .amsify-list .amsify-list-item ").show();
                        if (value == exist_layer) {
                            text_layer = list.closest("#ba_code .amsify-list .amsify-list-item ").text();
                            if (param1 != 'clear') {
                                $("#layer_code option[value='" + value + "']").prop('selected', true);
                                list.closest("#ba_code .amsify-list .amsify-list-item").addClass('active');
                            }
                        } else {
                            $("#ba_code .amsify-label").empty();
                            $("#ba_code .amsify-label").text("-----Select-----");
                        }
                    });
                });
                if (param1 != 'clear') {
                    if (baCodeName == 'empty'){
                        $("#ba_code .amsify-label").text("-----Select-----");
                    } else if (selectedLayerCode.length == 1)
                        $("#ba_code .amsify-label").text(text_layer);
                    else {
                        $("#ba_code .amsify-label").text(selectedLayerCode.length + " selected");
                    }
                } else {
                    $("#ba_code .amsify-selection-list").setAttribute("style", "display: block !important;");
                }    
            } else  {
                $("#ba_code .amsify-label").empty();
                $("#ba_code .amsify-label").text('-----Select-----');
            }
            if(selectedLayerCode == ""){
                $("#ba_code .amsify-label").text('-----Select-----');
            }
            if(type_order.length < 1 ) {
                $("#ba_code .amsify-label").text("-----Select-----");
            } else if(param1 != '') {
                $("#ba_code .amsify-label").text("-----Select-----");
            }

            $(".amsify-list-item").on("click", function() {
                var actVal = $(this)[0].children[0].defaultValue;
                var sess_layer_code = localStorage.getItem("SELECTED_LAYER");
                
                var layer_code = [];
                if(sess_layer_code.includes(actVal)) {
                    $.each(sess_layer_code.split(","), function(index, value) {
                        if(value != actVal) {
                            layer_code.push(value);
                        }else {
                            $("#layer_code option[value='" + actVal + "']").prop('selected', false);
                            $(".amsify-list li input[value='"+actVal+"']").parent().removeClass("active");
                        }
                    });
                }else if(actVal != undefined) {
                    sess_layer_code = (sess_layer_code != "") ? sess_layer_code+","+actVal : actVal;
                    layer_code = sess_layer_code.split(",");
                }
                
                localStorage.setItem("SELECTED_LAYER", "");
                localStorage.setItem("SEARCH_LAYERCODE", "");
                localStorage.setItem("SELECTED_LAYER", layer_code.toString());
                // localStorage.setItem("SEARCH_LAYERCODE", JSON.stringify(layer_code));
                
                var text_layer = '';
                $("#ba_code .amsify-list input").each(function(index) {
                    var exist_layer = $(this).val();
                    var list = $(this);
                    $("#layer_code option[value='" + exist_layer + "']").prop('selected', false);
                    $(".amsify-list li input[value='"+exist_layer+"']").parent().removeClass("active");
                    $.each(layer_code, function(index, values) {
                        if (values == exist_layer) {
                            text_layer = list.closest("#ba_code .amsify-list .amsify-list-item ").text();
                            $("#layer_code option[value='" + values + "']").prop('selected', true);
                            $(".amsify-list li input[value='"+values+"']").parent().addClass("active");
                        }
                    });
                });
                
                if (layer_code.length == 1)
                    $("#ba_code .amsify-label").text(text_layer);
                else if(layer_code.length < 1) 
                    $("#ba_code .amsify-label").text("-----Select-----");
                else {
                    $("#ba_code .amsify-label").text(layer_code.length + " selected");
                }
            });
        });

        // YarZar(02062022) - BA Code Data Change 
        // Get Flat Array data from Database and changed to Hierarachical data format
        // flatData_list = <?php echo json_encode($flat_list); ?>;
        // baTreeList = flatArrayToTree(flatData_list);
        // End - BA Code Data Change 

        //User ID は既に存在しています！ initial state of radio
        let dept_val = $('#department').val();
        $('#permission_wpr').hide();
        if (dept_val == 'Admin') {
            $('#permission_wpr').hide();
        } else if (dept_val == '経理') {
            $('#permission_wpr').show();
        } else if (dept_val == '営業') {
            $('#permission_wpr').hide();
        } else if (dept_val == '予算') {
            $('#permission_wpr').hide();
        }

        $("#uploadfile").change(function() {

            if ($(this).val() != '') {
                let file_name = $(this).prop('files')[0].name;
                let file_lenght = $(this).get(0).files.length

                if (file_lenght != '1') {

                    let newbr = document.createElement("div");
                    let a = document.querySelector("#error").appendChild(newbr);
                    a.appendChild(document.createTextNode(errMsg(commonMsg.JSE024)));
                    document.querySelector("#error").appendChild(a);
                    errorFlag = false;
                    document.querySelector('#contents').style.visibility = "hidden";
                    document.querySelector('#load').style.visibility = "hidden";

                } else {
                    document.forms[0].action = "<?php echo $this->webroot; ?>Users/UserPWImport";
                    document.forms[0].method = "POST";
                    document.forms[0].submit();
                    return true;
                }
            }
        });
        //User ID は既に存在しています！ end initial state of radio
        /* float thead */
        if ($('#tbl_user').length > 0) {

            $("#tbl_user").floatThead({position: 'absolute'});
            // $(".table-responsive").css('overflow-x', 'hidden');
        }


        $('.position').hide();

        //update
        /*Added by PanEiPhyo (20200313)
        Remove old code and get dynamic data from admin_level table*/
        $('#department').on('change click', function() {
            let dept_val = $(this).val();

            if (dept_val == 'Admin') {
                $('#permission_wpr').hide();
            } else if (dept_val == '経理') {
                $('#permission_wpr').show();
            } else if (dept_val == '営業') {
                $('#permission_wpr').hide();
            } else if (dept_val == '予算') {
                $('#permission_wpr').hide();
            }

            setPosition(dept_val);
        });

        $('#position').on('change click', function() {
            let position_val = $(this).val();
            let dept_val = $('#department').val();

            $("#admin_level").val(position_val);
        });

        // YarZar(02062022)
        // Start - BA Code Insert to Combo Tree 
        // comboTreeData = $('#baTree').comboTree({
        //     source: baTreeList,
        //     isMultiple: true,
        //     cascadeSelect: true,
        //     collapse: false
        // });
        // End - BA Code Insert to Combo Tree
        $(".createpw").hover(function() {
            $(this).attr('title', 'メール通知');

        });

        $(".edit").hover(function() {
            $(this).attr('title', '編集');
        });

        $(".remove").hover(function() {
            $(this).attr('title', '削除');
        });

        $(".reset").hover(function() {
            $(this).attr('title', 'リセット');
        });

        // resign date allow backspace and delete key
        $('#resigned_date').keydown(function(event) {
            var key = event.which;
            if (key != 8 && key != 46) {
            event.preventDefault();
            }
        });
    });

    function roleChange(){
        $('#type_order').empty();
        $('#type_order').append("<option value =''>" + '----- Select Layer Type -----' + "</option>");
        var id = $('#role_name').val();
        var layerTyOrder = localStorage.getItem('LAYERTYPEORDER');
        var searchLayerTyOrder = localStorage.getItem('SEARCH_LAYERTYPEORDER');
        var layerTypeOrder = layerTyOrder != "" ? layerTyOrder : searchLayerTyOrder != "" ? searchLayerTyOrder : "";
        localStorage.setItem('LAYERTYPEORDER', '');
        localStorage.setItem('SEARCH_LAYERTYPEORDER', '');
        $.ajax({
            url: '<?php echo $this->webroot; ?>Users/getLayerTypes',
            data: {
                id: id
            },
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if(response) {
                    $('#type_order').empty();
                    $('#type_order').append("<option value =''>" + '----- Select Layer Type -----' + "</option>");
                    $.each(response, function(index, values) {
                        $('#type_order').append("<option value ='" + index + "'>" + values + "</option>");
                    });
                    if(layerTypeOrder != '') {
                        $('#type_order option[value="' + layerTypeOrder + '"]').prop('selected', true);
                        $('#type_order').trigger('change', '');
                    } else {
                        $('#layer_code').empty();
                        $('.amsify-selection-list ul').empty();
                        $('.amsify-label').empty();
                        $('.amsify-label').text('----- Select -----');
                        localStorage.setItem("SELECTED_LAYER", "");
                    }
                } else {
                    $('#type_order').empty();
                    $('#type_order').append("<option value =''>" + '----- Select Layer Type -----' + "</option>");
                }
                
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    }



    //user edit data carry
    function UserList(id, type = null) {
        pre_type_order = '';
        document.querySelector("#error").innerHTML = '';
        document.querySelector("#success").innerHTML = '';
        $('.amsify-list-item').removeClass('active');
        $.ajax({
            type: "POST",
            url: "<?php echo $this->webroot; ?>Users/getUser",
            data: {
                id: id
            },
            dataType: 'json',
            beforeSend: function() {
                loadingPic();
            },
            success: function(data) {
                localStorage.setItem("SELECTED_LAYER", "");
                var sel_layers = data['layer_code'];
                data['layer_code'] = sel_layers.replaceAll("/", ","); 
                localStorage.setItem("SELECTED_LAYER", data['layer_code']);
                const layer_code = data['layer_code'].split(",");

                let primary_id = data['id'];
                let login_id = data['login_id'];
                let user_name = data['user_name'];
                let role_id = data['role_id'];
                let role_name = data['role_name'];
                let email = data['email'];
                let azure_object_id = data['azure_object_id'];
                let joined_date = data['joined_date'];
                let resigned_date = data['resigned_date'];
                let layer_type_order = data['layer_type_order'];
                let position_code = data['position_code'];
                localStorage.setItem('LAYERTYPEORDER', layer_type_order);
                $(".join.datepicker").datepicker("setDate",joined_date);
                $(".resigned.datepicker").datepicker("setDate",resigned_date);

                $("#primary_id").val(primary_id);
                $("#login_id").val(login_id);
                $("#login_id").prop("readonly", true);
                $("#user_name").val(user_name);
                $("#psw_div").hide();
                $("#confirm_psw_div").hide();
                $('#role_name').find(":selected").text();
                $('#role_name option[value="' + role_id + '"]').prop('selected', true);
                $('#layer_code option[value="' + layer_code + '"]').prop('selected', true);
                $('#position option[value="' + position_code + '"]').prop('selected', true);
                $("#joined_date").val(joined_date);
                $("#resigned_date").val(resigned_date);
                $("#email").val(email);
                $("#azure_object_id").val(azure_object_id);
                $('#role_name').trigger('change', layer_type_order);
                //end preparation for multi select edit

                $('#overlay').hide();
                $('#save').hide();
                $('#update').show();
            }
        });

    }

    function ResetPassword(id) {

        document.querySelector("#id").value = id;
        document.forms[0].action = "<?php echo $this->webroot; ?>Users/ResetPassword";
        document.forms[0].method = "POST";
        document.forms[0].submit();
        return true;
    }

    function UserDelete(id) {

        document.querySelector("#error").innerHTML = '';
        document.querySelector("#success").innerHTML = '';
        document.querySelector("#id").value = id;
        var rowsPerPage = <?php echo count($datas) ?>;
        var searchUser = '<?php echo !empty($search_data) ? 'search_user' : '' ?>';
        $("#rows_per_page").val(rowsPerPage);
        $("#search_user").val(searchUser);

        let errorFlag = true;

        let path = window.location.pathname;
        let page = path.split("/").pop();
        document.querySelector('#hid_page_no').value = page;

        if (errorFlag) {
            $.confirm({
                title: "<?php echo __('削除確認'); ?>",
                icon: 'fas fa-exclamation-circle',
                type: 'red',
                boxWidth: '30%',
                useBootstrap: false,
                typeAnimated: true,
                animateFromElement: true,
                animation: 'top',
                draggable: false,
                content: "<?php echo __('データを削除してよろしいですか。'); ?>",
                buttons: {
                    ok: {
                        text: "<?php echo __('はい'); ?>",
                        btnClass: 'btn-info',
                        action: function() {
                            loadingPic();
                            document.forms[0].action = "<?php echo $this->webroot; ?>Users/delete";
                            document.forms[0].method = "POST";
                            document.forms[0].submit();
                            return true;
                        }
                    },
                    cancel: {
                        text: "<?php echo __('いいえ'); ?>",
                        btnClass: 'btn-default',
                        cancel: function() {

                        }
                    }
                },
                theme: 'material',
                animation: 'rotateYR',
                closeAnimation: 'rotateXR'
            });
        }
    }


    function UpdateUserData() {

        document.querySelector("#error").innerHTML = "";
        document.querySelector("#success").innerHTML = "";
        var rowsPerPage = <?php echo count($datas) ?>;
        $("#rows_per_page").val(rowsPerPage);
        errorFlag = true;
        let user_name = document.querySelector("#user_name").value;
        let email = document.querySelector("#email").value;
        let type_order = document.querySelector('#type_order').value;
        let layer_code = document.querySelector("#layer_code").value;
        let role_id = document.querySelector("#role_name").value;
        let position_code = document.querySelector("#position").value;
        let joined_date = document.querySelector("#joined_date").value;
        let resigned_date = document.querySelector('#resigned_date').value;

        let activeValue = [];

        $("#ba_code .amsify-list .active input").each(function(index) {
            activeValue.push($(this).val());
        });
        $('#update_layer_code').val(activeValue);
        if (!checkNullOrBlank(user_name)) {

            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("ユーザ名"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        if (checkSpecialChar(user_name)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE019, ['<?php echo __("User Name") ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        if (!checkNullOrBlank(role_id)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("ロール"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        if (!checkNullOrBlank(email)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("Email"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        if (email !== null && email !== '') {
            let errorFlag_email = true;
            let emails = email.split(",");
            emails.forEach(function(email) {
                if (!validateEmail(email.trim())) {
                    errorFlag_email = false;
                }
            });
            if (!errorFlag_email) {
                let newbr = document.createElement("div");
                let a = document.querySelector("#error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
                    '<?php echo __('Validate Email Format'); ?>'
                ])));
                document.querySelector("#error").appendChild(a);
                errorFlag = false;
            }
        }
        if (!checkNullOrBlank(joined_date)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("入社日"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        if (!checkNullOrBlank(type_order)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("部署種類"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }

        if (!checkNullOrBlank(activeValue)) {

            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("部署"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }

        if(resigned_date != ''){
            if(joined_date >= resigned_date){
                let newbr = document.createElement("div");
                let a = document.querySelector("#error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE088)));
                document.querySelector("#error").appendChild(a);
                errorFlag = false;
            }
        }
        if (!checkNullOrBlank(position_code)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("等級"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }

        let path = window.location.pathname;
        let page = path.split("/").pop();
        document.querySelector('#hid_page_no').value = page;


        if (errorFlag) {
            $.confirm({
                title: "<?php echo __('変更確認'); ?>",
                icon: 'fas fa-exclamation-circle',
                type: 'green',
                typeAnimated: true,
                animateFromElement: true,
                animation: 'top',
                draggable: false,
                boxWidth: '30%',
                useBootstrap: false,
                content: "<?php echo __('データを変更してよろしいですか。'); ?>",
                buttons: {
                    ok: {
                        text: "<?php echo __('はい'); ?>",
                        btnClass: 'btn-info',
                        action: function() {
                            loadingPic();
                            document.forms[0].action = "<?php echo $this->webroot; ?>Users/add";
                            document.forms[0].method = "POST";
                            document.forms[0].submit();
                            return false;
                        }
                    },
                    cancel: {
                        text: "<?php echo __('いいえ'); ?>",
                        btnClass: 'btn-default',
                        cancel: function() {
                            console.log('the user clicked cancel');
                        }
                    }
                },
                theme: 'material',
                animation: 'rotateYR',
                closeAnimation: 'rotateXR'
            });
        }
        scrollText();
        return true;
    }

    function UserRegisterData() {
        document.querySelector("#error").innerHTML = "";
        document.querySelector("#success").innerHTML = "";

        let layerCodeArr =[];
        let login_id = document.querySelector("#login_id").value;
        let user_name = document.querySelector("#user_name").value;
        let role_name = document.querySelector("#role_name").value;
        let email = document.querySelector("#email").value;
        let type_order = document.querySelector('#type_order').value;
        let layer_code = document.querySelector('#layer_code').value;
        let position_code = document.querySelector('#position').value;
        let joined_date = document.querySelector('#joined_date').value;
        let resigned_date = document.querySelector('#resigned_date').value;

        $("#ba_code .amsify-list .active input").each(function(index) {
				layerCodeArr.push($(this).val());
        });


        let errorFlag = true;

        // let selectedValue = [];
        // let selected = document.querySelector('#layer_code');
        // let value = select.options[select.selectedIndex].value;
        // selectedValue.push(value);
        // console.log('selected', selectedValue);


        /*Added by PanEiPhyo (20200313), for access_type permission*/
        let access_type = "";

        let radios = document.getElementsByName('permission');
        for (let i = 0, length = radios.length; i < length; i++) {
            if (radios[i].checked) {
                access_type = radios[i].value;
                break;
            }
        }
        /*End*/
       
        if (!checkNullOrBlank(login_id)) {

            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("ユーザID"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        if (checkSpecialChar(login_id)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE019, ['<?php echo __("ユーザID") ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        if (login_id.indexOf(' ') >= 0) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE063, ['<?php echo __("ユーザID") ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }

        if (!checkNullOrBlank(user_name)) {

            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("ユーザ名"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        if (checkSpecialChar(user_name)) {

            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE019, ['<?php echo __("ユーザ名") ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }

        if (!checkNullOrBlank(role_name)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("ロール"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        if (!checkNullOrBlank(email)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, ['<?php echo __("Email"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        //allow multiple email with comma (edited by khin hnin myo / 23.01.2020)
        if (email !== null && email !== '') {
            let errorFlag_email = true;
            let emails = email.split(",");
            emails.forEach(function(email) {
                if (!validateEmail(email.trim())) {
                    errorFlag_email = false;
                }
            });
            if (!errorFlag_email) {
                let newbr = document.createElement("div");
                let a = document.querySelector("#error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE001, [
                    '<?php echo __('有効なメールアドレス'); ?>'
                ])));
                document.querySelector("#error").appendChild(a);
                errorFlag = false;
            }
        }
        if (!checkNullOrBlank(joined_date)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("入社日"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        if (!checkNullOrBlank(type_order)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("部署種類"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }

        if (!checkNullOrBlank(layerCodeArr)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("部署"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
        if(resigned_date != ''){
            if(joined_date >= resigned_date){
                let newbr = document.createElement("div");
                let a = document.querySelector("#error").appendChild(newbr);
                a.appendChild(document.createTextNode(errMsg(commonMsg.JSE088)));
                document.querySelector("#error").appendChild(a);
                errorFlag = false;
            }
        }
        if (!checkNullOrBlank(position_code)) {
            let newbr = document.createElement("div");
            let a = document.querySelector("#error").appendChild(newbr);
            a.appendChild(document.createTextNode(errMsg(commonMsg.JSE002, ['<?php echo __("等級"); ?>'])));
            document.querySelector("#error").appendChild(a);
            errorFlag = false;
        }
       
        /*Changed by PanEiPhyo (20200313), check layer_code null for Sales TL and Sales Incharge*/
        if (errorFlag) {
            $.confirm({
                title: "<?php echo __('保存確認'); ?>",
                icon: 'fas fa-exclamation-circle',
                type: 'green',
                boxWidth: '30%',
                useBootstrap: false,
                typeAnimated: true,
                animateFromElement: true,
                animation: 'top',
                draggable: false,
                content: "<?php echo __("データを保存してよろしいですか。"); ?>",
                buttons: {
                    ok: {
                        text: "<?php echo __('はい'); ?>",
                        btnClass: 'btn-info',
                        action: function() {
                            loadingPic();
                            document.forms[0].action = "<?php echo $this->webroot; ?>Users/add";
                            document.forms[0].method = "POST";
                            document.forms[0].submit();
                            return false;
                        }
                    },
                    cancel: {
                        text: "<?php echo __('いいえ'); ?>",
                        btnClass: 'btn-default',
                        btnId: 'btn_cancel',
                        cancel: function(msg) {
                            animation: scrollText();
                            $("#btn_cancel").html(msg);
                            $('html, body').animate({
                                scrollTop: 0
                            }, 0);
                        }
                    }
                },
                theme: 'material',
                animation: 'rotateYR',
                closeAnimation: 'rotateXR'
            });
        }
        scrollText();
    }

    function SearchUser() {
        document.querySelector("#error").innerHTML = "";
        document.querySelector("#success").innerHTML = "";
        localStorage.setItem("SEARCH_ROLE", "");
        localStorage.setItem("SEARCH_LAYERTYPEORDER", "");
        localStorage.setItem("SELECTED_LAYER", "");
        var rowsPerPage = <?php echo count($datas) ?>;
        $("#rows_per_page").val(rowsPerPage);

        let layerCodeArr = [];
        let login_id = document.querySelector("#login_id").value;
        let user_name = document.querySelector("#user_name").value;
        let role = document.querySelector("#role_name").value;
        let email = document.querySelector("#email").value;
        let type_order = document.querySelector('#type_order').value;
        let layer_code = document.querySelector('#layer_code').value;
        let position_code = document.querySelector('#position').value;
        let joined_date = document.querySelector('#joined_date').value;
        let resigned_date = document.querySelector('#resigned_date').value;
        $("#ba_code .amsify-list .active input").each(function(index) {
            layerCodeArr.push($(this).val());
        });
        localStorage.setItem("SEARCH_ROLE", role);
        localStorage.setItem("SEARCH_LAYERTYPEORDER", type_order);
        localStorage.setItem("SELECTED_LAYER", layerCodeArr.toString());
        localStorage.setItem("SEARCH_FLAG", 1);

        loadingPic();
        document.forms[0].action = "<?php echo $this->webroot; ?>Users/index";
        document.forms[0].method = "GET";
        document.forms[0].submit();
        return false;
    }


    // <input type="text" id="id"> value =10

    function CreatePW(id) {
        document.querySelector("#error").innerHTML = '';
        document.querySelector("#success").innerHTML = '';

        document.querySelector('#id').value = id;

        let errorFlag = true;
        let path = window.location.pathname;
        let page = path.split("/").pop();
        document.querySelector('#hid_page_no').value = page;

        if (errorFlag) {
            $.confirm({
                title: "<?php echo __('Create Password Confirmation'); ?>",
                icon: 'fas fa-exclamation-circle',
                type: 'green',
                boxWidth: '30%',
                typeAnimated: true,
                animateFromElement: true,
                animation: 'top',
                draggable: false,
                useBootstrap: false,
                content: "<?php echo __('パスワードを作成してもよろしいですか?'); ?>",
                buttons: {
                    ok: {
                        text: "<?php echo __('はい'); ?>",
                        btnClass: 'btn-info',
                        action: function() {
                            loadingPic();
                            document.forms[0].action = "<?php echo $this->webroot; ?>Users/CreatePW_MailSend";
                            document.forms[0].method = "POST";
                            document.forms[0].submit();
                            return false;
                        }
                    },
                    cancel: {
                        text: "<?php echo __('いいえ'); ?>",
                        btnClass: 'btn-default',
                        cancel: function() {

                        }
                    }
                },
                theme: 'material',
                animation: 'rotateYR',
                closeAnimation: 'rotateXR'
            });
        }
    }

    function bulkPassword() {
        document.querySelector("#error").innerHTML = '';
        document.querySelector("#success").innerHTML = '';

        let errorFlag = true;
        let path = window.location.pathname;
        let page = path.split("/").pop();
        document.querySelector('#hid_page_no').value = page;

        if (errorFlag) {
            $.confirm({
                title: "<?php echo __('Create Bulk Password Confirmation'); ?>",
                icon: 'fas fa-exclamation-circle',
                type: 'green',
                boxWidth: '30%',
                typeAnimated: true,
                animateFromElement: true,
                animation: 'top',
                draggable: false,
                useBootstrap: false,
                content: "<?php echo __('すべてのパスワードを作成してもよろしいですか?'); ?>",
                buttons: {
                    ok: {
                        text: "<?php echo __('はい'); ?>",
                        btnClass: 'btn-info',
                        action: function() {
                            loadingPic();
                            document.forms[0].action = "<?php echo $this->webroot; ?>Users/BulkCreatePW";
                            document.forms[0].method = "POST";
                            document.forms[0].submit();
                            return false;
                        }
                    },
                    cancel: {
                        text: "<?php echo __('いいえ'); ?>",
                        btnClass: 'btn-default',
                        cancel: function() {

                        }
                    }
                },
                theme: 'material',
                animation: 'rotateYR',
                closeAnimation: 'rotateXR'
            });
        }
    }


    function scrollText() {
        let successpage = $('#error').text();
        let errorpage = $('.success').text();

        if (successpage) {
            $("html, body").animate({
                scrollTop: 0
            }, "slow");
        }
        if (errorpage) {
            $("html, body").animate({
                scrollTop: 0
            }, "slow");
        }
    }

    // YarZar(02062022) - BA Code Data Change Flat to Hierarchical //
    // This function change for flat array to hierarchical
    // first set map and insert its child by its' id
    let baTreeList;
    let comboTreeData;
    let flatData_list;
    let layer = 0;

    function flatArrayToTree(list) {
        let map = {},
            node, roots = [],
            i;
        for (i = 0; i < list.length; i += 1) {
            if (list[i].layer_code)
                map[list[i].layer_code] = i; // initialize the map        
            list[i].subs = []; // initialize the children
            if (list[i].type_order > layer)
                layer = list[i].type_order;
        }
        for (i = 0; i < list.length; i += 1) {
            node = list[i];
            if (node.parent_id) {
                let jsonObj = JSON.parse(node.parent_id);
                let toMapArr = new Map(Object.entries(jsonObj));;
                let idx = Array.from(toMapArr.values()).pop(); // Get Last value from Array         
                let midx = map[idx];
                if (layer === node.type_order)
                    delete node['subs'];
                if (midx || midx == 0)
                    list[map[idx]].subs.push(node);
            } else {
                roots.push(node);
            }
        }
        return roots;
    }

    // YarZar(02062022) - BA Code Detail //
    // Data from table, to show detail of BA
    // how many Selected ba for that User
    // append in model box with Table
    function BaShowDetail(baCodes) {
        let baArray = baCodes.split(',');
        let table_html = '';
        let baDetailArray = [];
        $.each(baArray, function(key, value) {
            flatData_list.find(element => {
                let idx = element.parent_id.split('.').pop();
                if (element.id === value || element.org_id === value) {
                    baDetailArray.push(element);
                }
            });
        });
        $(".detail tbody tr").remove();
        $.each(baDetailArray, function(key, value) {
            let org_id = value.org_id ? value.org_id : "";
            let parentArray = value.parent_id.split('.');
            let path = "";
            for (let i = 0; i < parentArray.length; i += 1) {
                let getp = flatData_list.find(element => element.id === parentArray[i] || element.org_id ===
                    parentArray[i]);
                if (i != parentArray.length - 1)
                    path = path + getp.title + '  <i class="fa fa-arrow-right"></i>  ';
                else
                    path = path + getp.title;
            }
            table_html += '<tr>';
            table_html += '<td>' + value.title + '</td>';
            table_html += '<td>' + value.id + '</td>';
            table_html += '<td>' + path + '</td>';
            table_html += '</tr>';
        });
        $('.detail').append(table_html);
    }

    /*  
     * show hide loading overlay
     *@Zeyar Min  
     */
    function loadingPic() {
        $("#overlay").show();
        $('.jconfirm').hide();
    }

    /*
    *User History Detail 
    */
    function viewHistory(id) {
		$.ajax({
			type: "POST",
			url: "<?php echo $this->webroot; ?>Users/getUserHistoryData",
			data: {
				id: id
			},
			dataType: "json",
			success: function(data) {
				$(".history").html("");
				var table_html = "";
				table_html += "<tr>";
				table_html += "<th><?php echo __("ユーザID"); ?></th>";
				table_html += "<th><?php echo __("部署種類"); ?></th>";
				table_html += "<th><?php echo __("部署名"); ?></th>";
				table_html += "<th><?php echo __("入社日"); ?></th>";
				table_html += "<th><?php echo __("退職日"); ?></th>";
				table_html += "</tr>";
				$.each(data, function(key, value) {
					table_html += "<tr>";
                    if(key == 0)				
					table_html += '<td rowspan='+data.length+' style="width:100px;vertical-align:middle;text-align:left;">' + value['UserClone']['login_code'] + '</td>';
					table_html += '<td style="width:150px;vertical-align:top;text-align:left;">' + value['LayerTypeN']['layer_type_name'] + '</td>';
					table_html += '<td style="text-align: justify;text-justify: inter-word;vertical-align:top;text-align:left;">' + value['0']['layer_name'] + '</td>';
					table_html += '<td style="width:100px;vertical-align:top;text-align:center;">' +value['UserClone']['joined_date'].split(' ')[0] + '</td>';
					table_html += '<td style="width:100px;vertical-align:top;text-align:center;">' + value['UserClone']['resigned_date'].split(' ')[0] + '</td>';
					table_html += '</tr>';
				});
				$('.history').append(table_html);
			}
		});

	}
</script>
<!-- aznk -->
<div id="overlay">
    <span class="loader"></span>
</div>
<div id='output'>
</div>
<div class="content">
    <div class="register_form">
        <form action="Users/add" class="form-inline" id="UsersIndexForm" method="post" accept-charset="utf-8">
            <div style="display:none;">
                <input type="hidden" name="_method" value="POST" />
                <input type="hidden" name="id" id="id" />
                <!-- hide field for ba code @zeyar min-->
                <input type="hidden" name="update_layer_code" id="update_layer_code" />
                <input type="hidden" name="rows_per_page" id="rows_per_page" />
                <input type="hidden" name="search_user" id="search_user" value="">
            </div>
            <fieldset>
                <legend><?php echo __('ユーザー管理'); ?></legend>
                <div class="success" id="success"><?php echo ($this->Session->check("Message.UserSuccess")) ? $this->Flash->render("UserSuccess") : ''; ?><?php echo ($this->Session->check("Message.SuccessMsg")) ? $this->Flash->render("SuccessMsg") : ''; ?></div>
                <div class="error" id="error"><?php echo ($this->Session->check("Message.Error")) ? $this->Flash->render("Error") : ''; ?></div>
                <?php if($alertMsg != ''){
                ?>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <span class="alert alert-danger" role="alert">
                            <?php echo $alertMsg; ?>
                        </span>
                    </div>
                </div>
                <?php
                }
                ?>
                <div class="form-row">
                    <?php
                        $s_login_id   = ($search_data['search_login_id']!="") ? $search_data['search_login_id'] : "";
                        $s_user_name  = ($search_data['search_user_name']!="") ? $search_data['search_user_name'] : "";
                        $s_role  = ($search_data['search_role'] != "" ? $search_data['search_role'] : "");
                        $s_email  = ($search_data['search_email']!="") ? $search_data['search_email'] : "";
                        $s_type_order = ($search_data['search_type_order']!="") ? $search_data['search_type_order'] : "";
                        $s_layer_code = ($search_data['search_layer_code']!="") ? $search_data['search_layer_code'] : "";
                        $s_position   = ($search_data['search_position']!="") ? $search_data['search_position'] : "";
                        $s_joined_date    = ($search_data['search_joined_date']!="") ? $search_data['search_joined_date'] : "";
                        $s_resigned_date  = ($search_data['search_resigned_date']!="") ? $search_data['search_resigned_date'] : "";
                    ?>
   
                    <!-- Form Group 1 -->
                    <div class="form-group col-md-6">
                        <label class="required control-label"><?php echo __("ユーザID"); ?></label>
                        <!-- create and update-->
                        <input class="form-control form_input" type="text" id="login_id" name="login_id" maxlength="8" value="<?php echo $s_login_id; ?>" />
                    </div>
                    <!-- Form Group 2 -->
                    <div class="form-group col-md-6">
                        <label class="control-label required"><?php echo __("ユーザ名"); ?></label>
                        <input class="form-control form_input" id="user_name" name="user_name" type="text" maxlength="200" value="<?php echo $s_user_name; ?>" />

                    </div>
                    <!-- Form Group 3 -->
                    <div class="form-group col-md-6">
                        <label class="control-label required"><?php echo __("ロール"); ?></label>
                        <!-- create and update -->
                        <!-- <input type="hidden" id="admin_level" name="admin_level"> -->
                        <select id="role_name" name="role" class="form-control form_input" onchange="roleChange()">
                            <option value=""><?php echo __("----- Select Role -----"); ?></option>
                            <?php foreach ($role_name as $key => $value) : ?>
                                <?php
                                    if ($value['Role']['id'] == $s_role) {
                                        $select = 'selected';
                                    } else {
                                        $select = '';
                                    }
                                ?>
                                <option value="<?php echo $value['Role']['id']; ?>" <?= $select ?>>
                                    <?php echo $value['Role']['role_name']; ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <!-- Form Group 4 -->
                    <div class="form-group col-md-6">
                        <label class="control-label required"><?php echo __("メール"); ?></label>
                        <!-- create and update -->
                        <input class="form-control form_input" id="email" name="email" type="text" value="<?php echo $s_email; ?>" />
                    </div>
                    <!-- Form Group 5 -->
                    <div class="form-group col-md-6">
                        <label class="control-label required"><?php echo __("入社日"); ?></label>
                        <div class="input-group date form_input join datepicker" data-provide="datepicker" style="padding:0px;">
                            <input type="text" class="form-control " id="joined_date" name="joined_date" value="<?php echo $s_joined_date; ?>" autocomplete="off"  style="background-color: #fff;" readonly/>
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                    <!-- Form Group 6 -->
                    <div class="form-group col-md-6">
                        <label class="control-label"><?php echo __("退職日"); ?></label>
                        <div class="input-group date form_input resigned datepicker" data-provide="datepicker" style="padding:0px;">
                            <input type="text" class="form-control" id="resigned_date" name="resigned_date" value="<?php echo $s_resigned_date; ?>" autocomplete="off" style="background-color: #fff;" readonly/>
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                    <!-- Form Group 7 Business Area -->
                    <div class="form-group col-md-6">
                        <label class="col-md-4 control-label required ba-select-label"><?php echo __("部署種類"); ?></label>
                        <select name="type_order" id="type_order" class="form-control form_input" style="margin-left: 5px;">
                            <option value=""><?php echo __("----- Select Layer Type -----"); ?></option>
                        </select>
                    </div>

                    <div class="form-group col-md-6" id="ba_code">
                        <label class="control-label required ba-select-label"><?php echo __("部署"); ?></label>
                        <!-- YarZar(02062022) BA Code Combo Start -->
                        <div class="input-group form_input">
                            <select multiple="multiple" id="layer_code" name="layer_code[]" class="form-control form_input select-input">
                                <option value="" ><?php echo __("----- Select -----"); ?></option>
                            </select>
                        </div>
                        <!-- YarZar(02062022) BA Code Combo ENd -->
                    </div>
                    <div class="form-group col-md-6">
                        <label for="field_name" class="control-label required">
                            <?php echo __('等級'); ?>
                        </label>
                        <select id="position" name="position" class="form-control form_input">
                            <option value="">----- Select Position -----</option>
                            <?php foreach ($position as $position_code => $position_name) : ?>
                                <?php
                                    if ($position_code == $s_position) {
                                        $select = 'selected';
                                    } else {
                                        $select = '';
                                    }
                                ?>
                                <option value="<?= $position_code ?>" <?= $select ?>><?= $position_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Form Group 8 -->
                    <!-- <div class="form-group col-md-6"> -->
                        <!-- <label class="control-label "><?php echo __("AzureオブジェクトID"); ?></label> -->
                        <!-- create and update -->
                        <!-- <input class="form-control form_input" id="azure_object_id" name="azure_object_id" type="text" value="" /> -->
                    <!-- </div> -->
                    <!-- Form Group 9 -->
                    <div class="form-group col-md-6">
                    </div>
                    <!-- Form Group 10 -->
                    <div class="form-group col-md-6">
                        <div class="submit btn-save-wpr" id="save">
                            <input onclick="SearchUser();" type="button" value="<?php echo __("検索"); ?>" class="btn-save search_btn" />
                            <input onclick="UserRegisterData();" type="button" value="<?php echo __("保存"); ?>" class="btn-save" />
                        </div>
                        <!-- update -->
                        <div class="submit btn-save-wpr " id="update">
                            <input type="hidden" name="primary_id" id="primary_id" value="">
                            <input onclick="UpdateUserData();" type="button" value="<?php echo __("変更"); ?>" class="btn-save update-btn" />
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>

<?php if (!empty($datas)) { ?>
    <div class="d-flex justify-content-between" id="total_row" style="padding: 0 15px;">
        <div class="msgfont">
            <?= $count; ?>
        </div>
        <div style="height: 3rem">
            <button class="create-bulk-pw" onclick="bulkPassword()">
                <i class="fa-solid fa-arrow-rotate-right"></i>&nbsp;<?php echo __('一括パスワード作成') ?>
            </button>
        </div>
    </div>
    <div class="table-responsive content">
        <input type="hidden" name="id" id="id" />
        <table class="table table-bordered table-container" id="tbl_user" style="white-space: unset;">
            <thead>
                <tr style=" vertical-align: middle;">
                    <th style="width:5.5%"><?php echo __("ユーザID"); ?></th>
                    <th style="width:9%"><?php echo __("ユーザ名"); ?></th>
                    <th style="width:9%"><?php echo __("ロール"); ?></th>
                    <th style="width:9%"><?php echo __("部署種類"); ?></th>
                    <th style="width:20%"><?php echo __("部署名"); ?></th>
                    <th style="width:9%"><?php echo __("等級"); ?></th>
                    <th style="width:10%"><?php echo __("メール"); ?></th>
                    <th style="width:6%"><?php echo __("入社日"); ?></th>
                    <th style="width:6%"><?php echo __("退職日"); ?></th>
                    <th style="width:6%"><?php echo __("パスワード更新日"); ?></th>
                    <th colspan="5" style="width:13%"><?php echo __("アクション"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($datas as $result) :  ?>
                    <?php
                        /* remove time from dateTime */
                        $join_date = $result['User']['joined_date'];
                    //$join_date_obj = new DateTime($date_time);
                    $join_date_obj = new DateTime($join_date);
                    $strip_join_date = $join_date_obj->format('Y-m-d');

                    $resign_date = $result['User']['resigned_date'];
                    if($resign_date) {
                        $resign_date_obj = new DateTime($resign_date);
                        $strip_resign_date = $resign_date_obj->format('Y-m-d');
                    } else {
                        $strip_resign_date = '';
                    }

                    ?>
                    <tr>
                        <td style="vertical-align:middle;"><?= h($result['User']['login_code']) ?></td>
                        <td style="vertical-align:middle;"><?= h($result['User']['user_name']) ?></td>
                        <td style="vertical-align:middle;"><?= h($result['role']['role_name']) ?></td>
                        <!-- YZLA - BA Code and Name Show In Table -->
                        <?php
                            $layer = $this->Session->read('Config.language') == 'jpn' ? 'name_jp' : 'name_en';
                    $layer_name = h($result[0][$layer]);
                    $layername_implode = implode(', ', array_unique(explode(',', $layer_name)));
                    ?>
                        <td style="vertical-align:middle;"><?= h($result['layer_types'][$layer]) ?></td>
                        <td style="vertical-align:middle;"><?= h($layername_implode) ?></td>
                        <td style="vertical-align:middle;"><?= h($result['positions']['position_name']) ?></td>
                        <!-- End - BA Code and Name Show In Table -->
                        <?php
                    $access_type = $result['User']['access_type'];
                    if ($access_type == 1) {
                        $acc_type =  __("全て");
                    } elseif ($access_type == 2 || $access_type == '') {
                        $acc_type =  __("予実以外");
                    } elseif ($access_type == 3) {
                        $acc_type =  __("予実");
                    } else {
                        $acc_type =  "";
                    }

                    $pw_disable = (!empty($result['User']['password'])) ? 'disabled' : '';

                    if ($pw_disable == '') {

                        $updateDate = '-';
                    } else {

                        $updateDate = (!isset($result['pw']['created_date'])) ?
                            (($result['User']['created_date'] != '0000-00-00 00:00:00') ?
                                h(date("Y-m-d", strtotime($result['User']['created_date']))) : '-') :
                            h(date("Y-m-d", strtotime($result['pw']['created_date'])));
                    }

                    ?>
                        <td style="word-break: break-all; vertical-align:middle;"><?= h($result['User']['email']) ?></td>
                        <td style="word-break: break-all; text-align: center; vertical-align:middle;">
                            <?= h($strip_join_date) ?></td>
                        <td style="word-break: break-all; text-align: center; vertical-align:middle;">
                            <?=  $strip_resign_date; ?></td>

                        <td style="word-break: break-all;width: 100px; text-align: center;vertical-align:middle;">
                            <?= $updateDate ?></td>

                        <td style="word-break: break-all;text-align: center;width: fit-content; vertical-align:middle;font-size:1.3em !important; " class='createpw'>
                            <a class="" href="#" onclick="CreatePW(<?= h($result['User']['id']) ?>)"><i class="fa-regular fa-envelope" title='<?php echo __("パスワードを作成する");?>'></i></a>
                        </td>
                        <td style="word-break: break-all;text-align: center;vertical-align:middle;font-size:1.3em !important;" class='edit'>
                            <a class="" href="#" onclick="UserList(<?= h($result['User']['id']) ?>)" title='<?php echo __("編集");?>'><i class="fa-regular fa-pen-to-square"></i>
                            </a>
                        </td>
                        <td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='remove'>
                            <a class="" href="#" onclick="UserDelete(<?= h($result['User']['id']) ?>)" title='<?php echo __("削除");?>'><i class="fa-regular fa-trash-can"></i></a>
                        </td>
                        <?php $rs_disable = (empty($result['User']['password'])) ? 'disabled' : '' ?>
                        <td style="word-break: break-all;text-align: center;vertical-align:middle; font-size:1.3em !important;" class='reset <?php echo $rs_disable ?>'>
                            <a class="" href="#" onclick="ResetPassword(<?= h($result['User']['id']) ?>)" title='<?php echo __("リセット");?>'><i class="fa-solid fa-arrow-rotate-right"></i></a>
                        </td>
                        <td style="word-break: break-all;text-align: center;width: fit-content; vertical-align:middle;font-size:1.3em !important; ">
                        <?php $disable = ($result['UserB']['history_count']) > 1 ? "" : "disabled" ?>
                        <a class='history_link <?php echo($disable) ?>' data-target='#view_history_popup' data-toggle='modal' data-backdrop='static' data-keyboard='false' href='#' onclick="viewHistory(<?= h($result['User']['id']) ?>)" ; title='<?php echo __("ユーザー履歴の詳細");?>'><i class="fa-solid fa-list-ul"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php } else { ?>
    <div id="err" class="no-data">
        <?php echo ($noDataMsg); ?>
    </div>
<?php } ?>
<?php if (!empty($datas)) { ?>
    <div class="col-md-12" style="padding: 10px;text-align: center;margin-bottom: 50px;">
        <div class="paging">
            <?php
            if ($query_count > Paging::TABLE_PAGING) {
                echo $this->Paginator->first('<<');
                echo $this->Paginator->prev('< ', array(), null, array('class' => 'prev disabled'));
                echo $this->Paginator->numbers(array('separator' => '', 'modulus' => 6));
                echo $this->Paginator->next(' >', array(), null, array('class' => 'next disabled'));
                echo $this->Paginator->last('>>');
            }
            ?>
        </div>
    </div>
<?php } ?>
</div>
<input type="hidden" name="hid_page_no" id="hid_page_no" value="">

</div>
<!--end row -->
</div>
<!--end container -->

<!-- BA Detail PopUp Start -->
<!-- <div class="modal fade" id="ba_detail_popup" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content contantbond">
            <div class="modal-header">
                <button type="button" class="close" id="clearData" data-dismiss="modal">&times;</button>
                <h5 class="modal-title" id="exampleModalScrollableTitle"><?php echo __("事業領域名"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="modal_tbl_wrapper">
                    <table class="table table-striped table-bordered detail" id="tbl_history_Popup">
                        <thead>
                            <tr>
                                <th><?php echo __("Name"); ?></th>
                                <th><?php echo __("Id"); ?></th>
                                <th><?php echo __("Parent Path"); ?></th>
                            </tr>
                        </thead>
                        <tbody class="sortable">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> -->
<!-- BA Detail PopUp End -->

<!-- User History Detail PopUp -->
<div class="modal fade" id="view_history_popup" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content contantbond">
			<div class="modal-header">
				<button type="button" class="close" id="clearData" data-dismiss="modal">&times;</button>
				<h5 class="modal-title" id="exampleModalScrollableTitle"><?php echo __("ユーザー履歴の詳細"); ?></h5>
			</div>
			<div class="modal-body">
                <div class="table-responsive">
					<table class="table table-responsive table-bordered" id="tbl_history_Popup">
						<tbody class="sortable history">
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- error sent password mail -->
<div class="modal fade" id="mailSentError" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button>
        <h5 class="modal-title" id="exampleModalLongTitle"><?php echo __("Mail Sent Error");?></h5>
      </div>
      <div class="modal-body">
        <?php foreach($session_mail_error as $key => $value) : ?>
            <div>
                <?php echo ($value); ?>
            </div>
        <?php endforeach ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php
    echo $this->Form->end();
?>

</div>
</div>
</div>