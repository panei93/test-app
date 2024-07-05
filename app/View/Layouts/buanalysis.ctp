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
    // echo $this->Html->meta('icon');

    echo $this->Html->css('fontawesome-all.min');
    echo $this->Html->css('bootstrap.css');
    echo $this->Html->css('datepicker.min.css');
    echo $this->Html->css('bootstrap-datetimepicker.min.css');
    echo $this->Html->css('bootstrap.min.css');
    echo $this->Html->css('jquery.floatingscroll');
    echo $this->Html->css('jquery-ui.css');
    echo $this->Html->css('style.css');
    echo $this->Html->css('jquery-confirm.css');
    echo $this->Html->css('all.min.css');
    echo $this->Html->css('fontawesome.min.css');

    echo $this->Html->script('moment.min.js');
    echo $this->Html->script('jquery-2.1.4.min.js');
    echo $this->Html->script('jquery-ui.min.js');
    echo $this->Html->script('jquery-confirm');
    echo $this->Html->script('bootstrap.min.js');
    echo $this->Html->script('commonMessage-jp.js');
    echo $this->Html->script('jquery.amsifyselect.js');
    echo $this->Html->script('script.js');
    echo $this->Html->script('bootstrap-datepicker.min.js');
    echo $this->Html->script('bootstrap-datetimepicker.js');
    echo $this->Html->script('jquery-confirm.js');
    echo $this->Html->script('custom.js');
    echo $this->Html->script('sprintf');
    echo $this->Html->script('commonMessage');
    echo $this->Html->script('jquery.floatThead.js');
    echo $this->Html->script('jquery.floatingscroll.min');
    echo $this->Html->script('freeze-table.min');
    echo $this->Html->script('jquery.tablednd.js');
    echo $this->Html->script('all.min.js');
    echo $this->Html->script('fontawesome.min.js');


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
    </style>
    <script>
		let langConfig = "<?php echo $this->Session->read('Config.language'); ?>";
        $(document).ready(function() {
            $("#myNavbar [href]").each(function() {
                var controller_name = "<?php echo $this->params['controller']; ?>";
                var action_name = "<?php echo $this->params['action']; ?>";
                var urlName = "";

                if (controller_name=="BuBudgetProgress" && action_name=="Summary") {
                    urlName = controller_name+action_name;
                } else {
                    urlName = controller_name;
                }
                var str = this.href.indexOf(controller_name);
                if (str != -1) {
                    var getClassName = urlName.toLowerCase();
                    $("." + getClassName).addClass('drop_show_active');
                    $("."+getClassName).parent().parent().siblings('.pactive').addClass('drop_show_active');
                }
                if ($('.nav-bar-collapse .d-flex ul li a').hasClass('drop_show_active')) {
                    $('.more-menu').addClass('drop_show_active');
                }
            });

        });
    </script>
</head>
<?php
    $Common = New CommonController();

    $layer_code = $this->Session->read('SESSION_LAYER_CODE');
    $role_id = $this->Session->read('ADMIN_LEVEL_ID');
    $login_id = $this->Session->read('LOGIN_ID');
    $pagename = $this->request->params['controller'];
    $action = $this->request->params;
    
    $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
    $this->Session->write('PERMISSIONS', $permissions);
    if($pagename == 'BudgetResult') $pagename = 'ForecastForms';
    if($pagename == 'BusinessAnalysis') $pagename = 'BusinessAnalysisSheet';
    // if($pagename == 'BuBudgetProgress') $pagename = 'Progress Management(Budget)';
    if($pagename == 'BuBudgetProgress' && $action == 'Summary') $pagename = 'BuBudgetProgressSummary';
    $menus = $Common->getMenuByRole($role_id, $pagename, $this->layout);
    $this->Session->write('MENULISTS', $menus);
?>

