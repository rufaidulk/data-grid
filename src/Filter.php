<?php

namespace Rufaidulk\DataGrid;

use InvalidArgumentException;

final class Filter
{
    const INPUT_TEXT = 'text';
    const INPUT_SELECT = 'select';
    
    const OPERATOR_LIKE = 'like';
    const OPERATOR_EQUALS = '=';

    private $name;
    private $type;
    private $attribute;
    private $operator;
    private $selectOptions;


    public function __construct(array $properties, string $attribute)
    {
        $this->name = $attribute;
        $this->attribute = $attribute;
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

    public function handle()
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'value' => '',
            'options' => $this->selectOptions
        ];
    }

    public function addFilterWhere($query, $param)
    {
        return $query->where($this->attribute, $this->operator, $param);
    }
}