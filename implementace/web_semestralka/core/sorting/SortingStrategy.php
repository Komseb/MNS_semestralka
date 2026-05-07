<?php

namespace App\Core\Sorting;

interface SortingStrategy {
    public function getSortedQuerry(): string;
}