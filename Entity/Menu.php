<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 16/04/2019 à 10:21
 */

namespace Aropixel\MenuBundle\Entity;

use Aropixel\PageBundle\Entity\Page;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


/**
 * Menu
 */
class Menu implements MenuInterface
{

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $originalTitle;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var string
     */
    protected $linkDomain;

    /**
     * @var bool
     */
    protected $isActiveItem = false;

    /**
     * @var bool
     */
    protected $isRequired = false;

    /**
     * @var string
     */
    protected $staticPage;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var integer
     */
    protected $left;

    /**
     * @var integer
     */
    protected $level;

    /**
     * @var integer
     */
    protected $right;

    /**
     * @var integer
     */
    protected $root;

    /**
     * @var MenuInterface
     */
    protected $parent;

    /**
     * @var MenuInterface[]
     */
    protected $children;

    /**
     * @var bool
     */
    private $isBlankTarget = false;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return MenuInterface
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
     * @return MenuInterface
     */
    public function setType(?string $type): MenuInterface
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
     * @return MenuInterface
     */
    public function setTitle(?string $title): MenuInterface
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
     * @return MenuInterface
     */
    public function setOriginalTitle(?string $originalTitle): MenuInterface
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
     * @return MenuInterface
     */
    public function setSlug($slug): MenuInterface
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
     * @return MenuInterface
     */
    public function setLink(?string $link): MenuInterface
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
     * @return MenuInterface
     */
    public function setLinkDomain(?string $linkDomain): MenuInterface
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
     * @return MenuInterface
     */
    public function setIsActiveItem(bool $isActiveItem): MenuInterface
    {
        $this->isActiveItem = $isActiveItem;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * @param bool $isRequired
     * @return Menu
     */
    public function setIsRequired(bool $isRequired): MenuInterface
    {
        $this->isRequired = $isRequired;
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
     * @return MenuInterface
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
     * @return MenuInterface
     */
    public function setPage(?Page $page): MenuInterface
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
     * @return MenuInterface
     */
    public function setLeft(int $left): MenuInterface
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
     * @return MenuInterface
     */
    public function setLevel(int $level): MenuInterface
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
     * @return MenuInterface
     */
    public function setRight(int $right): MenuInterface
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
     * @return MenuInterface
     */
    public function setRoot(?int $root): MenuInterface
    {
        $this->root = $root;
        return $this;
    }

    /**
     * @return MenuInterface
     */
    public function getParent(): MenuInterface
    {
        return $this->parent;
    }

    /**
     * @param self $parent
     * @return MenuInterface
     */
    public function setParent(self $parent): MenuInterface
    {
        $this->parent = $parent;

        $parent->addChild($this);

        return $this;
    }

    /**
     * @return MenuInterface[]|Collection
     */
    public function getChildren()
    {
        return $this->children;
    }


    /**
     * @param $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * Add child
     *
     * @param Menu $child
     *
     * @return self
     */
    public function addChild(Menu $child)
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isBlankTarget()
    {
        return $this->isBlankTarget;
    }

    /**
     * @param bool $isBlankTarget
     * @return Menu
     */
    public function setIsBlankTarget($isBlankTarget): MenuInterface
    {
        $this->isBlankTarget = $isBlankTarget;
        return $this;
    }

    /**
     * Remove child
     *
     * @param Menu $child
     */
    public function removeChild(Menu $child)
    {
        $this->children->removeElement($child);
    }


}
