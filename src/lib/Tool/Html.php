<?

namespace AdminConstructor\Tool;

use AdminConstructor\Helper\Url;
use AdminConstructor\Lang;
use Bitrix\Main\Page\Asset;

class Html
{
    private static $filesCache = [];

    public static function referenceInput(string $name, string $id, string $url, string $value = null): string
    {
        $id = htmlspecialchars($id);
        $url = htmlspecialchars($url);
        $placeholder = Lang::get('REFERENCE_PLACEHOLDER');

        \CJSCore::Init('jquery3');

        $assets = Asset::getInstance();
        $assets->addJs(Url::getStaticDir(true) . '/js/reference.js');

        return "
            <div class='ac-reference'>
                <input type='text' name='{$name}' id='{$id}' class='ac-reference-input' value='{$value}'>
                <input type='text' class='ac-reference-search' placeholder='{$placeholder}'>
                <div class='ac-reference-list'></div>
                <button type='button' class='ac-reference-get'></button>
            </div>
            <script>
                new ReferenceInput('{$id}','{$url}');
            </script>
        ";
    }

    public static function autocompleteInput(
        string $name,
        string $id,
        string $value = null,
        string $url = null,
        array $values = [],
        int $size = 5
    ): string {
        $placeholder = Lang::get('REFERENCE_PLACEHOLDER');
        $result = '';
        if (strlen($url) > 0) {
            $mode = 'search';
        } else {
            $mode = 'select';
            foreach ($values as $safe => $display) {
                $safe = htmlspecialchars($safe);
                $result .= "<span data-value='{$safe}'>{$display}</span>";
            }
        }

        \CJSCore::Init('jquery3');

        $assets = Asset::getInstance();
        $assets->addJs(Url::getStaticDir(true) . '/js/autocomplete.js');

        return "
            <div class='ac-autocomplete {$mode}' id='wrap_{$id}'>
                <input placeholder='{$placeholder}' autocomplete='off' type='text' size='{$size}' name='{$name}' id='{$id}' value='{$value}' class='ac-autocomplete-input'>
                <div class='ac-autocomplete-result' data-result style='display:none;'>{$result}</div>
            </div>
            <script>
                new AutocompleteInput('wrap_{$id}','{$url}');
            </script>
        ";
    }

    public static function wrap(string $content = null, int $width = 100): string
    {
        if (mb_strlen($content) === 0) {
            return '';
        }

        return "<span class='ac-wrap-box' style='min-width:{$width}px;'>{$content}</span>";
    }

    public static function status(string $hex = '#FFFFFF', string $content = null): string
    {
        return "<div class='ac-status-box' style='background-color:{$hex};'><span style='color:{$hex}'>{$content}</span></div>";
    }

    public static function color(string $hex = '#FFFFFF'): string
    {
        if (mb_strlen($hex) === 0) {
            return '';
        }

        return "<div class='ac-color-box' style='background-color:{$hex};'><span style='color:{$hex}'>{$hex}</span></div>";
    }

    public static function file(int $id = null, string $description = null, int $width = 60, int $height = 60): string
    {
        $unique = "file_{$id}_{$width}_{$height}";

        if (isset(static::$filesCache[$unique])) {
            $arFile = static::$filesCache[$unique];
        } else {
            $arFile = \CFile::MakeFileArray($id);

            if ($arFile) {
                $arFile['IS_IMAGE'] = mb_strlen(\CFile::CheckImageFile($arFile)) <= 0;

                if ($arFile['IS_IMAGE']) {
                    $arFile['RESIZE'] = \CFile::ResizeImageGet(
                        $id,
                        ['width' => $width, 'height' => $height],
                        BX_RESIZE_IMAGE_EXACT
                    );

                    if ($arFile['RESIZE']) {
                        $arFile['RESIZE'] = $arFile['RESIZE']['src'];
                    }
                }

                $arFile['SRC'] = \CFile::GetPath($id);

                static::$filesCache[$unique] = $arFile;
            }
        }

        if (!$arFile) {
            return '';
        }

        $original = htmlspecialchars($arFile['name']);
        $description = htmlspecialchars($description);
        $style = "width:{$width}px;height:{$height}px;";
        $resize = $arFile['RESIZE'];
        $src = $arFile['SRC'];

        if ($arFile['IS_IMAGE'] && $arFile['RESIZE']) {
            $class = "ac-preview-image";
            $content = "<img src='{$resize}' alt='{$original}'>";
            $download = '';
        } else {
            $class = "ac-preview-file";
            $content = "<span class='align'><span class='icon'></span><span class='name'>{$original}</span></span>";
            $download = "download='{$original}'";
        }

        return "<a class='{$class}' style='{$style}' href='{$src}' target='_blank' {$download}>{$content}</a>";
    }

    public static function files(array $files = null, int $cols = 5, int $width = 60, int $height = 60)
    {
        $prepare = [];

        foreach ($files as $file) {
            $html = static::file(intval($file['id']), $file['description'], $width, $height);

            if (mb_strlen($html) > 0) {
                $prepare[] = $html;
            }
        }

        $box = $width * min(max($cols, 1), count($prepare));

        if (count($prepare) <= 0) {
            return '';
        }

        $content = implode('', $prepare);

        return "<div class='ac-files-box' style='width:{$box}px;'>{$content}</div>";
    }
}
