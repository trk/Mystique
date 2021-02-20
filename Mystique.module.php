<?php

namespace ProcessWire;

/**
 * Class Mystique
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 * 
 * @var bool $useGlob
 *
 * @package Altivebir\Mystique
 */
class Mystique extends WireData implements Module, ConfigurableModule
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

        $this->wire('classLoader')->addNamespace('Altivebir\TemplateFieldManager', __DIR__ . '/src');

        $this->set('useGlob', '');
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $paths = array_merge($this->finder($this->config->paths->siteModules), $this->finder($this->config->paths->templates . "configs" . DIRECTORY_SEPARATOR));
        
        foreach ($paths as $file) {

            $dirname = dirname(dirname($file));
            $base = strtolower(str_replace([dirname(dirname(dirname($file))), "/", "\\"], "", $dirname));
            $name = str_replace([dirname($file), "/", "\\", "Mystique.", ".php"], "", $file);

            if (substr($base, 0, 1 ) === ".") {
                continue;
            }
            
            $this->resources[$base][$name] = $file;
        }
    }

    /**
	 * Finder: find config files for module
	 *
	 * @param string $path
	 * @param string $filter
	 * 
	 * @return array
	 */
	protected function finder(string $path, $filter = "configs/Mystique.")
	{
        if ($this->useGlob) {
            $paths = glob('{' . $this->config->paths->templates . $filter. '*.php,' . $this->config->paths->siteModules . '*/' . $filter . '*.php}', GLOB_BRACE | GLOB_NOSORT);
        } else {
            $paths = array();

            foreach($this->files->find($path, ["extensions" => ["php"]]) as $path) {
                if ($filter && strpos($path, $filter) === false) {
                    continue;
                }

                $paths[] = $path;
            }
        }

		return $paths;
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

    /**
     * Return an InputfieldWrapper of Inputfields used to configure the class
     *
     * @param array $data Array of config values indexed by field name
     * 
     * @return InputfieldsWrapper
     *
     */
    public function getModuleConfigInputfields(array $data) {
        
        $wrapper = new InputfieldWrapper();

        /**
         * @var InputfieldCheckbox $checkbox
         */
        $checkbox = wire('modules')->get('InputfieldCheckbox');
        $checkbox->attr('name', 'useGlob');
        $checkbox->set('label', 'Finder method');
        $checkbox->set('checkboxLabel', __('Use `glob` method for find config files'));
        $checkbox->attr('checked', $this->useGlob ? 'checked' : '');

        $wrapper->add($checkbox);
        
		return $wrapper;
	}
}
