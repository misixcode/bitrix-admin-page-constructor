<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;

class ColorInput extends Input
{
    private $value;
    private $name;
    private $default;

    public function __construct(Field $field = null, string $code = null, string $title = null, bool $required = null, string $default = '#000000')
    {
        $this->default = $default;
        parent::__construct($field, $code, $title, $required);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->name = $this->getPrefix() . $this->getCode();

        $this->value = isset($request[$this->name])
            ? trim(htmlspecialchars(strval($request[$this->name])))
            : strval($defaults[$this->getCode()]);

        $values[$this->getCode()] = $this->value;
    }

    public function getInput(): string
    {
        global $APPLICATION;

        $input = "<input class='ac-color-input' type='text' maxlength='7' size='7' name='{$this->name}' id='{$this->name}' value='{$this->value}' class='ac-mask-hex'>";

        ob_start();
        ?>
        <script>
            function SET_COLOR_<?=$this->name?>(color)
            {
                if (!color)
                    color = '<?=$this->default?>';

                $('#<?=$this->name?>').val(color).css('borderColor', color);
            }

            $(document).ready(function () {
                SET_COLOR_<?=$this->name?>($('#<?=$this->name?>').val());
            });

            $('#<?=$this->name?>').change(function () {
                SET_COLOR_<?=$this->name?>($(this).val());
            });

            $('#<?=$this->name?>').keyup(function () {
                SET_COLOR_<?=$this->name?>($(this).val());
            });
        </script>
        <?
        $APPLICATION->IncludeComponent(
            'bitrix:main.colorpicker',
            '',
            array(
                'SHOW_BUTTON' => 'Y',
                'ID' => "COLOR_{$this->name}",
                'NAME' => '',
                'ONSELECT' => "SET_COLOR_{$this->name}"
            ),
            false
        );
        $input .= ob_get_clean();

        return $input;
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}
