<?php

namespace Altivebir\Mystique;

use ProcessWire\Mystique;
use ProcessWire\Inputfield;
use ProcessWire\InputfieldFieldset;
use ProcessWire\InputfieldMystique;
use ProcessWire\InputfieldWrapper;
use ProcessWire\Page;
use ProcessWire\Wire;
use ProcessWire\WireException;

/**
 * Class MystiqueFormManager
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\Mystique
 */
class MystiqueFormManager extends Wire
{
    /**
     * @var Mystique $Mystique
     */
    protected $Mystique;

    /* @var array $resource Resource configs */
    private $resource = [];

    /* @var string $name Name of resource */
    public $resourceName = '';

    /* @var string $name Path of resource */
    public $resourcePath = '';

    /* @var string $resourceJSON Resource data as json string */
    public $resourceJSON = '';

    /* @var string $fields Resource fields */
    public $fields = [];

    /* @var array $inputFields store all input fields name => value */
    public $inputFields = [];

    /* @var array $checkboxNames store all checkbox fields */
    public $checkboxFields = [];

    /* @var array $languageFields store all language fields */
    public $languageFields = [];

    /* @var InputfieldMystique $field */
    public $field;

    /* @var Page $page */
    public $page;

    /* @var array $values */
    protected $values = [];

    /**
     * @inheritDoc
     *
     * @param InputfieldMystique $field
     * @param Page $page
     * @throws WireException
     */
    public function __construct($field, $page)
    {
        parent::__construct();

        $this->Mystique = $this->wire("modules")->get("Mystique");
        $this->field = $field;
        $this->page = $page;

        if($this->field->useJson && $this->field->jsonString) {
            $resource = json_decode($field->jsonString, true);
        } else {
            $resource =  $this->Mystique->resource($this->field->resource);
        }

        $this->resourceName = isset($resource['__name']) ? $resource['__name'] : '';
        $this->resourcePath = isset($resource['__path']) ? $resource['__path'] : '';
        $this->resourceJSON = json_encode($resource);
        $this->resource = $resource;

        $fields = isset($this->resource["__data"]) && isset($this->resource["__data"]["fields"]) ? $this->resource["__data"]["fields"] : [];

        $this->setFields($fields);
    }

    /**
     * Set field (prepare language fields, checkboxes and other fields )
     *
     * @param array $fields
     */
    private function setFields($fields = [])
    {
        foreach ($fields as $key => $input) {
            $name = array_key_exists('name', $input) ? $input['name'] : $key;
            $type = array_key_exists('type', $input) ? $input['type'] : Mystique::TEXT;
            $type_fallback = array_key_exists('type_fallback', $input) ? $input['type_fallback'] : false;

            $field = $input;
            if(array_key_exists('type', $field)) {
                unset($field['type']);
            }

            if(strpos($type, 'Inputfield') === false) {
                $type = 'Inputfield' . ucfirst($type);
            }
            if($type_fallback && strpos($type_fallback, 'Inputfield') === false) {
                $type_fallback = 'Inputfield' . ucfirst($type_fallback);
            }

            if($this->modules->isInstalled($type)) {
                $field['type'] = $type;
            } else if($this->modules->isInstalled($type_fallback)) {
                $field['type'] = $type_fallback;
            }

            if(array_key_exists('type', $field)) {

                if(array_key_exists('useLanguages', $field) && $field['useLanguages']) {
                    $this->languageFields[] = $name;
                }

                if(!$type != Mystique::FIELDSET && !$type != Mystique::MARKUP) {
                    $value = array_key_exists('value', $field) ? $field['value'] : '';
                    if(!$value && array_key_exists('defaultValue', $field)) {
                        $value = $field['defaultValue'];
                    }
                    $this->inputFields[$name] = $value;
                }

                if ($type == Mystique::CHECKBOX || $type == Mystique::TOGGLE_CHECKBOX) {
                    $this->checkboxFields[] = $name;
                }

                if(array_key_exists('children', $field)) {
                    $this->setFields($field['children']);
                }
            }
        }
    }

    /**
     * Build fields for render form
     *
     * @param MystiqueValue $value
     * @return \ProcessWire\_Module|\ProcessWire\Module|null
     * @throws \ProcessWire\WireException
     * @throws \ProcessWire\WirePermissionException
     */
    public function build(MystiqueValue $value)
    {
        $this->values = $value instanceof MystiqueValue ? $value->array() : [];
        
        $fields = isset($this->resource["__data"]) && isset($this->resource["__data"]["fields"]) ? $this->resource["__data"]["fields"] : [];

        return $this->buildFields($fields, new InputfieldWrapper());
    }

