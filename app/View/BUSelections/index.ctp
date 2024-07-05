<style>
  .mt-20 {
    margin-top: 20px;
  }

  .pr-30 {
    padding-right: 30px;
  }
</style>

<?php
echo $this->Form->create(false, array(
  'url' => 'add',
  'type' => 'post'
));
?>


<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12">
    <h3><?php echo __("Business Comprehensive Analysis"); ?></h3>
    <hr>
    <div class="success" id="success"></div>
    <div class="error" id="error"><?php echo ($this->Session->check("Message.BuError")) ? $this->Flash->render("BuError") : ''; ?></div>

    <!-- added by Khin Hnin Myo (show message from message table) -->
    <?php if ($show_msg != null) { ?>
      <div class="alert alert-success" id="msg">
        <strong><?php echo nl2br(htmlspecialchars($show_msg)); ?></strong>
      </div>
    <?php } ?>

    <div class="form_test">
      <fieldset class="scheduler-border">
        <legend class="scheduler-border"><?php echo __("Basic Selection"); ?></legend>

        <div class="form-group row">
          <div class="col-md-12">
            <p style="color: blue;padding-left: 15px;">
              <?php echo __('Please choose the Term Name, BU and Group, and then click set selection button.'); ?>
            </p>
          </div>

          <div class="col-md-5"> <!-- Term Name Input -->
            <label for="term_name" class="col-md-4 col-form-label required"><?php echo __("期間"); ?></label>
            <div class="col-md-8">
              <select id="term_name" name="term_name" class="form-control">
                <option value=""><?php echo " -- Select " . __("期間") . " --"; ?></option>
              </select>
            </div>
          </div>

          <div class="col-md-5"> <!-- BU Input -->
            <label for="bu_input" class="col-md-4 col-form-label ">
              <?php echo $BU_LABEL; ?></label>
            <div class="col-md-8">
              <select id="bu_input" name="bu_input" class="form-control">
                <option value=""><?php echo "-- Select " . $BU_LABEL . " --"; ?></option>
              </select>
            </div>
          </div>

          <div class="col-md-5 mt-20"> <!-- Group Input -->
            <label for="group_input" class="col-md-4 col-form-label ">
              <?php echo $GROUP_LABEL; ?></label>
            <div class="col-md-8">
              <select id="group_input" name="group_input" class="form-control">
                <option value=""><?php echo "-- Select " . $GROUP_LABEL . " --"; ?></option>
              </select>
            </div>
          </div>
          <div class="col-md-5 mt-20 pr-30" style="text-align: right;">
            <button type="button" class="btn btn-success btn-sumisho" id="btn_set_selection"><?php echo __("設定選択"); ?> </button>
          </div>
        </div>
      </fieldset>
    </div>
  </div>
</div>
<?php
echo $this->Form->end();
?>

