<div class="container-fluid">
	<div class="row">
		<div class="col-sm-12" >
			<div class="login-wrapper">
				<div class="cus-login-box">
					<div class="cus-login-header">
						<!-- remove logo png by Nu Nu Lwin (21/Jan/2020)-->
						<h3><?php echo $subject; ?></h3>
					</div>
					<div class="cus-body">
						<p><?php echo __($title); ?></p>
						<p> <?php echo __($body); ?></p>
						<?php if ( (!empty($link)) || ($link != "") ) : ?>
							<p><a href="<?php echo $link;?>" class=''><?php echo __('ページに行く'); ?></a></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>