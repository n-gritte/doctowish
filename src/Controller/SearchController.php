<?php

namespace App\Controller;

use App\Repository\DoctorRepository;
use App\Repository\SpecialityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class SearchController extends AbstractController
{
    public function __construct(
        private ContainerBagInterface $params,
        private SpecialityRepository $specialityRepository,
        private DoctorRepository $doctorRepository
    ) {
    }
    
    #[Route('/search', name: 'app_search')]
    public function index(Request $request): Response
    {
        $terms = $request->request->get('terms');
        $specialities = $this->specialityRepository->getSearchSpeciality($terms);
        $doctors = $this->doctorRepository->getSearchDoctor($terms);
        $cities = $this->doctorRepository->getSearchDoctorCity($terms);

        return $this->render('search/index.html.twig', [
            'terms' => $terms,
            'specialities' => $specialities,
            'doctors' => $doctors,
            'cities' => $cities
        ]);
    }
}
