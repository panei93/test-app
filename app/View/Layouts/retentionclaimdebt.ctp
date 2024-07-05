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
                var str = this.href.indexOf(controller_name);
                if (str !== -1) {
                    var getClassName = controller_name.toLowerCase();
                    $("." + getClassName).addClass('drop_show_active');

                    if($("." + getClassName).parent().parent().get( 0 ).id == 'menuone'){
                        $('#menuone').prev().addClass('drop_show_active');
                    }
                }

            });
            // console.log($(".navbar-right .backperiod"));
            // $(".navbar-right .backperiod").click(function() {
            //     $(".backperiod").addClass('drop_show_active');
            //         });
            // if(this.className.indexOf('backperiod') != -1){console.log(this.className)
            //         $(this).addClass('drop_show_active');
            //     }


        });
    </script>
</head>
<?php
$menus = $this->Session->read('MENULISTS');
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
    					<ul class="nav navbar-nav menu_active">
    						<?php if(in_array('SapImports', $menus)) { ?>
                                <li>
                                    <a href="<?php echo $this->webroot; ?>SapImports/index" class="menuitem sapimports"><?php echo __("SAPデータインポート");?></a>
                                </li>
                            <?php } ?>
							<?php if(in_array('SapAccountPreviews', $menus)) { ?>
                                <li>
                                    <a href="<?php echo $this->webroot; ?>SapAccountPreviews/index" class="menuitem sapaccountpreviews"><?php echo __("経理プレビュー");?></a>
                                </li>
                            <?php } ?>
                            <?php if(in_array('SapAddComments', $menus)) { ?>
                                <li>
                                    <a class="menuitem sapaddcomments" href="<?php echo $this->webroot; ?>SapAddComments/index"><?php echo __("コメント追加");?></a>
                                </li>
                            <?php } ?>
                            <?php if(in_array('SapAccountReviews', $menus)) { ?>
                                <li>
                                    <a class="menuitem sapaccountreviews" href="<?php echo $this->webroot; ?>SapAccountReviews/index"><?php echo __("経理レビュー");?></a>
                                </li>
                            <?php } ?>
							<?php if(in_array('SapSummaryReports', $menus) || in_array('SapSummaryDetails', $menus) || in_array('SapProgressReports', $menus)) { ?>
                                <li class="dropdown">
                                    <a class="dropdown-toggle menuitemlist" data-toggle="dropdown" href="#"><?php echo __("レポート");?>
                                        <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu" id="menuone">
                                        <?php if(in_array('SapSummaryReports', $menus)) { ?>
                                            <li>
                                                <a class="dropdown_menu sapsummaryreports" href="<?php echo $this->webroot; ?>SapSummaryReports/index"><?php echo __("速報版");?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if(in_array('SapSummaryDetails', $menus)) { ?>
                                            <li>
                                                <a class="dropdown_menu sapsummarydetails" href="<?php echo $this->webroot; ?>SapSummaryDetails/index"><?php echo __("長期滞留債権報告書");?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if(in_array('SapProgressReports', $menus)) { ?>
                                            <li>
                                                <a class="dropdown_menu sapprogressreports" href="<?php echo $this->webroot; ?>SapProgressReports/index"><?php echo __("進捗");?></a>
                                            </li>
                                        <?php } ?>                                 
                                    </ul>
                                </li>
                            <?php } ?>
    					</ul>
    					<!-- right -->
    					<ul class="nav navbar-nav navbar-right">
				        	<li>
					    		<a href="<?=$this->webroot;?>Menus"><i class="fa-regular fa-circle-left"></i>&nbsp;<?php echo __('メインメニュー');?></a>
      						</li>
      						<li>
					    		<a class="backperiod sapselections" href="<?=$this->webroot;?>SapSelections"><i class="fa-regular fa-circle-left"></i>&nbsp;<?php echo __('期間選択');?></a>
      						</li>
    						<li class="dropdown">
						    	<a class="dropdown-toggle user_name" data-toggle="dropdown" href="#">
                                    <i class="fa-regular fa-circle-user"></i>
						    		<?php echo $this->Session->read('LOGIN_USER');?>		
						    		<span class="caret"></span>
						    	</a>
						    	<ul class="dropdown-menu">
				                	
					        		<li>
					        			<a href="<?php echo $this->webroot; ?>Logins/logout">
					        			<i class="fa-solid fa-arrow-right-from-bracket"></i>&nbsp;<?php echo __('ログアウト');?></a>
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
