<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Model\Track;
use AppBundle\Model\Type;
use AppBundle\Model\FormValidate;
use AppBundle\Model\Session;

class TrackController extends Controller
{
    /**
     * @Route("/track", name="track")
     */
    public function trackAction(Request $request)
    {
        $session = new Session($request->headers->get('Cookie'));

        if ($session->get('credentialId')) {
            $date = $session->get('tracksJsonPath');
            $date = preg_replace("/^.*\//", '', $date);
            $date = preg_replace("/.json/", '', $date);

            $track = new Track($session->get('tracksJsonPath'));

            return $this->render('track.html.twig', [
                "tracks" => $track->getTracks(),
                "date"   => $date
            ]);
        }
        else {
            return $this->redirectToRoute("login");
        }
    }

    /**
     * @Route("/create", name="create")
     */
    public function createAction(Request $request)
    {
        $session = new Session($request->headers->get('Cookie'));

        if ($session->get('credentialId')) {
            if ($request->isMethod('POST')) {
                $validate = new FormValidate($request);
                $valid    = $validate->validateCreatePayment();

                if (isset($valid['success'])) {
                    $date = $request->request->get('date');
                    $date = preg_replace("/-\d\d$/", '', $date);

                    $jsonPath = sprintf(
                        "data/tracks/%s/%s.json",
                        $session->get('credentialId'),
                        $date
                    );

                    $track = new Track($jsonPath);
                    $tracks = $track->getTracks();
                    $tracks[] = $request->request->all();

                    $track->saveTrack($tracks);

                    return $this->redirectToRoute("track");
                }
                else {
                    $errors = implode("<br>", $valid['errors']);

                    $this->addFlash(
                        'error',
                        $errors
                    );
                    
                    return $this->redirectToRoute("create");
                }
            }
            else {
                $type = new Type($session->get('typesJsonPath'));
                $now = new \DateTime('NOW');

                return $this->render('create.html.twig', [
                    'types' => $type->getTypes(),
                    'date'  => $now->format('Y-m-d')
                ]);
            }
        }
        else {
            return $this->redirectToRoute("login");
        }
    }

    /**
     * @Route("/edit/{index}", name="edit")
     */
    public function editAction($index, Request $request)
    {
        $session = new Session($request->headers->get('Cookie'));

        if ($session->get('credentialId')) {
            $track = new Track($session->get('tracksJsonPath'));
            $tracks = $track->getTracks();

            if ($request->isMethod('POST')) {
                $validate = new FormValidate($request);
                $valid    = $validate->validateCreatePayment();

                if (isset($valid['success'])) {
                    $tracks[$index] = $request->request->all();

                    $track->saveTrack($tracks);
                
                    return $this->redirectToRoute("track");
                }
                else {
                    $errors = implode("<br>", $valid['errors']);

                    $this->addFlash(
                        'error',
                        $errors
                    );

                    return $this->redirectToRoute("edit", ["index" => $index]);
                }
            }
            else {
                $type = new Type($session->get('typesJsonPath'));

                return $this->render('edit.html.twig', [
                    'types' => $type->getTypes(),
                    'track' => $tracks[$index]
                ]);
            }
        }
        else {
            return $this->redirectToRoute("login");
        }
    }

    /**
     * @Route("/delete/{index}", name="delete")
     */
    public function deleteAction($index, Request $request)
    {
        $session = new Session($request->headers->get('Cookie'));

        $track = new Track($session->get('tracksJsonPath'));
        $tracks = $track->getTracks();

        unset($tracks[$index]);

        $track->saveTrack(array_values($tracks));
        
        return $this->redirectToRoute("track");
    }

    /**
     * @Route("/changedate/{sub}", name="changedate")
     */
    public function changedateAction($sub, Request $request)
    {
        $session = new Session($request->headers->get('Cookie'));

        if ($session->get('credentialId')) {
            $now = new \DateTime('NOW'); 
            $year = $now->format('Y') - $sub;

            return $this->render('changedate.html.twig', [
                "year" => $year,
                "sub"  => $sub,
                "current" => $now->format('Y')
            ]);
        }
        else {
            return $this->redirectToRoute("login");
        }
    }

    /**
     * @Route("/setdate/{date}", name="setdate")
     */
    public function setdateAction($date, Request $request)
    {
        $session = new Session($request->headers->get('Cookie'));

        $jsonPath = sprintf(
            "data/tracks/%s/%s.json",
            $session->get('credentialId'),
            $date
        );
                
        $session->set('tracksJsonPath', $jsonPath);

        return $this->redirectToRoute("track");
    }
}
