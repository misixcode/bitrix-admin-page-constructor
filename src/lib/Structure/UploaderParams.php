<?

namespace AdminConstructor\Structure;

class UploaderParams
{
    private $prefix = '';
    private $name = '';
    private $current = [];
    private $directory = 'admin_constructor';
    private $optimize = false;
    private $width = 1200;
    private $height = 1200;
    private $count = 1;
    private $image = false;
    private $ext = '';

    public function setPrefix(string $prefix): UploaderParams
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function setName(string $name): UploaderParams
    {
        $this->name = $name;
        return $this;
    }

    public function setCurrent(array $current): UploaderParams
    {
        $this->current = $current;
        return $this;
    }

    public function setDirectory(string $directory): UploaderParams
    {
        $this->directory = $directory;
        return $this;
    }

    public function setOptimize(bool $optimize, int $width, int $height): UploaderParams
    {
        $this->optimize = $optimize;
        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    public function setCount(int $count): UploaderParams
    {
        $this->count = $count;
        return $this;
    }

    public function setImage(bool $image): UploaderParams
    {
        $this->image = $image;
        return $this;
    }

    public function setExtensions(string $ext): UploaderParams
    {
        $this->ext = $ext;
        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCurrent(): array
    {
        return $this->current;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function isOptimize(): bool
    {
        return $this->optimize;
    }

    public function getOptimizeWidth(): int
    {
        return $this->width;
    }

    public function getOptimizeHeight(): int
    {
        return $this->height;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function isImage(): bool
    {
        return $this->image;
    }

    public function getExtensions(): string
    {
        return $this->ext;
    }
}