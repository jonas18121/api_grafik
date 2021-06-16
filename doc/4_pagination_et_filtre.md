# Paginations et Filtres

Ici on va voir Paginations et filtres


## Pagination

Pagination : https://api-platform.com/docs/core/pagination/#pagination

API Platform Core prend en charge nativement les collections paginées. La pagination est activée par défaut pour toutes les collections. Chaque collection contient 30 éléments par page. L'activation de la pagination et le nombre d'éléments par page peuvent être configurés à partir de :

    - le côté serveur (globalement ou par ressource)

    - côté client, via un paramètre GET personnalisé (désactivé par défaut)

## Filtre

Filtres: https://api-platform.com/docs/v2.4/core/filters/

API Platform Core fournit un système générique pour appliquer des filtres sur les collections. Des filtres utiles pour Doctrine ORM et MongoDB ODM sont fournis avec la bibliothèque. Vous pouvez également créer des filtres personnalisés qui correspondent à vos besoins spécifiques. Vous pouvez également ajouter la prise en charge du filtrage à vos fournisseurs de données personnalisés en implémentant les interfaces fournies par la bibliothèque.

Par défaut, tous les filtres sont désactivés. Ils doivent être activés explicitement.

Lorsqu'un filtre est activé, il est automatiquement documenté en tant que hydra:searchpropriété dans la réponse de collection. Il apparaît également automatiquement dans la documentation NelmioApiDoc s'il est disponible.




### Dans Post.php

- Pour la pagination, on va dans la propriété `attributes` de `@ApiRessource`

    - `pagination_items_per_page` dedans on met le nombre d'item qu'on veut par pages

    - `maximum_items_per_page` dedans on met le nombre d'item maximum qu'on veut par pages, c'est une sercurité

    - `pagination_client_items_per_page` dedans l'user pour décidé du nombre d'item qu'il veut par pages toujours en respectant `maximum_items_per_page`

    - on peut controler cela dans l'interface de ApiPlatefrom ou directement en ligne commande 

        > curl -X GET "http://127.0.0.1:8000/api/posts?page=1" -H "accept: application/json"

- Pour le filtre, On importe `@ApiFilter` depuis `use ApiPlatform\Core\Annotation\ApiFilter;`, c'est un contenant comme  `@ApiResource`, il est à l'extérieur de `@ApiResource`

- on importe aussi `use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;` qui sera le premier argument de `@ApiFilter`, 

- En deuxièmes argument de `@ApiFilter`, il y aura `properties`, dedans on mettra les propriétés de la table qu'on veut filtrer 

- Les supports de filtres exact, partial, start, endet les word_startstratégies correspondantes:

    - `partial` stratégie utilise `LIKE %text%` pour rechercher les champs qui contiennent `text`.
    
    - `start` stratégie utilise `LIKE text%` pour rechercher les champs qui commencent par `text`.
    
    - `end` stratégie utilise `LIKE %text` pour rechercher les champs qui se terminent par `text`.
    
    - `word_start` stratégie utilise `LIKE text% OR LIKE % text%` pour rechercher des champs contenant des mots commençant par `text`.

Dans `Post.php`

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use App\Repository\PostRepository;
    use ApiPlatform\Core\Annotation\ApiFilter;
    use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
    use ApiPlatform\Core\Annotation\ApiResource;
    use Symfony\Component\Serializer\Annotation\Groups;
    use Symfony\Component\Validator\Constraints as Assert;


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
    *          }
    *      }
    * )
    * 
    * @ApiFilter(SearchFilter::class, properties={"id": "exact", "title": "partial"})
    */
    class Post
    {
        ....
    }