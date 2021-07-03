<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Dependency;

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