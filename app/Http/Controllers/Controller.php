<?php

namespace App\Http\Controllers;

use App\Models\WebPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app(ViewFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        $user = Auth::user();

        if ($user && !$user->hasRole('super-admin')) {
            $webpage = WebPage::where('file_path', $view)->first();
            if (!$webpage || !$user->hasPermissionTo($webpage->permission)) {
                abort(403);
            }
        }

        return $factory->make($view, $data, $mergeData)
            ->with('blade_user', Auth::user())
            ->with('blade_view', $view);
    }

    /**
     * Upload File
     *
     * @param object $file File to be upload
     * @param string $folder Target folder where files are stored
     * @return string File directory is after upload
     */
    public function uploadFile($file, $folder)
    {
        $file_name = md5(time() . rand()) . '.' . $file->getClientOriginalExtension();
        $file->storeAs('uploads/' . $folder . '/', $file_name);
        return 'uploads/' . $folder . '/' . $file_name;
    }

    /**
     * Show File
     *
     * @param object $request Request object
     * @param string $filename File name
     * @return string File directory is after upload
     */
    public function viewFile(Request $request, $filename)
    {
        $path = urldecode($request->path);

        $file = Storage::disk()->get($path . '/' . $filename);
        $type = Storage::disk()->mimeType($path . '/' . $filename);

        if (!$file) {
            return abort(404);
        }

        if ($request->type == 'private' && !Auth::user()) {
            return abort(404);
        } else if ($request->type == 'public') {
            if (!$request->has('temp_key')) {
                return abort(404);
            }

            try {
                $temp_key = decrypt($request->temp_key);
                if ($temp_key != md5($path)) {
                    return abort(404);
                }
            } catch (\Exception $e) {
                return abort(404);
            }
        }

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

    /**
     * Download File
     *
     * @param object $request Request object
     * @param string $filename File name
     * @return string File directory is after upload
     */
    public function downloadFile(Request $request, $filename)
    {
        $path = urldecode($request->path);

        $file = Storage::disk()->get($path . '/' . $filename);

        if (!$file) {
            return abort(404);
        }

        if ($request->type == 'private' && !Auth::user()) {
            return abort(404);
        } else if ($request->type == 'public') {
            if (!$request->has('temp_key')) {
                return abort(404);
            }

            try {
                $temp_key = decrypt($request->temp_key);
                if ($temp_key != md5($path)) {
                    return abort(404);
                }
            } catch (\Exception $e) {
                return abort(404);
            }
        }

        return Storage::disk()->download($path . '/' . $filename);
    }
}
