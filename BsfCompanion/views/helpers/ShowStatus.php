<?php
/**
 * BSF Companion Functions
 *
 * @copyright Copyright 2017 id[+] Technology - All Rights Reserved
 */

class BsfCompanion_View_Helper_ShowStatus extends Zend_View_Helper_Abstract
{
	public function showStatus($package = false, $view = false)
	{
		return $view->partial('status.php', compact('package','view'));
	}
}