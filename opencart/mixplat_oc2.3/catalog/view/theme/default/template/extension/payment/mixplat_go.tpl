<?php echo $header; ?>
<div class="container">
   <div class="row"><?php echo $column_left; ?>
    <?php if ($column_left && $column_right) { ?>
    <?php $class = 'col-sm-6'; ?>
    <?php } elseif ($column_left || $column_right) { ?>
    <?php $class = 'col-sm-9'; ?>
    <?php } else { ?>
    <?php $class = 'col-sm-12'; ?>
    <?php } ?>
    <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
  <h1><?php echo $heading_title; ?></h1>
  <p><?php echo $send_text; ?></p>
 <div class="buttons">
 <div class="pull-left">
    <?php if ($paystat != 1){ ?><form method="POST" action="<?php echo $merchant_url;?>"><input type="submit" value="<?php echo $button_pay; ?>" class="btn btn-primary"></form> <?php } ?></div>
</div>
<?php echo $content_bottom; ?></div>
<?php echo $column_right; ?></div>
</div>
<?php echo $footer; ?>