# Opération personnalisé ( partie 2 )

## Etape 3 : Créer un nouveau controlleur personnalisé et Configurer OpenApi dans apiPlateform pour ce controlleur

Ici, on va créer un point d'entrer qui aurait cette url : `/api/posts/count` avec la méthode `GET`  dans l'opération de type collection `collectionOperations` et qui nous renverra le nombre total d'articles que l'on a dans notre BDD

### Dans Post.php

#### Le controlleur personnalisé

- Dans `collectionOperations` on va créer notre opération qui concernera tous les items de type `Post` dans notre BDD

- L'opération s'appellera `count`

- Dans `"method"`, on passe le verbe HTTP à utiliser, ici ce sera `"GET"` 

- Dans `"path"`, on passe le chemin qui va nous permettre de faire cette opération, ici ce sera `"/posts/count"` (on est pas obliger de mettre `/api` au début)

- Dans `"controller"`, on passe le controlleur à appeler, ici ce sera `PostCountController`, on va créer ce controlleur dans le dossier `Controller`, puis on importe le controller `use App\Controller\PostCountController;`  

- on met `"read"=false`, comme on ne va pas utiliser l'argument `$data` dans notre controller et qui ne recevra pas de données d'aucun article dans tout les cas, cela permettra aussi qu'il ne fasse pas de requête en trop.


#### Configurer OpenApi pour l'opération "count"

- On va modifier `"openapi_context"`


- Dans l'opération`"count"`, 

    - `"pagination_enabled"=false,` sert à enlevé la pagination pour `"count"` dans l'interface de apiPlateform, comme on en a pas besoin 

    - `"filters"={},` sert à enlevé les filtres pour `"count"` dans l'interface de apiPlateform, comme on en a pas besoin 

    - On  écrit `"openapi_context"` et dedans on va lui passé un objet qui contient des spécifités de notre api pour cette opération

    - `"summary"` permet d'écrire un petit résumé 

    - `"parameters"`, il va prendre un objet pour spécifié ce qu'on attend que l'utilisateur rentre dans ce champ

        - `"in"="query",` le paramètre sera de type query

        - `"name"="online",` on donne un nom a ce paramètre

        - Dans `"schema"` on va mettre un objet qui aura un `type`, une valeur `maximum`, et une valeur `minimum`

        - `"description"`, pour expliquer a quoi ça correspond
    
    - `"responses"` , on documenter les réponses pour le status 200

        - `"200"` le status qu'on va documenter

            - `"description"`, message lorsque ce status code est bon

            - On va attendre du JSON donc dans `"content"` on met `"application/json"`

            - Dans `"schema"` on va mettre un objet qui aura un `type` et un `example`



Dans `Post.php`

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use App\Repository\PostRepository;
    use App\Controller\PostCountController;
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
    *      collectionOperations={
    *          "get",
    *          "post",
    * 
    *          "count"={
    *              "method"="GET",
    *              "path"="/posts/count",
    *              "controller"=PostCountController::class,
    *              "read"=false,
    *              "pagination_enabled"=false,
    *              "filters"={},
    *              "openapi_context"={
    *                  "summary"="Récupère le nombre total d'articles",
    *                  "parameters"={
    *                      {
    *                          "in"="query",
    *                          "name"="online",
    *                          "schema"={
    *                              "type"="integer",
    *                              "maximun"=1,
    *                              "minimum"=0
    *                          },
    *                          "description"="Entrer le chiffre 1 pour voir les articles en ligne, Entrer le chiffre 0 pour voir les articles hors ligne"
    *                      }
    *                  },
    *                  "responses"={
    *                      "200"={
    *                          "description"="L'opération à reussie",
    *                          "content"={
    *                              "application/json"={
    *                                  "schema"={
    *                                      "type"="integer",
    *                                      "example"=3
    *                                  }
    *                              }
    *                          }
    *                      }
    *                  }
    *              }
    *          }
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


### Dans PostCountController.php

- On créer notre controlleur manuellement

- la fonction ` __invoke()` sera appeller automatiquement lorsqu'on va utiliser le controlleur `PostCountController`

- ApiPlateform va automatiquement mettre dans l'argument `$data`, les données des articles qui seront traiter, grace au `DataProvider` 

- Ce controlleur va nous servir seulment a compter le nombre d'articles qu'il y a dans la BDD. Il n'a pas besoin de l'argument `$data` donc on va mettre `"read"=false` dans l'opération `count` de `post.php`, cela permettra ausii qu'il ne fasse pas de requête en trop. 

- On importe `use App\Repository\PostRepository;`,  on le fait passe dans le constructeur pour l'utiliser dans la fonction `__invoke`

- Dans la fonction `__invoke`

    - `$onlineQuery = $request->get('online');`, on recupère la valeur que l'utilisateur a entrer dans le champ de la requète de l'opérateur `count`, soit 0, 1 ou rien

    - on fait une conddition `if($onlineQuery != null) ` si la variable `$onlineQuery` est différent de null, on peut rentrer dans la condition, sinon, on n'entre pas dans la condition et la variable `$conditions = []`, restera un tableau vide, et ça retournera tous les articles 

    - `$conditions = [ 'online' => $onlineQuery === '1' ? true : false ]`, si dans le champ `online`, la variable `$onlineQuery` est égale a 1, on retourne le boolean `true` sinon on retourne le boolean `false`

    - `return $this->postRepository->count($conditions);`, va aller dans la table `Post` pour compter le nombre d'articles qu'il y a en prennent en compte ce qui a été passer dans la variable `$conditions`, puis le retourne a ApiPlateform


Dans `PostCountController.php`


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