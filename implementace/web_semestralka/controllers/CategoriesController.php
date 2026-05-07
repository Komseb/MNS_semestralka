<?php


namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Category;

class CategoriesController extends BaseController
{
    //render page showing all categories with post counts and top posts
    public function handle(array $params = []): void
    {
        //configure seo metadata
        $this->meta = [
            'title' => 'Témata - Ziggid',
            'keywords' => 'témata, kategorie, diskuze, komunita',
            'description' => 'Prohlížej témata a kategorie na platformě Ziggid',
        ];

        //load all categories including post count and top voted post
        $categories = Category::getAllWithStats();

        //display categories grid to user
        $this->render('categories/index.twig', [
            'categories' => $categories
        ]);
    }
}
