<?php

namespace App\Form;

use App\Entity\Doctor;
use App\Entity\Speciality;
use App\Repository\SpecialityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DoctorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('siret')
            ->add('address1')
            ->add('address2')
            ->add('city')
            ->add('zipcode')
            ->add('country')
            ->add('specialities', EntityType::class, [
                'class' => Speciality::class,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'title',
                'query_builder' => function(SpecialityRepository $sr){
                    return $sr->createQueryBuilder('s')
                    ->orderBy('s.title', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Doctor::class,
        ]);
    }
}
