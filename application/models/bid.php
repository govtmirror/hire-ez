<?php

class Bid extends Eloquent {

  public static $timestamps = true;

  public $includes = array('vendor', 'vendor.user', 'project');

  public function vendor() {
    return $this->belongs_to('Vendor');
  }

  public function project() {
    return $this->belongs_to('Project');
  }

  public function is_mine() {
    return (Auth::vendor() && ($this->vendor->id == Auth::vendor()->id)) ? true : false;
  }

  public function dismissed() {
    return $this->dismissed_at ? true : false;
  }

  public function get_status() {
    if ($this->dismissed()) {
      return "Dismissed";
    } elseif ($this->awarded_at) {
      return "Won!";
    } else {
      return "Pending Review";
    }
  }

  public function submit() {
    // we removed 'submitted_at', just keeping this around for legacy compatibility
    $this->save();
  }

  public function award() {
    $this->awarded_at = new \DateTime;
    $this->awarded_by = Auth::officer()->id;
    Notification::send("ApplicantHired", array('bid' => $this));
  }

  public function bid_officer() {
    return BidOfficer::mine_for_bid($this->id);
  }

  public function has_read_bid_officer() {
    return BidOfficer::read_for_bid($this->id) ? true : false;

  }

  public function assign_officer_read($read) {
    $bid_officer = $this->bid_officer();

    if ($bid_officer->read == $read) return;

    if ($read && !$this->anyone_read) {
      $this->anyone_read = true;
    }

    $bid_officer->read = $read;
    $bid_officer->save();
  }

  public function assign_officer_starred($starred) {
    $bid_officer = $this->bid_officer();

    if ($bid_officer->starred == $starred) return;

    $bid_officer->starred = $starred;
    $bid_officer->save();
    if ($starred) $this->assign_officer_thumbs_downed(false);
  }

  public function assign_officer_thumbs_downed($thumbs_downed) {
    $bid_officer = $this->bid_officer();

    if ($bid_officer->thumbs_downed == $thumbs_downed) return;

    $bid_officer->thumbs_downed = $thumbs_downed;
    $bid_officer->save();
    if ($thumbs_downed) $this->assign_officer_starred(false);
  }

  public function sync_anyone_read($read) {
    if (!$read && $this->anyone_read && !$this->has_read_bid_officer()) {
      $this->anyone_read = false;
    }
  }

  public function assign_dismissed($dismissed) {
    if ($this->dismissed_at && $dismissed) return;
    if (!$this->dismissed_at && !$dismissed) return;

    $this->dismissed_at = $dismissed ? new \DateTime : null;
  }

  public function assign_awarded($awarded) {
    if ($this->awarded_at && $awarded) return;
    if (!$this->awarded_at && !$awarded) return;

    if ($awarded) {
      $this->award();
    } else {
      $this->awarded_at = null;
      $this->awarded_by = null;
    }
  }

  public function calculate_total_scores() {
    $this->total_stars = BidOfficer::where_bid_id($this->id)->where_starred(true)->count();
    $this->total_thumbs_down = BidOfficer::where_bid_id($this->id)->where_thumbs_downed(true)->count();
  }

  public static function with_officer_fields() {
    return self::left_join('bid_officer', function($join){
                    $join->on('bid_id', '=', 'bids.id');
                    $join->on('bid_officer.officer_id', '=', DB::raw(Auth::officer()->id));
                })
                ->left_join('vendors', 'vendor_id', '=', 'vendors.id')
                ->select(array('*',
                               'bids.id as id',
                               'bids.created_at as created_at',
                               'bids.updated_at as updated_at',
                               DB::raw('(`bids`.`total_stars` - `bids`.`total_thumbs_down`) as `total_score`')));
  }

}

