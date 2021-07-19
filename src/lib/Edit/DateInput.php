<?

namespace AdminConstructor\Edit;

use Bitrix\Main\ORM\Fields\Field;

class DateInput extends DateTimeInput
{
    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null)
    {
        parent::__construct($field, $code, $title, $required, false);
    }
}