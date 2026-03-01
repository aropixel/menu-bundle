<?php

namespace Aropixel\MenuBundle\Entity;

use Aropixel\MenuBundle\Repository\MenuRepository;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Handler\TreeSlugHandler;

#[ORM\MappedSuperclass(repositoryClass: MenuRepository::class)]
#[ORM\Table(name: 'aropixel_menu')]
#[Gedmo\Tree(type: 'nested')]
class Menu implements MenuInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected int $id;

    #[ORM\Column(type: Types::STRING, length: 50)]
    protected ?string $type = null;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $title = null;

    #[Gedmo\Slug(fields: ['title'])]
    #[Gedmo\SlugHandler(class: TreeSlugHandler::class, options: [
        'parentRelationField' => 'parent',
        'separator' => '/',
    ])]
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $originalTitle = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $link = null;

    protected ?string $linkDomain = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    protected ?string $staticPage = null;

    protected ?bool $isRequired = false;

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: Types::INTEGER)]
    protected int $left;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: Types::INTEGER)]
    protected int $level;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: Types::INTEGER)]
    protected int $right;

    #[Gedmo\TreeRoot]
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected ?int $root = null;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: MenuInterface::class, inversedBy: 'children', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?MenuInterface $parent = null;

    #[ORM\OneToMany(targetEntity: MenuInterface::class, mappedBy: 'parent', fetch: 'EAGER')]
    #[ORM\OrderBy(['left' => 'ASC'])]
    protected Collection $children;

    protected $page;

    protected ?bool $isActiveItem = false;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getOriginalTitle(): ?string
    {
        return $this->originalTitle;
    }

    public function setOriginalTitle(?string $originalTitle): self
    {
        $this->originalTitle = $originalTitle;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getLinkDomain(): ?string
    {
        $parsing = parse_url($this->link);
        if (!is_array($parsing)) {
            return null;
        }

        return $parsing['host'] ?? null;
    }

    public function setLinkDomain(?string $linkDomain): MenuInterface
    {
        $this->linkDomain = $linkDomain;

        return $this;
    }

    public function isActiveItem(): bool
    {
        return $this->isActiveItem;
    }

    public function setIsActiveItem(bool $isActiveItem): self
    {
        $this->isActiveItem = $isActiveItem;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    public function getStaticPage(): ?string
    {
        return $this->staticPage;
    }

    public function setStaticPage(?string $staticPage): self
    {
        $this->staticPage = $staticPage;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getLeft(): int
    {
        return $this->left;
    }

    public function setLeft(int $left): self
    {
        $this->left = $left;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getRight(): int
    {
        return $this->right;
    }

    public function setRight(int $right): self
    {
        $this->right = $right;

        return $this;
    }

    public function getRoot(): ?int
    {
        return $this->root;
    }

    public function setRoot(?int $root): self
    {
        $this->root = $root;

        return $this;
    }

    public function getParent(): ?MenuInterface
    {
        return $this->parent;
    }

    public function setParent(?MenuInterface $parent): self
    {
        $this->parent = $parent;

        if ($parent) {
            $parent->addChild($this);
        }

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection|array $children): void
    {
        if (\is_array($children)) {
            $children = new ArrayCollection($children);
        }

        $this->children = $children;
    }

    public function addChild(MenuInterface $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function isBlankTarget(): bool
    {
        return $this->isBlankTarget;
    }

    public function setIsBlankTarget(bool $isBlankTarget): self
    {
        $this->isBlankTarget = $isBlankTarget;

        return $this;
    }

    public function removeChild(MenuInterface $child): void
    {
        $this->children->removeElement($child);
    }
}
