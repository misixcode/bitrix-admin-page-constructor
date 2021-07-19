<?

namespace AdminConstructor\Tool;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use AdminConstructor\Structure\UploaderParams;

class Uploader
{
    private $result = [];
    private $files = [];
    private $input = '';
    private $old = [];
    private $remove = [];

    /**
     * @var HttpRequest;
     */
    private $request;

    /**
     * Uploader constructor.
     * @param UploaderParams $params
     */
    public function __construct(UploaderParams $params)
    {
        $prefix = $params->getPrefix();
        $inputName = $params->getName();
        $old = $params->getCurrent();
        $dirName = $params->getDirectory();
        $optimize = $params->isOptimize();
        $width = $params->getOptimizeWidth();
        $height = $params->getOptimizeHeight();
        $this->request = Context::getCurrent()->getRequest();
        $this->input = 'BX_UPLOADER_' . $prefix . $inputName;
        $this->old = $old;

        $i = 0;
        while (isset($this->request[$this->input . '_' . $i])) {
            $this->prepareItem($i);
            $i++;
        }

        $i = 0;
        while (isset($this->request[$this->input . '_' . $i . '_NEW'])) {
            $this->prepareItem($i, '_NEW');
            $i++;
        }

        foreach ($this->files as $path) {
            if (intval($path) > 0) {
                $fileId = intval($path);
            } else {
                $arFile = \CFile::MakeFileArray($path);
                $name = pathinfo($arFile['name']);
                $arFile['name'] = \Cutil::translit($name['filename'], 'ru', ['replace_space' => '_', 'replace_other' => '_']) . '.' . $name['extension'];

                if (strlen(\CFile::CheckImageFile($arFile)) === 0 && $optimize) {
                    \CFile::ResizeImage($arFile, ['width' => $width, 'height' => $height], BX_RESIZE_IMAGE_PROPORTIONAL);
                }

                $fileId = intval(\CFile::SaveFile($arFile, $dirName));
            }

            if ($fileId > 0) {
                $this->result[] = $fileId;
            }
        }

        unset($_SESSION[$this->input . '_SAVED']);
        unset($_SESSION[$this->input . '_SAVED_PUBLIC']);
    }

    private function prepareItem(int $i, string $add = '')
    {
        $fileKey = $this->input . '_' . $i . $add;
        $tmp = $this->request[$fileKey];

        if ($this->request[$fileKey . '_del'] !== 'Y') {
            if (is_array($tmp)) {
                $absolute = implode(
                    DIRECTORY_SEPARATOR,
                    [
                        rtrim(Application::getDocumentRoot(), '\\/'),
                        'upload',
                        'tmp',
                        trim($tmp['tmp_name'], '\\/')
                    ]
                );

                $dir = implode(
                    DIRECTORY_SEPARATOR,
                    [
                        rtrim(Application::getDocumentRoot(), '\\/'),
                        'upload',
                        'tmp_codeos',
                        bitrix_sessid()
                    ]
                );

                $name = pathinfo($tmp['name']);

                $path = implode(
                    DIRECTORY_SEPARATOR,
                    [
                        $dir,
                        \Cutil::translit($name['filename'], 'ru', ['replace_space' => '_', 'replace_other' => '_']) . '.' . $name['extension']
                    ]
                );

                $fileNumber = 0;
                while (file_exists($path)) {
                    $fileNumber++;
                    $path = implode(
                        DIRECTORY_SEPARATOR,
                        [
                            $dir,
                            \Cutil::translit($name['filename'], 'ru', ['replace_space' => '_', 'replace_other' => '_']) . '_' . $fileNumber . '.' . $name['extension']
                        ]
                    );
                }

                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }

                copy($absolute, $path);

                $this->files[$this->input . '_' . count($this->files)] = str_replace(rtrim(Application::getDocumentRoot(), '\\/'), null, $path);
            } else {
                $this->files[$this->input . '_' . count($this->files)] = $tmp;
            }
        } else {
            if (in_array($tmp, $this->old)) {
                $this->remove[] = $tmp;
            }
        }
    }

    public function getArray(): array
    {
        return $this->result;
    }

    public function reset(): void
    {
        foreach ($this->result as $key => $file) {
            if (in_array($file, $this->old)) {
                continue;
            }

            \CFile::Delete($file);
            unset($this->result[$key]);
        }

        $_SESSION[$this->input . '_SAVED'] = $this->files;
        $_SESSION[$this->input . '_SAVED_PUBLIC'] = [];
        foreach ($this->files as $path) {
            $temp = \CFile::MakeFileArray($path);
            $temp['image'] = strlen(\CFile::CheckImageFile($temp)) === 0;
            $temp['status'] = true;
            $temp['url'] = $path;
            $_SESSION[$this->input . '_SAVED_PUBLIC'][] = $temp;
        }
    }

    public function clear(): void
    {
        foreach ($this->remove as $file) {
            \CFile::Delete($file);
        }

        unset($_SESSION[$this->input . '_SAVED']);
        unset($_SESSION[$this->input . '_SAVED_PUBLIC']);
    }
}