<table class="full">
    <thead>
        <tr>
            <?php echo browse_sort_links(array(
                __('Name') => 'name',
                __('Audience') => 'audience',
                __('Language') => 'language',
                __('Last Modified') => 'modified',
				__('Total Number of Items') => null),
				array('link_tag' => 'th scope="col"', 'list_tag' => ''));
            ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach (loop('package_manager_packages') as $pkg): ?>
        <tr>
            <td>
                <span class="title">
                    <a href="<?php echo html_escape(record_url('package_manager_package', 'show')); ?>">
                        <?php echo metadata('package_manager_package', 'name'); ?>
                    </a>
                </span>
                <ul class="action-links group">
                    <li><a class="edit" href="<?php echo html_escape(record_url('package_manager_package', 'edit')); ?>">
                        <?php echo __('Edit'); ?>
                    </a></li>
                    <li><a class="delete-confirm" href="<?php echo html_escape(record_url('package_manager_package', 'delete-confirm')); ?>">
                        <?php echo __('Delete'); ?>
                    </a></li>
                </ul>
            </td>
            <td><?php echo metadata('package_manager_package', 'audience');?></td>
            <td><?php echo metadata('package_manager_package', 'language');?></td>
            <td><?php echo __('by <strong>%1$s</strong> on %2$s',
                metadata('package_manager_package', 'created_username'),
                html_escape(format_date(metadata('package_manager_package', 'modified'), Zend_Date::DATETIME_SHORT))); ?>
            </td>
			<td>
				<a href="<?php echo html_escape(record_url('package_manager_package', 'show')); ?>">
					<?php echo $pkg->getNumberOfItems();?>
				</a>			
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
