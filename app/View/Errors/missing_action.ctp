<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=EDGE"/>
	<title>FINANCIIO</title>
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/Sumisho.svg">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php
		echo $this->Html->css('style');
	?>
	<style>
		body {
			background-image: url('<?php echo $this->webroot; ?>img/Background.svg');
			background-position: center;
			background-size: cover;

		}
		.card {
			position: fixed;
			top: 15%;
			left: 28%;
			text-align: center;
			width: 40%;
			padding: 50px 0px;
		}
		#text-panel {
			position: relative;
			z-index: 40;
		}
		#text-title {
			text-align: center;
			font-size: 120px;
			font-weight: bolder;
			color: #e01e37;
			margin-bottom: 2rem;
		}
		.text-not-found {
			margin-top: -20px;
			font-size: 20px;
			margin-bottom: 1rem;
		}
		.obj-center {
			text-align: center;
		}
        a.btn-home {
        	margin-top: 20px;
        	display: inline-block;
        	text-decoration: none;
        	width: 200px;
        	height: 50px;
        	line-height: 50px;
        	border-radius: 30px;
        	border:2px solid #f09282;
        	background-color: transparent;
        	text-decoration: none;
        	color: #f09282;
        }
        a.btn-home:hover {
        	background-color: #f09282;
        	color: #fff;
        	transition: 0.5s;
        }
		.triangle-big-1 {
			position: fixed;
			top: 10%;
			left: 10%;
			width: 0;
			height: 0;
			border-left: 50px solid transparent;
			border-right: 50px solid transparent;
			border-bottom: 100px solid #FE0B75;
			transform: rotate(140deg);
		}
		.triangle-small-1 {
			position: fixed;
			top: 10%;
			left: 15%;
         	width: 0;
			height: 0;
			border-left: 20px solid transparent;
			border-right: 20px solid transparent;
			border-bottom: 50px solid #00D1C6;
			transform: rotate(140deg);
		}
		.triangle-big-2 {
			position: fixed;
			top: 40%;
			left: 5%;
			width: 0;
			height: 0;
			border-left: 60px solid transparent;
			border-right: 60px solid transparent;
			border-bottom: 120px solid #FFB300;
			transform: rotate(100deg);
		}
		.circle-one {
			position: fixed;
			top: 40%;
			left: 15%;
			width: 30px;
			height: 30px;
			border-radius: 50%;
			background: linear-gradient(to top left, #f09282, #4ee553);
		}
		.triangle-big-3 {
			position: fixed;
			top: 75%;
			left: 33%;
			width: 0;
			height: 0;
			border-left: 60px solid transparent;
			border-right: 60px solid transparent;
			border-bottom: 120px solid #1DE9B6;
			transform: rotate(10deg);
		}
		.triangle-small-10 {
			position: fixed;
			top: 30%;
			left: 76%;
			width: 0;
			height: 0;
			border-left: 30px solid transparent;
			border-right: 30px solid transparent;
			border-bottom: 60px solid #00BCD4;
			transform: rotate(140deg);
		}
		.triangle-big-4 {
			position: fixed;
			top: 65%;
			left: 65%;
			width: 0;
			height: 0;
			border-left: 60px solid transparent;
			border-right: 60px solid transparent;
			border-bottom: 120px solid #00897B;
			transform: rotate(140deg);
		}
		.triangle-small-3 {
			position: fixed;
			top: 88%;
			left: 43%;
			width: 0;
			height: 0;
			border-left: 15px solid transparent;
			border-right: 15px solid transparent;
			border-bottom: 30px solid #607D8B;
			transform: rotate(320deg);
		}
		.triangle-small-4 {
			position: fixed;
			top: 65%;
			left: 70%;
			width: 0;
			height: 0;
			border-left: 30px solid transparent;
			border-right: 30px solid transparent;
			border-bottom: 60px solid #d50000;
			transform: rotate(310deg);
		}
		.triangle-big-5 {
			position: fixed;
			top: 75%;
			left: 85%;
			width: 0;
			height: 0;
			border-left: 60px solid transparent;
			border-right: 60px solid transparent;
			border-bottom: 120px solid #651FFF;
			transform: rotate(170deg);
		}
		.triangle-small-5 {
			position: fixed;
			top: 77%;
			left: 93%;
			width: 0;
			height: 0;
			border-left: 15px solid transparent;
			border-right: 15px solid transparent;
			border-bottom: 30px solid #4FC3F7;
			transform: rotate(260deg);
		}
		.triangle-big-6 {
			position: fixed;
			top: 5%;
			left: 80%;
			width: 0;
			height: 0;
			border-left: 60px solid transparent;
			border-right: 60px solid transparent;
			border-bottom: 120px solid #455A64;
			transform: rotate(280deg);
		}
		.triangle-small-6 {
			position: fixed;
			top: 6%;
			left: 80%;
			width: 0;
			height: 0;
			border-left: 20px solid transparent;
			border-right: 20px solid transparent;
			border-bottom: 40px solid #C6FF00;
			transform: rotate(180deg);
		}
		.triangle-small-7 {
			position: fixed;
			top: 13%;
			left: 43%;
			width: 0;
			height: 0;
			border-left: 20px solid transparent;
			border-right: 20px solid transparent;
			border-bottom: 60px solid #FF9800;
			transform: rotate(120deg);
		}
		.triangle-big-7 {
			position: fixed;
			top: 9%;
			left: 45%;
			width: 0;
			height: 0;
			border-left: 60px solid transparent;
			border-right: 60px solid transparent;
			border-bottom: 120px solid #FFD600;
			transform: rotate(150deg);
		}
		.triangle-small-8 {
			position: fixed;
			top: 30%;
			left: 25%;
			width: 0;
			height: 0;
			border-left: 30px solid transparent;
			border-right: 30px solid transparent;
			border-bottom: 60px solid #283593;
			transform: rotate(100deg);
		}
		.triangle-big-8 {
			position: fixed;
			top: 68%;
			left: 12%;
			width: 0;
			height: 0;
			border-left: 60px solid transparent;
			border-right: 60px solid transparent;
			border-bottom: 120px solid #651FFF;
			transform: rotate(170deg);
		}
		.triangle-small-9 {
			position: fixed;
			top: 80%;
			left: 10%;
			width: 0;
			height: 0;
			border-left: 15px solid transparent;
			border-right: 15px solid transparent;
			border-bottom: 30px solid #d50000;
			transform: rotate(0deg);
		}
	</style>
</head>
<body>
	<div class="card">
		<div id="text-panel">
			<div id="text-title">Oops!</div>
			<div class="obj-center text-not-found">The page you're looking for is not found!</div>
			<div class="obj-center">
				<a class="btn-home" href="javascript:history.go(-1)">Go Back!</a>
			</div>
		</div>
		<!-- <div class="triangle-big-1"></div>
		<div class="triangle-small-1"></div>
		<div class="triangle-big-2"></div>
		<div class="circle-one"></div>
		<div class="triangle-big-3"></div>
		<div class="triangle-small-10"></div>
		<div class="triangle-big-4"></div>
		<div class="triangle-small-3"></div>
		<div class="triangle-small-4"></div>
		<div class="triangle-big-5"></div>
		<div class="triangle-small-5"></div>
		<div class="triangle-big-6"></div>
		<div class="triangle-small-6"></div>
		<div class="triangle-small-7"></div>
		<div class="triangle-big-7"></div>
		<div class="triangle-small-8"></div>
		<div class="triangle-big-8"></div>
		<div class="triangle-small-9"></div> -->
	</div>
</body>
</html>
