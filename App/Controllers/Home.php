<?php
namespace App\Controllers;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

class Home extends \Core\Controller
{
    public function indexAction(): void
    {
    }
    protected function before(): void
    {
        echo "(before) ";
    }
    protected function after(): void
    {
        echo " (after)";
    }
}