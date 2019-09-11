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
            'version' => 7,
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
        parent::__construct();

        self::$paths = new FilenameArray();

        $this->wire('classLoader')->addNamespace('Altivebir\TemplateFieldManager', __DIR__ . '/src');
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        // Add default paths
        self::add(__DIR__ . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR);
        self::add($this->config->paths->templates . 'configs' . DIRECTORY_SEPARATOR);
    }

    /**
     * @inheritDoc
     */
    public function ready()
    {
    }

    public static function add($path)
    {
        $path = $path . self::getInstance()->className . '.*.php';
        self::$paths->add($path);
    }

    /**
     * Find all added resources
     *
     * @return array
     */
    public static function resource_paths()
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
     * Get resource data
     *
     * @param string $name
     * @param bool $json
     *
     * @return array|string
     */
    public static function resource($name = '', $json = false)
    {
        $resources = self::resource_paths();
        if(array_key_exists($name, $resources) && file_exists($resources[$name])) {
            $data = include $resources[$name];
            if(is_array($data)) {
                $resource = $data;
            } else {
                $resource = ['__data' => $data];
            }

            $resource['__type'] = 'file';
            $resource['__name'] = $name;
            $resource['__path'] = $resources[$name];
        } else {
            $resource = [
                '__type' => '',
                '__data' => 'Resource not found !',
                '__name' => $name,
                '__path' => ''
            ];
        }

        return $json ? json_encode($resource, true) : $resource;
    }

    /**
     * Get all resources data
     *
     * @param bool $json
     *
     * @return array|string
     */
    public static function resources($json = false)
    {
        $resources = [];

        foreach (self::resource_paths() as $name => $path) {
            $resources[$name] = self::resource($name);
        }

        return $json ? json_encode($resources, true) : $resources;
    }
}
