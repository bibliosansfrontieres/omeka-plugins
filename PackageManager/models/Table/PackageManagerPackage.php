<?php
/**
 * Package Manager
 *
 * @copyright Copyright 2017 id[+] Technology
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Package Manager Package table class.
 */
class Table_PackageManagerPackage extends Omeka_Db_Table
{
    public function applySearchFilters($select, $params)
    {
        $alias = $this->getTableAlias();
        $paramNames = array('id', 
                            'name', 
                            'owner_id');
                            
        foreach($paramNames as $paramName) {
            if (isset($params[$paramName])) {             
                $select->where($alias . '.' . $paramName . ' = ?', array($params[$paramName]));
            }            
        }

        if (isset($params['sort'])) {
            switch($params['sort']) {
                case 'alpha':
                    $select->order("{$alias}.name ASC");
                    $select->order("{$alias}.id ASC");
                    break;
                case 'id':
                    $select->order("{$alias}.id ASC");
                    $select->order("{$alias}.name ASC");
                    break;
            }
        }         
    }
	
    public function findByItemContent($itemId)
    {
        $select = $this->getSelect()
			->joinLeft(array('package_manager_package_contents'=>$this->_db->PackageManagerPackagesContent), 'package_manager_package_contents.package_id = package_manager_packages.id')
			->reset(Zend_Db_Select::COLUMNS)
			->columns(array("package_manager_packages.id", "package_manager_packages.name"))
			->where('package_manager_package_contents.item_id = ?', (int) $itemId);
		return $this->fetchObjects($select);
    }

    public function findByItemRelation($itemId)
    {
        $select = $this->getSelect()
			->joinLeft(array('package_manager_package_relations'=>$this->_db->PackageManagerPackagesRelation), 'package_manager_package_relations.package_id = package_manager_packages.id')
			->reset(Zend_Db_Select::COLUMNS)
			->columns(array("package_manager_packages.id", "package_manager_packages.name"))
			->where('package_manager_package_relations.item_id = ?', (int) $itemId);
		return $this->fetchObjects($select);
    }

    public function findNamesForAll()
    {
        $data = [];
        $records =  $this->findAll();
        foreach($records as $record){
            $data[$record['id']] =$record['name'];
        }
        return $data;
    }

}
