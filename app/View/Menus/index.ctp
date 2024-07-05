<?php
echo $this->Html->css('fontawesome-all.min');
echo $this->Html->css('bootstrap.min.css');
echo $this->Html->css('style.css');
echo $this->Html->script('jquery.min.js');
echo $this->Html->script('bootstrap.js');
echo $this->Html->script('script.js');
echo $this->Html->script('moment.min.js');
echo $this->Html->script('bootstrap-datepicker.min.js');
echo $this->Html->script('bootstrap-datetimepicker.js');
?>

<head>
    <title>FINANCIIO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/Sumisho.svg">
    <style>

    </style>
    <script type="text/javascript">
        function ResetPassword() {

            document.forms[0].action = "<?php echo $this->webroot; ?>Users/ResetPassword";
            document.forms[0].method = "POST";
            document.forms[0].submit();
            return true;
        }
    </script>
</head>

<body>
    <div class="content menu-content">
        <div class="row header-row">
            <div class="col-lg-3 col-md-3"></div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-center menu-head">
                <h2><?php echo __("メインメニュー"); ?></h2>
                <button class="logout-btn pull-right">
                    <a href="<?php echo $this->webroot; ?>Logins/logout">
                    <span class=""></span><i class="fa-solid fa-arrow-right-from-bracket"></i>&nbsp;&nbsp;<?php echo __('ログアウト'); ?>
                    </a>
                </button>
            </div>
            <div class="col-lg-3 col-md-3"></div>
        </div>
        <div class="row header-row">
            <div class="col-lg-3 col-md-3 "></div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 menu-container">
                <div class="row">
                    <?php foreach ($menus as $menu_name => $page_name) : ?>
                    <?php $page_name = str_replace(" ", "", $page_name) ?>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12  menu-margin">
                        <a href="<?php echo $this->webroot . $page_name; ?>"
                            class="menu-box"><span style="padding: 2px;"><?php echo __($menu_name); ?></span></a>
                    </div>
                    <?php endforeach ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-3"></div>
        </div>

    </div>
</body>