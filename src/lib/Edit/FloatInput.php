<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class FloatInput extends Input
{
    private $value;
    private $name;
    private $size;
    private $max;
    private $decimals;

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null, int $decimals = 2, int $size = 15, int $max = 15)
    {
        $this->size = $size;
        $this->max = $max;
        $this->decimals = max(0, $decimals);

        parent::__construct($field, $code, $title, $required);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->name = $this->getPrefix() . $this->getCode();

        $this->value = isset($request[$this->name]) ? $request[$this->name] : $defaults[$this->getCode()];
        $this->value = round(floatval(str_replace(',', '.', $this->value)), $this->decimals);

        $values[$this->getCode()] = $this->value;
    }

    public function getInput(): string
    {
        return "<input type='text' maxlength='{$this->max}' size='{$this->size}' name='{$this->name}' id='{$this->name}' value='{$this->value}' class='ac-mask-float'>";
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}