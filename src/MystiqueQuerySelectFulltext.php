<?php

namespace Altivebir\Mystique;

/**
 * Class MystiqueQuerySelectFulltext
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\Mystique
 */
class MystiqueQuerySelectFulltext extends \ProcessWire\DatabaseQuerySelectFulltext
{
	protected string $jsonFieldName = '';
	
	/**
	 * Get 'tableName.fieldName' string
	 * 
	 * @return string
	 * 
	 */
	protected function tableField() {
        return "JSON_UNQUOTE(JSON_EXTRACT(" . $this->tableName . ".`data`, '$." . $this->fieldName . "'))";
	}

    /**
	 * Match equals, not equals, less, greater, etc.
	 *
	 * @param string $value
	 *
	 */
	protected function matchEquals($value)
    {
        $op = $this->wire()->database->escapeOperator($this->operator, \ProcessWire\WireDatabasePDO::operatorTypeComparison);
        $this->query->where("{$this->jsonFieldName}{$op}?", $value);
	}

    /**
	 * Match is an empty empty string, null or not present
	 * 
	 */
	protected function matchIsEmpty()
    {
        $this->query->where("({$this->jsonFieldName}='' OR {$this->jsonFieldName} IS NULL)");
    }

    /**
	 * Match is present, not null and not an empty string
	 * 
	 */
	protected function matchIsNotEmpty()
    {
        $this->query->where("({$this->jsonFieldName} IS NOT NULL AND {$this->jsonFieldName}!='')");
    }

    /**
	 * Match LIKE phrase
	 * 
	 * @param string $value
	 * 
	 */
	protected function matchLikePhrase($value)
    {
        $likeType = $this->not ? 'NOT LIKE' : 'LIKE';
		$this->query->where("{$this->jsonFieldName} $likeType ?", '%' . $this->escapeLike($value) . '%');
    }

    /**
	 * Match starts-with or ends-with using only LIKE (no match/against index)
	 * 
	 * Does not ignore whitespace, closing tags or punctutation at start/end the way that the
	 * matchStartEnd() method does, so this can be used to perform more literal start/end matches.
	 * 
	 * @param string $value
	 * 
	 */
	protected function matchLikeStartEnd($value)
    {
        $likeType = $this->not ? 'NOT LIKE' : 'LIKE';
		if(strpos($this->operator, '^') !== false) {
			$this->query->where("{$this->jsonFieldName} $likeType ?", $this->escapeLike($value) . '%');
		} else {
			$this->query->where("{$this->jsonFieldName} $likeType ?", '%' . $this->escapeLike($value));
		}
	}

    /**
	 * Match words (plural) LIKE, given words can appear in full or in any part of a word
	 * 
	 * @param string $value
	 * @since 3.0.160
	 * 
	 */
	protected function matchLikeWords($value) {
		
		// ~%=  Match all words LIKE
		// ~|%= Match any words LIKE
		
		$likeType = $this->not ? 'NOT LIKE' : 'LIKE';
		$any = strpos($this->operator, '|') !== false;
		$words = $this->words($value); 
		$binds = array(); // used only in $any mode
		$wheres = array(); // used only in $any mode
		
		foreach($words as $word) {
			$word = $this->escapeLike($word);
			if(!strlen($word)) continue;
			if($any) {
				$bindKey = $this->query->getUniqueBindKey();
				$wheres[] = "({$this->jsonFieldName} $likeType $bindKey)";
				$binds[$bindKey] = "%$word%";
			} else {
				$this->query->where("({$this->jsonFieldName} $likeType ?)", "%$word%");
			}
		}
		
		if($any && count($words)) {
			$this->query->where('(' . implode(' OR ', $wheres) . ')'); 
			$this->query->bindValues($binds); 
		}
	}

	/**
	 * Update the query (provided to the constructor) to match the given arguments
	 * 
	 * @param string $tableName
	 * @param string $fieldName
	 * @param string $operator
	 * @param string|int|array $value Value to match. Array value support added 3.0.141 (not used by PageFinder)
	 * @return $this
	 * @throws WireException If given $operator argument is not implemented here
	 * 
	 */
	public function match($tableName, $fieldName, $operator, $value)
	{
		$this->jsonFieldName = "JSON_UNQUOTE(JSON_EXTRACT(" . $this->database->escapeTable($tableName) . ".`data`, '$." . $fieldName . "'))";

		return parent::match($tableName, $fieldName, $operator, $value);
	}
}