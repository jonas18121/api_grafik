# Créer un DataPersister personnaliser partie 2

On cree `DependencyRepository.php`, qui va imiter un repository pour doctrine , par contre `DependencyRepository.php`, n'aura aucun lien avec doctrine


### Dans DependencyRepository.php

- On a pris certaine logique qu'il avait dans `DependencyDataProvider.php` et on va le mettre dans `DependencyRepository.php`, comme ça  `DependencyDataProvider.php` et `DependencyDataPersister.php` pourra les utilisées

- La logique qu'on a pris sont :

    - La fonction construct() qui prend le chemin racine du projet + le fichier `composer.json`

    - La fonction `getDependencies()`

    - Le contenu qu'il y avait dans la fonction `getCollection()` de `DependencyDataProvider.php` et on la mis dans la fonction `findAll()` de `DependencyRepository.php`

        - Dans la fonction `getCollection()` de `DependencyDataProvider.php`, on va mettre `return $this->repository->findAll();` pour utiliser la fonction `findAll()` de `DependencyRepository.php`


    - Le contenu qu'il y avait dans la fonction `getItem()` de `DependencyDataProvider.php` et on la mis dans la fonction `find()` de `DependencyRepository.php`

        - Dans la fonction `getItem()` de `DependencyDataProvider.php`, on va mettre `return $this->repository->find($id);` pour utiliser la fonction `find()` de `DependencyRepository.php`

#### persist()

- On crée la fonction `persist()` et dedans :

    - `$json = json_decode(file_get_contents($this->rootPathComposer), true);`, on recupère les dépendances qui sont déjà présent dans le fichier composer, on décode le fichier composer en tableau

    - `$json['require'][$dependency->getName()] = $dependency->getVersion();`, on rajoute la nouvelle dépendance dans le fichier composer

    - `file_put_contents($this->rootPathComposer, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));` on réécrit le fichier composer avec la nouvelle dépendance et on encode le fichier composer pour qu'il redevienne en format json

        - `JSON_PRETTY_PRINT` permet d'avoir un fichier composer bien indenter en format json

        - `JSON_UNESCAPED_SLASHES` permet d'enlever les antiSlashes, que `json_encode()` a rajouter inutilement lors de cette opération 

#### remove()

- On crée la fonction `remove()` et dedans :

    - `$json = json_decode(file_get_contents($this->rootPathComposer), true);`, on recupère les dépendances qui sont déjà présent dans le fichier composer, on décode le fichier composer en tableau

    - `unset($json['require'][$dependency->getName()]);`, On supprime la clé qui correspond au nom de la dépendance

    - `file_put_contents($this->rootPathComposer, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));` on réécrit le fichier composer sans la dépendance qui a été supprimer et on encode le fichier composer pour qu'il redevienne en format json

        - `JSON_PRETTY_PRINT` permet d'avoir un fichier composer bien indenter en format json

        - `JSON_UNESCAPED_SLASHES` permet d'enlever les antiSlashes, que `json_encode()` a rajouter inutilement lors de cette opération

