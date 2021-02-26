<?php

namespace Altivebir\Mystique;

use ProcessWire\WireData;
use ProcessWire\Language;

/**
 * Class MystiqueValue
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\Mystique
 */
class MystiqueValue extends WireData
{
    /**
     * @var array
     */
    protected $inputFields;

    /**
     * @var array
     */
    protected $languageFields;

    /**
     * @var array
     */
    protected $checkboxFields;

    /**
     * @var array
     */
    protected $pageFields;

    /**
     * @inheritDoc
     */
    public function __construct($values = null, array $options = [])
    {
        parent::__construct();

        foreach (['inputFields', 'languageFields', 'checkboxFields', 'pageFields'] as $n) {
            $this->{$n} = isset($options[$n]) && is_array($options[$n]) ? $options[$n] : [];
        }

        if (is_string($values) && is_array(json_decode($values, true))) {
            $values = json_decode($values, true);
        }

        if (is_array($values)) {
            foreach ($values as $name => $value) {
            
                $this->set($name, $value);
    
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if (in_array($key, $this->languageFields) && $this->user->language instanceof Language) {
            if ($this->user->language->isDefault()) {
                return parent::get($key);
            } else {
                return parent::get($key.$this->user->language->id);
            }
        }

        return parent::get($key);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        return parent::set($key, $value);
    }

    /**
     * Return field types
     *
     * @return array
     */
    public function getFieldTypes(): array
    {
        return [
            'inputFields' => $this->getInputFields(),
            'languageFields' => $this->getLanguageFields(),
            'checkboxFields' => $this->getCheckboxFields(),
            'pageFields' => $this->getPageFields()
        ];
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
     * Return Checkbox Fields
     *
     * @return array
     */
    public function getCheckboxFields(): array
    {
        return $this->checkboxFields;
    }

    /**
     * Return Page Fields
     *
     * @return array
     */
    public function getPageFields(): array
    {
        return $this->pageFields;
    }
}
