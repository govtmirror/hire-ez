<?php

class Officer extends Eloquent {

  const ROLE_NOT_APPROVED = 0;
  const ROLE_APPROVED = 1;
  const ROLE_ADMIN = 2;
  const ROLE_SUPER_ADMIN = 3;

  public static $timestamps = true;

  public static $accessible = array('phone', 'name', 'title', 'agency');

  public static $hidden = array('created_at', 'updated_at');

  public $includes = array('user');

  public $includes_in_array = array('role_text');

  public $validator = false;

  public function validator() {
    if ($this->validator) return $this->validator;

    $rules = array();

    $validator = Validator::make($this->attributes, $rules);
    $validator->passes(); // hack to populate error messages

    return $this->validator = $validator;
  }

  public function is_role_or_higher($role) {
    return $this->role >= $role;
  }

  public function approve() {
    if ($this->role > self::ROLE_NOT_APPROVED) return;
    $this->role = self::ROLE_APPROVED;
    $this->save();
  }

  public function role_text() {
    switch ($this->role) {
      case self::ROLE_NOT_APPROVED:
        return "Not Approved";
      case self::ROLE_APPROVED:
        return "Approved";
      case self::ROLE_ADMIN:
        return "Admin";
      case self::ROLE_SUPER_ADMIN:
        return "Super Admin";
    }
  }

  public function projects() {
    return $this->has_many_and_belongs_to('Project', 'project_collaborators');
  }

  public function comments() {
    return $this->has_many('Comment')->where_null('deleted_at');
  }

  public function user() {
    return $this->belongs_to('User');
  }

  public function collaborates_on($project_id) {
    return in_array($project_id, $this->projects()->lists('id'));
  }

  public function ban() {
    $this->user->banned_at = new \DateTime;
    $this->user->save();
  }

  public function unban() {
    $this->user->banned_at = null;
    $this->user->save();
    $this->save();
  }

}
