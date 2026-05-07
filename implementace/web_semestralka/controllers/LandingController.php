<?php

namespace App\Controllers;

use App\Core\BaseController;

class LandingController extends BaseController
{
    //render public homepage for unauthenticated visitors
    public function handle(array $params = []): void
    {
        //configure landing page seo metadata
        $this->meta = [
            'title' => 'Ziggid - Komunitní platforma pro sdílení a diskuzi',
            'keywords' => 'forum, diskuze, komunita, příspěvky, témata',
            'description' => 'Ziggid je moderní komunitní platforma pro sdílení příspěvků, diskuzi a propojení lidí se společnými zájmy.',
        ];

        //show marketing page with features and call-to-action
        $this->render('landing/index.twig');
    }
}
