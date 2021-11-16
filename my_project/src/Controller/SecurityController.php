<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="security_register")
     */
    public function create(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute("security_login");
        }
        return $this->render('User/create.html.twig', [
            'User' => $form->createView()
        ]);
    }


    /**
     * @Route("/login", name="security_login")
     */
    public function login()
    {
        return $this->render('User/login.html.twig');
    }


    /**
     * @Route("/")
     */
    public function acceuil()
    {

        return $this->redirectToRoute("your_profile");
    }

    /**
     * @Route("/home", name="your_profile")
     */
    public function profile(ProductRepository $product)
    {
        $products = $product->findAll();
        return $this->render('User/profile.html.twig', ["products" => $products]);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
    }

    /**
     * @Route("/admin")
     */
    public function adminPage(CartRepository $cart)
    {
        $fullCart = $cart->findAll();
        return $this->render('show.html.twig', ["cart" => $fullCart]);
    }
}
