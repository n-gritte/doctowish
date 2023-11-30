<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\Doctor;
use App\Form\AvailabilityType;
use App\Form\DoctorType;
use App\Form\ConsultationType;
use App\Repository\ConsultationRepository;
use App\Repository\DoctorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DoctorController extends AbstractController
{
    #[Route('/doctor', name: 'app_doctor')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $cabinets = $user->getDoctors();
        dump($cabinets);

        $doctor = new Doctor;
        $form = $this->createForm(DoctorType::class, $doctor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctor = $form->getData();
            $doctor->setUser($user);
            $em->persist($doctor);
            $em->flush();

        }

        return $this->render('doctor/index.html.twig', [
            'form' => $form->createView(),
            'cabinets' => $cabinets,
        ]);
    }

    #[Route('/doctor/{id}', name: 'app_doctor_edit')]
    public function editDoctor(Request $request, $id, DoctorRepository $dm, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $doctor = $dm->find($id);
        $consultation = new Consultation;
        $availability = $doctor->getAvailability() ?? [];
        $formDoctor = $this->createForm(DoctorType::class, $doctor);
        $formDoctor->handleRequest($request);

        $formConsultation = $this->createForm(ConsultationType::class, $consultation);
        $formConsultation->handleRequest($request);

        $formAvailability = $this->createForm(AvailabilityType::class);
        $formAvailability->handleRequest($request);

        if ($formDoctor->isSubmitted() && $formDoctor->isValid()) {
            $doctor = $formDoctor->getData();
            $doctor->setUser($user);
            $em->persist($doctor);
            $em->flush();
            $this->addFlash(
                'notice', 'Cabinet crée'
            );
        }

        if ($formConsultation->isSubmitted() && $formConsultation->isValid()) {
            $consultation = $formConsultation->getData();
            $consultation->setDoctor($doctor);
            $em->persist($consultation);
            $em->flush();
            $this->addFlash(
                'notice', 'Motif de consultation crée'
            );
        }

        if ($formAvailability->isSubmitted() && $formAvailability->isValid()) {
            $newAvailability = $formAvailability->getData();
            if ($this->isSlotAvailable($newAvailability['start'], $newAvailability['end'], $availability, $request)) {
                $availability[] = $newAvailability;
                $doctor->setAvailability($availability);
                $em->persist($doctor);
                $em->flush();
                $this->addFlash(
                    'notice', 'Créneau ajouté'
                );
            }else {
                $this->addFlash('error', 'Le créneau n\'est pas disponible. Veuillez choisir un autre créneau.');
            }
        }
        dump($availability);
        return $this->render('doctor/edit.html.twig',[
            'formDoctor' => $formDoctor->createView(),
            'formConsultation' => $formConsultation->createView(),
            'formAvailability' => $formAvailability->createView(),
            'availability' => $availability,
            'doctor' => $doctor,
        ]);
    }

    private function isSlotAvailable(\DateTimeInterface $start, \DateTimeInterface $end, array $availability, $request): bool
    {
        if ($availability === null) {
            return true;
        }

        foreach ($availability as $slot) {
            // dd($slot);
            // $slot = $request->getContent();
            
            $slotStart = new \DateTimeImmutable($slot['start']["date"]);
            $slotEnd = new \DateTimeImmutable($slot['end']["date"]);

            // Vérifiez si le nouveau créneau se superpose à un créneau existant
            if ($start >= $slotStart && $start < $slotEnd) {
                return false;
            }

            if ($end > $slotStart && $end <= $slotEnd) {
                return false;
            }
        }

        return true;
    }

    #[Route('/doctor/delete/{id}', name: 'app_doctor_delete')]
    public function deleteDoctor($id, DoctorRepository $dm, EntityManagerInterface $em): Response
    {
        $doctor = $dm->find($id);
        $em->remove($doctor);
        $em->flush();

        $this->addFlash(
            'notice',
            'Cabinet supprimé'
        );

        return $this->redirectToRoute('app_doctor');
    }

    #[Route('/doctor/deleteconsultation/{id}', name: 'app_doctor_consultationdelete')]
    public function deleteConsultation($id, ConsultationRepository $cm, EntityManagerInterface $em): Response
    {
        $consultation = $cm->find($id);
        $em->remove($consultation);
        $em->flush();

        $this->addFlash(
            'notice',
            'Motif de consultation supprimé'
        );

        return $this->redirectToRoute('app_doctor');
    }
}
