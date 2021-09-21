<?

namespace AdminConstructor\Page;

use AdminConstructor\Data\Filter;
use AdminConstructor\Data\ReferenceFilter;
use AdminConstructor\Helper\Url;
use AdminConstructor\Structure\ReferenceItem;
use AdminConstructor\Structure\ReferenceSearch;
use AdminConstructor\System\AdminList;
use AdminConstructor\System\AdminListRow;
use Bitrix\Main\DB\Result;
use Bitrix\Main\UI\PageNavigation;
use AdminConstructor\Lang;

abstract class Table extends Base
{
    public const PARAM_OLD = 0;
    public const PARAM_D7 = 1;

    private $tableId = '';
    private $columnRowId = '';
    private $reference = false;
    private $referenceInput = '';

    private $by = false;
    private $order = false;

    /** @var \CAdminSorting */
    private $sort;

    /**  @var AdminList */
    private $list;

    /** @var Filter[] */
    private $filters = [];

    private $staticFilters = [];

    private $dataType = self::PARAM_D7;
    private $parameters = [];

    private $pageNavigation = true;
    private $totalRow = true;

    private $orders = [];
    private $headers = [];

    private $tableButtons = [];
    private $groupActions = [];

    abstract protected function setTableId(): string;
    abstract protected function setColumnRowId(): string;
    abstract protected function getReferenceItem(string $id = null): ReferenceItem;
    abstract protected function getReferenceSearch(string $search = null): ReferenceSearch;
    abstract protected function prepareParams(): void;
    abstract protected function modifyRow(AdminListRow &$row, array $rowData, AdminList &$list): void;
    abstract protected function setRowActions(array &$actions, array $rowData, AdminList &$list): void;
    abstract protected function executeActions(AdminList &$list, array $parameters): void;
    abstract protected function executeTotalRow(AdminListRow &$row, array $headers, array $parameters, AdminList &$list): void;

    /**
     * @throws \Exception
     */
    final protected function prepare(): void
    {
        $this->checkReference();
        $this->prepareParams();

        $this->tableId = $this->setTableId();
        $this->columnRowId = $this->setColumnRowId();
        $this->sort = new \CAdminSorting($this->tableId, $this->by, $this->order);
        $this->list = new AdminList($this->tableId, $this->sort);
        $this->prepareFilter();
        $this->prepareList();
    }

    final public function print(): void
    {
        if ($this->isFatalErrors()) {
            $this->showErrors($this->getFatalErrors());
            return;
        }

        $this->showContextMenuButtons();
        $this->printFilter();
        $this->showMessages();
        $this->showErrors();
        $this->showTopNotice();
        $this->list->DisplayList();
        $this->showBottomNotices();
    }

    private function checkReference()
    {
        $request = $this->getRequest();

        if ($request->get(ReferenceFilter::REF_PAGE) !== 'Y') {
            return;
        }

        if (isset($request[ReferenceFilter::REF_NAME])) {
            $this->returnJson($this->getReferenceItem(strval($request->get(ReferenceFilter::REF_NAME)))->asArray());
        }

        if (isset($request[ReferenceFilter::REF_SEARCH])) {
            $this->returnJson($this->getReferenceSearch(strval($request->get(ReferenceFilter::REF_SEARCH)))->asArray());
        }

        if (isset($request[ReferenceFilter::REF_INPUT])) {
            $this->referenceInput = strval($request[ReferenceFilter::REF_INPUT]);
            $this->setModalMode(true);
            $this->reference = true;
        }
    }

    private function prepareFilter(): void
    {
        if (count($this->filters) == 0) {
            return;
        }

        $variables = [];
        foreach ($this->filters as $filter) {
            $variables = array_merge($variables, $filter->getVars());
        }

        $this->list->InitFilter($variables);

        foreach ($this->filters as $filter) {
            $filter->prepare($this->parameters, $this->dataType);
        }
    }

    private function printFilter(): void
    {
        if (count($this->filters) == 0) {
            return;
        }

        global $APPLICATION;
        $url = $APPLICATION->GetCurPage();

        $titles = [];
        foreach ($this->filters as $filter) {
            $titles[] = $filter->getTitle();
        }

        $form = new \CAdminFilter($this->tableId . '_filter', $titles);
        echo "<form name='find_form' method='get' action='$url'>";

        foreach ($this->staticFilters as $key => $value) {
            echo "<input type='hidden' name='$key' value='$value'>";
        }

        $form->Begin();

        foreach ($this->filters as $filter) {
            echo "<tr><td>{$filter->getTitle()}</td><td>{$filter->getInput()}</td></tr>";
        }

        $form->Buttons(['table_id' => $this->tableId, 'url' => $url, 'form' => 'find_form']);
        $form->End();

        echo '</form>';
    }

