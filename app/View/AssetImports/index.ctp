<?php
echo $this->Form->create(false, array('type' => 'post', 'class' => 'form-inline', 'id' => '', 'enctype' => 'multipart/form-data'));
?>
<style type="text/css">
   #overlay {
      display: none;
      z-index: 1000;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.2);
   }

    .form-horizontal .control-label {
        text-align: left !important;
    }
   #overlay img {
      position: relative;
      top: 40%;
      left: 45%;
   }

   .form-horizontal .control-label {
      text-align: left !important;
   }

   .jconfirm-box-container {
      margin-left: unset !important;
   }
   .form-input {
      margin-top: 5px; 
      padding-left: 0;
      box-sizing: border-box;
   }
   .temp-download {
      float: left;
      line-height: 2.5rem;
      text-align: start;
   }
   .fa-csv-remove .fa-circle-xmark {
      font-size: 1.3rem;
   }
   .filename-div {
      margin-top: 5px;
      display: flex;
      flex-direction: row;
   }
   .upd-file-name {
      margin-left: 1rem;
   }
   .fa-csv-remove {
      cursor: pointer;
      margin-left: 1rem;
      margin-right: 1rem;
   }

</style>

<script type="text/javascript">
   $(document).ready(function() {

      $(".fa-csv-remove").hide();

      $("#uploadfile").change(function() {

         if ($(this).val() != '') {

            var file_name = $(this).prop('files')[0].name;

            $(".fa-csv-remove").show();
            // $("#upd-file-name").empty();
            $("#upd-file-name").html(file_name);

         }

      });
      $("#fa-csv-remove").on('click', function() {
         $("#uploadfile").val('');
         $(".fa-csv-remove").hide();
         $("#upd-file-name").empty();
      });



      var CheckBAcode = "<?php if (!empty($CheckBAcode)) {
                              echo $CheckBAcode;
                           } else {
                              echo '';
                           }  ?>";

      var SkipSameAssetno = "<?php if (!empty($SkipSameAssetno)) {
                                 echo $SkipSameAssetno;
                              } else {
                                 echo '';
                              }  ?>";
      var SkipSameAssetnoflag = "<?php if (!empty($SkipSameAssetnoflag)) {
                                    echo $SkipSameAssetnoflag;
                                 } else {
                                    echo '';
                                 }  ?>";

      var SkipSameAssetnoExcel = "<?php if (!empty($SkipSameAssetnoExcel)) {
                                       echo $SkipSameAssetnoExcel;
                                    } else {
                                       echo '';
                                    }  ?>";

      /* Please Register BA Code */
      if (CheckBAcode == "CheckBAcode") {

         $.ajax({

            url: "<?php echo $this->webroot ?>AssetImports/Check_BAcode",
            type: 'post',
            dataType: 'json',
            beforeSend: function() {
               loadingPic();
            }, 
            success: function(data) {
               if(data != null) {
                  var str = data.toString();
               var output = str.split(',').join(" , ");

               $.confirm({
                  title: '<?php echo __("部署を登録してください"); ?>',
                  icon: 'fas fa-exclamation-circle',
                  type: 'blue',
                  typeAnimated: true,
                  animateFromElement: true,
                  closeIcon: true,
                  columnClass: 'medium',
                  animation: 'top',
                  draggable: false,
                  content: output,
                  buttons: {
                     ok: {
                        text: "<?php echo __("はい"); ?>",
                        btnClass: 'btn-info',
                        action: function() {

                           if (SkipSameAssetnoflag == "SkipSameAssetnoflag") {

                              $.ajax({

                                 url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetnoflag",
                                 type: 'post',
                                 dataType: 'json',
                                 success: function(data) {

                                    var str = data.toString();
                                    var output = str.split(',').join("<br>");

                                    $.confirm({
                                       title: '<?php echo __("スキップ承認された行"); ?>',
                                       icon: 'fas fa-exclamation-circle',
                                       type: 'blue',
                                       typeAnimated: true,
                                       animateFromElement: true,
                                       closeIcon: true,
                                       columnClass: 'medium',
                                       animation: 'top',
                                       draggable: false,
                                       content: output,
                                       buttons: {
                                          ok: {
                                             text: "<?php echo __("はい"); ?>",
                                             btnClass: 'btn-info',
                                             action: function() {

                                                if (SkipSameAssetnoExcel == "SkipSameAssetnoExcel") {

                                                   $.ajax({

                                                      url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetnoExcel",
                                                      type: 'post',
                                                      dataType: 'json',
                                                      success: function(data) {

                                                         var str = data.toString();
                                                         var output = str.split(',').join("<br>");

                                                         $.confirm({
                                                            title: '<?php echo __("CSVで重複スキップ行"); ?>',
                                                            icon: 'fas fa-exclamation-circle',
                                                            type: 'blue',
                                                            typeAnimated: true,
                                                            animateFromElement: true,
                                                            closeIcon: true,
                                                            columnClass: 'medium',
                                                            animation: 'top',
                                                            draggable: false,
                                                            content: output,
                                                            buttons: {
                                                               ok: {
                                                                  text: "<?php echo __("はい"); ?>",
                                                                  btnClass: 'btn-info',
                                                                  action: function() {

                                                                     if (SkipSameAssetno == "SkipSameAssetno") {

                                                                        $.ajax({

                                                                           url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetno",
                                                                           type: 'post',
                                                                           dataType: 'json',
                                                                           success: function(data) {

                                                                              var str = data.toString();
                                                                              var output = str.split(',').join("<br>");

                                                                              $.confirm({
                                                                                 title: '<?php echo __("スキップ重複資産番号行"); ?>',
                                                                                 icon: 'fas fa-exclamation-circle',
                                                                                 type: 'blue',
                                                                                 typeAnimated: true,
                                                                                 animateFromElement: true,
                                                                                 closeIcon: true,
                                                                                 columnClass: 'medium',
                                                                                 animation: 'top',
                                                                                 draggable: false,
                                                                                 content: output,
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
                                                                              $('#overlay').hide();

                                                                           },
                                                                           error: function(e) {
                                                                              console.log(e);
                                                                           }

                                                                        });

                                                                     }

                                                                  }


                                                               }
                                                            },
                                                            theme: 'material',
                                                            animation: 'rotateYR',
                                                            closeAnimation: 'rotateXR'
                                                         });
                                                         $('#overlay').hide();

                                                      },
                                                      error: function(e) {
                                                         console.log(e);
                                                      }

                                                   });

                                                }

                                             }

                                          }
                                       },
                                       theme: 'material',
                                       animation: 'rotateYR',
                                       closeAnimation: 'rotateXR'
                                    });
                                    $('#overlay').hide();

                                 },
                                 error: function(e) {
                                    console.log(e);
                                 }

                              });

                           } else if (SkipSameAssetnoExcel == "SkipSameAssetnoExcel") {

                              $.ajax({

                                 url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetnoExcel",
                                 type: 'post',
                                 dataType: 'json',
                                 success: function(data) {

                                    var str = data.toString();
                                    var output = str.split(',').join("<br>");

                                    $.confirm({
                                       title: '<?php echo __("CSVで重複スキップ行"); ?>',
                                       icon: 'fas fa-exclamation-circle',
                                       type: 'blue',
                                       typeAnimated: true,
                                       animateFromElement: true,
                                       closeIcon: true,
                                       columnClass: 'medium',
                                       animation: 'top',
                                       draggable: false,
                                       content: output,
                                       buttons: {
                                          ok: {
                                             text: "<?php echo __("はい"); ?>",
                                             btnClass: 'btn-info',
                                             action: function() {
                                                if (SkipSameAssetno == "SkipSameAssetno") {

                                                   $.ajax({

                                                      url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetno",
                                                      type: 'post',
                                                      dataType: 'json',
                                                      success: function(data) {

                                                         var str = data.toString();
                                                         var output = str.split(',').join("<br>");

                                                         $.confirm({
                                                            title: '<?php echo __("スキップ重複資産番号行"); ?>',
                                                            icon: 'fas fa-exclamation-circle',
                                                            type: 'blue',
                                                            typeAnimated: true,
                                                            animateFromElement: true,
                                                            closeIcon: true,
                                                            columnClass: 'medium',
                                                            animation: 'top',
                                                            draggable: false,
                                                            content: output,
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
                                                         $('#overlay').hide();

                                                      },
                                                      error: function(e) {
                                                         console.log(e);
                                                      }

                                                   });

                                                }

                                             }
                                          }
                                       },
                                       theme: 'material',
                                       animation: 'rotateYR',
                                       closeAnimation: 'rotateXR'
                                    });
                                    $('#overlay').hide();

                                 },
                                 error: function(e) {
                                    console.log(e);
                                 }

                              });

                           } else if (SkipSameAssetno == "SkipSameAssetno") {

                              $.ajax({

                                 url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetno",
                                 type: 'post',
                                 dataType: 'json',
                                 success: function(data) {

                                    var str = data.toString();
                                    var output = str.split(',').join("<br>");

                                    $.confirm({
                                       title: '<?php echo __("スキップ重複資産番号行"); ?>',
                                       icon: 'fas fa-exclamation-circle',
                                       type: 'blue',
                                       typeAnimated: true,
                                       animateFromElement: true,
                                       closeIcon: true,
                                       columnClass: 'medium',
                                       animation: 'top',
                                       draggable: false,
                                       content: output,
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
                                    $('#overlay').hide();

                                 },
                                 error: function(e) {
                                    console.log(e);
                                 }

                              });

                           }

                        }
                     }
                  },
                  theme: 'material',
                  animation: 'rotateYR',
                  closeAnimation: 'rotateXR'
               });
               $('#overlay').hide();
               }
               $('#overlay').hide();
            },
            error: function(e) {
               console.log(e);
            }

         });

      } else if (SkipSameAssetnoflag == "SkipSameAssetnoflag") {

         $.ajax({

            url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetnoflag",
            type: 'post',
            dataType: 'json',
            success: function(data) {

               if ($.trim(data)) {
                  var str = data.toString();
                  var output = str.split(',').join("<br>");

                  $.confirm({
                     title: '<?php echo __("スキップ承認された行"); ?>',
                     icon: 'fas fa-exclamation-circle',
                     type: 'blue',
                     typeAnimated: true,
                     animateFromElement: true,
                     closeIcon: true,
                     columnClass: 'medium',
                     animation: 'top',
                     draggable: false,
                     content: output,
                     buttons: {
                        ok: {
                           text: "<?php echo __("はい"); ?>",
                           btnClass: 'btn-info',
                           action: function() {

                              if (SkipSameAssetnoExcel == "SkipSameAssetnoExcel") {

                                 $.ajax({

                                    url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetnoExcel",
                                    type: 'post',
                                    dataType: 'json',
                                    success: function(data) {

                                       var str = data.toString();
                                       var output = str.split(',').join("<br>");

                                       $.confirm({
                                          title: '<?php echo __("CSVで重複スキップ行"); ?>',
                                          icon: 'fas fa-exclamation-circle',
                                          type: 'blue',
                                          typeAnimated: true,
                                          animateFromElement: true,
                                          closeIcon: true,
                                          columnClass: 'medium',
                                          animation: 'top',
                                          draggable: false,
                                          content: output,
                                          buttons: {
                                             ok: {
                                                text: "<?php echo __("はい"); ?>",
                                                btnClass: 'btn-info',
                                                action: function() {

                                                   if (SkipSameAssetno == "SkipSameAssetno") {

                                                      $.ajax({

                                                         url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetno",
                                                         type: 'post',
                                                         dataType: 'json',
                                                         success: function(data) {

                                                            var str = data.toString();
                                                            var output = str.split(',').join("<br>");

                                                            $.confirm({
                                                               title: '<?php echo __("スキップ重複資産番号行"); ?>',
                                                               icon: 'fas fa-exclamation-circle',
                                                               type: 'blue',
                                                               typeAnimated: true,
                                                               animateFromElement: true,
                                                               closeIcon: true,
                                                               columnClass: 'medium',
                                                               animation: 'top',
                                                               draggable: false,
                                                               content: output,
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

                                                }

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

                              } else if (SkipSameAssetno == "SkipSameAssetno") {

                                 $.ajax({

                                    url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetno",
                                    type: 'post',
                                    dataType: 'json',
                                    success: function(data) {

                                       var str = data.toString();
                                       var output = str.split(',').join("<br>");

                                       $.confirm({
                                          title: '<?php echo __("スキップ重複資産番号行"); ?>',
                                          icon: 'fas fa-exclamation-circle',
                                          type: 'blue',
                                          typeAnimated: true,
                                          animateFromElement: true,
                                          closeIcon: true,
                                          columnClass: 'medium',
                                          animation: 'top',
                                          draggable: false,
                                          content: output,
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

                           }

                        }
                     },
                     theme: 'material',
                     animation: 'rotateYR',
                     closeAnimation: 'rotateXR'
                  });
               } else {

               }

            },
            error: function(e) {
               console.log(e);
            }

         });

      } else if (SkipSameAssetnoExcel == "SkipSameAssetnoExcel") {

         $.ajax({

            url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetnoExcel",
            type: 'post',
            dataType: 'json',
            success: function(data) {

               if ($.trim(data)) {
                  var str = data.toString();
                  var output = str.split(',').join("<br>");

                  $.confirm({
                     title: '<?php echo __("CSVで重複スキップ行"); ?>',
                     icon: 'fas fa-exclamation-circle',
                     type: 'blue',
                     typeAnimated: true,
                     animateFromElement: true,
                     closeIcon: true,
                     columnClass: 'medium',
                     animation: 'top',
                     draggable: false,
                     content: output,
                     buttons: {
                        ok: {
                           text: "<?php echo __("はい"); ?>",
                           btnClass: 'btn-info',
                           action: function() {

                              if (SkipSameAssetno == "SkipSameAssetno") {

                                 $.ajax({

                                    url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetno",
                                    type: 'post',
                                    dataType: 'json',
                                    success: function(data) {

                                       var str = data.toString();
                                       var output = str.split(',').join("<br>");

                                       $.confirm({
                                          title: '<?php echo __("スキップ重複資産番号行"); ?>',
                                          icon: 'fas fa-exclamation-circle',
                                          type: 'blue',
                                          typeAnimated: true,
                                          animateFromElement: true,
                                          closeIcon: true,
                                          columnClass: 'medium',
                                          animation: 'top',
                                          draggable: false,
                                          content: output,
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

                           }

                        }
                     },
                     theme: 'material',
                     animation: 'rotateYR',
                     closeAnimation: 'rotateXR'
                  });
               } else {

               }

            },
            error: function(e) {
               console.log(e);
            }

         });

      } else if (SkipSameAssetno == "SkipSameAssetno") {

         $.ajax({

            url: "<?php echo $this->webroot ?>AssetImports/Skip_SameAssetno",
            type: 'post',
            dataType: 'json',
            success: function(data) {

               if ($.trim(data)) {
                  var str = data.toString();
                  var output = str.split(',').join("<br>");

                  $.confirm({
                     title: '<?php echo __("スキップ重複資産番号行"); ?>',
                     icon: 'fas fa-exclamation-circle',
                     type: 'blue',
                     typeAnimated: true,
                     animateFromElement: true,
                     closeIcon: true,
                     columnClass: 'medium',
                     animation: 'top',
                     draggable: false,
                     content: output,
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
               } else {

               }

            },
            error: function(e) {
               console.log(e);
            }

         });

      }
   });

   /*  
	*	Show hide loading overlay
	*	@Zeyar Min  
	*/
	function loadingPic() { 
		$("#overlay").show();
		$('.jconfirm').hide();  
	}
   function SaveCSVFile() {
      document.getElementById("success").innerHTML = "";
      document.getElementById("error").innerHTML = "";
      document.getElementById("messageContent").innerHTML = "";

      var csvFile = document.getElementById('uploadfile').files.length;

      var chk = true;

      if (csvFile != '1') {

         var newbr = document.createElement("div");
         var a = document.getElementById("error").appendChild(newbr);
         a.appendChild(document.createTextNode(errMsg(commonMsg.JSE024)));
         document.getElementById("error").appendChild(a);
         chk = false;

      }
      if (chk) {
         var isOkClicked = false;
         $.confirm({
            title: '<?php echo __("保存確認"); ?>',
            icon: 'fas fa-exclamation-circle',
            type: 'blue',
            typeAnimated: true,
            closeIcon: true,
            columnClass: 'medium',
            animateFromElement: true,
            animation: 'top',
            draggable: false,
            content: "<?php echo __("データを保存してよろしいですか。"); ?>",
            buttons: {
               ok: {
                  text: '<?php echo __("はい"); ?>',
                  btnClass: 'btn-info',
                  action: function() {
                     loadingPic();
                     if (isOkClicked == false) {

                        isOkClicked = true;
                        
                        document.forms[0].action = "<?php echo $this->webroot; ?>AssetImports/Save_CSV_File";
                        document.forms[0].method = "POST";
                        document.forms[0].submit();
                        return true;
                     }
                  }
               },
               cancel: {
                  text: '<?php echo __("いいえ"); ?>',
                  btnClass: 'btn-default',
                  cancel: function() {
                     console.log('the user clicked cancel');
                     scrollText();
                     $('#overlay').hide();
                  },
               }
            },
            theme: 'material',
            animation: 'rotateYR',
            closeAnimation: 'rotateXR'
         });
      }
   }
</script>

<div id="overlay">
   <span class="loader"></span>
</div>
<div class="content register_container">                     
    <div class="row register_form" >
        <div class="col-lg-12 col-md-12 col-sm-12">
            <h3 class=""><?php echo __("固定資産インポートCSV");?></h3>
            <hr>

         <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="success" id="success"><?php echo ($this->Session->check("Message.UserSuccess")) ? $this->Flash->render("UserSuccess") : ''; ?></div>
            <div class="error" id="error"><?php echo ($this->Session->check("Message.UserError")) ? $this->Flash->render("UserError") : ''; ?></div>

            <div class="errorSuccess" id="messageContent"><?php if ($this->Session->check('Message.excelError')) : ?>
                  <div class="error" id="sess_error">
                     <?php echo $this->Flash->render("excelError"); ?>
                  </div>
               <?php endif; ?>
            </div>
         </div>
         <div class="errorSuccess" id="messageContent">
            <div class="success" id="success"><?php echo $successMsg; ?></div>
            <div class="error" id="error"><?php echo $errorMsg; ?></div>
         </div>

         <div class="form-row">
            <div class="form-group col-md-5 col-sm-12">
               <label class="control-label"><?php echo __("イベント名"); ?></label>
               <input class='form-control register form_input' style="margin-bottom: 7px;" type="textbox" id='event_name' value='<?php echo $event_name; ?>' disabled="disabled">
            </div>
            <?php if(!empty($buttons)){ ?>
               <div class="col-md-7 col-sm-12">
                  <div class="col-md-3 col-sm-12">
                     <a href="<?php echo $this->webroot ?>templates/assetimports_template.csv" class="temp-download"><u>Get Template <i class="fa-solid fa-file-arrow-down"></i></u></a>
                  </div>
                  <div class="form-group col-md-7 col-sm-12" style="padding-right: 0;">
                     <label style="color:white; float: left;" id="btn_browse" class="control-label"><?php echo __('ブラウズ'); ?>
                        <input type="file" name="uploadfile" id="uploadfile" class="uploadfile btn_sumisho">
                     </label>
                  <!-- </div>
                  <div class="form_input col-md-5 col-sm-12 d-flex flex-row"> -->
                     <div class="filename-div">
                        <span class="upd-file-name" id="upd-file-name"></span>
                        <span class="fa-csv-remove" id="fa-csv-remove"><i class="fa-regular fa-circle-xmark" style="color: red;font-size:1.3rem;"></i></span>
                     </div>
                     
                     <!-- <div class="col-md-4 col-xs-12 sap_excelremovelink" id="sap_excelremovelink" style="text-align: start;">
                        <a href="#" class="sap_excelremove" id="sap_excelremove"><i class="fa-regular fa-circle-xmark" style="color: red;font-size:1.3rem;"></i></a>
                     </div> -->
                  </div>
                  <div class="form-group col-md-2 col-sm-12">
                     <button type="button" class="btn-save" style="margin:unset" onClick="SaveCSVFile();"><?php echo __('保存'); ?></button>
                  </div>
               </div>
               <div class="col-md-6"></div>

            <?php } ?>
         </div>
         <div class="form-row">
         </div>

         <div class="row line">
            <div class="col-md-6">
               <div class="form-group">
               </div>
            </div>
         </div>
         <br><br><br>

         <div class="row line">
            <div class="col-md-1">
               <div class="form-group">
               </div>
            </div>
         </div>
         <br><br>

         <div class="col-md-12">
            <div class="row">
            </div>
         </div>
         <br>
         <br>
         <br>
         <br>
      </div>
   </div>
</div>


<?php
echo $this->form->end();
?>