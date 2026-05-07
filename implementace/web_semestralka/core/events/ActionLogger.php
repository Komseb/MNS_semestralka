<?php

namespace App\Core\Events;

class ActionLogger implements Observer {
    public function update(string $event, array $data): void {
        $logEntry = date("Y-m-d H:i:s") . " - EVENT: $event - DATA: " . json_encode($data) . PHP_EOL;
        file_put_contents(__DIR__ . '/../../logs/app.log', $logEntry, FILE_APPEND);
    }
}