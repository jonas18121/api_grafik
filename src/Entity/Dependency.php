<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

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
}