<?php

namespace ProcessWire;

use Altivebir\Mystique\FormManager;
use Altivebir\Mystique\MystiqueValue;

/**
 * Class FieldtypeMystique
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\Mystique
 */
class FieldtypeMystique extends Fieldtype
{
    /**
     * @inheritdoc
     *
     * @return array
     */
    public static function getModuleInfo()
    {
        return [
            'title' => 'Mystique',
            'version' => '0.0.16',
            'summary' => __('Mystique fields data for ProcessWire CMS/CMF by ALTI VE BIR.'),
            'href' => 'https://www.altivebir.com',
            'author' => 'İskender TOTOĞLU | @ukyo(community), @trk (Github), https://www.altivebir.com',
            'requires' => [
                'PHP>=7.0.0',
                'ProcessWire>=3.0.0',
                'Mystique'
            ],
            'icon' => 'cogs'
        ];
    }

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->wire('classLoader')->addNamespace('Altivebir\Mystique', __DIR__ . '/src');
    }

    /**
     * @inheritdoc
     */
    public function getInputfield(Page $page, Field $field)
    {
        /* @var InputfieldMystique $inputField */
        $inputField = $this->wire('modules')->get('InputfieldMystique');
        $inputField->setField($field);
        $inputField->setEditedPage($page);

        return $inputField;
    }

    /**
     * Get a blank value used by this FieldType
     *
     * @param Page $page
     * @param Field $field
     * @return MystiqueValue|int|object|string|null
     * @throws WireException
     */
    public function getBlankValue(Page $page, Field $field)
    {
        $field = $page->id ? $page->template->fieldgroup->getField($field, true) : $field;

        /**
         * @var Mystique
         */
        $mystique = $this->modules->get('Mystique');

        if($field->useJson && $field->jsonString) {
            $resource = json_decode($field->jsonString, true);
        } else {
            $resource = $mystique->loadResource($field->resource, $page, $field);
        }

        if (!isset($resource['fields']) || !is_array($resource['fields'])) {
            return new MystiqueValue();
        }

        $form = new FormManager([
            'fields' => $resource['fields']
        ]);

        return new MystiqueValue($form->getValues());
    }

    /**
     * @inheritdoc
     */
    public function sanitizeValue(Page $page, Field $field, $value)
    {
        // add support for $page->setAndSave('yourfield', ['foo'=>'bar']);
        // to overwrite properties of the mystique field value
        if(is_array($value)) {
            $old = $page->get($field->name);
            foreach($value as $k=>$v) $old->$k = $v;
            $value = $old;
        }
        if ($value instanceof MystiqueValue) {
            return $value;
        }

        return $this->getBlankValue($page, $field);
    }

    /**
     * @inheritdoc
     */
    public function ___wakeupValue(Page $page, Field $field, $value)
    {
        $MystiqueValue = $this->getBlankValue($page, $field);

        $data = $value ? json_decode($value, true) : [];


        foreach ($MystiqueValue as $name => $val) {
            $MystiqueValue->{$name} = array_key_exists($name, $data) ? $data[$name] : '';
        }

        return $MystiqueValue;
    }

    /**
     * @inheritdoc
     */
    public function ___sleepValue(Page $page, Field $field, $value)
    {
        if(!$value instanceof MystiqueValue) {
            throw new WireException("Expecting an instance of MystiqueValue");
        }

        return [
            'data' => json_encode($value->getArray())
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function ___getConfigAllowContext($field)
    {
        $fields = parent::___getConfigAllowContext($field);
        $fields = array_merge($fields, ["useJson", "jsonString", "resource"]);
        
        return $fields;
	}

    /**
     * @inheritdoc
     */
    public function getDatabaseSchema(Field $field)
    {
        $schema = parent::getDatabaseSchema($field);

        $schema['data'] = 'TEXT NOT NULL';

        $schema['keys']['data'] = 'FULLTEXT KEY `data` (`data`)';

        if ($field->id) {
            $this->updateDatabaseSchema($field, $schema);
        }
        
        return $schema;
    }

    /**
	 * Update the DB schema, if necessary
	 * 
	 * @param Field $field
	 * @param array $schema
	 *
	 */
    protected function updateDatabaseSchema(Field $field, array $schema)
    {

		$requiredVersion = 1; 
		$schemaVersion = (int) $field->get('schemaVersion'); 

		if($schemaVersion >= $requiredVersion) {
			// already up-to-date
			return;
		}

		if($schemaVersion == 0) {
            $schemaVersion = 1; 
			$database = $this->wire('database');
			$table = $database->escapeTable($field->getTable());
			$query = $database->prepare("SHOW TABLES LIKE '$table'"); 
			$query->execute();
			$row = $query->fetch(\PDO::FETCH_NUM); 
			$query->closeCursor();
			if (!empty($row)) {
                $sql = "ALTER TABLE {$field->getTable()} MODIFY data LONGTEXT NOT NULL";
                $query = $this->wire('database')->prepare($sql);
                $query->execute();
                $this->message("Updated {$field->getTable()} database for LONGTEXT storage", Notice::log);
            }
		}

		$field->set('schemaVersion', $schemaVersion); 
		$field->save();
    }
    
    /**
     * @inheritDoc
     *
     * @param DatabaseQuerySelect $query
     * @param string $table
     * @param string $subfield
     * @param string $operator
     * @param mixed $value
     * 
     * @return DatabaseQuery|DatabaseQuerySelect
     */
    public function getMatchQuery($query, $table, $subfield, $operator, $value)
    {
        $database = $this->wire("database");

		if(empty($subfield) || $subfield == "data") {
			$path = '$.*';
		} else {
			$path = '$.' . $subfield;
		}

		$table = $database->escapeTable($table);
        $value = $database->escapeStr($value);
        
		if($operator == "=") {
			$query->where("JSON_SEARCH({$table}.data, 'one', '$value', NULL, '$path') IS NOT NULL");			
		} else if($operator == "*=" || $operator == "%=") {
			$query->where("JSON_SEARCH({$table}.data, 'one', '%$value%', NULL, '$path') IS NOT NULL");			
		} else if($operator == "^=") {
			$query->where("JSON_SEARCH({$table}.data, 'one', '$value%', NULL, '$path') IS NOT NULL");			
		} else if($operator == "$=") {
			$query->where("JSON_SEARCH({$table}.data, 'one', '%$value', NULL, '$path') IS NOT NULL");			
        }

        return $query;
    }
}

