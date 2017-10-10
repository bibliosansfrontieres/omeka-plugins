<?php
/**
 * Package Manager
 *
 * @copyright Copyright 2017 id[+] Technology
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Package Manager packages relations record class.
 */

class PackageManagerPackagesRelations extends Omeka_Record_AbstractRecord
{
    public $package_id;
    public $item_id;
}