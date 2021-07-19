<?

namespace AdminConstructor\Helper;

class Url
{
    public static function make(string $page, array $params = [], string $langId = LANGUAGE_ID): string
    {
        if (mb_strlen($langId) > 0) {
            $params['lang'] = $langId;
        }

        $query = http_build_query($params);

        if (mb_strlen($query) > 0) {
            $query = "?$query";
        }

        return "{$page}{$query}";
    }

    public static function systemToRelative(string $path): string
    {
        $matches = [];
        preg_match('#.+([\\\/]bitrix[\\\/].+|[\\\/]local[\\\/].+)#', $path, $matches);
        $relative = $matches[1];

        return '/' . trim(str_replace('\\', '/', $relative), '/');
    }

    public static function getStaticDir(bool $relative = false): string
    {
        $path = __DIR__ . '/../../static';
        return $relative ? static::systemToRelative(realpath($path)) : realpath($path);
    }
}