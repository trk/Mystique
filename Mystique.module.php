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

    const TOGGLE = 'InputfieldToggle';

    const TOGGLE_CHECKBOX = 'InputfieldToggleCheckbox';

    const SELECT = 'InputfieldSelect';

    const INTEGER = 'InputfieldInteger';

    const IMAGE = 'InputfieldImage';

    const ICON_PICKER = 'InputfieldFontIconPicker';

    const ICON = 'InputfieldIcon';

    const COLOR = 'InputfieldColor';

    /* @var Mystique $instance */
    public static $instance;

    /**
     * @var array $resources
     */
    public $resources = [];

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

        $this->wire('classLoader')->addNamespace('Altivebir\TemplateFieldManager', __DIR__ . '/src');
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        
    }

    /**
     * @inheritDoc
     */
    public function ready()
    {
        $path = $this->wire("config")->paths->siteModules . "**/configs/Mystique.*.php";
        $path .= "," . $this->wire("config")->paths->templates . "configs/Mystique.*.php";
        
        foreach (glob("{" . $path . "}", GLOB_BRACE) as $file) {

            $dirname = dirname(dirname($file));
            $base = strtolower(str_replace([dirname(dirname(dirname($file))), "/"], "", $dirname));
            $name = str_replace([dirname($file), "/", "Mystique.", ".php"], "", $file);

            $this->resources[$base][$name] = $file;
        }
    }

    /**
     * Make translations for custom "Application Module"
     *
     * @param array $fields
     * @return array
     */
    protected function translate(array $fields = [])
    {
        foreach ($fields as $name => $field) {
            
            $title = isset($field["title"]) ? $field["title"] : "";
            if ($title) {
                $field["title"] = $this->wire("app")["_t"]($title);
            }
            
            $label = isset($field["label"]) ? $field["label"] : "";
            if ($label) {
                $field["label"] = $this->wire("app")["_t"]($label);
            }
            
            $checkboxLabel = isset($field["checkboxLabel"]) ? $field["checkboxLabel"] : "";
            if ($checkboxLabel) {
                $field["checkboxLabel"] = $this->wire("app")["_t"]($checkboxLabel);
            }
            
            $description = isset($field["description"]) ? $field["description"] : "";
            if ($description) {
                $field["description"] = $this->wire("app")["_t"]($description);
            }
            
            $notes = isset($field["notes"]) ? $field["notes"] : "";
            if ($notes) {
                $field["notes"] = $this->wire("app")["_t"]($notes);
            }

            $fields[$name] = $field;
        }

        return $fields;
    }

    /**
     * Get resource data
     *
     * @param string $name
     * @param string $name
     * @param boolean $json
     *
     * @return array|string
     */
    public function resource($name = "", $base = "", $json = false)
    {
        if (strpos($name, ".") !== false) {
            $explode = explode(".", $name);

            if (isset($this->resources[$explode[0]])) {
                $base = $explode[0];
            }

            $name = $explode[1];
        }

        $data = [
            "__id" => "",
            "__base" => "",
            "__name" => "",
            "__title" => "",
            "__type" => "",
            "__path" => "",
            "__data" => ""
        ];

        if ($base && $name) {

            if (isset($this->resources[$base]) && isset($this->resources[$base][$name]) && file_exists($this->resources[$base][$name])) {
                
                $resource = include $this->resources[$base][$name];
                
                // be sure we have fields inside resource array
                if (is_array($resource) && isset($resource["fields"]) && is_array($resource["fields"])) {
                    
                    $title = isset($resource["title"]) ? $resource["title"] : $name;

                    if ($this->wire("app")) {
                        // $resource["fields"] = $this->translate($resource["fields"]);
                    }
    
                    $data["__id"] = $base . "." . $name;
                    $data["__base"] = $base;
                    $data["__name"] = $name;
                    $data["__title"] = $title;
                    $data["__type"] = "file";
                    $data["__path"] = $this->resources[$base][$name];
                    $data["__data"] = $resource;

                }
            }
        }

        return $json ? json_encode($data, true) : $data;
    }

    /**
     * Get all resources data
     *
     * @param bool $json
     *
     * @return array|string
     */
    public function resources($json = false)
    {
        $resources = [];

        foreach ($this->resources as $base => $sources) {
            foreach ($sources as $name => $data) {
                $resources[$base . "." . $name] = $this->resource($name, $base, $json);
            }
        }

        return $json ? json_encode($resources, true) : $resources;
    }
}
