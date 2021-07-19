<?

namespace AdminConstructor;

use AdminConstructor\Page\Base;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

class Router
{
    protected const NAME_SEPARATORS = ['_', '-', '.'];

    private $normalise = true;
    private $module = '';
    private $prefix = '';
    private $namespace = '';

    /** @var Base */
    private $page;

    /** @var callable */
    private $route;

    public function __construct(callable $route = null)
    {
        $this->route = $route;
    }

    /**
     * @throws \Exception
     */
    public function begin(): void
    {
        if (mb_strlen($this->module) > 0 && !Loader::includeModule($this->module)) {
            throw new \Exception("Module {$this->module} not found!");
        }

        $class = implode('\\', [$this->namespace, $this->getClassName()]);

        if (!class_exists($class)) {
            throw new \Exception("Class {$class} not found!");
        }

        if (!is_subclass_of($class, Base::class)) {
            throw new \Exception("Class {$class} is not subclass " . Base::class . "!");
        }

        $this->page = new $class();
    }

    /**
     * @throws \Exception
     */
    public function print(): void
    {
        ob_start();
        $level = ob_get_level();

        try {
            $this->page->print();
            ob_end_flush();
        } catch (\Throwable $e) {
            while (ob_get_level() >= $level) {
                ob_end_clean();
            }
            throw new \Exception(null, null, $e);
        }
    }

    protected function getClassName(): string
    {
        $request = Context::getCurrent()->getRequest();
        $server = $request->getServer();
        $name = basename(parse_url($server->getRequestUri(), PHP_URL_PATH));

        if (is_callable($this->route)) {
            return strval(call_user_func_array($this->route, [$name, $request]));
        }

        return $this->convertClassName($name);
    }

    protected function convertClassName(string $name): string
    {
        if (mb_strtolower(substr($name, -4, 4)) === '.php') {
            $name = substr($name, 0, mb_strlen($name) - 4);
        }

        if (mb_strlen($this->prefix) > 0) {
            if (mb_strtolower(substr($name, 0, mb_strlen($this->prefix))) === mb_strtolower($this->prefix)) {
                $name = substr($name, mb_strlen($this->prefix));
            }
        }

        if ($this->normalise) {
            $explode = [$name];

            foreach (static::NAME_SEPARATORS as $separator) {
                $temp = [];
                foreach ($explode as $section) {
                    $temp = array_merge($temp, explode($separator, $section));
                }
                $explode = $temp;
            }

            $result = [];
            foreach ($explode as $section) {
                $section = trim($section);
                if (mb_strlen($section) > 0) {
                    $result[] = mb_strtoupper(substr($section, 0, 1))
                        . mb_strtolower(substr($section, 1, mb_strlen($section)));
                }
            }

            return implode('', $result);
        }

        return str_replace(static::NAME_SEPARATORS, '', $name);
    }

    public function showError(\Throwable $e): void
    {
        $message = new \CAdminMessage([
            'MESSAGE' => sprintf(
                "%s in %s at line %s<br><br>%s",
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            ),
            'TYPE' => 'ERROR'
        ]);
        echo $message->Show();
    }

    public function isModalMode(): bool
    {
        if (!($this->page instanceof Base)) {
            return false;
        }

        return $this->page->isModalMode();
    }

    public function setNormalise(bool $normalise): void
    {
        $this->normalise = $normalise;
    }

    public function setModule(string $module): void
    {
        $this->module = $module;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = trim($namespace, '\\');
    }
}