<?php

namespace App\Core\Sorting;

class TopSortingStrategy implements SortingStrategy {
    public function getSortedQuerry(): string {
        return " ORDER BY vote_score DESC, p.created_at DESC ";
    }
}
 
