<?php

namespace App\Controller\Admin\Product;

use App\Entity\Product\Mercurial;
use App\Form\Product\MercurialImportType;
use App\Message\Product\MercurialImport;
use App\Service\Product\MercurialImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\Turbo\TurboBundle;

class MercurialImportController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private MercurialImportService $mercurialImportService
    ){
    }
    #[Route(path: 'product/mercurial/import', name: 'product_mercurial_import')]
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
                    return $this->render('admin/product/mercurial-import.success.html.twig', ['supplier' => $mercurialImport->getSupplier()]);
                }
            } else {
                if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                    // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                    return $this->render('admin/product/mercurial-import.failure.html.twig', ['errors' => $form->getErrors(true)]);
                }
            }

        }

        return $this->render('admin/product/mercurial-import.html.twig', [
            'form' => $emptyForm->createView(),
            'errors' => $form->getErrors(true)
        ]);
    }
}
