# La serialisation

Ici on va voir le principe de serialisation

C'est le système de serialisation de symphony qui va travailler

## Le Processus de sérialisation

- `Les données` partent depuis `la base de données` avec le `backend` pour aller vers le `frontend`

- A partir du `backend` ces données seront dans des `Object` et devront être convertis en format ` (JSON ou XML ou autre)` pour utiliser ces données dans le `frontend`

- Pour passé depuis des données de format `Object` jusqu'aux données de format `(JSON ou XML ou autre)`, on appelle cela le processus de `serialisation`

- Pour passé depuis des données de format `(JSON ou XML ou autre)` jusqu'aux données de format `Object` , on appelle cela le processus de `deserialisation`

- Dans le processus de `sérialisation`, 

    - Les données de format `Object` sont convertis en données de format `Array` on appelle cela la `normalisation`

    - Puis les données de format `Array` sont convertis en données de format `(JSON ou XML ou autre)` on appelle cela `encode`

- Dans le processus de `désérialisation`, 

    - Les données de format `(JSON ou XML ou autre)` sont convertis en données de format `Array` on appelle cela `decode`

    - Puis les données de format `Array` sont convertis en données de format `Object` on appelle cela la `denormalisation`

#### Schema

- `serialisation` = `Object` => `Array` => `(JSON ou XML ou autre)`

- `deserialisation` =`(JSON ou XML ou autre)` => `Array` => `Object`


Dans ces processus, on peut definir les champs que l'on veut traiter en faisant des groupes de `normalisation`

### Dans Post.php 

- On importe `use Symfony\Component\Serializer\Annotation\Groups;` pour faire des groupes de `normalisation`

- On va créer un groupe nommé `"read:Post:collection"` pour afficher certaines propriété lorsqu'on voudra afficher des articles en collection. collection = afficher plusieurs article en même temps

- `normalizationContext={"groups"={"read:Post:collection"}},`, `"read:Post:collection"` ecrit dans `normalizationContext={"groups"={  }}`, veut dire qu'on va l'utiliser en globalité, c-a-d les verbes http pourrons l'utiliser dans `itemOperations` et dans `collectionOperations` lors de la `normalisation`

- `denormalizationContext={"groups"={"write:Post:item"}},` c'est pareil que `normalizationContext`, sauf que c'est lors de la `dénormalisation` en globale, donc pour les verbes HTTP put et post en même temps. On peut aussi directement les utilisés dans un `itemOperations`

    exemple:

        "put"={
            "denormalization_context"={"groups"={"read:Post:item"}}
        }

- `@Groups({"read:Post:item"}) ` on l'utilise dans nos propriétés qu'on voudra afficher

`REMARQUE:` lorsqu'on fait nos testes, il ce peut qu'on ne voit pas de changement même si le code est bien écrit, à ce moment là, il faut exécuté la commande ci-dessous pour vider le cache

    > php bin/console cache:clear

- `"get"={"normalization_context"={"groups"={"read:Post:collection", "read:Post:item", "read:Post:and:Category:item"}} }` dans `itemOperations`, veut dire qu'on traiter certaines propriétés lorsqu'un seul item est traiter, lors de la `normalisation`, on peut faire pareil pour le verbe HTTP :  put 

- Pour utiliser les groupes a travers les relations entre les entité, c'est facile. `"read:Post:and:Category:item"` dans `"get"={"normalization_context"={"groups"={}}}` de l'entité `Post`, puis on va dans l'entité `Category` et on met `@Groups({"read:Post:and:Category:item"}) ` dans les propriétés de l'entité `Category` qu'on voudra affiché, lorsqu'on affiche un article 


Dans `Post.php` 


    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use App\Repository\PostRepository;
    use ApiPlatform\Core\Annotation\ApiResource;
    use Symfony\Component\Serializer\Annotation\Groups;

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
        * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="posts")
        * @Groups({"read:Post:item", "write:Post:item"}) 
        */
        private $category;

        public function __construct()
        {
            $this->createdAt = new \DateTime();
            $this->udatedAt = new \DateTime();
        }

        public function getId(): ?int
        {
            return $this->id;
        }

        public function getTitle(): ?string
        {
            return $this->title;
        }

        public function setTitle(string $title): self
        {
            $this->title = $title;

            return $this;
        }

        public function getSlug(): ?string
        {
            return $this->slug;
        }

        public function setSlug(string $slug): self
        {
            $this->slug = $slug;

            return $this;
        }

        public function getContent(): ?string
        {
            return $this->content;
        }

        public function setContent(string $content): self
        {
            $this->content = $content;

            return $this;
        }

        public function getCreatedAt(): ?\DateTimeInterface
        {
            return $this->createdAt;
        }

        public function setCreatedAt(\DateTimeInterface $createdAt): self
        {
            $this->createdAt = $createdAt;

            return $this;
        }

        public function getUpdatedAt(): ?\DateTimeInterface
        {
            return $this->updatedAt;
        }

        public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
        {
            $this->updatedAt = $updatedAt;

            return $this;
        }

        public function getCategory(): ?Category
        {
            return $this->category;
        }

        public function setCategory(?Category $category): self
        {
            $this->category = $category;

            return $this;
        }
    }




### Dans Category.php

- On utilise `@Groups({"read:Post:and:Category:item"}) ` dans les propriétés de l'entité `Category` qu'on voudra affiché, lorsqu'on affiche un article de puis l'entité `Post`

Dans `Category.php`

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

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
     * @Groups({"read:Post:and:Category:item"}) 
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="category")
     */
    private $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setCategory($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getCategory() === $this) {
                $post->setCategory(null);
            }
        }

        return $this;
    }
}
