<div class="rtaxFees form">
<?php echo $this->Form->create('RtaxFee'); ?>
	<fieldset>
		<legend><?php echo __('Add Rtax Fee'); ?></legend>
	<?php
		echo $this->Form->input('target_year');
		echo $this->Form->input('rate');
		echo $this->Form->input('flag');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Rtax Fees'), array('action' => 'index')); ?></li>
	</ul>
</div>
