<?php
namespace Tonis\Web\TestAsset;

use Symfony\Component\Console\Command\Command;
use Tonis\Di\ContainerAwareInterface;
use Tonis\Di\ContainerAwareTrait;

class TestCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;
}
