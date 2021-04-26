<?php

namespace ProcessWire;

use Altivebir\Mystique\FormManager;
use Altivebir\Mystique\MystiqueValue;

/**
 * Class InputfieldMystique
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @property string $resource
 * @property bool $useJson
 * @property string $jsonString
 *
 * @package Altivebir\Mystique
 */
class InputfieldMystique extends Inputfield
{
    /**
     * @var FieldtypeMystique
     */
    protected $module;

    /**
     * @var InputfieldMystique
     */
    private $field;

    /**
     * @var Page $editedPage
     */
    private $editedPage;

    /**
     * @inheritdoc
     *
     * @return array
     */
	public static function getModuleInfo()
    {
        return [
            'title' => 'Mystique',
            'version' => '0.0.17',
            'summary' => __('Provides builder input for ProcessWire CMS/CMF by ALTI VE BIR.'),
            'href' => 'https://www.altivebir.com',
            'author' => 'İskender TOTOĞLU | @ukyo(community), @trk (Github), https://www.altivebir.com',
            'requires' => [
                'PHP>=7.0.0',
                'ProcessWire>=3.0.0',
                'FieldtypeMystique'
            ],
            'icon' => 'cogs'
        ];
	}
    
    /**
     * @inheritDoc
     */
	public function __construct($module = null)
    {
		parent::__construct();
        
        $this->wire('classLoader')->addNamespace('Altivebir\Mystique', __DIR__ . '/src');

        $this->module = $module ?: $this->modules->get('FieldtypeMystique');

        $resource = '';

        foreach ($this->module->getResources() as $base => $resources) {

            if ($resource) {
                continue;
            }

            foreach ($resources as $name => $source) {
                $resource = $source['caller'];
            }
        }

        // Set default resource
        $this->set('resource', $resource);
        $this->set('allowImport', false);
        $this->set('allowExport', false);
        $this->set('useJson', false);
        $this->set('jsonString', '');
	}

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isEmpty()
    {
        return (!$this->value);
    }

    /**
     * @inheritDoc
     *
     * @param Field $field
     */
    public function setField(Field $field)
    {
        $this->field = $field;
    }

    /**
     * @inheritDoc
     *
     * @return Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @inheritDoc
     *
     * @param Page $page
     */
    public function setEditedPage(Page $page)
    {
        $this->editedPage = $page;
    }

    /**
     * @inheritDoc
     *
     * @return Page
     */
    public function getEditedPage()
    {
        return $this->editedPage;
    }

    /**
     * @inheritdoc
     *
     * @param array|string $key
     * @param array|int|string $value
     * @return Inputfield
     * @throws WireException
     */
	public function setAttribute($key, $value)
    {
		if($key == 'value' && !$value instanceof MystiqueValue && !is_null($value)) {
			throw new WireException("This input only accepts a PageBuilderValue for it's value");
		}

		return parent::setAttribute($key, $value); 
	}

    /**
     * @inheritdoc
     *
     * @return string
     * @throws WireException
     * @throws WirePermissionException
     */
	public function ___render()
    {
        $page = $this->getEditedPage();
        $field = $this->getField();
        $value = $this->attr('value');

        if($field->useJson && $field->jsonString) {
            $resource = json_decode($field->jsonString, true);
        } else {
            $resource = $this->module->loadResource($field->resource, $page, $field, $value);
        }

        if (!isset($resource['fields']) || !is_array($resource['fields'])) {
            return $this;
        }

        $form = new FormManager([
            'prefix' => $field->name . '_',
            'suffix' => '_' . $page->id,
            'fields' => $resource['fields']
        ], $value->getArray());

        $values = $form->getValues();

        $form = $form->generateFields(new InputfieldWrapper());

        $script = '';

        bd($field->allowImport);
        bd($field->allowExport);

        if ($field->allowImport) {
            /**
             * @var InputfieldTextarea $import
             */
            $import = $this->modules->get('InputfieldTextarea');
            $import->collapsed = Inputfield::collapsedYes;
            $import->attr('name', $field->name . '_import_data_' . $page->id);
            $import->label = $this->_('Import');
            $import->description = $this->_('Paste in the data from an export.');
            $import->notes = $this->_('Copy the export data from another field then paste into the box above with CTRL-V or CMD-V.');
            $import->icon = 'paste';
            $form->add($import);
        }

        if ($field->allowImport) {
            /**
             * @var InputfieldTextarea $export
             */
            $export = $this->wire('modules')->get('InputfieldTextarea');
            $export->collapsed = Inputfield::collapsedYes;
            $export->attr('id+name', $field->name . '_export_data_' . $page->id);
            $export->label = $this->_('Export');
            $export->description = $this->_('Copy and paste this data into the "Import" box of another installation.');
            $export->notes = $this->_('Click anywhere in the box to select all export data. Once selected, copy the data with CTRL-C or CMD-C.');
            $export->icon = 'copy';
            $export->attr('value', wireEncodeJSON($values, true, true));
            $form->add($export);

            $script = '<script>$(document).ready(function() {$("#' . $field->name . '_export_data_' . $page->id . '").click(function() { $(this).select(); });});</script>';
        }
        
        
        return $form->render() . $script;
	}

