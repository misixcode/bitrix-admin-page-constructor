<?

namespace AdminConstructor\Data;

use AdminConstructor\Page\Table;
use Bitrix\Main\ORM\Fields\Field;
use AdminConstructor\Lang;

class StringFilter extends Filter
{
    protected const TYPE = '_type';

    protected $code = '';
    protected $title = '';
    protected $vars = [];
    protected $types = [
        'equals' => '=',
        'except' => '!%',
        'empty' => '=',
    ];

    public function __construct(string $code, Field &$field = null, string $title = null)
    {
        $this->code = strtolower($code);
        $this->title = $title ?? (!is_null($field) ? $field->getTitle() : $code);
        $this->vars = [
            static::PREFIX . $this->code,
            static::PREFIX . $this->code . static::TYPE
        ];
    }

    public function prepareParams(&$parameters, int $parametersType = Table::PARAM_D7): void
    {
        $value = $this->getValues()[static::PREFIX . $this->code];
        $type = $this->getValues()[static::PREFIX . $this->code . static::TYPE];

        if ($type == 'empty') {
            $value = false;
        }

        $type = $this->types[$type] ?? '%';

        if ($value === false || $this->checkValue($value)) {
            $parameters['filter'][$type . $this->code] = $value;
        }
    }

    protected function checkValue(&$value): bool
    {
        return !empty($value);
    }

    public function getInput(): string
    {
        $name = static::PREFIX . $this->code;
        $value = $this->getValues()[$name];
        $type = $this->getValues()[$name . static::TYPE];
        $this->checkValue($value);

        $select = SelectBoxFromArray(
            $name . static::TYPE,
            [
                'REFERENCE' => [
                    Lang::get('FILTER_STRING_EQUALS'),
                    Lang::get('FILTER_STRING_EXCEPT'),
                    Lang::get('FILTER_STRING_EMPTY'),
                ],
                'REFERENCE_ID' => array_keys($this->types)
            ],
            $type,
            Lang::get('FILTER_STRING_INCLUDE')
        );

        return "{$select}<input type='text' value='{$value}' name='{$name}'>";
    }

    public function getVars(): array
    {
        return $this->vars;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}