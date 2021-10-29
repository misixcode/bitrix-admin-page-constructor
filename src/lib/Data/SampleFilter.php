<?

namespace AdminConstructor\Data;

use AdminConstructor\Page\Table;
use Bitrix\Main\ORM\Fields\Field;

class SampleFilter extends Filter
{
    protected $code = '';
    protected $title = '';
    protected $vars = [];

    public function __construct(string $code, Field &$field = null, string $title = null)
    {
        $this->code = $code;
        $this->title = $title ?? (!is_null($field) ? $field->getTitle() : $code);
        $this->vars = [static::PREFIX . $this->code];
    }

    public function prepareParams(&$parameters, int $parametersType = Table::PARAM_D7): void
    {
        $value = $this->getValues()[static::PREFIX . $this->code];

        if ($this->checkValue($value)) {
            $parameters['filter'][$this->code] = $value;
        }
    }

    protected function checkValue(&$value): bool
    {
        return !empty($value);
    }

    public function getInput(): string
    {
        $name = $this::PREFIX . $this->code;
        $value = $this->getValues()[$name];

        return "<input type='text' value='{$value}' name='{$name}'>";
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