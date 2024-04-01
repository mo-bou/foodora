<?php

namespace App\Tests\Product\EventListener;

use App\Entity\Product\Product;
use App\Entity\Product\Supplier;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductPriceUpdateListenerTest extends KernelTestCase
{
    private ?EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        $this->entityManager = $container->get(id: 'doctrine')->getManager();
    }

    public function testPreUpdateListener()
    {
        $suppliers = $this->entityManager->getRepository(Supplier::class)->findAll();
        $product = new Product();
        $product->setCode('aaa01');
        $product->setPrice(10.5);
        $product->setSupplier($suppliers[0]);
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        $this->assertCount(1, $product->getPriceHistory());

        $product->setPrice(50);
        $this->entityManager->flush();
        $this->assertCount(2, $product->getPriceHistory());
    }
}
