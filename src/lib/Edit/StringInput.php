<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class StringInput extends Input
{
    public const MASK_EMAIL = 'ac-mask-email';
    public const MASK_PHONE = 'ac-mask-phone';
    public const MASK_HEX = 'ac-mask-hex';
    public const MASK_LINK = 'ac-mask-link';
    public const MASK_SAFE = 'ac-mask-safe';
    public const MASK_RU = 'ac-mask-ru';
    public const MASK_EN = 'ac-mask-en';

    private $value;
    private $name;
    private $mask;
    private $size;
    private $max;

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null, string $mask = null, int $size = 50, int $max = 250)
    {
        $this->size = $size;
        $this->max = $max;
        $this->mask = preg_replace('/[^a-zA-Z0-9_-]/', '', $mask);
        parent::__construct($field, $code, $title, $required);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->name = $this->getPrefix() . $this->getCode();

        $this->value = isset($request[$this->name])
            ? trim(htmlspecialchars(strval($request[$this->name])))
            : htmlspecialchars(strval($defaults[$this->getCode()]));

        $values[$this->getCode()] = $this->value;
    }

    public function getInput(): string
    {
        return "<input type='text' maxlength='{$this->max}' size='{$this->size}' name='{$this->name}' id='{$this->name}' value='{$this->value}' class='{$this->mask}'>";
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}
