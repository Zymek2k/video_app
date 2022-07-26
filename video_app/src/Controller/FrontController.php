<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Video;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFrontPage;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Form\UserType;

class FrontController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine=$doctrine;
    }

    #[Route('/', name: 'main')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    #[Route('/video-list/category/{categoryname},{id}/{page}',defaults: ['page' => 1], name: 'video_list')]
    public function videoList($id, $page, CategoryTreeFrontPage $categories, Request $request)
    {
        $ids = $categories->getChildIds($id);
        array_push($ids, $id);

        $videos = $this->doctrine
            ->getRepository(Video::class)
            ->findByChildIds($ids ,$page, $request->get('sortby'));

        $categories->getCategoryListAndParent($id);
        return $this->render('front/video_list.html.twig',[
            'subcategories' => $categories,
            'videos'=>$videos
        ]);
    }

    #[Route('/video-details/{video}', name: 'video_details')]
    public function videoDetails( VideoRepository $repo, $video)
    {
        return $this->render('front/video_details.html.twig',[
            'video'=>$repo->videoDetails($video),
        ]);
    }

    #[Route('/search-results/{page}', name: 'search_results', defaults:["page"=>1], methods: ['GET'])]
    public function searchResults($page, Request $request)
    {
        $videos = null;
        $query = null;

        if($query = $request->get('query'))
        {
            $videos = $this->doctrine
                ->getRepository(Video::class)
                ->findByTitle($query, $page, $request->get('sortby'));

            if(!$videos->getItems()) $videos = null;
        }
        return $this->render('front/search_results.html.twig',[
            'videos' => $videos,
            'query' => $query,
        ]);
    }

    #[Route('/pricing', name: 'pricing')]
    public function pricing()
    {
        return $this->render('front/pricing.html.twig');
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $user = new User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->doctrine->getManager();
            $user->setName($request->request->all('user')['name']);
            $user->setLastName($request->request->all('user')['lastName']);
            $user->setEmail($request->request->all('user')['email']);
            $password=$passwordHasher->hashPassword($user, $request->request->all('user')['password']['first']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_main');
        }

        return $this->render('front/register.html.twig', [
            'form'=>$form->createView(),
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $helper)
    {
        return $this->render('front/login.html.twig',[
            'error'=>$helper->getLastAuthenticationError()
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(AuthenticationUtils $helper)
    {
        throw new \Exception('This should never be reached!');
    }

    #[Route('/payment', name: 'payment')]
    public function payment()
    {
        return $this->render('front/payment.html.twig');
    }

    #[Route("/new-comment/{video}", methods: ["POST"], name: "new_comment")]
    public function newComment(Video $video, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if( !empty(trim($request->request->get('comment'))))
        {
            $comment = new Comment();
            $comment->setContent($request->request->get('comment'));
            $comment->setUser($this->getUser());
            $comment->setVideo($video);

            $entitymanager = $this->doctrine->getManager();
            $entitymanager->persist($comment);
            $entitymanager->flush();
        }

        return $this->redirectToRoute('video_details', ['video'=>$video->getId()]);
    }

    public function mainCategories()
    {
        $categories = $this->doctrine->getRepository(Category::class)->findBy(['parent'=>null], ['name'=>'ASC']);
        return $this->render('front/_main_categories.html.twig',[
            'categories'=>$categories
        ]);
    }
}
