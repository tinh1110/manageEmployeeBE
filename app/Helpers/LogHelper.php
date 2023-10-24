<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

class LogHelper
{
    /**
     * Log exception with trace info about class, method
     */
    public static function writeErrorLog(\Exception $e, string $level = 'error'): void
    {
        $exceptionInfo = '';
        $traceInfo = $e->getTrace()[0] ?? null;
        if (!empty($traceInfo)) {
            $classTrace = $traceInfo['class'] ?? '';
            $functionTrace = $traceInfo['function'] ?? '';
            $fileTrace = $traceInfo['file'] ?? '';
            $lineTrace = $traceInfo['line'] ?? '';
            $line = $e->getLine();
            $message = $e->getMessage();
            $exceptionInfo = "[{$classTrace}->{$functionTrace}:{$line}] {$message} call from {$fileTrace}({$lineTrace})";

            if (!empty($exceptionInfo)) {
                LogHelper::logMessage($exceptionInfo, $level);
            }
        }
    }

    /**
     * Write log to file.
     */
    public static function logMessage(string $message, string $level = 'error'): void
    {
        if (empty($level)) {
            $level = 'error';
        }

        Log::$level($message);
    }

}
