<?php


namespace Modules\Browser\Pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Modules\Base\Services\Components\Base\Alert;
use Modules\Base\Services\Components\Base\Component;
use Modules\Base\Services\Components\Base\Render;
use Modules\Base\Services\Resource\Page;
use Modules\Browser\Pages\Actions\UploadAction;
use Modules\Browser\Pages\Modals\UploadModal;

class BrowserPage extends Page
{
    public ?string $path = "browser";
    public ?string $group = "Tools";
    public ?string $icon = "bx bxs-folder";
    public ?bool $page = true;

    public function index(Request $request)
    {
        if ($request->has('folder_path')) {
            $request->validate([
                "folder_path" => "required",
                "folder_name" => "required",
                "type" => "required",
            ]);

            $root = $request->get('folder_path');
            $name = $request->get('folder_name');
            $type = $request->get('type');
        } else if ($request->has('file_path')) {
            $name = $request->get('file_name');
            $setFilePath = $request->get('file_path');
            $root = str_replace('/' . $name, '', $request->get('file_path'));
        } else {
            $root = base_path();
            $name = base_path();
            $type = "home";
        }

        if ($request->has('file_path')) {
            $getFile = File::get($setFilePath);

            $folders =  File::directories($root);
            $files =  File::files($root);
            $foldersArray = [];
            $filesArray = [];

            foreach ($folders as $folder) {
                $foldersArray[] = [
                    "path" => $folder,
                    "name" => str_replace($root . '/', '', $folder),
                ];
            }

            foreach ($files as $file) {
                $filesArray[] = [
                    "path" => $file->getRealPath(),
                    "name" => str_replace($root . '/', '', $file),
                ];
            }

            $exploadName = explode('/', $root);
            $count = count($exploadName);
            $setName = $exploadName[$count - 1];

            $ex = File::extension($setFilePath);

            if ($ex === 'webp' || $ex === 'jpg' || $ex === 'png' || $ex === 'svg' || $ex === 'jpeg' || $ex === 'ico' ||  $ex === 'gif' || $ex === 'tif') {
                $imagBase64 = base64_encode($getFile);
                $getFile = $imagBase64;
            }

            return Render::make('Browser')->module('Browser')->data([
                "folders" => $foldersArray,
                "files" => $filesArray,
                "back_path" => $root,
                "back_name" => $setName,
                "current_path" => $root,
                "file" => $getFile,
                "ex" => $ex,
                "path" => $setFilePath,
                "history" => [
                    "folders" => $foldersArray,
                    "files" => $filesArray,
                    "back_path" => $root,
                    "back_name" => $setName,
                    "current_path" => $root,
                    "file" => $getFile,
                    "ex" => $ex,
                    "path" => $setFilePath
                ],
                "roles" => [
                    "view" => $this->canView,
                    "viewAny" => $this->canViewAny,
                    "edit" => $this->canEdit,
                    "create" => $this->canCreate,
                    "delete" => $this->canDelete,
                    "deleteAny" => $this->canDeleteAny,
                ],
                "render" => [
                    "components" => $this->components(),
                    "lang" => $this->loadTranslations(),
                ],
                "list" => [
                    "url" => $this->table
                ]
            ])->render();
        } elseif ($request->has('content')) {
            $checkIfFileEx = File::exists($request->get('path'));
            if ($checkIfFileEx) {
                File::put($request->get('path'), $request->get('content'));

                session(["message" => __('File Has Been Updated Success')]);
                return back();
            }
        } else {
            $folders =  File::directories($root);
            $files =  File::files($root);
            $foldersArray = [];
            $filesArray = [];

            foreach ($folders as $folder) {
                $foldersArray[] = [
                    "path" => $folder,
                    "name" => str_replace($root . '/', '', $folder),
                ];
            }

            foreach ($files as $file) {
                $ex = File::extension($file);
                $filesArray[] = [
                    "path" => $file->getRealPath(),
                    "name" => str_replace($root . '/', '', $file),
                    "ex" => $ex
                ];
            }

            if ($root == base_path()) {
                $filesArray[] = [
                    "path" => base_path('.env'),
                    "name" => ".env",
                ];
            }

            $exploadName = explode('/', $root);
            $count = count($exploadName);
            $setName = $exploadName[$count - 2];

            return Render::make('Browser')->module('Browser')->data([
                "folders" => $foldersArray,
                "files" => $filesArray,
                "back_path" => str_replace('/' . $name, '', $root),
                "back_name" => $setName,
                "current_path" => $root,
                "file" => false,
                "ex" => false,
                "path" => false,
                "history" => [
                    "folders" => $foldersArray,
                    "files" => $filesArray,
                    "back_path" => str_replace('/' . $name, '', $root),
                    "back_name" => $setName,
                    "current_path" => $root,
                    "file" => false,
                    "ex" => false,
                    "path" => false
                ],
                "roles" => [
                    "view" => $this->canView,
                    "viewAny" => $this->canViewAny,
                    "edit" => $this->canEdit,
                    "create" => $this->canCreate,
                    "delete" => $this->canDelete,
                    "deleteAny" => $this->canDeleteAny,
                ],
                "render" => [
                    "components" => $this->components(),
                    "lang" => $this->loadTranslations(),
                ],
                "list" => [
                    "url" => $this->table
                ]
            ])->render();
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            "media" => "required|array",
            "path" => "required|string"
        ]);

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $file->move($request->get('path'), $file->getClientOriginalName());
            }
        }

        return Alert::make(__('Your File Has Been Uploaded Success'))->fire();
    }

    public function components(): array
    {
        return [
            Component::make(UploadAction::class)->action(),
            Component::make(UploadModal::class)->modal(),
        ];
    }
}
