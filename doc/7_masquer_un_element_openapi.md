# Masquer un élément dans la documentation openApi

api-platform/core : https://github.com/api-platform/core

Ici, on va désactiver le point d'entrer `/api/categories/{id}`, pour s'exercer


### Dans Category.php

- pour desactiver un point d'entrer

- On importe `use ApiPlatform\Core\Action\NotFoundAction;`, qui existe déjà dans apiPlateform :  https://github.com/api-platform/core/blob/main/src/Action/NotFoundAction.php

- Dans `"get"` qui est dans `itemOperations`

    - On met `NotFoundAction;` en tant que `"controller"` de `"get"` 

    - `"read"=false,`, on désactive la lecture, pour ne pas faire de requêtes pour cette opération

    - `"output"=false,`, on désactive l'affichage d'un résultat

    - Dans `"openapi_context"` , on va écrire `"hidden"` dans la propriété `"summary"`, ce la nous permettra de traiter les chemins qui on un `"summary"` qui a le mot `"hidden"`

- Puis on va créer une classe nommé `OpenApiFactory` et on va faire en sorte qu'il soit appellé à la place de l'autre classe  `OpenApiFactory` de ApiPlateform qui existe déjà dans ce chemin : `vendor\api-platform\core\src\OpenApi\Factory\OpenApiFactory.php` ou https://github.com/api-platform/core/blob/main/src/OpenApi/Factory/OpenApiFactory.php

Dans `Category.php`


    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use App\Repository\CategoryRepository;
    use ApiPlatform\Core\Action\NotFoundAction;
    use Doctrine\Common\Collections\Collection;
    use ApiPlatform\Core\Annotation\ApiResource;
    use Doctrine\Common\Collections\ArrayCollection;
    use Symfony\Component\Serializer\Annotation\Groups;
    use Symfony\Component\Validator\Constraints as Assert;

    /**
    * @ORM\Entity(repositoryClass=CategoryRepository::class)
    * 
    * @ApiResource(
    * 
    *      normalizationContext={
    *          "openapi_definition_name"="List_categories"
    *      },
    * 
    *      denormalizationContext={
    *          "openapi_definition_name"="write_one_category"
    *      },
    * 
    *      itemOperations={
    *          "put",
    *          "delete",
    *          "get"={
    *              "normalization_context"={
    *                  "openapi_definition_name"="read_one_category"
    *               },
    *              "controller"=NotFoundAction::class,
    *              "read"=false,
    *              "output"=false,
    *              "openapi_context"={
    *                  "summary"="hidden"
    *              }
    *          }
    *      }
    * )
    */
    class Category



### Dans OpenApi/OpenApiFactory.php

- La classe `OpenApiFactory`, on a créer va implémenter la classe `OpenApiFactoryInterface` https://github.com/api-platform/core/blob/main/src/OpenApi/Factory/OpenApiFactoryInterface.php

- Notre `OpenApiFactory` va décoré le `OpenApiFactory` de base https://github.com/api-platform/core/blob/main/src/OpenApi/Factory/OpenApiFactory.php

- On cree un constructeur et on met `OpenApiFactoryInterface` dans une propriété nommé `decorated`

-  `OpenApiFactoryInterface` a besoin de `public function __invoke(array $context = []): OpenApi` pour bien fonctionner 

- `$openApi = $this->decorated->__invoke($context);`, on appel la méthode parente, dans `__invoke($context)` on passe les paramètre de notre context

- `$openApi;` on retourne OpenApi

- On va dans `config/services.yaml`, on va dire au système que lorsqu'on veut utiliser `OpenApiFactory`, il faut utiliser le notre et décorer le `OpenApiFactory` de base

- Dans `foreach`, on veut traiter un tableaux de chemin exemple

    ^ array:6 [▼
        "/api/categories/{id}" => ApiPlatform\Core\OpenApi\Model\PathItem {#1861 ▶}
        "/api/categories" => ApiPlatform\Core\OpenApi\Model\PathItem {#1904 ▶}
        "/api/posts/{id}" => ApiPlatform\Core\OpenApi\Model\PathItem {#2182 ▶}
        "/api/posts{id}/publish" => ApiPlatform\Core\OpenApi\Model\PathItem {#2201 ▶}
        "/api/posts" => ApiPlatform\Core\OpenApi\Model\PathItem {#2455 ▶}
        "/api/posts/count" => ApiPlatform\Core\OpenApi\Model\PathItem {#2442 ▶}
    ]

- `$pathURI` = `"/api/categories/{id}"`

- `$pathItem` = `ApiPlatform\Core\OpenApi\Model\PathItem {#1861 ▶}`

- Dans chaque chemin, il y a en clé une `URI` ce sera `$pathURI` et en valeur un objet `PathItem` ce sera `$pathItem`

    - `if($pathItem->getGet() && $pathItem->getGet()->getSummary() === 'hidden')` si le chemin existe `$pathItem->getGet()` et que sa propriété `summary` est égale a `hidden`, on continu le traitement

    - `$openApi->getPaths()` on récupère l'objet chemin, on lui demande de rajouter un chemin qui correcpondra avec celui qu'on a déjà avec `->addPath($pathURI, $pathItem->withGet(null))`

    - On lui change le get qui sera null, `$pathItem->withGet(null)`

et voila , normalement ça a Masquer un élément dans la documentation openApi

#### Exemple pour ajouter un chemin 

- $openApi->getPaths()->addPath('/ping', new PathItem(null, 'ping', null, new Operation('ping-id', [], [], 'Répond')));

- `PathItem` : https://github.com/api-platform/core/blob/main/src/OpenApi/Model/PathItem.php

- `Operation` : https://github.com/api-platform/core/blob/main/src/OpenApi/Model/Operation.php

Dans `OpenApi/OpenApiFactory.php`

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

            return $openApi;
        }
    }



### Dans config/services.yaml

- `App\OpenApi\OpenApiFactory:`, on précise le nom de notre classe

- `decorates: 'api_platform.openapi.factory'` , on précise quel classe, on va décorer, on met le nom du service `api_platform.openapi.factory`

- `arguments: ['@App\OpenApi\OpenApiFactory.inner']`, on met comme argument notre classe

- `autoconfigure: false`, car on ne veut pas le brancher avec la détection de ce que c'est

Dans `config/services.yaml`

    parameters:

    services:
        # default configuration for services in *this* file
        _defaults:
            autowire: true      # Automatically injects dependencies in your services.
            autoconfigure: true # Automatically registers your services as commands, event subscribers, etc.

        # makes classes in src/ available to be used as services
        # this creates a service per class whose id is the fully-qualified class name
        App\:
            resource: '../src/'
            exclude:
                - '../src/DependencyInjection/'
                - '../src/Entity/'
                - '../src/Kernel.php'
                - '../src/Tests/'

        # controllers are imported separately to make sure services can be injected
        # as action arguments even if you don't extend any base controller class
        App\Controller\:
            resource: '../src/Controller/'
            tags: ['controller.service_arguments']

        # add more service definitions when explicit configuration is needed
        # please note that last definitions always *replace* previous ones

        App\OpenApi\OpenApiFactory:
            decorates: 'api_platform.openapi.factory'
            arguments: ['@App\OpenApi\OpenApiFactory.inner']
            autoconfigure: false