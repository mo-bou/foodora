<?php

namespace App\DataFixtures;

use App\Entity\Supplier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SupplierFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $suppliersData = [
            [
                'name' => 'Primeur Deluxe',
                'ref' => 'primeur'
            ],
            [
                'name' => 'La rape à fromage',
                'ref' => 'fromager'
            ]
        ];

        $suppliers = [];
        foreach ($suppliersData as $record) {
            $supplier = new Supplier(name: $record['name']);
            $manager->persist(object: $supplier);
            $suppliers[$record['ref']] = $supplier;
        }

        $manager->flush();

        foreach ($suppliers as $key => $record) {
            $this->addReference(name: $key, object: $record);
        }
    }
}
