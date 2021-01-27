<?php

namespace Rufaidulk\DataGrid\Filters;

use InvalidArgumentException;

class FilterOption
{
    const INPUT_TEXT = 'text';
    const INPUT_SELECT = 'select';
    
    const OPERATOR_LIKE = 'like';
    const OPERATOR_EQUALS = '=';

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $attribute;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var array
     */
    private $selectOptions;

    
    public function __construct(array $properties, string $attribute)
    {
        $this->field = $attribute;
        $this->attribute = $attribute;

        $this->configProperties($properties);
    }

    /**
     * @return array
     */
    public function handle()
    {
        return [
            'field' => $this->field,
            'type' => $this->type,
            'value' => '',
            'options' => $this->selectOptions
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder $query
     * @param string $param
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function addFilterWhere($query, $param)
    {
        if ($this->operator == self::OPERATOR_LIKE) {
            return $query->where($this->attribute, $this->operator, '%' . $param . '%');
        }
        
        return $query->where($this->attribute, $this->operator, $param);
    }

    /**
     * @param array $properties
     * 
     * @throws \InvalidArgumentException
     */
    private function configProperties($properties)
    {
        if (isset($properties['attribute'])) {
            $this->attribute = $properties['attribute'];
        }

        $this->type = isset($properties['type']) ? $properties['type'] : self::INPUT_TEXT;
        $this->operator = isset($properties['operator']) ? $properties['operator'] : self::OPERATOR_LIKE;
        $this->selectOptions = isset($properties['data']) ? $properties['data'] : [];

        if ($this->type == self::INPUT_SELECT && ! is_array($this->selectOptions)) {
            throw new InvalidArgumentException('Filter option data must be an associative array');
        }
    }
}