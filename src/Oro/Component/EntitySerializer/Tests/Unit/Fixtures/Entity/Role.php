<?php

namespace Oro\Component\EntitySerializer\Tests\Unit\Fixtures\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'role_table')]
class Role
{
    #[ORM\Column(name: 'code', type: Types::STRING, length: 50)]
    #[ORM\Id]
    private ?string $code;

    #[ORM\Column(name: 'label', type: Types::STRING, length: 255, unique: true)]
    private ?string $label = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: 'category_name', referencedColumnName: 'name')]
    private ?Category $category = null;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class)]
    #[ORM\JoinTable(name: 'rel_role_to_group_table')]
    #[ORM\JoinColumn(name: 'role_code', referencedColumnName: 'code', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'role_group_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Collection $groups = null;

    public function __construct(?string $code = null)
    {
        $this->code = $code;
        $this->groups = new ArrayCollection();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    public function removeGroup(Group $group): static
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }
}
