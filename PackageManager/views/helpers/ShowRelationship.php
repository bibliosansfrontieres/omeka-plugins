<?php
class PackageManager_View_Helper_ShowRelationship extends Zend_View_Helper_Abstract
{
	public function showRelationship($item = false, $view = false, $context = false)
	{
		if($context!="relation") $context = "content";
		$db = get_db();
		$packages = ($context == 'relation') ? $db->getTable('PackageManagerPackage')->findByItemRelation($item->id) : $db->getTable('PackageManagerPackage')->findByItemContent($item->id);
		return $view->partial('relationship.php', compact('packages','context'));
	}
}