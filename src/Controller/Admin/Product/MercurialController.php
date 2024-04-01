<?php

namespace App\Controller\Admin\Product;

use App\Entity\Product\Mercurial;
use App\Entity\Product\Product;
use App\Form\Product\MercurialImportType;
use App\Message\Product\MercurialImport;
use App\Message\Product\ProductUpdate;
use App\Repository\Product\MercurialRepository;
use App\Repository\Product\ProductRepository;
use App\Service\Product\MercurialImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\UX\Turbo\TurboBundle;

#[Route(path: 'product/')]
class MercurialController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface    $messageBus,
        private readonly MercurialImportService $mercurialImportService,
        private readonly MercurialRepository    $mercurialRepository,
        private readonly ValidatorInterface     $validator,
        private readonly ProductRepository      $productRepository,
    ){
    }

    #[Route(path: 'mercurial', name: 'product_mercurial_list')]
    #[Route(path: 'mercurial/list', name: 'product_mercurial_list')]
    public function list(Request $request): Response
    {
        $mercurials = $this->mercurialRepository->findAll();

        return $this->render('admin/product/mercurial_list.html.twig', ['mercurials' => $mercurials]);
    }

    #[Route(path: 'mercurial/{id}', name: 'product_mercurial_detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, ?Mercurial $mercurial): Response
    {
        if (null === $mercurial) {
            throw new NotFoundHttpException(sprintf('Mercurial `%s` was not found', $request->get('id')));
        }

        $productUpdateMessages = $this->mercurialImportService->getProductUpdateMessagesFromMercurial($mercurial);
        $productCodes = array_map(callback: function (ProductUpdate $productUpdate){
            return $productUpdate->getCode();
        }, array: $productUpdateMessages);

        //Todo : Refactor to only fetch the existing codes without retrieving the full entities (operation is more cost-effective)
        $existingProducts = $this->productRepository->findBy(['code' => $productCodes]);
        $existingProductCodes = array_map(callback: function (Product $product){
            return $product->getCode();
        }, array: $existingProducts);

        $errors = [];
        foreach ($productUpdateMessages as $productUpdate) {
            $errors[$productUpdate->getCode()] = $this->validator->validate($productUpdate);
        }

        return $this->render('admin/product/mercurial_detail.html.twig', [
            'mercurial' => $mercurial,
            'productUpdates' => $productUpdateMessages,
            'errors' => $errors,
            'existingProductCodes' => $existingProductCodes
        ]);
    }

    #[Route(path: 'mercurial/import', name: 'product_mercurial_import')]
    public function import(Request $request, SluggerInterface $slugger): Response
    {
        $mercurialImport = new Mercurial();
        $form = $this->createForm(type: MercurialImportType::class, data: $mercurialImport);
        $emptyForm = clone $form ;

        $form->handleRequest(request: $request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var UploadedFile $mercurialImportFile */
                $mercurialImportFile = $form->get('mercurial')->getData();
                $filename = $this->mercurialImportService->storeMercurialFileContents(
                    fileContents: $mercurialImportFile->getContent(),
                    supplierName: $mercurialImport->getSupplier()->getName()
                );

                $mercurialImportMessage = new MercurialImport(
                    filename: $filename,
                    supplierId: $mercurialImport->getSupplier()->getId()
                );
                $this->messageBus->dispatch(message: $mercurialImportMessage);

                if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                    // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                    return $this->render('admin/product/mercurial_import.success.html.twig', ['supplier' => $mercurialImport->getSupplier()]);
                }
            } else {
                if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                    // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                    return $this->render('admin/product/mercurial_import.failure.html.twig', ['errors' => $form->getErrors(true)]);
                }
            }

        }

        return $this->render('admin/product/mercurial_import.html.twig', [
            'form' => $emptyForm->createView(),
            'errors' => $form->getErrors(true)
        ]);
    }
}
