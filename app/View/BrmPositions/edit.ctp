<div class="positionMps form">
<?php echo $this->Form->create('PositionMp'); ?>
	<fieldset>
		<legend><?php echo __('Edit Position Mp'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('field_id');
		echo $this->Form->input('position_name_jp');
		echo $this->Form->input('position_name_en');
		echo $this->Form->input('unit_salary');
		echo $this->Form->input('flag');
		echo $this->Form->input('created_id');
		echo $this->Form->input('updated_id');
		echo $this->Form->input('created_date');
		echo $this->Form->input('updated_date');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('PositionMp.id')), array('confirm' => __('Are you sure you want to delete # %s?', $this->Form->value('PositionMp.id')))); ?></li>
		<li><?php echo $this->Html->link(__('List Position Mps'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Field Models'), array('controller' => 'field_models', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Field Model'), array('controller' => 'field_models', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Manpower Plans'), array('controller' => 'manpower_plans', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Manpower Plan'), array('controller' => 'manpower_plans', 'action' => 'add')); ?> </li>
	</ul>
</div>
