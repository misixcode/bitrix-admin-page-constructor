<?

namespace AdminConstructor\Edit;

use Bitrix\Main\Entity\ScalarField;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

abstract class Input
{
    private $title;
    private $code;
    private $required;
    private $prefix;
    private $section;

    /** @var Field */
    private $field;

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null)
    {
        if ($field instanceof Field) {
            $this->field = $field;
            $this->title = $field->getTitle();
            $this->code = $field->getName();

            if ($field instanceof ScalarField) {
                $this->required = $field->isRequired();
            }
        }

        $this->title = $title ?? $this->title;
        $this->code = $code ?? $this->code;
        $this->required = $required ?? $this->required;
    }

    final public function getCode(): string
    {
        return $this->code;
    }

    public function getField(): Field
    {
        return $this->field;
    }

    public function isRequired(): bool
    {
        return (bool) $this->required;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isPrint(): bool
    {
        return true;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setSection(string $section): void
    {
        $this->section = $section;
    }

    final public function execute(array $defaults, array &$values, HttpRequest &$request): void
    {
        $input = [];

        if (strlen($this->section) > 0) {
            $defaults = $defaults[$this->section] ?? [];
        }

        $this->setValues($defaults, $input, $request);

        if (strlen($this->section) > 0) {
            $input = [$this->section => $input];
        }

        $values = array_merge_recursive($values, $input);
    }

    abstract public function setValues(array $defaults, array &$values, HttpRequest &$request): void;
    abstract public function getInput(): string;
    abstract public function completeActions(bool $success): void;
}