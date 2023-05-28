<?

namespace AdminConstructor\Page;

use AdminConstructor\Helper\Url;
use AdminConstructor\Lang;
use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Page\Asset;

abstract class Base
{
    protected const ICON_NEW = 'btn_new';
    protected const ICON_LIST = 'btn_list';
    protected const ICON_COPY = 'btn_copy';
    protected const ICON_DELETE = 'btn_delete';

    protected const ICON_LIST_EDIT = 'edit';
    protected const ICON_LIST_VIEW = 'view';
    protected const ICON_LIST_COPY = 'copy';
    protected const ICON_LIST_DELETE = 'delete';

    /** @var HttpRequest */
    private static $request;

    private $readRight = true;
    private $writeRight = true;
    private $modalMode = false;

    private $messages = [];
    private $errors = [];
    private $fatalErrors = [];
    private $topNotices = [];
    private $bottomNotices = [];

    private $menuButtons = [];

    abstract protected function prepare(): void;
    abstract public function print(): void;
    abstract protected function setReadRight(): bool;
    abstract protected function setWriteRight(): bool;

    final public function __construct()
    {
        global $APPLICATION;
        $APPLICATION->SetAdditionalCSS(Url::getStaticDir(true) . '/css/admin.min.css');

        \CJSCore::Init('jquery3');

        $assets = Asset::getInstance();
        $assets->addJs(Url::getStaticDir(true) . '/js/admin.js');
        $assets->addJs(Url::getStaticDir(true) . '/js/jquery.mask.js');


        self::$request = Context::getCurrent()->getRequest();
        $this->readRight = $this->setReadRight();
        $this->writeRight = $this->setWriteRight();

        if (!$this->readRight) {
            self::processAuthForm();
        }

        $this->prepare();
    }

    final protected function getRequest(): HttpRequest
    {
        return self::$request;
    }

    final public function isModalMode(): bool
    {
        return $this->modalMode;
    }

    final protected function setModalMode(bool $enable): void
    {
        $this->modalMode = $enable;
    }

    final protected function isReadRight(): bool
    {
        return $this->readRight;
    }

    final protected function isWriteRight(): bool
    {
        return $this->writeRight;
    }

    final protected function setTitle(string $title): void
    {
        global $APPLICATION;
        $APPLICATION->SetTitle($title);
    }

    final protected function processAuthForm(): void
    {
        global $APPLICATION;
        $APPLICATION->AuthForm(Lang::get('ACCESS_DENIED'));
        die();
    }

    final protected function returnJson(array $content)
    {
        $json = strval(json_encode($content));

        $response = Context::getCurrent()->getResponse();
        $response->getHeaders()->set('Content-Type', 'application/json');
        $response->setStatus('200 OK');
        $response->flush($json);

        die();
    }

    final protected function addError(string $message, bool $fatal = false): void
    {
        if ($fatal) {
            $this->fatalErrors[] = $message;
            return;
        }

        $this->errors[] = $message;
    }

    final protected function addErrors(array $errors): void
    {
        foreach ($errors as $message) {
            $this->addError(strval($message));
        }
    }

    final protected function getErrors(): array
    {
        return $this->errors;
    }

    final protected function isErrors(): bool
    {
        return count($this->errors) > 0;
    }

    final protected function getFatalErrors(): array
    {
        return $this->fatalErrors;
    }

    final protected function isFatalErrors(): bool
    {
        return count($this->fatalErrors) > 0;
    }

    final protected function addMessage(string $message): void
    {
        $this->messages[] = $message;
    }

    final protected function addNotice(string $message, bool $bottom = false): void
    {
        if ($bottom) {
            $this->bottomNotices[] = $message;
            return;
        }

        $this->topNotices[] = $message;
    }

    final protected function showMessages(): void
    {
        if (count($this->messages) > 0) {
            $visual = new \CAdminMessage([
                'MESSAGE' => implode('<br>', $this->messages),
                'TYPE' => 'OK'
            ]);
            echo $visual->Show();
        }
    }

    final protected function showErrors(array $arErrors = null): void
    {
        $errors = $arErrors ?? $this->errors;

        if (count($errors) > 0) {
            $visual = new \CAdminMessage([
                'MESSAGE' => implode('<br>', $errors),
                'TYPE' => 'ERROR'
            ]);
            echo $visual->Show();
        }
    }

    final protected function showTopNotice(): void
    {
        if (count($this->topNotices) > 0) {
            echo BeginNote() . implode('<br>', $this->topNotices) . EndNote();
        }
    }

    final protected function showBottomNotices(): void
    {
        if (count($this->bottomNotices) > 0) {
            echo BeginNote() . implode('<br>', $this->bottomNotices) . EndNote();
        }
    }

    final protected function addContextMenuButton(string $name, string $link = null, string $icon = null, string $click = null): void
    {
        $this->menuButtons[] = [
            'TEXT' => $name,
            'LINK' => $link,
            'ICON' => $icon,
            'ONCLICK' => $click,
        ];
    }

    final protected function showContextMenuButtons()
    {
        if (count($this->menuButtons) > 0) {
            $context = new \CAdminContextMenu($this->menuButtons);
            $context->Show();
        }
    }
}
