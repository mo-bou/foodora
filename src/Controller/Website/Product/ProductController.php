<?php

namespace App\Controller\Website\Product;

use App\Repository\Product\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    const PRODUCTS_PER_PAGE = 12;

    public function __construct(
        private ProductRepository $productRepository,
    ) {

    }

    #[Route(path: '/products', name: 'product_list')]
    public function list(Request $request): Response
    {
        $query = $request->get('q');
        $page = $request->get('page', default: 1);

        if (null !== $query) {
            $products = $this->productRepository->findByCodeOrDescriptionContainingString(searchString: $query, limit: self::PRODUCTS_PER_PAGE, page: $page);
        } else {
            $products = $this->productRepository->findAllPaginated(limit: self::PRODUCTS_PER_PAGE, page: $page);
        }

        return $this->render(view: 'website/pages/product/product_list.html.twig', parameters: [
            'products' => $products,
            'items_per_page' => self::PRODUCTS_PER_PAGE,
        ]);
    }
}
