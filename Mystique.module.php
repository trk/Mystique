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
class Mystique extends WireData implements Module {

    const FIELDSET = 'InputfieldFieldset';

    const MARKUP = 'InputfieldMarkup';

    const TEXT = 'InputfieldText';

    const TEXTAREA = 'InputfieldTextarea';

    const CHECKBOX = 'InputfieldCheckbox';

    const TOGGLE_CHECKBOX = 'InputfieldToggleCheckbox';

    const SELECT = 'InputfieldSelect';

    const INTEGER = 'InputfieldInteger';

    const IMAGE = 'InputfieldImage';

    const ICON_PICKER = 'InputfieldFontIconPicker';

    const ICON = 'InputfieldIcon';

    const COLOR = 'InputfieldColor';

    // const $pathSchema = 'resource.*.php';

    /* @var array $resources */
    protected static $resources = [];

    /* @var array $paths */
    protected static $paths = [];

    /* @var string $class_name static class name*/
    protected static $class_name = '';

    /**
     * @inheritdoc
     *
     * @return array
     */
    public static function getModuleInfo() {
        return [
            'title' => 'Mystique',
            'version' => 1,
            'summary' => __('Mystique is a config file based field creation module for ProcessWire CMS/CMF by ALTI VE BIR.'),
            'href' => 'https://www.altivebir.com',
            'author' => 'İskender TOTOĞLU | @ukyo(community), @trk (Github), https://www.altivebir.com',
            'requires' => [
                'PHP>=7.0.0',
                'ProcessWire>=3.0.0',
                'FieldtypeMystique',
                'InputfieldMystique'
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

    public function ready()
    {
        self::$class_name = $this->className;

        // Add default paths
        self::addPath(__DIR__ . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR);
        self::addPath($this->config->paths->templates . 'configs' . DIRECTORY_SEPARATOR);
        self::addPath($this->config->paths->siteModules . 'Altivebir' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR);

        // Add resources from given paths
        $paths = glob('{' . implode(',', self::$paths) . '}', GLOB_BRACE);
        foreach ($paths as $k => $path) {
            $name = str_replace([realpath($path), $this->className . '.', '.php'], '', basename($path));
            self::$resources[$name] = $path;
        }
    }

    /**
     * Add resource to resources
     *
     * @param string $name
     * @param string $path
     */
    public static function addResource(string $name, string $path)
    {
        self::$resources[$name] = $path;
    }

    /**
     * Get named resource
     *
     * @param string $name
     * @return array|mixed
     */
    public static function getResource(string $name)
    {
        if(array_key_exists($name, self::$resources)) {
            $path = self::$resources[$name];
            if(file_exists($path)) {
                $resource = include $path;
                $resource['__name'] = $name;
                $resource['__path'] = $path;
                return $resource;
            }
        }

        return [];
    }

    /**
     * Add path to paths
     *
     * @param string $path
     */
    public static function addPath(string $path)
    {
        $path = $path . self::$class_name . '.*.php';

        if(!in_array($path, self::$paths)) {
            self::$paths[] = $path;
        }
    }

    /**
     * Get all paths
     *
     * @return array
     */
    public static function getPaths()
    {
        return self::$paths;
    }

    /**
     * Get all resources
     *
     * @return array
     */
    public static function getResources()
    {
        $data = [];
        foreach (self::$resources as $name => $path) {
            $data[$name] = self::getResource($name);
        }

        return $data;
    }

}
