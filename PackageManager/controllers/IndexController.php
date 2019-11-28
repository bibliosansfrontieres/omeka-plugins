<?php
/**
 * Package Manager
 *
 * @copyright Copyright 2017 id[+] Technology
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Package Manager index controller class.
 *
 * @package PackageManager
 */
class PackageManager_IndexController extends Omeka_Controller_AbstractActionController
{
	protected $session;
	protected $_browseRecordsPerPage = self::RECORDS_PER_PAGE_SETTING;

    public function init()
    {
        // Set the model class so this controller can perform some functions, 
        // such as $this->findById()
        $this->_helper->db->setDefaultModelName('PackageManagerPackage');
		$this->session = new Zend_Session_Namespace('pm_cart');
    }

    public function indexAction()
    {
        // Always go to browse.
        $this->_helper->redirector('browse');
        return;
    }

    public function browseAction()
    {
		$this->view->nb_items_in_cart = (!is_array($this->session->cart) || empty($this->session->cart)) ? 0 : count($this->session->cart);
		parent::browseAction();
	}

    public function showAction()
    {
        // Get the requested package.
		$package = $this->_helper->db->findById();
 		$contents = array_map(function($o) {
						return $o->item_id;
					}, $package->Contents);
		$this->view->package_manager_package = $package;
		$this->view->contents = $contents;
		$this->view->export_fields = unserialize(get_option('package_manager_export_fields'));
		$this->view->relations = $package->Relations;
		$output_type = $this->_helper->contextSwitch->getCurrentContext();
		if(in_array($output_type, array('csv','yaml','object'))){
			if(!get_option('package_manager_export_inline')){
				switch($output_type){
					case 'csv':
						$this->getResponse()->setHeader('Content-Type', 'text/csv; charset=utf-8', true);
					break;
					case 'yaml':
						$this->getResponse()->setHeader('Content-Type', 'application/yaml; charset=utf-8', true);
					break;
					case 'object': // Unable to set it as JSON (default Zend output type)
						$this->getResponse()->setHeader('Content-Type', 'application/json; charset=utf-8', true);
						$output_type = 'json';
					break;
				}
				$this->getResponse()->setHeader('Content-disposition', 'attachment; filename="'.metadata('package_manager_package', 'slug').'.'.$output_type.'"', true);
			}
		}
        $requiredItemFileds = array(
            'Dublin Core' => array('Title', 'Description', 'Creator', 'Language'),
            'Item Type Metadata' => array('Path'),
        );
        $results = array();
        $this->view->incompleteItems = array();
        foreach (array_keys($requiredItemFileds) as $scope) {
            foreach ($requiredItemFileds[$scope] as $field) {
                $results[$field] = $this->_findIncompleteItemsInPackage($package, $field, $scope);
                if( !empty($results[$field])){ $this->view->incompleteItems[$field] =$results[$field];}
            }
        }
		// parent::showAction();
	}

    public function addAction()
    {
        if(!is_array($this->session->cart) || empty($this->session->cart)){
			$this->_helper->flashMessenger(__('You must add item(s) in your cart before creating a package'), 'error');
			$this->_helper->redirector('browse');
		}

        // Create a new package.
        $package = new PackageManagerPackage;
        $form = $this->_getForm($package);
        $this->view->form = $form;
        $this->_processPackageForm($package, $form, 'add');
    }

