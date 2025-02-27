<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PagesController extends AbstractController
{
    private $emi;
    public function __construct(EntityManagerInterface $em) {
        $this->emi = $em;
    }

    #[Route('/', name: 'home_page')]
    public function index(): Response{
        return $this->render('pages/index.html.twig', [
            'name' => 'Osama',
            'country' => 'Tunisia',
        ]);
    }
    #[Route('/about', name: 'about_page')]
    public function about(): Response{
        return $this->render('pages/about.html.twig', [
            'name' => 'Osama',
        ]);
    }
    #[Route('/connexion', name: 'connexion_page')]
    public function login(Request $req): Response{
        if($req->isMethod('POST')){
            $email = $req->request->get('email');
            $password = $req->request->get('password');
            $user = $this->emi->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$user || !password_verify($password, $user->getPassword())) {
                return new Response("Invalid credentials", Response::HTTP_BAD_REQUEST);
            }
            return $this->redirectToRoute('home_page', [
                'name' => $user->getUsername(),
                'country' => 'Tunisia',
            ]);
        }
        return $this->render('pages/connexion.html.twig');
    }
    #[Route('/inscription', name: 'inscription_page')]
    public function signup(Request $req): Response{
        if($req->isMethod('POST')){
            $password = $req->request->get('password');
            $confirmPassword = $req->request->get('confirm_password');
            if ($password !== $confirmPassword) {
                return new Response("Passwords do not match!", Response::HTTP_BAD_REQUEST);
            }
            $user = new User();
            $user->setUsername($req->request->get('username'));
            $user->setEmail($req->request->get('email'));
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
            $this->emi->persist($user);
            $this->emi->flush();
            return $this->redirectToRoute('connexion_page');
        }

        return $this->render('pages/inscription.html.twig');
    }
}
