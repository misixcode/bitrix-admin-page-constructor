<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class ReadInput extends Input
{
    private $value;
    private $modify;
    private $force;

    public function __construct(Field $field = null, string $code = null, string $title = null, callable $modify = null, bool $force = false)
    {
        $this->force = $force;
        $this->modify = $modify;
        parent::__construct($field, $code, $title, false);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->value = $defaults[$this->getCode()];
    }

    public function getInput(): string
    {
        if (is_callable($this->modify)) {
            return call_user_func_array($this->modify, [$this->value]);
        }

        return strval($this->value);
    }

    public function isPrint(): bool
    {
        return strlen($this->value) > 0 || $this->force;
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}