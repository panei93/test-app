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

$siteDescription = __d('cake_dev', 'SUMISHO');
$copyRight = __d('cake_dev', '2022 All Rights Reserved © by Brycen Myanmar Co.,Ltd.');
?>
<!DOCTYPE html>
<html>

<head>
	<?php echo $this->Html->charset();
	?>

	<title>
		<?php echo $siteDescription ?>:
		<?php echo $this->fetch('title'); ?>
	</title>

	<?php
	// echo $this->Html->meta('icon');

	//Load css files
	echo $this->Html->css('cake.generic');
	echo $this->Html->css('bootstrap.min');
	echo $this->Html->css('bootstrap-grid.min');
	echo $this->Html->css('bootstrap-reboot.min');
	echo $this->Html->css('style.css');

	//Load js files
	echo $this->Html->script('jquery-3.6.0.min');
	echo $this->Html->script('bootstrap.bundle.min');
	echo $this->Html->script('bootstrap.min');

	echo $this->fetch('meta');
	echo $this->fetch('css');
	echo $this->fetch('script');
	?>
</head>

<body>
	<div id="container" class="default-content">
		<div id="header">
			<nav class="navbar navbar-dark navbar-expand-lg">
				<a class="navbar-brand" href="#"><?php echo ($siteDescription) ?></a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>

				<div class="collapse navbar-collapse" id="">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item <?php echo (!empty($this->params['controller']) && ($this->params['controller'] == 'home')) ? 'active' : 'inactive' ?>">
							<a class="nav-link" href="home">Home <span class="sr-only">(current)</span></a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#">Link</a>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								Dropdown
							</a>
							<div class="dropdown-menu" aria-labelledby="navbarDropdown">
								<a class="dropdown-item" href="#">Action</a>
								<a class="dropdown-item" href="#">Another action</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="#">Something else here</a>
							</div>
						</li>
						<li class="nav-item">
							<a class="nav-link disabled" href="#">Disabled</a>
						</li>
					</ul>
					<form class="form-inline my-2 my-lg-0">
						<input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
						<button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
					</form>
				</div>
			</nav>
		</div>
		<div id="content">

			<?php echo $this->Flash->render(); ?>

			<?php echo $this->fetch('content'); ?>
		</div>
		<div id="footer">
			<p>
				<?php echo $copyRight; ?>
			</p>
		</div>
	</div>
	<!-- <?php echo $this->element('sql_dump'); ?> -->
</body>

</html>