<?php

namespace app\Http\Controllers;

use Hypervel\Support\Facades\App;

/**
 * CrudTrait
 * 
 * by default, resource controller mengharuskan untuk define methods:
 * 
 * - `index()`
 * - `show()`
 * - `create()`
 * - `store()`
 * - `edit()`
 * - `update()`
 * - `destroy()`
 * 
 * tapi karena terlalu banyak boilerplate hanya untuk methods (dan hidup terlalu singkat), maka dibuatlah trait ini.
 * sehingga kamu hanya perlu define methods:
 * 
 * - index
 * - form
 * - save (rencananya, method ini akan saya hapus)
 * - delete (tidak harus, hanya jika butuh melakukan custom)
 * - findModel
 * 
 * @link https://darkghosthunter.medium.com/laravel-dependency-injection-on-methods-and-closures-ac17c7d5e8d1
 */
trait CrudTrait
{
    public function show($id)
    {
        return App::call([$this, 'view'], ['id' => $id]);
    }

    public function create()
    {
        return App::call([$this, 'form']);
    }

    public function edit($id)
    {
        return App::call([$this, 'form'], ['id' => $id]);
    }

    public function store()
    {
        return App::call([$this, 'save']);
    }

    public function update($id)
    {
        return App::call([$this, 'save'], ['id' => $id]);
    }

    public function destroy($id)
    {
        return App::call([$this, 'delete'], ['id' => $id]);
    }
}
