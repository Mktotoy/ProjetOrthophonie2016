<?php
/**
 * Created by IntelliJ IDEA.
 * User: thaonzo
 * Date: 14/02/2017
 * Time: 14:53
 */

namespace UPOND\OrthophonieBundle\Controller;


use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\SubmitButton;
use UPOND\OrthophonieBundle\Entity\Medecin;
use UPOND\OrthophonieBundle\Entity\Multimedia;
use UPOND\OrthophonieBundle\Entity\Patient;
use UPOND\OrthophonieBundle\Entity\Utilisateur;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use UPOND\OrthophonieBundle\Form\UserSearchType;
use UPOND\OrthophonieBundle\Repository\StrategieRepository;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class RessourcesManagerController extends Controller
{
    public function imagesEditAction(Request $request){

        if ($request->getSession()->get('role') != 'medecin') {
            return $this->redirectToRoute('upond_orthophonie_home');
        }
        // on recupere l'exercice associ�e a la strategie, la phase, le niveau et la partie
        $page = isset($_GET['page'])?$_GET['page']:1;
        $limit = 25;
        //$pag = new Paginator();
        $em = $this->getDoctrine()->getManager();
        $MultimediaRepository = $em->getRepository('UPONDOrthophonieBundle:Multimedia');


        $counttotal = count($MultimediaRepository->findAll());
        $nbpages = ($counttotal%$limit)+1;

        $listMultimedia = $MultimediaRepository->findBy(
            array(),        // $where
            array(),    // $orderBy
            $limit,                        // $limit
            ($page-1)*$limit                          // $offset
        );

        //print_r(var_dump($MultimediaRepository));
        return $this->render('UPONDOrthophonieBundle:Administration:images_modification.html.twig', array('listMultimedias' => $listMultimedia,"nbpages"=> $nbpages,"page" => $page));
    }
    public function imagesCreateAction(Request $request){

        if ($request->getSession()->get('role') != 'medecin') {
            return $this->redirectToRoute('upond_orthophonie_home');
        }
        // on recupere l'exercice associ�e a la strategie, la phase, le niveau et la partie

        //print_r(var_dump($MultimediaRepository));
        return $this->render('UPONDOrthophonieBundle:Administration:images_creation.html.twig', array());
    }

    public function imagesEditUpdateAction(Request $request){

        if ($request->getSession()->get('role') != 'medecin') {
            return $this->redirectToRoute('upond_orthophonie_home');
        }


        if($request->getMethod() == 'POST') {


            $em = $this->getDoctrine()->getManager();
            $MultimediaRepository = $em->getRepository('UPONDOrthophonieBundle:Multimedia');

            // on recupere l'id utilisateur via le formulaire POST pr�c�dent
            $idMultimedias = $_POST['image'];
            $idSon = $_POST['son'];
            $textmedia = $_POST['text'];
            // on r�cup�re le patient
            foreach($idMultimedias as $k=>$idm){
                $actualmedia = $MultimediaRepository->find($idm);
                $actualmedia->setNom($textmedia[$k]);
            }

            $em->flush();
        }

        return $this->forward("UPONDOrthophonieBundle:RessourcesManager:imagesEdit");
    }
}