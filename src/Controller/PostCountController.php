<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;

class PostCountController
{
    private PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }


    /**
     * retourne le nombre d'article qu'il y a dans la base de donnée
     */
    public function __invoke(Request $request) : int 
    {
        $onlineQuery = $request->get('online');

        $conditions = [];

        if ($onlineQuery != null) {
            $conditions = [ 'online' => $onlineQuery === '1' ? true : false ];
        }

        return $this->postRepository->count($conditions);
    }
}