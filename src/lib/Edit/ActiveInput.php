<?

namespace AdminConstructor\Edit;

use Bitrix\Main\ORM\Fields\Field;

class ActiveInput extends CheckboxInput
{
    public function __construct(Field $field = null, string $code = null, string $title = null)
    {
        parent::__construct($field, $code, $title, true);
    }
}