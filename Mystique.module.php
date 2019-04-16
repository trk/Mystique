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

    /* @var Mystique $instance */
    public static $instance;

    /* @var array $paths Resources paths */
    protected static $paths = [];

    /**
     * @inheritdoc
     *
     * @return array
     */
    public static function getModuleInfo() {
        return [
            'title' => 'Mystique',
            'version' => 2,
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

    /**
     * @inheritDoc
     *
     * @return Mystique
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        self::$paths = new FilenameArray();
    }

    /**
     * @inheritDoc
     */
    public function ready()
    {
        // Add default paths
        self::add(__DIR__ . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR);
        self::add($this->config->paths->templates . 'configs' . DIRECTORY_SEPARATOR);
    }

    public static function add($path)
    {
        $path = $path . self::getInstance()->className . '.*.php';
        self::$paths->add($path);
    }

    /**
     * Get all added resources paths
     *
     * @return array
     */
    public static function getResourcesPaths()
    {
        $resources = [];

        $paths = [];
        foreach (self::$paths as $path) {
            $paths[] = $path;
        }

        $paths = glob('{' . implode(',', $paths) . '}', GLOB_BRACE);
        foreach ($paths as $k => $path) {
            $name = str_replace([realpath($path), self::getInstance()->className . '.', '.php'], '', basename($path));
            $resources[$name] = $path;
        }

        return $resources;
    }

    /**
     * Get resource data from resources
     *
     * @param string $name
     * @return array|mixed
     */
    public static function getResource(string $name)
    {
        $resources = self::getResourcesPaths();
        if(array_key_exists($name, $resources)) {
            $path = $resources[$name];
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
     * Get all resources data from resources paths
     *
     * @return array
     */
    public static function getResources()
    {
        $resources = [];
        $resourcesPaths = self::getResourcesPaths();
        foreach ($resourcesPaths as $name => $path) {
            $resources[$name] = self::getResource($name);
        }

        return $resources;
    }
}
