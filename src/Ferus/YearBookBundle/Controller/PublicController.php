<?php

namespace Ferus\YearBookBundle\Controller;

use Ferus\FairPayApi\Exception\ApiErrorException;
use Ferus\YearBookBundle\Entity\Student;
use Ferus\YearBookBundle\Form\StudentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ferus\FairPayApi\FairPay;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;

class PublicController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var UploadableManager
     */
    private $uploadableManager;

    /**
     * @Template
     */
    public function indexAction(Request $request)
    {
        if($request->isMethod('POST')){
            $student = $this->em->getRepository('FerusYearBookBundle:Student')->findOneById($request->request->get('id'));

            if($student === null){
                try{
                    $fairpay = new FairPay();
                    $data = $fairpay->getStudent($request->request->get('id'));
                }
                catch(ApiErrorException $e){
                    return array(
                        'error' => 'Code cantine incorrect.'
                    );
                }

                $student = new Student();
                $student->setId($data->id);
                $student->setClass($data->class);
                $student->setFirstName($data->first_name);
                $student->setLastName($data->last_name);
                $student->setEmail($data->email);
                $student->setPassword($request->request->get('password'));
            }
            else{
                if($student->getPassword() !== $request->request->get('password'))
                    return array(
                        'error' => 'Code d\'édition incorrect.'
                    );
            }

            $form = $this->createForm(new StudentType(), $student);
            $form->handleRequest($request);

            if(! $form->isValid())
                return array(
                    'error' => 'Formulaire mal rempli. Le fichier n\'est pas une image de moins de 4Mo ou la citation n\'est pas entre 2 et 240 caractères.'
                );

            if($student->getFile()) $this->uploadableManager->markEntityToUpload($student, $student->getFile());

            $this->em->persist($student);
            $this->em->flush();

            if(strpos($student->getPath(), '.png'))
                $img = imagecreatefrompng($student->getPath());
            else
                $img = imagecreatefromjpeg($student->getPath());

            $result = imagecreatetruecolor(472, 551);
            imagecopyresampled($result, $img,
                0, 0,
                $request->request->get('x'), $request->request->get('y'),
                472, 551,
                $request->request->get('w'), $request->request->get('h')
            );

            if(strpos($student->getPath(), '.png'))
                imagepng($result, $student->getPath(), 0);
            else
                imagejpeg($result, $student->getPath(), 100);

            $message = \Swift_Message::newInstance()
                ->setSubject('[Year Book ] Confirmation')
                ->setFrom(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                ->setTo(array($student->getEmail() => $student->getFirstName() . ' ' . $student->getLastName()))
                ->setBody(
                    $this->renderView(
                        'FerusYearBookBundle:Email:confirm.html.twig',
                        array(
                            'name' => $student->getFirstName(),
                            'code' => $student->getPassword(),
                            'quote' => $student->getQuote(),
                            'path' => $student->getWebPath(),
                        )
                    )
                )
            ;
            $this->get('mailer')->send($message);

            return array(
                'success' => 'Infos mis à jour. Check tes mails !'
            );
        }

        return array(
        );
    }

    public function searchAction($query)
    {
        try{
            $fairpay = new FairPay();
            $student = $fairpay->getStudent($query);
            $inBdd = $this->em->getRepository('FerusYearBookBundle:Student')->findAsArray($student->id);

            if($inBdd !== null)
                $student = $inBdd;
        }
        catch(ApiErrorException $e){
            return new Response(json_encode($e->returned_value), $e->returned_value->code);
        }

        return new Response(json_encode($student), 200);
    }
}
