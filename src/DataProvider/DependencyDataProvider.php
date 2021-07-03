<?php

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
     * Récupère les dépandances qui son dans le fichier composer.json
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

            $items[] = new Dependency($name, $version);
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