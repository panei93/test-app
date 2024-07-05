<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta charset="UTF-8">
	<?php
		echo $this->Html->css('bootstrap.min');
		echo $this->Html->css('style');
	    echo $this->Html->script('jquery-2.1.4.min');
	    echo $this->Html->script('bootstrap.min');
	    echo $this->fetch('meta');
	    echo $this->fetch('css');
	    echo $this->fetch('script');
	?>
	<style>
		.container-fluid {
			margin-top: 10px;
		}
		.cus-login-wrapper {
			width: 100%;
		}
		.cus-login-box {
			width: 70%;
			margin: 0 auto;
			padding: 30px;
			background-color:#e9f1f1;
			box-shadow: 0px 2px 4px 0px #1c1c1c;
			border-radius: 3px;
		}
		.cus-login-header {
			padding: 8px 0px;
			font-family: 'Anton';
			font-size: 1em;
			color: #347aeb;
			border-bottom-style: solid;
		}
		.cus-body {
			font-size: 20px;
			font-family: archivo;
			margin-top: 15px;
		}
		
	</style>
</head>
<body>
	<?php 
		echo $this->fetch('content'); 
	?>
</body>
</html>