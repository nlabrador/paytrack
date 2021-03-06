<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Model\Track;
use AppBundle\Model\Credential;
use AppBundle\Model\Session;
use AppBundle\Model\FormValidate;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $session = new Session($request->headers->get('Cookie'));

        if ($session->get('credentialId')) {
            $obj    = new Track($session->get('tracksJsonPath'));
            $tracks = $obj->getAllTracks();
            $types  = [];

            foreach ($tracks as $track) {
                $total = 0;
                $count = 0;

                if (isset($types[$track->type])) {
                    $total = $track->amount + $types[$track->type]['total'];
                    $count = $types[$track->type]['count'] + 1;
                }
                else {
                    $total = $track->amount;
                    $count = 1;
                }

                $types[$track->type] = [
                    "total" => $total,
                    "count" => $count,
                    "average" => ($count > 1) ? $total/$count : $total
                ];
            }

            return $this->render('home.html.twig', [
                "types" => $types
            ]);
        }
        else {
            return $this->render('default/index.html.twig');
        }
    }

    /**
     * @Route("/signup", name="signup")
     */
    public function signupAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $validate = new FormValidate($request);
            $valid    = $validate->validateSignup();

            if (isset($valid['success'])) {
                $credential = new Credential(); 

                if ($credential->find($request->request->get('email'))) {
                    $this->addFlash(
                        'error',
                        'Email address already exists. Forget your password?'
                    );

                    return $this->redirectToRoute("signup");
                }
                else {
                    if ($credential->create($request->request->get('email'), $request->request->get('password'))) {
                        return $this->redirectToRoute("login");
                    }
                    else {
                        $this->addFlash(
                            'error',
                            'This is embarassing. We have informed our team about the issue.'
                        );
                        return $this->redirectToRoute("signup");
                    }
                }
            }
            else {
                $errors = implode("<br>", $valid['errors']);

                $this->addFlash(
                    'error',
                    $errors
                );

                return $this->redirectToRoute("signup");
            }
        }
        else {
            return $this->render('default/signup.html.twig');
        }
    }

    /**
     * @Route("/forgot", name="forgot")
     */
    public function forgotAction(Request $request)
    {
        return $this->render('default/forgot.html.twig');
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $credential = new Credential(); 

            if ($credential->validate($request->request->get('emailAddress'), $credential->generatePassKey($request->request->get('password')))) {
                $session = new Session($request->headers->get('Cookie'));

                $session->set('credentialId', md5($request->request->get('emailAddress')));

                $now = new \DateTime('NOW');
                $jsonPath = sprintf(
                    "data/tracks/%s/%s.json",
                    $session->get('credentialId'),
                    $now->format('Y-m')
                );
                
                $session->set('tracksJsonPath', $jsonPath);

                return $this->redirectToRoute("homepage");
            }
            else {
                return $this->redirectToRoute("login");
            }
        }
        else {
            return $this->render('default/login.html.twig');
        }
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request)
    {
        $session = new Session($request->headers->get('Cookie'));

        $session->set('credentialId', null);
        $session->set('tracksJsonPath', null);

        return $this->redirectToRoute("login");
    }
}
