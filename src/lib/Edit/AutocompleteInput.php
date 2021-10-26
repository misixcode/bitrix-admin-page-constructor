<?php

namespace AdminConstructor\Edit;

use AdminConstructor\Tool\Html;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class AutocompleteInput extends Input
{
    private $value;
    private $name;
    private $size;
    private $url;
    private $values;

    public function __construct(
        Field $field = null,
        string $code = null,
        string $title = null,
        bool $required = null,
        string $url = null,
        array $values = [],
        int $size = 50
    ) {
        $this->size = $size;
        $this->url = $url;
        $this->values = $values;
        parent::__construct($field, $code, $title, $required);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->name = $this->getPrefix() . $this->getCode();

        $this->value = isset($request[$this->name])
            ? trim(htmlspecialchars(strval($request[$this->name])))
            : strval($defaults[$this->getCode()]);

        $values[$this->getCode()] = $this->value;
    }

    public function getInput(): string
    {
        return Html::autocompleteInput(
            $this->name,
            $this->name,
            $this->value,
            $this->url,
            $this->values,
            $this->size
        );
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}
