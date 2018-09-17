<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Model\Track;
use AppBundle\Model\Type;
use AppBundle\Model\FormValidate;

class TrackController extends Controller
{
    /**
     * @Route("/track", name="track")
     */
    public function trackAction(Request $request)
    {
        if ($this->get('session')->get('credentialId')) {
            $date = $this->get('session')->get('tracksJsonPath');
            $date = preg_replace("/^.*\//", '', $date);
            $date = preg_replace("/.json/", '', $date);

            $track = new Track($this->get('session')->get('tracksJsonPath'));

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
        if ($this->get('session')->get('credentialId')) {
            if ($request->isMethod('POST')) {
                $validate = new FormValidate($request);
                $valid    = $validate->validateCreatePayment();

                if (isset($valid['success'])) {
                    $date = $request->request->get('date');
                    $date = preg_replace("/-\d\d$/", '', $date);

                    $jsonPath = sprintf(
                        "%s/tracks/%s/%s.json",
                        $this->getParameter('data'),
                        $this->get('session')->get('credentialId'),
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
                $type = new Type($this->get('session')->get('typesJsonPath'));
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
        if ($this->get('session')->get('credentialId')) {
            $track = new Track($this->get('session')->get('tracksJsonPath'));
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
                $type = new Type($this->get('session')->get('typesJsonPath'));

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
        $track = new Track($this->get('session')->get('tracksJsonPath'));
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
        if ($this->get('session')->get('credentialId')) {
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
        $jsonPath = sprintf(
            "%s/tracks/%s/%s.json",
            $this->getParameter('data'),
            $this->get('session')->get('credentialId'),
            $date
        );
                
        $this->get('session')->set('tracksJsonPath', $jsonPath);

        return $this->redirectToRoute("track");
    }
}
