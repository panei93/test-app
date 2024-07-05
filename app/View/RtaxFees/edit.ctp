<div class="rtaxFees form">
<?php echo $this->Form->create('RtaxFee'); ?>
	<fieldset>
		<legend><?php echo __('Edit Rtax Fee'); ?></legend>
	<?php
		echo $this->Form->input('id');
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
		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('RtaxFee.id')), array('confirm' => __('Are you sure you want to delete # %s?', $this->Form->value('RtaxFee.id')))); ?></li>
		<li><?php echo $this->Html->link(__('List Rtax Fees'), array('action' => 'index')); ?></li>
	</ul>
</div>
