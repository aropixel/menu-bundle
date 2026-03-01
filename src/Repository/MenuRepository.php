<?php

namespace Aropixel\MenuBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class MenuRepository extends NestedTreeRepository
{
    public function findRootsWithPage(): array
    {
        $qb = $this->createQueryBuilder('m');

        $qb->leftJoin('m.page', 'page')
            ->addSelect('page')
            ->where('m.parent IS NULL')
            ->orderBy('m.id', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    public function deleteMenu(string $type): void
    {
        $qb = $this->createQueryBuilder('m');

        $qb->delete($this->getClassName(), 'm')
            ->where('m.type = ?1')
            ->setParameter(1, $type)
        ;

        $qb->getQuery()->getResult();
    }
}
