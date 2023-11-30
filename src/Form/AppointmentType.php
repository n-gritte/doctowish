<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Appointment;
use App\Entity\Consultation;
use App\Repository\ConsultationRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\ChoiceList\Factory\Cache\ChoiceLabel;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $doctor_id = $options['doctor_id'];
        $builder
            ->add('start')
            ->add('consultation', EntityType::class, [
                'class' => Consultation::class,
                'choice_label' => 'title',
                'query_builder' => function(ConsultationRepository $c) use ($doctor_id){
                    return $c->createQueryBuilder('c')
                    ->join('c.doctor', 'd')
                    ->where('d.id = :doctor_id' )
                    ->setParameter('doctor_id', $doctor_id);
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
            'doctor_id' => null,
        ]);
    }
}
