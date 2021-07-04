<?php

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
            

            return null;
        }
    }

    /*
        {
            "name": "dependance de test",
            "version": "8.2.*"
        }
    */

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
}