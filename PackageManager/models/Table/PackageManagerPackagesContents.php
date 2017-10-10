<?php
/**
 * Package Manager
 *
 * @copyright Copyright 2017 id[+] Technology
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Package Manager packages contents table class.
 */
class Table_PackageManagerPackagesContents extends Omeka_Db_Table
{
    public function findByPackage($packageId)
    {
        $select = $this->getSelect()->where('package_manager_packages_contents.package_id = ?', (int) $packageId);
        return $this->fetchObjects($select);
    }
    public function countPackageContent($packageId)
    {
		return count($this->findByPackage($packageId));
	}
    public function findByAssoc($item_id, $packageId)
    {
        $select = $this->getSelect()
			->where('package_manager_packages_contents.package_id = ?', (int) $packageId)
			->where('package_manager_packages_contents.item_id = ?', (int) $item_id);
        return $this->fetchObject($select);
    }	
    public function findByItem($item_id)
    {
        $select = $this->getSelect()
			->where('package_manager_packages_contents.item_id = ?', (int) $item_id);
        return $this->fetchObjects($select);
    }	
}