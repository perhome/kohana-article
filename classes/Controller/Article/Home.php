<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Article_Home extends Controller_Article_Template {


  public function before()
  {
    parent::before();
  }

  public function action_index()
  {
    //$post = array('title'=>'第一篇文章', 'body'=>'Hello World!');
    //$key = $this->model_article->save($post);
    $start = null;
    $score = null;
    $data = $this->model_article->get_list_sorted_by_hot($start, $score);
    $view = View::factory($this->tpl_dir.'index');
    $view->bind('data', $data);
    $this->template->content = $view;
  }

  public function action_detail()
  {
    $key = $this->request->param('id');
    $data = $this->model_article->get($key);
    if ($data) {
      $view = View::factory($this->tpl_dir.'detail');
      $view->bind('data', $data);
      $this->template->content = $view;
    }
    else {
      throw new Kohana_HTTP_Exception_404();
    }
  }

  public function action_search()
  {
    $post = Arr::extract($_GET, array('keyword', 'page'));
    $data = $this->model_article->search($post);
    $view = View::factory($this->tpl_dir.'index');
    $view->bind('data', $data);
    $this->template->content = $view;
  }

  public function action_save()
  {
    $post = array(
      'id' => 0,
      'title'=>'第一篇文章', 
      'body'=>'Hello World!',
      'created' => time(),
    );
    $post['title'] .= Text::random();
    $key = $this->model_article->save($post);
    echo $key;
  }

} // End
