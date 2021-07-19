<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class LangInput extends Input
{
    private $value;
    private $name;
    private $area;
    private $rows;
    private $cols;

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null, bool $area = false, int $rows = 4, int $cols = 60)
    {
        $this->rows = $rows;
        $this->cols = $cols;
        $this->area = $area;
        parent::__construct($field, $code, $title, $required);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->name = $this->getPrefix() . $this->getCode();

        $this->value = [];

        if (isset($request[$this->name])) {
            foreach ($request[$this->name] as $code => $value) {
                $this->value[$code] = trim(strval($value));
            }
        } else {
            foreach ($defaults[$this->getCode()] as $code => $value) {
                $this->value[$code] = trim(strval($value));
            }
        }

        $values[$this->getCode()] = $this->value;
    }

    public function getInput(): string
    {
        $res = \CLanguage::GetList($by = 'SORT', $order = 'ASC', []);

        $return = '<table class="internal ac-lang-input-box">';

        while ($ar = $res->Fetch()) {
            $input = $this->getInputHtml(strval($ar['ID']), strval($this->value[$ar['ID']]));
            $return .= "<tr><td>{$ar['NAME']}</td><td>{$input}</td></tr>";
        }

        $return .= '</table>';

        return $return;
    }

    private function getInputHtml(string $code, string $value): string
    {
        if ($this->area) {
            return "<textarea rows='{$this->rows}' cols='{$this->cols}' name='{$this->name}[{$code}]' id='{$this->name}_{$code}'>{$value}</textarea>";
        }

        return "<input type='text' maxlength='200' size='48' name='{$this->name}[{$code}]' id='{$this->name}_{$code}' value='{$value}'>";
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}