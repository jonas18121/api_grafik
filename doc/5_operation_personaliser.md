# Opération personnalisé

Ici on va voir comment créer de nouvelle opération pour nos articles

On va découpé les modification en plusieurs étapes

## Etape 1 : Création du controlleur personnalisé

### Dans Post.php

- Pour commencer on va créer, en ligne de commande un champ de type boolean nommé `$online` pour indiquer si l'article est en ligne ou pas

        /**
        * @ORM\Column(type="boolean", options={"default": "0"})
        * @Groups({"read:Post:collection"}) 
        */
        private $online = false;

- Après la création de `$online` :

    - On va ajouter `false` par défaut, pour chaque création de nouvel article `private $online = false;`

    - On ajoute en annotation `options={"default": "0"}`, pour qu'au moment ou on va faire la migration dans la base de données, les articles déjà existant reçoivent la colonne `online` avec comme valeur `zéro` (égale à `false`) par défaut, pour éviter les conflits et de devoir supprimer les articles déjà existant

    - On le met dans un groupe `@Groups({"read:Post:collection"}) `

- Puis execute les commandent en CLI pour effectué les migrations dans la BDD 

#### Le controlleur personnalisé

- Dans `itemOperations` on va créer notre opération qui concernera un item en particulier à la fois

        "publish"={
        *    "method"="POST",
        *    "path"="/posts{id}/publish",
        *    "controller"=PostPublishController::class
        *  }

    - L'opération s'appellera `"publish"`
    
    - Dans `"method"`, on passe la méthode à utiliser, ici ce sera `"POST"` 

    - Dans `"path"`, on passe le chemin qui va nous permettre de faire cette opération, ici ce sera `"/posts{id}/publish"` 

    - Dans `"controller"`, on passe le controlleur à appeler, ici ce sera `PostPublishController`, on va créer ce controlleur dans le dossier `Controller`, puis on importe le controller `use App\Controller\PostPublishController;`  


