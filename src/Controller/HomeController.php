<?php

namespace App\Controller;

use App\Entity\Admin\Messages;
use App\Entity\Post;
use App\Form\Admin\MessagesType;
use App\Repository\Admin\CommentRepository;
use App\Repository\ImageRepository;
use App\Repository\PostRepository;
use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Smtp\GmailTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SettingRepository $settingRepository, PostRepository $postRepository)
    {
        $setting=$settingRepository->findBy(['id'=>1]);
        $slider=$postRepository->findBy([],['title'=>'ASC'],3);
        $posts=$postRepository->findBy([],['title'=>'DESC'],3);
        $lastPosts=$postRepository->findBy([],['title'=>'DESC'],6);



        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting'=> $setting,
            'slider'=> $slider,
            'posts'=> $posts,
            'lastPosts'=> $lastPosts,

        ]);
    }
    /**
     * @Route("post/{id}", name="post_show", methods={"GET"})
     */
    public function show(Post $post, $id, ImageRepository $imageRepository, CommentRepository $commentRepository): Response
    {
        $images=$imageRepository->findBy(['post'=>$id]);
        //$comments=$commentRepository->findBy(['postid'=>$id, 'status'=>'New']);
        $comments=$commentRepository->getAllCommentsPost($id);
        //dump($comments);
        //die();
        return $this->render('home/postshow.html.twig', [
            'post' => $post,
            'images' => $images,
            'comments' => $comments,
        ]);
    }
    /**
     * @Route("/about", name="home_about")
     */
    public function about(SettingRepository $settingRepository): Response
    {
        $setting=$settingRepository->findBy(['id'=>1]);
        return $this->render('home/aboutus.html.twig', [
            'setting'=> $setting,
        ]);
    }

    /**
     * @Route("/contact", name="home_contact",  methods={"GET","POST"})
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function contact(SettingRepository $settingRepository, Request $request): Response
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $setting=$settingRepository->findBy(['id'=>1]);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $message->setStatus('new');
            $message->setIp($_SERVER['REMOTE_ADDR']);

            $entityManager->persist($message);
            $entityManager->flush();
            $this->addFlash('success','Your message has been sent successfully');

            //******************SEND EMAIL ************************//
            $email = (new Email())
                ->from($setting[0]->getSmtpemail())
                ->to($form['email']->getData())
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject('Bueno-Food Blog Your Request')
                //->text('Sending emails is fun again!')
                ->html("Dear ".$form['name']->getData(). "<br>
                             <p>We will evaulate your requests and contact you as soon as possible</p>
                             Thank you for your message <br>
                             =============================================================================
                            <br>".$setting[0]->getBlog()." <br>
                             Address: <br>".$setting[0]->getAddress()." <br>
                             Phone  : <br>".$setting[0]->getPhone()."<br>"         );
            $transport = new GmailTransport($setting[0]->getSmtpemail(),$setting[0]->getSmtppassword());
            $mailer = new Mailer($transport);
            $mailer->send($email);
            //*******************SEND EMAIL **********************************//

            return $this->redirectToRoute('home_contact');
        }
        $setting=$settingRepository->findBy(['id'=>1]);
        return $this->render('home/contact.html.twig', [
            'setting'=> $setting,
            'form' => $form->createView(),
        ]);
    }

}
