<?php

class Projects_Controller extends Base_Controller {

  public function __construct() {
    parent::__construct();

    $this->filter('before', 'project_exists')->except(array('new', 'create', 'mine', 'index', 'rss'));

    $this->filter('before', 'template_exists_and_is_forkable')->only('template_post');

    $this->filter('before', 'i_am_collaborator')->except(array('new', 'create', 'mine', 'index', 'show', 'destroy_collaborator', 'rss'));

    $this->filter('before', 'i_am_owner')->only(array('destroy_collaborator'));

    $this->filter('before', 'admin_only')->only(array('new'));
  }

  public function action_new() {
    $view = View::make('projects.new');
    $this->layout->content = $view;
  }

  public function action_create() {
    $project = new Project(Input::get('project'));
    $project->save();
    $project->officers()->attach(Auth::officer()->id, array('owner' => true));
    return Redirect::to_route('root');
  }

  public function action_template() {
    $view = View::make('projects.template');
    $view->project = Config::get('project');
    $view->templates = $view->project->available_templates()->take(3)->get();
    $view->more_templates_count = $view->project->available_templates()->count() - 3;
    if ($view->more_templates_count <= 0) $view->more_templates_count = false;
    $this->layout->content = $view;
  }

  public function action_template_post() {
    // Forking template
    $project = Config::get('project');
    $template = Config::get('template');
    $project->fork_from($template);
    return Redirect::to_route('project_background', array($project->id));
  }

  public function action_more_templates() {
    $project = Config::get('project');
    // @todo this will break once we have more than 100 templates.
    $templates = $project->available_templates()->take(100)->skip(3)->get();

    return Response::json(array('status' => 'success',
                                'html' => View::make('projects.partials.template_lis')
                                              ->with('templates', $templates)
                                              ->with('project', $project)
                                              ->render() ));
  }

  public function action_background() {
    $view = View::make('projects.background');
    $view->project = Config::get('project');
    $this->layout->content = $view;
  }

  public function action_background_post() {
    $project = Config::get('project');
    $project->fill(Input::get('project'));
    $project->save();
    return Redirect::to_route('project_sections', array($project->id));
  }

  public function action_sections_remove($project_id, $section_id) {
    $project = Config::get('project');
    $project->remove_section($section_id);

    if (Input::get('requested_html') == "sections_for_editing") {
      return Response::json(array('status' => 'success',
                                  'sections_for_editing_html' => View::make('projects.partials.sections_for_editing')
                                                                  ->with('project', $project)
                                                                  ->render()));

    }

  }

  public function action_sections_add($project_id, $section_id) {
    $project = Config::get('project');
    $project->add_section($section_id);

    return Response::json(array('status' => 'success',
                                'sections_for_editing_html' => View::make('projects.partials.sections_for_editing')
                                                                ->with('project', $project)
                                                                ->render() ));
  }

  public function action_sections() {
    $view = View::make('projects.sections');
    $view->project = Config::get('project');
    $view->available_sections = $view->project->available_sections()->order_by('times_used', 'desc')->take(20)->get();
    $this->layout->content = $view;
  }

  // This is for adding a new section or updating the text of an existing one.
  public function action_sections_post() {
    $project = Config::get('project');
    $section_id = Input::get('section_id');
    $section_input = Input::get('project_section');

    if ($section_id) {
      // we're editing an existing section
      $section = ProjectSection::find($section_id);
      if ($section->can_edit_without_forking()){
        $section->fill($section_input);
        $section->times_used = 1;
        $section->created_by_project_id = $project->id;
        $section->save();
      } else {
        $new_section = $section->fork($project->id, $section_input);
        $project->replace_section($section->id, $new_section->id);
      }

    } else {
      // we're adding a new sction
      $section = new ProjectSection($section_input);
      $section->created_by_project_id = $project->id;
      $section->save();
      $section->project_types()->attach($project->project_type_id);
      $project->add_section($section->id);
    }

    return Response::json(array('status' => 'success',
                                'sections_for_editing_html' => View::make('projects.partials.sections_for_editing')
                                                                   ->with('project', $project)
                                                                   ->render() ));
  }

  public function action_sections_reorder() {
    $project = Config::get('project');

    $new_order = array_map(function($n){
      return intval($n);
    }, Input::get('sections'));

    $project->reorder_sections_to($new_order);
    return Response::json(array('status' => 'success'));
  }

  public function action_blanks() {
    $view = View::make('projects.blanks');
    $view->project = Config::get('project');
    $this->layout->content = $view;
  }

  public function action_blanks_post() {
    $project = Config::get('project');
    $project->variables = Input::get('variables');
    $project->save();
    return Redirect::to_route('project_timeline', array($project->id));
  }

