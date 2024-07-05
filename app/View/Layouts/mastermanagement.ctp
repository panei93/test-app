<?php

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>

<head>
    <?php echo $this->Html->charset(); ?>
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE,IE=11" />
    <title>FINANCIIO</title>
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/Sumisho.svg">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php
    echo $this->Html->css('fontawesome-all.min');
    echo $this->Html->css('bootstrap.min.css');
    echo $this->Html->css('datepicker.min.css');
    echo $this->Html->css('bootstrap-grid.min.css');
    echo $this->Html->css('bootstrap-reboot.min.css');
    echo $this->Html->css('jquery-confirm');
    echo $this->Html->css('bootstrap-datetimepicker.min.css');
    echo $this->Html->css('amsify.select.css');
    echo $this->Html->css('jquery.floatingscroll');
    echo $this->Html->css('jquery-ui.css');
    echo $this->Html->css('style.css');
    echo $this->Html->css('jquery-confirm.css');
    echo $this->Html->css('select2.min.css');
    echo $this->Html->css('all.min.css');
    echo $this->Html->css('fontawesome.min.css');
    echo $this->Html->css('treeview.css');


    echo $this->Html->script('moment.min.js');
    echo $this->Html->script('commonMessage-jp.js');
    echo $this->Html->script('jquery-2.1.4.min.js');
    //   echo $this->Html->script('jquery-3.6.0.min.js');
    echo $this->Html->script('jquery-ui.min.js');
    echo $this->Html->script('jquery-confirm');
    echo $this->Html->script('bootstrap.min.js');
    echo $this->Html->script('moment.min.js');
    echo $this->Html->script('bootstrap-datepicker.min.js');
    echo $this->Html->script('bootstrap-datetimepicker.js');
    echo $this->Html->script('jquery.amsifyselect.js');
    echo $this->Html->script('script.js');
    echo $this->Html->script('jquery-confirm.js');
    echo $this->Html->script('custom.js');
    echo $this->Html->script('sprintf');
    echo $this->Html->script('commonMessage');
    echo $this->Html->script('jquery.floatThead.js');
    echo $this->Html->script('jquery.floatingscroll.min');
    echo $this->Html->script('jquery.tablednd.js');
    echo $this->Html->script('freeze-table.min');
    echo $this->Html->script('select2.min.js');
    echo $this->Html->script('jquery.autocomplete.js');
    echo $this->Html->script('all.min.js');
    echo $this->Html->script('fontawesome.min.js');
    echo $this->Html->script('treeview.js');


    echo $this->fetch('meta');
    echo $this->fetch('css');
    echo $this->fetch('script');

    if ($this->Session->read('Config.language') == 'eng') {
        echo $this->Html->script('commonMessage');
    } else {
        echo $this->Html->script('commonMessage-jp');
    }
    ?>

    <style type="text/css">
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 40px;
        }

        ul#menuone {
            font-family: 'Aileron-Light';
            font-size: 14px;
            color: #000000 !important;
        }

        .transparent-overlay {
            background-color: transparent;
            position: fixed;
            top: 50;
            left: 0;
            right: 0;
            bottom: 0;
            height: 100vh;
            display: none;
            z-index: 1000;
        }

        .nav-bar-collapse {
            display: none;
            position: fixed;
            width: 15%;
            z-index: 100000;
            background-color: #e5ffff;
            border-radius: 5px;
            box-shadow: 0px 5px 10px 2px rgba(0, 0, 0, 0.29);
        }

        .nav-bar-collapse ul li {
            width: 100%;
        }

        .btn-more-menu {
            border: none;
            background-color: #e5ffff;
            color: #9d9d9d;
            font-size: 14px;
            outline: none;
        }

        .btn-more-menu:focus,
        .btn-more-menu:active,
        .btn-more-menu:hover {
            border: none;
            outline: none;
            background-color: #00a6a0;
            color: #ffffff;
        }

        ul.hover-show-menu {
            position: absolute;
            right: 0;
            left: 100%;
            top: 0;
            border: none;
            background-color: #e5ffff;
        }

        .dropdown:hover .hover-show-menu {
            display: block;
        }

        .hover-show-menu li a {
            line-height: 20px;
            padding: 10px 15px;
            color: #9d9d9d;
        }

        .colortest {
            background-color: #00a6a0;
        }

        /* a.click-active:focus,
        a.click-active:active {
            background-color: #025a57 !important;
        } */

        @media screen and (max-width: 576px) {
            .nav-bar-collapse {
                width: 50%;
                z-index: 100000;
                background-color: #e5ffff;
                border-radius: 5px;
                box-shadow: 0px 5px 10px 2px rgba(0, 0, 0, 0.29);
            }

            .nav-bar-collapse ul li {
                width: 93%;
            }

            .nav-bar-collapse ul li a {
                margin-left: 2rem;
            }

        }
    </style>
    <script>
        let langConfig = "<?php echo $this->Session->read('Config.language'); ?>";
        $(document).ready(function() {
            $("#myNavbar [href]").each(function() {
                var controller_name = "<?php echo $this->params['controller']; ?>";
                var str = this.href.indexOf(controller_name);
                if (str != -1) {
                    var getClassName = controller_name.toLowerCase();
                    $("." + getClassName).addClass('drop_show_active');
                    $("." + getClassName).parent().parent().siblings('.pactive').addClass('drop_show_active');

                    // if ($("." + getClassName).parent().parent().get(0).id == 'menuone') {
                    //     $('#menuone').prev().addClass('drop_show_active');
                    // } else if ($("." + getClassName).parent().parent().get(0).id == 'menutwo') {
                    //     $('#menutwo').prev().addClass('drop_show_active');
                    // } else if ($('.' + getClassName).parent().parent().get(0).id == 'menuthree') {
                    //     $('#menuthree').prev().addClass('drop_show_active');
                    // }else if ($('.' + getClassName).parent().parent().get(0).id == 'menufour') {
                    //     $('#menufour').prev().addClass('drop_show_active');
                    // }else if ($('.' + getClassName).parent().parent().get(0).id == 'menufive') {
                    //     $('#menufive').prev().addClass('drop_show_active');
                    // }
                }
                if ($('.nav-bar-collapse .d-flex ul li a').hasClass('drop_show_active')) {
                    $('.more-menu').addClass('drop_show_active');
                }
            });
        });
        /* 
         * zeyar min
         * more menu toggle
         */
        function toggleMore() {
            $(".nav-bar-collapse").toggle();
            $('.transparent-overlay').toggle();
            $('.caret-down').toggle();
            $('.caret-up').toggle();
            // $('.more-menu').addClass('click-active');
        }
        // hide more menu dropdown when click on transparent overlay
        function transOverlayClick() {
            $(".nav-bar-collapse").toggle();
            $('.transparent-overlay').toggle();
            $('.caret-down').toggle();
            $('.caret-up').toggle();
        }
        // hide more menu dropdown when scroll
        window.onscroll = function() {
            $('.nav-bar-collapse').hide();
            $('.transparent-overlay').hide();
            $('.caret-down').show();
            $('.caret-up').hide();
            // $('.more-menu').removeClass('click-active');
        }
    </script>
