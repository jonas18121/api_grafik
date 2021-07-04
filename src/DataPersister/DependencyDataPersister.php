<?php

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

    }
}