# Créer un DataProvider personnalisé sur une Entité qui ne provient pas de notre BDD, il n'a pas les annotions de doctrine

on va créer un nouveau end Point qui va lister les dependances qu'on a dans notre projet, via le fichier `composer.json`


## 1) Creer un objet qui va repésenter les données

On va créer un objet nommé `Entity/Dependency.php` qui contiendra le nom de la dépendance et la version de la dépendance


### Dans Dependency.php

- On crée nos propriétés privée `$uuid`, `$name` et `$version` 

- On crée leurs getteur

- On importe `use ApiPlatform\Core\Annotation\ApiResource;` pour utilisé `@ApiResource` en annotation

- On importe `use ApiPlatform\Core\Annotation\ApiProperty;` pour utilisé `@ApiProperty` en annotation

- Dans `@ApiResource`

    - `paginationEnabled=false,` On enlève la paganation

    - `collectionOperations={ "get" },` et `itemOperations={ "get" }` on veut afficher que la methode `GET`

- `@ApiProperty(identifier=true)`, pour la propriété `$uuid`, on met `identifier` a `true` pour dire a apiPlateform que `$uuid` sera l'identifiant de l'entité `Dependency` à la place de `$id`

Dans `Dependency.php`

    namespace App\Entity;

    use ApiPlatform\Core\Annotation\ApiProperty;
    use ApiPlatform\Core\Annotation\ApiResource;

    /**
    * @ApiResource(
    * 
    *      paginationEnabled=false,
    * 
    *      collectionOperations={
    *          "get"
    *      },
    * 
    *      itemOperations={
    *          "get"
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
        */
        private string $version;

        public function __construct(string $uuid, string $name, string $version)
        {
            $this->uuid = $uuid;
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
    }


## 2) Créer un DataProvider personnalisé pour récupéré les données

- On crée manuellement le fichier `src/DataProvider/DependencyDataProvider.php`

- La classe `DependencyDataProvider` va implémenter :




    - `ContextAwareCollectionDataProviderInterface` avec sa fonction ci-dessous pour récupéré une listes de données :

        public function getCollection(string $resourceClass, string $operationName = null, array $context = []){}






    - `RestrictedDataProviderInterface` avec sa fonction ci-dessous pour faire une restriction et que `DependencyDataProvider` s'active seulement pour les requêtes vers l'entité `Dependency` 

        public function supports(string $resourceClass, string $operationName = null, array $context = []) : bool {}





    - `ItemDataProviderInterface` avec sa fonction ci-dessous pour récupéré une donnée en particulier 

        public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []) {}





### Dans DataProvider/DependencyDataProvider.php



- La fonction `supports()` est appeler en premier lorsqu'une requête est faite, il vérifie si les données reçu dans la variable `$resourceClass` sont bien de type `Dependency`, 

    - Si c'est `true`, on pourra continuer le processus et excétuter les fonctions `getCollection()` et `getItem()`, au choix

    - Si c'est `false`, on ne continue pas le processus




- Dans `supports()`

    - `return $resourceClass === Dependency::class;`, vérifie si les données reçu dans la variable `$resourceClass` sont bien de type `Dependency`, et retourne un boolean




- On va avoir besion du chemin racine du projet, donc on va passer par un service

    - `private string $rootPath` recevra le chemin racine du projet, on le met en argument de la fonction  `__construct()`, le service passera le chemin racine à `$rootPath`, aller voir `services.yaml` plus bas



- La fonction `getDependencies()`, Récupère les dépandances qui sont dans le fichier `composer.json`, il décode le rendu qui est en json en tableau et retourne la liste des dépandances qui sont dans l'objet require{} du fichier composer.json

- Dans `getDependencies()`

    - `$path = $this->rootPath . '/composer.json';`, On recupère le chemin de qui va vers `composer.json`

    - `$json = json_decode(file_get_contents($path), true);`

        - `file_get_contents($path)`, On lie le fichier `composer.json`

        - `json_decode()` on decode le fichier qui est en json

        - On met `true` en deuxième argument pour que ça devienne un tableau associative

        - `return $json['require'];`, on retourne le tableau, on veut uniquement les dépendances qui sont dans l'objet `'require'` du fichier `composer.json`




