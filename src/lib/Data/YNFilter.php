<?

namespace AdminConstructor\Data;

use Bitrix\Main\ORM\Fields\Field;
use AdminConstructor\Lang;

class YNFilter extends EnumFilter
{
    public function __construct(string $code, Field &$field = null, string $title = null)
    {
        parent::__construct($code, $field, $title, Lang::getYNTypes(), false);
    }
}