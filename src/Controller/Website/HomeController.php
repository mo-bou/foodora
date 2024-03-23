<?php

namespace App\Controller\Website;

use App\Repository\Product\SupplierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private SupplierRepository $supplierRepository,
    ) {
    }

    #[Route(path: '/', name: 'home')]
    public function home(Request $request): Response
    {
        $promotedSuppliers = $this->supplierRepository->findBy(['promotedToHome' => true], limit: 3);

        return $this->render(view: 'website/pages/home.html.twig', parameters: [
            'promotedSuppliers' => $promotedSuppliers
        ]);
    }

}
