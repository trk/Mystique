<?php

namespace ProcessWire;

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
     * @var array
     */
    protected $resources = [];

    /**
     * @inheritdoc
     *
     * @return array
     */
    public static function getModuleInfo() {
        return [
            'title' => 'Mystique',
            'version' => '0.0.16',
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

        $files = $this->glob([
            $this->config->paths->siteModules . '*/configs/mystique.*.php',
            $this->config->paths->siteModules . '*/configs/Mystique.*.php',
            $this->config->paths->templates . 'configs/mystique.*.php',
            $this->config->paths->templates . 'configs/Mystique.*.php'
        ]);

        foreach ($files as $file) {
            $base = strtolower(strtr(dirname(dirname($file)), [
                dirname(dirname(dirname($file))) => '',
                '/' => '',
                '\\' => ''
            ]));

            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $name = strtr(basename($file), [
                'Mystique.' => '',
                'mystique.' => '',
                '.' => '',
                $ext => ''
            ]);

            $this->resources[$base][$name] = [
                'base' => $base,
                'name' => $name,
                'ext' => $ext,
                'path' => $file,
                'data' => []
            ];
        }
    }

    /**
     * List of resources
     *
     * @return array
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * Get resource and load resource data
     *
     * @param string $path
     * 
     * @return array
     */
    public function getResource(string $name, string $base = '')
    {
        if (!$base && strpos($name, '.') !== false) {
            $explode = explode('.', $name);
            $name = $explode[1];
            $base = $explode[0];
        }

        $resource = [
            'base' => $base,
            'name' => $name,
            'ext' => '',
            'path' => '',
            'data' => [
                'name' => 'Resource not found',
                'fields' => []
            ]
        ];

        if (isset($this->resources[$base][$name])) {
            $resource = $this->resources[$base][$name];            
            if (file_exists($resource['path'])) {
                if ($resource['ext'] == 'json') {
                    $resource['data'] = json_decode(file_get_contents($resource['path']), true);
                } else {
                    $resource['data'] = require $resource['path'];
                }
            }
        }

        return $resource;
    }

    /**
     * Glob files with braces support.
     *
     * @param array $paths
     * @param int $flags
     *
     * @return array
     */
    protected function glob(array $paths, $flags = 0): array
    {
        if (defined('GLOB_BRACE')) {
                
            $pattern = '{' . implode(',', $paths) . '}';

            $files = glob($pattern, $flags | GLOB_BRACE | GLOB_NOSORT);
        } else {

            $files = [];

            foreach ($paths as $path) {
                $files = array_merge($files, glob($path, $flags | GLOB_NOSORT) ?: []);
            }
        }

        return $files;
    }
}
