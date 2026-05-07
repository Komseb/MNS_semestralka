<?php

namespace App\Core\Sorting;

class NewestSortingStrategy implements SortingStrategy {
    public function getSortedQuerry(): string {
        return " ORDER BY p.created_at DESC ";
    }
}
 