    /**
     * Build fields for render
     *
     * @param array $fields
     * @param null $wrapper
     * @return InputfieldFieldset|InputfieldWrapper|null
     * @throws \ProcessWire\WireException
     * @throws \ProcessWire\WirePermissionException
     */
    public function buildFields($fields = [], $wrapper = null)
    {
        /* @var InputfieldWrapper|InputfieldFieldset $wrapper */
        $wrapper = $wrapper ?: $this->modules->get('InputfieldWrapper');

        if(count($fields)) {
            foreach ($fields as $key => $input) {
                $name = array_key_exists('name', $input) ? $input['name'] : $key;
                $type = array_key_exists('type', $input) ? $input['type'] : Mystique::TEXT;
                $type_fallback = array_key_exists('type_fallback', $input) ? $input['type_fallback'] : false;

                $field = $input;
                if(array_key_exists('type', $field)) {
                    unset($field['type']);
                }

                if(strpos($type, 'Inputfield') === false) {
                    $type = 'Inputfield' . ucfirst($type);
                }
                if($type_fallback && strpos($type_fallback, 'Inputfield') === false) {
                    $type_fallback = 'Inputfield' . ucfirst($type_fallback);
                }

                if($this->modules->isInstalled($type)) {
                    $field['type'] = $type;
                } else if($this->modules->isInstalled($type_fallback)) {
                    $field['type'] = $type_fallback;
                }

                if(array_key_exists('type', $field)) {
                    $field['name'] = $name;

                    if($field['type'] == Mystique::FIELDSET) {
                        if(array_key_exists('children', $field) && count($field['children'])) {
                            $children = $field['children'];
                            unset($field['children']);
                            $fieldset = $this->buildInputField($field);
                            $wrapper->add($this->buildFields($children, $fieldset));
                        }
                    } else {
                        $wrapper->add($this->buildInputField($field));
                    }
                }
            }
        }

        return $wrapper;
    }

    /**
     *  Build and return Inputfield
     *
     * @param $field
     * @return Inputfield
     * @throws \ProcessWire\WireException
     * @throws \ProcessWire\WirePermissionException
     */
    function buildInputField($field)
    {
        /* @var Inputfield $inputField */
        $inputField = $this->modules->get($field['type']);
        $type = $field['type'];
        $name = $field['name'];
        $field['name'] = $this->buildPrefix($field['name']);
        unset($field['type']);
        foreach ($field as $property => $value) {
            if (is_array($value) && $property == 'showIf') {
                $conditions = [];
                foreach ($value as $name => $condition) {
                    $conditions[] = $this->buildPrefix($name) . $condition;
                }
                $inputField->{$property} = implode(',', $conditions);
            } else if (is_array($value) && $property == 'set') {
                foreach ($value as $prop => $val) {
                    $inputField->set($prop, $val);
                }
            } else if (is_array($value) && $property == 'attrs') {
                foreach ($value as $prop => $val) {
                    $inputField->attr($prop, $val);
                }
            } else if ($property == 'attr') {
                $inputField->attr($property, $value);
            } else if (is_array($value) && $property == 'wrapAttrs') {
                foreach ($value as $prop => $val) {
                    $inputField->wrapAttr($prop, $val);
                }
            } else if ($property == 'wrapAttr') {
                $inputField->wrapAttr($property, $value);
            } else {
                $inputField->{$property} = $value;
            }
        }

        if(!$type != Mystique::FIELDSET && !$type != Mystique::MARKUP) {
            $value = '';
            if(!count($this->values)) {
                $value = array_key_exists('value', $field) ? $field['value'] : '';
                if(!$value && array_key_exists('defaultValue', $field)) {
                    $value = $field['defaultValue'];
                }
            } elseif(isset($this->values[$name])) {
                $value = $this->values[$name];
            }

            if(array_key_exists('useLanguages', $field) && $field['useLanguages']) {
                foreach ($this->wire('languages') ?: [] as $language) {
                    if ($language->isDefault()) {
                        continue;
                    }
                    if (isset($this->values[$name.$language->id])) {
                        $inputField->attr("value{$language->id}", $this->values[$name.$language->id]);
                    }
                }
            }
            if(!$type != Mystique::FIELDSET && !$type != Mystique::MARKUP) {
                $inputField->value = $value;
            }
            if ($type == Mystique::CHECKBOX || $type == Mystique::TOGGLE_CHECKBOX) {
                $inputField->attr('checked', ($value ? 'checked' : ''));
            }
        }

        return $inputField;
    }

    /**
     * Build prefix for input names
     *
     * @param string $name
     * @return string
     */
    public function buildPrefix($name = '')
    {
        return $this->field->name . '_' . $name . '_' . $this->page->id;
    }
}