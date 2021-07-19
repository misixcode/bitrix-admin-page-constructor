<?

namespace AdminConstructor\Structure;

class ReferenceItem
{
    private $name = '';
    private $value = '';
    private $more = '';
    private $icon = '';

    public function __construct(string $value = null, string $name = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function setMore(string $url): void
    {
        $this->more = $url;
    }

    public function setIcon(string $iconCode): void
    {
        $this->icon = $iconCode;
    }

    public function asArray(): array
    {
        $return = [
            'name' => $this->name,
            'value' => $this->value,
        ];

        if (mb_strlen($this->more) > 0) {
            $return['more'] = $this->more;
            $return['icon'] = $this->icon;
        }

        return $return;
    }
}