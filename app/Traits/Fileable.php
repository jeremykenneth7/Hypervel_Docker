<?php

namespace App\Traits;

use app\models\File;
use Hypervel\Http\UploadedFile;
use Hypervel\Support\Facades\Request;
use Hypervel\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait Fileable
{
    public $files = [];
    public $fileParentId;
    public $fileParentTable;
    protected $maxFileSize = 350;
    protected $maxDimension = 1600;
    protected $memoryLimit = 512;

    public function file($field)
    {
        if (empty($this->files[$field])) {
            $file = File::query()->where([
                'parent_id'     => $this->id,
                'parent_table'  => $this->table,
                'parent_field'  => $field,
            ])->first();

            if (empty($file)) {
                $file = new File();
                $file->fill([
                    'parent_id'     => $this->id,
                    'parent_table'  => $this->table,
                    'parent_field'  => $field,
                ]);
            }
            $this->files[$field] = $file;
        }
        return $this->files[$field];
    }

    public function loadAllFiles()
    {
        $this->files = File::where('parent_id', $this->id)
            ->where('parent_table', $this->table)->get();
    }

    protected function compressImage(UploadedFile $file)
    {
        if (!str_starts_with($file->getMimeType(), 'image/')) {
            return $file;
        }

        $fileSize = $file->getSize() / 1024;
        if ($fileSize <= $this->maxFileSize) {
            return $file;
        }

        try {
            ini_set('memory_limit', $this->memoryLimit . 'M');

            $tmpPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $file->getClientOriginalExtension();

            $img = Image::make($file->getRealPath());

            $width = $img->width();
            $height = $img->height();

            $ratio = $width / $height;

            if ($width > $this->maxDimension || $height > $this->maxDimension) {
                if ($ratio > 1) {
                    $newWidth = $this->maxDimension;
                    $newHeight = round($this->maxDimension / $ratio);
                } else {
                    $newHeight = $this->maxDimension;
                    $newWidth = round($this->maxDimension * $ratio);
                }

                $img->resize($newWidth, $newHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $qualities = [80, 60, 40, 20];
            $targetReached = false;

            foreach ($qualities as $quality) {
                $img->save($tmpPath, $quality);

                if (filesize($tmpPath) / 1024 <= $this->maxFileSize) {
                    $targetReached = true;
                    break;
                }

                if ($quality === 40) {
                    $currentWidth = $img->width();
                    $currentHeight = $img->height();

                    $img->resize(round($currentWidth * 0.75), round($currentHeight * 0.75), function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
            }

            if (!$targetReached) {
                $img->resize(800, 800, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->save($tmpPath, 20);
            }

            $img->destroy();

            return new UploadedFile(
                $tmpPath,
                $file->getClientOriginalName(),
                $file->getMimeType(),
                null,
                true
            );
        } catch (\Exception $e) {
            return $file;
        }
    }

    public function saveAllFiles()
    {
        $files = array_filter(Request::allFiles(), function ($v, $k) {
            return strpos($k, 'upload_') === 0;
        }, ARRAY_FILTER_USE_BOTH);

        foreach ($files as $field => $uploadedFiles) {
            if (!is_array($uploadedFiles)) {
                $uploadedFiles = [$uploadedFiles];
            }

            foreach ($uploadedFiles as $uploadedFile) {
                if ($uploadedFile instanceof UploadedFile) {
                    try {
                        $processedFile = $this->compressImage($uploadedFile);

                        $path = date('Y/m/d');
                        $name = uniqid();

                        $modelFile = new File();
                        $modelFile->fill([
                            'parent_id' => $this->id,
                            'parent_table' => $this->table,
                            'parent_field' => str_replace("upload_", "", $field),
                            'name' => $uploadedFile->getClientOriginalName(),
                            'mime' => $uploadedFile->getMimeType(),
                            'path' => $path . "/" . $name . "." . $processedFile->getClientOriginalExtension(),
                        ]);

                        $modelFile->save();

                        Storage::put(
                            $modelFile->path,
                            file_get_contents($processedFile->getRealPath())
                        );

                        if (file_exists($processedFile->getRealPath())) {
                            unlink($processedFile->getRealPath());
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }
    }

    public static function bootFileable()
    {
        static::retrieved(function ($model) {
            $model->loadAllFiles();
        });

        static::saved(function ($model) {
            $model->saveAllFiles();
        });
    }
}
