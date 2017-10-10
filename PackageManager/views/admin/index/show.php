<?php
$title = strip_formatting(metadata('package_manager_package', 'name'));
$head = array('bodyclass' => 'package-manager primary',
              'title' => $title,
              'content_class' => 'horizontal-nav');
echo head($head);
echo flash();
?>

<section class="seven columns alpha">
    <?php echo flash(); ?>
	<small class='right'><?php echo __('Last update');?> : <?php echo metadata('package_manager_package', 'modified');?></small>
	<h2><?php echo __('Description');?></h2>
	<p><?php echo metadata('package_manager_package', 'description');?><p>

	<div class="details" style="margin:15px 0 15px 0">
		<p><strong><?php echo __('Goal');?></strong> : <?php echo metadata('package_manager_package', 'global_objective');?></p>
		<p><strong><?php echo __('Audience');?></strong> : <?php echo metadata('package_manager_package', 'audience');?></p>
		<p><strong><?php echo __('Language');?></strong> : <?php echo metadata('package_manager_package', 'language');?></p>
		<p><strong><?php echo __('Language level');?></strong> : <?php echo metadata('package_manager_package', 'language_level');?></p>
		<p><strong><?php echo __('Specificity');?></strong> : <?php echo metadata('package_manager_package', 'target_specificity');?></p>
		<p><strong><?php echo __('Education level');?></strong> : <?php echo metadata('package_manager_package', 'education_level');?></p>
		<p><strong><?php echo __('Other objectives');?></strong> : <?php echo metadata('package_manager_package', 'other_objectives');?></p>
	<?php if(($pm_relation = get_option('package_manager_item_type_relation')) && $pm_relation > 0):
		$associations = array();
		foreach($relations as $relation){
			$item = get_record_by_id('item', $relation->item_id);	
			if(!$item || empty($item) || $item->item_type_id != $pm_relation) continue;
			$associations[] = link_to_item(metadata($item, array('Dublin Core', 'Title')), array(), 'show', $item)." ";
			release_object($item);
		}
	?>
		<br/>
		<p><strong><?php echo __('Related');?></strong> :</p>
		<?php echo implode(" / ", $associations);?>
	<?php endif;?>
	</div>
	<div class="panel">
		<h2><?php echo __('Content');?> (<?php echo metadata('package_manager_package', 'nb_items');?>)</h2>
		<?php 
		$total_items=0;
		foreach($contents as $item_id):
			$item = get_record_by_id('item', $item_id);	
			if(!$item || empty($item)) continue;
			$total_items++;		
		?>
		<div class="recent-row">
			<p>
			<?php echo link_to_item(metadata($item, array('Dublin Core', 'Title')), array(), 'show', $item);?>
			</p>
			<ul>
				<li><strong>Summary</strong> : <?php echo metadata($item, array('Dublin Core', 'Description'));?></li>
				<li><strong>Lang</strong>    : <?php echo metadata($item, array('Dublin Core', 'Language'));?></li>
				<li><strong>Credits</strong> : <?php echo metadata($item, array('Dublin Core', 'Creator'));?></li>
				<li><strong>Tags</strong>    : <?php echo tag_string($item, null, ', ');?></li>
				<li><strong>Path</strong>    : <?php echo (element_exists('Item Type Metadata', 'Path')) ? metadata($item, array('Item Type Metadata', 'Path')) : "";?></li>
				<li><strong>Preview</strong>    : <?php $preview = $item->Files; echo ($preview && element_exists('Item Type Metadata', 'Path')) ? metadata($item, array('Item Type Metadata', 'Path')).".png" : "";?></li>
			</ul>
			<?php fire_plugin_hook('admin_package_show_each', array('item' => $item, 'view' => $this));?>
			<?php release_object($item);?>
		</div>
		<?php endforeach;?>
		<?php if($total_items > 0):?>
		<p class="center"><a href="<?php echo $this->url("package-manager/cart/push/id/". metadata('package_manager_package', 'id'));?>"><?php echo __('Push content to Cart');?></a></p>
		<?php else:?>
		<p><?php echo __('No content');?></p>
		<?php endif;?>
	</div>
	<?php fire_plugin_hook('admin_package_show', array('package'=>$package_manager_package, 'view' => $this));?>
</section>

<section class="three columns omega">
    
    <div id="edit" class="panel">
        <?php if (is_allowed($package_manager_package, 'edit')): ?>
        <?php echo link_to($package_manager_package, 'edit', __('Edit'), array('class'=>'big green button')); ?>
        <?php endif; ?>
        <?php if (is_allowed($package_manager_package, 'delete')): ?>
        <?php echo link_to($package_manager_package, 'delete-confirm', __('Delete'), array('class' => 'delete-confirm big red button')); ?>
        <?php endif; ?>
    </div>

    <div class="panel">
        <h4><?php echo __('Export Package'); ?></h4>
        <div><?php echo output_format_list(false); ?></div>
    </div>
	
	<?php fire_plugin_hook('admin_package_show_sidebar', array('package'=>$package_manager_package, 'view' => $this));?>
</section>

<?php echo foot();?>
