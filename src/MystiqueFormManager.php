<?php

namespace Altivebir\Mystique;

use ProcessWire\Mystique;
use ProcessWire\Inputfield;
use ProcessWire\InputfieldMystique;
use ProcessWire\InputfieldWrapper;
use ProcessWire\Wire;

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
    /* @var array $resource Resource configs */
    private $resource = [];

    /* @var string $name Name of resource */
    public $resourceName = '';

    /* @var string $name Path of resource */
    public $resourcePath = '';

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

    /**
     * @inheritDoc
     *
     * @param $field
     * @throws \ProcessWire\WireException
     */
    public function __construct($field)
    {
        parent::__construct();

        $this->field = $field;

        $resource =  Mystique::getResource($this->field->resource);

        $this->resourceName = $resource['__name'];
        $this->resourcePath = $resource['__path'];
        $this->resource = $resource;

        $this->prepareFields($this->resource['fields']);
    }

    public function buildFields(MystiqueValue $value)
    {
        return $this->prepareFields($this->resource['fields'], $value);
    }

    /**
     * Prepare fields for add wrapper
     *
     * @param array $fields
     * @param null $value
     * @return array
     * @throws \ProcessWire\WireException
     */
    private function prepareFields($fields = [], $value = null)
    {
        $data = [];

        foreach ($fields as $name => $input) {

            $type = array_key_exists('type', $input) ? $input['type'] : Mystique::TEXT;
            $type_fallback = array_key_exists('type_fallback', $input) ? $input['type_fallback'] : Mystique::TEXT;

            $field = $input;

            if(array_key_exists('type', $field)) {
                unset($field['type']);
            }

            if($this->modules->isInstalled($type)) {
                $field['type'] = $type;
            } else if($this->modules->isInstalled($type_fallback)) {
                $field['type'] = $type_fallback;
            }

            if(array_key_exists('type', $field)) {

                $val = '';
                if(is_null($value)) {
                    $val = array_key_exists('value', $field) ? $field['value'] : '';
                    if(!$val && array_key_exists('defaultValue', $field)) {
                        $val = $field['defaultValue'];
                    }
                } elseif($value instanceof MystiqueValue && $value->get($name)) {
                    $val = $value->get($name);
                }

                if(array_key_exists('useLanguages', $field) && $field['useLanguages']) {
                    $this->languageFields[] = $name;
                    foreach ($this->wire('languages') ?: [] as $language) {
                        if ($language->isDefault()) {
                            continue;
                        }
                        if ($value instanceof MystiqueValue && $value->get($name . $language->id)) {
                            $field['value' . $language->id] = $value->get($name . $language->id);
                        }
                    }
                }

                if(!$type != Mystique::FIELDSET && !$type != Mystique::MARKUP) {
                    $this->inputFields[$name] = $val;
                    $field['value'] = $val;
                }

                if ($type == Mystique::CHECKBOX || $type == Mystique::TOGGLE_CHECKBOX) {
                    $this->checkboxFields[] = $name;
                    if($val == 1) {
                        $field['attr']['checked'] = 'checked';
                    }
                }

                $label = array_key_exists('label', $field) ? $field['label'] : $name;

                $field['name'] = $this->addPrefix($name);
                $field['label'] = $label;

                if(array_key_exists('showIf', $field) && is_array($field['showIf'])) {
                    $field['showIf'] = $this->showIf($field['showIf']);
                }
                if(array_key_exists('children', $field)) {
                    $field['children'] = $this->prepareFields($field['children'], $value);
                }

                $data[] = $field;
            }
        }

        return $data;
    }

    /**
     * Add prefix to given name
     *
     * @param string $name
     * @return string
     */
    public function addPrefix($name = '')
    {
        return $this->field->name . '_' . $name;
    }

    /**
     * Convert array showIf to string
     *
     * @param $showIf
     * @return string
     */
    protected function showIf($showIf) {

        $stringIF = '';
        $separator = ', ';

        $i = 1;
        foreach ($showIf as $name => $condition) {
            $x = $i++;
            $stringIF .= $this->addPrefix($name) . $condition;
            if($x < count($showIf)) $stringIF .= $separator;
        }

        return $stringIF;
    }

    /**
     * Populate the given form with the given data.
     *
     * @param InputfieldWrapper $form
     * @param array $values
     * @throws \ProcessWire\WireException
     */
    public function populateValues(InputfieldWrapper $form, array $values)
    {
        $form->populateValues($values);

        foreach ($form->getAll() as $inputfield) {
            if ($inputfield->useLanguages) {
                $this->populateLanguageValue($inputfield, $values);
            }
        }
    }

    /**
     * Populate values of all languages to the given inputfield.
     *
     * @param Inputfield $inputfield
     * @param array $values
     * @throws \ProcessWire\WireException
     */
    private function populateLanguageValue(Inputfield $inputfield, array $values)
    {
        foreach ($this->wire('languages') ?: [] as $language) {
            $langId = $language->id;
            $name = $inputfield->attr('name');
            $value = $values[$name . $langId] ?? $values[$name] ?? '';
            $inputfield->set("value{$langId}", $value);
        }
    }

    /**
     * Build and return the inputfield for a given SEO config, e.g. meta description.
     *
     * @param string $group
     * @param string $name
     * @param array $options
     *
     * @return \ProcessWire\Inputfield
     */
    private function getInputfield($group, $name, array $options)
    {
        $getter = 'getInputfield' . ucfirst($group);

        $inputfield = $this->{$getter}($name, $options);

        $inputfield->attr('name', sprintf('%s_%s', $group, $name));
        $inputfield->label = $options['label'] ?? $name;
        $inputfield->description = $options['description'] ?? '';
        $inputfield->notes = $options['notes'] ?? '';
        $inputfield->useLanguages = $options['translatable'] ?? false;

        return $inputfield;
    }
}