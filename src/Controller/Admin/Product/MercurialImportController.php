<?php

namespace App\Controller\Admin\Product;

use App\Entity\Product\Mercurial;
use App\Form\Product\MercurialImportType;
use App\Message\Product\MercurialImport;
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
        private EntityManagerInterface $em,
        private MessageBusInterface $messageBus,
    ){

    }
    #[Route(path: 'product/mercurial/import', name: 'product_mercurial_import')]
    public function import(Request $request, SluggerInterface $slugger): Response
    {
        $mercurialImport = new Mercurial();
        $form = $this->createForm(type: MercurialImportType::class, data: $mercurialImport);
        $emptyForm = clone $form ;

        $form->handleRequest(request: $request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $mercurialImportFile */
            $mercurialImportFile = $form->get('mercurial')->getData();
            $supplierName = $mercurialImport->getSupplier()->getName();
            $uploadDirectory = $this->getParameter('app.product.mercurial_csv_tmp_dir');
            $filename = $slugger->slug($supplierName).'_'.uniqid().'.'.$mercurialImportFile->guessExtension();
            $mercurialImportFile->move(directory: $this->getParameter('app.product.mercurial_csv_tmp_dir'), name: $filename);
            $mercurialImport->setFilePath(filePath: $filename);

            $this->em->persist($mercurialImport);
            $this->em->flush();

            $mercurialImportMessage = new MercurialImport(
                filename: $uploadDirectory.'/'.$filename,
                supplierId: $mercurialImport->getSupplier()->getId()
            );

            $this->messageBus->dispatch(message: $mercurialImportMessage);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('admin/product/mercurial-import.success.html.twig', ['supplier' => $mercurialImport->getSupplier()]);
            }
        }


        return $this->render('admin/product/mercurial-import.html.twig', [
            'form' => $emptyForm->createView()
        ]);
    }
}
