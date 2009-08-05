<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
Loader::model('collection_attributes');
class DashboardPagesTypesAttributesController extends Controller {
	
	public $helpers = array('form');
	
	public function __construct() {
		parent::__construct();
		$otypes = AttributeType::getList();
		$types = array();
		foreach($otypes as $at) {
			$types[$at->getAttributeTypeID()] = $at->getAttributeTypeName();
		}
		$this->set('types', $types);
	}
	
	public function select_type() {
		$atID = $this->request('atID');
		$at = AttributeType::getByID($atID);
		$this->set('type', $at);
		$this->set('category', AttributeKeyCategory::getByHandle('collection'));
	}
	
	public function add() {
		$this->select_type();
		$type = $this->get('type');
		$cnt = $type->getController();
		$e = $cnt->validateKey();
		if ($e->has()) {
			$this->set('error', $e);
		} else {
			$ak = CollectionAttributeKey::add($this->post('akHandle'), $this->post('akName'), $this->post('akIsSearchable'), $this->post('atID'));
			$cnt->setAttributeKey($ak);
			$cnt->saveKey();
			$this->redirect('/dashboard/pages/types/', 'attribute_created');
		}
	}
	
	public function attribute_type_passthru($atID, $method) {
		$args = func_get_args();
		$type = AttributeType::getByID($atID);
		$cnt = $type->getController();
		
		$method = $args[1];
		
		array_shift($args);
		array_shift($args);
		
		call_user_func_array(array($cnt, 'action_' . $method), $args);
	}
	
	public function edit($akID = 0) {
		if ($this->post('akID')) {
			$akID = $this->post('akID');
		}
		$key = CollectionAttributeKey::getByID($akID);
		$type = $key->getAttributeType();
		$this->set('key', $key);
		$this->set('type', $type);
		$this->set('category', AttributeKeyCategory::getByHandle('collection'));
		
		if ($this->isPost()) {
			$cnt = $type->getController();
			$cnt->setAttributeKey($key);
			$e = $cnt->validateKey();
			if ($e->has()) {
				$this->set('error', $e);
			} else {
				$key->update($this->post('akHandle'), $this->post('akName'), $this->post('akIsSearchable'), $this->post('atID'));
				$cnt->saveKey();
				$this->redirect('/dashboard/pages/types/', 'attribute_updated');
			}
		}
	}
	
}