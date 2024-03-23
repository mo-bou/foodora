<?php

namespace App\Form\Product;

use App\Entity\Product\Mercurial;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\UX\Dropzone\Form\DropzoneType;

class MercurialImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $builder
            ->add('mercurial', DropZoneType::class,  [
                'label' => 'upload file',
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Constraints\File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'text/csv',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid CSV document',
                    ])
                ],
            ])
            ->add('supplier', SupplierAutocompleteField::class, [
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(defaults: [
            'data_class' => Mercurial::class
        ]);

    }
}
