<div class="rtaxFees view">
<h2><?php echo __('Rtax Fee'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($rtaxFee['RtaxFee']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Target Year'); ?></dt>
		<dd>
			<?php echo h($rtaxFee['RtaxFee']['target_year']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Rate'); ?></dt>
		<dd>
			<?php echo h($rtaxFee['RtaxFee']['rate']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Flag'); ?></dt>
		<dd>
			<?php echo h($rtaxFee['RtaxFee']['flag']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Rtax Fee'), array('action' => 'edit', $rtaxFee['RtaxFee']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Rtax Fee'), array('action' => 'delete', $rtaxFee['RtaxFee']['id']), array('confirm' => __('Are you sure you want to delete # %s?', $rtaxFee['RtaxFee']['id']))); ?> </li>
		<li><?php echo $this->Html->link(__('List Rtax Fees'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Rtax Fee'), array('action' => 'add')); ?> </li>
	</ul>
</div>
