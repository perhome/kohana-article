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
    $from = Arr::get($_GET, 'from', false);
    if ($from) {
      $from = explode('-',$from);
      if (count($from) == 2) {
        list($start, $score) = $from;
      }
    }
    $data = $this->model_article->get_list_sorted_by_created($start, $score);
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
    $view = View::factory($this->tpl_dir.'search');
    $view->bind('data', $data);
    $this->template->content = $view;
  }

  public function action_save()
  {
    $fields = array(
      'title' => array(
            array('min_length', array(':value', 3)),
      'body' => array(
            array('not_empty')),
      'category' => array(
            array('not_empty'),
            array('digit')),
      ));
    $post = Validation::factory( Arr::extract(Security::xss_clean($_POST),  array_keys($fields)) );
    foreach ($fields as $k => $v) {
      $post->rules($k, $v);
    }
    if($post->check()) {
      $data = $post->data();
      $key = $this->model_article->save($data);
      print_r($key);
    }
    else {
      $error = $post->errors('site');
      print_r($error);
    }
  }

} // End