Dans `Post.php`


    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use App\Repository\PostRepository;
    use App\Controller\PostPublishController;
    use ApiPlatform\Core\Annotation\ApiFilter;
    use ApiPlatform\Core\Annotation\ApiResource;
    use Symfony\Component\Serializer\Annotation\Groups;
    use Symfony\Component\Validator\Constraints as Assert;
    use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;


    // curl -X GET "http://127.0.0.1:8000/api/posts?page=1" -H "accept: application/json"


    /**
    * @ORM\Entity(repositoryClass=PostRepository::class)
    * 
    * @ApiResource(
    *      
    *      normalizationContext={"groups"={"read:Post:collection"}},
    *      denormalizationContext={"groups"={"write:Post:item"}},
    *      attributes={
    *          "pagination_items_per_page"=2,
    *          "maximum_items_per_page"=2,
    *          "pagination_client_items_per_page"=true
    *      },
    *      itemOperations={
    *          "put",
    *          "delete",
    *          "get"={
    *              "normalization_context"={"groups"={"read:Post:collection", "read:Post:item", "read:Post:and:Category:item"}}
    *          },
    *          "publish"={
    *              "method"="POST",
    *              "path"="/posts{id}/publish",
    *              "controller"=PostPublishController::class
    *          }
    *      }
    * )
    * 
    * @ApiFilter(SearchFilter::class, properties={"id": "exact", "title": "partial"})
    */
    class Post
    {
        /**
        * @ORM\Id
        * @ORM\GeneratedValue
        * @ORM\Column(type="integer")
        * @Groups({"read:Post:collection"}) 
        */
        private $id;

        /**
        * @ORM\Column(type="string", length=255)
        * @Groups({"read:Post:collection", "write:Post:item"}) 
        * @Assert\Length(min = 5, minMessage = "Le titre de votre article doit comporter au minimum 5 caractères" )
        */
        private $title;

        /**
        * @ORM\Column(type="string", length=255)
        * @Groups({"read:Post:collection", "write:Post:item"}) 
        */
        private $slug;

        /**
        * @ORM\Column(type="text")
        * @Groups({"read:Post:item", "write:Post:item"}) 
        */
        private $content;

        /**
        * @ORM\Column(type="datetime")
        * @Groups({"read:Post:item"}) 
        */
        private $createdAt;

        /**
        * @ORM\Column(type="datetime", nullable=true)
        */
        private $updatedAt;

        /**
        * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="posts", cascade={"persist"})
        * @Groups({"read:Post:item", "write:Post:item"}) 
        * @Assert\Valid()
        */
        private $category;

        /**
        * @ORM\Column(type="boolean", options={"default": "0"})
        * @Groups({"read:Post:collection"}) 
        */
        private $online = false;




### Dans PostPublishController.php

- On créer notre controlleur manuellement

- la fonction ` __invoke()` sera appeller automatiquement lorsqu'on va utiliser le controlleur `PostPublishController`

- ApiPlateform va automatiquement mettre dans l'argument `$data`, les données de l'article qui sera traiter, grace au `DataProvider`  

    - Si dans `"publish"` de `Post.php` on met `"read"=false`, l'argument `$data` ne recevra pas de données d'aucun article, ApiPlateform va croire que l'on crée un nouvel article en entier, alors que l'on veut seulment modifier `online` d'un article déjà existant

                "publish"={
            *     "method"="POST",
            *     "path"="/posts{id}/publish",
            *     "controller"=PostPublishController::class,
            *     "read"=false
            *   }

- Ici on va juste modifier la propriété `online`  de l'article qui est traiter à `true`, `$data->setOnline(true);`

- Puis On retourne l'article

- ApiPlateform va automatiquement persister la modification dans la bdd

    - Si dans `"publish"` de `Post.php` on met `"write"=false`. On n'aura plus la partie persistance automatique de ApiPlateform et il faudra rajouter manuellement dans le controller toute la logique pour persiter dans la bdd avec `->persist()` et `->flush()`, comme dans Symfony standard 

                "publish"={
            *     "method"="POST",
            *     "path"="/posts{id}/publish",
            *     "controller"=PostPublishController::class,
            *     "write"=false
            *   }


Dans `PostPublishController.php`

    namespace App\Controller;

    use App\Entity\Post;

    class PostPublishController
    {
        public function __invoke(Post $data): Post
        {
            $data->setOnline(true);
            return $data;
        }
    }



## Etape 2 : Configurer OpenApi dans apiPlateform

Site openApi : https://oai.github.io/Documentation/start-here.html

Site ApiPlateform : https://api-platform.com/docs/core/openapi/

OpenApi nous permet de documenter le fonctionnement d'une api

L'outil `SwaggerUi` qu'on a par defaut avec apiPlateform nous permet de lire depuis son interface `OpenApi`

On peut exporter des fichiers de description depuis le `SwaggerUi` en format yaml de pour `OpenApi`

Si on execute la commande ci-dessous, on verra les différentes commandes pour exporter un fichier

    > php bin/console api

Si on execute la commande ci-dessous, on verra des données en format JSON qui contient la définition de notre API

    > php bin/console api:openapi:export

Si on execute la commande ci-dessous, on verra des données en format yaml qui contient la définition de notre API

    > php bin/console api:openapi:export --yaml


### Dans Post.php

- On va mettre une petite description a notre api via OpenApi

- Dans `"publish"`, on commence par écrire `"openapi_context"` et dedans on va lui passé un objet qui contient des spécifités de notre api

    - `"summary"` permet d'écrire un petit résumé 

    - on met une `"requestBody"` et dedans on met ce qu'on attend, en fonction des différents format

    - On va attendre du JSON donc dans `"content"` on met `"application/json"`

    - Comme notre itemOpération `"publish"` va seulement modifier le paramètre `$online` et qu'on a pas a besoin de lui envoyer des données. 

        - On va mettre dans `"schema"` un objet vide, pour signifier qu'on envoie rien `"schema"={ },`

        - On fait pareil pour `"example"`, il représentera un tableau vide dans le SwaggerUi `"example"={ }`

- On peut importer `use ApiPlatform\Core\Annotation\ApiProperty;` et utiliser `@ApiProperty` pour documenter une propriété précise 

- Dans `@ApiProperty` on écrit `openapiContext` qui contiendra un objet dans lequel on peut passer un `type`, une `description` etc

        * @ApiProperty(openapiContext={
        *      "type"="boolean",
        *      "description"="En ligne ou pas ?"
        * })
        */
        private $online = false;

- Dans `normalizationContext` on peut utiliser `"openapi_definition_name"` pour bien documenter la partie `Schema` qui est tout en bas dans l'interface de apiPlateform, en lui donnant un bon nom

- Dans `normalizationContext` la normalisation globale pour les collection, `"openapi_definition_name"="List_articles"`, le nom qui correspond le mieux est `"List_articles"`, comme ça va afficher une liste d'articles

- Dans `denormalizationContext` la dénormalisation globale pour un article précis, `"openapi_definition_name"="write_one_article"`, le nom qui correspond le mieux est `"write_one_article"`,  comme ça va agir pour les verbes http POST, PUT et PATCH

- Dans `"get"` qui est dans `itemOperations`, `"openapi_definition_name"="read_one_article"`,le nom qui correspond le mieux est `"read_one_article"`, comme ça va afficher qu'un seul article

Dans `Post.php`

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use App\Repository\PostRepository;
    use App\Controller\PostPublishController;
    use ApiPlatform\Core\Annotation\ApiFilter;
    use ApiPlatform\Core\Annotation\ApiProperty;
    use ApiPlatform\Core\Annotation\ApiResource;
    use Symfony\Component\Serializer\Annotation\Groups;
    use Symfony\Component\Validator\Constraints as Assert;
    use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;


    /**
    * @ORM\Entity(repositoryClass=PostRepository::class)
    * 
    * @ApiResource(
    *      
    *      normalizationContext={
    *          "groups"={"read:Post:collection"},
    *          "openapi_definition_name"="List_articles"
    *      },
    * 
    *      denormalizationContext={
    *          "groups"={"write:Post:item"}, 
    *          "openapi_definition_name"="write_one_article"
    *      },
    * 
    *      attributes={
    *          "pagination_items_per_page"=2,
    *          "maximum_items_per_page"=2,
    *          "pagination_client_items_per_page"=true
    *      },
    * 
    *      itemOperations={
    *          "put",
    *          "delete",
    *          "get"={
    *              "normalization_context"={
    *                  "groups"={"read:Post:collection", "read:Post:item", "read:Post:and:Category:item"},
    *                  "openapi_definition_name"="read_one_article"
    *              }
    *          },
    *          "publish"={
    *              "method"="POST",
    *              "path"="/posts{id}/publish",
    *              "controller"=PostPublishController::class,
    *              "openapi_context"={
    *                  "summary"="Permet de publier un article",
    *                  "requestBody"={
    *                      "content"={
    *                          "application/json"={
    *                              "schema"={ },
    *                              "example"={ }
    *                          }
    *                      }
    *                  }
    *              }
    *          }
    *      }
    * )
    * 
    * @ApiFilter(SearchFilter::class, properties={"id": "exact", "title": "partial"})
    */
    class Post
    {
        ....

        /**
        * @ORM\Column(type="boolean", options={"default": "0"})
        * @Groups({"read:Post:collection"}) 
        * @ApiProperty(openapiContext={
        *      "type"="boolean",
        *      "description"="En ligne ou pas ?"
        * })
        */
        private $online = false;




### Dans Category.php

- On va faire pareil que l'entité `Post.php`, pour `normalizationContext`, `denormalizationContext` et `"get"` afin de bien documenter la partie `Schema` qui est tout en bas dans l'interface de apiPlateform, en lui donnant un bon nom

Dans `Category.php`

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use App\Repository\CategoryRepository;
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
    *               }
    *           }
    *      }
    * )
    */
    class Category