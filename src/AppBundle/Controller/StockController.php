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
     * @Route("/stock-option/update-values")
     * @Method({"POST"})
     */
    public function updateValuesAction()
    {
        /** @var StockOption[] $stockOptions */
        $stockOptions = $this->getDoctrine()->getRepository('AppBundle:StockOption')->findAll();

        foreach ($stockOptions as $stockOption) {
            $newValue = rand(20, 40);
            $stockOption->setValue((string)$newValue);

            $this->getDoctrine()->getManager()->persist($stockOption);
        }
        $this->getDoctrine()->getManager()->flush();

        return $this->json(['OK']);
    }

    /**
     * @Route("/stock-option")
     */
    public function stockIndexAction()
    {
        $stockOptions = $this->getDoctrine()->getRepository('AppBundle:StockOption')->findAll();

        return $this->json($stockOptions);
    }

    /**
     * @Route("/stock-option/{user}")
     */
    public function indexAction(Request $request, User $user)
    {
        $stockOptions = $user
            ->getTrades()
            ->toArray();

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
                'quantity' => (int)$quantity,
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
        list(
            $quantity,
            $total,
            $fee,
            $actualBalance,
            $newBalance
            ) = $this->parseBuy($request, $user, $stockOption);

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
                        'stock_option' => $stockOption,
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
                    'error' => 'Ocorreu um erro ao processar sua transação: '.$e->getMessage(),
                ],
                400
            );
        }

        return $this->json(['OK']);
    }

    /**
     * @param Request $request
     * @param User $user
     * @param StockOption $stockOption
     *
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
                    'error' => 'Quantidade de ações não disponível.',
                ],
                422
            );
        }

        if ($newBalance < 0) {
            return $this->json(
                [
                    'error' => 'Saldo insuficiente.',
                ],
                422
            );
        }
    }

    /**
     * @Route("/stock-option/{user}/{stockOption}/sell")
     * @Method({"GET"})
     * @param Request $request
     * @param User $user
     * @param StockOption $stockOption
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function preSellAction(Request $request, User $user, StockOption $stockOption)
    {
        /** @var Trade $trade */
        $trade = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Trade')
            ->findOneBy(
                [
                    'user' => $user,
                    'stock_option' => $stockOption,
                ]
            );

        if (!$trade instanceof Trade) {
            return $this->json(
                [
                    'error' => 'Usuário não possui ações da empresa '.$stockOption->getCompany(),
                ]
            );
        }

        $fee = 1.5;
        $tax = '15%';
        $actualBalance = $user->getBalance();
        $newBalance = $user->getBalance();

        if ($request->get('quantity')) {
            $quantity = $request->get('quantity');
            $validation = $this->validateSell($trade, $quantity);
            if ($validation) {
                return $validation;
            }

            $subTotal = $quantity * $stockOption->getValue();
            $total = $subTotal - $fee - ($subTotal * 0.15);

            $newBalance = (float)$user->getBalance() + $total;
        }

        return $this->json(
            [
                'trade' => $trade,
                'fee' => $fee,
                'tax' => $tax,
                'actualBalance' => $actualBalance,
                'newBalance' => $newBalance,
            ]
        );
    }

    /**
     * @Route("/stock-option/{user}/{stockOption}/sell")
     * @Method({"POST"})
     * @param Request $request
     * @param User $user
     * @param StockOption $stockOption
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function sellAction(Request $request, User $user, StockOption $stockOption)
    {
        /** @var Trade $trade */
        $trade = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Trade')
            ->findOneBy(
                [
                    'user' => $user,
                    'stock_option' => $stockOption,
                ]
            );

        $quantity = $request->get('quantity');
        $validation = $this->validateSell($trade, $quantity);
        if ($validation) {
            return $validation;
        }

        $fee = 1.5;

        $subTotal = $quantity * $stockOption->getValue();
        $total = $subTotal - $fee - ($subTotal * 0.15);

        $newBalance = (float)$user->getBalance() + $total;

        try {
            $trade->setQuantity($trade->getQuantity() - $quantity);
            $stockOption->setQuantity($stockOption->getQuantity() + $quantity);
            $user->setBalance($newBalance);

            $this->getDoctrine()->getManager()->persist($trade);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->persist($stockOption);

            if (0 === $trade->getQuantity()) {
                $this->getDoctrine()->getManager()->remove($trade);
            }

            $this->getDoctrine()->getManager()->flush();
        } catch (\Exception $e) {
            return $this->json(
                [
                    'error' => 'Ocorreu um erro ao processar sua transação: '.$e->getMessage(),
                ],
                400
            );
        }

        return $this->json(['OK']);
    }

    private function validateSell(Trade $trade, $quantity)
    {
        if ($trade->getQuantity() < $quantity) {
            return $this->json(
                [
                    'error' => 'Quantidade de ações não disponível.',
                ],
                422
            );
        }
    }
}