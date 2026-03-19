<?php

declare(strict_types=1);

namespace App\Form;

use App\Request\PhotoFilters;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhotoFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $rowAttr = ['row_attr' => ['class' => 'filter-field']];

        $builder
            ->add('location', TextType::class, $rowAttr + [
                'required' => false,
                'attr'     => ['placeholder' => 'e.g. Paris'],
            ])
            ->add('camera', TextType::class, $rowAttr + [
                'required' => false,
                'attr'     => ['placeholder' => 'e.g. Canon EOS'],
            ])
            ->add('description', TextType::class, $rowAttr + [
                'required' => false,
                'attr'     => ['placeholder' => 'keyword'],
            ])
            ->add('username', TextType::class, $rowAttr + [
                'required' => false,
                'attr'     => ['placeholder' => '@username'],
            ])
            ->add('takenAtFrom', DateTimeType::class, $rowAttr + [
                'required' => false,
                'widget'   => 'single_text',
                'input'    => 'datetime_immutable',
                'label'    => 'Taken from',
            ])
            ->add('takenAtTo', DateTimeType::class, $rowAttr + [
                'required' => false,
                'widget'   => 'single_text',
                'input'    => 'datetime_immutable',
                'label'    => 'Taken to',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => PhotoFilters::class,
            'method'          => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
