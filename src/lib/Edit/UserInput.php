<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class UserInput extends Input
{
    private $value;
    private $name;

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null)
    {
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
        return FindUserID($this->name, $this->value, '', 'edit_form');
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}