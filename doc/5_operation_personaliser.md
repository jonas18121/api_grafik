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

- après la création de `$online` :

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