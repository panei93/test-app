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

$siteDescription = __d('cake_dev', 'FINANCIIO');
$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=EDGE,IE=11"/>
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/Sumisho.svg">
	<link href="<?php echo $this->webroot; ?>css/fontawesome.min.css" rel="stylesheet">
    <link href="<?php echo $this->webroot; ?>css/all.min.css" rel="stylesheet">

	<title>
		<?php echo $siteDescription; ?>
	</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php
		// echo $this->Html->meta('icon');

		echo $this->Html->css('fontawesome-all.min');
		echo $this->Html->css('bootstrap.css');
		echo $this->Html->css('datepicker.min.css');
		echo $this->Html->css('jquery-confirm');
		echo $this->Html->css('bootstrap-datetimepicker.min.css');
		echo $this->Html->css('jquery.floatingscroll');
		echo $this->Html->css('jquery-ui.css');
		echo $this->Html->css('style.css');
		echo $this->Html->css('all.min.css');
		echo $this->Html->css('fontawesome.min.css');

		echo $this->Html->script('jquery-2.1.4.min.js');
		echo $this->Html->script('jquery-ui.min.js');
		echo $this->Html->script('jquery-confirm');
		echo $this->Html->script('bootstrap.min.js');	
		echo $this->Html->script('moment.min.js');
		echo $this->Html->script('bootstrap-datepicker.min.js');
		echo $this->Html->script('bootstrap-datetimepicker.js');
		echo $this->Html->script('script.js');
		echo $this->Html->script('custom.js');
		echo $this->Html->script('sprintf');
		echo $this->Html->script('commonMessage');
		echo $this->Html->script('jquery.floatThead');
		echo $this->Html->script('jquery.floatingscroll.min');
		echo $this->Html->script('all.min.js');
		echo $this->Html->script('fontawesome.min.js');
		
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
		
		if($this->Session->read('Config.language') == 'eng') {			
			echo $this->Html->script('commonMessage');
		}
		else {
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

		ul#menuone{
			font-family: 'Aileron-Light';
			font-size: 14px;
			color: #000000 !important;
		}

		.drop_show_active{
			 background-color: #569B45;
		}

	</style>
	<script>
	let langConfig = "<?php echo $this->Session->read('Config.language'); ?>";
	$(document).ready(function() {
	    $("#myNavbar [href]").each(function() {

	        if (this.href == window.location.href) {

	        	var str = window.location.href;
	        	if(str.indexOf('ProgressReport') != -1 || str.indexOf('SummaryReport') != -1 || str.indexOf('SummaryDetail') != -1 ){
					$(".menuitemlist").addClass('drop_show_active');
				}

			    $(this).addClass("drop_show_active");           
	        }
	    });

	});

	</script>
</head>
<?php 
	$user_level = $this->Session->read('ADMIN_LEVEL_ID');
?>
<body>
	<div id="header">			
		<nav class="navbar navbar-inverse">
			<div class="wrap group">
				<div class="container-fluid">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
					        <span class="icon-bar"></span>
					        <span class="icon-bar"></span>
					        <span class="icon-bar"></span>                        
				      	</button>
				      	<span class="lblSumisho">
				      		<!-- <?php echo __("Sumisho");?> -->
				      		<img src="<?=$this->webroot;?>img/sumisho_logo.svg" height=20 width=120>      		
				      	</span>
  						
					</div>
					<div class="collapse navbar-collapse" id="myNavbar">
    					<ul class="nav navbar-nav menu_active">
				        	<li>
					    		<a href="<?=$this->webroot;?>Menus"><i class="fa-regular fa-circle-left"></i>&nbsp;<?php echo __('メインメニュー');?></a>
      						</li>
    					</ul>
    					<!-- right -->
    					<ul class="nav navbar-nav navbar-right">
    						<li class="dropdown">
						    	<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									<i class="fa-regular fa-circle-user"></i>
						    		<?php echo $this->Session->read('LOGIN_USER');?>		
						    		<span class="caret"></span>
						    	</a>
						    	<ul class="dropdown-menu">
				                	
					        		<li>
					        			<a href="<?php echo $this->webroot; ?>Logins/logout">
					        			<i class="fa-solid fa-arrow-right-from-bracket"></i>&nbsp; <?php echo __('ログアウト');?></a>
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
</html>
