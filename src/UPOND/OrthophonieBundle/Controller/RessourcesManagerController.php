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
use Symfony\Component\HttpKernel\Kernel;
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

use Symfony\Bridge\Twig\Extension\AssetExtension;

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
        return $this->render('UPONDOrthophonieBundle:Administration:medias_list.html.twig', array('listMultimedias' => $listMultimedia,"nbpages"=> $nbpages,"page" => $page));
    }
    public function imagesCreateAction(Request $request){

        if ($request->getSession()->get('role') != 'medecin') {
            return $this->redirectToRoute('upond_orthophonie_home');
        }
        // on recupere l'exercice associ�e a la strategie, la phase, le niveau et la partie

        //print_r(var_dump($MultimediaRepository));
        return $this->render('UPONDOrthophonieBundle:Administration:media_create_update.html.twig', array("action" => "Create"));
    }

    public function imagesCreateDoAction(Request $request){

        if ($request->getSession()->get('role') != 'medecin') {
            return $this->redirectToRoute('upond_orthophonie_home');
        }

        $maxsize = ini_get('post_max_size');

        //TEST SI FICHIERS CONFORMES
        $error_tab= array(
            UPLOAD_ERR_NO_FILE => "fichier manquant",
            UPLOAD_ERR_INI_SIZE => "fichier dépassant la taille maximale autorisée par PHP.",
            UPLOAD_ERR_FORM_SIZE => "fichier dépassant la taille maximale autorisée par le formulaire.",
            UPLOAD_ERR_PARTIAL => "fichier transféré partiellement."
        );

        if ($_FILES['img']['error'] > 0)
            return $this->render('UPONDOrthophonieBundle:Administration:media_create_update.html.twig', array("action" => "Create","erreur_img" => $error_tab[$_FILES['img']['error']].$maxsize));
        if ($_FILES['audio']['error'] > 0)
            return $this->render('UPONDOrthophonieBundle:Administration:media_create_update.html.twig', array("action" => "Create","erreur_audio" =>  $error_tab[$_FILES['audio']['error']].$maxsize));


        $img_extensions_valides = array( 'jpg' , 'jpeg' , 'png');
        $audio_extensions_valides = array( 'mp3');
        //1. strrchr renvoie l'extension avec le point (« . »).
        //2. substr(chaine,1) ignore le premier caractère de chaine.
        //3. strtolower met l'extension en minuscules.
        $img_extension_upload = strtolower(  substr(  strrchr($_FILES['img']['name'], '.')  ,1)  );
        $audio_extension_upload = strtolower(  substr(  strrchr($_FILES['audio']['name'], '.')  ,1)  );

        if ( !in_array($img_extension_upload,$img_extensions_valides) )
            return $this->render('UPONDOrthophonieBundle:Administration:media_create_update.html.twig', array("action" => "Create","erreur_img" => "Extension non valide"));
        if ( !in_array($audio_extension_upload,$audio_extensions_valides) )
            return $this->render('UPONDOrthophonieBundle:Administration:media_create_update.html.twig', array("action" => "Create","erreur_audio" =>  "Extension non valide"));


        $success_message="";
        $image = "Banque images et sons/Images/".$_POST['nom'].".".$img_extension_upload;
        $resultat_img = move_uploaded_file($_FILES['img']['tmp_name'],$image);
        if ($resultat_img) $success_message.= "ajout image réussi<br>";

        $audio = "Banque images et sons/Sons/".$_POST['nom'].".".$audio_extension_upload;
        $resultat_audio = move_uploaded_file($_FILES['audio']['tmp_name'],$audio);
        if ($resultat_audio) $success_message.= "ajout son réussi<br>";

        $success_message.= "Multimedia resussi";
        //FIN TEST FICHIERS CONFORMES
        return $this->render('UPONDOrthophonieBundle:Administration:media_create_update.html.twig', array("action" => "Create","success_message"=>$success_message));
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