<?

namespace AdminConstructor\Structure;

class ReferenceSearch
{
    private $search = '';
    private $collection = [];

    public function __construct(string $search = null)
    {
        $this->search = $search;
    }

    public function add(string $key, string $value): void
    {
        $value = preg_replace_callback(
            '/(' . preg_quote($this->search, '/') . ')/ui',
            function ($match) {
                return "<b>{$match[0]}</b>";
            },
            $value
        );

        $this->collection[$key] = $value;
    }

    public function asArray(): array
    {
        return ['result' => $this->collection];
    }
}
