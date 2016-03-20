<?php
namespace Woodhouse\Project\Question;

use Woodhouse\Task\ITask;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question as SimpleQuestion;

/**
 * Class HomePageQuestion
 *
 * @package Woodhouse\Project\Question
 * @author  Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class HomepageQuestion extends Question
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->getProject()->setHomepage($this->ask($input, $output, $this->buildQuestion()));
        }catch(\Exception $e){
            $this->getProject()->setHomepage('');
        }
        return ITask::NO_ERROR_CODE;
    }

    /**
     * @return SimpleQuestion
     */
    private function buildQuestion()
    {
        $question = new SimpleQuestion('<question>Enter your project homepage:</question>');
        $question->setValidator($this->buildValidator());
        $question->setMaxAttempts(3);
        return $question;
    }

    /**
     * @return callable
     */
    private function buildValidator()
    {
        return function ($answer) {
            if ($answer && !filter_var($answer, FILTER_VALIDATE_URL)) {
                throw new \RuntimeException('Error: format not valid');
            }
            return $answer;
        };
    }

}
