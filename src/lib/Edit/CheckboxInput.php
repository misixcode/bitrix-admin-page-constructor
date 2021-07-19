<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class CheckboxInput extends Input
{
    private $value;
    private $name;
    private $checked;

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $checked = false)
    {
        $this->checked = $checked;
        parent::__construct($field, $code, $title, false);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->name = $this->getPrefix() . $this->getCode();

        $this->value = isset($request[$this->name])
            ? strval($request[$this->name])
            : strval($defaults[$this->getCode()]);

        $values[$this->getCode()] = $this->value === 'Y' ? 'Y' : 'N';
    }

    public function getInput(): string
    {
        if (!in_array($this->value, ['Y','N'])) {
            $this->value = $this->checked ? 'Y' : 'N';
        }

        $checked = $this->value === 'Y' ? 'checked' : null;

        return "<input type='hidden' name='{$this->name}' value='N'><input type='checkbox' name='{$this->name}' id='{$this->name}' value='Y' $checked>";
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}