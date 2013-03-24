<?php

Class Helper {

  public static function demographic_percentage($num) {
    return round($num*100, 2)."%";
  }

  public static function get_bid_paginator($skip, $per_page, $total) {
    $firstResult = $skip + 1;
    $lastResult = $firstResult + $per_page - 1;
    if ($lastResult > $total) $lastResult = $total;
    if ($lastResult == 0) $firstResult = 0;
    return array(
      'display_range' => $firstResult."-".$lastResult,
      'total' => $total,
      'lastResult' => $lastResult,
      'showingLastResult' => $lastResult == $total,
      'showingFirstResult' => ($firstResult < 2)
    );
  }

  public static function preserve_input($name) {
    if ($val = e(Input::get($name))) {
      return "<input type='hidden' name='$name' value='$val' />";
    } else {
      return "";
    }
  }

  public static function url_with_query_and_sort_params($url) {
    return self::current_url_without_params("skip", $url);
  }

  public static function current_url_without_sort_params() {
    return self::current_url_without_params(array('skip', 'sort', 'order'));
  }

  public static function current_url_without_search_params() {
    return self::current_url_without_params('q');
  }

  public static function current_url_without_params($params, $url = false) {
    if (!$url) $url = URL::current();
    if (!is_array($params)) $params = array($params);
    $url_params = $_GET;
    foreach ($params as $param) { unset($url_params[$param]); }
    if (!$url_params) return $url;
    return $url . "?" . e(http_build_query($url_params));
  }

  public static function current_sort_link($sort, $title, $default_order = false) {
    $return_str = "<a href='".self::current_sort_url($sort, $default_order)."'>$title</a>";

    if ($sort == Config::get('review_bids_sort')) {
      $return_str .= " " . (Input::get('order') == 'desc' ? "<i class='icon-chevron-down'></i>" : "<i class='icon-chevron-up'></i>");
    }

    return $return_str;
  }

  public static function current_sort_url($sort, $default_order = false) {

    $params = array('sort' => $sort);

    $params = array_merge($_GET, $params);

    if ($sort == Config::get('review_bids_sort')) {
      $params["order"] = (Input::get('order') == 'desc') ? 'asc' : 'desc';
    } elseif ($default_order) {
      $params["order"] = $default_order;
    } else {
      unset($params["order"]);
    }

    unset($params["page"]);

    return URL::current() . "?" . e(http_build_query($params));
  }

  public static function current_url_with_params($parameters) {
    return URL::current() . "?" . e(http_build_query(array_merge($_GET, $parameters)));
  }

  public static function models_to_array($models)
  {
    if ($models instanceof Laravel\Database\Eloquent\Model)
    {
      return json_encode($models->to_array());
    }

    return array_map(function($m) { return $m->to_array(); }, $models);
  }

  public static function asset($n) {

    if (preg_match('/^css/', $n)) {
      $ext = Config::get('assets.use_minified') === false ? ".css" : ".min.css?t=".Config::get('deploy_timestamp');
      return HTML::style($n.$ext);
    } elseif (preg_match('/^js/', $n)) {
      $ext = Config::get('assets.use_minified') === false ? ".js" : ".min.js?t=".Config::get('deploy_timestamp');
      return HTML::script($n.$ext);
    } else {
      throw new \Exception("Can't handle that asset type.");
    }
  }

  public static function timeago($timestamp) {
    $str = strtotime($timestamp);
    return "<span class='timeago' title='".date('c', $str)."'>".date('r', $str)."</abbr>";
  }

  public static function helper_tooltip($title, $placement = "top", $pull_right = false, $no_margin = false) {
    return "<span class='helper-tooltip ".($pull_right ? 'pull-right' : '')."' " .($no_margin ? 'style="margin:0;"' : ''). " data-title=\"".htmlspecialchars($title)."\" data-trigger='manual' data-placement='$placement'>
        <i class='icon-question-sign'></i>
      </span>";
  }

  public static function datum($label, $content, $link = false) {
    if ($content) {
      $isEmail = filter_var($content, FILTER_VALIDATE_EMAIL);
      return "<dt>".e($label)."</dt>
                <dd>".($link ? "<a href='".($isEmail ? "mailto:".e($content) : e($content)).
                  "' ".($isEmail ? '' : 'target="_blank"').">" : "").e($content).($link ? '</a>' : '')."</dd>
             ";
    } else {
      return '';
    }
  }

  public static function flash_errors($errors) {
    if (!is_array($errors)) $errors = array($errors);

    if (Session::has('errors')) {
      Session::flash('errors', array_merge(Session::get('errors'), $errors));
    } else {
      Session::flash('errors', $errors);
    }
  }

  public static function active_nav($section) {
    return (Section::yield('active_nav') == $section) ? true : false;
  }

  public static function active_subnav($section) {
    return (Section::yield('active_subnav') == $section) ? true : false;
  }

  public static function active_sidebar($section) {
    return (Section::yield('active_sidebar') == $section) ? true : false;
  }

  public static function truncate($phrase, $max_words) {
    $phrase_array = explode(' ',$phrase);
    if(count($phrase_array) > $max_words && $max_words > 0) $phrase = implode(' ',array_slice($phrase_array, 0, $max_words)).'...';
    return $phrase;
  }

  public static function full_title($title = "", $action = "") {
    if ($title == "") {
      return __('r.app_name');
    } elseif ($action == "") {
      return "$title | " . __('r.app_name');
    } else {
      return "$action | $title | " . __('r.app_name');
    }
  }

  public static function all_us_states() {
    return array('AL'=>"Alabama",
                'AK'=>"Alaska",
                'AZ'=>"Arizona",
                'AR'=>"Arkansas",
                'CA'=>"California",
                'CO'=>"Colorado",
                'CT'=>"Connecticut",
                'DE'=>"Delaware",
                'DC'=>"District of Columbia",
                'FL'=>"Florida",
                'GA'=>"Georgia",
                'HI'=>"Hawaii",
                'ID'=>"Idaho",
                'IL'=>"Illinois",
                'IN'=>"Indiana",
                'IA'=>"Iowa",
                'KS'=>"Kansas",
                'KY'=>"Kentucky",
                'LA'=>"Louisiana",
                'ME'=>"Maine",
                'MD'=>"Maryland",
                'MA'=>"Massachusetts",
                'MI'=>"Michigan",
                'MN'=>"Minnesota",
                'MS'=>"Mississippi",
                'MO'=>"Missouri",
                'MT'=>"Montana",
                'NE'=>"Nebraska",
                'NV'=>"Nevada",
                'NH'=>"New Hampshire",
                'NJ'=>"New Jersey",
                'NM'=>"New Mexico",
                'NY'=>"New York",
                'NC'=>"North Carolina",
                'ND'=>"North Dakota",
                'OH'=>"Ohio",
                'OK'=>"Oklahoma",
                'OR'=>"Oregon",
                'PA'=>"Pennsylvania",
                'RI'=>"Rhode Island",
                'SC'=>"South Carolina",
                'SD'=>"South Dakota",
                'TN'=>"Tennessee",
                'TX'=>"Texas",
                'UT'=>"Utah",
                'VT'=>"Vermont",
                'VA'=>"Virginia",
                'WA'=>"Washington",
                'WV'=>"West Virginia",
                'WI'=>"Wisconsin",
                'WY'=>"Wyoming");
  }
}
