<?php
/**
 * Package Manager
 *
 * @copyright Copyright 2017 id[+] Technology
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Package Manager cart controller class.
 *
 * @package PackageManager
 */
class PackageManager_CartController extends Omeka_Controller_AbstractActionController
{
    private $_ajaxRequiredActions = array(
        'add',
    );

    private $_methodRequired = array(
        'add' => array('POST')
    );
	
	protected $session;
	
    public function init()
    {
		$this->session = new Zend_Session_Namespace('pm_cart');
    }

    public function preDispatch()
    {
        $action = $this->getRequest()->getActionName();
        if (in_array($action, $this->_ajaxRequiredActions)) {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                return $this->_forward('forbidden', 'error', 'default');
            }
        }
        if (array_key_exists($action, $this->_methodRequired)) {
            if (!in_array($this->getRequest()->getMethod(),
                          $this->_methodRequired[$action])) {
                return $this->_forward('method-not-allowed', 'error', 'default');
            }
        }
		if(!is_array($this->session->cart)) $this->session->cart = array();
    }
	
    public function indexAction()
    {
        // Always go to browse.
        $this->_helper->redirector('browse');
        return;
    }
	
    public function browseAction()
    {
		$this->view->assign('cart', $this->session->cart);
	}
	
    public function clearAction()
    {
		$this->session->cart = array();
		$this->_helper->flashMessenger(__('Cart cleared'), 'success');
		$this->_helper->redirector('browse');
	}
	
	public function deleteAction()
    {
		$id = $this->getParam('id');
		if($id && preg_match("/^[0-9]+$/", $id) && array_key_exists($id, $this->session->cart ))
			unset($this->session->cart[$id]);
		$this->_helper->redirector('browse');
	}
	
	public function addAction()
    {
		$status = "ok";
		$ids = ($this->getParam('id'))?:$this->getParam('items');
        $batchAll = (boolean) $this->_getParam('batch-all');
		
		try{
			// Process Batch All items.
			if ($batchAll) {
				$params = json_decode($this->_getParam('params'), true) ?: array();
				unset($params['admin']);
				unset($params['module']);
				unset($params['controller']);
				unset($params['action']);
				unset($params['submit_search']);
				unset($params['page']);
				$records = $this->_helper->db->getTable('Item')->findBy($params);
				if (empty($records)) throw new Omeka_Validate_Exception(__("No item to batch add"));
				// Get all the ids
				$ids = array_map(function($o) {
						return $o->id;
					}, $records);
			}
 
			if(is_null($ids)) throw new Omeka_Validate_Exception(__("No item to add"));
			if(is_string($ids)) $ids = array($ids);
			$x = 0;
			$excluded_item_type = get_option('package_manager_item_type_relation');
			foreach($ids as $id){
				if($excluded_item_type>0){
					$item = $this->_helper->db->getTable('Item')->find($id);
					if($item && $excluded_item_type == $item->item_type_id) continue;
				}
				if(array_key_exists($id, $this->session->cart)) continue;
				$this->session->cart[$id]=$id;
				$x++;
			}
			if($x===0) throw new Omeka_Validate_Exception(__("No item added"));
			$result = ($x > 1) ? __("%d Items added", $x) : __("Item added");

		}
		catch (Omeka_Validate_Exception $e) {
			$status = "ko";
			$result = $e->getMessage();
		}		
		$response = array("status"=>$status, "result"=>$result);			

		// $this->view->data = Zend_Json::encode($response);	
		echo Zend_Json::encode($response);	
		$this->_helper->viewRenderer->setNoRender(true);
    }
	
	public function pushAction()
    {
		$package_id = $this->getParam('id');
		try{
			if(is_null($package_id)) throw new Omeka_Validate_Exception(__("Can't retrieve package info"));
			$package = $this->_helper->db->findById($package_id, 'PackageManagerPackage');
			if(!$package || !$package->exists()) throw new Omeka_Validate_Exception(__("Can't retrieve package info"));
			$ids = array_map(function($o) {
						return $o->item_id;
					}, $package->Contents);
			if (empty($ids)) throw new Omeka_Validate_Exception(__("No item to add"));

			$x = 0;
			foreach($ids as $id){
				if(array_key_exists($id, $this->session->cart)) continue;
				$this->session->cart[$id]=$id;
				$x++;
			}
			if($x===0) throw new Omeka_Validate_Exception(__("No item added"));
			$result = ($x > 1) ? __("%d Items added", $x) : __("Item added");		
			$this->_helper->flashMessenger($result, 'success');
		}
		catch (Exception $e) {
			$this->_helper->flashMessenger($e->getMessage(), 'error');
		}
		$this->_helper->redirector('browse');
        return;
	}
}
