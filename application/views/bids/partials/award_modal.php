<div id="award-modal" class="modal hide" tabindex="-1" role="dialog">
  <form class="form-horizontal">
    <div class="modal-header">
      <button class="close" type="button" data-dismiss="modal">×</button>
      <h3>
        Mark this applicant as 'Hired'
        <span class="company-name"></span>
      </h3>
    </div>
    <div class="modal-body">
      <p>
        <strong><?php echo e(__("r.bids.partials.award_modal.header")); ?>
</strong>
        <?php echo __("r.bids.partials.award_modal.description"); ?>
      </p>
      <p><?php echo __("r.bids.partials.award_modal.co_warning"); ?></p>
      <?php if ($project->is_open_for_bids()): ?>
        <div class="alert alert-danger"><?php echo __("r.bids.partials.award_modal.due_date_warning"); ?></div>
      <?php endif; ?>
      <label class="bold-label">Message to vendor: (will be sent to <em class="vendor-email"></em>)</label>
      <textarea class="awarded-message" name="awarded_message"><?php echo __("r.bid_award_message", array("title" => $project->title, "officer_name" => Auth::officer()->name, "officer_email" => Auth::officer()->user->email)); ?></textarea>
      <label class="checkbox">
        <?php echo __("r.bids.partials.award_modal.no_email_label"); ?>
        <input class="manual-awarded-message-checkbox" type="checkbox" />
      </label>
    </div>
    <div class="modal-footer">
      <button class="btn" data-dismiss="modal">Cancel</button>
      <button class="btn btn-primary award-btn" data-loading-text="Hiring...">Hire</button>
    </div>
  </form>
</div>