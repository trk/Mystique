<?php

namespace Altivebir\Mystique;

use ProcessWire\InputfieldWrapper;
use ProcessWire\Wire;

/**
 * Class FormManager
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\Mystique
 */
class FormManager extends Wire
{
    /**
     * @var array
     */
    protected $resource = [
        'fields' => [],
        'prefix' => '',
        'suffix' => '',
        'checkboxInputs' => [
            'InputfieldChecboxes',
            'InputfieldCheckbox',
            'InputfieldToggle'
        ],
        'noValueInputs' => [
            'InputfieldFieldset',
            'InputfieldMarkup',
            'InputfieldButton',
            'InputfieldForm',
        ]
    ];

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var array
     */
    protected $inputFields = [];

    /**
     * @var array
     */
    protected $languageFields = [];

    /**
     * @var array
     */
    protected $checkboxFields = [];

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * @var array
     */
    protected $labels = [];

    /**
     * @var array
     */
    protected $checkboxLabels = [];

    /**
     * @var array
     */
    protected $descriptions = [];

    /**
     * @var array
     */
    protected $notes = [];

    /**
     * @inheritDoc
     *
     * @param array $resource
     */
    public function __construct(array $resource = [], array $values = [])
    {
        parent::__construct();

        $this->resource = array_replace_recursive($this->resource, $resource);
        $this->values = $values;

        $this->initialize();
    }

    /**
     * Initialize
     *
     * @return void
     */
    protected function initialize()
    {
        $this->fields = $this->prepareFields($this->resource['fields']);
    }

    /**
     * Prepare fields
     *
     * @param array $fields
     * 
     * @return void
     */
    protected function prepareFields(array $fields = []): array
    {
        $data = [];

        foreach ($fields as $key => $field) {
            
            $name = isset($field['name']) ? $field['name'] : $key;
            $field['name'] = $name;

            $type = isset($field['type']) ? $field['type'] : 'text';
            $type_fallback = isset($field['type_fallback']) ? $field['type_fallback'] : false;

            if (isset($field['type'])) {
                unset($field['type']);
            }

            if (isset($field['type_fallback'])) {
                unset($field['type_fallback']);
            }

            if (strpos($type, 'Inputfield') === false) {
                $type = 'Inputfield' . ucfirst($type);
            }

            if ($this->modules->isInstalled($type)) {
                $field['type'] = $type;
            } else if ($this->modules->isInstalled($type_fallback)) {
                $field['type'] = $type_fallback;
            }

            if (!$field['type']) {
                continue;
            }

            if (isset($field['label'])) {
                $this->labels[$name] = $field['label'];
            }

            if (isset($field['checkboxLabel'])) {
                $this->checkboxLabels[$name] = $field['checkboxLabel'];
            }

            if (isset($field['description'])) {
                $this->descriptions[$name] = $field['description'];
            }

            if (isset($field['notes'])) {
                $this->notes[$name] = $field['notes'];
            }

            if (!in_array($type, $this->resource['noValueInputs'])) {

                $defaultValue = null;

                if (isset($field['value'])) {
                    $defaultValue = $field['value'];
                } else if (isset($field['defaultValue'])) {
                    $defaultValue = $field['defaultValue'];
                }

                $value = isset($this->values[$name]) ? $this->values[$name] : $defaultValue;

                if (!is_null($defaultValue)) {
                    $this->defaults[$name] = $defaultValue;
                }

                $this->values[$name] = $value ?: '';

                if ($type == 'InputfieldPage' && $value) {
                    $pages = $this->pages->newPageArray();
                    $pages->add($value);

                    $value = $pages;
                }

                $field['value'] = $value;

                if (isset($field['useLanguages']) && $field['useLanguages']) {
                    $this->languageFields[] = $name;
    
                    foreach ($this->wire('languages') ?: [] as $language) {
    
                        if ($language->isDefault()) {
                            continue;
                        }

                        $defaultValue = null;

                        if (isset($field['value' . $language->id])) {
                            $defaultValue = $field['value' . $language->id];
                        } else if (isset($field['defaultValue' . $language->id])) {
                            $defaultValue = $field['defaultValue' . $language->id];
                        }

                        $value = isset($this->values[$name . $language->id]) ? $this->values[$name . $language->id] : $defaultValue;

                        if (!is_null($defaultValue)) {
                            $this->defaults[$name . $language->id] = $defaultValue;
                        }

                        $this->values[$name . $language->id] = $value ?: '';
                        $field['value' . $language->id] = $value ?: '';
                    }
                }
    
                if (in_array($type, $this->resource['checkboxInputs'])) {
                    $this->checkboxFields[] = $name;

                    if ($field['value'] && !isset($field['attrs']['checked'])) {
                        $field['attrs']['checked'] = true;
                    }
                }

                $this->inputFields[] = $name;
            }

            if (isset($field['children'])) {
                if (is_array($field['children']) && $field['children']) {
                    $field['children'] = $this->prepareFields($field['children']);
                } else {
                    unset($field['children']);
                }
            }

            $data[$name] = $field;
        }

        return $data;
    }

