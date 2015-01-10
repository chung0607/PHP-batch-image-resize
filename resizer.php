<?php

/** @var STRING $from - Path of the source folder / file */
$from = '/path/from';

/** @var STRING $to - Path of the destination folder / file */
$to = '/path/to';

/** @var boolean $isReplace - Will overwrite existing files in destination if true. */
$isReplace = false;

$resizer = new Resizer($from, $to, $isReplace);
$resizer->resize();

class Resizer
{
    private $_from;
    private $_to;
    private $_isReplace;

    private $_counter;

    public function __construct($from, $to, $isReplace)
    {
        $this->_from = $from;
        $this->_to   = $to;
        $this->_isReplace = $isReplace;
        $this->_counter = 0;
    }

    public function resize()
    {
        if (!$this->_checkToFolder()) {
            echo "\n\e[0;37mCould not create destination folder. Unable to start!\n";
            return;
        }

        echo "\n\e[0;37mStart resizing!\n";
        $this->_folderLooper('');
        echo "\n\e[0;37mFinish resizing!\n";
    }

    private function _checkToFolder()
    {
        if (is_dir($this->_from)) {
            if (!is_dir($this->_to)) {
                mkdir($this->_to);
            }
            if (!is_dir($this->_to)) {
                return false;
            }
        }
        return true;
    }

    private function _folderLooper($folder)
    {
        // input is actually a file, resize it
        if (!is_dir($this->_from . $folder)) {
            if (is_file($this->_from . $folder)) {
                $this->_resizeFile($folder);
            }
        }
        else {
            echo "\e[0;36mOpening folder: \t$this->_from$folder ... \e[0;37m";
            $files = scandir($this->_from . $folder);

            if ($files !== false) {
                $count = count($files) - 2;
                echo "\e[0;34m\t$count items found\e[0;37m\n";
                if (!file_exists($this->_to . $folder)) {
                    echo "\e[0;36mCreate folder: \t\t$this->_to$folder\e[0;37m\n";
                    mkdir($this->_to . $folder);
                }
                foreach ($files as $file) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }

                    $this->_folderLooper($folder . '/' . $file);
                }
            }
            else {
                echo "\e[0;31m\tFailed\e[0;37m\n";
            }
        }
    }

    private function _resizeFile($file)
    {
        $this->_incrementCounter();

        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $ext = strtolower($ext);

        if ($ext != 'jpg' && $ext != 'jpeg') {
            echo "\e[0;33mSkip non JPEG file: \t$file\e[0;37m\n";
            return;
        }

        if (!$this->_isReplace && file_exists($this->_to . '/' . $file)) {
            echo "\e[0;33mSkip existing image: \t$file\e[0;37m\n";
            return;
        }

        echo "Resizing image: \t$file ... ";

        try {
            $img       = new Imagick($this->_from . '/' . $file);
            $dimension = $img->getImageGeometry();
            if ($dimension['height'] > $dimension['width']) {
                $img->scaleImage(0, 1024);
            }
            else {
                $img->scaleImage(1024, 0);
            }
            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality(80);
            $img->writeImage($this->_to . '/' . $file);
            $img->destroy();
        }
        catch (Exception $e) {
            echo "\e[0;31m\tFailed\e[0;37m\n";
            echo $e->getMessage() . "\n";
        }

        echo "\e[0;32m\tDone!\e[0;37m\n";

        return true;
    }

    private function _incrementCounter()
    {
        $this->_counter++;
        if ($this->_counter % 100 == 0) {
            echo "\n\e[0;32m$this->_counter items handled\e[0;37m\n\n";
        }
    }

}