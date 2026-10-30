<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Illuminate\Support\Facades\File;

class ServeCommand extends BaseServeCommand
{
    protected function serverCommand()
    {
        // PHP handles multipart uploads before Laravel boots. Configure this
        // in the child process, since ini_set() in a controller is too late.
        $uploadDirectory = storage_path('framework/uploads');
        File::ensureDirectoryExists($uploadDirectory);

        $command = parent::serverCommand();
        array_splice($command, 1, 0, [
            '-d', 'upload_tmp_dir='.$uploadDirectory,
        ]);

        return $command;
    }
}
