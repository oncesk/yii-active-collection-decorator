<?php
/**
 * Class ActiveCollectionDecorator
 */
class ActiveCollectionDecorator extends CMap {

	/**
	 * @var CActiveRecord
	 */
	protected $_owner;

	/**
	 * @var array
	 */
	protected $_attributeNames;

	/**
	 * @var array
	 */
	protected $_relations;

	/**
	 * @param CActiveRecord   $model
	 * @param CActiveRecord[] $models
	 */
	public function __construct(CActiveRecord $model, array $models) {
		parent::__construct($models);
		$this->_owner = $model;
		$this->_attributeNames = $model->attributeNames();
		$this->_relations = $model->relations();
	}

	public function save() {
		$this->_redirectCall('save');
		return true;
	}

	public function delete() {
		$this->_redirectCall('delete');
		return true;

	}

	public function refresh() {
		$this->_redirectCall('refresh');
		return true;
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		$attributes = array();
		$this->_each(function (CActiveRecord $model) use (&$attributes) {
			$attributes[] = $model->getAttributes();
		});
		return $attributes;
	}

	/**
	 * Get collection values for concrete $attribute
	 *
	 * @param string $attribute
	 *
	 * @return array list of $attribute values
	 */
	public function getAttribute($attribute) {
		$values = array();
		if (is_string($attribute)) {
			$this->_each(function (CActiveRecord $model) use (&$values, $attribute) {
				$values[] = $model->getAttribute($attribute);
			});
		}
		return $values;
	}

	/**
	 * @param $attribute
	 * @param $value
	 */
	public function setAttribute($attribute, $value) {
		if (in_array($attribute, $this->_attributeNames)) {
			$this->_each(function (CActiveRecord $model) use ($attribute, $value) {
				$model->setAttribute($attribute, $value);
			});
		}
	}

	/**
	 * @param string $name
	 * @param bool   $refresh
	 * @param array  $params
	 *
	 * @return array
	 */
	public function getRelated($name, $refresh = false, $params = array()) {
		$related = array();
		if (array_key_exists($name, $this->_relations)) {
			$this->_each(function (CActiveRecord $model) use (&$related, $name, $refresh, $params) {
				$related[] = $model->getRelated($name, $refresh, $params);
			});
		}
		return $related;
	}

	/**
	 * Fetch models attributes
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get($name) {
		if (in_array($name, $this->_attributeNames)) {
			return $this->getAttribute($name);
		} else if (array_key_exists($name, $this->_relations)) {
			return $this->getRelated($name);
		}
		return array();
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return mixed|void
	 */
	public function __set($name, $value) {
		$this->setAttribute($name, $value);
	}

	/**
	 * @param string $method
	 * @param array  $arguments
	 */
	protected function _redirectCall($method, array $arguments = array()) {
		$this->_each(function (CActiveRecord $model) use ($method, $arguments) {
			call_user_func_array(array($model, $method), $arguments);
		});
	}

	/**
	 * @param \Closure|callable $callback
	 * @param array             $parameters
	 */
	protected function _each($callback, array $parameters = array()) {
		foreach ($this as $model) {
			call_user_func($callback, $model, $parameters);
		}
	}
}