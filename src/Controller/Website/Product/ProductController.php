<?php

namespace App\Controller\Website\Product;

use App\Repository\Product\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    const PRODUCTS_PER_PAGE = 20;

    public function __construct(
        private ProductRepository $productRepository,
    ) {

    }

    #[Route(path: '/products', name: 'product_list')]
    public function listProducts(Request $request)
    {
        $query = $request->get('q');
        $page = $request->get('page', default: 1);

        if (null !== $query) {
            $products = $this->productRepository->findByCodeOrDescriptionContainingString(searchString: $query, page: $page);
        } else {
            $products = $this->productRepository->findAllPaginated(page: $page);
        }


        return $this->render(view: 'website/pages/product/product_list.html.twig', parameters: [
            'products' => $products,
        ]);
    }
}
