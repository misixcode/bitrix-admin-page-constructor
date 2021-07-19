<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class DateTimeInput extends Input
{
    private $value;
    private $name;
    private $time;

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null, bool $time = true)
    {
        $this->time = $time;
        parent::__construct($field, $code, $title, $required);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->name = $this->getPrefix() . $this->getCode();
        $value = isset($request[$this->name]) ? $request[$this->name] : $defaults[$this->getCode()];

        try {
            if (empty(trim($value))) {
                throw new \Exception();
            }

            if ($this->time) {
                $this->value = new DateTime($value);
            } else {
                $this->value = new Date($value);
            }

        } catch (\Exception $e) {
            $this->value = null;
        }

        $values[$this->getCode()] = $this->value;
    }

    public function getInput(): string
    {
        return CalendarDate(
            $this->name,
            $this->value,
            'edit_form',
            $this->time ? 20 : 10
        );
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}