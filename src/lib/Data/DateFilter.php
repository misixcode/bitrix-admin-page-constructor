<?

namespace AdminConstructor\Data;

use Bitrix\Main\ORM\Fields\Field;

class DateFilter extends DateTimeFilter
{
    public function __construct(string $code, Field &$field = null, string $title = null)
    {
        parent::__construct($code, $field, $title, false);
    }
}