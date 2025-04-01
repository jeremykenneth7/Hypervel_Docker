<?php

namespace app\Http\Controllers;

use app\models\File;
use Hypervel\Support\Facades\App;

class FileController extends AbstractController
{
    public function inline($id)
    {
        $model = $this->findModel(['id' => $id]);
        return response()->download($model->path(), $model->name, [], 'inline');
    }

    public function attachment($id)
    {
        $model = $this->findModel(['id' => $id]);
        return response()->download($model->path(), $model->name, [], 'attachment');
    }

    public function download($id)
    {
        return App::call([$this, 'attachment'], ['id' => $id]);
    }

    /**
     * @return File
     */
    public function findModel($wheres)
    {
        $class = config('jeemce.models.file', File::class);
        $model = $class::query()->where($wheres)->firstOrFail();
        return $model;
    }
}
