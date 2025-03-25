<?php

namespace Tourze\Symfony\AsyncMessage\MessageHandler;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Tourze\Symfony\AsyncMessage\Message\RunCommandMessage;

/**
 * @see https://symfony.com/doc/current/console/command_in_controller.html
 */
class RunCommandHandler
{
    private Application $application;

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->application = new Application($this->kernel);
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);
    }

    public function __invoke(RunCommandMessage $message): void
    {
        $this->logger?->info('准备执行命令', [
            'command' => $message->getCommand(),
            'options' => $message->getOptions(),
        ]);

        $input = new ArrayInput([
            'command' => $message->getCommand(),
            ...$message->getOptions(),
            // (optional) define the value of command arguments
            // 'fooArgument' => 'barValue',
            // (optional) pass options to the command
            // '--bar' => 'fooValue',
            // (optional) pass options without value
            // '--baz' => true,
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new NullOutput();

        try {
            $command = $this->application->get($message->getCommand());
            $command->run($input, $output);
        } catch (RecoverableMessageHandlingException $exception) {
            // 内部有要求重试的话，我们这里直接抛出
            throw $exception;
        } catch (\Throwable $exception) {
            $this->logger?->error('异步执行命令时发生异常:' . $exception->getMessage(), [
                'command' => $message->getCommand(),
                'options' => $message->getOptions(),
                'exception' => $exception,
            ]);
        }
    }
}
