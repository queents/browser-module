<?php

namespace Modules\Browser\Pages\Modals;

use Modules\Base\Services\Components\Base\Action;
use Modules\Base\Services\Components\Modals;
use Modules\Base\Services\Rows\Media;
use Modules\Base\Services\Rows\Text;

class UploadModal extends Modals
{
    public function setup(): void
    {
        $this->name("upload");
        $this->label(__("Upload Your File To Current Path"));
        $this->type("success");
        $this->icon("bx bx-upload");
        $this->rows([
            Media::make('media')
                ->label(__("Media File"))
                ->required(),
            Text::make('path')->label(__('Input File Path'))->required(),
        ]);
        $this->buttons([
            Action::make('upload')
                ->label(__("Upload"))
                ->type("success")
                ->icon("bx bx-upload")
                ->action("browser.upload"),
        ]);
    }
}
