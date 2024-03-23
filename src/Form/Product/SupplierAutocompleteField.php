<?php

namespace App\Form\Product;

use App\Entity\Product\Supplier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class SupplierAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Supplier::class,
            'searchable_fields' => ['name'],
            'label' => 'supplier name',
            'choice_label' => 'name',
            'multiple' => false,
            'constraints' => [
                new Constraints\NotNull(message: 'Please select a Supplier'),
            ],
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
