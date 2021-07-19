<?

namespace AdminConstructor\Edit;

use Bitrix\Main\HttpRequest;

class NoticeInput extends Input
{
    private $value;

    public function __construct(string $content)
    {
        $this->value = $content;
        parent::__construct(null, '', '', false);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        return;
    }

    public function getInput(): string
    {
        return BeginNote('data-type="internal"') . strval($this->value) . EndNote();
    }

    public function completeActions(bool $success): void
    {
        return;
    }
}