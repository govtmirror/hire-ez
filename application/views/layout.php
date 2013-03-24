<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="description" content="" />
  <meta name="viewport" content="width=device-width" />
  <title><?php echo e(Helper::full_title(Section::yield('page_title'), Section::yield('page_action'))); ?></title>
  <?php if (!Config::get('application.dont_load_fonts')): ?>
    <link href="//fonts.googleapis.com/css?family=Telex" media="all" type="text/css" rel="stylesheet">
  <?php endif; ?>
  <?php echo Helper::asset('css/all'); ?>
  <?php if (Auth::guest()): ?>
    <?php $body_class = "no-auth"; ?>
    <?php print "<style>.only-user { display: none; }</style>"; ?>
  <?php else: ?>
    <?php $body_class = "auth " . (Auth::user()->vendor ? "vendor" : "officer"); ?>
    <?php print "<style>.only-user:not(.only-user-".Auth::user()->id."), .not-user-".Auth::user()->id." { display: none; }</style>"; ?>
  <?php endif; ?>
  <?php if (Auth::officer() && Auth::officer()->role == Officer::ROLE_ADMIN): ?>
    <?php $body_class .= " admin" ?>
  <?php elseif (Auth::officer() && Auth::officer()->role == Officer::ROLE_SUPER_ADMIN): ?>
    <?php $body_class .= " super-admin" ?>
  <?php endif; ?>
  <?php echo HTML::script('js/modernizr.js'); ?>
  <?php echo HTML::script('js/vendor/jquery-1.8.1.min.js'); ?>
  <?php echo Helper::asset('js/global'); ?>
</head>
<body class="<?php echo e($body_class); ?>" data-current-page="<?php echo e(Section::yield('current_page')); ?>" data-officer-name="<?php echo e(Auth::officer() ? Auth::officer()->name : ''); ?>" data-officer-id="<?php echo e(Auth::officer() ? Auth::officer()->id : ''); ?>" data-officer-user-id="<?php echo e(Auth::officer() ? Auth::officer()->user_id : ''); ?>">
  <!--[if lt IE 8]>
    <p class="chromeframe"><?= __("r.chromeframe_text") ?></p>
  <![endif]-->
  <div id="outer-container">
    <?php echo View::make('partials.topnav'); ?>
    <div class="logo-header">
      <div class="container">
        <a class="brand" href="<?php echo e(route('root')); ?>" data-no-turbolink="data-no-turbolink">
          <img src="/img/logo-text-only.png" alt="Presidential Innovation Fellows" />
        </a>
      </div>
    </div>
    <div class="container">
      <?php if (Auth::guest()): ?>
        <?php echo View::make('partials.signin_modal'); ?>
      <?php endif; ?>
      <?php if (Session::has('errors')): ?>
        <div class="alert alert-error">
          <button type="button" class="close" data-dismiss="alert">×</button>
          <ul>
            <?php foreach(Session::get('errors') as $error): ?>
              <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <?php if (Session::has('notice')): ?>
        <div class="alert alert-success alert-bigger">
          <?php echo e(Session::get('notice')); ?>
          <button type="button" class="close" data-dismiss="alert">×</button>
        </div>
      <?php endif; ?>
      <?php if (!Section::yield('no_page_header')) { ?>
        <h4>
          <?php echo e(Section::yield('page_title')); ?>
          <?php echo Section::yield('inside_header'); ?>
        </h4>
      <?php } ?>
      <?php echo Section::yield('subnav'); ?>
      <?php echo $content; ?>
    </div>
  </div>
  <?php echo View::make('partials.footer'); ?>
  <?php if (Request::is_env('production')): ?>
    <script src="/js/vendor/google.analytics.js"></script>
  <?php endif; ?>
</body>
</html>