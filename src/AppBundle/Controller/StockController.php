<?php
/**
 * Created by PhpStorm.
 * User: davi
 * Date: 26.11.16
 * Time: 16:12
 */

namespace AppBundle\Controller;

use AppBundle\Entity\StockOption;
use AppBundle\Entity\Trade;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class StockController extends Controller
{
    /**
     * @Route("/stock-option/{user}")
     */
    public function indexAction(Request $request, User $user)
    {
        $stockOptions = $user->getTrades()->toArray();
//        dump($stockOptions); die;

//        $stockOptions = $this->get('doctrine')->getRepository('AppBundle:Trade')->findAll();

        return $this->json($stockOptions);
    }

    /**
     * @Route("/stock-option/{user}/{stockOption}")
     */
    public function showAction(Request $request, User $user, StockOption $stockOption)
    {
        return $this->json($stockOption);
    }

    /**
     * @Route("/stock-option/{user}/{stockOption}/buy")
     * @Method({"GET"})
     */
    public function preBuyAction(Request $request, User $user, StockOption $stockOption)
    {
        list($quantity, $total, $fee, $actualBalance, $newBalance) = $this->parseBuy($request, $user, $stockOption);

        $validation = $this->validateBuy($stockOption, $quantity, $newBalance);
        if ($validation) {
            return $validation;
        }

        return $this->json(
            [
                'total' => $total,
                'actualBalance' => $actualBalance,
                'newBalance' => $newBalance,
                'fee' => $fee,
                'stockOption' => $stockOption,
            ]
        );
    }

    /**
     * @Route("/stock-option/{user}/{stockOption}/buy")
     * @Method({"POST"})
     */
    public function buyAction(Request $request, User $user, StockOption $stockOption)
    {
        list($quantity, $total, $fee, $actualBalance, $newBalance) = $this->parseBuy($request, $user, $stockOption);

        $validation = $this->validateBuy($stockOption, $quantity, $newBalance);
        if ($validation) {
            return $validation;
        }

        try {
            $trade = $this
                ->getDoctrine()
                ->getRepository('AppBundle:Trade')
                ->findOneBy(
                    [
                        'user' => $user,
                        'stock_option' => $stockOption
                    ]
                );

            if (!$trade instanceof Trade) {
                /** @var Trade $trade */
                $trade = new Trade();
                $trade->setUser($user);
                $trade->setStockOption($stockOption);
            }

            $trade->setQuantity($quantity + $trade->getQuantity());

            $stockOption->setQuantity($stockOption->getQuantity() - $quantity);
            $user->setBalance($user->getBalance() - $total);

            $this->getDoctrine()->getManager()->persist($trade);
            $this->getDoctrine()->getManager()->persist($stockOption);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
        } catch (\Exception $e) {
            return $this->json(
                [
                    'error' => 'Ocorreu um erro ao processar sua transação: ' . $e->getMessage()
                ],
                400
            );
        }

        return $this->json(['OK']);
    }

    /**
     * @Route("/dashboard/buy-stocks/teste", name="buy-stocks")
     */
    public function oldBuyAction(Request $request)
    {
        /** @var Trade $trade */
        $trade = new Trade();

        $form = $this->createFormBuilder($trade)
            ->add(
                'stockOption',
                EntityType::class,
                [
                    'class' => 'AppBundle:StockOption',
                    'label' => 'Empresa',
                ]
            )
            ->add('quantity', IntegerType::class, ['label' => 'Quantidade'])
            ->add('save', SubmitType::class, ['label' => 'Comprar'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!($trade->getQuantity() <= $trade->getStockOption()->getQuantity())) {
                $form
                    ->get('stockOption')
                    ->addError(new FormError('Esta empresa não possui essa quantidade de ações disponíveis.'));
            }

            $transactionValue = $trade->getStockOption()->getValue() * $trade->getQuantity();
            if (!($transactionValue <= $this->getUser()->getBalance())) {
                $form
                    ->get('quantity')
                    ->addError(new FormError('Seu saldo é insuficiente para executar esta transação.'));
            }

            if ($form->isValid()) {

                $trade = $form->getData();
                $trade->setUser($this->getUser());

                $trade->setPaid($trade->getStockOption()->getValue());


                $newBalance = $this->getUser()->getBalance() - $transactionValue;

                $this->getUser()->setBalance($newBalance);

                $newQuantity = $trade->getStockOption()->getQuantity() - $trade->getQuantity();
                $trade->getStockOption()->setQuantity($newQuantity);

                // ... perform some action, such as saving the task to the database
                // for example, if Task is a Doctrine entity, save it!
                $em = $this->getDoctrine()->getManager();
                $em->persist($trade);
                $em->flush();

                return $this->redirectToRoute('user-dashboard');
            }
        }

        return $this->render(
            'home-broker/buy-stocks.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     * @param User $user
     * @param StockOption $stockOption
     * @return array
     */
    private function parseBuy(Request $request, User $user, StockOption $stockOption)
    {
        $quantity = $request->get('quantity');
        $total = $stockOption->getValue() * $quantity;
        $fee = 1.5;
        $actualBalance = $user->getBalance();
        $newBalance = $user->getBalance() - $total - $fee;

        return [$quantity, $total, $fee, $actualBalance, $newBalance];
    }

    private function validateBuy(StockOption $stockOption, $quantity, $newBalance)
    {
        if ($stockOption->getQuantity() < $quantity) {
            return $this->json(
                [
                    'error' => 'Quantidade de ações não disponível.'
                ],
                422
            );
        }

        if ($newBalance < 0) {
            return $this->json(
                [
                    'error' => 'Saldo insuficiente.'
                ],
                422
            );
        }
    }
}