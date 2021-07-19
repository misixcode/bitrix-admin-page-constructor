<?

namespace AdminConstructor\Edit;

use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\UI\FileInput as BXFileInput;
use AdminConstructor\Tool\Uploader;
use AdminConstructor\Structure\UploaderParams;

class FileInput extends Input
{
    private $value;
    private $name;
    private $parameters = [
        'name' => '',
        'description' => false,
        'upload' => true,
        'allowUpload' => 'F',
        'medialib' => true,
        'fileDialog' => true,
        'cloud' => false,
        'delete' => true,
        'maxCount' => 1,
    ];

    /** @var UploaderParams */
    private $params;

    /** @var Uploader */
    private $uploader;

    public function __construct(UploaderParams $params, Field $field = null, string $code = null, string $title = null)
    {
        $this->params = $params;
        parent::__construct($field, $code, $title, false);
    }

    public function setValues(array $defaults, array &$values, HttpRequest &$request): void
    {
        $this->params
            ->setPrefix($this->getPrefix())
            ->setName($this->getCode())
            ->setCurrent(array_column($defaults[$this->getCode()] ?? [], 'id'));

        $this->name = 'BX_UPLOADER_' . $this->getPrefix() . $this->getCode();
        $this->parameters['name'] = $this->name . '_#IND#_NEW';
        $this->parameters['allowUpload'] = $this->params->isImage() ? 'I' : 'F';
        $this->parameters['maxCount'] = max(1, $this->params->getCount());

        if (strlen($this->params->getExtensions()) > 0) {
            $this->parameters['allowUploadExt'] = $this->params->getExtensions();
        }

        $this->value = $this->params->getCurrent();

        $tmp = [];
        $i = 0;
        foreach ($this->value as $file) {
            $tmp[$this->name . '_' . $i] = $file;
            $i++;
        }

        $this->value = (count($tmp) > 0 ? $tmp : 0);
        $this->uploader = new Uploader($this->params);

        foreach ($this->uploader->getArray() as $item) {
            $values[$this->getCode()][] = [
                'id' => intval($item),
                'description' => '',
            ];
        }
    }

    public function completeActions(bool $success): void
    {
        if ($success) {
            $this->uploader->clear();
        } else {
            $this->uploader->reset();
        }
    }

    public function getInput(): string
    {
        if (Context::getCurrent()->getRequest()->isPost() && is_array($_SESSION[$this->name . '_SAVED'])) {
            $this->value = $_SESSION[$this->name . '_SAVED'];
            unset($_SESSION[$this->name . '_SAVED']);
        }

        return BXFileInput::createInstance($this->parameters)->show($this->value);
    }
}