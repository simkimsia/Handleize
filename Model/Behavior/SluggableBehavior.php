<?php

App::import('Inflector');

class SluggableBehavior extends ModelBehavior {

    private $_settings = array();

    function setup(&$model, $settings = array()) {
        $default = array(
            'fields' => 'title',
            'scope' => false,
            'conditions' => false,
            'slugfield' => 'slug',
            'separator' => '-',
            'overwrite' => false,
            'length' => 256,
            'lower' => true
        );

        $this->_settings[$model->alias] = (!empty($settings)) ? $settings + $default : $default;
    }

    function beforeSave(&$model) {
        $fields = (array) $this->_settings[$model->alias]['fields'];
        $scope = (array) $this->_settings[$model->alias]['scope'];
        $conditions = !empty($this->_settings[$model->alias]['conditions']) ? (array) $this->_settings[$model->alias]['conditions'] : array();
        $slugfield = $this->_settings[$model->alias]['slugfield'];
        $hasFields = true;

        foreach ($fields as $field) {
            if (!$model->hasField($field)) {
                $hasFields = false;
            }

            if (!isset($model->data[$model->alias][$field])) {
                $hasFields = false;
            }
        }

        if ($hasFields && $model->hasField($slugfield) && ($this->_settings[$model->alias]['overwrite'] || empty($model->id))) {
            $toSlug = array();

            foreach ($fields as $field) {
                $toSlug[] = $model->data[$model->alias][$field];
            }
            
            $toSlug = join(' ', $toSlug);

            $slug = Inflector::slug($toSlug, $this->_settings[$model->alias]['separator']);

            if ($this->_settings[$model->alias]['lower']) {
                $slug = strtolower($slug);
            }

            if (strlen($slug) > $this->_settings[$model->alias]['length']) {
                $slug = substr($slug, 0, $this->_settings[$model->alias]['length']);
            }

            $conditions[$model->alias . '.' . $slugfield . ' LIKE'] = $slug . '%';

            if (!empty($model->id)) {
                $conditions[$model->alias . '.' . $model->primaryKey . ' !='] = $model->id;
            }

            if (!empty($scope)) {
                foreach ($scope as $s) {
                    if (isset($model->data[$model->alias][$s]) && !empty($model->data[$model->alias][$s])) {
                        $conditions[$model->alias . '.' . $s] = $model->data[$model->alias][$s];
                    }
                }
            }

            $sameUrls = $model->find('all', array(
                'recursive'  => -1,
                'conditions' => $conditions,
                'fields'     => array($slugfield, 'id'),
            ));

            $sameUrls = (!empty($sameUrls)) ?
                Set::extract($sameUrls, '{n}.' . $model->alias . '.' . $slugfield) :
                null;

            if ($sameUrls) {
                if (in_array($slug, $sameUrls)) {
                    $begginingSlug = $slug;
                    $index = 1;

                    while ($index > 0) {
                        if (!in_array($begginingSlug . $this->_settings[$model->alias]['separator'] . $index, $sameUrls)) {
                            $slug = $begginingSlug . $this->_settings[$model->alias]['separator'] . $index;
                            $index = -1;
                        }

                        $index++;
                    }
                }
            }

            if (!empty($model->whitelist) && !in_array($slugfield, $model->whitelist)) {
                $model->whitelist[] = $slugfield;
            }

            $model->data[$model->alias][$slugfield] = $slug;
        }

        return parent::beforeSave($model);
    }
}