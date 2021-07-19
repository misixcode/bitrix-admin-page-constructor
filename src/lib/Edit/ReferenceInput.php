<?

namespace AdminConstructor\Edit;

use AdminConstructor\Tool\Html;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class ReferenceInput extends Input
{
    private $value;
    private $name;
    private $url;

    public function __construct(string $url, Field $field = null, string $code = null, string $title = null, bool $required = null)
    {
        $this->url = $url;
        parent::__construct($field, $code, $title, $required);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->name = $this->getPrefix() . $this->getCode();

        $this->value = isset($request[$this->name])
            ? strval($request[$this->name])
            : strval($defaults[$this->getCode()]);

        $values[$this->getCode()] = $this->value;
    }

    public function getInput(): string
    {
        return Html::referenceInput($this->name, $this->name, $this->url, $this->value);
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}