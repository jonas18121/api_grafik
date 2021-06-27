<?php

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