    public function addItemsAction()
    {
        // Get the requested package.
        $package = $this->_helper->db->findById();
        $packageId = $package->id;
        $contentsTable = get_db()->getTable('PackageManagerPackagesContents');

        foreach ($_POST['itemsIds'] as $itemId) {
            if($contentsTable->findByAssoc($itemId, $packageId) == null){
                $assoc = new PackageManagerPackagesContents;
                $assoc->package_id = $packageId;
                $assoc->item_id = $itemId;
                $assoc->save();
            }
        }
        // Updating last_exportable_modification
        // using insert() instead of save() to avoid pre/post callbacks
        $package->last_exportable_modification = date('Y-m-d H:i:s');
        get_db()->insert(get_class($package), $package->toArray());

        // add flash message for UX
        $itemsCount = count($_POST['itemsIds']);
        $this->_helper->flashMessenger($itemsCount.__(" items successfully added to package"), 'success');

        $response = array(
            "status"=>'redirect',
            "result"=>url("package-manager/index/show/id/$packageId")
        );

        echo Zend_Json::encode($response);
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function editAction()
    {
        // Get the requested package.
        $package = $this->_helper->db->findById();
        $form = $this->_getForm($package);
        $this->view->form = $form;
        $this->_processPackageForm($package, $form, 'edit');
    }

    public function deleteAction() {
        if (!$this->getRequest()->isPost()) {
            $this->_forward('method-not-allowed', 'error', 'default');
            return;
        }

        $record = $this->_helper->db->findById();

        $form = $this->_getDeleteForm();
        if ($form->isValid($_POST)) {
            $record->delete();
            $successMessage = __('Package %s deleted.', $record->name);
        } else {
            throw new Omeka_Controller_Exception_404;
        }

        if (isset($successMessage)) {
            $this->_helper->flashMessenger($successMessage, 'success');
        }
        $this->_redirectAfterDelete($record);
    }

    protected function _getForm($package = null)
    {
        $formOptions = array('type' => 'package_manager_package');
        if ($package && $package->exists()) {
            $formOptions['record'] = $package;
			$contents = array_map(function($o) {
						return $o->item_id;
					}, $package->Contents);
			$add_from_cart = array_diff((array) $this->session->cart, $contents);
        }
		else{
			$contents = $this->session->cart;
		}


        $form = new Omeka_Form_Admin($formOptions);
        $form->addElementToEditGroup(
            'text', 'name',
            array(
                'id' => 'package-manager-package-name',
                'value' => $package->name,
                'label' => __('Name'),
                'description' => __('Package name for Omeka (required)'),
                'required' => true
            )
        );
        $form->addElementToEditGroup(
            'text', 'ideascube_name',
            array(
                'id' => 'package-manager-package-ideascube-name',
                'value' => $package->ideascube_name,
                'label' => __('IdeasCube Name'),
                'description' => __('Package name for IdeasCube (required)'),
                'required' => true
            )
        );
        $form->addElementToEditGroup(
            'textarea', 'description',
            array(
				'id' => 'package-manager-package-description',
                'cols'  => 50,
                'rows'  => 10,
                'value' => $package->description,
                'label' => __('Description'),
                'description' => __('Description of the package (required)'),
                'required' => true
            )
        );

        $form->addElementToEditGroup(
            'text', 'global_objective',
            array(
                'id' => 'package-manager-package-global-objective',
                'value' => $package->global_objective,
                'label' => __('Goal'),
            )
        );
        $form->addElementToEditGroup(
            'text', 'audience',
            array(
                'id' => 'package-manager-package-audience',
                'value' => $package->audience,
                'label' => __('Audience'),
            )
        );
        $form->addElementToEditGroup(
            'text', 'language',
            array(
                'id' => 'package-manager-package-language',
                'value' => $package->language,
                'label' => __('Language'),
                'required' => true
            )
        );
        $form->addElementToEditGroup(
            'text', 'language_level',
            array(
                'id' => 'package-manager-package-language-level',
                'value' => $package->language_level,
                'label' => __('Language level'),
            )
        );
        $form->addElementToEditGroup(
            'text', 'subject',
            array(
                'id' => 'package-manager-package-subject',
                'value' => $package->subject,
                'label' => __('Subject'),
            )
        );
        $form->addElementToEditGroup(
            'text', 'education_level',
            array(
                'id' => 'package-manager-package-education-level',
                'value' => $package->education_level,
                'label' => __('Education level'),
            )
        );
        $form->addElementToEditGroup(
            'textarea', 'other_objectives',
            array(
                'id' => 'package-manager-package-other-objectives',
                'value' => $package->other_objectives,
                'label' => __('Other objectives'),
                'cols'  => 50,
                'rows'  => 10,
			)
        );

		if(get_option('package_manager_enable_simple_vocab_filter'))
			$form = $this->_process_simple_vocab_form_filter( $form );

		if(get_option('package_manager_item_type_relation')>0){
			$elements = $form->getValues();
			$order = count($elements);
			$relations_items = $package->RelationsDropdown;
			$current_relations = array_map(function($o) {
						return $o->item_id;
					}, $package->Relations);
			$form->addElementToEditGroup('multiCheckbox', 'relations', array(
				'label'=> __('Associations'),
				'description'=> __('This package can be associated with the following item(s)'),
				'value'=> $current_relations,
				'multiOptions' => $relations_items,
				'class' => "pm-cbox",
				'order' => $order++,
			));
		}

		$itemCheckboxes = array();
		foreach ($contents as $id) {
			if (!($item = get_record_by_id('item', $id))) {
				continue;
			}

			$itemCheckboxes[$id] = metadata($item, array('Dublin Core', 'Title'), array('no_escape' => true));
			release_object($item);
		}
		$selected = $itemCheckboxes;
		if(isset($add_from_cart) && count($add_from_cart)>0){
			foreach ($add_from_cart as $id) {
				if (!($item = get_record_by_id('item', $id))) {
					continue;
				}

				$itemCheckboxes[$id] = metadata($item, array('Dublin Core', 'Title'), array('no_escape' => true));
				release_object($item);
			}
		}
		$form->addElementToEditGroup('multiCheckbox', 'items', array(
			'label'=>__('Content'),
			'description'=>__('Only checked items will be saved as package content.'),
			'value'=> array_keys($selected),
			'multiOptions' => $itemCheckboxes,
			'class' => "pm-cbox",
			'required' => true,
			'order'=>100,
		));

        if (class_exists('Omeka_Form_Element_SessionCsrfToken')) {
            $form->addElement('sessionCsrfToken', 'csrf_token');
        }

        return $form;
    }

    /**
     * Process the package add and edit forms.
     */
    private function _processPackageForm($package, $form, $action)
    {
        // Set the package object to the view.
		$this->view->package = $package;

        if ($this->getRequest()->isPost()) {
            if (!$form->isValid($_POST)) {
                $this->_helper->_flashMessenger(__('There was an error on the form. Please try again.'), 'error');
                return;
            }
            try {
                $package->setPostData($_POST);
                if ($package->save()) {
					$this->session->cart = array(); // clear cart
                    if ('add' == $action) {
                        $this->_helper->flashMessenger(__('The package "%s" has been added.', $package->name), 'success');
                    } else if ('edit' == $action) {
                        $this->_helper->flashMessenger(__('The package "%s" has been updated.', $package->name), 'success');
                    }

                    $this->_helper->redirector('browse');
                    return;
                }
            // Catch validation errors.
            } catch (Omeka_Validate_Exception $e) {
                $this->_helper->flashMessenger($e);
            }
        }
    }

	/**
    * filter form (via SimpleVocal plugin)
    */
	private function _process_simple_vocab_form_filter( $form = false ) {
		// Verify entry
		if(!$form || !($form instanceof Omeka_Form_Admin)) return $form;

		// SimpleVocab plugin installed AND activated
		$plugins = Zend_Registry::get('pluginloader');
		$simple_vocab = $plugins->getPlugin('SimpleVocab');
		if(is_null($simple_vocab) || $simple_vocab->active != 1) return $form;

		// SimpleVocab table exists
		$db = get_db();
		$nb = $db->query("SHOW TABLES LIKE '".$db->SimpleVocabTerm."'")->fetchAll();
		if(count( $nb ) !== 1) return $form;

		$select = $db->getTable('SimpleVocabTerm')->getSelect()
			->reset(Zend_Db_Select::COLUMNS)
			->columns(array('element_id', 'terms'));
		$simpleVocabTerms = $db->fetchPairs($select);
		foreach ($simpleVocabTerms as $element_id => $terms) {
			$element = $db->getTable('Element')->find($element_id);
			$variable = strtolower(str_replace(" ","_",$element->name));
			$possible_terms[$variable] = array('' => __('Select Below')) + array_combine(explode("\n", $terms), explode("\n", $terms));
		}

		$elements = $form->getValues();
		$order = 0;
		foreach($elements as $element => $value){
			if(array_key_exists($element, $possible_terms)){
				$attr = $form->getElement($element)->getAttribs() +
						array(
							'label' => $form->getElement($element)->getLabel(),
							'value' => $form->getElement($element)->getValue(),
							'multiOptions' => $possible_terms[$element],
							'order' => ++$order,
						);
				$form->removeElement($element);
				$form->addElementToEditGroup(
					'select', $element,
					$attr
				);
			}
			else $form->getElement($element)->setOrder(++$order);
		}
		return $form;
	}

    /**
     * Find items with missing field in a package.
     *
     * @param $package
     * @param $field
     * @param string $elementSet
     * @return array
     */
    protected function _findIncompleteItemsInPackage($package, $field, $elementSet='Dublin Core'){
        $item_ids = array_map(function($o) {return $o->item_id;}, $package->Contents);
	    $result = array();
            foreach ($item_ids as $item_id) {
                $item = get_record_by_id('item', $item_id);
                $value = metadata($item, array($elementSet, $field));
                if($value==null){array_push($result, $item->id);}
            }
        return $result;
    }
}
