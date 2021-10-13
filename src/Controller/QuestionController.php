<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Service\MarkdownHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sentry\State\HubInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class QuestionController extends AbstractController
{

    private $logger;
    private $isDebug;

    public function __construct(LoggerInterface $logger, bool $isDebug)
    {
        $this->logger = $logger;
        $this->isDebug = $isDebug;
    }

    public function homepage(QuestionRepository $repository)
    {
        $questions = $repository->findAllAskedOrderedByNewest();


        return $this->render('question/homepage.html.twig', [
            'questions' => $questions
        ]);
    }

    public function new_new(EntityManagerInterface $entityManager)
    {

        return new Response('Sounds like a GREAT feature for v2');
    }

    public function show(Question $question)
    {

        if ($this->isDebug){
            $this->logger->info('We are in debug mode!');
        }



        $answers = [
            'Make sure your cat is sitting `purrrfectly` still 🤣',
            'Honestly, I like furry shoes better than MY cat',
            'Maybe... try saying the spell backwards?',
        ];

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answers' => $answers,
        ]);
    }

    public function questionVote(Question $question, Request $request, EntityManagerInterface $entityManager )
    {
        $direction = $request->request->get('direction');

        if ($direction === 'up'){
            $question->upVote();
        } elseif ($direction === 'down'){
            $question->downVote();
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_question_show', [
            'slug'=> $question->getSlug(),
        ]);
    }

}
