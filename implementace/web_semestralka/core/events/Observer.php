<?php

namespace App\Core\Events;

interface Observer{
    public function update(string $event, array $data): void;
}