<script>
  var langValue = <?= json_encode($LANG); ?>;
  var terms = <?= json_encode($TERMS); ?>;
  var layers = <?= json_encode($LAYERS); ?>;
  var layerSetting = <?= json_encode($layerSetting) ?>;
  var chooseTerm = <?= json_encode($CHOOSE_TERM) ?>;
  var chooseBu = <?= json_encode($CHOOSE_BU) ?>;
  var chooseGroup = <?= json_encode($CHOOSE_GROUP) ?>;
  var selectedTerm = <?= json_encode($this->Session->read('BU_TERM_ID')) ?>;
  var selectedBu = <?= json_encode($this->Session->read('SELECTED_BU')) ?>;
  var selectedGroup = <?= json_encode($this->Session->read('SELECTED_GROUP')) ?>;

  $(document).ready(() => {
    const lang = langValue;
    const buTerms = terms;
    const allLayers = layers;
    const typeOrder = layerSetting;
    const REQD_TERM = chooseTerm;
    const REQD_BU = chooseBu;
    const REQD_GROUP = chooseGroup;
    const SELECTED_TERM = selectedTerm;
    const SELECTED_BU = selectedBu;
    const SELECTED_GROUP = selectedGroup;

    let buLayers = [];
    let groupLayers = [];
    let pariedLayers = [];
    let startDate = undefined;
    let endDate = undefined;

    [buLayers, groupLayers] = seperateLayers(allLayers, typeOrder);
    pariedLayers = getPairedLayers(buLayers, groupLayers, typeOrder);

    showTerms(buTerms)
    $('#term_name').find(`option[value="${SELECTED_TERM}"]`).prop('selected', true);
    
    setTimeout(() => { // set time out, so the action won't be performed before the element is rendered on DOM
      $('#term_name').trigger('change');
      
      $('#bu_input').find(`option[value="${SELECTED_BU}"]`).prop('selected', true);
      $('#bu_input').trigger('change');
      
      $('#group_input').find(`option[value="${SELECTED_GROUP}"]`).prop('selected', true);
    }, 300);

    // check if the term is selected or not. ---
    $('#bu_input').on("click", (event) => {
      event.preventDefault();
      removeError();
      buInputValidator([REQD_TERM]);
    });

    $('#group_input').on("click", (event) => {
      event.preventDefault();
      removeError();
      groupInputValidator([{
        REQD_TERM,
        REQD_BU
      }]);
    });

    $('#term_name').on("click", () => {
      removeError();
    });
    // ---

    $('#btn_set_selection').on("click", (event) => {
      event.preventDefault();
      const termVal = $('#term_name').val();
      const buVal = $('#bu_input').val();
      const groupVal = $('#group_input').val();


      let REQD = [];

      if (termVal == '') {
        REQD.push(REQD_TERM);
      }

      /**
       * -> uncomment if the layer and group are require on the form again,
       * -> currently only the term is required.
       */
      // if (buVal == '') {
      //   REQD.push(REQD_BU);
      // }

      // if (groupVal == '') {
      //   REQD.push(REQD_GROUP);
      // }

      if (REQD.length > 0) {
        showError(REQD);
        return;
      }
      // Prepare the data to send in the POST request
      const formValues = {
        term: termVal,
        bu: buVal,
        group: groupVal
      };

      // Make an AJAX POST request to the save endpoint
      $.ajax({
        method: "POST",
        url: "<?= $this->webroot; ?>BUSelections/add",
        dataType: "json",
        data: {
          formValues: formValues
        }, // Send the data to the server
        success: function(response) {
          showSuccess([response.message]);
        },
        error: function(xhr, status, error) {
          // Handle the error if needed
          console.error("Error: Error Occour");
        }
      });

    });

    $('#term_name').change((event) => {
      const selectedTerm = event.target.value;

      showBu([], lang);
      showGroup([], lang);

      if (selectedTerm > 0) {
        [startDate, endDate] = getStartAndEndDates(buTerms, selectedTerm);
        const termBuLayers = searchLayers(startDate, endDate, buLayers);
        showBu(termBuLayers, lang);
        return;
      }
    });

    $('#bu_input').change((event) => {
      const selecetedBu = event.target.value;
      const gpLayers = searchChildren(selecetedBu, pariedLayers);
      const termBuGroupLayers = searchLayers(startDate, endDate, gpLayers);
      showGroup(termBuGroupLayers, lang);
      return;

    });
  });

  showError = (msg) => {
    removeSuccess();
    $('#error').html(msg.map((msg) => msg + "<br>"));
  }

  removeError = () => {
    $('#error').html('');
    $('#flashMessage').html('');
  }

  showSuccess = (msg) => {
    removeError();
    $('#success').html(msg.map((msg) => msg + "<br>"));
  }

  removeSuccess = () => {
    $('#success').html('');
  }

  buInputValidator = (REQD) => {
    const termVal = $('#term_name').val();

    if (termVal == '') {
      event.preventDefault();
      showError(REQD);
      return;
    }
  }

  groupInputValidator = (REQD) => {
    const termVal = $('#term_name').val();
    const buVal = $('#bu_input').val();
    let visibleErrors = [];

    if (termVal == '') {
      visibleErrors.push(REQD[0].REQD_TERM);
    }

    if (buVal == '') {
      visibleErrors.push(REQD[0].REQD_BU);
    }

    if (termVal == '' || buVal == '') {
      event.preventDefault();
      showError(visibleErrors);
      return;
    }
  }

  getBuTerms = () => {
    return $.ajax({
      method: "POST",
      url: "<?= $this->webroot; ?>BUSelections/getBuTerms",
      dataType: "json"
    });
  }

  getLayers = () => {
    return $.ajax({
      method: "POST",
      url: "<?= $this->webroot; ?>BUSelections/getLayers",
      dataType: "json"
    });
  }

  seperateLayers = (layers, typeOrder) => {
    const firstOrder = typeOrder;
    const secondOrder = (+typeOrder + 1);

    const buLayers = layers.filter((layer) => {
      return layer.type_order == firstOrder
    });

    const groupLayers = layers.filter((layer) => {
      return layer.type_order == secondOrder
    });

    return [buLayers, groupLayers];
  }

  showTerms = (terms) => {
    terms.map((term) => {
      // Sanitize term.term_name using $.text() to prevent HTML injection
      var sanitizedTermName = $('<div>').text(term.term_name).html();

      // Append the option element to the select element
      $('#term_name').append(`<option value="${term.id}">${sanitizedTermName}</option>`);
    })
  }

  showBu = (layers, lang) => {
    $('#bu_input').find('option').not(':first').remove();

    if (layers.length > 0) {
      layers.map((layer) => {
        // $('#bu_input').append(`<option value="${layer.layer_code}">${layer.layer_code}/${ lang == "eng" ? layer.name_en : layer.name_jp}</option>`);
        $('#bu_input').append(`<option value="${layer.layer_code}">${ lang == "eng" ? layer.name_en : layer.name_jp}</option>`);
      })
    }
  }

  showGroup = (layers, lang) => {
    $('#group_input').find('option').not(':first').remove();

    if (layers.length > 0) {
      layers.map((layer) => {
        // $('#group_input').append(`<option value="${layer.layer_code}">${layer.layer_code}/${ lang == "eng" ? layer.name_en : layer.name_jp}</option>`);
        $('#group_input').append(`<option value="${layer.layer_code}">${ lang == "eng" ? layer.name_en : layer.name_jp}</option>`);
      })
    }
  }

  getStartAndEndDates = (terms, id) => {
    const month = terms.find((term) => term.id === id)?.start_month;
    const year = terms.find((term) => term.id === id)?.budget_year;

    const date = new Date(year, month - 1);

    const startDate = $.datepicker.formatDate('yy-mm-dd', new Date(year, month - 1, 1)); // Start date

    const startDateObj = new Date(startDate); // Create a new Date object based on the start date

    startDateObj.setMonth(startDateObj.getMonth() + 11); // Add 11 months to the start date

    const endDate = $.datepicker.formatDate('yy-mm-dd', startDateObj); // Format the end date as 'yy-mm-dd'

    return [
      startDate,
      endDate,
    ];
  }

  searchLayers = (startDate, endDate, layers) => {
    startDateObj = new Date(startDate);
    endDateObj = new Date(endDate);

    const newLayers = layers.filter((layer) => {
      const fromDateObj = new Date(layer.from_date);
      const toDateObj = new Date(layer.to_date);

      //return fromDateObj <= startDateObj && endDateObj <= toDateObj; //my Logic of StartDate & EndDate;
      return fromDateObj <= endDateObj && toDateObj >= startDateObj; // ko hein htet ko logic for StartDate & EndDate;
    });

    return newLayers;
  }

  searchChildren = (parent, pariedLayers) => {
    let children = [];

    pariedLayers.forEach(layer => {
      if (parent == layer.layer_code) {
        layer.child.forEach(layer => {
          children.push(layer);
        })
      }
    });

    return children;
  }

  getPairedLayers = (buLayers, groupLayers, layerOne) => {
    let parent = [];

    for (let buIndex = 0; buIndex < buLayers.length; buIndex++) {
      let currentParent = {
        "id": buLayers[buIndex].id,
        "layer_code": buLayers[buIndex].layer_code,
        "child": []
      };

      for (let gpIndex = 0; gpIndex < groupLayers.length; gpIndex++) {
        const JsonParentId = groupLayers[gpIndex].parent_id;
        const parentId = JSON.parse(JsonParentId);
        const objIndex = `L${layerOne}`;
        // if (groupLayers[gpIndex].layer_code.indexOf(buLayers[buIndex].layer_code) !== -1) { // condition of comparing parent and child layer code
        //   currentParent.child.push(groupLayers[gpIndex]);
        // }
        if (parentId[objIndex].indexOf(buLayers[buIndex].layer_code) !== -1) { // condition of comparing parent layercode to child Ln value, n is dynamic;
          currentParent.child.push(groupLayers[gpIndex]);
        }
      }

      parent.push(currentParent);
    }

    return parent;
  }
</script>