<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 16/04/2019 à 10:21
 */

namespace Aropixel\MenuBundle\Entity;

use Aropixel\PageBundle\Entity\Page;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Aropixel\MenuBundle\Repository\MenuRepository")
 */
class Menu
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @Gedmo\Slug(handlers={
     *      @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\TreeSlugHandler", options={
     *          @Gedmo\SlugHandlerOption(name="parentRelationField", value="parent"),
     *          @Gedmo\SlugHandlerOption(name="separator", value="/")
     *      })
     * }, fields={"title"})
     * @Doctrine\ORM\Mapping\Column(length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $originalTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $link;


    /** @var string */
    private $linkDomain;


    /** @var bool */
    private $isActiveItem = false;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $staticPage;

    /**
     * @var Page
     */
    private $page;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer", name="lft")
     */
    private $left;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer", name="lvl")
     */
    private $level;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer", name="rgt")
     */
    private $right;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Menu", cascade={"persist"}, inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="parent")
     * @ORM\OrderBy({"left" = "ASC"})
     */
    private $children;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Menu
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return Menu
     */
    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return Menu
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOriginalTitle(): ?string
    {
        return $this->originalTitle;
    }

    /**
     * @param string|null $originalTitle
     * @return Menu
     */
    public function setOriginalTitle(?string $originalTitle): self
    {
        $this->originalTitle = $originalTitle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     * @return Menu
     */
    public function setSlug($slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string|null $link
     * @return Menu
     */
    public function setLink(?string $link): self
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLinkDomain(): ?string
    {
        return $this->linkDomain;
    }

    /**
     * @param string|null $linkDomain
     * @return Menu
     */
    public function setLinkDomain(?string $linkDomain): self
    {
        $this->linkDomain = $linkDomain;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActiveItem(): bool
    {
        return $this->isActiveItem;
    }

    /**
     * @param bool $isActiveItem
     * @return Menu
     */
    public function setIsActiveItem(bool $isActiveItem): self
    {
        $this->isActiveItem = $isActiveItem;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStaticPage()
    {
        return $this->staticPage;
    }

    /**
     * @param mixed $staticPage
     * @return Menu
     */
    public function setStaticPage($staticPage)
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

    /**
     * @param string|null $page
     * @return Menu
     */
    public function setPage(?Page $page): self
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getLeft(): int
    {
        return $this->left;
    }

    /**
     * @param int $left
     * @return Menu
     */
    public function setLeft(int $left): self
    {
        $this->left = $left;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     * @return Menu
     */
    public function setLevel(int $level): self
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return int
     */
    public function getRight(): int
    {
        return $this->right;
    }

    /**
     * @param int $right
     * @return Menu
     */
    public function setRight(int $right): self
    {
        $this->right = $right;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRoot(): ?int
    {
        return $this->root;
    }

    /**
     * @param int|null $root
     * @return Menu
     */
    public function setRoot(?int $root): self
    {
        $this->root = $root;
        return $this;
    }

    /**
     * @return self
     */
    public function getParent(): self
    {
        return $this->parent;
    }

    /**
     * @param self $parent
     * @return Menu
     */
    public function setParent(self $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return self[]|Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param self[]|Collection $children
     * @return Menu
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

}
