<div class="accountTypes view">
<h2><?php echo __('Account Type'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($accountType['AccountType']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Type Name'); ?></dt>
		<dd>
			<?php echo h($accountType['AccountType']['type_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Display Flag'); ?></dt>
		<dd>
			<?php echo h($accountType['AccountType']['display_flag']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Display Order'); ?></dt>
		<dd>
			<?php echo h($accountType['AccountType']['display_order']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Flag'); ?></dt>
		<dd>
			<?php echo h($accountType['AccountType']['flag']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Account Type'), array('action' => 'edit', $accountType['AccountType']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Account Type'), array('action' => 'delete', $accountType['AccountType']['id']), array('confirm' => __('Are you sure you want to delete # %s?', $accountType['AccountType']['id']))); ?> </li>
		<li><?php echo $this->Html->link(__('List Account Types'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Account Type'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Accounts'), array('controller' => 'accounts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Account'), array('controller' => 'accounts', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Accounts'); ?></h3>
	<?php if (!empty($accountType['Account'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Account Type Id'); ?></th>
		<th><?php echo __('Account Code'); ?></th>
		<th><?php echo __('Account Name'); ?></th>
		<th><?php echo __('Account Type'); ?></th>
		<th><?php echo __('Postfix'); ?></th>
		<th><?php echo __('Operator'); ?></th>
		<th><?php echo __('Base Param'); ?></th>
		<th><?php echo __('Memo'); ?></th>
		<th><?php echo __('Flag'); ?></th>
		<th><?php echo __('Created By'); ?></th>
		<th><?php echo __('Updated By'); ?></th>
		<th><?php echo __('Created Date'); ?></th>
		<th><?php echo __('Updated Date'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($accountType['Account'] as $account): ?>
		<tr>
			<td><?php echo $account['id']; ?></td>
			<td><?php echo $account['account_type_id']; ?></td>
			<td><?php echo $account['account_code']; ?></td>
			<td><?php echo $account['account_name']; ?></td>
			<td><?php echo $account['account_type']; ?></td>
			<td><?php echo $account['postfix']; ?></td>
			<td><?php echo $account['operator']; ?></td>
			<td><?php echo $account['base_param']; ?></td>
			<td><?php echo $account['memo']; ?></td>
			<td><?php echo $account['flag']; ?></td>
			<td><?php echo $account['created_by']; ?></td>
			<td><?php echo $account['updated_by']; ?></td>
			<td><?php echo $account['created_date']; ?></td>
			<td><?php echo $account['updated_date']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'accounts', 'action' => 'view', $account['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'accounts', 'action' => 'edit', $account['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'accounts', 'action' => 'delete', $account['id']), array('confirm' => __('Are you sure you want to delete # %s?', $account['id']))); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Account'), array('controller' => 'accounts', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
