<?php

declare(strict_types=1);

interface LoggerInterface
{
    public function send(LogMessage $message): void;

    public function addTarget(LogTargetInterface $target): void;

    public function removeTarget(LogTargetInterface $target): void;
}

interface LogTargetInterface
{
    public function write(LogMessage $message): void;
}

enum LogLevel: string
{
    case INFO = 'INFO';
    case WARNING = 'WARNING';
    case ERROR = 'ERROR';
    case DEBUG = 'DEBUG';
}

readonly class LogMessage
{
    public function __construct(
        public LogLevel $level,
        public string $message,
        public DateTime $timestamp
    ) {}
}

class Logger implements LoggerInterface
{
    private array $targets = [];
    public function send(LogMessage $message): void
    {
        foreach ($this->targets as $target) {
            $target->write($message);
        }
    }

    public function addTarget(LogTargetInterface $target): void
    {
        $this->targets[spl_object_hash($target)] = $target;
    }

    public function removeTarget(LogTargetInterface $target): void
    {
        unset($this->targets[spl_object_hash($target)]);
    }
}

class FileLogger implements LogTargetInterface
{
    public function write(LogMessage $message): void
    {
        $time = $message->timestamp->format('Y-m-d H:i:s');
        echo "[{$time}] [{$message->level->value}] {$message->message}. This is the log message from File <br>";
    }
}

class ConsoleLogger implements LogTargetInterface
{
    public function write(LogMessage $message): void
    {
        $time = $message->timestamp->format('Y-m-d H:i:s');
        echo "[{$time}] [{$message->level->value}] {$message->message}. This is the log message from Console <br>";
    }
}

$logger = new Logger();
$fileLogger = new FileLogger();
$consoleLogger = new ConsoleLogger();
$logger->addTarget($fileLogger);
$logger->addTarget($consoleLogger);
$message = new LogMessage(
    level: LogLevel::INFO,
    message: 'The logging system is now live!',
    timestamp: new DateTime()
);
$logger->send($message);
