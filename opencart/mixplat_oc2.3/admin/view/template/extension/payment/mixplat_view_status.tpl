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
  <div class="container-fluid">
    <div class="panel panel-default"> 
      <div class="panel-body">
      <table class="table table-striped">
      <thead>
        <tr>
            <th><?php echo $id; ?></th>
            <th class="text-center"><?php echo $num_order; ?></th>
            <th><?php echo $sum; ?></th>
            <th><?php echo $label; ?></th>
            <th class="text-center"><?php echo $status; ?></th>
            <th><?php echo $user; ?></th>
            <th><?php echo $email; ?></th>
            <th><?php echo $date_created; ?></th>
            <th><?php echo $date_enroled; ?></th>
            <th><?php echo $sender; ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($viewstatuses as $viewstatus){   ?>
          <tr>
          <td><?php echo $viewstatus['pay_id']; ?></td>
          <td class="text-center"><?php echo $viewstatus['num_order']; ?></td>
          <td><?php echo $viewstatus['sum']; ?></td>
          <td><?php echo $viewstatus['label']; ?></td>
          <td class="text-center"><?php echo $viewstatus['status']; ?></td>
          <td><?php echo $viewstatus['user']; ?></td>
          <td><?php echo $viewstatus['email']; ?></td>
          <td><?php echo $viewstatus['date_created']; ?></td>
          <td><?php echo $viewstatus['date_enroled']; ?></td>
          <td><?php echo $viewstatus['sender']; ?></td>
          <td><a class="btn btn-info" href="<?php echo $viewstatus['info']; ?>"><i class="fa fa-eye"></i></a></td>
          </tr>
        <?php }
        ?>
        </tbody>
        </table>
<div class="pagination"><?php echo $pagination; ?></div>
  </div>
</div>
</div>
</div>
<?php echo $footer; ?>