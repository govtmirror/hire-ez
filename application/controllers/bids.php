<?php

class Bids_Controller extends Base_Controller {

  public function __construct() {
    parent::__construct();

    $this->filter('before', 'vendor_only')->only(array('new', 'create', 'mine', 'destroy'));

    $this->filter('before', 'project_exists')->except(array('mine'));

    $this->filter('before', 'i_am_collaborator')->only(array('review', 'update', 'transfer', 'show'));

    $this->filter('before', 'bid_exists')->only(array('update', 'transfer', 'show'));

    $this->filter('before', 'i_have_not_already_bid')->only(array('new', 'create'));
  }

  public $bid_sort_options = array('score' => 'total_score',
                                   'name' => 'vendors.name',
                                   'unread' => 'bid_officer.read',
                                   'comments' => 'vendors.total_comments');


  // review page
  public function action_review($project_id, $filter = "") {
    $view = View::make('bids.review');
    $view->query = Input::get('q');
    $view->project = Config::get('project');
    if ($filter) Config::set('review_bids_filter', $filter);

    $sortInput = Input::get('sort') ?: "name";
    Config::set('review_bids_sort', $sortInput);
    $sort = @$this->bid_sort_options[$sortInput];
    $order = Input::get('order');

    if ($filter == 'unread') {
      $q = $view->project->unread_bids();
    } elseif ($filter == 'interview') {
      $q = $view->project->interview_bids();
    } elseif ($filter == 'hired') {
      $q = $view->project->winning_bids();
    } elseif ($filter == 'starred') {
      $q = $view->project->starred_bids();
    } elseif ($filter == 'thumbs-downed') {
      $q = $view->project->thumbs_downed_bids();
    } elseif ($filter == 'spam') {
      $q = $view->project->dismissed_bids();
    } else {
      $q = $view->project->all_bids();
    }

    if ($view->query) {
      $q = $q->where(function($q)use($view){
        $q->or_where('name', 'LIKE', '%'.$view->query.'%');
        $q->or_where('bids.body', 'LIKE', '%'.$view->query.'%');
        $q->or_where('resume', 'LIKE', '%'.$view->query.'%');
        $q->or_where('email', 'LIKE', '%'.$view->query.'%');
        $q->or_where('phone', 'LIKE', '%'.$view->query.'%');
        $q->or_where('general_paragraph', 'LIKE', '%'.$view->query.'%');
        $q->or_where('link_1', 'LIKE', '%'.$view->query.'%');
        $q->or_where('link_2', 'LIKE', '%'.$view->query.'%');
        $q->or_where('link_3', 'LIKE', '%'.$view->query.'%');
        $q->or_where('location', 'LIKE', '%'.$view->query.'%');
        $q->or_where_not_null('comments.id');
      })->left_join('comments', function($join)use($view){
        $join->on('comments.commentable_type', '=', DB::raw('"vendor"'));
        $join->on('comments.commentable_id', '=', 'bids.vendor_id');
      })->where(function($q)use($view){
        $q->or_where('comments.body', 'LIKE', '%'.$view->query.'%');
        $q->or_where_null('comments.body');
      });
;
    }

    $total = $q->count();
    if ($sort) $q = $q->order_by($sort, $order);

    $per_page = 25;
    $view->skip = Input::get('skip', 0);
    $view->sort = $sortInput;
    $bids = $q->take($per_page)->skip($view->skip)->get();

    $view->paginator = Helper::get_bid_paginator($view->skip, $per_page, $total);

    $view->bids_json = eloquent_to_json($bids);
    $this->layout->content = $view;
  }

  // "transfer" a bid to another project
  // doesn't actually remove the bid from your project,
  // just clones it and adds it to another project.
  public function action_transfer() {
    $bid = Config::get('bid');
    $project = Config::get('project');
    $from_email = Auth::officer()->user->email;
    $transfer_to_project = Project::find(Input::get('project_id'));

    if (!$transfer_to_project) {
      Session::flash('error', "Couldn't find the project that you're trying to transfer this applicant to.");
      return Redirect::to_route('bid', array($project->id, $bid->id));
    }

    $new_bid = new Bid(array('body' => "Applicant referred from project $project->title by $from_email.",
                             'project_id' => $transfer_to_project->id));

    $new_bid->vendor_id = $bid->vendor_id;

    $new_bid->save();

    Notification::send("ApplicantForwarded", array('bid' => $new_bid, 'from_project' => $project, 'project' => $transfer_to_project));

    Session::flash('notice', "Success! " . $bid->vendor->name." referred to ".$transfer_to_project->title.".");
    return Redirect::back();

  }