    /**
     * @throws \Exception
     */
    private function prepareList(): void
    {
        $this->executeActions($this->list, $this->parameters);

        global $by, $order;
        if (in_array($by, $this->orders)) {
            $this->parameters['order'] = array_merge([$by => $order], ($this->parameters['order'] ?? []));
        }

        if ($this->pageNavigation && $this->dataType === self::PARAM_D7) {
            $nav = new PageNavigation('nav');
            $nav->allowAllRecords(false);
            $nav->setPageSizes([10,20,50,100,200,500]);
            $nav->initFromUri();
            $this->parameters['count_total'] = true;
            $this->parameters['offset'] = $nav->getOffset();
            $this->parameters['limit'] = $nav->getLimit();
            $result = $this->getResultD7($this->parameters);
            $nav->setRecordCount($result->getCount());

            global $APPLICATION;
            ob_start();
            $APPLICATION->IncludeComponent(
                'bitrix:main.pagenavigation',
                'admin',
                [
                    'NAV_OBJECT' => $nav,
                    'TABLE_ID' => $this->tableId,
                    'TITLE' => Lang::get('NAV_TITLE'),
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );
            $navString = ob_get_clean();
            $this->list->NavText($navString);
        } elseif ($this->pageNavigation && $this->dataType !== self::PARAM_D7) {
            $result = new \CAdminResult($this->getResult($this->parameters), $this->tableId);
            $result->NavStart(20, false);
            $this->list->NavText($result->GetNavPrint(Lang::get('NAV_TITLE'), false));
        } else {
            $result = $this->getResult($this->parameters);
        }

        $this->list->AddHeaders($this->headers);

        if ($this->totalRow) {
            $row = &$this->list->AddTotalRow();
            $this->executeTotalRow($row, array_keys($this->headers), $this->parameters, $this->list);
        }

        while ($rowData = $this->getRowData($result)) {
            $row = &$this->list->AddRow($rowData[$this->columnRowId], $rowData);
            $this->modifyRow($row, $rowData, $this->list);

            $arActions = [];
            $this->setRowActions($arActions, $rowData, $this->list);
            $row->AddActions($arActions);
        }

        $this->list->AddFooter([['counter' => true, 'value' => 0]]);
        $this->list->AddGroupActionTable($this->groupActions);
        $this->list->AddAdminContextMenu($this->tableButtons);
        $this->list->CheckListMode();
    }

    private function getRowData($result)
    {
        if ($result instanceof \CAdminResult) {
            return $result->NavNext(false);
        } elseif ($result instanceof \CAllDBResult) {
            return $result->Fetch();
        } elseif ($result instanceof Result) {
            return $result->fetch();
        }

        return false;
    }

    public function isReference(): bool
    {
        return $this->reference;
    }

    final protected function setDataType(int $type = self::PARAM_D7): void
    {
        $this->dataType = $type;
    }

    final protected function setPageNavigation(bool $enable): void
    {
        $this->pageNavigation = $enable;
    }

    final protected function setTotalRow(bool $enable): void
    {
        $this->totalRow = $enable;
    }

    final protected function addFilter(Filter $filter): void
    {
        $this->filters[] = $filter;
    }

    final public function addStaticFilterParam(string $key, string $value = ''): void
    {
        $this->staticFilters[$key] = $value;
    }

    final public function setDefaultOrder(string $by, string $order = 'ASC'): void
    {
        $this->by = $by;
        $this->order = $order;
    }

    final public function addTableButton(string $name, string $link, string $icon = '', string $title = ''): void
    {
        $this->tableButtons[] = ['TEXT' => $name, 'LINK' => $link, 'TITLE' => $title, 'ICON' => $icon];
    }

    final public function addGroupAction(string $code, string $title): void
    {
        $this->groupActions[$code] = $title;
    }

    final public function addHeader(string $key, string $title, bool $sort = true, bool $default = true, string $align = 'left'): void
    {
        $header = [
            'id' => $key,
            'content' => $title,
            'align' => $align,
            'default'  => $default
        ];

        if ($sort) {
            $header['sort'] = $key;
            $this->orders[] = $key;
        }

        $this->headers[$key] = $header;
    }

    final protected function jsReferenceAction(string $id): string
    {
        return "$(window.opener.document).find('#{$this->referenceInput}').val('{$id}');
            window.opener.document.changeInput('#{$this->referenceInput}'); window.close();";
    }

    final protected function jsDeleteAction(string $id, string $params = ''): string
    {
        $confirm = json_encode(str_replace('#ID#', $id, Lang::get('CONFIRM_DELETE_LIST')));
        return "if (confirm({$confirm})) {$this->list->ActionDoGroup($id, 'delete', $params)}";
    }

    final protected function jsAction(string $id, string $action, string $params = ''): string
    {
        return $this->list->ActionDoGroup($id, $action, $params);
    }

    final protected function jsRedirectAction(string $url, array $params = []): string
    {
        return $this->list->ActionRedirect(Url::make($url, $params));
    }

    /**
     * @param array $parameters
     * @return Result
     * @throws \Exception
     */
    protected function getResultD7(array $parameters): Result
    {
        throw new \Exception('Method is not implemented!');
    }

    /**
     * @param array $parameters
     * @return \CAllDBResult
     * @throws \Exception
     */
    protected function getResult(array $parameters): \CAllDBResult
    {
        throw new \Exception('Method is not implemented!');
    }
}
