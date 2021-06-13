# La Validation

Ici on va géré la validation, qui sera piloté par la validation de Symfony standard 

la validation sera pour des données qui viennent depuis un fromulaire par exemple

Site : https://api-platform.com/docs/core/validation/


### Dans Post.php

- On importe `use Symfony\Component\Validator\Constraints as Assert;`

- Dans la propriété `$title` on va mettre en annotation `@Assert\Length(min = 5, minMessage = "Le titre de votre article doit comporter au minimum 5 caractères" )` pour controlé la taille minimum que doit contenir le titre d'un article

- Pour par exemple, creer une catégorie en même temps qu'on creer un article, 

    - on va mettre `"write:Post:item"` dans `@Groups({"read:Post:item", "write:Post:item"}) ` de la propriété `$category` de l'entité `Post.php` pour que l'entité `Category.php` soit traiter aussi lors de la dénormalisation

    - On va mettre `cascade={"persist"}` dans `@ORM\ManyToOne(targetEntity=Category::class, inversedBy="posts", cascade={"persist"})` de la propriété `$category` de l'entité `Post.php`, pour que cela presiste en cascade, 

    - On va mettre en annotation `@Assert\Valid()` dans la propriété `$category` de l'entité `Post.php`, pour observer les règles de validation qu'on va mettre dans les propriétés de l'entité `Category.php` qu'on voudra traiter lors de la dénormalisation, et vérifier qu'ils soient valide

    - on va dans l'entité `Category.php` et on va mettre `"write:Post:item"` dans `@Groups({"read:Post:item", "write:Post:item"}) ` de la propriété `$name` pour qu'il soit traiter lors de la dénormalisation

    - `@Assert\Length(min = 5, minMessage = "Le nom de la catégorie doit comporter au minimum 5 caractères" )` on met notre règles de validation pour la propriété `$name` de l'entité `Category.php`

Bravo !!!

Dans `Post.php`

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use App\Repository\PostRepository;
    use ApiPlatform\Core\Annotation\ApiResource;
    use Symfony\Component\Serializer\Annotation\Groups;
    use Symfony\Component\Validator\Constraints as Assert;

    /**
    * @ORM\Entity(repositoryClass=PostRepository::class)
    * 
    * @ApiResource(
    * 
    *      normalizationContext={"groups"={"read:Post:collection"}},
    *      denormalizationContext={"groups"={"write:Post:item"}},
    * 
    *      itemOperations={
    *          "put",
    *          "delete",
    *          "get"={
    *              "normalization_context"={"groups"={"read:Post:collection", "read:Post:item", "read:Post:and:Category:item"}}
    *          }
    *      }
    * )
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


### Dans Category.php

- on va mettre `"write:Post:item"` dans `@Groups({"read:Post:item", "write:Post:item"}) ` de la propriété `$name` pour qu'il soit traiter lors de la dénormalisation

- `@Assert\Length(min = 5, minMessage = "Le nom de la catégorie doit comporter au minimum 5 caractères" )` on met notre règles de validation pour la propriété `$name` 

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
    * @ApiResource()
    */
    class Category
    {
        /**
        * @ORM\Id
        * @ORM\GeneratedValue
        * @ORM\Column(type="integer")
        * @Groups({"read:Post:and:Category:item"}) 
        */
        private $id;

        /**
        * @ORM\Column(type="string", length=255)
        * @Groups({"read:Post:and:Category:item", "write:Post:item"}) 
        * 
        * @Assert\Length(min = 5, minMessage = "Le nom de la catégorie doit comporter au minimum 5 caractères" )
        */
        private $name;

        /**
        * @ORM\OneToMany(targetEntity=Post::class, mappedBy="category")
        */
        private $posts;