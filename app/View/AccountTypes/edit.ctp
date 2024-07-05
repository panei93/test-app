<div class="accountTypes form">
<?php echo $this->Form->create('AccountType'); ?>
	<fieldset>
		<legend><?php echo __('Edit Account Type'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('type_name');
		echo $this->Form->input('display_flag');
		echo $this->Form->input('display_order');
		echo $this->Form->input('flag');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('AccountType.id')), array('confirm' => __('Are you sure you want to delete # %s?', $this->Form->value('AccountType.id')))); ?></li>
		<li><?php echo $this->Html->link(__('List Account Types'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Accounts'), array('controller' => 'accounts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Account'), array('controller' => 'accounts', 'action' => 'add')); ?> </li>
	</ul>
</div>
