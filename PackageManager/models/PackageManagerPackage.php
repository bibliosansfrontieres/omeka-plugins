<?php
/**
 * Package Manager
 *
 * @copyright Copyright 2017 id[+] Technology
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Package Manager package record class.
 */

class PackageManagerPackage extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface
{

    public $name;
    public $ideascube_name;
    public $description;
    public $global_objective;
    public $audience;
    public $language;
    public $language_level;
    public $target_specificity;
    public $education_level;
    public $other_objectives;
    public $slug;
    public $modified;
    public $last_exportable_modification;
    public $added;
    public $owner_id;
	
    protected $_related = array(
        'Contents' => 'getContents',
		'Relations' => 'getRelations',
		'RelationsDropdown' => 'getRelationsDropdown',
    );

    protected function _initializeMixins()
    {
        $this->_mixins[] = new Mixin_Timestamp($this, 'added', 'modified');
		$this->_mixins[] = new Mixin_Owner($this, 'owner_id');
		$this->_mixins[] = new Mixin_Search($this);
    }


    public function getContents()
    {
        return $this->getTable('PackageManagerPackagesContents')->findByPackage($this->id);
    }

    public function getRelations()
    {
        return $this->getTable('PackageManagerPackagesRelations')->findByPackage($this->id);
    }

    protected function getRelationsDropdown()
    {
		$result = array();
        $params = array(
			'type'=>get_option('package_manager_item_type_relation'),
            'sort_field' => 'added',
            'sort_dir' => 'd',
		);
        $items = $this->getTable('Item')->findBy($params);
		if($items && !empty($items)){
			foreach($items as $item){
				$result[$item->id] = metadata($item, array('Dublin Core', 'Title'));
			}
		}
		return $result;
    }	
    /**
     * Returns the number of items in the package.
     */
    public function getNumberOfItems()
    {
		return $this->getTable('PackageManagerPackagesContents')->countPackageContent($this->id);
    }
	
    /**
     * Returns the package's creator user infos.
     */
    public function getCreatedByUser()
    {
        return $this->getTable('User')->find($this->owner_id);
    }
	
    /**
     * Validate the form data.
     */
    protected function _validate()
    {        
        if (empty($this->name)) {
            $this->addError('name', __('The package must be given a name.'));
        }
        if (empty($this->ideascube_name)) {
            $this->addError('name', __('The package must be given a name for IdeasCube.'));
        }
		if (empty($this->description)) {
            $this->addError('name', __('The package must be given a description.'));
        }        
		if (empty($this->language)) {
            $this->addError('name', __('The package must be given a language.'));
        }

        if (100 < strlen($this->name)) {
            $this->addError('name', __('The name for your package must be 100 characters or less.'));
        }
        if (100 < strlen($this->ideascube_name)) {
            $this->addError('name', __('The IdeasCube\'s name for your package must be 100 characters or less.'));
        }

        if (!$this->fieldIsUnique('name')) {
            $this->addError('name', __('The name is already in use by another package. Please choose another.'));
        }
    }
    
    /**
     * Prepare special variables before saving the form.
     */
    protected function beforeSave($args)
    {
        $this->name = trim($this->name);
        $this->ideascube_name = trim($this->ideascube_name);
        $this->description = trim($this->description);
        // Generate slug from package name.
        $this->slug = $this->_generateSlug($this->name.'-'.$this->language);

        // Check whether last_exportable_modification should be updated
        $storedObject = get_record_by_id('PackageManagerPackage', $this->id);
        if( $this->_isExportableEdit($storedObject, $this->_postData) ){
            $this->last_exportable_modification = date('Y-m-d H:i:s');
        }
        // $this->modified_by_user_id = current_user()->id;
    }

    function _isExportableEdit($storedObject, $formObject) {
        $contents = array_map(function($o) {return $o->item_id;}, $storedObject->Contents);
        return ( ($storedObject['name'] != $formObject['name'])
            || ($storedObject['ideascube_name'] != $formObject['ideascube_name'])
            || ($storedObject['description'] != $formObject['description'])
            || ! package_manager_array_same_content($contents, $formObject['items'])
        );
    }

    /**
     * Delete records that are associated with this Package.
     */
    protected function afterDelete()
    {
		$package_contents = $this->getTable('PackageManagerPackagesContents')
			->findByPackage($this->id);
		foreach ($package_contents as $content) {
			$content->delete();
		}
		$package_relations = $this->getTable('PackageManagerPackagesRelations')
			->findByPackage($this->id);
		foreach ($package_relations as $relation) {
			$relation->delete();
		}		
	}

	/**
     * Save Content records that are associated with this Package.
     */
    protected function afterSave($args)
    {
		// add/remove content.
		$contents = array_map(function($o) {
								return $o->item_id;
							}, $this->Contents);
		$contentsToSave = array_diff($args['post']['items'], $contents);
		$contentsToRemove = array_diff($contents,$args['post']['items']);
		foreach ($contentsToSave as $item_id) {
			$assoc = new PackageManagerPackagesContents;
			$assoc->package_id = $this->id;
			$assoc->item_id = $item_id;
			$assoc->save();
		}
		foreach ($contentsToRemove as $item_id) {
			$assoc = $this->getTable('PackageManagerPackagesContents')
			->findByAssoc($item_id, $this->id);
			$assoc->delete();			
		}
		if(get_option('package_manager_item_type_relation')>0){
			$relations_post = (isset($args['post']['relations'])) ? $args['post']['relations'] : array();
			$relations = array_map(function($o) {
					return $o->item_id;
				}, $this->Relations);
			$relationsToSave = array_diff($relations_post, $relations);
			$relationsToRemove = array_diff($relations, $relations_post);
			
			foreach ($relationsToSave as $item_id) {
				$assoc = new PackageManagerPackagesRelations;
				$assoc->package_id = $this->id;
				$assoc->item_id = $item_id;
				$assoc->save();
			}
			foreach ($relationsToRemove as $item_id) {
				$assoc = $this->getTable('PackageManagerPackagesRelations')
				->findByAssoc($item_id, $this->id);
				$assoc->delete();			
			}	
		}
        $this->setSearchTextTitle( $this->name );
        $this->addSearchText($this->name);
        $this->addSearchText($this->ideascube_name);
        $this->addSearchText($this->description);
        $this->addSearchText($this->global_objective);
        $this->addSearchText($this->audience);
        $this->addSearchText($this->language);
        $this->addSearchText($this->language_level);
        $this->addSearchText($this->target_specificity);
        $this->addSearchText($this->education_level);
        $this->addSearchText($this->other_objectives);
		
		return;
    } 
	
    /**
     * Generate a slug given a seed string.
     * 
     * @param string
     * @return string
     */
    private function _generateSlug($seed)
    {
		return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($seed, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }
	
    public function getRecordUrl($action='edit')
    {
        return array('module' => 'package-manager', 'controller' => 'index', 
                     'action' => $action, 'id' => $this->id);
    }
	
    public function getProperty($property)
    {
        switch($property) {
            case 'nb_items':
                return $this->getNumberOfItems();
			case 'created_username':
                return $this->getCreatedByUser()->username;
            default:
                return parent::getProperty($property);
        }
    }

    public function getResourceId()
    {
		return 'PackageManager_Index';
    }	
}
