<?

namespace AdminConstructor\Edit;

use Bitrix\Main\ORM\Fields\Field;

class MultipleEnumInput extends EnumInput
{
    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null, array $values = [])
    {
        parent::__construct($field, $code, $title, $required, $values, false, true);
    }
}