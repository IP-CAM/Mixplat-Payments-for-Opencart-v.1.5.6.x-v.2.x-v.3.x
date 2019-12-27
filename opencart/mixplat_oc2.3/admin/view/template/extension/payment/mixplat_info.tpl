<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <?php if (isset($capture) ) { ?>
    <div class="container-fluid">
      <div class="pull-left">
        <a href="<?php echo $capture; ?>" class="btn btn-primary"><?php echo $text_capture; ?></a>
        <a href="<?php echo $cancel; ?>" class="btn btn-primary"><?php echo $text_cancel; ?></a>
        <div class="panel"></div>
      </div>  
    </div>
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
      <table class="table table-striped">
        <?php foreach ($statuses as $key => $value) { ?>
        <tr>
          <td width="25%"><?php echo $key; ?></td>
          <td width="25%"><?php echo $value; ?></td>
        </tr>
        <?php } ?>
      </table>
  </div>
</div>
</div>
</div>
<?php echo $footer; ?>