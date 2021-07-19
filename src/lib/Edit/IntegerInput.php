<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class IntegerInput extends Input
{
    private $value;
    private $name;
    private $size;
    private $max;

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null, int $size = 15, int $max = 11)
    {
        $this->size = $size;
        $this->max = $max;
        parent::__construct($field, $code, $title, $required);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->name = $this->getPrefix() . $this->getCode();

        $this->value = isset($request[$this->name])
            ? intval($request[$this->name])
            : intval($defaults[$this->getCode()]);

        $values[$this->getCode()] = $this->value;
    }

    public function getInput(): string
    {
        return "<input type='text' maxlength='{$this->max}' size='{$this->size}' name='{$this->name}' id='{$this->name}' value='{$this->value}' class='ac-mask-int'>";
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}