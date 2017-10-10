<?php
$head = array('bodyclass' => 'package-manager primary',
              'title' => html_escape(__('Browse packages')),
              'content_class' => 'horizontal-nav');
echo head($head);
echo flash();
?>
<?php if (!has_loop_records('package_manager_package')): ?>
	<h2><?php echo __('You have no packages.'); ?></h2>
	<?php if($nb_items_in_cart>0):?>
		<p><a href="<?php echo html_escape(url('package-manager/index/add')); ?>"><?php echo __('Add your first package.'); ?></a></p>
	<?php else: ?>
		<p><?php echo __('You must add item(s) in your cart before creating a package');?></p>
		<p><?php echo link_to("items", "browse", __("Select items"));?></p>
	<?php endif; ?>
<?php else: ?>
	<p><a class="add-package button small green" href="<?php echo html_escape(url('package-manager/index/add')); ?>"><?php echo __('Add a Package'); ?></a>
	<?php if($nb_items_in_cart > 0) echo ' <em>('.__(plural("you have one item in your cart", "you have %d items in your cart", $nb_items_in_cart),$nb_items_in_cart).')</em>';?></p>
	<?php echo $this->partial('index/browse-list.php', array('packageManager' => $package_manager_packages)); ?>
	<div class="pagination"><?php echo $paginationLinks = pagination_links(); ?></div>
<?php endif; ?>
<?php echo foot(); ?>
