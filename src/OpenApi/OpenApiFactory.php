<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\Model\RequestBody;

class OpenApiFactory implements OpenApiFactoryInterface 
{
    private OpenApiFactoryInterface $decorated;

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        // supprimer de OpenApi, un chemin qui contient le mot 'hidden' dans la propriété summary
        foreach ($openApi->getPaths()->getPaths() as $pathURI => $pathItem) {

            if($pathItem->getGet() && $pathItem->getGet()->getSummary() === 'hidden'){

                $openApi->getPaths()->addPath($pathURI, $pathItem->withGet(null)); 
            }
            
        }

        //exemple de création d'une nouvelle route qui sera afficher dans OpenApi
        // $openApi->getPaths()->addPath('/ping', new PathItem(null, 'ping', null, new Operation('ping-id', [], [], 'Répond')));



        // création du cookie dans openapi de apiplateform
        $schemas = $openApi->getComponents()->getSecuritySchemes();
        $schemas["cookieAuth"] = new \ArrayObject([
            "type"  => "apiKey",
            "in"    => "cookie",
            "name"  => "PHPSESSID"
        ]);

        // affiche un cadenas dans openapi de apiplateform mais ça ne fonctionne pas avec SF4
        // $openApi = $openApi->withSecurity(["cookieAuth" => []]);
        
        // partie qui sera dans la RequestBody, pour qu'un user ce connecte
        $schemas = $openApi->getComponents()->getSchemas();
        $schemas["Credentials"] = new \ArrayObject([
            "type"  => "object",
            "properties" => [
                "username" => [
                    'type' => 'string',
                    'example' => 'test@gmail.com',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'test'
                ]
            ]
        ]);

        /**
         * représente toute la structure pour q'un user ce connecte via cette route /api/login
         */ 
        $pathItem = new PathItem(
            null, 
            'sommaire 1', 
            'description 1', 
            null,
            null,

            new Operation(
                'apiUser-id',
                ['User'],
                [

                    '200' => [
                        'description' => 'Utilisateur connecté',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/User-List_users'
                                ]
                            ]
                        ]
                    ]
                ],
                'Connexion d\'un utilisateur', 
                'L\'utilisateur va tenter de ce connecter ici',
                null,
                [],

                new RequestBody(
                    'Un User tente de ce connecter',
                    new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials'
                            ]
                        ]
                    ])
                )

            ),
        );

        $openApi->getPaths()->addPath('/api/login', $pathItem);

        return $openApi;
    }
}