<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

trait SluggedTrait
{
    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Gedmo\Slug(fields: ["name"], updatable: false)]
    private string $slug;

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return SluggedTrait|\App\Entity\Role
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}