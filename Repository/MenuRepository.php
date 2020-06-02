<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 06/05/2019 à 14:14
 */

namespace Aropixel\MenuBundle\Repository;

use Aropixel\MenuBundle\Entity\Menu;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;


class MenuRepository extends NestedTreeRepository
{

    /**
     * Get root items with page info in same query
     */
    public function findRootsWithPage() {


        $qb = $this->createQueryBuilder('m');

        $qb->leftJoin('m.page', 'page')
            ->addSelect('page')
            ->where('m.parent IS NULL');

        $query = $qb->getQuery();
        return $query->getResult();

    }

    /**
     * @param string $type
     */
    public function deleteMenu($type) {


        $qb = $this->createQueryBuilder('m');

        $qb->delete($this->getClassName(), 'm')
            ->where('m.type = ?1')
            ->setParameter(1, $type);

        $query = $qb->getQuery();
        $query->getResult();

    }


}
