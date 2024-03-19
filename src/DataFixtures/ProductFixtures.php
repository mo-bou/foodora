<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Supplier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $productData = [
            [
                'code' => 'tom01',
                'price' => 1.25,
                'description' => 'tomate fraîche',
                'supplier' => $this->getReference(name: 'primeur', class: Supplier::class),
            ],
            [
                'code' => 'rad01',
                'price' => 4.50,
                'description' => 'Radis en botte',
                'supplier' => $this->getReference(name: 'primeur', class: Supplier::class),
            ],
            [
                'code' => 'con01',
                'price' => 1.25,
                'description' => 'Concombre',
                'supplier' => $this->getReference(name: 'primeur', class: Supplier::class),
            ],
            [
                'code' => 'tom01',
                'price' => 11.55,
                'description' => 'Tomme Bio',
                'supplier' => $this->getReference(name: 'fromager', class: Supplier::class),
            ],
            [
                'code' => 'moz01',
                'price' => 9.50,
                'description' => 'Mozzarella',
                'supplier' => $this->getReference(name: 'fromager', class: Supplier::class),
            ],

        ];

        foreach ($productData as $record) {
            $product = new Product();
            $product->setCode(code: $record['code']);
            $product->setPrice(price: $record['price']);
            $product->setDescription(description: $record['description']);
            $product->setSupplier($record['supplier']);
            $manager->persist($product);
        }

        $manager->flush();
    }


    public function getDependencies(): array
    {
        return [SupplierFixtures::class];
    }
}
