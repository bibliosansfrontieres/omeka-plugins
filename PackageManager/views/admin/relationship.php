    <div class="package_relation panel">
        <h4><?php echo __('Packages'); ?></h4>
        <div>
		<?php if($packages):?>
			<?php if($context=="content"):?>
				<p><?php echo __('This item is part of the following packages');?> :</p>
			<?php else:?>
				<p><?php echo __('This item is associated with the following packages');?> :</p>
			<?php endif;?>
			<ul>
			<?php foreach (loop('package_manager_packages', $packages) as $pkg): ?>
				<li><a href="<?php echo html_escape(record_url('package_manager_packages', 'show')); ?>">
				<?php echo metadata('package_manager_packages', 'name'); ?>
				</a></li>
			<?php endforeach;?>
			</ul>
		<?php else:?>
			<p><?php echo __('No package');?></p>
		<?php endif;?>
        </div>
    </div>


