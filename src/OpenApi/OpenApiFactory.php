<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;

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
        $openApi->getPaths()->addPath('/ping', new PathItem(null, 'ping', null, new Operation('ping-id', [], [], 'Répond')));



        $schemas = $openApi->getComponents()->getSecuritySchemes();
        $schemas["cookieAuth"] = new \ArrayObject([
            "type"  => "apiKey",
            "in"    => "cookie",
            "name"  => "PHPSESSID"
        ]);

        $openApi = $openApi->withSecurity(["cookieAuth" => []]);
        // dd($openApi);

        return $openApi;
    }
}