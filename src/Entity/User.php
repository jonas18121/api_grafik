<?php

namespace App\Entity;

use App\Controller\MeController;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use ApiPlatform\Core\Action\NotFoundAction;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\security;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * 
 * @ApiResource(
 * 
 * 
 *      security="is_granted('ROLE_USER')",
 * 
 *      normalizationContext={
 *          "groups"={"read:User:collection"},
 *          "openapi_definition_name"="List_users"
 *      },
 * 
 *      collectionOperations={
 *          
 *          "me"={
 *              "pagination_enabled"=false,
 *              "path"="/me",
 *              "method"="get",
 *              "controller"=MeController::class,
 *              "read"=false,
 *              "openapi_context"={
 *                  "security"={
 *                      "cookieAuth"={ }
 *                  }
 *              }
 *          },
 *      },
 * 
 *      itemOperations={
 * 
 *          "get"={
 *              "controller"="NotFoundAction::class",
 *              "openapi_context"={
 *                  "summary"="hidden"
 *              },
 *              "read"=false,
 *              "output"=false
 *          }
 *      }
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:User:collection"}) 
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"read:User:collection"}) 
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"read:User:collection"}) 
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
