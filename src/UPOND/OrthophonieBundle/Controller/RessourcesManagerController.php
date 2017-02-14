<?php
/**
 * Created by IntelliJ IDEA.
 * User: thaonzo
 * Date: 14/02/2017
 * Time: 14:53
 */

namespace UPOND\OrthophonieBundle\Controller;


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
        // on recupere l'exercice associée a la strategie, la phase, le niveau et la partie
        $em = $this->getDoctrine()->getManager();
        $MultimediaRepository = $em->getRepository('UPONDOrthophonieBundle:Multimedia');


        $listMultimedia = $MultimediaRepository->findAll();

        return $this->render('UPONDOrthophonieBundle:Administration:images_modification.html.twig', array('listMultimedias' => $listMultimedia));
    }
}