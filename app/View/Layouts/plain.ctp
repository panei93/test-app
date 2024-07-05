<?php

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

$siteDescription = __d('cake_dev', 'FINANCIIO');
$copyRight = __d('cake_dev', '2022 All Rights Reserved © by Brycen Myanmar Co.,Ltd.');
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=EDGE,IE=11" />
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/Sumisho.svg">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>
		<?php echo $siteDescription; ?>
	</title>
	<script>
		let langConfig = "<?php echo $this->Session->read('Config.language'); ?>";
	</script>

	<?php
	// echo $this->Html->meta('icon');

	//Load css files
	echo $this->Html->css('cake.generic');
	echo $this->Html->css('bootstrap.min');
	echo $this->Html->css('datepicker.min.css');
    echo $this->Html->css('bootstrap-datetimepicker.min.css');
	echo $this->Html->css('bootstrap-grid.min');
	echo $this->Html->css('bootstrap-reboot.min');
    echo $this->Html->css('bootstrap-datetimepicker.min.css');
	echo $this->Html->css('style.css');
	echo $this->Html->css('all.min.css');
    echo $this->Html->css('fontawesome.min.css');


	//Load js files
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
    echo $this->Html->script('commonMessage.js');
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
</head>

<body>
	<div class="col-sm-12 head-bar">
		<ul class="language_nav">
			<li>
				<img src="<?php echo $this->webroot; ?>img/sumisho_logo.svg" alt="SUMISHO LOGO" class="sumisho-logo">
			</li>
			<nav class="lang-nav-bar"> 
				<li>
				<?php $show_flag = ($this->Session->check('SHOW_FLAG') && strpos($_SERVER['REQUEST_URI'], 'ssoLogin') !== false)?
                        $this->Session->read('SHOW_FLAG') : 'true'; if($show_flag != 'false'):?>
					<a href="javascript:changeLanguage('eng')" class="lang-menu lang-eng">
						<img src="<?php echo $this->webroot; ?>img/english.png" height="20px" width="20px" alt="ENG">
						&nbsp;English</a>
				<?php endif; ?>
				</li>
				<li>
				<?php $show_flag = ($this->Session->check('SHOW_FLAG') &&  strpos($_SERVER['REQUEST_URI'], 'ssoLogin') !== false)?
                        $this->Session->read('SHOW_FLAG') : 'true'; if($show_flag != 'false'):?>
					<a href="javascript:changeLanguage('jpn')" class="lang-menu lang-jpn">
						<img src="<?php echo $this->webroot; ?>img/japan.png" height="20px" width="20px" alt="JPN" class="img-flag-jpn">
						&nbsp;Japanese</a>
				<?php endif; ?>
				</li>
			</nav>
		</ul>
	</div>

	<div id="container" class="plain-content">
		<div id="content">

			<?php echo $this->Flash->render(); ?>

			<?php echo $this->fetch('content'); ?>
		</div>
	</div>
	<!-- <?php echo $this->element('sql_dump'); ?> -->
</body>

</html>