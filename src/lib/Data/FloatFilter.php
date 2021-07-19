<?

namespace AdminConstructor\Data;

use Bitrix\Main\ORM\Fields\Field;

class FloatFilter extends IntegerFilter
{
    private $decimals;

    public function __construct(string $code, Field &$field = null, string $title = null, int $decimals = 2)
    {
        $this->decimals = $decimals;
        parent::__construct($code, $field, $title);
    }

    protected function checkValue(&$value): bool
    {
        $value = str_replace(',', '.', $value);

        if (!is_numeric($value)) {
            $value = null;
            return false;
        }

        $value = round(floatval($value), $this->decimals);
        return true;
    }
}