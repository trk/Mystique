<?php

namespace ProcessWire;

use Altivebir\Mystique\Finder;
use Closure;

/**
 * Class Mystique
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\Mystique
 */
class Mystique extends WireData implements Module
{

    const FIELDSET = 'InputfieldFieldset';

    const MARKUP = 'InputfieldMarkup';

    const TEXT = 'InputfieldText';

    const TEXTAREA = 'InputfieldTextarea';

    const CHECKBOX = 'InputfieldCheckbox';

    const TOGGLE = 'InputfieldToggle';

    const TOGGLE_CHECKBOX = 'InputfieldToggleCheckbox';

    const SELECT = 'InputfieldSelect';

    const INTEGER = 'InputfieldInteger';

    const IMAGE = 'InputfieldImage';

    const ICON_PICKER = 'InputfieldFontIconPicker';

    const ICON = 'InputfieldIcon';

    const COLOR = 'InputfieldColor';

    /**
     * @var string
     */
    protected $base = 'templates';

    /**
     * @var array
     */
    protected $resources = [];

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
            'summary' => __('Mystique is a config file based field creation module for ProcessWire CMS/CMF by ALTI VE BIR.'),
            'href' => 'https://www.altivebir.com',
            'author' => 'İskender TOTOĞLU | @ukyo(community), @trk (Github), https://www.altivebir.com',
            'requires' => [
                'PHP>=7.0.0',
                'ProcessWire>=3.0.0'
            ],
            'installs' => [
                'FieldtypeMystique',
                'InputfieldMystique'
            ],
            // 'permanent' => false,
            // 'permission' => 'permission-name',
            'permissions' => [],
            'icon' => 'cogs',
            'singular' => true,
            'autoload' => true
        ];
    }

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->wire('classLoader')->addNamespace('Altivebir\Mystique', __DIR__ . '/src');

        $this->setResources();
    }

    /**
     * Get element resources
     *
     * @return void
     */
    protected function setResources()
    {
        $paths = join(',', array_filter([
            $this->config->paths->templates,
            $this->config->paths->siteModules . '*/',
        ]));

        $paths = "{{$paths}}configs/{Mystique.*.php,mystique.*.php,Mystique.*.json,mystique.*.json}";

        $files = Finder::glob($paths);
        foreach ($files as $file) {
            
            $base = strtolower(strtr(dirname(dirname($file)), [
                dirname(dirname(dirname($file))) => '',
                DIRECTORY_SEPARATOR => ''
            ]));

            $ext = pathinfo($file, PATHINFO_EXTENSION);

            $name = strtolower(strtr($file, [
                dirname($file) => '',
                DIRECTORY_SEPARATOR => '',
                'Mystique.' => '',
                'mystique.' => '',
                '.' . $ext => ''
            ]));

            $this->resources[$base][$name] = [
                'path' => $file,
                'caller' => $base . '.' . $name,
                'base' => $base,
                'name' => $name
            ];
        }
    }

    /**
     * Get all resources as array list
     *
     * @return array
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * Check and get element resource
     *
     * @param string $name
     * 
     * @return array
     */
    public function getResource(string $name): array
    {
        $base = '';

        $explode = explode('.', $name);

        if (isset($explode[1])) {
            $base = $explode[0];
            $name = $explode[1];
        } else {
            $base = $this->base;
            $name = $name;
        }

        $resource = [
            'path' => '',
            'caller' => '',
            'base' => '',
            'name' => '',
            'title' => '',
            'fields' => []
        ];

        if ($base && $name) {
            $resources = $this->getResources();

            if (isset($resources[$base][$name])) {
                $resource = array_merge($resource, $resources[$base][$name]);
            }
        }

        return $resource;
    }
    
    /**
     * Load resource data
     *
     * @param string $name
     * @param Page|null $page
     * @param Field|null $field
     * 
     * @return array
     */
    public function loadResource(string $name, $page = null, $field = null): array
    {
        $resource = $this->getResource($name);

        if ($resource['path']) {

            $file = $resource['path'];
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            $data = [];

            if ($ext == 'json') {
                $data = json_decode(file_get_contents($file), true);
            } else {
                $data = require $file;

                if ($data instanceof Closure) {
                    $data = $data($page, $field);
                }
            }

            if (isset($data['title']) && $data['title']) {
                $resource['title'] = $data['title'];
            }

            $name = isset($data['name']) ? $data['name'] : basename($file);
            $resource['title'] = $resource['title'] ?: $name;

            $resource['fields'] = isset($data['fields']) && is_array($data['fields']) ? $data['fields'] : [];
        }

        return $resource;
    }

}
