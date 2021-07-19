<?

namespace AdminConstructor\Edit;

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\EnumField;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;
use AdminConstructor\Lang;

class EnumInput extends Input
{
    private $value;
    private $name;
    private $multiple;
    private $empty;
    private $values = [];

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null, array $values = [], bool $empty = true, bool $multiple = false)
    {
        $this->multiple = $multiple;
        $this->empty = $empty;

        if ($field instanceof EnumField) {
            $this->values = array_flip($field->getValues());
        }

        if ($field instanceof BooleanField) {
            $this->values = Lang::getBooleanTypes();
        }

        if (count($values) > 0) {
            $this->values = $values;
        }

        parent::__construct($field, $code, $title, $required);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->name = $this->getPrefix() . $this->getCode();
        $value = $request->isPost() ? $request[$this->name] : $defaults[$this->getCode()];
        $value = $value ?? [];

        if ($this->multiple) {
            $this->value = [];
            foreach ($value as $v) {
                if (in_array($v, array_keys($this->values))) {
                    $this->value[] = $v;
                }
            }
        } else {
            $this->value = in_array($value, array_keys($this->values)) ? $value : '';
        }

        $values[$this->getCode()] = $this->value;
    }

    public function getInput(): string
    {
        if ($this->multiple) {
            return SelectBoxMFromArray(
                $this->name . '[]',
                [
                    'REFERENCE' => array_values($this->values),
                    'REFERENCE_ID' => array_keys($this->values),
                ],
                $this->value,
                '',
                false,
                max(2, min(10, count($this->values))),
                'class="ac-large-select"'
            );
        }

        return SelectBoxFromArray(
            $this->name,
            [
                'REFERENCE' => array_values($this->values),
                'REFERENCE_ID' => array_keys($this->values),
            ],
            $this->value,
            $this->empty ? Lang::get('EDIT_NO_SELECT') : '',
            'class="ac-large-select"'
        );
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}