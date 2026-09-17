<?php

use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Console\ServeCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

function serverOutputProbe(): ServeCommand
{
    $command = new class extends ServeCommand
    {
        public function feed(string $stream, string $chunk): void
        {
            ($this->handleProcessOutput())($stream, $chunk);
        }

        public function acceptedPorts(): array
        {
            return array_keys($this->requestsPool ?? []);
        }
    };
    $command->setLaravel(app());
    $command->setOutput(new OutputStyle(new ArrayInput([]), new BufferedOutput));

    return $command;
}

test('interleaving PHP stderr and router stdout reproduces the reported truncated timestamp', function () {
    $command = serverOutputProbe();
    $command->feed('err', '[Thu Sep 17 14:27:37 2');
    $command->feed('out', "[Thu Sep 17 14:27:37 2026] 127.0.0.1:54321 [GET] URI: /up\n");
    expect(fn () => $command->feed('err', "026] 127.0.0.1:54321 Accepted\n"))->toThrow(ErrorException::class);
});

test('single stream request logs tolerate split reads without losing the timestamp', function () {
    $command = serverOutputProbe();
    $command->feed('err', '[Thu Sep 17 14:27:37 2');
    $command->feed('err', "026] 127.0.0.1:54321 Accepted\n");
    $command->feed('err', "[Thu Sep 17 14:27:37 2026] 127.0.0.1:54321 [GET] URI: /up\n");
    expect($command->acceptedPorts())->toBe([54321]);
});

test('the project router sends its request log to stderr', function () {
    $script = '$_SERVER["REQUEST_URI"] = "/up"; $_SERVER["REQUEST_METHOD"] = "GET"; $_SERVER["REMOTE_ADDR"] = "127.0.0.1"; $_SERVER["REMOTE_PORT"] = "54321"; $_SERVER["SCRIPT_NAME"] = "/index.php"; $_SERVER["HTTP_HOST"] = "localhost"; require "server.php";';
    $process = new Process([PHP_BINARY, '-r', $script], base_path());
    $process->setTimeout(15);
    $process->mustRun();
    expect($process->getErrorOutput())->toContain('127.0.0.1:54321 [GET] URI: /up')
        ->and($process->getOutput())->toContain('Application up')
        ->and($process->getOutput())->not->toContain('URI: /up');
});
