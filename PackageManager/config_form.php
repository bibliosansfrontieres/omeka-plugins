<div class="field">
    <div id="package_manager_export_inline_label" class="two columns alpha">
        <label for="package_manager_export_inline"><?php echo __('Export packages inline ?'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('If checked, package export will be output inline.'); ?></p>
        <?php echo get_view()->formCheckbox('package_manager_export_inline', true, 
        array('checked'=>(boolean)get_option('package_manager_export_inline'))); ?>
    </div>
</div>
<div class="field">
    <div id="package_manager_show_item_relationship_label" class="two columns alpha">
        <label for="package_manager_show_item_relationship"><?php echo __('Display relationship ?'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('If checked, package relationship will be displayed on item page.'); ?></p>
        <?php echo get_view()->formCheckbox('package_manager_show_item_relationship', true, 
        array('checked'=>(boolean)get_option('package_manager_show_item_relationship'))); ?>
    </div>
</div>
<div class="field">
    <div id="package_manager_keep_empty_package_label" class="two columns alpha">
        <label for="package_manager_keep_empty_package"><?php echo __('Keep Empty Packages ?'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('If checked, empty packages (after items deletion) will be keeped.'); ?></p>
        <?php echo get_view()->formCheckbox('package_manager_keep_empty_package', true, 
        array('checked'=>(boolean)get_option('package_manager_keep_empty_package'))); ?>
    </div>
</div>
<div class="field">
    <div id="package_manager_enable_simple_vocab_filter_label" class="two columns alpha">
        <label for="package_manager_enable_simple_vocab_filter"><?php echo __('Enable Simple Vocab filter ?'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('If checked, use Simple Vocab filter on matching package metadata.'); ?></p>
        <?php echo get_view()->formCheckbox('package_manager_enable_simple_vocab_filter', true, 
        array('checked'=>(boolean)get_option('package_manager_enable_simple_vocab_filter'))); ?>
    </div>
</div>

<div class="field">
    <div id="package_manager_item_type_relation_label" class="two columns alpha">
        <label for="package_manager_item_type_relation"><?php echo __('Item Type Association ?'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('If you choose an item type here, packages could be associated with one or more items of this type. Be careful, those items couldn\'t be added to cart anymore.'); ?></p>
		<?php echo get_view()->formSelect('package_manager_item_type_relation',
			(int)get_option('package_manager_item_type_relation'),
			array('id' => 'item-type-id'),
			get_table_options('ItemType'));?>
    </div>
</div>

<?php if(isset($dropdown) && is_array($dropdown) && !empty($dropdown)):?>
 <div class="field">
   <div class="inputs seven columns alpha">
		<p><label for="package_manager_export_fields"><?php echo __('Select exported fields'); ?> : </label></p>
		<br>
		<div id="package_manager_export_fields">
		<?php if(isset($export_fields) && is_array($export_fields) && !empty($export_fields)):?>
			<?php foreach($export_fields as $x=>$field):?>
				<p><a href="#" class="remove-element"><?php echo __('remove');?></a>
				<?php echo get_view()->formSelect('package_manager_export_fields[]', $field, array('class' => 'element-id', 'multiple'=>false), $dropdown) ?></p>
			<?php endforeach;?>
		<?php else:?>
				<p><a href="#" class="remove-element"><?php echo __('remove');?></a>
				<?php echo get_view()->formSelect('package_manager_export_fields[]', null, array('class' => 'element-id', 'multiple'=>false), $dropdown) ?></p>
		<?php endif;?>
		</div>
		<br>
		<a href="#" id="more-element" class="blue button"><?php echo __('add field');?></a>
	</div>
</div>

<script type="text/template" id="element-tpl">
<p><a href="#" class="remove-element"><?php echo __('remove');?></a>
<?php echo get_view()->formSelect('package_manager_export_fields[]', null, array('class' => 'element-id', 'multiple'=>false), $dropdown) ?></p>
</script>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($) {
	$('#package_manager_export_fields').on('click','a.remove-element', function(e){
		e.preventDefault();
		$(this).parent().remove();
		return false;
	});
	$('a#more-element').on('click', function(e){
		e.preventDefault();
		$($('#element-tpl').html()).appendTo('#package_manager_export_fields');
		return false;
	})
});
//]]>
</script>
<?php endif;?>