<?php

namespace ProcessWire;

use Altivebir\Mystique\MystiqueFormManager;
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
     * @var Mystique $Mystique
     */
    protected $Mystique;

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

        $this->Mystique = $this->wire("modules")->get("Mystique");

        $resource = "";

        // get resources
        $resources = $this->Mystique->resources();
        if (count($resources)) {
            $resource = reset($resources);
            $resource = $resource["__id"];
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
        /* @var MystiqueFormManager $manager */
        $manager = new MystiqueFormManager($this->field, $this->editedPage);
        /* @var $wrapper InputfieldWrapper */
        $wrapper = $this->wire(new InputfieldWrapper());
        /* @var $value MystiqueValue */
        $value = $this->attr('value');
        // add fields with values to wrapper
        $wrapper->add($manager->build($value));

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
        /* @var MystiqueFormManager $manager */
        $manager = new MystiqueFormManager($this->field, $this->editedPage);
        /* @var MystiqueValue $mystiqueValue */
        $mystiqueValue = $this->attr('value');
        // Loop all inputs and check posted data
        foreach ($manager->inputFields as $name => $value) {
            if(in_array($name, $manager->checkboxFields)) {
                $value = $this->input->post->{$manager->buildPrefix($name)};
                if($value) {
                    $mystiqueValue->set($name, '1');
                } else {
                    $mystiqueValue->set($name, '0');
                }
            } else if(in_array($name, $manager->languageFields)) {
                $value = $this->input->post->{$manager->buildPrefix($name)};
                if ($value !== null) {
                    $mystiqueValue->set($name, $value);
                }
                foreach ($this->wire('languages') ?: [] as $language) {
                    if ($language->isDefault()) {
                        continue;
                    }
                    $value = $this->input->post->{$manager->buildPrefix($name) . '__' . $language->id};
                    if ($value !== null) {
                        $mystiqueValue->set($name . $language->id, $value);
                    }
                }
            } else {
                $value = $this->input->post->{$manager->buildPrefix($name)};
                if ($value) {
                    $mystiqueValue->set($name, $value);
                } else {
                    $mystiqueValue->set($name, '');
                }
            }
        }

        if ($mystiqueValue->isChanged()) {
            $this->trackChange('value');
            $mystiqueValue->getPage()->trackChange($this->attr('name'));
        }

        $mystiqueValue->set('__json', $manager->resourceJson);
        $mystiqueValue->set('__resource', $manager->resourceName);
        $mystiqueValue->set('__path', $manager->resourcePath);


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

        /* @var InputfieldCheckbox $checkbox */
        $checkbox = $this->wire->modules->get('InputfieldCheckbox');
        $checkbox->attr('name', 'useJson');
        $checkbox->set('label', $this->_('Use JSON string'));
        $checkbox->set('checkboxLabel', $this->_('Use json string instead of a config file.'));
        $checkbox->attr('checked', $this->useJson ? 'checked' : '');

        $wrapper->append($checkbox);

        /* @var InputfieldTextarea $textarea */
        $textarea = $this->wire->modules->get('InputfieldTextarea');
        $textarea->attr('name', 'jsonString');
        $textarea->set('label', $this->_('JSON string'));
        $textarea->value = $this->jsonString;
        $textarea->showIf = "useJson!=''";

        $wrapper->append($textarea);

        /* @var InputfieldSelect $select */
        $select = $this->modules->get('InputfieldSelect');
        $select->attr('name', 'resource');
        $select->label = __('Resource');
        $select->required = true;
        $select->showIf = "useJson=''";

        // get resources
        $resources = $this->Mystique->resources();
        if (count($resources)) {
            $resource = reset($resources);

            $select->defaultValue = $resource["__id"];

            foreach ($resources as $name => $resource) {
                $select->addOption($resource["__id"], "{$resource['__title']} ({$resource['__base']})");
            }
        }

        $select->value = $this->resource;

        $wrapper->append($select);

        return $wrapper;
    }
}
