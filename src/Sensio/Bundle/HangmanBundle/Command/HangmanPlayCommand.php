<?php

namespace Sensio\Bundle\HangmanBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class HangmanPlayCommand extends ContainerAwareCommand
{
    private $game;

    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('length', null, InputOption::VALUE_OPTIONAL, 'The word length', 8),
            ))
            ->setName('hangman:play')
            ->setDescription('Play the famous hangman game from the CLI')
            ->setHelp(<<<EOF
The <info>hangman:play</info> command starts a new game of the
famous hangman game:

<info>hangman:play --length=8</info>

Try to guess the hidden <comment>word</comment> whose length is
<comment>8</comment> before you reach the maximum number of
<comment>attempts</comment>.

EOF
            )
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $context = $container->get('hangman.game_context');

        $dialog = $this->getHelperSet()->get('dialog');

        $this->game = $context->newGame($input->getOption('length'));

        // Write the output
        $this->writeIntro($output, 'Welcome in the Hangman Game');
        $this->writeInfo($output, sprintf('You have %u attempts to guess the hidden word.', $this->game->getRemainingAttempts()));
        $this->writeHiddenWord($output);

        do {
            if ($letter = $dialog->ask($output, 'Type a letter... ')) {
                $this->game->tryLetter($letter);
                $this->writeHiddenWord($output);
            }

            if (!$letter && $word = $dialog->ask($output, 'Try a word... ')) {
                $this->game->tryWord($word);
            }
        } while (!$this->game->isOver());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->game->isWon()) {
            $this->writeInfo($output, sprintf('Congratulations, you won and guessed the word "%s".', $this->game->getWord()));
        } else {
            $this->writeError($output, sprintf('Oops, you\'ve been hanged! The word to guess was "%s".', $this->game->getWord()));
        }
    }

    private function writeError(OutputInterface $output, $message)
    {
        return $this->writeMessage($output, $message, 'error');
    }

    private function writeInfo(OutputInterface $output, $message)
    {
        return $this->writeMessage($output, $message, 'info');
    }

    private function writeIntro(OutputInterface $output, $message)
    {
        return $this->writeMessage($output, $message, 'bg=blue;fg=white');
    }

    private function writeMessage(OutputInterface $output, $message, $style)
    {
        $formatter = $this->getHelperSet()->get('formatter');
        $message = $formatter->formatBlock($message, $style, true);

        $output->writeln(array('', $message));
    }

    protected function writeHiddenWord(OutputInterface $output)
    {
        $letters = array();
        foreach ($this->game->getWordLetters() as $letter) {
            $letters[] = $this->game->isLetterFound($letter) ? $letter : '-';
        }

        $this->writeInfo($output, implode(' ', $letters));
    }
}