<?php

namespace App\Controller;


use App\Entity\Cart;
use LogicException;
use App\Entity\User;
use App\Entity\Product;
use App\Form\CartType;
use App\Form\RegistrationType;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CartController extends AbstractController
{

    public $commandRequestWorkflow;

    public function __construct(WorkflowInterface $commandRequestWorkflow)
    {
        $this->commandRequestWorkflow = $commandRequestWorkflow;
    }


    /**
     * @Route("/new/{id}", name="app_new")
     */
    public function index(Request $request, EntityManagerInterface $entityManager, ProductRepository $product, $id): Response
    {
        $productId = $product->find($id);
        $cart = new Cart();

        $cart->setUser($this->getUser());
        $cart->setProduct($productId);

        $form = $this->createForm(CartType::class, $cart);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cart = $form->getData();
            try {
                $this->commandRequestWorkflow->apply($cart, 'to_pass');
            } catch (LogicException $exception) {
                //
            }

            $entityManager->persist($cart);
            $entityManager->flush();
            return $this->render('pay.html.twig', ['product' => $productId, "cart" => $cart]);
        }
        return $this->render('request.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/pay/{id}", name="app_pay")
     */
    public function pay(CartRepository $cartrep, EntityManagerInterface $entityManager, $id): Response
    {
        $cartOb = $cartrep->find($id);
        try {
            $this->commandRequestWorkflow->apply($cartOb, 'to_pay');
        } catch (LogicException $exception) {
            //
        }

        $entityManager->persist($cartOb);
        $entityManager->flush();
        return new Response("<html>payment is validated</html>");
    }

    /**
     * @Route("/cancel/{id}", name="app_cancel")
     */
    public function cancel(CartRepository $cartrep, EntityManagerInterface $entityManager, $id): Response
    {
        $cartOb = $cartrep->find($id);
        try {
            $this->commandRequestWorkflow->apply($cartOb, 'to_cancel');
        } catch (LogicException $exception) {
            //
        }
        $entityManager->remove($cartOb);
        $entityManager->flush();
        return new Response("<html>command is canceled</html>");
    }

    /**
     * @Route("/send/{id}", name="app_send")
     */
    public function send(CartRepository $cartrep, EntityManagerInterface $entityManager, $id): Response
    {
        $cartOb = $cartrep->find($id);
        try {
            $this->commandRequestWorkflow->apply($cartOb, 'to_send');
        } catch (LogicException $exception) {
            //
        }
        $entityManager->persist($cartOb);
        $entityManager->flush();
        return new Response("<html>command is sent</html>");
    }

    /**
     * @Route("/deliver/{id}", name="app_deliver")
     */
    public function deliver(CartRepository $cartrep, EntityManagerInterface $entityManager, $id): Response
    {
        $cartOb = $cartrep->find($id);
        try {
            $this->commandRequestWorkflow->apply($cartOb, 'to_deliver');
        } catch (LogicException $exception) {
            //
        }
        $entityManager->persist($cartOb);
        $entityManager->flush();
        return new Response("<html>command is delivered</html>");
    }

}
