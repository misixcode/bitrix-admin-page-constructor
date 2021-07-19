<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class EditorInput extends Input
{
    private $value;
    private $name;
    private $height;
    private $components;

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null, int $height = 500, bool $components = false)
    {
        $this->height = $height;
        $this->components = $components;
        parent::__construct($field, $code, $title, $required);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->name = $this->getPrefix() . $this->getCode();

        $this->value = isset($request[$this->name])
            ? strval($request[$this->name])
            : strval($defaults[$this->getCode()]);

        $values[$this->getCode()] = $this->value;
    }

    public function getInput(): string
    {
        ob_start();

        \CFileMan::AddHTMLEditorFrame(
            $this->name,
            $this->value,
            $this->name . '_TYPE',
            'html',
            ['height' => $this->height, 'width' => '100%'],
            "N",
            0,
            "",
            "",
            LANGUAGE_ID,
            !$this->components,
            false,
            ['hideTypeSelector' => true, 'saveEditorKey' => 'HTML_EDITOR_' . $this->name]
        );

        return ob_get_clean();
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}