  public function action_timeline() {
    $view = View::make('projects.timeline');
    $project = Config::get('project');

    // if this step is not yet completed, try to create some
    // deliverables from the project's SOW sections
    if ($project->sow_progress < 5) $project->create_deliverables_from_sow_sections();
    if ($project->deliverables()->count() == 0) $project->deliverables()->insert(new Deliverable());

    $view->project = $project;
    $view->deliverables_json = eloquent_to_json($view->project->deliverables);
    $this->layout->content = $view;
  }

  public function action_review() {
    $view = View::make('projects.review');
    $view->project = Config::get('project');
    $this->layout->content = $view;
  }

  public function action_show() {
    $view = View::make('projects.show');
    $view->project = Config::get('project');
    $this->layout->content = $view;
    // if (Auth::user()) Auth::user()->view_notification_payload('project', $view->project->id, "read");
  }

  public function action_update() {
    $project = Config::get('project');
    $project->fill(Input::get('project'));
    $project->save();
    Session::flash('notice', "$project->title was successfully updated.");
    return Redirect::back();
  }

  public function action_mine() {
    $view = View::make('projects.mine');
    $view->count = Auth::user()->unread_notification_count();
    $view->projects = Auth::officer()->projects;
    $this->layout->content = $view;
  }

  public function action_admin() {
    $view = View::make('projects.admin');
    $view->project = Config::get('project');
    $view->collaborators_json = eloquent_to_json($view->project->officers()->get());
    $this->layout->content = $view;
    Auth::user()->view_project_notifications_for_notification_type($view->project->id, "CollaboratorAdded");
  }

  public function action_toggle_public() {
    $project = Config::get('project');
    $project->toggle_public();
    return Redirect::to(Input::get('redirect'));
  }

  public function action_index() {
    $view = View::make('projects.index');
    $view->projects = Project::order_by('created_at', 'asc')->get();
    $this->layout->content = $view;
  }

  public function action_search_available_sections() {
    $project = Config::get('project');
    $query = Input::get('query');
    $available_sections = $project->available_sections()
                                  ->where(function($q)use($query){
                                    $q->where('section_category', 'LIKE', '%'.$query.'%');
                                    $q->or_where('title', 'LIKE', '%'.$query.'%');
                                    $q->or_where('body', 'LIKE', '%'.$query.'%');
                                  })
                                  ->order_by('times_used', 'desc')
                                  ->take(20)
                                  ->get();

    return Response::json(array('status' => 'success',
                                'available_sections_tbody_html' => View::make('projects.partials.available_sections_tbody')
                                                                       ->with('project', $project)
                                                                       ->with('available_sections', $available_sections)
                                                                       ->render() ));
  }

  public function action_add_collaborator() {
    $project = Config::get('project');
    $input = Input::json();
    $email = $input->user->email;
    $user = User::where_email($email)->first();

    if (!$user) {
      $user = User::new_officer_from_invite($email, Auth::user(), $project);
      if (!$user) return Response::make('400', '400');
      $send_email = false;
    } else {
      $send_email = true;
    }

    if ($user->officer->collaborates_on($project->id)) return Response::json(array("status" => "already exists"));

    $project->officers()->attach($user->officer->id);

    $user->officer->approve();

    Notification::send("CollaboratorAdded", array("project" => $project, "officer" => $user->officer), $send_email);

    return Response::json($user->officer->to_array());
  }

  public function action_destroy_collaborator($project_id, $officer_id) {
    $collaborator = ProjectCollaborator::where_project_id($project_id)
                                       ->where_officer_id($officer_id)
                                       ->where_owner(false)
                                       ->first();

    if ($collaborator) $collaborator->delete();

    return Response::json(array("status" => "success"));
  }

  public function action_release_applicants() {
    $project = Config::get('project');
    $project->release_applicants();
    return Redirect::back();
  }

}

Route::filter('project_exists', function() {
  $id = Request::$route->parameters[0];
  $project = Project::find($id);
  if (!$project) return Redirect::to('/');
  Config::set('project', $project);
});

Route::filter('template_exists_and_is_forkable', function(){
  $project = Config::get('project');
  $id = Request::$route->parameters[1];
  $template = Project::where_id($id)
                     ->where_public(true)
                     ->where_project_type_id($project->project_type_id)
                     ->first();

  if (!$template) return Redirect::to_route('project_background', array($project->id));
  Config::set('template', $template);
});

Route::filter('i_am_collaborator', function() { // also allowed if user is ADMIN
  $project = Config::get('project');
  if (!Auth::officer()) return Redirect::to('/');
  if (!$project->is_mine() && !Auth::officer()->is_role_or_higher(Officer::ROLE_ADMIN)) return Redirect::to('/');
});

Route::filter('i_am_owner', function() {
  $project = Config::get('project');
  if (!$project->i_am_owner() && !Auth::officer()->is_role_or_higher(Officer::ROLE_SUPER_ADMIN)) return Redirect::to('/');
});

Route::filter('admin_only', function() {
  if (!Auth::officer()->is_role_or_higher(Officer::ROLE_ADMIN))
    return Redirect::to('/');
});

