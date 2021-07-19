<?

namespace AdminConstructor\System;

class AdminListRow extends \CAdminListRow
{
    public $class;

    function Display()
    {
        $sDefAction = $sDefTitle = "";
        if (!$this->bEditMode) {
            global $adminSidePanelHelper;

            if (!empty($this->link)) {
                $sDefAction = ((is_object($adminSidePanelHelper) && $adminSidePanelHelper->isPublicSidePanel()) ?
                    "BX.adminSidePanel.onOpenPage('" . \CUtil::JSEscape($this->link) . "');" :
                    "BX.adminPanel.Redirect([], '" . \CUtil::JSEscape($this->link) . "', event);");
                $sDefTitle = $this->title;
            } else {
                $this->aActions = array_values($this->aActions);
                foreach ($this->aActions as $action) {
                    if ($action["DEFAULT"] == true) {
                        if (!empty($action["ACTION"])) {
                            $sDefAction = $action["ACTION"];
                        } else {
                            $sDefAction = ((is_object($adminSidePanelHelper) && $adminSidePanelHelper->isPublicSidePanel()) ?
                                "BX.adminSidePanel.onOpenPage('" . \CUtil::JSEscape($this->link) . "');" :
                                "BX.adminPanel.Redirect([], '" . \CUtil::JSEscape($action["LINK"]) . "', event)");
                        }

                        $sDefTitle = (!empty($action["TITLE"]) ? $action["TITLE"] : $action["TEXT"]);
                        break;
                    }
                }
            }

            $sDefAction = htmlspecialcharsbx($sDefAction);
            $sDefTitle = htmlspecialcharsbx($sDefTitle);
        }

        $sMenuItems = "";
        if (!empty($this->aActions)) {
            $sMenuItems = htmlspecialcharsbx(\CAdminPopup::PhpToJavaScript($this->aActions));
        }
        ?>
            <tr class="adm-list-table-row <?=$this->class?> <?=(isset($this->aFeatures["footer"]) && $this->aFeatures["footer"] == true ? ' footer' : '')?><?=$this->bEditMode ? ' adm-table-row-active' : ''?>"<?=($sMenuItems <> "" ? ' oncontextmenu="return ' . $sMenuItems . ';"' : '');?><?=($sDefAction <> "" ? ' ondblclick="' . $sDefAction . '"' . (!empty($sDefTitle) ? ' title="' . GetMessage("admin_lib_list_double_click") . ' ' . $sDefTitle . '"' : '') : '')?>>
                <?
                if (count($this->pList->arActions) > 0 || $this->pList->bCanBeEdited) :
                    $check_id = RandString(5);
                    ?>
                    <td class="adm-list-table-cell adm-list-table-checkbox adm-list-table-checkbox-hover<?=$this->bReadOnly ? ' adm-list-table-checkbox-disabled' : ''?>"><input type="checkbox" class="adm-checkbox adm-designed-checkbox" name="ID[]" id="<?=$this->table_id . "_" . $this->id . "_" . $check_id;?>" value="<?=$this->id?>" autocomplete="off" title="<?=GetMessage("admin_lib_list_check")?>"<?=$this->bReadOnly ? ' disabled="disabled"' : ''?><?=$this->bEditMode ? ' checked="checked" disabled="disabled"' : ''?> /><label class="adm-designed-checkbox-label adm-checkbox" for="<?=$this->table_id . "_" . $this->id . "_" . $check_id;?>"></label></td>
                    <?
                endif;

                if ($this->pList->bShowActions) :
                    if (!empty($this->aActions)) :
                        ?>
                        <td class="adm-list-table-cell adm-list-table-popup-block" onclick="BX.adminList.ShowMenu(this.firstChild, this.parentNode.oncontextmenu(), this.parentNode);"><div class="adm-list-table-popup" title="<?=GetMessage("admin_lib_list_actions_title")?>"></div></td>
                        <?
                    else :
                        ?>
                        <td class="adm-list-table-cell"></td>
                        <?
                    endif;
                endif;

                end($this->pList->aVisibleHeaders);
                $last_id = key($this->pList->aVisibleHeaders);
                reset($this->pList->aVisibleHeaders);

                $bVarsFromForm = ($this->bEditMode && is_array($this->pList->arUpdateErrorIDs) && in_array($this->id, $this->pList->arUpdateErrorIDs));
                foreach ($this->pList->aVisibleHeaders as $id => $header_props) {
                    $field = $this->aFields[$id];
                    if ($this->bEditMode && isset($field["edit"])) {
                        if ($bVarsFromForm && $_REQUEST["FIELDS"]) {
                            $val = $_REQUEST["FIELDS"][$this->id][$id];
                        } else {
                            $val = $this->arRes[$id];
                        }

                        $val_old = $this->arRes[$id];

                        echo '<td class="adm-list-table-cell',
                        (isset($header_props['align']) && $header_props['align'] ? ' align-' . $header_props['align'] : ''),
                        (isset($header_props['valign']) && $header_props['valign'] ? ' valign-' . $header_props['valign'] : ''),
                        ($id === $last_id ? ' adm-list-table-cell-last' : ''),
                        '">';

                        if (is_array($val_old)) {
                            foreach ($val_old as $k => $v) {
                                echo '<input type="hidden" name="FIELDS_OLD[' . htmlspecialcharsbx($this->id) . '][' . htmlspecialcharsbx($id) . '][' . htmlspecialcharsbx($k) . ']" value="' . htmlspecialcharsbx($v) . '">';
                            }
                        } else {
                            echo '<input type="hidden" name="FIELDS_OLD[' . htmlspecialcharsbx($this->id) . '][' . htmlspecialcharsbx($id) . ']" value="' . htmlspecialcharsbx($val_old) . '">';
                        }

                        switch ($field["edit"]["type"]) {
                            case "checkbox":
                                echo '<input type="hidden" name="FIELDS[' . htmlspecialcharsbx($this->id) . '][' . htmlspecialcharsbx($id) . ']" value="N">';
                                echo '<input type="checkbox" name="FIELDS[' . htmlspecialcharsbx($this->id) . '][' . htmlspecialcharsbx($id) . ']" value="Y"' . ($val == 'Y' ? ' checked' : '') . '>';
                                break;
                            case "select":
                                echo '<select name="FIELDS[' . htmlspecialcharsbx($this->id) . '][' . htmlspecialcharsbx($id) . ']"' . $this->__AttrGen($field["edit"]["attributes"]) . '>';
                                foreach ($field["edit"]["values"] as $k => $v) {
                                    echo '<option value="' . htmlspecialcharsbx($k) . '" ' . ($k == $val ? ' selected' : '') . '>' . htmlspecialcharsbx($v) . '</option>';
                                }
                                echo '</select>';
                                break;
                            case "input":
                                if (!$field["edit"]["attributes"]["size"]) {
                                    $field["edit"]["attributes"]["size"] = "10";
                                }
                                echo '<input type="text" ' . $this->__AttrGen($field["edit"]["attributes"]) . ' name="FIELDS[' . htmlspecialcharsbx($this->id) . '][' . htmlspecialcharsbx($id) . ']" value="' . htmlspecialcharsbx($val) . '">';
                                break;
                            case "calendar":
                                if (!$field["edit"]["attributes"]["size"]) {
                                    $field["edit"]["attributes"]["size"] = "10";
                                }
                                echo '<span style="white-space:nowrap;"><input type="text" ' . $this->__AttrGen($field["edit"]["attributes"]) . ' name="FIELDS[' . htmlspecialcharsbx($this->id) . '][' . htmlspecialcharsbx($id) . ']" value="' . htmlspecialcharsbx($val) . '">';
                                echo \CAdminCalendar::Calendar(
                                    'FIELDS[' . htmlspecialcharsbx($this->id) . '][' . htmlspecialcharsbx($id) . ']',
                                    '',
                                    '',
                                    $field['edit']['useTime']
                                ) . '</span>';
                                break;
                            case "file":
                                echo \CFileInput::Show(
                                    'FIELDS[' . htmlspecialcharsbx($this->id) . '][' . htmlspecialcharsbx($id) . ']',
                                    $val,
                                    $field["edit"]["showInfo"],
                                    $field["edit"]["inputs"]
                                );
                                break;
                            default:
                                echo $field["edit"]['value'];
                        }
                        echo '</td>';
                    } else {
                        if (!is_array($this->arRes[$id])) {
                            $val = trim($this->arRes[$id]);
                        } else {
                            $val = $this->arRes[$id];
                        }

                        if (isset($field["view"])) {
                            switch ($field["view"]["type"]) {
                                case "checkbox":
                                    if ($val == 'Y') {
                                        $val = htmlspecialcharsex(GetMessage("admin_lib_list_yes"));
                                    } else {
                                        $val = htmlspecialcharsex(GetMessage("admin_lib_list_no"));
                                    }
                                    break;
                                case "select":
                                    if ($field["edit"]["values"][$val]) {
                                        $val = htmlspecialcharsex($field["edit"]["values"][$val]);
                                    } else {
                                        $val = htmlspecialcharsex($val);
                                    }
                                    break;
                                case "file":
                                    if ($val > 0) {
                                        $val = \CFileInput::Show(
                                            'NO_FIELDS[' . htmlspecialcharsbx($this->id) . '][' . htmlspecialcharsbx($id) . ']',
                                            $val,
                                            $field["view"]["showInfo"],
                                            $field["view"]["inputs"]
                                        );
                                    } else {
                                        $val = '';
                                    }
                                    break;
                                case "html":
                                    $val = $field["view"]['value'];
                                    break;
                                default:
                                    $val = htmlspecialcharsex($val);
                                    break;
                            }
                        } else {
                            $val = htmlspecialcharsex($val);
                        }

                        echo '<td class="adm-list-table-cell',
                        (isset($header_props['align']) && $header_props['align'] ? ' align-' . $header_props['align'] : ''),
                        (isset($header_props['valign']) && $header_props['valign'] ? ' valign-' . $header_props['valign'] : ''),
                        ($id === $last_id ? ' adm-list-table-cell-last' : ''),
                        '">';
                        echo ((string)$val <> "" ? $val : '&nbsp;');
                        if (isset($field["edit"]) && $field["edit"]["type"] == "calendar") {
                            \CAdminCalendar::ShowScript();
                        }
                        echo '</td>';
                    }
                }
                ?>
            </tr>
        <?
    }
}