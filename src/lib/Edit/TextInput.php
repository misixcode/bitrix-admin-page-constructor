<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class TextInput extends Input
{
    private $value;
    private $name;
    private $rows;
    private $cols;

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null, int $rows = 8, int $cols = 60)
    {
        $this->rows = $rows;
        $this->cols = $cols;
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
        return "<textarea rows='{$this->rows}' cols='{$this->cols}' name='{$this->name}' id='{$this->name}'>{$this->value}</textarea>";
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}