<?

namespace AdminConstructor\Data;

use AdminConstructor\Page\Table;
use Bitrix\Main\Entity\EnumField;
use Bitrix\Main\ORM\Fields\Field;
use AdminConstructor\Lang;

class EnumFilter extends Filter
{
    protected $code = '';
    protected $title = '';
    protected $vars = [];
    protected $values = [];
    protected $multiple = false;

    public function __construct(string $code, Field &$field = null, string $title = null, array $values = null, bool $multiple = false)
    {
        $this->code = $code;
        $this->title = $title ?? (!is_null($field) ? $field->getTitle() : $code);
        $this->multiple = $multiple;
        $this->vars = [static::PREFIX . $this->code];

        /** @var EnumField $field */
        $this->values = $values ?? ($field instanceof EnumField ? array_flip($field->getValues()) : []);
    }

    public function prepareParams(&$parameters, int $parametersType = Table::PARAM_D7): void
    {
        $d7 = $parametersType === Table::PARAM_D7;
        $value = $this->getValues()[static::PREFIX . $this->code];

        if (!$this->multiple || !$this->checkValueMultiple($value)) {
            if (!$this->checkValue($value)) {
                return;
            }
        }

        $parameters['filter'][($d7 ? '=' : '') . $this->code] = $value;
    }

    protected function checkValue(&$value): bool
    {
        return mb_strlen($value) > 0;
    }

    protected function checkValueMultiple(&$value): bool
    {
        return is_array($value) && count($value) > 0;
    }

    public function getInput(): string
    {
        $name = static::PREFIX . $this->code;
        $value = $this->getValues()[$name];

        if ($this->multiple) {
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
        } else {
            return SelectBoxFromArray(
                $name,
                [
                    'REFERENCE_ID' => array_keys($this->values),
                    'REFERENCE' => array_values($this->values)
                ],
                $value,
                Lang::get('FILTER_LIST_EMPTY')
            );
        }
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