#### Récupéré une liste d'item

- Dans `getCollection()` 

    - `$dependencies = $this->getDependencies(); ` On recupère les dépendances qui sont dans l'objet `'require'` du fichier `composer.json`

    - `foreach ($dependencies as $name => $version)`, on fait une boucle pour parcourir chaques dépendances

    - `$items[] = new Dependency(Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString(), $name, $version);`

        - On installe `ramsey/uuid` pour généré des identifiant unique

            > composer require ramsey/uuid

        - on importe `ramsey/uuid`

            use Ramsey\Uuid\Uuid;

        - On utilise la fonction `Uuid::uuid5()`

            - en premier paramètre on met un namespace, on va utiliser `Uuid::NAMESPACE_URL`

            - En deuxièmes paramètres, on met le nom de la dépendance `$name`

            - On transforme le tout en chaine de caractère `->toString()`

    - `return $items;`, on retourne le tableau `$items`




#### Récupéré un item en particulier


- Dans `getItem()`

    - le deuxième argument `$id` est ce qu' va passer, exemple notre uuid, qu'on va passer depuis le client

    - `$dependencies = $this->getDependencies(); ` On recupère les dépendances qui sont dans l'objet `'require'` du fichier `composer.json`

    - `foreach ($dependencies as $name => $version)`, on fait une boucle pour parcourir chaques dépendances

    - `$uuid = Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString();`, on récupère l'uuid via le nom de la dépendance depuis l'objet `'require'` du fichier `composer.json`

    - `if($uuid === $id){}` on compare si l' `$uuid` est égale à `$id` qui est passer en paramètre

    - `return new Dependency($uuid, $name, $version);`, Si, c'est `true`, on récupère la dépendance et on la retourne

    - Si c'est `false` , on retourne null 

Dans `DependencyDataProvider.php`


    namespace App\DataProvider;

    use Ramsey\Uuid\Uuid;
    use App\Entity\Dependency;
    use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
    use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
    use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;

    class DependencyDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface, ItemDataProviderInterface
    {
        /**
        * chemin racine du projet
        */
        private string $rootPath;

        public function __construct(string $rootPath)
        {
            $this->rootPath = $rootPath;
        }

        /**
        * Récupère les dépandances qui sont dans le fichier composer.json
        * décode le json en tableau
        * retourne la liste des dépandances qui sont dans l'objet require{} du fichier composer.json
        *
        * @return void
        */
        public function getDependencies() : array
        {
            $path = $this->rootPath . '/composer.json';
            $json = json_decode(file_get_contents($path), true);

            return $json['require'];
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
            $dependencies = $this->getDependencies();        

            $items = [];

            foreach ($dependencies as $name => $version) {

                $items[] = new Dependency(Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString(), $name, $version);
            }

            return $items;
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
            $dependencies = $this->getDependencies(); 

            foreach ($dependencies as $name => $version) {

                $uuid = Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString();

                if($uuid === $id){

                    return new Dependency($uuid, $name, $version);
                }

                return null;
            }
        }
    }


### Dans services.yaml

- On va dire à `services.yaml` comment initialiser la classe `DependencyDataProvider` 

- `App\DataProvider\DependencyDataProvider:`, On dit sur quel classe on veut agir

- `arguments: ['%kernel.project_dir%']`, on dit que le premier argument sera de type `%kernel.project_dir%`, c'est ce que `$rootPath` va recevoir dans `DependencyDataProvider` 

Dans `services.yaml`

    services:
        
        ...

        # add more service definitions when explicit configuration is needed
        # please note that last definitions always *replace* previous ones
        App\OpenApi\OpenApiFactory:
            decorates: 'api_platform.openapi.factory'
            arguments: ['@App\OpenApi\OpenApiFactory.inner']
            autoconfigure: false

        
        App\DataProvider\DependencyDataProvider:
            arguments: ['%kernel.project_dir%']
