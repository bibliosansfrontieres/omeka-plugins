<?php
class PackageManager_View_Helper_BatchAddToPackage extends Zend_View_Helper_Abstract
{
	public function batchAddToPackage($packagesNames, $view)
	{
	    $NAME_MAX_LENGTH = 50;
        return $view->partial('batch-add-to-package.php', compact('packagesNames','NAME_MAX_LENGTH'));
	}

}