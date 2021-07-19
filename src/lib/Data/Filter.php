<?

namespace AdminConstructor\Data;

use AdminConstructor\Page\Table;

abstract class Filter
{
    protected const PREFIX = 'filter_';

    private $values = [];

    final public function prepare(&$parameters, int $parametersType = Table::PARAM_D7): void
    {
        foreach ($this->getVars() as $var) {
            if (!is_string($var)) {
                continue;
            }

            global $$var;
            $this->values[$var] = $$var;
        }

        $this->prepareParams($parameters, $parametersType);
    }

    protected function getValues(): array
    {
        return $this->values;
    }

    abstract public function prepareParams(&$parameters, int $parametersType = Table::PARAM_D7): void;
    abstract public function getVars(): array;
    abstract public function getTitle(): string;
    abstract public function getInput(): string;
}