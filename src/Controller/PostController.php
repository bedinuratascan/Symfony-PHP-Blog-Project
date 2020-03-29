<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\Post1Type;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="user_post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository): Response
    {
        $user = $this->getUser(); // Get User Login Data
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findBy(['userid'=>$user->getId()]),
        ]);
    }

    /**
     * @Route("/new", name="user_post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(Post1Type::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            /** @var file $file */
            $file= $form['image']->getData();
            if($file){
                $fileName = $this->generateUniqueFileName() . '.' . $file -> guessExtension();
                try {
                    $file -> move(
                        $this->getParameter('image_directory'),
                        $fileName
                    );
                }catch(FileException $e){

                }
                $post -> setImage($fileName);
            }

            $user = $this->getUser(); // Get User Login Data
            $post->setUserid($user->getId());
            $post->setStatus("new");

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('user_post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_post_show", methods={"GET"})
     */
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(Post1Type::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var file $file */
            $file= $form['image']->getData();
            if($file){
                $fileName = $this -> generateUniqueFileName() . '.' . $file -> guessExtension();
                try {
                    $file -> move(
                        $this->getParameter('image_directory'),
                        $fileName
                    );
                }catch(FileException $e){

                }
                $post -> setImage($fileName);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_post_index');
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @return string
     */
    private function generateUniqueFileName(){
        return md5(uniqid());
    }


    /**
     * @Route("/{id}", name="user_post_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_post_index');
    }
}
