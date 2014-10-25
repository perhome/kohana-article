<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Controller_Article_Template extends Controller {

  public $tpl_dir;
  public $group = 'default';
  public $template = 'article';

  protected $model_article;
  public $auto_render = TRUE;

  public function before()
  {
    parent::before();
    $this->model_article = Model_Article_Core::instance($this->group);

    if ($this->auto_render === TRUE)
    {
      // Load the template
      $this->tpl_dir = $this->template.DIRECTORY_SEPARATOR.$this->group.DIRECTORY_SEPARATOR;
      $this->template = View::factory($this->tpl_dir.'template');
    }
  }

  /**
   * Assigns the template [View] as the request response.
   */
  public function after()
  {
    if ($this->auto_render === TRUE)
    {
      $this->response->body($this->template->render());
    }

    parent::after();
  }

}
