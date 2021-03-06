<?php

namespace crocodicstudio\crudbooster\Modules\FileManagerModule;

use crocodicstudio\crudbooster\controllers\CBController;
use CRUDBooster;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request;

class AdminFileManagerController extends CBController
{
    public function cbInit()
    {
    }

    public function getIndex()
    {

        $path = request('path') ? base64_decode(request('path')) : '';

        if (strpos($path, '..') || $path == '.' || strpos($path, '/.')) {
            return redirect()->route('AdminFileManagerControllerGetIndex');
        }

        $currentPath = $path ?: 'uploads';
        $currentPath = trim($currentPath, '/');

        $directories = Storage::directories($currentPath);
        $files = Storage::files($currentPath);

        return view('CbFileManager::index', ['files' => $files, 'directories' => $directories, 'currentPath' => $currentPath]);
    }

    public function postCreateDirectory()
    {
        $path = base64_decode(request('path'));
        $path = ($path) ?: 'uploads';
        $name = request('name');
        $name = str_slug($name, '_');
        Storage::makeDirectory($path.'/'.$name);

        backWithMsg('The directory has been created!');
    }

    public function postUpload()
    {
        $allowedExtension = explode(',', strtolower(cbConfig('UPLOAD_TYPES')));
        $path = request('path') ? base64_decode(request('path')) : 'uploads';
        $file = Request::file('userfile');
        if (! $file) {
            return null;
        }

        $fileName = $file->getClientOriginalName();
        $isAllowed = in_array($file->getClientOriginalExtension(), $allowedExtension);

        if (! $isAllowed) {
            backWithMsg('The file '.$fileName.' type is not allowed!', 'warning');
        }

        Storage::putFileAs($path, $file, $fileName);

        backWithMsg('The file '.$fileName.' has been uploaded!');
    }

    public function getDeleteDirectory($dir)
    {
        $dir = base64_decode($dir);
        Storage::deleteDirectory($dir);

        backWithMsg('The directory has been deleted!');
    }

    public function getDeleteFile($file)
    {
        $file = base64_decode($file);
        Storage::delete($file);

        backWithMsg('The file has been deleted!');
    }
}
