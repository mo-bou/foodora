<?php

namespace App\Controller\Admin\Product;

use App\Entity\Product\Product;
use App\Repository\Product\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/products', name: 'product_')]
class ProductController extends AbstractController
{
    #[Route(path: ['/', ''], name: 'list')]
    public function list(Request $request, ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render(view: 'admin/product/list.html.twig', parameters: [
            'products' => $products
        ]);
    }

    #[Route(path: ['/{id}'], name: 'detail')]
    public function get(Request $request, ?Product $product, EntityManagerInterface $em): Response
    {
        if (null === $product) {
            throw new NotFoundHttpException();
        }

        return $this->render(view: 'admin/product/detail.html.twig', parameters: [
            'product' => $product
        ]);
    }
}
