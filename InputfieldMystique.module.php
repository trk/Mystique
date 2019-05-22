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
 *
 * @package Altivebir\Mystique
 */
class InputfieldMystique extends Inputfield {

    /* @var array $resources */
    private $resources = [];

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
            'version' => 5,
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

        $this->resources = Mystique::getResources();

        $resource = '';
        if(count($this->resources)) {
            $resource = array_keys($this->resources)[0];
        }

        // Set default resource
        $this->set('resource', $resource);
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

        $mystiqueValue->set('__resource', $manager->resourceName);
        $mystiqueValue->set('__path', $manager->resourcePath);


        return $this;
    }

    /**
     * @inheritdoc
     */
    public function ___getConfigInputfields()
    {
        $resources = Mystique::getResources();

        $wrapper = parent::___getConfigInputfields();

        /* @var InputfieldSelect $select */
        $select = $this->modules->get('InputfieldSelect');
        $select->attr('name', 'resource');
        $select->label = __('Resource');
        $select->required = true;
        if(count($resources)) {
            $select->defaultValue = array_keys($resources)[0];
        }
        foreach ($resources as $name => $resource) {
            $title = array_key_exists('title', $resource) ? $resource['title'] : $name;
            $select->addOption($name, $title);
        }

        $select->value = $this->resource;

        $wrapper->append($select);

        return $wrapper;
    }
}
