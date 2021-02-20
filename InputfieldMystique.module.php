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
	public function __construct()
    {
		parent::__construct();
        
        $this->wire('classLoader')->addNamespace('Altivebir\Mystique', __DIR__ . '/src');

        $this->module = $this->modules->get('FieldtypeMystique');

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

        if($field->useJson && $field->jsonString) {
            $resource = json_decode($field->jsonString, true);
        } else {
            $resource = $this->module->loadResource($field->resource, $page, $field);
        }

        if (!isset($resource['fields']) || !is_array($resource['fields'])) {
            return $this;
        }

        /**
         * @var MystiqueValue $value
         */
        $value = $this->attr('value');

        $form = new FormManager([
            'prefix' => $field->name . '_',
            'suffix' => '_' . $page->id,
            'fields' => $resource['fields']
        ], $value->getArray());
        
        return $form->generateFields(new InputfieldWrapper())->render();
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

        if($field->useJson && $field->jsonString) {
            $resource = json_decode($field->jsonString, true);
        } else {
            $resource = $this->module->loadResource($field->resource, $page, $field);
        }

        if (!isset($resource['fields']) || !is_array($resource['fields'])) {
            return $this;
        }

        /**
         * @var MystiqueValue $mystiqueValue
         */
        $mystiqueValue = $this->attr('value');

        $form = new FormManager([
            'prefix' => $field->name . '_',
            'suffix' => '_' . $page->id,
            'fields' => $resource['fields']
        ], $mystiqueValue->getArray());

        $checkboxFields = $form->getCheckboxFields();
        $languageFields = $form->getLanguageFields();
        
        $post = $this->input->post;

        $values = $form->getValues();

        foreach ($form->getInputFields() as $name) {

            $rename = $field->name . '_' . $name . '_' . $page->id;
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

        $mystiqueValue->setArray($values);

        if ($mystiqueValue->isChanged()) {
            $this->trackChange('value');
            $page->trackChange($this->attr('name'));
        }

        $mystiqueValue->set('__resource', $resource);

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
        $fields = array_merge($fields, ["useJson", "jsonString", "resource"]);
        
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

                $select->addOption($resource['caller'], $resource['title']);
            }
        }

        $select->value = $this->resource;

        $wrapper->append($select);

        return $wrapper;
    }
}
