<?php

namespace App\Controller;

use App\Entity\Speciality;
use App\Form\SpecialityType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $speciality = new Speciality;
        $formSpeciality = $this->createForm(SpecialityType::class, $speciality);
        $formSpeciality->handleRequest($request);

        if ($formSpeciality->isSubmitted() && $formSpeciality->isValid()) {
            $speciality = $formSpeciality->getData();
            $em->persist($speciality);
            $em->flush();
        }

        return $this->render('admin/index.html.twig', [
            'formSpeciality' => $formSpeciality->createView(),
        ]);
    }
}
