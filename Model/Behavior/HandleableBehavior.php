<?php
/**
 * Handleable Behavior
 *
 * This behavior is meant for constructing virtual fields for handle/permalinks
 * 
 * 
 * 
 * @author Sim Kim Sia (aka keisimone in github.com, Zeu5 in #cakephp irc channel, kimcity@gmail.com)
 * @package app
 * @subpackage app.models.behaviors
 * @filesource http://github.com/...
 * @version 0.1
 * @lastmodified 2011-04-16
 */

class HandleableBehavior extends ModelBehavior {
/**
 * The default options for the behavior
 */
	var $defaultOptions = array(
                'handleFieldName' => 'handle',
                'virtualFieldName' => 'url',
                
	);

	

/**
 * The array that saves the $options for the behavior
 */
	var $__fields = array();



/**
 * Constructor
 *
 */
	function __construct() {
		
		
	}

/**
 * Setup the behavior. It stores a reference to the model, merges the default options with the options for each field, and setup the validation rules.
 *
 * @param $model Object
 * @param $settings Array[optional]
 * @return null
 * @author Sim Kim Sia
 */
	function setup(&$model, $settings = array()) {
		
                $this->__fields[$model->alias] = array_merge($this->defaultOptions, $settings );
                
	}


/**
 * Performs a toggle on the field
 *
 * @param $model Object
 * @param $id The primary key of the record
 * @param $fieldName The fieldname that we are going to toggle
 * @return boolean Whether the update is successful. Even if no fields are changed, true is returned
 * @author Sim Kim Sia
 **/

        function createVirtualFieldForUrl(&$model, $controller = null, $action=null) {
                
                if ($controller == null) {
                        $controller = Inflector::tableize($model->alias);
                }
                
                if ($action == null) {
                        $action = 'view';
                }
                
		if (!isset($model->virtualFields[$this->__fields[$model->alias]['virtualFieldName']])) {
                        $handleFieldName = $this->__fields[$model->alias]['handleFieldName'];
                        $virtualFieldName = $this->__fields[$model->alias]['virtualFieldName'];
                        //$model->virtualFields[$virtualFieldName] = "CONCAT('/products/',`{$this->alias}`.`handle`)";
                        $model->virtualFields[$virtualFieldName] = "CONCAT('/', '$controller', '/', `{$model->alias}`.`$handleFieldName`)";
                                
                }
                
	}
        
        
/**
 * Merges two arrays recursively
 * primeminister / 2009-11-13 : Added fix for numeric arrays like allowedMime and allowedExt.
 * These values will remain intact even if the passed options were shorter.
 * Solved that with array_splice to keep intact the previous indexes (already merged)
 *
 * @param $arr Array
 * @param $ins Array
 * @return array
 * @author Vinicius Mendes
 */
	function _arrayMerge($arr, $ins) {
		if (is_array($arr)) {
			if (is_array($ins)) {
				foreach ($ins as $k => $v) {
					if (isset($arr[$k]) && is_array($v) && is_array($arr[$k])) {
						$arr[$k] = $this->_arrayMerge($arr[$k], $v);
					} elseif (is_numeric($k)) {
						array_splice($arr, $k, count($arr));
						$arr[$k] = $v;
					} else {
						$arr[$k] = $v;
					}
				}
			}
		} elseif (!is_array($arr) && (strlen($arr) == 0 || $arr == 0)) {
			$arr = $ins;
		}
		return $arr;
	}
}
?>