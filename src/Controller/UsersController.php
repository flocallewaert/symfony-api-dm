<?php

namespace App\Controller;

use App\Repository\UserRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UsersController extends AbstractFOSRestController
{
    private $userRepository;
    private $em;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    // ADMIN SEARCH
    /**
     * @Rest\Get("/api/admin/users")
     * @Rest\View(serializerGroups={"user-admin"})
     */
    public function getApiUsers()
    {
        $users = $this->userRepository->findAll();
        if(count($users) <= 0) {
             return $this->view(null, Response::HTTP_NO_CONTENT); // 204
         }
         return $this->view($users, Response::HTTP_OK); // 200
    }

    // ADMIN CREATE

    /**
     * @Rest\Post("/api/admin/users")
     * @Rest\View(serializerGroups={"user-admin"})
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @param User $user
     * @param ConstraintViolationListInterface $validationErrors
     * @return \FOS\RestBundle\View\View
     */
    public function postApiUser(User $user, ConstraintViolationListInterface $validationErrors)
    {
        $errors = array();
        if($validationErrors->count() > 0){
            /* @var ConstraintViolation $constraintViolation */
            foreach($validationErrors as $constraintViolation){
                $message = $constraintViolation->getMessage();
                $propertyPath = $constraintViolation->getPropertyPath();
                $errors[] = ['message' => $message, 'propertyPath' => $propertyPath];
            }
        }
        if(!empty($errors)) {
            throw new BadRequestHttpException(\json_encode($errors));
        }

        $this->em->persist($user);
        $this->em->flush();
        return $this->view($user, Response::HTTP_CREATED); // 201
    }

    // ADMIN READ
    /**
     * @Rest\Get("/api/admin/users/{id}")
     * @Rest\View(serializerGroups={"user-admin"})
     * @param User $user
     * @return \FOS\RestBundle\View\View
     */
    public function getApiUser(User $user)
    {
        return $this->view($user, Response::HTTP_OK); // 200
    }

    // ADMIN UPDATE

    /**
     * @Rest\Patch("/api/admin/users/{id}")
     * @Rest\View(serializerGroups={"user-admin"})
     * @param User $user
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return \FOS\RestBundle\View\View
     */
    public function patchApiUser(User $user, Request $request, ValidatorInterface $validator)
    {
        if(!empty($request->get('firstName')))
        {
            $user->setFirstName($request->get('firstName'));
        }
        if(!empty($request->get('lastName')))
        {
            $user->setLastName($request->get('lastName'));
        }
        if(!empty($request->get('email')))
        {
            $user->setEmail($request->get('email'));
        }
        if(!empty($request->get('birthday')))
        {
            $user->setBirthday($request->get('birthday'));
        }
        if(!empty($request->get('apiKey')))
        {
            $user->setApiKey($request->get('apiKey'));
        }

        $validationErrors = $validator->validate($user);
        $errors = array();
        if($validationErrors->count() > 0){
            /* @var ConstraintViolation $constraintViolation */
            foreach($validationErrors as $constraintViolation){
                $message = $constraintViolation->getMessage();
                $propertyPath = $constraintViolation->getPropertyPath();
                $errors[] = ['message' => $message, 'propertyPath' => $propertyPath];
            }
        }
        if(!empty($errors)) {
            throw new BadRequestHttpException(\json_encode($errors));
        }

         $this->em->persist($user);
         $this->em->flush();

        return $this->view($user, Response::HTTP_OK); // 200
    }

    // ADMIN DELETE
    /**
     * @Rest\Delete("/api/admin/users/{id}")
     * @Rest\View(serializerGroups={"user-admin"})
     * @param User $user
     */
    public function deleteApiUser(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
        $this->view(null, Response::HTTP_NO_CONTENT); // 204
    }

    // USER READ
    /**
     * @Rest\Get("/api/users")
     * @Rest\View(serializerGroups={"user"})
     */
    public function getCurrentUser()
    {
        $currentUser = $this->getUser();
        return $this->view($currentUser,Response::HTTP_OK); // 200
    }
    // USER UPDATE
    /**
     * @Rest\Patch("/api/users")
     * @Rest\View(serializerGroups={"user"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function patchCurrentUser(Request $request)
    {
        $currentUser = $this->getUser();

        if(!empty($request->get('firstName')))
        {
            $currentUser->setFirstName($request->get('firstName'));
        }
        if(!empty($request->get('lastName')))
        {
            $currentUser->setLastName($request->get('lastName'));
        }
        if(!empty($request->get('email')))
        {
            $currentUser->setEmail($request->get('email'));
        }
        if(!empty($request->get('apiKey')))
        {
            $currentUser->setApiKey($request->get('apiKey'));
        }

        $this->em->persist($currentUser);
        $this->em->flush();

        return $this->view($currentUser, Response::HTTP_OK); // 200
    }
    // USER DELETE
    /**
     * @Rest\Delete("/api/users")
     * @Rest\View(serializerGroups={"user"})
     * @return mixed
     */
    public function deleteCurrentUser()
    {
        $currentUser = $this->getUser();

        $this->em->remove($currentUser);
        $this->em->flush();
        $this->view(null, Response::HTTP_NO_CONTENT); // 204
    }

    // ANONYMOUS CREATE
    /**
     * @Rest\Post("/api/signup")
     * @Rest\View(serializerGroups={"user-key"})
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @param User $user
     * @return \FOS\RestBundle\View\View
     */
    public function postAnonUser(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
        return $this->view($user, Response::HTTP_CREATED); // 201
    }
}
