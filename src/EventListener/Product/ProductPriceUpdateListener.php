<?php

namespace App\EventListener\Product;

use App\Entity\Product\Product;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;


#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Product::class)]
#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Product::class)]
class ProductPriceUpdateListener
{
    public function preUpdate(Product $product, PreUpdateEventArgs $event): void
    {
        $unitOfWork = $event->getObjectManager()->getUnitOfWork();
        $changeSet = $unitOfWork->getEntityChangeSet($product);

        if (in_array(needle: 'price', haystack: array_keys($changeSet))) {
            $newPrice = $changeSet['price'][1];
            $product->addPriceHistory(newPrice: $newPrice);
        }
    }

    public function prePersist(Product $product, PrePersistEventArgs $event): void
    {
        $product->addPriceHistory(newPrice: $product->getPrice());
    }
}
