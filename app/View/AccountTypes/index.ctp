<div class="accountTypes index">
	<h2><?php echo __('Account Types'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('type_name'); ?></th>
			<th><?php echo $this->Paginator->sort('display_flag'); ?></th>
			<th><?php echo $this->Paginator->sort('display_order'); ?></th>
			<th><?php echo $this->Paginator->sort('flag'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($accountTypes as $accountType): ?>
	<tr>
		<td><?php echo h($accountType['AccountType']['id']); ?>&nbsp;</td>
		<td><?php echo h($accountType['AccountType']['type_name']); ?>&nbsp;</td>
		<td><?php echo h($accountType['AccountType']['display_flag']); ?>&nbsp;</td>
		<td><?php echo h($accountType['AccountType']['display_order']); ?>&nbsp;</td>
		<td><?php echo h($accountType['AccountType']['flag']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $accountType['AccountType']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $accountType['AccountType']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $accountType['AccountType']['id']), array('confirm' => __('Are you sure you want to delete # %s?', $accountType['AccountType']['id']))); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
		'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Account Type'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Accounts'), array('controller' => 'accounts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Account'), array('controller' => 'accounts', 'action' => 'add')); ?> </li>
	</ul>
</div>
