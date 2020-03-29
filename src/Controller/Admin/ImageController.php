<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/image")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/", name="admin_image_index", methods={"GET"})
     */
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('admin/image/index.html.twig', [
            'images' => $imageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="admin_image_new", methods={"GET","POST"})
     */
    public function new(Request $request, $id, ImageRepository $imageRepository): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            //************ file upload *******>>>>>>>>>>>>
            /** @var file $file */
            $file= $form['image'] -> getData();
            if($file){
                $fileName= $this->generateUniqueFileName().'.'.$file->guessExtension();
                try{
                    $file -> move(
                        $this->getParameter('image_directory'),
                        $fileName
                    );
                } catch (FileException $e){

                }
                $image ->setImage($fileName);
            }
            //************* file upload *******>>>>>>>>>>>>>>>
            $entityManager->persist($image);
            $entityManager->flush();

            return $this->redirectToRoute('admin_image_new', ['id'=>$id]);
        }
        $imagess = $imageRepository -> findBy(['post'=>$id]);
        return $this->render('admin/image/new.html.twig', [
            'imagess' => $imagess,
            'id' => $id,
            'image' => $image,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_image_show", methods={"GET"})
     */
    public function show(Image $image): Response
    {
        return $this->render('admin/image/show.html.twig', [
            'image' => $image,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_image_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Image $image): Response
    {
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_image_index');
        }

        return $this->render('admin/image/edit.html.twig', [
            'image' => $image,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{pid}", name="admin_image_delete", methods={"DELETE"})
     */
    public function delete(Request $request,$pid, Image $image): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $entityManager->flush();
        }


        return $this->redirectToRoute('admin_image_new', ['id'=>$pid]);    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}