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
        $question = new Question();
        $question->setName('Missing pants')
            ->setSlug('missing-pants-'.rand(0,1000))
            ->setQuestion(<<<EOF
 
Lorem ipsum dolor sit amet, consectetur adipiscing elit.
Vestibulum varius felis in nulla tempus, et porttitor sem semper.
Morbi pharetra at orci eget efficitur.
Curabitur vitae odio interdum, mattis justo ut, efficitur mi.
Morbi eget porta libero, eu dignissim nisl.
Integer dapibus est sed velit scelerisque bibendum. 
Etiam neque magna, gravida nec posuere eu, tincidunt at turpis.
In molestie nisl at lacus porta rhoncus ac a ligula.
Sed suscipit hendrerit augue, non vulputate orci consectetur sit amet. 
Nulla consequat eros in lacus faucibus, quis facilisis nisl viverra. 
Fusce justo ex, imperdiet non dolor sit amet, eleifend aliquam elit. 
Aliquam eget porttitor dolor. Aliquam dictum ligula nunc, vel molestie tellus dapibus at.

EOF
            );
        if (rand(1,10)>2){
            $question->setAskedAt(new \DateTime(sprintf('-%d days', rand(1, 100))));
        }

        $question->setVotes(rand(-10,20));



        $entityManager->persist($question);
        $entityManager->flush();

        return new Response(sprintf(
            'Well hallo! The shiny new question is id #%d, slug %s',
                $question->getId(),
                $question->getSlug()
            )
        );
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
