<?php

namespace App\Controller;

use DateInterval;
use App\Entity\Doctor;
use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\DoctorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(DoctorRepository $dm): Response
    {
        $doctors = $dm->findAll();
        return $this->render('index.html.twig', [
            'doctors' => $doctors,
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/newrdv/{id}', name: 'app_rdv')]
    public function addRdv(Request $request, DoctorRepository $dm, EntityManagerInterface $em, $id): Response
    {
        $user = $this->getUser();
        $doctor = $dm->find($id);
        $appointment = new Appointment;

        $formAppointment = $this->createForm(AppointmentType::class, $appointment, ['doctor_id' => $doctor->getId()]);
        $formAppointment->handleRequest($request);

        if ($formAppointment->isSubmitted() && $formAppointment->isValid()) {
            $appointment = $formAppointment->getData();
            $appointment->setUser($user);
            $appointment->setDoctor($doctor); 
            $start = $formAppointment->get('start')->getData();
            $end = $start->add(new DateInterval('PT' . strval($formAppointment->get('consultation')->getData()->getDuration()) . 'M'));
            $appointment->setEnd($end);
            $appointment->setStatut('A venir');
            // dd($doctor->getAvailability());
            if ($this->isAppointmentAvailable($start, $end, $doctor) === false){
                // dd('BIFBOF');
                $this->addFlash('error', 'Un rendez-vous existe déjà pendant les horaires sélectionnés.');
                return $this->redirectToRoute('app_rdv',['id' => $doctor->getId()]);
            }
            // dd('YEAH');
            $this->removeSlotFromAvailability($start, $end, $doctor);
            
            $em->persist($appointment);
            $em->flush();
            $this->addFlash('notice', 'Rdv crée');
        }

        return $this->render('addrdv.html.twig', [
            'doctor' => $doctor,
            'formAppointment' => $formAppointment->createView()
        ]);
    }

    /**
     * Vérifie si un rendez-vous est disponible pendant les horaires sélectionnés.
     *
     * @param \DateTimeImmutable $start
     * @param \DateTimeImmutable $end
     * @param Doctor $doctor
     * @return bool
     */
    private function isAppointmentAvailable(\DateTimeImmutable $start, \DateTimeImmutable $end, Doctor $doctor): bool
    {
        $availability = $doctor->getAvailability();
        
        if ($availability === null) {
            return false;
        }

        foreach ($availability as $key=>$slot) {
            
            $slotStart = new \DateTimeImmutable($availability[$key]['start']['date']);
            $slotEnd = new \DateTimeImmutable($availability[$key]['end']['date']);
            // dump($slotStart);
            // dump($slotEnd);
            // dump($start);
            // dd($end);
            if ($start >= $slotStart && $end <= $slotEnd) {
                return true;
            }
        }
        // [{"start":{"date":"2023-11-25 09:00:00.000000","timezone_type":3,"timezone":"Europe\/Berlin"},"end":{"date":"2023-11-23 20:00:00.000000","timezone_type":3,"timezone":"Europe\/Berlin"}}]
        return false;
    }

    /**
     * Retire le créneau horaire occupé par le rendez-vous de la disponibilité du praticien.
     *
     * @param \DateTimeImmutable $start
     * @param \DateTimeImmutable $end
     * @param Doctor $doctor
     */
    private function removeSlotFromAvailability(\DateTimeImmutable $start, \DateTimeImmutable $end, Doctor $doctor): void
    {
        $availability = $doctor->getAvailability();

        if ($availability === null) {
            return;
        }

        $adjustedAvailability = [];
        
        foreach ($availability as $key=>$slot) {
            $slotStart = new \DateTimeImmutable($availability[$key]['start']['date']);
            $slotEnd = new \DateTimeImmutable($availability[$key]['end']['date']);

            if ($end <= $slotStart || $start >= $slotEnd) {

                $adjustedAvailability[] = $slot;
            } else {

                if ($start > $slotStart) {
                    $adjustedAvailability[] = [
                        'start' => $slot['start'],
                        'end' => [
                            'date' => $start->format('Y-m-d H:i:s'),
                            'timezone_type' => $slot['end']['timezone_type'],
                            'timezone' => $slot['end']['timezone'],
                        ],
                    ];
                }

                if ($end < $slotEnd) {
                    $adjustedAvailability[] = [
                        'start' => [
                            'date' => $end->format('Y-m-d H:i:s'),
                            'timezone_type' => $slot['start']['timezone_type'],
                            'timezone' => $slot['start']['timezone'],
                        ],
                        'end' => $slot['end'],
                    ];
                }
            }
        }

        $doctor->setAvailability($adjustedAvailability);
    }
}