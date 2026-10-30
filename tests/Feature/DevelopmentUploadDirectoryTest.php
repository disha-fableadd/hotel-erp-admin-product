<?php

namespace Tests\Feature;

use App\Console\Commands\ServeCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Tests\TestCase;

class DevelopmentUploadDirectoryTest extends TestCase
{
    public function test_development_server_configures_uploads_before_php_starts(): void
    {
        $command = app(\Illuminate\Contracts\Console\Kernel::class)->all()['serve'];
        $this->assertInstanceOf(ServeCommand::class, $command);
        $command->setLaravel($this->app);

        $input = new ArrayInput(['--host' => '127.0.0.1', '--port' => '8000'], $command->getDefinition());
        $inputProperty = new \ReflectionProperty(\Illuminate\Console\Command::class, 'input');
        $inputProperty->setValue($command, $input);
        $method = new \ReflectionMethod(ServeCommand::class, 'serverCommand');
        $arguments = $method->invoke($command);

        $this->assertSame('-d', $arguments[1]);
        $this->assertSame('upload_tmp_dir='.storage_path('framework/uploads'), $arguments[2]);
        $this->assertSame('-S', $arguments[3]);
        $this->assertTrue(is_writable(storage_path('framework/uploads')));
    }
}