    /**
     * Return Fields
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Return Checkbox Fields
     *
     * @return array
     */
    public function getCheckboxFields(): array
    {
        return $this->checkboxFields;
    }

    /**
     * Return Input Fields
     *
     * @return array
     */
    public function getInputFields(): array
    {
        return $this->inputFields;
    }

    /**
     * Return Language Fields
     *
     * @return array
     */
    public function getLanguageFields(): array
    {
        return $this->languageFields;
    }

    /**
     * Return Values
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Return Default Values
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Return Labels
     *
     * @return array
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Return Checkbox Labels
     *
     * @return array
     */
    public function getCheckboxLabels(): array
    {
        return $this->checkboxLabels;
    }

    /**
     * Return Descriptions
     *
     * @return array
     */
    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    /**
     * Return Notes
     *
     * @return array
     */
    public function getNotes(): array
    {
        return $this->notes;
    }

    /**
     * Generate ProcessWire input field
     *
     * @param array $field
     * 
     * @return Inputfield
     */
    protected function generateInput(array $field)
    {
        $name = $field['name'];
        $type = $field['type'];
        unset($field['type']);

        /**
         * @var Inputfield $inputField
         **/
        $input = $this->modules->get($type);

        if ($this->resource['prefix']) {
            $field['name'] = $this->resource['prefix'] . $field['name'];
        }

        if ($this->resource['suffix']) {
            $field['name'] = $field['name'] . $this->resource['suffix'];
        }

        foreach ($field as $property => $value) {
            
            if (is_array($value) && $property == 'showIf') {
                $conditions = [];
                foreach ($value as $name => $condition) {
                    if ($this->resource['prefix']) {
                        $name = $this->resource['prefix'] . $name;
                    }
            
                    if ($this->resource['suffix']) {
                        $name = $name . $this->resource['suffix'];
                    }
                    $conditions[] = $name . $condition;
                }
                $input->{$property} = implode(',', $conditions);
            } else if (is_array($value) && $property == 'set') {
                foreach ($value as $prop => $val) {
                    $input->set($prop, $val);
                }
            } else if (is_array($value) && $property == 'attrs') {
                foreach ($value as $prop => $val) {
                    $input->attr($prop, $val);
                }
            } else if ($property == 'attr') {
                $input->attr($property, $value);
            } else if (is_array($value) && $property == 'wrapAttrs') {
                foreach ($value as $prop => $val) {
                    $input->wrapAttr($prop, $val);
                }
            } else if ($property == 'wrapAttr') {
                $input->wrapAttr($property, $value);
            } else if (substr($property, 0, 5) === 'value') {
                $input->attr($property, $value);
            } else {
                $input->{$property} = $value;
            }

        }

        return $input;
    }

    /**
     * Generate fields
     *
     * @param array $fields
     * @param InputfieldFieldset|null $container
     * 
     * @return array|InputfieldFieldset
     */
    protected function generate(array $fields = [], $container = null)
    {
        if ($container) {
            
            foreach ($fields as $field) {
                
                $input = $this->generateInput($field);

                if (isset($field['children']) && is_array($field['children'])) {
                    $input = $this->generate($field['children'], $input);
                }

                $container->add($input);
            }

            return $container;

        }

        $data = [];

        foreach ($fields as $field) {

            $input = $this->generateInput($field);

            if (isset($field['children']) && is_array($field['children'])) {
                $input = $this->generate($field['children'], $input);
            }

            $data[$field['name']] = $input;
        }

        return $data;
    }

    /**
     * Generate fields with InputfieldWrapper
     *
     * @return InputfieldWrapper
     */
    public function generateFields($container = null)
    {
        $container = $container ?: new InputfieldWrapper();

        $fields = $this->generate($this->fields);

        foreach ($fields as $field) {

            $container->add($field);
        }

        return $container;
    }

    /**
     * Render form
     *
     * @return string
     */
    public function render(): string
    {
        $fields = $this->generate($this->fields);

        $output = '';

        foreach ($fields as $field) {
            $output .= $field->render();
        }

        return $output;
    }
}