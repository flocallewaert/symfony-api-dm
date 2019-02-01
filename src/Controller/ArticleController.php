<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Article;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ArticleController extends AbstractFOSRestController
{
    private $articleRepository;
    private $em;

    public function __construct(ArticleRepository $articleRepository, EntityManagerInterface $em)
    {
        $this->articleRepository = $articleRepository;
        $this->em = $em;
    }

    // ADMIN SEARCH
    /**
     * @Rest\Get("/api/admin/articles")
     * @Rest\View(serializerGroups={"article-admin"})
     */
    public function getApiArticles()
    {
        $articles = $this->articleRepository->findAll();
        if(count($articles) <= 0) {
            return $this->view(null, Response::HTTP_NO_CONTENT); // 204
        }
        return $this->view($articles, Response::HTTP_OK); // 200
    }

    // ADMIN CREATE
    /**
     * @Rest\Post("/api/admin/articles")
     * @Rest\View(serializerGroups={"article-admin"})
     * @ParamConverter("article", converter="fos_rest.request_body")
     * @param Article $article
     * @return \FOS\RestBundle\View\View
     */
    public function postApiArticle(Article $article)
    {
        $article->setUser($this->getUser());
        $this->em->persist($article);
        $this->em->flush();
        return $this->view($article, Response::HTTP_CREATED); // 201
    }

    // ADMIN READ
    /**
     * @Rest\Get("/api/admin/articles/{id}")
     * @Rest\View(serializerGroups={"article-admin"})
     * @param Article $article
     * @return \FOS\RestBundle\View\View
     */
    public function getApiArticle(Article $article)
    {
        return $this->view($article,Response::HTTP_OK); // 200
    }

    // ADMIN UPDATE
    /**
     * @Rest\Patch("/api/admin/articles/{id}")
     * @Rest\View(serializerGroups={"article-admin"})
     * @param Article $article
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function patchApiArticle(Article $article, Request $request)
    {
        if(!empty($request->get('name')))
        {
            $article->setName($request->get('name'));
        }
        if(!empty($request->get('description')))
        {
            $article->setDescription($request->get('description'));
        }
        if(!empty($request->get('createdAt')))
        {
            $article->setDescription($request->get('createdAt'));
        }

        $this->em->persist($article);
        $this->em->flush();
        return $this->view($article, Response::HTTP_OK); // 200
    }

    // ADMIN DELETE
    /**
     * @Rest\Delete("/api/admin/articles/{id}")
     * @Rest\View(serializerGroups={"article-admin"})
     * @param Article $article
     * @return \FOS\RestBundle\View\View
     */
    public function deleteApiArticle(Article $article)
    {
        $this->em->remove($article);
        $this->em->flush();
        return $this->view(null,Response::HTTP_NO_CONTENT); // 204
    }

    // USER SEARCH
    /**
     * @Rest\Get("/api/articles")
     * @Rest\View(serializerGroups={"article"})
     */
    public function getCurrentArticles()
    {
        $articles = $this->getUser()->articles;
        if(count($articles) <= 0) {
            return $this->view(null, Response::HTTP_NO_CONTENT); // 204
        }
        return $this->view($articles, Response::HTTP_OK); // 200
    }

    // USER CREATE
    /**
     * @Rest\Post("/api/articles")
     * @ParamConverter("article", converter="fos_rest.request_body")
     * @param Article $article
     * @return \FOS\RestBundle\View\View
     */
    public function postCurrentArticle(Article $article)
    {
        $article->setUser($this->getUser());
        $this->em->persist($article);
        $this->em->flush();
        return $this->view($article, Response::HTTP_OK); // 200
    }

    // USER READ
    /**
     * @Rest\Get("/api/articles/{id}")
     * @Rest\View(serializerGroups={"article"})
     * @param Article $article
     * @return \FOS\RestBundle\View\View
     */
    public function getCurrentArticle(Article $article)
    {
        if($article->getUser()->getId() !== $this->getUser()->getId())
        {
            return $this->view(null, Response::HTTP_FORBIDDEN); // 403
        }
        return $this->view($article, Response::HTTP_OK); // 200
    }

    // USER UPDATE
    /**
     * @Rest\Patch("/api/articles/{id}")
     * @Rest\View(serializerGroups={"article"})
     * @param Article $article
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function patchCurrentArticle(Article $article, Request $request)
    {
        if($article->getUser()->getId() !== $this->getUser()->getId()){
            return $this->view(null, Response::HTTP_FORBIDDEN); // 403
        }

        if(!empty($request->get('name')))
        {
            $article->setName($request->get('name'));
        }
        if(!empty($request->get('description')))
        {
            $article->setDescription($request->get('description'));
        }
        if(!empty($request->get('createdAt')))
        {
            $article->setDescription($request->get('createdAt'));
        }

        $this->em->persist($article);
        $this->em->flush();
        return $this->view($article, Response::HTTP_OK); // 200
    }

    // USER DELETE
    /**
     * @Rest\Delete("/api/articles/{id}")
     * @Rest\View(serializerGroups={"article"})
     * @param Article $article
     * @return \FOS\RestBundle\View\View
     */
    public function deleteCurrentArticle(Article $article)
    {
        if($article->getUser()->getId() !== $this->getUser()->getId()){
            return $this->view(null, Response::HTTP_FORBIDDEN);// 403
        }

        $this->em->remove($article);
        $this->em->flush();
        return $this->view(null, Response::HTTP_NO_CONTENT); // 204
    }
}
