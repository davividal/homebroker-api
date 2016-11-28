<?php
/**
 * Created by PhpStorm.
 * User: davi
 * Date: 26.11.16
 * Time: 17:48
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class UserController extends Controller
{
    /**
     * @Route("/login")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function loginAction(Request $request)
    {
        $body = json_decode($request->getContent());

        /** @var User $user */
        $user = $this
            ->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['login' => $body->login]);

        if ($user->isPasswordCorrect($body->password)) {
            return $this->json(['OK']);
        } else {
            return $this->json(['Ops'], 401);
        }
    }

    /**
     * @Route("/balance/{user}")
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function balanceAction(User $user)
    {
        return $this->json($user);
    }
}