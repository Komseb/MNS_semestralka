<?php


namespace App\Controllers;

use App\Core\BaseController;

class ErrorController extends BaseController
{
    //render 404 error page for non-existent routes
    public function handle(array $params = []): void
    {
        //set proper http status code for search engines
        http_response_code(404);
        $this->meta = [
            'title' => 'Error 404',
            'keywords' => 'error, 404, not found',
            'description' => 'the page you are looking for does not exist',
        ];
        $this->render('error/404.twig');
    }
}