    /**
     * @inheritdoc
     *
     * @param WireInputData $input
     * @return $this|Inputfield
     * @throws WireException
     */
    public function ___processInput(WireInputData $input)
    {
        $page = $this->getEditedPage();
        $field = $this->getField();
        $mystiqueValue = $this->attr('value');

        if($field->useJson && $field->jsonString) {
            $resource = json_decode($field->jsonString, true);
        } else {
            $resource = $this->module->loadResource($field->resource, $page, $field, $mystiqueValue);
        }

        if (!isset($resource['fields']) || !is_array($resource['fields'])) {
            return $this;
        }

        $form = new FormManager([
            'prefix' => $field->name . '_',
            'suffix' => '_' . $page->id,
            'fields' => $resource['fields']
        ], $mystiqueValue->getArray());

        $checkboxFields = $form->getCheckboxFields();
        $languageFields = $form->getLanguageFields();
        
        $post = $this->input->post;

        $values = $form->getValues();

        $importData = [];

        if ($field->allowImport) {
            $json = $post->get($field->name . '_import_data_' . $page->id);
            $json = $json ? json_decode($json, true) : [];
            $importData = is_array($json) ? $json : [];
        }

        foreach ($form->getInputFields() as $name) {

            $rename = $field->name . '_' . $name . '_' . $page->id;

            if (isset($importData[$name])) {
                $values[$name] = $importData[$name];
            } else {
                $value = $post->get($rename);

                if (in_array($name, $checkboxFields)) {

                    $values[$name] = $value ? 1 : 0;

                } else if (in_array($name, $languageFields)) {

                    if ($value !== null) {
                        $values[$name] = $value;
                    }

                    foreach ($this->wire('languages') ?: [] as $language) {

                        if ($language->isDefault()) {
                            continue;
                        }

                        $val = $post->get($rename . '__' . $language->id);

                        if ($val !== null) {
                            $values[$name . $language->id] = $val;
                        }

                    }

                } else {

                    $values[$name] = $value ?: '';
                    
                }
            }
        }

        $mystiqueValue->setArray($values);

        if ($mystiqueValue->isChanged()) {
            $this->trackChange('value');
            $page->trackChange($this->attr('name'));
        }

        $mystiqueValue->set('__resource', $resource['caller']);

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @param Field $field
     * 
     * @return void
     */
    public function ___getConfigAllowContext($field)
    {
        $fields = parent::___getConfigAllowContext($field);
        $fields = array_merge($fields, ['allowImport', 'allowExport', 'useJson', 'jsonString', 'resource']);
        
        return $fields;
	}

    /**
     * @inheritdoc
     */
    public function ___getConfigInputfields()
    {
        $wrapper = parent::___getConfigInputfields();

        /** @var InputfieldCheckbox $checkbox */
        $checkbox = $this->wire->modules->get('InputfieldCheckbox');
        $checkbox->attr('name', 'allowImport');
        $checkbox->set('label', $this->_('Allow import input values'));
        $checkbox->set('description', $this->_('This option will add an input, bottom of your Mystique input'));
        $checkbox->set('checkboxLabel', $this->_('Allow import'));
        $checkbox->attr('checked', $this->allowImport ? 'checked' : '');

        $wrapper->append($checkbox);

        /** @var InputfieldCheckbox $checkbox */
        $checkbox = $this->wire->modules->get('InputfieldCheckbox');
        $checkbox->attr('name', 'allowExport');
        $checkbox->set('label', $this->_('Allow export input values'));
        $checkbox->set('description', $this->_('This option will add an input, bottom of your Mystique input'));
        $checkbox->set('checkboxLabel', $this->_('Allow export'));
        $checkbox->attr('checked', $this->allowExport ? 'checked' : '');

        $wrapper->append($checkbox);

        /** @var InputfieldCheckbox $checkbox */
        $checkbox = $this->wire->modules->get('InputfieldCheckbox');
        $checkbox->attr('name', 'useJson');
        $checkbox->set('label', $this->_('Use JSON string'));
        $checkbox->set('checkboxLabel', $this->_('Use json string instead of a config file.'));
        $checkbox->attr('checked', $this->useJson ? 'checked' : '');

        $wrapper->append($checkbox);

        /** @var InputfieldTextarea $textarea */
        $textarea = $this->wire->modules->get('InputfieldTextarea');
        $textarea->attr('name', 'jsonString');
        $textarea->set('label', $this->_('JSON string'));
        $textarea->value = $this->jsonString;
        $textarea->showIf = "useJson!=''";

        $wrapper->append($textarea);

        /** @var InputfieldSelect $select */
        $select = $this->modules->get('InputfieldSelect');
        $select->attr('name', 'resource');
        $select->label = __('Resource');
        $select->required = true;
        $select->showIf = "useJson=''";

        $page = $this->getEditedPage();
        $field = $this->getField();

        foreach ($this->module->getResources() as $base => $resources) {
            foreach ($resources as $name => $resource) {

                if (!$select->defaultValue) {
                    $select->defaultValue = $resource['caller'];
                }

                $resource = $this->module->loadResource($resource['caller'], $page, $field);

                $select->addOption($resource['caller'], $resource['title'] . ' (' . $resource['base'] . ')');
            }
        }

        $select->value = $this->resource;

        $wrapper->append($select);

        return $wrapper;
    }
}
