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
	echo $this->Html->css('style.css');
	echo $this->Html->css('jquery-confirm');
	echo $this->Html->css('bootstrap-datetimepicker.min.css');
	echo $this->Html->css('jquery-ui.css');
	echo $this->Html->css('amsify.select.css');
	echo $this->Html->css('jquery.floatingscroll');
	echo $this->Html->css('all.min.css');
    echo $this->Html->css('fontawesome.min.css');
    echo $this->Html->css('select2.min.css');

	echo $this->Html->script('jquery-2.1.4.min.js');
	echo $this->Html->script('jquery-ui.min.js');
	echo $this->Html->script('jquery-confirm');
	echo $this->Html->script('bootstrap.min.js');
	echo $this->Html->script('moment.min.js');
	echo $this->Html->script('bootstrap-datepicker.min.js');
	echo $this->Html->script('bootstrap-datetimepicker.js');
	echo $this->Html->script('jquery.amsifyselect.js');
	echo $this->Html->script('script.js');
	echo $this->Html->script('custom.js');
	echo $this->Html->script('sprintf');
	echo $this->Html->script('commonMessage');
	echo $this->Html->script('jquery.floatThead');
	echo $this->Html->script('jquery.floatingscroll.min');
	echo $this->Html->script('freeze-table.min'); /*NuNuLiwn (20200630)*/
	echo $this->Html->script('jquery-calx-1.1.8.min.js'); /*NuNuLiwn (20200717)*/
	echo $this->Html->script('jquery.freezeheader.js'); /*PanEiPhyo (20200923)*/
	echo $this->Html->script('jquery.tablednd.js');
	echo $this->Html->script('all.min.js');
    echo $this->Html->script('fontawesome.min.js');
    echo $this->Html->script('select2.min.js');


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
				var controller_name = "<?php  echo $this->params['controller']; ?>";
				var str = this.href;

				if (str.indexOf(controller_name) != -1) {
					$(this).addClass("drop_show_active");
					$(".menuitemlist").removeClass("drop_show_active");
					$(".menureportlist").removeClass("drop_show_active");
					$(".menusummarylist").removeClass("drop_show_active");
					$(".menuplanlist").removeClass("drop_show_active");
					$(".menuinternalhistorylist").removeClass("drop_show_active");
					$(".user_name").removeClass("drop_show_active");
					// $(".backperiod").removeClass("drop_show_active");

					var str = window.location.href;
					if (str.indexOf('BrmActualResult') != -1) {
						$(".menuitemlist").addClass('drop_show_active');
					} else if (str.indexOf('BrmBudgetProgressReport') != -1 || str.indexOf('BrmMonthlyProgressReport') != -1) {
						$(".menureportlist").addClass('drop_show_active');
					} else if (str.indexOf('BrmPLSummary') != -1 || str.indexOf('BrmSummary') != -1) {

						$(".menusummarylist").addClass('drop_show_active');
					} else if (str.indexOf('BrmTradingPlan') != -1 || str.indexOf('BrmManpowerPlan') != -1 || str.indexOf('ForecastPlan') != -1 || str.indexOf('BudgetPlan') != -1 || str.indexOf('BrmForecastBudgetDifference') != -1) {
						$(".menuplanlist").addClass('drop_show_active');
					} else if (str.indexOf('InternalPaymentHistory') != -1) {
						$(".menuinternalhistorylist ").addClass('drop_show_active');
					}
					if (str.indexOf('BrmPLSummary') == -1) {

						$('.pl').removeClass("drop_show_active");
					}
					if (str.indexOf('BrmForecastBudgetDifference') == -1) {

						$('.remove').removeClass("drop_show_active");
					}
				}
			});

			$("#plan").click(function() {
				var results = window.location.href;
				$("#error").empty();
				$("#planer").empty();
				$(".remove").removeClass("no_data");
				var err_msg = '';
				var validate = true;
				var term_name = localStorage.getItem("PLAN").split(',');

				var disable_trade = localStorage.getItem("DISABLE_TRADE");

				/* added by Hein Htet Ko */
				$.urlParam = function(name) {
					var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
					return results[1] || 0;
				}
				if (results.indexOf('?hq_id') != -1) {
					if ($.urlParam('term_name') != 0) {
						term_name[0] = '1';
						term_name[1] = $.urlParam('term_name');
						//validate = true;
						localStorage.setItem("PLAN", term_name[0] + "," + term_name[1] + ",");
					}
				}

				if (!term_name[0]) {
					err_msg += errMsg(commonMsg.JSE002, ['<?php echo __("期間選択"); ?>']) + "<br/>";
					validate = false;
				}

				if (validate == false) {
					$(".remove").addClass("no_data");
					$("#error").append(err_msg);
				} else {
					var name = term_name[1].split('~');
					var name1 = name[1].split('(');
					localStorage.setItem("BUDGET_START_YEAR", name[0]);
					var from_ba = localStorage.getItem("FROM_BA_YEAR");
					var term_range = range(name[0], name1[0]);
					if (from_ba !== '' && from_ba >= name[0]) {
						//plan form of start year depend on ba_code of from_date
						//var term_range = range(from_ba, name1[0]);

					} else {
						//plan form of start year depend on term
						//var term_range = range(name[0], name1[0]);

					}
					var menu = <?php echo json_encode ($_SESSION['MENULISTS']); ?>;
					
					var year_count = 0;
					var html = '';
					
					$.each(term_range, function(key, value) {
						year_count++;
						var style = '';
						var aStyle = '';
						
						var  tpHref = "<?php echo $this->webroot; ?>BrmTradingPlan/?year="+value;
						var  mpHref = "<?php echo $this->webroot; ?>BrmManpowerPlan/?year="+value;
						if (year_count == 1) var  budgetHref = "<?php echo $this->webroot; ?>BrmBudgetPlan/?year="+value+"&forecast";
						else var  budgetHref = "<?php echo $this->webroot; ?>BrmBudgetPlan/?year="+value+"&budget";
						if(value < from_ba) {
							style = 'background-color: #eee;border-bottom:1px solid lightgray;';
							aStyle = 'cursor: not-allowed;';
							tpHref = '#';
							mpHref = '#';
							budgetHref = '#';
						}
						//if (disable_trade == '') {
							if (menu.indexOf("BrmTradingPlan") > -1) {
								if (results.indexOf("BrmTradingPlan/?year=" + value) > -1) {
									
									/* added dropdown active */
									html += '<li style="'+style+'"><a style="'+aStyle+'" href="'+tpHref+'" value="' + value + '" class="menuitem drop_show_active">' + value + ' <?php echo __(
																																																	"年度取引計画フォーム"
																																																) ?></a></li>';
								} else {
									
									html += '<li style="'+style+'"><a style="'+aStyle+'" href="'+tpHref+'" value="' + value + '" class="menuitem">' + value + ' <?php echo __(
																																													"年度取引計画フォーム"
																																												) ?></a></li>';
								}
							}
					//	}
						if (menu.indexOf("BrmManPowerPlan") > -1) {
							if (results.indexOf("BrmManpowerPlan/?year=" + value) > -1) {
								/* added dropdown active */
								html += '<li style="'+style+'"><a style="'+aStyle+'" href="'+mpHref+'" value="' + value + '" class="menuitem drop_show_active">' + value + ' <?php echo __("年度人員計画フォーム") ?></a></li>';
							} else {

								html += '<li style="'+style+'"><a style="'+aStyle+'" href="'+mpHref+'" value="' + value + '" class="menuitem">' + value + ' <?php echo __("年度人員計画フォーム") ?></a></li>';
							}
						}
						if (menu.indexOf("BrmBudgetPlan") > -1) {
							if (year_count == 1) {
								if (results.indexOf("BrmBudgetPlan/?year=" + value + "&forecast") > -1) {
									/* added dropdown active */
									html += '<li style="'+style+'"><a style="'+aStyle+'" href="'+budgetHref+ '" value="' + value + '" class="menuitem drop_show_active">' + value + ' <?php echo __("年度見込フォーム") ?> </a></li>';
								} else {

									html += '<li style="'+style+'"><a style="'+aStyle+'" href="'+budgetHref+'" value="' + value + '" class="menuitem">' + value + ' <?php echo __("年度見込フォーム") ?> </a></li>';
								}
							} else {
								if (results.indexOf("BrmBudgetPlan/?year=" + value + "&budget") > -1) {
									/* added dropdown active */
									html += '<li style="'+style+'"><a style="'+aStyle+'" href="'+budgetHref+'" value="' + value + '" class="menuitem drop_show_active">' + value + ' <?php echo __("年度予算フォーム") ?> </a></li>';
								} else {
									html += '<li style="'+style+'"><a style="'+aStyle+'" href="'+budgetHref+'" value="' + value + '" class="menuitem">' + value + ' <?php echo __("年度予算フォーム") ?> </a></li>';
								}

							}
						}
					});
					if (results.indexOf("BrmForecastBudgetDifference") > -1) {
						/* added dropdown active */
						html += '<li><a href="<?php echo $this->webroot; ?>BrmForecastBudgetDifference" class="menuitem drop_show_active"><?php echo __("見込対予算増減一覧") ?> </a></li>';
					} else {
						html += '<li><a href="<?php echo $this->webroot; ?>BrmForecastBudgetDifference" class="menuitem"><?php echo __("見込対予算増減一覧") ?> </a></li>';
					}
					$("#planer").html('');
					$("#planer").append(html);
				}

			});

			$("#paymenthistory").click(function() {
				$("#error").empty();
				$("#success").empty();
				$("#paymenthistorylink").empty();
				$(".remove").removeClass("no_data");
				var err_msg = '';
				var validate = true;
				var term_name = localStorage.getItem("PLAN").split(',');

				if (!term_name[0]) {
					err_msg += errMsg(commonMsg.JSE002, ['<?php echo __("期間選択"); ?>']) + "<br/>";
					validate = false;
				}


				if (validate == false) {
					$(".remove").addClass("no_data");
					$("#error").append(err_msg);
				} else {
					var name = term_name[1].split('~');
					var name1 = name[1].split('(');
					var term_range = range(name[0], name1[0]);

					var year_count = 0;
					var html = '';
					var path = window.location.href;
					var url_year = path.split("year=").pop();
					$.each(term_range, function(key, value) {
						year_count++;
						if (url_year == value) {
							html += '<li><a href="<?php echo $this->webroot; ?>InternalPaymentHistory/?year=' + value + '" value="' + value + '" class="menuitem drop_show_active">' + value + ' <?php echo __("受払履歴") ?></a></li>';
						} else {
							html += '<li><a href="<?php echo $this->webroot; ?>InternalPaymentHistory/?year=' + value + '" value="' + value + '" class="menuitem">' + value + ' <?php echo __("受払履歴") ?></a></li>';
						}


					});


					$("#paymenthistorylink").append(html);
				}

			});

			// $(document).on('click', '#settargetyear [href]', function() {
			// 	var target_year =  $(this).attr('value');
			// 	document.cookie = "TARGET_YEAR = "+ target_year +" ";

			// });

			function range(start, end) {
				var array = new Array();
				for (var i = start; i <= end; i++) {
					array.push(i);
				}
				return array;
			}

			$("#delete_setItem").click(function() {
				localStorage.setItem("PLAN", "");
			});

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
				<div class="container-fluid" style="padding: 0px 5px;">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<span class="lblSumisho">
							<img src="<?= $this->webroot; ?>img/sumisho_logo.svg" class="sumisho-logo">
						</span>
					</div>
					<div class="collapse navbar-collapse" id="myNavbar">
						<?php if(in_array('BrmActualResult', $menus) || in_array('Budget', $menus)) { ?>
							<ul class="nav navbar-nav menu_active">
								<li class="dropdown">
									<a class="dropdown-toggle menuitemlist" data-toggle="dropdown" href="#"><?php echo __("インポート"); ?>
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu checklist" id="menuone">
										<?php if(in_array('Budget', $menus)) { ?>
											<li><a href="<?php echo $this->webroot; ?>Budget" ><?php echo __("予算のインポート"); ?></a></li>
										<?php } ?>
										<?php if(in_array('BrmActualResult', $menus)) { ?>
											<li><a href="<?php echo $this->webroot; ?>BrmActualResult"><?php echo __("実績のインポート"); ?></a></li>
										<?php } ?>

									</ul>
								</li>
							</ul>
						<?php } ?>
						<?php if(in_array('BrmTradingPlan', $menus) || in_array('BrmManpowerPlan', $menus) || in_array('BrmBudgetPlan', $menus) || in_array('BrmForecastBudgetDifference', $menus)) { ?>
							<div id="settargetyear">
								<ul class="nav navbar-nav menu_active">
									<li class="dropdown">
										<a class="dropdown-toggle menuplanlist" data-toggle="dropdown" href="#" id="plan"><?php echo __("計画フォーム"); ?>
											<span class="caret"></span>
										</a>
										<ul class="dropdown-menu checklist remove" id="planer">
										</ul>
									</li>
								</ul>
								<!--start internal history -->
								<?php if ($permissions['index']['limit'] >= 0) : ?>
									<?php if (in_array('BrmTradingPlan', $menus) && in_array('InternalPaymentHistory', $menus)) : ?>
									<ul class="nav navbar-nav menu_active">
										<li class="dropdown">
											<a class="dropdown-toggle menuinternalhistorylist" data-toggle="dropdown" href="#" id="paymenthistory"><?php echo __("受払履歴"); ?>
												<span class="caret"></span>
											</a>
											<ul class="dropdown-menu checklist remove" id="paymenthistorylink">
											</ul>
										</li>
									</ul>
									<?php endif ?>
								<?php endif ?>
								<!-- end internal history -->
							</div>
						<?php } ?>
						<?php if(in_array('BrmPLSummary', $menus) || in_array('BrmSummary', $menus)) { ?>
							<ul class="nav navbar-nav menu_active">
								<li class="dropdown">
									<a class="dropdown-toggle menusummarylist" data-toggle="dropdown" href="#"><?php echo __("予算ヒアリング"); ?>
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu checklist" id="menuone">
										<?php if(in_array('BrmPLSummary', $menus)) { ?>
											<li>
												<a href="<?php echo $this->webroot; ?>BrmPLSummary/index" class="menuitem pl"><?php echo __("PLサマリー"); ?></a>
											</li>
										<?php } ?>
										<?php if(in_array('BrmSummary', $menus)) { ?>
											<li>
												<a href="<?php echo $this->webroot; ?>BrmSummary/index" class="menuitem"><?php echo __("総括表"); ?></a>
											</li>
										<?php } ?>
									</ul>
								</li>
							</ul>
						<?php } ?> 
						<?php if(in_array('BrmBudgetResultDifference', $menus)) { ?>
							<ul class="nav navbar-nav menu_active">
								<li><a href="<?php echo $this->webroot; ?>BrmBudgetResultDifference" class="menuitem"><?php echo __("予実比較"); ?></a>
								</li>
							</ul>
						<?php } ?>
						<?php if(in_array('BrmMonthlyReport', $menus)) { ?>
							<ul class="nav navbar-nav menu_active">
								<li><a href="<?php echo $this->webroot; ?>BrmMonthlyReport" class="menuitem"><?php echo __("月次業績報告"); ?></a>
								</li>
							</ul>
						<?php } ?>
						<?php if(in_array('BrmMonthlyProgressReport', $menus) || in_array('BrmBudgetProgressReport', $menus)) { ?>
							<ul class="nav navbar-nav menu_active">
								<li class="dropdown">
									<a class="dropdown-toggle menureportlist" data-toggle="dropdown" href="#"><?php echo __("レポート"); ?>
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu checklist" id="menuone">
										<?php if(in_array('BrmMonthlyProgressReport', $menus)) { ?>
											<li><a href="<?php echo $this->webroot; ?>BrmMonthlyProgressReport" class="menuitem"><?php echo __("月次業績報告進捗"); ?></a></li>
										<?php } ?>
										<?php if(in_array('BrmBudgetProgressReport', $menus)) { ?>
											<li><a href="<?php echo $this->webroot; ?>BrmBudgetProgressReport" class="menuitem"><?php echo __("予算進捗"); ?></a></li>
										<?php } ?>
									</ul>
								</li>
							</ul>
						<?php } ?>
						<?php if(in_array('BrmBackupFile', $menus)) { ?>
							<ul class="nav navbar-nav menu_active">
								<li>
									<a class="brmbackupfile" href="<?= $this->webroot; ?>BrmBackupFile"><?php echo __('バックアップマスター'); ?></a>
								</li>
							</ul>
						<?php } ?>
						<!-- right -->
						<ul class="nav navbar-nav navbar-right">
							<li><a href="<?php echo $this->webroot; ?>Menus" class="menuitem"><i class="fa-regular fa-circle-left"></i>&nbsp;<?php echo __("メインメニュー"); ?></a>
							</li>
							<li><a class="backperiod" href="<?php echo $this->webroot; ?>BrmTermSelection" class="menuitem"><i class="fa-regular fa-circle-left"></i>&nbsp;<?php echo __("期間選択"); ?></a>
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
	<!-- <div class="rmn_time"><span id="ten-countdown"></span></div> -->

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

</html>