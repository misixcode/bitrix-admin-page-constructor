<?

namespace AdminConstructor\Data;

use AdminConstructor\Page\Table;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class DateTimeFilter extends Filter
{
    protected const START = '_start';
    protected const STOP = '_stop';

    protected $code = '';
    protected $title = '';
    protected $vars = [];
    protected $time = false;

    public function __construct(string $code, Field &$field = null, string $title = null, bool $time = true)
    {
        $this->code = $code;
        $this->title = $title ?? (!is_null($field) ? $field->getTitle() : $code);
        $this->vars = [
            static::PREFIX . $this->code . static::START,
            static::PREFIX . $this->code . static::STOP,
        ];
        $this->time = $time;
    }

    public function prepareParams(&$parameters, int $parametersType = Table::PARAM_D7): void
    {
        $nameStart = static::PREFIX . $this->code . static::START;
        $nameStop = static::PREFIX . $this->code . static::STOP;
        $valueStart = $this->getValues()[$nameStart];
        $valueStop = $this->getValues()[$nameStop];

        try {
            if ($this->time) {
                $valueStart = !empty($valueStart) ? new DateTime($this->getValues()[$nameStart]) : false;
                $valueStop = !empty($valueStop) ? new DateTime($this->getValues()[$nameStop]) : false;
            } else {
                $valueStart = !empty($valueStart) ? new Date($this->getValues()[$nameStart]) : false;
                $valueStop = !empty($valueStop) ? new Date($this->getValues()[$nameStop]) : false;
            }

        } catch (\Exception $e) {
            return;
        }

        if ($valueStart instanceof Date) {
            $parameters['filter']['>=' . $this->code] = $valueStart;
        }

        if ($valueStop instanceof Date) {
            $parameters['filter']['<=' . $this->code] = $valueStop;
        }
    }

    public function getInput(): string
    {
        $nameStart = static::PREFIX . $this->code . static::START;
        $nameStop = static::PREFIX . $this->code . static::STOP;
        $valueStart = $this->getValues()[$nameStart];
        $valueStop = $this->getValues()[$nameStop];

        return CalendarPeriod(
            $nameStart,
            $valueStart,
            $nameStop,
            $valueStop,
            'find_form',
            "Y",
            '',
            '',
            ($this->time ? 20 : 10)
        );
    }

    public function getVars(): array
    {
        return $this->vars;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}