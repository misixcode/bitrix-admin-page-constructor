<?

namespace AdminConstructor\System;

class AdminList extends \CAdminList
{
    public function &AddRow($id = false, $arRes = array(), $link = false, $title = false)
    {
        $row = new AdminListRow($this->aHeaders, $this->table_id);
        $row->id = $id;
        $row->arRes = $arRes;
        $row->link = $link;
        $row->title = $title;
        $row->pList = &$this;
        $row->class = '';

        if ($id) {
            if ($this->bEditMode && in_array($id, $this->arEditedRows)) {
                $row->bEditMode = true;
            } elseif (!empty($this->arUpdateErrorIDs) && in_array($id, $this->arUpdateErrorIDs)) {
                $row->bEditMode = true;
            }
        }

        $this->aRows[] = &$row;
        return $row;
    }

    public function &AddTotalRow($id = false, $arRes = array(), $link = false, $title = false)
    {
        $row = new AdminListRow($this->aHeaders, $this->table_id);
        $row->id = $id;
        $row->arRes = $arRes;
        $row->link = $link;
        $row->title = $title;
        $row->pList = &$this;
        $row->class = 'ac-total-row';
        $row->bReadOnly = true;
        $this->aRows[] = &$row;
        return $row;
    }
}