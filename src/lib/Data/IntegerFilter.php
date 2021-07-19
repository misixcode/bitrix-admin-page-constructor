<?

namespace AdminConstructor\Data;

use AdminConstructor\Page\Table;
use Bitrix\Main\ORM\Fields\Field;
use AdminConstructor\Lang;

class IntegerFilter extends Filter
{
    protected const TYPE = '_type';

    protected $code = '';
    protected $title = '';
    protected $vars = [];
    protected $types = [
        'big' => '>',
        'small' => '<'
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
        $type = $this->types[$type] ?? '=';

        if ($this->checkValue($value)) {
            $parameters['filter'][$type . $this->code] = $value;
        }
    }

    protected function checkValue(&$value): bool
    {
        if (!is_numeric($value)) {
            $value = null;
            return false;
        }

        $value = intval($value);
        return true;
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
                'REFERENCE' => [Lang::get('FILTER_INT_BIG'), Lang::get('FILTER_INT_SMALL')],
                'REFERENCE_ID' => array_keys($this->types)
            ],
            $type,
            Lang::get('FILTER_INT_EQUAL')
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
