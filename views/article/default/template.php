<?php defined('SYSPATH') or die('No direct script access.');?><!DOCTYPE html>
<head>
    <meta charset="utf-8"/>
    <title>文章</title>
    <link rel="shortcut icon" href="/media/favicon.ico?ver=0.1" /> 
    <?php echo HTML::script('media/jquery-2.0.2.min.js'); ?>
    <?php echo HTML::style('media/pure-min.css'); ?>
    <?php echo HTML::style('media/awesome/css/font-awesome.min.css'); ?>
</head>
<body>
<div class="pure-g" style="width: 960px; margin: 0 auto;">
  <div class="pure-u-1">
    <h1>文章</h1>
  </div>
  <div class="pure-u-1">
  <?php echo HTML::anchor('test_article/index', '列表'); ?>
  <?php echo HTML::anchor('test_article/search'.URL::query(array('keyword'=>'文章')), '搜索'); ?>
  <?php if (isset($content)): echo $content; endif; ?> 
  </div>
  <div class="pure-u-1">
  版权所有 (C) 2013
  </div>    
</div>
</body>
</html>
