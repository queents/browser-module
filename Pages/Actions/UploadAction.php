<?php

namespace Modules\Browser\Pages\Actions;

use Modules\Base\Services\Components\Actions;

class UploadAction extends Actions
{
    public function setup(): void
    {
        $this->name("upload");
        $this->label(__("Upload"));
        $this->type("success");
        $this->icon("bx bx-upload");
        $this->modal("upload");
    }
}
