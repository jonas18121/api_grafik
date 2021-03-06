<?php

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
