<?php echo $header; ?>
<div id="content">
      <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
    <div class="box">    
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" /><?php echo $heading_title; ?></h1>
    </div>
  
  <?php if (isset($capture) ) { ?>
    <br>
    <div class="container-fluid">
      <div class="pull-left">
        <a href="<?php echo $capture; ?>" class="button"><?php echo $text_capture; ?></a>
        <a href="<?php echo $cancel; ?>" class="button"><?php echo $text_cancel; ?></a>
        <div class="panel"></div>
      </div>  
    </div><br>
  <?php } ?>
  <?php if ($success) { ?>
  <div class="container-fluid">
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
  </div>
  <?php } ?>
  <?php if ($error_warning) { ?>
  <div class="container-fluid">
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
  </div>
  <?php } ?>
  <div class="container-fluid">
    <div class="panel panel-default"> 
      <div class="panel-body">
      <table class="form">
        <?php foreach ($statuses as $key => $value) { ?>
        <tr>
          <td><?php echo $key; ?></td>
          <td><?php echo $value; ?></td>
        </tr>
        <?php } ?>
      </table>
  </div>
</div>
</div>
</div>
</div>
<?php echo $footer; ?>