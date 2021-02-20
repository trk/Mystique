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
            'version' => '0.0.16',
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
     * @inheritdoc
     *
     * @throws WireException
     */
	public function __construct()
    {
		parent::__construct();

        $this->wire('classLoader')->addNamespace('Altivebir\Mystique', __DIR__ . '/src');

        /**
         * @var Mystique
         */
        $mystique = $this->modules->get('Mystique');

        $resource = '';

        foreach ($mystique->getResources() as $base => $resources) {

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
     * @param Page $page
     */
    public function setEditedPage(Page $page)
    {
        $this->editedPage = $page;
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
        /**
         * @var Mystique
         */
        $mystique = $this->modules->get('Mystique');

        if($this->field->useJson && $this->field->jsonString) {
            $resource = json_decode($this->field->jsonString, true);
        } else {
            $resource = $mystique->loadResource($this->field->resource, $this->editedPage, $this->field);
        }

        if (!isset($resource['fields']) || !is_array($resource['fields'])) {
            return $this;
        }

        /**
         * @var MystiqueValue $mystiqueValue
         */
        $mystiqueValue = $this->attr('value');

        $form = new FormManager([
            'prefix' => $this->field->name . '_',
            'suffix' => '_' . $this->editedPage->id,
            'fields' => $resource['fields']
        ], $mystiqueValue->getArray());
        
        $wrapper = $form->generateFields(new InputfieldWrapper());

		return $wrapper->render();
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
        /**
         * @var Mystique
         */
        $mystique = $this->modules->get('Mystique');

        if($this->field->useJson && $this->field->jsonString) {
            $resource = json_decode($this->field->jsonString, true);
        } else {
            $resource = $mystique->loadResource($this->field->resource, $this->editedPage, $this->field);
        }

        if (!isset($resource['fields']) || !is_array($resource['fields'])) {
            return $this;
        }

        /**
         * @var MystiqueValue $mystiqueValue
         */
        $mystiqueValue = $this->attr('value');

        $form = new FormManager([
            'prefix' => $this->field->name . '_',
            'suffix' => '_' . $this->editedPage->id,
            'fields' => $resource['fields']
        ], $mystiqueValue->getArray());

        $checkboxFields = $form->getCheckboxFields();
        $languageFields = $form->getLanguageFields();
        
        // Loop all inputs and check posted data
        foreach ($form->getValues() as $name => $value) {
            $_name = $this->field->name . '_' . $name . '_' . $this->editedPage->id;
            $value = $this->input->post->{$_name};
            if(in_array($name, $checkboxFields)) {
                if($value) {
                    $mystiqueValue->set($name, '1');
                } else {
                    $mystiqueValue->set($name, '0');
                }
            } else if(in_array($name, $languageFields)) {
                if ($value !== null) {
                    $mystiqueValue->set($name, $value);
                }
                foreach ($this->wire('languages') ?: [] as $language) {
                    if ($language->isDefault()) {
                        continue;
                    }
                    $value = $this->input->post->{$_name . '__' . $language->id};
                    if ($value !== null) {
                        $mystiqueValue->set($name . $language->id, $value);
                    }
                }
            } else {
                if ($value) {
                    $mystiqueValue->set($name, $value);
                } else {
                    $mystiqueValue->set($name, '');
                }
            }
        }

        if ($mystiqueValue->isChanged()) {
            $this->trackChange('value');
            $this->editedPage->trackChange($this->attr('name'));
        }

        $mystiqueValue->set('__resource', $resource);

        return $this;
    }

    public function ___getConfigAllowContext($field) {
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

        /**
         * @var Mystique
         */
        $mystique = $this->modules->get('Mystique');
        foreach ($mystique->getResources() as $base => $resources) {
            foreach ($resources as $name => $resource) {

                if (!$select->defaultValue) {
                    $select->defaultValue = $resource['caller'];
                }

                $resource = $mystique->loadResource($resource['caller'], $this->editedPage, $this->field);

                $select->addOption($resource['caller'], $resource['title']);
            }
        }

        $select->value = $this->resource;

        $wrapper->append($select);

        return $wrapper;
    }
}
