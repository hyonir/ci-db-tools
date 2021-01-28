<?php namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // To load a template from a Twig environment, call the load() method which returns a \Twig\TemplateWrapper instance:

        $template = $this->twig->load('index.html');

        // To render the template with some variables, call the render() method:

        return $template->render(['the' => 'variables', 'go' => 'here']);

        // The display() method is a shortcut to output the rendered template.
        // OR You can also load and render the template in one fell swoop:

        // return $this->twig->render('index.html', ['the' => 'variables', 'go' => 'here']);

        // If a template defines blocks, they can be rendered individually via the renderBlock() call:

        // return $template->renderBlock('block_name', ['the' => 'variables', 'go' => 'here']);

        // Note any of them above will work
    }

    //--------------------------------------------------------------------

}
