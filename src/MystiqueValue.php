<?php

namespace Altivebir\Mystique;

use ProcessWire\Language;
use ProcessWire\Mystique;
use ProcessWire\Field;
use ProcessWire\Page;
use ProcessWire\WireData;
use ProcessWire\WireException;
use ProcessWire\InputfieldMystique;

/**
 * Class MystiqueValue
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @property $resource
 * @property $path
 *
 * @package Altivebir\Mystique
 */
class MystiqueValue extends WireData
{
    /**
     * @var MystiqueFormManager
     */
    private $manager;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var InputfieldMystique
     */
    private $field;

    /**
     * @inheritDoc
     *
     * @param Page $page
     * @param InputfieldMystique $field
     * @throws WireException
     */
    public function __construct(Page $page, Field $field)
    {
        parent::__construct();

        $this->page = $page;
        $this->field = $field;

        if($field->resource) {
            $this->manager = new MystiqueFormManager($field);
            $resource = Mystique::getResource($field->resource);

            // Set default values
            foreach ($this->manager->inputFields as $name => $value) {
                if(in_array($name, $this->manager->languageFields)) {
                    foreach ($this->languages ?: [] as $language) {
                        if ($language->isDefault()) {
                            $this->set($name, $value);
                        } else {
                            $this->set($name . $language->id, $value);
                        }
                    }
                } else {
                    $this->set($name, $value);
                }
            }

            $this->set('name', $resource['__name']);
            $this->set('path', $resource['__path']);
        } else {
            throw new WireException("You need to select a resource and save field before start to use Mystique.");
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if(in_array($key, $this->manager->languageFields)) {
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

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}