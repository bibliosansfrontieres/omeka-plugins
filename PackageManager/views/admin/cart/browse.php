<?php
$head = array('bodyclass' => 'package-manager primary',
              'title' => html_escape(__('Your cart')),
              'content_class' => 'horizontal-nav');
echo head($head);
echo flash();
?>
<section class="seven columns alpha">
	<div class="panel">
		<?php if(empty($cart)):?>
			<h2><?php echo __('Your cart is empty'); ?></h2>
			<p><?php echo link_to("items", "browse", __("Select items"));?></p>
		<?php else:?>
			<h2><?php echo __('Currently in cart'); ?></h2>
			<?php 
				foreach ($cart as $id) :
				if (!($item = get_record_by_id('item', $id))) continue;
			?>
			<div class="recent-row">
				<p class="recent">
				<?php echo metadata($item, array('Dublin Core', 'Title'));?>
				</p>
				<p class="dash-edit">
				<?php echo "<a href='".url("package-manager/cart/delete/id/".$id)."'>".__("remove")."</a>";?>
				</p>
			</div>
			<?php 
				release_object($item);
				endforeach;
			?>
		<?php endif;?>
	</div>
</section>
<section class="three columns omega">
	<div id="save" class="panel">
		<a href="<?php echo url("package-manager/index/add");?>" class="big green button"><?php echo __('Create Package from basket'); ?></a>
		<a href="<?php echo url("package-manager/cart/clear");?>" class="big blue button"><?php echo __('Clear Basket'); ?></a>
	</div>
</section>

<?php echo foot(); ?>