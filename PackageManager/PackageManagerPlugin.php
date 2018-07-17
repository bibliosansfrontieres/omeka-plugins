<?php
/**
 * Package Manager
 *
 * @copyright Copyright 2017 id[+] Technology
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

require_once dirname(__FILE__) . '/helpers/PackageManagerFunctions.php';
 
/**
 * Package Manager plugin.
 */
class PackageManagerPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
		'install', 'uninstall', 'upgrade', 'initialize',
        'define_acl', 'config_form', 'config','after_delete_record',
        'admin_items_browse_simple_each', 'admin_items_browse',
        'admin_items_show','admin_head','admin_items_show_sidebar'
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main','admin_navigation_global',
								'response_contexts','action_contexts',
								'search_record_types', 'api_resources');
    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        'package_manager_export_inline' => '0',
        'package_manager_show_item_relationship' => '1',
        'package_manager_keep_empty_package' => '0',
        'package_manager_enable_simple_vocab_filter' => '0',
        'package_manager_item_type_relation' => '0',
		'package_manager_export_fields' => false,
    );

    public function __construct(){
		$package_manager_export_fields_default = serialize(array(50,41,44,'tags'));
		$this->_options['package_manager_export_fields'] = $package_manager_export_fields_default; 
		parent::__construct();
	}
	
    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Create tables.
        $db = $this->_db;

		$db->query("
		CREATE TABLE IF NOT EXISTS `".$db->PackageManagerPackage."` (
		  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `name` tinytext COLLATE utf8_unicode_ci NOT NULL,
          `slug` tinytext COLLATE utf8_unicode_ci NOT NULL,
          `description` mediumtext COLLATE utf8_unicode_ci,
		  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `added` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00',
		  `owner_id` int(10) UNSIGNED DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `owner_id` (`owner_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;		
		");
		
		$db->query("
		CREATE TABLE IF NOT EXISTS `".$db->PackageManagerPackagesContent."` (
		  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `package_id` int(10) UNSIGNED DEFAULT NULL,
		  `item_id` int(10) UNSIGNED DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `package` (`package_id`,`item_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;		
		");
		
		$this->_installOptions();
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        // Drop tables.
        $db = $this->_db;
        $db->query("DROP TABLE IF EXISTS `".$db->PackageManagerPackage."`");
        $db->query("DROP TABLE IF EXISTS `".$db->PackageManagerPackagesContent."`");
        $db->query("DROP TABLE IF EXISTS `".$db->PackageManagerPackagesRelation."`");

		// Remove options
        $this->_uninstallOptions();
    }

    /**
     * Upgrade the plugin.
     *
     * @param array $args contains: 'old_version' and 'new_version'
     */
    public function hookUpgrade($args)
    {
		$oldVersion = $args['old_version'];
		$newVersion = $args['new_version'];
		$db = $this->_db;

		if (version_compare($oldVersion, '1.0.3', '<')) {
			// delete orphan assoc
			$sql = "DELETE a FROM `".$db->PackageManagerPackagesContent."` a LEFT JOIN `".$db->Item."` b on a.`item_id` = b.`id` WHERE b.`id` IS NULL";
			$db->query($sql); 
			
			// add more package metadata
			$sql = "
			ALTER TABLE `".$db->PackageManagerPackage."` 
			ADD `global_objective` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `description`, 
			ADD `audience` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `global_objective`, 
			ADD `language` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `audience`, 
			ADD `language_level` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `language`, 
			ADD `target_specificity` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `language_level`, 
			ADD `education_level` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `target_specificity`, 
			ADD `other_objectives` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `education_level`;
			";
			$db->query($sql); 
			
			set_option('package_manager_export_fields', $this->_options['package_manager_export_fields']);
		}
		
		if (version_compare($oldVersion, '1.0.4', '<')) {
			// create package relationship with items from a defined type
			$sql = "
			CREATE TABLE IF NOT EXISTS `".$db->PackageManagerPackagesRelation."` (
			  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			  `package_id` int(10) UNSIGNED DEFAULT NULL,
			  `item_id` int(10) UNSIGNED DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `package` (`package_id`,`item_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;		
			";
			$db->query($sql); 
			set_option('package_manager_item_type_relation', $this->_options['package_manager_item_type_relation']);
		}

		if (version_compare($oldVersion, '1.0.7', '<')) {
			// create new field last_exportable_modification
			$sql = "
			ALTER TABLE `".$db->PackageManagerPackage."`
			ADD `last_exportable_modification` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00';
			";
			$db->query($sql);

            // add initial values for field last_exportable_modification
            // (`modified`=`modified` is here to prevent this field's ON UPDATE update)
			$sql = "
			UPDATE `".$db->PackageManagerPackage."`
			SET `last_exportable_modification` = `modified`, `modified` = `modified`;
			";
			$db->query($sql);
		}

		if (version_compare($oldVersion, '1.0.8', '<')) {
			// create new field last_exportable_modification
			$sql = "
			ALTER TABLE `".$db->PackageManagerPackage."`
			ADD `ideascube_name` tinytext COLLATE utf8_unicode_ci  AFTER `name`;
			";
			$db->query($sql);

            // add initial values for field last_exportable_modification
            // (`modified`=`modified` is here to prevent this field's ON UPDATE update)
			$sql = "
			UPDATE `".$db->PackageManagerPackage."`
			SET `ideascube_name` = `name`, `modified` = `modified`;
			";
			$db->query($sql);
		}
    }

    /**
     * Add the translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
    }

    /**
     * Define the ACL.
     * 
     * @param Omeka_Acl
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        
        $indexResource = new Zend_Acl_Resource('PackageManager_Index');
        $packageResource = new Zend_Acl_Resource('PackageManager_Cart');
        $acl->add($indexResource);
		$acl->add($packageResource);
        $acl->allow(array('super', 'admin'), array('PackageManager_Index', 'PackageManager_Cart'));
    }

    /**
     * Display the plugin config form.
     */
    public function hookConfigForm()
    {
		$manage_fields = false; // experimental - disabled for now
		if($manage_fields){
			$db = $this->_db;
			$select = $db->select()
						 ->from(array('element_sets' => $db->ElementSet), 
								array('element_set_name' => 'element_sets.name'))
						 ->join(array('elements' => $db->Element), 
								'element_sets.id = elements.element_set_id', 
								array('element_id' =>'elements.id', 
									  'element_name' => 'elements.name'))
						 ->joinLeft(array('item_types_elements' => $db->ItemTypesElements), 
									'elements.id = item_types_elements.element_id',
									array())
						 ->joinLeft(array('item_types' => $db->ItemType), 
									'item_types_elements.item_type_id = item_types.id', 
									array('item_type_name' => 'item_types.name'))
						 ->where('element_sets.record_type IS NULL OR element_sets.record_type = "Item"')
						 ->order(array('element_sets.name', 'item_types.name', 'elements.name'));
			$elements = $db->fetchAll($select);		
			$dropdown = array('' => __('Select Below'));
			foreach ($elements as $element) {
				if ($element['item_type_name']) {
					$optGroup = __('Item Type') . ': ' . __($element['item_type_name']);
				} else {
					$optGroup = __($element['element_set_name']);
				}
				$value = __($element['element_name']);

				$dropdown[$optGroup][$element['element_id']] = $value;
			}
			$dropdown['Omeka specific'] = array(
				'tags' => __('Tags'),
				'files' => __('Files'),
				'collections' => __('Collections'),
			);

			$export_fields = unserialize(get_option('package_manager_export_fields'));
		}
        require dirname(__FILE__) . '/config_form.php';
    }

    /**
     * Set the options from the config form input.
     */
    public function hookConfig($args)
    {
        set_option('package_manager_export_inline', (int)$args['post']['package_manager_export_inline']);
        set_option('package_manager_show_item_relationship', (int)$args['post']['package_manager_show_item_relationship']);
        set_option('package_manager_keep_empty_package', (int)$args['post']['package_manager_keep_empty_package']);
        set_option('package_manager_enable_simple_vocab_filter', (int)$args['post']['package_manager_enable_simple_vocab_filter']);
        set_option('package_manager_export_fields', serialize(array_filter((array)$args['post']['package_manager_export_fields'])));
        set_option('package_manager_item_type_relation', (int)$args['post']['package_manager_item_type_relation']);
    }
	
    /**
     * Print out the add-cart link on the admin items browse page.
     */
    public function hookAdminItemsBrowseSimpleEach($args)
    {
		$excluded_item_type = get_option('package_manager_item_type_relation');
		if($excluded_item_type == 0 || $args['item']->item_type_id != $excluded_item_type)
			echo get_view()->addToCart($args['item']);
    }

    /**
     * Print out the add-cart link on the admin item show page.
     */
    public function hookAdminItemsShow($args)
    {
		$excluded_item_type = get_option('package_manager_item_type_relation');
		if($excluded_item_type == 0 || $args['item']->item_type_id != $excluded_item_type)
			echo get_view()->addToCart($args['item']);
    }
	
    /**
     * Print out the packages relationship on the admin item show page.
     */
    public function hookAdminItemsShowSidebar($args)
    {
		if(get_option('package_manager_show_item_relationship')){
			$excluded_item_type = get_option('package_manager_item_type_relation');
			if($excluded_item_type == 0 || $args['item']->item_type_id != $excluded_item_type){
					echo get_view()->showRelationship($args['item'], $args['view']);
			}
			else{
					echo get_view()->showRelationship($args['item'], $args['view'], 'relation');
			}
		}
    }

    public function hookAdminItemsBrowse($args){
        $allPackagesNames = get_db()->getTable('PackageManagerPackage')->findNamesForAll();
        echo get_view()->batchAddToPackage($allPackagesNames, $args['view']);
    }

    /**
     * Print out the Package Manager JS file.
     */
    public function hookAdminHead()
    {
        $cart_url =  get_view()->addToCart(false, true);
        $addItems_url =  url("package-manager/index/add-items/id/%d", null, array(), false);;
        queue_css_url('https://cdn.jsdelivr.net/sweetalert2/6.4.2/sweetalert2.min.css');
        queue_css_string("input.pm-cbox{margin-right:10px;} a.pm-cart{min-height:16px;background: transparent url(".img('cart.png').") center left no-repeat;padding-left:20px;}");
        queue_js_url('https://cdn.jsdelivr.net/sweetalert2/6.4.2/sweetalert2.min.js');
        queue_js_string('Omeka.pm_cart_url = "'.$cart_url.'";');
        queue_js_string('Omeka.pm_addItems_url = "'.$addItems_url.'";');
        queue_js_file('pm');
        queue_css_file('PackageManager');
    }

    /**
     * Avoid orphan package items
     */
    public function hookAfterDeleteRecord($args)
    {
        $record = $args['record'];
		if($record->record_type !== 'Item' || !($record instanceof SearchText)) return; // Only primary items are concerned, not ItemElements
		$assoc = get_db()->getTable('PackageManagerPackagesContents')->findByItem($record->record_id);
		if($assoc && !empty($assoc)){
			foreach($assoc as $item_assoc) {
				$nb_items = get_db()->getTable('PackageManagerPackagesContents')->countPackageContent($item_assoc->package_id);
				$item_assoc->delete();
				if($nb_items==1 && !get_option('package_manager_keep_empty_package')){ // delete orphan package
					$pkg = $this->_db->getTable('PackageManagerPackage')->find($item_assoc->package_id);
					if($pkg && $pkg->exists()) $pkg->delete();
				}
			}
		}
		
		$assoc = get_db()->getTable('PackageManagerPackagesRelations')->findByItem($record->record_id);
		if($assoc && !empty($assoc)){
			foreach($assoc as $item_assoc) {
				$item_assoc->delete();
			}
		}
		return;
    }

    /**
     * Add the Package Manager link to the admin main navigation.
     * 
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
		$idx = 3;
		$pkg_link = array(
            'label' => __('Packages'),
            'uri' => url('package-manager'),
            'resource' => 'PackageManager_Index',
            'privilege' => 'browse'
        );
		array_splice( $nav, $idx, 0, "_tmp_" );
		$nav[$idx] = $pkg_link;
        return $nav;
    }
	
	public function filterAdminNavigationGlobal($nav) {
		$cart_link = array(
				'label' => __('Cart'),
				'uri' => url("package-manager/cart"),
				'class'=>'pm-cart'
				);
		array_unshift($nav, $cart_link);
		return $nav;
	}

    public function filterSearchRecordTypes($recordTypes)
    {
        $recordTypes['PackageManagerPackage'] = __('Package');
        return $recordTypes;
    }
	
    public function filterResponseContexts($contexts)
    {
        $contexts['csv'] = array(
			'suffix'  => 'csv',
			'headers' => array(
				'Content-Type' => 'text/plain; charset=utf-8',
				'Content-Disposition' => 'inline; filename="export.csv"'
				)
			); 
		$contexts['yaml'] = array(
			'suffix'  => 'yaml',
			'headers' => array(
				'Content-Type' => 'text/plain; charset=utf-8',
				'Content-Disposition' => 'inline; filename="export.yaml"'
				)
			);
		$contexts['object'] = array(
			'suffix'  => 'object',
			'headers' => array(
				'Content-Type' => 'text/plain; charset=utf-8',
				'Content-Disposition' => 'inline; filename="export.json"'
				)
			);
        return $contexts;
    }
	
    public function filterActionContexts($contexts, $args)
    {
        $controller = $args['controller'];
        if ($controller instanceof PackageManager_IndexController) {
            $contexts['show'] = array('csv','yaml','object');
        }
        return $contexts;
    }

    public function filterApiResources($apiResources)
    {
        $apiResources['packages'] = array(
            'module' => 'Package Manager',
            'record_type' => 'PackageManagerPackage',
            'actions' => array('get', 'index'),
            'index_params' => array('relations')
        );

        $apiResources['package_relations'] = array(
            'module' => 'Package Manager',
            'record_type' => 'PackageManagerPackagesRelations',
            'actions' => array('get', 'index'),
            'index_params' => array('item_id','package_id')
        );

        return $apiResources;
    }
}
