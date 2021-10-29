<?

namespace AdminConstructor\Data;

use AdminConstructor\Page\Table;
use AdminConstructor\Tool\Html;
use Bitrix\Main\ORM\Fields\Field;

class ReferenceFilter extends SpecialFilter
{
    public const REF_PAGE = 'reference-page';
    public const REF_NAME = 'reference-name';
    public const REF_SEARCH = 'reference-search';
    public const REF_INPUT = 'reference-input';

    protected $code = '';
    protected $title = '';
    protected $url = '';
    protected $vars = [];

    public function __construct(string $url, string $code, Field &$field = null, string $title = null)
    {
        $this->url = $url;
        $this->code = $code;
        $this->title = $title ?? (!is_null($field) ? $field->getTitle() : $code);
        $this->vars = [static::PREFIX . $this->code];
    }

    public function prepareParams(&$parameters, int $parametersType = Table::PARAM_D7): void
    {
        $value = $this->getValues()[static::PREFIX . $this->code];

        if ($this->checkValue($value)) {
            $parameters['filter'][$this->code] = $value;
        }
    }

    protected function checkValue(&$value): bool
    {
        return !empty($value);
    }

    public function getInput(): string
    {
        $name = $this::PREFIX . $this->code;
        $value = $this->getValues()[$name];

        return Html::referenceInput($name, $name, $this->url, $value);
    }

    public function getVars(): array
    {
        return $this->vars;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
