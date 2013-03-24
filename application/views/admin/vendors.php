<?php Section::inject('no_page_header', true) ?>
<?php echo View::make('admin.partials.subnav')->with('current_page', 'vendors'); ?>
<table class="table table-bordered table-striped admin-vendors-table">
  <thead>
    <tr>
      <th>id</th>
      <th>company_name</th>
      <th>actions</th>
    </tr>
  </thead>
  <tbody id="vendors-tbody">
    <?php foreach ($vendors->results as $vendor): ?>
      <tr>
        <td><?php echo e($vendor->id); ?></td>
        <td><?php echo e($vendor->company_name); ?></td>
        <td></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<div class="pagination-wrapper">
  <?php echo $vendors->links(); ?>
</div>