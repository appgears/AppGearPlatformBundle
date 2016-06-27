<?php

namespace AppGear\PlatformBundle\Field\Advanced\File;

use Cosmologist\Gears\Obj;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Initializer
{
    /**
     * Директория для хранения файлов
     *
     * @var string
     */
    protected $filesDirectory;

    /**
     * @param string $filesDirectory Директория для хранения файлов
     */
    public function __construct($filesDirectory)
    {
        $this->filesDirectory = $filesDirectory;
    }

    /**
     * Формирует значение поля на основе переданных данных
     *
     * @param object $instance Инстанц сущности к которой относится поле
     * @param mixed $data Данные, на основе которых должно быть сформировано значение
     * @param Field $property Объект поля модели, соответствующий данному полю
     *
     * @return mixed
     */
    public function init($instance, $data, $property)
    {
        if ($data instanceof UploadedFile) {
            $nonExistentFileName = $this->getNonExistentFileName($this->filesDirectory, $data->getClientOriginalName());

            return $data
                ->move($this->filesDirectory, $nonExistentFileName)
                ->getPathName();
        }

        return Obj::get($instance, $property->getName());
    }

    /**
     * Директория для свойства
     *
     * @param Property $property Property
     *
     * @return string
     */
    protected function getPropertyDirectoryPath($property)
    {
        // Заменяем разделители неймспейса на юниксовые разделители директорий
        $path = str_replace($property->getModel()->getFullName(), '\\', '/');

        // Удаляем лишние элементы оставшиеся от неймспейса
        $path = str_replace($path, 'bundle/entity/', '/');

        return $path;
    }

    /**
     * Возвращает подходящее не занятое имя файла
     *
     * @param string $directory Директория для хранения файла
     * @param string $originalFileName Исходное название файла
     *
     * @return string
     */
    protected function getNonExistentFileName($directory, $originalFileName)
    {
        $fileName = $originalFileName;

        $i = 0;
        while (file_exists($directory . $fileName)) {
            $fileName = $i . '_' . $originalFileName;
            $i++;
        }

        return $fileName;
    }
}