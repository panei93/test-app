<div class="users view">
<h2><?php echo __('User'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($user['User']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Login Code'); ?></dt>
		<dd>
			<?php echo h($user['User']['login_code']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User Name'); ?></dt>
		<dd>
			<?php echo h($user['User']['user_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Password'); ?></dt>
		<dd>
			<?php echo h($user['User']['password']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($user['User']['email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Azure Object'); ?></dt>
		<dd>
			<?php echo h($user['User']['azure_object']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Role'); ?></dt>
		<dd>
			<?php echo $this->Html->link($user['Role']['id'], array('controller' => 'roles', 'action' => 'view', $user['Role']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Layer Code'); ?></dt>
		<dd>
			<?php echo h($user['User']['layer_code']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Flag'); ?></dt>
		<dd>
			<?php echo h($user['User']['flag']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created By'); ?></dt>
		<dd>
			<?php echo h($user['User']['created_by']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated By'); ?></dt>
		<dd>
			<?php echo h($user['User']['updated_by']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created Date'); ?></dt>
		<dd>
			<?php echo h($user['User']['created_date']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated Date'); ?></dt>
		<dd>
			<?php echo h($user['User']['updated_date']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit User'), array('action' => 'edit', $user['User']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete User'), array('action' => 'delete', $user['User']['id']), array('confirm' => __('Are you sure you want to delete # %s?', $user['User']['id']))); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Roles'), array('controller' => 'roles', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Role'), array('controller' => 'roles', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Labor Cost Details'), array('controller' => 'labor_cost_details', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Labor Cost Detail'), array('controller' => 'labor_cost_details', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Labor Costs'), array('controller' => 'labor_costs', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Labor Cost'), array('controller' => 'labor_costs', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Labor Cost Details'); ?></h3>
	<?php if (!empty($user['LaborCostDetail'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Target Year'); ?></th>
		<th><?php echo __('User Id'); ?></th>
		<th><?php echo __('Position Id'); ?></th>
		<th><?php echo __('Layer Id'); ?></th>
		<th><?php echo __('Business Type'); ?></th>
		<th><?php echo __('Person Count'); ?></th>
		<th><?php echo __('Person Total'); ?></th>
		<th><?php echo __('Comment'); ?></th>
		<th><?php echo __('Flag'); ?></th>
		<th><?php echo __('Created By'); ?></th>
		<th><?php echo __('Updated By'); ?></th>
		<th><?php echo __('Created Date'); ?></th>
		<th><?php echo __('Updated Date'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($user['LaborCostDetail'] as $laborCostDetail): ?>
		<tr>
			<td><?php echo $laborCostDetail['id']; ?></td>
			<td><?php echo $laborCostDetail['target_year']; ?></td>
			<td><?php echo $laborCostDetail['user_id']; ?></td>
			<td><?php echo $laborCostDetail['position_id']; ?></td>
			<td><?php echo $laborCostDetail['layer_code']; ?></td>
			<td><?php echo $laborCostDetail['business_type']; ?></td>
			<td><?php echo $laborCostDetail['person_count']; ?></td>
			<td><?php echo $laborCostDetail['person_total']; ?></td>
			<td><?php echo $laborCostDetail['comment']; ?></td>
			<td><?php echo $laborCostDetail['flag']; ?></td>
			<td><?php echo $laborCostDetail['created_by']; ?></td>
			<td><?php echo $laborCostDetail['updated_by']; ?></td>
			<td><?php echo $laborCostDetail['created_date']; ?></td>
			<td><?php echo $laborCostDetail['updated_date']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'labor_cost_details', 'action' => 'view', $laborCostDetail['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'labor_cost_details', 'action' => 'edit', $laborCostDetail['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'labor_cost_details', 'action' => 'delete', $laborCostDetail['id']), array('confirm' => __('Are you sure you want to delete # %s?', $laborCostDetail['id']))); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Labor Cost Detail'), array('controller' => 'labor_cost_details', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
<div class="related">
	<h3><?php echo __('Related Labor Costs'); ?></h3>
	<?php if (!empty($user['LaborCost'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Target Year'); ?></th>
		<th><?php echo __('Position Id'); ?></th>
		<th><?php echo __('User Id'); ?></th>
		<th><?php echo __('Layer Id'); ?></th>
		<th><?php echo __('Person Count'); ?></th>
		<th><?php echo __('B Person Count'); ?></th>
		<th><?php echo __('Common Expense'); ?></th>
		<th><?php echo __('B Person Total'); ?></th>
		<th><?php echo __('Labor Unit'); ?></th>
		<th><?php echo __('Corpo Unit'); ?></th>
		<th><?php echo __('Yearly Labor Cost'); ?></th>
		<th><?php echo __('Unit Labor Cost'); ?></th>
		<th><?php echo __('Adjust Labor Cost'); ?></th>
		<th><?php echo __('Yearly Corpo Cost'); ?></th>
		<th><?php echo __('Unit Corpo Cost'); ?></th>
		<th><?php echo __('Adjust Corpo Cost'); ?></th>
		<th><?php echo __('Flag'); ?></th>
		<th><?php echo __('Created By'); ?></th>
		<th><?php echo __('Updated By'); ?></th>
		<th><?php echo __('Created Date'); ?></th>
		<th><?php echo __('Updated Date'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($user['LaborCost'] as $laborCost): ?>
		<tr>
			<td><?php echo $laborCost['id']; ?></td>
			<td><?php echo $laborCost['target_year']; ?></td>
			<td><?php echo $laborCost['position_id']; ?></td>
			<td><?php echo $laborCost['user_id']; ?></td>
			<td><?php echo $laborCost['layer_code']; ?></td>
			<td><?php echo $laborCost['person_count']; ?></td>
			<td><?php echo $laborCost['b_person_count']; ?></td>
			<td><?php echo $laborCost['common_expense']; ?></td>
			<td><?php echo $laborCost['b_person_total']; ?></td>
			<td><?php echo $laborCost['labor_unit']; ?></td>
			<td><?php echo $laborCost['corpo_unit']; ?></td>
			<td><?php echo $laborCost['yearly_labor_cost']; ?></td>
			<td><?php echo $laborCost['unit_labor_cost']; ?></td>
			<td><?php echo $laborCost['adjust_labor_cost']; ?></td>
			<td><?php echo $laborCost['yearly_corpo_cost']; ?></td>
			<td><?php echo $laborCost['unit_corpo_cost']; ?></td>
			<td><?php echo $laborCost['adjust_corpo_cost']; ?></td>
			<td><?php echo $laborCost['flag']; ?></td>
			<td><?php echo $laborCost['created_by']; ?></td>
			<td><?php echo $laborCost['updated_by']; ?></td>
			<td><?php echo $laborCost['created_date']; ?></td>
			<td><?php echo $laborCost['updated_date']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'labor_costs', 'action' => 'view', $laborCost['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'labor_costs', 'action' => 'edit', $laborCost['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'labor_costs', 'action' => 'delete', $laborCost['id']), array('confirm' => __('Are you sure you want to delete # %s?', $laborCost['id']))); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Labor Cost'), array('controller' => 'labor_costs', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
