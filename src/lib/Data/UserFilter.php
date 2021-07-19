<?

namespace AdminConstructor\Data;

class UserFilter extends SampleFilter
{
    protected function checkValue(&$value): bool
    {
        return intval($value) > 0;
    }

    public function getInput(): string
    {
        $name = $this::PREFIX . $this->code;
        $value = $this->getValues()[$name];

        return FindUserID($name, $value, '', 'find_form');
    }
}