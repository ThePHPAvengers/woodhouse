<?php
namespace Woodhouse\Project\Question;

use Woodhouse\Task\ITask;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question as SimpleQuestion;

/**
 * Class KeywordsQuestion
 *
 * @package Woodhouse\Project\Question
 * @author  Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class KeywordsQuestion extends Question
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $answer = $this->ask(
            $input,
            $output,
            new SimpleQuestion('<question>Enter your project keywords (comma separated):</question>')
        );

        $this->setAnswer($answer);

        return ITask::NO_ERROR_CODE;
    }

    /**
     * @param $answer
     */
    private function setAnswer($answer)
    {
        if ($answer) {
            $this->getProject()->setKeywords(array_map('trim', explode(',', $answer)));
        } else {
            $this->getProject()->setKeywords([]);
        }
    }
}