Dans `DependencyRepository.php`

    namespace App\Repository;

    use Ramsey\Uuid\Uuid;
    use App\Entity\Dependency;

    class DependencyRepository
    {

        /**
        * chemin racine du projet
        */
        private string $rootPathComposer;

        public function __construct(string $rootPath)
        {
            $this->rootPathComposer = $rootPath . '/composer.json';
        }

        /**
        * Récupère les dépandances qui son dans le fichier composer.json
        * décode le json en tableau
        * retourne la liste des dépandances qui sont dans l'objet require{} du fichier composer.json
        *
        * @return void
        */
        public function getDependencies() : array
        {
            // $path = $this->rootPath . '/composer.json';
            $json = json_decode(file_get_contents($this->rootPathComposer), true);

            return $json['require'];
        }

        /**
        * Récupéré la liste des dépandances qui sont dans l'objet require{} du fichier composer.json
        *
        * @return void
        */
        public function findAll() : array
        {
            $dependencies = $this->getDependencies();        

            $items = [];

            foreach ($dependencies as $name => $version) {

                $items[] = new Dependency($name, $version);
            }

            return $items;
        }


        /**
        * recupéré une dépandance en particulier qui est l'objet require{} du fichier composer.json
        *
        * @param string $id
        * @return Dependency|null
        */
        public function find(string $id) : ?Dependency
        {
            $dependencies = $this->getDependencies(); 

            
            foreach ($dependencies as $name => $version) {
                
                $uuid = Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString();
                
                if($uuid === $id){
                    
                    return new Dependency($name, $version, $uuid);
                }
            }
            
            return null;
        }


        /**
        * Ajouter une dépendance dans l'objet require{} du fichier composer.json
        *
        * @param Dependency $dependency
        * @return void
        */
        public function persist(Dependency $dependency)
        {
            $json = json_decode(file_get_contents($this->rootPathComposer), true);
            $json['require'][$dependency->getName()] = $dependency->getVersion();

            file_put_contents($this->rootPathComposer, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }


        /**
        * permet de supprimer des donnée
        *
        * @param Dependency $dependency
        * @return void
        */
        public function remove(Dependency $dependency)
        {
            $json = json_decode(file_get_contents($this->rootPathComposer), true);
            
            //supprime la clé qui correspond au nom de la dépendance
            unset($json['require'][$dependency->getName()]);

            file_put_contents($this->rootPathComposer, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }


### Dans DependencyDataProvider.php

- On importe `DependencyRepository`, on l'appel dans le constructeur et on utilise ses méthodes

Dans `DependencyDataProvider.php`


    namespace App\DataProvider;

    use Ramsey\Uuid\Uuid;
    use App\Entity\Dependency;
    use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
    use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
    use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
    use App\Repository\DependencyRepository;

    class DependencyDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface, ItemDataProviderInterface
    {

        private DependencyRepository $repository;

        public function __construct(DependencyRepository $repository)
        {
            $this->repository = $repository;
        }
        
        /**
        * Récupéré la liste des dépandances qui sont dans l'objet require{} du fichier composer.json
        *
        * @param string $resourceClass
        * @param string $operationName
        * @param array $context
        * @return void
        */
        public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
        {
            return $this->repository->findAll();
        }

        public function supports(string $resourceClass, string $operationName = null, array $context = []) : bool
        {
            return $resourceClass === Dependency::class;
        }

        /**
        * recupéré une dépandance en particulier qui est l'objet require{} du fichier composer.json
        *
        * @param array|int|object|string $id
        *
        * @throws ResourceClassNotSupportedException
        *
        * @return object|null
        */
        public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
        {
            return $this->repository->find($id);
        }
    }

### Dans services.yaml

- On envoie le chemin racine du projet au constructeur de `DependencyRepository.php`

Dans `services.yaml`

    services:
    
        ...
            
        App\Repository\DependencyRepository:
            arguments: ['%kernel.project_dir%']

### Dans DataPersister/DependencyDataPersister.php

- On importe `DependencyRepository`, on l'appel dans le constructeur et on utilise ses méthodes


Dans `DataPersister/DependencyDataPersister.php`

    namespace App\DataPersister;

    use App\Entity\Dependency;
    use App\Repository\DependencyRepository;
    use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

    class DependencyDataPersister implements ContextAwareDataPersisterInterface 
    {
        private DependencyRepository $repository;

        public function __construct(DependencyRepository $repository)
        {
            $this->repository = $repository;
        }
        
        /**
        * Vérifier si la variable $data est une instance de  Dependency
        * {@inheritdoc}
        */
        public function supports($data, array $context = []): bool
        {
            return $data instanceof Dependency;
        }

        /**
        * Permet de sauvegarder nos données dans la BDD,
        * Nous on ne va pas sauvegarder nos données dans la BDD, on va les sauvegarder dans le fichier composer.json
        * {@inheritdoc}
        */
        public function persist($data, array $context = [])
        {
            $this->repository->persist($data);
        }

        /**
        * permet de supprimer des données
        * Pour nous, on supprimera nos dependances
        * {@inheritdoc}
        */
        public function remove($data, array $context = [])
        {
            $this->repository->remove($data);
        }
    }


### Dans Dependency.php

- On rajoute quelques règles de validation avec `use Symfony\Component\Validator\Constraints as Assert;`

#### Verbe HTTP PUT

- On va rajouter la possibilité de faire des opérations avec le verbe http `put` dans itemOperations

- Pour faire des opérations avec le verbe http `put` dans itemOperations, on va `rajouter des setters` pour les propriétés qu'on veut modifier

- Dans `"put"={}` : 

    - On va rajouter un groupe de dénormalisation `denormalization_context` pour que l'opération avec le verbe http `put` ai accès seulement aux propriétés qui auront le nom de ce groupe `"write:put:Dependency:item"` en annotation pour les `modifiés`

    - On va rajouter un groupe de normalisation `normalization_context` pour que l'opération avec le verbe http `put` ai accès seulement aux propriétés qui auront le nom de ce groupe `"read:put:Dependency:item"` en annotation pour les `affichés`

    - Le `DependencyDataProvider` va être appeler avec la methode `find()` pour fournir les données puis le `DependencyDataPersister` va être appeler pour enregistrer les données qui seront modifier avec la methode `persiste()` 

        "put"={
            "normalization_context"={
                "groups"={"read:put:Dependency:item"},
                "openapi_definition_name"="read_put_one_dependency"
            },
            "denormalization_context"={
                "groups"={"write:put:Dependency:item"},
                "openapi_definition_name"="write_put_one_dependency"
            }
        },

        ....

         /**
          ....

          @Groups({"write:put:Dependency:item", "read:put:Dependency:item"}) 
        */
        private string $version;

#### Verbe HTTP DELETE

- On va rajouter la possibilité de faire des opérations avec le verbe http `delete` dans itemOperations

- le `DependencyDataPersister` va être appeler pour supprimer des données avec la methode `remove()` 


Dans `Dependency.php`

    namespace App\Entity;

    use ApiPlatform\Core\Annotation\ApiProperty;
    use ApiPlatform\Core\Annotation\ApiResource;
    use Ramsey\Uuid\Uuid;
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Serializer\Annotation\Groups;

    /**
    * @ApiResource(
    * 
    *      paginationEnabled=false,
    * 
    *      collectionOperations={
    *          "get",
    *          "post"
    *      },
    * 
    *      itemOperations={
    *          "get",
    *          "delete",
    *          "put"={
    *              "normalization_context"={
    *                  "groups"={"read:put:Dependency:item"},
    *                  "openapi_definition_name"="read_put_one_dependency"
    *              },
    *              "denormalization_context"={
    *                  "groups"={"write:put:Dependency:item"},
    *                  "openapi_definition_name"="write_put_one_dependency"
    *              }
    *          },
    *      }
    * )
    */
    class Dependency
    {


        //Pour que ça fonctionne il faut que la propriété soit en public ou on crée un Getter pour cette propriété  
        // lorsqu'on utilise @ApiProperty en annotation
        // il ne faut pas de commentaire  dans /** */ de la propriété
        /**
        * @ApiProperty(identifier=true)
        */
        private string $uuid;

        /**
        * @ApiProperty(
        *      openapiContext={
        *          "type"="string",
        *          "description"="Nom de la dépendance"
        *      }
        * )
        * 
        * @Assert\Length(min = 2, minMessage = "Le nom de la dépendance doit comporter au minimum 2 caractères" )
        * @Assert\NotBlank
        */
        private string $name;

        /**
        * @ApiProperty(
        *      openapiContext={
        *          "type"="string",
        *          "description"="version de la dépendance",
        *          "example"="5.2.*"
        *      }
        * )
        * 
        * @Assert\Length(min = 2, minMessage = "La version de la dépendance doit comporter au minimum 2 caractères" )
        * @Assert\NotBlank
        * @Groups({"write:put:Dependency:item", "read:put:Dependency:item"}) 
        */
        private string $version;

        public function __construct(string $name, string $version, string $uuid = null)
        {
            $this->uuid = $uuid === null ? Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString() : $uuid;
            $this->name = $name;
            $this->version = $version;
        }

        /**
        * Get the value of uuid
        */ 
        public function getUuid()
        {
            return $this->uuid;
        }

        /**
        * Get openapiContext={
        */ 
        public function getName()
        {
            return $this->name;
        }

        /**
        * Get openapiContext={
        */ 
        public function getVersion()
        {
            return $this->version;
        }

        /**
        * Set openapiContext={
        *
        * @return  self
        */ 
        public function setName($name)
        {
            $this->name = $name;

            return $this;
        }

        /**
        * Set openapiContext={
        *
        * @return  self
        */ 
        public function setVersion($version)
        {
            $this->version = $version;

            return $this;
        }
    }