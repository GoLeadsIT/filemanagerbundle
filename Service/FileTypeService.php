<?php

namespace Goleadsit\FileManagerBundle\Service;


use Goleadsit\FileManagerBundle\Helpers\FileManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Finder\SplFileInfo;

class FileTypeService
{
    const IMAGE_SIZE = [
        FileManager::VIEW_LIST => '22',
        FileManager::VIEW_THUMBNAIL => '100',
    ];

    /**
     * @var Router
     */
    private $router;

    /*
     * @var Packages
     */
    private $packages;

    /**
     * FileTypeService constructor.
     *
     * @param Router   $router
     * @param Packages $packages
     */
    public function __construct(Router $router, Packages $packages)
    {
        $this->router = $router;
        $this->packages = $packages;
    }

    public function preview(FileManager $fileManager, SplFileInfo $file)
    {
        if ($fileManager->getImagePath()) {
            $filePath = htmlentities($fileManager->getImagePath().rawurlencode($file->getFilename()));
        } else {
            $filePath = $this->router->generate('file_manager_file', array_merge($fileManager->getQueryParameters(), ['fileName' => rawurlencode($file->getFilename())]));
        }
        $extension = $file->getExtension();
        $type = $file->getType();
        if ('file' === $type) {
            $size = $this::IMAGE_SIZE[$fileManager->getView()];

            return $this->fileIcon($filePath, $extension, $size);
        }
        if ('dir' === $type) {
            $href = $this->router->generate('file_manager', array_merge($fileManager->getQueryParameters(), ['route' => $fileManager->getRoute().DIRECTORY_SEPARATOR.rawurlencode($file->getFilename())]));

            return [
                'path' => $filePath,
                'html' => "<i class='fa fa-folder-o' aria-hidden='true'></i>",
                'folder' => '<a  href="'.$href.'">'.$file->getFilename().'</a>',
            ];
        }
    }

    public function accept($type)
    {
        switch ($type) {
            case 'image':
                $accept = 'image/*';
                break;
            case 'media':
                $accept = 'video/*';
                break;
            case 'file':
                return false;
            default:
                return false;
        }

        return $accept;
    }

    public function fileIcon($filePath, $extension = null, $size = 75)
    {
        if ($extension === null) {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        }

        $filePath = str_replace('\\', '/', substr($filePath, 1));
        $filePath = $this->packages->getUrl($filePath);

        switch (true) {
            case preg_match('/(gif|png|jpe?g|svg)$/i', $extension):
                /** @var FileManager $fileManager */
                return [
                    "path" => $filePath,
                    "html" => "<img src=\"{$filePath}\" height='{$size}'>"
                ];
            case preg_match('/(mp4|ogg|webm)$/i', $extension):
                $fa = 'fa-file-video-o';
                break;
            case preg_match('/(pdf)$/i', $extension):
                $fa = 'fa-file-pdf-o';
                break;
            case preg_match('/(docx?)$/i', $extension):
                $fa = 'fa-file-word-o';
                break;
            case preg_match('/(xlsx?|csv)$/i', $extension):
                $fa = 'fa-file-excel-o';
                break;
            case preg_match('/(pptx?)$/i', $extension):
                $fa = 'fa-file-powerpoint-o';
                break;
            case preg_match('/(zip|rar|gz)$/i', $extension):
                $fa = 'fa-file-archive-o';
                break;
            default :
                $fa = 'fa-file-o';
        }
        return [
            "path" => $filePath,
            "html" => "<i class='fa {$fa}' aria-hidden='true'></i>"
        ];
    }

    public function isYoutubeVideo($url)
    {
        $rx = '~
              ^(?:https?://)?                            
               (?:www[.])?                               
               (?:youtube[.]com/watch[?]v=|youtu[.]be/)  
               ([^&]{11})                                
                ~x';

        return $has_match = preg_match($rx, $url, $matches);
    }
}