  // handle updates from backbone
  public function action_update() {
    $bid = Config::get('bid');
    $input = Input::json(true);

    $bid->interview = $input["interview"];
    $bid->assign_officer_read($input["read"]);
    $bid->assign_officer_starred($input["starred"]);
    $bid->assign_officer_thumbs_downed($input["thumbs_downed"]);
    $bid->assign_dismissed($input["dismissed_at"]);
    $bid->assign_awarded($input["awarded_at"]);

    $bid->sync_anyone_read($input["read"]);

    $bid->calculate_total_scores();
    $bid->save();

    $bid = Bid::with_officer_fields()
              ->where('bids.id', '=', $bid->id)
              ->first();

    $bid->vendor->includes_in_array = array('titles_of_projects_applied_for', 'ids_of_projects_applied_for', 'projects_not_applied_for');

    return Response::json($bid->to_array());
  }

  // handle updates from backbone
  public function action_show() {
    $bid = Config::get('bid');

    if (Request::ajax()) {
      Auth::user()->view_notification_payload("bid", $bid->id);

      $bid = Bid::with_officer_fields()
                ->where('bids.id', '=', $bid->id)
                ->first();

      $bid->vendor->includes_in_array = array('titles_of_projects_applied_for', 'ids_of_projects_applied_for', 'projects_not_applied_for');

      return Response::json($bid->to_array());

    } else {
      if (!$bid->read) $bid->assign_officer_read(true);

      Auth::user()->view_notification_payload("bid", $bid->id);

      $bid = Bid::with_officer_fields()
                ->where('bids.id', '=', $bid->id)
                ->first();

      $bid->vendor->includes_in_array = array('titles_of_projects_applied_for', 'ids_of_projects_applied_for', 'projects_not_applied_for');

      $view = View::make('bids.show');
      $view->project = Config::get('project');
      $view->bid = $bid;
      $view->bid_json = json_encode($bid->to_array());
      $this->layout->content = $view;
    }
  }

}

Route::filter('project_exists', function() {
  $id = Request::$route->parameters[0];
  $project = Project::find($id);
  if (!$project) return Redirect::to('/');
  Config::set('project', $project);
});

Route::filter('i_am_collaborator', function() {
  $project = Config::get('project');
  if (!$project->is_mine()) return Redirect::to('/');
});

Route::filter('bid_exists', function() {
  $id = Request::$route->parameters[1];
  $bid = Bid::with_officer_fields()
            ->where('bids.id', '=', $id)
            ->first();

  if (!$bid) return "doesn't exist id " . $id;
  Config::set('bid', $bid);
});

Route::filter('bid_is_not_awarded', function() {
  $bid = Config::get('bid');
  $project = Config::get('project');
  if ($bid->awarded_at) return Redirect::to_route('project', array($project->id));
});

Route::filter('i_am_collaborator_or_bid_vendor', function() {
  $bid = Config::get('bid');
  $project = Config::get('project');
  if (!$bid->is_mine() && !$project->is_mine()) return Redirect::to('/');
});

Route::filter('i_am_bid_vendor', function() {
  $bid = Config::get('bid');
  $project = Config::get('project');
  if (!$bid->is_mine()) return Redirect::to('/');
});

Route::filter('i_have_not_already_bid', function() {
  $project = Config::get('project');
  $bid = $project->current_bid_from(Auth::vendor());

  if ($bid) {
    Session::flash('notice', __("r.flashes.already_bid"));
    return Redirect::to_route('project', array($project->id));
  }
});

Route::filter('project_has_not_already_been_awarded', function() {
  $project = Config::get('project');
  if ($project->winning_bid())
    return Redirect::to_route('project', array($project->id))->with('errors', array(__("r.flashes.already_awarded")));
});

Route::filter('bid_has_not_been_dismissed_or_awarded', function(){
  $bid = Config::get('bid');
  if ($bid->awarded_at || $bid->dismissed_at) return Redirect::to_route('bid', array($bid->project->id, $bid->id));
});
