<?

namespace AdminConstructor\Data;

use AdminConstructor\Page\Table;
use Bitrix\Main\ORM\Fields\Field;

class MultipleEnumFilter extends SpecialFilter
{
    protected $code = '';
    protected $title = '';
    protected $vars = [];
    protected $values = [];

    public function __construct(string $code, Field &$field = null, string $title = null, array $values = null)
    {
        $this->code = strtolower($code);
        $this->title = $title ?? (!is_null($field) ? $field->getTitle() : $code);
        $this->vars = [static::PREFIX . $this->code];
        $this->values = $values;
    }

    public function prepareParams(&$parameters, int $parametersType = Table::PARAM_D7): void
    {
        $value = $this->getValues()[static::PREFIX . $this->code];

        if ($this->checkValueMultiple($value)) {
            $data = [];

            foreach ($value as $id) {
                $data[] = "#{$id}#";
            }

            $parameters['filter']['%' . $this->code] = $data;
        }
    }

    protected function checkValueMultiple(&$value): bool
    {
        return is_array($value) && count($value) > 0;
    }

    public function getInput(): string
    {
        $name = static::PREFIX . $this->code;
        $value = $this->getValues()[$name];

        return SelectBoxMFromArray(
            $name . '[]',
            [
                'REFERENCE_ID' => array_keys($this->values),
                'REFERENCE' => array_values($this->values)
            ],
            $value,
            '',
            false,
            max(2, min(10, count($this->values)))
        );
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