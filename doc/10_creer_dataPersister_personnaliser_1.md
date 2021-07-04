# Créer un DataPersister personnaliser partie 1

On va Créer un DataPersister personnaliser, pour sauvegarder une nouvelle dépendance dans notre fichier composer.json 

Pour géré cela, on va creer manuellement une nouvelle classe nommé `DependencyDataPersister.php` dans un dossier nommé `DataPersister`

## 1) Créer un DataPersister personnalisé pour sauvegarder les données

- On crée manuellement le fichier `src/DataPersister/DependencyDataPersister.php`

- La classe `DependencyDataPersister` va implémenter :


    - `ContextAwareDataPersisterInterface ` avec ses 3 fonctions ci-dessous qui sont obligatoire pour sauvegarder des données, faire une restrition et supprimer des données :

        /**
        ** Vérifier si la variable $data est une instance de  Dependency
        ** {@inheritdoc}
        */
        public function supports($data, array $context = []): bool {}


        /**
        ** Permet de sauvegarder nos données dans la BDD,
        ** Nous on ne va pas sauvegarder nos données dans la BDD, on va les sauvegarder dans le fichier composer.json
        ** {@inheritdoc}
        */
        public function persist($data, array $context = []) {}


        /**
        ** permet de supprimer des données
        ** Pour nous, on supprimera nos dependances
        ** {@inheritdoc}
        */
        public function remove($data, array $context = []) {}


### Dans DataPersister/DependencyDataPersister.php

- La fonction `supports()` est appeler en premier lorsqu'une requête est faite, il vérifie si les données reçu dans la variable `$data` sont bien de type `Dependency`

    - Si c'est `true`, on pourra continuer le processus et excétuter les fonctions `persist()` et `remove()`, au choix

    - Si c'est `false`, on ne continue pas le processus

- Dans `supports()`

    - `return $data instanceof Dependency;`, vérifie si la variable `$data` est une instance de `Dependency`, et retourne un boolean


Dans `DataPersister/DependencyDataPersister.php`



    namespace App\DataPersister;

    use App\Entity\Dependency;
    use App\Repository\DependencyRepository;
    use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

    class DependencyDataPersister implements ContextAwareDataPersisterInterface 
    {
        
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
            
        }

        /**
        * permet de supprimer des données
        * Pour nous, on supprimera nos dependances
        * {@inheritdoc}
        */
        public function remove($data, array $context = [])
        {

        }
    }




### Dans Entity/Dependency.php

- On autorise la `collectionOperations` d'utiliser le verbe HTTP POST `"post"`

- On change un peut le constructeur, pour qu'on n'est pas besoin d'envoyer un `Uuid` lorsqu'on persiste, maintenant ça ce fera tout seul dans le constructeur avec le code ci-dessous, 

    $this->uuid = Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString();

- Pensez a modifier le fichier `DataProvider/DependencyDataProvider.php` dans `getCollection`

    Avant : 
    
    $items[] = new Dependency(Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString(), $name, $version);


    Après : 

    $items[] = new Dependency($name, $version);


Dans `Entity/Dependency.php`

    namespace App\Entity;

    use ApiPlatform\Core\Annotation\ApiProperty;
    use ApiPlatform\Core\Annotation\ApiResource;
    use Ramsey\Uuid\Uuid;

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

        public function __construct( string $name, string $version)
        {
            $this->uuid = Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString();
            $this->name = $name;
            $this->version = $version;
        }

