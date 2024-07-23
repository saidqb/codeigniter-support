<?php

namespace Saidqb\CodeigniterSupport\Concerns;

use Config\Mimes;

trait HasFile
{
    protected function is_file()
    {
        return is_file($this->path);
    }

    protected function fileExtensionType($type)
    {
        $exstension = [
            'image' => [
                'jpg',
                'jpeg',
                'png',
                'gif',
                'bmp',
                'webp',
                'svg',
                'ico'
            ],
            'document' => [
                'pdf',
                'doc',
                'docx',
                'xls',
                'xlsx',
                'ppt',
                'pptx',
                'txt',
                'csv',
                'rtf'
            ],
            'video' => [
                'mp4',
                'avi',
                'flv',
                'wmv',
                'mov',
                'webm',
                'mkv',
                '3gp',
                'mpg',
                'mpeg'
            ],
        ];

        if (isset($exstension[$type])) {
            return $exstension[$type];
        }
        return [];
    }

    protected function fileMimeType($type)
    {
        $mime = $this->fileExtensionType($type);
        $nmimes = [];

        foreach ($mime as $key => $value) {
            $nmimes[] = Mimes::guessTypeFromExtension($value);
        }

        return $nmimes;
    }

    protected function fileDirUpload($type = '')
    {
        $dir = '';
        if ($type == 'private') {
            $dir = 'private/';
        }
        if ($type == 'public') {
            $dir = 'public/';
        }
        return WRITEPATH . 'uploads/' . $dir;
    }

    protected function fileUrl($type = '')
    {
        $dir = '';
        if ($type == 'private') {
            $dir = 'private/';
        }
        if ($type == 'public') {
            $dir = 'public/';
        }
        return 'uploads/' . $dir;
    }

    protected function fileNameExtension($originalName)
    {
        return pathinfo($originalName, PATHINFO_EXTENSION) ?? '';
    }

    protected function fileIsImage($exstension)
    {
        $type = Mimes::guessTypeFromExtension($exstension) ?? '';

        if (mb_strpos($type, 'image') !== 0) {
            return false;
        }

        return true;
    }
}
