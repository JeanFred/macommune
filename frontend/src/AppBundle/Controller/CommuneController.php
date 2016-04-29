<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Entity\Commune;

class CommuneController extends Controller
{
	public function computeSectionsState($sections)
	{
		$result = array();
		$keys = array(
			"Économie" => "economie",
			"Géographie" => "geographie",
			"Histoire" => "histoire",
			"Politique et administration" => "politique",
			"Population et société" => "population",
			"Culture locale et patrimoine" => "culture",
			"Toponymie" => "toponymie",
			"Urbanisme" => "urbanisme"
		);
		foreach($sections as $section)
		{
			$title = $section->getTitle();
			if (isset($keys[$title])) {
				$key = $keys[$title];
				$result[$key] = $section;
			}
		}
		return $result;
	}

	public function renderCommune($commune)
	{
		$session = new Session();
		$session->set("commune_title", $commune->getTitle());
		$session->set("commune_wpTitle", $commune->getWpTitle());
		$session->set("commune_qid", $commune->getQid());

		$response = $this->render('communes/show.html.twig', array(
			"commune" => $commune,
			"sections" => $this->computeSectionsState($commune->getSections())
		));

		return $response;
	}

	/**
	* @Route("/commune/{qid}/show", name="communeShow")
	*/
	public function showAction(Request $request, $qid)
	{
		$em = $this->getDoctrine();
		$commune = $em->getRepository('AppBundle:Commune')->find($qid);
		if (!$commune) throw $this->createNotFoundException('Pas de commune trouvée pour #'.$qid);
		return $this->renderCommune($commune);
	}

	/**
	* @Route("/commune/search", name="communeSearch")
	*/
	public function indexAction(Request $request)
	{
		$title = $request->get("title");
		$em = $this->getDoctrine();
		$communes = $em->getRepository('AppBundle:Commune')->findByTitle($title);

		if (count($communes) == 0) {
			return $this->render('index.html.twig', array("error" => "Cette commune n’existe pas dans notre base"));
		}
		elseif (count($communes) == 1) {
			$commune = $communes[0];
			return $this->renderCommune($commune);
		}
		else {
			return $this->render('communes/list.html.twig', array("communes" => $communes));
		}
	}
}
