<?php

namespace Altivebir\Mystique;

use ProcessWire\Language;
use ProcessWire\WireData;

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
    protected $languageFields;

    /**
     * @inheritDoc
     */
    public function __construct(array $values = [], array $languageFields = [])
    {
        parent::__construct();

        $this->languageFields = $languageFields;

        foreach ($values as $name => $value) {
            
            $this->set($name, $value);

        }
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if (in_array($key, $this->languageFields)) {
            $user = $this->user;
            $language = $user ? $user->language : null;
            if($language instanceof Language && !$language->isDefault) {
                return parent::get($key . $language->id);
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
}