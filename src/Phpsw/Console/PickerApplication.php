<?php

namespace Phpsw\Console;

use Phpsw\Console\Command\PickCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

class PickerApplication extends Application
{
    /**
     * @{inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new PickCommand();

        return $defaultCommands;
    }

    /**
     * @{inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'pick:pick';
    }

    /**
     * @{inheritdoc}
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();

        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
