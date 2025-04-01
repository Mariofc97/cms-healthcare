<?php

declare(strict_types=1);

namespace audit;

use Exception;

enum Outcome
{
    case SUCCESS;
    case ERROR;
}

class AuditGenerator
{
    static function genarateLog(string $username, string $action, Outcome $outcome)
    {
        $timestamp = date('Y-m-d H:i:s');
        $remoteIP = $_SERVER['REMOTE_ADDR'];
        $remotePort = $_SERVER['REMOTE_PORT'];
        $outcome = ($outcome === Outcome::SUCCESS) ? "Success" : "Error";
        $logEntry = "[$timestamp] IP: $remoteIP, Port: $remotePort\nAction: $action, Outcome: $outcome\n";
        $targetDir = "../data/$username/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir);
        }
        $logFile = $targetDir . 'audit.log';
        if (@file_put_contents($logFile, $logEntry, FILE_APPEND) === false) {
            throw new Exception("Error on updating audit log", 500);
        }
    }
}

try {
    AuditGenerator::genarateLog("me", "Change password", Outcome::ERROR);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    http_response_code($e->getCode());
}