<body>
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
                        <?php if(in_array('BuTerms', $menus)) { ?>
                        <ul class="nav navbar-nav menu_active">
                            <li>
                                <a class="buterms" href="<?= $this->webroot; ?>BuTerms"><?php echo __('期間'); ?></a>
                            </li>
                        </ul>
                        <?php } ?>
                        <!-- User Master: written by HHK -->
                        <?php if(in_array('LaborCosts', $menus)) { ?>
                        <ul class="nav navbar-nav menu_active">
                            <li>
                                <a class="laborcosts" href="<?= $this->webroot; ?>LaborCosts"><?php echo __('予算人員表'); ?></a>
                            </li>
                        </ul>
                        <?php } ?>
                        <?php if(in_array('LaborCostDetails', $menus)) { ?>
                        <!-- Layer type Master: written by HHK -->
                        <ul class="nav navbar-nav menu_active">
                            <li>
                                <a class="laborcostdetails" href="<?= $this->webroot; ?>LaborCostDetails"><?php echo __("ビジネス別人員表"); ?></a>
                            </li>
                        </ul>
                        <?php } ?>
                        <?php if(in_array('ForecastForms', $menus)) { ?>
                        <!-- Layer Master: written by HHK -->
                        <ul class="nav navbar-nav menu_active">
                            <li>
                                <a class="budgetresult" href="<?= $this->webroot; ?>BudgetResult"><?php echo __("ビジネス総合分析表"); ?></a>
                            </li>
                        </ul>
                        <?php } ?>
                        <?php if(in_array('BusinessAnalysisSheet', $menus)) { ?>
                        <ul class="nav navbar-nav menu_active">
                            <li>
                                <a class="businessanalysis" href="<?= $this->webroot; ?>BusinessAnalysis"><?php echo __("集計表"); ?></a>
                            </li>
                        </ul>
                        <?php } ?>
                        <!-- < ?php if(in_array('BuBudgetProgress', $menus)) { ?> -->
                        <!-- <ul class="nav navbar-nav menu_active">
                            <li>
                                <a class="bubudgetprogress" href="<?= $this->webroot; ?>BuBudgetProgress"><?php echo __("進捗管理 "); ?></a>
                            </li>
                        </ul> -->
                        <?php if(in_array('BuBudgetProgress', $menus) || in_array('BuBudgetProgressSummary', $menus)){
                        ?>
                        <ul class="nav navbar-nav menu_active">
                            <li class="dropdown">
                                <a class="dropdown-toggle menureportlist pactive" data-toggle="dropdown" href="#"><?php echo __("進捗"); ?>
                                    <span><i class="fa-solid fa-caret-down"></i></span></span>
                                </a>
                                <ul class="dropdown-menu checklist" id="menuone">
                                <?php if(in_array('BuBudgetProgress', $menus)){
                                ?>
                                    <li>
                                        <a class="bubudgetprogress" href="<?= $this->webroot; ?>BuBudgetProgress"><?php echo __("進捗管理(ビジネス総合分析表)"); ?></a>
                                    </li>
                                <?php } ?>
                                <?php if(in_array('BuBudgetProgressSummary', $menus)){
                                ?>
                                    <li>
                                        <a class="bubudgetprogresssummary" href="<?= $this->webroot; ?>BuBudgetProgress/Summary"><?php echo __("進捗管理(集計表)"); ?></a>
                                    </li>
                                <?php } ?>
                                </ul>
                            </li>
                        </ul>
                        <?php } ?>
                        <!-- < ?php } ?> -->
                        <!-- Account Master: written by HHK -->
                        <!-- <ul class="nav navbar-nav menu_active">
                            <li>
                                <a class="businessanalysissheet" href="<?= $this->webroot; ?>BusinessAnalysisSheet"><?php echo __('集計表'); ?></a>
                            </li>
                        </ul> -->

                        <!-- right -->
                        <ul class="nav navbar-nav navbar-right">
                            <li>
                                <a href="<?= $this->webroot; ?>Menus" class="backperiod"><i class="fa-regular fa-circle-left"></i>&nbsp;<?php echo __('メインメニュー'); ?></a>
                            </li>
                            <li>
      							<a class="backperiod buselections" href="<?=$this->webroot;?>BUSelections"><i class="fa-regular fa-circle-left"></i>&nbsp;<?php echo __('期間選択');?></a>
      						</li>

                            <li class="dropdown">
                                <a class="dropdown-toggle user_name" data-toggle="dropdown" href="#">
                                <i class="fa-regular fa-circle-user"></i>
                                    <?php echo $this->Session->read('LOGIN_USER'); ?>
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">

                                    <li>
                                        <a href="<?php echo $this->webroot; ?>Logins/logout">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i>&nbsp; <?php echo __('ログアウト'); ?></a>
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