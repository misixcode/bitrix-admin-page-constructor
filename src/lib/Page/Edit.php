<?

namespace AdminConstructor\Page;

use AdminConstructor\Edit\Input;
use AdminConstructor\Helper\Url;

abstract class Edit extends Base
{
    private $buttonParams = [
        'disabled' => true,
        'back_url' => '',
        'btnCancel' => true,
        'btnApply' => false,
        'btnSave' => false,
        'btnSaveAndAdd' => false
    ];

    private $requestId = '';
    private $requestCopy = '';

    private $id = '';
    private $copyId = '';

    private $values = [];
    private $defaults = [];
    private $edit = false;

    private $prefix = 'INPUT_';
    private $columnWidth = '40';

    private $tabs = [];
    private $tabHandlers = [];

    /** @var Input[] */
    private $inputs = [];

    /** @var \CAdminTabControl */
    private $tabControl;

    abstract protected function setRequestId(): string;
    abstract protected function setRequestCopy(): string;
    abstract protected function enableButtons(): bool;
    abstract protected function customButtons(): void;
    abstract protected function checkId(): bool;
    abstract protected function checkCopyId(): bool;
    abstract protected function getDefaultValues(string $id): array;
    abstract protected function getEditValues(string $id): array;
    abstract protected function getCopyValues(string $id): array;
    abstract protected function prepareEditParams(): void;
    abstract protected function executeContextActions(): void;
    abstract protected function executeActions(array $values): void;

    final protected function prepare(): void
    {
        $request = $this->getRequest();
        $this->requestId = $this->setRequestId();
        $this->requestCopy = $this->setRequestCopy();
        $this->id = strval($request[$this->requestId]);
        $this->copyId = strval($request[$this->requestCopy]);

        $this->buttonParams['disabled'] = !$this->isWriteRight();

        if (check_bitrix_sessid() && isset($request['action'])) {
            $this->executeContextActions();
        }

        if ($this->checkCopyId()) {
            $this->defaults = $this->getCopyValues($this->copyId);
        } elseif ($this->checkId()) {
            $this->edit = true;
            $this->defaults = $this->getEditValues($this->id);
        } else {
            $this->defaults = $this->getDefaultValues($this->id);
        }

        $this->prepareEditParams();

        foreach ($this->inputs as $unique => $input) {
            $input->setPrefix($this->prefix);
            $input->execute($this->getDefaults(), $this->values, $request);
        }

        if ($request->isPost() && check_bitrix_sessid() && !$this->isErrors()) {
            $this->executeActions($this->getValues());
        }
    }

    /**
     * @throws \Exception
     */
    final public function print(): void
    {
        if ($this->isFatalErrors()) {
            $this->showErrors($this->getFatalErrors());
            return;
        }

        $this->showContextMenuButtons();
        $this->showMessages();
        $this->showErrors();
        $this->showTopNotice();

        if (count($this->tabs) <= 0) {
            throw new \Exception('Tabs not found!');
        }

        global $APPLICATION;
        $url = Url::make($APPLICATION->GetCurPage());

        $this->tabControl = new \CAdminTabControl('tabControl', array_values($this->tabs));
        echo "<form method='post' name='edit_form' action='{$url}' enctype='multipart/form-data'>";
        echo bitrix_sessid_post();
        $this->printHiddenParam($this->requestId, $this->id);
        $this->tabControl->Begin();

        foreach ($this->tabs as $key => $tab) {
            $this->tabControl->BeginNextTab();
            call_user_func_array($this->tabHandlers[$key], [$tab]);
        }

        $this->tabControl->EndTab();

        if ($this->enableButtons()) {
            $this->tabControl->Buttons($this->buttonParams);
            $this->customButtons();
        }

        $this->tabControl->End();

        echo "</form>";

        $this->showBottomNotices();
    }

    final protected function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    final protected function getId(): string
    {
        return $this->id;
    }

    final protected function getCopyId(): string
    {
        return $this->copyId;
    }

    final protected function getDefaults(): array
    {
        return $this->defaults;
    }

    final protected function getValues(): array
    {
        return $this->values;
    }

    final protected function isEdit(): bool
    {
        return $this->edit;
    }

    final protected function setButtons(string $back, bool $cancel = true, bool $apply = false, bool $save = false, bool $saveAdd = false): void
    {
        $this->buttonParams['back_url'] = $back;
        $this->buttonParams['btnCancel'] = $cancel;
        $this->buttonParams['btnApply'] = $apply;
        $this->buttonParams['btnSave'] = $save;
        $this->buttonParams['btnSaveAndAdd'] = $saveAdd;
    }

    final protected function setColumnWidth(string $width): void
    {
        $this->columnWidth = $width;
    }

    final protected function addTab(string $code, string $name, callable $handler, string $title = null): void
    {
        $this->tabs[$code] = [
            'DIV' => $code,
            'TAB' => $name,
            'TITLE' => $title ?? $name,
        ];

        $this->tabHandlers[$code] = $handler;
    }

    final protected function addInput(string $unique, Input $input, string $section = null): void
    {
        if (strlen($section) > 0) {
            $input->setSection($section);
        }

        $this->inputs[$unique] = $input;
    }

    final protected function completeActions(bool $success): void
    {
        foreach ($this->inputs as $input) {
            $input->completeActions($success);
        }
    }

    /**
     * @param string $unique
     * @param bool $fluid
     * @param bool $head
     * @throws \Exception
     */
    final protected function printRow(string $unique, bool $fluid = false, bool $head = true): void
    {
        $input = $this->inputs[$unique];

        if (!($input instanceof Input)) {
            throw new \Exception("Input with key {$unique} not found!");
        }

        if (!$input->isPrint()) {
            return;
        }

        $title = $input->isRequired() ? "<b>{$input->getTitle()}</b>" : $input->getTitle();

        if (!empty($title)) {
            $title .= ':';
        }

        if ($fluid) {
            if ($head) {
                $this->printHead($title);
            }
            echo "<tr><td colspan='2'>{$input->getInput()}</td></tr>";
        } else {
            echo "<tr><td style='width:{$this->columnWidth}%;'>$title</td><td>{$input->getInput()}</td></tr>";
        }
    }

    final protected function printHiddenParam(string $name, string $value): void
    {
        echo "<input type='hidden' name='$name' value='$value'>";
    }

    final protected function printHead(string $title): void
    {
        echo "<tr class='heading'><td colspan='2'>$title</td></tr>";
    }

    final protected function beginCustomRow(bool $fluid = true, string $title = null): void
    {
        if ($fluid) {
            echo "<tr><td colspan='2'>";
        } else {
            echo "<tr><td style='width:{$this->columnWidth}%;'>$title</td><td>";
        }
    }

    final protected function endCustomRow(): void
    {
        echo "</td></tr>";
    }

    final protected function jsEditRedirect(string $path, string $action, array $params = [], string $confirm = ''): string
    {
        $params['sessid'] = bitrix_sessid();
        $params['action'] = $action;
        $url = json_encode(Url::make($path, $params));

        if (mb_strlen($confirm) > 0) {
            $confirm = json_encode($confirm);
            return "if (confirm({$confirm})) {document.location.href={$url}}";
        }

        return "document.location.href={$url}";
    }
}