</head>
<?php
$Common = new CommonController();
$user_level = $this->Session->read('ADMIN_LEVEL_ID');
$pagename = $this->request->params['controller'];
if ($pagename == 'LayerChart') $pagename = 'LayerCharts';

$menus = $Common->getMenuByRoleWithoutLayout($user_level, $pagename);
?>

<body>
    <div class="transparent-overlay" onclick="transOverlayClick()"></div>
    <div id="header">
        <nav class="navbar navbar-inverse">
            <div class="wrap group">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#myNavbar" aria-expanded="false">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="lblSumisho" href="#">
                            <img src="<?= $this->webroot; ?>img/sumisho_logo.svg" class="sumisho-logo">
                        </a>
                    </div>
                    <div class="collapse navbar-collapse" id="myNavbar">
                        <?php
                        if (in_array("Users", $menus)) {
                        ?>
                            <!-- User Master: written by HHK -->
                            <ul class="nav navbar-nav menu_active">
                                <li>
                                    <a class="users" href="<?= $this->webroot; ?>Users"><?php echo __('ユーザー'); ?></a>
                                </li>
                            </ul>
                        <?php
                        }
                        ?>

                        <?php
                        $isLayerTypesVisible = in_array("LayerTypes", $menus);
                        $isLayersVisible = in_array("Layers", $menus);
                        $isLayerChartsVisible = in_array("LayerCharts", $menus);

                        if ($isLayerTypesVisible || $isLayersVisible || $isLayerChartsVisible) {
                        ?>
                            <!-- Layer Master -->
                            <ul class="nav navbar-nav menu_active">
                                <li class="dropdown">
                                    <a class="dropdown-toggle menureportlist pactive" data-toggle="dropdown" href="#"><?php echo __("部署"); ?>
                                        <span><i class="fa-solid fa-caret-down"></i></span></span>
                                    </a>
                                    <ul class="dropdown-menu checklist" id="menuone">
                                        <?php
                                        if ($isLayerTypesVisible) {
                                        ?>
                                            <li>
                                                <a class="layertypes" href="<?= $this->webroot; ?>LayerTypes"><?php echo __("部署種類"); ?></a>
                                            </li>
                                        <?php
                                        }

                                        if ($isLayersVisible) {
                                        ?>
                                            <li>
                                                <a class="layers" href="<?= $this->webroot; ?>Layers"><?php echo __("部署"); ?></a>
                                            </li>
                                        <?php
                                        }

                                        if ($isLayerChartsVisible) {
                                        ?>
                                            <li>
                                                <a class="layerchart" href="<?= $this->webroot; ?>LayerChart"><?php echo __("部署チャート"); ?></a>
                                            </li>
                                        <?php
                                        }
                                        ?>
                                    </ul>
                                </li>
                            </ul>
                        <?php
                        }
                        ?>

                        <?php
                        if (in_array("Roles", $menus)) {
                        ?>
                            <!-- Role Master -->
                            <ul class="nav navbar-nav menu_active">
                                <li>
                                    <a class="roles" href="<?= $this->webroot; ?>Roles"><?php echo __('ロール'); ?></a>
                                </li>
                            </ul>
                        <?php
                        }
                        ?>

                        <?php
                        if (in_array("MailFlowSettings", $menus)) {
                        ?>
                            <!-- Mail Master -->
                            <ul class="nav navbar-nav menu_active">
                                <li>
                                    <a class="mailflowsettings" href="<?= $this->webroot; ?>MailFlowSettings"><?php echo __('メール設定'); ?></a>
                                </li>
                            </ul>
                        <?php
                        }
                        ?>

                        <!-- Tax Fees Rates -->
                        <!-- <ul class="nav navbar-nav menu_active">
                            <li>
                                <a class="rtaxfees" href="<?= $this->webroot; ?>RtaxFees"><?php echo __('税率'); ?></a>
                            </li>
                        </ul> -->
                        <!-- Accounts -->
                        <!-- <ul class="nav navbar-nav menu_active">
                            <li class="dropdown">
                                <a class="dropdown-toggle menureportlist pactive" data-toggle="dropdown" href="#"><?php echo __("勘定科目"); ?>
                                    <span><i class="fa-solid fa-caret-down"></i></span></span>
                                </a>
                                <ul class="dropdown-menu" id="menutwo">
                                    <li>
                                        <a class="accounts" href="<?= $this->webroot; ?>Accounts"><?php echo __('勘定科目'); ?></a>
                                    </li>
                                    <li>
                                        <a class="accountsettings" href="<?= $this->webroot; ?>AccountSettings"><?php echo __('勘定科目設定'); ?></a>
                                    </li>
                                </ul>
                            </li>
                        </ul> -->

                        <?php
                        $isAccountsVisible = in_array("Accounts", $menus);
                        $isAccountSettingsVisible = in_array("AccountSettings", $menus);
                        $isPositionsVisible = in_array("Positions", $menus);
                        $isRexchangesVisible = in_array("Rexchanges", $menus);
                        $isRtaxFeesVisible = in_array("RtaxFees", $menus);
                        $isInterestCostsVisible = in_array("InterestCost", $menus);

                        if ($isAccountsVisible || $isAccountSettingsVisible || $isPositionsVisible || $isRexchangesVisible || $isRtaxFeesVisible || $isInterestCostsVisible) {
                        ?>
                            <!-- Layer Master -->
                            <ul class="nav navbar-nav menu_active">
                                <li class="dropdown">
                                    <a class="dropdown-toggle menureportlist pactive" data-toggle="dropdown" href="#"><?php echo __("ビジネス総合分析"); ?>
                                        <span><i class="fa-solid fa-caret-down"></i></span></span>
                                    </a>
                                    <ul class="dropdown-menu checklist" id="menuone">
                                        <?php
                                        if ($isAccountsVisible) {
                                        ?>
                                            <li>
                                                <a class="accounts" href="<?= $this->webroot; ?>Accounts"><?php echo __('勘定科目'); ?></a>
                                            </li>
                                        <?php
                                        }

                                        if ($isAccountSettingsVisible) {
                                        ?>
                                            <li>
                                                <a class="accountsettings" href="<?= $this->webroot; ?>AccountSettings"><?php echo __('勘定科目設定'); ?></a>
                                            </li>
                                        <?php
                                        }

                                        if ($isPositionsVisible) {
                                        ?>
                                            <li>
                                                <a class="positions" href="<?= $this->webroot; ?>Positions"><?php echo __('人員単価'); ?></a>
                                            </li>
                                        <?php
                                        }

                                        if ($isRexchangesVisible) {
                                        ?>
                                            <li>
                                                <a class="rexchanges" href="<?= $this->webroot; ?>Rexchanges"><?php echo __("為替レート"); ?></a>
                                            </li>
                                        <?php
                                        }

                                        if ($isRtaxFeesVisible) {
                                        ?>
                                            <li>
                                                <a class="rtaxfees" href="<?= $this->webroot; ?>RtaxFees"><?php echo __('税率'); ?></a>
                                            </li>
                                        <?php
                                        }

                                        if ($isInterestCostsVisible) {
                                        ?>
                                            <li>
                                                <a class="interestcost" href="<?= $this->webroot; ?>InterestCost"><?php echo __('金利'); ?></a>
                                            </li>
                                        <?php
                                        }
                                        ?>
                                    </ul>
                                </li>
                            </ul>
                        <?php
                        }
                        ?>

                        <!-- Budget & Result Managent -->
                        <!-- <ul class="nav navbar-nav menu_active">
                            <li class="dropdown">
                                <a class="dropdown-toggle menureportlist pactive" data-toggle="dropdown" href="#"><?php echo __("予算と結果"); ?>
                                    <span><i class="fa-solid fa-caret-down"></i></span></span>
                                </a>
                                <ul class="dropdown-menu" id="menufour">
                                    <li>
                                        <a class="brmterms" href="<?php echo $this->webroot; ?>BrmTerms"><?php echo __("期間"); ?></a>
                                    </li>
                                    <li>
                                        <a class="brmaccounts" href="<?php echo $this->webroot; ?>BrmAccounts"><?php echo __('勘定科目'); ?></a>
                                    </li>
                                    <li>
                                        <a class="brmsaccounts" href="<?php echo $this->webroot; ?>BrmSaccounts"><?php echo __('小勘定科目'); ?></a>
                                    </li>
                                    <li>
                                        <a class="brmaccountsetup" href="<?php echo $this->webroot; ?>BrmAccountSetup?param='url'"><?php echo __('アカウント設定'); ?></a>
                                    </li>
                                    <li>
                                        <a class="brmfields" href="<?php echo $this->webroot; ?>BrmFields"><?php echo __('職務'); ?></a>
                                    </li>
                                    <li>
                                        <a class="brmpositions" href="<?php echo $this->webroot; ?>BrmPositions/index/positionmp"><?php echo __('役職'); ?></a>
                                    </li>
                                    <li>
                                        <a class="brmlogistics" href="<?= $this->webroot; ?>BrmLogistics/index/trading"><?php echo __('取引'); ?></a>
                                    </li>
                                </ul>
                            </li>
                        </ul> -->
                        <!-- Fixed Assets -->
                        <!-- <ul class="nav navbar-nav menu_active">
                            <li class="dropdown">
                                <a class="dropdown-toggle menureportlist pactive" data-toggle="dropdown" href="#"><?php echo __("固定資産"); ?>
                                    <span><i class="fa-solid fa-caret-down"></i></span></span>
                                </a>
                                <ul class="dropdown-menu" id="menufive">
                                    <li>
                                    <a class="assetevents" href="<?= $this->webroot; ?>AssetEvents"><?php echo __("イベント"); ?></a>
                                    </li>
                                </ul>
                            </li>
                        </ul> -->
                        <!-- right -->
                        <ul class="nav navbar-nav navbar-right">
                            <li>
                                <a href="<?= $this->webroot; ?>Menus" class="backperiod"><i class="fa-regular fa-circle-left"></i>&nbsp;<?php echo __('メインメニュー'); ?></a>
                            </li>
                            <li class="dropdown">
                                <a class="dropdown-toggle user_name" data-toggle="dropdown" href="#">
                                    <i class="fa-regular fa-circle-user"></i>
                                    <?php echo $this->Session->read('LOGIN_USER'); ?>
                                    <i class="fa-solid fa-caret-down"></i>
                                </a>
                                <ul class="dropdown-menu">

                                    <li>
                                        <a href="<?php echo $this->webroot; ?>Logins/logout">
                                            <i class="fa-solid fa-arrow-right-from-bracket"></i>&nbsp;
                                            <?php echo __('ログアウト'); ?></a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                        <!--end right -->
                    </div>

                </div>
            </div>
        </nav>
    </div>

    <!-- Content -->
    <div class="container-fluid">

        <?php
        echo $this->Flash->render();
        echo $this->fetch('content');
        ?>

    </div>

    <!-- Footer start -->
    <footer class="panel-footer text-center footer">
        <p>2019 All Rights Reserved &copy; by <a href="http://brycenmyanmar.com.mm" target="_blank" class="txt-link">Brycen Myanmar Co.,Ltd.</a></p>
    </footer>
    <!-- Footer end -->

</body>