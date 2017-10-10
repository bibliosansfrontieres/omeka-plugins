<?php
class PackageManager_View_Helper_AddToCart extends Zend_View_Helper_Abstract
{
	public function addToCart($item = false, $url_only = false)
	{
		$link = $this->_retrieveUrl($item);
		return ($url_only) ? $link : "<a href='".$link."' class='pm_add_to_cart'>".__("Add to cart")."</a>";
	}

	protected function _retrieveUrl($item)
	{
		$suffix = ($item instanceof Item) ? "/id/".$item->id : "";
		return url("package-manager/cart/add".$suffix, null, array(), false);
	}
}