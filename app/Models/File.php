<?php

namespace App\models;

use Hypervel\Support\Facades\Storage;

class File extends Model
{
    protected $table = 'file';
    protected $guarded = [];

    public function path()
    {
        return Storage::path($this->path);
    }

    public function hasFile()
    {
        if (empty($this->id) || empty($this->path)) {
            return false;
        }
        return true;
    }

    public function urlFile(string $action = null, array $params = [])
    {
        $action = 'file.download';
        $params['id'] = $this->id;
        return route($action, $params);
    }

    public function url()
    {
        return $this->urlFile();
    }

    public function exist()
    {
        return $this->hasFile();
    }
}
