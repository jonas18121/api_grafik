# Installation du projet avec Symphony 4.4 et ApiPlateform

- En ligne de commande, on installe Symfony 4.4

    > symfony new api_grafik --version=4.4

- On va dans le projet

    > cd api_grafik

- On installe ApiPlateform

    > composer require api

- On installe bundle maker

    > composer require symfony/maker-bundle --dev

- On installe le server de Symfony 4.4, a partir de Symfony 5, on n'utilise plus cette cammande

    > composer require server --dev

- On ferra touner le projet une fois qu'on aura créer notre bdd avec cette commande

    > php bin/console server:run

### Dans .env

- On fait notre bdd avec mysql

    `DATABASE_URL="mysql://root:@127.0.0.1:3306/api_grafik?serverVersion=5.7"`

- En ligne de commande : 

    - Pour créer la bdd dans phpMyAdmin

    > php bin/console doctrine:database:create

Dans `.env`

    # In all environments, the following files are loaded if they exist,
    # the latter taking precedence over the former:
    #
    #  * .env                contains default values for the environment variables needed by the app
    #  * .env.local          uncommitted file with local overrides
    #  * .env.$APP_ENV       committed environment-specific defaults
    #  * .env.$APP_ENV.local uncommitted environment-specific overrides
    #
    # Real environment variables win over .env files.
    #
    # DO NOT DEFINE PRODUCTION SECRETS IN THIS FILE NOR IN ANY OTHER COMMITTED FILES.
    #
    # Run "composer dump-env prod" to compile .env files for production use (requires symfony/flex >=1.2).
    # https://symfony.com/doc/current/best_practices.html#use-environment-variables-for-infrastructure-configuration

    ###> symfony/framework-bundle ###
    APP_ENV=dev
    APP_SECRET=19085cb63c783dd5e43a95baaf1961a2
    #TRUSTED_PROXIES=127.0.0.0/8,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16
    #TRUSTED_HOSTS='^(localhost|example\.com)$'
    ###< symfony/framework-bundle ###

    ###> doctrine/doctrine-bundle ###
    # Format described at https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
    # IMPORTANT: You MUST configure your server version, either here or in config/packages/doctrine.yaml
    #
    # DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
    DATABASE_URL="mysql://root:@127.0.0.1:3306/api_grafik?serverVersion=5.7"
    # DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=13&charset=utf8"
    ###< doctrine/doctrine-bundle ###

    ###> nelmio/cors-bundle ###
    CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
    ###< nelmio/cors-bundle ###


### Dans Entity/Post.php

On creer notre entité post avec la commande ci dessous, et on lui met les champs dont on a besoin

    > php bin/console make:entity

- Pour les champs qui on besoin d'être en `relation` avec une autre `entité` par expmle le champ category, il faut d'abord créer l'autre entité avant

- On va creer, l'entité category puis on reviens ici

- On envoie cette version de notre entité en bdd avec 

    > php bin/console make:migration

    > php bin/console doctrine:migrations:migrate

- Si on va à cette url `http://127.0.0.1:8000/api` on verra le `Swagger`, `open API` car on n'a pas encore definit les entités qui seront géré par ApiPlateform 

- On importe `use ApiPlatform\Core\Annotation\ApiResource;` afin d'utiliser l'annotation `@ApiResource()` pour definir à ApiPlateform qu'il devrat géré les url de l'entité `Post.php`

Dans `Entity/Post.php`

    namespace App\Entity;

    use ApiPlatform\Core\Annotation\ApiResource;
    use App\Repository\PostRepository;
    use Doctrine\ORM\Mapping as ORM;

    /**
    * @ORM\Entity(repositoryClass=PostRepository::class)
    * 
    * @ApiResource()
    */
    class Post
    {
        /**
        * @ORM\Id
        * @ORM\GeneratedValue
        * @ORM\Column(type="integer")
        */
        private $id;

        /**
        * @ORM\Column(type="string", length=255)
        */
        private $title;

        /**
        * @ORM\Column(type="string", length=255)
        */
        private $slug;

        /**
        * @ORM\Column(type="text")
        */
        private $content;

        /**
        * @ORM\Column(type="datetime")
        */
        private $createdAt;

        /**
        * @ORM\Column(type="datetime", nullable=true)
        */
        private $updatedAt;

        /**
        * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="posts")
        */
        private $category;

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

- On cree notre entité `Category.php` en ligne de commande et on le met en `relation` avec l'entité `Post.php` depuis `Post.php`

Dans `Category.php`

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use App\Repository\CategoryRepository;
    use Doctrine\Common\Collections\Collection;
    use ApiPlatform\Core\Annotation\ApiResource;
    use Doctrine\Common\Collections\ArrayCollection;

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
        */
        private $id;

        /**
        * @ORM\Column(type="string", length=255)
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
