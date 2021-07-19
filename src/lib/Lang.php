<?

namespace AdminConstructor;

abstract class Lang
{
    private static $load = false;
    private static $language = 'ru';
    private static $phrases = [];

    private static function load(): void
    {
        if (static::$load) {
            return;
        }

        $path = dirname(__DIR__) . '/lang/' . static::$language . '.php';

        if (file_exists($path)) {
            $pack = include $path;
            foreach ($pack as $key => $phrase) {
                static::set($key, $phrase);
            }
        }

        static::$load = true;
    }

    public static function setLanguage(string $code): void
    {
        static::$language = $code;
    }

    public static function set(string $key, string $phrase): void
    {
        static::$phrases[$key] = $phrase;
    }

    public static function get(string $key, array $replace = [])
    {
        self::load();

        $phrase = static::$phrases[$key] ?? '';

        foreach ($replace as $key => $value) {
            $phrase = str_replace(strval($key), strval($value), $phrase);
        }

        return $phrase;
    }

    public static function getBooleanTypes(): array
    {
        return [
            'N' => static::get('BOOLEAN_TYPE_N'),
            'Y' => static::get('BOOLEAN_TYPE_Y'),
        ];
    }

    public static function getBooleanType(string $id = null): string
    {
        return static::getBooleanTypes()[$id] ?? '';
    }

    public static function getYNTypes(): array
    {
        return [
            0 => static::get('BOOLEAN_TYPE_N'),
            1 => static::get('BOOLEAN_TYPE_Y'),
        ];
    }

    public static function getYNType(int $id = null): string
    {
        return static::getYNTypes()[$id] ?? '';
    }
}