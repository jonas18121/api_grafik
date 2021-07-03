<?php

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


// curl -X GET "http://127.0.0.1:8000/api/posts?page=1" -H "accept: application/json"


// vidéo 10 stopper a 00:03:29


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
     * @ApiProperty(openapiContext={
     *      "type"="boolean",
     *      "description"="En ligne ou pas ?"
     * })
     */
    private $online = false;

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

    public function getOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }
}
