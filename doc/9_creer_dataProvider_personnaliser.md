# Créer un DataProvider personnalisé

on va créer un nouveau end Point qui va lister les dependances qu'on a dans notre projet, via le fichier `composer.json`


## Creer un objet qui va repésenter les données

On va créer un objet nommé `Entity/Dependency.php` qui contiendra le nom de la dépendance et la version de la dépendance


### Dans Dependency.php

- On crée nos propriétés privée `$uuid`, `$name` et `$version` 

- On crée leurs getteur

- On importe `use ApiPlatform\Core\Annotation\ApiResource;` pour utilisé `@ApiResource` en annotation

- On importe `use ApiPlatform\Core\Annotation\ApiProperty;` pour utilisé `@ApiProperty` en annotation

- Dans `@ApiResource`

    - `paginationEnabled=false,` On enlève la paganation

    - `collectionOperations={ "get" },` et `itemOperations={ "get" }` on veut afficher que la methode `GET`

- `@ApiProperty(identifier=true)`, pour la propriété `$uuid`, on met `identifier` a `true` pour dire a apiPlateform que `$uuid` sera l'indantifiant de l'entité `Dependency` à la place de `$id`

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

