<?php defined('SYSPATH') or die('No direct script access.');?><!DOCTYPE html>
<?php if (isset($data) && $data): ?>
  <ul>
  <?php foreach($data as $one): $one = json_decode($one, true);?>
    <li><?php echo HTML::anchor('test_article/detail/'.$one['spell'], Arr::get($one, 'title')); ?></li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>
