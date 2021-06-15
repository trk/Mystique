<?php

namespace Altivebir\Mystique;

use ProcessWire\WireData;
use ProcessWire\Language;
use ProcessWire\NullPage;
use ProcessWire\PageArray;

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
     * @var array
     */
    protected $pageFieldsAsPage;

    /**
     * @inheritDoc
     */
    public function __construct($values = null, array $options = [])
    {
        parent::__construct();

        foreach (['inputFields', 'languageFields', 'checkboxFields', 'pageFields', 'pageFieldsAsPage'] as $n) {
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

        if (in_array($key, $this->pageFields)) {
            $value = parent::get($key);

            if (is_array($value)) {
                $value = $this->wire->pages->findMany('id=' . implode('|', $value));
            } else if (is_string($value) && $value) {
                $value = $this->wire->pages->get('id=' . $value);
            }

            if (!$value) {
                $derefAsPage = isset($this->pageFieldsAsPage[$key]) ? $this->pageFieldsAsPage[$key] : 0;
                if ($derefAsPage === 0) {
                    $value = new PageArray();
                } else if ($derefAsPage === 1) {
                    $value = false;
                } else if ($derefAsPage === 2) {
                    $value = new NullPage();
                }
            }

            return $value;
        }

        return parent::get($key);
    }
    
    /**
     * Get current language value, if empty get default language value
     *
     * @param string $key
     * 
     * @return void
     */
    public function getLanguageValue(string $key)
    {
        if (in_array($key, $this->languageFields) && $this->user->language instanceof Language) {
            if ($this->user->language->isDefault()) {
                $value = parent::get($key);
            } else {
                $value = parent::get($key . $this->user->language->id);
                if (!$value) {
                    $value = parent::get($key);
                }
            }
        } else {
            $value = $this->get($key);
        }

        return $value;
    }

    /**
     * @inheritDoc
     *
     * @return array
     */
    public function getArray()
    {
        $data = $this->data;

        if ($this->languageFields) {
            foreach ($this->languageFields as $key) {
                $data[$key] = $this->get($key);
            }
        }
        
        if ($this->pageFields) {
            foreach ($this->pageFields as $key) {
                $data[$key] = $this->get($key);
            }
        }

        return $data;
    }

    /**
     * Return data as array
     *
     * @return array
     */
    public function getDataArray(): array
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        return parent::set($key, $value);
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * 
     * @return MystiqueValue
     */
    public function setArray(array $data)
    {
        return parent::setArray($data);
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
            'pageFields' => $this->getPageFields(),
            'pageFieldsAsPage' => $this->pageFieldsAsPage()
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

    /**
     * Return Page Fields as Page
     *
     * @return array
     */
    public function pageFieldsAsPage(): array
    {
        return $this->pageFieldsAsPage;
    }
}
